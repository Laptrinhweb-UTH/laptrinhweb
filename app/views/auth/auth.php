<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
  /* Căn giữa hộp đăng nhập */
  .auth-page-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 150px);
    background-color: var(--bg);
    padding: 40px 20px;
  }

  /* Nền hộp xám nhạt để làm nổi bật ô nhập liệu màu trắng */
  .auth-container {
    background: #f8fafc; /* Nền xám nhạt y hệt ảnh */
    width: 100%;
    max-width: 420px;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    padding: 32px;
    border: 1px solid var(--border);
  }

  .auth-header {
    text-align: center;
    margin-bottom: 24px;
  }

  .auth-header i {
    font-size: 40px;
    color: var(--primary-light);
    margin-bottom: 10px;
  }

  /* Tabs Đăng nhập / Đăng ký */
  .auth-tabs {
    display: flex;
    background: #e2e8f0;
    border-radius: 12px;
    margin-bottom: 32px;
    padding: 4px;
  }

  .tab-btn {
    flex: 1;
    padding: 12px;
    text-align: center;
    border: none;
    background: transparent;
    border-radius: 8px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: 0.3s;
    font-size: 15px;
  }

  .tab-btn.active {
    background: var(--white);
    color: var(--primary);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .auth-form {
    display: none;
    flex-direction: column;
    gap: 20px; /* Khoảng cách giữa các cụm input rộng rãi hơn */
  }

  .auth-form.active {
    display: flex;
    animation: fadeIn 0.4s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* CHUẨN STYLE THEO ẢNH: Label nằm trên, Input không viền */
  .input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .input-group label {
    font-size: 15px;
    font-weight: 500;
    color: #0f172a;
  }

  .input-wrapper {
    position: relative;
  }

  .input-wrapper input {
    width: 100%;
    padding: 16px 48px 16px 16px; /* Để chỗ cho icon con mắt bên phải */
    border: none; /* Bỏ viền */
    border-radius: 12px;
    font-size: 15px;
    background-color: #ffffff; /* Nền ô trắng tinh */
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    box-sizing: border-box;
    outline: none;
    transition: all 0.3s ease;
  }

  .input-wrapper input::placeholder {
    color: #94a3b8;
  }

  .input-wrapper input:focus {
    box-shadow: 0 0 0 2px var(--primary-light);
  }

  /* Nút con mắt ẩn/hiện mật khẩu */
  .toggle-password {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    cursor: pointer;
    font-size: 18px;
    transition: color 0.2s;
  }

  .toggle-password:hover {
    color: var(--primary);
  }

  /* Link Quên mật khẩu */
  .forgot-password {
    text-align: right;
    display: block;
    color: #2563eb; /* Màu xanh biển */
    font-size: 14px;
    text-decoration: none;
    font-weight: 500;
    margin-top: -8px; /* Kéo lên gần ô mật khẩu 1 chút */
  }

  .forgot-password:hover {
    text-decoration: underline;
  }

.btn-submit {
    width: 100%;
    background: #10b981; /* Màu xanh lá chuẩn của SpinBike */
    color: #ffffff;
    padding: 16px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 8px;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25); /* Bóng đổ phát sáng màu xanh lá nhẹ */
  }

  .btn-submit:hover {
    background: #059669; /* Xanh lá đậm hơn 1 tông khi rê chuột */
    transform: translateY(-2px); /* Hiệu ứng nảy lên 1 chút xíu */
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35); /* Bóng đổ to hơn khi rê chuột */
  }


  .back-home {
    display: block;
    text-align: center;
    margin-top: 24px;
    color: #64748b;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
  }

  .back-home:hover {
    color: var(--primary);
  }
</style>

