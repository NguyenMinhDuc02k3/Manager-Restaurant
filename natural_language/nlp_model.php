<?php
// Kiểm tra và tạo autoload nếu chưa có
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("Vui lòng chạy 'composer install' trước khi sử dụng script này.");
}

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\ModelManager;
use Phpml\Dataset\ArrayDataset;
use Phpml\Classification\KNearestNeighbors;

// Dữ liệu training mở rộng
$samples = [];
$labels = [];

// Giờ mở cửa
$opening_hours_samples = [
    'nhà hàng mở cửa mấy giờ',
    'giờ mở cửa',
    'thời gian mở cửa',
    'mở cửa lúc mấy giờ',
    'mấy giờ đóng cửa',
    'nhà hàng đóng cửa lúc mấy giờ',
    'nhà hàng các bạn mở cửa đến mấy giờ',
    'đóng cửa lúc mấy giờ vậy',
    'thời gian phục vụ',
    'giờ làm việc',
    'mở đến khuya không',
    'mở vào cuối tuần không',
    'có mở cửa vào chủ nhật không',
    'ngày lễ mở cửa',
    'giờ cao điểm',
    'thời gian phục vụ',
    'nhà hàng mở cửa',
    'nhà hàng mở cửa lúc mấy giờ',
    'nhà hàng đóng cửa lúc mấy giờ',
    'giờ cao điểm là mấy giờ',
    'nhà hàng mở cửa vào ngày lễ không',
    'nhà hàng có nghỉ trưa không',
    'nhà hàng mở cửa đến mấy giờ tối',
    'nhà hàng phục vụ đến mấy giờ',
    'nhà hàng phục vụ từ mấy giờ',
    'giờ phục vụ bữa sáng',
    'giờ phục vụ bữa trưa',
    'giờ phục vụ bữa tối',
    'nhà hàng có phục vụ 24/7 không',
    'nhà hàng có mở cửa vào buổi sáng sớm không',
    'nhà hàng có mở cửa vào đêm khuya không',
];

foreach ($opening_hours_samples as $sample) {
    $samples[] = $sample;
    $labels[] = 'opening_hours';
}

// Địa chỉ
$location_samples = [
    'địa chỉ nhà hàng ở đâu',
    'nhà hàng ở đâu',
    'vị trí nhà hàng',
    'chỗ nhà hàng',
    'đường đến nhà hàng',
    'nhà hàng nằm ở đâu',
    'địa điểm',
    'chỉ đường đến nhà hàng',
    'nhà hàng ở con đường nào',
    'địa chỉ cụ thể là gì',
    'làm sao để đến nhà hàng',
    'nhà hàng gần chỗ nào',
    'liên hệ',
    'số điện thoại',
    'email',
    'website',
    'mạng xã hội',
    'địa chỉ của nhà hàng ở đâu vậy',
    'nhà hàng nằm ở con đường nào',
    'nhà hàng ở khu vực nào',
    'nhà hàng gần ga tàu không',
    'nhà hàng gần trung tâm không',
    'nhà hàng cách trung tâm bao xa',
    'làm thế nào để đi đến nhà hàng',
    'tôi có thể đi bằng phương tiện gì đến nhà hàng',
    'nhà hàng có chi nhánh ở đâu không',
    'các chi nhánh của nhà hàng',
    'nhà hàng ở thành phố nào',
    'địa chỉ email của nhà hàng',
    'số điện thoại liên hệ của nhà hàng',
    'fanpage của nhà hàng',
    'trang web của nhà hàng',
    'nhà hàng có mấy chi nhánh',
];

foreach ($location_samples as $sample) {
    $samples[] = $sample;
    $labels[] = 'location';
}

