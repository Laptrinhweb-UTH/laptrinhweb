document.addEventListener("DOMContentLoaded", () => {
  // JS Xử lý Hamburger Menu trên Mobile
  const mobileToggle = document.getElementById("mobile-toggle");
  const headerCenter = document.getElementById("header-center");
  const mobileIcon = mobileToggle.querySelector("i");

  // JS Xử lý Dropdown riêng trên Mobile (Click để mở thay vì Hover)
  const dropdownToggleMobile = document.getElementById(
    "dropdown-toggle-mobile",
  );
  const dropdownMenuMobile = document.getElementById("dropdown-menu-mobile");

  // Đóng/Mở menu Hamburger
  mobileToggle.addEventListener("click", () => {
    headerCenter.classList.toggle("active");

    // Đổi icon hamburger <-> dấu X
    if (headerCenter.classList.contains("active")) {
      mobileIcon.classList.remove("fa-bars");
      mobileIcon.classList.add("fa-xmark");
    } else {
      mobileIcon.classList.remove("fa-xmark");
      mobileIcon.classList.add("fa-bars");
    }
  });

  // Xử lý mở/đóng danh mục sản phẩm khi đang ở chế độ mobile
  dropdownToggleMobile.addEventListener("click", (e) => {
    if (window.innerWidth <= 768) {
      e.preventDefault(); // Ngăn link '#' nhảy lên đầu trang
      dropdownMenuMobile.classList.toggle("active");
    }
  });
});
// =========================================
// JS XỬ LÝ HERO SLIDER BANNER
// =========================================
const sliderWrapper = document.getElementById("slider-wrapper");
const slides = document.querySelectorAll(".slide");
const dots = document.querySelectorAll(".dot");

let currentSlide = 0;
const totalSlides = slides.length;
const autoSlideInterval = 5000; // 7000ms = 7 giây
let slideTimer;

// Hàm thực hiện trượt slide
function goToSlide(index) {
  // Xử lý logic quay vòng lặp lại
  if (index >= totalSlides) {
    currentSlide = 0;
  } else if (index < 0) {
    currentSlide = totalSlides - 1;
  } else {
    currentSlide = index;
  }

  // Di chuyển Wrapper sang trái
  const translateX = -currentSlide * 100;
  sliderWrapper.style.transform = `translateX(${translateX}%)`;

  // Cập nhật trạng thái các dấu chấm (Dots)
  dots.forEach((dot) => dot.classList.remove("active"));
  if (dots[currentSlide]) {
    dots[currentSlide].classList.add("active");
  }
}

// Hàm tự động lướt
function startAutoSlide() {
  slideTimer = setInterval(() => {
    goToSlide(currentSlide + 1);
  }, autoSlideInterval);
}

// Reset bộ đếm thời gian khi người dùng tự thao tác (click dots)
function resetAutoSlide() {
  clearInterval(slideTimer);
  startAutoSlide();
}

// Bắt sự kiện click vào các dấu chấm
dots.forEach((dot) => {
  dot.addEventListener("click", function () {
    const index = parseInt(this.getAttribute("data-index"));
    goToSlide(index);
    resetAutoSlide(); // Khởi động lại đếm ngược 7 giây
  });
});

// Bắt đầu chạy Slider nếu có slide tồn tại
if (totalSlides > 0) {
  startAutoSlide();
}
// =========================================
// JS XỬ LÝ VUỐT/KÉO CHUỘT (SWIPE/DRAG)
// =========================================
let isDragging = false;
let startPos = 0;
let currentPos = 0;

// Lấy tọa độ X của chuột hoặc ngón tay
function getPositionX(event) {
  return event.type.includes("mouse") ? event.pageX : event.touches[0].clientX;
}

// Khi bắt đầu nhấn chuột / chạm tay
function touchStart(event) {
  isDragging = true;
  startPos = getPositionX(event);
  clearInterval(slideTimer); // Tạm dừng tự động chuyển slide khi đang kéo
}

// Khi di chuyển chuột / ngón tay
function touchMove(event) {
  if (!isDragging) return;
  currentPos = getPositionX(event);
}

