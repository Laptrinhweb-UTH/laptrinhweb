# SpinBike

SpinBike là đồ án môn Lập Trình Web theo hướng marketplace mua bán xe đạp thể thao cũ, kết hợp cơ chế escrow mô phỏng để tăng độ tin cậy giao dịch.

## Flow chính

`Đăng tin -> Duyệt tin -> Hiển thị bán -> Người mua đặt mua -> Thanh toán an toàn -> Hệ thống giữ tiền -> Người bán xác nhận đơn -> Giao xe -> Người mua xác nhận hoặc khiếu nại -> Giải phóng tiền / Admin xử lý hoàn tiền`

## Chức năng đã có

- Đăng ký, đăng nhập, cập nhật hồ sơ người dùng
- Đăng tin bán xe với hình ảnh Cloudinary
- Quản lý tin đăng của người bán theo trạng thái
- Admin duyệt, từ chối, ẩn tin đăng
- Danh sách xe chỉ hiển thị các tin `approved`
- Đặt mua an toàn và tạo đơn hàng
- Hệ thống tạo escrow `holding` sau khi thanh toán
- Người bán xác nhận tiếp nhận đơn và cập nhật đang giao xe
- Người mua xác nhận nhận xe hoặc gửi khiếu nại
- Seller/Admin hoàn tiền khi đơn đang tranh chấp

## Cấu trúc chính

- `public/`: entry point và tài nguyên giao diện
- `app/models/`: xử lý dữ liệu sản phẩm
- `app/views/`: giao diện auth, products, orders
- `app/controllers/`: các endpoint thao tác trạng thái và xử lý đơn
- `app/services/`: nghiệp vụ escrow
- `app/helpers/`: config flow, DB helper, utility
- `spinbike_db.sql`: schema + seed data demo chuẩn để import lại

## Cách chạy local với XAMPP

1. Copy project vào thư mục web root của XAMPP.
2. Tạo hoặc sửa `config/config.php` nếu cần đổi `APP_URL` / `APP_BASE_PATH`.
3. Mở phpMyAdmin.
4. Drop database `spinbike_db` cũ nếu đã tồn tại.
5. Import file `spinbike_db.sql`.
6. Truy cập project trên trình duyệt qua URL gốc của thư mục project, ví dụ `http://localhost/spinbike`.

## Tài khoản demo

- `admin@spinbike.local / admin123`
- `seller1@spinbike.local / seller123`
- `seller2@spinbike.local / seller123`
- `seller3@spinbike.local / seller123`
- `buyer1@spinbike.local / buyer123`
- `buyer2@spinbike.local / buyer123`
- `demo@spinbike.local / 123456`

## Lưu ý demo

- Các listing chỉ bán được khi ở trạng thái `approved`
- Sau khi đặt mua thành công, listing sẽ bị khóa sang `sold`
- Escrow chỉ giải phóng tiền khi buyer xác nhận đã nhận xe
- Nếu buyer khiếu nại, tiền sẽ bị khóa và seller/admin có thể xử lý hoàn tiền
