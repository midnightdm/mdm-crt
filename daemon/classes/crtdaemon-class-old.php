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
    $this->LiveScanModel = new LiveScanModel();
    $this->PassagesModel = new PassagesModel();
    $this->VesselsModel = new VesselsModel();
    //Debug test line... 
    //var_dump($this);
    //return;
    error_log('crtconfig.php loaded');
  }

  protected function run() {
    $xml = "";
    echo "run()";
    while($this->run) {
      echo '$this->kmlUrl = ' . $this->kmlUrl;
      //$xml = grab_page($this->kmlUrl);
      //echo "$xml = " . var_dump($xml);
      if(!($this->xmlObj = simplexml_load_file($this->kmlUrl))) {
      //if(!($this->xmlObj = simplexml_load_string($xml))) {
        $msg = "XML load failure " . date('c');
        error_log($msg);
        //mail($this->errEmail, $msg, $msg, '');        
        echo $msg;
        continue;
      }
      
      //Pull time string out of description
      $ts = substr($this->xmlObj->Document->description,-19);
      //Correct European date format
      $ts = strtotime(str_replace("/", "-", $ts));
      
      echo "$ts = " . $ts;
      if($this->lastScanTS === $ts) {
        echo "lastScanTS === ts (sleep)";
        sleep(60);
        continue;
      }
      $pms = $this->xmlObj->Document->Placemark;
      foreach($pms as $pm) {
        $name = $pm->name;
        $desc = strval($pm->description);
        $point = explode(',', $pm->Point->coordinates);
        $lat  = floatval($point[1]);
        $lon  = floatval($point[0]);
        $key  = 'mmsi'.$desc;
        if(isset($this->liveScan[$key])) {
          $this->liveScan[$key]->update($ts, $name, $desc, $lat, $lon);
          echo "liveScan->update(". $ts . " " . $name . " " . $desc . " ". $lat . " " . $lon.")";
        } else {
          $this->liveScan[$key] = new LiveScan($ts, $name, $desc, $lat, $lon, $this);
          echo "new LiveScan(". $ts . " " . $name . " " . $desc . " ". $lat . " " . $lon.")";
        }
      }
      $this->lastScanTS = $ts;
      $this->removeOldScans();
      //pnctl disabled for window run
      //pcntl_signal_dispatch();    
    }
  }

  protected function removeOldScans() {
    $now = time();
    foreach($this->liveScan as $key => $obj) {     
      //If record is old...
      if(($now - $this->timeout) > $obj->liveLastTS) {
        //...then save it to passages table
        if($this->PassagesModel->savePassage($obj)) {
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
    if(!($data = $this->LiveScanModel->getAllLiveScans())) {
      echo "no old scans\n";
      return;
    }
    $this->liveScan = array();
    foreach($data as $row) {
      $key = 'mmsi'. $row['liveVesselID'];
      $this->liveScan[$key] = new LiveScan(null, null, null, null, null, $this, true, $row);
      $this->liveScan[$key]->lookUpVessel();
    }
  }

  protected function shutdown() {
    $msg = 'crtdaemon shutdown ' . date('c');
    error_log($msg);
    mail($this->errEmail, $msg, $msg, '', '');    
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