<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * LiveScan Class
 * daemon/classes/livescan.class.php
 * 
 */

class LiveScan {
  public $liveID;
  public $liveInitTS;
  public $liveInitLat;
  public $liveInitLon;
  public $liveLastTS = null;
  public $liveLastLat = null;
  public $liveLastLon = null;
  public $liveDirection = 'undetermined';
  public $liveName;
  public $liveVesselID;
  public $liveVessel = null;
  public $liveMarkerAlphaWasReached = FALSE;
  public $liveMarkerAlphaTS;
  public $liveMarkerBravoWasReached = FALSE;
  public $liveMarkerBravoTS;
  public $liveMarkerCharlieWasReached = FALSE;
  public $liveMarkerCharlieTS;
  public $liveMarkerDeltaWasReached = FALSE;
  public $liveMarkerDeltaTS;
  public $liveCallSign;
  public $liveDest;
  public $liveEta;
  public $liveSpeed;
  public $liveCourse;
  public $liveLength;
  public $liveWidth;
  public $liveDraft;
  public $livePassageWasSaved = false;
  public $liveIsLocal;
  public $callBack;
  public $lookUpCount = 0;

  public function __construct($ts, $name, $id, $lat, $lon, $speed, $course, $dest, $length, $width, $draft, $callsign, $cb, $reload=false, $reloadData=[]) {
    $this->callBack = $cb;
    if ($reload) {
      foreach ($reloadData as $attribute => $value) {        
        $this->$attribute = $value;
        if($attribute=='liveName') {
          echo "  Reloading ".$value." from DB.\n";
        }
      }      
    } else {
      $this->setTimestamp($ts, 'liveInitTS');
      $this->setTimestamp($ts, 'liveLastTS');
      $this->liveName = $name;
      $this->liveVesselID = $id;
      $this->liveIsLocal = in_array($id, $this->callBack->localVesselFilter);      
      $this->liveInitLat = $lat;
      $this->liveInitLon = $lon;
      $this->liveSpeed = $speed;
      $this->liveCourse = $course;
      $this->liveDest = $dest;
      $this->liveLength = $length;
      $this->liveWidth = $width;
      $this->liveDraft = $draft;
      $this->liveCallSign = $callsign;      
      $this->lookUpVessel();
      $this->insertNewRecord();    
      $this->callBack->AlertsModel->triggerDetectEvent($this);
    }   
  }

  public function setTimestamp($ts, $attribute) {
    $test = ['liveLastTS', 'liveInitTS', 'liveMarkerAlphaTS', 'liveMarkerBravoTS', 'liveMarkerCharlieTS', 'liveMarkerDeltaTS'];
    if(!in_array($attribute, $test)) { 
      $errMsg = "Invalid attribute: " . $attribute . " in LiveScan::setTimeStamp().";
      throw new Exception($errMsg);
      return null;  
    }
    if(is_int($ts) && $ts > 1506800000) {
      $this->$attribute = $ts;
    } elseif ($unixTS = strtotime($ts)) {
        $this->$attribute = $unixTS;
    } else {
        $errMsg = "Invalid timestamp " . $ts . " in LiveScan:setTimeStamp().";
        throw new Exception($errMsg);
    }
  }

  public function insertNewRecord() {
    $data = [];
    $data['liveInitTS'] = $this->liveInitTS;
    $data['liveLastTS'] = $this->liveLastTS;
    $data['liveInitLat'] = $this->liveInitLat;
    $data['liveInitLon'] = $this->liveInitLon;
    $data['liveDirection'] = $this->liveDirection;
    $data['liveVesselID'] = $this->liveVesselID;
    $data['liveName'] = $this->liveName;
    $data['liveLength'] = $this->liveLength;
    $data['liveWidth'] = $this->liveWidth;
    $data['liveDraft'] = $this->liveDraft;
    $data['liveCallSign'] = $this->liveCallSign;
    $data['liveSpeed'] = $this->liveSpeed;
    $data['liveCourse'] = $this->liveCourse;
    $data['liveDest'] = $this->liveDest;
    $data['liveIsLocal'] = $this->liveIsLocal;
    echo 'Inserting new livescan record for '.$this->liveName .' '.getNow()."\n";
    $this->liveID = $this->callBack->LiveScanModel->insertLiveScan($data);
  }

