<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối DB
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Hàm kiểm tra quyền
function hasPermission($perm, $permissions) {
    return in_array(trim($perm), array_map('trim', $permissions));
}

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

// Kiểm tra quyền xem nhân viên
if (!hasPermission('Xem nhan vien', $permissions)) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}

// Kiểm tra quyền thêm, sửa, xóa nhân viên
$canAdd = hasPermission('Them nhan vien', $permissions);
$canEdit = hasPermission('Sua nhan vien', $permissions);
$canDelete = hasPermission('Xoa nhan vien', $permissions);

// Xử lý xóa nhân viên
if (isset($_POST['delete_idnv'])) {
    if (!hasPermission('Xoa nhan vien', $permissions)) {
        echo "<script>alert('Bạn không có quyền xóa nhân viên!'); window.location.href='index.php?page=dsnhanvien';</script>";
        exit;
    }
    $idnv_to_delete = $_POST['delete_idnv'];
    $stmt = $conn->prepare("DELETE FROM nhanvien WHERE idnv = ?");
    $stmt->bind_param("i", $idnv_to_delete);
    if ($stmt->execute()) {
        echo "<script>alert('Xóa nhân viên thành công!'); window.location.href='index.php?page=dsnhanvien';</script>";
    } else {
        echo "<script>alert('Lỗi xóa nhân viên: " . $conn->error . "'); window.location.href='index.php?page=dsnhanvien';</script>";
    }
    exit;
}

?>
<div class="container mb-3">
    <div class="mt-4">
        <div class="d-flex align-items-center justify-content-end mb-3 pe-5">
            <?php if (hasPermission('Them nhan vien', $permissions) && $_SESSION['vaitro_id'] == 4): ?>
                <a href="index.php?page=themnv" class="d-flex align-items-center text-decoration-none">
                    <p class="mb-0 me-2"><b>Thêm</b></p>
                    <i class="fas fa-plus fs-4"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div style="overflow-x: auto; max-height: 100%">
        <table class="table table-head-bg-primary ms-3 me-3">
            <thead>
                <tr>
                    <th scope="col">Mã nhân viên</th>
                    <th scope="col">Hình ảnh</th>
                    <th scope="col">Họ tên</th>
                    <th scope="col">Giới tính</th>
                    <th scope="col">Chức vụ</th>
                    <th scope="col">Số điện thoại</th>
                    <th scope="col">Email</th>
                    <th scope="col">Địa chỉ</th>
                    <th scope="col">Lương</th>
                    <th scope="col">Tùy chọn</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $str = "SELECT * FROM nhanvien JOIN vaitro ON nhanvien.idvaitro = vaitro.idvaitro";
                $result = $conn->query($str);
                if ($result->num_rows > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['idnv'] . "</td>";
                        echo "<td><img src='assets/img/{$row['HinhAnh']}' style='width:100px'></td>";
                        echo "<td>" . $row['HoTen'] . "</td>";
                        echo "<td>" . $row['GioiTinh'] . "</td>";
                        echo "<td>" . $row['tenvaitro'] . "</td>";
                        echo "<td>" . $row['SoDienThoai'] . "</td>";
                        echo "<td>" . $row['Email'] . "</td>";
                        echo "<td>" . $row['DiaChi'] . "</td>";
                        echo "<td>" . $row['Luong'] . "</td>";
                        echo "<td>";
                        if (hasPermission('Sua nhan vien', $permissions) ) {
                            echo "<a href='index.php?page=suanv&idnv={$row["idnv"]}' class='btn btn-warning btn-sm'>
                                    <i class='fas fa-pencil-alt' style='color:white; font-size:17px'></i>
                                </a>";
                        }
                        if (hasPermission('Xoa nhan vien', $permissions) && $_SESSION['vaitro_id'] == 4) {
                            echo "<button 
                                    type='button' 
                                    class='btn btn-danger btn-sm btn-delete' 
                                    data-idnv='{$row["idnv"]}' 
                                    data-hoten='" . htmlspecialchars($row["HoTen"]) . "'
                                    data-bs-toggle='modal' 
                                    data-bs-target='#deleteModal'>
                                    <i class='fas fa-trash-alt' style='color:white; font-size:17px'></i>
                                </button>";
                        }
                        echo "</td>";
                        echo "</tr>";
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
        <form method="POST" id="deleteForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmText">Bạn có chắc muốn xóa nhân viên này?</p>
                    <input type="hidden" name="delete_idnv" id="delete_idnv">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script xử lý xác nhận -->
<script>
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const confirmText = document.getElementById('confirmText');
    const deleteInput = document.getElementById('delete_idnv');
    const deleteForm = document.getElementById('deleteForm');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const idnv = btn.getAttribute('data-idnv');
            const hoten = btn.getAttribute('data-hoten');
            confirmText.textContent = `Bạn có chắc muốn xóa nhân viên "${hoten}" không?`;
            deleteInput.value = idnv;
        });
    });

    deleteForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(deleteForm);
        fetch('', {
            method: 'POST',
            body: formData
        }).then(response => {
            alert('Xóa nhân viên thành công!');
            window.location.reload();
        });
    });
</script>
<?php mysqli_close($conn); ?>