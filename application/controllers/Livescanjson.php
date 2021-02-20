<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function grab_page($url, $query='') {	
	$ch = curl_init();
	$ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0";
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 40);
	curl_setopt($ch, CURLOPT_URL, $url.$query);
	//ob_start();
	return curl_exec($ch);
	//ob_end_clean();
	curl_close($ch);
}  

class LiveScanJson extends CI_Controller {

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
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 *
	 *
	 */
	 
	public function index()	{
		//$this->output->cache(0.5);
		$this->load->model('LiveScanModel', '', true);
		$this->load->model('VesselsModel',  '', true);
    $data = [];
		$lsm = $this->LiveScanModel->getAllScans();
		
    foreach($lsm as $live) {
      $inner['liveLastScanTS']       = $live->liveLastTS==null ? $live->liveInitTS : $live->liveLastTS;
      $inner['id']       = $live->liveVesselID;
      $inner['name']     = $live->liveName;
      $inner['position']['lat'] = $live->liveLastLat==null ? $live->liveInitLat : $live->liveLastLat;
      $inner['position']['lng'] = $live->liveLastLon==null ? $live->liveInitLon : $live->liveLastLon;
      $inner['speed'] = $live->liveSpeed;
      $inner['course'] = $live->liveCourse;
      $inner['dest'] = $live->liveDest;
      $inner['length'] = $live->liveLength;
      $inner['width'] = $live->liveWidth;
      $inner['draft'] = $live->liveDraft;
      $inner['callsign'] = $live->liveCallSign;
			$inner['dir'] = $live->liveDirection;
			$inner['liveIsLocal'] = $live->liveIsLocal;
      $inner['liveMarkerAlphaWasReached'] = intval($live->liveMarkerAlphaWasReached);
      $inner['liveMarkerAlphaTS'] = $live->liveMarkerAlphaTS == 0 ? null : $live->liveMarkerAlphaTS;
      $inner['liveMarkerBravoWasReached'] = intval($live->liveMarkerBravoWasReached);
      $inner['liveMarkerBravoTS'] = $live->liveMarkerBravoTS == 0 ? null : $live->liveMarkerBravoTS;
      $inner['liveMarkerCharlieWasReached'] = intval($live->liveMarkerCharlieWasReached);
      $inner['liveMarkerCharlieTS'] = $live->liveMarkerCharlieTS == 0 ? null : $live->liveMarkerCharlieTS;
      $inner['liveMarkerDeltaWasReached'] = intval($live->liveMarkerDeltaWasReached);
      $inner['liveMarkerDeltaTS'] =  $live->liveMarkerDeltaTS == 0 ? null : $live->liveMarkerDeltaTS;

      $vm = $this->VesselsModel->getVessel($live->liveVesselID);
			$vessel = [];
			$vessel['vesselHasImage'] = $vm ? $vm->vesselHasImage : null;      
			$vessel['vesselImageUrl'] = $vm ? $vm->vesselImageUrl : null;        
			$vessel['vesselType']     = $vm ? $vm->vesselType : null;
			$vessel['vesselOwner']    = $vm ? $vm->vesselOwner : null;
			$vessel['vesselBuilt']    = $vm ? $vm->vesselBuilt : null;
			$inner['vessel'] = $vessel;    
 			array_push($data, $inner);
		}	
		echo json_encode($data);
	}
} 
?>