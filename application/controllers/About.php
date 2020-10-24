<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class About extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 *  Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 *
	 *
	 */
	 
	public function index()	{
    $data['title'] = "About";
    $data['main']['view']  = "about";
		$data['main']['css']   = "css/about.css";
		$data['main']['path']  = "";
    $this->load->vars($data);
    $this->load->view('template');
	}  
	
	public function status() {
		$data['title'] = "Status";
		$data['main']['view']  = "status";
		$data['main']['css']   = "css/about.css";
		$data['main']['path']  = "../";
		$str    = 'Y-m-d H:i:s';
		$this->load->model('ShipPlotterModel', '', true);
		$status = $this->ShipPlotterModel->getStatus();
		//var_dump($status); 
		$data['isReachable']  = "The Ship Plotter KML server is ";
		$data['isReachable'] .= $status[0]['isReachable'] ? "UP." : "DOWN!";	
    $data['lastUpTS']     = date($str, ($status[0]['lastUpTS'] - 18000));
    $data['lastDownTS']   = date($str, ($status[0]['lastDownTS'] - 18000));
		
		$this->load->vars($data);
		$this->load->view('template');						
	}
} 
?>

