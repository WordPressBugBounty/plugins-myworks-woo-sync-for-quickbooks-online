<?php
	if ( ! defined( 'ABSPATH' ) )
	exit;

	$page_url = 'admin.php?page=myworks-wc-qbo-push&tab=refund';

	global $wpdb;
	global $MWQS_OF;
	global $MSQS_QL;
	
	$MSQS_QL->set_per_page_from_url();
	$items_per_page = $MSQS_QL->get_item_per_page();

	$MSQS_QL->set_and_get('refund_push_search');
	$refund_push_search = $MSQS_QL->get_session_val('refund_push_search');

	$MSQS_QL->set_and_get('refund_date_from');
	$refund_date_from = $MSQS_QL->get_session_val('refund_date_from');

	$MSQS_QL->set_and_get('refund_date_to');
	$refund_date_to = $MSQS_QL->get_session_val('refund_date_to');

	//$MSQS_QL->set_and_get('refund_status_srch');
	//$refund_status_srch = $MSQS_QL->get_session_val('refund_status_srch');
	$refund_status_srch = '';
	
	$total_records = $MSQS_QL->count_refund_list($refund_push_search,$refund_date_from,$refund_date_to,$refund_status_srch);

	$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
	$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

	$wc_refund_list = $MSQS_QL->get_refund_list($refund_push_search," $offset , $items_per_page",$refund_date_from,$refund_date_to,$refund_status_srch);
	$order_statuses = wc_get_order_statuses();

	$wc_currency = get_woocommerce_currency();
	$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);
	
	//$MSQS_QL->_p($wc_refund_list);

	$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page);
	$sstchc = (!$show_sync_status)?'class="sstchc"':'';
	
	$push_map_data_arr = array();
	if($show_sync_status && is_array($wc_refund_list) && count($wc_refund_list)){
		$refund_item_ids_arr = array();
		/**/
		$is_qb_next_ord_num = false;
		/*
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$MSQS_QL->get_qbo_company_setting('is_custom_txn_num_allowed')){
			$is_qb_next_ord_num = true;
		}
		*/
		
		foreach($wc_refund_list as $refund_details){
			//$refund_item_ids_arr[] = "'".$refund_details['order_id'].'-'.$refund_details['ID']."'";
			/**/
			if(!$is_qb_next_ord_num){
				$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($refund_details['order_id']);
				$ord_id_num = ($wc_inv_no!='')?$wc_inv_no:$refund_details['order_id'];
			}else{
				$ord_id_num = get_post_meta($refund_details['order_id'],'_mw_qbo_sync_ord_doc_no',true);
				//$ord_id_num = $refund_details['_mw_qbo_sync_ord_doc_no'];
				if(empty($ord_id_num)){
					continue;
				}
			}
			
			$refund_item_ids_arr[] = "'".$ord_id_num.'-'.$refund_details['ID']."'";
		}
		
		//$MSQS_QL->_p($refund_item_ids_arr);
		$push_map_data_arr = $MSQS_QL->get_push_refund_map_data($refund_item_ids_arr);
		//$MSQS_QL->_p($push_map_data_arr);
	}
	
?>

