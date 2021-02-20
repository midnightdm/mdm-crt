<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * AlertsModel Class
 * daemon/classes/AlertsModel.class.php
 * 
 */
class AlertsModel extends Dbh {
  public function __construct() {
    parent::__construct();
  }

  //DEPRECIATED alert queueing eliminated and replaced by generateAlertMessages() method
  public function queueAlertsForVessel($vesselID, $event, $gtSeconds, $dir=null) {
    switch($dir) {
      case "upriver"  : $add = "Up";   break;
      case "downriver": $add = "Down"; break;
      default         : $add = "";     break;
    }
    switch($event) {      
      case "alpha"  : $statement = "alertOnAlpha";   break;      
      case "delta"  : $statement = "alertOnDelta";   break;
      default       : $statement = false;
    }
    if(!$statement) { 
      error_log('Bad event in queueAlertsForVessel('.$event.')');
      return false; 
    }
    $db = $this->db();
    if($add=='') {
      $sql = "SELECT alertID FROM alerts WHERE (alertVesselID = ? OR alertVesselID = 'any') AND ".$statement." = true AND NOT EXISTS "
        . "(SELECT * FROM alertlog WHERE alogAlertID=alertID AND alogTS > ?)";
    } else {
      $sql = "SELECT alertID FROM alerts WHERE (alertVesselID = ? OR alertVesselID = 'any') AND ".$statement.$add." = true"
        . "AND NOT EXISTS (SELECT * FROM alertlog WHERE alogAlertID=alertID AND alogTS > ?)";
    }    
    $q = $db->prepare($sql);
    $q->execute([$vesselID, (time()-$gtSeconds)]);
    $tot = count($q->fetchAll());
    unset($q);
    //Put into alertqueue table
    $sql2 = "INSERT INTO alertqueue (aqueueVesselID, aqueueEventType, aqueueDirection, aqueueInitTS, aqueueJobTotal, aqueueJobRemaining) "
         .  "VALUES (:aqueueVesselID, :aqueueEventType, :aqueueDirection, :aqueueInitTS, :aqueueJobTotal, :aqueueJobRemaining)";
    $data['aqueueVesselID']  = $vesselID;
    $data['aqueueEventType'] = $event;
    $data['aqueueDirection'] = $dir;
    $data['aqueueInitTS']    = time();
    $data['aqueueJobTotal']  = $data['aqueueJobRemaining'] = $tot;
    $q = $db->prepare($sql2);
    $q->execute($data);
    unset($q);
  }

  //DEPRECIATED alert queueing eliminated and replaced by generateAlertMessages() method
  public function processQueuedAlert() {
    if(!($row = $this->getFirstVesselQueued())) {
      echo "No data from getFirstVesselQueued(). \n";
      return;
    }
    $dir  = $row['aqueueDirection'];
    $jt   = intval($row['aqueueJobTotal']);
    $jr   = intval($row['aqueueJobRemaining']);
    $jobsDone = 0;
    $jobsToDo = $jr!=$jt ? $jr : $jt;
    $step = $jobsToDo>100 ? 100 : $jobsToDo; //Do all when fewer than 100
    $page = 0;
    $msgController = new Messages();
    for($i=0; $i<$jobsToDo; $i=+$step) {
      $alerts = $this->getAlertsForVesselEvent($row['aqueueVesselID'], $row['aqueueEventType'], $dir, $step, $page);
      //Package SMS messages
      $smsMessages = [];
      foreach($alerts['sms'] as $alt) {
        $txt = $this->buildAlertMessage(
          $row['aqueueEventType'],
          $alt['liveName'],
          $alt['vesselType'],
          $alt['liveDirection'],
          $row['aqueueInitTS'],
          $alt['liveInitLat'],
          $alt['liveInitLon']
        );
        //Only phone & text get used by sendSms(). Others go to alertlog.
        $msg = ['phone'=>$alt['alertDest'], 'text'=>$txt, 'event' => $row['aqueueEventType'], 'dir' => $dir, 'alertID' => $alt['alertID']];
        $smsMessages[] = $msg;        
      }
      //Test code end point
      //echo "Dumping smsMessages array now....:\n\n";
      //echo var_dump($smsMessages);
      //Test code end point
      $clickSendResponse = json_decode($msgController->sendSMS($smsMessages));
      $this->generateAlertLogSms($clickSendResponse, $smsMessages);
      unset($smsMessages);
      //Package Email Messages
      $emailMessages = [];
      foreach($alerts['email'] as $alt) {
        $txt = $this->buildAlertMessage(
          $row['aqueueEventType'],
          $alt['liveName'],
          $alt['vesselType'],
          $alt['liveDirection'],
          $row['aqueueInitTS'],
          $alt['liveInitLat'],
          $alt['liveInitLon']
        );
        $msg = ['to'=>$alt['alertDest'], 'text'=>$txt, 'subject'=> 'CRT Alert for '.$alt['liveName']];
        $emailMessages[] = $msg;        
      }
      //$clickSendResponse = json_decode($msgController->sendSMS($emailMessages));
      $msgController->sendEmail($emailMessages);
      $this->generateAlertLogEmail(null, $emailMessages);
      unset($emailMessages);
      //End of loop property updates
      $jr = $jr - $jobsDone;
      $this->refreshAlertQueue($row['aqueueID'], $jr);
    }
  }

