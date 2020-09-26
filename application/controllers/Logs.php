<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logs extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
		* 		http://example.com/index.php/welcome/index
		*	- or -
		* Since this controller is set as the default controller in
		* config/routes.php, it's displayed at http://example.com/
		*
		*  So any other public methods not prefixed with an underscore will
		* map to /index.php/welcome/<method_name>
		* @see https://codeigniter.com/user_guide/general/urls.html
		*
		*
		*/
	 
	public function index()	{
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
    $data['main']['view']  = "logs";
		$data['main']['path']  = "";
		$data['main']['css']   = "css/logs.css";

		//Get a list of all bridge passages	
		$dmodel = $this->LogsModel->getAllCharliePassages();
		$str    = "F j, Y"; 
		$lis     = "";
		if($dmodel) {
			foreach($dmodel as $row) {  			
				$bridge = $row->passageMarkerCharlieTS==0 ? "No Data" : date($str, $row->passageMarkerCharlieTS);
				$url = $row->vesselImageUrl;
				$li  =   <<<EOT
					<li><a href="logs/vessel/{$row->passageVesselID}">{$row->vesselName}</a><br><b>Type: </b><span>{$row->vesselType}</span><br><b>Direction: </b><span>{$row->passageDirection}</span><br><span>$bridge</span><br><img class="vessel" src="$url" height="50" /></li>
					EOT;
					$lis .= $li;
			} 
			$data['lis'] = $lis;
		} else {
			$data['lis'] = '<li>No logged vessels were found.</li>';
		}		
		$this->load->vars($data);
    $this->load->view('template');
	}    
	
	public function today() {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
		$data['main']['view']  = "today";
		$data['main']['css']   = "css/logs.css";
		$data['main']['path']  = "../";
		$data['subtitle'] = "Vessel Passages Today";
		$range = getTodayRange();
		$data['range'] = printRange($range);
		
		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = $row->passageMarkerBravoTS==0 ? "No Data" : date($str, $row->passageMarkerBravoTS); 
				$bridge = $row->passageMarkerCharlieTS==0 ? "No Data" : date($str, $row->passageMarkerCharlieTS);
				$url = $row->vesselImageUrl;
				$tr     =   <<<EOT
					<tr><td>{$row->vesselName}</td><td>{$row->vesselType}</td><td>{$row->passageDirection}</td><td>$lock13</td>
					<td>$bridge</td><td><img src="$url" height="50" /></td></tr>
					EOT;
					$table .= $tr;
			} 
			$data['table'] = $table;
		} else {
			$data['table'] = '<tr><td colspan="6">No vessels were logged during selected range.</td></tr>';
		}
		
	  $this->load->vars($data);
    $this->load->view('template');
	}

	public function yesterday() {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
		$data['subtitle'] = "Vessel Passages Yesterday";
    $data['main']['view']  = "yesterday";
		$data['main']['css']   = "css/logs.css";
		$data['main']['path']  = "../";
		$range = getYesterdayRange();
		$data['range'] = printRange($range);

		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = $row->passageMarkerBravoTS==0 ? "No Data" : date($str, $row->passageMarkerBravoTS); 
				$bridge = $row->passageMarkerCharlieTS==0 ? "No Data" : date($str, $row->passageMarkerCharlieTS);
				$url = $row->vesselImageUrl;     
				$tr     =   <<<EOT
					<tr><td><a href="vessel/{$row->passageVesselID}">{$row->vesselName}</a></td><td>{$row->vesselType}</td><td>{$row->passageDirection}</td><td>$lock13</td>
					<td>$bridge</td><td><img src="$url" height="50" /></td></tr>
					EOT;
					$table .= $tr;
			} 
			$data['table'] = $table;
		} else {
			$data['table'] = '<tr><td colspan="6">No vessels were logged during selected range.</td></tr>';
		}
		
		$this->load->vars($data);
    $this->load->view('template');
	}

	public function past24hours() {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
		$data['subtitle'] = "Vessel Passages Past 24 Hours";
		$data['main']['view']  = "past24hours";
		$data['main']['css']   = "css/logs.css";
		$data['main']['path']  = "../";		
		$range = getLast24HoursRange();
		$data['range'] = printRange($range);

		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = $row->passageMarkerBravoTS==0 ? "No Data" : date($str, $row->passageMarkerBravoTS); 
				$bridge = $row->passageMarkerCharlieTS==0 ? "No Data" : date($str, $row->passageMarkerCharlieTS);
				$url = $row->vesselImageUrl;     
				$tr     =   <<<EOT
					<tr><td><a href="vessel/{$row->passageVesselID}">{$row->vesselName}</a></td><td>{$row->vesselType}</td><td>{$row->passageDirection}</td><td>$lock13</td>
					<td>$bridge</td><td><img src="$url" height="50" /></td></tr>
					EOT;
					$table .= $tr;
			} 
			$data['table'] = $table;
		} else {
			$data['table'] = '<tr><td colspan="6">No vessels were logged during selected range.</td></tr>';
		}

		$this->load->vars($data);
    $this->load->view('template');
	}

	public function past7days() {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
		$data['subtitle'] = "Vessel Passages Past 7 Days";
		$data['main']['view']  = "past7days";
		$data['main']['css']   = "css/logs.css";
		$data['main']['path']  = "../";
		$range = getLast7DaysRange();
		$data['range'] = printRange($range);
				
		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = $row->passageMarkerBravoTS==0 ? "No Data" : date($str, $row->passageMarkerBravoTS); 
				$bridge = $row->passageMarkerCharlieTS==0 ? "No Data" : date($str, $row->passageMarkerCharlieTS);     
				$url = $row->vesselImageUrl;
				$tr     =   <<<EOT
					<tr><td><a href="vessel/{$row->passageVesselID}">{$row->vesselName}</a></td><td>{$row->vesselType}</td><td>{$row->passageDirection}</td><td>$lock13</td>
					<td>$bridge</td><td><img src="$url" height="50" /></td></tr>
					EOT;
					$table .= $tr;
			} 
			$data['table'] = $table;
		} else {
			$data['table'] = '<tr><td colspan="6">No vessels were logged during selected range.</td></tr>';
		}

		$this->load->vars($data);
    $this->load->view('template');
	}

	public function vessel($id) {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Log";		
		$data['main']['view']  = "vessel";
		$data['main']['css']   = "css/logs.css";
		$data['main']['path']  = "../../";
		$id = strval(trim($id));
		$dmodel = $this->LogsModel->getPassagesForVessel($id);
		$str    = "g:ia l, F j, Y"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = $row->passageMarkerBravoTS==0 ? "No Data" : date($str, $row->passageMarkerBravoTS); 
				$bridge = $row->passageMarkerCharlieTS==0 ? "No Data" : date($str, $row->passageMarkerCharlieTS);
				$north3 = $row->passageMarkerAlphaTS==0 ? "No Data" : date($str, $row->passageMarkerAlphaTS);
				$south3 = $row->passageMarkerDeltaTS==0 ? "No Data" : date($str, $row->passageMarkerDeltaTS);    
				$url = $row->vesselImageUrl;
				$tr     =   <<<EOT
					<tr><td>{$row->passageDirection}</td><td>$north3</td><td>$lock13</td><td>$bridge</td>
					<td>$south3</td></tr>
					EOT;
					$table .= $tr;
			} 
			$data['vesselName'] = $dmodel[0]->vesselName;
			$data['vesselType'] = $dmodel[0]->vesselType;
			$data['vesselImageUrl'] = $dmodel[0]->vesselImageUrl;
			$data['table'] = $table;
		} else {
			$data['table'] = '<tr><td colspan="6">No vessels were logged during selected range.</td></tr>';
		}

		$this->load->vars($data);
    $this->load->view('template');
	}

} 

?>
