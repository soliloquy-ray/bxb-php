<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

class Mail{

	private $sendgrid_api;
	private $mail;
    private static $_instance;

	public static function getInstance(){
		if(!self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct($api = ''){
		$this->sendgrid_api = $api;
		$this->mail = new PHPMailer(true);
	    $this->mail->SMTPDebug = 0;                                 // Enable verbose debug output
	    $this->mail->isSMTP();                                      // Set mailer to use SMTP
	    $this->mail->Host = 'smtp.sendgrid.net;smtp2.example.com';  // Specify main and backup SMTP servers
	    $this->mail->SMTPAuth = true;                               // Enable SMTP authentication
	    $this->mail->Username = 'apikey';                 // SMTP username
	    $this->mail->Password = $this->sendgrid_api;                      // SMTP password
	    $this->mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	    $this->mail->Port = 587;        
	}

    // Magic method clone is empty to prevent duplication of connection
    private function __clone(){}

    public function send($from, $to, $subject, $content){
		try {

		    //Recipients
		    $this->mail->setFrom($from);
		    $this->mail->addAddress($to);     // Add a recipient
		    //$mail->addAddress('ellen@example.com');               // Name is optional
		    $this->mail->addReplyTo($to);
		    //$mail->addCC('cc@example.com');
		    //$mail->addBCC('bcc@example.com');

		    //Attachments
		    //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

		    //Content
		    $this->mail->isHTML(true);                                  // Set email format to HTML
		    $this->mail->Subject = $subject;
		    $this->mail->Body    = $content;
		    $this->mail->AltBody = $content;

		    return $this->mail->send();
		    
		    return true;
		} catch (Exception $e) {
		    return false;
		}
    }
	
}
?>