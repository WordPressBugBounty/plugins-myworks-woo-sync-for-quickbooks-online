<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-pull&tab=inventory';

global $wpdb;
global $MWQS_OF;
global $MSQS_QL;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('inventory_pull_search');
$inventory_pull_search = $MSQS_QL->get_session_val('inventory_pull_search');

$MSQS_QL->set_and_get('inventory_pull_date_from');
$inventory_pull_date_from = $MSQS_QL->get_session_val('inventory_pull_date_from');

$MSQS_QL->set_and_get('inventory_pull_date_to');
$inventory_pull_date_to = $MSQS_QL->get_session_val('inventory_pull_date_to');


$total_records = $MSQS_QL->count_qbo_inventory_list($inventory_pull_search,$inventory_pull_date_from,$inventory_pull_date_to,false,true);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page,true);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$qbo_inventory_list = $MSQS_QL->get_qbo_inventory_list($inventory_pull_search," STARTPOSITION $offset MaxResults $items_per_page",$inventory_pull_date_from,$inventory_pull_date_to,false,true);
$order_statuses = wc_get_order_statuses();

$wc_currency = get_woocommerce_currency();
$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);

$qbo_home_currency = $MSQS_QL->get_qbo_company_setting('h_currency');

//30-03-2017
$pull_map_data_arr = array();
$pull_map_data_arr_variation = array();
if(is_array($qbo_inventory_list) && count($qbo_inventory_list)){
	$inventory_item_ids_arr = array();
	foreach($qbo_inventory_list as $inventory){
		$inventory_item_ids_arr[] = $MSQS_QL->qbo_clear_braces($inventory->getId());
	}
	$pull_map_data_arr = $MSQS_QL->get_pull_inventory_map_data($inventory_item_ids_arr);
	$pull_map_data_arr_variation = $MSQS_QL->get_pull_inventory_map_data_variation($inventory_item_ids_arr);
	//$MSQS_QL->_p($pull_map_data_arr);
	//$MSQS_QL->_p($pull_map_data_arr_variation);
}

