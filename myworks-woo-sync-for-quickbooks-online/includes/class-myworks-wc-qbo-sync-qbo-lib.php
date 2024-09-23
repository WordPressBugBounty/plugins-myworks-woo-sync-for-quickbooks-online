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

if( !defined( "QUICKBOOKS_BASEDIR" )){	
	require_once plugin_dir_path( __FILE__ ) . 'lib/quickbooks-lib/QuickBooks.php';
}

class MyWorks_WC_QBO_Sync_QBO_Lib {
	protected $Context;
	protected $realm;

	protected $IPP;

	protected $creds;
	protected $is_connected;

	protected $mw_wc_qbo_sync_plugin_options;
	protected $qbo_company_preferences = false;
	protected $qbo_company_info = false;
	var $qbo_query_limit = 1000;

	protected $quickbooks_connection_dashboard_url='https://app.myworks.software';
	public $mwqbosession = null;
	
	public function __construct($queue_conn=false,$oauth2_refresh=false){
		/**/
		if($this->use_php_session()){
			if(!session_id() && $this->is_allow_php_session()) {
				session_start();
			}
		}else{
			#Wc Session initialize
			if($queue_conn){
				$this->initialize_session();
			}
			#
		}		
		
		if(!$this->mw_wc_qbo_sync_plugin_options || empty($this->mw_wc_qbo_sync_plugin_options)){
			$this->set_plugin_options();
			//
			if(!$this->option_checked('mw_wc_qbo_sync_customer_qbo_check')){
				update_option('mw_wc_qbo_sync_customer_qbo_check','true');
			}
			
			/**/
			if(!$this->option_checked('mw_wc_qbo_sync_ord_sa_new_pr_pg_fnct')){
				if(get_option('mw_wc_qbo_sync_order_as_sales_receipt') == 'true'){
					update_option('mw_wc_qbo_sync_order_qbo_sync_as','SalesReceipt');
				}				
				update_option('mw_wc_qbo_sync_ord_sa_new_pr_pg_fnct','true');
			}
		}
		#$this->_p($this->mw_wc_qbo_sync_plugin_options);
		if(($queue_conn || !$this->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')) && ($oauth2_refresh || ($this->Context=='' && $this->realm==''))){
			if($this->check_oauth2_refresh()){				
				$this->refresh_server_oauth2_connection();
			}
			$this->creds();
			$this->connect();
		}
		
		if($this->is_connected() && !$this->qbo_company_preferences){
			$this->get_qbo_company_preferences();
		}

		if($this->is_connected() && !$this->qbo_company_info){
			$this->set_qbo_company_info();
		}

	}
	
	#Debug Function
	public function debug(){
		
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			
			//$this->_p($this->get_qbo_company_info('is_category_enabled'),true);
			
			/*
			$invoiceService = new QuickBooks_IPP_Service_Invoice();
			$invoices = $invoiceService->query($Context,$realm ,"SELECT * FROM Invoice WHERE DocNumber = '1091' ");			
			$this->_p($invoices,true);
			*/			
			
			/*
			$ItemService = new QuickBooks_IPP_Service_Item();
			$sql = "SELECT * FROM Item WHERE Type = 'Group' ";
			$items = $ItemService->query($Context, $realm, $sql);
			$this->_p($items);
			*/

			//$this->_p($this->is_plugin_active('woocommerce-measurement-price-calculator'),true);
			//$this->_p($this->qbo_company_preferences);
			//$this->_p($this->get_qbo_group_product_details(34));
			//$this->Cron_Deposit(array('stripe'),'0',array('USD'));
			//$this->Cron_Deposit_Sr(array('stripe'),0,array('USD'));
			//$this->_p($this->get_qbo_company_setting('ClassTrackingPerTxnLine'),true);
			//$prf = $this->qbo_company_preferences;
			//$this->_p($prf);

			//CDC Test

			/*
			$interval_mins = 30;
			$now = new DateTime('', new DateTimeZone('America/Los_Angeles'));
			$datetime = $now->format('Y-m-d H:i:s');
			$datetime = date('Y-m-d H:i:s',strtotime("-{$interval_mins} minutes",strtotime($datetime)));
			$timestamp = date('Y-m-d', strtotime($datetime)) . 'T' . date('H:i:s', strtotime($datetime));

			$cdc_objects = array('Item');

			$CDCService = new QuickBooks_IPP_Service_ChangeDataCapture();
			$cdc = $CDCService->cdc($Context, $realm, $cdc_objects,	$timestamp);

			$this->_p($cdc);
			$this->_p($CDCService->lastRequest());
			$this->_p($CDCService->lastResponse());
			*/
			//$this->_p($this->db_check_get_fields_details());

			/*
			$wc_inv_id = 23765;//642
			$order = get_post($wc_inv_id);
			$invoice_data = $this->get_wc_order_details_from_order($wc_inv_id,$order);
			//$this->_p($invoice_data);
			$this->AddInvoice($invoice_data);
			*/
			
			
			//$this->_p($this->is_wc_deposit_pmnt_order($invoice_data));
			
			
			//$df_txt = '';
			//echo $this->get_wc_fee_qbo_product($df_txt,'',$invoice_data);
			//$this->_p($_SESSION);
			//$this->_p($this->get_wc_order_details_from_order(1961,get_post(1961)));
			//$this->qbo_functionality_after_pugin_activation();
			
			/*
			$q_prf = new QuickBooks_IPP_Service_Preferences();
			$prf = $this->qbo_company_preferences;
			$prf->getSalesFormsPrefs()->setAllowDiscount('true');
			$prf->getSalesFormsPrefs()->setAllowShipping('true');
			
			if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countCustomField()){
				$prf->getSalesFormsPrefs()->unsetCustomField();
			}
			
			if($prf->countVendorAndPurchasesPrefs() && $prf->getVendorAndPurchasesPrefs()->countPOCustomField()){
				$prf->getVendorAndPurchasesPrefs()->unsetPOCustomField();
			}
			
			if($prf->countMetaData()){
				$prf->unsetMetaData();
			}
			
			//$this->_p($prf);
			if($resp = $q_prf->update($Context, $realm, $prf->getId(), $prf)){
			
			}else{
				$res_err = $q_prf->lastError($Context);				
				echo 'Error:<br>';
				echo $res_err;
				echo '<br>';
				echo 'Request:<br>';
				echo $this->get_IPP()->lastRequest();
				echo '<br>';
				echo 'Response:<br>';
				echo $this->get_IPP()->lastResponse();				
			}
			*/

			//$invoice_data = $this->get_wc_order_details_from_order(23580,get_post(23580));
			//$this->AddPurchaseOrder($invoice_data);
			//$this->AddInvoice($invoice_data);			
			//$this->_p($this->get_qb_category_option_arr());
			//$this->_p($this->qbo_get_customer_sales_term(5));
			#$this->Pull_Product_Image(31,1);
			
			/*
			echo 'Product Image Push Test';
			$wpid = 23663;
			$p_m_img_a = wp_get_attachment_image_src( get_post_thumbnail_id($wpid),'single-post-thumbnail');
			$p_m_img_url = (is_array($p_m_img_a) && !empty($p_m_img_a))?$p_m_img_a[0]:'';
			$this->PushProductImg(67,array('wc_product_id'=>$wpid,'p_m_img_url'=>$p_m_img_url));
			*/
			
			/*
			$Attachable = new QuickBooks_IPP_Object_Attachable();
			$AttachableRef = new QuickBooks_IPP_Object_AttachableRef();
			
			$AttachableRef->setEntityRef("{-50}");
			$AttachableRef->setEntityRef_type('Item');			
			$AttachableRef->setIncludeOnSend('false');
			
			$Attachable->addAttachableRef($AttachableRef);
			$Attachable->set('sparse','false');
			$Attachable->set('domain','QBO');
			#$this->_p($Attachable);
			#$xml = $Attachable->asXML();
			
			#echo '<textarea style="border:none; width:80%; height:200px;">';
			#echo $xml;
			#echo '</textarea>';
			*/			
			
			
			/*
			echo 'Testing...';
			echo '<br>';
			$qbp_id = 64;
			$AttachableService = new QuickBooks_IPP_Service_Attachable();
			#$AQ = "SELECT * FROM Attachable WHERE AttachableRef.EntityRef.Type = 'Item' and AttachableRef.EntityRef.value = '{$qbp_id}'";
			$AQ = "SELECT * FROM Attachable WHERE Id='5100000000000630327'";
			
			$Attachables = $AttachableService->query($Context,$realm,$AQ);
			echo $AQ;
			$this->_p($Attachables);
			*/
			
			#$r = $this->check_attachable_exists_by_entity(68,'Item',true);
			#$this->_p($r);
			#echo 'PushOrderDocument';
			
			/*
			$this->PushOrderDocument(
				289,
				'Invoice',
				array('ord_doc_sync_test_f'=>'https://qbonline.myworks.dev/wp-content/uploads/2022/10/pexels-katie-burandt-1212693-scaled.jpg'),
				$this->get_cf_map_data(),
				$this->get_cf_map_data(true)
			);
			*/

			/*
			$data = $this->get_wc_order_details_from_order(23747,get_post(23747 ));
			$data['qbo_customerid'] = 85;
			if($this->is_sync_estimate_payment($data)){
				$this->PushEstimatePayment($data);
			}
			*/
		}
		
		//$this->_p($this->get_wc_booking_dtls(576));
		//$this->_p($this->get_compt_plugin_license_addons_arr());
		//$this->_p($this->ext_opsl_crypt('Testing','e'));
		//$this->_p($this->ext_opsl_crypt('NXRoem9Hdm1Rc09ldGZOR2dPbDRvdz09','d'));
		//$this->_p($this->get_wc_order_details_from_order(1694,get_post(1694)));
		//$this->_p($this->get_wc_order_details_from_order(1712,get_post(1712)));
		//$this->_p($this->get_wc_order_details_from_order(9858,get_post(9858)));
		//$this->_p($this->get_wc_order_details_from_order(23155,get_post(23154 )));
		//$this->_p($this->get_wc_fee_plugin_check(),true);
		//$this->_p($this->get_qs_catch_all_order_ids());		
		//echo $this->get_cmt_pl_nm_by_pl_dr_fn('','');
		//echo $this->get_variation_name_from_id('XXX','',1808);
		//$this->_p($this->get_woo_ord_number_from_order(1920),true);
		//$this->_p($this->get_order_sync_to_qbo_as(1942));
		//$this->_p($this->get_qbo_customer_email_by_id_from_db(156));
		//$this->_p($this->get_option('mw_wc_qbo_sync_wc_cust_role'));
		
		//$this->_p($this->get_woo_refund_id_from_order_id(23263));
		//echo $this->get_frl_c_qb_tlt_val(array('_billing_country' => 'MQ'));
		//$this->_p($this->get_domain());
		
		$this->test_debug_function();		
	}
	
	private function test_debug_function(){
		/**/
		//$this->Qbo_Pull_Payment(array('qbo_payment_id'=>258));		
	}
	
	public function initialize_session(){
		# New
		if(!is_user_logged_in()){
			return false;
		}
		
		$this->wc_session_includes();
		$session_class = 'MyWorks_WC_QBO_Sync_QBO_Lib_Session_Handler';
		if ( is_null( $this->mwqbosession ) || ! $this->mwqbosession instanceof $session_class ) {
			$this->mwqbosession = new $session_class();
			$this->mwqbosession->init();
		}
	}
	
	public function wc_session_includes(){
		if ( ! class_exists( 'WC_Session' ) ) {
			require_once WC_ABSPATH . 'includes/abstracts/abstract-wc-session.php';
		}
		
		require_once plugin_dir_path( __FILE__ ) . 'lib/session-handler.php';
	}
	
	public function use_php_session(){
		return false;
	}
	
	public function is_allow_php_session(){
		/*
		if(is_admin()){
			return false;
		}
		*/
		
		if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'wp-json/wp') !== false){
			return false;
		}
		
		if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'site-health.php') !== false){			
			return false;
		}
		
		if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'theme-editor.php') !== false){
			return false;
		}
		
		if(isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'], 'theme-editor.php') !== false){
			return false;
		}
		
		if((isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') !== false) || (isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'], 'admin-ajax.php') !== false)){
			//_wp_http_referer
			if(isset($_POST['action']) && $_POST['action'] == 'edit-theme-plugin-file'){
				return false;
			}
			
			//
			if(isset($_POST['action']) && strpos($_POST['action'], 'health-check-') !== false){				
				return false;
			}
			
			if(isset($_POST['action']) && strpos($_POST['action'], 'heartbeat') !== false){
				return false;
			}
		}
		
		return true;
	}
	
	function remove_scheme_from_url($url) {
	   $disallowed = array('http://', 'https://');
	   foreach($disallowed as $d) {
		  if(strpos($url, $d) === 0) {
			 return str_replace($d, '//', $url);
		  }
	   }
	   return $url;
	}

	public function get_dashboard_domain(){
		$url = $this->quickbooks_connection_dashboard_url;
		$url = parse_url($url, PHP_URL_HOST);
		return $url;
	}

	public function get_quickbooks_connection_dashboard_url($remove_scheme=false){
		if($remove_scheme){
			return $this->remove_scheme_from_url($this->quickbooks_connection_dashboard_url);
		}
		return $this->quickbooks_connection_dashboard_url;
	}
	
	public function set_plugin_options(){
		global $wpdb;
		$option_arr = array();
		#New
		$ignore_opts = array(
			"'mw_wc_qbo_sync_imp_oslcd_dca'",
			#"'mw_wc_qbo_sync_localkey'",
		);
		$io_q = implode(",",$ignore_opts);
		
		$option_data = $this->get_data("SELECT * FROM ".$wpdb->options." WHERE `option_name` LIKE 'mw_wc_qbo_sync%' AND `option_name` NOT IN({$io_q}) ");
		if(is_array($option_data) && !empty($option_data)){
			foreach($option_data as $Option){
				$option_arr[$Option['option_name']] = $Option['option_value'];
			}
		}
		$this->mw_wc_qbo_sync_plugin_options = $option_arr;
	}

	public function qbo_clear_braces($resp){
		preg_match("/\d+/i", $resp, $match);
		return (isset($match[0]))?$match[0]:$resp;
	}
	
	public function qcb_s($s){
		if(!empty($s) && strpos($s, '{-') !== false && strpos($s, '}') !== false){			
			$s = str_replace(array('{-','}'),'',$s);
		}
		
		return $s;
	}
	
	//Quickbooks Dropdowns
	//Product Dropdown
	public function get_product_list_array($realtime=false){
		$options = array();
		if($this->is_connected() && $realtime){
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $ItemService->query($Context, $realm, "SELECT COUNT(*)  FROM Item WHERE Type IN ('Inventory','Service','NonInventory','Group') ");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$items = $ItemService->query($Context, $realm, "SELECT Id , Name FROM Item WHERE Type IN ('Inventory','Service','NonInventory','Group') ORDER BY Name ASC STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($items && !empty($items)){
					foreach($items as $Item){
						$item_id = $this->qbo_clear_braces($Item->getId());
						$options[$item_id] = $this->escape($Item->getName());
					}
				}
			}
		}else{
			global $wpdb;
			$whr='';
			$product_list = $this->get_data("SELECT `itemid` , `name` FROM `".$wpdb->prefix.'mw_wc_qbo_sync_qbo_items'."` WHERE `ID` >0 ".$whr." ORDER BY `name` ASC");
			if(is_array($product_list) && !empty($product_list)){
				foreach($product_list as $product){
					$options[$product['itemid']] = $this->escape($product['name']);
				}
			}
		}
		return $options;
	}

	public function get_product_dropdown_list($s_val='',$realtime=false,$sku=false){
		$options = '';
		if($this->is_connected() && $realtime){
			$up_check_disabled = true;
			
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $ItemService->query($Context, $realm, "SELECT COUNT(*)  FROM Item WHERE Type IN ('Inventory','Service','NonInventory','Group') ");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$items = $ItemService->query($Context, $realm, "SELECT Id , Name FROM Item WHERE Type IN ('Inventory','Service','NonInventory','Group') ORDER BY Name ASC STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($items && !empty($items)){
					foreach($items as $Item){
						if($up_check_disabled || $Item->countUnitPrice()){
							$item_id = $this->qbo_clear_braces($Item->getId());
							$selected = '';
							if($s_val==$item_id){
								$selected = ' selected="selected" ';
							}
							$options.= '<option '.$selected.' value="'.esc_attr($item_id).'">'.$this->escape($Item->getName()).'</option>';
						}
					}
				}
			}
		}else{
			global $wpdb;
			if($sku){				
				$kv_arr = array();
				$op_fields = "itemid,name,sku";
				$op_data = $this->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_qbo_items',$op_fields,'','name ASC');
				if(is_array($op_data) && !empty($op_data)){
					foreach ($op_data as $key => $value) {
						$ov_str = (!empty($value['name']))?$value['name']:$value['sku'];

						if(!empty($value['name']) && !empty($value['sku'])){
							$ov_str .= '- SKU: '.$value['sku'];
						}

						if(!empty($ov_str)){
							$ov_str = stripslashes($ov_str);
						}

						$kv_arr[$value['itemid']] = $ov_str;
					}

					$options.= $this->only_option($s_val,$kv_arr,'','',true);
				}

			}else{
				$options.= $this->option_html($s_val, $wpdb->prefix.'mw_wc_qbo_sync_qbo_items','itemid','name','','name ASC','',true);
			}			

		}
		return $options;
	}

	//Term Dropdown
	public function get_term_dropdown_list($s_val=''){
		$options = '';
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$TermService = new QuickBooks_IPP_Service_Term();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $TermService->query($Context, $realm, "SELECT COUNT(*)  FROM Term");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$terms = $TermService->query($Context, $realm, "SELECT Id , Name FROM Term STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($terms && !empty($terms)){
					foreach($terms as $Term){
						$t_id = $this->qbo_clear_braces($Term->getId());
						$selected = '';
						if($s_val==$t_id){
							$selected = ' selected="selected" ';
						}
						$options.= '<option '.$selected.' value="'.esc_attr($t_id).'">'.$this->escape($Term->getName()).'</option>';
					}
				}
			}
		}
		return $options;
	}
	
	//
	public function get_term_list_array(){
		$options = array();
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$TermService = new QuickBooks_IPP_Service_Term();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $TermService->query($Context, $realm, "SELECT COUNT(*)  FROM Term");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$terms = $TermService->query($Context, $realm, "SELECT Id , Name FROM Term STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($terms && !empty($terms)){
					foreach($terms as $Term){
						$t_id = $this->qbo_clear_braces($Term->getId());
						$options[$t_id] = $this->escape($Term->getName());						
					}
				}
			}
		}
		return $options;
	}
	
	//Vendor Dropdown
	public function get_vendor_dropdown_list($s_val='',$realtime=true){
		$realtime=true;//
		$options = '';
		if($this->is_connected() && $realtime){
			$Context = $this->Context;
			$realm = $this->realm;

			$VendorService = new QuickBooks_IPP_Service_Vendor();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $VendorService->query($Context, $realm, "SELECT COUNT(*)  FROM Vendor");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$vendors = $VendorService->query($Context, $realm, "SELECT Id , DisplayName FROM Vendor STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				/*
				$return_obj_data=false
				if($return_obj_data){
					return $vendors;
				}
				*/
				if($vendors && !empty($vendors)){
					foreach($vendors as $Vendor){
						$v_id = $this->qbo_clear_braces($Vendor->getId());
						$selected = '';
						if($s_val==$v_id){
							$selected = ' selected="selected" ';
						}
						$options.= '<option '.$selected.' value="'.esc_attr($v_id).'">'.$this->escape($Vendor->getDisplayName()).'</option>';
					}
				}
			}
		}else{
			global $wpdb;
			$server_db = $this->db_check_get_fields_details();
			if(is_array($server_db) && isset($server_db[$wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors'])){
				$options.= $this->option_html($s_val, $wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors','qbo_vendorid','dname','','dname ASC','',true);
			}			
		}
		return $options;
	}
	
	//Tax Code Dropdown
	public function get_tax_code_dropdown_list($s_val=''){
		$options = '';
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$TaxCodeService = new QuickBooks_IPP_Service_TaxCode();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $TaxCodeService->query($Context, $realm, "SELECT COUNT(*)  FROM TaxCode");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$taxcodes = $TaxCodeService->query($Context, $realm, "SELECT Id , Name FROM TaxCode STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($taxcodes && !empty($taxcodes)){
					foreach($taxcodes as $TaxCode){
						$tc_id = $this->qbo_clear_braces($TaxCode->getId());
						$selected = '';
						if($s_val==$tc_id){
							$selected = ' selected="selected" ';
						}
						$options.= '<option '.$selected.' value="'.esc_attr($tc_id).'">'.$this->escape($TaxCode->getName()).'</option>';
					}
				}
			}
		}
		return $options;
	}

	//Department Dropdown
	public function get_department_dropdown_list($s_val=''){
		$options = '';
		if($this->is_connected() && $this->get_qbo_company_setting('TrackDepartments')){
			$Context = $this->Context;
			$realm = $this->realm;

			$DepartmentService = new QuickBooks_IPP_Service_Department();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $DepartmentService->query($Context, $realm, "SELECT COUNT(*)  FROM Department");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$Departments = $DepartmentService->query($Context, $realm, "SELECT Id , Name FROM Department STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($Departments && !empty($Departments)){
					foreach($Departments as $Department){
						$class_id = $this->qbo_clear_braces($Department->getId());
						$selected = '';
						if($s_val==$class_id){
							$selected = ' selected="selected" ';
						}
						$options.= '<option '.$selected.' value="'.esc_attr($class_id).'">'.$this->escape($Department->getName()).'</option>';
					}
				}
			}
		}
		return $options;
	}
	
	//Class Dropdown
	public function get_class_dropdown_list($s_val='',$txl_lavel=false){
		$options = '';
		
		if($this->is_plg_lc_p_l()){
			return $options;
		}
		
		if(!$txl_lavel){
			/*
			if(!empty($this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class'))){
				return $options;
			}
			*/
			
			if(!$this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
				return false;
			}
		}
		
		//
		if(!$this->get_qbo_company_setting('ClassTrackingPerTxn') && !$this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
			return $options;
		}
		
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$ClassService = new QuickBooks_IPP_Service_Class();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $ClassService->query($Context, $realm, "SELECT COUNT(*)  FROM Class");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$classes = $ClassService->query($Context, $realm, "SELECT Id , Name FROM Class STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($classes && !empty($classes)){
					foreach($classes as $Class){
						$class_id = $this->qbo_clear_braces($Class->getId());
						$selected = '';
						if($s_val==$class_id){
							$selected = ' selected="selected" ';
						}
						$options.= '<option '.$selected.' value="'.esc_attr($class_id).'">'.$this->escape($Class->getName()).'</option>';
					}
				}
			}
		}
		return $options;
	}

	//Payment Method Dropdown
	public function get_payment_method_dropdown_list($s_val=''){
		$options = '';
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$PaymentMethodService = new QuickBooks_IPP_Service_PaymentMethod();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $PaymentMethodService->query($Context, $realm, "SELECT COUNT(*)  FROM PaymentMethod");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$pmethods = $PaymentMethodService->query($Context, $realm, "SELECT Id , Name FROM PaymentMethod STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($pmethods && !empty($pmethods)){
					foreach($pmethods as $PaymentMethod){
						$pm_id = $this->qbo_clear_braces($PaymentMethod->getId());
						$selected = '';
						if($s_val==$pm_id){
							$selected = ' selected="selected" ';
						}
						$options.= '<option '.$selected.' value="'.esc_attr($pm_id).'">'.$this->escape($PaymentMethod->getName()).'</option>';
					}
				}
			}
		}
		return $options;
	}
	
	//Account Dropdown
	public function get_account_list_array($show_ac_type=false){
		$options = array();
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$AccountService = new QuickBooks_IPP_Service_Account();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $AccountService->query($Context, $realm, "SELECT COUNT(*)  FROM Account");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$accounts = $AccountService->query($Context, $realm, "SELECT * FROM Account STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($accounts && !empty($accounts)){
					foreach($accounts as $Account){
						$ac_type = $Account->getAccountType();
						$a_id = $this->qbo_clear_braces($Account->getId());

						if($show_ac_type){
							$options[$a_id] =  $this->escape($Account->getFullyQualifiedName()).' ('.$this->escape($ac_type).')';
						}else{
							$options[$a_id] = $this->escape($Account->getFullyQualifiedName());
						}

					}
				}
			}
		}
	}

	public function get_account_dropdown_list($s_val='',$show_ac_type=false,$b_udf_l=false){
		$options = '';
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$AccountService = new QuickBooks_IPP_Service_Account();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $AccountService->query($Context, $realm, "SELECT COUNT(*)  FROM Account");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$accounts = $AccountService->query($Context, $realm, "SELECT * FROM Account STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($accounts && !empty($accounts)){
					foreach($accounts as $Account){
						$ac_type = $Account->getAccountType();
						//
						if($b_udf_l){
							if($ac_type != 'Bank' && $ac_type != 'Other Current Asset' && $Account->getFullyQualifiedName() != 'Undeposited Funds'){
								continue;
							}
						}
						
						$a_id = (int) $this->qbo_clear_braces($Account->getId());
						$selected = '';
						if($s_val==$a_id){
							$selected = ' selected="selected" ';
						}
						if($show_ac_type){
							$options.= '<option '.$selected.' value="'.esc_attr($a_id).'">'.$this->escape($Account->getFullyQualifiedName()).' ('.$this->escape($ac_type).')'.'</option>';
						}else{
							$options.= '<option '.$selected.' value="'.esc_attr($a_id).'">'.$this->escape($Account->getFullyQualifiedName()).'</option>';
						}

					}
				}
			}
		}
		return $options;
	}
	
	public function get_qb_account_option_arr($blank_option=false){
		$acc_arr = array();
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$AccountService = new QuickBooks_IPP_Service_Account();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $AccountService->query($Context, $realm, "SELECT COUNT(*)  FROM Account");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$accounts = $AccountService->query($Context, $realm, "SELECT * FROM Account STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($accounts && !empty($accounts)){
					if($blank_option){
						$acc_arr[''] = '';
					}
					foreach($accounts as $Account){
						$ac_type = $Account->getAccountType();
						$acc_name = $Account->getFullyQualifiedName();
						$a_id = $this->qbo_clear_braces($Account->getId());
						
						$acc_arr[$a_id] = $this->escape($acc_name).' ('.$this->escape($ac_type).')';
					}
				}
			}
		}
		
		return $acc_arr;
	}

	#New
	public function get_qb_category_option_arr($blank_option=false){
		$cat_arr = array();
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Item();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $ItemService->query($Context, $realm, "SELECT COUNT(*)  FROM Item WHERE Type = 'Category'");			
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$categories = $ItemService->query($Context, $realm, "SELECT * FROM Item WHERE Type = 'Category' STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($categories && !empty($categories)){
					if($blank_option){
						$cat_arr[''] = '';
					}
					foreach($categories as $Category){						
						$cat_name = $Category->getFullyQualifiedName();
						$c_id = $this->qbo_clear_braces($Category->getId());
						
						$cat_arr[$c_id] = $this->escape($cat_name);
					}
				}
			}
		}
		
		return $cat_arr;
	}
	
	//CustomerType Dropdown
	public function get_customer_type_dropdown_list($s_val=''){
		$options = '';
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$CustomerTypeService = new QuickBooks_IPP_Service_CustomerType();
			
			$customer_types = $CustomerTypeService->query($Context, $realm, "SELECT Id , Name FROM CustomerType");			
			if($customer_types && !empty($customer_types)){
				foreach($customer_types as $CustomerType){
					$tc_id = $this->qbo_clear_braces($CustomerType->getId());
					$selected = '';
					if($s_val==$tc_id){
						$selected = ' selected="selected" ';
					}
					$options.= '<option '.$selected.' value="'.esc_attr($tc_id).'">'.$this->escape($CustomerType->getName()).'</option>';
				}
			}
		}
		return $options;
	}
	
	public function htmlspecialchars_decode_c($str){
		return htmlspecialchars_decode($str,ENT_QUOTES);
	}
	
	public function html_entity_decode_c($str){
		return html_entity_decode($str,ENT_QUOTES);
	}
	
	public function get_array_isset($data,$keyword,$default='',$decode=true,$trim=0,$addslash=false,$replace_array=array(),$stripslash=true){
		$return = $default;
		if(is_array($data) && !empty($data)){
			if(isset($data[$keyword])){
				$return = $data[$keyword];
				$return = trim($return);
				if($decode){
					//
					$return= strip_tags($return);
					
					$return = htmlspecialchars_decode($return,ENT_QUOTES);
					//27-06-2017
					$return = html_entity_decode($return,ENT_QUOTES);
				}
				if($trim){
					if(strlen($return) > $trim){
						$return = substr($return,0,$trim);
					}
				}
				
				/**/
				if($stripslash){
					$return = stripslashes($return);
				}
				
				if($addslash){
					$return = addslashes($return);
				}
				if(is_array($replace_array) && !empty($replace_array)){
					$return = str_replace($replace_array,'',$return);
				}
			}
		}
		return $return;
	}

	/*Restrictions*/
	public function if_sync_customer($wc_cus_id){
		return true;
	}
	
	public function if_sync_vendor($wc_cus_id){
		return true;
	}

	//14-03-2017
	public function check_save_get_qbo_guest_id($customer_data){
		if($qbo_customerid = $this->if_qbo_guest_exists($customer_data,true)){
			return $qbo_customerid;
		}
		
		//
		if($this->option_checked('mw_wc_qbo_sync_block_new_cus_sync_qb')){
			return 0;
		}
		
		return $this->AddGuest($customer_data);
	}
	
	public function if_qbo_guest_exists($customer_data,$return_qbo_customert_id=false){
		if($this->is_connected()){
			# New
			if(isset($customer_data['ParentRef']) && !empty($customer_data['ParentRef'])){
				return $this->qb_check_sub_customer_guest($customer_data['ParentRef'],$customer_data,$return_qbo_customer_id);
			}

			$Context = $this->Context;
			$realm = $this->realm;

			$name_replace_chars = array(':','\t','\n');
			$billing_email = $this->get_array_isset($customer_data,'billing_email','');
			$shipping_company = $this->get_array_isset($customer_data,'shipping_company','',true,100,true,$name_replace_chars);
			$billing_company = $this->get_array_isset($customer_data,'billing_company','',true,100,true,$name_replace_chars);
			
			$display_name = $this->get_array_isset($customer_data,'display_name','',true,100,true,$name_replace_chars);
			
			$firstname = $this->get_array_isset($customer_data,'billing_first_name','',true,25,true,$name_replace_chars);
			$lastname = $this->get_array_isset($customer_data,'billing_last_name','',true,25,true,$name_replace_chars);
			$fl_name = $firstname.' '.$lastname;
			
			if($billing_email!=''){
				$CustomerService = new QuickBooks_IPP_Service_Customer();
				if($shipping_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr')){
					$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$shipping_company}' ");
				}elseif($billing_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company')){				
					$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$billing_company}' ");
				}elseif(!empty($fl_name) && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
					$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$fl_name}' ");
				}else{
					$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE PrimaryEmailAddr = '{$billing_email}' ");
					
					//
					if($this->option_checked('mw_wc_qbo_sync_customer_match_by_name') && !$customers && !empty($display_name)){
						$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$display_name}' ");
					}
				}

				if($customers && !empty($customers)){
					$customer = $customers[0];

					if($return_qbo_customert_id){
						return $this->qbo_clear_braces($customer->getId());
					}else{
						return $customer;
					}
				}
			}

		}
		return false;
	}

	public function if_sync_guest($email){
		return true;
	}

	public function UpdateGuest($customer_data,$qbo_customer_obj=false){
		$manual = $this->get_array_isset($customer_data,'manual',false);
		if($manual){
			$this->set_session_val('sync_window_push_manual_update',true);
		}

		if(!$this->is_connected()){
			return false;
		}

		if(is_array($customer_data) && !empty($customer_data)){
			$billing_email = $this->get_array_isset($customer_data,'billing_email','');
			if($billing_email==''){
				return false;
			}

			$wc_inv_id = $this->get_array_isset($customer_data,'wc_inv_id',0);

			if($this->if_sync_guest($wc_customerid)){
				if($qbo_customer_obj && !empty($qbo_customer_obj)){
					$customer = $qbo_customer_obj;
				}else{
					$customer = $this->if_qbo_guest_exists($customer_data);
				}

				if(!$customer){
					$this->save_log("Update Customer/Guest Error \n"."Email:{$billing_email}",'QuickBooks Customer Not Found.','Customer',0);
					return false;
				}

				$customerService = new QuickBooks_IPP_Service_Customer();
				$Context = $this->Context;
				$realm = $this->realm;

				$name_replace_chars = array(':','\t','\n');

				$firstname = $this->get_array_isset($customer_data,'billing_first_name','',true,25,false,$name_replace_chars);
				$lastname = $this->get_array_isset($customer_data,'billing_last_name','',true,25,false,$name_replace_chars);
				$company = $this->get_array_isset($customer_data,'billing_company','',true,50,false,$name_replace_chars);
				$display_name = $this->get_array_isset($customer_data,'display_name','',true,100,false,$name_replace_chars);

				if($wc_inv_id && $this->check_qbo_customer_by_display_name($display_name)){
					if(!$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr') && !$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company') && !$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
						$display_name.=' -'.$wc_inv_id;
					}
				}
				
				//
				$middlename = '';

				$phone = $this->get_array_isset($customer_data,'billing_phone','',true,21);
				$email = $this->get_array_isset($customer_data,'billing_email','',true);


				$currency = $this->get_array_isset($customer_data,'currency','',true);

				$note = $this->get_array_isset($customer_data,'note','',true);

				$customer->setGivenName($firstname);
				$customer->setFamilyName($lastname);

				$customer->setCompanyName($company);
				$customer->setDisplayName($display_name);

				/*
				$primaryEmailAddr = new QuickBooks_IPP_Object_PrimaryEmailAddr();
				$primaryEmailAddr->setAddress($email);
				$customer->setPrimaryEmailAddr($primaryEmailAddr);
				*/

				if($phone!=''){
					$PrimaryPhone = new QuickBooks_IPP_Object_PrimaryPhone();
					$PrimaryPhone->setFreeFormNumber($phone);
					$customer->setPrimaryPhone($PrimaryPhone);
				}
				if($note!=''){
					$customer->setNotes($note);
				}

				if($currency!='' && $this->get_qbo_company_setting('is_m_currency')){
					 $customer->setCurrencyRef("{-$currency}");
				}
				
				/**/
				$customer_type_ref = $this->get_option('mw_wc_qbo_sync_qb_customer_type_fnc');
				/**/
				if($this->option_checked('mw_wc_qbo_sync_compt_np_wurqbld_ed')){
					$wc_user_role = 'wc_guest_user';					
					if(!empty($wc_user_role)){
						$wurqbct_wur_qbct_map = get_option('mw_wc_qbo_sync_wurqbct_wur_qbct_map');
						if(is_array($wurqbct_wur_qbct_map) && !empty($wurqbct_wur_qbct_map) && isset($wurqbct_wur_qbct_map[$wc_user_role])){
							$mct_id = trim($wurqbct_wur_qbct_map[$wc_user_role]);
							if($mct_id!= ''){
								$customer_type_ref = $mct_id;									
							}
						}
					}					
				}
				
				if(!empty($customer_type_ref)){
					$customer->setCustomerTypeRef($customer_type_ref);
				}
				
				$address = $this->get_array_isset($customer_data,'billing_address_1','',true);
				if($address!=''){
					$BillAddr = new QuickBooks_IPP_Object_BillAddr();
					$BillAddr->setLine1($address);

					$BillAddr->setLine2($this->get_array_isset($customer_data,'billing_address_2','',true));

					$BillAddr->setCity($this->get_array_isset($customer_data,'billing_city','',true));

					$country = $this->get_array_isset($customer_data,'billing_country','',true);
					$country = $this->get_country_name_from_code($country);

					$BillAddr->setCountry($country);

					$BillAddr->setCountrySubDivisionCode($this->get_array_isset($customer_data,'billing_state','',true));

					$BillAddr->setPostalCode($this->get_array_isset($customer_data,'billing_postcode','',true));
					$customer->setBillAddr($BillAddr);
				}

				//
				$shipping_address = $this->get_array_isset($customer_data,'shipping_address_1','',true);
				if($shipping_address!=''){
					$ShipAddr = new QuickBooks_IPP_Object_ShipAddr();
					$ShipAddr->setLine1($shipping_address);

					$ShipAddr->setLine2($this->get_array_isset($customer_data,'shipping_address_2','',true));

					$ShipAddr->setCity($this->get_array_isset($customer_data,'shipping_city','',true));

					$country = $this->get_array_isset($customer_data,'shipping_country','',true);
					$country = $this->get_country_name_from_code($country);
					$ShipAddr->setCountry($country);

					$ShipAddr->setCountrySubDivisionCode($this->get_array_isset($customer_data,'shipping_state','',true));

					$ShipAddr->setPostalCode($this->get_array_isset($customer_data,'shipping_postcode','',true));
					$customer->setShipAddr($ShipAddr);
				}

				$log_title = "";
				$log_details = "";
				$log_status = 0;

				if ($resp = $customerService->update($Context, $realm, $customer->getId(), $customer)){
					$qbo_customerid = $this->qbo_clear_braces($customer->getId());
					$log_title.="Update Customer/Guest\n";
					$log_title.="Email: {$billing_email}";
					$log_details.="Customer #$wc_customerid has been updated, Quickbooks Customer ID is #$qbo_customerid";
					$log_status = 1;
					$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Update');
					$this->add_qbo_item_obj_into_log_file('Customer/Guest Update',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
					$this->save_qbo_customer_local($qbo_customerid,$firstname,$lastname,$middlename,$company,$display_name,$email);

					return $qbo_customerid;
				}else{
					$res_err = $customerService->lastError($Context);
					$log_title.="Update Customer/Guest Error\n";
					$log_title.="Email: {$billing_email}";
					$log_details.="Error:$res_err";
					$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Update');
					$this->add_qbo_item_obj_into_log_file('Customer/Guest Update',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
					return false;
				}
			}
		}
	}
	
	public function AddGuest($customer_data){
		if(!$this->is_connected()){
			return false;
		}
		if(is_array($customer_data) && !empty($customer_data)){
			$billing_email = $this->get_array_isset($customer_data,'billing_email','');
			if($billing_email==''){
				return false;
			}
			$wc_inv_id = $this->get_array_isset($customer_data,'wc_inv_id',0);

			if($this->if_sync_guest($billing_email)){
				if(!$this->if_qbo_guest_exists($customer_data)){
					$name_replace_chars = array(':','\t','\n');

					$firstname = $this->get_array_isset($customer_data,'billing_first_name','',true,25,false,$name_replace_chars);
					$lastname = $this->get_array_isset($customer_data,'billing_last_name','',true,25,false,$name_replace_chars);
					$company = $this->get_array_isset($customer_data,'billing_company','',true,50,false,$name_replace_chars);

					$display_name = $this->get_array_isset($customer_data,'display_name','',true,100,false,$name_replace_chars);

					if(!isset($customer_data['ParentRef']) && $wc_inv_id && $this->check_qbo_customer_by_display_name($display_name)){
						if(!$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr') && !$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company') && !$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
							$display_name.=' -'.$wc_inv_id;
						}
					}
					
					//
					$middlename = '';

					$phone = $this->get_array_isset($customer_data,'billing_phone','',true,21);
					$email = $this->get_array_isset($customer_data,'billing_email','',true);


					$currency = $this->get_array_isset($customer_data,'currency','',true);

					$note = $this->get_array_isset($customer_data,'note','',true);

					$Context = $this->Context;
					$realm = $this->realm;

					$customerService = new QuickBooks_IPP_Service_Customer();
					$customer = new QuickBooks_IPP_Object_Customer();

					$customer->setGivenName($firstname);
					$customer->setFamilyName($lastname);

					$customer->setCompanyName($company);
					$customer->setDisplayName($display_name);

					# New
					if(isset($customer_data['ParentRef']) && !empty($customer_data['ParentRef'])){
						$customer->setParentRef($customer_data['ParentRef']);
						$customer->setJob(true);
					}
					
					$primaryEmailAddr = new QuickBooks_IPP_Object_PrimaryEmailAddr();
					$primaryEmailAddr->setAddress($email);
					$customer->setPrimaryEmailAddr($primaryEmailAddr);

					if($phone!=''){
						$PrimaryPhone = new QuickBooks_IPP_Object_PrimaryPhone();
						$PrimaryPhone->setFreeFormNumber($phone);
						$customer->setPrimaryPhone($PrimaryPhone);
					}
					if($note!=''){
						$customer->setNotes($note);
					}

					if($currency!='' && $this->get_qbo_company_setting('is_m_currency')){
						 $customer->setCurrencyRef("{-$currency}");
					}
					
					/**/
					$customer_type_ref = $this->get_option('mw_wc_qbo_sync_qb_customer_type_fnc');
					/**/
					if($this->option_checked('mw_wc_qbo_sync_compt_np_wurqbld_ed')){
						$wc_user_role = 'wc_guest_user';					
						if(!empty($wc_user_role)){
							$wurqbct_wur_qbct_map = get_option('mw_wc_qbo_sync_wurqbct_wur_qbct_map');
							if(is_array($wurqbct_wur_qbct_map) && !empty($wurqbct_wur_qbct_map) && isset($wurqbct_wur_qbct_map[$wc_user_role])){
								$mct_id = trim($wurqbct_wur_qbct_map[$wc_user_role]);
								if($mct_id!= ''){
									$customer_type_ref = $mct_id;									
								}
							}
						}					
					}
					
					if(!empty($customer_type_ref)){
						$customer->setCustomerTypeRef($customer_type_ref);
					}
					
					$address = $this->get_array_isset($customer_data,'billing_address_1','',true);
					if($address!=''){
						$BillAddr = new QuickBooks_IPP_Object_BillAddr();
						$BillAddr->setLine1($address);

						$BillAddr->setLine2($this->get_array_isset($customer_data,'billing_address_2','',true));

						$BillAddr->setCity($this->get_array_isset($customer_data,'billing_city','',true));

						$country = $this->get_array_isset($customer_data,'billing_country','',true);
						$country = $this->get_country_name_from_code($country);

						$BillAddr->setCountry($country);

						$BillAddr->setCountrySubDivisionCode($this->get_array_isset($customer_data,'billing_state','',true));

						$BillAddr->setPostalCode($this->get_array_isset($customer_data,'billing_postcode','',true));
						$customer->setBillAddr($BillAddr);
					}

					//
					$shipping_address = $this->get_array_isset($customer_data,'shipping_address_1','',true);
					if($shipping_address!=''){
						$ShipAddr = new QuickBooks_IPP_Object_ShipAddr();
						$ShipAddr->setLine1($shipping_address);

						$ShipAddr->setLine2($this->get_array_isset($customer_data,'shipping_address_2','',true));

						$ShipAddr->setCity($this->get_array_isset($customer_data,'shipping_city','',true));

						$country = $this->get_array_isset($customer_data,'shipping_country','',true);
						$country = $this->get_country_name_from_code($country);
						$ShipAddr->setCountry($country);

						$ShipAddr->setCountrySubDivisionCode($this->get_array_isset($customer_data,'shipping_state','',true));

						$ShipAddr->setPostalCode($this->get_array_isset($customer_data,'shipping_postcode','',true));
						$customer->setShipAddr($ShipAddr);
					}

					$log_title = "";
					$log_details = "";
					$log_status = 0;
					//$this->_p($customer);
					//return false;
					if ($resp = $customerService->add($Context, $realm, $customer)){
						$qbo_customerid = $this->qbo_clear_braces($resp);
						$log_title.="Export Customer/Guest\n";
						$log_title.="Email: {$billing_email}";
						$log_details.="Customer has been exported, Quickbooks Customer ID is #$qbo_customerid";
						$log_status = 1;
						$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Customer/Guest Add',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
						$this->save_qbo_customer_local($qbo_customerid,$firstname,$lastname,$middlename,$company,$display_name,$email);

						return $qbo_customerid;

					}else{
						$res_err = $customerService->lastError($Context);
						$log_title.="Export Customer/Guest Error\n";
						$log_title.="Email: {$billing_email}";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Customer/Guest Add',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
					}
				}
			}
		}
	}

	public function check_save_get_qbo_customer_id($customer_data){
		$wc_customerid = $this->get_array_isset($customer_data,'wc_customerid',0);
		if($qbo_customerid = $this->if_qbo_customer_exists($customer_data,true)){
			return $qbo_customerid;
		}
		
		//
		if($this->option_checked('mw_wc_qbo_sync_block_new_cus_sync_qb')){
			return 0;
		}
		
		return $this->AddCustomer($customer_data);
	}
	
	public function qbo_real_time_customer_check_get_object($qbo_customerid){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$customerService = new QuickBooks_IPP_Service_Customer();
			$customerData = $customerService->query($Context, $realm, "SELECT * FROM Customer WHERE Id = '".$qbo_customerid."' ");
			//$this->_p($customerData,true);
			if($customerData && !empty($customerData)){
				return $customerData[0];
			}
			return false;
		}
	}
	
	public function qbo_get_customer_sales_term($qbo_customerid){
		$qbo_customerid = (int) $qbo_customerid;
		if($this->is_connected() && $qbo_customerid > 0){
			$Context = $this->Context;
			$realm = $this->realm;
			$customerService = new QuickBooks_IPP_Service_Customer();
			$customerData = $customerService->query($Context, $realm, "SELECT * FROM Customer WHERE Id = '".$qbo_customerid."' ");
			
			//$this->_p($customerData);
			if($customerData && !empty($customerData)){
				$customerData = $customerData[0];
				if(!empty($customerData->getSalesTermRef())){
					return $this->qbo_clear_braces($customerData->getSalesTermRef());
				}
				return '';
			}
			return '';
		}
		
		return '';
	}
	
	//
	public function qbo_real_time_vendor_check_get_object($qbo_vendorid){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$vendorService = new QuickBooks_IPP_Service_Vendor();
			$vendorData = $vendorService->query($Context, $realm, "SELECT * FROM Vendor WHERE Id = '".$qbo_vendorid."' ");
			//$this->_p($vendorData,true);
			if($vendorData && !empty($vendorData)){
				return $vendorData[0];
			}
			return false;
		}
	}
	
	//
	public function if_qbo_vendor_exists($vendor_data,$return_qbo_vendor_id=false,$realtime_check_get_obj=false,$supplier_data=null){
		global $wpdb;
		$name_replace_chars = array(':','\t','\n');
		
		$wc_customerid = (int) $this->get_array_isset($vendor_data,'wc_customerid','',true);
		$display_name = $this->get_array_isset($vendor_data,'display_name','',true,100,false,$name_replace_chars);
		$email = $this->get_array_isset($vendor_data,'email','',true);
		
		//Map table
		$table = $wpdb->prefix.'mw_wc_qbo_sync_vendor_pairs';
		$query = $wpdb->prepare("SELECT `qbo_vendorid` FROM `$table` WHERE `wc_customerid` = %d AND `qbo_vendorid` >0 AND `wc_customerid` > 0 ",$wc_customerid);

		//Qbo vendor table
		if(empty($this->get_data($query))){
			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors';
			$query = $wpdb->prepare("SELECT `qbo_vendorid` FROM `$table` WHERE `email` = %s AND `email` !='' ",$email);
		}
		
		$query_vendor = $this->get_row($query);
		//mw_wc_qbo_sync_vendor_qbo_check
		if(empty($query_vendor) && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check')){
			if($email!='' && $this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;

				$VendorService = new QuickBooks_IPP_Service_Vendor();
				$vendors = $VendorService->query($Context,$realm ,"SELECT * FROM Vendor WHERE PrimaryEmailAddr = '{$email}' ");				
				//$this->_p($vendors);die;
				
				if($vendors && !empty($vendors)){
					$vendor = $vendors[0];

					if($return_qbo_vendor_id){
						if($realtime_check_get_obj){
							return $vendor;
						}
						return $this->qbo_clear_braces($vendor->getId());
					}
					return true;
				}
			}
			return false;
		}
		
		if($return_qbo_vendor_id){
			if($realtime_check_get_obj){
				//$this->_p($query_vendor,true);
				if(is_array($query_vendor) && !empty($query_vendor)){
					return $this->qbo_real_time_vendor_check_get_object($query_vendor['qbo_vendorid']);
				}
				return false;
			}
			return (is_array($query_vendor) && !empty($query_vendor))?$query_vendor['qbo_vendorid']:0;
		}
		return (is_array($query_vendor) && !empty($query_vendor))?true:false;
	}

	# New 
	public function qb_check_sub_customer_guest($ParentRef,$customer_data,$return_qbo_customer_id=false,$realtime_check_get_obj=false){
		if($this->is_connected() && !empty($ParentRef) && is_array($customer_data) && !empty($customer_data)){
			$email = $this->get_array_isset($customer_data,'email','',true);
			$field_n = 'PrimaryEmailAddr';
			$field_v = $email;

			if(!empty($field_n) && !empty($field_v)){
				$Context = $this->Context;
				$realm = $this->realm;

				$CustomerService = new QuickBooks_IPP_Service_Customer();				
				$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE {$field_n} = '{$field_v}' AND  Job = true ");
				
				if($customers && !empty($customers)){
					foreach($customers as $customer){						
						$Q_ParentRef = $customer->getParentRef();
						$Q_ParentRef = $this->qbo_clear_braces($Q_ParentRef);
						if(!empty($Q_ParentRef) && $ParentRef == $Q_ParentRef){
							if($return_qbo_customer_id){
								if($realtime_check_get_obj){
									return $customer;
								}
								return $this->qbo_clear_braces($customer->getId());
							}

							return true;
						}
					}					
				}
			}			
		}

		return false;		
	}
	
	public function if_qbo_customer_exists($customer_data,$return_qbo_customer_id=false,$realtime_check_get_obj=false){
		# New
		if(isset($customer_data['ParentRef']) && !empty($customer_data['ParentRef'])){
			return $this->qb_check_sub_customer_guest($customer_data['ParentRef'],$customer_data,$return_qbo_customer_id,$realtime_check_get_obj);
		}

		global $wpdb;
		$name_replace_chars = array(':','\t','\n');

		$wc_customerid = (int) $this->get_array_isset($customer_data,'wc_customerid','',true);
		$display_name = $this->get_array_isset($customer_data,'display_name','',true,100,true,$name_replace_chars);
		$email = $this->get_array_isset($customer_data,'email','',true);

		$shipping_company = $this->get_array_isset($customer_data,'shipping_company','',true,100,true,$name_replace_chars);
		$billing_company = $this->get_array_isset($customer_data,'billing_company','',true,100,true,$name_replace_chars);
		
		/**/
		$firstname = $this->get_array_isset($customer_data,'firstname','',true,25,true,$name_replace_chars);
		$lastname = $this->get_array_isset($customer_data,'lastname','',true,25,true,$name_replace_chars);
		$fl_name = $firstname.' '.$lastname;
		
		$mfb_qbc_em_dn = true;

		# New
		if($this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr') && !isset($customer_data['values_from_order_meta']) && isset($customer_data['order_id'])){
			$o_shipping_company = get_post_meta($customer_data['order_id'],'_shipping_company',true);
			if(!empty($o_shipping_company)){
				$o_shipping_company = $this->get_array_isset(array('o_shipping_company'=>$o_shipping_company),'o_shipping_company','',true,100,true,$name_replace_chars);
				$shipping_company = $o_shipping_company;
			}
		}
		
		if($this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company') && !isset($customer_data['values_from_order_meta']) && isset($customer_data['order_id'])){
			$o_billing_company = get_post_meta($customer_data['order_id'],'_billing_company',true);
			if(!empty($o_billing_company)){
				$o_billing_company = $this->get_array_isset(array('o_billing_company'=>$o_billing_company),'o_billing_company','',true,100,true,$name_replace_chars);
				$billing_company = $o_billing_company;
			}
		}
		
		if($this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name') && isset($customer_data['order_id'])){
			if(isset($customer_data['values_from_order_meta'])){
				$obfn = $this->get_array_isset($customer_data,'billing_first_name','',true);
				$obln = $this->get_array_isset($customer_data,'billing_last_name','',true);
			}else{
				$obfn = get_post_meta($customer_data['order_id'],'_billing_first_name',true);
				$obln = get_post_meta($customer_data['order_id'],'_billing_last_name',true);
			}

			if(!empty($obfn) || !empty($obln)){
				if(!empty($obfn)){
					$obfn = $this->get_array_isset(array('obfn'=>$obfn),'obfn','',true,25,true,$name_replace_chars);
				}

				if(!empty($obln)){
					$obln = $this->get_array_isset(array('obln'=>$obln),'obln','',true,25,true,$name_replace_chars);
				}
				
				$fl_name = $obfn.' '.$obln;
			}
		}
		
		//31-05-2017
		if($shipping_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr')){
			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers';
			$query = $wpdb->prepare("SELECT `qbo_customerid` FROM `$table` WHERE `dname` = %s AND `dname` !='' ",$shipping_company);
		}elseif($billing_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company')){
			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers';
			$query = $wpdb->prepare("SELECT `qbo_customerid` FROM `$table` WHERE `dname` = %s AND `dname` !='' ",$billing_company);
		}elseif(!empty($fl_name) && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers';
			$query = $wpdb->prepare("SELECT `qbo_customerid` FROM `$table` WHERE `dname` = %s AND `dname` !='' ",$fl_name);
			$mfb_qbc_em_dn = false;
		}else{
			//Map table
			$table = $wpdb->prefix.'mw_wc_qbo_sync_customer_pairs';
			$query = $wpdb->prepare("SELECT `qbo_customerid` FROM `$table` WHERE `wc_customerid` = %d AND `qbo_customerid` >0 AND `wc_customerid` > 0 ",$wc_customerid);

			//Qbo customer table
			if($mfb_qbc_em_dn && empty($this->get_data($query))){
				$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers';
				if(!empty($email)){
					$query = $wpdb->prepare("SELECT `qbo_customerid` FROM `$table` WHERE `email` = %s AND `email` !='' ",$email);
				}
				
				//
				if(empty($email) || empty($this->get_data($query))){
					if(!empty($display_name) && $this->option_checked('mw_wc_qbo_sync_customer_match_by_name')){
						$query = $wpdb->prepare("SELECT `qbo_customerid` FROM `$table` WHERE `dname` = %s AND `dname` !='' ",$display_name);
					}
				}
				
			}
		}
		
		$query_customer = $this->get_row($query);
		//$this->_p($query_customer,true);die;

		//31-03-2017
		if(empty($query_customer) && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check')){
			if(($email!='' || (!empty($display_name) && $this->option_checked('mw_wc_qbo_sync_customer_match_by_name'))) && $this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;

				$CustomerService = new QuickBooks_IPP_Service_Customer();

				if($shipping_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr')){
					$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$shipping_company}' ");
				}elseif($billing_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company')){
					$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$billing_company}' ");
				}elseif(!empty($fl_name) && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
					$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$fl_name}' ");
				}else{
					if(!empty($email)){
						$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE PrimaryEmailAddr = '{$email}' ");
					}
					
					//
					if(empty($email) || !$customers){
						if(!empty($display_name) && $this->option_checked('mw_wc_qbo_sync_customer_match_by_name')){
							$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE DisplayName = '{$display_name}' ");
						}
					}					
				}

				//$this->_p($customers);die;
				if($customers && !empty($customers)){
					$customer = $customers[0];

					if($return_qbo_customer_id){
						if($realtime_check_get_obj){
							return $customer;
						}
						return $this->qbo_clear_braces($customer->getId());
					}
					return true;
				}
			}
			return false;
		}

		if($return_qbo_customer_id){
			if($realtime_check_get_obj){
				//$this->_p($query_customer,true);
				if(is_array($query_customer) && !empty($query_customer)){
					return $this->qbo_real_time_customer_check_get_object($query_customer['qbo_customerid']);
				}
				return false;
			}
			return (is_array($query_customer) && !empty($query_customer))?$query_customer['qbo_customerid']:0;
		}
		return (is_array($query_customer) && !empty($query_customer))?true:false;

	}
	
	/**/
	public function validate_get_currency_qb_cust_id($qbo_customerid,$_order_currency){
		if($qbo_customerid && !empty($_order_currency) && $this->is_connected()){
			if(!$this->get_qbo_company_setting('is_m_currency')){
				return $qbo_customerid;
			}
			
			$Context = $this->Context;
			$realm = $this->realm;
			
			$CustomerService = new QuickBooks_IPP_Service_Customer();
			$customers = $CustomerService->query($Context,$realm ,"SELECT * FROM Customer WHERE Id = '{$qbo_customerid}' ");
			if($customers && !empty($customers)){
				$customer = $customers[0];
				$CurrencyRef = $customer->getCurrencyRef();
				$CurrencyRef = $this->qcb_s($CurrencyRef);
				
				if($_order_currency == $CurrencyRef){
					return $qbo_customerid;
				}
				
				$DisplayName = $customer->getDisplayName();
				if(empty($DisplayName)){
					return $qbo_customerid;
				}
				
				$cdn = $DisplayName.' - '.$_order_currency;				
				$customers_cc = $CustomerService->query($Context,$realm ,"SELECT Id FROM Customer WHERE DisplayName = '{$cdn}' ");
				
				if($customers_cc && !empty($customers_cc)){
					$customer_cc = $customers_cc[0];					
					return $this->qbo_clear_braces($customer_cc->getId());
				}else{
					//$Id = $this->qbo_clear_braces($customer->getId());					
					$customer_n = $customer;
					$customer_n->unsetId();
					$customer_n->unsetsparse();
					$customer_n->unsetMetaData();
					$customer_n->unsetSyncToken();
					$customer_n->unsetdomain();
					$customer_n->unsetFullyQualifiedName();
					$customer_n->unsetLevel();
					$customer_n->unsetIsProject();
					//
					$customer_n->setDisplayName($cdn);					
					$customer_n->setCurrencyRef("{-$_order_currency}");
					
					$log_title = "";
					$log_details = "";
					$log_status = 0;
					//$this->_p($customer_n);
					
					if ($resp = $CustomerService->add($Context, $realm, $customer_n)){
						$qbo_customerid = $this->qbo_clear_braces($resp);
						/*
						$log_title.="Export Customer #$wc_customerid\n";
						$log_details.="Customer #$wc_customerid has been exported, Quickbooks Customer ID is #$qbo_customerid";
						$log_status = 1;
						$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Customer Add',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
						$this->save_qbo_customer_local($qbo_customerid,$firstname,$lastname,$middlename,$company,$display_name,$email);
						$this->save_customer_map($wc_customerid,$qbo_customerid);
						*/
						
						return $qbo_customerid;

					}else{
						/*
						$res_err = $CustomerService->lastError($Context);
						$log_title.="Export Customer Error #$wc_customerid\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Customer Add',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
						*/
					}
				}				
			}
			
		}
		return $qbo_customerid;
	}
	
	public function check_save_automap_vendor_data($w_cus,$all_qbo_vendors,$map_by='email'){
		global $wpdb;
		$map_tbl = $wpdb->prefix."mw_wc_qbo_sync_vendor_pairs";
		$user_email = $w_cus['user_email'];
		$user_email = $this->sanitize($user_email);
		
		$display_name = $w_cus['display_name'];
		$display_name = $this->sanitize($display_name);

		foreach($all_qbo_vendors as $q_vend){
			$is_match_map_vendor = false;
			if($map_by=='email' && $user_email!='' && $user_email==$q_vend['email']){
				$is_match_map_vendor = true;
			}
			
			if($map_by=='name' && $display_name!='' && $display_name==$q_vend['d_name']){
				$is_match_map_vendor = true;
			}
			
			if($is_match_map_vendor){
				$save_data = array();
				$save_data['wc_customerid'] = $w_cus['ID'];
				$save_data['qbo_vendorid'] = $q_vend['qbo_vendorid'];
				$wpdb->insert($map_tbl,$save_data);
				return (int) $wpdb->insert_id;
				break;
			}
		}
	}
	
	public function check_save_automap_customer_data_wf_qf($w_cus,$all_qbo_customers,$cam_wf,$cam_qf,$mo_um=false){
		global $wpdb;
		$map_tbl = $wpdb->prefix."mw_wc_qbo_sync_customer_pairs";
		
		//New for Only Unmapped
		if($mo_um){
			$ID = $w_cus['ID'];							
			$e_mr = $this->get_row($wpdb->prepare("SELECT `id` FROM {$map_tbl} WHERE `wc_customerid` = %d ",$ID));
			if(!empty($e_mr)){
				return;
			}
		}
		
		if(!isset($w_cus[$cam_wf])){
			if($cam_wf=='first_name_last_name'){
				$w_cus[$cam_wf] = get_user_meta($w_cus['ID'],'first_name',true) . ' '. get_user_meta($w_cus['ID'],'last_name',true);
			}else{
				$w_cus[$cam_wf] = get_user_meta($w_cus['ID'],$cam_wf,true);
			}			
		}
		
		$wf_v = $this->get_array_isset($w_cus,$cam_wf,'',true);		
		
		if(!empty($cam_wf) && !empty($cam_qf)){
			foreach($all_qbo_customers as $q_cus){
				$is_match_map_customer = false;
				if(isset($q_cus[$cam_qf]) || $cam_qf == 'first_last'){
					if($cam_qf == 'first_last'){
						$qf_v = $this->get_array_isset($q_cus,'first','',true) . ' '. $this->get_array_isset($q_cus,'last','',true);
					}else{
						$qf_v = $this->get_array_isset($q_cus,$cam_qf,'',true);
					}
					
					if($wf_v!='' && strtoupper($wf_v) == strtoupper($qf_v)){
						$is_match_map_customer = true;
					}
					
					if($is_match_map_customer){
						$save_data = array();
						$save_data['wc_customerid'] = $w_cus['ID'];
						$save_data['qbo_customerid'] = $q_cus['qbo_customerid'];
						$wpdb->insert($map_tbl,$save_data);
						return (int) $wpdb->insert_id;
						break;
					}
				}
			}
		}
	}
	
	public function check_save_automap_customer_data($w_cus,$all_qbo_customers,$map_by='email'){
		global $wpdb;
		$map_tbl = $wpdb->prefix."mw_wc_qbo_sync_customer_pairs";
		$user_email = $w_cus['user_email'];
		$user_email = $this->sanitize($user_email);
		
		$display_name = $w_cus['display_name'];
		$display_name = $this->sanitize($display_name);

		foreach($all_qbo_customers as $q_cus){
			$is_match_map_customer = false;
			if($map_by=='email' && $user_email!='' && $user_email==$q_cus['email']){
				$is_match_map_customer = true;
			}
			
			if($map_by=='name' && $display_name!='' && $display_name==$q_cus['d_name']){
				$is_match_map_customer = true;
			}
			
			if($is_match_map_customer){
				$save_data = array();
				$save_data['wc_customerid'] = $w_cus['ID'];
				$save_data['qbo_customerid'] = $q_cus['qbo_customerid'];
				$wpdb->insert($map_tbl,$save_data);
				return (int) $wpdb->insert_id;
				break;
			}
		}
	}
	
	public function AutoMapCustomerWfQf($cam_wf,$cam_qf,$mo_um=false){
		global $wpdb;
		$map_count = 0;
		
		if($this->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
			return $map_count;
		}
		
		if(empty($cam_wf) || empty($cam_qf)){
			return $map_count;
		}
		
		if(!is_array($this->get_n_cam_wf_list()) || !is_array($this->get_n_cam_qf_list())){
			return $map_count;
		}
		$cam_wf_la = $this->get_n_cam_wf_list();
		$cam_qf_la = $this->get_n_cam_qf_list();
		if(!isset($cam_wf_la[$cam_wf]) || !isset($cam_qf_la[$cam_qf])){
			return $map_count;
		}
		
		$roles = 'customer'; // we can use multiple role comma separeted
		
		$ext_roles = $this->get_option('mw_wc_qbo_sync_wc_cust_role');
		if($ext_roles!=''){
			$roles.=','.$ext_roles;
		}

		if ( ! is_array( $roles ) ){			
			$roles = array_map('trim',explode( ",", $roles ));
		}
		
		$sql = '
			SELECT  ' . $wpdb->users . '.ID, ' . $wpdb->users . '.display_name, ' . $wpdb->users . '.user_email, ' . $wpdb->users . '.user_login
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';
		
		if($cam_qf=='first_last'){
			$cam_qf_cl = "`first` , `last`";
		}else{
			$cam_qf_cl = "`$cam_qf`";
		}		
		
		$all_wc_customers = $this->get_data($sql);
		$all_qbo_customers = $this->get_data("SELECT `qbo_customerid`, {$cam_qf_cl} FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_customers");
		
		if(!$mo_um){
			$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` ");
		}
		
		if(is_array($all_wc_customers) && !empty($all_wc_customers) && is_array($all_qbo_customers) && !empty($all_qbo_customers)){
			foreach($all_wc_customers as $w_cus){
				$insert_id = (int) $this->check_save_automap_customer_data_wf_qf($w_cus,$all_qbo_customers,$cam_wf,$cam_qf,$mo_um);
				if($insert_id>0){
					$map_count++;
				}
			}
		}
		unset($all_wc_customers);
		unset($all_qbo_customers);
		
		return $map_count;
	}
	
	public function AutoMapCustomer($map_by='email'){
		global $wpdb;
		$map_count = 0;
		
		if($this->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
			return $map_count;
		}
		
		$roles = 'customer'; // we can use multiple role comma separeted

		$ext_roles = $this->get_option('mw_wc_qbo_sync_wc_cust_role');
		if($ext_roles!=''){
			$roles.=','.$ext_roles;
		}

		if ( ! is_array( $roles ) ){
			//$roles = array_walk( explode( ",", $roles ), 'trim' );
			$roles = array_map('trim',explode( ",", $roles ));
		}

		$sql = '
			SELECT  ' . $wpdb->users . '.ID, ' . $wpdb->users . '.display_name, ' . $wpdb->users . '.user_email
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';

		//$sql = "SELECT `ID` , `user_email` , `display_name` FROM ".$wpdb->users."";

		$all_wc_customers = $this->get_data($sql);
		$all_qbo_customers = $this->get_data("SELECT `qbo_customerid`, `email` , `dname` FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_customers");

		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` ");

		if(is_array($all_wc_customers) && !empty($all_wc_customers) && is_array($all_qbo_customers) && !empty($all_qbo_customers)){
			foreach($all_wc_customers as $w_cus){
				$insert_id = (int) $this->check_save_automap_customer_data($w_cus,$all_qbo_customers,$map_by);
				if($insert_id>0){
					$map_count++;
				}
			}
		}
		unset($all_wc_customers);
		unset($all_qbo_customers);
		return $map_count;
	}
	
	//
	public function AutoMapVendor($map_by='email'){
		global $wpdb;
		$map_count = 0;
		$roles = ''; // we can use multiple role comma separeted
		
		$ext_roles = $this->get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur');
		if($ext_roles!=''){
			$roles.=','.$ext_roles;
		}
		
		if(empty($roles)){
			return $map_count;
		}

		if ( ! is_array( $roles ) ){
			//$roles = array_walk( explode( ",", $roles ), 'trim' );
			$roles = array_map('trim',explode( ",", $roles ));
		}

		$sql = '
			SELECT  ' . $wpdb->users . '.ID, ' . $wpdb->users . '.display_name, ' . $wpdb->users . '.user_email
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';

		//$sql = "SELECT `ID` , `user_email` , `display_name` FROM ".$wpdb->users."";

		$all_wc_customers = $this->get_data($sql);
		$all_qbo_vendors = $this->get_data("SELECT `qbo_vendorid`, `email` , `dname` FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_vendors");

		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_vendor_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_vendor_pairs` ");

		if(is_array($all_wc_customers) && !empty($all_wc_customers) && is_array($all_qbo_vendors) && !empty($all_qbo_vendors)){
			foreach($all_wc_customers as $w_cus){
				$insert_id = (int) $this->check_save_automap_vendor_data($w_cus,$all_qbo_vendors,$map_by);
				if($insert_id>0){
					$map_count++;
				}
			}
		}
		unset($all_wc_customers);
		unset($all_qbo_vendors);
		return $map_count;
	}
	
	public function AutoMapVendorByName(){
		return $this->AutoMapVendor('name');
	}
	
	//24-03-2017
	public function AutoMapCustomerNew(){
		global $wpdb;
		
		if($this->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
			return 0;
		}
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` ");

		$roles = 'customer'; // we can use multiple role comma separeted

		$ext_roles = $this->get_option('mw_wc_qbo_sync_wc_cust_role');
		if($ext_roles!=''){
			$roles.=','.$ext_roles;
		}

		if ( ! is_array( $roles ) ){
			//$roles = array_walk( explode( ",", $roles ), 'trim' );
			$roles = array_map('trim',explode( ",", $roles ));
		}

		$sql_count = '
			SELECT  COUNT(DISTINCT(' . $wpdb->users . '.ID))
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id,
			'.$wpdb->prefix.'mw_wc_qbo_sync_qbo_customers qc
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql_count .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
			if ( $i < count( $roles ) ) $sql_count .= ' OR ';
			$i++;
		}
		$sql_count .= ' ) ';

		$sql_count.=' AND '. $wpdb->users . '.user_email !=\'\' ';
		$sql_count.=' AND '. $wpdb->users . '.user_email = qc.email';

		$max_limit = 1000;

		$count = (int) $wpdb->get_var($sql_count);
		if(!$count){return false;}

		$batchCount =  ($max_limit >= $count) ? 1 : ceil($count / $max_limit);

		for ($i=0; $i<$batchCount; $i++) {
			$startPos = $i*$max_limit;
			$sql = '
				SELECT  DISTINCT(' . $wpdb->users . '.ID),qc.qbo_customerid
				FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
				ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id,
				'.$wpdb->prefix.'mw_wc_qbo_sync_qbo_customers qc
				WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
				AND     (
			';
			$j = 1;
			foreach ( $roles as $role ) {
				$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
				if ( $j < count( $roles ) ) $sql .= ' OR ';
				$j++;
			}
			$sql .= ' ) ';

			$sql.=' AND '. $wpdb->users . '.user_email !=\'\' ';
			$sql.=' AND '. $wpdb->users . '.user_email = qc.email ';
			$sql.=' GROUP BY '. $wpdb->users . '.ID';
			$sql.=" LIMIT {$startPos},{$max_limit}";

			$match_data = $this->get_data($sql);
			//$this->_p($match_data);continue;
			$c_map_ivs = '';
			if(is_array($match_data) && !empty($match_data)){
				foreach($match_data as $md){
					$c_map_ivs.='('.(int) $md['ID'].','.(int) $md['qbo_customerid'].'),';
				}
			}
			if($c_map_ivs!=''){
				$c_map_ivs = substr($c_map_ivs,0,-1);
				$c_map_insert_q = "INSERT INTO {$wpdb->prefix}mw_wc_qbo_sync_customer_pairs (wc_customerid,qbo_customerid) VALUES {$c_map_ivs}";
				$wpdb->query($c_map_insert_q);
			}
		}

		return $count;
	}
	
	public function AutoMapCustomerByName(){
		return $this->AutoMapCustomer('name');
	}
	
	public function AutoMapCustomerByNameNew(){
		global $wpdb;
		
		if($this->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
			return 0;
		}

		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` ");

		$roles = 'customer'; // we can use multiple role comma separeted

		$ext_roles = $this->get_option('mw_wc_qbo_sync_wc_cust_role');
		if($ext_roles!=''){
			$roles.=','.$ext_roles;
		}

		if ( ! is_array( $roles ) ){
			//$roles = array_walk( explode( ",", $roles ), 'trim' );
			$roles = array_map('trim',explode( ",", $roles ));
		}

		$sql_count = '
			SELECT  COUNT(DISTINCT(' . $wpdb->users . '.ID))
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id,
			'.$wpdb->prefix.'mw_wc_qbo_sync_qbo_customers qc
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql_count .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
			if ( $i < count( $roles ) ) $sql_count .= ' OR ';
			$i++;
		}
		$sql_count .= ' ) ';

		$sql_count.=' AND '. $wpdb->users . '.display_name !=\'\' ';
		$sql_count.=' AND '. $wpdb->users . '.display_name = qc.dname';
		$sql_count.=' AND qc.qbo_customerid > 0';

		$max_limit = 1000;

		$count = (int) $wpdb->get_var($sql_count);
		if(!$count){return false;}

		$batchCount =  ($max_limit >= $count) ? 1 : ceil($count / $max_limit);

		for ($i=0; $i<$batchCount; $i++) {
			$startPos = $i*$max_limit;
			$sql = '
				SELECT  DISTINCT(' . $wpdb->users . '.ID),qc.qbo_customerid
				FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
				ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id,
				'.$wpdb->prefix.'mw_wc_qbo_sync_qbo_customers qc
				WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
				AND     (
			';
			$j = 1;
			foreach ( $roles as $role ) {
				$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
				if ( $j < count( $roles ) ) $sql .= ' OR ';
				$j++;
			}
			$sql .= ' ) ';

			$sql.=' AND '. $wpdb->users . '.display_name !=\'\' ';
			$sql.=' AND '. $wpdb->users . '.display_name = qc.dname ';
			$sql.=' AND qc.qbo_customerid > 0';
			$sql.=" LIMIT {$startPos},{$max_limit}";

			$match_data = $this->get_data($sql);
			//$this->_p($match_data);continue;
			$c_map_ivs = '';
			if(is_array($match_data) && !empty($match_data)){
				foreach($match_data as $md){
					$c_map_ivs.='('.(int) $md['ID'].','.(int) $md['qbo_customerid'].'),';
				}
			}
			if($c_map_ivs!=''){
				$c_map_ivs = substr($c_map_ivs,0,-1);
				$c_map_insert_q = "INSERT INTO {$wpdb->prefix}mw_wc_qbo_sync_customer_pairs (wc_customerid,qbo_customerid) VALUES {$c_map_ivs}";
				$wpdb->query($c_map_insert_q);
			}
		}

		return $count;
	}
	
	//
	public function check_save_automap_product_data_wf_qf($w_pro,$all_qbo_products,$pam_wf,$pam_qf,$mo_um=false){
		global $wpdb;
		$map_tbl = $wpdb->prefix."mw_wc_qbo_sync_product_pairs";
		
		//New for Only Unmapped
		if($mo_um){
			$ID = $w_pro['ID'];
			$e_mr = $this->get_row($wpdb->prepare("SELECT `id` FROM {$map_tbl} WHERE `wc_product_id` = %d ",$ID));
			if(!empty($e_mr)){
				return;
			}
		}
		
		$wf_v = $this->get_array_isset($w_pro,$pam_wf,'',true);
		//$this->_p($wf_v);
		
		foreach($all_qbo_products as $q_pro){
			$is_match_map_product = false;
			if(isset($q_pro[$pam_qf])){
				//$this->_p($wf_v);
				$qf_v = $this->get_array_isset($q_pro,$pam_qf,'',true);				
				if($wf_v!='' && strtoupper($wf_v) == strtoupper($qf_v)){
					$is_match_map_product = true;
				}
			}
			
			if($is_match_map_product){
				$save_data = array();
				$save_data['wc_product_id'] = $w_pro['ID'];
				$save_data['quickbook_product_id'] = $q_pro['itemid'];
				$wpdb->insert($map_tbl,$save_data);
				return (int) $wpdb->insert_id;
				break;
			}
		}
	}
	
	public function check_save_automap_product_data($w_pro,$all_qbo_products,$map_by='sku'){
		global $wpdb;
		$map_tbl = $wpdb->prefix."mw_wc_qbo_sync_product_pairs";
		$sku = $this->sanitize($w_pro['sku']);
		$name = $this->sanitize($w_pro['name']);

		foreach($all_qbo_products as $q_pro){
			//15-03-2017
			$is_match_map_product = false;

			if($map_by=='sku' && $sku!=''){
				if($sku==$q_pro['sku']){
					$is_match_map_product = true;
				}
				if($q_pro['sku']=='' && $sku==$q_pro['name']){
					$is_match_map_product = true;
				}
			}
			
			if($map_by=='name' && $name!=''){
				if($name==$q_pro['name']){
					$is_match_map_product = true;
				}
			}

			if($is_match_map_product){
				$save_data = array();
				$save_data['wc_product_id'] = $w_pro['ID'];
				$save_data['quickbook_product_id'] = $q_pro['itemid'];
				$wpdb->insert($map_tbl,$save_data);
				return (int) $wpdb->insert_id;
				break;
			}
		}
	}
	
	//
	public function AutoMapProductWfQf($pam_wf,$pam_qf,$mo_um=false){
		global $wpdb;
		$map_count = 0;
		
		if(empty($pam_wf) || empty($pam_qf)){
			return $map_count;
		}
		
		if(!is_array($this->get_n_pam_wf_list()) || !is_array($this->get_n_pam_qf_list())){
			return $map_count;
		}
		$pam_wf_la = $this->get_n_pam_wf_list();
		$pam_qf_la = $this->get_n_pam_qf_list();
		if(!isset($pam_wf_la[$pam_wf]) || !isset($pam_qf_la[$pam_qf])){
			return $map_count;
		}
		
		$m_whr = '';
		if($pam_wf=='sku'){
			$m_whr.=" AND pm1.meta_value!=''";
		}
		
		$sql = "
			SELECT DISTINCT(p.ID), p.post_title AS name, pm1.meta_value AS sku
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
			AND pm1.meta_key =  '_sku' )
			WHERE p.post_type =  'product'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			{$m_whr}
		";
		
		$all_wc_products = $this->get_data($sql);
		$all_qbo_products = $this->get_data("SELECT `itemid`, `sku` , `name` FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_items");
		
		if(!$mo_um){
			$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` ");
		}
		
		if(is_array($all_wc_products) && !empty($all_wc_products) && is_array($all_qbo_products) && !empty($all_qbo_products)){
			foreach($all_wc_products as $w_pro){
				$insert_id = (int) $this->check_save_automap_product_data_wf_qf($w_pro,$all_qbo_products,$pam_wf,$pam_qf,$mo_um);
				if($insert_id>0){
					$map_count++;
				}
			}
		}
		unset($all_wc_products);
		unset($all_qbo_products);
		return $map_count;
	}
	
	public function AutoMapProduct($map_by='sku'){
		global $wpdb;
		$map_count = 0;
		$status = 'publish';

		$m_whr = '';
		if($map_by=='sku'){
			$m_whr.=" AND pm1.meta_value!=''";
		}
		
		$sql = "
			SELECT DISTINCT(p.ID), p.post_title AS name, pm1.meta_value AS sku
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
			AND pm1.meta_key =  '_sku' )
			WHERE p.post_type =  'product'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			{$m_whr}
		";
		
		$all_wc_products = $this->get_data($sql);
		$all_qbo_products = $this->get_data("SELECT `itemid`, `sku` , `name` FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_items");

		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` ");

		if(is_array($all_wc_products) && !empty($all_wc_products) && is_array($all_qbo_products) && !empty($all_qbo_products)){
			foreach($all_wc_products as $w_pro){
				$insert_id = (int) $this->check_save_automap_product_data($w_pro,$all_qbo_products,$map_by);
				if($insert_id>0){
					$map_count++;
				}
			}
		}
		unset($all_wc_products);
		unset($all_qbo_products);
		return $map_count;
	}
	
	/**/
	public function AutoMapVariationWfQf_N($vam_wf,$vam_qf,$mo_um=false){
		global $wpdb;
		
		/**/
		$set_met = false;
		if($set_met){
			ini_set('max_execution_time', 0);
			set_time_limit(0);
		}
		
		$map_count = 0;
		
		if(empty($vam_wf) || empty($vam_qf)){
			return $map_count;
		}
		
		if(!is_array($this->get_n_vam_wf_list()) || !is_array($this->get_n_vam_qf_list())){
			return $map_count;
		}
		$vam_wf_la = $this->get_n_vam_wf_list();
		$vam_qf_la = $this->get_n_vam_qf_list();
		if(!isset($vam_wf_la[$vam_wf]) || !isset($vam_qf_la[$vam_qf])){
			return $map_count;
		}
		
		$m_whr = '';
		$m_join = '';
		$m_slt = '';
		
		if($vam_wf=='sku'){
			$m_whr.=" AND pm1.meta_value!=''";
			$m_join.=" INNER JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
			AND pm1.meta_key =  '_sku' )";
			
			$m_slt.=" , pm1.meta_value AS sku";
		}
		
		$sql_c = "
			SELECT COUNT(*)
			FROM ".$wpdb->posts." p	
			{$m_join}
			WHERE p.post_type =  'product_variation'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			{$m_whr}
		";
		
		$mml = 500;
		$amc = (int) $wpdb->get_var($sql_c);
		
		$mbc =  ($mml >= $amc) ? 1 : ceil($amc / $mml);
		
		/**/
		$li = 0;
		$amlcd_a = get_option('mw_wc_qbo_sync_automap_last_c_data');
		if(!is_array($amlcd_a)){$amlcd_a = array();}
		if(!empty($amlcd_a) && isset($amlcd_a['pv']) && is_array($amlcd_a['pv']) && !empty($amlcd_a['pv'])){
			$lcd_pv = $amlcd_a['pv'];
			if($lcd_pv['wk'] == $vam_wf && $lcd_pv['qk'] == $vam_qf){
				if($lcd_pv['li'] != $mbc){
					$li = $lcd_pv['li'];
				}
			}
		}
		
		$all_wc_variations = array();
		//$all_qbo_products = array();
		
		if(!$mo_um){
			$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` ");
		}
		
		$all_qbo_products = $this->get_data("SELECT `itemid`, `sku` , `name` FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_items");
		
		for ($i=$li; $i<$mbc; $i++) {
			$mlo = $i*$mml;
			
			$sql_d = "
				SELECT DISTINCT(p.ID), p.post_title AS name {$m_slt}
				FROM ".$wpdb->posts." p	
				{$m_join}
				WHERE p.post_type =  'product_variation'
				AND p.post_status NOT IN('trash','auto-draft','inherit')
				{$m_whr}
				ORDER BY name ASC
				LIMIT {$mlo}, {$mml}
			";
			
			$all_wc_variations = $this->get_data($sql_d);
			
			if(is_array($all_wc_variations) && !empty($all_wc_variations) && is_array($all_qbo_products) && !empty($all_qbo_products)){
				foreach($all_wc_variations as $w_pro){
					$insert_id = (int) $this->check_save_automap_variation_data_wf_qf($w_pro,$all_qbo_products,$vam_wf,$vam_qf,$mo_um);
					if($insert_id>0){
						$map_count++;
					}
				}
			}
			
			//
			$amlcd_a['pv'] = array(
				'wk' => $vam_wf,
				'qk' => $vam_qf,
				'li' => $i+1,
			);
			update_option('mw_wc_qbo_sync_automap_last_c_data',$amlcd_a,'no');
		}
		
		unset($all_wc_variations);
		unset($all_qbo_products);

		# New
		if(is_array($amlcd_a) && isset($amlcd_a['pv'])){
			unset($amlcd_a['pv']);
			update_option('mw_wc_qbo_sync_automap_last_c_data',$amlcd_a,'no');
		}
		
		return $map_count;
	}
	
	public function AutoMapVariationWfQf($vam_wf,$vam_qf,$mo_um=false){
		//
		$foapn = true;
		if($foapn){
			return $this->AutoMapVariationWfQf_N($vam_wf,$vam_qf,$mo_um);
		}
		
		global $wpdb;
		
		/**/
		$set_met = false;
		if($set_met){
			ini_set('max_execution_time', 0);
			set_time_limit(0);
		}
		
		$map_count = 0;
		
		if(empty($vam_wf) || empty($vam_qf)){
			return $map_count;
		}
		
		if(!is_array($this->get_n_vam_wf_list()) || !is_array($this->get_n_vam_qf_list())){
			return $map_count;
		}
		$vam_wf_la = $this->get_n_vam_wf_list();
		$vam_qf_la = $this->get_n_vam_qf_list();
		if(!isset($vam_wf_la[$vam_wf]) || !isset($vam_qf_la[$vam_qf])){
			return $map_count;
		}
		
		$m_whr = '';
		if($vam_wf=='sku'){
			$m_whr.=" AND pm1.meta_value!=''";
		}
		
		$sql = "
			SELECT DISTINCT(p.ID), p.post_title AS name, pm1.meta_value AS sku
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
			AND pm1.meta_key =  '_sku' )
			WHERE p.post_type =  'product_variation'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			{$m_whr}
		";
		$all_wc_variations = $this->get_data($sql);
		$all_qbo_products = $this->get_data("SELECT `itemid`, `sku` , `name` FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_items");
		
		if(!$mo_um){
			$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` ");
		}		
		
		if(is_array($all_wc_variations) && !empty($all_wc_variations) && is_array($all_qbo_products) && !empty($all_qbo_products)){
			foreach($all_wc_variations as $w_pro){
				$insert_id = (int) $this->check_save_automap_variation_data_wf_qf($w_pro,$all_qbo_products,$vam_wf,$vam_qf,$mo_um);
				if($insert_id>0){
					$map_count++;
				}
			}
		}
		unset($all_wc_variations);
		unset($all_qbo_products);
		return $map_count;
	}
	
	public function AutoMapVariation($map_by='sku'){
		global $wpdb;
		$map_count = 0;
		$status = 'publish';

		$m_whr = '';
		if($map_by=='sku'){
			$m_whr.=" AND pm1.meta_value!=''";
		}
		
		$sql = "
			SELECT DISTINCT(p.ID), p.post_title AS name, pm1.meta_value AS sku
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
			AND pm1.meta_key =  '_sku' )
			WHERE p.post_type =  'product_variation'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			{$m_whr}
		";
		$all_wc_variations = $this->get_data($sql);
		$all_qbo_products = $this->get_data("SELECT `itemid`, `sku` , `name` FROM ".$wpdb->prefix."mw_wc_qbo_sync_qbo_items");
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` ");

		if(is_array($all_wc_variations) && !empty($all_wc_variations) && is_array($all_qbo_products) && !empty($all_qbo_products)){
			foreach($all_wc_variations as $w_pro){
				$insert_id = (int) $this->check_save_automap_variation_data($w_pro,$all_qbo_products,$map_by);
				if($insert_id>0){
					$map_count++;
				}
			}
		}
		unset($all_wc_variations);
		unset($all_qbo_products);
		return $map_count;
		
	}
	
	public function AutoMapVariationByName(){
		return $this->AutoMapVariation('name');
	}
	
	public function check_save_automap_variation_data_wf_qf($w_pro,$all_qbo_products,$vam_wf,$vam_qf,$mo_um=false){
		global $wpdb;
		$map_tbl = $wpdb->prefix."mw_wc_qbo_sync_variation_pairs";
		
		//New for Only Unmapped
		if($mo_um){
			$ID = $w_pro['ID'];
			$e_mr = $this->get_row($wpdb->prepare("SELECT `id` FROM {$map_tbl} WHERE `wc_variation_id` = %d ",$ID));
			if(!empty($e_mr)){
				return;
			}
		}
		
		$wf_v = $this->get_array_isset($w_pro,$vam_wf,'',true);
		
		foreach($all_qbo_products as $q_pro){
			$is_match_map_variation = false;
			if(isset($q_pro[$vam_qf])){
				//$this->_p($wf_v);
				$qf_v = $this->get_array_isset($q_pro,$vam_qf,'',true);				
				if($wf_v!='' && strtoupper($wf_v) == strtoupper($qf_v)){
					$is_match_map_variation = true;
				}
			}
			
			if($is_match_map_variation){
				$save_data = array();
				$save_data['wc_variation_id'] = $w_pro['ID'];
				$save_data['quickbook_product_id'] = $q_pro['itemid'];
				$wpdb->insert($map_tbl,$save_data);
				return (int) $wpdb->insert_id;
				break;
			}
		}
	}
	
	public function check_save_automap_variation_data($w_pro,$all_qbo_products,$map_by='sku'){
		global $wpdb;
		$map_tbl = $wpdb->prefix."mw_wc_qbo_sync_variation_pairs";
		$sku = $this->sanitize($w_pro['sku']);
		$name = $this->sanitize($w_pro['name']);

		foreach($all_qbo_products as $q_pro){
			$is_match_map_variation = false;

			if($map_by=='sku' && $sku!=''){
				if($sku==$q_pro['sku']){
					$is_match_map_variation = true;
				}
				if(!$is_match_map_variation && $q_pro['sku']=='' && $sku==$q_pro['name']){ 
					$is_match_map_variation = true;
				}
			}
			
			if($map_by=='name' && $name!=''){
				if($name==$q_pro['name']){
					$is_match_map_variation = true;
				}
			}
			
			if($is_match_map_variation){
				$save_data = array();
				$save_data['wc_variation_id'] = $w_pro['ID'];
				$save_data['quickbook_product_id'] = $q_pro['itemid'];
				$wpdb->insert($map_tbl,$save_data);
				return (int) $wpdb->insert_id;
				break;
			}
		}
	}
	
	public function AutoMapVariationNew(){
		global $wpdb;

		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` ");
		
		$status = 'publish';
		$sql_count = "
			SELECT COUNT(DISTINCT(p.ID))
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
			AND pm1.meta_key =  '_sku' ),
			".$wpdb->prefix."mw_wc_qbo_sync_qbo_items qp
			WHERE p.post_type =  'product_variation'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			AND pm1.meta_value!=''
			AND qp.itemid > 0
			AND (qp.sku=pm1.meta_value OR (qp.sku='' AND pm1.meta_value=qp.name))
		";
		//AND p.post_status = '".$status."'

		$max_limit = 1000;

		$count = (int) $wpdb->get_var($sql_count);
		if(!$count){return false;}

		$batchCount =  ($max_limit >= $count) ? 1 : ceil($count / $max_limit);

		for ($i=0; $i<$batchCount; $i++) {
			$startPos = $i*$max_limit;

			$sql = "
				SELECT DISTINCT(p.ID), qp.itemid
				FROM ".$wpdb->posts." p
				LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
				AND pm1.meta_key =  '_sku' ),
				".$wpdb->prefix."mw_wc_qbo_sync_qbo_items qp
				WHERE p.post_type =  'product_variation'
				AND p.post_status NOT IN('trash','auto-draft','inherit')
				AND pm1.meta_value!=''
				AND qp.itemid > 0
				AND (qp.sku=pm1.meta_value OR (qp.sku='' AND pm1.meta_value=qp.name))
				GROUP BY p.ID
				LIMIT {$startPos},{$max_limit}
			";
			//AND p.post_status = '".$status."'
			//echo $sql;
			$match_data = $this->get_data($sql);
			$p_map_ivs = '';
			if(is_array($match_data) && !empty($match_data)){
				foreach($match_data as $md){
					$p_map_ivs.='('.(int) $md['ID'].','.(int) $md['itemid'].'),';
				}
			}
			if($p_map_ivs!=''){
				$p_map_ivs = substr($p_map_ivs,0,-1);
				$p_map_insert_q = "INSERT INTO {$wpdb->prefix}mw_wc_qbo_sync_variation_pairs (wc_variation_id,quickbook_product_id) VALUES {$p_map_ivs}";
				$wpdb->query($p_map_insert_q);
			}
		}
		return $count;
	}

	//24-03-2017
	public function AutoMapProductNew(){
		global $wpdb;

		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` ");

		$status = 'publish';
		$sql_count = "
			SELECT COUNT(DISTINCT(p.ID))
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
			AND pm1.meta_key =  '_sku' ),
			".$wpdb->prefix."mw_wc_qbo_sync_qbo_items qp
			WHERE p.post_type =  'product'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			AND pm1.meta_value!=''
			AND qp.itemid > 0
			AND (qp.sku=pm1.meta_value OR (qp.sku='' AND pm1.meta_value=qp.name))
		";
		//AND p.post_status = '".$status."'

		$max_limit = 1000;

		$count = (int) $wpdb->get_var($sql_count);
		if(!$count){return false;}

		$batchCount =  ($max_limit >= $count) ? 1 : ceil($count / $max_limit);

		for ($i=0; $i<$batchCount; $i++) {
			$startPos = $i*$max_limit;

			$sql = "
				SELECT DISTINCT(p.ID), qp.itemid
				FROM ".$wpdb->posts." p
				LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID
				AND pm1.meta_key =  '_sku' ),
				".$wpdb->prefix."mw_wc_qbo_sync_qbo_items qp
				WHERE p.post_type =  'product'
				AND p.post_status NOT IN('trash','auto-draft','inherit')
				AND pm1.meta_value!=''
				AND qp.itemid > 0
				AND (qp.sku=pm1.meta_value OR (qp.sku='' AND pm1.meta_value=qp.name))
				GROUP BY p.ID
				LIMIT {$startPos},{$max_limit}
			";
			//AND p.post_status = '".$status."'
			//echo $sql;
			$match_data = $this->get_data($sql);
			$p_map_ivs = '';
			if(is_array($match_data) && !empty($match_data)){
				foreach($match_data as $md){
					$p_map_ivs.='('.(int) $md['ID'].','.(int) $md['itemid'].'),';
				}
			}
			if($p_map_ivs!=''){
				$p_map_ivs = substr($p_map_ivs,0,-1);
				$p_map_insert_q = "INSERT INTO {$wpdb->prefix}mw_wc_qbo_sync_product_pairs (wc_product_id,quickbook_product_id) VALUES {$p_map_ivs}";
				$wpdb->query($p_map_insert_q);
			}
		}
		return $count;
	}
	
	public function AutoMapProductByName(){
		return $this->AutoMapProduct('name');
	}
	
	public function AutoMapProductByNameNew(){
		global $wpdb;

		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` ");

		$status = 'publish';
		$sql_count = "
			SELECT COUNT(DISTINCT(p.ID))
			FROM ".$wpdb->posts." p,
			".$wpdb->prefix."mw_wc_qbo_sync_qbo_items qp
			WHERE p.post_type =  'product'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			AND p.post_title!=''
			AND qp.itemid > 0
			AND qp.name=p.post_title
		";
		//AND p.post_status = '".$status."'

		$max_limit = 1000;

		$count = (int) $wpdb->get_var($sql_count);
		if(!$count){return false;}

		$batchCount =  ($max_limit >= $count) ? 1 : ceil($count / $max_limit);

		for ($i=0; $i<$batchCount; $i++) {
			$startPos = $i*$max_limit;

			$sql = "
				SELECT DISTINCT(p.ID), qp.itemid
				FROM ".$wpdb->posts." p,
				".$wpdb->prefix."mw_wc_qbo_sync_qbo_items qp
				WHERE p.post_type =  'product'
				AND p.post_status NOT IN('trash','auto-draft','inherit')
				AND p.post_title!=''
				AND qp.itemid > 0
				AND qp.name=p.post_title
				LIMIT {$startPos},{$max_limit}
			";

			//echo $sql;
			$match_data = $this->get_data($sql);
			$p_map_ivs = '';
			if(is_array($match_data) && !empty($match_data)){
				foreach($match_data as $md){
					$p_map_ivs.='('.(int) $md['ID'].','.(int) $md['itemid'].'),';
				}
			}
			if($p_map_ivs!=''){
				$p_map_ivs = substr($p_map_ivs,0,-1);
				$p_map_insert_q = "INSERT INTO {$wpdb->prefix}mw_wc_qbo_sync_product_pairs (wc_product_id,quickbook_product_id) VALUES {$p_map_ivs}";
				$wpdb->query($p_map_insert_q);
			}
		}
		return $count;
	}
	
	/**
	 * Update Vendor Into Quickbooks Online.
	 *
	 * @since    1.4.5 - 6
	 * Last Updated: 2018-05-04
	 */
	
	public function UpdateVendor($vendor_data){
		$manual = $this->get_array_isset($vendor_data,'manual',false);
		
		if($manual){
			$this->set_session_val('sync_window_push_manual_update',true);
		}
		
		if(!$this->is_connected()){
			return false;
		}
		
		if(is_array($vendor_data) && !empty($vendor_data)){
			$wc_customerid = $this->get_array_isset($vendor_data,'wc_customerid',0);
			if($this->if_sync_vendor($wc_customerid)){
				$supplier_data = $this->get_atum_supplier_dtls_from_wc_vendor_usr_id($wc_customerid,true);
				if(is_array($supplier_data) && !empty($supplier_data)){
					$vendor = $this->if_qbo_vendor_exists($vendor_data,true,true);					
					if(!$vendor){
						$this->save_log('Update Vendor Error #'.$wc_customerid,'QuickBooks Vendor Not Found.','Vendor',0);
						return false;
					}
					$name_replace_chars = array(':','\t','\n');
					
					/*
					$_default_settings_location = $this->get_array_isset($supplier_data,'_default_settings_location','',true);
					$sv_fln_d = $this->get_vendor_fln_from_sup_df_loc($_default_settings_location);
					$sv_fname = $this->get_array_isset($sv_fln_d,'sv_fname','',true);
					$sv_lname = $this->get_array_isset($sv_fln_d,'sv_lname','',true);
					
					if(empty($sv_fname) || empty($sv_lname)){
						return false;
					}
					*/
					
					$vendorService = new QuickBooks_IPP_Service_Vendor();
					$Context = $this->Context;
					$realm = $this->realm;					
					
					$fax = $this->get_array_isset($vendor_data,'fax','',true,21);
					$mobile_phone = $this->get_array_isset($vendor_data,'mobile_phone','',true,21);
					$work_phone = $this->get_array_isset($vendor_data,'work_phone','',true,21);
					$alternative_phone = $this->get_array_isset($vendor_data,'alternative_phone','',true,21);					
					$website = $this->get_array_isset($vendor_data,'website','',true);
					
					$billing_rate = $this->get_array_isset($vendor_data,'billing_rate','',true);
					$terms = $this->get_array_isset($vendor_data,'terms','',true);
					$account_number = $this->get_array_isset($vendor_data,'account_number','',true);
					$business_id_number = $this->get_array_isset($vendor_data,'business_id_number','',true);
					$track_payments = $this->get_array_isset($vendor_data,'track_payments','',true);
					$track_payments = ($track_payments==1)?true:false;					
					
					
					$firstname = $this->get_array_isset($vendor_data,'firstname','',true,25,false,$name_replace_chars);
					$lastname = $this->get_array_isset($vendor_data,'lastname','',true,25,false,$name_replace_chars);					
					
					/*
					$firstname = $sv_fname;
					$lastname = $sv_lname;
					*/
					
					$company = $this->get_array_isset($vendor_data,'company','',true,50,false,$name_replace_chars);
					$display_name = $this->get_array_isset($vendor_data,'display_name','',true,100,false,$name_replace_chars);
					
					$middlename = '';

					//$phone = $this->get_array_isset($vendor_data,'billing_phone','',true,21);
					$phone = $work_phone;
					
					$email = $this->get_array_isset($vendor_data,'email','',true);


					$currency = $this->get_array_isset($vendor_data,'currency','',true);

					$note = $this->get_array_isset($vendor_data,'note','',true);
					
					$vendor->setGivenName($firstname);
					$vendor->setFamilyName($lastname);
					
					$vendor->setCompanyName($company);
					$vendor->setDisplayName($display_name);
					
					/*
					$primaryEmailAddr = new QuickBooks_IPP_Object_PrimaryEmailAddr();
					$primaryEmailAddr->setAddress($email);
					$vendor->setPrimaryEmailAddr($primaryEmailAddr);
					*/					
					
					if($phone!=''){
						$PrimaryPhone = new QuickBooks_IPP_Object_PrimaryPhone();
						$PrimaryPhone->setFreeFormNumber($phone);
						$vendor->setPrimaryPhone($PrimaryPhone);
					}
					
					//
					if($fax!=''){
						$Fax = new QuickBooks_IPP_Object_Fax();
						$Fax->setFreeFormNumber($fax);
						$vendor->setFax($Fax);
					}
					
					if($mobile_phone!=''){
						$Mobile = new QuickBooks_IPP_Object_Mobile();
						$Mobile->setFreeFormNumber($mobile_phone);
						$vendor->setMobile($Mobile);
					}
					
					if($alternative_phone!=''){
						$AlternatePhone = new QuickBooks_IPP_Object_AlternatePhone();
						$AlternatePhone->setFreeFormNumber($alternative_phone);
						$vendor->setAlternatePhone($AlternatePhone);
					}
					
					if($website!=''){
						$WebAddr = new QuickBooks_IPP_Object_WebAddr();
						$WebAddr->setURI($website);
						$vendor->setWebAddr($WebAddr);
					}
					
					$vendor->setAcctNum($account_number);					
					$vendor->setTaxIdentifier($business_id_number);
					$vendor->setVendor1099($track_payments);
					
					if(!empty($terms)){
						$qbo_term_list = $this->get_term_list_array();
						if(is_array($qbo_term_list) && !empty($qbo_term_list)){
							$qbo_term_ref = array_search($terms,$qbo_term_list);
							if(!empty($qbo_term_ref)){
								$vendor->setTermRef($qbo_term_ref);
							}
						}
					}
					
					if($note!=''){
						//$vendor->setNotes($note);
					}

					if($currency!='' && $this->get_qbo_company_setting('is_m_currency')){
						 $vendor->setCurrencyRef("{-$currency}");
					}
					
					//AcctNum,Vendor1099,TermRef,TaxIdentifier

					$address = $this->get_array_isset($vendor_data,'billing_address_1','',true);
					if($address!=''){
						$BillAddr = new QuickBooks_IPP_Object_BillAddr();
						$BillAddr->setLine1($address);

						$BillAddr->setLine2($this->get_array_isset($vendor_data,'billing_address_2','',true));

						$BillAddr->setCity($this->get_array_isset($vendor_data,'billing_city','',true));

						$country = $this->get_array_isset($vendor_data,'billing_country','',true);
						$country = $this->get_country_name_from_code($country);

						$BillAddr->setCountry($country);

						$BillAddr->setCountrySubDivisionCode($this->get_array_isset($vendor_data,'billing_state','',true));

						$BillAddr->setPostalCode($this->get_array_isset($vendor_data,'billing_postcode','',true));
						$vendor->setBillAddr($BillAddr);
					}
					
					//
					$shipping_address = $this->get_array_isset($vendor_data,'shipping_address_1','',true);
					if($shipping_address!=''){
						$ShipAddr = new QuickBooks_IPP_Object_ShipAddr();
						$ShipAddr->setLine1($shipping_address);

						$ShipAddr->setLine2($this->get_array_isset($vendor_data,'shipping_address_2','',true));

						$ShipAddr->setCity($this->get_array_isset($vendor_data,'shipping_city','',true));

						$country = $this->get_array_isset($vendor_data,'shipping_country','',true);
						$country = $this->get_country_name_from_code($country);
						$ShipAddr->setCountry($country);

						$ShipAddr->setCountrySubDivisionCode($this->get_array_isset($vendor_data,'shipping_state','',true));

						$ShipAddr->setPostalCode($this->get_array_isset($vendor_data,'shipping_postcode','',true));
						$vendor->setShipAddr($ShipAddr);
					}

					$log_title = "";
					$log_details = "";
					$log_status = 0;
					
					/*
					$this->_p($vendor_data);
					$this->_p($vendor);
					return false;
					*/
					
					if ($resp = $vendorService->update($Context, $realm, $vendor->getId(), $vendor)){
						$qbo_vendorid = $this->qbo_clear_braces($vendor->getId());
						$log_title.="Update Vendor #$wc_customerid\n";
						$log_details.="Vendor #$wc_customerid has been updated, Quickbooks Vendor ID is #$qbo_vendorid";
						$log_status = 1;
						$this->save_log($log_title,$log_details,'Vendor',$log_status,true,'Update');
						$this->add_qbo_item_obj_into_log_file('Vendor Update',$vendor_data,$vendor,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
						$this->save_qbo_customer_local($qbo_vendorid,$firstname,$lastname,$middlename,$company,$display_name,$email);

						return $qbo_vendorid;
					}else{
						$res_err = $vendorService->lastError($Context);
						$log_title.="Update Vendor Error #$wc_customerid\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Vendor',$log_status,true,'Update');
						$this->add_qbo_item_obj_into_log_file('Vendor Update',$vendor_data,$vendor,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
					}
					
				}
			}
		}
	}
	
	/**
	 * Update Customer Into Quickbooks Online.
	 *
	 * @since    1.0.1
	 * Last Updated: 2017-02-20
	 */

	public function UpdateCustomer($customer_data){
		//$this->include_sync_functions('UpdateCustomer');
		$fn_name = 'UpdateCustomer';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function get_admin_user_dropdown_list($selected=''){
		$user_list = get_users(array('role'=>'Administrator'));
		$u_arr = array();
		if(is_array($user_list) && !empty($user_list)){
			foreach($user_list as $ul){
				if(isset($ul->data->user_email) && $ul->data->user_email!=''){
					$u_arr[$ul->ID] = $ul->display_name.' ('.$ul->data->user_email.')';
				}
			}
			$this->only_option($selected,$u_arr);
		}
	}

	public function get_admin_email_by_id($id){
		$id = (int) $id;
		if(!$id){return '';}

		$user_list = get_users(array('role'=>'Administrator'));
		$u_arr = array();
		if(is_array($user_list) && !empty($user_list)){
			foreach($user_list as $ul){
				if(isset($ul->data->user_email) && $ul->data->user_email!=''){
					if($id && $ul->ID==$id){
						return $ul->data->user_email;
					}
				}
			}
		}
	}



	public function check_qbo_customer_by_display_name($display_name){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$customerService = new QuickBooks_IPP_Service_Customer();
			$customerCheck = $customerService->query($Context,$realm, "SELECT Id FROM Customer WHERE DisplayName = '{$display_name}' ");
			if($customerCheck && !empty($customerCheck)){
				return true;
			}
		}
		return false;
	}
	
	public function check_qbo_vendor_by_display_name($display_name){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$vendorService = new QuickBooks_IPP_Service_Vendor();
			$vendorCheck = $vendorService->query($Context,$realm, "SELECT Id FROM Vendor WHERE DisplayName = '{$display_name}' ");
			if($vendorCheck && !empty($vendorCheck)){
				return true;
			}
		}
		return false;
	}
	
	public function get_wc_customer_currency($wc_cus_id){
		$wc_cus_id = (int) $wc_cus_id;
		if($wc_cus_id){
			global $wpdb;
			$om = $this->get_row($wpdb->prepare("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_customer_user' AND `meta_value` = %s LIMIT 0,1 ",$wc_cus_id));
			if(is_array($om) && !empty($om)){
				$order_id = (int) $om['post_id'];
				if($order_id){
					$om = $this->get_row($wpdb->prepare("SELECT `meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_order_currency' AND `post_id` = %d LIMIT 0,1 ",$order_id));
					if(is_array($om) && !empty($om)){
						return $om['meta_value'];
					}
				}
			}
		}
	}
	
	public function get_vendor_fln_from_sup_df_loc($loc,$sdl='_'){
		$f_name='';$l_name='';
		$loc = trim($loc);
		if($loc!='' &&  strpos($loc, $sdl) !== false){
			$loc_a = @explode($sdl,$loc);
			if(is_array($loc_a) && !empty($loc_a)){
				$f_name = $loc_a[0];
				unset($loc_a[0]);
				if(is_array($loc_a) && !empty($loc_a)){
					$l_name = implode(' ',$loc_a);
				}
			}
		}
		return array('sv_fname'=>$f_name,'sv_lname'=>$l_name);
	}
	
	/**
	 * Add Vendor Into Quickbooks Online.
	 *
	 * @since    1.4.5 - 6
	 * Last Updated: 2018-05-04
	 */
	
	public function AddVendor($vendor_data){
		//$this->include_sync_functions('AddVendor');
		$fn_name = 'AddVendor';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	/**/
	public function AddCustomer($customer_data){
		//$this->include_sync_functions('AddCustomer');
		$fn_name = 'AddCustomer';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function save_customer_map($wc_customerid,$qbo_customerid){
		$wc_customerid = intval($wc_customerid);
		$qbo_customerid = intval($qbo_customerid);
		if($wc_customerid && $qbo_customerid){
			global $wpdb;
			$save_data = array();
			$save_data['qbo_customerid'] = $qbo_customerid;
			$table = $wpdb->prefix.'mw_wc_qbo_sync_customer_pairs';

			if($this->get_field_by_val($table,'id','wc_customerid',$wc_customerid)){
				$wpdb->update($table,$save_data,array('wc_customerid'=>$wc_customerid),'',array('%d'));
			}else{
				$save_data['wc_customerid'] = $wc_customerid;
				$wpdb->insert($table, $save_data);
			}
		}
	}
	
	public function save_vendor_map($wc_customerid,$qbo_vendorid){
		$wc_customerid = intval($wc_customerid);
		$qbo_vendorid = intval($qbo_vendorid);
		if($wc_customerid && $qbo_vendorid){
			global $wpdb;
			$save_data = array();
			$save_data['qbo_vendorid'] = $qbo_vendorid;
			$table = $wpdb->prefix.'mw_wc_qbo_sync_vendor_pairs';

			if($this->get_field_by_val($table,'id','wc_customerid',$wc_customerid)){
				$wpdb->update($table,$save_data,array('wc_customerid'=>$wc_customerid),'',array('%d'));
			}else{
				$save_data['wc_customerid'] = $wc_customerid;
				$wpdb->insert($table, $save_data);
			}
		}
	}
	
	//
	public function save_item_map($wc_product_id,$quickbook_product_id,$pull=false,$is_variation=false){
		$wc_product_id = intval($wc_product_id);
		$quickbook_product_id = intval($quickbook_product_id);
		if($wc_product_id && $quickbook_product_id){
			global $wpdb;
			$save_data = array();
			$table = $wpdb->prefix.'mw_wc_qbo_sync_product_pairs';
			$w_p_f = 'wc_product_id';
			if($is_variation){
				$table = $wpdb->prefix.'mw_wc_qbo_sync_variation_pairs';
				$w_p_f = 'wc_variation_id';
			}

			if(!$pull){
				$save_data['quickbook_product_id'] = $quickbook_product_id;
				if($this->get_field_by_val($table,'id',$w_p_f,$wc_product_id)){
					$wpdb->update($table,$save_data,array($w_p_f=>$wc_product_id),'',array('%d'));
				}else{
					$save_data[$w_p_f] = $wc_product_id;
					$wpdb->insert($table, $save_data);
				}
			}else{
				$save_data[$w_p_f] = $wc_product_id;
				if($this->get_field_by_val($table,'id','quickbook_product_id',$quickbook_product_id)){
					$wpdb->update($table,$save_data,array('quickbook_product_id'=>$quickbook_product_id),'',array('%d'));
				}else{
					$save_data['quickbook_product_id'] = $quickbook_product_id;
					$wpdb->insert($table, $save_data);
				}
			}
		}
	}
	
	public function save_qbo_customer_local($qbo_customerid,$first,$last,$middle,$company,$dname,$email){
		$qbo_customerid = intval($qbo_customerid);
		if($qbo_customerid){
			global $wpdb;
			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers';
			$save_data = array();

			$save_data['first'] = $first;
			$save_data['last'] = $last;
			$save_data['middle'] = $middle;

			$save_data['company'] = $company;
			$save_data['dname'] = $dname;
			$save_data['email'] = $email;

			$save_data = array_map(array($this, 'trim_add_slash'), $save_data);

			if($this->get_field_by_val($table,'id','qbo_customerid',$qbo_customerid)){
				$wpdb->update($table,$save_data,array('qbo_customerid'=>$qbo_customerid),'',array('%d'));
				return $qbo_customerid;
			}else{
				$save_data['qbo_customerid'] = $qbo_customerid;
				$wpdb->insert($table, $save_data);
				$insert_id = $wpdb->insert_id;
				return $insert_id;
			}

		}
	}
	
	public function save_qbo_vendor_local($qbo_vendorid,$first,$last,$middle,$company,$dname,$email,$pocname=''){
		$qbo_customerid = intval($qbo_customerid);
		if($qbo_customerid){
			global $wpdb;
			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors';
			$save_data = array();

			$save_data['first'] = $first;
			$save_data['last'] = $last;
			$save_data['middle'] = $middle;

			$save_data['company'] = $company;
			$save_data['dname'] = $dname;
			$save_data['email'] = $email;
			
			if($pocname!=''){
				$save_data['pocname'] = $pocname;
			}			
			
			$save_data = array_map(array($this, 'trim_add_slash'), $save_data);

			if($this->get_field_by_val($table,'id','qbo_vendorid',$qbo_vendorid)){
				$wpdb->update($table,$save_data,array('qbo_vendorid'=>$qbo_vendorid),'',array('%d'));
				return $qbo_vendorid;
			}else{
				$save_data['qbo_vendorid'] = $qbo_vendorid;
				$wpdb->insert($table, $save_data);
				$insert_id = $wpdb->insert_id;
				return $insert_id;
			}

		}
	}

	//
	public function save_qbo_item_local($itemid,$name,$sku,$product_type){
		$itemid = intval($itemid);
		if($itemid){
			global $wpdb;
			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_items';
			$save_data = array();

			$save_data['name'] = $name;
			$save_data['sku'] = $sku;
			$save_data['product_type'] = $product_type;

			$save_data = array_map(array($this, 'trim_add_slash'), $save_data);

			if($this->get_field_by_val($table,'ID','itemid',$itemid)){
				$wpdb->update($table,$save_data,array('itemid'=>$itemid),'',array('%d'));
				return $itemid;
			}else{
				$save_data['itemid'] = $itemid;
				$wpdb->insert($table, $save_data);
				$insert_id = $wpdb->insert_id;
				return $insert_id;
			}

		}
	}

	public function trim_add_slash($str){
		return addslashes(trim($str));
	}
	
	public function get_hd_ldys_lmt(){
		if($this->is_plg_lc_p_l()){
			return 7;
		}
		
		if($this->option_checked('mw_wc_qbo_sync_trial_license')){
			return 7;
		}
		
		return 30;
	}
	
	public function is_pl_res_tml(){
		/*
		//return true;
		if($this->option_checked('mw_wc_qbo_sync_trial_license')){
			return true;
		}
		
		if($this->option_checked('mw_wc_qbo_sync_monthly_license')){
			return true;
		}
		
		if($this->is_plg_lc_p_l()){
			return true;
		}
		*/
		
		return false;
	}
	
	public function get_slmt_hstry_msg(){
		$mag = __( '<h2>Need to sync more than '.$this->get_hd_ldys_lmt().' days of history? <a href="'.$this->get_quickbooks_connection_dashboard_url().'/clientarea.php?action=services">Upgrade</a> to an annual plan!</h2>', 'mw_wc_qbo_sync' );
		if($this->is_plg_lc_p_l()){
			$mag = __( '<h2>Need to sync more than '.$this->get_hd_ldys_lmt().' days of history? <a href="'.$this->get_quickbooks_connection_dashboard_url().'/clientarea.php?action=services">Upgrade</a> to a paid plan!</h2>', 'mw_wc_qbo_sync' );
		}
		
		return $mag;
	}
	
	//27-04-2017
	public function set_qbo_company_info(){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$CompanyInfoService = new QuickBooks_IPP_Service_CompanyInfo();
			$Info = $CompanyInfoService->get($Context, $realm);

			$this->qbo_company_info = $Info;
		}
	}
	public function get_qbo_company_info($key='country',$debug=false,$frc=true){
		$return = '';
		if($key!='' && $this->is_connected()){

			$Info = $this->qbo_company_info;
			//$this->_p($key);
			if($debug){
				//$this->_p($Info);
			}

			if(!$Info){return;}

			switch ($key) {
				case 'name':
					if($Info->countCompanyName()){
						$return = $Info->getCompanyName();
					}
					break;

				case 'type':
					$return = $Info->getXPath('//CompanyInfo/NameValue[Name="CompanyType"]/Value');
					break;

				case 'country':
					if($Info->countCountry()){
						$return = $Info->getCountry();
					}
					break;
				
				case 'is_sku_enabled':
					/*
					$OfferingSku = $Info->getXPath('//CompanyInfo/NameValue[Name="OfferingSku"]/Value');
					if($OfferingSku=='QuickBooks Online Plus'){
						$return = true;
					}else{
						$return = false;
					}
					
					if($frc){
						if($this->option_checked('mw_wc_qbo_sync_qb_ed_invt_s_frc')){
							$return = true;
						}
					}
					*/
					
					$return = $this->get_qbo_company_setting('QuantityOnHand');
					break;

				case 'is_category_enabled':
					$ItemCategoriesFeature = $Info->getXPath('//CompanyInfo/NameValue[Name="ItemCategoriesFeature"]/Value');
					if($ItemCategoriesFeature=='true'){
						$return = true;
					}else{
						$return = false;
					}
					break;

				case "OfferingSku":
					$return = $Info->getXPath('//CompanyInfo/NameValue[Name="OfferingSku"]/Value');
					break;

				case 'AssignedTime':
					$return = $Info->getXPath('//CompanyInfo/NameValue[Name="AssignedTime"]/Value');
					break;

				case 'SubscriptionStatus':
					$return = $Info->getXPath('//CompanyInfo/NameValue[Name="SubscriptionStatus"]/Value');
					break;

				case 'FirstTxnDate':
					$return = $Info->getXPath('//CompanyInfo/NameValue[Name="FirstTxnDate"]/Value');
					break;

				case 'PayrollFeature':
					$return = $Info->getXPath('//CompanyInfo/NameValue[Name="PayrollFeature"]/Value');
					break;

				case 'AccountantFeature':
					$return = $Info->getXPath('//CompanyInfo/NameValue[Name="AccountantFeature"]/Value');
					break;

				default:
					# code...
					break;
			}


		}
		return $return;
	}


	public function get_qbo_company_preferences(){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$q_prf = new QuickBooks_IPP_Service_Preferences();
			$prf = $q_prf->get($Context, $realm);
			$this->qbo_company_preferences = $prf;
		}
	}
	
	/*Avl QBO CF Map Fields*/
	public function get_qbo_avl_cf_map_fields($not_actual_field=false){
		$qbo_cf_arr = $this->get_qbo_company_setting('sf_str_type_custom_field_list');
		//$this->_p($qbo_cf_arr);
		$qbo_avl_cf_list = array();
		
		$if_cfm_active = false;
		if($this->is_only_plugin_active('myworks-qbo-sync-custom-field-mapping') && $this->check_sh_cfm_hash()){
			$if_cfm_active = true;
		}
		if(!$if_cfm_active){
			return $qbo_avl_cf_list;
		}
		
		if(!$not_actual_field){
			$qbo_avl_cf_list['ShipDate'] = 'ShipDate';
			$qbo_avl_cf_list['CustomerMemo'] = 'CustomerMemo (Message on Invoice)';		
			$qbo_avl_cf_list['ShipMethodRef'] = 'ShipMethodRef'; //ship_method
			$qbo_avl_cf_list['TrackingNum'] = 'TrackingNum';
			
			//
			$qbo_avl_cf_list['ShipFromAddr'] = 'ShipFrom Address';
			
			$qbo_avl_cf_list['PrivateNote'] = 'PrivateNote (Message on Statement)';
			$qbo_avl_cf_list['TxnDate'] = 'TxnDate';
			$qbo_avl_cf_list['DueDate'] = 'DueDate';
		}
		
		$qbo_avl_cf_list['bill_addr'] = 'BillAddr';
		$qbo_avl_cf_list['ship_addr'] = 'ShipAddr';
		$qbo_avl_cf_list['bill_addr,ship_addr'] = 'BillAddr and ShipAddr';
		
		//
		$qbo_avl_cf_list['E_B_L_D'] = 'Extra Line Description';
		
		if($this->is_only_plugin_active('order-delivery-date','order_delivery_date')){
			$qbo_avl_cf_list['ServiceDate'] = 'Line Item Service Date';
		}	
		
		#New
		$qbo_avl_cf_list['O_S_Attachments'] = 'Order Sync -> Attachments';
		
		if(!$not_actual_field && is_array($qbo_cf_arr) && !empty($qbo_cf_arr)){
			$qbo_avl_cf_list = array_merge($qbo_avl_cf_list,$qbo_cf_arr);
		}
		return $qbo_avl_cf_list;
	}
	
	/*Avl WC CF Map Fields*/
	public function get_wc_static_billing_order_fields(){
		$wsbof = array();
		$wsbof[] = '_billing_first_name';
		$wsbof[] = '_billing_last_name';
		$wsbof[] = '_billing_company';
		$wsbof[] = '_billing_address_1';
		$wsbof[] = '_billing_address_2';
		$wsbof[] = '_billing_city';
		$wsbof[] = '_billing_state';
		$wsbof[] = '_billing_postcode';
		$wsbof[] = '_billing_country';
		$wsbof[] = '_billing_email';
		$wsbof[] = '_billing_phone';
		return $wsbof;
		
	}
	
	public function get_wc_static_shipping_order_fields(){
		$wscof = array();
		$wscof[] = '_shipping_first_name';
		$wscof[] = '_shipping_last_name';
		$wscof[] = '_shipping_company';
		$wscof[] = '_shipping_address_1';
		$wscof[] = '_shipping_address_2';
		$wscof[] = '_shipping_city';
		$wscof[] = '_shipping_state';
		$wscof[] = '_shipping_postcode';
		$wscof[] = '_shipping_country';
		
		//$wscof[] = '_shipping_phone';
		return $wscof;
		
	}
	
	public function get_wc_avl_cf_map_fields_by_group($not_actual_field=false){
		$wc_avl_cf_list = array();
		
		$if_cfm_active = false;
		if($this->is_only_plugin_active('myworks-qbo-sync-custom-field-mapping') && $this->check_sh_cfm_hash()){
			$if_cfm_active = true;
		}
		if(!$if_cfm_active){
			return $wc_avl_cf_list;
		}
		
		//WooCommerce Admin Custom Order Fields
		if($this->is_only_plugin_active('woocommerce-admin-custom-order-fields')){
			$wacof_fl = get_option('wc_admin_custom_order_fields');
			if(is_array($wacof_fl) && !empty($wacof_fl)){
				$tfa = array();$tfa_fl = array();
				$tfa['title'] = 'WooCommerce Admin Custom Order Fields';
				foreach($wacof_fl as $aof_k => $aof){
					$tfa_fl['_wc_acof_'.$aof_k] = $aof['label'].' ('.$aof['type'].')';
				}
				$tfa['fields'] = $tfa_fl;
				$wc_avl_cf_list[] = $tfa;
			}
		}
		
		$is_bdofa = false;$is_sdofa = false;
		//WooCommerce Checkout Field Editor Pro
		if($this->is_only_plugin_active('woocommerce-checkout-field-editor-pro')){
			$thwcfe_sections = get_option('thwcfe_sections');			
			if(is_array($thwcfe_sections) && !empty($thwcfe_sections)){
				$tfa = array();$tfa_fl = array();
				$tfa['title'] = 'WooCommerce Checkout Field Editor Pro';
				$tfa['fields'] = $tfa_fl;
				$wc_avl_cf_list[] = $tfa;
					
				//Billing
				if(isset($thwcfe_sections['billing']) && !empty($thwcfe_sections['billing']) && isset($thwcfe_sections['billing']->fields) && !empty($thwcfe_sections['billing']->fields)){
					$tfa = array();$tfa_fl = array();
					$tfa['title'] = 'Billing';
					$tfa['f_type'] = 'billing';
					$tfa['sub'] = true;
					$thwcfe_sections_add = $thwcfe_sections['billing']->fields;
					//$this->_p($thwcfe_sections_add);
					foreach($thwcfe_sections_add as $tsa_k => $tsa_v){
						if(in_array('_'.$tsa_k,$this->get_wc_static_billing_order_fields())){
							$tfa_fl['_'.$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
						}else{
							$tfa_fl[$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
						}						
					}					
					$tfa['fields'] = $tfa_fl;
					$wc_avl_cf_list[] = $tfa;
					$is_bdofa = true;
				}
				
				//Shipping
				if(isset($thwcfe_sections['shipping']) && !empty($thwcfe_sections['shipping']) && isset($thwcfe_sections['shipping']->fields) && !empty($thwcfe_sections['shipping']->fields)){
					$tfa = array();$tfa_fl = array();
					$tfa['title'] = 'Shipping';
					$tfa['f_type'] = 'shipping';
					$tfa['sub'] = true;
					$thwcfe_sections_add = $thwcfe_sections['shipping']->fields;
					//$this->_p($thwcfe_sections_add);
					foreach($thwcfe_sections_add as $tsa_k => $tsa_v){
						if(in_array('_'.$tsa_k,$this->get_wc_static_shipping_order_fields())){
							$tfa_fl['_'.$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
						}else{
							$tfa_fl[$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
						}						
					}
					$tfa['fields'] = $tfa_fl;
					$wc_avl_cf_list[] = $tfa;
					$is_sdofa = true;
				}
				
				//Additional
				if(isset($thwcfe_sections['additional']) && !empty($thwcfe_sections['additional']) && isset($thwcfe_sections['additional']->fields) && !empty($thwcfe_sections['additional']->fields)){
					$tfa = array();$tfa_fl = array();
					$tfa['title'] = 'Additional';
					$tfa['f_type'] = 'additional';
					$tfa['sub'] = true;
					$thwcfe_sections_add = $thwcfe_sections['additional']->fields;
					//$this->_p($thwcfe_sections_add);
					foreach($thwcfe_sections_add as $tsa_k => $tsa_v){
						$tfa_fl[$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
					}
					$tfa['fields'] = $tfa_fl;
					$wc_avl_cf_list[] = $tfa;
				}
				
			}
		}
		
		$is_bdofa_wcfe = false;
		//WooCommerce Checkout Field Editor
		if($this->is_plugin_active('woocommerce-checkout-field-editor')){
			$tfa = array();$tfa_fl = array();
			$tfa['title'] = 'WooCommerce Checkout Field Editor';
			$tfa['fields'] = $tfa_fl;
			$wc_avl_cf_list[] = $tfa;
			
			//Billing
			$wc_fields_billing = get_option('wc_fields_billing');
			if(empty($wc_fields_billing)){
				$wc_fields_billing = $this->get_wc_static_billing_order_fields();
			}
			//$this->_p($wc_fields_billing);
			if(is_array($wc_fields_billing) && !empty($wc_fields_billing)){
				$tfa = array();$tfa_fl = array();
				$tfa['title'] = 'Billing';
				$tfa['f_type'] = 'billing';
				$tfa['sub'] = true;
				$wcfe_bfa = false;
				foreach($wc_fields_billing as $wfb_k => $wfb_v){
					if(in_array('_'.$wfb_k,$this->get_wc_static_billing_order_fields())){
						if(!$is_bdofa){
							$is_bdofa_wcfe = true;
							$tfa_fl['_'.$wfb_k] = $wfb_k.'('.$wfb_v['type'].')';
							$wcfe_bfa = true;
						}
					}else{
						$wcfe_bfa = true;
						$tfa_fl[$wfb_k] = $wfb_k.'('.$wfb_v['type'].')';
					}
				}
				$tfa['fields'] = $tfa_fl;
				if($wcfe_bfa){
					$wc_avl_cf_list[] = $tfa;
				}				
			}
			
			//Shipping
			$wc_fields_shipping = get_option('wc_fields_shipping');
			if(empty($wc_fields_shipping)){
				//$wc_fields_shipping = $this->get_wc_static_shipping_order_fields();
			}
			//$this->_p($wc_fields_shipping);
			if(is_array($wc_fields_shipping) && !empty($wc_fields_shipping)){
				$tfa = array();$tfa_fl = array();
				$tfa['title'] = 'Shipping';
				$tfa['f_type'] = 'shipping';
				$tfa['sub'] = true;
				$wcfe_sfa = false;
				foreach($wc_fields_shipping as $wfs_k => $wfs_v){
					if(in_array('_'.$wfs_k,$this->get_wc_static_shipping_order_fields())){
						if(!$is_sdofa){
							$tfa_fl['_'.$wfs_k] = $wfs_k.'('.$wfs_v['type'].')';
							$wcfe_sfa = true;
						}
					}else{
						$wcfe_sfa = true;
						$tfa_fl[$wfs_k] = $wfs_k.'('.$wfs_v['type'].')';
					}
				}
				$tfa['fields'] = $tfa_fl;
				if($wcfe_sfa){
					$wc_avl_cf_list[] = $tfa;
				}				
			}
			
			//Additional
			$wc_fields_additional = get_option('wc_fields_additional');
			
			if(is_array($wc_fields_additional) && !empty($wc_fields_additional)){
				$tfa = array();$tfa_fl = array();
				$tfa['title'] = 'Additional';
				$tfa['f_type'] = 'additional';
				$tfa['sub'] = true;
				$wcfe_afa = false;
				foreach($wc_fields_additional as $wfa_k => $wfa_v){
					$tfa_fl[$wfa_k] = $wfa_k.'('.$wfa_v['type'].')';
					$wcfe_afa = true;
				}
				$tfa['fields'] = $tfa_fl;
				if($wcfe_afa){
					$wc_avl_cf_list[] = $tfa;
				}				
			}
		}
		
		//Others
		$tfa = array();$tfa_fl = array();
		$tfa['title'] = 'Others';
		
		#New
		$tfa_fl['wc_inv_id'] = 'WooCommerce Order ID';
		$tfa_fl['wc_inv_num'] = 'WooCommerce Order Number';
		
		$tfa_fl['wc_order_shipping_details'] = 'Order Shipping Address Details';
		$tfa_fl['wc_order_shipping_method_name'] = 'Order Shipping Method Name';
		$tfa_fl['wc_order_phone_number'] = 'Order Phone Number';
		if(!$is_bdofa && !$is_bdofa_wcfe){
			//$tfa_fl['_billing_phone'] = 'billing_phone';
		}else{
			if(isset($tfa_fl['_billing_phone'])){
				unset($tfa_fl['_billing_phone']);
			}
		}
		
		$tfa_fl['wopn_for_ba_sa'] = 'Billing Phone In BillAddr, ShipAddr';
		if($this->is_only_plugin_active('order-delivery-date','order_delivery_date')){
			$tfa_fl['ordd_as_li_sd'] = 'Order Due Date In Line Item Service Date';
		}		
		
		/**/
		$tfa_fl['wc_customer_username_field_val'] = 'Customer Username Field Value';
		//$tfa_fl['wc_order_line_item_meta_1st'] = 'Order Line Item Meta (1st Line)';
		$tfa_fl['wc_order_b_f_l_name'] = 'Order First + Last Name';
		
		$tfa_fl['Order_Comments_Notes'] = 'Order Comments';
		$tfa_fl['Order_All_Coupons'] = 'Order Coupons';
		
		$tfa['fields'] = $tfa_fl;
		$wc_avl_cf_list[] = $tfa;
		
		return $wc_avl_cf_list;
	}
	
	public function get_wc_avl_cf_map_fields($not_actual_field=false){
		$wc_avl_cf_list = array();
		
		$if_cfm_active = false;
		if($this->is_only_plugin_active('myworks-qbo-sync-custom-field-mapping') && $this->check_sh_cfm_hash()){
			$if_cfm_active = true;
		}
		if(!$if_cfm_active){
			return $wc_avl_cf_list;
		}
		
		if(!$not_actual_field){
			//WooCommerce Admin Custom Order Fields
			if($this->is_only_plugin_active('woocommerce-admin-custom-order-fields')){
				$wacof_fl = get_option('wc_admin_custom_order_fields');
				if(is_array($wacof_fl) && !empty($wacof_fl)){
					foreach($wacof_fl as $aof_k => $aof){
						$wc_avl_cf_list['_wc_acof_'.$aof_k] = $aof['label'].' ('.$aof['type'].')';
					}
				}
			}
			
			$is_bdofa = false;$is_sdofa = false;
			//WooCommerce Checkout Field Editor Pro
			if($this->is_only_plugin_active('woocommerce-checkout-field-editor-pro')){
				$thwcfe_sections = get_option('thwcfe_sections');
				if(is_array($thwcfe_sections) && !empty($thwcfe_sections)){
					//Billing
					if(isset($thwcfe_sections['billing']) && !empty($thwcfe_sections['billing']) && isset($thwcfe_sections['billing']->fields) && !empty($thwcfe_sections['billing']->fields)){						
						$thwcfe_sections_add = $thwcfe_sections['billing']->fields;
						//$this->_p($thwcfe_sections_add);
						foreach($thwcfe_sections_add as $tsa_k => $tsa_v){
							if(in_array('_'.$tsa_k,$this->get_wc_static_billing_order_fields())){
								$wc_avl_cf_list['_'.$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
							}else{
								$wc_avl_cf_list[$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
							}					
						}
						$is_bdofa = true;
					}
					
					//Shipping
					if(isset($thwcfe_sections['shipping']) && !empty($thwcfe_sections['shipping']) && isset($thwcfe_sections['shipping']->fields) && !empty($thwcfe_sections['shipping']->fields)){						
						$thwcfe_sections_add = $thwcfe_sections['shipping']->fields;
						//$this->_p($thwcfe_sections_add);
						foreach($thwcfe_sections_add as $tsa_k => $tsa_v){
							if(in_array('_'.$tsa_k,$this->get_wc_static_shipping_order_fields())){
								$wc_avl_cf_list['_'.$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
							}else{
								$wc_avl_cf_list[$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
							}						
						}						
						$is_sdofa = true;
					}
					
					//Additional
					if(isset($thwcfe_sections['additional']) && !empty($thwcfe_sections['additional']) && isset($thwcfe_sections['additional']->fields) && !empty($thwcfe_sections['additional']->fields)){
						$thwcfe_sections_add = $thwcfe_sections['additional']->fields;
						//$this->_p($thwcfe_sections_add);
						foreach($thwcfe_sections_add as $tsa_k => $tsa_v){
							$wc_avl_cf_list[$tsa_k] =  $tsa_v->name.'('.$tsa_v->type.')';
						}
					}
					
				}
			}
			
			$is_bdofa_wcfe = false;
			//WooCommerce Checkout Field Editor
			if($this->is_plugin_active('woocommerce-checkout-field-editor')){
				//Billing
				$wc_fields_billing = get_option('wc_fields_billing');
				if(is_array($wc_fields_billing) && !empty($wc_fields_billing)){
					foreach($wc_fields_billing as $wfb_k => $wfb_v){
						if(in_array('_'.$wfb_k,$this->get_wc_static_billing_order_fields())){
							if(!$is_bdofa){
								$wc_avl_cf_list['_'.$wfb_k] = $wfb_k.'('.$wfb_v['type'].')';
								$is_bdofa_wcfe = true;
							}
						}else{
							$wcfe_bfa = true;
							$wc_avl_cf_list[$wfb_k] = $wfb_k.'('.$wfb_v['type'].')';
						}
					}					
				}
				
				//Shipping
				$wc_fields_shipping = get_option('wc_fields_shipping');
				if(is_array($wc_fields_shipping) && !empty($wc_fields_shipping)){					
					foreach($wc_fields_shipping as $wfs_k => $wfs_v){
						if(in_array('_'.$wfs_k,$this->get_wc_static_shipping_order_fields())){
							if(!$is_sdofa){
								$wc_avl_cf_list['_'.$wfs_k] = $wfs_k.'('.$wfs_v['type'].')';								
							}
						}else{							
							$wc_avl_cf_list[$wfs_k] = $wfs_k.'('.$wfs_v['type'].')';
						}
					}									
				}
				
				//Additional
				$wc_fields_additional = get_option('wc_fields_additional');
				if(is_array($wc_fields_additional) && !empty($wc_fields_additional)){					
					foreach($wc_fields_additional as $wfa_k => $wfa_v){
						$wc_avl_cf_list[$wfa_k] = $wfa_k.'('.$wfa_v['type'].')';						
					}					
				}
			}			
			
			//WooCommerce Custom Fields
			if($this->is_only_plugin_active('woocommerce-custom-fields')){
				//$wccf_fields = $this->get_compt_checkout_fields();
				//$this->_p($wccf_fields);
			}			
			
		}
		
		#New
		$tfa_fl['wc_inv_id'] = 'WooCommerce Order ID';
		$tfa_fl['wc_inv_num'] = 'WooCommerce Order Number';
		
		$wc_avl_cf_list['wc_order_shipping_details'] = 'Order Shipping Address Details';
		$wc_avl_cf_list['wc_order_shipping_method_name'] = 'Order Shipping Method Name';		
		$wc_avl_cf_list['wc_order_phone_number'] = 'Order Phone Number';
		if(!$is_bdofa && !$is_bdofa_wcfe){
			//$wc_avl_cf_list['_billing_phone'] = 'billing_phone';
		}else{
			if(isset($wc_avl_cf_list['_billing_phone'])){
				unset($wc_avl_cf_list['_billing_phone']);
			}
		}
		
		$wc_avl_cf_list['wopn_for_ba_sa'] = 'Billing Phone In BillAddr, ShipAddr';
		if($this->is_only_plugin_active('order-delivery-date','order_delivery_date')){
			$wc_avl_cf_list['ordd_as_li_sd'] = 'Order Due Date In Line Item Service Date';
		}
		
		/**/
		$wc_avl_cf_list['wc_customer_username_field_val'] = 'Customer Username Field Value';
		//$wc_avl_cf_list['wc_order_line_item_meta_1st'] = 'Order Line Item Meta (1st Line)';
		$wc_avl_cf_list['wc_order_b_f_l_name'] = 'Order First + Last Name';
		
		$wc_avl_cf_list['Order_Comments_Notes'] = 'Order Comments';
		$tfa_fl['Order_All_Coupons'] = 'Order Coupons';
		
		return $wc_avl_cf_list;
	}
	
	/**/
	public function qbo_functionality_after_pugin_activation(){
		//return false;
		$force_run = false;
		if(!$this->option_checked('mw_wc_qbo_sync_qb_func_af_plg_act_run') || $force_run){
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				
				$income_acc_ref = '';
				$asset_acc_ref = '';
				$cogs_acc_ref = '';
				
				$ia_acc_name = 'Inventory Asset';
				$cogs_acc_name = 'Cost of Goods Sold';
				
				if($this->get_qbo_company_info('country') == 'UK' || $this->get_qbo_company_info('country') == 'GB'){
					$ia_acc_name = 'Stock Asset';
					$cogs_acc_name = 'Cost of Sales';
				}
				
				$AccountService = new QuickBooks_IPP_Service_Account();				
				$accounts =	$AccountService->query($Context, $realm, "SELECT * FROM Account WHERE FullyQualifiedName IN('Sales Income','Sales of Product Income','{$ia_acc_name}','{$cogs_acc_name}') ");
				//$this->_p($accounts);
				if($accounts && is_array($accounts) && !empty($accounts)){
					foreach($accounts as $Account){
						if($Account->getFullyQualifiedName() == 'Sales Income' && $Account->getAccountType() == 'Income'){
							$income_acc_ref = $this->qbo_clear_braces($Account->getId());
						}
						
						if(empty($income_acc_ref) && $Account->getFullyQualifiedName() == 'Sales of Product Income' && $Account->getAccountType() == 'Income'){
							$income_acc_ref = $this->qbo_clear_braces($Account->getId());
						}
						
						if($Account->getFullyQualifiedName() == $ia_acc_name && $Account->getAccountType() == 'Other Current Asset'){
							$asset_acc_ref = $this->qbo_clear_braces($Account->getId());
						}
						
						if($Account->getFullyQualifiedName() == $cogs_acc_name && $Account->getAccountType() == 'Cost of Goods Sold'){
							$cogs_acc_ref = $this->qbo_clear_braces($Account->getId());
						}
					}					
				}
				
				if(!empty($income_acc_ref) && empty($this->get_option('mw_wc_qbo_sync_default_qbo_product_account'))){
					update_option('mw_wc_qbo_sync_default_qbo_product_account',$income_acc_ref);
				}
				
				if(!empty($asset_acc_ref) && empty($this->get_option('mw_wc_qbo_sync_default_qbo_asset_account'))){
					update_option('mw_wc_qbo_sync_default_qbo_asset_account',$asset_acc_ref);
				}
				
				if(!empty($cogs_acc_ref) && empty($this->get_option('mw_wc_qbo_sync_default_qbo_expense_account'))){
					update_option('mw_wc_qbo_sync_default_qbo_expense_account',$cogs_acc_ref);
				}
				
				# QuickBooks Non-taxable Rate
				if(empty($this->get_option('mw_wc_qbo_sync_tax_rule')) && !$this->get_qbo_company_setting('is_automated_sales_tax')){
					$TaxCodeService = new QuickBooks_IPP_Service_TaxCode();
					$taxcodes = $TaxCodeService->query($Context, $realm, "SELECT Id , Name FROM TaxCode");
					if($taxcodes && !empty($taxcodes)){
						$qbntc = 0;
						foreach($taxcodes as $TaxCode){
							$tcn = $TaxCode->getName();
							$tc_id = $this->qbo_clear_braces($TaxCode->getId());

							if(!$qbntc){
								if(strtolower($tcn) == strtolower('Out of Scope')){
									$qbntc = $tc_id;
								}
							}

							if($qbntc){
								break;
							}
						}
						
						if(!$qbntc){
							foreach($taxcodes as $TaxCode){
								$tcn = $TaxCode->getName();
								$tc_id = $this->qbo_clear_braces($TaxCode->getId());

								if(!$qbntc){
									if($tcn == 'Exempt'){
										$qbntc = $tc_id;
									}
								}

								if($qbntc){
									break;
								}
							}
						}

						if(!$qbntc){
							foreach($taxcodes as $TaxCode){
								$tcn = $TaxCode->getName();
								$tc_id = $this->qbo_clear_braces($TaxCode->getId());

								if(!$qbntc){
									if($tcn == 'Zero-rated'){
										$qbntc = $tc_id;
									}
								}

								if($qbntc){
									break;
								}
							}
						}

						if($qbntc > 0){
							update_option('mw_wc_qbo_sync_default_qbo_item',$qbntc);
						}
					}
				}
				
				if(empty($this->get_option('mw_wc_qbo_sync_default_qbo_item')) && $income_acc_ref > 0){
					$ItemService = new QuickBooks_IPP_Service_Item();
					$df_itm = $ItemService->query($Context, $realm, "SELECT * FROM Item WHERE Name = 'Default for Unmatched Products' AND Type IN ('NonInventory') ");
					//$this->_p($df_itm);
					
					if(!$df_itm){
						$item = new QuickBooks_IPP_Object_Item();
						$name = 'Default for Unmatched Products';
						$sku = '';
						$type = 'NonInventory';
						
						$item->setName($name);
						$item->setDescription('Default for Unmatched Products (MyWorks Sync)');
						$item->setType($type);
						
						/*
						$item->setUnitPrice(0);
						$item->setTaxable(false);
						$item->setActive(true);
						
						*/
						
						$item->setIncomeAccountRef($income_acc_ref);							
						//$this->_p($item);							
						if ($resp = $ItemService->add($Context, $realm, $item)){
							$qbo_item_id = $this->qbo_clear_braces($resp);
							update_option('mw_wc_qbo_sync_default_qbo_item',$qbo_item_id);
							$this->save_qbo_item_local($qbo_item_id,$name,$sku,$type);
						}else{
							$res_err = $ItemService->lastError($Context);
							//$this->_p($res_err);
						}												
					}else{
						$qbo_item_id = (int) $this->qbo_clear_braces($df_itm[0]->getId());
						update_option('mw_wc_qbo_sync_default_qbo_item',$qbo_item_id);
					}
				}
				
				//
				$customer_count = (int) $this->quick_refresh_qbo_customers();
				$product_count = (int) $this->quick_refresh_qbo_products();
				
				$enable_qb_setting_change = true;
				if($enable_qb_setting_change && (!$this->get_qbo_company_setting('is_discount_allowed') || !$this->get_qbo_company_setting('is_shipping_allowed') || !$this->get_qbo_company_setting('is_deposit_allowed_real'))){
					$prf = $this->qbo_company_preferences;
					//$this->_p($prf);
					if($prf && !empty($prf)){
						$q_prf = new QuickBooks_IPP_Service_Preferences();
						
						$q_prf_new = new QuickBooks_IPP_Object_Preferences();
						$q_prf_new->setId($this->qbo_clear_braces($prf->getId()));
						$q_prf_new->setSyncToken($prf->getSyncToken());
						$q_prf_new->setsparse('true');
						
						$sfp = new QuickBooks_IPP_Object_SalesFormsPrefs();
						
						if(!$this->get_qbo_company_setting('is_discount_allowed')){
							$sfp->setAllowDiscount('true');
						}
						
						if(!$this->get_qbo_company_setting('is_shipping_allowed')){
							$sfp->setAllowShipping('true');
						}
						
						if(!$this->get_qbo_company_setting('is_deposit_allowed_real')){
							$sfp->setAllowDeposit('true');
						}
						
						$q_prf_new->setSalesFormsPrefs($sfp);
						
						//$this->_p($q_prf_new);
						//return false;
						
						if($resp = $q_prf->update($Context, $realm, $prf->getId(), $q_prf_new)){							
							//$this->_p($resp,true);
						}else{
							$res_err = $q_prf->lastError($Context);
							
							/*
							$this->add_qbo_item_obj_into_log_file('Preferences Update',array('Id'=>$prf->getId()),$q_prf_new,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
							*/
							
							/*
							echo 'Error:<br>';
							echo $res_err;
							echo '<br>';							
							
							echo 'Request:<br>';
							echo $this->get_IPP()->lastRequest();
							echo '<br>';
							echo 'Response:<br>';
							echo $this->get_IPP()->lastResponse();
							*/							
						}
					}
				}
				
				update_option('mw_wc_qbo_sync_qb_func_af_plg_act_run','true');
			}
		}		
	}
	
	public function get_qbo_company_setting($setting=''){
		$return = false;
		
		if($setting!='' && $this->is_connected()){
			/*
			$Context = $this->Context;
			$realm = $this->realm;

			$q_prf = new QuickBooks_IPP_Service_Preferences();
			$prf = $q_prf->get($Context, $realm);
			*/

			$prf = $this->qbo_company_preferences;
			//$this->_p($prf);

			if(!$prf){
				return $return;
			}

			switch ($setting) {
				case 'is_shipping_allowed':
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countAllowShipping()){
						$return = $prf->getSalesFormsPrefs()->getAllowShipping();
					}

					break;

				case 'is_discount_allowed':
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countAllowDiscount()){
						$return = $prf->getSalesFormsPrefs()->getAllowDiscount();
					}

					break;
				
				case 'default_discount_account':
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countDefaultDiscountAccount()){
						$return = $prf->getSalesFormsPrefs()->getDefaultDiscountAccount();
					}

					break;
				
				case 'is_deposit_allowed':
					/*
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countAllowDeposit()){
						$return = $prf->getSalesFormsPrefs()->getAllowDeposit();
					}
					*/
					$return = true;

					break;
				
				case 'is_deposit_allowed_real':					
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countAllowDeposit()){
						$return = $prf->getSalesFormsPrefs()->getAllowDeposit();
					}			

					break;

				case 'is_service_date_allowed':
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countAllowServiceDate()){
						$return = $prf->getSalesFormsPrefs()->getAllowServiceDate();
					}

					break;
				
				case 'sf_str_type_custom_field_list':
					$return = array();
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countCustomField()){						
						$sf_str_cf_count = $prf->getSalesFormsPrefs()->countCustomField();
						for($i=0;$i<$sf_str_cf_count;$i++){											
							$cf_obj = $prf->getSalesFormsPrefs()->getCustomField($i);
							if($cf_obj->countCustomField()){
								$cf_obj_count = $cf_obj->countCustomField();
								for($j=0;$j<$cf_obj_count;$j++){
									$cf_obj_f = $cf_obj->getCustomField($j);														
									if($cf_obj_f->getType() == 'StringType'){
										$cf_nf = $cf_obj_f->getName();
										$cf_id = substr($cf_nf, -1);
										$cf_id = (int) $cf_id;
										if($cf_id>0 && $cf_obj_f->getStringValue()!=''){
											$cf_key = $cf_id.','.$cf_obj_f->getStringValue();
											$return[$cf_key] = $cf_obj_f->getStringValue();
										}
									}									
								}
							}							
						}
					}
					
					break;

				case 'is_estimate_allowed':
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countAllowEstimates()){
						$return = $prf->getSalesFormsPrefs()->getAllowEstimates();
					}

					break;

				case 'is_custom_txn_num_allowed':
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countCustomTxnNumbers()){
						$return = $prf->getSalesFormsPrefs()->getCustomTxnNumbers();
					}

					break;
					
				//
				case 'ETransactionPaymentEnabled':
					/*
					if($prf->countSalesFormsPrefs() && $prf->getSalesFormsPrefs()->countETransactionPaymentEnabled()){
						$return = $prf->getSalesFormsPrefs()->getETransactionPaymentEnabled();
					}
					*/
					$return = 'true';

					break;
				
				case 'is_m_currency':
					if($prf->countCurrencyPrefs()){ //!$this->is_plg_lc_p_l() && 
						$return = $prf->getCurrencyPrefs()->getMultiCurrencyEnabled();
					}
					
					break;
				
				case 'h_currency':
					if($prf->countCurrencyPrefs()){
						$return = $prf->getCurrencyPrefs()->getHomeCurrency();
					}

					break;

				case 'is_sales_tax':
					if($prf->countTaxPrefs()){
						$return = $prf->getTaxPrefs()->getUsingSalesTax();
					}
					break;
					
				//18-01-2018
				case 'is_automated_sales_tax':
					if($prf->countTaxPrefs()){
						if($prf->getTaxPrefs()->countPartnerTaxEnabled()){
							$return = $prf->getTaxPrefs()->getPartnerTaxEnabled();
						}						
					}
					break;
					
				case 'is_automated_sales_tax_only_enabled':
					if($prf->countTaxPrefs()){
						if($prf->getTaxPrefs()->countPartnerTaxEnabled()){
							$return = true;
						}else{
							$return = false;
						}						
					}
					break;

				case 'TrackDepartments':
					if($prf->countAccountingInfoPrefs()){
						$return = $prf->getAccountingInfoPrefs()->getTrackDepartments();
					}
					break;

				case 'DepartmentTerminology':
					if($prf->countAccountingInfoPrefs()){
						$return = $prf->getAccountingInfoPrefs()->getDepartmentTerminology();
					}
					break;

				case 'ClassTrackingPerTxn':
					if($prf->countAccountingInfoPrefs()){
						$return = $prf->getAccountingInfoPrefs()->getClassTrackingPerTxn();
					}
					break;

				case 'ClassTrackingPerTxnLine':
					if($prf->countAccountingInfoPrefs()){
						$return = $prf->getAccountingInfoPrefs()->getClassTrackingPerTxnLine();
					}
					break;
				
				case 'QuantityOnHand':
					if($prf->countProductAndServicesPrefs()){
						$return = $prf->getProductAndServicesPrefs()->getQuantityOnHand();
					}
					break;
				
				default:
					# code...
					break;
			}
		}
		//21-04-2017
		if($return=='true'){
			$return = true;
		}elseif($return=='false'){
			$return = false;
		}

		return $return;
	}

	public function mod_qbo_get_tx_dtls($qbo_tax_code='',$is_po=false){
		$tx_dtls = array();
		if($qbo_tax_code!='' && $this->is_connected()){
			$qbo_tax_code_w = $this->qcb_s($qbo_tax_code);

			$Context = $this->Context;
			$realm = $this->realm;

			$TaxCodeService = new QuickBooks_IPP_Service_TaxCode();
			$taxcodes = $TaxCodeService->query($Context, $realm, "SELECT * FROM TaxCode WHERE Id = '$qbo_tax_code_w' ");
			#$this->_p($taxcodes);
			if($taxcodes && !empty($taxcodes)){
				$TaxCode = $taxcodes[0];
				$tx_dtls['Name'] = $TaxCode->getName();
				$tx_dtls['Active'] = $TaxCode->getActive();
				$tx_dtls['Taxable'] = $TaxCode->getTaxable();
				$tx_dtls['TaxGroup'] = $TaxCode->getTaxGroup();
				$tx_dtls['TaxRateDetail'] = array();

				#New - Changes for PO
				if(!$is_po){
					if($TaxCode->countSalesTaxRateList() && $TaxCode->getSalesTaxRateList()){
						if($TaxCode->getSalesTaxRateList()->countTaxRateDetail()){
							for($i=0;$i<$TaxCode->getSalesTaxRateList()->countTaxRateDetail();$i++){
								$TaxRateDetail = $TaxCode->getSalesTaxRateList()->getTaxRateDetail($i);
								$s_rate_details = array();
								$s_rate_details['TaxRateRef'] = $TaxRateDetail->getTaxRateRef();
								$s_rate_details['TaxRateRef_name'] = $TaxRateDetail->getTaxRateRef_name();
								$s_rate_details['TaxTypeApplicable'] = $TaxRateDetail->getTaxTypeApplicable();
								$s_rate_details['TaxOrder'] = $TaxRateDetail->getTaxOrder();
								$s_rate_details['TaxOnTaxOrder'] = $TaxRateDetail->getTaxOnTaxOrder();

								$tx_dtls['TaxRateDetail'][] = $s_rate_details;
							}
						}

					}
				}else{
					if($TaxCode->countPurchaseTaxRateList() && $TaxCode->getPurchaseTaxRateList()){
						if($TaxCode->getPurchaseTaxRateList()->countTaxRateDetail()){
							for($i=0;$i<$TaxCode->getPurchaseTaxRateList()->countTaxRateDetail();$i++){
								$TaxRateDetail = $TaxCode->getPurchaseTaxRateList()->getTaxRateDetail($i);
								$s_rate_details = array();
								$s_rate_details['TaxRateRef'] = $TaxRateDetail->getTaxRateRef();
								$s_rate_details['TaxRateRef_name'] = $TaxRateDetail->getTaxRateRef_name();
								$s_rate_details['TaxTypeApplicable'] = $TaxRateDetail->getTaxTypeApplicable();
								$s_rate_details['TaxOrder'] = $TaxRateDetail->getTaxOrder();
								$s_rate_details['TaxOnTaxOrder'] = $TaxRateDetail->getTaxOnTaxOrder();

								$tx_dtls['TaxRateDetail'][] = $s_rate_details;
							}
						}

					}
				}				

			}
		}

		return $tx_dtls;
	}

	public function get_qbo_tax_code_value_by_key($qbo_tax_code='{-TAX}',$key="TaxRateRef"){
		if(!$this->is_connected()){
			return;
		}
		$qbo_tax_code_w = $this->qcb_s($qbo_tax_code);

		$Context = $this->Context;
		$realm = $this->realm;

		$TaxCodeService = new QuickBooks_IPP_Service_TaxCode();
		//$taxcodes = $TaxCodeService->query($Context, $realm, "SELECT * FROM TaxCode");
		$taxcodes = $TaxCodeService->query($Context, $realm, "SELECT * FROM TaxCode WHERE Id = '$qbo_tax_code_w' ");
		$return = '';

		if(!empty($taxcodes)){
			foreach ($taxcodes as $TaxCode){
				if($TaxCode->getId()==$qbo_tax_code){

					if($TaxCode->countSalesTaxRateList() && $TaxCode->getSalesTaxRateList()){
					   if($TaxCode->getSalesTaxRateList()->countTaxRateDetail()){

						 if($key=='TaxRateRef'){$return = $TaxCode->getSalesTaxRateList()->getTaxRateDetail()->getTaxRateRef();}
						 if($key=='TaxTypeApplicable'){$return = $TaxCode->getSalesTaxRateList()->getTaxRateDetail()->getTaxTypeApplicable();}
						 if($key=='TaxOrder'){$return = $TaxCode->getSalesTaxRateList()->getTaxRateDetail()->getTaxOrder();}

					   }
					}

					//if need other data
					if($TaxCode->countActive()){
						if($key == 'Active'){$return = $TaxCode->getActive();}
					}

					if($TaxCode->countTaxable() && $key == 'Taxable'){$return = $TaxCode->getTaxable();}
					if($TaxCode->countName() && $key == 'Name'){$return = $TaxCode->getName();}
					if($TaxCode->countTaxGroup() && $key == 'TaxGroup'){$return = $TaxCode->getTaxGroup();}

					break;
				}
			}
		}

		return $return;
	}

	public function get_qbo_tax_rate_value_by_key($qbo_tax_rate_code='',$key="RateValue"){

		if(!$this->is_connected()){
			return;
		}

		$qbo_tax_rate_code_w = $this->qcb_s($qbo_tax_rate_code);

		$return =($key=="RateValue")?0:'';

		if($qbo_tax_rate_code!=''){

			$Context = $this->Context;
			$realm = $this->realm;

			$TaxRateService = new QuickBooks_IPP_Service_TaxRate();
			//$taxrates = $TaxRateService->query($Context, $realm, "SELECT * FROM TaxRate");
			$taxrates = $TaxRateService->query($Context, $realm, "SELECT * FROM TaxRate WHERE Id = '$qbo_tax_rate_code_w' ");
			foreach ($taxrates as $TaxRate){

				if($TaxRate->getId()==$qbo_tax_rate_code){

					if($TaxRate->countRateValue()){
						if($key=='RateValue'){
						   $return =  $TaxRate->getRateValue();

						}
					}
					//

					if($key=='Name'){
						$return =  $TaxRate->getName();
					}

					if($key=='Active'){
						$return =  $TaxRate->getActive();
					}

					break;

				}
			}

		}
		return $return;
	}

	public function get_option($key='',$default=''){
		/**/
		if($key == 'mw_qbo_sync_activation_redirect'){
			return 'true';
		}
		
		if($key == 'mw_wc_qbo_sync_access_token'){
			return 'ACCESS_TOKEN';
		}
		
		if($key == 'mw_wc_qbo_sync_compt_p_wconmkn'){
			if(!$this->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
				return '';
			}
			
			if($this->is_only_plugin_active('woocommerce-sequential-order-numbers')){
				return '_order_number';
			}
		}
		
		if($key == 'mw_wc_qbo_sync_session_cn_ls_chk'){return '';}
		
		if($key=='mw_wc_qbo_sync_order_as_sales_receipt'){
			if($this->get_option('mw_wc_qbo_sync_order_qbo_sync_as')=='SalesReceipt'){
				return 'true';
			}
			return '';
		}
		
		/**/
		if($key == 'mw_wc_qbo_sync_wc_cust_role'){
			$wra = array();
			if(!function_exists('get_editable_roles')){
				require_once(ABSPATH.'wp-admin/includes/user.php');
			}
			
			$wu_roles = get_editable_roles();
			if(is_array($wu_roles) && !empty($wu_roles)){
				foreach ($wu_roles as $role_name => $role_info){
					$wra[] = $role_name;
				}
			}
			return implode(',',$wra);
		}
		
		
		$option = $default;
		if($key!=''){
			//$this->_p($this->mw_wc_qbo_sync_plugin_options);
			if(is_array($this->mw_wc_qbo_sync_plugin_options) && !empty($this->mw_wc_qbo_sync_plugin_options) && isset($this->mw_wc_qbo_sync_plugin_options[$key])){
				$option = $this->mw_wc_qbo_sync_plugin_options[$key];
			}else{
				$option = get_option($key);
			}
		}
		$option = trim($option);
		/**/
		if($key == 'mw_wc_qbo_sync_order_qbo_sync_as' && ($option == 'Per Role' || $option == 'Per Gateway' || $option == 'Estimate') && $this->is_plg_lc_p_l()){
			$option = 'Invoice';
		}
		
		if($key == 'mw_wc_qbo_sync_product_pull_desc_field' || $key == 'mw_wc_qbo_sync_produc_push_purchase_desc_field'){
			if(empty($option)){
				$option = 'none';
			}
		}
		
		return $option;
	}
	
	public function get_all_options($keys=array()){
		$option_arr = array();
		if(isset($this->mw_wc_qbo_sync_plugin_options) && is_array($this->mw_wc_qbo_sync_plugin_options)){
			$option_arr =  $this->mw_wc_qbo_sync_plugin_options;
		}
		//
		if(is_array($keys) && !empty($keys)){
			foreach($keys as $val){
				if(!isset($option_arr[$val])){
					$option_arr[$val] = '';
				}
			}
		}
		return $option_arr;
	}
	
	/**/
	public function get_key_value_options_from_table($blank_option=false,$t_name='',$key_field='',$val_field='',$whr='',$orderby='',$limit=''){
		$kv_arr = array();
		if($t_name!='' && $key_field!='' && $val_field!=''){
			$op_fields = "$key_field,$val_field";
			$op_data = $this->get_tbl($t_name,$op_fields,$whr,$orderby,$limit);
			
			if($this->start_with($val_field,'CONCAT(') || $this->start_with($val_field,'CONCAT_WS(')){
				$vfa = preg_split('/\s+/', $val_field);
				$val_field = end($vfa);
			}
			
			if(is_array($op_data) && !empty($op_data)){
				if($blank_option){
					$kv_arr[''] = '';
				}
				foreach ($op_data as $key => $value) {
					$kv_arr[$value[$key_field]] = $value[$val_field];
				}
			}
		}
		return $kv_arr;
	}
	
	public function option_checked($option=''){
		if($option == 'mw_wc_qbo_sync_pause_up_qbo_conection'){return true;}
		
		if($option == 'mw_wc_qbo_sync_allow_cdc_for_invnt_import'){
			return false;
		}
		
		if($option == 'mw_wc_qbo_sync_allow_cdc_for_prc_import'){
			return false;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_qb_ed_invt_s_frc'){
			return false;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_rt_push_enable'){
			return true;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_webhook_enable'){
			return true;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_wam_mng_inv_ed' && ($this->is_plg_lc_p_l() || $this->is_plg_lc_p_r())){
			return false;
		}
		
		if($option == 'mw_wc_qbo_sync_po_sync_after_ord_ed' && $this->is_plg_lc_p_r()){
			return false;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_skip_os_lid'){
			if($this->get_option('mw_wc_qbo_sync_inv_sr_qb_lid_val') == 'no_desc'){
				if($this->get_option('mw_wc_qbo_sync_wolim_iqilid_desc') != 'true'){
					return true;
				}				
			}
			
			return false;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_os_price_fp_update'){
			return false;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_pull_enable'){
			return true;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_hide_vpp_fmp_pages'){
			return true;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_ignore_cdc_for_invnt_import'){
			return true;
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_invoice_notes'){return false;}
		if($option == 'mw_wc_qbo_sync_invoice_memo'){
			$won_qbf_v = $this->get_option('mw_wc_qbo_sync_won_qbf_sync');
			if(!empty($won_qbf_v)){
				if($won_qbf_v == 'PrivateNote'){
					return true;
				}else{
					return false;
				}
			}
		}
		
		/**/
		if($option == 'mw_wc_qbo_sync_qbo_push_invoice_date'){
			return true;
		}
		
		/**/
		if($option=='mw_wc_qbo_sync_sync_txn_fee_as_ng_li'){
			if($this->is_plg_lc_p_l()){
				return false;
			}
		}
		
		/**/
		if($option=='mw_wc_qbo_sync_order_as_sales_receipt'){
			if($this->get_option('mw_wc_qbo_sync_order_qbo_sync_as')=='SalesReceipt'){
				return true;
			}
			return false;
		}
		
		if($option=='mw_wc_qbo_sync_order_as_estimate'){
			if($this->get_option('mw_wc_qbo_sync_order_qbo_sync_as')=='Estimate'){
				return true;
			}
			return false;
		}
		
		/**/
		if($option=='mw_wc_qbo_sync_wc_qbo_product_desc'){
			if($this->get_option('mw_wc_qbo_sync_product_pull_desc_field')=='name'){
				return true;
			}
			return false;
		}
		
		if($option=='mw_wc_qbo_sync_order_as_per_role'){
			if($this->get_option('mw_wc_qbo_sync_order_qbo_sync_as')=='Per Role'){
				return true;
			}
			return false;
		}
		
		if($option=='mw_wc_qbo_sync_order_as_per_gateway'){
			if($this->get_option('mw_wc_qbo_sync_order_qbo_sync_as')=='Per Gateway'){
				return true;
			}
			return false;
		}
		
		//
		if($option == 'mw_wc_qbo_sync_use_qb_next_ord_num_iowon'){
			if($this->is_plg_lc_p_l()){
				return false;
			}
		}

		//
		if($option == 'mw_wc_qbo_sync_all_order_to_customer'){
			if($this->is_plg_lc_p_l()){
				return false;
			}
		}
		
		if($this->get_option($option)=='true'){
			return true;
		}
		return false;
	}
	
	public function get_qb_ivnt_p_til($onlp=false){
		$tia = array();
		$tia['MWQBO_5min'] = '5 minutes';
		$tia['MWQBO_15min'] = '15 minutes';
		$tia['MWQBO_30min'] = '30 minutes';
		
		if($onlp){
			return $tia;
		}
		
		if($this->is_plg_lc_p_r()){
			//$tia['MWQBO_45min'] = '45 minutes';
		}
		
		$tia['MWQBO_60min'] = '1 hour';
		$tia['MWQBO_360min'] = '6 hours';
		
		//if(!$this->is_plg_lc_p_l()){}
		
		return $tia;
	}
	
	public function get_qb_queue_p_til($onlp=false){
		$tia = array();
		$tia['MWQBO_5min'] = '5 minutes';
		$tia['MWQBO_10min'] = '10 minutes';
		$tia['MWQBO_15min'] = '15 minutes';
		
		$tia['MWQBO_30min'] = '30 minutes';
		
		if($onlp){
			return $tia;
		}
		
		if($this->is_plg_lc_p_r()){
			//$tia['MWQBO_45min'] = '45 minutes';
		}
		
		$tia['MWQBO_60min'] = '1 hour';
		
		//if(!$this->is_plg_lc_p_l()){}
		
		return $tia;
	}
	
	public function truncate_number( $number, $precision = 2) {
		$value = ( string )$number;
		preg_match( "/(-+)?\d+(\.\d{1,".$precision."})?/" , $value, $matches );
		return (float) $matches[0];
		//old code
		if($number>0){
		// Are we negative?
		$negative = $number / $this->_abs($number);
		// Cast the number to a positive to solve rounding
		$number = $this->_abs($number);
		// Calculate precision number for dividing / multiplying
		$precision = pow(10, $precision);
		// Run the math, re-applying the negative value to ensure returns correctly negative / positive
		return floor( $number * $precision ) / $precision * $negative;
		}else{
			return $number;
		}
	}

	public function sp_round($num=''){
		$i_amnt = $num;
		if ($num!='' && $num>0 && strpos($num, '.') !== false) {
			list($before_dot, $after_dot) = explode('.', $num);
			if(strlen($after_dot)>2){
				$first_three_digit = substr($after_dot, 0, 3);
				$last_digit = substr($first_three_digit, -1);
				if($last_digit>5){
					$i_amnt = round($num,2,PHP_ROUND_HALF_DOWN);
				}else{
					$first_two_digit = substr($after_dot, 0, 2);
					$i_amnt = $before_dot.'.'.$first_two_digit;


				}
			}
		}

		$i_amnt = floatval($i_amnt);
		return $i_amnt;
	}

	public function save_log($log_title='',$log_msg='',$type='',$success=0,$add_into_loggly=false,$l_av='Other'){
		if($log_title!=''){
			global $wpdb;
			$table = $wpdb->prefix.'mw_wc_qbo_sync_log';
			
			//Existing param value when calling from some pages / functions
			if(!$l_av){
				$l_av = 'Other';
			}
			
			$max_log_save_day = intval($this->get_option('mw_wc_qbo_sync_save_log_for'));
			$max_log_save_day = ($max_log_save_day<30)?30:$max_log_save_day;

			$log_last_date = date('Y-m-d',strtotime("-$max_log_save_day days",strtotime($this->now())));
			$log_last_date = $log_last_date.' 23:59:59';

			$wpdb->query(
			$wpdb->prepare(
					"
					DELETE FROM $table
					 WHERE `added_date` < %s
					",
					$log_last_date
					)
			);

			$log_data = array();
			$log_title = addslashes($log_title);
			$log_msg = addslashes($log_msg);

			$log_data['log_title'] = $log_title;
			$log_data['details'] = $log_msg;
			$log_data['log_type'] = $type;
			$log_data['success'] = intval($success);
			$log_data['added_date'] = $this->now();

			$wpdb->insert($table, $log_data);
			//$log_id = $wpdb->insert_id;
			if($add_into_loggly){

				$loggly_msg = array();

				$s_type = (intval($success))?'success':'error';
				$loggly_msg['type'] = $s_type;

				$licensekey = $this->get_option('mw_wc_qbo_sync_license');
				$loggly_msg['licensekey'] = $licensekey;

				$loggly_msg['url'] = get_site_url();
				$loggly_msg['title'] = $log_title;
				$loggly_msg['message'] = $log_msg;
				//
				if($type == 'Invoice'){$type = 'Order';}
				
				$loggly_msg['log_type'] = $type;
				//
				$loggly_msg['log_activity'] = $l_av;

				$loggly_msg['product'] = 'WOOQBO';

				if($this->is_connected()){
					$realm = $this->realm;
					$loggly_msg['qbo_realm'] = $realm;
				}

				/*
				$loggly_msg = '';
				$loggly_msg.= "URL: ".get_site_url()."\n";
				$loggly_msg.="Title: ".$log_title;
				$loggly_msg.="Message: ".$log_msg;
				*/

				$this->loggly_api_add_log($loggly_msg);
			}

		}
	}

	public function get_qbo_salesreceipt_id($wc_inv_id,$wc_inv_num=''){
		 return $this->check_quickbooks_salesreceipt_get_obj($wc_inv_id,$wc_inv_num,true);
	}
	
	//
	public function get_qbo_estimate_id($wc_inv_id,$wc_inv_num=''){
		 return $this->check_quickbooks_estimate_get_obj($wc_inv_id,$wc_inv_num,true);
	}	
	
	public function get_qbo_invoice_id($wc_inv_id,$wc_inv_num=''){
		 return $this->check_quickbooks_invoice_get_obj($wc_inv_id,$wc_inv_num,true);
	}
	
	//25-04-2017
	public function if_sync_category($wc_category_id){
		if(!$this->get_qbo_company_info('is_category_enabled')){
			return false;
		}
		return true;
	}
	public function check_category_exists($cat_data, $return_id=false, $get_obj=false){
		//$name = $this->get_array_isset($cat_data,'name','',true);
		$name_replace_chars = array(':');
		$name = $this->get_array_isset($cat_data,'name','',true,100,false,$name_replace_chars);
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$ItemService = new QuickBooks_IPP_Service_Term();
			if($item_data = $ItemService->query($Context, $realm, "SELECT * FROM Item WHERE Type = 'Category' AND Name = '$name'")){
				$item_id = $this->qbo_clear_braces($item_data[0]->getId());
				if($get_obj){
					return $item_data[0];
				}
				return ($return_id)?$item_id:true;
			}
		}
		return false;
	}
	
	//08-05-2017
	public function UpdateCategory($cat_data){
		//$this->include_sync_functions('UpdateCategory');
		$fn_name = 'UpdateCategory';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function AddCategory($cat_data){
		//$this->include_sync_functions('AddCategory');
		$fn_name = 'AddCategory';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	/**/
	public function AddProduct($product_data){
		//$this->include_sync_functions('AddProduct');
		$fn_name = 'AddProduct';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	//01-06-2017
	public function UpdateProduct($product_data){
		//$this->include_sync_functions('UpdateProduct');
		$fn_name = 'UpdateProduct';
		//
		if($this->option_checked('mw_wc_qbo_sync_os_price_fp_update')){
			$fn_name = 'OnlyPrice_UpdateProduct';
		}
		
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function if_sync_product($wc_product_id){
		return true;
	}

	public function check_product_exists($product_data,$realtime=true,$return_id=false){
		global $wpdb;
		$is_exists = false;
		$wc_product_id = (int) $this->get_array_isset($product_data,'wc_product_id',0,true);

		$name_replace_chars = array(':');
		$name = $this->get_array_isset($product_data,'name','',true,100,false,$name_replace_chars);

		//$name = $this->get_array_isset($product_data,'name','',true);

		$sku = $this->get_array_isset($product_data,'_sku','',true);
		//$type = $this->get_array_isset($product_data,'type','',true);

		$is_variation = $this->get_array_isset($product_data,'is_variation',false,false);
		$item_id = 0;
		if($name!=''){
			if($item_id = $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','ID','name',$name)){
				$is_exists = true;
			}
		}

		//10-04-2017
		if(!$is_exists){
			if($sku!=''){
				if($item_id = $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','ID','sku',$sku)){
					$is_exists = true;
				}
			}
		}

		//map table
		$map_exists = false;
		if(!$is_exists){
			$map_tbl = ($is_variation)?'mw_wc_qbo_sync_variation_pairs':'mw_wc_qbo_sync_product_pairs';
			$w_p_f = ($is_variation)?'wc_variation_id':'wc_product_id';
			if($item_id = $this->get_field_by_val($wpdb->prefix.$map_tbl,'quickbook_product_id',$w_p_f,$wc_product_id)){
				$is_exists = true;
				$map_exists = true;
			}
		}

		//11-07-2017
		if($is_exists && $map_exists){
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				$ItemService = new QuickBooks_IPP_Service_Term();
				if($item_data = $ItemService->query($Context, $realm, "SELECT * FROM Item WHERE Id = '$item_id' AND Type IN ('Inventory','Service','NonInventory','Group') ")){
					$item_id = $this->qbo_clear_braces($item_data[0]->getId());
				}else{
					$is_exists = false;
				}
			}
		}

		if(!$is_exists && $realtime){
			$item_id = (int) $this->check_product_realtime($product_data);
			if($item_id){
				$is_exists = true;
			}
		}

		return ($return_id)?$item_id:$is_exists;
	}

	public function check_product_realtime($product_data,$get_obj=false){
		$name = $this->get_array_isset($product_data,'name','',true);
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$ItemService = new QuickBooks_IPP_Service_Term();
			if($item_data = $ItemService->query($Context, $realm, "SELECT * FROM Item WHERE Name = '$name' AND Type IN ('Inventory','Service','NonInventory','Group') ")){
				$item_id = $this->qbo_clear_braces($item_data[0]->getId());
				return ($get_obj)?$item_data[0]:$item_id;
			}
		}
		return false;
	}
	
	/**/
	public function check_quickbooks_po_get_obj($wc_inv_id,$wc_inv_num='',$get_only_id=false){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$DocNumber = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
			
			/**/
			if($this->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$this->get_qbo_company_setting('is_custom_txn_num_allowed')){
				$DocNumber = get_post_meta($wc_inv_id,'_mw_qbo_sync_ord_doc_no',true);
				$DocNumber = trim($DocNumber);
				if(empty($DocNumber)){
					return false;
				}
			}
			
			$PurchaseOrderService = new QuickBooks_IPP_Service_PurchaseOrder();
			$po_data = $PurchaseOrderService->query($Context, $realm, "SELECT * FROM PurchaseOrder WHERE DocNumber = '$DocNumber'");
			//$this->_p($po_data,true);
			if($po_data && !empty($po_data)){
				$po_data = $po_data[0];
				if($get_only_id){
					return $this->qbo_clear_braces($po_data->getId());
				}
				return $po_data;
			}
			return false;
		}
	}
	
	public function check_quickbooks_invoice_get_obj($wc_inv_id,$wc_inv_num='',$get_only_id=false){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$DocNumber = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
			
			/**/
			if($this->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$this->get_qbo_company_setting('is_custom_txn_num_allowed')){
				$DocNumber = get_post_meta($wc_inv_id,'_mw_qbo_sync_ord_doc_no',true);
				$DocNumber = trim($DocNumber);
				if(empty($DocNumber)){
					return false;
				}
			}
			
			$invoiceService = new QuickBooks_IPP_Service_Invoice();
			$invoices_data = $invoiceService->query($Context, $realm, "SELECT * FROM Invoice WHERE DocNumber = '$DocNumber'");
			//$this->_p($invoices_data,true);
			if($invoices_data && !empty($invoices_data)){
				$invoices_data = $invoices_data[0];
				if($get_only_id){
					return $this->qbo_clear_braces($invoices_data->getId());
				}
				return $invoices_data;
			}
			return false;
		}
	}

	public function check_quickbooks_salesreceipt_get_obj($wc_inv_id,$wc_inv_num='',$get_only_id=false){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$DocNumber = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
			
			/**/
			if($this->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$this->get_qbo_company_setting('is_custom_txn_num_allowed')){
				$DocNumber = get_post_meta($wc_inv_id,'_mw_qbo_sync_ord_doc_no',true);
				$DocNumber = trim($DocNumber);
				if(empty($DocNumber)){
					return false;
				}
			}
			
			$SalesReceiptService = new QuickBooks_IPP_Service_SalesReceipt();
			$salesreceipt_data = $SalesReceiptService->query($Context, $realm, "SELECT * FROM SalesReceipt WHERE DocNumber = '$DocNumber'");
			//$this->_p($salesreceipt_data,true);
			if($salesreceipt_data && !empty($salesreceipt_data)){
				$salesreceipt_data = $salesreceipt_data[0];
				if($get_only_id){
					return $this->qbo_clear_braces($salesreceipt_data->getId());
				}
				return $salesreceipt_data;
			}
			return false;
		}
	}
	
	public function check_quickbooks_estimate_get_obj($wc_inv_id,$wc_inv_num='',$get_only_id=false){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$DocNumber = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
			
			/**/
			if($this->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$this->get_qbo_company_setting('is_custom_txn_num_allowed')){
				$DocNumber = get_post_meta($wc_inv_id,'_mw_qbo_sync_ord_doc_no',true);
				$DocNumber = trim($DocNumber);
				if(empty($DocNumber)){
					return false;
				}
			}
			
			$EstimateService = new QuickBooks_IPP_Service_Estimate();
			$estimate_data = $EstimateService->query($Context, $realm, "SELECT * FROM Estimate WHERE DocNumber = '$DocNumber'");
			//$this->_p($estimate_data,true);
			if($estimate_data && !empty($estimate_data)){
				$estimate_data = $estimate_data[0];
				if($get_only_id){
					return $this->qbo_clear_braces($estimate_data->getId());
				}
				return $estimate_data;
			}
			return false;
		}
	}
	
	/**/
	public function get_qbo_product_PurchaseCost($qbo_product_id){
		if($this->is_connected() && $qbo_product_id){
			$Context = $this->Context;
			$realm = $this->realm;
			
			$ItemService = new QuickBooks_IPP_Service_Term();
			$items = $ItemService->query($Context, $realm, "SELECT PurchaseCost FROM Item WHERE Id='{$qbo_product_id}' ");
			if($items && !empty($items)){
				//$this->_p($items);
				$item = $items[0];
				if($item->countPurchaseCost()){
					return $item->getPurchaseCost();
				}
			}
		}		
	}
	
	public function if_sync_po($wc_inv_id,$wc_cus_id,$wc_inv_num){
		if($this->option_checked('mw_wc_qbo_sync_po_sync_after_ord_ed') && !empty($this->get_option('mw_wc_qbo_sync_po_sync_after_ord_qb_vendor')) && !empty($this->get_option('mw_wc_qbo_sync_po_sync_after_ord_pa_acc'))){
			return true;
		}
		return false;
	}
	
	public function if_sync_invoice($wc_inv_id,$wc_cus_id=0,$wc_inv_no=''){
		$ord_id_num = ($wc_inv_no!='')?$wc_inv_no:$wc_inv_id;
		if($wc_inv_id < (int) $this->get_option('mw_wc_qbo_sync_invoice_min_id')){
			$this->save_log('Export Order #'.$ord_id_num,'Order sync not allowed for ID less than #'.(int) $this->get_option('mw_wc_qbo_sync_invoice_min_id'),'Invoice',2);
			return false;
		}
		return true;
	}
	
	//19-06-2017
	public function get_wc_order_id_from_qbo_inv_sr_doc_no($qbo_inv_sr_doc_no=0){
		$qbo_inv_sr_doc_no = $this->sanitize($qbo_inv_sr_doc_no);
		$wc_inv_id = 0;
		if($qbo_inv_sr_doc_no!=''){
			global $wpdb;
			//woocommerce-sequential-order-numbers
			if($this->is_plugin_active('woocommerce-sequential-order-numbers-pro','') && $this->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
				/*
				$onk_f = '_order_number_formatted';
				if($this->is_only_plugin_active('woocommerce-sequential-order-numbers')){
					$onk_f = '_order_number';
				}*/
				$onk_f = $this->get_woo_ord_number_key_field();
				
				$sql = "SELECT p.ID FROM `{$wpdb->posts}` p, `{$wpdb->postmeta}` pm WHERE pm.meta_key = '{$onk_f}' AND pm.meta_value = %s AND pm.post_id = p.ID AND p.post_type = 'shop_order' ";
			}else{
				/**/
				if($this->is_plugin_active('custom-order-numbers-for-woocommerce') && $this->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
					$sql = "SELECT p.ID FROM `{$wpdb->posts}` p, `{$wpdb->postmeta}` pm WHERE pm.meta_key = '_alg_wc_full_custom_order_number' AND pm.meta_value = %s AND pm.post_id = p.ID AND p.post_type = 'shop_order' ";
					$akd = $this->get_row($sql);
					
					if(empty($akd)){
						$sql = "SELECT p.ID FROM `{$wpdb->posts}` p, `{$wpdb->postmeta}` pm WHERE pm.meta_key = '_alg_wc_custom_order_number' AND pm.meta_value = %s AND pm.post_id = p.ID AND p.post_type = 'shop_order' ";
					}
					
				}elseif(!empty($this->get_option('mw_wc_qbo_sync_compt_p_wconmkn')) && $this->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
					/**/
					$wconmkn_key = $this->get_option('mw_wc_qbo_sync_compt_p_wconmkn');
					$sql = $wpdb->prepare("SELECT p.ID FROM `{$wpdb->posts}` p, `{$wpdb->postmeta}` pm WHERE pm.meta_key = %s AND pm.meta_value = %s AND pm.post_id = p.ID AND p.post_type = 'shop_order' ",$wconmkn_key);
				}
				else{
					$qbo_inv_sr_doc_no = (int) $qbo_inv_sr_doc_no;
					$sql = "SELECT `ID` FROM `{$wpdb->posts}` WHERE `ID` = %d AND `post_type` = 'shop_order' ";
				}				
			}
			$sql = $wpdb->prepare($sql,$qbo_inv_sr_doc_no);
			$wc_ord_data = $this->get_row($sql);
			
			if(is_array($wc_ord_data) && !empty($wc_ord_data)){
				$wc_inv_id = (int) $wc_ord_data['ID'];
			}			
		}
		return $wc_inv_id;
	}

	public function Qbo_Pull_Payment($payment_info){
		if($this->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
			return false;
		}
		$qbo_payment_id = (int) $this->get_array_isset($payment_info,'qbo_payment_id',0);
		$manual = $this->get_array_isset($payment_info,'manual',false);

		$webhook_log_txt = '';
		$webhook = $this->get_array_isset($payment_info,'webhook',false);
		if($webhook){
			$webhook_log_txt = 'Webhook ';
			//$manual = true;
		}

		global $wpdb;
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$PaymentService = new QuickBooks_IPP_Service_Payment();
			$sql = "SELECT * FROM Payment WHERE Id = '$qbo_payment_id' ";
			$items = $PaymentService->query($Context, $realm, $sql);
			//$this->_p($items);return;
			if(!$items || empty($items)){
				$this->save_log($webhook_log_txt.'Import Payment Error #'.$qbo_payment_id,'Invalid QuickBooks Payment.','Payment',0);
				return false;
			}

			$payment = $items[0];
			//$this->_p($payment);die;
			
			$CustomerRef = $payment->getCustomerRef();
			$TotalAmt = $payment->getTotalAmt();

			$TxnDate = $payment->getTxnDate();
			
			/*Changes for Multiple Support*/			
			$pmnt_line_count = $payment->countLine();
			//$this->_p($pmnt_line_count);
			if($pmnt_line_count){
				for($i=0;$i<$pmnt_line_count;$i++){
					$qbo_inv_id = 0;
					$wc_inv_id = 0;
					
					if($payment->getLine($i)->countLinkedTxn()){
						if($payment->getLine($i)->getLinkedTxn()->getTxnType()=='Invoice'){
							$qbo_inv_id = $this->qbo_clear_braces($payment->getLine($i)->getLinkedTxn()->getTxnId());
						}
					}
					
					$qbo_inv_doc_no = '';
					$qbo_inv_balance = 0;
					$qbo_inv_total_amt = 0;
					//$this->_p($qbo_inv_id,true);
					
					if($qbo_inv_id){
						$invoiceService = new QuickBooks_IPP_Service_Invoice();
						$invoices_data = $invoiceService->query($Context, $realm, "SELECT * FROM Invoice WHERE Id = '$qbo_inv_id'");
						//$this->_p($invoices_data);
						if($invoices_data && !empty($invoices_data)){
							$qbo_inv_doc_no = $invoices_data[0]->getDocNumber();
							$qbo_inv_balance = $invoices_data[0]->getBalance();
							$qbo_inv_total_amt = $invoices_data[0]->getTotalAmt();
							//$this->_p($qbo_inv_doc_no);
							$wc_inv_id = $this->get_wc_order_id_from_qbo_inv_sr_doc_no($qbo_inv_doc_no);
						}
					}
					
					if($wc_inv_id){
						$order = get_post($wc_inv_id);
						$invoice_data = $this->get_wc_order_details_from_order($wc_inv_id,$order);
						if(is_object($order) && !empty($order)){
							$order_status = $order->post_status;
							
							$op_invalid_status_static = array('auto-draft','draft','trash');
							$prevent_statues = $this->get_option('mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses');
							if($prevent_statues!=''){
								$prevent_statues = explode(',',$prevent_statues);
							}

							$is_valid_payment_pull = true;
							$payment_post_status = $this->get_option('mw_wc_qbo_sync_pmnt_pull_order_status');
							$payment_post_status = trim($payment_post_status);
							if($payment_post_status==''){
								$payment_post_status = 'wc-completed';
							}

							if(is_array($prevent_statues) && in_array($order_status,$prevent_statues)){
								$is_valid_payment_pull = false;
							}

							if(in_array($order_status,$op_invalid_status_static)){
								$is_valid_payment_pull = false;
							}

							if($order_status==$payment_post_status){
								$is_valid_payment_pull = false;
							}
							
							/**/
							$is_pp_change_order_status = false;
							
							/*
							$_order_total = $this->get_array_isset($invoice_data,'_order_total',0);
							if(floatval($_order_total) == floatval($TotalAmt)){
								$is_pp_change_order_status = true;
							}
							*/
							
							/*
							if(floatval($qbo_inv_total_amt) == floatval($TotalAmt)){
								$is_pp_change_order_status = true;
							}
							*/
							
							if($qbo_inv_balance == 0){
								$is_pp_change_order_status = true;
							}
							
							//$this->_p($payment);die;
							
							if($order_status!='' && $payment_post_status!='' && $is_valid_payment_pull && $is_pp_change_order_status){
								$post_data = array();
								$post_data['ID'] = $wc_inv_id;
								//
								if($is_pp_change_order_status){
									$post_data['post_status'] = $payment_post_status;
								}
								
								$post_data['wp_error'] = true;

								$post_meta_arr = array();

								$log_title = '';
								$log_details = '';
								$log_status = 0;

								$is_wp_error = false;
								$wp_err_txt = '';

								#$return = $this->save_wp_post('shop_order',$post_data,$post_meta_arr);
								
								/*
								if ( is_wp_error( $return ) ) {
									$is_wp_error = true;
									$wp_err_txt.= (string) $return->get_error_message();
									$wp_err_txt.= (string) $return->get_error_data();
								}
								*/
								
								# New Changes
								$wp_err_txt = 'Order Status Update Error';
								$return = 0;
								
								if($is_pp_change_order_status){
									$order = new WC_Order($wc_inv_id);
									$r = $order->update_status( $payment_post_status );

									if($r){
										$return = $wc_inv_id;
									}else{
										$is_wp_error = true;
									}
								}else{
									$is_wp_error = true;
								}								
								
								if(!$is_wp_error && (int) $return){
									$post_id = (int) $return;

									/*Order Note*/
									$order_statuses = wc_get_order_statuses();
									$old_status =(is_array($order_statuses) && isset($order_statuses[$order_status]))?$order_statuses[$order_status]:$order_status;
									$new_status =(is_array($order_statuses) && isset($order_statuses[$payment_post_status]))?$order_statuses[$payment_post_status]:$payment_post_status;
									$order = new WC_Order( $post_id );
									$order_note = __('Order status changed from '.$old_status.' to '.$new_status,'mw_wc_qbo_sync');
									$order_note.=PHP_EOL;
									$order_note.='Payment Pull - MyWorks WooCommerce Sync for QuickBooks Online';
									$order->add_order_note($order_note);

									$log_title.=$webhook_log_txt."Import Payment #$qbo_payment_id\n";
									$log_details.="Payment #$qbo_payment_id has been imported, WooCommerce Order #{$qbo_inv_doc_no}";

									$log_status = 1;
									$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
									$this->save_payment_id_map($post_id,$qbo_payment_id,1);
									
									//return $post_id;
								}else{
									$log_title.=$webhook_log_txt."Import Payment Error #$qbo_payment_id\n";
									$log_details = "WooCommerce Order #{$qbo_inv_doc_no}\n";
									if(isset($post_data['wp_error'])){
										$log_details.="Error:$wp_err_txt";
									}else{
										$log_details.="Error:Wordpress save post error";
									}
									$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
									//return false;
								}
							}
						}
					}
				}				
			}
			
		}
	}

	//02-05-2017
	public function Qbo_Pull_Category($category_info){
		if(!$this->get_qbo_company_info('is_category_enabled')){
			return false;
		}

		$qbo_category_id = (int) $this->get_array_isset($category_info,'qbo_category_id',0);
		$manual = $this->get_array_isset($category_info,'manual',false);

		$webhook_log_txt = '';
		$webhook = $this->get_array_isset($category_info,'webhook',false);
		if($webhook){
			$webhook_log_txt = 'Webhook ';
			//$manual = true;
		}

		global $wpdb;
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Item();
			$sql = "SELECT * FROM Item WHERE Type = 'Category' AND Id = '$qbo_category_id' ";
			$items = $ItemService->query($Context, $realm, $sql);
			if(!$items || empty($items)){
				$this->save_log($webhook_log_txt.'Import Category Error','Invalid QuickBooks Category.','Category',0);
				return false;
			}

			$item = $items[0];
			$cat_name = $item->getName();
			//$cat_name = $this->sanitize($cat_name);

			//$name_replace_chars = array(':');
			//$cat_name = $this->get_array_isset(array('cat_name'=>$cat_name),'cat_name','',true,100,false,$name_replace_chars);

			$cat_name = esc_sql($cat_name);
			$cat_name = htmlspecialchars($cat_name);


			$wc_cat_check_sql = "
			SELECT t.term_id AS id, t.name
			FROM   {$wpdb->terms} t
			LEFT JOIN {$wpdb->term_taxonomy} tt
			ON t.term_id = tt.term_id
			WHERE  tt.taxonomy = 'product_cat'
			AND (t.name = %s OR REPLACE(t.name,':','') = %s)
			LIMIT 0,1
			";
			$wc_cat_check_sql = $wpdb->prepare($wc_cat_check_sql,$cat_name,$cat_name);
			$wc_cat_check_data = $this->get_row($wc_cat_check_sql);


			//$wc_cat_check_data = term_exists( $cat_name, 'product_cat' );

			//08-05-2017
			$up_term_id = 0;

			if(is_array($wc_cat_check_data) && !empty($wc_cat_check_data)){
				$up_term_id = $wc_cat_check_data['id'];
				if($manual){
					//$this->save_log($webhook_log_txt.'Import Category Error','WooCommerce category already exists with same name.','Category',0);
				}
				//return false;
			}

			$up_term_id = (int) $up_term_id;

			//15-06-2017
			if($up_term_id && $manual){
				$this->set_session_val('sync_window_pull_manual_update',true);
			}

			$wc_parent_cat_id = 0;
			if($item->countSubItem() && $item->getSubItem()=='true'){
				$ParentRef = $item->getParentRef();
				$ParentRef = $this->qbo_clear_braces($ParentRef);

				/*
				$sql_p = "SELECT Name FROM Item WHERE Type = 'Category' AND Id = '$ParentRef' ";
				$items_p = $ItemService->query($Context, $realm, $sql_p);
				if($items_p && !empty($items_p)){
					$items_p = $items_p[0];
					$ParentRef_name = $items_p->getName();
				}
				*/

				$ParentRef_name = $item->getParentRef_name();
				if(strpos( $ParentRef_name, ':' ) !== false){
					$ParentRef_name_arr = explode(':',$ParentRef_name);
					if(is_array($ParentRef_name_arr) && !empty($ParentRef_name_arr)){
						$ParentRef_name = end($ParentRef_name_arr);
					}
				}

				//$ParentRef_name = $this->get_array_isset(array('ParentRef_name'=>$ParentRef_name),'ParentRef_name','',true,100,false,$name_replace_chars);
				$ParentRef_name = esc_sql($ParentRef_name);

				$Level = $item->getLevel();


				$wc_cat_check_sql = "
				SELECT t.term_id AS id, t.name
				FROM   {$wpdb->terms} t
				LEFT JOIN {$wpdb->term_taxonomy} tt
				ON t.term_id = tt.term_id
				WHERE  tt.taxonomy = 'product_cat'
				AND (t.name = %s OR REPLACE(t.name,':','') = %s)
				LIMIT 0,1
				";
				$wc_cat_check_sql = $wpdb->prepare($wc_cat_check_sql,$ParentRef_name,$ParentRef_name);
				$wc_cat_check_data = $this->get_row($wc_cat_check_sql);

				//$wc_cat_check_data = term_exists( $ParentRef_name, 'product_cat' );
				if(is_array($wc_cat_check_data) && !empty($wc_cat_check_data)){
					$wc_parent_cat_id = $wc_cat_check_data['id'];
					//$wc_parent_cat_id = $wc_cat_check_data['term_id'];
				}else{
					if($up_term_id){
						$this->save_log($webhook_log_txt.'Import Update Category Error','Parent category '.$ParentRef_name.' (#'.$ParentRef.') not found in WooCommerce.','Category',0);
					}else{
						$this->save_log($webhook_log_txt.'Import Category Error','Parent category '.$ParentRef_name.' (#'.$ParentRef.') not found in WooCommerce.','Category',0);
					}

					return false;
				}
			}
			$wc_term_arg = array();
			$wc_term_arg['description'] = '';
			if($wc_parent_cat_id){
				$wc_term_arg['parent'] = (int) $wc_parent_cat_id;
			}

			//$this->_p($up_term_id);die;
			if($up_term_id){
				$term_insert_data = wp_update_term(
				  $up_term_id, // the term
				  'product_cat', // the taxonomy
				  $wc_term_arg
				);
			}else{
				$term_insert_data = wp_insert_term(
				  $cat_name, // the term
				  'product_cat', // the taxonomy
				  $wc_term_arg
				);
			}



			$log_title = '';
			$log_details = '';
			$log_status = 0;
			
			$l_av = 'Add';

			if(is_array($term_insert_data) && !empty($term_insert_data) && isset($term_insert_data['term_id']) && (int) $term_insert_data['term_id']){
				$wc_cat_id = $term_insert_data['term_id'];

				if($up_term_id){
					$log_title.=$webhook_log_txt."Import Update Category #$qbo_category_id\n";
					$log_details.="Category #$qbo_category_id has been updated, WooCommerce Product Category ID is #$wc_cat_id";
					$l_av = 'Update';
				}else{
					$log_title.=$webhook_log_txt."Import Category #$qbo_category_id\n";
					$log_details.="Category #$qbo_category_id has been imported, WooCommerce Product Category ID is #$wc_cat_id";
				}


				$log_status = 1;
				$this->save_log($log_title,$log_details,'Category',$log_status,true,$l_av);
				return $wc_cat_id;
			}else{
				if($up_term_id){
					$log_title.=$webhook_log_txt."Import Update Category Error #$qbo_category_id\n";
					$l_av = 'Update';
				}else{
					$log_title.=$webhook_log_txt."Import Category Error #$qbo_category_id\n";
				}

				$log_details.="Error:Wordpress save term error";
				$this->save_log($log_title,$log_details,'Category',$log_status,true,$l_av);
				return false;
			}
		}
	}
	
	//
	private function U_W_P_O_P($product_info,$Item,$wc_product_id,$qbo_product_id,$is_variation,$name,$sku,$type){
		global $wpdb;
		$Price = ($Item->countUnitPrice())?$Item->getUnitPrice():'';
		if(empty($Price)){
			//return false;
			$Price = 0;
		}
		
		$is_up_sp = true;
		$_sale_price = get_post_meta($wc_product_id,'_sale_price',true);
		$_sale_price = floatval($_sale_price);
		
		if($_sale_price > 0){ //strlen($_sale_price) >0
			$is_up_sp = false;
			$_price = get_post_meta($wc_product_id,'_regular_price',true);
		}else{
			$_price = get_post_meta($wc_product_id,'_price',true);
		}
		
		$P_Name = $this->get_field_by_val($wpdb->posts,'post_title','ID',(int) $wc_product_id);
		$P_Sku = get_post_meta($wc_product_id,'_sku',true);
		$ext_log = "\n".'Name: '.$P_Name;
		$ext_log .= "\n".'SKU: '.$P_Sku;
		
		$ext_log_txt = '';
		
		if($Price!=$_price){
			$_price = number_format(floatval($_price),2);
			update_post_meta($wc_product_id,'_regular_price',$Price);
		
			if($is_up_sp){			
				update_post_meta($wc_product_id,'_price',$Price);
			}
			
			$log_title = '';
			$log_details = '';
			$log_status = 0;

			$is_wp_error = false;
			$wp_err_txt = '';
			
			$wpv_txt = ($is_variation)?'Variation':'Product';
			
			//$log_title.=$webhook_log_txt."Import Product (Only Price) Update #$qbo_product_id\n";
			//$log_title.=$webhook_log_txt."Import Product Price #$qbo_product_id\n";
			//$log_details.="Product #$qbo_product_id has been updated, WooCommerce {$wpv_txt} ID is #$wc_product_id";
			//$log_details.="Product price #$qbo_product_id has been updated, WooCommerce {$wpv_txt} ID is #$wc_product_id";
			
			$log_title = $ext_log_txt.'Import Product Price #'.$qbo_product_id;
			$log_details = "WooCommerce {$wpv_txt} #{$wc_product_id} price updated from {$_price} to {$Price} ".$ext_log;
			
			$l_av = 'Update';
			
			$log_status = 1;
			$this->save_log($log_title,$log_details,'Product',$log_status,true,$l_av);
			$this->save_qbo_item_local($qbo_product_id,$name,$sku,$type);
			$this->save_item_map($wc_product_id,$qbo_product_id,true,$is_variation);
			
			return $wc_product_id;
		}		
	}
	
	//23-02-2017

	public function Qbo_Pull_Product($product_info,$opu=false,$i_obj=null){
		if(!$this->is_connected()){return false;}
		
		global $wpdb;
		$qbo_product_id = (int) $this->get_array_isset($product_info,'qbo_product_id',0);
		$manual = $this->get_array_isset($product_info,'manual',false);

		$webhook_log_txt = '';
		$webhook = $this->get_array_isset($product_info,'webhook',false);
		if($webhook){
			$webhook_log_txt = 'Webhook ';
			//$manual = true;
		}
		
		if($Item = $this->check_is_valid_qbo_product_get_obj($qbo_product_id,false,$i_obj)){	
			//$this->_p($Item);
			$type = $Item->getType();
			//17-05-2017
			if($type=='Group'){
				if(!$opu){
					$this->save_log($webhook_log_txt.'Import Product Error #'.$qbo_product_id,'Bundle item not supported.'.$p_map_log_txt.'. ','Product',0);
				}				
				return false;
			}
			
			$name =  $Item->getName();
			$sku = ($Item->countSku())?$Item->getSku():'';
			
			/**/
			if($this->option_checked('mw_wc_qbo_sync_wc_qbo_product_desc')){				
				//$name = (string) $Item->getDescription();
			}
			
			$pull_wpn_field = $this->get_option('mw_wc_qbo_sync_product_pull_wpn_field');
			if($pull_wpn_field == 'Description' && !empty($Item->getDescription())){
				$name = (string) $Item->getDescription();
			}
			
			if($pull_wpn_field == 'SKU' && !empty($sku)){
				$name = $sku;
			}
			
			$name =  wp_strip_all_tags($name);
			
			if($name==''){
				return false;
			}
			//
			$is_variation = false;
			$is_update = false;
			$is_mapped_variation = false;
			if($wc_product_id = (int) $this->check_wc_product_exists($qbo_product_id,$name,$sku,true)){
				$item_id = (int) $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_product_pairs','wc_product_id','quickbook_product_id',$qbo_product_id);

				//18-05-2017
				if(!$item_id){
					$item_id = (int) $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_variation_pairs','wc_variation_id','quickbook_product_id',$qbo_product_id);
					$is_variation = true;
					$is_mapped_variation = true;
				}

				$p_map_log_txt = '';
				if($item_id){
					$is_update = true;
				}else{
					$p_map_log_txt = ' (Sku or name matched but not mapped)';
				}
				
				if($manual && !$item_id && !$opu){
					$this->save_log($webhook_log_txt.'Import Product Error #'.$qbo_product_id,'Product already exists'.$p_map_log_txt.'. ','Product',0);
				}
				
				//
				if($item_id && $is_variation){
					//$this->save_log($webhook_log_txt.'Import Update Variation Error #'.$qbo_product_id,'Not supported by plugin.','Product',0);
					//return false;
				}

				if(!$item_id){
					return false;
				}
				
				$wc_product_id = $item_id;
			}
			
			#New
			if($opu && !$is_update){
				return false;
			}
			
			/**/
			if($is_update && $is_mapped_variation){
				//return false;
			}
			
			//
			if($is_update && ($this->option_checked('mw_wc_qbo_sync_os_price_fp_update') || $opu)){
				return $this->U_W_P_O_P($product_info,$Item,$wc_product_id,$qbo_product_id,$is_variation,$name,$sku,$type);
			}
			
			$wp_manage_stock = '';
			$wc_product_data = array();
			//ID for update
			if($is_update){
				$wp_manage_stock = get_post_meta($wc_product_id,'_manage_stock',true);
				$wc_product_data['ID'] = $wc_product_id;
				if($manual){
					$this->set_session_val('sync_window_pull_manual_update',true);
				}
			}
			
			//23-06-2017
			$post_excerpt = '';
			$post_content = '';

			$mw_wc_qbo_sync_product_pull_desc_field = $this->get_option('mw_wc_qbo_sync_product_pull_desc_field');
			if($mw_wc_qbo_sync_product_pull_desc_field=='short_description'){
				$post_excerpt = (string) $Item->getDescription();
			}else{
				$post_content = (string) $Item->getDescription();
			}
			
			if($mw_wc_qbo_sync_product_pull_desc_field!='none'){
				if($mw_wc_qbo_sync_product_pull_desc_field=='short_description'){
					$wc_product_data['post_excerpt'] = $post_excerpt;
				}
				
				if($mw_wc_qbo_sync_product_pull_desc_field=='description'){
					$wc_product_data['post_content'] = $post_content;
				}

				if($mw_wc_qbo_sync_product_pull_desc_field == 'name' && !empty($Item->getDescription())){
					$name = (string) $Item->getDescription();
				}
			}			
			
			$wc_product_data['post_title'] = $name;

			$wc_product_data['wp_error'] = true;

			if(!$is_update){
				$post_status = $this->get_option('mw_wc_qbo_sync_product_pull_wc_status');
				$wc_product_data['post_status'] = $post_status;
			}

			$wc_product_meta = array();

			$_tax_status = ($Item->getTaxable())?'taxable':'';
			if(!$is_variation && !$is_update){
				$wc_product_meta['_tax_status'] = $_tax_status;
			}			
			
			$wc_product_meta['_sku'] = $sku;
			
			$_price = ($Item->countUnitPrice())?$Item->getUnitPrice():'';			
			$wc_product_meta['_regular_price'] = $_price;
			
			$is_up_sp = true;
			if($is_update){
				$_sale_price = get_post_meta($wc_product_id,'_sale_price',true);
				/*
				if(!empty($_sale_price) && $_sale_price){
					$is_up_sp = false;
				}
				*/
				
				if(strlen($_sale_price) >0){
					$is_up_sp = false;
				}
			}
			
			if($is_up_sp){
				$wc_product_meta['_price'] = $_price;
			}
			
			#New
			if($is_update){
				unset($wc_product_meta['_regular_price']);
				if($is_up_sp){
					unset($wc_product_meta['_price']);
				}
			}
			
			#$_manage_stock = ($Item->getTrackQtyOnHand())?'yes':'no';
			$_manage_stock = ($type=='Inventory')?'yes':'no';
			
			if(!$is_update){
				$wc_product_meta['_manage_stock'] = $_manage_stock;
			}			
			
			if($_manage_stock=='yes'){
				$_stock = ($Item->countQtyOnHand())?$Item->getQtyOnHand():'';
				if(!$is_update){
					$wc_product_meta['_stock'] = $_stock;
					//21-03-2017
					if($_stock && $_stock>0){
						$wc_product_meta['_stock_status'] = 'instock';
					}else{
						$wc_product_meta['_stock_status'] = 'outofstock';
					}
				}
			}
			
			#New
			if($_manage_stock == 'no' || get_option('woocommerce_manage_stock')  != 'yes'){
				$wc_product_meta['_stock_status'] = 'instock';
			}
			
			//
			if(!$is_variation && !$is_update){
				$wc_product_meta['total_sales'] = '0';
				$wc_product_meta['_downloadable'] = 'no';
				$wc_product_meta['_visibility'] = 'visible';
				$wc_product_meta['_virtual'] = 'no';

				$wc_product_meta['_purchase_note'] = '';
			}
			
			//_regular_price,_sale_price,_featured,_weight,_length,_width,_height,_product_attributes,_sale_price_dates_from,_sale_price_dates_to
			//_sold_individually,_backorders


			$tax_input = array();
			
			if(!$is_variation && $this->get_qbo_company_info('is_category_enabled')){
				$wc_p_cat_id_arr = array();
				if($Item->countSubItem() && $Item->getSubItem()=='true'){
					$ParentRef = $Item->getParentRef();
					$ParentRef = $this->qbo_clear_braces($ParentRef);
					$Level = $Item->getLevel();

					$ParentRef_name = $Item->getParentRef_name();
					$ParentRef_name_arr = array();

					if(strpos( $ParentRef_name, ':' ) !== false){
						$ParentRef_name_arr = explode(':',$ParentRef_name);
					}else{
						$ParentRef_name_arr[] = $ParentRef_name;
					}

					if(is_array($ParentRef_name_arr) && !empty($ParentRef_name_arr)){
						foreach($ParentRef_name_arr as $ParentRef_name){
							$ParentRef_name = esc_sql($ParentRef_name);

							$wc_cat_check_sql = "
							SELECT t.term_id AS id, t.name
							FROM   {$wpdb->terms} t
							LEFT JOIN {$wpdb->term_taxonomy} tt
							ON t.term_id = tt.term_id
							WHERE  tt.taxonomy = 'product_cat'
							AND (t.name = %s OR REPLACE(t.name,':','') = %s)
							LIMIT 0,1
							";
							$wc_cat_check_sql = $wpdb->prepare($wc_cat_check_sql,$ParentRef_name,$ParentRef_name);
							$wc_cat_check_data = $this->get_row($wc_cat_check_sql);

							if(is_array($wc_cat_check_data) && !empty($wc_cat_check_data)){
								$wc_p_cat_id = $wc_cat_check_data['id'];
								$wc_p_cat_id_arr[] = (int) $wc_p_cat_id;
							}
						}
					}
				}

				if(is_array($wc_p_cat_id_arr) && !empty($wc_p_cat_id_arr)){
					$wc_p_cat_id_arr = array_map('intval',$wc_p_cat_id_arr);
					$wc_p_cat_id_arr = array_unique( $wc_p_cat_id_arr );
					$tax_input = array('product_cat'=>$wc_p_cat_id_arr);
				}
			}

			//$this->_p($wc_product_data);
			//$this->_p($wc_product_meta);
			//return false;
			
			$log_title = '';
			$log_details = '';
			$log_status = 0;

			$is_wp_error = false;
			$wp_err_txt = '';
			
			if(!$is_variation){
				$return = $this->save_wp_post('product',$wc_product_data,$wc_product_meta,$tax_input);
			}else{
				if($is_update){
					$return = $this->save_wp_post('product_variation',$wc_product_data,$wc_product_meta,$tax_input);
				}else{
					return false;
				}				
			}
			
			$wpv_txt = ($is_variation)?'Variation':'Product';
			//$this->_p($return);

			if ( is_wp_error( $return ) ) {
				$is_wp_error = true;
				$wp_err_txt.= (string) $return->get_error_message();
				$wp_err_txt.= (string) $return->get_error_data();
			}
			//return false;
			$l_av = 'Add';
			if(!$is_wp_error && (int) $return){
				$post_id = (int) $return;
				if($is_update){
					$log_title.=$webhook_log_txt."Import Product Update #$qbo_product_id\n";
					$log_details.="Product #$qbo_product_id has been updated, WooCommerce {$wpv_txt} ID is #$post_id";
					$l_av = 'Update';
				}else{
					$log_title.=$webhook_log_txt."Import Product #$qbo_product_id\n";
					$log_details.="Product #$qbo_product_id has been imported, WooCommerce {$wpv_txt} ID is #$post_id";					
				}
				
				$log_status = 1;
				$this->save_log($log_title,$log_details,'Product',$log_status,true,$l_av);
				$this->save_qbo_item_local($qbo_product_id,$name,$sku,$type);
				$this->save_item_map($post_id,$qbo_product_id,true,$is_variation);
				
				#New
				if($this->option_checked('mw_wc_qbo_sync_sync_product_images_pp')){
					#For now only for no image products in woocommerce
					$old_img_dtls = $this->get_post_img_data_by_id($post_id);
					if(empty($old_img_dtls)){
						$this->Pull_Product_Image($qbo_product_id,$post_id,$is_variation,$is_update); #Sending $is_update,$is_variation for future use
					}
				}

				return $post_id;
			}else{
				if($is_update){
					$log_title.=$webhook_log_txt."Import Product Update Error #$qbo_product_id\n";
					$l_av = 'Update';
				}else{
					$log_title.=$webhook_log_txt."Import Product Error #$qbo_product_id\n";
				}

				if(isset($wc_product_data['wp_error'])){
					$log_details.="Error:$wp_err_txt";
				}else{
					$log_details.="Error:Wordpress save post error";
				}
				$this->save_log($log_title,$log_details,'Product',$log_status,true,$l_av);
				return false;
			}
		}
	}

	public function save_wp_post($post_type,$post_data,$post_meta_arr=array(),$tax_input=array()){
		if($post_type!='' && is_array($post_data) && !empty($post_data)){
			$wp_error = $this->get_array_isset($post_data,'wp_error',false);
			$post_data['post_type'] = $post_type;
			//03-05-2017
			if(is_array($tax_input) && !empty($tax_input)){
				//$post_data['tax_input'] = $tax_input;
			}
			
			if(isset($post_data['ID']) && (int) $post_data['ID']){
				$return = wp_update_post( $post_data ,$wp_error );
			}else{
				$return = wp_insert_post( $post_data ,$wp_error );
			}

			if((int) $return && is_array($post_meta_arr) && !empty($post_meta_arr)){
				$post_id = (int) $return;
				foreach($post_meta_arr as $key => $val){
					update_post_meta($post_id, $key, $val);
				}
				
				/**/
				if(is_array($tax_input) && isset($tax_input['product_cat']) && is_array($tax_input['product_cat']) && !empty($tax_input['product_cat'])){
					/*
					foreach($tax_input['product_cat'] as $cat_id){
						wp_set_object_terms($post_id, $cat_id, 'product_cat');
					}
					*/
					
					wp_set_object_terms($post_id, $tax_input['product_cat'], 'product_cat',true);
				}
			}
			return $return;
		}
	}

	public function check_wc_product_exists($qbo_product_id,$name,$sku='',$get_wc_product_id=false){
		global $wpdb;
		$is_exists = false;

		//15-06-2017
		$name = htmlspecialchars($name);

		//
		$wc_product_id = 0;

		//map table
		if(!$is_exists){
			if($item_id = $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_product_pairs','wc_product_id','quickbook_product_id',$qbo_product_id)){
				$is_exists = true;
				$wc_product_id = $item_id;
			}
		}

		//18-05-2017
		if(!$is_exists){
			if($item_id = $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_variation_pairs','wc_variation_id','quickbook_product_id',$qbo_product_id)){
				$is_exists = true;
				$wc_product_id = $item_id;
			}
		}

		$sku = (string) $sku;
		if(!$is_exists && $sku!=''){
			$sql = $wpdb->prepare("SELECT `meta_id` , `post_id` FROM {$wpdb->postmeta} WHERE `meta_key` = '_sku' AND `meta_value` = %s AND `meta_value`  !='' ",$sku);
			$check_product = $this->get_row($sql);
			if(is_array($check_product) && !empty($check_product)){
				$is_exists = true;
				$wc_product_id = $check_product['post_id'];
			}
		}

		$p_tbl_chk = false;
		if(!$is_exists){
			//$check_product = $this->get_row($wpdb->prepare("SELECT `ID` FROM {$wpdb->posts} WHERE `post_title` = %s ",$name));
			$check_product = $this->get_row($wpdb->prepare("SELECT `ID` FROM {$wpdb->posts} WHERE REPLACE(post_title,':','') = %s AND `post_type` = 'product' AND post_status NOT IN('auto-draft','trash') ",$name));

			$p_tbl_chk = true;
			if(is_array($check_product) && !empty($check_product)){
				$is_exists = true;
				$wc_product_id = $check_product['ID'];
			}
		}

		//New
		if(!$is_exists){
			$check_product = $this->get_row($wpdb->prepare("SELECT `ID` FROM {$wpdb->posts} WHERE REPLACE(post_title,':','') = %s AND `post_type` = 'product_variation' AND post_status NOT IN('auto-draft','trash') ",$name));

			$p_tbl_chk = true;
			if(is_array($check_product) && !empty($check_product)){
				$is_exists = true;
				$wc_product_id = $check_product['ID'];
			}
		}

		//23-06-2017
		if(!$is_exists && !$p_tbl_chk){
			$wc_product_id = (int) $wc_product_id;
			if($wc_product_id){
				$check_product = $this->get_row($wpdb->prepare("SELECT `ID` FROM {$wpdb->posts} WHERE ID = %d AND `post_type` IN('product','product_variation') AND post_status NOT IN('auto-draft','trash') ",$wc_product_id));
				if(!is_array($check_product) || empty($check_product)){
					$is_exists = false;
				}
			}
		}

		//16-03-2017
		if($get_wc_product_id){
			if($is_exists){
				return $wc_product_id;
			}else{
				return 0;
			}
		}
		return $is_exists;
	}

	//16-03-2017
	public function check_if_real_time_push_enable_for_item($item=''){
		if($item!=''){
			//
			if($item == 'Inventory'){return true;}
			$mw_wc_qbo_sync_rt_push_enable = $this->option_checked('mw_wc_qbo_sync_rt_push_enable');
			if(!$mw_wc_qbo_sync_rt_push_enable){
				return false;
			}
			$mw_wc_qbo_sync_rt_push_items = (string) $this->get_option('mw_wc_qbo_sync_rt_push_items');
			if($mw_wc_qbo_sync_rt_push_items!=''){
				$mw_wc_qbo_sync_rt_push_items = explode(',',$mw_wc_qbo_sync_rt_push_items);
				if(is_array($mw_wc_qbo_sync_rt_push_items) && !empty($mw_wc_qbo_sync_rt_push_items)){
					if(in_array($item,$mw_wc_qbo_sync_rt_push_items)){
						return true;
					}
				}
			}else{
				//return true;
			}
		}
		return false;
	}
	
	public function check_if_real_time_pull_enable_for_item($item=''){
		if($item!=''){
			$mw_wc_qbo_sync_webhook_enable = $this->option_checked('mw_wc_qbo_sync_webhook_enable');
			if(!$mw_wc_qbo_sync_webhook_enable){
				return false;
			}
			$mw_wc_qbo_sync_webhook_items = (string) $this->get_option('mw_wc_qbo_sync_webhook_items');
			if($mw_wc_qbo_sync_webhook_items!=''){
				$mw_wc_qbo_sync_webhook_items = explode(',',$mw_wc_qbo_sync_webhook_items);
				if(is_array($mw_wc_qbo_sync_webhook_items) && !empty($mw_wc_qbo_sync_webhook_items)){
					if(in_array($item,$mw_wc_qbo_sync_webhook_items)){
						return true;
					}
				}
			}else{
				//return true;
			}
		}
		return false;
	}
	
	public function Process_QuickBooks_WebHooks_Request($entities){
		//https://developer.intuit.com/docs/0100_quickbooks_online/0300_references/0000_programming_guide/0020_webhooks
		if(!$this->is_connected()){
			return false;
		}

		if(is_array($entities) && !empty($entities)){
			$is_sku_enabled = $this->get_qbo_company_info('is_sku_enabled');
			$is_category_enabled = $this->get_qbo_company_info('is_category_enabled');

			foreach($entities as $Entity){

				$name = $Entity->name;
				$id = (int) $Entity->id;
				$operation = $Entity->operation;
				$lastUpdated = $Entity->lastUpdated;

				$mw_wc_qbo_sync_webhook_items = $this->get_option('mw_wc_qbo_sync_webhook_items');
				if($mw_wc_qbo_sync_webhook_items!=''){
					$mw_wc_qbo_sync_webhook_items = explode(',',$mw_wc_qbo_sync_webhook_items);
				}

				if(is_array($mw_wc_qbo_sync_webhook_items) && !empty($mw_wc_qbo_sync_webhook_items)){
					$is_rt_item_import = false;
					
					if(in_array('Product',$mw_wc_qbo_sync_webhook_items) || in_array('Inventory',$mw_wc_qbo_sync_webhook_items) || in_array('Category',$mw_wc_qbo_sync_webhook_items) || in_array('Pricing',$mw_wc_qbo_sync_webhook_items)){
						$is_rt_item_import = true;
					}

					if($name == 'Item' && $is_rt_item_import){

						$Context = $this->Context;
						$realm = $this->realm;

						$ItemService = new QuickBooks_IPP_Service_Term();
						$sql = "SELECT * FROM Item WHERE Id = '$id' ";
						$items = $ItemService->query($Context, $realm, $sql);
						$items = ($items && !empty($items))?$items[0]:'';

						//Product Add/Update
						if(in_array('Product',$mw_wc_qbo_sync_webhook_items)){
							if(($operation == 'Create' || $operation=='Update') && $this->check_is_valid_qbo_product($id,$items)){
								$return_id = $this->Qbo_Pull_Product(array('qbo_product_id'=>$id,'webhook'=>true));
							}
						}
						
						//Inventory Update
						/*
						if(in_array('Inventory',$mw_wc_qbo_sync_webhook_items) && !$this->is_plg_lc_p_l() && $is_sku_enabled){
							if($operation == 'Update' && $name == 'Item' && $this->check_is_valid_qbo_inventory($id,$items)){
								$return_id = $this->UpdateWooCommerceInventory(array('qbo_inventory_id'=>$id,'webhook'=>true));
							}
						}
						*/
						
						//Category Add
						if(in_array('Category',$mw_wc_qbo_sync_webhook_items) && !$this->is_plg_lc_p_l() && $is_category_enabled){
							if(($operation == 'Create') && $this->check_is_valid_qbo_category($id,$items)){
								$return_id = $this->Qbo_Pull_Category(array('qbo_category_id'=>$id,'webhook'=>true));
							}
						}
						
						//Only Price Update
						if(in_array('Pricing',$mw_wc_qbo_sync_webhook_items)){
							if($operation=='Update' && $this->check_is_valid_qbo_product($id,$items)){
								$return_id = $this->Qbo_Pull_Product(array('qbo_product_id'=>$id,'webhook'=>true),true);
							}
						}
					}
					
					if($name == 'Customer' && in_array('Customer',$mw_wc_qbo_sync_webhook_items)){
						//
					}

					if($name == 'Invoice' && in_array('Invoice',$mw_wc_qbo_sync_webhook_items)){
						//
					}
					
					//Payment Add
					if($name == 'Payment' && in_array('Payment',$mw_wc_qbo_sync_webhook_items) && !$this->is_plg_lc_p_l() && !$this->is_plg_lc_p_r()){
						if(($operation == 'Create')){
							$return_id = $this->Qbo_Pull_Payment(array('qbo_payment_id'=>$id,'webhook'=>true));
						}
					}

				}
			}
		}
	}

	public function check_is_valid_qbo_product_get_obj($qbo_product_id,$get_only_id=false,$i_obj=null){
		if($this->is_connected()){
			#New
			if(is_object($i_obj) && !empty($i_obj)){
				if($get_only_id){
					return $this->qbo_clear_braces($i_obj->getId());
				}
				return $i_obj;
			}
			
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();
			$sql = "SELECT * FROM Item WHERE Id = '$qbo_product_id' AND Type IN ('Inventory','Service','NonInventory','Group') ";
			$items = $ItemService->query($Context, $realm, $sql);
			if($items && !empty($items)){
				if($get_only_id){
					return $this->qbo_clear_braces($items[0]->getId());
				}
				return $items[0];
			}
		}
		return false;
	}

	public function check_is_valid_qbo_inventory($qbo_inventory_id,$item_obj=''){
		if($this->is_connected()){
			if(is_object($item_obj) && !empty($item_obj)){
				if($item_obj->countType() && $item_obj->getType()=='Inventory'){
					return true;
				}
				return false;
			}

			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();
			$sql = "SELECT * FROM Item WHERE Type = 'Inventory' AND Id = '$qbo_inventory_id' ";
			$items = $ItemService->query($Context, $realm, $sql);
			if($items && !empty($items)){
				return true;
			}
		}
		return false;
	}

	//03-05-2017
	public function check_is_valid_qbo_category($qbo_inventory_id,$item_obj=''){
		if($this->is_connected()){
			if(is_object($item_obj) && !empty($item_obj)){
				if($item_obj->countType() && $item_obj->getType()=='Category'){
					return true;
				}
				return false;
			}

			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();
			$sql = "SELECT * FROM Item WHERE Type = 'Category' AND Id = '$qbo_inventory_id' ";
			$items = $ItemService->query($Context, $realm, $sql);
			if($items && !empty($items)){
				return true;
			}
		}
		return false;
	}

	public function check_is_valid_qbo_product($qbo_product_id,$item_obj=''){
		if($this->is_connected()){
			if(is_object($item_obj) && !empty($item_obj)){
				//
				if($item_obj->countType() && ($item_obj->getType()=='NonInventory' || $item_obj->getType()=='Inventory' || $item_obj->getType()=='Service' || $item_obj->getType()=='Group')){
					return true;
				}
				return false;
			}

			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();
			$sql = "SELECT * FROM Item WHERE Id = '$qbo_product_id' AND Type IN ('Inventory','Service','NonInventory','Group') ";
			$items = $ItemService->query($Context, $realm, $sql);
			if($items && !empty($items)){
				return true;
			}
		}
		return false;
	}

	//06-06-2017
	public function VariationUpdateQboInventory($inventory_data){
		if($this->is_connected()){
			if(!$this->get_qbo_company_info('is_sku_enabled')){
				return false;
			}
			global $wpdb;
			$wc_inventory_id = (int) $this->get_array_isset($inventory_data,'wc_inventory_id',0);
			$manual = $this->get_array_isset($inventory_data,'manual',false);
			if($wc_inventory_id){
				$ext_log = '';
				$variation = get_post($wc_inventory_id);

				if(!is_object($variation) || empty($variation)){
					if($manual){
						$this->save_log('Export Variation Inventory Error #'.$wc_inventory_id,'Woocommerce variation not found!','Inventory',0);
					}
					return false;
				}

				if($variation->post_type!='product_variation'){
					if($manual){
						$this->save_log('Export Variation Inventory Error #'.$wc_inventory_id,'Woocommerce variation is not valid.','Inventory',0);
					}
					return false;
				}

				$variation_meta = get_post_meta($wc_inventory_id);

				if(!$variation_meta){
					if($manual){
						$this->save_log($webhook_log_txt.'Export Variation Inventory Error #'.$wc_inventory_id,'WooCommerce variation information not found. '.$ext_log,'Inventory',0);
					}
					return false;
				}

				$_manage_stock = (isset($variation_meta['_manage_stock'][0]))?$variation_meta['_manage_stock'][0]:'no';
				$_backorders = (isset($variation_meta['_backorders'][0]))?$variation_meta['_backorders'][0]:'no';
				$_stock = (isset($variation_meta['_stock'][0]))?$variation_meta['_stock'][0]:0;

				if($_manage_stock!='yes'){
					if($manual){
						$this->save_log('Export Variation Inventory Error #'.$wc_inventory_id,'Invalid Woocommerce inventory. ','Inventory',0);
					}
					return false;
				}
				
				$map_data = $this->get_row($wpdb->prepare("SELECT `quickbook_product_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `wc_variation_id` = %d AND `quickbook_product_id` > 0 ",$wc_inventory_id));
				$quickbook_product_id = 0;
				if(is_array($map_data) && !empty($map_data)){
					$quickbook_product_id = (int) $map_data['quickbook_product_id'];
				}
				if(!$quickbook_product_id){
					if($manual){
						$this->save_log('Export Variation Inventory Error #'.$wc_inventory_id,'QuickBooks inventory not found. ','Inventory',0);
					}
					return false;
				}

				$Context = $this->Context;
				$realm = $this->realm;

				$ItemService = new QuickBooks_IPP_Service_Item();
				$sql = "SELECT * FROM Item WHERE Type = 'Inventory' AND Id = '$quickbook_product_id' ";
				$items = $ItemService->query($Context, $realm, $sql);

				if(!$items || empty($items)){
					if($manual){
						$this->save_log('Export Variation Inventory Error #'.$wc_inventory_id,'Invalid QuickBooks inventory. ','Inventory',0);
					}
					return false;
				}

				$Inventory = $items[0];
				$QtyOnHand = $Inventory->getQtyOnHand();

				if($QtyOnHand!=$_stock){
					$Inventory->setQtyOnHand($_stock);
					$t_date = $this->now('Y-m-d');
					$Inventory->setInvStartDate($t_date);
					
					/**/
					$Inventory->remove('TaxClassificationRef');
					$Inventory->remove('TaxClassificationRef_name');
					
					//$this->set_show_all_error();
					if ($resp = $ItemService->update($Context, $realm, $Inventory->getId(), $Inventory)){
						$qbo_inv_id = $this->qbo_clear_braces($Inventory->getId());
						$log_title ="Update Variation Inventory #$wc_inventory_id\n";
						//$log_details ="Variation Inventory #$wc_inventory_id has been updated, QuickBooks Inventory ID is #$qbo_inv_id";
						$log_details ="QuickBooks Inventory {#$qbo_inv_id} updated from {$QtyOnHand} to {$_stock}";
						$this->save_log($log_title,$log_details,'Inventory',1,true,'Update');
						$this->add_qbo_item_obj_into_log_file('Variation Inventory Update',$inventory_data,$Inventory,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
						return $qbo_inv_id;

					}else{
						$res_err = $ItemService->lastError($Context);
						$log_title ="Update Variation Inventory Error #$wc_inventory_id\n";
						$log_details ="Error:$res_err";
						$this->save_log($log_title,$log_details,'Inventory',0,true,'Update');
						$this->add_qbo_item_obj_into_log_file('Variation Inventory Update',$inventory_data,$Inventory,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
					}
				}else{
					$log = "Stocks on both ends are same (".$QtyOnHand.").".$ext_log;
					$this->save_log('Export Variation Inventory #'.$wc_inventory_id,$log,'Inventory',1);
				}
			}
		}
	}
	
	//09-05-2017
	public function UpdateQboInventory($inventory_data){		
		if($this->is_connected()){
			if(!$this->get_qbo_company_info('is_sku_enabled')){
				return false;
			}
			global $wpdb;
			$wc_inventory_id = (int) $this->get_array_isset($inventory_data,'wc_inventory_id',0);
			$manual = $this->get_array_isset($inventory_data,'manual',false);
			if($wc_inventory_id){
				$ext_log = '';
				$_product = wc_get_product( $wc_inventory_id );
				
				if(empty($_product)){
					$this->save_log('Export Inventory Error #'.$wc_inventory_id,'Woocommerce product not found. ','Inventory',0);
					return false;
				}

				$product_meta = get_post_meta($wc_inventory_id);
				
				if(!$product_meta){
					if($manual){
						$this->save_log($webhook_log_txt.'Export Inventory Error #'.$wc_inventory_id,'WooCommerce product information not found. '.$ext_log,'Inventory',0);
					}
					return false;
				}

				$_manage_stock = (isset($product_meta['_manage_stock'][0]))?$product_meta['_manage_stock'][0]:'no';
				$_backorders = (isset($product_meta['_backorders'][0]))?$product_meta['_backorders'][0]:'no';
				$_stock = (isset($product_meta['_stock'][0]))?$product_meta['_stock'][0]:0;

				if($_manage_stock!='yes'){
					if($manual){
						$this->save_log('Export Inventory Error #'.$wc_inventory_id,'Invalid Woocommerce inventory. ','Inventory',0);
					}
					return false;
				}

				$map_data = $this->get_row($wpdb->prepare("SELECT `quickbook_product_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `wc_product_id` = %d AND `quickbook_product_id` > 0 ",$wc_inventory_id));
				$quickbook_product_id = 0;
				if(is_array($map_data) && !empty($map_data)){
					$quickbook_product_id = (int) $map_data['quickbook_product_id'];
				}
				
				if(!$quickbook_product_id){
					if($manual){
						$this->save_log('Export Inventory Error #'.$wc_inventory_id,'QuickBooks inventory not found. ','Inventory',0);
					}
					return false;
				}

				$Context = $this->Context;
				$realm = $this->realm;

				$ItemService = new QuickBooks_IPP_Service_Item();
				$sql = "SELECT * FROM Item WHERE Type = 'Inventory' AND Id = '$quickbook_product_id' ";
				$items = $ItemService->query($Context, $realm, $sql);
				
				if(!$items || empty($items)){
					if($manual){
						$this->save_log('Export Inventory Error #'.$wc_inventory_id,'Invalid QuickBooks inventory. ','Inventory',0);
					}
					return false;
				}

				$Inventory = $items[0];
				$QtyOnHand = $Inventory->getQtyOnHand();

				if($QtyOnHand!=$_stock){
					$Inventory->setQtyOnHand($_stock);
					$t_date = $this->now('Y-m-d');
					$Inventory->setInvStartDate($t_date);
					
					/**/
					$Inventory->remove('TaxClassificationRef');
					$Inventory->remove('TaxClassificationRef_name');
					
					//$this->set_show_all_error();
					if ($resp = $ItemService->update($Context, $realm, $Inventory->getId(), $Inventory)){
						$qbo_inv_id = $this->qbo_clear_braces($Inventory->getId());
						$log_title ="Update Inventory #$wc_inventory_id\n";
						//$log_details ="Inventory #$wc_inventory_id has been updated, QuickBooks Inventory ID is #$qbo_inv_id";
						$log_details ="QuickBooks Inventory #{$qbo_inv_id} updated from {$QtyOnHand} to {$_stock}";
						$this->save_log($log_title,$log_details,'Inventory',1,true,'Update');
						$this->add_qbo_item_obj_into_log_file('Inventory Update',$inventory_data,$Inventory,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
						return $qbo_inv_id;

					}else{
						$res_err = $ItemService->lastError($Context);
						$log_title ="Update Inventory Error #$wc_inventory_id\n";
						$log_details ="Error:$res_err";
						$this->save_log($log_title,$log_details,'Inventory',0,true,'Update');
						$this->add_qbo_item_obj_into_log_file('Inventory Update',$inventory_data,$Inventory,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
					}
				}else{
					$log = "Stocks on both ends are same (".$QtyOnHand.").".$ext_log;
					$this->save_log('Export Inventory #'.$wc_inventory_id,$log,'Inventory',1);
				}

			}
		}
	}

	public function set_show_all_error(){
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
	}

	//21-02-2017
	public function UpdateWooCommerceInventory($inventory_data){
		if($this->is_connected()){
			/**/
			if($this->is_plg_lc_p_l()){
				//return false; #lpa
			}
			
			global $wpdb;
			$qbo_inventory_id = (int) $this->get_array_isset($inventory_data,'qbo_inventory_id',0);
			$manual = $this->get_array_isset($inventory_data,'manual',false);

			//
			$webhook_log_txt = '';
			$webhook = $this->get_array_isset($inventory_data,'webhook',false);
			if($webhook){
				$webhook_log_txt = 'Webhook ';
				//$manual = true;
			}

			$cron = $this->get_array_isset($inventory_data,'cron',false);
			if($cron){
				$webhook_log_txt = 'Cron ';
			}

			if($qbo_inventory_id){
				$Context = $this->Context;
				$realm = $this->realm;

				$ItemService = new QuickBooks_IPP_Service_Term();
				
				if(!isset($inventory_data['QtyOnHand'])){
					$sql = "SELECT Name, QtyOnHand FROM Item WHERE Type = 'Inventory' AND Id = '$qbo_inventory_id' ";
					$items = $ItemService->query($Context, $realm, $sql);

					if(!$items || empty($items)){
						if($manual){
							$this->save_log($webhook_log_txt.'Import Inventory Error #'.$qbo_inventory_id,'Invalid QuickBooks inventory. ','Inventory',0);
						}
						return false;
					}
					
					$Inventory = $items[0];
					$QtyOnHand = $Inventory->getQtyOnHand();					
					$qbo_product_name =  $Inventory->getName();
					
				}else{
					$QtyOnHand = $this->get_array_isset($inventory_data,'QtyOnHand',0);
					$qbo_product_name = $this->get_array_isset($inventory_data,'Name','');
				}				
				
				$ext_log = "\n".'Name: '.$qbo_product_name;
				
				//get_row
				$map_data = $this->get_data($wpdb->prepare("SELECT `wc_product_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `quickbook_product_id` = %d AND `wc_product_id` > 0 ",$qbo_inventory_id));
				
				//variation
				$is_variation = false;
				if(empty($map_data)){
					//get_row
					$map_data = $this->get_data($wpdb->prepare("SELECT `wc_variation_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `quickbook_product_id` = %d AND `wc_variation_id` > 0 ",$qbo_inventory_id));
					$is_variation = true;
				}				
				
				
				if(empty($map_data)){
					if($manual){
						$this->save_log($webhook_log_txt.'Import Inventory Error #'.$qbo_inventory_id,'WooCommerce product not found. '.$ext_log,'Inventory',0);
					}
					return false;
				}				
				
				$wc_product_id = 0;
				
				//Multiple Support
				if(is_array($map_data)){
					$is_v_parent_stock_status_updated = false;					
					foreach($map_data as $map_data_c){
						$wc_product_id = 0;
						$is_variation_parent = false;
						$parent_id = 0;

						if($is_variation){
							$wc_variation_id = $map_data_c['wc_variation_id'];
							$variation_manage_stock = get_post_meta($wc_variation_id,'_manage_stock',true);
							
							$parent_id = (int) $this->get_field_by_val($wpdb->posts,'post_parent','ID',$wc_variation_id);
							
							if($variation_manage_stock=='yes'){
								$wc_product_id = $wc_variation_id;
							}else{								
								if($parent_id){
									$wc_product_id = $parent_id;
									$is_variation_parent = true;
								}

							}

						}else{
							$wc_product_id = $map_data_c['wc_product_id'];
						}
						
						$product_meta = get_post_meta($wc_product_id);
						if(!$product_meta){
							if($manual){
								$this->save_log($webhook_log_txt.'Import Inventory Error #'.$qbo_inventory_id,'WooCommerce product information not found. '.$ext_log,'Inventory',0);
							}
							return false;
						}

						$_manage_stock = (isset($product_meta['_manage_stock'][0]))?$product_meta['_manage_stock'][0]:'no';
						$_backorders = (isset($product_meta['_backorders'][0]))?$product_meta['_backorders'][0]:'no';
						$_stock = (isset($product_meta['_stock'][0]))?$product_meta['_stock'][0]:0;


						$is_valid_wc_inventory = false;

						if($_manage_stock=='yes'){
							$is_valid_wc_inventory = true;
						}

						if(!$is_valid_wc_inventory){
							if($manual){
								$this->save_log($webhook_log_txt.'Import Inventory Error #'.$qbo_inventory_id,'WooCommerce inventory not valid. '.$ext_log,'Inventory',0);
							}
							return false;
						}

						//Parent
						if($is_variation_parent){
							$parent_qbo_inventory_id = (int) $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_product_pairs','quickbook_product_id','wc_product_id',$wc_product_id);

							if(!$parent_qbo_inventory_id){
								if($manual){
									$this->save_log($webhook_log_txt.'Import Inventory Error #'.$qbo_inventory_id,'Invalid QuickBooks inventory. ','Inventory',0);
								}
								return false;
							}

							$sql = "SELECT QtyOnHand FROM Item WHERE Type = 'Inventory' AND Id = '$parent_qbo_inventory_id' ";
							$items = $ItemService->query($Context, $realm, $sql);

							if(!$items || empty($items)){
								if($manual){
									$this->save_log($webhook_log_txt.'Import Inventory Error #'.$qbo_inventory_id,'Invalid QuickBooks inventory. ','Inventory',0);
								}
								return false;
							}

							$Inventory = $items[0];
							$QtyOnHand = $Inventory->getQtyOnHand();
						}

						if($QtyOnHand!=$_stock){
							$_stock = number_format(floatval($_stock),2);
							//$return = update_post_meta($wc_product_id, '_stock', $QtyOnHand);
							
							/*
							$always_ss_update = true;
							if($always_ss_update || $this->option_checked('mw_wc_qbo_sync_invnt_pull_set_prd_stock_sts')){
								if($QtyOnHand && $QtyOnHand>0){
									update_post_meta($wc_product_id, '_stock_status', 'instock');
									
									if($parent_id && !$is_v_parent_stock_status_updated){
										$is_v_parent_stock_status_updated = true;
										update_post_meta($parent_id, '_stock_status', 'instock');
									}
								}else{
									update_post_meta($wc_product_id, '_stock_status', 'outofstock');
									
									if($parent_id && !$is_v_parent_stock_status_updated){
										$is_v_parent_stock_status_updated = true;
										update_post_meta($parent_id, '_stock_status', 'outofstock');
									}
								}
							}
							*/
							
							wc_update_product_stock($wc_product_id,$QtyOnHand);
							
							//
							$this->set_session_val('prevent_rt_inventory_push_ot',1);
							
							$log = "WooCommerce Product #$wc_product_id stock updated from $_stock to $QtyOnHand ".$ext_log;
							$this->save_log($webhook_log_txt.'Import Inventory #'.$qbo_inventory_id,$log,'Inventory',1);
						}else{
							/*
							$log = "Stocks on both ends are same (".$QtyOnHand.").".$ext_log;
							$this->save_log($webhook_log_txt.'Import Inventory #'.$qbo_inventory_id,$log,'Inventory',1);
							*/
						}
					}
				}
				
				return $wc_product_id;

			}
		}
	}

	//12-06-2017
	public function get_mapped_acof_qbo_item_from_val($acof_txt=''){
		$qp_id = 0;
		$acof_txt = trim($acof_txt);
		if($acof_txt!=''){
			$acof_map = $this->get_option('mw_wc_qbo_sync_compt_acof_wf_qi_map');
			if($acof_map!=''){
				$acof_map_arr = unserialize($acof_map);
				if(is_array($acof_map_arr) && !empty($acof_map_arr)){
					foreach($acof_map_arr as $k =>$v){
						$k = base64_decode($k);
						if($acof_txt==$k){
							$qp_id = (int) $v;
							break;
						}
					}
				}

			}
		}
		return $qp_id;
	}
	
	/**/
	public function get_woo_v_name_trimmed($v_name){
		$v_name = trim($v_name);
		//50 24 25
		if(!empty($v_name) && strlen($v_name) > 100){
			$fs = substr($v_name, 0, 49);
			$ls = substr($v_name, -50);
			$v_name = $fs.' '.$ls;
		}
		return $v_name;
	}
	
	//04-01-2018
	public function get_variation_name_from_id($v_name,$p_name='',$v_id=0,$p_id=0){
		$v_name = trim($v_name);$p_name = trim($p_name);
		/*New*/
		//return $v_name;
		
		$v_id = intval($v_id);$p_id = intval($p_id);
		if($v_name!='' && $v_id>0){
			global $wpdb;
			if(!$p_id || empty($p_name)){
				$p_data = $this->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->posts}` WHERE  `ID` = %d AND `post_type` = 'product_variation'  ",$v_id));
				if(is_array($p_data) && !empty($p_data)){
					$p_id = (int) $p_data['post_parent'];
					//$p_name = $p_data['post_title'];
					$p_name = $this->get_field_by_val($wpdb->posts,'post_title','ID',$p_id);
				}
			}
			
			if($p_id>0 && !empty($p_name)){
				$_product_attributes_a = get_post_meta($p_id,'_product_attributes',true);
				if(is_array($_product_attributes_a) && !empty($_product_attributes_a)){
					$pa_k_a = array();
					foreach($_product_attributes_a as $pak => $pav){
						$pa_k_a[] = $pak;
					}
					
					$v_meta = get_post_meta($v_id);					
					
					if(is_array($v_meta) && !empty($v_meta)){
						$v_av_pa = array();
						foreach($v_meta as $vmk => $vmv){								
							if (substr($vmk, 0, strlen('attribute_')) == 'attribute_') {
								$vmk = substr($vmk, strlen('attribute_'));
								if(in_array($vmk,$pa_k_a)){
									$vmv = ($vmv[0])?$vmv[0]:'';
									if(!is_numeric($vmv)){
										$vmv = ucfirst($vmv);
									}
									$p_name.=' - '.$vmv;
									/*
									if($this->start_with($vmk,'pa_')){
										$vmk = $this->sanitize(substr($vmk,3));
									}
									$v_av_pa[$vmk] = $vmv;
									*/
								}
							}								
						}
					}
					
					return $p_name;
				}
			}
		}
		return $v_name;
	}
	
	public function get_mapped_qbo_items_from_wc_items($wc_items=array(),$real_time_data=false,$acof_txt='',$tax_details=array(),$invoice_data=array()){
		//$this->_p($wc_items);return false;
		$qbo_items = array();
		if(is_array($wc_items) && !empty($wc_items)){
			global $wpdb;
			$wc_product_id = (int) $wc_items['product_id'];
			//07-03-2017
			$map_data = array();
			//12-06-2017
			$acof_map_product = false;
			if($acof_txt!=''){
				$acof_p_id = (int) $this->get_mapped_acof_qbo_item_from_val($acof_txt);
				if($acof_p_id){
					$map_data['itemid'] = $acof_p_id;
					$acof_map_product = true;
				}
			}
			$wc_variation_id = 0;
			if(empty($map_data)){
				$wc_variation_id = (isset($wc_items['variation_id']))?(int) $wc_items['variation_id']:0;
				if($wc_variation_id){
					$map_data = $this->get_row($wpdb->prepare("SELECT `quickbook_product_id` AS itemid , `class_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `wc_variation_id` = %d AND `quickbook_product_id` > 0 ",$wc_variation_id));
				}
			}
			
			if(empty($map_data)){
				$map_data = $this->get_row($wpdb->prepare("SELECT `quickbook_product_id` AS itemid , `class_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `wc_product_id` = %d AND `quickbook_product_id` > 0 ",$wc_product_id));
			}
			
			//24-05-2017
			if(!empty($map_data)){
				$qbo_item_id = (int) $map_data['itemid'];
				$product_type = $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','product_type','itemid',$qbo_item_id);
				$map_data['product_type'] = $product_type;
			}

			if(empty($map_data)){
				$qbo_default_product_id = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
				$map_data = $this->get_row($wpdb->prepare("SELECT `itemid` , `product_type` FROM `".$wpdb->prefix."mw_wc_qbo_sync_qbo_items` WHERE `itemid` = %d ",$qbo_default_product_id));
			}
			
			//24-07-2017
			$Description = $this->get_array_isset($wc_items,'name','');
			$wc_variation_id = (isset($wc_items['variation_id']))?(int) $wc_items['variation_id']:0;
			
			/**/
			$inv_sr_qb_lid_val = $this->get_option('mw_wc_qbo_sync_inv_sr_qb_lid_val');
			
			if($inv_sr_qb_lid_val == 'woo_pv_sdc'){
				if($wc_variation_id > 0){
					$Description = $this->get_field_by_val($wpdb->posts,'post_excerpt','ID',$wc_variation_id);
					if(empty($Description)){
						$Description = $this->get_field_by_val($wpdb->posts,'post_excerpt','ID',$wc_product_id);
					}
				}else{
					$Description = $this->get_field_by_val($wpdb->posts,'post_excerpt','ID',$wc_product_id);
				}
				//post_content
			}
			
			#New
			if($inv_sr_qb_lid_val == 'woo_pbs'){
				$p_v_id = ($wc_variation_id > 0)?$wc_variation_id:$wc_product_id;
				$pbs = get_post_meta($p_v_id,'_backorders',true);
				if($pbs == 'yes'){
					//$Description = __('Allow','mw_wc_qbo_sync');
					$Description = 'Allow';
				}elseif($pbs == 'notify'){
					$Description = 'Allow, but notify customer';
				}else{
					$Description = 'Do not allow';
				}
			}
			
			#New
			if($inv_sr_qb_lid_val == 'mp_qbp_dc' && !empty($map_data)){
				$qbo_item_id = (int) $map_data['itemid'];
				$Description = $this->get_qb_prd_rt_field_v($qbo_item_id,'description');
			}
			
			#New
			if($inv_sr_qb_lid_val == 'no_desc'){
				$Description = '';
			}
			
			$o_li_meta_data = '';
			
			$enable_variation_attr_desc = false;
			if($wc_variation_id && $enable_variation_attr_desc){
				$att_keys = array();
				$att_keys_str = '';
				foreach($wc_items as $wk => $wv){
					if($this->start_with($wk,'pa_')){
						$wk_k = $this->sanitize(substr($wk,3));
						$att_keys_str.="'{$wk_k}',";
					}
				}
				if($att_keys_str!=''){
					$att_keys_str = substr($att_keys_str,0,-1);
					$atl_data = $this->get_data("SELECT attribute_name , attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id > 0 AND `attribute_name` IN (".$att_keys_str.") ");
					if(is_array($atl_data) && !empty($atl_data)){
						$atl_data_kv = array();
						foreach($atl_data as $ad){
							$atl_data_kv[$ad['attribute_name']] = $ad['attribute_label'];
						}
						$atr_v_txt = '';
						foreach($wc_items as $wk => $wv){
							if($this->start_with($wk,'pa_')){
								$wk_k = $this->sanitize(substr($wk,3));
								$atr_v_txt.=(isset($atl_data_kv[$wk_k]))?$atl_data_kv[$wk_k].': '.$wv.', ':'';
							}
						}
						if($atr_v_txt!=''){
							$atr_v_txt = trim($atr_v_txt);
							$atr_v_txt = substr($atr_v_txt,0,-1);
							$Description.=' - '.$atr_v_txt;
							//$o_li_meta_data.=PHP_EOL.$atr_v_txt;
						}
					}
				}
			}
			
			//Visual Products Configurator
			if($this->is_plugin_active('visual-product-configurator','vpc') && $this->option_checked('mw_wc_qbo_sync_enable_wc_vpc_epod')){
				$ext_options_str = '';
				if(isset($wc_items['vpc-cart-data']) && $wc_items['vpc-cart-data']!=''){
					$vpc_cart_data = unserialize($wc_items['vpc-cart-data']);
					if(is_array($vpc_cart_data) && !empty($vpc_cart_data)){
						foreach($vpc_cart_data as $vpc_k => $vpc_v){
							if(is_array($vpc_v)){
								$ext_options_str.=$vpc_k.': '.implode('',$vpc_v).PHP_EOL;
							}else{
								$ext_options_str.=$vpc_k.': '.$vpc_v.PHP_EOL;
							}
						}
					}
				}
				
				if($ext_options_str!=''){
					$Description.=PHP_EOL.$ext_options_str;
					$o_li_meta_data.=PHP_EOL.$ext_options_str;
				}
			}
			
			//WooCommerce TM Extra Product Options
			if($this->is_plugin_active('woocommerce-tm-extra-product-options','tm-woo-extra-product-options') && $this->option_checked('mw_wc_qbo_sync_compt_p_wtmepo')){
				$ext_options_str = '';
				if(isset($wc_items['tmcartepo_data']) && $wc_items['tmcartepo_data']!=''){
					$tmcartepo_data = unserialize($wc_items['tmcartepo_data']);
					if(is_array($tmcartepo_data) && !empty($tmcartepo_data)){
						foreach($tmcartepo_data as $ed){
							$ext_options_str.=$ed['name'].': '.$ed['value'].PHP_EOL;
						}
					}
				}
				if($ext_options_str!=''){
					$Description.=PHP_EOL.$ext_options_str;
					$o_li_meta_data.=PHP_EOL.$ext_options_str;
				}
			}
			
			//WooCommerce Product Add-ons
			$pv_adn_arr = array();
			if($this->is_plugin_active('woocommerce-product-addons') && $this->option_checked('mw_wc_qbo_sync_compt_p_wapao')){
				if($wc_product_id>0){					
					//Product Addons
					$_product_addons = get_post_meta($wc_product_id,'_product_addons',true);
					/*
					if(empty($_product_addons)){
						if($wc_variation_id>0){
							$_product_addons = get_post_meta($wc_variation_id,'_product_addons',true);
						}
					}
					*/
					if(is_array($_product_addons) && !empty($_product_addons)){
						foreach($_product_addons as $pa_d){
							if(is_array($pa_d) && isset($pa_d['name']) && $pa_d['name']!=''){
								$pv_adn_arr[] = $pa_d['name'];
							}
						}
					}
					
					//Global Addons
					$_p_a_eg = get_post_meta($wc_product_id,'_product_addons_exclude_global',true);
					if(!$_p_a_eg){
						$ga_posts = $this->get_data("SELECT ID FROM {$wpdb->posts} WHERE `post_type` = 'global_product_addon' AND `post_status` NOT IN ('auto-draft','trash','draft') ");
						if(is_array($ga_posts) && !empty($ga_posts)){
							foreach($ga_posts as $gp){
								$gp_product_addons = get_post_meta($gp['ID'],'_product_addons',true);
								if(is_array($gp_product_addons) && !empty($gp_product_addons)){
									foreach($gp_product_addons as $g_pa_d){
										if(is_array($g_pa_d) && isset($g_pa_d['name']) && $g_pa_d['name']!=''){
											$pv_adn_arr[] = $g_pa_d['name'];
										}
									}
								}
							}
						}
					}					
					
					if(!empty($pv_adn_arr)){
						$pv_adn_arr = array_unique($pv_adn_arr);
					}
					
					if(!empty($pv_adn_arr)){
						$p_addon_str = '';
						foreach($wc_items as $wk => $wv){
							foreach($pv_adn_arr as $pa){
								if($this->start_with($wk,$pa)){
									$p_addon_str.=$wk.': '.$wv.PHP_EOL;
								}
							}
						}
						if($p_addon_str!=''){
							$Description.=PHP_EOL.$p_addon_str;
							$o_li_meta_data.=PHP_EOL.$p_addon_str;
						}
					}
					
					
					//_product_addons_exclude_global
				}
			}
			
			/**/
			if($this->option_checked('mw_wc_qbo_sync_wolim_iqilid_desc')){
				$solm_arr = array(
					'name', '_qty', 'qty',
					'unit_price', 'product_id', 'variation_id',
					'tax_class', 'line_subtotal', 'line_subtotal_tax',
					'line_total', 'line_tax', 'line_tax_data',
					'wc_avatax_rate', 'wc_avatax_code', 'wc_cog_item_cost',
					'wc_cog_item_total_cost', 'reduced_stock', 'type','order_item_id',
					'tmcartepo_data', 'vpc-cart-data', '_order_item_wh',
					
					'line_subtotal_base_currency', 'line_total_base_currency', 'line_tax_base_currency',
				);
				
				#New
				if(is_array($pv_adn_arr) && !empty($pv_adn_arr)){
					foreach($pv_adn_arr as $paa){
						$solm_arr[] = $paa;
					}
				}
				
				$oaslim_a = array();
				$oaslim = $this->get_option('mw_wc_qbo_sync_oaslim_iqbld');
				if(!empty($oaslim)){
					$oaslim_a = explode(',',$oaslim);
					if(is_array($oaslim_a) && !empty($oaslim_a)){
						$oaslim_a = array_map('trim',$oaslim_a);
					}
				}
				
				$ext_olim_d = '';
				foreach($wc_items as $wk => $wv){
					if(empty($wv)){continue;}
					
					$is_olim_lid_add = true;
					if(in_array($wk,$solm_arr)){
						$is_olim_lid_add = false;
					}
					
					//
					if(!empty($oaslim_a) && !in_array($wk,$oaslim_a)){
						$is_olim_lid_add = false;
					}
					
					$is_va_pa_olim = false;
					if($wc_variation_id && $this->start_with($wk,'pa_')){
						//$is_olim_lid_add = false;
						$is_va_pa_olim = true;
					}
					
					if($is_olim_lid_add && !$is_va_pa_olim){
						$olim_csd = @unserialize($wv);
						if ($wv === 'b:0;' || $olim_csd !== false) {
							$is_olim_lid_add = false;
							/**/
							$is_sv_a = false;
							if($wk == 'product_attributes'){
								$is_sv_a = true;
								
							}
							
							if($is_sv_a){
								if(is_array($olim_csd) && !empty($olim_csd)){									
									$iaa = false;
									if(array_keys($olim_csd) !== range(0, count($olim_csd) - 1)){
										$iaa = true;										
									}
									
									foreach($olim_csd as $sak => $sav){
										$sav = trim($sav);
										if($iaa){
											$sak = ucfirst(str_replace('_',' ',$sak));	
											$ext_olim_d.=$sak.': '.$sav.PHP_EOL;
										}else{
											$ext_olim_d.=$sav.PHP_EOL;
										}
									}
								}
							}							
						}
					}
					
					if($is_olim_lid_add){
						/*
						$eolm_k = ucfirst(str_replace('_',' ',$wk));
						$eolm_v = trim($wv);
						$ext_olim_d.=$eolm_k.': '.$eolm_v.PHP_EOL;
						*/
						if($is_va_pa_olim){
							$eolm_k = wc_attribute_label($wk);
						}else{
							$eolm_k = ucfirst(str_replace('_',' ',$wk));
						}
						
						$eolm_v = trim($wv);
						if($is_va_pa_olim){
							//$eolm_v = ucfirst($wv);
							$eolm_v = ucfirst(str_replace('-',' ',$wv));
						}
						
						$ext_olim_d.=$eolm_k.': '.$eolm_v.PHP_EOL;
						
					}
				}
				
				if($ext_olim_d!=''){
					$Description.=PHP_EOL.$ext_olim_d;
					$o_li_meta_data.=PHP_EOL.$ext_olim_d;
				}
				
				//
				$is_lim_sku_ad = true;
				if(!empty($oaslim_a) && !in_array('sku',$oaslim_a)){
					$is_lim_sku_ad = false;
				}
				
				$lim_sku = '';
				if($is_lim_sku_ad){
					if($wc_variation_id > 0){
						$lim_sku = $this->get_field_by_val($wpdb->prefix.'wc_product_meta_lookup','sku','product_id',$wc_variation_id);
					}
					
					if(empty($lim_sku) && $wc_product_id > 0){
						$lim_sku = $this->get_field_by_val($wpdb->prefix.'wc_product_meta_lookup','sku','product_id',$wc_product_id);
					}
				}
				
				if(!empty($lim_sku)){
					$Description.=PHP_EOL.'SKU: '.$lim_sku;
				}
			}

			# Event Tickets | Event Tickets Plus
			if($this->is_plugin_active('event-tickets') || $this->is_plugin_active('event-tickets-plus')){				
				if(isset($invoice_data['_tribe_has_tickets']) && $invoice_data['_tribe_has_tickets'] == 1){					
					if(isset($wc_items['tribe_wooticket_attendee_optout']) && $wc_items['tribe_wooticket_attendee_optout'] == 'yes'){
						$wc_inv_id = (int) $invoice_data['wc_inv_id'];
						if($wc_inv_id > 0){
							$q =  $wpdb->prepare("SELECT `post_id` FROM {$wpdb->postmeta} WHERE `meta_key` = '_tribe_wooticket_order' AND `meta_value` = %d",$wc_inv_id);
							$e_ids_data = $this->get_data($q);
							if(is_array($e_ids_data) && !empty($e_ids_data)){
								$e_key_data = array(
									'_unique_id' => 'Ticket ID',
									'_tribe_tickets_full_name' => 'Name',
									'_tribe_tickets_email' => 'Email',
									'_tribe_wooticket_security_code' => 'Security Code',									
								);

								$event_data = [];
								foreach($e_ids_data as $ids){
									$e_id = (int) $ids['post_id'];
									if($e_id > 0){
										$e_m_data = get_post_meta($e_id);
										if(is_array($e_m_data) && !empty($e_m_data)){
											$t_event_d = [];
											foreach($e_m_data as $emk => $emv){
												if(isset($e_key_data[$emk])){
													$t_event_d[$emk] = $emv[0];
												}
											}

											if(!empty($t_event_d)){
												$event_data[] = $t_event_d;
											}										
										}
									}
								}
								
								#$this->_p($event_data);
								if(is_array($event_data) && !empty($event_data)){
									$e_l_c = 0;
									foreach($event_data as $e_data_v){
										if($e_l_c > 0){
											$Description.=PHP_EOL.' ';
										}

										foreach($e_key_data as $ekk => $ekv){
											$Description.=PHP_EOL.$e_key_data[$ekk].': '.$e_data_v[$ekk];
										}

										$e_l_c++;
									}
								}

							}
						}
					}
				}
			}
			
			//
			$Description = str_replace(PHP_EOL . PHP_EOL,PHP_EOL,$Description);
			
			$Description = $this->get_array_isset(array('Description'=>$Description),'Description','',false);
			$o_li_meta_data = $this->get_array_isset(array('o_li_meta_data'=>$o_li_meta_data),'o_li_meta_data','');
			if(!empty($o_li_meta_data)){
				$o_li_meta_data = ltrim($o_li_meta_data);
			}
			
			if(is_array($map_data) && !empty($map_data)){
				$qbo_items_tmp = array();
				$qbo_items_tmp['Description'] = $Description;
				$qbo_items_tmp['Qty'] = $wc_items['_qty'];
				
				if(isset($wc_items['qty'])){
					$qbo_items_tmp['Qty_Str'] = $wc_items['qty'];
					unset($wc_items['qty']);
				}
				
				$qbo_items_tmp['UnitPrice'] = $wc_items['unit_price'];
				
				//
				if($this->wacs_base_cur_enabled()){
					$qbo_items_tmp['UnitPrice_base_currency'] = $wc_items['unit_price_base_currency'];
				}
				
				$qbo_items_tmp['ItemRef'] = $map_data['itemid'];

				$qbo_items_tmp['acof_map_product'] = $acof_map_product;
				//
				$qbo_items_tmp['qbo_product_type'] = $map_data['product_type'];

				$qbo_items_tmp['Taxed'] = ($wc_items['line_tax']>0)?1:0;
				if(is_array($tax_details) && !empty($tax_details) && !$this->get_qbo_company_setting('is_automated_sales_tax')){
					$qbo_items_tmp['Taxed'] = 1;
				}
				
				if($this->get_qbo_company_setting('ClassTrackingPerTxnLine')){					
					/**/
					$qbo_items_tmp['ClassRef'] = (isset($map_data['class_id']))?$map_data['class_id']:'';
					if(empty($qbo_items_tmp['ClassRef'])){
						$qbo_items_tmp['ClassRef'] = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
					}
					
					/**/
					if($this->is_only_plugin_active('booking-resources-quickbooks-class-map-for-myworks-qbo-sync')){
						$c_ClassRef = $this->get_brqcm_class($wc_items);
						if(!empty($c_ClassRef)){
							$qbo_items_tmp['ClassRef'] = $c_ClassRef;
						}
					}
					
					if($this->is_plg_lc_p_l()){
						$qbo_items_tmp['ClassRef'] = '';
					}
					
				}else{
					$qbo_items_tmp['ClassRef'] = '';
				}				
				
				//Line Item date
				if($this->is_plugin_active('woocommerce-appointments') && $this->option_checked('mw_wc_qbo_sync_compt_wapnt_li_date')){
					if(isset($wc_items['Date']) && !empty($wc_items['Date'])){
						$li_date = trim($wc_items['Date']);
						$li_date = $this->view_date($li_date,'Y-m-d');
						$qbo_items_tmp['Date_QF'] = $li_date;
					}
				}
				
				$qbo_items = $qbo_items_tmp;
				foreach($wc_items as $k => $val){
					if($k!='name' && $k!='_qty' && $k!='unit_price'){
						$qbo_items[$k] = $val;
					}
				}
				
				$o_li_meta_data = '';
				$qbo_items['Line_Item_Meta'] = $o_li_meta_data;

			}
		}
		return $qbo_items;
	}
	
	/**/
	private function get_brqcm_class($wc_items){		
		$qbcr = '';
		if(is_array($wc_items) && !empty($wc_items) && isset($wc_items['order_item_id']) && (int) $wc_items['order_item_id'] > 0){			
			$order_item_id = (int) $wc_items['order_item_id'];
			global $wpdb;
			$bp_q = $wpdb->prepare("SELECT `post_id` FROM {$wpdb->postmeta} WHERE meta_key = '_booking_order_item_id' AND meta_value = %s",$order_item_id);
			$bp_id = (int) $wpdb->get_var($bp_q);
			if($bp_id > 0){
				$_booking_resource_id = (int) get_post_meta($bp_id,'_booking_resource_id',true);
				if($_booking_resource_id > 0){
					$brqcm_tbl = $wpdb->prefix.'mw_wc_qbo_sync_booking_resources_class_map';
					$qbcr = (string) $this->get_field_by_val($brqcm_tbl,'class_id','br_id',$_booking_resource_id);
				}
			}
		}
		return $qbcr;
	}
	
	//08-02-2017
	public function get_mapped_shipping_product($wc_shippingmethod=''){
		global $wpdb;
		$qbo_shipping_product = array();
		$qbo_shipping_product['ItemRef'] = (int) $this->get_option('mw_wc_qbo_sync_default_shipping_product');
		//
		if($qbo_shipping_product['ItemRef'] < 1){
			$qbo_shipping_product['ItemRef'] = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
		}
		
		//29-05-2017
		if($wc_shippingmethod=='no_method_found'){
			return $qbo_shipping_product;
		}

		$wc_shippingmethod = $this->sanitize($wc_shippingmethod);
		if($wc_shippingmethod!=''){
			$map_data = $this->get_row($wpdb->prepare("SELECT `qbo_product_id` , `class_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_shipping_product_map` WHERE `wc_shippingmethod` = %s AND  `qbo_product_id` > 0 ",$wc_shippingmethod));
			if(is_array($map_data) && !empty($map_data)){
				$qbo_shipping_product['ItemRef'] = (int) $map_data['qbo_product_id'];
				
				if($this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
					
					/**/
					$qbo_shipping_product['ClassRef'] = (isset($map_data['class_id']))?$map_data['class_id']:'';
					if(empty($qbo_shipping_product['ClassRef'])){
						$qbo_shipping_product['ClassRef'] = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
					}
					
					if($this->is_plg_lc_p_l()){
						$qbo_shipping_product['ClassRef'] = '';
					}
				}else{
					$qbo_shipping_product['ClassRef'] = '';
				}
			}
		}
		return $qbo_shipping_product;
	}
	
	public function get_mapped_payment_method_data($wc_paymentmethod='',$wc_currency=''){
		global $wpdb;
		$wc_paymentmethod = $this->sanitize($wc_paymentmethod);
		if($wc_paymentmethod!=''){
			$map_data = $this->get_row($wpdb->prepare("SELECT * FROM `".$wpdb->prefix."mw_wc_qbo_sync_paymentmethod_map` WHERE `wc_paymentmethod` = %s AND `currency` = %s ",$wc_paymentmethod,$wc_currency));
			
			#lpa
			/*
			if(is_array($map_data) && !empty($map_data) && $this->is_plg_lc_p_l()){
				$rpmf_arr = array(
					'enable_transaction',
					'txn_expense_acc_id',
					'txn_refund',
					'enable_refund',
					'enable_batch',
					'udf_account_id',
					'vendor_id',					
					'lump_weekend_batches',
					'term_id',
					'ps_order_status',
					'individual_batch_support',
					'deposit_cron_utc',
					'inv_due_date_days',
					'order_sync_as',
					'deposit_date_field',
					'deposit_cron_sch',
				);
				
				$rpmf_arr = array();
				
				if(is_array($rpmf_arr) && !empty($rpmf_arr)){
					foreach($rpmf_arr as $rfv){
						if(isset($map_data[$rfv])){unset($map_data[$rfv]);}
					}
				}				
			}
			*/
			
			return $map_data;
		}
		return array();
	}
	
	//10-02-2017
	public function get_mapped_coupon_product($wc_couponcode=''){
		global $wpdb;
		$promo_id = 0;
		$description = '';

		$qbo_coupon_product = array();
		$qbo_coupon_product['ItemRef'] = (int) $this->get_option('mw_wc_qbo_sync_default_coupon_code');
		$wc_couponcode = $this->sanitize($wc_couponcode);

		if($wc_couponcode!=''){
			$promo_data = $this->get_row($wpdb->prepare("SELECT `ID` , `post_excerpt` FROM `".$wpdb->posts."` WHERE `post_type` = 'shop_coupon' AND `post_title` = %s ",$wc_couponcode));
			if(is_array($promo_data) && !empty($promo_data)){
				$promo_id = (int) $promo_data['ID'];
				$description = $promo_data['post_excerpt'];
			}

			$map_data = $this->get_row($wpdb->prepare("SELECT `qbo_product_id` , `class_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_promo_code_product_map` WHERE `promo_id` = %s AND  `qbo_product_id` > 0 ",$promo_id));
			if(is_array($map_data) && !empty($map_data)){
				$qbo_coupon_product['ItemRef'] = (int) $map_data['qbo_product_id'];
				
				if($this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
					
					/**/
					$qbo_coupon_product['ClassRef'] = (isset($map_data['class_id']))?$map_data['class_id']:'';
					if(empty($qbo_coupon_product['ClassRef'])){
						$qbo_coupon_product['ClassRef'] = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
					}
					
					if($this->is_plg_lc_p_l()){
						$qbo_coupon_product['ClassRef'] = '';
					}
				}else{
					$qbo_coupon_product['ClassRef'] = '';
				}											
				
			}
		}
		$qbo_coupon_product['Description'] = 'Coupon: '.$wc_couponcode;
		$qbo_coupon_product['Description'] = $this->get_array_isset($qbo_coupon_product,'Description');
		return $qbo_coupon_product;
	}
	
	/**/
	public function get_woo_ord_number_from_order($order_id,$invoice_data=array()){
		$o_num = '';
		$order_id = (int) $order_id;
		
		$onk_f = '';
		if($order_id > 0){
			//
			global $wpdb;
			if($this->get_field_by_val($wpdb->posts,'post_type','ID',$order_id) != 'shop_order'){
				return '';
			}
			
			if($this->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
				//woocommerce-sequential-order-numbers
				if($this->is_plugin_active('woocommerce-sequential-order-numbers-pro','',true)){					
					$onk_f = '_order_number_formatted';
				}				
				
				if($this->is_only_plugin_active('woocommerce-sequential-order-numbers')){
					$onk_f = '_order_number';
				}				
				
				$ak = '';
				if($this->is_plugin_active('custom-order-numbers-for-woocommerce')){					
					$onk_f = '_alg_wc_custom_order_number';
					$ak = '_alg_wc_full_custom_order_number';
				}
				
				if(empty($onk_f) && !empty($this->get_option('mw_wc_qbo_sync_compt_p_wconmkn'))){
					$onk_f = $this->get_option('mw_wc_qbo_sync_compt_p_wconmkn');
				}
				
				if(!empty($onk_f)){
					$ton = '';
					if(is_array($invoice_data) && !empty($invoice_data) && isset($invoice_data[$onk_f])){						
						if(!empty($ak)){
							if(isset($invoice_data[$ak])){
								$ton = $invoice_data[$ak];
							}else{
								$ton = get_post_meta($order_id,$ak,true);
							}							
						}

						$o_num = $invoice_data[$onk_f];
					}else{
						if(!empty($ak)){$ton = get_post_meta($order_id,$ak,true);}
						$o_num = get_post_meta($order_id,$onk_f,true);
					}
					
					if(!empty($ton)){
						$o_num = $ton;
					}
					
					if($o_num!=''){
						$o_num = trim($o_num);
					}					
				}

				if(empty($o_num)){
					$o_num = apply_filters( 'woocommerce_order_number', $order_id, wc_get_order($order_id) );
				}
			}
		}
		
		return $o_num;
	}
	
	//11-04-2017
	public function get_wc_order_details_from_order($order_id,$order){
		global $wpdb;
		$order_id = (int) $order_id;
		if($order_id && is_object($order) && !empty($order)){
			//$this->_p($order);
			$order_meta = get_post_meta($order_id);
			//$this->_p($order_meta);
			$invoice_data = array();
			$invoice_data['wc_inv_id'] = $order_id;
			$invoice_data['wc_inv_num'] = '';
			
			/**/
			$invoice_data['wc_inv_num'] = $this->get_woo_ord_number_from_order($order_id);
			
			$invoice_data['order_type'] = '';

			//17-07-2017
			if($this->option_checked('mw_wc_qbo_sync_qbo_push_invoice_date')){
				//New
				$qodfv = $order->post_date;
				$qb_ord_df = $this->get_option('mw_wc_qbo_sync_qb_ord_df_val');
				
				if($qb_ord_df == 'd_o_s'){$qodfv = $this->now('Y-m-d');}
				
				if($qb_ord_df == 'o_p_d'){
					$qodfv = (is_array($order_meta) && isset($order_meta['_paid_date'][0]) && !empty($order_meta['_paid_date'][0]))?$order_meta['_paid_date'][0]:$qodfv;
				}
				
				if($qb_ord_df == 'o_c_d'){
					$qodfv = (is_array($order_meta) && isset($order_meta['_completed_date'][0]) && !empty($order_meta['_completed_date'][0]))?$order_meta['_completed_date'][0]:$qodfv;
				}
				
				$invoice_data['wc_inv_date'] = $qodfv;
				$invoice_data['wc_inv_due_date'] = $qodfv;
				
				$invoice_data['wc_inv_date_ori'] = $order->post_date;
				$invoice_data['wc_inv_due_date_ori'] = $order->post_date;
			}else{
				$invoice_data['wc_inv_date'] = $order->post_date;
				$invoice_data['wc_inv_due_date'] = $order->post_date;
			}
			
			//$invoice_data['customer_message'] = $order->post_excerpt;
			$invoice_data['customer_note'] = $order->post_excerpt;
			$invoice_data['order_status'] = $order->post_status;
			
			$wc_cus_id = isset($order_meta['_customer_user'][0])?(int) $order_meta['_customer_user'][0]:0;
			$invoice_data['wc_cus_id'] = $wc_cus_id;
			$invoice_data['wc_customerid'] = $wc_cus_id;


			if(is_array($order_meta) && !empty($order_meta)){
				foreach ($order_meta as $key => $value){
					$omv = ($value[0])?$value[0]:'';
					if($key == '_wcpay_intent_currency' && !empty($omv)){
						$omv = strtoupper($omv);
					}

					$invoice_data[$key] = $omv;
				}
			}

			$wc_oi_table = $wpdb->prefix.'woocommerce_order_items';
			$wc_oi_meta_table = $wpdb->prefix.'woocommerce_order_itemmeta';
			
			$order_items = $this->get_data($wpdb->prepare("SELECT * FROM {$wc_oi_table} WHERE `order_id` = %d ORDER BY order_item_id ASC ",$order_id));
			//$this->_p($order_items);
			$line_items = $used_coupons = $tax_details = $shipping_details = array();
			$dc_gt_fees = array();
			//
			$pw_gift_card = array();
			$gift_card = array();
			if(is_array($order_items) && !empty($order_items)){
				foreach($order_items as $oi){
					$order_item_id = (int) $oi['order_item_id'];
					$oi_meta = $this->get_data($wpdb->prepare("SELECT * FROM {$wc_oi_meta_table} WHERE `order_item_id` = %d ",$order_item_id));
					//$this->_p($oi_meta);
					$om_arr = array();
					if(is_array($oi_meta) && !empty($oi_meta)){
						foreach($oi_meta as $om){
							$om_arr[$om['meta_key']] = $om['meta_value'];
						}
					}

					$om_arr['name'] = $oi['order_item_name'];
					$om_arr['type'] = $oi['order_item_type'];

					if($oi['order_item_type']=='line_item'){
						$om_arr['order_item_id'] = $order_item_id;
						$line_items[] = $om_arr;
					}

					if($oi['order_item_type']=='coupon'){
						$used_coupons[] = $om_arr;
					}

					if($oi['order_item_type']=='shipping'){
						if(isset($om_arr['name'])){
							$om_arr['name'] = $this->get_array_isset($om_arr,'name');
						}
						$shipping_details[] = $om_arr;
					}

					if($oi['order_item_type']=='tax'){
						if(isset($om_arr['label'])){
							$om_arr['label'] = $this->get_array_isset($om_arr,'label');
						}
						$tax_details[] = $om_arr;
					}
					
					//16-05-2017
					if($oi['order_item_type']=='fee' || $oi['order_item_type'] == 'shipping_option'){
						if(isset($om_arr['name'])){
							$om_arr['name'] = $this->get_array_isset($om_arr,'name');
						}
						
						//
						if($oi['order_item_type'] == 'shipping_option'){
							$om_arr['_line_total'] = $om_arr['cost'];
							$om_arr['_line_tax'] = $om_arr['tax_amount'];
							if($om_arr['total_tax']){
								$om_arr['_line_tax'] = $om_arr['total_tax'];
							}
						}
						
						$dc_gt_fees[] = $om_arr;
					}
					
					/**/
					if($this->option_checked('mw_wc_qbo_sync_compt_pwwgc_gpc_ed')){
						if($oi['order_item_type']=='pw_gift_card'){
							if(isset($om_arr['name'])){
								$om_arr['name'] = $this->get_array_isset($om_arr,'name');
							}
							$pw_gift_card[] = $om_arr;
						}
					}
					
					/**/
					if($this->option_checked('mw_wc_qbo_sync_compt_wgcp_gpc_ed')){
						if($oi['order_item_type']=='gift_card'){
							if(isset($om_arr['name'])){
								$om_arr['name'] = $this->get_array_isset($om_arr,'name');
							}
							$gift_card[] = $om_arr;
						}
					}
				}
			}

			//12-06-2017
			$acof_txt='';
			if($this->is_plugin_active('woocommerce-admin-custom-order-fields') && $this->option_checked('mw_wc_qbo_sync_compt_p_wacof')){
				$mw_wc_qbo_sync_compt_p_wacof_m_field = (int) $this->get_option('mw_wc_qbo_sync_compt_p_wacof_m_field');
				$mw_wc_qbo_sync_compt_acof_wf_qi_map = $this->get_option('mw_wc_qbo_sync_compt_acof_wf_qi_map');
				if($mw_wc_qbo_sync_compt_acof_wf_qi_map!=''){
					$mw_wc_qbo_sync_compt_acof_wf_qi_map = unserialize($mw_wc_qbo_sync_compt_acof_wf_qi_map);
				}
				if($mw_wc_qbo_sync_compt_p_wacof_m_field && is_array($mw_wc_qbo_sync_compt_acof_wf_qi_map) && !empty($mw_wc_qbo_sync_compt_acof_wf_qi_map)){
					if(isset($invoice_data['_wc_acof_'.$mw_wc_qbo_sync_compt_p_wacof_m_field])){
						$acof_txt = $invoice_data['_wc_acof_'.$mw_wc_qbo_sync_compt_p_wacof_m_field];
						$acof_txt = trim($acof_txt);
					}
				}
			}

			$qbo_inv_items = array();
			$lis_arr = array();
			//$this->_p($line_items);
			if(is_array($line_items) && !empty($line_items)){
				foreach ( $line_items as $item ) {
					$product_data = array();
					foreach($item as $key=>$val){
						if($this->start_with($key,'_') && $key != '_qty'){
							$key = substr($key,1);
						}
						$product_data[$key] = $val;
					}
					
					/**/
					if(!$product_data['_qty']){$product_data['_qty'] = 1;}
					
					//$product_data['unit_price'] = ($product_data['line_subtotal']/$product_data['_qty']);
					
					$l_up = ($product_data['line_subtotal']/$product_data['_qty']);
					
					#New
					$use_lt_if_ist_l_item = $this->option_checked('mw_wc_qbo_sync_use_lt_if_ist_l_item');
					$skip_ltgc = false;
					
					$_cart_discount = $this->get_array_isset($invoice_data,'_cart_discount',0);
					if($_cart_discount > 0){
						$use_lt_if_ist_l_item = false;
					}
					
					if($use_lt_if_ist_l_item){
						if($product_data['line_total'] > $product_data['line_subtotal'] || $skip_ltgc){
							$l_up = ($product_data['line_total']/$product_data['_qty']);
						}						
					}
					
					//
					if($this->option_checked('mw_wc_qbo_sync_no_ad_discount_li')){
						if($product_data['line_total']<$product_data['line_subtotal']){
							$l_up = ($product_data['line_total']/$product_data['_qty']);
						}
					}
					
					//$l_up = $this->qbo_limit_decimal_points($l_up);
					$l_up = $this->trim_after_decimal_place($l_up,7);
					$product_data['unit_price'] = $l_up;
					
					if($this->wacs_base_cur_enabled()){
						$product_data['unit_price_base_currency'] = $product_data['unit_price'];
						if(isset($product_data['line_subtotal_base_currency'])){
							$l_up_bc = ($product_data['line_subtotal_base_currency']/$product_data['_qty']);
							#New
							if($use_lt_if_ist_l_item){
								if($product_data['line_total_base_currency'] > $product_data['line_subtotal_base_currency'] || $skip_ltgc){
									$l_up_bc = ($product_data['line_total_base_currency']/$product_data['_qty']);
								}
							}
							
							//
							if($this->option_checked('mw_wc_qbo_sync_no_ad_discount_li')){
								if($product_data['line_total_base_currency']<$product_data['line_subtotal_base_currency']){
									$l_up_bc = ($product_data['line_total_base_currency']/$product_data['_qty']);
								}
							}
							
							//$l_up_bc = $this->qbo_limit_decimal_points($l_up_bc);
							$l_up_bc = $this->trim_after_decimal_place($l_up_bc,7);
							$product_data['unit_price_base_currency'] = $l_up_bc;
						}
					}
					
					$mqi_fwi = $this->get_mapped_qbo_items_from_wc_items($product_data,false,$acof_txt,$tax_details,$invoice_data);
					if(is_array($mqi_fwi) && !empty($mqi_fwi)){
						$qbo_inv_items[] = $mqi_fwi;
						$lis_arr[] = $product_data['name'];
					}					
				}
			}

			$invoice_data['used_coupons'] = $used_coupons;

			$order_shipping_total = isset($order_meta['_order_shipping'][0])?$order_meta['_order_shipping'][0]:0;

			$invoice_data['shipping_details'] = $shipping_details;
			$invoice_data['order_shipping_total'] = $order_shipping_total;

			$invoice_data['tax_details'] = $tax_details;
			
			if($this->get_option('mw_wc_qbo_sync_qb_soli_sv') == 'atz_pn' && is_array($lis_arr) && !empty($lis_arr)){
				if(count($lis_arr) == count($qbo_inv_items)){
					asort($lis_arr);
					$tqii = array();
					foreach($lis_arr as $k => $v){
						$tqii[] = $qbo_inv_items[$k];
					}
					
					$qbo_inv_items = $tqii;
				}
			}			
			
			$invoice_data['qbo_inv_items'] = $qbo_inv_items;

			$invoice_data['dc_gt_fees'] = $dc_gt_fees;
			
			$invoice_data['pw_gift_card'] = $pw_gift_card;
			
			$invoice_data['gift_card'] = $gift_card;
			
			/**/
			
			/*
			$order_notes = '';
			
			$on_tbl = $wpdb->prefix . 'comments';
			$on_q = $wpdb->prepare("SELECT * FROM {$on_tbl} WHERE  `comment_post_ID` = %d AND  `comment_type` LIKE  'order_note'",$order_id);
			$o_notes = $this->get_data($on_q);
			if(is_array($o_notes) && !empty($o_notes)){
				foreach($o_notes as $note){
					$note_st = strip_tags($note['comment_content']) . '(' . $note['comment_date'] . ')';
					$note_st = $this->get_array_isset(array('note_st'=>$note_st),'note_st','');
					if(!empty($note_st)){
						$note_st = $note_st . PHP_EOL;
					}
					$order_notes .= $note_st;
				}
			}
			if(!empty($order_notes)){
				$order_notes = rtrim($order_notes);
			}
			*/
			
			//$order_notes = $order->post_excerpt;			
			$order_notes = $this->get_array_isset($invoice_data,'customer_note','');
			$invoice_data['Order_Comments_Notes'] = $order_notes;
			
			#New			
			$oac = '';
			if(is_array($invoice_data['used_coupons']) && !empty($invoice_data['used_coupons'])){				
				foreach($invoice_data['used_coupons'] as $uc_k => $uc_v){
					if($uc_v['type'] == 'coupon' && !empty($uc_v['name'])){
						$oac .= $uc_v['name'].',';
					}
				}
				
				if(!empty($oac)){
					$oac = substr($oac,0,-1);
				}
			}
			
			$invoice_data['Order_All_Coupons'] = $oac;
			
			//$this->_p($invoice_data);
			return $invoice_data;
		}
	}

	//
	public function get_plugin_db_tbl_list(){
		global $wpdb;
		$tl_q = "SHOW TABLES LIKE '{$wpdb->prefix}mw_wc_qbo_sync\_%'";
		$tbl_list = $this->get_data($tl_q);

		$p_tbls = array();
		if(is_array($tbl_list) && !empty($tbl_list)){
			foreach($tbl_list as $tl){
				if(is_array($tl) && !empty($tl)){
					$tl_v = current($tl);$tl_v = (string) $tl_v;$tl_v = trim($tl_v);
					if($tl_v!=''){
						$p_tbls[] = $tl_v;
					}
				}
			}
		}
		return $p_tbls;
	}
	public function db_check_get_fields_details($s_tbf_list=array()){
		$tb_f_list = array();
		$tbls = $this->get_plugin_db_tbl_list();
		if(is_array($tbls) && !empty($tbls)){
			foreach($tbls as $tln){
				$tcq = "SHOW COLUMNS FROM {$tln}";
				$tc_list = $this->get_data($tcq);
				$tc_tmp_arr = array();
				if(is_array($tc_list) && !empty($tc_list)){
					foreach($tc_list as $tc_l){
						$tc_tmp_arr[$tc_l['Field']] = $tc_l;
					}
				}
				//$this->_p($tc_list);
				$tb_f_list[$tln] = $tc_tmp_arr;
			}
		}
		//$this->_p($tbls);
		//$this->_p($tb_f_list);
		return $tb_f_list;
	}
	
	public function get_wc_booking_dtls($order_id){
		global $wpdb;
		$order_id = (int) $order_id;
		$booking_order_id = 0;
		$booking_order_p = $this->get_row($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_parent=%d AND post_type='wc_booking' ",$order_id));
		
		if(is_array($booking_order_p) && !empty($booking_order_p)){
			$booking_order_id = $booking_order_p['ID'];
		}		
		if($booking_order_id){
			$order_id = $booking_order_id;
			$order = get_post($order_id);
			if(is_object($order) && !empty($order)){
				$order_meta = get_post_meta($order_id);
				$invoice_data = array();
				$invoice_data['wc_inv_id'] = $order_id;
				$invoice_data['wc_inv_num'] = '';
				
				$invoice_data['order_type'] = '';

				$invoice_data['wc_inv_date'] = $order->post_date;
				$invoice_data['wc_inv_due_date'] = $order->post_date;
	
				$invoice_data['customer_note'] = $order->post_excerpt;
				$invoice_data['order_status'] = $order->post_status;
	
				$wc_cus_id = isset($order_meta['_customer_user'][0])?(int) $order_meta['_customer_user'][0]:0;
				$invoice_data['wc_cus_id'] = $wc_cus_id;
	
				if(is_array($order_meta) && !empty($order_meta)){
					foreach ($order_meta as $key => $value){
						$invoice_data[$key] = ($value[0])?$value[0]:'';
					}
				}
				
				return $invoice_data;
			}			
		}
	}	
	
	/*Void Invoice*/
	public function VoidInvoice($qbo_inv_id,$invoice_data,$chk_get_inv_id_from_qb=false){
		if($this->is_connected()){
			if(!$this->option_checked('mw_wc_qbo_sync_invoice_cancelled')){
				return false;
			}
			
			$qbo_inv_id = (int) $qbo_inv_id;			
			$wc_inv_id = $this->get_array_isset($invoice_data,'wc_inv_id',0);
			$wc_inv_num = $this->get_array_isset($invoice_data,'wc_inv_num','');			
			
			if(!$qbo_inv_id && $chk_get_inv_id_from_qb && $wc_inv_id>0){
				$qbo_inv_id = $this->get_qbo_invoice_id($wc_inv_id,$wc_inv_num);
			}
			
			if($qbo_inv_id>0 && $wc_inv_id>0){
				/*Full Refunded Order With QB Refund*/
				
				/*
				$order_status = $this->get_array_isset($invoice_data,'order_status','');				
				if($order_status == 'wc-refunded'){
					$refund_id = $this->get_woo_refund_id_from_order_id($wc_inv_id);
					if($refund_id && $this->if_refund_exists(array('wc_inv_id'=>$wc_inv_id,'refund_id'=>$refund_id))){
						return false;
					}
				}
				*/
				
				$refund_id = $this->get_woo_refund_id_from_order_id($wc_inv_id);
				if($refund_id){
					return false;
				}
				
				$wc_cus_id = $this->get_array_isset($invoice_data,'wc_cus_id','');
				$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
				
				if($this->if_sync_invoice($wc_inv_id,$wc_cus_id,$wc_inv_num)){
					$Context = $this->Context;
					$realm = $this->realm;
					$invoiceService = new QuickBooks_IPP_Service_Invoice();
					
					$log_title = "";
					$log_details = "";
					$log_status = 0;
					
					if ($resp = $invoiceService->void($Context, $realm, $qbo_inv_id)){
						$log_title.="Void Invoice - Order #$ord_id_num\n";
						$log_details.="Order #$ord_id_num has been marked void in QBO, QuickBooks Invoice ID is #$qbo_inv_id";
						$log_status = 1;
						$this->save_log($log_title,$log_details,'Invoice',$log_status,true,'Void');
						return true;
					}else{
						$res_err = $invoiceService->lastError($Context);
						$log_title.="Void Invoice Error Order #$ord_id_num\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Invoice',$log_status,true,'Void');
						return false;
					}
				}
			}
		}
	}
	
	/*Update Invoice*/
	public function UpdateInvoice($invoice_data){
		//$this->include_sync_functions('UpdateInvoice');
		$fn_name = 'UpdateInvoice';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	/*Update Estimate*/
	public function UpdateEstimate($invoice_data){
		//$this->include_sync_functions('UpdateEstimate');
		$fn_name = 'UpdateEstimate';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	//
	public function get_wc_bundle_line_item_up_from_oli($qbo_inv_items,$qbo_item_c){
		$up=0;
		if(is_array($qbo_inv_items) && !empty($qbo_inv_items) && is_array($qbo_item_c) && !empty($qbo_item_c)){
			$bundled_items = array();
			if(isset($qbo_item_c['bundled_items']) && !empty($qbo_item_c['bundled_items']) && $qbo_item_c['qbo_product_type'] == 'Group'){
				if(isset($qbo_item_c['bundle_cart_key']) && !empty($qbo_item_c['bundle_cart_key'])){
					$bundled_items = unserialize($qbo_item_c['bundled_items']);
				}				
			}
			foreach($qbo_inv_items as $qbo_item){
				if($qbo_item['qbo_product_type']!='Group' && isset($qbo_item['bundled_item_id']) && $qbo_item['bundled_item_id']>0){
					if(isset($qbo_item['bundle_cart_key']) && !empty($qbo_item['bundle_cart_key'])){
						if(is_array($bundled_items) && in_array($qbo_item['bundle_cart_key'],$bundled_items)){
							$up += $qbo_item['UnitPrice']*$qbo_item['Qty'];
						}
					}					
				}
			}
			
			$up = $up/$qbo_item_c['Qty'];
		}		
		return $up;
	}
	
	//27-06-2017
	public function wc_get_wst_data($_wc_shipment_tracking_items){
		$wsti_data = array();
		if($_wc_shipment_tracking_items!=''){
			$_wc_shipment_tracking_items = unserialize($_wc_shipment_tracking_items);
			if(is_array($_wc_shipment_tracking_items) && !empty($_wc_shipment_tracking_items)){
				$wsti = $_wc_shipment_tracking_items[0];
				$tracking_provider = ($wsti['tracking_provider']!='')?$wsti['tracking_provider']:$wsti['custom_tracking_provider'];
				$tracking_number = $wsti['tracking_number'];
				$date_shipped = $wsti['date_shipped'];
				if($date_shipped!=''){
					$date_shipped = date('Y-m-d',$date_shipped);
				}
				$wsti_data['tracking_provider'] = $tracking_provider;
				$wsti_data['tracking_number'] = $tracking_number;
				$wsti_data['date_shipped'] = $date_shipped;
			}
		}
		return $wsti_data;
	}
	
	public function wc_get_wst_data_pro($wf_wc_shipment_source,$wf_wc_shipment_result){
		$is_ph_wst_pro = $this->is_plugin_active('ph-woocommerce-shipment-tracking','woocommerce-shipment-tracking');
		$wsti_data = array();
		if($wf_wc_shipment_source!='' && $wf_wc_shipment_result!=''){
			$wf_wc_shipment_source = @unserialize($wf_wc_shipment_source);
			$wf_wc_shipment_result = @unserialize($wf_wc_shipment_result);
			
			if(is_array($wf_wc_shipment_source) && !empty($wf_wc_shipment_source)){
				$tracking_number = $wf_wc_shipment_source['shipment_id_cs'];
				
				if(is_array($wf_wc_shipment_result) && !empty($wf_wc_shipment_result) && isset($wf_wc_shipment_result['tracking_info']) && is_array($wf_wc_shipment_result['tracking_info']) && !empty($wf_wc_shipment_result['tracking_info'])){
					$wsti = $wf_wc_shipment_result['tracking_info'][0];
					$tracking_number = $wsti['tracking_id'];
					
				}
				
				$tracking_provider = $wf_wc_shipment_source['shipping_service'];
				$date_shipped = $wf_wc_shipment_source['order_date'];					
				if($date_shipped!=''){
					//$date_shipped = date('Y-m-d',$date_shipped);
					if($is_ph_wst_pro){
						$date_shipped = date('Y-m-d',strtotime($date_shipped));
					}					
				}
				
				if($is_ph_wst_pro && !empty($tracking_provider)){
					$tracking_provider = $this->get_ph_wst_pro_s_service_name($tracking_provider);
				}				
				
				$wsti_data['tracking_provider'] = $tracking_provider;
				$wsti_data['tracking_number'] = $tracking_number;
				$wsti_data['date_shipped'] = $date_shipped;
				
			}
		}
		return $wsti_data;
	}
	
	private function get_ph_wst_pro_s_service_name($shipping_service){
		$ph_wst_ss_arr = array(
			'2go'=>'2GO',
			'abf-com'=>'ABF.com',
			'apc-overnight'=>'APC Overnight',
			'aramex'=>'Aramex',
			'asendia-usa'=>'ASENDIA (USA)',
			'asm-es'=>'ASM (ES)',
			'australian-post'=>'Australian Post',
			'averitt-express'=>'Averitt Express',
			'blue-dart'=>'Blue Dart',
			'bpost-belgium'=>'Bpost Belgium',
			'canada-post'=>'Canada Post',
			'canpar-courier'=>'Canpar Courier',
			'ceva-logistics'=>'CEVA Logistics',
			'colis-prive-adrexo'=>'Colis Prive (Adrexo)',
			'colissimo'=>'Colissimo',
			'collect'=>'Collect+',
			'con-way-freight'=>'Con-Way Freight',
			'correios'=>'Correios',
			'correos-express'=>'Correos Express',
			'courierpost'=>'CourierPost',
			'ctt-expresso'=>'CTT Expresso',
			'db-schenker'=>'DB Schenker',
			'deutsche-post-dhl'=>'Deutsche Post (DHL)',
			'dhl-cz'=>'DHL (CZ)',
			'dhl-express'=>'DHL Express',
			'dhl-global'=>'DHL Global',
			'dhl-intraship-de'=>'DHL Intraship (DE)',
			'dhl-parcel-belgium'=>'DHL Parcel Belgium',
			'dhl-usa'=>'DHL USA',
			'dpd-cz'=>'DPD (CZ)',
			'dpd-de'=>'dpd (DE)',
			'dpd-nl'=>'DPD (NL)',
			'dsv'=>'DSV',
			'estes-express'=>'ESTES Express',
			'fastway-couriers'=>'Fastway Couriers',
			'fedex'=>'FedEx',
			'fedex-sameday'=>'FedEx SameDay',
			'freightquote'=>'FreightQuote',
			'giaohangnhanh'=>'Giaohangnhanh',
			'globegistics'=>'Globegistics',
			'gojavas'=>'Gojavas',
			'hermesworld'=>'Hermesworld',
			'i-parcel-ups'=>'i-parcel (UPS)',
			'icc-world'=>'ICC World',
			'india-post'=>'India Post',
			'interlink-express-1'=>'Interlink Express (1)',
			'interlink-express-2'=>'Interlink Express (2)',
			'japan-post'=>'Japan Post',
			'la-poste'=>'La Poste',
			'lasership'=>'LaserShip',
			'mrw-es'=>'MRW (ES)',
			'myhermes-uk'=>'myHermes (UK)',
			'new-zealand-post'=>'New Zealand Post',
			'old-dominion'=>'Old Dominion',
			'ontrac'=>'OnTrac',
			'parcel-force'=>'Parcel Force',
			'pbt-couriers'=>'PBT Couriers',
			'post-ag'=>'Post AG',
			'posta-cz'=>'Posta (CZ)',
			'posti'=>'Posti',
			'postnl'=>'PostNL',
			'postnl-02'=>'PostNL (02)',
			'postnord'=>'Postnord',
			'ppl-cz'=>'PPL (CZ)',
			'purolator'=>'Purolator',
			'rl-carriers'=>'RL Carriers',
			'roadrunner'=>'Roadrunner',
			'royal-mail'=>'Royal Mail',
			'saia'=>'SAIA',
			'skynet-worldwide-express'=>'SkyNet Worldwide Express',
			'stamps-com-usps'=>'Stamps.com (USPS)',
			'the-professional-couriers'=>'The Professional Couriers',
			'tnt-consignment'=>'TNT (Consignment)',
			'tnt-reference'=>'TNT (Reference)',
			'tourline-express-es'=>'Tourline express (ES)',
			'uk-mail'=>'UK Mail',
			'united-states-postal-service-usps'=>'United States Postal Service (USPS)',
			'ups'=>'UPS',
			'yodel-direct'=>'Yodel Direct',
			'yrc-freight'=>'YRC Freight',
			'yrc-regional'=>'YRC Regional',
		);
		
		if(!empty($shipping_service) && isset($ph_wst_ss_arr[$shipping_service])){
			return $ph_wst_ss_arr[$shipping_service];
		}
		
		return $shipping_service;
	}
	
	/*Void SalesReceipt*/
	public function VoidSalesReceipt($qbo_sr_id,$invoice_data,$chk_get_sr_id_from_qb=false){
		if($this->is_connected()){
			if(!$this->option_checked('mw_wc_qbo_sync_invoice_cancelled')){
				return false;
			}
			
			$qbo_sr_id = (int) $qbo_sr_id;			
			$wc_inv_id = $this->get_array_isset($invoice_data,'wc_inv_id',0);
			$wc_inv_num = $this->get_array_isset($invoice_data,'wc_inv_num','');
			
			if(!$qbo_sr_id && $chk_get_sr_id_from_qb && $wc_inv_id>0){
				$qbo_sr_id = $this->get_qbo_salesreceipt_id($wc_inv_id,$wc_inv_num);
			}
			if($qbo_sr_id>0 && $wc_inv_id>0){
				/*Full Refunded Order With QB Refund*/
				
				/*
				$order_status = $this->get_array_isset($invoice_data,'order_status','');				
				if($order_status == 'wc-refunded'){
					$refund_id = $this->get_woo_refund_id_from_order_id($wc_inv_id);
					if($refund_id && $this->if_refund_exists(array('wc_inv_id'=>$wc_inv_id,'refund_id'=>$refund_id))){
						return false;
					}
				}
				*/
				
				$refund_id = $this->get_woo_refund_id_from_order_id($wc_inv_id);
				if($refund_id){
					return false;
				}
				
				$wc_cus_id = $this->get_array_isset($invoice_data,'wc_cus_id','');
				$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
				
				if($this->if_sync_invoice($wc_inv_id,$wc_cus_id,$wc_inv_num)){
					$Context = $this->Context;
					$realm = $this->realm;
					$SalesReceiptService = new QuickBooks_IPP_Service_SalesReceipt();
					
					$log_title = "";
					$log_details = "";
					$log_status = 0;
					
					if ($resp = $SalesReceiptService->void($Context, $realm, $qbo_sr_id)){
						$log_title.="Void SalesReceipt - Order #$ord_id_num\n";
						$log_details.="Order #$ord_id_num has been marked void in QBO, QuickBooks SalesReceipt ID is #$qbo_sr_id";
						$log_status = 1;
						$this->save_log($log_title,$log_details,'Invoice',$log_status,true,'Void');
						return true;
					}else{
						$res_err = $SalesReceiptService->lastError($Context);
						$log_title.="Void SalesReceipt Error Order #$ord_id_num\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Invoice',$log_status,true,'Void');
						return false;
					}
				}
			}
		}
	}
	
	public function VoidEstimate($qbo_est_id,$invoice_data,$chk_get_sr_id_from_qb=false){
		if($this->is_connected()){
			if(!$this->option_checked('mw_wc_qbo_sync_invoice_cancelled')){
				return false;
			}
			
			$qbo_est_id = (int) $qbo_est_id;			
			$wc_inv_id = $this->get_array_isset($invoice_data,'wc_inv_id',0);
			$wc_inv_num = $this->get_array_isset($invoice_data,'wc_inv_num','');
			
			if(!$qbo_est_id && $chk_get_sr_id_from_qb && $wc_inv_id>0){
				$qbo_est_id = $this->get_qbo_estimate_id($wc_inv_id,$wc_inv_num);
			}
			if($qbo_est_id>0 && $wc_inv_id>0){
				/*Full Refunded Order With QB Refund*/
				
				/*
				$order_status = $this->get_array_isset($invoice_data,'order_status','');
				if($order_status == 'wc-refunded'){
					$refund_id = $this->get_woo_refund_id_from_order_id($wc_inv_id);
					if($refund_id && $this->if_refund_exists(array('wc_inv_id'=>$wc_inv_id,'refund_id'=>$refund_id))){
						return false;
					}
				}
				*/
				
				$refund_id = $this->get_woo_refund_id_from_order_id($wc_inv_id);
				if($refund_id){
					return false;
				}
				
				$wc_cus_id = $this->get_array_isset($invoice_data,'wc_cus_id','');
				$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
				
				if($this->if_sync_invoice($wc_inv_id,$wc_cus_id,$wc_inv_num)){
					$Context = $this->Context;
					$realm = $this->realm;
					$EstimateService = new QuickBooks_IPP_Service_Estimate();
					
					$log_title = "";
					$log_details = "";
					$log_status = 0;
					
					if ($resp = $EstimateService->void($Context, $realm, $qbo_est_id)){
						$log_title.="Void Estimate - Order #$ord_id_num\n";
						$log_details.="Order #$ord_id_num has been marked void in QBO, QuickBooks Estimate ID is #$qbo_est_id";
						$log_status = 1;
						$this->save_log($log_title,$log_details,'Invoice',$log_status,true,'Void');
						return true;
					}else{
						$res_err = $EstimateService->lastError($Context);
						$log_title.="Void Estimate Error Order #$ord_id_num\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Invoice',$log_status,true,'Void');
						return false;
					}
				}
			}
		}
	}
	
	/**/
	public function UpdateSalesReceipt($invoice_data){
		//$this->include_sync_functions('UpdateSalesReceipt');
		$fn_name = 'UpdateSalesReceipt';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	//27-04-2017
	public function wc_get_sm_data_from_method_id_str($method_id='',$key=''){
		$shipping_method = $method_id;
		if($method_id!=''){
			if(strpos( $method_id, ':' ) !== false){
				$shipping_method = substr($method_id, 0, strpos($method_id, ":"));
			}else{
				$sm_arr = explode('_',$method_id);
				if(is_array($sm_arr) && count($sm_arr)>2){
					$sm_count = count($sm_arr);

					$sm_id_index = (int) ($sm_count-2);
					$sm_id = 0;
					if(isset($sm_arr[$sm_id_index]) && is_numeric($sm_arr[$sm_id_index])){
						$sm_id = (int) $sm_arr[$sm_id_index];
						unset($sm_arr[$sm_id_index]);
					}


					$sm_reg_id_index = (int) ($sm_count-1);
					$sm_reg_id = 0;
					if(isset($sm_arr[$sm_reg_id_index]) && is_numeric($sm_arr[$sm_reg_id_index])){
						$sm_reg_id = (int) $sm_arr[$sm_reg_id_index];
						unset($sm_arr[$sm_reg_id_index]);
					}
					$shipping_method = implode('_',$sm_arr);
				}
			}
		}
		return $shipping_method;
	}
	
	public function get_cf_map_data($id_val=false){
		$rd = array();
		global $wpdb;
		$cmd = $this->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map');
		if(is_array($cmd) && !empty($cmd)){
			foreach($cmd as $row){
				if(!$id_val){
					$mk = $row['wc_field'].'____'.$row['id'];
					$rd[$mk] = $row['qb_field'];
					$rd[$mk.'_ext_data'] = $row['ext_data'];
				}else{
					$rd[$row['id']] = $row['wc_field'];
				}
			}
		}
		return $rd;
	}
	
	public function get_woo_field_fm_cmd($mkf,$id_f_m){
		if(is_array($id_f_m) && !empty($id_f_m) && !empty($mkf)){
			$mkf_a = explode('____',$mkf);
			if(is_array($mkf_a) && !empty($mkf_a)){
				$ac = count($mkf_a);
				if($ac > 1){
					$lv = $ac- 1;					
					if(strpos($mkf_a[$lv], '_ext_data')===false){
						$mkf_a_lv = str_replace('_','',$mkf_a[$lv]);
						$fm_id =  (int) $mkf_a_lv;
						if($fm_id > 0 && isset($id_f_m[$fm_id])){
							$mkf = str_replace('____'.$fm_id,'',$mkf);						
						}
					}
				}				
			}
		}
		return $mkf;
	}
	
	public function get_cf_cmd_concat_k($wk,$cfm_iv){
		if(!empty($wk) && is_array($cfm_iv) && !empty($cfm_iv)){
			$a_k = array_search($wk,$cfm_iv);
			if($a_k){
				return $wk.'____'.$a_k;
			}
		}
		return $wk;
	}
	
	public function get_shipping_details_from_order_data($invoice_data){
		$sd='';
		if($this->get_array_isset($invoice_data,'_shipping_first_name','',true)!=''){
			$_shipping_first_name = $this->get_array_isset($invoice_data,'_shipping_first_name','',true);
			$_shipping_last_name = $this->get_array_isset($invoice_data,'_shipping_last_name','',true);

			$_shipping_company = $this->get_array_isset($invoice_data,'_shipping_company','',true);

			$_shipping_address_1 = $this->get_array_isset($invoice_data,'_shipping_address_1','',true);
			$_shipping_address_2 = $this->get_array_isset($invoice_data,'_shipping_address_2','',true);

			$_shipping_city = $this->get_array_isset($invoice_data,'_shipping_city','',true);
			$_shipping_country = $this->get_array_isset($invoice_data,'_shipping_country','',true);
			$country = $this->get_country_name_from_code($_shipping_country);

			$_shipping_state = $this->get_array_isset($invoice_data,'_shipping_state','',true);
			$_shipping_postcode = $this->get_array_isset($invoice_data,'_shipping_postcode','',true);

			$sd = $_shipping_first_name.' '.$_shipping_last_name.PHP_EOL;
			$sd.=$_shipping_company.PHP_EOL;

			$sd.=$_shipping_address_1.PHP_EOL;
			$sd.=$_shipping_address_2.PHP_EOL;

			$sd.=$_shipping_city.', '.$_shipping_state.' '.$_shipping_postcode.PHP_EOL;
			if($_shipping_country!=$country){
				$sd.=$country.' ('.$_shipping_country.')';
			}else{
				$sd.=$country;
			}
		}
		return $sd;
	}
	
	/*Not in Use*/
	private function include_sync_functions($fn_name){
		/*
		$fn_name = trim($fn_name);
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
		*/
	}
	
	/*Add Invoice*/
	public function AddInvoice($invoice_data){		
		//$this->include_sync_functions('AddInvoice');
		$fn_name = 'AddInvoice';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){				
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	/*Add Estimate*/
	public function AddEstimate($invoice_data){
		//$this->include_sync_functions('AddEstimate');
		$fn_name = 'AddEstimate';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){				
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	/*Add AddPurchaseOrder*/
	public function AddPurchaseOrder($invoice_data){
		//$this->include_sync_functions('AddPurchaseOrder');
		$fn_name = 'AddPurchaseOrder';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){				
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function cfm_ft_ev_pv($wcf_val,$wcfm_ext_data){
		if(!empty($wcf_val) && !empty($wcfm_ext_data)){
			$wcfm_ext_data = unserialize($wcfm_ext_data);
			if(is_array($wcfm_ext_data) && !empty($wcfm_ext_data)){
				if(isset($wcfm_ext_data['field_type']) && isset($wcfm_ext_data['ext_val'])){
					if(!empty($wcfm_ext_data['field_type']) && !empty($wcfm_ext_data['ext_val'])){
						if($wcfm_ext_data['field_type'] == 'Date'){
							$df_val = '';
							$df = $wcfm_ext_data['ext_val'];
							/*
							if($df == 'yyyy-mm-dd' || $df == 'yyyy/mm/dd'){
								$ek = ($df == 'yyyy-mm-dd')?'-':'/';
								$wcf_val_a = explode($ek,$wcf_val);
								if(is_array($wcf_val_a) && count($wcf_val_a) == 3){
									$df_val = $wcf_val_a[0].'-'.$wcf_val_a[1].'-'.$wcf_val_a[2];
								}
							}
							
							if($df == 'dd-mm-yyyy' || $df == 'dd/mm/yyyy'){
								$ek = ($df == 'dd-mm-yyyy')?'-':'/';
								$wcf_val_a = explode($ek,$wcf_val);
								if(is_array($wcf_val_a) && count($wcf_val_a) == 3){
									$df_val = $wcf_val_a[2].'-'.$wcf_val_a[1].'-'.$wcf_val_a[0];
								}
							}
							
							if($df == 'mm-dd-yyyy' || $df == 'mm/dd/yyyy'){
								$ek = ($df == 'mm-dd-yyyy')?'-':'/';
								$wcf_val_a = explode($ek,$wcf_val);
								if(is_array($wcf_val_a) && count($wcf_val_a) == 3){
									$df_val = $wcf_val_a[2].'-'.$wcf_val_a[0].'-'.$wcf_val_a[1];
								}
							}
							*/
							
							$df = str_replace(array('dd','mm'),array('d','m'),$df);
							$yf = 'y';
							if (strpos($df, 'yyyy') !== false) {
								$yf = 'Y';
							}
							$df = str_replace(array('yyyy','yy'),$yf,$df);
							if (strpos($wcf_val, ':') !== false) {
								$df.= ' H:i';
							}
							
							$dto = DateTime::createFromFormat($df, $wcf_val);
							$df_val = ($dto)?$dto->format('Y-m-d'):'';
							
							if(!empty($df_val)){
								$wcf_val = $df_val;
							}
						}
					}
				}
			}
		}
		return $wcf_val;
	}

	public function add_txt_to_log_file($txt){
		if($txt!=''){
			$lof_file_path = MW_QBO_SYNC_LOG_DIR."mw-qbo-sync-log.log";
			if(file_exists($lof_file_path)){
				$f_ot = 'a';
				$log_file = fopen($lof_file_path, $f_ot);
				fwrite($log_file, "\n". $txt);
				fclose($log_file);
			}
		}
	}

	//20-03-2017
	public function add_qbo_item_obj_into_log_file($type,$wc_data,$item,$request='',$response='',$suc_log=false,$append=true){
		$is_log_allowed = false;
		if($this->option_checked('mw_wc_qbo_sync_err_add_item_obj_into_log_file') && !$suc_log){
			$is_log_allowed = true;
		}

		if($this->option_checked('mw_wc_qbo_sync_success_add_item_obj_into_log_file') && $suc_log){
			$is_log_allowed = true;
		}

		if($is_log_allowed && $type!='' && !empty($item) && !empty($wc_data)){
			$f_log_txt = '';
			$f_log_txt.=$type.' ('.$this->now('Y-m-d H:i:s').')'.PHP_EOL;

			$f_log_txt.="Woocommerce Data:".PHP_EOL;
			$f_log_txt.=print_r($wc_data,true).PHP_EOL;

			$f_log_txt.="QuickBooks Object:".PHP_EOL;
			$f_log_txt.=print_r($item,true).PHP_EOL;

			$f_log_txt.="Request:".PHP_EOL;
			$f_log_txt.=$request.PHP_EOL;

			$f_log_txt.="Response:".PHP_EOL;
			$f_log_txt.=$response.PHP_EOL;

			$f_ot = ($append)?'a':'w';

			$log_filename = ($suc_log)?'mw-qbo-sync-req-res-log.log':'mw-qbo-sync-log.log';
			//07-04-2017
			if((time()-filemtime(MW_QBO_SYNC_LOG_DIR.$log_filename)) > 86400){
				$f_ot = 'w';
			}

			$log_file = fopen(MW_QBO_SYNC_LOG_DIR.$log_filename, $f_ot);
			fwrite($log_file, "\n". $f_log_txt);
			fclose($log_file);
		}
	}

	//06-06-2017
	public function get_compt_map_dep_item_id($source){
		$mdp_id = 0;
		if($source!=''){
			$dpt_ma = $this->get_option('mw_wc_qbo_sync_compt_wchau_wf_qi_map');
			$wchau_options = get_option('wchau_options');
			if($dpt_ma && $wchau_options!=''){
				$dpt_ma = unserialize($dpt_ma);
				$wchau_options = explode(PHP_EOL,$wchau_options);
				if(is_array($dpt_ma) && !empty($dpt_ma) && is_array($wchau_options) && !empty($wchau_options)){
					$wchau_options = array_map('trim',$wchau_options);
					$dpt_ma = array_map('trim',$dpt_ma);

					if(in_array($source,$wchau_options)){
						foreach($dpt_ma as $k => $dp){
							$k = base64_decode($k);
							if($source==$k){
								$mdp_id = (int) $dp;
								break;
							}
						}
					}else{
						foreach($dpt_ma as $k => $dp){
							$k = base64_decode($k);
							if('Other'==$k || 'Others'==$k){
								$mdp_id = (int) $dp;
								break;
							}
						}
					}
				}
			}
		}
		return $mdp_id;
	}
	
	//22-02-2017	
	public function AddSalesReceipt($invoice_data){
		//$this->include_sync_functions('AddSalesReceipt');
		$fn_name = 'AddSalesReceipt';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function get_item_tax_rate($line_tax,$line_total,$item_id=0,$tax_class=''){
		$item_rate = round(ceil(($line_tax / $line_total)*100),2);
		//$item_rate = number_format(round((float)$item_rate,4),4);
		$item_rate = number_format((float)$item_rate, 4, '.', '');
		return $item_rate;
	}

	//16-05-2017
	public function get_qbo_tax_map_code_from_serl_line_tax_data($ltd,$l_type=''){
		$tr1_id = 0;
		$tr2_id = 0;
		if($ltd!=''){
			$ltd = unserialize($ltd);
			if(is_array($ltd) && !empty($ltd)){
				$ltd_arr = array();
				if($l_type=='shipping'){
					//22-05-2017
					if(isset($ltd['total']) && is_array($ltd['total']) && !empty($ltd['total'])){
						$ltd_arr = $ltd['total'];
					}else{
						$ltd_arr = $ltd;
					}
				}else{
					if(isset($ltd['total']) && is_array($ltd['total']) && !empty($ltd['total'])){
						$ltd_arr = $ltd['total'];
					}
					if(isset($ltd['subtotal']) && is_array($ltd['subtotal']) && !empty($ltd['subtotal'])){
						//$ltd_arr = $ltd['subtotal'];
					}
				}
				if(is_array($ltd_arr) && !empty($ltd_arr)){
					$i=1;
					foreach($ltd_arr as $k=>$v){
						if($i==1){
							$tr1_id = (int) $k;
						}
						if($i==2){
							$tr2_id = (int) $k;
						}
						$i++;
					}
				}
			}
		}
		if($tr1_id>0 || $tr2_id>0){
			return $this->get_qbo_mapped_tax_code($tr1_id,$tr2_id);
		}
		return '';
	}
	
	public function get_qbo_mapped_tax_code($tax_rate_id,$tax_rate_id_2=0,$tax_details=array(),$invoice_data=array()){

		$qbo_tax_code = '';
		//$woocommerce_tax_based_on = $this->get_option('woocommerce_tax_based_on');

		/*
		if($woocommerce_tax_based_on=='billing'){
			$state = $this->get_array_isset($invoice_data,'_billing_state','',true);
			$country = $this->get_array_isset($invoice_data,'_billing_country','',true);
		}elseif($woocommerce_tax_based_on=='shipping'){
			$state = $this->get_array_isset($invoice_data,'_shipping_state','',true);
			$country = $this->get_array_isset($invoice_data,'_shipping_country','',true);
		}else{
			$base_location = wc_get_base_location();
			$state = $this->get_array_isset($base_location,'state','',true);
			$country = $this->get_array_isset($base_location,'country','',true);
		}
		*/

		global $wpdb;
		$tax_map_table = $wpdb->prefix.'mw_wc_qbo_sync_tax_map';

		$tax_map_data = $this->get_row($wpdb->prepare("SELECT `qbo_tax_code` FROM ".$tax_map_table." WHERE `wc_tax_id` = %d AND `wc_tax_id_2` = %d ",$tax_rate_id,$tax_rate_id_2));
		//$this->_p($tax_map_data);
		if(is_array($tax_map_data) && !empty($tax_map_data)){
			$qbo_tax_code = $tax_map_data['qbo_tax_code'];
		}
		return $qbo_tax_code;
	}	
	
	public function get_per_line_tax_code_id($qbo_tax_code,$qbo_item,$tax_details,$qi_k=null,$qbo_inv_items=array(),$new_logic=true){		
		if(is_array($qbo_item) && is_array($tax_details)){
			$rate_arr = array();
			$rate_id = 0;$rate_id_2 = 0;
			$allow_zero_tax = true;
			if(isset($qbo_item['line_tax']) && ($qbo_item['line_tax'] > 0 || $allow_zero_tax) && isset($qbo_item['line_tax_data']) && !empty($qbo_item['line_tax_data'])){
				$line_tax_data = @unserialize($qbo_item['line_tax_data']);
				if(is_array($line_tax_data) && !empty($line_tax_data)){
					/*
					if(isset($line_tax_data['total']) && is_array($line_tax_data['total']) && !empty($line_tax_data['total'])){
						foreach($line_tax_data['total'] as $k => $v){
							$rate_id = (int) $k;
							break;
						}
					}
					*/
					
					if(isset($line_tax_data['total']) && is_array($line_tax_data['total']) && !empty($line_tax_data['total'])){
						foreach($line_tax_data['total'] as $k => $v){
							if(strlen($v) > 0){
								//$rate_id = (int) $k;
								//break;
								$rate_arr[] = (int) $k;
							}							
						}
					}
				}
			}
			
			/*
			if($rate_id > 0){
				$qtc = $this->get_qbo_mapped_tax_code($rate_id,0);
				if(!empty($qtc)){
					$qbo_tax_code = $qtc;
				}
			}
			*/
			
			if(is_array($rate_arr) && !empty($rate_arr)){
				$rate_id = $rate_arr[0];
				if(count($rate_arr) > 1){
					$rate_id_2 = $rate_arr[1];
				}
				
				$qtc = $this->get_qbo_mapped_tax_code($rate_id,$rate_id_2);
				if(!empty($qtc)){
					$qbo_tax_code = $qtc;
				}
			}
			
		}
		//$this->_p($qbo_tax_code,true);
		return $qbo_tax_code;
	}
	
	public function get_per_line_tax_code_from_shipping_line($qbo_tax_code,$qbo_item){
		$rate_id = 0;
		if(is_array($qbo_item) && !empty($qbo_item) && isset($qbo_item['taxes']) && !empty($qbo_item['taxes'])){
			$taxes = @unserialize($qbo_item['taxes']);
			if(is_array($taxes) && !empty($taxes)){
				/*
				if(isset($taxes['total']) && is_array($taxes['total']) && !empty($taxes['total'])){
					foreach($taxes['total'] as $k => $v){
						$rate_id = (int) $k;
						break;
					}
				}
				*/
				
				if(isset($taxes['total']) && is_array($taxes['total']) && !empty($taxes['total'])){
					foreach($taxes['total'] as $k => $v){
						if(strlen($v) > 0){
							$rate_id = (int) $k;
							break;
						}							
					}
				}				
			}
			
		}
		
		if($rate_id > 0){
			$qtc = $this->get_qbo_mapped_tax_code($rate_id,0);
			if(!empty($qtc)){
				$qbo_tax_code = $qtc;
			}
		}
		
		//$this->_p($qbo_tax_code,true);
		return $qbo_tax_code;		
	}
	
	protected function check_wc_is_diff_tax_per_line($tax_details){
		//For Now
		return true;
		/*
		if(is_array($tax_details) && count($tax_details) > 1){
			if($tax_details[1]['tax_amount'] > 0 && $tax_details[1]['shipping_tax_amount'] == 0){
				return true;
			}
		}
		return false;
		*/
	}
	
	public function get_discounted_item_price($discount,$subtotal,$amount){
		$item_amount = $amount-($discount/($subtotal)*$amount);
		$item_amount = number_format((float)$item_amount, 4, '.', '');
		return $item_amount;
	}

	public function get_qbo_zero_rated_tax_code($country=''){
		if($country==''){
			$country = $this->get_qbo_company_info('country');
		}
		$qbo_tax_code = '';
		if($this->is_connected()){
			if($country=='US'){
				$qbo_tax_code = 'NON';
			}else{
				$qbo_tax_code = $this->get_option('mw_wc_qbo_sync_tax_rule');
			}
		}
		return $qbo_tax_code;
	}

	public function get_tax_type($prices_include_tax='no'){
		if($prices_include_tax=='yes'){
			//return 'TaxInclusive';
		}
		//return $this->get_option('mw_wc_qbo_sync_tax_format');
		if($this->get_option('woocommerce_prices_include_tax') == 'yes'){
			return 'TaxInclusive';
		}
		//return 'TaxExclusive';
		return 'TaxExcluded';
	}
	public function is_tax_inclusive($tax_type=''){
		$tax_type = ($tax_type=='')?$this->get_tax_type():$tax_type;
		return ($tax_type=='TaxInclusive')?true:false;
	}

	/*Get qbo currency rate by date*/
	public function get_qbo_cur_rate($source_cur,$date='',$target_car=''){
		$rate = 1;
		if($this->is_connected()){
			if($date==''){
			$date = $this->now('Y-m-d');
			}

			if($source_cur!=''){
				$Context = $this->Context;
				$realm = $this->realm;

				$ExchangeRateService = new QuickBooks_IPP_Service_ExchangeRate();
				$exchangerates = $ExchangeRateService->query($Context, $realm, "SELECT * FROM ExchangeRate WHERE SourceCurrencyCode = '$source_cur' AND AsOfDate = '$date' ");
				if(!$exchangerates){
					$yesterday = date('Y-m-d',strtotime("-1 days",strtotime($this->now())));
					$exchangerates = $ExchangeRateService->query($Context, $realm, "SELECT * FROM ExchangeRate WHERE SourceCurrencyCode = '$source_cur' AND AsOfDate = '$yesterday' ");
				}
				if($exchangerates && !empty($exchangerates)){
					$ExchangeRate = $exchangerates[0];
					if($ExchangeRate->countRate()){
						$rate = $ExchangeRate->getRate();
					}
				}else{
					//get qbo home currency rate from woocommerce
					if($target_car!=''){

					}
				}
			}
		}
		return $rate;
	}

	//07-04-2017
	public function AddRefund($refund_data){
		//$this->include_sync_functions('AddRefund');
		$fn_name = 'AddRefund';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function if_refund_exists($refund_data){
		//if($refund_data['refund_id'] == '23385' || $refund_data['wc_inv_id'] == '23376'){return false;}
		if($this->is_connected()){
			$wc_inv_id = (int) $this->get_array_isset($refund_data,'wc_inv_id',0);
			$wc_rfnd_id = (int) $this->get_array_isset($refund_data,'refund_id',0);
			//
			$wc_inv_num = $this->get_array_isset($refund_data,'wc_inv_num','');
			$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
			
			/*
			if($this->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$this->get_qbo_company_setting('is_custom_txn_num_allowed')){
				$DocNumber = get_post_meta($wc_inv_id,'_mw_qbo_sync_ord_doc_no',true);
				$DocNumber = trim($DocNumber);
				if(empty($DocNumber)){
					return false;
				}
				
				$ord_id_num = $DocNumber;
			}
			*/
			
			$Context = $this->Context;
			$realm = $this->realm;
			
			$RefundReceiptService = new QuickBooks_IPP_Service_RefundReceipt();
			$refund_obj = $RefundReceiptService->query($Context, $realm, "SELECT Id FROM RefundReceipt WHERE DocNumber = '{$ord_id_num}-{$wc_rfnd_id}' ");
			if($refund_obj && !empty($refund_obj)){
				return true;
			}
		}
		return false;
	}
	
	public function if_sync_refund($refund_data){
		$a_chk_w_qbc = true;
		if($this->is_connected() || $a_chk_w_qbc){
			$manual = $this->get_array_isset($refund_data,'manual',false);
			if($manual){
				return true;
			}
			
			//$this->add_txt_to_log_file(print_r($refund_data,true));
			$_payment_method = $this->get_array_isset($refund_data,'_payment_method','',true);
			$_order_currency = $this->get_array_isset($refund_data,'_order_currency','',true);
			$pm_map_data = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);
			//$this->add_txt_to_log_file(print_r($pm_map_data,true));
			$enable_refund = (int) $this->get_array_isset($pm_map_data,'enable_refund',0);
			if($enable_refund){
				return true;
			}
		}
		return false;
	}
	
	/**/
	public function get_qs_catch_all_order_ids(){
		$ra = array();
		if($this->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$qc_it = $this->get_option('mw_wc_qbo_sync_queue_cron_interval_time');
			$qc_it_m = 0;
			
			//
			$ilp = $this->is_plg_lc_p_l();
			if(empty($qc_it)){$qc_it = 'MWQBO_5min';}
	
			$oa_qit = $this->get_qb_queue_p_til();
			if(!isset($oa_qit[$qc_it])){$qc_it = 'MWQBO_5min';}
			
			if($ilp && $qc_it != 'MWQBO_60min'){
				$qc_it = 'MWQBO_60min';
			}
			
			//if($qc_it == 'MWQBO_5min'){$qc_it_m = 5;}
			if($qc_it == 'MWQBO_10min'){$qc_it_m = 10;}
			if($qc_it == 'MWQBO_30min'){$qc_it_m = 30;}
			if($qc_it == 'MWQBO_60min'){$qc_it_m = 60;}
			
			if(!$qc_it_m){
				$qc_it_m = 10;
			}
			$qc_it_m += 30;
			//$qc_it_m = 60*24*15;
			$wp_date_time_c = $this->now();
			$last_q_int_dt = date('Y-m-d H:i:s', strtotime("-{$qc_it_m} minutes", strtotime($wp_date_time_c)));
			if(!empty($last_q_int_dt)){
				global $wpdb;
				$date_whr = " AND p.`post_modified` >= '{$last_q_int_dt}' AND p.`post_modified` <= '{$wp_date_time_c}' ";
				
				$ext_join = '';
				$ext_whr = '';
				
				$sql = "
				SELECT DISTINCT(p.ID), p.post_status, p.post_modified
				FROM
				{$wpdb->prefix}posts as p
				{$ext_join}
				WHERE
				p.post_type = 'shop_order'
				AND p.post_status NOT IN('auto-draft','trash','draft')
				{$date_whr}
				{$ext_whr}
				";
				
				$orderby = 'p.post_modified ASC';
				$sql .= ' ORDER BY  '.$orderby;
				
				//echo $sql;				
				$q_data =  $this->get_data($sql);
				//$this->_p($q_data);
				
				if(is_array($q_data) && !empty($q_data)){
					foreach($q_data as $ord){						
						$order_id = (int) $ord['ID'];						
						$payment_id = 0;						
						$opmd = $this->get_row($wpdb->prepare("SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = '_transaction_id' AND post_id = %d ",$order_id));
						if(is_array($opmd) && !empty($opmd)){
							$payment_id = (int) $opmd['meta_id'];
						}
						
						$ra[] = array(
							'order_id' => $order_id,
							'payment_id' => $payment_id
						);
					}
				}
			}
		}
		return $ra;
	}
	
	//11-05-2017
	public function get_wc_deposit_payment_list($date_whr,$gateway,$currency,$single_pmnt_id=0,$deposit_date_field='post_date'){
		if($deposit_date_field == '_paid_date'){
			$date_whr = str_replace('`{date}`','pm9.meta_value',$date_whr);
		}elseif($deposit_date_field == '_completed_date'){
			$date_whr = str_replace('`{date}`','pm14.meta_value',$date_whr);
		}else{
			$date_whr = str_replace('`{date}`','p.post_date',$date_whr);
		}
		
		$single_pmnt_id = (int) $single_pmnt_id;
		$single_whr = ($single_pmnt_id)?" AND pm8.meta_id = '{$single_pmnt_id}' ":'';

		if($single_pmnt_id){
			$date_whr = '';
		}
		$dwfjt = 'LEFT';
		global $wpdb;
		$sql = "
		SELECT DISTINCT(p.ID) as order_id, p.post_status as order_status, p.post_date as order_date, pm3.meta_value as order_total, pm5.meta_value as customer_user, pm6.meta_value as order_currency,
		pm8.meta_id as payment_id, pm8.meta_value as transaction_id, pm9.meta_value as paid_date, pm14.meta_value as completed_date, pm10.meta_value as payment_method, pim.qbo_payment_id, pm12.meta_value as stripe_txn_fee, pm13.meta_value as paypal_txn_fee, pm11.meta_value as order_number_formatted, pm_con.meta_value as _alg_wc_custom_order_number
		FROM
		{$wpdb->prefix}posts as p
		LEFT JOIN ".$wpdb->postmeta." pm3
		ON ( pm3.post_id = p.ID AND pm3.meta_key =  '_order_total' )

		LEFT JOIN ".$wpdb->postmeta." pm5
		ON ( pm5.post_id = p.ID AND pm5.meta_key =  '_customer_user' )
		LEFT JOIN ".$wpdb->postmeta." pm6
		ON ( pm6.post_id = p.ID AND pm6.meta_key =  '_order_currency' )

		INNER JOIN ".$wpdb->postmeta." pm8
		ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_transaction_id' )
		{$dwfjt} JOIN ".$wpdb->postmeta." pm9
		ON ( pm9.post_id = p.ID AND pm9.meta_key =  '_paid_date' )
		
		{$dwfjt} JOIN ".$wpdb->postmeta." pm14
		ON ( pm14.post_id = p.ID AND pm14.meta_key =  '_completed_date' )
		
		INNER JOIN ".$wpdb->postmeta." pm10
		ON ( pm10.post_id = p.ID AND pm10.meta_key =  '_payment_method' )

		LEFT JOIN ".$wpdb->postmeta." pm12
		ON ( pm12.post_id = p.ID AND pm10.meta_value = 'stripe' AND (pm12.meta_key =  'Stripe Fee' OR pm12.meta_key =  '_stripe_fee') )
		
		LEFT JOIN ".$wpdb->postmeta." pm13
		ON ( pm13.post_id = p.ID AND pm10.meta_value = 'paypal' AND pm13.meta_key =  'PayPal Transaction Fee' )
		
		LEFT JOIN ".$wpdb->postmeta." pm11
		ON ( pm11.post_id = p.ID AND pm11.meta_key =  '_order_number_formatted' )
		
		LEFT JOIN ".$wpdb->postmeta." pm_con
		ON ( pm_con.post_id = p.ID AND pm_con.meta_key =  '_alg_wc_custom_order_number' )

		LEFT JOIN {$wpdb->prefix}mw_wc_qbo_sync_payment_id_map pim ON ( pm8.meta_id = pim.wc_payment_id AND pim.is_wc_order = 0)
		WHERE
		p.post_type = 'shop_order'
		{$single_whr}
		AND pm8.meta_id > 0
		
		AND pm10.meta_value!=''
		AND pm10.meta_value = '{$gateway}'
		AND pm6.meta_value!=''
		AND pm6.meta_value = '{$currency}'
		{$date_whr}
		";
		//AND pm9.meta_value!=''
		//AND pm8.meta_value!=''

		$sql .='GROUP BY pm8.meta_id';

		$orderby = '(pm9.meta_value IS NULL) DESC, p.ID DESC';
		$sql .= ' ORDER BY  '.$orderby;


		//echo $sql;
		return $this->get_data($sql);
	}
	
	public function get_wc_deposit_sr_order_list($date_whr,$gateway,$currency,$single_ord_id=0,$deposit_date_field='post_date'){
		if($deposit_date_field == '_paid_date'){
			$date_whr = str_replace('`{date}`','pm9.meta_value',$date_whr);
		}elseif($deposit_date_field == '_completed_date'){
			$date_whr = str_replace('`{date}`','pm14.meta_value',$date_whr);
		}else{
			$date_whr = str_replace('`{date}`','p.post_date',$date_whr);
		}
		
		$single_ord_id = (int) $single_ord_id;
		$single_whr = ($single_ord_id)?" AND p.ID={$single_ord_id} ":'';
		
		if($single_ord_id){
			$date_whr = '';
		}
		$dwfjt = 'LEFT';
		global $wpdb;
		$sql = "
		SELECT DISTINCT(p.ID) as order_id, p.post_status as order_status, p.post_date as order_date, pm3.meta_value as order_total, pm5.meta_value as customer_user, pm6.meta_value as order_currency,
		pm8.meta_id as payment_id, pm8.meta_value as transaction_id, pm9.meta_value as paid_date, pm14.meta_value as completed_date, pm10.meta_value as payment_method, pim.qbo_payment_id, pm12.meta_value as stripe_txn_fee, pm13.meta_value as paypal_txn_fee, pm11.meta_value as order_number_formatted, pm_con.meta_value as _alg_wc_custom_order_number
		FROM
		{$wpdb->prefix}posts as p
		LEFT JOIN ".$wpdb->postmeta." pm3
		ON ( pm3.post_id = p.ID AND pm3.meta_key =  '_order_total' )
		
		LEFT JOIN ".$wpdb->postmeta." pm5
		ON ( pm5.post_id = p.ID AND pm5.meta_key =  '_customer_user' )
		LEFT JOIN ".$wpdb->postmeta." pm6
		ON ( pm6.post_id = p.ID AND pm6.meta_key =  '_order_currency' )
		
		INNER JOIN ".$wpdb->postmeta." pm8
		ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_transaction_id' )
		{$dwfjt} JOIN ".$wpdb->postmeta." pm9
		ON ( pm9.post_id = p.ID AND pm9.meta_key =  '_paid_date' )
		
		{$dwfjt} JOIN ".$wpdb->postmeta." pm14
		ON ( pm14.post_id = p.ID AND pm14.meta_key =  '_completed_date' )
		
		INNER JOIN ".$wpdb->postmeta." pm10
		ON ( pm10.post_id = p.ID AND pm10.meta_key =  '_payment_method' )
		
		LEFT JOIN ".$wpdb->postmeta." pm12
		ON ( pm12.post_id = p.ID AND pm10.meta_value = 'stripe' AND (pm12.meta_key =  'Stripe Fee' OR pm12.meta_key =  '_stripe_fee') )
		
		LEFT JOIN ".$wpdb->postmeta." pm13
		ON ( pm13.post_id = p.ID AND pm10.meta_value = 'paypal' AND pm13.meta_key =  'PayPal Transaction Fee' )

		LEFT JOIN ".$wpdb->postmeta." pm11
		ON ( pm11.post_id = p.ID AND pm11.meta_key =  '_order_number_formatted' )
		
		LEFT JOIN ".$wpdb->postmeta." pm_con
		ON ( pm_con.post_id = p.ID AND pm_con.meta_key =  '_alg_wc_custom_order_number' )
		
		LEFT JOIN {$wpdb->prefix}mw_wc_qbo_sync_payment_id_map pim ON ( p.ID = pim.wc_payment_id AND pim.is_wc_order = 1)
		WHERE
		p.post_type = 'shop_order'
		{$single_whr}
		AND pm8.meta_id > 0
		
		AND pm10.meta_value!=''
		AND pm10.meta_value = '{$gateway}'
		AND pm6.meta_value!=''
		AND pm6.meta_value = '{$currency}'
		{$date_whr}
		";

		$sql .='GROUP BY p.ID';
		//$sql .='GROUP BY pm8.meta_id';

		$orderby = 'p.post_date DESC';
		//$orderby = '(pm9.meta_value IS NULL) DESC, p.ID DESC';
		$sql .= ' ORDER BY  '.$orderby;


		//echo $sql;
		return $this->get_data($sql);
		
	}
	
	public function get_wc_deposit_os_payment_list($date_whr,$gateway,$currency,$order_status,$single_ord_id=0){
		$date_whr = str_replace('`{date}`','p.post_date',$date_whr);

		$single_ord_id = (int) $single_ord_id;
		$single_whr = ($single_ord_id)?" AND p.ID={$single_ord_id} ":'';

		if($single_ord_id){
			$date_whr = '';
		}

		global $wpdb;
		$sql = "
		SELECT DISTINCT(p.ID) as order_id, p.post_status as order_status, p.post_date as order_date, pm3.meta_value as order_total, pm5.meta_value as customer_user, pm6.meta_value as order_currency,
		pm10.meta_value as payment_method, pim.qbo_payment_id, pm11.meta_value as order_number_formatted, pm_con.meta_value as _alg_wc_custom_order_number
		FROM
		{$wpdb->prefix}posts as p
		LEFT JOIN ".$wpdb->postmeta." pm3
		ON ( pm3.post_id = p.ID AND pm3.meta_key =  '_order_total' )

		LEFT JOIN ".$wpdb->postmeta." pm5
		ON ( pm5.post_id = p.ID AND pm5.meta_key =  '_customer_user' )
		LEFT JOIN ".$wpdb->postmeta." pm6
		ON ( pm6.post_id = p.ID AND pm6.meta_key =  '_order_currency' )

		INNER JOIN ".$wpdb->postmeta." pm10
		ON ( pm10.post_id = p.ID AND pm10.meta_key =  '_payment_method' )

		LEFT JOIN ".$wpdb->postmeta." pm11
		ON ( pm11.post_id = p.ID AND pm11.meta_key =  '_order_number_formatted' )
		
		LEFT JOIN ".$wpdb->postmeta." pm_con
		ON ( pm_con.post_id = p.ID AND pm_con.meta_key =  '_alg_wc_custom_order_number' )

		LEFT JOIN {$wpdb->prefix}mw_wc_qbo_sync_payment_id_map pim ON ( p.ID = pim.wc_payment_id AND pim.is_wc_order = 1)
		WHERE
		p.post_type = 'shop_order'
		{$single_whr}
		AND p.post_status = '{$order_status}'
		AND pm10.meta_value!=''
		AND pm10.meta_value = '{$gateway}'
		AND pm6.meta_value!=''
		AND pm6.meta_value = '{$currency}'
		{$date_whr}
		";

		$sql .='GROUP BY p.ID';

		$orderby = 'p.post_date DESC';
		$sql .= ' ORDER BY  '.$orderby;


		//echo $sql;
		return $this->get_data($sql);
	}

	public function get_dps_utc_time_arr(){
		$utc_arr = array();
		for($hours=0; $hours<24; $hours++){
			for($mins=0; $mins<60; $mins+=30){
				$ts = str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($mins,2,'0',STR_PAD_LEFT);
				$utc_arr[$ts] = $ts;
			}
		}
		return $utc_arr;
	}
	
	public function get_dps_sch_arr(){
		return array(
			'Daily' => 'Daily',
			'Monday' => 'Weekly - Monday',
			'Tuesday' => 'Weekly - Tuesday',
			'Wednesday' => 'Weekly - Wednesday',
			'Thursday' => 'Weekly - Thursday',
			'Friday' => 'Weekly - Friday',			
		);
	}
	
	public function get_dps_cron_ser_str(){
		$dps = '';
		$allow_this = true;
		if($allow_this || $this->is_connected()){
			global $wpdb;
			$p_maps_q = "SELECT * FROM `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` WHERE `id` >0 AND `enable_payment` = 1 AND `qbo_account_id` > 0 AND `enable_batch` = 1 AND `individual_batch_support` = 0 AND `wc_paymentmethod` !='' AND `deposit_cron_utc` !='' ";

			$p_maps_data = $this->get_data($p_maps_q);
			if(is_array($p_maps_data) && !empty($p_maps_data)){
				//$this->_p($p_maps_data);
				$dps_arr = array();
				$t_arr = array();
				foreach($p_maps_data as $pmd){
					$t_arr[$pmd['deposit_cron_utc']][$pmd['wc_paymentmethod']][] = $pmd['currency'];
				}
				$dps_arr['deposit_cron_url'] = base64_encode(site_url('index.php?mw_qbo_sync_public_deposit_cron=1'));
				$dps_arr['c_items'] = $t_arr;
				//$this->_p($dps_arr);
				$dps = serialize($dps_arr);
				$dps = base64_encode($dps);
			}
		}
		return $dps;
	}
	
	//06-11-2017
	public function Cron_Deposit_Sr($gateways=array(),$single_ord_id=0,$currency=array()){
		if($this->is_connected()){
			if(!$this->get_qbo_company_setting('is_deposit_allowed')){
				$this->save_log('Export Deposit Error','QuickBooks Deposit Not Allowed','Deposit',0);
				return false;
			}
			
			if($single_ord_id && !$this->is_order_sync_as_sales_receipt($single_ord_id)){
				return false;
			}
			
			if($this->is_plg_lc_p_l()){
				return false;
			}
			
			global $wpdb;
			$single_ord_id = (int) $single_ord_id;
			
			$p_map_whr = '';
			if(is_array($gateways) && !empty($gateways)){
				$pgm_str = '';
				foreach($gateways as $gt){
					if($gt!=''){
						$gt = esc_sql($gt);
						$pgm_str.="'{$gt}',";
					}
				}
				if($pgm_str!=''){
					$pgm_str = substr($pgm_str,0,-1);
					$p_map_whr = " AND `wc_paymentmethod` IN ($pgm_str)";
				}

			}
			$ibs_whr = ' AND `individual_batch_support` = 0 ';
			if($single_ord_id){
				$ibs_whr = " AND `individual_batch_support` = 1 ";
			}
			
			if(is_array($currency) && !empty($currency)){
				$pgm_str = '';
				foreach($currency as $gt){
					if($gt!=''){
						$gt = esc_sql($gt);
						$pgm_str.="'{$gt}',";
					}
				}
				if($pgm_str!=''){
					$pgm_str = substr($pgm_str,0,-1);
					$p_map_whr.= " AND `currency` IN ($pgm_str)";
				}
			}

			$p_maps_q = "SELECT * FROM `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` WHERE `id` >0 AND `enable_payment` = 1 AND `qbo_account_id` > 0 AND `enable_batch` = 1 {$ibs_whr} AND `wc_paymentmethod` !='' {$p_map_whr}";

			$p_maps_data = $this->get_data($p_maps_q);
			
			if(is_array($p_maps_data) && !empty($p_maps_data)){
				foreach($p_maps_data as $pmd){
					$total_deposit_amnt = 0;
					$total_pmnt_amnt = 0;
					$total_txn_fee = 0;

					$today = $this->now('Y-m-d');
					$day_name = strtolower($this->now('l'));

					$wc_paymentmethod = $pmd['wc_paymentmethod'];
					
					$is_dps_dt_applied = false;
					//New Changes
					$deposit_cron_sch = $pmd['deposit_cron_sch'];
					$is_d_daily = (empty($deposit_cron_sch) || $deposit_cron_sch == 'Daily')?true:false;
					//
					$deposit_cron_utc = $pmd['deposit_cron_utc'];
					$wp_timezone = $this->get_sys_timezone();
					
					//
					$cdi_hr = 24;
					$cdi_d = 1;
					if(!empty($deposit_cron_utc) && !empty($wp_timezone)){
						$utc_now = new DateTime();
						$utc_now->setTimezone(new DateTimeZone('UTC'));
						$utc_date = $utc_now->format('Y-m-d');
						$utc_date_time = $utc_date.' '.$deposit_cron_utc.':00';						
						
						$wp_date_time_c = $this->converToTz($utc_date_time,$wp_timezone,'UTC');
						//
						if(!$is_d_daily){
							//$dn_c = date('l',strtotime($wp_date_time_c));
							$dn_c = $this->now('l');
							if($deposit_cron_sch != $dn_c){
								continue;
							}
							
							$cdi_hr = 168;
						}
						
						$last_24_hour_dt = date('Y-m-d H:i:s', strtotime('-'.$cdi_hr.' hours', strtotime($wp_date_time_c)));
						if(!empty($last_24_hour_dt)){							
							$date_whr = " AND `{date}` >= '{$last_24_hour_dt}' AND `{date}` <= '{$wp_date_time_c}' ";
							$is_dps_dt_applied = true;
						}						
					}
					
					if(!$is_dps_dt_applied){
						$date_whr = " AND `{date}` >= now() - INTERVAL {$cdi_d} DAY ";
					}					
					
					$lump_weekend_batches = (int) $pmd['lump_weekend_batches'];
					//
					if(!$is_d_daily){
						$lump_weekend_batches = false;
					}
					
					if($lump_weekend_batches){
						if($day_name=='saturday'){
							continue;
						}
						if($day_name=='sunday'){
							/*
							$yesterday = date('Y-m-d', strtotime('-1 day', strtotime($today)));
							$date_whr = " AND `{date}` >= now() - INTERVAL 2 DAY ";
							*/
							continue;
						}
						
						if($day_name=='monday'){
							if(!$is_dps_dt_applied){
								$date_whr = " AND `{date}` >= now() - INTERVAL 3 DAY ";
							}else{
								$last_72_hour_dt = date('Y-m-d H:i:s', strtotime('-72 hours', strtotime($wp_date_time_c)));
								$date_whr = " AND `{date}` >= '{$last_72_hour_dt}' AND `{date}` <= '{$wp_date_time_c}' ";
							}							
						}
					}

					$pmap_currency = $pmd['currency'];
					$payment_cur = $pmap_currency;
					$cur_rate = 1;
					
					$wc_inv_ids = array();
					$wc_inv_ids_int = array();					
					
					$ps_order_status = trim($pmd['ps_order_status']);
					$deposit_date_field = trim($pmd['deposit_date_field']);
					$p_list_arr = array();
					if($ps_order_status!=''){
						continue;						
					}else{
						$p_list_arr = $this->get_wc_deposit_sr_order_list($date_whr,$wc_paymentmethod,$payment_cur,$single_ord_id,$deposit_date_field);
					}
					
					//$this->_p($p_list_arr);die;
					$wc_qb_sr_map_arr = array();
					if(is_array($p_list_arr) && !empty($p_list_arr)){
						foreach($p_list_arr as $p_list){
							$wc_order_id = (int) $p_list['order_id'];
							
							/**/
							$order_number_formatted = $this->get_woo_ord_number_from_order($wc_order_id,$p_list);
							
							$qbo_sr_id = (int) $this->get_qbo_salesreceipt_id($wc_order_id,$order_number_formatted);
							
							if($qbo_sr_id>0){
								$wc_qb_sr_map_arr[$wc_order_id] = $qbo_sr_id;
								$total_pmnt_amnt+=$p_list['order_total'];
								/*
								$total_txn_fee+=(($p_list['payment_method']=='stripe' || $p_list['payment_method']=='paypal') && isset($p_list[$wc_paymentmethod.'_txn_fee']))?(float) $p_list[$wc_paymentmethod.'_txn_fee']:0;
								*/
							
								$tfli_data = $this->get_order_txn_fee_data_by_id($wc_order_id);
								if(is_array($tfli_data)){
									$total_txn_fee+=$tfli_data['t_f_amnt'];
								}
								
								//$total_txn_fee = 0;
								
								if($order_number_formatted!=''){
									$wc_inv_ids[] = '#'.$order_number_formatted;
								}else{
									$wc_inv_ids[] = '#'.$p_list['order_id'];
								}
								
								$wc_inv_ids_int[] = $p_list['order_id'];								

							}
						}
					}
					
					//Deposit Debug
					$deposit_debug_log = true;
					if($deposit_debug_log){
						$dd_log_d = 'Datetime -> '.$this->now('Y-m-d H:i:s').PHP_EOL;
						$dd_log_d .= 'Query Date Where -> '.$date_whr.PHP_EOL;
						
						$dd_o_nums = (!empty($wc_inv_ids))?implode(',',$wc_inv_ids):'';						
						$dd_log_d .= 'Orders -> '.$dd_o_nums;
						
						$this->save_log('Deposit Debug',$dd_log_d,'Deposit',2,false);
					}
					
					if($pmd['enable_transaction'] && $pmd['txn_expense_acc_id']){
						$total_deposit_amnt = $total_pmnt_amnt-$total_txn_fee;						
					}else{
						$total_deposit_amnt = $total_pmnt_amnt;
					}
					
					
					$total_deposit_amnt = $total_pmnt_amnt;

					$batch_support_rf = true;
					$b_rf_arr  = array();
					$total_b_rf_amnt = 0;

					if($batch_support_rf && !empty($wc_inv_ids_int)){
						$total_deposit_amnt = $total_deposit_amnt-$total_b_rf_amnt;
					}

					if($total_deposit_amnt>0){
						$Context = $this->Context;
						$realm = $this->realm;

						$DepositService = new QuickBooks_IPP_Service_Deposit();
						$Deposit = new QuickBooks_IPP_Object_Deposit();

						$Deposit->setDepositToAccountRef($pmd['qbo_account_id']);
						$Deposit->setTotalAmt($total_deposit_amnt);
						$Deposit->setTxnDate($this->now('Y-m-d'));
						
						//Deposit Memo Add
						$Dps_Memo = 'Orders: ';
						if(is_array($wc_inv_ids) && !empty($wc_inv_ids)){
							$Dps_Memo.= implode(',',$wc_inv_ids);
						}
						$Deposit->setPrivateNote($Dps_Memo);
						
						$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
						
						$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
						$_order_currency = $payment_cur;
						if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){

							$currency_rate_date = $this->now('Y-m-d');
							$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);

							$Deposit->setCurrencyRef($_order_currency);
							$Deposit->setExchangeRate($currency_rate);
						}
						
						if(is_array($p_list_arr) && !empty($p_list_arr)){
							foreach($p_list_arr as $p_list){
								$qbo_sr_id = (isset($wc_qb_sr_map_arr[$p_list['order_id']]))?(int) $wc_qb_sr_map_arr[$p_list['order_id']]:0;
								$amount = $p_list['order_total'];
								if($qbo_sr_id>0){
									$Line = new QuickBooks_IPP_Object_Line();
									$Line->setAmount($amount);

									$LinkedTxn = new QuickBooks_IPP_Object_LinkedTxn();
									$LinkedTxn->setTxnId($qbo_sr_id);
									$LinkedTxn->setTxnType('SalesReceipt');
									$LinkedTxn->setTxnLineId(0);

									$Line->setLinkedTxn($LinkedTxn);

									$Deposit->addLine($Line);
								}
							}
						}
						
						
						if($pmd['enable_transaction'] && $pmd['txn_expense_acc_id']){
							$Line = new QuickBooks_IPP_Object_Line();
							$dp_amnt = -1 * $this->_abs($total_txn_fee);
							$Line->setAmount($dp_amnt);

							$dp_line_desc = "Transaction Fees for $wc_paymentmethod ".'('.$this->now('m/d').')';
							$Line->setDescription($dp_line_desc);

							$Line->setDetailType('DepositLineDetail');
							$DepositLineDetail = new QuickBooks_IPP_Object_DepositLineDetail();

							$dp_vendor_id = $pmd['vendor_id'];
							$qb_p_method_id = $pmd['qb_p_method_id'];

							if($dp_vendor_id>0){
								$DepositLineDetail->setEntity($dp_vendor_id);
							}

							$DepositLineDetail->setAccountRef($pmd['txn_expense_acc_id']);
							if($qb_p_method_id>0){
								$DepositLineDetail->setPaymentMethodRef($qb_p_method_id);
							}
							
							//
							if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
								$DepositLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
							}
							
							$Line->addDepositLineDetail($DepositLineDetail);
							$Deposit->addLine($Line);
						}
						
						
						if($total_b_rf_amnt>0){
							$Line = new QuickBooks_IPP_Object_Line();
							$rf_amnt = -1 * $this->_abs($total_b_rf_amnt);

							$Line->setAmount($rf_amnt);
							$dp_line_desc = "Refund for $wc_paymentmethod ".'('.$this->now('m/d').')';

							$Line->setDescription($dp_line_desc);
							$Line->setDetailType('DepositLineDetail');
							$DepositLineDetail = new QuickBooks_IPP_Object_DepositLineDetail();

							$dp_vendor_id = $pmd['vendor_id'];
							$qb_p_method_id = $pmd['qb_p_method_id'];

							if($dp_vendor_id>0){
								$DepositLineDetail->setEntity($dp_vendor_id);
							}

							$DepositLineDetail->setAccountRef($pmd['txn_expense_acc_id']);
							if($qb_p_method_id>0){
								$DepositLineDetail->setPaymentMethodRef($qb_p_method_id);
							}

							$Line->addDepositLineDetail($DepositLineDetail);
							//$Deposit->addLine($Line);
						}

						//$this->_p($Deposit);
						//return false;
						
						$log_title = "";
						$log_details = "";
						$log_status = 0;

						if ($resp = $DepositService->add($Context, $realm, $Deposit)){
							$qbo_dpst_id = $this->qbo_clear_braces($resp);
							$log_title.="Export Deposit\n";
							if(count($wc_inv_ids)>1){
								$log_details.="Created Deposit with ".count($wc_inv_ids)." Orders\n";
							}else{
								$log_details.="Created Deposit with ".count($wc_inv_ids)." Order\n";
							}
							$log_details.="Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}, QuickBooks Deposit ID #{$qbo_dpst_id}";
							
							//
							$log_details.="\nBank Deposit Total: {$total_deposit_amnt}";
							
							if(!empty($wc_inv_ids)){
								$log_details.="\nWooCommerce Orders Included: ".implode(', ',$wc_inv_ids)."\n";
							}							
							
							$log_status = 1;
							$this->save_log($log_title,$log_details,'Deposit',$log_status,true,'Add');
							$this->add_qbo_item_obj_into_log_file('Deposit Add',$gateways,$Deposit,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
							return $qbo_dpst_id;

						}else{
							$res_err = $DepositService->lastError($Context);
							$log_title.="Export Deposit Error\n";
							$log_details.="Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}\n";

							if(!empty($wc_inv_ids)){
								$log_details.="WooCommerce Orders Included: ".implode(', ',$wc_inv_ids)."\n";
							}							

							$log_details.="Error:{$res_err}";
							$this->save_log($log_title,$log_details,'Deposit',$log_status,true,'Add');
							$this->add_qbo_item_obj_into_log_file('Deposit Add',$gateways,$Deposit,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
							return false;
						}
					}else{
						if(!$single_ord_id){
							$this->save_log('Deposit cron run - no payments to process.',"Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}",'Deposit',2);
						}else{
							$idi = 'Order';
							$this->save_log('Export Deposit Error (Individual)',"Incorrect deposit amount\n{$idi} #{$single_ord_id} Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}",'Deposit',2);
						}
					}
				}
			}
			
		}
	}
	
	public function Cron_Deposit($gateways=array(),$single_pmnt_id=0,$currency=array()){
		if($this->is_connected()){
			if($this->is_plg_lc_p_l()){
				return false;
			}
			if(!$this->get_qbo_company_setting('is_deposit_allowed')){
				$this->save_log('Export Deposit Error','QuickBooks Deposit Not Allowed','Deposit',0);
				return false;
			}
			global $wpdb;

			$single_pmnt_id = (int) $single_pmnt_id;
			
			/*
			if($single_pmnt_id && $this->is_order_sync_as_estimate($single_ord_id)){
				return false;
			}
			*/
			
			if($single_pmnt_id && !$this->is_order_sync_as_invoice($single_ord_id)){
				return false;
			}
			
			//
			$ospg = (!$single_pmnt_id && $this->get_option('mw_wc_qbo_sync_order_qbo_sync_as') == 'Per Gateway')?true:false;
			
			$p_map_whr = '';
			if(is_array($gateways) && !empty($gateways)){
				$pgm_str = '';
				foreach($gateways as $gt){
					if($gt!=''){
						$gt = esc_sql($gt);
						$pgm_str.="'{$gt}',";
					}
				}
				if($pgm_str!=''){
					$pgm_str = substr($pgm_str,0,-1);
					$p_map_whr = " AND `wc_paymentmethod` IN ($pgm_str)";
				}

			}
			$ibs_whr = ' AND `individual_batch_support` = 0 ';
			if($single_pmnt_id){
				$ibs_whr = " AND `individual_batch_support` = 1 ";
			}

			if(is_array($currency) && !empty($currency)){
				$pgm_str = '';
				foreach($currency as $gt){
					if($gt!=''){
						$gt = esc_sql($gt);
						$pgm_str.="'{$gt}',";
					}
				}
				if($pgm_str!=''){
					$pgm_str = substr($pgm_str,0,-1);
					$p_map_whr.= " AND `currency` IN ($pgm_str)";
				}
			}

			$p_maps_q = "SELECT * FROM `{$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map` WHERE `id` >0 AND `enable_payment` = 1 AND `qbo_account_id` > 0 AND `enable_batch` = 1 {$ibs_whr} AND `wc_paymentmethod` !='' {$p_map_whr}";

			$p_maps_data = $this->get_data($p_maps_q);

			if(is_array($p_maps_data) && !empty($p_maps_data)){
				foreach($p_maps_data as $pmd){
					$total_deposit_amnt = 0;
					$total_pmnt_amnt = 0;
					$total_txn_fee = 0;

					$today = $this->now('Y-m-d');
					$day_name = strtolower($this->now('l'));

					$wc_paymentmethod = $pmd['wc_paymentmethod'];
					//
					$pg_osa = $pmd['order_sync_as'];
					$pg_npd = false;
					if($ospg && $pg_osa == 'Estimate'){
						$pg_npd = true;
						continue;
					}
					
					$is_dps_dt_applied = false;
					//New Changes
					$deposit_cron_sch = $pmd['deposit_cron_sch'];
					$is_d_daily = (empty($deposit_cron_sch) || $deposit_cron_sch == 'Daily')?true:false;
					//
					$deposit_cron_utc = $pmd['deposit_cron_utc'];
					$wp_timezone = $this->get_sys_timezone();
					
					//
					$cdi_hr = 24;
					$cdi_d = 1;
					if(!empty($deposit_cron_utc) && !empty($wp_timezone)){
						$utc_now = new DateTime();
						$utc_now->setTimezone(new DateTimeZone('UTC'));
						$utc_date = $utc_now->format('Y-m-d');
						$utc_date_time = $utc_date.' '.$deposit_cron_utc.':00';						
						
						$wp_date_time_c = $this->converToTz($utc_date_time,$wp_timezone,'UTC');
						//
						if(!$is_d_daily){
							//$dn_c = date('l',strtotime($wp_date_time_c));
							$dn_c = $this->now('l');
							if($deposit_cron_sch != $dn_c){
								continue;
							}
							
							$cdi_hr = 168;
						}
						
						$last_24_hour_dt = date('Y-m-d H:i:s', strtotime('-'.$cdi_hr.' hours', strtotime($wp_date_time_c)));
						if(!empty($last_24_hour_dt)){							
							$date_whr = " AND `{date}` >= '{$last_24_hour_dt}' AND `{date}` <= '{$wp_date_time_c}' ";
							$is_dps_dt_applied = true;
						}						
					}
					
					if(!$is_dps_dt_applied){
						$date_whr = " AND `{date}` >= now() - INTERVAL {$cdi_d} DAY ";
					}					
					
					$lump_weekend_batches = (int) $pmd['lump_weekend_batches'];
					//
					if(!$is_d_daily){
						$lump_weekend_batches = false;
					}
					
					if($lump_weekend_batches){
						if($day_name=='saturday'){
							continue;
						}
						if($day_name=='sunday'){
							/*
							$yesterday = date('Y-m-d', strtotime('-1 day', strtotime($today)));
							$date_whr = " AND `{date}` >= now() - INTERVAL 2 DAY ";
							*/
							continue;
						}
						
						if($day_name=='monday'){
							if(!$is_dps_dt_applied){
								$date_whr = " AND `{date}` >= now() - INTERVAL 3 DAY ";
							}else{
								$last_72_hour_dt = date('Y-m-d H:i:s', strtotime('-72 hours', strtotime($wp_date_time_c)));
								$date_whr = " AND `{date}` >= '{$last_72_hour_dt}' AND `{date}` <= '{$wp_date_time_c}' ";
							}
							
						}
					}
					
					$pmap_currency = $pmd['currency'];
					$payment_cur = $pmap_currency;
					$cur_rate = 1;

					$wc_inv_ids = array();
					$wc_pmnt_ids = array();

					$wc_inv_ids_int = array();
					$wc_pmnt_ids_int = array();
					
					$ps_order_status = trim($pmd['ps_order_status']);
					$deposit_date_field = trim($pmd['deposit_date_field']);
					$p_list_arr = array();
					if($ps_order_status!=''){
						continue;
						//$p_list_arr = $this->get_wc_deposit_os_payment_list($date_whr,$wc_paymentmethod,$payment_cur,$ps_order_status,$single_pmnt_id);
					}else{
						if($ospg && $pg_osa == 'SalesReceipt'){
							$pg_npd = true;
							$p_list_arr = $this->get_wc_deposit_sr_order_list($date_whr,$wc_paymentmethod,$payment_cur,0,$deposit_date_field);
						}else{
							$p_list_arr = $this->get_wc_deposit_payment_list($date_whr,$wc_paymentmethod,$payment_cur,$single_pmnt_id,$deposit_date_field);
						}						
					}
					
					//$this->_p($p_list_arr);die;
					if(!$pg_npd && is_array($p_list_arr) && !empty($p_list_arr)){
						foreach($p_list_arr as $p_list){
							$wc_payment_id = (int) $p_list['payment_id'];
							$qbo_payment_id = (int) $p_list['qbo_payment_id'];

							if($qbo_payment_id>0){
								$total_pmnt_amnt+=$p_list['order_total'];
								/*
								$total_txn_fee+=(($p_list['payment_method']=='stripe' || $p_list['payment_method']=='paypal') && isset($p_list[$wc_paymentmethod.'_txn_fee']))?(float) $p_list[$wc_paymentmethod.'_txn_fee']:0;
								*/
								
								$tfli_data = $this->get_order_txn_fee_data_by_id($p_list['order_id']);
								if(is_array($tfli_data)){
									$total_txn_fee+=$tfli_data['t_f_amnt'];
								}
								
								//$total_txn_fee = 0;
								
								/**/
								$order_number_formatted = $this->get_woo_ord_number_from_order($p_list['order_id'],$p_list);
								if(!empty($order_number_formatted)){
									$wc_inv_ids[] = '#'.$order_number_formatted;
								}else{
									$wc_inv_ids[] = '#'.$p_list['order_id'];
								}

								$wc_pmnt_ids[] = '#'.$p_list['payment_id'];

								$wc_inv_ids_int[] = $p_list['order_id'];
								$wc_pmnt_ids_int[] = $p_list['payment_id'];

							}
						}
					}
					
					/**/
					$wc_qb_sr_map_arr = array();
					if($pg_npd && is_array($p_list_arr) && !empty($p_list_arr)){
						foreach($p_list_arr as $p_list){
							$wc_order_id = (int) $p_list['order_id'];
							
							/**/
							$order_number_formatted = $this->get_woo_ord_number_from_order($wc_order_id,$p_list);
							
							$qbo_sr_id = (int) $this->get_qbo_salesreceipt_id($wc_order_id,$order_number_formatted);
							
							if($qbo_sr_id>0){
								$wc_qb_sr_map_arr[$wc_order_id] = $qbo_sr_id;
								$total_pmnt_amnt+=$p_list['order_total'];
								/*
								$total_txn_fee+=(($p_list['payment_method']=='stripe' || $p_list['payment_method']=='paypal') && isset($p_list[$wc_paymentmethod.'_txn_fee']))?(float) $p_list[$wc_paymentmethod.'_txn_fee']:0;
								*/
							
								$tfli_data = $this->get_order_txn_fee_data_by_id($wc_order_id);
								if(is_array($tfli_data)){
									$total_txn_fee+=$tfli_data['t_f_amnt'];
								}
								
								//$total_txn_fee = 0;
								
								if($order_number_formatted!=''){
									$wc_inv_ids[] = '#'.$order_number_formatted;
								}else{
									$wc_inv_ids[] = '#'.$p_list['order_id'];
								}
								
								$wc_inv_ids_int[] = $p_list['order_id'];								

							}
						}
					}
					
					//Deposit Debug
					$deposit_debug_log = true;
					if($deposit_debug_log){
						$dd_log_d = 'Datetime -> '.$this->now('Y-m-d H:i:s').PHP_EOL;
						$dd_log_d .= 'Query Date Where -> '.$date_whr.PHP_EOL;
						
						$dd_p_ids = (!empty($wc_pmnt_ids))?implode(',',$wc_pmnt_ids):'';
						$dd_o_nums = (!empty($wc_inv_ids))?implode(',',$wc_inv_ids):'';
						
						$dd_log_d .= 'Payments -> '.$dd_p_ids.PHP_EOL;
						$dd_log_d .= 'Orders -> '.$dd_o_nums;
						
						$this->save_log('Deposit Debug',$dd_log_d,'Deposit',2,false);
					}					
					
					if($pmd['enable_transaction'] && $pmd['txn_expense_acc_id']){
						$total_deposit_amnt = $total_pmnt_amnt-$total_txn_fee;
						//$total_deposit_amnt = $total_pmnt_amnt;
					}else{
						$total_deposit_amnt = $total_pmnt_amnt;
					}
					
					//
					if($pg_npd){
						$total_deposit_amnt = $total_pmnt_amnt;
					}
					
					$batch_support_rf = true;
					$b_rf_arr  = array();
					$total_b_rf_amnt = 0;

					if(!$pg_npd && $batch_support_rf && !empty($wc_pmnt_ids_int)){
						$total_deposit_amnt = $total_deposit_amnt-$total_b_rf_amnt;
					}
					
					//
					if($pg_npd && $batch_support_rf && !empty($wc_inv_ids_int)){
						$total_deposit_amnt = $total_deposit_amnt-$total_b_rf_amnt;
					}
					
					if($total_deposit_amnt>0){
						$Context = $this->Context;
						$realm = $this->realm;

						$DepositService = new QuickBooks_IPP_Service_Deposit();
						$Deposit = new QuickBooks_IPP_Object_Deposit();

						$Deposit->setDepositToAccountRef($pmd['qbo_account_id']);
						$Deposit->setTotalAmt($total_deposit_amnt);
						$Deposit->setTxnDate($this->now('Y-m-d'));
						
						//Deposit Memo Add
						$Dps_Memo = 'Orders: ';
						if(is_array($wc_inv_ids) && !empty($wc_inv_ids)){
							$Dps_Memo.= implode(',',$wc_inv_ids);
						}
						$Deposit->setPrivateNote($Dps_Memo);
						
						$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');

						$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
						$_order_currency = $payment_cur;
						if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){

							$currency_rate_date = $this->now('Y-m-d');
							$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);

							$Deposit->setCurrencyRef($_order_currency);
							$Deposit->setExchangeRate($currency_rate);
						}

						if(!$pg_npd && is_array($p_list_arr) && !empty($p_list_arr)){
							foreach($p_list_arr as $p_list){
								$qbo_payment_id = (int) $p_list['qbo_payment_id'];
								$amount = $p_list['order_total'];
								if($qbo_payment_id>0){
									$Line = new QuickBooks_IPP_Object_Line();
									$Line->setAmount($amount);

									$LinkedTxn = new QuickBooks_IPP_Object_LinkedTxn();
									$LinkedTxn->setTxnId($qbo_payment_id);
									$LinkedTxn->setTxnType('Payment');
									$LinkedTxn->setTxnLineId(0);

									$Line->setLinkedTxn($LinkedTxn);

									$Deposit->addLine($Line);
								}
							}
						}
						
						//
						if($pg_npd && is_array($p_list_arr) && !empty($p_list_arr)){
							foreach($p_list_arr as $p_list){
								$qbo_sr_id = (isset($wc_qb_sr_map_arr[$p_list['order_id']]))?(int) $wc_qb_sr_map_arr[$p_list['order_id']]:0;
								$amount = $p_list['order_total'];
								if($qbo_sr_id>0){
									$Line = new QuickBooks_IPP_Object_Line();
									$Line->setAmount($amount);

									$LinkedTxn = new QuickBooks_IPP_Object_LinkedTxn();
									$LinkedTxn->setTxnId($qbo_sr_id);
									$LinkedTxn->setTxnType('SalesReceipt');
									$LinkedTxn->setTxnLineId(0);

									$Line->setLinkedTxn($LinkedTxn);

									$Deposit->addLine($Line);
								}
							}
						}
						
						if($pmd['enable_transaction'] && $pmd['txn_expense_acc_id']){
							$Line = new QuickBooks_IPP_Object_Line();
							$dp_amnt = -1 * $this->_abs($total_txn_fee);
							$Line->setAmount($dp_amnt);

							$dp_line_desc = "Transaction Fees for $wc_paymentmethod ".'('.$this->now('m/d').')';
							$Line->setDescription($dp_line_desc);

							$Line->setDetailType('DepositLineDetail');
							$DepositLineDetail = new QuickBooks_IPP_Object_DepositLineDetail();

							$dp_vendor_id = $pmd['vendor_id'];
							$qb_p_method_id = $pmd['qb_p_method_id'];

							if($dp_vendor_id>0){
								$DepositLineDetail->setEntity($dp_vendor_id);
							}

							$DepositLineDetail->setAccountRef($pmd['txn_expense_acc_id']);
							if($qb_p_method_id>0){
								$DepositLineDetail->setPaymentMethodRef($qb_p_method_id);
							}
							
							//
							if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
								$DepositLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
							}
							
							$Line->addDepositLineDetail($DepositLineDetail);
							$Deposit->addLine($Line);
						}

						if($total_b_rf_amnt>0){
							$Line = new QuickBooks_IPP_Object_Line();
							$rf_amnt = -1 * $this->_abs($total_b_rf_amnt);

							$Line->setAmount($rf_amnt);
							$dp_line_desc = "Refund for $wc_paymentmethod ".'('.$this->now('m/d').')';

							$Line->setDescription($dp_line_desc);
							$Line->setDetailType('DepositLineDetail');
							$DepositLineDetail = new QuickBooks_IPP_Object_DepositLineDetail();

							$dp_vendor_id = $pmd['vendor_id'];
							$qb_p_method_id = $pmd['qb_p_method_id'];

							if($dp_vendor_id>0){
								$DepositLineDetail->setEntity($dp_vendor_id);
							}

							$DepositLineDetail->setAccountRef($pmd['txn_expense_acc_id']);
							if($qb_p_method_id>0){
								$DepositLineDetail->setPaymentMethodRef($qb_p_method_id);
							}

							$Line->addDepositLineDetail($DepositLineDetail);
							//$Deposit->addLine($Line);
						}

						//$this->_p($Deposit);
						//return false;
						
						$log_title = "";
						$log_details = "";
						$log_status = 0;

						if ($resp = $DepositService->add($Context, $realm, $Deposit)){
							$qbo_dpst_id = $this->qbo_clear_braces($resp);
							$log_title.="Export Deposit\n";
							
							if(!$pg_npd){
								if(count($wc_pmnt_ids)>1){
									$log_details.="Created Deposit with ".count($wc_pmnt_ids)." Payments\n";
								}else{
									$log_details.="Created Deposit with ".count($wc_pmnt_ids)." Payment\n";
								}
							}else{
								if(count($wc_inv_ids)>1){
									$log_details.="Created Deposit with ".count($wc_inv_ids)." Orders\n";
								}else{
									$log_details.="Created Deposit with ".count($wc_inv_ids)." Order\n";
								}
							}
							
							$log_details.="Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}, QuickBooks Deposit ID #{$qbo_dpst_id}";
							
							//
							$log_details.="\nBank Deposit Total: {$total_deposit_amnt}";
							
							if(!empty($wc_inv_ids)){
								$log_details.="\nWooCommerce Orders Included: ".implode(', ',$wc_inv_ids)."\n";
							}
							if(!$pg_npd && !empty($wc_pmnt_ids)){
								$log_details.='WooCommerce Payments Included: '.implode(', ',$wc_pmnt_ids)."\n";
							}
							
							$log_status = 1;
							$this->save_log($log_title,$log_details,'Deposit',$log_status,true,'Add');
							$this->add_qbo_item_obj_into_log_file('Deposit Add',$gateways,$Deposit,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
							return $qbo_dpst_id;

						}else{
							$res_err = $DepositService->lastError($Context);
							$log_title.="Export Deposit Error\n";
							$log_details.="Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}\n";

							if(!empty($wc_inv_ids)){
								$log_details.="WooCommerce Orders Included: ".implode(', ',$wc_inv_ids)."\n";
							}
							if(!$pg_npd && !empty($wc_pmnt_ids)){
								$log_details.='WooCommerce Payments Included: '.implode(', ',$wc_pmnt_ids)."\n";
							}
							
							$log_details.="Error:{$res_err}";
							$this->save_log($log_title,$log_details,'Deposit',$log_status,true,'Add');
							$this->add_qbo_item_obj_into_log_file('Deposit Add',$gateways,$Deposit,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
							return false;
						}
					}else{
						if(!$single_pmnt_id){
							$this->save_log('Deposit cron run - no payments to process.',"Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}",'Deposit',2);
						}else{
							$idi = ($ps_order_status!='')?'Order':'Payment';
							$this->save_log('Export Deposit Error (Individual)',"Incorrect deposit amount\n{$idi} #{$single_pmnt_id} Gateway: {$wc_paymentmethod} , Currency: {$pmap_currency}",'Deposit',2);
						}
					}

				}
			}
		}
	}
	
	public function Sr_AddJournalEntry($je_data,$je_refund=false){
		if($this->is_connected()){
			$manual = $this->get_array_isset($je_data,'manual',false);
			$wc_inv_id = (int) $this->get_array_isset($je_data,'wc_inv_id',0);
			$qbo_sr_id = (int) $this->get_array_isset($je_data,'qbo_sr_id',0);			
			$qbo_customer_id = (int) $this->get_array_isset($je_data,'qbo_customer_id',0);
			
			$txn_fee_amount = $this->get_array_isset($je_data,'txn_fee_amount',0);
			
			$date = $this->get_array_isset($je_data,'date','');
			$date = $this->view_date($date);
			
			$qbo_account_id = (int) $this->get_array_isset($je_data,'qbo_account_id',0);
			$txn_expense_acc_id = (int) $this->get_array_isset($je_data,'txn_expense_acc_id',0);

			$_order_currency = $this->get_array_isset($je_data,'order_currency','',true);
			
			if(!$txn_expense_acc_id){
				$this->save_log('Export Transaction Fee Error #'.$qbo_sr_id,'QuickBooks Expense Account ID Not Found','Journal Entry',0);
				return false;
			}
			
			if($qbo_sr_id && $txn_fee_amount>0){
				$Context = $this->Context;
				$realm = $this->realm;
				
				$JournalEntryService = new QuickBooks_IPP_Service_JournalEntry();
				if($je_refund){
					$chk_Je = $JournalEntryService->query($Context, $realm, "SELECT * FROM JournalEntry WHERE DocNumber = 'R-{$qbo_sr_id}' ");
				}else{
					$chk_Je = $JournalEntryService->query($Context, $realm, "SELECT * FROM JournalEntry WHERE DocNumber = '{$qbo_sr_id}' ");
				}

				if($chk_Je && !empty($chk_Je)){
					return false;
				}
				
				$JournalEntry = new QuickBooks_IPP_Object_JournalEntry();
				if($je_refund){
					$JournalEntry->setDocNumber('R-'.$qbo_sr_id);
				}else{
					$JournalEntry->setDocNumber($qbo_sr_id);
				}
				
				$JournalEntry->setTxnDate($date);
				
				/**/
				$mw_wc_qbo_sync_inv_sr_txn_qb_department = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_department');
				$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');

				// Debit line
				$Line1 = new QuickBooks_IPP_Object_Line();
				$Line1->setDescription('Transactions Fee Debit');
				$Line1->setAmount($txn_fee_amount);
				$Line1->setDetailType('JournalEntryLineDetail');

				$Detail1 = new QuickBooks_IPP_Object_JournalEntryLineDetail();
				$Detail1->setPostingType('Debit');

				if($je_refund){
					$Detail1->setAccountRef("{-$qbo_account_id}");
				}else{
					$Detail1->setAccountRef("{-$txn_expense_acc_id}");
				}

				//Customer
				$Entity = new QuickBooks_IPP_Object_Entity();
				$Entity->setType('Customer');
				$Entity->setEntityRef("{-$qbo_customer_id}");
				$Detail1->setLinkedTxn($Entity);
				
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_department)){
					$Detail1->setDepartmentRef($mw_wc_qbo_sync_inv_sr_txn_qb_department);
				}
				
				//
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
					$Detail1->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
				}
				
				$Line1->addJournalEntryLineDetail($Detail1);

				//LinkedTxn
				$LinkedTxn = new QuickBooks_IPP_Object_LinkedTxn();
				$LinkedTxn->setTxnId("{-$qbo_sr_id}");
				$LinkedTxn->setTxnType('SalesReceipt');
				$Line1->setLinkedTxn($LinkedTxn);

				$JournalEntry->addLine($Line1);
				
				// Credit line
				$Line2 = new QuickBooks_IPP_Object_Line();
				$Line2->setDescription('Transactions Fee Credit');
				$Line2->setAmount($txn_fee_amount);
				$Line2->setDetailType('JournalEntryLineDetail');

				$Detail2 = new QuickBooks_IPP_Object_JournalEntryLineDetail();
				$Detail2->setPostingType('Credit');

				if($je_refund){
					$Detail2->setAccountRef("{-$txn_expense_acc_id}");
				}else{
					$Detail2->setAccountRef("{-$qbo_account_id}");
				}
				
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_department)){
					$Detail2->setDepartmentRef($mw_wc_qbo_sync_inv_sr_txn_qb_department);
				}
				
				//
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
					$Detail2->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
				}
				
				$Line2->addJournalEntryLineDetail($Detail2);
				$JournalEntry->addLine($Line2);

				//JE Currency
				$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
				if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){

					$currency_rate_date = $date;
					$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);

					$JournalEntry->setCurrencyRef($_order_currency);
					$JournalEntry->setExchangeRate($currency_rate);
				}

				$log_title = "";
				$log_details = "";
				$log_status = 0;
				
				//$this->_p($je_data);
				//$this->_p($JournalEntry);
				//return false;
				if ($resp = $JournalEntryService->add($Context, $realm, $JournalEntry)){
					$qbo_je_id = $this->qbo_clear_braces($resp);
					$log_title.="Export Transaction Fee #$qbo_sr_id\n";
					$log_details.="QuickBooks Journal Entry ID is #$qbo_je_id\n";
					$log_details.="WooCommerce Order #{$wc_inv_id}";
					$log_status = 1;

					$this->save_log($log_title,$log_details,'Journal Entry',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Journal Entry Add',$je_data,$JournalEntry,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);

					return $qbo_je_id;
				}else{
					$res_err = $JournalEntryService->lastError($Context);
					$log_title.="Export Transaction Fee Error #$qbo_sr_id\n";
					$log_details.="WooCommerce Order #{$wc_inv_id}\n";
					$log_details.="Error:$res_err";
					$this->save_log($log_title,$log_details,'Journal Entry',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Journal Entry Add',$je_data,$JournalEntry,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
					return false;
				}
			}
		}
	}
	
	public function AddJournalEntry($je_data,$je_refund=false){
		if($this->is_connected()){
			$manual = $this->get_array_isset($je_data,'manual',false);

			$wc_payment_id = (int) $this->get_array_isset($je_data,'wc_payment_id',0);
			$qbo_payment_id = (int) $this->get_array_isset($je_data,'qbo_payment_id',0);

			$wc_inv_id = (int) $this->get_array_isset($je_data,'wc_inv_id',0);
			$qbo_invoice_id = (int) $this->get_array_isset($je_data,'qbo_invoice_id',0);

			$qbo_customer_id = (int) $this->get_array_isset($je_data,'qbo_customer_id',0);

			$txn_fee_amount = $this->get_array_isset($je_data,'txn_fee_amount',0);

			$date = $this->get_array_isset($je_data,'date','');
			$date = $this->view_date($date);

			$qbo_account_id = (int) $this->get_array_isset($je_data,'qbo_account_id',0);
			$txn_expense_acc_id = (int) $this->get_array_isset($je_data,'txn_expense_acc_id',0);

			$_order_currency = $this->get_array_isset($je_data,'order_currency','',true);

			if(!$txn_expense_acc_id){
				$this->save_log('Export Transaction Fee Error #'.$qbo_payment_id,'QuickBooks Expense Account ID Not Found','Journal Entry',0);
				return false;
			}

			if($qbo_payment_id && $txn_fee_amount>0){
				$Context = $this->Context;
				$realm = $this->realm;

				$JournalEntryService = new QuickBooks_IPP_Service_JournalEntry();
				if($je_refund){
					$chk_Je = $JournalEntryService->query($Context, $realm, "SELECT * FROM JournalEntry WHERE DocNumber = 'R-{$qbo_payment_id}' ");
				}else{
					$chk_Je = $JournalEntryService->query($Context, $realm, "SELECT * FROM JournalEntry WHERE DocNumber = '{$qbo_payment_id}' ");
				}

				if($chk_Je && !empty($chk_Je)){
					return false;
				}

				$JournalEntry = new QuickBooks_IPP_Object_JournalEntry();
				if($je_refund){
					$JournalEntry->setDocNumber('R-'.$qbo_payment_id);
				}else{
					$JournalEntry->setDocNumber($qbo_payment_id);
				}

				$JournalEntry->setTxnDate($date);
				
				/**/
				$mw_wc_qbo_sync_inv_sr_txn_qb_department = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_department');
				$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
				
				// Debit line
				$Line1 = new QuickBooks_IPP_Object_Line();
				$Line1->setDescription('Transactions Fee Debit');
				$Line1->setAmount($txn_fee_amount);
				$Line1->setDetailType('JournalEntryLineDetail');

				$Detail1 = new QuickBooks_IPP_Object_JournalEntryLineDetail();
				$Detail1->setPostingType('Debit');

				if($je_refund){
					$Detail1->setAccountRef("{-$qbo_account_id}");
				}else{
					$Detail1->setAccountRef("{-$txn_expense_acc_id}");
				}

				//Customer
				$Entity = new QuickBooks_IPP_Object_Entity();
				$Entity->setType('Customer');
				$Entity->setEntityRef("{-$qbo_customer_id}");
				$Detail1->setLinkedTxn($Entity);
				
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_department)){
					$Detail1->setDepartmentRef($mw_wc_qbo_sync_inv_sr_txn_qb_department);
				}
				
				//
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
					$Detail1->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
				}

				$Line1->addJournalEntryLineDetail($Detail1);
				
				//LinkedTxn
				$LinkedTxn = new QuickBooks_IPP_Object_LinkedTxn();
				$LinkedTxn->setTxnId("{-$qbo_payment_id}");
				$LinkedTxn->setTxnType('ReceivePayment');
				$Line1->setLinkedTxn($LinkedTxn);

				$JournalEntry->addLine($Line1);

				// Credit line
				$Line2 = new QuickBooks_IPP_Object_Line();
				$Line2->setDescription('Transactions Fee Credit');
				$Line2->setAmount($txn_fee_amount);
				$Line2->setDetailType('JournalEntryLineDetail');

				$Detail2 = new QuickBooks_IPP_Object_JournalEntryLineDetail();
				$Detail2->setPostingType('Credit');

				if($je_refund){
					$Detail2->setAccountRef("{-$txn_expense_acc_id}");
				}else{
					$Detail2->setAccountRef("{-$qbo_account_id}");
				}
				
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_department)){
					$Detail2->setDepartmentRef($mw_wc_qbo_sync_inv_sr_txn_qb_department);
				}
				
				//
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
					$Detail2->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
				}
				
				$Line2->addJournalEntryLineDetail($Detail2);
				$JournalEntry->addLine($Line2);

				//JE Currency
				$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
				if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){

					$currency_rate_date = $date;
					$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);

					$JournalEntry->setCurrencyRef($_order_currency);
					$JournalEntry->setExchangeRate($currency_rate);
				}

				$log_title = "";
				$log_details = "";
				$log_status = 0;

				//$this->_p($je_data);
				//$this->_p($JournalEntry);
				//return false;
				if ($resp = $JournalEntryService->add($Context, $realm, $JournalEntry)){
					$qbo_je_id = $this->qbo_clear_braces($resp);
					$log_title.="Export Transaction Fee #$qbo_payment_id\n";
					$log_details.="QuickBooks Journal Entry ID is #$qbo_je_id\n";
					$log_details.="WooCommerce Order #{$wc_inv_id}";
					$log_status = 1;

					$this->save_log($log_title,$log_details,'Journal Entry',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Journal Entry Add',$je_data,$JournalEntry,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);

					return $qbo_je_id;
				}else{
					$res_err = $JournalEntryService->lastError($Context);
					$log_title.="Export Transaction Fee Error #$qbo_payment_id\n";
					$log_details.="WooCommerce Order #{$wc_inv_id}\n";
					$log_details.="Error:$res_err";
					$this->save_log($log_title,$log_details,'Journal Entry',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Journal Entry Add',$je_data,$JournalEntry,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
					return false;
				}
			}
		}
	}

	public function get_je_data_from_pmnt_data($payment_data,$pm_map_data,$je_ed=array()){
		$return = array();
		if(is_array($payment_data) && !empty($payment_data) && is_array($pm_map_data) && !empty($pm_map_data) && is_array($je_ed) && !empty($je_ed)){
			$is_txn_fee_sync = false;

			if($payment_data['payment_method']=='stripe' && (float) $payment_data['stripe_txn_fee']>0){
				$is_txn_fee_sync = true;
			}
			
			if($payment_data['payment_method']=='paypal' && (float) $payment_data['paypal_txn_fee']>0){
				$is_txn_fee_sync = true;
			}
			
			if($is_txn_fee_sync){
				//Currently only home currency fee sync enabled
				$wc_currency = get_woocommerce_currency();
				if($wc_currency!=$payment_data['order_currency']){
					$is_txn_fee_sync = false;
				}
			}

			if($is_txn_fee_sync){
				$return['wc_inv_id'] = $payment_data['order_id'];
				/*
				$return['wc_inv_no'] = $je_ed['wc_inv_no'];
				$return['qbo_invoice_id'] = $je_ed['qbo_invoice_id'];
				$return['qbo_customer_id'] = $je_ed['qbo_customer_id'];
				$return['qbo_payment_id'] = $je_ed['qbo_payment_id'];
				*/
				foreach($je_ed as $ed_k => $ed_v){
					if(!isset($return[$ed_k])){
						$return[$ed_k] = $ed_v;
					}
				}
				
				$return['wc_payment_id'] = $payment_data['payment_id'];

				$return['payment_method'] = $payment_data['payment_method'];
				
				$payment_method = $payment_data['payment_method'];
				if(isset($payment_data[$payment_method.'_txn_fee'])){
					$return['txn_fee_amount'] = (float) $payment_data[$payment_method.'_txn_fee'];
				}
				
				//$return['txn_fee_amount'] = (float) $payment_data['stripe_txn_fee'];

				$return['qbo_account_id'] = $pm_map_data['qbo_account_id'];
				$return['txn_expense_acc_id'] = $pm_map_data['txn_expense_acc_id'];

				$return['date'] = $payment_data['paid_date'];
				$return['order_currency'] = $payment_data['order_currency'];
			}
		}
		return $return;
	}
	
	/**/
	public function AddPayment($payment_data,$customer_data=array(),$p_order_data=array()){
		//$this->include_sync_functions('AddPayment');
		$fn_name = 'AddPayment';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function save_payment_id_map($payment_id,$qbo_pmnt_id,$is_wc_order=0){
		$payment_id = intval($payment_id);
		$qbo_pmnt_id = intval($qbo_pmnt_id);

		$is_wc_order = intval($is_wc_order);

		if($qbo_pmnt_id && $qbo_pmnt_id){
			global $wpdb;
			$save_data = array();
			$save_data['qbo_payment_id'] = $qbo_pmnt_id;
			$table = $wpdb->prefix.'mw_wc_qbo_sync_payment_id_map';

			//$this->get_field_by_val($table,'id','wc_payment_id',$payment_id)
			$pim_row = $this->get_row($wpdb->prepare("SELECT `id` FROM {$table} WHERE `wc_payment_id` = %d AND `is_wc_order` = %d LIMIT 0,1 ",$payment_id,$is_wc_order));

			if(is_array($pim_row) && !empty($pim_row)){
				$pim_id = $pim_row['id'];
				$wpdb->update($table,$save_data,array('id'=>$pim_id),array('%d'),array('%d'));
			}else{
				$save_data['is_wc_order'] = $is_wc_order;
				$save_data['wc_payment_id'] = $payment_id;
				$wpdb->insert($table, $save_data);
			}
		}
	}

	public function check_payment_get_obj($payment_data,$qbo_invoice_id=0,$qbo_customer_id=0){
		if($this->is_connected()){
			$is_payment_exists = false;
			$payment_id = (int) $this->get_array_isset($payment_data,'payment_id',0);
			global $wpdb;
			$Context = $this->Context;
			$realm = $this->realm;

			if($payment_id){
				$payment_id_map_row = $this->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mw_wc_qbo_sync_payment_id_map WHERE `wc_payment_id` = %d AND `qbo_payment_id` > 0 AND `is_wc_order` = 0 ",$payment_id));
				if(is_array($payment_id_map_row) && !empty($payment_id_map_row)){
					$qbo_payment_id = (int) $payment_id_map_row['qbo_payment_id'];
					$PaymentService = new QuickBooks_IPP_Service_Payment();
					$Payment_Row = $PaymentService->query($Context, $realm, "SELECT * FROM Payment WHERE Id = '$qbo_payment_id' ");
					//$this->_p($Payment_Row,true);
					if($Payment_Row && !empty($Payment_Row)){
						$is_payment_exists = true;
					}
				}
			}
			return $is_payment_exists;
		}
	}

	//04-05-2017
	public function if_sync_os_payment($invoice_data){
		/**/
		if(is_array($invoice_data) && isset($invoice_data['mw_qbo_yithwgcp']) && isset($invoice_data['mw_qbo_yithwgcp'])){
			return true;
		}
		
		$_order_currency = $this->get_array_isset($invoice_data,'_order_currency','',true);
		$_payment_method = $this->get_array_isset($invoice_data,'_payment_method','',true);
		$order_status = $this->get_array_isset($invoice_data,'order_status','',true);

		$payment_method_map_data  = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);
		$ps_order_status = $this->get_array_isset($payment_method_map_data,'ps_order_status','',true);
		if($order_status!='' && $order_status==$ps_order_status){
			return true;
		}
		return false;
	}
	
	public function check_os_payment_get_obj($payment_data,$qbo_invoice_id=0,$qbo_customer_id=0){
		if($this->is_connected()){
			$is_payment_exists = false;
			$payment_id = (int) $this->get_array_isset($payment_data,'wc_inv_id',0); // order_id
			global $wpdb;
			$Context = $this->Context;
			$realm = $this->realm;

			if($payment_id){
				$payment_id_map_row = $this->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mw_wc_qbo_sync_payment_id_map WHERE `wc_payment_id` = %d AND `qbo_payment_id` > 0 AND `is_wc_order` = 1 ",$payment_id));
				if(is_array($payment_id_map_row) && !empty($payment_id_map_row)){
					$qbo_payment_id = (int) $payment_id_map_row['qbo_payment_id'];
					$PaymentService = new QuickBooks_IPP_Service_Payment();
					$Payment_Row = $PaymentService->query($Context, $realm, "SELECT * FROM Payment WHERE Id = '$qbo_payment_id' ");
					//$this->_p($Payment_Row,true);
					if($Payment_Row && !empty($Payment_Row)){
						$is_payment_exists = true;
					}
				}
			}
			return $is_payment_exists;
		}
	}
	
	public function is_wc_deposit_pmnt_order($invoice_data,$is_dp_main_order=false){
		if($this->is_plugin_active('woocommerce-deposits','woocommmerce-deposits') && $this->option_checked('mw_wc_qbo_sync_enable_wc_deposit')){
			if(is_array($invoice_data) && !empty($invoice_data)){
				$qbo_inv_items = (isset($invoice_data['qbo_inv_items']))?$invoice_data['qbo_inv_items']:array();
				if(is_array($qbo_inv_items) && !empty($qbo_inv_items)){
					foreach($qbo_inv_items as $qii){
						if(isset($qii['original_order_id'])){
							return $qii['original_order_id'];							
						}
						if($is_dp_main_order && isset($qii['deposit_full_amount_ex_tax'])){
							return true;
						}
					}
				}
			}
		}
		return false;
	}
	
	/**/
	public function PushOsPayment($payment_data,$customer_data=array()){
		//$this->include_sync_functions('PushOsPayment');
		$fn_name = 'PushOsPayment';
		if(!empty($fn_name)){
			$fn_file_path = plugin_dir_path( __FILE__ ) . 'sync-functions'. DIRECTORY_SEPARATOR .$fn_name.'.php';
			if(file_exists($fn_file_path)){
				//return require_once($fn_file_path);
				return require($fn_file_path);
			}
		}
	}
	
	public function if_sync_payment($payment_data,$block_realtime_gateways=true){
		return true;
		$_payment_method = $this->get_array_isset($payment_data,'_payment_method','',true);
		$manual = $this->get_array_isset($payment_data,'manual',false);
		if($block_realtime_gateways && !$manual && ($_payment_method=='bacs' || $_payment_method=='cheque' || $_payment_method=='cod')){
			//return false;
		}
		return true;
	}
	
	public function get_domain(){
		$u_sn = false;
		if($u_sn && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])){
			return $_SERVER['SERVER_NAME'];
		}else{
			$siteurl = $this->get_option('siteurl'); //get_site_url
			if(!empty($siteurl)){
				$psurl = parse_url($siteurl);
				if(is_array($psurl) && isset($psurl['host'])){
					return $psurl['host'];
				}
			}			
		}
		return '';
	}
	
	public function get_plugin_ip(){
		$s_laddr = (isset($_SERVER['LOCAL_ADDR']))?$_SERVER['LOCAL_ADDR']:'';
		$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $s_laddr;
		
		//
		$u_sn = false;
		$sname = ($u_sn && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME']))?$_SERVER['SERVER_NAME']:$this->get_domain();
		if(empty($usersip) && !empty($sname)){
			$usersip = gethostbyname($sname);
		}
		return $usersip;
	}
	
	public function get_plugin_connection_dir(){
		$dirpath = dirname(__FILE__);
		return $dirpath;
	}

	//07-03-2017
	public function loggly_api_add_log($log_data){
		if(!empty($log_data)){
			if(is_array($log_data) && !empty($log_data)){
				$log_data = json_encode($log_data);
			}
			/*
			$requestHeader = array(
				"content-type:text/plain"
			);
			*/
			
			$requestHeader = array(
				'content-type' => 'text/plain',
			);
			
			$api_key = 'cbb22de2-5cca-4f43-a028-da5f00a2cebd';
			$api_url = "http://logs-01.loggly.com/inputs/".$api_key."/tag/http/";			
						
			$response = wp_remote_post($api_url, [
				'timeout' => 30,
				'headers' => $requestHeader,
				'body' => $log_data,
			]);
			
			//$this->_p($response);
		}
	}
	
	/**/
	public function get_remote_access_token_from_license_key(){
		$a_token = '';
		$l_key = $this->get_option('mw_wc_qbo_sync_license','');
		if(!empty($l_key)){
			$rat_url = $this->quickbooks_connection_dashboard_url.'/wc-qbo-return-access-token.php';
			
			$server_name = $this->get_domain();
			//$wc_qbo_plugin_dirpath = $this->get_plugin_connection_dir();
			//$wc_qbo_plugin_usersip = $this->get_plugin_ip();
			
			$requestHeader = array(
				'Accept' => 'application/json',
				'Licensekey' => $l_key,				
				'Servername' => $server_name,
				//'Dirpath' => $wc_qbo_plugin_dirpath,
				//'Userip' => $wc_qbo_plugin_usersip,
			);
			
			$params = array(
				//'timeout' => 10,
				'headers' => $requestHeader,
			);
			
			$response = wp_remote_get($rat_url, $params);
			$is_res_ok = false;
			
			if (is_array( $response )){
				$response_code = wp_remote_retrieve_response_code( $response );
				if($response_code == 200){
					$is_res_ok = true;
				}
			}

			if($is_res_ok && $response['body']!=''){
				$atd = json_decode($response['body']);
				if(is_array($atd) && isset($atd['access_token']) && !empty($atd['access_token'])){
					$a_token = $atd['access_token'];
				}
			}
			
		}
		return $a_token;
	}
	
	/**/
	public function creds($f_remote=false){
		if($this->option_checked('mw_wc_qbo_sync_is_oauth2_qb_connection_fa')){
			return $this->creds_v2_oauth2();
		}
		
		$ses_cred_a = false;
		if($this->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $ses_cred_a){
			$qbo_con_creds = $this->get_session_val('qbo_con_creds',array());
			if(!$this->get_session_val('new_con_number_rts',0) && !$this->get_session_val('new_access_token_rts',0) && is_array($qbo_con_creds) && !empty($qbo_con_creds)){
				//$qbo_con_creds = array_map(array($this,'ext_aes_dec'),$qbo_con_creds);
				$this->creds = $qbo_con_creds;
				return false;
			}
		}
		
		$creds = array();
		
		//New Functionality		
		if(empty($this->get_option('mw_wc_qbo_sync_license','')) || empty($this->get_option('mw_wc_qbo_sync_access_token',''))){
			return $creds;
		}
		
		//
		if(empty($this->get_option('mw_wc_qbo_sync_localkey',''))){
			return $creds;
		}
		
		$localkey = $this->get_option('mw_wc_qbo_sync_conn_cred_local_key','');
		
		$conn_cred_secret_key = '5KQ4JEPBBPM1JPM';
		$localkeydays = 7;
		$allowcheckfaildays = 7;
		
		$checkdate = date("Ymd");		
		$localkeyvalid = false;
		$localkeyresults = array();
		
		if(!$f_remote){
			if($localkey) {
				$localkey = str_replace("\n", '', $localkey);
				$localdata = substr($localkey, 0, strlen($localkey) - 32);
				$md5hash = substr($localkey, strlen($localkey) - 32);
				if($md5hash == md5($localdata . $conn_cred_secret_key)) {
					$localdata = strrev($localdata);
					$md5hash = substr($localdata, 0, 32);
					$localdata = substr($localdata, 32);
					$localdata = base64_decode($localdata);
					$localkeyresults = unserialize($localdata);
					$originalcheckdate = $localkeyresults['checkdate'];
					
					if ($md5hash == md5($originalcheckdate . $conn_cred_secret_key)) {
						$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
						if ($originalcheckdate > $localexpiry) {
							$localkeyvalid = true;
							$creds = $localkeyresults;							
						}
					}
				}
			}
		}
		
		if(!$localkeyvalid){
			$server_name = $this->get_domain();
			$wc_qbo_plugin_dirpath = $this->get_plugin_connection_dir();
			$wc_qbo_plugin_usersip = $this->get_plugin_ip();
			/*
			$requestHeader = array(
				'Accept: application/json',
				'Licensekey: '.$this->get_option('mw_wc_qbo_sync_license',''),
				'Accesstoken: '.$this->get_option('mw_wc_qbo_sync_access_token',''),
				'Servername: '.$server_name,
				'Connectionnumber: '.$this->get_option('mw_wc_qbo_sync_connection_number',1),
				'Sandboxmode: '.$this->get_option('mw_wc_qbo_sync_sandbox_mode','no'),
				'Dirpath: '.$wc_qbo_plugin_dirpath,
				'Userip: '.$wc_qbo_plugin_usersip,
			);
			*/
			
			$requestHeader = array(
				'Accept' => 'application/json',
				'Licensekey' => $this->get_option('mw_wc_qbo_sync_license',''),
				'Accesstoken' => $this->get_option('mw_wc_qbo_sync_access_token',''),
				'Servername' => $server_name,
				'Connectionnumber' => $this->get_option('mw_wc_qbo_sync_connection_number',1),
				'Sandboxmode' => $this->get_option('mw_wc_qbo_sync_sandbox_mode','no'),
				'Dirpath' => $wc_qbo_plugin_dirpath,
				'Userip' => $wc_qbo_plugin_usersip,
			);
			
			//$qc_creds_api_url = $this->quickbooks_connection_dashboard_url.'/wc-qbo-get-connection-creds.php';
			$qc_creds_api_url = $this->quickbooks_connection_dashboard_url.'/wc-qbo-get-connection-creds-v2.php';
			
			$params = array(
				//'timeout' => 10,
				'headers' => $requestHeader,
			);
			$response = wp_remote_get($qc_creds_api_url, $params);
			
			//$this->_p($response);
			$is_res_ok = false;
			
			/*
			if($response['status']['statusCode']==200){
				$is_res_ok = true;
			}
			*/
			
			if (is_array( $response )){
				$response_code = wp_remote_retrieve_response_code( $response );
				if($response_code == 200){
					$is_res_ok = true;
				}
			}
			
			if($is_res_ok && $response['body']!=''){
				$creds = json_decode($response['body']);
				$creds = (array) $creds;
				
				if(isset($creds['is_oauth2_connection']) && $creds['is_oauth2_connection']){
					update_option('mw_wc_qbo_sync_is_oauth2_qb_connection_fa','true');
					return $this->creds_v2_oauth2($creds);
				}
				
				if(isset($creds['oauth_access_token']) && isset($creds['oauth_access_token_secret']) && isset($creds['encryption_key'])){
					$force_local_encrypt = false;
					if(!$force_local_encrypt && isset($creds['oauth_access_token_d']) && isset($creds['oauth_access_token_secret_d'])){
						$creds['oauth_access_token'] = $creds['oauth_access_token_d'];
						$creds['oauth_access_token_secret'] = $creds['oauth_access_token_secret_d'];
					}else{
						/**/
						$allow_aes_wc = false;
						if ($allow_aes_wc || function_exists('mcrypt_module_open')) {
							$AES = QuickBooks_Encryption_Factory::create('aes');
							$creds['oauth_access_token'] = $AES->decrypt($creds['encryption_key'], $creds['oauth_access_token']);
							$creds['oauth_access_token_secret'] = $AES->decrypt($creds['encryption_key'], $creds['oauth_access_token_secret']);	
						}else{
							unset($creds['oauth_access_token']);
							unset($creds['oauth_access_token_secret']);
						}
					}				
				}

			}else{
				$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
				if(!empty($localkeyresults) && isset($originalcheckdate) && $originalcheckdate > $localexpiry) {
					$creds = $localkeyresults;
				}
			}
			
			if(is_array($creds) && !empty($creds) && isset($creds['oauth_access_token']) && isset($creds['oauth_access_token_secret'])){
				$creds['checkdate'] = $checkdate;
				
				$data_encoded = serialize($creds);
				$data_encoded = base64_encode($data_encoded);
				$data_encoded = md5($checkdate . $conn_cred_secret_key) . $data_encoded;
				$data_encoded = strrev($data_encoded);
				$data_encoded = $data_encoded . md5($data_encoded . $conn_cred_secret_key);
				$data_encoded = wordwrap($data_encoded, 80, "\n", true);
				$localkey = $data_encoded;
				
				if(!empty($localkey)){
					update_option('mw_wc_qbo_sync_conn_cred_local_key',$localkey);
				}
			}			
		}
		
		//$this->_p($creds);
		$this->creds = $creds;

		if(is_array($creds) && !empty($creds)){
			//$creds = array_map(array($this,'ext_aes_enc'),$creds);
		}
		
		if($this->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $ses_cred_a){
			$this->set_session_val('qbo_con_creds',$creds);
		}
	}
	
	/**/
	public function creds_v2_oauth2($creds_e=array()){
		$creds = array();
		
		if(is_array($creds_e) && !empty($creds_e)){
			$creds = $creds_e;
		}else{
			$server_name = $this->get_domain();
			$wc_qbo_plugin_dirpath = $this->get_plugin_connection_dir();
			$wc_qbo_plugin_usersip = $this->get_plugin_ip();
			
			$requestHeader = array(
				'Accept' => 'application/json',
				'Licensekey' => $this->get_option('mw_wc_qbo_sync_license',''),
				'Accesstoken' => $this->get_option('mw_wc_qbo_sync_access_token',''),
				'Servername' => $server_name,
				'Connectionnumber' => $this->get_option('mw_wc_qbo_sync_connection_number',1),
				'Sandboxmode' => $this->get_option('mw_wc_qbo_sync_sandbox_mode','no'),
				'Dirpath' => $wc_qbo_plugin_dirpath,
				'Userip' => $wc_qbo_plugin_usersip,
				'is_oauth2_connection' => '1',
			);		
			
			$qc_creds_api_url = $this->quickbooks_connection_dashboard_url.'/wc-qbo-get-connection-creds-v2.php';
			
			$params = array(
				//'timeout' => 10,
				'headers' => $requestHeader,
			);
			
			$response = wp_remote_get($qc_creds_api_url, $params);		
			$is_res_ok = false;
			
			if (is_array( $response )){
				$response_code = wp_remote_retrieve_response_code( $response );
				if($response_code == 200){
					$is_res_ok = true;
				}
			}
			
			if($is_res_ok && $response['body']!=''){
				$creds = json_decode($response['body']);
				$creds = (array) $creds;
			}
		}		
		
		if(is_array($creds) && !empty($creds)){
			if(isset($creds['oauth_access_token']) && isset($creds['oauth_refresh_token']) && isset($creds['encryption_key'])){
				$force_local_encrypt = false;
				if(!$force_local_encrypt && isset($creds['oauth_access_token_d']) && isset($creds['oauth_refresh_token_d'])){
					$creds['oauth_access_token'] = $creds['oauth_access_token_d'];
					$creds['oauth_refresh_token'] = $creds['oauth_refresh_token_d'];
				}else{
					/**/
					$allow_aes_wc = false;
					if ($allow_aes_wc || function_exists('mcrypt_module_open')) {
						$AES = QuickBooks_Encryption_Factory::create('aes');
						$creds['oauth_access_token'] = $AES->decrypt($creds['encryption_key'], $creds['oauth_access_token']);
						$creds['oauth_refresh_token'] = $AES->decrypt($creds['encryption_key'], $creds['oauth_refresh_token']);	
					}else{
						unset($creds['oauth_access_token']);
						unset($creds['oauth_refresh_token']);
					}
				}				
			}
			
			/**/
			if(isset($creds['oauth_access_token']) && isset($creds['oauth_refresh_token']) && !empty($creds['oauth_access_token'])){
				$qbc_cn_d = array(
					'oauth_access_expiry' => $creds['oauth_access_expiry'],
					'oauth_refresh_expiry' => $creds['oauth_refresh_expiry'],
					'last_refresh_datetime' => $creds['last_refresh_datetime'],
					'timezone' => $creds['timezone'],
					'c_time' => $creds['c_time']
				);
				
				update_option('mw_wc_qbo_sync_oauth2_qbc_cn_d',$qbc_cn_d,true);
			}		
		}
		
		//$this->_p($creds);
		$this->creds = $creds;
	}
	
	public function check_oauth2_refresh(){
		if($this->option_checked('mw_wc_qbo_sync_is_oauth2_qb_connection_fa')){
			$qbc_cn_d = get_option('mw_wc_qbo_sync_oauth2_qbc_cn_d');
			if(is_array($qbc_cn_d) && !empty($qbc_cn_d)){
				$s_time = $this->now('Y-m-d H:i:s',$qbc_cn_d['timezone']);
				if(strtotime($qbc_cn_d['oauth_access_expiry']) - 60 < strtotime($s_time)){
					return true;
				}
			}
		}		
		return false;
	}
	
	private function refresh_server_oauth2_connection(){
		$server_name = $this->get_domain();
		$wc_qbo_plugin_dirpath = $this->get_plugin_connection_dir();
		$wc_qbo_plugin_usersip = $this->get_plugin_ip();
		
		$requestHeader = array(
			'Accept' => 'application/json',
			'Licensekey' => $this->get_option('mw_wc_qbo_sync_license',''),
			'Accesstoken' => $this->get_option('mw_wc_qbo_sync_access_token',''),
			'Servername' => $server_name,
			'Connectionnumber' => $this->get_option('mw_wc_qbo_sync_connection_number',1),
			'Sandboxmode' => $this->get_option('mw_wc_qbo_sync_sandbox_mode','no'),
			'Dirpath' => $wc_qbo_plugin_dirpath,
			'Userip' => $wc_qbo_plugin_usersip,
			//'is_oauth2_connection' => '1',
		);	
		$qco2_cr_api_url = $this->quickbooks_connection_dashboard_url.'/wc-qbo-oauth2-connection-refresh.php';
		
		$params = array(
			//'timeout' => 10,
			'headers' => $requestHeader,
		);
		
		$response = wp_remote_get($qco2_cr_api_url, $params);
		$is_res_ok = false;
		
		if (is_array( $response )){
			$response_code = wp_remote_retrieve_response_code( $response );
			if($response_code == 200){
				$is_res_ok = true;
			}
		}
		
		if($is_res_ok && $response['body']!=''){
			//
		}
	}
	
	private function ext_opsl_crypt($string, $action = 'e' ){
		$secret_key = 'GWT\WLK;^5[B2R+?S39D+RX2=y6@hy.rF)<[8^Q&"9_5wM+)';
		$secret_iv = 'M4>K]r^gLkb&=R"$';
	 
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$key = hash( 'sha256', $secret_key );
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
	 
		if( $action == 'e' ) {
			$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
		}
		else if( $action == 'd' ){
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}
	 
		return $output;
	}
	
	//Disabled
	private function ext_aes_enc($v){
		return $this->ext_opsl_crypt($v,'e');
		/*
		$AES = QuickBooks_Encryption_Factory::create('aes');
		$e_key = '8?3k%cYtA3=G';
		$salt = '2e3wX(_$~`jT=CW$p%ALPh&4';
		return $AES->encrypt($e_key, $v, $salt);
		*/
	}
	
	//Disabled
	private function ext_aes_dec($v){
		return $this->ext_opsl_crypt($v,'d');
		/*
		$AES = QuickBooks_Encryption_Factory::create('aes');
		$e_key = '8?3k%cYtA3=G';
		return $AES->decrypt($e_key, $v);
		*/
	}
	
	public function check()
	{
		//die('Check');
		if ($arr = $this->load())
		{
			return true;
		}

		return false;
	}
	
	/**/
	public function load()
	{
		//die('#Load');
		$arr = $this->creds;		
		if(is_array($arr) && !empty($arr)){
			$is_oauth2_connection = $this->get_array_isset($arr,'is_oauth2_connection',false);
			if(!$is_oauth2_connection){
				if(isset($arr['oauth_access_token']) &&  isset($arr['oauth_access_token_secret']) && strlen($arr['oauth_access_token']) > 0 && strlen($arr['oauth_access_token_secret']) > 0){
					return $arr;
				}
			}else{
				if(isset($arr['oauth_access_token']) &&  isset($arr['oauth_refresh_token']) && strlen($arr['oauth_access_token']) > 0 && strlen($arr['oauth_refresh_token']) > 0 && isset($arr['oauth_consumer_key']) &&  isset($arr['oauth_consumer_secret']) && strlen($arr['oauth_consumer_key']) > 0 && strlen($arr['oauth_consumer_secret']) > 0){
					if(!isset($arr['qb_flavor'])){
						$arr['qb_flavor'] = QuickBooks_IPP_IDS::FLAVOR_ONLINE;
					}
					return $arr;
				}
			}
		}		
		
		return false;
	}
	
	/**/
	public function test()
	{
		if ($creds = $this->load())
		{
			//$this->_p($creds);
			$is_oauth2_connection = $this->get_array_isset($creds,'is_oauth2_connection',false);
			$dsn = null;
			$IPP = new QuickBooks_IPP($dsn, $creds['encryption_key']);
			
			if(!$is_oauth2_connection){
				$authmode = QuickBooks_IPP::AUTHMODE_OAUTHV1;				
			}else{
				$authmode = QuickBooks_IPP::AUTHMODE_OAUTHV2;				
			}
			
			$IPP->authMode(
				$authmode,
				$creds
			);
			
			$sandbox = $this->get_array_isset($creds,'sandbox',false);
			if($sandbox){
				$IPP->sandbox(true);
			}
			
			if ($Context = $IPP->context())
			{
				// Set the IPP flavor
				$IPP->flavor($creds['qb_flavor']);

				// Get the base URL if it's QBO
				if ($creds['qb_flavor'] == QuickBooks_IPP_IDS::FLAVOR_ONLINE)
				{
					$cur_version = $IPP->version();

					$IPP->version(QuickBooks_IPP_IDS::VERSION_3);		// Need v3 for this

					$CustomerService = new QuickBooks_IPP_Service_Customer();
					$customers = $CustomerService->query($Context, $creds['qb_realm'], "SELECT * FROM Customer MAXRESULTS 1");

					$IPP->version($cur_version);		// Revert back to whatever they set
				}
				else
				{
					$companies = $IPP->getAvailableCompanies($Context);
				}

				// Check the last error code now...
				if ($IPP->errorCode() == 401 or 			// most calls return this
					$IPP->errorCode() == 3100 or            // OAuth token error
					$IPP->errorCode() == 3200)				// but for some stupid reason the getAvailableCompanies call returns this
				{
					$this->is_connected =false;
					update_option('mw_wc_qbo_sync_qbo_is_connected',0,true);
					$this->set_session_val('qbo_is_connected_rts',1);
					
					return false;
				}
				
				$this->is_connected =true;
				update_option('mw_wc_qbo_sync_qbo_is_connected',1,true);
				$this->set_session_val('qbo_is_connected_rts',1);
				
				return true;
			}
			
		}
		
		$this->is_connected =false;
		update_option('mw_wc_qbo_sync_qbo_is_connected',0,true);
		$this->set_session_val('qbo_is_connected_rts',1);
		
		return false;
	}
	
	public function check_invalid_chars_in_db_conn_info(){
		return false;
		/*
		//$invalid_chars = array('@',':','/','\\','\'','+','?','%','#');
		$invalid_chars = array('+','/','#','%','\'','?');
		foreach($invalid_chars as $char){
			if( strpos( DB_USER, $char ) !== false || strpos( DB_PASSWORD, $char ) !== false || strpos( DB_HOST, $char ) !== false || strpos( DB_NAME, $char ) !== false) {
				return true;
			}
		}
		return false;
		*/
	}

	protected function get_IPP(){
		return $this->IPP;
	}
	
	/**/
	public function connect(){
		$chk_oth_opt = false;
		if($this->option_checked('mw_wc_qbo_sync_session_cn_ls_chk')){
			if(!$this->get_session_val('new_con_number_rts',0,true) && !$this->get_session_val('new_access_token_rts',0,true)){
				$chk_oth_opt = true;
			}
		}
		
		if (($chk_oth_opt && $this->get_session_val('qbo_is_connected_rts',0)) || ($this->check() && 	$this->test())){			
			if($this->get_session_val('qbo_is_connected_rts',0)){
				$this->is_connected = ((int) $this->get_option('mw_wc_qbo_sync_qbo_is_connected')==1)?true:false;
			}

			if($this->check_invalid_chars_in_db_conn_info()){
				$this->is_connected =false;
				update_option('mw_wc_qbo_sync_qbo_is_connected',0,true);
				return false;
			}
			$creds = $this->creds;
			if(empty($creds)){
				$this->is_connected =false;
				return false;
			}
			
			//$sandbox = true;
			if(isset($creds['sandbox'])){
				$sandbox = ($creds['sandbox'])?true:false;
			}else{
				$mw_wc_qbo_sync_sandbox_mode = $this->get_option('mw_wc_qbo_sync_sandbox_mode','');
				$sandbox = ($mw_wc_qbo_sync_sandbox_mode=='yes')?true:false;
			}
			
			$is_oauth2_connection = $this->get_array_isset($creds,'is_oauth2_connection',false);
			
			//$dsn = 'mysqli://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME;
			$dsn = null;
			$IPP = new QuickBooks_IPP($dsn,$creds['encryption_key']);
			
			//$the_username = (isset($creds['app_username']))?$creds['app_username']:'';
			if(!$is_oauth2_connection){
				$authmode = QuickBooks_IPP::AUTHMODE_OAUTHV1;				
			}else{
				$authmode = QuickBooks_IPP::AUTHMODE_OAUTHV2;				
			}
			
			$IPP->authMode(
				$authmode,
				$creds
			);
			
			if ($sandbox)
			{
				// Turn on sandbox mode/URLs
				$IPP->sandbox(true);
			}

			$Context = $IPP->context();

			$realm = (isset($creds['qb_realm']))?$creds['qb_realm']:'';

			$this->Context = $Context;
			$this->realm = $realm;

			$this->IPP = $IPP;

		}else{
			update_option('mw_wc_qbo_sync_qbo_is_connected',0,true);
			$this->Context = '';
			$this->realm = '';
		}
	}
	
	public function is_connected(){
		return ($this->is_connected)?true:false;
	}
	public function getContext(){
		return $this->Context;
	}
	public function getRealm(){
		return $this->realm;
	}
	
	/***************************************************************Other Variables******************************************************/
	var $yes_no = array(
    	'no'=>'No',
    	'yes'=>'Yes',
    );

	var $no_yes = array(
    	'no'=>'No',
    	'yes'=>'Yes',
    );

	var $show_per_page = array(
		'10'=>'10',
		'20'=>'20',
		'50'=>'50',
		'100'=>'100',
		'200'=>'200',
		'500'=>'500',
	);

	var $log_save_days = array(
		'30'=>'30',
		'60'=>'60',
		'90'=>'90',
		'120'=>'120',
	);

	var $tax_format = array(
		'TaxExcluded'=>'Exclusive of Tax',
		//'TaxExclusive'=>'Exclusive of Tax',
		'TaxInclusive'=>'Inclusive of Tax'
	);

	var $product_pull_status = array(
		'Pending'=>'Pending Review',
		'publish'=>'Published',
		'draft'=>'Draft',
	);

	var $product_pull_desc_fields = array(
		'none'=>'None',
		'name'=>'WooCommerce Product Name',
		'description'=>'Description',
		'short_description'=>'Short Description',		
	);
	
	var $product_push_purchase_desc_fields = array(
		'none'=>'None',
		'name'=>'WooCommerce Product Name',
		'description'=>'Description',
		'short_description'=>'Short Description',		
	);
	
	var $qbo_webhook_items = array(
		//'Customer'=>'Customer',
		//'Invoice'=>'Order',
		'Product'=>'Product',
		'Inventory'=>'Inventory',
		'Pricing'=>'Pricing',
		//'Category'=>'Category',
		'Payment'=>'Payment',
	);
	
	public function qbo_webhook_items(){
		$qwi = $this->qbo_webhook_items;
		if($this->is_plg_lc_p_l()){
			//return array();
			$qwi = array();
			$qwi['Product'] = 'Product';
			#lpa
			$qwi['Inventory'] = 'Inventory';
			$qwi['Pricing'] = 'Pricing';
		}
		
		if($this->is_plg_lc_p_r()){
			unset($qwi['Payment']);
		}
		
		return $qwi;
	}
	
	var $qbo_rt_push_items = array(
		'Customer'=>'Customer',
		'Invoice'=>'Order',
		'Product'=>'Product',
		'Variation'=>'Variation',
		//'Inventory'=>'Inventory',
		//'Category'=>'Category',
		'Payment'=>'Payment',
		//'Refund'=>'Refund',
	);
	
	public function qbo_rt_push_items(){
		$qrpi = $this->qbo_rt_push_items;		
		if($this->is_plg_lc_p_l()){			
			if(isset($qrpi['Inventory'])){
				//unset($qrpi['Inventory']);#lpa
			}
		}
		return $qrpi;
	}
	
	var $client_dropdown_sort_order = array(
		'dname'=>'Display name',
		'first'=>'First name',
		'last'=>'Last name',
		'company'=>'Company name',
	);

	var $default_show_per_page;

	/***************************************************************Other Functions******************************************************/
	public function if_show_sync_status($it_pp,$ss_spe_limit=200,$i_type='',$s_type='push'){
		/**/
		if($i_type == 'order'){
			$osa = $this->get_option('mw_wc_qbo_sync_order_qbo_sync_as');
			if($osa == 'Per Role' || $osa == 'Per Gateway'){
				return false;
			}
		}		
		
		if($it_pp<=$ss_spe_limit){
			return true;
		}
		return false;
	}

	function is_in_array($array, $key, $key_value){
	  $within_array = false;
	  if(!is_array($array)){
		  return false;
	  }
	  foreach( $array as $k=>$v ){
		if( is_array($v) ){
			$within_array = $this->is_in_array($v, $key, $key_value);
			if( $within_array == true ){
				break;
			}
		} else {
			if( $v == $key_value && $k == $key ){
				$within_array = true;
				break;
			}
		}
	  }
	  return $within_array;
	}
	
	public function array_sanitize($data){		
		if (is_array($data) && !empty($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->array_sanitize($value);
			}
		} else {
			# For empty array - It will return array
			if(is_array($data)){
				return $data;
			}
			
			$data = $this->sanitize($data);
		}

		return $data;		  
	}
	
	public function sanitize($txt,$textarea=false){
		if($txt != ''){
			$txt = trim($txt);
		}
		
		if(empty($txt)){
			return $txt;
		}

		$txt = esc_sql( $txt );
		
		if($textarea){
			$txt = sanitize_textarea_field( $txt );
		}else{
			$txt = sanitize_text_field( $txt );
		}

		return $txt;
	}

	public function escape($str){
		if(!empty($str)){
			//$str = strip_tags( $str );

			$str = esc_html( $str );
			$str = esc_js( $str );			
		}

		return $str;
	}

	public function wc_connection_num(){
		return array_combine(range(1,5), range(1,5));
	}
	
	public function due_days_list_arr(){
		return array_combine(range(1,100), range(1,100));
	}

	public function _p($item='',$dump=false){
		echo '<pre style="background:white;">';
		if(is_object($item) || is_array($item)){
			if($dump){
				var_dump($item);
			}else{
				print_r($item);
			}
		}else{
			if($dump){
				var_dump($item);
			}else{
				echo $item;
			}

		}
		echo '</pre>';
	}
	public function ipr_p($item='',$dump=false){
		if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == ''){
			$this->_p($item,$dump);
		}
	}
	public function var_p($key=''){
		if($key!=''){
			if(isset($_POST[$key])){
				if(!is_array($_POST[$key])){
					return trim($_POST[$key]);
				}
				else{
					return $_POST[$key];
				}
			}
		}
	}

	public function var_g($key=''){
		if($key!=''){
			if(isset($_GET[$key])){
				return trim($_GET[$key]);
			}
		}
	}
	public function is_post($key=''){
		$return = false;
		$key = trim($key);
		if($key!='' && isset($_POST[$key])){
			$return = true;
		}
		return $return;
	}
	public function get_file_extention($filename=''){
		if(trim($filename!='')){
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$ext = strtolower($ext);
			return  $ext;
		}

	}
	
	public function get_sys_timezone(){
		/*New*/
		$ntz_m = true;
		if($ntz_m ){
			return wp_timezone_string();
		}	
		
		//Not in Use
		$tz = $this->get_option('timezone_string');
		if(empty($tz)){
			$offset = $this->get_option('gmt_offset');
			if(!empty($offset)){
				$hours   = (int) $offset;
				$minutes = $this->_abs( ( $offset - (int) $offset ) * 60 );
				$offset_utc  = sprintf( '%+03d:%02d', $hours, $minutes );
				//$offset_utc = 'UTC' . $offset_utc;
				
				$tz = $offset_utc;
				
				/*
				$min    = 60 * $offset;
				$sign   = $min < 0 ? "-" : "+";
				$absmin = $this->_abs($min);
				$tz     = sprintf("UTC%s%02d:%02d", $sign, $absmin/60, $absmin%60);
				*/
			}
		}
		return $tz;
	}
	
	public function converToTz($time="",$toTz='',$fromTz='',$format='Y-m-d H:i:s'){
        // timezone by php friendly values
        $date = new DateTime($time, new DateTimeZone($fromTz));
        $date->setTimezone(new DateTimeZone($toTz));
        $time= $date->format($format);
        return $time;
    }
	
	public function get_cdc_timezone(){
		return 'America/Los_Angeles';
	}
	
	public function now($format='Y-m-d H:i:s',$timezone=''){
		if(empty($timezone)){
			$timezone = $this->get_sys_timezone();
		}		
		if($timezone!=''){
			$now = new DateTime('', new DateTimeZone($timezone));
			$datetime = $now->format($format);
			return $datetime;
		}		
		return date($format);
	}


	public function set_session_msg($key='',$msg=''){
		if(!$this->use_php_session()){
			return $this->set_session_msg_wc($key,$msg);
		}
		
		if(!isset($_SESSION[$this->session_prefix.'mwqs_session_msg'])){
			$_SESSION[$this->session_prefix.'mwqs_session_msg'] = array();
		}

		$_SESSION[$this->session_prefix.'mwqs_session_msg'][$key] = $msg;
	}
	
	#New
	public function set_session_msg_wc($key='',$msg=''){
		$smv = array();
		$smk = 'mwqs_session_msg';
		if(!$this->isset_session($smk)){
			$this->set_session_val($smk,$smv);
		}else{
			$smv = $this->get_session_val($smk);
		}
		
		$smv[$key] = $msg;
		$this->set_session_val($smk,$smv);
	}
	
	public function show_session_msg($key='',$div_class="",$unset=true){
		if(!$this->use_php_session()){
			return $this->show_session_msg_wc($key,$div_class,$unset);
		}
		
		if(isset($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key])){
			if(!empty($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key])){
			echo '<div class="mwqs_session_msg_div '.$div_class.'">';
			if(is_array($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key])){
				echo implode('<br />', $_SESSION[$this->session_prefix.'mwqs_session_msg'][$key]);
			}
			else{
				echo $_SESSION[$this->session_prefix.'mwqs_session_msg'][$key];
			}
			echo '</div>';
			}

			if($unset){
				unset($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key]);
			}
		}
	}
	
	#New
	public function show_session_msg_wc($key='',$div_class="",$unset=true){
		$smv = array();
		$smk = 'mwqs_session_msg';
		
		if(!empty($key) && $this->isset_session($smk)){
			$smv = $this->get_session_val($smk);
			if(isset($smv[$key])){
				$msg = $smv[$key];
				if(!empty($msg)){
					echo '<div class="mwqs_session_msg_div '.$div_class.'">';
					if(is_array($msg)){
						echo implode('<br />', $msg);
					}else{
						echo $msg;
					}
					echo '</div>';
				}
				
				if($unset){
					unset($smv[$key]);
					$this->set_session_val($smk,$smv);
				}
			}
		}
	}

	public function get_session_msg($key='',$div_class="",$unset=true){
		if(!$this->use_php_session()){
			return $this->get_session_msg_wc($key,$div_class,$unset);
		}
		
		$return="";
		if(isset($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key])){
			if(!empty($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key])){
				$return.='<div class="mwqs_session_msg_div '.$div_class.'">';
				if(is_array($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key])){
					$return.= implode('<br />', $_SESSION[$this->session_prefix.'mwqs_session_msg'][$key]);
				}
				else{
					$return.= $_SESSION[$this->session_prefix.'mwqs_session_msg'][$key];
				}
				$return.= '</div>';
			}
			if($unset){
			   unset($_SESSION[$this->session_prefix.'mwqs_session_msg'][$key]);
			}
		}
		return $return;
	}
	
	public function get_session_msg_wc($key='',$div_class="",$unset=true){
		$return="";
		$smv = array();
		$smk = 'mwqs_session_msg';
		
		if(!empty($key) && $this->isset_session($smk)){
			$smv = $this->get_session_val($smk);
			if(isset($smv[$key])){
				$msg = $smv[$key];
				if(!empty($msg)){
					$return.='<div class="mwqs_session_msg_div '.$div_class.'">';
					if(is_array($msg)){
						$return.= implode('<br />', $msg);
					}else{						
						$return.= $msg;
					}
					$return.= '</div>';
				}
				
				if($unset){
					unset($smv[$key]);
					$this->set_session_val($smk,$smv);
				}
			}
		}
		
		return $return;
	}
	
	public function window_redirect($url='',$session_msg=false,$key='',$msg=''){
		if($url!=''){
		?>
		<script type="text/javascript">
			window.location='<?php echo $url;?>';
		</script>
		<?php
		}
	}

	public function redirect($url=''){
		if(trim($url)!=''){
			header("location:$url");
			exit(0);
		}
	}

	public function view_date($date,$format="Y-m-d"){
		if($date!='' && $date!=NULL && $date!='0000-00-00 00:00:00'){
			$date = strtotime($date);
			return date($format,$date);
		}
	}

	public function view_date_time($date,$format="d-m-Y h:i A"){
		if($date!='' && $date!=NULL && $date!='0000-00-00 00:00:00'){
			$date = strtotime($date);
			return date($format,$date);
		}
	}


	public  function get_paginate_links($total_records=0,$items_per_page=20,$show_total=true,$page=''){
		if($page==''){
			$page = $this->get_page_var();
		}
		
		if($total_records>0){
			$pagination_data = '<div class="mwqs_paginate_div">';

			$i_text = ($total_records>1)?'items':'item';

			if($show_total){
				//$pagination_data.='<div class="tot_div">Total <span>'.$total_records.'</span> '.$i_text.'</div>';
				//
				$total_pages = ceil($total_records / $items_per_page);				
				$pgn_txt = $this->get_pagination_count_txt($page,$total_pages,$total_records,$items_per_page);

				$pagination_data.= '<div class="mwqspd_si_txt">'.$pgn_txt.'</div>';
			}

			if($total_records>$items_per_page){

			$pagination_data.='<div class="pagination">';

			$pagination_data.=paginate_links( array(
								'base' => add_query_arg( 'paged', '%#%' ),
								'format' => '',
								'prev_text' => __('&laquo;'),
								'next_text' => __('&raquo;'),
								'total' => ceil($total_records / $items_per_page),
								'current' => $page,
								'end_size' =>2,
								'mid_size' =>3

								));

			$pagination_data.='</div>';
			}

			$pagination_data.='</div>';

			return $pagination_data;

		}
	}
	
	public function get_pagination_count_txt($page,$total_pages,$count,$itemPerPage){
		$cur_page = ($page==0)?1:$page;
		if ($page != 0) $page--;

		$txt = '';
		if($cur_page<=$total_pages){
			$e_text = ($count>1)?'entries':'entry';
			$txt = 'Showing '.($page*$itemPerPage+1).' to '.(($total_pages==$cur_page || $itemPerPage>=$count)?$count:($page+1)*$itemPerPage).' of '.$count.' '.$e_text;
		}
		return $txt;
	}


	//18-01-2017
	public function get_log_chart_data(){
		global $wpdb;
		$today = $this->now("Y-m-d").' 00:00:00';
        $month = date("Y-m-d H:i:s", mktime(0, 0, 0, $this->now("m"), $this->now("d") - 30, $this->now("Y")));
        $year = date("Y-m-d H:i:s", mktime(0, 0, 0, $this->now("m") - 12, 1, $this->now("Y")));

		$invoiceData = array();
		$result_inv_today = $this->get_data("SELECT date_format(added_date, '%k') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$today' AND `log_type`='Invoice' AND `success`=1 AND `details` NOT LIKE '%Draft Invoice not allowed%' GROUP BY date_format(added_date, '%k')");
		if(!empty($result_inv_today)){
			foreach($result_inv_today as $data){
				$invoiceData['today'][$data['date']] = $data['count'];
			}
		}
		$result_inv_month = $this->get_data("SELECT date_format(added_date, '%e %M') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$month' AND `log_type`='Invoice' AND `success`=1 AND `details` NOT LIKE '%Draft Invoice not allowed%' GROUP BY date_format(added_date, '%e')");
		if(!empty($result_inv_month)){
			foreach($result_inv_month as $data){
				$invoiceData['month'][$data['date']] = $data['count'];
			}
		}

		$result_inv_year = $this->get_data("SELECT date_format(added_date, '%M %Y') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$year' AND `log_type`='Invoice' AND `success`=1 AND `details` NOT LIKE '%Draft Invoice not allowed%' GROUP BY date_format(added_date, '%M')");
		if(!empty($result_inv_year)){
			foreach($result_inv_year as $data){
				$invoiceData['year'][$data['date']] = $data['count'];
			}
		}

		//
		$paymentData = array();
		$result_pmnt_today = $this->get_data("SELECT date_format(added_date, '%k') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$today' AND `log_type`='Payment' AND `success`=1 GROUP BY date_format(added_date, '%k')");
		if(!empty($result_pmnt_today)){
			foreach($result_pmnt_today as $data){
				$paymentData['today'][$data['date']] = $data['count'];
			}
		}
		$result_pmnt_month = $this->get_data("SELECT date_format(added_date, '%e %M') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$month' AND `log_type`='Payment' AND `success`=1 GROUP BY date_format(added_date, '%e')");
		if(!empty($result_pmnt_month)){
			foreach($result_pmnt_month as $data){
				$paymentData['month'][$data['date']] = $data['count'];
			}
		}

		$result_pmnt_year = $this->get_data("SELECT date_format(added_date, '%M %Y') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$year' AND `log_type`='Payment' AND `success`=1 GROUP BY date_format(added_date, '%M')");
		if(!empty($result_pmnt_year)){
			foreach($result_pmnt_year as $data){
				$paymentData['year'][$data['date']] = $data['count'];
			}
		}

		//
		$clientData = array();
		$result_cl_today = $this->get_data("SELECT date_format(added_date, '%k') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$today' AND `log_type`='Customer' AND `success`=1 GROUP BY date_format(added_date, '%k')");
		if(!empty($result_cl_today)){
			foreach($result_cl_today as $data){
				$clientData['today'][$data['date']] = $data['count'];
			}
		}
		$result_cl_month = $this->get_data("SELECT date_format(added_date, '%e %M') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$month' AND `log_type`='Customer' AND `success`=1 GROUP BY date_format(added_date, '%e')");
		if(!empty($result_cl_month)){
			foreach($result_cl_month as $data){
				$clientData['month'][$data['date']] = $data['count'];
			}
		}

		$result_cl_year = $this->get_data("SELECT date_format(added_date, '%M %Y') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$year' AND `log_type`='Customer' AND `success`=1 GROUP BY date_format(added_date, '%M')");
		if(!empty($result_cl_year)){
			foreach($result_cl_year as $data){
				$clientData['year'][$data['date']] = $data['count'];
			}
		}

		//
		$errorData = array();
		$result_er_today = $this->get_data("SELECT date_format(added_date, '%k') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$today' AND `success`=0 GROUP BY date_format(added_date, '%k')");
		if(!empty($result_er_today)){
			foreach($result_er_today as $data){
				$errorData['today'][$data['date']] = $data['count'];
			}
		}
		$result_er_month = $this->get_data("SELECT date_format(added_date, '%e %M') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$month' AND `success`=0 GROUP BY date_format(added_date, '%e')");
		if(!empty($result_er_month)){
			foreach($result_er_month as $data){
				$errorData['month'][$data['date']] = $data['count'];
			}
		}

		$result_er_year = $this->get_data("SELECT date_format(added_date, '%M %Y') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$year' AND `success`=0 GROUP BY date_format(added_date, '%M')");
		if(!empty($result_er_year)){
			foreach($result_er_year as $data){
				$errorData['year'][$data['date']] = $data['count'];
			}
		}

		//
		$depositData = array();
		$result_dp_today = $this->get_data("SELECT date_format(added_date, '%k') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$today' AND `log_type`='Deposit' AND `success`=1 GROUP BY date_format(added_date, '%k')");
		if(!empty($result_dp_today)){
			foreach($result_dp_today as $data){
				$depositData['today'][$data['date']] = $data['count'];
			}
		}

		$result_dp_month = $this->get_data("SELECT date_format(added_date, '%e %M') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$month' AND `log_type`='Deposit' AND `success`=1 GROUP BY date_format(added_date, '%e')");
		if(!empty($result_dp_month)){
			foreach($result_dp_month as $data){
				$depositData['month'][$data['date']] = $data['count'];
			}
		}

		$result_dp_year = $this->get_data("SELECT date_format(added_date, '%M %Y') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$year' AND `log_type`='Deposit' AND `success`=1 GROUP BY date_format(added_date, '%M')");
		if(!empty($result_dp_year)){
			foreach($result_dp_year as $data){
				$depositData['year'][$data['date']] = $data['count'];
			}
		}

		//
		$productData = array();
		$result_prd_today = $this->get_data("SELECT date_format(added_date, '%k') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$today' AND `log_type`='Product' AND `success`=1 GROUP BY date_format(added_date, '%k')");
		if(!empty($result_prd_today)){
			foreach($result_prd_today as $data){
				$productData['today'][$data['date']] = $data['count'];
			}
		}

		$result_prd_month = $this->get_data("SELECT date_format(added_date, '%e %M') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$month' AND `log_type`='Product' AND `success`=1 GROUP BY date_format(added_date, '%e')");
		if(!empty($result_prd_month)){
			foreach($result_prd_month as $data){
				$productData['month'][$data['date']] = $data['count'];
			}
		}

		$result_prd_year = $this->get_data("SELECT date_format(added_date, '%M %Y') AS date, COUNT(id) AS count FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE added_date>'$year' AND `log_type`='Product' AND `success`=1 GROUP BY date_format(added_date, '%M')");
		if(!empty($result_prd_year)){
			foreach($result_prd_year as $data){
				$productData['year'][$data['date']] = $data['count'];
			}
		}

		//
		return array(
            'invoices' => array(
                'total' => $invoiceData,
            ),
            'clients' => array(
                'total' => $clientData,
            ),
			 'errors' => array(
                'total' => $errorData,
            ),
			'payments' => array(
                'total' => $paymentData,
            ),
			'deposits' => array(
                'total' => $depositData,
            ),
			'products' => array(
                'total' => $productData,
            ),

        );
	}

	public function get_log_chart_output($viewPeriod=''){
		$data = $this->get_log_chart_data();
		if (!in_array($viewPeriod, array('today', 'month', 'year'))) {
            $viewPeriod = 'today';
        }

		$invoiceData = (isset($data['invoices']['total'][$viewPeriod]))?$data['invoices']['total'][$viewPeriod]:array();
		$clientData = (isset($data['clients']['total'][$viewPeriod]))?$data['clients']['total'][$viewPeriod]:array();
		//$errorData = (isset($data['errors']['total'][$viewPeriod]))?$data['errors']['total'][$viewPeriod]:array();

		$paymentData = (isset($data['payments']['total'][$viewPeriod]))?$data['payments']['total'][$viewPeriod]:array();

		$productData = (isset($data['products']['total'][$viewPeriod]))?$data['products']['total'][$viewPeriod]:array();
		$depositData = (isset($data['deposits']['total'][$viewPeriod]))?$data['deposits']['total'][$viewPeriod]:array();

		if ($viewPeriod == 'today') {

            $graphLabels = array();

            $graphDataInv = array();
            $graphDataCus = array();
			$graphDataErr = array();

			$graphDataPmnt = array();

			$graphDataPrdt = array();
			$graphDataDpst = array();


            for ($i = 0; $i <= $this->now("H"); $i++) {
                $graphLabels[] = date("ga", mktime($i, $this->now("i"), $this->now("s"), $this->now("m"), $this->now("d"), $this->now("Y")));
                $graphDataInv[] = isset($invoiceData[$i]) ? $invoiceData[$i] : 0;
                $graphDataCus[] = isset($clientData[$i]) ? $clientData[$i] : 0;
				//$graphDataErr[] = isset($errorData[$i]) ? $errorData[$i] : 0;

				$graphDataPmnt[] = isset($paymentData[$i]) ? $paymentData[$i] : 0;

				$graphDataPrdt[] = isset($productData[$i]) ? $productData[$i] : 0;
				$graphDataDpst[] = isset($depositData[$i]) ? $depositData[$i] : 0;
            }

        } elseif ($viewPeriod == 'month') {

            $graphLabels = array();

		    $graphDataInv = array();
            $graphDataCus = array();
			//$graphDataErr = array();

			$graphDataPmnt = array();
			$graphDataPrdt = array();
			$graphDataDpst = array();

            for ($i = 0; $i < 30; $i++) {
                $time = mktime(0, 0, 0, $this->now("m"), $this->now("d") - $i, $this->now("Y"));
                $graphLabels[] = date("jS", $time);
                $graphDataInv[] = isset($invoiceData[date("j F", $time)]) ? $invoiceData[date("j F", $time)] : 0;
                $graphDataCus[] = isset($clientData[date("j F", $time)]) ? $clientData[date("j F", $time)] : 0;
				//$graphDataErr[] = isset($errorData[date("j F", $time)]) ? $errorData[date("j F", $time)] : 0;

				$graphDataPmnt[] = isset($paymentData[date("j F", $time)]) ? $paymentData[date("j F", $time)] : 0;

				$graphDataPrdt[] = isset($productData[date("j F", $time)]) ? $productData[date("j F", $time)] : 0;
				$graphDataDpst[] = isset($depositData[date("j F", $time)]) ? $depositData[date("j F", $time)] : 0;
            }

            $graphLabels = array_reverse($graphLabels);

            $graphDataInv = array_reverse($graphDataInv);
            $graphDataCus = array_reverse($graphDataCus);
			//$graphDataErr = array_reverse($graphDataErr);

			$graphDataPmnt = array_reverse($graphDataPmnt);

			$graphDataPrdt = array_reverse($graphDataPrdt);
			$graphDataDpst = array_reverse($graphDataDpst);

        } elseif ($viewPeriod == 'year') {

            $graphLabels = array();

			$graphDataInv = array();
            $graphDataCus = array();
			//$graphDataErr = array();

			$graphDataPmnt = array();
			$graphDataPrdt = array();
			$graphDataDpst = array();

            for ($i = 0; $i < 12; $i++) {
                $time = mktime(0, 0, 0, $this->now("m") - $i, 1, $this->now("Y"));
                $graphLabels[] = date("F y", $time);
                $graphDataInv[] = isset($invoiceData[date("F Y", $time)]) ? $invoiceData[date("F Y", $time)] : 0;
                $graphDataCus[] = isset($clientData[date("F Y", $time)]) ? $clientData[date("F Y", $time)] : 0;
				//$graphDataErr[] = isset($errorData[date("F Y", $time)]) ? $errorData[date("F Y", $time)] : 0;

				$graphDataPmnt[] = isset($paymentData[date("F Y", $time)]) ? $paymentData[date("F Y", $time)] : 0;
				$graphDataPrdt[] = isset($productData[date("F Y", $time)]) ? $productData[date("F Y", $time)] : 0;
				$graphDataDpst[] = isset($depositData[date("F Y", $time)]) ? $depositData[date("F Y", $time)] : 0;
            }

            $graphLabels = array_reverse($graphLabels);

            $graphDataInv = array_reverse($graphDataInv);
            $graphDataCus = array_reverse($graphDataCus);
			//$graphDataErr = array_reverse($graphDataErr);

			$graphDataPmnt = array_reverse($graphDataPmnt);
			$graphDataPrdt = array_reverse($graphDataPrdt);
			$graphDataDpst = array_reverse($graphDataDpst);

        }

        $graphLabels = '"' . implode('","', $graphLabels) . '"';

        $graphDataInv = implode(',', $graphDataInv);

        $graphDataCus = implode(',', $graphDataCus);
		//$graphDataErr = implode(',', $graphDataErr);

		$graphDataPmnt = implode(',', $graphDataPmnt);
		$graphDataPrdt = implode(',', $graphDataPrdt);
		$graphDataDpst = implode(',', $graphDataDpst);

        $activeToday = ($viewPeriod == 'today') ? ' active' : '';
        $activeThisMonth = ($viewPeriod == 'month') ? ' active' : '';
        $activeThisYear = ($viewPeriod == 'year') ? ' active' : '';

		//colors
		$client_bg_color_rgb = '220,220,220,0.5';
		$client_border_color_rgb = '220,220,220,1';
		$client_point_bg_color_rgb = '220,220,220,1';
		$client_point_border_color = '#fff';




		$payment_bg_color_rgb = '66, 134, 244, 0.5';
		$payment_border_color_rgb = '66, 134, 244, 1';
		$payment_point_bg_color_rgb = '66, 134, 244, 1';
		$payment_point_border_color = '#fff';

		$deposit_bg_color_rgb = '66, 238, 244, 0.5';
		$deposit_border_color_rgb = '66, 238, 244, 1';
		$deposit_point_bg_color_rgb = '66, 238, 244, 1';
		$deposit_point_border_color = '#fff';

		$product_bg_color_rgb = '232, 163, 2, 0.5';
		$product_border_color_rgb = '232, 163, 2,1';
		$product_point_bg_color_rgb = '232, 163, 2, 1';
		$product_point_border_color = '#fff';

		$help_txt = __('Click on colors or labels for enable/disable','mw_wc_qbo_sync');

		//
		return <<<EOF
    <div style="padding:20px;">
    <div class="btn-group btn-group-sm btn-period-chooser" role="group" aria-label="...">
        <button type="button" class="btn btn-default{$activeToday}" data-period="today">Today</button>
        <button type="button" class="btn btn-default{$activeThisMonth}" data-period="month">This Month</button>
        <button type="button" class="btn btn-default{$activeThisYear}" data-period="year">This Year</button>
    </div>
	<p>{$help_txt}</p>
</div>

<div style="width:100%;height:450px;">
    <div id="ChartParent_MWQS">
        <canvas id="Chart_MWQS" height="400"></canvas>
    </div>
</div>

<script>

jQuery(document).ready(function($) {

    $('.btn-period-chooser button').click(function() {
        $('.btn-period-chooser button').removeClass('active');
        $(this).addClass('active');
		var period = $(this).data('period');
		mw_wc_qbo_sync_refresh_log_chart(period);
    });

    var lineData = {
        labels: [{$graphLabels}],
        datasets: [
            {
                label: "Customer",
                backgroundColor: "rgba({$client_bg_color_rgb})",
                borderColor: "rgba({$client_border_color_rgb})",
                pointBackgroundColor: "rgba({$client_point_bg_color_rgb})",
                pointBorderColor: "{$client_point_border_color}",
                data: [{$graphDataCus}]
            },
            {
                label: "Invoice",
                backgroundColor: "rgba(93,197,96,0.5)",
                borderColor: "rgba(93,197,96,1)",
                pointBackgroundColor: "rgba(93,197,96,1)",
                pointBorderColor: "#fff",
                data: [{$graphDataInv}]
            },
			{
                label: "Payment",
                backgroundColor: "rgba({$payment_bg_color_rgb})",
                borderColor: "rgba({$payment_border_color_rgb})",
                pointBackgroundColor: "rgba({$payment_point_bg_color_rgb})",
                pointBorderColor: "{$payment_point_border_color}",
                data: [{$graphDataPmnt}]
            },
			{
                label: "Deposit",
                backgroundColor: "rgba({$deposit_bg_color_rgb})",
                borderColor: "rgba({$deposit_border_color_rgb})",
                pointBackgroundColor: "rgba({$deposit_point_bg_color_rgb})",
                pointBorderColor: "{$deposit_point_border_color}",
                data: [{$graphDataDpst}]
            },
			{
                label: "Product",
                backgroundColor: "rgba({$product_bg_color_rgb})",
                borderColor: "rgba({$product_border_color_rgb})",
                pointBackgroundColor: "rgba({$product_point_bg_color_rgb})",
                pointBorderColor: "{$product_point_border_color}",
                data: [{$graphDataPrdt}]
            },
        ]
    };

    var canvas = document.getElementById("Chart_MWQS");
    var parent = document.getElementById('ChartParent_MWQS');

    canvas.width = parent.offsetWidth;
    canvas.height = parent.offsetHeight;

    var ctx = $("#Chart_MWQS");
	//var ctx = $("#Chart_MWQS").get(0).getContext("2d");
	//var chartDisplay = new Chart(document.getElementById("Chart_MWQS").getContext("2d")).Line(lineData);
	//var ctx = document.getElementById("Chart_MWQS").getContext("2d");
	var options = {
	 responsive: true,
		maintainAspectRatio: false,
		scales: {
			 yAxes: [{
				 ticks: {
					 beginAtZero: true,
					 userCallback: function(label, index, labels) {
						 // when the floored value is the same as the value we have a whole number
						 if (Math.floor(label) === label) {
							 return label;
						 }

					 },
				 }
			 }],
		},
	}
	var Chart_MWQS = Chart.Line(ctx, {
		data: lineData,
		options: options
	});

	/*
    new Chart(ctx, {
        type: 'line',
        data: lineData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
			scales: {
				 yAxes: [{
					 ticks: {
						 beginAtZero: true,
						 userCallback: function(label, index, labels) {
							 // when the floored value is the same as the value we have a whole number
							 if (Math.floor(label) === label) {
								 return label;
							 }

						 },
					 }
				 }],
			},
        }
    });
	*/
});
</script>
EOF;

	}
	/************************************************************---------------------------------******************************************************/

	public function get_data($query){
		global $wpdb;
		$query = trim($query);
		if($query!=''){
			return $wpdb->get_results($query,ARRAY_A);
		}
	}

	public function get_row($query){
		global $wpdb;
		$query = trim($query);
		if($query!=''){
			return $wpdb->get_row($query,ARRAY_A);
		}
	}

	public function get_row_by_val($tbl,$field,$field_val){
        global $wpdb;
        if($tbl!='' && $field!='' && $field_val!=''){
            $tbl_q = "SELECT * FROM $tbl WHERE $field= '%s'";
            $tbl_data = $this->get_row($wpdb->prepare($tbl_q,$field_val));
            return $tbl_data;
        }
        else{
            return array();
        }
    }

    public function get_field_by_val($tbl,$get_field,$field,$field_val,$stripslash=true){
        global $wpdb;
        if($tbl!='' && $get_field!='' && $field!='' && $field_val!=''){
            $tbl_q = "SELECT $get_field FROM $tbl WHERE $field= '%s'";
            $tbl_data = $this->get_row($wpdb->prepare($tbl_q,$field_val));
			if($stripslash){
				return (isset($tbl_data[$get_field]))?stripslashes($tbl_data[$get_field]):'';
			}else{
				return (isset($tbl_data[$get_field]))?$tbl_data[$get_field]:'';
			}            
        }
        else{
            return '';
        }
    }
	
	/**/
	public function stripslash_get_data($q_data,$ss_fields){
		$qd_n = array();
		if(is_array($q_data) && !empty($q_data) && is_array($ss_fields) && !empty($ss_fields)){
			foreach($q_data as $k => $v){
				/*
				$tsa= array();
				if(is_array($v) && !empty($v)){
					foreach($v as $v_k => $v_v){
						if(in_array($v_k,$ss_fields)){
							$v_v = stripslashes($v_v);
						}
						$tsa[$v_k] = $v_v;
					}
				}
				if(empty($tsa)){
					$tsa = $v;
				}
				*/
				
				if(is_array($v) && !empty($v)){
					$v = array_map('stripslashes',$v);
				}
				
				$qd_n[$k] = $v;
			}
		}
		if(empty($qd_n)){
			$qd_n = $q_data;
		}
		return $qd_n;
	}


    public function get_tbl($tbl='',$fields='*',$whr='',$orderby='',$limit='',$group_by='',$having=''){
		if($tbl!=''){

			if(trim($fields)==''){$fields='*';}

			$tl_q = "SELECT $fields FROM $tbl ";

			if($whr!=''){
				$tl_q.="WHERE $whr ";
			}

			if($group_by!=''){
				$tl_q.="GROUP BY $group_by ";
			}

			if($having!=''){
				$tl_q.="HAVING $having ";
			}

			if($orderby!=''){
				$tl_q.="ORDER BY $orderby ";
			}

			if($limit!=''){
				$tl_q.="LIMIT $limit ";
			}


			return $this->get_data($tl_q);
		}
	}

	public function only_option($selected='',$opt_arr = array(),$s_key='',$s_val='',$return=false,$dsbl_arr=array()){
		$options='';
		if(is_array($opt_arr) && !empty($opt_arr)){
			foreach ($opt_arr as $key => $value) {
				$sel_text = '';

				if($s_key!='' && $s_val!=''){
                    //change for multi
                    if(is_array($selected) && !empty($selected)){
                        if(in_array($value[$s_key],$selected)){$sel_text = 'selected="selected"';}
                    }else{
                        if($value[$s_key] == $selected){$sel_text = 'selected="selected"';}
                    }
					
					
					$odsbl = '';
					/*
					if(is_array($dsbl_arr) && isset($dsbl_arr[$value[$s_key]])){
						$odsbl = ' disabled';
					}
					*/
					
					if($return){
						$options.='<option'.$odsbl.' value="'.esc_attr($value[$s_key]).'" '.$sel_text.'>'.$this->escape(stripslashes($value[$s_val])).'</option>';
					}else{
						echo '<option'.$odsbl.' value="'.esc_attr($value[$s_key]).'" '.$sel_text.'>'.$this->escape(stripslashes($value[$s_val])).'</option>';
					}

				}else{
                    //change for multi
                    if(is_array($selected) && !empty($selected)){
                        if(in_array($key,$selected)){$sel_text = 'selected="selected"';}
                    }else{
                        if($key == $selected){$sel_text = 'selected="selected"';}
                    }
					
					/**/
					$odsbl = '';					
					if(is_array($dsbl_arr) && isset($dsbl_arr[$key])){
						$odsbl = ' disabled';
					}
					
					if($return){
						$options.='<option'.$odsbl.' value="'.esc_attr($key).'" '.$sel_text.'>'.$this->escape(stripslashes($value)).'</option>';
					}else{
						echo '<option'.$odsbl.' value="'.esc_attr($key).'" '.$sel_text.'>'.$this->escape(stripslashes($value)).'</option>';
					}

				}
			}
		}
		if($return){
			return $options;
		}
	}
	
	public function option_html($selected='',$t_name='',$key_field='',$val_field='',$whr='',$orderby='',$limit='',$return=false){
		if($t_name!='' && $key_field!='' && $val_field!=''){
			$op_fields = "$key_field,$val_field";
			$op_data = $this->get_tbl($t_name,$op_fields,$whr,$orderby,$limit);
			if($return){
				return $this->only_option($selected,$op_data,$key_field,$val_field,$return);
			}
			$this->only_option($selected,$op_data,$key_field,$val_field,$return);
		}
	}
	
	public function get_key_val_pair_from_tbl($t_name,$key_field,$val_field,$whr='',$orderby='',$limit=''){
		$kvd = array();
		if($t_name!='' && $key_field!='' && $val_field!=''){
			$op_fields = "$key_field,$val_field";
			$opt_arr = $this->get_tbl($t_name,$op_fields,$whr,$orderby,$limit);
			if(is_array($opt_arr) && !empty($opt_arr)){
				foreach ($opt_arr as $key => $value) {
					$kvd[$value[$key_field]] = $value[$val_field];
				}
			}
		}
		return $kvd;
	}
	
	/************************************************************************--*********************************************************************/
	var $per_page_keyword = 'mwqs_per_page';
	public function set_per_page_from_url($unique=''){
		if(isset($_GET[$this->per_page_keyword]) && (int) $_GET[$this->per_page_keyword]>0){
			$pp = (int) $_GET[$this->per_page_keyword];
			if(!$pp){$pp=$this->default_show_per_page;}
			#$_SESSION[$this->session_prefix.'item_per_page'.$unique] = $pp;
			$this->set_session_val('item_per_page'.$unique,$pp);
		}

	}
	
	public function get_item_per_page($unique='',$default=50){
		$default = (!(int) $default)?(int) $this->default_show_per_page:$default;
		
		/*
		$itemPerPage = (isset($_SESSION[$this->session_prefix.'item_per_page'.$unique]))?
		$_SESSION[$this->session_prefix.'item_per_page'.$unique]:$default;
		*/
		
		$itemPerPage = $this->get_session_val('item_per_page'.$unique,$default);
		return $itemPerPage;
	}
	
	public function get_url_var($name='page'){
		$strURL = $_SERVER['REQUEST_URI'];
		$arrVals = explode("/",$strURL);
		$found = 0;
		if(is_array($arrVals) && !empty($arrVals)){
			foreach ($arrVals as $index => $value){
				if($value == $name){
					$found = $index;
				}
			}
			$place = $found + 1;
			return $arrVals[$place];
		}
		return '';		
	}
	
	public function get_page_var($ft=false,$ft_name='page'){
		if(!$ft){
			//$page = (get_query_var('paged')) ? (int) get_query_var('paged') : 1;
			$page = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;
		}else{
			$page = (int) $this->get_url_var($ft_name);		
		}
		
		if(!$page){$page=1;}
		return $page;
	}
	var $session_prefix = 'mw_wc_qbo_sync_';
	public function set_and_get($keyword){
		if(isset($_GET[$keyword])){
		  #$_SESSION[$this->session_prefix.$keyword] = $_GET[$keyword];
		  $this->set_session_val($keyword,$_GET[$keyword]);
		}
	}
	
	public function set_and_post($keyword){
		if(isset($_POST[$keyword])){
		  #$_SESSION[$this->session_prefix.$keyword] = $_POST[$keyword];
		   $this->set_session_val($keyword,$_POST[$keyword]);
		}
	}
	
	public function set_session_val($keyword,$value){
		if(!$this->use_php_session()){
			return $this->set_session_val_wc($keyword,$value);
		}
		
		//Prevent Some Keys
		if(!$this->option_checked('mw_wc_qbo_sync_session_cn_ls_chk')){
			if($keyword=='new_con_number_rts' || $keyword=='new_access_token_rts' || $keyword=='qbo_is_connected_rts'){
				return false;
			}
		}

		$_SESSION[$this->session_prefix.$keyword] = $value;
	}
	
	#New
	public function set_session_val_wc($keyword,$value){
		//Prevent Some Keys
		if(!$this->option_checked('mw_wc_qbo_sync_session_cn_ls_chk')){
			if($keyword=='new_con_number_rts' || $keyword=='new_access_token_rts' || $keyword=='qbo_is_connected_rts'){
				return false;
			}
		}
		
		if(is_null($this->mwqbosession)){
			return false;
		}
		
		#WC()->session->set($this->session_prefix.$keyword, $value);
		$this->mwqbosession->set($this->session_prefix.$keyword, $value);
	}
	
	public function get_session_val($keyword,$default='',$reset=false){
		if(!$this->use_php_session()){
			return $this->get_session_val_wc($keyword,$default,$reset);
		}
		
		//Prevent Some Keys
		if(!$this->option_checked('mw_wc_qbo_sync_session_cn_ls_chk')){
			if($keyword=='new_con_number_rts' || $keyword=='new_access_token_rts' || $keyword=='qbo_is_connected_rts'){
				return $default;
			}
		}
		
		$val = $default;
		if(isset($_SESSION[$this->session_prefix.$keyword])){
			$val = $_SESSION[$this->session_prefix.$keyword];
			/*
			if(!is_array($_SESSION[$this->session_prefix.$keyword])){
				$val = sanitize_text_field(esc_sql($_SESSION[$this->session_prefix.$keyword]));
			}
			*/
			if($reset){
				unset($_SESSION[$this->session_prefix.$keyword]);
			}
		}

		return $val;
	}
	
	#New
	public function get_session_val_wc($keyword,$default='',$reset=false){
		//Prevent Some Keys
		if(!$this->option_checked('mw_wc_qbo_sync_session_cn_ls_chk')){
			if($keyword=='new_con_number_rts' || $keyword=='new_access_token_rts' || $keyword=='qbo_is_connected_rts'){
				return $default;
			}
		}
		
		if(is_null($this->mwqbosession)){
			return $default;
		}
		
		#return $val = $this->mwqbosession->get($this->session_prefix.$keyword,$default); #Debugging
		$val = $default;
		if(!empty($keyword) && $this->mwqbosession->__isset($this->session_prefix.$keyword)){
			$val = $this->mwqbosession->get($this->session_prefix.$keyword,$default);
			if($reset){
				$this->mwqbosession->__unset($this->session_prefix.$keyword);
			}
		}
		return $val;
	}
	
	public function isset_session($keyword){
		if(!$this->use_php_session()){
			return $this->isset_session_wc($keyword);
		}
		
		if(isset($_SESSION[$this->session_prefix.$keyword])){
			return true;
		}
		return false;
	}
	
	public function isset_session_wc($keyword){
		if(is_null($this->mwqbosession)){return false;}
		if(!empty($keyword) && $this->mwqbosession->__isset($this->session_prefix.$keyword)){
			return true;
		}
		return false;
	}
	
	public function unset_session($keyword){
		if(!$this->use_php_session()){
			return $this->unset_session_wc($keyword);
		}
		
		if(isset($_SESSION[$this->session_prefix.$keyword])){
			unset($_SESSION[$this->session_prefix.$keyword]);
		}
	}
	
	public function unset_session_wc($keyword){
		if(is_null($this->mwqbosession)){return false;}
		if(!empty($keyword) && $this->mwqbosession->__isset($this->session_prefix.$keyword)){
			$this->mwqbosession->__unset($this->session_prefix.$keyword);
		}
	}
	
	//29-03-2017
	public function get_push_all_wc_customer_count(){
		$roles = 'customer';
		global $wpdb;
		if ( ! is_array( $roles ) )
			$roles = array_map('trim',explode( ",", $roles ));
		$sql = '
			SELECT  COUNT(DISTINCT(' . $wpdb->users . '.ID))
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%"' . $role . '"%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';
		//echo $sql;
		return $wpdb->get_var($sql);
	}

	public function get_push_all_wc_customer_ids($count){
		$count = (int) $count;
		if($count>0){
			global $wpdb;
			//$gc_length = 1024;
			$gc_length = $count*10;

			//SET GLOBAL
			$wpdb->query("SET group_concat_max_len = {$gc_length}");

			$roles = 'customer';
			if ( ! is_array( $roles ) )
				$roles = array_map('trim',explode( ",", $roles ));
			$sql = '
				SELECT  GROUP_CONCAT(DISTINCT(' . $wpdb->users . '.ID)) AS  `ids`
				FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
				ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
				WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
				AND     (
			';
			$i = 1;
			foreach ( $roles as $role ) {
				$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%"' . $role . '"%\' ';
				if ( $i < count( $roles ) ) $sql .= ' OR ';
				$i++;
			}
			$sql .= ' ) ';
			//echo $sql;
			return (string) $wpdb->get_var($sql);
		}
	}
	
	public function count_vendors($search_txt='',$list_page=false){
		$roles = '';
		$v_roles = $this->get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur');
		if(empty($v_roles)){
			return 0;
		}
		
		if($v_roles!=''){
			$roles.=','.$v_roles;
		}
		
		global $wpdb;
		if ( ! is_array( $roles ) )
			$roles = array_map('trim',explode( ",", $roles ));
		$sql = '
			SELECT  COUNT(DISTINCT(' . $wpdb->users . '.ID))
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			LEFT JOIN ' . $wpdb->usermeta . ' um1 ON ( um1.user_id = ' . $wpdb->users . '.ID
			AND um1.meta_key =  \'first_name\' )
			LEFT JOIN ' . $wpdb->usermeta . ' um2 ON ( um2.user_id = ' . $wpdb->users . '.ID
			AND um2.meta_key =  \'last_name\' )
			LEFT JOIN ' . $wpdb->usermeta . ' um3 ON ( um3.user_id = ' . $wpdb->users . '.ID
			AND um3.meta_key =  \'billing_company\' )
			LEFT JOIN ' . $wpdb->prefix . 'mw_wc_qbo_sync_vendor_pairs
			ON          ' . $wpdb->users . '.ID             =       ' . $wpdb->prefix . 'mw_wc_qbo_sync_vendor_pairs.wc_customerid
			LEFT JOIN ' . $wpdb->prefix . 'mw_wc_qbo_sync_qbo_vendors qv ON ' . $wpdb->prefix . 'mw_wc_qbo_sync_vendor_pairs.qbo_vendorid = qv.qbo_vendorid
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%"' . $role . '"%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';

		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$sql .=" AND (".$wpdb->users.".display_name LIKE '%%%s%%' OR ".$wpdb->users.".user_email LIKE '%%%s%%' OR um3.meta_value LIKE '%%%s%%' ) ";
		}
		
		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt,$search_txt);
		}

		return $wpdb->get_var($sql);
	}
	
	/**/
	public function count_customers($search_txt='',$list_page=false) {
		global $wpdb;
		
		$roles = 'customer';		
		
		$ext_roles = $this->get_option('mw_wc_qbo_sync_wc_cust_role');
		if($ext_roles!=''){
			$roles.=','.$ext_roles;
		}
		
		/**/
		if($this->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt')){
			//$roles = '';
			$sc_roles_as_cus = $this->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus');
			if(!empty($sc_roles_as_cus)){
				$roles = $sc_roles_as_cus;
			}
		}
		
		if(!is_array( $roles )){
			$roles = array_map('trim',explode( ",", $roles ));
		}
		
		$ext_join = '';
		$ext_whr = '';
		
		$ext_whr .= ' AND     (';
		$i = 1;
		foreach ( $roles as $role ) {
			$ext_whr .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%"' . $role . '"%\' ';
			if ( $i < count( $roles ) ) $ext_whr .= ' OR ';
			$i++;
		}
		$ext_whr .= ' ) ';
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			/*
			$ext_join .= ' LEFT JOIN ' . $wpdb->usermeta . ' um3 ON ( um3.user_id = ' . $wpdb->users . '.ID
			AND um3.meta_key =  \'billing_company\' ) ';
			
			$ext_join .= ' LEFT JOIN ' . $wpdb->usermeta . ' um1 ON ( um1.user_id = ' . $wpdb->users . '.ID
			AND um1.meta_key =  \'first_name\' ) ';
			
			$ext_join .= ' LEFT JOIN ' . $wpdb->usermeta . ' um2 ON ( um2.user_id = ' . $wpdb->users . '.ID
			AND um2.meta_key =  \'last_name\' ) ';
			
			$ext_whr .= $wpdb->prepare(" AND (".$wpdb->users.".display_name LIKE '%%%s%%' OR ".$wpdb->users.".user_email LIKE '%%%s%%' OR um3.meta_value LIKE '%%%s%%' OR ".$wpdb->users.".ID = %s OR um1.meta_value LIKE '%%%s%%' OR um2.meta_value LIKE '%%%s%%' OR CONCAT(um1.meta_value,' ', um2.meta_value) LIKE '%%%s%%' ) ", $search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			*/
			
			/**/			
			$mv_w = $wpdb->prepare("meta_value LIKE '%%%s%%'",$search_txt);
			$cs_gcq = "SELECT GROUP_CONCAT(DISTINCT(user_id)) AS c_ids FROM {$wpdb->usermeta} WHERE {$mv_w} AND meta_key IN('billing_company','first_name','last_name')";
			
			$st_a = explode(' ',$search_txt);
			if(is_array($st_a) && count($st_a) > 1){
				$cs_gcq = "
				SELECT GROUP_CONCAT(DISTINCT(um.user_id)) as c_ids
				FROM {$wpdb->usermeta} um 
				INNER JOIN {$wpdb->usermeta} um_f ON (um.user_id = um_f.user_id AND um_f.meta_key = 'first_name') 
				INNER JOIN {$wpdb->usermeta} um_l ON (um.user_id = um_l.user_id AND um_l.meta_key = 'last_name') 
				WHERE (um.meta_value LIKE '%%%s%%' AND um.meta_key = 'billing_company')
				OR um_f.meta_value LIKE '%%%s%%'
				OR um_l.meta_value LIKE '%%%s%%'
				OR CONCAT(um_f.meta_value,' ', um_l.meta_value) LIKE '%%%s%%' ";
				$cs_gcq = $wpdb->prepare($cs_gcq,$search_txt,$search_txt,$search_txt,$search_txt);
			}
			
			$s_c_ids = $wpdb->get_var($cs_gcq);
			$c_id_w = (!empty($s_c_ids))?" OR ".$wpdb->users.".ID IN ({$s_c_ids})":'';
			
			$ext_whr .= $wpdb->prepare(" AND (".$wpdb->users.".display_name LIKE '%%%s%%' OR ".$wpdb->users.".user_email LIKE '%%%s%%' OR ".$wpdb->users.".ID = %s {$c_id_w} ) ", $search_txt,$search_txt,$search_txt);
			
		}		
		
		$sql = '
			SELECT  COUNT(DISTINCT(' . $wpdb->users . '.ID))
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			'.$ext_join.'
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'				
		';
		
		$sql .= $ext_whr;
		
		//echo $sql;		
		return $wpdb->get_var($sql);
	}
	
	public function get_vendors($search_txt='',$limit='',$list_page=false){
		$roles = '';
		$v_roles = $this->get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur');
		if(empty($v_roles)){
			return array();
		}
		
		if($v_roles!=''){
			$roles.=','.$v_roles;
		}
		
		global $wpdb;
		if ( ! is_array( $roles ) )			
			$roles = array_map('trim',explode( ",", $roles ));			
		$sql = '
			SELECT  DISTINCT(' . $wpdb->users . '.ID), ' . $wpdb->users . '.display_name, ' . $wpdb->users . '.user_email, ' . $wpdb->prefix . 'mw_wc_qbo_sync_vendor_pairs.qbo_vendorid, um1.meta_value AS first_name, um2.meta_value AS last_name, um3.meta_value AS billing_company, qv.dname as `qbo_dname`, qv.email as `qbo_email`
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			LEFT JOIN ' . $wpdb->usermeta . ' um1 ON ( um1.user_id = ' . $wpdb->users . '.ID
			AND um1.meta_key =  \'first_name\' )
			LEFT JOIN ' . $wpdb->usermeta . ' um2 ON ( um2.user_id = ' . $wpdb->users . '.ID
			AND um2.meta_key =  \'last_name\' )
			LEFT JOIN ' . $wpdb->usermeta . ' um3 ON ( um3.user_id = ' . $wpdb->users . '.ID
			AND um3.meta_key =  \'billing_company\' )
			LEFT JOIN ' . $wpdb->prefix . 'mw_wc_qbo_sync_vendor_pairs
			ON          ' . $wpdb->users . '.ID             =       ' . $wpdb->prefix . 'mw_wc_qbo_sync_vendor_pairs.wc_customerid
			LEFT JOIN ' . $wpdb->prefix . 'mw_wc_qbo_sync_qbo_vendors qv ON ' . $wpdb->prefix . 'mw_wc_qbo_sync_vendor_pairs.qbo_vendorid = qv.qbo_vendorid
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%%"' . $role . '"%%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';

		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){			
			$sql .=" AND (".$wpdb->users.".display_name LIKE '%%%s%%' OR ".$wpdb->users.".user_email LIKE '%%%s%%' OR um3.meta_value LIKE '%%%s%%' ) ";
		}
		
		$sql.=' GROUP BY '. $wpdb->users . '.ID';

		$orderby = $wpdb->users.'.display_name ASC';
		$sql .= ' ORDER BY  '.$orderby;

		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt,$search_txt);
		}
		//echo $sql;

		return $this->get_data($sql);
	}
	
	/**/
	public function get_customers($search_txt='',$limit='',$list_page=false) {
		global $wpdb;
		
		$roles = 'customer';
		
		$ext_roles = $this->get_option('mw_wc_qbo_sync_wc_cust_role');
		if($ext_roles!=''){
			$roles.=','.$ext_roles;
		}
		
		/**/
		if($this->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt')){
			//$roles = '';
			$sc_roles_as_cus = $this->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus');
			if(!empty($sc_roles_as_cus)){
				$roles = $sc_roles_as_cus;
			}
		}
		
		if(!is_array( $roles )){
			$roles = array_map('trim',explode( ",", $roles ));
		}
		
		$ext_join = '';
		$ext_whr = '';
		
		$ext_whr .= ' AND     (';
		$i = 1;
		foreach ( $roles as $role ) {
			$ext_whr .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%"' . $role . '"%\' ';
			if ( $i < count( $roles ) ) $ext_whr .= ' OR ';
			$i++;
		}
		$ext_whr .= ' ) ';
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			/*
			$ext_join .= ' LEFT JOIN ' . $wpdb->usermeta . ' um3 ON ( um3.user_id = ' . $wpdb->users . '.ID
			AND um3.meta_key =  \'billing_company\' ) ';
			
			$ext_join .= ' LEFT JOIN ' . $wpdb->usermeta . ' um1 ON ( um1.user_id = ' . $wpdb->users . '.ID
			AND um1.meta_key =  \'first_name\' ) ';
			
			$ext_join .= ' LEFT JOIN ' . $wpdb->usermeta . ' um2 ON ( um2.user_id = ' . $wpdb->users . '.ID
			AND um2.meta_key =  \'last_name\' ) ';
			
			$ext_whr .= $wpdb->prepare(" AND (".$wpdb->users.".display_name LIKE '%%%s%%' OR ".$wpdb->users.".user_email LIKE '%%%s%%' OR um3.meta_value LIKE '%%%s%%' OR ".$wpdb->users.".ID = %s OR um1.meta_value LIKE '%%%s%%' OR um2.meta_value LIKE '%%%s%%' OR CONCAT(um1.meta_value,' ', um2.meta_value) LIKE '%%%s%%' ) ", $search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			*/
			
			/**/			
			$mv_w = $wpdb->prepare("meta_value LIKE '%%%s%%'",$search_txt);
			$cs_gcq = "SELECT GROUP_CONCAT(DISTINCT(user_id)) AS c_ids FROM {$wpdb->usermeta} WHERE {$mv_w} AND meta_key IN('billing_company','first_name','last_name')";
			
			$st_a = explode(' ',$search_txt);
			if(is_array($st_a) && count($st_a) > 1){
				$cs_gcq = "
				SELECT GROUP_CONCAT(DISTINCT(um.user_id)) as c_ids
				FROM {$wpdb->usermeta} um 
				INNER JOIN {$wpdb->usermeta} um_f ON (um.user_id = um_f.user_id AND um_f.meta_key = 'first_name') 
				INNER JOIN {$wpdb->usermeta} um_l ON (um.user_id = um_l.user_id AND um_l.meta_key = 'last_name') 
				WHERE (um.meta_value LIKE '%%%s%%' AND um.meta_key = 'billing_company')
				OR um_f.meta_value LIKE '%%%s%%'
				OR um_l.meta_value LIKE '%%%s%%'
				OR CONCAT(um_f.meta_value,' ', um_l.meta_value) LIKE '%%%s%%' ";
				$cs_gcq = $wpdb->prepare($cs_gcq,$search_txt,$search_txt,$search_txt,$search_txt);
			}
			
			$s_c_ids = $wpdb->get_var($cs_gcq);
			$c_id_w = (!empty($s_c_ids))?" OR ".$wpdb->users.".ID IN ({$s_c_ids})":'';
			
			$ext_whr .= $wpdb->prepare(" AND (".$wpdb->users.".display_name LIKE '%%%s%%' OR ".$wpdb->users.".user_email LIKE '%%%s%%' OR ".$wpdb->users.".ID = %s {$c_id_w} ) ", $search_txt,$search_txt,$search_txt);
		}
		
		$sql = '
			SELECT  DISTINCT(' . $wpdb->users . '.ID) , ' . $wpdb->users . '.display_name, ' . $wpdb->users . '.user_email
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id
			'.$ext_join.'
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'				
		';
		
		$sql .= $ext_whr;
		
		$orderby = $wpdb->users.'.display_name ASC';
		$sql .= ' ORDER BY  '.$orderby;
		
		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}
		
		//echo $sql;
		$r_data = array();
		$q_data =  $this->get_data($sql);
		//$this->_p($q_data);
		
		if(is_array($q_data) && !empty($q_data)){
			foreach($q_data as $rd){
				$cu_tmp_arr = array();
				$cu_tmp_arr['ID'] = $rd['ID'];
				$cu_tmp_arr['display_name'] = $rd['display_name'];
				$cu_tmp_arr['user_email'] = $rd['user_email'];
				
				$c_meta = get_user_meta($rd['ID']);		
				$cu_tmp_arr['first_name'] = (is_array($c_meta) && isset($c_meta['first_name'][0]))?$c_meta['first_name'][0]:'';
				$cu_tmp_arr['last_name'] = (is_array($c_meta) && isset($c_meta['last_name'][0]))?$c_meta['last_name'][0]:'';
				
				$cu_tmp_arr['billing_company'] = (is_array($c_meta) && isset($c_meta['billing_company'][0]))?$c_meta['billing_company'][0]:'';
				
				$ext_cq = "
				SELECT cmap.qbo_customerid, qc.dname as `qbo_dname`, qc.email as `qbo_email`
				FROM " . $wpdb->prefix . "mw_wc_qbo_sync_customer_pairs cmap				
				LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_qbo_customers qc ON cmap.qbo_customerid = qc.qbo_customerid
				WHERE cmap.wc_customerid = ".$rd['ID']."
				AND cmap.qbo_customerid > 0
				LIMIT 0,1
				";
				
				$ext_data =  $this->get_row($ext_cq);
				$cu_tmp_arr['qbo_customerid'] = (is_array($ext_data) && isset($ext_data['qbo_customerid']))?$ext_data['qbo_customerid']:'';
				$cu_tmp_arr['qbo_dname'] = (is_array($ext_data) && isset($ext_data['qbo_dname']))?$ext_data['qbo_dname']:'';
				$cu_tmp_arr['qbo_email'] = (is_array($ext_data) && isset($ext_data['qbo_email']))?$ext_data['qbo_email']:'';
				
				$r_data[] = $cu_tmp_arr;
			}
		}
		
		unset($q_data);
		//$this->_p($r_data);
		return $r_data;		
	}
	
	public function start_with($haystack, $needle){
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	/*28-01-2017*/
	public function show_sync_window_message($id, $message, $progress=0, $tot=0) {
		$d = array('message' => $message , 'progress' => $progress,'total' => $tot,'cur' => $id);
		echo json_encode($d);
		ob_flush();
		flush();
		die();
	}

	public function real_time_hooks_add_queue($item_type,$item_id,$item_action='',$wc_hook=''){
		global $wpdb;
		$f_enable = true;
		if($this->option_checked('mw_wc_qbo_sync_disable_realtime_sync') || $f_enable){
			/**/
			if(($item_action == 'OrderPush' || $item_action == 'CustomerPush') && !is_admin()){
				$s_key = 'current_rt_order_id_';
				if($item_action == 'CustomerPush'){
					$s_key = 'current_rt_customer_id_';
				}
				
				if((int) $this->get_session_val($s_key.$item_id,0)==$item_id){
					return false;
				}
				$this->set_session_val($s_key.$item_id,$item_id);
			}
			
			if($item_action == 'OrderPush' && !$this->is_queue_add($item_action,array('order_id'=>$item_id))){
				return false;
			}
			
			if($item_action == 'PaymentPush' && !$this->is_queue_add($item_action,array('order_id'=>$item_id))){
				return false;
			}
			
			if($item_action == 'RefundPush' && !$this->is_queue_add($item_action,array('order_id'=>$item_id))){
				return false;
			}
			
			$queue_table = $wpdb->prefix.'mw_wc_qbo_sync_real_time_sync_queue';
			$check_queue_query = $wpdb->prepare("SELECT * FROM `$queue_table` WHERE `item_type` = %s AND `item_action` = %s AND `item_id` = %d ",$item_type,$item_action,$item_id);
			if(empty($this->get_row($check_queue_query))){
				$save_queue_data = array();
				$save_queue_data['item_type'] = $item_type;
				$save_queue_data['item_action'] = $item_action;
				$save_queue_data['item_id'] = $item_id;
				$save_queue_data['woocommerce_hook'] = $wc_hook;
				$save_queue_data['run'] = 0;
				$save_queue_data['success'] = 0;
				
				if($item_type=='Invoice' && $item_action == 'OrderPush'){
					$ord_id = (int) $item_id;
					$pmnt_chk = $this->get_row($wpdb->prepare("SELECT * FROM `{$queue_table}` WHERE `item_type` = 'Payment' AND `item_action` = 'PaymentPush' AND `item_id` = %d ",$ord_id));
					if(is_array($pmnt_chk) && !empty($pmnt_chk) && !empty($pmnt_chk['added_date']) && $pmnt_chk['added_date']!='0000-00-00 00:00:00'){
						$save_queue_data['added_date'] = date('Y-m-d H:i:s',strtotime('-1 second',strtotime($pmnt_chk['added_date'])));
					}else{
						$save_queue_data['added_date'] = $this->now();
					}
					
				}elseif($item_type=='Payment' && $item_action == 'PaymentPush'){
					//$save_queue_data['added_date'] = date('Y-m-d H:i:s',strtotime('+10 second',strtotime($this->now())));
					$save_queue_data['added_date'] = $this->now();
				}else{
					$save_queue_data['added_date'] = $this->now();
				}
				$wpdb->insert($queue_table, $save_queue_data);
			}
			return true;
		}
		return false;
	}
	
	public function get_offset($page, $items_per_page,$qb=false){
		//return ( $page * $items_per_page ) - $items_per_page;
		$offset = ( $page * $items_per_page ) - $items_per_page;
		if($qb){
			$offset++;
		}
		return $offset;
	}

	//29-03-2017
	public function get_push_all_wc_product_count(){
		global $wpdb;
		$sql = "
		SELECT COUNT(DISTINCT(p.ID))
		FROM ".$wpdb->posts." p
		WHERE p.post_type =  'product'
		AND p.post_status NOT IN('trash','auto-draft','inherit')
		";

		//echo $sql;
		return (int) $wpdb->get_var($sql);
	}

	public function get_push_all_wc_product_ids($count){
		$count = (int) $count;
		if($count>0){
			global $wpdb;

			$gc_length = $count*10;
			$wpdb->query("SET group_concat_max_len = {$gc_length}");

			$sql = "
			SELECT GROUP_CONCAT(DISTINCT(p.ID)) AS `ids`
			FROM ".$wpdb->posts." p
			WHERE p.post_type =  'product'
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			";

			//echo $sql;
			return (string) $wpdb->get_var($sql);
		}
	}

	//30-03-2017
	public function get_pull_inventory_map_data($item_arr){
		if(is_array($item_arr) && !empty($item_arr)){
			$item_arr = array_map('intval',$item_arr);
			$item_ids = implode(',',$item_arr);
			global $wpdb;
			$sql = "
			SELECT DISTINCT(p.ID) AS wc_product_id, pm5.meta_value AS stock, pmap.quickbook_product_id
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm5 ON ( pm5.post_id = p.ID
			AND pm5.meta_key =  '_stock' )
			INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_product_pairs pmap ON p.ID = pmap.wc_product_id
			INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_qbo_items qp ON pmap.quickbook_product_id = qp.itemid
			WHERE p.post_type =  'product'
			AND qp.itemid IN ({$item_ids})
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			";

			//echo $sql;
			return $this->get_data($sql);
		}
	}

	//19-05-2017
	public function get_pull_inventory_map_data_variation($item_arr){
		if(is_array($item_arr) && !empty($item_arr)){
			$item_arr = array_map('intval',$item_arr);
			$item_ids = implode(',',$item_arr);
			global $wpdb;
			$sql = "
			SELECT DISTINCT(p.ID) AS wc_variation_id, pm5.meta_value AS stock, pmap.quickbook_product_id
			FROM ".$wpdb->posts." p
			LEFT JOIN ".$wpdb->postmeta." pm5 ON ( pm5.post_id = p.ID
			AND pm5.meta_key =  '_stock' )
			INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_variation_pairs pmap ON p.ID = pmap.wc_variation_id
			INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_qbo_items qp ON pmap.quickbook_product_id = qp.itemid
			WHERE p.post_type =  'product_variation'
			AND qp.itemid IN ({$item_ids})
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			";

			//echo $sql;
			return $this->get_data($sql);
		}
	}

	//02-05-2017
	public function get_pull_category_map_data($item_arr){
		if(is_array($item_arr) && !empty($item_arr)){
			//$item_arr = array_map(array($this,'sanitize'),$item_arr);			
			$item_names = implode(',',$item_arr);
			if($this->is_connected() && $item_names!=''){
				global $wpdb;
				$cat_map_sql = "
				SELECT t.term_id AS id, t.name
				FROM   {$wpdb->terms} t
				LEFT JOIN {$wpdb->term_taxonomy} tt
				ON t.term_id = tt.term_id
				WHERE  tt.taxonomy = 'product_cat'
				AND (t.name IN ({$item_names}) OR REPLACE(t.name,':','') IN ({$item_names}))
				";
				//echo $cat_map_sql;
				$categories = $this->get_data($cat_map_sql);
				//$this->_p($categories);
				$cat_map_arr = array();
				if($categories && !empty($categories)){
					foreach($categories as $category){
						$tmp_cat_arr = array();
						$tmp_cat_arr['Id'] = $this->qbo_clear_braces($category['id']);

						$name_replace_chars = array(':');
						$cat_name = $this->get_array_isset(array('cat_name'=>$category['name']),'cat_name','',true,100,false,$name_replace_chars);
						//echo $cat_name.'<br />';
						$tmp_cat_arr['Name'] = md5(base64_encode($cat_name));
						$cat_map_arr[] = $tmp_cat_arr;
					}
				}
				return $cat_map_arr;
			}
		}
	}

	//25-04-2017
	public function get_push_category_map_data($item_arr){
		if(is_array($item_arr) && !empty($item_arr)){
			$item_names = implode(',',$item_arr);
			if($this->is_connected() && $item_names!=''){
				$Context = $this->Context;
				$realm = $this->realm;

				$ItemService = new QuickBooks_IPP_Service_Term();
				$categories = $ItemService->query($Context, $realm, "SELECT Id,Name  FROM Item WHERE Type = 'Category' AND Name IN({$item_names}) ");

				//$this->_p($categories);
				$cat_map_arr = array();
				if($categories && !empty($categories)){
					foreach($categories as $category){
						$tmp_cat_arr = array();
						$tmp_cat_arr['Id'] = $this->qbo_clear_braces($category->getId());
						$tmp_cat_arr['Name'] = md5(base64_encode($category->getName()));
						$cat_map_arr[] = $tmp_cat_arr;
					}
				}
				return $cat_map_arr;
			}
		}
	}

	//09-05-2017
	public function get_push_inventory_map_data($item_arr){
		if(is_array($item_arr) && !empty($item_arr)){
			$item_ids = implode(',',$item_arr);
			if($this->is_connected() && $item_ids!=''){
				$Context = $this->Context;
				$realm = $this->realm;

				$ItemService = new QuickBooks_IPP_Service_Term();
				$items = $ItemService->query($Context, $realm, "SELECT Id,QtyOnHand  FROM Item WHERE Type = 'Inventory' AND Id IN({$item_ids}) ");

				//$this->_p($items);
				$invnt_map_arr = array();
				if($items && !empty($items)){
					foreach($items as $item){
						$tmp_invnt_arr = array();
						$tmp_invnt_arr['quickbook_product_id'] = $this->qbo_clear_braces($item->getId());
						$tmp_invnt_arr['QtyOnHand'] = $item->getQtyOnHand();
						$invnt_map_arr[] = $tmp_invnt_arr;
					}
				}
				return $invnt_map_arr;
			}
		}
	}

	//21-06-2017
	public function get_push_payment_map_data($item_arr){
		$pmnt_map_arr = array();
		if(is_array($item_arr) && !empty($item_arr)){
			$item_ids = implode(',',$item_arr);
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				$PaymentService = new QuickBooks_IPP_Service_Payment();
				$payments = $PaymentService->query($Context,$realm ,"SELECT Id FROM Payment WHERE Id IN({$item_ids}) ");
				if($payments && !empty($payments)){
					foreach($payments as $payment){
						$pmnt_map_arr[] = $this->qbo_clear_braces($payment->getId());
					}
				}
			}
		}
		return $pmnt_map_arr;
	}

	//11-07-2017
	public function get_push_product_map_data($item_arr){
		$prdt_map_arr = array();
		if(is_array($item_arr) && !empty($item_arr)){
			$item_ids = implode(',',$item_arr);
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				$ItemService = new QuickBooks_IPP_Service_Term();
				$items = $ItemService->query($Context,$realm ,"SELECT Id FROM Item WHERE Id IN({$item_ids}) ");
				if($items && !empty($items)){
					foreach($items as $item){
						$prdt_map_arr[] = $this->qbo_clear_braces($item->getId());
					}
				}
			}
		}
		return $prdt_map_arr;
	}

	public function get_push_customer_map_data($item_arr){
		$cust_map_arr = array();
		if(is_array($item_arr) && !empty($item_arr)){
			$item_ids = implode(',',$item_arr);
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				$CustomerService = new QuickBooks_IPP_Service_Term();
				$items = $CustomerService->query($Context,$realm ,"SELECT Id FROM Customer WHERE Id IN({$item_ids}) ");
				if($items && !empty($items)){
					foreach($items as $item){
						$cust_map_arr[] = $this->qbo_clear_braces($item->getId());
					}
				}
			}
		}
		return $cust_map_arr;
	}
	
	//
	public function get_push_vendor_map_data($item_arr){
		$vend_map_arr = array();
		if(is_array($item_arr) && !empty($item_arr)){
			$item_ids = implode(',',$item_arr);
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				$VendorService = new QuickBooks_IPP_Service_Term();
				$items = $VendorService->query($Context,$realm ,"SELECT Id FROM Vendor WHERE Id IN({$item_ids}) ");
				if($items && !empty($items)){
					foreach($items as $item){
						$vend_map_arr[] = $this->qbo_clear_braces($item->getId());
					}
				}
			}
		}
		return $vend_map_arr;
	}
	
	//31-03-2017
	public function get_push_invoice_map_data($item_arr,$is_qbosa_sr=false,$is_qbosa_est=false,$is_dn_md5=false,$qb_href=false){
		if(is_array($item_arr) && !empty($item_arr)){
			$item_ids = implode(',',$item_arr);
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				$osa = 'Invoice';
				if($this->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $is_qbosa_sr){
					$osa = 'SalesReceipt';
					$SalesReceiptService = new QuickBooks_IPP_Service_SalesReceipt();
					$invoices = $SalesReceiptService->query($Context,$realm ,"SELECT Id,DocNumber FROM SalesReceipt WHERE DocNumber IN({$item_ids}) ");
				}elseif($this->option_checked('mw_wc_qbo_sync_order_as_estimate') || $is_qbosa_est){
					$osa = 'Estimate';
					$EstimateService = new QuickBooks_IPP_Service_Estimate();
					$invoices = $EstimateService->query($Context,$realm ,"SELECT Id,DocNumber FROM Estimate WHERE DocNumber IN({$item_ids}) ");
				}
				else{
					$osa = 'Invoice';
					$invoiceService = new QuickBooks_IPP_Service_Invoice();
					$invoices = $invoiceService->query($Context,$realm ,"SELECT Id,DocNumber FROM Invoice WHERE DocNumber IN({$item_ids}) ");
				}
				
				//$this->_p($invoices);
				$inv_map_arr = array();
				if($invoices && !empty($invoices)){
					foreach($invoices as $invoice){
						$tmp_inv_arr = array();
						$tmp_inv_arr['Id'] = $this->qbo_clear_braces($invoice->getId());
						$tmp_inv_arr['DocNumber'] = $invoice->getDocNumber();
						if($is_dn_md5){
							$tmp_inv_arr['DocNumber_Md5'] = md5($tmp_inv_arr['DocNumber']);
						}
						
						if($qb_href){
							$tmp_inv_arr['qb_href'] = $this->get_push_qbo_view_href($osa,$tmp_inv_arr['Id']);
						}
						
						$inv_map_arr[] = $tmp_inv_arr;
					}
				}
				return $inv_map_arr;
			}
		}
	}
	
	/**/
	public function get_push_refund_map_data($item_arr){
		if(is_array($item_arr) && !empty($item_arr)){
			$item_ids = implode(',',$item_arr);
			if($this->is_connected()){
				$Context = $this->Context;
				$realm = $this->realm;
				
				$RefundReceiptService = new QuickBooks_IPP_Service_RefundReceipt();
				$refunds = $RefundReceiptService->query($Context,$realm ,"SELECT Id,DocNumber FROM RefundReceipt WHERE DocNumber IN({$item_ids}) ");
				
				//$this->_p($refunds);
				$rfnd_map_arr = array();
				if($refunds && !empty($refunds)){
					foreach($refunds as $refund){
						$tmp_rfnd_arr = array();
						$tmp_rfnd_arr['Id'] = $this->qbo_clear_braces($refund->getId());
						$tmp_rfnd_arr['DocNumber'] = $refund->getDocNumber();
						$rfnd_map_arr[] = $tmp_rfnd_arr;
					}
				}
				return $rfnd_map_arr;
			}
		}
	}
	
	//24-04-2017
	public function count_woocommerce_category_list($search_txt='') {
		global $wpdb;
		$sql = "
		SELECT COUNT(DISTINCT(t.term_id))
		FROM   {$wpdb->terms} t
		LEFT JOIN {$wpdb->term_taxonomy} tt
		ON t.term_id = tt.term_id
		LEFT JOIN ".$wpdb->termmeta." tm1 ON ( tm1.term_id = t.term_id
		AND tm1.meta_key =  'product_count_product_cat' )
		WHERE  tt.taxonomy = 'product_cat'
		";

		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$sql .=" AND ( t.name LIKE '%%%s%%' OR tt.description LIKE '%%%s%%' ) ";
		}

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt);
		}
		//echo $sql;
		return $wpdb->get_var($sql);
	}
	
	public function get_woocommerce_category_list($search_txt='',$limit='') {
		global $wpdb;
		$sql = "
		SELECT t.term_id AS id, t.name, t.slug, tm1.meta_value AS product_count,tt.description,tt.parent
		FROM   {$wpdb->terms} t
		LEFT JOIN {$wpdb->term_taxonomy} tt
		ON t.term_id = tt.term_id
		LEFT JOIN ".$wpdb->termmeta." tm1 ON ( tm1.term_id = t.term_id
		AND tm1.meta_key =  'product_count_product_cat' )
		WHERE  tt.taxonomy = 'product_cat'
		";

		//tt.count

		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$sql .=" AND ( t.name LIKE '%%%s%%' OR tt.description LIKE '%%%s%%' ) ";
		}

		$sql.=" ORDER  BY t.name ASC ";

		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt);
		}
		//echo $sql;
		return $this->get_data($sql);
	}
	
	//
	public function count_woocommerce_product_list($search_txt='',$is_inventory=false,$p_type='',$product_um_srch='') {
		$status = 'publish';
		global $wpdb;
		$search_txt = $this->sanitize($search_txt);

		$ext_join = '';
		if($search_txt!=''){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key =  '_sku' )";
		}

		if($is_inventory){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm8 ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_manage_stock')";
		}		

		$ext_sql = ($is_inventory)?" AND pm8.meta_value='yes' ":'';
		
		$p_type = $this->sanitize($p_type);
		if($p_type!='' && $p_type != 'all'){
			//$pt_jt = ($p_type=='simple')?'LEFT':'INNER';
			$pt_jt = 'INNER';
			$ext_join.= "
				{$pt_jt} JOIN {$wpdb->term_relationships} AS term_relationships ON p.ID = term_relationships.object_id
				{$pt_jt} JOIN {$wpdb->term_taxonomy} AS term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
				{$pt_jt} JOIN {$wpdb->terms} AS terms ON term_taxonomy.term_id = terms.term_id
			";
			if($p_type=='simple'){
				$ext_sql.= " AND term_taxonomy.taxonomy = 'product_type' AND (terms.slug = '{$p_type}' OR terms.slug = '' OR terms.slug IS NULL)";
				//$ext_sql.= " AND term_taxonomy.taxonomy = 'product_type' AND terms.slug NOT IN('grouped','external','variable','bundle')";
			}else{
				$ext_sql.= " AND term_taxonomy.taxonomy = 'product_type' AND terms.slug = '{$p_type}'";
			}			
		}
		
		/**/		
		$product_um_srch = $this->sanitize($product_um_srch);
		if($product_um_srch == 'only_m'){
			$ext_join.= " INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_product_pairs pmap ON p.ID = pmap.wc_product_id";
		}
		
		if($product_um_srch == 'only_um'){
			$ext_join.= " LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_product_pairs pmap ON p.ID = pmap.wc_product_id";
			$ext_sql.= " AND (pmap.quickbook_product_id IS NULL OR pmap.quickbook_product_id = '')";
		}
		
		//
		if($this->option_checked('mw_wc_qbo_sync_hide_vpp_fmp_pages') && empty($p_type)){
			$ext_sql.= " AND p.ID NOT IN(SELECT post_parent FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND post_parent>0) ";
		}
		
		$sql = "
		SELECT COUNT(DISTINCT(p.ID))
		FROM ".$wpdb->posts." p
		{$ext_join}
		WHERE p.post_type =  'product'
		AND p.post_status NOT IN('trash','auto-draft','inherit')
		
		{$ext_sql}
		";
		
		//

		if($search_txt!=''){
			$sql .=" AND ( p.post_title LIKE '%%%s%%' OR pm1.meta_value LIKE '%%%s%%' OR p.ID = %s ) ";
		}
		
		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt,$search_txt);
		}
		//echo $sql;
		return $wpdb->get_var($sql);
	}
	
	//New
	public function get_product_type_by_id($product_id){
		$pt = '';
		$product_id = (int) $product_id;
		if($product_id>0){
			//return (string) get_product_type($product_id);
			
			global $wpdb;
			$pt_q = "
			SELECT DISTINCT(p.ID), terms.name as wc_pt
			FROM ".$wpdb->posts." p			
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON p.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
			INNER JOIN {$wpdb->terms} AS terms ON term_taxonomy.term_id = terms.term_id
			WHERE p.post_type =  'product'
			AND p.ID = %d
			AND term_taxonomy.taxonomy = 'product_type'
			";
			$pt_q = $wpdb->prepare($pt_q,$product_id);
			$pt_row = $this->get_row($pt_q);
			if(is_array($pt_row) && !empty($pt_row)){
				$pt = $pt_row['wc_pt'];
			}
			
			if(empty($pt)){
				$_product = wc_get_product( $product_id );
				if(is_object($_product) && !empty($_product)){
					if($_product->is_type( 'simple' )){
						$pt = 'simple';
					}elseif($_product->is_type( 'grouped' )){
						$pt = 'grouped';
					}elseif($_product->is_type( 'external' )){
						$pt = 'external';
					}elseif($_product->is_type( 'variable' )){
						$pt = 'variable';
					}elseif($_product->is_type( 'bundle' )){
						$pt = 'bundle';
					}
				}
			}
		}
		return $pt;
	}
	
	public function get_woocommerce_product_list($search_txt='',$limit='',$is_inventory=false,$p_type='',$product_um_srch='') {
		$status = 'publish';
		global $wpdb;
		$search_txt = $this->sanitize($search_txt);

		$ext_join = '';
		if($search_txt!=''){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key =  '_sku' )";
		}

		if($is_inventory){
			$ext_join.= "LEFT  JOIN ".$wpdb->postmeta." pm8 ON ( pm8.post_id = p.ID	AND pm8.meta_key =  '_manage_stock')";
		}

		$ext_sql = ($is_inventory)?" AND pm8.meta_value='yes' ":'';
		
		$p_type = $this->sanitize($p_type);
		if($p_type!='' && $p_type != 'all'){
			//$pt_jt = ($p_type=='simple')?'LEFT':'INNER';
			$pt_jt = 'INNER';
			$ext_join.= "
				{$pt_jt} JOIN {$wpdb->term_relationships} AS term_relationships ON p.ID = term_relationships.object_id
				{$pt_jt} JOIN {$wpdb->term_taxonomy} AS term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
				{$pt_jt} JOIN {$wpdb->terms} AS terms ON term_taxonomy.term_id = terms.term_id
			";
			if($p_type=='simple'){
				$ext_sql.= " AND term_taxonomy.taxonomy = 'product_type' AND (terms.slug = '{$p_type}' OR terms.slug = '' OR terms.slug IS NULL)";
				//$ext_sql.= " AND term_taxonomy.taxonomy = 'product_type' AND terms.slug NOT IN('grouped','external','variable','bundle')";
			}else{
				$ext_sql.= " AND term_taxonomy.taxonomy = 'product_type' AND terms.slug = '{$p_type}'";
			}
		}
		
		/**/		
		$product_um_srch = $this->sanitize($product_um_srch);
		if($product_um_srch == 'only_m'){
			$ext_join.= " INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_product_pairs pmap ON p.ID = pmap.wc_product_id";
		}
		
		if($product_um_srch == 'only_um'){
			$ext_join.= " LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_product_pairs pmap ON p.ID = pmap.wc_product_id";
			$ext_sql.= " AND (pmap.quickbook_product_id IS NULL OR pmap.quickbook_product_id = '')";
		}
		
		if($this->option_checked('mw_wc_qbo_sync_hide_vpp_fmp_pages') && empty($p_type)){
			$ext_sql.= " AND p.ID NOT IN(SELECT post_parent FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND post_parent>0) ";
		}
		
		$sql = "
		SELECT DISTINCT(p.ID), p.post_title AS name
		FROM ".$wpdb->posts." p
		{$ext_join}
		WHERE p.post_type =  'product'
		AND p.post_status NOT IN('trash','auto-draft','inherit')
		
		{$ext_sql}
		";
		
		//
		
		if($search_txt!=''){
			$sql .=" AND ( p.post_title LIKE '%%%s%%' OR pm1.meta_value LIKE '%%%s%%' OR p.ID = %s ) ";
		}

		$orderby = 'p.post_title ASC';
		$sql .= ' ORDER BY  '.$orderby;

		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt,$search_txt);
		}

		//echo $sql;
		$r_data = array();
		$q_data =  $this->get_data($sql);
		//$this->_p($q_data);

		if(is_array($q_data) && !empty($q_data)){
			foreach($q_data as $rd){
				$pd_tmp_arr = array();
				$pd_tmp_arr['ID'] = $rd['ID'];
				$pd_tmp_arr['name'] = $rd['name'];
				
				$p_meta = get_post_meta($rd['ID']);
				$pd_tmp_arr['sku'] = (is_array($p_meta) && isset($p_meta['_sku'][0]))?$p_meta['_sku'][0]:'';
				$pd_tmp_arr['regular_price'] = (is_array($p_meta) && isset($p_meta['_regular_price'][0]))?$p_meta['_regular_price'][0]:'';
				$pd_tmp_arr['sale_price'] = (is_array($p_meta) && isset($p_meta['_sale_price'][0]))?$p_meta['_sale_price'][0]:'';
				$pd_tmp_arr['price'] = (is_array($p_meta) && isset($p_meta['_price'][0]))?$p_meta['_price'][0]:'';
				$pd_tmp_arr['stock'] = (is_array($p_meta) && isset($p_meta['_stock'][0]))?$p_meta['_stock'][0]:'';
				$pd_tmp_arr['backorders'] = (is_array($p_meta) && isset($p_meta['_backorders'][0]))?$p_meta['_backorders'][0]:'';
				$pd_tmp_arr['stock_status'] = (is_array($p_meta) && isset($p_meta['_stock_status'][0]))?$p_meta['_stock_status'][0]:'';
				$pd_tmp_arr['manage_stock'] = (is_array($p_meta) && isset($p_meta['_manage_stock'][0]))?$p_meta['_manage_stock'][0]:'';
				$pd_tmp_arr['total_sales'] = (is_array($p_meta) && isset($p_meta['total_sales'][0]))?$p_meta['total_sales'][0]:'';				
						
				$pd_tmp_arr['wc_product_type'] = $this->get_product_type_by_id($rd['ID']);

				$ext_cq = "
				SELECT pmap.quickbook_product_id, pmap.class_id, qp.name as qp_name, qp.sku as qp_sku, qp.product_type as qp_product_type
				FROM ".$wpdb->posts." p
				LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_product_pairs pmap ON p.ID = pmap.wc_product_id
				LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_qbo_items qp ON pmap.quickbook_product_id = qp.itemid
				WHERE p.ID = ".$rd['ID']."
				LIMIT 0,1
				";
				$ext_data =  $this->get_row($ext_cq);
				$pd_tmp_arr['quickbook_product_id'] = (!empty($ext_data) && isset($ext_data['quickbook_product_id']))?$ext_data['quickbook_product_id']:'';
				$pd_tmp_arr['class_id'] = (!empty($ext_data) && isset($ext_data['class_id']))?$ext_data['class_id']:'';

				$pd_tmp_arr['qp_name'] = (!empty($ext_data) && isset($ext_data['qp_name']))?$ext_data['qp_name']:'';
				$pd_tmp_arr['qp_sku'] = (!empty($ext_data) && isset($ext_data['qp_sku']))?$ext_data['qp_sku']:'';
				$pd_tmp_arr['qp_product_type'] = (!empty($ext_data) && isset($ext_data['qp_product_type']))?$ext_data['qp_product_type']:'';
				$r_data[] = $pd_tmp_arr;
			}
		}

		unset($q_data);
		//$this->_p($r_data);
		return $r_data;
	}
	
	//07-03-2017
	public function count_woocommerce_variation_list($search_txt='',$is_inventory=false,$stock_status='',$product_um_srch='') {
		global $wpdb;

		$search_txt = $this->sanitize($search_txt);
		$ext_join = '';
		
		if($search_txt!=''){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID	AND pm1.meta_key =  '_sku' )";
			$ext_join.= " LEFT JOIN " . $wpdb->posts . " p1 ON p.post_parent = p1.ID";
		}
		
		$stock_status = $this->sanitize($stock_status);
		if($stock_status!=''){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm7 ON ( pm7.post_id = p.ID AND pm7.meta_key =  '_stock_status' ) ";
			
		}
		
		if($is_inventory){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm8 ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_manage_stock')";
		}
		
		$ext_sql = '';
		
		/**/		
		$product_um_srch = $this->sanitize($product_um_srch);
		if($product_um_srch == 'only_m'){
			$ext_join.= " INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_variation_pairs pmap ON p.ID = pmap.wc_variation_id";
		}
		
		if($product_um_srch == 'only_um'){
			$ext_join.= " LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_variation_pairs pmap ON p.ID = pmap.wc_variation_id";
			$ext_sql.= " AND (pmap.quickbook_product_id IS NULL OR pmap.quickbook_product_id = '')";
		}
		
		$sql = "
		SELECT COUNT(DISTINCT(p.ID))
		FROM ".$wpdb->posts." p
		{$ext_join}
		
		WHERE p.post_type =  'product_variation'		
		AND p.post_status NOT IN('trash','auto-draft','inherit')
		{$ext_sql}
		";
		
		if($is_inventory){
			$sql .=" AND pm8.meta_value='yes' ";
		}

		if($search_txt!=''){
			$sql .=" AND ( p.post_title LIKE '%%%s%%' OR p1.post_title LIKE '%%%s%%' OR pm1.meta_value LIKE '%%%s%%' OR p.ID = %s ) ";
		}
		
		if($stock_status!=''){
			$sql.= " AND pm7.meta_value='{$stock_status}' ";
		}

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt,$search_txt,$search_txt);
		}
		//echo $sql;
		return $wpdb->get_var($sql);
	}
	
	public function get_woocommerce_variation_list($search_txt='',$is_inventory=false,$limit='',$stock_status='',$product_um_srch='') {
		global $wpdb;
		
		$search_txt = $this->sanitize($search_txt);
		$ext_join = '';
		if($search_txt!=''){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm1 ON ( pm1.post_id = p.ID	AND pm1.meta_key =  '_sku' )";
			$ext_join.= " LEFT JOIN " . $wpdb->posts . " p1 ON p.post_parent = p1.ID";
		}
		
		$stock_status = $this->sanitize($stock_status);
		if($stock_status!=''){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm7 ON ( pm7.post_id = p.ID AND pm7.meta_key =  '_stock_status' ) ";
			
		}
		
		if($is_inventory){
			$ext_join.= " LEFT JOIN ".$wpdb->postmeta." pm8 ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_manage_stock')";
		}
		
		$ext_sql = '';
		
		/**/		
		$product_um_srch = $this->sanitize($product_um_srch);
		if($product_um_srch == 'only_m'){
			$ext_join.= " INNER JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_variation_pairs pmap ON p.ID = pmap.wc_variation_id";
		}
		
		if($product_um_srch == 'only_um'){
			$ext_join.= " LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_variation_pairs pmap ON p.ID = pmap.wc_variation_id";
			$ext_sql.= " AND (pmap.quickbook_product_id IS NULL OR pmap.quickbook_product_id = '')";
		}
		
		$sql = "
		SELECT DISTINCT(p.ID), p.post_title AS name, p.post_parent as parent_id, p.post_name
		FROM ".$wpdb->posts." p
		{$ext_join}
		WHERE p.post_type =  'product_variation'
		AND p.post_status NOT IN('trash','auto-draft','inherit')		
		{$ext_sql}
		";
		
		if($is_inventory){
			$sql .=" AND pm8.meta_value='yes' ";
		}

		if($search_txt!=''){
			$sql .=" AND ( p.post_title LIKE '%%%s%%' OR p1.post_title LIKE '%%%s%%' OR pm1.meta_value LIKE '%%%s%%' OR p.ID = %s ) ";
		}
		
		if($stock_status!=''){
			$sql.= " AND pm7.meta_value='{$stock_status}' ";
		}
		
		if($search_txt!=''){
			$orderby = 'p.ID DESC, p1.post_parent ASC';
		}else{
			$orderby = 'p.ID DESC';
		}

		$sql .= ' ORDER BY  '.$orderby;

		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt,$search_txt,$search_txt);
		}
		
		$r_data = array();
		$q_data =  $this->get_data($sql);
		
		if(is_array($q_data) && !empty($q_data)){
			foreach($q_data as $rd){
				$pd_tmp_arr = array();
				$pd_tmp_arr['ID'] = $rd['ID'];
				$pd_tmp_arr['name'] = $rd['name'];
				$pd_tmp_arr['post_name'] = $rd['post_name'];
				$pd_tmp_arr['parent_id'] = $rd['parent_id'];
				
				$p_meta = get_post_meta($rd['ID']);
				$pd_tmp_arr['sku'] = (is_array($p_meta) && isset($p_meta['_sku'][0]))?$p_meta['_sku'][0]:'';
				$pd_tmp_arr['regular_price'] = (is_array($p_meta) && isset($p_meta['_regular_price'][0]))?$p_meta['_regular_price'][0]:'';
				$pd_tmp_arr['sale_price'] = (is_array($p_meta) && isset($p_meta['_sale_price'][0]))?$p_meta['_sale_price'][0]:'';
				$pd_tmp_arr['price'] = (is_array($p_meta) && isset($p_meta['_price'][0]))?$p_meta['_price'][0]:'';
				$pd_tmp_arr['stock'] = (is_array($p_meta) && isset($p_meta['_stock'][0]))?$p_meta['_stock'][0]:'';
				$pd_tmp_arr['backorders'] = (is_array($p_meta) && isset($p_meta['_backorders'][0]))?$p_meta['_backorders'][0]:'';
				$pd_tmp_arr['stock_status'] = (is_array($p_meta) && isset($p_meta['_stock_status'][0]))?$p_meta['_stock_status'][0]:'';
				$pd_tmp_arr['manage_stock'] = (is_array($p_meta) && isset($p_meta['_manage_stock'][0]))?$p_meta['_manage_stock'][0]:'';
				$pd_tmp_arr['total_sales'] = (is_array($p_meta) && isset($p_meta['total_sales'][0]))?$p_meta['total_sales'][0]:'';
				
				$attribute_names = '';
				$attribute_names_arr = array();
				
				$attribute_values = '';
				$attribute_values_arr = array();
				
				if(is_array($p_meta) && !empty($p_meta)){
					foreach($p_meta as $pm_k => $pm_v){
						if($this->start_with($pm_k,'attribute_')){
							$attribute_names_arr[] = $pm_k;
							$attribute_values_arr[] = (isset($pm_v[0]))?$pm_v[0]:'';
						}
					}
				}
				
				if(!empty($attribute_names_arr) && !empty($attribute_values_arr)){
					$attribute_names = implode(',',$attribute_names_arr);
					$attribute_values = implode(',',$attribute_values_arr);
				}
				
				$pd_tmp_arr['attribute_names'] = $attribute_names;
				$pd_tmp_arr['attribute_values'] = $attribute_values;
				
				$parent_name = '';
				if($rd['parent_id']>0){
					$parent_id = (int) $rd['parent_id'];
					$parent_name = $this->get_field_by_val($wpdb->posts,'post_title','ID',$parent_id);
				}				
				$pd_tmp_arr['parent_name'] = $parent_name;
				
				$ext_cq = "
				SELECT pmap.quickbook_product_id, pmap.class_id, qp.name as qp_name, qp.sku as qp_sku, qp.product_type as qp_product_type
				FROM ".$wpdb->posts." p
				LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_variation_pairs pmap ON p.ID = pmap.wc_variation_id
				LEFT JOIN " . $wpdb->prefix . "mw_wc_qbo_sync_qbo_items qp ON pmap.quickbook_product_id = qp.itemid
				WHERE p.ID = ".$rd['ID']."
				LIMIT 0,1
				";
				
				$ext_data =  $this->get_row($ext_cq);
				$pd_tmp_arr['quickbook_product_id'] = (!empty($ext_data) && isset($ext_data['quickbook_product_id']))?$ext_data['quickbook_product_id']:'';
				$pd_tmp_arr['class_id'] = (!empty($ext_data) && isset($ext_data['class_id']))?$ext_data['class_id']:'';

				$pd_tmp_arr['qp_name'] = (!empty($ext_data) && isset($ext_data['qp_name']))?$ext_data['qp_name']:'';
				$pd_tmp_arr['qp_sku'] = (!empty($ext_data) && isset($ext_data['qp_sku']))?$ext_data['qp_sku']:'';
				$pd_tmp_arr['qp_product_type'] = (!empty($ext_data) && isset($ext_data['qp_product_type']))?$ext_data['qp_product_type']:'';
				
				$r_data[] = $pd_tmp_arr;
			}
		}
		
		unset($q_data);
		//$this->_p($r_data);
		return $r_data;
	}
	
	//29-03-2017
	public function get_push_all_wc_order_count(){
		global $wpdb;
		$sql = "
		SELECT COUNT(DISTINCT(p.ID))
		FROM
		{$wpdb->prefix}posts as p
		WHERE
		p.post_type = 'shop_order'
		";

		//echo $sql;
		return (int) $wpdb->get_var($sql);
	}
	
	public function get_push_all_wc_order_ids($count){
		$count = (int) $count;
		if($count>0){
			global $wpdb;

			$gc_length = $count*10;
			$wpdb->query("SET group_concat_max_len = {$gc_length}");

			$sql = "
			SELECT GROUP_CONCAT(DISTINCT(p.ID)) AS `ids`
			FROM
			{$wpdb->prefix}posts as p
			WHERE
			p.post_type = 'shop_order'
			";

			//echo $sql;
			return (string) $wpdb->get_var($sql);
		}
	}
	
	public function count_order_list($search_txt='',$date_from='',$date_to='',$status=''){
		global $wpdb;
		
		$ext_whr = '';
		$ext_join = '';
		
		$onc_mf = $this->get_woo_ord_number_key_field();
		
		if($this->is_pl_res_tml()){
			//$ext_whr = " AND p.post_date BETWEEN NOW() - INTERVAL 30 DAY AND NOW() ";
			
			$wp_date_time_c = $this->now();
			$last_30_days_dt = date('Y-m-d H:i:s', strtotime('-'.$this->get_hd_ldys_lmt().' days', strtotime($wp_date_time_c)));
			$ext_whr = " AND p.post_date BETWEEN '{$last_30_days_dt}' AND '{$wp_date_time_c}' ";
		}		
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$ext_join .="
			LEFT JOIN ".$wpdb->postmeta." pm1
			ON ( pm1.post_id = p.ID AND pm1.meta_key =  '_billing_first_name' )
			LEFT JOIN ".$wpdb->postmeta." pm2
			ON ( pm2.post_id = p.ID AND pm2.meta_key =  '_billing_last_name' )
			LEFT JOIN ".$wpdb->postmeta." pm7
			ON ( pm7.post_id = p.ID AND pm7.meta_key =  '_billing_company' )
			";
			
			if(!empty($onc_mf)){
				$ext_join .="
				LEFT JOIN ".$wpdb->postmeta." pm10
				ON ( pm10.post_id = p.ID AND pm10.meta_key =  '{$onc_mf}' )
				";
			}
			
			if(!empty($onc_mf)){
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%'  OR p.ID = %s OR pm10.meta_value = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}else{
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%' OR p.ID = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}
		}
		
		$status = $this->sanitize($status);
		if($status!=''){
			$ext_whr .=$wpdb->prepare(" AND p.post_status = %s",$status);
		}
		
		$date_from = $this->sanitize($date_from);
		if($date_from!=''){
			$ext_whr .=" AND p.post_date>='".$date_from." 00:00:00'";
		}

		$date_to = $this->sanitize($date_to);
		if($date_to!=''){
			$ext_whr .=" AND p.post_date<='".$date_to." 23:59:59'";
		}
		
		$sql = "
		SELECT COUNT(DISTINCT(p.ID))
		FROM
		{$wpdb->prefix}posts as p
		{$ext_join}
		WHERE
		p.post_type = 'shop_order'
		{$ext_whr}
		";
		
		//echo $sql;		
		return $wpdb->get_var($sql);	
	}
	
	public function get_order_list($search_txt='',$limit='',$date_from='',$date_to='',$status=''){
		global $wpdb;
		
		$ext_whr = '';
		$ext_join = '';
		
		$onc_mf = $this->get_woo_ord_number_key_field();
		
		if($this->is_pl_res_tml()){
			//$ext_whr = " AND p.post_date BETWEEN NOW() - INTERVAL 30 DAY AND NOW() ";
			
			$wp_date_time_c = $this->now();
			$last_30_days_dt = date('Y-m-d H:i:s', strtotime('-'.$this->get_hd_ldys_lmt().' days', strtotime($wp_date_time_c)));
			$ext_whr = " AND p.post_date BETWEEN '{$last_30_days_dt}' AND '{$wp_date_time_c}' ";
		}
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$ext_join .="
			LEFT JOIN ".$wpdb->postmeta." pm1
			ON ( pm1.post_id = p.ID AND pm1.meta_key =  '_billing_first_name' )
			LEFT JOIN ".$wpdb->postmeta." pm2
			ON ( pm2.post_id = p.ID AND pm2.meta_key =  '_billing_last_name' )
			LEFT JOIN ".$wpdb->postmeta." pm7
			ON ( pm7.post_id = p.ID AND pm7.meta_key =  '_billing_company' )
			";
			
			if(!empty($onc_mf)){
				$ext_join .="
				LEFT JOIN ".$wpdb->postmeta." pm10
				ON ( pm10.post_id = p.ID AND pm10.meta_key =  '{$onc_mf}' )
				";
			}
			
			if(!empty($onc_mf)){
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%'  OR p.ID = %s OR pm10.meta_value = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}else{
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%' OR p.ID = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}
			
		}
		
		$status = $this->sanitize($status);
		if($status!=''){
			$ext_whr .=$wpdb->prepare(" AND p.post_status = %s",$status);
		}
		
		$date_from = $this->sanitize($date_from);
		if($date_from!=''){
			$ext_whr .=" AND p.post_date>='".$date_from." 00:00:00'";
		}

		$date_to = $this->sanitize($date_to);
		if($date_to!=''){
			$ext_whr .=" AND p.post_date<='".$date_to." 23:59:59'";
		}
		
		$sql = "
		SELECT DISTINCT(p.ID), p.post_status, p.post_date
		FROM
		{$wpdb->prefix}posts as p
		{$ext_join}
		WHERE
		p.post_type = 'shop_order'
		{$ext_whr}
		";
		
		$orderby = 'p.post_date DESC';
		$sql .= ' ORDER BY  '.$orderby;

		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}
		
		//echo $sql;
		$r_data = array();
		$q_data =  $this->get_data($sql);
		//$this->_p($q_data);
		
		if(is_array($q_data) && !empty($q_data)){
			foreach($q_data as $rd){
				$od_tmp_arr = array();
				$od_tmp_arr['ID'] = $rd['ID'];
				$od_tmp_arr['post_status'] = $rd['post_status'];
				$od_tmp_arr['post_date'] = $rd['post_date'];
				
				$o_meta = get_post_meta($rd['ID']);
				$od_tmp_arr['billing_first_name'] = (is_array($o_meta) && isset($o_meta['_billing_first_name'][0]))?$o_meta['_billing_first_name'][0]:'';
				$od_tmp_arr['billing_last_name'] = (is_array($o_meta) && isset($o_meta['_billing_last_name'][0]))?$o_meta['_billing_last_name'][0]:'';
				
				$od_tmp_arr['order_total'] = (is_array($o_meta) && isset($o_meta['_order_total'][0]))?$o_meta['_order_total'][0]:'';
				$od_tmp_arr['order_key'] = (is_array($o_meta) && isset($o_meta['_order_key'][0]))?$o_meta['_order_key'][0]:'';
				$od_tmp_arr['customer_user'] = (is_array($o_meta) && isset($o_meta['_customer_user'][0]))?$o_meta['_customer_user'][0]:'';
				$od_tmp_arr['order_currency'] = (is_array($o_meta) && isset($o_meta['_order_currency'][0]))?$o_meta['_order_currency'][0]:'';				
				$od_tmp_arr['payment_method'] = (is_array($o_meta) && isset($o_meta['_payment_method'][0]))?$o_meta['_payment_method'][0]:'';
				$od_tmp_arr['payment_method_title'] = (is_array($o_meta) && isset($o_meta['_payment_method_title'][0]))?$o_meta['_payment_method_title'][0]:'';
				$od_tmp_arr['order_number_formatted'] = (is_array($o_meta) && isset($o_meta['_order_number_formatted'][0]))?$o_meta['_order_number_formatted'][0]:'';
				
				/**/
				$od_tmp_arr['_alg_wc_custom_order_number'] = (is_array($o_meta) && isset($o_meta['_alg_wc_custom_order_number'][0]))?$o_meta['_alg_wc_custom_order_number'][0]:'';
				
				/**/
				if(!empty($this->get_option('mw_wc_qbo_sync_compt_p_wconmkn'))){
					$wconmkn_key = $this->get_option('mw_wc_qbo_sync_compt_p_wconmkn');
					$od_tmp_arr[$wconmkn_key] = (is_array($o_meta) && isset($o_meta[$wconmkn_key][0]))?$o_meta[$wconmkn_key][0]:'';
				}
				
				$od_tmp_arr['billing_company'] = (is_array($o_meta) && isset($o_meta['_billing_company'][0]))?$o_meta['_billing_company'][0]:'';
				
				$od_tmp_arr['_mw_qbo_sync_ord_doc_no'] = (is_array($o_meta) && isset($o_meta['_mw_qbo_sync_ord_doc_no'][0]))?$o_meta['_mw_qbo_sync_ord_doc_no'][0]:'';
				
				$r_data[] = $od_tmp_arr;
			}
		}
		
		unset($q_data);
		//$this->_p($r_data);
		return $r_data;		
	}
	
	public function count_wc_payment_list($search_txt='',$date_from='',$date_to=''){
		global $wpdb;
		
		$ext_whr = '';
		$ext_join = '';
		
		$onc_mf = $this->get_woo_ord_number_key_field();
		
		if($this->is_pl_res_tml()){
			//$ext_whr = " AND p.post_date BETWEEN NOW() - INTERVAL 30 DAY AND NOW() ";
			
			$wp_date_time_c = $this->now();
			$last_30_days_dt = date('Y-m-d H:i:s', strtotime('-'.$this->get_hd_ldys_lmt().' days', strtotime($wp_date_time_c)));
			$ext_whr = " AND p.post_date BETWEEN '{$last_30_days_dt}' AND '{$wp_date_time_c}' ";
		}
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$ext_join .="
			LEFT JOIN ".$wpdb->postmeta." pm1
			ON ( pm1.post_id = p.ID AND pm1.meta_key =  '_billing_first_name' )
			LEFT JOIN ".$wpdb->postmeta." pm2
			ON ( pm2.post_id = p.ID AND pm2.meta_key =  '_billing_last_name' )
			LEFT JOIN ".$wpdb->postmeta." pm7
			ON ( pm7.post_id = p.ID AND pm7.meta_key =  '_billing_company' )
			";
			
			if(!empty($onc_mf)){
				$ext_join .="
				LEFT JOIN ".$wpdb->postmeta." pm11
				ON ( pm11.post_id = p.ID AND pm11.meta_key =  '{$onc_mf}' )
				";
			}			
			
			if(!empty($onc_mf)){
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%'  OR p.ID = %s OR pm11.meta_value = %s OR pm8.meta_value = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}else{
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%' OR p.ID = %s OR pm8.meta_value = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}
		}
		
		$date_from = $this->sanitize($date_from);
		if($date_from!=''){
			//$ext_whr .=" AND p.post_date>='".$date_from." 00:00:00'";
			$ext_whr .=" AND pm9.meta_value>='".$date_from." 00:00:00'";
			//$ext_whr .=" AND (pm9.meta_value>='".$date_from." 00:00:00' OR p.post_date>='".$date_from." 00:00:00')";
		}
		
		$date_to = $this->sanitize($date_to);
		if($date_to!=''){
			//$ext_whr .=" AND p.post_date<='".$date_to." 23:59:59'";
			$ext_whr .=" AND pm9.meta_value<='".$date_to." 23:59:59'";
			//$ext_whr .=" AND (pm9.meta_value<='".$date_to." 23:59:59' OR p.post_date<='".$date_to." 23:59:59')";
		}
		
		if($date_from!='' || $date_to!=''){
			$ext_join .="
			LEFT JOIN ".$wpdb->postmeta." pm9
			ON ( pm9.post_id = p.ID AND pm9.meta_key =  '_paid_date' )
			";			
		}
		
		$sql = "
		SELECT COUNT(DISTINCT(p.ID))
		FROM
		{$wpdb->prefix}posts as p
		LEFT JOIN ".$wpdb->postmeta." pm8
		ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_transaction_id' )
		LEFT JOIN ".$wpdb->postmeta." pm10
		ON ( pm10.post_id = p.ID AND pm10.meta_key =  '_payment_method' )
		{$ext_join}
		WHERE
		p.post_type = 'shop_order'
		AND pm8.meta_id > 0		
		AND pm10.meta_value!=''
		{$ext_whr}
		";
		
		//echo $sql;		
		return $wpdb->get_var($sql);
	}
	
	public function get_wc_payment_list($search_txt='',$limit='',$date_from='',$date_to=''){
		global $wpdb;
		
		$ext_whr = '';
		$ext_join = '';
		
		$onc_mf = $this->get_woo_ord_number_key_field();
		
		if($this->is_pl_res_tml()){
			//$ext_whr = " AND p.post_date BETWEEN NOW() - INTERVAL 30 DAY AND NOW() ";
			
			$wp_date_time_c = $this->now();
			$last_30_days_dt = date('Y-m-d H:i:s', strtotime('-'.$this->get_hd_ldys_lmt().' days', strtotime($wp_date_time_c)));
			$ext_whr = " AND p.post_date BETWEEN '{$last_30_days_dt}' AND '{$wp_date_time_c}' ";
		}
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$ext_join .="
			LEFT JOIN ".$wpdb->postmeta." pm1
			ON ( pm1.post_id = p.ID AND pm1.meta_key =  '_billing_first_name' )
			LEFT JOIN ".$wpdb->postmeta." pm2
			ON ( pm2.post_id = p.ID AND pm2.meta_key =  '_billing_last_name' )
			LEFT JOIN ".$wpdb->postmeta." pm7
			ON ( pm7.post_id = p.ID AND pm7.meta_key =  '_billing_company' )
			";
			
			if(!empty($onc_mf)){
				$ext_join .="
				LEFT JOIN ".$wpdb->postmeta." pm11
				ON ( pm11.post_id = p.ID AND pm11.meta_key =  '{$onc_mf}' )
				";
			}			
			
			if(!empty($onc_mf)){
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%'  OR p.ID = %s OR pm11.meta_value = %s OR pm8.meta_value = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}else{
				$ext_whr .=$wpdb->prepare(" AND ( pm1.meta_value LIKE '%%%s%%' OR pm2.meta_value LIKE '%%%s%%' OR pm7.meta_value LIKE '%%%s%%' OR CONCAT(pm1.meta_value,' ', pm2.meta_value) LIKE '%%%s%%' OR p.ID = %s OR pm8.meta_value = %s ) ",$search_txt,$search_txt,$search_txt,$search_txt,$search_txt,$search_txt);
			}
			
		}
		
		$date_from = $this->sanitize($date_from);
		if($date_from!=''){
			//$ext_whr .=" AND p.post_date>='".$date_from." 00:00:00'";
			$ext_whr .=" AND pm9.meta_value>='".$date_from." 00:00:00'";
			//$ext_whr .=" AND (pm9.meta_value>='".$date_from." 00:00:00' OR p.post_date>='".$date_from." 00:00:00')";
		}

		$date_to = $this->sanitize($date_to);
		if($date_to!=''){
			//$ext_whr .=" AND p.post_date<='".$date_to." 23:59:59'";
			$ext_whr .=" AND pm9.meta_value<='".$date_to." 23:59:59'";
			//$ext_whr .=" AND (pm9.meta_value<='".$date_to." 23:59:59' OR p.post_date<='".$date_to." 23:59:59')";
		}
		
		if($date_from!='' || $date_to!=''){
			$ext_join .="
			LEFT JOIN ".$wpdb->postmeta." pm9
			ON ( pm9.post_id = p.ID AND pm9.meta_key =  '_paid_date' )
			";			
		}
		
		$sql = "
		SELECT DISTINCT(p.ID), p.post_status, p.post_date, pm8.meta_id as payment_id
		FROM
		{$wpdb->prefix}posts as p
		LEFT JOIN ".$wpdb->postmeta." pm8
		ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_transaction_id' )
		LEFT JOIN ".$wpdb->postmeta." pm10
		ON ( pm10.post_id = p.ID AND pm10.meta_key =  '_payment_method' )
		{$ext_join}
		WHERE
		p.post_type = 'shop_order'
		AND pm8.meta_id > 0		
		AND pm10.meta_value!=''
		{$ext_whr}
		";
		
		$orderby = 'p.post_date DESC';
		//$orderby = '(pm9.meta_value IS NULL) DESC, p.ID DESC';
		$sql .= ' ORDER BY  '.$orderby;

		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}
		
		//echo $sql;
		$r_data = array();
		$q_data =  $this->get_data($sql);
		//$this->_p($q_data);
		
		if(is_array($q_data) && !empty($q_data)){
			foreach($q_data as $rd){
				$pmd_tmp_arr = array();
				$pmd_tmp_arr['order_id'] = $rd['ID'];
				$pmd_tmp_arr['order_status'] = $rd['post_status'];
				$pmd_tmp_arr['order_date'] = $rd['post_date'];
				
				$payment_id = (int) $rd['payment_id'];
				$pmd_tmp_arr['payment_id'] = $payment_id;
				
				$pm_meta = get_post_meta($rd['ID']);
				$pmd_tmp_arr['billing_first_name'] = (is_array($pm_meta) && isset($pm_meta['_billing_first_name'][0]))?$pm_meta['_billing_first_name'][0]:'';
				$pmd_tmp_arr['billing_last_name'] = (is_array($pm_meta) && isset($pm_meta['_billing_last_name'][0]))?$pm_meta['_billing_last_name'][0]:'';
				
				$pmd_tmp_arr['order_total'] = (is_array($pm_meta) && isset($pm_meta['_order_total'][0]))?$pm_meta['_order_total'][0]:'';
				$pmd_tmp_arr['order_key'] = (is_array($pm_meta) && isset($pm_meta['_order_key'][0]))?$pm_meta['_order_key'][0]:'';
				$pmd_tmp_arr['customer_user'] = (is_array($pm_meta) && isset($pm_meta['_customer_user'][0]))?$pm_meta['_customer_user'][0]:'';
				$pmd_tmp_arr['order_currency'] = (is_array($pm_meta) && isset($pm_meta['_order_currency'][0]))?$pm_meta['_order_currency'][0]:'';
				
				$pmd_tmp_arr['transaction_id'] = (is_array($pm_meta) && isset($pm_meta['_transaction_id'][0]))?$pm_meta['_transaction_id'][0]:'';
				$pmd_tmp_arr['paid_date'] = (is_array($pm_meta) && isset($pm_meta['_paid_date'][0]))?$pm_meta['_paid_date'][0]:'';
				
				$pmd_tmp_arr['payment_method'] = (is_array($pm_meta) && isset($pm_meta['_payment_method'][0]))?$pm_meta['_payment_method'][0]:'';
				$pmd_tmp_arr['payment_method_title'] = (is_array($pm_meta) && isset($pm_meta['_payment_method_title'][0]))?$pm_meta['_payment_method_title'][0]:'';
				
				$pmd_tmp_arr['billing_company'] = (is_array($pm_meta) && isset($pm_meta['_billing_company'][0]))?$pm_meta['_billing_company'][0]:'';
				
				$pmd_tmp_arr['stripe_txn_fee'] = (is_array($pm_meta) && isset($pm_meta['Stripe Fee'][0]))?$pm_meta['Stripe Fee'][0]:'';
				if(empty($pmd_tmp_arr['stripe_txn_fee'])){
					$pmd_tmp_arr['stripe_txn_fee'] = (is_array($pm_meta) && isset($pm_meta['_stripe_fee'][0]))?$pm_meta['_stripe_fee'][0]:'';
				}
				
				$pmd_tmp_arr['paypal_txn_fee'] = (is_array($pm_meta) && isset($pm_meta['PayPal Transaction Fee'][0]))?$pm_meta['PayPal Transaction Fee'][0]:'';
				
				$pmd_tmp_arr['order_number_formatted'] = (is_array($pm_meta) && isset($pm_meta['_order_number_formatted'][0]))?$pm_meta['_order_number_formatted'][0]:'';
				
				$pmd_tmp_arr['_alg_wc_custom_order_number'] = (is_array($pm_meta) && isset($pm_meta['_alg_wc_custom_order_number'][0]))?$pm_meta['_alg_wc_custom_order_number'][0]:'';
				
				/**/
				if(!empty($this->get_option('mw_wc_qbo_sync_compt_p_wconmkn'))){
					$wconmkn_key = $this->get_option('mw_wc_qbo_sync_compt_p_wconmkn');
					$pmd_tmp_arr[$wconmkn_key] = (is_array($pm_meta) && isset($pm_meta[$wconmkn_key][0]))?$pm_meta[$wconmkn_key][0]:'';
				}
				
				$qbo_payment_id = '';
				
				$pm_id_map_data = $this->get_row($wpdb->prepare("SELECT `qbo_payment_id` FROM {$wpdb->prefix}mw_wc_qbo_sync_payment_id_map WHERE `wc_payment_id` = %d AND `is_wc_order` = 0 ",$payment_id));
				
				if(is_array($pm_id_map_data) && isset($pm_id_map_data['qbo_payment_id'])){
					$qbo_payment_id = $pm_id_map_data['qbo_payment_id'];
				}
				
				$pmd_tmp_arr['qbo_payment_id'] = $qbo_payment_id;
				
				$r_data[] = $pmd_tmp_arr;
			}
		}
		
		unset($q_data);
		//$this->_p($r_data);
		return $r_data;
	}
	
	/**/
	public function count_refund_list($search_txt='',$date_from='',$date_to='',$status=''){
		$ext_whr = '';
		if($this->is_pl_res_tml()){
			//$ext_whr = " AND p.post_date BETWEEN NOW() - INTERVAL 30 DAY AND NOW() ";
			
			$wp_date_time_c = $this->now();
			$last_30_days_dt = date('Y-m-d H:i:s', strtotime('-'.$this->get_hd_ldys_lmt().' days', strtotime($wp_date_time_c)));
			$ext_whr = " AND p.post_date BETWEEN '{$last_30_days_dt}' AND '{$wp_date_time_c}' ";
		}
		
		global $wpdb;
		$sql = "
		SELECT COUNT(DISTINCT(p.ID))
		FROM
		{$wpdb->prefix}posts as p
		
		WHERE
		p.post_type = 'shop_order_refund'
		{$ext_whr}
		";
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$sql .=" AND ( p.ID = %d OR p.post_parent = %d ) ";
		}
		
		//
		$status = $this->sanitize($status);
		if($status!=''){
			$sql .=$wpdb->prepare(" AND p.post_status = %s",$status);
		}

		$date_from = $this->sanitize($date_from);
		if($date_from!=''){
			$sql .=" AND p.post_date>='".$date_from." 00:00:00'";
		}

		$date_to = $this->sanitize($date_to);
		if($date_to!=''){
			$sql .=" AND p.post_date<='".$date_to." 23:59:59'";
		}

		//$sql .='GROUP BY p.ID';

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt);
		}
		//echo $sql;
		return $wpdb->get_var($sql);
		
	}
	
	public function get_refund_list($search_txt='',$limit='',$date_from='',$date_to='',$status=''){
		$ext_whr = '';
		if($this->is_pl_res_tml()){
			//$ext_whr = " AND p.post_date BETWEEN NOW() - INTERVAL 30 DAY AND NOW() ";
			
			$wp_date_time_c = $this->now();
			$last_30_days_dt = date('Y-m-d H:i:s', strtotime('-'.$this->get_hd_ldys_lmt().' days', strtotime($wp_date_time_c)));
			$ext_whr = " AND p.post_date BETWEEN '{$last_30_days_dt}' AND '{$wp_date_time_c}' ";
		}
		
		global $wpdb;
		$sql = "
		SELECT DISTINCT(p.ID), p.post_status, p.post_date as refund_date,p.post_parent as order_id
		FROM
		{$wpdb->prefix}posts as p
		
		WHERE
		p.post_type = 'shop_order_refund'
		AND p.post_parent > 0
		{$ext_whr}
		";
		
		$search_txt = $this->sanitize($search_txt);
		if($search_txt!=''){
			$sql .=" AND ( p.ID = %d OR p.post_parent = %d ) ";
		}
		
		//
		$status = $this->sanitize($status);
		if($status!=''){
			$sql .=$wpdb->prepare(" AND p.post_status = %s",$status);
		}

		$date_from = $this->sanitize($date_from);
		if($date_from!=''){
			$sql .=" AND p.post_date>='".$date_from." 00:00:00'";
		}

		$date_to = $this->sanitize($date_to);
		if($date_to!=''){
			$sql .=" AND p.post_date<='".$date_to." 23:59:59'";
		}

		//$sql .='GROUP BY p.ID';
		
		$orderby = 'p.post_date DESC';
		$sql .= ' ORDER BY  '.$orderby;

		if($limit!=''){
			$sql .= ' LIMIT  '.$limit;
		}

		if($search_txt!=''){
			$sql = $wpdb->prepare($sql,$search_txt,$search_txt);
		}
		//echo $sql;
		return $this->get_data($sql);
		
	}
	
	/*Manage Invoice*/
	public function count_qbo_invoice_list($search_txt='',$date_from='',$date_to='',$qb_customer_id=0){
		if($this->is_connected() && !$this->is_plg_lc_p_l()){
			$qb_customer_id = (int) $qb_customer_id;
			if($qb_customer_id < 1){return 0;}
			
			$Context = $this->Context;
			$realm = $this->realm;
			$InvoiceService = new QuickBooks_IPP_Service_Invoice();

			$whr = '';
			if($qb_customer_id > 0){
				$whr.=" AND CustomerRef =  '$qb_customer_id' ";
			}
			
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND DocNumber LIKE '%$search_txt%' ";
			}			
			
			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				$whr .=" AND MetaData.CreateTime >='".$date_from."'";
			}
			
			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				$whr .=" AND MetaData.CreateTime <='".$date_to."'";
			}
			
			$sql = "SELECT COUNT(*)  FROM Invoice WHERE Id > '0' $whr ";
			//echo $sql;			
			$totalCount = $InvoiceService->query($Context, $realm, $sql);			
			return $totalCount;
		}
		return 0;
	}
	
	public function get_qbo_invoice_list($search_txt='',$limit='',$date_from='',$date_to='',$qb_customer_id=0){
		if($this->is_connected() && !$this->is_plg_lc_p_l()){
			$qb_customer_id = (int) $qb_customer_id;
			if($qb_customer_id < 1){return array();}
			
			$Context = $this->Context;
			$realm = $this->realm;

			$InvoiceService = new QuickBooks_IPP_Service_Invoice();

			$whr = '';
			if($qb_customer_id > 0){
				$whr.=" AND CustomerRef = '$qb_customer_id' ";
			}
			
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND DocNumber LIKE '%$search_txt%' ";
				//OR Not Supported
			}

			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				$whr .=" AND MetaData.CreateTime >='".$date_from."'";				
			}

			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				$whr .=" AND MetaData.CreateTime <='".$date_to."'";				
			}
			
			$sql = "SELECT * FROM Invoice WHERE Id > '0' $whr ORDER BY TxnDate DESC $limit ";
			//echo $sql;
			
			$items = $InvoiceService->query($Context, $realm, $sql);			
			return $items;
		}		
	}
	
	public function count_qbo_salesreceipt_list($search_txt='',$date_from='',$date_to='',$qb_customer_id=0){
		if($this->is_connected() && !$this->is_plg_lc_p_l()){
			$qb_customer_id = (int) $qb_customer_id;
			if($qb_customer_id < 1){return 0;}
			
			$Context = $this->Context;
			$realm = $this->realm;
			$SalesReceiptService = new QuickBooks_IPP_Service_SalesReceipt();

			$whr = '';
			if($qb_customer_id > 0){
				$whr.=" AND CustomerRef =  '$qb_customer_id' ";
			}
			
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND DocNumber LIKE '%$search_txt%' ";
			}			
			
			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				$whr .=" AND MetaData.CreateTime >='".$date_from."'";
			}
			
			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				$whr .=" AND MetaData.CreateTime <='".$date_to."'";
			}
			
			$sql = "SELECT COUNT(*)  FROM SalesReceipt WHERE Id > '0' $whr ";
			//echo $sql;			
			$totalCount = $SalesReceiptService->query($Context, $realm, $sql);			
			return $totalCount;
		}
		return 0;
	}
	
	public function get_qbo_salesreceipt_list($search_txt='',$limit='',$date_from='',$date_to='',$qb_customer_id=0){
		if($this->is_connected() && !$this->is_plg_lc_p_l()){
			$qb_customer_id = (int) $qb_customer_id;
			if($qb_customer_id < 1){return array();}
			
			$Context = $this->Context;
			$realm = $this->realm;

			$SalesReceiptService = new QuickBooks_IPP_Service_SalesReceipt();

			$whr = '';
			if($qb_customer_id > 0){
				$whr.=" AND CustomerRef = '$qb_customer_id' ";
			}
			
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND DocNumber LIKE '%$search_txt%' ";
				//OR Not Supported
			}

			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				$whr .=" AND MetaData.CreateTime >='".$date_from."'";				
			}

			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				$whr .=" AND MetaData.CreateTime <='".$date_to."'";				
			}
			
			$sql = "SELECT * FROM SalesReceipt WHERE Id > '0' $whr ORDER BY TxnDate DESC $limit ";
			//echo $sql;
			
			$items = $SalesReceiptService->query($Context, $realm, $sql);			
			return $items;
		}		
	}
	
	public function count_qbo_creditmemo_list($search_txt='',$date_from='',$date_to='',$qb_customer_id=0){
		if($this->is_connected() && !$this->is_plg_lc_p_l()){
			$qb_customer_id = (int) $qb_customer_id;
			if($qb_customer_id < 1){return 0;}
			
			$Context = $this->Context;
			$realm = $this->realm;
			$CreditMemoService = new QuickBooks_IPP_Service_CreditMemo();

			$whr = '';
			if($qb_customer_id > 0){
				$whr.=" AND CustomerRef =  '$qb_customer_id' ";
			}
			
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND DocNumber LIKE '%$search_txt%' ";
			}			
			
			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				$whr .=" AND MetaData.CreateTime >='".$date_from."'";
			}
			
			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				$whr .=" AND MetaData.CreateTime <='".$date_to."'";
			}
			
			$sql = "SELECT COUNT(*)  FROM CreditMemo WHERE Id > '0' $whr ";
			//echo $sql;			
			$totalCount = $CreditMemoService->query($Context, $realm, $sql);			
			return $totalCount;
		}
		return 0;
	}
	
	public function get_qbo_creditmemo_list($search_txt='',$limit='',$date_from='',$date_to='',$qb_customer_id=0){
		if($this->is_connected() && !$this->is_plg_lc_p_l()){
			$qb_customer_id = (int) $qb_customer_id;
			if($qb_customer_id < 1){return array();}
			
			$Context = $this->Context;
			$realm = $this->realm;

			$CreditMemoService = new QuickBooks_IPP_Service_CreditMemo();

			$whr = '';
			if($qb_customer_id > 0){
				$whr.=" AND CustomerRef = '$qb_customer_id' ";
			}
			
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND DocNumber LIKE '%$search_txt%' ";
				//OR Not Supported
			}

			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				$whr .=" AND MetaData.CreateTime >='".$date_from."'";				
			}

			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				$whr .=" AND MetaData.CreateTime <='".$date_to."'";				
			}
			
			$sql = "SELECT * FROM CreditMemo WHERE Id > '0' $whr ORDER BY TxnDate DESC $limit ";
			//echo $sql;
			
			$items = $CreditMemoService->query($Context, $realm, $sql);			
			return $items;
		}		
	}
	
	public function get_qb_customer_invoice_pdf($qb_customer_id,$qbo_inv_id,$type=''){
		if($this->is_connected() && !$this->is_plg_lc_p_l()){
			$qb_customer_id = (int) $qb_customer_id;
			$qbo_inv_id = (int) $qbo_inv_id;
			
			if($qb_customer_id > 0 && $qbo_inv_id > 0){
				$Context = $this->Context;
				$realm = $this->realm;
				
				//
				$cuc_mwc = current_user_can('manage_woocommerce');
				$pdf_ref = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
				if($cuc_mwc && strpos($pdf_ref, '/post.php?post=') !== false && strpos($pdf_ref, '&action=edit') !== false){
					$this->set_session_val('pdf_referer',$pdf_ref);
				}
				
				$s_pdf_ref = $this->get_session_val('pdf_referer');
				
				if($type == 'SalesReceipt'){
					$SalesReceiptService = new QuickBooks_IPP_Service_SalesReceipt();
					if($cuc_mwc && !empty($s_pdf_ref)){
						$sql = "SELECT * FROM SalesReceipt WHERE Id = '{$qbo_inv_id}' ";
					}else{
						$sql = "SELECT * FROM SalesReceipt WHERE Id = '{$qbo_inv_id}' AND CustomerRef = '$qb_customer_id' ";
					}
					
					$items = $SalesReceiptService->query($Context, $realm, $sql);
				}elseif($type == 'CreditMemo'){
					$CreditMemoService = new QuickBooks_IPP_Service_CreditMemo();
					if($cuc_mwc && !empty($s_pdf_ref)){
						$sql = "SELECT * FROM CreditMemo WHERE Id = '{$qbo_inv_id}' ";
					}else{
						$sql = "SELECT * FROM CreditMemo WHERE Id = '{$qbo_inv_id}' AND CustomerRef = '$qb_customer_id' ";
					}
					
					$items = $CreditMemoService->query($Context, $realm, $sql);
				}else{
					$InvoiceService = new QuickBooks_IPP_Service_Invoice();
					if($cuc_mwc && !empty($s_pdf_ref)){
						$sql = "SELECT * FROM Invoice WHERE Id = '{$qbo_inv_id}' ";
					}else{
						$sql = "SELECT * FROM Invoice WHERE Id = '{$qbo_inv_id}' AND CustomerRef = '$qb_customer_id' ";
					}
					
					$items = $InvoiceService->query($Context, $realm, $sql);					
				}
				
				
				if($items && is_array($items) && !empty($items)){
					if($type == 'SalesReceipt'){
						$inv_file_name = 'quickbooks_salesreceipt_'.$qbo_inv_id.'.pdf';
					}elseif($type == 'CreditMemo'){
						$inv_file_name = 'quickbooks_creditmemo_'.$qbo_inv_id.'.pdf';
					}else{
						$inv_file_name = 'quickbooks_invoice_'.$qbo_inv_id.'.pdf';
					}					
					
					/*
					header("Content-type: application/x-pdf");
					header("Content-Disposition: attachment; filename=$inv_file_name");
					*/
					
					header('Content-type: application/pdf');
					header("Content-Disposition: inline; filename=$inv_file_name");
					header('Content-Transfer-Encoding: binary');
					header('Accept-Ranges: bytes');
					
					if($type == 'SalesReceipt'){
						print $SalesReceiptService->pdf($Context, $realm, $qbo_inv_id);
					}elseif($type == 'CreditMemo'){
						print $CreditMemoService->pdf($Context, $realm, $qbo_inv_id);
					}else{
						print $InvoiceService->pdf($Context, $realm, $qbo_inv_id);
					}
					
					exit(0);
				}
			}
		}		
	}
	
	//Pull QuickBooks Inventory
	public function count_qbo_inventory_list($search_txt='',$date_from='',$date_to='',$show_all_product=false,$lut_df=false){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			$ItemService = new QuickBooks_IPP_Service_Term();

			$whr = '';
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND Name LIKE '%$search_txt%' ";
			}

			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				if($lut_df){
					$whr .=" AND MetaData.LastUpdatedTime >='".$date_from."'";
				}else{
					$whr .=" AND MetaData.CreateTime >='".$date_from."'";
				}
			}
			
			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				if($lut_df){
					$whr .=" AND MetaData.LastUpdatedTime <='".$date_to."'";
				}else{
					$whr .=" AND MetaData.CreateTime <='".$date_to."'";
				}
			}

			$type_whr = '';
			if(!$show_all_product){
				$type_whr.=" AND Type = 'Inventory' ";
			}else{
				if((string) $show_all_product=='category'){
					$type_whr.=" AND Type = 'Category' ";
				}else{
					$type_whr.=" AND Type IN ('Inventory','Service','NonInventory','Group') ";
				}
			}

			$sql = "SELECT COUNT(*)  FROM Item WHERE Id > '0' $type_whr $whr ";
			//echo $sql;
			$totalCount = $ItemService->query($Context, $realm, $sql);
			return $totalCount;
		}
	}

	public function get_qbo_inventory_list($search_txt='',$limit='',$date_from='',$date_to='',$show_all_product=false,$lut_df=false){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();

			$whr = '';
			$search_txt = $this->sanitize($search_txt);
			if($search_txt!=''){
				$whr.=" AND Name LIKE '%$search_txt%' ";
				//OR Not Supported
			}

			$date_from = $this->sanitize($date_from);
			if($date_from!=''){
				$date_from = date('c',strtotime($date_from.' 00:00:00'));
				if($lut_df){
					$whr .=" AND MetaData.LastUpdatedTime >='".$date_from."'";
				}else{
					$whr .=" AND MetaData.CreateTime >='".$date_from."'";
				}				
			}

			$date_to = $this->sanitize($date_to);
			if($date_to!=''){
				$date_to = date('c',strtotime($date_to.' 23:59:59'));
				if($lut_df){
					$whr .=" AND MetaData.LastUpdatedTime <='".$date_to."'";
				}else{
					$whr .=" AND MetaData.CreateTime <='".$date_to."'";
				}				
			}

			$type_whr = '';
			if(!$show_all_product){
				$type_whr.=" AND Type = 'Inventory' ";
			}else{
				if((string) $show_all_product == 'category'){
					$type_whr.=" AND Type = 'Category' ";
				}else{
					$type_whr.=" AND Type IN ('Inventory','Service','NonInventory','Group') ";
				}
			}

			$sql = "SELECT * FROM Item WHERE Id > '0' $type_whr $whr ORDER BY Id DESC $limit ";
			//echo $sql;

			$items = $ItemService->query($Context, $realm, $sql);
			return $items;
		}
	}

	public function wc_get_payment_details_by_txn_id($transaction_id='',$order_id=0){
		$order_id = (int) $order_id;
		$payment_row = array();
		$transaction_id = $this->sanitize($transaction_id);		
		
		global $wpdb;
		$whr = '';
		
		if($transaction_id!=''){
			$whr.=" AND pm8.meta_value='{$transaction_id}' ";
		}
		
		if($order_id){
			$whr.=$wpdb->prepare(" AND p.ID = %d ",$order_id);
		}
		
		$sql = "
		SELECT DISTINCT(p.ID) as order_id, p.post_status as order_status, p.post_date as order_date, pm1.meta_value as billing_first_name, pm2.meta_value as billing_last_name, pm3.meta_value as order_total, pm4.meta_value as order_key, pm5.meta_value as customer_user, pm6.meta_value as order_currency,
		pm8.meta_id as payment_id, pm8.meta_value as transaction_id, pm9.meta_value as paid_date, pm10.meta_value as payment_method, pm11.meta_value as payment_method_title, pm12.meta_value as stripe_txn_fee, pm13.meta_value as paypal_txn_fee
		FROM
		{$wpdb->prefix}posts as p
		LEFT JOIN ".$wpdb->postmeta." pm1
		ON ( pm1.post_id = p.ID AND pm1.meta_key =  '_billing_first_name' )
		LEFT JOIN ".$wpdb->postmeta." pm2
		ON ( pm2.post_id = p.ID AND pm2.meta_key =  '_billing_last_name' )
		LEFT JOIN ".$wpdb->postmeta." pm3
		ON ( pm3.post_id = p.ID AND pm3.meta_key =  '_order_total' )
		LEFT JOIN ".$wpdb->postmeta." pm4
		ON ( pm4.post_id = p.ID AND pm4.meta_key =  '_order_key' )
		LEFT JOIN ".$wpdb->postmeta." pm5
		ON ( pm5.post_id = p.ID AND pm5.meta_key =  '_customer_user' )
		LEFT JOIN ".$wpdb->postmeta." pm6
		ON ( pm6.post_id = p.ID AND pm6.meta_key =  '_order_currency' )
		LEFT JOIN ".$wpdb->postmeta." pm7
		ON ( pm7.post_id = p.ID AND pm7.meta_key =  '_billing_company' )
		INNER JOIN ".$wpdb->postmeta." pm8
		ON ( pm8.post_id = p.ID AND pm8.meta_key =  '_transaction_id' )
		INNER JOIN ".$wpdb->postmeta." pm9
		ON ( pm9.post_id = p.ID AND pm9.meta_key =  '_paid_date' )
		INNER JOIN ".$wpdb->postmeta." pm10
		ON ( pm10.post_id = p.ID AND pm10.meta_key =  '_payment_method' )
		LEFT JOIN ".$wpdb->postmeta." pm11
		ON ( pm11.post_id = p.ID AND pm11.meta_key =  '_payment_method_title' )
		
		LEFT JOIN ".$wpdb->postmeta." pm12
		ON ( pm12.post_id = p.ID AND pm10.meta_value = 'stripe' AND (pm12.meta_key =  'Stripe Fee' OR pm12.meta_key =  '_stripe_fee') )
		
		LEFT JOIN ".$wpdb->postmeta." pm13
		ON ( pm13.post_id = p.ID AND pm10.meta_value = 'paypal' AND pm13.meta_key =  'PayPal Transaction Fee' )

		WHERE
		p.post_type = 'shop_order'
		
		AND pm10.meta_value!=''
		AND pm8.meta_id > 0
		
		$whr
		";
		//AND pm9.meta_value!=''
		//AND pm8.meta_value!=''

		$payment_row = $this->get_row($sql);
		return $payment_row;
	}
	
	public function get_custom_post_list($post_type='post',$items_per_page=50,$search_txt='',$orderby='post_date',$order='desc',$post_status='publish',$meta_query=array()){

		$offset = $this->get_offset($this->get_page_var(),$items_per_page);

		$args = array(
			'posts_per_page'   => $items_per_page,
			'orderby'          => $orderby,
			'order'            => $order,
			'post_type'        => $post_type,
			'post_status'      => $post_status,
			'offset'          => $offset,
		);

		$search_txt = trim($search_txt);
		if($search_txt!=''){
			$args['s'] = $search_txt;
		}

		if(is_array($meta_query) && !empty($meta_query)){
			$args['meta_query'] = $meta_query;
		}

		$post_query_obj = new WP_Query( $args );
		$post_array = $post_query_obj->posts;

		$total_records = $post_query_obj->found_posts;
		wp_reset_query();
		//$this->_p($post_query_obj);
		$pagination_links = $this->get_paginate_links($total_records,$items_per_page);
		return array('post_array'=>$post_array, 'pagination_links'=>$pagination_links);
	}
	
	//
	public function quick_refresh_qbo_vendors(){
		if($this->is_connected()){
			global $wpdb;

			$Context = $this->Context;
			$realm = $this->realm;

			$VendorService = new QuickBooks_IPP_Service_Term();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $VendorService->query($Context, $realm, "SELECT COUNT(*)  FROM Vendor");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);

			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors';

			//if not truncate permission
			$wpdb->query("DELETE FROM `".$table."` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$table."` ");
			
			$total_vendor_added = 0;
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$vendors = $VendorService->query($Context, $realm, "SELECT * FROM Vendor STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($vendors && !empty($vendors)){
					$qrv_val_str = '';
					foreach($vendors as $Vendor){
						//$this->_p($Vendor);
						$vendor_id = $this->qbo_clear_braces($Vendor->getId());
						
						$save_data = array();

						$save_data['first'] = (string) $Vendor->getGivenName();
						$save_data['middle'] = (string) $Vendor->getMiddleName();
						$save_data['last'] = (string) $Vendor->getFamilyName();
						$save_data['company'] = (string) $Vendor->getCompanyName();
						$save_data['dname'] = (string) $Vendor->getDisplayName();
						
						$save_data['qbo_vendorid'] = $vendor_id;

						$email = ($Vendor->countPrimaryEmailAddr())?$Vendor->getPrimaryEmailAddr()->getAddress():'';
						
						$save_data['email'] = $email;
						$save_data['pocname'] = (string) $Vendor->getPrintOnCheckName();
						$save_data = array_map('trim', $save_data);
						//$save_data = array_map('addslashes', $save_data);
						$save_data = array_map(array($this, 'sanitize'), $save_data);
						
						//
						$save_data = array_map(array($this, 'htmlspecialchars_decode_c'), $save_data);
						$save_data = array_map(array($this, 'html_entity_decode_c'), $save_data);

						$qrv_val_str.=$wpdb->prepare("(%s,%s,%s,%s,%s,%d,%s,%s),",$save_data['first'],$save_data['middle'],$save_data['last'],$save_data['company'],$save_data['dname'],$save_data['qbo_vendorid'],$save_data['email'],$save_data['pocname']);
						
						$total_vendor_added++;

					}
					if($qrv_val_str!=''){
						$qrv_val_str = substr($qrv_val_str,0,-1);
						$qrv_insert_q = "INSERT INTO {$table} (".implode(", ", array_keys($save_data)).") VALUES {$qrv_val_str} ";
						//echo $qrv_insert_q;
						$wpdb->query($qrv_insert_q);
					}
				}
			}
			
			if($total_vendor_added>0){
				$this->clear_vendor_invalid_mappings();
			}
			
			return $total_vendor_added;
		}
	}

	public function quick_refresh_qbo_customers(){
		if($this->is_connected()){
			global $wpdb;

			$Context = $this->Context;
			$realm = $this->realm;

			$CustomerService = new QuickBooks_IPP_Service_Term();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $CustomerService->query($Context, $realm, "SELECT COUNT(*)  FROM Customer");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);

			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers';

			//if not truncate permission
			$wpdb->query("DELETE FROM `".$table."` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$table."` ");

			$total_customer_added = 0;
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$customers = $CustomerService->query($Context, $realm, "SELECT * FROM Customer STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($customers && !empty($customers)){
					$qrc_val_str = '';
					foreach($customers as $Customer){
						//$this->_p($Customer);
						$customer_id = $this->qbo_clear_braces($Customer->getId());

						$save_data = array();

						$save_data['first'] = (string) $Customer->getGivenName();
						$save_data['middle'] = (string) $Customer->getMiddleName();
						$save_data['last'] = (string) $Customer->getFamilyName();
						$save_data['company'] = (string) $Customer->getCompanyName();
						$save_data['dname'] = (string) $Customer->getDisplayName();

						$save_data['qbo_customerid'] = $customer_id;

						$email = ($Customer->countPrimaryEmailAddr())?$Customer->getPrimaryEmailAddr()->getAddress():'';

						/*
						if($email==''){
							continue;
						}
						*/

						//
						$save_data['email'] = $email;

						/*
						if($this->get_field_by_val($table,'id','email',$email)){
							$wpdb->update($table,$save_data,array('email'=>$email),'',array('%s'));
						}else{
							//
							$save_data['email'] = $email;
							$wpdb->insert($table, $save_data);
						}
						*/
						$save_data = array_map('trim', $save_data);
						$save_data = array_map('addslashes', $save_data);
						$save_data = array_map(array($this, 'sanitize'), $save_data);
						
						//$save_data = array_map(array($this, 'htmlspecialchars_decode_c'), $save_data);
						//$save_data = array_map(array($this, 'html_entity_decode_c'), $save_data);

						//$qrc_val_str.="('".$save_data['first']."','".$save_data['middle']."','".$save_data['last']."','".$save_data['company']."','".$save_data['dname']."',".$save_data['qbo_customerid'].",'".$save_data['email']."'),";

						$qrc_val_str.=$wpdb->prepare("(%s,%s,%s,%s,%s,%d,%s),",$save_data['first'],$save_data['middle'],$save_data['last'],$save_data['company'],$save_data['dname'],$save_data['qbo_customerid'],$save_data['email']);

						//$wpdb->insert($table, $save_data);
						$total_customer_added++;

					}
					if($qrc_val_str!=''){
						$qrc_val_str = substr($qrc_val_str,0,-1);
						$qrc_insert_q = "INSERT INTO {$table} (".implode(", ", array_keys($save_data)).") VALUES {$qrc_val_str} ";
						//echo $qrc_insert_q;
						$wpdb->query($qrc_insert_q);
					}
				}
			}
			
			if($total_customer_added>0){
				$this->clear_customer_invalid_mappings();
			}
			
			return $total_customer_added;
		}
	}

	public function quick_refresh_qbo_products(){
		if($this->is_connected()){
			global $wpdb;
			
			$up_check_disabled = false; //true if 'where' in query
			
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Term();
			$qboMaxLimit = $this->qbo_query_limit;
			/*
			$totalCount = $ItemService->query($Context, $realm, "SELECT COUNT(*) FROM Item WHERE Type IN ('Inventory','Service','NonInventory','Group') ");
			*/
			
			$totalCount = $ItemService->query($Context, $realm, "SELECT COUNT(*) FROM Item");
			
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);

			$table = $wpdb->prefix.'mw_wc_qbo_sync_qbo_items';
			
			//if not truncate permission
			$wpdb->query("DELETE FROM `".$table."` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$table."` ");
			$total_product_added = 0;
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				/*
				$items = $ItemService->query($Context, $realm, "SELECT * FROM Item WHERE Type IN ('Inventory','Service','NonInventory','Group') ORDER BY Name ASC STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				*/
				
				$items = $ItemService->query($Context, $realm, "SELECT * FROM Item ORDER BY Name ASC STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				
				if($items && !empty($items)){
					$qrp_val_str = '';
					foreach($items as $Item){
						if($up_check_disabled || $Item->countUnitPrice()){
							//$this->_p($Item);
							$item_id = $this->qbo_clear_braces($Item->getId());
							$item_name = $Item->getName();
							$sku = ($Item->countSku())?$Item->getSku():'';

							$product_type = $Item->getType();

							$save_data = array();
							$save_data['name'] = $item_name;
							$save_data['sku'] = $sku;
							$save_data['product_type'] = $product_type;
							//
							$save_data['itemid'] = $item_id;

							$save_data = array_map('trim', $save_data);
							$save_data = array_map('addslashes', $save_data);
							$save_data = array_map(array($this, 'sanitize'), $save_data);
							
							//$save_data = array_map(array($this, 'htmlspecialchars_decode_c'), $save_data);
							//$save_data = array_map(array($this, 'html_entity_decode_c'), $save_data);

							/*
							if($this->get_field_by_val($table,'id','itemid',$item_id)){
								$wpdb->update($table,$save_data,array('itemid'=>$item_id),'',array('%d'));
							}else{
								$save_data['itemid'] = $item_id;
								$wpdb->insert($table, $save_data);
							}
							*/
							//$qrp_val_str.="('".$save_data['name']."','".$save_data['sku']."','".$save_data['product_type']."',".$save_data['itemid']."),";

							$qrp_val_str.=$wpdb->prepare("(%s,%s,%s,%d),",$save_data['name'],$save_data['sku'],$save_data['product_type'],$save_data['itemid']);
							//$wpdb->insert($table, $save_data);
							$total_product_added++;
						}
					}
					if($qrp_val_str!=''){
						$qrp_val_str = substr($qrp_val_str,0,-1);
						$qrp_insert_q = "INSERT INTO {$table} (".implode(", ", array_keys($save_data)).") VALUES {$qrp_val_str} ";
						//echo $qrp_insert_q;
						$wpdb->query($qrp_insert_q);
					}
				}
			}
			
			if($total_product_added>0){
				$this->clear_product_invalid_mappings();
				$this->clear_variation_invalid_mappings();
			}
			
			return $total_product_added;
		}
	}

	//
	public function get_wc_tax_rate_dropdown($wc_tax_rates,$selected='',$skip_rate_id='',$skip_rate_class='None'){
		$options='<option value=""></option>';
		if(is_array($wc_tax_rates) && !empty($wc_tax_rates)){
			foreach($wc_tax_rates as $rates){
				if($skip_rate_id!=$rates['tax_rate_id'] && $skip_rate_class!=$rates['tax_rate_class']){
					//
					$options.='<option  data-tax_rate_country="'.$rates['tax_rate_country'].'"  data-tax_rate_state="'.$rates['tax_rate_state'].'"  data-tax_rate="'.$rates['tax_rate'].'"  data-tax_rate_name="'.$rates['tax_rate_name'].'"  data-tax_rate_priority="'.$rates['tax_rate_priority'].'"  data-tax_rate_compound="'.$rates['tax_rate_compound'].'"  data-tax_rate_shipping="'.$rates['tax_rate_shipping'].'" data-tax_rate_order="'.$rates['tax_rate_order'].'" data-tax_rate_class="'.$rates['tax_rate_class'].'" value="'.$rates['tax_rate_id'].'">'.$rates['tax_rate_name'].'</option>';
				}
			}
		}
		return $options;
	}

	public function get_wc_tax_rate_id_array($wc_tax_rates){
		$tx_rate_arr = array();
		if(is_array($wc_tax_rates) && !empty($wc_tax_rates)){
			foreach($wc_tax_rates as $rates){
				$tx_rate_arr[$rates['tax_rate_id']] = $rates;
			}
		}
		return $tx_rate_arr;
	}
	
	public function get_wc_tax_rates_a_lc_add($wc_tax_rates_a){
		$tx_rate_arr = array();
		if(is_array($wc_tax_rates_a) && !empty($wc_tax_rates_a)){
			global $wpdb;
			$wtr_lt = $wpdb->prefix.'woocommerce_tax_rate_locations';
			foreach($wc_tax_rates_a as $k => $rates){
				$tax_rate_id  = (int) $rates['tax_rate_id'];
				$lc_a = $this->get_row(" SELECT `location_code` FROM {$wtr_lt} WHERE `tax_rate_id` = {$tax_rate_id} AND location_type = 'city' LIMIT 0,1");
				$location_code = '';
				if(is_array($lc_a) && !empty($lc_a)){
					$location_code = $lc_a['location_code'];
				}
				
				$rates['location_code'] = $location_code;
				$tx_rate_arr[$k] = $rates;
			}
		}
		return $tx_rate_arr;
	}
	
	//28-03-2017
	public function wc_get_formated_qbo_display_name($firstname,$lastname,$company,$email,$wc_customerid=0,$d_name='',$billing_phone='',$username=''){
		$format = trim($this->get_option('mw_wc_qbo_sync_display_name_pattern'));
		if($format!=''){
			$format = str_replace('{phone_number}','{billing_phone}',$format);
			$s_arr = array('{firstname}','{lastname}','{companyname}','{email}');
			$r_arr = array($firstname,$lastname,$company,$email);
			$wc_customerid = (int) $wc_customerid;

			if($wc_customerid){
				$s_arr[] = '{id}';
				$r_arr[] = $wc_customerid;
			}
			
			//
			if(!empty($d_name)){
				$s_arr[] = '{display_name}';
				$r_arr[] = $d_name;
			}
			
			//
			if(!empty($billing_phone)){
				$s_arr[] = '{billing_phone}';
				$r_arr[] = $billing_phone;
			}

			if(!empty($username)){
				$s_arr[] = '{username}';
				$r_arr[] = $username;
			}
			
			$display_name = str_replace($s_arr,$r_arr,$format);
		}else{
			$display_name = $firstname." ".$lastname;
		}
		return $display_name;
	}

	//07-02-2017
	public function wc_get_display_name($customer_data,$guest=false){
		$display_name = '';
		$wc_customerid = 0;

		if($guest){
			$firstname = $this->get_array_isset($customer_data,'billing_first_name','',true);
			$lastname = $this->get_array_isset($customer_data,'billing_last_name','',true);
			$company = $this->get_array_isset($customer_data,'billing_company','',true);
			$email = $this->get_array_isset($customer_data,'billing_email','',true);
		}else{
			$firstname = $this->get_array_isset($customer_data,'firstname','',true);
			$lastname = $this->get_array_isset($customer_data,'lastname','',true);
			$company = $this->get_array_isset($customer_data,'company','',true);
			$email = $this->get_array_isset($customer_data,'email','',true);

			$wc_customerid = $this->get_array_isset($customer_data,'wc_customerid',0);
		}
		
		$d_name = $this->get_array_isset($customer_data,'display_name','',true);
		
		$shipping_company = $this->get_array_isset($customer_data,'shipping_company','',true);
		$billing_company = $this->get_array_isset($customer_data,'billing_company','',true);
		
		$billing_phone = $this->get_array_isset($customer_data,'billing_phone','',true);

		$username = '';
		if(!$guest && $wc_customerid > 0){
			if(isset($customer_data['username'])){
				$username = $this->get_array_isset($customer_data,'username','',true);
			}else{
				global $wpdb;
				$username = $this->get_field_by_val($wpdb->users,'user_login','ID',$wc_customerid);
			}
		}
		
		$fl_name = $firstname.' '.$lastname;

		# New
		if(!$guest && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr') && !isset($customer_data['values_from_order_meta']) && isset($customer_data['order_id'])){
			$o_shipping_company = get_post_meta($customer_data['order_id'],'_shipping_company',true);
			if(!empty($o_shipping_company)){
				$shipping_company = $o_shipping_company;
			}
		}

		if(!$guest && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company') && !isset($customer_data['values_from_order_meta']) && isset($customer_data['order_id'])){
			$o_billing_company = get_post_meta($customer_data['order_id'],'_billing_company',true);
			if(!empty($o_billing_company)){
				$billing_company = $o_billing_company;
			}
		}

		if(!$guest && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name') && isset($customer_data['order_id'])){
			if(isset($customer_data['values_from_order_meta'])){
				$obfn = $this->get_array_isset($customer_data,'billing_first_name','',true);
				$obln = $this->get_array_isset($customer_data,'billing_last_name','',true);
			}else{
				$obfn = get_post_meta($customer_data['order_id'],'_billing_first_name',true);
				$obln = get_post_meta($customer_data['order_id'],'_billing_last_name',true);
			}

			if(!empty($obfn) || !empty($obln)){
				$fl_name = $obfn.' '.$obln;
			}
		}
		
		//$display_name = $firstname." ".$lastname;
		if($shipping_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr')){
			$display_name = $shipping_company;
		}elseif($billing_company!='' && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company')){
			$display_name = $billing_company;
		}elseif(!empty($fl_name) && $this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
			$display_name = $fl_name;
		}else{
			$display_name = $this->wc_get_formated_qbo_display_name($firstname,$lastname,$company,$email,$wc_customerid,$d_name,$billing_phone,$username);
		}
		
		//		
		if(trim($display_name)==''){
			$display_name = $firstname." ".$lastname;
		}
		
		if(trim($display_name)==''){
			$display_name = $this->get_array_isset($customer_data,'display_name','',true);
		}
		if(trim($display_name)==''){
			$display_name = $email;
		}
		return $display_name;
	}
	
	//
	public function check_sh_cfm_hash(){
		if($this->is_plg_lc_p_l() || $this->is_plg_lc_p_r()){
			return false;
		}
		return true;
		/*
		$sh_cfm_h = $this->get_option('mw_wc_qbo_sync_sh_cfm_hash');
		$ch_hash = sha1('w9%Ctq' . 'v=*]p?f+UK,#L4]9');
		if($sh_cfm_h==$ch_hash){
			return true;
		}
		return false;
		*/
	}

	public function get_country_name_from_code($code=''){
		if($code!=''){
			 $countries_obj   = new WC_Countries();
			 $countries   = $countries_obj->__get('countries');
			 if(is_array($countries) && isset($countries[$code])){
				 //
				 if($countries[$code] == 'United States (US)'){
					return 'United States';
				 }
				 return $countries[$code];
			 }
		}
		return $code;
	}

	//07-03-2017
	public function get_dashboard_status_items(){
		$items = array();
		global $wpdb;

		$quickbooks_connection = ($this->get_option('mw_wc_qbo_sync_qbo_is_connected'))?true:false;
		$initial_quickbooks_data_loaded = $this->option_checked('mw_wc_qbo_sync_qbo_is_refreshed');
		$default_setting_saved = $this->option_checked('mw_wc_qbo_sync_qbo_is_default_settings');
		$mapping_active = $this->option_checked('mw_wc_qbo_sync_qbo_is_data_mapped');

		$items['quickbooks_connection'] = $quickbooks_connection;
		$items['initial_quickbooks_data_loaded'] = $initial_quickbooks_data_loaded;
		$items['default_setting_saved'] = $default_setting_saved;
		$items['mapping_active'] = $mapping_active;

		$customer_mapped = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_customer_pairs WHERE `qbo_customerid` > 0 ");
		$items['customer_mapped'] = $customer_mapped;

		$product_mapped = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_product_pairs WHERE `quickbook_product_id` > 0 ");
		$items['product_mapped'] = $product_mapped;
		
		$variation_mapped = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_variation_pairs WHERE `quickbook_product_id` > 0 ");
		$items['variation_mapped'] = $variation_mapped;

		$gateway_mapped = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_paymentmethod_map WHERE `qbo_account_id` > 0 ");
		$items['gateway_mapped'] = $gateway_mapped;

		$tax_mapped = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_tax_map WHERE `qbo_tax_code` !='' ");
		$items['tax_mapped'] = $tax_mapped;

		//from log table
		$customer_synced = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_log WHERE `log_type` = 'Customer' AND `success` = 1 ");
		$items['customer_synced'] = $customer_synced;

		$order_synced = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_log WHERE `log_type` = 'Invoice' AND `success` = 1 ");
		$items['order_synced'] = $order_synced;

		$product_synced = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_log WHERE `log_type` = 'Product' AND `success` = 1 ");
		$items['product_synced'] = $product_synced;

		$error = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_log WHERE `success` = 0 ");
		$items['error'] = $error;
		
		//woocommerce stats
		$wc_total_customer = (int) $this->count_customers();
		$wc_total_product = (int) $this->count_woocommerce_product_list();
		$wc_total_variation = (int) $this->count_woocommerce_variation_list();
		
		$wc_p_methods = array();
		$available_gateways = WC()->payment_gateways()->payment_gateways;
		if(is_array($available_gateways) && !empty($available_gateways)){
			foreach($available_gateways as $key=>$value){
				if($value->enabled=='yes'){
					$wc_p_methods[$value->id] = $value->title;
				}		
			}
		}
		
		$wc_total_gateway = count($wc_p_methods);
		
		$items['wc_total_customer'] = $wc_total_customer;
		$items['wc_total_product'] = $wc_total_product;
		$items['wc_total_variation'] = $wc_total_variation;
		$items['wc_total_gateway'] = $wc_total_gateway;
		

		return $items;
	}

	//28-03-2017
	public function send_daily_email_log(){
		if(!$this->option_checked('mw_wc_qbo_sync_email_log')){
			return false;
		}
		global $wpdb;
		$to = '';

		$w_admin_id = (int) $this->get_option('mw_wc_qbo_sync_admin_email');
		if($w_admin_id){
			$to = (string) $this->get_admin_email_by_id($w_admin_id);
		}

		if($to==''){return false;}

		$l_date_whr = " `added_date` > DATE_SUB(CURDATE(), INTERVAL 1 DAY) ";
		$log_q = "SELECT * FROM `{$wpdb->prefix}mw_wc_qbo_sync_log` WHERE {$l_date_whr} ";
		$log_data = $this->get_data($log_q);
		$log_email_html = '';

		if(is_array($log_data) && !empty($log_data)){
			//06-07-2017
			$success_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_log WHERE `success` = 1 AND {$l_date_whr} ");
			$error_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_log WHERE `success` = 0 AND {$l_date_whr} ");
			$other_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mw_wc_qbo_sync_log WHERE `success` > 1 AND {$l_date_whr} ");

			$total_summary = '<b>Success Total: </b>'.$success_count.'<br />';
			$total_summary.= '<b>Errors Total: </b>'.$error_count.'<br />';
			$total_summary.= '<b>Others Total: </b>'.$other_count.'<br /><br />';

			$border_color = 'lightgrey';
			$log_email_html = '
			<h1>MyWorks WooCommerce Sync for QuickBooks Online</h1>
			<p>Last 24 hours log list ('.$this->now().')</p>
			'.$total_summary.'
			<table style="width:100%;border-top:1px solid '.$border_color.';border-right:1px solid '.$border_color.';font-size:14px;" cellpadding="0" cellspacing="0">
			<tr>
				<td width="5%" align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">#</td>
				<td width="10%" align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">Type</td>
				<td width="25%" align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">Title</td>
				<td width="44%" align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">Message</td>
				<td width="16%" align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">Date</td>
			</tr>
			';
			foreach($log_data as $ld){
				$log_color = ($ld['success']==0)?'color:red;':'';
				$log_email_html.='
				<tr>
					<td align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">'.$ld['id'].'</td>
					<td align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">'.$ld['log_type'].'</td>
					<td align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;'.$log_color.'">'.$ld['log_title'].'</td>
					<td align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;'.$log_color.'">'.$ld['details'].'</td>
					<td align="left" style="border-bottom:1px solid '.$border_color.';border-left:1px solid '.$border_color.';padding:2px;">'.$ld['added_date'].'</td>
				</tr>
				';
			}
			$log_email_html.='</table>';

			$headers = array(
				'MIME-Version: 1.0',
				'Content-type:text/html;charset=UTF-8',
			);
			//echo $log_email_html;return;
			wp_mail($to, 'Daily Email Log', $log_email_html, $headers);
		}
	}

	//31-03-2017
	public function get_current_request_protocol(){
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			 return $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}
		return (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='OFF') ? "https" : "http";
	}

	public function get_sync_window_url(){
		//$this->_p($_SERVER);
		$request_protocol = $this->get_current_request_protocol();

		$current_url = $request_protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
		$sync_window_url = site_url('index.php?mw_qbo_sync_public_sync_window=1');

		if(strpos($current_url, 's://')===false){
			$sync_window_url = str_replace('s://','://',$sync_window_url);
		}else{
			if(strpos($sync_window_url, 's://')===false){
				$sync_window_url = str_replace('://','s://',$sync_window_url);
			}
		}

		if(strpos($current_url, '://www.')===false){
			$sync_window_url = str_replace('://www.','://',$sync_window_url);
		}else{
			if(strpos($sync_window_url, '://www.')===false){
				$sync_window_url = str_replace('://','://www.',$sync_window_url);
			}
		}

		return $sync_window_url;
	}

	//10-04-2017
	public function get_order_currency_list(){
		global $wpdb;

		$gc_length = 2048;
		$wpdb->query("SET group_concat_max_len = {$gc_length}");

		$cur_list = $this->get_var("SELECT GROUP_CONCAT(DISTINCT(`meta_value`)) AS currency_list FROM {$wpdb->postmeta} WHERE `meta_key` = '_order_currency' AND `meta_key` != '' ");
		if($cur_list==''){
			$cur_list = get_woocommerce_currency();
		}

		if(is_array($cur_list)){
			return $cur_list;
		}

		if($cur_list!=''){
			return explode(',',$cur_list);
		}
	}

	public function get_world_currency_list($symbol=false){
		if($symbol){			
			$symbols = get_woocommerce_currency_symbols();
			return $symbols;
		}		

		$cur_arr = get_woocommerce_currencies();
		if($symbol=='name'){
			return $cur_arr;
		}
		return array_combine(array_keys($cur_arr),array_keys($cur_arr));
	}
	
	//18-05-2017
	public function get_compt_checkout_fields($s_f_name = ''){
		global $wpdb;
		$s_whr = '';
		if($s_f_name!=''){
			$s_whr = " AND pm1.meta_value = %s ";
		}

		$sql="
		SELECT p.ID, pm1.meta_value as cf_label, pm2.meta_value as cf_key
		FROM `{$wpdb->posts}` p
		LEFT JOIN `{$wpdb->postmeta}` pm1
		ON(p.ID = pm1.post_id AND pm1.meta_key='label')

		LEFT JOIN `{$wpdb->postmeta}` pm2
		ON(p.ID = pm2.post_id AND pm2.meta_key='key')

		WHERE p.`post_type` = 'wccf_checkout_field'
		{$s_whr}
		AND p.post_status NOT IN('trash','auto-draft','inherit')
		";

		if($s_f_name!=''){
			$sql.=' LIMIT 0,1 ';
			$sql = $wpdb->prepare($sql,$s_f_name);
		}

		//echo $sql;
		if($s_f_name!=''){
			return $this->get_row($sql);
		}
		return $this->get_data($sql);
	}
	
	/**/
	public function get_wc_fee_product_class_ref($qbo_product_id){
		$fp_cr = '';
		if(!empty($qbo_product_id)){
			if($this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
				if(!empty($this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class'))){
					$fp_cr = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
				}
				
				##
				if($this->option_checked('mw_wc_qbo_sync_compt_np_oli_fee_sync')){
					if(!empty($this->get_option('mw_wc_qbo_sync_compt_np_oli_fee_qb_class'))){
						$fp_cr = $this->get_option('mw_wc_qbo_sync_compt_np_oli_fee_qb_class');
					}
				}				
			}
		}
		return $fp_cr;
	}
	
	public function get_wc_fee_qbo_product($dfn='',$efd='',$invoice_data=array()){
		$fee_qp = 0;
		
		/*
		$isdf = false;
		if($this->is_plugin_active('woocommerce-gateways-discounts-and-fees') && $this->option_checked('mw_wc_qbo_sync_compt_gf_qbo_is')){
			$isdf = true;
		}

		if($this->is_plugin_active('woocommerce-additional-fees','woocommerce_additional_fees_plugin') && $this->option_checked('mw_wc_qbo_sync_compt_gf_qbo_is_gbf')){
			$isdf = true;
		}		
		
		if($this->is_plugin_active('rp-woo-donation','index') && $this->option_checked('mw_wc_qbo_sync_compt_wdotocac_fee_li_ed')){
			$isdf = true;
		}
		
		if($this->is_plugin_active('woocommerce-conditional-product-fees-for-checkout') && $this->option_checked('mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed')){
			$isdf = true;
		}
		
		if($this->is_plugin_active('woo-add-custom-fee','woo-add-custom-fee.php') && $this->option_checked('mw_wc_qbo_sync_compt_woacfp_fee_li_ed')){
			$isdf = true;
		}
		*/
		
		//
		#$isdf = true;
		$isdf = $this->option_checked('mw_wc_qbo_sync_compt_np_oli_fee_sync');
		
		if($isdf){
			$fee_qp = (int) $this->get_option('mw_wc_qbo_sync_compt_gf_qbo_item');
		}
		
		if($this->is_plugin_active('woocommerce-custom-fields') && $this->option_checked('mw_wc_qbo_sync_compt_wccf_fee')){
			$fee_qp = 0;
			if($dfn!=''){
				$ccf_data = $this->get_compt_checkout_fields($dfn);
				if(is_array($ccf_data) && !empty($ccf_data)){
					$ccf_id = (int) $ccf_data['ID'];
					if($ccf_id){
						$mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map = $this->get_option('mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map');
						if($mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map!=''){
							$ccf_map_arr = unserialize($mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map);
							if(is_array($ccf_map_arr) && !empty($ccf_map_arr)){
								if(isset($ccf_map_arr[$ccf_id]) && (int) $ccf_map_arr[$ccf_id]){
									$fee_qp = (int) $ccf_map_arr[$ccf_id];
								}
							}
						}
					}
				}
			}

		}

		//04-07-2017
		if($this->is_plugin_active('woocommerce-checkout-field-editor-pro') && $this->option_checked('mw_wc_qbo_sync_wcfep_add_fld')){
			$fee_qp = 0;
			if($dfn!=''){
				$thwcfe_sections = get_option('thwcfe_sections');
				if(is_array($thwcfe_sections) && !empty($thwcfe_sections) && isset($thwcfe_sections['additional']) && !empty($thwcfe_sections['additional']) && isset($thwcfe_sections['additional']->fields) && !empty($thwcfe_sections['additional']->fields)){
					$thwcfe_sections_add = $thwcfe_sections['additional']->fields;
					$mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map = $this->get_option('mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map');
					if($mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map!=''){
						$wcfep_map_arr = unserialize($mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map);
						if(is_array($wcfep_map_arr) && !empty($wcfep_map_arr)){
							$wcfep_add_f_name = '';
							foreach($thwcfe_sections_add as $thwcfe_add){
								if($thwcfe_add->price_field==1){
									if($this->start_with($dfn,$thwcfe_add->title)){
										if(is_array($invoice_data) && isset($invoice_data[$thwcfe_add->id])){
											$wcfep_add_f_name = $thwcfe_add->id;
											break;
										}
									}
								}
							}

							if($wcfep_add_f_name!=''){
								if(isset($wcfep_map_arr[$wcfep_add_f_name]) && (int) $wcfep_map_arr[$wcfep_add_f_name]){
									$fee_qp = (int) $wcfep_map_arr[$wcfep_add_f_name];
								}
							}
						}
					}
				}
			}
		}
		
		//
		if(!$fee_qp && $isdf){
			$fee_qp = (int) $this->get_option('mw_wc_qbo_sync_compt_gf_qbo_item');
		}
		
		if(!$fee_qp){
			$fee_qp = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
		}
		return $fee_qp;
	}
	
	public function get_wc_fee_plugin_check(){
		return true;
		
		/*
		if($this->option_checked('mw_wc_qbo_sync_compt_np_oli_fee_sync')){
			return true;
		}
		
		return false;
		*/
		
		/*
		$enabled = false;
		if($this->is_plugin_active('woocommerce-gateways-discounts-and-fees') && $this->option_checked('mw_wc_qbo_sync_compt_gf_qbo_is')){
			$enabled = true;
		}

		if($this->is_plugin_active('woocommerce-additional-fees','woocommerce_additional_fees_plugin') && $this->option_checked('mw_wc_qbo_sync_compt_gf_qbo_is_gbf')){
			$enabled = true;
		}

		if($this->is_plugin_active('woocommerce-custom-fields') && $this->option_checked('mw_wc_qbo_sync_compt_wccf_fee')){
			$enabled = true;
		}
		
		if($this->is_plugin_active('woocommerce-checkout-field-editor-pro') && $this->option_checked('mw_wc_qbo_sync_wcfep_add_fld')){
			$enabled = true;
		}		
		
		if($this->is_plugin_active('rp-woo-donation','index') && $this->option_checked('mw_wc_qbo_sync_compt_wdotocac_fee_li_ed')){
			$enabled = true;
		}
		
		if($this->is_plugin_active('woocommerce-conditional-product-fees-for-checkout') && $this->option_checked('mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed')){
			$enabled = true;
		}		
		
		if($this->is_plugin_active('woo-add-custom-fee','woo-add-custom-fee.php') && $this->option_checked('mw_wc_qbo_sync_compt_woacfp_fee_li_ed')){
			$enabled = true;
		}
		
		return $enabled;
		*/
	}
	
	private function get_local_key_results(){
		$localkeyresults = array();
		$localkey = $this->get_option('mw_wc_qbo_sync_localkey');
		if(!empty($localkey)){
			$localkey = str_replace("\n", '', $localkey);
			$localdata = substr($localkey, 0, strlen($localkey) - 32);
			$localdata = strrev($localdata);
			$localdata = substr($localdata, 32);
			$localdata = @base64_decode($localdata);
			$localkeyresults = @unserialize($localdata);
		}
		return $localkeyresults;
	}
	
	private function get_plg_lc_plan(){
		$pln = '';
		$lkr = $this->get_local_key_results();
		if(is_array($lkr) && !empty($lkr) && isset($lkr[base64_decode('cHJvZHVjdG5hbWU=')]) && !empty($lkr[base64_decode('cHJvZHVjdG5hbWU=')])){
			if(strpos($lkr[base64_decode('cHJvZHVjdG5hbWU=')],base64_decode('TGF1bmNo'))!==false){
				$pln = base64_decode('TGF1bmNo');
			}
			
			if(strpos($lkr[base64_decode('cHJvZHVjdG5hbWU=')],base64_decode('UmlzZQ=='))!==false){
				$pln = base64_decode('UmlzZQ==');
			}
			
			if(strpos($lkr[base64_decode('cHJvZHVjdG5hbWU=')],base64_decode('R3Jvdw=='))!==false){
				$pln = base64_decode('R3Jvdw==');
			}
			
			if(strpos($lkr[base64_decode('cHJvZHVjdG5hbWU=')],base64_decode('U2NhbGU='))!==false){
				$pln = base64_decode('U2NhbGU=');
			}

			if(strpos($lkr[base64_decode('cHJvZHVjdG5hbWU=')],base64_decode('U29hcg=='))!==false){
				$pln = base64_decode('U29hcg==');
			}
		}
		return $pln;
	}
	
	public function is_plg_lc_p_l($fs=null){				
		if(!is_null($fs)){return $fs;}
		//return false;
		//return true;
		if($this->get_plg_lc_plan() == base64_decode('TGF1bmNo')){
			return true;
		}
		return false;
	}
	
	public function is_plg_lc_p_r(){
		if($this->get_plg_lc_plan() == base64_decode('UmlzZQ==')){
			return true;
		}
		return false;
	}
	
	public function is_plg_lc_p_g(){
		if($this->get_plg_lc_plan() == base64_decode('R3Jvdw==')){
			return true;
		}
		return false;
	}
	
	public function is_plg_lc_p_s(){
		if($this->get_plg_lc_plan() == base64_decode('U2NhbGU=')){
			return true;
		}
		return false;
	}

	public function is_plg_lc_p_sr(){
		if($this->get_plg_lc_plan() == base64_decode('U29hcg==')){
			return true;
		}
		return false;
	}
	
	private function get_addons_frm_local_key(){
		$addons = '';
		$localkeyresults = $this->get_local_key_results();
		if(is_array($localkeyresults) && !empty($localkeyresults)){
			$addons = (isset($localkeyresults['addons']))?$localkeyresults['addons']:'';
		}
		return $addons;
	}
	
	public function get_compt_plugin_license_addons_arr(){
		$addons = $this->get_addons_frm_local_key();
		$ar_data = array();
		if($addons!=''){
			$addon_arr = explode('|',$addons);
			foreach($addon_arr as $ar){
				$ar_r = str_replace(';','&',$ar);
				$ar_r_a = array();
				parse_str($ar_r,$ar_r_a);
				if(is_array($ar_r_a) && !empty($ar_r_a)){
					$ar_data[] = $ar_r_a;
				}
			}
		}
		
		//$this->_p($ar_data);
		return $ar_data;
	}
	
	public function get_cmt_pl_nm_by_pl_dr_fn($p_dir,$p_fn){
		$pl_name = '';
		if(!empty($p_dir) && !empty($p_fn)){
			$s_pl_n_arr = array();
			$s_pl_n_arr['np_custom_order_number'] = 'Custom Order Number';
			
			//Static WC Plugin Compatibility Addon Name Map
			//$s_pl_n_arr['woocommerce-measurement-price-calculator'] = 'Measurement Price Calculator';
			//$s_pl_n_arr['woocommerce-deposits'] = 'WooCommerce Deposits';
			//$s_pl_n_arr['woocommerce-product-bundles'] = 'Product Bundles';
			//$s_pl_n_arr['woocommerce-gateways-discounts-and-fees'] = '';
			//$s_pl_n_arr['woocommerce-additional-fees'] = 'Payment Gateway Based Fees';
			//$s_pl_n_arr['woocommerce-order-delivery'] = 'WooCommerce Order Delivery';
			//$s_pl_n_arr['woocommerce-sequential-order-numbers-pro'] = 'Sequential Order Numbers Pro';
			//$s_pl_n_arr['woocommerce-custom-fields'] = 'WooCommerce Custom Fields';
			$s_pl_n_arr['woocommerce-checkout-field-editor'] = 'Checkout Field Editor';
			$s_pl_n_arr['woocommerce-checkout-field-editor-pro'] = 'Checkout Field Editor Pro';
			//$s_pl_n_arr['woocommerce-hear-about-us'] = 'Hear About Us';
			//$s_pl_n_arr['woocommerce-admin-custom-order-fields'] = 'Admin Custom Order Fields';
			//$s_pl_n_arr['woocommerce-shipment-tracking'] = 'Shipment Tracking';
			$s_pl_n_arr['woocommerce-cost-of-goods'] = 'Cost of Goods Sold';
			$s_pl_n_arr['woocommerce-avatax'] = 'Avalara Avatax';
			//$s_pl_n_arr['taxify-for-woocommerce'] = 'Taxify for WooCommerce';
			//$s_pl_n_arr['woocommerce-tm-extra-product-options'] = 'TM Extra Product Options';
			//$s_pl_n_arr['woocommerce-product-addons'] = 'WooCommerce Product Add-ons';
			//$s_pl_n_arr['woocommerce-appointments'] = 'WooCommerce Appointments';
			
			//$s_pl_n_arr['woocommerce-subscriptions'] = 'WooCommerce Subscriptions';
			
			if(isset($s_pl_n_arr[$p_dir]) && !empty($s_pl_n_arr[$p_dir])){
				$pl_name = $s_pl_n_arr[$p_dir];
			}else{
				if($this->is_only_plugin_active($p_dir,$p_fn)){
					$pl_fpath = WP_PLUGIN_DIR . '/'.$p_dir.'/'.$p_fn.'.php';
					if( !function_exists('get_plugin_data') ){
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					}
					
					$plugin_data = get_plugin_data( $pl_fpath, false, false );					
					//$this->_p($pl_fpath);
					//$this->_p($plugin_data);
					if(is_array($plugin_data)){
						if(isset($plugin_data['Name'])){
							$pl_name = $plugin_data['Name'];
						}
					}
				}				
			}
		}
		return $pl_name;
	}
	
	public function chk_compt_addons_active($p_dir,$p_fn){
		if(!empty($p_dir) && !empty($p_fn)){
			$pl_name = $this->get_cmt_pl_nm_by_pl_dr_fn($p_dir,$p_fn);
			//$this->_p($pl_name);			
			if(!empty($pl_name)){
				$addons_arr = $this->get_compt_plugin_license_addons_arr();
				//$this->_p($addons_arr);
				if(is_array($addons_arr) && !empty($addons_arr)){
					foreach($addons_arr as $aa_d){
						if(isset($aa_d['name']) && !empty($aa_d['name'])){
							$t_date = $this->now('Y-m-d');
							$addon_name = $this->get_array_isset($aa_d,'name','',true);
							$addon_nextduedate = $this->get_array_isset($aa_d,'nextduedate','',true);
							$addon_status = $this->get_array_isset($aa_d,'status','',true);
							$c_addon_name = $pl_name.' (QBO)';
							if($c_addon_name == $addon_name || strpos($c_addon_name,$addon_name)!==false){
								if($addon_status == 'Active'){
									return true;
								}
							}
						}						
					}					
				}
			}			
		}		
		return false;
	}
	
	//
	public function is_only_plugin_active($plugin='',$diff_filename=''){
		if($plugin == 'myworks-qbo-sync-custom-field-mapping'){
			if($this->is_plg_lc_p_l() || $this->is_plg_lc_p_r()){
				return false;
			}
			return true;
		}
		
		if($plugin == 'woocommerce-deposits'){$diff_filename = '';}
		
		$active = false;
		$plugin = trim($plugin);
		$diff_filename = trim($diff_filename);
		$plugin_file = ($diff_filename!='')?$diff_filename:$plugin;
		
		if($plugin!=''){
			if(function_exists('is_plugin_active')){
				if( is_plugin_active( $plugin.'/'.$plugin_file.'.php' ) ) {
					$active = true;
				}				
			}else{
				$active = in_array( $plugin.'/'.$plugin_file.'.php', (array) get_option( 'active_plugins', array() ) );				
			}
		}
		
		return $active;
	}
	
	public function is_plugin_active($plugin='',$diff_filename='',$fc=false){
		/**/
		if($plugin == 'myworks-qbo-sync-custom-field-mapping'){
			if($this->is_plg_lc_p_l() || $this->is_plg_lc_p_r()){
				return false;
			}
			return true;
		}

		if($plugin == 'myworks-qbo-sync-compatibility' && !$fc){
			return true;
		}
		
		if($plugin == 'woocommerce-deposits'){$diff_filename = '';}
		
		$active = false;
		$plugin = trim($plugin);
		$diff_filename = trim($diff_filename);
		$plugin_file = ($diff_filename!='')?$diff_filename:$plugin;		
		
		$compt_addon_active = false;
		if($plugin!=''){
			//require_once(ABSPATH.'wp-admin/includes/plugin.php');
			if(function_exists('is_plugin_active')){				
				if( is_plugin_active( $plugin.'/'.$plugin_file.'.php' ) ) {
					$active = true;
				}
				/**/
				if(!$active && $plugin == 'woocommerce-sequential-order-numbers-pro' && $diff_filename == 'woocommerce-sequential-order-numbers'){
					if( is_plugin_active( $plugin_file.'/'.$plugin_file.'.php' ) ) {
						$active = true;
					}
				}
				
				/**/
				if(!$active && $plugin == 'visual-product-configurator'){
					$plugin = 'visual-products-configurator';
					if( is_plugin_active( $plugin.'/'.$plugin_file.'.php' ) ) {
						$active = true;
					}
					
					if(!$active){
						$plugin = 'Visual-products-configurator';
						if( is_plugin_active( $plugin.'/'.$plugin_file.'.php' ) ) {
							$active = true;
						}
					}
				}
				
				/**/
				if(!$active && $plugin == 'pw-woocommerce-gift-cards' && $diff_filename == 'pw-gift-cards'){
					if( is_plugin_active( $diff_filename.'/'.$diff_filename.'.php' ) ) {
						$active = true;
					}
				}
				
				/*
				if( is_plugin_active( 'myworks-qbo-sync-compatibility/myworks-qbo-sync-compatibility.php' ) ) {
					$compt_addon_active = true;
				}
				*/
			}else{				
				$active = in_array( $plugin.'/'.$plugin_file.'.php', (array) get_option( 'active_plugins', array() ) );
				/**/
				if(!$active && $plugin == 'woocommerce-sequential-order-numbers-pro' && $diff_filename == 'woocommerce-sequential-order-numbers'){
					$active = in_array( $plugin_file.'/'.$plugin_file.'.php', (array) get_option( 'active_plugins', array() ) );
				}
				
				/**/
				if(!$active && $plugin == 'visual-product-configurator'){
					$plugin = 'visual-products-configurator';
					$active = in_array( $plugin.'/'.$plugin_file.'.php', (array) get_option( 'active_plugins', array() ) );
					
					if(!$active){
						$plugin = 'Visual-products-configurator';
						$active = in_array( $plugin.'/'.$plugin_file.'.php', (array) get_option( 'active_plugins', array() ) );
					}
				}
				
				/**/
				if(!$active && $plugin == 'pw-woocommerce-gift-cards' && $diff_filename == 'pw-gift-cards'){				
					$active = in_array( $diff_filename.'/'.$diff_filename.'.php', (array) get_option( 'active_plugins', array() ) );
				}
				
				/*
				$compt_addon_active = in_array( 'myworks-qbo-sync-compatibility/myworks-qbo-sync-compatibility.php', (array) get_option( 'active_plugins', array() ) );
				*/
			}
		}
		
		if($plugin=='woocommerce-measurement-price-calculator' && !class_exists('WC_Measurement_Price_Calculator')){
			$active = false;
		}
		
		//13-06-2017
		$compt_p_arr = array();
		$compt_p_arr[] = 'woocommerce-measurement-price-calculator';
		$compt_p_arr[] = 'woocommerce-deposits';
		
		$compt_p_arr[] = 'visual-product-configurator';
		$compt_p_arr[] = 'woocommerce-eu-vat-number';
		
		$compt_p_arr[] = 'woocommerce-product-bundles';
		$compt_p_arr[] = 'woocommerce-gateways-discounts-and-fees';
		$compt_p_arr[] = 'woocommerce-additional-fees';
		
		$compt_p_arr[] = 'rp-woo-donation';
		$compt_p_arr[] = 'woo-add-custom-fee';
		$compt_p_arr[] = 'woocommerce-conditional-product-fees-for-checkout';
		
		$compt_p_arr[] = 'woocommerce-order-delivery';
		//$compt_p_arr[] = 'woocommerce-sequential-order-numbers-pro';
		$compt_p_arr[] = 'woocommerce-custom-fields';//
		$compt_p_arr[] = 'woocommerce-checkout-field-editor-pro';//
		$compt_p_arr[] = 'woocommerce-checkout-field-editor';
		
		$compt_p_arr[] = 'woocommerce-hear-about-us';
		$compt_p_arr[] = 'woocommerce-admin-custom-order-fields';//
		$compt_p_arr[] = 'woocommerce-shipment-tracking';
		
		$compt_p_arr[] = 'ph-woocommerce-shipment-tracking';
		
		//$compt_p_arr[] = 'woocommerce-cost-of-goods';
		$compt_p_arr[] = 'woocommerce-avatax';
		$compt_p_arr[] = 'taxify-for-woocommerce';
		$compt_p_arr[] = 'woocommerce-tm-extra-product-options';		
		$compt_p_arr[] = 'woocommerce-product-addons';
		$compt_p_arr[] = 'woocommerce-appointments';
		
		$compt_p_arr[] = 'atum-stock-manager-for-woocommerce';
		
		//if($fc){}
		$compt_p_arr[] = 'woocommerce-aelia-currencyswitcher';
		
		$compt_p_arr[] = 'woocommerce-aelia-currencyswitcher';
		$compt_p_arr[] = 'woocommerce-wholesale-prices';
		
		$compt_p_arr[] = 'yith-woocommerce-gift-cards-premium';
		$compt_p_arr[] = 'ebaylink';
		
		//$compt_p_arr[] = 'woocommerce-subscriptions';
		
		//
		//$compt_p_arr[] = 'custom-order-numbers-for-woocommerce';		
		
		$compt_p_arr[] = 'pw-woocommerce-gift-cards';
		$compt_p_arr[] = 'pw-gift-cards';
		$compt_p_arr[] = 'woocommerce-gift-cards';
		
		/*
		if(in_array($plugin,$compt_p_arr) && !$compt_addon_active){
			$active = false;
		}
		*/
		
		if(!$fc){
			if($plugin=='woocommerce-sequential-order-numbers-pro' && !$active){
				if($this->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
					$active = true;
					/*
					if($this->chk_compt_addons_active('np_custom_order_number','np_custom_order_number')){
						$active = true;
					}
					*/
				}
			}
		}
		
		//
		/*
		if(in_array($plugin,$compt_p_arr) && $active){
			$is_addon_active = $this->chk_compt_addons_active($plugin,$plugin_file);
			$active = $is_addon_active;
		}
		*/
		
		if(in_array($plugin,$compt_p_arr) && $active){
			if($this->is_plg_lc_p_l()){
				//$active = false;#lpa
			}
		}
		
		return $active;
	}

	#New
	public function get_qb_prd_rt_field_v($p_id,$pf='description'){
		$p_id = (int) $p_id;
		if($p_id > 0 && $this->is_connected() && !empty($pf)){
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Item();
			$sql = "SELECT * FROM Item WHERE Id = '{$p_id}' ";
			$items = $ItemService->query($Context, $realm, $sql);
			if($items && !empty($items)){
				$item = $items[0];
				if($pf == 'description'){
					return $item->getDescription();
				}
			}
		}

		return '';
	}

	//26-05-2017
	public function get_qbo_group_product_details($p_id){
		$return = array();
		$p_id = (int) $p_id;
		if($p_id && $this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Item();
			$sql = "SELECT * FROM Item WHERE Id = '{$p_id}' AND Type = 'Group' ";
			$items = $ItemService->query($Context, $realm, $sql);
			if($items && !empty($items)){
				$item = $items[0];
				//$this->_p($item);
				$return['Id'] = $this->qbo_clear_braces($item->getId());
				$return['Name'] = $item->getName();
				$return['Sku'] = $item->getSku();

				#New
				$return['Description'] = $item->getDescription();

				$buldle_items = array();
				$b_tp = 0;
				
				if($item->countItemGroupDetail() && is_object($item->getItemGroupDetail()) && !empty($item->getItemGroupDetail())){
					$line_count = $item->getItemGroupDetail()->countItemGroupLine();
					//$this->_p($line_count);
					$gi_id_arr = array();
					$gi_tp_arr = array();

					for($i=0;$i<$line_count;$i++){
						$GI = $item->getItemGroupDetail()->getItemGroupLine($i);
						$gi_id = (int) $this->qbo_clear_braces($GI->getItemRef());
						if($gi_id){
							$gi_id_arr[] = $gi_id;
							$gi_tp_arr[] = $GI->getItemRef_type();
						}
					}

					$gi_price_arr = array();
					if(!empty($gi_id_arr) && !empty($gi_tp_arr)){
						$gi_tp_arr = array_unique($gi_tp_arr);
						$gi_tp_str = implode("','", $gi_tp_arr);
						$gi_tp_str = "'".$gi_tp_str."'";

						$gi_id_str = implode("','", $gi_id_arr);
						$gi_id_str = "'".$gi_id_str."'";

						$sql = "SELECT Id,UnitPrice,Type  FROM Item WHERE Id IN({$gi_id_str}) AND Type IN({$gi_tp_str}) ";
						$g_items = $ItemService->query($Context, $realm, $sql);
						//$this->_p($g_items);
						if($g_items && !empty($g_items)){
							foreach($g_items as $gi){
								if($gi->countUnitPrice()){
									$gi_price_arr[(int) $this->qbo_clear_braces($gi->getId())] = $gi->getUnitPrice();
								}
							}
						}
					}

					//$this->_p($gi_price_arr);

					for($i=0;$i<$line_count;$i++){
						$GI = $item->getItemGroupDetail()->getItemGroupLine($i);
						$gi_id = (int) $this->qbo_clear_braces($GI->getItemRef());
						if($gi_id){
							$tbi = array();
							$tbi['ItemRef'] = $gi_id;

							$up = (isset($gi_price_arr[$gi_id]))?$gi_price_arr[$gi_id]:0;
							$tbi['UnitPrice'] = $up;
							$b_tp+=($up*$GI->getQty());

							$tbi['Qty'] = $GI->getQty();
							$tbi['ItemRef_name'] = $GI->getItemRef_name();
							$tbi['ItemRef_type'] = $GI->getItemRef_type();
							$buldle_items[] = $tbi;
						}
					}

				}

				$return['buldle_items'] = $buldle_items;
				$return['b_tp'] = $b_tp;
			}
		}
		return $return;
	}
	
	/**/
	protected function get_woo_child_bundle_line_item_parent_line($qbo_inv_items,$c_bundled_by){
		$p_arr = array();
		if(is_array($qbo_inv_items) && !empty($qbo_inv_items) && !empty($c_bundled_by)){
			foreach($qbo_inv_items as $qi_k => $qbo_item){
				if(isset($qbo_item['bundled_items']) && isset($qbo_item['bundle_cart_key']) && $qbo_item['bundle_cart_key'] == $c_bundled_by){
					return $qbo_item;
				}
			}
		}
		return $p_arr;
	}
	
	public function get_woo_version_number(){
		// If get_plugins() isn't available, require it
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file = 'woocommerce.php';

		// If the plugin version number is set, return it
		if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
			return $plugin_folder[$plugin_file]['Version'];

		} else {
		// Otherwise return null
			return NULL;
		}
	}
	
	public function ord_pmnt_is_mt_ls_check_by_ord_id($order_id){
		$order_id = (int) $order_id;
		if($order_id>0){
			if(!$this->is_pl_res_tml()){return true;}	
			global $wpdb;
			$pa = $this->get_row($wpdb->prepare("SELECT `post_date` FROM {$wpdb->posts} WHERE `post_type` = 'shop_order' AND `ID` = %d ",$order_id));
			if(is_array($pa) && !empty($pa)){
				$pd = $pa['post_date'];
				if(empty($pd)){return false;}
				$pd = strtotime($pd);				
				if ($pd < strtotime('-'.$this->get_hd_ldys_lmt().' days')){
					return false;
				}else{
					return true;
				}
			}
		}
	}
	
	private function clear_invalid_mappings($type,$loop=false){
		$list_table = '';$it_id_field = '';$it_qb_id_field = '';
		$map_table = '';$mt_id_field = '';$mt_qb_id_field = '';
		
		switch ($type) {
			case "product":
				$list_table = 'mw_wc_qbo_sync_qbo_items';
				$map_table = 'mw_wc_qbo_sync_product_pairs';
				
				$it_qb_id_field = 'itemid';
				$mt_qb_id_field = 'quickbook_product_id';
				
				$it_id_field = 'ID';
				
				break;
			case "variation":
				$list_table = 'mw_wc_qbo_sync_qbo_items';
				$map_table = 'mw_wc_qbo_sync_variation_pairs';
				
				$it_qb_id_field = 'itemid';
				$mt_qb_id_field = 'quickbook_product_id';
				
				$it_id_field = 'ID';
				
				break;
			case "customer":
				$list_table = 'mw_wc_qbo_sync_qbo_customers';
				$map_table = 'mw_wc_qbo_sync_customer_pairs';
				
				$it_qb_id_field = 'qbo_customerid';
				$mt_qb_id_field = 'qbo_customerid';
				
				break;
			case "vendor":
				$list_table = 'mw_wc_qbo_sync_qbo_vendors';
				$map_table = 'mw_wc_qbo_sync_vendor_pairs';
				
				$it_qb_id_field = 'qbo_vendorid';
				$mt_qb_id_field = 'qbo_vendorid';
				
				break;
			case "paymentmethod":
				
				break;
			default:
				
		}
		
		if($list_table!='' && $map_table!='' && $it_qb_id_field!='' && $mt_qb_id_field!=''){
			global $wpdb;
			$list_table = $wpdb->prefix.$list_table;
			$map_table = $wpdb->prefix.$map_table;
			
			if(empty($it_id_field)){
				$it_id_field = 'id';
			}
			
			if(empty($mt_id_field)){
				$mt_id_field = 'id';
			}
			
			if($loop){
				return $this->clear_invalid_mappings_by_loop($list_table,$map_table,$it_id_field,$mt_id_field,$it_qb_id_field,$mt_qb_id_field);
			}
			
			/*
			$sq = " SELECT `{$it_qb_id_field}` FROM {$list_table} ";
			$q = " DELETE FROM {$map_table} WHERE `{$mt_qb_id_field}` NOT IN ({$sq}) ";
			*/
			
			$sq = "SELECT `{$it_qb_id_field}` FROM {$list_table} WHERE {$list_table}.{$it_qb_id_field} = {$map_table}.{$mt_qb_id_field}";
			$q = "DELETE FROM {$map_table} WHERE NOT EXISTS ({$sq}); ";
			$wpdb->query($q);
			return true;
		}
	}	
	
	private function clear_invalid_mappings_by_loop($list_table,$map_table,$it_id_field,$mt_id_field,$it_qb_id_field,$mt_qb_id_field){
		global $wpdb;
		$map_data = $this->get_data("SELECT `{$mt_id_field}` , `{$mt_qb_id_field}` FROM {$map_table}");
		$tot_deleted = 0;
		if(is_array($map_data) && !empty($map_data)){
			foreach($map_data as $md){
				$mt_id_val = (int) $md[$mt_id_field];
				$mt_qb_val = $md[$mt_qb_id_field];
				$ld = $this->get_row($wpdb->prepare("SELECT `{$it_id_field}` FROM {$list_table} WHERE `{$it_qb_id_field}` !='' AND `{$it_qb_id_field}` = %s ",$mt_qb_val));
				if(empty($ld)){
					$wpdb->query($wpdb->prepare("DELETE FROM `{$map_table}` WHERE `{$mt_id_field}` = %d AND `{$mt_qb_id_field}` = %s ",$mt_id_val,$mt_qb_val));
					$tot_deleted++;
				}
			}
		}
		return $tot_deleted;
	}
	
	public function clear_customer_invalid_mappings(){
		return $this->clear_invalid_mappings('customer');
	}
	
	public function clear_vendor_invalid_mappings(){
		return $this->clear_invalid_mappings('vendor');
	}
	
	public function clear_product_invalid_mappings(){
		return $this->clear_invalid_mappings('product');
	}
	
	public function clear_variation_invalid_mappings(){
		return $this->clear_invalid_mappings('variation');
	}

	public function get_log_qbo_view_link($data){
		$qb_view_link = '';
		if(is_array($data) && !empty($data)){
			$qb_view_items = array();
			$qb_view_items[] = 'Customer';
			$qb_view_items[] = 'Invoice';
			$qb_view_items[] = 'Payment';

			$qb_view_items[] = 'Journal Entry';
			$qb_view_items[] = 'Deposit';
			$qb_view_items[] = 'Refund';

			$qb_view_items[] = 'Product';
			$qb_view_items[] = 'Category';

			$is_sandbox_con = ($this->get_option('mw_wc_qbo_sync_sandbox_mode')=='yes')?true:false;
			$qbo_url = ($is_sandbox_con)?'https://sandbox.qbo.intuit.com/app/':'https://qbo.intuit.com/app/';

			if(in_array($data['log_type'],$qb_view_items) && $data['success']==1){
				$chk_extra_options = true;
				if(strpos($data['log_title'],'Webhook')!==false || strpos($data['log_title'],'Import')!==false){
					$chk_extra_options = false;
				}

				if(strpos($data['details'],'ID is #')!==false && $chk_extra_options){
					$qbo_id_arr = explode('ID is #', $data['details']);
					$qbo_id = (int) end($qbo_id_arr);
					if($qbo_id>0){
						switch ($data['log_type']) {
							case "Customer":
								$qb_view_link = $qbo_url.'customerdetail?nameId='.$qbo_id;
								break;
							case "Invoice":
								if(strpos($data['details'],'SalesReceipt')!==false){
									$qb_view_link = $qbo_url.'salesreceipt?txnId='.$qbo_id;
								}else{
									$qb_view_link = $qbo_url.'invoice?txnId='.$qbo_id;
								}
								break;
							case "Payment":
								$qb_view_link = $qbo_url.'recvpayment?txnId='.$qbo_id;
								break;

							case "Journal Entry":
								$qb_view_link = $qbo_url.'journal?txnId='.$qbo_id;
								break;

							case "Refund":
								$qb_view_link = $qbo_url.'refundreceipt?txnId='.$qbo_id;
								break;

							case "Deposit":
								$qb_view_link = $qbo_url.'deposit?txnId='.$qbo_id;
								break;

							case "Product":
								$qb_view_link = $qbo_url.'items';
								break;

							case "Category":
								$qb_view_link = $qbo_url.'categories';
								break;

							default:

						}
					}
				}
			}

			if($qb_view_link!=''){
				$qb_view_link = '<a target="_blank" class="lg_qb_view" href="'.$qb_view_link.'" title="View in QuickBooks Online">i</a>';
			}
		}
		return $qb_view_link;
	}

	public function get_push_qbo_view_href($type,$qbo_id){
		$qb_view_link = 'javascript:void(0);';
		$type = (string) $type;
		$qbo_id = (int) $qbo_id;
		$is_sandbox_con = ($this->get_option('mw_wc_qbo_sync_sandbox_mode')=='yes')?true:false;
		$qbo_url = ($is_sandbox_con)?'https://sandbox.qbo.intuit.com/app/':'https://qbo.intuit.com/app/';
		$qb_view_items = array();
		switch ($type) {
			case "Customer":
				$qb_view_link = $qbo_url.'customerdetail?nameId='.$qbo_id;
				break;

			case "Invoice":
				$qb_view_link = $qbo_url.'invoice?txnId='.$qbo_id;
				break;

			case "SalesReceipt":
				$qb_view_link = $qbo_url.'salesreceipt?txnId='.$qbo_id;
				break;

			case "Payment":
				$qb_view_link = $qbo_url.'recvpayment?txnId='.$qbo_id;
				break;
				
			case "Refund":
				$qb_view_link = $qbo_url.'refundreceipt?txnId='.$qbo_id;
				break;
			
			default:
		}
		return $qb_view_link;
	}
	public function get_menu_queue_count(){
		global $wpdb;
		$cq = "SELECT COUNT(*) FROM `{$wpdb->prefix}mw_wc_qbo_sync_real_time_sync_queue` WHERE `id` >0 AND `run` = 0 ";
		return $wpdb->get_var($cq);
	}
	
	public function get_qsmd_msg(){
		return __('Plesae resume realtime sync from the plugin dashboard page to enable settings changes','mw_wc_qbo_sync');
	}
	
	public function is_shop_manager() {
		$user = wp_get_current_user();
		if ( is_array($user) && isset( $user['roles'][0] ) && $user['roles'][0] == 'shop_manager' ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function is_wq_vendor_pm_enable(){
		if($this->is_plugin_active('atum-stock-manager-for-woocommerce') && $this->option_checked('mw_wc_qbo_sync_compt_np_wuqbovendor_ms') && !empty($this->get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur'))){
			return true;
		}
		return false;
	}
	
	public function get_atum_supplier_dtls_from_wc_vendor_usr_id($id=0,$ext_d=false){
		$id = intval($id);
		if($id>0){
			global $wpdb;
			$smd = $this->get_row($wpdb->prepare("SELECT `post_id` FROM {$wpdb->postmeta} WHERE `meta_key` = '_default_settings_assigned_to' AND `meta_value` = %s ",$id));
			$supplier_id = 0;
			if(is_array($smd) && !empty($smd)){
				$supplier_id = $smd['post_id'];
			}
			
			if($supplier_id>0){
				$sd = $this->get_row($wpdb->prepare("SELECT ID,post_title FROM {$wpdb->posts} WHERE ID = %d AND `post_type` = 'atum_supplier' ",$supplier_id));
				if($ext_d && is_array($sd) && !empty($sd)){
					$sd_m = get_post_meta($supplier_id);
					if(is_array($sd_m) && !empty($sd_m)){
						$sd['_default_settings_location'] = (isset($sd_m['_default_settings_location'][0]))?$sd_m['_default_settings_location'][0]:'';
					}
				}
				return $sd;
			}
		}	
	}
	
	public function get_n_cam_wf_list(){
		return array(
			'user_email' => 'Email',
			'user_login' => 'Username',
			'display_name' => 'Display Name',
			'first_name_last_name' => 'First Name + Last Name',
			//'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'billing_company' => 'Company Name',
		);
	}
	
	public function get_n_cam_qf_list(){
		return array(
			'email' => 'Email',
			'dname' => 'Display Name',
			'first_last' => 'First Name + Last Name',
			//'first' => 'First Name',
			'last' => 'Last Name',
			'company' => 'Company Name',
		);
	}
	
	public function get_n_pam_wf_list(){
		return array(
			'name' => 'Name',
			'sku' => 'SKU',
		);
	}
	
	public function get_n_pam_qf_list(){
		return array(
			'name' => 'Name',
			'sku' => 'SKU',
		);
	}
	
	public function get_n_vam_wf_list(){
		return array(
			'name' => 'Name',
			'sku' => 'SKU',
		);
	}
	
	public function get_n_vam_qf_list(){
		return array(
			'name' => 'Name',
			'sku' => 'SKU',
		);
	}
	
	public function get_string_between($string, $start, $end){
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) return '';
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}
	
	public function get_string_after($string, $start){
		$arr = explode($start, $string);
		if(is_array($arr) && isset($arr[1])){
			return $arr[1];
		}
		return '';
	}
	
	/**/
	public function get_osl_sm_val($prm=array()){
		$stk = base64_decode('T3JkZXJBZGQ=');
		$sl_okv = base64_decode('bXdfd2NfcWJvX3N5bmNfaW1wX29zbGNkX2RjYQ==');		
		$oslcd = get_option($sl_okv);
		
		$cy = $this->now('Y');
		$cm = $this->now('F');
		
		if(is_array($oslcd) && !empty($oslcd)){
			if(isset($oslcd[$cy]) && is_array($oslcd)){
				if(isset($oslcd[$cy][$cm]) && is_array($oslcd[$cy][$cm])){
					if(isset($oslcd[$cy][$cm][$stk]) && (int) $oslcd[$cy][$cm][$stk] > 0){
						$e_scv = (int) $oslcd[$cy][$cm][$stk];
						return $e_scv;
					}
				}
			}
		}
		return 0;
	}
	
	public function get_osl_lp_count($prm=array()){
		$osl_pm_v = 20;
		if($this->is_plg_lc_p_r()){
			$osl_pm_v = 60;
		}
		
		if($this->is_plg_lc_p_g()){
			$osl_pm_v = 300;
		}

		if($this->is_plg_lc_p_s()){
			$osl_pm_v = 1000;
		}

		return $osl_pm_v;
	}
	
	public function lp_chk_osl_allwd($prm=array()){
		if(!$this->is_plg_lc_p_sr()){
			$e_scv = (int) $this->get_osl_sm_val();
			$osl_pm_v = $this->get_osl_lp_count();							
			if($e_scv >= $osl_pm_v){
				return false;
			}
		}
		return true;
	}
	
	private function set_imp_sync_data($prm=array()){
		/*
		if(is_array($prm) && isset($prm['stk']) && !empty($prm['stk'])){
			$stk = base64_decode($prm['stk']);
		}else{
			$stk = base64_decode('T3JkZXJBZGQ=');
		}
		*/
		
		$E_ID = 0;
		if(is_array($prm) && isset($prm['ID']) && (int) $prm['ID'] > 0){
			$E_ID = (int) $prm['ID'];
		}
		
		$stk = base64_decode('T3JkZXJBZGQ=');
		
		$sl_okv = base64_decode('bXdfd2NfcWJvX3N5bmNfaW1wX29zbGNkX2RjYQ==');		
		$oslcd = get_option($sl_okv);
		
		$cy = $this->now('Y');
		$cm = $this->now('F');		
		//
		
		$keep_p_ym_d = false;
		
		if(is_array($oslcd) && !empty($oslcd)){
			#New
			if(!$keep_p_ym_d){
				foreach($oslcd as $y => $d){
					if($y != $cy){
						unset($oslcd[$y]);
					}else{
						if(isset($oslcd[$y]) && !empty($oslcd[$y])){
							foreach($oslcd[$y] as $m => $d){
								if($m != $cm){
									unset($oslcd[$cy][$m]);
								}
							}
						}
					}
				}
			}
			
			$e_scv_inc = false;
			if(isset($oslcd[$cy]) && is_array($oslcd)){
				if(isset($oslcd[$cy][$cm]) && is_array($oslcd[$cy][$cm])){
					if(isset($oslcd[$cy][$cm][$stk]) && (int) $oslcd[$cy][$cm][$stk] > 0){
						$ii_cv = true;						
						if($E_ID > 0 && isset($oslcd[$cy][$cm][$stk.'_IDs'])){
							$stk_id_arr = $oslcd[$cy][$cm][$stk.'_IDs'];
							if(is_array($stk_id_arr) && in_array($E_ID,$stk_id_arr)){
								$ii_cv = false;
							}else{
								$stk_id_arr[] = $E_ID;
								$oslcd[$cy][$cm][$stk.'_IDs'] = $stk_id_arr;
							}
						}
						
						if($ii_cv){
							$e_scv = (int) $oslcd[$cy][$cm][$stk];
							$e_scv++;
							$oslcd[$cy][$cm][$stk] = $e_scv;
						}
						
						$e_scv_inc = true;
					}
				}
			}
			
			if(!$e_scv_inc){
				$oslcd[$cy][$cm][$stk] = 1;				
			}
			
			if($E_ID > 0 && !isset($oslcd[$cy][$cm][$stk.'_IDs'])){
				$oslcd[$cy][$cm][$stk.'_IDs'] = array($E_ID);
			}
		}else{
			$oslcd = array();
			$oslcd[$cy] = array(
				$cm => array(
					$stk => 1,
				),
			);
			
			if($E_ID > 0 ){
				$oslcd[$cy][$cm][$stk.'_IDs'] = array($E_ID);
			}			
		}
		
		update_option($sl_okv,$oslcd,false);
	}
	
	public function log_page_msg_col_output($l_row){
		$l_msg = '';
		$oth_arr = array();
		if(is_array($l_row) && isset($l_row['details'])){
			$is_error = (!$l_row['success'])?true:false;
			
			$l_type = $l_row['log_type'];
			$l_title  = $l_row['log_title'];
			
			if(!empty($l_row['details'])){
				$lm_tmp = trim($l_row['details']);
				//$lm_arr = preg_split('/\s+/', $lm_tmp);
				$lm_arr = explode(' ',$lm_tmp);
				
				/*
				if(isset($_GET['debug'])){echo '<tr><td colspan="5">';$this->_p($lm_arr);echo '</td></tr>';}
				*/
				
				$lm_arr_m = array();
				if(is_array($lm_arr) && !empty($lm_arr)){
					$lm_arr_count = count($lm_arr);
					//
					$ni_push = ['Invoice','Payment','Customer','Vendor','Product'];
					$ni_pull = ['Product','Inventory'];
					
					foreach($lm_arr as $lm_ak => $lm_av){						
						if(!$is_error && !empty($l_type)){							
							if(in_array($l_type,$ni_push) && !$this->start_with($l_title,'Import')){
								$is_update = $this->start_with($l_title,'Update');
								if($lm_ak == 1){									
									if($this->start_with($lm_av,'#')){										
										$lm_av = '<span class="lm_wid">'.$lm_av.'</span>';
									}
								}
								
								if($lm_ak == $lm_arr_count-1){
									if($this->start_with($lm_av,'#')){
										$lm_av = '<span class="lm_qid">'.$lm_av.'</span>';
									}
								}
							}							
							
							if(in_array($l_type,$ni_pull) && !$this->start_with($l_title,'Import')){
								if($lm_ak == 1 || ($l_type == 'Inventory' && $lm_ak == 2)){									
									if($this->start_with($lm_av,'#')){										
										$lm_av = '<span class="lm_qid">'.$lm_av.'</span>';
									}
								}
								
								if($lm_ak == $lm_arr_count-1){
									if($this->start_with($lm_av,'#')){
										$lm_av = '<span class="lm_wid">'.$lm_av.'</span>';
									}
								}
							}
							
							/**/
							if($l_type == 'Deposit' && $this->start_with($lm_tmp,'Created Deposit with')){
								if($this->start_with($lm_av,'#') && isset($lm_arr[$lm_ak-1]) && $lm_arr[$lm_ak-1] == 'ID' && isset($lm_arr[$lm_ak-2]) && $lm_arr[$lm_ak-2] == 'Deposit' && isset($lm_arr[$lm_ak-3]) && $lm_arr[$lm_ak-3] == 'QuickBooks'){
									$lm_av_nl_av = $lm_av;
									$lm_av_nl_arr = explode(PHP_EOL,$lm_av);									
									if(is_array($lm_av_nl_arr) && count($lm_av_nl_arr) == 2 && $lm_av_nl_arr[1] == 'WooCommerce'){
										$lm_av = '<span class="lm_qid">'.$lm_av_nl_arr[0].'</span>'. PHP_EOL . $lm_av_nl_arr[1];
									}									
								}
							}
							
							/**/
							if($l_type == 'Cron' && $l_title == 'Quick Refresh Sync' && $this->start_with($lm_tmp,'Quick Refresh Sync Started')){
								$lm_av_nl_av = $lm_av;
								$lm_av_nl_arr = explode(PHP_EOL,$lm_av);
								if(is_array($lm_av_nl_arr) && count($lm_av_nl_arr) == 2 && ($lm_av_nl_arr[1] == 'Total' || $lm_av_nl_arr[1] == 'Quick')){
									if(is_numeric($lm_av_nl_arr[0])){
										$lm_av = '<b class="noi">'.$lm_av_nl_arr[0].'</b>'. PHP_EOL . $lm_av_nl_arr[1];
									}
								}								
							}
							
						}
						
						if($is_error && !empty($l_type)){							
							$error_code = '';
							$lm_av_nl_av = $lm_av;
							
							$lm_av_nl_arr = explode(PHP_EOL,$lm_av);							
							if($l_type == 'Deposit' && is_array($lm_av_nl_arr) && count($lm_av_nl_arr) == 2){
								$lm_av_nl_av = $lm_av_nl_arr[1];
							}
							
							if($this->start_with($lm_av_nl_av,'Error:') && substr($lm_av_nl_av, -1) == ':'){
								$lm_av_nxt = (isset($lm_arr[$lm_ak+1]))?$lm_arr[$lm_ak+1]:'';								
								if(!empty($lm_av_nxt) && $this->start_with($lm_av_nxt,'[')){
									$ec_arr = explode(':',$lm_av);
									//$this->_p($ec_arr);
									if(is_array($ec_arr) && count($ec_arr) == 3 && !empty($ec_arr[1]) && is_numeric($ec_arr[1])){
										$error_code = $ec_arr[1];
										$oth_arr['error_code'] = $error_code;
										$oth_arr['error_code_url'] = 'https://developer.intuit.com/docs/00_quickbooks_online/2_build/20_explore_the_quickbooks_online_api/error_codes';
									}
								}
							}
						}
						//https://developer.intuit.com/docs/00_quickbooks_online/2_build/20_explore_the_quickbooks_online_api/error_codes
						$lm_arr_m[$lm_ak] = $lm_av;
					}
				}
				
				/*
				if(isset($_GET['debug'])){echo '<tr><td colspan="5">';$this->_p($oth_arr);echo '</td></tr>';}
				if(isset($_GET['debug'])){echo '<tr><td colspan="5">';$this->_p($lm_arr_m);echo '</td></tr>';}
				*/
				
				$l_msg = implode(' ',$lm_arr_m);
				
				/**/
				if(!$is_error){
					if($l_type == 'Deposit' && $this->start_with($lm_tmp,'Created Deposit with')){
						$woi = $this->get_string_between($l_msg,'WooCommerce Orders Included: ','WooCommerce Payments Included: ');
						$wpi = $this->get_string_after($l_msg,'WooCommerce Payments Included: ');						
						
						$woi_n = '';
						$wpi_n = '';
						
						if(!empty($woi)){
							$woi_a = explode(', ',$woi);
							if(is_array($woi_a) && !empty($woi_a)){
								$woi_a = array_map(
								function ($oi) {
									if($this->start_with($oi,'#')){
										return '<span class="lm_wid ord">'.$oi.'</span>';
									}
								}
								, $woi_a
								);
								
								$woi_n = implode(', ',$woi_a);
								$l_msg = str_replace($woi,$woi_n,$l_msg);
							}							
						}
						
						if(!empty($wpi)){
							$wpi_a = explode(', ',$wpi);
							if(is_array($wpi_a) && !empty($wpi_a)){
								$wpi_a = array_map(
								function ($pi) {
									if($this->start_with($pi,'#')){
										return '<span class="lm_wid pmnt">'.$pi.'</span>';
									}
								}
								, $wpi_a
								);
								
								$wpi_n = implode(', ',$wpi_a);
								$l_msg = str_replace($wpi,$wpi_n,$l_msg);
							}							
						}
					}
				}
			}
			
			//$l_msg = $l_row['details'];
			$l_msg = nl2br(stripslashes($l_msg));
		}
		
		return array('details'=>$l_msg,'oth'=>$oth_arr);
	}
	
	public function get_order_base_currency_total_from_order_id($order_id){
		$o_tot = 0;
		$order_id = (int) $order_id;
		if($order_id>0){
			$od = $this->get_wc_order_details_from_order($order_id,get_post($order_id));
			if(is_array($od) && !empty($od)){
				if(isset($od['qbo_inv_items']) && is_array($od['qbo_inv_items']) && !empty($od['qbo_inv_items'])){
					foreach($od['qbo_inv_items'] as $oi){
						$o_tot+= $oi['line_subtotal_base_currency'];
						//$o_tot+= $oi['line_total_base_currency'];						
					}
				}
				
				if(isset($od['tax_details']) && is_array($od['tax_details']) && !empty($od['tax_details'])){
					foreach($od['tax_details'] as $oi){
						$o_tot+= $oi['tax_amount_base_currency'];
						$o_tot+= $oi['shipping_tax_amount_base_currency'];
					}
				}
				
				if(isset($od['used_coupons']) && is_array($od['used_coupons']) && !empty($od['used_coupons'])){
					foreach($od['used_coupons'] as $uc){
						if(isset($uc['discount_amount_base_currency'])){
							$o_tot-= $uc['discount_amount_base_currency'];
						}
						
						if(isset($uc['[discount_amount_tax_base_currency'])){
							$o_tot-= $uc['[discount_amount_tax_base_currency'];
						}
					}
				}
				
				$o_tot+= $od['_order_shipping_base_currency'];
				
				//$o_tot-= $od['_order_shipping_tax_base_currency'];
				
				//$o_tot-= $od['_cart_discount_base_currency'];
				
				$o_tot = $this->qbo_limit_decimal_points($o_tot);
			}
		}
		return $o_tot;
	}
	
	public function Fix_All_WooCommerce_Variations_Names(){
		/*Disabled*/
		return 0;
		
		global $wpdb;
		$sql = "
			SELECT p.ID, p.post_title AS name, p.post_parent as parent_id, p1.post_title AS parent_name
			FROM ".$wpdb->posts." p
			LEFT JOIN " . $wpdb->posts . " p1 ON p.post_parent = p1.ID			
			WHERE p.post_type =  'product_variation'
			AND p1.post_title != ''
			AND (p.post_title = '' OR p.post_title = p1.post_title)
			AND p.post_status NOT IN('trash','auto-draft','inherit')
			ORDER BY p.ID ASC
		";
		//echo $sql;
		$v_list = $this->get_data($sql);
		//$this->_p($v_list);
		$total_v_name_changed = 0;
		if(is_array($v_list) && !empty($v_list)){
			foreach($v_list as $vl_d){
				$v_name_suffix = '';
				$ID = intval($vl_d['ID']);
				$p_id = (int) $vl_d['parent_id'];
				$_product_attributes_a = get_post_meta($p_id,'_product_attributes',true);
				//$this->_p($_product_attributes_a);
				if(is_array($_product_attributes_a) && !empty($_product_attributes_a)){
					$vm_sql = "
						SELECT `meta_key` , `meta_value` FROM {$wpdb->postmeta} 
						WHERE `post_id` = {$ID}
						AND meta_key LIKE 'attribute_%%'
					";
					$vm_list = $this->get_data($vm_sql);
					//$this->_p($vm_list);
					if(is_array($vm_list) && !empty($vm_list)){
						foreach($vm_list as $vmk => $vmv){
							if (substr($vmv['meta_key'], 0, strlen('attribute_')) == 'attribute_') {
								$att_key = substr($vmv['meta_key'], strlen('attribute_'));
								$att_val = $vmv['meta_value'];
								if(!empty($att_key) && isset($_product_attributes_a[$att_key])){
									if($vmk == 0){
										$v_name_suffix.= ' - '.$att_val;
									}else{
										$v_name_suffix.= ', '.$att_val;
									}
								}
							}							
						}
					}
				}
				
				//$this->_p($v_name_suffix);
				if(!empty($v_name_suffix)){
					$new_variation_name = $vl_d['parent_name'] . $v_name_suffix;
					//$this->_p($new_variation_name);
					//wp_update_post
					$vnu_sql = $wpdb->prepare("UPDATE {$wpdb->posts} SET `post_title` = %s WHERE `ID` = %d AND `post_type` = 'product_variation' ",$new_variation_name,$ID);
					//echo $vnu_sql;
					$wpdb->query($vnu_sql);
					$total_v_name_changed++;
				}
			}
		}
		
		if($total_v_name_changed>0){			
			$this->save_log('Variations Name Update','Number of variations name updated: '.$total_v_name_changed,'Variation_NU',1,true,'Update');
		}
		
		return $total_v_name_changed;
	}
	
	public function qbo_limit_decimal_points($amount,$dp=2){
		$amount = trim($amount);
		$dp = (int) $dp;
		if ($amount!='' && $d_pos = strpos($amount, '.') !== false && $dp>0) {
			$a_dp = substr($amount, $d_pos+1);
			if(strlen($a_dp) > $dp){
				$amount = number_format((float)$amount, 2, '.', '');
			}
		}
		return $amount;
	}
	
	public function bcdiv_m($_ro, $_lo, $_scale=0) {
		return round($_ro/$_lo, $_scale);
	}	
	
	public function trim_after_decimal_place($amount,$dp=2){
		$amount = trim($amount);
		$dp = (int) $dp;		
		if ($amount!='' && $d_pos = strpos($amount, '.') !== false && $dp>0) {
			$a_dp = strlen(substr(strrchr($amount, "."), 1));
			if($a_dp > $dp){
				$amount = floatval($amount);
				/*
				if(!function_exists(bcdiv)){
					$amount = $this->bcdiv_m($amount, 1, $dp);
				}else{
					$amount = bcdiv($amount, 1, $dp);
				}
				*/
				
				$amount = $this->bcdiv_m($amount, 1, $dp);			
			}
		}		
		return $amount;
	}
	
	public function wacs_base_cur_enabled(){
		if($this->is_plugin_active('woocommerce-aelia-currencyswitcher')){
			if($this->option_checked('mw_wc_qbo_sync_wacs_base_cur_support')){
				return true;
			}			
		}
		return false;
	}
	
	public function get_qbo_bundle_sub_item_desc_from_woo($bsi_id,$bsi_desc){
		$w_bsi_desc = '';
		$bsi_id = intval($bsi_id);
		if($bsi_id > 0){
			global $wpdb;
			
			$map_data = $this->get_row($wpdb->prepare("SELECT `wc_product_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `quickbook_product_id` = %d AND `wc_product_id` > 0 ",$bsi_id));			
			
			$is_variation = false;
			if(empty($map_data)){				
				$map_data = $this->get_row($wpdb->prepare("SELECT `wc_variation_id` FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `quickbook_product_id` = %d AND `wc_variation_id` > 0 ",$bsi_id));
				$is_variation = true;
			}
			
			//
			$inv_sr_qb_lid_val = $this->get_option('mw_wc_qbo_sync_inv_sr_qb_lid_val');
			
			$wc_product_id = 0;
			$wc_variation_id = 0;
			
			if(empty($map_data)){
				if($inv_sr_qb_lid_val == 'no_desc'){
					return '';
				}
				return $bsi_desc;
			}
			
			if($is_variation){
				$wc_variation_id = $map_data['wc_variation_id'];
			}else{
				$wc_product_id = $map_data['wc_product_id'];
			}
			
			/**/
			if($inv_sr_qb_lid_val == 'woo_pv_sdc'){
				if($wc_variation_id > 0){
					$w_bsi_desc = $this->get_field_by_val($wpdb->posts,'post_excerpt','ID',$wc_variation_id);					
				}else{
					$w_bsi_desc = $this->get_field_by_val($wpdb->posts,'post_excerpt','ID',$wc_product_id);
				}				
			}else{
				if($wc_variation_id > 0){
					$w_bsi_desc = $this->get_field_by_val($wpdb->posts,'post_title','ID',$wc_variation_id);					
				}else{
					$w_bsi_desc = $this->get_field_by_val($wpdb->posts,'post_title','ID',$wc_product_id);
				}
			}
			
			$w_bsi_desc = $this->get_array_isset(array('w_bsi_desc'=>$w_bsi_desc),'w_bsi_desc','');
		}
		
		if(empty($w_bsi_desc)){
			$w_bsi_desc = $bsi_desc;
		}
		
		#New
		if($inv_sr_qb_lid_val == 'woo_pbs'){
			$p_v_id = ($wc_variation_id > 0)?$wc_variation_id:$wc_product_id;
			$pbs = get_post_meta($p_v_id,'_backorders',true);
			if($pbs == 'yes'){
				//$w_bsi_desc = __('Allow','mw_wc_qbo_sync');
				$w_bsi_desc = 'Allow';
			}elseif($pbs == 'notify'){
				$w_bsi_desc = 'Allow, but notify customer';
			}else{
				$w_bsi_desc = 'Do not allow';
			}
		}

		#New
		if($inv_sr_qb_lid_val == 'mp_qbp_dc'){
			$Description = $this->get_qb_prd_rt_field_v($bsi_id,'description');
		}
		
		#New
		if($inv_sr_qb_lid_val == 'no_desc'){
			$w_bsi_desc = '';
		}
		
		return $w_bsi_desc;
	}
	
	//
	public function get_wes_eo_qb_loc($invoice_data){
		$qbl = '';
		if(is_array($invoice_data) && !empty($invoice_data)){
			if($this->option_checked('mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed')){
				if($this->is_plugin_active('woocommerce-ebay-sync')){
					$_affinity_ebayorder = $this->get_array_isset($invoice_data,'_affinity_ebayorder',0);
					if($_affinity_ebayorder == 1){
						 $qbl = $this->get_option('mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc');
					}else{
						 $qbl = $this->get_option('mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc');
					}
				}
				
				if($this->is_plugin_active('ebaylink')){
					$customer_note = $this->get_array_isset($invoice_data,'customer_note','',true);
					if(!empty($customer_note) && $this->start_with($customer_note,'eBay')){
						$qbl = $this->get_option('mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc');
					}else{
						$qbl = $this->get_option('mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc');
					}
				}
				
			}
		}
		return $qbl;
	}
	
	public function is_queue_add($action='',$data=null){
		if(!empty($action) && is_array($data) && !empty($data)){
			if($action=='OrderPush' && isset($data['order_id']) && (int) $data['order_id'] > 0){
				$order_id = $data['order_id'];
				$order = get_post($order_id);
				
				if(is_object($order) && !empty($order)){
					if($order->post_type == 'shop_order'){						
						if($order->post_status!='auto-draft' || $order->post_status!='trash'){
							/*if($order->post_status!='draft'){}*/
							$only_sync_status = $this->get_option('mw_wc_qbo_sync_specific_order_status');
							if(!empty($only_sync_status)){
								$only_sync_status = explode(',',$only_sync_status);
								if(is_array($only_sync_status) && in_array($order->post_status,$only_sync_status)){
									return true;
								}
								return false;
							}
							return true;
						}
					}
				}
				
				return false;
			}
			
			//
			if($action=='PaymentPush' && isset($data['order_id']) && (int) $data['order_id'] > 0){
				global $wpdb;
				
				$order_id = $data['order_id'];				
				$queue_table = $wpdb->prefix.'mw_wc_qbo_sync_real_time_sync_queue';
				$check_queue_query = $wpdb->prepare("SELECT * FROM `$queue_table` WHERE `item_type` = %s AND `item_action` = %s AND `item_id` = %d ",'Invoice','OrderPush',$order_id);
				if(!empty($this->get_row($check_queue_query))){
					return true;
				}
				return false;
			}
			
			//
			if($action=='RefundPush' && isset($data['order_id']) && (int) $data['order_id'] > 0){
				$order_id = (int) $data['order_id'];
				$_payment_method = get_post_meta( $order_id, '_payment_method', true );
				$_order_currency = get_post_meta( $order_id, '_order_currency', true );
				if(!$this->if_sync_refund(array('_payment_method'=>$_payment_method,'_order_currency'=>$_order_currency))){
					return false;
				}
			}
			
		}
		return true;
	}
	
	public function get_wc_refund_id_from_order_id($order_id){
		$refund_id = 0;
		global $wpdb;
		$ID = (int) $order_id;
		$rfd_q = $wpdb->prepare("SELECT ID FROM `{$wpdb->posts}` WHERE `post_type` = 'shop_order_refund' AND `post_parent` = %d ORDER BY ID DESC LIMIT 0,1 ",$ID);
		$rf_data = $wpdb->get_row($rfd_q);
		if(is_object($rf_data) && !empty($rf_data)){
			$refund_id = $rf_data->ID;
		}
		
		return $refund_id;
	}
	
	public function get_wc_ord_smart_coupon_discount_amount($invoice_data){
		$_cart_discount = 0;
		if(is_array($invoice_data) && isset($invoice_data['_recorded_coupon_usage_counts']) && $invoice_data['_recorded_coupon_usage_counts'] == 'yes'){
			$used_coupons  = (isset($invoice_data['used_coupons']))?$invoice_data['used_coupons']:array();
			if(!empty($used_coupons)){
				foreach($used_coupons as $coupon){
					if(strpos($coupon['coupon_data'], 'smart_coupon') !== false){
						$coupon_discount_amount = (float) $coupon['discount_amount'];
						//$coupon_discount_amount_tax = (float) $coupon['discount_amount_tax'];
						$_cart_discount += $coupon_discount_amount;
					}					
				}
			}
		}
		return $_cart_discount;
	}
	
	public function get_wc_ord_get_discount_amount_from_coupons($invoice_data,$d_tax=false){
		$_cart_discount = 0;
		if(is_array($invoice_data)){
			$used_coupons  = (isset($invoice_data['used_coupons']))?$invoice_data['used_coupons']:array();
			if(!empty($used_coupons)){
				foreach($used_coupons as $coupon){
					$coupon_discount_amount = (float) $coupon['discount_amount'];					
					$_cart_discount += $coupon_discount_amount;
					
					if($d_tax){
						$coupon_discount_amount_tax = (float) $coupon['discount_amount_tax'];
						$_cart_discount += $coupon_discount_amount_tax;
					}					
				}
			}
		}
		return $_cart_discount;
	}
	
	/**/
	public function get_woo_ord_number_key_field(){
		$onk_f = '';
		if($this->option_checked('mw_wc_qbo_sync_compt_p_wsnop')){
			//woocommerce-sequential-order-numbers
			if($this->is_plugin_active('woocommerce-sequential-order-numbers-pro','',true)){					
				$onk_f = '_order_number_formatted';
			}				
			
			if($this->is_only_plugin_active('woocommerce-sequential-order-numbers')){
				$onk_f = '_order_number';
			}
			
			if($this->is_plugin_active('custom-order-numbers-for-woocommerce')){
				$onk_f = '_alg_wc_custom_order_number';
			}
			
			if(empty($onk_f) && !empty($this->get_option('mw_wc_qbo_sync_compt_p_wconmkn'))){
				$onk_f = $this->get_option('mw_wc_qbo_sync_compt_p_wconmkn');
			}
		}
		
		return $onk_f;
	}
	
	/**/
	public function get_qbo_cus_tax_exem_rsn_id_list(){
		return array(
			'1' => 'Federal government',
			'2' => 'State government',
			'3' => 'Local government',
			'4' => 'Tribal government',
			'5' => 'Charitable organization',
			'6' => 'Religious organization',
			'7' => 'Educational organization',
			'8' => 'Hospital',
			'9' => 'Resale',
			'10' => 'Direct pay permit',
			'11' => 'Multiple points of use',
			'12' => 'Direct mail',
			'13' => 'Agricultural production',
			'14' => 'Industrial production / manufacturing',
			'15' => 'Foreign diplomat',			
		);
	}
	
	public function get_order_sync_to_qbo_as($order_id){
		$q_osa = 'Invoice';
		if($this->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
			$q_osa = 'SalesReceipt';
		}
		
		if($this->option_checked('mw_wc_qbo_sync_order_as_estimate')){
			$q_osa = 'Estimate';
		}
		
		$order_id = (int) $order_id;
		if($order_id > 0){
			$qost_arr = array(
				'Invoice' => 'Invoice',
				'SalesReceipt' => 'SalesReceipt',
				'Estimate' => 'Estimate',
			);
			
			if($this->get_option('mw_wc_qbo_sync_order_qbo_sync_as') == 'Per Role'){
				$wc_user_role = '';
				$wc_cus_id = (int) get_post_meta($order_id,'_customer_user',true);
				if($wc_cus_id > 0){
					$user_info = get_userdata($wc_cus_id);
					if(isset($user_info->roles) && is_array($user_info->roles)){
						$wc_user_role = $user_info->roles[0];
					}
				}else{
					$wc_user_role = 'wc_guest_user';
				}
				//$this->_p($wc_user_role);
				if(!empty($wc_user_role)){
					$mw_wc_qbo_sync_oqsa_pr_data = get_option('mw_wc_qbo_sync_oqsa_pr_data');
					if(is_array($mw_wc_qbo_sync_oqsa_pr_data) && !empty($mw_wc_qbo_sync_oqsa_pr_data)){
						if(isset($mw_wc_qbo_sync_oqsa_pr_data[$wc_user_role]) && !empty($mw_wc_qbo_sync_oqsa_pr_data[$wc_user_role])){
							if(isset($qost_arr[$mw_wc_qbo_sync_oqsa_pr_data[$wc_user_role]])){
								$q_osa = $mw_wc_qbo_sync_oqsa_pr_data[$wc_user_role];
							}								
						}
					}
				}
			}
			
			if($this->get_option('mw_wc_qbo_sync_order_qbo_sync_as') == 'Per Gateway'){
				$_payment_method = get_post_meta($order_id,'_payment_method',true);
				$_order_currency = get_post_meta($order_id,'_order_currency',true);
				
				if(!empty($_payment_method) && !empty($_order_currency)){
					$pm_map_data = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);
					$order_sync_as = $this->get_array_isset($pm_map_data,'order_sync_as','',true);
					if(!empty($order_sync_as) && isset($qost_arr[$order_sync_as])){
						$q_osa = $order_sync_as;
					}
				}
			}
		}
		
		if($q_osa!= 'Invoice' && $q_osa!= 'SalesReceipt' && $q_osa!= 'Estimate'){
			$q_osa = 'Invoice';
		}
		//echo $q_osa;
		return $q_osa;
	}
	
	public function is_order_sync_as_sales_receipt($order_id=0){
		if($this->get_order_sync_to_qbo_as($order_id) == 'SalesReceipt'){
			return true;
		}
		return false;
	}
	
	//
	public function is_order_sync_as_estimate($order_id=0){
		if($this->get_order_sync_to_qbo_as($order_id) == 'Estimate'){
			return true;
		}
		return false;
	}
	
	//
	public function is_order_sync_as_invoice($order_id=0){
		if($this->get_order_sync_to_qbo_as($order_id) == 'Invoice'){
			return true;
		}
		return false;
	}
	
	public function get_qbo_customer_email_by_id_from_db($qbo_customerid){
		global $wpdb;
		$qbo_customerid = (int) $qbo_customerid;
		if($qbo_customerid > 0){
			return $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_customers','email','qbo_customerid',$qbo_customerid);
		}
		return '';
	}
	
	/**/
	public function week_day_int_to_str_w($wd){
		$wd = (int) $wd;
		if($wd > 0 && $wd <8){
			$wdis_a = array(
				'1' => 'Sunday','2' => 'Monday','3' => 'Tuesday','4' => 'Wednesday',
				'5' => 'Thursday','6' => 'Friday','7' => 'Saturday'
			);
			if(isset($wdis_a[$wd])){
				return $wdis_a[$wd];
			}
		}
		return '';
	}
	
	public function mbl_lwb_remove_weekend_orders($oi,$odt,$od,$ods_s,$wp_date_time_c){
		//$this->_p($oi);$this->_p($odt);$this->_p($od);$this->_p($ods_s);
		if(is_array($oi) && is_array($odt) && is_array($od)){
			if(count($oi) == count($oi)){
				$u_keys = array();
				
				$d_time = (int) date('Gi',strtotime($wp_date_time_c));
				foreach($od as $k => $v){
					$irwo = false;					
					$o_time = (int) date('Gi',strtotime($odt[$k]));					
					
					if($v == '6' ){	
						if($o_time > $d_time){
							$irwo = true;
						}
					}
					
					if($v == '2'){
						//echo '**'.$o_time.'->'.$d_time.'<br>';
						if($o_time < $d_time){							
							$irwo = true;
						}
					}
					
					if($v == '7' || $v == '1'){
						$irwo = true;
					}
					
					if($irwo){
						$u_keys[] = $k;
					}
				}
				
				if(!empty($u_keys)){
					foreach($u_keys as $ukv){
						unset($oi[$ukv]);
						unset($odt[$ukv]);
						unset($od[$ukv]);
						unset($ods_s[$ukv]);
					}
				}
			}
		}		
		//$this->_p($oi);$this->_p($odt);$this->_p($od);$this->_p($ods_s);
		return array(
			'order_ids' => $oi,
			'order_datetimes' => $odt,
			'order_days' => $od,
			'order_days_s' => $ods_s,
		);
	}
	
	/**/
	public function Import_All_QB_Inventory(){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			
			$ItemService = new QuickBooks_IPP_Service_Item();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $ItemService->query($Context, $realm, "SELECT COUNT(*)  FROM Item WHERE Type = 'Inventory' ");
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$items = $ItemService->query($Context, $realm, "SELECT Id, Name, QtyOnHand FROM Item WHERE Type = 'Inventory' STARTPOSITION $startPos MaxResults $qboMaxLimit ");
				if($items && !empty($items)){
					foreach($items as $Item){
						$item_id = $this->qbo_clear_braces($Item->getId());
						$Name = $Item->getName();
						$QtyOnHand = $Item->getQtyOnHand();
						$return_id = $this->UpdateWooCommerceInventory(array('qbo_inventory_id'=>$item_id,'Name'=>$Name,'QtyOnHand'=>$QtyOnHand,'cron'=>true));
					}
				}
			}
		}
	}
	
	/**/
	public function Import_All_QB_Pricing(){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			
			$ItemService = new QuickBooks_IPP_Service_Item();
			$qboMaxLimit = $this->qbo_query_limit;
			$totalCount = $ItemService->query($Context, $realm, "SELECT COUNT(*)  FROM Item WHERE Type IN ('Inventory','Service','NonInventory') ");//,'Group'
			$batchCount =  ($qboMaxLimit >= $totalCount) ? 1 : ceil($totalCount / $qboMaxLimit);
			
			$qdf = ($this->get_option('mw_wc_qbo_sync_product_pull_wpn_field') == 'Description')?', Description':'';
			for ($i=0; $i<$batchCount; $i++) {
				$startPos = $i*$qboMaxLimit;
				$items = $ItemService->query($Context, $realm, "SELECT Id, Name, Type, Sku, UnitPrice {$qdf} FROM Item WHERE Type IN ('Inventory','Service','NonInventory') STARTPOSITION $startPos MaxResults $qboMaxLimit ");//,'Group'
				if($items && !empty($items)){
					foreach($items as $Item){
						$item_id = $this->qbo_clear_braces($Item->getId());
						//$Name = $Item->getName();						
						$return_id = $this->Qbo_Pull_Product(array('qbo_product_id'=>$item_id,'webhook'=>false),true,$Item);
					}
				}
			}
		}
	}
	
	/**/
	public function get_woo_refund_id_from_order_id($order_id){
		$refund_id = 0;
		$order_id = (int) $order_id;
		if($order_id){
			global $wpdb;			
			$rfd_q = $wpdb->prepare("SELECT ID FROM `{$wpdb->posts}` WHERE `post_type` = 'shop_order_refund' AND `post_parent` = %d ORDER BY ID DESC LIMIT 0,1 ",$order_id);
			$rf_data = $wpdb->get_row($rfd_q);
			if(is_object($rf_data) && !empty($rf_data)){
				$refund_id = $rf_data->ID;
			}
		}
		
		return $refund_id;
	}
	
	/**/
	public function get_frl_c_qb_tlt_val($o_data){
		$tlt_v = '';
		if($this->get_qbo_company_info('country') == 'FR'){
			if(is_array($o_data) && isset($o_data['_billing_country']) && !empty($o_data['_billing_country'])){
				$country = $this->get_array_isset($o_data,'_billing_country','',true);				
				$tlt_v = 'OutsideEU';
				
				if($country == 'FR'){
					$tlt_v = 'WithinFrance';
				}
				
				$eu_countries = array(
					"AT" => "Austria",
					"BE" => "Belgium",
					"BG" => "Bulgaria",
					"HR" => "Croatia",
					"CY" => "Cyprus",
					"CZ" => "Czech Republic",
					"DK" => "Denmark",
					"EE" => "Estonia",
					"FI" => "Finland",
					//"FR" => "France",
					"DE" => "Germany",
					"GR" => "Greece",
					"HU" => "Hungary",
					"IE" => "Ireland",
					"IT" => "Italy",
					"LV" => "Latvia",
					"LT" => "Lithuania",
					"LU" => "Luxembourg",
					"MT" => "Malta",
					"NL" => "Netherlands",
					"PL" => "Poland",
					"PT" => "Portugal",
					"RO" => "Romania",
					"SK" => "Slovakia (Slovak Republic)",
					"SI" => "Slovenia",
					"ES" => "Spain",
					"SE" => "Sweden",
					//"RD" => "Local IP" // added for testing purposes
				);
				
				if(isset($eu_countries[$country])){
					$tlt_v = 'OutsideFranceWithEU';
				}
				
				$fod_countries = array(
					"MQ" => "Martinique",
					"GP" => "Guadeloupe",
					"RE" => "Reunion Island",
				);
				
				if(isset($fod_countries[$country])){
					$tlt_v = 'FranceOverseas';
				}
			}
		}
		
		return $tlt_v;
	}
	
	public function _abs($n){
		if(!strlen($n)){
			$n = 0;
		}
		
		if(!is_int($n) && !is_float($n)){
			$n = floatval($n);
		}
		
		return abs($n);
	}

	public function get_order_txn_fee_data_by_id($order_id){
		if(is_numeric($order_id)){$order_id = (int) $order_id;}
		if(is_int($order_id) && $order_id > 0){			
			$order_meta = get_post_meta($order_id);
			if(is_array($order_meta) && !empty($order_meta)){
				$invoice_data = [];
				foreach ($order_meta as $key => $value){
					$omv = ($value[0])?$value[0]:'';
					$invoice_data[$key] = $omv;
				}

				return $this->get_txn_fee_ld_f_o_data($invoice_data);
			}
		}
	}
	
	#New
	public function get_txn_fee_ld_f_o_data($invoice_data){
		$t_f_desc = 'Transaction Fee';
		$t_f_amnt = 0;
		$tfk = '';
		
		if(is_array($invoice_data) && !empty($invoice_data)){
			$pm_c = $this->get_array_isset($invoice_data,'_payment_method','',true);
			$_p_m_t = $this->get_array_isset($invoice_data,'_payment_method_title','',true);

			$pm_c_ori = $pm_c;
			$_p_m_t_ori = $_p_m_t;
			
			if(!empty($pm_c)){$pm_c = strtolower($pm_c);}
			if(!empty($_p_m_t)){$_p_m_t = strtolower($_p_m_t);}
			
			#New
			$skip_pm_pmt_c = true;
			
			if($skip_pm_pmt_c || strpos($pm_c, 'stripe') !== false || strpos($_p_m_t, 'stripe') !== false){
				$isf = false;
				if(isset($invoice_data['_stripe_fee'])){
					$isf = true;
					$tfk = '_stripe_fee';
				}else{
					if(isset($invoice_data['Stripe Fee'])){
						$isf = true;
						$tfk = 'Stripe Fee';
					}
				}
				
				if($isf){
					$t_f_desc = 'Stripe Fee';
				}				
			}			
			
			if($skip_pm_pmt_c || strpos($pm_c, 'paypal') !== false || strpos($_p_m_t, 'paypal') !== false){
				$ipf = false;
				
				#New WooCommerce PayPal gateway fee support
				if(isset($invoice_data['_ppcp_paypal_fees']) && !empty($invoice_data['_ppcp_paypal_fees'])){
					$_ppcp_paypal_fees = unserialize($invoice_data['_ppcp_paypal_fees']);
					//$this->_p($_ppcp_paypal_fees);
					if(is_array($_ppcp_paypal_fees) && isset($_ppcp_paypal_fees['paypal_fee']) && !empty($_ppcp_paypal_fees['paypal_fee'])){
						if($_ppcp_paypal_fees['paypal_fee']['currency_code'] == $invoice_data['_order_currency']){
							$invoice_data['_paypal_transaction_fee'] = floatval($_ppcp_paypal_fees['paypal_fee']['value']);
							$ipf = true;
							$tfk = '_paypal_transaction_fee';
						}
					}
				}

				if(isset($invoice_data['_paypal_transaction_fee'])){
					$ipf = true;
					$tfk = '_paypal_transaction_fee';
				}else{
					if(isset($invoice_data['_paypal_fee'])){
						$ipf = true;
						$tfk = '_paypal_fee';
					}else{
						if(isset($invoice_data['PayPal Transaction Fee'])){
							$ipf = true;
							$tfk = 'PayPal Transaction Fee';
						}
					}
				}
				
				if($ipf){
					$t_f_desc = 'PayPal Transaction Fee';
				}				
			}
			
			#New
			if($this->is_plugin_active('woocommerce-gateway-affirm')){
				if(isset($invoice_data['_wc_gateway_affirm_fee_amount'])){
					$tfk = '_wc_gateway_affirm_fee_amount';
					$t_f_desc = 'Affirm Transaction Fee';
				}
			}

			#New
			if($pm_c_ori == 'woocommerce_payments'){
				if(isset($invoice_data['_wcpay_transaction_fee'])){
					$tfk = '_wcpay_transaction_fee';
				}
			}

			if($pm_c_ori == 'payfast'){
				if(isset($invoice_data['payfast_amount_fee'])){
					$tfk = 'payfast_amount_fee';
					$t_f_desc = 'PayFast Transaction Fee';
				}
			}
			
			if(empty($tfk) && isset($invoice_data['_transaction_fee'])){
				$tfk = '_transaction_fee';
			}
			
			if(empty($tfk) && isset($invoice_data['transaction_fee'])){
				$tfk = 'transaction_fee';
			}
			
			if(!empty($tfk) && isset($invoice_data[$tfk])){
				$t_f_amnt = (float) $this->get_array_isset($invoice_data,$tfk,0);
				
				$t_f_amnt = abs($t_f_amnt); # negative
				
				#New
				if($tfk == '_wc_gateway_affirm_fee_amount' && $t_f_amnt > 0){
					$t_f_amnt = $t_f_amnt/100;
				}
			}
		}		
		
		$t_f_a = array(
			't_f_desc' => $t_f_desc,
			't_f_amnt' => $t_f_amnt,
		);
		
		return $t_f_a;
	}
	
	#New
	public function PushProductImg($qbp_id,$product_data,$is_variation=false){
		if(!$this->is_connected()){
			return false;
		}
		
		$sync_item = ($is_variation)?'Variation':'Product';
		$wc_product_id = (int) $this->get_array_isset($product_data,'wc_product_id',0,true);
		$p_m_img_url = $this->get_array_isset($product_data,'p_m_img_url','');
		#p_t_img_url
		$filename = basename($p_m_img_url);
		#$s_mime_type = "image/jpeg";
		
		if($qbp_id > 0 && !empty($p_m_img_url) && !empty($filename)){
			$AttachableService = new QuickBooks_IPP_Service_Attachable();
			$Attachable = new QuickBooks_IPP_Object_Attachable();
			$Attachable->setFileName($filename);
			
			$AttachableRef = new QuickBooks_IPP_Object_AttachableRef();
			
			$AttachableRef->setEntityRef("{-$qbp_id}");
			$AttachableRef->setEntityRef_type('Item');			
			$AttachableRef->setIncludeOnSend('false');
			
			$Attachable->addAttachableRef($AttachableRef);
			
			$Attachable->setCategory('Image');
			#Tag Tag_{Img_FN_Without_Ext}
			
			$Attachable->set('sparse','false');
			$Attachable->set('domain','QBO');
			
			#$this->_p($Attachable);			
			
			$xml = $Attachable->asXML(0, null, null, null, QuickBooks_IPP_IDS::VERSION_3);
			
			$Context = $this->Context;
			$realm = $this->realm;			
			
			$img_base64 = $this->img_to_base64($p_m_img_url);
			#$filename = basename($p_m_img_url);
			$mimeType = $this->qbo_get_image_mime_type($p_m_img_url);
			
			if(empty($filename) || ($mimeType != 'image/png' && $mimeType != 'image/jpg' && $mimeType != 'image/jpeg')){
				return false;
			}			
			
			$log_title = "";
			$log_details = "";
			$log_status = 0;
			
			$img_for_s = $img_base64;
			#$img_for_s = base64_decode($img_base64);
			$resp = $AttachableService->upload($Context, $realm, $Attachable,$img_for_s,$filename,$mimeType);
			$lastResponse = $this->get_IPP()->lastResponse();
			if (strpos($lastResponse, '<Id>') !== false && strpos($lastResponse, '</TempDownloadUri>') !== false) {
				$Id = $this->get_string_between($lastResponse,'<Id>','</Id>');
				$TempDownloadUri = $this->get_string_between($lastResponse,'<TempDownloadUri>','</TempDownloadUri>');
				if(!empty($Id) && !empty($TempDownloadUri)){
					#$lastRequest = $this->get_IPP()->lastRequest();
					#$lastResponse = $this->get_IPP()->lastResponse();
					#$this->add_qbo_item_obj_into_log_file('P Img Add Success',$product_data,$Attachable,$lastRequest,$lastResponse,true);
					
					#echo $Id;
					$resp = $Id;
					#Attachable Update
					$aud = array();
					$aud['qbp_id'] = $qbp_id;
					$aud['e_type'] = 'Item';
					#$this->AttachableUpdate($Id,$aud);
				}
			}
			
			/*
			if ($resp){
				$lastRequest = $this->get_IPP()->lastRequest();
				#$lastResponse = $this->get_IPP()->lastResponse();
				#$this->_p($resp);
				#$this->_p($lastRequest);
				#$this->_p($lastResponse);
				
				#$log_status = 1;
				#$this->add_qbo_item_obj_into_log_file('P Img Add Success',$product_data,$Attachable,$lastRequest,$lastResponse,true);
			}else{
				$res_err = $AttachableService->lastError($Context);
				#$lastRequest = $this->get_IPP()->lastRequest();
				#$lastResponse = $this->get_IPP()->lastResponse();
				
				#$this->_p($res_err);
				#$this->_p($lastRequest);
				#$this->_p($lastResponse);
				#$this->add_qbo_item_obj_into_log_file('P Img Add Error',$product_data,$Attachable,$lastRequest,$lastResponse,true);
			}
			*/
		}		
	}
	
	public function AttachableUpdate($Id,$aud){
		if(!$this->is_connected()){
			return false;
		}
		
		$Context = $this->Context;
		$realm = $this->realm;
		
		$AttachableService = new QuickBooks_IPP_Service_Attachable();
		$AQ = "SELECT * FROM Attachable WHERE Id='{$Id}'";
			
		$Attachables = $AttachableService->query($Context,$realm,$AQ);
		if($Attachables && !empty($Attachables)){
			$Attachable = $Attachables[0];
			#$this->_p($Attachable);			
			
			$AttachableRef = new QuickBooks_IPP_Object_AttachableRef();
			$qbp_id = $aud['qbp_id'];
			$AttachableRef->setEntityRef("{-$qbp_id}");
			$AttachableRef->setEntityRef_type($aud['e_type']);
			$AttachableRef->setIncludeOnSend(false);
			
			$Attachable->addAttachableRef($AttachableRef);
			
			#$Attachable->setNote('This is an updated attached note.');
			$Attachable->set('sparse','false');
			$Attachable->set('domain','QBO');
			
			#$this->_p($Attachable);
			if ($resp = $AttachableService->update($Context, $realm, $Attachable->getId(), $Attachable)){
				#$this->_p($resp);
				$lastRequest = $this->get_IPP()->lastRequest();
				$lastResponse = $this->get_IPP()->lastResponse();
				$this->add_qbo_item_obj_into_log_file('Attachable Update',$aud,$Attachable,$lastRequest,$lastResponse,true);
			}else{
				$res_err = $AttachableService->lastError($Context);
				#$this->_p($res_err);
			}
		}
	}
	
	public function Pull_Product_Image($qbp_id,$wpv_id,$is_variation=false,$is_update=false){
		if($this->is_connected()){
			$Context = $this->Context;
			$realm = $this->realm;
			
			$AttachableService = new QuickBooks_IPP_Service_Attachable();
			$AQ = "SELECT * FROM Attachable WHERE AttachableRef.EntityRef.Type = 'Item' and AttachableRef.EntityRef.value = '{$qbp_id}'";
			$Attachables = $AttachableService->query($Context,$realm,$AQ);
			if($Attachables && !empty($Attachables)){
				$Attachable = $Attachables[0];
				#$this->_p($Attachable);
				
				$A_Id = $this->qbo_clear_braces($Attachable->getId());
				$A_Img_Url = $Attachable->getTempDownloadUri();
				$A_Img_Name = $Attachable->getFileName();
				
				if($A_Id > 0 && $wpv_id > 0 && !empty($A_Img_Url) && !empty($A_Img_Name)){
					$this->wp_img_url_add_post_featured_image($A_Img_Url,$wpv_id,$A_Img_Name,$is_variation,$is_update);
				}
			}
		}		
	}
	
	#New
	public function check_attachable_exists_by_entity($ent_id,$ent_type,$r_arr=true){
		$ent_id = (int) $ent_id;
		if($this->is_connected() && $ent_id > 0 && !empty($ent_type)){
			$Context = $this->Context;
			$realm = $this->realm;
			
			$AttachableService = new QuickBooks_IPP_Service_Attachable();
			$AQ = "SELECT * FROM Attachable WHERE AttachableRef.EntityRef.Type = '{$ent_type}' and AttachableRef.EntityRef.value = '{$ent_id}'";
			#echo $AQ;
			$Attachables = $AttachableService->query($Context,$realm,$AQ);
			
			if($Attachables && !empty($Attachables)){
				$Attachable = $Attachables[0];
				if($r_arr){
					return array(
						'Id' => $this->qbo_clear_braces($Attachable->getId()),
						'TempDownloadUri' => $Attachable->getTempDownloadUri(),
						'FileName' => $Attachable->getFileName(),
					);
				}
				return true;
			}			
			return false;
		}
		
		return false;
	}
	
	public function wp_img_url_add_post_featured_image( $image_url, $post_id, $filename, $is_variation=false, $is_product_update=false ){
		if(empty($image_url) || empty($filename) || (int)  $post_id < 1){
			return false;
		}
		
		$image_data = file_get_contents($image_url);
		#$filename = basename($image_url);
		
		$upload_dir = wp_upload_dir();
		if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
		else                                    $file = $upload_dir['basedir'] . '/' . $filename;
		file_put_contents($file, $image_data);

		$wp_filetype = wp_check_filetype($filename, null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => sanitize_file_name($filename),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		
		/*Prevent same name image when updating woocommerce product*/
		if($is_product_update){
			$old_img_dtls = $this->get_post_img_data_by_id($post_id);
			if(is_array($old_img_dtls) && !empty($old_img_dtls) && !empty($old_img_dtls[0])){
				$o_img_url = trim($old_img_dtls[0]);
				$p_info = pathinfo($o_img_url);
				if(is_array($p_info) && !empty($p_info)){
					if(isset($p_info['basename'])){
						$o_img_bn = $p_info['basename'];
					}else{
						$o_img_bn = $p_info['filename'].'.'.$p_info['extension'];
					}
					
					if(strtolower($o_img_bn) == strtolower($filename)){
						return false;
					}
				}
			}
			
			$old_featured_img_id = get_post_thumbnail_id($post_id);
			//$old_featured_img_id = '';
			if(!empty($old_featured_img_id)){
				wp_delete_attachment( $old_featured_img_id, true );
			}
		}
		
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		$res1= wp_update_attachment_metadata( $attach_id, $attach_data );
		$res2= set_post_thumbnail( $post_id, $attach_id );
		
		$wpv_txt = ($is_variation)?'Variation':'Product';
		$log_title = '';$log_details = '';
		if($res2){
			//
			$log_title.="WooCommerce Product Image Add #$post_id\n";
			$log_details.="Product image has been imported into WooCommerce\n";
			$log_details.="Filename: {$filename}";
			$log_status = 1;			
			#$this->save_log($log_title,$log_details,'Product',$log_status,true,'Add');
		}
	}
	
	public function get_post_img_data_by_id($p_id){
		$p_id = (int) $p_id;
		if($p_id > 0){
			$p_img_data = wp_get_attachment_image_src( get_post_thumbnail_id( $p_id ), 'single-post-thumbnail' );
			if(is_array($p_img_data) && !empty($p_img_data) && !empty($p_img_data[0])){
				return $p_img_data;
			}
		}
		return array();
	}
	
	public function img_to_base64($img_path){
		$i_b64 = '';
		if(!empty($img_path)){
			#$type = pathinfo($img_path, PATHINFO_EXTENSION);
			$data = file_get_contents($img_path);
			//$i_b64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
			//$data = utf8_encode($data);
			$i_b64 = base64_encode($data);
		}
		
		return $i_b64;
	}
	
	public function base64_to_img_file($base64_string, $output_file) {
		// open the output file for writing
		$ifp = fopen( $output_file, 'wb' ); 

		// split the string on commas
		// $data[ 0 ] == "data:image/png;base64"
		// $data[ 1 ] == <actual base64 string>
		$data = explode( ',', $base64_string );

		// we could add validation here with ensuring count( $data ) > 1
		fwrite( $ifp, base64_decode( $data[ 1 ] ) );

		// clean up the file resource
		fclose( $ifp ); 

		return $output_file; 
	}
	
	public function getBytesFromHexString($hexdata){
		for($count = 0; $count < strlen($hexdata); $count+=2)
			$bytes[] = chr(hexdec(substr($hexdata, $count, 2)));

		return implode($bytes);
	}
	
	public function get_base64_img_type($encoded_string){
		$mime_type = '';
		if(!empty($encoded_string)){			
			$imgdata = base64_decode($encoded_string);
			/*
			$f = finfo_open();
			$mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
			finfo_close($f);
			*/
			
			$imagemimetypes = array( 
				//"jpeg" => "FFD8",
				"jpg" => "FFD8",
				"png" => "89504E470D0A1A0A", 
				"gif" => "474946",
				"bmp" => "424D", 
				//"tiff" => "4949",
				//"tiff" => "4D4D"
			);
			
			foreach ($imagemimetypes as $mime => $hexbytes){
				$bytes = $this->getBytesFromHexString($hexbytes);
				if (substr($imgdata, 0, strlen($bytes)) == $bytes)
				$mime_type = $mime;
			}
		}
		
		//if($mime_type == 'jpeg' && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){}
		
		return $mime_type;
	}
	
	function qbo_get_image_mime_type($image_path){
		$mimes  = array(
			IMAGETYPE_GIF => "image/gif",
			IMAGETYPE_JPEG => "image/jpg",			
			IMAGETYPE_PNG => "image/png",
			IMAGETYPE_SWF => "image/swf",
			IMAGETYPE_PSD => "image/psd",
			IMAGETYPE_BMP => "image/bmp",
			IMAGETYPE_TIFF_II => "image/tiff",
			IMAGETYPE_TIFF_MM => "image/tiff",
			IMAGETYPE_JPC => "image/jpc",
			IMAGETYPE_JP2 => "image/jp2",
			IMAGETYPE_JPX => "image/jpx",
			IMAGETYPE_JB2 => "image/jb2",
			IMAGETYPE_SWC => "image/swc",
			IMAGETYPE_IFF => "image/iff",
			IMAGETYPE_WBMP => "image/wbmp",
			IMAGETYPE_XBM => "image/xbm",
			IMAGETYPE_ICO => "image/ico");

		if (($image_type = exif_imagetype($image_path))	&& (array_key_exists($image_type ,$mimes))){
			return $mimes[$image_type];
		}
		else{
			return FALSE;
		}
	}	
	
	#New
	public function PushOrderDocument($qbo_id,$os_type,$invoice_data,$cf_map_data,$cfm_iv){
		if(!$this->is_connected()){
			return false;
		}
		
		$qbo_id = (int) $qbo_id;
		if(is_array($cf_map_data) && !empty($cf_map_data) && $qbo_id > 0 && !empty($os_type) && is_array($invoice_data) && !empty($invoice_data)){
			if(is_array($cfm_iv) && !empty($cfm_iv)){
				$isod = false;
				foreach($cf_map_data as $wcfm_k_id => $wcfm_v){
					$wcfm_k = $this->get_woo_field_fm_cmd($wcfm_k_id,$cfm_iv);
					$wcfm_k = trim($wcfm_k);
					$wcfm_v = trim($wcfm_v);
					if($wcfm_v == 'O_S_Attachments' && isset($invoice_data[$wcfm_k]) && !empty($invoice_data[$wcfm_k])){
						if(!$this->check_attachable_exists_by_entity($qbo_id,$os_type,false)){
							$od_url = trim($invoice_data[$wcfm_k]);
							if(filter_var($od_url, FILTER_VALIDATE_URL) !== FALSE) {
								$od_fn = basename($od_url);
								if(empty($od_fn)){
									return false;
								}
								
								$od_fn = strtok($od_fn, '?');
								#$od_headers = get_headers($od_url, 1);
								#$od_headers['Content-Type'] == 'image/'
								
								$od_fe = pathinfo($od_fn, PATHINFO_EXTENSION);
								
								#New - Supported Doc Types
								$smte_arr = array(
									#Text PDF CSV
									'pdf' => 'application/pdf',
									'csv' => 'text/csv',
									'txt' => 'text/plain',
									
									#MS Ofice
									'doc' => 'application/msword',
									'docx' => 'application/msword',
									
									#'xls' => 'application/vnd/ms-excel',
									#'xlsx' => 'application/vnd/ms-excel',
									
									#Image
									'jpg' => 'image/jpg',
									'jpeg' => 'image/jpeg',
									'png' => 'image/png',									
								);								
								
								if(!empty($od_fe) && isset($smte_arr[$od_fe]) && !empty($smte_arr[$od_fe])){
									$is_img_doc = false;
									if($od_fe == 'jpg' || $od_fe == 'jpeg' || $od_fe == 'png'){
										$is_img_doc = true;
									}
									
									$mimeType = $smte_arr[$od_fe];
									
									if($is_img_doc){
										$mimeType = $this->qbo_get_image_mime_type($od_url);			
										if(empty($od_fn) || ($mimeType != 'image/png' && $mimeType != 'image/jpg' && $mimeType != 'image/jpeg')){
											return false;
										}										
									}else{
										#other mime types checks to be done if needed
									}
									
									$AttachableService = new QuickBooks_IPP_Service_Attachable();
									$Attachable = new QuickBooks_IPP_Object_Attachable();
									$Attachable->setFileName($od_fn);
									
									$AttachableRef = new QuickBooks_IPP_Object_AttachableRef();
									$AttachableRef->setEntityRef("{-$qbo_id}");
									$AttachableRef->setEntityRef_type($os_type);		
									$AttachableRef->setIncludeOnSend('false');
									
									$Attachable->addAttachableRef($AttachableRef);
									
									if($is_img_doc){
										$Attachable->setCategory('Image');
									}else{
										$Attachable->setCategory('Document');
									}
									
									$Attachable->set('sparse','false');
									$Attachable->set('domain','QBO');
									
									#$this->_p($Attachable);
									$xml = $Attachable->asXML(0, null, null, null, QuickBooks_IPP_IDS::VERSION_3);
									
									#echo '<textarea>'.$xml.'</textarea>';
									#return;
									
									$Context = $this->Context;
									$realm = $this->realm;
									
									$f_base64 = $this->img_to_base64($od_url);
									
									#$log_title = "";$log_details = "";$log_status = 0;
									$resp = $AttachableService->upload($Context, $realm, $Attachable,$f_base64,$od_fn,$mimeType);
									
									#$lastRequest = $this->get_IPP()->lastRequest();
									#$lastResponse = $this->get_IPP()->lastResponse();
									#$this->add_qbo_item_obj_into_log_file('Order Doc Sync Debug',$invoice_data,$Attachable,$lastRequest,$lastResponse,true);
								}
							}
						}						
						
						break;
					}
				}
			}			
		}		
	}

	# New
	public function is_sync_estimate_payment($invoice_data){		
		$_paid_date = $this->get_array_isset($invoice_data,'_paid_date','',true);
		if(empty($_paid_date)){
			return false;
		}

		$_transaction_id = $this->get_array_isset($invoice_data,'_transaction_id','',true);
		if(empty($_transaction_id)){
			return false;
		}

		$_order_currency = $this->get_array_isset($invoice_data,'_order_currency','',true);
		$_payment_method = $this->get_array_isset($invoice_data,'_payment_method','',true);

		$payment_method_map_data  = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);

		$enable_payment = $this->get_array_isset($payment_method_map_data,'enable_payment',0,false);
		if($enable_payment == 1){
			return true;
		}

		return false;
	}

	public function PushEstimatePayment($payment_data){
		if($this->is_connected()){
			$wc_inv_id = (int) $this->get_array_isset($payment_data,'wc_inv_id',0);
			$wc_inv_num = $this->get_array_isset($payment_data,'wc_inv_num','');

			$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;

			# Checkings
			$_paid_date = $this->get_array_isset($payment_data,'wc_inv_date','',true);
			if(empty($_paid_date)){
				return false;
			}

			$_transaction_id = $this->get_array_isset($payment_data,'_transaction_id','',true);

			if(empty($_transaction_id)){
				return false;
			}

			$payment_amount = (float) $this->get_array_isset($payment_data,'_order_total',0);
			if($payment_amount <= 0){
				return false;
			}
			
			$qbo_customerid = (int) $this->get_array_isset($payment_data,'qbo_customerid',0);

			if(!$qbo_customerid){
				$this->save_log('Export Payment Error Order #'.$ord_id_num,'QuickBooks customer not found!','Payment',0);
				return false;
			}			

			if(!$this->check_os_payment_get_obj($payment_data)){
				$Context = $this->Context;
				$realm = $this->realm;

				$PaymentService = new QuickBooks_IPP_Service_Payment();
				$Payment = new QuickBooks_IPP_Object_Payment();

				$_order_currency = $this->get_array_isset($payment_data,'_order_currency','',true);

				$_payment_method = $this->get_array_isset($payment_data,'_payment_method','',true);
				$_payment_method_title = $this->get_array_isset($payment_data,'_payment_method_title','',true);

				$pm_map_data = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);

				$enable_payment = (int) $this->get_array_isset($pm_map_data,'enable_payment',0);

				if($enable_payment && $payment_amount>0){
					$qb_p_method_id = (int) $this->get_array_isset($pm_map_data,'qb_p_method_id',0);
					$qbo_account_id = (int) $this->get_array_isset($pm_map_data,'qbo_account_id',0);

					$RefNum = $ord_id_num;
					if($this->get_option('mw_wc_qbo_sync_qb_pmnt_ref_num_vf') == 'TXN_ID' && !empty($_transaction_id)){
						$RefNum = $_transaction_id;
					}
					
					if(strlen($RefNum) > 21){
						$RefNum = substr($RefNum,-21);
					}
					
					$Payment->setPaymentRefNum($RefNum);

					$_paid_date = $this->view_date($_paid_date);
					$Payment->setTxnDate($_paid_date);

					//Payment Currency
					$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
					if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){

						$currency_rate_date = $_paid_date;
						$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);

						$Payment->setCurrencyRef($_order_currency);
						$Payment->setExchangeRate($currency_rate);
					}

					if($qb_p_method_id>0){
						 $Payment->setPaymentMethodRef($qb_p_method_id);
					}

					$dtar_acc_id = $qbo_account_id;
					$Payment->setTotalAmt($payment_amount);

					$Payment->setCustomerRef("{-$qbo_customerid}");
					$Payment->setDepositToAccountRef("{-$dtar_acc_id}");

					$log_title = "";
					$log_details = "";
					$log_status = 0;
					
					//$this->_p($payment_data);
					//$this->_p($Payment);
					//return false;

					if ($resp = $PaymentService->add($Context, $realm, $Payment)){
						$qbo_pmnt_id = $this->qbo_clear_braces($resp);
						$log_title.="Export Payment Order #$ord_id_num\n";
						$log_details.="Payment for Order #$ord_id_num has been exported, QuickBooks Payment ID is #$qbo_pmnt_id";
						$log_status = 1;
						$this->save_payment_id_map($payment_id,$qbo_pmnt_id,1);
						$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Order Payment Add',$payment_data,$Payment,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
						return $qbo_pmnt_id;

					}else{
						$res_err = $PaymentService->lastError($Context);
						$log_title.="Export Payment Error Order #$ord_id_num\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Order Payment Add',$payment_data,$Payment,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
					}
				}
			}
		}

		return false;
	}
	
}
/*Class End*/

/*Frontend Functions(WP)*/
require_once plugin_dir_path( __FILE__ ) . 'class-myworks-wc-qbo-sync-lib-frontend.php';

/*Ext*/
require_once plugin_dir_path( __FILE__ ) . 'class-myworks-wc-qbo-sync-qbo-lib-ext.php';