// Món ăn
$menu_samples = [
    'nhà hàng có món gì ngon',
    'món đặc sản',
    'món nổi tiếng',
    'món ăn phổ biến',
    'món ngon nhất',
    'thực đơn có gì',
    'menu nhà hàng',
    'món signature',
    'món đặc trưng',
    'có món chay không',
    'món chay',
    'nhà hàng có món chay không',
    'nhà hàng có món chay',
    'món dành cho trẻ em',
    'món bán chạy nhất',
    'món mới',
    'món theo mùa',
    'đồ uống',
    'tráng miệng',
    'khai vị',
    'các món hải sản',
    'món ăn',
    'món ngon',
    'có món nào ngon',
    'món đặc biệt',
    'món phổ biến',
    'món chính',
    'khai vị',
    'tráng miệng',
    'đồ uống',
    'nước uống',
    'thực đơn',
    'menu',
    'món chay',
    'món hải sản',
    'món ăn cho trẻ em',
    'món ăn theo mùa',
    'món ăn địa phương',
    'món ăn cho người ăn kiêng',
    'thực đơn trẻ em',
    'món ăn không gluten',
    'món ăn cay',
    'trưa ăn gì',
    'ăn trưa',
    'tối ăn gì',
    'ăn tối',
    'sáng ăn gì',
    'ăn sáng',
    'bữa sáng có gì',
    'bữa trưa có gì',
    'bữa tối có gì',
    'bữa sáng',
    'giá món ăn',
    'giá món ăn của nhà hàng',
    'món ăn giá bao nhiêu',
    'giá thực đơn',
    'giá các món ăn',
    'bảng giá món ăn',
    'chi phí bữa ăn',
    'giá cả món ăn',
    'menu có giá không',
    'thực đơn và giá',
    'giá đồ uống',
    'giá khai vị',
    'giá món tráng miệng',
    'so sánh giá món ăn',
    'so sánh món ăn',
    'món nào rẻ nhất',
    'món đặc sản giá bao nhiêu',
    'món signature giá thế nào',
    'chi phí trung bình cho một người',
    'menu nhà hàng có những món gì',
    'nhà hàng có bữa trưa không',
    'nhà hàng có món đặc sản gì',
    'nhà hàng có món signature nào',
    'nhà hàng có món nào ngon',
    'món ăn nào bán chạy nhất',
    'nhà hàng có món nào hợp cho trẻ em',
    'nhà hàng có món nào hợp cho người ăn kiêng',
    'nhà hàng có món nào không chứa gluten',
    'nhà hàng có món nào không cay',
    'nhà hàng có món nào cay',
    'nhà hàng có món nào hợp cho người ăn chay',
    'nhà hàng có món nào hợp cho người ăn thuần chay',
    'nhà hàng có món nào hợp cho người dị ứng hải sản',
    'nhà hàng có món nào hợp cho người dị ứng đậu phộng',
    'nhà hàng có món nào hợp cho người dị ứng sữa',
    // Thêm các câu hỏi thông dụng về menu
    'menu có những món gì',
    'menu có gì',
    'thực đơn có gì',
    'thực đơn có những món gì',
    'nhà hàng có món gì',
    'nhà hàng có những món gì',
    'có những món gì',
    'có món gì',
    'menu gồm những gì',
    'thực đơn gồm những gì',
    'menu hôm nay có gì',
    'hôm nay có món gì',
    'menu có món gì ngon',
    'thực đơn có món gì đặc biệt',
    'món ngon nhất là gì',
    'món đặc trưng là gì',
    'món nổi tiếng nhất là gì',
    'menu',
    'thực đơn',
    'món ăn',
    'danh sách món ăn',
    'danh sách thực đơn',
    'cho xem menu',
    'cho xem thực đơn',
    'xem menu',
    'xem thực đơn',
    'menu của nhà hàng',
    'thực đơn của nhà hàng',
    'nhà hàng phục vụ món gì',
    'nhà hàng có phục vụ món gì',
    'món ăn đặc trưng',
    'món ăn đặc sản',
    'món ăn nổi tiếng',
    'món ăn phổ biến',
    'món ăn được yêu thích',
    'món ăn được gọi nhiều nhất',
    'món ăn bán chạy nhất',
    'món ăn ngon nhất',
    'món ăn đặc biệt',
    'món ăn theo mùa',
    'món ăn mới',
    'món ăn truyền thống',
    'món ăn hiện đại',
    'món ăn fusion',
    'món ăn Á',
    'món ăn Âu',
    'món ăn Việt',
    'món ăn Nhật',
    'món ăn Hàn',
    'món ăn Thái',
    'món ăn Trung',
    'món ăn Ý',
    'món ăn Pháp',
    'món ăn Mexico',
];

foreach ($menu_samples as $sample) {
    $samples[] = $sample;
    $labels[] = 'menu';
}

// Khuyến mãi
$promotion_samples = [
    'có khuyến mãi gì không',
    'ưu đãi hiện tại',
    'giảm giá',
    'combo nào rẻ',
    'chương trình khuyến mãi',
    'có voucher nào không',
    'khuyến mãi cuối tuần',
    'ưu đãi sinh nhật',
    'khuyến mãi cho nhóm',
    'có giảm giá không',
    'ưu đãi cho khách hàng thân thiết',
    'quà tặng kèm',
    'khuyến mãi ngày lễ',
    'deal hôm nay',
    'khuyến mãi',
    'giảm giá',
    'ưu đãi',
    'mã giảm giá',
    'ưu đãi sinh nhật',
    'thẻ thành viên',
    'khuyến mãi cuối tuần',
    'ưu đãi nhóm',
    'chương trình khách hàng thân thiết',
    'ưu đãi đặt online',
    'combo',
    'set',
    'set menu',
    'buffet',
    'combo gia đình',
    'set ăn trưa',
    'buffet tối',
];

foreach ($promotion_samples as $sample) {
    $samples[] = $sample;
    $labels[] = 'promotion';
}

