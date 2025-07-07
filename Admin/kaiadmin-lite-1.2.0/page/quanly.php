<?php
// Thiết lập mã hóa UTF-8 (Bỏ header vì gây lỗi)
// Sử dụng meta tag trong HTML để thiết lập charset

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Kết nối CSDL
$conn = mysqli_connect('localhost', 'hceeab2b55_chung9atm', 'Chung2002!', 'hceeab2b55_restaurant');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Thêm thư viện Chart.js
?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<?php
// Hàm kiểm tra quyền
function hasPermission($perm, $permissions) {
    return in_array(trim($perm), array_map('trim', $permissions));
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id']) || !isset($_SESSION['vaitro_id'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='index.php?page=dangnhap';</script>";
    exit;
}

// Lấy quyền của nhân viên hiện tại
$idnv = $_SESSION['nhanvien_id'];
$queryRole = "SELECT v.quyen FROM nhanvien n JOIN vaitro v ON n.idvaitro = v.idvaitro WHERE n.idnv = ?";
$stmt = mysqli_prepare($conn, $queryRole);
mysqli_stmt_bind_param($stmt, "i", $idnv);
mysqli_stmt_execute($stmt);
$resultRole = mysqli_stmt_get_result($stmt);
$roleData = mysqli_fetch_assoc($resultRole);
$permissions = $roleData && !empty($roleData['quyen']) ? explode(",", $roleData['quyen']) : [];

// Kiểm tra quyền "Xem trang chủ"
if (!hasPermission('Xem trang chu', $permissions)) {
    die(""); // Trả về trang trắng nếu không có quyền
}

// Lấy tổng doanh thu
$queryTotalRevenue = "SELECT SUM(h.TongTien) as total 
                     FROM hoadon h 
                     JOIN donhang d ON h.idDH = d.idDH 
                     WHERE d.TrangThai = 'Đã thanh toán'";
$resultTotalRevenue = mysqli_query($conn, $queryTotalRevenue);
$totalRevenue = mysqli_fetch_assoc($resultTotalRevenue)['total'] ?? 0;

// Lấy tổng số khách hàng
$queryTotalCustomers = "SELECT COUNT(*) as total FROM khachhang";
$resultTotalCustomers = mysqli_query($conn, $queryTotalCustomers);
$totalCustomers = mysqli_fetch_assoc($resultTotalCustomers)['total'] ?? 0;

// Lấy tổng số đơn hàng
$queryTotalOrders = "SELECT COUNT(*) as total FROM donhang WHERE TrangThai = 'Đã thanh toán'";
$resultTotalOrders = mysqli_query($conn, $queryTotalOrders);
$totalOrders = mysqli_fetch_assoc($resultTotalOrders)['total'] ?? 0;

// Lấy tổng số nhân viên
$queryTotalEmployees = "SELECT COUNT(*) as total FROM nhanvien";
$resultTotalEmployees = mysqli_query($conn, $queryTotalEmployees);
$totalEmployees = mysqli_fetch_assoc($resultTotalEmployees)['total'] ?? 0;

// Lấy 10 khách hàng thân thiết nhất (có nhiều hóa đơn nhất)
$queryLoyalCustomers = "SELECT k.*, COUNT(d.idDH) as total_orders
                       FROM khachhang k
                       JOIN donhang d ON k.idKH = d.idKH
                       WHERE d.TrangThai = 'Đã thanh toán'
                       GROUP BY k.idKH
                       ORDER BY total_orders DESC
                       LIMIT 10";
$resultLoyalCustomers = mysqli_query($conn, $queryLoyalCustomers);

// Lấy doanh thu theo ngày trong tháng hiện tại
$queryMonthlyRevenue = "SELECT 
    DATE(h.Ngay) as date,
    SUM(h.TongTien) as total
FROM hoadon h
JOIN donhang d ON h.idDH = d.idDH
WHERE d.TrangThai = 'Đã thanh toán'
AND MONTH(h.Ngay) = MONTH(CURRENT_DATE())
AND YEAR(h.Ngay) = YEAR(CURRENT_DATE())
GROUP BY DATE(h.Ngay)
ORDER BY date ASC";
$resultMonthlyRevenue = mysqli_query($conn, $queryMonthlyRevenue);
$monthlyData = [];
while($row = mysqli_fetch_assoc($resultMonthlyRevenue)) {
    $monthlyData[] = $row;
}

// Lấy doanh thu theo ngày
$queryDailyRevenue = "SELECT 
    DATE(h.Ngay) as date,
    SUM(h.TongTien) as total
FROM hoadon h
JOIN donhang d ON h.idDH = d.idDH
WHERE d.TrangThai = 'Đã thanh toán'
AND DATE(h.Ngay) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(h.Ngay)
ORDER BY date DESC";
$resultDailyRevenue = mysqli_query($conn, $queryDailyRevenue);
$dailyData = [];
while($row = mysqli_fetch_assoc($resultDailyRevenue)) {
    $dailyData[] = $row;
}

