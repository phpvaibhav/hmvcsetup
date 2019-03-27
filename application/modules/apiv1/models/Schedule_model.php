<?php
class Schedule_model extends CI_Model
{
    function assignDelivery(){
        
        $delivery_Data =array();
        $currentDate =  date("Y-m-d");
       $saturdayDate = Date('Y-m-d', StrToTime("next saturday"));
       $friday = Date('Y-m-d', StrToTime("today"));
        // $currentDate = Date('Y-m-d', StrToTime("next wednesday"));
       $currentDay = date('l');
        if($currentDay=='Friday'){
            //*****************************************
            $this->db->select("*")->from(ORDER);
            $this->db->where(array('order_status '=>'1','start_date <='=>$currentDate));
            $this->db->where("(end_date >= '$currentDate' OR is_end_date='0')");
            $this->db->where(array('schedule'=>'2'));
            $sql = $this->db->get();

           // echo $this->db->last_query();die;
            if($sql->num_rows()){
               $orders = $sql->result();
                foreach ($orders as $k => $order) {
                    //date conversion
                    $saturday = date("jS F, Y", strtotime($saturdayDate));

                     $delivery_Data[$k]['order_id'] = $order->orderId;
                     $delivery_Data[$k]['user_id'] = $order->user_id;
                     $delivery_Data[$k]['total_payment'] = $order->grand_total;
                     $delivery_Data[$k]['delivery_date'] = $saturdayDate;
                }//End foreach
              
                if(!empty($delivery_Data)){
                     $insert=$this->common_model->insertDataBatch(DELIVERY,$delivery_Data);
                    if(!empty($insert)){
                            $billGenrate = array();
                        foreach ($orders as $k => $order) {  
                            //echo "string";die();
                            //  schedule Type 2( weekly Time Delivery )  bill Genrate  
                            $billGenrate[$k]['schedule_type']   = $order->schedule;
                            $billGenrate[$k]['order_id']        = $order->orderId;
                            $billGenrate[$k]['grand_payment']   = $order->grand_total;
                            $billGenrate[$k]['delivery_id']     = $insert++;
                            $billGenrate[$k]['user_id']         = $order->user_id;
                            $billGenrate[$k]['payFor']          = $order->auto_pay;

                            //getting device token data from users table
                            $userData = $this->common_model->is_data_exists(USERS,array('id'=>$order->user_id));
                            $refrenID['token'] = $userData->deviceToken;//device token for push notification
                            $notif_msg['type']          = 'Delivery';
                            $notif_msg['body']          = 'your order  no '.$order->invoice_id.'  delivery and bill created successfully and delivery will be delivered on '.$saturday;
                            $notif_msg['type']          = 'Delivery';
                            $notif_msg['title']         = 'Delivery created successfully';
                            $notif_msg['sound']         = 'default';
                            $notif_msg['order_id']         = $order->orderId;

                            $dataNotifiy['title']          = 'Delivery created successfully';
                            $dataNotifiy['user_id']          = $order->user_id;
                            $dataNotifiy['order_id']          = $order->orderId;
                            $dataNotifiy['type']          = 'Delivery';
                            $dataNotifiy['is_read']         = '0';
                            $dataNotifiy['current']         = datetime();
                            $dataNotifiy['message'] ='your order  no '.$order->invoice_id.'  delivery and bill created successfully and delivery will be delivered on '.$saturday;
                            //for notifcation 
                            if(!empty($refrenID['token'])){
                            $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                            }
                        }
                            $payment =$this->common_model->insertDataBatch(BILL_PAYMENT,$billGenrate);
                            if(!empty($payment)){
                                return $payment;
                            }else{
                                return false;
                            }
                        }else{
                            return false;
                        }
                }
               
            }// endif  check order
            return false;
            //*****************************************
        }else{
         return false;  
        }//end If 
        return false;
    }//End Function