// Đặt bàn
$reservation_samples = [
    'đặt bàn như thế nào',
    'làm sao để đặt bàn',
    'đặt chỗ trước',
    'đặt bàn online',
    'hủy đặt bàn',
    'đặt bàn cho 2 người',
    'tôi muốn đặt bàn',
    'tôi muốn đặt bàn cho 2 người tối nay',
    'có thể đặt bàn cho tối nay không',
    'đặt bàn cho nhóm 10 người',
    'muốn đặt tiệc',
    'đặt bàn vào buổi tối',
    'muốn đặt chỗ ngồi ngoài trời',
    'đặt bàn cho bữa trưa mai',
    'đặt phòng riêng',
    'cần đặt cọc khi đặt bàn không',
    'đặt bàn',
    'đặt chỗ',
    'đặt tiệc',
    'hủy đặt bàn',
    'cọc đặt bàn',
    'đặt bàn cho nhóm đông',
    'tổ chức sự kiện',
    'đặt tiệc sinh nhật',
    'đặt tiệc cưới',
    'đặt bàn online',
    'sức chứa tối đa',
    'trang trí tiệc',
    'đặt tiệc công ty',
    'thanh toán',
    'phương thức thanh toán',
    'trả góp',
    'giao hàng',
    'ship',
    'thời gian giao hàng',
    'đặt món online',
    'phí giao hàng',
    'đơn tối thiểu',
    'thanh toán qua ứng dụng',
    'hóa đơn điện tử',
    'giao hàng ngoài giờ',
    'khu vực giao hàng',
];

foreach ($reservation_samples as $sample) {
    $samples[] = $sample;
    $labels[] = 'reservation';
}

// Tiện ích - INTENT MỚI
$facilities_samples = [
    'wifi',
    'nhà hàng có wifi không',
    'mật khẩu wifi',
    'wifi miễn phí',
    'bãi đỗ xe',
    'chỗ đậu xe',
    'có chỗ đỗ xe không',
    'có bãi đậu xe ô tô không',
    'xuất hóa đơn',
    'hóa đơn vat',
    'chỗ ngồi',
    'phòng riêng',
    'có phòng riêng không',
    'khu vực hút thuốc',
    'chỗ chơi cho trẻ em',
    'có khu vui chơi trẻ em không',
    'người khuyết tật',
    'lối đi cho người khuyết tật',
    'thú cưng',
    'có cho mang thú cưng vào không',
    'không gian ngoài trời',
    'có chỗ ngồi ngoài trời không',
    'chỗ ngồi ngoài trời',
    'tiệc cưới ngoài trời',
    'nhạc sống',
    'có nhạc sống không',
    'dịch vụ chụp ảnh',
    'hỗ trợ người cao tuổi',
    'khăn giấy',
    'nhà vệ sinh',
    'có nhà vệ sinh không',
    'máy lạnh',
    'điều hòa',
    'có máy lạnh không',
    'tiện ích',
    'dịch vụ',
    'phòng vip',
    'có phòng vip không',
    'có máy chiếu không',
    'tiệc ngoài trời',
    'khu vực riêng',
    'có khu vực riêng không',
];

foreach ($facilities_samples as $sample) {
    $samples[] = $sample;
    $labels[] = 'facilities';
}

// Ngoài phạm vi - INTENT MỚI
$out_of_scope_samples = [
    'thời tiết hôm nay thế nào',
    'thời tiết ngày mai',
    'dự báo thời tiết',
    'bạn là ai',
    'bạn tên gì',
    'tên của bạn là gì',
    'ai tạo ra bạn',
    'bạn bao nhiêu tuổi',
    'bạn biết gì về facebook',
    'facebook là gì',
    'instagram là gì',
    'vật liệu xây dựng',
    'vật liệu xây dựng là gì',
    'các loại vật liệu xây dựng',
    'bạn biết gì về vật liệu xây dựng',
    'vật liệu xây dựng hiện đại',
    'vật liệu xây nhà',
    'giá vật liệu xây dựng',
    'xi măng',
    'cát xây dựng',
    'sắt thép xây dựng',
    'gạch xây dựng',
    'bàn thờ đẹp',
    'bàn ghế gỗ đẹp',
    'bàn làm việc',
    'bàn học',
    'bàn trang điểm',
    'bàn ăn đẹp',
    'bàn gỗ',
    'bàn phím',
    'bàn chải',
    'cách nấu món ăn ngon',
    'công thức nấu ăn',
    'cách làm món ăn',
    'dạy nấu ăn',
    'trang trí nhà hàng',
    'thiết kế nhà hàng',
    'mở nhà hàng',
    'kinh doanh nhà hàng',
    'làm thế nào để mở nhà hàng',
    'chi phí mở nhà hàng',
    'giấy phép kinh doanh nhà hàng',
    'giá vàng hôm nay',
    'tỷ giá đô la',
    'tỷ giá ngoại tệ',
    'cách làm bánh',
    'công thức nấu ăn',
    'chỉ đường đến sân bay',
    'đường đến bệnh viện',
    'thể thao',
    'bóng đá',
    'kết quả bóng đá',
    'tin tức hôm nay',
    'thời sự',
    'chính trị',
    'kinh tế',
    'văn hóa',
    'giáo dục',
    'y tế',
    'sức khỏe',
    'làm thế nào để giảm cân',
    'cách học tiếng anh',
    'dịch tiếng anh sang tiếng việt',
    'cách kiếm tiền online',
    'đầu tư chứng khoán',
    'bitcoin là gì',
    'tiền điện tử',
    'mua sắm online',
    'mã giảm giá shopee',
    'mã giảm giá lazada',
    'phim hay',
    'phim mới',
    'nhạc hay',
    'bài hát mới',
    'ca sĩ nổi tiếng',
    'diễn viên',
    'du lịch',
    'địa điểm du lịch',
    'khách sạn',
    // Thêm các câu hỏi vô nghĩa và từ ngữ không phù hợp
    'chán',
    'cc',
    'dm',
    'đm',
    'dcm',
    'đcm',
    'đéo',
    'deo',
    'cút',
    'cut',
    'vl',
    'vcl',
    'wtf',
    'fuck',
    'shit',
    'crap',
    '???',
    '!!!',
    '...',
    'hỏi chấm',
    'cc gì vậy',
    'm trả lời gì thế',
    'nói gì vậy',
    'ai hiểu gì đâu',
    'nói linh tinh',
    'trả lời linh tinh',
    'nói nhảm',
    'trả lời nhảm',
    'không hiểu gì cả',
    'nói gì không hiểu',
    'bot ngu',
    'bot kém',
    'bot dở',
    'chatbot dở',
    'chatbot ngu',
    'trợ lý ảo ngu',
    'trợ lý ảo kém',
    'trợ lý ảo dở',
];

