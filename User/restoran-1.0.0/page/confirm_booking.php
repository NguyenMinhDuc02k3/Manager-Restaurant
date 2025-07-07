<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Đảm bảo session được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session
if (!isset($_SESSION['debug'])) {
    $_SESSION['debug'] = [];
}
$_SESSION['debug'][] = [
    'time' => date('Y-m-d H:i:s'),
    'page' => 'confirm_booking.php',
    'session_booking' => isset($_SESSION['booking']) ? $_SESSION['booking'] : 'not_set',
    'session_monan' => isset($_SESSION['selected_monan']) ? $_SESSION['selected_monan'] : 'not_set',
    'session_id' => session_id(),
    'request' => $_SERVER['REQUEST_METHOD'],
    'get' => $_GET,
    'post' => $_POST
];

// Kiểm tra session booking
if (!isset($_SESSION['booking'])) {
    header('Location: index.php?page=booking');
    exit;
}

require_once 'class/clskvban.php';
require_once 'class/clsban.php';

// Khởi tạo các lớp
$khuVuc = new KhuVucBan();
$ban = new clsBan();

// Lấy thông tin khu vực và bàn
$khuVucTen = $khuVuc->getTenKhuVuc($_SESSION['booking']['khuvuc']);
$banInfo = $ban->getBanById($_SESSION['booking']['maban']);

// Tổng tiền tạm tính
$totalTemp = 0;
$selectedMonAn = isset($_SESSION['selected_monan']) ? $_SESSION['selected_monan'] : [];
foreach ($selectedMonAn as $mon) {
    $totalTemp += $mon['DonGia'] * $mon['soluong'];
}

// Thời gian giữ bàn (3 phút)
$bookingTime = isset($_SESSION['booking']['booking_time']) ? $_SESSION['booking']['booking_time'] : time();
$timeLeft = max(0, 180 - (time() - $bookingTime)); // 180 giây = 3 phút
if ($timeLeft <= 0) {
    unset($_SESSION['booking']);
    unset($_SESSION['selected_monan']);
    header('Location: index.php?page=booking');
    exit;
}
$_SESSION['booking']['booking_time'] = $bookingTime;

// Lưu tổng tiền cuối cùng vào session để sử dụng ở customer_info.php
$_SESSION['booking']['total_final'] = $totalTemp;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt bàn - Restoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .container { max-width: 1200px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table th, .summary-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .error, .success { font-size: 14px; margin-top: 5px; }
        .error { color: red; }
        .success { color: green; }
        .btn-back { background-color: #6c757d; }
        .btn-back:hover { background-color: #5a6268; }
        .btn-primary {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        .btn-primary:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
        }
        .alert { margin-bottom: 20px; }
        .timer { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Đang tải...</span>
            </div>
        </div>

        <!-- Hero Header -->
        <div class="container-xxl py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">Xác nhận đặt bàn</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php?page=trangchu" class="text-warning">Trang Chủ</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-warning">Trang</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Xác nhận</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Nội dung xác nhận -->
        <div class="container my-5">
            <?php if ($timeLeft > 0): ?>
                <p class="timer">Bàn sẽ được giữ trong <span id="timer"><?php echo $timeLeft; ?></span> giây nữa.</p>
            <?php endif; ?>
            <?php if (empty($selectedMonAn)): ?>
                <div class="alert alert-warning">Bạn chưa chọn món ăn. Bạn có thể tiếp tục hoặc quay lại để chọn món.</div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6">
                    <h3>Thông tin đặt bàn</h3>
                    <table class="summary-table">
                        <tr>
                            <th>Số lượng khách</th>
                            <td><?php echo htmlspecialchars($_SESSION['booking']['people_count']); ?> người</td>
                        </tr>
                        <tr>
                            <th>Ngày giờ</th>
                            <td><?php echo htmlspecialchars($_SESSION['booking']['datetime']); ?></td>
                        </tr>
                        <tr>
                            <th>Khu vực</th>
                            <td><?php echo htmlspecialchars($khuVucTen); ?></td>
                        </tr>
                        <tr>
                            <th>Bàn</th>
                            <td><?php echo $banInfo ? htmlspecialchars($banInfo['SoBan']) : 'Không xác định'; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h3>Tóm tắt đơn hàng</h3>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Món</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedMonAn as $mon): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mon['tenmonan']); ?></td>
                                    <td><?php echo $mon['soluong']; ?></td>
                                    <td><?php echo number_format($mon['DonGia'] * $mon['soluong']); ?> VND</td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($selectedMonAn)): ?>
                                <tr><td colspan="3">Chưa có món nào được chọn.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <p class="mt-3">Tổng tiền: <strong><?php echo number_format($totalTemp); ?> VND</strong></p>
                </div>
            </div>
            <div class="mt-4">
                <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='index.php?page=book_menu'">Quay lại</button>
                <button type="button" class="btn btn-warning" onclick="window.location.href='index.php?page=customer_info'">Tiếp theo</button>
            </div>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script>
        // Đếm ngược thời gian giữ bàn
        let timeLeft = <?php echo $timeLeft; ?>;
        const timerElement = document.getElementById('timer');
        if (timerElement) {
            const timerInterval = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Thời gian giữ bàn đã hết. Vui lòng đặt lại.');
                    window.location.href = 'index.php?page=booking';
                }
            }, 1000);
        }
    </script>
</body>
</html>