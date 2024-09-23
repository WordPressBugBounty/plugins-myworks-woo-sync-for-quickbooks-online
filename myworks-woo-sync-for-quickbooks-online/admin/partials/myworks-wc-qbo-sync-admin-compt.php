<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $wpdb;
global $MSQS_QL;
global $MWQS_OF;

$disable_section = true;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$page_url = 'admin.php?page=myworks-wc-qbo-sync-compt';

if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_save_compt_stng', 'map_wc_qbo_update_compt_stng' ) ) {
	$fp_dps = false;
	//$MSQS_QL->_p($_POST);die;
	
	//WooCommerce Deposits
	if(isset($_POST['comp_wcdeposit'])){
		$mw_wc_qbo_sync_enable_wc_deposit = '';
		if(isset($_POST['mw_wc_qbo_sync_enable_wc_deposit'])){
			$mw_wc_qbo_sync_enable_wc_deposit = 'true';
		}
		update_option('mw_wc_qbo_sync_enable_wc_deposit',$mw_wc_qbo_sync_enable_wc_deposit);
	}	
	
	//Visual Products Configurator
	if(isset($_POST['comp_wcvpc'])){
		$mw_wc_qbo_sync_enable_wc_vpc_epod = '';
		if(isset($_POST['mw_wc_qbo_sync_enable_wc_vpc_epod'])){
			$mw_wc_qbo_sync_enable_wc_vpc_epod = 'true';
		}
		update_option('mw_wc_qbo_sync_enable_wc_vpc_epod',$mw_wc_qbo_sync_enable_wc_vpc_epod);
	}	
	
	//WooCommerce EU VAT Number
	if(isset($_POST['comp_wc_wevc_cvn'])){
		$mw_wc_qbo_sync_enable_wc_wevc_cvn = '';
		if(isset($_POST['mw_wc_qbo_sync_enable_wc_wevc_cvn'])){
			$mw_wc_qbo_sync_enable_wc_wevc_cvn = 'true';
		}
		//update_option('mw_wc_qbo_sync_enable_wc_wevc_cvn',$mw_wc_qbo_sync_enable_wc_wevc_cvn);
	}	
	
	//WooCommerce Subscriptions
	if(isset($_POST['comp_wcsubscriptions'])){
		$mw_wc_qbo_sync_enable_wc_subs_rnord_sync = '';
		if(isset($_POST['mw_wc_qbo_sync_enable_wc_subs_rnord_sync'])){
			$mw_wc_qbo_sync_enable_wc_subs_rnord_sync = 'true';
		}
		update_option('mw_wc_qbo_sync_enable_wc_subs_rnord_sync',$mw_wc_qbo_sync_enable_wc_subs_rnord_sync);
		
		//
		$wc_p_methods = array();
		$available_gateways = WC()->payment_gateways()->payment_gateways;
		if(is_array($available_gateways) && !empty($available_gateways)){
			foreach($available_gateways as $key=>$value){
				if($value->enabled=='yes'){
					$wc_p_methods[$value->id] = $value->title;
				}		
			}
		}
		update_option('mw_wc_qbo_sync_available_gateways',$wc_p_methods);
	}
	
	//WooCommerce Measurement Price Calculator
	if(isset($_POST['comp_wmpc'])){
		$mw_wc_qbo_sync_measurement_qty = '';
		if(isset($_POST['mw_wc_qbo_sync_measurement_qty'])){
			$mw_wc_qbo_sync_measurement_qty = 'true';
		}
		update_option('mw_wc_qbo_sync_measurement_qty',$mw_wc_qbo_sync_measurement_qty);
	}	
	
	//WooCommerce AvaTax	
	if(isset($_POST['comp_avatax'])){
		$mw_wc_qbo_sync_wc_avatax_support = '';
		if(isset($_POST['mw_wc_qbo_sync_wc_avatax_support'])){
			$mw_wc_qbo_sync_wc_avatax_support = 'true';
		}
		update_option('mw_wc_qbo_sync_wc_avatax_support',$mw_wc_qbo_sync_wc_avatax_support);
		
		$mw_wc_qbo_sync_wc_avatax_map_qbo_product = '';
		if(isset($_POST['mw_wc_qbo_sync_wc_avatax_map_qbo_product'])){			
			$mw_wc_qbo_sync_wc_avatax_map_qbo_product = (int) $_POST['mw_wc_qbo_sync_wc_avatax_map_qbo_product'];
		}
		update_option('mw_wc_qbo_sync_wc_avatax_map_qbo_product',$mw_wc_qbo_sync_wc_avatax_map_qbo_product);
	}	
	
	//Taxify for WooCommerce
	/*
	if(isset($_POST['comp_taxify'])){
		$mw_wc_qbo_sync_wc_taxify_support = '';
		if(isset($_POST['mw_wc_qbo_sync_wc_taxify_support'])){
			$mw_wc_qbo_sync_wc_taxify_support = 'true';
		}
		update_option('mw_wc_qbo_sync_wc_taxify_support',$mw_wc_qbo_sync_wc_taxify_support);
		
		$mw_wc_qbo_sync_wc_taxify_map_qbo_product = '';
		if(isset($_POST['mw_wc_qbo_sync_wc_taxify_map_qbo_product'])){			
			$mw_wc_qbo_sync_wc_taxify_map_qbo_product = (int) $_POST['mw_wc_qbo_sync_wc_taxify_map_qbo_product'];
		}
		update_option('mw_wc_qbo_sync_wc_taxify_map_qbo_product',$mw_wc_qbo_sync_wc_taxify_map_qbo_product);
	}
	*/
	
	//WooCommerce Shipment Tracking
	if(isset($_POST['comp_wshtr'])){
		$mw_wc_qbo_sync_w_shp_track = '';
		if(isset($_POST['mw_wc_qbo_sync_w_shp_track'])){
			$mw_wc_qbo_sync_w_shp_track = 'true';
		}
		update_option('mw_wc_qbo_sync_w_shp_track',$mw_wc_qbo_sync_w_shp_track);
	}	
	
	//WooCommerce Cost of Goods
	if(isset($_POST['comp_wcogsf'])){
		$mw_wc_qbo_sync_wcogs_fiels = '';
		if(isset($_POST['mw_wc_qbo_sync_wcogs_fiels'])){
			$mw_wc_qbo_sync_wcogs_fiels = 'true';
		}
		update_option('mw_wc_qbo_sync_wcogs_fiels',$mw_wc_qbo_sync_wcogs_fiels);
	}
	
	//WooCommerce Wholesale Prices
	if(isset($_POST['comp_wwpfps'])){
		$mw_wc_qbo_sync_wwpfps_qb = '';
		if(isset($_POST['mw_wc_qbo_sync_wwpfps_qb'])){
			$mw_wc_qbo_sync_wwpfps_qb = 'true';
		}
		update_option('mw_wc_qbo_sync_wwpfps_qb',$mw_wc_qbo_sync_wwpfps_qb);
	}
	
	//QuickBooks Automated Sales Tax (NP)
	if(isset($_POST['comp_fotali'])){
		$mw_wc_qbo_sync_fotali_waste = '';
		if(isset($_POST['mw_wc_qbo_sync_fotali_waste'])){
			$mw_wc_qbo_sync_fotali_waste = 'true';
		}
		update_option('mw_wc_qbo_sync_fotali_waste',$mw_wc_qbo_sync_fotali_waste);
		
		$mw_wc_qbo_sync_otli_qbo_product = '';
		if(isset($_POST['mw_wc_qbo_sync_otli_qbo_product'])){			
			$mw_wc_qbo_sync_otli_qbo_product = (int) $_POST['mw_wc_qbo_sync_otli_qbo_product'];
		}
		update_option('mw_wc_qbo_sync_otli_qbo_product',$mw_wc_qbo_sync_otli_qbo_product);
	}
	
	//WooCommerce - Payment Gateways Discounts and Fees
	if(isset($_POST['comp_wpgdf'])){
		/*
		$mw_wc_qbo_sync_compt_gf_qbo_is = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_gf_qbo_is'])){
			$mw_wc_qbo_sync_compt_gf_qbo_is = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_gf_qbo_is',$mw_wc_qbo_sync_compt_gf_qbo_is);
		
		$mw_wc_qbo_sync_compt_gf_qbo_item = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_gf_qbo_item'])){
			$fp_dps = true;
			$mw_wc_qbo_sync_compt_gf_qbo_item = (int) $_POST['mw_wc_qbo_sync_compt_gf_qbo_item'];
		}
		update_option('mw_wc_qbo_sync_compt_gf_qbo_item',$mw_wc_qbo_sync_compt_gf_qbo_item);
		*/
	}	
	
	//WooCommerce Product Bundles
	if(isset($_POST['comp_wpbs'])){
		$mw_wc_qbo_sync_compt_wpbs = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wpbs'])){
			$mw_wc_qbo_sync_compt_wpbs = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wpbs',$mw_wc_qbo_sync_compt_wpbs);
		
		$mw_wc_qbo_sync_compt_wpbs_ap_item = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wpbs_ap_item'])){			
			$mw_wc_qbo_sync_compt_wpbs_ap_item = (int) $_POST['mw_wc_qbo_sync_compt_wpbs_ap_item'];
		}
		update_option('mw_wc_qbo_sync_compt_wpbs_ap_item',$mw_wc_qbo_sync_compt_wpbs_ap_item);
	}
	
	//WooCommerce Order Fee Line Item (NP)
	if(isset($_POST['comp_woflts'])){
		$mw_wc_qbo_sync_compt_np_oli_fee_sync = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_np_oli_fee_sync'])){
			$mw_wc_qbo_sync_compt_np_oli_fee_sync = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_np_oli_fee_sync',$mw_wc_qbo_sync_compt_np_oli_fee_sync);
		
		$mw_wc_qbo_sync_compt_gf_qbo_item = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_gf_qbo_item'])){
			$fp_dps = true;
			$mw_wc_qbo_sync_compt_gf_qbo_item = (int) $_POST['mw_wc_qbo_sync_compt_gf_qbo_item'];
		}
		update_option('mw_wc_qbo_sync_compt_gf_qbo_item',$mw_wc_qbo_sync_compt_gf_qbo_item);
		
		//
		$mw_wc_qbo_sync_compt_np_oli_fee_qb_class = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_np_oli_fee_qb_class'])){
			$mw_wc_qbo_sync_compt_np_oli_fee_qb_class = trim($_POST['mw_wc_qbo_sync_compt_np_oli_fee_qb_class']);
		}
		update_option('mw_wc_qbo_sync_compt_np_oli_fee_qb_class',$mw_wc_qbo_sync_compt_np_oli_fee_qb_class);
		
		$mw_wc_qbo_sync_compt_np_nfli_asli = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_np_nfli_asli'])){
			$mw_wc_qbo_sync_compt_np_nfli_asli = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_np_nfli_asli',$mw_wc_qbo_sync_compt_np_nfli_asli,false);
		
	}
	
	//WooCommerce Payment Gateway Based Fees
	if(isset($_POST['comp_wpgdf_gbf'])){
		/*
		$mw_wc_qbo_sync_compt_gf_qbo_is_gbf = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_gf_qbo_is_gbf'])){
			$mw_wc_qbo_sync_compt_gf_qbo_is_gbf = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_gf_qbo_is_gbf',$mw_wc_qbo_sync_compt_gf_qbo_is_gbf);		
		
		if(!$fp_dps && isset($_POST['mw_wc_qbo_sync_compt_gf_qbo_item'])){
			$mw_wc_qbo_sync_compt_gf_qbo_item = (int) $_POST['mw_wc_qbo_sync_compt_gf_qbo_item'];
			update_option('mw_wc_qbo_sync_compt_gf_qbo_item',$mw_wc_qbo_sync_compt_gf_qbo_item);
		}
		*/
	}	
	
	//WooCommerce Donation Or Tip On Cart And Checkout
	if(isset($_POST['comp_wdotocac'])){
		$mw_wc_qbo_sync_compt_wdotocac_fee_li_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wdotocac_fee_li_ed'])){
			$mw_wc_qbo_sync_compt_wdotocac_fee_li_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wdotocac_fee_li_ed',$mw_wc_qbo_sync_compt_wdotocac_fee_li_ed);		
		
		if(isset($_POST['mw_wc_qbo_sync_compt_dntp_qbo_item'])){
			$mw_wc_qbo_sync_compt_dntp_qbo_item = (int) $_POST['mw_wc_qbo_sync_compt_dntp_qbo_item'];
			update_option('mw_wc_qbo_sync_compt_dntp_qbo_item',$mw_wc_qbo_sync_compt_dntp_qbo_item);
		}
		
		if(isset($_POST['mw_wc_qbo_sync_compt_dntp_fn_itxt'])){
			$mw_wc_qbo_sync_compt_dntp_fn_itxt = trim($_POST['mw_wc_qbo_sync_compt_dntp_fn_itxt']);
			update_option('mw_wc_qbo_sync_compt_dntp_fn_itxt',$mw_wc_qbo_sync_compt_dntp_fn_itxt);
		}		
	}	
	
	//Woo Add Custom Fee
	if(isset($_POST['comp_woacfp'])){
		/*
		$mw_wc_qbo_sync_compt_woacfp_fee_li_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_woacfp_fee_li_ed'])){
			$mw_wc_qbo_sync_compt_woacfp_fee_li_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_woacfp_fee_li_ed',$mw_wc_qbo_sync_compt_woacfp_fee_li_ed);		
		
		if(!$fp_dps && isset($_POST['mw_wc_qbo_sync_compt_gf_qbo_item'])){
			$mw_wc_qbo_sync_compt_gf_qbo_item = (int) $_POST['mw_wc_qbo_sync_compt_gf_qbo_item'];
			update_option('mw_wc_qbo_sync_compt_gf_qbo_item',$mw_wc_qbo_sync_compt_gf_qbo_item);
		}
		*/
	}
	
	
	//WooCommerce Conditional Product Fees for Checkout Pro
	if(isset($_POST['comp_wcpffcp'])){
		/*
		$mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed'])){
			$mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed',$mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed);		
		
		if(!$fp_dps && isset($_POST['mw_wc_qbo_sync_compt_gf_qbo_item'])){
			$mw_wc_qbo_sync_compt_gf_qbo_item = (int) $_POST['mw_wc_qbo_sync_compt_gf_qbo_item'];
			update_option('mw_wc_qbo_sync_compt_gf_qbo_item',$mw_wc_qbo_sync_compt_gf_qbo_item);
		}
		*/
	}
	
	//WooCommerce Custom Fields
	if(isset($_POST['comp_wccf'])){
		/*
		$mw_wc_qbo_sync_compt_wccf_fee = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wccf_fee'])){
			$mw_wc_qbo_sync_compt_wccf_fee = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wccf_fee',$mw_wc_qbo_sync_compt_wccf_fee);
		*/
		
		$mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map = '';
		$wqm_arr = array();
		if(isset($_POST['wccf_pid']) && is_array($_POST['wccf_pid']) && !empty($_POST['wccf_pid'])){
			$wccf_pid_arr = $_POST['wccf_pid'];
			foreach($wccf_pid_arr as $wpk => $wpid){
				if(is_array($_POST['wccf_qbp']) && isset($_POST['wccf_qbp'][$wpk])){
					$qbpid = (int) $_POST['wccf_qbp'][$wpk];
					$wpid = (int) $wpid;
					if($qbpid && $wpid){
						$wqm_arr[$wpid] = $qbpid;
					}
				}
			}
			if(!empty($wqm_arr)){
				$mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map = $wqm_arr;
			}
		}
		//$MSQS_QL->_p($wqm_arr);die;
		update_option('mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map',$mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map);
	}	
	
	//WooCommerce Checkout Field Editor Pro
	if(isset($_POST['comp_wcfep'])){
		/*
		$mw_wc_qbo_sync_wcfep_add_fld = '';
		if(isset($_POST['mw_wc_qbo_sync_wcfep_add_fld'])){
			$mw_wc_qbo_sync_wcfep_add_fld = 'true';
		}
		update_option('mw_wc_qbo_sync_wcfep_add_fld',$mw_wc_qbo_sync_wcfep_add_fld);
		*/
		
		$mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map = '';
		$wqm_arr = array();
		if(isset($_POST['wcfep_pid']) && is_array($_POST['wcfep_pid']) && !empty($_POST['wcfep_pid'])){
			$wcfep_pid_arr = $_POST['wcfep_pid'];
			foreach($wcfep_pid_arr as $wpk => $wpid){
				if(is_array($_POST['wcfep_qbp']) && isset($_POST['wcfep_qbp'][$wpk])){
					$qbpid = (int) $_POST['wcfep_qbp'][$wpk];
					$wpid = trim($wpid);
					if($qbpid && $wpid!=''){
						$wqm_arr[$wpid] = $qbpid;
					}
				}
			}
			if(!empty($wqm_arr)){
				$mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map = $wqm_arr;
			}
		}
		//$MSQS_QL->_p($wqm_arr);die;
		update_option('mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map',$mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map);
	}	
	
	//WooCommerce Checkout Field Editor
	if(isset($_POST['comp_wcfe_srqcm'])){
		$mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed'])){
			$mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed',$mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed);
		
		$mw_wc_qbo_sync_wcfe_cf_rep_qc_map = '';
		$wcfe_rfcfsr_qc_map_arr = array();
		if(isset($_POST['wcfe_cf_wsr']) && is_array($_POST['wcfe_cf_wsr']) && !empty($_POST['wcfe_cf_wsr'])){
			$wcfe_cf_wsr_arr = $_POST['wcfe_cf_wsr'];
			foreach($wcfe_cf_wsr_arr as $wck => $wcv){
				if(is_array($_POST['wcfe_cf_rep_qc_map']) && isset($_POST['wcfe_cf_rep_qc_map'][$wck])){
					$qbqcid = trim($_POST['wcfe_cf_rep_qc_map'][$wck]);
					$wcv = trim($wcv);
					if($qbqcid && $wcv!=''){
						$wcfe_rfcfsr_qc_map_arr[$wcv] = $qbqcid;
					}
				}
			}
			if(!empty($wcfe_rfcfsr_qc_map_arr)){
				$mw_wc_qbo_sync_wcfe_cf_rep_qc_map = $wcfe_rfcfsr_qc_map_arr;
			}
		}
		
		//$MSQS_QL->_p($wcfe_rfcfsr_qc_map_arr);die;
		update_option('mw_wc_qbo_sync_wcfe_cf_rep_qc_map',$mw_wc_qbo_sync_wcfe_cf_rep_qc_map);
		
		/**/
		$wcfe_srqcm_wfn = (isset($_POST['wcfe_srqcm_wfn']) && !is_array($_POST['wcfe_srqcm_wfn']))?$_POST['wcfe_srqcm_wfn']:'';
		update_option('mw_wc_qbo_sync_wcfe_srqcm_wfn',$wcfe_srqcm_wfn);
	}
	
	//WooCommerce User Role -> QuickBooks Location and Class Map (NP)
	//Class Support Added Later
	if(isset($_POST['comp_np_wurqbld'])){
		$mw_wc_qbo_sync_compt_np_wurqbld_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_np_wurqbld_ed'])){
			$mw_wc_qbo_sync_compt_np_wurqbld_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_np_wurqbld_ed',$mw_wc_qbo_sync_compt_np_wurqbld_ed);
		
		$mw_wc_qbo_sync_wurqbld_wur_qbld_map = '';$mw_wc_qbo_sync_wurqbcls_wur_qbcls_map = '';
		$mw_wc_qbo_sync_wurqbct_wur_qbct_map = '';
		$wurqbld_wur_qbld_map_arr = array();$wurqbcls_wur_qbcls_map_arr = array();$wurqbct_wur_qbct_map_arr = array();
		if(isset($_POST['wurqbld_wur']) && is_array($_POST['wurqbld_wur']) && !empty($_POST['wurqbld_wur'])){
			$wurqbld_wur_arr = $_POST['wurqbld_wur'];
			foreach($wurqbld_wur_arr as $wck => $wcv){
				if(is_array($_POST['wurqbld_wur_qbld_map']) && isset($_POST['wurqbld_wur_qbld_map'][$wck])){
					$qb_ld_id = trim($_POST['wurqbld_wur_qbld_map'][$wck]);
					$wcv = trim($wcv);
					if($qb_ld_id && $wcv!=''){
						$wurqbld_wur_qbld_map_arr[$wcv] = $qb_ld_id;
					}
				}
				
				//
				if(is_array($_POST['wurqbcls_wur_qbcls_map']) && isset($_POST['wurqbcls_wur_qbcls_map'][$wck])){
					$qb_cls_id = trim($_POST['wurqbcls_wur_qbcls_map'][$wck]);
					$wcv = trim($wcv);
					if($qb_cls_id && $wcv!=''){
						$wurqbcls_wur_qbcls_map_arr[$wcv] = $qb_cls_id;
					}
				}
				
				//
				if(is_array($_POST['wurqbct_wur_qbct_map']) && isset($_POST['wurqbct_wur_qbct_map'][$wck])){
					$qb_ct_id = trim($_POST['wurqbct_wur_qbct_map'][$wck]);
					$wcv = trim($wcv);
					if($qb_ct_id && $wcv!=''){
						$wurqbct_wur_qbct_map_arr[$wcv] = $qb_ct_id;
					}
				}
			}
			
			if(!empty($wurqbld_wur_qbld_map_arr)){
				$mw_wc_qbo_sync_wurqbld_wur_qbld_map = $wurqbld_wur_qbld_map_arr;
			}
			
			//
			if(!empty($wurqbcls_wur_qbcls_map_arr)){
				$mw_wc_qbo_sync_wurqbcls_wur_qbcls_map = $wurqbcls_wur_qbcls_map_arr;
			}
			
			//
			if(!empty($wurqbct_wur_qbct_map_arr)){
				$mw_wc_qbo_sync_wurqbct_wur_qbct_map = $wurqbct_wur_qbct_map_arr;
			}
		}
		
		//$MSQS_QL->_p($wurqbct_wur_qbct_map_arr);die;
		update_option('mw_wc_qbo_sync_wurqbld_wur_qbld_map',$mw_wc_qbo_sync_wurqbld_wur_qbld_map);
		update_option('mw_wc_qbo_sync_wurqbcls_wur_qbcls_map',$mw_wc_qbo_sync_wurqbcls_wur_qbcls_map);
		update_option('mw_wc_qbo_sync_wurqbct_wur_qbct_map',$mw_wc_qbo_sync_wurqbct_wur_qbct_map);
	}	
	
	//WooCommerce Hear About Us
	if(isset($_POST['comp_wchau'])){
		$mw_wc_qbo_sync_compt_wchau_enable = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wchau_enable'])){
			$mw_wc_qbo_sync_compt_wchau_enable = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wchau_enable',$mw_wc_qbo_sync_compt_wchau_enable);
		
		$mw_wc_qbo_sync_compt_wchau_wf_qi_map = '';
		$wqm_arr = array();
		if(isset($_POST['wchau_pid']) && is_array($_POST['wchau_pid']) && !empty($_POST['wchau_pid'])){
			$wchau_pid_arr = $_POST['wchau_pid'];
			foreach($wchau_pid_arr as $wpk => $wpid){
				if(is_array($_POST['wchau_qbp']) && isset($_POST['wchau_qbp'][$wpk])){
					$qbpid = (int) $_POST['wchau_qbp'][$wpk];
					$wpid = trim($wpid);
					$wpid = base64_encode($wpid);
					if($qbpid && $wpid!=''){
						$wqm_arr[$wpid] = $qbpid;
					}
				}
			}
			if(!empty($wqm_arr)){
				$mw_wc_qbo_sync_compt_wchau_wf_qi_map = $wqm_arr;
			}
		}
		//$MSQS_QL->_p($wqm_arr);die;
		update_option('mw_wc_qbo_sync_compt_wchau_wf_qi_map',$mw_wc_qbo_sync_compt_wchau_wf_qi_map);
	}	
	
	//WooCommerce Admin Custom Order Fields
	if(isset($_POST['comp_wacof'])){
		$mw_wc_qbo_sync_compt_p_wacof = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wacof'])){
			$mw_wc_qbo_sync_compt_p_wacof = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_p_wacof',$mw_wc_qbo_sync_compt_p_wacof);
		
		$mw_wc_qbo_sync_compt_p_wacof_m_field = 0;
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wacof_m_field']) && (int) $_POST['mw_wc_qbo_sync_compt_p_wacof_m_field']){
			$mw_wc_qbo_sync_compt_p_wacof_m_field = (int) $_POST['mw_wc_qbo_sync_compt_p_wacof_m_field'];
		}
		update_option('mw_wc_qbo_sync_compt_p_wacof_m_field',$mw_wc_qbo_sync_compt_p_wacof_m_field);
		
		$mw_wc_qbo_sync_compt_acof_wf_qi_map = '';
		$wqm_arr = array();
		if($mw_wc_qbo_sync_compt_p_wacof_m_field && isset($_POST['acof_pid']) && is_array($_POST['acof_pid']) && !empty($_POST['acof_pid'])){
			$acof_pid_arr = $_POST['acof_pid'];
			foreach($acof_pid_arr as $wpk => $wpid){
				if(is_array($_POST['acof_qbp']) && isset($_POST['acof_qbp'][$wpk])){
					$qbpid = (int) $_POST['acof_qbp'][$wpk];
					$wpid = trim($wpid);
					$wpid = base64_encode($wpid);
					if($qbpid && $wpid!=''){
						$wqm_arr[$wpid] = $qbpid;
					}
				}
			}
			if(!empty($wqm_arr)){
				$mw_wc_qbo_sync_compt_acof_wf_qi_map = $wqm_arr;
			}
		}
		//$MSQS_QL->_p($wqm_arr);die;
		update_option('mw_wc_qbo_sync_compt_acof_wf_qi_map',$mw_wc_qbo_sync_compt_acof_wf_qi_map);
	}
	
	//WooCommerce Order Delivery
	if(isset($_POST['comp_wod'])){
		$mw_wc_qbo_sync_compt_p_wod = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wod'])){
			$mw_wc_qbo_sync_compt_p_wod = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_p_wod',$mw_wc_qbo_sync_compt_p_wod);
	}
	
	//WooCommerce Sequential Order Numbers Pro
	if(isset($_POST['comp_wsnop'])){
		$mw_wc_qbo_sync_compt_p_wsnop = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wsnop'])){
			$mw_wc_qbo_sync_compt_p_wsnop = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_p_wsnop',$mw_wc_qbo_sync_compt_p_wsnop);
	}
	
	//Custom Order Numbers for WooCommerce
	if(isset($_POST['comp_confw'])){
		$mw_wc_qbo_sync_compt_p_wsnop = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wsnop'])){
			$mw_wc_qbo_sync_compt_p_wsnop = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_p_wsnop',$mw_wc_qbo_sync_compt_p_wsnop);
	}
	
	if(isset($_POST['comp_wsnop_fb_omk'])){
		$mw_wc_qbo_sync_compt_p_wsnop = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wsnop'])){
			$mw_wc_qbo_sync_compt_p_wsnop = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_p_wsnop',$mw_wc_qbo_sync_compt_p_wsnop);
		
		$mw_wc_qbo_sync_compt_p_wconmkn = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wconmkn'])){
			$mw_wc_qbo_sync_compt_p_wconmkn = trim($_POST['mw_wc_qbo_sync_compt_p_wconmkn']);
			update_option('mw_wc_qbo_sync_compt_p_wconmkn',$mw_wc_qbo_sync_compt_p_wconmkn);
		}
	}
	
	//WooCommerce TM Extra Product Options
	if(isset($_POST['comp_wtmepo'])){
		$mw_wc_qbo_sync_compt_p_wtmepo = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wtmepo'])){
			$mw_wc_qbo_sync_compt_p_wtmepo = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_p_wtmepo',$mw_wc_qbo_sync_compt_p_wtmepo);
	}
	
	//WooCommerce Product Add-ons
	if(isset($_POST['comp_wapao'])){
		$mw_wc_qbo_sync_compt_p_wapao = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_p_wapao'])){
			$mw_wc_qbo_sync_compt_p_wapao = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_p_wapao',$mw_wc_qbo_sync_compt_p_wapao);
	}	
	
	//WooCommerce Appointments
	if(isset($_POST['comp_wappointments'])){
		$mw_wc_qbo_sync_compt_wapnt_li_date = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wapnt_li_date'])){
			$mw_wc_qbo_sync_compt_wapnt_li_date = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wapnt_li_date',$mw_wc_qbo_sync_compt_wapnt_li_date);
	}
	
	//WooCommerce USER  ==> QuickBooks Online Vendor (NP)
	if(isset($_POST['comp_np_wuqbovendor'])){
		$mw_wc_qbo_sync_compt_np_wuqbovendor_ms = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_np_wuqbovendor_ms'])){
			$mw_wc_qbo_sync_compt_np_wuqbovendor_ms = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_np_wuqbovendor_ms',$mw_wc_qbo_sync_compt_np_wuqbovendor_ms);
		
		$mw_wc_qbo_sync_compt_np_wuqbovendor_wcur = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_np_wuqbovendor_wcur']) && is_array($_POST['mw_wc_qbo_sync_compt_np_wuqbovendor_wcur']) && !empty($_POST['mw_wc_qbo_sync_compt_np_wuqbovendor_wcur'])){
			$mw_wc_qbo_sync_compt_np_wuqbovendor_wcur = implode(',',$_POST['mw_wc_qbo_sync_compt_np_wuqbovendor_wcur']);
		}
		update_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur',$mw_wc_qbo_sync_compt_np_wuqbovendor_wcur);
	}
	
	//WooCommerce Product ==> QuickBooks Online Product (NP)
	if(isset($_POST['comp_np_wcprdqpef'])){		
		$mw_wc_qbo_sync_compt_np_wcprdqpef = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_np_wcprdqpef'])){
			$mw_wc_qbo_sync_compt_np_wcprdqpef = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_np_wcprdqpef',$mw_wc_qbo_sync_compt_np_wcprdqpef);
	}
	
	//Aelia Currency Switcher for WooCommerce
	if(isset($_POST['comp_wacs_bc'])){
		$mw_wc_qbo_sync_wacs_base_cur_support = '';
		if(isset($_POST['mw_wc_qbo_sync_wacs_base_cur_support'])){
			$mw_wc_qbo_sync_wacs_base_cur_support = 'true';
		}
		update_option('mw_wc_qbo_sync_wacs_base_cur_support',$mw_wc_qbo_sync_wacs_base_cur_support);
	}
	
	//Aelia Currency Switcher for WooCommerce
	if(isset($_POST['comp_wacs_satoc_cb'])){
		$mw_wc_qbo_sync_wacs_satoc_cb = '';
		if(isset($_POST['mw_wc_qbo_sync_wacs_satoc_cb'])){
			$mw_wc_qbo_sync_wacs_satoc_cb = 'true';
		}
		update_option('mw_wc_qbo_sync_wacs_satoc_cb',$mw_wc_qbo_sync_wacs_satoc_cb);
		
		$mw_wc_qbo_sync_wacs_satoc_map_cur_cus = '';
		$satoc_map_arr = array();
		if(isset($_POST['wacs_satoc_cur']) && is_array($_POST['wacs_satoc_cur']) && !empty($_POST['wacs_satoc_cur'])){
			$wacs_satoc_cur_arr = $_POST['wacs_satoc_cur'];
			foreach($wacs_satoc_cur_arr as $wck => $wcv){
				if(is_array($_POST['wacs_satoc_map_cc']) && isset($_POST['wacs_satoc_map_cc'][$wck])){
					$qbcid = trim($_POST['wacs_satoc_map_cc'][$wck]);
					$wcv = trim($wcv);
					if($qbcid && $wcv!=''){
						$satoc_map_arr[$wcv] = $qbcid;
					}
				}
			}
			if(!empty($satoc_map_arr)){
				$mw_wc_qbo_sync_wacs_satoc_map_cur_cus = $satoc_map_arr;
			}
		}
		
		//$MSQS_QL->_p($satoc_map_arr);die;
		update_option('mw_wc_qbo_sync_wacs_satoc_map_cur_cus',$mw_wc_qbo_sync_wacs_satoc_map_cur_cus);
		
		//
		$mw_wc_qbo_sync_wacs_satoc_skip_c_roles = '';
		if(isset($_POST['mw_wc_qbo_sync_wacs_satoc_skip_c_roles'])){
			if(is_array($_POST['mw_wc_qbo_sync_wacs_satoc_skip_c_roles']) && !empty($_POST['mw_wc_qbo_sync_wacs_satoc_skip_c_roles'])){
				$mw_wc_qbo_sync_wacs_satoc_skip_c_roles = implode(',',$_POST['mw_wc_qbo_sync_wacs_satoc_skip_c_roles']);
			}
		}
		update_option('mw_wc_qbo_sync_wacs_satoc_skip_c_roles',$mw_wc_qbo_sync_wacs_satoc_skip_c_roles);
		
	}
	
	//YITH WooCommerce Gift Cards Premium
	if(isset($_POST['comp_yithwgcp'])){
		$mw_wc_qbo_sync_compt_yithwgcp_gpc_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_yithwgcp_gpc_ed'])){
			$mw_wc_qbo_sync_compt_yithwgcp_gpc_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_yithwgcp_gpc_ed',$mw_wc_qbo_sync_compt_yithwgcp_gpc_ed);
		
		if(isset($_POST['mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc'])){
			$mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc = (int) $_POST['mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc'];
			update_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc',$mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc);
		}
		
		$mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl'])){
			$mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl = $MSQS_QL->sanitize($_POST['mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl']);
			update_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl',$mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl);
		}
		
		if(isset($_POST['mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod'])){
			$mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod = (int) $_POST['mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod'];
			update_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod',$mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod);
		}		
	}
	
	//WooCommerce eBay Sync
	if(isset($_POST['comp_wc_ebays_qbl_m'])){
		$mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed'])){
			$mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed',$mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed);
		
		if(isset($_POST['mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc'])){
			$mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc = (int) $_POST['mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc'];
			update_option('mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc',$mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc);
		}
		
		if(isset($_POST['mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc'])){
			$mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc = (int) $_POST['mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc'];
			update_option('mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc',$mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc);
		}
	}
	
	//PW WooCommerce Gift Cards
	if(isset($_POST['comp_pwwgc'])){
		$mw_wc_qbo_sync_compt_pwwgc_gpc_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_pwwgc_gpc_ed'])){
			$mw_wc_qbo_sync_compt_pwwgc_gpc_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_pwwgc_gpc_ed',$mw_wc_qbo_sync_compt_pwwgc_gpc_ed,false);
		
		$mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item'])){
			$fp_dps = true;
			$mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item = intval($_POST['mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item']);
		}
		update_option('mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item',$mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item,false);		
	}
	
	//WooCommerce Gift Cards
	if(isset($_POST['comp_wgcp'])){
		$mw_wc_qbo_sync_compt_wgcp_gpc_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wgcp_gpc_ed'])){
			$mw_wc_qbo_sync_compt_wgcp_gpc_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wgcp_gpc_ed',$mw_wc_qbo_sync_compt_wgcp_gpc_ed,false);
		
		$mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item'])){
			$fp_dps = true;
			$mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item = intval($_POST['mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item']);
		}
		update_option('mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item',$mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item,false);		
	}	
	
	//WooCommerce Smart Coupons
	if(isset($_POST['comp_wscdis'])){
		$mw_wc_qbo_sync_compt_wsc_dis_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wsc_dis_ed'])){
			$mw_wc_qbo_sync_compt_wsc_dis_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_wsc_dis_ed',$mw_wc_qbo_sync_compt_wsc_dis_ed,false);
		
		$mw_wc_qbo_sync_compt_wsc_dis_qbo_item = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_wsc_dis_qbo_item'])){
			$fp_dps = true;
			$mw_wc_qbo_sync_compt_wsc_dis_qbo_item = intval($_POST['mw_wc_qbo_sync_compt_wsc_dis_qbo_item']);
		}
		update_option('mw_wc_qbo_sync_compt_wsc_dis_qbo_item',$mw_wc_qbo_sync_compt_wsc_dis_qbo_item,false);		
	}
	
	//WooCommerce MultiLocation Inventory & Order Routing
	if(isset($_POST['comp_mwrqldm'])){
		$mw_wc_qbo_sync_mwrqldm_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_mwrqldm_ed'])){
			$mw_wc_qbo_sync_mwrqldm_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_mwrqldm_ed',$mw_wc_qbo_sync_mwrqldm_ed);
		
		//
		$mw_wc_qbo_sync_compt_mwrqldm_mv = '';
		$mwrqldm_map_arr = array();
		if(isset($_POST['mwrqldm_wf']) && is_array($_POST['mwrqldm_wf']) && isset($_POST['mwrqldm_qf']) && is_array($_POST['mwrqldm_qf'])){
			$mwrqldm_wf = $_POST['mwrqldm_wf'];
			$mwrqldm_qf = $_POST['mwrqldm_qf'];
			if(is_array($mwrqldm_wf) && !empty($mwrqldm_wf) && is_array($mwrqldm_qf) && !empty($mwrqldm_qf) && count($mwrqldm_wf) == count($mwrqldm_qf)){
				foreach($mwrqldm_wf as $k => $v){
					$mwrqldm_map_arr[$v] = (isset($mwrqldm_qf[$k]))?$mwrqldm_qf[$k]:'';
				}
			}
		}
		
		if(!empty($mwrqldm_map_arr)){
			$mw_wc_qbo_sync_compt_mwrqldm_mv = $mwrqldm_map_arr;
		}
		update_option('mw_wc_qbo_sync_compt_mwrqldm_mv',$mw_wc_qbo_sync_compt_mwrqldm_mv);
	}
	
	//Shipping US State / Canadian Province QuickBooks Location Map Compatibility
	if(isset($_POST['comp_cucsp_qbl_m_sb'])){
		$mw_wc_qbo_sync_compt_cucsp_qbl_map_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_cucsp_qbl_map_ed'])){
			$mw_wc_qbo_sync_compt_cucsp_qbl_map_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_cucsp_qbl_map_ed',$mw_wc_qbo_sync_compt_cucsp_qbl_map_ed);
		
		/*USA*/
		$mw_wc_qbo_sync_cucsp_ship_us_st_qb_loc_map = '';
		$sus_qbloc_m_arr = array();
		if(isset($_POST['susqblocm_us']) && is_array($_POST['susqblocm_us']) && !empty($_POST['susqblocm_us'])){
			$susqblocm_us = $_POST['susqblocm_us'];
			foreach($susqblocm_us as $wck => $wcv){
				if(is_array($_POST['ship_us_st_qb_loc_map']) && isset($_POST['ship_us_st_qb_loc_map'][$wck])){
					$qblocid = trim($_POST['ship_us_st_qb_loc_map'][$wck]);
					$wcv = trim($wcv);
					if($qblocid && $wcv!=''){
						$sus_qbloc_m_arr[$wcv] = $qblocid;
					}
				}
			}
			if(!empty($sus_qbloc_m_arr)){
				$mw_wc_qbo_sync_cucsp_ship_us_st_qb_loc_map = $sus_qbloc_m_arr;
			}
		}
		
		//$MSQS_QL->_p($sus_qbloc_m_arr);
		update_option('mw_wc_qbo_sync_cucsp_ship_us_st_qb_loc_map',$mw_wc_qbo_sync_cucsp_ship_us_st_qb_loc_map);
		
		/*Canada*/
		$mw_wc_qbo_sync_cucsp_ship_ca_pv_qb_loc_map = '';
		$sca_qbloc_m_arr = array();
		if(isset($_POST['scpqblocm_ca']) && is_array($_POST['scpqblocm_ca']) && !empty($_POST['scpqblocm_ca'])){
			$scpqblocm_ca = $_POST['scpqblocm_ca'];
			foreach($scpqblocm_ca as $wck => $wcv){
				if(is_array($_POST['ship_ca_pv_qb_loc_map']) && isset($_POST['ship_ca_pv_qb_loc_map'][$wck])){
					$qblocid = trim($_POST['ship_ca_pv_qb_loc_map'][$wck]);
					$wcv = trim($wcv);
					if($qblocid && $wcv!=''){
						$sca_qbloc_m_arr[$wcv] = $qblocid;
					}
				}
			}
			if(!empty($sca_qbloc_m_arr)){
				$mw_wc_qbo_sync_cucsp_ship_ca_pv_qb_loc_map = $sca_qbloc_m_arr;
			}
		}
		
		//$MSQS_QL->_p($sca_qbloc_m_arr);
		update_option('mw_wc_qbo_sync_cucsp_ship_ca_pv_qb_loc_map',$mw_wc_qbo_sync_cucsp_ship_ca_pv_qb_loc_map);
	}
	
	//Shipping Country, QuickBooks Location Map Compatibility
	if(isset($_POST['comp_oshcntry_qbl_m_sb'])){
		$mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed = '';
		if(isset($_POST['mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed'])){
			$mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed = 'true';
		}
		update_option('mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed',$mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed);
		
		
		$mw_wc_qbo_sync_oshcntry_qb_loc_map = '';
		$oshcntry_qbloc_m_arr = array();
		if(isset($_POST['oshcntry_c']) && is_array($_POST['oshcntry_c']) && !empty($_POST['oshcntry_c'])){
			$oshcntry_c = $_POST['oshcntry_c'];
			foreach($oshcntry_c as $wck => $wcv){
				if(is_array($_POST['ship_oshcntry_qb_loc_map']) && isset($_POST['ship_oshcntry_qb_loc_map'][$wck])){
					$qblocid = trim($_POST['ship_oshcntry_qb_loc_map'][$wck]);
					$wcv = trim($wcv);
					if($qblocid && $wcv!=''){
						$oshcntry_qbloc_m_arr[$wcv] = $qblocid;
					}
				}
			}
			if(!empty($oshcntry_qbloc_m_arr)){
				$mw_wc_qbo_sync_oshcntry_qb_loc_map = $oshcntry_qbloc_m_arr;
			}
		}
		
		//$MSQS_QL->_p($oshcntry_qbloc_m_arr);
		update_option('mw_wc_qbo_sync_oshcntry_qb_loc_map',$mw_wc_qbo_sync_oshcntry_qb_loc_map);
		
	}	
	
	//
	$MSQS_QL->set_session_val('compt_settings_save_msg',__('Compatibility settings saved successfully.','mw_wc_qbo_sync'));
	$MSQS_QL->redirect($page_url);
}

