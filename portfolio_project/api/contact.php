<?php
// Enable CORS for your React app domain
header("Access-Control-Allow-Origin: *"); // Replace * with your domain in production
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['name']) || !isset($data['email']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Sanitize inputs
$name = filter_var($data['name'], FILTER_SANITIZE_STRING);
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$message = filter_var($data['message'], FILTER_SANITIZE_STRING);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Load environment variables
// Simple function to load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env file (adjust path as needed - this assumes .env is in the parent directory)
loadEnv(__DIR__ . '/../.env');

// Azure SQL Database connection info from environment variables
$serverName = getenv('DB_SERVER') ?: "alexsportfoliodatabase.database.windows.net";
$connectionOptions = array(
    "Database" => getenv('DB_DATABASE') ?: "PortfolioDB",
    "Uid" => getenv('DB_USERNAME') ?: "alexmerlo23",
    "PWD" => getenv('DB_PASSWORD')
);

// Connect to Azure SQL Database
try {
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    
    if ($conn === false) {
        throw new Exception("Database connection failed: " . print_r(sqlsrv_errors(), true));
    }
    
    // Insert data into database
    $query = "INSERT INTO ContactMessages (Name, Email, Message) 
              VALUES (?, ?, ?)";
    
    $params = array($name, $email, $message);
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        throw new Exception("Database query failed: " . print_r(sqlsrv_errors(), true));
    }
    
    // Close the connection
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Contact message saved successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>