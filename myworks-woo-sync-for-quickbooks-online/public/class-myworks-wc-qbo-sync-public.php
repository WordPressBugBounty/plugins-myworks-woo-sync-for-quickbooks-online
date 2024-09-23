<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://myworks.design/software/wordpress/woocommerce/myworks-wc-qbo-sync
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/includes
 * @author     My Works <support@myworks.design>
 */
class MyWorks_WC_QBO_Sync_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/myworks-wc-qbo-sync-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/myworks-wc-qbo-sync-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the connection token.
	 *
	 * @since    1.0.0
	 */
	public function mw_qbo_sync_public_api_init_internal(){
		add_rewrite_rule( 'myworks-wc-qbo-sync-public-api.php$', 'index.php?mw_qbo_sync_public_api=1', 'top' );
		
		add_rewrite_rule( 'myworks-wc-qbo-sync-public-qbo-webhooks.php$', 'index.php?mw_qbo_sync_public_qbo_webhooks=1', 'top' );
		//Cron
		add_rewrite_rule( 'myworks-wc-qbo-sync-public-quick-refresh.php$', 'index.php?mw_qbo_sync_public_quick_refresh=1', 'top' );
		//Sync Window (Push/Pull/Admin login required)
		add_rewrite_rule( 'myworks-wc-qbo-sync-public-sync-window.php$', 'index.php?mw_qbo_sync_public_sync_window=1', 'top' );
		
		add_rewrite_rule( 'myworks-wc-qbo-sync-public-json-item-list.php$', 'index.php?mw_qbo_sync_public_get_json_item_list=1', 'top' );
		
		add_rewrite_rule( 'myworks-wc-qbo-sync-public-deposit-cron.php$', 'index.php?mw_qbo_sync_public_deposit_cron=1', 'top' );
		
		//
		add_rewrite_rule( 'myworks-wc-qbo-sync-public-user-invoice-pdf.php$', 'index.php?mw_qbo_sync_public_get_user_invoice_pdf=1', 'top' );
		
		
	}
	
	public function  mw_qbo_sync_public_api_query_vars( $query_vars ){
		$query_vars[] = 'mw_qbo_sync_public_api';
		$query_vars[] = 'mw_qbo_sync_public_qbo_webhooks';
		$query_vars[] = 'mw_qbo_sync_public_quick_refresh';
		$query_vars[] = 'mw_qbo_sync_public_sync_window';
		$query_vars[] = 'mw_qbo_sync_public_get_json_item_list';
		
		$query_vars[] = 'mw_qbo_sync_public_deposit_cron';
		
		$query_vars[] = 'mw_qbo_sync_public_get_user_invoice_pdf';
		
		
		return $query_vars;
	}
	
	public function mw_qbo_sync_public_api_request($wp) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//& omitted
		
		if ( array_key_exists( 'mw_qbo_sync_public_api', $wp->query_vars ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-public-api.php';
			exit();
		}
		
		if ( array_key_exists( 'mw_qbo_sync_public_qbo_webhooks', $wp->query_vars ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-public-qbo-webhooks.php';
			exit();
		}
		
		if ( array_key_exists( 'mw_qbo_sync_public_quick_refresh', $wp->query_vars ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-public-quick-refresh.php';
			exit();
		}
		
		if ( array_key_exists( 'mw_qbo_sync_public_sync_window', $wp->query_vars ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-public-sync-window.php';
			exit();
		}
		
		if ( array_key_exists( 'mw_qbo_sync_public_get_json_item_list', $wp->query_vars ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-public-json-item-list.php';
			exit();
		}
		
		if ( array_key_exists( 'mw_qbo_sync_public_deposit_cron', $wp->query_vars ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-public-deposit-cron.php';
			exit();
		}
		
		if ( array_key_exists( 'mw_qbo_sync_public_get_user_invoice_pdf', $wp->query_vars ) ) {
			#New
			$ai_pdf = false;
			$cuc_mwc = current_user_can('manage_woocommerce');
			
			$pdf_ref = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
			$s_pdf_ref = (isset($_SESSION['mw_wc_qbo_sync_pdf_referer']))?$_SESSION['mw_wc_qbo_sync_pdf_referer']:'';
			
			if($cuc_mwc && strpos($pdf_ref, '/post.php?post=') !== false && strpos($pdf_ref, '&action=edit') !== false){
				$ai_pdf = true;
			}
			
			if(!empty($s_pdf_ref)){
				$ai_pdf = true;
			}
			
			if(get_option('mw_wc_qbo_sync_wam_mng_inv_ed') == 'true' || $ai_pdf){
				require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-public-user-invoice-pdf.php';
			}else{
				echo 'Not Authorized';
			}			
			exit();
		}

	}

}
