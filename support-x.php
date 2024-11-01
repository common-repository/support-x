<?php
/**
* Plugin Name: CRM Perks HelpDesk Integration
* Description: Show user tickets from HelpScout, ZenDesk, FreshDesk and Teamwork in wordpress. Users can create new tickets and reply to old tickets from wordpress. 
* Version: 1.1.5
* Requires at least: 3.8
* Author URI: https://www.crmperks.com
* Plugin URI: https://www.crmperks.com/plugins/support-plugins/support-x/
* Author: CRM Perks
* Text Domain: support-x
*/
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vx_support_x' ) ):

class vx_support_x {

  
 public  $url = "https://www.crmperks.com";
  public  $version = "1.1.5";
  public  $data = null;
  public $domain = "support-x";
  private $plugin_dir= "";

  public static $id = "vx_support_x";
  public static $title='Support X';  
  public static $path = ''; 
  public static $slug = "";
  public static  $lic_msg = "";  
  public static $plugin;    
  public static $meta;   
  public static $token;   

 public function instance(){

    $this->define_constants();  
 self::$slug=self::get_slug();
  self::$path=$this->get_base_path(); 
 
 add_action( 'plugins_loaded', array( $this, 'setup_main' ) );
register_deactivation_hook(__FILE__,array($this,'deactivate'));
register_activation_hook(__FILE__,(array($this,'activate')));
}
/**
  * Creates or updates database tables. Will only run when version changes
  * 
  */
public  function setup_main(){

load_plugin_textdomain('support-x', FALSE,  $this->plugin_dir_name(). '/languages/' ); 
if(is_admin()){
     //plugin api
$this->plugin_api(true);

require_once(self::$path."includes/admin-pages.php"); 
$this->start_plugin();
}

add_shortcode( 'crm-perks-tickets' , array($this,'user_tickets_table'));
add_shortcode( 'crm-perks-form' , array($this,'create_ticket_form'));

add_filter( 'woocommerce_account_menu_items', array( $this, 'wc_account_tab' ) );


add_action( 'template_redirect', array($this,'process_form') ); 
add_action( 'admin_init', array($this,'process_form') ); 
add_action( 'init', array($this,'init') ); 
//add_filter( 'query_vars',array($this,'add_query_var') );
}
public function init(){   
    $this->add_endpoint();
}
public  function plugin_api($start_instance=false){
    if(empty(self::$path)){   self::$path=$this->get_base_path(); }
$file=self::$path . "pro/plugin-api.php";
if(file_exists($file)){   
if(!class_exists('vxcf_plugin_api')){    include_once($file); }

if(class_exists('vxcf_plugin_api')){
 $slug=self::get_slug();
 $settings_link=$this->link_to_settings();
 $is_plugin_page=$this->is_crm_page(); 
 
self::$plugin=new vxcf_plugin_api(vx_support_x::$id,$this->version,vx_support_x::$id,$this->domain,'8000001',self::$title,$slug,self::$path,$settings_link,$is_plugin_page);
if($start_instance){
self::$plugin->instance();
}
}
}
 }
public function start_plugin(){
    $file=self::$path."pro/pro.php";
    if(file_exists($file)){
    include_once($file); 
    }
    $file=self::$path."wp/crmperks-notices.php";
    if(file_exists($file)){
    include_once($file); 
    }
}
        /**
     * Define WC Constants.
     */
private function define_constants() {

        $this->define( 'vx_support_x_dir', plugin_dir_path(__FILE__) );
        $this->define( 'vx_support_x_url', plugin_dir_url(__FILE__) );
 
    }
        /**
     * Define constant if not already set.
     *
     * @param  string $name
     * @param  string|bool $value
     */
private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
}
public  function get_customer_tickets($email='', $user_id='',$records=50){
 $meta=$this->get_meta();
$crm=vx_support_x::post('crm',$meta);
     $hs_id=get_user_meta($user_id,'vx_'.$crm.'_id',true);
    
    $api=$this->get_api($meta); 
if(!$api){ return; }
 
//$hs_id=''; 
    if(empty($hs_id)){
     //search by email
     if(empty($email)){
     $user=get_user_by('ID',$user_id);
     if(isset($user->data->user_email)){
     $email=$user->data->user_email;    
     } 
     }
  
 $hs_id=$api->get_customer($email);

if(!empty($hs_id)){
  update_user_meta($user_id,'vx_'.$crm.'_id',$hs_id);  
}
}

$arr=array();   
 if(!empty($hs_id)){
     //get conversations
$page='';
 if(!empty($_GET['vx_page'])){
  $page=vx_support_x::post('vx_page');   
 }
 $arr=$api->get_tickets($hs_id,$page);
 }   
return $arr;
}
public  function get_ticket($id){
       $meta=$this->get_meta();
    $api=$this->get_api($meta);
    $res=$api->get_ticket($id);
return $res;
}
public function get_api($meta=''){
if(!empty($meta['crm'])){
 include_once(self::$path.'includes/'.$meta['crm'].'-api.php');
 $class='vx_'.$meta['crm'].'_api';
// var_dump($class);
return new $class($meta); 
    }   
}
public function create_ticket_form($atts){
    $this->start_plugin();
    $single_form=true;
        $logged=false;
        $user_id='';
    $user=wp_get_current_user(); 
    if(isset($user->user_email)){
    $user_id=$user->ID;   
    $logged=true;
    }
    $ticket=array();
wp_enqueue_script( 'vx-google-captcha', 'https://www.google.com/recaptcha/api.js' ); 
  ///  $meta=array('enable_phone'=>'d','enable_file'=>'a');
  $meta=$this->get_meta(); 
  ob_start();
    include_once(self::$path . "templates/style.php"); 
   include_once(self::$path . "templates/ticket-form.php"); 
   return ob_get_clean();
}
public  function user_tickets_table($atts){

     $this->start_plugin();
     wp_enqueue_script( 'vx-google-captcha', 'https://www.google.com/recaptcha/api.js' ); 
     
     $meta=$this->get_meta(); 
     $crm=vx_support_x::post('crm',$meta);
  $fields=array(array('name'=>'number','label'=>__('#','support-x')), array('name'=>'status','label'=> __('Status', 'support-x')),array('name'=>'subject','label'=> __('Subject', 'support-x')) );
if( $crm == 'helpscout'){
  $fields['threadCount']=array('name'=>'threadCount','label'=> __('Count', 'support-x'));
}
  $fields['created']=array('name'=>'createdAt', 'label'=> __('Last update', 'support-x'));
//  $fields['created']=array('name'=>'createdAt', 'label'=>'Time');

  //array('name'=>'threadCount','label'=>'Count') ,array('name'=>'preview','label'=>'Body')
    $css='';
    if(!empty($atts['font-size'])){
    // $atts['font-size']='x-small'; 
      $css=' style="font-size: '.$atts['font-size'].'"';     
    }
  

      $class='vx_entries_table ';
    if(!empty($atts['class'])){
     $class.=$atts['class'];   
    }
   $class=' class="'.$class.'"';   
  

    
      $table_id='';
    if(!empty($atts['id'])){
   $table_id='id="'.$atts['font-size'].'"';   
  }
  //var_dump($fields);
  $limit='60';
    if(!empty($atts['limit'])){
   $limit=$atts['limit'];   
  }
  $current_user_id=get_current_user_id(); 

$email='';  $user_id='';  $logged=false;    $single_form=false; $show_form=true;
  if(!empty($atts['disable-new-tickets'])){
     $show_form=false;  
    }
if(!empty($atts['email'])){
$email=$atts['email'];   
$user=get_user_by('email',$email);
if(isset($user->ID)){
$user_id=$user->ID;    
}
}
if(!empty($atts['user_id']) || !empty($current_user_id)){
$user_id=!empty($atts['user_id']) ? $atts['user_id'] : $current_user_id; 
$user=get_user_by('ID',$user_id);
if(isset($user->data->user_email)){
$email=$user->data->user_email;    
}  }
$ticket=array();  

ob_start();
if(!empty($current_user_id)){ 
    $logged=true;
//  'page' => int 1 'pages' => int 1 'count' => int 1
$link_t=$link = $this->current_url();
 $q=$_GET;
 unset($q['vx_type']);   
 unset($q['vx_msg']);   
 unset($q['vx_thread']);   
if(is_array($q) && count($q)>0){

$link.='?'.http_build_query($q); 
$link_q=$link.'&';

unset($q['conversation_id']);
$link_t=$link_t.'?'.http_build_query($q);    
}else{
$link_q=$link.'?';    
}
$base_url=$this->get_base_url();
$leads=array();

//wp_enqueue_style( 'vx-fonts-front' ); 

$time_format=$this->time_format();
$offset=$this->time_offset();

if(isset($_GET['conversation_id'])){

$user_photo=get_avatar_url($user_id, 40 ); 
$guest_photo=$base_url.'/images/guest.png';    
$ticket=$this->get_ticket($_GET['conversation_id']);

$hs_id=get_user_meta($user_id,'vx_'.$crm.'_id',true);

$user_ids=array();
if(!empty($ticket['item']['user_id'])){
$user_ids=$ticket['item']['user_id'];
if(!is_array($user_ids)){
$user_ids=array($user_ids);
}
}
if( isset($ticket['item']) && !empty($ticket['item']['number']) && in_array($hs_id,$user_ids)){
    $ticket=$ticket['item'];
    $path=self::$path . "templates/single-ticket.php";
   $path=apply_filters('support_x_single_ticket_path',$path);
    include_once($path);
 //  $q_str='conversation_id='.$_GET['conversation_id'];
}
}
else{

$res=$this->get_customer_tickets($email,$user_id,$limit);

$total=$pages=0;
if(!empty($res['items'])){
 $leads=$res['items']; 
 $total=$res['count'];
$pages=$res['pages'];  
}
$path=self::$path . "templates/tickets-table.php";
$path=apply_filters('support_x_tickets_table_path',$path);

include($path);
//$page=$res['page'];
$page=1;
if(!empty($_GET['vx_page'])){
$page=vx_support_x::post('vx_page');    
}

$per_page=50;
if(isset($res['per_page'])){
    $per_page=$res['per_page'];
}
if($pages>1){
  echo $this->pagination($total,$page,$per_page);
}
$show_form=apply_filters('vx_support_x_show_ticket_form',$show_form); 
}

}
include(self::$path . "templates/style.php");  
if($show_form){

$path=self::$path . "templates/ticket-form.php";
$path=apply_filters('support_x_ticket_form_path',$path);
include_once($path);
}
return  ob_get_clean();
//var_dump($entries);
}    

