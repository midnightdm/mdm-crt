<?php

if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * *
 * Messages class
 * daemon/classes/Messages.class.php
 *
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Messages {
  public $config;
  public $smsApiInstance;
  public $emailApiInstance;
  public $msg;

  function __construct() {
    require_once(__DIR__ . '/../../vendor/autoload.php');
    // Configure HTTP basic authorization: BasicAuth
    $this->config = ClickSend\Configuration::getDefaultConfiguration()
      ->setUsername(getEnv('MDM_CRT_ERR_EML'))
      ->setPassword(getEnv('CLICKSEND_KEY'));
    $this->smsApiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$this->config);
    $this->emailApiInstance = $this->initEmail();
  }
  
  function sendSMS($messages) { //$messages needs to be assoc. array
    $msgs = [];
    foreach($messages as $m)  {   
      $msg = new \ClickSend\Model\SmsMessage();
      $msg->setBody($m['text']); 
      $msg->setTo($m['phone']);
      $msg->setSource("sdk");
      $msgs[] = $msg;
    }

    // \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
    $sms_messages = new \ClickSend\Model\SmsMessageCollection(); 
    $sms_messages->setMessages($msgs);

    try {
        $result = $this->smsApiInstance->smsSendPost($sms_messages);
        return $result;
    } catch (Exception $e) {
        echo 'Exception when calling SMSApi->smsSendPost: ', $e->getMessage(), PHP_EOL;
    }
  }
  
  function sendEmail($messages) { //$messages needs to be assoc. array
    foreach($messages as $m) {
      $this->emailApiInstance->Subject = $m['subject'];
      $this->emailApiInstance->Body    = $m['text'];
      $this->emailApiInstance->AddAddress($m['to']);
      try {
        $this->emailApiInstance->Send();
      } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$this->emailApiInstance->ErrorInfo}"; 
      }      
    }
  }

  public function initEmail() {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug  = 2;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Host = "smtp.gmail.com";
    $mail->Port = "587";
    $mail->Username = getEnv('CRT_GMAIL_USERNAME');
    $mail->Password = getEnv('CRT_GMAIL_PASSWORD');
    $mail->SetFrom(getEnv('CRT_GMAIL_USERNAME'));
    return $mail;
  }
}  
?>
