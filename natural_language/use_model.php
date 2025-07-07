<?php
// Kiểm tra và tạo autoload nếu chưa có
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("Vui lòng chạy 'composer install' trước khi sử dụng script này.");
}

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\ModelManager;

// Kiểm tra xem mô hình đã được huấn luyện chưa
if (!file_exists(__DIR__ . '/restaurant_intent_model') || 
    !file_exists(__DIR__ . '/vocabulary.json') ||
    !file_exists(__DIR__ . '/stopwords.json')) {
    die("Vui lòng chạy nlp_model.php trước để huấn luyện mô hình.");
}

// Tải mô hình
$modelManager = new ModelManager();
$classifier = $modelManager->restoreFromFile(__DIR__ . '/restaurant_intent_model');

// Tải từ điển và stopwords
$vocabulary = json_decode(file_get_contents(__DIR__ . '/vocabulary.json'), true);
$stopwords = json_decode(file_get_contents(__DIR__ . '/stopwords.json'), true);

// Tải keywords nếu có
$keywords = [];
if (file_exists(__DIR__ . '/keywords.json')) {
    $keywords = json_decode(file_get_contents(__DIR__ . '/keywords.json'), true);
}

// Hàm tiền xử lý văn bản
function preprocessText($text) {
    // Chuyển về chữ thường
    $text = mb_strtolower($text, 'UTF-8');
    
    // Loại bỏ các ký tự đặc biệt
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
    
    // Tách từ
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    
    return $words;
}

// Hàm kiểm tra nếu câu hỏi liên quan đến giá cả của nhà hàng
function isPriceRelatedQuestion($message) {
    $priceKeywords = ['giá', 'bao nhiêu', 'chi phí', 'đắt', 'rẻ', 'tiền'];
    $restaurantKeywords = ['món', 'ăn', 'thực đơn', 'menu', 'đồ uống', 'nhà hàng', 'quán'];
    $compareKeywords = ['so sánh', 'so với', 'đắt hơn', 'rẻ hơn', 'so'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    $hasPriceKeyword = false;
    foreach ($priceKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            $hasPriceKeyword = true;
            break;
        }
    }
    
    $hasCompareKeyword = false;
    foreach ($compareKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            $hasCompareKeyword = true;
            break;
        }
    }
    
    $hasRestaurantKeyword = false;
    foreach ($restaurantKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            $hasRestaurantKeyword = true;
            break;
        }
    }
    
    // Nếu có từ khóa về giá hoặc so sánh, và có từ khóa về nhà hàng
    return ($hasPriceKeyword || ($hasCompareKeyword && $hasRestaurantKeyword)) && $hasRestaurantKeyword;
}

