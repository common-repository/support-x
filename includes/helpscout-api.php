<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vx_helpscout_api' ) ) {

/**
* Main class
*
* @since       1.0.0
*/
class vx_helpscout_api{
  
 // public static $url=true;
//  private static $email= false;
  private $mailbox='';
  private $meta;
     
public function __construct($meta){ 
  
    $this->meta=$meta;
    if(!is_array($this->meta)){
        $this->meta=array();
    }

    if(!empty($meta['helpscout_mailbox'])){
        $this->mailbox=$meta['helpscout_mailbox'];
    } 
}
public function refresh_token(){
  $meta=$this->meta;
  

if( empty($meta['help_refresh_token']) ){
return $meta;   
}
$body=array("client_id"=>$meta['help_key'],"client_secret"=>$meta['help_secret'],"grant_type"=>"refresh_token","refresh_token"=>$meta['help_refresh_token']);
$token=$this->post_crm('','token',$body);

  if(!empty($token['access_token'])){ 
  $meta["help_token"]=$token['access_token'];
  if(!empty($token['refresh_token'])){
  $meta["help_refresh_token"]=$token['refresh_token'];
  }
  $meta["token_time"]=time(); 
  }else{
  $meta['help_error']=$token['error_description'].' refresh token='.$meta['help_refresh_token'];
  $meta['help_token']="";
 // $to=get_option('admin_email');
 if(!empty($meta['help_error_email'])){
  wp_mail( $meta['help_error_email'], 'wordpress helpscout support plugin', 'Helpscout Error= '.$meta['help_error']  );
  } }
  //api validity check
  $this->meta=$meta;
vx_support_x::update_meta($meta);
  return $meta; 
}

public function handle_code(){
      $meta=$this->meta;
  $token=array();

  if(isset($_REQUEST['code'])){
  $code=vx_support_x::post('code'); 
  
  if(!empty($code)){
  $body=array("client_id"=>$meta['help_key'],"client_secret"=>$meta['help_secret'],"grant_type"=>"authorization_code","code"=>$code);
 $token=$this->post_crm('','token',$body);
  }
  if(isset($_REQUEST['error_description'])){
   $token['error']=vx_support_x::post('error_description');   
  }

  }

  $meta['help_token']=vx_support_x::post('access_token',$token);
 // $info['token_exp']=$this->post('expires_in_sec',$token);
  $meta['help_key_id']=$meta['help_key'];
  $meta['help_refresh_token']=vx_support_x::post('refresh_token',$token);
  $meta['token_time']=time();
  $meta['_time']=time();
  $meta['help_error']=vx_support_x::post('error',$token);
  $meta['crm']="helpscout";

  $this->meta=$meta;

 vx_support_x::update_meta($meta); 
  return $meta;
 }

public function get_customer($email){
 
 $path='customers?mailbox='.$this->mailbox.'&query=(email:"'.urlencode($email).'")';
$search_res=$this->post_crm($path,'GET');
$hs_id='';
if(!empty($search_res['_embedded']['customers'][0]['id'])){
  $hs_id=$search_res['_embedded']['customers'][0]['id']; 
}  
return $hs_id;

}

public function get_tickets($user_id,$page=''){

    $arr=$res=array();   
 if(!empty($user_id) && !empty($this->mailbox)){
     //get conversations
 $path='conversations?status=all&mailbox='.$this->mailbox.'&query=(customerIds:'.$user_id.')';
 if(!empty($page)){
  $path.='&page='.$page;   
 }
$arr_a=$this->post_crm($path,'GET'); //var_dump($arr_a,$page);

if(!empty($arr_a['_embedded']['conversations']) && is_array($arr_a['_embedded']['conversations'])){
    foreach($arr_a['_embedded']['conversations'] as $v){
     $v['status']=array('color'=>$v['status'],'name'=>$v['status']);   
  $v['threadCount']=$v['threads'];

    $arr[]=$v;
    }
$res['items']=$arr;
  if(!empty($arr_a['page']['totalPages'])){
   $res['pages']=$arr_a['page']['totalPages'];   
  }
  if(!empty($arr_a['page']['number'])){
   $res['page']=$arr_a['page']['number'];   
  }
  if(!empty($arr_a['page']['totalElements'])){
   $res['count']=$arr_a['page']['totalElements'];   
  }
}
//$status=!in_array($field_label,array('active','pending')) ? 'closed' : $field_label;
 }
return $res;

}

public function get_ticket($id,$page=''){

$res=array();   
$path='conversations/'.$id;
$item=$this->post_crm($path,'GET'); //var_dump($arr);
if(!empty($item['primaryCustomer']['id'])){
$res['item']=$item;    
$res['item']['user_id']=$item['primaryCustomer']['id'];  
$res['item']['threadCount']= $item['threads'];
}
$path='conversations/'.$id.'/threads';
$arr=$this->post_crm($path,'GET');
//var_dump($arr); die();
$user_ids=array();
if(!empty($arr['_embedded']['threads'])){
 $threads=array();
  foreach($arr['_embedded']['threads'] as $k=>$v){
      if(!isset($v['state'])){ $v['state']='published'; }
      if(isset($v['type']) && in_array($v['type'],array('message','customer')) && isset($v['state']) && in_array($v['state'],array('published'))){
       $files=array(); 
    if(!empty($v['_embedded']['attachments'])){
        foreach($v['_embedded']['attachments'] as $vv){
            $files[]=array('fileName'=>$vv['filename'],'url'=>$vv['_links']['web']['href']);
        }
    }
    unset($v['_embedded']);
    $v['attachments']=$files;
    $threads[]=$v; 
    $user_ids[]=$v['createdBy']['id'];     
    $user_ids[]=$v['customer']['id'];     
      }
  }
$res['item']['threadCount']= count($threads);    
$res['item']['threads']=$threads;
$res['item']['user_id']=array_unique($user_ids);
}

if(isset($res['item']['status'])){
 $res['item']['status']=array('color'=>$res['item']['status'],'name'=>$res['item']['status']);
}
 return $res;

}

public function create_ticket($meta,$email,$req){

    $arr=array();
        if(!empty($req['body']) && !empty($email) && !empty($meta['helpscout_mailbox']) ){
   if(isset($req['subject'])){
    $subject=$req['subject']; 
   }   
    $body=$req['body'];

    $thread=array('type'=>'customer','text'=>$body,'customer'=>array( 'email'=>$email));  
   
    //process files attachment
    if(!empty($_FILES['file']['tmp_name'])){
$tmp_file=$_FILES['file']['tmp_name'];  
$contents=file_get_contents($tmp_file);  
if(!empty($tmp_file) && !empty($contents)){
$filename = sanitize_file_name( $_FILES['file']['name'] );
$mime_type=vx_support_x::get_mime_type($tmp_file,$filename);
if(!empty($mime_type)){    
$attach=array('fileName'=>$filename,'mimeType'=>$mime_type,'data'=>base64_encode( $contents));    
$thread['attachments']=array($attach);
/*$attach_res=$this->post_crm('attachments.json','post',$attach); 
if(!empty($attach_res['item']['hash'])){
  $thread['attachments']=array(array('hash'=>$attach_res['item']['hash']));  
} */
}
}
  
  }
  $customer=array('email'=>$email);
  if(!empty($req['name'])){
   $n=explode(' ',$req['name']);
   if(!empty($n[0])){
  $customer['firstName']=$n[0];     
   }
    if(!empty($n[1])){
  $customer['lastName']=$n[1];     
   }   
  }
   
   if(isset($req['phone'])){
   $customer['phone']=$req['phone']; 
   }
$post=array('type'=>'email',
'mailboxId'=>$meta['helpscout_mailbox'],
'customer'=>$customer,
'subject'=>$subject,
'status'=>'active',
'threads'=>array($thread)
);
  
if(!empty($req['tag'])){
 $post['tags']=array($req['tag']);   
}
$path='conversations'; $method='post';
    if(isset($_GET['conversation_id'])){
$path='conversations/'.vx_support_x::post('conversation_id').'/customer'; 
$post=$thread;      
    }
//$post['reload']=true;    

 $res=$this->post_crm($path,$method,$post);

   if(isset($res['message'])){
   $arr['error']=$res['message'];   
  }else{
      $arr['id']='xx';
      $arr['thread_id']='xx';
         if(isset($req['status'])){
   // $thread['status']=$req['status']; 
    $path='conversations/'.vx_support_x::post('conversation_id');
$this->post_crm($path,'PATCH',array('op'=>'replace','path'=>'/status','value'=>'closed'));
   
     } 
  }
        }
  return $arr;      
}

public function get_mailboxes(){
$arr=$this->post_crm('mailboxes');
//var_dump($arr,$this->meta);
$boxes=array();
if(isset($arr['_embedded']['mailboxes']) && is_array($arr['_embedded']['mailboxes'])){
    foreach($arr['_embedded']['mailboxes'] as $v){
  $boxes[$v['id']]=$v['name'];      
    }
}

return $boxes;
}
public function get_tags(){
$arr=$this->post_crm('tags');

$boxes=array();
if(isset($arr['_embedded']['tags']) && is_array($arr['_embedded']['tags'])){
    foreach($arr['_embedded']['tags'] as $v){
  $boxes[$v['id']]=$v['name'];      
    }
}

return $boxes;
}
public function post_crm($path,$method='get',$body=''){

    $res=$this->post_help($path,$method,$body);
    if( $method != 'token' && isset($res['error']) && $res['error'] == 'invalid_token'){
     $this->refresh_token(); 
     if(!empty($this->meta['help_token'])){
    $res=$this->post_help($path,$method,$body);   
     }   
    }
 return $res;   
}
public function post_help($path,$method='get',$body=''){
    
    $meta=$this->meta; $head=array();
    $token=vx_support_x::post('help_token',$meta);
    $url='https://api.helpscout.net/v2/'.$path;
    if($method == 'token'){
    $url='https://api.helpscout.net/v2/oauth2/token';
    $method='post';
    $body=http_build_query($body);
    }else{
     $head=array('Authorization'=> 'Bearer '.$token);   
    }
       
     if(is_array($body) && count($body)>0){
         $body=json_encode($body);
         $head['Content-Length']=strlen($body);
         $head['Content-Type']='application/json';
    }else{
       $head['Content-Type']='application/x-www-form-urlencoded';   
    }


            $args = array(
            'body' => $body,
            'headers'=> $head,
            'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
            'timeout' => 20,
        );

       $response = wp_remote_request($url, $args);

   $json=wp_remote_retrieve_body($response);
return json_decode($json,true);
}
}

}
