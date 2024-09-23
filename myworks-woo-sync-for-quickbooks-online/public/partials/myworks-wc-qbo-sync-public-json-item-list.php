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
?>
<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


global $MWQS_OF;
$is_valid_user = false;

/*
if(is_user_logged_in() && (current_user_can('editor') || current_user_can('administrator'))){	
	$is_valid_user = true;
}
*/

if(is_user_logged_in() && current_user_can('manage_woocommerce')){
	$is_valid_user = true;
}

$allow_without_conn = true;
if($is_valid_user){	
	global $MSQS_QL;
	global $wpdb;
	
	if($allow_without_conn || $MSQS_QL->is_connected()){
		$item = (isset($_GET['item']))?$_GET['item']:'';
		
		$search = (isset($_GET['q']))?$_GET['q']:'';		
		$search = $MSQS_QL->sanitize($search);
		
		$limit = ' LIMIT 0,50';
		
		if($item=='qbo_product'){
			
			$query = "SELECT `itemid` as `id`, `name` as `text` FROM `{$wpdb->prefix}mw_wc_qbo_sync_qbo_items` WHERE `name` LIKE '%%%s%%'  OR `sku` LIKE '%%%s%%' ORDER BY `name` ASC {$limit} ";
			
			$query = $wpdb->prepare($query,$search,$search);
			header('Content-Type: application/json');
			$q_data = $MSQS_QL->get_data($query);
			$q_data = $MSQS_QL->stripslash_get_data($q_data,array('text'));
			echo json_encode($q_data);
		}
		
		if($item=='qbo_customer'){
			
			$query = "SELECT `qbo_customerid` as `id`, `dname` as `text` FROM `{$wpdb->prefix}mw_wc_qbo_sync_qbo_customers` WHERE `dname` LIKE '%%%s%%'  OR `email` LIKE '%%%s%%'  OR `first` LIKE '%%%s%%'  OR `last` LIKE '%%%s%%'  OR `company` LIKE '%%%s%%' ORDER BY `dname` ASC {$limit} ";
			
			$query = $wpdb->prepare($query,$search,$search,$search,$search,$search);
			header('Content-Type: application/json');
			echo json_encode($MSQS_QL->get_data($query));
		}
		
		if($item=='qbo_vendor' && $MSQS_QL->is_wq_vendor_pm_enable()){
			
			$query = "SELECT `qbo_vendorid` as `id`, `dname` as `text` FROM `{$wpdb->prefix}mw_wc_qbo_sync_qbo_vendors` WHERE `dname` LIKE '%%%s%%'  OR `email` LIKE '%%%s%%'  OR `first` LIKE '%%%s%%'  OR `last` LIKE '%%%s%%'  OR `company` LIKE '%%%s%%' ORDER BY `dname` ASC {$limit} ";
			
			$query = $wpdb->prepare($query,$search,$search,$search,$search,$search);
			header('Content-Type: application/json');
			echo json_encode($MSQS_QL->get_data($query));
		}
		
	}
}