$show_only_mapped_qty_mismatch_items = $MSQS_QL->option_checked('mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl_pull');
?>
</br></br>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Inventory Pull', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card pull-block-responsive">
		<div class="card-content">

						<div class="col s12 m12 l12">
	
								<div class="panel panel-primary">
									<div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input placeholder="<?php echo __('Name','mw_wc_qbo_sync')?>" type="text" id="inventory_pull_search" value="<?php echo esc_attr($inventory_pull_search);?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('Updated from yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="inventory_pull_date_from" value="<?php echo esc_attr($inventory_pull_date_from);?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('Updated to yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="inventory_pull_date_to" value="<?php echo esc_attr($inventory_pull_date_to);?>">
									  &nbsp;									  
									  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
									  &nbsp;
									  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
									  &nbsp;
									  <span class="filter-right-sec">
										  <span class="entries">Show entries</span>
										  &nbsp;
										  <select style="width:50px;" onchange="javascript:window.location='<?php echo esc_url($page_url);?>&<?php echo $MSQS_QL->escape($MSQS_QL->per_page_keyword);?>='+this.value;">
											<?php echo  $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page);?>
										 </select>
									 </span>
									 </div>
									 <br />
									 <div class="row">						
										<div class="input-field col s12 m12 14">
											<button id="pull_selected_inventory_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('pull Selected Inventories','mw_wc_qbo_sync')?></button>
										
											
										</div>
									</div>
									<br />
									<?php $qmhi = 0;?>
									<?php if(is_array($qbo_inventory_list) && count($qbo_inventory_list)):?>
									<?php //$MSQS_QL->_p($qbo_inventory_list);?>
									<div class="table-m">
									<div class="myworks-wc-qbo-sync-table-responsive">
										<table id="mwqs_inventory_pull_table" class="table tablesorter" width="100%">
											<thead>
												<tr>
													<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'inventory_pull_')">
													</th>
													<th width="4%">ID</th>
													<th width="23%"><?php _e( 'QuickBooks Product Name', 'mw_wc_qbo_sync' ) ?></th>
													<th width="12%"><?php _e( 'SKU', 'mw_wc_qbo_sync' ) ?></th>
													<th width="5%"><?php _e( 'Active', 'mw_wc_qbo_sync' ) ?></th>												
													<th title="UnitPrice" width="7%"><?php _e( 'Price', 'mw_wc_qbo_sync' ) ?></th>
													<th title="TrackQtyOnHand" width="7%"><?php _e( 'Manage</br>Stock', 'mw_wc_qbo_sync' ) ?></th>
													<th title="WooCommerce Stock" width="8%"><?php _e( 'WooCommerce</br>Stock', 'mw_wc_qbo_sync' ) ?></th>
													<th title="QtyOnHand" width="8%"><?php _e( 'QuickBooks</br>Stock', 'mw_wc_qbo_sync' ) ?></th>
													<th title="InvStartDate" width="7%"><?php _e( 'Start Date', 'mw_wc_qbo_sync' ) ?></th>
													<th title="LastUpdatedTime" width="13%"><?php _e( 'Updated', 'mw_wc_qbo_sync' ) ?></th>
													<th width="5%">Sync</br>Status</th>
												</tr>
											</thead>
											
											 <tbody>
												<?php 
												foreach($qbo_inventory_list as $inventory):
												$inventory_id = $MSQS_QL->qbo_clear_braces($inventory->getId());											
												/**/
												if($show_only_mapped_qty_mismatch_items){
													$stock = 0;
													$Qb_QtyOnHand = $inventory->getQtyOnHand();
													$is_inventory_mapped = false;
													if(is_array($pull_map_data_arr) && $MSQS_QL->is_in_array($pull_map_data_arr,'quickbook_product_id',$inventory_id)){
														$is_inventory_mapped = true;
														$k = array_search($inventory_id, array_column($pull_map_data_arr, 'quickbook_product_id'));
														$stock = $pull_map_data_arr[$k]['stock'];
													}
													
													if(!$is_inventory_mapped){
														if(is_array($pull_map_data_arr_variation) && $MSQS_QL->is_in_array($pull_map_data_arr_variation,'quickbook_product_id',$inventory_id)){
															$is_inventory_mapped = true;
															$k = array_search($inventory_id, array_column($pull_map_data_arr_variation, 'quickbook_product_id'));
															$stock = $pull_map_data_arr_variation[$k]['stock'];
														}														
													}
													
													if($is_inventory_mapped){														
														
														if(!$stock && !$Qb_QtyOnHand){
															$qmhi++;
															continue;
														}
														
														if(floatval($stock) == floatval($Qb_QtyOnHand)){
															$qmhi++;
															continue;
														}
														
														if(strpos($stock,'.')!==false){
															$stock = number_format(floatval($stock),2);
														}
													}
												}												
												?>
													<tr class="wip_vtr" id="tr_wi_<?php echo (int) $inventory_id;?>">
														<td><input type="checkbox" id="inventory_pull_<?php echo $inventory_id?>"></td>
														<td><?php echo (int) $inventory_id?></td>
														<td><?php echo $MSQS_QL->escape($inventory->getName());?></td>
														<td><?php echo $MSQS_QL->escape($inventory->getSku());?></td>
														<td><?php echo $MSQS_QL->escape($inventory->getActive());?></td>
														<td>
														<?php 
														echo ($wc_currency==$qbo_home_currency)?$MSQS_QL->escape($wc_currency_symbol):$MSQS_QL->escape($qbo_home_currency);
														echo $MSQS_QL->escape($inventory->getUnitPrice());
														?>
														</td>
														<td><?php echo $MSQS_QL->escape($inventory->getTrackQtyOnHand());?></td>
														<td id="p_wc_stock_<?php echo (int) $inventory_id;?>"></td>
														<td><?php echo $MSQS_QL->escape($inventory->getQtyOnHand());?></td>
														<td><?php echo $MSQS_QL->escape($inventory->getInvStartDate());?></td>
														<td>
														<?php echo $MSQS_QL->escape($MSQS_QL->view_date($inventory->getMetaData()->getLastUpdatedTime(),'Y-m-d H:i:s'));?>
														</td>
														<td class="p_invt_ss" id="p_invt_ss_<?php echo (int) $inventory_id;?>"></td>
													</tr>
												<?php endforeach;?>
											 </tbody>
										</table>
										</div>
									</div>
									<?php echo $pagination_links?>
									<?php else:?>
									
									<h4 class="mw_mlp_ndf">
										<?php _e( 'No available inventories to display.', 'mw_wc_qbo_sync' );?>
									</h4>
									<?php endif;?>
								
								</div>

						    </div>
			</div>
		</div>
