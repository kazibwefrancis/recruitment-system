<?php
require_once dirname(__DIR__) . '/phpmailer/PHPMailer.php';
require_once dirname(__DIR__) . '/phpmailer/SMTP.php';
require_once dirname(__DIR__) . '/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // Uncomment for SMTP debug output
        // $this->mail->SMTPDebug = 2; 
        // $this->mail->Debugoutput = 'html';

        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'francis.b.kaz@gmail.com';
        $this->mail->Password = 'bhaxgnulrbfmcnzs';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;

        $fromAddress = 'francis.b.kaz@gmail.com';
        $fromName = 'Recruitment System'; // or replace 'Recruitment System' with your app name
        $this->mail->setFrom($fromAddress, $fromName);
    }

    public function sendCongratulatoryEmail($to, $firstName, $lastName) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to, $firstName . ' ' . $lastName);
            $this->mail->Subject = "Application Received!";
            $this->mail->isHTML(true);
            $this->mail->Body = "
                <h2>Hi {$firstName} {$lastName},</h2>
                <p>Your application has been received and is under review.</p>
                <br>
                <p>Best regards,<br>The Recruitment Team</p>
            ";
            $this->mail->AltBody = "Hi {$firstName} {$lastName},\n\nYour application has been received and is under review.\n\nBest regards,\nThe Recruitment Team";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            echo "<pre>Mailer Error: {$this->mail->ErrorInfo}</pre>";
            error_log("Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>