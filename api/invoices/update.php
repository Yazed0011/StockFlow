<?php
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/auth.php";
include_once __DIR__ . "/../../core/db.php";

if ($user['admin'] !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "You Don't Have Access"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "PUT") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

// get data

$data = json_decode(file_get_contents("php://input"), true);

// validate data
$errors = [];
if (empty($data['id']) || !is_numeric($data['id'])) {
    $errors[] = "ID is required";
}
if (empty($data['customer_name']) || trim($data['customer_name']) == "") {
    $errors[] = "Customer Name is required";
}
if (empty($data['products']) || !is_array($data['products'])) {
    $errors[] = "Products is required";
}
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit;
}

$products = $data['products'];
$name = strip_tags($data['customer_name']);
$id = (int) $data['id'];

try {
    $con->beginTransaction();

    // تحديث بيانات الفاتورة الأساسية
    $stmt = $con->prepare("UPDATE invoice SET customer_name = :customer_name WHERE id = :id");
    $stmt->bindParam(":customer_name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    // إعادة حساب الإجمالي وتحديث العناصر
    $total = 0;

    foreach ($products as $item) {
        $itemId  = isset($item['id']) ? (int) $item['id'] : 0;
        $qty     = isset($item['qty']) ? (int) $item['qty'] : 0;
        $price   = isset($item['price']) ? (float) $item['price'] : 0;

        // تجاوز أي عنصر غير صالح
        if ($itemId <= 0 || $qty < 0 || $price < 0) {
            continue;
        }

        $subtotal = $qty * $price;
        $total   += $subtotal;

        $stmt = $con->prepare("UPDATE invoice_items SET qty = :qty WHERE id = :id");
        $stmt->bindParam(":qty", $qty, PDO::PARAM_INT);
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // تحديث الإجمالي بعد الانتهاء من العناصر
    $stmt = $con->prepare("UPDATE invoice SET total = :total WHERE id = :id");
    $stmt->bindParam(":total", $total);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    $con->commit();
    echo json_encode([
        "success" => true,
        "message" => "Update is Successfully"
    ]);
} catch (PDOException $e) {
    error_log($e);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "SERVER ERROR"
    ]);
    exit;
}
