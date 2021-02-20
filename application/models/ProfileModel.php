<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* * * * * * * *
* models/ProfileModel.php
*/

 
 class ProfileModel extends CI_Model {

    function __constructor() {
        parent::__constructor();
    }
    
    function verifyUser($token) {
        $this->db->select("*");
        $this->where('proToken', $token);
        $this->limit(1);
        $q = $this->db->get('alertprofiles');
        if ($q->num_rows() > 0) {
            $row = $q->row_array();
            $_SESSION['proName'] = $row['proName'];
            $_SESSION['proPhone'] = $row['proPhone'];
            $_SESSION['proEmail'] = $row['proEmail'];
            $_SESSION['proStatus'] = $row['proStatus'];
        } else {
            $_SESSION['err_msg'] = "Unable to sign you in automatically. Turn on cookies or use the token URL.";
        }    
    }
}
?>