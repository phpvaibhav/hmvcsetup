<?php

class Product extends Common_Service_Controller
{
		function __construct(){
			parent::__construct();
				$this->load->model('product_model');
				$this->load->model('common_model');
			
		}
		public function product_list_get(){
			if(!$this->check_service_auth()){
	    	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
		
			$water = $this->common_model->getAll(WATER, $order_fld = '', $order_type = '', $select = 'all', $limit = '', $offset = '',$group_by='',$where='');

			$bottle = $this->product_model->getBottle();

			$recycle_bottle = $this->product_model->getRecycleBottle();

			if(!empty($water)){
			$delivery_rates = $this->common_model->getAll(OFFICE_CHARGES, $order_fld = '', $order_type = '', $select = 'all', $limit = '', $offset = '',$group_by='',$where='');

			$response = array('status'=>SUCCESS,'delivery_rates'=>$delivery_rates,'water'=>$water,'bottle'=>$bottle,'recycle_bottle'=>$recycle_bottle);
			}else{
				$response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(404),'data'=>array());
			}
			$this->response($response);
		}//ENd FUnction 

		public function get_order_get(){
			if(!$this->check_service_auth()){
        		$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
			$where = array();
			$where['user_id'] = $this->authData->id;
			$order_status  = trim($this->get('order_status'));// Complete or Pending 
			if($order_status== 'complete'){
				$where['order_status'] = 3;
			}else if($order_status== 'pending'){
				$where['order_status !='] =3;
			}
			$data = $this->product_model->get_ordersList($where);

			if(!empty($data)){
				$response = array('status' => SUCCESS,'data'=>$data);
			}else{
				$response = array('status' => FAIL,'message'=>ResponseMessages::getStatusCodeMessage(404),'data'=>array());
			}
			$this->response($response);				

		}//END OF FUNCTION

		public function order_detail_post(){
			if(!$this->check_service_auth()){
        		$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
			$this->form_validation->set_rules('order_id','order Id ','required');
			if($this->form_validation->run()==FALSE){
				$response = array('status'=>FAIL,'message'=>strip_tags(validation_errors()));
			}
			$where = array('orderId'=>$this->post('order_id'));
			$is_data_exists = $this->common_model->is_data_exists(ORDER,$where);
			if(empty($is_data_exists)){
				$response = array('status'=>FAIL,'message'=>'invalid order Id');
			}else{
				$data = $this->product_model->order_detail($this->post('order_id'));
				if(!empty($data)){
				$response = array('status'=>SUCCESS,'data'=>$data);

				}else{
				$response = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(404),'data'=>array());

				}

			}
			$this->response($response);
				

		}//END OF FUNCTION



		public function notification_list_get(){
			if(!$this->check_service_auth()){
        		$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
			$user_id = $this->authData->id;
			$where = array('user_id'=>$user_id);
			$is_data_exists = $this->product_model->get_notification_list($where);

				if(!empty($is_data_exists)){
					$response = array('status'=>SUCCESS,'data'=>$is_data_exists);	
				}else{
					$response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(404),'data'=>array());
				}
			$this->response($response);

		}

		public function order_post(){
			if(!$this->check_service_auth()){
        	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
		
			$this->form_validation->set_rules('grand_total','grand total','required');
			$this->form_validation->set_rules('product_charge','product charge','required');
			$this->form_validation->set_rules('autherized','autherized','required');
			$this->form_validation->set_rules('payment_method','payment method','required');
			$this->form_validation->set_rules('distance_charge','distance charge','required');
			$this->form_validation->set_rules('delivery_distance','delivery distance','required');
			$this->form_validation->set_rules('auto_pay','auto pay','required');
			$this->form_validation->set_rules('card_id','card id ','required',array("required"=>"please select card"));
			$this->form_validation->set_rules('schedule_type','schedule type ','required',array("required"=>"please select frequency"));
			//$this->form_validation->set_message('schedule_type','please select frequency');
			$this->form_validation->set_rules('address','address','required');
			$this->form_validation->set_rules('product','product','required');
			$this->form_validation->set_rules('end_not_define','end_not_define','required');

			if($this->post('schedule_type')==2 && 3 && 4){
				$this->form_validation->set_rules('start_date','start date ','required');
					
				if($this->post('end_not_define')==1){
					$this->form_validation->set_rules('end_date','end date','required');
					$order['end_date'] = $this->post('end_date');
				}
			}
			if($this->form_validation->run()==FALSE){
				$response = array('status' => FAIL,'message'=>strip_tags(validation_errors()));
			}else{
				
				$product = json_decode($this->post('product'));
				$recycle = json_decode($this->post('recycle'));
				$address = json_decode($this->post('address'));
				
				if($address[0]->same_address == 1){
					$address[0]->b_address = $address[0]->d_address;
					$address[0]->b_city = $address[0]->d_city;
					$address[0]->b_state = $address[0]->d_state;
					$address[0]->b_zip_code = $address[0]->d_zip_code;
					$address[0]->b_lattitude = $address[0]->d_lattitude;
					$address[0]->b_longitude = $address[0]->d_longitude;
					$address[0]->b_contact_no = $address[0]->d_contact_no;
					$address[0]->b_email = $address[0]->d_email;
					$address[0]->b_contact_name = $address[0]->d_contact_name;
				}
				//pr($product);
				if($this->post('end_not_define')==1){
				$order = array('end_date'=> $this->post('end_date'));
				}
				//next saturday date when delivery is going to delivered
				$saturdayDate = Date('Y-m-d', StrToTime("next saturday"));

				$rand =date('y').rand(111,9999).date("md"); //Rendom  invoice  Create
				$userId = $this->authData->id;
				$customer_stripe_id = $this->authData->customer_stripe_id;

				$currentDate =  date("Y-m-d h:i:sa");
				$saturday = date("jS F, Y", strtotime($saturdayDate));
				//getting late and long of user
				$where = array('user_id'=>$userId);
				$userData = $this->common_model->is_data_exists(ADDRESS, $where);


				$grand_total =$this->post('grand_total');
				$schedule_type =$this->post('schedule_type');
				$auto_pay =$this->post('auto_pay');

				$order['grand_total'] = $grand_total;
				$order['delivery_distance'] = $this->post('delivery_distance');
				$order['distance_charge'] = $this->post('distance_charge');
				$order['product_charge'] = $this->post('product_charge');
				$order['invoice_id'] = $rand;
				$order['user_id'] = $userId;
				$order['pay_method'] = $this->post('payment_method');
				$order['autherized'] = $this->post('autherized');
				$order['auto_pay'] = $auto_pay;
				$order['card_id'] = $this->post('card_id');
				$order['schedule'] = $schedule_type;
				$order['start_date'] = $this->post('start_date');
				$order['is_end_date'] = $this->post('end_not_define');
				$order['crd'] =    $order['upd'] = datetime();

				$orderInsert = $this->common_model->insertData(ORDER, $order); //INsert Order  & retrive  Order Id
				if(!empty($orderInsert)){

					//***********************************
					//insert order recycle
					$countRecycle = count($recycle);
					for ($i=0; $i < $countRecycle; $i++) { 
							$recycle[$i]->order_id = $orderInsert;
							$price = $recycle[$i]->price;
							$quantit = $product[$i]->quantity;
							$total = $price*$quantit;

							$recycle[$i]->order_id = $orderInsert;
							$recycle[$i]->total = $total;
					} //End 

					if(!empty($recycle)){
							$insertRecycle =$this->common_model->insertDataBatch(ORDER_RECYCLE,$recycle);
						}//ENd  function
					// Product Add
				
					$countProduct = sizeof($product);
					for ($i=0; $i < $countProduct; $i++) { 
							$product[$i]->order_id = $orderInsert;
							$price = $product[$i]->product_price;
							$quantit = $product[$i]->quantity;
							$total = $price*$quantit;

							$product[$i]->order_id = $orderInsert;
							$product[$i]->total = $total;
					} //End 

					if(!empty($product)){
							$insertProduct =$this->common_model->insertDataBatch(ORDER_PRODUCT,$product);
						}//ENd  function	
					//*****************************************
					// Address Add
						$address[0]->order_id = $orderInsert;
						//$address[0]->user_id = $userId;
						 $dataAddress = isset($address[0]) ? $address[0] :array();
						 $insertAddress =0;
						if(!empty($dataAddress)){
							//Order Delivery Address Manage
							$insertAddress =$this->common_model->insertData(ORDER_DETAIL,$dataAddress);	
						}
					//*******************************************
						//message for delivery and order
                        $notif_msg['body']          = 'your order '.$rand.'  created successfully and delivery will be delivered on '.$saturday;

                        $dataNotifiy['message'] ='your order '.$rand.' is created successfully and delivery will be delivered on '.$saturday;
                        //if schedule type one time then generate bill
						if($schedule_type==1): 
						// Delvery  Manage
							$delivery =array();
							$delivery['order_id'] 	= $orderInsert;
							$delivery['user_id'] 	= $userId;
							$delivery['total_payment'] 	= $grand_total;
							$delivery['delivery_date'] 	= $saturdayDate;
							if(!empty($delivery)){
								//Order Delivery 
								$deliveryId =$this->common_model->insertData(DELIVERY,$delivery);	
									//  schedule Type 1( ONe Time Delivery )  bill Genrate  
									$billGenrate = array();
									$billGenrate['schedule_type'] 	= $schedule_type;
									$billGenrate['order_id'] 		= $orderInsert;
									$billGenrate['grand_payment']   = $grand_total;
									$billGenrate['delivery_id']   	= $deliveryId;
									$billGenrate['user_id']   		= $userId;
									$billGenrate['payFor']   		= $auto_pay;


									//when order type is one time and auto pay off then deduct payment
									if($auto_pay == 0){
	
										$this->load->library('stripe');
                        				$stripe = $this->stripe->pay_by_card_id($grand_total,$customer_stripe_id,$currency='USD');
                        				if(!empty($stripe['status'])){                       					
										//get card Id
										$transaction_id = $stripe['data']['balance_transaction'];//transationId
										$billGenrate['card_id'] = $this->post('card_id');
										$billGenrate['transaction_id'] = $transaction_id;
										$billGenrate['payment_status'] = 1;

						                $notif_msg['body']          = 'your order '.$rand.'  created successfully,payment ' .$grand_total. ' is deducted and delivery will be delivered on '.$saturday;

                        				$dataNotifiy['message'] ='your order '.$rand.' is created successfully,payment ' .$grand_total. ' is deducted and delivery will be delivered on '.$saturday;
                        				}
									}

									$payment =$this->common_model->insertData(BILL_PAYMENT,$billGenrate);

							}
						endif;
							$userData = $this->common_model->is_data_exists(USERS,array('id'=>$userId));
                            $refrenID['token'] = $userData->deviceToken;//device token for push notification
                      
                            $notif_msg['type']          = 'Order';
                            $notif_msg['type']          = 'Order';
                            $notif_msg['title']         = 'Order created successfully';
                            $notif_msg['sound']         = 'default';
                            $notif_msg['order_id']         = $orderInsert;
                

                            $dataNotifiy['title']          = 'Order created successfully';
                            $dataNotifiy['user_id']          = $userId;
                            $dataNotifiy['order_id']          = $orderInsert;
                            $dataNotifiy['type']          = 'Order';
                            $dataNotifiy['is_read']         = '0';
                            $dataNotifiy['current']         = date("Y-m-d h:i:sa");
                            //for notifcation 
                            if(!empty($refrenID['token'])){
                        
                            $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                            }

					$response = array('status' => SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(115));	
					
				}else{
					$response = array('status' => FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));

				} //ENd FUnction  order Create



			} //End Function Validation  
			$this->response($response);
		}//END OF FUNCTION


		function cancelOrder_post(){
			if(!$this->check_service_auth()){
        	$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
			$this->form_validation->set_rules('order_id','order id','required');
			if($this->form_validation->run()==FALSE){
				$response = array('status' => FAIL,'message'=>strip_tags(validation_errors()));
			}else{
				$order_id = $this->post('order_id');
				$where = array('orderId'=>$order_id);
				$isExist = $this->common_model->is_data_exists(ORDER,$where);//check if exist
				if(!empty($isExist)){
					$data = array('order_status'=>4);
					$this->common_model->updateFields(ORDER, $data, $where);
					if($this->db->affected_rows() > 0){
						$response = array('status'=>SUCCESS,'message'=>ResponseMessages::getStatusCodeMessage(129));
					}else{
						$response = array('status' => FAIL,'message'=>ResponseMessages::getStatusCodeMessage(118));

					}
				}else{
					$response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(103));
				}
			}
			$this->response($response);
		}//END OF FUNCTION

		function waterList_get(){
			if(!$this->check_service_auth()){
        		$this->response($this->token_error_msg(), SERVER_ERROR); //authentication failed
			}
			$list = $this->common_model->getAll(WATER);
			if(!empty($list)){
				$response = array('status'=>SUCCESS,'data'=>$list);
			}else{
				$response = array('status'=>FAIL,'message'=>ResponseMessages::getStatusCodeMessage(103));
			}
			$this->response($response);
		}

}
?>