</div>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php $sync_window_url = $MSQS_QL->get_sync_window_url();?>
 <script type="text/javascript">
	function search_item(){		
		var inventory_pull_search = jQuery('#inventory_pull_search').val();
		var inventory_pull_date_from = jQuery('#inventory_pull_date_from').val();
		var inventory_pull_date_to = jQuery('#inventory_pull_date_to').val();
		
		inventory_pull_search = jQuery.trim(inventory_pull_search);
		inventory_pull_date_from = jQuery.trim(inventory_pull_date_from);
		inventory_pull_date_to = jQuery.trim(inventory_pull_date_to);
		
		if(inventory_pull_search!='' || inventory_pull_date_from!='' || inventory_pull_date_to!=''){		
			window.location = '<?php echo $page_url;?>&inventory_pull_search='+inventory_pull_search+'&inventory_pull_date_from='+inventory_pull_date_from+'&inventory_pull_date_to='+inventory_pull_date_to;
		}else{
			alert('<?php echo __('Please enter search keyword or dates.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&inventory_pull_search=&inventory_pull_date_from=&inventory_pull_date_to=';
	}
	
	jQuery(document).ready(function($) {
		
		var list_ids = [];
		
		 <?php if(is_array($pull_map_data_arr) && count($pull_map_data_arr)):?>
		 <?php foreach($pull_map_data_arr as $pmd):?>
		 <?php 
			$sync_status_html = '<i title="Mapped to #'.$pmd['wc_product_id'].'" class="fa fa-check-circle" style="color:green"></i>';
		 ?>
		 list_ids.push("<?php echo $pmd['quickbook_product_id']?>");
		 jQuery('#p_wc_stock_<?php echo $pmd['quickbook_product_id']?>').html('<?php echo (float) $pmd['stock']?>');
		 jQuery('#p_invt_ss_<?php echo $pmd['quickbook_product_id']?>').html('<?php echo $sync_status_html?>');
		 <?php endforeach;?>
		 <?php endif;?>
		 <?php unset($pull_map_data_arr);?>
		 
		 jQuery('.p_invt_ss').each(function(){
			 console.log($(this).attr('id').replace("p_invt_ss_", ""));
			 if($.inArray($(this).attr('id').replace("p_invt_ss_", ""), list_ids) == -1){
				$(this).html('<i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		 
		 var list_ids = [];
		
		 <?php if(is_array($pull_map_data_arr_variation) && count($pull_map_data_arr_variation)):?>
		 <?php foreach($pull_map_data_arr_variation as $pmd):?>
		 <?php 
			$sync_status_html = '<i title="Mapped to Variation #'.$pmd['wc_variation_id'].'" class="fa fa-check-circle" style="color:green"></i>';
		 ?>
		 list_ids.push("<?php echo $pmd['quickbook_product_id']?>");
		 jQuery('#p_wc_stock_<?php echo $pmd['quickbook_product_id']?>').html('<?php echo (float) $pmd['stock']?>');
		 jQuery('#p_invt_ss_<?php echo $pmd['quickbook_product_id']?>').html('<?php echo $sync_status_html?>');
		 <?php endforeach;?>
		 <?php endif;?>
		 <?php unset($pull_map_data_arr_variation);?>
		 
		 jQuery('.p_invt_ss').each(function(){
			 console.log($(this).attr('id').replace("p_invt_ss_", ""));
			 if($.inArray($(this).attr('id').replace("p_invt_ss_", ""), list_ids) == -1){
				//$(this).html('<i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		
		<?php if($qmhi):?>
		 $('.mwqspd_si_txt').append(' ('+'<?php echo $qmhi;?>'+' Hidden)');
		<?php endif;?>
		 
		var item_type = 'inventory';
		$('#pull_selected_inventory_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='inventory_pull_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('inventory_pull_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_inventory_pull',0,0,650,350);
			return false;
		});
		
		$('#pull_all_inventory_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&sync_all=1&item_type='+item_type,'mw_qs_inventory_pull',0,0,650,350);
			return false;
		});	
		
	});
	
	jQuery( function($) {
		$('.mwqs_datepicker').css('cursor','pointer');
		$( ".mwqs_datepicker" ).datepicker(
			{ 
			dateFormat: 'yy-mm-dd',
			yearRange: "-50:+0",
			changeMonth: true,
			changeYear: true
			}
		);
	  } );
 </script>
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_inventory_pull_table');?>