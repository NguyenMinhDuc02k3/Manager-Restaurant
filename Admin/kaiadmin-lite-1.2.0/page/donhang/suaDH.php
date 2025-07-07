<?php
// Thêm logic chặn URL
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set timezone to Vietnam
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id']) || !isset($_SESSION['vaitro_id'])) {
    echo "<script>window.location.href='../../page/dangnhap.php';</script>";
    exit;
}

// Lấy quyền
$idnv = $_SESSION['nhanvien_id'];
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

// Kiểm tra quyền Sửa đơn hàng và vai trò Thu ngân (2) hoặc Quản lý (4)
if (!hasPermission('Sua don hang', $permissions) ) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}
// Kết thúc logic chặn URL

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy ID đơn hàng từ URL
if (!isset($_GET['idDH'])) {
    echo "<script>alert('Không tìm thấy đơn hàng'); window.location.href='index.php?page=dsdonhang';</script>";
    exit;
}
$idDH = intval($_GET['idDH']);

// Lấy thông tin đơn hàng và khách hàng
$donhangInfo = null;
$khachhangInfo = null;
$banInfo = null;

// Lấy thông tin đơn hàng từ database
$sqlDonHang = "SELECT d.*, b.SoBan, k.tenKH, k.sodienthoai 
               FROM donhang d 
               JOIN ban b ON d.idban = b.idban 
               JOIN khachhang k ON d.idKH = k.idKH 
               WHERE d.idDH = ?";
$stmtDonHang = mysqli_prepare($conn, $sqlDonHang);
mysqli_stmt_bind_param($stmtDonHang, "i", $idDH);
mysqli_stmt_execute($stmtDonHang);
$resultDonHang = mysqli_stmt_get_result($stmtDonHang);

if (mysqli_num_rows($resultDonHang) == 0) {
    echo "<script>alert('Không tìm thấy thông tin đơn hàng'); window.location.href='index.php?page=dsdonhang';</script>";
    exit;
}

$donhangInfo = mysqli_fetch_assoc($resultDonHang);
$ngayDatHang = new DateTime($donhangInfo['NgayDatHang']);
$day = $ngayDatHang->format('d');
$month = $ngayDatHang->format('m');
$year = $ngayDatHang->format('Y');
$hour = $ngayDatHang->format('H');
$minute = $ngayDatHang->format('i');
$soban = $donhangInfo['idban'];
$idKH = $donhangInfo['idKH'];
$tenKH = $donhangInfo['tenKH'];
$sdt = $donhangInfo['sodienthoai'];
$trangThai = $donhangInfo['TrangThai'];

// Kiểm tra nếu đơn hàng đã thanh toán thì không cho sửa
if ($trangThai == 'Đã thanh toán') {
    echo "<script>alert('Đơn hàng đã thanh toán không thể sửa!'); window.location.href='index.php?page=dsdonhang';</script>";
    exit;
}

// Lấy danh sách món ăn trong đơn hàng
$monanList = [];
$sqlMonAn = "SELECT c.idmonan, c.SoLuong, m.tenmonan, m.DonGia 
            FROM chitietdonhang c 
            JOIN monan m ON c.idmonan = m.idmonan 
            WHERE c.idDH = ?";
$stmtMonAn = mysqli_prepare($conn, $sqlMonAn);
mysqli_stmt_bind_param($stmtMonAn, "i", $idDH);
mysqli_stmt_execute($stmtMonAn);
$resultMonAn = mysqli_stmt_get_result($stmtMonAn);