/*
$fee_compt_enable = false;
$fee_li_chk_arr = $MSQS_QL->get_row("SELECT `order_item_id` FROM {$wpdb->prefix}woocommerce_order_items WHERE `order_item_type` = 'fee' AND `order_item_name` != '' AND `order_id` > 0 LIMIT 0, 1");
if(is_array($fee_li_chk_arr) && !empty($fee_li_chk_arr)){
	$fee_compt_enable = true;
}
*/
$fee_compt_enable = true;

$option_keys = $MWQS_OF->get_plugin_option_keys();
$admin_settings_data = $MSQS_QL->get_all_options($option_keys);
//$MSQS_QL->_p($admin_settings_data);
$is_compt = false;

//
$is_fee_plugin = false;

$mw_qbo_product_list = '';
if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
	$mw_qbo_product_list = $MSQS_QL->get_product_dropdown_list('');
}

//
$qbo_customer_options = '';
if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){	
	$cdd_sb = 'dname';
	$mw_wc_qbo_sync_client_sort_order = $MSQS_QL->sanitize($MSQS_QL->get_option('mw_wc_qbo_sync_client_sort_order'));
	if($mw_wc_qbo_sync_client_sort_order!=''){
		$cdd_sb = $mw_wc_qbo_sync_client_sort_order;
		if($cdd_sb!='dname' && $cdd_sb!='first' && $cdd_sb!='last' && $cdd_sb!='company'){
			$cdd_sb = 'dname';
		}
	}
	$qbo_customer_options = $MSQS_QL->option_html('', $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers','qbo_customerid','dname','',$cdd_sb.' ASC','',true);
}

//
$mw_qbo_class_list = $MSQS_QL->get_class_dropdown_list('',true);

//
$mw_qbo_customer_type_list = $MSQS_QL->get_customer_type_dropdown_list('',true);

//
$mw_qbo_location_list = $MSQS_QL->get_department_dropdown_list('');

//
$mw_qbo_account_list = $MSQS_QL->get_account_dropdown_list('',true);

$qbo_payment_method_options = $MSQS_QL->get_payment_method_dropdown_list();

//
$countries_obj   = new WC_Countries();
$countries_list  = $countries_obj->__get('countries');
//$MSQS_QL->_p($countries_list);
//$default_country = $countries_obj->get_base_country();
$us_state_list = $countries_obj->get_states( 'US' );
$ca_state_list = $countries_obj->get_states( 'CA' );

$list_selected = '';
if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_gf_qbo_item\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item'].');';
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_wpbs_ap_item\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_wpbs_ap_item'].');';
	
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_dntp_qbo_item\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_dntp_qbo_item'].');';
	
	
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_wc_avatax_map_qbo_product\').val('.$admin_settings_data['mw_wc_qbo_sync_wc_avatax_map_qbo_product'].');';
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_wc_taxify_map_qbo_product\').val('.$admin_settings_data['mw_wc_qbo_sync_wc_taxify_map_qbo_product'].');';
	
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_otli_qbo_product\').val('.$admin_settings_data['mw_wc_qbo_sync_otli_qbo_product'].');';
	
	//
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item'].');';
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item'].');';
	
	//
	$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_wsc_dis_qbo_item\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_wsc_dis_qbo_item'].');';
}

