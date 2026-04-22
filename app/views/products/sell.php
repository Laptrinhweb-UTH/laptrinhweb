<?php
$status = $_GET['status'] ?? '';
$message = trim((string) ($_GET['message'] ?? ''));
$noticeClass = $status === 'success' ? 'alert-success' : 'alert-error';
include __DIR__ . '/../layouts/header.php';
?>

<main class="sell-container">
    <div class="sell-wrapper">
        
        <aside class="sell-sidebar">
            <div class="sidebar-block guide-block">
                <h3 class="sidebar-title"><i class="fas fa-tasks"></i> Tiến trình</h3>
                <ul class="step-list">
                    <li class="step-item active"><span class="step-dot"></span> Thông tin cơ bản</li>
                    <li class="step-item"><span class="step-dot"></span> Tình trạng xe</li>
                    <li class="step-item"><span class="step-dot"></span> Địa chỉ xem xe</li>
                    <li class="step-item"><span class="step-dot"></span> Hình ảnh & Mô tả</li>
                </ul>
            </div>

            <div class="sidebar-block tips-block">
                <h4 class="sidebar-title"><i class="fas fa-lightbulb text-warning"></i> Mẹo bán nhanh</h4>
                <ul class="tips-list">
                    <li>Chụp ảnh ngoài trời, đủ sáng.</li>
                    <li>Chụp rõ các vết xước (nếu có) để tăng độ uy tín.</li>
                    <li>Tham khảo giá các xe tương tự trên SpinBike trước khi định giá.</li>
                </ul>
            </div>
        </aside>

        <div class="sell-main">
            <div class="sell-header">
                <div>
                    <h1 class="page-title">Đăng bán xe đạp</h1>
                    <p class="page-subtitle">Nhập thông tin chi tiết để tìm chủ mới cho chiếc xe của bạn</p>
                </div>
                <div class="badge-free">Miễn phí đăng tin</div>
            </div>

            <?php if ($message !== ''): ?>
            <div class="alert-box <?php echo $noticeClass; ?>" id="alertBox">
                <i class="fas fa-<?php echo $status === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
                <button class="btn-close-alert" onclick="document.getElementById('alertBox').style.display='none'">×</button>
            </div>
            <?php endif; ?>

            <form id="sellBikeForm" action="<?php echo route_url('sell.process'); ?>" method="POST" enctype="multipart/form-data" class="sell-form">
                
                <div class="form-section">
                    <h2 class="section-title">1. Thông tin cơ bản</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Tên xe <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="VD: Trek Domane SL 5 đời 2022" required />
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Hãng xe <span class="required">*</span></label>
                            <select id="brandSelect" class="form-control" onchange="toggleCustomBrand()" required>
                                <option value="">-- Chọn hãng --</option>
                                <option value="Giant">Giant</option>
                                <option value="Trek">Trek</option>
                                <option value="Trinx">Trinx</option>
                                <option value="Asama">Asama</option>
                                <option value="Martin 107">Martin 107</option>
                                <option value="Thống Nhất">Thống Nhất</option>
                                <option value="OTHER">Hãng khác...</option>
                            </select>
                            <input type="text" id="customBrandInput" class="form-control mt-2" placeholder="Nhập tên hãng xe..." style="display: none;" />
                            <input type="hidden" name="brand" id="finalBrandValue" />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mức giá (VNĐ) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="input-icon">₫</span>
                                <input type="text" id="priceDisplay" class="form-control pl-40" placeholder="0" required oninput="formatPrice(this)" />
                                <input type="hidden" name="price" id="priceReal" required />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">2. Tình trạng xe</h2>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Loại xe</label>
                            <select name="bike_type" class="form-control">
                                <option value="">-- Chọn loại --</option>
                                <option value="Road">Road Bike</option>
                                <option value="MTB">Mountain Bike</option>
                                <option value="Gravel">Gravel Bike</option>
                                <option value="Touring">Touring</option>
                                <option value="Fixed">Fixed Gear</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Size khung</label>
                            <select name="frame_size" class="form-control">
                                <option value="">-- Chọn size --</option>
                                <option value="XS">XS (< 50cm)</option>
                                <option value="S">S (50-54cm)</option>
                                <option value="M">M (54-58cm)</option>
                                <option value="L">L (58-62cm)</option>
                                <option value="XL">XL (> 62cm)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Độ mới (%)</label>
                            <select name="condition_percent" class="form-control">
                                <option value="">-- Chọn độ mới --</option>
                                <option value="99">99% - Như mới</option>
                                <option value="95">95% - Rất tốt</option>
                                <option value="90">90% - Tốt</option>
                                <option value="85">85% - Khá</option>
                                <option value="80">80% - Trung bình</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">3. Địa chỉ xem xe</h2>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Tỉnh/Thành phố <span class="required">*</span></label>
                            <select id="province" class="form-control" required><option value="">-- Chọn --</option></select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quận/Huyện <span class="required">*</span></label>
                            <select id="district" class="form-control" required disabled><option value="">-- Chọn --</option></select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phường/Xã <span class="required">*</span></label>
                            <select id="ward" class="form-control" required disabled><option value="">-- Chọn --</option></select>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label">Số nhà, tên đường <span class="required">*</span></label>
                        <input type="text" id="street" class="form-control" placeholder="VD: 123 Nguyễn Văn Cừ..." required />
                        <input type="hidden" name="location" id="fullLocation" />
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">4. Hình ảnh & Mô tả</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Hình ảnh xe <span class="required">*</span> <span class="text-muted fw-normal">(Tối đa 5MB/ảnh)</span></label>
                        <div class="upload-zone" onclick="document.getElementById('bikeImages').click()">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p>Nhấn hoặc kéo thả ảnh vào đây để tải lên</p>
                            <input type="file" id="bikeImages" name="images[]" multiple accept="image/*" style="display: none;" required onchange="previewImages(event)">
                        </div>
                        <div id="imagePreviewContainer" class="image-preview-grid"></div>
                    </div>

                    <div class="form-group mt-4">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Tình trạng hoạt động, lý do bán, đồ chơi tặng kèm..." oninput="updateCharCount(this)"></textarea>
                        <div class="char-count"><span id="charCount">0</span>/1000</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="window.history.back()">Hủy bỏ</button>
                    <button type="submit" class="btn-sell">
                        <i class="fas fa-paper-plane"></i> Đăng bán ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    /* Bảng màu và Biến */
    :root {
        --orange-primary: #F97316;
        --orange-hover: #EA580C;
        --text-main: #1F2937;
        --text-muted: #6B7280;
        --border-color: #E5E7EB;
        --bg-body: #F3F4F6;
        --bg-white: #FFFFFF;
        --radius-lg: 16px;
        --radius-md: 8px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --transition: all 0.2s ease-in-out;
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-main);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Layout chính */
    .sell-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .sell-wrapper {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .sell-wrapper {
            grid-template-columns: 1fr;
        }
        .sell-sidebar {
            display: none; /* Ẩn sidebar trên mobile cho gọn */
        }
    }

    /* Sidebar */
    .sidebar-block {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 24px;
        border: 1px solid var(--border-color);
    }

    .sidebar-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .step-list, .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .step-item {
        padding: 12px 0;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.95rem;
    }

    .step-item.active {
        color: var(--orange-primary);
        font-weight: 600;
    }

    .step-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: var(--border-color);
    }

    .step-item.active .step-dot {
        background-color: var(--orange-primary);
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.2);
    }

    .tips-list li {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 12px;
        padding-left: 16px;
        position: relative;
        line-height: 1.5;
    }
    
    .tips-list li::before {
        content: "•";
        color: #FBBF24;
        position: absolute;
        left: 0;
        font-weight: bold;
    }

    /* Main Content */
    .sell-main {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .sell-header {
        padding: 32px 40px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #FAFAFA;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .page-subtitle {
        color: var(--text-muted);
        margin: 0;
        font-size: 0.95rem;
    }

    .badge-free {
        background: rgba(249, 115, 22, 0.1);
        color: var(--orange-primary);
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid rgba(249, 115, 22, 0.2);
    }

    .sell-form {
        padding: 40px;
    }

    .form-section {
        margin-bottom: 48px;
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--bg-body);
        color: var(--text-main);
    }

    /* Form Controls */
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; }
    
    .form-group { margin-bottom: 20px; }
    
    .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .required { color: #EF4444; }
    .text-muted { color: var(--text-muted); font-size: 0.85rem; }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 1rem;
        transition: var(--transition);
        background: var(--bg-white);
        color: var(--text-main);
        outline: none;
    }

    .form-control:focus {
        border-color: var(--orange-primary);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .input-with-icon { position: relative; }
    .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-weight: 600;
    }
    .pl-40 { padding-left: 40px !important; }

    /* Upload Zone */
    .upload-zone {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-md);
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        background: #FAFAFA;
    }

    .upload-zone:hover {
        border-color: var(--orange-primary);
        background: rgba(249, 115, 22, 0.02);
    }

    .upload-icon {
        font-size: 2.5rem;
        color: var(--text-muted);
        margin-bottom: 12px;
    }

    .upload-zone p { margin: 0; color: var(--text-muted); font-size: 0.95rem; }

    .image-preview-grid {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }
    
