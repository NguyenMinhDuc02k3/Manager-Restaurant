<?php
// Thêm logic chặn URL
session_start();
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id']) || !isset($_SESSION['vaitro_id'])) {
    die("<script>alert('Vui lòng đăng nhập!'); window.location.href='../../page/dangnhap.php';</script>");
    exit;
}

// Kiểm tra vai trò Quản lý (idvaitro = 4)
if ($_SESSION['vaitro_id'] != 4) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}
// Kết thúc logic chặn URL

if (!isset($_GET['page'])) {
    $page = 'themmonan';
} else {
    $page = $_GET['page'];
}
?>
<div class="container mb-5">
    <div class="text-center">
        <h1><b>Thêm món ăn</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form class="" action="" method="POST" enctype="multipart/form-data">
                <div class="form-group mb-3">
                    <label for="hinhanh" class="form-label">Hình ảnh </label>
                    <input type="file" class="form-control" id="hinhanh" name="hinhanh" required />
                </div>
                <div class="form-group mb-3">
                    <label for="tenmon" class="form-label">Tên món </label>
                    <input type="text" class="form-control" id="tenmon" name="tenmon" placeholder="Nhập tên món ăn"
                        required />
                </div>

                <div class="form-group mb-3">
                    <label for="mota" class="form-label">Mô tả </label>
                    <textarea class="form-control" id="mota" name="mota" placeholder="Mô tả món ăn" rows="5"
                        required></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="DVT" class="form-label">Đơn vị tính </label>
                    <input type="text" class="form-control" id="DVT" name="DVT" placeholder="Đơn vị tính" required />
                </div>
                <div class="form-group mb-3">
                    <label for="gia" class="form-label">Giá </label>
                    <input type="number" class="form-control" id="gia" name="gia" placeholder="Giá" required />
                </div>
                <div class="form-group mb-3">
                    <label for="danhmuc" class="form-label">Danh mục </label>
                    <select class="form-select" id="danhmuc" name="danhmuc" required>
                        <option value="" disabled selected hidden>Chọn danh mục</option>
                        <?php
                        $sql = "SELECT iddm, tendanhmuc FROM danhmuc";
                        $result = mysqli_query($conn, $sql);
                        if ($result) {
                            $danhmucList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                            foreach ($danhmucList as $dm) {
                                echo '<option value="' . $dm['iddm'] . '">' . htmlspecialchars($dm['tendanhmuc'], ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="themmon">Thêm </button>
                    <a href="index.php?page=dsmonan" class="btn btn-secondary" name="huy">Hủy</a>

                </div>
            </form>
            <?php

            if (isset($_POST['themmon'])) {
                $tenmon = $_POST['tenmon'];
                $mota = $_POST['mota'];
                $gia = $_POST['gia'];
                $danhmuc = $_POST['danhmuc'];
                $donvitinh = $_POST['DVT'];
                $hinhAnh = $_FILES['hinhanh'];
                $file_hinh = $hinhAnh['name'] ?? '';
                if ($conn) {
                    move_uploaded_file($hinhAnh['tmp_name'], '../assets/img/' . $file_hinh);
                    $str = "insert into monan (tenmonan, mota, DonGia, iddm, hinhanh, DonViTinh)
                                    values ('$tenmon','$mota', '$gia','$danhmuc', '$file_hinh','$donvitinh')";
                    if ($conn->query($str)) {
                        echo "<script>alert('Thêm thành công'); window.location.href='index.php?page=dsmonan'</script>";
                    } else {
                        echo "<script>alert('Thêm thất bại'); window.location.href='index.php?page=themmonan'</script>";
                    }
                }
            }
            ?>
        </div>
    </div>
</div>