<?php
// Bắt đầu output buffering
ob_start();

// Đảm bảo session được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Debug session
if (!isset($_SESSION['debug'])) {
    $_SESSION['debug'] = [];
}
$_SESSION['debug'][] = [
    'time' => date('Y-m-d H:i:s'),
    'page' => 'index.php',
    'session_booking' => isset($_SESSION['booking']) ? $_SESSION['booking'] : 'not_set',
    'session_madatban' => isset($_SESSION['madatban']) ? $_SESSION['madatban'] : 'not_set',
    'session_id' => session_id(),
    'get' => $_GET
];

// Xử lý AJAX cập nhật đơn hàng
if (isset($_GET['page']) && $_GET['page'] == 'update_order') {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_monan'])) {
        try {
            $selectedMonAn = json_decode($_POST['selected_monan'], true);
            if (is_array($selectedMonAn)) {
                $_SESSION['selected_monan'] = $selectedMonAn;
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    }
    exit;
}

// Xử lý AJAX lọc danh mục và tìm kiếm
if (isset($_GET['page']) && $_GET['page'] == 'filter_menu') {
    header('Content-Type: text/html; charset=UTF-8');
    require_once 'class/clsmonan.php';
    $monAn = new clsMonAn();

    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $danhmuc = isset($_POST['danhmuc']) && $_POST['danhmuc'] !== '' ? (int)$_POST['danhmuc'] : 0;

    if ($search && $danhmuc) {
        $monAnList = $monAn->searchMonAnByDanhMuc($search, $danhmuc);
    } elseif ($search) {
        $monAnList = $monAn->searchMonAn($search);
    } elseif ($danhmuc) {
        $monAnList = $monAn->getMonAnByDanhMuc($danhmuc);
    } else {
        $monAnList = $monAn->getAllMonAn();
    }

    $_SESSION['debug'][] = [
        'time' => date('Y-m-d H:i:s'),
        'action' => 'filter_menu',
        'danhmuc' => $danhmuc,
        'search' => $search,
        'monAnList_count' => count($monAnList)
    ];

    ob_start();
    if (empty($monAnList)) {
        echo '<p>Không tìm thấy món ăn nào.</p>';
    } else {
        foreach ($monAnList as $mon) {
            ?>
            <div class="menu-item">
                <img src="img/<?= htmlspecialchars($mon['hinhanh'] ?: 'default.jpg') ?>" alt="<?= htmlspecialchars($mon['tenmonan']) ?>">
                <div class="menu-item-details">
                    <strong><?= htmlspecialchars($mon['tenmonan']) ?></strong><br>
                    <?= htmlspecialchars($mon['mota'] ?: 'Không có mô tả') ?><br>
                    Giá: <span class="DonGia"><?= number_format($mon['DonGia']) ?> VND</span>
                </div>
                <button class="btn btn-sm btn-primary" onclick="addMonAn(<?= $mon['idmonan'] ?>, '<?= addslashes($mon['tenmonan']) ?>', <?= $mon['DonGia'] ?>)">+</button>
                <button class="btn btn-sm btn-secondary" onclick="removeMonAn(<?= $mon['idmonan'] ?>)">−</button>
            </div>
            <?php
        }
    }
    $html = ob_get_clean();
    echo $html;
    exit;
}

// Kiểm tra timeout cho các trang nhạy cảm
if (isset($_GET['page']) && in_array($_GET['page'], ['customer_info'])) {
    if (isset($_SESSION['booking']['booking_time'])) {
        $timeout = 180; // 3 phút
        $timeLeft = max(0, $timeout - (time() - $_SESSION['booking']['booking_time']));
        if ($timeLeft <= 0) {
            unset($_SESSION['booking']);
            unset($_SESSION['selected_monan']);
            unset($_SESSION['madatban']);
            $_SESSION['error'] = 'Thời gian giữ bàn đã hết. Vui lòng đặt lại.';
            header('Location: index.php?page=error');
            exit;
        }
    } else {
        unset($_SESSION['booking']);
        unset($_SESSION['selected_monan']);
        unset($_SESSION['madatban']);
        $_SESSION['error'] = 'Phiên đặt bàn không hợp lệ. Vui lòng đặt lại.';
        header('Location: index.php?page=error');
        exit;
    }
}

// Kiểm tra session cho trang nhập thông tin khách hàng
if (in_array($_GET['page'] ?? '', ['customer_info']) && !isset($_SESSION['booking'])) {
    unset($_SESSION['madatban']);
    $_SESSION['error'] = 'Thông tin đặt bàn không tồn tại. Vui lòng đặt lại.';
    header('Location: index.php?page=error');
    exit;
}

// Include layout và trang
include('layout/header.php');

// Chỉ hiển thị menu khi không phải đang trong quá trình đặt bàn
$booking_pages = ['booking', 'book_menu', 'confirm_booking', 'customer_info', 'success'];
if (!isset($_GET['page']) || !in_array($_GET['page'], $booking_pages)) {
    include('layout/menu.php');
}

// Xử lý các trang
$page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';

$pageFile = 'page/' . $page . '.php';
if (file_exists($pageFile)) {
    include($pageFile);
} else {
    include('page/trangchu.php');
}

include('layout/footer.php');

// Include chatbot
include('page/chatbot.php');

// Ở cuối file, sau tất cả output
ob_end_flush();


?>