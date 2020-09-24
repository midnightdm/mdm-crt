<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * *
 * ShipPlotterModel class
 * daemon/classes/ShipPlotterModel.php
 *
 */
class ShipPlotterModel extends Dbh {

  public function __construct() {
    parent::__construct();
  }

  public function getStatus() {
    $sql = "select * from shipplotter where id = 1";
    $db  = $this->db();
    $ret = $db->query($sql);
    $row = $ret->fetch();
    return $row;
    //$status['isReachable']  
    //$status['lastUpTS']    
    //$status['lastDownTS']   
  }

  public function serverIsUp($ts) {
    $sql = "update shipplotter set isReachable = true, lastUpTS = ?  WHERE id = 1";
    $db  = $this->db();
    $ret = $db->prepare($sql, $ts);
    $ret->execute();    
  }

  public function serverIsDown($ts) {
    $sql = "update shipplotter set isReachable = false, lastDownTS = ?  WHERE id = 1";
    $db  = $this->db();
    $ret = $db->prepare($sql, $ts);
    $ret->execute();
  }
}
?>