<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://myworks.design/software/wordpress/woocommerce/myworks-wc-qbo-sync
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/includes
 * @author     My Works <support@myworks.design>
 */
class MyWorks_WC_QBO_Sync {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      MyWorks_WC_QBO_Sync_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'myworks_wc_qbo_sync';
		$this->version = '';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - MyWorks_WC_QBO_Sync_Loader. Orchestrates the hooks of the plugin.
	 * - MyWorks_WC_QBO_Sync_i18n. Defines internationalization functionality.
	 * - MyWorks_WC_QBO_Sync_Admin. Defines all hooks for the admin area.
	 * - MyWorks_WC_QBO_Sync_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		
		/**
		 * The class responsible for defining all qb lib functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-myworks-wc-qbo-sync-qbo-lib.php';
		
		
		/**
		 * The class responsible for defining all other functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-myworks-wc-qbo-sync-oth-funcs.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-myworks-wc-qbo-sync-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-myworks-wc-qbo-sync-i18n.php';		

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-myworks-wc-qbo-sync-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-myworks-wc-qbo-sync-public.php';

		$this->loader = new MyWorks_WC_QBO_Sync_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the MyWorks_WC_QBO_Sync_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new MyWorks_WC_QBO_Sync_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new MyWorks_WC_QBO_Sync_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'mw_wc_qbo_admin_init' );
		$this->loader->add_action( 'init', $plugin_admin, 'mw_wc_qbo_init' );
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'mw_wc_qbo_cron_schedules' );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );		
		
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'create_admin_menus' );		
		
		//woocommerce hooks for realtime synchronization		
		//$this->loader->add_action( 'before_delete_post', $plugin_admin, 'mw_qbo_wc_product_delete' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'mw_qbo_wc_product_save', 999, 2 );

		# New
		$this->loader->add_action( 'woocommerce_new_product', $plugin_admin, 'mw_qbo_wc_product_save',10, 2 );
		$this->loader->add_action( 'woocommerce_update_product', $plugin_admin, 'mw_qbo_wc_product_save',10, 2 );
		
		//
		//$this->loader->add_action( 'woocommerce_process_product_meta_variable', $plugin_admin, 'mw_qbo_wc_variation_save', 999, 2 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'mw_qbo_wc_variation_save', 999, 2 );
		
		$this->loader->add_action( 'woocommerce_payment_complete', $plugin_admin, 'mw_qbo_wc_order_payment' );	
		//$this->loader->add_action( 'user_register', $plugin_admin, 'myworks_wc_qbo_sync_registration_realtime' );
		/**/
		//$this->loader->add_action( 'woocommerce_update_customer', $plugin_admin, 'myworks_wc_qbo_sync_registration_realtime' );
		$this->loader->add_action( 'profile_update', $plugin_admin, 'myworks_wc_qbo_sync_user_update',10, 2 );
		//personal_options_update
		//edit_user_profile_update
		
		
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
		//$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin, 'mw_qbo_wc_order_payment' );
		$this->loader->add_action( 'woocommerce_order_refunded', $plugin_admin, 'mw_wc_qbo_sync_woocommerce_order_refunded' );
		
		//Only Frontend
		if(!is_admin()){
			//$this->loader->add_action( 'save_post_shop_order', $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
		}
		