// Lấy số đơn hàng theo ngày
$queryDailyOrders = "SELECT 
    DATE(NgayDatHang) as date,
    COUNT(*) as total
FROM donhang 
WHERE TrangThai = 'Đã thanh toán'
AND DATE(NgayDatHang) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(NgayDatHang)
ORDER BY date DESC";
$resultDailyOrders = mysqli_query($conn, $queryDailyOrders);
$dailyOrdersData = [];
while($row = mysqli_fetch_assoc($resultDailyOrders)) {
    $dailyOrdersData[] = $row;
}
?>

  <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h1 class="fw-bold mb-3">Báo cáo</h1>
            </div>
            <!-- <div class="ms-md-auto py-2 py-md-0">
              <a href="#" class="btn btn-label-info btn-round me-2">Manage</a>
              <a href="#" class="btn btn-primary btn-round">Add Customer</a>
            </div> -->
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-primary bubble-shadow-small">
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Khách hàng</p>
                        <h4 class="card-title"><?php echo number_format($totalCustomers); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-success bubble-shadow-small">
                        <i class="fas fa-luggage-cart"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Doanh thu</p>
                        <h4 class="card-title"><?php echo number_format($totalRevenue); ?> VNĐ</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-secondary bubble-shadow-small">
                        <i class="far fa-check-circle"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Đơn hàng</p>
                        <h4 class="card-title"><?php echo number_format($totalOrders); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-info bubble-shadow-small">
                        <i class="fas fa-user-check"></i>
                      </div>
                    </div>
                     <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Nhân viên</p>
                        <h4 class="card-title"><?php echo number_format($totalEmployees); ?></h4>
                      </div>
                    </div> 
                  </div>
                </div>
              </div>
            </div>
          </div>
          
            
          <div class="row">
            <div class="col-md-8">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Doanh thu trong tháng</div>
                    <div class="card-tools">
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="chart-container" style="min-height: 375px">
                    <canvas id="monthlyRevenueChart"></canvas>
                  </div>
                </div>
              </div>
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Đơn hàng trong tháng</div>
                    <div class="card-tools">
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="chart-container" style="min-height: 375px">
                    <canvas id="monthlyOrdersChart"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-round">
                <div class="card-body pb-0">
                  <div class="card-title">Đơn hàng trong ngày</div>
                  <h2 class="mb-2"><?php 
                        $todayOrders = 0;
                        foreach($dailyOrdersData as $data) {
                            if($data['date'] == date('Y-m-d')) {
                                $todayOrders = $data['total'];
                                break;
                            }
                        }
                        echo $todayOrders;
                    ?></h2>
                </div>
              </div>
              <div class="card card-primary card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Doanh thu trong ngày</div>
                  </div>
                </div>
                <div class="card-body pb-0">
                  <div class="mb-4 mt-2">
                    <h1><?php 
                        $todayRevenue = 0;
                        foreach($dailyData as $data) {
                            if($data['date'] == date('Y-m-d')) {
                                $todayRevenue = $data['total'];
                                break;
                            }
                        }
                        echo number_format($todayRevenue); 
                    ?> VNĐ</h1>
                  </div>
                </div>
              </div>
              <div class="card card-round">
                <div class="card-body">
                  <div class="card-head-row card-tools-still-right">
                    <div class="card-title">Khách hàng thân thiết</div>
                    <div class="card-tools">
                      <div class="dropdown">
                        <button class="btn btn-icon btn-clean me-0" type="button" id="dropdownMenuButton"
                          data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="fas fa-ellipsis-h"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="card-list py-4">
                    <?php while($customer = mysqli_fetch_assoc($resultLoyalCustomers)): ?>
                    <div class="item-list">
                      <div class="avatar">
                        <span class="avatar-title rounded-circle border border-white bg-primary">
                          <?php echo strtoupper(substr($customer['tenKH'], 0, 1)); ?>
                        </span>
                      </div>
                      <div class="info-user ms-3">
                        <div class="username"><?php echo htmlspecialchars($customer['tenKH']); ?></div>
                        <div class="status">
                          <?php echo htmlspecialchars($customer['email']); ?> 
                          <span class="badge badge-primary ms-2"><?php echo $customer['total_orders']; ?> đơn</span>
                        </div>
                      </div>
                    </div>
                    <?php endwhile; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            
            <!-- <div class="col-md-8">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row card-tools-still-right">
                    <div class="card-title">Lịch sử giao dịch</div>
                    <div class="card-tools">
                      <div class="dropdown">
                        <button class="btn btn-icon btn-clean me-0" type="button" id="dropdownMenuButton"
                          data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <a class="dropdown-item" href="#">Action</a>
                          <a class="dropdown-item" href="#">Another action</a>
                          <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                     Projects table 
                    <table class="table align-items-center mb-0">
                      <thead class="thead-light">
                        <tr>
                          <th scope="col">Payment Number</th>
                          <th scope="col" class="text-end">Date & Time</th>
                          <th scope="col" class="text-end">Amount</th>
                          <th scope="col" class="text-end">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <th scope="row">
                            <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                              <i class="fa fa-check"></i>
                            </button>
                            Payment from #10231
                          </th>
                          <td class="text-end">Mar 19, 2020, 2.45pm</td>
                          <td class="text-end">$250.00</td>
                          <td class="text-end">
                            <span class="badge badge-success">Completed</span>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row">
                            <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                              <i class="fa fa-check"></i>
                            </button>
                            Payment from #10231
                          </th>
                          <td class="text-end">Mar 19, 2020, 2.45pm</td>
                          <td class="text-end">$250.00</td>
                          <td class="text-end">
                            <span class="badge badge-success">Completed</span>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row">
                            <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                              <i class="fa fa-check"></i>
                            </button>
                            Payment from #10231
                          </th>
                          <td class="text-end">Mar 19, 2020, 2.45pm</td>
                          <td class="text-end">$250.00</td>
                          <td class="text-end">
                            <span class="badge badge-success">Completed</span>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row">
                            <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                              <i class="fa fa-check"></i>
                            </button>
                            Payment from #10231
                          </th>
                          <td class="text-end">Mar 19, 2020, 2.45pm</td>
                          <td class="text-end">$250.00</td>
                          <td class="text-end">
                            <span class="badge badge-success">Completed</span>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row">
                            <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                              <i class="fa fa-check"></i>
                            </button>
                            Payment from #10231
                          </th>
                          <td class="text-end">Mar 19, 2020, 2.45pm</td>
                          <td class="text-end">$250.00</td>
                          <td class="text-end">
                            <span class="badge badge-success">Completed</span>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row">
                            <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                              <i class="fa fa-check"></i>
                            </button>
                            Payment from #10231
                          </th>
                          <td class="text-end">Mar 19, 2020, 2.45pm</td>
                          <td class="text-end">$250.00</td>
                          <td class="text-end">
                            <span class="badge badge-success">Completed</span>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row">
                            <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                              <i class="fa fa-check"></i>
                            </button>
                            Payment from #10231
                          </th>
                          <td class="text-end">Mar 19, 2020, 2.45pm</td>
                          <td class="text-end">$250.00</td>
                          <td class="text-end">
                            <span class="badge badge-success">Completed</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div> -->
                </div>
              </div>
            </div>
          </div>
        </div>
  </div>

