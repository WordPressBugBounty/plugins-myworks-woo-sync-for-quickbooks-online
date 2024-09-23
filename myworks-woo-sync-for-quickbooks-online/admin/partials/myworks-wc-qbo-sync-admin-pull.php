<?php
if ( ! defined( 'ABSPATH' ) )
exit;
 
global $MSQS_QL;
MyWorks_WC_QBO_Sync_Admin::is_trial_version_check();

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-nav.php' ?>

<?php
$tab = isset($_GET['tab']) ? $_GET['tab'] : '' ;
if($tab=='inventory'){
	#lpa
	if(!$MSQS_QL->is_plg_lc_p_l(false) && $MSQS_QL->get_qbo_company_info('is_sku_enabled')){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-inventory.php';
	}	
}else if($tab=='customer'){
	echo '<h4>'.__('Coming Soon...','mw_wc_qbo_sync').'</h4>';
	//require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-customer.php';
}else if($tab=='invoice'){
	echo '<h4>'.__('Coming Soon...','mw_wc_qbo_sync').'</h4>';
	//require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-invoice.php';
}else if($tab=='product'){	
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-product.php';
}else if($tab=='payment'){
	echo '<h4>'.__('Coming Soon...','mw_wc_qbo_sync').'</h4>';
	//require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-payment.php';
}else if($tab=='category'){
	if(!$MSQS_QL->is_plg_lc_p_l() && $MSQS_QL->get_qbo_company_info('is_category_enabled')){
		require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-category.php';
	}	
}else{
	//require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-product.php';
	//echo '<h4>'.__('Coming Soon...','mw_wc_qbo_sync').'</h4>';
	//require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-customer.php';
	
	require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-pull-dashboard.php';
}
?>