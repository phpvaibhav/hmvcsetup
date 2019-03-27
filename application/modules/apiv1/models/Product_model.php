<?php
class Product_model extends CI_Model
{

    public function get_datas($address_id){
        if($address_id){
             $this->db->select('a.addressId,a.user_id,a.d_address,a.d_city,a.d_state,a.d_zip_code,a.d_contact_name,a.d_contact_no,a.d_email,a.d_lattitude,a.d_longitude,a.b_address,a.b_city,a.b_state,a.b_zip_code,a.b_lattitude,a.b_longitude,a.b_contact_no,a.b_email,a.b_contact_name,a.crd,a.upd,u.fullName as user_name');
            $this->db->from('address as a');
            $this->db->join('users as u','u.id=a.user_id','left');
            $this->db->where('addressId',$address_id);
        $data = $this->db->get()->row();
        // pr($data);
        return $data;
        }

    }

    public function getBottle(){
        $this->db->select('*,CONCAT(b.unit_type, " Gallon ", b.bottle_type ) AS bottles_type');
        $this->db->from(BOTTLE ." as b");
        $res = $this->db->get();
        if($res->num_rows()){
            $result = $res->result();
            return $result;
        }
    }

    public function getRecycleBottle(){
        $this->db->select('*,CONCAT(b.unit_type, " Gallon ", b.bottle_type ) AS recycle_type');
        $this->db->from(RECYCLE_BOTTLE ." as b");
        $res = $this->db->get();
        if($res->num_rows()){
            $result = $res->result();
            return $result;
        }
    }

     public function get_addresses($user_id){
        if($user_id){
            $this->db->where('user_id',$user_id);
        $data = $this->db->get(ADDRESS)->row();
        return $data;
        }

    }    

    public function is_data_exist($table,$type){
       $url =  base_url().'uploads/document/';//path of the document
        $this->db->select('title,description,concat("'.$url.'",pdf) as pdfurl');
        $query = $this->db->get($table);
        
        if($query->num_rows() > 0){
            $row = $query->result();
            return $row;

        }else{
            return false;
        }

    }
    
    public function get_delivery_addresses($user_id){
        if($user_id){
             $this->db->select('addressId,user_id,d_address,d_city,d_state,d_zip_code,d_contact_name,d_contact_no,d_email,d_lattitude,d_longitude,crd,upd');
            $this->db->where('user_id',$user_id);
        $data = $this->db->get(ADDRESS);
            if($data->num_rows()){
                $row = $data->row();
                return $row;
            }else{
                return false;
            }
        }

    } 
    

     public function get_billing_addresses($user_id){
        if($user_id){
            $this->db->select('addressId,user_id,b_address,b_city,b_state,b_zip_code,b_contact_name,b_contact_no,b_email,b_lattitude,b_longitude,crd,upd');
            $this->db->where('user_id',$user_id);
        $data = $this->db->get(ADDRESS);
            if($data->num_rows()){
                $row = $data->row();
                return $row;
            }else{
                return false;
            }
        }

    } //END OF FUNCTION

    public function get_order($order_status){
        if($order_status){
            $this->db->select('*');
            $this->db->from('order as o');
            $this->db->join('order_product as op','o.orderId=op.order_id','left');
            $this->db->where('order_status',$order_status);
           $data = $this->db->get();
            if($data->num_rows()){
                $row = $data->result();
                return $row;
            }else{
                return false;
            }

        }

    }//END OF FUNCTION

