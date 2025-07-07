<?php
// Tắt tất cả các thông báo lỗi để tránh ảnh hưởng đến output JSON
error_reporting(0);

// Thêm CORS headers để cho phép gọi API từ mọi nguồn
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Nếu là OPTIONS request (preflight), trả về 200 và dừng
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Không cần session_start() vì đã được gọi trong chat.php
// header('Content-Type: application/json'); // Đã được xử lý trong chat.php

// Danh sách câu trả lời cố định
$fixed_responses = [
    "nhà hàng mở cửa mấy giờ" => "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "giờ mở cửa" => "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "địa chỉ" => "Nhà hàng nằm tại 123 ABC Street, Quận 1, TP.HCM. Rất hân hạnh được đón tiếp quý khách! 📍",
    "ở đâu" => "Nhà hàng nằm tại 123 ABC Street, Quận 1, TP.HCM. Rất hân hạnh được đón tiếp quý khách! 📍",
    "liên hệ" => "Quý khách có thể liên hệ với chúng tôi qua số điện thoại 0123456789 hoặc email info@restaurant.com. 📞",
    "số điện thoại" => "Quý khách có thể liên hệ với chúng tôi qua số điện thoại 0123456789. 📞",
    "email" => "Email liên hệ của nhà hàng là info@restaurant.com. 📧",
    "dịch vụ" => "Chúng tôi có dịch vụ đặt tiệc, tổ chức sinh nhật, khu vực riêng cho nhóm đông người và phục vụ đặt món mang về. 💝",
    
    "món ăn" => "Nhà hàng có đa dạng món ăn từ khai vị, món chính đến tráng miệng. Các món nổi bật: Vịt quay Bắc Kinh (200,000đ), Cơm chiên hải sản (100,000đ), Bò lúc lắc (95,000đ). 🍽️",
    "món ngon" => "Nhà hàng có nhiều món ngon như Vịt quay Bắc Kinh (200,000đ), Cơm chiên hải sản (100,000đ), Bò lúc lắc (95,000đ). 🍽️",
    "có món nào ngon" => "Nhà hàng có nhiều món ngon như Vịt quay Bắc Kinh (200,000đ), Cơm chiên hải sản (100,000đ), Bò lúc lắc (95,000đ). 🍽️",
    "món đặc biệt" => "Món đặc biệt của nhà hàng là Vịt quay Bắc Kinh (200,000đ) và Cá Lăng Đặc Sắc (350,000đ). 🍽️",
    "món phổ biến" => "Các món được yêu thích nhất tại nhà hàng gồm Cơm chiên hải sản, Bò lúc lắc và Gỏi xoài tôm khô. 🍽️",
    "món chính" => "Các món chính của nhà hàng gồm: Vịt quay Bắc Kinh (200,000đ), Cơm chiên hải sản (100,000đ) và nhiều món khác. 🍽️",
    "khai vị" => "Các món khai vị của nhà hàng gồm: Gỏi xoài tôm khô (90,000đ), Súp hải sản (80,000đ) và nhiều món khác. 🍽️",
    "tráng miệng" => "Các món tráng miệng của nhà hàng gồm: Bánh flan (15,000đ), Kem dâu (20,000đ), Bánh lọt lá dứa (25,000đ). 🍦",
    "đồ uống" => "Nhà hàng có các loại nước ép trái cây tươi, sinh tố, trà, cà phê và các loại bia, rượu vang. 🍹",
    "nước uống" => "Nhà hàng có các loại nước ép trái cây tươi, sinh tố, trà, cà phê và các loại bia, rượu vang. 🍹",
    
    "khuyến mãi" => "Hiện tại chúng tôi có khuyến mãi giảm 20,000đ cho mọi hóa đơn và giảm 5% cho hóa đơn tháng 5. Hạn đến ngày 31/05/2025. 🎉",
    "giảm giá" => "Hiện tại chúng tôi có khuyến mãi giảm 20,000đ cho mọi hóa đơn và giảm 5% cho hóa đơn tháng 5. Hạn đến ngày 31/05/2025. 🎉",
    "ưu đãi" => "Hiện tại chúng tôi có khuyến mãi giảm 20,000đ cho mọi hóa đơn và giảm 5% cho hóa đơn tháng 5. Hạn đến ngày 31/05/2025. 🎉",
    
    "combo" => "Combo 1 (450,000đ): Phù hợp cho 4-6 người, gồm các món đặc sắc truyền thống. Combo 2 (250,000đ): Phù hợp cho 1-2 người, là set thưởng thức miền Tây. 🍽️",
    "set" => "Combo 1 (450,000đ): Phù hợp cho 4-6 người, gồm các món đặc sắc truyền thống. Combo 2 (250,000đ): Phù hợp cho 1-2 người, là set thưởng thức miền Tây. 🍽️",
    
    "đặt bàn" => "Quý khách có thể đặt bàn trước qua số điện thoại 0123456789 hoặc đặt trực tiếp trên website của nhà hàng. 📅",
    "đặt chỗ" => "Quý khách có thể đặt bàn trước qua số điện thoại 0123456789 hoặc đặt trực tiếp trên website của nhà hàng. 📅",
    "đặt tiệc" => "Nhà hàng nhận đặt tiệc sinh nhật, họp mặt, liên hoan công ty với ưu đãi đặc biệt. Vui lòng liên hệ trước 3-5 ngày. 🎂",
    
    "thanh toán" => "Nhà hàng chấp nhận thanh toán bằng tiền mặt, thẻ tín dụng/ghi nợ, và các ví điện tử như Momo, ZaloPay, VNPay. 💳",
    "phương thức thanh toán" => "Nhà hàng chấp nhận thanh toán bằng tiền mặt, thẻ tín dụng/ghi nợ, và các ví điện tử như Momo, ZaloPay, VNPay. 💳",
    
    "giao hàng" => "Nhà hàng có dịch vụ giao hàng trong phạm vi 5km với phí 15,000đ. Miễn phí giao hàng cho đơn từ 500,000đ. 🛵",
    "ship" => "Nhà hàng có dịch vụ giao hàng trong phạm vi 5km với phí 15,000đ. Miễn phí giao hàng cho đơn từ 500,000đ. 🛵",
    "thời gian giao hàng" => "Thời gian giao hàng thông thường từ 30-45 phút tùy khoảng cách. 🕙",
    
    "wifi" => "Nhà hàng có cung cấp Wifi miễn phí cho khách hàng. Bạn có thể hỏi nhân viên để biết mật khẩu. 📶",
    "bãi đỗ xe" => "Nhà hàng có bãi đỗ xe ô tô và xe máy miễn phí cho khách hàng. 🚗",
    "chỗ đậu xe" => "Nhà hàng có bãi đỗ xe ô tô và xe máy miễn phí cho khách hàng. 🚗",
    "xuất hóa đơn" => "Nhà hàng có thể xuất hóa đơn VAT theo yêu cầu. Vui lòng thông báo nhân viên trước khi thanh toán. 📝",
    "chỗ ngồi" => "Nhà hàng có sức chứa khoảng 150 khách, gồm các khu vực trong nhà máy lạnh, khu sân vườn và phòng VIP riêng tư. 🪑"
];

