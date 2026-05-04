<?php

final class VnpayHelper
{
    public static function isConfigured(): bool
    {
        return trim((string) VNPAY_TMN_CODE) !== '' && trim((string) VNPAY_HASH_SECRET) !== '';
    }

    public static function buildPaymentUrl(array $params): string
    {
        $inputData = array_filter($params, static fn($value): bool => $value !== null && $value !== '');
        ksort($inputData);

        $query = http_build_query($inputData);
        $secureHash = hash_hmac('sha512', $query, VNPAY_HASH_SECRET);

        return VNPAY_URL . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    }

    public static function validateReturn(array $queryParams): bool
    {
        $secureHash = (string) ($queryParams['vnp_SecureHash'] ?? '');
        if ($secureHash === '') {
            return false;
        }

        $inputData = [];
        foreach ($queryParams as $key => $value) {
            if (str_starts_with((string) $key, 'vnp_')
                && $key !== 'vnp_SecureHash'
                && $key !== 'vnp_SecureHashType'
            ) {
                $inputData[$key] = $value;
            }
        }

        ksort($inputData);
        $hashData = http_build_query($inputData);
        $calculatedHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);

        return hash_equals($calculatedHash, $secureHash);
    }

    public static function buildOrderInfo(int $orderId): string
    {
        return 'Thanh toan don hang SpinBike ' . $orderId;
    }

    public static function responseMessage(string $responseCode, string $transactionStatus = ''): string
    {
        if ($responseCode === '00' && ($transactionStatus === '' || $transactionStatus === '00')) {
            return 'Thanh toán VNPAY sandbox thành công.';
        }

        return match ($responseCode) {
            '07' => 'Giao dịch bị nghi ngờ gian lận trên môi trường VNPAY.',
            '09' => 'Thẻ hoặc tài khoản chưa đăng ký Internet Banking.',
            '10' => 'Xác thực thông tin thẻ không đúng quá số lần cho phép.',
            '11' => 'Đã hết hạn chờ thanh toán.',
            '12' => 'Thẻ hoặc tài khoản đang bị khóa.',
            '13' => 'Mã OTP không đúng.',
            '24' => 'Bạn đã hủy giao dịch thanh toán.',
            '51' => 'Tài khoản không đủ số dư.',
            '65' => 'Tài khoản vượt hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '97' => 'Chữ ký phản hồi VNPAY không hợp lệ.',
            default => 'Thanh toán VNPAY chưa thành công. Mã phản hồi: ' . $responseCode,
        };
    }
}
