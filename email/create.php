<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// get database connection
include_once '../config/database.php';
 
// instantiate email object
include_once '../objects/email.php';
 
$database = new Database();
$db = $database->getConnection();
 
$email = new Email($db);
 
// get posted data
$data = json_decode(file_get_contents("php://input"));
 
// set email property values
$email->sender = $data->sender;
$email->recipients = $data->recipients;
$email->cc = isset($data->cc) ? $data->cc : NULL;
$email->bcc = isset($data->bcc) ? $data->cc : NULL;
$email->subject = isset($data->subject) ? $data->subject : NULL;
$email->body = $data->body;
$email->alt_body = isset($data->alt_body) ? $data->alt_body : NULL;
$email->created_at = date('Y-m-d H:i:s');
 
// create the email
if ($email->create()) {
    echo '{';
        echo '"message": "Email was created."';
    echo '}';
} else {
    echo '{';
        echo '"message": "Unable to create email."';
    echo '}';
}

?>