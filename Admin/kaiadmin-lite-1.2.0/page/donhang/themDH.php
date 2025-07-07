<?php
// Thêm logic chặn URL
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set timezone to Vietnam

// Kiểm tra nếu là yêu cầu AJAX, chuyển hướng đến tệp xử lý AJAX riêng biệt
if (isset($_GET['action']) && $_GET['action'] == 'filter_monan') {
    include('ajax_filter_monan.php');
    exit;
}

// Tiếp tục với phần code thông thường
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id']) || !isset($_SESSION['vaitro_id'])) {
    header("Location: dangnhap.php");
    exit;
}

// Lấy quyền của nhân viên
$idnv = $_SESSION['nhanvien_id'];
$vaitro_id = $_SESSION['vaitro_id'];
$queryRole = "SELECT v.quyen FROM nhanvien n JOIN vaitro v ON n.idvaitro = v.idvaitro WHERE n.idnv = ?";
$stmt = mysqli_prepare($conn, $queryRole);
mysqli_stmt_bind_param($stmt, "i", $idnv);
mysqli_stmt_execute($stmt);
$resultRole = mysqli_stmt_get_result($stmt);
$roleData = mysqli_fetch_assoc($resultRole);
$permissions = $roleData && !empty($roleData['quyen']) ? explode(",", $roleData['quyen']) : [];

function hasPermission($perm, $permissions) {
    return in_array(trim($perm), array_map('trim', $permissions));
}

// Kiểm tra quyền thêm đơn hàng - Chỉ cho Nhân viên hoặc Quản lý
if (!hasPermission('Them don hang', $permissions)) {
    echo "<script>alert('Bạn không có quyền thêm đơn hàng!'); window.location.href='index.php';</script>";
    exit;
}
// Kết thúc logic chặn URL

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['page'])) {
    $page = 'themDH';
} else {
    $page = $_GET['page'];
}

// Khởi tạo biến để lưu trạng thái các trường
$tenKH = '';
$idKH = '';
$sdt = isset($_POST['sdt']) ? $_POST['sdt'] : '';

// Lấy ngày hiện tại để điền mặc định
$current_day = date('d');
$current_month = date('m');
$current_year = date('Y');
$day = isset($_POST['day']) ? $_POST['day'] : $current_day;
$month = isset($_POST['month']) ? $_POST['month'] : $current_month;
$year = isset($_POST['year']) ? $_POST['year'] : $current_year;
$soban = isset($_POST['soban']) ? $_POST['soban'] : '';
$sdt_error = '';

// Tạo ký hiệu và số dựa trên thời gian hiện tại
$kyHieu = date('y') . 'C' . date('m') . 'TBB';
$so = date('His');

// Tạo mã đơn hàng từ sự kết hợp ngày và ký hiệu
$maDonHang = date('dmy') . '-' . $so;

// Lấy danh sách danh mục
$danhMucList = [];
$sqlDanhMuc = "SELECT * FROM danhmuc";
$resultDanhMuc = mysqli_query($conn, $sqlDanhMuc);
if ($resultDanhMuc) {
    $danhMucList = mysqli_fetch_all($resultDanhMuc, MYSQLI_ASSOC);
}

// Lấy danh sách món ăn mặc định
$monAnList = [];
$danhMucSelected = isset($_GET['danhmuc']) ? $_GET['danhmuc'] : '';
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

