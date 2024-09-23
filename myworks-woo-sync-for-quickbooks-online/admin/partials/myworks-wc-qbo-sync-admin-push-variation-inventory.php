<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url_product = 'admin.php?page=myworks-wc-qbo-push&tab=inventory';
$page_url = 'admin.php?page=myworks-wc-qbo-push&tab=inventory&variation=1';
 
global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('variation_inventory_push_search');
$variation_inventory_push_search = $MSQS_QL->get_session_val('variation_inventory_push_search');

//$MWQDC_LB->set_and_get('variation_stock_srch');
//$variation_stock_srch = $MWQDC_LB->get_session_val('variation_stock_srch');
$variation_stock_srch = '';

$total_records = $MSQS_QL->count_woocommerce_variation_list($variation_inventory_push_search,true,$variation_stock_srch);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$wc_inventory_list = $MSQS_QL->get_woocommerce_variation_list($variation_inventory_push_search,true," $offset , $items_per_page",$variation_stock_srch);
$wc_currency_symbol = get_woocommerce_currency_symbol();
//$MSQS_QL->_p($wc_inventory_list);

$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page);
$sstchc = (!$show_sync_status)?'class="sstchc"':'';

$push_map_data_arr = array();
$wi_m_ids = array();
if($show_sync_status && is_array($wc_inventory_list) && count($wc_inventory_list)){
	$inventory_item_ids_arr = array();
	foreach($wc_inventory_list as $inventory){
		if((int) $inventory['quickbook_product_id']){
			$wi_m_ids[$inventory['ID']] = (int) $inventory['quickbook_product_id'];
			$inventory_item_ids_arr[] = "'".(int) $inventory['quickbook_product_id']."'";
		}		
	}
	$push_map_data_arr = $MSQS_QL->get_push_inventory_map_data($inventory_item_ids_arr);
	//$MSQS_QL->_p($push_map_data_arr);
}

//$MSQS_QL->_p($wi_m_ids);
$show_only_mapped_qty_mismatch_items = $MSQS_QL->option_checked('mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl');
$js_somqmi = false;
?>
<style>
	.sstchc{display:none;}
</style>
<div class="mwqs_page_tab_cont">
	<span class="tab_one"><a href="<?php echo $page_url_product;?>"><?php _e( 'Products', 'mw_wc_qbo_sync' );?></a></span>
	&nbsp;
	<span class="tab_two active"><a href="<?php echo $page_url;?>"><?php _e( 'Variations', 'mw_wc_qbo_sync' );?></a></span>
</div>

