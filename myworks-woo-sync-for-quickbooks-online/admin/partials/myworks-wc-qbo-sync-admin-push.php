<?php
if ( ! defined( 'ABSPATH' ) )
exit;

MyWorks_WC_QBO_Sync_Admin::is_trial_version_check();
global $MSQS_QL;
global $MWQS_OF;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$show_srmi = true; # Setup restricted menu items
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-nav.php' ?>

<?php
$tab = isset($_GET['tab']) ? $_GET['tab'] : '' ;

if($tab=='customer' && !$MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
	if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt') || !empty($MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus'))){
		//if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')){}
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-customer.php';
	}	
}else if($tab=='invoice'){
	if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-invoice.php';
	}	
}else if($tab=='refund' && !$MSQS_QL->is_plg_lc_p_l()){
	if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')){
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate')){
			require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-refund.php';
		}
	}	
}else if($tab=='deposit'){
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-deposit.php';
}else if($tab=='product'){
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_default_settings') || $show_srmi){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-product.php';
	}	
}else if($tab=='variation'){
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_default_settings') || $show_srmi){ //mw_qbo_sync_activation_redirect
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-variation.php';
	}	
}else if($tab=='inventory'){
	if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')){
		#lpa
		if(!$MSQS_QL->is_plg_lc_p_l(false) && $MSQS_QL->get_qbo_company_info('is_sku_enabled')){
			if(isset($_GET['variation'])){
				require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-variation-inventory.php';
			}else{
				require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-inventory.php';
			}		
		}
	}
	
}else if($tab=='category'){
	if($enable_this=false && $MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')){
		if($MSQS_QL->get_qbo_company_info('is_category_enabled')){
			require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-category.php';
		}
	}
	
}else if($tab=='payment'){
	if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')){
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate')){
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
				echo '<h4>'.__('WooCommerce Orders as Sales Receipts Option Enabled in Settings. ','mw_wc_qbo_sync'). '</h4>';
			}else{
				require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-payment.php';
			}
		}
	}
		
}elseif($tab=='vendor'){
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-vendor.php';
}else{
	/*
	if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt')){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-customer.php';
	}else{
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-invoice.php';
	}
	*/
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-push-dashboard.php';
}
?>