// Thực hiện truy vấn để lấy danh sách món ăn mặc định khi trang được tải lần đầu
// Xác định truy vấn dựa vào bộ lọc
if (!empty($danhMucSelected) && !empty($searchKeyword)) {
    // Tìm kiếm món ăn theo tên và danh mục
    $sqlMonAn = "SELECT * FROM monan WHERE tenmonan LIKE ? AND iddm = ? AND TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
    $searchParam = "%$searchKeyword%";
    mysqli_stmt_bind_param($stmtMonAn, "si", $searchParam, $danhMucSelected);
} elseif (!empty($danhMucSelected)) {
    // Lấy món ăn theo danh mục
    $sqlMonAn = "SELECT * FROM monan WHERE iddm = ? AND TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
    mysqli_stmt_bind_param($stmtMonAn, "i", $danhMucSelected);
} elseif (!empty($searchKeyword)) {
    // Tìm kiếm món ăn theo tên
    $sqlMonAn = "SELECT * FROM monan WHERE tenmonan LIKE ? AND TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
    $searchParam = "%$searchKeyword%";
    mysqli_stmt_bind_param($stmtMonAn, "s", $searchParam);
} else {
    // Lấy tất cả món ăn mặc định
    $sqlMonAn = "SELECT * FROM monan WHERE TrangThai = 'active'";
    $stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
}

mysqli_stmt_execute($stmtMonAn);
$resultMonAn = mysqli_stmt_get_result($stmtMonAn);
if ($resultMonAn) {
    $monAnList = mysqli_fetch_all($resultMonAn, MYSQLI_ASSOC);
}
mysqli_stmt_close($stmtMonAn);

