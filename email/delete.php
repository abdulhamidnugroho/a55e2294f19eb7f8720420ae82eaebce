<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// include database and object file
include_once '../config/database.php';
include_once '../objects/email.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// prepare email object
$email = new Email($db);
 
// get email id
$data = json_decode(file_get_contents("php://input"));
 
// set email id to be deleted
$email->id = $data->id;
 
// delete the email
if ($email->delete()) {
    echo '{';
        echo '"message": "Email was deleted."';
    echo '}';
} else {
    echo '{';
        echo '"message": "Unable to delete object."';
    echo '}';
}
?>