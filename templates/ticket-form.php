<?php
  if ( ! defined( 'ABSPATH' ) ) {
     exit;
 } 
 
     if(isset($_GET['vx_type']) && isset($_GET['vx_msg'])){
         $alert_msg=$_GET['vx_msg'];
?>
<div class="vx_alert_msg vx_alert_msg_<?php echo $_GET['vx_type'] ?>"><?php echo $alert_msg ?></div>
<?php
    } 

if($single_form !== true){ 
?>
  <div class="vx_ticket_heading"><u>
<?php
      if(isset($ticket['number'])){
   echo sprintf(__('Reply to ticket #%s', 'support-x'),$ticket['number']);       
      }else{
   _e('Create New Ticket', 'support-x');      
      }
  ?>  </u>
  </div>
  <?php
}
  ?>
  <form method="post" enctype="multipart/form-data" onsubmit="vx_support_submit(this)" class="vx_support_form">
  <?php
  if(!$logged){ 
      ?>
      <div class="vx_form_control"><label class="vx_field_label" for="vx_name"> <?php _e('Name', 'support-x'); ?> <span class="required">*</span></label>
<div class="vx_field_input"><input type="text" class="vx_input" id="vx_name" name="name" placeholder=" <?php _e('Enter Name', 'support-x'); ?>" required></div>
</div>
 <div class="vx_form_control"><label class="vx_field_label" for="vx_email"> <?php _e('Email', 'support-x'); ?> <span class="required">*</span></label>
<div class="vx_field_input"><input type="text" class="vx_input" id="vx_email" name="email" placeholder=" <?php _e('Enter Email', 'support-x'); ?>" required></div>
</div>
<?php

  }
  
      if(!isset($ticket['number'])){
  ?>
<div class="vx_form_control"><label class="vx_field_label" for="vx_subject"> <?php _e('Subject', 'support-x'); ?> <span class="required">*</span></label>
<div class="vx_field_input"><input type="text" class="vx_input" id="vx_subject" name="subject" placeholder=" <?php _e('Enter Subject', 'support-x'); ?>" required></div>
</div>
<?php 
}

?>
<div class="vx_form_control"><label class="vx_field_label" for="vx_msg"> <?php _e('Description', 'support-x'); ?> <span class="required">*</span></label>
<div class="vx_field_input"><textarea class="vx_input" rows="10" id="vx_msg" name="body" placeholder="<?php _e('Enter Message', 'support-x'); ?>" required></textarea></div>
</div>
<?php
do_action('from_'.vx_support_x::$id,$ticket,$meta,$user_id);
$cap_key='';
if(!empty($meta['cap_key'])){
$cap_key=$meta['cap_key'];
}
    $enable_cap=vx_support_x::post('captcha',$meta);
    $display_cap=false; 
if($enable_cap == '' || ($enable_cap == 'common' && !$logged)){
    wp_enqueue_script( 'vx-google-captcha' );
    $display_cap=true;
    ?>
<div class="vx_form_control">
<div class="g-recaptcha" data-sitekey="<?php echo $cap_key ?>"></div>
</div>
  <?php
}

if($display_cap && empty($cap_key)){
?>
<div style="border: 2px solid #b94a48; padding: 5px 10px; font-size: 14px; font-weight: bold; color:#b94a48"><?php _e('Please Enter Google reCaptcha key and secret in settings', 'support-x'); ?></div>
<?php    
}else{
    wp_nonce_field('vx_nonce','vx_nonce'); 
 if(isset($ticket['number']) && in_array($crm,array('helpscout')) ){
  ?>
<div class="vx_form_control">
 <input type="checkbox" name="vx_support_x_close" value="close"><label for="vx_support_x_close"> <?php _e('Close ticket', 'support-x'); ?></label> 
</div>
<?php
 }
?>
 <input type="hidden" name="vx_support_x_form" value="submit">
<button class="button vx_support_submit_btn" type="submit">
<span class="vx_reg_ok"><?php _e('Submit', 'support-x'); ?></span>
<span class="vx_reg_proc" style="display: none"><?php _e('Submiting ... ', 'support-x'); ?></span>
</button>
<?php
}
?>
</form>

<script type="text/javascript">
function vx_support_submit(el){
    var btn=jQuery(el).find('.vx_support_submit_btn');
    btn.prop('disabled',true);
    btn.find('.vx_reg_ok').hide();
    btn.find('.vx_reg_proc').show();
}
</script>