foreach ($out_of_scope_samples as $sample) {
    $samples[] = $sample;
    $labels[] = 'out_of_scope';
}

// Danh sách các từ dừng tiếng Việt (stopwords)
$stopwords = [
    'và', 'hoặc', 'của', 'là', 'cho', 'có', 'không', 'bạn', 
    'cái', 'thì', 'mà', 'với', 'các', 'những',
    'được', 'vào', 'ra', 'đã', 'trong', 'ngoài', 'thế',
    'còn', 'cũng', 'này', 'lúc', 'về', 'khi'
];

// Từ khóa quan trọng cho mỗi intent
$keywords = [
    'opening_hours' => ['mở', 'cửa', 'giờ', 'thời gian', 'đóng', 'khuya', 'phục vụ', 'làm việc', 'cao điểm', 'mở cửa', 'đóng cửa', 'giờ mở', 'giờ đóng', 'thời gian mở', 'thời gian đóng', 'giờ làm việc', 'giờ phục vụ'],
    'location' => ['địa chỉ', 'đâu', 'vị trí', 'chỗ', 'đường', 'nằm', 'địa điểm', 'liên hệ', 'số điện thoại', 'email', 'website', 'nhà hàng ở đâu', 'nhà hàng nằm ở đâu', 'nhà hàng ở chỗ nào', 'nhà hàng ở đường nào', 'nhà hàng ở khu vực nào', 'chi nhánh'],
    'menu' => ['món', 'ăn', 'ngon', 'đặc sản', 'thực đơn', 'menu', 'signature', 'đồ uống', 'tráng miệng', 'khai vị', 'chay', 'hải sản', 'cay', 'bữa sáng', 'bữa trưa', 'bữa tối', 'giá', 'bao nhiêu', 'chi phí', 'rẻ', 'đắt', 'so sánh', 'món chay', 'có món chay không', 'nhà hàng có món chay', 'món ăn', 'thực đơn', 'menu', 'món signature', 'món đặc sản', 'món ngon', 'món bán chạy', 'món cho trẻ em', 'món cay', 'món không cay', 'món hải sản', 'món kiêng', 'món không gluten', 'món thuần chay'],
    'promotion' => ['khuyến mãi', 'ưu đãi', 'giảm giá', 'combo', 'voucher', 'deal', 'set', 'buffet', 'thẻ thành viên', 'sinh nhật', 'giảm', 'quà tặng', 'miễn phí', 'tặng', 'khuyến', 'mãi', 'khuyến mãi', 'ưu đãi', 'giảm giá', 'quà tặng', 'miễn phí', 'mua 1 tặng 1', 'giảm', 'tặng', 'free', 'combo', 'set', 'buffet'],
    'reservation' => ['đặt', 'bàn', 'chỗ', 'tiệc', 'cọc', 'phòng', 'người', 'tối', 'trưa', 'muốn đặt', 'nhóm', 'sự kiện', 'sinh nhật', 'cưới', 'thanh toán', 'giao hàng', 'ship', 'đặt bàn', 'đặt chỗ', 'đặt tiệc', 'đặt phòng', 'đặt bàn trước', 'đặt chỗ trước', 'đặt tiệc sinh nhật', 'đặt tiệc cưới', 'đặt tiệc công ty', 'đặt bàn cho nhóm', 'đặt bàn cho gia đình', 'đặt bàn cho cặp đôi', 'đặt bàn cho 2 người', 'đặt bàn cho 4 người', 'đặt bàn cho 6 người', 'đặt bàn cho 10 người', 'đặt bàn cho 20 người'],
    'facilities' => ['wifi', 'đỗ xe', 'đậu xe', 'phòng riêng', 'hút thuốc', 'trẻ em', 'khuyết tật', 'thú cưng', 'ngoài trời', 'nhạc sống', 'vệ sinh', 'máy lạnh', 'tiện ích', 'dịch vụ', 'vip', 'máy chiếu', 'wifi', 'internet', 'wifi miễn phí', 'bãi đỗ xe', 'chỗ đậu xe', 'phòng riêng', 'phòng vip', 'khu vực hút thuốc', 'khu vực không hút thuốc', 'khu vực ngoài trời', 'khu vực trong nhà', 'ghế trẻ em', 'góc chơi cho trẻ em', 'lối đi cho người khuyết tật', 'cho phép mang thú cưng', 'nhạc sống', 'máy chiếu', 'karaoke', 'điều hòa', 'máy lạnh', 'nhà vệ sinh', 'toilet'],
    'out_of_scope' => ['thời tiết', 'bạn là ai', 'tên', 'tuổi', 'facebook', 'instagram', 'vật liệu', 'xây dựng', 'xi măng', 'cát', 'sắt thép', 'gạch', 'bàn thờ', 'bàn ghế', 'bàn làm việc', 'bàn học', 'bàn trang điểm', 'cách nấu', 'công thức', 'dạy nấu', 'trang trí', 'thiết kế', 'mở nhà hàng', 'kinh doanh', 'giấy phép', 'giá vàng', 'tỷ giá', 'bánh', 'nấu ăn', 'sân bay', 'bệnh viện', 'thể thao', 'bóng đá', 'tin tức', 'thời sự', 'chính trị', 'kinh tế', 'giáo dục', 'y tế', 'sức khỏe', 'giảm cân', 'tiếng anh', 'kiếm tiền', 'chứng khoán', 'bitcoin', 'mua sắm', 'shopee', 'lazada', 'phim', 'nhạc', 'ca sĩ', 'diễn viên', 'du lịch', 'chán', 'hỏi chấm', 'cc', 'cc gì vậy', 'm trả lời gì thế', 'dm', 'đm', 'dcm', 'đcm', 'đéo', 'deo', 'cút', 'cut', 'vl', 'vcl', 'wtf', 'fuck', 'shit', 'crap'],
];

