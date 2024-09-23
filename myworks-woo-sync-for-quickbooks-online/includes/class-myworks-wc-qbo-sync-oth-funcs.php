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
class MyWorks_WC_QBO_Sync_Oth_Funcs {	
	
	/**
	 * Method description.
	 *
	 * @since    1.0.0
	 */
	protected $quickbooks_connection_dashboard_url='https://app.myworks.software';
	
	protected $is_valid_license = false;
	protected $plugin_license_status = '';
	
	private $license_data_for_conn_page_view;
	
	public function __construct(){
		/*
		if(!session_id()) {			
			session_start();
		}
		*/
	}
	
	/**/
	private function mw_license_lk_blank_check_run($licensekey,$localkey="",$realtime=false){
		$recent_llk = get_option('mw_wc_qbo_sync_localkey','');		
		if(empty($recent_llk)){
			$is_lbcr = true;
			$td = date('Y-m-d');
			$td_rc = 0;
			
			$lbcr_chk_opt = get_option('mw_wc_qbo_sync_lbcr_chk_count_dt','');
			if(!empty($lbcr_chk_opt) && is_array($lbcr_chk_opt)){
				if(isset($lbcr_chk_opt[$td])){
					$td_rc = (int) $lbcr_chk_opt[$td];
					if($td_rc  < 0){$td_rc = 0;}
					if($td_rc  >= 2){
						$is_lbcr = false;
					}
				}
			}
			
			if($is_lbcr){
				$td_rc++;
				$lbcr_nd = array();
				$lbcr_nd[$td] = $td_rc;
				
				update_option('mw_wc_qbo_sync_lbcr_chk_count_dt',$lbcr_nd);
				
				if($this->is_valid_license($licensekey,$localkey,true)){
					return true;
				}				
			}
		}
		
		return false;
	}
	
	public function is_valid_license($licensekey,$localkey="",$realtime=false){		
		if(!$this->is_valid_license){
			//$is_sn_chk = (get_option('mw_wc_qbo_sync_session_cn_ls_chk')=='true')?true:false;
			$is_sn_chk = false;
			$is_lc_func_run = false;
			if($is_sn_chk && $licensekey!='' && (!$realtime && !isset($_SESSION['mw_wc_qbo_sync_new_license_check']))){
				if(!isset($_SESSION['mw_wc_qbo_sync_rts_license_data'])){
					$license_data = $this->myworks_wc_qbo_sync_check_license($licensekey,$localkey);
					$is_lc_func_run = true;
					$_SESSION['mw_wc_qbo_sync_rts_license_data'] = $license_data;
				}else{
					$license_data = $_SESSION['mw_wc_qbo_sync_rts_license_data'];
				}
			}else{
				$license_data = $this->myworks_wc_qbo_sync_check_license($licensekey,$localkey,$realtime);
				$is_lc_func_run = true;
			}
			
			if($is_lc_func_run){
				$this->mw_license_lk_blank_check_run($licensekey,$localkey,$realtime);
			}
			
			if($is_sn_chk){
				if(!$realtime && isset($_SESSION['mw_wc_qbo_sync_new_license_check'])){
					unset($_SESSION['mw_wc_qbo_sync_new_license_check']);
				}
			}		
			
			if(!$realtime && is_array($license_data) && count($license_data)){
				foreach($license_data as $ldk => $ldv){
					if($ldk!='status' && $ldk!='trial_expired'){
						unset($license_data[$ldk]);
					}
				}
			}
			
			$this->plugin_license_status = (isset($license_data['status']))?$license_data['status']:'';
			if(isset($license_data['status']) && $license_data['status']=='Active' && !isset($license_data['trial_expired'])){
				$this->is_valid_license = true;
			}else{
				if(isset($license_data['trial_expired'])){
					$this->plugin_license_status = 'Invalid';
				}				
			}			
		}
		
		return $this->is_valid_license;
	}
	
	public function get_license_status(){
		return $this->plugin_license_status;
	}
	
	public function lcf_debug_f($l_key,$llk,$rt,$orc){
		if(empty($l_key) || !is_admin()){
			return false;
		}
		return $this->myworks_wc_qbo_sync_check_license($l_key,$llk,$rt,$orc,true);
	}
	
	protected function myworks_wc_qbo_sync_check_license($licensekey,$localkey="",$realtime=false,$only_remote_check=false,$dc=false) {
		$results_df = array('status'=>'','nextduedate'=>'','billingcycle'=>'','productname'=>'','email'=>'','validdomain'=>'');
		$results = $results_df;
		if(empty($licensekey)){
			return $results;
		}
		//$realtime = true;
		
		$licensing_secret_key = 'QM8S20LSKJ03H3J'; #ALL
		
		
		// Enter the url to your WHMCS installation here
		$whmcsurl = $this->quickbooks_connection_dashboard_url.'/';
		// Must match what is specified in the MD5 Hash Verification field
		// of the licensing product that will be used with this check.  
		
		// The number of days to wait between performing remote license checks
		$localkeydays = 2;
		// The number of days to allow failover for after local key expiry
		$allowcheckfaildays = 7;

		// -----------------------------------
		//  -- Do not edit below this line --
		// -----------------------------------
		
		
		$check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
		$checkdate = date("Ymd");
		//$domain = $_SERVER['SERVER_NAME'];
		$domain = $this->get_plugin_domain();
	   //$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
	    $usersip = $this->get_plugin_ip();
		
		$dirpath = dirname(__FILE__);
		$verifyfilepath = 'modules/servers/licensing/verify.php';
		$localkeyvalid = false;
		
		$localkeyresults = array();
		
		if (!$only_remote_check && $localkey) {
			$localkey = str_replace("\n", '', $localkey); # Remove the line breaks
			$localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
			$md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
			if ($md5hash == md5($localdata . $licensing_secret_key)) {
				$localdata = strrev($localdata); # Reverse the string
				$md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
				$localdata = substr($localdata, 32); # Extract License Data
				$localdata = base64_decode($localdata);
				$localkeyresults = unserialize($localdata);
				$originalcheckdate = $localkeyresults['checkdate'];
				if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
					$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
					if ($originalcheckdate > $localexpiry) {
						$localkeyvalid = true;
						$results = $localkeyresults;
						if(is_array($results)){
							if(isset($results['validdomain'])){
								$validdomains = explode(',', $results['validdomain']);
								if (!in_array($domain, $validdomains)) {
									$localkeyvalid = false;
									$localkeyresults['status'] = "Invalid";
									$results = $results_df;
								}
							}
							
							/*
							if(isset($results['validip'])){
								$validips = explode(',', $results['validip']);
								if (!in_array($usersip, $validips)) {
									$localkeyvalid = false;
									$localkeyresults['status'] = "Invalid";
									$results = $results_df;
								}
							}
							*/
							
							/*
							if(isset($results['validdirectory'])){
								$validdirs = explode(',', $results['validdirectory']);
								if (!in_array($dirpath, $validdirs)) {
									$localkeyvalid = false;
									$localkeyresults['status'] = "Invalid";
									$results = $results_df;
								}
							}
							*/
						}						
					}else{
						if(!$dc){
							//delete_option('mw_wc_qbo_sync_localkey');
						}
						
						$realtime = true;
					}
				}
			}
		}
		
