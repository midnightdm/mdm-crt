<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* * * * * * * *
 * models/LogsModel.php
 */

class LogsModel extends CI_Model {
  function __constructor() {
    parent::__constructor();
  }

  function getVesselDataList() {
    $data = [];
    //$sql  = "SELECT * from vessels";
    $this->db->select('*');
    $this->db->from('vessels');
    $this->db->order_by('vesselName');
    $q = $this->db->get();
    
    if($q->num_rows()) {
      foreach($q->result_array() as $row) {
        $data[] = $row;        
      }
    }
    $q->free_result();
    //$data['vesselImageUrl'] = "http://mdm-crt.s3-website.us-east-2.amazonaws.com/vessels/mmsi" . $data['vesselID'] .".jpg";
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
        $data[] = $row;        
      }  
      $q->free_result();
      return $data;
    } else {
      return false;
    }    
  }

  function getAllCharliePassages() {
    $data = [];
    $last = null;
    $sql = "select  passageVesselID, passageDirection, passageMarkerCharlieTS, vesselName, vesselType, vesselImageUrl "
      .    "from vessels, passages "
      .    "where vesselID=passageVesselID and passageMarkerCharlieTS > 0 order by vesselName asc, passageMarkerCharlieTS desc";
    $q =$this->db->query($sql);  
    if($q->num_rows()>0) {
      foreach($q->result() as $row) {
        //Eliminate duplicate vessel finds
        if($row->passageVesselID==$last) {
          continue;
        }
        $data[] = $row;
        $last = $row->passageVesselID; 
      }  
      $q->free_result();       
      return $data;
    } else {
      return false;
    }  
  }

  function getPassagesForVessel($id) {
    $data = [];    
    $sql = "select passages.*, vesselName, vesselType, vesselImageUrl from vessels, passages "
      .    "where vesselID = ? and passageVesselID=vesselID order by passageMarkerCharlieTS desc";
    $q =$this->db->query($sql, [$id]);  
    if($q->num_rows()>0) {
      foreach($q->result() as $row) {
        $data[] = $row;
      }  
      $q->free_result();       
      return $data;
    } else {
      return false;
    }
  }
 
}