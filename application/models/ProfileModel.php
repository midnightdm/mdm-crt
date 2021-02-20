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
        $this->where('proToken', $token);
        $this->limit(1);
        $q = $this->db->get('alertprofiles');
        if ($q->num_rows() > 0) {
            $row = $q->row_array();
            $_SESSION['proToken'] = $token;
            $_SESSION['proName'] = $row['proName'];
            $_SESSION['proPhone'] = $row['proPhone'];
            $_SESSION['proEmail'] = $row['proEmail'];
            $_SESSION['proStatus'] = $row['proStatus'];  
        }   
    }

    function addProfile($token, $name, $phone, $email) {
        $data = array(
            'proToken' => $token,
            'proName' => $name,
            'proPhone' => $phone,
            'proEmail' => $email,
            'proStatus' => 'active'
        );
        $this->db->insert('alertprofiles', $data);
        $_SESSION['proToken'] = $token;
        $_SESSION['proName'] = $row['proName'];
        $_SESSION['proPhone'] = $row['proPhone'];
        $_SESSION['proEmail'] = $row['proEmail'];
        $_SESSION['proStatus'] = $row['proStatus'];
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