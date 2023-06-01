<?php
require __DIR__.'/CONFI/connection.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserProfile($token) {
        $stmt = $this->conn->prepare("SELECT id, name, cnic, email FROM demo_user WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $profileData = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'cnic' => $row['cnic'],
                'email' => $row['email']
            );
            $response['status'] = 'success';
            $response['profile'] = $profileData;
            echo json_encode($response);
            exit;
        }

        // Token not found or invalid
        echo json_encode(['message' => 'Invalid token']);
        exit;
    }
}

$user = new User($conn);

$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authorizationHeader) {
    $token = str_replace('Bearer ', '', $authorizationHeader);
    $user->getUserProfile($token);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing authorization header']);
    exit;
}
?>
