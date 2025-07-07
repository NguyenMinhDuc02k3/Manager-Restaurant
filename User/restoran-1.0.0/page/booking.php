<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Đảm bảo session được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session
if (!isset($_SESSION['debug'])) {
    $_SESSION['debug'] = [];
}
$_SESSION['debug'][] = [
    'time' => date('Y-m-d H:i:s'),
    'page' => 'booking.php',
    'session_booking' => isset($_SESSION['booking']) ? $_SESSION['booking'] : 'not_set',
    'session_id' => session_id(),
    'request' => $_SERVER['REQUEST_METHOD'],
    'get' => $_GET,
    'post' => $_POST
];

require_once 'class/clsdatban.php';
require_once 'class/clskvban.php';

if (!isset($_GET['page'])) {
    $page = 'booking';
} else {
    $page = $_GET['page'];
}

// Xử lý yêu cầu POST hoặc GET
$maKhuVuc = null;
$datetime = null;
$people_count = 0;
$tenKhuVuc = '';
$dsBan = [];
$dsBanDaDat = [];
$selectKhuVuc = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['khuvuc']) && isset($_POST['datetime'])) {
    // Xử lý POST từ form chọn khu vực
    $maKhuVuc = (int)trim($_POST['khuvuc']);
    $datetime = trim($_POST['datetime']);
    $people_count = isset($_POST['people_count']) ? (int)trim($_POST['people_count']) : 0;

    if (empty($maKhuVuc) || empty($datetime) || $people_count < 1) {
        header("Location: index.php?page=trangchu");
        exit;
    }

    // Lưu vào session
    $_SESSION['booking'] = [
        'khuvuc' => $maKhuVuc,
        'datetime' => $datetime,
        'people_count' => $people_count
    ];
    $_SESSION['debug'][] = ['time' => date('Y-m-d H:i:s'), 'action' => 'set_booking_post', 'data' => $_SESSION['booking']];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['khuvuc']) && isset($_GET['datetime'])) {
    // Xử lý GET từ nút "Quay lại"
    $maKhuVuc = (int)trim($_GET['khuvuc']);
    $datetime = urldecode(trim($_GET['datetime']));
    $people_count = isset($_GET['people_count']) ? (int)trim($_GET['people_count']) : 0;

    if (empty($maKhuVuc) || empty($datetime) || $people_count < 1) {
        header("Location: index.php?page=trangchu");
        exit;
    }

    // Lưu vào session
    $_SESSION['booking'] = [
        'khuvuc' => $maKhuVuc,
        'datetime' => $datetime,
        'people_count' => $people_count
    ];
    $_SESSION['debug'][] = ['time' => date('Y-m-d H:i:s'), 'action' => 'set_booking_get', 'data' => $_SESSION['booking']];
} elseif (isset($_SESSION['booking'])) {
    // Khôi phục từ session
    $maKhuVuc = $_SESSION['booking']['khuvuc'];
    $datetime = $_SESSION['booking']['datetime'];
    $people_count = $_SESSION['booking']['people_count'];
    $_SESSION['debug'][] = ['time' => date('Y-m-d H:i:s'), 'action' => 'restore_booking_session', 'data' => $_SESSION['booking']];
} else {
    header("Location: index.php?page=trangchu");
    exit;
}

// Lấy danh sách bàn và khu vực
$ban = new datban();
$dsBan = $ban->getBanTheoKhuVuc($maKhuVuc);
$dsBanDaDat = $ban->getBanDaDat($maKhuVuc, $datetime);
$khuvuc = new KhuVucBan();
$selectKhuVuc = $khuvuc->selectKvban($maKhuVuc);
$tenKhuVuc = $khuvuc->getTenKhuVuc($maKhuVuc);

