<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Bắt đầu output buffering
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'class/clskvban.php';
require_once 'class/clsban.php';
require_once 'class/clsconnect.php';
require_once 'class/clsmonan.php';
require_once 'class/clsdatban.php';

// Kiểm tra session
$requiredBookingFields = ['total_final', 'maban', 'datetime', 'people_count', 'khuvuc'];
foreach ($requiredBookingFields as $field) {
    if (!isset($_SESSION['booking'][$field])) {
        $_SESSION['error'] = 'Thông tin đặt bàn không đầy đủ. Vui lòng đặt lại.';
        header('Location: index.php?page=error');
        exit;
    }
}

$khuVuc = new KhuVucBan();
$ban = new clsBan();
$db = new connect_db();
$monAn = new clsMonAn();
$datBan = new datban();

$khuVucTen = $khuVuc->getTenKhuVuc($_SESSION['booking']['khuvuc']);
$banInfo = $ban->getBanById($_SESSION['booking']['maban']);
if (!$banInfo) {
    $_SESSION['error'] = 'Bàn không hợp lệ. Vui lòng chọn lại.';
    header('Location: index.php?page=error');
    exit;
}

$totalTemp = 0;
$selectedMonAn = isset($_SESSION['selected_monan']) ? $_SESSION['selected_monan'] : [];
if (!empty($selectedMonAn)) {
    foreach ($selectedMonAn as $mon) {
        // Xác thực idmonan
        $sql = "SELECT idmonan, DonGia FROM monan WHERE idmonan = ? AND TrangThai = 'active'";
        $monInfo = $db->xuatdulieu_prepared($sql, [(int)$mon['idmonan']]);
        if (empty($monInfo)) {
            $_SESSION['error'] = 'Món ăn không hợp lệ: ' . htmlspecialchars($mon['tenmonan']);
            header('Location: index.php?page=error');
            exit;
        }
        $totalTemp += $mon['DonGia'] * $mon['soluong'];
    }
}

$totalFinal = $_SESSION['booking']['total_final'];

