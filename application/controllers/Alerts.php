<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function convertUrlToLink($alertString) {
	$txt = $alertString;
	$p1  = strpos($txt,'https:');   
	if(!$p1) return $txt;
	$url = substr($txt, $p1);
	$p2  = strpos($url, 'q=')+2;
	$deg = substr($url, $p2);
	$front = substr($txt, 0, $p1);
	$link = "<a href=\"".$url."\">".$deg."</a>";
	return $front.$link;
 }

 function getStringBeforeDate($alertString) {
	 $txt = $alertString;
	 $p1  = strpos($txt, '.');
	 $front = substr($txt, 0, $p1);
	 return $front;
 }

class Alerts extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 *  Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 *
	 *
	 */


	public function index()	{
		$this->load->model('AlertsModel',  '', true);
		$data = array();
		$data["title"] = "Alerts";
    	$data["main"]["view"]  = "alerts";
		$data["main"]["css"]   = "css/alerts.css";
		$data["main"]["path"]  = "";
		$data["items"] = "";
		$dmodel = $this->AlertsModel->getAlertPublish();
		
		//$str    = "D M j G:i:s \C\D\T Y"; 
		//$offset = getTimeOffset();
		//$time   = time();
		//$data['pubdate'] = date( $str, ($time + $offset) );
		
		$items = " ";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$vesselLink = getEnv('BASE_URL')."logs/vessel/".$row['apubVesselID'];
				$vesselName  = $row['apubVesselName'];
				$alertID   = $row['apubID'];
				//Turn www address into a link
				$text       =  convertUrlToLink($row['apubText']);
				$items .= "<li><h3><a href=\"$vesselLink\">$vesselName</a></h3><div>Alert# {$alertID}: $text</div></li>";
			}
		}	else {
			$items = "<li>No events to show currently.</li>";
		}
		$data["items"] = $items;
		$this->load->vars($data);
		$this->load->view('template');
	}

	public function list() {
		$this->load->model('AlertsModel',  '', true);
		$data["title"] = "Alerts";
    	$data["main"]["view"]  = "alerts-list";
		$data["main"]["css"]   = "css/alerts.css";
		$data["main"]["path"]  = "../";
		$data["items"] = " ";
		$dest = $this->input->post('destination');
		$str = 'F j, Y';
		if($dmodel = $this->AlertsModel->getAlertsForDest($dest)) {
			$output = "<h2>".ucfirst($dmodel[0]['alertMethod'])." Alerts for ".$dmodel[0]['alertDest']."</h2>\n"
		    ."<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\" width=\"500\">\n"
		    ."<tr valign=\"top\"><th>Vessel ID</th><th>Vessel Name</th><th>Created</th><th></th></tr>\n";		
			foreach ($dmodel as $arr) {				
				$output .= "<tr><td>".$arr[alertVesselID]."</td><td>"
				.$arr[vesselName]."</td><td>". date($str, $arr[alertCreatedTS])
				."</td><td>". anchor('alerts/delete/'.$arr[alertID], 'Delete')."</td></tr>";				
			}
			$output .= "</table>\n";
		} else {
			$radioData1 = array(
				'name'          => 'destType',
				'id'            => 'newsletter',
				'value'         => 'email',
				'checked'       =>  FALSE,			
			);
	
			$radioData2 = array(
				'name'          => 'destType',
				'id'            => 'newsletter',
				'value'         => 'sms',
				'checked'       =>  TRUE,			
			);
			$output = "<h2>Select a delivery method and destination (phone number or email address) to list.</h2>"
			.form_open('alerts/list')
			." Email ".form_radio($radioData1)." SMS ".form_radio($radioData2)." ".form_input(['name'=>'destination', 'size'=>'25'])
			. form_submit('submit', 'Submit');			
		}
		$output .= "<h3>Create new Alert</h3>". form_submit('add', 'New');
		$data["output"] = $output;
		$this->load->vars($data);
		$this->load->view('alerts-list');		

	}

	public function smsapi() {
		if($this->input->post('timestamp')) {
			//Set post variables
			$from          = trim($this->input->post('from'));
			$ts            = trim($this->input->post('timestamp'));
			$body          = trim($this->input->post('body'));
			$original      = trim($this->input->post('original_body'));
			$alogMessageID = trim($this->input->post('original_message_id'));
			$msgID         = trim($this->input->post('message_id'));
			
			$this->load->model('AlertsModel',  '', true);
			$this->AlertsModel->saveInboundSms($ts, $msgID, $from, $body, $alogMessageID, $original);
			//$this->processSmsRequests($body, $original, $alogMessageID);

			//Return Post acknowledgement
			echo '{ "status": 200, "message": "ok" }';			
		} else {
			echo '{ "status": 401, "message": "unauthorized" }';
		}
	}

	public function feed() {
		$this->load->model('AlertsModel',  '', true);
		$this->load->model('VesselsModel', '', true);
		$str    = "D, j M Y G:i:s \C\D\T"; 
		$offset = getTimeOffset();
		$time   = time();
		$dmodel = $this->AlertsModel->getAlertPublish();
		$data["title"]   = "Clinton River Traffic";
		$data["pubdate"] = date( $str, ($time + $offset) );
		$items = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$vesselID  = $row['apubVesselID'];
				$alertID   = $row['apubID'];
				$vesselLink = getEnv('BASE_URL')."logs/vessel/".$vesselID;
				$vesselName  = $row['apubVesselName'];
				$itemPubDate = date( $str, ($row['apubTS']+$offset) );
				$vesselImg  = $this->VesselsModel->getVesselImageUrl($vesselID);
				$text       = $row['apubText'];
				$title      = "Notice# ".$alertID." ".getStringBeforeDate($text);
				$hyperText  = convertUrlToLink($text);
				$items .= "<item>\n\t\t<title>$title</title>\n\t\t<description>$text</description>\n\t\t"
				."<pubDate>$itemPubDate</pubDate>\n\t\t<link>$vesselLink</link>\n\t"
				."\t<content:encoded><![CDATA[<img src='$vesselImg' alt='Image of vessel $vesselName'/><p>$hyperText</p>]]></content:encoded>\n\ts</item>\n\t";
			}
		}
		$data["items"] = $items;
	
		$this->load->vars($data);
		$this->load->view('feed');
	}
}
?>
