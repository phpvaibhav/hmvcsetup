<?php
	if(!defined('BASEPATH')) exit('No direct script access allowed');
class Schedule extends Common_Service_Controller{	

		function __construct(){
			parent::__construct();

			$this->load->model('schedule_model'); 
		}
	/**
	* Assign Delevery Per Saturday  Manage By  Order scheduled
	**/
	function assignDelivery_get(){
		// log_event('crone job working', $file_name);
		// die;
		$result = $this->schedule_model->assignDelivery();
		if($result){
			$responseArray = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(200));
		}else{
			$responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));
		}
		
		$response = $this->generate_response($responseArray);
		$this->response($response);

	}//ENd Function 

	function alterMonthlyDelivery_get(){
		$result = $this->schedule_model->alterMonthlyDelivery();
		if($result){
			$responseArray = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(200));
		}else{
			$responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));
		}
		
		$response = $this->generate_response($responseArray);
		$this->response($response);

	}//ENd Function

	function alterMonthlyBillgenerate_get(){
		$result = $this->schedule_model->alterMonthlyBillgenerate();
		if($result){
			$responseArray = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(200));
		}else{
			$responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));
		}
		
		$response = $this->generate_response($responseArray);
		$this->response($response);

	}//ENd Function

	function monthlyPayment_get(){
		$result = $this->schedule_model->monthlyPayment();
		if($result){
			$responseArray = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(200));
		}else{
			$responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));
		}
		
		$response = $this->generate_response($responseArray);
		$this->response($response);

	}//ENd Function

	function alterWeekPayment_get(){
		$result = $this->schedule_model->alterWeekPayment();
		if($result){
			$responseArray = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(200));
		}else{
			$responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));
		}
		
		$response = $this->generate_response($responseArray);
		$this->response($response);

	}//ENd Function

	function manualPayment_post(){
		if(!$this->check_service_auth()){
        	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
		}
		$this->form_validation->set_rules('payment_id', 'payment Id', 'required');
		$this->form_validation->set_rules('card_id', 'card Id', 'required');
		$this->form_validation->set_rules('custumer_id', 'customer Id', 'required');
		$this->form_validation->set_rules('source_type', 'source type', 'required');
		if($this->form_validation->run() == FALSE){
				$response = array('status' => FAIL, 'message' => strip_tags(validation_errors()));
				$this->response($response);
		}else{
			$payment_id = $this->post('payment_id');
			$card_id = $this->post('card_id');
			$custumer_id = $this->post('custumer_id');
			$source_type = $this->post('source_type');
			$where = array('paymentId' =>$payment_id);
			$isExist = $this->common_model->is_data_exists(BILL_PAYMENT,$where);
			if(!empty($isExist)){
				$res = $this->schedule_model->manualPayment($payment_id,$card_id,$custumer_id,$source_type);
				//pr($res);
				if($res == 'TRUE'){
					$responseArray = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(200));
				}elseif($res == "DNC"){
					$responseArray = array('status'=>FAIL,'message'=>"delivery not completed");
				}elseif($res == "TIE"){
					$responseArray = array('status'=>FAIL,'message'=>$stripe['message']);
				}elseif($res == "AP"){
					$responseArray = array('status'=>FAIL,'message'=>"delivery is auto paid");
				}elseif($res == "APC"){
					$responseArray = array('status'=>FAIL,'message'=>"payment is already completed");
				}else{
					$responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));
				}
			}else{
				$responseArray = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));
			}
		}
		$this->response($responseArray);
	}


//******************************checking for notification**************************************
// function sendFCM_get() {
// 	$message['title'] = 'title';
// 	$message['body'] = 'title';
// 	$id = "cxExoTDJ_KY:APA91bHb1ZTiXg42n1AmtYYte2S12_C0PcU5XIGXNiqHe8AlDfP94pfFaR8V5oFKU21r4zjUj03oBtM2mcr2-VkG1-Hdmkc1tMuNm5nZu_NkY4A5Vtn77mhCzrdh4hbvTVZFloc-6t3n"; 

//     $API_ACCESS_KEY = NOTIFICATION_KEY;

//     $url = 'https://fcm.googleapis.com/fcm/send';

//     $fields = array (
//             'registration_ids' => array (
//                     $id
//             ),
//             'data' => array (
//                     "message" => $message,
//                     'message_info' => $message_info,
//             ),                
//             'priority' => 'high',
//             'notification' => array(
//                         'title' => $message['title'],
//                         'body' => $message['body'],                            
//             ),
//     );
//     $fields = json_encode ( $fields );

//     $headers = array (
//             'Authorization: key=' . $API_ACCESS_KEY,
//             'Content-Type: application/json'
//     );
//     $ch = curl_init ();
//     curl_setopt ( $ch, CURLOPT_URL, $url );
//     curl_setopt ( $ch, CURLOPT_POST, true );
//     curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
//     curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
//     curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
//     $result = curl_exec ( $ch );
//     pr($result);
//     curl_close ( $ch );
// }
	
}//ENd Class
?>