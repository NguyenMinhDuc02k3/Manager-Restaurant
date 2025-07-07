<?php
// AJAX handler for customer search by phone number
header('Content-Type: application/json');

// Kết nối CSDL
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Kết nối CSDL thất bại']));
}
mysqli_set_charset($conn, "utf8");

// Lấy số điện thoại từ request
$sdt = isset($_POST['sdt']) ? trim($_POST['sdt']) : '';

if (empty($sdt)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập số điện thoại']);
    mysqli_close($conn);
    exit;
}

// Tìm khách hàng theo số điện thoại
$sql = "SELECT idKH, tenKH FROM khachhang WHERE sodienthoai = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $sdt);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true, 
        'data' => [
            'idKH' => $row['idKH'],
            'tenKH' => $row['tenKH']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => "Không tìm thấy khách hàng với số điện thoại '$sdt'"]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn); 