		// ACTION FOR ADMIN (BACK END) ORDER		
		if(is_admin()){
			//$this->loader->add_action( 'save_post_shop_order', $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
			
			
			$a_os_l = get_option('mw_wc_qbo_sync_specific_order_status');
			if(!empty($a_os_l)){
				$a_os_l = trim($a_os_l);
				if($a_os_l!=''){$a_os_l = explode(',',$a_os_l);}
				if(is_array($a_os_l) && count($a_os_l)){
					foreach($a_os_l as $os){
						$os = substr($os,3);
						if(!empty($os)){
							$os_action = 'woocommerce_order_status_'.$os;
							$this->loader->add_action( $os_action, $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
						}						
					}
				}
			}			
			
			//$this->loader->add_action( 'woocommerce_new_order', $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
			//$this->loader->add_action( 'woocommerce_process_shop_order_meta', $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
			
			//woocommerce_order_status_changed
			
			//$this->loader->add_action( 'mw_wc_qbo_sync_add_order_status_draft_qbo', $plugin_admin, 'myworks_wc_qbo_sync_admin_order_push' , 100,2 );
			//$this->loader->add_action( 'woocommerce_order_actions_end', $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
			//$this->loader->add_action( 'woocommerce_admin_order_actions_end', $plugin_admin, 'myworks_wc_qbo_sync_order_realtime' , 10,1 );
			
			$this->loader->add_action( 'post_updated', $plugin_admin, 'myworks_wc_qbo_sync_order_update' , 10,3 );
			
			$this->loader->add_action( 'post_updated', $plugin_admin, 'myworks_wc_qbo_sync_pu_product_stock_update' , 10,3 );
			$this->loader->add_action( 'post_updated', $plugin_admin, 'myworks_wc_qbo_sync_pu_variation_stock_update' , 10,3 );
		}
		
		//Other Plugin Actions
		$this->loader->add_action( 'mw_ups_cof_qbo_sync', $plugin_admin, 'order_push_as_admin_side' , 10,1 );
		
		//		
		if(get_option('mw_wc_qbo_sync_enable_wc_subs_rnord_sync') == 'true'){
			/*
			$available_gateways = get_option('mw_wc_qbo_sync_available_gateways');			
			if(is_array($available_gateways) && count($available_gateways)){
				foreach($available_gateways as $key=>$value){
					if(!empty($key) && !empty($value)){
						$gateway = trim($key);
						$wc_sub_ord_rnw_gty_hook = 'woocommerce_scheduled_subscription_payment_'.$gateway;						
						$this->loader->add_action( $wc_sub_ord_rnw_gty_hook, $plugin_admin, 'myworks_wc_qbo_sync_wc_subs_comt_rnw_ord_push' , 10,2 );
					}
				}
			}
			*/
			$this->loader->add_action( 'woocommerce_subscription_renewal_payment_complete', $plugin_admin, 'myworks_wc_qbo_sync_comt_hook_wsrpc' , 10,2 );
		}
		
		//
		$this->loader->add_action( 'create_product_cat', $plugin_admin, 'myworks_wc_qbo_sync_product_category_realtime' );
		
		//$this->loader->add_action( 'woocommerce_product_set_stock', $plugin_admin, 'myworks_wc_qbo_sync_update_stock' );
		
		//$this->loader->add_action( 'woocommerce_variation_set_stock', $plugin_admin, 'myworks_wc_qbo_sync_variation_update_stock' );
		
		//Cancel order
		$this->loader->add_action( 'woocommerce_order_status_cancelled', $plugin_admin, 'myworks_wc_qbo_order_cancelled' );
		
		//Order After Synced
		$this->loader->add_action( 'mw_wc_qbo_sync_order_sync_after_action', $plugin_admin, 'myworks_wc_qbo_sync_after_order_synced_into_qb' , 10,2 );
		$this->loader->add_action( 'mw_wc_qbo_sync_order_update_sync_after_action', $plugin_admin, 'myworks_wc_qbo_sync_after_order_updated_into_qb' , 10,2 );
		
		$this->loader->add_action( 'woocommerce_delete_product_variation', $plugin_admin, 'delete_variation_mapping' );
		
		$this->loader->add_action( 'delete_post', $plugin_admin, 'delete_product_mapping' );
		$this->loader->add_action( 'wp_trash_post', $plugin_admin, 'delete_product_mapping' );
		
		//Ajax Actions
		add_action( 'wp_ajax_myworks_wc_qbo_sync_check_license', 'myworks_wc_qbo_sync_check_license' );
		//
		//add_action( 'wp_ajax_myworks_wc_qbo_sync_check_license_latest', 'myworks_wc_qbo_sync_check_license_latest' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_refresh_log_chart', 'mw_wc_qbo_sync_refresh_log_chart' );
		add_action( 'wp_ajax_mw_wc_qbo_sync_window', 'mw_wc_qbo_sync_window' );
		add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_mappings', 'mw_wc_qbo_sync_clear_all_mappings' );
		//add_action( 'wp_ajax_mw_wc_qbo_sync_automap_customers', 'mw_wc_qbo_sync_automap_customers' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_automap_customers_wf_qf', 'mw_wc_qbo_sync_automap_customers_wf_qf' );
		
		//add_action( 'wp_ajax_mw_wc_qbo_sync_automap_products', 'mw_wc_qbo_sync_automap_products' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_automap_products_wf_qf', 'mw_wc_qbo_sync_automap_products_wf_qf' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_logs', 'mw_wc_qbo_sync_clear_all_logs' );
		add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_log_errors', 'mw_wc_qbo_sync_clear_all_log_errors' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_queues', 'mw_wc_qbo_sync_clear_all_queues' );
		//add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_queue_errors', 'mw_wc_qbo_sync_clear_all_queue_errors' );
		
