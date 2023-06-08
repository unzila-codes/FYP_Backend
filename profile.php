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
            
            $bidStmt = $this->conn->prepare("SELECT b.bid_id,b.status, b.name, b.amount, p.title FROM bidding b JOIN property p ON b.property_id = p.id JOIN demo_user u ON u.id = p.user_id WHERE token = ?");
            $bidStmt->bind_param("s", $token);
            $bidStmt->execute();

            $bidResult = $bidStmt->get_result();
            if ($bidResult->num_rows >0) {
                while ($bidRow = $bidResult->fetch_assoc()) {
                    $biddingData[] = array(
                        'bid_id ' => $bidRow['bid_id'],
                        'status' => $bidRow['status'],
                        'name' => $bidRow['name'],
                        'amount' => $bidRow['amount'],
                        'title' => $bidRow['title']
                    );
                }
            }

            $response['status'] = 'success';
            $response['profile'] = $profileData;
            $response['biddingData'] = $biddingData;
            echo json_encode($response);
            exit;
        }

        // Token not found or invalid
        echo json_encode(['message' => 'Invalid token']);
        exit;
    }

    public function updateBidStatus($bidId, $status) {
        $stmt = $this->conn->prepare("UPDATE bidding SET status = ? WHERE bid_id  = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => $this->conn->error]);
            exit;
        }
    
        $stmt->bind_param("si", $status, $bidId);
        $result = $stmt->execute();
    
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
            exit;
        }
    
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Bid status updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update bid status']);
        }
        exit;
    }
    
}

$user = new User($conn);

$headers = apache_request_headers();
$authorizationHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authorizationHeader) {
    $token = str_replace('Bearer ', '', $authorizationHeader);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if it's a POST request
        // Retrieve the bidId and status from the URL query parameters
        $bidId = isset($_GET['bid_id']) ? $_GET['bid_id'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        if ($bidId !== null && $status !== null) {
            $user->updateBidStatus($bidId, $status);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
            exit;
        }
    } else {
        // For GET requests, retrieve the user profile
        $user->getUserProfile($token);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing authorization header']);
    exit;
}
?>
