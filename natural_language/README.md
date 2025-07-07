# NLP Chatbot cho Nhà Hàng

Đây là một ứng dụng chatbot đơn giản sử dụng Natural Language Processing (NLP) để hiểu ngữ cảnh của câu hỏi và đưa ra câu trả lời phù hợp.

## Cài đặt

1. Đảm bảo bạn đã cài đặt PHP (>= 7.2) và Composer.

2. Cài đặt các thư viện cần thiết:
   ```
   cd natural_language
   composer install
   ```

## Cách sử dụng

### Huấn luyện mô hình

```
php nlp_model.php
```

Lệnh này sẽ huấn luyện mô hình NLP và lưu các file cần thiết.

### Sử dụng mô hình từ command line

```
php use_model.php "nhà hàng mở cửa lúc mấy giờ?"
```

### Sử dụng giao diện web để test

Truy cập file `test.php` qua trình duyệt web để sử dụng giao diện đơn giản để test mô hình.

### Tích hợp với API hiện có

Để tích hợp mô hình NLP này vào API chatbot hiện có (`direct_chat.php`), bạn có thể thêm đoạn code sau vào file `direct_chat.php`:

```php
// Thêm vào đầu file, sau các khai báo header
require_once 'path/to/natural_language/use_model.php';

// Thay đổi phần xử lý message
if (!empty($message)) {
    // Thử sử dụng mô hình NLP để dự đoán ý định
    try {
        $intent = predictIntent($message, $classifier, $vectorizer, $tfIdfTransformer);
        $response = getResponseByIntent($intent, $message);
    } catch (Exception $e) {
        // Nếu có lỗi, sử dụng phương pháp từ khóa cũ
        $found = false;
        foreach ($special_keywords as $keyword => $answer) {
            // Code hiện tại của bạn
        }
        // ...
    }
}
```

## Cải thiện mô hình

Để cải thiện mô hình, bạn có thể:

1. Thêm nhiều câu hỏi mẫu vào mảng `$samples` trong file `nlp_model.php`
2. Thêm các intent mới và câu trả lời tương ứng
3. Điều chỉnh các tham số của mô hình SVC để tối ưu hóa hiệu suất

## Các intent hiện có

- `opening_hours`: Thông tin về giờ mở cửa
- `location`: Thông tin về địa chỉ nhà hàng
- `menu`: Thông tin về thực đơn và món ăn
- `promotion`: Thông tin về khuyến mãi
- `reservation`: Thông tin về đặt bàn 