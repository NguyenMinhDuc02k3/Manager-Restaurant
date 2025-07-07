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

// Kiểm tra quyền sửa nhân viên
if (!hasPermission('Sua nhan vien', $permissions)) {
    echo "<script>alert('Bạn không có quyền sửa nhân viên!'); window.location.href='index.php';</script>";
    exit;
}

// Lấy thông tin nhân viên
if (!isset($_GET['idnv'])) {
    echo "<script>alert('Thiếu ID nhân viên!'); window.location.href='index.php?page=dsnhanvien';</script>";
    exit();
}
$edit_idnv = intval($_GET['idnv']);
$sql = "SELECT * FROM nhanvien join vaitro on nhanvien.idvaitro = vaitro.idvaitro WHERE idnv = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $edit_idnv);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result->num_rows > 0) {
    $row = mysqli_fetch_assoc($result);
    $hoten = $row['HoTen'];
    $gioitinh = $row['GioiTinh'];
    $chucvu = $row['tenvaitro'];
    $sdt = $row['SoDienThoai'];
    $email = $row['Email'];
    $diachi = $row['DiaChi'];
    $luong = $row['Luong'];
    $hinhAnh = $row['HinhAnh'];
} else {
    echo "<script>alert('Không tìm thấy nhân viên!'); window.location.href='index.php?page=dsnhanvien';</script>";
    exit();
}
?>
<div class="container mb-5">
    <div class="text-center">
        <h1><b>Sửa thông tin nhân viên</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form class="" action="" method="POST" enctype="multipart/form-data">
                <div class="text-center">
                    <img src='assets/img/<?php echo htmlspecialchars($hinhAnh); ?>' style='width:100px'>
                </div>

                <div class="form-group">
                    <label for="hinhanh">Hình ảnh</label>
                    <input type="file" class="form-control-file" id="hinhanh" name="hinhanh" accept="image/*" />
                </div>
                <div class="form-group">
                    <label for="hoten">Họ tên</label>
                    <input type="text" class="form-control" id="hoten" name="hoten" value="<?php echo htmlspecialchars($hoten); ?>" required />
                </div>

                <div class="form-group">
                    <label>Giới tính</label><br />
                    <div class="d-flex">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNam" value="Nam" <?php if ($gioitinh == "Nam") echo "checked"; ?> required />
                            <label class="form-check-label" for="gioitinhNam">Nam</label>
                        </div>
                        <div class="form-check ms-3">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNu" value="Nữ" <?php if ($gioitinh == "Nữ") echo "checked"; ?> required />
                            <label class="form-check-label" for="gioitinhNu">Nữ</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="chucvu">Chức vụ</label>
                    <select class="form-select" id="chucvu" name="chucvu" required>
                        <?php
                        $sql = "SELECT idvaitro, tenvaitro FROM vaitro";
                        $result = mysqli_query($conn, $sql);
                        $danhmucList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        foreach ($danhmucList as $dm) {
                            $selected = ($row['idvaitro'] == $dm['idvaitro']) ? 'selected' : '';
                            echo '<option value="' . $dm['idvaitro'] . '" ' . $selected . '>' . htmlspecialchars($dm['tenvaitro']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sdt">Số điện thoại</label>
                    <input type="tel" class="form-control" id="sdt" name="sdt" placeholder="Số điện thoại" value="<?php echo htmlspecialchars($sdt); ?>" required />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required />
                </div>
                <div class="form-group">
                    <label for="diachi">Địa chỉ</label>
                    <input type="text" class="form-control" id="diachi" name="diachi" placeholder="Nhập địa chỉ nhân viên" value="<?php echo htmlspecialchars($diachi); ?>" required />
                </div>
                <div class="form-group">
                    <label for="luong">Lương</label>
                    <input type="number" class="form-control" id="luong" name="luong" placeholder="Nhập lương nhân viên" value="<?php echo htmlspecialchars($luong); ?>" required />
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="suaNV">Sửa</button>
                    <a href="index.php?page=dsnhanvien" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
            <?php
            if (isset($_POST['suaNV'])) {
                $hoten = $_POST['hoten'];
                $gioitinh = $_POST['gioitinh'];
                $chucvu = $_POST['chucvu'];
                $sdt = $_POST['sdt'];
                $email = $_POST['email'];
                $diachi = $_POST['diachi'];
                $luong = intval($_POST['luong']);
                $hinhAnh = $_FILES['hinhanh']['name'] ?? '';

                if ($hinhAnh !== '') {
                    // Kiểm tra file upload
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    if (!in_array($_FILES['hinhanh']['type'], $allowed_types) || $_FILES['hinhanh']['size'] > $max_size) {
                        echo "<script>alert('File ảnh không hợp lệ! Chỉ chấp nhận JPEG, PNG, GIF, tối đa 5MB.'); window.location.href='index.php?page=suanv&idnv=$edit_idnv';</script>";
                        exit();
                    }
                    $upload_dir = 'assets/img/';
                    $upload_path = $upload_dir . basename($hinhAnh);
                    if (!move_uploaded_file($_FILES['hinhanh']['tmp_name'], $upload_path)) {
                        echo "<script>alert('Lỗi khi upload ảnh!'); window.location.href='index.php?page=suanv&idnv=$edit_idnv';</script>";
                        exit();
                    }
                    $sql = "UPDATE nhanvien SET HoTen = ?, GioiTinh = ?, idvaitro = ?, SoDienThoai = ?, Email = ?, DiaChi = ?, Luong = ?, HinhAnh = ? WHERE idnv = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ssssssisi", $hoten, $gioitinh, $chucvu, $sdt, $email, $diachi, $luong, $hinhAnh, $edit_idnv);
                } else {
                    $sql = "UPDATE nhanvien SET HoTen = ?, GioiTinh = ?, idvaitro = ?, SoDienThoai = ?, Email = ?, DiaChi = ?, Luong = ? WHERE idnv = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ssssssii", $hoten, $gioitinh, $chucvu, $sdt, $email, $diachi, $luong, $edit_idnv);
                }

                if (mysqli_stmt_execute($stmt)) {
                    echo "<script>alert('Sửa thành công'); window.location.href='index.php?page=dsnhanvien';</script>";
                } else {
                    echo "<script>alert('Sửa thất bại'); window.location.href='index.php?page=suanv&idnv=$edit_idnv';</script>";
                }
                mysqli_stmt_close($stmt);
            }
            ?>
        </div>
    </div>
</div>
<?php mysqli_close($conn); ?>