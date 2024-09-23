<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * Add Refund Into Quickbooks Online.
 *
 * @since    
 * Last Updated: 2019-01-25
*/

$include_this_function = true;

if($include_this_function){
	if($this->is_connected()){
		$manual = $this->get_array_isset($refund_data,'manual',false);
		$wc_inv_id = (int) $this->get_array_isset($refund_data,'wc_inv_id',0);
		$wc_rfnd_id = (int) $this->get_array_isset($refund_data,'refund_id',0);
		$wc_cus_id = (int) $this->get_array_isset($refund_data,'customer_user',0);

		$wc_inv_num = $this->get_array_isset($refund_data,'wc_inv_num','');
		$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;

		//Zero Total Option Check
		$_refund_amount = $this->get_array_isset($refund_data,'_refund_amount',0);
		$_order_total = $this->get_array_isset($refund_data,'_order_total',0);
		if($this->option_checked('mw_wc_qbo_sync_null_invoice')){
			if($_refund_amount==0 || $_refund_amount<0 || $_order_total==0 || $_order_total<0){
				$this->save_log('Export Refund Error #'.$wc_rfnd_id,'Refund or Order amount 0 not allowed in setting ','Refund',2);
				return false;
			}
		}
		
		if($this->if_sync_refund($refund_data)){
			//$this->add_txt_to_log_file('Refund Test');
			$qbo_customer_id = (int) $this->get_array_isset($refund_data,'qbo_customerid',0);
			if(!$this->if_refund_exists($refund_data)){
				//
				$_billing_email = $this->get_array_isset($refund_data,'_billing_email','');
				
				$Context = $this->Context;
				$realm = $this->realm;

				$RefundReceiptService = new QuickBooks_IPP_Service_RefundReceipt();
				$RefundReceipt = new QuickBooks_IPP_Object_RefundReceipt();

				$wc_inv_date = $this->get_array_isset($refund_data,'wc_inv_date','');
				$wc_inv_date = $this->view_date($wc_inv_date);

				$wc_rfnd_date = $this->get_array_isset($refund_data,'refund_date','');
				$wc_rfnd_date = $this->view_date($wc_rfnd_date);

				//$RefundReceipt->setDocNumber($wc_inv_id.'-'.$wc_rfnd_id);
				$RefundReceipt->setDocNumber($ord_id_num.'-'.$wc_rfnd_id);
				
				$RefundReceipt->setCustomerRef("{-$qbo_customer_id}");
				//
				$BillEmail = new QuickBooks_IPP_Object_BillEmail();
				$BillEmail->setAddress($_billing_email);
				$RefundReceipt->setBillEmail($BillEmail);

				#New - Refund Bill / Ship Address

				//BillAddr
				$skip_billing_address = $this->get_array_isset($refund_data,'skip_billing_address',false);
				if(!$skip_billing_address){
					$BillAddr = new QuickBooks_IPP_Object_BillAddr();
					$BillAddr->setLine1($this->get_array_isset($refund_data,'_billing_first_name','',true).' '.$this->get_array_isset($refund_data,'_billing_last_name','',true));
					
					//01-06-2017
					$is_cf_bf_applied = false;
					$_billing_phone_cfmk = $this->get_cf_cmd_concat_k('wopn_for_ba_sa',$cfm_iv);
					if(isset($cf_map_data[$_billing_phone_cfmk]) && $cf_map_data[$_billing_phone_cfmk]!=''){
						$bp_a = explode(',',$cf_map_data[$_billing_phone_cfmk]);
						if(is_array($bp_a) && in_array('bill_addr',array_map('trim', $bp_a))){
							$_billing_phone = $this->get_array_isset($refund_data,'_billing_phone','',true);
							if($_billing_phone!=''){
								$BillAddr->setLine2($_billing_phone);
								$is_cf_bf_applied = true;
							}
						}
					}
					
					if($is_cf_bf_applied){
						$BillAddr->setLine3($this->get_array_isset($refund_data,'_billing_company','',true));
						$BillAddr->setLine4($this->get_array_isset($refund_data,'_billing_address_1','',true));
						$BillAddr->setLine5($this->get_array_isset($refund_data,'_billing_address_2','',true));
					}else{
						$BillAddr->setLine2($this->get_array_isset($refund_data,'_billing_company','',true));
						$BillAddr->setLine3($this->get_array_isset($refund_data,'_billing_address_1','',true));
						$BillAddr->setLine4($this->get_array_isset($refund_data,'_billing_address_2','',true));
					}

					$BillAddr->setCity($this->get_array_isset($refund_data,'_billing_city','',true));

					$country = $this->get_array_isset($refund_data,'_billing_country','',true);
					$country = $this->get_country_name_from_code($country);
					/**/
					if($this->is_plugin_active('custom-us-ca-sp-loc-map-for-myworks-qbo-sync') && $this->option_checked('mw_wc_qbo_sync_compt_cucsp_opn_bsa_ed')){
						$_billing_phone = $this->get_array_isset($refund_data,'_billing_phone','',true);
						if(!empty($_billing_phone)){
							$country = $_billing_phone;
						}
					}
					
					if(!$this->option_checked('mw_wc_qbo_sync_sync_skip_cf_ibs_addr')){
						$BillAddr->setCountry($country);
					}				
					
					$BillAddr->setCountrySubDivisionCode($this->get_array_isset($refund_data,'_billing_state','',true));
					$BillAddr->setPostalCode($this->get_array_isset($refund_data,'_billing_postcode','',true));
					$RefundReceipt->setBillAddr($BillAddr);
				}
				
				//ShipAddr
				$skip_shipping_address = false;
				if(!$skip_shipping_address){
					$ShipAddr = new QuickBooks_IPP_Object_ShipAddr();
					$ShipAddr->setLine1($this->get_array_isset($refund_data,'_shipping_first_name','',true).' '.$this->get_array_isset($refund_data,'_shipping_last_name','',true));

					$is_cf_bf_applied = false;
					$_billing_phone_cfmk = $this->get_cf_cmd_concat_k('wopn_for_ba_sa',$cfm_iv);
					if(isset($cf_map_data[$_billing_phone_cfmk]) && $cf_map_data[$_billing_phone_cfmk]!=''){
						$bp_a = explode(',',$cf_map_data[$_billing_phone_cfmk]);
						if(is_array($bp_a) && in_array('ship_addr',array_map('trim', $bp_a))){
							$_billing_phone = $this->get_array_isset($refund_data,'_billing_phone','',true);
							if($_billing_phone!=''){
								$ShipAddr->setLine2($_billing_phone);
								$is_cf_bf_applied = true;
							}
						}
					}
					
					if($is_cf_bf_applied){
						$ShipAddr->setLine3($this->get_array_isset($refund_data,'_shipping_company','',true));
						$ShipAddr->setLine4($this->get_array_isset($refund_data,'_shipping_address_1','',true));
						$ShipAddr->setLine5($this->get_array_isset($refund_data,'_shipping_address_2','',true));
					}else{
						$ShipAddr->setLine2($this->get_array_isset($refund_data,'_shipping_company','',true));
						$ShipAddr->setLine3($this->get_array_isset($refund_data,'_shipping_address_1','',true));
						$ShipAddr->setLine4($this->get_array_isset($refund_data,'_shipping_address_2','',true));
					}

					$ShipAddr->setCity($this->get_array_isset($refund_data,'_shipping_city','',true));

					$country = $this->get_array_isset($refund_data,'_shipping_country','',true);
					$country = $this->get_country_name_from_code($country);
					/**/
					if($this->is_plugin_active('custom-us-ca-sp-loc-map-for-myworks-qbo-sync') && $this->option_checked('mw_wc_qbo_sync_compt_cucsp_opn_bsa_ed')){
						$_billing_phone = $this->get_array_isset($refund_data,'_billing_phone','',true);
						if(!empty($_billing_phone)){
							$country = $_billing_phone;
						}
					}
					
					if(!$this->option_checked('mw_wc_qbo_sync_sync_skip_cf_ibs_addr')){
						$ShipAddr->setCountry($country);
					}
					
					$ShipAddr->setCountrySubDivisionCode($this->get_array_isset($refund_data,'_shipping_state','',true));
					$ShipAddr->setPostalCode($this->get_array_isset($refund_data,'_shipping_postcode','',true));
					$RefundReceipt->setShipAddr($ShipAddr);
				}
				
				
				$RefundReceipt->setTxnDate($wc_rfnd_date);

				$_order_currency = $this->get_array_isset($refund_data,'_order_currency','',true);
				//
				$_payment_method = $this->get_array_isset($refund_data,'_payment_method','',true);
				$pm_map_data = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);
				
				$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
				if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){

					$currency_rate_date = $wc_inv_date;
					$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);

					$RefundReceipt->setCurrencyRef($_order_currency);
					$RefundReceipt->setExchangeRate($currency_rate);
				}
				
				/**/
				$_transaction_id = $this->get_array_isset($refund_data,'transaction_id','',true);
				$RefNum = $ord_id_num;
				if($this->get_option('mw_wc_qbo_sync_qb_pmnt_ref_num_vf') == 'TXN_ID'){
					$RefNum = $_transaction_id;
				}
				
				if(strlen($RefNum) > 21){
					$RefNum = substr($RefNum,-21);
				}
				
				$RefundReceipt->setPaymentRefNum($RefNum);
				
				$qbo_inv_items = (isset($refund_data['qbo_inv_items']))?$refund_data['qbo_inv_items']:array();
				
				$is_partial = false;

				if($_order_total!=$_refund_amount){
					$is_partial = true;
				}

				//$is_partial = true;
				$rd_dtls = $this->get_wc_order_details_from_order($wc_rfnd_id,get_post($wc_rfnd_id));
				$rd_qii = array();$skp_rli_wp_ids = array();$skp_rli_wv_ids = array();$r_shp_dtls = array();$r_tx_dtls = array();
				if($is_partial && $wc_rfnd_id >0){					
					$rd_qii = (isset($rd_dtls['qbo_inv_items']))?$rd_dtls['qbo_inv_items']:array();
					
					$r_shp_dtls = (isset($rd_dtls['shipping_details']))?$rd_dtls['shipping_details']:array();
					$r_tx_dtls = (isset($rd_dtls['tax_details']))?$rd_dtls['tax_details']:array();
					
					/*
					if(is_array($rd_qii) && count($rd_qii)){					
						foreach($rd_qii as $ri){
							$skp_rli_wp_ids[] = $ri['product_id'];
							if(isset($ri['variation_id']) && $ri['variation_id'] > 0){
								$skp_rli_wv_ids[] = $ri['variation_id'];
							}
						}
					}
					
					if(!empty($skp_rli_wp_ids) || !empty($skp_rli_wv_ids)){
						if(is_array($qbo_inv_items) && count($qbo_inv_items)){						
							foreach($qbo_inv_items as $qi_k => $qbo_item){
								if(!empty($skp_rli_wp_ids) && $qbo_item['product_id'] > 0 && !in_array($qbo_item['product_id'],$skp_rli_wp_ids)){
									unset($qbo_inv_items[$qi_k]);
								}
								
								if(!empty($skp_rli_wv_ids) && $qbo_item['variation_id'] > 0 && !in_array($qbo_item['variation_id'],$skp_rli_wv_ids)){
									unset($qbo_inv_items[$qi_k]);
								}
							}
						}
					}
					*/
					
					$qbo_inv_items = $rd_qii;
					if(empty($qbo_inv_items) && empty($r_shp_dtls)){
						//$qbo_inv_items = (isset($refund_data['qbo_inv_items']))?$refund_data['qbo_inv_items']:array();
					}
					
					//$qbo_inv_items = array_values($qbo_inv_items);
					$refund_data['shipping_details'] = $r_shp_dtls;
					$refund_data['tax_details'] = $r_tx_dtls;
				}
				
				/*Count Total Amounts*/
				$_cart_discount = $this->get_array_isset($refund_data,'_cart_discount',0);
				$_cart_discount_tax = $this->get_array_isset($refund_data,'_cart_discount_tax',0);

				$_order_tax = (float) $this->get_array_isset($refund_data,'_order_tax',0);
				$_order_shipping_tax = (float) $this->get_array_isset($refund_data,'_order_shipping_tax',0);
				$_order_total_tax = ($_order_tax+$_order_shipping_tax);

				$order_shipping_total = $this->get_array_isset($refund_data,'order_shipping_total',0);
				
				//Qbo settings
				$qbo_is_sales_tax = $this->get_qbo_company_setting('is_sales_tax');
				$qbo_company_country = $this->get_qbo_company_info('country');

				$qbo_is_shipping_allowed = $this->get_qbo_company_setting('is_shipping_allowed');
				if($this->option_checked('mw_wc_qbo_sync_odr_shipping_as_li')){
					$qbo_is_shipping_allowed = false;
				}
				
				$is_automated_sales_tax = $this->get_qbo_company_setting('is_automated_sales_tax');
				if($is_automated_sales_tax){
					$qbo_is_sales_tax = false;
				}
				
				/**/
				if($is_partial && $wc_rfnd_id >0){
					if(!empty($r_shp_dtls)){
						$order_shipping_total = (float) $r_shp_dtls[0]['cost'];
						$order_shipping_total = $this->_abs($order_shipping_total);
					}
					
					if(!empty($rd_dtls)){
						$_cart_discount = $this->get_array_isset($rd_dtls,'_cart_discount',0);
						$_cart_discount = $this->_abs($_cart_discount);
						$_cart_discount_tax = $this->get_array_isset($rd_dtls,'_cart_discount_tax',0);
						$_cart_discount_tax = $this->_abs($_cart_discount_tax);
				
						$_order_tax = (float) $this->get_array_isset($rd_dtls,'_order_tax',0);
						$_order_tax = $this->_abs($_order_tax);
						$_order_shipping_tax = (float) $this->get_array_isset($rd_dtls,'_order_shipping_tax',0);
						$_order_shipping_tax = $this->_abs($_order_shipping_tax);
						$_order_total_tax = ($_order_tax+$_order_shipping_tax);
						
						$order_shipping_total = $this->get_array_isset($rd_dtls,'order_shipping_total',0);
						$order_shipping_total = $this->_abs($order_shipping_total);
					}
				}

				#New
				$is_sync_shipping_line = true;
				if($this->option_checked('mw_wc_qbo_sync_qb_ns_shipping_li_if_z') && $order_shipping_total <= 0){
					$qbo_is_shipping_allowed = false;
					$is_sync_shipping_line = false;
				}
				
				//Tax rates
				$qbo_tax_code = '';
				$apply_tax = false;
				$is_tax_applied = false;
				$is_inclusive = false;

				$qbo_tax_code_shipping = '';

				$tax_rate_id = 0;
				$tax_rate_id_2 = 0;

				$tax_details = (isset($refund_data['tax_details']))?$refund_data['tax_details']:array();
				
				$is_avatax_active = false;
				/*
				$wc_avatax_enable_tax_calculation = get_option('wc_avatax_enable_tax_calculation');
				if($this->is_plugin_active('woocommerce-avatax') && $this->option_checked('mw_wc_qbo_sync_wc_avatax_support') && $wc_avatax_enable_tax_calculation=='yes'){
				  $is_avatax_active = true;
				  $qbo_is_sales_tax = false;
				}
				*/
				
				//
				$is_so_tax_as_li = false;
				if($this->option_checked('mw_wc_qbo_sync_odr_tax_as_li') && !$is_automated_sales_tax){
					$is_so_tax_as_li = true;
					$qbo_is_sales_tax = false;
				}
				
				//
				$ast_force_tax_as_li = $this->option_checked('mw_wc_qbo_sync_fotali_waste');
				if($ast_force_tax_as_li){
					$is_so_tax_as_li = true;
					$qbo_is_sales_tax = false;
				}
				
				//New - Tax Condition
				/* || $is_automated_sales_tax*/
				if($qbo_is_sales_tax){
					if(count($tax_details)){
						$tax_rate_id = $tax_details[0]['rate_id'];
					}
					if(count($tax_details)>1){
						if($this->_abs($tax_details[1]['tax_amount'])>0){
							$tax_rate_id_2 = $tax_details[1]['rate_id'];
						}
					}
					
					if(count($tax_details)>1 && $qbo_is_shipping_allowed){
						foreach($tax_details as $td){
						  if($this->_abs($td['tax_amount'])==0 && $this->_abs($td['shipping_tax_amount'])>0){
							$qbo_tax_code_shipping = $this->get_qbo_mapped_tax_code($td['rate_id'],0);
							break;
						  }
						}
					}
					
					$qbo_tax_code = $this->get_qbo_mapped_tax_code($tax_rate_id,$tax_rate_id_2);
					if($qbo_tax_code!='' || $qbo_tax_code!='NON'){
						if($qbo_is_sales_tax){
							$apply_tax = true;
						}
					}

					$Tax_Code_Details = $this->mod_qbo_get_tx_dtls($qbo_tax_code);					
					$is_qbo_dual_tax = false;
					
					if(count($Tax_Code_Details)){
						if($Tax_Code_Details['TaxGroup'] && count($Tax_Code_Details['TaxRateDetail'])>1){
							$is_qbo_dual_tax = true;
						}
					}

					
					$Tax_Rate_Ref = (isset($Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef']:'';
					$TaxPercent = $this->get_qbo_tax_rate_value_by_key($Tax_Rate_Ref);
					$Tax_Name = (isset($Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef_name']:'';

					$NetAmountTaxable = 0;

					if($is_qbo_dual_tax){
						$Tax_Rate_Ref_2 = (isset($Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef']:'';
						$TaxPercent_2 = $this->get_qbo_tax_rate_value_by_key($Tax_Rate_Ref_2);
						$Tax_Name_2 = (isset($Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef_name']:'';
						$NetAmountTaxable_2 = 0;
					}
					
					$s_iqsdt = false;
					if($qbo_tax_code_shipping!=''){
						$Tax_Code_Details_Shipping = $this->mod_qbo_get_tx_dtls($qbo_tax_code_shipping);
						#AL
						if(!empty($Tax_Code_Details_Shipping)){
							if($Tax_Code_Details_Shipping['TaxGroup'] && is_array($Tax_Code_Details_Shipping['TaxRateDetail']) && count($Tax_Code_Details_Shipping['TaxRateDetail'])>1){
								$s_iqsdt = true;
							}
						}
						
						$Tax_Rate_Ref_Shipping = (isset($Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef']:'';
						$TaxPercent_Shipping = $this->get_qbo_tax_rate_value_by_key($Tax_Rate_Ref_Shipping);
						$Tax_Name_Shipping = (isset($Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef_name']:'';
						$NetAmountTaxable_Shipping = 0;
						
						if($s_iqsdt){
							$Tax_Rate_Ref_Shipping_2 = (isset($Tax_Code_Details_Shipping['TaxRateDetail'][1]['TaxRateRef']))?$Tax_Code_Details_Shipping['TaxRateDetail'][1]['TaxRateRef']:'';
							$TaxPercent_Shipping_2 = $this->get_qbo_tax_rate_value_by_key($Tax_Rate_Ref_Shipping_2);
							$Tax_Name_Shipping_2 = (isset($Tax_Code_Details_Shipping['TaxRateDetail'][1]['TaxRateRef']))?$Tax_Code_Details_Shipping['TaxRateDetail'][1]['TaxRateRef_name']:'';
							$NetAmountTaxable_Shipping_2 = 0;
						}
					  }

					$_prices_include_tax = $this->get_array_isset($refund_data,'_prices_include_tax','no',true);
					if($qbo_is_sales_tax){
						$tax_type = $this->get_tax_type($_prices_include_tax);
						$is_inclusive = $this->is_tax_inclusive($tax_type);
						$RefundReceipt->setGlobalTaxCalculation($tax_type);
						//$RefundReceipt->setApplyTaxAfterDiscount(true);
					}
				}
				
				$is_nc_pr_diff_tax = true; # Forced
				if($qbo_is_sales_tax || $is_automated_sales_tax){
					if(empty($qbo_tax_code) && is_array($tax_details) && count($tax_details)>1 && $this->check_wc_is_diff_tax_per_line($tax_details)){
						$is_nc_pr_diff_tax = true;
						
						//
						if(!$apply_tax){
							$t_qtc_1 = $this->get_qbo_mapped_tax_code($tax_rate_id);
							$t_qtc_2 = $this->get_qbo_mapped_tax_code($tax_rate_id_2);

							if($t_qtc_1 == 'NON'){$t_qtc_1 = '';}
							if($t_qtc_2 == 'NON'){$t_qtc_2 = '';}

							if(!empty($t_qtc_1) || !empty($t_qtc_2)){
								$apply_tax = true;
							}
						}
					}
				}
				
				#$this->_p($is_nc_pr_diff_tax,true);
				
				/*Single S Tax Support - For Now*/
				$s_stax_applied = false;
				if(is_array($tax_details) && count($tax_details) == 1){
					if($tax_details[0]['tax_amount'] == 0 && $tax_details[0]['shipping_tax_amount'] > 0){
						//$qbo_tax_code = '';
						$is_nc_pr_diff_tax = true;
						$s_stax_applied = true;
					}
				}
				
				/**/
				$order_refund_details = (isset($refund_data['order_refund_details']))?$refund_data['order_refund_details']:array();
				$r_order_tax = $this->get_array_isset($order_refund_details,'_order_tax',0);
				$r_order_shipping_tax = $this->get_array_isset($order_refund_details,'_order_shipping_tax',0);
				/**/
				$r_order_tax = $this->_abs($r_order_tax);
				$r_order_shipping_tax = $this->_abs($r_order_shipping_tax);
				
				//
				if($is_partial){
					//$apply_tax = false;
					$RefundReceipt->setTotalAmt($_refund_amount);
				}

				$refund_note = $this->get_array_isset($refund_data,'refund_note','',true,4000);
				$RefundReceipt->setPrivateNote($refund_note);
				
				/**/
				$disable_this = false;
				if(!$disable_this && $is_partial && empty($qbo_inv_items) && !empty($r_shp_dtls)){
					$line = new QuickBooks_IPP_Object_Line();
					$line->setDetailType('SalesItemLineDetail');
					
					$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
					$qdp = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
					$salesItemLineDetail->setItemRef($qdp);
					$salesItemLineDetail->setUnitPrice(0);
					//$salesItemLineDetail->setQty(1);
					$line->setAmount(0);
					$line->setDescription('Refund - '.'Shipping');
					
					//
					if($qbo_is_sales_tax){
						if($is_tax_applied){
							$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
							if($TaxCodeRef!=''){
								$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
							}
						}else{
							$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
							$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
						}
					}
					
					if($is_automated_sales_tax){
						if($_order_shipping_tax > 0){
							$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
							if($TaxCodeRef!=''){
								$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
							}
						}else{
							$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
							$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
						}						
					}
					
					$line->addSalesItemLineDetail($salesItemLineDetail);
					$RefundReceipt->addLine($line);					
				}
				
				#New - Refund QB Group items Support
				if(is_array($qbo_inv_items) && count($qbo_inv_items)){
					foreach($qbo_inv_items as $qi_k => $qbo_item){
						if($qbo_item["qbo_product_type"]=='Group'){
							/**/
							$qbo_item['UnitPrice'] = $this->_abs($qbo_item['UnitPrice']);
							$qbo_item['line_subtotal'] = $this->_abs($qbo_item['line_subtotal']);
							$qbo_item['line_total'] = $this->_abs($qbo_item['line_total']);

							$qbo_item['line_subtotal_tax'] = $this->_abs($qbo_item['line_subtotal_tax']);
							$qbo_item['line_tax'] = $this->_abs($qbo_item['line_tax']);

							$line = new QuickBooks_IPP_Object_Line();
							$line->setDetailType('GroupLineDetail');

							$line->setAmount(0);
							$GroupLineDetail = new QuickBooks_IPP_Object_GroupLineDetail();

							$GroupLineDetail->setGroupItemRef($qbo_item['ItemRef']);
							$GroupLineDetail->setQuantity($qbo_item['Qty']);

							/*
							if(!$this->option_checked('mw_wc_qbo_sync_skip_os_lid')){
								$line->setDescription($qbo_item['Description']);
							}
							*/

							$line->setDescription($qbo_item['Description']);
							
							#New
							$qbo_tax_code_fli = $qbo_tax_code;
							if($is_nc_pr_diff_tax){
								$qbo_tax_code_fli = $this->get_per_line_tax_code_id($qbo_tax_code_fli,$qbo_item,$tax_details,$qi_k,$qbo_inv_items);
							}
							
							$qbo_gp_details = $this->get_qbo_group_product_details($qbo_item['ItemRef']);
							if(is_array($qbo_gp_details) && count($qbo_gp_details) && isset($qbo_gp_details['buldle_items'])){
								if(is_array($qbo_gp_details['buldle_items']) && count($qbo_gp_details['buldle_items'])){
									foreach($qbo_gp_details['buldle_items'] as $qbo_gp_item){
										$gp_line = new QuickBooks_IPP_Object_Line();

										$gp_line->setDetailType('SalesItemLineDetail');
										$UnitPrice = $qbo_gp_item["UnitPrice"];
										//$Amount = $qbo_gp_item['Qty']*$UnitPrice;
										$Amount = ($qbo_gp_item['Qty']*$qbo_item['Qty'])*$UnitPrice;
										$gp_line->setAmount($Amount);
										
										if(!$this->option_checked('mw_wc_qbo_sync_skip_os_lid')){												
											//$gp_line->setDescription($qbo_gp_item['ItemRef_name']);
											$bsi_desc = $this->get_qbo_bundle_sub_item_desc_from_woo($qbo_gp_item["ItemRef"],$qbo_gp_item['ItemRef_name']);
											$gp_line->setDescription($bsi_desc);
										}
										
										$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();

										$tax_class =  $qbo_item["tax_class"];

										if($qbo_is_sales_tax){
											if($apply_tax && $qbo_item["Taxed"]){
												$is_tax_applied = true;
												$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code_fli;

												if($is_inclusive){
													//$TaxInclusiveAmt = ($qbo_item['line_total']+$qbo_item['line_tax']);
													//$salesItemLineDetail->setTaxInclusiveAmt($TaxInclusiveAmt);
												}

												if($TaxCodeRef!=''){
													if($is_inclusive){
														$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
														//$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
														$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
													}else{
														$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
													}														
												}
											}else{
												$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
												$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
											}
										}
										
										if($is_automated_sales_tax){
											if($qbo_item["Taxed"]){
												$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code_fli;
												if($TaxCodeRef!=''){
													if($is_inclusive){
														$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
														//$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
														$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
													}else{
														$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
													}														
												}
											}else{
												//$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
												//$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
											}
										}
										
										//AvaTax 0 Rated Tax Code
										if($is_avatax_active && !$is_automated_sales_tax && $this->get_qbo_company_setting('is_sales_tax')){
											$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
											$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
										}
										
										$salesItemLineDetail->setItemRef($qbo_gp_item["ItemRef"]);

										$Qty = $qbo_gp_item["Qty"];
										//$Qty = $qbo_item['Qty'];
										
										//$salesItemLineDetail->setQty($Qty);
										$salesItemLineDetail->setQty($Qty*$qbo_item['Qty']);
										$salesItemLineDetail->setUnitPrice($UnitPrice);
										//$salesItemLineDetail->setUnitPrice($UnitPrice*$qbo_item['Qty']);
										
										//
										if(!empty($qbo_item['ClassRef'])){
											$salesItemLineDetail->setClassRef($qbo_item['ClassRef']);
										}
										
										$gp_line->addSalesItemLineDetail($salesItemLineDetail);
										$GroupLineDetail->addLine($gp_line);
									}
								}

								$wc_b_price = $qbo_item['UnitPrice'];
								//$wc_b_price = $qbo_item['UnitPrice']*$qbo_item['Qty'];
								//$wc_b_price = $qbo_item['line_total'];
								
								/*
								$wc_b_price = $qbo_item['line_subtotal'];
								if($this->option_checked('mw_wc_qbo_sync_no_ad_discount_li')){
									$wc_b_price = $qbo_item['line_total'];
								}
								
								if($this->option_checked('mw_wc_qbo_sync_use_lt_if_ist_l_item')){
									$wc_b_price = $qbo_item['line_total'];
								}
								*/
								
								/**/
								if($this->is_plugin_active('woocommerce-deposits','woocommmerce-deposits') && $this->option_checked('mw_wc_qbo_sync_enable_wc_deposit') && isset($qbo_item["is_deposit"]) && $qbo_item["is_deposit"] == 'yes'){
									//$wc_b_price = $qbo_item["deposit_full_amount_ex_tax"];
									if(isset($qbo_item["deposit_deposit_amount_ex_tax"]) && $qbo_item["deposit_deposit_amount_ex_tax"] > 0){
										$wc_b_price = $qbo_item["deposit_deposit_amount_ex_tax"];
									}
									
									//
									if(isset($qbo_item["deposit_full_amount_ex_tax"]) && $qbo_item["deposit_full_amount_ex_tax"] > 0){
										$wc_b_price = $qbo_item["deposit_full_amount_ex_tax"];
									}
									
									$qbo_item['Qty'] = 1;
								}
								
								$qbo_b_tp = $qbo_gp_details['b_tp'];
								$qbo_b_tp = $qbo_b_tp*$qbo_item['Qty'];
								$gp_p_diff = ($wc_b_price-$qbo_b_tp);
								
								$allow_bndl_line_adstmnt = true;
								if($gp_p_diff!=0 && $allow_bndl_line_adstmnt){
									$b_q_ap = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
									$gp_line = new QuickBooks_IPP_Object_Line();
									$gp_line->setDetailType('SalesItemLineDetail');
									
									$UnitPrice = $gp_p_diff;
									//$UnitPrice = $gp_p_diff*$qbo_item['Qty'];
									$Qty = 1;
									$Amount = $Qty*$UnitPrice;
									$gp_line->setAmount($Amount);

									$gp_line->setDescription('Bundle Product Price Adjustment');
									$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();

									$tax_class =  $qbo_item["tax_class"];

									if($qbo_is_sales_tax){
										if($apply_tax && $qbo_item["Taxed"]){
											$is_tax_applied = true;
											$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code_fli;

											if($is_inclusive){
												//$TaxInclusiveAmt = ($qbo_item['line_total']+$qbo_item['line_tax']);
												//$salesItemLineDetail->setTaxInclusiveAmt($TaxInclusiveAmt);
											}

											if($TaxCodeRef!=''){
												if($is_inclusive){
													$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
													//$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
													$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
												}else{
													$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
												}														
											}
										}else{
											$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
											$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
										}
									}
									
									if($is_automated_sales_tax){
										if($qbo_item["Taxed"]){
											$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code_fli;
											if($TaxCodeRef!=''){
												if($is_inclusive){
													$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
													//$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
													$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
												}else{
													$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
												}														
											}
										}else{
											//$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
											//$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
										}
									}
									
									//AvaTax 0 Rated Tax Code
									if($is_avatax_active && !$is_automated_sales_tax && $this->get_qbo_company_setting('is_sales_tax')){
										$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
										$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
									}
									
									$salesItemLineDetail->setItemRef($b_q_ap);

									$salesItemLineDetail->setQty($Qty);
									$salesItemLineDetail->setUnitPrice($UnitPrice);
									
									//
									if(!empty($qbo_item['ClassRef'])){
										$salesItemLineDetail->setClassRef($qbo_item['ClassRef']);
									}
									
									$gp_line->addSalesItemLineDetail($salesItemLineDetail);
									$GroupLineDetail->addLine($gp_line);
								}
							}

							$line->addGroupLineDetail($GroupLineDetail);
							$RefundReceipt->addLine($line);

						}
					}
				}
				
				//Add Refund items
				$first_line_desc = '';
				if(is_array($qbo_inv_items) && count($qbo_inv_items)){
					$first_line_desc = $qbo_inv_items[0]['Description'];
					foreach($qbo_inv_items as $qi_k => $qbo_item){
						#New
						if($qbo_item["qbo_product_type"]=='Group'){
							continue;
						}

						$line = new QuickBooks_IPP_Object_Line();
						$line->setDetailType('SalesItemLineDetail');

						$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
						
						$UnitPrice = $qbo_item["UnitPrice"];
						$UnitPrice = $this->_abs($UnitPrice);						
						
						if($_cart_discount){
							//$UnitPrice = $this->get_discounted_item_price($_cart_discount,$total_line_subtotal,$UnitPrice);
						}
						
						$tax_class =  $qbo_item["tax_class"];						
						/**/
						$qbo_tax_code_fli = $qbo_tax_code;
						if($s_stax_applied){
							$qbo_tax_code_fli = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
						}
						
						if($is_nc_pr_diff_tax){
							$qbo_tax_code_fli = $this->get_per_line_tax_code_id($qbo_tax_code_fli,$qbo_item,$tax_details,$qi_k,$qbo_inv_items);
						}
						
						/**/
						if($qbo_item["Taxed"] && !$qbo_item['line_tax'] && !$is_nc_pr_diff_tax && empty($qbo_tax_code_fli)){
							$qbo_tax_code_fli = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
						}
						
						/**/
						if($qbo_item["Taxed"] && !$qbo_item['line_tax']){
							$liqtc = $this->get_per_line_tax_code_id('',$qbo_item,$tax_details,$qi_k,$qbo_inv_items);
							if(empty($liqtc)){
								$qbo_tax_code_fli = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
							}
						}
						
						if($qbo_tax_code_fli == $this->get_qbo_zero_rated_tax_code($qbo_company_country)){
							$qbo_item["Taxed"] = 0;							
						}
						
						if($is_partial){
							//$UnitPrice = $_refund_amount;
							//$UnitPrice = $_refund_amount+($r_order_tax)+($r_order_shipping_tax);
							$qbo_item["Qty"] = $this->_abs($qbo_item["Qty"]);
							
							if($apply_tax && $TaxPercent > 0){
								/*
								$UnitPrice = round($UnitPrice / (($TaxPercent/100) + 1),2);
								if($is_qbo_dual_tax && $TaxPercent_2 > 0){
									$UnitPrice = round($UnitPrice / (($TaxPercent_2/100) + 1),2);
								}
								*/
								
								/*
								$comb_tp = ($is_qbo_dual_tax && $TaxPercent_2 > 0)?$TaxPercent+$TaxPercent_2:$TaxPercent;
								$UnitPrice = round($UnitPrice / (($comb_tp/100) + 1),2);
								*/
								
								$is_tax_applied = true;
							}
							
							if($qbo_is_sales_tax){
								if($qbo_item["Taxed"]){ //$is_tax_applied
									$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
									if($TaxCodeRef!=''){
										$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
									}
								}else{
									$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
									$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
								}
							}
							
							if($is_automated_sales_tax){
								if($qbo_item["Taxed"]){
									$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
									if($TaxCodeRef!=''){
										$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
									}
								}else{
									$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
									$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
								}
							}
						}
						
						$Amount = $qbo_item['Qty']*$UnitPrice;
						$line->setDescription('Refund - '.$qbo_item['Description']);
						
						if(!$is_partial && $qbo_is_sales_tax){
							if($apply_tax && $qbo_item["Taxed"]){
								$is_tax_applied = true;
								$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code_fli;

								if($is_inclusive){
									$TaxInclusiveAmt = ($this->_abs($qbo_item['line_total'])+$this->_abs($qbo_item['line_tax']));
									$salesItemLineDetail->setTaxInclusiveAmt($TaxInclusiveAmt);
								}

								if($TaxCodeRef!=''){
									$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
								}
							}else{
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
						}
						
						if(!$is_partial && $is_automated_sales_tax){
							if($qbo_item["Taxed"]){
								$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code_fli;
								if($TaxCodeRef!=''){
									$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
								}
							}else{
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
						}
						
						if($qbo_item["qbo_product_type"]=='Group'){
							$qdp = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
							$salesItemLineDetail->setItemRef($qdp);
						}else{
							$salesItemLineDetail->setItemRef($qbo_item["ItemRef"]);
						}

						if(isset($qbo_item["ClassRef"]) && $qbo_item["ClassRef"]!=''){
							if($qbo_item["qbo_product_type"]!='Group'){
								$salesItemLineDetail->setClassRef($qbo_item["ClassRef"]);
							}
						}

						if($this->option_checked('mw_wc_qbo_sync_invoice_date')){
							$salesItemLineDetail->setServiceDate($wc_inv_date);
						}

						$salesItemLineDetail->setUnitPrice($UnitPrice);
						
						if($is_partial && empty($rd_qii)){
							$line->setAmount($UnitPrice);
							$salesItemLineDetail->setQty(1);
						}else{
							$line->setAmount($Amount);
							$salesItemLineDetail->setQty($qbo_item["Qty"]);
						}
						
						$line->addSalesItemLineDetail($salesItemLineDetail);
						$RefundReceipt->addLine($line);
						//if($is_partial){break;}
					}
				}
				
				//pgdf compatibility
				$is_negative_fee_discount_line = false;
				if($this->get_wc_fee_plugin_check()){
					//$dc_gt_fees = (isset($invoice_data['dc_gt_fees']))?$invoice_data['dc_gt_fees']:array();
					$dc_gt_fees = (isset($rd_dtls['dc_gt_fees']))?$rd_dtls['dc_gt_fees']:array();
					
					if(is_array($dc_gt_fees) && count($dc_gt_fees)){
						foreach($dc_gt_fees as $df){
							$UnitPrice = $df['_line_total'];
							if($UnitPrice>0){
								$is_negative_fee_discount_line = true;
								continue;
							}

							$UnitPrice = $this->_abs($UnitPrice);
							
							$line = new QuickBooks_IPP_Object_Line();
							$line->setDetailType('SalesItemLineDetail');
							
							$Qty = 1;
							$Amount = $Qty*$UnitPrice;

							$line->setAmount($Amount);
							
							//
							$fl_desc = $df['name'];
							if(isset($df['_wc_checkout_add_on_value']) && !empty($df['_wc_checkout_add_on_value'])){
								$fl_desc .= ' - '.$df['_wc_checkout_add_on_value'];
							}
							
							$fl_desc = $this->get_array_isset(array('fl_desc'=>$fl_desc),'fl_desc','');
							
							$line->setDescription($fl_desc);
							
							$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();							
							$_line_tax = $df['_line_tax'];
							$_line_tax = $this->_abs($_line_tax);
							//$df_tax_code = $this->get_qbo_tax_map_code_from_serl_line_tax_data($df['_line_tax_data']);
							if($_line_tax && $qbo_is_sales_tax){
								//$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$df_tax_code;
								$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
								if($TaxCodeRef!=''){
									$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
								}
							}
							if(!$_line_tax && $qbo_is_sales_tax){
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
							
							if($is_automated_sales_tax){
								if($_line_tax){
									$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
									if($TaxCodeRef!=''){
										$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
									}
								}else{
									$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
									$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
								}
							}
							
							/**/
							$df_ItemRef = 0;
							if($this->option_checked('mw_wc_qbo_sync_compt_wdotocac_fee_li_ed') && !empty($this->get_option('mw_wc_qbo_sync_compt_dntp_fn_itxt')) && $this->get_option('mw_wc_qbo_sync_compt_dntp_qbo_item') > 0){
								if($df['name'] == $this->get_option('mw_wc_qbo_sync_compt_dntp_fn_itxt')){
									$df_ItemRef = (int) $this->get_option('mw_wc_qbo_sync_compt_dntp_qbo_item');
								}
							}
							
							if(!$df_ItemRef){
								//$df_ItemRef = $this->get_wc_fee_qbo_product($df['name'],'',$invoice_data);
								$df_ItemRef = $this->get_wc_fee_qbo_product($df['name'],'',$rd_dtls);
							}
							
							$salesItemLineDetail->setItemRef($df_ItemRef);
							$salesItemLineDetail->setQty($Qty);
							$salesItemLineDetail->setUnitPrice($UnitPrice);
							/**/
							$df_ClassRef = $this->get_wc_fee_product_class_ref($df_ItemRef);
							if(!empty($df_ClassRef)){
								$salesItemLineDetail->setClassRef($df_ClassRef);
							}
							
							$line->addSalesItemLineDetail($salesItemLineDetail);
							$RefundReceipt->addLine($line);
						}
					}
				}

				//pw_gift_card compatibility
				if($this->is_plugin_active('pw-woocommerce-gift-cards','pw-gift-cards') && $this->option_checked('mw_wc_qbo_sync_compt_pwwgc_gpc_ed') && !empty($this->get_option('mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item'))){
					$pw_gift_card = (isset($rd_dtls['pw_gift_card']))?$rd_dtls['pw_gift_card']:array();
					if(is_array($pw_gift_card) && count($pw_gift_card)){
						foreach($pw_gift_card as $pgc){
							$pgc_amount = $pgc['amount'];
							if($pgc_amount > 0){
								$pgc_amount = -1 * abs($pgc_amount);
							}
							
							$gift_l_desc = $pgc['card_number'];
							
							$line = new QuickBooks_IPP_Object_Line();
							$line->setDetailType('SalesItemLineDetail');
							$line->setAmount($pgc_amount);
							$line->setDescription($gift_l_desc);
							$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
							$gift_l_item_ref = (int) $this->get_option('mw_wc_qbo_sync_compt_pwwgc_gpc_qbo_item');
							
							$salesItemLineDetail->setItemRef($gift_l_item_ref);
							$salesItemLineDetail->setQty(1);
							//
							$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
							if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
								$salesItemLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
							}
							
							$salesItemLineDetail->setUnitPrice($pgc_amount);
							
							if($this->get_qbo_company_setting('is_sales_tax')){
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
							
							$line->addSalesItemLineDetail($salesItemLineDetail);
							$RefundReceipt->addLine($line);
						}
					}
				}
				
				//gift_card compatibility
				if($this->is_plugin_active('woocommerce-gift-cards') && $this->option_checked('mw_wc_qbo_sync_compt_wgcp_gpc_ed') && !empty($this->get_option('mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item'))){
					$gift_card = (isset($rd_dtls['gift_card']))?$rd_dtls['gift_card']:array();
					if(is_array($gift_card) && count($gift_card)){
						foreach($gift_card as $gc){
							$gc_amount = $gc['amount'];
							if($gc_amount > 0){
								$gc_amount = -1 * abs($gc_amount);
							}
							
							$gift_l_desc = $gc['card_number'];
							
							$line = new QuickBooks_IPP_Object_Line();
							$line->setDetailType('SalesItemLineDetail');
							$line->setAmount($gc_amount);
							$line->setDescription($gift_l_desc);
							$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
							$gift_l_item_ref = (int) $this->get_option('mw_wc_qbo_sync_compt_wgcp_gpc_qbo_item');
							
							$salesItemLineDetail->setItemRef($gift_l_item_ref);
							$salesItemLineDetail->setQty(1);
							//
							$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
							if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
								$salesItemLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
							}
							
							$salesItemLineDetail->setUnitPrice($gc_amount);
							
							if($this->get_qbo_company_setting('is_sales_tax')){
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
							
							$line->addSalesItemLineDetail($salesItemLineDetail);
							$RefundReceipt->addLine($line);
						}
					}
				}
				
				$smart_coupons  = (isset($rd_dtls['used_coupons']))?$rd_dtls['used_coupons']:array();
				//smart_coupons compatibility (gift card / store credit)
				$is_dt_sc = false;
				if((!$_cart_discount || count($smart_coupons) > 1) && isset($rd_dtls['smart_coupons_contribution']) && $this->is_plugin_active('woocommerce-smart-coupons') && $this->option_checked('mw_wc_qbo_sync_compt_wsc_dis_ed') && !empty($this->get_option('mw_wc_qbo_sync_compt_wsc_dis_qbo_item'))){
					#$qbo_is_discount_allowed = false;
					
					if(is_array($smart_coupons) && count($smart_coupons)){
						foreach($smart_coupons as $sc){
							#New						
							if(strpos($sc['coupon_data'], 'smart_coupon') !== false){
								$is_dt_sc = true;
							}else{
								continue;
							}
							
							$sc_amount = $sc['discount_amount'];
							if($sc_amount > 0){
								$sc_amount = -1 * abs($sc_amount);
							}
							
							$sc_l_desc = $sc['name'];
							
							$line = new QuickBooks_IPP_Object_Line();
							$line->setDetailType('SalesItemLineDetail');
							$line->setAmount($sc_amount);
							$line->setDescription($sc_l_desc);
							$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
							$sc_l_item_ref = (int) $this->get_option('mw_wc_qbo_sync_compt_wsc_dis_qbo_item');
							
							$salesItemLineDetail->setItemRef($sc_l_item_ref);
							$salesItemLineDetail->setQty(1);
							//
							$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
							if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
								$salesItemLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
							}
							
							$salesItemLineDetail->setUnitPrice($pgc_amount);
							
							if($this->get_qbo_company_setting('is_sales_tax')){
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
							
							$line->addSalesItemLineDetail($salesItemLineDetail);
							$RefundReceipt->addLine($line);
						}
					}
				}
				
				/*Negative Fee Line Discount*/
				if($is_negative_fee_discount_line){
					//$dc_gt_fees = (isset($invoice_data['dc_gt_fees']))?$invoice_data['dc_gt_fees']:array();
					$dc_gt_fees = (isset($rd_dtls['dc_gt_fees']))?$rd_dtls['dc_gt_fees']:array();
					if(is_array($dc_gt_fees) && count($dc_gt_fees)){
						foreach($dc_gt_fees as $df){
							$UnitPrice = $df['_line_total'];								
							if($UnitPrice<0){	
								continue;
							}

							$UnitPrice = $this->_abs($UnitPrice);
							//$qbo_discount_account = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_discount_account');
							$qbo_discount_account = (int) $this->get_qbo_company_setting('default_discount_account');
							
							$line = new QuickBooks_IPP_Object_Line();
							$line->setDetailType('DiscountLineDetail');
							
							$Qty = 1;
							$Amount = $Qty*$UnitPrice;
							
							$line->setAmount($Amount);
							
							//
							$fl_desc = $df['name'];
							if(isset($df['_wc_checkout_add_on_value']) && !empty($df['_wc_checkout_add_on_value'])){
								$fl_desc .= ' - '.$df['_wc_checkout_add_on_value'];
							}
							
							$fl_desc = $this->get_array_isset(array('fl_desc'=>$fl_desc),'fl_desc','');
							
							$line->setDescription($fl_desc);
							
							$discountLineDetail = new QuickBooks_IPP_Object_DiscountLineDetail();
							$discountLineDetail->setPercentBased(false);
							$discountLineDetail->setDiscountAccountRef($qbo_discount_account);
							
							$_line_tax = $df['_line_tax'];
							$_line_tax = $this->_abs($_line_tax);
							//$df_tax_code = $this->get_qbo_tax_map_code_from_serl_line_tax_data($df['_line_tax_data']);
							if($_line_tax && $qbo_is_sales_tax){
								//$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$df_tax_code;
								$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
								if($TaxCodeRef!=''){
									$discountLineDetail->setTaxCodeRef($TaxCodeRef);
								}
							}
							if(!$_line_tax && $qbo_is_sales_tax){
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$discountLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
							
							if($is_automated_sales_tax){
								if($_line_tax){
									$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
									if($TaxCodeRef!=''){
										$discountLineDetail->setTaxCodeRef($TaxCodeRef);
									}
								}else{
									$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
									$discountLineDetail->setTaxCodeRef($zero_rated_tax_code);
								}
							}
							
							$line->addDiscountLineDetail($discountLineDetail);
							$RefundReceipt->addLine($line);
							
						}
					}
				}
				
				/*Add Refund Shipping*/
				$shipping_details  = (isset($refund_data['shipping_details']))?$refund_data['shipping_details']:array();
				
				$shipping_method = '';
				$shipping_method_name = '';

				$shipping_taxes = '';
				if(isset($shipping_details[0])){
				  if($this->get_array_isset($shipping_details[0],'type','')=='shipping'){
					$shipping_method_id = $this->get_array_isset($shipping_details[0],'method_id','');
					if($shipping_method_id!=''){
					  //$shipping_method = substr($shipping_method_id, 0, strpos($shipping_method_id, ":"));
					  $shipping_method = $this->wc_get_sm_data_from_method_id_str($shipping_method_id);			        }
					$shipping_method = ($shipping_method=='')?'no_method_found':$shipping_method;
					$shipping_method_name =  $this->get_array_isset($shipping_details[0],'name','',true,30);
					//Serialized
					$shipping_taxes = $this->get_array_isset($shipping_details[0],'taxes','');
				  }
				}
				
				//$order_shipping_total+=$_order_shipping_tax;
				
				if($shipping_method!='' && (!$is_partial || ($is_partial && !empty($r_shp_dtls))) && $is_sync_shipping_line){
				  if($qbo_is_shipping_allowed){
					$line = new QuickBooks_IPP_Object_Line();
					$line->setDetailType('SalesItemLineDetail');
					$line->setAmount($order_shipping_total);

					$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
					$salesItemLineDetail->setItemRef('SHIPPING_ITEM_ID');

					if($qbo_is_sales_tax){
					  if($_order_shipping_tax > 0){
						$TaxCodeRef = ($qbo_company_country=='US')?'{-TAX}':$qbo_tax_code;
						if($qbo_tax_code_shipping!=''){
						  $NetAmountTaxable_Shipping = $order_shipping_total;
						  $TaxCodeRef = ($qbo_company_country=='US')?'{-TAX}':$qbo_tax_code_shipping;
						}

						$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
					  }else{
						$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
						$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
					  }
					}
					
					if($is_automated_sales_tax){
						if($_order_shipping_tax > 0){
							$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
							if($TaxCodeRef!=''){
								$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
							}
						}else{
							$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
							$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
						}
					}

					$line->addSalesItemLineDetail($salesItemLineDetail);
					$RefundReceipt->addLine($line);
				  }else{
					$shipping_product_arr = $this->get_mapped_shipping_product($shipping_method);
					$line = new QuickBooks_IPP_Object_Line();
					$line->setDetailType('SalesItemLineDetail');

					//$order_shipping_total = $this->get_array_isset($refund_data,'order_shipping_total',0);
					
					$line->setAmount($order_shipping_total);

					$shipping_description = ($shipping_method_name!='')?'Shipping ('.$shipping_method_name.')':'Shipping';

					$line->setDescription($shipping_description);

					$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();


					$salesItemLineDetail->setItemRef($shipping_product_arr["ItemRef"]);

					if(isset($shipping_product_arr["ClassRef"]) && $shipping_product_arr["ClassRef"]!=''){
					  $salesItemLineDetail->setClassRef($shipping_product_arr["ClassRef"]);
					}

					$salesItemLineDetail->setUnitPrice($order_shipping_total);


					if($qbo_is_sales_tax){
					  if($_order_shipping_tax > 0){
						//$shipping_tax_code = $this->get_qbo_tax_map_code_from_serl_line_tax_data($shipping_taxes,'shipping');
						//$TaxCodeRef = ($qbo_company_country=='US')?'{-TAX}':$shipping_tax_code;
						$TaxCodeRef = ($qbo_company_country=='US')?'{-TAX}':$qbo_tax_code;

						if($qbo_tax_code_shipping!=''){
						  //$NetAmountTaxable_Shipping = $order_shipping_total;
						  //$TaxCodeRef = ($qbo_company_country=='US')?'{-TAX}':$qbo_tax_code_shipping;
						}

						if($TaxCodeRef!=''){
						  $salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
						}
					  }else{
						$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
						$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
					  }
					}
					
					if($is_automated_sales_tax){
						if($_order_shipping_tax > 0){
							$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
							if($TaxCodeRef!=''){
								$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
							}
						}else{
							$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
							$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
						}
					}

					$line->addSalesItemLineDetail($salesItemLineDetail);
					$RefundReceipt->addLine($line);
				  }

				}
				
				/*Add Refund Coupons*/
				$used_coupons  = (isset($refund_data['used_coupons']))?$refund_data['used_coupons']:array();
				$qbo_is_discount_allowed = $this->get_qbo_company_setting('is_discount_allowed');
				if($this->option_checked('mw_wc_qbo_sync_no_ad_discount_li')){
					$qbo_is_discount_allowed = false;
				}
				$discount_line_item_allowed = false;
				
				if(count($used_coupons) && $discount_line_item_allowed){
				  foreach($used_coupons as $coupon){
					$coupon_name = $coupon['name'];
					$coupon_discount_amount = $coupon['discount_amount'];
					$coupon_discount_amount = -1 * $this->_abs($coupon_discount_amount);

					$coupon_discount_amount_tax = $coupon['discount_amount_tax'];

					$coupon_product_arr = $this->get_mapped_coupon_product($coupon_name);
					$line = new QuickBooks_IPP_Object_Line();

					$line->setDetailType('SalesItemLineDetail');
					if($qbo_is_discount_allowed){
					  $line->setAmount(0);
					}else{
					  $line->setAmount($coupon_discount_amount);
					}


					$line->setDescription($coupon_product_arr['Description']);

					$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
					$salesItemLineDetail->setItemRef($coupon_product_arr['ItemRef']);
					if(isset($coupon_product_arr["ClassRef"]) && $coupon_product_arr["ClassRef"]!=''){
					  $salesItemLineDetail->setClassRef($coupon_product_arr["ClassRef"]);
					}
					if($qbo_is_discount_allowed){
					  //$salesItemLineDetail->setUnitPrice(0);
					}else{
					  $salesItemLineDetail->setUnitPrice($coupon_discount_amount);
					}

					if($qbo_is_sales_tax){
					  if($coupon_discount_amount_tax > 0 || $is_tax_applied){
						$TaxCodeRef = ($qbo_company_country=='US')?'{-TAX}':$qbo_tax_code;
						if($TaxCodeRef!=''){
						  $salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
						}
					  }else{
						$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
						$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
					  }
					}
					
					if($is_automated_sales_tax){
						if($coupon_discount_amount_tax > 0 || $is_tax_applied){
							$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
							if($TaxCodeRef!=''){
								$salesItemLineDetail->setTaxCodeRef($TaxCodeRef);
							}
						}else{
							$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
							$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
						}
					}
					
					$line->addSalesItemLineDetail($salesItemLineDetail);
					$RefundReceipt->addLine($line);
				  }
				}
				
				$calc_dc_amnt_fli = true;
				/**/
				if(!$_cart_discount && $this->is_only_plugin_active('woocommerce-smart-coupons')){
					//$_cart_discount = $this->get_wc_ord_smart_coupon_discount_amount($refund_data);
					$calc_dc_amnt_fli = false;
				}
				
				/**/
				if($calc_dc_amnt_fli && !$_cart_discount && !empty($used_coupons)){
					$_cart_discount = $this->get_wc_ord_get_discount_amount_from_coupons($invoice_data);
				}
				
				/*Discount Line*/
				if($_cart_discount && $qbo_is_discount_allowed && !$is_partial){
				  //$qbo_discount_account = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_discount_account');
				  $qbo_discount_account = (int) $this->get_qbo_company_setting('default_discount_account');
				  
				  $line = new QuickBooks_IPP_Object_Line();
				  $line->setDetailType('DiscountLineDetail');
				  $line->setAmount($_cart_discount);
				  $line->setDescription('Total Discount');

				  $discountLineDetail = new QuickBooks_IPP_Object_DiscountLineDetail();
				  $discountLineDetail->setPercentBased(false);
				  $discountLineDetail->setDiscountAccountRef($qbo_discount_account);
				  if($qbo_is_sales_tax){
					if($is_tax_applied){
					  $TaxCodeRef = ($qbo_company_country=='US')?'{-TAX}':$qbo_tax_code;
					  if($TaxCodeRef!=''){
						$discountLineDetail->setTaxCodeRef($TaxCodeRef);
					  }
					}else{
					  $zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
					  $discountLineDetail->setTaxCodeRef($zero_rated_tax_code);
					}
				  }
				  
				  if($is_automated_sales_tax){
					if($_cart_discount){
						$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
						if($TaxCodeRef!=''){
							$discountLineDetail->setTaxCodeRef($TaxCodeRef);
						}
					}else{
						$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
						$discountLineDetail->setTaxCodeRef($zero_rated_tax_code);
					}
				 }

				  $line->addDiscountLineDetail($discountLineDetail);
				  $RefundReceipt->addLine($line);

				}
				
				/*Txn Fee*/
				$r_tfli = false;
				$enable_transaction = (int) $this->get_array_isset($pm_map_data,'enable_transaction',0);
				//$enable_transaction && 
				if($r_tfli && $this->option_checked('mw_wc_qbo_sync_sync_txn_fee_as_ng_li')){
					/*
					if($_payment_method == 'stripe'  || strpos($_payment_method, 'paypal') !== false){
						
						#$wc_currency = get_woocommerce_currency();
						#if($wc_currency == $_order_currency){}						
						
						if($_payment_method == 'stripe'){
							if(isset($refund_data['Stripe Fee'])){
								$txn_fee_amount = (float) $this->get_array_isset($refund_data,'Stripe Fee',0);
							}else{
								$txn_fee_amount = (float) $this->get_array_isset($refund_data,'_stripe_fee',0);
							}
							
							$txn_fee_desc = 'Stripe Fee';
						}else{
							if(isset($refund_data['PayPal Transaction Fee'])){
								$txn_fee_amount = (float) $this->get_array_isset($refund_data,'PayPal Transaction Fee',0);
							}else{
								$txn_fee_amount = (float) $this->get_array_isset($refund_data,'_paypal_transaction_fee',0);
							}
							
							$txn_fee_desc = 'PayPal Transaction Fee';
						}						
					}
					*/
					
					$tfli_data = $this->get_txn_fee_ld_f_o_data($refund_data);
					$txn_fee_desc = $tfli_data['t_f_desc'];
					$txn_fee_amount = $tfli_data['t_f_amnt'];
					
					if($txn_fee_amount > 0){
						$txn_fee_amount = -1 * $this->_abs($txn_fee_amount);
						
						$line = new QuickBooks_IPP_Object_Line();
						$line->setDetailType('SalesItemLineDetail');
						$line->setAmount($txn_fee_amount);
						$line->setDescription($txn_fee_desc);
						$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
						$txn_fee_item_ref = (int) $this->get_option('mw_wc_qbo_sync_txn_fee_li_qbo_item');
						if(!$txn_fee_item_ref){
							$txn_fee_item_ref = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
						}
						
						$salesItemLineDetail->setItemRef($txn_fee_item_ref);
						$salesItemLineDetail->setQty(1);
						//
						$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
						if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
							$salesItemLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
						}
						
						$salesItemLineDetail->setUnitPrice($txn_fee_amount);
						
						if($qbo_is_sales_tax){
							$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
							$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
						}
						
						$line->addSalesItemLineDetail($salesItemLineDetail);
						$RefundReceipt->addLine($line);
					}
				}
				
				//$order_total_tax = floatval($_order_tax) + floatval($_order_shipping_tax);
				//Avatax Line item
				/*
				if($is_avatax_active && count($tax_details) && $_order_tax >0){
				  $line = new QuickBooks_IPP_Object_Line();
				  $line->setDetailType('SalesItemLineDetail');

				  $Qty = 1;
				  $UnitPrice = $order_total_tax;
				  $Amount = $Qty*$UnitPrice;

				  $line->setAmount($Amount);
				  $line->setDescription('AVATAX - QBO Line Item');

				  $salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
				  $avatax_item = (int) $this->get_option('mw_wc_qbo_sync_wc_avatax_map_qbo_product');
				  if($avatax_item<1){
					$avatax_item = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
				  }

				  $salesItemLineDetail->setItemRef($avatax_item);
				  $salesItemLineDetail->setQty($Qty);
				  $salesItemLineDetail->setUnitPrice($UnitPrice);

				  $line->addSalesItemLineDetail($salesItemLineDetail);
				  $RefundReceipt->addLine($line);
				}
				*/
				
				//Order Tax Line Item
				if($is_so_tax_as_li && !empty($tax_details) && $_order_total_tax >0){
					$line = new QuickBooks_IPP_Object_Line();
					$line->setDetailType('SalesItemLineDetail');

					$Qty = 1;						
					
					$otli_desc = '';
					if(is_array($tax_details) && count($tax_details)){
						if(isset($tax_details[0]['label'])){
							$otli_desc = $tax_details[0]['label'];
						}
						
						if(isset($tax_details[1]) && $tax_details[1]['label']){
							if(!empty($tax_details[1]['label'])){
								$otli_desc = $otli_desc.', '.$tax_details[1]['label'];
							}
						}
					}
					
					if(empty($otli_desc)){
						$otli_desc = 'Woocommerce Refund Tax - QBO Line Item';
					}
					
					if($this->wacs_base_cur_enabled()){
						$otli_desc.= " ({$_order_currency} {$_order_total_tax})";
						$UnitPrice = $_order_total_tax_base_currency;
					}else{
						$UnitPrice = $_order_total_tax;
					}
					
					$Amount = $Qty*$UnitPrice;

					$line->setAmount($Amount);
					
					$line->setDescription($otli_desc);

					$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
					$otli_item = (int) $this->get_option('mw_wc_qbo_sync_otli_qbo_product');
					if($otli_item<1){
						$otli_item = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
					}
					
					$salesItemLineDetail->setItemRef($otli_item);
					$salesItemLineDetail->setQty($Qty);
					$salesItemLineDetail->setUnitPrice($UnitPrice);
					
					//
					$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
					if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
						$salesItemLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
					}
					
					$line->addSalesItemLineDetail($salesItemLineDetail);
					$RefundReceipt->addLine($line);
				}
				
				$enable_batch = (int) $this->get_array_isset($pm_map_data,'enable_batch',0);
				if($enable_batch){
					$r_acc_id = (int) $this->get_array_isset($pm_map_data,'udf_account_id',0);
				}else{
					$r_acc_id = (int) $this->get_array_isset($pm_map_data,'qbo_account_id',0);
				}
				$RefundReceipt->setDepositToAccountRef("{-$r_acc_id}");

				$qb_p_method_id = (int) $this->get_array_isset($pm_map_data,'qb_p_method_id',0);
				if($qb_p_method_id){
					$RefundReceipt->setPaymentMethodRef("{-$qb_p_method_id}");
				}

				/*Add Refund Tax*/
				
				//AST
				if($is_automated_sales_tax){					
					$TotalTax = 0;
					if($qbo_is_shipping_allowed){
						//$TotalTax = $_order_tax;
						if(!$is_partial || ($is_partial && !empty($r_tx_dtls))){
							$TotalTax = $_order_tax+$_order_shipping_tax;
						}
					}else{
						if(!$is_partial || ($is_partial && !empty($r_tx_dtls))){
							$TotalTax = $_order_tax+$_order_shipping_tax;
						}							
					}
					
					if($TotalTax > 0){
						$TxnTaxDetail = new QuickBooks_IPP_Object_TxnTaxDetail();
						if(!empty($qbo_tax_code)){
							$TxnTaxDetail->setTxnTaxCodeRef($qbo_tax_code);
						}							
						
						$TxnTaxDetail->setTotalTax($TotalTax);						
						$RefundReceipt->addTxnTaxDetail($TxnTaxDetail);
					}												
				}
				
				if($apply_tax && $is_tax_applied && $Tax_Rate_Ref!=''  && $Tax_Name!=''){
					$TxnTaxDetail = new QuickBooks_IPP_Object_TxnTaxDetail();
					$TxnTaxDetail->setTxnTaxCodeRef($qbo_tax_code);
					$TaxLine = new QuickBooks_IPP_Object_TaxLine();
					$TaxLine->setDetailType('TaxLineDetail');

					if($is_qbo_dual_tax && $TaxPercent_2>0){
						$TaxLine_2 = new QuickBooks_IPP_Object_TaxLine();
						$TaxLine_2->setDetailType('TaxLineDetail');

						$TaxLineDetail_2 = new QuickBooks_IPP_Object_TaxLineDetail();
					}

					$TaxLineDetail = new QuickBooks_IPP_Object_TaxLineDetail();

					$TaxLineDetail->setTaxRateRef($Tax_Rate_Ref);
					$TaxLineDetail->setPerCentBased('true');

					//$NetAmountTaxable = 0;
					//$TaxLineDetail->setNetAmountTaxable($NetAmountTaxable);

					$TaxLineDetail->setTaxPercent($TaxPercent);

					$TaxLine->addTaxLineDetail($TaxLineDetail);

					if($is_qbo_dual_tax && $TaxPercent_2>0){
						$TaxLineDetail_2->setTaxRateRef($Tax_Rate_Ref_2);
						$TaxLineDetail_2->setPerCentBased('true');
						$TaxLineDetail_2->setTaxPercent($TaxPercent_2);

						//$NetAmountTaxable_2 = 0;
						//$TaxLineDetail_2->setNetAmountTaxable($NetAmountTaxable_2);

						$TaxLine_2->addTaxLineDetail($TaxLineDetail_2);
					}

					$TxnTaxDetail->addTaxLine($TaxLine);

					if($is_qbo_dual_tax && $TaxPercent_2>0){
						$TxnTaxDetail->addTaxLine($TaxLine_2);
					}

					$SalesTax = new QuickBooks_IPP_Object_SalesTax();
					$SalesTax->setTaxable('true');
					$SalesTax->setSalesTaxCodeId($Tax_Rate_Ref);

					$SalesTax->setSalesTaxCodeName($Tax_Name);

					$RefundReceipt->addSalesTax($SalesTax);

					if($is_qbo_dual_tax && $TaxPercent_2>0){
						$SalesTax_2 = new QuickBooks_IPP_Object_SalesTax();
						$SalesTax_2->setTaxable('true');
						$SalesTax_2->setSalesTaxCodeId($Tax_Rate_Ref_2);

						$SalesTax_2->setSalesTaxCodeName($Tax_Name_2);

						$RefundReceipt->addSalesTax($SalesTax_2);
					}

				  //Shipping Tax Line
				  if($qbo_tax_code_shipping!='' && $Tax_Rate_Ref_Shipping!='' && !$is_partial){
					$TaxLine_Shipping = new QuickBooks_IPP_Object_TaxLine();
					$TaxLine_Shipping->setDetailType('TaxLineDetail');

					$TaxLineDetail_Shipping = new QuickBooks_IPP_Object_TaxLineDetail();

					$TaxLineDetail_Shipping->setTaxRateRef($Tax_Rate_Ref_Shipping);
					$TaxLineDetail_Shipping->setPerCentBased('true');
					$TaxLineDetail_Shipping->setTaxPercent($TaxPercent_Shipping);

					$TaxLineDetail_Shipping->setNetAmountTaxable($NetAmountTaxable_Shipping);

					$TaxLine_Shipping->addTaxLineDetail($TaxLineDetail_Shipping);

					$TxnTaxDetail->addTaxLine($TaxLine_Shipping);
					
					#AL
					if($s_iqsdt && $TaxPercent_Shipping_2 > 0){
						$TaxLine_Shipping_2 = new QuickBooks_IPP_Object_TaxLine();
						$TaxLine_Shipping_2->setDetailType('TaxLineDetail');
						
						$TaxLineDetail_Shipping_2 = new QuickBooks_IPP_Object_TaxLineDetail();
						$TaxLineDetail_Shipping_2->setTaxRateRef($Tax_Rate_Ref_Shipping_2);
						$TaxLineDetail_Shipping_2->setPerCentBased('true');
						$TaxLineDetail_Shipping_2->setTaxPercent($TaxPercent_Shipping_2);
						
						$TaxLineDetail_Shipping_2->setNetAmountTaxable($NetAmountTaxable_Shipping);
						$TaxLine_Shipping_2->addTaxLineDetail($TaxLineDetail_Shipping_2);
						$TxnTaxDetail->addTaxLine($TaxLine_Shipping_2);
					}
					
					/*
					$SalesTax_Shipping = new QuickBooks_IPP_Object_SalesTax();
					$SalesTax_Shipping->setTaxable('true');
					$SalesTax_Shipping->setSalesTaxCodeId($Tax_Rate_Ref_Shipping);

					$SalesTax_Shipping->setSalesTaxCodeName($Tax_Name_Shipping);

					$RefundReceipt->addSalesTax($SalesTax_2);
					*/
				  }
				  
				  # New
				  if(!$is_automated_sales_tax && $qbo_company_country=='US'){
				  	$RefundReceipt->addTxnTaxDetail($TxnTaxDetail);
				  }

				}
				
				/**/
				if($this->option_checked('mw_wc_qbo_sync_qb_ap_tx_aft_discount')){
					$RefundReceipt->setApplyTaxAfterDiscount(true);
				}else{
					//$RefundReceipt->setApplyTaxAfterDiscount('0');
				}
				
				/*PrintStatus*/
				$qb_o_print_status = $this->get_option('mw_wc_qbo_sync_qb_o_print_status_v');
				if(empty($qb_o_print_status)){$qb_o_print_status = 'NotSet';}
				$RefundReceipt->setPrintStatus($qb_o_print_status);
				
				$inv_sr_txn_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
				
				//WCFE CF SalesRep QBO Class Map
				if($this->is_plugin_active('woocommerce-checkout-field-editor') && $this->option_checked('mw_wc_qbo_sync_compt_wcfe_rf_srqcm_ed')){
					$wcfe_cf_rep_qc_map = get_option('mw_wc_qbo_sync_wcfe_cf_rep_qc_map');
					if(is_array($wcfe_cf_rep_qc_map) && count($wcfe_cf_rep_qc_map)){
						$wsr_ky = $this->get_option('mw_wc_qbo_sync_wcfe_srqcm_wfn');
						if(empty($wsr_ky)){
							$wsr_ky = 'sales-rep';
						}
						
						$wcfe_cf_rep = $this->get_array_isset($refund_data,$wsr_ky,'');
						if(!empty($wcfe_cf_rep)){
							if(isset($wcfe_cf_rep_qc_map[$wcfe_cf_rep]) && !empty($wcfe_cf_rep_qc_map[$wcfe_cf_rep])){
								$inv_sr_txn_class = $wcfe_cf_rep_qc_map[$wcfe_cf_rep];									
							}
						}
					}
				}
				
				//
				$is_dpt_added = false;
				if($this->is_plugin_active('woocommerce-hear-about-us') && $this->get_qbo_company_setting('TrackDepartments') && $this->option_checked('mw_wc_qbo_sync_compt_wchau_enable')){
					$source = $this->get_array_isset($refund_data,'source','',true);
					if($source!=''){
						$mdp_id = (int) $this->get_compt_map_dep_item_id($source);
						if($mdp_id){
							$RefundReceipt->setDepartmentRef($mdp_id);
							$is_dpt_added = true;
						}
					}
				}
				
				/**/				
				if($this->option_checked('mw_wc_qbo_sync_compt_np_wurqbld_ed') && ($this->get_qbo_company_setting('TrackDepartments') || $this->get_qbo_company_setting('ClassTrackingPerTxn'))){
					$wc_user_role = '';
					if($wc_cus_id > 0){
						$user_info = get_userdata($wc_cus_id);
						if(isset($user_info->roles) && is_array($user_info->roles)){
							$wc_user_role = $user_info->roles[0];
						}							
					}else{
						$wc_user_role = 'wc_guest_user';
					}
					
					if(!empty($wc_user_role)){
						if($this->get_qbo_company_setting('TrackDepartments')){
							$wurqbld_wur_qbld_map = get_option('mw_wc_qbo_sync_wurqbld_wur_qbld_map');
							if(is_array($wurqbld_wur_qbld_map) && count($wurqbld_wur_qbld_map) && isset($wurqbld_wur_qbld_map[$wc_user_role])){
								$mdp_id = (int) $wurqbld_wur_qbld_map[$wc_user_role];
								if($mdp_id){
									$RefundReceipt->setDepartmentRef($mdp_id);
									$is_dpt_added = true;
								}
							}
						}
						
						if($this->get_qbo_company_setting('ClassTrackingPerTxn')){
							$wurqbcls_wur_qbcls_map = get_option('mw_wc_qbo_sync_wurqbcls_wur_qbcls_map');
							if(is_array($wurqbcls_wur_qbcls_map) && count($wurqbcls_wur_qbcls_map) && isset($wurqbcls_wur_qbcls_map[$wc_user_role])){
								$mcls_id = trim($wurqbcls_wur_qbcls_map[$wc_user_role]);
								if($mcls_id!= ''){
									$inv_sr_txn_class = $mcls_id;									
								}
							}
						}
					}
				}
				
				/**/
				$mw_wc_qbo_sync_inv_sr_txn_qb_department = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_department');
				if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_department)){
					$RefundReceipt->setDepartmentRef($mw_wc_qbo_sync_inv_sr_txn_qb_department);
					$is_dpt_added = true;
				}
				
				$wes_ebay_oth_ord_qb_department = $this->get_wes_eo_qb_loc($refund_data);
				if(!empty($wes_ebay_oth_ord_qb_department)){
					$RefundReceipt->setDepartmentRef($wes_ebay_oth_ord_qb_department);
					$is_dpt_added = true;
				}
				
				/**/
				if($this->get_qbo_company_setting('TrackDepartments') && $this->is_plugin_active('custom-us-ca-sp-loc-map-for-myworks-qbo-sync') && $this->option_checked('mw_wc_qbo_sync_compt_cucsp_qbl_map_ed')){
					$_shipping_country = $this->get_array_isset($refund_data,'_shipping_country','',true);
					$_shipping_state = $this->get_array_isset($refund_data,'_shipping_state','',true);
					if(!empty($_shipping_country) && !empty($_shipping_state) && ($_shipping_country == 'US' || $_shipping_country == 'CA')){
						$us_ca_sp_qb_loc_map_key = ($_shipping_country =='US')?'mw_wc_qbo_sync_cucsp_ship_us_st_qb_loc_map':'mw_wc_qbo_sync_cucsp_ship_ca_pv_qb_loc_map';
						$us_ca_sp_qb_loc_map_data = get_option($us_ca_sp_qb_loc_map_key);
						if(is_array($us_ca_sp_qb_loc_map_data) && !empty($us_ca_sp_qb_loc_map_data) && isset($us_ca_sp_qb_loc_map_data[$_shipping_state]) && !empty($us_ca_sp_qb_loc_map_data[$_shipping_state])){
							$mdp_id = (int) $us_ca_sp_qb_loc_map_data[$_shipping_state];
							if($mdp_id){								
								$RefundReceipt->setDepartmentRef($mdp_id);
								$is_dpt_added = true;
							}
						}
					}
				}
				
				if($inv_sr_txn_class!='' && $this->get_qbo_company_setting('ClassTrackingPerTxn')){
					$RefundReceipt->setClassRef($inv_sr_txn_class);
				}
				
				//
				$tlt_v = $this->get_frl_c_qb_tlt_val($refund_data);
				if(!empty($tlt_v)){
					$RefundReceipt->setTransactionLocationType($tlt_v);
				}

				# New
				if(!$RefundReceipt->countLine()){
					$line = new QuickBooks_IPP_Object_Line();
					$line->setDetailType('SalesItemLineDetail');
					$line->setAmount($RefundReceipt->getTotalAmt());
					$line->setDescription('Refund');

					$salesItemLineDetail = new QuickBooks_IPP_Object_SalesItemLineDetail();
					$df_i_ref = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_item');
							
					$salesItemLineDetail->setItemRef($df_i_ref);
					$salesItemLineDetail->setQty(1);

					$mw_wc_qbo_sync_inv_sr_txn_qb_class = $this->get_option('mw_wc_qbo_sync_inv_sr_txn_qb_class');
					if(!empty($mw_wc_qbo_sync_inv_sr_txn_qb_class) && $this->get_qbo_company_setting('ClassTrackingPerTxnLine')){
						$salesItemLineDetail->setClassRef($mw_wc_qbo_sync_inv_sr_txn_qb_class);
					}
						
					$salesItemLineDetail->setUnitPrice($RefundReceipt->getTotalAmt());

					if($this->get_qbo_company_setting('is_sales_tax')){
						$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
						$salesItemLineDetail->setTaxCodeRef($zero_rated_tax_code);
					}
					
					$line->addSalesItemLineDetail($salesItemLineDetail);
					$RefundReceipt->addLine($line);
				}
				
				//$this->_p($refund_data);
				//$this->_p($rd_dtls);
				//$this->_p($RefundReceipt);
				//return false;
				
				$log_title = "";
				$log_details = "";
				$log_status = 0;
				
				if ($resp = $RefundReceiptService->add($Context, $realm, $RefundReceipt)){
					$qbo_rfnd_id = $this->qbo_clear_braces($resp);
					$log_title.="Export Refund #$wc_rfnd_id Order #$ord_id_num\n";
					$log_details.="Refund #$wc_rfnd_id has been exported, QuickBooks Refund ID is #$qbo_rfnd_id";
					$log_status = 1;
					$this->save_log($log_title,$log_details,'Refund',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Refund Add',$refund_data,$RefundReceipt,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
					return $qbo_rfnd_id;

				}else{
					$res_err = $RefundReceiptService->lastError($Context);
					$log_title.="Export Refund Error #$wc_rfnd_id Order #$ord_id_num\n";
					$log_details.="Error:$res_err";
					$this->save_log($log_title,$log_details,'Refund',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Refund Add',$refund_data,$RefundReceipt,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
					return false;
				}
			}
		}
	}
	return false;
}