<?php
// This file handles redirects after payment processing to avoid output buffering issues

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log the redirect
$log_file = $_SERVER['DOCUMENT_ROOT'] . '/CNM/redirect_log.txt';
$log_data = date('Y-m-d H:i:s') . " - Redirect triggered\n";
$log_data .= "GET: " . print_r($_GET, true) . "\n";
$log_data .= "SESSION: " . print_r($_SESSION, true) . "\n";
$log_data .= "----------------------------------------\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

// Get parameters
$idDH = isset($_GET['idDH']) ? intval($_GET['idDH']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Set session variables for success message
if ($status == 'success') {
    $_SESSION['payment_success'] = true;
    $_SESSION['payment_method'] = isset($_GET['method']) ? $_GET['method'] : 'cash';
    $_SESSION['payment_amount'] = isset($_GET['amount']) ? $_GET['amount'] : '';
    
    // Redirect to order details page using JavaScript
    echo "<script>
        window.location.href = 'index.php?page=xemDH&idDH=$idDH&trangthai=Đã thanh toán';
    </script>";
} else {
    // Redirect back to payment page with error using JavaScript
    echo "<script>
        alert('Lỗi xử lý thanh toán: " . addslashes($message) . "');
        window.location.href = 'index.php?page=thanhtoan/payment&idDH=$idDH';
    </script>";
}
?> 