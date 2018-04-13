<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
    
    /**
     * @def __construct
     * auto call magic function
     */
    function __construct()
    {
        // Construct the parent class
        parent::__construct();       
        $this->load->model('user_model','user_model');                        
    }
    /**
     * Index Page for this controller.
     *     
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        $this->load->view('welcome_message');
    }

    /*
    * @description : This function developed for view set password
    * @Method name: setpassword
    */
    public function setpassword(){

        $data['code'] = $this->uri->segment(2);
        $this->load->view('resetpassword', $data);        
    }
    
    /*
    * @description : This function developed for view reset password
    * @Method name: resetpassword
    */
    public function resetpassword(){
        $code = $this->uri->segment(3);
        $userData = $this->user_model->getuserDetailBYCode(array('code' => $code));
        if ($userData) {
            if ($this->input->server('REQUEST_METHOD') === "POST") {
                $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]');
                $this->form_validation->set_rules('password_again', 'Confirm Password', 'trim|required|matches[password]');
               // var_dump($this->form_validation->run());die;
                if ($this->form_validation->run() != FALSE) {
                    $password = md5($this->input->post('password'));                   
                    $updateData = $this->user_model->setPassword(array('userID' => $userData['userID']), $password);

                    if ($updateData) {
                        $this->session->set_flashdata('msg', '<div style="color:green">Your password has been reset successfully.</div>');
                    }
                    redirect('setpassword/'.$code);
                    } else {
                        if(form_error('password')!=''){
                        $this->message = form_error('password');
                        }
                        else if(form_error('password_again')!='') {
                            $this->message = form_error('password_again');
                        } 
                        $this->session->set_flashdata('msg', '<div style="color:red">'.$this->message.'</div>'); 
                    }
              
            }
        } else {
            $this->session->set_flashdata('msg', '<div style="color:red">Your link has been expired.</div>');
            redirect('setpassword/'.$code);
        }
        redirect('setpassword/'.$code);
    }
    
    /*
    * @description : This function developed for verify activation mail
    * @Method name: checkactivation
    */  
    public function activation() {
        $code = $this->uri->segment(2);        
        $dataArray = array('code' => $code);
         
        $data = $this->user_model->getuserDetail($dataArray);
        
        if ($data) {
            $this->session->set_flashdata('msg', '<div style="color:green;text-align: center;">Your account has been activated successfully. Please login with your email and password.</div>');
            $this->load->view('userEmailActivation');                     
        } else {
            $this->session->set_flashdata('msg', '<div style="color:red;text-align: center;">Link has been expired. Contact to the administrator for futhur query.</div>');
            $this->load->view('userEmailActivation');                      
        }
    }
}
