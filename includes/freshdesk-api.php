<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vx_freshdesk_api' ) ) {

/**
* Main class
*
* @since       1.0.0
*/
class vx_freshdesk_api{
  
 private $url='';
//  private static $email= false;
  private $token='';
  private $mailbox='';
  private $agents;
     
public function __construct($meta){ 
  
    if(!empty($meta['freshdesk_token'])){
        $this->token=$meta['freshdesk_token'];
    }  
    if(!empty($meta['freshdesk_url'])){
    
        $this->url=trailingslashit($meta['freshdesk_url']);
    }
    if(!empty($meta['freshdesk_mailbox'])){
        $this->mailbox=$meta['freshdesk_mailbox'];
    }
    }
public function get_customer($email){
 $path='api/v2/contacts?email='.urlencode($email);
$search_res=$this->post_crm($path,'get');
//var_dump($path,$search_res);
$hs_id='';
if(!empty($search_res[0]['id'])){
  $hs_id=$search_res[0]['id']; 
}else{ //find in agents
 $path='api/v2/agents?email='.urlencode($email);
$search_res=$this->post_crm($path,'get');  
 
if(!empty($search_res[0]['id'])){
  $hs_id=$search_res[0]['id']; 
}
}  

return $hs_id;

}

public function get_tickets($user_id,$page=''){

             //api does not resturn total pages or items , only returns next page link in header
    $arr=array();  $leads=array();  
 if(!empty($user_id) ){
     //get conversations
 $path='api/v2/tickets?requester_id='.$user_id;
 if(!empty($page)){
  $path.='&page='.$page;   
 }
 $status_arr=$this->get_status_list();
$arr=$this->post_crm($path,'GET');

if(is_array($arr)){
    foreach($arr as $v){
        if(is_array($v)){
 $v['number']=$v['id'];
 $v['createdAt']=$v['created_at'];
 $v['modifiedAt']=$v['updated_at'];
 if(isset($status_arr[$v['status']])){
 
 $v['status']=array('name'=>$status_arr[$v['status']],'color'=>$this->label_color($v['status']));
 }
  $leads[]=$v;      
    } }
}

 }
return array('items'=>$leads,'count'=>100,'pages'=>1,'page'=>1);

}
public function label_color($status){ 
  $color='closed';
  if($status == 2){ //open
      $color='active';
  }else if($status == 3){ //pending
    $color='pending';  
  }else if($status == 4){ //resloved
   $color='info';   
  }  
  return $color;
}
public function get_ticket($id,$page=''){

$path='api/v2/tickets/'.$id; //
$ticket=$this->post_crm($path,'GET');

$notes=array(); $res=array();

if(!empty($ticket['id'])){
 $temp=array('body'=>$ticket['description'],'body_text'=>$ticket['description_text'],'subject'=>$ticket['subject'],'id'=>$ticket['id'],'created_at'=>$ticket['created_at'],'private'=>false,'incoming'=>false);
 if($ticket['source'] != '3'){
     $temp['incoming']=true;
 }
 $notes[]=array_merge($temp,$ticket); 
 $path='api/v2/tickets/'.$id.'/conversations?per_page=100';
 $arr=$this->post_crm($path,'GET');  
if(!empty($arr)){
$notes=array_merge($notes,$arr);
if(count($arr) == 100){
$path='api/v2/tickets/'.$id.'/conversations?per_page=100&page=2';
$arr=$this->post_crm($path,'GET');
$notes=array_merge($notes,$arr);        
} }
 
// array_unshift($notes,$ticket);
//$notes[]=$temp; 
$notes=array_reverse($notes);

if(!empty($notes)){
 foreach($notes as $v){     
if($v['private'] === true){continue; }
if(isset($v['created_at'])){
 $v['createdAt']=$v['created_at'];   
}
$by=array();
if($v['incoming'] === false  && isset($v['user_id'])){ //reply from agent
$by=$this->get_user($v['user_id']); 
if(!empty($by['name'])){
$by['type']='user';    
}else{
 $by['type']='customer';    
}  

 }else{
$by['type']='customer';    
 }  
//$v['body']=nl2br($v['body']); 
$v['body']=!empty($v['body']) ? $v['body'] : $v['body_text']; 
$v['createdBy']=$by; 

   if(isset($v['attachments']) && is_array($v['attachments'])){
  $atts=array();
    foreach($v['attachments'] as $att){
  $atts[]=array('fileName'=>$att['name'],'url'=>$att['attachment_url']);      
    }   
$v['attachments']=$atts;   
   }  
$threads_arr[]=$v;
 }   

$res=array('threads'=>$threads_arr);
if(!empty($ticket['requester_id'])){
$res['user_id']=$ticket['requester_id'];    
}
$res['threadCount']=count($threads_arr);
$labels=$this->get_status_list();
if(isset($ticket['status']) && isset($labels[$ticket['status']])){
$res['status']=$labels[$ticket['status']];    
$res['status']=array('name'=>$labels[$ticket['status']],'color'=>$this->label_color($ticket['status']));    
}
if(isset($ticket['created_at'])){
 $res['createdAt']=$ticket['created_at'];   
}

if(isset($ticket['subject'])){
 $res['subject']=$ticket['subject'];   
}
$res['number']=$ticket['id']; 
  
}
}
    
return array('item'=>$res);


}

public function create_ticket($meta,$email,$req){


    $arr=array();
        if(!empty($req['body'])){   
    $body=$req['body'];
    
$post=array();

if(!empty($email)){
 $post['email']=$email;   
}

  if(!empty($req['name'])){
$post['name']=$req['name'];  
  }
   
   if(isset($req['phone'])){
   $post['phone']=$req['phone'];
   }

  if(!empty($req['subject'])){
$post['subject']=$req['subject'];  
  }   

if(!empty($req['tag'])){
 $post['tags[]']=$req['tag'];   
}
if(!empty($meta['freshdesk_priority'])){
    $post['priority']=$meta['freshdesk_priority'];
}
if(!empty($meta['freshdesk_status'])){
    $post['status']=$meta['freshdesk_status'];
}
if(!empty($req['vx_fields'])){
$custom=get_option(vx_support_x::$id.'_custom_fields',array());
$crm= isset($meta['crm']) ? $meta['crm'] : array();
$fields=!empty($custom[$crm]) ? $custom[$crm] : array();  

 foreach($req['vx_fields'] as $k=>$v){
     if(!empty($v)){
     $post['custom_fields['.$k.']']=isset($fields[$k]['type']) && $fields[$k]['type'] == 'checkbox' ? 0 : $v;
     }
 }
}

$path='api/v2/tickets'; $method='post';
    if(isset($_GET['conversation_id'])){
$path='api/v2/tickets/'.vx_support_x::post('conversation_id').'/reply';       
  $post=array('body'=>$body); //,'from_email'=>$email
  if(!empty($req['hs_id'])){
   $post['user_id']=$req['hs_id'];   
  }
  
    }else{
  $post['description']=nl2br($body);      
    } 
 //process files attachment
if(!empty($_FILES['file']['tmp_name'])){
$tmp_file=$_FILES['file']['tmp_name'];  
if(!empty($tmp_file)){
$filename = sanitize_file_name( $_FILES['file']['name'] );
$mime_type=vx_support_x::get_mime_type($tmp_file,$filename);
if(function_exists('curl_file_create')){       
$post['attachments[]']=curl_file_create($tmp_file,$mime_type,$filename);
//$post['attachment']=array('path'=>$tmp_file,'name'=>$filename,'type'=>$mime_type);
}
} }
//var_dump($post); die();
$res=$this->post_crm($path,$method,$post);
//var_dump($res,$post); die('-----------');
if(isset($res['id'])){
$arr['id']=$res['id'];
if(!empty($res['requester_id']) && !empty($req['phone'])){
    $path='api/v2/contacts/'.$res['requester_id'];
  $this->post_crm($path,'put',array('phone'=>$req['phone']));  
} 
    if(isset($res['item']['threads'][0]['id'])){
    $arr['thread_id']=$res['item']['threads'][0]['id'];   
    }   
}else{    
$err='';
if(isset($res['message'])){
    $err=$res['message'];
}
if(isset($res['errors'][0]['message'])){
if(isset($res['errors'][0]['field'])){
$err=$res['errors'][0]['field'].':';    
}
$err.=$res['errors'][0]['message'];
}
$arr['error']=$err;   
}
        }
  return $arr;      
}
public function get_user($id){
   if(empty($this->agents)){
    $this->agents=get_option(vx_support_x::$id.'_freshdesk_agents',array());
   }

    if(!isset($this->agents[$id])){
    $path='api/v2/agents'; //
$agents_arr=$this->post_crm($path,'GET');
$agents=array();
if(is_array($agents_arr) && count($agents_arr)>0){
    foreach($agents_arr as $k=>$v){
 $agents[$v['id']]=$v['contact'];       
    }
}

$this->agents=$agents;

update_option(vx_support_x::$id.'_freshdesk_agents',$agents);    
    } 
   
    $user=array();
     if(isset($this->agents[$id])){
   $user=$this->agents[$id];      
     }
 return $user;    
}
public function get_mailboxes(){
$arr=$this->post_crm('mailboxes.json');
$boxes=array();
if(isset($arr['items']) && is_array($arr['items'])){
    foreach($arr['items'] as $v){
  $boxes[$v['id']]=$v['name'];      
    }
}

return $boxes;
}

public function get_status_list(){
       $status_arr=array('2'=>'Open','3'=>'Pending','4'=>'Resolved','5'=>'Closed');
    
    return $status_arr;
}
public function get_priority_list(){
 $status_arr=array('1'=>'Low','2'=>'Medium','3'=>'High','4'=>'Urgent');
    return $status_arr;
}
public function get_ticket_fields(){
    $path='api/v2/ticket_fields';
    $arr=$this->post_crm($path); 
$field_types=array('custom_dropdown'=>'select');
    $fields=array();
    if(!empty($arr) && is_array($arr)){
        foreach($arr as $v){
            
         if(isset($v['customers_can_edit']) && $v['customers_can_edit'] === true && $v['default'] === false){
                
                $type=isset($field_types[$v['type']]) ? $field_types[$v['type']] : $v['type'];
                if(in_array($type,array('custom_checkbox','custom_decimal'))){
                    continue;
                }
     $field=array('name'=>$v['name'],'label'=>$v['label'],'type'=>$type);           
     if(!empty($v['choices'])){
    $options=array();
     foreach($v['choices'] as $option){
    $options[$option]=$option;     
     }    
     $field['options']=$options;
     }
     $fields[$v['name']]=$field;
            }
        }
    }
 // var_dump($fields,$arr);
return $fields;
}

public function curl_file_create($filename, $mimetype = '', $postname = '') {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
public function post_crm($path,$method='get',$body=''){ 
   
   $head=array('Authorization: Basic '.base64_encode($this->token.':X'),'Content-Type: multipart/form-data');    
$method=strtolower($method);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $this->url.$path );
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
curl_setopt($ch, CURLOPT_TIMEOUT, 100);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
if($method=="put")
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
else if($method=="post")
curl_setopt($ch, CURLOPT_POST, true );
else if($method=="get")
curl_setopt($ch, CURLOPT_HTTPGET, true );  
else if($method='delete')   
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
if($body !=""){
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
 $json=curl_exec($ch); 
return json_decode($json,true);     
}
}

}
