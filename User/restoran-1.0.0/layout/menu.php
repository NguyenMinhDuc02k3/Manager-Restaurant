<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ Việt Nam
?>
<div class="container-xxl position-relative p-0">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0">
        <a href="" class="navbar-brand p-0">
            <h1 class="text-primary m-0"><i class="fa fa-utensils me-3"></i>Restoran</h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="fa fa-bars"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0 pe-4">
                <a href="index.php?page=trangchu" class="nav-item nav-link active">Trang chủ</a>
                <a href="index.php?page=about" class="nav-item nav-link">Về chúng tôi</a>
                <a href="index.php?page=service" class="nav-item nav-link">Dịch vụ</a>
                <a href="index.php?page=menu" class="nav-item nav-link">Menu</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Trang</a>
                    <div class="dropdown-menu m-0">
                        <a href="index.php?page=team" class="dropdown-item">Đội Ngũ</a>
                        <a href="index.php?page=testimonial" class="dropdown-item">Đánh Giá</a>
                    </div>
                </div>
                <a href="index.php?page=contact" class="nav-item nav-link">Liên Hệ</a>
            </div>

            <!-- Nút mở Modal -->
            <button id="openModalBtn" type="button" class="btn btn-primary">
                Đặt Bàn
            </button>

            <!-- Modal Đặt bàn -->
            <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="false" data-bs-backdrop="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reservationModalLabel">Đặt bàn</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <?php
                            require_once 'class/clskvban.php';
                            $kvb = new KhuVucBan();
                            ?>
                            <form method="POST" action="index.php?page=booking">
                                <div class="row g-3">
                                    <!-- Số người -->
                                    <div class="col-md-12">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="people_count" name="people_count" placeholder="Số lượng người" required min="1">
                                            <label for="people_count">Số lượng người</label>
                                        </div>
                                    </div>
                                    <!-- Ngày giờ -->
                                    <div class="col-md-12">
                                        <div class="form-floating">
                                            <input type="datetime-local" class="form-control" id="datetime" name="datetime" required>
                                            <label for="datetime">Ngày và giờ</label>
                                        </div>
                                    </div>
                                    <!-- Khu vực -->
                                    <div class="col-md-12">
                                        <div class="form-floating">
                                            <select class="form-select" id="khuvuc" name="khuvuc" required>
                                                <option value="">-- Chọn khu vực --</option>
                                                <?php echo $kvb->selectKvban(); ?>
                                            </select>
                                            <label for="khuvuc">Khu vực</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary w-100">Tiếp tục</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- CSS Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <!-- JS Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
         // Khởi tạo Flatpickr cho input ngày giờ
         flatpickr("#datetime", {
                enableTime: true, // Bật chọn giờ
                dateFormat: "Y-m-d H:i", // Định dạng ngày giờ (năm-tháng-ngày giờ:phút)
                minDate: "today", // Chỉ cho phép chọn từ hôm nay
                time_24hr: true, // Sử dụng giờ 24h
                allowInput: true, // Cho phép nhập tay
                defaultDate: null, // Không đặt ngày mặc định
                onReady: function(selectedDates, dateStr, instance) {
                    instance.input.addEventListener("input", function(e) {
                        instance.setDate(e.target.value, true, "Y-m-d H:i");
                    });
                }
            });
            // Khởi tạo Modal
        const modalElement = document.getElementById('reservationModal');
        const myModal = new bootstrap.Modal(modalElement, {
            backdrop: false,  // TẮT lớp nền mờ
            keyboard: true
        });

        document.getElementById('openModalBtn').addEventListener('click', function () {
            myModal.show();
        });
    });
</script>

    <!-- Custom CSS -->
    <style>
        .flatpickr-calendar {
            z-index: 9999 !important; /* Đảm bảo datetimepicker nằm trên modal */
        }
        .modal-backdrop {
            display: none !important; /* Ẩn lớp nền mờ */
        }
        .modal {
            background: transparent !important; /* Làm trong suốt nền modal */
        }
    </style>
</div>