<?php
// Hiển thị tất cả lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra xem mô hình đã được huấn luyện chưa
if (!file_exists(__DIR__ . '/restaurant_intent_model')) {
    echo "<h2>Bước 1: Huấn luyện mô hình</h2>";
    echo "<p>Mô hình chưa được huấn luyện. Đang huấn luyện...</p>";
    
    // Chạy file huấn luyện
    ob_start();
    include __DIR__ . '/nlp_model.php';
    $output = ob_get_clean();
    
    echo "<pre>$output</pre>";
}

// Tạo form để test
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test NLP Model</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border-left: 5px solid #4CAF50;
        }
        .intent {
            color: #1a73e8;
            font-weight: bold;
        }
        .examples {
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .examples h3 {
            margin-top: 0;
        }
        .examples ul {
            padding-left: 20px;
        }
        .debug {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border-left: 5px solid #1a73e8;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Test NLP Model cho Chatbot Nhà Hàng</h1>
    
    <div class="container">
        <h2>Nhập câu hỏi để kiểm tra</h2>
        
        <form id="testForm" method="post" action="">
            <div class="form-group">
                <label for="message">Câu hỏi:</label>
                <input type="text" id="message" name="message" placeholder="Ví dụ: nhà hàng mở cửa lúc mấy giờ?" required>
            </div>
            
            <button type="submit">Kiểm tra</button>
        </form>
        
        <?php
        // Xử lý form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message'])) {
            $message = $_POST['message'];
            
            // Gọi file use_model.php để dự đoán
            require_once __DIR__ . '/use_model.php';
            
            $intent = predictIntent($message, $classifier, $vocabulary, $stopwords);
            $response = getResponseByIntent($intent, $message);
            
            // Hiển thị kết quả xử lý
            $words = preprocessText($message);
            
            // Loại bỏ stopwords
            $filteredWords = [];
            foreach ($words as $word) {
                if (!in_array($word, $stopwords) && mb_strlen($word, 'UTF-8') > 1) {
                    $filteredWords[] = $word;
                }
            }
            
            echo '<div class="result">';
            echo '<p><strong>Câu hỏi:</strong> ' . htmlspecialchars($message) . '</p>';
            echo '<p><strong>Intent được nhận diện:</strong> <span class="intent">' . htmlspecialchars($intent) . '</span></p>';
            echo '<p><strong>Phản hồi:</strong> ' . htmlspecialchars($response) . '</p>';
            echo '</div>';
            
            echo '<div class="debug">';
            echo '<p><strong>Phân tích:</strong></p>';
            echo '<p>Các từ sau khi tiền xử lý: ' . htmlspecialchars(implode(', ', $words)) . '</p>';
            echo '<p>Các từ sau khi loại bỏ stopwords: ' . htmlspecialchars(implode(', ', $filteredWords)) . '</p>';
            echo '</div>';
        }
        ?>
        
        <div class="examples">
            <h3>Các ví dụ câu hỏi để test:</h3>
            <ul>
                <li>Nhà hàng các bạn mở cửa lúc mấy giờ vậy?</li>
                <li>Cho mình hỏi địa chỉ nhà hàng</li>
                <li>Nhà hàng có món gì ngon nhất</li>
                <li>Có chương trình khuyến mãi nào không</li>
                <li>Tôi muốn đặt bàn cho 2 người tối nay</li>
                <li>Đặt bàn cho nhóm 10 người thì làm thế nào</li>
            </ul>
        </div>
    </div>
</body>
</html> 