// Nhận input
$data = json_decode(file_get_contents('php://input'), true);
$message = isset($data['message']) ? $data['message'] : '';

// Mặc định response
$response = "Xin lỗi, tôi không hiểu câu hỏi của bạn. Bạn có thể hỏi về giờ mở cửa, địa chỉ, món ăn, khuyến mãi, đặt bàn hoặc dịch vụ giao hàng.";

if (!empty($message)) {
    // Chuyển message về chữ thường
    $message = mb_strtolower($message, 'UTF-8');
    
    // Tìm từ khóa khớp với câu hỏi
    foreach ($fixed_responses as $keyword => $answer) {
        if (strpos($message, $keyword) !== false) {
            $response = $answer;
            break;
        }
    }
    
    // Thử kết nối database nếu có thể
    try {
        if (file_exists('../config/config.php') && file_exists('../class/clsConnect.php')) {
            require_once '../config/config.php';
            require_once '../class/clsConnect.php';
            
            // Khởi tạo session nếu chưa có
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Tạo session_id nếu chưa có
            if (!isset($_SESSION['chat_session_id'])) {
                $_SESSION['chat_session_id'] = session_id();
            }
            
            $db = new connect_db();
            $conn = $db->getConnection();
            
            // Lưu tin nhắn của người dùng và bot
            if ($conn) {
                // Lưu tin nhắn của người dùng
                $stmt = $conn->prepare("INSERT INTO chat_history (session_id, message, sender, created_at) VALUES (?, ?, 'user', NOW())");
                $stmt->bind_param("ss", $_SESSION['chat_session_id'], $message);
                $stmt->execute();
                
                // Kiểm tra xem có câu trả lời từ database không
                $stmt = $conn->prepare("SELECT answer FROM chatbot_qa WHERE question_pattern LIKE ?");
                $searchPattern = '%' . $message . '%';
                $stmt->bind_param("s", $searchPattern);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    
                    // Nếu câu trả lời là SQL query, thực thi query
                    if (stripos($row['answer'], 'SELECT') === 0) {
                        $query = $row['answer'];
                        $queryResult = $conn->query($query);
                        
                        if ($queryResult && $queryResult->num_rows > 0) {
                            $response = "<strong>Kết quả tìm kiếm:</strong><br>";
                            while ($item = $queryResult->fetch_assoc()) {
                                // Format kết quả tùy thuộc vào loại dữ liệu
                                if (isset($item['name']) && isset($item['price'])) {
                                    // Định dạng cho món ăn
                                    $response .= "- " . $item['name'] . ": " . number_format($item['price']) . "đ";
                                    if (isset($item['description'])) {
                                        $response .= " (" . $item['description'] . ")";
                                    }
                                    $response .= "<br>";
                                } else if (isset($item['SoBan']) && isset($item['TrangThai'])) {
                                    // Định dạng cho bàn
                                    $response .= "- Bàn " . $item['SoBan'] . " (" . $item['soluongKH'] . " người): " . $item['TrangThai'] . "<br>";
                                } else if (isset($item['TenKM']) && isset($item['NoiDung'])) {
                                    // Định dạng cho khuyến mãi
                                    $response .= "- " . $item['TenKM'] . ": " . $item['NoiDung'] . " (từ " . $item['NgayBD'] . " đến " . $item['NgayKT'] . ")<br>";
                                } else {
                                    // Định dạng chung
                                    foreach ($item as $key => $value) {
                                        $response .= "- " . $key . ": " . $value . "<br>";
                                    }
                                }
                            }
                        }
                    } else {
                        // Nếu không phải SQL query, sử dụng câu trả lời trực tiếp
                        $response = $row['answer'];
                    }
                }
                
                // Lưu tin nhắn của bot
                $stmt = $conn->prepare("INSERT INTO chat_history (session_id, message, sender, created_at) VALUES (?, ?, 'bot', NOW())");
                $stmt->bind_param("ss", $_SESSION['chat_session_id'], $response);
                $stmt->execute();
            }
        }
    } catch (Exception $e) {
        // Bỏ qua lỗi khi tương tác với database
        error_log("Database error: " . $e->getMessage());
    }
}

// Trả về response
echo json_encode([
    'status' => 'success',
    'message' => $response
]); 