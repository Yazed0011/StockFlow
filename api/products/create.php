<?php
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/db.php";
include_once __DIR__ . "/../../core/auth.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
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
if (empty($data['name']) || trim($data['name']) == "") {
    $errors[] = "Name is Required";
}
if (empty($data['description']) || trim($data['description']) == "") {
    $errors[] = "Description is Required";
}
if (empty($data['purchase_price']) || !is_numeric($data['purchase_price']) || floatval($data['purchase_price']) < 0) {
    $errors[] = "Purchase Price is Required and must be a positive number";
}
if (empty($data['current_stock']) || !is_numeric($data['current_stock']) || intval($data['current_stock']) < 0) {
    $errors[] = "Stock is Required and must be a non-negative number";
}
if (empty($data['sale_price']) || !is_numeric($data['sale_price']) || floatval($data['sale_price']) < 0) {
    $errors[] = "Price is Required and must be a positive number";
}
if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
    $errors[] = "Category is Required";
}
if (empty($data['supplier_id']) || !is_numeric($data['supplier_id'])) {
    $errors[] = "Supplier is Required";
}
if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit;
}

$name = strip_tags(trim($data['name']));
$description = strip_tags(trim($data['description']));
$purchase_price = floatval($data['purchase_price']);
$current_stock = intval($data['current_stock']);
$sale_price = floatval($data['sale_price']);
$category_id = intval($data['category_id']);
$supplier_id = intval($data['supplier_id']);

// Verify category exists
try {
    $checkCategory = $con->prepare("SELECT id FROM categories WHERE id = :category_id");
    $checkCategory->bindParam(":category_id", $category_id, PDO::PARAM_INT);
    $checkCategory->execute();
    if ($checkCategory->rowCount() == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Category not found"
        ]);
        exit;
    }

    // Verify supplier exists
    $checkSupplier = $con->prepare("SELECT id FROM suppliers WHERE id = :supplier_id");
    $checkSupplier->bindParam(":supplier_id", $supplier_id, PDO::PARAM_INT);
    $checkSupplier->execute();
    if ($checkSupplier->rowCount() == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Supplier not found"
        ]);
        exit;
    }

    $stmt = $con->prepare("INSERT INTO products (name, description, purchase_price, current_stock, sale_price, category_id, supplier_id) VALUES (:name, :description, :purchase_price, :current_stock, :sale_price, :category_id, :supplier_id)");
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":description", $description, PDO::PARAM_STR);
    $stmt->bindParam(":purchase_price", $purchase_price);
    $stmt->bindParam(":current_stock", $current_stock, PDO::PARAM_INT);
    $stmt->bindParam(":sale_price", $sale_price);
    $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
    $stmt->bindParam(":supplier_id", $supplier_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Product created successfully"
    ]);
} catch (PDOException $e) {
    error_log("Product creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "SERVER ERROR"
    ]);
    exit;
}
