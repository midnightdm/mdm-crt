<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* * * * * * * *
 * models/AlertsModel.php
 */

class AlertsModel extends CI_Model {
  function __construct() {
    parent::__construct();
  }

  function getAlertPublish() {
    $data = [];    
    $this->db->select('*');
    $this->db->order_by('apubTS DESC');
    $this->db->limit(20);
    $q = $this->db->get('alertpublish');
    
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[] = $row;        
      }
    }
    $q->free_result();  
    return $data;
  }  

  function getAlertPublishPassenger() {
    $data = [];    
    $this->db->select('*');
    $this->db->from('alertpublish');
    $this->db->join('vessels', 'vessels.vesselID = alertpublish.apubVesselID');
    $this->db->where('vessels.vesselWatchOn', true);
    $this->db->order_by('apubTS DESC');
    $this->db->limit(20);
    $q = $this->db->get();
    
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[] = $row;        
      }
    }
    $q->free_result();  
    return $data;
  }

  function getWaypointEvent($apubID) {
    $data = [];
    $q = $this->db->select('*')->where('apubID', $apubID)->get('alertpublish');
    if($q->num_rows()) {
      return $q->result_array();      
    }
    $q->free_result();  
    return false;
  }

  function getAlertsForDest($dest) {
    //$this->db->select('*');
    //$this->db->where('alertDest', $dest);
    $sql = "select alerts.*, vesselName FROM alerts, vessels WHERE alertDest = ? AND alertVesselID=vesselID";
    $sql2 = "select * from alerts WHERE alertDest = ? and alertVesselID = 'any'";
    $q = $this->db->query($sql, [$dest]);    
    $data = [];   
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[] = $row;        
      }
    }
    $q->free_result();  
    $c = count($data);
    $q = $this->db->query($sql2, [$dest]);
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[$c] = $row;
        $data[$c]['vesselName'] = "";
        $c++;       
      }
    }
    return $data;
  }      
  
  public function saveInboundSms($ts, $msgID, $from, $body, $alogMessageID, $original) {
    $data['smsTS']         = intval($ts);
    $data['smsMsgID']      = $msgID;
    $data['smsFrom']       = $from;
    $data['smsBody']       = $body;
    $data['smsOrigMsgID']  = $alogMessageID;
    $data['smsOrigBody']   = $original;
    $this->db->insert('smsin', $data);    
  }

//Methods for logging Alert messages modified from daemon/classes/AlertsModel.class.php

  public function generateAlertLogSms($clickSendResponse, $smsMessages) {
    //Gets run by api_sendMessages() method of Admin.php page controller {
    $csArr = $clickSendResponse->data->messages;    
    foreach ($smsMessages as $msg) {
      $data = [];
      $data['alogAlertID']   = intval($msg['alertID']);
      $data['alogDirection'] = $msg['dir'];
      $data['alogType']      = $msg['event'];
      $data['alogMessageTo'] = $msg['phone'];
      $data['alogMessageType'] = 'sms';
      $sms = current($csArr);
      while($sms) {
        if($sms->to == $msg['phone']) {
          $data['alogMessageID']     = $sms->message_id;
          $data['alogMessageCost']    = $sms->message_price;
          $data['alogMessageStatus'] = $sms->status;
          $data['alogTS']            = $sms->date;
          break;          
        }
        next($csArr);
      }
      //Test dump
      //error_log("AlertsModel::generateAlertLogSms() test dumping data array...\n". var_dump($data));
      $result = $this->db->insert('alertlog', $data);
      error_log("AlertsModel::generateAlertLogSms() db->insert returned: ".$result);
    }
  }

  public function generateAlertLogNotif($pusherResponse, $notifMessages) {
   //Gets run by api_sendMessages() method of Admin.php page controller
    $now = time();
    foreach($notifMessages as $m) {    
      $data = [
        'alogAlertID'=>substr($m['subject'],9), 
        'alogType'=>$m['event'], 
        'alogTS'=>$now, 
        'alogDirection'=>$m['dir'],
        'alogMessageType'=>'notif',
        'alogMessageTo' =>$m['to'],
        'alogMessageID'=>$pusherResponse->publishId,
        'alogMessageCost'=> "",
        'alogMessageStatus'=>""
      ];
      $this->db->insert('alertlog', $data);
    }
  }


  public function generateAlertLogEmail($clickSendResponse, $emailMessages ) {
    //Gets run by api_sendMessages() method of Admin.php page controller
    //clickSend host portion is depreciated
    //$csArr = $clickSendResponse->data->data;
    foreach ($emailMessages as $msg) {
      $data = [];
      $data['alogAlertID']   = intval($msg['alertID']);
      $data['alogDirection'] = $msg['dir'];
      $data['alogType']      = $msg['event'];
      $data['alogMessageTo'] = $msg['to'];
      $data['alogMessageType'] = 'email';
      //$cs = current($csArr);
      //while($cs) {
      //  if($cs->to->email == $msg['to']) {
      //    $data['alogMessageID']     = $cs->message_id;
      //    $data['alogMessgeCost']    = $cs->price;
      //    $data['alogMessageStatus'] = $cs->status;
      //    $data['alogTS']            = $cs->date_added;
      //    break;          
      //  }
      //  next($csArr);
      //}
      $data['alogMessageID']     = 'N/A';
      $data['alogMessageCost']    = 0.0;
      $data['alogMessageStatus'] = 'N/A';
      $data['alogTS']            = time();
      //Test dump
      //echo "AlertsModel::generateAlertLogEmail() test dumping data array...\n";
      //var_dump($data);
      $this->db->insert('alertlog', $data);
    }
  }


}



?>