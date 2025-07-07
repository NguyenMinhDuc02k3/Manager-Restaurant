<?php
// Standalone file for AJAX processing of menu item filters
// This prevents "headers already sent" errors

// Kết nối cơ sở dữ liệu
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "Kết nối CSDL thất bại";
    exit;
}
mysqli_set_charset($conn, "utf8");

// Lấy danh sách món ăn theo bộ lọc
$danhMucSelected = isset($_POST['danhmuc']) ? $_POST['danhmuc'] : '';
$searchKeyword = isset($_POST['search']) ? $_POST['search'] : '';

// Xác định truy vấn dựa vào bộ lọc
if (!empty($danhMucSelected) && !empty($searchKeyword)) {
    // Tìm kiếm món ăn theo tên và danh mục
    $sqlMonAn = "SELECT * FROM monan WHERE tenmonan LIKE ? AND iddm = ? AND TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
    $searchParam = "%$searchKeyword%";
    mysqli_stmt_bind_param($stmtMonAn, "si", $searchParam, $danhMucSelected);
} elseif (!empty($danhMucSelected)) {
    // Lấy món ăn theo danh mục
    $sqlMonAn = "SELECT * FROM monan WHERE iddm = ? AND TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
    mysqli_stmt_bind_param($stmtMonAn, "i", $danhMucSelected);
} elseif (!empty($searchKeyword)) {
    // Tìm kiếm món ăn theo tên
    $sqlMonAn = "SELECT * FROM monan WHERE tenmonan LIKE ? AND TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
    $searchParam = "%$searchKeyword%";
    mysqli_stmt_bind_param($stmtMonAn, "s", $searchParam);
} else {
    // Lấy tất cả món ăn mặc định
    $sqlMonAn = "SELECT * FROM monan WHERE TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
}

mysqli_stmt_execute($stmtMonAn);
$resultMonAn = mysqli_stmt_get_result($stmtMonAn);
$monAnList = [];
if ($resultMonAn) {
    $monAnList = mysqli_fetch_all($resultMonAn, MYSQLI_ASSOC);
}

// Trả về HTML trực tiếp
header('Content-Type: text/html; charset=UTF-8');

if (empty($monAnList)) {
    echo '<div class="col-12 text-center"><p>Không tìm thấy món ăn nào.</p></div>';
} else {
    foreach ($monAnList as $mon) {
        ?>
        <div class="col-md-6 col-lg-4 mb-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <img src="<?= !empty($mon['hinhanh']) ? 'assets/img/' . htmlspecialchars($mon['hinhanh']) : 'assets/img/default-food.jpg' ?>" 
                            alt="<?= htmlspecialchars($mon['tenmonan']) ?>" 
                            class="rounded me-2" style="width: 45px; height: 45px; object-fit: cover;">
                        <div>
                            <h6 class="card-title mb-0 text-truncate" title="<?= htmlspecialchars($mon['tenmonan']) ?>"><?= htmlspecialchars($mon['tenmonan']) ?></h6>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="fw-bold text-primary"><?= number_format($mon['DonGia'], 0, ',', '.') ?> VNĐ</span>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1 me-1" style="width: 28px; height: 28px; line-height: 1;" 
                                            data-id="<?= $mon['idmonan'] ?>" 
                                            data-name="<?= htmlspecialchars($mon['tenmonan']) ?>" 
                                            data-price="<?= $mon['DonGia'] ?>"><i class="fas fa-minus"></i></button>
                                    <span class="fw-bold px-2" id="qty-<?= $mon['idmonan'] ?>" style="min-width: 25px; text-align: center;">0</span>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-circle p-1 ms-1" style="width: 28px; height: 28px; line-height: 1;" 
                                            data-id="<?= $mon['idmonan'] ?>" 
                                            data-name="<?= htmlspecialchars($mon['tenmonan']) ?>" 
                                            data-price="<?= $mon['DonGia'] ?>"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

mysqli_close($conn);
exit; 