public function wc_account_tab($items){
    
    
    $meta=$this->get_meta();
   if(!empty($meta['wc_tab']) && !empty($meta['tab_path']) && !empty($meta['tab_title'])){
    $count=count($items);
    if($count>2){
        $mid=floor($count/2);
    $a=array_slice($items,0,$mid);    
    $b=array_slice($items,$mid-1);
    $items=array_merge($a,array($meta['tab_path']=>$meta['tab_title']),$b);    
    }else{
 $items[$meta['tab_path']]=$meta['tab_title'];
    }
   } 
 
    return $items;   
}
public function wc_account_content(){

$user_id=get_current_user_id();
if(!empty($user_id)){ 
  $phone=get_user_meta( $user_id, 'billing_phone', true ); 
 // if(!empty($phone)){ 
      $atts=array('phone'=>$phone,'user_id'=>$user_id,'sortable'=>'true','pager'=>'true','limit'=>200);
     echo  $this->user_tickets_table($atts);
 // }  
}
}
public function add_endpoint(){
      $meta=$this->get_meta();
   if(!empty($meta['wc_tab']) && !empty($meta['tab_path'])){
  add_action( 'woocommerce_account_'.$meta['tab_path'].'_endpoint', array( $this, 'wc_account_content' ) );  
 add_rewrite_endpoint( $meta['tab_path'], EP_ROOT | EP_PAGES );
   }    
}
public function add_endpoint_flush(){
    $this->add_endpoint();
   
    flush_rewrite_rules();  
}
public function add_query_var($vars){
      $vars[] = 'support-tickets';
    return $vars;
}
 

   /**
  * admin_screen_message function.
  * 
  * @param mixed $message
  * @param mixed $level
  */
  public  function screen_msg( $message, $level = 'updated') {
  echo '<div class="'. esc_attr( $level ) .' fade below-h2 notice is-dismissible"><p>';
  echo $message ;
  echo '</p></div>';
  } 


    public function get_meta(){ 
        if(empty(self::$meta)){
              $option=apply_filters('crm_perks_support_x_options',self::$meta); 
              if(empty($option)){  
            $option=get_option(vx_support_x::$id.'_meta',true);
              }
      self::$meta=json_decode(self::de_crypt($option),true); 
              
    if(!empty(self::$meta['hs_key'])){
        self::$token=self::$meta['hs_key'];
    }     
        }
     $meta=is_array(self::$meta) ? self::$meta : array();    
      return $meta; 
    }
