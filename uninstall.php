<?php
/**
 * Uninstall
 */
 if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
$path=plugin_dir_path(__FILE__);
include_once($path . "support-x.php");
$settings=$vx_support_x->get_meta();
if(!empty($settings['plugin_data'])){
$options=array('custom_fields','boxes','tags','meta','freshdesk_agents','zendesk_agents');
foreach($options as $v){
    delete_option(vx_support_x::$id.'_'.$v);
}
}
