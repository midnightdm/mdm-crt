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
    $this->db->join('watchlist', 'watchlist.watchVesselID = alertpublish.apubVesselID');
    $this->db->where('watchlist.watchOn', true);
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
}
?>