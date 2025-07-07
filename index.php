<?php
// File này được tạo để xử lý việc chuyển hướng từ VNPay

// Ghi log yêu cầu đến
$log_file = "redirect_log.txt";
$log_message = date("Y-m-d H:i:s") . " - Request from: " . $_SERVER['REMOTE_ADDR'] . "\n";
$log_message .= "GET: " . print_r($_GET, true) . "\n";
$log_message .= "POST: " . print_r($_POST, true) . "\n";
$log_message .= "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
$log_message .= "----------------------------------------\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

// Bắt đầu session
session_start();

// Kiểm tra nếu là yêu cầu từ VNPay
if (isset($_GET['vnp_ResponseCode'])) {
    // Đây là chuyển hướng từ VNPay, xử lý thanh toán
    $idDH = 0;
    
    // Lấy ID đơn hàng từ vnp_TxnRef (định dạng idDH_timestamp)
    if (isset($_GET['vnp_TxnRef']) && strpos($_GET['vnp_TxnRef'], '_') !== false) {
        $idDH = (int)explode('_', $_GET['vnp_TxnRef'])[0];
    }
    
    // Nếu không có idDH hợp lệ, chuyển đến trang danh sách đơn hàng
    if (!$idDH) {
        header("Location: Admin/kaiadmin-lite-1.2.0/index.php?page=dsdonhang");
        exit;
    }
    
    // Lưu các tham số VNPay vào session để xử lý sau
    $_SESSION['vnpay_params'] = $_GET;

    // Xử lý thanh toán
    try {
        // Include database connection and payment handler class
        $root_path = $_SERVER['DOCUMENT_ROOT'] . '/CNM/Admin/kaiadmin-lite-1.2.0/';
        require_once $root_path . 'class/clsconnect.php';
        require_once $root_path . 'class/clsPayment.php';

        // VNPAY configuration
        $vnpayConfig = [
            'vnp_TmnCode' => 'A7RWSAH8',
            'vnp_HashSecret' => 'DCJD0IOT5N6FIT6YDS71JQ5JUH91L6H5'
        ];

        // Get the secure hash from the callback
        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
        
        // Process all input data from VNPAY
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        // Remove the secure hash from the input data
        unset($inputData['vnp_SecureHash']);
        
        // Sort the input data by key
        ksort($inputData);
        
        // Create a query string from the input data
        $query = http_build_query($inputData);
        
        // Generate a hash using the same method as in the payment.php
        $hashData = hash_hmac('sha512', $query, $vnpayConfig['vnp_HashSecret']);

        // Initialize database connection
        $db = new connect_db();
        
        // Verify the hash
        if ($hashData === $vnp_SecureHash) {
            // Extract data from the callback
            $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
            $vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
            $vnp_Amount = ($_GET['vnp_Amount'] ?? 0) / 100; // Convert from VND cents to VND
            $vnp_OrderInfo = $_GET['vnp_OrderInfo'] ?? '';
            $vnp_BankCode = $_GET['vnp_BankCode'] ?? '';
            
            // Check if payment was successful
            if ($vnp_ResponseCode === '00') {
                // Additional logging
                file_put_contents($log_file, "Processing VNPay payment for order #$idDH, amount: $vnp_Amount, transaction: $vnp_TransactionNo\n", FILE_APPEND);
                
                // 1. Cập nhật trạng thái đơn hàng
                $updateOrderSql = "UPDATE donhang SET TrangThai = 'Đã thanh toán' WHERE idDH = ?";
                $db->tuychinh($updateOrderSql, [$idDH]);
                
                // 2. Tạo mã đơn hàng mới
                $currentDate = date('dmy');
                $currentTime = date('His');
                $maDonHang = $currentDate . '-' . $currentTime;
                
                // Cập nhật mã đơn hàng
                $updateMaDonHangSql = "UPDATE donhang SET MaDonHang = ? WHERE idDH = ?";
                $db->tuychinh($updateMaDonHangSql, [$maDonHang, $idDH]);

                // 3. Lấy thông tin đơn hàng
                $orderSql = "SELECT d.*, k.tenKH, k.sodienthoai, k.email 
                            FROM donhang d 
                            JOIN khachhang k ON d.idKH = k.idKH 
                            WHERE d.idDH = ?";
                $orderResult = $db->xuatdulieu_prepared($orderSql, [$idDH]);
                $order = $orderResult[0];

                // 4. Tạo hóa đơn
                // Kiểm tra và tạo bảng hoadon nếu chưa tồn tại
                $checkTableSql = "SHOW TABLES LIKE 'hoadon'";
                $tableExists = $db->xuatdulieu($checkTableSql);
                if (empty($tableExists)) {
                    file_put_contents($log_file, "Creating hoadon table...\n", FILE_APPEND);
                    $createTableSql = "CREATE TABLE IF NOT EXISTS hoadon (
                        idHD INT AUTO_INCREMENT PRIMARY KEY,
                        idKH INT NOT NULL,
                        idDH INT NOT NULL,
                        Ngay DATETIME DEFAULT CURRENT_TIMESTAMP,
                        hinhthucthanhtoan VARCHAR(50) NOT NULL,
                        TongTien DECIMAL(10,2) NOT NULL,
                        INDEX(idDH)
                    )";
                    $db->tuychinh($createTableSql);
                }

                file_put_contents($log_file, "Creating invoice for order #$idDH with customer ID: {$order['idKH']}\n", FILE_APPEND);
                $hoadonSql = "INSERT INTO hoadon (idKH, idDH, Ngay, hinhthucthanhtoan, TongTien) 
                             VALUES (?, ?, NOW(), 'Chuyển khoản', ?)";
                $result = $db->tuychinh($hoadonSql, [$order['idKH'], $idDH, $order['TongTien']]);
                if ($result === false) {
                    file_put_contents($log_file, "Failed to create invoice. SQL Error: " . mysqli_error($db->conn) . "\n", FILE_APPEND);
                    throw new Exception("Không thể tạo hóa đơn");
                }
                $idHD = $db->getLastInsertId();
                file_put_contents($log_file, "Created invoice #$idHD\n", FILE_APPEND);

                // 5. Thêm chi tiết hóa đơn
                $cthdSql = "INSERT INTO chitiethoadon (idHD, idmonan, SoLuong, DonGia, ThanhTien) 
                           SELECT ?, c.idmonan, c.SoLuong, m.DonGia, (c.SoLuong * m.DonGia) 
                           FROM chitietdonhang c 
                           JOIN monan m ON c.idmonan = m.idmonan 
                           WHERE c.idDH = ?";
                $db->tuychinh($cthdSql, [$idHD, $idDH]);

                // 6. Thêm vào bảng thanh toán
                $thanhtoanSql = "INSERT INTO thanhtoan (idDH, SoTien, NgayThanhToan, PhuongThuc, TrangThai, MaGiaoDich) 
                                VALUES (?, ?, NOW(), 'Chuyển khoản', 'completed', ?)";
                $db->tuychinh($thanhtoanSql, [$idDH, $order['TongTien'], $vnp_TransactionNo]);

                // 7. Ghi log giao dịch
                $logSql = "INSERT INTO log_giaodich (idDH, SoTien, PhuongThuc, MaGiaoDich, GhiChu, ThoiGian, TrangThai) 
                          VALUES (?, ?, 'Chuyển khoản', ?, ?, NOW(), 'completed')";
                $chiTiet = "Thanh toán đơn hàng #$idDH qua VNPay";
                $db->tuychinh($logSql, [$idDH, $order['TongTien'], $vnp_TransactionNo, $chiTiet]);

                // Set session variables for success notification
                $_SESSION['payment_success'] = true;
                $_SESSION['payment_method'] = 'vnpay';
                
                // Chuyển đến trang thanh toán thành công
                header("Location: Admin/kaiadmin-lite-1.2.0/page/thanhtoan/payment_success.php");
                exit;
            } else {
                // Payment failed
                // Redirect to error page
                header("Location: Admin/kaiadmin-lite-1.2.0/index.php?page=xemDH&idDH=$idDH&payment_failed=1");
                exit;
            }
        } else {
            // Invalid hash
            header("Location: Admin/kaiadmin-lite-1.2.0/index.php?page=dsdonhang&hash_error=1");
            exit;
        }
    } catch (Exception $e) {
        // Log the error
        file_put_contents($log_file, "Payment error: " . $e->getMessage() . "\n", FILE_APPEND);
        
        // Redirect to error page
        header("Location: Admin/kaiadmin-lite-1.2.0/index.php?page=dsdonhang&error=" . urlencode($e->getMessage()));
        exit;
    }
}
// Kiểm tra nếu là yêu cầu xem đơn hàng
else if (isset($_GET['page']) && $_GET['page'] == 'xemDH' && isset($_GET['idDH'])) {
    $idDH = (int)$_GET['idDH'];
    
    // Chuyển tiếp tham số trangthai nếu có
    $trangthai_param = isset($_GET['trangthai']) ? '&trangthai=' . urlencode($_GET['trangthai']) : '';
    
    header("Location: Admin/kaiadmin-lite-1.2.0/index.php?page=xemDH&idDH=$idDH$trangthai_param");
    exit;
} 
// Mặc định chuyển hướng đến trang Admin
else {
    header("Location: Admin/kaiadmin-lite-1.2.0/index.php");
    exit;
}
?> 