while ($monan = mysqli_fetch_assoc($resultMonAn)) {
    $monanList[] = $monan;
}

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capNhatDH'])) {
    // Bắt đầu transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Lấy dữ liệu từ form
        $idban = $_POST['soban'] ?? $soban;
        $monan_ids = $_POST['monan'] ?? [];
        $soluong_values = $_POST['soluong'] ?? [];
        
        // Cập nhật thông tin đơn hàng
        $sql_update = "UPDATE donhang SET idban = ?, TrangThai = 'Đang chuẩn bị' WHERE idDH = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ii", $idban, $idDH);
        
        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception("Lỗi cập nhật đơn hàng: " . mysqli_stmt_error($stmt_update));
        }
        
        // Xóa chi tiết đơn hàng cũ
        $sql_delete = "DELETE FROM chitietdonhang WHERE idDH = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $idDH);
        
        if (!mysqli_stmt_execute($stmt_delete)) {
            throw new Exception("Lỗi xóa chi tiết đơn hàng cũ: " . mysqli_stmt_error($stmt_delete));
        }
        
        // Thêm chi tiết đơn hàng mới
        $sql_chitiet = "INSERT INTO chitietdonhang (idDH, idmonan, SoLuong) VALUES (?, ?, ?)";
        $stmt_chitiet = mysqli_prepare($conn, $sql_chitiet);
        
        // Kiểm tra dữ liệu món ăn hợp lệ
        $hasValidMonan = false;
        
        for ($i = 0; $i < count($monan_ids); $i++) {
            if (!empty($monan_ids[$i]) && is_numeric($soluong_values[$i]) && $soluong_values[$i] > 0) {
                $idmonan = (int)$monan_ids[$i];
                $soluong_item = (int)$soluong_values[$i];
                
                mysqli_stmt_bind_param($stmt_chitiet, "iii", $idDH, $idmonan, $soluong_item);
                if (!mysqli_stmt_execute($stmt_chitiet)) {
                    throw new Exception("Lỗi thêm chi tiết đơn hàng (món $idmonan): " . mysqli_stmt_error($stmt_chitiet));
                }
                
                $hasValidMonan = true;
            }
        }
        
        if (!$hasValidMonan) {
            throw new Exception("Vui lòng chọn ít nhất một món ăn với số lượng hợp lệ.");
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo "<script>alert('Cập nhật đơn hàng thành công'); window.location.href='index.php?page=dsdonhang&trangthai=Đang chuẩn bị';</script>";
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Cập nhật đơn hàng thất bại: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?page=suaDH&idDH=$idDH';</script>";
    }
}

?>

