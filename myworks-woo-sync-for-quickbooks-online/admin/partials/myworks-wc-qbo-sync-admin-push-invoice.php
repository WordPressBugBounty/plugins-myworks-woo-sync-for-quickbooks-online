<?php
if ( ! defined( 'ABSPATH' ) )
     exit;

 $page_url = 'admin.php?page=myworks-wc-qbo-push&tab=invoice';
 
 global $MWQS_OF;
 global $MSQS_QL;
 global $wpdb;
 
$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('invoice_push_search');
$invoice_push_search = $MSQS_QL->get_session_val('invoice_push_search');

$MSQS_QL->set_and_get('invoice_date_from');
$invoice_date_from = $MSQS_QL->get_session_val('invoice_date_from');

$MSQS_QL->set_and_get('invoice_date_to');
$invoice_date_to = $MSQS_QL->get_session_val('invoice_date_to');

$MSQS_QL->set_and_get('invoice_status_srch');
$invoice_status_srch = $MSQS_QL->get_session_val('invoice_status_srch');


$total_records = $MSQS_QL->count_order_list($invoice_push_search,$invoice_date_from,$invoice_date_to,$invoice_status_srch);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$wc_order_list = $MSQS_QL->get_order_list($invoice_push_search," $offset , $items_per_page",$invoice_date_from,$invoice_date_to,$invoice_status_srch);
$order_statuses = wc_get_order_statuses();

$wc_currency = get_woocommerce_currency();
$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);

$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page,200,'order');
$sstchc = (!$show_sync_status)?'class="sstchc"':'';

$push_map_data_arr = array();
if($show_sync_status && is_array($wc_order_list) && count($wc_order_list)){
	$order_item_ids_arr = array();
	
	/**/
	$is_qb_next_ord_num = false;
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$MSQS_QL->get_qbo_company_setting('is_custom_txn_num_allowed')){
		$is_qb_next_ord_num = true;
	}
	
	foreach($wc_order_list as $order_details){		
		/**/
		if(!$is_qb_next_ord_num){
			$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($order_details['ID'],$order_details);
			$ord_id_num = ($wc_inv_no!='')?$wc_inv_no:$order_details['ID'];
		}else{
			//$ord_id_num = get_post_meta($order_details['ID'],'_mw_qbo_sync_ord_doc_no',true);
			$ord_id_num = $order_details['_mw_qbo_sync_ord_doc_no'];
			if(empty($ord_id_num)){
				continue;
			}
		}		
		
		$order_item_ids_arr[] = "'".$ord_id_num."'";
		
		if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
			$order_item_ids_arr[] = "'S-".$ord_id_num."'";
			$order_item_ids_arr[] = "'OS-".$ord_id_num."'";
		}
		
	}
	$push_map_data_arr = $MSQS_QL->get_push_invoice_map_data($order_item_ids_arr);
	//$MSQS_QL->_p($push_map_data_arr);
}

?>
<style>
	.sstchc{display:none;}
	.ss_pf_span{display:none;}
