<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VesselsModel extends CI_Model {
  function __constructor() {
    parent::CI_Model;
  }

  function getVessel($id) {
    $this->db->select('*');
    //$this->db->from('vessels');
    $this->db->where('vesselID', $id);
    $q = $this->db->get('vessels');
    if($q->num_rows()) {
      $data = $q->row();
      $q->free_result();
      return $data;
    }    
    return false;
  }
}  