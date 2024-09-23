<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC_Admin_Setup_Wizard class.
 */
class QB_Online_Admin_Setup_Wizard {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		//add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this,'add_action_links') );
		add_action( 'admin_menu', array( $this, 'qb_online_setup_admin_menus' ) );
		add_action( 'admin_init', array( $this, 'qb_online_setup_wizard' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'qb_online_setup_enqueue_scripts' ) );
	}

	/**
	* Admin action links
	*/
	public function add_action_links($links) {
		$adminlinks = array(
		 '<a href="' . admin_url( '?page=qb-online-setup' ) . '">Setup Wizard</a>',
		 );
		$adminlinks[] = '<a href="' . admin_url( '?page=myworks-wc-qbo-sync-settings' ) . '">Settings</a>';
		return array_merge( $links, $adminlinks );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', QB_ADMIN_SETUP_PLUGIN_FILE ) );
	}	

	/**
	 * Add admin menus/screens.
	 */
	public function qb_online_setup_admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'qb-online-setup', '' );
	}

	public function qb_online_setup_wizard() {
			if ( empty( $_GET['page'] ) || 'qb-online-setup' !== $_GET['page'] ) { // WPCS: CSRF ok, input var ok.
				return;
			}
			$default_steps = array(
				'connect_to_qb' => array(
					'name'    => __( 'Connect to QuickBooks', 'woocommerce' ),
					'view'    => array( $this, 'connect_to_qb_setup'),
					'handler' => array( $this, 'connect_to_qb_setup_save'),
				),
				'default_qb_settings'     => array(
					'name'    => __( 'Default Settings', 'woocommerce' ),
					'view'    => array( $this, 'default_qb_settings_setup'),
					'handler' => array( $this, 'default_qb_settings_setup_save'),
				),
				'sync_qb_settings'    => array(
					'name'    => __( 'Sync Settings', 'woocommerce' ),
					'view'    => array( $this, 'sync_qb_settings_setup'),
					'handler' => array( $this, 'sync_qb_settings_setup_save'),
				),
				'mapping_qb_settings' => array(
					'name'    => __( 'Mapping', 'woocommerce' ),
					'view'    => array( $this, 'mapping_qb_settings_setup'),
					'handler' => array( $this, 'mapping_qb_settings_setup_save'),
				),
				'qb_setup_ready'  => array(
					'name'    => __( 'Ready!', 'woocommerce' ),
					'view'    => array( $this, 'qb_online_setup_ready'),
					'handler' => '',
				),
			);

			$this->steps = apply_filters( 'qb_online_setup_wizard_steps', $default_steps );
			$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) ); // WPCS: CSRF ok, input var ok.

			// @codingStandardsIgnoreStart
			if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				call_user_func( $this->steps[ $this->step ]['handler'], $this );
			}
			// @codingStandardsIgnoreEnd

			ob_start();
			$this->setup_wizard_header();
			$this->setup_wizard_steps();
			$this->setup_wizard_content();
			$this->setup_wizard_footer();
			exit;
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step  slug (default: current step).
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 3.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys, true );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
	}	

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {
		set_current_screen();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'MyWorks Sync &rsaquo; Setup Wizard', 'woocommerce' ); ?></title>
			<?php do_action( 'admin_enqueue_scripts' ); ?>
			<?php wp_print_scripts( 'wc-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wc-setup wp-core-ui">
			<h1 id="wc-logo"><a href="https://myworks.software"><img src="<?php echo esc_url( $this->plugin_url() ); ?>/assets/admin/images/mwd-logo.png" alt="MyWorks" /></a></h1>
		<?php
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$output_steps      = $this->steps;
		?>
		<ol class="wc-setup-steps">
			<?php
			foreach ( $output_steps as $step_key => $step ) {
				$is_completed = array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true );

				if ( $step_key === $this->step ) {
					?>
					<li class="active"><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				} elseif ( $is_completed ) {
					?>
					<li class="done">
						<a href="<?php echo esc_url( add_query_arg( 'step', $step_key, remove_query_arg( 'activate_error' ) ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
					</li>
					<?php
				} else {
					?>
					<li><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				}
			}
			?>
		</ol>
		<?php
	}


	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="wc-setup-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		?>
			<?php if ( 'connect_to_qb' === $this->step ) : ?>
				<a class="wc-setup-footer-links" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Not right now', 'woocommerce' ); ?></a>
			<?php elseif ( 'sync_qb_settings' === $this->step || 'mapping_qb_settings' === $this->step ) : ?>
				<a class="wc-setup-footer-links" href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step', 'woocommerce' ); ?></a>
			<?php endif; ?>
			</body>
		</html>
		<?php
	}

	public function qb_online_setup_enqueue_scripts() {
		$page = isset($_GET['page']) ? $_GET['page'] : '';

		if(!empty($page) && $page=='qb-online-setup') {
		// Whether or not there is a pending background install of Jetpack.
		$pending_jetpack = ! class_exists( 'Jetpack' ) && get_option( 'woocommerce_setup_background_installing_jetpack' );
		$suffix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_localize_script(
			'wc-enhanced-select',
			'wc_enhanced_select_params',
			array(
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			)
		);
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array( 'dashicons', 'install' ), WC_VERSION );

		wp_enqueue_style( 'qb-setup-css', $this->plugin_url() . '/assets/admin/css/admin-qb-setup.css', array( 'dashicons', 'install' ), WC_VERSION );		

		wp_register_script( 'wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup' . $suffix . '.js', array( 'jquery', 'wc-enhanced-select', 'jquery-blockui', 'wp-util', 'jquery-tiptip' ), WC_VERSION );
		wp_localize_script(
			'wc-setup',
			'wc_setup_params',
			array(
				'pending_jetpack_install' => $pending_jetpack ? 'yes' : 'no',
				'states'                  => WC()->countries->get_states(),
				'current_step'            => isset( $this->steps[ $this->step ] ) ? $this->step : false,
				'i18n'                    => array(
					'extra_plugins' => array(
						'payment' => array(
							'stripe_create_account'                              => __( 'Stripe setup is powered by Jetpack and WooCommerce Services.', 'woocommerce' ),
							'ppec_paypal_reroute_requests'                       => __( 'PayPal setup is powered by Jetpack and WooCommerce Services.', 'woocommerce' ),
							'stripe_create_account,ppec_paypal_reroute_requests' => __( 'Stripe and PayPal setup are powered by Jetpack and WooCommerce Services.', 'woocommerce' ),
						),
					),
				),
			)
		);
		}
	}

	public function connect_to_qb_setup() {
		?>
<form method="post" class="address-step" action="<?php echo esc_url( $this->get_next_step_link() ); ?>">
			<p class="store-setup"><?php esc_html_e( 'You are just a few steps away from syncing your store to QuickBooks. Let\'s get started!', 'woocommerce' ); ?></p>

			<div class="store-address-container">

				<label class="location-prompt" for="qb_licesnse_key"><?php esc_html_e( 'License Key', 'woocommerce' ); ?></label>
				<input type="text" id="qb_licesnse_key" class="location-input" name="qb_licesnse_key" required value="<?php echo esc_attr( $qb_licesnse_key ); ?>" />
				
				<label class="location-prompt" for="qb_access_token"><?php esc_html_e( 'Access Token', 'woocommerce' ); ?></label>
				<input type="text" id="qb_access_token" class="location-input" name="qb_access_token" required value="<?php echo esc_attr( $qb_access_token ); ?>" />				

			</div>				
			<p class="wc-setup-actions step">
				<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Let's go!", 'woocommerce' ); ?>" name="save_step"><?php esc_html_e( "Let's go!", 'woocommerce' ); ?></button>
			</p>
		</form>
		<?php
	}

