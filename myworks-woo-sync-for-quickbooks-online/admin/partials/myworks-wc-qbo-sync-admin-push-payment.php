<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-push&tab=payment';

global $wpdb;
global $MWQS_OF;
global $MSQS_QL;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('payment_push_search');
$payment_push_search = $MSQS_QL->get_session_val('payment_push_search');

$MSQS_QL->set_and_get('payment_date_from');
$payment_date_from = $MSQS_QL->get_session_val('payment_date_from');

$MSQS_QL->set_and_get('payment_date_to');
$payment_date_to = $MSQS_QL->get_session_val('payment_date_to');


$total_records = $MSQS_QL->count_wc_payment_list($payment_push_search,$payment_date_from,$payment_date_to);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$wc_payment_list = $MSQS_QL->get_wc_payment_list($payment_push_search," $offset , $items_per_page",$payment_date_from,$payment_date_to);
$order_statuses = wc_get_order_statuses();

$wc_currency = get_woocommerce_currency();
$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);
//$MSQS_QL->_p($wc_payment_list);

$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page);
$sstchc = (!$show_sync_status)?'class="sstchc"':'';

//21-06-2017
$push_map_data_arr = array();
if($show_sync_status && is_array($wc_payment_list) && count($wc_payment_list)){
	$payment_item_ids_arr = array();
	foreach($wc_payment_list as $payment_details){
		if((int) $payment_details['qbo_payment_id']){
			$payment_item_ids_arr[] = "'".(int) $payment_details['qbo_payment_id']."'";
		}		
	}
	$push_map_data_arr = $MSQS_QL->get_push_payment_map_data($payment_item_ids_arr);
	//$MSQS_QL->_p($push_map_data_arr);
}

?>
<style>
	.sstchc{display:none;}
