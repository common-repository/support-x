<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vx_teamwork_api' ) ) {

/**
* Main class
*
* @since       1.0.0
*/
class vx_teamwork_api{
  
 private $url='';
  private $token='';
  private $email='';
  private $mailbox='';
  private $agents;
     
public function __construct($meta){ 
  
    if(!empty($meta['teamwork_token'])){
        $this->token=$meta['teamwork_token'];
    }  
      
          if(!empty($meta['teamwork_mailbox'])){
        $this->mailbox=$meta['teamwork_mailbox'];
    } 
    if(!empty($meta['teamwork_url'])){
    
        $this->url=trailingslashit($meta['teamwork_url']).'desk/v1/';
    }
    
    }
public function get_customer($email){

 $path='customers/search.json?search='.urlencode($email);
$search_res=$this->post_crm($path,'GET');

$hs_id='';
if(!empty($search_res['customers'][0]['id'])){
  $hs_id=$search_res['customers'][0]['id']; 
} 

return $hs_id;

}

public function get_tickets($user_id,$page=''){
  
    $arr=array();  $leads=array();  $per_page=30;
 if(!empty($user_id) ){
     //get conversations
 $path='customers/'.$user_id.'/previoustickets.json?sort_direction=desc&per_page='.$per_page;
 if(!empty($page)){
  $path.='&page='.$page;   
 }

$arr=$this->post_crm($path,'GET');

if(!empty($arr['tickets']) && is_array($arr['tickets'])){
    foreach($arr['tickets'] as $v){
 $v['number']=$v['id'];

if(empty($v['subject'])){
    $v['subject']='[no subject]';
}
 
 $v['status']=array('name'=>$v['status'],'color'=>$this->label_color($v['status']));
 
  $leads[]=$v;      
    }
}
 }
 $pages=1; $count=100;
 if(!empty($arr['count'])){
  $pages=ceil($arr['count']/$per_page);
  $count=$arr['count'];   
 }
return array('items'=>$leads,'count'=>$count,'pages'=>$pages,'per_page'=>$per_page);

}
public function label_color($status){ 
  $color='closed';
  if($status == 'active'){ //open
      $color='active';
  }elseif($status == 'waiting'){ //open
      $color='pending';
  }else if($status == 'on-hold'){ //pending
    $color='pending';  
  }else if($status == 'solved'){ //resloved
   $color='info';   
  }  
  return $color;
}
public function get_ticket($id,$page=''){

 
 $path='tickets/'.$id.'.json'; //
$ticket=$this->post_crm($path,'GET');

if(!empty($ticket['ticket'])){
    $ticket=$ticket['ticket'];
}
if(!empty($ticket['customer']['id'])){
  $ticket['user_id']=$ticket['customer']['id'];  
} 
// var_dump($ticket['threads']); 

if(!empty($ticket['threads'])){
    $threads=!empty($ticket['threads']) ? $ticket['threads'] : array();
  $threads_arr=array();
   if(!empty($threads)){
      foreach($threads as $v){

//$v['body']=nl2br($v['body']);  
if(empty($v['body']) || $v['type'] !='message'){
    continue;
}
// 
 $threads_arr[]=$v;  
      } 
      $ticket['threads']=$threads_arr;
  //var_dump($threads_arr); die('-----------'); 

$ticket['threadCount']=count($threads_arr);

if(isset($ticket['status'])){    
$ticket['status']=array('name'=>$ticket['status'],'color'=>$this->label_color($ticket['status']));    
}


$ticket['number']=$ticket['id']; 
 
 
   } 
   
 
}

//die(json_encode($res));
    
return array('item'=>$ticket);


}

public function create_ticket($meta,$email,$req){
  
    $arr=array();
        if(!empty($req['body']) && !empty($this->mailbox)){   
    $body=$req['body'];
    
$post=array();

$customer=array();
if(!empty($email)){
 $customer['email']=$email;   
  
}

  if(!empty($req['first_name'])){
$customer['firstName']=$req['first_name'];  
  }
  if(empty($req['last_name'])){
  $req['last_name']=strtok($email,'@');    
  }
$customer['lastName']=$req['last_name'];  
  if(empty($customer['firstName'])){
  $customer['firstName']=$req['last_name'];
  }

   
   if(isset($req['phone'])){
   $customer['phone']=$req['phone'];
   }
   $user_id=get_current_user_id();
  $crm_id='';         
    if(!empty($user_id)){
    $crm_id=get_user_meta($user_id,'vx_teamwork_id',true);
    }
    
 if(empty($crm_id)){
     //find by email
   $crm_id=$this->get_customer($email);
      
   if(empty($crm_id)){
  //create customer
 $res=$this->post_crm('customers.json','post',$customer);

 if(isset($res['errors'])){
  $msg=json_encode($res['errors']);   
 }

 if(!empty($res['id']) ){
 $crm_id=$res['id'];
   if(!empty($user_id)){
 update_user_meta($user_id,'vx_teamwork_id',$crm_id);
   }    
 }       
   }  
 }   

 if(!empty($crm_id)){

if(!empty($req['tag'])){
 $post['tags']=array($req['tag']);   
}
$post['message']=$body;
$post['inboxId']=(int)$this->mailbox;
$post['customerId']=(int)$crm_id;

  if(!empty($req['subject'])){
$post['subject']=$req['subject'];  
  } 


$path='tickets.json'; $method='post';
    if(isset($_GET['conversation_id'])){
$path='tickets/'.vx_support_x::post('conversation_id').'.json';
   $post=array('body'=>$post['message'],'customerId'=>(int)$crm_id);
    }  
 $res=$this->post_crm($path,$method,$post);
//var_dump($res,$post); die(); 
   $uploads=array();  
        //process files attachment
    if(!empty($_FILES['file']['tmp_name']) && !empty($res['id'])){
$tmp_file=$_FILES['file']['tmp_name'];  
$contents=file_get_contents($tmp_file);  
$filename = sanitize_file_name( $_FILES['file']['name'] );
$mime_type=vx_support_x::get_mime_type($tmp_file,$filename);
if(!empty($tmp_file) && !empty($contents) && !empty($mime_type)){

 if( !empty($res['_links']['attachments']['href'])){
   //  $path=$res['_links']['attachments']['href'];
$u_id=$res['id'];
   if(isset($_GET['conversation_id'])){
$u_id=vx_support_x::post('conversation_id');    
}
   $path='api/v2/cases/'.$u_id.'/attachments';   
$upload=$this->post_crm($path,'post',array('file_name'=>$filename,'content_type'=>$mime_type,'content'=>base64_encode($contents)));

 }
}
  
  }
//var_dump($res,$post,$path); die();
$err='';
if(isset($res['id'])){
$arr['id']=$res['id'];

 if(isset($_GET['conversation_id'])){
$arr['thread_id']=$res['id'];    
}    
}else if(isset($res['errors'])){

 $err=json_encode($res['errors']);    
 
$arr['error']=$err;  
}
}
        }
  return $arr;      
}

public function get_mailboxes(){
$arr=$this->post_crm('inboxes.json');

$boxes=array();
if(isset($arr['inboxes']) && is_array($arr['inboxes'])){
    foreach($arr['inboxes'] as $v){
        if(empty($v['email'])){
         continue;   
        }
  $boxes[$v['id']]=$v['name'].'('.$v['email'].')';      
    }
}

return $boxes;
}
public function get_tags(){
$arr=$this->post_crm('tags.json');
$boxes=array();
if(isset($arr['tags']) && is_array($arr['tags'])){
    foreach($arr['tags'] as $v){
  $boxes[$v['name']]=$v['name'];      
    }
}

return $boxes;
}


public function curl_file_create($filename, $mimetype = '', $postname = '') {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
public function post_crm($path,$method='get',$body=''){
    
 
$head=array('Authorization'=>'Basic '.base64_encode($this->token.':xxx'),'User-Agent'=>'Wordpress Plugin');

if(!empty($body)){
    if(is_array($body)){
    $body=json_encode($body);
    $head['Content-Type']='application/json';    
    }else{
   $head['Content-Type']='application/binary';     
    }
}
$args = array('body' => $body,'headers'=> $head,
            'method' => strtoupper($method),'sslverify' => false,'timeout' => 40);
$response = wp_remote_request($this->url.$path, $args);
$json=wp_remote_retrieve_body($response);
 $arr=json_decode($json,true);
 if(!is_array($arr) && !empty($arr)){
  $arr['error']=$json;   
 }
 return $arr;
}
}

}
