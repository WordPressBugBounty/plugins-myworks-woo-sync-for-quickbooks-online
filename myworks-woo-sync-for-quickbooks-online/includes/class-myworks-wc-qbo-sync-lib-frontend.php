<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



class MyWorks_WC_QBO_Sync_QBO_Lib_Frontend {

	public function __construct() {
		$qrts = get_option('mw_wc_qbo_sync_wam_mng_inv_qrts');
		if(empty($qrts)) { $qrts = 'Invoice';}
		if(get_option('mw_wc_qbo_sync_wam_mng_inv_ed') == 'true') { 

			if($qrts == 'Invoice' || $qrts == 'Invoice_SalesReceipt' || $qrts == 'Inv_CMemo' || $qrts == 'All') {

				add_shortcode( 'myworks_quickbooks_manage_invoice', array($this, 'mw_qbo_invoice_user_management'));
				add_action( 'init', array($this,'invoice_user_management_account_endpoints' ) );
				add_filter( 'woocommerce_account_menu_items', array($this, 'invoice_user_management_account_menu_items'));
				add_action( 'woocommerce_account_invoices_endpoint', array($this,'invoice_user_management_endpoint_content' ));
			}
			
			if($qrts == 'SalesReceipt' || $qrts == 'Invoice_SalesReceipt' || $qrts == 'All') {

				add_shortcode( 'myworks_quickbooks_manage_salesreceipt', array($this, 'mw_qbo_salesreceipt_user_management'));
				add_action( 'init', array($this,'salesreceipt_user_management_account_endpoints' ) );
				add_filter( 'woocommerce_account_menu_items', array($this, 'salesreceipt_user_management_account_menu_items'));
				add_action( 'woocommerce_account_salesreceipts_endpoint', array($this,'salesreceipt_user_management_endpoint_content' ));
			}
			
			if($qrts == 'CreditMemo' || $qrts == 'Inv_CMemo' || $qrts == 'All') {

				add_shortcode( 'myworks_quickbooks_manage_creditmemo', array($this, 'mw_qbo_creditmemo_user_management'));
				add_action( 'init', array($this,'creditmemo_user_management_account_endpoints' ) );
				add_filter( 'woocommerce_account_menu_items', array($this, 'creditmemo_user_management_account_menu_items'));
				add_action( 'woocommerce_account_creditmemos_endpoint', array($this,'creditmemo_user_management_endpoint_content' ));
			}
		}


	}
	
	public function invoice_user_management_account_endpoints() {
	    add_rewrite_endpoint( 'invoices', EP_PAGES );
	    if(get_option('mw_wc_qbo_sync_acc_inv_shortcode') != 'true') {
	    	flush_rewrite_rules();
	    	update_option('mw_wc_qbo_sync_acc_inv_shortcode','true',true);
	    }
	}

	public function invoice_user_management_account_menu_items( $items ) {
	    $items['invoices'] = __( 'Invoices' );
	    return $items;
	}
	
	public function invoice_user_management_endpoint_content() {
	    echo do_shortcode('[myworks_quickbooks_manage_invoice]');
	}
	
	
	public function mw_qbo_invoice_user_management( $attr ) {

		
		$attr = shortcode_atts(
			array(
				"per_page" => 10,
				"order" => "Id",
				"order_by" => "DESC"
			),
			$attr
		);
		ob_start();
		include plugin_dir_path(__DIR__ ) . 'public/partials/myworks-wc-qbo-sync-public-shortcode-user-manage-invoice.php';
		return ob_get_clean();
	}

	/******* Sales Receipt ******/

	public function salesreceipt_user_management_account_endpoints() {
	    add_rewrite_endpoint( 'salesreceipts', EP_PAGES );
		#mw_wc_qbo_sync_acc_sr_shortcode
	    if(get_option('mw_wc_qbo_sync_acc_inv_shortcode') != 'true') {
	    	flush_rewrite_rules();
	    	update_option('mw_wc_qbo_sync_acc_inv_shortcode','true',true);
	    }
	}
	
	public function salesreceipt_user_management_account_menu_items( $items ) {
	    $items['salesreceipts'] = __( 'Sales Receipts' );
	    return $items;
	}

	public function salesreceipt_user_management_endpoint_content() {
	    echo do_shortcode('[myworks_quickbooks_manage_salesreceipt]');
	}

	public function mw_qbo_salesreceipt_user_management( $attr ) {

		
		$attr = shortcode_atts(
			array(
				"per_page" => 10,
				"order" => "Id",
				"order_by" => "DESC"
			),
			$attr
		);
		ob_start();
		include plugin_dir_path(__DIR__ ) . 'public/partials/myworks-wc-qbo-sync-public-shortcode-user-manage-salesreceipt.php';
		return ob_get_clean();
	}
	
	/******* Credit Memo ******/
	
	public function creditmemo_user_management_account_endpoints() {
	    add_rewrite_endpoint( 'creditmemos', EP_PAGES );
		#mw_wc_qbo_sync_acc_cm_shortcode
	    if(get_option('mw_wc_qbo_sync_acc_inv_shortcode') != 'true') {
	    	flush_rewrite_rules();
	    	update_option('mw_wc_qbo_sync_acc_inv_shortcode','true',true);
	    }
	}
	
	public function creditmemo_user_management_account_menu_items( $items ) {
	    $items['creditmemos'] = __( 'Credit Memos' );
	    return $items;
	}
	
	public function creditmemo_user_management_endpoint_content() {
	    echo do_shortcode('[myworks_quickbooks_manage_creditmemo]');
	}
	
	public function mw_qbo_creditmemo_user_management( $attr ) {

		
		$attr = shortcode_atts(
			array(
				"per_page" => 10,
				"order" => "Id",
				"order_by" => "DESC"
			),
			$attr
		);
		ob_start();
		include plugin_dir_path(__DIR__ ) . 'public/partials/myworks-wc-qbo-sync-public-shortcode-user-manage-creditmemo.php';
		return ob_get_clean();
	}
}

new MyWorks_WC_QBO_Sync_QBO_Lib_Frontend();