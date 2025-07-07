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

// Kiểm tra quyền thêm tồn kho - Đầu bếp (vaitro_id = 3) HOẶC có quyền "Thêm tồn kho"
if (!hasPermission('Them ton kho', $permissions)) {
    echo "<script>alert('Bạn không có quyền thêm tồn kho!'); window.location.href='index.php';</script>";
    exit;
}
?>

<div class="container mb-5">
    <div class="text-center">
        <h1><b>Thêm tồn kho</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tentonkho">Tên tồn kho</label>
                    <input type="text" class="form-control" id="tentonkho" name="tentonkho" placeholder="Nhập tên tồn kho" required />
                </div>
                <div class="form-group">
                    <label for="soluong">Số lượng</label>
                    <input type="number" class="form-control" id="soluong" name="soluong" required />
                </div>
                <div class="form-group">
                    <label for="DonViTinh">Đơn vị</label>
                    <input type="text" class="form-control" id="DonViTinh" name="DonViTinh" placeholder="cái/lon/kg..." required />
                </div>
                <div class="form-group">
                    <label for="loaiTK">Loại tồn kho</label>
                    <select class="form-select" id="loaiTK" name="loaiTK" required>
                        <option value="" disabled selected>Chọn loại</option>
                        <?php
                        $sql = "SELECT idloaiTK, tenloaiTK FROM loaitonkho";
                        $result = $conn->query($sql);
                        while ($dm = $result->fetch_assoc()) {
                            echo '<option value="' . $dm['idloaiTK'] . '">' . htmlspecialchars($dm['tenloaiTK']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ncc">Nhà cung cấp</label>
                    <select class="form-select" id="ncc" name="ncc" required>
                        <option value="" disabled selected>Chọn nhà cung cấp</option>
                        <?php
                        $sql = "SELECT idncc, tennhacungcap FROM nhacungcap";
                        $result = $conn->query($sql);
                        while ($dm = $result->fetch_assoc()) {
                            echo '<option value="' . $dm['idncc'] . '">' . htmlspecialchars($dm['tennhacungcap']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="themtonkho">Thêm</button>
                    <a href="index.php?page=dstonkho" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
            <?php
            if (isset($_POST['themtonkho'])) {
                $tentonkho = $_POST['tentonkho'];
                $soluong = $_POST['soluong'];
                $DonViTinh = $_POST['DonViTinh'];
                $loaiTK = $_POST['loaiTK'];
                $ncc = $_POST['ncc'];
                $stmt = $conn->prepare("INSERT INTO tonkho (tentonkho, soluong, DonViTinh, idloaiTK, idncc) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sisii", $tentonkho, $soluong, $DonViTinh, $loaiTK, $ncc);
                if ($stmt->execute()) {
                    echo "<script>alert('Thêm thành công'); window.location.href='index.php?page=dstonkho';</script>";
                } else {
                    echo "<script>alert('Thêm thất bại: " . addslashes($conn->error) . "'); window.location.href='index.php?page=themtonkho';</script>";
                }
            }
            ?>
        </div>
    </div>
</div>
<?php mysqli_close($conn); ?>