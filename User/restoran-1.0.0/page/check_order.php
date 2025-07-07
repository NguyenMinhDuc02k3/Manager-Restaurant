<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
$mysqli = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
if ($mysqli->connect_error) {
    die("Database connection error: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");

// Lấy ID đặt bàn từ parameter hoặc mặc định là 34
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 34;

// Kiểm tra đơn hàng theo mã đặt bàn
$searchPattern = '%' . $bookingId . '%';
$query = "SELECT * FROM donhang WHERE MaDonHang LIKE ? ORDER BY idDH DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $searchPattern);
$stmt->execute();
$result = $stmt->get_result();

// Hiển thị kết quả
echo "<h2>Kiểm tra đơn hàng với mã đặt bàn: $bookingId</h2>";

if ($result->num_rows > 0) {
    echo "<h3>Đã tìm thấy " . $result->num_rows . " đơn hàng:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    
    // Lấy thông tin cột
    $firstRow = $result->fetch_assoc();
    foreach (array_keys($firstRow) as $column) {
        echo "<th>$column</th>";
    }
    echo "</tr>";
    
    // Reset pointer
    $result->data_seek(0);
    
    // Hiển thị dữ liệu
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra chi tiết đơn hàng
    echo "<h3>Chi tiết đơn hàng:</h3>";
    
    $result->data_seek(0);
    while ($order = $result->fetch_assoc()) {
        $idDH = $order['idDH'];
        
        echo "<h4>Chi tiết cho đơn hàng ID: $idDH (Mã: {$order['MaDonHang']})</h4>";
        
        $detailQuery = "SELECT cd.*, m.tenmonan, m.DonGia 
                       FROM chitietdonhang cd 
                       JOIN monan m ON cd.idmonan = m.idmonan 
                       WHERE cd.idDH = ?";
        $detailStmt = $mysqli->prepare($detailQuery);
        $detailStmt->bind_param('i', $idDH);
        $detailStmt->execute();
        $detailResult = $detailStmt->get_result();
        
        if ($detailResult->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Tên món</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>";
            
            $totalAmount = 0;
            while ($detail = $detailResult->fetch_assoc()) {
                $amount = $detail['SoLuong'] * $detail['DonGia'];
                $totalAmount += $amount;
                
                echo "<tr>";
                echo "<td>{$detail['idCTDH']}</td>";
                echo "<td>{$detail['tenmonan']}</td>";
                echo "<td>{$detail['SoLuong']}</td>";
                echo "<td>" . number_format($detail['DonGia']) . " VND</td>";
                echo "<td>" . number_format($amount) . " VND</td>";
                echo "</tr>";
            }
            
            echo "<tr>";
            echo "<td colspan='4' style='text-align: right;'><strong>Tổng cộng:</strong></td>";
            echo "<td><strong>" . number_format($totalAmount) . " VND</strong></td>";
            echo "</tr>";
            
            echo "</table>";
        } else {
            echo "<p>Không tìm thấy chi tiết đơn hàng</p>";
        }
        
        $detailStmt->close();
    }
} else {
    echo "<p>Không tìm thấy đơn hàng nào với mã đặt bàn $bookingId</p>";
    
    // Kiểm tra đặt bàn
    $bookingQuery = "SELECT * FROM datban WHERE madatban = ?";
    $bookingStmt = $mysqli->prepare($bookingQuery);
    $bookingStmt->bind_param('i', $bookingId);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    
    if ($bookingResult->num_rows > 0) {
        $booking = $bookingResult->fetch_assoc();
        echo "<h3>Thông tin đặt bàn:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        foreach (array_keys($booking) as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";
        echo "<tr>";
        foreach ($booking as $key => $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
        echo "</table>";
        
        // Kiểm tra chi tiết đặt bàn
        $detailQuery = "SELECT cd.*, m.tenmonan 
                      FROM chitietdatban cd 
                      JOIN monan m ON cd.idmonan = m.idmonan 
                      WHERE cd.madatban = ?";
        $detailStmt = $mysqli->prepare($detailQuery);
        $detailStmt->bind_param('i', $bookingId);
        $detailStmt->execute();
        $detailResult = $detailStmt->get_result();
        
        if ($detailResult->num_rows > 0) {
            echo "<h3>Chi tiết đặt bàn:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Tên món</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>";
            
            $totalAmount = 0;
            while ($detail = $detailResult->fetch_assoc()) {
                $amount = $detail['SoLuong'] * $detail['DonGia'];
                $totalAmount += $amount;
                
                echo "<tr>";
                echo "<td>{$detail['idChiTiet']}</td>";
                echo "<td>{$detail['tenmonan']}</td>";
                echo "<td>{$detail['SoLuong']}</td>";
                echo "<td>" . number_format($detail['DonGia'], 2) . " VND</td>";
                echo "<td>" . number_format($amount, 2) . " VND</td>";
                echo "</tr>";
            }
            
            echo "<tr>";
            echo "<td colspan='4' style='text-align: right;'><strong>Tổng cộng:</strong></td>";
            echo "<td><strong>" . number_format($totalAmount, 2) . " VND</strong></td>";
            echo "</tr>";
            
            echo "</table>";
        }
        
        $detailStmt->close();
    } else {
        echo "<p>Không tìm thấy thông tin đặt bàn với mã $bookingId</p>";
    }
    
    $bookingStmt->close();
}

// Kiểm tra tất cả đơn hàng gần đây
echo "<h3>5 đơn hàng gần đây nhất:</h3>";
$recentQuery = "SELECT * FROM donhang ORDER BY idDH DESC LIMIT 5";
$recentResult = $mysqli->query($recentQuery);

if ($recentResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    $firstRow = $recentResult->fetch_assoc();
    foreach (array_keys($firstRow) as $column) {
        echo "<th>$column</th>";
    }
    echo "</tr>";
    
    $recentResult->data_seek(0);
    while ($row = $recentResult->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Không tìm thấy đơn hàng nào</p>";
}

// Thêm form để kiểm tra đơn hàng khác
echo "<h3>Kiểm tra đơn hàng khác:</h3>";
echo "<form method='get'>";
echo "<input type='number' name='id' placeholder='Nhập mã đặt bàn' required>";
echo "<button type='submit'>Kiểm tra</button>";
echo "</form>";

// Đóng kết nối
$mysqli->close();
?> 