  //DEPRECIATED replaced by generateAlertMessages() method
  public function getAlertsForVesselEvent($vesselID, $event, $direction, $size, $page) {
    $db = $this->db();
    $txm = []; 
    $emm = [];
    if($direction == "upriver") {
      $add = "Up";
    } else if($direction == "downriver") {
      $add = "Down";
    }
    $eventCol =  "alertOn".ucfirst($event).$add." = true ";

    $sqlOld = "SELECT alerts.*, liveName, liveDirection, liveInitLat, liveInitLon, vesselType FROM alerts, live, vessels WHERE "
        .  "(alertVesselID = ? OR (alertVesselID = 0 AND alertOnAny = true)) AND ".$eventCol." AND liveVesselID=alertVesselID AND vesselID=alertVesselID ORDER BY "
        .  "alertCreatedTS LIMIT ".$size.", ".$page;
    
    //Work in progress, doesn't include "any" alerts and limit needs work.
    $sql = "SELECT DISTINCT alerts.*, liveName, liveDirection, liveInitLat, liveInitLon, vesselType FROM alerts, live, vessels WHERE "
        .  "alertVesselID = ? AND ".$eventCol." AND liveVesselID=alertVesselID AND vesselID=alertVesselID ORDER BY "
        .  "alertCreatedTS LIMIT ".$page.", ". $size;
    
        $q = $db->prepare($sql);
    $q->execute([$vesselID]);
    foreach($q as $row) {
      switch($row['alertMethod']) {
        case "sms"  : $txm[] = $row; break;
        case "email": $emm[] = $row; break;
      }
    } 
    $data = ['sms' => $txm, 'email' => $emm];
    unset($db);
    //echo "Dumping data from getAlertsForVesselEvent() :\n";
    //echo var_dump($data);
    return $data;
  }


  public function buildAlertMessage($event, $vesselName, $vesselType, $direction, $ts, $lat, $lon) {
    $loc = "";
    $str = "m/j h:i:sa";
    $offset = date("I", $ts) ? -18000 : -21600;
    switch($event) {
      case "detected": $evtDesc = "Transponder was detected ";
                     $loc    .= "\nLocation: ".$lat.", ".$lon; break;
      case "alpha" : $evtDesc = "crossed 3 mi N of Lock 13 ";  break;
      case "bravo" : $evtDesc = $direction=="downriver" ? "is leaving " : "has reached ";
                     $evtDesc .= "Lock 13"; break;
      case "charlie" : $evtDesc = "is at Clinton RR drawbridge ";  break;
      case "delta" : $evtDesc = "crossed 3 mi S of drawbridge ";  break;
    }
    $txt  = str_replace('Vessel', '', $vesselType);
    $txt .= " Vessel ".$vesselName." ".$evtDesc." traveling ".$direction;
    $txt .= ". ".date($str, ($ts+$offset)).$loc;
    return $txt;
  }

  //DEPRECIATED  because alertQueue table eliminated
  public function getFirstVesselQueued() {
    $sql = "select * from alertqueue where aqueueJobRemaining > 0 order by aqueueInitTS limit 1;";
    $db = $this->db();
    $q = $db->query($sql);
    if($results = $q->fetch()) {
      echo "getFirstVesselQueued(): \n";
      echo var_dump($results);
      return $results;
    }
    return false;
  }

