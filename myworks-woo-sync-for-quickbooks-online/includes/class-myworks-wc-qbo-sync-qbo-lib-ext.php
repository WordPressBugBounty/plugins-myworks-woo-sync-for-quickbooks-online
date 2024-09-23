<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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

class MyWorks_WC_QBO_Sync_QBO_Lib_Ext extends MyWorks_WC_QBO_Sync_QBO_Lib{
	public function __construct(){
		add_filter( 'woocommerce_product_data_tabs', array($this,'qb_product_data_tab') );
		add_action( 'admin_head', array($this,'qpdt_style') );
		
		add_action('woocommerce_product_data_panels', array($this,'qb_product_data_fields'));
		add_action( 'woocommerce_process_product_meta_simple', array($this,'qb_product_data_save_fields') );
		
		if($this->option_checked('mw_wc_qbo_sync_sqaiw_v_sec')){
			add_action( 'woocommerce_product_after_variable_attributes', array($this,'qb_variation_data_fields'), 10, 3 );
			add_action( 'woocommerce_save_product_variation', array($this,'qb_variation_data_save_fields'), 10, 2 );	
		}
	}
	
	public function qb_product_data_tab( $product_data_tabs ) {
		$product_data_tabs['mwqbos-custom-tab'] = array(
			'label' => __( 'QuickBooks', 'woocommerce' ),
			'target' => 'mwqbos_custom_product_data',
			'class'     => array( 'show_if_simple' ),
		);
		
		return $product_data_tabs;
	}
	
	public function qpdt_style(){		
		$cnt = '"\f183"';
		echo '<style type="text/css">#woocommerce-product-data ul.wc-tabs li.mwqbos-custom-tab_options a:before { content: '.$cnt.'; }</style>';		
	}
	
	public function qb_product_data_fields() {
		global $post;
		global $wpdb;
		global $product;
		
		//variable
		if($product instanceof WC_Product && !$product->is_type( 'simple' )){
			return '';
		}
		
		$is_vendor_field = false;
		$v_tbl = $wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors';
		$dbtl = $this->get_plugin_db_tbl_list();
		
		if(is_array($dbtl) && in_array($v_tbl,$dbtl)){
			$is_vendor_field = true;
			$qbo_vendor_options = $this->get_key_value_options_from_table(true,$v_tbl,'qbo_vendorid','dname','','dname ASC','');
			
			$qb_p_vendor = get_post_meta( $post->ID, 'qb_p_vendor', true );
			if( empty( $qb_p_vendor ) ) $qb_p_vendor = '';
		}
		
		/**/
		$p_tbl = $wpdb->prefix.'mw_wc_qbo_sync_qbo_items';
		$pm_tbl = $wpdb->prefix.'mw_wc_qbo_sync_product_pairs';
		
		$qb_mapped_product = $this->get_field_by_val($pm_tbl,'quickbook_product_id','wc_product_id',$post->ID);		
		$qbo_product_options = '';		
		
		$dd_ext_class = '';
		if(!$this->option_checked('mw_wc_qbo_sync_select2_ajax')){
			$qbo_product_options = $this->get_key_value_options_from_table(true,$p_tbl,'itemid','name','','name ASC','');
		}else{
			$dd_ext_class = 'mwqs_dynamic_select';
			$qbo_product_options = array(
				'' => '',
			);
			if(!empty($qb_mapped_product)){
				$qb_item_name = $this->get_field_by_val($p_tbl,'name','itemid',$qb_mapped_product);
				$qbo_product_options[$qb_mapped_product] = $qb_item_name;
			}			
		}
		
		//
		if(empty($qb_mapped_product)){
			$qb_income_account = get_post_meta( $post->ID, 'qb_income_account', true );
			if( empty( $qb_income_account ) ) $qb_income_account = '';
			
			$qb_cogs_account = get_post_meta( $post->ID, 'qb_cogs_account', true );
			if( empty( $qb_cogs_account ) ) $qb_cogs_account = '';
			
			$qb_ia_account = get_post_meta( $post->ID, 'qb_ia_account', true );
			if( empty( $qb_ia_account ) ) $qb_ia_account = '';		
			
			if($this->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') && !$this->is_connected()){
				$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);			
				$qbo_account_options = $MSQS_QL->get_qb_account_option_arr(true);

				$qbo_category_options = $MSQS_QL->get_qb_category_option_arr(true);
			}else{
				$qbo_account_options = $this->get_qb_account_option_arr(true);

				$qbo_category_options = $this->get_qb_category_option_arr(true);
			}

			$qb_p_category = get_post_meta( $post->ID, 'qb_p_category', true );
			if( empty( $qb_p_category ) ) $qb_p_category = '';

			$qb_p_cost = get_post_meta( $post->ID, 'qb_p_cost', true );
			if( empty( $qb_p_cost ) ) $qb_p_cost = '';
		}
		