// Tiền xử lý văn bản
function preprocessText($text) {
    // Chuyển về chữ thường
    $text = mb_strtolower($text, 'UTF-8');
    
    // Loại bỏ các ký tự đặc biệt
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
    
    // Tách từ
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    
    return $words;
}

// Biến đổi mỗi câu thành một mảng các từ đã qua xử lý
$processedSamples = [];
foreach ($samples as $sample) {
    $words = preprocessText($sample);
    
    // Loại bỏ stopwords
    $filteredWords = [];
    foreach ($words as $word) {
        if (!in_array($word, $stopwords) && mb_strlen($word, 'UTF-8') > 1) {
            $filteredWords[] = $word;
        }
    }
    
    $processedSamples[] = $filteredWords;
}

// Tạo bộ từ điển (vocabulary)
$vocabulary = [];
foreach ($processedSamples as $sample) {
    foreach ($sample as $word) {
        if (!in_array($word, $vocabulary)) {
            $vocabulary[] = $word;
        }
    }
}

// Thêm từ khóa quan trọng vào từ điển nếu chưa có
foreach ($keywords as $intent => $keywordList) {
    foreach ($keywordList as $keyword) {
        $keywordWords = explode(' ', $keyword);
        foreach ($keywordWords as $word) {
            if (!in_array($word, $vocabulary) && mb_strlen($word, 'UTF-8') > 1) {
                $vocabulary[] = $word;
            }
        }
    }
}