<div class="container">
	<div class="page_title"><h4><?php _e( 'Variation Inventory Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card qo-push-responsive">
		<div class="card-content">

						<div class="col s12 m12 l12">

						        <div class="panel panel-primary">
						             <div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input type="text" id="variation_inventory_push_search" value="<?php echo $variation_inventory_push_search;?>">
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
									 <div class="row">
										<div class="input-field col s12 m12 14">
											<button id="push_selected_inventory_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Push Selected Inventory','mw_wc_qbo_sync')?></button>
																					
										</div>
									</div>
									 <br />

									<?php if(is_array($wc_inventory_list) && count($wc_inventory_list)):?>
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">
											<table class="table" id="mwqs_inventory_push_table">
												<thead>
													<tr>
														<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'variation_inventory_push_')">
														</th>
														<th width="5%">ID</th>
														<th width="23%">Variation Name</th>
														<th width="24%">Parent Product</th>
														<th width="10%">SKU</th>
														<th width="8%">Price</th>														
														<th width="8%">WooCommerce</br>Stock</th>
														<th width="8%">QuickBooks</br>Stock</th>
														<th width="8%">Stock</br>Status</th>
														<th width="5%" <?php echo $sstchc;?>>Sync</br>Status</th>										
													</tr>
												</thead>
												<tbody>
												<?php $qmhi = 0;?>
												<?php foreach($wc_inventory_list as $p_val):?>
												<?php
												$sync_status_html = '';
												if($show_sync_status){
													$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
													if((int) $p_val['quickbook_product_id']){
														$quickbook_product_id = (int) $p_val['quickbook_product_id'];
														if(is_array($push_map_data_arr) && $MSQS_QL->is_in_array($push_map_data_arr,'quickbook_product_id',$quickbook_product_id)){
															$sync_status_html = '<i title="Mapped to #'.$p_val['quickbook_product_id'].'" class="fa fa-check-circle" style="color:green"></i>';
															
															if($show_only_mapped_qty_mismatch_items){
																$k = array_search($quickbook_product_id, array_column($push_map_data_arr, 'quickbook_product_id'));
																$Qb_QtyOnHand = $push_map_data_arr[$k]['QtyOnHand'];
																if(!$stock && !$Qb_QtyOnHand){
																	$qmhi++;
																	continue;
																}
																
																if(floatval($stock) == floatval($Qb_QtyOnHand)){
																	$qmhi++;
																	continue;
																}
															}
														}												
													}
												}
												?>
												<tr class="wip_vtr" id="tr_wi_<?php echo $p_val['ID']?>">
													<td><input type="checkbox" id="variation_inventory_push_<?php echo $p_val['ID']?>"></td>
													<td><?php echo $p_val['ID']?></td>
													<td><?php _e( $p_val['name'], 'mw_wc_qbo_sync' );?></td>
													<td>
														<a title="<?php echo $p_val['parent_id']?>" target="_blank" href="post.php?post=<?php echo $p_val['parent_id']?>&action=edit">
															<?php _e( $p_val['parent_name'], 'mw_wc_qbo_desk' );?>
														</a>
													</td>
													<td><?php echo $p_val['sku'];?></td>
													<td>
													<?php
													echo $wc_currency_symbol;
													echo (isset($p_val['price']))?floatval($p_val['price']):'0.00';
													?>
													</td>												
													
													<td id="w_qty_<?php echo $p_val['ID']?>">
														<?php echo number_format(floatval($p_val['stock']),2);?>
													</td>
													<?php if((int) $p_val['quickbook_product_id']):?>
													<td class="p_wc_stock_<?php echo (int) $p_val['quickbook_product_id'];?>"></td>
													<?php else:?>
													<td></td>
													<?php endif;?>
													
													<td><?php echo $p_val['stock_status'];?></td>
													
													<td <?php echo $sstchc;?>><?php echo $sync_status_html;?></td>
												</tr>
												<?php endforeach;?>									
												</tbody>
											</table>
										</div>
									</div>
									<?php echo $pagination_links?>
									<?php else:?>									
									<h4 class="mw_mlp_ndf">
										<?php _e( 'No available variation inventories to display.', 'mw_wc_qbo_sync' );?>
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
		var variation_inventory_push_search = jQuery('#variation_inventory_push_search').val();
		if(variation_inventory_push_search!=''){			
			window.location = '<?php echo $page_url;?>&variation_inventory_push_search='+variation_inventory_push_search;
		}else{
			alert('<?php echo __('Please enter search keyword.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&variation_inventory_push_search=';
	}
	
	jQuery(document).ready(function($) {		
		
		 <?php if(is_array($push_map_data_arr) && count($push_map_data_arr)):?>
		 <?php foreach($push_map_data_arr as $pmd):?>		 	 
		 jQuery('.p_wc_stock_<?php echo $pmd['quickbook_product_id']?>').html('<?php echo $pmd['QtyOnHand']?>');		 
		 <?php endforeach;?>
		 
		 <?php if($show_only_mapped_qty_mismatch_items && $js_somqmi):?>
		 var wi_m_ids = <?php echo json_encode($wi_m_ids) ?>;
		 var wi_h_tr = 0;
		 jQuery('.wip_vtr').each(function(){
			var show_i_tr = false;
			var tr_wi_id = $(this).attr('id').replace('tr_wi_','');
			if (tr_wi_id in wi_m_ids){
				var qi_id = wi_m_ids[tr_wi_id];				
				var w_qty = $('#w_qty_'+tr_wi_id).html().trim();
				var q_qty = $('.p_wc_stock_'+qi_id).html().trim();				
				
				if((w_qty && q_qty) && (w_qty != q_qty && w_qty != q_qty+'.00')){
					show_i_tr = true;
				}				
			}
			
			if(!show_i_tr){
				$(this).hide();
				wi_h_tr++;
			}
		 });
		 if(wi_h_tr>0){
			$('.mwqspd_si_txt').append(' ('+wi_h_tr+' Hidden)');
		 }
		 <?php endif;?>
		 
		 <?php else:?>
		 
		 <?php if($show_sync_status && $show_only_mapped_qty_mismatch_items && $js_somqmi):?>
		 //jQuery('.wip_vtr').hide();
		 <?php endif;?>
		 
		 <?php endif;?>
		 <?php unset($push_map_data_arr);?>
		 
		var item_type = 'v_inventory';
		$('#push_selected_inventory_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='variation_inventory_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('variation_inventory_push_','');
					only_id = parseInt(only_id);
					if(only_id>0){
						item_ids+=only_id+',';
					}					
				}
			});
			
			if(item_ids!=''){
				item_ids = item_ids.substring(0, item_ids.length - 1);
			}
			
			if(item_checked==0){
				alert('<?php echo __('Please select at least one item.','mw_wc_qbo_sync');?>');
				return false;
			}
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_variation_inventory_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_inventory_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_all=1&item_type='+item_type,'mw_qs_variation_inventory_push',0,0,650,350);
			return false;
		});		
		
	});
 </script>
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_inventory_push_table');?>