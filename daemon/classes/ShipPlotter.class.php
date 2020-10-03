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

  public function __construct() {
    $this->ShipPlotterModel = new ShipPlotterModel();
    $status = $this->ShipPlotterModel->getStatus();
    $this->isReachable = $status['isReachable'];
    $this->lastUpTS    = $status['lastUpTS'];
    $this->lastDownTS  = $status['lastDownTS'];
  }

  public function serverIsUp($bool) {
    $ts = time();
    if($bool) {
      switch($this->isReachable) {
        case null :
        case false: $this->isReachable = true;
                    $this->lastUpTS    = $ts;
                    $this->ShipPlotterModel->serverIsUp($ts);
                    $this->sendServerAlert();
                    break;
        case true : break;
        default   : break;            
      }
    } else {
      switch($this->isReachable) {
        case null :
        case true : $this->isReachable = false;
                    $this->lastDownTS    = $ts;
                    $this->ShipPlotterModel->serverIsDown($ts);
                    $this->sendServerAlert();
                    break;
        case false: break;
        default   : break;     
      }  
    }
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