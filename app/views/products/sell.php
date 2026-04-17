<?php include __DIR__ . '/../layouts/header.php'; ?>

<main class="main-content" style="max-width: 800px; margin: 40px auto; background: var(--white); padding: 40px; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid var(--border);">
    
    <div style="margin-bottom: 32px; border-bottom: 1px solid var(--border); padding-bottom: 20px;">
        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">Đăng bán xe đạp</h1>
        <p style="color: var(--text-secondary);">Vui lòng điền thông tin để đăng bán xe của bạn.</p>
    </div>

    <form id="sellBikeForm" class="sell-form" action="<?php echo asset_url('process_sell.php'); ?>" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label class="form-label">Tên xe <span class="text-danger">*</span></label>
            <input type="text" name="title" placeholder="VD: Trek Domane SL 5 2022" class="form-input" required />
        </div>

        <div class="form-row">
          <div class="form-group">
    <label class="form-label">Hãng xe <span class="text-danger">*</span></label>
    
    <select id="brandSelect" class="form-input" onchange="toggleCustomBrand()" required>
        <option value="">-- Chọn hãng xe --</option>
        <option value="Giant">Giant</option>
        <option value="Trek">Trek</option>
        <option value="Trinx">Trinx</option>
        <option value="Asama">Asama</option>
        <option value="Martin 107">Martin 107</option>
        <option value="Thống Nhất">Thống Nhất</option>
        <option value="OTHER">Hãng khác</option>
    </select>

    <input type="text" id="customBrandInput" placeholder="Nhập tên hãng xe của bạn..." class="form-input" style="display: none; margin-top: 8px;" />
    
    <input type="hidden" name="brand" id="finalBrandValue" />
</div>
            
            <div class="form-group">
                <label class="form-label">Mức giá (VNĐ) <span class="text-danger">*</span></label>
                <input type="text" id="priceDisplay" placeholder="VD: 2.500.000" class="form-input" required />
                <input type="hidden" name="price" id="priceReal" required />
            </div>
        </div> 
    <div class="form-group">
            <label class="form-label">Địa chỉ xem xe <span class="text-danger">*</span></label>
            
            <div class="form-row" style="margin-bottom: 12px;">
                <select id="province" class="form-input" required>
                    <option value="">-- Chọn Tỉnh/Thành --</option>
                </select>
                <select id="district" class="form-input" required disabled>
                    <option value="">-- Chọn Quận/Huyện --</option>
                </select>
                <select id="ward" class="form-input" required disabled>
                    <option value="">-- Chọn Phường/Xã --</option>
                </select>
            </div>
            
            <input type="text" id="street" placeholder="Số nhà, tên đường..." class="form-input" required />
            
            <input type="hidden" name="location" id="fullLocation" />
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Mô tả chi tiết</label>
            <textarea name="description" rows="5" class="form-input" style="resize: vertical; font-family: inherit;" placeholder="Mô tả tình trạng xe, thời gian sử dụng..."></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Hình ảnh thực tế (Tối thiểu 1 ảnh) <span class="text-danger">*</span></label>
            <div class="upload-area" onclick="document.getElementById('bikeImages').click()">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <p style="font-weight: 600; margin-bottom: 4px;">Nhấn vào đây để tải ảnh lên</p>
            </div>
            <input type="file" id="bikeImages" name="images[]" multiple accept="image/*" style="display: none;" required onchange="previewImages(event)">
            <div id="imagePreviewContainer" class="image-preview-container"></div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 24px;">
            <i class="fa-solid fa-paper-plane"></i> Đăng tin bán xe
        </button>
    </form>
</main>

<script>
const priceDisplay = document.getElementById('priceDisplay');
const priceReal = document.getElementById('priceReal');

// Khi nhập: chỉ giữ số
priceDisplay.addEventListener('input', function () {
    let rawValue = this.value.replace(/\D/g, '');
    priceReal.value = rawValue;
    this.value = rawValue; // không format
});

