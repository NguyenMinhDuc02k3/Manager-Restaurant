<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set timezone to Vietnam
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

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

// Kiểm tra quyền xem đơn hàng
if (!hasPermission('Xem don hang', $permissions)) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}

// Kiểm tra vai trò quản lý (4) hoặc phục vụ (2)
$canUpdateOrderStatus = ($vaitro_id == 4 || $vaitro_id == 2);

// Xử lý xóa nếu có yêu cầu
if (isset($_POST['delete_idDH']) && $canUpdateOrderStatus && hasPermission('Xoa don hang', $permissions)) {
    $delete_id = intval($_POST['delete_idDH']);
    
    // Kiểm tra trạng thái đơn hàng trước khi xóa
    $checkStmt = $conn->prepare("SELECT TrangThai FROM donhang WHERE idDH = ?");
    $checkStmt->bind_param("i", $delete_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $orderStatus = $checkResult->fetch_assoc()['TrangThai'];
        
        // Cho phép xóa đơn hàng nếu chưa ở trạng thái "Đã thanh toán"
        if ($orderStatus != 'Đã thanh toán') {
            // Xóa chi tiết đơn hàng trước
            $deleteDetailStmt = $conn->prepare("DELETE FROM chitietdonhang WHERE idDH = ?");
            $deleteDetailStmt->bind_param("i", $delete_id);
            $deleteDetailStmt->execute();
            
            // Sau đó xóa đơn hàng
            $deleteOrderStmt = $conn->prepare("DELETE FROM donhang WHERE idDH = ?");
            $deleteOrderStmt->bind_param("i", $delete_id);
            $result = $deleteOrderStmt->execute();
            
            if ($result) {
                echo "<script>alert('Xóa đơn hàng thành công!'); window.location.href='index.php?page=dsdonhang';</script>";
            } else {
                echo "<script>alert('Lỗi xóa đơn hàng: " . $conn->error . "'); window.location.href='index.php?page=dsdonhang';</script>";
            }
        } else {
            echo "<script>alert('Không thể xóa đơn hàng này vì đã thanh toán!'); window.location.href='index.php?page=dsdonhang';</script>";
        }
    } else {
        echo "<script>alert('Không tìm thấy đơn hàng cần xóa!'); window.location.href='index.php?page=dsdonhang';</script>";
    }
    exit;
}

?>

