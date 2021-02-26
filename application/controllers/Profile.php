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
		
		$token = $this->uri->segment(2,0);
		echo $token;
	
		if(isset($_COOKIE['crt_token'])) {
			$tk = $_COOKIE['crt_token'];
		} elseif(isset($token)) {
			$tk = $token;
		}
		if(!empty($tk)) {
			$profile = $this->ProfileModel->verifyProfile($tk);
			
				$_SESSION['proToken'] = $tk;
				$_SESSION['proName'] = $profile['proName'];
				$_SESSION['proPhone'] = $profile['proPhone'];
				$_SESSION['proEmail'] = $profile['proEmail'];
				$_SESSION['proMethod'] = $profile['proMethod'];
				$_SESSION['proStatus'] = $profile['proStatus']; 
				echo $token.var_dump($profile);
		}
		
		if(isset($_SESSION['proToken'])) {
			redirect('profile/list', 'refresh');
		}

		$data = [];
		$data['main']['view'] = 'profilelogin';
		$data['main']['css'] = 'css/alerts.css';
		$data['main']['path'] = "../";
		$data['title'] = "Profile";
		$this->load->vars($data);
		$this->load->view('template', $data);
	}

	function list() {
		session_start();
		$this->load->model('AlertsModel',  '', true);
		$data = [];
		$data['main']['view'] = 'profilelist';
		$data['main']['css'] = 'css/alerts.css';
		$data['main']['path'] = "../";
		$data['title'] = "Profile";
		$this->load->vars($data);
		$this->load->view('template', $data);
	}

	function register() {
		session_start();
		$this->load->model('ProfileModel', '', true);
		$token = substr(md5("crtsalt".time()), 0, 16);
		if($this->input->post('destination')) {
			$name = $this->input->post('proName');
			$method = $this->input->post('method');
			$destination = $this->input->post('destination');
		}
		$this->ProfileModel->addProfile($token, $name, $method, $destination);
		$data = [];
		$data['main']['view'] = 'profileconfirm';
		$data['main']['css'] = 'css/alerts.css';
		$data['main']['path'] = "../";
		$data['title'] = "Profile";
		//$data['method'] = $method;
		//$data['name'] = $name;
		//$data['destination'] = $destination;


		$smsWelcome = <<<_END
<p>Welcome, $name. An SMS text message has been sent to $destination. We're going to wait for you to send a reply to the message
before fully activating your service.  This ensures that...</p>
<ul>
	<li>The phone is really yours. (Not some fool you wanted to bombard.)</li>
	<li>You really want to our receive messages. (Some bozo didn't sign you unwillingly.)</li> 
	<li>You received the text okay. (Our resources aren't wasted sending to nowhere.)</li>
	</ul>
_END;
		
		 $emailWelcome = <<<_END
	<p>Welcome, $name. An email message has been sent to $destination. We're going to wait for you to click the confirmation link inside
before fully activating your service.  This ensures that...</p>
<ul>
	<li>The address is really yours. (Not some fool you wanted to bombard)</li>
	<li>You really want to our receive email notices. (Some bozo didn't sign you unwillingly)</li> 
	<li>You received the email okay. (Our resources aren't wasted sending to nowhere.)</li>
	</ul>
_END;
		$data['welcome'] = $method=='sms' ? $smsWelcome : $emailWelcome;
		$this->load->vars($data);
		$this->load->view('template', $data);
	}
}
?>