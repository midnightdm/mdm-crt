<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* * * * * * * *
* controlers/Profile.php
*/
class Profile extends CI_Controller {

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

	function index() {
		session_start();
		$this->load->model('ProfileModel', '', true);
		
		$token = $this->uri->segment(3) or "";
		if(isset($_COOKIE['crt_token'])) {
			$tk = $_COOKIE['crt_token'];
		} elseif(isset($token)) {
			$tk = $token;
		}
		if(!empty($tk)) {
			$this->ProfileModel->verifyProfile($tk);
		}
		if(isset($_SESSION['proToken'])) {
			redirect('profile/profilelist', 'refresh');
		}

		$data = [];
		$data['main']['view'] = 'profilelogin';
		$data['main']['css'] = 'css/alerts.css';
		$data['main']['path'] = "";
		$data['title'] = "Profile Setup";
		$this->load->vars($data);
		$this->load->view('template', $data);
	}

	function list() {
		$this->load->model('AlertsModel',  '', true);
	}

	function register() {
		$token = substr(md5("crtsalt".time()), 0, 16);
	}
}
?>