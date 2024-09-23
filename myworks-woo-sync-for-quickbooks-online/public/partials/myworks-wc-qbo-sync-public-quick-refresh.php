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

//$req_headers = apache_request_headers();
//echo '<pre>';print_r($req_headers);echo '</pre>';
global $MWQS_OF;
global $MSQS_QL;
$is_valid_user = false;
if(is_user_logged_in() && (current_user_can('editor') || current_user_can('administrator') || current_user_can('shop_manager') )){
	$is_valid_user = true;
}
$data_type = '';
if(isset($_GET['data_type']) && $_GET['data_type']!='customer' && $_GET['data_type']!='product'){
	$is_valid_user = false;
	if($_GET['data_type']=='vendor' && $MSQS_QL->is_wq_vendor_pm_enable()){
		$is_valid_user = true;
	}
}

$go_back_txt = '<a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync').'">Return to Dashboard</a>';
if(!$is_valid_user){	
	die($MWQS_OF->get_html_msg(__('Not Authorized','mw_wc_qbo_sync'),'<h1>'.__('Not Authorized','mw_wc_qbo_sync').'</h1>'));
}

global $wpdb;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
}

if($MSQS_QL->is_connected()){
	$data_type = (isset($_GET['data_type']))?$_GET['data_type']:'';
	
	$count_msg = '';
	
	$customer_count = 0;
	$product_count = 0;
	$red_url = '';
	if($data_type==''){
		$customer_count = (int) $MSQS_QL->quick_refresh_qbo_customers();
		$product_count = (int) $MSQS_QL->quick_refresh_qbo_products();
		
		$count_msg.=__('<p>Total Customers Recognized: '.$customer_count.'</p>','mw_wc_qbo_sync');
		$count_msg.=__('<p>Total Products Recognized: '.$product_count.'</p>','mw_wc_qbo_sync');
		
		if($MSQS_QL->is_wq_vendor_pm_enable()){
			$vendor_count = (int) $MSQS_QL->quick_refresh_qbo_vendors();
			$count_msg.=__('<p>Total Vendors Recognized: '.$vendor_count.'</p>','mw_wc_qbo_sync');
		}
	}
	
	if($data_type=='customer'){
		$customer_count = (int) $MSQS_QL->quick_refresh_qbo_customers();
		$count_msg.=__('<p>Total Customers Recognized: '.$customer_count.'</p>','mw_wc_qbo_sync');
		if($MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
			$red_url = admin_url('admin.php?page=myworks-wc-qbo-sync-custom-customer-map&rf_data_count='.$customer_count);
		}else{
			$red_url = admin_url('admin.php?page=myworks-wc-qbo-map&tab=customer&rf_data_count='.$customer_count);
		}		
	}
	
	if($data_type=='vendor' && $MSQS_QL->is_wq_vendor_pm_enable()){
		$vendor_count = (int) $MSQS_QL->quick_refresh_qbo_vendors();
		$count_msg.=__('<p>Total Vendors Recognized: '.$vendor_count.'</p>','mw_wc_qbo_sync');
		$red_url = admin_url('admin.php?page=myworks-wc-qbo-map&tab=vendor&rf_data_count='.$vendor_count);
	}
	
	if($data_type=='product'){
		$product_count = (int) $MSQS_QL->quick_refresh_qbo_products();
		$count_msg.=__('<p>Total Products Recognized: '.$product_count.'</p>','mw_wc_qbo_sync');
		$red_url = admin_url('admin.php?page=myworks-wc-qbo-map&tab=product&rf_data_count='.$product_count);
		if(isset($_GET['variation']) && $_GET['variation']==1){
			$red_url.='&variation=1';
		}
	}

	if($red_url!=''){
		wp_redirect($red_url);
		exit(0);
	}
	
	echo $MWQS_OF->get_html_msg(__('Myworks Quickbooks Sync Quick Refresh','mw_wc_qbo_sync'),'<h2>'.__('Quickbooks Online Data Successfully Recognized','mw_wc_qbo_sync').'</h2>'.$count_msg);
}else{
	echo $MWQS_OF->get_html_msg(__('Myworks Quickbooks Sync Quick Refresh','mw_wc_qbo_sync'),'<h2>'.__('Quickbooks Not Connected','mw_wc_qbo_sync').'</h2>');
}
echo $go_back_txt;