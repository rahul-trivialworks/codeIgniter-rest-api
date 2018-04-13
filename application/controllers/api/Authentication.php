<?php  defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Authentication
 * @category        Controller
 * @author          Trivialworks
 * @license         MIT
 * @link            https://github.com/jaykaypee/codeigniter-secure-rest-server
 */
class Authentication extends REST_Controller {
    
    /**
     * This defines the message field name
     * Must be overridden it in a controller so that it is set
     *
     * @var string|NULL
     */
    protected $_status = NULL;
    /**
     * This defines the message field name
     * Must be overridden it in a controller so that it is set
     *
     * @var string|NULL
     */
    protected $_message = '';
    /**
     * This defines the key field name
     * Must be overridden it in a controller so that it is set
     *
     * @var string|NULL
     */
    protected $_key = NULL;
    
    /**
     * This defines the message field name
     * Must be overridden it in a controller so that it is set
     *
     * @var string|NULL
     */
    protected $_data = NULL;
    /**
     * This defines the imgae upload status 
     * Must be overridden it in a controller so that it is set
     *
     * @var false
     */
    protected $_image = true;
    /**
     * This will store the response data for as api return data 
     * Must be overridden it in a controller so that it is set
     *
     * @var array
     */    
    protected $_response = array();
    /**
     * Configure limits on our controller methods
     * Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
     */                     
    protected  $methods = [
        'updateProfile_post'        => ['level' => 1, 'limit' => 50],
        'logout_post'               => ['level' => 1],
        'getUserDetail_post'        => ['level' => 1, 'limit' => 50]
    ];
    /**
     * @def __construct
     * auto call magic function
     */
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->library('Key');
        $this->load->model('user_model','user_model');    
        $this->_status  = $this->config->item('rest_status_field_name');
        $this->_message = $this->config->item('rest_message_field_name');
        $this->_key     = $this->config->item('rest_key_name');
        $this->_data    = $this->config->item('rest_response_data_name');                
    }


    /*
    * @description : This function developed for registration
    * @Method name: register
    */
    public function register_post() {                   

    $postData                           = $this->post();         
    if (!empty($postData['email']))
     {
     $emailData = $this->common_model->_getById('userinfo', array("email" => $postData['email'],'mobileNo_Verified'=>1));
     if(!empty($emailData))
     {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[userinfo.email]');     
     }
     else
     {
     $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');     
     }
    }
            $this->form_validation->set_rules('mobileNo', 'Mobile Number', 'trim|required');
             $this->form_validation->set_rules('isdCode', 'isdCode Number', 'trim|required');
            $this->form_validation->set_rules('fullName', 'Full Name', 'trim|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]');
            $this->form_validation->set_rules('deviceToken', 'Device Token ', 'trim|required');
            $this->form_validation->set_rules('deviceType', 'Device type ', 'trim|required|numeric');

            if ($this->form_validation->run()       == true) {    
                $otp                                = rand(1100, 9999);         
                $insert_data                        = array();
                $insert_data['name']                = $this->db->escape_str($postData['fullName']);
                $insert_data['mobileNo']            = $this->db->escape_str($postData['mobileNo']); 
                $insert_data['countryCode']         = $postData['isdCode']; 
                $insert_data['password']            =  md5($this->db->escape_str($postData['password']));
                $insert_data['email']               = $this->db->escape_str($postData['email']);             
                //other data
                $insert_data['hash_key']  	    = $otp;
                $insert_data['loginType']           = '1';
                $insert_data['createdOn']           = date("Y-m-d H:i:s");
                $insert_data['updatedOn']           = date("Y-m-d H:i:s"); 
                //inserting record into user table                
               	$insertId = $this->common_model->insert('userinfo',$insert_data);                
                if ($insertId) 
                {
                    $usersData = $this->common_model->id_check('userinfo',array('userID'=>$insertId));                 
                    //inserting data into userdevice table
                    $condition      = array('userID' => $insertId, 'deviceToken' => $postData['deviceToken'], 'deviceType' => $postData['deviceType']);
                    $device_data    = $this->common_model->id_check('userdevice',$condition);
                    if(empty($device_data)){
                        $device['userID']       = $insertId;
                        $device['deviceToken']  = $postData['deviceToken'];
                        $device['deviceType']   = $postData['deviceType'];
                        //$device['appversion']   = $postData['appversion'];
                        $device['createdOn']    = date("Y-m-d H:i:s");
                        $device['updatedOn']    = date("Y-m-d H:i:s");
                        $this->common_model->insert('userdevice',$device);
                    }
                  /*
                    $name = $insert_data['fullName'];                   
                    $this->activationmail($insert_data['email'],$name,$insert_data['code']);*/
                    
                    //getting user record after registration successfull                  
                    //generating token after login successfull
                    $access_token                      = $this->key->generate(1, 0, $insertId);                    
                    $this->_response[$this->_status]   = true;
                    $this->_response[$this->_message]  = $this->lang->line('registration_success');
                    $this->_response[$this->_key]      = $access_token;
                    $this->_response[$this->_data]     = $usersData;                 
                    $this->response($this->_response, REST_Controller::HTTP_OK); // CREATED (200) being the HTTP response code 
                }
                else{
                    //if the registration was un-successful
                    $this->_response[$this->_status]   = false;
                    $this->_response[$this->_message]  = 'Registration failed Please try again';                    
                    $this->response($this->_response, REST_Controller::HTTP_OK); // not authoritative (203) being the HTTP response code                 
                }
                }
            } 
            else 
            {
                  if(form_error('fullName')!=''){
                    $this->message = form_error('fullName');
                } else if(form_error('email')!='') {
                    $this->message = form_error('email');
                } else if(form_error('password')!='') {
                    $this->message = form_error('password');
                }
                else if(form_error('mobileNo')!='') {
                    $this->message = form_error('mobileNo');
                }
                else if(form_error('isdCode')!='') {
                    $this->message = form_error('isdCode');
                }
                else if(form_error('deviceToken')!='') {
                    $this->message = form_error('deviceToken');
                }
                else if(form_error('deviceType')!='') {
                    $this->message = form_error('deviceType');
                }
              //  echo "??????". $this->message; die;
              
            	//set the flash data error message if there is one
                $this->_response[$this->_status]  = false;
                $this->_response[$this->_message] =  strip_tags($this->message);                                  
                $this->response($this->_response, REST_Controller::HTTP_OK); // Bad request (400) being the HTTP response code
            }             
        
    }
    /*
    * @description : This function developed for user login
    * @Method name: login
    */
    public function login_post() {
             
             $this->form_validation->set_rules('email', 'Email/mobile', 'trim|required');       
            $this->form_validation->set_rules('password', 'Password', 'trim|required');
            $this->form_validation->set_rules('deviceToken', 'Device Token ', 'trim|required');
            $this->form_validation->set_rules('deviceType', 'Device type ', 'trim|required|numeric');            
            if ($this->form_validation->run() == true) {  
                          $postData       = $this->post();
               
                $email          = $this->db->escape_str($postData['email']);     
                $pass          = $this->db->escape_str($postData['password']); 

                
         
                  $condition      = array('email' => $email,'password'=>md5($pass));                                
                 
              
                                 
                    $userData           = $this->common_model->loginUser($condition);
                     
                    if (!empty($userData)) {   
                        
                        $condition      = array('userID' => $userData['userID'], 'deviceToken' => $postData['deviceToken'], 'deviceType' => $postData['deviceType']);                        
                        $device_data    = $this->common_model->id_check('userdevice',$condition);   
                       
                        if(empty($device_data)){
                            $device['userID']       = $userData['userID'];
                            $device['deviceToken']  = $postData['deviceToken'];
                            $device['deviceType']   = $postData['deviceType'];
                            $device['createdOn']    = date("Y-m-d H:i:s");
                            $device['updatedOn']    = date("Y-m-d H:i:s"); 
                            $id                     = $this->common_model->insert('userdevice',$device);
                        }else{                            
                            $device['deviceToken']  = $postData['deviceToken'];
                            $device['deviceType']   = $postData['deviceType'];
                            $device['updatedOn']    = date("Y-m-d H:i:s");                                 
                            $id                     = $this->common_model->update('userdevice',$condition,$device);                           
                        }

                        //generating token after login successfull
                        $access_token                       = $this->key->generate(1, 0, $userData['userID']);                        
                        $this->_response[$this->_status]    = true;
                        $this->_response[$this->_message]   = $this->lang->line('login_success');
                        $this->_response[$this->_key]       = $access_token;
                        $this->_response[$this->_data]      = $userData;                 
                        $this->response($this->_response, REST_Controller::HTTP_OK); // CREATED (200) being the HTTP response code                   
                    } 
                    else {
                       //if the login was un-successful
                        
                        $this->_response[$this->_status]    = false;
                        $this->_response[$this->_message]  = $this->lang->line('incorrect_login');                    
                        $this->response($this->_response, REST_Controller::HTTP_NON_AUTHORITATIVE_INFORMATION); // not authoritative (203) being the HTTP response code                 
                    }
            }
             
            else {            
                //set the flash data error message if there is one
                
                $this->_response[$this->_status]  = false;
                $this->_response[$this->_message] = validation_errors();                                  
                $this->response($this->_response, REST_Controller::HTTP_BAD_REQUEST); // Bad request (400) being the HTTP response code
            }                               
    }  
        /*
    * @description : This function developed for user logout
    * @Method name: logout
    */
    public function getUserDetail_post()
    {
           //print_r($postData); die;
        $this->form_validation->set_rules('userID', 'userID', 'trim|required|is_natural_no_zero');        
        $this->form_validation->set_rules('deviceToken', 'Device Token', 'trim|required');        
        $this->form_validation->set_rules('deviceType', 'Device Type', 'trim|required');        
        
        if ($this->form_validation->run() == true) {   
        $postData       = $this->post();
        $condition      = array('userID'=>$postData['userID']);                                
                   
                    $userData           = $this->common_model->loginUser($condition);
          //set the validation error into the response
            $this->_response[$this->_status]  = true;
            $this->_response[$this->_message] = "user info:";
            $this->_response[$this->_data]    = $userData;
            $this->response($this->_response, REST_Controller::HTTP_OK); // Bad request (400) being the HTTP response code  
        }
        else
        {
          //set the validation error into the response
            $this->_response[$this->_status]  = false;
            $this->_response[$this->_message] = validation_errors();                                  
            $this->response($this->_response, REST_Controller::HTTP_OK); // Bad request (400) being the HTTP response code  
        }
    }
    
  
  
    /*
    * @description : This function developed for user logout
    * @Method name: logout
    */
    public function logout_post() {

        $this->form_validation->set_rules('userID', 'userID', 'trim|required|is_natural_no_zero');        
        $this->form_validation->set_rules('deviceToken', 'Device Token', 'trim|required');        
        $this->form_validation->set_rules('deviceType', 'Device Type', 'trim|required');        
        
        if ($this->form_validation->run() == true) { 
                          
        $postData = $this->post(); 
            
            $userID     = $postData['userID'];
            $token      = $postData['deviceToken'];
            $deviceType = $postData['deviceType'];
            $condition  = array('userID'    =>  $userID,    'deviceToken' =>  $token, 'deviceType'    =>  $deviceType);          
            //getting api key to suspend it on logout+
         // print_r($condition); die;
      
           //$data=$this->input->request_headers(); print_r($data);die;
               $api_key    = $this->input->request_headers()['X-API-KEY'];
           
            if(!$this->key->suspend($api_key, $userID,1)){
               // echo "dsflkdsl"; die;
                //setting response to the client end 
                $this->_response[$this->_status]   = false;                
                $this->_response[$this->_message]  = $this->lang->line('text_rest_invalid_api_key');
                $this->response($this->_response, REST_Controller::HTTP_OK); // not authoritative (203) being the HTTP response code                 
            }
            else{
                $this->common_model->delete_by_id('userdevice',$condition);//deleting device token records
                //setting response to the client end 
                $this->_response[$this->_status]   = true;              
                $this->_response[$this->_message]  = $this->lang->line('logout_success');
                $this->response($this->_response, REST_Controller::HTTP_OK); // CREATED (200) being the HTTP response code 
            }
        } 
        else {
            //set the validation error into the response
            $this->_response[$this->_status]  = false;
            $this->_response[$this->_message] = validation_errors();                                  
            $this->response($this->_response, REST_Controller::HTTP_OK); // Bad request (400) being the HTTP response code  
        }                              
    }


    
    

}
