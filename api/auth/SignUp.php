<?php
header("Content-Type: application/json");
// Step 1 : Connect DB
include_once __DIR__ . "/../../core/db.php";

// Step 2 : Verify the method
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

// Step 3 : Retrieve data from the user interface
$data = json_decode(file_get_contents("php://input"), true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON format"
    ]);
    exit;
}

// Step 4 : Data verification
$errors = [];

// Validate name
if (empty($data['name']) || trim($data['name']) === "") {
    $errors[] = "Name is required";
} elseif (strlen(trim($data['name'])) < 2) {
    $errors[] = "Name must be at least 2 characters";
} elseif (strlen(trim($data['name'])) > 100) {
    $errors[] = "Name must not exceed 100 characters";
}

// Validate email
if (empty($data['email'])) {
    $errors[] = "Email is required";
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email is not valid";
} elseif (strlen($data['email']) > 255) {
    $errors[] = "Email must not exceed 255 characters";
}

// Validate password
if (empty($data['password'])) {
    $errors[] = "Password is required";
} elseif (strlen($data['password']) < 8) {
    $errors[] = "Password must be at least 8 characters";
} elseif (strlen($data['password']) > 128) {
    $errors[] = "Password must not exceed 128 characters";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit;
}

// Sanitize and prepare data
$name = trim(strip_tags($data['name']));
$email = trim(strtolower($data['email'])); // Normalize email to lowercase
$password = password_hash($data['password'], PASSWORD_DEFAULT);

// Step 5 : Data handling
try {
    // Step 6 : Check if the user already exists
    $checkstmt = $con->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $checkstmt->bindParam(":email", $email, PDO::PARAM_STR);
    $checkstmt->execute();
    $user = $checkstmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        http_response_code(409); // Conflict status code
        echo json_encode([
            "success" => false,
            "message" => "Email already exists"
        ]);
        exit;
    }

    // Step 7 : Insert the new user
    $stmt = $con->prepare("INSERT INTO users (name, email, password) VALUES(:name, :email, :password)");
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->bindParam(":password", $password, PDO::PARAM_STR);
    $stmt->execute();

    http_response_code(201); // Created status code
    echo json_encode([
        "success" => true,
        "message" => "User registered successfully"
    ]);
}
// Step 8 : If the query fails
catch (PDOException $e) {
    // Log the error for debugging (don't expose to user)
    error_log("SignUp Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred. Please try again later."
    ]);
    exit;
}
