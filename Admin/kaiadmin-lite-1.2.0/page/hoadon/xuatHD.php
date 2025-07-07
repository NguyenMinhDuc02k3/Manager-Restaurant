<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set timezone to Vietnam

if (!isset($_GET['page'])) {
    $page = 'xuatHD';
} else {
    $page = $_GET['page'];
}

if (!isset($_GET['idDH']) || !is_numeric($_GET['idDH'])) {
    die("Lỗi: Thiếu hoặc idDH không hợp lệ.");
}
$idDH = (int) $_GET['idDH'];

$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra trạng thái đơn hàng
$trangThaiDonHang = "";
if ($conn) {
    $trangThaiQuery = $conn->prepare("SELECT TrangThai FROM donhang WHERE idDH = ?");
    $trangThaiQuery->bind_param("i", $idDH);
    $trangThaiQuery->execute();
    $trangThaiResult = $trangThaiQuery->get_result();
    if ($trangThaiResult->num_rows > 0) {
        $trangThaiRow = $trangThaiResult->fetch_assoc();
        $trangThaiDonHang = $trangThaiRow['TrangThai'];
    }
}

// Kiểm tra xem đơn hàng này đã có hóa đơn chưa
$daCoHoaDon = false;
if ($conn) {
    $hoaDonQuery = $conn->prepare("SELECT idHD FROM hoadon WHERE idDH = ?");
    $hoaDonQuery->bind_param("i", $idDH);
    $hoaDonQuery->execute();
    $hoaDonResult = $hoaDonQuery->get_result();
    if ($hoaDonResult->num_rows > 0) {
        $daCoHoaDon = true;
    }
}
?>

