<?php
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
 
require 'vendor/autoload.php';

function sent_noti_email($to, $subject, $body) {

    $mail = new PHPMailer(true);
 
    try {
        $mail->SMTPDebug = 0;                                      
        $mail->isSMTP();                                           
        $mail->Host       = 'usersmtphost;';                   
        $mail->SMTPAuth   = true;                            
        $mail->Username   = 'username';                
        $mail->Password   = 'password';                       
        $mail->SMTPSecure = 'tls';                             
        $mail->Port       = 587; 
    
        $mail->setFrom('username', 'DB Backup');          
        $mail->addAddress('receiver@example.com');
        
        $mail->isHTML(true);                                 
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = 'Body in plain text for non-HTML mail clients';
        $mail->send();
        echo "Mail has been sent successfully!";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

}
 ?>