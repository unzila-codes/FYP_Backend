<?php
require __DIR__.'/CONFI/connection.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function updateUserProfile($token, $name, $cnic) {
        $stmt = $this->conn->prepare("UPDATE demo_user SET name = ?, cnic = ? WHERE token = ?");
        $stmt->bind_param("sss", $name, $cnic, $token);
        $stmt->execute();

        if ($stmt->affected_rows === 1) {
            $response['status'] = 'success';
            $response['message'] = 'Profile updated successfully';
            echo json_encode($response);
            exit;
        }

        // Profile update failed
        echo json_encode(['message' => 'Failed to update profile']);
        exit;
    }
}

$user = new User($conn);

$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authorizationHeader) {
    $token = str_replace('Bearer ', '', $authorizationHeader);

    // Get the updated profile data from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $name = isset($data['name']) ? $data['name'] : '';
    $cnic = isset($data['cnic']) ? $data['cnic'] : '';

    $user->updateUserProfile($token, $name, $cnic);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing authorization header']);
    exit;
}
?>
