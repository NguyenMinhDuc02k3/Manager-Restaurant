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

// Danh sách câu trả lời cố định
$fixed_responses = [
    // Giờ mở cửa & thông tin cơ bản
    "nhà hàng mở cửa mấy giờ" => "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "giờ mở cửa" => "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "thời gian mở cửa" => "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "mở cửa" => "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "địa chỉ" => "Nhà hàng nằm tại 123 ABC Street, Quận 1, TP.HCM. Rất hân hạnh được đón tiếp quý khách! 📍",
    "ở đâu" => "Nhà hàng nằm tại 123 ABC Street, Quận 1, TP.HCM. Rất hân hạnh được đón tiếp quý khách! 📍",
    "vị trí" => "Nhà hàng nằm tại 123 ABC Street, Quận 1, TP.HCM, gần trung tâm thương mại ABC. Có chỗ đỗ xe rộng rãi và dễ tìm. 📍",
    "liên hệ" => "Quý khách có thể liên hệ với chúng tôi qua số điện thoại 0123456789 hoặc email info@restaurant.com. 📞",
    "số điện thoại" => "Quý khách có thể liên hệ với chúng tôi qua số điện thoại 0123456789. 📞",
    "email" => "Email liên hệ của nhà hàng là info@restaurant.com. 📧",
    "dịch vụ" => "Chúng tôi có dịch vụ đặt tiệc, tổ chức sinh nhật, khu vực riêng cho nhóm đông người và phục vụ đặt món mang về. 💝",
    
    // Thông tin món ăn & thực đơn
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
    "thực đơn" => "Thực đơn của nhà hàng rất đa dạng với hơn 50 món ăn từ khai vị, món chính đến tráng miệng. Bạn có thể xem thực đơn đầy đủ tại website: restaurant.com/menu 📋",
    "menu" => "Thực đơn của nhà hàng rất đa dạng với hơn 50 món ăn từ khai vị, món chính đến tráng miệng. Bạn có thể xem thực đơn đầy đủ tại website: restaurant.com/menu 📋",
    "món chay" => "Nhà hàng có thực đơn chay riêng với các món như: Cơm chiên nấm, Đậu hủ sốt cà chua, Canh rau củ... Các món chay đều được chế biến riêng biệt. 🥬",
    "món hải sản" => "Nhà hàng có nhiều món hải sản tươi ngon như: Cua rang me, Tôm hùm nướng phô mai, Mực xào sả ớt, Cá chẽm hấp Hồng Kông. 🦞",
    "món ăn cho trẻ em" => "Nhà hàng có thực đơn dành riêng cho trẻ em với các món như: Mì Ý sốt bò, Gà rán, Hamburger mini và đồ ngọt phù hợp với khẩu vị của bé. 👶",
    
    // Khuyến mãi & ưu đãi
    "khuyến mãi" => "Hiện tại chúng tôi có khuyến mãi giảm 20,000đ cho mọi hóa đơn và giảm 5% cho hóa đơn tháng 5. Hạn đến ngày 31/05/2025. 🎉",
    "giảm giá" => "Hiện tại chúng tôi có khuyến mãi giảm 20,000đ cho mọi hóa đơn và giảm 5% cho hóa đơn tháng 5. Hạn đến ngày 31/05/2025. 🎉",
    "ưu đãi" => "Hiện tại chúng tôi có khuyến mãi giảm 20,000đ cho mọi hóa đơn và giảm 5% cho hóa đơn tháng 5. Hạn đến ngày 31/05/2025. 🎉",
    "mã giảm giá" => "Nhà hàng có các mã giảm giá định kỳ được gửi qua email khi bạn đăng ký thành viên. Hiện tại bạn có thể sử dụng mã WELCOME để được giảm 10% cho lần đầu đặt bàn. 🎁",
    "ưu đãi sinh nhật" => "Nhà hàng tặng bánh sinh nhật miễn phí và giảm 15% tổng hóa đơn cho khách hàng có sinh nhật (áp dụng trong vòng 3 ngày trước/sau ngày sinh nhật, cần xuất trình CMND/CCCD). 🎂",
    "thẻ thành viên" => "Nhà hàng có chương trình thẻ thành viên với 3 hạng: Bạc, Vàng và Kim cương. Thành viên được tích điểm và hưởng ưu đãi từ 5-15% tùy hạng thẻ. Đăng ký miễn phí tại quầy lễ tân. 💳",
    
    // Combo & set
    "combo" => "Combo 1 (450,000đ): Phù hợp cho 4-6 người, gồm các món đặc sắc truyền thống. Combo 2 (250,000đ): Phù hợp cho 1-2 người, là set thưởng thức miền Tây. 🍽️",
    "set" => "Combo 1 (450,000đ): Phù hợp cho 4-6 người, gồm các món đặc sắc truyền thống. Combo 2 (250,000đ): Phù hợp cho 1-2 người, là set thưởng thức miền Tây. 🍽️",
    "set menu" => "Nhà hàng có 4 lựa chọn set menu từ 200,000đ - 500,000đ/người, bao gồm khai vị, món chính, tráng miệng và đồ uống. Lý tưởng cho các buổi hẹn hoặc tiếp khách. 🍴",
    "buffet" => "Nhà hàng phục vụ buffet vào buổi trưa các ngày trong tuần, giá 299,000đ/người lớn và 150,000đ/trẻ em. Gồm hơn 50 món ăn và free nước ngọt. 🍱",
    
    // Đặt bàn & tiệc
    "đặt bàn" => "Quý khách có thể đặt bàn trước qua số điện thoại 0123456789 hoặc đặt trực tiếp trên website của nhà hàng. 📅",
    "đặt chỗ" => "Quý khách có thể đặt bàn trước qua số điện thoại 0123456789 hoặc đặt trực tiếp trên website của nhà hàng. 📅",
    "đặt tiệc" => "Nhà hàng nhận đặt tiệc sinh nhật, họp mặt, liên hoan công ty với ưu đãi đặc biệt. Vui lòng liên hệ trước 3-5 ngày. 🎂",
    "hủy đặt bàn" => "Quý khách có thể hủy đặt bàn miễn phí trước 4 giờ so với giờ đã đặt. Vui lòng gọi số 0123456789 để hủy và nhận mã hủy đặt bàn. ❌",
    "cọc đặt bàn" => "Đối với nhóm trên 10 người hoặc vào dịp lễ/Tết, nhà hàng cần đặt cọc 20% tổng hóa đơn dự kiến. Cọc sẽ được trừ vào hóa đơn khi dùng bữa. 💰",
    "đặt bàn cho nhóm đông" => "Nhà hàng có không gian riêng cho nhóm 15-30 người. Quý khách nên đặt trước ít nhất 2 ngày và có thể yêu cầu trang trí theo chủ đề. 👨‍👩‍👧‍👦",
    "tổ chức sự kiện" => "Nhà hàng nhận tổ chức sự kiện công ty, họp mặt, liên hoan với sức chứa lên đến 150 khách. Có dịch vụ âm thanh, ánh sáng và MC chuyên nghiệp. 🎭",
    "đặt tiệc sinh nhật" => "Nhà hàng có gói tiệc sinh nhật trọn gói từ 2,000,000đ cho 10 người, bao gồm trang trí, bánh sinh nhật và đồ uống. Liên hệ 0123456789 để được tư vấn. 🎉",
    "đặt tiệc cưới" => "Nhà hàng có dịch vụ tổ chức tiệc cưới mini với không gian lãng mạn, phù hợp cho 30-50 khách. Giá từ 350,000đ/phần, bao gồm trang trí và champagne. 💒",
    
    // Thanh toán & giao hàng
    "thanh toán" => "Nhà hàng chấp nhận thanh toán bằng tiền mặt, thẻ tín dụng/ghi nợ, và các ví điện tử như Momo, ZaloPay, VNPay. 💳",
    "phương thức thanh toán" => "Nhà hàng chấp nhận thanh toán bằng tiền mặt, thẻ tín dụng/ghi nợ, và các ví điện tử như Momo, ZaloPay, VNPay. 💳",
    "trả góp" => "Nhà hàng hỗ trợ thanh toán trả góp 0% lãi suất cho hóa đơn từ 3,000,000đ với thẻ tín dụng của các ngân hàng: Vietcombank, BIDV, Sacombank. 💸",
    "giao hàng" => "Nhà hàng có dịch vụ giao hàng trong phạm vi 5km với phí 15,000đ. Miễn phí giao hàng cho đơn từ 500,000đ. 🛵",
    "ship" => "Nhà hàng có dịch vụ giao hàng trong phạm vi 5km với phí 15,000đ. Miễn phí giao hàng cho đơn từ 500,000đ. 🛵",
    "thời gian giao hàng" => "Thời gian giao hàng thông thường từ 30-45 phút tùy khoảng cách. 🕙",
    "đặt món online" => "Quý khách có thể đặt món online qua website restaurant.com hoặc qua các ứng dụng giao đồ ăn như GrabFood, ShopeeFood, Baemin. 📱",
    "phí giao hàng" => "Phí giao hàng là 15,000đ cho đơn hàng dưới 500,000đ trong bán kính 5km. Miễn phí giao hàng cho đơn từ 500,000đ hoặc khách hàng thành viên hạng Vàng trở lên. 🚚",
    "đơn tối thiểu" => "Đơn hàng giao tối thiểu là 100,000đ. Nhà hàng khuyến khích đặt trước 1 giờ trong khung giờ cao điểm (11h-13h và 18h-20h). 📋",
    
    // Tiện ích & dịch vụ
    "wifi" => "Nhà hàng có cung cấp Wifi miễn phí cho khách hàng. Bạn có thể hỏi nhân viên để biết mật khẩu. 📶",
    "bãi đỗ xe" => "Nhà hàng có bãi đỗ xe ô tô và xe máy miễn phí cho khách hàng. 🚗",
    "chỗ đậu xe" => "Nhà hàng có bãi đỗ xe ô tô và xe máy miễn phí cho khách hàng. 🚗",
    "xuất hóa đơn" => "Nhà hàng có thể xuất hóa đơn VAT theo yêu cầu. Vui lòng thông báo nhân viên trước khi thanh toán. 📝",
    "chỗ ngồi" => "Nhà hàng có sức chứa khoảng 150 khách, gồm các khu vực trong nhà máy lạnh, khu sân vườn và phòng VIP riêng tư. 🪑",
    "phòng riêng" => "Nhà hàng có 5 phòng VIP với sức chứa từ 6-20 khách, phù hợp cho các buổi họp kín hoặc tiệc gia đình. Phí sử dụng phòng: 500,000đ (được trừ vào hóa đơn). 🚪",
    "khu vực hút thuốc" => "Nhà hàng có khu vực hút thuốc riêng ở sân vườn, cách biệt với khu vực ăn uống chính. 🚬",
    "chỗ chơi cho trẻ em" => "Nhà hàng có khu vui chơi dành cho trẻ em với các trò chơi an toàn và nhân viên trông trẻ vào cuối tuần. 👶",
    "người khuyết tật" => "Nhà hàng có lối đi và nhà vệ sinh dành riêng cho người khuyết tật. Nhân viên luôn sẵn sàng hỗ trợ khi cần. ♿",
    "thú cưng" => "Nhà hàng cho phép mang thú cưng vào khu vực sân vườn. Vui lòng giữ thú cưng có dây dắt và mang theo đồ dùng vệ sinh. 🐕",
    
    // Các câu hỏi khác
    "bữa sáng" => "Nhà hàng phục vụ bữa sáng từ 8:00 - 10:30 hàng ngày với các món Âu - Á đa dạng. Giá buffet sáng: 150,000đ/người lớn, 80,000đ/trẻ em. ☕",
    "phục vụ tại bàn" => "Vâng, nhà hàng có dịch vụ phục vụ tại bàn với đội ngũ nhân viên chuyên nghiệp. Thời gian phục vụ món thông thường là 10-15 phút sau khi đặt. 👨‍🍳",
    "đồ uống tự mang" => "Nhà hàng cho phép khách mang rượu vang với phí mở nút là 150,000đ/chai. Các loại đồ uống khác vui lòng không mang vào nhà hàng. 🍷",
    "tiếng ồn" => "Nhà hàng giữ không gian yên tĩnh vừa phải. Vào cuối tuần có nhạc sống nhẹ nhàng từ 19:00 - 21:00. 🎵",
    "phản hồi" => "Rất cảm ơn quý khách quan tâm. Quý khách có thể gửi phản hồi qua email feedback@restaurant.com hoặc điền vào phiếu đánh giá tại nhà hàng. 📋",
    "đánh giá" => "Rất cảm ơn quý khách quan tâm. Quý khách có thể gửi đánh giá qua email feedback@restaurant.com hoặc điền vào phiếu đánh giá tại nhà hàng. 🌟",
    "chính sách hủy" => "Quý khách có thể hủy đặt bàn miễn phí trước 4 giờ. Hủy trễ hơn hoặc không đến có thể bị tính phí 20% giá trị đơn đặt bàn đã cọc. ⏱️",
    "trẻ em" => "Trẻ em dưới 5 tuổi được miễn phí buffet. Trẻ từ 5-10 tuổi được tính 50% giá người lớn. Nhà hàng có ghế dành cho trẻ em và menu đặc biệt cho bé. 👶",
    "làm việc" => "Nhà hàng có khu vực yên tĩnh với sạc điện thoại và wifi tốc độ cao, phù hợp cho làm việc. Có ổ cắm điện tại hầu hết các bàn. 💻",
    "suất ăn công nghiệp" => "Nhà hàng có dịch vụ cung cấp suất ăn công nghiệp cho công ty với giá từ 35,000đ/suất. Liên hệ 0123456789 để được tư vấn gói phù hợp. 🍱",
    "dị ứng thực phẩm" => "Nhà hàng có thể điều chỉnh món ăn theo yêu cầu đối với khách hàng bị dị ứng. Vui lòng thông báo cho nhân viên khi đặt món. ⚕️",
    "chính sách bảo mật" => "Nhà hàng cam kết bảo mật thông tin khách hàng, chỉ sử dụng để phục vụ việc đặt bàn và thông báo ưu đãi. Chi tiết tại restaurant.com/privacy. 🔒"
    ,// Các câu hỏi về giờ mở cửa & thông tin cơ bản (tiếp tục)
    "ngày lễ mở cửa" => "Vào các ngày lễ, nhà hàng vẫn mở cửa từ 8:00 - 22:00. Tuy nhiên, khuyến nghị quý khách đặt bàn trước để đảm bảo chỗ ngồi. 🎄",
    "giờ cao điểm" => "Giờ cao điểm của nhà hàng thường là 11:30 - 13:30 và 18:00 - 20:00. Quý khách nên đặt bàn trước để tránh chờ đợi. ⏰",
    "thời gian phục vụ" => "Thời gian phục vụ món ăn trung bình từ 10-15 phút, tùy thuộc vào món và thời điểm đông khách. 👨‍🍳",
    "website" => "Quý khách có thể tìm hiểu thêm thông tin và đặt bàn qua website chính thức của nhà hàng: restaurant.com. 🌐",
    "mạng xã hội" => "Theo dõi chúng tôi trên Facebook và Instagram (@RestaurantABC) để cập nhật thực đơn mới và các chương trình khuyến mãi đặc biệt! 📸",

    // Các câu hỏi về món ăn & thực đơn (tiếp tục)
    "món ăn theo mùa" => "Nhà hàng có các món theo mùa như Lẩu nấm mùa đông (250,000đ) và Gỏi hoa chuối mùa hè (120,000đ). Vui lòng kiểm tra thực đơn theo mùa tại restaurant.com/menu. 🌸",
    "món ăn địa phương" => "Chúng tôi tự hào phục vụ các món đặc sản địa phương như Bánh xèo miền Tây (80,000đ) và Gỏi sầu riêng (150,000đ). 🍲",
    "món ăn cho người ăn kiêng" => "Nhà hàng có các món ít calo như Salad cá hồi (120,000đ) và Súp bí đỏ (70,000đ), phù hợp cho khách ăn kiêng. 🥗",
    "thực đơn trẻ em" => "Thực đơn trẻ em gồm các món như Mì Ý sốt bò (60,000đ), Gà rán giòn (50,000đ) và Sinh tố trái cây (30,000đ). 👶",
    "món ăn không gluten" => "Nhà hàng cung cấp các món không gluten như Cơm gạo lứt với gà nướng (90,000đ) và Salad rau củ (80,000đ). Vui lòng thông báo khi đặt món. 🌾",
    "món ăn cay" => "Các món cay nổi bật gồm Lẩu Thái (250,000đ), Gà xào sả ớt (95,000đ) và Mì xào cay Tứ Xuyên (100,000đ). 🌶️",

    // Các câu hỏi về khuyến mãi & ưu đãi (tiếp tục)
    "khuyến mãi cuối tuần" => "Cuối tuần, nhà hàng có chương trình tặng kèm món tráng miệng miễn phí cho hóa đơn từ 1,000,000đ. Áp dụng thứ Bảy và Chủ Nhật. 🥮",
    "ưu đãi nhóm" => "Nhóm từ 10 người trở lên được giảm 10% tổng hóa đơn khi đặt bàn trước. Liên hệ 0123456789 để được hỗ trợ. 👨‍👩‍👧‍👦",
    "chương trình khách hàng thân thiết" => "Khách hàng thân thiết tích lũy điểm mỗi lần dùng bữa (1,000đ = 1 điểm). Đổi 100 điểm để nhận voucher 100,000đ. 💎",
    "ưu đãi đặt online" => "Đặt món online qua website restaurant.com được giảm 10% cho đơn hàng đầu tiên và miễn phí giao hàng cho đơn từ 300,000đ. 📱",

    // Các câu hỏi về combo & set (tiếp tục)
    "combo gia đình" => "Combo Gia Đình (600,000đ): Phù hợp cho 6-8 người, gồm Vịt quay Bắc Kinh, Cơm chiên hải sản, Gỏi xoài tôm khô và nước ép trái cây. 🍴",
    "set ăn trưa" => "Set ăn trưa (120,000đ/người): Bao gồm 1 món chính (Cơm chiên hoặc Bò lúc lắc), 1 món khai vị và 1 ly trà đá. Lý tưởng cho dân văn phòng. 🍱",
    "buffet tối" => "Buffet tối cuối tuần (từ 18:00 - 21:00) giá 350,000đ/người lớn, 180,000đ/trẻ em, với hơn 60 món ăn và quầy đồ uống tự chọn. 🍴",

    // Các câu hỏi về đặt bàn & tiệc (tiếp tục)
    "đặt bàn online" => "Quý khách có thể đặt bàn online qua website restaurant.com hoặc ứng dụng GrabFood, ShopeeFood. Đặt trước để đảm bảo chỗ ngồi! 📱",
    "sức chứa tối đa" => "Nhà hàng có sức chứa tối đa 150 khách, với các khu vực phòng VIP, sân vườn và khu vực chung. Phù hợp cho mọi loại tiệc. 🏛️",
    "trang trí tiệc" => "Nhà hàng cung cấp dịch vụ trang trí tiệc theo chủ đề (sinh nhật, kỷ niệm, cưới) với chi phí từ 500,000đ. Vui lòng đặt trước 3 ngày. 🎈",
    "đặt tiệc công ty" => "Nhà hàng nhận tổ chức tiệc công ty với các gói từ 5,000,000đ cho 20 người, bao gồm thực đơn tùy chỉnh và dịch vụ MC. 🎤",

    // Các câu hỏi về thanh toán & giao hàng (tiếp tục)
    "thanh toán qua ứng dụng" => "Nhà hàng hỗ trợ thanh toán qua các ứng dụng như Momo, ZaloPay, VNPay với ưu đãi giảm 5% cho đơn hàng đầu tiên. 📲",
    "hóa đơn điện tử" => "Nhà hàng cung cấp hóa đơn điện tử qua email. Vui lòng cung cấp thông tin hóa đơn khi thanh toán. 📧",
    "giao hàng ngoài giờ" => "Dịch vụ giao hàng hoạt động từ 8:00 - 21:30. Đơn hàng sau 21:00 có thể đặt qua các ứng dụng giao đồ ăn như GrabFood. 🛵",
    "khu vực giao hàng" => "Nhà hàng giao hàng trong bán kính 5km từ địa chỉ 123 ABC Street, Quận 1, TP.HCM. Liên hệ để kiểm tra khu vực ngoài bán kính. 📍",

    // Các câu hỏi về tiện ích & dịch vụ (tiếp tục)
    "không gian ngoài trời" => "Nhà hàng có khu vực sân vườn thoáng mát, phù hợp cho các buổi hẹn hò hoặc tiệc ngoài trời. Có mái che khi trời mưa. 🌳",
    "nhạc sống" => "Nhà hàng có nhạc sống vào thứ Sáu và thứ Bảy từ 19:00 - 21:00 với các bản nhạc acoustic nhẹ nhàng. 🎸",
    "dịch vụ chụp ảnh" => "Nhà hàng cung cấp dịch vụ chụp ảnh chuyên nghiệp cho các sự kiện với giá từ 1,000,000đ/gói. Vui lòng đặt trước 3 ngày. 📸",
    "hỗ trợ người cao tuổi" => "Nhà hàng có ghế ưu tiên và lối đi thuận tiện cho người cao tuổi. Nhân viên luôn sẵn sàng hỗ trợ khi cần. 👴",
    "khăn giấy" => "Nhà hàng cung cấp khăn giấy miễn phí tại bàn. Khăn ướt có tính phí 5,000đ/chiếc nếu khách yêu cầu. 🧻",

    // Các câu hỏi khác (tiếp tục)
    "thời gian chờ" => "Thời gian chờ bàn vào giờ cao điểm khoảng 10-15 phút nếu không đặt trước. Đặt bàn để được phục vụ ngay! ⏳",
    "chính sách hoàn tiền" => "Nhà hàng hoàn tiền cọc đặt bàn trong vòng 48 giờ nếu hủy đúng quy định. Vui lòng liên hệ 0123456789 để được hỗ trợ. 💸",
    "thực đơn tiếng Anh" => "Nhà hàng có thực đơn tiếng Anh dành cho khách nước ngoài. Vui lòng yêu cầu nhân viên cung cấp khi đến. 📖",
    "khách nước ngoài" => "Nhà hàng có nhân viên giao tiếp bằng tiếng Anh và thực đơn tiếng Anh để hỗ trợ khách nước ngoài. 🌍",
    "an toàn thực phẩm" => "Tất cả nguyên liệu tại nhà hàng đều được kiểm tra kỹ lưỡng và đạt chuẩn vệ sinh an toàn thực phẩm. 🍽️",
    "chương trình từ thiện" => "Nhà hàng tổ chức chương trình từ thiện hàng tháng, hỗ trợ bữa ăn miễn phí cho trẻ em khó khăn. Liên hệ để biết thêm chi tiết. ❤️",
    // Các câu hỏi về bữa ăn theo thời điểm
    "trưa ăn gì" => "Buổi trưa, nhà hàng gợi ý các món như Cơm chiên hải sản (100,000đ), Bò lúc lắc (95,000đ) hoặc Set ăn trưa (120,000đ/người) gồm món chính, khai vị và trà đá. Buffet trưa (299,000đ/người) cũng là lựa chọn tuyệt vời! 🍱",
    "ăn trưa" => "Nhà hàng phục vụ bữa trưa từ 11:00 - 14:00 với các món đặc sắc như Vịt quay Bắc Kinh (200,000đ), Mì xào hải sản (90,000đ) hoặc Set ăn trưa (120,000đ/người). Buffet trưa giá 299,000đ/người với hơn 50 món. 🥗",
    "tối ăn gì" => "Buổi tối, bạn có thể thưởng thức Cá Lăng Đặc Sắc (350,000đ), Lẩu Thái (250,000đ) hoặc Combo Gia Đình (600,000đ cho 6-8 người). Buffet tối cuối tuần (350,000đ/người) cũng rất được ưa chuộng! 🌙",
    "ăn tối" => "Bữa tối tại nhà hàng từ 17:00 - 21:30 với các món nổi bật như Tôm hùm nướng phô mai (450,000đ), Bò lúc lắc (95,000đ) hoặc Buffet tối cuối tuần (350,000đ/người) với hơn 60 món và đồ uống tự chọn. 🍴",
    "sáng ăn gì" => "Buổi sáng, nhà hàng phục vụ từ 8:00 - 10:30 với các món như Phở bò (80,000đ), Bánh mì trứng ốp la (50,000đ) hoặc Buffet sáng (150,000đ/người lớn, 80,000đ/trẻ em) với món Âu - Á đa dạng. ☕",
    "ăn sáng" => "Nhà hàng phục vụ bữa sáng từ 8:00 - 10:30 với các lựa chọn như Bún bò Huế (85,000đ), Croissant kẹp trứng và thịt xông khói (60,000đ) hoặc Buffet sáng (150,000đ/người lớn) với hơn 30 món. 🥐",
    "bữa sáng có gì" => "Bữa sáng tại nhà hàng từ 8:00 - 10:30 có các món như Hủ tiếu Nam Vang (80,000đ), Bánh cuốn nhân tôm (70,000đ) và Buffet sáng (150,000đ/người lớn) với trà, cà phê miễn phí. ☕",
    "bữa trưa có gì" => "Bữa trưa từ 11:00 - 14:00 có các món như Cơm tấm sườn nướng (90,000đ), Gỏi xoài tôm khô (90,000đ) hoặc Set ăn trưa (120,000đ/người). Buffet trưa (299,000đ/người) với hơn 50 món cũng rất đáng thử! 🍚",
    "bữa tối có gì" => "Bữa tối từ 17:00 - 21:30 có các món đặc biệt như Cua rang me (450,000đ), Lẩu nấm mùa đông (250,000đ) hoặc Buffet tối cuối tuần (350,000đ/người) với quầy hải sản tươi sống. 🦞",
];

