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

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

try {
    $stmt = $con->prepare("
        SELECT
            i.id AS invoice_id,
            i.invoice_number,
            i.customer_name,
            i.total,
            i.created_at,
            ii.qty,
            ii.price,
            p.name AS product_name
        FROM invoice i
        JOIN invoice_items ii ON ii.invoice_id = i.id
        JOIN products p ON p.id = ii.product_id
        ORDER BY i.id DESC
    ");

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // تنظيم البيانات (كل فاتورة وبداخلها منتجاتها)
    $invoices = [];

    foreach ($rows as $row) {
        $id = $row['invoice_id'];

        if (!isset($invoices[$id])) {
            $invoices[$id] = [
                "invoice_id"     => $id,
                "invoice_number" => $row['invoice_number'],
                "customer_name"  => $row['customer_name'],
                "total"          => $row['total'],
                "created_at"     => $row['created_at'],
                "items"          => []
            ];
        }

        $invoices[$id]['items'][] = [
            "product_name" => $row['product_name'],
            "qty"          => $row['qty'],
            "price"        => $row['price'],
            "subtotal"     => $row['qty'] * $row['price']
        ];
    }

    echo json_encode([
        "success" => true,
        "data" => array_values($invoices)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "SERVER ERROR"
    ]);
}
