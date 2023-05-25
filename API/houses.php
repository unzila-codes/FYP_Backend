 <?php
include('../CONFI/connection.php');

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT p.*, i.*
FROM property p
INNER JOIN image i ON p.id = i.property_id
");
$stmt->execute();
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin (not recommended for production)
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow specific HTTP methods
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // Allow specific HTTP headers

// Return the data as JSON
echo json_encode($houses);
exit(); // Make sure to exit to prevent any additional output
?> 

