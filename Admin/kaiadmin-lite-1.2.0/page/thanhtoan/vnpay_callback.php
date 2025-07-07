<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the start of the callback processing
error_log('VNPAY Callback started');

// Include database connection and payment handler class
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/CNM/Admin/kaiadmin-lite-1.2.0/';
require_once $root_path . 'class/clsconnect.php';
require_once $root_path . 'class/clsPayment.php';

// Log script access details
error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
error_log('Callback script path: ' . __FILE__);

// Debug session data
error_log('Session data in callback: ' . print_r($_SESSION, true));
error_log('GET data: ' . print_r($_GET, true));

// VNPAY configuration
$vnpayConfig = [
    'vnp_TmnCode' => 'A7RWSAH8',
    'vnp_HashSecret' => 'DCJD0IOT5N6FIT6YDS71JQ5JUH91L6H5'
];

// Get the secure hash from the callback
$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
if (empty($vnp_SecureHash)) {
    error_log('Error: Missing vnp_SecureHash parameter');
}

// Process all input data from VNPAY
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

error_log('Input data processed: ' . count($inputData) . ' parameters');

// Remove the secure hash from the input data
unset($inputData['vnp_SecureHash']);

// Sort the input data by key
ksort($inputData);

// Create a query string from the input data
$query = http_build_query($inputData);
error_log('Query string generated: ' . $query);

// Generate a hash using the same method as in the payment.php
$hashData = hash_hmac('sha512', $query, $vnpayConfig['vnp_HashSecret']);
error_log('Calculated hash: ' . $hashData);
error_log('Received hash: ' . $vnp_SecureHash);

// Initialize database connection and payment handler
try {
    $db = new connect_db();
    $paymentHandler = new PaymentHandler();
    error_log('Database connection and payment handler initialized');
} catch (Exception $e) {
    error_log('Error initializing: ' . $e->getMessage());
    $redirectUrl = '../../index.php?page=dsdonhang';
    echo "<script>alert('Lỗi khởi tạo hệ thống: " . addslashes($e->getMessage()) . "'); window.location.href='$redirectUrl';</script>";
    exit;
}

// Verify the hash
if ($hashData === $vnp_SecureHash) {
    error_log('Hash verification successful');
    
    // Extract data from the callback
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
    $vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
    $vnp_Amount = ($_GET['vnp_Amount'] ?? 0) / 100; // Convert from VND cents to VND
    $vnp_OrderInfo = $_GET['vnp_OrderInfo'] ?? '';
    $vnp_PayDate = $_GET['vnp_PayDate'] ?? '';
    $vnp_BankCode = $_GET['vnp_BankCode'] ?? '';
    $vnp_CardType = $_GET['vnp_CardType'] ?? '';
    
    // Extract the order ID from the transaction reference
    $vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
    $idDH = 0;

    // Parse the transaction reference to get the order ID
    if (strpos($vnp_TxnRef, '_') !== false) {
        $idDH = (int)explode('_', $vnp_TxnRef)[0];
    }
    
    // If we can't get the order ID from the transaction reference, try session
    if (!$idDH && isset($_SESSION['payment_info']['idDH'])) {
        $idDH = (int)$_SESSION['payment_info']['idDH'];
    }
    
    // If still no order ID, try to extract it from the order info
    if (!$idDH && preg_match('/Thanh toan don hang (\d+)/', $vnp_OrderInfo, $matches)) {
        $idDH = (int)$matches[1];
    }
    
    // If still no order ID, redirect to error
    if (!$idDH) {
        error_log('Invalid idDH from OrderInfo: ' . $vnp_OrderInfo);
        $redirectUrl = '../../index.php?page=dsdonhang';
        echo "<script>alert('Không tìm thấy mã đơn hàng hợp lệ.'); window.location.href='$redirectUrl';</script>";
        exit;
    }

    // Save VNPay parameters to session for display in chitietdonhang.php
    $_SESSION['vnpay_params'] = [
        'vnp_Amount' => $_GET['vnp_Amount'] ?? 0,
        'vnp_BankCode' => $vnp_BankCode,
        'vnp_TransactionNo' => $vnp_TransactionNo
    ];
    
    // Check if payment was successful
    if ($vnp_ResponseCode === '00') {
        try {
            // Add additional logging
            error_log("Processing VNPay payment for order #$idDH, amount: $vnp_Amount, transaction: $vnp_TransactionNo");
            
            // Tạo mã đơn hàng mới
            $currentDate = date('dmy');
            $currentTime = date('His');
            $maDonHang = $currentDate . '-' . $currentTime;
            
            // Cập nhật mã đơn hàng
            $updateMaDonHangSql = "UPDATE donhang SET MaDonHang = ? WHERE idDH = ?";
            $db->tuychinh($updateMaDonHangSql, [$maDonHang, $idDH]);
            
            // Xử lý thanh toán VNPay bằng cách sử dụng PaymentHandler
            $result = $paymentHandler->processVnPayPayment($idDH, $vnp_Amount, $vnp_TransactionNo, $vnp_BankCode);
            
            if ($result['status']) {
                // Set session variables for success notification
                $_SESSION['payment_success'] = true;
                $_SESSION['payment_method'] = 'vnpay';
                
                // Chuyển đến trang thanh toán thành công
                header("Location: payment_success.php");
                exit;
            } else {
                throw new Exception($result['message']);
            }
        } catch (Exception $e) {
            // Log the error
            error_log('Payment Processing Error: ' . $e->getMessage());
            
            // Redirect to error page
            $errorRedirectUrl = '../../index.php?page=xemDH&idDH=' . $idDH;
            echo "<script>
                    alert('Lỗi xử lý thanh toán: " . addslashes($e->getMessage()) . "');
                    window.location.href='$errorRedirectUrl';
                  </script>";
            exit;
        }
    } else {
        // Payment failed
        error_log('Payment Failed - Response Code: ' . $vnp_ResponseCode);
        
        // Redirect to error page
        $failedRedirectUrl = '../../index.php?page=xemDH&idDH=' . $idDH;
        echo "<script>
                alert('Giao dịch không thành công. Mã lỗi: " . $vnp_ResponseCode . "');
                window.location.href='$failedRedirectUrl';
              </script>";
        exit;
    }
} else {
    // Invalid hash
    error_log('Invalid hash signature');
    error_log('Expected: ' . $hashData);
    error_log('Received: ' . $vnp_SecureHash);
    
    // Redirect to error page
    $invalidRedirectUrl = '../../index.php?page=dsdonhang';
    echo "<script>
            alert('Chữ ký không hợp lệ');
            window.location.href='$invalidRedirectUrl';
          </script>";
    exit;
}
?>