// Khi rời ô input mới format
priceDisplay.addEventListener('blur', function () {
    let rawValue = priceReal.value;
    this.value = rawValue ? new Intl.NumberFormat('vi-VN').format(rawValue) : '';
});
// --- XỬ LÝ API ĐỊA CHỈ HÀNH CHÍNH VN ---
    const provinceSel = document.getElementById('province');
    const districtSel = document.getElementById('district');
    const wardSel = document.getElementById('ward');
    const streetInput = document.getElementById('street');
    const fullLocationInput = document.getElementById('fullLocation');

    // 1. Gọi API lấy danh sách Tỉnh/Thành phố
    fetch('https://provinces.open-api.vn/api/?depth=1')
        .then(response => response.json())
        .then(data => {
            data.forEach(province => {
                provinceSel.innerHTML += `<option value="${province.code}">${province.name}</option>`;
            });
        });

    // 2. Khi chọn Tỉnh/Thành -> Gọi API lấy Quận/Huyện
    provinceSel.addEventListener('change', function() {
        districtSel.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
        wardSel.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSel.disabled = true;

        if (this.value) {
            fetch(`https://provinces.open-api.vn/api/p/${this.value}?depth=2`)
                .then(response => response.json())
                .then(data => {
                    data.districts.forEach(district => {
                        districtSel.innerHTML += `<option value="${district.code}">${district.name}</option>`;
                    });
                    districtSel.disabled = false;
                });
        } else {
            districtSel.disabled = true;
        }
        updateFullLocation(); // Cập nhật lại chuỗi địa chỉ
    });

    // 3. Khi chọn Quận/Huyện -> Gọi API lấy Phường/Xã
    districtSel.addEventListener('change', function() {
        wardSel.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        
        if (this.value) {
            fetch(`https://provinces.open-api.vn/api/d/${this.value}?depth=2`)
                .then(response => response.json())
                .then(data => {
                    data.wards.forEach(ward => {
                        wardSel.innerHTML += `<option value="${ward.code}">${ward.name}</option>`;
                    });
                    wardSel.disabled = false;
                });
        } else {
            wardSel.disabled = true;
        }
        updateFullLocation();
    });

    // 4. Khi chọn Phường/Xã hoặc gõ tên đường -> Gộp vào ô hidden
    wardSel.addEventListener('change', updateFullLocation);
    streetInput.addEventListener('input', updateFullLocation);

    // Hàm gộp địa chỉ
    function updateFullLocation() {
        let pName = provinceSel.options[provinceSel.selectedIndex]?.text || '';
        let dName = districtSel.options[districtSel.selectedIndex]?.text || '';
        let wName = wardSel.options[wardSel.selectedIndex]?.text || '';
        let street = streetInput.value.trim();

        // Chỉ ghép chữ khi người dùng đã chọn thực sự (bỏ qua dòng "-- Chọn...")
        let addressParts = [];
        if (street) addressParts.push(street);
        if (wardSel.value) addressParts.push(wName);
        if (districtSel.value) addressParts.push(dName);
        if (provinceSel.value) addressParts.push(pName);

        // Gán vào thẻ hidden để gửi form (Ví dụ: "Số 10 Lê Lợi, Phường Bến Nghé, Quận 1, Thành phố Hồ Chí Minh")
        fullLocationInput.value = addressParts.join(', ');
    }
    function toggleCustomBrand() {
    const select = document.getElementById('brandSelect');
    const customInput = document.getElementById('customBrandInput');
    const finalValue = document.getElementById('finalBrandValue');

    if (select.value === 'OTHER') {
        // Hiện ô nhập tay, ẩn required ở select, ép required ở ô nhập tay
        customInput.style.display = 'block';
        customInput.required = true;
        select.removeAttribute('name'); // Tránh gửi nhầm chữ "OTHER" về backend
        finalValue.value = customInput.value;
    } else {
        // Ẩn ô nhập tay, lấy trực tiếp giá trị từ select
        customInput.style.display = 'none';
        customInput.required = false;
        customInput.value = ''; // Xóa trắng ô tự gõ
        finalValue.value = select.value;
    }
}
// Tạo một mảng để lưu trữ cộng dồn các file ảnh
let allSelectedFiles = [];

// Hàm này chạy mỗi khi người dùng bấm tải thêm ảnh
function previewImages(event) {
    const newFiles = event.target.files;
    
    // Đẩy các file mới chọn vào mảng dùng chung
    for (let i = 0; i < newFiles.length; i++) {
        allSelectedFiles.push(newFiles[i]);
    }
    
    // Gọi hàm cập nhật và vẽ lại ảnh
    updateFileInputAndRender();
}

// Hàm vẽ ảnh ra màn hình và đóng gói file gửi đi
function updateFileInputAndRender() {
    const previewContainer = document.getElementById('imagePreviewContainer');
    const fileInput = document.getElementById('bikeImages');
    
    // 1. Dùng DataTransfer để gói tất cả ảnh trong mảng nhét lại vào thẻ <input>
    // Nhờ vậy khi bấm Submit, Form sẽ gửi đi đầy đủ ảnh
    const dataTransfer = new DataTransfer();
    allSelectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;

    // 2. Xóa giao diện cũ để vẽ lại từ đầu
    previewContainer.innerHTML = '';

    // 3. Chạy vòng lặp để vẽ từng ảnh trong mảng
    allSelectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Tạo khung chứa ảnh
            const imgDiv = document.createElement('div');
            imgDiv.style.display = 'inline-block';
            imgDiv.style.margin = '10px 10px 10px 0';
            imgDiv.style.position = 'relative';

            // Tạo thẻ ảnh
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            img.style.border = '1px solid var(--border)';

            // Tạo nút Xóa (Dấu X màu đỏ)
            const removeBtn = document.createElement('div');
            removeBtn.innerHTML = '×';
            removeBtn.style.position = 'absolute';
            removeBtn.style.top = '-8px';
            removeBtn.style.right = '-8px';
            removeBtn.style.background = '#dc3545';
            removeBtn.style.color = 'white';
            removeBtn.style.borderRadius = '50%';
            removeBtn.style.width = '22px';
            removeBtn.style.height = '22px';
            removeBtn.style.display = 'flex';
            removeBtn.style.justifyContent = 'center';
            removeBtn.style.alignItems = 'center';
            removeBtn.style.cursor = 'pointer';
            removeBtn.style.fontWeight = 'bold';
            removeBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';

            // Xử lý sự kiện khi bấm nút Xóa
            removeBtn.onclick = function() {
                allSelectedFiles.splice(index, 1); // Xóa file khỏi mảng
                updateFileInputAndRender(); // Vẽ lại giao diện
            };

            // Ghép nối các thẻ lại với nhau
            imgDiv.appendChild(img);
            imgDiv.appendChild(removeBtn);
            previewContainer.appendChild(imgDiv);
        }
        reader.readAsDataURL(file);
    });
}
// Lắng nghe lúc người dùng tự gõ chữ vào ô "Hãng khác" để cập nhật vào thẻ hidden
document.getElementById('customBrandInput').addEventListener('input', function() {
    document.getElementById('finalBrandValue').value = this.value;
});

// Lắng nghe khi người dùng đổi lựa chọn select để cập nhật ngay vào thẻ hidden
document.getElementById('brandSelect').addEventListener('change', function() {
    if (this.value !== 'OTHER') {
        document.getElementById('finalBrandValue').value = this.value;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
