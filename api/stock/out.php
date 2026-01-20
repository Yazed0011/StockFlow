<?php
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/auth.php";
include_once __DIR__ . "/../../core/db.php";

if ($user['admin'] !== 1) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "You don't have access"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$errors = [];
if (empty($data['items']) || !is_array($data['items'])) {
    $errors[] = "Items array is required";
}

if (empty($errors)) {
    foreach ($data['items'] as $index => $item) {
        if (!isset($item['id']) || !is_numeric($item['id'])) {
            $errors[] = "Item #{$index} id is required and must be numeric";
        }
        if (!isset($item['qty']) || !is_numeric($item['qty']) || (int)$item['qty'] <= 0) {
            $errors[] = "Item #{$index} qty is required and must be > 0";
        }
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit;
}

$items = $data['items'];

try {
    $con->beginTransaction();

    $stmt = $con->prepare("INSERT INTO stock (qty,id_product,status) VALUES (:qty , :id_product , 'out')");
    $sumInStmt = $con->prepare("SELECT COALESCE(SUM(qty),0) AS total_in FROM stock WHERE status = 'in' AND id_product = :id_product");
    $sumOutStmt = $con->prepare("SELECT COALESCE(SUM(qty),0) AS total_out FROM stock WHERE status = 'out' AND id_product = :id_product");
    $updateRestStmt = $con->prepare("UPDATE stock SET rest_qty = :rest_qty WHERE id = :id");

    foreach ($items as $item) {
        $idProduct = (int)$item['id'];
        $qty = (int)$item['qty'];

        // current available before inserting this OUT movement
        $sumInStmt->execute([':id_product' => $idProduct]);
        $inRow = $sumInStmt->fetch(PDO::FETCH_ASSOC);
        $sumOutStmt->execute([':id_product' => $idProduct]);
        $outRow = $sumOutStmt->fetch(PDO::FETCH_ASSOC);

        $available = (int)($inRow['total_in'] ?? 0) - (int)($outRow['total_out'] ?? 0);
        if ($qty > $available) {
            $con->rollBack();
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "message" => "Not enough stock for product {$idProduct}. Available: {$available}"
            ]);
            exit;
        }

        $stmt->bindParam(":qty", $qty, PDO::PARAM_INT);
        $stmt->bindParam(":id_product", $idProduct, PDO::PARAM_INT);
        $stmt->execute();

        $insertedId = (int)$con->lastInsertId();

        // rest after inserting this OUT movement
        $sumOutStmt->execute([':id_product' => $idProduct]);
        $outRowAfter = $sumOutStmt->fetch(PDO::FETCH_ASSOC);
        $restQty = (int)($inRow['total_in'] ?? 0) - (int)($outRowAfter['total_out'] ?? 0);

        $updateRestStmt->execute([
            ':rest_qty' => $restQty,
            ':id' => $insertedId
        ]);
    }
    $con->commit();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Insert is successful"
    ]);
} catch (PDOException $e) {
    if ($con->inTransaction()) {
        $con->rollBack();
    }
    error_log($e);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "SERVER ERROR"
    ]);
    exit;
}
