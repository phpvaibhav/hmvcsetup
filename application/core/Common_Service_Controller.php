<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Common controller for service modules
* version: 2.0 (14-08-2018)
*/

require APPPATH . '/libraries/REST_Controller.php';  //load rest library
//use Restserver\Libraries\REST_Controller;

class Common_Service_Controller extends REST_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model('service_model'); //load service model
        $this->load->helper('responseMessages'); //load api response message helper
        $this->load->model('notification_model'); //load push notification model
    }
    
    /**
     * Check auth token of request
     * Modified in ver 2.0
     */
    public function check_service_auth(){
        /*Authtoken*/
        $this->authData = '';
        $header = $this->input->request_headers();
       
        //check if key exist as different server may have different types of key (case sensitive) 
        if(array_key_exists ( 'authToken' , $header )){
            $key = 'authToken';
        }
        elseif(array_key_exists ( 'Authtoken' , $header )){
            $key = 'Authtoken';
        }
        elseif(array_key_exists ( 'AuthToken' , $header )){
            $key = 'AuthToken';
        }
        else{
            $this->response($this->token_error_msg(), SERVER_ERROR); //authetication failed 
        }
       
        $authToken = isset($header[$key]) ? $header[$key] : '';
        $userAuthData =  !empty($authToken) ? $this->service_model->isValidToken($authToken,USERS) : '';
        

        if(empty($userAuthData)){ 
            $this->response($this->token_error_msg(2), SERVER_ERROR); //authetication failed 
        }

        if($userAuthData->status != 1){

            $this->response($this->token_error_msg(1), SERVER_ERROR); //authetication failed, user is inactive 
        } 
        
        //user authenticated successfully
        $this->authData = $userAuthData; 
        return TRUE;
    }
    
    /**
     * Show auth token error message
     * Added in ver 2.0
     */
    public function token_error_msg($inactive_status=1){
        $res_arr = array('message'=>ResponseMessages::getStatusCodeMessage(101),'authToken'=>'','responseCode'=>300, 'isActive'=>1);

        if($inactive_status==1){
            $res_arr['isActive'] = 0; //user is inactive
        }

        return $res_arr;
    }

}//End Class 