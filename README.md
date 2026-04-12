```
spinbike/
├── app/
│   ├── controllers/
│   │   ├── ProductController.php
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   └── SellController.php
│   ├── models/
│   │   ├── Product.php
│   │   ├── User.php
│   │   └── Order.php
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   └── base.php
│   │   ├── products/
│   │   │   ├── list.php
│   │   │   ├── detail.php
│   │   │   └── sell.php
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── register.php
│   │   └── user/
│   │       └── profile.php
│   └── helpers/
│       ├── Router.php
│       └── Database.php
├── config/
│   └── config.php (DB connection)
├── public/
│   ├── index.php
│    └── assets/
│       ├── css/
│       │   └── style.css
│       └── js/
│           └── script.js
├── storage/
│   ├── uploads/
│   │   └── bikes/
│   └── logs/
└── routes/
    └── web.php
```
