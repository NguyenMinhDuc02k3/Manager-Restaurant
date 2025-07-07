<?php
// Logic chặn URL
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

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

// Kiểm tra quyền xem đơn hàng
if (!hasPermission('Xem don hang', $permissions)) {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!'); window.location.href='index.php';</script>";
    exit();
}

// Kiểm tra vai trò quản lý (4) hoặc phục vụ (2)
$canUpdateOrderStatus = ($vaitro_id == 4 || $vaitro_id == 2);

error_reporting(0);
ini_set('display_errors', 0);
if (!isset($_GET['page'])) {
  $page = 'xemDH';
} else {
  $page = $_GET['page'];
}
if (isset($_GET['idDH'])) {
  $idDH = $_GET['idDH'];
}

// Kiểm tra nếu có thông báo trạng thái từ URL
$showPaymentSuccess = isset($_GET['trangthai']) && $_GET['trangthai'] === 'Đã thanh toán';

// Xử lý xóa nếu có yêu cầu
if (isset($_POST['delete_idDH']) && $canUpdateOrderStatus) {
  $delete_id = $_POST['delete_idDH'];
  
  // Kiểm tra trạng thái đơn hàng trước khi xóa
  $checkStmt = $conn->prepare("SELECT TrangThai FROM donhang WHERE idDH = ?");
  $checkStmt->bind_param("i", $delete_id);
  $checkStmt->execute();
  $checkResult = $checkStmt->get_result();
  
  if ($checkResult && $checkResult->num_rows > 0) {
    $orderStatus = $checkResult->fetch_assoc()['TrangThai'];
    
    // Chỉ cho phép xóa đơn hàng ở trạng thái "Chờ xử lý"
    if ($orderStatus == 'Chờ xử lý') {
      // Xóa chi tiết đơn hàng trước
      $deleteDetailStmt = $conn->prepare("DELETE FROM chitietdonhang WHERE idDH = ?");
      $deleteDetailStmt->bind_param("i", $delete_id);
      $deleteDetailStmt->execute();
      
      // Sau đó xóa đơn hàng
      $deleteOrderStmt = $conn->prepare("DELETE FROM donhang WHERE idDH = ?");
      $deleteOrderStmt->bind_param("i", $delete_id);
      $deleteOrderStmt->execute();
      
      echo "<script>
              alert('Xóa đơn hàng thành công!');
              window.location.href = 'index.php?page=dsdonhang';
            </script>";
    } else {
      echo "<script>
              alert('Không thể xóa đơn hàng này vì đã được xử lý!');
              window.location.reload();
            </script>";
    }
  } else {
    echo "<script>
            alert('Không tìm thấy đơn hàng cần xóa!');
            window.location.reload();
          </script>";
  }
  exit;
}

// Xử lý xác nhận (cập nhật trạng thái thành "Đang chuẩn bị")
if (isset($_POST['confirm_idDH']) && $canUpdateOrderStatus) {
  $idDH = $_POST['confirm_idDH'];
  $stmt = $conn->prepare("UPDATE donhang SET TrangThai = 'Đang chuẩn bị' WHERE idDH = ?");
  $stmt->bind_param("i", $idDH);
  $stmt->execute();
  echo "<script>
      alert('Xác nhận đơn hàng thành công!');
      window.location.href = 'index.php?page=dsdonhang&trangthai=Đang chuẩn bị';
    </script>";
  exit;
}

// Xử lý cập nhật trạng thái thành "Đã giao")
if (isset($_POST['giaohang']) && $canUpdateOrderStatus) {
  $idDH = $_POST['giaohang'];
  $stmt = $conn->prepare("UPDATE donhang SET TrangThai = 'Đã giao' WHERE idDH = ?");
  $stmt->bind_param("i", $idDH);
  $stmt->execute();
  echo "<script>
      alert('Giao đơn hàng thành công!');
      window.location.href = 'index.php?page=dsdonhang&trangthai=Đã giao';
    </script>";
  exit;
}
?>

