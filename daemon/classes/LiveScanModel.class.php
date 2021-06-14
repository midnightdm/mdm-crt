<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * *
 * LiveScanModel class
 * daemon/classes/LiveScanModel.class.php
 *
 */

class LiveScanModel extends Dbh {

  public function __construct() {
    parent::__construct();
  }

  public function getAllLiveScans() {
    $db = $this->db();
    return $db->query('SELECT * FROM live')->fetchAll();
  }

  public function getAllLivePlots() {
    $db = $this->db();
    return $db->query('SELECT * FROM plot')->fetchAll();
  }

  public function insertLiveScan($dataArr) {
    $db = $this->db();
    $sql = "INSERT INTO live (liveInitTS, liveLastTS, liveInitLat, liveInitLon, liveDirection, liveLocation, liveVesselID, liveName, liveLength, liveWidth, liveDraft, liveCallSign, liveSpeed, liveCourse, liveDest, liveIsLocal) VALUES (:liveInitTS, :liveLastTS, :liveInitLat, :liveInitLon, :liveDirection, :liveLocation, :liveVesselID, :liveName, :liveLength, :liveWidth, :liveDraft, :liveCallSign, :liveSpeed, :liveCourse, :liveDest, :liveIsLocal)";
     $ret = $db->prepare($sql);
     $ret->execute($dataArr);
     $liveID = $db->lastInsertID();
     //$ret = null;
     //echo "{$dataArr['liveName']} added to db liveID= $liveID , errorCode= ".var_dump($ret->errorInfo())."\n";
     unset($db);
     return $liveID;    
  }

  public function updateLiveScan($dataArr) {
    $db = $this->db();
    $sql = "UPDATE live SET liveLastTS = :liveLastTS, liveLastLat = :liveLastLat, "
      . "liveLastLon = :liveLastLon, liveDirection = :liveDirection,"
      . "liveLocation= :liveLocation, liveName = :liveName, "
      . "liveSpeed = :liveSpeed, liveDest = :liveDest, liveCourse = :liveCourse, "      
      . "liveMarkerAlphaWasReached = :liveMarkerAlphaWasReached, "
      . "liveMarkerBravoWasReached = :liveMarkerBravoWasReached, "
      . "liveMarkerCharlieWasReached = :liveMarkerCharlieWasReached, "
      . "liveMarkerDeltaWasReached = :liveMarkerDeltaWasReached, "
      . "liveMarkerAlphaTS = :liveMarkerAlphaTS, liveMarkerBravoTS = :liveMarkerBravoTS, "
      . "liveMarkerCharlieTS = :liveMarkerCharlieTS, liveMarkerDeltaTS = :liveMarkerDeltaTS, livePassageWasSaved = :livePassageWasSaved "
      . "WHERE liveVesselID = :liveVesselID";
    $ret = $db->prepare($sql);
    $db->beginTransaction();
    //echo "updateLiveScan() data: ".var_dump($dataArr);
    $ret->execute($dataArr);
    $db->commit();
    $c = $ret->rowCount();
    $ret = null;
    //echo "LiveScanModel::updateLiveScan() rows updated= $c for {$dataArr['liveName']} \n";
    
  }

  public function deleteLiveScan($liveID) {
    $db = $this->db();
    $sql = "DELETE FROM live WHERE liveID = ?";
    $ret = $db->prepare($sql);
    $ret->execute([$liveID]);
    $c = $ret->rowCount();
    return $c;
  }

  public function getRecentScan($vesselID) {
    $db = $this->db();
    $q  = $db->prepare('SELECT * FROM recent WHERE liveVesselID = ?');
    $q->execute([$vesselID]);
    if ($q->rowCount()) { 
      $data = $q->fetchAll();
      return $data[0];
    }
    return false;  
  }

  public function getRecentIDFor($vesselID) {
    $db = $this->db();
    $q = $db->prepare("SELECT liveID FROM recent WHERE liveVesselID = ?");
    $q->execute([$vesselID]);
    if($q->rowCount()) {
      $data = $q->fetch();
      echo var_dump($data);
      return $data["liveID"];
    }
  }

