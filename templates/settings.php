<?php
  if ( ! defined( 'ABSPATH' ) ) {
     exit;
 } 
if(isset($_GET['crm'])){
 $meta['crm']=vx_support_x::post('crm');   
}
$crm=vx_support_x::post('crm',$meta);
     ?>  
      <script type="text/javascript">
  jQuery(document).ready(function($){

    $(document).on('click','.vx_toggle_key',function(e){
  e.preventDefault();  
  var key=$(this).parents(".vx_tr").find(".crm_text"); 
  if($(this).hasClass('vx_hidden')){
  $(this).text('<?php _e('Show Key','support-x') ?>');  
  $(this).removeClass('vx_hidden');
  key.attr('type','password');  
  }else{
  $(this).text('<?php _e('Hide Key','support-x') ?>');  
  $(this).addClass('vx_hidden');
  key.attr('type','text');  
  }
  });

    

 $('#vx_refresh_boxes').click(function(e){
  e.preventDefault();  
  var btn=$(this);
  var action=$(this).data('id');
var data = $('#vx_form').serializeArray();
data.push({name: 'action', value: 'vx_support_x_get_mailboxes'});

  button_state_vx("ajax",btn);
  $.post(ajaxurl,data,function(res){
  var re=$.parseJSON(res);
  button_state_vx("ok",btn);  
  if(re.status){
 if(re.status == "ok"){
  $.each(re.data,function(k,v){
   if($("#"+k).length){
   $("#"+k).html(v);    
   }   
  })   
 }else{
  if(re.error && re.error!=""){
      alert(re.error);
  }   
 }
  }   

  });   
  });
  $(document).on('click','.vx_revoke',function(e){
  
  if(!confirm('<?php _e('Notification - Remove Connection?','support-x'); ?>')){
  e.preventDefault();   
  }
  });
  
$('#vx_crm').change(function(){
 var id=$(this).val();
 window.location=window.location+'&crm='+id;   
});


 

  });
     function button_state_vx(state,button){
var ok=button.find('.reg_ok');
var proc=button.find('.reg_proc');
     if(state == "ajax"){
          button.attr({'disabled':'disabled'});
ok.hide();
proc.show();
     }else{
         button.removeAttr('disabled');
   ok.show();
proc.hide();      
     }
}
  </script> 
  
<div class="vx_wrap">

<form action="" method="post" id="vx_form">
  <?php wp_nonce_field("vx_nonce") ?>

  <table class="form-table">
  
      <tr>
  <th scope="row"><label for="vx_crm">
  <?php _e('Select Ticket System', 'support-x'); ?>
  </label>
  </th>
  <td>
<select name="meta[crm]" id="vx_crm" style="width: 100%;">
<option value=""></option>
<?php
    foreach($crms as $k=>$v){
    $sel="";
  if($crm == $k)
  $sel="selected='selected'";
  echo "<option value='".$k."' $sel>".$v."</option>";       
    }
