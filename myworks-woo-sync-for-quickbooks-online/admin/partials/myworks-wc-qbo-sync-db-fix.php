<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $MSQS_QL;
global $wpdb;
$server_db = $MSQS_QL->db_check_get_fields_details();
$error = false;

if(is_array($server_db) && count($server_db)){
	foreach($server_db as $k=>$v){
		$is_db_updated = false;
		if($k == $wpdb->prefix.'mw_wc_qbo_sync_payment_id_map'){
			if(!array_key_exists("is_wc_order",$v)){
				// Database schema management - use esc_sql for table name security
				$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_payment_id_map');
				// Use direct query with escaped table name for ALTER TABLE
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
				$wpdb->query("ALTER TABLE `{$table_name}` ADD `is_wc_order` INT(1) NOT NULL DEFAULT '0' AFTER `qbo_payment_id`;");
				$is_db_updated = true;
				
				$error = true;		
				echo '<div class="mw_qbo_sync_db_fix_section">
				<p class="mw_qbo_sync_db_fix_no_error">You had an issue with ' . esc_html($wpdb->prefix) . 'mw_wc_qbo_sync_payment_id_map table, and it got resolved now!.</p>
				</div>';
			}
		}
		
		if($k == $wpdb->prefix.'mw_wc_qbo_sync_paymentmethod_map'){
			if(!array_key_exists("ps_order_status",$v)){
				$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_paymentmethod_map');
				// Use direct query with escaped table name for ALTER TABLE
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
				$wpdb->query("ALTER TABLE `{$table_name}` ADD `ps_order_status` VARCHAR(255) NOT NULL AFTER `term_id`;");
				$is_db_updated = true;
				$error = true;
			}
			
			if(!array_key_exists("individual_batch_support",$v)){
				$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_paymentmethod_map');
				// Use direct query with escaped table name for ALTER TABLE
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
				$wpdb->query("ALTER TABLE `{$table_name}` ADD `individual_batch_support` INT(1) NOT NULL AFTER `ps_order_status`;");
				$is_db_updated = true;
				$error = true;
			}
			
			if(!array_key_exists("deposit_cron_utc",$v)){
				$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_paymentmethod_map');
				// Use direct query with escaped table name for ALTER TABLE
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
				$wpdb->query("ALTER TABLE `{$table_name}` ADD `deposit_cron_utc` VARCHAR(255) NOT NULL AFTER `individual_batch_support`;");
				$is_db_updated = true;
				$error = true;
			}
			if(!array_key_exists("inv_due_date_days",$v)){
				$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_paymentmethod_map');
				// Use direct query with escaped table name for ALTER TABLE
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
				$wpdb->query("ALTER TABLE `{$table_name}` ADD `inv_due_date_days` INT(3) NOT NULL AFTER `deposit_cron_utc`;");
				$is_db_updated = true;
				$error = true;
			}			
			
			if($error){
				echo '<div class="mw_qbo_sync_db_fix_section">
				<p class="mw_qbo_sync_db_fix_no_error">You had an issue with ' . esc_html($wpdb->prefix) . 'mw_wc_qbo_sync_paymentmethod_map table, and it got resolved now!.</p>
				</div>';
			}			
		}
		
		if($k == $wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'){
			if(!array_key_exists("ext_data",$v)){
				$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_wq_cf_map');
				// Use direct query with escaped table name for ALTER TABLE
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
				$wpdb->query("ALTER TABLE `{$table_name}` ADD `ext_data` TEXT NOT NULL AFTER `qb_field`;");
				$is_db_updated = true;
				
				$error = true;		
				echo '<div class="mw_qbo_sync_db_fix_section">
				<p class="mw_qbo_sync_db_fix_no_error">You had an issue with ' . esc_html($wpdb->prefix) . 'mw_wc_qbo_sync_wq_cf_map table, and it got resolved now!.</p>
				</div>';
			}
		}
	}
	
	/*New Tables*/
	$is_new_db_tbl_created = false;			
	if(!isset($server_db[$wpdb->prefix.'mw_wc_qbo_sync_variation_pairs'])){
		$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_variation_pairs');
		// Use direct query with escaped table name for CREATE TABLE
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$table_name}` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_variation_id` int(11) NOT NULL,
		`quickbook_product_id` int(11) NOT NULL,
		`class_id` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		$is_new_db_tbl_created = true;
		
		$error = true;		
		echo '<div class="mw_qbo_sync_db_fix_section">
		<p class="mw_qbo_sync_db_fix_no_error">You had an issue with ' . esc_html($wpdb->prefix) . 'mw_wc_qbo_sync_variation_pairs table, and it got resolved now!.</p>
		</div>';
	}
	
	if(!isset($server_db[$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'])){
		$table_name = esc_sql($wpdb->prefix . 'mw_wc_qbo_sync_wq_cf_map');
		// Use direct query with escaped table name for CREATE TABLE
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name safely escaped with esc_sql()
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$table_name}` (
		`id` int(11) NOT NULL AUTO_INCREMENT,								  
		`wc_field` varchar(255) NOT NULL,
		`qb_field` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		$is_new_db_tbl_created = true;
		
		$error = true;		
		echo '<div class="mw_qbo_sync_db_fix_section">
		<p class="mw_qbo_sync_db_fix_no_error">You had an issue with ' . esc_html($wpdb->prefix) . 'mw_wc_qbo_sync_wq_cf_map table, and it got resolved now!.</p>
		</div>';
	}
}

if(!$error){
	echo '<div class="mw_qbo_sync_db_fix_section">
	<p class="mw_qbo_sync_db_fix_no_error">You don\'t have any issue with database tables.</p>
	</div>';
}