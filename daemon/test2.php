<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}

//Functions

function getTimeOffset() {
    $tz = new DateTimeZone("America/Chicago");
    $dt = new DateTime();
    $dt->setTimeZone($tz);
    return $dt->format("I") ? -18000 : -21600;
}

function passageDone($vesselID, $vesselName, $ts) {
    echo "Passage of ".$vesselName." on ".date('c', $ts+getTimeOffset())." ".$dir."\r\n";
    unset($messages[$vesselID]);
}

function getAlertPublish() {
    $db = $this->db();
    $sql = "select * from alertpublish where apubTS between 1621660841 and 1621902094 order by apubTS desc limit 500;";
    $q1 = $db->query($sql);
    
    //Get data for new found messages     
    $publishData    = $q1->fetchAll();
    unset($db);
    $count = 0;
    echo "Starting getAlertPublsh().\r\n";

    //Loop through publish data 
    foreach($publishData as $row) {
      $alertID   = $row['apubID'];
      $txt       = $row['apubText'];
      $vesselID  = $row['apubVesselID'];
      $name      = $row['apubVesselName'];
      $event     = $row['apubEvent'];
      $dir       = $row['apubDir'];
      $ts        = $row['apubTS'];

      if(isset($messages[$vesselID])) {
          $return = $messages[$vesselID]->update($event, $dir, $ts);
          if($return) {
              $count++;
              echo "Process ".$count."\r\n"; 
          } else {
              echo "Skipped\r\n";
          }
      } else {
          $messages[$vesselID] = new Passages($vesselID, $name, $dir);
          echo "New object\r\n";
      }
    }
    echo "Process finished.\r\n";
}

include_once('classes/Dbh.class.php');


//Class
class Passages {
    public $passageVesselID;
    public $passageDirection;
    public $passageMarkerAlphaTS;
    public $passageMarkerBravoTS;
    public $passageMarkerCharlieTS;
    public $passageMarkerDeltaTS;
    public $fillCount;
    public $vesselName;

    public function __construct($vesselID, $vesselName, $direction) {
        $this->passageVesselID = $vesselID;
        $this->vesselName      = $vesselName;
        $this->passageDirection = $direction;
        $this->fillCount = 0;
    }
    
    public function update($event, $direction, $ts) {
        if($direction != $this->passageDirection) {
            return false;
        }
        switch($event) {
            case 'detected' : {
                return false;
            }
            case 'alpha' : {
                if($ts == $this->passageMarkerAlphaTS) {
                    //Don't process duplicate
                    return false;
                }
                $this->passageMarkerAlphaTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                return true;
            }
            case 'bravo' : {
                if($ts == $this->passageMarkerBravoTS) {
                    //Don't process duplicate
                    return false;
                }
                $this->passageMarkerBravoTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                return true;
            }
            case 'charlie': {
                if($ts == $this->passageMarkerCharlieTS) {
                    //Don't process duplicate
                    return false;
                }
                $this->passageMarkerCharlieTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                return true;
            }
            case 'delta' : {
                if($ts == $this->passageDeltaBravoTS) {
                    //Don't process duplicate
                    return false;
                }
                $this->passageMarkerDeltaTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                return true;
            }
        }
    }

    public function writePassage() {
        $data = array(
            'passageVesselID' => $this->passageVesselID,
            'passageDirection' => $this->passageDirection,
            'passageMarkerAlphaTS' => $this->passageMarkerAlphaTS,
            'passageMarkerBravoTS' => $this->passageMarkerBravoTS,
            'passageMarkerCharlieTS' => $this->passageMarkerCharlieTS,
            'passageMarkerDeltaTS' => $this->passageMarkerDeltaTS
        );
        //$db = $this->db();
        //$ret = $db->prepare($sql)      ;
        //$ret->execute($data);
        //$c = $ret->rowCount();
        passageDone($this->passageVesselID, $this->vesselName, $this->passageMarkerAlphaTS, $this->passageDirection);

    }

}


$messages = array();

//Run program
getAlertPublish();
?>