<div class="m-5" style="font-family: 'Times New Roman', serif; font-size: 14px;">
  <!-- Thêm thông báo thanh toán thành công -->
  <?php if (isset($_GET['trangthai']) && $_GET['trangthai'] === 'Đã thanh toán'): ?>
  <div class="alert alert-success text-center mb-4" style="width: 80%; margin: 0 auto;">
    <h4><i class="fas fa-check-circle"></i> Thanh toán thành công!</h4>
    <p>Đơn hàng #<?php echo $idDH; ?> đã được thanh toán qua VNPay.</p>
    <?php
    // Lấy thông tin thanh toán từ session nếu có
    if (isset($_SESSION['vnpay_params'])) {
        $payment_info = $_SESSION['vnpay_params'];
        $vnp_Amount = isset($payment_info['vnp_Amount']) ? number_format(($payment_info['vnp_Amount'] / 100), 0, ',', '.') : '';
        $vnp_BankCode = isset($payment_info['vnp_BankCode']) ? $payment_info['vnp_BankCode'] : '';
        $vnp_TransactionNo = isset($payment_info['vnp_TransactionNo']) ? $payment_info['vnp_TransactionNo'] : '';
        
        if ($vnp_Amount) {
            echo '<p><strong>Số tiền:</strong> ' . $vnp_Amount . ' VND</p>';
        }
        if ($vnp_BankCode) {
            echo '<p><strong>Ngân hàng:</strong> ' . $vnp_BankCode . '</p>';
        }
        if ($vnp_TransactionNo) {
            echo '<p><strong>Mã giao dịch:</strong> ' . $vnp_TransactionNo . '</p>';
        }
        
        // Xóa thông tin thanh toán khỏi session sau khi đã hiển thị
        unset($_SESSION['vnpay_params']);
    }
    ?>
  </div>
  <?php endif; ?>

  <div style=" margin:100px 100px 30px 100px ; border: 2px solid black; box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3);">

    <div style="text-align: center; font-weight: bold; ">

      <h2 style="margin: 5px; padding:20px;">ĐƠN BÁN HÀNG </h2>
      <?php
      // Debug: Hiển thị ID đơn hàng
      echo "<p style='color:red;'>ID Đơn hàng: " . $idDH . "</p>";
      ?>
      <?php
      $ngay = "";
      $thang = "";
      $nam = "";
      $maDonHang = "";
      if ($conn) {
        try {
          // Kiểm tra xem bảng donhang có cột MaDonHang chưa
          $hasMaDonHang = false;
          $columnsResult = $conn->query("SHOW COLUMNS FROM donhang LIKE 'MaDonHang'");
          if ($columnsResult && $columnsResult->num_rows > 0) {
              $hasMaDonHang = true;
          }
          
          // Sử dụng prepared statement để tránh SQL injection
          if ($hasMaDonHang) {
              $str = "SELECT NgayDatHang, MaDonHang FROM donhang WHERE idDH = ?";
          } else {
              $str = "SELECT NgayDatHang FROM donhang WHERE idDH = ?";
          }
          
          $stmt = $conn->prepare($str);
          if (!$stmt) {
            echo "<p style='color:red;'>Lỗi chuẩn bị truy vấn: " . $conn->error . "</p>";
          } else {
            $stmt->bind_param("i", $idDH);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
              echo "<p style='color:red;'>Lỗi thực thi truy vấn: " . $stmt->error . "</p>";
            } else if ($result->num_rows == 0) {
              echo "<p style='color:red;'>Không tìm thấy thông tin đơn hàng với ID: " . $idDH . "</p>";
            } else {
              $row = $result->fetch_assoc();
              $ngayDatHang = $row["NgayDatHang"];
              $date = new DateTime($ngayDatHang);
              $ngay = $date->format('d');
              $thang = $date->format('m');
              $nam = $date->format('Y');
              
              if ($hasMaDonHang && !empty($row["MaDonHang"])) {
                $maDonHang = $row["MaDonHang"];
              } else {
                // Nếu không có MaDonHang, tạo mã đơn hàng tạm thời
                $maDonHang = "DH" . $idDH;
              }
            }
          }
        } catch (Exception $e) {
          echo "<p style='color:red;'>Lỗi xử lý: " . $e->getMessage() . "</p>";
        }
      }
      ?>
      <p>Ngày <strong><?php echo $ngay ?></strong> tháng <strong><?php echo $thang ?></strong> năm
        <strong><?php echo $nam ?></strong>
      </p>
    </div>

    <div style="float: right; padding-right: 20px;">
      <p><strong>Mã đơn hàng:</strong> <?php echo !empty($maDonHang) ? $maDonHang : "DH" . $idDH; ?></p>
    </div>

    <div style="clear: both;"></div>
    <div style="height: 1px; border: none; background-color: black; margin: 20px 0;"></div>

    <table style="border-collapse: collapse; margin: 10px; ">
      <tr>
        <td colspan="2" style="border: none; padding: 3px;"><strong>Người bán:</strong> Nhà hàng Restoran </td>
      </tr>

      <tr>
        <td colspan="2" style="border: none; padding: 3px;"><strong>Địa chỉ:</strong> 12 Nguyễn Văn Bảo,phường 4, quận
          Gò Vấp,
          TP.HCM
        </td>
      </tr>
      <tr>
        <td style="border: none; padding: 3px;"><strong>Điện thoại:</strong>0123456789</td>
      </tr>
    </table>
    <div style="height: 1px; border: none; background-color: black; margin: 20px 0;"></div>

    <table style="border-collapse: collapse; margin: 10px; ">
      <?php
      $tenKH = "";
      $sdt = "";
      $ban = "";
      if ($conn) {
        try {
          $str = "SELECT ban.SoBan, khachhang.tenKH, khachhang.sodienthoai 
                FROM donhang 
                JOIN ban ON donhang.idban = ban.idban 
                JOIN khachhang ON donhang.idKH = khachhang.idKH 
                WHERE donhang.idDH = ?";
          $stmt = $conn->prepare($str);
          if (!$stmt) {
            echo "<p style='color:red;'>Lỗi chuẩn bị truy vấn khách hàng: " . $conn->error . "</p>";
          } else {
            $stmt->bind_param("i", $idDH);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Debug: Hiển thị kết quả truy vấn
            if (!$result) {
                echo "<p style='color:red;'>Lỗi truy vấn khách hàng: " . $stmt->error . "</p>";
            } else if ($result->num_rows == 0) {
                echo "<p style='color:red;'>Không tìm thấy thông tin khách hàng với ID đơn hàng: " . $idDH . "</p>";
            } else {
              while ($row = mysqli_fetch_assoc($result)) {
                $tenKH = $row["tenKH"] ?? "Không có thông tin";
                $sdt = $row["sodienthoai"] ?? "Không có thông tin";
                $ban = $row["SoBan"] ?? "Không có thông tin";
              }
            }
          }
        } catch (Exception $e) {
          echo "<p style='color:red;'>Lỗi xử lý thông tin khách hàng: " . $e->getMessage() . "</p>";
        }
      }
      ?>
      <tr>
        <td style="border: none; padding: 3px;"><strong>Bàn :</strong> <?php echo $ban ?></td>
      </tr>
      <tr>
        <td style="border: none; padding: 3px;"><strong>Khách hàng :</strong> <?php echo $tenKH ?></td>
      </tr>
      <tr>
        <td style="border: none; padding: 3px;"><strong>Số điện thoại:</strong> <?php echo $sdt ?></td>
      </tr>


    </table>

    <br>

    <table class="table table-bordered" style="width: 95%;margin: auto">
      <thead style="border:1px solid black;">
        <tr>
          <th>STT</th>
          <th>Tên hàng hóa</th>
          <th>Đơn vị tính</th>
          <th>Số lượng</th>
          <th>Đơn giá</th>
          <th>Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $tongTien = 0;
        if ($conn) {

          // Đơn bán -> monan
          $sqlCT = "SELECT ma.tenmonan AS tenhang, ctdh.SoLuong, ma.DonGia, ma.DonViTinh 
                      FROM chitietdonhang ctdh JOIN monan ma ON ctdh.idmonan = ma.idmonan 
                      WHERE ctdh.idDH = ?";

          $stmtCT = $conn->prepare($sqlCT);
          if (!$stmtCT) {
            echo "<tr><td colspan='6' style='color:red;'>Lỗi chuẩn bị truy vấn chi tiết: " . $conn->error . "</td></tr>";
            $resultCT = false;
          } else {
            $stmtCT->bind_param("i", $idDH);
            $stmtCT->execute();
            $resultCT = $stmtCT->get_result();
          }
          $stt = 1;
          
          // Debug: Kiểm tra truy vấn chi tiết đơn hàng
          if (!$resultCT) {
              echo "<tr><td colspan='6' style='color:red;'>Lỗi truy vấn chi tiết: " . $conn->error . "</td></tr>";
          } else if ($resultCT->num_rows == 0) {
              echo "<tr><td colspan='6' style='color:red;'>Không tìm thấy chi tiết đơn hàng</td></tr>";
          }

          if ($resultCT && $resultCT->num_rows > 0) {
            while ($row = $resultCT->fetch_assoc()) {
              $thanhTien = $row['SoLuong'] * $row['DonGia'];
              $tongTien += $thanhTien;
              echo "<tr>";
              echo "<td>" . $stt++ . "</td>";
              echo "<td>" . $row['tenhang'] . "</td>";
              echo "<td>" . $row['DonViTinh'] . "</td>";
              echo "<td>" . $row['SoLuong'] . "</td>";
              echo "<td>" . number_format($row['DonGia'], 0, ',', '.') . "</td>";
              echo "<td>" . number_format($thanhTien, 0, ',', '.') . "</td>";
              echo "</tr>";

            }
          }
        }
        ?>
        <tr>
          <td colspan="5" style="padding: 5px; text-align: right;"><strong>Tổng tiền:</strong></td>
          <td style="padding: 5px; text-align: right;">
            <strong><?php echo number_format($tongTien, 0, ',', '.') ?></strong>
          </td>
        </tr>
        <tr>

      </tbody>
    </table>

    <br><br>

    <table style="width: 100%;">
      <tr>
        <td style="text-align: center; border: none;">
          <strong>Người mua hàng</strong><br>(Chữ ký số nếu có)
        </td>
        <td style="text-align: center; border: none;">
          <strong>Người bán hàng</strong><br>(Chữ ký điện tử, chữ ký số)
        </td>
      </tr>
    </table>

    <p style="text-align: center; font-style: italic;">(Cần kiểm tra, đối chiếu khi lập, nhận hóa đơn)</p>
    <p colspan="6" style="padding: 5px; text-align: center;"><strong>Xin cảm ơn và hẹn gặp lại quý khách!</strong>
    </p>
  </div>
  <div style="text-align: right; margin-right:120px;">
    <?php
    // Hiển thị thông báo thanh toán thành công nếu có tham số từ URL
    if ($showPaymentSuccess && (isset($_SESSION['payment_success']) || isset($_SESSION['vnpay_params']))) {
      // Lấy thông tin thanh toán từ session nếu có
      $payment_method = isset($_SESSION['payment_method']) ? $_SESSION['payment_method'] : 'vnpay';
      
      // Nếu không có trong session, thử lấy từ database
      if (empty($payment_method) && !empty($phuongThucThanhToan)) {
        if (strtolower($phuongThucThanhToan) === 'tiền mặt' || strtolower($phuongThucThanhToan) === 'tien mat') {
          $payment_method = 'cash';
        } else {
          $payment_method = 'vnpay';
        }
      }
      
      if ($payment_method === 'cash') {
        // Hiển thị thông báo thanh toán tiền mặt
        $payment_amount = isset($_SESSION['payment_amount']) ? number_format($_SESSION['payment_amount'], 0, ',', '.') : '';
        
        echo '<div class="alert alert-success mt-3 mb-3" style="text-align: center; width: 80%; margin: 0 auto; padding: 15px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background-color: #d4edda; border-color: #c3e6cb;">
                <h4 style="color: #155724; margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Thanh toán thành công!</h4>
                <p style="color: #155724; margin-bottom: 5px;">Đơn hàng #'.$idDH.' đã được thanh toán bằng tiền mặt.</p>';
        
        if ($payment_amount) {
          echo '<p style="color: #155724; margin-bottom: 5px;"><strong>Số tiền:</strong> '.$payment_amount.' VND</p>';
        }
        
        echo '</div>';
        
        // Xóa thông tin thanh toán khỏi session sau khi đã hiển thị
        unset($_SESSION['payment_method']);
        unset($_SESSION['payment_amount']);
        unset($_SESSION['payment_success']);
      } else {
        // Hiển thị thông báo thanh toán VNPay (code hiện tại)
        $payment_info = isset($_SESSION['vnpay_params']) ? $_SESSION['vnpay_params'] : [];
        $vnp_Amount = isset($payment_info['vnp_Amount']) ? number_format(($payment_info['vnp_Amount'] / 100), 0, ',', '.') : '';
        $vnp_BankCode = isset($payment_info['vnp_BankCode']) ? $payment_info['vnp_BankCode'] : '';
        $vnp_TransactionNo = isset($payment_info['vnp_TransactionNo']) ? $payment_info['vnp_TransactionNo'] : '';
        
        echo '<div class="alert alert-success mt-3 mb-3" style="text-align: center; width: 80%; margin: 0 auto; padding: 15px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background-color: #d4edda; border-color: #c3e6cb;">
                <h4 style="color: #155724; margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Thanh toán thành công!</h4>
                <p style="color: #155724; margin-bottom: 5px;">Đơn hàng #'.$idDH.' đã được thanh toán qua VNPay.</p>';
        
        if ($vnp_Amount) {
          echo '<p style="color: #155724; margin-bottom: 5px;"><strong>Số tiền:</strong> '.$vnp_Amount.' VND</p>';
        }
        
        if ($vnp_BankCode) {
          echo '<p style="color: #155724; margin-bottom: 5px;"><strong>Ngân hàng:</strong> '.$vnp_BankCode.'</p>';
        }
        
        if ($vnp_TransactionNo) {
          echo '<p style="color: #155724; margin-bottom: 5px;"><strong>Mã giao dịch:</strong> '.$vnp_TransactionNo.'</p>';
        }
        
        echo '</div>';
        
        // Xóa thông tin thanh toán khỏi session sau khi đã hiển thị
        unset($_SESSION['vnpay_params']);
      }
    }
    
    // Lấy trạng thái từ bảng donhang thay vì từ $_GET
    $trangthai = '';
    $phuongThucThanhToan = '';
    if ($conn) {
      $str = "SELECT d.TrangThai, 
              (SELECT h.hinhthucthanhtoan FROM hoadon h WHERE h.idDH = d.idDH ORDER BY h.idHD DESC LIMIT 1) AS PhuongThucThanhToan 
              FROM donhang d WHERE d.idDH = ?";
      $stmt = $conn->prepare($str);
      $stmt->bind_param("i", $idDH);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $trangthai = $row['TrangThai'];
        $phuongThucThanhToan = $row['PhuongThucThanhToan'];
      }
    }
    
    // Chỉ hiển thị nút cập nhật trạng thái cho quản lý (4) và phục vụ (2)
    if ($canUpdateOrderStatus) {
      if ($trangthai == 'Chờ xử lý') {
        echo "<form method='POST' style='display:inline;'>
                  <input type='hidden' name='confirm_idDH' value='$idDH'>
                  <button type='submit' class='btn btn-primary btn-sm' style='height:50px;'>
                          <p style='color:white; font-size:17px'>Xác nhận</p>
                  </button>
              </form>
              <button 
              type='button' 
              class='btn btn-danger btn-sm btn-delete' 
              style='height:50px;'
              data-idDH='$idDH' 
              data-bs-toggle='modal' 
              data-bs-target='#deleteModal'>
              <p style='color:white; font-size:17px'>Xóa</p>
              </button>";
      } else if ($trangthai == 'Đang chuẩn bị') {
        echo "<form method='POST' style='display:inline;'>
                  <input type='hidden' name='giaohang' value='$idDH'>
                  <button type='submit' class='btn btn-primary btn-sm' style='height:50px;'>
                          <p style='color:white; font-size:17px'>Giao hàng</p>
                  </button>
              </form>";
      }
    }
    
    if ($trangthai == 'Đã giao') {
      // Chỉ hiển thị nút Xuất hóa đơn cho người có quyền thanh toán
      if (hasPermission('Thanh toan don hang', $permissions)) {
        echo "<a href='index.php?page=thanhtoan/payment&idDH=$idDH' class='btn btn-primary btn-sm' style='height:50px; margin-right: 5px;'>
                <p style='color:white; font-size:17px'>Thanh toán</p>
            </a>";
      }
      if (hasPermission('Sua don hang', $permissions)) {
        echo "<a href='index.php?page=suaDH&idDH=$idDH' class='btn btn-warning btn-sm' style='height:50px;'>
              <p style='color:white; font-size:17px'>Sửa</p>
          </a>";
      }
    } else if ($trangthai == 'Đã thanh toán') {
      // Hiển thị thông tin phương thức thanh toán
      $phuongThucHienThi = "";
      if (!empty($phuongThucThanhToan)) {
        if (strtolower($phuongThucThanhToan) === 'tiền mặt' || strtolower($phuongThucThanhToan) === 'tien mat') {
          $phuongThucHienThi = " (Tiền mặt)";
        } else if (strtolower($phuongThucThanhToan) === 'chuyển khoản' || strtolower($phuongThucThanhToan) === 'chuyen khoan') {
          $phuongThucHienThi = " (Chuyển khoản)";
        } else {
          $phuongThucHienThi = " (" . $phuongThucThanhToan . ")";
        }
      }
      
      echo "<a href='index.php?page=thanhtoan/payment_history&idDH=$idDH' class='btn btn-info btn-sm' style='height:50px; margin-right: 5px;'>
              <p style='color:white; font-size:17px'>Lịch sử thanh toán" . $phuongThucHienThi . "</p>
          </a>";
    }
    ?>
  </div>
</div>
<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Xác nhận xóa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <p>Bạn có chắc muốn xóa đơn hàng này không?</p>
          <input type="hidden" name="delete_idDH" id="delete_idDH">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-danger">Xóa</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Script xử lý xác nhận và nhấp vào dòng -->
<script>
  // Xử lý nút xóa
  document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteInput = document.getElementById('delete_idDH');

    deleteButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const idDH = btn.getAttribute('data-idDH');
        deleteInput.value = idDH;
      });
    });
  });
</script>