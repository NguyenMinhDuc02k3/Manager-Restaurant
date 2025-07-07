<?php
require_once 'clsconnect.php';
class datban extends connect_db
{
    public function getBanDaDat($maKhuVuc, $datetime) {
        $db = new connect_db();
        
        // Chuyển đổi datetime thành timestamp
        $bookingTime = strtotime($datetime);
        
        // Tính thời gian 2 tiếng trước và sau
        $twoHoursBefore = date('Y-m-d H:i:s', $bookingTime - 7200);
        $twoHoursAfter = date('Y-m-d H:i:s', $bookingTime + 7200);
        
        $sql = "SELECT DISTINCT d.idban 
                FROM datban d 
                JOIN ban b ON d.idban = b.idban 
                WHERE b.makv = ? 
                AND d.NgayDatBan BETWEEN ? AND ?
                AND d.TrangThai != 'cancelled'";
                
        $result = $db->xuatdulieu_prepared($sql, [$maKhuVuc, $twoHoursBefore, $twoHoursAfter]);
        
        $dsBanDaDat = [];
        foreach ($result as $row) {
            $dsBanDaDat[] = $row['idban'];
        }
        
        return $dsBanDaDat;
    }
    
    public function getBanTheoKhuVuc($maKhuVuc) {
        $sql = "SELECT idban, SoBan, MaKV FROM ban WHERE MaKV = ?";
        $params = [(int)$maKhuVuc];
        $result = $this->xuatdulieu_prepared($sql, $params);
        return is_array($result) ? $result : [];
    }

    public function checkAvailableTimeSlot($idban, $datetime) {
        $db = new connect_db();
        
        // Chuyển đổi datetime thành timestamp
        $bookingTime = strtotime($datetime);
        
        // Tính thời gian 2 tiếng trước và sau
        $twoHoursBefore = date('Y-m-d H:i:s', $bookingTime - 7200); // 2 tiếng = 7200 giây
        $twoHoursAfter = date('Y-m-d H:i:s', $bookingTime + 7200);
        
        // Kiểm tra xem có đặt bàn nào trong khoảng thời gian này không
        $sql = "SELECT * FROM datban 
                WHERE idban = ? 
                AND NgayDatBan BETWEEN ? AND ?
                AND TrangThai != 'cancelled'";
                
        $result = $db->xuatdulieu_prepared($sql, [$idban, $twoHoursBefore, $twoHoursAfter]);
        
        if (empty($result)) {
            return true; // Không có đặt bàn nào trong khoảng thời gian này
        }
        
        return false; // Đã có đặt bàn trong khoảng thời gian này
    }
    
    // Thêm phương thức lưu thông tin đặt bàn với cấu trúc mới
    public function saveDatBan($idban, $ngayDatBan, $soLuongKhach, $tongTien, $tenKH, $email, $soDienThoai) {
        $db = new connect_db();
        
        error_log("saveDatBan - Start saving booking with params: idban=$idban, date=$ngayDatBan, people=$soLuongKhach, total=$tongTien, name=$tenKH, email=$email");
        
        $sql = "INSERT INTO datban (idban, NgayDatBan, SoLuongKhach, TongTien, TrangThai, tenKH, email, sodienthoai) 
                VALUES (?, ?, ?, ?, 'confirmed', ?, ?, ?)";
                
        $params = [(int)$idban, $ngayDatBan, (int)$soLuongKhach, (float)$tongTien, $tenKH, $email, $soDienThoai];
        
        try {
            $result = $db->tuychinh($sql, $params);
            
            if ($result) {
                $lastId = $db->getLastInsertId();
                error_log("saveDatBan - Successfully saved booking with ID: $lastId");
                return $lastId;
            } else {
                error_log("saveDatBan - Failed to save booking");
                return false;
            }
        } catch (Exception $e) {
            error_log("saveDatBan - Exception: " . $e->getMessage());
            return false;
        }
    }
}
?>