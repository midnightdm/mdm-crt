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
    return $data;
  }
}