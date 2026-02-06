<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-push&tab=variation';
 
global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

// Sanitize and validate search parameters before processing
if (isset($_GET['variation_push_search'])) {
	$_GET['variation_push_search'] = sanitize_text_field(wp_unslash($_GET['variation_push_search']));
	// Limit search string length for security
	if (strlen($_GET['variation_push_search']) > 100) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$_GET['variation_push_search'] = substr($_GET['variation_push_search'], 0, 100); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
}
if (isset($_GET['variation_um_srch'])) {
	$variation_um_srch_input = sanitize_text_field(wp_unslash($_GET['variation_um_srch']));
	// Validate against allowed values
	$allowed_um_values = array('', 'only_um', 'only_m');
	if (in_array($variation_um_srch_input, $allowed_um_values, true)) {
		$_GET['variation_um_srch'] = $variation_um_srch_input;
	} else {
		$_GET['variation_um_srch'] = '';
	}
}

$MSQS_QL->set_and_get('variation_push_search');
$variation_push_search = $MSQS_QL->get_session_val('variation_push_search');

$MSQS_QL->set_and_get('variation_um_srch');
$variation_um_srch = $MSQS_QL->get_session_val('variation_um_srch');

$total_records = $MSQS_QL->count_woocommerce_variation_list($variation_push_search,false,'',$variation_um_srch);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$wc_variation_list = $MSQS_QL->get_woocommerce_variation_list($variation_push_search,false," $offset , $items_per_page",'',$variation_um_srch);
$wc_currency_symbol = get_woocommerce_currency_symbol();
//$MSQS_QL->_p($wc_variation_list);

$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page);
$sstchc = (!$show_sync_status) ? 'class="sstchc"' : '';

//11-07-2017
$push_map_data_arr = array();
if($show_sync_status && is_array($wc_variation_list) && count($wc_variation_list)){
	$product_item_ids_arr = array();
	foreach($wc_variation_list as $p_val){
		if((int) $p_val['quickbook_product_id']){
			$product_item_ids_arr[] = "'".(int) $p_val['quickbook_product_id']."'";
		}		
	}
	$push_map_data_arr = $MSQS_QL->get_push_product_map_data($product_item_ids_arr);
	//$MSQS_QL->_p($push_map_data_arr);
}

?>
<style>
	.sstchc{display:none;}
</style>
<div class="container">
	<div class="page_title"><h4><?php esc_html_e( 'Variation Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card qo-push-responsive">
		<div class="card-content">

						<div class="col s12 m12 l12">

						        <div class="panel panel-primary">
						             <div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input type="text" placeholder="NAME / SKU / ID" id="variation_push_search" value="<?php echo esc_attr($variation_push_search);?>">
									  &nbsp;
	
									  <span>
										  <select title="Mapped/UnMapped" style="width:80px;" name="variation_um_srch" id="variation_um_srch">
											<?php if(empty($variation_um_srch)):?>
											<option value="">All</option>
											<?php endif;?>
											<?php $MSQS_QL->only_option($variation_um_srch,array('only_um'=>'Only Unmapped','only_m'=>'Only Mapped'), '', '', true) ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										  </select>
									  </span>
									  
									  &nbsp;		
									  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
									  &nbsp;
									  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
									  &nbsp;
									  <span class="filter-right-sec">
										  <span class="entries">Show entries</span>
										  &nbsp;
										  <select style="width:50px;" onchange="javascript:window.location='<?php echo esc_url_raw($page_url);?>&<?php echo esc_attr($MSQS_QL->per_page_keyword);?>='+this.value;">
											<?php echo $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page, '', '', true) ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										 </select>
									 </span>
									 </div>
									 <br />
									 <div class="row">
										<div class="input-field col s12 m12 14">
											<?php wp_nonce_field('myworks_variation_push_action', 'myworks_variation_push_nonce'); ?>
											<button id="push_selected_variation_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo esc_html__('Push Selected Variations','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_variation_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo esc_html__('Push All Variations','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_unsynced_variation_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo esc_html__('Push Un-synced Variations','mw_wc_qbo_sync')?></button>
										</div>
									</div>
									 <br />

									<?php if(is_array($wc_variation_list) && count($wc_variation_list)):?>
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">
											<table class="table" id="mwqs_variation_push_table">
												<thead>
													<tr>
														<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'variation_push_')">
														</th>
														<th width="5%">ID</th>
														<th width="25%">Variation Name</th>
														<th width="24%">Parent Product</th>
														<th width="10%">SKU</th>
														<th width="8%">Price</th>
														<th width="7%"><?php echo wp_kses( __( 'Manage<br>Stock', 'mw_wc_qbo_sync' ), array( 'br' => array() ) ); ?></th>
														<th width="6%">Stock</th>
														
														<th width="8%"><?php echo wp_kses( __( 'Stock<br>Status', 'mw_wc_qbo_sync' ), array( 'br' => array() ) ); ?></th>
														
														<th width="6%" <?php echo $sstchc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>><?php echo wp_kses( __( 'Sync<br>Status', 'mw_wc_qbo_sync' ), array( 'br' => array() ) ); ?></th>										
													</tr>
												</thead>
												<tbody>
												
												<?php foreach($wc_variation_list as $p_val):?>
												<?php
												$sync_status_html = '';
												if($show_sync_status){
													$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
													if((int) $p_val['quickbook_product_id']){
														$quickbook_product_id = (int) $p_val['quickbook_product_id'];
														if(is_array($push_map_data_arr) && in_array($quickbook_product_id,$push_map_data_arr)){
															$sync_status_html = '<i title="Mapped to #'.esc_attr($p_val['quickbook_product_id']).'" class="fa fa-check-circle" style="color:green"></i>';
														}
													}
												}
												
												?>
												<tr>
													<td><input type="checkbox" id="variation_push_<?php echo esc_attr($p_val['ID'])?>"></td>
													<td><?php echo esc_html($p_val['ID'])?></td>
													<td>
													<?php 
														//_e( $p_val['name'], 'mw_wc_qbo_sync' );
														echo esc_html($MSQS_QL->get_variation_name_from_id($p_val['name'],$p_val['parent_name'],$p_val['ID'],$p_val['parent_id']));
													?>
													</td>
													<td>
													<a title="<?php echo esc_attr($p_val['parent_id'])?>" target="_blank" href="post.php?post=<?php echo esc_attr($p_val['parent_id'])?>&action=edit">
													<?php esc_html_e( $p_val['parent_name'], 'mw_wc_qbo_sync' );?>
													</a>
													</td>
													<td><?php echo esc_html($p_val['sku']);?></td>
													<td>
													<?php
													echo esc_html($wc_currency_symbol);
													echo (isset($p_val['price']))?floatval($p_val['price']):'0.00';
													?>
													</td>
													
													<td><?php echo esc_html($p_val['manage_stock']);?></td>
													<td><?php echo number_format(floatval($p_val['stock']),2);?></td>
													
													<td><?php echo esc_html($p_val['stock_status']);?></td>
													
													<td <?php echo $sstchc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>><?php echo $sync_status_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
												</tr>
												<?php endforeach;?>									
												</tbody>
											</table>
										</div>
									</div>
									<?php echo $pagination_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php else:?>									
									<h4 class="mw_mlp_ndf">
										<?php esc_html_e( 'No available variations to display.', 'mw_wc_qbo_sync' );?>
									</h4>
									<?php endif;?>						           
						        </div>

						</div>
		</div>
	</div>
