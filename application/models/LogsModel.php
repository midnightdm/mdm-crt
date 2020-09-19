<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LogsModel extends CI_Model {
  function __constructor() {
    parent::CI_Model;
  }

  function getVesselDataList() {
    $data = [];
    //$sql  = "SELECT * from vessels";
    $this->db->select('*');
    $this->db->from('vessels');
    $this->db->order_by('vesselName');
    $q = $this->db->get();
    
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[] = $row;        
      }
    }
    $q->free_result();
    //$data['vesselImageUrl'] = "http://mdm-crt.s3-website.us-east-2.amazonaws.com/vessels/mmsi" . $data['vesselID'] .".jpg";
    return $data;
  }

  function getPassagesInTimeRange($rangeArr) {
    $data = [];
    $sql = 'select passages.*, vesselName, vesselType, vesselHasImage, vesselImageUrl '
         . 'from passages, vessels '
         . 'where passageVesselID=vesselID and passageMarkerCharlieTS between ? and ?';
    $q =$this->db->query($sql, $rangeArr);    
    if($q->num_rows()>0) {
      foreach($q->result() as $row) {
        $data[] = $row;        
      }  
      $q->free_result();
      return $data;
    } else {
      return false;
    }
    
  }
  
}