<?php
// Tắt tất cả các thông báo lỗi
error_reporting(0);

// Đảm bảo trả về JSON
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Nếu là OPTIONS request, trả về 200 và dừng
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Đường dẫn đến file direct_chat.php gốc
$original_chat_api = __DIR__ . '/../User/restoran-1.0.0/api/direct_chat.php';

try {
    // Xử lý request
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && isset($data['message'])) {
        $message = $data['message'];
        
        // Xử lý tin nhắn
        $result = handleMessage($message);
        
        // Trả về kết quả
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // Trả về lỗi nếu không có tin nhắn
    header('Content-Type: application/json');
    echo json_encode([
        'intent' => 'unknown',
        'message' => 'Xin lỗi, tôi không hiểu câu hỏi của bạn. Vui lòng thử lại với câu hỏi khác.',
        'error' => 'Invalid request'
    ]);
    exit;

} catch (Throwable $e) {
    // Bắt tất cả các lỗi và exception
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}

// Tạo phản hồi dựa trên intent
function generateResponse($intent, $message) {
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
                return "Nhà hàng có nhiều mức giá phù hợp với nhiều đối tượng khách hàng. Các món khai vị từ 45.000đ - 120.000đ, món chính từ 85.000đ - 250.000đ, tráng miệng từ 35.000đ - 75.000đ. Chi phí trung bình cho một người khoảng 200.000đ - 350.000đ. Vui lòng xem menu đầy đủ tại nhà hàng hoặc trên website của chúng tôi. 💰🍽️";
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
            if (isInappropriateOrNonsenseQuestion($message)) {
                return "Xin lỗi, tôi là trợ lý ảo của nhà hàng và chỉ có thể trả lời các câu hỏi liên quan đến nhà hàng. Bạn có thể hỏi về giờ mở cửa, địa chỉ, món ăn, khuyến mãi, đặt bàn hoặc tiện ích của nhà hàng. 🙂";
            } else {
                return "Xin lỗi, tôi chỉ có thể cung cấp thông tin về nhà hàng. Bạn có thể hỏi tôi về giờ mở cửa, địa chỉ, thực đơn, khuyến mãi, đặt bàn hoặc các tiện ích của nhà hàng. ℹ️";
            }
        default:
            return "Xin lỗi, tôi không hiểu câu hỏi của bạn. Bạn có thể hỏi về giờ mở cửa, địa chỉ, món ăn, khuyến mãi, đặt bàn hoặc tiện ích của nhà hàng. ℹ️";
    }
}
?> 