public static function update_meta($meta){
    $meta=vx_support_x::en_crypt( json_encode( $meta ) );
    $meta=apply_filters('crm_perks_support_x_options_update',$meta);
    if($meta){
    update_option(vx_support_x::$id.'_meta' , $meta);
    }
}
public function do_actions(){
     if(!is_object(self::$plugin) ){ $this->plugin_api(); }
      if(is_object(self::$plugin) && method_exists(self::$plugin,'valid_addons')){
       return self::$plugin->valid_addons();  
      }
    
   return false;   
  }
  
  /**
  * Returns true if the current page is an Feed pages. Returns false if not
  * 
  * @param mixed $page
  */
  public  function is_crm_page($page=""){
  if(empty($page)) {
  $page = vx_support_x::post("page");
  }
    $tab= vx_support_x::post('tab');
if($page == 'vxcf_leads' && in_array($tab,array('calls','sms'))){
    return true;
}
if( !class_exists( 'vxcf_form' )  && empty($tab)){
    return true;
}
  return false;
  }   
  

  /**
  * settings link
  * 
  * @param mixed $escaped
  */
  public  function link_to_settings( $tab='' ) {
  $q=array('page'=>'vx_support_x');
   if(!empty($tab)){
   $q['tab']=$tab; 
   }
  $url = admin_url('admin.php?'.http_build_query($q));
  
  return  $url;
  }