<div class="m-5" style="font-family: 'Times New Roman', serif; font-size: 14px;">
  <form id="donHangForm" action="" method="POST" enctype="multipart/form-data">
    <div style="margin:100px 50px 30px 50px; border: 2px solid black; box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3);">
      <div>
        <div style="text-align: center; font-weight: bold;">
          <h2 style="margin: 5px; padding:20px;">SỬA ĐƠN HÀNG</h2>
          <p>
            Ngày <strong><?php echo $day; ?></strong> 
            tháng <strong><?php echo $month; ?></strong> 
            năm <strong><?php echo $year; ?></strong>
            - <strong><?php echo $hour; ?>:<?php echo $minute; ?></strong>
          </p>
        </div>

        <div style="float: right; padding-right: 20px;">
          <?php if (isset($donhangInfo['MaDonHang']) && !empty($donhangInfo['MaDonHang'])): ?>
            <p><strong>Mã đơn hàng:</strong> <?php echo $donhangInfo['MaDonHang']; ?></p>
          <?php else: ?>
            <p><strong>Ký hiệu:</strong> <?php echo $donhangInfo['KyHieu'] ?? '2C21TBB'; ?></p>
            <p><strong>Số:</strong> <?php echo $donhangInfo['SoHoaDon'] ?? '98723'; ?></p>
          <?php endif; ?>
        </div>

        <div style="clear: both;"></div>
        <div style="height: 1px; border: none; background-color: black; margin: 20px 0;"></div>

        <table style="border-collapse: collapse; margin: 10px;">
          <tr>
            <td colspan="2" style="border: none; padding: 3px;"><strong>Người bán:</strong> Nhà hàng Restoran</td>
          </tr>
          <tr>
            <td colspan="2" style="border: none; padding: 3px;"><strong>Địa chỉ:</strong> 12 Nguyễn Văn Bảo, phường 4,
              quận Gò Vấp, TP.HCM</td>
          </tr>
          <tr>
            <td style="border: none; padding: 3px;"><strong>Điện thoại:</strong>0123456789</td>
          </tr>
        </table>

        <div style="height: 1px; border: none; background-color: black; margin: 20px 0;"></div>

        <table style="border-collapse: collapse; margin: 10px;">
          <tr>
            <td style="border: none; padding: 5px; width:50px;"><strong>Bàn:</strong>
              <select class="form-selected" name="soban" style="padding:5px; margin-left: 10px;" required>
                <?php
                $sql = "SELECT idban, SoBan FROM ban";
                $result = mysqli_query($conn, $sql);
                $banList = mysqli_fetch_all($result, MYSQLI_ASSOC);
                foreach ($banList as $ban) {
                    $selected = ($ban['idban'] == $soban) ? 'selected' : '';
                    echo '<option value="' . $ban['idban'] . '" ' . $selected . '>' . htmlspecialchars($ban['SoBan']) . '</option>';
                }
                ?>
              </select>
            </td>
          </tr>
          <tr>
            <td style="border: none; padding: 3px;width:1000px">
              <div style="display: flex; align-items: center;">
                <strong>Khách hàng:</strong>
                <input type="text" name="tenKH" id="tenKH" 
                  style="padding: 5px; margin-left: 10px;" value="<?php echo htmlspecialchars($tenKH); ?>" readonly>
                <input type="hidden" name="idKH" id="idKH" value="<?php echo htmlspecialchars($idKH); ?>">
              </div>
            </td>
          </tr>
          <tr>
            <td style="border: none; padding: 3px;width:1000px">
              <strong>Số điện thoại:</strong>
              <span style="padding: 5px;margin-left: 10px;"><?php echo htmlspecialchars($sdt); ?></span>
            </td>
          </tr>
          <tr>
            <td style="border: none; padding: 3px;">
              <strong>Trạng thái hiện tại:</strong>
              <span style="padding: 5px;margin-left: 10px; font-weight: bold; color: <?php echo $trangThai == 'Chờ xử lý' ? 'orange' : 'green'; ?>">
                <?php echo htmlspecialchars($trangThai); ?>
              </span>
            </td>
          </tr>
        </table>

        <br>

        <table class="table table-bordered" style="width: 95%; margin: auto" id="monan-table">
          <thead style="border:1px solid black;">
            <tr>
              <th>STT</th>
              <th style="width: 37%;">Tên món ăn</th>
              <th style="width: 18%;">Số lượng</th>
              <th>Đơn giá</th>
              <th>Thành tiền</th>
              <th style="width: 7%;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            if (count($monanList) > 0) {
                foreach ($monanList as $index => $monan) {
                    $stt = $index + 1;
                    $thanhtien = $monan['SoLuong'] * $monan['DonGia'];
                    echo "<tr>
                        <td class='stt'>$stt</td>
                        <td>
                          <select name='monan[]' class='form-control'>
                            <option value=''>Chọn món</option>";
                    
                    // Lấy tất cả món ăn
                    $sqlAllMon = "SELECT * FROM monan";
                    $resAllMon = $conn->query($sqlAllMon);
                    while ($row = $resAllMon->fetch_assoc()) {
                        $selected = ($row['idmonan'] == $monan['idmonan']) ? 'selected' : '';
                        echo "<option value='{$row['idmonan']}' data-dongia='{$row['DonGia']}' $selected>{$row['tenmonan']}</option>";
                    }
                    
                    echo "</select>
                        </td>
                        <td><input type='number' name='soluong[]' class='form-control' value='{$monan['SoLuong']}' min='1'></td>
                        <td class='dongia'>" . number_format($monan['DonGia'], 0, '.', '.') . "</td>
                        <td class='thanhtien'>" . number_format($thanhtien, 0, '.', '.') . "</td>
                        <td><button type='button'
                            class='btn btn-danger btn-sm remove d-flex align-items-center justify-content-center'
                            style='width:35px; height:35px;'>
                            <i class='fas fa-trash-alt' style='color:white;'></i>
                          </button></td>
                      </tr>";
                }
            } else {
                // Thêm một hàng trống nếu không có món ăn
                echo "<tr>
                    <td class='stt'>1</td>
                    <td>
                      <select name='monan[]' class='form-control'>
                        <option value=''>Chọn món</option>";
                
                $sqlMon = "SELECT * FROM monan";
                $resMon = $conn->query($sqlMon);
                while ($row = $resMon->fetch_assoc()) {
                    echo "<option value='{$row['idmonan']}' data-dongia='{$row['DonGia']}'>{$row['tenmonan']}</option>";
                }
                
                echo "</select>
                    </td>
                    <td><input type='number' name='soluong[]' class='form-control' value='1' min='1'></td>
                    <td class='dongia'>0</td>
                    <td class='thanhtien'>0</td>
                    <td><button type='button'
                        class='btn btn-danger btn-sm remove d-flex align-items-center justify-content-center'
                        style='width:35px; height:35px;'>
                        <i class='fas fa-trash-alt' style='color:white;'></i>
                      </button></td>
                  </tr>";
            }
            ?>
          </tbody>
        </table>
        <div style="text-align: right; padding: 10px 30px;">
          <button type="button" id="addRow" class="btn btn-primary">Thêm món</button>
        </div>
        <div style="text-align: left; padding: 10px 30px; font-size: 20px;">
          <strong>Tổng tiền: </strong><span id="tongTien">0</span> VNĐ
        </div>

        <br><br>

        <p style="text-align: center; font-style: italic;">(Cần kiểm tra, đối chiếu khi lập, nhận hóa đơn)</p>
        <p style="padding: 5px; text-align: center;"><strong>Xin cảm ơn và hẹn gặp lại quý khách!</strong></p>
      </div>
    </div>
    <div style="text-align: right; margin-right: 50px; margin-top: 20px">
      <button type="submit" class="btn btn-success" name="capNhatDH">Lưu</button>
      <a href="index.php?page=dsdonhang" class="btn btn-secondary">Quay lại</a>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateTongTien();
    
    document.getElementById('donHangForm').addEventListener('submit', function(e) {
        if (!e.submitter || e.submitter.name !== 'capNhatDH') {
            return;
        }
        
        const soban = document.querySelector('select[name="soban"]').value;
        const monan = document.querySelectorAll('select[name="monan[]"]');
        const soluong = document.querySelectorAll('input[name="soluong[]"]');
        
        let hasValidMonan = false;
        const monanErrors = [];
        monan.forEach(function(select, index) {
            if (!soluong[index]) {
                monanErrors.push(`Hàng ${index + 1}: Thiếu trường số lượng.`);
                return;
            }
            const soluongValue = parseInt(soluong[index].value);
            if (select.value !== '' && !isNaN(soluongValue) && soluongValue > 0) {
                hasValidMonan = true;
            } else if (select.value !== '' && (isNaN(soluongValue) || soluongValue <= 0)) {
                monanErrors.push(`Hàng ${index + 1}: Số lượng phải là số nguyên dương.`);
            }
        });
        
        const errors = [];
        
        if (!soban) {
            errors.push('Vui lòng chọn số bàn.');
        }
        
        if (!hasValidMonan) {
            errors.push('Vui lòng chọn ít nhất một món ăn với số lượng hợp lệ.');
        }
        if (monanErrors.length > 0) {
            errors.push(...monanErrors);
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join('\n'));
            return false;
        }
        
        if (!confirm('Xác nhận cập nhật đơn hàng? Trạng thái sẽ chuyển thành "Đang chuẩn bị".')) {
            e.preventDefault();
        }
    });
    
    document.getElementById('addRow').addEventListener('click', function () {
        const table = document.querySelector('#monan-table tbody');
        const newRow = table.rows[0].cloneNode(true);
        const index = table.rows.length + 1;
        newRow.querySelector('.stt').textContent = index;
        newRow.querySelector('select').value = "";
        newRow.querySelector('input').value = 1;
        newRow.querySelector('.dongia').textContent = "0";
        newRow.querySelector('.thanhtien').textContent = "0";
        table.appendChild(newRow);
        updateEventListeners();
    });
    
    updateEventListeners();
});