// Biến đổi mỗi mẫu thành vector đặc trưng với trọng số
$features = [];
foreach ($processedSamples as $index => $sample) {
    $feature = array_fill(0, count($vocabulary), 0);
    
    // Đếm số lần xuất hiện của từng từ
    foreach ($sample as $word) {
        $vocabIndex = array_search($word, $vocabulary);
        if ($vocabIndex !== false) {
            $feature[$vocabIndex]++;
        }
    }
    
    // Tăng trọng số cho các từ khóa quan trọng
    $currentLabel = $labels[$index];
    if (isset($keywords[$currentLabel])) {
        foreach ($keywords[$currentLabel] as $keyword) {
            $keywordWords = explode(' ', $keyword);
            foreach ($keywordWords as $word) {
                $vocabIndex = array_search($word, $vocabulary);
                if ($vocabIndex !== false && in_array($word, $sample)) {
                    // Tăng trọng số cho các từ khóa
                    $weight = 5; // Tăng trọng số cơ bản từ 3 lên 5
                    
                    // Tăng trọng số mạnh hơn cho các từ khóa chính của mỗi intent
                    if ($currentLabel == 'opening_hours' && in_array($word, ['mở', 'cửa', 'giờ', 'mở cửa', 'đóng cửa', 'thời gian'])) {
                        $weight = 15; // Tăng từ 10 lên 15
                    } else if ($currentLabel == 'location' && in_array($word, ['địa chỉ', 'đâu', 'vị trí', 'nhà hàng ở đâu', 'nhà hàng nằm ở đâu'])) {
                        $weight = 15; // Tăng từ 10 lên 15
                    } else if ($currentLabel == 'menu' && in_array($word, ['món', 'ăn', 'menu', 'thực đơn', 'món ăn', 'món ngon'])) {
                        $weight = 15; // Tăng từ 10 lên 15
                    } else if ($currentLabel == 'promotion' && in_array($word, ['khuyến mãi', 'ưu đãi', 'giảm giá', 'giảm', 'tặng'])) {
                        $weight = 15; // Tăng từ 10 lên 15
                    } else if ($currentLabel == 'reservation' && in_array($word, ['đặt', 'bàn', 'tiệc', 'đặt bàn', 'đặt chỗ', 'đặt tiệc'])) {
                        $weight = 15; // Tăng từ 10 lên 15
                    } else if ($currentLabel == 'facilities' && in_array($word, ['wifi', 'đỗ xe', 'phòng riêng', 'tiện ích', 'wifi miễn phí', 'bãi đỗ xe'])) {
                        $weight = 15; // Tăng từ 10 lên 15
                    } else if ($currentLabel == 'out_of_scope' && in_array($word, ['thời tiết', 'bạn là ai', 'vật liệu', 'xây dựng', 'bàn thờ', 'chán', 'cc'])) {
                        $weight = 15; // Tăng từ 5 lên 15
                    }
                    
                    // Tăng trọng số lên 15 lần cho từ khóa "giá" trong intent menu
                    if ($currentLabel == 'menu' && ($word == 'giá' || $word == 'bao nhiêu' || $word == 'chi phí')) {
                        $weight = 20; // Tăng từ 15 lên 20
                    }
                    
                    // Tăng trọng số lên 15 lần cho từ khóa "vật liệu" và "xây dựng" trong intent out_of_scope
                    if ($currentLabel == 'out_of_scope' && ($word == 'vật liệu' || $word == 'xây dựng' || $word == 'xi măng' || $word == 'cát' || $word == 'gạch')) {
                        $weight = 20; // Tăng từ 15 lên 20
                    }
                    
                    $feature[$vocabIndex] *= $weight;
                }
            }
        }
    }
    
    $features[] = $feature;
}

// Kiểm tra số lượng mẫu và nhãn
echo "Số lượng mẫu: " . count($samples) . "\n";
echo "Số lượng nhãn: " . count($labels) . "\n";
echo "Số lượng features: " . count($features) . "\n";

// Huấn luyện mô hình
$dataset = new ArrayDataset($features, $labels);

// Sử dụng KNN với k=3 để có kết quả tốt hơn với dữ liệu nhiều
$classifier = new KNearestNeighbors(3);
$classifier->train($dataset->getSamples(), $dataset->getTargets());

// Lưu mô hình
$modelManager = new ModelManager();
$modelManager->saveToFile($classifier, __DIR__ . '/restaurant_intent_model');

// Lưu vocabulary, stopwords và keywords để sử dụng sau này
file_put_contents(__DIR__ . '/vocabulary.json', json_encode($vocabulary));
file_put_contents(__DIR__ . '/stopwords.json', json_encode($stopwords));
file_put_contents(__DIR__ . '/keywords.json', json_encode($keywords));

// Thêm một vài dữ liệu test để kiểm tra độ chính xác
$testSamples = [
    'nhà hàng mở cửa lúc mấy giờ?' => 'opening_hours',
    'địa chỉ của nhà hàng ở đâu vậy?' => 'location',
    'tôi muốn đặt bàn cho 2 người tối nay' => 'reservation',
    'có chương trình khuyến mãi nào không?' => 'promotion',
    'menu nhà hàng có những món gì?' => 'menu',
    'nhà hàng có bữa trưa không?' => 'menu',
    'làm sao để đặt bàn cho 10 người?' => 'reservation',
    'có khuyến mãi cho sinh nhật không?' => 'promotion',
    'nhà hàng nằm ở con đường nào?' => 'location',
    'giờ cao điểm là mấy giờ?' => 'opening_hours',
    'nhà hàng có wifi không?' => 'facilities',
    'có chỗ đậu xe ô tô không?' => 'facilities',
    'có phòng riêng cho nhóm không?' => 'facilities',
    'thời tiết hôm nay thế nào?' => 'out_of_scope',
    'bạn là ai?' => 'out_of_scope',
    'giá vàng hôm nay' => 'out_of_scope',
    'giá món ăn của nhà hàng' => 'menu',
    'món ăn giá bao nhiêu' => 'menu',
    'so sánh món ăn' => 'menu',
    'nhà hàng có món chay không?' => 'menu',
    'món chay' => 'menu',
    'bàn thờ đẹp' => 'out_of_scope',
    'bàn ghế gỗ đẹp' => 'out_of_scope',
    'bàn làm việc' => 'out_of_scope',
    'cách nấu món ăn ngon' => 'out_of_scope',
    'trang trí nhà hàng' => 'out_of_scope',
    'làm thế nào để mở nhà hàng' => 'out_of_scope',
    'có wifi không' => 'facilities',
    'có chỗ đậu xe không' => 'facilities',
    'tôi muốn đặt bàn cho 5 người' => 'reservation',
];