.preview-img-wrapper {
        position: relative; /* Cần có để căn chỉnh nút X */
        width: 100px;
        height: 100px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .preview-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    /* CSS cho nút X */
    .btn-remove-img {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background-color: #EF4444;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 14px;
        font-weight: bold;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .btn-remove-img:hover {
        background-color: #DC2626;
        transform: scale(1.1);
    }

    .char-count {
        text-align: right;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 8px;
    }

    /* Buttons */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 16px;
        margin-top: 40px;
        padding-top: 24px;
        border-top: 1px solid var(--border-color);
    }

    .btn-cancel {
        padding: 12px 24px;
        background: var(--bg-body);
        color: var(--text-main);
        border: none;
        border-radius: 24px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .btn-cancel:hover { background: #E5E7EB; }

    /* Nút Đăng Bán Màu Cam Yêu Cầu */
    .btn-sell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: var(--orange-primary);
        color: var(--bg-white);
        border: none;
        padding: 12px 32px;
        border-radius: 24px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: transform var(--transition), background-color var(--transition), box-shadow var(--transition);
        box-shadow: 0 12px 24px -14px rgba(249, 115, 22, 0.55);
    }

    .btn-sell:hover {
        background-color: var(--orange-hover);
        transform: translateY(-1px);
        box-shadow: 0 16px 30px -18px rgba(234, 88, 12, 0.55);
    }

    /* Alerts */
    .alert-box {
        margin: 20px 40px 0;
        padding: 16px 20px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .alert-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
    .alert-error { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
    .btn-close-alert { margin-left: auto; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: inherit;}
    .mt-2 { margin-top: 8px; }
    .mt-3 { margin-top: 16px; }
    .mt-4 { margin-top: 24px; }
</style>

<script>
    // Xử lý ẩn hiện input nhập hãng xe khác
    function toggleCustomBrand() {
        const select = document.getElementById('brandSelect');
        const customInput = document.getElementById('customBrandInput');
        const finalValue = document.getElementById('finalBrandValue');
        
        if (select.value === 'OTHER') {
            customInput.style.display = 'block';
            customInput.required = true;
            finalValue.value = customInput.value;
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
            finalValue.value = select.value;
        }
    }

    document.getElementById('customBrandInput').addEventListener('input', function() {
        document.getElementById('finalBrandValue').value = this.value;
    });

    // Format giá tiền có dấu chấm
    function formatPrice(input) {
        let value = input.value.replace(/\D/g, '');
        if(value !== '') {
            document.getElementById('priceReal').value = value;
            input.value = new Intl.NumberFormat('vi-VN').format(value);
        } else {
            document.getElementById('priceReal').value = '';
            input.value = '';
        }
    }

    // Hiển thị trước hình ảnh tải lên
// Tạo một DataTransfer toàn cục để quản lý file ảnh
    let selectedImages = new DataTransfer();

    function previewImages(event) {
        const input = event.target;
        const newFiles = input.files;
        
        // Kiểm tra số lượng
        if (selectedImages.items.length + newFiles.length > 10) {
            alert('Bạn chỉ được tải lên tối đa 10 ảnh!');
            input.value = ''; // Reset input
            return;
        }

        // Thêm các file mới chọn vào danh sách quản lý
        for (let i = 0; i < newFiles.length; i++) {
            const file = newFiles[i];
            if (file.size > 5 * 1024 * 1024) {
                alert(`Ảnh "${file.name}" vượt quá 5MB!`);
                continue;
            }
            selectedImages.items.add(file);
        }
        
        // Cập nhật lại input file và vẽ lại giao diện
        input.files = selectedImages.files;
        renderImagePreview();
    }

    function renderImagePreview() {
        const container = document.getElementById('imagePreviewContainer');
        container.innerHTML = ''; 
        const files = selectedImages.files;

        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-img-wrapper';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="btn-remove-img" onclick="removeImage(${i})" title="Xoá ảnh này">×</button>
                `;
                container.appendChild(div);
            }
            reader.readAsDataURL(files[i]);
        }
    }

    function removeImage(index) {
        const input = document.getElementById('bikeImages');
        const dt = new DataTransfer();
        
        // Copy các file sang DataTransfer mới, ngoại trừ file bị xoá
        for (let i = 0; i < selectedImages.files.length; i++) {
            if (i !== index) {
                dt.items.add(selectedImages.files[i]);
            }
        }
        
        // Cập nhật lại danh sách quản lý và input
        selectedImages = dt;
        input.files = selectedImages.files;
        
        // Vẽ lại giao diện
        renderImagePreview();
    }
    // Đếm ký tự mô tả
    function updateCharCount(textarea) {
        const count = textarea.value.length;
        const display = document.getElementById('charCount');
        display.textContent = count;
        if(count > 1000) {
            display.style.color = '#EF4444';
        } else {
            display.style.color = 'var(--text-muted)';
        }
    }

    // ==========================================
    // TÍCH HỢP API TỈNH THÀNH VIỆT NAM
    // ==========================================
    const host = "https://provinces.open-api.vn/api/";

    // 1. Lấy danh sách Tỉnh/Thành phố khi load trang
    fetch(host + "?depth=1")
        .then(response => response.json())
        .then(data => {
            let html = '<option value="">-- Chọn Tỉnh/Thành phố --</option>';
            data.forEach(element => {
                // Lưu tên tỉnh vào thuộc tính data-name để lát nữa lấy tên thay vì lấy mã code
                html += `<option value="${element.code}" data-name="${element.name}">${element.name}</option>`;
            });
            document.getElementById('province').innerHTML = html;
        });

    // 2. Khi chọn Tỉnh -> Load danh sách Quận/Huyện
    document.getElementById('province').addEventListener('change', function() {
        const provinceCode = this.value;
        const districtSelect = document.getElementById('district');
        const wardSelect = document.getElementById('ward');
        
        // Reset Phường/Xã
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSelect.disabled = true;

        if (provinceCode) {
            fetch(host + "p/" + provinceCode + "?depth=2")
                .then(response => response.json())
                .then(data => {
                    let html = '<option value="">-- Chọn Quận/Huyện --</option>';
                    data.districts.forEach(element => {
                        html += `<option value="${element.code}" data-name="${element.name}">${element.name}</option>`;
                    });
                    districtSelect.innerHTML = html;
                    districtSelect.disabled = false; // Mở khóa ô Quận/Huyện
                });
        } else {
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            districtSelect.disabled = true;
        }
    });

    // 3. Khi chọn Quận/Huyện -> Load danh sách Phường/Xã
    document.getElementById('district').addEventListener('change', function() {
        const districtCode = this.value;
        const wardSelect = document.getElementById('ward');

        if (districtCode) {
            fetch(host + "d/" + districtCode + "?depth=2")
                .then(response => response.json())
                .then(data => {
                    let html = '<option value="">-- Chọn Phường/Xã --</option>';
                    data.wards.forEach(element => {
                        html += `<option value="${element.code}" data-name="${element.name}">${element.name}</option>`;
                    });
                    wardSelect.innerHTML = html;
                    wardSelect.disabled = false; // Mở khóa ô Phường/Xã
                });
        } else {
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
        }
    });

    // ==========================================
    // GHÉP ĐỊA CHỈ TRƯỚC KHI SUBMIT FORM
    // ==========================================
    document.getElementById('sellBikeForm').addEventListener('submit', function(e) {
        // Cập nhật lại giá trị Hãng xe lần cuối
        const select = document.getElementById('brandSelect');
        if(select.value !== 'OTHER') {
            document.getElementById('finalBrandValue').value = select.value;
        }
        
        // Lấy TÊN của các đơn vị hành chính thay vì mã code
        const provinceSelect = document.getElementById('province');
        const districtSelect = document.getElementById('district');
        const wardSelect = document.getElementById('ward');

        // Dùng data-name đã lưu lúc nãy để lấy chuỗi chữ (VD: "Hà Nội" thay vì số "1")
        const provinceName = provinceSelect.options[provinceSelect.selectedIndex]?.getAttribute('data-name') || '';
        const districtName = districtSelect.options[districtSelect.selectedIndex]?.getAttribute('data-name') || '';
        const wardName = wardSelect.options[wardSelect.selectedIndex]?.getAttribute('data-name') || '';
        
        const street = document.getElementById('street').value.trim();

        // Ghép thành 1 chuỗi địa chỉ hoàn chỉnh, cách nhau bởi dấu phẩy
        const fullAddressArray = [street, wardName, districtName, provinceName].filter(item => item !== '');
        document.getElementById('fullLocation').value = fullAddressArray.join(', ');
    });
</script>