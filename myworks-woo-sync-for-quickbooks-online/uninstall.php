<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://myworks.design/
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$sql = "DROP TABLE `{$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue`, `{$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_history`, `{$wpdb->prefix}mw_wc_qbo_sync_customer_pairs`, `{$wpdb->prefix}mw_wc_qbo_sync_payment_id_map` , `{$wpdb->prefix}mw_wc_qbo_sync_shipping_product_map`, `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map`, `{$wpdb->prefix}mw_wc_qbo_sync_tax_map`, `{$wpdb->prefix}mw_wc_qbo_sync_log`, `{$wpdb->prefix}mw_wc_qbo_sync_qbo_customers`, `{$wpdb->prefix}mw_wc_qbo_sync_product_pairs`, `{$wpdb->prefix}mw_wc_qbo_sync_qbo_items`, `{$wpdb->prefix}mw_wc_qbo_sync_variation_pairs`, `{$wpdb->prefix}mw_wc_qbo_sync_wq_cf_map` ";

//, `{$wpdb->prefix}mw_wc_qbo_sync_promo_code_product_map`

$wpdb->query($sql);

$registered_options = array(
'mw_wc_qbo_sync_sandbox_mode',
'mw_wc_qbo_sync_default_qbo_item',
'mw_wc_qbo_sync_default_qbo_product_account',
'mw_wc_qbo_sync_default_qbo_asset_account',
'mw_wc_qbo_sync_default_qbo_expense_account',
'mw_wc_qbo_sync_default_qbo_discount_account',
'mw_wc_qbo_sync_default_coupon_code',
'mw_wc_qbo_sync_default_shipping_product',
'mw_wc_qbo_sync_order_as_sales_receipt',
'mw_wc_qbo_sync_invoice_min_id',
'mw_wc_qbo_sync_null_invoice',
'mw_wc_qbo_sync_invoice_notes',
'mw_wc_qbo_sync_invoice_note_id',
'mw_wc_qbo_sync_invoice_note_name',
'mw_wc_qbo_sync_invoice_cancelled',
'mw_wc_qbo_sync_invoice_memo',
'mw_wc_qbo_sync_invoice_memo_statement',
'mw_wc_qbo_sync_invoice_date',
'mw_wc_qbo_sync_tax_rule',
'mw_wc_qbo_sync_tax_format',
'mw_wc_qbo_sync_append_client',
'mw_wc_qbo_sync_display_name_pattern',
'mw_wc_qbo_sync_client_sort_order',
'mw_wc_qbo_sync_client_check_email',
'mw_wc_qbo_sync_pull_enable',
'mw_wc_qbo_sync_product_pull_wc_status',
'mw_wc_qbo_sync_product_pull_desc_field',
'mw_wc_qbo_sync_auto_pull_client',
'mw_wc_qbo_sync_auto_pull_invoice',
'mw_wc_qbo_sync_auto_pull_payment',
'mw_wc_qbo_sync_auto_pull_limit',
'mw_wc_qbo_sync_auto_pull_interval',
'mw_wc_qbo_sync_webhook_enable',
'mw_wc_qbo_sync_webhook_items',
'mw_wc_qbo_sync_rt_push_enable',
'mw_wc_qbo_sync_rt_push_items',
'mw_wc_qbo_sync_disable_realtime_sync',
'mw_wc_qbo_sync_disable_sync_status',
'mw_wc_qbo_sync_disable_realtime_client_update',
'mw_wc_qbo_sync_enable_invoice_prefix',
'mw_wc_qbo_sync_qbo_invoice',
'mw_wc_qbo_sync_email_log',
'mw_wc_qbo_sync_save_log_for',
'mw_wc_qbo_sync_err_add_item_obj_into_log_file',
'mw_wc_qbo_sync_license',
'mw_wc_qbo_sync_connection_number',
'mw_wc_qbo_sync_access_token',
'mw_wc_qbo_sync_localkey',
'mw_qbo_sync_activation_redirect',
'mw_wc_qbo_sync_qbo_is_connected',
'mw_wc_qbo_sync_qbo_is_refreshed',
'mw_wc_qbo_sync_qbo_is_default_settings',
'mw_wc_qbo_sync_qbo_is_data_mapped',
'mw_wc_qbo_sync_qbo_is_data_mapped_customer',
'mw_wc_qbo_sync_qbo_is_data_mapped_product',
'mw_wc_qbo_sync_qbo_is_data_mapped_payment',
'mw_wc_qbo_sync_qbo_is_init',
'mw_wc_qbo_sync_update_option',
'mw_wc_qbo_sync_auto_refresh',
'mw_wc_qbo_sync_admin_email',
'mw_wc_qbo_sync_customer_qbo_check',
'mw_wc_qbo_sync_select2_status',
'mw_wc_qbo_sync_select2_ajax',
'mw_wc_qbo_sync_orders_to_specific_cust',
'mw_wc_qbo_sync_orders_to_specific_cust_opt',
'mw_wc_qbo_sync_store_currency',
'mw_wc_qbo_sync_specific_order_status',
'mw_wc_qbo_sync_measurement_qty',
'mw_wc_qbo_sync_compt_gf_qbo_is',
'mw_wc_qbo_sync_compt_gf_qbo_item',
'mw_wc_qbo_sync_compt_gf_qbo_is_gbf',
'mw_wc_qbo_sync_compt_wccf_fee',
'mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map',
'mw_wc_qbo_sync_compt_p_wod',
'mw_wc_qbo_sync_compt_p_wsnop',
'mw_wc_qbo_sync_compt_wpbs',
'mw_wc_qbo_sync_compt_wpbs_ap_item',
'mw_wc_qbo_sync_customer_qbo_check_ship_addr',
'mw_wc_qbo_sync_compt_wchau_enable',
'mw_wc_qbo_sync_compt_wchau_wf_qi_map',
'mw_wc_qbo_sync_compt_p_wacof',
'mw_wc_qbo_sync_compt_p_wacof_m_field',
'mw_wc_qbo_sync_compt_acof_wf_qi_map',
'mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses',
'mw_wc_qbo_sync_pmnt_pull_order_status',
'mw_wc_qbo_sync_w_shp_track',
'mw_wc_qbo_sync_wcfep_add_fld',
'mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map',
'mw_wc_qbo_sync_update_option_date',
'mw_wc_qbo_sync_wc_qbo_product_desc',
'mw_wc_qbo_sync_qbo_push_invoice_date',
'mw_wc_qbo_sync_wc_avatax_support',
'mw_wc_qbo_sync_cron_notice_status',
'mw_wc_qbo_sync_db_fix',
'mw_wc_qbo_sync_session_cn_ls_chk',
'mw_wc_qbo_sync_wc_cust_role',
'mw_wc_qbo_sync_customer_qbo_check_billing_company',
'mw_wc_qbo_sync_compt_p_wtmepo',
'mw_wc_qbo_sync_force_shipping_line_item',
'mw_wc_qbo_sync_wc_taxify_support',
'mw_wc_qbo_sync_wc_taxify_map_qbo_product',
'mw_wc_qbo_sync_enable_wc_deposit',
'mw_wc_qbo_sync_queue_cron_interval_time',
'mw_wc_qbo_sync_hide_vpp_fmp_pages',
'mw_wc_qbo_sync_qb_func_af_plg_act_run',

'mw_wc_qbo_sync_compt_p_wconmkn',
'mw_qbo_sync_iishc_cdc_last_inv_impt_timestamp',

'mw_wc_qbo_sync_os_skip_uprice_l_item',
'mw_wc_qbo_sync_sync_skip_cf_ibs_addr',
'mw_wc_qbo_sync_use_qb_ba_for_eqc',
'mw_wc_qbo_sync_os_price_fp_update',
'mw_wc_qbo_sync_block_new_cus_sync_qb',
);

foreach($registered_options as $option){
	delete_option( $option );
}

//
wp_clear_scheduled_hook('mw_qbo_sync_logging_hook');