public function default_qb_settings_setup() {
global $MSQS_QL,$MWQS_OF,$wpdb;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
}
$option_keys = $MWQS_OF->get_plugin_option_keys();
$admin_settings_data = $MSQS_QL->get_all_options($option_keys);
$mw_qbo_product_list = '';
if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
	$mw_qbo_product_list = $MSQS_QL->get_product_dropdown_list('');
}
$get_account_dropdown_list = $MSQS_QL->get_account_dropdown_list('',true);
?>

<h1><?php esc_html_e( 'Default Settings', 'woocommerce' ); ?></h1>
<form method="post" class="address-step" action="<?php echo esc_url( $this->get_next_step_link() ); ?>">
	<p>This is where you can set some basic default settings for your sync! You can always change these later in MyWorks Sync > Settings. </p>
			<div class="store-address-container">

				<label for="unmatched_products" class="location-prompt"><?php esc_html_e( 'Unmatched Products', 'woocommerce' ); ?></label>
				<select id="unmatched_products" name="unmatched_products" required data-placeholder="<?php esc_attr_e( 'Choose a product&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
				<?php
				$dd_options = '<option value=""></option>';
				$dd_ext_class = '';
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
				$dd_ext_class = 'mwqs_dynamic_select';
				if((int) $admin_settings_data['mw_wc_qbo_sync_default_qbo_item']){
				$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_default_qbo_item'];
				$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
				if($qb_item_name!=''){
				$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
				}
				}
				}else{
				$dd_options.=$mw_qbo_product_list;
				}
				echo $dd_options;
				?>
				</select>

				<label for="qb_sales_new_products" class="location-prompt"><?php esc_html_e( 'QuickBooks Sales Account for New Products', 'woocommerce' ); ?></label>
				<select id="qb_sales_new_products" name="qb_sales_new_products" required data-placeholder="<?php esc_attr_e( 'Choose a product&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
				<option value=""></option>
				<?php echo $get_account_dropdown_list; ?>
				</select>

				<label for="qb_inventory_new_products" class="location-prompt"><?php esc_html_e( 'QuickBooks Inventory Asset Account for New Products', 'woocommerce' ); ?></label>
				<select id="qb_inventory_new_products" name="qb_inventory_new_products" required data-placeholder="<?php esc_attr_e( 'Choose a product&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
				<option value=""></option>
				<?php echo $get_account_dropdown_list; ?>
				</select>

				<label for="qb_cogs_new_products" class="location-prompt"><?php esc_html_e( 'QuickBooks COGS Account for New Products', 'woocommerce' ); ?></label>
				<select id="qb_cogs_new_products" name="qb_cogs_new_products" required data-placeholder="<?php esc_attr_e( 'Choose a product&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
					<option value=""></option>
					<?php echo $get_account_dropdown_list; ?>
				</select>
				
				<label for="qb_sales_new_products" class="location-prompt"><?php esc_html_e( 'Sync WooCommerce Orders as', 'woocommerce' ); ?></label>
				<select id="qb_sales_new_products" name="qb_sales_new_products" required data-placeholder="<?php esc_attr_e( 'Choose a product&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
				<option value="invoice">Invoice</option>
				<option value="sales-receipt" selected="selected">Sales Receipt</option>
				</select>

				</div>

			<p class="wc-setup-actions step">
				<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woocommerce' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'woocommerce' ); ?></button>
			</p>

