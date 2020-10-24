<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* * * * * *
 * ShipPlotterModel class
 * models/ShipPlotterModel.php
 *
 */
class ShipPlotterModel extends CI_Model {

  public function __construct() {
    parent::__construct();
  }

  public function getStatus() {
    $sql = "select * from shipplotter where id = 1";
    $q = $this->db->query($sql);
    $data = $q->result_array();
    return $data;
    //$data->isReachable  
    //$data->lastUpTS   
    //$data->lastDownTS
  }
}
?>



