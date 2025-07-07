<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to Vietnam
date_default_timezone_set('Asia/Ho_Chi_Minh');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'class/clsconnect.php';
require_once 'class/clskvban.php';
require_once 'class/clsban.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . '/PHPMailer/Exception.php';
require dirname(__DIR__) . '/PHPMailer/PHPMailer.php';
require dirname(__DIR__) . '/PHPMailer/SMTP.php';

// Kiểm tra madatban từ session
$madatban = isset($_SESSION['madatban']) ? (int)$_SESSION['madatban'] : null;

// Add this after getting madatban
if (!$madatban) {
    error_log("Debug - No madatban found in session");
} else {
    error_log("Debug - Found madatban: " . $madatban);
}

$db = new connect_db();

// Lấy thông tin đặt bàn
$sql = "SELECT d.*, b.SoBan, kvb.TenKV as TenKhuVuc
        FROM datban d 
        LEFT JOIN ban b ON d.idban = b.idban
        LEFT JOIN khuvucban kvb ON b.MaKV = kvb.MaKV
        WHERE d.madatban = ?";
$result = $db->xuatdulieu_prepared($sql, [$madatban]);

if (empty($result)) {
    $_SESSION['error'] = 'Không tìm thấy thông tin đặt bàn. Vui lòng đặt lại.';
    header('Location: index.php?page=error');
    exit;
}

$booking = $result[0];

// Sử dụng thông tin khách hàng từ bảng datban (cấu trúc mới)
if (empty($booking['tenKH']) && isset($_SESSION['customer_info'])) {
    // Nếu không có trong bảng, dùng từ session
    $booking['tenKH'] = $_SESSION['customer_info']['tenKH'];
    $booking['email'] = $_SESSION['customer_info']['email'];
    $booking['sodienthoai'] = $_SESSION['customer_info']['sodienthoai'];
}

// Lấy chi tiết món ăn
$sql = "SELECT m.tenmonan, m.idmonan, c.SoLuong, c.DonGia
        FROM chitietdatban c
        JOIN monan m ON c.idmonan = m.idmonan
        WHERE c.madatban = ?";
$dishes = $db->xuatdulieu_prepared($sql, [$madatban]);

// Tính tổng tiền
$totalFinal = 0;
foreach ($dishes as $dish) {
    $totalFinal += $dish['SoLuong'] * $dish['DonGia'];
}

// Tự động tạo đơn hàng từ đặt bàn
$orderCreated = false;
$orderError = '';
$debugInfo = []; // For debugging purposes

// Add this before the order creation block
error_log("Debug - Starting order creation process");
error_log("Debug - Checking if order already exists: " . (isset($_SESSION['order_created_'.$madatban]) ? 'Yes' : 'No'));

