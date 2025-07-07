<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.';
unset($_SESSION['error']);
unset($_SESSION['booking']);
unset($_SESSION['selected_monan']);
unset($_SESSION['madatban']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lỗi - Restaurant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .container { max-width: 1200px; }
        .error { color: red; font-size: 16px; margin-bottom: 20px; }
        .btn-primary {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        .btn-primary:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
        }
    </style>
</head>
<body>
    <div class="container-xxl bg-white p-0">
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <div class="container-xxl py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">Lỗi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php?page=trangchu">Home</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Lỗi</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container my-5">
            <h3>Đã xảy ra lỗi</h3>
            <div class="error">
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
            <div class="mt-4">
                <a href="index.php?page=trangchu" class="btn btn-primary">Quay về trang chủ</a>
                <a href="index.php?page=booking" class="btn btn-secondary">Thử đặt lại</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>