</style>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Order Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card qo-push-responsive">
		<div class="card-content">			
						<div class="col s12 m12 l12">

						        <div class="panel panel-primary">
						            <div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input placeholder="<?php echo __('Name / Company / ID / NUM','mw_wc_qbo_sync')?>" type="text" id="invoice_push_search" value="<?php echo $invoice_push_search;?>">
									  &nbsp;
									  <input style="width:130px;" class="mwqs_datepicker" placeholder="<?php echo __('From yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="invoice_date_from" value="<?php echo $invoice_date_from;?>">
									  &nbsp;
									  <input style="width:130px;" class="mwqs_datepicker" placeholder="<?php echo __('To yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="invoice_date_to" value="<?php echo $invoice_date_to;?>">
									  &nbsp;
									  <span>
										  <select style="width:130px;" name="invoice_status_srch" id="invoice_status_srch">
											<option value="">All</option>
											<?php echo  $MSQS_QL->only_option($invoice_status_srch,$order_statuses);?>
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
									 
									 <?php if(is_array($wc_order_list) && count($wc_order_list)):?>
									 <div class="row">
										<div class="input-field col s12 m12 14">
											<button id="push_selected_invoice_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Push Selected Orders','mw_wc_qbo_sync')?></button>
											<button id="push_all_invoice_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push All Orders','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_unsynced_invoice_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push Un-synced Orders','mw_wc_qbo_sync')?></button>
										</div>
									</div>
									 <br />
									 <?php endif;?>

									 <?php if(is_array($wc_order_list) && count($wc_order_list)):?>
									<div class="table-m">
									<div class="myworks-wc-qbo-sync-table-responsive">
									   <table id="mwqs_invoice_push_table" class="table tablesorter">
											<thead>
												<tr>
													<th width="2%">
													<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'invoice_push_')">
													</th>
													<?php if($MSQS_QL->is_plugin_active('woocommerce-sequential-order-numbers-pro','woocommerce-sequential-order-numbers')):?>
													<th width="13%">Order Number / ID</th>
													<?php else:?>
													<th width="13%">Order ID</th>
													<?php endif;?>
													<th width="15%">Customer</th>
													<th width="15%">Company</th>
													<th width="13%">Date</th>
													<th width="10%">Amount</th>
													<th width="13%">Payment</br>Method</th>
													<th width="14%">Order</br>Status</th>
													<th width="5%" <?php echo $sstchc;?>>Sync</br>Status</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach($wc_order_list as $order_details):?>
												<?php
													$sync_status_html = '';
													if($show_sync_status){
														$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
													}
												?>
												<tr>
													<td><input type="checkbox" id="invoice_push_<?php echo $order_details['ID']?>"></td>
													
													<?php
													$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($order_details['ID'],$order_details);
													?>
													
													<td>
													<a target="_blank" href="<?php echo admin_url('post.php?post='.$order_details['ID'].'&action=edit') ?>">
													<?php echo (!empty($wc_inv_no))?$wc_inv_no.'<br/>':'';?>
													<?php echo $order_details['ID'] ?>													
													</a>
													</td>
													<?php $dts=true;?>
													<td <?php if(!$dts && !(int) $order_details['customer_user']):?> style="color:#039be5;" title="Guest Order"<?php endif;?>>
													<?php echo $order_details['billing_first_name'] ?> <?php echo $order_details['billing_last_name'] ?>
													</td>
													<td><?php echo $order_details['billing_company'] ?></td>
													<td><?php echo $order_details['post_date'] ?></td>
													<td>
													<?php
													if($wc_currency==$order_details['order_currency']){
														echo $wc_currency_symbol;
													}else{
														echo $MSQS_QL->get_array_isset($MSQS_QL->get_world_currency_list(true),$order_details['order_currency'],$order_details['order_currency'],false);
													}													
													echo ($order_details['order_total']!='')?$order_details['order_total']:'0.00';
													?>
													</td>
													<td title="<?php echo $order_details['payment_method_title'] ?>">
														<?php echo $order_details['payment_method'] ?>
													</td>
													
													<td><?php echo $MSQS_QL->get_array_isset($order_statuses,$order_details['post_status'],$order_details['post_status']); ?></td>
													<?php
														$r_key = $order_details['ID'];
														/**/
														$r_key = ($wc_inv_no!='')?$wc_inv_no:$r_key;
														
														if($is_qb_next_ord_num && !empty($order_details['_mw_qbo_sync_ord_doc_no'])){
															$r_key = $order_details['_mw_qbo_sync_ord_doc_no'];
														}														
														
														$r_key = md5($r_key);
													?>
													<td class="ph_inv_ss<?php if(!$show_sync_status){echo ' sstchc';}?>" id="ph_inv_ss_<?php echo $r_key;?>"><?php echo $sync_status_html;?></td>
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
										<?php _e( 'No available orders to display.', 'mw_wc_qbo_sync' );?>
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
		var invoice_push_search = jQuery('#invoice_push_search').val();
		var invoice_date_from = jQuery('#invoice_date_from').val();
		var invoice_date_to = jQuery('#invoice_date_to').val();
		var invoice_status_srch = jQuery('#invoice_status_srch').val();
		
		invoice_push_search = jQuery.trim(invoice_push_search);
		invoice_date_from = jQuery.trim(invoice_date_from);
		invoice_date_to = jQuery.trim(invoice_date_to);
		invoice_status_srch = jQuery.trim(invoice_status_srch);
		
		if(invoice_push_search!='' || invoice_date_from!='' || invoice_date_to!='' || invoice_status_srch!=''){		
			window.location = '<?php echo $page_url;?>&invoice_push_search='+invoice_push_search+'&invoice_date_from='+invoice_date_from+'&invoice_date_to='+invoice_date_to+'&invoice_status_srch='+invoice_status_srch;
		}else{
			alert('<?php echo __('Please enter search keyword or dates and status.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&invoice_push_search=&invoice_date_from=&invoice_date_to=&invoice_status_srch=';
	}
	
	jQuery(document).ready(function($) {
		var list_ids = [];
		
		 <?php if(is_array($push_map_data_arr) && count($push_map_data_arr)):?>
		 <?php foreach($push_map_data_arr as $pmd):?>
		 <?php
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
				$qbo_href = $MSQS_QL->get_push_qbo_view_href('SalesReceipt',$pmd['Id']);
				$sync_status_html = '<i title="QuickBooks SalesReceipt Id #'.$pmd['Id'].' - Click to view it in QuickBooks Online" class="fa fa-check-circle" style="color:green"></i>';
			}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate')){
				$qbo_href = $MSQS_QL->get_push_qbo_view_href('Estimate',$pmd['Id']);
				$sync_status_html = '<i title="QuickBooks Estimate Id #'.$pmd['Id'].' - Click to view it in QuickBooks Online" class="fa fa-check-circle" style="color:green"></i>';
			}
			else{
				$qbo_href = $MSQS_QL->get_push_qbo_view_href('Invoice',$pmd['Id']);
				$sync_status_html = '<i title="QuickBooks Invoice Id #'.$pmd['Id'].' - Click to view it in QuickBooks Online" class="fa fa-check-circle" style="color:green"></i>';
			}
			$sync_status_html = '<span class="ss_pf_span">1</span><a target="_blank" href="'.$qbo_href.'">'.$sync_status_html.'</a>';
			
		 ?>
		  <?php 
		  $c_doc_no = $pmd['DocNumber'];
		  if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
			 if($MSQS_QL->start_with($c_doc_no,'S-')){
				$c_doc_no = substr($c_doc_no,2);
			 }elseif($MSQS_QL->start_with($c_doc_no,'OS-')){
				$c_doc_no = substr($c_doc_no,3);
			 }			 
		  }
		  $c_doc_no = md5($c_doc_no);
		  ?>
		 if($.inArray('<?php echo $c_doc_no;?>', list_ids) == -1){
		 list_ids.push("<?php echo $c_doc_no;?>");		
		 jQuery('#ph_inv_ss_<?php echo $c_doc_no;?>').html('<?php echo $sync_status_html?>');
		 }else{
			var ss_title = jQuery('#ph_inv_ss_<?php echo $c_doc_no;?>').children('i').attr('title');
			jQuery('#ph_inv_ss_<?php echo $c_doc_no;?>').children('i').attr('title',ss_title+', #<?php echo $pmd['Id'];?>');
		 }
		 
		 <?php endforeach;?>
		 
		 
		 jQuery('.ph_inv_ss').each(function(){
			 //console.log($(this).attr('id').replace("ph_inv_ss_", ""));
			 if($.inArray($(this).attr('id').replace("ph_inv_ss_", ""), list_ids) == -1){
				$(this).html('<span class="ss_pf_span">0</span><i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		 
		 <?php endif;?>
		 <?php unset($push_map_data_arr);?>
		 
		var item_type = 'invoice';
		$('#push_selected_invoice_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='invoice_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('invoice_push_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_invoice_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_invoice_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_all=1&item_type='+item_type,'mw_qs_invoice_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_invoice_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_unsynced=1&item_type='+item_type,'mw_qs_invoice_push',0,0,650,350);
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
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_invoice_push_table');?>