// Khi nhả chuột / nhấc tay lên
function touchEnd() {
  if (!isDragging) return;
  isDragging = false;

  // Tính khoảng cách đã kéo
  const movedBy = currentPos - startPos;

  // Nếu kéo sang trái hơn 50px -> Qua slide tiếp theo
  if (movedBy < -50 && currentPos !== 0) {
    goToSlide(currentSlide + 1);
  }
  // Nếu kéo sang phải hơn 50px -> Về slide trước đó
  else if (movedBy > 50 && currentPos !== 0) {
    goToSlide(currentSlide - 1);
  }
  // Nếu click nhẹ (không kéo) hoặc kéo quá ít -> Xử lý click link
  else if (movedBy > -50 && movedBy < 50) {
    // Lấy link đích của slide hiện tại và chuyển trang
    const targetLink = slides[currentSlide]
      .querySelector("a")
      .getAttribute("href");
    if (targetLink && targetLink !== "#") {
      window.location.href = targetLink;
    }
  }

  // Reset lại tọa độ
  startPos = 0;
  currentPos = 0;
  resetAutoSlide(); // Chạy lại bộ đếm tự động
}

// Gắn sự kiện cho ngón tay (Mobile)
sliderWrapper.addEventListener("touchstart", touchStart);
sliderWrapper.addEventListener("touchmove", touchMove);
sliderWrapper.addEventListener("touchend", touchEnd);

// Gắn sự kiện cho chuột (Desktop)
sliderWrapper.addEventListener("mousedown", touchStart);
sliderWrapper.addEventListener("mousemove", touchMove);
sliderWrapper.addEventListener("mouseup", touchEnd);
sliderWrapper.addEventListener("mouseleave", touchEnd); // Trường hợp kéo chuột lố ra ngoài khung
// =========================================
// JS XỬ LÝ PRODUCT SLIDER
// =========================================
document.addEventListener("DOMContentLoaded", () => {
  const wrapper = document.getElementById("productWrapper");
  const next = document.querySelector(".next");
  const prev = document.querySelector(".prev");

  if (wrapper && next && prev) {
    // Lấy danh sách TẤT CẢ các thẻ sản phẩm để tính vị trí
    const cards = wrapper.querySelectorAll(".product-card");

    // Hàm tìm thẻ đang hiển thị đầu tiên bên trái màn hình
    const getFirstVisibleCardIndex = () => {
      let index = 0;
      let minDistance = Infinity;
      const wrapperRect = wrapper.getBoundingClientRect();

      cards.forEach((card, i) => {
        const cardRect = card.getBoundingClientRect();
        // Tính khoảng cách từ mép trái của thẻ đến mép trái của wrapper
        const distance = Math.abs(cardRect.left - wrapperRect.left);
        if (distance < minDistance) {
          minDistance = distance;
          index = i;
        }
      });
      return index;
    };

    // =========================================
    // XỬ LÝ NÚT PHẢI (NEXT) - LƯỚT TỪNG LẦN
    // =========================================
    next.addEventListener("click", () => {
      // Tìm vị trí hiện tại và nhảy tới thẻ tiếp theo
      const currentIndex = getFirstVisibleCardIndex();
      const nextIndex = currentIndex + 1;

      // Nếu còn sản phẩm phía sau
      if (nextIndex < cards.length) {
        // Bắt thẻ tiếp theo cuộn vào đúng tầm nhìn (start = sát lề trái)
        cards[nextIndex].scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "start",
        });
      } else {
        // Nếu đã đến cuối, quay lại sản phẩm đầu tiên
        cards[0].scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "start",
        });
      }
    });

    // =========================================
    // XỬ LÝ NÚT TRÁI (PREV) - LƯỚT TỪNG LẦN
    // =========================================
    prev.addEventListener("click", () => {
      const currentIndex = getFirstVisibleCardIndex();
      const prevIndex = currentIndex - 1;

      // Nếu còn sản phẩm phía trước
      if (prevIndex >= 0) {
        cards[prevIndex].scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "start",
        });
      } else {
        // Nếu đã ở đầu, nhảy đến sản phẩm cuối cùng
        cards[cards.length - 1].scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "start",
        });
      }
    });
  }
});