    function alterMonthlyDelivery(){
        
        $delivery_Data =array();
        $currentDate =  date("Y-m-d");
        $saturdayDate = Date('Y-m-d', StrToTime("next saturday"));
        $currentDay = date('l');
        if($currentDay=='Friday'){
            //*****************************************
            $this->db->select("*")->from(ORDER);
            $this->db->where(array('order_status'=>'1','start_date <='=>$currentDate));
            $this->db->where("(end_date >= '$currentDate' OR is_end_date='0')");
            $this->db->where("(schedule >'2')");                              
            $sql = $this->db->get();

//lq();
            if($sql->num_rows()){   
               $orders = $sql->result();
               $orderCount = sizeof($orders);

               //pr($orders);
               $refrenID = array();
               foreach ($orders as $key => $order) {

                        //date conversion
                    $saturday = date("jS F, Y", strtotime($saturdayDate));
                        //getting device token data from users table
                    $userData = $this->common_model->is_data_exists(USERS,array('id'=>$order->user_id));
                    $refrenID['token'] = $userData->deviceToken;//device token for push notification
                    

                     $delivery_Data['order_id'] = $order->orderId;
                     $delivery_Data['user_id'] = $order->user_id;
                     $delivery_Data['total_payment'] = $order->grand_total;
                     $delivery_Data['delivery_date'] = $saturdayDate;
                   $delivery=$this->common_model->insertData(DELIVERY,$delivery_Data);

                    if(!empty($delivery)){
                            $notif_msg['type']          = 'Delivery';
                            $notif_msg['body']          = 'your order  no '.$order->invoice_id.'  delivery  created successfully and delivery will be delivered by '.$saturday;
                            $notif_msg['type']          = 'Delivery';
                            $notif_msg['title']         = 'Delivery created successfully';
                            $notif_msg['sound']         = 'default';
                            $notif_msg['order_id']         = $order->orderId;

                            $dataNotifiy['title']          = 'Delivery created successfully';
                            $dataNotifiy['user_id']          = $order->user_id;
                            $dataNotifiy['order_id']          = $order->orderId;
                            $dataNotifiy['type']          = 'Delivery';
                            $dataNotifiy['is_read']         = '0';
                            $dataNotifiy['current']         = datetime();
                            $dataNotifiy['message'] ='your order  no '.$order->invoice_id.'  delivery created successfully and delivery will be delivered on '.$saturday;

                        //for notifcation 
                        if(!empty($refrenID['token'])){
                        $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                        }
                    }
                }
                    if($this->db->affected_rows()){
                        return TRUE;
                    }else{
                        return FALSE;
                   }
             }// endif  check order
             return FALSE;
            //*****************************************
        }else{
         return FALSE;  
        }//end If
               
    }

