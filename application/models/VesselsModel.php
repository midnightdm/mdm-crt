<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* * * * * *
 * VesselsModel class
 * models/VesselsModel.php
 *
 */

class VesselsModel extends CI_Model {
  /*
  function __construct() {
    parent::CI_Model;
  }
  */
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

  function getVesselImageUrl($id) {
    $this->db->select('vesselHasImage, vesselImageUrl');
    $this->db->where('vesselID', $id);
    $q = $this->db->get('vessels');
    if($q->num_rows()) {
      //Sustitute image placeholder if vesselHasImage is false
      if($q->row()->vesselHasImage == false) {
        $q->row()->vesselImageUrl = getenv('BASE_URL')."images/vessels/no-image-placard.jpg";
      }
      $data = $q->row()->vesselImageUrl;
      $q->free_result();
      return $data;
    }    

  }
}  