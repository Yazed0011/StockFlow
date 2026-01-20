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

if($_SERVER['REQUEST_METHOD'] !== "DELETE"){
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

$id=(int)$_GET['id'];

try{
    $stmt=$con->prepare("DELETE FROM suppliers WHERE id = :id");
    $stmt->execute([":id" => $id ]);

    echo json_encode([
        "success" => true,
        "message" => "Delete is Successfully"
    ]);
}
catch(PDOException $e){
    error_log($e);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "SERVER ERROR"
    ]);
    exit;
}