<?php
// app/services/EscrowService.php

require_once __DIR__ . '/../helpers/ProjectFlow.php';

class EscrowService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function getLockedOrderWithEscrow(int $orderId): array {
        $stmt = $this->conn->prepare(
            "SELECT o.*, e.status as escrow_status
             FROM orders o
             JOIN escrows e ON o.id = e.order_id
             WHERE o.id = ?
             FOR UPDATE"
        );
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception("Không tìm thấy đơn hàng.");
        }

        return $order;
    }

    private function buildResult(string $status, string $message, array $extra = []): array {
        return array_merge([
            'status' => $status,
            'message' => $message,
        ], $extra);
    }

    public function sellerConfirmOrder($order_id, $current_user_id) {
        try {
            $orderId = (int) $order_id;
            $currentUserId = (int) $current_user_id;

            $this->conn->beginTransaction();

            $order = $this->getLockedOrderWithEscrow($orderId);

            if ((int) $order['seller_id'] !== $currentUserId) {
                throw new Exception("Chỉ người bán mới có quyền xác nhận tiếp nhận đơn hàng.");
            }

            if (!ProjectFlow::sellerCanConfirmOrder((string) $order['status'], (string) $order['escrow_status'])) {
                throw new Exception("Đơn hàng hiện không ở trạng thái chờ người bán xác nhận.");
            }

            $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?")
                ->execute([ProjectFlow::ORDER_SELLER_CONFIRMED, $orderId]);

            $this->conn->commit();

            return $this->buildResult('success', 'Người bán đã xác nhận tiếp nhận đơn hàng.', [
                'order_status' => ProjectFlow::ORDER_SELLER_CONFIRMED,
                'escrow_status' => $order['escrow_status'],
            ]);
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return $this->buildResult('error', $e->getMessage());
        }
    }

    public function markOrderShipping($order_id, $current_user_id) {
        try {
            $orderId = (int) $order_id;
            $currentUserId = (int) $current_user_id;

            $this->conn->beginTransaction();

            $order = $this->getLockedOrderWithEscrow($orderId);

            if ((int) $order['seller_id'] !== $currentUserId) {
                throw new Exception("Chỉ người bán mới có quyền cập nhật đơn hàng sang trạng thái đang giao.");
            }

            if (!ProjectFlow::sellerCanMarkShipping((string) $order['status'], (string) $order['escrow_status'])) {
                throw new Exception("Đơn hàng hiện chưa thể chuyển sang trạng thái đang giao.");
            }

            $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?")
                ->execute([ProjectFlow::ORDER_SHIPPING, $orderId]);

            $this->conn->commit();

            return $this->buildResult('success', 'Đơn hàng đã được cập nhật sang trạng thái đang giao xe.', [
                'order_status' => ProjectFlow::ORDER_SHIPPING,
                'escrow_status' => $order['escrow_status'],
            ]);
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return $this->buildResult('error', $e->getMessage());
        }
    }

    // 1. NGƯỜI MUA XÁC NHẬN NHẬN HÀNG -> GIẢI PHÓNG TIỀN CHO NGƯỜI BÁN
    public function releaseFunds($order_id, $current_user_id) {
        try {
            $orderId = (int) $order_id;
            $currentUserId = (int) $current_user_id;

            // BẮT ĐẦU GIAO DỊCH (Nếu 1 dòng lỗi, toàn bộ sẽ Rollback, không mất tiền)
            $this->conn->beginTransaction();

            $order = $this->getLockedOrderWithEscrow($orderId);

            // BẢO MẬT: Kiểm tra điều kiện ngặt nghèo
            if ((int) $order['buyer_id'] !== $currentUserId) throw new Exception("Chỉ người mua mới có quyền xác nhận nhận hàng.");
            if (!ProjectFlow::orderCanBeConfirmedByBuyer((string) $order['status'], (string) $order['escrow_status'])) {
                throw new Exception("Trạng thái đơn hàng không hợp lệ.");
            }

            // TÍNH TOÁN TIỀN BẠC
            $total_amount = (float) $order['amount'];
            $fee = $total_amount * 0.05; // Hệ thống cắn 5%
            $seller_earnings = $total_amount - $fee;

            // BƯỚC 1: Cập nhật Escrow và Order
            $this->conn->prepare("UPDATE escrows SET status = 'released', released_at = NOW() WHERE order_id = ?")->execute([$orderId]);
            $this->conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?")->execute([$orderId]);

            // BƯỚC 2: Cộng tiền vào tài khoản người bán
            $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$seller_earnings, $order['seller_id']]);

            // BƯỚC 3: Ghi nhận lịch sử giao dịch (Transaction)
            // 3.1: Ghi nhận người bán nhận tiền
            $this->conn->prepare("INSERT INTO transactions (user_id, order_id, amount, fee, type) VALUES (?, ?, ?, ?, 'earn')")
                       ->execute([$order['seller_id'], $orderId, $seller_earnings, $fee]);

            // HOÀN TẤT GIAO DỊCH
            $this->conn->commit();
            return $this->buildResult('success', 'Đã giải phóng tiền cho người bán!', [
                'order_status' => ProjectFlow::ORDER_COMPLETED,
                'escrow_status' => ProjectFlow::ESCROW_RELEASED,
            ]);

        } catch (Exception $e) {
            // CÓ LỖI -> QUAY XE, KHÔNG TRỪ/CỘNG TIỀN GÌ HẾT
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return $this->buildResult('error', $e->getMessage());
        }
    }

    public function markAsDisputed($order_id, $current_user_id, $reason) {
        try {
            $orderId = (int) $order_id;
            $currentUserId = (int) $current_user_id;
            $cleanReason = trim((string) $reason);
            if ($cleanReason === '') {
                throw new Exception("Lý do khiếu nại không được để trống.");
            }

            $this->conn->beginTransaction();

            $order = $this->getLockedOrderWithEscrow($orderId);

            if ((int) $order['buyer_id'] !== $currentUserId) {
                throw new Exception("Chỉ người mua mới có quyền gửi khiếu nại cho đơn hàng này.");
            }

            if (!ProjectFlow::orderCanBeDisputedByBuyer((string) $order['status'], (string) $order['escrow_status'])) {
                throw new Exception("Đơn hàng này không còn ở trạng thái có thể khiếu nại.");
            }

            $this->conn->prepare("UPDATE escrows SET status = 'disputed' WHERE order_id = ?")->execute([$orderId]);

            $this->conn->commit();
            return $this->buildResult('success', 'Đã ghi nhận khiếu nại của bạn. SpinBike sẽ tạm giữ tiền để chờ xử lý.', [
                'order_status' => $order['status'],
                'escrow_status' => ProjectFlow::ESCROW_DISPUTED,
                'reason' => $cleanReason,
            ]);
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return $this->buildResult('error', $e->getMessage());
        }
    }

    // 2. HOÀN TIỀN CHO NGƯỜI MUA (REFUND)
    public function refundBuyer($order_id, $actor_id, $actor_role = 'user') {
        try {
            $orderId = (int) $order_id;
            $actorId = (int) $actor_id;
            $actorRole = (string) $actor_role;

            $this->conn->beginTransaction();

            $order = $this->getLockedOrderWithEscrow($orderId);

            $canResolveRefund = $actorRole === 'admin' || $actorId === (int) $order['seller_id'];
            if (!$canResolveRefund) {
                throw new Exception("Bạn không có quyền hoàn tiền cho đơn hàng này.");
            }

            if (!ProjectFlow::orderCanBeRefunded((string) $order['escrow_status'])) {
                throw new Exception("Không thể hoàn tiền đơn này ở trạng thái hiện tại.");
            }

            // Hoàn 100% tiền cho người mua
            $this->conn->prepare("UPDATE escrows SET status = 'refunded', released_at = NOW() WHERE order_id = ?")->execute([$orderId]);
            $this->conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$orderId]);
            $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$order['amount'], $order['buyer_id']]);
            
            $this->conn->prepare("INSERT INTO transactions (user_id, order_id, amount, type) VALUES (?, ?, ?, 'refund')")
                       ->execute([$order['buyer_id'], $orderId, $order['amount']]);

            $this->conn->commit();
            return $this->buildResult('success', 'Đã hoàn tiền cho người mua!', [
                'order_status' => ProjectFlow::ORDER_CANCELLED,
                'escrow_status' => ProjectFlow::ESCROW_REFUNDED,
            ]);

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return $this->buildResult('error', $e->getMessage());
        }
    }
}
?>