  //DEPRECIATED because alertQueue table eliminated
  public function refreshAlertQueue($aqueueID, $jobsRemaining) {
    echo "refreshAlertQueue() aqeueuID=".$aqueueID." jobsRemaining=".$jobsRemaining." \n";
    if($jobsRemaining>0) {
      $sql = "UPDATE alertqueue SET aqueueJobRemaining = ".$jobsRemaining." WHERE aqueueID = ?";
      echo "alertqueue $aqueueID updated. ";
    } else if($jobsRemaining==0) {
      $sql = "DELETE FROM alertqueue WHERE aqueueID = ?";
      echo "alertqueue $aqueueID deleted ";
    }
    $db = $this->db();
    $db->prepare($sql)->execute([$aeueueID]);
  }

  
  public function postAlertMessage($event, $liveScan) {
  //This function gets run by Event trigger methods of this class 
    $ts = time();
    $vesselType = $liveScan->liveVessel==null ? "" : $liveScan->liveVessel->vesselType;
    $txt = $this->buildAlertMessage(
      $event, 
      $liveScan->liveName, 
      $vesselType,
      $liveScan->liveDirection, 
      $ts, 
      $liveScan->liveInitLat, 
      $liveScan->liveInitLon
    );
    //$sql = "INSERT INTO alertpublish (apubTS, apubText, apubVesselID, apubVesselName) VALUES ( ". $ts.", ".addslashes($txt).", "
    // .$liveScan->liveVesselID.", ".$liveScan->liveName.")";
    $sql = "INSERT INTO alertpublish (apubTS, apubText, apubVesselID, apubVesselName, apubEvent, apubDir) VALUES (:apubTS, :apubText, :apubVesselID, :apubVesselName, :apubEvent, :apubDir)";
    $data = ['apubTS'=>$ts, 'apubText'=>$txt, 'apubVesselID'=>$liveScan->liveVesselID, 'apubVesselName' => $liveScan->liveName, 'apubEvent'=>$event, 'apubDir'=>$liveScan->liveDirection];
    $db = $this->db();
    $res = $db->prepare($sql);
    
    try {
      $res->execute($data);
    } catch(PDOException $exception){ 
      echo $exception; 
    }            
  }
  
  
  public function generateAlertMessages($limit) {
    //This function gets run by CRTdaemon::checkAlertStatus()
    $db = $this->db();
    $sql = "SELECT * FROM alertpublish ORDER BY apubTS DESC LIMIT $limit";
    $q1 = $db->query($sql);
    
    //Get data for new found messages     
    if($limit > 1) {
      $publishData    = $q1->fetchAll();
    } elseif($limit==1) {
      $publishData    = [];
      $publishData[0] = $q1->fetch(); 
    }
    unset($db);
    //arrays for messages
    $smsMessages   = [];
    $emailMessages = [];
    
    //Check data
    //echo "Dumping \$publishData array with \$limit = ".$limit."\n";
    //die(var_dump($publishData));

    //Loop through publish data to get elements for next searches
    foreach($publishData as $row) {
      $alertID   = $row['apubID'];
      $txt       = $row['apubText'];
      $vesselID  = $row['apubVesselID'];
      $name      = $row['apubVesselName'];
      $event     = $row['apubEvent'];
      $dir       = $row['apubDir'];
      switch($dir) {
        case "upriver"  : $add = "Up";   break;
        case "downriver": $add = "Down"; break;
        default         : $add = "";     break;
      }
      
      //Find alerts for this event and direction for 'any' vessel
      
      $sql = "SELECT alertDest, alertMethod FROM alerts WHERE alertOnAny = 1 AND alertOn".ucfirst($event).$add. " = 1";
      $db = $this->db();
      
      
      try {
        $q2 = $db->query($sql);
        //echo "Tried query was \"".$sql."\"\n";
        //echo "Dumping \$q2 ".var_dump($q2)."\n";
      } catch(PDOException $exception){ 
        echo $exception; 
      }  
      if(!$q2) {
        error_log("No 'Any' alerts found for alertpublish ID $alertID");
        echo "No 'Any' alerts found for alertpublish ID $alertID\n";
        continue;
      } elseif ($q2->rowCount()) {
        $alertOnAnyData = $q2->fetchAll();
        foreach($alertOnAnyData as $row) {
          if($row['alertMethod']=='sms') {
            $smsMsg = ['phone'=>$row['alertDest'], 'text'=>'CRT Alert '.$alertID."\n".$txt, 'event' => $event, 'dir' => $dir, 'alertID' => $alertID];
            $smsMessages[] = $smsMsg;
          } elseif($row['alertMethod']=='email') {
            $emlMsg = ['to'=>$row['alertDest'],  'text'=>$txt, 'subject'=> 'CRT Alert '.$alertID.' for '.$name, 'event' => $event, 'dir' => $dir, 'alertID' => $alertID];
            $emailMessages[] = $emlMsg;
          }
        }
      }      
      unset($db);
      
      //Find alerts for this event and direction for specified vessel   
      $sql = "SELECT alertDest, alertMethod FROM alerts WHERE alertVesselID = ? AND alertOn".ucfirst($event).$add. " = 1";
      $db = $this->db();
      $q3 = $db->prepare($sql);
      $q3->execute([$vesselID]);
      
      if(!$q3) {
        error_log("No vessel specific alerts found for alertpublish ID $alertID");
        echo "No 'vessel specific alerts found for alertpublish ID $alertID\n";
        continue;
      } elseif ($q3->rowCount()) { 
        $alertOnVesselData = $q3->fetchAll();
        foreach($alertOnVesselData as $row) {
          if($row['alertMethod']=='sms') {
            $smsMsg = ['phone'=>$row['alertDest'], 'text'=>'CRT Alert '.$alertID.' '.$txt, 'event' => $event, 'dir' => $dir, 'alertID' => $alertID];
            $smsMessages[] = $smsMsg;
          } elseif($row['alertMethod']=='email') {
            $emlMsg = ['to'=>$row['alertDest'],  'text'=>$txt, 'subject'=> 'CRT Alert '.$alertID.' for '.$name, 'event' => $event, 'dir' => $dir, 'alertID' => $alertID];
            $emailMessages[] = $emlMsg;
          }
        }
      }
      unset($db);
    }

 
     //Test code start point
     //echo "Dumping smsMessages array now....:\n\n";
     //echo var_dump($smsMessages);
     //echo "Dumping emailMessages array now....:\n\n";
     //echo var_dump($emailMessages);
     //Test code end point
     
     //Send $smsMessages & $emailMessages assembled in loops above
     $msgController = new Messages();
     $qtySmsMessages = count($smsMessages);
     if($qtySmsMessages>0) {
        $clickSendResponse = json_decode($msgController->sendSMS($smsMessages));
        $this->generateAlertLogSms($clickSendResponse, $smsMessages);
        echo "Sent $qtySmsMessages SMS messages.\n";
        unset($smsMessages);
     }
      
     $qtyEmailMessages = count($emailMessages);
     if($qtyEmailMessages>0) {
       //$clickSendResponse = json_decode($msgController->sendSMS($emailMessages));
        $msgController->sendEmail($emailMessages);
        $this->generateAlertLogEmail(null, $emailMessages);
        echo "Sent $qtyEmailMessages Email messages.\n";
        unset($emailMessages);
     }
  }
  
