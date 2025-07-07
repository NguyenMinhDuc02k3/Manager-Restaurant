<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['nhanvien_id'])) {
    // Kết nối CSDL
    $conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
    if (!$conn) {
        die("Kết nối thất bại: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8");

    // Khởi tạo đối tượng Permission
    require_once 'class/clsPermission.php';
    $permission = new Permission($conn);

    include("layout/header.php");
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        $action = 'view'; // Mặc định là action view

        // Xác định action dựa trên tên trang
        if (strpos($page, 'them') === 0) {
            $action = 'add';
        } else if (strpos($page, 'sua') === 0) {
            $action = 'edit';
        }

        // Kiểm tra quyền truy cập
        if (!$permission->checkAccess($page, $action)) {
            echo "<div class='alert alert-danger'>Bạn không có quyền truy cập trang này!</div>";
            include("page/quanly.php");
        } else {
            // Handle pages with slashes in their path
            if ($page == 'thanhtoan/payment') {
                include('page/thanhtoan/payment.php');
            } else if ($page == 'thanhtoan/vnpay_callback') {
                include('page/thanhtoan/vnpay_callback.php');
            } 
            // Original routing logic
            else if ($page == 'dsnhanvien') {
                include('page/nhanvien/dsnhanvien.php');
            } else if ($page == 'themnv') {
                include('page/nhanvien/themnv.php');
            } else if ($page == 'suanv') {
                include('page/nhanvien/suanv.php');
            } else if ($page == 'dskhachhang') {
                include('page/khachhang/dskhachhang.php');
            } else if ($page == 'themkh') {
                include('page/khachhang/themkh.php');
            } else if ($page == 'suakh') {
                include('page/khachhang/suakh.php');
            } else if ($page == 'dsmonan') {
                include('page/monan/dsmonan.php');
            } else if ($page == 'monantheodm') {
                include('page/monan/monantheodm.php');
            } else if ($page == 'themmonan') {
                include('page/monan/themmon.php');
            } else if ($page == 'suamonan') {
                include('page/monan/suamon.php');
            } else if ($page == 'timkiem') {
                include('page/timkiem.php');
            } else if ($page == 'dstonkho') {
                include('page/tonkho/dstonkho.php');
            } else if ($page == 'tonkhotheoloai') {
                include('page/tonkho/tonkhotheoloai.php');
            } else if ($page == 'themtonkho') {
                include('page/tonkho/themtonkho.php');
            } else if ($page == 'suatonkho') {
                include('page/tonkho/suatonkho.php');
            } else if ($page == 'dsdonhang') {
                include('page/donhang/dsdonhang.php');
            } else if ($page == 'xemDH') {
                include('page/donhang/chitietdonhang.php');
            } else if ($page == 'themDH') {
                include('page/donhang/themDH.php');
            } else if ($page == 'suaDH') {
                include('page/donhang/suaDH.php');
            } else if ($page == 'dshoadon') {
                include('page/hoadon/dshoadon.php');
            } else if ($page == 'payment') {
                include('page/thanhtoan/payment.php');
            } else if ($page == 'chitietHD') {
                include('page/hoadon/chitiethoadon.php');
            } else if ($page == 'phanquyen') {
                include('page/phanquyen/phanquyen.php');
            } else if ($page == 'dangxuat') {
                include('page/dangxuat.php');
            } else {
                include("page/quanly.php");
            }
        }
    } else {
        include("page/quanly.php");
    }
    
    
    
    include("layout/footer.php");
} else {
    include("page/dangnhap.php");
}
?>