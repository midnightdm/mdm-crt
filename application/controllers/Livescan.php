<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LiveScan extends CI_Controller {
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
	
	public function index2() {
		
		echo '$host     = getEnv(\'MDM_CRT_DB_HOST\') = '.getEnv('MDM_CRT_DB_HOST')."<br>";
        echo '$dbname   = getEnv(\'MDM_CRT_DB_NAME\') = '.getEnv('MDM_CRT_DB_NAME')."<br>";
        echo '$username = getEnv(\'MDM_CRT_DB_USR\')  = '.getEnv('MDM_CRT_DB_USR')."<br>";
		echo '$password = getEnv(\'MDM_CRT_DB_PWD\')  = '.getEnv('MDM_CRT_DB_PWD');
		echo '$_ENV vardump = '.var_dump($_ENV);

	}

	public function index()	{
		//echo 'This is livescan. <a href="../../css/livescan.css">css</a>';
		header('Access-Control-Allow-Origin: https://maps.googleapis.com');
		header('Access-Control-Allow-Origin: http://mdm-crt.s3-website.us-east-2.amazonaws.com');
    $data['title'] = "Live";
    $data['main']['view']  = "livescan";
		$data['main']['path']  = "";
		$data['main']['css']   = "css/livescan.css";
    $this->load->vars($data);
    $this->load->view('template');
  } 	
} 
?>