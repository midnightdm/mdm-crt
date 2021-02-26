<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* * * * * * * *
* models/ProfileModel.php
*/

 
 class ProfileModel extends CI_Model {

    function __constructor() {
        parent::__constructor();
    }
    
    function verifyProfile($token) {
        $this->db->select("*");
        $this->db->where('proToken', $token);
        $this->db->limit(1);
        $q = $this->db->get('alertprofiles');
        if ($q->num_rows() > 0) {                       
            $row = $q->row_array();
            //Update status pending to active 
            if($row['proStatus']=='pending') {
                $data = ['proStatus' => 'active'];
                $this->db->where('proToken', $token);
                $this->db->update('alertprofiles', $data);
            }
            return $row;
        }   
    }

    function addProfile($token, $name, $method, $destination) {
        
        $data = array(
            'proToken' => $token,
            'proName' => $name,
            'proPhone' => '',            
            'proEmail' => '',
            'proMethod' => $method,
            'proStatus' => 'pending'
        );
        switch($method) {
            case 'sms': {
                $data['proPhone'] = $destination;
                break;
            }
            case 'email': {
                $data['proEmail'] = $destination;
                break;
            }
        }
        $this->db->insert('alertprofiles', $data);
    }

    function confirmPendingProfile($token) {

    }

    function disableProfileByPhone($phone) {
        $data = array(
            'proStatus' => 'frozen'
        );
        $this->db->where('proPhone', $phone);
        $this->db->update('alertprofiles', $data);
    }

    function reenableProfile($token, $phone) {
        $this->db->where('proToken', $token);
        $q = $this->db->get('alertprofiles', 1);
        $row = $q->row_array();
        $response = "";
        if(count($row)){
            $proPhone = $row['proPhone'];
            if($proPhone==$phone) {
                $data = ['proStatus' => 'active'];
                $this->db->where('proToken', $token);
                $this->db->update('alertprofiles', $data);
                return 'ok';
            } else {
                return 'Error: Submitted phone number doesn\'t match record.';
            }
        } else {
            return 'Error: Profile token not found.';
        }    
    }
}

?>