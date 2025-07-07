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

// Lấy quyền của nhân viên hiện tại
$idnv = $_SESSION['nhanvien_id'];
$queryRole = "
    SELECT v.quyen
    FROM nhanvien n
    JOIN vaitro v ON n.idvaitro = v.idvaitro
    WHERE n.idnv = ?";
$stmt = mysqli_prepare($conn, $queryRole);
mysqli_stmt_bind_param($stmt, "i", $idnv);
mysqli_stmt_execute($stmt);
$resultRole = mysqli_stmt_get_result($stmt);
$roleData = mysqli_fetch_assoc($resultRole);
$permissions = $roleData && !empty($roleData['quyen']) ? explode(",", $roleData['quyen']) : [];

// Kiểm tra quyền "Sửa món ăn"
if (!in_array('Sua mon an', array_map('trim', $permissions))) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}
// Kết thúc logic chặn URL

if (!isset($_GET['page'])) {
    $page = 'suamonan';
} else {
    $page = $_GET['page'];
}
if (isset($_GET['idmonan'])) {
    $idmonan = $_GET['idmonan'];
}

$sql = "SELECT m.*, d.tendanhmuc FROM monan m JOIN danhmuc d ON m.iddm = d.iddm WHERE idmonan = $idmonan";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $hinhanh = $row['hinhanh'];
        $tenmonan = $row['tenmonan'];
        $mota = $row['mota'];
        $gia = $row['DonGia'];
        $iddm_selected = $row['iddm'];
        $donvitinh = $row['DonViTinh'] ?? '';
    }
}
?>
<div class="container mb-5">
    <div class="text-center">
        <h1><b>Sửa thông tin món ăn</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form class="" action="" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-3">
                    <img src='assets/img/<?php echo $hinhanh ?>' style='width:300px'>
                </div>
                <div class="form-group mb-3">
                    <label for="hinhanh" class="form-label">Hình ảnh </label>
                    <input type="file" class="form-control" id="hinhanh" name="hinhanh" />
                </div>
                <div class="form-group mb-3">
                    <label for="tenmon" class="form-label">Tên món </label>
                    <input type="text" class="form-control" id="tenmon" name="tenmon"
                        value="<?php echo htmlspecialchars($tenmonan, ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>

                <div class="form-group mb-3">
                    <label for="mota" class="form-label">Mô tả </label>
                    <textarea class="form-control" id="mota" name="mota" rows="5" required><?php echo htmlspecialchars($mota, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                
                <div class="form-group mb-3">
                    <label for="DVT" class="form-label">Đơn vị tính </label>
                    <input type="text" class="form-control" id="DVT" name="DVT" 
                        value="<?php echo htmlspecialchars($donvitinh, ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>
                
                <div class="form-group mb-3">
                    <label for="gia" class="form-label">Giá </label>
                    <input type="number" class="form-control" id="gia" name="gia" 
                        value="<?php echo $gia ?>" required />
                </div>
                <div class="form-group mb-3">
                    <label for="danhmuc" class="form-label">Danh mục </label>
                    <select class="form-select" id="danhmuc" name="danhmuc" required>
                        <option value="" disabled>Chọn danh mục</option>
                        <?php
                        $sql = "SELECT iddm, tendanhmuc FROM danhmuc";
                        $result = mysqli_query($conn, $sql);
                        if ($result) {
                            $danhmucList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                            foreach ($danhmucList as $dm) {
                                $selected = ($dm['iddm'] == $iddm_selected) ? 'selected' : '';
                                echo '<option value="' . $dm['iddm'] . '" ' . $selected . '>' . htmlspecialchars($dm['tendanhmuc'], ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="suamon">Sửa </button>
                    <a href="index.php?page=dsmonan" class="btn btn-secondary" name="huy">Hủy</a>

                </div>
            </form>
            <?php

            if (isset($_POST['suamon'])) {
                $tenmon = $_POST['tenmon'];
                $mota = $_POST['mota'];
                $gia = $_POST['gia'];
                $danhmuc = $_POST['danhmuc'];
                $donvitinh = $_POST['DVT'] ?? '';
                $hinhanh = $_FILES['hinhanh'];
                $file_hinh = $hinhanh['name'] ?? '';
                if ($conn) {
                    if ($file_hinh !== '') {
                        move_uploaded_file($hinhanh['tmp_name'], '../assets/img/' . $file_hinh);
                        $str = "UPDATE monan SET hinhanh = '$file_hinh', tenmonan = '$tenmon', mota = '$mota', 
                                DonGia = '$gia', iddm='$danhmuc', DonViTinh='$donvitinh' WHERE idmonan= $idmonan";
                    } else {
                        $str = "UPDATE monan SET tenmonan = '$tenmon', mota = '$mota', DonGia = '$gia',
                                iddm='$danhmuc', DonViTinh='$donvitinh' WHERE idmonan= $idmonan";
                    }
                    if ($conn->query($str)) {
                        echo "<script>alert('Sửa thành công'); window.location.href='index.php?page=dsmonan'</script>";
                    } else {
                        echo "<script>alert('Sửa thất bại'); window.location.href='index.php?page=suamonan'</script>";
                    }
                }
            }

            ?>
        </div>
    </div>
</div>