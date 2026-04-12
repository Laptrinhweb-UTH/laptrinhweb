<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
  /* Căn giữa hộp đăng nhập mà không làm hỏng layout của Header/Footer */
  .auth-page-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 150px);
    background-color: var(--bg);
    padding: 40px 20px;
  }

  .auth-container {
    background: var(--white);
    width: 100%;
    max-width: 400px;
    border-radius: 24px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding: 32px;
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

  .auth-tabs {
    display: flex;
    background: #f3f4f6;
    border-radius: 12px;
    margin-bottom: 24px;
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
    color: #6b7280;
    cursor: pointer;
    transition: 0.3s;
  }

  .tab-btn.active {
    background: var(--white);
    color: var(--primary);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .auth-form {
    display: none;
    flex-direction: column;
    gap: 16px;
  }

  .auth-form.active {
    display: flex;
    animation: fadeIn 0.4s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .input-group input {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 15px;
    box-sizing: border-box;
    outline: none;
    transition: 0.3s;
  }

  .input-group input:focus {
    border-color: var(--primary-light);
  }

  .btn-submit {
    width: 100%;
    background: var(--primary);
    color: var(--white);
    padding: 14px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 8px;
  }

  .btn-submit:hover {
    background: #083c52;
  }

  .back-home {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
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
      <button class="tab-btn active" onclick="switchTab('login')">
        Đăng nhập
      </button>
      <button class="tab-btn" onclick="switchTab('register')">Đăng ký</button>
    </div>

    <form id="loginForm" class="auth-form active">
      <div class="input-group">
        <input type="email" name="email" placeholder="Email của bạn" required />
      </div>
      <div class="input-group">
        <input
          type="password"
          name="password"
          placeholder="Mật khẩu"
          required
        />
      </div>

      <div
        id="loginMessage"
        style="
          text-align: center;
          font-size: 14px;
          font-weight: 500;
          margin-bottom: 8px;
        "
      ></div>

      <button type="submit" class="btn-submit">Đăng nhập</button>
    </form>

    <form id="registerForm" class="auth-form">
      <div class="input-group">
        <input type="text" name="fullname" placeholder="Họ và tên" required />
      </div>
      <div class="input-group">
        <input type="email" name="email" placeholder="Email của bạn" required />
      </div>
      <div class="input-group">
        <input
          type="password"
          name="password"
          placeholder="Mật khẩu"
          required
        />
      </div>
      <div class="input-group">
        <input
          type="password"
          name="confirm_password"
          placeholder="Xác nhận mật khẩu"
          required
        />
      </div>

      <div
        id="registerMessage"
        style="text-align: center; font-size: 14px; font-weight: 500"
      ></div>

      <button type="submit" class="btn-submit">Tạo tài khoản</button>
    </form>

    <a href="<?php echo BASE_URL; ?>/index.php" class="back-home">
      <i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ
    </a>
  </div>
</main>

<script>
  function switchTab(tabId) {
    document
      .querySelectorAll(".tab-btn")
      .forEach((btn) => btn.classList.remove("active"));
    document
      .querySelectorAll(".auth-form")
      .forEach((form) => form.classList.remove("active"));

    if (tabId === "login") {
      document.querySelectorAll(".tab-btn")[0].classList.add("active");
      document.getElementById("loginForm").classList.add("active");
    } else {
      document.querySelectorAll(".tab-btn")[1].classList.add("active");
      document.getElementById("registerForm").classList.add("active");
    }
  }

  // XỬ LÝ FORM ĐĂNG KÝ
  document
    .getElementById("registerForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();
      const messageBox = document.getElementById("registerMessage");
      messageBox.style.color = "#0a4d68";
      messageBox.innerHTML =
        "<i class='fa-solid fa-spinner fa-spin'></i> Đang xử lý...";

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
    messageBox.innerHTML =
      "<i class='fa-solid fa-spinner fa-spin'></i> Đang kiểm tra...";

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
            // Chuyển hướng về trang chủ dùng PHP BASE_URL
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