/**/
$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc'].');';

$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod'].');';

$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_np_oli_fee_qb_class\').val('."'".$admin_settings_data['mw_wc_qbo_sync_compt_np_oli_fee_qb_class']."'".');';

//
$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc'].');';
$list_selected.='jQuery(\'#mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc\').val('.$admin_settings_data['mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc'].');';

?>

<?php
	$wu_roles = get_editable_roles();
?>

<style>
	.wurqbct_tbl select{
		float: none !important;
		width:150px !important;
	}
	
	.wurqbct_tbl .select2-container{
		float: none !important;
		width: 150px !important;
	}
</style>

<?php if(!$disable_section && isset($_GET['debug']) && $_GET['debug'] ==1):?>
	<div style="margin:10px;">
		<!--<h2>Available Addons</h2>-->
		<div style="background:white;padding:5px;">
			<?php 
				//$a_addons = $MSQS_QL->get_compt_plugin_license_addons_arr();
				//$MSQS_QL->_p($a_addons);
			?>
		</div>
	</div>
<?php endif;?>

<h2 class="compt_addon_heading">Compatibility Included / Addons</h2> <!-- + Addons-->

<div class="container map-coupon-code-outer qo-compatibility-addons">
	<form method="post" action="<?php echo $page_url;?>">
	<?php wp_nonce_field( 'myworks_wc_qbo_sync_save_compt_stng', 'map_wc_qbo_update_compt_stng' ); ?>
		
		<!--WooCommerce Measurement Price Calculator-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-measurement-price-calculator')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-measurement-price-calculator"><?php _e( 'WooCommerce Measurement Price Calculator', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Measurement Qty', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_measurement_qty" id="mw_wc_qbo_sync_measurement_qty" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_measurement_qty']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Measurement Price Calculator', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wmpc" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce Deposits-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-deposits','woocommmerce-deposits')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-deposits"><?php _e( 'WooCommerce Deposits', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Deposit Support', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_enable_wc_deposit" id="mw_wc_qbo_sync_enable_wc_deposit" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_enable_wc_deposit']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Deposits', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wcdeposit" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--Visual Products Configurator-->
		<?php if($MSQS_QL->is_plugin_active('visual-product-configurator','vpc')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="visual-product-configurator"><?php _e( 'Visual Products Configurator', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Extra Product Options', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_enable_wc_vpc_epod" id="mw_wc_qbo_sync_enable_wc_vpc_epod" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_enable_wc_vpc_epod']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Visual Products Configurator', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wcvpc" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--WooCommerce EU VAT Number-->
		<?php if($this_section=false && $MSQS_QL->is_plugin_active('woocommerce-eu-vat-number')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-eu-vat-number"><?php _e( 'WooCommerce EU VAT Number', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Customer VAT Number Sync', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_enable_wc_wevc_cvn" id="mw_wc_qbo_sync_enable_wc_wevc_cvn" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_enable_wc_wevc_cvn']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce EU VAT Number', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wc_wevc_cvn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--WooCommerce Subscriptions-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-subscriptions','woocommerce-subscriptions')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-subscriptions"><?php _e( 'WooCommerce Subscriptions', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Renewal Orders Sync', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_enable_wc_subs_rnord_sync" id="mw_wc_qbo_sync_enable_wc_subs_rnord_sync" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_enable_wc_subs_rnord_sync']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Syncing renewal orders automatically to QB', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wcsubscriptions" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce Product Bundles-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-product-bundles')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-product-bundles"><?php _e( 'WooCommerce Product Bundles', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td><?php _e( 'Enable bundle product support', 'mw_wc_qbo_sync' );?>:</td>
							<td>
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wpbs" id="mw_wc_qbo_sync_compt_wpbs" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_wpbs']=='true') echo 'checked' ?>>
							</td>
							<td>
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Enable support for syncing orders that contain bundled products.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<tr>
							<td><?php _e( 'QuickBooks product used to keep line item total accurate', 'mw_wc_qbo_sync' );?>:</td>
							<td>
								<?php
									$dd_options = '<option value=""></option>';
									$dd_ext_class = '';
									if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
										$dd_ext_class = 'mwqs_dynamic_select';
										if((int) $admin_settings_data['mw_wc_qbo_sync_compt_wpbs_ap_item']){
											$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_wpbs_ap_item'];
											$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
											if($qb_item_name!=''){
												$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
											}
										}
									}else{
										$dd_options.=$mw_qbo_product_list;
									}
								?>
								<select name="mw_wc_qbo_sync_compt_wpbs_ap_item" id="mw_wc_qbo_sync_compt_wpbs_ap_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
									<?php echo $dd_options;?>
								</select>
							</td>
							<td>
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Select a QuickBooks Product that will be inserted as the last line of a bundle in QuickBooks if the WooCommerce line item total for a bundle does not match the QuickBooks bundle total - and this product will be used as an adjustment line item to ensure the line item total is correct.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>					
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_wpbs" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>	
				</div>
			</div>
		</div>		
		<?php endif;?>
		
		<!--WooCommerce Order Fee Line Item (NP)-->
		<?php if($enable_this=true && $fee_compt_enable):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title=""><?php _e( 'WooCommerce Order Fee Line Items', 'mw_wc_qbo_sync' );?></h4>
			</div>			
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td><?php _e( 'Sync fee line items in a WooCommerce Order to QuickBooks', 'mw_wc_qbo_sync' );?>:</td>
							<td>
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_np_oli_fee_sync" id="mw_wc_qbo_sync_compt_np_oli_fee_sync" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_np_oli_fee_sync']=='true') echo 'checked' ?>>
							</td>
							<td>
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Enable/Disable syncing "fee" line items in WooCommerce Orders to QuickBooks line items.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<tr>
							<td><?php _e( 'QuickBooks product for fee line item', 'mw_wc_qbo_sync' );?>:</td>
							<td>
								<?php
									$dd_options = '<option value=""></option>';
									$dd_ext_class = '';
									if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
										$dd_ext_class = 'mwqs_dynamic_select';
										if((int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item']){
											$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item'];
											$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
											if($qb_item_name!=''){
												$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
											}
										}
									}else{
										$dd_options.=$mw_qbo_product_list;
									}
								?>
								<select name="mw_wc_qbo_sync_compt_gf_qbo_item" id="mw_wc_qbo_sync_compt_gf_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
									<?php echo $dd_options;?>
								</select>
							</td>
							<td>
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Choose the QuickBooks product that will be used in QuickBooks to represent "fee" line items in WooCommerce.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<?php 
						if(!$MSQS_QL->is_plg_lc_p_l() && ($MSQS_QL->get_qbo_company_setting('ClassTrackingPerTxn') || $MSQS_QL->get_qbo_company_setting('ClassTrackingPerTxnLine'))):
						?>
						<tr>
							<td width="60%"><?php _e( 'QuickBooks class for fee line item', 'mw_wc_qbo_sync' );?> :</td>
							<td width="20%">							
								<select name="mw_wc_qbo_sync_compt_np_oli_fee_qb_class" id="mw_wc_qbo_sync_compt_np_oli_fee_qb_class" class="filled-in production-option mw_wc_qbo_sync_select">
								<option value=""></option>
								<?php echo $mw_qbo_class_list;?>
								</select>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Choose the QuickBooks class that will be used in QuickBooks to represent "fee" line items in WooCommerce.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						<?php endif;?>
						
						<tr>
							<td><?php _e( 'Sync negative fees as a line item (instead of discount line) to QuickBooks', 'mw_wc_qbo_sync' );?>:</td>
							<td>
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_np_nfli_asli" id="mw_wc_qbo_sync_compt_np_nfli_asli" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_np_nfli_asli']=='true') echo 'checked' ?>>
							</td>
							<td>
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Enable/Disable syncing negative "fee" line items in WooCommerce Orders to QuickBooks as line items instead of discount line.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_woflts" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>	
				</div>
			</div>
		</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>
		
		<!--WooCommerce - Payment Gateways Discounts and Fees-->
		<?php if($enable_this=false && $MSQS_QL->is_plugin_active('woocommerce-gateways-discounts-and-fees')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-gateways-discounts-and-fees"><?php _e( 'WooCommerce - Payment Gateways Discounts and Fees', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<!--WooCommerce Gateway Fee Plugin-->
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable gateway fee as a line item to the QuickBooks Online invoice/sales receipt', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_gf_qbo_is" id="mw_wc_qbo_sync_compt_gf_qbo_is" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_is']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce - Payment Gateways Discounts and Fees', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td><?php _e( 'QuickBooks product for discounts and fees line item', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_compt_gf_qbo_item" id="mw_wc_qbo_sync_compt_gf_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wpgdf" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>	
				</div>
			</div>
		</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>
		
		<!--WooCommerce Gateway Fee Plugin-->
		
		
		<!--WooCommerce Payment Gateway Based Fees-->
		<?php if($enable_this=false && $MSQS_QL->is_plugin_active('woocommerce-additional-fees','woocommerce_additional_fees_plugin')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-additional-fees"><?php _e( 'WooCommerce Payment Gateway Based Fees', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable gateway fee as a line item to the QuickBooks Online invoice/sales receipt', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_gf_qbo_is_gbf" id="mw_wc_qbo_sync_compt_gf_qbo_is_gbf" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_is_gbf']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Payment Gateway Based Fees', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<?php if(!$is_fee_plugin):?>
					<tr>
						<td><?php _e( 'QuickBooks product for discounts and fees line item', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_compt_gf_qbo_item" id="mw_wc_qbo_sync_compt_gf_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wpgdf_gbf" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>	
				</div>
			</div>
		</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>		
		
		<!--WooCommerce Donation Or Tip On Cart And Checkout-->
		<?php if($MSQS_QL->is_plugin_active('rp-woo-donation','index')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="rp-woo-donation"><?php _e( 'WooCommerce Donation Or Tip On Cart And Checkout', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable tips/donations as a line item to the QuickBooks Online invoice/sales receipt', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wdotocac_fee_li_ed" id="mw_wc_qbo_sync_compt_wdotocac_fee_li_ed" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_wdotocac_fee_li_ed']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Donation Or Tip On Cart And Checkout', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td><?php _e( 'Donation fee name for identifying donation fee line', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="text" class="" name="mw_wc_qbo_sync_compt_dntp_fn_itxt" id="mw_wc_qbo_sync_compt_dntp_fn_itxt" value="<?php echo $admin_settings_data['mw_wc_qbo_sync_compt_dntp_fn_itxt']?>">
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Donation fee name', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td><?php _e( 'QuickBooks product for donations/tips', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_dntp_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_dntp_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_compt_dntp_qbo_item" id="mw_wc_qbo_sync_compt_dntp_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wdotocac" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>	
				</div>
			</div>
		</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>		
		
		<!--Woo Add Custom Fee-->
		<?php if($enable_this=false && $MSQS_QL->is_plugin_active('woo-add-custom-fee','woo-add-custom-fee.php')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woo-add-custom-fee"><?php _e( 'Woo Add Custom Fee', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable custom fee as a line item to the QuickBooks Online invoice/sales receipt', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_woacfp_fee_li_ed" id="mw_wc_qbo_sync_compt_woacfp_fee_li_ed" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_woacfp_fee_li_ed']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Woo Add Custom Fee', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<?php if(!$is_fee_plugin):?>
					<tr>
						<td><?php _e( 'QuickBooks product for custom fee line item', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_compt_gf_qbo_item" id="mw_wc_qbo_sync_compt_gf_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_woacfp" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>	
				</div>
			</div>
		</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>		
		
		<!--WooCommerce Conditional Product Fees for Checkout Pro-->
		<?php if($enable_this=false && $MSQS_QL->is_plugin_active('woocommerce-conditional-product-fees-for-checkout')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-conditional-product-fees-for-checkout"><?php _e( 'WooCommerce Conditional Product Fees for Checkout Pro', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable product fees as a line item to the QuickBooks Online invoice/sales receipt', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed" id="mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_wcpffcp_fee_li_ed']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Conditional Product Fees for Checkout Pro', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<?php if(!$is_fee_plugin):?>
					<tr>
						<td><?php _e( 'QuickBooks product for discounts and fees line item', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_gf_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_compt_gf_qbo_item" id="mw_wc_qbo_sync_compt_gf_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wcpffcp" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>	
				</div>
			</div>
		</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>		
		
		<!--WooCommerce Order Delivery-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-order-delivery')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-order-delivery"><?php _e( 'WooCommerce Order Delivery', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable delivery date sync as QBO ship date ', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wod" id="mw_wc_qbo_sync_compt_p_wod" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wod']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Order Delivery', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wod" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce Sequential Order Numbers Pro-->
		<?php $is_cwonp_np_eb = false;?>
		<?php
		/**/
		$is_sequential_plugin_active = false;
		$is_seq_pro = false;
		$seq_p_name = '';
		$seq_p_file = '';
		//woocommerce-sequential-order-numbers
		if($MSQS_QL->is_plugin_active('woocommerce-sequential-order-numbers-pro','',true)){
			$is_sequential_plugin_active = true;
			$is_seq_pro = false;
			
			$seq_p_name = 'WooCommerce Sequential Order Numbers Pro';
			$seq_p_file = 'woocommerce-sequential-order-numbers-pro';
		}
		
		if($MSQS_QL->is_only_plugin_active('woocommerce-sequential-order-numbers')){
			$is_sequential_plugin_active = true;
			
			$seq_p_name = 'WooCommerce Sequential Order Numbers';
			$seq_p_file = 'woocommerce-sequential-order-numbers';
		}
		
		?>
		<?php if($is_sequential_plugin_active):?>
		<?php $is_compt=true;?>
		<?php $is_cwonp_np_eb = true;?>
		<div class="page_title">
			<h4 title="<?php echo $seq_p_file;?>"><?php _e( $seq_p_name, 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td width="60%"><?php _e( 'Enable '.$seq_p_name.' Support', 'mw_wc_qbo_sync' );?>:</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wsnop" id="mw_wc_qbo_sync_compt_p_wsnop" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wsnop']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'When enabled, orders will sync into QuickBooks using the "pretty" order number created by '.$seq_p_name.' - instead of the WooCommerce Order ID.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_wsnop" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php else:?>
		<!--Custom Order Number (NP)-->
		<?php
			$np_custom_order_number = true;
		?>		
		<?php if($np_custom_order_number && $MSQS_QL->chk_compt_addons_active('np_custom_order_number','np_custom_order_number')):?>
		<?php $is_compt=true;?>
		<?php $is_cwonp_np_eb = true;?>
		<div class="page_title">
			<h4 title="Custom Order Number"><?php _e( 'Custom Order Number', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td width="60%"><?php _e( 'Enable Custom Order Number Support ', 'mw_wc_qbo_sync' );?> :</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wsnop" id="mw_wc_qbo_sync_compt_p_wsnop" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wsnop']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Custom Order Number - Filter Hook (woocommerce_order_number)', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						<tr>
							<td colspan="3">							
								<input type="submit" name="comp_wsnop" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<?php endif;?>
		
		<!--Custom Order Numbers for WooCommerce-->
		<?php if($MSQS_QL->is_plugin_active('custom-order-numbers-for-woocommerce')):?>
		<?php $is_compt=true;?>
		<?php $is_cwonp_np_eb = true;?>
		<div class="page_title">
			<h4 title="woocommerce-sequential-order-numbers-pro"><?php _e( 'Custom Order Numbers for WooCommerce', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td width="60%"><?php _e( 'Enable Custom Order Number ', 'mw_wc_qbo_sync' );?> :</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wsnop" id="mw_wc_qbo_sync_compt_p_wsnop" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wsnop']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Custom Order Numbers for WooCommerce', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_confw" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--Custom Order Number Field (NP)-->			
		<?php if(!$is_cwonp_np_eb):?>
		<?php $is_compt=true;?>		
		<div class="page_title">
			<h4 title="Custom Order Number"><?php _e( 'Custom Order Number Field', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				    <div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td width="60%"><?php _e( 'Enable Custom Order Number Field Support ', 'mw_wc_qbo_sync' );?> :</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wsnop" id="mw_wc_qbo_sync_compt_p_wsnop" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wsnop']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Custom Order Number Field', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<tr>
							<td width="60%"><?php _e( 'Custom Order Number Field Meta Key Name ', 'mw_wc_qbo_sync' );?> :</td>
							<td width="20%">
								<input type="text" class="filled-in production-option" name="mw_wc_qbo_sync_compt_p_wconmkn" id="mw_wc_qbo_sync_compt_p_wconmkn" value="<?php echo $admin_settings_data['mw_wc_qbo_sync_compt_p_wconmkn'];?>">
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Custom Order Number Field Meta Key', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<tr>
							<td colspan="3">							
								<input type="submit" name="comp_wsnop_fb_omk" id="comp_wsnop_fb_omk" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		
		<!--WooCommerce Custom Fields-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-custom-fields')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-custom-fields"><?php _e( 'WooCommerce Custom Fields (Checkout Fields)', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr style="display:none;">
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr style="display:none;">
						<td><?php _e( 'Enable custom fee as a line item to the QuickBooks Online invoice/sales receipt', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wccf_fee" id="mw_wc_qbo_sync_compt_wccf_fee" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_wccf_fee']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Custom Fields (Checkout Fields)', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<b><?php _e( 'Checkout Custom Fields List', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<?php
					$wccf_fields = $MSQS_QL->get_compt_checkout_fields();
					//$MSQS_QL->_p($wccf_fields);
					?>
					
					<?php if(is_array($wccf_fields) && !empty($wccf_fields)):?>
					<?php
						$f_map_arr = array();
						$f_map_str = $admin_settings_data['mw_wc_qbo_sync_compt_wccf_fee_wf_qi_map'];
						if($f_map_str!=''){
							$f_map_arr = unserialize($f_map_str);
						}
						//$MSQS_QL->_p($f_map_arr);
					?>
					<?php foreach($wccf_fields as $wccf):?>					
					<tr>
						<td><?php echo $wccf['cf_label'];?> :</td>
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if(isset($f_map_arr[$wccf['ID']]) && (int) $f_map_arr[$wccf['ID']]){
										$itemid = (int) $f_map_arr[$wccf['ID']];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<input value="<?php echo $wccf['ID'];?>" type="hidden" name="wccf_pid[]" id="wccf_pid_<?php echo $wccf['ID'];?>">
							<select name="wccf_qbp[]" id="wccf_qbp_<?php echo $wccf['ID'];?>" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QBO Product', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($f_map_arr) && !empty($f_map_arr)):?>
					<?php foreach($f_map_arr as $key => $m_qpi):?>
					<?php 
						$list_selected.='jQuery(\'#wccf_qbp_'.$key.'\').val('.$m_qpi.');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wccf" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>	
					</div>
				</div>
			</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>
		
		<!--WooCommerce Checkout Field Editor Pro-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-checkout-field-editor-pro')):?>
		<?php $is_compt=true;?>
		<?php
			$thwcfe_sections = get_option('thwcfe_sections');			
			//$MSQS_QL->_p($thwcfe_sections['additional']);			
		?>
			<div class="page_title">
				<h4 title="woocommerce-checkout-field-editor-pro"><?php _e( 'WooCommerce Checkout Field Editor Pro', 'mw_wc_qbo_sync' );?></h4>
			</div>
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr style="display:none;">
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr style="display:none;">
							<td width="60%"><?php _e( 'Enable Additional Checkout Fees Field', 'mw_wc_qbo_sync' );?> :</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wcfep_add_fld" id="mw_wc_qbo_sync_wcfep_add_fld" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_wcfep_add_fld']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'WooCommerce Checkout Field Editor Pro', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<tr>
							<td colspan="3">
								<b><?php _e( 'Checkout Additional Custom Fields List (Price Fields)', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						
						<?php if(is_array($thwcfe_sections) && !empty($thwcfe_sections) && isset($thwcfe_sections['additional']) && !empty($thwcfe_sections['additional']) && isset($thwcfe_sections['additional']->fields) && !empty($thwcfe_sections['additional']->fields)):?>
						<?php
							$thwcfe_sections_add = $thwcfe_sections['additional']->fields;
						?>
						<?php
							$wcfep_map_arr = array();
							$wcfep_map_str = $admin_settings_data['mw_wc_qbo_sync_compt_wcfep_price_wf_qi_map'];
							if($wcfep_map_str!=''){
								$wcfep_map_arr = unserialize($wcfep_map_str);
							}
							//$MSQS_QL->_p($wcfep_map_arr);
						?>	
						<?php foreach($thwcfe_sections_add as $thwcfe_add):?>
						<?php if($thwcfe_add->price_field==1):?>
						<tr>
							<td><?php echo $thwcfe_add->title;?> (<?php echo $thwcfe_add->type;?>,Price:<?php echo $thwcfe_add->price;?>) :</td>
							
							<td>
								<?php
									$dd_options = '<option value=""></option>';
									$dd_ext_class = '';
									if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
										$dd_ext_class = 'mwqs_dynamic_select';
										if(isset($wcfep_map_arr[$thwcfe_add->id]) && (int) $wcfep_map_arr[$thwcfe_add->id]){
											$itemid = (int) $wcfep_map_arr[$thwcfe_add->id];
											$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
											if($qb_item_name!=''){
												$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
											}
										}
									}else{
										$dd_options.=$mw_qbo_product_list;
									}
								?>
								<input value="<?php echo $thwcfe_add->id;?>" type="hidden" name="wcfep_pid[]" id="wcfep_pid_<?php echo $thwcfe_add->id;?>">
								<select name="wcfep_qbp[]" id="wcfep_qbp_<?php echo $thwcfe_add->id;?>" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
									<?php echo $dd_options;?>
								</select>
							</td>
							
							<td>
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Map QBO Product', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>
						</tr>
						<?php if(is_array($wcfep_map_arr) && !empty($wcfep_map_arr)):?>
						<?php foreach($wcfep_map_arr as $key => $m_qpi):?>
						<?php 
							$list_selected.='jQuery(\'#wcfep_qbp_'.$key.'\').val('.$m_qpi.');';
						?>
						<?php endforeach;?>
						<?php endif;?>
						
						<?php endif;?>						
						<?php endforeach;?>
						
						<?php endif;?>
					
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_wcfep" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
						</div>
					</div>
				</div>
			</div>
		<?php $is_fee_plugin=true;?>
		<?php endif;?>		
		
		<!--WooCommerce User Role -> QuickBooks Location and Class Map (NP)-->
		<!--Class Support Added Later-->
		<?php $woocommerce_user_role_qbo_location_map = true;?>
		
		<?php if($woocommerce_user_role_qbo_location_map && ($MSQS_QL->get_qbo_company_setting('TrackDepartments') || $MSQS_QL->get_qbo_company_setting('ClassTrackingPerTxn') || !empty($mw_qbo_customer_type_list))): // || $MSQS_QL->get_qbo_company_setting('ClassTrackingPerTxnLine')?>
		<?php $is_compt=true;?>
		
		<div class="page_title">
			<h4 title=""><?php _e( 'WooCommerce User Role -> QuickBooks Location, Class and Customer Type Map', 'mw_wc_qbo_sync' );?></h4>
		</div>
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg wurqbct_tbl" width="100%">
					<tr>
						<td colspan="5">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<tr>
						<td width="44%"><?php _e( 'Enable WooCommerce User Role -> QuickBooks Location, Class and Customer Type Map', 'mw_wc_qbo_sync' );?> :</td>
						<td width="17%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_np_wurqbld_ed" id="mw_wc_qbo_sync_compt_np_wurqbld_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_np_wurqbld_ed')=='true') echo 'checked' ?>>
						</td>
						<td width="17%">&nbsp;</td>
						<td width="17%">&nbsp;</td>
						<td width="5%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce User Role -> QuickBooks Location, Class and Customer Type Map', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
					</tr>
					
					<tr>
						<td colspan="5">
							<b><?php _e( 'WooCommerce User Role -> QuickBooks Location, Class and Customer Type Map', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<tr>
						<td>&nbsp;</td>
						<td><?php _e( 'QuickBooks Location', 'mw_wc_qbo_sync' );?></td>
						<td><?php _e( 'QuickBooks Class', 'mw_wc_qbo_sync' );?></td>
						<td><?php _e( 'QB Customer Type', 'mw_wc_qbo_sync' );?></td>
						<td>&nbsp;</td>
					</tr>
					
					<?php if(is_array($wu_roles) && !empty($wu_roles)):?>
					
					<?php 
						$wurqbld_wur_qbld_map = get_option('mw_wc_qbo_sync_wurqbld_wur_qbld_map');
						//
						$wurqbcls_wur_qbcls_map = get_option('mw_wc_qbo_sync_wurqbcls_wur_qbcls_map');
						//
						$wurqbct_wur_qbct_map = get_option('mw_wc_qbo_sync_wurqbct_wur_qbct_map');
						
						$wu_roles_kv_arr = array();
						foreach ($wu_roles as $role_name => $role_info){
							$wu_roles_kv_arr[$role_name] = $role_info['name'];
						}
						
						$wu_roles_kv_arr['wc_guest_user'] = '<b>Guest User</b>';
					?>
					
					<?php foreach ($wu_roles_kv_arr as $role_k => $role_v):?>
					<tr>
						<td><?php echo $role_v;?></td>
						<td>
							<input value="<?php echo $role_k;?>" type="hidden" name="wurqbld_wur[]" id="wurqbld_wur_<?php echo $role_k;?>">
							<select name="wurqbld_wur_qbld_map[]" id="wurqbld_wur_qbld_map_<?php echo $role_k;?>" class="filled-in production-option mw_wc_qbo_sync_select wurqbct_s">
							<option value=""></option>
							<?php echo $mw_qbo_location_list;?>
							</select>
						</td>
						
						<td>
							<input value="<?php echo $role_k;?>" type="hidden" name="wurqbcls_wur[]" id="wurqbcls_wur_<?php echo $role_k;?>"><!--Not in Use-->
							<select name="wurqbcls_wur_qbcls_map[]" id="wurqbcls_wur_qbcls_map_<?php echo $role_k;?>" class="filled-in production-option mw_wc_qbo_sync_select wurqbct_s">
							<option value=""></option>
							<?php echo $mw_qbo_class_list;?>
							</select>
						</td>
						
						<td>
							<input value="<?php echo $role_k;?>" type="hidden" name="wurqbct_wur[]" id="wurqbct_wur_<?php echo $role_k;?>">
							<select name="wurqbct_wur_qbct_map[]" id="wurqbct_wur_qbct_map_<?php echo $role_k;?>" class="filled-in production-option mw_wc_qbo_sync_select wurqbct_s">
							<option value=""></option>
							<?php echo $mw_qbo_customer_type_list;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QuickBooks Location and Class', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($wurqbld_wur_qbld_map) && !empty($wurqbld_wur_qbld_map)):?>
					<?php foreach($wurqbld_wur_qbld_map as $wrqlm_k => $wrqlm_v):?>
					<?php 
						$list_selected.='jQuery(\'#wurqbld_wur_qbld_map_'.$wrqlm_k.'\').val(\''.$wrqlm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php if(is_array($wurqbcls_wur_qbcls_map) && !empty($wurqbcls_wur_qbcls_map)):?>
					<?php foreach($wurqbcls_wur_qbcls_map as $wrqlm_k => $wrqlm_v):?>
					<?php 
						$list_selected.='jQuery(\'#wurqbcls_wur_qbcls_map_'.$wrqlm_k.'\').val(\''.$wrqlm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<!-->
					<?php if(is_array($wurqbct_wur_qbct_map) && !empty($wurqbct_wur_qbct_map)):?>
					<?php foreach($wurqbct_wur_qbct_map as $wrqlm_k => $wrqlm_v):?>
					<?php 
						$list_selected.='jQuery(\'#wurqbct_wur_qbct_map_'.$wrqlm_k.'\').val(\''.$wrqlm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php endif;?>
					
					<tr>
						<td colspan="4">
							<input type="submit" name="comp_np_wurqbld" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--WooCommerce Checkout Field Editor-->
		<?php $qb_sr_cm_e = false;?>		
		
		<!---->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-checkout-field-editor')):?>
		
		<?php
			$w_cf_sr_options = array();
			$wc_fields_additional = get_option('wc_fields_additional');
			if(is_array($wc_fields_additional) && !empty($wc_fields_additional)){
				foreach($wc_fields_additional as $k => $v){
					if($k == 'sales_rep' && $v['type'] == 'select'){
						$w_cf_sr_options = $v['options'];
					}
				}
			}
			
		?>
		
		<?php if(is_array($w_cf_sr_options) && !empty($w_cf_sr_options)):?>
		<?php $is_compt=true;?>
		<?php $qb_sr_cm_e=true;?>
		
		<div class="page_title">
			<h4 title="woocommerce-checkout-field-editor"><?php _e( 'WooCommerce Checkout Field Editor', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Sales Rep QuickBooks Class Map', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed" id="mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed')=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Checkout Field Editor', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<b><?php _e( 'Sales Rep Mapping (sales_rep -> select type -> additional_field)', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<?php
						/*
						$w_cf_sr_options = array();
						$wc_fields_additional = get_option('wc_fields_additional');
						if(is_array($wc_fields_additional) && !empty($wc_fields_additional)){
							foreach($wc_fields_additional as $k => $v){
								if($k == 'sales_rep' && $v['type'] == 'select'){
									$w_cf_sr_options = $v['options'];
								}
							}
						}
						*/
					?>
					
					<?php //if(is_array($w_cf_sr_options) && !empty($w_cf_sr_options)):?>
					
					<?php 
						$wcfe_cf_rep_qc_map = get_option('mw_wc_qbo_sync_wcfe_cf_rep_qc_map');
					?>
					
					<?php foreach($w_cf_sr_options as $wcf_k => $wcf_v):?>
					<tr>
						<td><?php echo $wcf_v;?></td>
						<td>
							<input value="<?php echo $wcf_k;?>" type="hidden" name="wcfe_cf_wsr[]" id="wcfe_cf_wsr_<?php echo $wcf_k;?>">
							<select name="wcfe_cf_rep_qc_map[]" id="wcfe_cf_rep_qc_map_<?php echo md5($wcf_k);?>" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_class_list;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QuickBooks Class', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($wcfe_cf_rep_qc_map) && !empty($wcfe_cf_rep_qc_map)):?>
					<?php foreach($wcfe_cf_rep_qc_map as $wcrm_k => $wcrm_v):?>
					<?php 
						$list_selected.='jQuery(\'#wcfe_cf_rep_qc_map_'.md5($wcrm_k).'\').val(\''.$wcrm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php //endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wcfe_srqcm" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							<input type="hidden" name="wcfe_srqcm_wfn" value="sales_rep">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		<?php endif;?>
		
		
		<?php if(!$qb_sr_cm_e && $MSQS_QL->is_plugin_active('woocommerce-checkout-field-editor')):?>
		<?php $is_compt=true;?>		
		
		<div class="page_title">
			<h4 title="woocommerce-checkout-field-editor"><?php _e( 'WooCommerce Checkout Field Editor', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Sales Rep QuickBooks Class Map', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed" id="mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed')=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Checkout Field Editor', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<b><?php _e( 'Sales Rep Mapping (sales-rep -> select type -> billing_field)', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<?php
						$w_cf_sr_options = array();
						$wc_fields_billing = get_option('wc_fields_billing');
						if(is_array($wc_fields_billing) && !empty($wc_fields_billing)){
							foreach($wc_fields_billing as $k => $v){
								if($k == 'sales-rep' && $v['type'] == 'select'){
									$w_cf_sr_options = $v['options'];
								}
							}
						}
						
					?>
					
					<?php if(is_array($w_cf_sr_options) && !empty($w_cf_sr_options)):?>
					
					<?php 
						$wcfe_cf_rep_qc_map = get_option('mw_wc_qbo_sync_wcfe_cf_rep_qc_map');
					?>
					
					<?php foreach($w_cf_sr_options as $wcf_k => $wcf_v):?>
					<tr>
						<td><?php echo $wcf_v;?></td>
						<td>
							<input value="<?php echo $wcf_k;?>" type="hidden" name="wcfe_cf_wsr[]" id="wcfe_cf_wsr_<?php echo $wcf_k;?>">
							<select name="wcfe_cf_rep_qc_map[]" id="wcfe_cf_rep_qc_map_<?php echo md5($wcf_k);?>" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_class_list;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QuickBooks Class', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($wcfe_cf_rep_qc_map) && !empty($wcfe_cf_rep_qc_map)):?>
					<?php foreach($wcfe_cf_rep_qc_map as $wcrm_k => $wcrm_v):?>
					<?php 
						$list_selected.='jQuery(\'#wcfe_cf_rep_qc_map_'.md5($wcrm_k).'\').val(\''.$wcrm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wcfe_srqcm" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							<input type="hidden" name="wcfe_srqcm_wfn" value="sales-rep">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--WooCommerce Hear About Us-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-hear-about-us') && $MSQS_QL->get_qbo_company_setting('TrackDepartments')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-hear-about-us"><?php _e( 'WooCommerce Hear About Us', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable hear about us as location to the QuickBooks Online invoice/sales receipt', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wchau_enable" id="mw_wc_qbo_sync_compt_wchau_enable" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_wchau_enable']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Hear About Us', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<b><?php _e( 'WooCommerce Hear About Us Option List', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<?php
					$wchau_options = get_option('wchau_options');
					if($wchau_options!=''){
						$wchau_options = explode(PHP_EOL,$wchau_options);
					}
					//$MSQS_QL->_p($wchau_options);
					?>
					
					<?php if(is_array($wchau_options) && !empty($wchau_options)):?>
					<?php
						$wchau_options = array_map('trim',$wchau_options);
						$f_map_arr = array();
						$f_map_str = $admin_settings_data['mw_wc_qbo_sync_compt_wchau_wf_qi_map'];
						if($f_map_str!=''){
							$f_map_arr = unserialize($f_map_str);
						}
						//$MSQS_QL->_p($f_map_arr);
					?>
					<?php foreach($wchau_options as $wchau_op):?>				
					<tr>
						<td><?php echo $wchau_op;?> :</td>
						<td>							
							<input value="<?php echo $wchau_op;?>" type="hidden" name="wchau_pid[]" id="wchau_pid_<?php echo md5($wchau_op);?>">
							<select name="wchau_qbp[]" id="wchau_qbp_<?php echo md5($wchau_op);?>" class="filled-in production-option mw_wc_qbo_sync_select">
								<option value=""></option>
								<?php echo $mw_qbo_location_list;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QBO Department', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($f_map_arr) && !empty($f_map_arr)):?>
					<?php //$MSQS_QL->_p($f_map_arr);?>
					<?php foreach($f_map_arr as $key => $m_qpi):?>
					<?php
						//echo base64_decode($key).'<br />';
						$list_selected.='jQuery(\'#wchau_qbp_'.md5(base64_decode($key)).'\').val('.$m_qpi.');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wchau" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>	
					</div>
				</div>
			</div>		
		<?php endif;?>		
		
		<!--WooCommerce Admin Custom Order Fields-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-admin-custom-order-fields')):?>
		<?php $is_compt=true;?>
		<?php
			$wacof_fl = get_option('wc_admin_custom_order_fields');			
			//$MSQS_QL->_p($wacof_fl);
		?>
			<div class="page_title">
				<h4 title="woocommerce-admin-custom-order-fields"><?php _e( 'WooCommerce Admin Custom Order Fields', 'mw_wc_qbo_sync' );?></h4>
			</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable Admin Custom Order Fields Map ', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wacof" id="mw_wc_qbo_sync_compt_p_wacof" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wacof']=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Admin Custom Order Fields', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>					
					</tr>
					
					<?php 
					if(is_array($wacof_fl) && !empty($wacof_fl)):
					$wacof_m_f = (int) $admin_settings_data['mw_wc_qbo_sync_compt_p_wacof_m_field'];
					?>
					<tr>
						<td>
							<b><?php _e( 'Select the field', 'mw_wc_qbo_sync' );?></b>
						</td>
						<td>
						<select name="mw_wc_qbo_sync_compt_p_wacof_m_field" id="mw_wc_qbo_sync_compt_p_wacof_m_field" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php foreach($wacof_fl as $aof_k => $aof):?>
							<option <?php if($wacof_m_f==$aof_k){echo ' selected="selected"';}?> value="<?php echo $aof_k;?>"><?php echo $aof['label'].' ('.$aof['type'].')';?><option>
							<?php endforeach;?>
						</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Needs to be \'Select\' Field', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
					</tr>
					
					<?php					
					if($wacof_m_f && isset($wacof_fl[$wacof_m_f]) && $wacof_fl[$wacof_m_f]['type']=='select' && is_array($wacof_fl[$wacof_m_f]['options'])):
					?>
					<tr>
						<td colspan="3">
							<b><?php _e( 'Mapping', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<?php
					$acof_f_options = $wacof_fl[$wacof_m_f]['options'];
					if(!empty($acof_f_options)):
					?>
					<?php
						$f_map_arr = array();
						$f_map_str = $admin_settings_data['mw_wc_qbo_sync_compt_acof_wf_qi_map'];
						if($f_map_str!=''){
							$f_map_arr = unserialize($f_map_str);
						}
						//$MSQS_QL->_p($f_map_arr);
					?>
					<?php foreach($acof_f_options as $w_acof_k => $w_acof_f):?>					
					<tr>
						<td><?php echo $w_acof_f['label'];?> :</td>
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if(is_array($f_map_arr) && !empty($f_map_arr) && isset($f_map_arr[$w_acof_f['value']]) && (int) $f_map_arr[$w_acof_f['value']]){
										$itemid = (int) $f_map_arr[$w_acof_f['value']];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<input value="<?php echo $w_acof_f['value'];?>" type="hidden" name="acof_pid[]" id="acof_pid_<?php echo md5($w_acof_f['value']);?>">
							<select name="acof_qbp[]" id="acof_qbp_<?php echo md5($w_acof_f['value']);?>" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QBO Product', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($f_map_arr) && !empty($f_map_arr)):?>
					<?php foreach($f_map_arr as $key => $m_qpi):?>
					<?php 
						$list_selected.='jQuery(\'#acof_qbp_'.md5(base64_decode($key)).'\').val('.$m_qpi.');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php else:?>
					<tr>
						<td colspan="3">
							<?php _e( 'Field options not found', 'mw_wc_qbo_sync' );?>
						</td>
					</tr>
					<?php endif;?>
					
					<?php else:?>
					<tr>
						<td colspan="3">
							<?php _e( 'No field selected or invalid field - After selecting and saving a valid field you will see the map option', 'mw_wc_qbo_sync' );?>
						</td>
					</tr>
					<?php endif;?>
					
					<?php else:?>
					
					<tr>
						<td colspan="3">
							<?php _e( 'No fields found', 'mw_wc_qbo_sync' );?>
						</td>
					</tr>
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wacof" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce Shipment Tracking-->
		<?php
			$is_ph_wst_pro = $MSQS_QL->is_plugin_active('ph-woocommerce-shipment-tracking','woocommerce-shipment-tracking');
		?>
		<?php if($MSQS_QL->is_plugin_active('woocommerce-shipment-tracking') || $is_ph_wst_pro):?>
		<?php $is_compt=true;?>
		
		<?php 
			$cp_path =  plugin_dir_path(__FILE__);
			$wstp_path = str_replace('myworks-woo-sync-for-quickbooks-online/admin/partials','woocommerce-shipment-tracking',$cp_path);
			
			$wstp_name = 'WooCommerce Shipment Tracking';
			if(!$is_ph_wst_pro && file_exists($wstp_path.'woocommerce-shipment-tracking.php')){
				$wstp_data = get_plugin_data($wstp_path.'woocommerce-shipment-tracking.php');
				//$MSQS_QL->_p($wstp_data);
				
				if(is_array($wstp_data) && !empty($wstp_data) && isset($wstp_data['Name']) && trim($wstp_data['Name']) !=''){
					$wstp_name = trim($wstp_data['Name']);
				}
			}else{
				$wstp_name = 'WooCommerce Shipment Tracking Pro';
			}			
		?>
		
		<div class="page_title">
			<h4 title="woocommerce-shipment-tracking"><?php _e( $wstp_name , 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Shipment Tracking', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_w_shp_track" id="mw_wc_qbo_sync_w_shp_track" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_w_shp_track']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Sync TrackingNum, ShipDate Into QuickBooks Online', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wshtr" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--WooCommerce Cost of Goods-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-cost-of-goods')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-cost-of-goods"><?php _e( 'WooCommerce Cost of Goods', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td width="60%"><?php _e( 'Enable Cost of Goods Support', 'mw_wc_qbo_sync' );?>:</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wcogs_fiels" id="mw_wc_qbo_sync_wcogs_fiels" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_wcogs_fiels']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Enable syncing the COGS for a WooCommerce product into QuickBooks Online.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_wcogsf" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce AvaTax-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-avatax')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-avatax"><?php _e( 'WooCommerce AvaTax', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td width="60%"><?php _e( 'Enable AvaTax Support', 'mw_wc_qbo_sync' );?> :</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wc_avatax_support" id="mw_wc_qbo_sync_wc_avatax_support" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_wc_avatax_support']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'WooCommerce AvaTax', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						<?php 
							//$wc_avatax_specific_tax_locations = get_option('wc_avatax_specific_tax_locations');
							//$qbo_tax_options = '<option value=""></option>';
							//$qbo_tax_options.=$MSQS_QL->get_tax_code_dropdown_list();
						?>
						<tr>
							<td colspan="3">
								<b><?php _e( 'Mapping', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						
						<tr>
							<td>AVATAX</td>
							
							<td>
								<?php
									$dd_options = '<option value=""></option>';
									$dd_ext_class = '';
									if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
										$dd_ext_class = 'mwqs_dynamic_select';
										if((int) $admin_settings_data['mw_wc_qbo_sync_wc_avatax_map_qbo_product']){
											$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_wc_avatax_map_qbo_product'];
											$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
											if($qb_item_name!=''){
												$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
											}
										}
									}else{
										$dd_options.=$mw_qbo_product_list;
									}
								?>
								<select name="mw_wc_qbo_sync_wc_avatax_map_qbo_product" id="mw_wc_qbo_sync_wc_avatax_map_qbo_product" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
									<?php echo $dd_options;?>
								</select>
							</td>
							
							<td>
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'QBO Product', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>
						</tr>
						
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_avatax" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--Taxify for WooCommerce-->
		<?php if(!$disable_section && $MSQS_QL->is_plugin_active('taxify-for-woocommerce','woocommerce-taxify')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="taxify-for-woocommerce"><?php _e( 'Taxify for WooCommerce', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Taxify Support', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wc_taxify_support" id="mw_wc_qbo_sync_wc_taxify_support" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_wc_taxify_support']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Taxify for WooCommerce', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<?php 
						/**/
					?>
					<tr>
						<td colspan="3">
							<b><?php _e( 'Mapping', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<tr>
						<td>Taxify</td>
						
						<td>
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_wc_taxify_map_qbo_product']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_wc_taxify_map_qbo_product'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_wc_taxify_map_qbo_product" id="mw_wc_qbo_sync_wc_taxify_map_qbo_product" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QBO Product', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
					</tr>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_taxify" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce TM Extra Product Options-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-tm-extra-product-options','tm-woo-extra-product-options')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-tm-extra-product-options"><?php _e( 'WooCommerce TM Extra Product Options', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Extra Product Options', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wtmepo" id="mw_wc_qbo_sync_compt_p_wtmepo" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wtmepo']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce TM Extra Product Options', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wtmepo" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--WooCommerce Product Add-ons-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-product-addons')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-product-addons"><?php _e( 'WooCommerce Product Add-ons', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Product Add-ons', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_p_wapao" id="mw_wc_qbo_sync_compt_p_wapao" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_p_wapao']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Product Add-ons', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wapao" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce Appointments-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-appointments')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-appointments"><?php _e( 'WooCommerce Appointments', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Line Item Date', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wapnt_li_date" id="mw_wc_qbo_sync_compt_wapnt_li_date" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_wapnt_li_date']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Add line item date as qbo invoice/salesreceipt txn date', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wappointments" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>		
		
		<!--WooCommerce Product ==> QuickBooks Online Product (NP)-->
		<?php $wc_prd_qbo_prd_ext_fld_compt = true;?>
		<?php if($wc_prd_qbo_prd_ext_fld_compt && $MSQS_QL->is_plugin_active('atum-stock-manager-for-woocommerce')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title=""><?php _e( 'WooCommerce Product ==> QuickBooks Online Product - ATUM - Rosemaria', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'When syncing WooCommerce products to QuickBooks, include the following field mappings', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_np_wcprdqpef" id="mw_wc_qbo_sync_compt_np_wcprdqpef" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_np_wcprdqpef']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Product extra fields', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<b><?php _e( 'Fields', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<tr>
						<td colspan="3">							
							<table>
								<tr>
									<td width="15%">Consignors Commission</td>
									<td width="2%">=></td>
									<td>Purchasing Information</td>
								</tr>
								<tr>
									<td>USER (Username)</td>
									<td>=></td>
									<td>Preferred Vendor</td>
								</tr>
								<tr>
									<td>Reorder Point</td>
									<td>=></td>
									<td>Reorder Point</td>
								</tr>
							</table>					
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_np_wcprdqpef" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce USER  ==> QuickBooks Online Vendor (NP)-->
		<?php $wc_user_to_qbo_vendor_compt = true;?>
		<?php if($wc_user_to_qbo_vendor_compt && $MSQS_QL->is_plugin_active('atum-stock-manager-for-woocommerce')):?>
		<?php $is_compt=true;?>
		
		<div class="page_title">
			<h4 title=""><?php _e( 'WooCommerce USER  ==> QuickBooks Online Vendor - ATUM - Rosemaria', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Allow pushing WooCommerce Users (in specific role) to QuickBooks as a vendor', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_np_wuqbovendor_ms" id="mw_wc_qbo_sync_compt_np_wuqbovendor_ms" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_np_wuqbovendor_ms']=='true') echo 'checked' ?>>							
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Allow pushing WooCommerce Users (in specific role) to QuickBooks as a vendor', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td width="60%"><?php _e( 'Select WooCommerce User Roles', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<?php
							$role_dd_options = '';
							$mw_wc_qbo_sync_compt_np_wuqbovendor_wcur = $MSQS_QL->get_option('mw_wc_qbo_sync_compt_np_wuqbovendor_wcur');
							$mw_wc_qbo_sync_wc_cust_role_exp = explode(',',$mw_wc_qbo_sync_compt_np_wuqbovendor_wcur);
							
							if(is_array($wu_roles) && !empty($wu_roles)){
								foreach ($wu_roles as $role_name => $role_info):
									$selected = '';
									if($mw_wc_qbo_sync_compt_np_wuqbovendor_wcur != ''){
										if( in_array( $role_name, $mw_wc_qbo_sync_wc_cust_role_exp ) ){
											$selected = 'selected="selected"';							
										}else{
											$selected = '';
										}
									}
									$role_dd_options .= '<option value="'.$role_name.'" '.$selected.'>'.$role_name.'</option>';
								endforeach;
							}
							
							?>
							<select name="mw_wc_qbo_sync_compt_np_wuqbovendor_wcur[]" id="mw_wc_qbo_sync_compt_np_wuqbovendor_wcur" class="filled-in production-option mw_wc_qbo_sync_select" multiple="multiple">
								<?php echo $role_dd_options;?>
							</select>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce user roles sync as vendors', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_np_wuqbovendor" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--Aelia Currency Switcher for WooCommerce-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-aelia-currencyswitcher')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-aelia-currencyswitcher"><?php _e( 'Aelia Currency Switcher for WooCommerce', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Base Currency Support', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wacs_base_cur_support" id="mw_wc_qbo_sync_wacs_base_cur_support" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_wacs_base_cur_support')=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Base Currency Support', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wacs_bc" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce Wholesale Prices-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-wholesale-prices','woocommerce-wholesale-prices.bootstrap')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-wholesale-prices"><?php _e( 'WooCommerce Wholesale Prices', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						<tr>
							<td width="60%"><?php _e( 'Enable to Use Wholesale Price When Syncing Product', 'mw_wc_qbo_sync' );?>:</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wwpfps_qb" id="mw_wc_qbo_sync_wwpfps_qb" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_wwpfps_qb']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'Enable syncing the wholesale price as price for a WooCommerce product into QuickBooks Online.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_wwpfps" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--QuickBooks Automated Sales Tax (NP)-->		
		<?php if($MSQS_QL->get_qbo_company_setting('is_automated_sales_tax')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title=""><?php _e( 'QuickBooks Automated Sales Tax', 'mw_wc_qbo_sync' );?></h4>
		</div>		
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<div class="myworks-wc-qbo-sync-table-responsive">
						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
						<tr>
							<td colspan="3">
								<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
							</td>
						</tr>
						
						<tr>
							<td width="60%"><?php _e( 'Sync WooCommerce Order Tax as a Line Item', 'mw_wc_qbo_sync' );?>:</td>
							<td width="20%">
								<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_fotali_waste" id="mw_wc_qbo_sync_fotali_waste" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_fotali_waste']=='true') echo 'checked' ?>>
							</td>
							<td width="20%">
								<div class="material-icons tooltipped tooltip">?
									<span class="tooltiptext">
										<?php _e( 'If enabled, this will override/invalidate any tax mappings set in MyWorks Sync > Map > Taxes, and sync order tax as a line item instead of assigning it to a rate in QuickBooks.', 'mw_wc_qbo_sync' );?>
									</span>
								</div>
							</td>						
						</tr>
						
						<tr>
							<td>
								<?php echo __('QuickBooks Product for Sales Tax line item','mw_wc_qbo_sync') ?>
							</td>
							<td>
								<div class="row">
									<div class="input-field col s12 m12 l12">
										<p>													
											<?php
												$dd_options = '<option value=""></option>';
												$dd_ext_class = '';
												if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
													$dd_ext_class = 'mwqs_dynamic_select';
													if((int) $admin_settings_data['mw_wc_qbo_sync_otli_qbo_product']){
														$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_otli_qbo_product'];
														$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
														if($qb_item_name!=''){
															$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
														}
													}
												}else{
													$dd_options.=$mw_qbo_product_list;
												}
											?>
											<select name="mw_wc_qbo_sync_otli_qbo_product" id="mw_wc_qbo_sync_otli_qbo_product" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
												<?php echo $dd_options;?>
											</select>
										</p>
									</div>
								</div>
							</td>
							
							<td>
								<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
							  <span class="tooltiptext"><?php echo __('Choose a QuickBooks Product that will be the line item in the QuickBooks Invoice/Sales Receipt to represent the sales tax from the WooCommerce Order.','mw_wc_qbo_sync') ?></span>
							</div>
							</td>
						</tr>
						
						<tr>
							<td colspan="3">
								<input type="submit" name="comp_fotali" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--YITH WooCommerce Gift Cards Premium-->
		<?php if($MSQS_QL->is_plugin_active('yith-woocommerce-gift-cards-premium','init')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="yith-woocommerce-gift-cards-premium"><?php _e( 'YITH WooCommerce Gift Cards Premium', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable Gift Payment Compatibility', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_yithwgcp_gpc_ed" id="mw_wc_qbo_sync_compt_yithwgcp_gpc_ed" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_compt_yithwgcp_gpc_ed']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'YITH WooCommerce Gift Cards Premium', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td width="60%"><?php _e( 'Gift Cards Payment Account', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">							
							<select name="mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc" id="mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_account_list;?>
							</select>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks Account for Gift Cards Payment', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr style="display:none">
						<td width="60%"><?php _e( 'Payment Method Label', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">						
							<input type="text" name="mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl" id="mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl" value="<?php echo $admin_settings_data['mw_wc_qbo_sync_compt_yithwgcp_gcp_pm_lbl']?>">
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Payment Method Label ', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td width="60%"><?php _e( 'Gift Cards Payment QuickBooks Online Payment Method', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">							
							<select name="mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod" id="mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $qbo_payment_method_options;?>
							</select>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks Online Payment Method for Gift Cards Payment', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_yithwgcp" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce eBay Sync-->
		<?php if(($MSQS_QL->is_plugin_active('ebaylink') || $MSQS_QL->is_plugin_active('woocommerce-ebay-sync')) && $MSQS_QL->get_qbo_company_setting('TrackDepartments')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="woocommerce-ebay-sync/ebaylink"><?php _e( 'WooCommerce eBay Sync', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable E-bay Order QuickBooks Location Settings', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed" id="mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed" value="true" <?php if($admin_settings_data['mw_wc_qbo_sync_wc_ebay_sync_qb_loc_s_ed']=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'E-bay Order QuickBooks Location Settings', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td width="60%"><?php _e( 'QuickBooks Location for E-bay Orders', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">							
							<select name="mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc" id="mw_wc_qbo_sync_compt_wes_ebay_ord_qb_loc" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_location_list;?>
							</select>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'E-bay Orders QuickBooks Location ', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td width="60%"><?php _e( 'QuickBooks Location for Non E-bay Orders', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">							
							<select name="mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc" id="mw_wc_qbo_sync_compt_wes_oth_ord_qb_loc" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_location_list;?>
							</select>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Non E-bay Orders QuickBooks Location ', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wc_ebays_qbl_m" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--PW WooCommerce Gift Cards-->
		<?php if($MSQS_QL->is_plugin_active('pw-woocommerce-gift-cards','pw-gift-cards')):?>
		<?php 
			$is_compt=true;
			$pwgc_pt = 'PW WooCommerce Gift Cards';$pwgc_pf = 'pw-woocommerce-gift-cards';
			if($MSQS_QL->is_plugin_active('pw-gift-cards')){
				$pwgc_pt = 'PW WooCommerce Gift Cards Pro';
				$pwgc_pf = 'pw-gift-cards';
			}			
		?>
			<div class="page_title">
			<h4 title="<?php echo $pwgc_pf;?>"><?php _e( $pwgc_pt, 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable Gift Payment Compatibility', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_pwwgc_gpc_ed" id="mw_wc_qbo_sync_compt_pwwgc_gpc_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_pwwgc_gpc_ed')=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'PW WooCommerce Gift Cards', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td><?php _e( 'QuickBooks product for gift card payment line item', 'mw_wc_qbo_sync' );?> :</td>
						<td>							
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
								?>
							<select name="mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item" id="mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_pwwgc" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>	
				</div>
			</div>
		</div>		
		<?php endif;?>
		
		<!--WooCommerce Gift Cards-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-gift-cards')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-gift-cards"><?php _e( 'WooCommerce Gift Cards', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable Gift Payment Compatibility', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wgcp_gpc_ed" id="mw_wc_qbo_sync_compt_wgcp_gpc_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_wgcp_gpc_ed')=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Gift Cards', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td><?php _e( 'QuickBooks product for gift card payment line item', 'mw_wc_qbo_sync' );?> :</td>
						<td>							
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item" id="mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wgcp" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>	
				</div>
			</div>
		</div>		
		<?php endif;?>
		
		<!--WooCommerce Smart Coupons-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-smart-coupons')):?>
		<?php $is_compt=true;?>
			<div class="page_title">
			<h4 title="woocommerce-smart-coupons"><?php _e( 'WooCommerce Smart Coupons', 'mw_wc_qbo_sync' );?></h4>
			</div>
			
			<div class="card">
				<div class="card-content">
					<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Enable Smart Coupons Compatibility', 'mw_wc_qbo_sync' );?> :</td>
						<td>
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_wsc_dis_ed" id="mw_wc_qbo_sync_compt_wsc_dis_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_wsc_dis_ed')=='true') echo 'checked' ?>>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce Smart Coupons', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td><?php _e( 'QuickBooks product for smart coupon discount line item', 'mw_wc_qbo_sync' );?> :</td>
						<td>							
							<?php
								$dd_options = '<option value=""></option>';
								$dd_ext_class = '';
								if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
									$dd_ext_class = 'mwqs_dynamic_select';
									if((int) $admin_settings_data['mw_wc_qbo_sync_compt_wsc_dis_qbo_item']){
										$itemid = (int) $admin_settings_data['mw_wc_qbo_sync_compt_wsc_dis_qbo_item'];
										$qb_item_name = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_items','name','itemid',$itemid);
										if($qb_item_name!=''){
											$dd_options = '<option value="'.$itemid.'">'.$qb_item_name.'</option>';
										}
									}
								}else{
									$dd_options.=$mw_qbo_product_list;
								}
							?>
							<select name="mw_wc_qbo_sync_compt_wsc_dis_qbo_item" id="mw_wc_qbo_sync_compt_wsc_dis_qbo_item" class="filled-in production-option mw_wc_qbo_sync_select <?php echo $dd_ext_class;?>">
								<?php echo $dd_options;?>
							</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks product for the above option', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>					
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wscdis" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>	
				</div>
			</div>
		</div>		
		<?php endif;?>
		
		<!--Aelia Currency Switcher for WooCommerce-->
		<?php if($MSQS_QL->is_plugin_active('woocommerce-aelia-currencyswitcher')):?>
		<?php $is_compt=true;?>
		
		<?php
			$aelia_enabled_currencies = array();
			$wc_aelia_currency_switcher = get_option('wc_aelia_currency_switcher');
			if(is_array($wc_aelia_currency_switcher) && !empty($wc_aelia_currency_switcher)){
				if(isset($wc_aelia_currency_switcher['enabled_currencies']) && is_array($wc_aelia_currency_switcher['enabled_currencies'])){
					$aelia_enabled_currencies = $wc_aelia_currency_switcher['enabled_currencies'];
				}
			}
			//$MSQS_QL->_p($aelia_enabled_currencies);
		?>
		
		<div class="page_title">
			<h4 title="woocommerce-aelia-currencyswitcher"><?php _e( 'Aelia Currency Switcher for WooCommerce', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Sync all orders to one QBD Customer Based On Currency', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_wacs_satoc_cb" id="mw_wc_qbo_sync_wacs_satoc_cb" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_wacs_satoc_cb')=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Currency Based Customer Support', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<tr>
						<td colspan="3">
							<b><?php _e( 'Currency -> Customer Mapping', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>

					<?php if(is_array($aelia_enabled_currencies) && !empty($aelia_enabled_currencies)):?>
					<?php 
						$aelia_cur_cus_map = get_option('mw_wc_qbo_sync_wacs_satoc_map_cur_cus');						
					?>
					
					<?php foreach($aelia_enabled_currencies as $aec):?>
					<tr>
						<td><?php echo $aec;?></td>
						<td>
							<?php
							$custId = 0;
							$dd_options = '<option value=""></option>';
							$dd_ext_class = '';
							if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
								$dd_ext_class = 'mwqs_dynamic_select';
								if($custId > 0){
									$qbo_c_dname = $MSQS_QL->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_customers','dname','qbo_customerid',$custId);
									$dd_options = '<option value="'.$custId.'">'.$qbo_c_dname.'</option>';
								}
							}else{
								$dd_options.=$qbo_customer_options;												
							}										
							?>
							<input value="<?php echo $aec;?>" type="hidden" name="wacs_satoc_cur[]" id="wacs_satoc_cur_<?php echo $aec;?>">
							<select name="wacs_satoc_map_cc[]" id="wacs_satoc_map_cc_<?php echo $aec;?>" class="filled-in production-option mw_wc_qbo_sync_select_cus <?php echo $dd_ext_class;?>">
							<?php echo $dd_options;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QBD Customer', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($aelia_cur_cus_map) && !empty($aelia_cur_cus_map)):?>
					<?php foreach($aelia_cur_cus_map as $accm_cur => $accm_cus):?>
					<?php 
						$list_selected.='jQuery(\'#wacs_satoc_map_cc_'.$accm_cur.'\').val(\''.$accm_cus.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>					
					
					<?php
						$wc_user_roles = get_editable_roles();
						$role_dd_options = '';
						if(is_array($wc_user_roles) && !empty($wc_user_roles)){
							$satoc_skip_c_roles = $MSQS_QL->get_option('mw_wc_qbo_sync_wacs_satoc_skip_c_roles');
							$satoc_skip_c_roles_exp = explode(',',$satoc_skip_c_roles);
							foreach ($wc_user_roles as $role_name => $role_info){
								$selected = '';
								if($satoc_skip_c_roles != ''){
									if( in_array( $role_name, $satoc_skip_c_roles_exp ) ){
										$selected = 'selected="selected"';
									}
								}
								$role_dd_options .= '<option value="'.$role_name.'" '.$selected.'>'.$role_name.'</option>';
							}
						}
						
					?>
					
					<tr>
						<th class="title-description">
							<?php echo __('Skip This For These WooCommerce User Roles','mw_wc_qbo_sync') ?>
							
						</th>
						<td>
							<div class="row">
								<div class="input-field col s12 m12 l12">
									<select name="mw_wc_qbo_sync_wacs_satoc_skip_c_roles[]" id="mw_wc_qbo_sync_wacs_satoc_skip_c_roles" class="mw_wc_qbo_sync_select mqs_multi" multiple="multiple">
									<option value=""></option>									
									<?php echo $role_dd_options;?>
									</select>
								</div>
							</div>
						</td>
						<td>
							<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
							  <span class="tooltiptext"><?php echo __('Allow sync customer in QuickBooks for these user roles.','mw_wc_qbo_sync') ?></span>
							</div>
						</td>
					</tr>
					
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_wacs_satoc_cb" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--WooCommerce MultiLocation Inventory & Order Routing-->		
		<?php if($MSQS_QL->is_plugin_active('myworks-warehouse-routing','mw_warehouse_routing')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="myworks-warehouse-routing"><?php _e( 'WooCommerce MultiLocation Inventory & Order Routing', 'mw_wc_qbo_sync' );?></h4>
		</div>		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					<tr>
						<td width="60%"><?php _e( 'Enable MultiLocation QuickBooks Location/Department Support', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_mwrqldm_ed" id="mw_wc_qbo_sync_mwrqldm_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_mwrqldm_ed')=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'WooCommerce MultiLocation QuickBooks Location/Department Support Support', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<?php
						$wi_loc_whr = "post_type='mw_location'";// AND post_status='publish'
						$wi_loc_arr = $MSQS_QL->get_tbl($wpdb->posts,'ID, post_title',$wi_loc_whr,'post_title ASC');
						//$MSQS_QL->_p($wi_loc_arr);
						$w_ccfl = array();
						if(is_array($wi_loc_arr) && !empty($wi_loc_arr)){
							foreach($wi_loc_arr as $wld){
								$w_ccfl[$wld['ID']] = $wld['post_title'];
							}
						}						
						//$MSQS_QL->_p($w_ccfl);
						$mw_wc_qbo_sync_compt_mwrqldm_mv = get_option('mw_wc_qbo_sync_compt_mwrqldm_mv');						
					?>
					
					<tr>
						<td colspan="3">
							<b><?php _e( 'WooCommerce Inventory Locations', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<?php
					if(is_array($w_ccfl) && !empty($w_ccfl)):
					foreach($w_ccfl as $wf_k => $wf_v):
					?>
					<tr>
						<td>
						<?php echo $wf_v;?>
						<input type="hidden" name="mwrqldm_wf[]" value="<?php echo $wf_k;?>">
						</td>
						<td>
						<select id="mwrqldm_tp_<?php echo $wf_k;?>" name="mwrqldm_qf[]" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php 
								$sv = (is_array($mw_wc_qbo_sync_compt_mwrqldm_mv) && isset($mw_wc_qbo_sync_compt_mwrqldm_mv[$wf_k]))?$mw_wc_qbo_sync_compt_mwrqldm_mv[$wf_k]:'';
								$list_selected.='jQuery(\'#mwrqldm_tp_'.$wf_k.'\').val(\''.$sv.'\');';
							?>							
							<?php echo $mw_qbo_location_list;?>
						</select>
						</td>
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'QuickBooks Location/Department', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
					</tr>
					<?php endforeach;?>
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_mwrqldm" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--Shipping US State / Canadian Province QuickBooks Location Map Compatibility-->
		<?php if(!$MSQS_QL->is_plugin_active('custom-us-ca-sp-loc-map-for-myworks-qbo-sync')):?>
		<?php $is_compt=true;?>
		<div class="page_title">
			<h4 title="Shipping US State / Canadian Province, QuickBooks Location Map"><?php _e( 'Shipping US State / Canadian Province, QuickBooks Location Map', 'mw_wc_qbo_sync' );?></h4>
		</div>
		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">					
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<tr>
						<td width="60%"><?php _e( 'Enable Shipping US State / Canadian Province, QuickBooks Location Map', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_cucsp_qbl_map_ed" id="mw_wc_qbo_sync_compt_cucsp_qbl_map_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_cucsp_qbl_map_ed')=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Shipping US State / Canadian Province, QuickBooks Location Map', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<!---->
					<tr class="tr_c_bus_qcm" <?php if(!$MSQS_QL->get_option('mw_wc_qbo_sync_compt_cucsp_qbl_map_ed') =='true') echo 'style="display:none;"' ?>>
						<td colspan="3">
							<b><?php _e( 'Shipping US State, QuickBooks Location Map', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>

					<?php if(is_array($us_state_list) && !empty($us_state_list)):?>
					<?php 
						$ship_us_st_qb_loc_map = get_option('mw_wc_qbo_sync_cucsp_ship_us_st_qb_loc_map');
					?>
					
					<?php foreach($us_state_list as $wcr_k => $wcr_v):?>
					<tr class="tr_c_sus_qcusm" <?php if(!$MSQS_QL->get_option('mw_wc_qbo_sync_compt_cucsp_qbl_map_ed') =='true') echo 'style="display:none;"' ?>>
						<td><?php echo $wcr_v;?></td>
						<td>
							<input value="<?php echo $wcr_k;?>" type="hidden" name="susqblocm_us[]" id="susqblocm_us_<?php echo $wcr_k;?>">
							<select name="ship_us_st_qb_loc_map[]" id="ship_us_st_qb_loc_map_<?php echo $wcr_k;?>" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_location_list;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QuickBooks Location', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($ship_us_st_qb_loc_map) && !empty($ship_us_st_qb_loc_map)):?>
					<?php foreach($ship_us_st_qb_loc_map as $wcrm_k => $wcrm_v):?>
					<?php 
						$list_selected.='jQuery(\'#ship_us_st_qb_loc_map_'.$wcrm_k.'\').val(\''.$wcrm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php endif;?>
					
					<!---->
					<tr class="tr_c_bus_qcm" <?php if(!$MSQS_QL->get_option('mw_wc_qbo_sync_compt_cucsp_qbl_map_ed') =='true') echo 'style="display:none;"' ?>>
						<td colspan="3">
							<b><?php _e( 'Shipping Canadian Province, QuickBooks Location Map', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>

					<?php if(is_array($ca_state_list) && !empty($ca_state_list)):?>
					<?php 
						$ship_ca_pv_qb_loc_map = get_option('mw_wc_qbo_sync_cucsp_ship_ca_pv_qb_loc_map');
					?>
					
					<?php foreach($ca_state_list as $wcr_k => $wcr_v):?>
					<tr class="tr_c_sus_qcusm" <?php if(!$MSQS_QL->get_option('mw_wc_qbo_sync_compt_cucsp_qbl_map_ed') =='true') echo 'style="display:none;"' ?>>
						<td><?php echo $wcr_v;?></td>
						<td>
							<input value="<?php echo $wcr_k;?>" type="hidden" name="scpqblocm_ca[]" id="scpqblocm_ca_<?php echo $wcr_k;?>">
							<select name="ship_ca_pv_qb_loc_map[]" id="ship_ca_pv_qb_loc_map_<?php echo $wcr_k;?>" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_location_list;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QuickBooks Location', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($ship_ca_pv_qb_loc_map) && !empty($ship_ca_pv_qb_loc_map)):?>
					<?php foreach($ship_ca_pv_qb_loc_map as $wcrm_k => $wcrm_v):?>
					<?php 
						$list_selected.='jQuery(\'#ship_ca_pv_qb_loc_map_'.$wcrm_k.'\').val(\''.$wcrm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_cucsp_qbl_m_sb" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		<?php endif;?>
		
		<!--Shipping Country, QuickBooks Location Map Compatibility-->
		<?php
		$sc_qb_loc_map_c = true;
		if($sc_qb_loc_map_c):
		?>
		<div class="page_title">
			<h4 title="Shipping Country, QuickBooks Location Map"><?php _e( 'Shipping Country, QuickBooks Location Map', 'mw_wc_qbo_sync' );?></h4>
		</div>
		
		<div class="card">
			<div class="card-content">
				<div class="col s12 m12 l12">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">					
					<tr>
						<td colspan="3">
							<b><?php _e( 'Settings', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<tr>
						<td width="60%"><?php _e( 'Enable Shipping Country, QuickBooks Location Map', 'mw_wc_qbo_sync' );?> :</td>
						<td width="20%">
							<input type="checkbox" class="filled-in mwqs_st_chk  production-option" name="mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed" id="mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed" value="true" <?php if($MSQS_QL->get_option('mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed')=='true') echo 'checked' ?>>
						</td>
						<td width="20%">
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Shipping Country, QuickBooks Location Map', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>						
					</tr>
					
					<!---->
					<tr class="tr_oshcntry_qcusm"  <?php if(!$MSQS_QL->get_option('mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed') =='true') echo 'style="display:none;"' ?>>
						<td colspan="3">
							<b><?php _e( 'Shipping Country, QuickBooks Location Map', 'mw_wc_qbo_sync' );?></b>
						</td>
					</tr>
					
					<?php if(is_array($countries_list) && !empty($countries_list)):?>
					<?php 
						$ship_oshcntry_qb_loc_map = get_option('mw_wc_qbo_sync_oshcntry_qb_loc_map');
					?>
					
					<?php foreach($countries_list as $wcr_k => $wcr_v):?>
					<tr class="tr_oshcntry_qcusm"  <?php if(!$MSQS_QL->get_option('mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed') =='true') echo 'style="display:none;"' ?>>
						<td><?php echo $wcr_v;?></td>
						<td>
							<input value="<?php echo $wcr_k;?>" type="hidden" name="oshcntry_c[]" id="oshcntry_c<?php echo $wcr_k;?>">
							<select name="ship_oshcntry_qb_loc_map[]" id="ship_oshcntry_qb_loc_map_<?php echo $wcr_k;?>" class="filled-in production-option mw_wc_qbo_sync_select">
							<option value=""></option>
							<?php echo $mw_qbo_location_list;?>
							</select>
						</td>
						
						<td>
							<div class="material-icons tooltipped tooltip">?
								<span class="tooltiptext">
									<?php _e( 'Map QuickBooks Location', 'mw_wc_qbo_sync' );?>
								</span>
							</div>
						</td>
						
					</tr>
					<?php endforeach;?>
					
					<?php if(is_array($ship_oshcntry_qb_loc_map) && !empty($ship_oshcntry_qb_loc_map)):?>
					<?php foreach($ship_oshcntry_qb_loc_map as $wcrm_k => $wcrm_v):?>
					<?php 
						$list_selected.='jQuery(\'#ship_oshcntry_qb_loc_map_'.$wcrm_k.'\').val(\''.$wcrm_v.'\');';
					?>
					<?php endforeach;?>
					<?php endif;?>
					
					<?php endif;?>
					
					<tr>
						<td colspan="3">
							<input type="submit" name="comp_oshcntry_qbl_m_sb" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" value="Save">
						</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		<?php $is_compt=true;?>
		<?php endif;?>
		
		<!--If No Plugin-->
		<?php if(!$is_compt):?>
		<table width="100%">
			<tr>
				<td colspan="3">
					<b><?php _e( 'No Compatibility Included.', 'mw_wc_qbo_sync' );?></b>
				</td>
			</tr>
		</table>
		<?php endif;?>
		
	</form>
</div>
<?php MyWorks_WC_QBO_Sync_Admin::get_settings_assets(1);?>
<?php MyWorks_WC_QBO_Sync_Admin::set_setting_alert($MSQS_QL->get_session_val('compt_settings_save_msg','',true)) ?>
<script type="text/javascript">
jQuery(document).ready(function($){
	<?php echo $list_selected;?>
	jQuery('input.mwqs_st_chk').attr('data-size','small');
	jQuery('input.mwqs_st_chk').bootstrapSwitch();
	
	$("#mw_wc_qbo_sync_compt_p_wconmkn").keyup(function(){
	  var re = /^\w+$/;
	  if ($(this).val()!='' && !re.test($(this).val())) {
		$(this).attr('title',"Invalid Text");
		$(this).css("background-color", "pink");
		$("#comp_wsnop_fb_omk").attr('disabled', 'disabled');
		 return false;
	  }
	  
	  $(this).attr('title',"");
	  $(this).css("background-color", "");
	  $("#comp_wsnop_fb_omk").removeAttr("disabled");
	  return true;	 
	});
	
	//
	jQuery('#mw_wc_qbo_sync_compt_cucsp_qbl_map_ed').on('switchChange.bootstrapSwitch', function (event, state) {		
		if(jQuery("#mw_wc_qbo_sync_compt_cucsp_qbl_map_ed").is(':checked')) {			
			jQuery('.tr_c_bus_qcm').fadeIn("slow");
			jQuery('.tr_c_sus_qcusm').fadeIn("slow");
        }else {
			jQuery('.tr_c_bus_qcm').fadeOut("slow");
			jQuery('.tr_c_sus_qcusm').fadeOut("slow");
        }
	});
	
	//
	jQuery('#mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed').on('switchChange.bootstrapSwitch', function (event, state) {		
		if(jQuery("#mw_wc_qbo_sync_compt_oshcntry_qbl_map_ed").is(':checked')) {			
			jQuery('.tr_oshcntry_qcusm').fadeIn("slow");			
        }else {
			jQuery('.tr_oshcntry_qcusm').fadeOut("slow");			
        }
	});
	
});
</script>
<?php echo $MWQS_OF->get_select2_js('select','qbo_product');?>
<?php echo $MWQS_OF->get_select2_js('.mw_wc_qbo_sync_select_cus','qbo_customer');?>