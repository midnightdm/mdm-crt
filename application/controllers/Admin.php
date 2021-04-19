<?php
defined('BASEPATH') OR exit('No direct script access allowed: Admin.php');




class Admin extends CI_Controller {

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
		//if(empty($_COOKIE)) { $_COOKIE['crt_token'] = ""; } 
        session_start();
        $this->load->model('AdminModel',  '', true);
		$data = array();
		$data["title"] = "Admin";
    	$data["main"]["view"]  = "admin";
		$data["main"]["css"]   = "css/admin.css";
		$data["main"]["path"]  = "";
		$data["items"] = "";


        //Check for valid cookie
        if(isset($_COOKIE['crt_token']) && $_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY']) {
            $_SESSION['adminEmail'] = 'crt_token';
            redirect('admin/watchlist', 'refresh');
        }

        //Check for form submission
        if($this->input->post('email_address')) {
            $u = $this->input->post('email_address');
            $p = $this->input->post('password');
            if($p===$_ENV['MDM_CRT_DB_PWD'] && $u=== $_ENV['MDM_CRT_ERR_EML']) {
                $_SESSION['adminEmail'] = $u;
                $_SESSION['adminPassword'] = $p;     
                $data['response'] = "$u, Password: $p";
                redirect('admin/watchlist');
            } else {
                $data['response'] = "Invalid Login";
            }
        } else {
            $data['response'] = "";
        }
        $this->load->vars($data);
        $this->load->view('template');
	}

    public function logout() {
        session_start();
        if(isset($_SESSION['adminEmail'])) { 
            $_SESSION = array();
            if(isset($_COOKIE['crt_token'])) {
                $data = array();
                $data["title"] = "Admin";
                $data["main"]["view"]  = "admin";
                $data["main"]["css"]   = "css/admin.css";
                $data["main"]["path"]  = "";
                $data['main']['crt_token'] =	'';
                $data['main']['ttl'] = time()-3600; //Cookie Time expired 1 hour
                $this->load->vars($data);
                $this->load->view('admin-template');
            }
        }
        redirect('admin');
    }

    public function updateImageUrls() {
        //Disabled until needed to use

        /*
        $this->load->model('AdminModel',  '', true);
        if($this->AdminModel->rewriteImagePaths()){
            echo "<h2 style=\"color:red\">Image URLs updated.</h2>";
        } else {
            echo "<h2 style=\"color:red\">There was a problem.</h2>";
        }
        */
    }


    public function api_vessels()	{
        //Page for getting vessel data
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if(($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML']) || ($_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY'])) {
                //header('Access-Control-Allow-Origin: http://mdm-crt.s3-website.us-east-2.amazonaws.com');
                $this->load->model('AdminModel', '', true);
                echo '{ "status": "success", "code": 200, "message": "OK", '.json_encode($this->AdminModel->getAllVessels()).'}';
            }
        } else {
            echo '{ "status": "error", "code": 401, "message": "unauthorized" }';
        }  
    } 

    public function api_getMessagesLog() {
        //Page for getting messages log data
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if(($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML']) || ($_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY'])) {
                $this->load->model('AdminModel', '', true);
                echo '{ "status": "success", "code": "200", "message": "OK", "data":'.json_encode($this->AdminModel->getMessagesLog()).'}';
            }
        } else {
            echo '{ "status": "error", "code": 401, "message": "unauthorized" }';
        }  
    }

    public function api_sendMessage() {
        //Page for manually sending a message from the Admin page
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if(($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML']) || ($_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY'])) {               
                if($this->input->post('alertID')=="Sent From Admin Page") {
                    $this->load->model('AlertsModel', '', true);
                    $this->load->library('Messages');
                    //Set post variables
                    $data = array();
                    $data['alertMethod']  = trim($this->input->post('alertMethod'));
                    $data['alertDest']    = trim($this->input->post('alertDest'));
                    $data['text']         = trim($this->input->post('text'));
                    $data['subject']      = trim($this->input->post('subject'));
                    $data['event']        = trim($this->input->post('event'));
                    $data['dir']          = trim($this->input->post('dir'));
                                       
                    switch($data['alertMethod']) {
                        case "sms":{
                            $smsMsg = [
                                'phone' => $data['alertDest'],
                                'text'  => $data['text'],
                                'subject' => $data['subject'],
                                'event' => $data['event'],
                                'dir'   => $data['dir'],
                                'alertID' => null
                            ];
                            $clickSendResponse = $this->messages->sendSMS([$smsMsg]);
                            if(is_object($clickSendResponse)) {
                                $this->AlertsModel->generateAlertLogSms($clickSendResponse, [$smsMsg]);
                            } else {
                                echo '{ "status": "error", "code": "500", "message": <pre>'.
                                    var_dump($clickSendResponse).'</pre> }';
                                return;
                            }
                
                            break;
                        }
                        case "email": { 
                            $emlMsg = [
                                'to' => $data['alertDest'],
                                'text'  => $data['text'],
                                'subject' => $data['subject'],
                                'event' => $data['event'],
                                'dir'   => $data['dir'], 
                                'alertID' => null
                            ]; 
                            $emailResponse = $this->messages->sendEmail([$emlMsg]);
                            if($emailResponse=="okay") {
                                $this->AlertsModel->generateAlertLogEmail($emailResponse, [$emlMsg]);
                            } else {
                                echo '{ "status": "error", "code": "500", "message": '. $emailResponse.'}';
                                return;
                            }
                            break;
                        }
                        case "notification": {
                            $notMsg = [
                                'to' => $data['alertDest'],
                                'text'  => $data['text'],
                                'subject' => $data['subject'],
                                'event' => $data['event'],
                                'dir'   => $data['dir'],
                                'alertID' => null 
                            ]; 
                            $pusherResponse = $this->messages->sendNotification([$notMsg]);
                            $this->AlertsModel->generateAlertLogNotif($pusherResponse, [$notMsg]);
                        }
                    }

                    echo '{ "status": "success", "code": "200", "message": "OK" }';
                }
            }
        } else {
            echo '{ "status": "error", "code": 401, "message": "unauthorized" }';
        }  
    }

    public function api_lookupVessel() {
        //Accepts post of vesselID and returns scraped Vessel data
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if(($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML']) || ($_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY'])) {
                if($this->input->post('vesselID')) {
                    //Set post variables
                    $vesselID      = trim($this->input->post('vesselID'));                
                    $this->load->model('AdminModel',  '', true);
                    $data = $this->AdminModel->lookUpVessel($vesselID);
                    if(isset($data['error'])) {
                        echo '{ "status": "error", "code": 400, "message": "'.$data['error'].'" }';
                    } else {
                        echo '{ "status": "success", "code": 200, "message": "OK", "data": '.json_encode($data).'}';
                    }
                } else {
                    echo '{ "status": "error",  "code": 400, "message": "missing vesselID in post" }';
                }
            }
        } else {
            echo '{ "status": "error", "code": 401, "message": "unauthorized" }';
        }    
    }

    public function api_SetVessel() {
        //Accepts scraped vessel data and saves new vessels record
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if(($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML']) || ($_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY'])) {
                if($this->input->post('vesselID')) {
                    //Set post variables
                    $data = array();
                    $data['vesselID']       = trim($this->input->post('vesselID'));
                    $data['vesselName']     = trim($this->input->post('vesselName'));
                    $data['vesselCallSign'] = trim($this->input->post('vesselCallSign'));
                    $data['vesselType']     = trim($this->input->post('vesselType'));
                    $data['vesselLength']   = trim($this->input->post('vesselLength'));
                    $data['vesselWidth']    = trim($this->input->post('vesselWidth'));
                    $data['vesselDraft']    = trim($this->input->post('vesselDraft'));
                    $data['vesselHasImage'] = trim($this->input->post('vesselHasImage'))=="true"; //convert string to boolean
                    $data['vesselImageUrl'] = trim($this->input->post('vesselImageUrl'));
                    $data['vesselOwner']    = trim($this->input->post('vesselOwner'));
                    $data['vesselBuilt']    = trim($this->input->post('vesselBuilt'));
                    $data['vesselWatchOn']  = trim($this->input->post('vesselWatchOn'))=="true"; //convert string to boolean
                    $data['vesselRecordAddedTS'] = time();                                   
                    $this->load->model('AdminModel',  '', true);
                    //Check if this is insert or update
                    $success = false; //Covers missing postType
                    if($this->input->post('postType') == "insert") {
                        $success = $this->AdminModel->insertVessel($data);
                    } elseif ($this->input->post('postType') == "update") {
                        $success = $this->AdminModel->updateVessel($data);
                    }
                    if($success) {
                        echo '{ "status": "success", "code": 200, "message": "OK", "timestamp": '.$data['vesselRecordAddedTS'].' }';                        
                    } else {
                        echo '{ "status": "error", "code": 400, "message": "Didn\'t save to vessels table" }';
                    }
                } else {
                    echo '{ "status": "error",  "code": 400, "message": "missing data post" }';
                }
            }
        } else {
            echo '{ "status": "error", "code": 401, "message": "unauthorized" }';
        }    
    }



    public function watchlist() {
        //Manage watch list admin page
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if(($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML']) || ($_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY'])) {
                header('Access-Control-Allow-Origin: http://mdm-crt.s3-website.us-east-2.amazonaws.com');
                $data["title"] = "Admin";
                $data["main"]["view"]  = "admin-ko-watchlist";
                $data["main"]["css"]   = "css/admin.css";
                $data["main"]["path"]  = "../";
                $data["items"] = "";
                $data['main']['crt_token'] =	$_ENV['CLICKSEND_KEY'];
                $data['main']['ttl'] = time()+10368000; //Cookie Time to Live is 120 days
                $this->load->model('AdminModel',  '', true);
                if($vesselList = $this->AdminModel->getAllVessels()) {
                    $data['vesselList'] = $vesselList;
                } 
                $this->load->vars($data);
                $this->load->view('admin-template');
            }
        } else {
            redirect('admin', 'refresh');
        }    
    }
    
    
}