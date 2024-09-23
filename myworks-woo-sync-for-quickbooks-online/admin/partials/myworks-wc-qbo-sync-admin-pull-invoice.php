<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-pull&tab=invoice';

global $wpdb;
global $MWQS_OF;
global $MSQS_QL;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('invoice_pull_search');
$invoice_pull_search = $MSQS_QL->get_session_val('invoice_pull_search');

$MSQS_QL->set_and_get('invoice_pull_date_from');
$invoice_pull_date_from = $MSQS_QL->get_session_val('invoice_pull_date_from');

$MSQS_QL->set_and_get('invoice_pull_date_to');
$invoice_pull_date_to = $MSQS_QL->get_session_val('invoice_pull_date_to');


$total_records = $MSQS_QL->count_qbo_invoice_list($invoice_pull_search,$invoice_pull_date_from,$invoice_pull_date_to);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$qbo_invoice_list = $MSQS_QL->get_qbo_invoice_list($invoice_pull_search," $offset , $items_per_page",$invoice_pull_date_from,$invoice_pull_date_to);
$order_statuses = wc_get_order_statuses();

$wc_currency = get_woocommerce_currency();
$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);
?>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Invoice Pull', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card">
		<div class="card-content">
			<div class="row">
					<div class="row">
						<div class="col s12 m12 l12">
								<div class="row">
								<div class="panel panel-primary">
									<div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input placeholder="<?php echo __('Name / Company','mw_wc_qbo_sync')?>" type="text" id="invoice_pull_search" value="<?php echo $invoice_pull_search;?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('From yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="invoice_pull_date_from" value="<?php echo $invoice_pull_date_from;?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('To yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="invoice_pull_date_to" value="<?php echo $invoice_pull_date_to;?>">
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
								 
									 <?php if(is_array($qbo_invoice_list) && count($qbo_invoice_list)):?>
									<table id="mwqs_invoice_pull_table" class="table tablesorter" width="100%">
									
									</table>
									<?php echo $pagination_links?>
									<?php else:?>
									<p><?php _e( 'No invoice found.', 'mw_wc_qbo_sync' );?></p>
									<?php endif;?>
								
								</div>
								</div>
						    </div>
					</div>
						
					<div class="row">						
						<div class="input-field col s12 m12 14">
							<button id="pull_selected_invoice_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('pull Selected invoices','mw_wc_qbo_sync')?></button>
							<button id="pull_all_invoice_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('pull All invoices','mw_wc_qbo_sync')?></button>
							
						</div>
					</div>
					
			</div>
					
				
			</div>
		</div>
</div>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php $sync_window_url = site_url('index.php?mw_qbo_sync_public_sync_window=1');?>
 <script type="text/javascript">
	function search_item(){		
		var invoice_pull_search = jQuery('#invoice_pull_search').val();
		var invoice_pull_date_from = jQuery('#invoice_pull_date_from').val();
		var invoice_pull_date_to = jQuery('#invoice_pull_date_to').val();
		
		invoice_pull_search = jQuery.trim(invoice_pull_search);
		invoice_pull_date_from = jQuery.trim(invoice_pull_date_from);
		invoice_pull_date_to = jQuery.trim(invoice_pull_date_to);
		
		if(invoice_pull_search!='' || invoice_pull_date_from!='' || invoice_pull_date_to!=''){		
			window.location = '<?php echo $page_url;?>&invoice_pull_search='+invoice_pull_search+'&invoice_pull_date_from='+invoice_pull_date_from+'&invoice_pull_date_to='+invoice_pull_date_to;
		}else{
			alert('<?php echo __('Please enter search keyword or dates.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&invoice_pull_search=&invoice_pull_date_from=&invoice_pull_date_to=';
	}
	
	jQuery(document).ready(function($) {
		var item_type = 'invoice';
		$('#pull_selected_invoice_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='invoice_pull_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('invoice_pull_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_invoice_pull',0,0,650,350);
			return false;
		});
		
		$('#pull_all_invoice_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&sync_all=1&item_type='+item_type,'mw_qs_invoice_pull',0,0,650,350);
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
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_invoice_pull_table');?>