</style>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Payment Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card qo-push-responsive">
		<div class="card-content">


						<div class="col s12 m12 l12">

								<div class="panel panel-primary">
									<div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input placeholder="<?php echo __('Name / Company / ORDER ID / NUM','mw_wc_qbo_sync')?>" type="text" id="payment_push_search" value="<?php echo $payment_push_search;?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('From yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="payment_date_from" value="<?php echo $payment_date_from;?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('To yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="payment_date_to" value="<?php echo $payment_date_to;?>">
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
											<button id="push_selected_payment_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Push Selected Payments','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_payment_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push All Payments','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_unsynced_payment_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push Un-synced Payments','mw_wc_qbo_sync')?></button>
										</div>
									</div>
									 <br />
									 
									 <?php if(is_array($wc_payment_list) && count($wc_payment_list)):?>
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">
											<table id="mwqs_payment_push_table" class="table tablesorter" width="100%">
												<thead>
													<tr>
														<th width="2%">
															<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'payment_push_')">
														</th>
														<th width="5%">&nbsp; ID</th>
														<th width="9%"><?php _e( 'TXN ID', 'mw_wc_qbo_sync' ) ?></th>
														<th width="20%"><?php _e( 'Customer', 'mw_wc_qbo_sync' ) ?></th>
														<th width="13%"><?php _e( 'Order', 'mw_wc_qbo_sync' ) ?></th>
														<th width="8%" title="<?php _e( 'Order Amount', 'mw_wc_qbo_sync' ) ?>">
														<?php _e( 'Amount', 'mw_wc_qbo_sync' ) ?>
														</th>
														<th width="6%"><?php _e( 'TXN Fee', 'mw_wc_qbo_sync' ) ?></th>
														<th width="14%"><?php _e( 'Date', 'mw_wc_qbo_sync' ) ?></th>
														<th width="8%"><?php _e( 'Payment</br>Method', 'mw_wc_qbo_sync' ) ?></th>
														<th width="10%"><?php _e( 'Order</br>Status', 'mw_wc_qbo_sync' ) ?></th>
														<th width="5%" <?php echo $sstchc;?>><?php _e( 'Sync</br>Status', 'mw_wc_qbo_sync' ) ?></th>	
													</tr>
												</thead>
												
												 <tbody>
													<?php foreach($wc_payment_list as $payment_details):?>
													<?php
													$sync_status_html = '';
													if($show_sync_status){
														$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
														if((int) $payment_details['qbo_payment_id']){
															//
															$qbo_payment_id = (int) $payment_details['qbo_payment_id'];
															if(is_array($push_map_data_arr) && in_array($qbo_payment_id,$push_map_data_arr)){
																$qbo_href = $MSQS_QL->get_push_qbo_view_href('Payment',$qbo_payment_id);
																$sync_status_html = '<i title="QuickBooks Payment Id #'.$payment_details['qbo_payment_id'].' - Click to view it in QuickBooks Online" class="fa fa-check-circle" style="color:green"></i>';
																$sync_status_html = '<a target="_blank" href="'.$qbo_href.'">'.$sync_status_html.'</a>';
															}													
														}
													}
													
													?>
													<tr>
														<td><input type="checkbox" id="payment_push_<?php echo $payment_details['payment_id']?>"></td>
														<td><?php echo $payment_details['payment_id'] ?></td>
														<td><?php echo $payment_details['transaction_id'] ?></td>
														<?php $dts=true;?>
														<td <?php if(!$dts && !(int) $payment_details['customer_user']):?> style="color:red;" title="Guest Order"<?php endif;?>>
														<?php echo $payment_details['billing_first_name'] ?> <?php echo $payment_details['billing_last_name'] ?>
														</td>
														
														<?php
														$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($payment_details['order_id'],$payment_details);
														?>
														
														<td>
														<a target="_blank" href="<?php echo admin_url('post.php?post='.$payment_details['order_id'].'&action=edit') ?>">
														<?php echo (!empty($wc_inv_no))?$wc_inv_no.'<br/>':'';?>
														<?php echo $payment_details['order_id'] ?>													
														</a>
														</td>
														
														<td>
														<?php 
														if($wc_currency==$payment_details['order_currency']){
															echo $wc_currency_symbol;
														}else{
															echo $MSQS_QL->get_array_isset($MSQS_QL->get_world_currency_list(true),$payment_details['order_currency'],$payment_details['order_currency'],false);
														}													
														echo $payment_details['order_total'];
														?>
														</td>
														<td>
														<?php 
															if(isset($payment_details[$payment_details['payment_method'].'_txn_fee'])){
																echo $payment_details[$payment_details['payment_method'].'_txn_fee'];
															}else{
																echo '0.00';
															}
														?>
														</td>
														<td><?php echo ($payment_details['paid_date']!='')?$payment_details['paid_date']:$payment_details['order_date']; ?></td>
														<td title="<?php echo $payment_details['payment_method_title'] ?>">
														<?php echo $payment_details['payment_method'] ?>
														</td>
														<td><?php echo $MSQS_QL->get_array_isset($order_statuses,$payment_details['order_status'],$payment_details['order_status']); ?></td>
														<td <?php echo $sstchc;?>><?php echo $sync_status_html;?></td>
													</tr>
													<?php endforeach;?>		    	
												</tbody>
												
											</table>
										</div>
									</div>
									
									<?php if($MSQS_QL->is_pl_res_tml()):?>
										<div class="pp_mt_lsk_msg" style="text-align:center; padding:10px 5px;">
											<p>
											<?php echo $MSQS_QL->get_slmt_hstry_msg();?>
											</p>
										</div>
									<?php endif;?>
									
									<?php echo $pagination_links?>
									
									<?php else:?>									
									<h4 class="mw_mlp_ndf">
										<?php _e( 'No available payments to display.', 'mw_wc_qbo_sync' );?>
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
		var payment_push_search = jQuery('#payment_push_search').val();
		var payment_date_from = jQuery('#payment_date_from').val();
		var payment_date_to = jQuery('#payment_date_to').val();
		
		payment_push_search = jQuery.trim(payment_push_search);
		payment_date_from = jQuery.trim(payment_date_from);
		payment_date_to = jQuery.trim(payment_date_to);
		
		if(payment_push_search!='' || payment_date_from!='' || payment_date_to!=''){		
			window.location = '<?php echo $page_url;?>&payment_push_search='+payment_push_search+'&payment_date_from='+payment_date_from+'&payment_date_to='+payment_date_to;
		}else{
			alert('<?php echo __('Please enter search keyword or dates.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&payment_push_search=&payment_date_from=&payment_date_to=';
	}
	
	jQuery(document).ready(function($) {
		var item_type = 'payment';
		$('#push_selected_payment_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='payment_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('payment_push_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_payment_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_payment_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_all=1&item_type='+item_type,'mw_qs_payment_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_payment_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_unsynced=1&item_type='+item_type,'mw_qs_payment_push',0,0,650,350);
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
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_payment_push_table');?>