</form>				
	<?php
}

function sync_qb_settings_setup() {
	?>
<h1><?php esc_html_e( 'Automatic Sync', 'woocommerce' ); ?></h1>
<form method="post" class="wc-wizard-payment-gateway-form" action="<?php echo esc_url( $this->get_next_step_link() ); ?>">
<p>Here you can set the type of data you'd like automatically synced to QuickBooks. Since we only automatically sync new/updated data, you can easily push existing data to QuickBooks in our Push section after setup.</p>

<!--<label for="unmatched_products" class="location-prompt"><?php //esc_html_e( 'Sync Type', 'woocommerce' ); ?></label>
<select id="unmatched_products" name="unmatched_products" required data-placeholder="<?php //esc_attr_e( 'Choose sync type&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Sync', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<option value="real-time">Real time</option>
	<option value="automatic">Automatic</option>
</select>-->

<ul class="wc-wizard-services manual" style="margin-top: 15px;">
	<li class="wc-wizard-services-list-toggle">
		<div class="wc-wizard-service-name">
			<img src="<?php echo $this->plugin_url() . '/assets/admin/images/WOOtoQB.png'; ?>" >
			<?php //esc_html_e( 'WooCommerce to QuickBooks Online', 'woocommerce' ); ?>
		</div>
		<div class="wc-wizard-service-description">
			<?php esc_html_e( 'Set which new data will sync automatically from WooCommerce to QuickBooks.', 'woocommerce' ); ?>
		</div>
		<div class="wc-wizard-service-enable">
			<input class="wc-wizard-service-list-toggle" id="wc-wizard-service-list-toggle" type="checkbox">
			<label for="wc-wizard-service-list-toggle"></label>
		</div>
	</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Customers</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync new/updated customers to QuickBooks.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Orders</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync new/updated orders to QuickBooks.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Payments</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync new order payments to QuickBooks.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Products</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync new/updated products to QuickBooks.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Variations</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync new/updated variations to QuickBooks.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Inventory</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync manual inventory updates from WooCommerce to QuickBooks.</p>
				<p>Note: This is off by default, because inventory changes made by WooCommerce orders are always reflected in QB once we sync the order to QuickBooks.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>


</ul>


<ul class="wc-wizard-services manual">
	<li class="wc-wizard-services-list-toggle">
		<div class="wc-wizard-service-name">
			<img src="<?php echo $this->plugin_url() . '/assets/admin/images/QBtoWOO.png'; ?>" >
			<?php //esc_html_e( 'QuickBooks Online to WooCommerce', 'woocommerce' ); ?>
		</div>
		<div class="wc-wizard-service-description">
			<?php esc_html_e( 'Set which new/updated data will sync automatically from QuickBooks to WooCommerce.', 'woocommerce' ); ?>
		</div>
		<div class="wc-wizard-service-enable">
			<input class="wc-wizard-service-list-toggle" id="wc-wizard-service-list-toggle" type="checkbox">
			<label for="wc-wizard-service-list-toggle"></label>
		</div>
	</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Products</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync new/updated products to WooCommerce.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>

<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Inventory</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Sync updated inventory levels to WooCommerce.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>


<li class="wc-wizard-service-item ">
			<div class="wc-wizard-service-name">
			<p>Payments</p>
							</div>
			<div class="wc-wizard-service-description">
				<p>Mark a WooCommerce order complete once the invoice is paid in QuickBooks.</p>
							</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle ">
					<input id="wc-wizard-service-cheque" type="checkbox" name="wc-wizard-service-cheque-enabled" value="yes" checked="checked" data-plugins="null">
					<label for="wc-wizard-service-cheque">
				</label></span>
			</div>
</li>
</ul>

<p class="wc-setup-actions step">
	<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woocommerce' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'woocommerce' ); ?></button>
</p>

</form>
	<?php
}