?>
</select>
  </td>
  </tr>
 <?php
      switch($crm){
case'helpscout':
$link=$this->link_to_settings();
$admin_link=admin_url('admin.php'); 
?> 
  <tr>
  <th scope="row"><label for="vx_hs_key">
  <?php _e('HelpScout APP ID ', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[help_key]" value="<?php echo vx_support_x::post('help_key',$meta); ?>" style="width: 100%;" id="vx_<?php echo $crm ?>_key">
      <div class="howto">
  <ol>
  <li><?php _e('In HelpScout go to Your Profile -> My Apps -> Create New App','support-x'); ?></li>
  <li><?php _e('Enter App Name(eg. My App)','support-x'); ?></li>
  <li><?php echo sprintf(__('Enter %s  in Redirect URI','support-x'),'<code>'.$admin_link.'?support_x_action=get_code</code>'); ?>
  </li>
<li><?php _e('Save Application','support-x'); ?></li>
<li><?php echo __('Copy API Key and Secret','support-x'); ?></li>
<li><?php echo __('Save Changes then click Login with HelpScout button','support-x'); ?></li>
   </ol>
  </div>
  </td>
  </tr>
  
   <tr>
  <th scope="row"><label for="vx_hs_secret">
  <?php _e('HelpScout APP Secret', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[help_secret]" value="<?php echo vx_support_x::post('help_secret',$meta); ?>" style="width: 100%;" id="vx_<?php echo $crm ?>_secret">
  </td>
  </tr>
  
<?php
      if(!empty($meta['help_key']) && !empty($meta['help_secret'])){
  ?>  
    <tr>
  <th scope="row"><label for="vx_hs_key">
  <?php _e('HelpScout Access', 'support-x'); ?>
  </label>
  </th>
  <td>
<?php
if(!empty($meta['help_token']) ){ //&& !empty($meta['help_token'])
?>
  <div style="padding: 8px; margin-left: 0; margin-bottom: 10px;" class="vx_green updated below-h2"><i class="fa fa-check"></i> <?php
  echo sprintf(__("Authorized Connection to %s on %s",'crmperks-support'),'<code>HelpScout</code>',date('F d, Y h:i:s A',$meta['_time']));
  ?></div>
   <button type="submit" class="button button-secondary vx_revoke" name="vx_remove_help_connection"> <?php _e('Revoke Access','crmperks-support'); ?></button>  
   <button type="submit" class="button button-secondary" name="vx_test_help_connection"> <?php _e("Test Connection",'crmperks-support'); ?></button>
<?php
}else{
if(!empty($meta['help_error']) ){
    ?>
<div class="error below-h2" style="padding: 10px; margin-left: 0; margin-bottom: 10px;"><?php  echo $meta['help_error']; ?></div>    
<?php } ?>  
 <a class="button button-default button-hero" data-id="<?php echo esc_html($meta['help_key']) ?>" href="https://secure.helpscout.net/authentication/authorizeClientApplication?state=<?php echo urlencode($admin_link."?support_x_action=get_code");?>&client_id=<?php echo $meta['help_key']; ?>" title="<?php _e('Login with HelpScout','crmperks-support'); ?>" > <i class="fa fa-lock"></i> <?php _e('Login with HelpScout','crmperks-support'); ?></a>
<?php
      }
 ?>
  </td>
  </tr>
<?php
      }else{
?>
   <tr>
  <th scope="row"><label for="vx_hs_key">
  <?php _e('HelpScout Access', 'support-x'); ?>
  </label>
  </th>
  <td>
  <strong><?php _e('Please Save HelpScout APP ID and Secret First','crmperks-support'); ?></strong>
  </td>
  </tr>
<?php          
      } ?>
 <tr>
  <th scope="row"><label for="vx_hs_error_email">
  <?php _e('Email for Erorr Notice', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[help_error_email]" value="<?php echo vx_support_x::post('help_error_email',$meta); ?>" style="width: 100%;"  id="vx_hs_error_email">
  </td>
  </tr>
<?php      
break;

case'zendesk':
  ?> 
    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_domain">
  <?php _e('ZenDesk URL', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[zen_url]" value="<?php echo vx_support_x::post('zen_url',$meta); ?>" style="width: 100%;" placeholder="https://example.zendesk.com" id="vx_<?php echo $crm ?>_domain">
  
  </td>
  </tr>
  
      <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_email">
  <?php _e('ZenDesk Email', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[zen_email]" value="<?php echo vx_support_x::post('zen_email',$meta); ?>" style="width: 100%;"  id="vx_<?php echo $crm ?>_email">
  
  </td>
  </tr>


    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_token">
  <?php _e('ZenDesk API Key', 'support-x'); ?>
  </label>
  </th>
  <td>
    <div style="display: table; width: 100%;" class="vx_tr">
  <div style="display: table-cell; width: 85%;">
  <input type="password" style="width: 100%;" class="crm_text" id="vx_<?php echo $crm ?>_token" name="meta[zen_token]" placeholder="<?php _e('ZenDesk API Key','support-x') ?>" value="<?php echo vx_support_x::post('zen_token',$meta); ?>">
  </div>
  <div style="display: table-cell;">
  <a href="#" style="margin: 0 0 0 10px; vertical-align: baseline; text-align: center; width: 110px" class="button vx_toggle_key" title="<?php _e('Toggl Key','support-x'); ?>">Show Key</a>
  </div></div>
  
  </td>
  </tr>
<?php
break;

case'freshdesk':
  ?> 
    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_domain">
  <?php _e('FreshDesk URL', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[freshdesk_url]" value="<?php echo vx_support_x::post('freshdesk_url',$meta); ?>" style="width: 100%;" placeholder="https://example.freshdesk.com" id="vx_<?php echo $crm ?>_domain">
  
  </td>
  </tr>

  
    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_token">
  <?php _e('FreshDesk API Key', 'support-x'); ?>
  </label>
  </th>
  <td>
    <div style="display: table; width: 100%;" class="vx_tr">
  <div style="display: table-cell; width: 85%;">
  <input type="password" style="width: 100%;" class="crm_text" id="vx_<?php echo $crm ?>_token" name="meta[freshdesk_token]" placeholder="<?php _e('FreshDesk API Key','support-x') ?>" value="<?php echo vx_support_x::post('freshdesk_token',$meta); ?>">
  </div>
  <div style="display: table-cell;">
  <a href="#" style="margin: 0 0 0 10px; vertical-align: baseline; text-align: center; width: 110px" class="button vx_toggle_key" title="<?php _e('Toggl Key','support-x'); ?>">Show Key</a>
  </div></div>
  
  </td>
  </tr>
  
      <tr>
  <th scope="row"><label for="vx_status">
  <?php _e('Ticket Status', 'support-x'); ?>
  </label>
  </th>
  <td>
  <select name="meta[freshdesk_status]" style="width: 100%;"  id="vx_status">
  <?php
      $status_arr=$api->get_status_list();

  foreach($status_arr as $k=>$v){
       $sel="";
  if(vx_support_x::post('freshdesk_status',$meta) == $k)
  $sel="selected='selected'";
  echo "<option value='".$k."' $sel>".$v."</option>";    
  }    
  ?>
  </select>
  </td>
  </tr> 
  
        <tr>
  <th scope="row"><label for="vx_priority">
  <?php _e('Ticket Priority', 'support-x'); ?>
  </label>
  </th>
  <td>
  <select name="meta[freshdesk_priority]" style="width: 100%;"  id="vx_priority">
  <?php
  $status_arr=$api->get_priority_list();
  
  foreach($status_arr as $k=>$v){
       $sel="";
  if(vx_support_x::post('freshdesk_priority',$meta) == $k)
  $sel="selected='selected'";
  echo "<option value='".$k."' $sel>".$v."</option>";    
  }    
  ?>
  </select>
  </td>
  </tr>
<?php
break;

case'desk':
  ?> 
    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_domain">
  <?php _e('Desk.com URL', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[desk_url]" value="<?php echo vx_support_x::post('desk_url',$meta); ?>" style="width: 100%;" placeholder="https://example.desk.com" id="vx_<?php echo $crm ?>_domain">
  
  </td>
  </tr>

    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_email">
  <?php _e('Desk.com Email', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[desk_email]" value="<?php echo vx_support_x::post('desk_email',$meta); ?>" style="width: 100%;" id="vx_<?php echo $crm ?>_email">
  
  </td>
  </tr>
  
    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_token">
  <?php _e('Desk.com Password', 'support-x'); ?>
  </label>
  </th>
  <td>
    <div style="display: table; width: 100%;" class="vx_tr">
  <div style="display: table-cell; width: 85%;">
  <input type="password" style="width: 100%;" class="crm_text" id="vx_<?php echo $crm ?>_token" name="meta[desk_token]" placeholder="<?php _e('Desk.com Password','support-x') ?>" value="<?php echo vx_support_x::post('desk_token',$meta); ?>">
  </div>
  <div style="display: table-cell;">
  <a href="#" style="margin: 0 0 0 10px; vertical-align: baseline; text-align: center; width: 110px" class="button vx_toggle_key" title="<?php _e('Toggl Key','support-x'); ?>">Show Key</a>
  </div></div>
  
  </td>
  </tr>
  
<?php
break;

case'teamwork':
  ?> 
    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_domain">
  <?php _e('TeamWork Desk URL', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[teamwork_url]" value="<?php echo vx_support_x::post('teamwork_url',$meta); ?>" style="width: 100%;" placeholder="https://example.teamwork.com" id="vx_<?php echo $crm ?>_domain">
  
  </td>
  </tr>
  
    <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_token">
  <?php _e('TeamWork Desk API Key', 'support-x'); ?>
  </label>
  </th>
  <td>
    <div style="display: table; width: 100%;" class="vx_tr">
  <div style="display: table-cell; width: 85%;">
  <input type="password" style="width: 100%;" class="crm_text" id="vx_<?php echo $crm ?>_token" name="meta[teamwork_token]" placeholder="<?php _e('TeamWork Desk API Key','support-x') ?>" value="<?php echo vx_support_x::post('teamwork_token',$meta); ?>">
  </div>
  <div style="display: table-cell;">
  <a href="#" style="margin: 0 0 0 10px; vertical-align: baseline; text-align: center; width: 110px" class="button vx_toggle_key" title="<?php _e('Toggl Key','support-x'); ?>">Show Key</a>
  </div></div>
  
  </td>
  </tr>
  
<?php
break;
}
if(in_array($crm,array('teamwork','desk','helpscout'))){
?>
      <tr>
  <th scope="row"><label for="vx_<?php echo $crm ?>_mailbox">
  <?php _e('Select Mailbox', 'support-x'); ?>
  </label>
  </th>
  <td>
     <div style="display: table; width: 100%;" class="vx_tr">
  <div style="display: table-cell; width: 80%;">
  
  <select name="meta[<?php echo $crm ?>_mailbox]" id="vx_<?php echo $crm ?>_mailbox" style="width: 100%;">
<?php
    $crm_boxes=vx_support_x::post($crm,$boxes);
    $sel_box=vx_support_x::post($crm.'_mailbox',$meta);
    if(is_array($crm_boxes) && count($crm_boxes)>0){
    foreach($crm_boxes as $k=>$v){
    $sel="";
  if($sel_box == $k)
  $sel="selected='selected'";
  echo "<option value='".$k."' $sel>".$v."</option>";       
    }
    }
?>
</select>

  </div><div style="display: table-cell;">
             <button class="button" id="vx_refresh_boxes" autocomplete="off" style="margin: 0 0 0 10px; vertical-align: baseline; text-align: center;" title="<?php _e('Refresh List','support-x'); ?>">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php _e('Refresh Mailboxes','support-x') ?></span>
  <span class="reg_proc" style="display: none;"><i class="fa fa-refresh fa-spin"></i> <?php _e('Refreshing...','support-x') ?></span>
  </button>

  </div></div>
<p><?php _e('If list is empty then please refresh it', 'support-x'); ?></p>
  
  </td>
  </tr>
<?php
  }    
?> 
  
<?php
do_action('settings_fields_'.vx_support_x::$id,$crm,$meta,$api);

      if(class_exists('WooCommerce')){
         $tab_title=vx_support_x::post('tab_title',$meta);
         $tab_path=vx_support_x::post('tab_path',$meta);
         if(empty($tab_title)){
             $tab_title='Tickets';
         }
           if(empty($tab_path)){
             $tab_path='tickets';
         }
  ?>

      <tr>
  <th scope="row"><label for="vx_wc_tab">
  <?php _e('WooCommerce Integration', 'support-x'); ?>
  </label>
  </th>
  <td>
<label for="vx_wc_tab"><input type="checkbox" name="meta[wc_tab]" value="yes" <?php if(vx_support_x::post('wc_tab',$meta) == "yes"){echo 'checked="checked"';} ?> id="vx_wc_tab"><?php _e('Show customer tickets tab in WooCommerce account section','support-x'); ?></label>
  </td>
  </tr>

       <tr>
  <th scope="row"><label for="vx_tab_title">
  <?php _e('Tab Title', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[tab_title]" value="<?php echo $tab_title; ?>" style="width: 100%;"  id="vx_tab_title">
<p><?php _e('Tab will be added to WooCommece account section', 'support-x'); ?></p>
  
  </td>
  </tr> 

  <tr>
  <th scope="row"><label for="vx_tab_path">
  <?php _e('Tab Path', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[tab_path]" value="<?php echo $tab_path; ?>" style="width: 100%;"  id="vx_tab_path">
<p><code><?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ) ?>TAB-PATH/</code></p>
  
  </td>
  </tr>
    <?php
      }
  ?>
  
      <tr><td colspan="2"><h2><?php _e('Google reCaptcha Settings', 'support-x'); ?></h2>
<p><?php _e('reCAPTCHA is a free Google service to protect your website from spam and abuse', 'support-x'); ?></p>

</td></tr>

    <tr>
  <th scope="row"><label for="vx_captcha">
  <?php _e('Enable Captcha', 'support-x'); ?>
  </label>
  </th>
  <td>
  <select name="meta[captcha]" style="width: 100%;"  id="vx_captcha">
  <?php
      $tags_arr=array(''=>__('Display captcha to both logged in users and common visitors','support-x'),'common'=>__('Display captcha to only common visitors','support-x'));
//''=>__('Do not display Captcha (Spam Honeypot)','support-x')
  foreach($tags_arr as $k=>$v){
       $sel="";
  if(vx_support_x::post('captcha',$meta) == $k)
  $sel="selected='selected'";
  echo "<option value='".$k."' $sel>".$v."</option>";    
  }    
  ?>
  </select>
<p><?php _e('When Captcha should be displayed', 'support-x'); ?> </p>
  
  </td>
  </tr> 
  
         <tr>
  <th scope="row"><label for="vx_cap_key">
  <?php _e('reCaptcha Key', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[cap_key]" value="<?php echo vx_support_x::post('cap_key',$meta); ?>" style="width: 100%;"  id="vx_cap_key">
<p><?php echo sprintf(__('You can manage your reCaptcha keys %shere%s', 'support-x'),'<a href="https://www.google.com/recaptcha/admin" target="_blank">','</a>'); ?></p>
  
  </td>
  </tr> 
  
          <tr>
  <th scope="row"><label for="vx_cap_secret">
  <?php _e('reCaptcha Secret', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[cap_secret]" value="<?php echo vx_support_x::post('cap_secret',$meta); ?>" style="width: 100%;"  id="vx_cap_secret">
  
  </td>
  </tr> 
 <?php
if($crm == 'helpscout'){
  ?> 
  <tr><td colspan="2"><h2><?php _e('Help Scout Custom APP', 'support-x'); ?></h2>
<p><?php _e('When you view a ticket in help scout, Help Scout APP brings the customer data you need directly into the customer sidebar.In Help Scout go to Manage -> Apps -> Build Custom APP. Enter App name, select "Dynamic Content" as App type. Enter callback url and secret key from below settings. Click Save', 'support-x'); ?></p>

</td></tr>
         <tr>
  <th scope="row"><label for="vx_callback">
  <?php _e('Callback URL', 'support-x'); ?>
  </label>
  </th>
  <td>
<code><?php echo admin_url( 'admin-ajax.php' ).'?action=vx_helpscout_app'; ?></code>
  
  </td>
  </tr>
   <tr>
  <th scope="row"><label for="vx_secret">
  <?php _e('Secret Key', 'support-x'); ?>
  </label>
  </th>
  <td>
  <input type="text" name="meta[secret_key]" value="<?php echo vx_support_x::post('secret_key',$meta); ?>" style="width: 100%;"  id="vx_secret">
  </td>
  </tr>
  <?php
      }
   ?> 
    <tr>
  <th scope="row"><label for="vx_plugin_data">
  <?php _e("Plugin Data", 'support-x'); ?>
  </label>
  </th>
  <td>
<label for="vx_plugin_data"><input type="checkbox" name="meta[plugin_data]" value="yes" <?php if(vx_support_x::post('plugin_data',$meta) == "yes"){echo 'checked="checked"';} ?> id="vx_plugin_data"><?php _e('On deleting this plugin remove all of its data','support-x'); ?></label>
  </td>
  </tr>

  
  </table>
  
   <p class="submit">
   <button type="submit" value="save" class="button-primary" title="<?php _e('Save Changes','support-x'); ?>" name="save"><?php _e('Save Changes','support-x'); ?></button>
  <input type="hidden" name="vx_meta" value="1"> 
 </p>
 
  </form>
<table class="form-table">
     <tr><td colspan="2"><h2><?php _e('Short Codes', 'support-x'); ?></h2></td></tr>
  <tr><th>[crm-perks-tickets]</th>
  <td>
  <p><strong><?php _e('Display all tickets related to a wordpress user', 'support-x'); ?></strong></p>
  <p><code>user_id</code><?php _e('ID of wordpress user , default=logged in user id', 'support-x'); ?></p>
  <p><code>email</code><?php _e('Email of wordpress user', 'support-x'); ?></p>
  <p><code>font-size</code><?php _e('Font Size of Tickets table', 'support-x'); ?></p>
  <p><code>class</code><?php _e('Class Name of Tickets table', 'support-x'); ?></p>
  <p><code>id</code><?php _e('Id of Tickets table', 'support-x'); ?></p>
  <p><code>number-col</code><?php _e('set it to number-col="true" , if you want to display serial number column in Tickets table', 'support-x'); ?></p>
  <p><code>number-col-width</code><?php _e('Width of serial number column for example number-col-width="30"', 'support-x'); ?></p>
  <p><code>disable-new-tickets</code><?php _e('Disable creating new tickets for example disable-new-tickets="1"', 'support-x'); ?></p>
  <p><?php _e('[crm-perks-tickets] will show all tickets of user currently logged in wordpress. [crm-perks-tickets user_id="1"] will display tickets of wp user id "1"', 'support-x'); ?></p>
  </td></tr> 
  
    <tr><th>[crm-perks-form]</th>
  <td>
  <p><strong><?php _e('Display Create a Ticket Form', 'support-x'); ?></strong></p>
  <p><?php _e('for example [crm-perks-form] ', 'support-x'); ?></p>
  </td></tr> 
    
</table>
   <?php
  do_action('add_section_'.vx_support_x::$id);
  ?>
  </div>