		//add_action( 'wp_ajax_mw_wc_qbo_sync_automap_variations', 'mw_wc_qbo_sync_automap_variations' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_automap_variations_wf_qf', 'mw_wc_qbo_sync_automap_variations_wf_qf' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_trial_license_check_again', 'mw_wc_qbo_sync_trial_license_check_again' );		
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_del_license_local_key', 'mw_wc_qbo_sync_del_license_local_key' );
		add_action( 'wp_ajax_mw_wc_qbo_sync_del_conn_cred_local_key', 'mw_wc_qbo_sync_del_conn_cred_local_key' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_qcpp_on_off', 'mw_wc_qbo_sync_qcpp_on_off' );
		
		//add_action( 'wp_ajax_mw_wc_qbo_sync_automap_customers_by_name', 'mw_wc_qbo_sync_automap_customers_by_name' );
		//add_action( 'wp_ajax_mw_wc_qbo_sync_automap_products_by_name', 'mw_wc_qbo_sync_automap_products_by_name' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_mappings_products', 'mw_wc_qbo_sync_clear_all_mappings_products' );
		add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_mappings_variations', 'mw_wc_qbo_sync_clear_all_mappings_variations' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_mappings_customers', 'mw_wc_qbo_sync_clear_all_mappings_customers' );
		
		//
		if(get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_ms')=='true' && !empty(get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur'))){
			add_action( 'wp_ajax_mw_wc_qbo_sync_automap_vendors', 'mw_wc_qbo_sync_automap_vendors' );
			add_action( 'wp_ajax_mw_wc_qbo_sync_automap_vendors_by_name', 'mw_wc_qbo_sync_automap_vendors_by_name' );			
			add_action( 'wp_ajax_mw_wc_qbo_sync_clear_all_mappings_vendors', 'mw_wc_qbo_sync_clear_all_mappings_vendors' );
		}
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_get_nqc_time_diff', 'mw_wc_qbo_sync_get_nqc_time_diff' );
		
		add_action( 'wp_ajax_mw_wc_qbo_sync_rg_all_inc_variation_names', 'mw_wc_qbo_sync_rg_all_inc_variation_names' );
		
		/**/
		add_action( 'wp_ajax_mw_wc_qbo_sync_redirect_deactivation_popup', 'mw_wc_qbo_sync_redirect_deactivation_popup' );
		
		//
		add_action( 'wp_ajax_mw_wc_qbo_sync_odpage_qbsync', 'mw_wc_qbo_sync_odpage_qbsync' );
		add_action( 'wp_ajax_mw_wc_qbo_sync_odpage_sync_status', 'mw_wc_qbo_sync_odpage_sync_status' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new MyWorks_WC_QBO_Sync_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//
		$this->loader->add_action( 'init', $plugin_public, 'mw_qbo_sync_public_api_init_internal' );
		$this->loader->add_filter( 'query_vars', $plugin_public, 'mw_qbo_sync_public_api_query_vars' );
		$this->loader->add_action( 'parse_request', $plugin_public, 'mw_qbo_sync_public_api_request' );
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    MyWorks_WC_QBO_Sync_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
