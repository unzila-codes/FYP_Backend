<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__.'/CONFI/connection.php';
require __DIR__.'/CONFI/JwtHandler.php';


function msg($success, $status, $message, $extra = []) {
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ], $extra);
}

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT EQUAL TO POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = msg(0, 404, 'Page Not Found!');
} else {
    // CHECKING EMPTY FIELDS
    if (!isset($data->email) || !isset($data->password) || empty(trim($data->email)) || empty(trim($data->password))) {
        $fields = ['fields' => ['email', 'password']];
        $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);
    } else {
        $email = trim($data->email);
        $password = trim($data->password);

        // CHECKING THE EMAIL FORMAT (IF INVALID FORMAT)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $returnData = msg(0, 422, 'Invalid Email Address!');
        } else {
            // IF PASSWORD IS LESS THAN 8 THEN SHOW THE ERROR
            if (strlen($password) < 8) {
                $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');
            } else {
                $fetch_user_by_email = "SELECT * FROM `demo_user` WHERE `email`=?";
                $query_stmt = $conn->prepare($fetch_user_by_email);
                $query_stmt->bind_param("s", $email);
                $query_stmt->execute();

                $query_result = $query_stmt->get_result();

                // IF THE USER IS FOUND BY EMAIL
                if ($query_result->num_rows > 0) {
                    $row = $query_result->fetch_assoc();
                    $check_password = password_verify($password, $row['PASSWORD']);

                    // VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
                    // IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
                    if ($check_password) {
                        $jwt = new JwtHandler();
                        $user_id = $row['id']; // Fetch the user ID from $row

                        $token = $jwt->jwtEncodeData(
                            'http://localhost/FYP/',
                       
                            array("user_id" => $user_id)
                            // Include the user ID in the token payload
                        );

                        $returnData = [
                            'success' => 1,
                            'message' => 'You have successfully logged in.',
                            'token' => $token,
                           //'user_id' => $row['id'],
                        //   'user_id' => $user_id
                            // 'user_id' => (string)$user_id
                            'user_id' => $user_id // Add the user ID to the response

 

                        ];
                    } else {
                        $returnData = msg(0, 422, 'Invalid Password!');
                    }
                } else {
                    $returnData = msg(0, 422, 'Invalid Email Address!');
                }
            }
        }
    }
}

echo json_encode($returnData);
?>