  public function generateAlertLogSms($clickSendResponse, $smsMessages) {
    //Gets run by generateAlertMessages() method of this class to document response from sms host
    $csArr = $clickSendResponse->data->messages;
    foreach ($smsMessages as $msg) {
      $data = [];
      $data['alogAlertID']   = intval($msg['alertID']);
      $data['alogDirection'] = $msg['dir'];
      $data['alogType']      = $msg['event'];
      $data['alogMessageTo'] = $msg['phone'];
      $data['alogMessageType'] = 'sms';
      $sms = current($csArr);
      while($sms) {
        if($sms->to == $msg['phone']) {
          $data['alogMessageID']     = $sms->message_id;
          $data['alogMessageCost']    = $sms->message_price;
          $data['alogMessageStatus'] = $sms->status;
          $data['alogTS']            = $sms->schedule;
          break;          
        }
        next($csArr);
      }
      //Test dump
      //echo "AlertsModel::generateAlertLogSms() test dumping data array...\n";
      //var_dump($data);
      $db = $this->db();
      $sql = "INSERT INTO alertlog (alogAlertID, alogType, alogTS, alogDirection, alogMessageType, alogMessageTo, "
      . "alogMessageID, alogMessageCost, alogMessageStatus) VALUES (:alogAlertID, :alogType, :alogTS, "
      . ":alogDirection, :alogMessageType, :alogMessageTo, :alogMessageID, :alogMessageCost, :alogMessageStatus)";
      echo "AlertsModel::generateAlertLogSms()\n";
      $db->prepare($sql)->execute($data);
    }
  }