if (!isset($_SESSION['order_created_'.$madatban])) {
    // Create a direct MySQLi connection for this transaction
    $mysqli = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
    if ($mysqli->connect_error) {
        error_log("Debug - Database connection error: " . $mysqli->connect_error);
        $orderError = 'Database connection error: ' . $mysqli->connect_error;
        error_log($orderError);
    } else {
        error_log("Debug - Database connection successful");
        // Set charset to utf8
        $mysqli->set_charset("utf8");
        
        try {
            $debugInfo['start_time'] = date('Y-m-d H:i:s');
            $debugInfo['madatban'] = $madatban;
            $debugInfo['booking_info'] = $booking;
            
            // Kiểm tra xem đã có đơn hàng nào được tạo từ madatban này chưa
            $checkOrderStmt = $mysqli->prepare("SELECT idDH, MaDonHang FROM donhang WHERE MaDonHang LIKE ?");
            $searchPattern = '%' . $madatban;
            $checkOrderStmt->bind_param('s', $searchPattern);
            $checkOrderStmt->execute();
            $checkOrderResult = $checkOrderStmt->get_result();
            $existingOrder = $checkOrderResult->fetch_assoc();
            $checkOrderStmt->close();
            
            $debugInfo['check_order_result'] = $existingOrder;
            
            if (!empty($existingOrder) && isset($existingOrder['idDH'])) {
                // Đã có đơn hàng được tạo từ madatban này
                $_SESSION['order_created_'.$madatban] = true;
                $orderCreated = true;
                $debugInfo['order_exists'] = true;
                $debugInfo['existing_order_id'] = $existingOrder['idDH'];
                error_log("Order already exists for booking ID: $madatban, Order ID: " . $existingOrder['idDH']);
            } else {
                $debugInfo['order_exists'] = false;
                
                // Start a transaction
                $mysqli->begin_transaction();
                
                // Lấy idKH dựa trên số điện thoại hoặc email
                $customerStmt = $mysqli->prepare("SELECT idKH FROM khachhang WHERE sodienthoai = ? OR email = ? LIMIT 1");
                $customerStmt->bind_param('ss', $booking['sodienthoai'], $booking['email']);
                $customerStmt->execute();
                $customerResult = $customerStmt->get_result();
                $khachhang = $customerResult->fetch_assoc();
                $customerStmt->close();
                
                $debugInfo['get_customer_result'] = $khachhang;
                
                if (empty($khachhang)) {
                    // Nếu không tìm thấy khách hàng, tạo mới
                    $insertCustomerStmt = $mysqli->prepare("INSERT INTO khachhang (tenKH, sodienthoai, email) VALUES (?, ?, ?)");
                    $insertCustomerStmt->bind_param('sss', $booking['tenKH'], $booking['sodienthoai'], $booking['email']);
                    $insertCustomerStmt->execute();
                    $idKH = $mysqli->insert_id;
                    $insertCustomerStmt->close();
                    
                    $debugInfo['customer_created'] = true;
                    $debugInfo['new_customer_id'] = $idKH;
                } else {
                    $idKH = $khachhang['idKH'];
                    $debugInfo['customer_created'] = false;
                    $debugInfo['existing_customer_id'] = $idKH;
                }
                
                if (empty($idKH) || $idKH <= 0) {
                    throw new Exception("Invalid customer ID: " . $idKH);
                }
                
                // Tạo mã đơn hàng từ ngày và giờ hiện tại, kết hợp với madatban để đảm bảo duy nhất
                $maDonHang = date('ymd-His-') . $madatban;
                $soHoaDon = date('His');
                $trangThai = 'Chờ xử lý';
                
                $debugInfo['order_code'] = $maDonHang;
                $debugInfo['so_hoa_don'] = $soHoaDon;
                
                // Lấy ngày đặt hàng từ ngày đặt bàn
                $ngayDatHang = date('Y-m-d H:i:s', strtotime($booking['NgayDatBan']));
                $debugInfo['ngay_dat_hang'] = $ngayDatHang;
                
                // Thêm vào bảng donhang
                $insertOrderStmt = $mysqli->prepare("INSERT INTO donhang (idKH, idban, NgayDatHang, TongTien, TrangThai, MaDonHang, SoHoaDon) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if (!$insertOrderStmt) {
                    throw new Exception("Failed to prepare order insert statement: " . $mysqli->error);
                }
                
                error_log("Debug - Preparing to insert order with data: " . print_r([
                    'idKH' => $idKH,
                    'idban' => $booking['idban'],
                    'ngayDatHang' => $ngayDatHang,
                    'tongTien' => $booking['TongTien'],
                    'trangThai' => $trangThai,
                    'maDonHang' => $maDonHang,
                    'soHoaDon' => $soHoaDon
                ], true));
                
                $insertOrderStmt->bind_param('iisdsss', $idKH, $booking['idban'], $ngayDatHang, $booking['TongTien'], $trangThai, $maDonHang, $soHoaDon);
                $insertOrderResult = $insertOrderStmt->execute();
                
                if (!$insertOrderResult) {
                    throw new Exception("Failed to insert order: " . $mysqli->error . " - " . $insertOrderStmt->error);
                }
                
                $idDH = $mysqli->insert_id;
                $insertOrderStmt->close();
                
                $debugInfo['order_inserted'] = true;
                $debugInfo['new_order_id'] = $idDH;
                error_log("Debug - Order inserted successfully with ID: " . $idDH);
                
                if (empty($idDH) || $idDH <= 0) {
                    throw new Exception("Failed to get order ID after insert: " . $mysqli->error);
                }
                
                // Thêm chi tiết đơn hàng
                if (empty($dishes) || !is_array($dishes)) {
                    throw new Exception("No dishes found for booking ID: " . $madatban);
                }
                
                $debugInfo['dish_count'] = count($dishes);
                
                foreach ($dishes as $index => $dish) {
                    if (empty($dish['idmonan']) || $dish['idmonan'] <= 0) {
                        throw new Exception("Invalid dish ID for item " . $index);
                    }
                    
                    $idmonan = (int)$dish['idmonan'];
                    $soLuong = (int)$dish['SoLuong'];
                    
                    if ($soLuong <= 0) {
                        throw new Exception("Invalid quantity for dish ID: " . $idmonan);
                    }
                    
                    $insertDetailStmt = $mysqli->prepare("INSERT INTO chitietdonhang (idDH, idmonan, SoLuong) VALUES (?, ?, ?)");
                    $insertDetailStmt->bind_param('iii', $idDH, $idmonan, $soLuong);
                    $insertDetailResult = $insertDetailStmt->execute();
                    $insertDetailStmt->close();
                    
                    if (!$insertDetailResult) {
                        throw new Exception("Failed to insert order detail for dish " . $dish['idmonan'] . ": " . $mysqli->error);
                    }
                }
                $debugInfo['details_inserted'] = true;
                
                // Commit the transaction
                $mysqli->commit();
                
                // Đánh dấu đã tạo đơn hàng
                $_SESSION['order_created_'.$madatban] = true;
                $orderCreated = true;
                error_log("Order created successfully from booking ID: $madatban, Order ID: $idDH");
            }
            
            // Log debug info
            error_log("DEBUG INFO: " . json_encode($debugInfo, JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            // Rollback if there was an error
            $mysqli->rollback();
            
            $orderError = 'Không thể tạo đơn hàng tự động: ' . $e->getMessage();
            $debugInfo['final_error'] = $e->getMessage();
            error_log("Failed to create order from booking: " . $e->getMessage());
            error_log("DEBUG INFO: " . json_encode($debugInfo, JSON_UNESCAPED_UNICODE));
        }
        
        // Close the connection
        $mysqli->close();
    }
}

// Gửi email xác nhận
function sendConfirmationEmail($booking, $dishes, $madatban) {
    try {
        error_log("Trying to send email to: " . $booking['email']);
        
        $mail = new PHPMailer(true);
        
        // Bắt đầu output buffering để ngăn debug hiển thị trên trang
        ob_start();
        
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->SMTPDebug = 2; // Debug vẫn được ghi log nhưng không hiển thị trên trang
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer [$level]: $str"); // Ghi log thay vì hiển thị
        };
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nhocac2k3@gmail.com'; // Email của bạn
        $mail->Password = 'synj bwev gvut uamu'; // Mật khẩu ứng dụng của bạn
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Người gửi và người nhận
        $mail->setFrom('nhocac2k3@gmail.com', 'Restoran');
        $mail->addAddress($booking['email'], $booking['tenKH']);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đặt bàn thành công';

        // Tạo nội dung HTML cho email
        $emailContent = "
        <h2>Xác nhận đặt bàn thành công</h2>
        <p>Xin chào {$booking['tenKH']},</p>
        <p>Cảm ơn bạn đã đặt bàn tại nhà hàng của chúng tôi. Dưới đây là chi tiết đặt bàn của bạn:</p>
        
        <h3>Thông tin đặt bàn:</h3>
        <ul>
            <li>Mã đặt bàn: {$booking['madatban']}</li>
            <li>Ngày giờ: {$booking['NgayDatBan']}</li>
            <li>Số bàn: {$booking['SoBan']}</li>
            <li>Khu vực: {$booking['TenKhuVuc']}</li>
            <li>Số lượng khách: {$booking['SoLuongKhach']}</li>
        </ul>

        <h3>Các món đã đặt:</h3>
        <table border='1' style='border-collapse: collapse; width: 100%;'>
            <tr>
                <th>Món</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
            </tr>";

        foreach ($dishes as $dish) {
            $emailContent .= "
            <tr>
                <td>{$dish['tenmonan']}</td>
                <td>{$dish['SoLuong']}</td>
                <td>" . number_format($dish['DonGia']) . " VND</td>
            </tr>";
        }

        $emailContent .= "
        </table>
        <p><strong>Tổng tiền: " . number_format($booking['TongTien']) . " VND</strong></p>
        <p>Cảm ơn bạn đã lựa chọn nhà hàng của chúng tôi!</p>";

        // Thêm thông báo về đơn hàng được tạo tự động
        if (isset($_SESSION['order_created_'.$madatban])) {
            $emailContent .= "
            <p><strong>Lưu ý:</strong> Chúng tôi đã tự động tạo đơn hàng cho bạn với trạng thái 'Chờ xử lý'. 
            Nhà hàng sẽ chuẩn bị món ăn của bạn và phục vụ khi bạn đến.</p>";
        }

        $mail->Body = $emailContent;

        $result = $mail->send();
        
        // Xóa bỏ output buffer để không hiển thị debug trên trang
        ob_end_clean();
        
        error_log("Email sent successfully to " . $booking['email']);
        return true;
    } catch (Exception $e) {
        // Xóa bỏ output buffer nếu có lỗi
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

// Biến để lưu lỗi gửi email nếu có
$emailError = '';

// Gửi email nếu chưa được gửi
if (!isset($_SESSION['email_sent_'.$madatban]) && $booking['email']) {
    error_log("Preparing to send email. Email address: " . $booking['email']);
    if (sendConfirmationEmail($booking, $dishes, $madatban)) {
        $_SESSION['email_sent_'.$madatban] = true;
        error_log("Email sent marker set in session for booking ID: $madatban");
    } else {
        $emailError = 'Không thể gửi email xác nhận. Vui lòng liên hệ với nhà hàng để được hỗ trợ.';
        error_log("Failed to send email: " . $emailError);
    }
} else {
    if (isset($_SESSION['email_sent_'.$madatban])) {
        error_log("Email already marked as sent in session for booking ID: $madatban");
    }
    if (empty($booking['email'])) {
        error_log("No email address available in booking data");
        $emailError = 'Không có địa chỉ email để gửi xác nhận.';
    }
}

// Hiển thị trang thành công
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt bàn thành công - Restoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .container { max-width: 1200px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table th, .summary-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .btn-primary {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        .btn-primary:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
        }
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
                <h1 class="display-3 text-white mb-3 animated slideInDown">Đặt bàn thành công</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php?page=trangchu" class="text-warning">Trang Chủ</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Đặt bàn thành công</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-center mb-4">Cảm ơn bạn đã đặt bàn tại nhà hàng của chúng tôi! Dưới đây là chi tiết đặt bàn:</h4>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Thông tin đặt bàn</h5>
                                    <table class="table">
                                        <tr>
                                            <td>Mã đặt bàn:</td>
                                            <td><?php echo $booking['madatban']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Họ tên:</td>
                                            <td><?php echo htmlspecialchars($booking['tenKH']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Email:</td>
                                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Số điện thoại:</td>
                                            <td><?php echo htmlspecialchars($booking['sodienthoai']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Ngày giờ:</td>
                                            <td><?php echo $booking['NgayDatBan']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Số bàn:</td>
                                            <td><?php echo $booking['SoBan']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Khu vực:</td>
                                            <td><?php echo $booking['TenKhuVuc']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Số lượng khách:</td>
                                            <td><?php echo $booking['SoLuongKhach']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5>Tóm tắt đơn hàng</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Món</th>
                                                <th>Số lượng</th>
                                                <th>Đơn giá</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dishes as $dish): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($dish['tenmonan']); ?></td>
                                                <td><?php echo $dish['SoLuong']; ?></td>
                                                <td><?php echo number_format($dish['DonGia']); ?> VND</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2"><strong>Tổng tiền:</strong></td>
                                                <td><strong><?php echo number_format($booking['TongTien']); ?> VND</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="text-center">
                                <p>Chúng tôi đã gửi email xác nhận đến địa chỉ: <?php echo htmlspecialchars($booking['email']); ?></p>
                                <?php if (!empty($emailError)): ?>
                                    <div class="alert alert-warning mt-2">
                                        <p><?php echo htmlspecialchars($emailError); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['order_created_'.$madatban])): ?>
                                <div class="alert alert-success mt-2">
                                    <p><strong>Lưu ý:</strong> Chúng tôi đã tự động tạo đơn hàng cho bạn với trạng thái 'Chờ xử lý'. 
                                    Nhà hàng sẽ chuẩn bị món ăn của bạn và phục vụ khi bạn đến.</p>
                                </div>
                                <?php elseif (!empty($orderError)): ?>
                                <div class="alert alert-warning mt-2">
                                    <p><?php echo htmlspecialchars($orderError); ?></p>
                                    <?php if(isset($_GET['debug'])): ?>
                                    <div class="mt-3">
                                        <h5>Debug Information:</h5>
                                        <pre><?php echo htmlspecialchars(json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <a href="index.php" class="btn btn-primary">Trở về trang chủ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_GET['debug'])): ?>
    <div class="container my-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Debug Information</h5>
            </div>
            <div class="card-body">
                <h6>Order Creation Status: <?php echo $orderCreated ? 'Success' : 'Failed'; ?></h6>
                <?php if(!empty($orderError)): ?>
                <div class="alert alert-danger">Error: <?php echo htmlspecialchars($orderError); ?></div>
                <?php endif; ?>
                
                <h6>Debug Data:</h6>
                <pre class="bg-light p-3" style="max-height: 400px; overflow-y: auto;"><?php echo htmlspecialchars(json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                
                <h6>Session Data:</h6>
                <pre class="bg-light p-3" style="max-height: 400px; overflow-y: auto;"><?php echo htmlspecialchars(json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                
                <h6>Database Check:</h6>
                <?php
                try {
                    $debugDb = new connect_db();
                    
                    // Check donhang table structure
                    echo "<h6>donhang table structure:</h6>";
                    $tableInfo = $debugDb->xuatdulieu("SHOW COLUMNS FROM donhang");
                    echo "<pre class='bg-light p-3'>" . htmlspecialchars(json_encode($tableInfo, JSON_PRETTY_PRINT)) . "</pre>";
                    
                    // Check if there are any orders with this madatban
                    echo "<h6>Orders with madatban {$madatban}:</h6>";
                    $searchPattern = '%' . $madatban . '%';
                    $orders = $debugDb->xuatdulieu_prepared(
                        "SELECT * FROM donhang WHERE MaDonHang LIKE ?", 
                        [$searchPattern]
                    );
                    echo "<pre class='bg-light p-3'>" . htmlspecialchars(json_encode($orders, JSON_PRETTY_PRINT)) . "</pre>";
                    
                    // Check latest orders
                    echo "<h6>Latest 5 orders:</h6>";
                    $latestOrders = $debugDb->xuatdulieu("SELECT * FROM donhang ORDER BY idDH DESC LIMIT 5");
                    echo "<pre class='bg-light p-3'>" . htmlspecialchars(json_encode($latestOrders, JSON_PRETTY_PRINT)) . "</pre>";
                    
                    // Check order details for this booking
                    echo "<h6>Order details for orders related to this booking:</h6>";
                    $orderDetails = $debugDb->xuatdulieu_prepared(
                        "SELECT d.*, cd.idCTDH, cd.idmonan, cd.SoLuong, m.tenmonan 
                        FROM donhang d 
                        LEFT JOIN chitietdonhang cd ON d.idDH = cd.idDH 
                        LEFT JOIN monan m ON cd.idmonan = m.idmonan 
                        WHERE d.MaDonHang LIKE ? 
                        ORDER BY d.idDH DESC", 
                        [$searchPattern]
                    );
                    echo "<pre class='bg-light p-3'>" . htmlspecialchars(json_encode($orderDetails, JSON_PRETTY_PRINT)) . "</pre>";
                    
                    // Check if there are any issues with the database connection
                    echo "<h6>Testing direct database connection:</h6>";
                    $testConn = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                    if ($testConn->connect_error) {
                        echo "<div class='alert alert-danger'>Direct connection error: " . $testConn->connect_error . "</div>";
                    } else {
                        echo "<div class='alert alert-success'>Direct connection successful</div>";
                        
                        // Test a simple query
                        $testResult = $testConn->query("SELECT COUNT(*) as count FROM donhang");
                        if ($testResult) {
                            $row = $testResult->fetch_assoc();
                            echo "<div>Total orders in database: " . $row['count'] . "</div>";
                        } else {
                            echo "<div class='alert alert-danger'>Query error: " . $testConn->error . "</div>";
                        }
                        
                        $testConn->close();
                    }
                    
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Database check error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
                ?>
                
                <h6>Manual Order Creation Form:</h6>
                <form method="post" action="?page=success&debug=1&action=create_order">
                    <input type="hidden" name="madatban" value="<?php echo $madatban; ?>">
                    <button type="submit" class="btn btn-warning">Manually Create Order</button>
                </form>
                
                <?php if(isset($_GET['action']) && $_GET['action'] == 'create_order' && isset($_POST['madatban'])): ?>
                <div class="mt-4">
                    <h6>Manual Order Creation Result:</h6>
                    <?php
                    try {
                        $manualDb = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                        $manualDb->set_charset("utf8");
                        
                        // Get booking info
                        $stmt = $manualDb->prepare("SELECT * FROM datban WHERE madatban = ?");
                        $stmt->bind_param('i', $_POST['madatban']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $manualBooking = $result->fetch_assoc();
                        $stmt->close();
                        
                        if (!$manualBooking) {
                            throw new Exception("Booking not found");
                        }
                        
                        // Get customer ID
                        $stmt = $manualDb->prepare("SELECT idKH FROM khachhang WHERE sodienthoai = ? OR email = ? LIMIT 1");
                        $stmt->bind_param('ss', $manualBooking['sodienthoai'], $manualBooking['email']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $manualCustomer = $result->fetch_assoc();
                        $stmt->close();
                        
                        if (!$manualCustomer) {
                            throw new Exception("Customer not found");
                        }
                        
                        // Create order
                        $manualMaDonHang = date('ymd-His-') . $_POST['madatban'];
                        $manualSoHoaDon = date('His');
                        $manualTrangThai = 'Chờ xử lý';
                        $manualNgayDatHang = date('Y-m-d H:i:s');
                        
                        $stmt = $manualDb->prepare("INSERT INTO donhang (idKH, idban, NgayDatHang, TongTien, TrangThai, MaDonHang, SoHoaDon) VALUES ( ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param('iisdssss', $manualCustomer['idKH'], $manualBooking['idban'], $manualNgayDatHang, $manualBooking['TongTien'], $manualTrangThai, $manualMaDonHang
                        , $manualSoHoaDon);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to insert order: " . $stmt->error);
                        }
                        
                        $manualOrderId = $manualDb->insert_id;
                        $stmt->close();
                        
                        // Get dishes
                        $stmt = $manualDb->prepare("SELECT * FROM chitietdatban WHERE madatban = ?");
                        $stmt->bind_param('i', $_POST['madatban']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $manualDishes = [];
                        while ($dish = $result->fetch_assoc()) {
                            $manualDishes[] = $dish;
                        }
                        $stmt->close();
                        
                        // Insert order details
                        foreach ($manualDishes as $dish) {
                            $stmt = $manualDb->prepare("INSERT INTO chitietdonhang (idDH, idmonan, SoLuong) VALUES (?, ?, ?)");
                            $stmt->bind_param('iii', $manualOrderId, $dish['idmonan'], $dish['SoLuong']);
                            $stmt->execute();
                            $stmt->close();
                        }
                        
                        echo "<div class='alert alert-success'>Order created manually with ID: $manualOrderId</div>";
                        
                        $manualDb->close();
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>Manual order creation failed: " . $e->getMessage() . "</div>";
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script>
        // Hide spinner after page loads
        window.addEventListener('load', function() {
            document.getElementById('spinner').style.display = 'none';
        });
    </script>
</body>
</html>
<?php
// Xóa các session không cần thiết sau khi đã hiển thị trang thành công
unset($_SESSION['booking']);
unset($_SESSION['selected_monan']);
// KHÔNG xóa madatban và email_sent ở đây để tránh vấn đề nếu người dùng tải lại trang

// Xóa email_sent của đơn đặt bàn khác để không ảnh hưởng đơn mới
$current_keys = array_keys($_SESSION);
foreach($current_keys as $key) {
    if(strpos($key, 'email_sent_') === 0 && $key !== 'email_sent_'.$madatban) {
        unset($_SESSION[$key]);
    }
}
?>