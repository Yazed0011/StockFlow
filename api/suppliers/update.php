<?php
header("Content-Type: application/json");
include_once __DIR__ . "/../../core/db.php";
include_once __DIR__ . "/../../core/auth.php";

if($_SERVER['REQUEST_METHOD'] !== "PUT"){
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

// get data

$data=json_decode(file_get_contents("php://input") , true);


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
    if(empty($data['id']) || !is_numeric($data['id'])){
        $errors[]= "id is Required";
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
    $id=(int)$data['id'];

    try{
        // check email
        $checkstmt=$con->prepare("SELECT id FROM suppliers WHERE email = :email");
        $checkstmt->bindParam(":email" , $email , PDO::PARAM_STR);
        $checkstmt->execute();
        $user=$checkstmt->fetch(PDO::FETCH_ASSOC);

        if($user){
            echo json_encode([
                "success" => false,
                "message" => "Email Is Already Exist"
            ]);
            exit();
        }

        $stmt=$con->prepare("UPDATE suppliers SET name = :name , email =:email , phone =:phone WHERE id = :id");
        $stmt->bindParam(":name" , $name , PDO::PARAM_STR);
        $stmt->bindParam(":email" , $email , PDO::PARAM_STR);
        $stmt->bindParam(":phone" , $phone , PDO::PARAM_INT);
        $stmt->bindParam(":id" , $id , PDO::PARAM_INT);
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