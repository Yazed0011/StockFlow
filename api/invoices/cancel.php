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

if ($_SERVER['REQUEST_METHOD'] !== "DELETE") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON format"
    ]);
    exit;
}

// Validate data
if (empty($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "ID is Required"
    ]);
    exit;
}

$id = (int)$data['id'];

try {
    // Check if invoice exists first
    $checkStmt = $con->prepare("SELECT id FROM invoice WHERE id = :id LIMIT 1");
    $checkStmt->bindParam(":id", $id, PDO::PARAM_INT);
    $checkStmt->execute();
    $invoice = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Invoice not found"
        ]);
        exit;
    }

    // Delete invoice (CASCADE will handle invoice_items automatically)
    $stmt = $con->prepare("DELETE FROM invoice WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Invoice deleted successfully"
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
