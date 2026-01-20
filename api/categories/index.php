<?php 
    header("Content-Type: application/json");
    include_once __DIR__ . "/../../core/auth.php";
    include_once __DIR__ . "/../../core/db.php";

    if($user['admin'] !== 1){
        echo json_encode([
            "success" => false,
            "message" => "You Don/'t Have Access"
        ]);
        die;
    }

    if($_SERVER['REQUEST_METHOD'] !== "POST"){
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method Not Allow"
        ]);
        exit;
    }

    // جلب البيانات 
    $data=json_decode(file_get_contents("php://input") , true);

    // التحقق من البيانات
    $errors=[];
    if(trim($data['name']) == "" || empty($data['name'])){
        $errors[]= "Name is Required";
    }
    if(trim($data['description']) == "" || empty($data['description'])){
        $errors[]= "description is Required";
    }
    if(!empty($errors)){
        echo json_encode([
            "success" => false,
            "message" => $errors
        ]);
        exit;
    }

    $name=strip_tags($data['name']);
    $description=strip_tags($data['description']);

    try{
        $stmt=$con->prepare("INSERT INTO categories (name , description) VALUES (:name , :description)");
        $stmt->bindParam(":name" , $name , PDO::PARAM_STR);
        $stmt->bindParam(":description" , $description , PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Created is Successfully"
        ]);

    }catch(PDOException $e){
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "SERVER ERROR"
        ]);
    }