<main class="auth-page-wrapper">
  <div class="auth-container">
    <div class="auth-header">
      <i class="fa-solid fa-bicycle"></i>
      <h2 style="margin: 0; color: #0a4d68">SpinBike</h2>
    </div>

    <div class="auth-tabs">
      <button class="tab-btn active" onclick="switchTab('login')">Đăng nhập</button>
      <button class="tab-btn" onclick="switchTab('register')">Đăng ký</button>
    </div>

    <form id="loginForm" class="auth-form active">
      <div class="input-group">
        <label>Email / Số điện thoại</label>
        <div class="input-wrapper">
            <input type="text" name="email" placeholder="Nhập email/số điện thoại" required />
        </div>
      </div>
      
      <div class="input-group">
        <label>Mật khẩu</label>
        <div class="input-wrapper">
            <input type="password" id="loginPassword" name="password" placeholder="Nhập mật khẩu" required />
            <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePasswordVisibility('loginPassword', this)"></i>
        </div>
      </div>
      
      <a href="#" class="forgot-password">Quên mật khẩu?</a>

      <div id="loginMessage" style="text-align: center; font-size: 14px; font-weight: 500;"></div>

      <button type="submit" class="btn-submit">Đăng nhập</button>
    </form>

    <form id="registerForm" class="auth-form">
      <div class="input-group">
        <label>Họ và tên</label>
        <div class="input-wrapper">
            <input type="text" name="fullname" placeholder="VD: Nguyễn Hoài Nam" required />
        </div>
      </div>
      
      <div class="input-group">
        <label>Email</label>
        <div class="input-wrapper">
            <input type="email" name="email" placeholder="Nhập email của bạn" required />
        </div>
      </div>
      
      <div class="input-group">
        <label>Mật khẩu</label>
        <div class="input-wrapper">
            <input type="password" id="regPassword" name="password" placeholder="Tạo mật khẩu" required />
            <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePasswordVisibility('regPassword', this)"></i>
        </div>
      </div>
      
      <div class="input-group">
        <label>Xác nhận mật khẩu</label>
        <div class="input-wrapper">
            <input type="password" id="regConfirmPassword" name="confirm_password" placeholder="Nhập lại mật khẩu" required />
            <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePasswordVisibility('regConfirmPassword', this)"></i>
        </div>
      </div>

      <div id="registerMessage" style="text-align: center; font-size: 14px; font-weight: 500;"></div>

      <button type="submit" class="btn-submit">Tạo tài khoản</button>
    </form>

    <a href="<?php echo BASE_URL; ?>/index.php" class="back-home">
      <i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ
    </a>
  </div>
</main>

<script>
  // Hàm chuyển đổi form Đăng nhập / Đăng ký
  function switchTab(tabId) {
    document.querySelectorAll(".tab-btn").forEach((btn) => btn.classList.remove("active"));
    document.querySelectorAll(".auth-form").forEach((form) => form.classList.remove("active"));

    if (tabId === "login") {
      document.querySelectorAll(".tab-btn")[0].classList.add("active");
      document.getElementById("loginForm").classList.add("active");
    } else {
      document.querySelectorAll(".tab-btn")[1].classList.add("active");
      document.getElementById("registerForm").classList.add("active");
    }
  }

  // Hàm Ẩn/Hiện mật khẩu khi bấm vào con mắt
  function togglePasswordVisibility(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
  }

  // XỬ LÝ FORM ĐĂNG KÝ
  document.getElementById("registerForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const messageBox = document.getElementById("registerMessage");
    messageBox.style.color = "#0a4d68";
    messageBox.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Đang xử lý...";

    const formData = new FormData(this);

    fetch("register.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          messageBox.style.color = "#10b981";
          messageBox.innerHTML = data.message;
          this.reset();
          setTimeout(() => switchTab("login"), 2000);
        } else {
          messageBox.style.color = "#e11d48";
          messageBox.innerHTML = data.message;
        }
      })
      .catch((error) => {
        messageBox.style.color = "#e11d48";
        messageBox.innerHTML = "Lỗi kết nối đến máy chủ!";
      });
  });

  // XỬ LÝ FORM ĐĂNG NHẬP
  document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const messageBox = document.getElementById("loginMessage");
    messageBox.style.color = "#0a4d68";
    messageBox.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Đang kiểm tra...";

    const formData = new FormData(this);

    fetch("login.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          messageBox.style.color = "#10b981";
          messageBox.innerHTML = data.message;

          setTimeout(() => {
            window.location.href = "<?php echo BASE_URL; ?>/index.php";
          }, 1000);
        } else {
          messageBox.style.color = "#e11d48";
          messageBox.innerHTML = data.message;
        }
      })
      .catch((error) => {
        messageBox.style.color = "#e11d48";
        messageBox.innerHTML = "Lỗi kết nối đến máy chủ!";
      });
  });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>