  public function generateAlertLogEmail($clickSendResponse, $emailMessages ) {
    //Gets run by generateAlertMessages() method of this class to document phpMailer process. clickSend host portion is depreciated
    //$csArr = $clickSendResponse->data->data;
    foreach ($emailMessages as $msg) {
      $data = [];
      $data['alogAlertID']   = intval($msg['alertID']);
      $data['alogDirection'] = $msg['dir'];
      $data['alogType']      = $msg['event'];
      $data['alogMessageTo'] = $msg['to'];
      $data['alogMessageType'] = 'email';
      //$cs = current($csArr);
      //while($cs) {
      //  if($cs->to->email == $msg['to']) {
      //    $data['alogMessageID']     = $cs->message_id;
      //    $data['alogMessgeCost']    = $cs->price;
      //    $data['alogMessageStatus'] = $cs->status;
      //    $data['alogTS']            = $cs->date_added;
      //    break;          
      //  }
      //  next($csArr);
      //}
      $data['alogMessageID']     = 'N/A';
      $data['alogMessageCost']    = 0.0;
      $data['alogMessageStatus'] = 'N/A';
      $data['alogTS']            = time();
      //Test dump
      //echo "AlertsModel::generateAlertLogEmail() test dumping data array...\n";
      //var_dump($data);
      $db = $this->db();
      $sql = "INSERT INTO alertlog (alogAlertID, alogType, alogTS, alogDirection, alogMessageType, alogMessageTo, "
      . "alogMessageID, alogMessageCost, alogMessageStatus) VALUES (:alogAlertID, :alogType, :alogTS, "
      . ":alogDirection, :alogMessageType, :alogMessageTo, :alogMessageID, :alogMessageCost, :alogMessageStatus)";
      echo "AlertsModel::generateAlertLogEmail()\n";
      $db->prepare($sql)->execute($data);
    }
  }

  public function triggerDetectEvent($liveScan) { //Returns alertpublish record id
    $this->postAlertMessage("detected", $liveScan);
    //$this->queueAlertsForVessel($liveScan->liveVesselID, "detect", 21600, "undetermined"); //6 hours
    echo "Alerts monitor Detect Event triggered by ".$liveScan->liveName."  \n";
    $apubID = $this->getLastPublishedAlertId();
    return $apubID;
  }

  public function triggerAlphaEvent($liveScan) { //Returns alertpublish record id
    $this->postAlertMessage("alpha", $liveScan);
    /*
    if($liveScan->liveDirection == 'downriver') {
      $this->queueAlertsForVessel($liveScan->liveVesselID, "alpha", 7200, $liveScan->liveDirection); //2 hours
    } 
    */   
    echo "Alerts monitor Alpha Event triggered by ".$liveScan->liveName."  \n";
    $apubID = $this->getLastPublishedAlertId();
    return $apubID;
  }
  
  public function triggerBravoEvent($liveScan) { //Returns alertpublish record id
    $this->postAlertMessage("bravo", $liveScan);
    //$this->queueAlertsForVessel($liveScan->liveVesselID, "bravo", 7200, $liveScan->liveDirection); //2 hours);
    $apubID = $this->getLastPublishedAlertId();
    echo "Alerts monitor Bravo Event triggered by ".$liveScan->liveName." with \$apubID = ".$apubID."\n";
    return $apubID;
  }
  
  public function triggerCharlieEvent($liveScan) { //Returns alertpublish record id
    $this->postAlertMessage("charlie", $liveScan);
    //$this->queueAlertsForVessel($liveScan->liveVesselID, "charlie", 7200, $liveScan->liveDirection); //2 hours)
    echo "Alerts monitor Charlie Event triggered by ".$liveScan->liveName."  \n";
    $apubID = $this->getLastPublishedAlertId();
    return $apubID;
  }

  public function triggerDeltaEvent($liveScan) { //Returns alertpublish record id
    $this->postAlertMessage("delta", $liveScan);
    /*
    if($liveScan->liveDirection=='upriver') {
      $this->queueAlertsForVessel($liveScan->liveVesselID, "delta", 7200, $liveScan->liveDirection); //2 hours
    } 
    */   
    echo "Alerts monitor Delta Event triggered by ".$liveScan->liveName."  \n";
    $apubID = $this->getLastPublishedAlertId();
    return $apubID;
  }

  public function getLastPublishedAlertId() {
    //Gets run by CRTdaemon::setup() and by $this->trigger_____Event() methods
    $sql = "SELECT apubID FROM alertpublish ORDER BY apubTS DESC LIMIT 1";
    $q = $this->db()->query($sql);
    if($results = $q->fetch()) {
      $apubID = intval($results['apubID']);
      //echo "getLastPublishedAlertId(): \n";
      //echo var_dump($results);
      //echo "\n".$apubID;
      return $apubID;
    }
    return false;
  }
}