    public function get_ordersList($where){
        $array=array();
        $this->db->select("*,CASE
            WHEN order_status = 1 THEN 'Pending'
            WHEN order_status = 2 THEN 'Progress'
            WHEN order_status = 3 THEN 'Complete'
            ELSE 'Canceled'
            END as orderStatus,
            CASE
            WHEN payment_status = 1 THEN 'Pending'
            WHEN payment_status = 2 THEN 'Complete'
            ELSE 'Fail'
            END as paymentStatus")->from(ORDER);
        $this->db->where($where);
        $this->db->order_by('orderId','desc');
        $sql = $this->db->get();
        if($sql->num_rows()):
            $array = $sql->result();
            foreach ($array as $k => $val) {
                    $crd = $array[$k]->crd;
                $newDateTime = date('h:i A', strtotime($crd));
                //pr($crd);
                $array[$k]->time = $newDateTime;
                $array[$k]->products = $this->get_products_by_order($val->orderId);
                $array[$k]->recycle = $this->get_recycle_by_order($val->orderId);
                //$product_data = $this->get_products_by_order($val->orderId);
            }


        endif;
        return $array;    
    } //ENd Funtion
        function get_bottles_by_order($productId){
        $array  =array();
        $this->db->select('b.bottleId,b.bottle_name,b.unit_type,b.bottle_type');
        $this->db->from(PRODUCT.' as p');
        $this->db->join(BOTTLE.' as b','p.bottle_id=b.bottleId','left');
        $this->db->where('productId',$productId);
        $sql = $this->db->get();
       // echo $this->db->last_query();die;
        if($sql->num_rows()){
        $array  =$sql->result();
        }else{
            return array();
        }
        return $array;
    }//ENd

    function get_products_by_order($order_id){
        $array  =array();
        $this->db->select('*');
        $this->db->from('order_product as op');
        $this->db->join('water as w','w.waterId=op.water_id','left');
        $this->db->join('bottle as b','b.bottleId=op.bottle_id','left');
        $this->db->where('order_id',$order_id);
        $sql = $this->db->get();
       // echo $this->db->last_query();die;
        if($sql->num_rows()){
        $array  =$sql->result();
        }else{
            return array();
        }
        return $array;
    }//ENd   

    function get_recycle_by_order($order_id){
        $array  =array();
        $this->db->select('*');
        $this->db->from('order_recycle as or');
        $this->db->join('recycle_bottle as rb','rb.recycleBottleId=or.recycle_id','left');
        $this->db->where('order_id',$order_id);
        $sql = $this->db->get();
       // echo $this->db->last_query();die;
        if($sql->num_rows()){
        $array  =$sql->result();
        }else{
            return array();
        }
        return $array;
    }//ENd 
    function get_delivery_by_order($order_id){
        $array  =array();
        $this->db->where('order_id',$order_id);
        $this->db->order_by('deliveryId','desc');
        $sql = $this->db->get(DELIVERY);
       // echo $this->db->last_query();die;
        if($sql->num_rows()){
        $array  =$sql->result();
        }
        return $array;
    }//ENd  
 public function get_orders(){
        $array=array();
        $this->db->select("order_status")->from(ORDER);
        $sql = $this->db->get();
        if($sql->num_rows()):
            $array = $sql->result();
            foreach ($array as $k => $val) {

                $array[$k]->orders = $this->get_order($val->order_status);
               
            }


        endif;
        return $array;    
    } //ENd Funtion

    public function order_detail($order_id){
        if($order_id){
            $this->db->select('*,CASE
            WHEN schedule = 1 THEN "Upon request(whenever I run out)"
            WHEN schedule = 2 THEN "Weekly"
            WHEN schedule = 3 THEN "Every other week"
            ELSE "Monthly"
            END as schedule');
            $this->db->from('order as o');
            $this->db->join('order_detail as ad','o.orderId=ad.order_id','left');
            $this->db->where('orderId',$order_id);
            $data = $this->db->get()->row();
            $crd = $this->db->get(ORDER)->row()->crd;
            $newDateTime = date('h:i A', strtotime($crd));
            $data->time = $newDateTime;
            //pr($crd);
            if(!empty($data)){
                $data->bill_generate = $this->bill_generate($order_id);
                $data->receipt = $this->get_receipt($order_id);
                $data->products = $this->get_products_by_order($order_id);
                return $data;
            }else{
                return FALSE;
            }
            

        }

    }//END OF FUNCTION

    public function bill_generate($order_id){
        $this->db->select('*');
        $this->db->from(BILL_PAYMENT);
        $this->db->where('order_id',$order_id);
        $data = $this->db->get();
        if($data->num_rows()){
            return $data->row();
        }else{
            return $data =new stdClass();
        }


    }//END OF FUNCTION

    public function get_receipt($order_id){
        $where = array('orderId'=>$order_id);
        $start_date = $this->common_model->is_data_exists(ORDER,$where)->start_date;//order start date

        //prevous and next saturday
        $saturdayPrev = Date('Y-m-d', strtotime("last Saturday"));
        $afetrStartDateSaturday = Date('Y-m-d', StrToTime($start_date ."next saturday"));
        $nextSaturday = Date('Y-m-d', StrToTime("next saturday"));
        $currentDate =  Date("Y-m-d");

        if($afetrStartDateSaturday <= $nextSaturday){

        //date conversion formate for upcoming delivery
            $saturdayN = date("jS M, Y", strtotime($nextSaturday));
            $date['upcoming'] = $saturdayN; 

        }else{
        //date conversion formate for upcoming delivery
            $saturdayN = date("jS M, Y", strtotime($afetrStartDateSaturday));
            $date['upcoming'] = $saturdayN;
            
        }

        $this->db->select('delivery_date');
        $this->db->from(DELIVERY);
        $this->db->where('order_id',$order_id);
        $this->db->or_where('delivery_date',$saturdayPrev);
        $data = $this->db->get();// execution of query


        if($data->num_rows()){
            $res = $data->result();
            foreach ($res as $k => $result){
                if($result->delivery_date <= $currentDate){
                    //date conversion formate for previous delivery delivery
                    $saturdayP = date("jS M, Y", strtotime($result->delivery_date));
                    $date['delivered'] = $saturdayP; 
                }else{
                    $date['delivered'] = "";
                }
            }
        }else{
            $date['delivered'] = ""; 
        }
            return $date;
    }//END OF FUNCTION

    public function get_notification_list($where){
        $this->db->where($where);
        $this->db->order_by('notificationId','desc');
        $data = $this->db->get(NOTIFICATION);
        if($data->num_rows()){
         $res = $data->result();
         foreach ($res as $k => $result) {
             $res[$k]->current_time =  date("Y-m-d h:i:s");
         }
         return $res;
        }
    }//END OF FUNCTION
}
?>