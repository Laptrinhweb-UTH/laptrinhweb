// ==================== RENDER SẢN PHẨM ====================
function renderProducts(filtered) {
  const grid = document.getElementById("productGrid");
  grid.innerHTML = "";

  filtered.forEach((p) => {
    const isLiked = wishlist.includes(p.id);
    const verifiedHTML = p.verified
      ? `<i class="fa-solid fa-circle-check product-verified"></i>`
      : "";

    const html = `
            <div class="product-card" onclick="showDetail(${p.id})">
                <div class="product-image-container">
                    <img src="${p.images[0]}" class="product-image" alt="${p.title}">
                    <button class="product-wishlist-btn" onclick="event.stopImmediatePropagation(); toggleWishlist(${p.id});">
                        <i class="fa-solid fa-heart heart ${isLiked ? "liked" : ""}"></i>
                    </button>
                </div>
                <div class="product-info">
                    <div class="product-header">
                        <div class="product-title">${p.title}</div>
                        ${verifiedHTML}
                    </div>
                    <p class="product-meta">${p.brand} • Size ${p.size} • ${p.groupset}</p>
                    <div class="product-footer">
                        <span class="product-price">${(p.price / 1000000).toFixed(1)}tr</span>
                        <span class="product-condition">${p.condition}</span>
                    </div>
                </div>
            </div>`;
    grid.innerHTML += html;
  });

  document.getElementById("resultCount").textContent =
    `${filtered.length} sản phẩm`;
}

// ==================== FILTER LIVE ====================
function applyFilters() {
  let filtered = products;

  // Search
  const search = document.getElementById("searchInput").value.toLowerCase();
  if (search) {
    filtered = filtered.filter(
      (p) =>
        p.title.toLowerCase().includes(search) ||
        p.brand.toLowerCase().includes(search),
    );
  }

  // Type
  const activeTypes = Array.from(
    document.querySelectorAll("#typeFilters button.filter-btn.active"),
  ).map((b) => b.dataset.type);
  if (activeTypes.length)
    filtered = filtered.filter((p) => activeTypes.includes(p.type));

  // Size
  const size = document.getElementById("sizeFilter").value;
  if (size) filtered = filtered.filter((p) => p.size === size);

  // Price
  const minP =
    parseFloat(document.getElementById("priceMin").value) * 1000000 || 0;
  const maxP =
    parseFloat(document.getElementById("priceMax").value) * 1000000 || Infinity;
  filtered = filtered.filter((p) => p.price >= minP && p.price <= maxP);

  // Groupset
  const gs = document.getElementById("groupsetFilter").value;
  if (gs) filtered = filtered.filter((p) => p.groupset === gs);

  // Condition
  const activeCond = Array.from(
    document.querySelectorAll("#conditionFilters button.condition-btn.active"),
  ).map((b) => b.dataset.cond);
  if (activeCond.length)
    filtered = filtered.filter((p) => activeCond.includes(p.condition));

  // Sort
  const sortMode = document.getElementById("sortFilter").value;
  if (sortMode === "price-low") filtered.sort((a, b) => a.price - b.price);
  if (sortMode === "price-high") filtered.sort((a, b) => b.price - a.price);

  renderProducts(filtered);
}

// Toggle filter buttons
function toggleFilter(btn) {
  btn.classList.toggle("active");
  applyFilters();
}

function toggleCondition(btn) {
  btn.classList.toggle("active");
  applyFilters();
}

// ==================== WISHLIST ====================
function toggleWishlist(id) {
  if (wishlist.includes(id)) {
    wishlist = wishlist.filter((i) => i !== id);
  } else {
    wishlist.push(id);
  }
  localStorage.setItem("wishlist", JSON.stringify(wishlist));
  applyFilters();
}

function toggleWishlistFromModal() {
  if (!currentProduct) return;
  toggleWishlist(currentProduct.id);
  hideDetailModal();
}

// ==================== MODAL ====================
let currentProduct = null;

