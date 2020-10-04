<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * AlertsModel Class
 * classes/AlertsModel.class.php
 * 
 */
class AlertsModel extends Dbh {
  public function __construct() {
    parent::__construct();
  }

  public function queueAlertsForVessel($vesselID, $event, $gtSeconds, $dir=null) {
    switch($dir) {
      case "upriver"  : $add = "Up";   break;
      case "downriver": $add = "Down"; break;
      default         : $add = "";     break;
    }
    switch($event) {
      case "detect" : $statement = "alertOnDetect";  break;
      case "alpha"  : $statement = "alertOnAlpha";   break;
      case "bravo"  : $statement = "alertOnBravo";   break;
      case "charlie": $statement = "alertOnCharlie"; break;
      case "delta"  : $statement = "alertOnDelta";   break;
      default       : $statement = false;
    }
    if(!$statement) { 
      error_log('Bad event in queueAlertsForVessel('.$event.')');
      return false; 
    }
    $db = $this->db();
    if($add=='') {
      $sql = "SELECT alertID FROM alerts WHERE alertVesselID = ? AND ".$statement." = true AND NOT EXISTS "
        . "(SELECT * FROM alertlog WHERE alogAlertID=alertID AND alogTS > ?)";
    } else {
      $sql = "SELECT alertID FROM alerts WHERE alertVesselID = ? AND (".$statement." = true OR ".$statement.$add." = true)"
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

  public function processQueuedAlert() {
    if(!($row = $this->getFirstVesselQueued())) {
      return;
    }
    $dir  = $row['aqueueDirection'];
    $jt   = $row['aqueueJobTotal'];
    $jr   = $row['aqueueJobRemaining'];
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
        $txt = $this->buildResponse(
          $alt['alertID'],
          $row['aqueueEventType'],
          $alt['liveName'],
          $alt['vesselType'],
          $alt['liveDirection'],
          $row['aqueueInitTS'],
          $alt['liveInitLat'],
          $alt['liveInitLon']
        );
        //Only phone & text get used by sendSms(). Others go to alertlog.
        $msg = ['to'=>$alt['alertDest'], 'text'=>$txt, 'event' => $row['aqueueEventType'], 'dir' => $dir, 'alertID' => $alt['alertID']];
        $smsMessages[] = $msg;        
      }
      $clickSendResponse = json_decode($msgController->sendSMS($smsMessages));
      $this->generateAlertLogSms($clickSendResponse, $smsMessages);
      unset($smsMessages);
      //Package Email Messages
      $emailMessages = [];
      foreach($alerts['email'] as $alt) {
        $txt = $this->buildResponse(
          $alt['alertID'],
          $row['aqueueEventType'],
          $alt['liveName'],
          $alt['vesselType'],
          $alt['liveDirection'],
          $row['aqueueInitTS'],
          $alt['liveInitLat'],
          $alt['liveInitLon']
        );
        $msg = ['phone'=>$alt['alertDest'], 'text'=>$txt];
        $emailMessages[] = $msg;        
      }
      $clickSendResponse = json_decode($msgController->sendSMS($emailMessages));
      $this->generateAlertLogEmail($clickSendResponse, $emailMessages);
      unset($emailMessages);
      //End of loop property updates
      $jr = $jr - $jobsDone;
      $this->refreshAlertQueue($row['aqueueID'], $jr);
    }
  }

  public function getAlertsForVesselEvent($vesselID, $event, $direction, $size, $page) {
    $db = $this->db();
    $txm = []; 
    $emm = [];
    if($direction == "upriver") {
      $add = "Up";
    } else if($direction == "downriver") {
      $add = "Down";
    }
    $eventCol = $direction=="undetermined" ? 
      "alertOn".ucfirst($event)." = true" :
      "(alertOn".ucfirst($event)." = true or alertOn".ucfirst($event).$add." = true) ";

    $sql = "SELECT alerts.*, liveName, liveDirection, liveInitLat, liveInitLon, vesselType FROM alerts, live, vessels WHERE "
        .  "alertVesselID = ? AND ".$eventCol." AND liveVesselID=alertVesselID AND vesselID=alertVesselID ORDER BY "
        .  "alertCreatedTS LIMIT ".$size.", ".$page;
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
    return $data;
  }


  public function buildAlertMessage($alertID, $event, $vesselName, $vesselType, $direction, $ts, $lat, $lon) {
    $loc = "";
    $str = "m/j h:i:sa";
    $offset = date("I") ? -21600 : -18000;
    switch($event) {
      case "detect": $evtDesc = "Transponder was detected ";
                     $loc    .= "\nLocation: ".$lat.", ".$lon; break;
      case "alpha" : $evtDesc = "Crossed 3 mi N of Lock 13 ";  break;
      case "bravo" : $evtDesc = $direction=="downriver" ? "Is leaving " : "Has reached ";
                     $evtDesc .= "Lock 13"; break;
      case "charlie" : $evtDesc = "Is at Clinton RR drawbridge ";  break;
      case "delta" : $evtDesc = "Crossed 3 mi S of drawbridge ";  break;
    }
    $txt  = "CRT Alert: ".$alertID."\n\n".str_replace('Vessel', '', $vesselType);
    $txt .= " Vessel ".$vesselName."\nEvent: ".$evtDesc." traveling ".$direction;
    $txt .= ".\nTime: ".date($str, ($ts-$offset)).$loc;
    return $txt;
  }

