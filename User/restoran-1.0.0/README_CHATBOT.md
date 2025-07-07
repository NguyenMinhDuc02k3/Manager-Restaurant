# Hướng dẫn sử dụng Chatbot Nhà hàng

## Giới thiệu
Chatbot nhà hàng là một trợ lý ảo giúp khách hàng tìm hiểu thông tin về nhà hàng, menu, đặt bàn và các dịch vụ khác. Chatbot được tích hợp vào trang web nhà hàng và hoạt động hoàn toàn dựa trên PHP, không cần server Python.

## Cài đặt và sử dụng

### Cài đặt
1. Copy thư mục `page/chatbot.php` vào thư mục `page` của website
2. Copy thư mục `api/chatbot_handler.php` vào thư mục `api` của website
3. Đảm bảo file `api/chat.php` đã được cập nhật để include `chatbot_handler.php`

### Tích hợp vào trang web
Thêm đoạn mã sau vào cuối trang web (trước thẻ đóng body):
```php
<?php include 'page/chatbot.php'; ?>
```

## Tùy chỉnh Chatbot

### Thêm/Sửa câu trả lời
Để thêm hoặc sửa các câu trả lời của chatbot, bạn có thể chỉnh sửa file `api/chatbot_handler.php`:

1. Mở file `api/chatbot_handler.php`
2. Tìm đến phần `$fixed_responses`
3. Thêm hoặc sửa các cặp key-value trong mảng `$fixed_responses`
4. Lưu file

Ví dụ:
```php
$fixed_responses = [
    "nhà hàng mở cửa mấy giờ" => "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "món ăn" => "Nhà hàng có đa dạng món ăn từ khai vị, món chính đến tráng miệng...",
    // Thêm câu trả lời mới ở đây
    "món mới" => "Món mới của tháng này là XYZ, giá 150,000đ. 🍽️",
];
```

### Sử dụng database
Chatbot cũng có thể lấy câu trả lời từ database thông qua bảng `chatbot_qa`. Cấu trúc bảng:

```sql
CREATE TABLE `chatbot_qa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_pattern` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

Bạn có thể thêm câu trả lời vào database:
```sql
INSERT INTO `chatbot_qa` (`question_pattern`, `answer`, `category`) VALUES
('menu', 'SELECT name, price, description FROM monan WHERE status = 1', 'menu'),
('đặt bàn', 'SELECT SoBan, soluongKH, TrangThai FROM ban WHERE TrangThai = "Trống"', 'booking'),
('khuyến mãi', 'SELECT TenKM, NoiDung, NgayBD, NgayKT FROM khuyenmai WHERE NOW() BETWEEN NgayBD AND NgayKT', 'promotion');
```

Nếu `answer` bắt đầu bằng `SELECT`, chatbot sẽ thực thi câu SQL và định dạng kết quả.

### Lưu lịch sử chat
Chatbot tự động lưu lịch sử chat vào bảng `chat_history` nếu có. Cấu trúc bảng:

```sql
CREATE TABLE `chat_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `sender` enum('user','bot') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

## Test Chatbot
Bạn có thể test chatbot bằng cách truy cập file `test_chatbot.php`.

## Tùy chỉnh giao diện
Để tùy chỉnh giao diện chatbot, bạn có thể chỉnh sửa CSS trong file `page/chatbot.php`.

## Xử lý sự cố
- Nếu chatbot không hiển thị, kiểm tra console của trình duyệt để xem lỗi JavaScript
- Nếu chatbot không trả lời, kiểm tra file log của PHP để xem lỗi
- Đảm bảo đường dẫn đến `api/chat.php` là chính xác 