  public function update($ts, $name, $id, $lat, $lon, $speed, $course, $dest) {
    //Is this first update after init?
    if($this->liveLastLat == null) {
      //Yes. Then update TS.
      $this->setTimestamp($ts, 'liveLastTS');      
    } else {
      //No. Has position changed?
      if($this->liveLastLat != $lat && $this->liveLastLon != $lon) {
        //Yes. Then update TS.
        $this->setTimestamp($ts, 'liveLastTS'); 
      } //No. Then do nothing keeping last TS.
    }    
    
    $this->liveLastLat = $lat;
    $this->liveLastLon = $lon;
    $this->liveSpeed = $speed;
    $this->liveCourse = $course;
    $this->liveDest = $dest;
    $this->determineDirection();
    $this->checkMarkerPassage();
    if(is_null($this->liveVessel) && $this->lookUpCount < 5) {
      $this->lookUpVessel();
    }
    $this->savePassageIfComplete();
    $this->updateRecord();
  }

  public function updateRecord() {
    $data = [];
    $data['liveLastTS'] = $this->liveLastTS;
    $data['liveLastLat'] = $this->liveLastLat;
    $data['liveLastLon'] = $this->liveLastLon;
    $data['liveDirection'] = $this->liveDirection;
    $data['liveName'] = $this->liveName;
    $data['liveVesselID'] = $this->liveVesselID;
    $data['liveSpeed'] = $this->liveSpeed;
    $data['liveDest'] = $this->liveDest;
    $data['liveCourse'] = $this->liveCourse;
    $data['liveMarkerAlphaWasReached'] = $this->liveMarkerAlphaWasReached;
    $data['liveMarkerAlphaTS'] = $this->liveMarkerAlphaTS;
    $data['liveMarkerBravoWasReached'] = $this->liveMarkerBravoWasReached;
    $data['liveMarkerBravoTS'] = $this->liveMarkerBravoTS;
    $data['liveMarkerCharlieWasReached'] = $this->liveMarkerCharlieWasReached;
    $data['liveMarkerCharlieTS'] = $this->liveMarkerCharlieTS;
    $data['liveMarkerDeltaWasReached'] = $this->liveMarkerDeltaWasReached;
    $data['liveMarkerDeltaTS'] = $this->liveMarkerDeltaTS;
    $data['livePassageWasSaved'] = $this->livePassageWasSaved;
    //echo "updateRecord() Calling LiveScanModel->updateLiveScan  \n";
    //var_dump($data);
    $this->callBack->LiveScanModel->updateLiveScan($data);
  }

  public function determineDirection() {
    //Downriver when lat is decreasing
    if($this->liveLastLat < $this->liveInitLat) {
      $this->liveDirection = 'downriver';
      //Upriver when lat is increasing
    } elseif ($this->liveLastLat > $this->liveInitLat) {
      $this->liveDirection = 'upriver';
    }
  }