<div class="m-5" style="font-family: 'Times New Roman', serif; font-size: 14px;">
    <div style="margin:100px 100px 30px 100px; border: 2px solid black; box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3);">
        <div style="text-align: center; font-weight: bold;">
            <h2 style="margin: 5px; padding:20px;">HÓA ĐƠN BÁN HÀNG</h2>
            <?php
            $ngay = "";
            $thang = "";
            $nam = "";
            $gio = "";
            $phut = "";
            if ($conn) {
                $stmt = $conn->prepare("SELECT NgayDatHang FROM donhang WHERE idDH = ?");
                $stmt->bind_param("i", $idDH);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $ngayDatHang = $row["NgayDatHang"];
                    $date = new DateTime($ngayDatHang);
                    $ngay = $date->format('d');
                    $thang = $date->format('m');
                    $nam = $date->format('Y');
                    $gio = $date->format('H');
                    $phut = $date->format('i');
                }
            }
            ?>
            <p>Ngày <strong><?php echo htmlspecialchars($ngay); ?></strong> tháng
                <strong><?php echo htmlspecialchars($thang); ?></strong> năm
                <strong><?php echo htmlspecialchars($nam); ?></strong>
                - <strong><?php echo htmlspecialchars($gio); ?>:<?php echo htmlspecialchars($phut); ?></strong>
            </p>
        </div>

        <div style="float: right; padding-right: 20px;">
            <p><strong>Ký hiệu:</strong> 2C21TBB</p>
            <p><strong>Số:</strong> 98723</p>
        </div>

        <div style="clear: both;"></div>
        <div style="height: 1px; border: none; background-color: black; margin: 20px 0;"></div>

        <table style="border-collapse: collapse; margin: 10px;">
            <tr>
                <td colspan="2" style="border: none; padding: 3px;"><strong>Người bán:</strong> Nhà hàng Restoran</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; padding: 3px;"><strong>Địa chỉ:</strong> 12 Nguyễn Văn Bảo, phường
                    4, quận Gò Vấp, TP.HCM</td>
            </tr>
            <tr>
                <td style="border: none; padding: 3px;"><strong>Điện thoại:</strong> 0123456789</td>
            </tr>
        </table>
        <div style="height: 1px; border: none; background-color: black; margin: 20px 0;"></div>

        <table style="border-collapse: collapse; margin: 10px;">
            <?php
            $tenKH = '';
            $sdt = '';
            $ban = '';
            if ($conn) {
                $stmt = $conn->prepare("SELECT ban.SoBan, khachhang.tenKH, khachhang.sodienthoai 
                                        FROM donhang 
                                        JOIN ban ON donhang.idban = ban.idban 
                                        JOIN khachhang ON donhang.idKH = khachhang.idKH 
                                        WHERE donhang.idDH = ?");
                $stmt->bind_param("i", $idDH);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $tenKH = $row["tenKH"];
                    $sdt = $row["sodienthoai"];
                    $ban = $row["SoBan"];
                }
            }
            ?>
            <tr>
                <td style="border: none; padding: 3px;"><strong>Bàn:</strong> <?php echo htmlspecialchars($ban); ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 3px;"><strong>Khách hàng:</strong>
                    <?php echo htmlspecialchars($tenKH); ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 3px;"><strong>Số điện thoại:</strong>
                    <?php echo htmlspecialchars($sdt); ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 3px;"><strong>Hình thức thanh toán:</strong> 
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="idDH" value="<?php echo $idDH; ?>">
                        <select name="hinhthucthanhtoan">
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Chuyển khoản">Chuyển khoản</option>
                        </select>
                </td>
            </tr>
        </table>

        <br>

        <table class="table table-bordered" style="width: 95%; margin: auto">
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
                $chiTietMonAn = []; // Mảng lưu chi tiết món ăn để sử dụng khi lưu
                if ($conn) {
                    $sqlCT = "SELECT ma.idmonan, ma.tenmonan AS tenhang, ctdh.SoLuong, ma.DonGia, ma.DonViTinh 
                              FROM chitietdonhang ctdh 
                              JOIN monan ma ON ctdh.idmonan = ma.idmonan 
                              WHERE ctdh.idDH = ?";
                    $stmt = $conn->prepare($sqlCT);
                    $stmt->bind_param("i", $idDH);
                    $stmt->execute();
                    $resultCT = $stmt->get_result();
                    $stt = 1;

                    if ($resultCT && $resultCT->num_rows > 0) {
                        while ($row = $resultCT->fetch_assoc()) {
                            $thanhTien = $row['SoLuong'] * $row['DonGia'];
                            $tongTien += $thanhTien;

                            // Lưu chi tiết món ăn vào mảng
                            $chiTietMonAn[] = [
                                'idmonan' => $row['idmonan'],
                                'soluong' => $row['SoLuong'],
                                'thanhtien' => $thanhTien
                            ];

                            echo "<tr>";
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['tenhang']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['DonViTinh']) . "</td>";
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
        <p style="padding: 5px; text-align: center;"><strong>Xin cảm ơn và hẹn gặp lại quý khách!</strong></p>
    </div>

    <div style="text-align: right; margin-right:120px;">
        <?php if ($trangThaiDonHang != 'Đã thanh toán' && !$daCoHoaDon): ?>
            <button type="submit" class="btn btn-success" name="luuHD">Lưu</button>
        </form>
        <a href="index.php?page=xemDH&idDH=<?php echo urlencode($idDH); ?>" class="btn btn-danger">Hủy</a>
        <?php else: ?>
            </form>
            <a href="index.php?page=xemDH&idDH=<?php echo urlencode($idDH); ?>" class="btn btn-primary">Quay lại</a>
        <?php endif; ?>
    </div>
</div>

<?php
if (isset($_POST['luuHD'])) {
    // Kiểm tra nếu đơn hàng đã thanh toán hoặc đã có hóa đơn thì không lưu
    if ($trangThaiDonHang == 'Đã thanh toán' || $daCoHoaDon) {
        echo "<script>
                alert('Đơn hàng này đã được thanh toán và có hóa đơn!');
                window.location.href = 'index.php?page=xemDH&idDH=$idDH';
              </script>";
        exit;
    }
    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->begin_transaction();

    try {
        // Lấy idKH và NgayDatHang từ donhang
        $stmt = $conn->prepare("SELECT idKH, NgayDatHang FROM donhang WHERE idDH = ?");
        $stmt->bind_param("i", $idDH);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("Không tìm thấy đơn hàng.");
        }
        $donhang = $result->fetch_assoc();
        $idKH = $donhang['idKH'];
        
        // Sử dụng thời gian hiện tại thay vì ngày từ đơn hàng
        $ngay = date('Y-m-d H:i:s');

        // Lấy hình thức thanh toán từ form
        $hinhthucthanhtoan = isset($_POST['hinhthucthanhtoan']) ? $_POST['hinhthucthanhtoan'] : 'Tiền mặt';

        // Thêm vào bảng hoadon (bao gồm hinhthucthanhtoan)
        $stmt = $conn->prepare("INSERT INTO hoadon (idDH, idKH, ngay, TongTien, hinhthucthanhtoan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $idDH, $idKH, $ngay, $tongTien, $hinhthucthanhtoan);
        $stmt->execute();
        $idHD = $conn->insert_id; // Lấy idHD vừa tạo

        // Thêm vào bảng chitiethoadon (sử dụng $chiTietMonAn đã lưu)
        $stmt_insert_cthd = $conn->prepare("INSERT INTO chitiethoadon (idHD, idmonan, soluong, thanhtien) VALUES (?, ?, ?, ?)");
        foreach ($chiTietMonAn as $mon) {
            $stmt_insert_cthd->bind_param("iiid", $idHD, $mon['idmonan'], $mon['soluong'], $mon['thanhtien']);
            $stmt_insert_cthd->execute();
        }

        // Cập nhật TrangThai trong donhang
        $stmt = $conn->prepare("UPDATE donhang SET TrangThai = 'Đã thanh toán' WHERE idDH = ? AND TrangThai = 'Đã giao'");
        $stmt->bind_param("i", $idDH);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            throw new Exception("Đơn hàng không ở trạng thái 'Đã giao' hoặc không tồn tại.");
        }

        // Commit transaction
        $conn->commit();
        echo "<script>
                alert('Thêm hóa đơn thành công!');
                window.location.href = 'index.php?page=dshoadon';
              </script>";
        exit;
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        echo "<script>
                alert('Thêm hóa đơn thất bại: " . addslashes($e->getMessage()) . "');
                window.location.href = 'index.php?page=xuatHD&idDH=$idDH';
              </script>";
        exit;
    }
}
?>