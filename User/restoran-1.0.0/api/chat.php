<?php
// Tắt tất cả các thông báo lỗi để tránh ảnh hưởng đến output JSON
error_reporting(0);

// Đảm bảo trả về JSON trong mọi trường hợp
header('Content-Type: application/json');

try {
    // Chuyển hướng yêu cầu đến chatbot_handler.php
    require_once 'chatbot_handler.php';
} catch (Throwable $e) {
    // Bắt tất cả các lỗi và exception, trả về thông báo lỗi dạng JSON
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>