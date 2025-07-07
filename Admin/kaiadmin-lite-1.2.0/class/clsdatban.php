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
                AND d.TrangThai != 'cancelled'
                AND d.TrangThaiThanhToan != 'failed'";
                
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
                AND TrangThai != 'cancelled'
                AND TrangThaiThanhToan != 'failed'";
                
        $result = $db->xuatdulieu_prepared($sql, [$idban, $twoHoursBefore, $twoHoursAfter]);
        
        if (empty($result)) {
            return true; // Không có đặt bàn nào trong khoảng thời gian này
        }
        
        return false; // Đã có đặt bàn trong khoảng thời gian này
    }
}
?>