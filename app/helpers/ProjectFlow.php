<?php

final class ProjectFlow
{
    public const LISTING_PENDING = 'pending';
    public const LISTING_APPROVED = 'approved';
    public const LISTING_REJECTED = 'rejected';
    public const LISTING_SOLD = 'sold';
    public const LISTING_HIDDEN = 'hidden';

    public const ORDER_PENDING_PAYMENT = 'pending_payment';
    public const ORDER_PAID = 'paid';
    public const ORDER_SELLER_CONFIRMED = 'seller_confirmed';
    public const ORDER_SHIPPING = 'shipping';
    public const ORDER_COMPLETED = 'completed';
    public const ORDER_CANCELLED = 'cancelled';

    public const ESCROW_HOLDING = 'holding';
    public const ESCROW_RELEASED = 'released';
    public const ESCROW_REFUNDED = 'refunded';
    public const ESCROW_DISPUTED = 'disputed';

    private const FINAL_FLOW_STEPS = [
        'Đăng tin',
        'Duyệt tin',
        'Hiển thị bán',
        'Người mua đặt mua',
        'Thanh toán an toàn',
        'Hệ thống giữ tiền',
        'Người bán xác nhận đơn',
        'Giao xe',
        'Người mua xác nhận hoặc khiếu nại',
        'Giải phóng tiền / Admin xử lý hoàn tiền',
    ];

    private const LISTING_BLUEPRINT = [
        self::LISTING_PENDING => [
            'label' => 'Chờ duyệt',
            'description' => 'Tin mới tạo, đang chờ admin kiểm tra trước khi hiển thị.',
        ],
        self::LISTING_APPROVED => [
            'label' => 'Đang hiển thị',
            'description' => 'Tin đã được duyệt và xuất hiện ở trang mua.',
        ],
        self::LISTING_REJECTED => [
            'label' => 'Bị từ chối',
            'description' => 'Tin chưa đạt yêu cầu và cần người bán chỉnh sửa.',
        ],
        self::LISTING_SOLD => [
            'label' => 'Đã bán',
            'description' => 'Xe đã chốt giao dịch hoặc không còn mở bán.',
        ],
        self::LISTING_HIDDEN => [
            'label' => 'Đã ẩn',
            'description' => 'Tin tạm thời bị ẩn khỏi trang mua.',
        ],
    ];

    private const ORDER_BLUEPRINT = [
        self::ORDER_PENDING_PAYMENT => [
            'label' => 'Chờ thanh toán',
            'description' => 'Người mua đã đặt mua nhưng chưa hoàn tất thanh toán an toàn.',
            'runtime_supported' => false,
        ],
        self::ORDER_PAID => [
            'label' => 'Đã thanh toán',
            'description' => 'Hệ thống đã nhận tiền và bắt đầu giữ tiền cho giao dịch.',
            'runtime_supported' => true,
        ],
        self::ORDER_SELLER_CONFIRMED => [
            'label' => 'Người bán đã xác nhận đơn',
            'description' => 'Người bán đã nhận đơn và chuẩn bị giao xe.',
            'runtime_supported' => false,
        ],
        self::ORDER_SHIPPING => [
            'label' => 'Đang giao xe',
            'description' => 'Xe đang trong quá trình bàn giao hoặc vận chuyển.',
            'runtime_supported' => true,
        ],
        self::ORDER_COMPLETED => [
            'label' => 'Hoàn tất',
            'description' => 'Người mua đã xác nhận hài lòng, đơn đã khép lại.',
            'runtime_supported' => true,
        ],
        self::ORDER_CANCELLED => [
            'label' => 'Đã hủy',
            'description' => 'Đơn đã bị dừng hoặc xử lý hoàn tiền.',
            'runtime_supported' => true,
        ],
    ];

    private const ESCROW_BLUEPRINT = [
        self::ESCROW_HOLDING => [
            'label' => 'SpinBike đang giữ tiền',
            'description' => 'Khoản tiền đang được giữ để bảo vệ giao dịch.',
        ],
        self::ESCROW_RELEASED => [
            'label' => 'Đã giải phóng cho người bán',
            'description' => 'Người mua đã xác nhận hài lòng và tiền được chuyển cho người bán.',
        ],
        self::ESCROW_REFUNDED => [
            'label' => 'Đã hoàn tiền cho người mua',
            'description' => 'Giao dịch được xử lý theo hướng hoàn tiền.',
        ],
        self::ESCROW_DISPUTED => [
            'label' => 'Đang xử lý khiếu nại',
            'description' => 'Đơn đang có tranh chấp và tiền vẫn bị khóa.',
        ],
    ];

    public static function finalFlowSteps(): array
    {
        return self::FINAL_FLOW_STEPS;
    }

    public static function listingBlueprint(): array
    {
        return self::LISTING_BLUEPRINT;
    }

    public static function listingLabel(string $status): string
    {
        return self::LISTING_BLUEPRINT[$status]['label'] ?? 'Đang cập nhật';
    }

    public static function listingDescription(string $status): string
    {
        return self::LISTING_BLUEPRINT[$status]['description'] ?? 'Trạng thái tin đăng đang được cập nhật.';
    }