  public function getFirstVesselQueued() {
    $sql = "SELECT * FROM alertqueue ORDER BY aqueueInitTS LIMIT 1";
    $db = $this->db();
    $q = $db->query($sql);
    if($results = $q->fetch()) {
      return $results;
    }
    return false;
  }

  public function refreshAlertQueue($aqueueID, $jobsRemaining) {
    if($jobsRemaining>0) {
      $sql = "UPDATE alertqueue SET aqueueJobRemaining = ".$jobsRemaining." WHERE aqueueID = ?";
    } else if($jobsRemaining==0) {
      $sql = "DELETE FROM alertqueue WHERE aqueueID = ?";
    }
    $db = $this->db();
    $db->prepare($sql)->execute([$aeueueID]);
  }

  public function saveInboundSms($ts, $msgID, $from, $body, $alogMessageID, $original) {
    $sql = "INSERT INTO smsin (smsTS, smsMsgID, smsFrom, smsBody, smsOrigMsgID, smsOrigBody) VALUES "
       . "(:smsTS, :smsMsgID, :smsFrom, :smsBody, :smsOrigMsgID, :smsOrigBody)";
    $data['smsTS']         = intval($ts);
    $data['smsMsgID']      = $msgID;
    $data['smsFrom']       = $from;
    $data['smsBody']       = addslashes($body);
    $data['smsOrigMsgID']  = $alogMessageID;
    $data['smsOrigBody']   = addslashes($original);
  }

  public function postAlertMessage($event, $liveScan) {
    $ts = time();
    $vesselType = $liveScan->liveVessel==null ? "" : $liveScan->liveVessel->vesselType;
    $txt = $this->buildAlertMessage(
      "", 
      $event, 
      $liveScan->liveName, 
      $vesselType,
      $liveScan->liveDirection, 
      $ts, 
      $liveScan->liveInitLat, 
      $liveScan->liveInitLon
    );
    $sql = "INSERT INTO alertpublish (apubTS, apubText, apubVesselID, apubVesselName) VALUES ( ". $ts.", ".addslashes($txt).", "
      .$liveScan->liveVesselID.", ".$liveScan->liveName.")";
    $db = $this->db();
    $res = $db->query($sql);
    if($db->lastInsertId()>0) {
      echo "Published ".$event." alert for ".$liveScan->liveName; 
    } else {
      echo "No row count trying to publish for ".$liveScan->liveName; 
    }
    
  }
  
  public function generateAlertLogSms($clickSendResponse, $smsMessages) {
    $csArr = $clickSendResponse->data->messages;
    foreach ($smsMessages as $msg) {
      $data = [];
      $data['alogAlertID']   = $msg['alertID'];
      $data['alogDirection'] = $msg['dir'];
      $data['alogType']      = $msg['event'];
      $data['alogMessageTo'] = $msg['phone'];
      $data['alogMessageType'] = 'sms';
      $sms = current($csArr);
      while($sms) {
        if($sms->to == $msg['phone']) {
          $data['alogMessageID']     = $sms->message_id;
          $data['alogMessgeCost']    = $sms->message_price;
          $data['alogMessageStatus'] = $sms->status;
          $data['alogTS']            = $sms->schedule;
          break;          
        }
        next($csArr);
      }
      $db = $this->db();
      $sql = "INSERT INTO alertlog (alogAlertID, alogType, alogTS, alogDirection, alogMessageType, alogMessageTo, "
      . "alogMessageID, alogMessageCost, alogMessageStatus) VALUES (:alogAlertID, :alogType, :alogTS, "
      . ":alogDirection, :alogMessageType, :alogMessageTo, :alogMessageID, :alogMessageCost, :alogMessageStatus)";
      $db->prepare($sql)->execute($data);
    }
  }

  public function generateAlertLogEmail($clickSendResponse, $emailMessages ) {
    $csArr = $clickSendResponse->data->data;
    foreach ($emailMessages as $msg) {
      $data = [];
      $data['alogAlertID']   = $msg['alertID'];
      $data['alogDirection'] = $msg['dir'];
      $data['alogType']      = $msg['event'];
      $data['alogMessageTo'] = $msg['to'];
      $data['alogMessageType'] = 'email';
      $cs = current($csArr);
      while($cs) {
        if($cs->to->email == $msg['to']) {
          $data['alogMessageID']     = $cs->message_id;
          $data['alogMessgeCost']    = $cs->price;
          $data['alogMessageStatus'] = $cs->status;
          $data['alogTS']            = $cs->date_added;
          break;          
        }
        next($csArr);
      }
      $db = $this->db();
      $sql = "INSERT INTO alertlog (alogAlertID, alogType, alogTS, alogDirection, alogMessageType, alogMessageTo, "
      . "alogMessageID, alogMessageCost, alogMessageStatus) VALUES (:alogAlertID, :alogType, :alogTS, "
      . ":alogDirection, :alogMessageType, :alogMessageTo, :alogMessageID, :alogMessageCost, :alogMessageStatus)";
      $db->prepare($sql)->execute($data);
    }
  }
}
