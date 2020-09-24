<?php

if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * *
 * Messages class
 * daemon/classes/Messages.class.php
 *
 */

class Messages {
  public $config;
  public $apiInstance;
  public $msg;

  function __construct() {
    require_once(__DIR__ . '/../../vendor/autoload.php');
    // Configure HTTP basic authorization: BasicAuth
    $this->$config = ClickSend\Configuration::getDefaultConfiguration()
      ->setUsername(getEnv('MDM_CRT_ERR_EML'))
      ->setPassword(getEnv('CLICKSEND_KEY'));
    $this->apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$this->config);
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
        $result = $this->apiInstance->smsSendPost($sms_messages);
        print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling SMSApi->smsSendPost: ', $e->getMessage(), PHP_EOL;
    }
  }
  
}  



?>
