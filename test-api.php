<?php
//解決CORS問題
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
//解決CORS問題

// 設置資料庫連接資訊
$serverName = "127.0.0.1"; // 你的 SQL Server IP 或主機名
$connectionOptions = array(
    "Database" => "my-testdatabase", // 你的資料庫名稱
    "Uid" => "sa", // 使用者名
    "PWD" => "test0713", // 密碼
    "CharacterSet" => "UTF-8"
);

// 連接資料庫
$conn = sqlsrv_connect($serverName, $connectionOptions);

// 檢查是否連接成功
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// 獲取前端傳送的 JSON 請求
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    echo "Debug: OPTIONS request received";
    exit();
}

// 檢查前端數據
  if ( $input['action']=="buy" && isset($input['customer_ID'], $input['product_ID_1'], $input['product_amount_1']) ) {


    //取得執行 預存程序Database Engine 需要的參數v
    $customer_ID = $input['customer_ID'];
    $product_ID_1 = $input['product_ID_1'];
    $product_amount_1 = $input['product_amount_1'];
    $product_ID_2 = $input['product_ID_2'];
    $product_amount_2 = $input['product_amount_2'];
    $sql = "{CALL ProcessOrder(?, ?, ?, ?, ?)}";
    // $sql = "{CALL ProcessOrder(?, ?, ?)}";
    $params = array($customer_ID, $product_ID_1, $product_amount_1, $product_ID_2, $product_amount_2);
    // $params = array($customer_ID, $product_ID_1, $product_amount_1);




    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
      echo json_encode(array(
        "success" => false,
        "message" => "Failed to process order!!!.",
        "error"  => print_r(sqlsrv_errors(), true)  // 輸出錯誤訊息
      ));
    } else {
        echo json_encode(array("success" => true, "message" => "Order processed successfully."));
    }
  }
    
  elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $input['action'] === 'select_product') {
    $sql = "SELECT * FROM Products";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $data = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
    echo json_encode($data);
  }




  elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $input['action'] === 'select_customer') {
    $sql = "SELECT * FROM Customers";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $data = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
    echo json_encode($data);
  }
    
  else {
    echo json_encode(array("success" => false, "message" => "Invalid input."));
  }

    // 關閉連接
    sqlsrv_close($conn);
?>