        function alterMonthlyBillgenerate(){
            $currentDate =  date("Y-m-d");
            $monthLastDay = date('Y-m-t');
            $firstDayNextMonth = date('Y-m-d', strtotime('first day of next month'));
            //$fd = ($firstDayNextMonth - time()) / (24 * 3600);
            //date('Y-m-d', strtotime('first day of next month'));

            //initial day and and last day of the month
            $month_ini = new DateTime("first day of last month");//prev ini date
            $month_end = new DateTime("last day of last month");//prev last date

            $month_ini->format('Y-m-d'); 
            $month_end->format('Y-m-d');



            if($currentDate > $monthLastDay){//check condition last day of the month
                $delivery_Data =array();
                $saturdayDate = Date('Y-m-d', StrToTime("next saturday"));

                //getting delivery of last month
                $whereCrd = array('delivery_date <='=>$month_end->format('Y-m-d'),'delivery_date >='=>$month_ini->format('Y-m-d'),'delivery_status'=>2);
                $this->db->where($whereCrd); 
                $deliverData = $this->db->get(DELIVERY);   

                if($deliverData->num_rows()){
                    $deliveryData = $deliverData->result();

                    foreach ($deliveryData as $key => $delivery){
                        
                    $whereOrderId = array('orderId'=>$delivery->order_id,'schedule'=>4);
                    //order data 
                    $isExist = $this->common_model->is_data_exists(ORDER,$whereOrderId);

                    //check if order is already exist in payment table
                    $isOrderExist=$this->common_model->is_id_exist(BILL_PAYMENT,'order_id',$isExist->orderId);

                        if(!empty($isExist)){
                            //if orderId is not exist then insert new records
                            $billGenrate['schedule_type']   = $isExist->schedule;
                            $billGenrate['order_id']        = $isExist->orderId;
                            $billGenrate['grand_payment']   = $isExist->grand_total;
                            $billGenrate['delivery_id']     = $delivery->deliveryId;
                            $billGenrate['user_id']         = $isExist->user_id;
                            $billGenrate['payFor']          = $isExist->auto_pay;

                            if(empty($isOrderExist)){
                                $payment == $this->common_model->insertData(BILL_PAYMENT,$billGenrate);
                            }else{
                                //if orderId already exist then upadte delivery id
                                $where=array('order_id'=>$isOrderExist->order_id,'user_id'=>$isOrderExist->user_id);
                                $billUpdate['delivery_id'] = $isOrderExist->delivery_id.','.$delivery->deliveryId;//update delivery id
                                $payment = $this->common_model->updateFields(BILL_PAYMENT, $billUpdate, $where);
                            }
                              //getting device token data from users table
                            $userData = $this->common_model->is_data_exists(USERS,array('id'=>$isExist->user_id));
                            $refrenID['token'] = $userData->deviceToken;//device token for push notification
                            $notif_msg['type']          = 'Bill';
                            $notif_msg['body']          = 'your order  no '.$isExist->invoice_id.' bill created successfully';
                            $notif_msg['type']          = 'Bill';
                            $notif_msg['title']         = 'Bill created successfully';
                            $notif_msg['sound']         = 'default';
                            $notif_msg['order_id']         = $isExist->orderId;

                            $dataNotifiy['title']          = 'Bill created successfully';
                            $dataNotifiy['user_id']          = $isExist->user_id;
                            $dataNotifiy['order_id']          = $isExist->orderId;
                            $dataNotifiy['type']          = 'Bill';
                            $dataNotifiy['is_read']         = '0';
                            $dataNotifiy['current']         = datetime();
                            $dataNotifiy['message'] ='your order  no '.$isExist->invoice_id.' bill created successfully';

                            //for notifcation 
                            if(!empty($refrenID['token'])){
                            $notification = $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                            }
                        }
                    }
                }
            }
            //15 th day of every month
            $currentFirst = date('Y-m-01');
            $month15th = date("Y-m-d", strtotime("+14 day", strtotime($currentFirst)));

            //current month last date
            $currentLast =  date("Y-m-t", strtotime($currentDate));
            //check condition for month between date
            //pr($currentLast);
            if($currentDate == $month15th || $currentDate == $currentLast){
//echo "string";die();
                $this->db->select('*');
                $this->db->where('delivery_date BETWEEN DATE_SUB(NOW(), INTERVAL 15 DAY) AND NOW()');
                $this->db->where('delivery_status',2);
                $result = $this->db->get(DELIVERY);
                //lq();
                //pr($result->row());
                if($result->num_rows()){
                    $res = $result->result();
                   // pr($res);

                    foreach ($res as $key => $data){
                        
                    $whereOrder_id = array('orderId'=>$data->order_id,'schedule'=>3);
                    //order data 
                    $Exist = $this->common_model->is_data_exists(ORDER,$whereOrder_id);
                    //check if order is already exist in payment table
                    $isOrderExist=$this->common_model->is_id_exist(BILL_PAYMENT,'order_id',$Exist->orderId);

                        if(!empty($Exist)){
                            $billGenrate['schedule_type']   = $Exist->schedule;
                            $billGenrate['order_id']        = $Exist->orderId;
                            $billGenrate['grand_payment']   = $Exist->grand_total;
                            $billGenrate['delivery_id']     = $data->deliveryId;
                            $billGenrate['user_id']         = $Exist->user_id;
                            $billGenrate['payFor']          = $Exist->auto_pay;

                            if(empty($isOrderExist)){
                                $payment == $this->common_model->insertData(BILL_PAYMENT,$billGenrate);
                            }else{
                                //if orderId already exist then upadte delivery id
                                $where=array('order_id'=>$isOrderExist->order_id,'user_id'=>$isOrderExist->user_id);
                                $billUpdate['delivery_id'] = $isOrderExist->delivery_id.','.$data->deliveryId;//update delivery id
                                $payment = $this->common_model->updateFields(BILL_PAYMENT, $billUpdate, $where);
                            };
                              //getting device token data from users table
                            $userData = $this->common_model->is_data_exists(USERS,array('id'=>$Exist->user_id));
                            $refrenID['token'] = $userData->deviceToken;//device token for push notification
                           $notif_msg['type']          = 'Bill';
                            $notif_msg['body']          = 'your order  no '.$Exist->invoice_id.' bill created successfully';
                            $notif_msg['type']          = 'Bill';
                            $notif_msg['title']         = 'Bill created successfully';
                            $notif_msg['sound']         = 'default';
                            $notif_msg['order_id']         = $Exist->orderId;

                            $dataNotifiy['title']          = 'Bill created successfully';
                            $dataNotifiy['user_id']          = $Exist->user_id;
                            $dataNotifiy['order_id']          = $Exist->orderId;
                            $dataNotifiy['type']          = 'Bill';
                            $dataNotifiy['is_read']         = '0';
                            $dataNotifiy['current']         = datetime();
                            $dataNotifiy['message'] ='your order  no '.$Exist->invoice_id.' bill created successfully';

                            //for notifcation 
                            if(!empty($refrenID['token'])){
                            $notification = $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                            }
                        }
                    }

                }

            }
            if($payment == TRUE){
                return TRUE;
            }else{
                return FALSE;
            }                        
    }



