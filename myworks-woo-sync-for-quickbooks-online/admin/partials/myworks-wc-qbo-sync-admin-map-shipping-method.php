<?php
if ( ! defined( 'ABSPATH' ) )
     exit;

global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

/*
if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}
*/

$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=shipping-method';

$wc_sh_methods = WC()->shipping->load_shipping_methods();
//$MSQS_QL->_p($wc_sh_methods);

# New
if($MSQS_QL->use_new_qbo_local_data('class') && $MSQS_QL->get_option('mw_wc_qbo_sync_app_setting_qbo_classes_data_fetched') != 'true'){
	# Fetch and save new QBO classes into DB
	$MSQS_QL->save_all_classes();
	update_option('mw_wc_qbo_sync_app_setting_qbo_classes_data_fetched','true',false);
}

if (! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_shipping_method', 'map_wc_qbo_shipping_method' ) ) {
	$item_ids = array();
	foreach ($_POST as $key=>$value){
		if ($MSQS_QL->start_with($key, "map_shipping_method_")){
			$id = str_replace("map_shipping_method_", "", $key);
			$id = trim($id);
			if($id!='' && is_array($wc_sh_methods) && isset($wc_sh_methods[$id])){ //&& (int) $value
				$item_ids[$id] = (int) $value;
			}
		}
	}
	//$MSQS_QL->_p($item_ids);die;
	if(count($item_ids)){
		foreach ($item_ids as $key=>$value){
			$save_data = array();			
			$save_data['qbo_product_id'] = $value;
			$save_data['class_id'] = (isset($_POST['class_map_shipping_method_'.$key])) ? sanitize_text_field($_POST['class_map_shipping_method_'.$key]) : '';
			
			$table = $wpdb->prefix.'mw_wc_qbo_sync_shipping_product_map';
			if($MSQS_QL->get_field_by_val($table,'id','wc_shippingmethod',$key)){
				$wpdb->update($table,$save_data,array('wc_shippingmethod'=>$key),'',array('%s'));
			}else{
				$save_data['wc_shippingmethod'] = $key;
				$wpdb->insert($table, $save_data);
			}
		}
		$MSQS_QL->set_session_val('map_page_update_message',__('Shipping methods mapped successfully.','mw_wc_qbo_sync'));
	}
	$MSQS_QL->redirect($page_url);
}

$qbo_product_options = '<option value=""></option>';
$qbo_product_options.= $MSQS_QL->get_product_dropdown_list();

$qbo_class_options = '<option value=""></option>';
$qbo_class_options.= $MSQS_QL->get_class_dropdown_list();

$selected_options_script = '';
$sm_map_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_shipping_product_map');
if(is_array($sm_map_data) && count($sm_map_data)){
	foreach($sm_map_data as $sm_k=>$sm_val){
		$selected_options_script.='jQuery(\'#map_shipping_method_'.$sm_val['wc_shippingmethod'].'\').val(\''.$sm_val['qbo_product_id'].'\');';
		$selected_options_script.='jQuery(\'#class_map_shipping_method_'.$sm_val['wc_shippingmethod'].'\').val(\''.$sm_val['class_id'].'\');';
	}	
}
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>

<div class="container map-shipping-method-outer">
	<div class="page_title"><h4><?php esc_html_e( 'Shipping Method Mappings', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card">
		<div class="card-content">
			<div class="row">
			<?php if(is_array($wc_sh_methods) && count($wc_sh_methods)):?>
				<form method="POST" class="col s12 m12 l12" action="<?php echo esc_url($page_url);?>">
					<div class="row">
						<div class="col s12 m12 l12">
							<div class="myworks-wc-qbo-sync-table-responsive">
								<table class="mw-qbo-sync-map-table menu-blue-bg" width="100%">	
	                            	<thead>				
	                                    <tr>
	                                        <th width="50%" class="title-description">
	                                            Woocommerce Shipping Method								    	
	                                        </th>
	                                        <th width="25%" class="title-description">
	                                            QuickBooks Product								    	
	                                        </th>
	                                        <th width="25%" class="title-description">
	                                            QuickBooks Class
	                                        </th>
	                                    </tr>
	                                 </thead>   
									<?php foreach($wc_sh_methods as $sm_key => $sm_val):?>
									<tr>
										<td>
										<b><?php echo esc_html($sm_val->method_title);?></b> (<?php echo esc_html($sm_key);?>)
										<p><?php echo esc_html(stripslashes(strip_tags($sm_val->method_description)));?></p>
										</td>
										<td>
											<select name="map_shipping_method_<?php echo esc_attr($sm_key)?>" id="map_shipping_method_<?php echo esc_attr($sm_key)?>"
												<?php echo wp_kses($qbo_product_options, array('option' => array('value' => array(), 'selected' => array())));?>
											</select>
										</td>
										<td>
											<select name="class_map_shipping_method_<?php echo esc_attr($sm_key)?>" id="class_map_shipping_method_<?php echo esc_attr($sm_key)?>"
												<?php echo wp_kses($qbo_class_options, array('option' => array('value' => array(), 'selected' => array())));?>
											</select>
										</td>
									</tr>
									<?php endforeach;?>							
								</table>
							</div>
						</div>
					</div>
					
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_shipping_method', 'map_wc_qbo_shipping_method' ); ?>
						<div class="input-field col s12 m6 l4">
							<button class="waves-effect waves-light btn save-btn mw-qbo-sync-green">Save</button>
						</div>
					</div>
					
				</form>
			<?php else:?>
				
				<h4 class="mw_mlp_ndf">
					<?php esc_html_e( 'No available shipping methods to display.', 'mw_wc_qbo_sync' );?>
				</h4>
			<?php endif;?>
			</div>
		</div>
	</div>
</div>
<?php if($selected_options_script!=''):?>
<script type="text/javascript">
jQuery(document).ready(function(){
	<?php echo wp_kses($selected_options_script, array('script' => array()));?>
});
</script>
<?php endif;?>
<?php echo $MWQS_OF->get_select2_js(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already sanitized in get_select2_js function?>