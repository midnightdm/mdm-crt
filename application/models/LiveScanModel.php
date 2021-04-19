<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* * * * * * * *
 * models/LiveScanModel.php
 */

 class LiveScanModel extends CI_Model {
  /* function __construct() {
    parent::CI_Model;
  }
  */
  function getAllScans() {
    $data = [];
    $q = $this->db->get('live');
    if($q->num_rows()) {
      foreach($q->result() as $row) {
        $data[] = $row;
      }
    }
    $q->free_result();
    return $data;
  }
}  