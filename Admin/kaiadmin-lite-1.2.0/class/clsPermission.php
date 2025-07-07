<?php
class Permission {
    private $conn;
    private $permissions = [];
    private $user_id;
    private $role_id;

    public function __construct($conn) {
        $this->conn = $conn;
        if (isset($_SESSION['nhanvien_id'])) {
            $this->user_id = $_SESSION['nhanvien_id'];
            $this->role_id = $_SESSION['vaitro_id'];
            $this->loadPermissions();
        }
    }

    private function loadPermissions() {
        $query = "SELECT v.quyen 
                 FROM nhanvien n 
                 JOIN vaitro v ON n.idvaitro = v.idvaitro 
                 WHERE n.idnv = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $this->user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $roleData = mysqli_fetch_assoc($result);
        
        if ($roleData && !empty($roleData['quyen'])) {
            $this->permissions = array_map('trim', explode(",", $roleData['quyen']));
        }
    }

    public function hasPermission($permission) {
        // Nếu là quản lý (role_id = 4) thì có tất cả quyền
        if ($this->role_id == 4) {
            return true;
        }
        return in_array(trim($permission), $this->permissions);
    }

    public function checkAccess($page, $action = 'view') {
        // Danh sách các trang và quyền tương ứng
        $pagePermissions = [
            'dsnhanvien' => [
                'view' => 'Xem nhan vien',
                'add' => 'Them nhan vien',
                'edit' => 'Sua nhan vien',
                'delete' => 'Xoa nhan vien'
            ],
            'dskhachhang' => [
                'view' => 'Xem khach hang',
                'add' => 'Them khach hang',
                'edit' => 'Sua khach hang',
                'delete' => 'Xoa khach hang'
            ],
            'dsmonan' => [
                'view' => 'Xem mon an',
                'add' => 'Them mon an',
                'edit' => 'Sua mon an',
                'delete' => 'Xoa mon an'
            ],
            'dsdonhang' => [
                'view' => 'Xem don hang',
                'add' => 'Them don hang',
                'edit' => 'Sua don hang',
                'delete' => 'Xoa don hang'
            ],
            'dshoadon' => [
                'view' => 'Xem hoa don',
                'add' => 'Them hoa don',
                'edit' => 'Sua hoa don',
                'delete' => 'Xoa hoa don'
            ],
            'phanquyen' => [
                'view' => 'Phan quyen',
                'add' => 'Phan quyen',
                'edit' => 'Phan quyen',
                'delete' => 'Phan quyen'
            ]
        ];

        // Nếu là quản lý thì cho phép truy cập tất cả
        if ($this->role_id == 4) {
            return true;
        }

        // Kiểm tra quyền cho trang và action cụ thể
        if (isset($pagePermissions[$page]) && isset($pagePermissions[$page][$action])) {
            return $this->hasPermission($pagePermissions[$page][$action]);
        }

        // Nếu trang không có trong danh sách kiểm tra, mặc định cho phép truy cập
        return true;
    }

    public function getMenuItems() {
        $menuItems = [
            [
                'title' => 'Nhân viên',
                'icon' => 'fas icon-people',
                'url' => 'index.php?page=dsnhanvien',
                'permission' => 'Xem nhan vien'
            ],
            [
                'title' => 'Khách hàng',
                'icon' => 'fas fa-address-card',
                'url' => 'index.php?page=dskhachhang',
                'permission' => 'Xem khach hang'
            ],
            [
                'title' => 'Món ăn',
                'icon' => 'fas fa-utensils',
                'url' => 'index.php?page=dsmonan',
                'permission' => 'Xem mon an'
            ],
            [
                'title' => 'Đơn hàng',
                'icon' => 'fas fa-pen-square',
                'url' => 'index.php?page=dsdonhang',
                'permission' => 'Xem don hang'
            ],
            [
                'title' => 'Hóa đơn',
                'icon' => 'fas fa-align-right',
                'url' => 'index.php?page=dshoadon',
                'permission' => 'Xem hoa don'
            ],
            [
                'title' => 'Tồn kho',
                'icon' => 'fas icon-layers',
                'url' => 'index.php?page=dstonkho',
                'permission' => 'Xem ton kho'
            ],
            [
                'title' => 'Phân quyền',
                'icon' => 'fas icon-wrench',
                'url' => 'index.php?page=phanquyen',
                'permission' => 'Phan quyen',
                'role_required' => 4
            ]
        ];

        $filteredMenu = [];
        foreach ($menuItems as $item) {
            if (isset($item['role_required'])) {
                if ($this->role_id == $item['role_required']) {
                    $filteredMenu[] = $item;
                }
            } else if ($this->hasPermission($item['permission'])) {
                $filteredMenu[] = $item;
            }
        }

        return $filteredMenu;
    }
}
?> 