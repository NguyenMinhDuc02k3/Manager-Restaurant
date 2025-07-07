<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Kiểm tra trạng thái session trước khi gọi session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối DB
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>

<div class="container mb-5">
    <div style="overflow-x: auto; max-height: 100%; margin-top:20px;">
        <table class="table table-head-bg-primary table-hover ms-3 me-3">
            <thead>
                <tr>
                    <th scope="col">Mã hóa đơn</th>
                    <th scope="col">Tên khách hàng</th>
                    <th scope="col">Ngày </th>
                    <th scope="col">Tổng tiền</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php
                if ($conn) {
                    $str = "SELECT * FROM hoadon hd JOIN khachhang k ON hd.idKH = k.idKH";
                    $result = $conn->query($str);
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr class='row-clickable' data-idHD='{$row["idHD"]}' onmouseover=\"this.style.backgroundColor='rgb(39, 35, 35)'\" onmouseout=\"this.style.backgroundColor=''\" style='cursor: pointer;'>";
                            echo "<td>" . $row['idHD'] . "</td>";
                            echo "<td>" . $row['tenKH'] . "</td>";
                            echo "<td>" . $row['Ngay'] . "</td>";
                            echo "<td>" . number_format($row['TongTien'], 0, ',', '.') . "</td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Không có hóa đơn nào.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.row-clickable');
        rows.forEach(row => {
            row.addEventListener('click', function() {
                const idHD = this.getAttribute('data-idHD');
                window.location.href = `index.php?page=chitietHD&idHD=${idHD}`;
            });
        });
    });
</script>