</div>
<?php $sync_window_url = $MSQS_QL->get_sync_window_url();?>
 <script type="text/javascript">
	function search_item(){		
		var variation_push_search = jQuery('#variation_push_search').val();
		variation_push_search = jQuery.trim(variation_push_search);
		// Basic sanitization on client side
		variation_push_search = variation_push_search.replace(/[<>&"']/g, '');
		
		var variation_um_srch = jQuery('#variation_um_srch').val();
		variation_um_srch = jQuery.trim(variation_um_srch);
		
		if(variation_push_search!='' || variation_um_srch!=''){		
			window.location = '<?php echo esc_url_raw($page_url);?>&variation_push_search='+encodeURIComponent(variation_push_search)+'&variation_um_srch='+encodeURIComponent(variation_um_srch);
		}else{
			alert('<?php echo esc_js(__('Please enter search keyword or select mapped/unmapped.','mw_wc_qbo_sync')); ?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo esc_url_raw($page_url);?>&variation_push_search=&variation_um_srch=';
	}
	
	jQuery(document).ready(function($) {
		var item_type = 'variation';
		$('#push_selected_variation_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			var nonce = jQuery('#myworks_variation_push_nonce').val();
			
			jQuery( "input[id^='variation_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('variation_push_','');
					only_id = parseInt(only_id);
					if(only_id>0 && only_id < 999999999){  // Validate ID range
						item_ids+=only_id+',';
					}					
				}
			});
			
			if(item_ids!=''){
				item_ids = item_ids.substring(0, item_ids.length - 1);
			}
			
			if(item_checked==0){
				alert('<?php echo esc_js(__('Please select at least one item.','mw_wc_qbo_sync')); ?>');
				return false;
			}
			
			popUpWindow('<?php echo esc_js(esc_url($sync_window_url));?>&sync_type=push&item_ids='+encodeURIComponent(item_ids)+'&item_type='+encodeURIComponent(item_type)+'&_wpnonce='+encodeURIComponent(nonce),'mw_qs_variation_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_variation_btn').click(function(){
			var nonce = jQuery('#myworks_variation_push_nonce').val();
			popUpWindow('<?php echo esc_js(esc_url($sync_window_url));?>&sync_type=push&sync_all=1&item_type='+encodeURIComponent(item_type)+'&_wpnonce='+encodeURIComponent(nonce),'mw_qs_variation_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_variation_btn').click(function(){
			var nonce = jQuery('#myworks_variation_push_nonce').val();
			popUpWindow('<?php echo esc_js(esc_url($sync_window_url));?>&sync_type=push&sync_unsynced=1&item_type='+encodeURIComponent(item_type)+'&_wpnonce='+encodeURIComponent(nonce),'mw_qs_variation_push',0,0,650,350);
			return false;
		});
	});
 </script>
 <?php echo wp_kses( $MWQS_OF->get_tablesorter_js('#mwqs_variation_push_table'), array('script' => array('src' => array(), 'type' => array(), 'crossorigin' => array(), 'onerror' => array()), 'style' => array()) );?>