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

if($_SERVER['REQUEST_METHOD'] !== "POST"){
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

    // get data

    $data=json_decode(file_get_contents("php://input") , true);

    // Validate data

    $errors=[];
    if(empty($data['name']) || trim($data['name']) == ""){
        $errors[]= "Name is Required";
    }
// Validate email
    if (empty($data['email'])) {
        $errors[] = "Email is required";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid";
    } elseif (strlen($data['email']) > 255) {
        $errors[] = "Email must not exceed 255 characters";
    }

    if(empty($data['phone']) || !is_numeric($data['phone'])){
        $errors[]= "Phone is Required";
    }

    if(!empty($errors)){
        echo json_encode([
            "success" => false,
            "message" => $errors
        ]);
        exit;
    }

    $name=strip_tags($data['name']);
    $email=strip_tags(strtolower($data['email']));
    $phone=strip_tags($data['phone']);

    try{
        $stmt=$con->prepare("INSERT INTO suppliers (name , email , phone) VALUES (:name , :email , :phone)");
        $stmt->bindParam(":name" , $name , PDO::PARAM_STR);
        $stmt->bindParam(":email" , $email , PDO::PARAM_STR);
        $stmt->bindParam(":phone" , $phone , PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Supplier Created Successfully"
        ]);
    }
    catch(PDOException $e){
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "SERVER ERROR"
        ]);
        exit;
    }
