<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * CRTdaemon Class
 * classes/crtdaemon.class.php
 * 
 * (See pcntl disables below)
 */

class CRTdaemon  {
  protected $config;
  protected $run = false;
  protected $lastScanTS;
  protected $liveScan = array();
  protected $kmlUrl;
  protected $errEmail;
  protected $timeout;
  protected $xmlObj;
  protected $lastXmlObj;  
  protected $nonVesselFilter = array();
  public    $localVesselFilter = array();
  public    $LiveScanModel;
  public    $PassagesModel;
  public    $VesselsModel;

  public function __construct($configStr)  {   
    if(!is_string($configStr)) {
      throw new Exception('configStr must point to existing file.');
    }
    $this->config = $configStr;
    //pnctl disabled for Windows run
    //pcntl_signal(SIGINT, [$this, 'signalStop']);
    //pcntl_signal(SIGHUP, [$this, 'signalReload']);
    //pcntl_signal(SIGTERM, [$this, 'signalStop']);
  }

  protected function setup() {
    $config = include($this->config);
    $this->kmlUrl = $config['kmlUrl'];
    $this->timeout = intval($config['timeout']);
    $this->errEmail = $config['errEmail'];    
    $this->nonVesselFilter = $config['nonVesselFilter'];
    $this->localVesselFilter = $config['localVesselFilter'];
    $this->LiveScanModel = new LiveScanModel();
    $this->PassagesModel = new PassagesModel();
    $this->VesselsModel = new VesselsModel();
    $this->AlertsModel = new AlertsModel();
    //Debug test line... 
    //var_dump($this);
    //return;
    error_log('crtconfig.php loaded');
  }

  // ** This function is the main loop of this application **
  protected function run() {
    $xml = "";
    echo "run()";
    $shipPlotter = new ShipPlotter();
    while($this->run) {
      $ts   = time();                     
      if(!($this->xmlObj = simplexml_load_file($this->kmlUrl))) {
        $shipPlotter->serverIsUp(false);
        echo "Ship Plotter -up = ".$shipPlotter->isReachable;
        sleep(20);
        continue;
      } else {
        $shipPlotter->serverIsUp(true);
        echo "Ship Plotter +up = ".$shipPlotter->isReachable;
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
            echo "liveScan->update(". $ts . " " . $name . " " . $id . " ". $lat . " " . $lon . " " . $speed . " " . $course . " " . $dest .")";
          } else {
            $this->liveScan[$key] = new LiveScan($ts, $name, $id, $lat, $lon, $speed, $course, $dest, $length, $width, $draft, $callsign, $this);
            echo "new LiveScan(". $ts . " " . $name . " " . $id . " ". $lat . " " . $lon . " " . $speed . " " . $course . " " . $dest  . " " . $width . " " . $draft . " " . $callsign,")";
          }
        }                               
      }
      $this->lastXmlObj = $this->xmlObj;
      unset($pms);
      $this->removeOldScans();
      $this->AlertsModel->processQueuedAlert();
      //Subtract loop processing time from sleep delay...
      $endTS    = time();
      $duration = $endTS - $ts;
      //...unless time is more than 30 sec then use 1 sec
      $sleepTime = $duration > 30 ? 1 : (30 - $duration);
      echo "Loop duration = ".$duration;
      //pnctl disabled for window run
      //pcntl_signal_dispatch();       
      sleep($sleepTime);
    }
  }

  protected function removeOldScans() {
    echo "Starting CRTDaemon::removeOldScan() \n";
    $now = time();
    foreach($this->liveScan as $key => $obj) {     
      //If record is old...
      if(($now - $this->timeout) > $obj->liveLastTS) {
        //...then save it to passages table
        if($obj->savePassageIfComplete(true)) {          
          //Save was successful, delete from live table
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

  protected function reloadSavedScans() {
    echo "Starting CRTDaemon::reloadSavedScans() \n";
    if(!($data = $this->LiveScanModel->getAllLiveScans())) {
      echo "no old scans\n";
      return;
    }
    $this->liveScan = array();
    foreach($data as $row) {      
      $key = 'mmsi'. $row['liveVesselID'];
      //echo "Reloading ".var_dump($row)."\n\n";
      $this->liveScan[$key] = new LiveScan(null, null, null, null, null, null, null, null, null, null, null, null, $this, true, $row);
      $this->liveScan[$key]->lookUpVessel();
    }
  }

  protected function shutdown() {
    $msg = 'crtdaemon shutdown ' . date('c');
    error_log($msg);
    //mail($this->errEmail, $msg, $msg, '', '');    
  }

  public function signalStop($signal) {
    error_log('caught shutdown signal [' . $signal .']');
    $this->run = false;
  }

  public function signalReload($signal) {
    error_log('caught shutdown signal [' . $signal .']');
    $this->setup();
    $this->reloadSavedScans();
  }

  public function start() {
    echo "start()";
    $this->run = true;
    $this->setup();
    echo "setup()";
    $this->reloadSavedScans();
    $this->run();  
    $this->shutdown();
  }
}