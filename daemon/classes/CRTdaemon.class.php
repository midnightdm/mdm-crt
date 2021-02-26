<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * CRTdaemon Class
 * daemon/classes/crtdaemon.class.php
 *  
 *  */

class CRTdaemon  {
  protected $config;
  protected $run = false;
  protected $lastScanTS;
  protected $liveScan = array();
  protected $kmlUrl;
  public    $jsonUrl;
  protected $errEmail;
  protected $timeout;
  protected $xmlObj;
  protected $lastXmlObj;  
  protected $nonVesselFilter = array();
  public    $localVesselFilter = array();
  public    $LiveScanModel;
  public    $PassagesModel;
  public    $VesselsModel;
  public    $AlertsModel;
  public    $apubId;
  public    $lastApubId;

  public function __construct($configStr)  {   
    if(!is_string($configStr)) {
      throw new Exception('configStr must point to existing file.');
    }
    $this->config = $configStr;

  }

  public function setApubId($id) {
    //This method run by LiveScan::checkMarkerPassage()
    if(is_int($id)) {
      $this->apubId = intval($id);
    } else {
      echo "ERROR: CRTdaemon::setApubId() received an invalid id from an event trigger.\n";
    }
    
  }

  protected function setup() {
    $config = include($this->config);
    //$this->kmlUrl = $config['kmlUrl']; //For normal use
    $this->kmlUrl = "http://localhost/mdm-crt/js/pp_google-test0.kml"; //For testing only
    
    $this->jsonUrl = $config['jsonUrl'];
    $this->timeout = intval($config['timeout']);
    $this->errEmail = $config['errEmail'];    
    $this->nonVesselFilter = $config['nonVesselFilter'];
    $this->localVesselFilter = $config['localVesselFilter'];
    $this->LiveScanModel = new LiveScanModel();
    $this->PassagesModel = new PassagesModel();
    $this->VesselsModel = new VesselsModel();
    $this->AlertsModel = new AlertsModel();  
    //Load ID of last published alert.  
    $this->lastApubId = $this->apubId = $this->AlertsModel->getLastPublishedAlertId();
    echo "crtconfig.php loaded.\n";
  }

  // * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  // *  This function is the main loop of this application.  *
  // * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  protected function run() {
    $xml = ""; 
    $testIteration = 1; //Test Code Only
    echo "CRTdaemon::run()\n";
    $shipPlotter = new ShipPlotter();
    $logger = new TimeLogger();
    while($this->run) {
      $this->kmlUrl = "http://localhost/mdm-crt/js/pp_google-test".$testIteration.".kml"; //For testing only
      echo "testIteration = ".$testIteration;
      if($testIteration == 12) { 
        $this->run = FALSE;      
      } 
      $ts   = time();                                           
      $xml = @file_get_contents($this->kmlUrl);
      if ($xml===false) {
        echo "Ship Plotter -up = ".$shipPlotter->isReachable.' '.getNow();
        //Compares present value to stored state to prevent recursion
        if($shipPlotter->isReachable==true){
          $shipPlotter->serverIsUp(false);
        }                
        sleep(20);
        continue;                
      } else {
        $this->xmlObj  = simplexml_load_string($xml);
        //Compares present value to stored state to prevent recursion
        if($shipPlotter->isReachable==false){
          $shipPlotter->serverIsUp(true);
        }          
        echo "Ship Plotter +up = ".$shipPlotter->isReachable.' '.getNow()."\n";
      }
      if($this->xmlObj === $this->lastXmlObj){
        echo "xmlObj same as lastXmlObj: {$ts} \n\n";
        sleep(10);
        continue;
      }           
      //Loop through place marks
      $pms = $this->xmlObj->Document->Placemark;          
      foreach($pms as $pm) {
        if(isset($pm->description)) {
          $descArr = explode("\n", $pm->description);
          //Get vessel's name
          $name = $descArr[0];
          $startPos = strpos($name, 'Name ') +5;          
          $name     = trim(substr($name, $startPos)); //Remove white spaces
          $name     = str_replace(',', '', $name);   //Remove commas (,)
          $name     = str_replace('.', '. ', $name); //Add space after (.)
          $name     = str_replace('  ', ' ', $name); //Remove double space
          //Get vessel's MMSI id
          $id       = $descArr[1];
          $startPos = strpos($id,'MMSI ') + 5;
          $id       = trim(substr($id, $startPos)); //Remove white spaces  
          //Clean special case id
          $id       = str_replace('[us]', '', $id);
          
          //Filter out stationary transponders              
          if(in_array($id,   $this->nonVesselFilter)) { continue 1; }
          $name     = ucwords(strtolower($name)); //Change capitalization
        
          //Get vessel's coordinates
          $position = $descArr[6];
          $startPos = strpos($position,'Pos ') + 4;
          $position = substr($position, $startPos);
          $posArr   = explode(" ", $position);
          $lon      = floatval($posArr[1]);
          $lat      = floatval($posArr[0]);
          
          $speed    = $descArr[7];
          $pos      = strpos($speed,'Speed ') + 6;
          $speed    = trim(substr($speed, $pos));
          
          $course   = $descArr[8];
          $pos      = strpos($course,'Course ') + 7;
          $course   = trim(substr($course, $pos));
          
          $dest  = $descArr[4];
          $pos      = strpos($dest,'Dest ') + 5;
          $dest  = trim(substr($dest, $pos));
          
          $length   = $descArr[10];
          $pos      = strpos($length,'Length ') + 7;
          $length   = trim(substr($length, $pos));

          $width    = $descArr[11];
          $pos      = strpos($width,'Width ') + 6;
          $width    = trim(substr($width, $pos));

          $draft    = $descArr[12];
          $pos      = strpos($draft,'Draft ') + 6;
          $draft    = trim(substr($draft, $pos));                                   

          $callsign = $descArr[2];
          $pos      = strpos($callsign,'c/s ') + 4;
          $callsign = trim(substr($callsign, $pos)); 

          $key  = 'mmsi'.$id;
          if(isset($this->liveScan[$key])) {
            $this->liveScan[$key]->update($ts, $name, $id, $lat, $lon, $speed, $course, $dest);
            echo "liveScan->update(". $ts . " " . $name . " " . $id . " ". $lat . " " . $lon . " " . $speed . " " . $course . " " . $dest .")\n";
          } else {
            $this->liveScan[$key] = new LiveScan($ts, $name, $id, $lat, $lon, $speed, $course, $dest, $length, $width, $draft, $callsign, $this);
            echo "new LiveScan(". $ts . " " . $name . " " . $id . " ". $lat . " " . $lon . " " . $speed . " " . $course . " " . $dest  . " " . $width . " " . $draft . " " . $callsign,")\n";
          }
        }                               
      }
      //Test if liveScan triggered any events on this loop
      $this->checkAlertStatus();
      $this->lastXmlObj = $this->xmlObj;
      $this->lastApubId = $this->apubId;
      unset($pms);
      $this->removeOldScans();
    
      $logger->timecheck();
      //Extra process designed to keep VM alive
      sleep(15);
      //Force web server to generage json file
      $dummy = grab_page($this->jsonUrl);      
      unset($dummy);
      //Subtract loop processing time from sleep delay...
      $endTS    = time();
      $duration = $endTS - $ts;
      //...unless time is more than 30 sec then use 1 sec
      $sleepTime = $duration > 30 ? 1 : (30 - $duration);
      echo "Loop duration = ".$duration.' '.getNow()." \n";      
      sleep($sleepTime);
      $testIteration++; //Test Only: limits to 12 loops
    }
  }

  protected function removeOldScans() {
    echo "CRTDaemon::removeOldScans()... \n";
    $now = time();    
    foreach($this->liveScan as $key => $obj) {           
      //If record is old...
      echo '   ... Vessel '.$obj->liveName.' last updated '.$obj->liveLastTS . '. It\'s now '.$now.' Timeout is '.$this->timeout.".  \n";
      if(($now - $this->timeout) > $obj->liveLastTS) {
        //...then save it to passages table
        if($obj->savePassageIfComplete(true)) {          
          //Save was successful, delete from live table
          echo 'Deleting old livescan record for '.$obj->liveName .' '.getNow()."\n";
          if($this->LiveScanModel->deleteLiveScan($obj->liveID)){
            //Table delete was sucessful, remove object from array
            unset($this->liveScan[$key]);
          } else {
            error_log('Error deleting LiveScan ' . $obj->liveID);
          }
        } else {
          error_log('Error saving new passage for ' . $obj->liveName);
        }
      }       
    }
  }

  protected function checkAlertStatus() {
    //Calculate number of alerts published since last loop
    $alertQty = $this->apubId - $this->lastApubId;
    echo "alertQty ($alertQty) = apubId ($this->apubId) - lastApubID ($this->lastApubId)\n";
    if($alertQty > 0) {
      //New Alert Events triggered! Send messages to subscribers.
      $this->AlertsModel->generateAlertMessages($alertQty);
    }
  }

  protected function reloadSavedScans() {
    echo "CRTDaemon::reloadSavedScans() started ".getNow()."...\n";
    if(!($data = $this->LiveScanModel->getAllLiveScans())) {
      echo "   ... No old scans. ".getNow()."\n";
      return;
    }
    $this->liveScan = array();
    foreach($data as $row) {      
      $key = 'mmsi'. $row['liveVesselID'];
      echo "   ... Reloading {$row['liveName']}\n";
      $this->liveScan[$key] = new LiveScan(null, null, null, null, null, null, null, null, null, null, null, null, $this, true, $row);
      $this->liveScan[$key]->lookUpVessel();
    }
  }

  protected function shutdown() {
    $msg = "* * * CRTdaemon shutdown " . date('c')." * * *";
    error_log($msg);
    //mail($this->errEmail, $msg, $msg, '', '');    
  }

  //DEPRECIATED unusable on Heroku
  public function signalStop($signal) {
    error_log('caught shutdown signal [' . $signal .']');
    $this->run = false;
  }

  //DEPRECIATED unusable on Heroku
  public function signalReload($signal) {
    error_log('caught shutdown signal [' . $signal .']');
    $this->setup();
    $this->reloadSavedScans();
  }

  public function start() {
    echo "CRTdaemon::start()\n";
    $this->run = true;
    $this->setup();
    echo "CRTdaemon::setup()\n";
    $this->reloadSavedScans();
    $this->run();
    echo "CRTdaemon::shutdown()\n";  
    $this->shutdown();
  }
}