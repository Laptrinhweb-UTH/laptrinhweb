<?php include __DIR__ . '/../layouts/header.php'; ?>

<main class="auth-page-wrapper">
  <div class="auth-container">
    <div class="auth-header">
      <i class="fa-solid fa-bicycle"></i>
      <h2 class="auth-brand-title">SpinBike</h2>
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

      <div id="loginMessage" class="auth-message"></div>

      <button type="submit" class="btn-submit auth-submit-btn">Đăng nhập</button>
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

      <div id="registerMessage" class="auth-message"></div>

      <button type="submit" class="btn-submit auth-submit-btn">Tạo tài khoản</button>
    </form>

    <a href="<?php echo asset_url('index.php'); ?>" class="back-home">
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
            window.location.href = data.redirect_url || "<?php echo asset_url('index.php'); ?>";
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
