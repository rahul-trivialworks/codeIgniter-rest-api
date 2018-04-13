<?php

class Common_model extends CI_Model {


    function insert($table, $dataArray = array()) {
        $this->db->insert($table, $dataArray);
        $insertId = $this->db->insert_id();
        if ($insertId) {
            return $this->db->insert_id();            
        }
    }
    
    
    
  public function update($table,$condition,$data){
  
    $this->db->update($table, $data, $condition); 
    return $this->db->affected_rows();
  }
    
    
      public function id_check($table,$condition){

  $result =array();
    $this->db->from($table);
      $this->db->where($condition);
      $query = $this->db->get();
      if($query->num_rows() > 0){
         $result = $query->row_array();
        return $result;
      }else{
        return $result;
      }
  }
  
  
      public function id_checks($table,$condition){

  $result =array();
    $this->db->from($table);
      $this->db->where($condition);
      $query = $this->db->get();
      if($query->num_rows() > 0){
         $result = $query->result_array();
        return $result;
      }else{
        return $result;
      }
  }
  
 
        public function getDetails($table,$condition){

  $result =array();
  $this->db->select('userID,name,CONCAT(countryCode,'.' mobileNo) as mobileNo');
    $this->db->from($table);
      $this->db->where($condition);
      $query = $this->db->get();
      if($query->num_rows() > 0){
         $result = $query->row_array();
        return $result;
      }else{
        return $result;
      }
  }
  
  
          public function getDetailsNew($table,$condition){

  $result =array();
  $this->db->select('userID,name,CONCAT(countryCode,'.' mobileNo) as mobileNumber');
    $this->db->from($table);
      $this->db->where($condition);
      $query = $this->db->get();
      if($query->num_rows() > 0){
         $result = $query->row_array();
        return $result;
      }else{
        return $result;
      }
  }
   
    /*
  * @parameter : table, condition
  * @description :  This function is developed for get by
  * @Method name: _getById
  */
  public function _getById($table ,$condition='' ){
    if(!empty($table)){
      $this->db->from($table);
      $this->db->where($condition);
      $query = $this->db->get();
      if($query->num_rows() > 0){
        return $query->row_array();
      }else{
        return false;
      }
    }
  }



    function updateByEmail($condition, $data) {
        $dataArray = $data;
        $this->db->from($this->table);
        $this->db->where($condition);
        $update = $this->db->update('user', $dataArray);
        if ($update){
             $this->db->from('user');
             $this->db->where($condition);
             $query = $this->db->get();
             $userData = $query->row_array();

             if (!empty($userData))
                return $userData;
             else {
                return false;
             }
        }
        else
            return false;
    }

    function loginUser($data) {
        $this->db->from('userinfo');
        $this->db->where($data);
        $query = $this->db->get();        
        $userData = $query->row_array();
        if ($query->num_rows() > 0) {
            return $userData;
        } else {
            return false;
        }
    }

 
     

    function _checkfbemailexist($fbid , $email) {

        $this->db->from('user');
        $this->db->where(array('email' => $email, 'facebookId' => $fbid));
        $query = $this->db->get();
        $userData = $query->row_array();

        if (!empty($userData))
            return $userData;
        else {
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
     /*
  * @parameter : table, condition 
  * @description :  This function is developed to delete delete
  * @Method name: delete_by_id
  */
  public function delete_by_id($table,$condition){
           $this->db->where($condition);
        $this->db->delete($table);
        return $this->db->affected_rows();
  }

     public function is_userExist($id) {
        if ($id == "" ) {
            return false;
        }
        $this->db->from('user');
        
        $this->db->where(array('userID = ' => $id));
        $query = $this->db->get();

        if ($query->num_rows() > 0)
            return true;
        else {
            return false;
        }
    }        
}