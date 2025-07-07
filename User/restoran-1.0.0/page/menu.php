<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['page'])) {
    $page = 'menu';
} else {
    $page = $_GET['page'];
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực Đơn - Restoran</title>
</head>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Đang tải...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Navbar & Hero Start -->


        <div class="container-xxl py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown" style="font-size: 70px;"><b>THỰC ĐƠN</b></h1>
                <!-- <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Pages</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Menu</li>
                        </ol>
                    </nav> -->
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->


    <!-- Menu Start -->
    <div class="container-xxl py-5 ">
        <div class="container pb-5">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h5 class="section-title ff-secondary text-center text-primary fw-normal">Thực Đơn</h5>
                <h1 class="mb-5">Món Ăn Nổi Bật</h1>
                <hr>
            </div>

            <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
         

                <div class="tab-content">
                    <div id="tab-1" class="tab-pane fade show p-0 active">
                        <div class="row g-4">
                            <?php
                            $conn = mysqli_connect("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                            if ($conn) {
                                // Thiết lập UTF-8 cho kết nối
                                mysqli_set_charset($conn, "utf8");
                                
                                $str = "SELECT m.*, COUNT(ct.idmonan) as total_ordered 
                                        FROM monan m
                                        JOIN chitiethoadon ct ON m.idmonan = ct.idmonan
                                        JOIN hoadon h ON ct.idHD = h.idHD
                                        GROUP BY m.idmonan
                                        ORDER BY total_ordered DESC
                                        LIMIT 6";
                                $result = mysqli_query($conn, $str);
                                if ($result->num_rows > 0) {
                                    echo "<div class='row'>";
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "
                                            <div class='col-md-6 mt-5'>
                                                <div class='d-flex align-items-center mb-4'>
                                                    <img class='flex-shrink-0 img-fluid rounded' src='img/{$row['hinhanh']}' 
                                                        style='width: 150px; object-fit: cover;'>
                                                    <div class='w-100 d-flex flex-column text-start ps-4'>
                                                        <h5 class='d-flex justify-content-between border-bottom pb-2'>
                                                            <span>{$row['tenmonan']}</span>
                                                            <span class='text-primary'>" . number_format($row['DonGia'], 0, ',', '.') . "đ</span>
                                                        </h5>
                                                        <small class='fst-italic'>{$row['mota']}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            ";
                                    }
                                    echo "</div>";

                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>
                <!-- Khai vị -->
                <div class="container pt-5 pb-5">
                    <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                        <h5 class="section-title ff-secondary text-center text-primary fw-normal">Thực Đơn</h5>
                        <h1 class="mb-5">Khai vị</h1>
                        <hr>
                    </div>
                    <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane fade show p-0 active">
                                <div class="row g-4">
                                    <?php
                                    $conn = mysqli_connect("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                                    if ($conn) {
                                        // Thiết lập UTF-8 cho kết nối
                                        mysqli_set_charset($conn, "utf8");
                                        
                                        $str = "select*from monan where iddm = 1 ";
                                        $result = mysqli_query($conn, $str);
                                        if ($result->num_rows > 0) {
                                            echo "<div class='row'>";
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo "
                                            <div class='col-md-6 mt-5'>
                                                <div class='d-flex align-items-center mb-4'>
                                                    <img class='flex-shrink-0 img-fluid rounded' src='img/{$row['hinhanh']}' 
                                                        style='width: 150px; object-fit: cover;'>
                                                    <div class='w-100 d-flex flex-column text-start ps-4'>
                                                        <h5 class='d-flex justify-content-between border-bottom pb-2'>
                                                            <span>{$row['tenmonan']}</span>
                                                            <span class='text-primary'>" . number_format($row['DonGia'], 0, ',', '.') . "đ</span>
                                                        </h5>
                                                        <small class='fst-italic'>{$row['mota']}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            ";
                                            }
                                            echo "</div>";

                                        }
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Món chính -->
                <div class="container pt-5 pb-5">
                    <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                        <h5 class="section-title ff-secondary text-center text-primary fw-normal">Thực Đơn</h5>
                        <h1 class="mb-5">Món chính</h1>
                        <hr>
                    </div>
                    <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane fade show p-0 active">
                                <div class="row g-4">
                                    <?php
                                    $conn = mysqli_connect("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                                    if ($conn) {
                                        // Thiết lập UTF-8 cho kết nối
                                        mysqli_set_charset($conn, "utf8");
                                        
                                        $str = "select*from monan where iddm = 2 ";
                                        $result = mysqli_query($conn, $str);
                                        if ($result->num_rows > 0) {
                                            echo "<div class='row'>";
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo "
                                            <div class='col-md-6 mt-5'>
                                                <div class='d-flex align-items-center mb-4'>
                                                    <img class='flex-shrink-0 img-fluid rounded' src='img/{$row['hinhanh']}' 
                                                        style='width: 150px; object-fit: cover;'>
                                                    <div class='w-100 d-flex flex-column text-start ps-4'>
                                                        <h5 class='d-flex justify-content-between border-bottom pb-2'>
                                                            <span>{$row['tenmonan']}</span>
                                                            <span class='text-primary'>" . number_format($row['DonGia'], 0, ',', '.') . "đ</span>
                                                        </h5>
                                                        <small class='fst-italic'>{$row['mota']}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            ";
                                            }
                                            echo "</div>";

                                        }
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tráng miệng -->
                <div class="container pt-5 pb-5">
                    <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                        <h5 class="section-title ff-secondary text-center text-primary fw-normal">Thực Đơn</h5>
                        <h1 class="mb-5">Tráng miệng</h1>
                        <hr>
                    </div>
                    <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane fade show p-0 active">
                                <div class="row g-4">
                                    <?php
                                    $conn = mysqli_connect("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                                    if ($conn) {
                                        // Thiết lập UTF-8 cho kết nối
                                        mysqli_set_charset($conn, "utf8");
                                        
                                        $str = "select*from monan where iddm = 3 ";
                                        $result = mysqli_query($conn, $str);
                                        if ($result->num_rows > 0) {
                                            echo "<div class='row'>";
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo "
                                            <div class='col-md-6 mt-5'>
                                                <div class='d-flex align-items-center mb-4'>
                                                    <img class='flex-shrink-0 img-fluid rounded' src='img/{$row['hinhanh']}' 
                                                        style='width: 150px; object-fit: cover;'>
                                                    <div class='w-100 d-flex flex-column text-start ps-4'>
                                                        <h5 class='d-flex justify-content-between border-bottom pb-2'>
                                                            <span>{$row['tenmonan']}</span>
                                                            <span class='text-primary'>" . number_format($row['DonGia'], 0, ',', '.') . "đ</span>
                                                        </h5>
                                                        <small class='fst-italic'>{$row['mota']}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            ";
                                            }
                                            echo "</div>";

                                        }
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Đồ uống -->
                <div class="container pt-5 pb-5">
                    <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                        <h5 class="section-title ff-secondary text-center text-primary fw-normal">Thực Đơn</h5>
                        <h1 class="mb-5">Đồ uống</h1>
                        <hr>
                    </div>
                    <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane fade show p-0 active">
                                <div class="row g-4">
                                    <?php
                                    $conn = mysqli_connect("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                                    if ($conn) {
                                        // Thiết lập UTF-8 cho kết nối
                                        mysqli_set_charset($conn, "utf8");
                                        
                                        $str = "select*from monan where iddm = 4 ";
                                        $result = mysqli_query($conn, $str);
                                        if ($result->num_rows > 0) {
                                            echo "<div class='row'>";
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo "
                                            <div class='col-md-6 mt-5'>
                                                <div class='d-flex align-items-center mb-4'>
                                                    <img class='flex-shrink-0 img-fluid rounded' src='img/{$row['hinhanh']}' 
                                                        style='width: 150px; object-fit: cover;'>
                                                    <div class='w-100 d-flex flex-column text-start ps-4'>
                                                        <h5 class='d-flex justify-content-between border-bottom pb-2'>
                                                            <span>{$row['tenmonan']}</span>
                                                            <span class='text-primary'>" . number_format($row['DonGia'], 0, ',', '.') . "đ</span>
                                                        </h5>
                                                        <small class='fst-italic'>{$row['mota']}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            ";
                                            }
                                            echo "</div>";

                                        }
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Đặc biệt -->
                <div class="container pt-5 pb-5">
                    <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                        <h5 class="section-title ff-secondary text-center text-primary fw-normal">Thực Đơn</h5>
                        <h1 class="mb-5">Đặc biệt</h1>
                        <hr>
                    </div>

                    <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane fade show p-0 active">
                                <div class="row g-4">
                                    <?php
                                    $conn = mysqli_connect("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
                                    if ($conn) {
                                        // Thiết lập UTF-8 cho kết nối
                                        mysqli_set_charset($conn, "utf8");
                                        
                                        $str = "SELECT * FROM monan WHERE iddm = 5";
                                        $result = mysqli_query($conn, $str);
                                        if ($result->num_rows > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo "
                                <div class='col-12'>
                                    <div class='d-flex flex-lg-row flex-column align-items-center bg-white shadow-sm p-4 rounded-3'>
                                        <img src='img/{$row['hinhanh']}' class='img-fluid rounded mb-3 mb-lg-0' style='width: 300px; height: auto; object-fit: cover;'>
                                        <div class='ps-lg-4 text-start'>
                                            <h4 class='mb-2 fw-bold'>{$row['tenmonan']} 
                                                <span class='text-primary float-end'>" . number_format($row['DonGia'], 0, ',', '.') . "đ</span>
                                            </h4>
                                            <p class='fst-italic'>{$row['mota']}</p>
                                        </div>
                                    </div>
                                </div>
                                ";
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <!-- Menu End -->



</body>

</html>