// Hàm kiểm tra nếu câu hỏi liên quan đến vật liệu xây dựng
function isConstructionMaterialQuestion($message) {
    $constructionKeywords = ['vật liệu', 'xây dựng', 'xi măng', 'cát', 'sắt', 'thép', 'gạch', 'xây nhà'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    foreach ($constructionKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

// Hàm kiểm tra nếu câu hỏi liên quan đến nội thất, đồ gỗ
function isFurnitureQuestion($message) {
    $furnitureKeywords = ['bàn thờ', 'bàn ghế', 'bàn làm việc', 'bàn học', 'bàn trang điểm', 'bàn gỗ', 'ghế gỗ', 'tủ', 'kệ', 'giường', 'nội thất', 'đồ gỗ'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    foreach ($furnitureKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            // Loại trừ các trường hợp liên quan đến nhà hàng
            if (mb_strpos($message, 'đặt bàn') !== false || 
                mb_strpos($message, 'bàn ăn nhà hàng') !== false || 
                mb_strpos($message, 'bàn trong nhà hàng') !== false) {
                return false;
            }
            return true;
        }
    }
    
    return false;
}

// Hàm kiểm tra nếu câu hỏi liên quan đến nấu ăn, công thức
function isCookingQuestion($message) {
    $cookingKeywords = ['cách nấu', 'công thức', 'dạy nấu', 'hướng dẫn làm', 'chế biến', 'cách làm'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    foreach ($cookingKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

// Hàm kiểm tra nếu câu hỏi liên quan đến kinh doanh nhà hàng
function isRestaurantBusinessQuestion($message) {
    $businessKeywords = ['mở nhà hàng', 'kinh doanh nhà hàng', 'giấy phép', 'chi phí mở', 'đầu tư nhà hàng', 'thiết kế nhà hàng', 'trang trí nhà hàng'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    foreach ($businessKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

// Hàm kiểm tra nếu câu hỏi liên quan đến món chay
function isVegetarianFoodQuestion($message) {
    $vegetarianKeywords = ['món chay', 'đồ chay', 'chay', 'ăn chay', 'thực đơn chay'];
    $restaurantKeywords = ['nhà hàng', 'quán', 'menu', 'thực đơn'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    $hasVegetarianKeyword = false;
    foreach ($vegetarianKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            $hasVegetarianKeyword = true;
            break;
        }
    }
    
    $hasRestaurantKeyword = false;
    foreach ($restaurantKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            $hasRestaurantKeyword = true;
            break;
        }
    }
    
    // Nếu chỉ có từ "chay" đơn lẻ, cũng xem như là hỏi về món chay
    if (mb_strpos($message, 'chay') !== false) {
        return true;
    }
    
    // Nếu có từ khóa về món chay và từ khóa về nhà hàng
    return $hasVegetarianKeyword && ($hasRestaurantKeyword || mb_strpos($message, 'có') !== false);
}

// Hàm kiểm tra nếu câu hỏi liên quan đến wifi và tiện ích
function isFacilitiesQuestion($message) {
    $facilitiesKeywords = ['wifi', 'đỗ xe', 'đậu xe', 'phòng riêng', 'hút thuốc', 'trẻ em', 'khuyết tật', 'thú cưng', 'ngoài trời', 'nhạc sống', 'vệ sinh', 'máy lạnh', 'tiện ích', 'dịch vụ', 'vip', 'máy chiếu'];
    $restaurantKeywords = ['nhà hàng', 'quán', 'chỗ'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    $hasFacilitiesKeyword = false;
    foreach ($facilitiesKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            $hasFacilitiesKeyword = true;
            break;
        }
    }
    
    $hasRestaurantKeyword = false;
    foreach ($restaurantKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            $hasRestaurantKeyword = true;
            break;
        }
    }
    
    // Nếu chỉ có từ "wifi" đơn lẻ, cũng xem như là hỏi về tiện ích
    if (mb_strpos($message, 'wifi') !== false || mb_strpos($message, 'đỗ xe') !== false) {
        return true;
    }
    
    // Nếu có từ khóa về tiện ích và từ khóa về nhà hàng
    return $hasFacilitiesKeyword && ($hasRestaurantKeyword || mb_strpos($message, 'có') !== false);
}

// Hàm kiểm tra nếu câu hỏi chứa từ ngữ không phù hợp hoặc vô nghĩa
function isInappropriateOrNonsenseQuestion($message) {
    $inappropriateKeywords = ['chán', 'cc', 'dm', 'đm', 'dcm', 'đcm', 'đéo', 'deo', 'cút', 'cut', 'vl', 'vcl', 'wtf', 'fuck', 'shit', 'crap'];
    $nonsensePatterns = ['?', '??', '???', '!', '!!', '!!!', '.', '..', '...', ',', ',,', ',,,', '/', '//', '///', '\\', '\\\\', '\\\\\\'];
    $nonsensePhrases = ['hỏi chấm', 'cc gì vậy', 'm trả lời gì thế', 'nói gì vậy', 'ai hiểu gì đâu', 'nói linh tinh', 'trả lời linh tinh', 'nói nhảm', 'trả lời nhảm'];
    
    $message = mb_strtolower($message, 'UTF-8');
    
    // Kiểm tra từ ngữ không phù hợp
    foreach ($inappropriateKeywords as $keyword) {
        if (mb_strpos($message, $keyword) !== false) {
            return true;
        }
    }
    
    // Kiểm tra các cụm từ vô nghĩa
    foreach ($nonsensePhrases as $phrase) {
        if (mb_strpos($message, $phrase) !== false) {
            return true;
        }
    }
    
    // Kiểm tra các câu hỏi vô nghĩa
    if (strlen(trim($message)) <= 3) {
        return true; // Câu quá ngắn, có thể là vô nghĩa
    }
    
    // Kiểm tra nếu câu chỉ chứa các ký tự đặc biệt
    $specialCharsOnly = true;
    foreach (str_split($message) as $char) {
        if (ctype_alnum($char) || mb_strlen($char, 'UTF-8') > 1) { // Nếu là chữ cái, số hoặc ký tự Unicode (tiếng Việt)
            $specialCharsOnly = false;
            break;
        }
    }
    
    if ($specialCharsOnly) {
        return true;
    }
    
    // Kiểm tra nếu câu chỉ chứa các ký tự lặp lại
    if (preg_match('/^(.)\1+$/', $message)) {
        return true;
    }
    
    return false;
}

// Hàm dự đoán ý định
function predictIntent($text, $classifier, $vocabulary, $stopwords) {
    global $keywords;
    
    // Kiểm tra trước nếu câu hỏi chứa từ ngữ không phù hợp hoặc vô nghĩa
    if (isInappropriateOrNonsenseQuestion($text)) {
        return 'out_of_scope';
    }
    
    // Kiểm tra trước nếu câu hỏi liên quan đến vật liệu xây dựng
    if (isConstructionMaterialQuestion($text)) {
        return 'out_of_scope';
    }
    
    // Kiểm tra trước nếu câu hỏi liên quan đến nội thất, đồ gỗ
    if (isFurnitureQuestion($text)) {
        return 'out_of_scope';
    }
    
    // Kiểm tra trước nếu câu hỏi liên quan đến nấu ăn, công thức
    if (isCookingQuestion($text)) {
        return 'out_of_scope';
    }
    
    // Kiểm tra trước nếu câu hỏi liên quan đến kinh doanh nhà hàng
    if (isRestaurantBusinessQuestion($text)) {
        return 'out_of_scope';
    }
    
    // Kiểm tra trước nếu câu hỏi liên quan đến wifi và tiện ích
    if (isFacilitiesQuestion($text)) {
        return 'facilities';
    }
    
    // Kiểm tra trước nếu câu hỏi liên quan đến món chay
    if (isVegetarianFoodQuestion($text)) {
        return 'menu';
    }
    
    // Kiểm tra trước nếu câu hỏi liên quan đến giá cả của nhà hàng
    if (isPriceRelatedQuestion($text)) {
        return 'menu';
    }
    
    // Tiền xử lý văn bản
    $words = preprocessText($text);
    
    // Loại bỏ stopwords
    $filteredWords = [];
    foreach ($words as $word) {
        if (!in_array($word, $stopwords) && mb_strlen($word, 'UTF-8') > 1) {
            $filteredWords[] = $word;
        }
    }
    
    // Mô hình đơn giản dựa trên từ khóa
    $simpleIntentScore = simpleKeywordBasedIntent($text, $filteredWords);
    $highestScore = 0;
    $simpleIntent = null;
    
    foreach ($simpleIntentScore as $intent => $score) {
        if ($score > $highestScore) {
            $highestScore = $score;
            $simpleIntent = $intent;
        }
    }
    
    // Nếu mô hình đơn giản có độ tin cậy cao (>= 0.6), sử dụng kết quả của nó
    if ($highestScore >= 0.6) {
        return $simpleIntent;
    }
    
    // Tạo vector đặc trưng
    $feature = array_fill(0, count($vocabulary), 0);
    foreach ($filteredWords as $word) {
        $index = array_search($word, $vocabulary);
        if ($index !== false) {
            $feature[$index]++;
        }
    }
    
    // Tăng trọng số cho các từ khóa quan trọng
    if (!empty($keywords)) {
        foreach ($keywords as $intent => $keywordList) {
            foreach ($keywordList as $keyword) {
                $keywordWords = explode(' ', $keyword);
                foreach ($keywordWords as $word) {
                    $vocabIndex = array_search($word, $vocabulary);
                    if ($vocabIndex !== false && in_array($word, $filteredWords)) {
                        // Tăng trọng số lên 3 lần
                        $feature[$vocabIndex] *= 3;
                    }
                }
            }
        }
    }
    
    // Dự đoán intent từ mô hình KNN
    $knnIntent = $classifier->predict($feature);
    
    // Nếu mô hình đơn giản có điểm > 0.3 và mô hình KNN dự đoán 'out_of_scope', ưu tiên mô hình đơn giản
    if ($knnIntent === 'out_of_scope' && $highestScore > 0.3) {
        return $simpleIntent;
    }
    
    // Nếu là câu hỏi về menu nhưng KNN dự đoán sai, sửa lại
    if (isMenuQuestion($text) && $knnIntent !== 'menu') {
        return 'menu';
    }
    
    return $knnIntent;
}

// Hàm kiểm tra xem câu hỏi có liên quan đến menu không
function isMenuQuestion($text) {
    $menuKeywords = ['menu', 'thực đơn', 'món ăn', 'món gì', 'có món gì', 'có những món gì', 'món nào', 'món ngon'];
    
    $text = mb_strtolower($text, 'UTF-8');
    
    foreach ($menuKeywords as $keyword) {
        if (mb_strpos($text, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

// Hàm dự đoán intent đơn giản dựa trên từ khóa
function simpleKeywordBasedIntent($text, $filteredWords) {
    $text = mb_strtolower($text, 'UTF-8');
    
    // Định nghĩa từ khóa cho mỗi intent
    $intentKeywords = [
        'opening_hours' => ['mở cửa', 'đóng cửa', 'giờ', 'thời gian', 'mấy giờ', 'khi nào', 'giờ mở', 'giờ đóng'],
        'location' => ['địa chỉ', 'ở đâu', 'chỗ nào', 'vị trí', 'đường', 'quận', 'thành phố', 'đi đến', 'tìm đến'],
        'menu' => ['menu', 'thực đơn', 'món ăn', 'món gì', 'có món gì', 'có những món gì', 'món nào', 'món ngon', 'đặc sản', 'signature'],
        'promotion' => ['khuyến mãi', 'ưu đãi', 'giảm giá', 'voucher', 'combo', 'deal', 'miễn phí', 'tặng'],
        'reservation' => ['đặt bàn', 'đặt chỗ', 'đặt trước', 'đặt tiệc', 'book', 'reservation', 'giữ chỗ'],
        'facilities' => ['wifi', 'đỗ xe', 'đậu xe', 'phòng riêng', 'tiện ích', 'máy lạnh', 'toilet', 'nhà vệ sinh', 'phòng vip']
    ];
    
    // Tính điểm cho mỗi intent
    $scores = [
        'opening_hours' => 0,
        'location' => 0,
        'menu' => 0,
        'promotion' => 0,
        'reservation' => 0,
        'facilities' => 0,
        'out_of_scope' => 0
    ];
    
    // Tính điểm dựa trên từ khóa xuất hiện trong câu hỏi
    foreach ($intentKeywords as $intent => $keywords) {
        foreach ($keywords as $keyword) {
            if (mb_strpos($text, $keyword) !== false) {
                // Từ khóa hoàn toàn khớp
                $scores[$intent] += 0.3;
            } else {
                // Kiểm tra từng từ trong từ khóa
                $keywordWords = explode(' ', $keyword);
                foreach ($keywordWords as $word) {
                    if (in_array($word, $filteredWords)) {
                        $scores[$intent] += 0.1;
                    }
                }
            }
        }
    }
    
    // Kiểm tra các trường hợp đặc biệt
    if (mb_strpos($text, 'mở') !== false && mb_strpos($text, 'giờ') !== false) {
        $scores['opening_hours'] += 0.3;
    }
    
    if (mb_strpos($text, 'menu') !== false || mb_strpos($text, 'thực đơn') !== false) {
        $scores['menu'] += 0.3;
    }
    
    if (mb_strpos($text, 'món') !== false && (mb_strpos($text, 'gì') !== false || mb_strpos($text, 'nào') !== false)) {
        $scores['menu'] += 0.3;
    }
    
    return $scores;
}

// Hàm tạo phản hồi dựa trên ý định
function generateResponse($intent) {
    $message = $GLOBALS['current_message'];
    
    switch ($intent) {
        case 'opening_hours':
            return "Nhà hàng mở cửa từ 10:00 đến 22:00 hàng ngày, kể cả cuối tuần và ngày lễ. Giờ cao điểm thường là 11:30-13:30 và 18:00-20:00. 🕙Giờ mở cửa";
        case 'location':
            return "Nhà hàng nằm tại 123 Đường Nguyễn Huệ, Quận 1, TP.HCM. Bạn có thể dễ dàng tìm thấy chúng tôi gần Nhà hát Thành phố. Liên hệ: 028.1234.5678. 📍Địa chỉ";
        case 'menu':
            // Kiểm tra nếu câu hỏi liên quan đến món chay
            if (isVegetarianFoodQuestion($message)) {
                return "Nhà hàng có nhiều món chay ngon như: Rau củ xào (65.000đ), Đậu hũ sốt nấm (80.000đ), Canh rau củ (45.000đ), Cơm chiên rau củ (70.000đ). Quý khách có thể yêu cầu món chay khi đặt bàn hoặc khi dùng bữa tại nhà hàng. 🥗Menu chay";
            }
            // Kiểm tra nếu câu hỏi liên quan đến giá cả
            else if (isPriceRelatedQuestion($message)) {
                return "Nhà hàng có nhiều mức giá phù hợp với nhiều đối tượng khách hàng. Các món khai vị từ 45.000đ - 120.000đ, món chính từ 85.000đ - 250.000đ, tráng miệng từ 35.000đ - 75.000đ. Chi phí trung bình cho một người khoảng 200.000đ - 350.000đ. 💰Giá cả";
            } else {
                return "Nhà hàng chúng tôi phục vụ đa dạng món ăn từ Á đến Âu, đặc biệt là các món hải sản tươi sống và món đặc sản vùng miền. Chúng tôi cũng có thực đơn chay và thực đơn cho trẻ em. Giá từ 50.000đ - 300.000đ/món. 🍽️Menu";
            }
        case 'promotion':
            return "Hiện tại nhà hàng đang có chương trình giảm 15% tổng hóa đơn cho khách hàng đặt bàn online, combo gia đình giảm 20% vào cuối tuần, và tặng món tráng miệng cho nhóm từ 4 người trở lên. 🎁Khuyến mãi";
        case 'reservation':
            return "Bạn có thể đặt bàn qua số điện thoại 028.1234.5678 hoặc đặt online trên website của nhà hàng. Chúng tôi khuyến khích đặt trước ít nhất 2 giờ để đảm bảo có chỗ, đặc biệt vào cuối tuần và ngày lễ. 📝Đặt bàn";
        case 'facilities':
            return "Nhà hàng có wifi miễn phí, bãi đỗ xe rộng rãi, phòng VIP riêng tư, khu vực ngoài trời, và nhiều tiện ích khác để phục vụ quý khách. Vui lòng hỏi nhân viên để biết thêm chi tiết. 📶Tiện ích";
        case 'out_of_scope':
            // Kiểm tra nếu là câu hỏi không phù hợp hoặc vô nghĩa
            if (isInappropriateOrNonsenseQuestion($GLOBALS['current_message'])) {
                return "Xin lỗi, tôi là trợ lý ảo của nhà hàng và chỉ có thể trả lời các câu hỏi liên quan đến nhà hàng. Bạn có thể hỏi về giờ mở cửa, địa chỉ, món ăn, khuyến mãi, đặt bàn hoặc tiện ích của nhà hàng. 🙂";
            } else {
                return "Xin lỗi, tôi chỉ có thể cung cấp thông tin về nhà hàng. Bạn có thể hỏi tôi về giờ mở cửa, địa chỉ, thực đơn, khuyến mãi, đặt bàn hoặc các tiện ích của nhà hàng. ℹ️";
            }
        default:
            return "Xin lỗi, tôi không hiểu câu hỏi của bạn. Bạn có thể hỏi về giờ mở cửa, địa chỉ, món ăn, khuyến mãi, đặt bàn hoặc tiện ích của nhà hàng. ℹ️";
    }
}

// Kiểm tra nếu được gọi trực tiếp từ browser
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
    
    // Nhận message từ POST hoặc GET
    $message = isset($_POST['message']) ? $_POST['message'] : (isset($_GET['message']) ? $_GET['message'] : '');
    
    if (!empty($message)) {
        $intent = predictIntent($message, $classifier, $vocabulary, $stopwords);
        $response = generateResponse($intent);
        
        // Chuẩn bị thông tin debug
        $words = preprocessText($message);
        $filteredWords = [];
        foreach ($words as $word) {
            if (!in_array($word, $stopwords) && mb_strlen($word, 'UTF-8') > 1) {
                $filteredWords[] = $word;
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => $response,
            'intent' => $intent,
            'original' => $message,
            'processed_words' => $filteredWords,
            'nlp_used' => true
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Vui lòng cung cấp nội dung tin nhắn.'
        ]);
    }
} else {
    // Nếu chạy từ command line, cho phép test
    if ($argc > 1) {
        // Lấy câu hỏi từ tham số dòng lệnh
        $message = implode(" ", array_slice($argv, 1));
        
        // Lưu message hiện tại để sử dụng trong hàm generateResponse
        $GLOBALS['current_message'] = $message;
        
        if (!empty($message)) {
            $intent = predictIntent($message, $classifier, $vocabulary, $stopwords);
            $response = generateResponse($intent);
            
            // Chuẩn bị thông tin debug
            $debug_info = [
                'processed_words' => preprocessText($message),
                'intent_detected' => $intent,
                'response' => $response
            ];
            
            // In kết quả
            echo "Câu hỏi: $message\n";
            echo "Intent: $intent\n";
            echo "Trả lời: " . $response . "\n";
        } else {
            echo "Sử dụng: php use_model.php \"câu hỏi của bạn\"\n";
        }
    }
}
?> 