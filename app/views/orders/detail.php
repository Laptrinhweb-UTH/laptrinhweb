<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 900px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">Chi tiết đơn hàng #999</h3>
        <span class="badge bg-primary text-white p-2 px-3 rounded-pill fs-6">Đang giao hàng</span>
    </div>

    <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
        <div class="order-timeline">
            <div class="timeline-step active">
                <div class="timeline-icon"><i class="fa-solid fa-check"></i></div>
                <div class="timeline-text">Đã đặt hàng</div>
            </div>
            <div class="timeline-step active">
                <div class="timeline-icon"><i class="fa-solid fa-wallet"></i></div>
                <div class="timeline-text">Đã thanh toán</div>
            </div>
            <div class="timeline-step current">
                <div class="timeline-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div class="timeline-text">Đang giao xe</div>
            </div>
            <div class="timeline-step">
                <div class="timeline-icon"><i class="fa-solid fa-box-open"></i></div>
                <div class="timeline-text">Đã nhận</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-size: 20px;">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-primary mb-1">SpinBike đang giữ 3.000.000 đ</h5>
                    <p class="text-muted mb-3" style="font-size: 14px;">Khoản tiền này sẽ chỉ được chuyển cho người bán khi bạn xác nhận đã nhận xe an toàn.</p>
                    
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="d-flex justify-content-between mb-2" style="font-size: 14px;">
                            <span class="text-muted">Tổng tiền bạn đã trả:</span>
                            <span class="fw-bold">3.000.000 đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 14px;">
                            <span class="text-danger">Phí sàn (Trừ vào người bán - 5%):</span>
                            <span>- 150.000 đ</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between" style="font-size: 15px;">
                            <span class="text-success fw-bold">Người bán sẽ nhận:</span>
                            <span class="fw-bold text-success">2.850.000 đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3 mb-5">
        <button class="btn btn-success flex-grow-1 py-3 rounded-3 fw-bold fs-6 shadow-sm" onclick="confirmReceived()">
            <i class="fa-solid fa-check-circle me-2"></i> TÔI ĐÃ NHẬN ĐƯỢC XE
        </button>
        <button class="btn btn-outline-danger py-3 px-4 rounded-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#disputeModal">
            <i class="fa-solid fa-triangle-exclamation"></i> Gửi khiếu nại
        </button>
    </div>

</div>

<div class="modal fade" id="disputeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Yêu cầu Hoàn tiền / Khiếu nại</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="font-size: 14px;">Hệ thống sẽ đóng băng số tiền này. Quản trị viên SpinBike sẽ vào cuộc xử lý.</p>
        <form>
            <div class="mb-3">
                <label class="form-label fw-bold">Lý do khiếu nại</label>
                <select class="form-select rounded-3">
                    <option>Xe bị xước xát/hỏng hóc so với mô tả</option>
                    <option>Người bán giao sai xe</option>
                    <option>Chưa nhận được xe quá thời hạn</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Mô tả chi tiết</label>
                <textarea class="form-control rounded-3" rows="3" placeholder="Vui lòng mô tả rõ vấn đề bạn gặp phải..."></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Hình ảnh bằng chứng (Tùy chọn)</label>
                <input type="file" class="form-control rounded-3" multiple>
            </div>
            <button type="submit" class="btn btn-danger w-100 py-2 rounded-3 fw-bold">Gửi khiếu nại lên Admin</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
    function confirmReceived() {
        if(confirm('Xác nhận: Bạn đã kiểm tra xe và đồng ý giải phóng 3.000.000đ cho người bán? Hành động này không thể hoàn tác.')) {
            // Chỗ này gọi AJAX tới file PHP xử lý Escrow mà anh em mình bàn ở tin nhắn trước
            alert('Đã giải phóng tiền cho người bán!');
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>