		echo '<div id = "mwqbos_custom_product_data" class = "panel woocommerce_options_panel">';
		echo '<div class = "options_group">';
		
		woocommerce_wp_select(
			array(
			'id' => 'qb_mapped_product',
			'label' => __( 'QuickBooks Product', 'woocommerce' ),
			'options' => $qbo_product_options,
			'value'   => $qb_mapped_product,
			'description' => __( 'QuickBooks Product that this WooCommerce product (variation) is mapped to. This can also be managed in MyWorks Sync > Map > Products. If blank, this product is not mapped.', 'woocommerce' ),
			'desc_tip' => true,
			'class'	=> 'select short mwqbos_cpd_s2 mwqboscpd_bb '.$dd_ext_class,
			)
		);

		echo '<hr>';
		
		//
		if(empty($qb_mapped_product)){
			woocommerce_wp_select(
				array(
				'id' => 'qb_income_account',
				'label' => __( 'Income Account', 'woocommerce' ),
				'options' => $qbo_account_options,
				'value'   => $qb_income_account,
				'description' => __( 'Choose a QuickBooks income account for this product to use when created in QuickBooks. If left blank, the default income account in MyWorks Sync > Settings will be used.', 'woocommerce' ),
				'desc_tip' => true,
				'class'	=> 'select short mwqbos_cpd_s2',
				)
			);
			 
			woocommerce_wp_select(
				array(
				'id' => 'qb_cogs_account',
				'label' => __( 'COGS Account', 'woocommerce' ),
				'options' => $qbo_account_options,
				'value'   => $qb_cogs_account,
				'description' => __( 'Choose a QuickBooks COGS account for this product to use when created in QuickBooks. If left blank, the default COGS account in MyWorks Sync > Settings will be used.', 'woocommerce' ),
				'desc_tip' => true,
				'class'	=> 'select short mwqbos_cpd_s2',
				)
			);
			 
			woocommerce_wp_select(
				array(
				'id' => 'qb_ia_account',
				'label' => __( 'Inventory Asset Account', 'woocommerce' ),
				'options' => $qbo_account_options,
				'value'   => $qb_ia_account,
				'description' => __( 'Account: Choose a QuickBooks Inventory Asset account for this product to use when created in QuickBooks. If left blank, the default Inventory Asset account in MyWorks Sync > Settings will be used.', 'woocommerce' ),
				'desc_tip' => true,
				'class'	=> 'select short mwqbos_cpd_s2 mwqboscpd_bb',
				)
			);

			echo '<hr>';
		}
		
		if(empty($qb_mapped_product) && $is_vendor_field){
			woocommerce_wp_select(
				array(
					'id' => 'qb_p_vendor',
					'label' => __( 'Preferred Vendor', 'woocommerce' ),
					'options' => $qbo_vendor_options,
					'value'   => $qb_p_vendor,
					'description' => __( 'Preferred Vendor: Choose a Preferred Vendor in QuickBooks for this product to use when created in QuickBooks. If left blank, no preferred vendor will be set.', 'woocommerce' ),
					'desc_tip' => true,
					'class'	=> 'select short mwqbos_cpd_s2 mwqboscpd_bb',
				)
			 );

			#echo '<hr>';
		}

