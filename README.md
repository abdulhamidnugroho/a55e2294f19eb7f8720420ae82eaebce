# email rest api (PHP)

// specify your own database credentials
private $host = "localhost";
private $db_name = "email_rest";
private $username = "root";
private $password = "";

API will return formate in Json.

Below API content

1.0 Sending Email
1.1 <http://localhost:9090/email/create.php>

payload (JSON):
    sender (string)
    recipients (array of string)
    cc (string)
    bcc (string)
    subject (string)
    body (string)
    alt_body (string)
