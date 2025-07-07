<?php
// Thiết lập mã hóa UTF-8 (Bỏ header vì gây lỗi)
// Sử dụng meta tag trong HTML để thiết lập charset

// Thêm logic chặn URL
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

function hasPermission($perm, $permissions) {
    return in_array(trim($perm), array_map('trim', $permissions));
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id']) || !isset($_SESSION['vaitro_id'])) {
    echo "<script>window.location.href='index.php?page=dangnhap';</script>";
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

// Kiểm tra quyền xem món ăn
if (!hasPermission('Xem mon an', $permissions)) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}

// Kiểm tra quyền thêm, sửa, xóa món ăn
$canAdd = hasPermission('Them mon an', $permissions);
$canEdit = hasPermission('Sua mon an', $permissions);
$canDelete = hasPermission('Xoa mon an', $permissions);
// Kết thúc logic chặn URL

// Xử lý xoá nếu có yêu cầu
if (isset($_POST['delete_idmonan'])) {
    if (!$canDelete) {
        echo "<script>alert('Bạn không có quyền xóa món ăn!'); window.location.href='index.php?page=dsmonan';</script>";
        exit;
    }
    $idmonan = $_POST['delete_idmonan'];
    $stmt = $conn->prepare("DELETE FROM monan WHERE idmonan = ?");
    $stmt->bind_param("i", $idmonan);
    $stmt->execute();
    echo "<script>
            window.location.reload(); // Tự động tải lại trang
            alert('Xóa món ăn thành công!'); // Thông báo xóa thành công
          </script>";
    exit;
}
?>

<div class="container mb-3">
    <div class="mt-4">
        <div class="d-flex align-items-center justify-content-end mb-3 pe-5">
            <?php if ($canAdd): ?>
                <a href="index.php?page=themmonan" class="d-flex align-items-center text-decoration-none">
                    <p class="mb-0 me-2"><b>Thêm</b></p>
                    <i class="icon-user-follow fs-4"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div style="overflow-x: auto; max-height: 100%">
        <table class="table table-head-bg-primary  table-hover ms-3 me-3 ">
            <thead>
                <tr>
                    <th scope="col">Mã món ăn </th>
                    <th scope="col">Hình ảnh</th>
                    <th scope="col">Tên món ăn </th>
                    <th scope="col">Mô tả </th>
                    <th scope="col">Giá</th>
                    <th scope="col">Danh mục</th>
                    <th scope="col">Tùy chọn</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($conn) {
                    $str = "SELECT * FROM monan m join danhmuc d on m.iddm=d.iddm order BY m.idmonan ASC";
                    $result = $conn->query($str);
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['idmonan'] . "</td>";
                            echo "<td><img src='assets/img/{$row['hinhanh']}' style='width:100px'></td>";
                            echo "<td>" . $row['tenmonan'] . "</td>";
                            echo "<td>" . $row['mota'] . "</td>";
                            echo "<td>" . number_format($row['DonGia']) . "đ</td>";
                            echo "<td>" . $row['tendanhmuc'] . "</td>";
                            echo "<td>";
                            if ($canEdit) {
                                echo "<a href='index.php?page=suamonan&idmonan={$row["idmonan"]}' class='btn btn-warning btn-sm me-1'>
                                    <i class='fas fa-pencil-alt' style='color:white; font-size:17px'></i>
                                </a>";
                            }
                            if ($canDelete) {
                                echo "<button 
                                    type='button' 
                                    class='btn btn-danger btn-sm btn-delete' 
                                    data-idmonan='{$row["idmonan"]}' 
                                    data-tenmonan='" . htmlspecialchars($row["tenmonan"]) . "'
                                    data-bs-toggle='modal' 
                                    data-bs-target='#deleteModal'>
                                    <i class='fas fa-trash-alt' style='color:white; font-size:17px'></i>
                                </button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
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
                    <h5 class="modal-title">Xác nhận xoá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmText">Bạn có chắc muốn xoá món ăn này?</p>
                    <input type="hidden" name="delete_idmonan" id="delete_idmonan">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-danger">Xoá</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script xử lý xác nhận -->
<script>
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const confirmText = document.getElementById('confirmText');
    const deleteInput = document.getElementById('delete_idmonan');
    const deleteForm = document.getElementById('deleteForm'); // Lấy form xóa

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const idmonan = btn.getAttribute('data-idmonan');
            const tenmon = btn.getAttribute('data-tenmonan');
            confirmText.textContent = `Bạn có chắc muốn xoá món ăn "${tenmon}" không?`;
            deleteInput.value = idmonan;
        });
    });

    // Thêm sự kiện submit form khi người dùng nhấn "Xoá" trong modal
    deleteForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Ngăn chặn việc gửi form mặc định
        const formData = new FormData(deleteForm);
        fetch('', {
            method: 'POST',
            body: formData
        }).then(response => {
            // Thông báo xóa thành công và tải lại trang
            alert('Xóa món ăn thành công!');
            window.location.reload(); // Sau khi xóa thành công, reload trang
        });
    });
</script>