<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=coupon-code';

if (! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_coupon_code', 'map_wc_qbo_coupon_code' ) ) {
	$item_ids = array();
	foreach ($_POST as $key=>$value){
		if ($MSQS_QL->start_with($key, "map_coupon_code_")){
			$id = (int) str_replace("map_coupon_code_", "", $key);
			if($id){ //&& (int) $value
				$item_ids[$id] = (int) $value;
			}
		}
	}
	if(count($item_ids)){
		foreach ($item_ids as $key=>$value){
			$save_data = array();			
			$save_data['qbo_product_id'] = $value;
			$save_data['class_id'] = (isset($_POST['class_map_coupon_code_'.$key]))?$_POST['class_map_coupon_code_'.$key]:'';
			
			$table = $wpdb->prefix.'mw_wc_qbo_sync_promo_code_product_map';
			if($MSQS_QL->get_field_by_val($table,'id','promo_id',$key)){
				$wpdb->update($table,$save_data,array('promo_id'=>$key),'',array('%d'));
			}else{
				$save_data['promo_id'] = $key;
				$wpdb->insert($table, $save_data);
			}
		}
		$MSQS_QL->set_session_val('map_page_update_message',__('Coupons mapped successfully.','mw_wc_qbo_sync'));
	}
	$MSQS_QL->redirect($page_url);
}

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('coupon_map_search');
$coupon_map_search = $MSQS_QL->get_session_val('coupon_map_search');

$wc_coupon_arr = $MSQS_QL->get_custom_post_list('shop_coupon',$items_per_page,$coupon_map_search);

$wc_coupon_codes = $wc_coupon_arr['post_array'];
$pagination_links = $wc_coupon_arr['pagination_links'];

//$MSQS_QL->_p($wc_coupon_codes);

$qbo_product_options = '<option value=""></option>';
$qbo_product_options.= $MSQS_QL->get_product_dropdown_list();

$qbo_class_options = '<option value=""></option>';
$qbo_class_options.= $MSQS_QL->get_class_dropdown_list();

$selected_options_script = '';
$cpm_map_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_promo_code_product_map');
if(is_array($cpm_map_data) && count($cpm_map_data)){
	foreach($cpm_map_data as $cpm_k=>$cpm_val){
		$selected_options_script.='jQuery(\'#map_coupon_code_'.$cpm_val['promo_id'].'\').val(\''.$cpm_val['qbo_product_id'].'\');';
		$selected_options_script.='jQuery(\'#class_map_coupon_code_'.$cpm_val['promo_id'].'\').val(\''.$cpm_val['class_id'].'\');';
	}	
}
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>

<div class="container map-coupon-code-outer">
	<div class="page_title"><h4><?php _e( 'Coupon Code Mappings', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="mw_wc_filter">
	 <span class="search_text">Search</span>
	  &nbsp;
	  <input type="text" id="coupon_map_search" value="<?php echo $coupon_map_search;?>">
	  &nbsp;		
	  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
	  &nbsp;
	  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
	  &nbsp;
	  <span class="filter-right-sec">
		  <span class="entries">Show entries</span>
		  &nbsp;
		  <select style="width:50px;" onchange="javascript:window.location='<?php echo $page_url;?>&<?php echo $MSQS_QL->per_page_keyword;?>='+this.value;">
			<?php echo  $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page);?>
		 </select>
	 </span>
	 </div>
	 <br />
	<div class="card">
		<div class="card-content">
			<div class="row">
			<?php if(is_array($wc_coupon_codes) && count($wc_coupon_codes)):?>
				<form method="POST" class="col s12 m12 l12" action="<?php echo $page_url;?>">
					<div class="row">
						<div class="col s12 m12 l12">
							<div class="myworks-wc-qbo-sync-table-responsive">
								<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">		
	                            	<thead>
	                                	<tr>
	                                    	<th width="50%" class="title-description">
											Woocommerce Coupon Code								    	
	                                        </th>
	                                        <th width="25%" class="title-description">
	                                            Quickbooks Product								    	
	                                        </th>
	                                        <th width="25%" class="title-description">
	                                            Quickbooks Class
	                                        </th>
	                                	</tr>
	                                </thead>			
									
									<?php foreach($wc_coupon_codes as $cp_val):?>
									<tr>
										<td>
										<b><?php echo $cp_val->post_title;?></b>
										<p><?php echo stripslashes(strip_tags($cp_val->post_excerpt));?></p>
										</td>
										<td>
											<select class="mw_wc_qbo_sync_select2" name="map_coupon_code_<?php echo $cp_val->ID?>" id="map_coupon_code_<?php echo $cp_val->ID?>">
												<?php echo $qbo_product_options;?>
											</select>
										</td>
										<td>
											<select class="mw_wc_qbo_sync_select2" name="class_map_coupon_code_<?php echo $cp_val->ID?>" id="class_map_coupon_code_<?php echo $cp_val->ID?>">
												<?php echo $qbo_class_options;?>
											</select>
										</td>
									</tr>
									<?php endforeach;?>
								</table>
								<?php echo $pagination_links?>
							</div>
						</div>
					</div>
					
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_coupon_code', 'map_wc_qbo_coupon_code' ); ?>
						<div class="input-field col s12 m6 l4">
							<button class="waves-effect waves-light btn save-btn mw-qbo-sync-green">Save</button>
						</div>
					</div>
					
				</form>
				<?php else:?>
				
				<h4 class="mw_mlp_ndf">
					<?php _e( 'No available coupon codes to display.', 'mw_wc_qbo_sync' );?>
				</h4>
				<?php endif;?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function search_item(){		
		var coupon_map_search = jQuery('#coupon_map_search').val();
		coupon_map_search = jQuery.trim(coupon_map_search);
		if(coupon_map_search!=''){
			window.location = '<?php echo $page_url;?>&coupon_map_search='+coupon_map_search;
		}else{
			alert('<?php echo __('Please enter search keyword.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&coupon_map_search=';
	}
	<?php if($selected_options_script!=''):?>
	jQuery(document).ready(function(){
		<?php echo $selected_options_script;?>
	});
	<?php endif;?>
	
	<?php if($selected_options_script!=''):?>	
	jQuery(document).ready(function(){
		<?php echo $selected_options_script;?>
	});	
	<?php endif;?>
 </script>
 <?php echo $MWQS_OF->get_select2_js('.mw_wc_qbo_sync_select2');?>