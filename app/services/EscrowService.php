<?php
// app/services/EscrowService.php

class EscrowService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. NGƯỜI MUA XÁC NHẬN NHẬN HÀNG -> GIẢI PHÓNG TIỀN CHO NGƯỜI BÁN
    public function releaseFunds($order_id, $current_user_id) {
        try {
            // BẮT ĐẦU GIAO DỊCH (Nếu 1 dòng lỗi, toàn bộ sẽ Rollback, không mất tiền)
            $this->conn->beginTransaction();

            // Lấy thông tin đơn hàng và escrow
            $stmt = $this->conn->prepare("SELECT o.*, e.status as escrow_status FROM orders o JOIN escrows e ON o.id = e.order_id WHERE o.id = ? FOR UPDATE");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            // BẢO MẬT: Kiểm tra điều kiện ngặt nghèo
            if (!$order) throw new Exception("Không tìm thấy đơn hàng.");
            if ($order['buyer_id'] != $current_user_id) throw new Exception("Chỉ người mua mới có quyền xác nhận nhận hàng.");
            if ($order['status'] != 'shipping' && $order['status'] != 'paid') throw new Exception("Trạng thái đơn hàng không hợp lệ.");
            if ($order['escrow_status'] != 'holding') throw new Exception("Tiền không ở trạng thái giữ.");

            // TÍNH TOÁN TIỀN BẠC
            $total_amount = $order['amount'];
            $fee = $total_amount * 0.05; // Hệ thống cắn 5%
            $seller_earnings = $total_amount - $fee;

            // BƯỚC 1: Cập nhật Escrow và Order
            $this->conn->prepare("UPDATE escrows SET status = 'released', released_at = NOW() WHERE order_id = ?")->execute([$order_id]);
            $this->conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?")->execute([$order_id]);

            // BƯỚC 2: Cộng tiền vào tài khoản người bán
            $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$seller_earnings, $order['seller_id']]);

            // BƯỚC 3: Ghi nhận lịch sử giao dịch (Transaction)
            // 3.1: Ghi nhận người bán nhận tiền
            $this->conn->prepare("INSERT INTO transactions (user_id, order_id, amount, fee, type) VALUES (?, ?, ?, ?, 'earn')")
                       ->execute([$order['seller_id'], $order_id, $seller_earnings, $fee]);

            // HOÀN TẤT GIAO DỊCH
            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Đã giải phóng tiền cho người bán!'];

        } catch (Exception $e) {
            // CÓ LỖI -> QUAY XE, KHÔNG TRỪ/CỘNG TIỀN GÌ HẾT
            $this->conn->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // 2. HOÀN TIỀN CHO NGƯỜI MUA (REFUND)
    public function refundBuyer($order_id, $admin_or_seller_id) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT o.*, e.status as escrow_status FROM orders o JOIN escrows e ON o.id = e.order_id WHERE o.id = ? FOR UPDATE");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order || $order['escrow_status'] != 'holding') throw new Exception("Không thể hoàn tiền đơn này.");

            // Hoàn 100% tiền cho người mua
            $this->conn->prepare("UPDATE escrows SET status = 'refunded', released_at = NOW() WHERE order_id = ?")->execute([$order_id]);
            $this->conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$order_id]);
            $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$order['amount'], $order['buyer_id']]);
            
            $this->conn->prepare("INSERT INTO transactions (user_id, order_id, amount, type) VALUES (?, ?, ?, 'refund')")
                       ->execute([$order['buyer_id'], $order_id, $order['amount']]);

            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Đã hoàn tiền cho người mua!'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>