<script>
// Biểu đồ doanh thu theo tháng
var ctx = document.getElementById('monthlyRevenueChart').getContext('2d');
var monthlyData = <?php echo json_encode($monthlyData); ?>;
var labels = monthlyData.map(item => {
    var date = new Date(item.date);
    return date.getDate() + '/' + (date.getMonth() + 1);
});
var data = monthlyData.map(item => item.total);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Doanh thu',
            data: data,
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.2)',
            borderWidth: 2,
            pointBackgroundColor: '#4CAF50',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        size: 12,
                        family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(context.raw);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND',
                            maximumSignificantDigits: 3
                        }).format(value);
                    }
                },
                grid: {
                    drawBorder: false,
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Biểu đồ đơn hàng theo tháng
var monthlyOrdersCtx = document.getElementById('monthlyOrdersChart').getContext('2d');
var monthlyOrdersData = <?php 
    // Lấy số đơn hàng theo ngày trong tháng hiện tại
    $queryMonthlyOrders = "SELECT 
        DATE(NgayDatHang) as date,
        COUNT(*) as total
    FROM donhang 
    WHERE TrangThai = 'Đã thanh toán'
    AND MONTH(NgayDatHang) = MONTH(CURRENT_DATE())
    AND YEAR(NgayDatHang) = YEAR(CURRENT_DATE())
    GROUP BY DATE(NgayDatHang)
    ORDER BY date ASC";
    $resultMonthlyOrders = mysqli_query($conn, $queryMonthlyOrders);
    $monthlyOrdersData = [];
    while($row = mysqli_fetch_assoc($resultMonthlyOrders)) {
        $monthlyOrdersData[] = $row;
    }
    echo json_encode($monthlyOrdersData);
?>;
var monthlyOrdersLabels = monthlyOrdersData.map(item => {
    var date = new Date(item.date);
    return date.getDate() + '/' + (date.getMonth() + 1);
});
var monthlyOrdersValues = monthlyOrdersData.map(item => item.total);

new Chart(monthlyOrdersCtx, {
    type: 'bar',
    data: {
        labels: monthlyOrdersLabels,
        datasets: [{
            label: 'Số đơn hàng',
            data: monthlyOrdersValues,
            backgroundColor: 'rgba(255, 152, 0, 0.8)',
            borderColor: '#FF9800',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        size: 12,
                        family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                },
                grid: {
                    drawBorder: false,
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
      