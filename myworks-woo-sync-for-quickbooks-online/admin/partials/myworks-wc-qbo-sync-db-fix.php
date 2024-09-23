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
				$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_payment_id_map` ADD `is_wc_order` INT(1) NOT NULL DEFAULT '0' AFTER `qbo_payment_id`;";
				$wpdb->query($sql);
				$is_db_updated = true;
				
				$error = true;		
				echo '<div class="mw_qbo_sync_db_fix_section">
				<p class="mw_qbo_sync_db_fix_no_error">You had an issue with '.$wpdb->prefix.'mw_wc_qbo_sync_payment_id_map'.' table, and it got resolved now!.</p>
				</div>';
			}
		}
		
		if($k == $wpdb->prefix.'mw_wc_qbo_sync_paymentmethod_map'){
			if(!array_key_exists("ps_order_status",$v)){
				$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `ps_order_status` VARCHAR(255) NOT NULL AFTER `term_id`;";
				$wpdb->query($sql);
				$is_db_updated = true;
				$error = true;
			}
			
			if(!array_key_exists("individual_batch_support",$v)){
				$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `individual_batch_support` INT(1) NOT NULL AFTER `ps_order_status`;";
				$wpdb->query($sql);
				$is_db_updated = true;
				$error = true;
			}
			
			if(!array_key_exists("deposit_cron_utc",$v)){
				$sql = "LTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `deposit_cron_utc` VARCHAR(255) NOT NULL AFTER `individual_batch_support`;";
				$wpdb->query($sql);
				$is_db_updated = true;
				$error = true;
			}
			if(!array_key_exists("inv_due_date_days",$v)){
				$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `inv_due_date_days` INT(3) NOT NULL AFTER `deposit_cron_utc`;";
				$wpdb->query($sql);
				$is_db_updated = true;
				$error = true;
			}			
			
			if($error){
				echo '<div class="mw_qbo_sync_db_fix_section">
				<p class="mw_qbo_sync_db_fix_no_error">You had an issue with '.$wpdb->prefix.'mw_wc_qbo_sync_paymentmethod_map'.' table, and it got resolved now!.</p>
				</div>';
			}			
		}
		
		if($k == $wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'){
			if(!array_key_exists("ext_data",$v)){
				$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_wq_cf_map` ADD `ext_data` TEXT NOT NULL AFTER `qb_field`;";
				$wpdb->query($sql);
				$is_db_updated = true;
				
				$error = true;		
				echo '<div class="mw_qbo_sync_db_fix_section">
				<p class="mw_qbo_sync_db_fix_no_error">You had an issue with '.$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'.' table, and it got resolved now!.</p>
				</div>';
			}
		}
	}
	
	/*New Tables*/
	$is_new_db_tbl_created = false;			
	if(!isset($server_db[$wpdb->prefix.'mw_wc_qbo_sync_variation_pairs'])){
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'mw_wc_qbo_sync_variation_pairs` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`wc_variation_id` int(11) NOT NULL,
		`quickbook_product_id` int(11) NOT NULL,
		`class_id` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		$wpdb->query($sql);
		$is_new_db_tbl_created = true;
		
		$error = true;		
		echo '<div class="mw_qbo_sync_db_fix_section">
		<p class="mw_qbo_sync_db_fix_no_error">You had an issue with '.$wpdb->prefix.'mw_wc_qbo_sync_variation_pairs'.' table, and it got resolved now!.</p>
		</div>';
	}
	
	if(!isset($server_db[$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'])){
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map` (
		`id` int(11) NOT NULL AUTO_INCREMENT,								  
		`wc_field` varchar(255) NOT NULL,
		`qb_field` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		$wpdb->query($sql);
		$is_new_db_tbl_created = true;
		
		$error = true;		
		echo '<div class="mw_qbo_sync_db_fix_section">
		<p class="mw_qbo_sync_db_fix_no_error">You had an issue with '.$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'.' table, and it got resolved now!.</p>
		</div>';
	}
}

if(!$error){
	echo '<div class="mw_qbo_sync_db_fix_section">
	<p class="mw_qbo_sync_db_fix_no_error">You don\'t have any issue with database tables.</p>
	</div>';
}