public function current_url(){
    global $wp;
    if(is_admin() && !empty($_SERVER['REQUEST_URI'])){
   $url_arr=parse_url($_SERVER['REQUEST_URI']);
$link=$url_arr['path'];
}else{
$link=site_url(add_query_arg(array(),$wp->request)).'/';
}
  return  $link;
}


  /**
  * deactivate
  * 
  * @param mixed $action
  */
  public function deactivate($action="deactivate"){ 
  do_action('plugin_status_'.self::$id,$action);
  }
  /**
  * activate plugin
  * 
  */
  public function activate(){
  
$this->plugin_api(true);   
do_action('plugin_status_'.self::$id,'activate');  
  }

public function process_form(){
if(!empty($_POST['vx_support_x_form'])){

    check_admin_referer('vx_nonce','vx_nonce');
    $this->start_plugin();
    $logged=false;
    $user=wp_get_current_user();
    $email=$user_id='';
    if(isset($user->user_email)){
    $email=$user->user_email;    
    $user_id=$user->ID;    
    $logged=true;
    }else if(!empty($_POST['email'])){
     $email=vx_support_x::post('email');   
    }
      $meta=$this->get_meta();
$enable_captcha=vx_support_x::post('captcha',$meta);
$is_valid=true;
$msg='';$class='error';
      //validate form
if( $enable_captcha == '' || ($enable_captcha == 'common' && !$logged)){
    $secret=$meta['cap_secret'];
  $google=array("response"=>$_REQUEST['g-recaptcha-response'],"remoteip"=>$_SERVER['REMOTE_ADDR'],"secret"=>$secret);
    $path="https://www.google.com/recaptcha/api/siteverify";
    $google_response = "true success";
  $google_response=$this->request($path,"post",$google);
 //  var_dump($google_response);
    if(!preg_match("/true/",$google_response)){ $is_valid=false; $msg=__('Wrong Captcha! Please try again','support-x'); }
} 
  $msg='';  
    $query=array();
    if(isset($_GET) && is_array($_GET)){
     $query=$_GET;   
    }
if(isset($_GET['conversation_id'])){
$query['conversation_id']=vx_support_x::post('conversation_id');    
}
//process form
$req=array('subject'=>vx_support_x::post('subject'),'body'=>sanitize_textarea_field(wp_unslash($_POST['body'])) );  
if(!empty($_POST['name'])){
   $n=explode(' ',vx_support_x::post('name'));
   if(!empty($n[0])){
  $req['first_name']=$n[0];     
   }
    if(!empty($n[1])){
  $req['last_name']=$n[1];     
    }
}else{
        $last_name=$first_name='';
    if(isset($user->user_lastname)){
     $last_name=$user->user_lastname;
    }
    if(isset($user->user_firstname)){
     $first_name=$user->user_firstname;   
    }
   if(empty($last_name) && !empty($first_name)){
       $last_name=$first_name;
   } 
   if(empty($last_name)){
       $last_name=$user->display_name;
   }    
$req['last_name']=$last_name;
$req['first_name']=$first_name;
$req['name']=trim($last_name.' '.$first_name);
}
$req=apply_filters('posted_data_'.vx_support_x::$id,$req,$user_id);   
  
  if($is_valid){
   if(empty($_GET['conversation_id']) && empty($req['subject'])){
    $msg=__('Subject is Required','support-x');  
   }else if(empty($req['body'])){
    $msg=__('Description is Required','support-x');  
   }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $msg=__('Invalid Email','support-x');  
   }
  if(!empty($msg)){
      $is_valid=false;
  }    
  }

  if($is_valid){
$api=$this->get_api($meta);  

 if(isset($_POST['vx_support_x_close'])){
 	$req['status'] = "closed";
 } 

$crm=vx_support_x::post('crm',$meta);
$hs_id=get_user_meta($user_id,'vx_'.$crm.'_id',true);
$req['hs_id']=$hs_id;
// WP-OK pass Status to the create_ticket function
$res=$api->create_ticket($meta,$email,$req);  
//var_dump($res); die();
 if(isset($res['id'])){
  $msg =__('Message sent Successfully','support-x');     
    $class='success';   
    if(isset($res['thread_id']) && $logged === true){
    $query['vx_thread']=$res['thread_id'];  
    } 
 }else{
     $msg =__('Message Not Sent.Please Try Later','support-x'); 
     if(isset($res['error']) && $logged === true && current_user_can('manage_options')){
      $msg =$res['error'];
     }     
    $class='error';
 } 
        

   
}


$query['vx_type']=$class;
$query['vx_msg']=empty($msg) ? __('Message Not Sent.Please Try Later','support-x') : $msg;
 $link=$this->current_url();  
 $link.='?'.http_build_query($query); 
 

//var_dump($query);
wp_redirect($link);
die();
} }
public function request($path,$method,$body=''){
 $args = array(
            'body' => $body,
            'headers'=> array(),
            'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
            'sslverify' => false,
            'timeout' => 20,
        );

       $response = wp_remote_request($path, $args);

return wp_remote_retrieve_body($response);
}


 

  /**
  * if plugin user is valid
  * 
  * @param mixed $update
  */
