<?php
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/auth.php";
include_once __DIR__ . "/../../core/db.php";

    if($_SERVER['REQUEST_METHOD'] !== "GET"){
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method Not Allowed"
        ]);
        exit;
    }

    try{
        $stmt=$con->prepare("SELECT * FROM suppliers");
        $stmt->execute();
        $supplier=$stmt->fetchAll();

        if(!$supplier){
            echo json_encode([
                "success" => false,
                "message" => "Not Found Supplier"
            ]);
            exit;
        }

        echo json_encode([
            "success" => true,
            "message" => "All Supplier",
            "data" => $supplier
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