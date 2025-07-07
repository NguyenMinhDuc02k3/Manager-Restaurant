<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['page'])) {
    $page = 'suatonkho';
} else {
    $page = $_GET['page'];
}
if (isset($_GET['matonkho'])) {
    $matonkho = $_GET['matonkho'];
}
$conn = mysqli_connect(hostname: 'localhost', username: 'hceeab2b55_chung9atm', password: 'Chung2002!', database: 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
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

// Kiểm tra quyền sửa tồn kho - Đầu bếp (vaitro_id = 3) HOẶC có quyền "Sửa tồn kho"
if ( !hasPermission('Sua ton kho', $permissions)) {
    echo "<script>alert('Bạn không có quyền sửa tồn kho!'); window.location.href='index.php';</script>";
    exit;
}

// Sử dụng prepared statement để lấy thông tin tồn kho
$stmt = $conn->prepare("SELECT a.tenloaiTK, a.matonkho, a.tentonkho, a.soluong, a.DonViTinh, n.tennhacungcap, a.idncc, a.idloaiTK
        FROM (SELECT l.tenloaiTK, t.matonkho, t.tentonkho, t.soluong, t.DonViTinh, t.idncc, t.idloaiTK 
              FROM tonkho t JOIN loaitonkho l ON t.idloaiTK = l.idloaiTK) a 
        JOIN nhacungcap n ON a.idncc = n.idncc
        WHERE matonkho = ?");
$stmt->bind_param("i", $matonkho);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tentonkho = $row['tentonkho'];
        $soluong = $row['soluong'];
        $donvi = $row['DonViTinh'];
        $idloaiTK_selected = $row['idloaiTK'];
        $idncc_selected = $row['idncc'];
    }
}
?>
<div class="container mb-5">
    <div class="text-center">
        <h1><b>Sửa thông tin tồn kho</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form class="" action="" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="tentonkho">Tên tồn kho</label>
                    <input type="tentonkho" class="form-control" id="tentonkho" name="tentonkho"
                        value="<?php echo $tentonkho ?>" required />
                </div>

                <div class="form-group">
                    <label for="soluong">Số lượng</label>
                    <input type="number" class="form-control" id="soluong" name="soluong" value="<?php echo $soluong ?>"
                        required />
                </div>
                <div class="form-group">
                    <label for="donvi">Đơn vị</label>
                    <input type="text" class="form-control" id="donvi" name="donvi" value="<?php echo $donvi ?>"
                        required />
                </div>
                <div class="form-group">
                    <label for="loaiTK">Loại tồn kho</label>
                    <select class="form-select" id="loaiTK" name="loaiTK" required>
                        <option value="" disabled selected hidden>Chọn loại</option>
                        <?php
                        $sql = "SELECT idloaiTK, tenloaiTK FROM loaitonkho";
                        $result = mysqli_query($conn, $sql);
                        $loaiTKList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        foreach ($loaiTKList as $loaiTK) {
                            $selected = ($loaiTK['idloaiTK'] == $idloaiTK_selected) ? 'selected' : '';
                            echo '<option value="' . $loaiTK['idloaiTK'] . '" ' . $selected . '>' . htmlspecialchars($loaiTK['tenloaiTK']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ncc">Nhà cung cấp</label>
                    <select class="form-select" id="ncc" name="ncc" required>
                        <option value="" disabled selected hidden>Chọn nhà cung cấp</option>
                        <?php
                        $sql = "SELECT idncc, tennhacungcap FROM nhacungcap";
                        $result = mysqli_query($conn, $sql);
                        $nccList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        foreach ($nccList as $loaincc) {
                            $selected = ($loaincc['idncc'] == $idncc_selected) ? 'selected' : '';
                            echo '<option value="' . $loaincc['idncc'] . '" ' . $selected . '>' . htmlspecialchars($loaincc['tennhacungcap']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="suatonkho">Sửa </button>
                    <a href="index.php?page=dstonkho" class="btn btn-secondary" name="huy">Hủy</a>

                </div>
            </form>
            <?php

            if (isset($_POST['suatonkho'])) {
                $tentonkho = $_POST['tentonkho'];
                $soluong = $_POST['soluong'];
                $donvi = $_POST['donvi'];
                $loaiTK = $_POST['loaiTK'];
                $ncc = $_POST['ncc'];
                
                if ($conn) {
                    $stmt = $conn->prepare("UPDATE tonkho SET tentonkho = ?, soluong = ?, DonViTinh = ?, idloaiTK = ?, idncc = ? WHERE matonkho = ?");
                    $stmt->bind_param("sissii", $tentonkho, $soluong, $donvi, $loaiTK, $ncc, $matonkho);
                    
                    if ($stmt->execute()) {
                        echo "<script>alert('Sửa thành công'); window.location.href='index.php?page=dstonkho'</script>";
                    } else {
                        echo "<script>alert('Sửa thất bại: " . $conn->error . "'); window.location.href='index.php?page=suatonkho'</script>";
                    }
                }
            }
            ?>
        </div>
    </div>
</div>
<?php mysqli_close($conn); ?>