<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * AlertsMonitor Class
 * classes/AlertsMonitor.class.php
 * 
 */
class AlertsMonitor {
  public $LiveScan;
  public $AlertsModel;
  public $Messages;

  public function __construct($callBack) {
    $this->LiveScan    = $callBack; //LiveScan object
    $this->AlertsModel = new AlertsModel();
    $this->Messages    = new Messages();    
  }

  public function triggerDetectEvent() {
    $this->AlertsModel->postAlertMessage("detect", $this);
    $this->AlertsModel->queueAlertsForVessel($this->LiveScan->liveVesselID, "detect", 21600, "undetermined"); //6 hours
  }

  public function triggerAlphavent() {
    $this->AlertsModel->postAlertMessage("alpha", $this);
    $this->AlertsModel->queueAlertsForVessel($this->LiveScan->liveVesselID, "alpha", 7200, $this->LiveScan->liveDirection); //2 hours
  }
  
  public function triggerBravoEvent() {
    $this->AlertsModel->postAlertMessage("bravo", $this);
    $this->AlertsModel->queueAlertsForVessel($this->LiveScan->liveVesselID, "bravo", 7200, $this->LiveScan->liveDirection); //2 hours);
  }
  
  public function triggerCharlieEvent() {
    $this->AlertsModel->postAlertMessage("charlie", $this);
    $this->AlertsModel->queueAlertsForVessel($this->LiveScan->liveVesselID, "charlie", 7200, $this->LiveScan->liveDirection); //2 hours)
  }

  public function triggerDeltaEvent() {
    $this->AlertsModel->postAlertMessage("delta", $this);
    $this->AlertsModel->queueAlertsForVessel($this->LiveScan->liveVesselID, "delta", 7200, $this->LiveScan->liveDirection); //2 hours
  }
}