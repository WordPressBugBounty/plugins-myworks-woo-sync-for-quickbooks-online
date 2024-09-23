<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-push&tab=product';
 
global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('product_push_search');
$product_push_search = $MSQS_QL->get_session_val('product_push_search');

$MSQS_QL->set_and_get('product_type_srch');
$product_type_srch = $MSQS_QL->get_session_val('product_type_srch');

$MSQS_QL->set_and_get('product_um_srch');
$product_um_srch = $MSQS_QL->get_session_val('product_um_srch');


$total_records = $MSQS_QL->count_woocommerce_product_list($product_push_search,false,$product_type_srch,$product_um_srch);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$wc_product_list = $MSQS_QL->get_woocommerce_product_list($product_push_search," $offset , $items_per_page",false,$product_type_srch,$product_um_srch);
$wc_currency_symbol = get_woocommerce_currency_symbol();
//$MSQS_QL->_p($wc_product_list);

$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page);
$sstchc = (!$show_sync_status)?'class="sstchc"':'';

//11-07-2017
$push_map_data_arr = array();
$product_item_ids_arr = array();
if($show_sync_status && is_array($wc_product_list) && count($wc_product_list)){
	$payment_item_ids_arr = array();
	foreach($wc_product_list as $p_val){
		if((int) $p_val['quickbook_product_id']){
			$product_item_ids_arr[] = "'".(int) $p_val['quickbook_product_id']."'";
		}		
	}
	$push_map_data_arr = $MSQS_QL->get_push_product_map_data($product_item_ids_arr);
	//$MSQS_QL->_p($push_map_data_arr);
}

$wc_p_types = wc_get_product_types();
?>
<style>
	.sstchc{display:none;}
</style>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Product Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card qo-push-responsive">
		<div class="card-content">

						<div class="col s12 m12 l12">

						        <div class="panel panel-primary">
						             <div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input type="text" placeholder="NAME / SKU / ID" id="product_push_search" value="<?php echo $product_push_search;?>">
									  &nbsp;
									  
									  <span class="search_text">Product Type</span>
									  &nbsp;
									  <select id="product_type_srch" style="width:200px !important;">
										<option value="">All but parent variable products</option>
										<option value="all"<?php if($product_type_srch == 'all'){echo ' selected';}?>>All</option>
										<?php echo  $MSQS_QL->only_option($product_type_srch,$wc_p_types);?>
									  </select>
									  &nbsp;
										
									 <span>
										 <select title="Mapped/UnMapped" style="width:80px;" name="product_um_srch" id="product_um_srch">
											<?php if(empty($product_um_srch)):?>
											<option value="">All</option>
											<?php endif;?>
											<?php echo  $MSQS_QL->only_option($product_um_srch,array('only_um'=>'Only Unmapped','only_m'=>'Only Mapped'));?>
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
										  <select style="width:50px;" onchange="javascript:window.location='<?php echo $page_url;?>&<?php echo $MSQS_QL->per_page_keyword;?>='+this.value;">
											<?php echo  $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page);?>
										 </select>
									 </span>
									 </div>
									 <br />
									 <div class="row">
										<div class="input-field col s12 m12 14">
											<button id="push_selected_product_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Push Selected Products','mw_wc_qbo_sync')?></button>
											<button id="push_all_product_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push All Products','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_unsynced_product_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push Un-synced Products','mw_wc_qbo_sync')?></button>
										</div>
									</div>
									 <br />

									<?php if(is_array($wc_product_list) && count($wc_product_list)):?>
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">
											<table class="table" id="mwqs_product_push_table">
												<thead>
													<tr>
														<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'product_push_')">
														</th>
														<th width="5%">ID</th>
														<th width="30%">Product Name</th>
														<th width="14%">SKU</th>
														<th width="8%">Price</th>
														<th width="9%">Manage</br>Stock</th>
														<th width="5%">Stock</th>
														<th width="7%">Type</th>
														<th width="8%">Stock</br>Status</th>
														<th width="7%">Total</br>Sales</th>
														<th width="6%" <?php echo $sstchc;?>>Sync</br>Status</th>
													</tr>
												</thead>
												<tbody>
												
												<?php foreach($wc_product_list as $p_val):?>
												<?php
												$sync_status_html = '';
												if($show_sync_status){
													$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
													if((int) $p_val['quickbook_product_id']){
														$quickbook_product_id = (int) $p_val['quickbook_product_id'];
														if(is_array($push_map_data_arr) && in_array($quickbook_product_id,$push_map_data_arr)){
															$sync_status_html = '<i title="Mapped to #'.$p_val['quickbook_product_id'].'" class="fa fa-check-circle" style="color:green"></i>';
														}												
													}
												}											
												?>
												<tr>
													<td><input type="checkbox" id="product_push_<?php echo $p_val['ID']?>"></td>
													<td><?php echo $p_val['ID']?></td>
													<td><a href="<?php echo admin_url('post.php?action=edit&post=').$p_val['ID'] ?>" target="_blank"><?php _e( $p_val['name'], 'mw_wc_qbo_sync' );?></a></td>
													<td><?php echo $p_val['sku'];?></td>
													<td>
													<?php
													echo $wc_currency_symbol;
													echo (isset($p_val['price']))?floatval($p_val['price']):'0.00';
													?>
													</td>
													
													<td><?php echo $p_val['manage_stock'];?></td>
													<td><?php echo number_format(floatval($p_val['stock']),2);?></td>
													<td><?php echo $p_val['wc_product_type'];?></td>
													<td><?php echo $p_val['stock_status'];?></td>
													<td><?php echo $p_val['total_sales'];?></td>
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
										<?php _e( 'No available products to display.', 'mw_wc_qbo_sync' );?>
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
		var product_push_search = jQuery('#product_push_search').val();
		product_push_search = jQuery.trim(product_push_search);
		
		var product_type_srch = jQuery('#product_type_srch').val();
		product_type_srch = jQuery.trim(product_type_srch);
		
		var product_um_srch = jQuery('#product_um_srch').val();
		product_um_srch = jQuery.trim(product_um_srch);
		
		if(product_push_search!='' || product_type_srch!='' || product_um_srch!=''){			
			window.location = '<?php echo $page_url;?>&product_push_search='+product_push_search+'&product_type_srch='+product_type_srch+'&product_um_srch='+product_um_srch;
		}else{
			alert('<?php echo $MWQS_OF->get_mpp_bs_msg($page_url);?>');
		}
	}
	
	function reset_item(){		
		window.location = '<?php echo $page_url;?>&product_push_search=&product_type_srch=&product_um_srch=';
	}
	
	jQuery(document).ready(function($) {
		var item_type = 'product';
		$('#push_selected_product_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='product_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('product_push_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_product_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_product_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_all=1&item_type='+item_type,'mw_qs_product_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_product_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_unsynced=1&item_type='+item_type,'mw_qs_product_push',0,0,650,350);
			return false;
		});
	});
 </script>
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_product_push_table');?>