  public function saveRecentScan($obj) {
    $keys = ['liveInitTS', 'liveInitLat', 'liveInitLon', 'liveLastTS', 'liveLastLat', 'liveLastLon', 'liveDirection', 'liveVesselID', 'liveName', 'liveMarkerAlphaWasReached', 'liveMarkerAlphaTS','liveMarkerBravoWasReached', 'liveMarkerBravoTS', 'liveMarkerCharlieWasReached', 'liveMarkerCharlieTS','liveMarkerDeltaWasReached', 'liveMarkerDeltaTS', 'liveSpeed',  'liveDest',
    'liveWidth','liveLength', 'liveCallSign', 'liveDraft','liveCourse',  'livePassageWasSaved', 'liveIsLocal'];
    //Put object data into array format
    $data = array();
    foreach($keys as $key) {
      $data[$key] = $obj->$key;
    }
    $data['liveLocation'] = $obj->liveLocation->description;
    //Get liveID to update if a past record for this vessel exits
    $liveID = $this->getRecentIDFor($obj->liveVesselID);
    if($liveID) {
      $data['liveID'] = $liveID;  
      $this->updateRecentScan($data);
      return null;
    } else {
      return $this->insertRecentScan($data); //Returns liveID
    }
  }

  public function insertRecentScan($dataArr) {  //VERIFY THESE
    $db = $this->db();
    $sql = "INSERT INTO recent (liveInitTS, liveInitLat, liveInitLon, liveLastTS, liveLastLat, liveLastLon, liveDirection,  liveLocation, liveVesselID, liveName, liveMarkerAlphaWasReached, liveMarkerAlphaTS, liveMarkerBravoWasReached, liveMarkerBravoTS,
    liveMarkerCharlieWasReached, liveMarkerCharlieTS, liveMarkerDeltaWasReached, liveMarkerDeltaTS, liveSpeed, liveDest,
    liveWidth, liveLength, liveCallSign, liveDraft,  liveCourse, livePassageWasSaved, liveIsLocal ) VALUES (:liveInitTS, :liveInitLat, :liveInitLon, :liveLastTS, :liveLastLat, :liveLastLon, :liveDirection, :liveLocation, :liveVesselID, :liveName, :liveMarkerAlphaWasReached, :liveMarkerAlphaTS, :liveMarkerBravoWasReached, :liveMarkerBravoTS,
    :liveMarkerCharlieWasReached, :liveMarkerCharlieTS, :liveMarkerDeltaWasReached, :liveMarkerDeltaTS, :liveSpeed, :liveDest,
    :liveWidth, :liveLength, :liveCallSign, :liveDraft,  :liveCourse, :livePassageWasSaved, :liveIsLocal)";
     $ret = $db->prepare($sql);
     $ret->execute($dataArr);
     $liveID = $db->lastInsertID();
     unset($db);
     return $liveID;    
  }

  public function updateRecentScan($dataArr) {
    $db = $this->db();
    $sql = "UPDATE recent SET 
    liveInitTS = :liveInitTS,
    liveInitLat = :liveInitLat, 
    liveInitLon = :liveInitLon, 
    liveLastTS = :liveLastTS, 
    liveLastLat = :liveLastLat, 
    liveLastLon = :liveLastLon,
    liveDirection = :liveDirection,
    liveLocation = :liveLocation,
    liveVesselID = :liveVesselID, 
    liveName = :liveName, 
    liveMarkerAlphaWasReached = :liveMarkerAlphaWasReached,
    liveMarkerAlphaTS = :liveMarkerAlphaTS, 
    liveMarkerBravoWasReached = :liveMarkerBravoWasReached,
    liveMarkerBravoTS = :liveMarkerBravoTS,
    liveMarkerCharlieWasReached = :liveMarkerCharlieWasReached,
    liveMarkerCharlieTS = :liveMarkerCharlieTS,
    liveMarkerDeltaWasReached = :liveMarkerDeltaWasReached, 
    liveMarkerDeltaTS = :liveMarkerDeltaTS,
    liveSpeed = :liveSpeed,
    liveDest = :liveDest,
    liveWidth = :liveWidth,
    liveLength = :liveLength,
    liveCallSign = :liveCallSign,
    liveDraft = :liveDraft, 
    liveCourse = :liveCourse,
    livePassageWasSaved = :livePassageWasSaved,
    liveIsLocal = :liveIsLocal,  
    WHERE liveID = :liveID";
    
    $ret = $db->prepare($sql);
    $db->beginTransaction();
    $ret->execute($dataArr);
    $db->commit();
    $c = $ret->rowCount();
    $ret = null;   
  }


  
}
