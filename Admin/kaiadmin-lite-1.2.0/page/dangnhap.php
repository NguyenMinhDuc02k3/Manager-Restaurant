<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Bắt đầu session nếu chưa được bắt đầu
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['nhanvien_id'])) {
    header("Location: index.php");
    exit();
}

// Kết nối CSDL
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Xử lý đăng nhập
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    // Mã hóa mật khẩu nhập vào bằng MD5 để so sánh
    $hashed_password = md5($password);

    // Bảo vệ SQL Injection
    $stmt = $conn->prepare("SELECT n.idnv, n.password, n.idvaitro, v.quyen 
                            FROM nhanvien n 
                            LEFT JOIN vaitro v ON n.idvaitro = v.idvaitro 
                            WHERE n.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($hashed_password === $row['password']) { // So sánh mật khẩu đã mã hóa MD5
            $_SESSION['nhanvien_id'] = $row['idnv'];
            $_SESSION['vaitro_id'] = $row['idvaitro'];
            $_SESSION['permissions'] = $row['quyen'] ? explode(",", $row['quyen']) : [];
            // Debug: In ra thông tin để kiểm tra
            /*
            echo "<pre>";
            echo "Dữ liệu từ CSDL:\n";
            print_r($row);
            echo "Giá trị SESSION:\n";
            print_r($_SESSION);
            echo "</pre>";
            exit();
            */
            header("Location: index.php");
            exit();
        } else {
            $error = "Sai mật khẩu.";
        }
    } else {
        $error = "Email không tồn tại.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập nhân viên</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4">Đăng nhập nhân viên</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
        </form>
    </div>
</body>
</html>