function showDetail(id) {
  currentProduct = products.find((p) => p.id === id);
  if (!currentProduct) return;

  document.getElementById("detailModal").classList.remove("hidden");

  document.getElementById("modalTitle").textContent = currentProduct.title;
  document.getElementById("modalSubtitle").textContent =
    `${currentProduct.brand} • ${currentProduct.type}`;
  document.getElementById("modalPrice").innerHTML =
    `${(currentProduct.price / 1000000).toFixed(1)} <span style="font-size: 20px;">triệu</span>`;
  document.getElementById("modalVerified").innerHTML = currentProduct.verified
    ? `<i class="fa-solid fa-check-circle"></i> Verified Seller`
    : "";

  document.getElementById("modalSize").textContent = currentProduct.size;
  document.getElementById("modalGroupset").textContent =
    currentProduct.groupset;
  document.getElementById("modalCondition").textContent =
    currentProduct.condition;
  document.getElementById("modalYear").textContent = currentProduct.year;
  document.getElementById("modalLocation").textContent =
    currentProduct.location;
  document.getElementById("modalDesc").textContent = currentProduct.desc;

  document.getElementById("modalMainImage").innerHTML =
    `<img src="${currentProduct.images[0]}" alt="">`;
  document.getElementById("thumb0").style.backgroundImage =
    `url('${currentProduct.images[0]}')`;
  document.getElementById("thumb1").style.backgroundImage =
    `url('${currentProduct.images[1]}')`;

  // Update wishlist heart
  const heart = document.querySelector(".modal-info .heart");
  if (wishlist.includes(currentProduct.id)) {
    heart.classList.add("liked");
  } else {
    heart.classList.remove("liked");
  }
}

function changeModalImage(i) {
  if (!currentProduct) return;
  document.getElementById("modalMainImage").innerHTML =
    `<img src="${currentProduct.images[i]}" alt="">`;
}

function hideDetailModal() {
  document.getElementById("detailModal").classList.add("hidden");
}

function fakeChat() {
  hideDetailModal();
  setTimeout(
    () =>
      alert(
        "💬 Chat đã mở!\nNgười bán: Chào bạn! Xe em còn rất tốt, bạn muốn gặp xem trực tiếp không?",
      ),
    400,
  );
}

// ==================== ĐĂNG BÁN ====================
function showSellModal() {
  document.getElementById("sellModal").classList.remove("hidden");
}

// Hàm hiển thị ảnh xem trước khi upload
function previewImages(event) {
  const container = document.getElementById("imagePreviewContainer");
  container.innerHTML = ""; // Xóa ảnh cũ nếu người dùng chọn lại

  const files = event.target.files;

  // Duyệt qua từng file ảnh được chọn
  for (let i = 0; i < files.length; i++) {
    const file = files[i];

    // Chỉ xử lý file ảnh
    if (!file.type.startsWith("image/")) continue;

    const reader = new FileReader();
    reader.onload = function (e) {
      // Tạo thẻ img và nhúng vào container
      const img = document.createElement("img");
      img.src = e.target.result;
      img.className = "img-preview";
      container.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
}
// Hàm mở Modal Đăng Bán
function showSellModal() {
  const sellModal = document.getElementById("sellModal");
  if (sellModal) {
    sellModal.classList.remove("hidden");
  }
}

// Hàm đóng Modal Đăng Bán
function hideSellModal() {
  const sellModal = document.getElementById("sellModal");
  if (sellModal) {
    sellModal.classList.add("hidden");
  }
}
// Chặn form submit tải lại trang (chờ viết code PHP xử lý sau)
function handleSellSubmit(e) {
  e.preventDefault();
  alert(
    "Chức năng đang được hoàn thiện! Sắp tới chúng ta sẽ dùng PHP để lưu dữ liệu này vào Database.",
  );
}

// ==================== KHỞI ĐỘNG ====================
window.addEventListener("load", () => {
  renderProducts(products);

  // Live search
  document
    .getElementById("searchInput")
    .addEventListener("keyup", applyFilters);
});
