<?php
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/auth.php";
include_once __DIR__ . "/../../core/db.php";

if($user['admin'] !== 1){
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

$data=json_decode(file_get_contents("php://input") , true);

// validate data
$errors=[];
if(empty($data['id']) || !is_numeric($data['id'])){
    $errors[]="ID is Required";
}
if(!empty($errors)){
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit;
}

$id=(int)$data['id'];

try{
        // التحقق من وجود المنتج قبل الحذف
        $checkProduct = $con->prepare("SELECT id FROM products WHERE id = :id");
        $checkProduct->bindParam(":id", $id, PDO::PARAM_INT);
        $checkProduct->execute();
        if ($checkProduct->rowCount() == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Product not found"
            ]);
            exit;
        }
    $stmt=$con->prepare("DELETE FROM products WHERE id= :id");
    $stmt->bindParam(":id" , $id , PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode([
        "success" => true,
        "message" => "Product deleted successfully"
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