		#New
		if(empty($qb_mapped_product)){
			woocommerce_wp_select(
				array(
					'id' => 'qb_p_category',
					'label' => __( 'QuickBooks Category', 'woocommerce' ),
					'options' => $qbo_category_options,
					'value'   => $qb_p_category,
					'description' => __( 'QuickBooks Category: Choose a Category in QuickBooks for this product to use when created in QuickBooks. If left blank, no category will be set.', 'woocommerce' ),
					'desc_tip' => true,
					'class'	=> 'select short mwqbos_cpd_s2 ',
				)
			 );
			
			woocommerce_wp_text_input(
				array(
				'id' => 'qb_p_cost', 
				'label' => __('QuickBooks Cost (' . get_woocommerce_currency_symbol() . ')', 'woocommerce'), 
				'placeholder' => '0.00', 
				'desc_tip' => 'true', 
				'description' => __('QuickBooks Cost: Field for the cost of the product in QuickBooks, which if entered, would set this as the cost of the product when we create it in QuickBooks.', 'woocommerce'), 
				'type' => 'text',
				'class' => 'mwqboscpd_bb',
				)
			);
		}
		
		echo '</div>';
		echo '</div>';
		$MWQS_OF = new MyWorks_WC_QBO_Sync_Oth_Funcs();
		echo $MWQS_OF->get_select2_js('.mwqbos_cpd_s2','qbo_product','yes');
	}
	
	public function qb_product_data_save_fields($post_id){
		/**/
		if(isset($_POST['qb_mapped_product'])){
			global $wpdb;
			$pm_tbl = $wpdb->prefix.'mw_wc_qbo_sync_product_pairs';
			
			$qb_mapped_product = $this->var_p('qb_mapped_product');
			$qb_mapped_product = $this->sanitize($qb_mapped_product);
			//update_post_meta($post_id, 'qb_mapped_product', esc_attr($qb_mapped_product));
			
			$save_data = array();
			$save_data['quickbook_product_id'] = $qb_mapped_product;
			if($this->get_field_by_val($pm_tbl,'quickbook_product_id','wc_product_id',$post_id)){
				$wpdb->update($pm_tbl,$save_data,array('wc_product_id'=>$post_id),'',array('%d'));
			}else{
				if(!empty($qb_mapped_product)){
					$save_data['wc_product_id'] = $post_id;
					$save_data['class_id'] = '';								
					
					$wpdb->insert($pm_tbl, $save_data);
				}				
			}
		}
		
		if(isset($_POST['qb_income_account'])){
			$qb_income_account = $this->var_p('qb_income_account');
			update_post_meta($post_id, 'qb_income_account', esc_attr($qb_income_account));
		}

		if(isset($_POST['qb_cogs_account'])){
			$qb_cogs_account = $this->var_p('qb_cogs_account');
			update_post_meta($post_id, 'qb_cogs_account', esc_attr($qb_cogs_account));
		}
		
		if(isset($_POST['qb_ia_account'])){
			$qb_ia_account = $this->var_p('qb_ia_account');
			update_post_meta($post_id, 'qb_ia_account', esc_attr($qb_ia_account));
		}
		
		if(isset($_POST['qb_p_vendor'])){
			$qb_p_vendor = $this->var_p('qb_p_vendor');
			update_post_meta($post_id, 'qb_p_vendor', esc_attr($qb_p_vendor));
		}

		if(isset($_POST['qb_p_category'])){
			$qb_p_category = $this->var_p('qb_p_category');
			update_post_meta($post_id, 'qb_p_category', esc_attr($qb_p_category));
		}

		if(isset($_POST['qb_p_cost'])){
			$qb_p_cost = $this->var_p('qb_p_cost');
			$qb_p_cost = floatval($qb_p_cost);
			update_post_meta($post_id, 'qb_p_cost', esc_attr($qb_p_cost));
		}	
	}
	
	public function qb_variation_data_fields( $loop, $variation_data, $variation ) {
		global $wpdb;
		//$post_parent = $variation->post_parent;
		
		$is_vendor_field = false;
		$v_tbl = $wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors';
		$dbtl = $this->get_plugin_db_tbl_list();
		
		if(is_array($dbtl) && in_array($v_tbl,$dbtl)){
			$is_vendor_field = true;
			$qbo_vendor_options = $this->get_key_value_options_from_table(true,$v_tbl,'qbo_vendorid','dname','','dname ASC','');
			
			$qb_p_vendor = get_post_meta( $variation->ID, 'qb_p_vendor', true );
			if( empty( $qb_p_vendor ) ) $qb_p_vendor = '';
		}
		
		/**/
		$p_tbl = $wpdb->prefix.'mw_wc_qbo_sync_qbo_items';
		$pm_tbl = $wpdb->prefix.'mw_wc_qbo_sync_variation_pairs';
		
		$qb_mapped_product = $this->get_field_by_val($pm_tbl,'quickbook_product_id','wc_variation_id',$variation->ID);		
		$qbo_product_options = '';		
		
		$dd_ext_class = '';
		if(!$this->option_checked('mw_wc_qbo_sync_select2_ajax')){
			$qbo_product_options = $this->get_key_value_options_from_table(true,$p_tbl,'itemid','name','','name ASC','');
		}else{
			$dd_ext_class = 'mwqs_dynamic_select';
			$qbo_product_options = array(
				'' => '',
			);
			if(!empty($qb_mapped_product)){
				$qb_item_name = $this->get_field_by_val($p_tbl,'name','itemid',$qb_mapped_product);
				$qbo_product_options[$qb_mapped_product] = $qb_item_name;
			}			
		}
		
		//
		if(empty($qb_mapped_product)){
			$qb_income_account = get_post_meta( $variation->ID, 'qb_income_account', true );
			if( empty( $qb_income_account ) ) $qb_income_account = '';
			
			$qb_cogs_account = get_post_meta( $variation->ID, 'qb_cogs_account', true );
			if( empty( $qb_cogs_account ) ) $qb_cogs_account = '';
			
			$qb_ia_account = get_post_meta( $variation->ID, 'qb_ia_account', true );
			if( empty( $qb_ia_account ) ) $qb_ia_account = '';
			
			if($this->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') && !$this->is_connected()){
				$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);			
				$qbo_account_options = $MSQS_QL->get_qb_account_option_arr(true);
			}else{
				$qbo_account_options = $this->get_qb_account_option_arr(true);
			}
		}
		
		echo '<div class = "panel woocommerce_options_panel">';
		echo '<div class = "options_group">';
		echo '<p><strong>QuickBooks</strong></p>';
		
		woocommerce_wp_select(
			array(
			'id' => 'qb_mapped_product['.$variation->ID.']',
			'label' => __( 'QuickBooks Product', 'woocommerce' ),
			'options' => $qbo_product_options,
			'value'   => $qb_mapped_product,
			'description' => __( 'QuickBooks Product that this WooCommerce product (variation) is mapped to. This can also be managed in MyWorks Sync > Map > Products. If blank, this product is not mapped.', 'woocommerce' ),
			'desc_tip' => true,
			'class'	=> 'select short mwqbos_cpd_s2 '.$dd_ext_class,
			)
		);
		
		//
		if(empty($qb_mapped_product)){
			woocommerce_wp_select(
				array(
				'id' => 'qb_income_account['.$variation->ID.']',
				'label' => __( 'Income Account', 'woocommerce' ),
				'options' => $qbo_account_options,
				'value'   => $qb_income_account,
				'description' => __( 'Choose a QuickBooks income account for this variation to use when created in QuickBooks. If left blank, the default income account in MyWorks Sync > Settings will be used.', 'woocommerce' ),
				'desc_tip' => true,
				'class'	=> 'select short mwqbos_cpd_s2',
				)
			);
			
			woocommerce_wp_select(
				array(
				'id' => 'qb_cogs_account['.$variation->ID.']',
				'label' => __( 'COGS Account', 'woocommerce' ),
				'options' => $qbo_account_options,
				'value'   => $qb_cogs_account,
				'description' => __( 'Choose a QuickBooks COGS account for this variation to use when created in QuickBooks. If left blank, the default COGS account in MyWorks Sync > Settings will be used.', 'woocommerce' ),
				'desc_tip' => true,
				'class'	=> 'select short mwqbos_cpd_s2',
				)
			);
			 
			woocommerce_wp_select(
				array(
				'id' => 'qb_ia_account['.$variation->ID.']',
				'label' => __( 'Inventory Asset Account', 'woocommerce' ),
				'options' => $qbo_account_options,
				'value'   => $qb_ia_account,
				'description' => __( 'Choose a QuickBooks Inventory Asset account for this variation to use when created in QuickBooks. If left blank, the default Inventory Asset account in MyWorks Sync > Settings will be used.', 'woocommerce' ),
				'desc_tip' => true,
				'class'	=> 'select short mwqbos_cpd_s2',
				)
			);
		}
		
		if($is_vendor_field){
			woocommerce_wp_select(
				array(
					'id' => 'qb_p_vendor['.$variation->ID.']',
					'label' => __( 'Preferred Vendor', 'woocommerce' ),
					'options' => $qbo_vendor_options,
					'value'   => $qb_p_vendor,
					'description' => __( 'Choose a Preferred Vendor in QuickBooks for this variation to use when created in QuickBooks. If left blank, no preferred vendor will be set.', 'woocommerce' ),
					'desc_tip' => true,
					'class'	=> 'select short mwqbos_cpd_s2',
				)
			 );
		}
		
		echo '</div>';
		echo '</div>';
		
		/**/		
		if(!$this->get_session_val('mw_qvdf_s2_js',false,true)){
			$MWQS_OF = new MyWorks_WC_QBO_Sync_Oth_Funcs();
			echo $MWQS_OF->get_select2_js('.mwqbos_cpd_s2','qbo_product','yes');
		}		
		$this->set_session_val('mw_qvdf_s2_js',true);
	}
	
	public function qb_variation_data_save_fields( $post_id ) {
		/**/
		if(isset($_POST['qb_mapped_product'][ $post_id ])){
			global $wpdb;
			$pm_tbl = $wpdb->prefix.'mw_wc_qbo_sync_variation_pairs';
			
			$qb_mapped_product = $this->sanitize($_POST['qb_mapped_product'][ $post_id ]);
			//update_post_meta($post_id, 'qb_mapped_product', esc_attr($qb_mapped_product));
			
			$save_data = array();
			$save_data['quickbook_product_id'] = $qb_mapped_product;
			if($this->get_field_by_val($pm_tbl,'quickbook_product_id','wc_variation_id',$post_id)){
				$wpdb->update($pm_tbl,$save_data,array('wc_variation_id'=>$post_id),'',array('%d'));
			}else{
				if(!empty($qb_mapped_product)){
					$save_data['wc_variation_id'] = $post_id;				
					$save_data['class_id'] = '';
					$wpdb->insert($pm_tbl, $save_data);
				}				
			}
		}
		
		if(isset($_POST['qb_income_account'][ $post_id ])){
			$qb_income_account = $this->sanitize($_POST['qb_income_account'][ $post_id ]);
			update_post_meta($post_id, 'qb_income_account', esc_attr($qb_income_account));
		}
		
		if(isset($_POST['qb_cogs_account'][ $post_id ])){
			$qb_cogs_account = $this->sanitize($_POST['qb_cogs_account'][ $post_id ]);
			update_post_meta($post_id, 'qb_cogs_account', esc_attr($qb_cogs_account));
		}
		
		if(isset($_POST['qb_ia_account'][ $post_id ])){
			$qb_ia_account = $this->sanitize($_POST['qb_ia_account'][ $post_id ]);
			update_post_meta($post_id, 'qb_ia_account', esc_attr($qb_ia_account));
		}
		
		if(isset($_POST['qb_p_vendor'][ $post_id ])){
			$qb_p_vendor = $this->sanitize($_POST['qb_p_vendor'][ $post_id ]);
			update_post_meta($post_id, 'qb_p_vendor', esc_attr($qb_p_vendor));
		}		
		
	}
}
new MyWorks_WC_QBO_Sync_QBO_Lib_Ext();