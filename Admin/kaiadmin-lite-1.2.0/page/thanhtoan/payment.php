<?php
// Debug code - ghi log tất cả dữ liệu vào file
$debug_file = $_SERVER['DOCUMENT_ROOT'] . '/CNM/payment_debug.log';
$debug_data = date('Y-m-d H:i:s') . " - Request to payment.php\n";
$debug_data .= "GET: " . print_r($_GET, true) . "\n";
$debug_data .= "POST: " . print_r($_POST, true) . "\n";
$debug_data .= "SESSION: " . print_r($_SESSION, true) . "\n";
$debug_data .= "----------------------------------------\n";
file_put_contents($debug_file, $debug_data, FILE_APPEND);

// Enable comprehensive error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file to help with debugging
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/CNM/payment_errors.log');

// Thêm logic chặn URL
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id']) || !isset($_SESSION['vaitro_id'])) {
    header("Location: dangnhap.php");
    exit;
}

// Lấy quyền của nhân viên
$idnv = $_SESSION['nhanvien_id'];
$vaitro_id = $_SESSION['vaitro_id'];
$queryRole = "SELECT v.tenvaitro, v.quyen
             FROM nhanvien n
             JOIN vaitro v ON n.idvaitro = v.idvaitro
             WHERE n.idnv = ?";
$stmt = mysqli_prepare($conn, $queryRole);
mysqli_stmt_bind_param($stmt, "i", $idnv);
mysqli_stmt_execute($stmt);
$resultRole = mysqli_stmt_get_result($stmt);
$roleData = mysqli_fetch_assoc($resultRole);
$permissions = $roleData && !empty($roleData['quyen']) ? explode(",", $roleData['quyen']) : [];

function hasPermission($perm, $permissions) {
    return in_array(trim($perm), array_map('trim', $permissions));
}

// Kiểm tra quyền Thanh toán và vai trò Thu ngân hoặc Quản lý
if (!hasPermission('Thanh toan don hang', $permissions) ) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}
// Kết thúc logic chặn URL

// Kiểm tra GD extension
if (!extension_loaded('gd')) {
    echo "<script>alert('Thư viện GD chưa được bật. Vui lòng liên hệ quản trị viên để bật extension GD trong php.ini'); window.location.href='index.php';</script>";
    exit;
}

// Lấy ID đơn hàng
if (!isset($_GET['idDH'])) {
    echo "<script>alert('Không tìm thấy ID đơn hàng'); window.location.href='index.php?page=dsdonhang';</script>";
    exit;
}
$idDH = intval($_GET['idDH']);

// Load các class cần thiết
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/CNM/Admin/kaiadmin-lite-1.2.0/';
require_once $root_path . 'class/clsconnect.php';
require_once $root_path . 'class/clsban.php';
require_once $root_path . 'class/clsPayment.php';
require_once $root_path . 'vendor/autoload.php';

// Khai báo sử dụng các class từ thư viện Endroid QR Code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;

// Xử lý thanh toán tiền mặt nếu có
if (isset($_POST['cash_payment'])) {
    // Ghi log để debug
    error_log("Cash payment form submitted: " . print_r($_POST, true));
    
    try {
        // Lấy số tiền từ form
        if (!isset($_POST['amount']) || empty($_POST['amount'])) {
            throw new Exception("Không tìm thấy số tiền thanh toán");
        }
        
        $amount = floatval($_POST['amount']);
        if ($amount <= 0) {
            throw new Exception("Số tiền thanh toán không hợp lệ");
        }
        
        // Log thông tin thanh toán
        error_log("Processing cash payment for order #$idDH with amount $amount");
        
        // Sử dụng PaymentHandler để xử lý thanh toán
        $paymentHandler = new PaymentHandler();
        $result = $paymentHandler->processCashPayment($idDH, $amount);
        
        if ($result['status']) {
            // Đặt thông tin thanh toán vào session để hiển thị thông báo thành công
            $_SESSION['payment_success'] = true;
            $_SESSION['payment_method'] = 'cash';
            $_SESSION['payment_amount'] = $amount;
            
            // Sử dụng JavaScript để chuyển hướng thay vì header()
            echo "<script>
                window.location.href = 'index.php?page=xemDH&idDH=$idDH&trangthai=Đã thanh toán';
            </script>";
            exit;
        } else {
            throw new Exception($result['message']);
        }
        
    } catch (Exception $e) {
        // Log lỗi
        error_log("Payment error: " . $e->getMessage());
        
        // Sử dụng JavaScript để chuyển hướng với thông báo lỗi
        echo "<script>
            alert('Lỗi xử lý thanh toán: " . addslashes($e->getMessage()) . "');
            window.location.href = 'index.php?page=thanhtoan/payment&idDH=$idDH';
        </script>";
        exit;
    }
}

