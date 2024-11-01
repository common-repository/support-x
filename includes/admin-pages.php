<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vx_support_x_pages' ) ) {

class vx_support_x_pages   extends vx_support_x{
 
   public function __construct() {
   
       add_filter("admin_menu", array($this, 'main_menu'), 10);
       
       add_action( 'wp_ajax_vx_support_x_get_mailboxes', array( $this, 'get_mailboxes_list' ) );
       add_action( 'wp_ajax_vx_helpscout_app', array( $this, 'help_scout_app' ) );
       add_action( 'wp_ajax_nopriv_vx_helpscout_app', array( $this, 'help_scout_app' ) );
add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2); 
   }
public  function main_menu($menus){
  // Adding submenu if user has access
        $menu_id='vx_support_x'; 
        if(empty($GLOBALS['admin_page_hooks'][$menu_id])){
        $page_title =__('Tickets','support-x');
        $capability = 'manage_options';
       $hook=add_menu_page($page_title,$page_title,$capability,$menu_id,array( $this,'settings_page'),'dashicons-sos');
        }
    
if(  !empty($_REQUEST['support_x_action']) && $_REQUEST['support_x_action']=="get_code"){
if(current_user_can("manage_options")){ 
$meta=$this->get_meta(); 
$api=$this->get_api($meta);  
$meta=$api->handle_code();
$boxes=$api->get_mailboxes(); 
$crm=vx_support_x::post('crm',$meta); 
$option=get_option(vx_support_x::$id.'_boxes',array());
$option[$crm]=$boxes;
update_option(vx_support_x::$id.'_boxes',$option);
 
$link=$this->link_to_settings();
wp_redirect($link); 
die(); 
}
}      
        
}
  
  /**
  * Settings page
  * 
  */
  public  function settings_page(){ 

  $is_section=apply_filters('add_page_html_'.vx_support_x::$id,false);

  if($is_section === true){
    return;
} 

  $msgs=array(); 

 $crms=$this->get_crms();
 $meta=$this->get_meta();

if(empty($tab)){ 
$boxes=get_option(vx_support_x::$id.'_boxes',array());
  if(empty($meta['secret_key'])){
    $meta['secret_key']=wp_generate_password( 40, false );  
  }
  $api=$this->get_api($meta); 
  if(isset($_REQUEST['vx_test_help_connection'])){
   $token=$api->refresh_token();
   if(!empty($token['help_token'])){
   $msgs['test']=array('class'=>'updated','msg'=>__('Connection to HelpScout is Working','support-x'));  
   }else{
   $msgs['test']=array('class'=>'error','msg'=>__('Connection to HelpScout is NOT Working','support-x')); 
   } 
  }
   if(isset($_REQUEST['vx_remove_help_connection'])){
  unset($meta['help_token']);  
  unset($meta['help_refresh_token']); 
  vx_support_x::update_meta($meta);  
  }
   if(!empty($_POST["save"])){ //var_dump($_REQUEST); die(); 
  check_admin_referer("vx_nonce");
  $post=vx_support_x::post('meta');
$meta_key='';  
if($post['crm'] == 'helpscout' ){ //&& vx_support_x::post('hs_key',$meta)  != $post['hs_key']
 $meta_key='helpscout';    
}
if($post['crm'] == 'freshdesk' && vx_support_x::post('freshdesk_token',$meta) != $post['freshdesk_token']){
 $meta_key='freshdesk';   
}
if($post['crm'] == 'zendesk'  && vx_support_x::post('zen_token',$meta) != $post['zen_token']){
 $meta_key='zendesk';   
}
if($post['crm'] == 'teamwork'  && vx_support_x::post('teamwork_token',$meta) != $post['teamwork_token']){
 $meta_key='teamwork';   
}
$check=array('plugin_data','phone_field','file_field','wc_tab');
foreach($check as $c){
 $post[$c]=vx_support_x::post($c,$post);   
}
  if(is_array($meta)){
   $meta=array_merge($meta,$post);   
  }else{
$meta=$post;
  }
if(!empty($meta_key)){
       global $wpdb;
   $wpdb->update( $wpdb->usermeta , array('meta_value'=>''),array( 'meta_key' => 'vx_'.$meta_key.'_id' ) );
}
if(isset($meta['hs_tag']) && $meta['hs_tag'] == 'hs'){
    
 //get hs tags here
 $tags=$api->get_tags();
    $crm=vx_support_x::post('crm',$meta);
if(!empty($tags)){
 $option=get_option(vx_support_x::$id.'_tags');
 $option[$crm]=$tags;   
 update_option(vx_support_x::$id.'_tags',$option);   

} }
self::$meta=$meta;
if(!empty($meta['wc_tab'])){ $this->add_endpoint_flush(); }

if(!empty($meta['help_key_id']) && $meta['help_key_id'] != $meta['help_key']){
   unset($meta['help_token']);  
  unset($meta['help_refresh_token']);  
}

update_option(vx_support_x::$id.'_meta' ,vx_support_x::en_crypt( json_encode( $meta ) ) );

$msgs['submit']=array('class'=>'updated','msg'=>__('Settings Changed Successfully','support-x'));
}                
     }
    $api_fields=$this->get_api($meta);  

    if(isset($_GET['crm'])){
     $meta['crm']=vx_support_x::post('crm');    
    }
     $api=$this->get_api($meta); 
      $crm=vx_support_x::post('crm',$meta); 
           $tabs=array(''=>__('Settings', 'support-x'));
    if(is_object($api_fields) && method_exists($api_fields,'get_ticket_fields')){
    $tabs['fields']=__('Custom Fields', 'support-x');    
    }
$tabs=apply_filters('admin_tabs_vx_support_x',$tabs);
$tab=vx_support_x::post('tab');
$link=admin_url('admin.php?page=vx_support_x');
if(!empty($msgs)){
      foreach($msgs  as $msg){
      $this->screen_msg($msg['msg'],$msg['class']);    
      }
  }  
?><h2 class="nav-tab-wrapper">
<?php
        foreach($tabs as  $k=>$v){
         $tab_q=!empty($k) ? '&tab='.$k : ''    
    ?>
        <a href="<?php echo $link.$tab_q ?>" class="nav-tab <?php if($k == $tab){echo 'nav-tab-active';} ?>"><?php echo $v; ?></a>
            
    <?php
        }
        ?>
        </h2>
    <?php
    if(empty($tab)){ 

include_once(self::$path . "templates/settings.php");
    }else if($tab == 'fields'){
    $custom=get_option(vx_support_x::$id.'_custom_fields',array());
      // var_dump($custom); 
    $fields=!empty($custom[$crm]) ? $custom[$crm] : array();      
    if(!empty($_POST['refresh_fields']) || empty($fields)){
   $data=$fields;
   $fields=$api->get_ticket_fields();  
    }

     if(!empty($_POST['fields'])){
$data=vx_support_x::post('fields');      
     }

             if(!empty($data)){
    foreach($data as $k=>$v){
    if(isset($v['display']) && isset($fields[$k])){
    $fields[$k]['display']=$v['display'];    
    $fields[$k]['custom_label']=$v['label'];    
    }    
    }
    
  //     if(!empty($_POST['save_fields'])){
         $custom[$crm]=$fields;
         //  update_option(vx_support_x::$id.'_meta',$meta);
  update_option(vx_support_x::$id.'_custom_fields',$custom); 
  $msgs['submit']=array('class'=>'updated','msg'=>__('Settings Changed Successfully','support-x'));  
    //   }
        }
         if(!empty($msgs)){
      foreach($msgs  as $msg){
      $this->screen_msg($msg['msg'],$msg['class']);    
      }
  }
$crm_title=isset($crms[$crm]) ? $crms[$crm] : '';
     ?>
     <h2><u><?php echo $crm_title.' '; ?></u> Custom Fields</h2>
     <?php   
$custom_file=self::$path . "pro/custom-fields.php";
        if(empty($fields)){
       ?>
    <div class="error below-h2"><p><?php _e('No Fields Found','support-x'); ?></p></div>   
       <?php     
        }else if(!file_exists($custom_file)){
             ?>
    <div class="error below-h2"><p><?php _e('Custom Fields is a Premium Feature','support-x'); ?></p></div>   
       <?php     
        }else{
 // var_dump($fields);     
 include_once($custom_file);       
        }
 }else{
     do_action('admin_tabs_section_vx_support_x');
 }
    }

        /**
  * Add settings and support link
  * 
  * @param mixed $links
  * @param mixed $file
  */
  public function plugin_action_links( $links, $file ) {
   $slug=vx_support_x::$slug;
      if ( $file == $slug ) {
          $settings_link=$this->link_to_settings();
            array_unshift( $links, '<a href="' .$settings_link.'">' . __('Settings', 'support-x') . '</a>' );
        }
        return $links;
   }
  public function get_mailboxes_list(){
    $meta=vx_support_x::post('meta');
    $crm=vx_support_x::post('crm',$meta);
    if($crm == 'helpscout'){
    self::$meta=array();
    $meta=$this->get_meta();
}
 $api=$this->get_api($meta);
 $boxes=$api->get_mailboxes(); 
 
 $option=get_option(vx_support_x::$id.'_boxes',array());
 $option[$crm]=$boxes;
 update_option(vx_support_x::$id.'_boxes',$option);
 $html=''; $status='error';
 if(is_array($boxes)){
     $status='ok';
 foreach($boxes as $k=>$v){
  $html.="<option value='$k'>$v</option>";   
 }    
 }
 
$arr=array('status'=>$status,'data'=>array('vx_'.$crm.'_mailbox'=>$html));
echo json_encode($arr); 
die();
  }
  public function help_scout_app(){
 //   $order=wc_get_order(46);
      // clear output, some plugins might have thrown errors by now.
        if ( ob_get_level() > 0 ) {
            ob_end_clean();
        }
     ob_start();   
//var_dump($order->id); die();
$colors=array('Completed'=>'#03a747','Processing'=>'#ff9139');
      //verify signature
      $valid=false; 
      $headers=getallheaders(); 
    // $headers['X-Helpscout-Signature']='fEFOJIMW1FILxo3lWgNz+M0C1LE=';
      if(isset($headers['X-HelpScout-Signature'])){
        $token=$headers['X-HelpScout-Signature'];
        $meta=$this->get_meta();
      $db_token=vx_support_x::post('secret_key',$meta);   //var_dump($token == base64_encode($db_token),base64_encode($db_token),$token); die();
      $json=trim(file_get_contents( 'php://input' ));
      $req= json_decode($json,true);
      
      $expected_signature = base64_encode( hash_hmac( 'sha1',  $json , $db_token, true ) );
      if($token == $expected_signature){
     $valid=true;  
  
//die(json_encode($req));
//$json='{"ticket":{"id":"322585297","number":"42","subject":"first subject - Order 357"},"customer":{"id":"114322164","fname":"Tef","lname":"Ahd","email":"o25@gmail.com","emails":["o25@gmail.com","123@gmail.com"]},"user":{"fname":"abc","lname":"Asd","id":"134073","role":"admin","convRedirect":0},"mailbox":{"id":"100016","email":"test2@startd.com"}}';
//$req=json_decode($json,true);

$emails=array();
  if(isset($req['customer']['emails']) && is_array($req['customer']['emails']) && count($req['customer']['emails'])>0){
$emails=$req['customer']['emails'];  
  }else if(isset($req['customer']['email'])){
 $emails=array($req['customer']['email']);     
  }

 $user_ids=array();  
  if(is_array($emails) && count($emails)>0){
      foreach($emails as $email){
   $user=get_user_by('email',$email);
   if(isset($user->ID)){
   $user_ids[]=$user->ID;    
   }     
    }
  }
global $wpdb;
//get orders  
  if(is_array($user_ids) && count($user_ids)>0){
      $sql="   SELECT post_id
                FROM   $wpdb->postmeta AS postmeta
                WHERE postmeta.meta_key = '_customer_user'
                AND postmeta.meta_value IN( ".implode(',',$user_ids)." )
                LIMIT 10";
     $results= $wpdb->get_results($sql,ARRAY_A); 
    
       // var_dump($results,$sql); die(); 
 $open='open';      
  foreach($results as $v){
  if(!empty($v['post_id'])){
   //get order
   $order=wc_get_order($v['post_id']);
   if(empty($order->id)){
       continue;
   }
   $order_id=$order->id;
   $amount  = $order->get_formatted_order_total();
   $status  = wc_get_order_status_name( $order->get_status() );
   $time_format=$this->time_format();
   $date    = date( $time_format, strtotime( $order->order_date ) );
   $link= get_edit_post_link( $order_id );
   ?>
  <div class="toggleGroup <?php echo $open ?>"><i class="icon-cart"></i><a class="toggleBtn"> #<?php echo $order_id.' - '.$amount ?> <i class="icon-arrow"></i></a>
  
  <div class="toggle indent">
  <p><span class="muted"><?php echo $date ?></span></p>
  <p><a href="<?php echo $link ?>" target="_blank">#<?php echo $order_id  ?></a> - <span style="color:<?php echo isset($colors[$status]) ? $colors[$status] : '#f33d3d' ?>;"><?php echo $status ?></span></p>
  <?php
      $items          = $order->get_items();
  if ( is_array( $items ) && count( $items ) > 0 ) {
   ?>
   <ul class="unstyled">
   <?php
                foreach ( $items as $item_id => $item ) {
  ?>
  <li><div style="background:#fefefe;margin-bottom:1em;padding:.5em .7em;"><?php echo $item['name'].' - '.wc_price($order->get_item_total( $item, false, true )) ?></div>
  </li>
  <?php
                }
  ?>
   </ul>
  <?php
  }
  ?>
 
  </div>
  </div>
   <?php
  $open='';    
  }
      
  }
  }      
      }    
      }
 $html=ob_get_clean();
 if(empty($html)){
  $html='<p>No Data Found</p>';   
 }
      
die(json_encode(array('html'=>$html)));      
  }  
  private function get_crms(){
       $crms=array('helpscout'=>'Help Scout','zendesk'=>'ZenDesk','freshdesk'=>'FreshDesk','teamwork'=>'Teamwork Desk');
       return $crms;
  }  
}
}
new vx_support_x_pages();