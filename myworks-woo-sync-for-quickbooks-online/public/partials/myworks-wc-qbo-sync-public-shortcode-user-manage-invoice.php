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

$page_url = get_site_url(null,'/my-account/invoices/');
//$pdf_url = $page_url.'?quickbooks_invoice_pdf=true&id=';
$pdf_url = get_site_url(null,'index.php?mw_qbo_sync_public_get_user_invoice_pdf=1&id=');

global $wpdb;
global $MWQS_OF;
global $MSQS_QL;

$wc_user_id = (int) get_current_user_id();
$qb_customer_id = (int) $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_customer_pairs','qbo_customerid','wc_customerid',$wc_user_id);
$qbo_invoice_list = array();
//mw_wc_qbo_sync_order_qbo_sync_as
$qrts = $MSQS_QL->get_option('mw_wc_qbo_sync_wam_mng_inv_qrts');
if(empty($qrts)){$qrts = 'Invoice';}

#New
$qb_cus_cur = ''; $qb_cus_bl = 0;

if($qb_customer_id > 0 && ($qrts == 'Invoice' || $qrts == 'Invoice_SalesReceipt' || $qrts == 'Inv_CMemo' || $qrts == 'All') && !$MSQS_QL->is_plg_lc_p_l() && $MSQS_QL->option_checked('mw_wc_qbo_sync_wam_mng_inv_ed')){
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
		$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
	}
	
	/*PDF*/
	
	/*
	if(isset($_GET['quickbooks_invoice_pdf']) && $_GET['quickbooks_invoice_pdf'] == 'true' && isset($_GET['id']) && (int) $_GET['id'] > 0){
		$qbo_inv_id = (int) $_GET['id'];
		$MSQS_QL->get_qb_customer_invoice_pdf($qb_customer_id,$qbo_inv_id);
	}
	*/
	
	$MSQS_QL->set_per_page_from_url();
	$items_per_page = $MSQS_QL->get_item_per_page();

	$MSQS_QL->set_and_get('invoice_manage_search');
	$invoice_manage_search = $MSQS_QL->get_session_val('invoice_manage_search');

	$MSQS_QL->set_and_get('invoice_manage_date_from');
	$invoice_manage_date_from = $MSQS_QL->get_session_val('invoice_manage_date_from');

	$MSQS_QL->set_and_get('invoice_manage_date_to');
	$invoice_manage_date_to = $MSQS_QL->get_session_val('invoice_manage_date_to');

	$total_records = $MSQS_QL->count_qbo_invoice_list($invoice_manage_search,$invoice_manage_date_from,$invoice_manage_date_to,$qb_customer_id);	
	
	$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(true),$items_per_page,true);
	$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page,true,$MSQS_QL->get_page_var(true));

	$qbo_invoice_list = $MSQS_QL->get_qbo_invoice_list($invoice_manage_search," STARTPOSITION $offset MaxResults $items_per_page",$invoice_manage_date_from,$invoice_manage_date_to,$qb_customer_id);
	
	//$order_statuses = wc_get_order_statuses();
	
	//$wc_currency = get_woocommerce_currency();
	//$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);
	//$MSQS_QL->_p($qbo_invoice_list);

	#Customer Balance Field	
	$qb_customer_obj = $MSQS_QL->qbo_real_time_customer_check_get_object($qb_customer_id);
	if($qb_customer_obj && !empty($qb_customer_obj)){
		$qb_cus_bl = $qb_customer_obj->getBalance();		
		$qb_cus_cur = str_replace(array('{','-','}'),array('','',''),$qb_customer_obj->getCurrencyRef());
		$qb_cus_cur = get_woocommerce_currency_symbol($qb_cus_cur);
	}
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
	   <h2 class="mw_wsfqo_cbh2">
	   		Balance: <span id="mw_wsfqo_cbh2_sp"><?php echo $qb_cus_cur;?><?php echo number_format($qb_cus_bl,2);?></span>
	   </h2>
	  <h2 class="mw_wsfqo_mih2">Manage Invoices</h2>   
	  <?php if(is_array($qbo_invoice_list) && count($qbo_invoice_list)):?>
	  <table class="invoice_table">
	    <thead>
	      <tr>
	        <th>#</th>
	        <th>Date</th>
	        <th>Due Date</th>
			<th>Balance</th>
	        <th>Total</th>
			<th>Status</th>
	        <th>Actions</th>
	      </tr>
	    </thead>
	    <tbody>
		<?php 
		foreach($qbo_invoice_list as $Invoice):
		$qbo_id = $MSQS_QL->qbo_clear_braces($Invoice->getId());
		
		/**/
		$qbo_inv_currency = str_replace(array('{','-','}'),array('','',''),$Invoice->getCurrencyRef());
		$qbo_inv_currency_symbol = get_woocommerce_currency_symbol($qbo_inv_currency);
		
		$Balance = (float) $Invoice->getBalance();
		$TotalAmt = (float) $Invoice->getTotalAmt();
		$qb_inv_status = '';
		
		if($Balance < $TotalAmt || $Balance >0){
			$qb_inv_status = 'Partially Paid';
		}
		
		if($Balance == $TotalAmt){
			$qb_inv_status = 'Open';
		}		
		
		if($Balance == 0){
			$qb_inv_status = 'Closed';
		}
		
		//
		$qbo_href = '';
		if($qb_inv_status == 'Open' || $qb_inv_status == 'Partially Paid'){
			if($Invoice->getAllowOnlineACHPayment() == 'true' || $Invoice->getAllowOnlineCreditCardPayment() == 'true'){
				$qbo_href = $Invoice->getInvoiceLink();
			}
		}		
		
		//
		$TxnDate = $Invoice->getTxnDate();
		$DueDate = $Invoice->getDueDate();
		
		$date_format = get_option('date_format');
		if(!empty($date_format)){
			if(!empty($TxnDate)){
				$TxnDate = date($date_format,strtotime($TxnDate));
			}
			
			if(!empty($DueDate)){
				$DueDate = date($date_format,strtotime($DueDate));
			}			
		}
		
		?>
	      <tr>
	        <td>#<?php echo $Invoice->getDocNumber();?></td>
	        <td><?php echo $TxnDate;?></td>
	        <td><?php echo $DueDate;?></td>
			<td><?php echo $qbo_inv_currency_symbol;?><?php echo number_format($Invoice->getBalance(),2);?></td>
	        <td><?php echo $qbo_inv_currency_symbol;?><?php echo number_format($Invoice->getTotalAmt(),2);?></td>
			<td><?php echo $qb_inv_status;?></td>
	        <td>
				<a target="_blank" href="<?php echo $pdf_url.$qbo_id;?>"><button type="button" class="btn btn-pdf">VIEW<!--PDF--></button></a>
				<?php if(!empty($qbo_href)):?>
				&nbsp;
				<a href="<?php echo $qbo_href?>" target="_blank"><button type="button" class="btn btn-pay">Pay</button></a>
				<?php endif;?>
			</td>
	      </tr>
		  <?php endforeach;?>
	    </tbody>
	  </table>
	  <?php echo $pagination_links?>
	  
	 <?php else:?>
		 <?php if($qb_customer_id < 1): ?>
		 	  <div class="alert alert-warning">
		 		<strong>No</strong> invoices available - not mapped to a QuickBooks customer.
		 	  </div>
		 <?php else: ?>
		 	  <div class="alert alert-warning">
		 		<strong>No</strong> invoices available.
		 	  </div>
		 <?php endif;?>	
	<?php endif;?>
	</div>
</body>
</html>