function mapping_qb_settings_setup() {
global $MWQS_OF,$MSQS_QL,$wpdb,$woocommerce;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}
	?>
<h1><?php esc_html_e( 'Mapping Setup', 'woocommerce' ); ?></h1>
<form method="post" class="wc-wizard-payment-gateway-form" action="<?php echo esc_url( $this->get_next_step_link() ); ?>">

<p class="store-setup"><?php esc_html_e( 'WooCommerce & QuickBooks, a match made in heaven! But first, we need to match up your existing data. Automatically map your existing customers and products easily using the AutoMap feature below. Or, you can manually match them later in MyWorks Sync > Map.', 'woocommerce' ); ?></p>

<div class="store-address-container">
<div class="city-and-postcode">
<div>
	<label class="location-prompt" for="automap_customers_woo_field"><?php esc_html_e( 'Woocommerce Field', 'woocommerce' ); ?></label>
	<select id="automap_customers_woo_field" name="automap_customers_woo_field" data-placeholder="<?php esc_attr_e( 'Choose a field&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Field', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<option value=""></option>
	<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_cam_wf_list());?>		
	</select>
</div>
<div>
	<label class="location-prompt" for="automap_customers_qb_field"><?php esc_html_e( 'Quickbooks Field', 'woocommerce' ); ?></label>
	<select id="automap_customers_qb_field" name="automap_customers_qb_field" data-placeholder="<?php esc_attr_e( 'Choose a field&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Field', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<option value=""></option>
	<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_cam_qf_list());?>		
	</select>
</div>
</div>
</div>

