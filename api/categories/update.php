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
if($_SERVER['REQUEST_METHOD'] !== "PUT"){
    http_response_code(405);
    echo json_encode([
        "success"=> false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

$data=json_decode(file_get_contents("php://input") , true);

// التحقق من البيانات
$errors=[];

if(empty($data['id']) || !is_numeric($data['id'])){
    $errors[]="ID is Required";
}
if(empty($data['name']) || trim($data['name']) == ""){
    $errors[]="Name is Required";
}
if(empty($data['description']) || trim($data['description']) == ""){
    $errors[]="Description is Required";
}
if(!empty($errors)){
    echo json_encode([
        "success" => false,
        "message" => $data
    ]);
    exit;
}

$name=strip_tags($data['name']);
$description=strip_tags($data['description']);
$id=(int)$data['id'];

try{
    $stmt=$con->prepare("UPDATE categories SET name= :name , description= :description WHERE id= :id");
    $stmt->bindParam(":name" , $name , PDO::PARAM_STR);
    $stmt->bindParam(":id" , $id , PDO::PARAM_INT);
    $stmt->bindParam(":description" , $description , PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        "success" => true,
        "message" => "Update is Successfully"
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
