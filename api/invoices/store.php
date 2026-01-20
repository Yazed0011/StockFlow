<?php 
    header("Content-Type: application/json");
    include_once __DIR__ . "/../../core/auth.php";
    include_once __DIR__ . "/../../core/db.php";

    if($user['admin'] !== 1){
        echo json_encode([
            "success" => false,
            "message" => "You Don't Have Access"
        ]);
        die;
    }

    if($_SERVER['REQUEST_METHOD'] !== "POST"){
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method Not Allowed"
        ]);
        exit;
    }

    // جلب البيانات 
    $data=json_decode(file_get_contents("php://input") , true);
    // التحقق من البيانات
    $errors=[];
    if(empty($data['products']) || trim($data['products']) == ""){
        $errors[]= "Products is Required";
    }
    if(empty($data['customer_name']) || trim($data['customer_name']) == ""){
        $errors[]= "Customer Name is Required";
    }
    if(!empty($errors)){
        echo json_encode([
            "success" => false,
            "message" => $errors
        ]);
        exit;
    }
    
    $products=$data['products'];
    $name=$data['customer_name'];

    try{
        // 1 insert data
        $con->beginTransaction();
        $numberRandom=random_int(1000,9999);
        $stmt=$con->prepare('INSERT INTO invoice (invoice_number, customer_name) VALUES (:invoice_number , :customer_name)');
        $stmt->bindParam(":invoice_number" , $numberRandom , PDO::PARAM_INT);
        $stmt->bindParam(":customer_name" , $name , PDO::PARAM_STR);
        $stmt->execute();
        $invoicesId=$con->lastInsertId();
        $total=0;

        foreach($products as $item){
            $productId=$item['id'];
            $qty=$item['qty'];

            $stmt=$con->prepare("SELECT sale_price FROM products WHERE id = :id");
            $stmt->execute([':id' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$product){
                echo json_encode([
                    "success" => false,
                    "message" => "Product Not Found"
                ]);
                exit;
            }

            $price =$product['sale_price'];
            $subtotal= $price * $qty;
            $total += $subtotal;

            $stmt=$con->prepare("INSERT INTO invoice_items(invoice_id, product_id, qty, price) VALUES (:invoice_id, :product_id, :qty, :price)");
            $stmt->bindParam(":invoice_id" ,$invoicesId , PDO::PARAM_INT);
            $stmt->bindParam(":product_id" ,$productId , PDO::PARAM_INT);
            $stmt->bindParam(":qty" ,$qty , PDO::PARAM_INT);
            $stmt->bindParam(":price" ,$price);
            $stmt->execute();
        }

        $stmt = $con->prepare("UPDATE invoice SET total = :total WHERE id = :id");
        $stmt->execute([
    ':total' => $total,
    ':id'    => $invoicesId
]);


        $con->commit();

        echo json_encode([
            "success" => true,
            "invoice_id" => $invoicesId,
            "total" => $total
        ]);
    }
    catch(PDOException $e){
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "SERVER ERROR"
        ]);
        exit;
    }