<p class="store-setup" style="margin-top: 20px;"><?php esc_html_e( 'Automap Products', 'woocommerce' ); ?></p>
<div class="store-address-container">
<div class="city-and-postcode">
<div>
	<label class="location-prompt" for="automap_products_woo_field"><?php esc_html_e( 'Woocommerce Field', 'woocommerce' ); ?></label>
	<select id="automap_products_woo_field" name="automap_products_woo_field" data-placeholder="<?php esc_attr_e( 'Choose a field&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Field', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<option value=""></option>
	<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_pam_wf_list());?>
	</select>
</div>
<div>
	<label class="location-prompt" for="automap_products_qb_field"><?php esc_html_e( 'Quickbooks Field', 'woocommerce' ); ?></label>
	<select id="automap_products_qb_field" name="automap_products_qb_field" data-placeholder="<?php esc_attr_e( 'Choose a field&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Field', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<option value=""></option>
	<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_pam_qf_list());?>
	</select>
</div>
</div>
</div>
<hr style="margin-top: 25px;">
<h3 style="margin-top: 20px;"><?php esc_html_e( 'Payment Method Mappings', 'woocommerce' ); ?></h3>
<p>Ka-ching! Use the fields below to connect your WooCommerce payment methods to your QuickBooks bank accounts, so we're depositing payments into the right QuickBooks Bank account when we sync orders.</p>
<?php
$qbo_account_options = '<option value=""></option>';
$qbo_account_options.= $MSQS_QL->get_account_dropdown_list('',true,true);
$qbo_payment_method_options = '<option value=""></option>';
$qbo_payment_method_options.= $MSQS_QL->get_payment_method_dropdown_list();
?>
<p class="store-setup" style="margin-top: 20px;margin-bottom: 0px;"><input type="checkbox" name="bacs_qb_online_payment_method_enable" value="yes"><?php esc_html_e( 'Direct bank transfer (bacs)', 'woocommerce' ); ?></p>

<div class="store-address-container">
<div class="city-and-postcode">
<div>
	<label class="location-prompt" for="bacs_qb_online_payment_method"><?php esc_html_e( 'QuickBooks Online Payment Method', 'woocommerce' ); ?></label>
	<select id="bacs_qb_online_payment_method" name="bacs_qb_online_payment_method" data-placeholder="<?php esc_attr_e( 'Choose a method&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Method', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<?php echo $qbo_payment_method_options;?>
	</select>
</div>
<div>
	<label class="location-prompt" for="bacs_qb_online_bank_account"><?php esc_html_e( 'QuickBooks Online Bank Account', 'woocommerce' ); ?></label>
	<select id="bacs_qb_online_bank_account" name="bacs_qb_online_bank_account" data-placeholder="<?php esc_attr_e( 'Choose account&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Account', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<?php echo $qbo_account_options;?>
	</select>
</div>
</div>
</div>

<p class="store-setup" style="margin-top: 20px;margin-bottom: 0px;"><input type="checkbox" name="cheque_qb_online_payment_method_enable" value="yes"><?php esc_html_e( 'Check payments (cheque)', 'woocommerce' ); ?></p>

<div class="store-address-container">
<div class="city-and-postcode">
<div>
	<label class="location-prompt" for="cheque_qb_online_payment_method"><?php esc_html_e( 'QuickBooks Online Payment Method', 'woocommerce' ); ?></label>
	<select id="cheque_qb_online_payment_method" name="cheque_qb_online_payment_method" data-placeholder="<?php esc_attr_e( 'Choose a method&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Method', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<?php echo $qbo_payment_method_options;?>
	</select>
</div>
<div>
	<label class="location-prompt" for="cheque_qb_online_bank_account"><?php esc_html_e( 'QuickBooks Online Bank Account', 'woocommerce' ); ?></label>
	<select id="cheque_qb_online_bank_account" name="cheque_qb_online_bank_account" data-placeholder="<?php esc_attr_e( 'Choose account&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Account', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">	
	<?php echo $qbo_account_options;?>
	</select>
</div>
</div>
</div>

<p class="store-setup" style="margin-top: 20px;margin-bottom: 0px;"><input type="checkbox" name="cod_qb_online_payment_method_enable" value="yes"><?php esc_html_e( 'Cash on delivery (cod)', 'woocommerce' ); ?></p>