<div class="container mb-5">
    <div class="mt-4">
        <div>
            <a class="btn <?= !isset($_GET['trangthai']) ? 'active' : '' ?>" href="index.php?page=dsdonhang"
                style="border:1px solid black; margin:0px 5px 0px 20px;">Tất cả</a>
            <a class="btn <?= isset($_GET['trangthai']) && $_GET['trangthai'] == 'Chờ xử lý' ? 'active' : '' ?>"
                href="index.php?page=dsdonhang&trangthai=Chờ xử lý"
                style="border:1px solid black; margin:0px 5px 0px 20px;">Chờ xử lý</a>
            <a class="btn <?= isset($_GET['trangthai']) && $_GET['trangthai'] == 'Đang chuẩn bị' ? 'active' : '' ?>"
                href="index.php?page=dsdonhang&trangthai=Đang chuẩn bị"
                style="border:1px solid black; margin:0px 5px 0px 20px;">Đang chuẩn bị</a>
            <a class="btn <?= isset($_GET['trangthai']) && $_GET['trangthai'] == 'Đã giao' ? 'active' : '' ?>"
                href="index.php?page=dsdonhang&trangthai=Đã giao"
                style="border:1px solid black; margin:0px 5px 0px 20px;">Đã giao</a>
            <a class="btn <?= isset($_GET['trangthai']) && $_GET['trangthai'] == 'Đã thanh toán' ? 'active' : '' ?>"
                href="index.php?page=dsdonhang&trangthai=Đã thanh toán"
                style="border:1px solid black; margin:0px 5px 0px 20px;">Đã thanh toán</a>
        </div>
        <div class="d-flex align-items-center justify-content-end mb-3 pe-5">
            <?php if (hasPermission('Them don hang', $permissions)): ?>
                <a href="index.php?page=themDH" class="d-flex align-items-center text-decoration-none">
                    <p class="mb-0 me-2"><b>Thêm</b></p>
                    <i class="icon-user-follow fs-4"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div style="overflow-x: auto; max-height: 100%">
        <table class="table table-head-bg-primary table-hover ms-3 me-3">
            <thead>
                <tr>
                    <th scope="col">Mã đơn hàng</th>
                    <th scope="col">Tên khách hàng</th>
                    <th scope="col">Ngày đặt</th>
                    <th scope="col">Trạng thái</th>
                    <?php if (hasPermission('Sua don hang', $permissions) || hasPermission('Xoa don hang', $permissions)): ?>
                        <th scope="col">Tùy chọn</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($conn) {
                    // Kiểm tra xem bảng donhang có cột MaDonHang chưa
                    $hasMaDonHang = false;
                    $columnsResult = $conn->query("SHOW COLUMNS FROM donhang LIKE 'MaDonHang'");
                    if ($columnsResult && $columnsResult->num_rows > 0) {
                        $hasMaDonHang = true;
                    }
                    
                    // Sửa truy vấn SQL để lấy thêm MaDonHang nếu có
                    if ($hasMaDonHang) {
                        $str = "SELECT d.idDH, d.MaDonHang, k.tenKH, DATE_FORMAT(d.NgayDatHang, '%Y-%m-%d %H:%i') AS NgayDatHang, d.TrangThai,
                                h.hinhthucthanhtoan AS PhuongThucThanhToan
                                FROM donhang d 
                                JOIN khachhang k ON d.idKH = k.idKH 
                                LEFT JOIN (
                                    SELECT idDH, hinhthucthanhtoan 
                                    FROM hoadon 
                                    WHERE idHD IN (
                                        SELECT MAX(idHD) 
                                        FROM hoadon 
                                        GROUP BY idDH
                                    )
                                ) h ON d.idDH = h.idDH";
                    } else {
                        $str = "SELECT d.idDH, k.tenKH, DATE_FORMAT(d.NgayDatHang, '%Y-%m-%d %H:%i') AS NgayDatHang, d.TrangThai,
                                h.hinhthucthanhtoan AS PhuongThucThanhToan
                                FROM donhang d 
                                JOIN khachhang k ON d.idKH = k.idKH 
                                LEFT JOIN (
                                    SELECT idDH, hinhthucthanhtoan 
                                    FROM hoadon 
                                    WHERE idHD IN (
                                        SELECT MAX(idHD) 
                                        FROM hoadon 
                                        GROUP BY idDH
                                    )
                                ) h ON d.idDH = h.idDH";
                    }
                    
                    if (isset($_GET['trangthai']) && !empty($_GET['trangthai'])) {
                        $trangthai = mysqli_real_escape_string($conn, $_GET['trangthai']);
                        $str .= " WHERE d.TrangThai = '$trangthai'";
                    }
                    $str .= " ORDER BY idDH DESC"; // Sắp xếp theo thứ tự mới nhất trước

                    $result = $conn->query($str);
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $trangthai = $row['TrangThai'];
                            echo "<tr class='row-clickable' data-idDH='{$row["idDH"]}' data-trangthai='" . htmlspecialchars($row["TrangThai"], ENT_QUOTES) . "'>";
                            // Hiển thị MaDonHang nếu có, nếu không thì hiển thị idDH
                            if ($hasMaDonHang && !empty($row['MaDonHang'])) {
                                echo "<td>" . $row['MaDonHang'] . "</td>";
                            } else {
                                echo "<td>" . $row['idDH'] . "</td>";
                            }
                            echo "<td>" . $row['tenKH'] . "</td>";
                            echo "<td>" . $row['NgayDatHang'] . "</td>";
                            
                            // Hiển thị trạng thái và phương thức thanh toán nếu đã thanh toán
                            if ($row['TrangThai'] == 'Đã thanh toán') {
                                $phuongThuc = !empty($row['PhuongThucThanhToan']) ? $row['PhuongThucThanhToan'] : '';
                                
                                // Định dạng lại tên phương thức thanh toán
                                if (!empty($phuongThuc)) {
                                    $phuongThucLower = mb_strtolower($phuongThuc, 'UTF-8');
                                    
                                    if ($phuongThucLower == 'tiền mặt' || $phuongThucLower == 'tien mat' || $phuongThucLower == 'cash') {
                                        echo "<td><span class='badge bg-success'>Đã thanh toán (Tiền mặt)</span></td>";
                                    } else if ($phuongThucLower == 'chuyển khoản' || $phuongThucLower == 'chuyen khoan' || $phuongThucLower == 'vnpay' || $phuongThucLower == 'chuyên khoản') {
                                        echo "<td><span class='badge bg-primary'>Đã thanh toán (Chuyển khoản)</span></td>";
                                    } else {
                                        echo "<td><span class='badge bg-success'>Đã thanh toán (" . htmlspecialchars($phuongThuc) . ")</span></td>";
                                    }
                                } else {
                                    echo "<td><span class='badge bg-success'>Đã thanh toán</span></td>";
                                }
                            } else {
                                // Đơn hàng không phải trạng thái đã thanh toán
                                $badgeClass = '';
                                if ($row['TrangThai'] == 'Chờ xác nhận') {
                                    $badgeClass = 'bg-warning text-dark';
                                } else if ($row['TrangThai'] == 'Đang chuẩn bị') {
                                    $badgeClass = 'bg-info text-dark';
                                } else if ($row['TrangThai'] == 'Đã giao') {
                                    $badgeClass = 'bg-secondary';
                                } else {
                                    $badgeClass = 'bg-primary';
                                }
                                echo "<td><span class='badge {$badgeClass}'>" . $row['TrangThai'] . "</span></td>";
                            }
                            if (hasPermission('Sua don hang', $permissions) || hasPermission('Xoa don hang', $permissions)) {
                                echo "<td>";
                                if (hasPermission('Sua don hang', $permissions)) {
                                    // Chỉ hiển thị nút sửa nếu đơn hàng chưa thanh toán
                                    if ($row['TrangThai'] !== 'Đã thanh toán') {
                                        echo "<a href='index.php?page=suaDH&idDH={$row["idDH"]}' class='btn btn-warning btn-sm me-1'>
                                                <i class='fas fa-pencil-alt' style='color:white'></i>
                                              </a>";
                                    }
                                }
                                if ($canUpdateOrderStatus && hasPermission('Xoa don hang', $permissions) && $row['TrangThai'] != 'Đã thanh toán') {
                                    echo "<button type='button' class='btn btn-danger btn-sm btn-delete' 
                                                data-idDH='{$row["idDH"]}' 
                                                data-trangthai='" . htmlspecialchars($row["TrangThai"], ENT_QUOTES) . "'
                                                data-bs-toggle='modal' 
                                                data-bs-target='#deleteModal'>
                                                <i class='fas fa-trash-alt' style='color:white'></i>
                                          </button>";
                                }
                                echo "</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='" . (hasPermission('Sua don hang', $permissions) || hasPermission('Xoa don hang', $permissions) ? 5 : 4) . "'>Không có đơn hàng nào.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn xóa đơn hàng này không?</p>
                    <input type="hidden" name="delete_idDH" id="delete_idDH">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script xử lý xác nhận và nhấp vào dòng -->
<script>
    // Xử lý nút xóa và click dòng
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        const deleteInput = document.getElementById('delete_idDH');

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', (event) => {
                event.stopPropagation(); // Ngăn sự kiện click lan ra ngoài
                const idDH = btn.getAttribute('data-idDH');
                deleteInput.value = idDH;
            });
        });
        
        // Xử lý click vào dòng để xem chi tiết
        const rows = document.querySelectorAll('.row-clickable');
        rows.forEach(row => {
            row.addEventListener('click', function (event) {
                // Kiểm tra xem có phải click từ nút xóa hoặc sửa không
                if (!event.target.closest('.btn-delete') && !event.target.closest('.btn-warning')) {
                    const idDH = this.getAttribute('data-idDH');
                    window.location.href = `index.php?page=xemDH&idDH=${idDH}`;
                }
            });
        });
    });
</script>

<style>
    .btn.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
</style>