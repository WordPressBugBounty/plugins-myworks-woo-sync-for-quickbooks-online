<?php
if ( ! defined( 'ABSPATH' ) )
exit;
global $MSQS_QL;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

MyWorks_WC_QBO_Sync_Admin::is_trial_version_check();
MyWorks_WC_QBO_Sync_Admin::get_settings_assets(1);
$tab = isset($_GET['tab']) ? $_GET['tab'] : '';

global $wpdb;
$wc_tot_tax_rates = (int) $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."woocommerce_tax_rates` WHERE `tax_rate_id` >0 ");

if($tab=='customer' && !$MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
	if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt') || !empty($MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus'))){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-customer.php';
	}	
}elseif($tab=='payment-method'){ 
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-payment-method.php'; 
}elseif($tab=='product'){
	if(isset($_GET['variation'])){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-variation.php';
	}else{
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-product.php'; 
	}	
}elseif($tab=='tax-class'){
	if($wc_tot_tax_rates > 0){
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_odr_tax_as_li')){
			if(!$MSQS_QL->get_qbo_company_setting('is_automated_sales_tax')){
				require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-tax-class.php';
			}
		}
	}	
}elseif($tab=='custom-fields'){
	if($MSQS_QL->is_plugin_active('myworks-qbo-sync-custom-field-mapping') && $MSQS_QL->check_sh_cfm_hash()){
		//require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-custom-fields.php';
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-custom-fields-new.php';
	}else{
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-dashboard.php';
	}	
}elseif($tab=='shipping-method'){
	if(!$MSQS_QL->is_plg_lc_p_l() && !$MSQS_QL->get_qbo_company_setting('is_shipping_allowed')){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-shipping-method.php';
	}	 
}elseif($tab=='coupon-code'){
	/*
	if(!$MSQS_QL->get_qbo_company_setting('is_discount_allowed')){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-coupon-code.php';
	}
	*/
}elseif($tab=='vendor'){
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-vendor.php';
}else{
	/*
	if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt')){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-customer.php'; 
	}else{
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-payment-method.php';
	}
	*/
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-dashboard.php';
	
}

if($save_status = $MSQS_QL->get_session_val('map_page_update_message','',true)){
	$save_status = ($save_status!='')?$save_status:'error';
	MyWorks_WC_QBO_Sync_Admin::set_setting_alert($save_status);
}