function updateEventListeners() {
    document.querySelectorAll('select[name="monan[]"]').forEach((select) => {
        select.addEventListener('change', function () {
            const dongia = parseInt(this.options[this.selectedIndex].getAttribute('data-dongia')) || 0;
            const row = this.closest('tr');
            row.querySelector('.dongia').textContent = dongia.toLocaleString('vi-VN');
            updateThanhTien(row);
        });
    });

    document.querySelectorAll('input[name="soluong[]"]').forEach((input) => {
        input.addEventListener('input', function () {
            updateThanhTien(this.closest('tr'));
        });
    });

    document.querySelectorAll('.remove').forEach(btn => {
        btn.addEventListener('click', function () {
            if (document.querySelectorAll('#monan-table tbody tr').length > 1) {
                this.closest('tr').remove();
                updateTongTien();
                document.querySelectorAll('#monan-table tbody tr').forEach((row, index) => {
                    row.querySelector('.stt').textContent = index + 1;
                });
            } else {
                alert('Phải có ít nhất một món ăn.');
            }
        });
    });
}

function updateThanhTien(row) {
    const soluong = parseInt(row.querySelector('input[name="soluong[]"]').value) || 1;
    const dongiaText = row.querySelector('.dongia').textContent.replace(/\./g, '');
    const dongia = parseInt(dongiaText) || 0;
    const thanhtien = soluong * dongia;
    row.querySelector('.thanhtien').textContent = thanhtien.toLocaleString('vi-VN');
    updateTongTien();
}

function updateTongTien() {
    let total = 0;
    document.querySelectorAll('.thanhtien').forEach(td => {
        const value = parseInt(td.textContent.replace(/\./g, '')) || 0;
        total += value;
    });
    document.getElementById('tongTien').textContent = total.toLocaleString('vi-VN');
}
</script>

<?php
mysqli_close($conn);
?>