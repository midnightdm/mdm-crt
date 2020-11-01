<?php
defined('BASEPATH') OR exit('No direct script access allowed');


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

		$data['title'] = "Alerts";
    $data['main']['view']  = "alerts";
		$data['main']['css']   = "css/alerts.css";
		$data['main']['path']  = "";

		$dmodel = $this->AlertsModel->getAlertPublish();
		
		//$str    = "D M j G:i:s \C\D\T Y"; 
		//$offset = getTimeOffset();
		//$time   = time();
		//$data['pubdate'] = date( $str, ($time + $offset) );
		$items = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$vesselLink = getEnv('BASE_URL')."logs/vessel/".$row['apubVesselID'];
				$vesselName  = $row['apubVesselName'];
				$text       = $row['apubText'];
				$items .= <<<EOT
				<li>
				  <h3><a href="$vesselLink">$vesselName</a></h3>				  
					<div>$text</div>
				</li>
				EOT;
			}
		}	else {
			$items = "<li>No events to show currently.</li>";
		}
		$data['items'] = $items;
	
		$this->load->vars($data);
		$this->load->view('template');
	}

	public function list() {
		$this->load->model('AlertsModel',  '', true);

		$data['title'] = "Alerts";
    $data['main']['view']  = "alert-list";
		$data['main']['css']   = "css/alerts.css";
		$data['main']['path']  = "../";
		$dest = $this->input->post('destination');
		if($dmodel = $this->AlertsModel->getAlertsForDest($dest)) {
			//$output = <<<END
			echo "<h2>".ucfirst($dmodel[0]['alertMethod'])." Alerts for ".$dmodel[0]['alertDest']."</h2>\n";			
			echo "<table border='1' cellspacing='0' cellpadding='3' width='500'>";
			echo "<tr valign='top'>\n";
			echo "<th>Vessel ID</th><th>Vessel Name</th><th>Created</th><th></th></tr>\n";
			foreach ($dmodel as $arr) {				
				echo "<tr><td>".$arr['alertVesselID']       ."</td>";
				echo "<td>"   . $arr['vesselName']   ."</td>";				
				echo "<td>"   . date('F j, Y', $arr['alertCreatedTS'])."</td>";
				echo "<td>"   . anchor('alerts/delete/'.$arr['alertID'], 'Delete') ."</td></tr>";
			}
			//END;
		} else {
			echo "<h2>Select a delivery method and destination (phone number or email address) to list.</h2>\n";
			echo form_open('alerts/list');
			$radioData1 = array(
				'name'          => 'destType',
				'id'            => 'newsletter',
				'value'         => 'email',
				'checked'       =>  TRUE,			
			);
	
			$radioData2 = array(
				'name'          => 'destType',
				'id'            => 'newsletter',
				'value'         => 'sms',
				'checked'       =>  FALSE,			
			);
			echo "Email ".form_radio($radioData1);
			echo "SMS ".form_radio($radioData2);
			echo form_input(['name'=>'destination', 'size'=>'25']);
			echo form_submit('submit', 'Submit');
			$this->load->vars($data);
			$this->load->view('template');
		}
		
		

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
		$str    = "D M j G:i:s \C\D\T Y"; 
		$offset = getTimeOffset();
		$time   = time();
		$dmodel = $this->AlertsModel->getAlertPublish();
		$data['title']   = "Clinton River Traffic";
		$data['pubdate'] = date( $str, ($time + $offset) );
		$items = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$vesselLink = getEnv('BASE_URL')."logs/vessel/".$row['apubVesselID'];
				$vesselName  = $row['apubVesselName'];
				$text       = $row['apubText'];
				$items .= <<<EOT
				<item>
				  <title>$vesselName</title>
				  <link>$vesselLink</link>
				  <description>$text</description>
				</item>
				EOT;
			}
		}
		$data['items'] = $items;
	
		$this->load->vars($data);
		$this->load->view('feed');
	}
} 
?>