echo "Mô hình đã được huấn luyện và lưu thành công!\n";

// Kiểm tra độ chính xác
$correct = 0;
$total = count($testSamples);

echo "\nKiểm tra độ chính xác mô hình:\n";
echo "------------------------------\n";

$resultsByIntent = [];
foreach (array_unique($labels) as $intent) {
    $resultsByIntent[$intent] = [
        'correct' => 0,
        'total' => 0,
        'accuracy' => 0
    ];
}

foreach ($testSamples as $text => $expectedIntent) {
    // Tiền xử lý
    $words = preprocessText($text);
    
    // Loại bỏ stopwords
    $filteredWords = [];
    foreach ($words as $word) {
        if (!in_array($word, $stopwords) && mb_strlen($word, 'UTF-8') > 1) {
            $filteredWords[] = $word;
        }
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
    
    // Dự đoán
    $predictedIntent = $classifier->predict($feature);
    
    echo "Câu: '$text'\n";
    echo "Intent kỳ vọng: $expectedIntent\n";
    echo "Intent dự đoán: $predictedIntent\n";
    
    // Cập nhật thống kê cho intent này
    if (!isset($resultsByIntent[$expectedIntent])) {
        $resultsByIntent[$expectedIntent] = [
            'correct' => 0,
            'total' => 0,
            'accuracy' => 0
        ];
    }
    $resultsByIntent[$expectedIntent]['total']++;
    
    if ($predictedIntent === $expectedIntent) {
        echo "✓ Đúng\n\n";
        $correct++;
        $resultsByIntent[$expectedIntent]['correct']++;
    } else {
        echo "✗ Sai\n\n";
    }
}

// Tính toán độ chính xác cho mỗi intent
foreach ($resultsByIntent as $intent => &$result) {
    if ($result['total'] > 0) {
        $result['accuracy'] = ($result['correct'] / $result['total']) * 100;
    }
}

$accuracy = ($correct / $total) * 100;
echo "Độ chính xác tổng thể: $correct/$total (" . number_format($accuracy, 2) . "%)\n\n";

// Hiển thị độ chính xác cho từng intent
echo "Độ chính xác theo intent:\n";
echo "------------------------\n";
foreach ($resultsByIntent as $intent => $result) {
    if ($result['total'] > 0) {
        echo "$intent: " . $result['correct'] . "/" . $result['total'] . " (" . number_format($result['accuracy'], 2) . "%)\n";
    }
}

// Kiểm tra với các hàm phân loại đặc biệt
echo "\nKiểm tra các hàm phân loại đặc biệt:\n";
echo "--------------------------------\n";

// Tạo các hàm kiểm tra
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

// Kiểm tra các trường hợp đặc biệt
$specialCases = [
    'bàn thờ đẹp' => 'out_of_scope',
    'bàn ghế gỗ đẹp' => 'out_of_scope',
    'bàn làm việc' => 'out_of_scope',
    'cách nấu món ăn ngon' => 'out_of_scope',
    'trang trí nhà hàng' => 'out_of_scope',
    'làm thế nào để mở nhà hàng' => 'out_of_scope',
    'nhà hàng có món chay không' => 'menu',
    'món chay' => 'menu',
    'nhà hàng có wifi không' => 'facilities',
    'có chỗ đậu xe không' => 'facilities',
    'giá món ăn của nhà hàng' => 'menu',
    'món ăn giá bao nhiêu' => 'menu',
];

$specialCorrect = 0;
$specialTotal = count($specialCases);

foreach ($specialCases as $text => $expectedIntent) {
    $predictedIntent = null;
    
    // Kiểm tra các trường hợp đặc biệt trước
    if (isConstructionMaterialQuestion($text)) {
        $predictedIntent = 'out_of_scope';
    } else if (isFurnitureQuestion($text)) {
        $predictedIntent = 'out_of_scope';
    } else if (isCookingQuestion($text)) {
        $predictedIntent = 'out_of_scope';
    } else if (isRestaurantBusinessQuestion($text)) {
        $predictedIntent = 'out_of_scope';
    } else if (isVegetarianFoodQuestion($text)) {
        $predictedIntent = 'menu';
    } else if (isFacilitiesQuestion($text)) {
        $predictedIntent = 'facilities';
    } else if (isPriceRelatedQuestion($text)) {
        $predictedIntent = 'menu';
    }
    
    echo "Câu: '$text'\n";
    echo "Intent kỳ vọng: $expectedIntent\n";
    echo "Intent dự đoán: $predictedIntent\n";
    
    if ($predictedIntent === $expectedIntent) {
        echo "✓ Đúng\n\n";
        $specialCorrect++;
    } else {
        echo "✗ Sai\n\n";
    }
}

$specialAccuracy = ($specialCorrect / $specialTotal) * 100;
echo "Độ chính xác cho các trường hợp đặc biệt: $specialCorrect/$specialTotal (" . number_format($specialAccuracy, 2) . "%)\n";

// Kiểm tra so sánh giữa mô hình KNN và các hàm phân loại đặc biệt
echo "\nSo sánh mô hình KNN với các hàm phân loại đặc biệt:\n";
echo "------------------------------------------------\n";

$specialTestCases = [
    'bàn thờ đẹp' => 'out_of_scope',
    'bàn ghế gỗ đẹp' => 'out_of_scope',
    'bàn làm việc' => 'out_of_scope',
    'cách nấu món ăn ngon' => 'out_of_scope',
    'trang trí nhà hàng' => 'out_of_scope',
    'làm thế nào để mở nhà hàng' => 'out_of_scope',
    'nhà hàng có món chay không' => 'menu',
    'món chay' => 'menu',
    'nhà hàng có wifi không' => 'facilities',
    'có chỗ đậu xe không' => 'facilities',
    'giá món ăn của nhà hàng' => 'menu',
    'món ăn giá bao nhiêu' => 'menu',
];

$knnCorrect = 0;
$specialFuncCorrect = 0;
$totalSpecialCases = count($specialTestCases);

echo "| Câu hỏi | Intent kỳ vọng | KNN dự đoán | Hàm đặc biệt dự đoán | KNN | Hàm đặc biệt |\n";
echo "|---------|---------------|------------|---------------------|-----|-------------|\n";

foreach ($specialTestCases as $text => $expectedIntent) {
    // Dự đoán bằng KNN
    $words = preprocessText($text);
    $filteredWords = [];
    foreach ($words as $word) {
        if (!in_array($word, $stopwords) && mb_strlen($word, 'UTF-8') > 1) {
            $filteredWords[] = $word;
        }
    }
    
    $feature = array_fill(0, count($vocabulary), 0);
    foreach ($filteredWords as $word) {
        $index = array_search($word, $vocabulary);
        if ($index !== false) {
            $feature[$index]++;
        }
    }
    
    $knnPredictedIntent = $classifier->predict($feature);
    
    // Dự đoán bằng hàm đặc biệt
    $specialPredictedIntent = null;
    if (isConstructionMaterialQuestion($text)) {
        $specialPredictedIntent = 'out_of_scope';
    } else if (isFurnitureQuestion($text)) {
        $specialPredictedIntent = 'out_of_scope';
    } else if (isCookingQuestion($text)) {
        $specialPredictedIntent = 'out_of_scope';
    } else if (isRestaurantBusinessQuestion($text)) {
        $specialPredictedIntent = 'out_of_scope';
    } else if (isVegetarianFoodQuestion($text)) {
        $specialPredictedIntent = 'menu';
    } else if (isFacilitiesQuestion($text)) {
        $specialPredictedIntent = 'facilities';
    } else if (isPriceRelatedQuestion($text)) {
        $specialPredictedIntent = 'menu';
    }
    
    // Kiểm tra kết quả
    $knnCorrectMark = ($knnPredictedIntent === $expectedIntent) ? "✓" : "✗";
    $specialCorrectMark = ($specialPredictedIntent === $expectedIntent) ? "✓" : "✗";
    
    if ($knnPredictedIntent === $expectedIntent) {
        $knnCorrect++;
    }
    
    if ($specialPredictedIntent === $expectedIntent) {
        $specialFuncCorrect++;
    }
    
    echo "| $text | $expectedIntent | $knnPredictedIntent | $specialPredictedIntent | $knnCorrectMark | $specialCorrectMark |\n";
}

$knnAccuracy = ($knnCorrect / $totalSpecialCases) * 100;
$specialFuncAccuracy = ($specialFuncCorrect / $totalSpecialCases) * 100;

echo "\nĐộ chính xác của mô hình KNN cho các trường hợp đặc biệt: $knnCorrect/$totalSpecialCases (" . number_format($knnAccuracy, 2) . "%)\n";
echo "Độ chính xác của các hàm phân loại đặc biệt: $specialFuncCorrect/$totalSpecialCases (" . number_format($specialFuncAccuracy, 2) . "%)\n";

// So sánh hiệu suất
if ($knnAccuracy > $specialFuncAccuracy) {
    echo "\nKết luận: Mô hình KNN hoạt động tốt hơn các hàm phân loại đặc biệt.\n";
} else if ($knnAccuracy < $specialFuncAccuracy) {
    echo "\nKết luận: Các hàm phân loại đặc biệt hoạt động tốt hơn mô hình KNN.\n";
} else {
    echo "\nKết luận: Mô hình KNN và các hàm phân loại đặc biệt có hiệu suất ngang nhau.\n";
}

?> 