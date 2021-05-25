<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}


function getTimeOffset() {
    $tz = new DateTimeZone("America/Chicago");
    $dt = new DateTime();
    $dt->setTimeZone($tz);
    return $dt->format("I") ? -18000 : -21600;
}

echo "Time offset = ".getTimeOffset();

include_once('classes/Dbh.class.php');

class Passages {
    $passageVesselID;
    $passageDirection;
    $passageMarkerAlphaTS;
    $passageMarkerBravoTS;
    $passageMarkerCharlieTS;
    $passageMarkerDeltaTS;
    $fillCount;
    $callBack;
    $vesselName;

    public function __construct($vesselID, $vesselName, $direction, $callBack) {
        $this->passageDirection = $direction;
        $this->fillCount = 0;
        $this->callBack = $callBack
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
                    return;
                }
                $this->passageMarkerAlphaTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                break;
            }
            case 'bravo' : {
                if($ts == $this->passageMarkerBravoTS) {
                    //Don't process duplicate
                    return;
                }
                $this->passageMarkerBravoTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                break;
            }
            case 'charlie': {
                if($ts == $this->passageMarkerCharlieTS) {
                    //Don't process duplicate
                    return;
                }
                $this->passageMarkerCharlieTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                break;
            }
            case 'delta' : {
                if($ts == $this->passageDeltaBravoTS) {
                    //Don't process duplicate
                    return;
                }
                $this->passageMarkerDeltaTS = $ts; 
                $this->fillCount++;
                if($this->fillCount==4) {
                    $this->writePassage();
                }
                break;
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

function passageDone($vesselID, $vesselName, $ts) {
    echo "Passage of ".$vesselName." on ".date('c', $ts+getTimeOffset())." ".$dir."\r\n";
    unset($messages[$vesselID]);
}

$messages = array();

public function getAlertPublish() {
    $db = $this->db();
    $sql = "select * from alertpublish where apubTS between 1621660841 and 1621902094 order by apubTS desc limit 500;";
    $q1 = $db->query($sql);
    
    //Get data for new found messages     
    $publishData    = $q1->fetchAll();
    unset($db);


    //Loop through publish data to get elements for next searches
    foreach($publishData as $row) {
      $alertID   = $row['apubID'];
      $txt       = $row['apubText'];
      $vesselID  = $row['apubVesselID'];
      $name      = $row['apubVesselName'];
      $event     = $row['apubEvent'];
      $dir       = $row['apubDir'];

      if(isset($messages[$vesselID]))
    }
}
?>