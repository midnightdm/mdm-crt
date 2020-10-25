<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* * * * * * * *
 * models/AlertsModel.php
 */

class AlertsModel extends CI_Model {
  function __constructor() {
    parent::__constructor();
  }

  function getAlertPublish() {
    $data = [];
    //$sql  = "SELECT * from vessels";
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

  public function saveInboundSms($ts, $msgID, $from, $body, $alogMessageID, $original) {
    $sql = "INSERT INTO smsin (smsTS, smsMsgID, smsFrom, smsBody, smsOrigMsgID, smsOrigBody) VALUES "
       . "(:smsTS, :smsMsgID, :smsFrom, :smsBody, :smsOrigMsgID, :smsOrigBody)";
    $data['smsTS']         = intval($ts);
    $data['smsMsgID']      = $msgID;
    $data['smsFrom']       = $from;
    $data['smsBody']       = $body;
    $data['smsOrigMsgID']  = $alogMessageID;
    $data['smsOrigBody']   = $original;
    $this->db->insert('smsin', $data);    
  }
}
?>