<div class="store-address-container">
<div class="city-and-postcode">
<div>
	<label class="location-prompt" for="cod_qb_online_payment_method"><?php esc_html_e( 'QuickBooks Online Payment Method', 'woocommerce' ); ?></label>
	<select id="cod_qb_online_payment_method" name="cod_qb_online_payment_method" data-placeholder="<?php esc_attr_e( 'Choose a method&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Method', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<?php echo $qbo_payment_method_options;?>
	</select>
</div>
<div>
	<label class="location-prompt" for="cod_qb_online_bank_account"><?php esc_html_e( 'QuickBooks Online Bank Account', 'woocommerce' ); ?></label>
	<select id="cod_qb_online_bank_account" name="cod_qb_online_bank_account" data-placeholder="<?php esc_attr_e( 'Choose account&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Account', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<?php echo $qbo_account_options;?>
	</select>
</div>
</div>
</div>

<p class="store-setup" style="margin-top: 20px;margin-bottom: 0px;"><input type="checkbox" name="paypal_qb_online_payment_method_enable" value="yes"><?php esc_html_e( 'PayPal (paypal)', 'woocommerce' ); ?></p>

<div class="store-address-container">
<div class="city-and-postcode">
<div>
	<label class="location-prompt" for="paypal_qb_online_payment_method"><?php esc_html_e( 'QuickBooks Online Payment Method', 'woocommerce' ); ?></label>
	<select id="paypal_qb_online_payment_method" name="paypal_qb_online_payment_method" data-placeholder="<?php esc_attr_e( 'Choose a method&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Method', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<?php echo $qbo_payment_method_options;?>
	</select>
</div>
<div>
	<label class="location-prompt" for="paypal_qb_online_bank_account"><?php esc_html_e( 'QuickBooks Online Bank Account', 'woocommerce' ); ?></label>
	<select id="paypal_qb_online_bank_account" name="paypal_qb_online_bank_account" data-placeholder="<?php esc_attr_e( 'Choose account&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Account', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
	<?php echo $qbo_account_options;?>
	</select>
