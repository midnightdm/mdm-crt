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
				$alertTime   = date('c', $row['apubTS']+getTimeOffset());
				//Turn www address into a link
				$text       =  convertUrlToLink($row['apubText']);
				$items .= "<li><h3><a href=\"$vesselLink\">$vesselName</a> <time class=\"timeago\" datetime=\"". $alertTime
				 ."\">".$alertTime."</time></h3><div>Alert# {$alertID}: $text</div></li>";
			}
		}	else {
			$items = "<li>No events to show currently.</li>";
		}
		$data["items"] = $items;
		$this->load->vars($data);
		$this->load->view('template');
	}

	public function watchlist() {
		//passenger vessels list
		$this->load->model('AdminModel',  '', true);
		$data["title"] = "Alerts";
    	$data["main"]["view"]  = "alerts-list";
		$data["main"]["css"]   = "css/alerts.css";
		$data["main"]["path"]  = "../";
		$data['path']          = "../";
		//$dest = $this->input->post('destination');
		$str = 'F j, Y';
		if($dmodel = $this->AdminModel->getVesselWatchList()) {
			$tr = "";
			foreach($dmodel as $row) {
				$tr .= "<tr>";
				$tr .= "<td class=\"w-25\">";
				$tr .= "  <img src=\"".$row['vesselImageUrl']."\" class=\"img-fluid img-thumbnail\" alt=\"Image of ".$row['vesselName']."\" width=\"200\" height=\"160\"/>";
				$tr .= "</td>";
				$tr .= "<td>{$row['vesselType']} Vessel</td>";
				$tr .= "<td>{$row['vesselName']}</td>";
				$tr .= "<th scope=\"row\">{$row['vesselID']}</th>";
				$tr .= "</tr>";
			}
			$data['table_rows'] = $tr;
		} else {
			$data['table_rows'] = "<tr><td colspan=\"4\">Watchlist coming soon</td></tr>";
		}
		$this->load->vars($data);
		$this->load->view('template');		
	}

	public function passenger() {
		//Passenger Alert page
		$this->load->model('AlertsModel',  '', true);
		$data["title"] = "Alerts";
    	$data["main"]["view"]  = "alerts-passenger";
		$data["main"]["css"]   = "css/alerts.css";
		$data["main"]["path"]  = "../";
		$data['path']          = "../";
		$data["items"] = " ";
		$dmodel = $this->AlertsModel->getAlertPublishPassenger();
	
		$items = " ";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$vesselLink = getEnv('BASE_URL')."logs/vessel/".$row['apubVesselID'];
				$vesselName  = $row['apubVesselName'];
				$alertID   = $row['apubID'];
				$alertTime   = date('c', $row['apubTS']+getTimeOffset());
				//Turn www address into a link
				$text       =  convertUrlToLink($row['apubText']);
				$items .= "<li><h3><a href=\"$vesselLink\">$vesselName</a> <time class=\"timeago\" datetime=\"". $alertTime
				 ."\">".$alertTime."</time></h3><div>Alert# {$alertID}: $text</div></li>";
			}
		}	else {
			$items = "<li>No events to show currently.</li>";
		}
		$data["items"] = $items;
		$this->load->vars($data);
		$this->load->view('template');
	}

	public function subscribeAll() {
		//$data["title"] = "Alerts";
		$data["css"]   = "css/alerts.css";
		//$data['path']  = getEnv('BASE_URL')."../";
		$this->load->vars($data);
		$this->load->view('alerts-subscribe-all');
	}

	public function subscribePassenger() {
		//$data["title"] = "Alerts";
		$data["css"]   = "css/alerts.css";
		//$data['path']  = getEnv('BASE_URL')."../";
		$this->load->vars($data);
		$this->load->view('alerts-subscribe-passenger');
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

	public function rssall() {
		$this->load->model('AlertsModel',  '', true);
		$this->load->model('VesselsModel', '', true);
		$str    = "D, j M Y G:i:s \C\D\T"; 
		$offset = getTimeOffset();
		$time   = time();
		$dmodel = $this->AlertsModel->getAlertPublish();
		$data["title"]   = "Clinton River Traffic-ALL VESSELS";
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
				."\t<content:encoded><![CDATA[<img src='$vesselImg' alt='Image of vessel $vesselName'/><p>$hyperText</p>]]></content:encoded>\n\t</item>\n\t";
			}
		}
		$data["items"] = $items;
		$this->load->vars($data);
		$this->load->view('rss-all');
	}

	public function rsspassenger() {
		$this->load->model('AlertsModel',  '', true);
		$this->load->model('VesselsModel', '', true);
		$str    = "D, j M Y G:i:s \C\D\T"; 
		$offset = getTimeOffset();
		$time   = time();
		$dmodel = $this->AlertsModel->getAlertPublishPassenger();
		$data["title"]   = "Clinton River Traffic-PASSENGER VESSELS";
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
				."\t<content:encoded><![CDATA[<img src='$vesselImg' alt='Image of vessel $vesselName'/><p>$hyperText</p>]]></content:encoded>\n\t</item>\n\t";
			}
		}
		$data["items"] = $items;
		$this->load->vars($data);
		$this->load->view('rss-all');
	}
}
?>