  public function checkMarkerPassage() {
    //For upriver Direction (Lat increasing)
    if($this->liveDirection == "upriver") {
      if(!$this->liveMarkerDeltaWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_DELTA_LAT > $this->liveInitLat) && 
      ($this->liveLastLat > MARKER_DELTA_LAT))   {
        $this->liveMarkerDeltaWasReached = true;
        $this->liveMarkerDeltaTS = $this->liveLastTS;
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerDeltaEvent($this));        
      }
      if(!$this->liveMarkerCharlieWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_CHARLIE_LAT > $this->liveInitLat) && ($this->liveLastLat > MARKER_CHARLIE_LAT)) {
        $this->liveMarkerCharlieWasReached = true;
        $this->liveMarkerCharlieTS = $this->liveLastTS;        
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerCharlieEvent($this));
      }
      if(!$this->liveMarkerBravoWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_BRAVO_LAT > $this->liveInitLat) && ($this->liveLastLat > MARKER_BRAVO_LAT)) {
        $this->liveMarkerBravoWasReached = true;
        $this->liveMarkerBravoTS = $this->liveLastTS;        
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerBravoEvent($this));
      }
      if(!$this->liveMarkerAlphaWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_ALPHA_LAT > $this->liveInitLat) && ($this->liveLastLat > MARKER_ALPHA_LAT)) {
        $this->liveMarkerAlphaWasReached = true;
        $this->liveMarkerAlphaTS = $this->liveLastTS;        
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerAlphaEvent($this));
      }
    //For downriver direction (Lat decreasing)
    } elseif ($this->liveDirection == "downriver") {
      if(!$this->liveMarkerAlphaWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_ALPHA_LAT < $this->liveInitLat) && ($this->liveLastLat < MARKER_ALPHA_LAT)) {
        $this->liveMarkerAlphaWasReached = true;
        $this->liveMarkerAlphaTS = $this->liveLastTS;        
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerAlphaEvent($this));
      }
      if(!$this->liveMarkerBravoWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_BRAVO_LAT < $this->liveInitLat) && ($this->liveLastLat < MARKER_BRAVO_LAT)) {
        $this->liveMarkerBravoWasReached = true;
        $this->liveMarkerBravoTS = $this->liveLastTS;        
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerBravoEvent($this));
      }
      if(!$this->liveMarkerCharlieWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_CHARLIE_LAT < $this->liveInitLat) && ($this->liveLastLat < MARKER_CHARLIE_LAT)) {
        $this->liveMarkerCharlieWasReached = true;
        $this->liveMarkerCharlieTS = $this->liveLastTS;        
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerCharlieEvent($this));
      }
      if(!$this->liveMarkerDeltaWasReached && ($this->liveInitLat != $this->liveLastLat) && (MARKER_DELTA_LAT < $this->liveInitLat) && ($this->liveLastLat < MARKER_DELTA_LAT)) {
        $this->liveMarkerDeltaWasReached = true;
        $this->liveMarkerDeltaTS = $this->liveLastTS;        
        $this->callBack->setApubId($this->callBack->AlertsModel->triggerDeltaEvent($this));
      }           
    }
  }
  
  public function lookUpVessel() {   
    echo 'LiveScan::lookUpVessel() started '.getNow()."\n";
    //See if Vessel data is available locally
    if($data = $this->callBack->VesselsModel->getVessel($this->liveVesselID)) {
      //echo "Vessel found in database: " . var_dump($data);
      $this->liveVessel = new Vessel($data, $this->callBack);
      return;
    }
    //Otherwise scrape data from a website
    $url = 'https://www.myshiptracking.com/vessels/';
    $q = $this->liveVesselID;
    echo "Begin scraping for vesselID " . $this->liveVesselID."\n";
    $html = grab_page($url, $q);  
    echo "grab_page loaded.\n\n";
    //Edit segment from html string
    $startPos = strpos($html,'<div class="vessels_main_data cell">');
    $clip     = substr($html, $startPos);
    $endPos   = (strpos($clip, '</div>')+6);
    $len      = strlen($clip);
    $edit     = substr($clip, 0, ($endPos-$len));    
    //Count lookup attempt
    $this->lookUpCount++;        
    //Use DOM Document class
    $dom = new DOMDocument();
    @ $dom->loadHTML($edit);
    //assign data gleened from mst table rows
    $data = [];
    $rows = $dom->getElementsByTagName('tr');
    //desired rows are 5, 11 & 12
    $vesselName         =  ucwords( $rows->item(0)->getElementsByTagName('strong')->item(0)->textContent );
    $data['vesselType'] = $rows->item(5)->getElementsByTagName('td')->item(1)->textContent;
    $data['vesselOwner'] = $rows->item(11)->getElementsByTagName('td')->item(1)->textContent;
    $data['vesselBuilt'] = $rows->item(12)->getElementsByTagName('td')->item(1)->textContent;
    //Try for image
    try {
      if(saveImage($this->liveVesselID)) {
        //$endPoint = getEnv('AWS_ENDPOINT');
        $base = getEnv('BASE_URL');
        $data['vesselHasImage'] = true;
        //$data['vesselImageUrl'] = $endPoint . 'vessels/mmsi' . $this->liveVesselID . '.jpg';      
        $data['vesselImageUrl'] = $base.'vessels/jpg/' . $this->liveVesselID; 
      } else {
        $data['vesselHasImage'] = false;
      }
    }
    catch (exception $e) {
      //
      $data['vesselHasImage'] = false;
    }
    //assign data gleened locally
    $data['vesselID'] = $this->liveVesselID;
    $data['vesselName'] = $vesselName; //$this->liveName;
    $data['vesselCallSign'] = $this->liveCallSign;
    $data['vesselLength'] = $this->liveLength;
    $data['vesselWidth'] = $this->liveWidth;
    $data['vesselDraft'] = $this->liveDraft;    
    $this->liveVessel = new Vessel($data, $this->callBack);
  }  

  public function savePassageIfComplete($overRide = false) {
    if($this->livePassageWasSaved || $this->liveIsLocal) {
      return true;
    }
    //Save if at least 4 markers passed
    $score = 0;
    if($this->liveMarkerAlphaWasReached){   $score++; }
    if($this->liveMarkerBravoWasReached){   $score++; } 
    if($this->liveMarkerCharlieWasReached){ $score++; }
    if($this->liveMarkerDeltaWasReached){   $score++; }
      
    if($score >3) {
      $this->callBack->PassagesModel->savePassage($this);
      $this->livePassageWasSaved = true;
      return true;
    }
    if($overRide) {
      return true;
    }
    return false;
  }

}