</div>
</div>
</div>
<?php /* ?>
<ul class="wc-wizard-services in-cart">
<li class="wc-wizard-service-item paypal-logo">
<div class="wc-wizard-service-description">
<p>Append User ID for duplicate customers.</p>

</div>
<div class="wc-wizard-service-enable">
<span class="wc-wizard-service-toggle disabled">
<input id="wc-wizard-service-ppec_paypal" type="checkbox" name="wc-wizard-service-ppec_paypal-enabled" value="yes" data-plugins="[{&quot;slug&quot;:&quot;woocommerce-gateway-paypal-express-checkout&quot;,&quot;name&quot;:&quot;WooCommerce PayPal Checkout Gateway&quot;}]">
<label for="wc-wizard-service-ppec_paypal">
</label></span>
</div>
</li>
</ul>

<ul class="wc-wizard-services in-cart">
<li class="wc-wizard-service-item paypal-logo">
<div class="wc-wizard-service-description">
<p>Override customer mappings using Shipping Company.</p>

</div>
<div class="wc-wizard-service-enable">
<span class="wc-wizard-service-toggle disabled">
<input id="wc-wizard-service-ppec_paypal" type="checkbox" name="wc-wizard-service-ppec_paypal-enabled" value="yes" data-plugins="[{&quot;slug&quot;:&quot;woocommerce-gateway-paypal-express-checkout&quot;,&quot;name&quot;:&quot;WooCommerce PayPal Checkout Gateway&quot;}]">
<label for="wc-wizard-service-ppec_paypal">
</label></span>
</div>
</li>
</ul>

<ul class="wc-wizard-services in-cart">
<li class="wc-wizard-service-item paypal-logo">
<div class="wc-wizard-service-description">
<p>Override customer mappings using Billing Company.</p>

</div>
<div class="wc-wizard-service-enable">
<span class="wc-wizard-service-toggle disabled">
<input id="wc-wizard-service-ppec_paypal" type="checkbox" name="wc-wizard-service-ppec_paypal-enabled" value="yes" data-plugins="[{&quot;slug&quot;:&quot;woocommerce-gateway-paypal-express-checkout&quot;,&quot;name&quot;:&quot;WooCommerce PayPal Checkout Gateway&quot;}]">
<label for="wc-wizard-service-ppec_paypal">
</label></span>
</div>
</li>
</ul>

<ul class="wc-wizard-services in-cart">
<li class="wc-wizard-service-item paypal-logo">
<div class="wc-wizard-service-description">
<p>Sync all WooCommerce orders to one QuickBooks Online Customer.</p>

</div>
<div class="wc-wizard-service-enable">
<span class="wc-wizard-service-toggle disabled">
<input id="wc-wizard-service-ppec_paypal" type="checkbox" name="wc-wizard-service-ppec_paypal-enabled" value="yes" data-plugins="[{&quot;slug&quot;:&quot;woocommerce-gateway-paypal-express-checkout&quot;,&quot;name&quot;:&quot;WooCommerce PayPal Checkout Gateway&quot;}]">
<label for="wc-wizard-service-ppec_paypal">
</label></span>
</div>
</li>
</ul>

<ul class="wc-wizard-services in-cart">
<li class="wc-wizard-service-item paypal-logo">
<div class="wc-wizard-service-description">
<p>Push WooCommerce product title as QuickBooks Online product description?</p>

</div>
<div class="wc-wizard-service-enable">
<span class="wc-wizard-service-toggle disabled">
<input id="wc-wizard-service-ppec_paypal" type="checkbox" name="wc-wizard-service-ppec_paypal-enabled" value="yes" data-plugins="[{&quot;slug&quot;:&quot;woocommerce-gateway-paypal-express-checkout&quot;,&quot;name&quot;:&quot;WooCommerce PayPal Checkout Gateway&quot;}]">
<label for="wc-wizard-service-ppec_paypal">
</label></span>
</div>
</li>
</ul>

<div class="store-address-container">

<label class="location-prompt" for="qb_licesnse_key"><?php esc_html_e( 'QuickBooks Display Name format for new customers', 'woocommerce' ); ?></label>
<textarea></textarea>

<label for="unmatched_products" class="location-prompt"><?php esc_html_e( 'Customer Dropdown Sort Order', 'woocommerce' ); ?></label>
<select id="unmatched_products" name="unmatched_products" required data-placeholder="<?php esc_attr_e( 'Choose order&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Order', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
<option value="dname">Display name</option><option value="first">First name</option><option value="last">Last name</option><option value="company">Company name</option>
</select>

<label for="qb_sales_new_products" class="location-prompt"><?php esc_html_e( 'QuickBooks Customer', 'woocommerce' ); ?></label>
<select id="qb_sales_new_products" name="qb_sales_new_products" required data-placeholder="<?php esc_attr_e( 'Choose a customer&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Customer', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
<option value=""></option><option value="8">0969 Ocean View Road</option><option value="9">55 Twin Lane</option><option value="1">Amy\'s Bird Sanctuary</option><option value="23">Barnett Design</option><option value="61">BatchRT BatchTest</option><option value="2">Bill\'s Windsurf Shop</option><option value="83">Brendan Cote</option><option value="3">Cool Cars</option><option value="4">Diego Rodriguez</option><option value="5">Dukes Basketball Camp</option><option value="6">Dylan Sollfrank</option><option value="7">Freeman Sporting Goods</option><option value="10">Geeta Kalapatapu</option><option value="11">Gevelber Photography</option><option value="12">Jeff\'s Jalopies</option><option value="59">Jitesh Sen</option><option value="63">Jitesh Sen -687</option><option value="64">Jitesh Sen -688</option><option value="65">Jitesh Sen -689</option><option value="66">Jitesh Test Sen Test</option><option value="80">John Doe</option><option value="13">John Melton</option><option value="14">Kate Whelan</option><option value="16">Kookies by Kathy</option><option value="62">LeBilling TestMapping -57</option><option value="17">Mark Cho</option><option value="18">Paulsen Medical Supplies</option><option value="84">Peter Leonard</option><option value="85">Peter Leonards</option><option value="15">Pye\'s Cakes</option><option value="19">Rago Travel Agency</option><option value="20">Red Rock Diner</option><option value="21">Rondonuwu Fruit and Vegi</option><option value="22">Shara Barnett</option><option value="58">Sol Spier -4</option><option value="24">Sonnenschein Family Store</option><option value="25">Sushi by Katsuyuki</option><option value="86">Test Test</option><option value="60">Testing AutomatedTax</option><option value="71">Testing Geo</option><option value="72">Testing Geo -1313</option><option value="69">Testing Order</option><option value="73">Testing Webhook</option><option value="26">Travis Waldron</option><option value="27">Video Games by Dan</option><option value="28">Wedding Planning by Whitney</option><option value="29">Weiskopf Consulting</option><option value="77">Zae Anabt</option><option value="75">Zain A</option><option value="87">Zain A -1556</option><option value="78">Zain Anab</option><option value="81">Zain Anab -1461</option><option value="89">Zain Anab -1560</option><option value="88">Zain Anaba</option><option value="74">Zain Anabtawi</option><option value="79">Zain Anabtawi -1451</option><option value="82">Zain Anabtawi -1475</option><option value="76">Zane Anab</option>
</select>

<label for="qb_sales_new_products" class="location-prompt"><?php esc_html_e( 'Ignore these roles / Sync to individual mapped customer', 'woocommerce' ); ?></label>
<select id="qb_sales_new_products" name="qb_sales_new_products" required data-placeholder="<?php esc_attr_e( 'Choose a customer&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Customer', 'woocommerce' ); ?>" class="wc-enhanced-select location-input" multiple="multiple">
<option value="administrator" selected="selected">administrator</option><option value="author">author</option><option value="contributor">contributor</option><option value="customer">customer</option><option value="dropshipper">dropshipper</option><option value="editor">editor</option><option value="mw_qbo_sync_online">mw_qbo_sync_online</option><option value="shop_manager" selected="selected">shop_manager</option><option value="subscriber">subscriber</option><option value="wholesale">wholesale</option><option value="vendor">vendor</option>
</select>

<label for="qb_sales_new_products" class="location-prompt"><?php esc_html_e( 'Recognize other Wordpress roles as a customer', 'woocommerce' ); ?></label>
<select id="qb_sales_new_products" name="qb_sales_new_products" required data-placeholder="<?php esc_attr_e( 'Choose a customer&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Customer', 'woocommerce' ); ?>" class="wc-enhanced-select location-input" multiple="multiple">
<option value="administrator" selected="selected">administrator</option><option value="author">author</option><option value="contributor">contributor</option><option value="customer">customer</option><option value="dropshipper">dropshipper</option><option value="editor">editor</option><option value="mw_qbo_sync_online">mw_qbo_sync_online</option><option value="shop_manager" selected="selected">shop_manager</option><option value="subscriber">subscriber</option><option value="wholesale">wholesale</option><option value="vendor">vendor</option>
</select>

</div>
<?php */ ?>