    function alterWeekPayment(){
        $currentDate =  date("Y-m-d");//current date of the month
        $currentFirst = date('Y-m-01');//current month first date

        //last dte of the month
        $currentLast =  date("Y-m-t", strtotime($currentDate));

        //month 16th date
        $month15th = date("Y-m-d", strtotime("+15 day", strtotime($currentFirst))); 
        //pr($currentLast);
        //check condition for date 16th and after last date of the month 
        if($currentDate == $month15th || $currentDate >= $currentLast){
            $delivery_data = array();
            $delivery_id = array();
            $this->db->select("*");
            $this->db->from(BILL_PAYMENT);
            $this->db->where('crd BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND NOW()');
            $this->db->where('payment_status',0);
            $this->db->where('schedule_type',3);
            $sql = $this->db->get();
            if($sql->num_rows()){
                $res = $sql->result();//delivery data
                
                foreach ($res as $k => $data) {
                    //count no. of ids
                    $ids = explode(',', $data->delivery_id);
                    //check for completing alternate week
                    $where = array('orderId'=>$data->order_id,'schedule'=>'3','auto_pay'=>'1');//where job complete
                    $this->db->where($where);
                    $order = $this->db->get(ORDER);//get order data
                   // pr($order->row());
                    if($order->num_rows()){
                        $order_data = $order->row();
                        $card_id = $order_data->card_id;//card_id

                        //count payment depends on no. of delivery
                        $payment = $order_data->grand_total*sizeof($ids); 
                        $distance = $order_data->delivery_distance;//distance

                        //custId from users table
                        $whereUser = array('id'=>$order_data->user_id);
                        $custId = $this->common_model->is_data_exists(USERS, $whereUser)->customer_stripe_id;

                        //getting transation Id
                        $this->load->library('stripe');
                        $stripe = $this->stripe->pay_by_card_id($payment,$custId,$currency='USD');
                        //pr($stripe);
                        if($stripe['status']==""){
                            return FALSE;
                        }else{
                        $transaction_id = $stripe['data']['balance_transaction'];//transationId
                        //pr($transaction_id);

                        $where = array('order_id'=>$order_data->orderId,'user_id'=>$order_data->user_id);

                        $data = array('transaction_id'=>$transaction_id,'card_id'=>$card_id,'payment_status'=>1);

                        $update = $this->common_model->updateFields(BILL_PAYMENT, $data, $where);

                        }
                        if($this->db->affected_rows()){
                            //getting device token data from users table
                            $userData = $this->common_model->is_data_exists(USERS,array('id'=>$order_data->user_id));
                            $refrenID['token'] = $userData->deviceToken;//device token for push notification

                           $notif_msg['type']          = 'Payment';
                            $notif_msg['body']          = 'your order  no '.$order_data->invoice_id.' Payment  $'.$payment.' deduct successfully';
                            $notif_msg['type']          = 'Payment';
                            $notif_msg['title']         = 'Payment Deduct successfully';
                            $notif_msg['sound']         = 'default';
                            $notif_msg['order_id']         = $order_data->orderId;

                            $dataNotifiy['title']          = 'Payment Deduct successfully';
                            $dataNotifiy['user_id']          = $order_data->user_id;
                            $dataNotifiy['order_id']          = $order_data->orderId;
                            $dataNotifiy['type']          = 'Payment';
                            $dataNotifiy['is_read']         = '0';
                            $dataNotifiy['current']         = datetime();
                            $dataNotifiy['message'] ='your order  no '.$order_data->invoice_id.' Payment  $'.$payment.' deduct successfully';

                             //for notifcation 
                            if(!empty($refrenID['token'])){
                                $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                            }
                        }

                    }
                }
            }                    
        }

        if($update ==TRUE){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function manualPayment($payment_id,$card_id,$custId,$source_type){
        $where = array('paymentId'=>$payment_id,'payFor'=>0);
        $data = $this->common_model->is_data_exists(BILL_PAYMENT,$where);
        //count no. of ids
        $delivery_id = explode(',', $data->delivery_id);
        $ids = sizeof($delivery_id);

        //selected data from payment table
        $payment = $data->grand_payment*$ids;//payment of order depends no. of delivery
        $order_id = $data->order_id;//order Id
        $auto_pay = $data->payFor;//auto pay
        $schedule_type = $data->schedule_type;//schedule type
        $payment_status = $data->payment_status;//payment status
        $user_id = $data->user_id;//user_id

//echo "string";die();
        //selected data from order table
        $whereOrderId = array('orderId'=>$order_id);
        $orderData = $this->common_model->is_data_exists(ORDER,$whereOrderId);
        $distance = $orderData->delivery_distance;//distance from source to destination
        //getting data from delivery table
        $whereOrder_id = array('order_id'=>$order_id);
        $deliveryData = $this->common_model->is_data_exists(DELIVERY,$whereOrder_id);
        //pr($deliveryData);
        $delivery_status = $deliveryData->delivery_status;//delivery status

        if($delivery_status == 1 || $delivery_status == 0){//check delivery is completed
            return "DNC";
        }

        if($auto_pay == 1){
            return "AP";
        }

        if($payment_status == 1){
            return "APC";
        }

        

        //getting transation Id
        $this->load->library('stripe');
        $charge['customer_id'] = $custId;
        $charge['source'] = $card_id;
        $charge['amount'] = $payment;
        $charge['source_type'] = $source_type;
        $charge['currency'] = 'usd';
        //total payment amount by client
        $stripe = $this->stripe->charge_customer($charge);
        //$stripe = $this->stripe->pay_by_card_id($total_payment,$custId,$currency='USD');
        
        if($stripe['status']==""){
            echo $stripe['message'];die();
            return "TIE";
        }else{
            $transaction_id = $stripe['data']['balance_transaction'];//transationId
            //pr($transaction_id);

            $whereForPayment = array('order_id'=>$order_id);

            $dataOrder = array('transaction_id'=>$transaction_id,'card_id'=>$card_id,'payment_status'=>1);

            $update = $this->common_model->updateFields(BILL_PAYMENT, $dataOrder, $whereForPayment);

            if($update == TRUE){
                //for set notification
                //getting device token data from users table
                $userData = $this->common_model->is_data_exists(USERS,array('id'=>$user_id));
                $refrenID['token'] = $userData->deviceToken;//device token for push notification

                //data for notification
                $notif_msg['type']          = 'Payment';
                $notif_msg['body']          = 'your order  no '.$orderData->invoice_id.' Payment  $'.$payment.' deduct successfully';
                $notif_msg['type']          = 'Payment';
                $notif_msg['title']         = 'Payment Deduct successfully';
                $notif_msg['sound']         = 'default';
                $notif_msg['order_id']         = $order_id;

                $dataNotifiy['title']          = 'Payment Deduct successfully';
                $dataNotifiy['user_id']          = $user_id;
                $dataNotifiy['order_id']          = $order_id;
                $dataNotifiy['type']          = 'Payment';
                $dataNotifiy['is_read']         = '0';
                $dataNotifiy['current']         = datetime();
                $dataNotifiy['message'] ='your order  no '.$orderData->invoice_id.' Payment  $'.$payment.' deduct successfully';

                 //for notifcation 
                if(!empty($refrenID['token'])){
                $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                }

                return 'TRUE';      
            }else{
                return FALSE;
            }
        }

    }


    function monthlyPayment(){
        $currentDate =  date("Y-m-d");//current date of the month
        $currentFirst = date('Y-m-01');//current month first date
//pr($currentFirst);
        //last dte of the month
        $currentLast =  date("Y-m-t", strtotime($currentDate));

        //month 16th date
        $month9th = date("Y-m-d", strtotime("+9 day", strtotime($currentFirst)));
        
        //pr($currentLast);
        //check condition for date 16th and after last date of the month 
        if($currentDate == $month9th){
            $delivery_data = array();
            $delivery_id = array();
            $this->db->select("*");
            $this->db->from(BILL_PAYMENT);
            $this->db->where('crd BETWEEN DATE_SUB(NOW(), INTERVAL 10 DAY) AND NOW()');
            $this->db->where('payment_status',0);
            $this->db->where('schedule_type',4);
            $sql = $this->db->get();
        
            if($sql->num_rows()){
                $res = $sql->result();//payment data
                //pr($res);
                foreach ($res as $k => $data) {
                    //count no. of ids
                    $ids = explode(',', $data->delivery_id);
                    //check for completing alternate week
                    $where = array('orderId'=>$data->order_id,'schedule'=>'4','auto_pay'=>'1');//where job complete
                    $this->db->where($where);
                    $order = $this->db->get(ORDER);//get order data
                   // pr($order->row());
                    if($order->num_rows()){
                        $order_data = $order->row();
                        $card_id = $order_data->card_id;//card_id

                        //total payment depend on no. of delivery
                        $payment = $order_data->grand_total*sizeof($ids); 
                        $distance = $order_data->delivery_distance;//distance

                        //custId from users table
                        $whereUser = array('id'=>$order_data->user_id);
                        $custId = $this->common_model->is_data_exists(USERS, $whereUser)->customer_stripe_id;

                        //getting transation Id
                        $this->load->library('stripe');
                        $stripe = $this->stripe->pay_by_card_id($payment,$custId,$currency='USD');
                        //pr($stripe);
                        if($stripe['status']==""){
                            return FALSE;
                        }else{
                        $transaction_id = $stripe['data']['balance_transaction'];//transationId
                        //pr($transaction_id);

                        $where = array('order_id'=>$order_data->orderId,'user_id'=>$order_data->user_id);

                        $data = array('transaction_id'=>$transaction_id,'card_id'=>$card_id,'payment_status'=>1);

                        $update = $this->common_model->updateFields(BILL_PAYMENT, $data, $where);

                        }
                        if($this->db->affected_rows()){
                            //getting device token data from users table
                            $userData = $this->common_model->is_data_exists(USERS,array('id'=>$order_data->user_id));
                            $refrenID['token'] = $userData->deviceToken;//device token for push notification

                            $notif_msg['type']          = 'Payment';
                            $notif_msg['body']          = 'your order  no '.$order_data->invoice_id.' Payment  $'.$payment.' deduct successfully';
                            $notif_msg['type']          = 'Payment';
                            $notif_msg['title']         = 'Payment Deduct successfully';
                            $notif_msg['sound']         = 'default';
                            $notif_msg['order_id']         = $order_data->orderId;

                            $dataNotifiy['title']          = 'Payment Deduct successfully';
                            $dataNotifiy['user_id']          = $order_data->user_id;
                            $dataNotifiy['order_id']          = $order_data->orderId;
                            $dataNotifiy['type']          = 'Payment';
                            $dataNotifiy['is_read']         = '0';
                            $dataNotifiy['current']         = datetime();
                            $dataNotifiy['message'] ='your order  no '.$order_data->invoice_id.' Payment  $'.$payment.' deduct successfully';

                             //for notifcation 
                            if(!empty($refrenID['token'])){
                            $this->common_model->notification($dataNotifiy,$refrenID,$notif_msg);
                            }
                        }

                        if($update == TRUE){
                            return TRUE;
                        }else{
                            return FALSE;
                        }
                    }
                }
            }                    
        }
        
    }

} //ENd CLass
?>