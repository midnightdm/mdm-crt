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
	//echo "This is livescanjson.";
	$jsonStr = grab_page('http://mdm-crt.s3-website.us-east-2.amazonaws.com/json/livescan.json');
	echo $jsonStr;
	}
} 

?>