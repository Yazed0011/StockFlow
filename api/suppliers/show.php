<?php 
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/db.php";
include_once __DIR__ . "/../../core/auth.php";

    if($_SERVER['REQUEST_METHOD'] !== "GET"){
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method Not Allowed"
        ]);
        exit;
    }

    if(empty($_GET['id']) || !is_numeric($_GET['id'])){
        echo json_encode([
            "success" => false,
            "message" => "ID is Required"
        ]);
        exit;
    }

    $id=(int)$_GET['id'];

    try{
        $stmt=$con->prepare("SELECT name , phone , email WHERE id = :id");
        $stmt->bindParam(":id" , $id , PDO::PARAM_INT);
        $stmt->execute();
        $supplier=$stmt->fetch(PDO::FETCH_ASSOC);

        if(!$supplier){
            echo json_encode([
                "success" => false,
                "message" => "Supplier Not Found"
            ]);
            exit;
        }

        echo json_encode([
            "success" => true,
            "message" => $supplier
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