public function is_valid_user($update){
  return is_array($update) && isset($update['user']['user']) && $update['user']['user']!=""&& isset($update['user']['expires']);
  }     
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $arr
  */
public static function post($key, $arr="") {
  if($arr!=""){
  return isset($arr[$key])  ? $arr[$key] : "";
  }
  return isset($_REQUEST[$key]) ? self::clean($_REQUEST[$key]) : "";
}
public static function clean($var){
    if ( is_array( $var ) ) {
        return array_map('vx_support_x::clean', $var );
    } else {
        return  sanitize_text_field(wp_unslash($var));
    }
}
  /**
  * Get WP Encryption key
  * @return string Encryption key
  */
  public static  function get_key(){
  $k='Wezj%+l-x.4fNzx%hJ]FORKT5Ay1w,iczS=DZrp~H+ve2@1YnS;;g?_VTTWX~-|t';
  if(defined('AUTH_KEY')){
  $k=AUTH_KEY;
  }
  return substr($k,0,30);        
  }

    /**
  * Get time Offset 
  * 
  */
  public function time_offset(){
 $offset = (int) get_option('gmt_offset');
  return $offset*3600;
  } 
public function time_format(){
return get_option( 'date_format' ).' '.get_option( 'time_format' ); 
}  
public function pagination($total,$page,$per_page=25,$query_str=''){
$limit=$per_page;
 $links=2;
 $active_class='vx_active_page_link';
 $disabled_class='vx_disabled_page_link';
 $list_class='vx_paging_ul';
 
 if(!empty($query_str)){
  $query_str.='&';   
 }
 $query_str='?'.$query_str;  
 
    $last       = ceil( $total / $limit );
 
    $start      = ( ( $page - $links ) > 0 ) ? $page - $links : 1;
    $end        = ( ( $page + $links ) < $last ) ? $page + $links : $last;
 
    $html       = '<ul class="' . $list_class . '">';
 
    $class      = ( $page == 1 ) ? $disabled_class : "";
    $html       .= '<li class="' . $class . '"><a href="' .$query_str. 'vx_page=' . ( max($page - 1,1) ) . '">&laquo;</a></li>';
 
    if ( $start > 1 ) {
        $html   .= '<li><a href="' . $query_str . 'vx_page=1">1</a></li>';
        $html   .= '<li class="'.$disabled_class.'"><span>...</span></li>';
    }
 
    for ( $i = $start ; $i <= $end; $i++ ) {
        $class  = ( $page == $i ) ? $active_class : "";
        $html   .= '<li class="' . $class . '"><a href="' .$query_str. 'vx_page=' . $i . '">' . $i . '</a></li>';
    }
 
    if ( $end < $last ) {
        $html   .= '<li class="'.$disabled_class.'"><span class="vx_paging_span">...</span></li>';
        $html   .= '<li><a href="' . $query_str . 'vx_page=' . $last . '">' . $last . '</a></li>';
    }
 
    $class      = ( $page == $last ) ? $disabled_class : "";
    $html       .= '<li class="' . $class . '"><a href="' . $query_str . 'vx_page=' . ( $page + 1 ) . '">&raquo;</a></li>';
 
    $html       .= '</ul>';
    return $html;
}
 
  /**
  * Decrypts Values
  * @param array $info Salesforce encrypted API info 
  * @return array API settings
  */
  public static function de_crypt($info){
  $info=trim($info);
  if($info == "")
  return '';
  $str=base64_decode($info);
  $key=self::get_key();
      $decrypted_string='';
     if(function_exists("openssl_encrypt") && strpos($str,':')!==false ) {
$method='AES-256-CBC';
$arr = explode(':', $str);
 if(isset($arr[1]) && $arr[1]!=""){
 $decrypted_string=openssl_decrypt($arr[0],$method,$key,false, base64_decode($arr[1]));     
 }
 }else{
     $decrypted_string=$str;
 }
  return $decrypted_string;
  }   
  /**
  * Encrypts Values
  * @param  string $str 
  * @return string Encrypted Value
  */
  public static function en_crypt($str){
  $str=trim($str);
  if($str == "")
  return '';
  $key=self::get_key();
if(function_exists("openssl_encrypt")) {
$method='AES-256-CBC';
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
$enc_str=openssl_encrypt($str,$method, $key,false,$iv);
$enc_str.=":".base64_encode($iv);
  }else{
      $enc_str=$str;
  }
  $enc_str=base64_encode($enc_str);
  return $enc_str;
  }
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $key2
  * @param mixed $arr
  */
  public function post2($key,$key2, $arr="") {
  if(is_array($arr)){
  return isset($arr[$key][$key2])  ? $arr[$key][$key2] : "";
  }
  return isset($_REQUEST[$key][$key2]) ? $_REQUEST[$key][$key2] : "";
  }
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $key2
  * @param mixed $arr
  */
  public function post3($key,$key2,$key3, $arr="") {
  if(is_array($arr)){
  return isset($arr[$key][$key2][$key3])  ? $arr[$key][$key2][$key3] : "";
  }
  return isset($_REQUEST[$key][$key2][$key3]) ? $_REQUEST[$key][$key2][$key3] : "";
  }
  /**
  * get base url
  * 
  */
  public function get_base_url(){
  return plugin_dir_url(__FILE__);
  }
    /**
  * get plugin direcotry name
  * 
  */
  public function plugin_dir_name(){
  if(!empty($this->plugin_dir)){
  return $this->plugin_dir;
  }
  if(empty(self::$path)){
  self::$path=$this->get_base_path(); 
  }
  $this->plugin_dir=basename(self::$path);
  return $this->plugin_dir;
  }
  /**
  * get plugin slug
  *  
  */
  public static function get_slug(){
  return plugin_basename(__FILE__);
  }
  /**
  * get product url
  * 
  * @param mixed $update
  */
  public function get_url($update){
  $url=$this->url;
  if($this->post3('messages','url','html',$update) !=""){
  $url=$this->post3('messages','url','html',$update);     
  }
  return $url;
  }
  /**
  * Returns the physical path of the plugin's root folder
  * 
  */
  public function get_base_path(){
  return plugin_dir_path(__FILE__);
  }


    /**
  * get data object
  * 
  */
  public function get_data_object(){
  require_once(self::$path . "includes/data.php");     
  if(!is_object($this->data))
  $this->data=new vxcf_callcenter_data();
  return $this->data;
  }
public static function get_mime_type($tmp,$name=''){
    if(function_exists('mime_content_type')){
    return mime_content_type($tmp);    
    }
 $ext=substr($name,strrpos($name,'.')+1);
 $mimes=array('png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','pdf'=>'application/pdf','txt'=>'text/plain','csv'=>'text/plain');
$type='';
 if(isset($mimes[$ext])){
$type=$mimes[$ext];     
 }   
 return $type;
}


}

endif;


$vx_support_x=new vx_support_x();
$vx_support_x->instance();
$vx_all['vx_support_x']='vx_support_x';


