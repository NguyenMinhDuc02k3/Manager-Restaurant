<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['page'])) {
    $page = 'suakh';
} else {
    $page = $_GET['page'];
}
if (isset($_GET['idKH'])) {
    $idkh = $_GET['idKH'];
}
$conn = mysqli_connect(hostname: 'localhost', username: 'hceeab2b55_chung9atm', password: 'Chung2002!', database: 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

$stmt = $conn->prepare("SELECT * FROM khachhang WHERE idKH = ?");
$stmt->bind_param("i", $idkh);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tenKH = $row['tenKH'];
        $sodienthoai = $row['sodienthoai'];
        $email = $row['email'];
        $ngaysinh = $row['ngaysinh'];
        $gioitinh = $row['gioitinh'];
    }
}
?>
<div class="container mb-5">
    <div class="text-center">
        <h1><b>Sửa thông tin khách hàng</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form class="" action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tenKH">Họ tên</label>
                    <input type="tenKH" class="form-control" id="tenKH" name="tenKH" value="<?php echo $tenKH ?>"
                        required />
                    <!-- <small id="emailHelp2" class="form-text text-muted">We'll never share your email with anyone
                    else.</small> -->
                </div>
                <div class="form-group">
                    <label for="sdt">Số điện thoại </label>
                    <input type="tel" class="form-control" id="sdt" name="sdt" placeholder="Số điện thoại"
                        value="<?php echo $sodienthoai ?>" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                </div>

                <div class="form-group">
                    <label for="email">Email </label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com"
                        value="<?php echo $email ?>" required />
                </div>
                <div class="form-group">
                    <label for="ngaysinh">Ngày sinh </label>
                    <input type="date" class="form-control" id="ngaysinh" name="ngaysinh"
                        value="<?php echo $ngaysinh ?>" required />
                </div>
                <div class="form-group">
                    <label>Giới tính </label><br />
                    <div class="d-flex">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNam" value="Nam"
                                <?php if ($gioitinh == "Nam")
                                    echo "checked"; ?> />
                            <label class="form-check-label" for="gioitinhNam">
                                Nam
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNu" value="Nữ"
                                <?php if ($gioitinh == "Nữ")
                                    echo "checked"; ?> />
                            <label class="form-check-label" for="gioitinhNu">
                                Nữ
                            </label>
                        </div>
                    </div>
                </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="suaKH">Sửa </button>
                    <a href="index.php?page=dskhachhang" class="btn btn-secondary" name="huy">Hủy</a>

                </div>
            </form>
            <?php

            if (isset($_POST['suaKH'])) {
                $tenKH = $_POST['tenKH'];
                $sodienthoai = $_POST['sdt'];
                $email = $_POST['email'];
                $gioitinh = $_POST['gioitinh'];
                $ngaysinh = $_POST['ngaysinh'];
                
                if ($conn) {
                    $stmt = $conn->prepare("UPDATE khachhang SET tenKH = ?, sodienthoai = ?, email = ?, gioitinh = ?, ngaysinh = ? WHERE idKH = ?");
                    $stmt->bind_param("sssssi", $tenKH, $sodienthoai, $email, $gioitinh, $ngaysinh, $idkh);
                    
                    if ($stmt->execute()) {
                        echo "<script>alert('Sửa thành công'); window.location.href='index.php?page=dskhachhang'</script>";
                    } else {
                        echo "<script>alert('Sửa thất bại: " . $conn->error . "'); window.location.href='index.php?page=suakh'</script>";
                    }
                }
            }

            ?>
        </div>
    </div>
</div>