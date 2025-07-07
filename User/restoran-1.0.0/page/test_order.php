<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
$mysqli = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
if ($mysqli->connect_error) {
    die("Database connection error: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");

// Function to log results
function logResult($message) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo $message;
    echo "</div>";
}

// Test function to insert a dummy order
function testInsertOrder($mysqli) {
    // Test data
    $idKH = 6; // Existing customer from your database
    $idban = 1; // Existing table
    $ngayDatHang = date('Y-m-d H:i:s');
    $tongTien = 100000.00;
    $trangThai = 'Chờ xử lý';
    $maDonHang = 'TEST-' . date('YmdHis');
    $soHoaDon = date('His');
    
    // Log test data
    logResult("<h3>Test Data:</h3>");
    logResult("idKH: $idKH");
    logResult("idban: $idban");
    logResult("ngayDatHang: $ngayDatHang");
    logResult("tongTien: $tongTien");
    logResult("trangThai: $trangThai");
    logResult("maDonHang: $maDonHang");
    logResult("soHoaDon: $soHoaDon");
    
    // Check donhang table structure
    $tableResult = $mysqli->query("SHOW COLUMNS FROM donhang");
    if (!$tableResult) {
        logResult("<h3>Error checking table structure:</h3> " . $mysqli->error);
        return;
    }
    
    logResult("<h3>donhang Table Structure:</h3>");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($column = $tableResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Direct SQL query first to see specific errors
    $insertSQL = "INSERT INTO donhang (idKH, idban, NgayDatHang, TongTien, TrangThai, MaDonHang, SoHoaDon) 
                VALUES ($idKH, $idban, '$ngayDatHang', $tongTien, '$trangThai', '$maDonHang', '$soHoaDon')";
    
    logResult("<h3>SQL Query:</h3>");
    logResult($insertSQL);
    
    // Try direct query
    $directResult = $mysqli->query($insertSQL);
    if (!$directResult) {
        logResult("<h3>Direct Query Error:</h3> " . $mysqli->error);
    } else {
        logResult("<h3>Direct Query Success!</h3> Insert ID: " . $mysqli->insert_id);
    }
    
    // Try with prepared statement
    $stmt = $mysqli->prepare("INSERT INTO donhang (idKH, idban, NgayDatHang, TongTien, TrangThai, MaDonHang, SoHoaDon) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        logResult("<h3>Prepared Statement Error:</h3> " . $mysqli->error);
        return;
    }
    
    $stmt->bind_param('iisdsss', $idKH, $idban, $ngayDatHang, $tongTien, $trangThai, $maDonHang, $soHoaDon);
    $result = $stmt->execute();
    
    if (!$result) {
        logResult("<h3>Prepared Statement Execution Error:</h3> " . $stmt->error);
    } else {
        logResult("<h3>Prepared Statement Success!</h3> Insert ID: " . $mysqli->insert_id);
    }
    
    $stmt->close();
}

// Check for existing orders
function checkExistingOrders($mysqli) {
    $query = "SELECT * FROM donhang ORDER BY idDH DESC LIMIT 5";
    $result = $mysqli->query($query);
    
    if (!$result) {
        logResult("<h3>Error checking existing orders:</h3> " . $mysqli->error);
        return;
    }
    
    logResult("<h3>Latest 5 Orders:</h3>");
    
    if ($result->num_rows == 0) {
        logResult("No orders found!");
        return;
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    $firstRow = $result->fetch_assoc();
    foreach (array_keys($firstRow) as $column) {
        echo "<th>$column</th>";
    }
    echo "</tr>";
    
    // Reset pointer
    $result->data_seek(0);
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Check connection and privileges
function checkConnection($mysqli) {
    logResult("<h3>Connection Info:</h3>");
    logResult("Connected to MySQL version: " . $mysqli->server_info);
    
    // Check if user has privileges
    $result = $mysqli->query("SHOW GRANTS FOR CURRENT_USER()");
    if (!$result) {
        logResult("Error checking privileges: " . $mysqli->error);
        return;
    }
    
    logResult("<h3>User Privileges:</h3>");
    while ($row = $result->fetch_array()) {
        logResult($row[0]);
    }
}

// Execute test functions based on action
$action = isset($_GET['action']) ? $_GET['action'] : '';

echo "<h1>Order Database Test</h1>";
echo "<div style='margin-bottom: 20px;'>";
echo "<a href='?action=check_connection' style='margin-right: 10px;'>Check Connection</a>";
echo "<a href='?action=show_table' style='margin-right: 10px;'>Show Table Structure</a>";
echo "<a href='?action=check_orders' style='margin-right: 10px;'>Check Existing Orders</a>";
echo "<a href='?action=test_insert' style='margin-right: 10px;'>Test Insert Order</a>";
echo "</div>";

switch ($action) {
    case 'check_connection':
        checkConnection($mysqli);
        break;
        
    case 'show_table':
        // Show table structure only
        $tableResult = $mysqli->query("SHOW COLUMNS FROM donhang");
        if (!$tableResult) {
            logResult("<h3>Error checking table structure:</h3> " . $mysqli->error);
            break;
        }
        
        logResult("<h3>donhang Table Structure:</h3>");
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($column = $tableResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        break;
        
    case 'check_orders':
        checkExistingOrders($mysqli);
        break;
        
    case 'test_insert':
        testInsertOrder($mysqli);
        break;
        
    default:
        logResult("Select an action from the links above.");
}

// Close connection
$mysqli->close();
?> 