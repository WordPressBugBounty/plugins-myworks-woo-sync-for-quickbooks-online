<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://myworks.design/software/wordpress/woocommerce/myworks-wc-qbo-sync
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/admin
 * @author     My Works <support@myworks.design>
 */
class MyWorks_WC_QBO_Sync_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	 
	private $cur_db_version = '1.6';
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		global $MWQS_OF,$MSQS_QL,$MSQS_AD;
		$MWQS_OF = new MyWorks_WC_QBO_Sync_Oth_Funcs();
		$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib();
		
		$MSQS_AD = $this;
		
		/**
		 * The class responsible for defining all AJAX actions that occur in the site
		 * side of the site.
		 */
		require_once plugin_dir_path( __FILE__ ).'myworks-wc-qbo-sync-ajax-actions.php';
	}
	
	public function mw_wc_qbo_admin_init(){
		# Admin Head
		add_action('admin_head', array($this,'mwqbos_admin_head'));
		
		global $MSQS_QL;
		if(!current_user_can('manage_woocommerce') && !current_user_can('view_woocommerce_report')){
			//remove_menu_page( 'myworks-wc-qbo-sync' );
			return false;
		}		
		
		if(!$this->is_woocommerce_active()){
			deactivate_plugins( WP_PLUGIN_DIR . '/myworks-woo-sync-for-quickbooks-online/myworks-woo-sync-for-quickbooks-online.php' );
		  	add_action( 'admin_notices', array($this,'qbo_init_check_admin_notice') );
		}
		
		$mw_wc_qbo_sync_fresh_install = get_option('mw_wc_qbo_sync_fresh_install');
		$qbo_after_activate_admin_notice = get_option('qbo_after_activate_admin_notice');
		if($qbo_after_activate_admin_notice!='false' && $mw_wc_qbo_sync_fresh_install=='true'){
		  	add_action( 'admin_notices', array($this,'qbo_after_activate_admin_notice') );
		}

		if(isset($_GET['qbo_after_activate_admin_notice']) && $_GET['qbo_after_activate_admin_notice']=='false'){
			update_option('qbo_after_activate_admin_notice', 'false');
			wp_redirect(admin_url('admin.php?page=myworks-wc-qbo-sync-connection'));
		}
		
		$mw_wc_qbo_sync_qbo_is_init = get_option('mw_wc_qbo_sync_qbo_is_init');
		$qbo_after_admin_init_setup_notice = get_option('qbo_after_admin_init_setup_notice');
		if($mw_wc_qbo_sync_qbo_is_init=='true' && $qbo_after_admin_init_setup_notice!='false' && $mw_wc_qbo_sync_fresh_install=='true'){
		  	add_action( 'admin_notices', array($this,'qbo_after_admin_init_setup_notice') );
		}

		if(isset($_GET['qbo_after_admin_init_setup_notice']) && $_GET['qbo_after_admin_init_setup_notice']=='false'){
			update_option( 'mw_wc_qbo_sync_fresh_install', 'false');
			update_option('qbo_after_admin_init_setup_notice', 'false');
			wp_redirect(admin_url('admin.php?page=myworks-wc-qbo-push'));
		}
		
		//
		MyWorks_WC_QBO_Sync_Admin::mw_wc_qbo_sync_qbo_is_init();
		
		/*
		MyWorks_WC_QBO_Sync_Admin::admin_settings_save();
		MyWorks_WC_QBO_Sync_Admin::admin_settings_get();
		*/
		
		$this->myworks_wc_qbo_sync_activation_redirect();
		MyWorks_WC_QBO_Sync_Admin::mw_qbo_sync_version_control();
		MyWorks_WC_QBO_Sync_Admin::plugin_version_updated();
		$this->plugin_help_init();
		
		//$this->health_checker();
		$this->mw_qbo_sync_add_qbo_status_column();
		$this->mw_qbo_sync_add_qbo_status_widget();
		
		if(get_option('mw_wc_qbo_sync_db_fix') !== null){
			if(get_option('mw_wc_qbo_sync_db_fix') == 'true'){
				//$this->mw_wc_qbo_sync_db_fix_db_manually();
				if(isset($_GET['issue']) && $_GET['issue']!=''){
					//$this->mw_wc_qbo_sync_db_fix_db_manually($_GET['issue']);
				}
			}
		}
		
		/**/
		if($this->cur_db_version!=$MSQS_QL->get_option('mw_wc_qbo_sync_cur_db_version')){			
			$this->fix_db_alter_issue();
		}
		
		$this->wc_order_footer_script();
		
		$server_env = explode('/',$_SERVER['SERVER_SOFTWARE']);
		if(isset($server_env[0]) && $server_env[0] == 'nginx'){
			MyWorks_WC_QBO_Sync_Admin::nginx_register_apache_request_headers();
		}

		/*if(!get_option('mw_qbo_sync_tour_completed')){
			$this->mw_qbo_sync_init_tour();
		}*/
		
		/**/
		$this->myworks_wc_qbo_sync_group_tax_exempt_init();
		
		/**/
		add_action( 'post_submitbox_misc_actions', array($this,'sync_uiiqb_cb_field'));
		
	}

	# Admin Head
	public function mwqbos_admin_head(){
		global $MSQS_QL;
		echo '<style>#adminmenu #toplevel_page_myworks-wc-qbo-sync.wp-not-current-submenu .wp-has-submenu div.wp-menu-image img{padding: 12px 0 0; opacity: 1; max-width: 80%;}</style>';

		$isPluginAdminPage = false;
		if(is_admin() && isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])){
			$qsc = 'myworks-wc-qbo-';
			$queryString = explode('=',$MSQS_QL->sanitize($_SERVER['QUERY_STRING']));
			if(is_array($queryString) && isset($queryString[1]) && !empty($queryString[1]) && $queryString[0] == 'page'){
				$psa = [
					'sync',
					'map',
					'push',
					'pull',					
				];

				foreach($psa as $v){
					if( strpos( $queryString[1], $qsc.$v ) !== false ) {
						$isPluginAdminPage = true;
						break;
					}
				}				
			}
		}		
		
		if($isPluginAdminPage){
			# HelpScout
			echo '<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
			</script>
			<script type="text/javascript">window.Beacon(\'init\', \'e2db1e6b-1123-49de-b467-f708e56f447d\')</script>';

			# ProductFruits
			echo '<script type="text/javascript">
			    (function(w,d,u){
			        w.$productFruits=w.$productFruits||[];
			        w.productFruits=w.productFruits||{};w.productFruits.scrV=\'2\';
			        let a=d.getElementsByTagName(\'head\')[0];let r=d.createElement(\'script\');r.async=1;r.src=u;a.appendChild(r);
			    })(window,document,\'https://app.productfruits.com/static/script.js\');
			</script>';

			$userName = get_bloginfo('admin_email');
			echo '<script type="text/javascript">		   
			    $productFruits.push([\'init\', \'3wDQVCSYNjckZoDn\', \'en\', { username: \''.$userName.'\' }]);
			</script>';
		}
		
	}
	
	public function sync_uiiqb_cb_field($post){
		global $MSQS_QL;
		#lpa
		if($post->post_type != 'product' || $MSQS_QL->is_plg_lc_p_l(false)){return '';}
		//$mwqb_sync_uiiqb = (int) get_post_meta($post->ID, 'mwqb_sync_uiiqb', true);
		$mwqb_sync_uiiqb = isset($_POST['mwqb_sync_uiiqb']) ?1:0;
		//$_manage_stock = get_post_meta($post->ID, '_manage_stock', true);
		//if($_manage_stock != 'yes'){return '';}
		echo '
		<div class="misc-pub-section misc-pub-section-last">
		<span id="timestamp">'
		. '<label>
		<input type="checkbox"' . ($mwqb_sync_uiiqb == 1 ? ' checked="checked" ' : null) . 'value="1" name="mwqb_sync_uiiqb" /> Sync WooCommerce inventory level to QuickBooks when I save</label>'
		.'</span></div>
		';		
	}
	
	public function is_woocommerce_active(){
		if(is_multisite()){
			if(class_exists( 'WooCommerce' )) {
				return true;
			}
			
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			
			if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				return true;
			}
		}else{
			if(class_exists( 'WooCommerce' ) && in_array('woocommerce/woocommerce.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ))){
				return true;
			}			
		}
		
		return false;
	}
	
	public function qbo_init_check_admin_notice() {
		echo '<div title="MyWorks QuickBooks Sync Setup Error" class="notice notice-error mwqs-setup-notice">'.__('MyWorks Woo Sync for QuickBooks Online has been deactivated! This plugin requires <a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> to be active!', 'mw_wc_qbo_sync').'</div>';
	}
	
	public function qbo_after_activate_admin_notice() {
		$activation_message = get_option('mw_wc_qbo_sync_successfull_activation_message');
		if(empty( $activation_message )) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'You have successfully activated <b>MyWorks Sync for QuickBooks Online</b>. You may visit <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-connection').'">MyWorks Sync > Connection</a> in your menubar to begin setup!', 'mw_wc_qbo_sync' ); ?></p>
		</div>
		<?php
		update_option( 'mw_wc_qbo_sync_successfull_activation_message', 'triggered' );
		}
	}
	
	public function qbo_after_admin_init_setup_notice() {
		echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'.__('Setup is now complete for <b>MyWorks Sync!</b> New activity will be now be automatically synced to QuickBooks. You may manually push any existing data (like products/orders) to QuickBooks by visiting <a href="'.admin_url('admin.php?page=myworks-wc-qbo-push').'">MyWorks Sync > Push</a>.', 'mw_wc_qbo_sync').'</p><a style="z-index:99; text-decoration: none;" class="notice-dismiss" href="'.admin_url('admin.php?page=myworks-wc-qbo-push&qbo_after_admin_init_setup_notice=false').'"><span class="screen-reader-text">Dismiss this notice.</span></a></div>';
	}
	
	//
	public function mwqbosync_inventory_import_schedule_hook_callback(){
		global $MSQS_QL;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
		}
		
		/*
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_ignore_cdc_for_invnt_import')){
			return $MSQS_QL->Import_All_QB_Inventory();
		}
		*/
		
		/**/
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_allow_cdc_for_invnt_import')){
			return $MSQS_QL->Import_All_QB_Inventory();
		}
		
		if($MSQS_QL->is_connected()){
			$Context = $MSQS_QL->getContext();
			$realm = $MSQS_QL->getRealm();
			
			$last_timestamp = $MSQS_QL->get_option('mw_qbo_sync_iishc_cdc_last_inv_impt_timestamp');
			if(!empty($last_timestamp)){
				$timestamp = $last_timestamp;
			}else{
				$interval_mins = 60;
				$now = new DateTime(null, new DateTimeZone('America/Los_Angeles'));
				$datetime = $now->format('Y-m-d H:i:s');
				$datetime = date('Y-m-d H:i:s',strtotime("-{$interval_mins} minutes",strtotime($datetime)));
				
				$timestamp = date('Y-m-d', strtotime($datetime)) . 'T' . date('H:i:s', strtotime($datetime));
			}
			
			$cdc_objects = array('Item');
			
			$CDCService = new QuickBooks_IPP_Service_ChangeDataCapture();
			$cdc = $CDCService->cdc($Context, $realm, $cdc_objects,	$timestamp);
			
			$cdc_ivnt_ids = array();
			if($cdc && count($cdc)){
				foreach ($cdc as $object_type => $list){
					if($list && count($list)){
						foreach ($list as $Object){
							if($object_type=='Item'){
								$item_id = (int) $MSQS_QL->qbo_clear_braces($Object->getId());
								$item_type = $Object->getType();
								if($item_id && $item_type=='Inventory'){
									$cdc_ivnt_ids[] = $item_id;
									$return_id = $MSQS_QL->UpdateWooCommerceInventory(array('qbo_inventory_id'=>$item_id,'cron'=>true));
								}
							}
						}
					}										
				}
			}
			
			/**/
			if(!empty($cdc_ivnt_ids)){
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_success_add_ccqii_debug_ids_into_log')){
					$MSQS_QL->save_log('CDC Cron QBO Item Inventory Ids',print_r(array('Timestamp'=>$timestamp,'QBO Inventory Ids'=>$cdc_ivnt_ids),true),'CDC',2);
				}
				
				update_option('mw_qbo_sync_iishc_cdc_last_inv_impt_timestamp',$timestamp);
			}
			
		}
	}
	
	#Pricing
	public function mwqbosync_pricing_import_schedule_hook_callback(){
		global $MSQS_QL;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
		}
		
		/**/
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_allow_cdc_for_prc_import')){
			return $MSQS_QL->Import_All_QB_Pricing();
		}
		
		if($MSQS_QL->is_connected()){
			$Context = $MSQS_QL->getContext();
			$realm = $MSQS_QL->getRealm();
			
			$last_timestamp = $MSQS_QL->get_option('mw_qbo_sync_iishc_cdc_last_prc_impt_timestamp');
			if(!empty($last_timestamp)){
				$timestamp = $last_timestamp;
			}else{
				$interval_mins = 60;
				$now = new DateTime(null, new DateTimeZone('America/Los_Angeles'));
				$datetime = $now->format('Y-m-d H:i:s');
				$datetime = date('Y-m-d H:i:s',strtotime("-{$interval_mins} minutes",strtotime($datetime)));
				
				$timestamp = date('Y-m-d', strtotime($datetime)) . 'T' . date('H:i:s', strtotime($datetime));
			}
			
			$cdc_objects = array('Item');
			
			$CDCService = new QuickBooks_IPP_Service_ChangeDataCapture();
			$cdc = $CDCService->cdc($Context, $realm, $cdc_objects,	$timestamp);
			
			$cdc_item_ids = array();
			$it_arr = array('Inventory','Service','NonInventory'); //,'Group'
			if($cdc && count($cdc)){
				foreach ($cdc as $object_type => $list){
					if($list && count($list)){
						foreach ($list as $Object){
							if($object_type=='Item'){
								$item_id = (int) $MSQS_QL->qbo_clear_braces($Object->getId());
								$item_type = $Object->getType();
								if($item_id && in_array($item_type,$it_arr)){
									$cdc_item_ids[] = $item_id;									
									$return_id = $MSQS_QL->Qbo_Pull_Product(array('qbo_product_id'=>$item_id,'webhook'=>false),true);
								}
							}
						}
					}										
				}
			}
			
			/**/
			if(!empty($cdc_item_ids)){
				update_option('mw_qbo_sync_iishc_cdc_last_prc_impt_timestamp',$timestamp);
			}
			
		}
	}
	
	//
	public function mw_qbo_sync_deposit_cron_function_execute(){
		global $MSQS_QL;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate')){
			return false;
		}
		
		$deposit_ser_cron_data = $MSQS_QL->get_dps_cron_ser_str();
		if(!empty($deposit_ser_cron_data)){
			$deposit_ser_cron_data = base64_decode($deposit_ser_cron_data);
			$deposit_data = @unserialize($deposit_ser_cron_data);
			if(is_array($deposit_data) && count($deposit_data)){
				$now = new DateTime();
				$now->setTimezone(new DateTimeZone('UTC'));
				$hm = $now->format('H:i');
				
				$deposit_cron_url = (isset($deposit_data['deposit_cron_url']))?base64_decode($deposit_data['deposit_cron_url']):'';
				if(filter_var($deposit_cron_url, FILTER_VALIDATE_URL) !== FALSE){
					if(isset($deposit_data['c_items']) && is_array($deposit_data['c_items']) && count($deposit_data['c_items'])){
						foreach($deposit_data['c_items'] as $time => $c_items){
							$is_run_cron = false;
							$hm_st = strtotime($hm.':00');
							$time_st = strtotime($time.':00');
							
							$interval = $hm_st - $time_st;							
							$minutes   = (int) round($interval / 60);
							
							if(!$minutes || ($minutes>0 && $minutes<30)){
								$is_run_cron = true;
							}
							
							if($is_run_cron){
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
									$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
								}
								
								if(is_array($c_items) && !empty($c_items)){
									$gtw_cur_arr = array();
									foreach($c_items as $gateway => $currency_arr){							
										$currency = implode(',',$currency_arr);
										$gtw_cur_arr[$gateway] = $currency;
									}
									$MSQS_QL->save_log('Deposit Cron Request Items','Gateways: '.json_encode($gtw_cur_arr),'Deposit',2);
									
									if(is_array($gtw_cur_arr) && !empty($gtw_cur_arr)){										
										foreach($gtw_cur_arr as $gateway => $currency){
											$gateway_arr = array($gateway);
											$currency_arr = explode(',',$currency);
											//Queue Function Call
											$this->mw_qbo_sync_queue_cron_function_execute();
											
											if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
												$MSQS_QL->Cron_Deposit_Sr($gateway_arr,0,$currency_arr);
											}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate')){
												//$MSQS_QL->Cron_Deposit_Sr($gateway_arr,0,$currency_arr);
											}
											else{
												$MSQS_QL->Cron_Deposit($gateway_arr,0,$currency_arr);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}		
	}
	
	public function mw_qbo_sync_queue_cron_function_execute($is_forced=false){
		global $MSQS_QL;
		
		#New
		$is_dqfr_p = $MSQS_QL->option_checked('mw_wc_qbo_sync_enable_d_o_q_add_p');
		$lfp = MW_QBO_SYNC_LOG_DIR;
		$lf = $lfp . 'ql.lock';
		$fp = fopen($lf, "w");
		if(flock($fp, LOCK_EX | LOCK_NB)){
			//
		}else{
			if($is_dqfr_p){
				$MSQS_QL->save_log('Queue Sync Debug','Queue Function Already Running','Queue',2);
				return false;
			}			
		}
		
		//
		if($is_forced){
			delete_option('mw_wc_qbo_sync_force_queue_run');
		}		
		
		//$MSQS_QL->save_log('Queue Sync Run Test','Queue Function Executed','Queue',2);
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
		}
		
		if(!$MSQS_QL->is_connected()){
			flock($fp, LOCK_UN);
			return false;
		}
		
		$qoa_ids = array();
		$qpa_ids = array();
		$qca_ids = array();
		$qov_ids = array();
		
		global $wpdb;
		$qq = "SELECT * FROM {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue WHERE `run` = 0 ORDER BY `added_date` ASC"; // id
		//$qq = $wpdb->prepare()
		$queue_data = $MSQS_QL->get_data($qq);		
		if(is_array($queue_data) && count($queue_data)){
			$dt_str = $MSQS_QL->now('Y-m-d H:i:s');
			$dt_str = base64_encode($dt_str);
			
			$log_txt = "Queue Sync Run Started\n";
			
			$ord_log_count = 0;
			$cus_log_count = 0;
			$pmt_log_count = 0;
			$prd_log_count = 0;
			$vrn_log_count = 0;
			$prd_inv_log_count = 0;
			$vrn_inv_log_count = 0;
			$rfd_log_count = 0;
			
			$cat_log_count = 0;
			
			$tot_queue_item = count($queue_data);
			
			$is_oauth2_refreshed = false;
			
			$pbos_id_arr = array();
			foreach($queue_data as $q_dt){
				/*Queue Loop Oauth2 Refresh Connection Check*/
				if(!$is_oauth2_refreshed && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') && $MSQS_QL->check_oauth2_refresh()){
					$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true,true);					
					if(!$MSQS_QL->is_connected()){
						flock($fp, LOCK_UN);
						return false;
					}
					
					$is_oauth2_refreshed = true;
				}
				
				$log_type = '';
				
				$row_id = $q_dt['id'];
				
				$item_type = $q_dt['item_type'];				
				$item_id = $q_dt['item_id'];
				$item_action = $q_dt['item_action'];
				$woocommerce_hook = $q_dt['woocommerce_hook'];
				
				$success = 0;
				$run = 0;
				if($item_type=='Customer' && $item_action=='CustomerPush'){
					if(!in_array($item_id,$qca_ids)){
						$qca_ids[] = $item_id;
						$log_type = 'Client';
						$cus_log_count++;
						$run = 1;
						if($this->myworks_wc_qbo_sync_registration_realtime($item_id)){
							$success = 1;
						}
					}
					
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}
				
				if($item_type=='Invoice' && $item_action=='OrderPush'){
					if(!in_array($item_id,$qoa_ids)){
						$qoa_ids[] = $item_id;
						$log_type = 'Invoice';
						$ord_log_count++;
						$run = 1;
						if($this->myworks_wc_qbo_sync_order_realtime(array('order_id'=>$item_id,'queue_side'=>true))){//ID->Array #21-06-2018
							$success = 1;
						}
					}
					
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
					
					#New - Payment before order sync issue
					if(isset($pbos_id_arr[$item_id])){
						$qpa_ids[] = $item_id;
						$pmt_log_count++;
						$success_p = 0;
						if($this->mw_qbo_wc_order_payment($item_id)){
							$success_p = 1;
						}
						
						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success_p,$pbos_id_arr[$item_id]));
						
						unset($pbos_id_arr[$item_id]);
					}
				}
				
				//
				if($item_type=='Invoice' && $item_action=='OrderVoid'){
					if(!in_array($item_id,$qov_ids)){
						$qov_ids[] = $item_id;
						$log_type = 'Invoice';
						$ord_log_count++;
						$run = 1;
						if($this->myworks_wc_qbo_order_cancelled($item_id)){
							$success = 1;
						}
					}
					
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}
				
				if($item_type=='Payment' && $item_action=='PaymentPush'){
					if(!in_array($item_id,$qpa_ids)){
						#New - Payment before order sync issue
						$pbosq = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue WHERE `item_id` = %d AND `item_action` = 'OrderPush' AND `run` = 0 LIMIT 0,1",$item_id);
						$pbosr = $MSQS_QL->get_row($pbosq);
						if(is_array($pbosr) && !empty($pbosr)){
							$pbos_id_arr[$item_id] = $row_id;
							continue;
						}						
						
						$qpa_ids[] = $item_id;
						$log_type = 'Payment';
						$pmt_log_count++;
						$run = 1;
						if($this->mw_qbo_wc_order_payment($item_id)){
							$success = 1;
						}
					}
					
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}
				
				if($item_type=='Product' && $item_action=='ProductPush'){
					$log_type = 'Product';
					$prd_log_count++;
					$run = 1;
					if($this->mw_qbo_wc_product_save($item_id,'Queue')){
						$success = 1;
					}
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}
				
				if($item_type=='Variation' && $item_action=='VariationPush'){
					$log_type = 'Variation';
					$vrn_log_count++;
					$run = 1;
					if($this->mw_qbo_wc_variation_save($item_id)){
						$success = 1;
					}
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}
				
				if($item_type=='Refund' && $item_action=='RefundPush'){
					$log_type = 'Refund';
					$rfd_log_count++;
					$run = 1;
					if($this->mw_wc_qbo_sync_woocommerce_order_refunded($item_id)){
						$success = 1;
					}
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}				
				
				if($item_type=='Inventory' && $item_action=='InventoryPush'){
					$log_type = 'Inventory';
					$prd_inv_log_count++;
					$run = 1;
					if($this->myworks_wc_qbo_sync_update_stock($item_id)){
						$success = 1;
					}
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}
				
				if($item_type=='VariationInventory' && $item_action=='VariationInventoryPush'){
					$log_type = 'Inventory';
					$vrn_inv_log_count++;
					$run = 1;
					if($this->myworks_wc_qbo_sync_variation_update_stock($item_id)){
						$success = 1;
					}
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}				
				
				if($item_type=='Category' && $item_action=='CategoryPush'){
					$log_type = 'Category';
					$cat_log_count++;
					$run = 1;
					if($this->myworks_wc_qbo_sync_product_category_realtime($item_id)){
						$success = 1;
					}
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue SET `run` = %d , `success` = %d WHERE `id` = %d",1,$success,$row_id));
				}
				
				$cud_dt = $MSQS_QL->now('Y-m-d H:i:s');
				$s_history_data = array();
				$s_history_data['item_type'] = $item_type;
				$s_history_data['item_action'] = $item_action;
				$s_history_data['woocommerce_hook'] = $woocommerce_hook;
				$s_history_data['item_id'] = $item_id;
				$s_history_data['run'] = $run;
				$s_history_data['success'] = $success;
				$s_history_data['added_date'] = $cud_dt;
				$s_history_data['dt_str'] = $dt_str;
				$wpdb->insert($wpdb->prefix.'mw_wc_qbo_sync_real_time_sync_history', $s_history_data);
				
				//
				if($item_type=='Invoice' && $item_action=='OrderPush'){
					sleep(5);
				}
			}
			
			if($cus_log_count){$log_txt.="Total Customer Sync Run: $cus_log_count\n";}
			if($ord_log_count){$log_txt.="Total Order Sync Run: $ord_log_count\n";}
			if($pmt_log_count){$log_txt.="Total Payment Sync Run: $pmt_log_count\n";}
			
			if($rfd_log_count){$log_txt.="Total Refund Sync Run: $rfd_log_count\n";}
			
			if($prd_log_count){$log_txt.="Total Product Sync Run: $prd_log_count\n";}
			if($vrn_log_count){$log_txt.="Total Variation Sync Run: $vrn_log_count\n";}
			
			if($prd_inv_log_count){$log_txt.="Total Product Inventory Sync Run: $prd_inv_log_count\n";}
			if($vrn_inv_log_count){$log_txt.="Total Variation Inventory Sync Run: $vrn_inv_log_count\n";}
			
			if($cat_log_count){$log_txt.="Total Category Sync Run: $cat_log_count\n";}
			
			$log_txt.="Total Items in Queue: $tot_queue_item\n";
			
			$log_txt.="Queue Sync Run Ended";
			
			$is_s_queue_log = false;
			if($cus_log_count>0 || $ord_log_count>0 || $pmt_log_count>0 || $rfd_log_count>0 || $prd_log_count>0 || $vrn_log_count>0 || $prd_inv_log_count>0 || $vrn_inv_log_count>0 || $cat_log_count>0 ){
				$is_s_queue_log = true;
			}
			
			if($is_forced){
				$is_s_queue_log = false;
			}
			
			if($is_s_queue_log){
				$MSQS_QL->save_log('Queue Sync Run',$log_txt,'Queue',2);
			}
			
			$wpdb->query("DELETE FROM `{$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue` WHERE `run` = 1 ");
		}
		
		/**/
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_ca_ruso_dqs')){
			$ord_list = $MSQS_QL->get_qs_catch_all_order_ids();
			if(is_array($ord_list) && !empty($ord_list)){
				foreach($ord_list as $ol){
					$is_q_caos_run = false;
					if($ol['order_id'] > 0 && !in_array($ol['order_id'],$qoa_ids)){
						if($this->myworks_wc_qbo_sync_order_realtime(array('order_id'=>$ol['order_id'],'queue_side'=>true,'catch_all_q'=>true))){
							$is_q_caos_run = true;
						}
					}
					
					if($is_q_caos_run && $ol['payment_id'] > 0 && !in_array($ol['payment_id'],$qpa_ids)){
						if($this->mw_qbo_wc_order_payment(array('payment_id'=>$ol['payment_id'],'queue_side'=>true))){
							//
						}
					}
				}
			}
		}
		
		//
		flock($fp, LOCK_UN);
	}
	
	public function mw_wc_qbo_cron_schedules($schedules){
		global $MSQS_QL;
		
		if(is_null($MSQS_QL) or empty($MSQS_QL)){
			$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib();
		}
		
		/*
		$ilp = $MSQS_QL->is_plg_lc_p_l();		
		$cit = $MSQS_QL->get_option('mw_wc_qbo_sync_queue_cron_interval_time');
		$ipit = $MSQS_QL->get_option('mw_wc_qbo_sync_ivnt_pull_interval_time');
		*/
		
		/**/
		if(!isset($schedules["MWQBO_5min"])){
			$schedules["MWQBO_5min"] = array(
				'interval' => 5*60,
				'display' => __('Once every 5 minutes'));
		}
		
		if(!isset($schedules["MWQBO_10min"])){
			$schedules["MWQBO_10min"] = array(
				'interval' => 10*60,
				'display' => __('Once every 10 minutes'));
		}
		
		if(!isset($schedules["MWQBO_15min"])){
			$schedules["MWQBO_15min"] = array(
				'interval' => 15*60,
				'display' => __('Once every 15 minutes'));
		}
		
		//
		if(!isset($schedules["MWQBO_30min"])){
			$schedules["MWQBO_30min"] = array(
				'interval' => 30*60,
				'display' => __('Once every 30 minutes'));
		}
		
		/*
		if(!isset($schedules["MWQBO_45min"])){
			$schedules["MWQBO_45min"] = array(
				'interval' => 45*60,
				'display' => __('Once every 45 minutes'));
		}
		*/
		
		if(!isset($schedules["MWQBO_60min"])){
			$schedules["MWQBO_60min"] = array(
				'interval' => 60*60,
				'display' => __('Once every 1 hour'));
		}
		
		if(!isset($schedules["MWQBO_360min"])){
			$schedules["MWQBO_360min"] = array(
				'interval' => 360*60,
				'display' => __('Once every 6 hours'));
		}
		
		//
		if(!$MSQS_QL->is_plugin_active('inventory-import-for-myworks-qbo-sync')){
			if(!isset($schedules["IIFMQS_5min"])){
				$schedules["IIFMQS_5min"] = array(
					'interval' => 5*60,
					'display' => __('Once every 5 minutes'));
			}
			
			if(!isset($schedules["IIFMQS_30min"])){
				$schedules["IIFMQS_30min"] = array(
					'interval' => 30*60,
					'display' => __('Once every 30 minutes'));
			}
		}
		
		return $schedules;
	}
	
	public function mw_qbo_sync_rt_queue_cron(){
		global $MSQS_AD;
		global $MSQS_QL;
		
		$cit = $MSQS_QL->get_option('mw_wc_qbo_sync_queue_cron_interval_time');
		/*
		if($cit!='MWQBO_5min' && $cit!='MWQBO_10min' && $cit!='MWQBO_30min' && $cit!='MWQBO_60min'){
			$cit = 'MWQBO_10min';
		}
		*/
		
		$ilp = $MSQS_QL->is_plg_lc_p_l();
		if(empty($cit)){$cit = 'MWQBO_5min';}
	
		$oa_qit = $MSQS_QL->get_qb_queue_p_til();
		if(!isset($oa_qit[$cit])){$cit = 'MWQBO_5min';}
		
		$is_opuci = false;
		if($ilp && $cit != 'MWQBO_60min'){
			$cit = 'MWQBO_60min';			
			$is_opuci = true;
			
		}
		
		#New
		/*
		if($MSQS_QL->is_plg_lc_p_r() && ($cit == 'MWQBO_5min' || $cit == 'MWQBO_10min' || $cit == 'MWQBO_15min')){
			$cit = 'MWQBO_30min';			
			$is_opuci = true;
		}
		*/
		
		if($MSQS_QL->is_plg_lc_p_r() && $cit != 'MWQBO_60min' && $cit != 'MWQBO_30min'){
			$cit = 'MWQBO_30min';			
			$is_opuci = true;
			
		}
		
		if($is_opuci){
			update_option('mw_wc_qbo_sync_queue_cron_interval_time',$cit);
			//
			wp_clear_scheduled_hook('mw_qbo_sync_queue_cron_hook');
			wp_schedule_event(time(), $cit, 'mw_qbo_sync_queue_cron_hook');
		}
		
		if (!$is_opuci && ! wp_next_scheduled ( 'mw_qbo_sync_queue_cron_hook' )){			
			wp_schedule_event(time(), $cit, 'mw_qbo_sync_queue_cron_hook');
		}
		
		add_action('mw_qbo_sync_queue_cron_hook', array($this,'mw_qbo_sync_queue_cron_function'));		
	}
	
	public function mw_qbo_sync_queue_cron_function(){
		global $MSQS_AD;
		global $MSQS_QL;
		//$MSQS_QL->save_log('Queue Sync WP Cron','Queue Sync Function Executed','Queue',2);
		
		$MSQS_AD->mw_qbo_sync_queue_cron_function_execute();
		//$this->mw_qbo_sync_queue_cron_function_execute();
	}
	
	//
	public function mw_qbo_sync_rt_deposit_cron(){
		if (! wp_next_scheduled ( 'mw_qbo_sync_deposit_cron_hook' )){
			wp_schedule_event(time(), "MWQBO_30min", 'mw_qbo_sync_deposit_cron_hook');
		}
		
		add_action('mw_qbo_sync_deposit_cron_hook', array($this,'mw_qbo_sync_deposit_cron_function'));
	}
	
	public function mw_qbo_sync_deposit_cron_function(){
		global $MSQS_AD;
		//global $MSQS_QL;
		$MSQS_AD->mw_qbo_sync_deposit_cron_function_execute();
	}
	
	//
	public function mwqbosync_inventory_import_schedule_cron(){
		global $MSQS_QL;
		$mwqb_iis_hk = 'mwqbosync_inventory_import_schedule_hook';
		$ipit_on = 'mw_wc_qbo_sync_ivnt_pull_interval_time';		
		
		$ipit = $MSQS_QL->get_option($ipit_on);
		$ilp = $MSQS_QL->is_plg_lc_p_l();
		if(empty($ipit)){$ipit = 'MWQBO_5min';}
		
		$oa_ipit = $MSQS_QL->get_qb_ivnt_p_til();
		if(!isset($oa_ipit[$ipit])){$ipit = 'MWQBO_5min';}
		
		$is_opuci = false;
		if($ilp && $ipit != 'MWQBO_60min' && $ipit != 'MWQBO_360min'){
			$ipit = 'MWQBO_60min';
			$is_opuci = true;
		}
		
		#New
		/*
		if($MSQS_QL->is_plg_lc_p_r() && ($ipit == 'MWQBO_5min' || $ipit == 'MWQBO_15min')){
			$ipit = 'MWQBO_30min';
			$is_opuci = true;
		}
		*/
		
		if($MSQS_QL->is_plg_lc_p_r() && $ipit != 'MWQBO_60min' && $ipit != 'MWQBO_360min' && $ipit != 'MWQBO_30min'){
			$ipit = 'MWQBO_30min';
			$is_opuci = true;
		}
		
		if($is_opuci){
			update_option($ipit_on,$ipit);
			//
			wp_clear_scheduled_hook($mwqb_iis_hk);
			wp_schedule_event(time(), $ipit, $mwqb_iis_hk);
		}
		
		if (!$is_opuci && ! wp_next_scheduled ( $mwqb_iis_hk )){			
			wp_schedule_event(time(), $ipit, $mwqb_iis_hk);
		}
		
		add_action($mwqb_iis_hk, array($this,'mwqbosync_inventory_import_schedule_function'));		
	}
	
	#Pricing
	public function mwqbosync_pricing_import_schedule_cron(){
		global $MSQS_QL;
		$mwqb_ppis_hk = 'mwqbosync_pricing_import_schedule_hook';
		$ppit_on = 'mw_wc_qbo_sync_prc_pull_interval_time';
		
		$ppit = $MSQS_QL->get_option($ppit_on);
		$ilp = $MSQS_QL->is_plg_lc_p_l();
		if(empty($ppit)){$ppit = 'MWQBO_5min';}
		
		$oa_ppit = $MSQS_QL->get_qb_ivnt_p_til();
		if(!isset($oa_ppit[$ppit])){$ppit = 'MWQBO_5min';}
		
		$is_opuci = false;
		if($ilp && $ppit != 'MWQBO_60min' && $ppit != 'MWQBO_360min'){
			$ppit = 'MWQBO_60min';
			$is_opuci = true;
		}
		
		#New
		/*
		if($MSQS_QL->is_plg_lc_p_r() && ($ppit == 'MWQBO_5min' || $ppit == 'MWQBO_15min')){
			$ppit = 'MWQBO_30min';
			$is_opuci = true;
		}
		*/
		
		if($MSQS_QL->is_plg_lc_p_r() && $ppit != 'MWQBO_60min' && $ppit != 'MWQBO_360min' && $ppit != 'MWQBO_30min'){
			$ppit = 'MWQBO_30min';
			$is_opuci = true;
		}
		
		if($is_opuci){
			update_option($ppit_on,$ppit);
			//
			wp_clear_scheduled_hook($mwqb_ppis_hk);
			wp_schedule_event(time(), $ppit, $mwqb_ppis_hk);
		}
		
		if (!$is_opuci && ! wp_next_scheduled ( $mwqb_ppis_hk )){			
			wp_schedule_event(time(), $ppit, $mwqb_ppis_hk);
		}
		
		add_action($mwqb_ppis_hk, array($this,'mwqbosync_pricing_import_schedule_function'));		
	}
	
	public function mwqbosync_inventory_import_schedule_function(){
		global $MSQS_AD;
		$MSQS_AD->mwqbosync_inventory_import_schedule_hook_callback();
	}
	
	public function mwqbosync_pricing_import_schedule_function(){
		global $MSQS_AD;
		$MSQS_AD->mwqbosync_pricing_import_schedule_hook_callback();
	}
	
	#Main Init
	public function mw_wc_qbo_init(){
		global $MSQS_QL;
		#$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib();
		if(!$MSQS_QL->use_php_session()){
			$MSQS_QL->initialize_session();
		}
		
		/*
		if(!session_id() && $MSQS_QL->is_allow_php_session()) {			
			session_start();
		}
		*/
		
		/**/
		if(session_id()){
			$is_wlv = (isset($_POST['action']) && $_POST['action'] == 'woocommerce_load_variations')?true:false;
			if(!$is_wlv){
				$MSQS_QL->unset_session('mw_qvdf_s2_js');			
			}
		}
		
		/*IF QBO Connected - Cahnges due to default queue sync*/
		$force_run = false;
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_qb_func_af_plg_act_run') || $force_run){
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') && !$MSQS_QL->is_connected()){
				$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
			}			
			$MSQS_QL->qbo_functionality_after_pugin_activation();
		}
		
		$this->mw_wc_qbo_enable_big_select_join();
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$this->mw_qbo_sync_rt_queue_cron();
		}else{
			/*
			wp_clear_scheduled_hook('mw_qbo_sync_queue_cron_hook');
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_force_queue_run')){
				$this->mw_qbo_sync_queue_cron_function_execute(true);
			}
			*/
		}
		
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') || $MSQS_QL->option_checked('mw_wc_qbo_sync_force_realtime_sync_q')){
			delete_option('mw_wc_qbo_sync_force_realtime_sync_q');
			wp_clear_scheduled_hook('mw_qbo_sync_queue_cron_hook');
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_force_queue_run')){
				$this->mw_qbo_sync_queue_cron_function_execute(true);
			}
		}		
		
		//
		$this->mw_qbo_sync_rt_deposit_cron();
		
		#QB->Woo RT Items
		$mw_wc_qbo_sync_webhook_items = $MSQS_QL->get_option('mw_wc_qbo_sync_webhook_items');
		if($mw_wc_qbo_sync_webhook_items!=''){
			$mw_wc_qbo_sync_webhook_items = explode(',',$mw_wc_qbo_sync_webhook_items);
		}
		
		//
		$is_auto_inventory_pull_active = false;
		#lpa
		if(!$MSQS_QL->is_plugin_active('inventory-import-for-myworks-qbo-sync') && !$MSQS_QL->is_plg_lc_p_l(false)){
			if(is_array($mw_wc_qbo_sync_webhook_items) && count($mw_wc_qbo_sync_webhook_items)){
				if(in_array('Inventory',$mw_wc_qbo_sync_webhook_items)){
					$is_auto_inventory_pull_active = true;
					$this->mwqbosync_inventory_import_schedule_cron();
				}
			}			
		}
		
		$is_deposit_sync_active = false;
		$deposit_ser_cron_data = $MSQS_QL->get_dps_cron_ser_str();
		if(!empty($deposit_ser_cron_data)){
			$is_deposit_sync_active = true;
		}
		
		#Pricing
		$is_auto_pricing_pull_active = false;
		if(is_array($mw_wc_qbo_sync_webhook_items) && count($mw_wc_qbo_sync_webhook_items)){
			if(in_array('Pricing',$mw_wc_qbo_sync_webhook_items)){
				$is_auto_pricing_pull_active = true;
				$this->mwqbosync_pricing_import_schedule_cron();
			}
		}
		
		$mqsl_f_param = array(
		'is_auto_inventory_pull_active' => $is_auto_inventory_pull_active,
		'is_auto_pricing_pull_active' => $is_auto_pricing_pull_active,
		'is_deposit_sync_active' => $is_deposit_sync_active,
		);
		
		$this->mw_qbo_sync_logging($mqsl_f_param);
		$this->mw_qbo_sync_beta_version_control();
		
		/*
		$server_env = explode('/',$_SERVER['SERVER_SOFTWARE']);
		if(isset($server_env[0]) && $server_env[0] == 'nginx'){
			MyWorks_WC_QBO_Sync_Admin::nginx_register_apache_request_headers();
		}
		*/
		MyWorks_WC_QBO_Sync_Admin::nginx_register_apache_request_headers();
	}

	/*public function mw_qbo_sync_init_tour(){
		require_once plugin_dir_path( __FILE__ ) . '/partials/myworks-wc-qbo-sync-admin-tour.php';
	}*/
	
	/*Not Using This*/
	function mw_wc_qbo_sync_db_fix_db_manually($dbfixid=''){

		if(isset($dbfixid) && $dbfixid!=''){

			global $wpdb;

			$dbfix = array();

			$dbfix['mw_wc_qbo_sync_variation_pairs'] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'mw_wc_qbo_sync_variation_pairs` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `wc_variation_id` int(11) NOT NULL,
									  `quickbook_product_id` int(11) NOT NULL,
									  `class_id` varchar(255) NOT NULL,
									  PRIMARY KEY (`id`)
									)';
			
			$dbfix['mw_wc_qbo_sync_wq_cf_map'] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,								  
									  `wc_field` TEXT NOT NULL,
									  `qb_field` varchar(255) NOT NULL,
									  `ext_data` TEXT NOT NULL,
									  PRIMARY KEY (`id`)
									)';

			$dbfix['is_wc_order'] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_payment_id_map` ADD `is_wc_order` INT(1) NOT NULL DEFAULT '0' AFTER `qbo_payment_id`;";

			$dbfix['ps_order_status'] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `ps_order_status` VARCHAR(255) NOT NULL AFTER `term_id`;";

			$dbfix['individual_batch_support'] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `individual_batch_support` INT(1) NOT NULL AFTER `ps_order_status`;";

			$dbfix['deposit_cron_utc'] =  "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `deposit_cron_utc` VARCHAR(255) NOT NULL AFTER `individual_batch_support`;";
			
			//
			$dbfix['inv_due_date_days'] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `inv_due_date_days` INT(3) NOT NULL AFTER `deposit_cron_utc`;";
			
			if(isset($dbfix[$dbfixid])){
				$wpdb->query($dbfix[$dbfixid]);
			}
			
			//wp_redirect(admin_url('admin.php?page=myworks-wc-qbo-sync-db-fix'));
		}
	}
	
	public function wc_order_footer_script() {
		add_action('admin_footer', array($this,'wc_order_list_custom_footer_js'));				
	}
	
	public function wc_order_list_custom_footer_js(){
		global $MSQS_QL;
		$wco_ids = $MSQS_QL->get_session_val('wc_order_id_list',array(),true);
		#$MSQS_QL->_p($wco_ids);
		$hide_qbo_sc = false;
		if(is_array($wco_ids) && !empty($wco_ids) && count($wco_ids) <= 200){
			$json_woil = json_encode($wco_ids);
			wp_nonce_field( 'myworks_wc_qbo_sync_odpage_syncstatus', 'odpage_syncstatus' );
			echo '
			<script type="text/javascript">
				jQuery(document).ready(function(e){					
					var ord_ids = '.$json_woil.';
					var data = {
						"action": \'mw_wc_qbo_sync_odpage_sync_status\',
						"ord_ids": ord_ids,
						"odpage_syncstatus": jQuery(\'#odpage_syncstatus\').val(),
					};
					
					jQuery.ajax({
					   type: "POST",
					   url: ajaxurl,
					   data: data,
					   cache:  false ,
					   datatype: "json",
					   success: function(result){						   
						   var qb_ssd_json = JSON.parse(result);						  
							if(qb_ssd_json.qbc_status == "C"){
								jQuery("span.mqsss").html("<span>Not Synced</span>").addClass("mw_qbo_sync_status_due");
								if(!jQuery.isEmptyObject(qb_ssd_json.map_data)){
									jQuery.each(qb_ssd_json.map_data, function(key_osa,val){
										if(!jQuery.isEmptyObject(val)){
											jQuery.each(val, function(v_k,v_v){												
												var ss_title = "QuickBooks "+key_osa+" Id #"+v_v.Id+" - Click to view it in QuickBooks Online";
												var ss_html = "<a style=\'color:white;\' target=\'_blank\' href=\'"+v_v.qb_href+"\'><span title=\'"+ss_title+"\'>Synced</span></a>";
												
												jQuery("#ph_inv_ss_"+v_v.DocNumber_Md5).html(ss_html).removeClass("mw_qbo_sync_status_due").addClass("mw_qbo_sync_status_paid");
											});											
										}										
									});
								}								
							}else{
								//jQuery("span.mqsss").html("");
								jQuery(".column-mw_qbo_sync_inv_status").css("display","none");
								jQuery("#mw_qbo_sync_inv_status").css("display","none");
							}
					   },
					   error: function(result) {
							jQuery("span.mqsss").html("");
					   }
					});
				});
			</script>
			';
		}else{
			$hide_qbo_sc = true;
		}
		
		if($hide_qbo_sc){
			echo '
			<style type="text/css">
				.column-mw_qbo_sync_inv_status {display:none;}
				#mw_qbo_sync_inv_status {display:none;}
			</style>
			';
			return;
		}
	}
	
	public function wc_order_list_custom_footer_js_old(){
		global $MSQS_QL;
		$wco_ids = $MSQS_QL->get_session_val('wc_order_id_list',array(),true);
		if(is_array($wco_ids) && count($wco_ids) && $MSQS_QL->if_show_sync_status(count($wco_ids),200,'order')){
			//queue changes
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
				$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
			}
			
			if(!$MSQS_QL->is_connected()){
				echo '
				<style type="text/css">
					.column-mw_qbo_sync_inv_status {display:none;}
					#mw_qbo_sync_inv_status {display:none;}
				</style>
				';
				return;
			}
			
			echo '<!--MWQS Script-->'.PHP_EOL;
			$wco_map_data_arr = $MSQS_QL->get_push_invoice_map_data($wco_ids);				
			//$MSQS_QL->_p($wco_map_data_arr);				
			
			echo '
			<script type="text/javascript">
			jQuery(document).ready(function($) {
			var list_ids = [];
			';
			if(is_array($wco_map_data_arr) && count($wco_map_data_arr)){
				foreach($wco_map_data_arr as $pmd){
					$c_doc_no = $pmd['DocNumber'];
					if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
						if($MSQS_QL->start_with($c_doc_no,'S-')){
							$c_doc_no = substr($c_doc_no,2);
						}elseif($MSQS_QL->start_with($c_doc_no,'OS-')){
							$c_doc_no = substr($c_doc_no,3);
						}			 
					}
					$c_doc_no = md5($c_doc_no);
					if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
						$qbo_href = $MSQS_QL->get_push_qbo_view_href('SalesReceipt',$pmd['Id']);
						$sync_status_html = '<span title="QuickBooks SalesReceipt Id #'.$pmd['Id'].' - Click to view it in QuickBooks Online">Synced</span>';
					}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate')){
						$qbo_href = $MSQS_QL->get_push_qbo_view_href('Estimate',$pmd['Id']);
						$sync_status_html = '<span title="QuickBooks Estimate Id #'.$pmd['Id'].' - Click to view it in QuickBooks Online">Synced</span>';
					}
					else{
						$qbo_href = $MSQS_QL->get_push_qbo_view_href('Invoice',$pmd['Id']);
						$sync_status_html = '<span title="QuickBooks Invoice Id #'.$pmd['Id'].' - Click to view it in QuickBooks Online">Synced</span>';
					}
					$sync_status_html = '<a style="color:white;" target="_blank" href="'.$qbo_href.'">'.$sync_status_html.'</a>';
					
					echo 'if($.inArray(\''.$c_doc_no.'\', list_ids) == -1){';
					echo 'list_ids.push("'.$c_doc_no.'");';
					echo "jQuery('#ph_inv_ss_".$c_doc_no."').html('{$sync_status_html}').addClass('mw_qbo_sync_status_paid');";
					echo '}';
					
					echo 'else{';
					echo 'var ss_title = jQuery(\'#ph_inv_ss_'.$c_doc_no.'\').children(\'span\').attr(\'title\');';
					echo "jQuery('#ph_inv_ss_".$c_doc_no."').children('span').attr('title',ss_title+', #".$pmd['Id']."');";
					echo '}';
				}
			}				
			echo '
			jQuery(\'.ph_inv_ss\').each(function(){
				 //console.log($(this).attr(\'id\').replace("ph_inv_ss_", ""));
				 if($.inArray($(this).attr(\'id\').replace("ph_inv_ss_", ""), list_ids) == -1){
					$(this).html(\'<span>Not Synced</span>\').addClass(\'mw_qbo_sync_status_due\');					
				 }
			 });
			';
			echo '
			});
			</script>';
		}
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name.'-widget', plugin_dir_url( __FILE__ ) . 'css/wc-widget-css.css', array(), $this->version, 'all' );
		
		$query_string = explode('=',$_SERVER['QUERY_STRING']);
		//echo '<pre>';print_r($query_string);echo '</pre>';
		if(isset($query_string[1])){
			if( strpos( $query_string[1], "myworks-wc-qbo" ) !== false ) {				
			    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/myworks-wc-qbo-sync-admin.css', array(), $this->version, 'all' );
			}
			
			if( strpos( $query_string[1], "myworks-wc-qbo-sync-connection" ) !== false ) {				
				wp_enqueue_style( $this->plugin_name.'-bootstrap-min', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->plugin_name.'-connection', plugin_dir_url( __FILE__ ) . 'css/connection-design.css', array(), $this->version, 'all' );
			}
		}
	}
	
	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/myworks-wc-qbo-sync-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Register required menus for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function create_admin_menus() {
		if(!class_exists('WooCommerce')) return;
		
		if(!current_user_can('manage_woocommerce') && !current_user_can('view_woocommerce_report')) return false;
		
		global $MSQS_QL;
		/**
		 * Register the admin menu pages for plugin.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		global $MWQS_OF;
		$mw_wc_qbo_sync_license = $MSQS_QL->get_option('mw_wc_qbo_sync_license');
		$mw_wc_qbo_sync_localkey = $MSQS_QL->get_option('mw_wc_qbo_sync_localkey');
		$is_valid_license = $MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey);
		
		/**/
		if($is_valid_license){
			update_option('mw_wc_qbo_sync_is_valid_license','true',true);
		}else{
			delete_option('mw_wc_qbo_sync_is_valid_license');
		}
		
		add_menu_page( 
			__( 'MyWorks Sync</br><span style="font-size:10px;">QuickBooks Online</span>', 'mw_wc_qbo_sync' ),
			__( 'MyWorks Sync</br><span style="font-size:10px;">QuickBooks Online</span>', 'mw_wc_qbo_sync' ), 
			'read', 
			'myworks-wc-qbo-sync', 
			array($this, 'qbo_admin_menu_sync'),
			plugin_dir_url( __FILE__ ) . 'image/menu-icon-sync.png', 
			3
		);	
		
		$sub_page = add_submenu_page( 
			'myworks-wc-qbo-sync', 
			__( 'Dashboard', 'mw_wc_qbo_sync' ),
			__( 'Dashboard', 'mw_wc_qbo_sync' ),
			'read',
			'myworks-wc-qbo-sync',
			array($this, 'qbo_admin_menu_sync')
		);
		add_action('load-'.$sub_page, array($this, 'add_help_tabs'));
		
		//26-10-2017
		if($is_valid_license){
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
				$qc = (int) $MSQS_QL->get_menu_queue_count();
				$sub_page = add_submenu_page( 
					'myworks-wc-qbo-sync', 
					__( 'Queue', 'mw_wc_qbo_sync' ),
					__( 'Queue('.$qc.')', 'mw_wc_qbo_sync' ),
					'read',
					'myworks-wc-qbo-sync-queue',
					array($this, 'qbo_admin_sync_queue_submenu')
				);
				add_action('load-'.$sub_page, array($this, 'add_help_tabs'));
			}
		}
		
		$sub_page = add_submenu_page( 
			'myworks-wc-qbo-sync', 
			__( 'Connection', 'mw_wc_qbo_sync' ),
			__( 'Connection', 'mw_wc_qbo_sync' ),
			'read',
			'myworks-wc-qbo-sync-connection',
			array($this, 'qbo_admin_sync_connection_submenu')
		);
		add_action('load-'.$sub_page, array($this, 'add_help_tabs'));
		
		/**/
		if($is_valid_license){
			$sub_page = add_submenu_page( 
				'myworks-wc-qbo-sync', 
				__( 'Settings', 'mw_wc_qbo_sync' ),
				__( 'Settings', 'mw_wc_qbo_sync' ),
				'read',
				'myworks-wc-qbo-sync-settings',
				array($this, 'qbo_admin_sync_settings_submenu')
			);
			add_action('load-'.$sub_page, array($this, 'add_help_tabs'));

			$sub_page = add_submenu_page( 
				'myworks-wc-qbo-sync', 
				__( 'Log', 'mw_wc_qbo_sync' ),
				__( 'Log', 'mw_wc_qbo_sync' ),
				'read',
				'myworks-wc-qbo-sync-log',
				array($this, 'qbo_admin_sync_log_submenu')
			);
			add_action('load-'.$sub_page, array($this, 'add_help_tabs'));		
			
			$sub_page = add_submenu_page( 
				'myworks-wc-qbo-sync', 
				__( 'Map', 'mw_wc_qbo_sync' ),
				__( 'Map', 'mw_wc_qbo_sync' ),
				'read',
				'myworks-wc-qbo-map',
				array($this, 'qbo_admin_menu_map')
			);
			add_action('load-'.$sub_page, array($this, 'add_help_tabs'));

			$sub_page = add_submenu_page( 
				'myworks-wc-qbo-sync', 
				__( 'Push', 'mw_wc_qbo_sync' ),
				__( 'Push', 'mw_wc_qbo_sync' ),
				'read',
				'myworks-wc-qbo-push',
				array($this, 'qbo_admin_menu_push')
			);
			add_action('load-'.$sub_page, array($this, 'add_help_tabs'));
			
			//21-02-2017
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_pull_enable')){	
				$sub_page = add_submenu_page( 
					'myworks-wc-qbo-sync', 
					__( 'Pull', 'mw_wc_qbo_sync' ),
					__( 'Pull', 'mw_wc_qbo_sync' ),
					'read',
					'myworks-wc-qbo-pull',
					array($this, 'qbo_admin_menu_pull')
				);			
				add_action('load-'.$sub_page, array($this, 'add_help_tabs'));
			}
			
			if(get_option('mw_wc_qbo_sync_db_fix') == 'true'){
			add_submenu_page( 
				'myworks-wc-qbo-sync', 
				__( 'System Info', 'mw_wc_qbo_sync' ),
				__( 'System Info', 'mw_wc_qbo_sync' ),
				'read',
				'myworks-wc-qbo-sync-sys',
				array($this, 'qbo_admin_menu_sync_sys')
			);
			
			
				add_submenu_page( 
					'myworks-wc-qbo-sync',
					__( 'Database Fix', 'mw_wc_qbo_sync' ),
					__( 'Database Fix', 'mw_wc_qbo_sync' ),
					'read',
					'myworks-wc-qbo-sync-db-fix',
					array($this, 'qbo_admin_menu_sync_db_fix_live_inatall')
				);
			}			
			
			add_submenu_page(
				'myworks-wc-qbo-sync', 
				__( 'Compatibility', 'mw_wc_qbo_sync' ),
				__( 'Compatibility', 'mw_wc_qbo_sync' ),
				'read',
				'myworks-wc-qbo-sync-compt',
				array($this, 'qbo_admin_menu_sync_compt')
			);
			
			/**/
			if($MSQS_QL->is_plugin_active('myworks-qbo-sync-compatibility','myworks-qbo-sync-compatibility',true)){
				if(!function_exists('deactivate_plugins')){
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}
				
				deactivate_plugins('myworks-qbo-sync-compatibility/myworks-qbo-sync-compatibility.php',true);
			}
			
		}
		
	}
	
	public function mw_qbo_sync_add_qbo_status_column(){

		add_filter( 'manage_edit-shop_order_columns', array($this,'custom_shop_order_column'),11);
		add_action( 'manage_shop_order_posts_custom_column' , array($this,'custom_orders_list_column_content'), 10, 2 );		
	}
	
	public function custom_shop_order_column($columns){
		$columns['mw_qbo_sync_inv_status'] = __( 'QBO Status','mw_wc_qbo_sync');
		return $columns;
	}
	
	public function custom_orders_list_column_content( $column ){
		global $MSQS_QL;			
		global $post, $woocommerce, $the_order;
		
		$order_id = 0;
		if(is_object($post) && !empty($post)){
			if(isset($post->ID) && isset($post->post_type) && $post->post_type == 'shop_order'){
				$order_id = (int) $post->ID;
			}
		}
		
		$ord_no = $order_id;
		/**/
		$is_qb_next_ord_num = false;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$MSQS_QL->get_qbo_company_setting('is_custom_txn_num_allowed')){
			$is_qb_next_ord_num = true;
			if($order_id){
				$ord_no = get_post_meta($order_id,'_mw_qbo_sync_ord_doc_no',true);
				$ord_no = trim($ord_no);					
			}				
		}
		
		/**/
		if(!$is_qb_next_ord_num){
			$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($order_id);
			if(!empty($wc_inv_no)){
				$ord_no = $wc_inv_no;
			}
		}
		
		switch ( $column ){
			case 'mw_qbo_sync_inv_status' :					
				if($order_id){
					#$_SESSION[$MSQS_QL->session_prefix.'wc_order_id_list'][] = $order_id;
					$e_woil = (array) $MSQS_QL->get_session_val('wc_order_id_list',array());
					$e_woil[] = $order_id;
					$MSQS_QL->set_session_val('wc_order_id_list',$e_woil);
					
					$l_img = plugins_url('myworks-woo-sync-for-quickbooks-online/assets/loading.gif');
					echo '
					<span id="ph_inv_ss_'.md5($ord_no).'" class="ph_inv_ss mw_qbo_sync_status_span mqsss">
						<img src="'.$l_img.'" alt="">
					</span>
					';
				}
				
				break;
		}
	}
	
	public function custom_orders_list_column_content_old( $column ){
		global $MSQS_QL;			
		global $post, $woocommerce, $the_order;
		
		/*
		$woo_version = $MSQS_QL->get_woo_version_number();
		if ( $woo_version >= 3.0 ) {
			$order_id = $the_order->get_id();
		}else{
			$order_id = $the_order->id;
		}
		*/
		
		$order_id = 0;
		if(is_object($post) && !empty($post)){
			if(isset($post->ID) && isset($post->post_type) && $post->post_type == 'shop_order'){
				$order_id = (int) $post->ID;
			}
		}
		
		/**/
		$is_qb_next_ord_num = false;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$MSQS_QL->get_qbo_company_setting('is_custom_txn_num_allowed')){
			$is_qb_next_ord_num = true;
			if($order_id){
				$order_id = get_post_meta($order_id,'_mw_qbo_sync_ord_doc_no',true);
				$order_id = trim($order_id);					
			}				
		}
		
		/**/
		if(!$is_qb_next_ord_num){
			$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($order_id);
			if(!empty($wc_inv_no)){
				$order_id = $wc_inv_no;
			}
		}			
		
		switch ( $column ){
			case 'mw_qbo_sync_inv_status' :					
				if($order_id){
					$_SESSION[$MSQS_QL->session_prefix.'wc_order_id_list'][] = "'".$order_id."'";
					if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
						$_SESSION[$MSQS_QL->session_prefix.'wc_order_id_list'][] = "'S-".$order_id."'";
						$_SESSION[$MSQS_QL->session_prefix.'wc_order_id_list'][] = "'OS-".$order_id."'";
					}
				}
				
				echo '<span id="ph_inv_ss_'.md5($order_id).'" class="ph_inv_ss mw_qbo_sync_status_span"></span>';
				break;
		}
	}
	
	public function mw_qbo_sync_add_qbo_status_widget(){
		add_action( 'add_meta_boxes', array($this,'mw_add_meta_boxes') );		
	}
	
	public  function mw_add_meta_boxes(){
		global $woocommerce, $order, $post;
		add_meta_box( 'qbo_invoice_info', __('QuickBooks Status','mw_wc_qbo_sync'), array($this,'qbo_invoice_info_fn'), 'shop_order', 'side', 'core' );
	}
	
	public function qbo_invoice_info_fn(){
		global $woocommerce,$post;
		global $MSQS_QL;
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
		}
		
		if(!$MSQS_QL->is_connected()){
			echo '<p>QuickBooks Not Connected</p>';
			return;
		}
		
		//30-05-2017
		$order_id = $post->ID;
		$is_qbosa_sr = $MSQS_QL->is_order_sync_as_sales_receipt($order_id);
		$is_qbosa_est = $MSQS_QL->is_order_sync_as_estimate($order_id);
		
		//
		$is_mt_ls_ok = $MSQS_QL->ord_pmnt_is_mt_ls_check_by_ord_id($order_id);
		
		/**/
		$is_qb_next_ord_num = false;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$MSQS_QL->get_qbo_company_setting('is_custom_txn_num_allowed')){
			$is_qb_next_ord_num = true;
			if($order_id){
				$order_id = get_post_meta($order_id,'_mw_qbo_sync_ord_doc_no',true);
				$order_id = trim($order_id);
			}
		}
		
		/**/
		if(!$is_qb_next_ord_num){
			$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($order_id);
			if(!empty($wc_inv_no)){
				$order_id = $wc_inv_no;
			}
		}				
		
		$wco_map_data_arr = array();
		if($order_id){
			$wco_map_data_arr = $MSQS_QL->get_push_invoice_map_data(array("'{$order_id}'","'S-{$order_id}'","'OS-{$order_id}'"),$is_qbosa_sr,$is_qbosa_est);
		}
		
		//$MSQS_QL->_p($wco_map_data_arr);
		if($is_mt_ls_ok){
			wp_nonce_field( 'myworks_wc_qbo_sync_odpage_qbsync', 'odpage_qbsync' );
		}		
		
		$is_inv_sr = false;
		if(is_array($wco_map_data_arr) && count($wco_map_data_arr)){
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $is_qbosa_sr){
				$is_inv_sr = true;
				$qbo_href = $MSQS_QL->get_push_qbo_view_href('SalesReceipt',$wco_map_data_arr[0]['Id']);
				$ie_title = 'QuickBooks SalesReceipt Id #'.$wco_map_data_arr[0]['Id'].' - Click to view it in QuickBooks Online';
			}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $is_qbosa_est){
				$qbo_href = $MSQS_QL->get_push_qbo_view_href('Estimate',$wco_map_data_arr[0]['Id']);
				$ie_title = 'QuickBooks Estimate Id #'.$wco_map_data_arr[0]['Id'].' - Click to view it in QuickBooks Online';
			}
			else{
				$is_inv_sr = true;
				$qbo_href = $MSQS_QL->get_push_qbo_view_href('Invoice',$wco_map_data_arr[0]['Id']);
				$ie_title = 'QuickBooks Invoice Id #'.$wco_map_data_arr[0]['Id'].' - Click to view it in QuickBooks Online';
			}
			echo '<p class="mw_qbo_sync_status_p">
			<strong>Status:</strong> &nbsp;&nbsp;
			<span class="mw_qbo_sync_status_span mw_qbo_sync_status_paid">Synced</span>
			</p>
			<p class="mw_qbo_sync_number_p">
			<strong>Number:</strong>
			<span class="mw_qbo_sync_status_span mw_qbo_sync_status_info">'.$wco_map_data_arr[0]['DocNumber'].'</span>
			</p>';
			if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
				if(isset($wco_map_data_arr[1]) && is_array($wco_map_data_arr[1]) && count($wco_map_data_arr[1])){
					echo '
					<p class="mw_qbo_sync_number_p">
						<strong>Number:</strong> <span class="mw_qbo_sync_status_span mw_qbo_sync_status_info">'.$wco_map_data_arr[1]['DocNumber'].'</span>
					</p>';
				}
			}			
			
			if($is_mt_ls_ok){
				echo '<p>';
				echo '
				<a href="javascript:void(0);"><button class="button odp_qb_ou" type="button">Update in QuickBooks</button></a>
				&nbsp;
				';
				echo '</p>';
			}			
			
			echo '<p>';
			echo '
			<a target="_blank" href="'.$qbo_href.'" title="'.$ie_title.'"><button class="button" type="button">View</button></a>
			';
			
			if($is_inv_sr){
				$pdf_href = get_site_url(null,'index.php?mw_qbo_sync_public_get_user_invoice_pdf=1&id=');
				$pdf_href.= $wco_map_data_arr[0]['Id'];
				if($is_qbosa_sr){
					$pdf_href.= '&type=SalesReceipt';
				}
				
				$pdf_title = 'View/Save as PDF';
				echo '
				<a target="_blank" href="'.$pdf_href.'" title="'.$pdf_title.'"><button class="button" type="button">PDF</button></a>
				</p>';
			}
			echo '</p>';
			
			if($is_mt_ls_ok){
				echo '<script type="text/javascript">
				jQuery(document).ready(function(e){
					jQuery("button.odp_qb_ou").on("click",function(e){
						//alert("Update");
						jQuery("button.odp_qb_ou").text("Updating...");
						var data = {
							"action": \'mw_wc_qbo_sync_odpage_qbsync\',
							"ord_id": jQuery(\'#post_ID\').val(),
							"odpage_qbsync": jQuery(\'#odpage_qbsync\').val(),
							"odp_qb_ou": 1,
						};
						jQuery.ajax({
						   type: "POST",
						   url: ajaxurl,
						   data: data,
						   cache:  false ,
						   //datatype: "json",
						   success: function(result){
							   location.reload();
						   },
						   error: function(result) {
								jQuery("button.odp_qb_ou").text("Update in QuickBooks");
								alert("Something is wrong!");
						   }
						});
					});
				});
				</script>';
			}
		}else{
			
			echo '
			<p class="mw_qbo_sync_status_p">
			<strong>Status:</strong> &nbsp;&nbsp;
			<span class="mw_qbo_sync_status_span mw_qbo_sync_status_due">Not Synced</span>
			</p>			
			';
			
			if($is_mt_ls_ok){
				echo '
					<p><a href="javascript:void(0);"><button class="button odp_qb_op" type="button">Push to QuickBooks</button></a></p>
				';
			}
			
			if($is_mt_ls_ok){
				echo '<script type="text/javascript">
				jQuery(document).ready(function(e){
					jQuery("button.odp_qb_op").on("click",function(e){
						//alert("Push");
						jQuery("button.odp_qb_op").text("Pushing...");
						var data = {
							"action": \'mw_wc_qbo_sync_odpage_qbsync\',
							"ord_id": jQuery(\'#post_ID\').val(),
							"odpage_qbsync": jQuery(\'#odpage_qbsync\').val(),
							"odp_qb_op": 1,
						};
						jQuery.ajax({
						   type: "POST",
						   url: ajaxurl,
						   data: data,
						   cache:  false ,
						   //datatype: "json",
						   success: function(result){
							   location.reload();				  
						   },
						   error: function(result) {
								jQuery("button.odp_qb_op").text("Push to QuickBooks");
								alert("Something is wrong!");
						   }
						});
					});
				});
				</script>';
			}
		}
	}
	
	public function add_help_tabs(){
		return false; //Not Using For Now
		
		$help_tabs = array(
			'mwqs_help' => array(
				'title'	=> __('QuickBooks Sync'),
				'content'	=> '<p>' . __( 'QuickBooks Sync.','mw_wc_qbo_sync' ) . '</p>'
			),
			'mwqs_support' => array(
				'title'	=> __('Help & Support'),
				'content'	=> '<p>' . __( 'Help & Support.','mw_wc_qbo_sync' ) . '</p>'
			),
		);
		
		foreach($help_tabs as $key=>$val){
			$screen = get_current_screen();
			$screen->add_help_tab( array(
				'id'	=> $key,
				'title'	=> $val['title'],
				'content'	=> $val['content']
			) );
			$screen->set_help_sidebar(__('Sidebar' ,'mw_wc_qbo_sync' ));
		}
	}

	public static function admin_menu_page_i_cf($page){
		echo '<style>
			div.notice, div.notice-info{display:none !important;}
			div.wc_aelia{display:none !important;}
			div.updated{display:none !important;}
			div.error{display:none !important;}
			div.message{display:none !important;}

			div.fs-notice.updated, div.fs-notice.success, div.fs-notice.promotion {
				display:none !important;
			}
		</style>';
	}

	/**
	 * Register main menu page for admin area.
	 *
	 * @since    1.0.0
	 */
	public static function qbo_admin_menu_sync(){

		//
		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-dashboard.php';
		Self::admin_menu_page_i_cf('dashboard');
	}

	/**
	 * Register main menu page for admin area.
	 *
	 * @since    1.0.0
	 */
	public static function qbo_admin_menu_sync_sys(){
		//
		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-sys.php';
		Self::admin_menu_page_i_cf('sys');
	}

	public function qbo_admin_menu_sync_db_fix_live_inatall(){

		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-db-fix.php';
		Self::admin_menu_page_i_cf('db-fix');
		
	}	
	
	public static function qbo_admin_menu_sync_compt(){
		//
		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-compt.php';
		Self::admin_menu_page_i_cf('compt');
	}	
	
	/**
	 * Register connection submenu for admin area.
	 *
	 * @since    1.0.0
	 */
	public static function qbo_admin_sync_connection_submenu(){		
		$cn_fn = 'myworks-wc-qbo-sync-admin-connection-design.php';
		require_once plugin_dir_path( __FILE__ ) . 'partials/'.$cn_fn;
		Self::admin_menu_page_i_cf('connection');
	}
	
	/**
	 * Register settings submenu for admin area.
	 *
	 * @since    1.0.0
	 */
	public static function qbo_admin_sync_settings_submenu(){

		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-settings.php';
		Self::admin_menu_page_i_cf('settings');
	}

	/**
	 * Register log submenu for admin area.
	 *
	 * @since    1.0.0
	 */
	public static function qbo_admin_sync_log_submenu(){
		
		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-log.php';
		Self::admin_menu_page_i_cf('log');
	}
	
	public static function qbo_admin_sync_queue_submenu(){
		
		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-queue.php';
		Self::admin_menu_page_i_cf('queue');
	}	
	

	/**
	 * Register invoice submenu for admin area.
	 *
	 * @since    1.0.0
	 */
	public static function qbo_admin_menu_map(){

		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-map.php';
		Self::admin_menu_page_i_cf('map');
	}

	/**
	 * Register invoice submenu for admin area.
	 *
	 * @since    1.0.0
	 */
	public static function qbo_admin_menu_push(){
		global $MSQS_QL;
		/*
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-push-notice.php';
		}else{
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-push.php';
		}
		*/
		require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-push.php';
		Self::admin_menu_page_i_cf('push');
	}
	
	public static function qbo_admin_menu_pull(){
		global $MSQS_QL;		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true' || !$MSQS_QL->option_checked('mw_wc_qbo_sync_pull_enable')) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-pull-notice.php';
		}else{
			require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-admin-pull.php';
		}
		Self::admin_menu_page_i_cf('pull');
	}


	/**
	 * Save admin connection settings.
	 *
	 * @since    1.0.0
	 */
	public function admin_settings_save($data=array(),$trigger=''){
		if($trigger){
			$autoload_options = array(
				'mw_wc_qbo_sync_wam_mng_inv_ed',
				'mw_wc_qbo_sync_wam_mng_inv_qrts',
			);
			foreach($data as $key=>$value){
				$value   = sanitize_text_field( $value );
				if(is_array($autoload_options) && in_array($key,$autoload_options)){
					update_option($key,$value,true);
				}else{
					update_option($key,$value,false);
				}				
			}
		}
	}
	
	public static function set_setting_alert($save_status=''){
		
		if($save_status){
			if($save_status=='admin-success-green'){
				echo '<script>swal("Rock On!", "Your settings have been saved.", "success")</script>';
			}elseif($save_status=='red lighten-2'){
				echo '<script>swal("Oops!", "Hmmmm something went wrong.", "error")</script>';
			}elseif($save_status!='admin-success-green' && $save_status!='red lighten-2' && $save_status!='error'){
				echo '<script>swal("Rock On!", "'.$save_status.'", "success")</script>';
			}else{
				echo '<script>swal("Oops!", "Hmmmm something went wrong.", "error")</script>';
			}
			echo '<script type="text/javascript">
			jQuery(document).ready(function(e){
				jQuery(".confirm").on("click",function(e){
					jQuery(".sweet-overlay").hide();
					jQuery(".showSweetAlert").hide();
					jQuery("body").removeClass("stop-scrolling");
				});
			});
			</script>';
		}
	}

	public static function get_settings_assets($trigger=''){
		
		echo '<link href="'.esc_url( plugins_url( "admin/css/sweetalert.css", dirname(__FILE__) ) ).'" rel="stylesheet" type="text/css">';
		
		echo '<link href="'.esc_url( plugins_url( "admin/css/woocommerce-custom.css", dirname(__FILE__) ) ).'" rel="stylesheet" type="text/css">';
		
		echo '<link href="'.esc_url( plugins_url( "admin/css/font-awesome.css", dirname(__FILE__) ) ).'" rel="stylesheet" type="text/css">';
		
		echo '<script type="text/javascript" src="'.esc_url( plugins_url( "admin/js/sweetalert-dev.js", dirname(__FILE__) ) ).'"></script>';
		
		//Checkbox Switch
		echo MyWorks_WC_QBO_Sync_Admin::get_checkbox_switch_assets();
		
		//
		echo '<link href="'.esc_url( plugins_url( "admin/css/toggle-switch.css", dirname(__FILE__) ) ).'" rel="stylesheet" type="text/css">';
	}
	
	public static function get_checkbox_switch_assets(){
		//		
		return <<<EOF
		
		<!--<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' type='text/css' media='all' />-->
	   <!--<script type='text/javascript' src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>-->
	   
	   <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap2/bootstrap-switch.css' type='text/css' media='all' />
	   <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.js'></script>
EOF;
	
	}
	
	//
	public function mw_qbo_sync_logging($ext_param){
		global $MSQS_QL;
		global $crn_msg_co;
		$crn_msg_co = true;
		if(is_array($ext_param)){
			if($ext_param['is_auto_inventory_pull_active'] || $ext_param['is_deposit_sync_active'] || $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
				//$crn_msg_co = false;
			}
		}
		
		if($crn_msg_co && isset($_GET['disable_qbo_setup_admin_cron_notice'])){
			if($_GET['disable_qbo_setup_admin_cron_notice'] == 'true'){
				update_option('mw_wc_qbo_sync_cron_notice_status','true');
			}			
		}
		
		if(isset($_GET['disable_qbo_setup_admin_d_paypal_notice'])){
			if($_GET['disable_qbo_setup_admin_d_paypal_notice'] == 'true'){
				update_option('mw_wc_qbo_sync_d_paypal_notice_status','true');
			}			
		}
		
		//if(!defined('DISABLE_WP_CRON')){define('DISABLE_WP_CRON',true);} //For Testing
		
		if(defined('DISABLE_WP_CRON')){
			if( DISABLE_WP_CRON ){
				if($crn_msg_co &&  $MSQS_QL->get_option('mw_wc_qbo_sync_cron_notice_status') != 'true'){
					add_action( 'admin_notices', array($this,'qbo_setup_admin_notice_wp_cron') );
				}				
			}
		}		
		
		$allow_cron = true;
		if ($MSQS_QL->option_checked('mw_wc_qbo_sync_email_log') || $MSQS_QL->option_checked('mw_wc_qbo_sync_auto_refresh') || $allow_cron){

			if (! wp_next_scheduled ( 'mw_qbo_sync_logging_hook' )){
				wp_schedule_event(time(), 'daily', 'mw_qbo_sync_logging_hook');
			}
			add_action('mw_qbo_sync_logging_hook', array($this,'mw_qbo_sync_logging_callback'));						
		}else{
			wp_clear_scheduled_hook('mw_qbo_sync_logging_hook');
		}
		
		#Not Using Now
		/**/
		$is_using_bulit_in_paypal = false;
		$d_paypal_opt = get_option('woocommerce_paypal_settings');
		if(is_array($d_paypal_opt) && isset($d_paypal_opt['enabled']) && $d_paypal_opt['enabled'] == 'yes'){
			//$is_using_bulit_in_paypal = true;
		}
		
		if($is_using_bulit_in_paypal && $MSQS_QL->get_option('mw_wc_qbo_sync_d_paypal_notice_status') != 'true'){
			//add_action( 'admin_notices', array($this,'qbo_setup_admin_notice_bulit_in_paypal') );
		}
		
	}
	
	public function qbo_setup_admin_notice_bulit_in_paypal(){
		//$a_style = 'style="display:none;"';
		//$current_page = 'javascript:void(0);';
		$a_style = '';
		
		$query = 'disable_qbo_setup_admin_d_paypal_notice=true';
		$current_page = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$parsedUrl = parse_url($current_page);
		if ($parsedUrl['path'] == null) {
			$current_page .= '/';
		}
		$separator = isset($parsedUrl['query']) ? '&' : '?';
		$current_page .= $separator . $query;
		
		echo '<div title="MyWorks QuickBooks Sync Error" class="notice cross-relative-icon notice-error mwqs-setup-notice">'.__("<p>MyWorks Sync: We notice you're using the built-in PayPal payments gateway to process payments for your orders! We recommend NOT using this gateway and instead installing one of the many free PayPal gateway plugins available in Plugins > Add New on your site. This built-in PayPal gateway doesn't consistently notify our sync of new orders, resulting in these orders not being automatically synced as they should be.</p>", 'mw_wc_qbo_sync').'<a '.$a_style.' href="'.$current_page.'" class="cross_icon"></a></div>';
	}
	
	public function qbo_setup_admin_notice_wp_cron(){
		global $crn_msg_co;
		$a_style = '';
		if($crn_msg_co){
			$query = 'disable_qbo_setup_admin_cron_notice=true';
			$current_page = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$parsedUrl = parse_url($current_page);
			if ($parsedUrl['path'] == null) {
				$current_page .= '/';
			}
			$separator = isset($parsedUrl['query']) ? '&' : '?';
			$current_page .= $separator . $query;
		}else{
			$a_style = 'style="display:none;"';
			$current_page = 'javascript:void(0);';
		}
		
		echo '<div title="MyWorks QuickBooks Sync Error" class="notice cross-relative-icon notice-error mwqs-setup-notice">'.__("<p>MyWorks Sync: There seems to be an issue with wp-cron not correctly running on your site. WP-cron is a built-in Wordpress scheduling functionality - which powers all scheduled events on your site, including our queue sync, so if not correctly running, orders will simply build up in the queue. Your website developer can typically assist with ensuring WP-cron is correctly running on your site.</p>", 'mw_wc_qbo_sync').'<a '.$a_style.' href="'.$current_page.'" class="cross_icon"></a></div>';
	}
	
	public function mw_qbo_sync_logging_callback() {
		global $MSQS_QL;
		$enable_refresh_data = true;
		
		if($enable_refresh_data && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			//$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
			$enable_refresh_data = false;
		}
		
		// do something daily
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_email_log')){
			$MSQS_QL->save_log('Log Email Cron','Log Email Cron Run.','Cron',2);
			$MSQS_QL->send_daily_email_log();
		}
		
		//if($MSQS_QL->option_checked('mw_wc_qbo_sync_auto_refresh')){}
		
		if($enable_refresh_data){
			$qr_log = "Quick Refresh Sync Started\n";				
			$customer_added = (int) $MSQS_QL->quick_refresh_qbo_customers();
			$product_added = (int) $MSQS_QL->quick_refresh_qbo_products();
			
			$qr_log.= "Total Customer Added: {$customer_added}\n";
			$qr_log.= "Total Product Added: {$product_added}\n";
			
			$qr_log.= "Quick Refresh Sync Ended";
			$MSQS_QL->save_log('Quick Refresh Sync',$qr_log,'Cron',2);
		}
		
	}
	
	public function mw_wc_qbo_enable_big_select_join(){
		global $wpdb;
		$wpdb->query('SET SESSION SQL_BIG_SELECTS = 1');
		$wpdb->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
	}
	
	public function mw_wc_qbo_get_service_id(){
		global $MSQS_QL;
		$mw_wc_qbo_service_id = $MSQS_QL->get_option('mw_wc_qbo_sync_service_id');
		
		echo $mw_wc_qbo_service_id;
	}
	
	public static function is_trial_version_check(){
		global $MSQS_QL;
		
		$sts_tpc = false;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_trial_license') || $MSQS_QL->is_plg_lc_p_l()){
			$query_string = explode('=',$_SERVER['QUERY_STRING']);
			if(is_array($query_string) && isset($query_string[1])){
				if( strpos( $query_string[1], "myworks-wc-qbo" ) !== false ) {
					$sts_tpc = true;
				}
			}			
		}
		
		if(!$sts_tpc){
			return '';
		}
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_trial_license')){
			//
			$image = plugin_dir_url( __FILE__ ) . 'image/minilogo-square.png';
			echo '
			<div class="text-btn mwqb_tlmb" style="position: relative;">
				<img width="25"  alt="MyWorks Sync" title="MyWorks Sync" src="'.$image.'">&nbsp;
				<h3><b>'.(int) $MSQS_QL->get_option('mw_wc_qbo_sync_trial_days_left').'</b> &nbsp;DAYS LEFT ON YOUR FREE TRIAL</h3>
				<a target="_blank" href="'.$MSQS_QL->get_quickbooks_connection_dashboard_url().'/clientarea.php?action=productdetails&id='.$MSQS_QL->get_option('mw_wc_qbo_sync_trial_license_serviceid').'" class="btn btn-info" role="button">UPGRADE NOW!
				</a></br>
				&nbsp;
				<a id="mwqs_tl_chk_again" style="font-size:12px;text-align: center;" href="javascript:void(0);">Check Again...</a>
			</div>
			';
			
			wp_nonce_field( 'myworks_wc_qbo_sync_trial_license_check_again', 'trial_license_check_again' );
			
			echo '
			<script>
			jQuery(document).ready(function($){
				$(\'#mwqs_tl_chk_again\').click(function(){
					jQuery(this).html(\'Loading...\');
					var data = {
						"action": \'mw_wc_qbo_sync_trial_license_check_again\',
						"trial_license_check_again": jQuery(\'#trial_license_check_again\').val(),
					};
					jQuery.ajax({
					   type: "POST",
					   url: ajaxurl,
					   data: data,
					   cache:  false ,
					   //datatype: "json",
					   success: function(result){
						   if(result!=0 && result!=\'\'){
							location.reload();
						   }else{
							 jQuery(\'#mwqs_tl_chk_again\').html(\'Error!\');					 
						   }				  
					   },
					   error: function(result) { 		
							jQuery(\'#mwqs_tl_chk_again\').html(\'Error!\');
					   }
					});
				});
			});
			</script>
			';
		}else{
			//
			echo '
			<div class="text-btn mwqb_lpmb" style="position: relative;">
				
				<h3>YOU\'RE ON OUR FREE PLAN! WANT MORE?</h3>
				<a target="_blank" href="'.$MSQS_QL->get_quickbooks_connection_dashboard_url().'/clientarea.php?action=productdetails&id='.$MSQS_QL->get_option('mw_wc_qbo_sync_service_id').'" class="btn btn-info" role="button">UPGRADE NOW!
				</a></br>
				&nbsp;
				<small>Unlimited sync, 2-Way sync, Intelligent bank deposits and more!</small>
			</div>
			';
			// Inventory support,
		}
	}
	
	/**
	 * Get admin connection settings.
	 *
	 * @since    1.0.0
	 */
	public function admin_settings_get($data=array(),$trigger=''){

		if($trigger){
			foreach($data as $value){
				$admin_connection_settings_data[$value] = get_option($value);
			}
			return $admin_connection_settings_data;
		}
	}
	
	
	public function get_customer_meta_from_order_meta($order_id,$manual=false){
		$customer_data = array();
		global $MSQS_QL;
		
		$order_meta = get_post_meta((int) $order_id);
		//$MSQS_QL->_p($order_meta);
		$_customer_user = (isset($order_meta['_customer_user'][0]))?(int) $order_meta['_customer_user'][0]:0;
		$customer_data['wc_customerid'] = $_customer_user;
		$customer_data['wc_cus_id '] = $_customer_user;		
		
		if(is_array($order_meta) && count($order_meta)){
			foreach ($order_meta as $key => $value){				
				if($MSQS_QL->start_with($key,'_billing_') || $MSQS_QL->start_with($key,'_shipping_')){
					if($MSQS_QL->start_with($key,'_billing_')){
						$key = str_replace('_billing_','billing_',$key);
					}else{
						$key = str_replace('_shipping_','shipping_',$key);
					}					
					$customer_data[$key] = ($value[0])?$value[0]:'';
				}				
			}
		}
		
		$_order_currency = (isset($order_meta['_order_currency'][0]))?$order_meta['_order_currency'][0]:'';
		$customer_data['currency'] = $_order_currency;
		
		$customer_data['note'] = '';
		
		$mw_wc_display_name = $MSQS_QL->wc_get_display_name($customer_data,true);
		$customer_data['display_name'] = $mw_wc_display_name;
		
		$customer_data['manual'] = $manual;

		# New
		$customer_data['order_id'] = $order_id;
		$customer_data['values_from_order_meta'] = true;
		
		return $customer_data;
	}
	
	//
	public function cus_meta_usk(){
		global $wpdb;
		$us_k = array();
		$us_k[] = 'rich_editing';
		$us_k[] = 'syntax_highlighting';
		$us_k[] = 'comment_shortcuts';
		$us_k[] = 'admin_color';
		$us_k[] = 'use_ssl';
		$us_k[] = 'show_admin_bar_front';
		$us_k[] = 'locale';
		$us_k[] = $wpdb->prefix.'capabilities';
		$us_k[] = $wpdb->prefix.'user_level';
		$us_k[] = 'session_tokens';
		$us_k[] = 'last_update';
		$us_k[] = '_stripe_customer_id';
		$us_k[] = '_woocommerce_persistent_cart_1';
		
		return $us_k ;
	}
	
	public function mw_qbo_sync_customer_meta($customer_id='',$manual=false,$order_id=0){
			
		if($customer_id){
			global $MSQS_QL;
			$user_info = get_userdata($customer_id);
			$user_id = $user_info->ID;
			$mw_wc_first_name = $user_info->first_name?$user_info->first_name:'';
		    $mw_wc_last_name = $user_info->last_name?$user_info->last_name:'';
		    $mw_wc_display_name = $user_info->display_name?$user_info->display_name:'';
		    $mw_wc_email = $user_info->user_email?$user_info->user_email:'';
		    $mw_wc_username = $user_info->user_login?$user_info->user_login:'';
			
			$website = $user_info->user_url?$user_info->user_url:'';
			//
			$user_meta = get_user_meta($user_id);
			
			$customer_data = array();
			$customer_data['wc_customerid'] = $user_id;
			$customer_data['wc_cus_id '] = $user_id;
			
			$customer_data['firstname'] = $mw_wc_first_name;
			$customer_data['lastname'] = $mw_wc_last_name;
			
			$customer_data['email'] = $mw_wc_email;
			$customer_data['username'] = $mw_wc_username;
			$customer_data['website'] = $website;
			
			$mw_wc_billing_company = (isset($user_meta['billing_company'][0]))?$user_meta['billing_company'][0]:'';
			$customer_data['company'] = $mw_wc_billing_company;
			
			$s_all_meta = true;
			if(is_array($user_meta) && count($user_meta)){
				foreach ($user_meta as $key => $value){
					if($MSQS_QL->start_with($key,'billing_') || $MSQS_QL->start_with($key,'shipping_') || $s_all_meta){					
						$customer_data[$key] = ($value[0])?$value[0]:'';
					}				
				}
			}
			
			if($s_all_meta){
				$us_k = $this->cus_meta_usk();
				if(is_array($customer_data) && count($customer_data) && is_array($us_k) && count($us_k)){
					foreach($us_k as $v){
						if(isset($customer_data[$v])){
							unset($customer_data[$v]);
						}
					}
				}
			}
			
			//$customer_data['display_name'] = $mw_wc_display_name;		
			
			//
			$customer_data['note'] = '';
			$customer_data['manual'] = $manual;
			
			/**/
			$order_id = (int) $order_id;
			if($order_id > 0){
				$customer_data['firstname'] = get_post_meta($order_id,'_billing_first_name',true);
				$customer_data['lastname'] = get_post_meta($order_id,'_billing_last_name',true);
				
				$customer_data['currency'] = get_post_meta($order_id,'_order_currency',true);
				
				# New
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr') || $MSQS_QL->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company') || $MSQS_QL->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
					$order_meta = get_post_meta((int) $order_id);
					if(is_array($order_meta) && count($order_meta)){
						foreach ($order_meta as $key => $value){				
							if($MSQS_QL->start_with($key,'_billing_') || $MSQS_QL->start_with($key,'_shipping_')){
								if($MSQS_QL->start_with($key,'_billing_')){
									$key = str_replace('_billing_','billing_',$key);
								}else{
									$key = str_replace('_shipping_','shipping_',$key);
								}					
								$customer_data[$key] = ($value[0])?$value[0]:'';
							}				
						}
					}
					
					if(isset($customer_data['billing_company']) && !empty($customer_data['billing_company'])){
						$customer_data['company'] = $customer_data['billing_company'];
					}
				}
			}else{
				$customer_data['currency'] = (string) $MSQS_QL->get_wc_customer_currency($user_id);
				if(empty($customer_data['currency'])){
					$customer_data['currency'] = get_woocommerce_currency();
				}
			}
			
			$mw_wc_display_name = $MSQS_QL->wc_get_display_name($customer_data);
			$customer_data['display_name'] = $mw_wc_display_name;

			# New			
			if($order_id > 0){
				$customer_data['order_id'] = $order_id;
			}

			return $customer_data;
		}
	}
	
	public function myworks_wc_qbo_sync_activation_redirect(){
		global $MSQS_QL;
		$this->check_setup_status();
	}
	
	public function check_setup_status(){
		global $MSQS_QL;		
		$is_setup_ok = true;
		$error_msg = array();
		global $mwqs_admin_msg;
		$widget_msg = array();
		
		if (!(int) $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_connected')){
			$error_msg[] = __('QuickBooks not connected.','mw_wc_qbo_sync');
			$mwqs_admin_msg[] = __('QuickBooks not connected <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-connection').'">click here</a> to connect.','mw_wc_qbo_sync');
			$is_setup_ok = false;
		}
		
		if($is_setup_ok){
			global $wpdb;
			$qbo_customer_remote_data = array();
			$qbo_product_remote_data = array();
			
			//04-07-2017
			$wc_local_customer_data = array();
			$wc_local_product_data = array();
			
			$wc_local_variation_data = array();
			
			if($MSQS_QL->is_connected()){
				$Context = $MSQS_QL->getContext();
				$realm = $MSQS_QL->getRealm();
				$CustomerService = new QuickBooks_IPP_Service_Customer();
				$qbo_customer_remote_data = $CustomerService->query($Context,$realm,"SELECT Id FROM Customer STARTPOSITION 1 MaxResults 1 ");
				
				$ItemService = new QuickBooks_IPP_Service_Term();
				$qbo_product_remote_data = $ItemService->query($Context, $realm, "SELECT Id , Name FROM Item ORDER BY Name ASC STARTPOSITION 1 MaxResults 1 ");
				
				//wc data
				$roles = 'customer';
				$roles = array_map('trim',explode( ",", $roles ));
				$pq_whr = " WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND ( ";
				$i = 1;
				foreach ( $roles as $role ) {
					$pq_whr .= 'um.meta_value    LIKE    \'%"' . $role . '"%\' ';
					if ( $i < count( $roles ) ) $sql .= ' OR ';
					$i++;
				}
				$pq_whr .= ' ) ';
				
				$cq = "SELECT wu.ID FROM {$wpdb->users} wu INNER JOIN {$wpdb->usermeta} um ON wu.ID = um.user_id {$pq_whr} LIMIT 0,1";
				$wc_local_customer_data = $MSQS_QL->get_row($cq);				
				
				$pq = "SELECT ID FROM {$wpdb->posts} WHERE post_type =  'product' AND post_status NOT IN('trash','auto-draft','inherit') LIMIT 0,1";
				$wc_local_product_data = $MSQS_QL->get_row($pq);

				$pq = "SELECT ID FROM {$wpdb->posts} WHERE post_type =  'product_variation' AND post_status NOT IN('trash','auto-draft','inherit') LIMIT 0,1";
				//$wc_local_variation_data = $MSQS_QL->get_row($pq);
			}
			
			$quick_refresh_error = false;
			$customer_map_error = false;
			$product_map_error = false;
			
			if(is_array($qbo_customer_remote_data) && count($qbo_customer_remote_data) && is_array($wc_local_customer_data) && count($wc_local_customer_data)){
				$qbo_customer_local_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_qbo_customers', 'id', '', '', ' 0,1' );
				if(empty($qbo_customer_local_data)){
					$is_setup_ok = false;
					$error_msg[] = __('QuickBooks customers not found in local table.','mw_wc_qbo_sync');
					$quick_refresh_error = true;
				}else{
					$customer_map_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_customer_pairs', 'id', '', '', ' 0,1' );
					//$customer_map_data = array();
					if(empty($customer_map_data)){
						$sp_s_cus_map_ok = false;
						if($MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt') && $MSQS_QL->get_option('mw_wc_qbo_sync_orders_to_specific_cust')!=''){
							$sp_s_cus_map_ok = true;
						}

						//
						if(!$sp_s_cus_map_ok){
							if($MSQS_QL->option_checked('mw_wc_qbo_sync_all_order_to_customer')){
								$sp_s_cus_map_ok = true;
							}
						}
						
						if(!$sp_s_cus_map_ok){
							$is_setup_ok = false;
							$error_msg[] = __('Customers not mapped.','mw_wc_qbo_sync');
							$customer_map_error = true;
							delete_option('mw_wc_qbo_sync_qbo_is_data_mapped_customer');
						}else{
							update_option('mw_wc_qbo_sync_qbo_is_data_mapped_customer','true');
						}
						
					}else{
						update_option('mw_wc_qbo_sync_qbo_is_data_mapped_customer','true');
					}
				}
				
			}
			
			if(is_array($qbo_product_remote_data) && count($qbo_product_remote_data) && is_array($wc_local_product_data) && count($wc_local_product_data)){
				$qbo_product_local_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_qbo_items', 'id', '', '', ' 0,1' );
				if(empty($qbo_product_local_data)){
					$is_setup_ok = false;
					$error_msg[] = __('QuickBooks products not found in local table.','mw_wc_qbo_sync');
					$quick_refresh_error = true;
				}else{
					$product_map_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_product_pairs', 'id', '', '', ' 0,1' );
					$variation_map_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_variation_pairs', 'id', '', '', ' 0,1' );
					if(empty($product_map_data) && empty($variation_map_data)){
						$is_setup_ok = false;
						$error_msg[] = __('Products not mapped.','mw_wc_qbo_sync');
						$product_map_error = true;
						delete_option('mw_wc_qbo_sync_qbo_is_data_mapped_product');
					}else{
						update_option('mw_wc_qbo_sync_qbo_is_data_mapped_product','true');
					}
				}
				
			}
			
			
			//17-07-2017 - For empty customer/products
			if(empty($qbo_customer_remote_data) || empty($wc_local_customer_data)){
				update_option('mw_wc_qbo_sync_qbo_is_data_mapped_customer','true');
			}
			
			if(empty($qbo_product_remote_data) || empty($wc_local_product_data)){
				update_option('mw_wc_qbo_sync_qbo_is_data_mapped_product','true');
			}
			
			if($quick_refresh_error){
				$error_msg[] = __('Please click on Quick refresh data button in plugin <a href="admin.php?page=myworks-wc-qbo-sync">dashboard</a>.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('Please <a href="'.site_url('index.php?mw_qbo_sync_public_quick_refresh=1').'">click here</a> to add customers and products from QuickBooks.','mw_wc_qbo_sync');
				delete_option('mw_wc_qbo_sync_qbo_is_refreshed');
			}else{
				update_option('mw_wc_qbo_sync_qbo_is_refreshed','true',true);
			}
			
			if($customer_map_error){
				$mwqs_admin_msg[] = __('Customers not mapped <a href="'.admin_url('admin.php?page=myworks-wc-qbo-map').'">click here</a> to map.','mw_wc_qbo_sync');
			}
			
			if($product_map_error){
				$mwqs_admin_msg[] = __('Products not mapped <a href="'.admin_url('admin.php?page=myworks-wc-qbo-map&tab=product').'">click here</a> to map.','mw_wc_qbo_sync');
			}
			
			//28-04-2017
			$is_default_settings_ok = true;
			
			$mw_wc_qbo_sync_default_qbo_item = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_default_qbo_item');
			if(!$mw_wc_qbo_sync_default_qbo_item){
				$is_setup_ok = false;
				$error_msg[] = __('QuickBooks default product not set in settings.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('QuickBooks default product not set in <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-settings').'">settings</a>.','mw_wc_qbo_sync');
				$is_default_settings_ok = false;
			}

			$mw_wc_qbo_sync_default_qbo_product_account = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_default_qbo_product_account');
			if(!$mw_wc_qbo_sync_default_qbo_product_account){
				$is_setup_ok = false;
				$error_msg[] = __('QuickBooks default product account not set in settings.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('QuickBooks default product account not set in <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-settings').'">settings</a>.','mw_wc_qbo_sync');
				$is_default_settings_ok = false;
			}
			
			$mw_wc_qbo_sync_default_qbo_asset_account = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_default_qbo_asset_account');
			if(!$mw_wc_qbo_sync_default_qbo_asset_account){
				$is_setup_ok = false;
				$error_msg[] = __('QuickBooks default product inventory asset account not set in settings.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('QuickBooks default product inventory asset account not set in <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-settings').'">settings</a>.','mw_wc_qbo_sync');
				$is_default_settings_ok = false;
			}
			
			$mw_wc_qbo_sync_default_qbo_expense_account = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_default_qbo_expense_account');
			if(!$mw_wc_qbo_sync_default_qbo_expense_account){
				$is_setup_ok = false;
				$error_msg[] = __('QuickBooks default product expense account not set in settings.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('QuickBooks default product expense account not set in <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-settings').'">settings</a>.','mw_wc_qbo_sync');
				$is_default_settings_ok = false;
			}
			
			/*
			$mw_wc_qbo_sync_default_qbo_discount_account = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_default_qbo_discount_account');
			if(!$mw_wc_qbo_sync_default_qbo_discount_account){
				$is_setup_ok = false;
				$error_msg[] = __('QuickBooks default product discount account not set in settings.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('QuickBooks default product expense account not set in <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-settings').'">settings</a>.','mw_wc_qbo_sync');
				$is_default_settings_ok = false;
			}
			*/
			
			/*
			$mw_wc_qbo_sync_default_coupon_code = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_default_coupon_code');
			if(!$mw_wc_qbo_sync_default_coupon_code){
				$is_setup_ok = false;
				$error_msg[] = __('QuickBooks default coupon product not set in settings.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('QuickBooks default product expense account not set in <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-settings').'">settings</a>.','mw_wc_qbo_sync');
				$is_default_settings_ok = false;
			}
			*/
			
			$mw_wc_qbo_sync_default_shipping_product = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_default_shipping_product');
			if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') && !$mw_wc_qbo_sync_default_shipping_product && !$MSQS_QL->get_qbo_company_setting('is_shipping_allowed') && !$MSQS_QL->option_checked('mw_wc_qbo_sync_odr_shipping_as_li')){
				$is_setup_ok = false;
				$error_msg[] = __('QuickBooks default shipping product not set in settings.','mw_wc_qbo_sync');
				$mwqs_admin_msg[] = __('QuickBooks default product expense account not set in <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-settings').'">settings</a>.','mw_wc_qbo_sync');
				$is_default_settings_ok = false;
			}
			
			$p_map_whr = " `wc_paymentmethod` !='' AND `qbo_account_id` >0 AND `enable_payment` = 1 ";
			$qbo_payment_map_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_paymentmethod_map', 'id', $p_map_whr, '', ' 0,1' );
			
			if(empty($qbo_payment_map_data)){
				$error_msg[] = __('Payment methods not mapped.','mw_wc_qbo_sync');
				$is_setup_ok = false;
				$mwqs_admin_msg[] = __('Payment methods not mapped <a href="'.admin_url('admin.php?page=myworks-wc-qbo-map&tab=payment-method').'">click here</a> to map.','mw_wc_qbo_sync');
				delete_option('mw_wc_qbo_sync_qbo_is_data_mapped_payment');
			}else{
				update_option('mw_wc_qbo_sync_qbo_is_data_mapped_payment','true');
			}

			if($MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_data_mapped_payment') && $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_data_mapped_product') && $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_data_mapped_customer')){
				update_option('mw_wc_qbo_sync_qbo_is_data_mapped','true',true);
			}else{
				delete_option('mw_wc_qbo_sync_qbo_is_data_mapped');
			}

			if($is_default_settings_ok){
				update_option('mw_wc_qbo_sync_qbo_is_default_settings','true',true);
			}else{
				delete_option('mw_wc_qbo_sync_qbo_is_default_settings');
			}
			
		}
		if(count($error_msg)){
			$MSQS_QL->set_session_msg('mw_wc_qbo_sync_activation_session_msg',$error_msg);
		}
		
		if($is_setup_ok){
			update_option('mw_qbo_sync_activation_redirect', 'true');			
		}else{
			update_option('mw_qbo_sync_activation_redirect', 'false');
			//add_action( 'admin_notices', array($this,'qbo_setup_admin_notice'));
		}
		return $is_setup_ok;
	}
	
	public function qbo_setup_admin_notice() {
		/*
		echo '<div class="notice notice-error mwqs-setup-notice"><p>'.__( 'MyWorks QuickBooks Sync Initial Setup Required <a href="'.admin_url('admin.php?page=myworks-wc-qbo-sync-init').'">view</a>', 'mw_wc_qbo_sync' ).'</p></div>';
		*/
		global $mwqs_admin_msg;
		if(isset($mwqs_admin_msg) && is_array($mwqs_admin_msg) && count($mwqs_admin_msg)){
			echo '<div title="MyWorks QuickBooks Sync Setup Error" class="notice notice-error mwqs-setup-notice">';
			foreach($mwqs_admin_msg as $msg){											
				echo '<p>'.$msg.'</p>';						
			}
			echo '</div>';
		}
		
	}
	
	public function plugin_help_init(){
		add_action( 'admin_notices', array($this,'qbo_help_admin_notice') );
	}
	
	public function qbo_help_admin_notice() {
		$query_string = explode('=',$_SERVER['QUERY_STRING']);
		if(isset($query_string[1])){
			if( strpos( $query_string[1], "myworks-wc-qbo" ) !== false ) {
				global $MSQS_QL;
				if(!(int) $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_connected') || !$MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_refreshed') || !$MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_default_settings') || !$MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_data_mapped')){
					//require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-init.php';
					require_once plugin_dir_path( __FILE__ ) . 'partials/myworks-wc-qbo-sync-init-new.php';
				}
			}
		}
	}
	
	public static function mw_qbo_sync_version_control(){
		global $MSQS_QL;
		global $MWQS_OF;
		$mw_wc_qbo_sync_license = $MSQS_QL->get_option('mw_wc_qbo_sync_license');
		$mw_wc_qbo_sync_localkey = $MSQS_QL->get_option('mw_wc_qbo_sync_localkey');		
		$mw_wc_qbo_sync_update_option = $MSQS_QL->get_option('mw_wc_qbo_sync_update_option');
		
		/*
		if($MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey)){
			require_once( WP_PLUGIN_DIR . '/myworks-woo-sync-for-quickbooks-online/wp-updates-plugin.php' );
			new WPUpdatesPluginUpdater_1570( 'http://wp-updates.com/api/2/plugin', 'myworks-woo-sync-for-quickbooks-online/myworks-woo-sync-for-quickbooks-online.php' );
			
			if($mw_wc_qbo_sync_update_option == 'true'){
				require_once( WP_PLUGIN_DIR . '/myworks-woo-sync-for-quickbooks-online/wp-updates-plugin-beta.php' );
				new WPUpdatesPluginUpdater_1604( 'http://wp-updates.com/api/2/plugin', 'myworks-woo-sync-for-quickbooks-online/myworks-woo-sync-for-quickbooks-online.php' );
			}
		}
		*/
	}
	
	public function mw_qbo_sync_beta_version_control(){
		global $MSQS_QL;
		global $MWQS_OF;

		if(!$MSQS_QL->get_option('mw_wc_qbo_sync_update_option_date')){
			$date = strtotime("+8 day", strtotime($MSQS_QL->now('Y-m-d')));
			update_option('mw_wc_qbo_sync_update_option_date', date('Y-m-d', $date));
		}

		if(!$MSQS_QL->get_option('mw_wc_qbo_sync_update_option')){
			update_option('mw_wc_qbo_sync_update_option','false');
		}

		if($MSQS_QL->get_option('mw_wc_qbo_sync_update_option_date') == $MSQS_QL->now('Y-m-d')){
			update_option('mw_wc_qbo_sync_update_option','false');
		}	
	}

	public static function nginx_register_apache_request_headers(){

		if( !function_exists('apache_request_headers') ) {
		    function apache_request_headers() {
		        $arh = array();
		        $rx_http = '/\AHTTP_/';

		        foreach($_SERVER as $key => $val) {
		            if( preg_match($rx_http, $key) ) {
		                $arh_key = preg_replace($rx_http, '', $key);
		                $rx_matches = array();
			           // do some nasty string manipulations to restore the original letter case
			           // this should work in most cases
		                $rx_matches = explode('_', $arh_key);

		                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
		                    foreach($rx_matches as $ak_key => $ak_val) {
		                        $rx_matches[$ak_key] = ucfirst($ak_val);
		                    }

		                    $arh_key = implode('-', $rx_matches);
		                }

		                $arh[$arh_key] = $val;
		            }
		        }

		        return( $arh );
		    }
		}
	}

	public static function return_plugin_version(){
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/myworks-woo-sync-for-quickbooks-online/myworks-woo-sync-for-quickbooks-online.php', false, false );
		return $plugin_data['Version'];
	}

	public static function mw_wc_qbo_sync_qbo_is_init(){
		if(isset($_GET['mw_wc_qbo_sync_qbo_is_init']) && $_GET['mw_wc_qbo_sync_qbo_is_init']=='true'){
			update_option('mw_wc_qbo_sync_qbo_is_init','true');
		}
	}
	
	/*16-07-2018*/
	public function fix_db_alter_issue($redirect=false,$rd_url=''){
		global $MSQS_QL;
		global $wpdb;
		$server_db = $MSQS_QL->db_check_get_fields_details();
		//$MSQS_QL->_p($server_db);
		if(is_array($server_db) && count($server_db)){
			foreach($server_db as $k=>$v){
				$is_db_updated = false;
				if($k == $wpdb->prefix.'mw_wc_qbo_sync_payment_id_map'){
					if(!array_key_exists("is_wc_order",$v)){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_payment_id_map` ADD `is_wc_order` INT(1) NOT NULL DEFAULT '0' AFTER `qbo_payment_id`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
				}
				
				if($k == $wpdb->prefix.'mw_wc_qbo_sync_paymentmethod_map'){
					if(!array_key_exists("ps_order_status",$v)){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `ps_order_status` VARCHAR(255) NOT NULL AFTER `term_id`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
					
					if(!array_key_exists("individual_batch_support",$v)){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `individual_batch_support` INT(1) NOT NULL AFTER `ps_order_status`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
					
					if(!array_key_exists("deposit_cron_utc",$v)){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `deposit_cron_utc` VARCHAR(255) NOT NULL AFTER `individual_batch_support`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
					if(!array_key_exists("inv_due_date_days",$v)){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `inv_due_date_days` INT(3) NOT NULL AFTER `deposit_cron_utc`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
					
					if(!array_key_exists("order_sync_as",$v)){						
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `order_sync_as` VARCHAR(255) NOT NULL AFTER `inv_due_date_days`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
					
					if(!array_key_exists("deposit_date_field",$v)){						
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `deposit_date_field` VARCHAR(255) NOT NULL AFTER `order_sync_as`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
					
					if(!array_key_exists("deposit_cron_sch",$v)){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `deposit_cron_sch` VARCHAR(255) NOT NULL AFTER `deposit_cron_utc`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
				}
				
				if($k == $wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'){
					if(!array_key_exists("ext_data",$v)){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_wq_cf_map` ADD `ext_data` TEXT NOT NULL AFTER `qb_field`;";
						$wpdb->query($sql);
						$is_db_updated = true;
					}
					
					//
					if(isset($v['wc_field']['Type']) && $v['wc_field']['Type'] != 'text'){
						$sql = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_wq_cf_map` MODIFY `wc_field` TEXT NOT NULL;";
						$wpdb->query($sql);
						$is_db_updated = true;
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
				)';
				$wpdb->query($sql);
				$is_new_db_tbl_created = true;
			}
			
			if(!isset($server_db[$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map'])){
				$sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map` (
				`id` int(11) NOT NULL AUTO_INCREMENT,								  
				`wc_field` TEXT NOT NULL,
				`qb_field` varchar(255) NOT NULL,
				`ext_data` TEXT NOT NULL,
				PRIMARY KEY (`id`)
				)';
				$wpdb->query($sql);
				$is_new_db_tbl_created = true;
			}
			
			#New
			if(!isset($server_db[$wpdb->prefix.'mw_wc_qbo_sync_sessions'])){
				$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mw_wc_qbo_sync_sessions` (
				  `session_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				  `session_key` char(32) NOT NULL,
				  `session_value` longtext NOT NULL,
				  `session_expiry` BIGINT UNSIGNED NOT NULL,
				  PRIMARY KEY (`session_id`),
				  UNIQUE KEY `session_key` (`session_key`)
				)";
				$wpdb->query($sql);
				$is_new_db_tbl_created = true;
			}			
		}
		
		/**/
		$enable_oauth2_db_changes = false;
		if($enable_oauth2_db_changes && !$MSQS_QL->option_checked('mw_wc_qbo_sync_is_oauth2_db_change_applied')){
			$sql = 'ALTER TABLE `quickbooks_oauth` RENAME TO `quickbooks_oauthv1`;';
			$wpdb->query($sql);
			
			$sql = 'ALTER TABLE `quickbooks_oauthv1` CHANGE `quickbooks_oauth_id` `quickbooks_oauthv1_id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST;';
			$wpdb->query($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `quickbooks_oauthv2` (
			  `quickbooks_oauthv2_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `app_tenant` varchar(255) NOT NULL,
			  `oauth_state` varchar(255) DEFAULT NULL,
			  `oauth_access_token` text,
			  `oauth_refresh_token` text,
			  `oauth_access_expiry` datetime DEFAULT NULL,
			  `oauth_refresh_expiry` datetime DEFAULT NULL,
			  `qb_realm` varchar(32) DEFAULT NULL,
			  `request_datetime` datetime NOT NULL,
			  `access_datetime` datetime DEFAULT NULL,
			  `last_access_datetime` datetime DEFAULT NULL,
			  `last_refresh_datetime` datetime DEFAULT NULL,
			  `touch_datetime` datetime DEFAULT NULL,
			  PRIMARY KEY (`quickbooks_oauthv2_id`)
			);';
			$wpdb->query($sql);
			
			update_option('mw_wc_qbo_sync_is_oauth2_db_change_applied','true');
		}
		
		update_option('mw_wc_qbo_sync_cur_db_version',$this->cur_db_version);
	}
	
	/*Not Using This*/
	public static function db_checker($file_version,$db_version){
		global $wpdb;
		$db_delta = MyWorks_WC_QBO_Sync_Admin::determine($file_version,$db_version);		
		if(count($db_delta)){
			foreach($db_delta as $query){
				if($query)
				$wpdb->query($query);				
			}
		}
	}
	
	/*Not Using This*/
	public function health_checker($operation=''){

		global $wpdb;
		global $MSQS_QL;
		$tables = array(
			'mw_wc_qbo_sync_customer_pairs',
			'mw_wc_qbo_sync_log',
			'mw_wc_qbo_sync_paymentmethod_map',
			'mw_wc_qbo_sync_payment_id_map',
			'mw_wc_qbo_sync_product_pairs',
			//'mw_wc_qbo_sync_promo_code_product_map',
			'mw_wc_qbo_sync_qbo_customers',
			'mw_wc_qbo_sync_qbo_items',
			'mw_wc_qbo_sync_real_time_sync_history',
			'mw_wc_qbo_sync_real_time_sync_queue',
			'mw_wc_qbo_sync_shipping_product_map',
			'mw_wc_qbo_sync_tax_map',
			'mw_wc_qbo_sync_variation_pairs',
			'mw_wc_qbo_sync_wq_cf_map'
		);
		
		//
		if($MSQS_QL->is_wq_vendor_pm_enable()){
			//$tables[] = 'mw_wc_qbo_sync_qbo_vendors';
			//$tables[] = 'mw_wc_qbo_sync_vendor_pairs';
		}
		
		if($operation=='tables'){
			return $tables;
		}else{

			$missing_db = array();
			foreach ( $tables as $table ) {
				if($wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s;", $wpdb->prefix . $table ) ) !== $wpdb->prefix . $table){
					$missing_db[] = $table;
				}
			}
			
			if(count($missing_db)){
				$MSQS_QL->set_session_val('missing_db',$missing_db);
			}
			
			add_action( 'admin_notices', array($this,'qbo_setup_admin_notice_health_check') );
		}
	}
	
	public function qbo_setup_admin_notice_health_check() {
		global $MSQS_QL;				
		if(is_array($MSQS_QL->get_session_val('missing_db')) && count($MSQS_QL->get_session_val('missing_db'))){
			echo '<div class="notice notice-error mwqs-setup-notice">
			MyWorks Sync Database Table Missing: ';
			echo implode(',',$MSQS_QL->get_session_val('missing_db',array(),true));
			echo '</div>';
		}
		
		if($MSQS_QL->check_invalid_chars_in_db_conn_info()){

			echo '<div title="MyWorks QuickBooks Sync Setup Error" class="notice notice-error mwqs-setup-notice">'.__("MyWorks QuickBooks Online Sync for WooCommerce does not support these special characters in your database password in your wp-config.php file:  (+ / # % ‘ ?)  Please update your database password to not include these characters.", 'mw_wc_qbo_sync').'</div>';
		}
	}
	
	/*Not Using This*/
	public static function determine($base,$peak){
		global $MSQS_QL;
		global $wpdb;
		$db_array = $versions = array();
		
		$versions['1.0.8'] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'mw_wc_qbo_sync_variation_pairs` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `wc_variation_id` int(11) NOT NULL,
								  `quickbook_product_id` int(11) NOT NULL,
								  `class_id` varchar(255) NOT NULL,
								  PRIMARY KEY (`id`)
								)';
		$versions['1.0.19'][] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_payment_id_map` ADD `is_wc_order` INT(1) NOT NULL DEFAULT '0' AFTER `qbo_payment_id`;";
		$versions['1.0.19'][] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `ps_order_status` VARCHAR(255) NOT NULL AFTER `term_id`;";
		$versions['1.0.19'][] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `individual_batch_support` INT(1) NOT NULL AFTER `ps_order_status`;
		";
		$versions['1.0.20'] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,								  
								  `wc_field` TEXT NOT NULL,
								  `qb_field` varchar(255) NOT NULL,
								  `ext_data` TEXT NOT NULL,
								  PRIMARY KEY (`id`)
								)';
								
		$versions['1.0.21'][] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `deposit_cron_utc` VARCHAR(255) NOT NULL AFTER `individual_batch_support`;";
		
		//
		$versions['1.5.2'][] = "ALTER TABLE `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` ADD `inv_due_date_days` INT(3) NOT NULL AFTER `deposit_cron_utc`;";

		foreach($versions as $key=>$value){
			if(version_compare($key, $peak, '>') && version_compare($key, $base, '<=')){
				if($value!=''){
				    if(is_array($value)){
				        foreach($value as $sub_val)
					    $db_array[] = $sub_val;
				    }else{
				        $db_array[] = $value;
				    }
				}				
			}
		}
		
		//settings option
		if(version_compare('1.0.33', $peak, '>') && version_compare('1.0.33', $base, '<=')){
			if($MSQS_QL->get_option('mw_wc_qbo_sync_specific_order_status')==''){
				update_option('mw_wc_qbo_sync_specific_order_status','wc-processing,wc-completed');
			}
		}
		
		return $db_array;
	}
	// 07-03-2017
	
	public static function plugin_version_updated(){
		global $MSQS_QL;
		$current_version = MyWorks_WC_QBO_Sync_Admin::return_plugin_version();
		$old_version = $MSQS_QL->get_option('mw_qbo_sync_last_updated_version');
		if(!$old_version){
			update_option('mw_qbo_sync_last_updated_version',$current_version);
		}else{
			if (version_compare($current_version, $old_version, '<=')) {
			    return true;
			}else{
				
				//MyWorks_WC_QBO_Sync_Admin::db_checker($current_version,$old_version);				

				$url = get_bloginfo('url');
				$company = get_bloginfo('name');
				$email = get_bloginfo('admin_email');
				$license_key = $MSQS_QL->get_option('mw_wc_qbo_sync_license');
				
				// Peter disabled 06/26/17
				/*
				$message = "<b>WooCommerce Sync for QuickBooks Online</b></br>";
			    $message .= "</br>";
			    $message .= "<b>License Key:</b> " . $license_key ."</br>";
			    $message .= "</br>";
			    $message .= "<b>Old Version:</b> " . $old_version ."</br>";
			    $message .= "<b>New Version:</b> " . $current_version . "</br>";
			    $message .= "</br>";
			    $message .= "<b>Company:</b> " .$company ."</br>";
				$message .= "<b>Email:</b> " .$email ."</br>";
				$message .= "<b>WooCommerce URL:</b> " .$url ."</br>";
				
				$headers = array(
					'MIME-Version: 1.0',
					'Content-type:text/html;charset=UTF-8',
				);		
				
				$to = 'notifications@myworks.design';

				wp_mail($to, 'Upgrade - WooCommerce Sync', $message, $headers);
				*/
				
				$post_url = 'https://myworks.design/dashboard/api/dashboard/product/saveModule';
				
				$params = array(
					'api_version'=>'0.1',
					'result_type'=>'json',
					'process'=>'upgrade',
					'licensekey'=>$license_key,
					'version'=>$current_version,	
					'company'=>$company,
					'email'=>$email,
					'system_url'=>$url
				);

			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL, $post_url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); curl_setopt($ch, CURLOPT_PROXYPORT, 3128); curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $params); $response = curl_exec($ch);
			    curl_close($ch);

				update_option('mw_qbo_sync_last_updated_version',$current_version);
			}
		}
	}
	
	/*Not Using This*/
	public function mw_qbo_wc_product_delete($product_id=''){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			return false;
		}
		if($product_id){
			global $post_type;
			if ( $post_type != 'product' ) return false;
			//Delete Product
		}
	}
	
	public function delete_product_mapping($product_id){
		if(!class_exists('WooCommerce')) return;
		global $post_type;
		if ( $post_type != 'product' ) return false;
		
		$product_id = (int) $product_id;
		if($product_id > 0){
			global $wpdb;
			$wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->prefix}mw_wc_qbo_sync_product_pairs` WHERE `wc_product_id` = %d",$product_id));
		}
	}
	
	public function mw_qbo_wc_variation_save($post_id='', $post=''){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		global $wpdb;
		
		//25-10-2017 - Add into queue
		if(!is_array($post_id) && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Variation')){
				$MSQS_QL->real_time_hooks_add_queue('Variation',$post_id,'VariationPush',':mw_qbo_wc_variation_save');
			}
		}
		
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			return false;
		}
		
		if(is_array($post_id)){
			$variation_id = (int) $post_id['variation_id'];
			$manual = true;
		}else{
			$variation_id = (int) $post_id;
			$manual = false;
		}
		
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Variation')){
			return false;
		}
		
		$product_id = $MSQS_QL->get_field_by_val($wpdb->posts,'post_parent','ID',$variation_id);
		if($variation_id && $product_id){
			$variation = get_post($variation_id);
			$product = get_post($product_id);
			if(!is_object($variation) || empty($variation)){
				if($manual){
					$MSQS_QL->save_log('Export Variation Error #'.$variation_id,'Woocommerce variation not found!','Invoice',0);
				}
				return false;
			}
			
			if(!is_object($product) || empty($product)){
				if($manual){
					$MSQS_QL->save_log('Export Variation Error #'.$variation_id,'Woocommerce variation not found!','Invoice',0);
				}
				return false;
			}
			
			if($variation->post_type!='product_variation'){
				if($manual){
					$MSQS_QL->save_log('Export Variation Error #'.$variation_id,'Woocommerce variation is not valid.','Invoice',0);
				}
				return false;
			}			
			
			if($variation->post_status=='auto-draft' || $variation->post_status=='trash'){
				return false;
			}
			
			if(!$manual && $variation->post_status=='draft'){
				return false;
			}
			
			$variation_meta = get_post_meta($variation_id);
			
			$variation_data = array();
			$variation_data['wc_product_id'] = $variation->ID;
			
			//$variation_data['name'] = $variation->post_title;
			
			if(!empty($variation->post_content)){
				$variation_data['description'] = $variation->post_content;
			}else{
				$variation_data['description'] = $product->post_content;
			}
			
			//$variation_data['short_description'] = $variation->post_excerpt;
			
			$i_v_sd = false;
			if($i_v_sd && !empty($variation->post_excerpt)){
				$variation_data['short_description'] = $variation->post_excerpt;
			}else{
				$variation_data['short_description'] = $product->post_excerpt;
			}
			
			$variation_data['post_parent'] = $variation->post_parent;
			$variation_data['post_date'] = $variation->post->post_date;
			
			$variation_data['is_variation'] = true;
			
			$variation_data['manual'] = $manual;
			
			if(is_array($variation_meta) && count($variation_meta)){
				foreach ($variation_meta as $key => $value){
					$variation_data[$key] = ($value[0])?$value[0]:'';
				}
			}
			
			/**/
			$wqp_name = $MSQS_QL->get_variation_name_from_id($variation->post_title,'',$variation->ID);
			//
			$variation_data['name_ori_pt'] = $wqp_name;
			
			$push_qpn_field = $MSQS_QL->get_option('mw_wc_qbo_sync_product_push_qpn_field');
			
			if($push_qpn_field == 'description' && !empty($variation_data['description'])){
				$wqp_name = $variation_data['description'];
			}
			
			if($push_qpn_field == 'short_description' && !empty($variation_data['short_description'])){
				$wqp_name = $variation_data['short_description'];
			}
			
			if($push_qpn_field == '_sku' && isset($variation_data['_sku']) && !empty($variation_data['_sku'])){
				$wqp_name = $variation_data['_sku'];
			}
			
			$wqp_name = strip_tags($wqp_name);
			$wqp_name = trim($wqp_name);
			
			$variation_data['name'] = $wqp_name;
			$variation_data['name'] = $MSQS_QL->get_woo_v_name_trimmed($variation_data['name']);
			
			#New
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_sync_product_images_pp')){
				$p_m_img_a = wp_get_attachment_image_src( get_post_thumbnail_id($variation_id),'single-post-thumbnail');
				$p_t_img_a = wp_get_attachment_image_src( get_post_thumbnail_id($variation_id),'thumbnail');
				
				$variation_data['p_m_img_url'] = (is_array($p_m_img_a) && !empty($p_m_img_a))?$p_m_img_a[0]:'';
				$variation_data['p_t_img_url'] = (is_array($p_t_img_a) && !empty($p_t_img_a))?$p_t_img_a[0]:'';
			}
			
			//$MSQS_QL->_p($variation_data);
			if(!$MSQS_QL->check_product_exists($variation_data)){
				return $mw_qbo_product_id = $MSQS_QL->AddProduct($variation_data);
			}else{
				/*
				if($manual){
					$MSQS_QL->save_log('Export Variation Error #'.$variation_data['wc_product_id'],'Variation already exists!','Product',0);
				}
				*/
				return $mw_qbo_product_id = $MSQS_QL->UpdateProduct($variation_data);
			}
		}
		
	}
	
	public function mw_qbo_wc_product_save($post_id='', $source=''){

		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		global $wpdb;
		
		/**/
		if(!is_array($post_id) && empty($source)){
			$mwqb_sync_uiiqb = isset($_POST['mwqb_sync_uiiqb']) ?1:0;
			//update_post_meta($post_id, 'mwqb_sync_uiiqb', $mwqb_sync_uiiqb);
		}	
		
		if(!is_array($post_id) && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Product')){
				$MSQS_QL->real_time_hooks_add_queue('Product',$post_id,'ProductPush','woocommerce_process_product_meta:mw_qbo_wc_product_save');
			}
		}
		
		if(!$MSQS_QL->is_connected()){return;}

		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			//return false;
		}
		
		if(is_array($post_id)){
			$product_id = (int) $post_id['product_id'];
			$manual = true;
		}else{
			$product_id = (int) $post_id;
			$manual = false;			
		}
		
		//$MSQS_QL->save_log('Export Product Test #'.$product_id,'Hook Testing','Product',2);return false;
		
		//if(!$manual){return false;}
		
		//Check If Real Time Enabled
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Product')){
			return false;
		}
		
		if($product_id){								
			$_product = wc_get_product( $product_id );
			if(empty($_product)){
				$MSQS_QL->save_log('Export Product Error #'.$product_id,'Woocommerce product not found. ','Product',0);
				return false;
			}
			
			//12-04-2017
			if($_product->post->post_status=='auto-draft' || $_product->post->post_status=='trash'){
				return false;
			}
			//$MSQS_QL->_p($_product);
			
			if(!$manual && $_product->post->post_status=='draft'){
				return false;
			}
			
			//Skip Parent Variation Product
			$cvp_q = $wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND post_parent=%d",$product_id);
			$chk_v_parent = $MSQS_QL->get_row($cvp_q);
			if(is_array($chk_v_parent) && count($chk_v_parent)){
				return false;
			}
			
			$product_meta = get_post_meta($product_id);
			
			$product_data = array();
			$product_data['wc_product_id'] = $_product->id;
			
			$product_data['product_type'] = $_product->product_type;
			$product_data['total_stock'] = $_product->total_stock;
			
			$product_data['description'] = $_product->post->post_content;
			$product_data['short_description'] = $_product->post->post_excerpt;
			
			//
			$wqp_name = $_product->post->post_title;
			//
			$product_data['name_ori_pt'] = $wqp_name;
			
			$push_qpn_field = $MSQS_QL->get_option('mw_wc_qbo_sync_product_push_qpn_field');
			
			if($push_qpn_field == 'description' && !empty($product_data['description'])){
				$wqp_name = $product_data['description'];
			}
			
			if($push_qpn_field == 'short_description' && !empty($product_data['short_description'])){
				$wqp_name = $product_data['short_description'];
			}
			
			$product_data['post_date'] = $_product->post->post_date;
			
			if(is_array($product_meta) && count($product_meta)){
				foreach ($product_meta as $key => $value){
					$product_data[$key] = ($value[0])?$value[0]:'';
				}
			}
			
			if($push_qpn_field == '_sku' && isset($product_data['_sku']) && !empty($product_data['_sku'])){
				$wqp_name = $product_data['_sku'];
			}
			
			$wqp_name = strip_tags($wqp_name);
			$wqp_name = trim($wqp_name);
			
			$product_data['name'] = $wqp_name;
			
			$product_data['manual'] = $manual;
			
			#New
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_sync_product_images_pp')){				
				$p_m_img_a = wp_get_attachment_image_src( get_post_thumbnail_id($product_id),'single-post-thumbnail');
				$p_t_img_a = wp_get_attachment_image_src( get_post_thumbnail_id($product_id),'thumbnail');
				
				$product_data['p_m_img_url'] = (is_array($p_m_img_a) && !empty($p_m_img_a))?$p_m_img_a[0]:'';
				$product_data['p_t_img_url'] = (is_array($p_t_img_a) && !empty($p_t_img_a))?$p_t_img_a[0]:'';
			}
			
			#$MSQS_QL->_p($product_data);
			#return false;
			
			if(!$MSQS_QL->check_product_exists($product_data)){
				return $mw_qbo_product_id = $MSQS_QL->AddProduct($product_data);
			}else{
				/*
				if($manual){
					$MSQS_QL->save_log('Export Product Error #'.$product_data['wc_product_id'],'Product already exists!','Product',0);
				}
				*/
				return $mw_qbo_product_id = $MSQS_QL->UpdateProduct($product_data);
			}			
		}
	}
	
	/**/
	public function delete_variation_mapping($variation_id){
		$variation_id = (int) $variation_id;
		if($variation_id > 0){
			global $wpdb;
			$wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->prefix}mw_wc_qbo_sync_variation_pairs` WHERE `wc_variation_id` = %d",$variation_id));
		}
	}
	
	public function mw_qbo_wc_order_payment($pmnt_sync_info=''){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		
		//
		$yithwgcp_gpc_fp_manual_payment = $MSQS_QL->get_session_val('yithwgcp_gpc_fp_manual_payment',false,true);
		
		if(!is_array($pmnt_sync_info) && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') && !$yithwgcp_gpc_fp_manual_payment){
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Payment')){
				$MSQS_QL->real_time_hooks_add_queue('Payment',$pmnt_sync_info,'PaymentPush','woocommerce_payment_complete:mw_qbo_wc_order_payment');
			}
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			return false;
		}
		//$MSQS_QL->_p($pmnt_sync_info,true);die;
		global $wpdb;
		$_transaction_id = '';
		$payment_id = '';
		$is_manual_ps_hook = false;
		if(is_array($pmnt_sync_info)){
			if(isset($pmnt_sync_info['order_id'])){
				$order_id = (int) $pmnt_sync_info['order_id'];
				$is_manual_ps_hook = true;
				$manual = false;
			}else{
				$payment_id = (int) $pmnt_sync_info['payment_id'];
				$_transaction_id = $MSQS_QL->get_field_by_val($wpdb->postmeta,'meta_value','meta_id',$payment_id);
				$order_id = (int) $MSQS_QL->get_field_by_val($wpdb->postmeta,'post_id','meta_id',$payment_id);
				if(!isset($pmnt_sync_info['queue_side']) && !isset($pmnt_sync_info['after_order'])){
					$manual = true;
				}				
			}
			
		}else{
			$order_id = (int) $pmnt_sync_info;
			$_transaction_id = get_post_meta($order_id, '_transaction_id', true );
			$manual = false;
			
			/**/
			if($yithwgcp_gpc_fp_manual_payment){
				$manual = true;
			}
		}
		
		if(!$order_id){
			return false;
		}
		
		//Check If Real Time Enabled
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Payment')){
			return false;
		}
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($order_id)){
			return false;
		}
		
		//
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($order_id)){
			return false;
		}
		
		if(!$MSQS_QL->ord_pmnt_is_mt_ls_check_by_ord_id($order_id)){
			return false;
		}
		
		if(!$manual){
			
			if(!$is_manual_ps_hook){
				if($order_id > 0 && !$MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
					$rps_oid_arr = (array) get_option('mw_qbo_sync_payment_sync_rt_hook_order_id_list');
					if(!in_array($order_id,$rps_oid_arr)){
						$rps_oid_arr[] = $order_id;
						update_option('mw_qbo_sync_payment_sync_rt_hook_order_id_list',$rps_oid_arr);
					}
					return false;
				}				
			}			
			
			
			//$MSQS_QL->save_log('Payment Sync Debug','Hook Run','Payment',2);
		}		
		
		if($manual && !$payment_id && !$yithwgcp_gpc_fp_manual_payment){
			return false;
		}
		
		//Split Order
		if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
			return false;
		}
		
		/**/
		$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($order_id);
		
		$ord_id_num = ($wc_inv_no!='')?$wc_inv_no:$order_id;
		
		//21-06-2017		
		$_transaction_id_tmp = (!$_transaction_id)?'TXN-'.$order_id:$_transaction_id;
		
		if($_transaction_id_tmp){
			$payment_data = $MSQS_QL->wc_get_payment_details_by_txn_id($_transaction_id,$order_id);
			/**/
			$p_order_data = $MSQS_QL->get_wc_order_details_from_order($order_id,get_post($order_id));
			//$MSQS_QL->_p($p_order_data);
			/**/
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_compt_yithwgcp_gpc_ed')){
				$yithwgcp_gcp_qb_acc = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc');
				if($yithwgcp_gcp_qb_acc > 0){
					if(isset($p_order_data['_ywgc_applied_gift_cards_totals']) && $p_order_data['_ywgc_applied_gift_cards_totals'] > 0){
						$payment_data['mw_qbo_yithwgcp'] = true;
						$p_order_data['mw_qbo_yithwgcp'] = true;
						
						if(floatval($p_order_data['_order_total']) == 0){
							$payment_data['mw_qbo_yith_full_gift_paid'] = true;
							$p_order_data['mw_qbo_yith_full_gift_paid'] = true;
						}else{
							$payment_data['mw_qbo_yith_partial_gift_paid'] = true;
							$p_order_data['mw_qbo_yith_partial_gift_paid'] = true;
						}
						
						$yith_gift_card_no = '';
						if(isset($p_order_data['_ywgc_applied_gift_cards']) && !empty($p_order_data['_ywgc_applied_gift_cards'])){
							$_ywgc_applied_gift_cards = @unserialize($p_order_data['_ywgc_applied_gift_cards']);
							if(is_array($_ywgc_applied_gift_cards) && !empty($_ywgc_applied_gift_cards)){
								foreach($_ywgc_applied_gift_cards as $ygc_k => $ygc_v){
									$yith_gift_card_no = $ygc_k;
									break;
								}
							}
						}
						
						$p_order_data['yith_gift_card_no'] = $yith_gift_card_no;
						
						/*Not Required for Now - Maybe We will use Later*/
						if(!isset($payment_data['transaction_id'])){
							$payment_data['order_id'] = $order_id;
							$payment_data['order_status'] = $p_order_data['order_status'];
							$payment_data['order_date'] = $p_order_data['wc_inv_date'];
							$payment_data['billing_first_name'] = $p_order_data['_billing_first_name'];
							$payment_data['billing_last_name'] = $p_order_data['_billing_last_name'];
							
							$payment_data['order_total'] = $p_order_data['_order_total'];
							if(isset($payment_data['mw_qbo_yith_full_gift_paid'])){
								$payment_data['order_total'] = $p_order_data['_ywgc_applied_gift_cards_totals'];
							}
							
							$payment_data['order_key'] = $p_order_data['_order_key'];
							$payment_data['customer_user'] = (int) $p_order_data['_customer_user'];
							$payment_data['order_currency'] = $p_order_data['_order_currency'];
							$payment_data['payment_id'] = 0;
							$payment_data['transaction_id'] = $yith_gift_card_no;
							$payment_data['paid_date'] = $p_order_data['wc_inv_date'];
							$payment_data['payment_method'] = '';
							$payment_data['payment_method_title'] = '';
							$payment_data['stripe_txn_fee'] = 0;
							$payment_data['paypal_txn_fee'] = 0;
						}
					}					
				}
			}
			
			//$MSQS_QL->_p($payment_data);
			if(empty($payment_data)){
				if($manual){
					$MSQS_QL->save_log('Export Payment Error #'.$payment_id,'Woocommerce payment info not found!','Payment',0);
				}else{				
					$MSQS_QL->save_log('Export Payment Error for Order #'.$ord_id_num,'Woocommerce payment info not found!','Payment',0);					
				}
				return false;
			}
			
			if(!$MSQS_QL->check_payment_get_obj($payment_data)){
				$wc_cus_id = (int) $MSQS_QL->get_array_isset($payment_data,'customer_user',0);
				$customer_data['wc_inv_id'] = $order_id;
				if($wc_cus_id){
					$customer_data = $this->mw_qbo_sync_customer_meta($wc_cus_id,$manual,$order_id);					
				}else{					
					$customer_data = $this->get_customer_meta_from_order_meta($order_id,$manual);
				}
				
				//$MSQS_QL->_p($payment_data);$MSQS_QL->_p($customer_data);die;
				if(isset($payment_data['mw_qbo_yith_full_gift_paid']) && $payment_data['mw_qbo_yith_full_gift_paid']){
					$mw_qbo_payment_id = $MSQS_QL->PushOsPayment($p_order_data,$customer_data);
				}else{
					$mw_qbo_payment_id = $MSQS_QL->AddPayment($payment_data,$customer_data,$p_order_data);
				}				
				
				/**/
				if($mw_qbo_payment_id){
					if(isset($payment_data['mw_qbo_yith_partial_gift_paid']) && $payment_data['mw_qbo_yith_partial_gift_paid']){
						$payment_data['mw_qbo_yith_partial_gift_payment_add'] = true;
						$p_order_data['mw_qbo_yith_partial_gift_payment_add'] = true;
						//$mw_qbo_payment_id_yith = $MSQS_QL->AddPayment($payment_data,$customer_data,$p_order_data);
						$mw_qbo_payment_id_yith = $MSQS_QL->PushOsPayment($p_order_data,$customer_data);
					}
				}				
				
				return $mw_qbo_payment_id;
			}else{
				if($manual){
					$MSQS_QL->save_log('Export Payment Error #'.$payment_data['payment_id'],'Payment already exists!','Payment',0);
				}
			}
		}else{			
			$MSQS_QL->save_log('Export Payment Error for Order #'.$ord_id_num,'Woocommerce Payment TXN ID Not Found','Payment',0);
		}
	}
	
	//
	public function myworks_wc_qbo_sync_vendor_push($vend_sync_info=''){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		if(!$MSQS_QL->is_wq_vendor_pm_enable()){
			return false;
		}
		
		if(!is_array($vend_sync_info)){
			return false;
		}
		
		$user_id = (int) $vend_sync_info['user_id'];
		$manual = true;
		
		if($user_id){
			$user_info = get_userdata($user_id);			
			$is_sync_user_role = false;
			if(isset($user_info->roles) && is_array($user_info->roles)){
				$sv_roles = $MSQS_QL->get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur');
				if(!empty($sv_roles)){
					$sv_roles = explode(',',$sv_roles);
					if(is_array($sv_roles) && count($sv_roles)){
						foreach($sv_roles as $sr){
							if(in_array($sr,$user_info->roles)){
								$is_sync_user_role = true;
								break;
							}
						}
					}
				}				
			}
			
			if($is_sync_user_role){
				$vendor_data = $this->mw_qbo_sync_customer_meta($user_id,$manual);
				if(!$MSQS_QL->if_qbo_vendor_exists($vendor_data)){
					if($vendor_data['firstname']!='' || $manual){
						return $mw_qbo_vend_id = $MSQS_QL->AddVendor($vendor_data);
					}
					
					if($manual){
						$MSQS_QL->save_log('Export Vendor Error #'.$vendor_data['wc_customerid'],'Vendor name can\'t be empty','Vendor',0);
					}
					
				}else{
					if($manual){
						//$MSQS_QL->save_log('Export Vendor Error #'.$vendor_data['wc_customerid'],'Vendor already exists!','Vendor',0);
						return $mw_qbo_vend_id = $MSQS_QL->UpdateVendor($vendor_data);
					}					
				}
			}
		}
	}
	
	/**/
	public function myworks_wc_qbo_sync_user_update($user_id,$old_user_data){
		$user_id = intval($user_id);
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		//$MSQS_QL->save_log('Hook Run','Profile Update Hook Testing...','Customer',2);		
		if($user_id>0){
			//if( current_user_can('edit_user',$user_id) ){}
			$this->myworks_wc_qbo_sync_registration_realtime($user_id);
		}
	}
	
	public function myworks_wc_qbo_sync_registration_realtime($cust_sync_info=''){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		//$MSQS_QL->save_log('Hook Run','Testing...','Customer',2);
		
		//
		if(!is_array($cust_sync_info) && $MSQS_QL->option_checked('mw_wc_qbo_sync_block_new_cus_sync_qb')){
			return false;
		}
		
		if($MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
			return false;
		}
		
		if(!is_array($cust_sync_info) && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Customer')){
				$MSQS_QL->real_time_hooks_add_queue('Customer',$cust_sync_info,'CustomerPush','user_register:myworks_wc_qbo_sync_registration_realtime');
			}
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			//return false;
		}
		if(is_array($cust_sync_info)){
			$user_id = (int) $cust_sync_info['user_id'];
			$manual = true;
		}else{
			$user_id = (int) $cust_sync_info;
			$manual = false;
		}
		
		//Check If Real Time Enabled
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Customer')){
			return false;
		}
		
		$is_wacs_satoc = false;
		if(!$manual && $MSQS_QL->is_plugin_active('woocommerce-aelia-currencyswitcher') && $MSQS_QL->option_checked('mw_wc_qbo_sync_wacs_satoc_cb')){
			$wacs_satoc_skip_c_roles = $MSQS_QL->get_option('mw_wc_qbo_sync_wacs_satoc_skip_c_roles');			
			if(empty($wacs_satoc_skip_c_roles)){
				return false;
			}
			$is_wacs_satoc = true;
		}
		
		/*
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt')){
			return false;
		}
		*/
		
		if($user_id){						
			$user_info = get_userdata($user_id);
			
			//
			if($is_wacs_satoc && isset($user_info->roles) && is_array($user_info->roles)){
				$qb_cs = false;
				$wur = $user_info->roles[0];
				$wacs_satoc_skip_c_roles = explode(',',$wacs_satoc_skip_c_roles);				
				if(!empty($wur) && is_array($wacs_satoc_skip_c_roles) && count($wacs_satoc_skip_c_roles)){
					if(in_array($wur,$wacs_satoc_skip_c_roles)){
						$qb_cs = true;
					}
				}
				
				if(!$qb_cs){
					return false;
				}
			}

			$qbo_customerid = '';
			$is_scjm = false;

			# New
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_all_order_to_customer')){				
				$wc_user_role = '';
				if(isset($user_data->roles) && is_array($user_data->roles)){
					$wc_user_role = $user_data->roles[0];
				}
				
				$io_cs = true;
				if(!empty($wc_user_role)){
					$mw_wc_qbo_sync_aotc_rcm_data = get_option('mw_wc_qbo_sync_aotc_rcm_data');
					if(is_array($mw_wc_qbo_sync_aotc_rcm_data) && !empty($mw_wc_qbo_sync_aotc_rcm_data)){
						if(isset($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role]) && !empty($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role])){
							if($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role] != 'Individual'){
								$io_cs = false;
								$qbo_customerid = (int) $mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role];
							}
						}
					}
				}

				/**/							
				if(!$io_cs){
					$aotc_scjm_data = get_option('mw_wc_qbo_sync_aotc_scjm_data');
					if(is_array($aotc_scjm_data) && !empty($aotc_scjm_data)){
						if(isset($aotc_scjm_data[$wc_user_role]) && !empty($aotc_scjm_data[$wc_user_role])){
							$io_cs = true;
							$is_scjm = true;	
						}
					}
				}
				
				if(!$io_cs){
					return false;
				}
			}
			
			if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_all_order_to_customer') && $MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt')){
				$io_cs = false;
				if(isset($user_info->roles) && is_array($user_info->roles)){
					$sc_roles_as_cus = $MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus');
					if(!empty($sc_roles_as_cus)){
						$sc_roles_as_cus = explode(',',$sc_roles_as_cus);
						if(is_array($sc_roles_as_cus) && count($sc_roles_as_cus)){
							foreach($sc_roles_as_cus as $sr){
								if(in_array($sr,$user_info->roles)){
									$io_cs = true;
									break;
								}
							}
						}
					}
				}
				if(!$io_cs){
					return false;
				}
			}
			
			//
			$is_sync_user_role = false;
			if(isset($user_info->roles) && is_array($user_info->roles)){
				$sc_roles = $MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role');
				if(!empty($sc_roles)){
					$sc_roles = explode(',',$sc_roles);
					if(is_array($sc_roles) && count($sc_roles)){
						foreach($sc_roles as $sr){
							if(in_array($sr,$user_info->roles)){
								$is_sync_user_role = true;
								break;
							}
						}
					}
				}
				if(!$is_sync_user_role){
					if(in_array('customer',$user_info->roles)){
						$is_sync_user_role = true;						
					}
				}
			}
			
			/**/
			$allow_customer_update = true;
			
			if($is_sync_user_role){
				$customer_data = $this->mw_qbo_sync_customer_meta($user_id,$manual);
				if($is_scjm){
					$customer_data['ParentRef'] = $qbo_customerid;
					$qbo_customerid = '';
				}

				if(!$MSQS_QL->if_qbo_customer_exists($customer_data)){
					if($customer_data['firstname']!='' || $manual){
						return $mw_qbo_cust_id = $MSQS_QL->AddCustomer($customer_data);
					}
					if($manual){
						$MSQS_QL->save_log('Export Customer Error #'.$customer_data['wc_customerid'],'Customer name can\'t be empty','Customer',0);
					}
					
				}else{
					if($manual || $allow_customer_update){
						//$MSQS_QL->save_log('Export Customer Error #'.$customer_data['wc_customerid'],'Customer already exists!','Customer',0);
						return $mw_qbo_cust_id = $MSQS_QL->UpdateCustomer($customer_data);
					}					
				}				
			}
		}
	}
	
	//11-04-2017
	public function mw_wc_qbo_sync_woocommerce_order_refunded($order_sync_info=0,$refund_id=0){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		
		if($MSQS_QL->is_plg_lc_p_l()){
			return false;
		}
		
		if(!is_array($order_sync_info) && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			//if($MSQS_QL->check_if_real_time_push_enable_for_item('Refund')){}
			$MSQS_QL->real_time_hooks_add_queue('Refund',$order_sync_info,'RefundPush','woocommerce_order_refunded:mw_wc_qbo_sync_woocommerce_order_refunded');
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if(!(int) $refund_id && !is_array($order_sync_info)){
			global $wpdb;
			$ID = (int) $order_sync_info;
			$rfd_q = $wpdb->prepare("SELECT ID FROM `{$wpdb->posts}` WHERE `post_type` = 'shop_order_refund' AND `post_parent` = %d ORDER BY ID DESC LIMIT 0,1 ",$ID);
			$rf_data = $wpdb->get_row($rfd_q);
			if(is_object($rf_data) && !empty($rf_data)){
				$refund_id = $rf_data->ID;
			}
		}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			return false;
		}
		
		if(is_array($order_sync_info)){
			$order_id = (int) $order_sync_info['order_id'];
			$manual = true;
		}else{
			$order_id = (int) $order_sync_info;
			$manual = false;
		}
		$refund_id = (int) $refund_id;
		
		//Check If Real Time Enabled
		/*
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Refund')){
			return false;
		}
		*/
		
		//Split Order
		if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
			return false;
		}
		
		//$MSQS_QL->_p($order_id);$MSQS_QL->_p($refund_id);
		if($order_id && $refund_id){
			/*
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($order_id)){
				return false;
			}
			*/
			
			$order = get_post($order_id);
			
			//13-06-2017
			$_payment_method = get_post_meta( $order_id, '_payment_method', true );
			$_order_currency = get_post_meta( $order_id, '_order_currency', true );
			if(!$MSQS_QL->if_sync_refund(array('_payment_method'=>$_payment_method,'_order_currency'=>$_order_currency,'manual'=>$manual))){	
				return false;
			}
			
			if(!is_object($order) || empty($order)){
				if($manual){
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$order_id,'Woocommerce order not found!','Refund',0);
				}
				return false;
			}
			if($order->post_type!='shop_order'){
				if($manual){					
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$order_id,'Woocommerce order is not valid.','Refund',0);
				}
				return false;
			}
			$refund_data = $MSQS_QL->get_wc_order_details_from_order($order_id,$order);
			if(!is_array($refund_data) || empty($refund_data)){
				if($manual){					
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$order_id,'Woocommerce order details not found.','Refund',0);
				}
				return false;
			}
			
			//07-06-2017
			$wc_inv_num = $MSQS_QL->get_array_isset($refund_data,'wc_inv_num','');
			$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$order_id;
			
			$refund_post = get_post($refund_id);
			if(empty($refund_post)){
				if($manual){
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'Woocommerce refund not found!','Refund',0);
				}
				return false;
			}
			
			$refund_meta = get_post_meta($refund_id);
			
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($order_id)){
				$qbo_salesreceipt_id = (int) $MSQS_QL->get_qbo_salesreceipt_id($order_id,$wc_inv_num);
				//$qbo_salesreceipt_id = 123;
				if(!$qbo_salesreceipt_id){
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'QuickBooks salesreceipt not found!','Refund',0);
					return false;
				}
			}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($order_id)){
				//return false;
				/**/
				$qbo_estimate_id = (int) $MSQS_QL->get_qbo_estimate_id($order_id,$wc_inv_num);
				
				if(!$qbo_estimate_id){
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'QuickBooks estimate not found!','Refund',0);
					return false;
				}
				
			}
			else{
				$qbo_invoice_id = (int) $MSQS_QL->get_qbo_invoice_id($order_id,$wc_inv_num);
		
				if(!$qbo_invoice_id){
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'QuickBooks invoice not found!','Refund',0);
					return false;
				}
			}
			
			/**/
			$order_refund_details = array();
			if(is_array($refund_meta) && !empty($refund_meta)){
				foreach($refund_meta as $rm_k => $rm_v){
					$order_refund_details[$rm_k] = $rm_v[0];
				}
			}
			
			$refund_data['order_refund_details'] = $order_refund_details;

			$refund_data['refund_id'] = $refund_id;
			
			$refund_data['refund_date'] = $refund_post->post_date;
			$refund_data['refund_post_parent'] = $refund_post->post_parent;
			$refund_data['refund_note'] = $refund_post->post_excerpt;
			
			$_refund_amount = isset($refund_meta['_refund_amount'][0])?$refund_meta['_refund_amount'][0]:0;
			if($_refund_amount<= 0){
				//return false;
			}
			$refund_data['_refund_amount'] = $_refund_amount;
			
			$wc_cus_id = (int) $refund_data['wc_cus_id'];
			$qbo_customerid = 0;
			
			/*Custom Post Customer Support*/
			if($MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){				
				$wc_custom_cus_id = (int) get_post_meta( $order_id, 'assigned_customer_id', true );
				if($wc_custom_cus_id > 0){
					$qbo_customerid = (int) $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_customer_pairs','qbo_customerid','wc_customerid',$wc_custom_cus_id);
				}
				
				if(!$qbo_customerid){
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'Quickbooks customer not found!','Refund',0);
					return false;
				}
			}

			$is_scjm = false;
			
			if(!$MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt') || $MSQS_QL->option_checked('mw_wc_qbo_sync_all_order_to_customer')){
					if($MSQS_QL->option_checked('mw_wc_qbo_sync_all_order_to_customer')){
						if($wc_cus_id>0){
							$user_info = get_userdata($wc_cus_id);
							if(isset($user_info->roles) && is_array($user_info->roles)){
								$wc_user_role = $user_info->roles[0];
							}
						}else{
							$wc_user_role = 'wc_guest_user';
						}

						if(!empty($wc_user_role)){
							$io_cs = true;
							$mw_wc_qbo_sync_aotc_rcm_data = get_option('mw_wc_qbo_sync_aotc_rcm_data');
							if(is_array($mw_wc_qbo_sync_aotc_rcm_data) && !empty($mw_wc_qbo_sync_aotc_rcm_data)){
								if(isset($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role]) && !empty($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role])){
									if($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role] != 'Individual'){
										$io_cs = false;
										$qbo_customerid = (int) $mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role];
									}
								}
							}

							/**/							
							if(!$io_cs){
								$aotc_scjm_data = get_option('mw_wc_qbo_sync_aotc_scjm_data');
								if(is_array($aotc_scjm_data) && !empty($aotc_scjm_data)){
									if(isset($aotc_scjm_data[$wc_user_role]) && !empty($aotc_scjm_data[$wc_user_role])){
										$io_cs = true;
										$is_scjm = true;	
									}
								}
							}

							if($io_cs){
								$customer_data = $this->mw_qbo_sync_customer_meta($wc_cus_id,$manual,$order_id);				
								$get_cd_from_om = true;
								if(is_array($customer_data) && isset($customer_data['firstname']) && $customer_data['firstname']!=''){
									$get_cd_from_om = false;
								}
								
								if($get_cd_from_om){
									$customer_data = $this->get_customer_meta_from_order_meta($order_id,$manual);
								}

								if($is_scjm){
									$customer_data['ParentRef'] = $qbo_customerid;
									$qbo_customerid = '';
								}
								
								if(!$wc_cus_id){
									$customer_data['wc_inv_id'] = (int) $order_id;
									$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_guest_id($customer_data);
								}else{
									$customer_data['wc_inv_id'] = (int) $order_id;
									$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_customer_id($customer_data);
								}

							}
						}
					}else{
						if($wc_cus_id){
							$user_info = get_userdata($wc_cus_id);
							$io_cs = false;
							if(isset($user_info->roles) && is_array($user_info->roles)){
								$sc_roles_as_cus = $MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus');
								if(!empty($sc_roles_as_cus)){
									$sc_roles_as_cus = explode(',',$sc_roles_as_cus);
									if(is_array($sc_roles_as_cus) && count($sc_roles_as_cus)){
										foreach($sc_roles_as_cus as $sr){
											if(in_array($sr,$user_info->roles)){
												$io_cs = true;
												break;
											}
										}
									}
								}
							}
							
							if($io_cs){
								$customer_data = $this->mw_qbo_sync_customer_meta($wc_cus_id,$manual,$order_id);
								$get_cd_from_om = true;
								if(is_array($customer_data) && isset($customer_data['firstname']) && $customer_data['firstname']!=''){
									$get_cd_from_om = false;
								}
								
								if($get_cd_from_om){							
									$customer_data = $this->get_customer_meta_from_order_meta($order_id,$manual);
								}
								
								$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_customer_id($customer_data);
							}else{
								$qbo_customerid = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_orders_to_specific_cust');
							}
							
						}else{
							$qbo_customerid = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_orders_to_specific_cust');
						}
					}					
				}else{
					$customer_data = $this->mw_qbo_sync_customer_meta($wc_cus_id,$manual,$order_id);				
					$get_cd_from_om = true;
					if(is_array($customer_data) && isset($customer_data['firstname']) && $customer_data['firstname']!=''){
						$get_cd_from_om = false;
					}
					
					if($get_cd_from_om){
						$customer_data = $this->get_customer_meta_from_order_meta($order_id,$manual);
					}
					
					if(!$wc_cus_id){
						$customer_data['wc_inv_id'] = (int) $order_id;
						$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_guest_id($customer_data);
					}else{
						$customer_data['wc_inv_id'] = (int) $order_id;
						$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_customer_id($customer_data);
					}
				}
			}
			
			if(!$qbo_customerid){
				$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'Quickbooks customer not found!','Refund',0);
				return false;
			}
			$refund_data['qbo_customerid'] = $qbo_customerid;
			
			//
			if(is_array($refund_data['qbo_inv_items'])){
				$refund_data['qbo_inv_items'] = array_filter($refund_data['qbo_inv_items']);
			}
			
			/**/
			$is_refund_has_fees = false;
			if(isset($refund_data['dc_gt_fees']) && !empty($refund_data['dc_gt_fees'])){
				$is_refund_has_fees = true;
			}
			
			if(empty($refund_data['qbo_inv_items']) && !$is_refund_has_fees){
				$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'Order QBO mapped items not found!','Refund',0);
				return false;
			}
			
			$refund_data['manual'] = $manual;
			//$MSQS_QL->_p($refund_data);return false;
			
			if(!$MSQS_QL->if_refund_exists($refund_data)){
				return $mw_qbo_inv_id = $MSQS_QL->AddRefund($refund_data);
			}else{
				if($manual){
					$MSQS_QL->save_log('Export Refund Error #'.$refund_id.' Order #'.$ord_id_num,'Refund already exists in QuickBooks.','Refund',0);		
				}
				return false;
			}
		}
	}
	
	//25-04-2017
	public function myworks_wc_qbo_sync_product_category_realtime($category_sync_info='',$tt_id=0){	
		//die("I'm here!");
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		
		if(!is_array($category_sync_info) && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Category')){
				$MSQS_QL->real_time_hooks_add_queue('Category',$category_sync_info,'CategoryPush','create_product_cat:myworks_wc_qbo_sync_product_category_realtime');
			}
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			return false;
		}
		//27-04-2017
		if(!$MSQS_QL->get_qbo_company_info('is_category_enabled')){
			return false;
		}
		
		if(is_array($category_sync_info)){
			$category_id = (int) $category_sync_info['category_id'];
			$manual = true;
		}else{
			$category_id = (int) $category_sync_info;
			$manual = false;
		}
		
		//Check If Real Time Enabled
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Category')){
			return false;
		}
		
		if($category_id){
			$cat_data = (array) get_term($category_id,'product_cat');
			//$MSQS_QL->_p($cat_data);
			if(is_array($cat_data) && count($cat_data)){
				$cat_data['manual'] = $manual;
				if(!$MSQS_QL->check_category_exists($cat_data)){
					return $mw_qbo_category_id = $MSQS_QL->AddCategory($cat_data);
				}else{
					if($manual){						
						//$MSQS_QL->save_log('Export Category Error #'.$category_id,'Category already exists!','Category',0);
						return $mw_qbo_category_id = $MSQS_QL->UpdateCategory($cat_data);
					}
				}
			}
		}
	}
	
	public function myworks_wc_qbo_order_cancelled($order_id){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		
		if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_invoice_cancelled')){
			return false;
		}
		
		/**/
		$refund_id = $MSQS_QL->get_woo_refund_id_from_order_id($order_id);
		if($refund_id){
			return false;
		}
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Invoice')){
				$MSQS_QL->real_time_hooks_add_queue('Invoice',$order_id,'OrderVoid','woocommerce_order_status_cancelled:myworks_wc_qbo_order_cancelled');
			}
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			return false;
		}
		$manual = false;
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Invoice')){
			return false;
		}
		
		if($order_id){
			$order = get_post($order_id);
			if(is_object($order) && !empty($order)){
				if($order->post_status=='wc-cancelled'){
					$invoice_data = $MSQS_QL->get_wc_order_details_from_order($order_id,$order);
					if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($order_id)){
						$MSQS_QL->VoidSalesReceipt(0,$invoice_data,true);
					}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($order_id)){
						$MSQS_QL->VoidEstimate(0,$invoice_data,true);
					}
					else{
						$MSQS_QL->VoidInvoice(0,$invoice_data,true);
					}
				}
			}			
		}
	}
	
	//Renewal Order
	public function myworks_wc_qbo_sync_comt_hook_wsrpc($subscription,$last_order){
		global $MSQS_QL;
		/*		
		$MSQS_QL->save_log('Renewal Order Hook Run',print_r($subscription,true).print_r($last_order,true),'Test',2);
		*/
		
		if(is_object($subscription) && !empty($subscription) && is_object($last_order) && !empty($last_order)){
			$renewal_order_id = (int) $last_order->get_id();
			if($renewal_order_id>0){
				$this->myworks_wc_qbo_sync_order_realtime($renewal_order_id);
				/**/
				$order_id = $renewal_order_id;
				$order = get_post($order_id);
				$invoice_data = $MSQS_QL->get_wc_order_details_from_order($order_id,$order);
				$is_os_p_sync = $MSQS_QL->if_sync_os_payment($invoice_data);
				if(!$is_os_p_sync){
					$this->mw_qbo_wc_order_payment($order_id);
				}
			}
		}
	}
	
	//Not in Use
	public function myworks_wc_qbo_sync_wc_subs_comt_rnw_ord_push($renewal_total=0,$renewal_order=null){
		return false;
		/*
		global $MSQS_QL;
		$MSQS_QL->save_log('Renewal Order Hook Run',print_r($renewal_total,true).print_r($renewal_order,true),'Test',2);
		*/
		
		//$renewal_total>0 && 
		if(is_object($renewal_order) && !empty($renewal_order)){
			$renewal_order_id = (int) $renewal_order->get_id();
			if($renewal_order_id>0){
				//$this->myworks_wc_qbo_sync_order_realtime($renewal_order_id);
			}
		}
	}
	
	public function myworks_wc_qbo_sync_admin_order_push($order_id,$order){
		$order_id = (int) $order_id;		
		if($order_id>0 && is_object($order) && !empty($order)){
			if(isset($post_after->post_status) && $post_after->post_status != 'draft'){
				$this->myworks_wc_qbo_sync_order_realtime(array('order_id'=>$order_id,'admin_side'=>true));
			}
		}
	}
	
	public function myworks_wc_qbo_sync_pu_product_stock_update($post_ID, $post_after, $post_before){		
		$post_ID = (int) $post_ID;
		global $MSQS_QL;
		global $wpdb;
		if($post_ID>0 && is_object($post_after) && !empty($post_after)){
			if(isset($post_after->post_type) && $post_after->post_type == 'product'){
				$product_id = $post_ID;
				
				//$mwqb_sync_uiiqb = (int) get_post_meta($product_id, 'mwqb_sync_uiiqb', true);				
				$mwqb_sync_uiiqb = isset($_POST['mwqb_sync_uiiqb']) ?1:0;
				//update_post_meta($product_id, 'mwqb_sync_uiiqb', $mwqb_sync_uiiqb);
				
				#lpa
				if(is_object($post_before) && !empty($post_before) && $mwqb_sync_uiiqb == 1 && !$MSQS_QL->is_plg_lc_p_l(false)){
					if(isset($_POST['_original_stock']) && isset($_POST['_stock']) && isset($_POST['_manage_stock'])){
						if($_POST['_manage_stock'] == 'yes' && $_POST['_stock'] != $_POST['_original_stock']){
							
							$map_data = $MSQS_QL->get_row($wpdb->prepare("SELECT `quickbook_product_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `wc_product_id` = %d AND `quickbook_product_id` > 0 ",$product_id));
							$quickbook_product_id = 0;
							if(is_array($map_data) && count($map_data)){
								$quickbook_product_id = (int) $map_data['quickbook_product_id'];
							}
							
							if($quickbook_product_id > 0){
								if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
									if($MSQS_QL->check_if_real_time_push_enable_for_item('Inventory')){
										update_option('mw_wc_qbo_sync_force_queue_run','true');
										$MSQS_QL->real_time_hooks_add_queue('Inventory',$product_id,'InventoryPush','post_updated:myworks_wc_qbo_sync_pu_product_stock_update');
									}
								}else{
									$this->myworks_wc_qbo_sync_update_stock($product_id);
								}
							}							
						}
					}
				}
			}
		}
	}
	
	public function myworks_wc_qbo_sync_pu_variation_stock_update($post_ID, $post_after, $post_before){		
		$post_ID = (int) $post_ID;
		global $MSQS_QL;
		global $wpdb;
		//$MSQS_QL->_p($post_ID);$MSQS_QL->_p($post_after);$MSQS_QL->_p($post_before);$MSQS_QL->_p($_POST);
		if($post_ID>0 && is_object($post_after) && !empty($post_after) && isset($_POST['post_ID']) && (int) $_POST['post_ID'] > 0){
			if(isset($post_after->post_type) && $post_after->post_type == 'product' && isset($_POST['product-type']) && $_POST['product-type'] == 'variable'){
				$product_id = (int) $_POST['post_ID'];
				
				$mwqb_sync_uiiqb = isset($_POST['mwqb_sync_uiiqb']) ?1:0;
				//update_post_meta($product_id, 'mwqb_sync_uiiqb', $mwqb_sync_uiiqb);
				//$mwqb_sync_uiiqb = (int) get_post_meta($product_id,'mwqb_sync_uiiqb',true);
				
				#lpa
				if(is_object($post_before) && !empty($post_before) && $mwqb_sync_uiiqb == 1 && !$MSQS_QL->is_plg_lc_p_l(false)){
					if(isset($_POST['variable_original_stock']) && isset($_POST['variable_stock']) && isset($_POST['variable_manage_stock'])){
						if(is_array($_POST['variable_manage_stock']) && in_array('on',$_POST['variable_manage_stock'])){
							if(is_array($_POST['variable_stock']) && is_array($_POST['variable_original_stock'])){								
								$vs_diff = array_diff($_POST['variable_stock'],$_POST['variable_original_stock']);
								if(is_array($vs_diff) && count($vs_diff)){
									if(is_array($_POST['variable_post_id'])){
										$vp_ids = $_POST['variable_post_id'];
										foreach($vs_diff as $k=>$v){
											if(isset($vp_ids[$k])){
												$variation_id = (int) $vp_ids[$k];
												if($variation_id>0){
													$map_data = $MSQS_QL->get_row($wpdb->prepare("SELECT `quickbook_product_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `wc_variation_id` = %d AND `quickbook_product_id` > 0 ",$variation_id));
													$quickbook_product_id = 0;
													if(is_array($map_data) && count($map_data)){
														$quickbook_product_id = (int) $map_data['quickbook_product_id'];
													}
													
													if($quickbook_product_id){
														if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
															if($MSQS_QL->check_if_real_time_push_enable_for_item('Inventory')){
																update_option('mw_wc_qbo_sync_force_queue_run','true');
																$MSQS_QL->real_time_hooks_add_queue('VariationInventory',$variation_id,'VariationInventoryPush','post_updated:myworks_wc_qbo_sync_pu_variation_stock_update');
															}
														}else{
															$this->myworks_wc_qbo_sync_variation_update_stock($variation_id);
														}
													}													
													
												}
											}											
										}
									}									
								}
							}							
						}						
					}
				}
			}
		}
	}
	
	public function myworks_wc_qbo_sync_order_update($post_ID, $post_after, $post_before){
		$post_ID = (int) $post_ID;
		global $MSQS_QL;		
		
		/*
		$MSQS_QL->save_log('Hook Call Test Order Update',print_r($post_ID,true).print_r($post_after,true).print_r($post_before,true).print_r($_POST,true),'Test',2);
		return false;
		*/
		
		if($post_ID>0 && is_object($post_after) && !empty($post_after)){
			if(isset($post_after->post_type) && $post_after->post_type == 'shop_order'){
				$order_id = $post_ID;
				if(is_object($post_before) && !empty($post_before)){
					//if($post_after->post_status == $post_before->post_status){}
					if(isset($_POST['original_post_status']) && isset($_POST['order_status'])){
						$original_post_status = trim($_POST['original_post_status']);
						$order_status = trim($_POST['order_status']);
						
						$allow_spl_status = false;
						/*New Pending Status Problem*/
						$ext_status_save = false;
						
						
						if($order_status == 'wc-pending'){
							if(isset($_POST['post_status'])){
								$post_status = trim($_POST['post_status']);
								if($original_post_status == 'auto-draft' && $post_status == 'draft'){
									$a_os_l = $MSQS_QL->get_option('mw_wc_qbo_sync_specific_order_status');
									if(!empty($a_os_l)){
										$a_os_l = trim($a_os_l);
										if($a_os_l!=''){$a_os_l = explode(',',$a_os_l);}
										if(is_array($a_os_l) && count($a_os_l)){
											if(in_array($order_status,$a_os_l)){
												//$ext_status_save = true;
												//$allow_spl_status = true;
												
												if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
													/**/
													if(!$MSQS_QL->lp_chk_osl_allwd()){
														$MSQS_QL->save_log('Export Order Error','Monthly limit of orders synced has been reached. To manage your plan and view more details, visit MyWorks Sync > Connection.','Invoice',0);
														return false;
													}
													
													if($MSQS_QL->check_if_real_time_push_enable_for_item('Invoice')){
														update_option('mw_wc_qbo_sync_force_queue_run','true');
														$MSQS_QL->real_time_hooks_add_queue('Invoice',$order_id,'OrderPush','post_updated,woocommerce_order_status_pending:myworks_wc_qbo_sync_order_update');
													}
												}else{
													$ext_status_save = true;
												}												
											}
										}
									}
								}
							}
						}
						
						
						if($original_post_status == $order_status || $ext_status_save){
							$this->myworks_wc_qbo_sync_order_realtime(array('order_id'=>$order_id,'admin_side'=>true,'allow_spl_status'=>$allow_spl_status));
						}
					}					
				}				
			}
		}
	}
	
	public function order_push_as_admin_side($order_id){
		$order_id = (int) $order_id;
		if($order_id>0){
			$allow_spl_status = false;
			$this->myworks_wc_qbo_sync_order_realtime(array('order_id'=>$order_id,'admin_side'=>true,'allow_spl_status'=>$allow_spl_status));
		}
	}
	
	//Order Add
	public function myworks_wc_qbo_sync_order_realtime($order_sync_info=''){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		global $wpdb;
		/*
		$MSQS_QL->save_log('Hook Call Test',print_r($order_sync_info,true),'Test',2);
		return false;
		*/
		
		if(!$MSQS_QL->lp_chk_osl_allwd()){
			$MSQS_QL->save_log('Export Order Error','Monthly limit of orders synced has been reached. To manage your plan and view more details, visit MyWorks Sync > Connection.','Invoice',0);
			return false;
		}
		
		$or_queue_added = false;
		$odp_qb = false;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Invoice')){
				if(!is_array($order_sync_info)){
					$or_queue_added = true;
					$MSQS_QL->real_time_hooks_add_queue('Invoice',$order_sync_info,'OrderPush','woocommerce_thankyou,save_post_shop_order:myworks_wc_qbo_sync_order_realtime');
				}else{
					if(isset($order_sync_info['admin_side'])){
						$or_queue_added = true;
						$order_id = (int) $order_sync_info['order_id'];
						$MSQS_QL->real_time_hooks_add_queue('Invoice',$order_id,'OrderPush','post_updated:myworks_wc_qbo_sync_order_realtime');
					}
					
					//
					if(isset($order_sync_info['odp_qb']) && $order_sync_info['odp_qb'] == 1){
						$odp_qb = true;
					}
				}
			}			
		}
		
		/**/
		$o_id = (is_array($order_sync_info))?(int) $order_sync_info['order_id']:(int) $order_sync_info;		
		if($or_queue_added && !$MSQS_QL->is_order_sync_as_sales_receipt($o_id) && !$MSQS_QL->is_order_sync_as_estimate($o_id)){
			$_transaction_id = get_post_meta($o_id,'_transaction_id',true);
			$a_bti = true;
			if(!empty($_transaction_id) || $a_bti){
				$pm_r = $MSQS_QL->get_row($wpdb->prepare("SELECT `meta_id` FROM `{$wpdb->postmeta}` WHERE `post_id` = %d AND `meta_key` = '_transaction_id' ",$o_id));
				if(is_array($pm_r) && !empty($pm_r)){
					$payment_id = (int) $pm_r['meta_id'];
					if($payment_id > 0){
						$MSQS_QL->real_time_hooks_add_queue('Payment',$o_id,'PaymentPush','nh_manually_after_order:mw_qbo_wc_order_payment');
					}
				}
			}			
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {
			return false;
		}
		
		$manual = false;
		
		$ao_rt = false;
		$qu_s = false;
		
		$catch_all_q = false;
		
		$allow_spl_status = false;
		if(is_array($order_sync_info)){
			$order_id = (int) $order_sync_info['order_id'];
			if(!isset($order_sync_info['admin_side']) && !isset($order_sync_info['queue_side'])){
				$manual = true;
			}else{
				if(!isset($order_sync_info['queue_side'])){
					$ao_rt = true;
					$allow_spl_status = $order_sync_info['allow_spl_status'];
				}else{
					$qu_s = true;
				}				
			}
			
			if(isset($order_sync_info['catch_all_q'])){
				$catch_all_q = $order_sync_info['catch_all_q'];
			}
		}else{
			$order_id = (int) $order_sync_info;
			$manual = false;
		}
		
		if(is_admin()){
			$ao_rt = true;
		}
		
		/*
		if(!$manual){
			$MSQS_QL->save_log('Hook Call','Order Sync Hook #'.$order_id,'Test',2);
			return false;
		}
		*/
		
		//Check If Real Time Enabled
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Invoice')){
			return false;
		}
		
		if(!$MSQS_QL->ord_pmnt_is_mt_ls_check_by_ord_id($order_id)){
			return false;
		}
				
		if($order_id){
			$order = get_post($order_id);			
			if(!is_object($order) || empty($order)){
				if($manual){
					$MSQS_QL->save_log('Export Order Error #'.$order_id,'Woocommerce order not found!','Invoice',0);
				}
				return false;
			}
			if($order->post_type!='shop_order'){
				if($manual){
					$MSQS_QL->save_log('Export Order Error #'.$order_id,'Woocommerce order is not valid.','Invoice',0);
				}
				return false;
			}
			
			//25-04-2017
			if($order->post_status=='auto-draft' || $order->post_status=='trash'){				
				return false;
			}
			
			if(!$manual && $order->post_status=='draft'){				
				if(is_admin()){					
					//do_action('mw_wc_qbo_sync_add_order_status_draft_qbo', $order_id, $order);
				}
				if(!$allow_spl_status){
					return false;
				}				
			}
			
			$is_os_err = false; $is_os_p_sync = false;
			//24-04-2017
			$only_sync_status = $MSQS_QL->get_option('mw_wc_qbo_sync_specific_order_status');
			if($only_sync_status!=''){$only_sync_status = explode(',',$only_sync_status);}
			
			if(!$manual && (!is_array($only_sync_status) || (is_array($only_sync_status) && !in_array($order->post_status,$only_sync_status)))){
				//return false;
				if(!$allow_spl_status){
					//$is_os_err = true;
					return false;
				}				
			}			
			
			//if(is_admin()){$manual = true;}
			
			$invoice_data = $MSQS_QL->get_wc_order_details_from_order($order_id,$order);
			
			/**/
			$caq_not_exists = false;
			if($catch_all_q){
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($order_id)){
					if($MSQS_QL->check_quickbooks_salesreceipt_get_obj($invoice_data['wc_inv_id'],$invoice_data['wc_inv_num'])){
						return false;
					}
				}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($order_id)){
					if($MSQS_QL->check_quickbooks_estimate_get_obj($invoice_data['wc_inv_id'],$invoice_data['wc_inv_num'])){
						return false;
					}
				}
				else{
					if($MSQS_QL->check_quickbooks_invoice_get_obj($invoice_data['wc_inv_id'],$invoice_data['wc_inv_num'])){
						return false;
					}
				}
				$caq_not_exists = true;
			}
			
			$wc_inv_num = $MSQS_QL->get_array_isset($invoice_data,'wc_inv_num','');
			
			/*			
			if(empty($wc_inv_num) && !is_array($order_sync_info) && !$MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
				if($MSQS_QL->is_plugin_active('woocommerce-sequential-order-numbers-pro','woocommerce-sequential-order-numbers') && $MSQS_QL->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
					update_option('mw_wc_qbo_sync_force_queue_run','true');
					$MSQS_QL->real_time_hooks_add_queue('Invoice',$order_sync_info,'OrderPush','woocommerce_thankyou,save_post_shop_order:myworks_wc_qbo_sync_order_realtime');					
					return false;
				}
			}
			*/
			
			$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$order_id;
			
			$is_os_p_sync = $MSQS_QL->if_sync_os_payment($invoice_data);
			
			//04-05-2017 : 14-06-2017
			if(!is_array($invoice_data) || !isset($invoice_data['_order_key']) || !isset($invoice_data['_order_total'])){
				if($manual && !$is_os_err){
					$MSQS_QL->save_log('Export Order Error #'.$ord_id_num,'Woocommerce order details not found.','Invoice',0);
				}
				if($is_os_p_sync){
					$MSQS_QL->save_log('Export Payment Error Order #'.$ord_id_num,'Woocommerce order details not found.','Payment',0);
				}
				return false;
			}
			
			//19-05-2017 //Prevent Multiple Realtime call
			if(!$manual && !is_admin()){
				if((int) $MSQS_QL->get_session_val('current_rt_order_id_'.$order_id,0)==$order_id){
					return false;
				}
				$MSQS_QL->set_session_val('current_rt_order_id_'.$order_id,$order_id);
			}
			//$MSQS_QL->save_log('Hook Call','Order Sync Hook #'.$ord_id_num,'Test',2);
			
			$wc_cus_id = (int) $invoice_data['wc_cus_id'];
			
			$qbo_customerid = 0;
			//
			$skip_billing_address = false;
			$is_new_cus_sync_a = false;
			
			/**/
			if($MSQS_QL->is_plugin_active('woocommerce-aelia-currencyswitcher') && $MSQS_QL->option_checked('mw_wc_qbo_sync_wacs_satoc_cb')){
				// && empty($MSQS_QL->get_option('mw_wc_qbo_sync_wacs_satoc_skip_c_roles'))
				
				if(!empty($MSQS_QL->get_option('mw_wc_qbo_sync_wacs_satoc_skip_c_roles'))){
					if($wc_cus_id){
						$user_info = get_userdata($wc_cus_id);
						$io_cs = false;
						if(isset($user_info->roles) && is_array($user_info->roles)){
							$wacs_satoc_skip_c_roles = $MSQS_QL->get_option('mw_wc_qbo_sync_wacs_satoc_skip_c_roles');
							if(!empty($wacs_satoc_skip_c_roles)){
								$wacs_satoc_skip_c_roles = explode(',',$wacs_satoc_skip_c_roles);
								if(is_array($wacs_satoc_skip_c_roles) && count($wacs_satoc_skip_c_roles)){
									foreach($wacs_satoc_skip_c_roles as $sr){
										if(in_array($sr,$user_info->roles)){
											$io_cs = true;
											break;
										}
									}
								}
							}
						}
					}
				}
				
				if(!$io_cs){
					$_order_currency = $MSQS_QL->get_array_isset($invoice_data,'_order_currency','',true);
					if($_order_currency!=''){
						$aelia_cur_cus_map = get_option('mw_wc_qbo_sync_wacs_satoc_map_cur_cus');
						if(is_array($aelia_cur_cus_map) && count($aelia_cur_cus_map)){
							if(isset($aelia_cur_cus_map[$_order_currency]) && trim($aelia_cur_cus_map[$_order_currency])!=''){
								$qbo_customerid = trim($aelia_cur_cus_map[$_order_currency]);
							}
						}
					}
				}				
			}
			
			/*Custom Post Customer Support*/
			if($MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
				$wc_custom_cus_id = (isset($invoice_data['assigned_customer_id']))?(int) $invoice_data['assigned_customer_id']:0;
				if($wc_custom_cus_id > 0){
					$qbo_customerid = (int) $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_customer_pairs','qbo_customerid','wc_customerid',$wc_custom_cus_id);
				}
				
				if(!$qbo_customerid){
					if(!$is_os_err){					
						$MSQS_QL->save_log('Export Order Error #'.$ord_id_num,'Quickbooks customer not found!','Invoice',0);
					}else{
						if($is_os_p_sync){
							$MSQS_QL->save_log('Export Payment Error Order #'.$ord_id_num,'Quickbooks customer not found!','Payment',0);
						}					
					}			
					return false;
				}
			}

			$is_scjm = false;
			
			if(!$MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt') || $MSQS_QL->option_checked('mw_wc_qbo_sync_all_order_to_customer')){
					if($MSQS_QL->option_checked('mw_wc_qbo_sync_all_order_to_customer')){
						if($wc_cus_id>0){
							$user_info = get_userdata($wc_cus_id);
							if(isset($user_info->roles) && is_array($user_info->roles)){
								$wc_user_role = $user_info->roles[0];
							}
						}else{
							$wc_user_role = 'wc_guest_user';
						}

						if(!empty($wc_user_role)){
							$io_cs = true;
							$mw_wc_qbo_sync_aotc_rcm_data = get_option('mw_wc_qbo_sync_aotc_rcm_data');
							if(is_array($mw_wc_qbo_sync_aotc_rcm_data) && !empty($mw_wc_qbo_sync_aotc_rcm_data)){
								if(isset($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role]) && !empty($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role])){
									if($mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role] != 'Individual'){
										$io_cs = false;
										$qbo_customerid = (int) $mw_wc_qbo_sync_aotc_rcm_data[$wc_user_role];
									}
								}
							}
							

							/**/							
							if(!$io_cs){
								$aotc_scjm_data = get_option('mw_wc_qbo_sync_aotc_scjm_data');
								if(is_array($aotc_scjm_data) && !empty($aotc_scjm_data)){
									if(isset($aotc_scjm_data[$wc_user_role]) && !empty($aotc_scjm_data[$wc_user_role])){
										$io_cs = true;
										$is_scjm = true;	
									}
								}
							}
							
							if($io_cs){
								$customer_data = $this->mw_qbo_sync_customer_meta($wc_cus_id,$manual,$order_id);				
								$get_cd_from_om = true;
								if(is_array($customer_data) && isset($customer_data['firstname']) && $customer_data['firstname']!=''){
									$get_cd_from_om = false;
								}
								
								if($get_cd_from_om){
									$customer_data = $this->get_customer_meta_from_order_meta($order_id,$manual);
								}

								if($is_scjm){
									$customer_data['ParentRef'] = $qbo_customerid;
									$qbo_customerid = '';
								}
								
								if(!$wc_cus_id){
									$customer_data['wc_inv_id'] = (int) $order_id;
									$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_guest_id($customer_data);
								}else{
									$customer_data['wc_inv_id'] = (int) $order_id;
									$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_customer_id($customer_data);
								}

							}
						}
					}else{
						if($wc_cus_id){
							$user_info = get_userdata($wc_cus_id);
							$io_cs = false;
							if(isset($user_info->roles) && is_array($user_info->roles)){
								$sc_roles_as_cus = $MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus');
								if(!empty($sc_roles_as_cus)){
									$sc_roles_as_cus = explode(',',$sc_roles_as_cus);
									if(is_array($sc_roles_as_cus) && count($sc_roles_as_cus)){
										foreach($sc_roles_as_cus as $sr){
											if(in_array($sr,$user_info->roles)){
												$io_cs = true;
												break;
											}
										}
									}
								}
							}
							
							if($io_cs){
								$customer_data = $this->mw_qbo_sync_customer_meta($wc_cus_id,$manual,$order_id);
								$get_cd_from_om = true;
								if(is_array($customer_data) && isset($customer_data['firstname']) && $customer_data['firstname']!=''){
									$get_cd_from_om = false;
								}
								
								if($get_cd_from_om){							
									$customer_data = $this->get_customer_meta_from_order_meta($order_id,$manual);
								}
								
								$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_customer_id($customer_data);
							}else{
								$qbo_customerid = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_orders_to_specific_cust');
							}
							
						}else{
							$qbo_customerid = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_orders_to_specific_cust');
						}
					}					
					
				}else{
					$customer_data = $this->mw_qbo_sync_customer_meta($wc_cus_id,$manual,$order_id);
					//$MSQS_QL->_p($customer_data);die;
					$get_cd_from_om = true;
					if(is_array($customer_data) && isset($customer_data['firstname']) && $customer_data['firstname']!=''){
						$get_cd_from_om = false;
					}
					
					if($get_cd_from_om){							
						$customer_data = $this->get_customer_meta_from_order_meta($order_id,$manual);
					}
					//$MSQS_QL->_p($customer_data);die;
					if(!$wc_cus_id){					
						$customer_data['wc_inv_id'] = (int) $order_id;
						$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_guest_id($customer_data);
					}else{
						$customer_data['wc_inv_id'] = (int) $order_id;
						$qbo_customerid = (int) $MSQS_QL->check_save_get_qbo_customer_id($customer_data);
					}
					
					$is_new_cus_sync_a = true;
				}
			}
			
			//Customer currency validate for multicurrency
			if($qbo_customerid && $MSQS_QL->get_qbo_company_setting('is_m_currency')){
				$_order_currency = $MSQS_QL->get_array_isset($invoice_data,'_order_currency','',true);
				$qb_currency_cust_id = (int) $MSQS_QL->validate_get_currency_qb_cust_id($qbo_customerid,$_order_currency);
				if($qb_currency_cust_id > 0){
					$qbo_customerid = $qb_currency_cust_id;
				}
			}
			
			//$MSQS_QL->_p($qbo_customerid,true);return false;
			
			if(!$qbo_customerid){		
				if(!$is_os_err){					
					$MSQS_QL->save_log('Export Order Error #'.$ord_id_num,'Quickbooks customer not found!','Invoice',0);
				}else{
					if($is_os_p_sync){
						$MSQS_QL->save_log('Export Payment Error Order #'.$ord_id_num,'Quickbooks customer not found!','Payment',0);
					}					
				}			
				return false;
			}
			$invoice_data['qbo_customerid'] = $qbo_customerid;
			//
			if(!$is_new_cus_sync_a && $MSQS_QL->option_checked('mw_wc_qbo_sync_use_qb_ba_for_eqc')){
				$skip_billing_address = true;
			}
			
			$invoice_data['skip_billing_address'] = $skip_billing_address;
			
			//
			if(is_array($invoice_data['qbo_inv_items'])){
				$invoice_data['qbo_inv_items'] = array_filter($invoice_data['qbo_inv_items']);
			}
			
			/**/
			$is_order_has_fees = false;
			if(isset($invoice_data['dc_gt_fees']) && !empty($invoice_data['dc_gt_fees'])){
				$is_order_has_fees = true;
			}
			
			/**/
			$a_shipping_only_osync = false;
			if(isset($invoice_data['shipping_details']) && !empty($invoice_data['shipping_details'])){
				$qbo_is_shipping_allowed = $MSQS_QL->get_qbo_company_setting('is_shipping_allowed');
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_odr_shipping_as_li')){
					$qbo_is_shipping_allowed = false;
				}
				
				$order_shipping_total = $MSQS_QL->get_array_isset($invoice_data,'order_shipping_total',0);
				if(!$qbo_is_shipping_allowed && $order_shipping_total > 0){
					$a_shipping_only_osync = true;
				}
			}
			
			if(empty($invoice_data['qbo_inv_items']) && !$is_order_has_fees && !$a_shipping_only_osync){
				if(!$is_os_err){
					$MSQS_QL->save_log('Export Order Error #'.$ord_id_num,'Order QBO mapped items not found!','Invoice',0);
				}else{
					if($is_os_p_sync){
						$MSQS_QL->save_log('Export Payment Error Order #'.$ord_id_num,'Order QBO mapped items not found!','Payment',0);
					}					
				}				
				return false;
			}
			
			$invoice_data['manual'] = $manual;
			
			//Split_Order
			if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
				do_action('mw_wc_qbo_sync_split_order_custom_po_action',array(),$invoice_data);
				if($manual){
					$MSQS_QL->set_session_val('spli_order_manual',true);
				}
				return false;
			}
			
			$order_status = $MSQS_QL->get_array_isset($invoice_data,'order_status','');
			
			/*Payment Hook Issue*/
			$rt_ph_ord_id = 0;
			
			if(!$ao_rt && !$manual){
				$rps_oid_arr = (array) get_option('mw_qbo_sync_payment_sync_rt_hook_order_id_list');
				if(in_array($order_id,$rps_oid_arr)){
					$rt_ph_ord_id = $order_id;					
					$key = array_search($order_id, $rps_oid_arr);
					unset($rps_oid_arr[$key]);
					if(count($rps_oid_arr)){
						$rps_oid_arr = array_values($rps_oid_arr);
					}
					update_option('mw_qbo_sync_payment_sync_rt_hook_order_id_list',$rps_oid_arr);
				}
			}			
			
			//$MSQS_QL->_p($invoice_data);return false;
			
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($order_id)){
				$mw_qbo_sr_id = 0;
				if($is_os_err){return false;}
				if(($catch_all_q && $caq_not_exists) || !$MSQS_QL->check_quickbooks_salesreceipt_get_obj($invoice_data['wc_inv_id'],$invoice_data['wc_inv_num'])){					
					$mw_qbo_sr_id = $MSQS_QL->AddSalesReceipt($invoice_data);
					if($mw_qbo_sr_id>0){
						if($order_status=='wc-cancelled'){
							$MSQS_QL->VoidSalesReceipt($mw_qbo_sr_id,$invoice_data);
						}
					}
					//Action - 13-06-2017
					$ext_acd = array('order_sync_as'=>'salesreceipt','qbo_salesreceipt_id'=>$mw_qbo_sr_id);
					do_action('mw_wc_qbo_sync_order_sync_after_action',$ext_acd,$invoice_data);
					return $mw_qbo_sr_id;
				}else{
					if($manual || $ao_rt || $qu_s){ //2nd Condition added on 22-02-2018##21-06-2018
						$mw_qbo_sr_id = $MSQS_QL->UpdateSalesReceipt($invoice_data);
						if($mw_qbo_sr_id>0){
							if($order_status=='wc-cancelled'){
								$MSQS_QL->VoidSalesReceipt($mw_qbo_sr_id,$invoice_data);
							}
						}
						//
						$ext_acd = array('order_sync_as'=>'salesreceipt','qbo_salesreceipt_id'=>$mw_qbo_sr_id);
						do_action('mw_wc_qbo_sync_order_update_sync_after_action',$ext_acd,$invoice_data);
						return $mw_qbo_sr_id;
					}else{
						//27-06-2017
						if($MSQS_QL->is_plugin_active('woocommerce-shipment-tracking') && $MSQS_QL->option_checked('mw_wc_qbo_sync_w_shp_track')){
							if(is_admin() && isset($invoice_data['_wc_shipment_tracking_items'])){
								$mw_qbo_sr_id = $MSQS_QL->UpdateSalesReceipt($invoice_data);
								if($mw_qbo_sr_id>0){
									if($order_status=='wc-cancelled'){
										$MSQS_QL->VoidSalesReceipt($mw_qbo_sr_id,$invoice_data);
									}
								}
								//
								$ext_acd = array('order_sync_as'=>'salesreceipt','qbo_salesreceipt_id'=>$mw_qbo_sr_id);
								do_action('mw_wc_qbo_sync_order_update_sync_after_action',$ext_acd,$invoice_data);
								return $mw_qbo_sr_id;
							}
						}
					}
				}			
				
			}elseif($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($order_id)){
				$mw_qbo_est_id = 0;
				if($is_os_err){return false;}
				if(($catch_all_q && $caq_not_exists) || !$MSQS_QL->check_quickbooks_estimate_get_obj($invoice_data['wc_inv_id'],$invoice_data['wc_inv_num'])){					
					$mw_qbo_est_id = $MSQS_QL->AddEstimate($invoice_data);
					if($mw_qbo_est_id>0){
						# New - Estimate Payment
						if($MSQS_QL->is_sync_estimate_payment($invoice_data)){
							$MSQS_QL->PushEstimatePayment($invoice_data);
						}
						
						if($order_status=='wc-cancelled'){
							$MSQS_QL->VoidEstimate($mw_qbo_est_id,$invoice_data);
						}
					}
					//Action - 13-06-2017
					$ext_acd = array('order_sync_as'=>'estimate','qbo_estimate_id'=>$mw_qbo_est_id);
					do_action('mw_wc_qbo_sync_order_sync_after_action',$ext_acd,$invoice_data);
					return $mw_qbo_est_id;
				}else{
					if($manual || $ao_rt || $qu_s){ //2nd Condition added on 22-02-2018##21-06-2018
						$mw_qbo_est_id = $MSQS_QL->UpdateEstimate($invoice_data);
						if($mw_qbo_est_id>0){
							# New - Estimate Payment
							if($MSQS_QL->is_sync_estimate_payment($invoice_data)){
								$MSQS_QL->PushEstimatePayment($invoice_data);
							}

							if($order_status=='wc-cancelled'){
								$MSQS_QL->VoidEstimate($mw_qbo_est_id,$invoice_data);
							}
						}
						//
						$ext_acd = array('order_sync_as'=>'estimate','qbo_estimate_id'=>$mw_qbo_est_id);
						do_action('mw_wc_qbo_sync_order_update_sync_after_action',$ext_acd,$invoice_data);
						return $mw_qbo_est_id;
					}else{
						//27-06-2017
						if($MSQS_QL->is_plugin_active('woocommerce-shipment-tracking') && $MSQS_QL->option_checked('mw_wc_qbo_sync_w_shp_track')){
							if(is_admin() && isset($invoice_data['_wc_shipment_tracking_items'])){
								$mw_qbo_est_id = $MSQS_QL->UpdateEstimate($invoice_data);
								if($mw_qbo_est_id>0){
									if($order_status=='wc-cancelled'){
										$MSQS_QL->VoidEstimate($mw_qbo_est_id,$invoice_data);
									}
								}
								//
								$ext_acd = array('order_sync_as'=>'estimate','qbo_estimate_id'=>$mw_qbo_est_id);
								do_action('mw_wc_qbo_sync_order_update_sync_after_action',$ext_acd,$invoice_data);
								return $mw_qbo_est_id;
							}
						}
					}
				}			
				
			}
			else{
				if(($catch_all_q && $caq_not_exists) || !$MSQS_QL->check_quickbooks_invoice_get_obj($invoice_data['wc_inv_id'],$invoice_data['wc_inv_num'])){
					if($is_os_err){return false;}
					
					//24-10-2017
					$mw_qbo_inv_id = 0;
					//$MSQS_QL->_p($invoice_data);
					$ds_ori_wc_inv_id = (int) $MSQS_QL->is_wc_deposit_pmnt_order($invoice_data);
					
					if($ds_ori_wc_inv_id){
						//if(!$is_os_p_sync){}
						$ds_ori_order = get_post($ds_ori_wc_inv_id);
						$ds_ori_idata = $MSQS_QL->get_wc_order_details_from_order($ds_ori_wc_inv_id,$ds_ori_order);
						if(is_array($ds_ori_idata) && count($ds_ori_idata)){
							$invoice_data['ds_ori_idata'] = $ds_ori_idata;
							$MSQS_QL->PushOsPayment($invoice_data);
						}
					}else{						
						$mw_qbo_inv_id = $MSQS_QL->AddInvoice($invoice_data);
						
						if($mw_qbo_inv_id>0){
							//Payment Hook Issue
							$psfphia = false;
							if($rt_ph_ord_id>0 && !$MSQS_QL->option_checked('mw_wc_qbo_sync_enable_d_o_q_add_p')){
								#$psfphia = true;
								$this->mw_qbo_wc_order_payment(array('order_id'=>$rt_ph_ord_id));
							}

							#New
							if($MSQS_QL->option_checked('mw_wc_qbo_sync_enable_d_o_q_add_p')){
								$psfphia = true;
							}
							
							#Payment not syncing from order details page issue
							if($odp_qb){
								$psfphia = false;
							}
							
							/**/
							if((!$psfphia && (!$manual || $odp_qb)) && !$MSQS_QL->is_order_sync_as_sales_receipt($order_id)){
								$_transaction_id = get_post_meta($order_id,'_transaction_id',true);
								$a_bti = true;
								if(!empty($_transaction_id) || $a_bti){
									$pm_r = $MSQS_QL->get_row($wpdb->prepare("SELECT `meta_id` FROM `{$wpdb->postmeta}` WHERE `post_id` = %d AND `meta_key` = '_transaction_id' ",$order_id));
									if(is_array($pm_r) && !empty($pm_r)){
										$payment_id = (int) $pm_r['meta_id'];
										if($payment_id > 0 && !$MSQS_QL->check_payment_get_obj(array('payment_id' => $payment_id))){
											#
											$this->mw_qbo_wc_order_payment(array('payment_id'=>$payment_id,'after_order'=>true));
										}
									}
								}								
							}
							
							if($order_status=='wc-cancelled'){
								$MSQS_QL->VoidInvoice($mw_qbo_inv_id,$invoice_data);
							}
						}
					}
					
					//Add Payment Based On Order Status
					if($is_os_p_sync && !$MSQS_QL->is_wc_deposit_pmnt_order($invoice_data)){
						$MSQS_QL->PushOsPayment($invoice_data);
					}
					//Action
					$ext_acd = array('order_sync_as'=>'invoice','qbo_invoice_id'=>$mw_qbo_inv_id);
					do_action('mw_wc_qbo_sync_order_sync_after_action',$ext_acd,$invoice_data);
					return $mw_qbo_inv_id;
				}else{				
					if($manual || $ao_rt || $qu_s){ //2nd Condition added on 22-02-2018##21-06-2018
						if(!$is_os_err){
							$mw_qbo_inv_id = $MSQS_QL->UpdateInvoice($invoice_data);
							if($mw_qbo_inv_id>0){
								if(!$is_os_p_sync){
									$this->mw_qbo_wc_order_payment(['order_id'=>$order_id]);
								}

								if($order_status=='wc-cancelled'){
									$MSQS_QL->VoidInvoice($mw_qbo_inv_id,$invoice_data);
								}
							}
						}						
						if($is_os_p_sync){
							$MSQS_QL->PushOsPayment($invoice_data);
						}
						//
						$ext_acd = array('order_sync_as'=>'invoice','qbo_invoice_id'=>$mw_qbo_inv_id);
						do_action('mw_wc_qbo_sync_order_update_sync_after_action',$ext_acd,$invoice_data);
						if(!$is_os_err){
							return $mw_qbo_inv_id;
						}						
					}else{
						if($is_os_p_sync){
							$MSQS_QL->PushOsPayment($invoice_data);
						}
						//27-06-2017
						if($MSQS_QL->is_plugin_active('woocommerce-shipment-tracking') && $MSQS_QL->option_checked('mw_wc_qbo_sync_w_shp_track')){
							if(is_admin() && isset($invoice_data['_wc_shipment_tracking_items'])){
								$mw_qbo_inv_id = $MSQS_QL->UpdateInvoice($invoice_data);
								if($mw_qbo_inv_id>0){
									if($order_status=='wc-cancelled'){
										$MSQS_QL->VoidInvoice($mw_qbo_inv_id,$invoice_data);
									}
								}
								//
								$ext_acd = array('order_sync_as'=>'invoice','qbo_invoice_id'=>$mw_qbo_inv_id);
								do_action('mw_wc_qbo_sync_order_update_sync_after_action',$ext_acd,$invoice_data);
								return $mw_qbo_inv_id;
							}
						}
					}					
				}				
				
			}
			
		}
	}
	
	/**/
	public function myworks_wc_qbo_sync_after_order_synced_into_qb($ext_acd,$invoice_data){
		global $MSQS_QL;
		if(!$MSQS_QL->is_connected()){return false;}
		$is_update = $MSQS_QL->get_array_isset($invoice_data,'is_update',false);
		if($MSQS_QL->is_plg_lc_p_l()){return false;}
		//PO
		$allow_po_in_order_update = true;
		if(!$is_update || $allow_po_in_order_update){
			$qbo_inv_items = (isset($invoice_data['qbo_inv_items']))?$invoice_data['qbo_inv_items']:array();
			if(empty($qbo_inv_items)){
				return false;
			}
			
			if($MSQS_QL->if_sync_po($invoice_data['wc_inv_id'],$invoice_data['wc_cus_id'],$invoice_data['wc_inv_num'])){
				if(!$MSQS_QL->check_quickbooks_po_get_obj($invoice_data['wc_inv_id'],$invoice_data['wc_inv_num'])){
					$invoice_data['ext_acd_array'] = $ext_acd;
					$mw_qbo_po_id = $MSQS_QL->AddPurchaseOrder($invoice_data);
					return $mw_qbo_po_id;
				}
			}
		}
	}
	
	public function myworks_wc_qbo_sync_after_order_updated_into_qb($ext_acd,$invoice_data){
		$ext_acd['is_update'] = true;
		$this->myworks_wc_qbo_sync_after_order_synced_into_qb($ext_acd,$invoice_data);
	}
	
	//10-05-2017
	public function myworks_wc_qbo_sync_update_stock($instance){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		
		//New
		if(is_object($instance)){
			$product_id = (int) $instance->get_id();
		}else{
			$product_id = (int) $instance;
		}
		
		if($product_id < 1){
			return false;
		}
		
		//$mwqb_sync_uiiqb = (int) get_post_meta($product_id, 'mwqb_sync_uiiqb', true);
		//if(!$mwqb_sync_uiiqb){return false;}
		
		if($product_id>0 && $MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){			
			if($MSQS_QL->check_if_real_time_push_enable_for_item('Inventory')){
				update_option('mw_wc_qbo_sync_force_realtime_sync_q','true');
				update_option('mw_wc_qbo_sync_force_queue_run','true');
				$MSQS_QL->real_time_hooks_add_queue('Inventory',$product_id,'InventoryPush','woocommerce_product_set_stock:myworks_wc_qbo_sync_update_stock');
			}			
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		//$MSQS_QL->_p($instance);
		if(!is_admin()){
			//return false;
		}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {			
			return false;
		}
		$manual = false;
		
		/**/
		if(!$manual && $MSQS_QL->get_session_val('prevent_rt_inventory_push_ot',0,true)){
			//return false;
		}
		
		//Check If Real Time Enabled
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Inventory')){			
			return false;
		}
		
		//$MSQS_QL->_p($product_id);die;
		if($product_id){
			$inventory_data = array();
			$inventory_data['wc_inventory_id'] = $product_id;			
			return $MSQS_QL->UpdateQboInventory($inventory_data);
		}
	}
	
	//06-06-2017
	public function myworks_wc_qbo_sync_variation_update_stock($instance){
		if(!class_exists('WooCommerce')) return;
		global $MSQS_QL;
		
		if(is_object($instance)){
			$variation_id = (int) $instance->get_id();
		}else{
			$variation_id = (int) $instance;
		}
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){			
			if($variation_id>0 && $MSQS_QL->check_if_real_time_push_enable_for_item('Inventory')){
				$MSQS_QL->real_time_hooks_add_queue('VariationInventory',$variation_id,'VariationInventoryPush','woocommerce_variation_set_stock:myworks_wc_qbo_sync_variation_update_stock');
			}
		}
		
		if(!$MSQS_QL->is_connected()){return;}
		
		if(!is_admin()){
			//return false;
		}
		
		if ($MSQS_QL->get_option('mw_qbo_sync_activation_redirect')!='true') {			
			return false;
		}
		$manual = false;
		
		/**/
		if(!$manual && $MSQS_QL->get_session_val('prevent_rt_inventory_push_ot',0,true)){
			//return false;
		}
		
		//Check If Real Time Enabled
		if(!$manual && !$MSQS_QL->check_if_real_time_push_enable_for_item('Inventory')){			
			return false;
		}
		
		if(!is_object($instance) || empty($instance)){
			$instance = (int) $instance;
			if($instance<1){
				return false;
			}
			$variation_id = $instance;
		}		
		
		if(is_object($instance) && !empty($instance)){
			$variation_id = (int) $instance->get_id();
		}
		
		if($variation_id){
			$inventory_data = array();
			$inventory_data['wc_inventory_id'] = $variation_id;	
			$inventory_data['is_variation'] = true;
			return $MSQS_QL->VariationUpdateQboInventory($inventory_data);
		}
		
	}
	
	/**/
	public function myworks_wc_qbo_sync_group_tax_exempt_init(){
		global $MSQS_QL;
		if($MSQS_QL->is_only_plugin_active('groups')){
			add_action( 'edit_user_profile', array($this,'myworks_customer_group_qb_tax_exempt_field'));
			add_action( 'edit_user_profile_update', array($this,'myworks_customer_group_qb_tax_exempt_f_update'));
			add_action( 'admin_footer', array($this,'myworks_customer_group_qb_tax_exempt_js'));
		}
	}	
	
	public function myworks_customer_group_qb_tax_exempt_field($profileuser){
		global $MSQS_QL;
		
		$qb_te_yn = esc_attr( get_the_author_meta( 'myworks_qb_tax_exempt_yn', $profileuser->ID ) );
		$qb_te_reason = esc_attr( get_the_author_meta( 'myworks_qb_tax_exempt_reason', $profileuser->ID ) );
		$rds = ($qb_te_yn == 'yes')?'':' style="display:none;"';
		echo '
		<div id="mw-qb-customer-gte-cnt" style="display:none;">
			<h3>'. __('QuickBooks Tax Exempt Status', 'mw_wc_qbo_sync') .'</h3>
				<table class="form-table">
					<tr>
						<th><label for="myworks_qb_tax_exempt_yn">'. __('Tax-Exempt', 'mw_wc_qbo_sync') .'</label></th>
						<td>
							<select id="myworks_qb_tax_exempt_yn" name="myworks_qb_tax_exempt_yn">
							'.$MSQS_QL->only_option($qb_te_yn,$MSQS_QL->yes_no,'','',true).'
							</select>
						</td>
					</tr>
					
					<tr id="mwqbter_tr"'.$rds.'>
						<th><label for="myworks_qb_tax_exempt_reason">'. __('Reason', 'mw_wc_qbo_sync') .'</label></th>
						<td>
							<select id="myworks_qb_tax_exempt_reason" name="myworks_qb_tax_exempt_reason">							
							'.$MSQS_QL->only_option($qb_te_reason,$MSQS_QL->get_qbo_cus_tax_exem_rsn_id_list(),'','',true).'
							</select>
							<br>
							<span class="description">Select QuickBooks tax exemption reason</span>
						</td>
					</tr>
					
				</table>
		</div>
		';
	}
	
	public function myworks_customer_group_qb_tax_exempt_f_update($userId) {
		if (!current_user_can('edit_user', $userId)) {
			return;
		}
		
		update_user_meta($userId, 'myworks_qb_tax_exempt_yn', (isset($_POST['myworks_qb_tax_exempt_yn']))?trim($_POST['myworks_qb_tax_exempt_yn']):'');
		update_user_meta($userId, 'myworks_qb_tax_exempt_reason', (isset($_POST['myworks_qb_tax_exempt_reason']))?(int) $_POST['myworks_qb_tax_exempt_reason']:'');
	}
	
	public function myworks_customer_group_qb_tax_exempt_js(){
		$output = '';
		global $pagenow;
		if ( ( $pagenow == 'user-edit.php' ) && empty( $_GET['page'] ) ) {
			$output = '
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$("#mw-qb-customer-gte-cnt").remove().insertAfter( $("#user-groups").next("div.selectize-control").next().next("p.description") );
					$("#mw-qb-customer-gte-cnt").show();
					$("#myworks_qb_tax_exempt_yn").change(function(){						
						if($(this).val() == "yes"){
							$("#mwqbter_tr").show();
						}else{
							$("#mwqbter_tr").hide();
						}
					});
				});
			</script>
			';
		}
		echo $output;
	}
	
}
/*Class End*/