<p class="wc-setup-actions step">
	<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woocommerce' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'woocommerce' ); ?></button>
</p>

</form>
<?php
}

function qb_online_setup_ready() {
	?>
<h1><?php esc_html_e( 'You did it!', 'woocommerce' ); ?></h1>
<p>Great job! Your inital setup is complete, and you're ready to sync new customers, orders and more! Take a peek at these helpful tips below as you get started.</p>
<ul>
	<li>Your new customers/orders/payments will be automatically real-time synced to QuickBooks from this point forward, per the switches you enabled in Sync Settings.</li>
	<li>You can easily push old/existing orders, products and more to QuickBooks by visiting MyWorks Sync > Push.</li>
	<li>You already auto-mapped your existing customers and products in the last step of setup. Visit MyWorks Sync > Map > Customers to check these mappings, and manually match any unmapped customers that are already in both systems.</li>
	<li>New customers/products we sync from this point forward will be automatically mapped.</li>
	<li>We covered our basic sync settings in this Setup Helper, but you can visit MyWorks Sync > Settings to take a look at our full settings.</li>
</ul>
<p>Any other questions? <a href="https://app.myworks.software/submitticket.php">Open a ticket with us here!</a></p>

	<?php
}

}

new QB_Online_Admin_Setup_Wizard();