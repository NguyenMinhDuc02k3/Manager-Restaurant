<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['page'])) {
    $page = 'themkh';
} else {
    $page = $_GET['page'];
}

$conn = mysqli_connect(hostname: 'localhost', username: 'hceeab2b55_chung9atm', password: 'Chung2002!', database: 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>
<div class="container mb-5">
    <div class="text-center">
        <h1><b>Thêm khách hàng</b></h1>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <form class="" action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tenKH">Họ tên</label>
                    <input type="tenKH" class="form-control" id="tenKH" name="tenKH" required />
                </div>
                <div class="form-group">
                    <label for="sdt">Số điện thoại </label>
                    <input type="tel" class="form-control" id="sdt" name="sdt" placeholder="Số điện thoại" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                </div>

                <div class="form-group">
                    <label for="email">Email </label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" required />
                </div>
                <div class="form-group">
                    <label for="ngaysinh">Ngày sinh </label>
                    <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" required />
                </div>
                <div class="form-group">
                    <label>Giới tính </label><br />
                    <div class="d-flex">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNam" value="Nam" />
                            <label class="form-check-label" for="gioitinhNam">
                                Nam
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gioitinh" id="gioitinhNu" value="Nữ"
                                checked />
                            <label class="form-check-label" for="gioitinhNu">
                                Nữ
                            </label>
                        </div>
                    </div>
                </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="themKH">Thêm </button>
                    <a href="index.php?page=dskhachhang" class="btn btn-secondary" name="huy">Hủy</a>

                </div>
            </form>
            <?php

            if (isset($_POST['themKH'])) {
                $tenKH = $_POST['tenKH'];
                $sodienthoai = $_POST['sdt'];
                $email = $_POST['email'];
                $gioitinh = $_POST['gioitinh'];
                $ngaysinh = $_POST['ngaysinh'];
                if ($conn) {
                    $stmt = $conn->prepare("INSERT INTO khachhang (tenKH, sodienthoai, email, ngaysinh, gioitinh) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $tenKH, $sodienthoai, $email, $ngaysinh, $gioitinh);
                    
                    if ($stmt->execute()) {
                        echo "<script>alert('Thêm thành công'); window.location.href='index.php?page=dskhachhang'</script>";
                    } else {
                        echo "<script>alert('Thêm thất bại: " . $conn->error . "'); window.location.href='index.php?page=themkh'</script>";
                    }
                }
            }

            ?>
        </div>
    </div>
</div>