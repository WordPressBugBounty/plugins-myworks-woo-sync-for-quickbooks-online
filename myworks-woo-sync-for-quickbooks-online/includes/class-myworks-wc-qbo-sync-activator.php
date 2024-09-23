<?php

/**
 * Fired during plugin activation
 *
 * @link       http://myworks.design/software/wordpress/woocommerce/myworks-wc-qbo-sync
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/includes
 * @author     My Works <support@myworks.design>
 */

class MyWorks_WC_QBO_Sync_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		/*
		if (!extension_loaded('mcrypt'))
		die(__('This plugin requires <a target="_blank" href="http://php.net/manual/en/book.mcrypt.php">PHP Mcrypt Extension loaded into your server</a> to be active!', 'mw_wc_qbo_sync'));
		*/
	    
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		/*
		if(MyWorks_WC_QBO_Sync_Activator::activation_invalid_chars_in_db_conn_info()){

			$error_message = __('MyWorks QuickBooks Online Sync for WooCommerce does not support these special characters in your database password in your wp-config.php file:  (+ / # % ‘ ?)  Please update your database password to not include these characters.', 'mw_wc_qbo_sync');
			die($error_message);
		}
		*/
		
		$chk_if_woo_is_active = false;
		if (is_multisite()) {
			$chk_if_woo_is_active = MyWorks_WC_QBO_Sync_Activator::check_if_woocommerce_active_in_network();
			//$chk_if_woo_is_active = true;
		}else{
			$chk_if_woo_is_active = MyWorks_WC_QBO_Sync_Activator::check_if_woocommerce_active();
		}
		
		if ($chk_if_woo_is_active) {
		    
		    $current_blog = $wpdb->blogid;

		    $is_plugin_activate = true;
			$is_pos_plugin_active = false;
			if (class_exists( 'MW_QBO_Desktop_Sync_Qwc_Server_Lib' ) && in_array('myworks-quickbooks-desktop-sync/myworks-quickbooks-desktop-sync.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ))) {
				$is_plugin_activate = false;
			}
			if (class_exists( 'MW_QBO_Desktop_Sync_Qwc_Server_Lib' ) && in_array('myworks-quickbooks-pos-sync/myworks-quickbooks-pos-sync.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ))) {
				$is_pos_plugin_active = true;
				$is_plugin_activate = false;
			}
			if (!$is_plugin_activate) {
				if (!$is_pos_plugin_active) {
					$error_message = __('Plugin conflict - QuickBooks Desktop plugin is already activate', 'mw_wc_qbo_sync');
				} else {
					$error_message = __('Plugin conflict - QuickBooks POS plugin is already activate', 'mw_wc_qbo_desk');
				}
				die($error_message);
				
			}
		    
		    if(!empty( $current_blog )) {
		        switch_to_blog($current_blog);
		    }
		    
		    
			MyWorks_WC_QBO_Sync_Activator::create_databases();
			MyWorks_WC_QBO_Sync_Activator::do_after_activate();
			//
			MyWorks_WC_QBO_Sync_Activator::activation_pointer_add();

			return $is_plugin_activate;
		} else {
			$error_message = __('This plugin requires <a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!', 'mw_wc_qbo_sync');
			die($error_message);
		}
	}
	
	protected static function activation_pointer_add(){
		$admin_pointer_content = '<h3>' . __( 'MyWorks Sync' ) . '</h3>';
		$admin_pointer_content .= '<p>' . __( 'Automatically sync your WooCommerce store with QuickBooks Online! Get started with setup here, and check out our documentation, setup videos or a setup call (for paid plans) to get up and running right away!' ) . '</p>';
		update_option('mw_wc_qbo_sync_admin_pointers', $admin_pointer_content);
		delete_option( 'mw_wc_qbo_sync_deactivation_popup' );
	}
	
	protected function activation_invalid_chars_in_db_conn_info(){

		$invalid_chars = array('+','/','#','%','\'','?');
		foreach($invalid_chars as $char){
			if( strpos( DB_USER, $char ) !== false || strpos( DB_PASSWORD, $char ) !== false || strpos( DB_HOST, $char ) !== false || strpos( DB_NAME, $char ) !== false) {
				return true;
			}
		}
		return false;
	}
	
	protected static function check_if_woocommerce_active_in_network() {
		if(class_exists( 'WooCommerce' )) {
			return true;
		}
		
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		
		if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}
		
		return false;
	}
	
	protected static function check_if_woocommerce_active() {
		if(class_exists( 'WooCommerce' ) && in_array('woocommerce/woocommerce.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ))){
			return true;
		}
		return false;
	}
	
	protected static function create_databases(){

		global $wpdb;
		$sql = array();

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`item_type` varchar(255) NOT NULL,
		`item_action` varchar(255) NOT NULL,
		`woocommerce_hook` varchar(255) NOT NULL,
		`item_id` int(11) NOT NULL,
		`run` int(1) NOT NULL,
		`success` int(1) NOT NULL,
		`added_date` datetime NOT NULL,
		PRIMARY KEY (`id`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_history` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`item_type` varchar(255) NOT NULL,
		`item_action` varchar(255) NOT NULL,
		`woocommerce_hook` varchar(255) NOT NULL,
		`item_id` int(11) NOT NULL,
		`run` int(1) NOT NULL,
		`success` int(1) NOT NULL,
		`added_date` datetime NOT NULL,
		`dt_str` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_customer_pairs` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_customerid` int(11) NOT NULL,
		`qbo_customerid` int(11) NOT NULL,
		PRIMARY KEY (`id`)
		)";
		
		/*
		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_vendor_pairs` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_customerid` int(11) NOT NULL,
		`qbo_vendorid` int(11) NOT NULL,
		PRIMARY KEY (`id`)
		)";
		*/
		
		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_payment_id_map` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_payment_id` int(11) NOT NULL,
		`qbo_payment_id` int(11) NOT NULL,
		/*Peter added below on 08/04/17 */
		`is_wc_order` INT(1) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		)";
		/*
		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_promo_code_product_map` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`promo_id` int(11) NOT NULL,
		`qbo_product_id` int(11) NOT NULL,
		`class_id` VARCHAR( 255 ) NOT NULL,
		PRIMARY KEY (`id`)
		)";
		*/

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_shipping_product_map` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_shippingmethod` varchar(255) NOT NULL,
		`qbo_product_id` int(11) NOT NULL,
		`class_id` VARCHAR( 255 ) NOT NULL,
		PRIMARY KEY (`id`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_paymentmethod` varchar(255) NOT NULL,
		`qbo_account_id` int(11) NOT NULL,
		`currency` varchar(255) NOT NULL,
		`enable_transaction` int(11) NOT NULL,
		`txn_expense_acc_id` int(11) NOT NULL,
		`enable_payment` int(1) NOT NULL,
		`txn_refund` int(1) NOT NULL,
		`enable_refund` int(1) NOT NULL,
		`enable_batch` int(1) NOT NULL,
		`udf_account_id` int(11) NOT NULL,
		`vendor_id` int(11) NOT NULL,
		`qb_p_method_id` int(11) NOT NULL,
		`lump_weekend_batches` int(1) NOT NULL,
		`term_id` int(11) NOT NULL,
		`ps_order_status` varchar(255) NOT NULL,
		`individual_batch_support` int(1) NOT NULL,
		`deposit_cron_utc` varchar(255) NOT NULL,
		`deposit_cron_sch` varchar(255) NOT NULL,
		`inv_due_date_days` int(3) NOT NULL,
		`order_sync_as` varchar(255) NOT NULL,
		`deposit_date_field` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_tax_map` (
		`id` int(11) NOT NULL AUTO_INCREMENT,              
		`wc_tax_id` int(11) NOT NULL,
		`qbo_tax_code` varchar(255) NOT NULL,
		`wc_tax_id_2` int(11) NOT NULL,
		PRIMARY KEY (`id`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_log` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`log_title` varchar(255) NOT NULL,
		`details` text NOT NULL,
		`success` int(11) NOT NULL,
		`log_type` varchar(255) NOT NULL,
		`added_date` datetime NOT NULL,
		PRIMARY KEY (`id`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_qbo_customers` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`qbo_customerid` int(11) NOT NULL,
		`first` varchar(255) NOT NULL,
		`middle` varchar(255) NOT NULL,
		`last` varchar(255) NOT NULL,
		`company` varchar(255) NOT NULL,
		`dname` varchar(255) NOT NULL,
		`email` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		)";
		
		/*
		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_qbo_vendors` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`qbo_vendorid` int(11) NOT NULL,
		`first` varchar(255) NOT NULL,
		`middle` varchar(255) NOT NULL,
		`last` varchar(255) NOT NULL,
		`company` varchar(255) NOT NULL,
		`dname` varchar(255) NOT NULL,
		`pocname` varchar(255) NOT NULL,
		`email` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		)";
		*/

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_product_pairs` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_product_id` int(11) NOT NULL,
		`quickbook_product_id` int(11) NOT NULL,
		`class_id` VARCHAR( 255 ) NOT NULL,
		PRIMARY KEY (`id`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_qbo_items` (
		`ID` int(11) NOT NULL AUTO_INCREMENT,
		`itemid` int(11) NOT NULL,
		`name` varchar(255) NOT NULL,
		`sku` varchar(255) NOT NULL,
		`product_type` varchar(255) NOT NULL,
		`parent_id` int(11) NOT NULL,
		PRIMARY KEY (`ID`)
		)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_variation_pairs` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_variation_id` int(11) NOT NULL,
		`quickbook_product_id` int(11) NOT NULL,
		`class_id` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		)";
		
		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_wq_cf_map` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,								  
		  `wc_field` TEXT NOT NULL,
		  `qb_field` varchar(255) NOT NULL,
		  `ext_data` TEXT NOT NULL,
		  PRIMARY KEY (`id`)
		)";
		
		#New
		$sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_sessions` (
		  `session_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		  `session_key` char(32) NOT NULL,
		  `session_value` longtext NOT NULL,
		  `session_expiry` BIGINT UNSIGNED NOT NULL,
		  PRIMARY KEY (`session_id`),
		  UNIQUE KEY `session_key` (`session_key`)
		)";
		
		if($sql){

			foreach($sql as $query){

				$wpdb->query($query);

			}

		}

		#New - Empty Check - Add Default Value
		$pa_op_ecav_arr = array(
			'mw_wc_qbo_sync_save_log_for' => 30,
			'mw_wc_qbo_sync_tax_format' => 'TaxExclusive',
			'mw_wc_qbo_sync_connection_number' => 1,
			'mw_wc_qbo_sync_rt_push_enable' => 'true',
			#'mw_wc_qbo_sync_select2_ajax' => 'true',
			'mw_wc_qbo_sync_select2_status' => 'true',
			'mw_wc_qbo_sync_rt_push_items' => 'Invoice,Payment,Refund',
			'mw_wc_qbo_sync_store_currency' => get_option('woocommerce_currency'),
			'mw_wc_qbo_sync_specific_order_status' => 'wc-processing,wc-completed',
			'mw_wc_qbo_sync_fresh_install' => 'true',
			'mw_wc_qbo_sync_invnt_pull_set_prd_stock_sts' => 'true',
			#'mw_wc_qbo_sync_order_as_sales_receipt' => 'true',
			'mw_wc_qbo_sync_order_qbo_sync_as' => 'SalesReceipt',
			'mw_wc_qbo_sync_pause_up_qbo_conection' => 'true',
			'mw_wc_qbo_sync_hide_vpp_fmp_pages' => 'true',
			'mw_wc_qbo_sync_wc_cust_role' => 'administrator,subscriber',
			'mw_wc_qbo_sync_customer_match_by_name' => 'true',
			'mw_wc_qbo_sync_ca_ruso_dqs' => 'true',
			'mw_wc_qbo_sync_product_pull_desc_field' => 'none',
			'mw_wc_qbo_sync_qb_pmnt_ref_num_vf' => 'O_ID_NUM',
			'mw_wc_qbo_sync_queue_cron_interval_time' => 'MWQBO_5min',
			'mw_wc_qbo_sync_ivnt_pull_interval_time' => 'MWQBO_5min',
			'mw_wc_qbo_sync_ignore_cdc_for_invnt_import' => 'true',
			#'mw_wc_qbo_sync_compt_np_oli_fee_sync' => 'true',
			'mw_wc_qbo_sync_won_qbf_sync' => 'CustomerMemo',
			'mw_wc_qbo_sync_sync_product_images_pp' => 'true',
		);
		
		foreach($pa_op_ecav_arr as $k => $v){
			if($k == 'mw_wc_qbo_sync_rt_push_items' || $k == 'mw_wc_qbo_sync_specific_order_status'){
				if(!empty(get_option('mw_wc_qbo_sync_queue_cron_interval_time'))){
					continue;
				}
			}
			
			$eov = get_option($k);
			if(empty($eov) || $eov == 'false'){
				update_option($k,$v,false);
			}
		}
		
		//01-08-2017
		if(isset($_SESSION['mw_wc_qbo_sync_qbo_con_creds'])){
			unset($_SESSION['mw_wc_qbo_sync_qbo_con_creds']);
		}
		
		if(isset($_SESSION['mw_wc_qbo_sync_qbo_is_connected_rts'])){
			unset($_SESSION['mw_wc_qbo_sync_qbo_is_connected_rts']);
		}
		
		if(isset($_SESSION['mw_wc_qbo_sync_rts_license_data'])){
			unset($_SESSION['mw_wc_qbo_sync_rts_license_data']);
		}
		
		if(isset($_SESSION['mw_wc_qbo_sync_mwqs_session_msg'])){
			unset($_SESSION['mw_wc_qbo_sync_mwqs_session_msg']);
		}
		

	}
	
	protected static function do_after_activate(){
		$url = get_bloginfo('url');
		//wpurl
		
		$company = get_bloginfo('name');
		$email = get_bloginfo('admin_email');
		$wordpress_version = get_bloginfo('version');		
		
		$MyWorks_WC_QBO_Sync = new MyWorks_WC_QBO_Sync();
		$version = $MyWorks_WC_QBO_Sync->get_version();
		
		global $woocommerce;
		$woocommerce_version = $woocommerce->version;
		
		$message = '';
		$message .= "<b>WooCommerce Sync for QuickBooks Online</b></br>";
		$message .= "</br>";
		$message .= "<b>Company:</b> ".$company."</br>";
		$message .= "<b>Email:</b> ".$email."</br>";
		$message .= "<b>WooCommerce URL:</b> ".$url ."</br>";
		$message .= "<b>Wordpress Version:</b> ".$wordpress_version ."</br>";
		$message .= "<b>WooCommerce Version:</b> ".$woocommerce_version ."</br>";
		
		
		$headers = array(
			'MIME-Version: 1.0',
			'Content-type:text/html;charset=UTF-8',
		);
		
		$to = 'notifications@myworks.design';		
		
		wp_mail($to, 'New Install - WooCommerce Sync', $message, $headers);
		
		$post_url = 'https://myworks.design/dashboard/api/dashboard/product/saveModule';
		
		$params = array(
			'api_version'=>'0.1',
			'result_type'=>'json',
			'process'=>'activated',
			'version'=>$version,
			'company'=>$company,
			'email'=>$email,
			'system_url'=>$url
		);		
		
		wp_remote_post($post_url, [
			'timeout' => 30,
			'body' => $params,
		]);
		
		#New
		global $wpdb;
		$table = $wpdb->prefix.'mw_wc_qbo_sync_log';

		$ld = '';
		$current_user = wp_get_current_user();
		if(is_object($current_user) && !empty($current_user)){
			$cu_name = $current_user->data->display_name;
			$ld = $cu_name;

			if(isset($current_user->roles) && is_array($current_user->roles) && !empty($current_user->roles)){
				$cu_role = $current_user->roles[0];
				$ld .= ' ('.$cu_role.')';
			}
			
		}	

		$log_data = array();
		$log_data['log_title'] = 'Plugin Activated';
		$log_data['details'] = 'Wordpress User: '.$ld;
		$log_data['log_type'] = 'Plugin';
		$log_data['success'] = 3;
		$log_data['added_date'] = date('Y-m-d H:i:s');
		
		$wpdb->insert($table, $log_data);
	}
}