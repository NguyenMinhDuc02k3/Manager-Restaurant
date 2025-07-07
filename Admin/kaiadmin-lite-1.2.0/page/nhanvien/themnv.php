<?php

// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
// Đặt charset cho kết nối
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

// Debug
$isAdmin = ($vaitro_id == 4) ? 'Có' : 'Không';
error_log("Nhân viên ID: $idnv, Vai trò ID: $vaitro_id, Là admin: $isAdmin");

// Kiểm tra quyền thêm nhân viên
if (!hasPermission('Them nhan vien', $permissions) && $vaitro_id != 4) {
    echo "<script>alert('Bạn không có quyền thêm nhân viên!'); window.location.href='index.php';</script>";
    exit;
}

?>
<!-- Debug thông tin quyền - Chỉ hiển thị khi cần gỡ lỗi -->

<div class="container mb-5">
    <div class="text-center">
        <h1><b>Thêm nhân viên</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form class="" action="" method="POST" enctype="multipart/form-data">
                <div class="form-group mb-3">
                    <label for="hoten" class="form-label">Họ tên</label>
                    <input type="text" class="form-control" id="hoten" name="hoten"
                        placeholder="Nhập họ tên nhân viên" required />
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Giới tính</label><br />
                    <div class="d-flex">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNam" value="Nam" />
                            <label class="form-check-label" for="gioitinhNam">Nam</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNu" value="Nữ" checked />
                            <label class="form-check-label" for="gioitinhNu">Nữ</label>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="sdt" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="sdt" name="sdt" placeholder="Số điện thoại" required />
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" required />
                </div>
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required />
                </div>
                <div class="form-group mb-3">
                    <label for="diachi" class="form-label">Địa chỉ</label>
                    <input type="text" class="form-control" id="diachi" name="diachi"
                        placeholder="Nhập địa chỉ nhân viên" required />
                </div>
                <div class="form-group mb-3">
                    <label for="luong" class="form-label">Lương</label>
                    <input type="number" class="form-control" id="luong" name="luong"
                        placeholder="Nhập lương nhân viên" required />
                </div>
                <div class="form-group mb-3">
                    <label for="vaitro" class="form-label">Chức vụ</label>
                    <select class="form-select" id="vaitro" name="vaitro" required>
                        <option value="" disabled selected hidden>Chọn</option>
                        <?php
                        $sql = "SELECT idvaitro, tenvaitro FROM vaitro";
                        $result = mysqli_query($conn, $sql);
                        if ($result) {
                            $danhmucList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                            foreach ($danhmucList as $dm) {
                                echo '<option value="' . $dm['idvaitro'] . '">' . htmlspecialchars($dm['tenvaitro'], ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="hinhanh" class="form-label">Hình ảnh</label>
                    <input type="file" class="form-control" id="hinhanh" name="hinhanh" required />
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="themNV">Thêm</button>
                    <a href="index.php?page=dsnhanvien" class="btn btn-secondary" name="huy">Hủy</a>
                </div>
            </form>
            <?php
            if (isset($_POST['themNV'])) {
                $hoten = $_POST['hoten'];
                $gioitinh = $_POST['gioitinh'];
                $chucvu = $_POST['vaitro'];
                $sdt = $_POST['sdt'];
                $email = $_POST['email'];
                $diachi = $_POST['diachi'];
                $password = md5($_POST['password']); // Mã hóa mật khẩu bằng MD5
                $luong = $_POST['luong'];
                $hinhAnh = $_FILES['hinhanh'];
                $file_hinh = $hinhAnh['name'] ?? '';

                if ($conn) {
                    move_uploaded_file($hinhAnh['tmp_name'], '../assets/img/' . $file_hinh);
                    $stmt = $conn->prepare("INSERT INTO nhanvien (HoTen, GioiTinh, idvaitro, SoDienThoai, Email, DiaChi, Luong, HinhAnh, password)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssisssdss", $hoten, $gioitinh, $chucvu, $sdt, $email, $diachi, $luong, $file_hinh, $password);
                    if ($stmt->execute()) {
                        echo "<script>alert('Thêm thành công'); window.location.href='index.php?page=dsnhanvien'</script>";
                    } else {
                        echo "<script>alert('Thêm thất bại'); window.location.href='index.php?page=themnv'</script>";
                    }
                }
            }
            ?>
        </div>
    </div>
</div>