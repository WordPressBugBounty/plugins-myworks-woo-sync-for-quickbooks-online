<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-pull&tab=product';

global $wpdb;
global $MWQS_OF;
global $MSQS_QL;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('product_pull_search');
$product_pull_search = $MSQS_QL->get_session_val('product_pull_search');

$MSQS_QL->set_and_get('product_pull_date_from');
$product_pull_date_from = $MSQS_QL->get_session_val('product_pull_date_from');

$MSQS_QL->set_and_get('product_pull_date_to');
$product_pull_date_to = $MSQS_QL->get_session_val('product_pull_date_to');


$total_records = $MSQS_QL->count_qbo_inventory_list($product_pull_search,$product_pull_date_from,$product_pull_date_to,true);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page,true);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$qbo_product_list = $MSQS_QL->get_qbo_inventory_list($product_pull_search," STARTPOSITION $offset MaxResults $items_per_page",$product_pull_date_from,$product_pull_date_to,true);
$order_statuses = wc_get_order_statuses();

$wc_currency = get_woocommerce_currency();
$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);

$qbo_home_currency = $MSQS_QL->get_qbo_company_setting('h_currency');

$pull_map_data_arr = array();
$pull_map_data_arr_variation = array();
if(is_array($qbo_product_list) && count($qbo_product_list)){
	$product_item_ids_arr = array();
	foreach($qbo_product_list as $product){
		$product_item_ids_arr[] = $MSQS_QL->qbo_clear_braces($product->getId());
	}
	$pull_map_data_arr = $MSQS_QL->get_pull_inventory_map_data($product_item_ids_arr);
	$pull_map_data_arr_variation = $MSQS_QL->get_pull_inventory_map_data_variation($product_item_ids_arr);
	//$MSQS_QL->_p($pull_map_data_arr);
	//$MSQS_QL->_p($pull_map_data_arr_variation);
}
?>
</br></br>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Product Pull', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card pull-block-responsive">
		<div class="card-content">

						<div class="col s12 m12 l12">
	
								<div class="panel panel-primary">
									<div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input placeholder="<?php echo __('Name','mw_wc_qbo_sync')?>" type="text" id="product_pull_search" value="<?php echo esc_attr($product_pull_search);?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('Created from yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="product_pull_date_from" value="<?php echo esc_attr($product_pull_date_from);?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('Created to yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="product_pull_date_to" value="<?php echo esc_attr($product_pull_date_to);?>">
									  &nbsp;									  
									  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
									  &nbsp;
									  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
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
											<button id="pull_selected_product_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Pull Selected Products','mw_wc_qbo_sync')?></button>
										
											
										</div>
									</div>
									<br />
								 
									 <?php if(is_array($qbo_product_list) && count($qbo_product_list)):?>
									 <?php //$MSQS_QL->_p($qbo_product_list);?>
									<div class="table-m">
									<div class="myworks-wc-qbo-sync-table-responsive">
										<table id="mwqs_product_pull_table" class="table tablesorter" width="100%">
											<thead>
												<tr>
													<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'product_pull_')">
													</th>
													<th width="4%">ID</th>
													<th title="Type" width="10%"><?php _e( 'Type', 'mw_wc_qbo_sync' ) ?></th>
													<th width="25%"><?php _e( 'QuickBooks Product Name', 'mw_wc_qbo_sync' ) ?></th>
													<th width="14%"><?php _e( 'SKU', 'mw_wc_qbo_sync' ) ?></th>
													<th width="5%"><?php _e( 'Active', 'mw_wc_qbo_sync' ) ?></th>												
													<th title="UnitPrice" width="8%"><?php _e( 'Price', 'mw_wc_qbo_sync' ) ?></th>
													<th title="TrackQtyOnHand" width="7%"><?php _e( 'Manage</br>Stock', 'mw_wc_qbo_sync' ) ?></th>
													<th title="QtyOnHand" width="8%"><?php _e( 'QuickBooks</br>Stock', 'mw_wc_qbo_sync' ) ?></th>
													<th title="CreateTime" width="13%"><?php _e( 'Created', 'mw_wc_qbo_sync' ) ?></th>
													<th width="5%">Sync</br>Status</th>
												</tr>
											</thead>
											
											 <tbody>
												<?php 
												foreach($qbo_product_list as $product):
												$product_id = $MSQS_QL->qbo_clear_braces($product->getId());
												//04-05-2017
												$pn_title = '';
												if($MSQS_QL->get_qbo_company_info('is_category_enabled') && $product->countSubItem() && $product->getSubItem()=='true'){
													$pn_title = 'Category: '.$product->getParentRef_name();
												}
												?>
													<tr>
														<td><input type="checkbox" id="product_pull_<?php echo (int) $product_id?>"></td>
														<td><?php echo (int) $product_id?></td>
														<td><?php echo $MSQS_QL->escape($product->getType());?></td>
														<td title="<?php echo esc_attr($pn_title);?>"><?php echo $MSQS_QL->escape($product->getName());?></td>
														<td><?php echo ($product->countSku())?$MSQS_QL->escape($product->getSku()):'';?></td>
														<td><?php echo $MSQS_QL->escape($product->getActive());?></td>
														<td>
														<?php
														if($wc_currency==$qbo_home_currency){
															echo $MSQS_QL->escape($wc_currency_symbol);
														}else{
															echo $MSQS_QL->escape($MSQS_QL->get_array_isset($MSQS_QL->get_world_currency_list(true),$qbo_home_currency,$qbo_home_currency,false));
														}														
														echo $MSQS_QL->escape($product->getUnitPrice());
														?>
														</td>
														<td><?php echo $MSQS_QL->escape($product->getTrackQtyOnHand());?></td>
														<td><?php echo ($product->countQtyOnHand())?$MSQS_QL->escape($product->getQtyOnHand()):'';?></td>														
														<td><?php echo $MSQS_QL->escape($MSQS_QL->view_date($product->getMetaData()->getCreateTime(),'Y-m-d H:i:s'));?></td>
														<td class="p_prd_ss" id="p_prd_ss_<?php echo (int) $product_id;?>"></td>
													</tr>
												<?php endforeach;?>
											 </tbody>
										</table>
										</div>
									</div>
									<?php echo $pagination_links?>
									<?php else:?>
									
									<h4 class="mw_mlp_ndf">
										<?php _e( 'No available products to display.', 'mw_wc_qbo_sync' );?>
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
		var product_pull_search = jQuery('#product_pull_search').val();
		var product_pull_date_from = jQuery('#product_pull_date_from').val();
		var product_pull_date_to = jQuery('#product_pull_date_to').val();
		
		product_pull_search = jQuery.trim(product_pull_search);
		product_pull_date_from = jQuery.trim(product_pull_date_from);
		product_pull_date_to = jQuery.trim(product_pull_date_to);
		
		if(product_pull_search!='' || product_pull_date_from!='' || product_pull_date_to!=''){		
			window.location = '<?php echo $page_url;?>&product_pull_search='+product_pull_search+'&product_pull_date_from='+product_pull_date_from+'&product_pull_date_to='+product_pull_date_to;
		}else{
			alert('<?php echo __('Please enter search keyword or dates.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&product_pull_search=&product_pull_date_from=&product_pull_date_to=';
	}
	
	jQuery(document).ready(function($) {
		var list_ids = [];
		
		 <?php if(is_array($pull_map_data_arr) && count($pull_map_data_arr)):?>
		 <?php foreach($pull_map_data_arr as $pmd):?>
		 <?php 
			$sync_status_html = '<i title="Mapped to #'.$pmd['wc_product_id'].'" class="fa fa-check-circle" style="color:green"></i>';
		 ?>
		 list_ids.push("<?php echo $pmd['quickbook_product_id']?>");		 
		 jQuery('#p_prd_ss_<?php echo $pmd['quickbook_product_id']?>').html('<?php echo $sync_status_html?>');
		 <?php endforeach;?>
		 <?php endif;?>
		 <?php unset($pull_map_data_arr);?>
		 
		 jQuery('.p_prd_ss').each(function(){
			 console.log($(this).attr('id').replace("p_prd_ss_", ""));
			 if($.inArray($(this).attr('id').replace("p_prd_ss_", ""), list_ids) == -1){
				$(this).html('<i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		 
		 var list_ids = [];
		
		 <?php if(is_array($pull_map_data_arr_variation) && count($pull_map_data_arr_variation)):?>
		 <?php foreach($pull_map_data_arr_variation as $pmd):?>
		 <?php 
			$sync_status_html = '<i title="Mapped to Variation #'.$pmd['wc_variation_id'].'(Variation)" class="fa fa-check-circle" style="color:green"></i>';
		 ?>
		 list_ids.push("<?php echo $pmd['quickbook_product_id']?>");		 
		 jQuery('#p_prd_ss_<?php echo $pmd['quickbook_product_id']?>').html('<?php echo $sync_status_html?>');
		 <?php endforeach;?>
		 <?php endif;?>
		 <?php unset($pull_map_data_arr_variation);?>
		 
		 jQuery('.p_prd_ss').each(function(){
			 console.log($(this).attr('id').replace("p_prd_ss_", ""));
			 if($.inArray($(this).attr('id').replace("p_prd_ss_", ""), list_ids) == -1){
				//$(this).html('<i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		 
		 
		var item_type = 'product';
		$('#pull_selected_product_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='product_pull_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('product_pull_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_product_pull',0,0,650,350);
			return false;
		});
		
		$('#pull_all_product_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&sync_all=1&item_type='+item_type,'mw_qs_product_pull',0,0,650,350);
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
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_product_pull_table');?>