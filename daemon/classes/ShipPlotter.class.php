<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * *
 * ShipPlotter class
 * daemon/classes/ShipPlotter.class.php
 *
 */
class ShipPlotter {
  public $isReachable = null;
  public $lastUpTS;
  public $lastDownTS;
  public $ShipPlotterModel;
  public $alerted;

  public function __construct() {
    $this->ShipPlotterModel = new ShipPlotterModel();
    $status = $this->ShipPlotterModel->getStatus();
    $this->isReachable = $status['isReachable'];
    $this->lastUpTS    = $status['lastUpTS'];
    $this->lastDownTS  = $status['lastDownTS'];
  }

  public function serverIsUp($bool) {
    $ts = time();
    echo "saved isReachable status = ".$this->isReachable;
    if($bool==true) {
      switch($this->isReachable) {
        case null :
        case 0    : $this->casezero($ts);
                    break;
        case 1    : break;
        default   : break;            
      }
    } else if($bool==false) {
      switch($this->isReachable) {
        case null :
        case 1    : $this->caseone($ts);
                    break;
        case 0    : break;
        default   : break;     
      }  
    }
  }

  public function caseone($ts) {
    $this->ShipPlotterModel->serverIsDown($ts);
    $status = $this->ShipPlotterModel->getStatus();
    $this->isReachable = $status['isReachable'];
    echo "updated isReachable status = ".$this->isReachable;
    $this->lastUpTS    = $status['lastUpTS'];
    $this->lastDownTS  = $status['lastDownTS'];
    if ($this->alerted == false || ($ts - $this->lastDownTS) > 108000) {
      //$this->sendServerAlert();
      $this->alerted = true;                      
    } 
  }

  public function casezero($ts) {
    $this->isReachable = 1;
    $this->lastUpTS    = $ts;
    $this->ShipPlotterModel->serverIsUp($ts);
    //$this->sendServerAlert();
    $this->alerted = false;
  }

  public function sendServerAlert() {
    $msgObj = new Messages();
    $phone1 = '+15633215576';
    $phone2 = '+15632490215';
    $str    = 'Y-m-d H:i:s';
    $text   = "The Ship Plotter KML server is";
    $text  .= $this->isReachable ? " now UP. The CRT app thanks you! " : "DOWN!";
    $text  .= " Last Up = ". date($str, ($this->lastUpTS - 18000));
    $text  .= " Last Down = ". date($str, ($this->lastDownTS - 18000));
    $data1  = [
      ['phone' => $phone1, 'text' => $text], 
      ['phone' => $phone2, 'text' => $text]
    ];
    $data2  = [
      ['phone' => $phone1, 'text' => $text]      
    ];
    $msgObj->sendSMS($data2);
  }
}