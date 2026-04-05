<?php include '../config/includes/header.php'; ?>

<main class="main-content" style="max-width: 800px; margin: 40px auto; background: var(--white); padding: 40px; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid var(--border);">
    
    <div style="margin-bottom: 32px; border-bottom: 1px solid var(--border); padding-bottom: 20px;">
        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">Đăng bán xe đạp</h1>
        <p style="color: var(--text-secondary);">Vui lòng điền đầy đủ thông tin để tin đăng uy tín và dễ bán hơn.</p>
    </div>

    <form id="sellBikeForm" class="sell-form" action="process_sell.php" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label class="form-label">Tên xe <span class="text-danger">*</span></label>
            <input type="text" name="title" placeholder="VD: Trek Domane SL 5 2022" class="form-input" required />
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Loại xe <span class="text-danger">*</span></label>
                <select name="type" class="form-input" required>
                    <option value="">-- Chọn loại xe --</option>
                    <option value="Road">Road Bike (Xe cuộc)</option>
                    <option value="MTB">MTB (Xe địa hình)</option>
                    <option value="Gravel">Gravel Bike</option>
                    <option value="Fixed">Fixed Gear</option>
                    <option value="Touring">Touring</option>
                    <option value="Other">Khác</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Size khung <span class="text-danger">*</span></label>
                <select name="size" class="form-input" required>
                    <option value="">-- Chọn Size --</option>
                    <option value="XS">XS (Dưới 1m60)</option>
                    <option value="S">S (1m60 - 1m70)</option>
                    <option value="M">M (1m70 - 1m80)</option>
                    <option value="L">L (1m80 - 1m90)</option>
                    <option value="XL">XL (1m90 - 1m95)</option>
                    <option value="XXL">XXL (Trên 1m95)</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tình trạng khung <span class="text-danger">*</span></label>
                <select name="condition" class="form-input" required>
                    <option value="">-- Đánh giá --</option>
                    <option value="99">Mới 99% (Như mới, không xước)</option>
                    <option value="95">95% (Có xước dăm rất nhẹ)</option>
                    <option value="90">90% (Xước thấy rõ, không móp méo)</option>
                    <option value="80">80% (Cũ theo thời gian, tróc sơn)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tình trạng phụ tùng</label>
                <input type="text" name="parts_status" placeholder="VD: Groupset nguyên bản 105, sên mới..." class="form-input" />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Mức giá (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="price" placeholder="VD: 15000000" class="form-input" required />
            </div>
            <div class="form-group">
                <label class="form-label">Hình thức bán</label>
                <select name="negotiable" class="form-input">
                    <option value="0">Giá cố định (Không bớt)</option>
                    <option value="1">Có thương lượng</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Địa chỉ xem xe <span class="text-danger">*</span></label>
            <input type="text" name="location" placeholder="VD: Phường 12, Quận 10, TP.HCM" class="form-input" required />
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Mô tả chi tiết</label>
            <textarea name="description" rows="5" class="form-input" style="resize: vertical; font-family: inherit;" placeholder="Mô tả về lịch sử sử dụng, nâng cấp, hoặc lỗi (nếu có)..."></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Hình ảnh thực tế (Tối thiểu 1 ảnh) <span class="text-danger">*</span></label>
            <div class="upload-area" onclick="document.getElementById('bikeImages').click()">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <p style="font-weight: 600; margin-bottom: 4px;">Nhấn vào đây để tải ảnh lên</p>
                <span class="upload-hint">(Nên chụp rõ: Toàn cảnh, khung, groupset, lốp, đồng hồ...)</span>
            </div>
            <input type="file" id="bikeImages" name="images[]" multiple accept="image/*" style="display: none;" required onchange="previewImages(event)">
            <div id="imagePreviewContainer" class="image-preview-container"></div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 24px;">
            <i class="fa-solid fa-paper-plane"></i> Đăng tin bán xe
        </button>
    </form>
</main>

<?php include '../config/includes/footer.php'; ?>