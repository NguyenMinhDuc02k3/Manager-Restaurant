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

// Thiết lập UTF-8 cho kết nối
mysqli_set_charset($conn, "utf8mb4");

// Xử lý xoá nếu có yêu cầu
if (isset($_POST['delete_idKH'])) {
    $idKH = $_POST['delete_idKH'];
    $stmt = $conn->prepare("DELETE FROM khachhang WHERE idKH = ?");
    $stmt->bind_param("i", $idKH);
    $stmt->execute();
    echo "<script>
            window.location.reload(); // Tự động tải lại trang
            alert('Xóa khách hàng thành công!'); // Thông báo xóa thành công
          </script>";
    exit;
}
?>

<!-- Thêm meta charset UTF-8 -->
<meta charset="UTF-8">

<div class="container mb-5">
<div class="mt-4">
        <div class="d-flex align-items-center justify-content-end mb-3 pe-5">
            <a href="index.php?page=themkh" class="d-flex align-items-center text-decoration-none">
                <p class="mb-0 me-2"><b>Thêm</b></p>
                <i class="icon-user-follow fs-4"></i>
            </a>
        </div>
    </div>

    <div style="overflow-x: auto; max-height: 100%">
        <table class="table table-head-bg-primary ms-3 me-3 ">
            <thead>
                <tr>
                    <th scope="col">Mã khách hàng </th>
                    <th scope="col">Tên khách hàng </th>
                    <th scope="col">Số điện thoại</th>
                    <th scope="col">Email</th>
                    <th scope="col">Ngày sinh </th>
                    <th scope="col">Giới tính</th>
                    <th scope="col">Tùy chọn</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($conn) {
                    $str = "SELECT * FROM khachhang";
                    $result = $conn->query($str);
                    if ($result->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['idKH'] . "</td>";
                            echo "<td>" . $row['tenKH'] . "</td>";
                            echo "<td>" . $row['sodienthoai'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['ngaysinh'] . "</td>";
                            echo "<td>" . $row['gioitinh'] . "</td>";
                            echo "<td>
                                <a href='index.php?page=suakh&idKH={$row["idKH"]}' class='btn btn-warning btn-sm'>
                                    <i class='fas fa-pencil-alt' style='color:white; font-size:17px'></i>
                                </a>
                                <button 
                                    type='button' 
                                    class='btn btn-danger btn-sm btn-delete' 
                                    data-idKH='{$row["idKH"]}' 
                                    data-hoten='{$row["tenKH"]}'
                                    data-bs-toggle='modal' 
                                    data-bs-target='#deleteModal'>
                                    <i class='fas fa-trash-alt' style='color:white; font-size:17px'></i>
                                </button>
                            </td>";
                            echo "</tr>";
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="deleteForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xoá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmText">Bạn có chắc muốn xoá khách hàng này?</p>
                    <input type="hidden" name="delete_idKH" id="delete_idKH">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-danger">Xoá</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script xử lý xác nhận -->
<script>
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const confirmText = document.getElementById('confirmText');
    const deleteInput = document.getElementById('delete_idKH');
    const deleteForm = document.getElementById('deleteForm'); // Lấy form xóa

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const idKH = btn.getAttribute('data-idKH');
            const hoten = btn.getAttribute('data-hoten');
            confirmText.textContent = `Bạn có chắc muốn xoá khách hàng "${hoten}" không?`;
            deleteInput.value = idKH;
        });
    });

    // Thêm sự kiện submit form khi người dùng nhấn "Xoá" trong modal
    deleteForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Ngăn chặn việc gửi form mặc định
        const formData = new FormData(deleteForm);
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).then(response => {
            // Thông báo xóa thành công và tải lại trang
            alert('Xóa khách hàng thành công!');
            window.location.reload(); // Sau khi xóa thành công, reload trang
        });
    });
</script>