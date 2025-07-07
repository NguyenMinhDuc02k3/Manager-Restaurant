<?php
session_start();
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Hàm kiểm tra quyền
function hasPermission($perm, $permissions)
{
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

// Debug session và quyền
error_log("vaitro_id: $vaitro_id, Permissions: " . implode(', ', $permissions));

// Kiểm tra quyền xem tồn kho
if (!in_array($vaitro_id, [3, 4]) && !hasPermission('Xem ton kho', $permissions)) {
    echo "<script>alert('Chỉ Quản lý và Đầu bếp mới có quyền xem danh sách tồn kho !'); window.location.href='index.php';</script>";
    exit;
}

// Xử lý xóa
if (isset($_POST['delete_idtonkho'])) {
    $idtonkho = $_POST['delete_idtonkho'];
    $loaiTK = isset($_GET['loaiTK']) ? $_GET['loaiTK'] : '';
    // Debug POST data
    error_log("POST delete_idtonkho: $idtonkho, loaiTK: $loaiTK");

    // Kiểm tra quyền xóa (chỉ cho Đầu bếp)
    if ($vaitro_id == 3 && hasPermission('Xoa ton kho', $permissions)) {
        $stmt = $conn->prepare("DELETE FROM tonkho WHERE matonkho = ?");
        $stmt->bind_param("i", $idtonkho);
        if ($stmt->execute()) {
            echo "<script>
                    alert('Xóa tồn kho thành công!'); 
                    window.location.href = 'index.php?page=tonkhotheoloai&loaiTK=" . htmlspecialchars($loaiTK) . "';
                  </script>";
        } else {
            echo "<script>
                    alert('Lỗi xóa tồn kho: " . $conn->error . "');
                    window.location.href = 'index.php?page=tonkhotheoloai&loaiTK=" . htmlspecialchars($loaiTK) . "';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Bạn không có quyền xóa tồn kho!');
                window.location.href = 'index.php?page=tonkhotheoloai&loaiTK=" . htmlspecialchars($loaiTK) . "';
              </script>";
    }
    exit;
}

// Lọc theo danh mục
$loaiTK = isset($_GET['loaiTK']) ? $_GET['loaiTK'] : '';
$sql = "SELECT a.tenloaiTK, a.matonkho, a.tentonkho, a.soluong, a.DonViTinh, n.tennhacungcap 
        FROM (SELECT l.tenloaiTK, t.matonkho, t.tentonkho, t.soluong, t.DonViTinh, t.idncc, t.idloaiTK 
              FROM tonkho t JOIN loaitonkho l ON t.idloaiTK = l.idloaiTK) a 
        JOIN nhacungcap n ON a.idncc = n.idncc
        WHERE a.idloaiTK = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loaiTK);
$stmt->execute();
$result = $stmt->get_result();

// Lấy tên danh mục
$tenloaiTK = "";
if ($result->num_rows > 0) {
    $firstRow = $result->fetch_assoc();
    $tenloaiTK = $firstRow['tenloaiTK'];
    $result->data_seek(0);
}
?>

<div class="container mt-5 mb-3">
    <div class="mt-5 ms-3 me-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><?php echo htmlspecialchars($tenloaiTK); ?></h4>
            <?php if ($vaitro_id == 3 && hasPermission('Them ton kho', $permissions)): ?>
                <a href="index.php?page=themtonkho" class="d-flex align-items-center text-decoration-none">
                    <p class="mb-0 me-2"><b>Thêm</b></p>
                    <i class="fas fa-plus fs-4"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="table table-head-bg-primary table-bordered table-hover text-center ms-3 me-3">
            <thead class="table-primary">
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
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
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
                                echo "<a href='index.php?page=suatonkho&matonkho=" . htmlspecialchars($row['matonkho']) . "' class='btn btn-warning btn-sm me-1'>
                                        <i class='fas fa-pencil-alt' style='color:white; font-size:17px'></i>
                                      </a>";
                            }
                            if (hasPermission('Xoa ton kho', $permissions)) {
                                echo "<button type='button' class='btn btn-danger btn-sm btn-delete' 
                                            data-idtonkho='" . htmlspecialchars($row['matonkho']) . "' 
                                            data-tentonkho='" . htmlspecialchars($row['tentonkho']) . "'
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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