// Thêm thông báo về khoảng thời gian đặt bàn
$timeSlotMessage = "Lưu ý: Mỗi lần đặt bàn phải cách nhau ít nhất 2 tiếng.";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Bàn - Restoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .ban-btn { min-width: 120px; margin: 5px; }
        .btn-success { background-color: #28a745 !important; border-color: #28a745 !important; }
        .time-slot-message {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Đang tải...</span>
            </div>
        </div>

        <!-- Hero Header -->
        <div class="container-xxl py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">Đặt Bàn</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center text-uppercase">
                        <li class="breadcrumb-item "><a href="index.php?page=trangchu" class="text-warning">Trang Chủ</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-warning">Trang</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Đặt Bàn</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Nội dung chọn bàn -->
        <div class="container my-5">
            <div class="time-slot-message">
                <?php echo htmlspecialchars($timeSlotMessage); ?>
            </div>
            
            <div class="text-center mb-4">
                <form method="POST" id="khuvucForm" action="index.php?page=booking">
                    <input type="hidden" name="datetime" value="<?= htmlspecialchars($datetime) ?>">
                    <input type="hidden" name="people_count" value="<?= htmlspecialchars($people_count) ?>">
                    <label for="khuvuc">Chọn khu vực: </label>
                    <select name="khuvuc" id="khuvuc" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="">-- Chọn khu vực bàn --</option>
                        <?= $selectKhuVuc ?>
                    </select>
                </form>
                <h4 class="mt-3">Khu vực hiện tại: <strong id="tenKhuVuc">
                    <?= htmlspecialchars($tenKhuVuc) ?>
                </strong></h4>
            </div>

            <div class="row justify-content-center" id="dsBanContainer">
                <?php if (empty($dsBan)): ?>
                    <p>Không có bàn nào trong khu vực này.</p>
                <?php else: ?>
                    <?php foreach ($dsBan as $b): 
                        $isBooked = in_array($b['idban'], $dsBanDaDat);
                        $class = $isBooked ? 'btn-danger' : 'btn-outline-secondary';
                        $disabled = $isBooked ? 'disabled' : '';
                        $title = $isBooked ? 'Bàn này đã được đặt trong khoảng thời gian 2 tiếng trước hoặc sau thời điểm bạn chọn' : '';
                    ?>
                        <div class="col-auto mb-3">
                            <button class="btn <?= $class ?> ban-btn" 
                                    data-maban="<?= $b['idban'] ?>" 
                                    <?= $disabled ?>
                                    title="<?= $title ?>">
                                Bàn <?= htmlspecialchars($b['SoBan']) ?> 
                                <?= $isBooked ? '(Đã đặt)' : '' ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="index.php?page=book_menu" id="banForm">
                <input type="hidden" name="maban" id="mabanHidden">
                <input type="hidden" name="khuvuc" value="<?= htmlspecialchars($maKhuVuc) ?>">
                <input type="hidden" name="datetime" value="<?= htmlspecialchars($datetime) ?>">
                <input type="hidden" name="people_count" value="<?= htmlspecialchars($people_count) ?>">
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-warning" id="confirmButton" disabled>Tiếp tục chọn món</button>
                </div>
            </form>
        </div>

        <!-- JS Libraries -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const khuvucSelect = document.getElementById('khuvuc');
                const confirmButton = document.getElementById('confirmButton');
                if (!khuvucSelect) {
                    console.error('Không tìm thấy phần tử #khuvuc');
                    return;
                }

                function resetBanButtons() {
                    const buttons = document.querySelectorAll('.ban-btn');
                    buttons.forEach(btn => {
                        btn.addEventListener('click', e => {
                            e.preventDefault();
                            if (!btn.disabled) {
                                buttons.forEach(b => b.classList.remove('btn-success'));
                                btn.classList.add('btn-success');
                                document.getElementById('mabanHidden').value = btn.dataset.maban;
                                confirmButton.disabled = false;
                            }
                        });
                    });
                }

                resetBanButtons();
            });
        </script>
    </div>
</body>
</html>