<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kết nối tới database
$mysqli = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
if ($mysqli->connect_error) {
    die("Database connection error: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");

// Khai báo biến kết quả
$result_message = '';
$booking_info = null;
$is_processed = false;

// Lấy ID đặt bàn từ parameter hoặc form
$bookingId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 34;

// Kiểm tra và thêm đơn hàng khi form được submit
if (isset($_POST['add_order'])) {
    $bookingId = (int)$_POST['booking_id'];
    
    try {
        // Bắt đầu transaction
        $mysqli->begin_transaction();
        
        // 1. Lấy thông tin đặt bàn
        $bookingQuery = "SELECT * FROM datban WHERE madatban = ?";
        $bookingStmt = $mysqli->prepare($bookingQuery);
        $bookingStmt->bind_param('i', $bookingId);
        $bookingStmt->execute();
        $bookingResult = $bookingStmt->get_result();
        
        if ($bookingResult->num_rows === 0) {
            throw new Exception("Không tìm thấy thông tin đặt bàn với mã $bookingId");
        }
        
        $booking = $bookingResult->fetch_assoc();
        $bookingStmt->close();
        
        // 2. Kiểm tra xem đã tồn tại đơn hàng chưa
        $checkOrderQuery = "SELECT * FROM donhang WHERE MaDonHang LIKE ?";
        $searchPattern = '%' . $bookingId . '%';
        $checkOrderStmt = $mysqli->prepare($checkOrderQuery);
        $checkOrderStmt->bind_param('s', $searchPattern);
        $checkOrderStmt->execute();
        $orderExists = $checkOrderStmt->get_result()->num_rows > 0;
        $checkOrderStmt->close();
        
        if ($orderExists) {
            throw new Exception("Đã tồn tại đơn hàng cho đặt bàn này");
        }
        
        // 3. Lấy thông tin khách hàng
        $customerQuery = "SELECT * FROM khachhang WHERE sodienthoai = ? OR email = ? LIMIT 1";
        $customerStmt = $mysqli->prepare($customerQuery);
        $customerStmt->bind_param('ss', $booking['sodienthoai'], $booking['email']);
        $customerStmt->execute();
        $customerResult = $customerStmt->get_result();
        
        // Nếu không tìm thấy khách hàng, tạo mới
        if ($customerResult->num_rows === 0) {
            $insertCustomerQuery = "INSERT INTO khachhang (tenKH, sodienthoai, email) VALUES (?, ?, ?)";
            $insertCustomerStmt = $mysqli->prepare($insertCustomerQuery);
            $insertCustomerStmt->bind_param('sss', $booking['tenKH'], $booking['sodienthoai'], $booking['email']);
            $insertCustomerStmt->execute();
            $idKH = $mysqli->insert_id;
            $insertCustomerStmt->close();
        } else {
            $customer = $customerResult->fetch_assoc();
            $idKH = $customer['idKH'];
        }
        $customerStmt->close();
        
        // 4. Tạo mã đơn hàng
        $maDonHang = date('ymd-His-') . $bookingId;
        $soHoaDon = date('His');
        $trangThai = 'Chờ xử lý';
        $ngayDatHang = date('Y-m-d H:i:s', strtotime($booking['NgayDatBan']));
        $idban = (int)$booking['idban'];
        $tongTien = (float)$booking['TongTien'];
        
        // 5. Thêm đơn hàng
        $insertOrderSQL = "INSERT INTO donhang (idKH, idban, NgayDatHang, TongTien, TrangThai, MaDonHang, SoHoaDon) 
                          VALUES ($idKH, $idban, '$ngayDatHang', $tongTien, '$trangThai', '$maDonHang', '$soHoaDon')";
        
        if (!$mysqli->query($insertOrderSQL)) {
            throw new Exception("Không thể thêm đơn hàng: " . $mysqli->error);
        }
        
        $idDH = $mysqli->insert_id;
        
        // 6. Lấy chi tiết món ăn đã đặt
        $dishesQuery = "SELECT * FROM chitietdatban WHERE madatban = ?";
        $dishesStmt = $mysqli->prepare($dishesQuery);
        $dishesStmt->bind_param('i', $bookingId);
        $dishesStmt->execute();
        $dishesResult = $dishesStmt->get_result();
        
        if ($dishesResult->num_rows === 0) {
            throw new Exception("Không tìm thấy thông tin món ăn đã đặt");
        }
        
        // 7. Thêm chi tiết đơn hàng
        while ($dish = $dishesResult->fetch_assoc()) {
            $idmonan = (int)$dish['idmonan'];
            $soLuong = (int)$dish['SoLuong'];
            
            $insertDetailSQL = "INSERT INTO chitietdonhang (idDH, idmonan, SoLuong) VALUES ($idDH, $idmonan, $soLuong)";
            if (!$mysqli->query($insertDetailSQL)) {
                throw new Exception("Không thể thêm chi tiết đơn hàng cho món $idmonan: " . $mysqli->error);
            }
        }
        $dishesStmt->close();
        
        // 8. Commit transaction
        $mysqli->commit();
        
        $result_message = "Đơn hàng đã được tạo thành công với ID: $idDH";
        $is_processed = true;
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $mysqli->rollback();
        $result_message = "Lỗi: " . $e->getMessage();
    }
}

// Lấy thông tin đặt bàn để hiển thị
try {
    $bookingQuery = "SELECT d.*, b.SoBan, kvb.TenKV as TenKhuVuc
                    FROM datban d 
                    LEFT JOIN ban b ON d.idban = b.idban
                    LEFT JOIN khuvucban kvb ON b.MaKV = kvb.MaKV
                    WHERE d.madatban = ?";
    $bookingStmt = $mysqli->prepare($bookingQuery);
    $bookingStmt->bind_param('i', $bookingId);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    
    if ($bookingResult->num_rows > 0) {
        $booking_info = $bookingResult->fetch_assoc();
    }
    $bookingStmt->close();
    
    // Lấy chi tiết món ăn
    if ($booking_info) {
        $dishesQuery = "SELECT cd.*, m.tenmonan 
                       FROM chitietdatban cd 
                       JOIN monan m ON cd.idmonan = m.idmonan 
                       WHERE cd.madatban = ?";
        $dishesStmt = $mysqli->prepare($dishesQuery);
        $dishesStmt->bind_param('i', $bookingId);
        $dishesStmt->execute();
        $dishesResult = $dishesStmt->get_result();
        
        $dishes = [];
        while ($dish = $dishesResult->fetch_assoc()) {
            $dishes[] = $dish;
        }
        $dishesStmt->close();
    }
    
    // Kiểm tra xem đã có đơn hàng chưa
    $checkOrderQuery = "SELECT * FROM donhang WHERE MaDonHang LIKE ?";
    $searchPattern = '%' . $bookingId . '%';
    $checkOrderStmt = $mysqli->prepare($checkOrderQuery);
    $checkOrderStmt->bind_param('s', $searchPattern);
    $checkOrderStmt->execute();
    $orderExists = $checkOrderStmt->get_result()->num_rows > 0;
    $checkOrderStmt->close();
    
} catch (Exception $e) {
    $result_message = "Lỗi khi lấy thông tin đặt bàn: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm đơn hàng thủ công</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; }
        .alert-danger { background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1; }
        .alert-warning { background-color: #fcf8e3; color: #8a6d3b; border: 1px solid #faebcc; }
        .btn { display: inline-block; padding: 10px 15px; margin: 10px 0; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn:hover { background-color: #0069d9; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thêm đơn hàng thủ công từ đặt bàn</h1>
        
        <?php if ($result_message): ?>
            <div class="alert <?php echo $is_processed ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $result_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($orderExists): ?>
            <div class="alert alert-warning">
                <strong>Lưu ý:</strong> Đã tồn tại đơn hàng cho đặt bàn này. Bạn có thể kiểm tra tại 
                <a href="check_order.php?id=<?php echo $bookingId; ?>">đây</a>.
            </div>
        <?php endif; ?>
        
        <form method="get">
            <label for="id">Nhập mã đặt bàn:</label>
            <input type="number" id="id" name="id" value="<?php echo $bookingId; ?>" required>
            <button type="submit">Kiểm tra</button>
        </form>
        
        <?php if ($booking_info): ?>
            <h2>Thông tin đặt bàn (Mã: <?php echo $bookingId; ?>)</h2>
            
            <h3>Thông tin khách hàng:</h3>
            <table>
                <tr>
                    <th>Họ tên:</th>
                    <td><?php echo htmlspecialchars($booking_info['tenKH']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($booking_info['email']); ?></td>
                </tr>
                <tr>
                    <th>Số điện thoại:</th>
                    <td><?php echo htmlspecialchars($booking_info['sodienthoai']); ?></td>
                </tr>
            </table>
            
            <h3>Thông tin bàn:</h3>
            <table>
                <tr>
                    <th>Số bàn:</th>
                    <td><?php echo htmlspecialchars($booking_info['SoBan']); ?></td>
                </tr>
                <tr>
                    <th>Khu vực:</th>
                    <td><?php echo htmlspecialchars($booking_info['TenKhuVuc']); ?></td>
                </tr>
                <tr>
                    <th>Ngày đặt:</th>
                    <td><?php echo htmlspecialchars($booking_info['NgayDatBan']); ?></td>
                </tr>
                <tr>
                    <th>Số lượng khách:</th>
                    <td><?php echo htmlspecialchars($booking_info['SoLuongKhach']); ?></td>
                </tr>
                <tr>
                    <th>Tổng tiền:</th>
                    <td><?php echo number_format($booking_info['TongTien'], 0, ',', '.'); ?> VND</td>
                </tr>
                <tr>
                    <th>Trạng thái:</th>
                    <td><?php echo htmlspecialchars($booking_info['TrangThai']); ?></td>
                </tr>
            </table>
            
            <?php if (isset($dishes) && count($dishes) > 0): ?>
                <h3>Danh sách món ăn:</h3>
                <table>
                    <tr>
                        <th>Món ăn</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                    <?php 
                    $total = 0;
                    foreach ($dishes as $dish): 
                        $amount = $dish['SoLuong'] * $dish['DonGia'];
                        $total += $amount;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dish['tenmonan']); ?></td>
                            <td><?php echo $dish['SoLuong']; ?></td>
                            <td><?php echo number_format($dish['DonGia'], 0, ',', '.'); ?> VND</td>
                            <td><?php echo number_format($amount, 0, ',', '.'); ?> VND</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th colspan="3" style="text-align: right;">Tổng cộng:</th>
                        <td><strong><?php echo number_format($total, 0, ',', '.'); ?> VND</strong></td>
                    </tr>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">Không tìm thấy thông tin món ăn</div>
            <?php endif; ?>
            
            <?php if (!$orderExists && isset($dishes) && count($dishes) > 0): ?>
                <form method="post">
                    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                    <button type="submit" name="add_order" class="btn">Thêm đơn hàng thủ công</button>
                </form>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-warning">Không tìm thấy thông tin đặt bàn với mã <?php echo $bookingId; ?></div>
        <?php endif; ?>
        
        <p>
            <a href="check_order.php" class="btn">Kiểm tra đơn hàng</a>
            <a href="test_order.php" class="btn">Kiểm tra cấu trúc bảng</a>
        </p>
    </div>
</body>
</html>

<?php
// Đóng kết nối
$mysqli->close();
?> 