<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

session_start();
session_unset();
session_destroy();

echo "<script>
    alert('Đăng xuất thành công!');
    window.location.href = 'index.php';
</script>";
exit();

?>