		if ((!$localkeyvalid && $realtime) || $only_remote_check) {			
			$responseCode = 0;
			$postfields = array(
				'licensekey' => $licensekey,
				'domain' => $domain,
				'ip' => $usersip,
				'dir' => $dirpath,
			);
			if ($check_token) $postfields['check_token'] = $check_token;
			$query_string = '';
			foreach ($postfields AS $k=>$v) {
				$query_string .= $k.'='.urlencode($v).'&';
			}
			if (function_exists('curl_exec')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_POSTREDIR, 3);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			} else {
				$responseCodePattern = '/^HTTP\/\d+\.\d+\s+(\d+)/';
				$fp = @fsockopen($whmcsurl, 80, $errno, $errstr, 5);
				if ($fp) {
					$newlinefeed = "\r\n";
					$header = "POST ".$whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
					$header .= "Host: ".$whmcsurl . $newlinefeed;
					$header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
					$header .= "Content-length: ".@strlen($query_string) . $newlinefeed;
					$header .= "Connection: close" . $newlinefeed . $newlinefeed;
					$header .= $query_string;
					$data = $line = '';
					@stream_set_timeout($fp, 20);
					@fputs($fp, $header);
					$status = @socket_get_status($fp);
					while (!@feof($fp)&&$status) {
						$line = @fgets($fp, 1024);
						$patternMatches = array();
						if (!$responseCode
							&& preg_match($responseCodePattern, trim($line), $patternMatches)
						) {
							$responseCode = (empty($patternMatches[1])) ? 0 : $patternMatches[1];
						}
						$data .= $line;
						$status = @socket_get_status($fp);
					}
					@fclose ($fp);
				}
			}
			if ($responseCode != 200) {
				$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
				if (isset($originalcheckdate) && $originalcheckdate > $localexpiry) {
					$results = $localkeyresults;
				} else {
					$results = array();
					$results['status'] = "Invalid";
					$results['description'] = "Remote Check Failed";
					return $results;
				}
			} else {
				preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
				$results = array();
				foreach ($matches[1] AS $k=>$v) {
					$results[$v] = $matches[2][$k];
				}
			}
			if (!is_array($results)) {
				//die("Invalid License Server Response");
				return $results;
			}
			if (isset($results['md5hash'])) {
				if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
					$results['status'] = "Invalid";
					$results['description'] = "MD5 Checksum Verification Failed";
					return $results;
				}
			}
			if ($results['status'] == "Active") {
				$results['checkdate'] = $checkdate;
				$data_encoded = serialize($results);
				$data_encoded = base64_encode($data_encoded);
				$data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
				$data_encoded = strrev($data_encoded);
				$data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
				$data_encoded = wordwrap($data_encoded, 80, "\n", true);
				$results['localkey'] = $data_encoded;
			}else{
				if(!$dc){
					delete_option('mw_wc_qbo_sync_localkey');
				}				
			}			
			$results['remotecheck'] = true;
		}
		//$this->_p($results);
		if($dc){
			if(isset($results['localkey'])){unset($results['localkey']);}
			if(isset($results['md5hash'])){unset($results['md5hash']);}
			return $results;
		}
		
		/**/		
		if(!empty($results)){
			$ldfcpv = array();
			$ldfcpv['status'] = $results['status'];
			$ldfcpv['nextduedate'] = $results['nextduedate'];
			$ldfcpv['billingcycle'] = $results['billingcycle'];
			$l_pln = '';
			if(!empty($results['productname']) && strpos($results['productname'],' | ')!==false){
				$pn_arr = explode(' | ',$results['productname']);
				if(is_array($pn_arr) && count($pn_arr) == 2){
					$l_pln = $pn_arr[1];
				}
			}
			$ldfcpv['plan'] = $l_pln;
			$ldfcpv['productname'] = $results['productname'];
			$this->license_data_for_conn_page_view = $ldfcpv;
			
			$pd_ff_ext_ld = array();
			$pd_ff_ext_ld['email'] = $results['email'];
			$pd_ff_ext_ld['validdomain'] = $results['validdomain'];
			update_option('mw_wc_qbo_sync_pd_ff_ext_ld',$pd_ff_ext_ld);
		}		
		
		//
		if($licensekey!=''){
			update_option('mw_wc_qbo_sync_license',$licensekey);
		}
		if ($results["status"]=="Active" && isset($results["localkey"]) && $results["localkey"]!='') {			
			update_option('mw_wc_qbo_sync_localkey',$results["localkey"]);
		}
		if ($results["status"]=="Active"){			
			//24-03-2017
			$productname = $results["productname"];
			$serviceid = $results["serviceid"];
			$billingcycle = $results["billingcycle"];
			update_option('mw_wc_qbo_sync_service_id',$serviceid);
			if(strpos($productname,'Free Trial')!==false){
					$trialdaysleft = (int) 14-((strtotime(date("Y-m-d")) - strtotime($results["regdate"]))/86400);						
				if($trialdaysleft<0){
					//					
					delete_option('mw_wc_qbo_sync_localkey');
					delete_option('mw_wc_qbo_sync_access_token');
					$results['trial_expired'] = true;
					$trialdaysleft = 0;
				}
				$serviceid = $results["serviceid"];
				update_option('mw_wc_qbo_sync_trial_license','true');
				update_option('mw_wc_qbo_sync_trial_days_left',$trialdaysleft);
				update_option('mw_wc_qbo_sync_trial_license_serviceid',$serviceid);
			}else{
				delete_option('mw_wc_qbo_sync_trial_license');
				delete_option('mw_wc_qbo_sync_trial_days_left');
				delete_option('mw_wc_qbo_sync_trial_license_serviceid');
			}
			
			//
			if(strpos($billingcycle,'Monthly')!==false){
				update_option('mw_wc_qbo_sync_monthly_license','true');
			}else{
				delete_option('mw_wc_qbo_sync_monthly_license');
			}
		}
		unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
		return $results;
	}
	
	public function get_ldfcpv(){
		return (array) $this->license_data_for_conn_page_view;
	}
	
	public function get_connection_iframe_extra_params(){
		$extra_param = '';
		$dirpath = $this->get_plugin_connection_dir();
		$usersip = $this->get_plugin_ip();		
		$dirpath = base64_encode($dirpath);
		$extra_param.='&wc_qbo_plugin_dirpath='.$dirpath;
		$usersip = base64_encode($usersip);
		$extra_param.='&wc_qbo_plugin_usersip='.$usersip;
		return $extra_param;
	}
	
	public function get_plugin_domain(){
		$u_sn = false;
		if($u_sn && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])){
			return $_SERVER['SERVER_NAME'];
		}else{
			$siteurl = get_option('siteurl'); //get_site_url
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
		$sname = ($u_sn && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME']))?$_SERVER['SERVER_NAME']:$this->get_plugin_domain();
		if(empty($usersip) && !empty($sname)){
			$usersip = gethostbyname($sname);
		}
		return $usersip;
	}
	
	public function get_plugin_connection_dir(){
		$dirpath = dirname(__FILE__);
		return $dirpath;
	}
	
	public function myworks_wc_qbo_sync_set_session_msg($key='',$msg=''){
		if(!isset($_SESSION['myworks_wc_qbo_sync_session_msg'])){
			$_SESSION['myworks_wc_qbo_sync_session_msg'] = array();     
		}

		$_SESSION['myworks_wc_qbo_sync_session_msg'][$key] = $msg;        
	}

	public function myworks_wc_qbo_sync_show_session_msg($key='',$div_class=""){
		if(isset($_SESSION['myworks_wc_qbo_sync_session_msg'][$key])){
			if(!empty($_SESSION['myworks_wc_qbo_sync_session_msg'][$key])){            
			echo '<div class="myworks_wc_qbo_sync_session_msg_div '.$div_class.'">';
			if(is_array($_SESSION['myworks_wc_qbo_sync_session_msg'][$key])){
				echo implode('<br />', $_SESSION['myworks_wc_qbo_sync_session_msg'][$key]);
			}
			else{
				echo $_SESSION['myworks_wc_qbo_sync_session_msg'][$key];
			}
			echo '</div>';
			}

			unset($_SESSION['myworks_wc_qbo_sync_session_msg'][$key]);
		}
	}


	public function myworks_wc_qbo_sync_get_session_msg($key='',$div_class="",$unset=true){
		$return="";
		if(isset($_SESSION['myworks_wc_qbo_sync_session_msg'][$key])){
			if(!empty($_SESSION['myworks_wc_qbo_sync_session_msg'][$key])){
				$return.='<div class="myworks_wc_qbo_sync_session_msg_div '.$div_class.'">';
				if(is_array($_SESSION['myworks_wc_qbo_sync_session_msg'][$key])){
					$return.= implode('<br />', $_SESSION['myworks_wc_qbo_sync_session_msg'][$key]);
				}
				else{
					$return.= $_SESSION['myworks_wc_qbo_sync_session_msg'][$key];
				}
				$return.= '</div>';
			}
			if($unset){
			   unset($_SESSION['myworks_wc_qbo_sync_session_msg'][$key]); 
			}
		}
		return $return;    
	}
	
	public function myworks_wc_qbo_sync_now(){
		return date('Y-m-d H:i:s');
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
	
	public function _p($item=''){
		if(is_array($item) || is_object($item)){
			echo '<pre>'; print_r($item); echo '</pre>';
		}
		else{
			echo $item;
		}
	}
	
	public function get_select2_js($item='select',$d_item='',$pvep='no'){
		if(get_option('mw_wc_qbo_sync_select2_status')!='true'){
			return '';
		}
		
		$is_ajax_dd = 0;
		if(get_option('mw_wc_qbo_sync_select2_ajax')=='true'){
			$is_ajax_dd = 1;
		}
		
		$json_data_url = '';
		if($d_item=='qbo_product'){
			$json_data_url = site_url('index.php?mw_qbo_sync_public_get_json_item_list=1&item=qbo_product');
		}
		
		if($d_item=='qbo_customer'){
			$json_data_url = site_url('index.php?mw_qbo_sync_public_get_json_item_list=1&item=qbo_customer');
		}
		
		if($d_item=='qbo_vendor'){
			$json_data_url = site_url('index.php?mw_qbo_sync_public_get_json_item_list=1&item=qbo_vendor');
		}
		
		return <<<EOF
		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
		<script type="text/javascript">
		  jQuery(document).ready(function(){
			 jQuery('{$item}').addClass('mwqs_s2');
		  });

		  var d_item = '{$d_item}';
		  var pvep = '{$pvep}';
		  jQuery(function($){			 
			  //jQuery('{$item}').select2();
			   jQuery('{$item}').each(function(){
				   if(jQuery(this).prop('multiple')){
						jQuery(this).select2();
						 jQuery(this).removeClass('mwqs_s2');
				   }
			   });
			  
			  jQuery('{$item}').hover(function(){
				  var is_ajax_dd = {$is_ajax_dd};
				  if(jQuery(this).hasClass('mwqs_dynamic_select') && is_ajax_dd==1){					   
					   jQuery(this).select2({
						   ajax: {
							url: "{$json_data_url}",
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									q: params.term // search term
								};
							},
							processResults: function (data) {								
								return {
									results: data
								};
							},
							cache: true
						},
						minimumInputLength: 3
					   });
				  }else{
			  		if(d_item == 'qbo_product' && pvep != 'yes'){
			  			jQuery(this).select2({
						  	templateResult: mw_qb_s_s2_format,
						  	templateSelection: mw_qb_s_s2_format
						  });
		  			}else{
		  				jQuery(this).select2();
		  			}
					  
				  }
				  
				  jQuery(this).removeClass('mwqs_s2');
				  
			  });
			  var head = $("head");
			  var headlinklast = head.find("link[rel='stylesheet']:last");
			  var linkElement = "<style type='text/css'>ul.select2-results__options li:first-child{padding:12px 0;}</style>";
			  if (headlinklast.length){
			    headlinklast.after(linkElement);
			  }
			  else {
			   head.append(linkElement);
			  }
		  });

		  function mw_qb_s_s2_format(item){
		  	var selectionText = item.text.replace('- SKU: ','<br><b>SKU:</b> ');  	
		  	var returnString =jQuery('<span style="font-size:13px;">'+selectionText+'</span>');
		  	return returnString;
		  }		  
		</script>
EOF;
	}
	
	public function get_tablesorter_js($item='table'){
		return <<<EOF
		<!--<link href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.5/css/theme.blue.css" rel="stylesheet" />-->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.5/js/jquery.tablesorter.js"></script>
		<script type="text/javascript">
		  jQuery(function($){
			  //jQuery('{$item}').addClass('tablesorter-blue');
			  jQuery('{$item} th').css('cursor','pointer');
			  jQuery('{$item} th').each(function(){
				  var sort_th_title = jQuery(this).attr('title');
				  if (sort_th_title == null){
					  sort_th_title = '';
				  }
				  if(sort_th_title==''){
					  sort_th_title = jQuery(this).text();
				  }				  
				  sort_th_title = jQuery.trim(sort_th_title);				  
				  if(sort_th_title!=''){
					  sort_th_title = 'Sort By '+sort_th_title;
					jQuery(this).attr('title',sort_th_title);
				  }else{
					  //jQuery(this).addClass('{sorter: false}');
					  jQuery(this).attr('data-sorter','false');
					  jQuery(this).attr('data-filter','false');
				  }				  
			  });
			  jQuery('{$item}').tablesorter();
		  });
		</script>
EOF;
	}
	
	public function get_bootstrap_switch_lib(){
		return <<<EOF
		<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' type='text/css' media='all' />
	   <script type='text/javascript' src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
	   
	   <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap2/bootstrap-switch.css' type='text/css' media='all' />
	   <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.js'></script>
EOF;
	}
	
	public function get_html_msg($title='',$body=''){
		$display = '
		<html>
			<head>
			<title>'.$title.'</title>
			</head>
			
			<body>
			'.$body.'
			</body>
		</html>';
		return $display;
	}
	
	public function get_plugin_settings_post_data(){

		//$this->_p($_POST);die;
		return array(
		//'mw_wc_qbo_sync_sandbox_mode' => isset($_POST['mw_wc_qbo_sync_sandbox_mode'])?$_POST['mw_wc_qbo_sync_sandbox_mode']:'',
		
		'mw_wc_qbo_sync_default_qbo_item' => isset($_POST['mw_wc_qbo_sync_default_qbo_item'])?(int) $_POST['mw_wc_qbo_sync_default_qbo_item']:0,

		'mw_wc_qbo_sync_default_qbo_product_account' => isset($_POST['mw_wc_qbo_sync_default_qbo_product_account'])?(int) $_POST['mw_wc_qbo_sync_default_qbo_product_account']:0,

		'mw_wc_qbo_sync_default_qbo_asset_account' => isset($_POST['mw_wc_qbo_sync_default_qbo_asset_account'])?(int) $_POST['mw_wc_qbo_sync_default_qbo_asset_account']:0,

		'mw_wc_qbo_sync_default_qbo_expense_account' => isset($_POST['mw_wc_qbo_sync_default_qbo_expense_account'])?(int) $_POST['mw_wc_qbo_sync_default_qbo_expense_account']:0,
		
		'mw_wc_qbo_sync_default_qbo_discount_account' => isset($_POST['mw_wc_qbo_sync_default_qbo_discount_account'])?(int) $_POST['mw_wc_qbo_sync_default_qbo_discount_account']:0,

		'mw_wc_qbo_sync_default_coupon_code' => isset($_POST['mw_wc_qbo_sync_default_coupon_code'])?(int) $_POST['mw_wc_qbo_sync_default_coupon_code']:0,
		
		'mw_wc_qbo_sync_default_shipping_product' => isset($_POST['mw_wc_qbo_sync_default_shipping_product'])?(int) $_POST['mw_wc_qbo_sync_default_shipping_product']:0,
		
		'mw_wc_qbo_sync_txn_fee_li_qbo_item' => isset($_POST['mw_wc_qbo_sync_txn_fee_li_qbo_item'])?(int) $_POST['mw_wc_qbo_sync_txn_fee_li_qbo_item']:0,
		
		//'mw_wc_qbo_sync_order_as_sales_receipt' => isset($_POST['mw_wc_qbo_sync_order_as_sales_receipt'])?$_POST['mw_wc_qbo_sync_order_as_sales_receipt']:'false',
		
		'mw_wc_qbo_sync_invoice_min_id' => isset($_POST['mw_wc_qbo_sync_invoice_min_id'])?(int) $_POST['mw_wc_qbo_sync_invoice_min_id']:0,
		
		'mw_wc_qbo_sync_qbo_inventory_start_date' => isset($_POST['mw_wc_qbo_sync_qbo_inventory_start_date'])?trim($_POST['mw_wc_qbo_sync_qbo_inventory_start_date']):'',
		
		'mw_wc_qbo_sync_null_invoice' => isset($_POST['mw_wc_qbo_sync_null_invoice'])?$_POST['mw_wc_qbo_sync_null_invoice']:'false',
		
		'mw_wc_qbo_sync_invoice_notes' => isset($_POST['mw_wc_qbo_sync_invoice_notes'])?$_POST['mw_wc_qbo_sync_invoice_notes']:'false',
		
		'mw_wc_qbo_sync_invoice_note_id' => isset($_POST['mw_wc_qbo_sync_invoice_note_id'])?(int) $_POST['mw_wc_qbo_sync_invoice_note_id']:0,
		
		'mw_wc_qbo_sync_invoice_note_name' => isset($_POST['mw_wc_qbo_sync_invoice_note_name'])?$_POST['mw_wc_qbo_sync_invoice_note_name']:'',
		
		'mw_wc_qbo_sync_invoice_cancelled' => isset($_POST['mw_wc_qbo_sync_invoice_cancelled'])?$_POST['mw_wc_qbo_sync_invoice_cancelled']:'false',
		
		'mw_wc_qbo_sync_invoice_memo' => isset($_POST['mw_wc_qbo_sync_invoice_memo'])?$_POST['mw_wc_qbo_sync_invoice_memo']:'false',
		'mw_wc_qbo_sync_won_qbf_sync' => isset($_POST['mw_wc_qbo_sync_won_qbf_sync'])?trim($_POST['mw_wc_qbo_sync_won_qbf_sync']):'',
		
		'mw_wc_qbo_sync_onli_qbo_product' => isset($_POST['mw_wc_qbo_sync_onli_qbo_product'])?(int) $_POST['mw_wc_qbo_sync_onli_qbo_product']:0,
		
		'mw_wc_qbo_sync_use_qb_next_ord_num_iowon' => isset($_POST['mw_wc_qbo_sync_use_qb_next_ord_num_iowon'])?$_POST['mw_wc_qbo_sync_use_qb_next_ord_num_iowon']:'false',
		
		'mw_wc_qbo_sync_invoice_memo_statement' => isset($_POST['mw_wc_qbo_sync_invoice_memo_statement'])?$_POST['mw_wc_qbo_sync_invoice_memo_statement']:'false',
		
		'mw_wc_qbo_sync_invoice_date' => isset($_POST['mw_wc_qbo_sync_invoice_date'])?$_POST['mw_wc_qbo_sync_invoice_date']:'false',
		
		'mw_wc_qbo_sync_tax_rule' => isset($_POST['mw_wc_qbo_sync_tax_rule'])?(int) $_POST['mw_wc_qbo_sync_tax_rule']:0,
		
		'mw_wc_qbo_sync_tax_format' => isset($_POST['mw_wc_qbo_sync_tax_format'])?$_POST['mw_wc_qbo_sync_tax_format']:'',
		
		'mw_wc_qbo_sync_odr_tax_as_li' => isset($_POST['mw_wc_qbo_sync_odr_tax_as_li'])?$_POST['mw_wc_qbo_sync_odr_tax_as_li']:'false',
		'mw_wc_qbo_sync_odr_shipping_as_li' => isset($_POST['mw_wc_qbo_sync_odr_shipping_as_li'])?$_POST['mw_wc_qbo_sync_odr_shipping_as_li']:'false',
		
		'mw_wc_qbo_sync_set_bemail_to_cus_email_addr' => isset($_POST['mw_wc_qbo_sync_set_bemail_to_cus_email_addr'])?$_POST['mw_wc_qbo_sync_set_bemail_to_cus_email_addr']:'false',
		
		//
		'mw_wc_qbo_sync_po_sync_after_ord_ed' => isset($_POST['mw_wc_qbo_sync_po_sync_after_ord_ed'])?$_POST['mw_wc_qbo_sync_po_sync_after_ord_ed']:'false',
		
		'mw_wc_qbo_sync_po_sync_after_ord_qb_vendor' => isset($_POST['mw_wc_qbo_sync_po_sync_after_ord_qb_vendor'])?$_POST['mw_wc_qbo_sync_po_sync_after_ord_qb_vendor']:'',
		
		'mw_wc_qbo_sync_po_sync_after_ord_pa_acc' => isset($_POST['mw_wc_qbo_sync_po_sync_after_ord_pa_acc'])?$_POST['mw_wc_qbo_sync_po_sync_after_ord_pa_acc']:'',
		
		'mw_wc_qbo_sync_otli_qbo_product' => isset($_POST['mw_wc_qbo_sync_otli_qbo_product'])?(int) $_POST['mw_wc_qbo_sync_otli_qbo_product']:0,		
		
		'mw_wc_qbo_sync_append_client' => isset($_POST['mw_wc_qbo_sync_append_client'])?$_POST['mw_wc_qbo_sync_append_client']:'false',
		
		'mw_wc_qbo_sync_display_name_pattern' => isset($_POST['mw_wc_qbo_sync_display_name_pattern'])?$_POST['mw_wc_qbo_sync_display_name_pattern']:'',
		
		'mw_wc_qbo_sync_client_sort_order' => isset($_POST['mw_wc_qbo_sync_client_sort_order'])?$_POST['mw_wc_qbo_sync_client_sort_order']:'',
		'mw_wc_qbo_sync_qb_customer_type_fnc' => isset($_POST['mw_wc_qbo_sync_qb_customer_type_fnc'])?$_POST['mw_wc_qbo_sync_qb_customer_type_fnc']:'',
		
		'mw_wc_qbo_sync_client_check_email' => isset($_POST['mw_wc_qbo_sync_client_check_email'])?$_POST['mw_wc_qbo_sync_client_check_email']:'false',
		
		'mw_wc_qbo_sync_block_new_cus_sync_qb' => isset($_POST['mw_wc_qbo_sync_block_new_cus_sync_qb'])?$_POST['mw_wc_qbo_sync_block_new_cus_sync_qb']:'false',
		
		'mw_wc_qbo_sync_pull_enable' => isset($_POST['mw_wc_qbo_sync_pull_enable'])?$_POST['mw_wc_qbo_sync_pull_enable']:'false',
		'mw_wc_qbo_sync_product_pull_wc_status' => isset($_POST['mw_wc_qbo_sync_product_pull_wc_status'])?$_POST['mw_wc_qbo_sync_product_pull_wc_status']:'',
		'mw_wc_qbo_sync_product_pull_desc_field' => isset($_POST['mw_wc_qbo_sync_product_pull_desc_field'])?$_POST['mw_wc_qbo_sync_product_pull_desc_field']:'none',
		//
		'mw_wc_qbo_sync_produc_push_purchase_desc_field' => isset($_POST['mw_wc_qbo_sync_produc_push_purchase_desc_field'])?$_POST['mw_wc_qbo_sync_produc_push_purchase_desc_field']:'none',
		
		'mw_wc_qbo_sync_product_pull_wpn_field' => isset($_POST['mw_wc_qbo_sync_product_pull_wpn_field'])?$_POST['mw_wc_qbo_sync_product_pull_wpn_field']:'',
		
		'mw_wc_qbo_sync_product_push_qpn_field' => isset($_POST['mw_wc_qbo_sync_product_push_qpn_field'])?$_POST['mw_wc_qbo_sync_product_push_qpn_field']:'',
		
		'mw_wc_qbo_sync_auto_pull_client' => isset($_POST['mw_wc_qbo_sync_auto_pull_client'])?$_POST['mw_wc_qbo_sync_auto_pull_client']:'',
		
		'mw_wc_qbo_sync_auto_pull_invoice' => isset($_POST['mw_wc_qbo_sync_auto_pull_invoice'])?$_POST['mw_wc_qbo_sync_auto_pull_invoice']:'',
		
		'mw_wc_qbo_sync_auto_pull_payment' => isset($_POST['mw_wc_qbo_sync_auto_pull_payment'])?$_POST['mw_wc_qbo_sync_auto_pull_payment']:'',
		
		'mw_wc_qbo_sync_auto_pull_limit' => isset($_POST['mw_wc_qbo_sync_auto_pull_limit'])?$_POST['mw_wc_qbo_sync_auto_pull_limit']:'',
		
		'mw_wc_qbo_sync_auto_pull_interval' => isset($_POST['mw_wc_qbo_sync_auto_pull_interval'])?(int) $_POST['mw_wc_qbo_sync_auto_pull_interval']:0,
		'mw_wc_qbo_sync_webhook_enable' => isset($_POST['mw_wc_qbo_sync_webhook_enable'])?$_POST['mw_wc_qbo_sync_webhook_enable']:'false',
		'mw_wc_qbo_sync_webhook_items' => (isset($_POST['mw_wc_qbo_sync_webhook_items']) && is_array($_POST['mw_wc_qbo_sync_webhook_items']) && count($_POST['mw_wc_qbo_sync_webhook_items']))?implode(',',$_POST['mw_wc_qbo_sync_webhook_items']):'',
		
		'mw_wc_qbo_sync_pause_up_qbo_conection' => isset($_POST['mw_wc_qbo_sync_pause_up_qbo_conection'])?$_POST['mw_wc_qbo_sync_pause_up_qbo_conection']:'false',

		'mw_wc_qbo_sync_rt_push_enable' => isset($_POST['mw_wc_qbo_sync_rt_push_enable'])?$_POST['mw_wc_qbo_sync_rt_push_enable']:'false',
		'mw_wc_qbo_sync_rt_push_items' => (isset($_POST['mw_wc_qbo_sync_rt_push_items']) && is_array($_POST['mw_wc_qbo_sync_rt_push_items']) && count($_POST['mw_wc_qbo_sync_rt_push_items']))?implode(',',$_POST['mw_wc_qbo_sync_rt_push_items']):'',
		
		'mw_wc_qbo_sync_queue_cron_interval_time' => isset($_POST['mw_wc_qbo_sync_queue_cron_interval_time'])?$_POST['mw_wc_qbo_sync_queue_cron_interval_time']:'',
		
		'mw_wc_qbo_sync_inv_sr_txn_qb_class' => isset($_POST['mw_wc_qbo_sync_inv_sr_txn_qb_class'])?trim($_POST['mw_wc_qbo_sync_inv_sr_txn_qb_class']):'',
		'mw_wc_qbo_sync_inv_sr_txn_qb_department' => isset($_POST['mw_wc_qbo_sync_inv_sr_txn_qb_department'])?trim($_POST['mw_wc_qbo_sync_inv_sr_txn_qb_department']):'',		
		
		'mw_wc_qbo_sync_disable_realtime_sync' => isset($_POST['mw_wc_qbo_sync_disable_realtime_sync'])?$_POST['mw_wc_qbo_sync_disable_realtime_sync']:'false',
		
		'mw_wc_qbo_sync_disable_sync_status' => isset($_POST['mw_wc_qbo_sync_disable_sync_status'])?$_POST['mw_wc_qbo_sync_disable_sync_status']:'false',
		
		'mw_wc_qbo_sync_disable_realtime_client_update' => isset($_POST['mw_wc_qbo_sync_disable_realtime_client_update'])?$_POST['mw_wc_qbo_sync_disable_realtime_client_update']:'false',
		
		'mw_wc_qbo_sync_enable_invoice_prefix' => isset($_POST['mw_wc_qbo_sync_enable_invoice_prefix'])?$_POST['mw_wc_qbo_sync_enable_invoice_prefix']:'false',
		'mw_wc_qbo_sync_qbo_invoice' => isset($_POST['mw_wc_qbo_sync_qbo_invoice'])?$_POST['mw_wc_qbo_sync_qbo_invoice']:'false',
		
		'mw_wc_qbo_sync_email_log' => isset($_POST['mw_wc_qbo_sync_email_log'])?$_POST['mw_wc_qbo_sync_email_log']:'false',
		'mw_wc_qbo_sync_err_add_item_obj_into_log_file' => isset($_POST['mw_wc_qbo_sync_err_add_item_obj_into_log_file'])?$_POST['mw_wc_qbo_sync_err_add_item_obj_into_log_file']:'false',
		'mw_wc_qbo_sync_qbo_push_invoice_date' => isset($_POST['mw_wc_qbo_sync_qbo_push_invoice_date'])?$_POST['mw_wc_qbo_sync_qbo_push_invoice_date']:'false',
		'mw_wc_qbo_sync_success_add_item_obj_into_log_file' => isset($_POST['mw_wc_qbo_sync_success_add_item_obj_into_log_file'])?$_POST['mw_wc_qbo_sync_success_add_item_obj_into_log_file']:'false',
		
		'mw_wc_qbo_sync_success_add_ccqii_debug_ids_into_log' => isset($_POST['mw_wc_qbo_sync_success_add_ccqii_debug_ids_into_log'])?$_POST['mw_wc_qbo_sync_success_add_ccqii_debug_ids_into_log']:'false',
		
		'mw_wc_qbo_sync_save_log_for' => isset($_POST['mw_wc_qbo_sync_save_log_for'])?(int) $_POST['mw_wc_qbo_sync_save_log_for']:'',
		'mw_wc_qbo_sync_wc_qbo_product_desc' => isset($_POST['mw_wc_qbo_sync_wc_qbo_product_desc'])?$_POST['mw_wc_qbo_sync_wc_qbo_product_desc']:'false',
		'mw_wc_qbo_sync_auto_refresh' => isset($_POST['mw_wc_qbo_sync_auto_refresh'])?$_POST['mw_wc_qbo_sync_auto_refresh']:'false',
		'mw_wc_qbo_sync_admin_email' => isset($_POST['mw_wc_qbo_sync_admin_email'])?$_POST['mw_wc_qbo_sync_admin_email']:'',
		//'mw_wc_qbo_sync_customer_qbo_check' => isset($_POST['mw_wc_qbo_sync_customer_qbo_check'])?$_POST['mw_wc_qbo_sync_customer_qbo_check']:'',
		'mw_wc_qbo_sync_customer_qbo_check' => 'true',
		'mw_wc_qbo_sync_customer_qbo_check_ship_addr' => isset($_POST['mw_wc_qbo_sync_customer_qbo_check_ship_addr'])?$_POST['mw_wc_qbo_sync_customer_qbo_check_ship_addr']:'false',
		
		'mw_wc_qbo_sync_customer_match_by_name' => isset($_POST['mw_wc_qbo_sync_customer_match_by_name'])?$_POST['mw_wc_qbo_sync_customer_match_by_name']:'false',
		
		'mw_wc_qbo_sync_select2_status' => isset($_POST['mw_wc_qbo_sync_select2_status'])?$_POST['mw_wc_qbo_sync_select2_status']:'false',
		'mw_wc_qbo_sync_select2_ajax' => isset($_POST['mw_wc_qbo_sync_select2_ajax'])?$_POST['mw_wc_qbo_sync_select2_ajax']:'false',
		'mw_wc_qbo_sync_orders_to_specific_cust' => intval(isset($_POST['mw_wc_qbo_sync_orders_to_specific_cust'])?$_POST['mw_wc_qbo_sync_orders_to_specific_cust']:''),
		'mw_wc_qbo_sync_orders_to_specific_cust_opt' => isset($_POST['mw_wc_qbo_sync_orders_to_specific_cust_opt'])?$_POST['mw_wc_qbo_sync_orders_to_specific_cust_opt']:'false',
		'mw_wc_qbo_sync_store_currency' => (isset($_POST['mw_wc_qbo_sync_store_currency']) && is_array($_POST['mw_wc_qbo_sync_store_currency']) && count($_POST['mw_wc_qbo_sync_store_currency']))?implode(',',$_POST['mw_wc_qbo_sync_store_currency']):'',
		
		'mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses' => (isset($_POST['mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses']) && is_array($_POST['mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses']) && count($_POST['mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses']))?implode(',',$_POST['mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses']):'',
		
		'mw_wc_qbo_sync_pmnt_pull_order_status' => isset($_POST['mw_wc_qbo_sync_pmnt_pull_order_status'])?$_POST['mw_wc_qbo_sync_pmnt_pull_order_status']:'',
		'mw_wc_qbo_sync_db_fix' => isset($_POST['mw_wc_qbo_sync_db_fix'])?$_POST['mw_wc_qbo_sync_db_fix']:'false',
		
		//'mw_wc_qbo_sync_session_cn_ls_chk' => isset($_POST['mw_wc_qbo_sync_session_cn_ls_chk'])?$_POST['mw_wc_qbo_sync_session_cn_ls_chk']:'',
		'mw_wc_qbo_sync_session_cn_ls_chk' => 'false',
		'mw_wc_qbo_sync_customer_qbo_check_billing_company' => isset($_POST['mw_wc_qbo_sync_customer_qbo_check_billing_company'])?$_POST['mw_wc_qbo_sync_customer_qbo_check_billing_company']:'false',
		
		'mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name' => isset($_POST['mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name'])?$_POST['mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name']:'false',
		
		'mw_wc_qbo_sync_wam_mng_inv_ed' => isset($_POST['mw_wc_qbo_sync_wam_mng_inv_ed'])?$_POST['mw_wc_qbo_sync_wam_mng_inv_ed']:'false',
		
		'mw_wc_qbo_sync_sqaiw_v_sec' => isset($_POST['mw_wc_qbo_sync_sqaiw_v_sec'])?$_POST['mw_wc_qbo_sync_sqaiw_v_sec']:'false',
		'mw_wc_qbo_sync_wam_mng_inv_qrts' => isset($_POST['mw_wc_qbo_sync_wam_mng_inv_qrts'])?$_POST['mw_wc_qbo_sync_wam_mng_inv_qrts']:'',
		
		'mw_wc_qbo_sync_force_shipping_line_item' => isset($_POST['mw_wc_qbo_sync_force_shipping_line_item'])?$_POST['mw_wc_qbo_sync_force_shipping_line_item']:'false',
		
		'mw_wc_qbo_sync_skip_os_lid' => isset($_POST['mw_wc_qbo_sync_skip_os_lid'])?$_POST['mw_wc_qbo_sync_skip_os_lid']:'false',
		'mw_wc_qbo_sync_inv_sr_qb_lid_val' => isset($_POST['mw_wc_qbo_sync_inv_sr_qb_lid_val'])?$_POST['mw_wc_qbo_sync_inv_sr_qb_lid_val']:'',
		//
		'mw_wc_qbo_sync_qb_ord_df_val' => isset($_POST['mw_wc_qbo_sync_qb_ord_df_val'])?$_POST['mw_wc_qbo_sync_qb_ord_df_val']:'',
		
		'mw_wc_qbo_sync_qb_pmnt_ref_num_vf' => isset($_POST['mw_wc_qbo_sync_qb_pmnt_ref_num_vf'])?$_POST['mw_wc_qbo_sync_qb_pmnt_ref_num_vf']:'',
		
		//
		'mw_wc_qbo_sync_qb_o_print_status_v' => isset($_POST['mw_wc_qbo_sync_qb_o_print_status_v'])?$_POST['mw_wc_qbo_sync_qb_o_print_status_v']:'',
		'mw_wc_qbo_sync_qb_etpe_ops_o' => isset($_POST['mw_wc_qbo_sync_qb_etpe_ops_o'])?$_POST['mw_wc_qbo_sync_qb_etpe_ops_o']:'',
		'mw_wc_qbo_sync_qb_soli_sv' => isset($_POST['mw_wc_qbo_sync_qb_soli_sv'])?$_POST['mw_wc_qbo_sync_qb_soli_sv']:'',
		
		'mw_wc_qbo_sync_wolim_iqilid_desc' => isset($_POST['mw_wc_qbo_sync_wolim_iqilid_desc'])?$_POST['mw_wc_qbo_sync_wolim_iqilid_desc']:'false',
		//
		'mw_wc_qbo_sync_oaslim_iqbld' => isset($_POST['mw_wc_qbo_sync_oaslim_iqbld'])?trim($_POST['mw_wc_qbo_sync_oaslim_iqbld']):'',
		
		'mw_wc_qbo_sync_send_inv_sr_afsi_qb' => isset($_POST['mw_wc_qbo_sync_send_inv_sr_afsi_qb'])?$_POST['mw_wc_qbo_sync_send_inv_sr_afsi_qb']:'false',
		
		'mw_wc_qbo_sync_no_ad_discount_li' => isset($_POST['mw_wc_qbo_sync_no_ad_discount_li'])?$_POST['mw_wc_qbo_sync_no_ad_discount_li']:'false',
		
		'mw_wc_qbo_sync_qb_sdioli_isli' => isset($_POST['mw_wc_qbo_sync_qb_sdioli_isli'])?$_POST['mw_wc_qbo_sync_qb_sdioli_isli']:'false',
		
		//
		'mw_wc_qbo_sync_sync_txn_fee_as_ng_li' => isset($_POST['mw_wc_qbo_sync_sync_txn_fee_as_ng_li'])?$_POST['mw_wc_qbo_sync_sync_txn_fee_as_ng_li']:'false',
		//
		'mw_wc_qbo_sync_sync_skip_cf_ibs_addr' => isset($_POST['mw_wc_qbo_sync_sync_skip_cf_ibs_addr'])?$_POST['mw_wc_qbo_sync_sync_skip_cf_ibs_addr']:'false',
		
		'mw_wc_qbo_sync_use_qb_ba_for_eqc' => isset($_POST['mw_wc_qbo_sync_use_qb_ba_for_eqc'])?$_POST['mw_wc_qbo_sync_use_qb_ba_for_eqc']:'false',
		
		'mw_wc_qbo_sync_qb_ns_shipping_li_if_z' => isset($_POST['mw_wc_qbo_sync_qb_ns_shipping_li_if_z'])?$_POST['mw_wc_qbo_sync_qb_ns_shipping_li_if_z']:'false',
		
		'mw_wc_qbo_sync_qb_ap_tx_aft_discount' => isset($_POST['mw_wc_qbo_sync_qb_ap_tx_aft_discount'])?$_POST['mw_wc_qbo_sync_qb_ap_tx_aft_discount']:'false',
		
		'mw_wc_qbo_sync_specific_order_status' => (isset($_POST['mw_wc_qbo_sync_specific_order_status']) && is_array($_POST['mw_wc_qbo_sync_specific_order_status']) && count($_POST['mw_wc_qbo_sync_specific_order_status']))?implode(',',$_POST['mw_wc_qbo_sync_specific_order_status']):'',
		
		'mw_wc_qbo_sync_wc_cust_role' => (isset($_POST['mw_wc_qbo_sync_wc_cust_role']) && is_array($_POST['mw_wc_qbo_sync_wc_cust_role']) && count($_POST['mw_wc_qbo_sync_wc_cust_role']))?implode(',',$_POST['mw_wc_qbo_sync_wc_cust_role']):'',
		
		'mw_wc_qbo_sync_wc_cust_role_sync_as_cus' => (isset($_POST['mw_wc_qbo_sync_wc_cust_role_sync_as_cus']) && is_array($_POST['mw_wc_qbo_sync_wc_cust_role_sync_as_cus']) && count($_POST['mw_wc_qbo_sync_wc_cust_role_sync_as_cus']))?implode(',',$_POST['mw_wc_qbo_sync_wc_cust_role_sync_as_cus']):'',
		
		'mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl' => isset($_POST['mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl'])?$_POST['mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl']:'false',
		
		'mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl_pull' => isset($_POST['mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl_pull'])?$_POST['mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl_pull']:'false',
		
		'mw_wc_qbo_sync_invnt_pull_set_prd_stock_sts' => isset($_POST['mw_wc_qbo_sync_invnt_pull_set_prd_stock_sts'])?$_POST['mw_wc_qbo_sync_invnt_pull_set_prd_stock_sts']:'false',
		
		'mw_wc_qbo_sync_hide_vpp_fmp_pages' => isset($_POST['mw_wc_qbo_sync_hide_vpp_fmp_pages'])?$_POST['mw_wc_qbo_sync_hide_vpp_fmp_pages']:'false',
		/*
		'mw_wc_qbo_sync_ignore_cdc_for_invnt_import' => isset($_POST['mw_wc_qbo_sync_ignore_cdc_for_invnt_import'])?$_POST['mw_wc_qbo_sync_ignore_cdc_for_invnt_import']:'false',
		*/
		
		//
		'mw_wc_qbo_sync_allow_cdc_for_invnt_import' => isset($_POST['mw_wc_qbo_sync_allow_cdc_for_invnt_import'])?$_POST['mw_wc_qbo_sync_allow_cdc_for_invnt_import']:'false',
		
		//
		'mw_wc_qbo_sync_allow_cdc_for_prc_import' => isset($_POST['mw_wc_qbo_sync_allow_cdc_for_prc_import'])?$_POST['mw_wc_qbo_sync_allow_cdc_for_prc_import']:'false',
		
		'mw_wc_qbo_sync_ivnt_pull_interval_time' => isset($_POST['mw_wc_qbo_sync_ivnt_pull_interval_time'])?$_POST['mw_wc_qbo_sync_ivnt_pull_interval_time']:'',
		
		//
		'mw_wc_qbo_sync_prc_pull_interval_time' => isset($_POST['mw_wc_qbo_sync_prc_pull_interval_time'])?$_POST['mw_wc_qbo_sync_prc_pull_interval_time']:'',
		
		//
		'mw_wc_qbo_sync_os_price_fp_update' => isset($_POST['mw_wc_qbo_sync_os_price_fp_update'])?$_POST['mw_wc_qbo_sync_os_price_fp_update']:'false',
		
		'mw_wc_qbo_sync_sync_product_images_pp' => isset($_POST['mw_wc_qbo_sync_sync_product_images_pp'])?$_POST['mw_wc_qbo_sync_sync_product_images_pp']:'false',
		
		'mw_wc_qbo_sync_ca_ruso_dqs' => isset($_POST['mw_wc_qbo_sync_ca_ruso_dqs'])?$_POST['mw_wc_qbo_sync_ca_ruso_dqs']:'false',
		'mw_wc_qbo_sync_qb_ed_invt_s_frc' => isset($_POST['mw_wc_qbo_sync_qb_ed_invt_s_frc'])?$_POST['mw_wc_qbo_sync_qb_ed_invt_s_frc']:'false',
		
		'mw_wc_qbo_sync_os_skip_uprice_l_item' => isset($_POST['mw_wc_qbo_sync_os_skip_uprice_l_item'])?$_POST['mw_wc_qbo_sync_os_skip_uprice_l_item']:'false',
		
		'mw_wc_qbo_sync_use_lt_if_ist_l_item' => isset($_POST['mw_wc_qbo_sync_use_lt_if_ist_l_item'])?$_POST['mw_wc_qbo_sync_use_lt_if_ist_l_item']:'false',
		
		'mw_wc_qbo_sync_enable_d_o_q_add_p' => isset($_POST['mw_wc_qbo_sync_enable_d_o_q_add_p'])?$_POST['mw_wc_qbo_sync_enable_d_o_q_add_p']:'false',
		
		//
		'mw_wc_qbo_sync_zero_ord_spl_qb_class' => isset($_POST['mw_wc_qbo_sync_zero_ord_spl_qb_class'])?$_POST['mw_wc_qbo_sync_zero_ord_spl_qb_class']:'',

		//
		'mw_wc_qbo_sync_all_order_to_customer' => isset($_POST['mw_wc_qbo_sync_all_order_to_customer'])?$_POST['mw_wc_qbo_sync_all_order_to_customer']:'',
		
		
		);
	}
	
	public function get_plugin_option_keys(){
		$option_keys = array(
		'mw_wc_qbo_sync_sandbox_mode',
		'mw_wc_qbo_sync_default_qbo_item',
		'mw_wc_qbo_sync_default_qbo_product_account',
		'mw_wc_qbo_sync_default_qbo_asset_account',
		'mw_wc_qbo_sync_default_qbo_expense_account',
		'mw_wc_qbo_sync_default_qbo_discount_account',
		'mw_wc_qbo_sync_default_coupon_code',
		'mw_wc_qbo_sync_default_shipping_product',
		'mw_wc_qbo_sync_txn_fee_li_qbo_item',
		
		'mw_wc_qbo_sync_order_as_sales_receipt',
		'mw_wc_qbo_sync_invoice_min_id',
		'mw_wc_qbo_sync_null_invoice',
		'mw_wc_qbo_sync_invoice_notes',
		'mw_wc_qbo_sync_invoice_note_id',
		'mw_wc_qbo_sync_invoice_note_name',
		'mw_wc_qbo_sync_invoice_cancelled',
		'mw_wc_qbo_sync_invoice_memo',
		'mw_wc_qbo_sync_won_qbf_sync',
		'mw_wc_qbo_sync_onli_qbo_product',
		
		'mw_wc_qbo_sync_use_qb_next_ord_num_iowon',
		'mw_wc_qbo_sync_invoice_memo_statement',
		'mw_wc_qbo_sync_invoice_date',
		'mw_wc_qbo_sync_tax_rule',
		'mw_wc_qbo_sync_tax_format',
		
		'mw_wc_qbo_sync_odr_tax_as_li',
		'mw_wc_qbo_sync_odr_shipping_as_li',
		'mw_wc_qbo_sync_set_bemail_to_cus_email_addr',
		
		'mw_wc_qbo_sync_po_sync_after_ord_ed',
		'mw_wc_qbo_sync_po_sync_after_ord_qb_vendor',
		'mw_wc_qbo_sync_po_sync_after_ord_pa_acc',
		
		'mw_wc_qbo_sync_otli_qbo_product',
		
		'mw_wc_qbo_sync_append_client',
		'mw_wc_qbo_sync_display_name_pattern',
		'mw_wc_qbo_sync_client_sort_order',
		'mw_wc_qbo_sync_qb_customer_type_fnc',
		'mw_wc_qbo_sync_client_check_email',
		'mw_wc_qbo_sync_block_new_cus_sync_qb',
		'mw_wc_qbo_sync_pull_enable',
		'mw_wc_qbo_sync_product_pull_wc_status',
		'mw_wc_qbo_sync_product_pull_desc_field',
		//
		'mw_wc_qbo_sync_produc_push_purchase_desc_field',
		'mw_wc_qbo_sync_product_pull_wpn_field',
		'mw_wc_qbo_sync_product_push_qpn_field',
		
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
		'mw_wc_qbo_sync_err_add_item_obj_into_log_file',
		'mw_wc_qbo_sync_success_add_item_obj_into_log_file',
		'mw_wc_qbo_sync_success_add_ccqii_debug_ids_into_log',
		'mw_wc_qbo_sync_save_log_for',
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
		
		'mw_wc_qbo_sync_customer_match_by_name',
		
		'mw_wc_qbo_sync_compt_wchau_enable',
		'mw_wc_qbo_sync_compt_wchau_wf_qi_map',
		'mw_wc_qbo_sync_compt_p_wacof',
		'mw_wc_qbo_sync_compt_p_wacof_m_field',
		'mw_wc_qbo_sync_compt_acof_wf_qi_map',
		'mw_wc_qbo_sync_pmnt_pull_prevent_order_statuses',
		'mw_wc_qbo_sync_pmnt_pull_order_status',
		'mw_wc_qbo_sync_w_shp_track',
		'mw_wc_qbo_sync_wcogs_fiels',
		'mw_wc_qbo_sync_wcfep_add_fld',
		'mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map',
		'mw_wc_qbo_sync_wam_mng_inv_ed',
		
		'mw_wc_qbo_sync_sqaiw_v_sec',
		'mw_wc_qbo_sync_wam_mng_inv_qrts',
		'mw_wc_qbo_sync_wc_qbo_product_desc',
		'mw_wc_qbo_sync_qbo_push_invoice_date',
		'mw_wc_qbo_sync_wc_avatax_support',	
		'mw_wc_qbo_sync_db_fix',
		'mw_wc_qbo_sync_session_cn_ls_chk',
		'mw_wc_qbo_sync_wc_cust_role',
		'mw_wc_qbo_sync_wc_cust_role_sync_as_cus',
		'mw_wc_qbo_sync_customer_qbo_check_billing_company',		
		'mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name',
		
		'mw_wc_qbo_sync_compt_p_wtmepo',
		'mw_wc_qbo_sync_compt_p_wapao',
		'mw_wc_qbo_sync_force_shipping_line_item',
		'mw_wc_qbo_sync_skip_os_lid',
		'mw_wc_qbo_sync_wolim_iqilid_desc',
		'mw_wc_qbo_sync_send_inv_sr_afsi_qb',
		'mw_wc_qbo_sync_no_ad_discount_li',
		'mw_wc_qbo_sync_qb_sdioli_isli',
		'mw_wc_qbo_sync_sync_txn_fee_as_ng_li',
		'mw_wc_qbo_sync_sync_skip_cf_ibs_addr',
		'mw_wc_qbo_sync_use_qb_ba_for_eqc',
		'mw_wc_qbo_sync_qb_ns_shipping_li_if_z',
		
		'mw_wc_qbo_sync_qb_ap_tx_aft_discount',
		
		'mw_wc_qbo_sync_wc_avatax_map_qbo_product',
		'mw_wc_qbo_sync_wc_taxify_support',
		'mw_wc_qbo_sync_wc_taxify_map_qbo_product',
		'mw_wc_qbo_sync_enable_wc_deposit',
		'mw_wc_qbo_sync_enable_wc_subs_rnord_sync',
		'mw_wc_qbo_sync_queue_cron_interval_time',
		'mw_wc_qbo_sync_inv_sr_txn_qb_class',
		'mw_wc_qbo_sync_inv_sr_txn_qb_department',
		'mw_wc_qbo_sync_qbo_inventory_start_date',
		'mw_wc_qbo_sync_compt_wapnt_li_date',
		'mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl',
		'mw_wc_qbo_sync_os_mapped_not_matched_invt_lvl_pull',
		'mw_wc_qbo_sync_compt_np_wuqbovendor_ms',
		'mw_wc_qbo_sync_compt_np_wuqbovendor_wcur',
		'mw_wc_qbo_sync_compt_np_wcprdqpef',
		'mw_wc_qbo_sync_invnt_pull_set_prd_stock_sts',
		'mw_wc_qbo_sync_pause_up_qbo_conection',
		'mw_wc_qbo_sync_enable_wc_vpc_epod',
		'mw_wc_qbo_sync_enable_wc_wevc_cvn',
		'mw_wc_qbo_sync_compt_wdotocac_fee_li_ed',
		'mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed',
		'mw_wc_qbo_sync_wacs_base_cur_support',
		'mw_wc_qbo_sync_compt_woacfp_fee_li_ed',
		'mw_wc_qbo_sync_compt_np_wurqbld_ed',
		'mw_wc_qbo_sync_inv_sr_qb_lid_val',
		//
		'mw_wc_qbo_sync_qb_ord_df_val',
		'mw_wc_qbo_sync_qb_pmnt_ref_num_vf',
		//
		'mw_wc_qbo_sync_qb_o_print_status_v',
		'mw_wc_qbo_sync_qb_etpe_ops_o',
		'mw_wc_qbo_sync_qb_soli_sv',
		
		'mw_wc_qbo_sync_hide_vpp_fmp_pages',
		'mw_wc_qbo_sync_ignore_cdc_for_invnt_import',
		
		'mw_wc_qbo_sync_allow_cdc_for_invnt_import',
		'mw_wc_qbo_sync_allow_cdc_for_prc_import',
		
		'mw_wc_qbo_sync_ivnt_pull_interval_time',
		'mw_wc_qbo_sync_prc_pull_interval_time',
		//
		'mw_wc_qbo_sync_os_price_fp_update',
		'mw_wc_qbo_sync_sync_product_images_pp',
		'mw_wc_qbo_sync_compt_np_oli_fee_sync',
		'mw_wc_qbo_sync_compt_np_nfli_asli',
		'mw_wc_qbo_sync_ca_ruso_dqs',
		
		'mw_wc_qbo_sync_compt_yithwgcp_gpc_ed',
		'mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc',
		'mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl',
		'mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod',
		
		'mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed',
		'mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc',
		'mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc',
		
		'mw_wc_qbo_sync_compt_dntp_qbo_item',
		'mw_wc_qbo_sync_compt_dntp_fn_itxt',
		
		'mw_wc_qbo_sync_compt_np_oli_fee_qb_class',
		'mw_wc_qbo_sync_compt_p_wconmkn',
		'mw_wc_qbo_sync_qb_ed_invt_s_frc',
		
		'mw_wc_qbo_sync_os_skip_uprice_l_item',
		'mw_wc_qbo_sync_use_lt_if_ist_l_item',
		'mw_wc_qbo_sync_enable_d_o_q_add_p',
		
		'mw_wc_qbo_sync_wwpfps_qb',
		'mw_wc_qbo_sync_fotali_waste',
		'mw_wc_qbo_sync_zero_ord_spl_qb_class',
		
		'mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item',
		'mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item',
		'mw_wc_qbo_sync_compt_wsc_dis_qbo_item',
		'mw_wc_qbo_sync_all_order_to_customer',
		);
		return $option_keys;
	}
	
	//
	public function get_mpp_bs_msg($p=''){
		$msg = __('Please set a search criteria or click Reset','mw_wc_qbo_sync');
		return $msg;
	}
}