    public static function listingBadgeClass(string $status): string
    {
        return match ($status) {
            self::LISTING_APPROVED => 'bg-success',
            self::LISTING_PENDING => 'bg-warning text-dark',
            self::LISTING_REJECTED => 'bg-danger',
            self::LISTING_SOLD => 'bg-primary',
            self::LISTING_HIDDEN => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public static function sellerAllowedListingActions(string $status): array
    {
        return match ($status) {
            self::LISTING_APPROVED => ['hide', 'mark_sold'],
            self::LISTING_HIDDEN => ['show'],
            default => [],
        };
    }

    public static function adminAllowedListingActions(string $status): array
    {
        return match ($status) {
            self::LISTING_PENDING => ['approve', 'reject'],
            self::LISTING_APPROVED => ['hide', 'reject'],
            self::LISTING_REJECTED => ['approve'],
            self::LISTING_HIDDEN => ['approve'],
            default => [],
        };
    }

    public static function listingActionLabel(string $action): string
    {
        return match ($action) {
            'approve' => 'Duyệt tin',
            'reject' => 'Từ chối',
            'hide' => 'Ẩn tin',
            'show' => 'Hiện lại',
            'mark_sold' => 'Đánh dấu đã bán',
            default => 'Cập nhật',
        };
    }

    public static function orderBlueprint(): array
    {
        return self::ORDER_BLUEPRINT;
    }

    public static function escrowBlueprint(): array
    {
        return self::ESCROW_BLUEPRINT;
    }

    public static function orderRuntimeStatuses(): array
    {
        return array_keys(array_filter(
            self::ORDER_BLUEPRINT,
            static fn(array $meta): bool => !empty($meta['runtime_supported'])
        ));
    }

    public static function orderPendingCheckoutStatuses(): array
    {
        return [
            self::ORDER_PENDING_PAYMENT,
            self::ORDER_PAID,
            self::ORDER_SELLER_CONFIRMED,
            self::ORDER_SHIPPING,
        ];
    }

    public static function orderLabel(string $status): string
    {
        return self::ORDER_BLUEPRINT[$status]['label'] ?? 'Đang cập nhật';
    }

    public static function escrowLabel(string $status): string
    {
        return self::ESCROW_BLUEPRINT[$status]['label'] ?? 'Đang cập nhật';
    }

    public static function orderDescription(string $status): string
    {
        return self::ORDER_BLUEPRINT[$status]['description'] ?? 'Trạng thái đơn hàng đang được cập nhật.';
    }

    public static function escrowDescription(string $status): string
    {
        return self::ESCROW_BLUEPRINT[$status]['description'] ?? 'Trạng thái giữ tiền đang được cập nhật.';
    }

    public static function orderBadgeClass(string $status): string
    {
        return match ($status) {
            self::ORDER_COMPLETED, self::ESCROW_RELEASED => 'bg-success',
            self::ORDER_PAID, self::ORDER_SHIPPING, self::ORDER_SELLER_CONFIRMED => 'bg-primary',
            self::ESCROW_HOLDING => 'bg-warning text-dark',
            self::ORDER_CANCELLED, self::ESCROW_REFUNDED, self::ESCROW_DISPUTED => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function orderCanBeConfirmedByBuyer(string $orderStatus, string $escrowStatus): bool
    {
        return in_array($orderStatus, [self::ORDER_PAID, self::ORDER_SELLER_CONFIRMED, self::ORDER_SHIPPING], true)
            && $escrowStatus === self::ESCROW_HOLDING;
    }

    public static function orderCanBeDisputedByBuyer(string $orderStatus, string $escrowStatus): bool
    {
        return self::orderCanBeConfirmedByBuyer($orderStatus, $escrowStatus);
    }

    public static function orderCanBeRefunded(string $escrowStatus): bool
    {
        return in_array($escrowStatus, [self::ESCROW_HOLDING, self::ESCROW_DISPUTED], true);
    }

    public static function orderTimelineCurrentStep(string $orderStatus, string $escrowStatus): int
    {
        if ($escrowStatus === self::ESCROW_DISPUTED) {
            return 3;
        }

        return match ($orderStatus) {
            self::ORDER_PENDING_PAYMENT => 0,
            self::ORDER_PAID => 1,
            self::ORDER_SELLER_CONFIRMED, self::ORDER_SHIPPING => 2,
            self::ORDER_COMPLETED => 3,
            default => 1,
        };
    }

    public static function orderListGuide(string $orderStatus, string $escrowStatus, string $view): string
    {
        if ($escrowStatus === self::ESCROW_DISPUTED) {
            return $view === 'seller'
                ? 'Đơn đang có khiếu nại và tiền vẫn bị giữ. Bạn cần phối hợp xử lý trước khi giao dịch khép lại.'
                : 'Bạn đã gửi khiếu nại cho đơn này. SpinBike đang tạm giữ tiền để chờ xử lý.';
        }

        if ($escrowStatus === self::ESCROW_REFUNDED) {
            return $view === 'seller'
                ? 'Đơn đã được đóng theo hướng hoàn tiền cho người mua.'
                : 'Đơn đã được xử lý hoàn tiền và khép lại.';
        }

        if ($orderStatus === self::ORDER_COMPLETED) {
            return 'Đơn đã hoàn tất.';
        }

        if ($escrowStatus === self::ESCROW_HOLDING) {
            return 'Tiền vẫn đang được hệ thống giữ an toàn.';
        }

        return 'Đơn đang được theo dõi và cập nhật trạng thái.';
    }

    public static function orderFlowSnapshot(): array
    {
        return [
            'flow_steps' => self::finalFlowSteps(),
            'listing_statuses' => self::listingBlueprint(),
            'order_statuses' => self::orderBlueprint(),
            'escrow_statuses' => self::escrowBlueprint(),
        ];
    }
}
