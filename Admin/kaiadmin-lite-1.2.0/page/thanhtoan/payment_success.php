<?php
session_start();

// Kiểm tra nếu không có thông tin thanh toán trong session
if (!isset($_SESSION['vnpay_params']) && !isset($_SESSION['payment_success'])) {
    header("Location: ../../index.php?page=dsdonhang");
    exit;
}

// Lấy thông tin thanh toán VNPay
$payment_info = isset($_SESSION['vnpay_params']) ? $_SESSION['vnpay_params'] : [];

// Lấy ID đơn hàng từ vnp_TxnRef (định dạng idDH_timestamp)
$idDH = 0;
if (!empty($payment_info['vnp_TxnRef']) && strpos($payment_info['vnp_TxnRef'], '_') !== false) {
    $idDH = (int)explode('_', $payment_info['vnp_TxnRef'])[0];
}

// Nếu không có idDH, thử lấy từ session payment_info
if (!$idDH && isset($_SESSION['payment_info']['idDH'])) {
    $idDH = $_SESSION['payment_info']['idDH'];
}

// Nếu vẫn không có idDH, chuyển về trang danh sách đơn hàng
if (!$idDH) {
    header("Location: ../../index.php?page=dsdonhang");
    exit;
}

// Lấy thông tin thanh toán với kiểm tra null
$vnp_Amount = !empty($payment_info['vnp_Amount']) ? number_format(($payment_info['vnp_Amount'] / 100), 0, ',', '.') : '0';
$vnp_BankCode = !empty($payment_info['vnp_BankCode']) ? $payment_info['vnp_BankCode'] : 'N/A';
$vnp_TransactionNo = !empty($payment_info['vnp_TransactionNo']) ? $payment_info['vnp_TransactionNo'] : 'N/A';

// Kết nối database để lấy thông tin đơn hàng
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Lấy thông tin đơn hàng
$sql = "SELECT d.*, k.tenKH, k.sodienthoai, k.email, b.SoBan 
        FROM donhang d 
        JOIN khachhang k ON d.idKH = k.idKH 
        JOIN ban b ON d.idban = b.idban 
        WHERE d.idDH = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $idDH);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

// Xóa thông tin thanh toán khỏi session sau khi đã lấy
// Không xóa để tránh gây lỗi nếu người dùng reload trang
// unset($_SESSION['vnpay_params']);
// unset($_SESSION['payment_success']);
// unset($_SESSION['payment_method']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công - Restoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .payment-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn-primary {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        .btn-primary:hover {
            background-color: #e0a800;
            border-color: #e0a800;
            color: #000;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="success-container text-center">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="mb-4">Thanh toán thành công!</h2>
            
            <div class="payment-details text-start">
                <h4 class="mb-3">Thông tin đơn hàng</h4>
                <p><strong>Mã đơn hàng:</strong> #<?php echo $idDH; ?></p>
                <?php if ($order): ?>
                <p><strong>Khách hàng:</strong> <?php echo $order['tenKH']; ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo $order['sodienthoai']; ?></p>
                <p><strong>Bàn số:</strong> <?php echo $order['SoBan']; ?></p>
                <?php endif; ?>
                <p><strong>Số tiền:</strong> <?php echo $vnp_Amount; ?> VND</p>
                <p><strong>Ngân hàng:</strong> <?php echo $vnp_BankCode; ?></p>
                <p><strong>Mã giao dịch:</strong> <?php echo $vnp_TransactionNo; ?></p>
                <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>

            
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Tự động chuyển hướng sau 10 giây
        setTimeout(function() {
            window.location.href = '../../index.php?page=xemDH&idDH=<?php echo $idDH; ?>&trangthai=Đã thanh toán';
        }, 100000);
    </script>
</body>
</html> 