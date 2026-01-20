<?php 
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/db.php";
include_once __DIR__ . "/../../core/auth.php";

// التحقق من method
if($_SERVER['REQUEST_METHOD'] !== "GET"){
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allow"
    ]);
    exit();
}

try{
    $stmt=$con->prepare("SELECT * FROM invoices");
    $stmt->execute();
    $invocies=$stmt->fetchAll();

        if(!$invocies || empty($invocies)){
        echo json_encode([
            "success" => false,
            "message" => "Not Foound"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "ALL INVOICES",
        "data" => $invocies
    ]);
    
}
catch(PDOException $e){
    error_log($e);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "ERROR SERVER"
    ]);
    exit();
}