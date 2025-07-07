<?php
// Thêm logic chặn URL
session_start();
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
$queryRole = "SELECT v.quyen FROM nhanvien n JOIN vaitro v ON n.idvaitro = v.idvaitro WHERE n.idnv = ?";
$stmt = mysqli_prepare($conn, $queryRole);
mysqli_stmt_bind_param($stmt, "i", $idnv);
mysqli_stmt_execute($stmt);
$resultRole = mysqli_stmt_get_result($stmt);
$roleData = mysqli_fetch_assoc($resultRole);
$permissions = $roleData && !empty($roleData['quyen']) ? explode(",", $roleData['quyen']) : [];

function hasPermission($perm, $permissions) {
    return in_array(trim($perm), array_map('trim', $permissions));
}

// Kiểm tra quyền xem lịch sử thanh toán
if (!hasPermission('Thanh toan don hang', $permissions)) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
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
require_once $root_path . 'class/clsPayment.php';

// Khởi tạo đối tượng PaymentHandler
$paymentHandler = new PaymentHandler();

// Lấy thông tin đơn hàng
$db = new connect_db();
$sql = "SELECT d.*, k.tenKH, k.sodienthoai, b.SoBan 
        FROM donhang d 
        JOIN khachhang k ON d.idKH = k.idKH 
        JOIN ban b ON d.idban = b.idban 
        WHERE d.idDH = ?";
$orderInfo = $db->xuatdulieu_prepared($sql, [$idDH]);

if (empty($orderInfo)) {
    echo "<script>alert('Không tìm thấy thông tin đơn hàng'); window.location.href='index.php?page=dsdonhang';</script>";
    exit;
}
$orderInfo = $orderInfo[0];

// Lấy lịch sử thanh toán
$paymentHistory = $paymentHandler->getPaymentHistory($idDH);

// Lấy thông tin hóa đơn
$sql = "SELECT h.*, nv.TenNV 
        FROM hoadon h 
        LEFT JOIN nhanvien nv ON h.idNV = nv.idNV 
        WHERE h.idDH = ? 
        ORDER BY h.ngay DESC";
$invoiceHistory = $db->xuatdulieu_prepared($sql, [$idDH]);

// Lấy lịch sử giao dịch từ bảng log nếu có
$sql = "SHOW TABLES LIKE 'log_giaodich'";
$hasLogTable = $db->xuatdulieu($sql);

$transactionLogs = [];
if (!empty($hasLogTable)) {
    $sql = "SELECT l.*, nv.TenNV 
            FROM log_giaodich l 
            LEFT JOIN nhanvien nv ON l.IDNhanVien = nv.idNV 
            WHERE l.idDH = ? 
            ORDER BY l.ThoiGian DESC";
    $transactionLogs = $db->xuatdulieu_prepared($sql, [$idDH]);
}

// Hàm format số tiền
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VND';
}

