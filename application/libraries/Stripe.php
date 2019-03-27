<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
//echo APPPATH.'vendor/stripe/stripe-php/init.php';die();
require_once(APPPATH.'vendor/stripe/stripe-php/init.php');  //ver 6.8.1
class Stripe{

    public function __construct () {
        $this->ci =& get_instance();
        $secret_key = STRIPE_SECRET_KEY;
        $publishable_key = STRIPE_PUBLISH_KEY;
        Stripe\Stripe::setApiKey($secret_key);		
    }

    // to create custom account
    function create_custom_account($holderName,$dob,$country,$currency,$routingNumber,$accountNo,$ssnLast,$postalCode){

        if(!empty($holderName)){
            $names = explode(" ", $holderName);
        }

        $dob = explode("-", $dob);
        try{
            $acct = \Stripe\Account::create(array(

                "country" => $country,
                "type" => "custom",

                "external_account" => array(
                            "object" => "bank_account",
                            "country" => $country,
                            "currency" => $currency,
                            "routing_number" => $routingNumber,
                            "account_number" => $accountNo
                    ),

                    "tos_acceptance" => array(
                            "date" => time(),
                            "ip" => $_SERVER['SERVER_ADDR']
                    ),

                    "legal_entity" => array(

                            'dob' => array(
                                    'year'=>$dob[0],
                                    'month'=>$dob[1],
                                    'day'=>$dob[2]
                            ),

                            'first_name'=> $names[0],
                            'last_name'=> $names[1],
                            'type'=> "individual",

                            'address'=> array(
                                    'postal_code'=>$postalCode
                            ),
					'ssn_last_4'=>$ssnLast
                        )
                ));
			
            if(isset($acct->id) && !empty($acct->id)){ // getting account id

                    return array('status'=>true,'message'=>'ok','data'=>$acct);
            }

        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message,'data'=>'');
        } 
    }
    
    function addCardAccount($name,$number,$exp_month,$exp_year,$cvv){

        $success = 0;
        try {
            $result = Stripe\Token::create(
                array(
                    "card" => array(
                        "number" => $number,
                        "exp_month" => $exp_month,
                        "exp_year" => $exp_year,
                        "cvc" => $cvv
                    ) 
                )
            );
            $success = 1;
		    
        } catch(Stripe_CardError $e) {
            $error[] = $e->getMessage();
        } catch (Stripe_InvalidRequestError $e) {
            // Invalid parameters were supplied to Stripe's API
            $error[] = $e->getMessage();
        } catch (Stripe_AuthenticationError $e) {
            // Authentication with Stripe's API failed
            $error[] = $e->getMessage();
        } catch (Stripe_ApiConnectionError $e) {
            // Network communication with Stripe failed
            $error[] = $e->getMessage();
        } catch (Stripe_Error $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $error[] = $e->getMessage();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $error[] = $e->getMessage();
        }

        if ($success != 1){
            $response = array('status'=> FAIL ,'message' => $error);
            print_r(json_encode($response));die();
        }else{
            if(isset($result['id']) && !empty($result['id'])){
                    return $result['id'];
            }else{
                    return false;
            }     
        }		 
    }
	
    //create customer
    function create_customer($email='', $token=''){

        $create_data = array("email" => $email);
        if(!empty($token)){
                $create_data['source'] = $token;
        }
        
        try{
            $customer = Stripe\Customer::create($create_data);

            if(!isset($customer->id)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            return array('status'=>true,'message'=>'Customer created successfully', 'data'=>$customer); //success

        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message,'data'=>'');
        } 
    }

	//updatg customer token
    function update_customer($customer_id,$attrs=array()){

        $cu = \Stripe\Customer::retrieve($customer_id);
        
        try{

            foreach($attrs as $k => $v){
              	$cu->$k = $v;
            }
        
            $upd_status =  $cu->save();
            
            if(empty($upd_status)){
             	return false;
            }
            
            return true;
           
        }catch(Exception $e){
            //echo 'dsfsdf';die;
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }  
    }
    
    //Charge a customer on basis of customer ID
    function pay_by_card_id($payment,$custId, $currency='USD'){
		
        $paymentt = round(($payment*100),2);	
        try{
            $charge = Stripe\Charge::create(array(
                    "amount" => $paymentt, //convert into cent
                    "currency" => $currency,
                    "customer" => $custId,
                ));
            $var = $charge->balance_transaction;
	
            if(!isset($charge->balance_transaction)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Transaction completed successfully', 'data'=>$charge); //success
        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    function refundToCard($chargeId){

        $secret_key = $this->ci->config->item('secret_key');
        Stripe\Stripe::setApiKey($secret_key);
        try{

            $refund = \Stripe\Refund::create(array(
                "charge" =>$chargeId 
            ));
            if(isset($refund ->id) && !empty($refund ->id)){
                return array('status'=>true,'message'=>'ok','data'=>$refund ->id);
            }
        }catch(Exception $e){

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message,'data'=>'');
        }		
    }

    // to pay stripe to bank account
    function owner_pay_byBankId($data){

        $amount = round(($data['amount']*100),2);
        $success = 0;
        
        try{ // Transfer::create
            $transfer = \Stripe\Charge::create(array( 
                "amount" => $amount, 
                "currency" => $data['currency'],
                //"source_type"=>"bank_account",
                //"destination"=>"ba_1CkoO0HIJxV06OmpU3vJ0rCL"
                //"transfer_group" => "APOIM"
                "customer" => $data['customerId'],
                "destination" => $data['bankAccId']
            ));

            $success = 1;
		    
        } catch(Stripe_CardError $e) {
            $error[] = $e->getMessage();
        } catch (Stripe_InvalidRequestError $e) {
            // Invalid parameters were supplied to Stripe's API
            $error[] = $e->getMessage();
        } catch (Stripe_AuthenticationError $e) {
            // Authentication with Stripe's API failed
            $error[] = $e->getMessage();
        } catch (Stripe_ApiConnectionError $e) {
            // Network communication with Stripe failed
            $error[] = $e->getMessage();
        } catch (Stripe_Error $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $error[] = $e->getMessage();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $error[] = $e->getMessage();
        }

        if ($success != 1){
            return array('status'=> false ,'message' => $error);
        }else{
            if(isset($transfer->balance_transaction) && !empty($transfer->balance_transaction)){				
                return array('status'=>true,'message'=>'Transaction completed successfully', 'data'=>$transfer); //
            }else{
                return array('status'=>false,'message'=>'Something went wrong');
            }   
        }		 
    }
    
    //Retrieves the plan with the given ID.
    function get_plan($plan_id){

        try{
            $plan = \Stripe\Plan::retrieve($plan_id);
            if(empty($plan)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Plan retrieved successfully', 'data'=>$plan); //success
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }

    //Subscribe the customer to the plan
    function create_subscription($customer_id, $plan_id){
        try{
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer_id,
                'items' => [['plan' => $plan_id]],
            ]);
            
            if(empty($subscription)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Subscribed successfully', 'data'=>$subscription); //success
            
        }catch (Exception $e) {
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }

    //Retrieves the subscription detail with the given subscription ID.
    function get_subscription($subs_id){
    	
        try{

            $subscription = \Stripe\Subscription::retrieve($subs_id);

            if(empty($subscription)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Subscription retrieved successfully', 'data'=>$subscription); //success
            
        } catch (Exception $e) {
        	
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    } 

    //Cancels a customer’s subscription
    //By default, the cancellation takes effect immediately for $at_period_end = false
    //If you want to cancel the subscription at the end of the current billing period 
    //(i.e., for the duration of time the customer has already paid for), provide an at_period_end value of true
    function cancel_subscription($subscription_id, $at_period_end = false){

        try{

            $sub = \Stripe\Subscription::retrieve($subscription_id);

            $subscription = $sub->cancel(['at_period_end' => $at_period_end]);

            if(empty($subscription)){

                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Canceled successfully', 'data'=>$subscription); //success
            
        }catch (Exception $e) {

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    /*
     * Charge a customer on basis of token/source/card
     */
    function charge_customer($charge_data){
       //pr($charge_data);
        $amount = round(($charge_data['amount']*100),2); //convert to cents
        
        $charge_arr = array(
                    "amount" => $amount,
                    "currency" => $charge_data['currency'],
                    "source" => $charge_data['source'], //source or token or card ID
                );
        if($charge_data['source_type'] === 'card'){
            $charge_arr['customer'] = $charge_data['customer_id'];
        }
      //  pr($charge_arr);
        try{
           
            $charge = \Stripe\Charge::create($charge_arr);
            
            if(empty($charge)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
             
            return array('status'=>true, 'message'=>'Charged successfully', 'data'=>$charge); //success
            
        }catch (Exception $e) {

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    
    /*
     * Transfer fund from main stripe account to custom account (connected account)
     * This is done when admin wants to keep a commission(some % of amount) from a payemnt and pay remaning amount to customer
     * Note: Transfer from connected account to its attached bank account will be done automatically (via Stripe Payout)
     */
    function stripe_to_custom_account_transfer($acc_id, $amount, $currency='usd'){
        
        $amount = round(($amount*100),2); //convert to cents
        
        try{
            $transfer = \Stripe\Transfer::create( array(
                "amount" => $amount,
                "currency" => $currency,
                "destination" => $acc_id,

            ));
            if(empty($transfer)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true, 'message'=>'Funds transfered successfully', 'data'=>$transfer); //success
            
        }catch (Exception $e) {

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    
    /* 
     * Dev test method start here
     * =======================================================================
     * Methods below are created by Manish for testing purpose
     * =======================================================================
     */
    function create_custom_ac(){
        
        $acct = \Stripe\Account::create(array(
            "country" => "US",
            "type" => "custom"
        ));
        return $acct;
    }
    
    function create_bank_ac($acc_id){

        $account = \Stripe\Account::retrieve($acc_id);
        $account->external_accounts->create(array(
            "external_account" => array(
                "object" => "bank_account",
                "country" => "US",
                "currency" => "USD",
                "routing_number" => $routingNumber,
                "account_number" => $accountNo
            ),
        ));
    }    
    
    ////////// stripe to custom account(connected a/c) transfer
    function pay_by_bank_id($acctId,$payment){ 
       
        $transfer = \Stripe\Transfer::create(array( 
            "amount" => $payment, 
            "currency" => "USD", 
            "destination" => $acctId, 

        ));
        return $transfer;        
    }

    function get_bal($con_acc_id){

       	$bal =  \Stripe\Balance::retrieve(
		  	array("stripe_account" => $con_acc_id)
		);
       return $bal;
    }

    function get_ac($acct_id){

       	$ac =  \Stripe\Account::retrieve($acct_id);
       	return $ac;
    }

    function update_ac(){

        $account = \Stripe\Account::retrieve("acct_1CkoO0HIJxV06Omp");
        $account->payout_schedule = array('');
        $account->save();
    }

    function create_cus_charge(){

        \Stripe\Charge::create(array(
            "amount" => 1000,
            "currency" => "usd",
            "customer" => 'cus_DCO4wKxXl6rw1F',
            "destination" => 'acct_1CkoO0HIJxV06Omp'
        )); 
    }
    
    //Stripe to bank account transfer
    function payout_to_bank($acc_id){
        
        
        $transfer = \Stripe\Transfer::create( array(
            "amount" => 100,
            "currency" => "usd",
            "destination" => $acc_id,
            
        ));
        return $transfer;
        /*$transfer = \Stripe\Charge::create(array( 
                "amount" => 100, 
                "currency" => "usd",
                //"source_type"=>"bank_account",
                //"destination"=>"ba_1CkoO0HIJxV06OmpU3vJ0rCL"
                //"transfer_group" => "APOIM"
                //"customer" => $data['customerId'],
                "destination" => $acc_id
        ));
        return $transfer;*/
        
        /*$payout = \Stripe\Payout::create(
            array(
                "amount" => 100,
                "currency" => "usd",
            ),
            array( "stripe_account" => $acc_id )
        );
        
        return $payout;*/
    }
    
    /*
     * Dev test method ends here
     */
}