// Thêm các từ khóa đặc biệt
$special_keywords = [
    'valentine' => "Nhà hàng có chương trình đặc biệt cho ngày Valentine với set menu lãng mạn cho 2 người giá 599,000đ, bao gồm khai vị, món chính, tráng miệng, rượu vang và hoa hồng tặng kèm. Đặt bàn sớm để được vị trí đẹp nhất! 💕",
    'tết' => "Nhà hàng phục vụ các set menu đặc biệt ngày Tết với giá từ 699,000đ/người, bao gồm các món truyền thống như Bánh chưng, Giò lụa, Thịt đông và nhiều món ngon khác. Nhận đặt tiệc Tất niên và Tân niên với ưu đãi hấp dẫn! 🧧",
    'noel' => "Nhà hàng tổ chức tiệc Giáng sinh với set menu 499,000đ/người, bao gồm món Âu đặc trưng như Gà tây nướng, Bánh khúc cây và Rượu vang nóng. Đêm 24/12 có chương trình văn nghệ và ông già Noel tặng quà! 🎄",
    'trung thu' => "Nhà hàng có set Trung thu cho gia đình với giá 699,000đ/4 người, bao gồm các món đặc trưng và bánh Trung thu handmade. Khu vực sân vườn được trang trí đèn lồng đặc sắc. 🌕",
    '8/3' => "Nhân ngày 8/3, nhà hàng tặng 1 ly cocktail đặc biệt và 1 bông hồng cho khách nữ. Đặt bàn trước được giảm 15% tổng hóa đơn. 🌹",
    '20/10' => "Nhân ngày 20/10, nhà hàng tặng 1 phần tráng miệng đặc biệt và 1 bông hồng cho khách nữ. Đặt bàn trước được giảm 15% tổng hóa đơn. 🌹",
    '20/11' => "Nhân ngày Nhà giáo Việt Nam 20/11, nhà hàng giảm 20% tổng hóa đơn cho thầy cô giáo (xuất trình thẻ ngành). Đặt tiệc nhóm từ 10 người trở lên được tặng 1 chai rượu vang. 📚",
    'lễ tình nhân' => "Nhà hàng có chương trình đặc biệt cho ngày Valentine với set menu lãng mạn cho 2 người giá 599,000đ, bao gồm khai vị, món chính, tráng miệng, rượu vang và hoa hồng tặng kèm. Đặt bàn sớm để được vị trí đẹp nhất! 💕"
];

