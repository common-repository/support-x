<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vx_zendesk_api' ) ) {

/**
* Main class
*
* @since       1.0.0
*/
class vx_zendesk_api{
  
 private $url='';
  private $token='';
  private $email='';
  private $mailbox='';
  private $agents;
     
public function __construct($meta){ 
  
    if(!empty($meta['zen_token'])){
        $this->token=$meta['zen_token'];
    }  
      if(!empty($meta['zen_email'])){
        $this->email=$meta['zen_email'];
    } 
    if(!empty($meta['zen_url'])){
    
        $this->url=trailingslashit($meta['zen_url']);
    }
    
    }
public function get_customer($email){
 $path='api/v2/search.json?query='.urlencode('type:user email:'.$email);
$search_res=$this->post_crm($path,'GET');
//var_dump($search_res);
//die();
$hs_id='';
if(!empty($search_res['results'][0]['id'])){
  $hs_id=$search_res['results'][0]['id']; 
}  
return $hs_id;

}

public function get_tickets($user_id,$page=''){

    $arr=array();  $leads=array();  $per_page=30;
 if(!empty($user_id) ){
     //get conversations
 $path='api/v2/users/'.$user_id.'/tickets/requested.json?sort_order=desc&sort_by=id&per_page='.$per_page;
 if(!empty($page)){
  $path.='&page='.$page;   
 }

$arr=$this->post_crm($path,'GET');

if(!empty($arr['tickets']) && is_array($arr['tickets'])){
    foreach($arr['tickets'] as $v){
 $v['number']=$v['id'];
 $v['createdAt']=$v['created_at'];
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
  if($status == 'open'){ //open
      $color='active';
  }else if($status == 'pending'){ //pending
    $color='pending';  
  }else if($status == 'solved'){ //resloved
   $color='info';   
  }  
  return $color;
}
public function get_ticket($id,$page=''){


            $user_id=get_current_user_id();
    $crm_id='';
    if(!empty($user_id)){
    $crm_id=get_user_meta($user_id,'vx_zendesk_id',true);
    }
    $path='api/v2/tickets/'.$id.'.json'; //
$arr=$this->post_crm($path,'GET');

$res=array();
if(!empty($arr['ticket'])){
    $ticket=$arr['ticket'];
    $path='api/v2/tickets/'.$id.'/comments.json?sort_order=desc'; //
    $threads=$this->post_crm($path,'GET');
  $threads_arr=array();
   if(!empty($threads['comments'])){
      foreach($threads['comments'] as $v){
            if($v['public'] === false){
    continue;
}
     if(isset($v['created_at'])){
 $v['createdAt']=$v['created_at'];   
}
$by=array();
if($v['author_id'] == $crm_id){
$by['type']='customer';    
}else{
  $by=$this->get_user($v['author_id']);    
$by['type']='user';  
}

//$v['body']=nl2br($v['body']); 
$v['body']=$v['html_body']; 
$v['createdBy']=$by; 

//
   if(isset($v['attachments']) && is_array($v['attachments'])){
  $atts=array();
    foreach($v['attachments'] as $att){
  $atts[]=array('fileName'=>$att['file_name'],'url'=>$att['content_url']);      
    }   
$v['attachments']=$atts;   
   } 
 $threads_arr[]=$v;  
      } 
   
   $res=array('threads'=>$threads_arr);
$res['threadCount']=count($threads_arr);

if(isset($ticket['status'])){    
$res['status']=array('name'=>$ticket['status'],'color'=>$this->label_color($ticket['status']));    
}
if(isset($ticket['created_at'])){
 $res['createdAt']=$ticket['created_at'];   
}

if(isset($ticket['subject'])){
 $res['subject']=$ticket['subject'];   
}
$res['number']=$ticket['id']; 
 
 
   } 
 if(!empty($ticket['requester_id'])){
 $res['user_id']=$ticket['requester_id'];    
 }  
 
}

//die(json_encode($res));
    
return array('item'=>$res);


}

public function create_ticket($meta,$email,$req){
  

    $arr=array();
        if(!empty($req['body'])){   
    $body=$req['body'];
    
$post=array();

$customer=array();
if(!empty($email)){
 $customer['email']=$email;   
}

  if(!empty($req['name'])){
$customer['name']=$req['name'];  
  }
   
   if(isset($req['phone'])){
   $customer['phone']=$req['phone'];
   }

  if(!empty($req['subject'])){
$post['subject']=$req['subject'];  
  }   

if(!empty($req['tag'])){
 $post['tags']=array($req['tag']);   
}
$post['comment']=array('body'=>$body);
if(!empty($req['vx_fields'])){
 $custom=array();
 foreach($req['vx_fields'] as $k=>$v){
     if(!empty($v)){
     $custom[]=array('id'=>$k,'value'=>$v);
     }
 }
 if(!empty($custom)){
$post['custom_fields']=$custom; }    
}
//var_dump($meta['custom_fields']); die('-----');
  $uploads=array();  
        //process files attachment
    if(!empty($_FILES['file']['tmp_name'])){
$tmp_file=$_FILES['file']['tmp_name'];  
$contents=file_get_contents($tmp_file);  
if(!empty($tmp_file) && !empty($contents)){
$filename = sanitize_file_name( $_FILES['file']['name'] );
$path='api/v2/uploads.json?filename='.$filename;
$upload=$this->post_crm($path,'post',file_get_contents($tmp_file));

if(!empty($upload['upload']['token'])){       
$post['comment']['uploads']=array($upload['upload']['token']);
 
}
}
  
  }

$path='api/v2/tickets.json'; $method='post';
    if(isset($_GET['conversation_id'])){
$path='api/v2/tickets/'.vx_support_x::post('conversation_id').'.json';
$method='put';   
$user_id=get_current_user_id();
           
    if(!empty($user_id)){
    $crm_id=get_user_meta($user_id,'vx_zendesk_id',true);
    $post['comment']['author_id']=$crm_id;
    }    
 
    }else{
   $post['requester']=$customer;     
    } 
 $res=$this->post_crm($path,$method,array('ticket'=>$post));
 if(!empty($res['ticket']['requester_id']) && !empty($req['phone'])){
$req_id=$res['ticket']['requester_id'];
    $path='api/v2/users/'.$res['ticket']['requester_id'].'.json';
$this->post_crm($path,'put',array('user'=>array('phone'=>$req['phone']))); 
 }
//
$err='';
if(isset($res['error'])){
   $err=$res['error'];
    if(isset($res['description'])){
 $err=$res['description'];  
    }
 if(isset($res['details'])){
 $err.=' '.json_encode($res['details']);    
 }
$arr['error']=$err;  
}else if(isset($res['ticket']['id'])){
$arr['id']=$res['ticket']['id'];

if(isset($res['audit']['events'][0]['id'])){
$arr['thread_id']=$res['audit']['events'][0]['id'];    
}    
}
}

  return $arr;      
}
public function get_user($id){
   if(empty($this->agents)){
    $this->agents=get_option(vx_support_x::$id.'_zendesk_agents',array());
   }

    if(!isset($this->agents[$id])){
    $path='api/v2/users/'.$id.'.json'; //
$agents_arr=$this->post_crm($path,'GET');
$agents=array();
if(!empty($agents_arr['user'])){
$user=$agents_arr['user'];
 $agents[$user['id']]=$user;       

}

$this->agents=$agents;

update_option(vx_support_x::$id.'_zendesk_agents',$agents);    
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
public function get_tags(){
$arr=$this->post_crm('api/v2/tags.json');
$boxes=array();
if(isset($arr['tags']) && is_array($arr['tags'])){
    foreach($arr['tags'] as $v){
  $boxes[]=$v['name'];      
    }
}

return $boxes;
}
public function get_ticket_fields(){
    $path='api/v2/ticket_fields.json';
    $arr=$this->post_crm($path);

    $fields=array();
    if(!empty($arr['ticket_fields']) && is_array($arr['ticket_fields'])){
        foreach($arr['ticket_fields'] as $v){ 
            if($v['removable'] === true && $v['editable_in_portal'] === true){
                $type=$v['type'] == 'tagger' ? 'select' : $v['type'];
     $field=array('name'=>$v['id'],'label'=>$v['title'],'type'=>$type);           
     if(!empty($v['custom_field_options'])){
    $options=array();
     foreach($v['custom_field_options'] as $option){
    $options[$option['value']]=$option['name'];     
     }    
     $field['options']=$options;
     }
     if($v['required_in_portal'] === true){
         $field['req']='1';
     } 
     $fields[(string)$v['id']]=$field;
            }
        }
    }

return $fields;
}

public function curl_file_create($filename, $mimetype = '', $postname = '') {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
public function post_crm($path,$method='get',$body=''){
$head=array('Authorization'=>'Basic '.base64_encode($this->email.'/token:'.$this->token));
if(!empty($body)){
    if(is_array($body)){
    $body=json_encode($body);
    $head['Content-Type']='application/json';    
    }else{
   $head['Content-Type']='application/binary';     
    } }

            $args = array(
            'body' => $body,
            'headers'=> $head,
            'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
            'sslverify' => false,
            'timeout' => 20,
        );
$response = wp_remote_request($this->url.$path, $args);
$json=wp_remote_retrieve_body($response);
 return json_decode($json,true); 

}
}

}
