<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

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

// Kiểm tra quyền xem tồn kho
if ( !hasPermission('Xem ton kho', $permissions)) {
    echo "<script>alert('Chỉ Quản lý và Đầu bếp mới có quyền xem danh sách tồn kho !'); window.location.href='index.php';</script>";
    exit;
}

// Xử lý xóa
if (isset($_POST['delete_idtonkho'])) {
    $idtonkho = $_POST['delete_idtonkho'];
    
    // Kiểm tra quyền xóa (chỉ cho Đầu bếp)
    if ( hasPermission('Xoa ton kho', $permissions)) {
        $stmt = $conn->prepare("DELETE FROM tonkho WHERE matonkho = ?");
        $stmt->bind_param("i", $idtonkho);
        if ($stmt->execute()) {
            echo "<script>alert('Xóa tồn kho thành công!'); window.location.href='index.php?page=dstonkho';</script>";
        } else {
            echo "<script>alert('Lỗi xóa tồn kho: " . $conn->error . "'); window.location.href='index.php?page=dstonkho';</script>";
        }
    } else {
        echo "<script>alert('Bạn không có quyền xóa tồn kho!'); window.location.href='index.php?page=dstonkho';</script>";
    }
    exit;
}
?>

<div class="container mb-3">
    <div class="mt-4">
        <div class="d-flex align-items-center justify-content-end mb-3 pe-5">
            <?php if ( hasPermission('Them ton kho', $permissions)): ?>
                <a href="index.php?page=themtonkho" class="d-flex align-items-center text-decoration-none">
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
                    <th scope="col">Mã tồn kho</th>
                    <th scope="col">Tên tồn kho</th>
                    <th scope="col">Số lượng</th>
                    <th scope="col">Đơn vị</th>
                    <th scope="col">Loại tồn kho</th>
                    <th scope="col">Nhà cung cấp</th>
                    <?php if ($vaitro_id == 3): ?>
                        <th scope="col">Tùy chọn</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $str = "SELECT a.tenloaiTK, a.matonkho, a.tentonkho, a.soluong, a.DonViTinh, n.tennhacungcap 
                        FROM (SELECT l.tenloaiTK, t.matonkho, t.tentonkho, t.soluong, t.DonViTinh, t.idncc, t.idloaiTK 
                              FROM tonkho t JOIN loaitonkho l ON t.idloaiTK = l.idloaiTK) a 
                        JOIN nhacungcap n ON a.idncc = n.idncc";
                $result = $conn->query($str);
                if ($result->num_rows > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['matonkho']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tentonkho']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['soluong']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['DonViTinh']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tenloaiTK']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tennhacungcap']) . "</td>";
                        if ($vaitro_id == 3) {
                            echo "<td>";
                            if (hasPermission('Sua ton kho', $permissions)) {
                                echo "<a href='index.php?page=suatonkho&matonkho={$row["matonkho"]}' class='btn btn-warning btn-sm me-1'>
                                        <i class='fas fa-pencil-alt' style='color:white; font-size:17px'></i>
                                      </a>";
                            }
                            if (hasPermission('Xoa ton kho', $permissions)) {
                                echo "<button type='button' class='btn btn-danger btn-sm btn-delete' 
                                            data-idtonkho='{$row["matonkho"]}' 
                                            data-tentonkho='" . htmlspecialchars($row["tentonkho"]) . "'
                                            data-bs-toggle='modal' 
                                            data-bs-target='#deleteModal'>
                                        <i class='fas fa-trash-alt' style='color:white; font-size:17px'></i>
                                      </button>";
                            }
                            echo "</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='" . ($vaitro_id == 3 ? 7 : 6) . "' class='text-muted'>Không có dữ liệu tồn kho.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal xác nhận xóa (chỉ hiển thị cho Đầu bếp) -->
<?php if ($vaitro_id == 3): ?>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="deleteForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmText">Bạn có chắc muốn xóa tồn kho này?</p>
                    <input type="hidden" name="delete_idtonkho" id="delete_idtonkho">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script xử lý xác nhận (chỉ cho Đầu bếp) -->
<script>
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const confirmText = document.getElementById('confirmText');
    const deleteInput = document.getElementById('delete_idtonkho');
    const deleteForm = document.getElementById('deleteForm'); // Lấy form xóa

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const idtonkho = btn.getAttribute('data-idtonkho');
            const tentonkho = btn.getAttribute('data-tentonkho');
            confirmText.textContent = `Bạn có chắc muốn xoá tồn kho"${tentonkho}" không?`;
            deleteInput.value = idtonkho;
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
            alert('Xóa tồn kho thành công!');
            window.location.reload(); // Sau khi xóa thành công, reload trang
        });
    });
</script>
<?php endif; ?>

<?php mysqli_close($conn); ?>