// Lấy thông tin đơn hàng
$db = new connect_db();
$sql = "SELECT d.*, b.SoBan, k.tenKH, k.sodienthoai, k.email 
        FROM donhang d 
        JOIN ban b ON d.idban = b.idban 
        JOIN khachhang k ON d.idKH = k.idKH 
        WHERE d.idDH = ?";
$donhang = $db->xuatdulieu_prepared($sql, [$idDH]);

if (empty($donhang)) {
    echo "<script>alert('Không tìm thấy thông tin đơn hàng'); window.location.href='index.php?page=dsdonhang';</script>";
    exit;
}
$donhang = $donhang[0];

// Kiểm tra trạng thái đơn hàng
if ($donhang['TrangThai'] !== 'Đã giao' && $donhang['TrangThai'] !== 'Đã thanh toán') {
    echo "<script>alert('Đơn hàng chưa trong trạng thái Đã giao nên không thể thanh toán'); window.location.href='index.php?page=dsdonhang';</script>";
    exit;
}

// Nếu đơn hàng đã thanh toán, chuyển hướng đến trang chi tiết
if ($donhang['TrangThai'] === 'Đã thanh toán') {
    echo "<script>window.location.href='index.php?page=xemDH&idDH=$idDH&trangthai=Đã thanh toán';</script>";
    exit;
}

// Lấy chi tiết món ăn
$sql = "SELECT m.tenmonan, m.DonViTinh, c.SoLuong, m.DonGia, (c.SoLuong * m.DonGia) as ThanhTien
        FROM chitietdonhang c
        JOIN monan m ON c.idmonan = m.idmonan
        WHERE c.idDH = ?";

// Ghi log truy vấn để debug
error_log("SQL Query for order details: " . $sql);
$monAn = $db->xuatdulieu_prepared($sql, [$idDH]);

// Tính tổng tiền
$tongTien = 0;
foreach ($monAn as $mon) {
    $tongTien += $mon['ThanhTien'];
}

// Cập nhật tổng tiền vào bảng donhang nếu chưa có
if (empty($donhang['TongTien']) && $tongTien > 0) {
    $updateSql = "UPDATE donhang SET TongTien = ? WHERE idDH = ?";
    $db->tuychinh($updateSql, [$tongTien, $idDH]);
    
    // Cập nhật lại thông tin đơn hàng
    $donhang['TongTien'] = $tongTien;
}

// Cấu hình VNPAY
$vnpayConfig = [
    'vnp_TmnCode' => 'A7RWSAH8',
    'vnp_HashSecret' => 'DCJD0IOT5N6FIT6YDS71JQ5JUH91L6H5',
    'vnp_Url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    'vnp_ReturnUrl' => 'http://localhost/CNM/index.php'
];

// Tạo dữ liệu thanh toán
$vnp_TxnRef = $idDH . '_' . time(); // Mã đơn hàng + timestamp
$vnp_OrderInfo = 'Thanh toan don hang ' . $idDH;
$vnp_Amount = $tongTien * 100; // Số tiền * 100 (VNPay yêu cầu đơn vị xu)
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

// Đặt múi giờ cho Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Tạo thời gian với định dạng chính xác
$vnp_CreateDate = date('YmdHis');
$vnp_ExpireDate = date('YmdHis', strtotime('+30 minutes'));

// Dữ liệu thanh toán
$inputData = [
    'vnp_Version' => '2.1.0',
    'vnp_TmnCode' => $vnpayConfig['vnp_TmnCode'],
    'vnp_Amount' => $vnp_Amount,
    'vnp_Command' => 'pay',
    'vnp_CreateDate' => $vnp_CreateDate,
    'vnp_CurrCode' => 'VND',
    'vnp_IpAddr' => $vnp_IpAddr,
    'vnp_Locale' => 'vn',
    'vnp_OrderInfo' => $vnp_OrderInfo,
    'vnp_OrderType' => 'billpayment',
    'vnp_ReturnUrl' => $vnpayConfig['vnp_ReturnUrl'],
    'vnp_TxnRef' => $vnp_TxnRef,
    'vnp_ExpireDate' => $vnp_ExpireDate
];

// Tạo chuỗi hash
ksort($inputData);
$query = http_build_query($inputData);
$vnp_SecureHash = hash_hmac('sha512', $query, $vnpayConfig['vnp_HashSecret']);
$vnp_Url = $vnpayConfig['vnp_Url'] . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

