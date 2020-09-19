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
    $data['main']['css']   = "css/logs.css";

		//Get a list of all vessels for input list
		$data['datalist'] = $this->LogsModel->getVesselDataList();

		$this->load->vars($data);
    $this->load->view('template');
	}    
	
	public function today() {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
		$data['main']['view']  = "today";
		$data['main']['css']   = "css/logs.css";
		$data['subtitle'] = "Vessel Passages Today";
		$range = getTodayRange();
		$data['range'] = printRange($range);
		
		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = date($str, $row->passageMarkerBravoTS); 
				$bridge = date($str, $row->passageMarkerCharlieTS);
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
		$range = getYesterdayRange();
		$data['range'] = printRange($range);

		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = date($str, $row->passageMarkerBravoTS); 
				$bridge = date($str, $row->passageMarkerCharlieTS);
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

	public function past24hours() {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
		$data['subtitle'] = "Vessel Passages Past 24 Hours";
		$data['main']['view']  = "past24hours";
		$data['main']['css']   = "css/logs.css";		
		$range = getLast24HoursRange();
		$data['range'] = printRange($range);

		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = date($str, $row->passageMarkerBravoTS); 
				$bridge = date($str, $row->passageMarkerCharlieTS);
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

	public function past7days() {
		$this->load->model('LogsModel', '', true);
		$data['title'] = "Logs";
		$data['subtitle'] = "Vessel Passages Past 7 Days";
		$data['main']['view']  = "past7days";
		$data['main']['css']   = "css/logs.css";
		$range = getLast7DaysRange();
		$data['range'] = printRange($range);
				
		$dmodel = $this->LogsModel->getPassagesInTimeRange($range);
		$str    = "g:ia l, M j"; 
		$table  = "";
		if($dmodel) {
			foreach($dmodel as $row) {  
				$lock13 = date($str, $row->passageMarkerBravoTS); 
				$bridge = date($str, $row->passageMarkerCharlieTS);     
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

} 

?>
