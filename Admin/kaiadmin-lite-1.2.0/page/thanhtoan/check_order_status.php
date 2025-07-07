<?php
header('Content-Type: application/json');

// Kết nối database
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die(json_encode(['error' => 'Database connection failed']));
}
mysqli_set_charset($conn, "utf8");

// Kiểm tra ID đơn hàng
if (!isset($_GET['idDH'])) {
    die(json_encode(['error' => 'Missing order ID']));
}

$idDH = intval($_GET['idDH']);

// Lấy trạng thái đơn hàng
$sql = "SELECT TrangThai FROM donhang WHERE idDH = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $idDH);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['status' => $row['TrangThai']]);
} else {
    echo json_encode(['error' => 'Order not found']);
}

mysqli_close($conn);
?> 