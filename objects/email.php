<?php
include_once '../shared/utilities.php';

class Email {
    const SMTP_HOST = "email_host";
    const SMTP_AUTH = TRUE;
    const SMTP_USER = "";
    const SMTP_PASSWORD = "";
    const MAX_VALUE = "";
 
    // database connection and table name
    private $conn;
    private $table_name = "emails";
 
    // object properties
    public $id;
    public $sender;
    public $recipients;
    public $cc;
    public $bcc;
    public $subject;
    public $body;
    public $alt_body;
    public $created_at;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read emails
    function read(){
        // select all query
        $query = "SELECT
                    c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
                FROM
                    " . $this->table_name . " p
                    LEFT JOIN
                        categories c
                            ON p.category_id = c.id
                ORDER BY
                    p.created DESC";
     
        // prepare query statement
        $stmt = $this->conn->prepare($query);
     
        // execute query
        $stmt->execute();
     
        return $stmt;
    }

    // create email
    function create() {

        try {
            $db = $this->conn;

            $db->beginTransaction();

            $email_data = [
                'sender' => $this->sender,
                'recipients' => $this->recipients,
                'cc' => $this->cc,
                'bcc' => $this->bcc,
                'subject' => $this->subject,
                'body'  => $this->body,
                'alt_body' => $this->alt_body
            ];

            if (! $this->emailHandler($email_data)) {
                return false;
            }
            
            // query to insert record
            $query = "INSERT INTO
            " . $this->table_name . "
                SET
                    id=:id, sender=:sender, cc=:cc, bcc=:bcc, subject=:subject, body=:body, alt_body=:alt_body, created_at=:created_at";

            // prepare query
            $stmt = $db->prepare($query);

            $utilities = new Utilities;
            $email_uuid = $utilities->uuidGenerator();

            $this->id=$email_uuid;

            // sanitize
            $this->body=htmlspecialchars(strip_tags($this->body));

            // bind values
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":sender", $this->sender);
            $stmt->bindParam(":cc", $this->cc);
            $stmt->bindParam(":bcc", $this->bcc);
            $stmt->bindParam(":subject", $this->subject);
            $stmt->bindParam(":body", $this->body);
            $stmt->bindParam(":alt_body", $this->alt_body);
            $stmt->bindParam(":created_at", $this->created_at);

            // execute query
            $stmt->execute();

            $recipients = $this->recipients;
            $recipients_data = [];

            foreach ($recipients as $recipient) {
            $temp = [];
            $temp_uuid = $utilities->uuidGenerator();
            array_push($temp, $temp_uuid, $email_uuid, $recipient);
            $recipients_data[] =  $temp;
            }

            $row_length = count($recipients_data[0]);
            $nb_rows = count($recipients_data);
            $length = $nb_rows * $row_length;

            /* Fill in chunks with '?' and separate them by group of $row_length */
            $args = implode(',', array_map(
                    function($el) { return '('.implode(',', $el).')'; },
                    array_chunk(array_fill(0, $length, '?'), $row_length)
                ));

            $params = [];
            foreach($recipients_data as $row) {
            foreach($row as $value) {
                $params[] = $value;
            }
            }

            $query = "INSERT INTO email_recipients (id, email_id, recipient) VALUES ".$args;
            $stmt = $db->prepare($query);
            $stmt->execute($params);

            $db->commit();
        } catch (\PDOException $e) {
            // rollback the transaction
            $db->rollBack();
        
            // show the error message
            die($e->getMessage());

            return false;
        }

        return true;
    }
    
    function delete() {
        // delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
     
        // prepare query
        $stmt = $this->conn->prepare($query);
     
        // bind id of record to delete
        $stmt->bindParam(1, $this->id);
     
        // execute query
        if($stmt->execute()){
            return true;
        }
     
        return false;
    }

    public function emailHandler($email_data) {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;              //Enable verbose debug output
            $mail->isSMTP();                                    //Send using SMTP
            $mail->Host       = self::SMTP_HOST;                //Set the SMTP server to send through
            $mail->SMTPAuth   = self::SMTP_AUTH;                //Enable SMTP authentication
            $mail->Username   = self::SMTP_USER;                //SMTP username
            $mail->Password   = self::SMTP_PASSWORD;            //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    //Enable implicit TLS encryption
            $mail->Port       = 465;                            //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($email_data->sender);
            
            foreach ($email_data->recipients as $val) {
                $mail->addAddress($val);
            }
            
            $mail->addCC($email_data->cc);
            $mail->addBCC($email_data->bcc);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $email_data->subject;
            $mail->Body    = $email_data->body;
            $mail->AltBody = $email_data->alt_body;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}, {$e->getMessage()}";
        
            return false;
        }

        return true;
    }
}