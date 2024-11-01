<?php
  if ( ! defined( 'ABSPATH' ) ) {
     exit;
 }  
 $total_cols=0;
?>

 <table <?php echo $class.' '.$css ?> cellspacing="0" <?php echo $table_id ?>>
  <thead>
  <tr>
  <?php
      if(!empty($atts['number-col'])){
          $total_cols++;
  ?>
<th <?php if(!empty($atts['number-col-width'])){ echo 'style="width:'.$atts['number-col-width'].' "'; } ?>><?php _e('#','support-x'); ?></th>
<?php
      }

  foreach($fields as $field){  
$total_cols++;
?>
  <th <?php if($field['name'] == 'type'){echo 'style="width:50px"';} ?>><?php echo $field['label'] ?></th>
<?php
  }
?>

  </tr>
  </thead>

  <tfoot>
  <tr>
  <?php
      if(!empty($atts['number-col'])){
  ?>
<th <?php if(!empty($atts['number-col-width'])){ echo 'style="width:'.$atts['number-col-width'].' "'; } ?>><?php _e('#','support-x'); ?></th>
<?php
      }
  foreach($fields as $field){  
?>
  <th><?php echo $field['label'] ?></th>
<?php
  }
?>

  </tr>
  
  </tfoot>
  <tbody>
  <?php
  if(is_array($leads) && !empty($leads)){
  $sno=0;
      foreach($leads as $lead){
  $sno++;
  ?>
  <tr>
  <?php
      if(!empty($atts['number-col'])){
  ?>
  <td class="vx_td_center"><?php echo $sno ?></td>
    <?php
      }
foreach($fields as $field){  

$field_label='';
if(isset($lead[$field['name']])){
 $field_label=$lead[$field['name']];   

 if($field['name'] == 'createdAt'){
   if(!empty($lead['modifiedAt'])){
   $field_label=$lead['modifiedAt'];    
   }  
   $field_label=strtotime($field_label)+$offset;
$field_label= date_i18n($time_format,$field_label);   
}else if($field['name'] == 'subject'){
$field_label='<a href="'.$link_q.'conversation_id='.$lead['id'].'">'.esc_html($field_label).'</a>';  
} }
$class=''; if(in_array($field['name'],array('status','threadCount','number','createdAt') ) ){ $class='class="vx_td_center"'; }
?>
<td <?php echo $class; ?>><?php
if($field['name'] == 'threadCount'){
     $count=(int)$lead['threadCount'];
?><span class="vx_badge vx_badge_grey"><?php echo $count ?></span>
  <?php
}else if($field['name'] == 'status'){
    $color='closed'; $status_label=$field_label;
    if(is_array($field_label)){
$color=$field_label['color'];
 $status_label=$field_label['name'];       
    }

 ?>
<span class="vx_badge vx_badge_<?php echo $color ?>"><?php echo ucfirst($status_label) ?></span>
 <?php   
}else{
 echo $field_label; 
}
 ?></td>
  <?php
  }
  ?>

  </tr>
  <?php
  }
  }
  else {  
  ?>
  <tr>
    <td colspan="<?php echo $total_cols ?>">
        <?php _e("No Record(s) Found", 'support-x'); ?>
    </td>
  </tr>
  <?php
  }
  ?>
  </tbody>
  </table>