<div class="container">
	<div class="page_title"><h4><?php _e( 'Refund Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card qo-push-responsive">
		<div class="card-content">			
						<div class="col s12 m12 l12">

						        <div class="panel panel-primary">
						            <div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <!--Name / Company / ID / NUM-->
									  <input placeholder="<?php echo __('Refund/Order ID','mw_wc_qbo_sync')?>" type="text" id="refund_push_search" value="<?php echo $refund_push_search;?>">
									  &nbsp;
									  <input style="width:130px;" class="mwqs_datepicker" placeholder="<?php echo __('From yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="refund_date_from" value="<?php echo $refund_date_from;?>">
									  &nbsp;
									  <input style="width:130px;" class="mwqs_datepicker" placeholder="<?php echo __('To yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="refund_date_to" value="<?php echo $refund_date_to;?>">									  
									  &nbsp;
									  <?php if( $html_section=false):?>
									  <span>
										  <select style="width:130px;" name="refund_status_srch" id="refund_status_srch">
											<option value="">All</option>
											<?php //echo $MSQS_QL->only_option($refund_status_srch,$order_statuses);?>
										  </select>
									  </span>
									  &nbsp;
									  <?php endif;?>
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
									 
									 <?php if(is_array($wc_refund_list) && count($wc_refund_list)):?>
									 <div class="row">
										<div class="input-field col s12 m12 14">
											<button id="push_selected_refund_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Push Selected Refunds','mw_wc_qbo_sync')?></button>
											<button style="display:none;" id="push_all_refund_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push All Refunds','mw_wc_qbo_sync')?></button>
											<button style="display:none;" disabled="disabled" id="push_all_unsynced_refund_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push Un-synced Refunds','mw_wc_qbo_sync')?></button>
										</div>
									</div>
									 <br />
									 <?php endif;?>

									 <?php if(is_array($wc_refund_list) && count($wc_refund_list)):?>
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">
										    <table id="mwqs_refund_push_table" class="table tablesorter">
												<thead>
													<tr>
														<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all_desk(this,'refund_push_')">
														</th>
														<th>Refund ID</th>
														<th>Order ID</th>
														<th>Refund Date</th>
														<th>Refund Amount</th>
														<th>Order Amount</th>
														<th width="25%">Reason</th>
														<th>Sync Status</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($wc_refund_list as $refund_details):?>
													<?php
													$refund_meta = get_post_meta($refund_details['ID']);
													if(!is_array($refund_meta)){
														$refund_meta = array(
														'_order_currency' => array(''),
														'_order_total' => array(''),
														'_refund_amount' => array(''),
														'_refund_reason' => array('')
														);
													}
													
													//$order_meta = get_post_meta($refund_details['order_id']);
													
													?>
													<?php
													$trash_link = '';
													$s_chk_disabled = '';
													
													$sync_status_html = '';
													if($show_sync_status){
														$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
													}
													?>
													<tr>
														<td><input <?php echo $s_chk_disabled;?> type="checkbox" id="refund_push_<?php echo $refund_details['ID']?>"></td>
														
														<td><?php echo $refund_details['ID']?></td>
														<?php 
															$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($refund_details['order_id']);
														?>
														<td>
														<a href="<?php echo admin_url('post.php?post='.$refund_details['order_id'].'&action=edit');?>" target="_blank">
														<?php echo (!empty($wc_inv_no))?$wc_inv_no.'<br/>':'';?>
														<?php echo $refund_details['order_id']?>
														</a>
														</td>
														<td><?php echo $refund_details['refund_date']?></td>
														
														<td>
														<?php 
														if($wc_currency==$refund_meta['_order_currency'][0]){
															echo $wc_currency_symbol;
														}else{
															echo $MSQS_QL->get_array_isset($MSQS_QL->get_world_currency_list(true),$refund_meta['order_currency'][0],$refund_meta['_order_currency'][0],false);
														}													
														echo ($refund_meta['_refund_amount'][0]!='')?$refund_meta['_refund_amount'][0]:'0.00';
														?>
														</td>
														<td>
														<?php 
														if($wc_currency==$refund_meta['_order_currency'][0]){
															echo $wc_currency_symbol;
														}else{
															echo $MSQS_QL->get_array_isset($MSQS_QL->get_world_currency_list(true),$refund_meta['order_currency'][0],$refund_meta['_order_currency'][0],false);
														}													
														//echo ($refund_meta['_order_total'][0]!='')?$refund_meta['_order_total'][0]:'0.00';
														echo get_post_meta($refund_details['order_id'],'_order_total',true);
														?>
														</td>
														<td><?php echo strip_tags($refund_meta['_refund_reason'][0]);?></td>
														
														<?php
															//$r_key = $refund_details['order_id'].'-'.$refund_details['ID'];
															$ord_id_num = ($wc_inv_no!='')?$wc_inv_no:$refund_details['order_id'];
															
															/*
															if($is_qb_next_ord_num){
																$ord_id_num = get_post_meta($refund_details['order_id'],'_mw_qbo_sync_ord_doc_no',true);
															}
															*/
															
															$r_key = $ord_id_num.'-'.$refund_details['ID'];
															
															$r_key = md5($r_key);
														?>
														<td class="ph_rfnd_ss<?php if(!$show_sync_status){echo ' sstchc';}?>" id="ph_rfnd_ss_<?php echo $r_key;?>">
															<?php echo $sync_status_html;?>
														</td>
													</tr>
													<?php endforeach;?>		    	
												</tbody>
										   </table>
										  </div>
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
										<?php _e( 'No available refunds to display.', 'mw_wc_qbo_sync' );?>
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
		var refund_push_search = jQuery('#refund_push_search').val();
		var refund_date_from = jQuery('#refund_date_from').val();
		var refund_date_to = jQuery('#refund_date_to').val();
		var refund_status_srch = jQuery('#refund_status_srch').val();
		
		refund_push_search = jQuery.trim(refund_push_search);
		refund_date_from = jQuery.trim(refund_date_from);
		refund_date_to = jQuery.trim(refund_date_to);
		refund_status_srch = jQuery.trim(refund_status_srch);
		
		if(refund_push_search!='' || refund_date_from!='' || refund_date_to!='' || refund_status_srch!=''){		
			window.location = '<?php echo $page_url;?>&refund_push_search='+refund_push_search+'&refund_date_from='+refund_date_from+'&refund_date_to='+refund_date_to+'&refund_status_srch='+refund_status_srch;
		}else{
			alert('<?php echo __('Please enter search keyword or dates and status.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&refund_push_search=&refund_date_from=&refund_date_to=&refund_status_srch=';
	}
	
	jQuery(document).ready(function($) {
		var list_ids = [];
		<?php if(is_array($push_map_data_arr) && count($push_map_data_arr)):?>
		 <?php foreach($push_map_data_arr as $pmd):?>
		 <?php
			$qbo_href = $MSQS_QL->get_push_qbo_view_href('Refund',$pmd['Id']);
			$sync_status_html = '<i title="QuickBooks Refund Id #'.$pmd['Id'].' - Click to view it in QuickBooks Online" class="fa fa-check-circle" style="color:green"></i>';
			$sync_status_html = '<a target="_blank" href="'.$qbo_href.'">'.$sync_status_html.'</a>';
			
			$dn_arr = explode('-',$pmd['DocNumber']);
			$m_rf_id = (is_array($dn_arr) && count($dn_arr) == 2)?$dn_arr[1]:'';
			#AL
			if(is_array($dn_arr) && count($dn_arr) > 2){
				$m_rf_id = $dn_arr[count($dn_arr)-1];
			}
			
			if(!empty($m_rf_id)){
				echo 'jQuery("#refund_push_'.$m_rf_id.'").attr("disabled","disabled").attr("title","Synced");' .PHP_EOL;
			}
			
		 ?>
		  <?php 
		  $c_doc_no = $pmd['DocNumber'];		  
		  $c_doc_no = md5($c_doc_no);
		  ?>
		 if($.inArray('<?php echo $c_doc_no;?>', list_ids) == -1){
		 list_ids.push("<?php echo $c_doc_no;?>");		
		 jQuery('#ph_rfnd_ss_<?php echo $c_doc_no;?>').html('<?php echo $sync_status_html?>');
		 }else{
			var ss_title = jQuery('#ph_rfnd_ss_<?php echo $c_doc_no;?>').children('i').attr('title');
			jQuery('#ph_rfnd_ss_<?php echo $c_doc_no;?>').children('i').attr('title',ss_title+', #<?php echo $pmd['Id'];?>');
		 }
		 
		 <?php endforeach;?>		 
		 
		 jQuery('.ph_rfnd_ss').each(function(){
			 //console.log($(this).attr('id').replace("ph_rfnd_ss_", ""));
			 if($.inArray($(this).attr('id').replace("ph_rfnd_ss_", ""), list_ids) == -1){
				$(this).html('<i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		 
		 <?php endif;?>
		 <?php unset($push_map_data_arr);?>
		 
		var item_type = 'refund';
		$('#push_selected_refund_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='refund_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('refund_push_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_refund_push_desk',0,0,650,350);
			return false;
		});
		
		$('#push_all_refund_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_all=1&item_type='+item_type,'mw_qs_refund_push_desk',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_refund_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_unsynced=1&item_type='+item_type,'mw_qs_refund_push_desk',0,0,650,350);
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
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_refund_push_table');?>