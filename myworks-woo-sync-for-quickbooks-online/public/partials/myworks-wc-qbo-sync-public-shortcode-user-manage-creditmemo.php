<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://myworks.design/software/wordpress/woocommerce/myworks-wc-qbo-sync
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/public/partials
 */
 
 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$page_url = get_site_url(null,'/my-account/creditmemos/');
$pdf_url = get_site_url(null,'index.php?mw_qbo_sync_public_get_user_invoice_pdf=1&id=');

global $wpdb;
global $MWQS_OF;
global $MSQS_QL;

$wc_user_id = (int) get_current_user_id();
$qb_customer_id = (int) $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_customer_pairs','qbo_customerid','wc_customerid',$wc_user_id);
$qbo_invoice_list = array();

$qrts = $MSQS_QL->get_option('mw_wc_qbo_sync_wam_mng_inv_qrts');
if(empty($qrts)){$qrts = 'CreditMemo';}

if($qb_customer_id > 0 && ($qrts == 'CreditMemo' || $qrts == 'Inv_CMemo' || $qrts == 'All') && !$MSQS_QL->is_plg_lc_p_l() && $MSQS_QL->option_checked('mw_wc_qbo_sync_wam_mng_inv_ed')){
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
		$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
	}	
	
	$MSQS_QL->set_per_page_from_url();
	$items_per_page = $MSQS_QL->get_item_per_page();

	$MSQS_QL->set_and_get('invoice_manage_search');
	$invoice_manage_search = $MSQS_QL->get_session_val('invoice_manage_search');

	$MSQS_QL->set_and_get('invoice_manage_date_from');
	$invoice_manage_date_from = $MSQS_QL->get_session_val('invoice_manage_date_from');

	$MSQS_QL->set_and_get('invoice_manage_date_to');
	$invoice_manage_date_to = $MSQS_QL->get_session_val('invoice_manage_date_to');

	$total_records = $MSQS_QL->count_qbo_creditmemo_list($invoice_manage_search,$invoice_manage_date_from,$invoice_manage_date_to,$qb_customer_id);	
	
	$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(true),$items_per_page,true);
	$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page,true,$MSQS_QL->get_page_var(true));

	$qbo_creditmemo_list = $MSQS_QL->get_qbo_creditmemo_list($invoice_manage_search," STARTPOSITION $offset MaxResults $items_per_page",$invoice_manage_date_from,$invoice_manage_date_to,$qb_customer_id);
	
	//$order_statuses = wc_get_order_statuses();
	
	//$wc_currency = get_woocommerce_currency();
	//$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);
	//$MSQS_QL->_p($qbo_creditmemo_list);
}

?>

<!DOCTYPE html>
<html>
<head>

	<script type='text/javascript' src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.2/jquery.js"></script>
	<script type='text/javascript' src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
	<link rel='stylesheet' href='<?php echo dirname(plugin_dir_url( __FILE__ ));?>/css/myworks-wc-qbo-sync-public.css' type='text/css' media='all' />
</head>
<body>
	<div class="invoice_container mw_wsfqo_p_ic">
	  <h2 class="mw_wsfqo_mih2">Manage Credit Memos</h2> 
	  <?php if(is_array($qbo_creditmemo_list) && count($qbo_creditmemo_list)):?>
	  <table class="invoice_table">
	    <thead>
	      <tr>
	        <th>#</th>
	        <th>Date</th>
	        <th>Total</th>
			<th>Remaining Credit</th>
	        <th>Actions</th>
	      </tr>
	    </thead>
	    <tbody>
		<?php 
		foreach($qbo_creditmemo_list as $CreditMemo):
		$qbo_id = $MSQS_QL->qbo_clear_braces($CreditMemo->getId());
		
		/**/
		$qbo_href = '';		
		
		$qbo_inv_currency = str_replace(array('{','-','}'),array('','',''),$CreditMemo->getCurrencyRef());
		$qbo_inv_currency_symbol = get_woocommerce_currency_symbol($qbo_inv_currency);
		
		
		$TotalAmt = (float) $CreditMemo->getTotalAmt();
		//
		$TxnDate = $CreditMemo->getTxnDate();
		
		$date_format = get_option('date_format');
		if(!empty($date_format) && !empty($TxnDate)){
			$TxnDate = date($date_format,strtotime($TxnDate));			
		}
		
		?>
	      <tr>
	        <td>#<?php echo $CreditMemo->getDocNumber();?></td>
	        <td><?php echo $TxnDate;?></td>
	        <td><?php echo $qbo_inv_currency_symbol;?><?php echo number_format($CreditMemo->getTotalAmt(),2);?></td>
			<td><?php echo $qbo_inv_currency_symbol;?><?php echo number_format($CreditMemo->getRemainingCredit(),2);?></td>
	        <td>
				<a target="_blank" href="<?php echo $pdf_url.$qbo_id;?>&type=CreditMemo"><button type="button" class="btn btn-pdf">VIEW<!--PDF--></button></a>
			</td>
	      </tr>
		  <?php endforeach;?>
	    </tbody>
	  </table>
	  <?php echo $pagination_links?>
	  
	 <?php else:?>
		 <?php if($qb_customer_id < 1): ?>
		 	  <div class="alert alert-warning">
		 		<strong>No</strong> credit memos available - not mapped to a QuickBooks customer.
		 	  </div>
		 <?php else: ?>
		 	  <div class="alert alert-warning">
		 		<strong>No</strong> credit memos available.
		 	  </div>
		 <?php endif;?>	
	<?php endif;?>
	</div>
</body>
</html>