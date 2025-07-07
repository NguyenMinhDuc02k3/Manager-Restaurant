<?php
// Thêm error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Thêm logic chặn URL
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Kiểm tra đăng nhập - sử dụng JavaScript redirect thay vì header()
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

// Hàm kiểm tra quyền
function hasPermission($perm, $permissions) {
    return in_array(trim($perm), array_map('trim', $permissions));
}

// Kiểm tra quyền xem món ăn
if (!hasPermission('Xem mon an', $permissions)) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}

// Xử lý xoá
if (isset($_POST['delete_idmonan'])) {
    if (!hasPermission('Xoa mon an', $permissions)) {
        echo "<script>alert('Bạn không có quyền xóa món ăn!'); window.location.href='index.php?page=monantheodm&danhmuc={$_GET['danhmuc']}';</script>";
        exit;
    }
    $idmonan = $_POST['delete_idmonan'];
    $stmt = $conn->prepare("DELETE FROM monan WHERE idmonan = ?");
    $stmt->bind_param("i", $idmonan);
    $stmt->execute();
    echo "<script>
            alert('Xoá món ăn thành công!');
            window.location.href = window.location.href;
          </script>";
    exit;
}

// Lọc theo danh mục
$danhmucID = isset($_GET['danhmuc']) ? $_GET['danhmuc'] : '';
$tendanhmuc = "Tất cả món ăn";

try {
    // Kiểm tra nếu có tham số danh mục
    if (!empty($danhmucID)) {
        // Lấy tên danh mục
        $sqlDanhMuc = "SELECT tendanhmuc FROM danhmuc WHERE iddm = ?";
        $stmtDanhMuc = $conn->prepare($sqlDanhMuc);
        $stmtDanhMuc->bind_param("i", $danhmucID);
        $stmtDanhMuc->execute();
        $resultDanhMuc = $stmtDanhMuc->get_result();
        
        if ($resultDanhMuc->num_rows > 0) {
            $rowDanhMuc = $resultDanhMuc->fetch_assoc();
            $tendanhmuc = $rowDanhMuc['tendanhmuc'];
        }
        
        // Lấy món ăn theo danh mục
        $sql = "SELECT ma.*, dm.tendanhmuc FROM monan ma 
                JOIN danhmuc dm ON ma.iddm = dm.iddm
                WHERE ma.iddm = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $danhmucID);
    } else {
        // Lấy tất cả món ăn
        $sql = "SELECT ma.*, dm.tendanhmuc FROM monan ma 
                JOIN danhmuc dm ON ma.iddm = dm.iddm";
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Lỗi truy vấn: " . $e->getMessage() . "</div>";
}
?>

<div class="container mt-5 mb-3">
    <div class="mt-5 ms-3 me-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><?php echo $tendanhmuc ?></h4>
            <?php if (hasPermission('Them mon an', $permissions)): ?>
                <a href="index.php?page=themmonan" class="d-flex align-items-center text-decoration-none">
                    <p class="mb-0 me-2"><b>Thêm</b></p>
                    <i class="icon-user-follow fs-4"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div style="overflow-x: auto;">
        <table class="table table-head-bg-primary table-bordered table-hover text-center ms-3 me-3">
            <thead class="table-primary">
                <tr>
                    <th scope="col">Mã món ăn </th>
                    <th scope="col">Hình ảnh</th>
                    <th scope="col">Tên món ăn </th>
                    <th scope="col">Mô tả </th>
                    <th scope="col">Giá</th>
                    <th scope="col">Tùy chọn</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($result) && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['idmonan']}</td>";
                        echo "<td><img src='assets/img/{$row['hinhanh']}' width='100'></td>";
                        echo "<td>{$row['tenmonan']}</td>";
                        echo "<td>{$row['mota']}</td>";
                        echo "<td>" . number_format($row['DonGia']) . "đ</td>";
                        echo "<td>";
                        if (hasPermission('Sua mon an', $permissions)) {
                            echo "<a href='index.php?page=suamonan&idmonan={$row['idmonan']}' class='btn btn-warning btn-sm me-1'>
                                <i class='fas fa-pencil-alt' style='color:white; font-size:17px'></i>
                            </a>";
                        }
                        if (hasPermission('Xoa mon an', $permissions)) {
                            echo "<button 
                                class='btn btn-danger btn-sm btn-delete' 
                                data-idmonan='{$row['idmonan']}' 
                                data-tenmonan='{$row['tenmonan']}'
                                data-bs-toggle='modal' 
                                data-bs-target='#deleteModal'>
                                <i class='fas fa-trash-alt' style='color:white; font-size:17px'></i>
                            </button>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Không có món ăn nào trong danh mục này.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal xác nhận xoá -->
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

<!-- Script xử lý xoá -->
<script>
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const confirmText = document.getElementById('confirmText');
    const deleteInput = document.getElementById('delete_idmonan');
    const deleteForm = document.getElementById('deleteForm');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const idmonan = btn.getAttribute('data-idmonan');
            const tenmon = btn.getAttribute('data-tenmonan');
            confirmText.textContent = `Bạn có chắc muốn xoá món ăn "${tenmon}" không?`;
            deleteInput.value = idmonan;
        });
    });

    deleteForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(deleteForm);
        fetch('', {
            method: 'POST',
            body: formData
        }).then(() => {
            alert('Xoá món ăn thành công!');
            window.location.reload();
        });
    });
</script>