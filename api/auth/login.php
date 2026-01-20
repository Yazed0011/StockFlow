<?php 
header("Content-Type: application/json");

include_once __DIR__ . "/../../core/db.php";
include_once __DIR__ . "/../../core/jwt.php";
if($_SERVER['REQUEST_METHOD'] !== "POST"){
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allow"
    ]);
    exit;
}

// جلب البيانات
$data= json_decode(file_get_contents("php://input") , true);

// التحقق من البيانات
    $errors=[];

    if(empty($data['email']) || trim($data['email']) == "" || !filter_var($data['email'] , FILTER_VALIDATE_EMAIL)){
        $errors[] = "Email is Not Valid";
    }
    if(empty($data['password']) || trim($data['password']) == ""){
        $errors[] = "Password is Not Valid";
    }
    if(!empty($errors)){
        echo json_encode([
            "success" => false,
            "message" => $errors
        ]);
        exit();
    }

    // جلب البيانات بعد التحقق منها
    $email=strip_tags($data['email']);
    $password= $data['password'];

    try{
        $stmt=$con->prepare("SELECT id, name, email, password, admin FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(":email" , $email, PDO::PARAM_STR);
        $stmt->execute();
        $user=$stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password , $user['password'])){
            unset($user['password']); // عشان الأمان.
            $token=generateAccessToken($user,$JWT_SECRET,$JWT_ALGO);

            echo json_encode([
                "success" => true,
                "message" => "Login is Successfully",
                "data" => [
            "access_token" => $token,
            "token_type" => "Bearer",
            "expires_in" => $JWT_EXP
                ]
            ]);
        }
        else{
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Password Or Email Not Verfied"
            ]);
            exit;
        }
    }
    catch(PDOException $e){
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Server Error"
        ]);
        exit;
    }