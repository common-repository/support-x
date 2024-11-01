<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'vx_crmperks_notice_support' )):
class vx_crmperks_notice_support{
public $plugin_url="https://www.crmperks.com";

public function __construct(){
add_filter('admin_tabs_vx_support_x', array($this, 'add_section_cf'),99);
add_action('add_section_vx_support_x', array($this, 'free_plugins_notice'),99);
add_action('admin_tabs_section_vx_support_x', array($this, 'notice'),99);
add_filter( 'plugin_row_meta', array( $this , 'pro_link' ), 10, 2 );
}
public function add_section_cf($tabs){ 
$tabs["vxc_notice"]=__('Go Premium','crmperks-support');
return $tabs;
}

public function notice(){
$plugin_url=$this->plugin_url.'?vx_product=crmperks-support';
?>
 <div class="updated below-h2" style="border-left-color: #1192C1; margin: 30px 20px 30px 0px">
<h2>Premium Version</h2>
<ul style="list-style-type: square; padding-left: 30px;">
<li>Ticket Attachments</li>
<li>Phone Number field</li>
<li>Custom Fields</li>
<li>"Select Ticket Topic" field</li>
</ul>
<p>By purchasing the premium version of the plugin you will get access to advanced marketing features and you will get one year of free updates & support</p>
<p>
<a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a>
</p>
</div>
<?php
}
public function pro_link($links,$file){
    $slug=vx_support_x::get_slug();
    if($file == $slug){
    $url=$this->plugin_url.'?vx_product=crmperks-support';
    $links[]='<a href="'.$url.'"><b>Go Premium</b></a>';
    }
   return $links; 
}
public function free_plugins_notice(){
?>
<div class="updated below-h2" style="border: 1px solid  #1192C1; border-left-width: 6px; padding: 5px 12px;">
<h3>Our Other Free Plugins</h3>
<p><b><a href="https://wordpress.org/plugins/crm-perks-forms/" target="_blank">CRM Forms</a></b> is lightweight and highly optimized contact form builder with Poups and floating buttons.</p>
<p><b><a href="https://wordpress.org/plugins/contact-form-entries/" target="_blank">Contact Form Entries</a></b> saves contact form submissions from all popular contact forms(contact form 7 , crmperks forms, ninja forms, Gravity forms etc) into database.</p>
<p><b><a href="https://wordpress.org/plugins/woo-salesforce-plugin-crm-perks/" target="_blank">WooCommerce Salesforce Plugin</a></b> allows you to quickly integrate WooCommerce Orders with Salesforce CRM.</p>
<p><b><a href="https://wordpress.org/plugins/gf-freshdesk/" target="_blank">Gravity Forms FreshDesk Plugin</a></b> allows you to quickly integrate Gravity Forms with FreshDesk CRM.</p>
</div>
<?php    
}

}
new vx_crmperks_notice_support();
endif;
