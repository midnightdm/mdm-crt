<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* * * * * * * *
 * models/AdminModel.php
 * 
 * Class begins below helper function
 * 
 */

//Load S3 classes
$vendorFile = 'vendor/autoload.php' ;
require_once($vendorFile); 

class AdminModel extends CI_Model {
  function __constructor() {
    parent::__constructor();
  }

  function getAlertPublish() {
    $data = [];    
    $this->db->select('*');
    $this->db->order_by('apubTS DESC');
    $this->db->limit(20);
    $q = $this->db->get('alertpublish');
    
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[] = $row;        
      }
    }
    $q->free_result();  
    return $data;
  }  

  function getAllVessels() {
    $data = [];
    //$sql  = "SELECT * from vessels";
    $this->db->select('*');
    $this->db->from('vessels');
    $this->db->order_by('vesselName');
    $q = $this->db->get();
    
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        //Sustitute image placeholder if vesselHasImage is false
        if($row['vesselHasImage'] == false) {
          $row['vesselImageUrl'] = getenv('BASE_URL')."images/vessels/no-image-placard.jpg";
        }
        $data[] = $row;        
      }
    }
    $q->free_result();
    //$data['vesselImageUrl'] = "http://mdm-crt.s3-website.us-east-2.amazonaws.com/vessels/mmsi" . $data['vesselID'] .".jpg";
    return $data;
  }

  function updateVesselWatchOn($vesselID, $vesselWatchOn) {
    $this->db->where('vesselID', $vesselID)
      ->update('vessels', ['vesselWatchOn' => $vesselWatchOn]);
    return true;
  }

  public function insertVessel($dataArr) {
    $this->db->insert('vessels', $dataArr);
    //Additionally create an alert if vesselWatchOn is true
    if($dataArr['vesselWatchOn']==true) {
      $this->addVesselAlert($dataArr);
    }
    return true;    
  }

  public function updateVessel($dataArr) {
    $this->db->where('vesselID', $dataArr['vesselID'])
      ->update('vessels', $dataArr);
    //Additionally create an alert if vesselWatchOn is true
    if($dataArr['vesselWatchOn']==true) {
      $this->addVesselAlert($dataArr);
    } elseif ($dataArr['vesselWatchOn'] == false) {
      $this->removeVesselAlert($dataArr);
    }
    return true;  
  }

  public function addVesselAlert($data) {
    //Includes site specific value passenger interest notification
    $elements = [
      'alertVesselID'=> $data['vesselID'],
      'alertOnAny' => 0,
      'alertMethod' => 'notification',
      'alertDest' => 'passenger',
      'alertCreatedTS' => time(),
      'alertOnAlpha' => 0,
      'alertOnAlphaDown' => 1,
      'alertOnAlphaUp' => 1,
      'alertOnBravo' => 0,
      'alertOnBravoDown' => 1,
      'alertOnBravoUp' => 1,
      'alertOnCharlie' => 0,
      'alertOnCharlieDown' => 1,
      'alertOnCharlieUp' => 1,
      'alertOnDelta' => 0,
      'alertOnDeltaDown' => 1,
      'alertOnDeltaUp' => 1,
      'alertOnDetected' => 1
    ];
    $this->db->insert('alerts', $elements);    
    return true;
  }

  public function removeVesselAlert($data) {
    $this->db->where('alertVesselID', $data['vesselID'])
      ->where('alertMethod', 'notification')
      ->where('alertDest', 'passenger')
      ->delete('alerts');
    return true;
  }

  function getVesselWatchList() {
    $data = [];
    $q= $this->db->where('vesselWatchOn', 1)
      ->order_by('vessels.vesselName')
      ->get('vessels');       
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        //Sustitute image placeholder if vesselHasImage is false
        if($row['vesselHasImage'] == false) {
          $row['vesselImageUrl'] = getenv('BASE_URL')."images/vessels/no-image-placard.jpg";
        }
        $data[] = $row;        
      }
    }
    $q->free_result();
    return $data;
  }

  function getPassagesInTimeRange($rangeArr) {
    $data = [];
    $sql = 'select passages.*, vesselName, vesselType, vesselHasImage, vesselImageUrl '
         . 'from passages, vessels '
         . 'where passageVesselID=vesselID and passageMarkerCharlieTS between ? and ?';
    $q =$this->db->query($sql, $rangeArr);    
    if($q->num_rows()>0) {
      foreach($q->result() as $row) {
        //Sustitute image placeholder if vesselHasImage is false
        if($row->vesselHasImage == false) {
          $row->vesselImageUrl = getenv('BASE_URL')."images/vessels/no-image-placard.jpg";
        }
        $data[] = $row;        
      }  
      $q->free_result();
      return $data;
    } else {
      return false;
    }    
  }


  function lookUpVessel($vesselID) {      
    //See if Vessel data is available locally
    if($data = $this->vesselHasRecord($vesselID)) {
      //echo "Vessel found in database: " . var_dump($data);
      return ["error"=>"Vessel ID is already in the database."];
    }
    //Otherwise scrape data from a website
    $url = 'https://www.myshiptracking.com/vessels/';
    $q = $vesselID;
    $html = grab_page($url, $q);  
    //Edit segment from html string
    $startPos = strpos($html,'<div class="vessels_main_data cell">');
    $clip     = substr($html, $startPos);
    $endPos   = (strpos($clip, '</div>')+6);
    $len      = strlen($clip);
    $edit     = substr($clip, 0, ($endPos-$len));           
    //Use DOM Document class
    $dom = new DOMDocument();
    @ $dom->loadHTML($edit);
    //assign data gleened from mst table rows
    $data = [];
    $rows = $dom->getElementsByTagName('tr');
    //desired rows are 5, 11 & 12
    $data['vesselType'] = $rows->item(5)->getElementsByTagName('td')->item(1)->textContent;
    $data['vesselOwner'] = $rows->item(11)->getElementsByTagName('td')->item(1)->textContent;
    $data['vesselBuilt'] = $rows->item(12)->getElementsByTagName('td')->item(1)->textContent;
   
    //Try for image
    try {
      if(saveImage($vesselID)) {
        //$endPoint = getEnv('AWS_ENDPOINT');
        $base = getEnv('BASE_URL');
        $data['vesselHasImage'] = true;
        $data['vesselImageUrl'] = $base.'vessels/jpg/' . $vesselID;      
      } else {
        $data['vesselHasImage'] = false;
      }
    }
    catch (exception $e) {
      //
      $data['vesselHasImage'] = false;
    }
    //data gleened locally by daemon needs done remotely in manual admin add
    $data['vesselID']       = $vesselID;
    $name                   = $rows->item(0)->getElementsByTagName('td')->item(1)->textContent;
    //Test for no data returned which is probably bad vesselID 
    if($name=="---") {
      return ["error"=>"The provided Vessel ID was not found."];
    }
    $data['vesselCallSign'] = $rows->item(4)->getElementsByTagName('td')->item(1)->textContent;
    $size                   = $rows->item(6)->getElementsByTagName('td')->item(1)->textContent;
    $data['vesselDraft']    = $rows->item(8)->getElementsByTagName('td')->item(1)->textContent;   
    
    //Cleanup parsing needed for some data
    //$name     = trim(substr($name, $startPos)); //Remove white spaces
    $name     = str_replace(',', '', $name);   //Remove commas (,)
    $name     = str_replace('.', ' ', $name); //Add space after (.)
    $name     = str_replace('  ', ' ', $name); //Remove double space
    $name     = ucwords(strtolower($name)); //Change capitalization
    $data['vesselName'] = $name;
    //Format size string into seperate length and width
    if($size=="---") {
      $data['vesselLength'] = "---";
      $data['vesselWidth'] = "---";
    } else if(strpos($size, "x") === false) {
      $data['vesselLength'] = $size;
      $data['vesselWidth'] = $size;
    } else {
      $sizeArr = explode(" ", $size); 
      $data['vesselWidth'] = trim($sizeArr[2])."m";
      $data['vesselLength'] = trim($sizeArr[0])."m";
    }

    return $data;
  } 

  public function getVessel($vesselID) {
    $this->db->select('*');
    $this->db->where('vesselID', $vesselID);
    $q =$this->db->get('vessels');
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[] = $row;        
      }
    }
    $q->free_result();  
    return $data;
  }

  public function vesselHasRecord($vesselID) {
    $this->db->select('*');
    $this->db->where('vesselID', $vesselID);
    return $this->db->get('vessels')->num_rows();
  }

  public function rewriteImagePaths() {
    //Put the ids of all vessels with images in array
    $q = $this->db->select('vesselID')->where('vesselHasImage', 1)->get('vessels');
    $id_arr = array();
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $id_arr[] = $row['vesselID'];
      }
    }
    //update the record of each with the new path filling in the id
    //Note to update the path line to fit the specific new format
    foreach($id_arr as $id) {
      $vesselImageUrl = 'https://www.clintonrivertraffic.com/vessels/jpg/'.$id;
      $data = array('vesselImageUrl' => $vesselImageUrl);
      $this->db->where('vesselID', $id)->update('vessels', $data);
    }
    return true;  
  }

}