// Xử lý thêm đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['themDH'])) {
    $day = $_POST['day'] ?? '';
    $month = $_POST['month'] ?? '';
    $year = $_POST['year'] ?? '';
    $idban = $_POST['soban'] ?? '';
    $idKH = $_POST['idKH'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $monanData = isset($_POST['monan_data']) ? json_decode($_POST['monan_data'], true) : [];

    // Tạo mã đơn hàng mới
    $currentDate = date('dmy');
    $currentTime = date('His');
    $maDonHang = $currentDate . '-' . $currentTime;
    
    // Tạo ký hiệu mới (định dạng: yyCmmTBB)
    $kyHieu = date('y') . 'C' . date('m') . 'TBB';
    $so = $currentTime;

    // Kiểm tra dữ liệu đầu vào
    $errors = [];
    if (empty($idKH)) {
        $errors[] = 'Thiếu mã khách hàng (idKH). Vui lòng nhấn Tìm để xác nhận khách hàng.';
    } else {
        $sql_check_kh = "SELECT idKH FROM khachhang WHERE idKH = ?";
        $stmt_check_kh = mysqli_prepare($conn, $sql_check_kh);
        if ($stmt_check_kh) {
            mysqli_stmt_bind_param($stmt_check_kh, "i", $idKH);
            mysqli_stmt_execute($stmt_check_kh);
            $result_check_kh = mysqli_stmt_get_result($stmt_check_kh);
            if (mysqli_num_rows($result_check_kh) == 0) {
                $errors[] = "Mã khách hàng $idKH không tồn tại.";
            }
            mysqli_stmt_close($stmt_check_kh);
        } else {
            $errors[] = "Lỗi kiểm tra khách hàng: " . mysqli_error($conn);
        }
    }

    if (empty($idban)) {
        $errors[] = 'Vui lòng chọn số bàn.';
    } else {
        $sql_check_ban = "SELECT idban FROM ban WHERE idban = ?";
        $stmt_check_ban = mysqli_prepare($conn, $sql_check_ban);
        if ($stmt_check_ban) {
            mysqli_stmt_bind_param($stmt_check_ban, "i", $idban);
            mysqli_stmt_execute($stmt_check_ban);
            $result_check_ban = mysqli_stmt_get_result($stmt_check_ban);
            if (mysqli_num_rows($result_check_ban) == 0) {
                $errors[] = "Bàn $idban không tồn tại.";
            }
            mysqli_stmt_close($stmt_check_ban);
        } else {
            $errors[] = "Lỗi kiểm tra bàn: " . mysqli_error($conn);
        }
    }

    if (empty($day) || empty($month) || empty($year)) {
        $errors[] = 'Vui lòng chọn đầy đủ ngày, tháng, năm.';
    } else if (!checkdate($month, $day, $year)) {
        $errors[] = 'Ngày tháng năm không hợp lệ.';
    }

    if (empty($monanData)) {
        $errors[] = 'Vui lòng chọn ít nhất một món ăn.';
    }

    $hasValidMonan = false;
    $validMonan = [];
    $validSoluong = [];

    foreach ($monanData as $mon) {
        if (!isset($mon['idmonan']) || !isset($mon['soluong'])) {
            continue;
        }
        
        $idmonan = (int)$mon['idmonan'];
        $soluong = (int)$mon['soluong'];
        
        if ($idmonan > 0 && $soluong > 0) {
            $sql_check_monan = "SELECT idmonan FROM monan WHERE idmonan = ?";
            $stmt_check_monan = mysqli_prepare($conn, $sql_check_monan);
            if ($stmt_check_monan) {
                mysqli_stmt_bind_param($stmt_check_monan, "i", $idmonan);
                mysqli_stmt_execute($stmt_check_monan);
                $result_check_monan = mysqli_stmt_get_result($stmt_check_monan);
                if (mysqli_num_rows($result_check_monan) > 0) {
                    $hasValidMonan = true;
                    $validMonan[] = $idmonan;
                    $validSoluong[] = $soluong;
                } else {
                    $errors[] = "Món ăn ID {$idmonan} không tồn tại.";
                }
                mysqli_stmt_close($stmt_check_monan);
            } else {
                $errors[] = "Lỗi kiểm tra món ăn: " . mysqli_error($conn);
            }
        }
    }

    if (!$hasValidMonan) {
        $errors[] = 'Vui lòng chọn ít nhất một món ăn với số lượng hợp lệ.';
    }

    if (!empty($errors)) {
        $errorMessage = implode("\\n", array_map('addslashes', $errors));
        echo "<script>alert('$errorMessage');</script>";
    } else {
    // Định dạng ngày
    $ngayDatHang = sprintf("%04d-%02d-%02d %s", $year, $month, $day, date('H:i:s'));

    // Bắt đầu transaction
    mysqli_begin_transaction($conn);

    try {
            // Thêm vào bảng donhang
        $trangThai = 'Chờ xử lý';
            $sql_donhang = "INSERT INTO donhang (idKH, idban, NgayDatHang, TrangThai, MaDonHang, SoHoaDon) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_donhang = mysqli_prepare($conn, $sql_donhang);
        if (!$stmt_donhang) {
            throw new Exception("Lỗi chuẩn bị truy vấn donhang: " . mysqli_error($conn));
        }
            mysqli_stmt_bind_param($stmt_donhang, "iissss", $idKH, $idban, $ngayDatHang, $trangThai, $maDonHang, $so);
        if (!mysqli_stmt_execute($stmt_donhang)) {
            throw new Exception("Lỗi thêm đơn hàng: " . mysqli_stmt_error($stmt_donhang));
        }
        
        // Lấy idDH vừa tạo
        $idDH = mysqli_insert_id($conn);

            // Thêm vào bảng chitietdonhang
        $sql_chitiet = "INSERT INTO chitietdonhang (idDH, idmonan, SoLuong) VALUES (?, ?, ?)";
        $stmt_chitiet = mysqli_prepare($conn, $sql_chitiet);
        if (!$stmt_chitiet) {
            throw new Exception("Lỗi chuẩn bị truy vấn chitietdonhang: " . mysqli_error($conn));
        }

        for ($i = 0; $i < count($validMonan); $i++) {
                $idmonan = $validMonan[$i];
                $soluong_item = $validSoluong[$i];

            mysqli_stmt_bind_param($stmt_chitiet, "iii", $idDH, $idmonan, $soluong_item);
            if (!mysqli_stmt_execute($stmt_chitiet)) {
                throw new Exception("Lỗi thêm chi tiết đơn hàng (món $idmonan): " . mysqli_stmt_error($stmt_chitiet));
            }
        }

            // Đóng statement trước khi commit
        mysqli_stmt_close($stmt_chitiet);
        mysqli_stmt_close($stmt_donhang);

        // Commit transaction
        mysqli_commit($conn);
        
        echo "<script>alert('Thêm đơn hàng thành công'); window.location.href='index.php?page=dsdonhang';</script>";
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
            echo "<script>alert('Thêm đơn hàng thất bại: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

<div class="m-5">
  <h2 class="mb-4">Tạo đơn hàng mới</h2>
  
  <form id="donHangForm" action="" method="POST">
    <div class="row">
      <!-- Danh sách món ăn - Bên trái -->
      <div class="col-md-7">
        <div class="card">
          <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh sách món ăn</h5>
          </div>
          <div class="card-body">
            <!-- Form tìm kiếm -->
            <div class="mb-4">
              <div class="row">
                <div class="col-md-5">
                  <select id="danhmuc-filter" class="form-select">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($danhMucList as $dm): ?>
                      <option value="<?= $dm['iddm'] ?>" <?= ($danhMucSelected == $dm['iddm']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dm['tendanhmuc']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-7">
                  <div class="input-group">
                    <input type="text" id="search-monan" class="form-control" placeholder="Tìm kiếm món ăn..." 
                           value="<?= htmlspecialchars($searchKeyword) ?>">
                    <button type="button" id="search-btn" class="btn btn-primary">Tìm</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Danh sách món ăn -->
            <div id="monan-list" class="row">
              <?php if (empty($monAnList)): ?>
                <div class="col-12 text-center">
                  <p>Không tìm thấy món ăn nào.</p>
                </div>
              <?php else: ?>
                <?php foreach ($monAnList as $mon): ?>
                  <div class="col-md-6 mb-2">
                    <div class="card h-100 shadow-sm">
                      <div class="card-body p-2">
                        <div class="d-flex align-items-center">
                          <img src="<?= !empty($mon['hinhanh']) ? 'assets/img/' . htmlspecialchars($mon['hinhanh']) : 'assets/img/default-food.jpg' ?>" 
                               alt="<?= htmlspecialchars($mon['tenmonan']) ?>" 
                               class="rounded me-2" style="width: 45px; height: 45px; object-fit: cover;">
                          <div style="width: calc(100% - 120px); min-width: 0;">
                            <h6 class="card-title mb-0 text-truncate" title="<?= htmlspecialchars($mon['tenmonan']) ?>"><?= htmlspecialchars($mon['tenmonan']) ?></h6>
                            <span class="fw-bold text-primary small d-block text-truncate"><?= number_format($mon['DonGia'], 0, ',', '.') ?> VNĐ</span>
                          </div>
                          <div class="ms-auto" style="width: 75px; flex-shrink: 0;">
                            <div class="d-flex align-items-center justify-content-end">
                              <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1 me-1" style="width: 24px; height: 24px; line-height: 1; font-size: 10px;" 
                                      data-id="<?= $mon['idmonan'] ?>" 
                                      data-name="<?= htmlspecialchars($mon['tenmonan']) ?>" 
                                      data-price="<?= $mon['DonGia'] ?>"><i class="fas fa-minus"></i></button>
                              <span class="fw-bold px-1" id="qty-<?= $mon['idmonan'] ?>" style="min-width: 20px; text-align: center;">0</span>
                              <button type="button" class="btn btn-sm btn-outline-success rounded-circle p-1 ms-1" style="width: 24px; height: 24px; line-height: 1; font-size: 10px;" 
                                      data-id="<?= $mon['idmonan'] ?>" 
                                      data-name="<?= htmlspecialchars($mon['tenmonan']) ?>" 
                                      data-price="<?= $mon['DonGia'] ?>"><i class="fas fa-plus"></i></button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Thông tin đơn hàng - Bên phải -->
      <div class="col-md-5">
        <div class="card">
          <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Thông tin đơn hàng</h5>
          </div>
          <div class="card-body">
            <!-- Thông tin cơ bản -->
            <div class="mb-3">
              <div class="row mb-2">
                <div class="col-md-5">
                  <label for="day" class="form-label">Ngày đặt hàng:</label>
                </div>
                <div class="col-md-7">
                  <div class="d-flex">
                    <select id="day" name="day" class="form-select me-1" required>
                      <?php for ($i = 1; $i <= 31; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $day) ? 'selected' : '' ?>><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                    <select id="month" name="month" class="form-select me-1" required>
                      <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $month) ? 'selected' : '' ?>><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                    <select id="year" name="year" class="form-select" required>
                      <?php for ($i = 2020; $i <= 2030; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $year) ? 'selected' : '' ?>><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row mb-2">
                <div class="col-md-5">
                  <label for="soban" class="form-label">Số bàn:</label>
                </div>
                <div class="col-md-7">
                  <select class="form-select" name="soban" id="soban" required>
                    <option value="">Chọn bàn</option>
                    <?php
                    $sql = "SELECT idban, SoBan FROM ban";
                    $result = mysqli_query($conn, $sql);
                    $banList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    foreach ($banList as $ban): ?>
                      <option value="<?= $ban['idban'] ?>" <?= ($ban['idban'] == $soban) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ban['SoBan']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="row mb-2">
                <div class="col-md-5">
                  <label for="sdt" class="form-label">Số điện thoại KH:</label>
                </div>
                <div class="col-md-7">
                  <div class="input-group">
                    <input type="tel" name="sdt" id="sdt" class="form-control" placeholder="Nhập SĐT" 
                         pattern="[0-9]{10}" value="<?= htmlspecialchars($sdt) ?>" required>
                    <button type="button" id="search-customer-btn" class="btn btn-primary">Tìm</button>
                  </div>
                  <div id="sdt-error" class="text-danger mt-1" style="display: none;"></div>
                </div>
              </div>

              <div class="row mb-2">
                <div class="col-md-5">
                  <label for="tenKH" class="form-label">Tên khách hàng:</label>
                </div>
                <div class="col-md-7">
                  <input type="text" name="tenKH" id="tenKH" class="form-control" 
                         value="<?= htmlspecialchars($tenKH) ?>" readonly required>
                  <input type="hidden" name="idKH" id="idKH" value="<?= htmlspecialchars($idKH) ?>">
                </div>
              </div>
              
              <div class="row mb-2">
                <div class="col-md-5">
                  <label class="form-label">Mã đơn hàng:</label>
                </div>
                <div class="col-md-7">
                  <div class="form-control bg-light"><?= $maDonHang ?></div>
                  <input type="hidden" name="maDonHang" value="<?= $maDonHang ?>">
                  <input type="hidden" name="kyHieu" value="<?= $kyHieu ?>">
                  <input type="hidden" name="so" value="<?= $so ?>">
                </div>
              </div>
            </div>

            <!-- Danh sách món đã chọn -->
            <div class="mt-4">
              <h6 class="mb-3">Danh sách món đã chọn</h6>
              <div class="table-responsive">
                <table class="table table-bordered table-hover" id="selected-items">
                  <thead class="table-light">
                    <tr>
                      <th>Tên món</th>
                      <th style="width: 100px;">Số lượng</th>
                      <th style="width: 150px;">Đơn giá</th>
                      <th style="width: 150px;">Thành tiền</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr id="empty-cart">
                      <td colspan="4" class="text-center">Chưa có món ăn nào được chọn</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div class="mt-3 text-end">
                <h5>Tổng tiền: <span id="total-amount">0 VNĐ</span></h5>
              </div>
              
              <input type="hidden" name="monan_data" id="monan-data" value="[]">
              
              <div class="mt-4 d-flex justify-content-between">
                <a href="index.php?page=dsdonhang" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-success" name="themDH">Tạo đơn hàng</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo giỏ hàng
    let cartItems = [];
    
    // Xử lý tìm kiếm khách hàng bằng AJAX
    const searchCustomerBtn = document.getElementById('search-customer-btn');
    const sdtInput = document.getElementById('sdt');
    const sdtError = document.getElementById('sdt-error');
    const tenKHInput = document.getElementById('tenKH');
    const idKHInput = document.getElementById('idKH');
    
    searchCustomerBtn.addEventListener('click', function() {
        // Lấy số điện thoại
        const sdt = sdtInput.value.trim();
        
        // Kiểm tra số điện thoại
        if (!sdt) {
            sdtError.textContent = 'Vui lòng nhập số điện thoại';
            sdtError.style.display = 'block';
            return;
        }
        
        // Gửi yêu cầu AJAX để tìm khách hàng
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'page/donhang/ajax_search_customer.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        // Cập nhật thông tin khách hàng
                        tenKHInput.value = response.data.tenKH;
                        idKHInput.value = response.data.idKH;
                        sdtError.style.display = 'none';
                    } else {
                        // Hiển thị lỗi
                        sdtError.textContent = response.message;
                        sdtError.style.display = 'block';
                        tenKHInput.value = '';
                        idKHInput.value = '';
                    }
                } catch (e) {
                    console.error('Lỗi xử lý JSON:', e);
                    sdtError.textContent = 'Lỗi xử lý dữ liệu';
                    sdtError.style.display = 'block';
                }
            } else {
                sdtError.textContent = 'Lỗi kết nối tới máy chủ';
                sdtError.style.display = 'block';
            }
        };
        
        xhr.onerror = function() {
            sdtError.textContent = 'Lỗi kết nối tới máy chủ';
            sdtError.style.display = 'block';
        };
        
        // Gửi yêu cầu
        xhr.send(`sdt=${encodeURIComponent(sdt)}`);
    });
    
    // Xử lý nhấn Enter trong ô tìm kiếm khách hàng
    sdtInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchCustomerBtn.click();
        }
    });
    
    // Xử lý tìm kiếm món ăn
    const searchBtn = document.getElementById('search-btn');
    const searchInput = document.getElementById('search-monan');
    const danhmucFilter = document.getElementById('danhmuc-filter');
    const monanList = document.getElementById('monan-list');
    
    function filterMonAn() {
        const danhmuc = danhmucFilter.value;
        const search = searchInput.value.trim();
        
        // Hiển thị trạng thái đang tải
        monanList.innerHTML = '<div class="col-12 text-center"><p>Đang tải...</p></div>';
        
        // Sử dụng XMLHttpRequest để gửi yêu cầu AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'page/donhang/ajax_filter_monan.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Cập nhật danh sách món ăn
                monanList.innerHTML = xhr.responseText;
                
                // Đăng ký lại các sự kiện cho nút + và -
                attachEventHandlers();
                
                // Khôi phục số lượng từ giỏ hàng
                cartItems.forEach(item => {
                    const qtyElement = document.getElementById(`qty-${item.idmonan}`);
                    if (qtyElement) {
                        qtyElement.textContent = item.soluong;
                    }
                });
            } else {
                console.error('Lỗi khi tải danh sách món ăn:', xhr.statusText);
                monanList.innerHTML = '<div class="col-12 text-center"><p>Đã xảy ra lỗi khi tải món ăn.</p></div>';
            }
        };
        
        xhr.onerror = function() {
            console.error('Lỗi kết nối khi tải danh sách món ăn');
            monanList.innerHTML = '<div class="col-12 text-center"><p>Lỗi kết nối, vui lòng thử lại.</p></div>';
        };
        
        // Gửi yêu cầu với dữ liệu lọc
        xhr.send(`danhmuc=${encodeURIComponent(danhmuc)}&search=${encodeURIComponent(search)}`);
    }
    
    // Xử lý nút tìm kiếm món ăn
    searchBtn.addEventListener('click', filterMonAn);
    
    // Xử lý khi chọn danh mục
    danhmucFilter.addEventListener('change', function() {
        // Reset search input when category changes
        searchInput.value = '';
        filterMonAn();
    });
    
    // Xử lý khi nhấn Enter trong ô tìm kiếm món ăn
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterMonAn();
        }
    });
    
    // Xử lý tăng/giảm số lượng món ăn
    function attachEventHandlers() {
        // Nút tăng số lượng
        document.querySelectorAll('.btn-outline-success').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = parseInt(this.getAttribute('data-price'));
                
                // Tìm trong giỏ hàng
                const existingItem = cartItems.find(item => item.idmonan == id);
                
                if (existingItem) {
                    existingItem.soluong++;
                } else {
                    cartItems.push({
                        idmonan: id,
                        tenmonan: name,
                        dongia: price,
                        soluong: 1
                    });
                }
                
                // Cập nhật hiển thị
                document.getElementById(`qty-${id}`).textContent = existingItem ? existingItem.soluong : 1;
                updateCart();
            });
        });
        
        // Nút giảm số lượng
        document.querySelectorAll('.btn-outline-danger').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                // Tìm trong giỏ hàng
                const index = cartItems.findIndex(item => item.idmonan == id);
                
                if (index !== -1) {
                    cartItems[index].soluong--;
                    
                    // Nếu số lượng = 0, xóa khỏi giỏ hàng
                    if (cartItems[index].soluong <= 0) {
                        cartItems.splice(index, 1);
                        document.getElementById(`qty-${id}`).textContent = 0;
                    } else {
                        document.getElementById(`qty-${id}`).textContent = cartItems[index].soluong;
                    }
                    
                    updateCart();
                }
            });
        });
    }
    
    // Cập nhật hiển thị giỏ hàng
    function updateCart() {
        const tableBody = document.querySelector('#selected-items tbody');
        const emptyCartRow = document.getElementById('empty-cart');
        const totalAmountEl = document.getElementById('total-amount');
        let totalAmount = 0;
        
        // Ẩn/hiện dòng "Chưa có món ăn"
        if (cartItems.length > 0) {
            emptyCartRow.style.display = 'none';
        } else {
            emptyCartRow.style.display = '';
        }
        
        // Xóa tất cả các hàng hiện tại (trừ hàng empty-cart)
        document.querySelectorAll('#selected-items tbody tr:not(#empty-cart)').forEach(row => {
            row.remove();
        });
        
        // Thêm các món ăn đã chọn vào bảng
        cartItems.forEach(item => {
            const subtotal = item.dongia * item.soluong;
            totalAmount += subtotal;
            
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${item.tenmonan}</td>
                <td>${item.soluong}</td>
                <td>${parseInt(item.dongia).toLocaleString('vi-VN')} VNĐ</td>
                <td>${subtotal.toLocaleString('vi-VN')} VNĐ</td>
            `;
            
            tableBody.appendChild(newRow);
        });
        
        // Cập nhật tổng tiền
        totalAmountEl.textContent = totalAmount.toLocaleString('vi-VN') + ' VNĐ';
        
        // Cập nhật dữ liệu món ăn trong form
        document.getElementById('monan-data').value = JSON.stringify(cartItems);
    }
    
    // Xử lý gửi form
    document.getElementById('donHangForm').addEventListener('submit', function(e) {
        if (!e.submitter || e.submitter.name !== 'themDH') {
            return;
        }
        
        const tenKH = document.getElementById('tenKH').value;
        const idKH = document.getElementById('idKH').value;
        const soban = document.getElementById('soban').value;
        
        // Kiểm tra các trường bắt buộc
        let errorMessages = [];
        
        if (!tenKH || !idKH) {
            errorMessages.push('Vui lòng tìm kiếm khách hàng bằng số điện thoại.');
        }
        
        if (!soban) {
            errorMessages.push('Vui lòng chọn bàn.');
        }
        
        if (cartItems.length === 0) {
            errorMessages.push('Vui lòng chọn ít nhất một món ăn.');
        }
        
        if (errorMessages.length > 0) {
            e.preventDefault();
            alert(errorMessages.join('\n'));
        }
    });
    
    // Khởi tạo sự kiện
    attachEventHandlers();
});
</script>

<?php mysqli_close($conn); ?>