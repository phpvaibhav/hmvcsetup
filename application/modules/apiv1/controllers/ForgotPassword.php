<?php
	if(!defined('BASEPATH')) exit('No direct script access allowed');
	class ForgotPassword extends Common_Service_Controller{
	    public function forgot_password_post(){
	    	$this->form_validation->CI =& $this;
	    	//$this->load->library('form_validation');
	    	$this->form_validation->set_rules('email', 'Email', 'required|callback_email_check');
	    	 if($this->form_validation->run() == FALSE){
	         	$data=array('status'=>0,'message'=> strip_tags(validation_errors()));
				$this->response($data); exit;
	         }
	    		$email = $_POST['email'];
	         	if(!empty($email)){
	         		$encodingEmail = encoding($email);
	         		$where = array('email'=>$email);
	         		$data = array('password_token'=>0);
	         		$update = $this->common_model->updateFields(USERS, $data, $where);
					$subject = "regarding password change";
					$to = $_POST['email'];	
					$link['link'] =  base_url('password/ChangePassword/forgot_password_page?type=1&email=').$encodingEmail;
					$messag = $this->load->view('reset_password',$link,true);
					$message = $messag;
					$this->load->library('smtp_email');
					$is_send = $this->smtp_email->send_mail($to,$subject,$message);
					
					if($is_send == 1){
						$data=array('status'=>SUCCESS,'message'=>'password is successfully sent on your mail-Id');
					$this->response($data); 

					}else{
						$data=array('status'=>FAIL,'message'=>'problem in send mail');
						$this->response($data); 
					}
				}	
			
	    }

	   
		public function email_check($str){
			$this->db->where('email',$str);
			$query = $this->db->get(USERS)->row();
			if(!empty($query)){
				return TRUE;
			}
			else
			{
				$this->form_validation->set_message('email_check', 'This email is not exist plz enter a valid email');
				return FALSE;
			}
		}
		function generateRandomString($length = 10) {
		    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		    $charactersLength = strlen($characters);
		    $randomString = '';
		    for ($i = 0; $i < $length; $i++) {
		        $randomString .= $characters[rand(0, $charactersLength - 1)];
		    }
		    return $randomString;
		}
		
	}
?>