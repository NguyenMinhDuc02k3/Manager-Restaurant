<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['page'])) {
    $page ='contact' ;
} else {
    $page = $_GET['page'];
}

// Xử lý gửi form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = mysqli_connect("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
    
    if ($conn) {
        // Thiết lập UTF-8 cho kết nối
        mysqli_set_charset($conn, "utf8");
        
        $hoTen = $_POST['name'];
        $email = $_POST['email'];
        $soDienThoai = $_POST['phone'];
        $chuDe = $_POST['subject'];
        $noiDung = $_POST['message'];
        
        // Kiểm tra xem email có tồn tại trong bảng khachhang không
        $checkEmail = "SELECT idKH FROM khachhang WHERE email = '$email'";
        $result = mysqli_query($conn, $checkEmail);
        $idKH = null;
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $idKH = $row['idKH'];
        }
        
        $sql = "INSERT INTO phanhoi (idKH, HoTen, Email, SoDienThoai, ChuDe, NoiDung) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssss", $idKH, $hoTen, $email, $soDienThoai, $chuDe, $noiDung);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Cảm ơn bạn đã gửi phản hồi!');</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra, vui lòng thử lại!');</script>";
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ - Restoran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
</head>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Đang tải...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Navbar & Hero Start -->
       

            <div class="container-xxl py-5 bg-dark hero-header mb-5">
                <div class="container text-center my-5 pt-5 pb-4">
                    <h1 class="display-3 text-white mb-3 animated slideInDown">Liên Hệ</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="index.php?page=trangchu" class="text-warning">Trang Chủ</a></li>
                            <li class="breadcrumb-item"><a href="#" class="text-warning">Trang</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Liên Hệ</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Navbar & Hero End -->


        <!-- Contact Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Liên Hệ</h5>
                    <h1 class="mb-5">Liên Hệ Cho Bất Kỳ Thắc Mắc Nào</h1>
                </div>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="row gy-4">
                            <div class="col-md-4">
                                <h5 class="section-title ff-secondary fw-normal text-start text-primary">Đặt Bàn</h5>
                                <p><i class="fa fa-envelope-open text-primary me-2"></i>datban@restoran.com</p>
                            </div>
                            <div class="col-md-4">
                                <h5 class="section-title ff-secondary fw-normal text-start text-primary">Chung</h5>
                                <p><i class="fa fa-envelope-open text-primary me-2"></i>info@restoran.com</p>
                            </div>
                            <div class="col-md-4">
                                <h5 class="section-title ff-secondary fw-normal text-start text-primary">Kỹ Thuật</h5>
                                <p><i class="fa fa-envelope-open text-primary me-2"></i>kythuat@restoran.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 wow fadeIn" data-wow-delay="0.1s">
                        <iframe class="position-relative rounded w-100 h-100"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.5177580045663!2d106.69892867465813!3d10.771600089387599!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f40a3b49e59%3A0xa1bd14e483a602db!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBLaG9hIGjhu41jIFThu7Egbmhpw6puIFRQLiBIQ00!5e0!3m2!1svi!2s!4v1699889321195!5m2!1svi!2s"
                            frameborder="0" style="min-height: 350px; border:0;" allowfullscreen="" aria-hidden="false"
                            tabindex="0"></iframe>
                    </div>
                    <div class="col-md-6">
                        <div class="wow fadeInUp" data-wow-delay="0.2s">
                            <form method="POST" action="">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Họ và tên" required>
                                            <label for="name">Họ và tên</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Số điện thoại" required>
                                            <label for="phone">Số điện thoại</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Chủ đề" required>
                                            <label for="subject">Chủ đề</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" placeholder="Nội dung" id="message" name="message" style="height: 150px" required></textarea>
                                            <label for="message">Nội dung</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100 py-3" type="submit">Gửi phản hồi</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contact End -->

</body>

</html>

<script>
$(document).ready(function(){
    $('.testimonial-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        autoplay: true,
        responsive:{
            0:{
                items: 1
            },
            768:{
                items: 2
            },
            992:{
                items: 3
            }
        }
    });
});
</script>