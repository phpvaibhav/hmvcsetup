<?php
//echo "string";die();
	if(!defined('BASEPATH')) exit('No direct script access allowed');
	class Apiv1 extends Common_Service_Controller{	

		function __construct(){

			parent::__construct();
			$this->load->model('image_model'); 
			$this->load->model('service_model'); 
		}
		
		function userRegistration_post(){	
	    $this->load->library('form_validation');
	    $socialId = $this->post('socialId');
	     $userData = array();
	     $userId =''; 
	    if(empty($socialId)){     
	     $this->form_validation->set_rules('fullName', 'full Name', 'trim|required');
	     $this->form_validation->set_rules('email', 'email', 'trim|required|is_unique[users.email]',array('is_unique'=>"Email address already exist."));
	     $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[8]|min_length[6]');
	     $this->form_validation->set_rules('contact', 'caontact No.', 'trim|numeric|max_length[20]|min_length[7]|is_unique[users.contact]',array('is_unique'=>"Contact number already exist."));
	     }
	      else{
	     $this->form_validation->set_rules('email', 'email', 'trim|required');
	      $this->form_validation->set_rules('socialType','SocialType','required');
	      } 
	    if($this->form_validation->run() == FALSE){
	      $response = array('status'=>FAIL,'message'=>strip_tags(validation_errors()));
	      $this->response($response);
	    }else{
	            $image  =1;
	            $profileImage="";
	            if(!empty($_FILES['profileImage']['name'])){ 
	              $folder = 'profile';
	              $this->load->model('image_model');
	              $profileImage = $this->image_model->upload_image('profileImage',$folder);
	                if(!empty($profileImage['error'])){
	                    $response = array('status'=>FAIL,'message'=>$this->upload->display_errors());
	                     $this->response($response);   
	                  }else{
	                  	$userData['profileImage'] =!empty($profileImage) ? $profileImage :"" ;
	                  }  
	            }else{
	            	$userData['profileImage'] = $this->post('profileImage');
	            } 
	            $this->load->library('stripe');
                    $stripe = $this->stripe->create_customer($this->post('email'));
                    $stripe_id = $stripe['data']['id'];
                    
	       $password = $this->post('password'); 
	      $this->load->library('encryption');
	      $authToken = $this->service_model->generate_token();
	      $socialType = $this->post('socialType');
	      
	      $userData['fullName']     = $this->post('fullName');
	      $userData['email']        = $this->post('email');
	      $userData['password']     = password_hash($password, PASSWORD_DEFAULT); 
	      $userData['contact']      = $this->post('contact');
	      $userData['deviceToken']  = $this->post('deviceToken');
	      $userData['deviceType']   = $this->post('deviceType');
	      $userData['socialId']     = $socialId;
	      $userData['customer_stripe_id']     = $stripe_id;
	      $userData['socialType']   = !empty($socialType) ? $socialType: '';
	      $userData['authToken']    = $authToken;
	      $this->load->model('service_model');
	      $isRegister = $this->service_model->registration($userData);

	      //pr($isRegister);
	      
	      if(is_array($isRegister) && $isRegister['regType'] == 'SL'){//successfully login
	        $response = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(106),'userDetail'=>$isRegister['returnData']);
	      
	      } elseif(is_string($isRegister) && $isRegister['regType'] == 'NA'){//user inactive
	        $response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(121));
	      
	      } elseif(is_array($isRegister) && $isRegister['regType'] == 'SR'){//register successfully
	        $response = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(135),'userDetail'=>$isRegister['returnData']);
	      
	      } elseif(is_array($isRegister) && $isRegister['regType'] == 'NR'){//normal registration

	        $response = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(135),'userDetail'=>$isRegister['returnData']);
	      
	      } elseif(is_array($isRegister) && $isRegister['regType'] == 'AE'){//user already exist
	        $response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(117));
	      
	      }elseif(is_string($isRegister) && $isRegister['regType'] == 'EMAIL'){
	      	 $response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(117));
	      }else{
	      	
	        $response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(117));
	      }
	      $this->response($response);
	    }
	} 
	  //end function

	function userLogin_post(){  
	    $this->load->library('form_validation');
	    $this->form_validation->set_rules('email','Email','required');
	    $this->form_validation->set_rules('password','Password','required');
	    if($this->form_validation->run() == FALSE){
	      $responseArray = array('status'=>FAIL,'message'=>strip_tags(validation_errors()));
	      $response = $this->generate_response($responseArray);
	      $this->response($response);
	    } else {      
	      $authToken = $this->service_model->generate_token();
	      $userData = array();
	      $userData['password'] = $this->post('password');
	      $userData['email'] = $this->post('email');
	      $userData['deviceToken'] = $this->post('deviceToken');
	      $userData['deviceType'] = $this->post('deviceType');
	      $userData['userType'] = $this->post('userType');
	      $userData['authToken'] = $authToken;
	      $isLoggedIn = $this->service_model->login($userData,$authToken);
	      
			if($isLoggedIn['returnType'] == 'WP'){
	        $responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(105), 'userDetail'=>array());
	      }elseif( $isLoggedIn['returnType'] == 'SL'){
	        $responseArray = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(106),'userDetail'=>$isLoggedIn['userDetail']);
	      }else{
	        $responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(105));
	      }
	      $response = $this->generate_response($responseArray);
	      $this->response($response);
	    }
  	} 
  //end function
  	public function userInfo_post(){
		if(!$this->check_service_auth()){
    	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
		}
		$length = 50;
		$data = "";
		
			$userId = $this->authData->id;
			$where = array('id'=>$userId);
			$res = $this->service_model->userInfo($where);

			if(!empty($res)){
			$response = array('status'=>SUCCESS,'data'=>$res);
			}else{
				$response = array('status'=>FAIL,'message'=>'invalid user id');
			}
		 $this->response($response);
		}//END OF FUNCTION

		public function logout_get(){
			if(!$this->check_service_auth()){
    		$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
			$userId = $this->authData->id;
			$where = array('id'=>$userId);
			$is_data_exists = $this->common_model->is_data_exists(USERS, $where);
			if(!empty($is_data_exists)){
				$data = array('authToken'=>'','deviceType'=>'','deviceToken'=>'');
				$update = $this->common_model->updateFields(USERS, $data, $where);
				if($update){
					$response = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(106));
				}else{
					$response = array('status'=>FAIL,'message'=>'problem in query');
				}

			}
			$this->response($response);

		}//END OF FUNCTION

		public function update_profile_post(){
	    	if(!$this->check_service_auth()){
        	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}

		    $data = "";
	        $response = array();
	        $photo = '';
	        
	         // pr($pic);
	        $this->form_validation->set_rules('name', ' name','required' ,'max_length[50]|min_length[5]');
	        $this->form_validation->set_rules('email','Email','required');
	        if($this->form_validation->run() == FALSE){
	         	$data=array('status' => FAIL, 'message' => strip_tags(validation_errors()));
   				$this->response($data);
	        }else{
		       	$userId = $this->authData->id;
	        	$check=$this->_change_check_unique_email($userId,$this->post('email'),'users.email');
	        	
        		if($check==FALSE){
                $response = array('status' => FAIL, 'message' => 'Email Already Exists');
                $this->response($response);
        		}else{
			        if(!empty($_FILES['profile_image']['name'])){
			            $this->load->model('image_model');   
			            $upload    =  $_FILES['image_name']['name'];
			            $imageName =  'profile_image';
			            $folder    =  "profile";
			            $response  =  $this->image_model->upload_image($imageName,$folder);
			            
			        }
			       
			        if(!empty($response['error']) && is_array($response)){
				
	                	$image = 0;
	                 	$data =array('status'=>FAIL,'error'=>$this->upload->display_errors());
			 			$this->response($data);
		       		}else{
		       			// pr($where);
			            $dataUpdate = array();
			            $image =   isset($response) ? ($response):"";
			            $dataUpdate['fullName'] = $this->input->post('name');
			            $dataUpdate['email'] = $this->input->post('email');
			            if(!empty($image)){ 
			                $dataUpdate['profileImage'] = $image;
			            }
			            $where = array('id'=>$userId);     
				        $update =   $this->common_model->updateFields(USERS,$dataUpdate,$where); //UPDATE DATABASE.
				             // pr($update);
			            if($update == 1){
			               	$data =array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(108));
				 			$this->response($data);
			            }elseif($update == FALSE)
			            {
			                $data =array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(108));
			                $this->response($data); 
			            }else{
			            	$data =array('status'=>FAIL,'error'=>ResponseMessages::getStatusCodeMessage(105));
			            	$this->response($data); 
			            } 
		        	}
		        }	
	        }
	    }//END OF FUNCTION

	    //validation callback fn to check Phone Number Unique Change Time
    	function _change_check_unique_email($id,$str, $tb_data){
	        $tb_arr = explode(".",$tb_data);
	        $where = array($tb_arr[1]=>$str);

	        $result = $this->common_model->getsingle($tb_arr[0], $where, $fld = NULL, $order_by = '', $order = '');
	        if(!empty($result)){

	        	
	            if($result->id!=$id){
	              
	            $this->form_validation->set_message('_change_check_unique_email',ResponseMessages::getStatusCodeMessage(149));
	              return FALSE;
	            }else{
	              return TRUE;
	            }

	        }
	        else{

	            return TRUE;
	        }     
	    }//END OF FUNCTION

	    public function letter_check($str){
			if (ctype_alpha(str_replace(' ', '', $str)) === false)  {
			  $this->form_validation->set_message('letter_check', 'The ' .$str. ' is not allowed');
			  return FALSE;
			}
		}//END OF FUNCTION

		public function changePassword_post(){

			if(!$this->check_service_auth()){
        	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}

			$this->form_validation->set_rules('oldPassword', 'current password', 'required');
			$this->form_validation->set_rules('newPassword', 'new password', 'required|max_length[20]|min_length[6]|numeric');
			$this->form_validation->set_rules('confirmPassword', 'Confirm password', 'trim|required|matches[newPassword]');
			$userData = array();
			if($this->form_validation->run() == FALSE){
				$response = array('status' => FAIL, 'message' => strip_tags(validation_errors()));
				$this->response($response);

			}else{
				$userId = $this->authData->id;

				$cPassword = $this->post('oldPassword');
				$newPassword = password_hash($this->post('newPassword'),PASSWORD_DEFAULT);
				$user = $this->service_model->getPassword();
				if(password_verify($cPassword, $user->password)){
					$response = $this->service_model->changePassword($newPassword);
					if($response > 0){
						$response = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(144));
					}else{
						$response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(113));
					}
					$this->response($response);
				}else{
					$response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(144));
					$this->response($response);
				}
			}
		} //END OF FUNCTION

		public function add_addresses_post(){
			if(!$this->check_service_auth()){
        	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
			$data = array();
			$datadifferent = array();
			$this->form_validation->set_rules('user_name','user name','required');
			$this->form_validation->set_rules('d_contact_name','delivery Contact name','required');
			$this->form_validation->set_rules('d_contact_no','delivery Contact number','required');
			// $this->form_validation->set_rules('d_email','delivery Contact email','required');
			$this->form_validation->set_rules('d_address','delivery address','required');
			if($this->post('same_address') == 0){
				$this->form_validation->set_rules('b_contact_name','Contact name','required');
				$this->form_validation->set_rules('b_contact_no','Contact number','required');
				// $this->form_validation->set_rules('b_email','Contact email','required');
				$this->form_validation->set_rules('b_address','billing address','required');
			}
			if($this->form_validation->run()== FALSE){
				$response = array('status' => FAIL, 'message' => strip_tags(validation_errors()));
				$this->response($response);
			}else{
				$userId = $this->authData->id;
				$where = array('user_id'=>$userId);
				$update_user = $this->common_model->updateFields(USERS, array('fullName'=>$this->post('user_name')), array('id'=>$userId));
				

				$is_data_exists = $this->common_model->is_data_exists(ADDRESS,$where);
				$data = array(
				'user_id' => $userId,
				'd_address' => $this->post('d_address'),
				'd_city' => $this->post('d_city'),
				'd_state' => $this->post('d_state'),
				'd_zip_code' => $this->post('d_zip_code'),
				'd_contact_name' => $this->post('d_contact_name'),
				'd_contact_no' => $this->post('d_contact_no'),
				'd_email' => $this->post('d_email'),
				'd_lattitude' => $this->post('d_lattitude'),
				'd_longitude' => $this->post('d_longitude'),
				'b_address' => $this->post('d_address'),
				'b_city' => $this->post('d_city'),
				'b_state' => $this->post('d_state'),
				'b_zip_code' => $this->post('d_zip_code'),
				'b_lattitude' => $this->post('d_lattitude'),
				'b_longitude' => $this->post('d_longitude'),
				'b_contact_no' => $this->post('d_contact_no'),
				'b_email' => $this->post('d_email'),
				'b_contact_name' => $this->post('d_contact_name'),
				'same_address' => $this->post('same_address')
				);

				$datadifferent = array(
							'user_id' => $userId,
							'd_address' => $this->post('d_address'),
							'd_city' => $this->post('d_city'),
							'd_state' => $this->post('d_state'),
							'd_zip_code' => $this->post('d_zip_code'),
							'd_contact_name' => $this->post('d_contact_name'),
							'd_contact_no' => $this->post('d_contact_no'),
							'd_email' => $this->post('d_email'),
							'd_lattitude' => $this->post('d_lattitude'),
							'd_longitude' => $this->post('d_longitude'),
							'b_address' => $this->post('b_address'),
							'b_city' => $this->post('b_city'),
							'b_state' => $this->post('b_state'),
							'b_zip_code' => $this->post('b_zip_code'),
							'b_lattitude' => $this->post('b_lattitude'),
							'b_longitude' => $this->post('b_longitude'),
							'b_contact_no' => $this->post('b_contact_no'),
							'b_email' => $this->post('b_email'),
							'b_contact_name' => $this->post('b_contact_name'),
							'same_address' => $this->post('same_address')
									);
				if(!empty($is_data_exists)){
					$addressId = $is_data_exists->addressId;
						$where = array('addressId'=>$addressId);
					if($this->post('same_address')==1){
						$update = $this->common_model->updateFields(ADDRESS, $data,$where);
					}else{
						$update = $this->common_model->updateFields(ADDRESS, $datadifferent,$where);	
					}
					$data = $this->service_model->get_data($addressId);
					//pr($data);
					$response = array('status' => SUCCESS, 'message' => 'Profile updated successfully','data'=>$data);
				}else{
					if($this->post('same_address')==1){
					$insert = $this->common_model->insertData(ADDRESS, $data);
					}else{
						$insert = $this->common_model->insertData(ADDRESS, $datadifferent);	
					}
					$data = $this->service_model->get_data($insert);
					$response = array('status' => SUCCESS, 'message' => 'insert data successfully','data'=>$data);
				}

			}
			$this->response($response);


		}//END OF FUNCTION
	
		public function get_addresses_get(){
			if(!$this->check_service_auth()){
        	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}

			$userId = $this->authData->id;
			$data = $this->service_model->get_addresses($userId);
			if(!empty($data)){
				$response = array('status' => SUCCESS,'data'=>$data);	
			}else{
				$response = array('status' => FAIL, 'message' => ResponseMessages::getStatusCodeMessage(404),'data'=>array());
			}

			$this->response($response);
		}//END OF FUNCTION




		public function get_document_post(){
			if(!$this->check_service_auth()){
        		$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}

				
				$data = $this->service_model->is_data_exist(DOCUMENT, $type);
				if(!empty($data)){
					$response = array('status' => SUCCESS,'data'=>$data);
				}else{
					$response = array('status' => FAIL,'message'=>ResponseMessages::getStatusCodeMessage(404),'data'=>array());
				}	
			$this->response($response);

		}//END OF FUNCTION

				
			

}//END OF CLASS