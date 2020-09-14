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
  protected $vesselIDFilter = array();
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
    $this->vesselIDFilter = $config['vesselIDFilter'];
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
      $ts   = time();
      echo '$this->kmlUrl = ' . $this->kmlUrl;                   
      if(!($this->xmlObj = simplexml_load_file($this->kmlUrl))) {
        $msg = "XML load failure " . date(DATE_ATOM);
        error_log($msg);
        //mail($this->errEmail, $msg, $msg, '');        
        echo $msg;
        continue;
      }
      if($this->xmlObj === $this->lastXmlObj){
        echo "xmlObj same as lastXmlObj: {$ts} \n\n";
        sleep(10);
        continue;
      }           

      $pms = $this->xmlObj->Document->Placemark;
    
      $time = date(DATE_ATOM, $ts);
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

          //Filter out stationary transponders              
          if(in_array($id,   $this->vesselIDFilter)) { continue 1; }
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
      $this->saveJSON();
      $this->removeOldScans();
      //pnctl disabled for window run
      //pcntl_signal_dispatch(); 
      //die("End of one run.");
      sleep(30);
    }
  }

  protected function removeOldScans() {
    echo "Starting CRTDaemon::removeOldScan() \n";
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

  public function saveJSON() {
    echo "Starting CRTDaemon::saveJSON() \n";
    $data = [];
    $awsKey      = getEnv('AWS_ACCESS_KEY_ID');
    $awsSecret   = getEnv('AWS_SECRET_ACCES_KEY');
    $credentials = new Aws\Credentials\Credentials($awsKey, $awsSecret);

    $s3 = new Aws\S3\S3Client([
        'version'     => 'latest',
        'region'      => 'us-east-2',
        'credentials' => $credentials
    ]); 

    foreach($this->liveScan as $live) {
      $inner['liveLastScanTS']       = $live->liveLastTS==null ? $live->liveInitTS : $live->liveLastTS;
      $inner['id']       = $live->liveVesselID;
      $inner['name']     = $live->liveName;
      $inner['position']['lat'] = $live->liveLastLat==null ? $live->liveInitLat : $live->liveLastLat;
      $inner['position']['lng'] = $live->liveLastLon==null ? $live->liveInitLon : $live->liveLastLon;
      $inner['speed'] = $live->liveSpeed;
      $inner['course'] = $live->liveCourse;
      $inner['dest'] = $live->liveDest;
      $inner['length'] = $live->liveLength;
      $inner['width'] = $live->liveWidth;
      $inner['draft'] = $live->liveDraft;
      $inner['callsign'] = $live->liveCallSign;
      $inner['dir'] = $live->liveDirection;
      $inner['liveMarkerAlphaWasReached'] = intval($live->liveMarkerAlphaWasReached);
      $inner['liveMarkerAlphaTS'] = $live->liveMarkerAlphaTS == 0 ? null : $live->liveMarkerAlphaTS;
      $inner['liveMarkerBravoWasReached'] = intval($live->liveMarkerBravoWasReached);
      $inner['liveMarkerBravoTS'] = $live->liveMarkerBravoTS == 0 ? null : $live->liveMarkerBravoTS;
      $inner['liveMarkerCharlieWasReached'] = intval($live->liveMarkerCharlieWasReached);
      $inner['liveMarkerCharlieTS'] = $live->liveMarkerCharlieTS == 0 ? null : $live->liveMarkerCharlieTS;
      $inner['liveMarkerDeltaWasReached'] = intval($live->liveMarkerDeltaWasReached);
      $inner['liveMarkerDeltaTS'] =  $live->liveMarkerDeltaTS == 0 ? null : $live->liveMarkerDeltaTS;

      if($live->liveVessel != null) {
        $vessel = [];
        $vessel['vesselHasImage'] = $live->liveVessel->vesselHasImage;
        $vessel['vesselImageUrl'] = $live->liveVessel->vesselImageUrl;
        $vessel['vesselType']     = $live->liveVessel->vesselType;
        $vessel['vesselOwner']    = $live->liveVessel->vesselOwner;
        $vessel['vesselBuilt']    = $live->liveVessel->vesselBuilt;
        $inner['vessel'] = $vessel;    
      }

      array_push($data, $inner);
      //$filePath = $_SERVER['DOCUMENT_ROOT'].'../application/views/livescanjson.php';
      //echo 'crtdaemon.class.php filepath for file_put_contents of livescanjson.
      //file_put_contents($filePath, json_encode($data));

      //New write to code
      $s3->upload($bucket, 'json/livescan.json', json_encode($data));
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