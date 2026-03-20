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
document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.getElementById("product-carousel");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");

  if (carousel && prevBtn && nextBtn) {
    // Trượt sang trái
    prevBtn.addEventListener("click", function () {
      carousel.scrollBy({ left: -260, behavior: "smooth" });
    });

    // Trượt sang phải
    nextBtn.addEventListener("click", function () {
      carousel.scrollBy({ left: 260, behavior: "smooth" });
    });
  }
});
/* ============================================== */
/* JS THUẦN  SECTION SLIDER SẢN PHẨM           */
/* ============================================== */
// ==============================================
// CUSTOM SLIDER - JavaScript
// ==============================================

// Config
const config = {
  itemsPerView: 1,
  currentIndex: 0,
};
/* ============================================== */
/* NOTE: (VI) CAROUSEL BÊN PHẢI - chỉ hiện 3 thẻ, bấm mũi tên để trượt */
/* ============================================== */
const sliderTrack = document.querySelector(".cs-track");
const btnPrev = document.getElementById("btnPrev");
const btnNext = document.getElementById("btnNext");

const cards = document.querySelectorAll(".cs-card");
let currentIndex = 0;

function updateCarousel() {
  if (!sliderTrack || cards.length === 0) return;

  const cardWidth = cards[0].getBoundingClientRect().width;
  const gap = 12; // NOTE: (VI) phải khớp với gap trong CSS
  const step = cardWidth + gap;

  sliderTrack.style.transform = `translateX(-${currentIndex * step}px)`;

  // NOTE: (VI) giới hạn để luôn còn đúng 3 thẻ trong khung
  const maxIndex = Math.max(0, cards.length - 3);
  if (btnPrev) btnPrev.disabled = currentIndex <= 0;
  if (btnNext) btnNext.disabled = currentIndex >= maxIndex;
}

if (btnPrev) {
  btnPrev.addEventListener("click", () => {
    currentIndex = Math.max(0, currentIndex - 1);
    updateCarousel();
  });
}

if (btnNext) {
  btnNext.addEventListener("click", () => {
    const maxIndex = Math.max(0, cards.length - 3);
    currentIndex = Math.min(maxIndex, currentIndex + 1);
    updateCarousel();
  });
}

window.addEventListener("resize", updateCarousel);
updateCarousel();
