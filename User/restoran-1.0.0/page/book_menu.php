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
    'page' => 'book_menu.php',
    'session_booking' => isset($_SESSION['booking']) ? $_SESSION['booking'] : 'not_set',
    'session_id' => session_id(),
    'request' => $_SERVER['REQUEST_METHOD'],
    'get' => $_GET,
    'post' => $_POST
];

require_once 'class/clsmonan.php';
require_once 'class/clsdanhmuc.php';

// Khởi tạo các lớp
$monAn = new clsMonAn();
$danhMuc = new clsDanhMuc();
$danhMucList = $danhMuc->getAllDanhMuc();
$monAnList = $monAn->getAllMonAn(); // Lấy tất cả món ăn ban đầu

// Xử lý thông tin booking từ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maban']) && isset($_POST['khuvuc']) && isset($_POST['datetime']) && isset($_POST['people_count'])) {
    $_SESSION['booking'] = [
        'maban' => (int)trim($_POST['maban']),
        'khuvuc' => (int)trim($_POST['khuvuc']),
        'datetime' => trim($_POST['datetime']),
        'people_count' => (int)trim($_POST['people_count']),
    ];
    $_SESSION['debug'][] = ['time' => date('Y-m-d H:i:s'), 'action' => 'set_booking', 'data' => $_SESSION['booking']];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Món Ăn - Restoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .menu-container { display: flex; margin: 20px 0; }
        .menu-left { width: 60%; padding-right: 20px; }
        .menu-right { width: 40%; background-color: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .menu-item { display: flex; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .menu-item img { width: 60px; height: 60px; object-fit: cover; margin-right: 15px; border-radius: 5px; }
        .menu-item-details { flex-grow: 1; }
        .menu-item button { padding: 5px 10px; margin-left: 10px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table th, .summary-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .error { color: red; font-size: 14px; margin-top: 5px; }
        .btn-back { background-color: #6c757d; }
        .btn-back:hover { background-color: #5a6268; }
        .alert { margin-bottom: 20px; }
        .btn-primary {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        .btn-primary:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
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
                <h1 class="display-3 text-white mb-3 animated slideInDown">Chọn Món Ăn</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php?page=trangchu" class="text-warning">Trang Chủ</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-warning">Trang</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Chọn Món</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Nội dung chọn món ăn -->
        <div class="container my-5">
            <?php if (!isset($_SESSION['booking'])): ?>
                <div class="alert alert-warning">
                    Vui lòng hoàn tất bước đặt bàn trước khi chọn món. <a href="index.php?page=booking">Quay lại đặt bàn</a>.
                </div>
            <?php endif; ?>
            <div class="menu-container">
                <!-- Danh sách món ăn -->
                <div class="menu-left">
                    <form id="menu-filter-form" class="mb-4" onsubmit="event.preventDefault(); filterMenu();">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <select id="danhmuc" class="form-select">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach ($danhMucList as $dm): ?>
                                        <option value="<?= $dm['iddm'] ?>">
                                            <?= htmlspecialchars($dm['tendanhmuc']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="search" class="form-control" placeholder="Tìm kiếm món ăn...">
                                <button type="submit" class="btn btn-warning mt-2">Tìm</button>
                            </div>
                        </div>
                    </form>

                    <div id="menu-list">
                        <?php if (empty($monAnList)): ?>
                            <p>Không tìm thấy món ăn nào.</p>
                        <?php else: ?>
                            <?php foreach ($monAnList as $mon): ?>
                                <div class="menu-item">
                                    <img src="img/<?= htmlspecialchars($mon['hinhanh'] ?: 'default.jpg') ?>" alt="<?= htmlspecialchars($mon['tenmonan']) ?>">
                                    <div class="menu-item-details">
                                        <strong><?= htmlspecialchars($mon['tenmonan']) ?></strong><br>
                                        <?= htmlspecialchars($mon['mota'] ?: 'Không có mô tả') ?><br>
                                        Giá: <span class="DonGia"><?= number_format($mon['DonGia']) ?> VND</span>
                                    </div>
                                    <button class="btn btn-sm btn-warning" onclick="addMonAn(<?= $mon['idmonan'] ?>, '<?= addslashes($mon['tenmonan']) ?>', <?= $mon['DonGia'] ?>)">+</button>
                                    <button class="btn btn-sm btn-secondary" onclick="removeMonAn(<?= $mon['idmonan'] ?>)">−</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tóm tắt đơn hàng -->
                <div class="menu-right">
                    <h3>Tóm tắt đơn hàng</h3>
                    <table class="summary-table" id="order-summary">
                        <thead>
                            <tr>
                                <th>Món</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <p class="mt-3">Tổng tiền: <strong id="total-tien">0 VND</strong></p>
                    <div id="error-message" class="error"></div>
                    <form method="POST" action="index.php?page=confirm_booking">
                        <input type="hidden" name="selected_monan" id="selected_monan">
                        <button type="button" class="btn btn-secondary w-100 mb-2" onclick="window.location.href='index.php?page=booking&khuvuc=<?= isset($_SESSION['booking']['khuvuc']) ? urlencode($_SESSION['booking']['khuvuc']) : '' ?>&datetime=<?= isset($_SESSION['booking']['datetime']) ? urlencode($_SESSION['booking']['datetime']) : '' ?>&people_count=<?= isset($_SESSION['booking']['people_count']) ? urlencode($_SESSION['booking']['people_count']) : '' ?>'">Quay lại</button>
                        <button type="submit" class="btn btn-warning w-100" <?= !isset($_SESSION['booking']) ? 'disabled' : '' ?>>Tiếp theo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script>
        let selectedMonAn = [];

        function addMonAn(idmonan, tenmonan, DonGia) {
            const existing = selectedMonAn.find(m => m.idmonan === idmonan);
            if (existing) {
                existing.soluong++;
            } else {
                selectedMonAn.push({ idmonan, tenmonan, DonGia, soluong: 1 });
            }
            updateSummary();
            saveOrder();
        }

        function removeMonAn(idmonan) {
            const index = selectedMonAn.findIndex(m => m.idmonan === idmonan);
            if (index !== -1) {
                selectedMonAn[index].soluong--;
                if (selectedMonAn[index].soluong === 0) {
                    selectedMonAn.splice(index, 1);
                }
            }
            updateSummary();
            saveOrder();
        }

        function updateSummary() {
            const summary = document.querySelector('#order-summary tbody');
            let total = 0;
            summary.innerHTML = '';
            selectedMonAn.forEach(mon => {
                const subtotal = mon.DonGia * mon.soluong;
                total += subtotal;
                summary.innerHTML += `
                    <tr>
                        <td>${mon.tenmonan}</td>
                        <td>${mon.soluong}</td>
                        <td>${subtotal.toLocaleString()} VND</td>
                    </tr>`;
            });
            document.getElementById('total-tien').textContent = total.toLocaleString() + ' VND';
            document.getElementById('selected_monan').value = JSON.stringify(selectedMonAn);
        }

        function saveOrder() {
            fetch('index.php?page=update_order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'selected_monan=' + encodeURIComponent(JSON.stringify(selectedMonAn))
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    console.error('Error saving order:', data.message);
                }
            })
            .catch(error => console.error('Fetch error:', error));
        }

        // AJAX cho chọn danh mục và tìm kiếm
        function filterMenu() {
            const danhmuc = document.getElementById('danhmuc').value;
            const search = document.getElementById('search').value;
            const menuList = document.getElementById('menu-list');

            fetch('index.php?page=filter_menu', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'danhmuc=' + encodeURIComponent(danhmuc) + '&search=' + encodeURIComponent(search)
            })
            .then(response => response.text())
            .then(html => {
                menuList.innerHTML = html;
            })
            .catch(error => {
                console.error('Fetch error:', error);
                menuList.innerHTML = '<p>Lỗi khi tải danh sách món ăn.</p>';
            });
        }

        // Xử lý chọn danh mục
        document.getElementById('danhmuc').addEventListener('change', () => {
            document.getElementById('search').value = ''; // Reset thanh tìm kiếm
            filterMenu();
        });

        // Xử lý form submit (cả nút tìm và nhấn Enter)
        document.getElementById('menu-filter-form').addEventListener('submit', (e) => {
            e.preventDefault();
            filterMenu();
        });

        // Tải danh sách món đã chọn từ session (nếu có)
        window.addEventListener('DOMContentLoaded', () => {
            <?php if (isset($_SESSION['selected_monan'])): ?>
                selectedMonAn = <?php echo json_encode($_SESSION['selected_monan']); ?>;
                updateSummary();
            <?php endif; ?>
        });
    </script>
</body>
</html>