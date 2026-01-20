<?php
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/db.php";
include_once __DIR__ . "/../../core/auth.php";

// التحقق من الصالحيه
if($user['admin'] !== 1){
    echo json_encode([
        "success" => false,
        "message"=> "You Don/'t Have Access"
    ]);
    exit;
}

// التحقق من method
if($_SERVER['REQUEST_METHOD'] !== "DELETE"){
    http_response_code(405);
    echo json_encode([
        "success"=> false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

$data=json_decode(file_get_contents("php://input") , true);

// التحقق من البيانات

if(empty($data['id']) || !is_numeric($data['id'])){
    echo json_encode([
        "success" => false,
        "message" => "ID is Required" 
    ]);
    exit;
}

$id=(int)$data['id'];

try{
    $stmt=$con->prepare("DELETE FROM categories WHERE id= :id");
    $stmt->bindParam(":id" , $id , PDO::PARAM_INT);
    $stmt->execute();
    
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
