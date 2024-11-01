<?php
  if ( ! defined( 'ABSPATH' ) ) {
     exit;
 } 
     
         $color='closed'; $status_label=$ticket['status'];

    if(is_array($ticket['status'])){
$color=$ticket['status']['color'];
 $status_label=$ticket['status']['name'];       
    }

?>
<div>
<div class="vx_ticket_heading"><span class="vx_ticket_number"> # <?php echo $ticket['number']; ?> </span> <u><?php echo $ticket['subject']; ?></u> </div>
<div><span class="vx_badge vx_badge_<?php echo $color ?>"><?php echo ucfirst($status_label); ?></span> <?php echo sprintf(__(' By %s','support-x'),date($time_format,strtotime($ticket['createdAt'])+$offset));?>  

<a class="button" id="vx_support_back_btn" href="<?php echo $link_t ?>" style="float: right"><?php _e('Back to Tickets', 'support-x'); ?></a>
<div style="clear: both"></div>
</div>
</div>
<?php 
 $item_no=$total_items=$ticket['threadCount'];
    if(!empty($ticket['threads'])){
  foreach($ticket['threads'] as $item){ 
    
      $body=wp_kses_post($item['body']);
      if(empty($body)){
          continue;
      }
      $photo=$guest_photo;
    
    if(!empty($item['createdBy']['photoUrl'])){
    $photo=$item['createdBy']['photoUrl'];    
    }else{
        if($item['createdBy']['type'] == 'customer'){
       $photo=$user_photo;   
        }
    }  
    $name=__('You','support-x'); $reply_class='';
     if( !empty($item['createdBy']['type']) && $item['createdBy']['type'] == 'user'){
        $name='';
         if(isset($item['createdBy']['name'])){
        $name=$item['createdBy']['name'];     
         }else if(isset($item['createdBy']['firstName'])){
       $name=$item['createdBy']['firstName'].' '.$item['createdBy']['lastName'];
         }   
     $reply_class='vx_box_reply';   
     }else if(isset($_GET['vx_thread']) && $_GET['vx_thread'] == $item['id']){
      $reply_class='vx_box_reply_me';   
     }
     $time=date($time_format,strtotime($item['createdAt'])+$offset);
   

      ?>
   <div class="vx_ticket_box <?php echo $reply_class; ?>">
   <div class="vx_ticket_box_title">
   <div style="float: left;"><span class="vx_user_img"><img src="<?php echo $photo; ?>"></span> <span class="vx_ticket_title"><?php echo $name ?></span></div>
   <div class="vx_box_time"><?php echo $time ?></div>
   <div style="clear: both;"></div>
   </div>
   <div class="vx_ticket_box_body">
   <?php
   echo $body;
  
      if(isset($item['attachments']) && is_array($item['attachments']) && count($item['attachments'])>0){
          
  ?>
 <div class="vx_title_small"><?php _e('Attachments','support-x') ?></div> 
  <?php
 foreach($item['attachments'] as $k=>$v){
     ?>
 <a class="vx_attachment" href="<?php echo $v['url'] ?>" target="_blank"><?php echo $v['fileName'] ?></a>    
     <?php
 }
   }
   ?>
   </div>
   <div class="vx_ticket_box_footer"><?php echo sprintf(__('%s of %s','support-x'),'<b>'.$item_no.'</b>',$total_items); ?></div>
   
   </div>   
      <?php
      $item_no--;
  }      
    }
      
?>