// Tạo mã QR
try {
    error_log('Starting QR code generation with Endroid library');
    error_log('QR URL: ' . $vnp_Url);
    
    // Tạo QR code với thư viện Endroid
    $qrCode = new QrCode($vnp_Url);
    $qrCode->setSize(300);
    $qrCode->setMargin(10);
    $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh());
    $qrCode->setForegroundColor(new Color(0, 0, 0));
    $qrCode->setBackgroundColor(new Color(255, 255, 255));
    
    // Tạo writer để xuất QR code
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Lấy data URI để hiển thị trong HTML
    $qrCodeBase64 = $result->getDataUri();
    
    error_log('QR code generated successfully with Endroid');
} catch (Exception $e) {
    error_log('QR code generation error: ' . $e->getMessage());
    error_log('Error trace: ' . $e->getTraceAsString());
    
    // Tạo một hình ảnh mặc định nếu không thể tạo mã QR
    if (extension_loaded('gd')) {
        $im = imagecreatetruecolor(200, 200);
        $bgColor = imagecolorallocate($im, 255, 255, 255);
        $textColor = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 200, 200, $bgColor);
        imagestring($im, 5, 40, 80, 'Scan to pay', $textColor);
        imagestring($im, 3, 30, 100, 'Error generating QR', $textColor);
        
        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);
        
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($imageData);
        error_log('Using fallback QR image due to error');
    } else {
        $qrCodeBase64 = ''; // Không có hình ảnh
        echo "<script>alert('Không thể tạo mã QR: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Lưu thông tin vào session để callback có thể truy cập
$_SESSION['payment_info'] = [
    'idDH' => $idDH,
    'amount' => $tongTien,
    'txn_ref' => $vnp_TxnRef
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Restoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .container { max-width: 800px; }
        .btn-primary {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        .btn-primary:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
        }
        .qr-code { text-align: center; margin: 30px 0; }
        .qr-expiry {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
        }
        .qr-code-container {
            position: relative;
            display: inline-block;
        }
        .qr-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
        }
        .payment-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .buttons {
            text-align: center;
            margin-top: 20px;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #000;
        }
        .tab-content {
            padding: 20px 0;
        }
        .input-group-text {
            background-color: #ffc107;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h3 class="text-center mb-4">Thanh toán đơn hàng #<?php echo $idDH; ?></h3>
                
                <!-- Thêm thông báo thanh toán thành công -->
                <div id="paymentSuccessAlert" class="alert alert-success text-center" style="display: none;">
                    <h4><i class="fas fa-check-circle"></i> Thanh toán thành công!</h4>
                    <p>Đơn hàng #<?php echo $idDH; ?> đã được thanh toán qua VNPay.</p>
                    <p>Đang chuyển hướng đến trang chi tiết đơn hàng...</p>
                </div>

                <div class="payment-card">
                    <div class="order-summary mb-4">
                        <h5>Thông tin đơn hàng</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Khách hàng:</strong> <?php echo $donhang['tenKH']; ?></p>
                                <p><strong>Số điện thoại:</strong> <?php echo $donhang['sodienthoai']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Bàn số:</strong> <?php echo $donhang['SoBan']; ?></p>
                                <p><strong>Tổng tiền:</strong> <?php echo number_format($tongTien); ?> VND</p>
                            </div>
                        </div>
                    </div>
                    
                    <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="cash-tab" data-bs-toggle="tab" data-bs-target="#cash" 
                                    type="button" role="tab" aria-controls="cash" aria-selected="true">
                                Thanh toán tiền mặt
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="vnpay-tab" data-bs-toggle="tab" data-bs-target="#vnpay" 
                                    type="button" role="tab" aria-controls="vnpay" aria-selected="false">
                                Thanh toán VNPay
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="paymentTabsContent">
                        <!-- Tab thanh toán tiền mặt -->
                        <div class="tab-pane fade show active" id="cash" role="tabpanel" aria-labelledby="cash-tab">
                            <form method="POST" action="" id="cashPaymentForm">
                                <input type="hidden" name="idDH" value="<?php echo $idDH; ?>">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Số tiền thanh toán</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="amount" name="amount" 
                                               value="<?php echo $tongTien; ?>" required>
                                        <span class="input-group-text">VND</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="received" class="form-label">Tiền khách đưa</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="received" name="received" 
                                               placeholder="Nhập số tiền khách đưa">
                                        <span class="input-group-text">VND</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="change" class="form-label">Tiền thối lại</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="change" readonly>
                                        <span class="input-group-text">VND</span>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="cash_payment" value="1" class="btn btn-success">
                                        <i class="fas fa-money-bill-wave me-2"></i> Xác nhận thanh toán
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Tab thanh toán VNPay -->
                        <div class="tab-pane fade" id="vnpay" role="tabpanel" aria-labelledby="vnpay-tab">
                            <div class="qr-code">
                                <div class="qr-code-container">
                                    <img src="<?php echo $qrCodeBase64; ?>" alt="VNPay QR Code">
                                    <div class="qr-overlay" id="qrOverlay">
                                        Mã QR đã hết hạn. Vui lòng làm mới trang để tạo mã mới.
                                    </div>
                                </div>
                                <p class="qr-expiry" id="qr-expiry">Mã QR sẽ hết hạn sau 30 phút. Vui lòng thanh toán trước khi hết hạn.</p>
                            </div>
                            
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                                    <i class="fas fa-sync-alt me-2"></i> Làm mới mã QR
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="index.php?page=xemDH&idDH=<?php echo $idDH; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Quay lại
                        </a>
                        
                        <?php if ($_SESSION['vaitro_id'] == 4): // Chỉ hiển thị cho quản lý ?>
                        <div class="mt-3">
                            <!-- Đã xóa các liên kết đến trang check_table và fix_table -->
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
        // Cập nhật JavaScript để hiển thị thời gian còn lại cho mã QR
        const expiryTime = new Date('<?php echo date('Y-m-d H:i:s', strtotime($vnp_ExpireDate)); ?>').getTime();
        
        function updateTimeRemaining() {
            const now = new Date().getTime();
            const timeLeft = expiryTime - now;
            
            if (timeLeft <= 0) {
                document.getElementById('qrOverlay').style.display = 'flex';
                document.getElementById('qr-expiry').textContent = 'Mã QR đã hết hạn. Vui lòng làm mới trang để tạo mã mới.';
            } else {
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                document.getElementById('qr-expiry').textContent = 
                    `Mã QR sẽ hết hạn sau ${minutes} phút ${seconds} giây. Vui lòng thanh toán trước khi hết hạn.`;
            }
        }
        
        // Cập nhật mỗi giây
        setInterval(updateTimeRemaining, 1000);
        // Cập nhật ngay khi trang load
        updateTimeRemaining();

        // Kiểm tra trạng thái đơn hàng mỗi 5 giây
        function checkOrderStatus() {
            fetch('/CNM/Admin/kaiadmin-lite-1.2.0/page/thanhtoan/check_order_status.php?idDH=<?php echo $idDH; ?>', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Order status:', data);
                if (data.status === 'Đã thanh toán') {
                    // Hiển thị thông báo thành công
                    document.getElementById('paymentSuccessAlert').style.display = 'block';
                    
                    // Chuyển hướng sau 3 giây
                    setTimeout(() => {
                        window.location.href = '../../index.php?page=xemDH&idDH=<?php echo $idDH; ?>&trangthai=Đã thanh toán';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error checking order status:', error);
                setTimeout(checkOrderStatus, 5000);
            });
        }

        // Kiểm tra trạng thái cho cả hai tab
        let statusCheckInterval = setInterval(checkOrderStatus, 5000);

        // Xử lý tính tiền thối lại
        const receivedInput = document.getElementById('received');
        const changeInput = document.getElementById('change');
        const amountInput = document.getElementById('amount');

        receivedInput.addEventListener('input', calculateChange);
        amountInput.addEventListener('input', calculateChange);

        function calculateChange() {
            const amount = parseFloat(amountInput.value) || 0;
            const received = parseFloat(receivedInput.value) || 0;
            
            if (received >= amount) {
                const change = received - amount;
                changeInput.value = change.toLocaleString('vi-VN');
            } else {
                changeInput.value = '';
            }
        }
        
        // Xử lý form thanh toán tiền mặt
        document.getElementById('cashPaymentForm').addEventListener('submit', function(e) {
            // Validate form
            const amount = parseFloat(amountInput.value) || 0;
            const received = parseFloat(receivedInput.value) || 0;
            
            if (amount <= 0) {
                e.preventDefault();
                alert('Số tiền thanh toán không hợp lệ!');
                amountInput.focus();
                return false;
            }
            
            if (received < amount) {
                e.preventDefault();
                alert('Số tiền khách đưa phải lớn hơn hoặc bằng số tiền cần thanh toán!');
                receivedInput.focus();
                return false;
            }
            
            // Xác nhận thanh toán
            if (!confirm('Xác nhận thanh toán tiền mặt cho đơn hàng #<?php echo $idDH; ?>?\n\nSố tiền: ' + amount.toLocaleString('vi-VN') + ' VND')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });

        // Cleanup interval when leaving page
        window.addEventListener('beforeunload', function() {
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
        });

        // Kiểm tra trạng thái ngay khi trang load
        checkOrderStatus();
    </script>
</body>
</html>