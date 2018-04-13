<?php

class User_model extends CI_Model {

    var $table = "userinfo";

    function register($dataArray = array()) {
        $this->db->insert($thisi->table, $dataArray);
        $insertId = $this->db->insert_id();
        if ($insertId) {
            return $this->db->insert_id();            
        }
    }  
    

    public function _isValid_user($email = '', $password = '') {
        $condition = array('email' => $email, 'password' => md5($password));
        $query = $this->db->get_where($this->table, $condition);

        if ($email && $password) {
            return $query->row_array();
        }

        return FALSE;
    }

   

    function getuserBYEmail($email) {
        if($email){
            $this->db->select("userID,name,email,password,hash_key");
            $this->db->from('userinfo');
            $this->db->where(array('email' => $email));
            $query = $this->db->get();
            //echo $this->db->last_query();die;
            $userData = $query->row_array();

            if (!empty($userData))
                return $userData;
            else {
                return false;
            }
        }
        else{
            return false;
        }
    }

            

    public function is_emailExist($id, $email) {
        if ($id == "" || $email == "") {
            return false;
        }
        $this->db->from('user');
        $this->db->where(array('email' => $email));
        $this->db->where(array('userID != ' => $id));
        $query = $this->db->get();

        if ($query->num_rows() > 0)
            return false;
        else {
            return true;
        }
    }

    
    public function getUserDetails($id) {
        if ($id == "" ) {
            return false;
        }
        $q = "userID,name,imageURL,gender,CONCAT(countryCode,'.', mobileNo) as mobileNo FROM `userinfo` where userID = $id ";
        
        $this->db->select('userID,name,email,imageURL,gender,CONCAT(countryCode,'.', mobileNo) as mobileNo');
        $this->db->where("userID", $id);
        $this->db->from("userinfo");
        $query = $this->db->get();
       
        //echo $this->db->last_query();die;
        if ($query->num_rows())
            return $query->row();
        else {
            return false;
        }
    }
    
   

}