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

if ( ! defined( 'WPINC' ) ) {
	die;
}

global $MWQS_OF;
global $MSQS_QL;

$qbo_inv_id = (isset( $_GET['id']))?(int) $_GET['id']:0;
if($qbo_inv_id > 0 && is_user_logged_in()){
	global $wpdb;
	$wc_user_id = (int) get_current_user_id();
	$qb_customer_id = (int) $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_customer_pairs','qbo_customerid','wc_customerid',$wc_user_id);
	if($qb_customer_id > 0){
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
		}
		$type = (isset( $_GET['type']) && ($_GET['type'] == 'SalesReceipt' || $_GET['type'] == 'CreditMemo'))?trim($_GET['type']):'';
		$MSQS_QL->get_qb_customer_invoice_pdf($qb_customer_id,$qbo_inv_id,$type);
	}	
}