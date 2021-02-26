
<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * AlertsModel Class [Depreciated methods backup]
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
}