// Hàm format thời gian
function formatDateTime($datetime) {
    if (empty($datetime)) return '';
    $date = new DateTime($datetime);
    return $date->format('d/m/Y H:i:s');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử thanh toán - Đơn hàng #<?php echo $idDH; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container { max-width: 900px; }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: black;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .payment-method {
            font-weight: bold;
            text-transform: uppercase;
        }
        .timeline {
            position: relative;
            margin: 0 0 20px 20px;
            padding: 0;
            list-style: none;
        }
        .timeline > li {
            position: relative;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-left: 2px solid #e9ecef;
            padding-left: 20px;
        }
        .timeline > li:before {
            content: '';
            position: absolute;
            left: -9px;
            top: 0;
            background-color: #007bff;
            height: 16px;
            width: 16px;
            border-radius: 50%;
            border: 2px solid white;
        }
        .timeline > li:last-child {
            border-left: 2px solid transparent;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Lịch sử thanh toán - Đơn hàng #<?php echo $idDH; ?></h3>
            <a href="index.php?page=xemDH&idDH=<?php echo $idDH; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Quay lại
            </a>
        </div>
        
        <!-- Thông tin đơn hàng -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Thông tin đơn hàng
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Mã đơn hàng:</strong> <?php echo $idDH; ?></p>
                        <p><strong>Khách hàng:</strong> <?php echo $orderInfo['tenKH']; ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo $orderInfo['sodienthoai']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ngày đặt:</strong> <?php echo formatDateTime($orderInfo['NgayDatHang']); ?></p>
                        <p><strong>Bàn số:</strong> <?php echo $orderInfo['SoBan']; ?></p>
                        <p>
                            <strong>Trạng thái:</strong> 
                            <span class="badge <?php 
                                echo $orderInfo['TrangThai'] === 'Đã thanh toán' ? 'badge-success' : 
                                    ($orderInfo['TrangThai'] === 'Đã giao' ? 'badge-warning' : 'badge-secondary'); 
                            ?>">
                                <?php echo $orderInfo['TrangThai']; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lịch sử thanh toán -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-money-bill-wave me-2"></i> Lịch sử thanh toán
            </div>
            <div class="card-body">
                <?php if (empty($paymentHistory)): ?>
                    <div class="alert alert-info">Không có lịch sử thanh toán nào được ghi nhận.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Phương thức</th>
                                    <th>Số tiền</th>
                                    <th>Mã giao dịch</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paymentHistory as $payment): ?>
                                    <tr>
                                        <td><?php echo formatDateTime($payment['NgayThanhToan']); ?></td>
                                        <td>
                                            <span class="payment-method">
                                                <?php 
                                                    if ($payment['PhuongThuc'] === 'vnpay') {
                                                        echo '<i class="fas fa-credit-card me-1"></i> VNPay';
                                                    } elseif ($payment['PhuongThuc'] === 'Tiền mặt') {
                                                        echo '<i class="fas fa-money-bill-alt me-1"></i> Tiền mặt';
                                                    } else {
                                                        echo $payment['PhuongThuc'];
                                                    }
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatCurrency($payment['SoTien']); ?></td>
                                        <td><?php echo $payment['MaGiaoDich'] ?: 'N/A'; ?></td>
                                        <td>
                                            <span class="badge <?php echo $payment['TrangThai'] === 'completed' ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo $payment['TrangThai'] === 'completed' ? 'Thành công' : $payment['TrangThai']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Thông tin hóa đơn -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-file-invoice me-2"></i> Thông tin hóa đơn
            </div>
            <div class="card-body">
                <?php if (empty($invoiceHistory)): ?>
                    <div class="alert alert-info">Không có thông tin hóa đơn nào được ghi nhận.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã hóa đơn</th>
                                    <th>Ngày lập</th>
                                    <th>Hình thức thanh toán</th>
                                    <th>Tổng tiền</th>
                                    <th>Nhân viên</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoiceHistory as $invoice): ?>
                                    <tr>
                                        <td><?php echo $invoice['idHD']; ?></td>
                                        <td><?php echo formatDateTime($invoice['ngay']); ?></td>
                                        <td>
                                            <?php 
                                                if ($invoice['hinhthucthanhtoan'] === 'Chuyển khoản') {
                                                    echo '<i class="fas fa-credit-card me-1"></i> Chuyển khoản';
                                                } elseif ($invoice['hinhthucthanhtoan'] === 'Tiền mặt') {
                                                    echo '<i class="fas fa-money-bill-alt me-1"></i> Tiền mặt';
                                                } else {
                                                    echo $invoice['hinhthucthanhtoan'];
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo formatCurrency($invoice['TongTien']); ?></td>
                                        <td><?php echo $invoice['TenNV'] ?: 'N/A'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Nhật ký giao dịch -->
        <?php if (!empty($transactionLogs)): ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Nhật ký giao dịch
            </div>
            <div class="card-body">
                <ul class="timeline">
                    <?php foreach ($transactionLogs as $log): ?>
                        <li>
                            <div>
                                <strong><?php echo formatDateTime($log['ThoiGian']); ?></strong>
                                <p class="mb-0">
                                    <?php 
                                        if ($log['PhuongThuc'] === 'vnpay') {
                                            echo '<i class="fas fa-credit-card me-1"></i> Thanh toán VNPay ';
                                        } elseif ($log['PhuongThuc'] === 'Tiền mặt') {
                                            echo '<i class="fas fa-money-bill-alt me-1"></i> Thanh toán tiền mặt ';
                                        } else {
                                            echo $log['PhuongThuc'] . ' ';
                                        }
                                        echo formatCurrency($log['SoTien']);
                                    ?>
                                </p>
                                <?php if (!empty($log['GhiChu'])): ?>
                                    <p class="text-muted mb-0"><?php echo $log['GhiChu']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($log['MaGiaoDich'])): ?>
                                    <p class="text-muted mb-0">Mã giao dịch: <?php echo $log['MaGiaoDich']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($log['MaNganHang'])): ?>
                                    <p class="text-muted mb-0">Ngân hàng: <?php echo $log['MaNganHang']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($log['TenNV'])): ?>
                                    <p class="text-muted mb-0">Người thực hiện: <?php echo $log['TenNV']; ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html> 