// Kiểm tra timeout
$bookingTime = isset($_SESSION['booking']['booking_time']) ? $_SESSION['booking']['booking_time'] : time();
$timeLeft = max(0, 180 - (time() - $bookingTime));
if ($timeLeft <= 0) {
    unset($_SESSION['booking']);
    unset($_SESSION['selected_monan']);
    unset($_SESSION['madatban']);
    $_SESSION['error'] = 'Thời gian giữ bàn đã hết. Vui lòng đặt lại.';
    header('Location: index.php?page=error');
    exit;
}
$_SESSION['booking']['booking_time'] = $bookingTime;

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Customer Info - Form submitted');
    error_log('POST data: ' . print_r($_POST, true));

    $tenKH = trim($_POST['tenKH'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sodienthoai = trim($_POST['sodienthoai'] ?? '');

    if (empty($tenKH)) $errors[] = 'Vui lòng nhập họ tên.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Vui lòng nhập email hợp lệ.';
    if (empty($sodienthoai) || !preg_match('/^[0-9]{10}$/', $sodienthoai)) $errors[] = 'Vui lòng nhập số điện thoại 10 số.';

    if (empty($errors)) {
        try {
            error_log('Customer Info - Starting database operations');

            // Lưu thông tin khách hàng vào session
            $_SESSION['customer_info'] = [
                'tenKH' => $tenKH,
                'email' => $email,
                'sodienthoai' => $sodienthoai
            ];

            // Sử dụng phương thức saveDatBan để lưu thông tin đặt bàn
            $madatban = $datBan->saveDatBan(
                $_SESSION['booking']['maban'],
                $_SESSION['booking']['datetime'],
                $_SESSION['booking']['people_count'],
                $totalFinal,
                $tenKH,
                $email,
                $sodienthoai
            );

            if (!$madatban) {
                throw new Exception('Không thể lưu thông tin đặt bàn');
            }

            error_log('Customer Info - Inserted datban with ID: ' . $madatban);

            // Insert chitietdatban
            foreach ($selectedMonAn as $mon) {
                $sql = "INSERT INTO chitietdatban (madatban, idmonan, SoLuong, DonGia) VALUES (?, ?, ?, ?)";
                $db->tuychinh($sql, [$madatban, (int)$mon['idmonan'], (int)$mon['soluong'], (float)$mon['DonGia']]);
            }
            error_log('Customer Info - Inserted chitietdatban records');

            // Lưu madatban vào session
            $_SESSION['madatban'] = $madatban;
            error_log('Customer Info - Set session madatban: ' . $madatban);

            // Kiểm tra xem khách hàng đã tồn tại trong cơ sở dữ liệu chưa
            $sql = "SELECT * FROM khachhang WHERE email = ? OR sodienthoai = ?";
            $existingCustomer = $db->xuatdulieu_prepared($sql, [$email, $sodienthoai]);
            
            if (empty($existingCustomer)) {
                // Khách hàng chưa tồn tại, thêm mới
                $sql = "INSERT INTO khachhang (tenKH, email, sodienthoai) VALUES (?, ?, ?)";
                $db->tuychinh($sql, [$tenKH, $email, $sodienthoai]);
                error_log('Customer Info - Inserted new customer: ' . $email . ', ' . $sodienthoai);
            } else {
                // Khách hàng đã tồn tại (email hoặc số điện thoại đã tồn tại)
                // Không thực hiện thêm mới hoặc cập nhật
                error_log('Customer Info - Customer already exists with email or phone: ' . $email . ', ' . $sodienthoai);
                
                // Lưu thông tin debug để kiểm tra
                $_SESSION['debug'][] = [
                    'time' => date('Y-m-d H:i:s'),
                    'action' => 'customer_exists',
                    'email' => $email,
                    'sodienthoai' => $sodienthoai,
                    'existing_data' => $existingCustomer[0]
                ];
            }
            
            // Ghi log debug
            $_SESSION['debug'][] = [
                'time' => date('Y-m-d H:i:s'),
                'action' => 'save_customer_info',
                'madatban' => $madatban,
                'session_madatban' => $_SESSION['madatban']
            ];

            // Chuyển hướng đến trang success
            header('Location: index.php?page=success');
            exit;
        } catch (Exception $e) {
            error_log('Customer Info - Error: ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu thông tin: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin khách hàng - Restoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .container { max-width: 1200px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table th, .summary-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; }
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
        .timer { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-xxl bg-white p-0">
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Đang tải...</span>
            </div>
        </div>

        <div class="container-xxl py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">Thông tin khách hàng</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php?page=trangchu" class="text-warning">Trang Chủ</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Thông tin khách hàng</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container my-5">
            <?php if ($timeLeft > 0): ?>
                <p class="timer">Bàn sẽ được giữ trong <span id="timer"><?php echo $timeLeft; ?></span> giây nữa.</p>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
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
                    <h3 class="mt-4">Tóm tắt đơn hàng</h3>
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
                    <p class="mt-3">Tổng tiền: <strong><?php echo number_format($totalFinal); ?> VND</strong></p>
                </div>
                <div class="col-md-6">
                    <h3>Thông tin khách hàng</h3>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="tenKH" class="form-label">Họ tên</label>
                            <input type="text" class="form-control" id="tenKH" name="tenKH" value="<?php echo isset($_POST['tenKH']) ? htmlspecialchars($_POST['tenKH']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="sodienthoai" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="sodienthoai" name="sodienthoai" value="<?php echo isset($_POST['sodienthoai']) ? htmlspecialchars($_POST['sodienthoai']) : ''; ?>" required>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='index.php?page=confirm_booking'">Quay lại</button>
                            <button type="submit" class="btn btn-warning">Hoàn tất đặt bàn</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script>
        let timeLeft = <?php echo $timeLeft; ?>;
        const timerElement = document.getElementById('timer');
        if (timerElement) {
            const timerInterval = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Thời gian giữ bàn đã hết. Vui lòng đặt lại.');
                    window.location.href = 'index.php?page=error';
                }
            }, 1000);
        }
    </script>
</body>
</html>
<?php
// Ở cuối file, sau tất cả output
ob_end_flush();
?>