ban-xe-dap/
├── assets/
│   ├── css/
│   │   └── style.css          ← CSS chính (sẽ dùng Bootstrap cho đẹp nhanh)
│   ├── js/
│   │   └── script.js          ← JS cho giỏ hàng, validate...
│   └── images/
│       ├── logo.png
│       ├── products/          ← ảnh xe đạp (tải miễn phí từ Unsplash/Pexels)
│       └── banners/
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── config.php             ← kết nối DB
│   └── functions.php          ← hàm tiện ích (format giá, giỏ hàng session...)
├── admin/                     ← khu vực quản trị (sau bảo vệ bằng mật khẩu)
│   ├── index.php              ← dashboard
│   ├── login.php
│   ├── products/
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── list.php
│   └── orders/
│       └── list.php
├── cart.php                   ← giỏ hàng
├── checkout.php               ← thanh toán / đặt hàng
├── product-detail.php         ← chi tiết xe (chọn size, màu, phụ kiện)
├── index.php                  ← trang chủ
└── contact.php                ← liên hệ (tùy chọn)