try {
    // Nhận input
    $data = json_decode(file_get_contents('php://input'), true);
    $message = isset($data['message']) ? $data['message'] : '';
    
    // Mặc định response
    $response = "Xin lỗi, tôi không hiểu câu hỏi của bạn. Bạn có thể hỏi về giờ mở cửa, địa chỉ, món ăn, khuyến mãi, đặt bàn hoặc dịch vụ giao hàng.";
    
    if (!empty($message)) {
        // Chuyển message về chữ thường
        $message = mb_strtolower($message, 'UTF-8');
        
        // Tìm kiếm từ khóa đặc biệt trước
        $found = false;
        foreach ($special_keywords as $keyword => $answer) {
            if (strpos($message, $keyword) !== false) {
                $response = $answer;
                $found = true;
                break;
            }
        }
        
        // Nếu không tìm thấy từ khóa đặc biệt, tìm trong danh sách câu trả lời cố định
        if (!$found) {
            // Tìm kiếm chính xác trước
            foreach ($fixed_responses as $keyword => $answer) {
                if (strpos($message, $keyword) !== false) {
                    $response = $answer;
                    $found = true;
                    break;
                }
            }
            
            // Nếu không tìm thấy, thử tìm từng từ một
            if (!$found) {
                // Tách câu hỏi thành các từ riêng lẻ
                $words = preg_split('/\s+/', $message);
                
                foreach ($words as $word) {
                    // Bỏ qua các từ quá ngắn hoặc từ không quan trọng
                    if (mb_strlen($word) < 3) {
                        continue;
                    }
                    
                    foreach ($fixed_responses as $keyword => $answer) {
                        // Tách keyword thành các từ
                        $keyword_words = preg_split('/\s+/', $keyword);
                        
                        // Nếu từ đơn lẻ xuất hiện trong một từ của keyword
                        foreach ($keyword_words as $kw) {
                            if (strpos($kw, $word) !== false) {
                                $response = $answer;
                                $found = true;
                                break 3; // Thoát cả 3 vòng lặp
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Trả về response
    echo json_encode([
        'status' => 'success',
        'message' => $response
    ]);
    
} catch (Throwable $e) {
    // Bắt tất cả các lỗi và exception
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?> 