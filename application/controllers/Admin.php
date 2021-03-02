<?php
defined('BASEPATH') OR exit('No direct script access allowed');


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
            redirect('admin/list', 'refresh');
        }

        //Check for form submission
        if($this->input->post('email_address')) {
            $u = $this->input->post('email_address');
            $p = $this->input->post('password');
            if($p===$_ENV['MDM_CRT_DB_PWD'] && $u=== $_ENV['MDM_CRT_ERR_EML']) {
                $_SESSION['adminEmail'] = $u;
                $_SESSION['adminPassword'] = $p;        
                $data['response'] = "$u, Password: $p";
                redirect('admin/list', 'refresh');
            } else {
                $data['response'] = "Invalid Login";
            }
        } else {
            $data['response'] = "";
        }

        //$dmodel = $this->AdminModel->getAlertPublish();
		
		
		$items = " ";
		/*
        if($dmodel) {
			foreach($dmodel as $row) {  
				$vesselLink = getEnv('BASE_URL')."logs/vessel/".$row['apubVesselID'];
				$vesselName  = $row['apubVesselName'];
				$alertID   = $row['apubID'];
				//Turn www address into a link
				$text       =  convertUrlToLink($row['apubText']);
				$items .= "<li><h3><a href=\"$vesselLink\">$vesselName</a></h3><div>Alert# {$alertID}: $text</div></li>";
			}
		}	else {
			$items = "<li>No events to show currently.</li>";
		}
		$data["items"] = $items;
		*/
        $this->load->vars($data);
        $this->load->view('template');
	}

    public function list() {
        //Manage watch list admin page
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if(($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML']) || ($_COOKIE['crt_token']==$_ENV['CLICKSEND_KEY'])) {
                $data["title"] = "Admin";
                $data["main"]["view"]  = "admin-list";
                $data["main"]["css"]   = "css/admin.css";
                $data["main"]["path"]  = "../";
                $data["items"] = "";
                $data['main']['crt_token'] =	$_ENV['CLICKSEND_KEY'];
                $this->load->vars($data);
                $this->load->view('admin-template');
            }
        } else {
            redirect('admin', 'refresh');
        }

        
    }

    public function vessels() {
        //Manage Vessels admin page
        session_start();
        $data = array();
        if(isset($_SESSION['adminEmail'])) {
            if($_SESSION['adminEmail']===$_ENV['MDM_CRT_ERR_EML'] || $_COOKIE['crttoken']==$_ENV['CLICKSEND_KEY']) {
                $this->load->model('AdminModel', '', true);
                $data["title"] = "Admin";              
                $data["main"]["css"]   = "css/admin.css";
                $data["main"]["path"]  = "../";
                $data["items"] = "";
                $data['main']['crt_token'] =	$_ENV['CLICKSEND_KEY'];
                //if($this->input->post)
                if($this->input->post('mmsi_form')) {
                    //Handle MMSI form post
                    $vesselID = $this->input->post('mmsi');
                    $data["main"]["view"]  = "admin-vessels-mmsi-handler";
                    $data['dmodel'] = $this->AdminModel->lookUpVessel($vesselID);                        
                    $this->load->vars($data);
                    $this->load->view('admin-template');
                } elseif($this->input->post('submit')=="No") {
                    redirect('admin/vessels', 'refresh');
                } elseif($this->input->post('submit')=="Yes") {
                    //Handle MMSI Save form post...
                    if($this->input->post("save_vessel_form")) {
                        //...coming unchanged as scraped
                        $dmodel = $this->input->post(NULL, false)['dmodel'];
                    } elseif($this->input->post("edit_vessel_form")) {
                        //...coming from edited form
                        $dmodel = $this->input->post(NULL, false);
                        //Kill form data not needed by CRUD function
                        unset($dmodel['submit']);
                        unset($dmodel['edit_vessel_form']);                       
                    }
                    //Put timestamp into data array
                    $dmodel['vesselRecordAddedTS'] = time();
                    $data["main"]["view"]  = "admin-vessels-mmsi-saved";
                    $this->AdminModel->insertVessel($dmodel);                        
                    $this->load->vars($data);
                    $this->load->view('admin-template');    
                } elseif($this->input->post('submit')=="Edit") {
                    //Handle Vessel Edit form post
                    $dmodel = $this->input->post(NULL, false)['dmodel'];
                    //Kill form data not needed by the view model
                    unset($dmodel['submit']);
                    unset($dmodel['save_vessel_form']);
                    $data["main"]["view"]  = "admin-vessels-edit-handler";
                    $data['dmodel'] = $dmodel;                        
                    $data['post_data'] = $this->input->post(NULL, false);
                    $this->load->vars($data);
                    $this->load->view('admin-template'); 
                } else {
                    $data["main"]["view"]  = "admin-vessels";
                    //Get data for all vessels
                    $dmodel = $this->AdminModel->getVesselDataList();                    
                    $data['dmodel'] = $dmodel;
                    $data['post_data'] = $this->input->post(NULL, false);
                    $this->load->vars($data);
                    $this->load->view('admin-template');
                }                
            }
        } else {
            redirect('admin', 'refresh');
        }
    }

    
}