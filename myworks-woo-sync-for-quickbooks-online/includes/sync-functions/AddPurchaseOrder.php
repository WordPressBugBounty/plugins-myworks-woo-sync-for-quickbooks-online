<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * Add PurchaseOrder Into Quickbooks Online.
 *
 * @since    1.0.0
 * Last Updated: 2019-11-04
*/

$include_this_function = true;

if($include_this_function){
	if($this->is_connected()){
		//$this->_p($invoice_data);return false;
		if(!$this->lp_chk_osl_allwd()){
			return false;
		}
		
		$wc_inv_id = $this->get_array_isset($invoice_data,'wc_inv_id',0);
		$wc_inv_num = $this->get_array_isset($invoice_data,'wc_inv_num','');
		$wc_cus_id = $this->get_array_isset($invoice_data,'wc_cus_id','');

		$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
		
		$qbo_inv_items = (isset($invoice_data['qbo_inv_items']))?$invoice_data['qbo_inv_items']:array();
		if(empty($qbo_inv_items)){
			return false;
		}
		
		//Zero Total Option Check
		$_order_total = $this->get_array_isset($invoice_data,'_order_total',0);
		if($this->option_checked('mw_wc_qbo_sync_null_invoice')){
			if($_order_total==0 || $_order_total<0){
				$this->save_log('Export Purchase Order Error #'.$ord_id_num,'Order amount 0 not allowed in setting','Purchase Order',0);
				return false;
			}
		}
		
		if($this->if_sync_po($wc_inv_id,$wc_cus_id,$wc_inv_num)){
			if(!$this->check_quickbooks_po_get_obj($wc_inv_id,$wc_inv_num)){
				$wc_inv_date = $this->get_array_isset($invoice_data,'wc_inv_date','');
				$wc_inv_date = $this->view_date($wc_inv_date);

				$wc_inv_due_date = $this->get_array_isset($invoice_data,'wc_inv_due_date','');
				$wc_inv_due_date = $this->view_date($wc_inv_due_date);

				$qbo_customerid = $this->get_array_isset($invoice_data,'qbo_customerid',0);
				
				/*PM Due Date*/
				$_order_currency = $this->get_array_isset($invoice_data,'_order_currency','',true);
				$_payment_method = $this->get_array_isset($invoice_data,'_payment_method','',true);
				
				if($this->wacs_base_cur_enabled()){
					$base_currency = get_woocommerce_currency();
					$payment_method_map_data  = $this->get_mapped_payment_method_data($_payment_method,$base_currency);
				}else{
					$payment_method_map_data  = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);
				}
				
				$inv_due_date_days = (int) $this->get_array_isset($payment_method_map_data,'inv_due_date_days',0);
				
				if(!empty($wc_inv_date) && $inv_due_date_days > 0){
					$wc_inv_due_date = date('Y-m-d',strtotime($wc_inv_date . "+{$inv_due_date_days} days"));
				}
				
				$qbo_is_sales_tax = $this->get_qbo_company_setting('is_sales_tax');
				$qbo_company_country = $this->get_qbo_company_info('country');
				
				$is_automated_sales_tax = $this->get_qbo_company_setting('is_automated_sales_tax');
				if($is_automated_sales_tax){
					//$qbo_is_sales_tax = false;
				}
				
				$Context = $this->Context;
				$realm = $this->realm;

				$PurchaseOrderService = new QuickBooks_IPP_Service_PurchaseOrder();
				$PurchaseOrder = new QuickBooks_IPP_Object_PurchaseOrder();

				$DocNumber = ($wc_inv_num!='')?$wc_inv_num:$wc_inv_id;
				
				$is_send_doc_num = true;
				if($this->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$this->get_qbo_company_setting('is_custom_txn_num_allowed')){
					//$is_send_doc_num = false;
					$DocNumber = get_post_meta($wc_inv_id,'_mw_qbo_sync_ord_doc_no',true);
					$DocNumber = trim($DocNumber);
					if(empty($DocNumber)){
						$this->save_log('Export Purchase Order Error #'.$ord_id_num,'Order Quickbooks Doc Number not found','PurchaseOrder',0);
						return false;
					}
				}
				
				if($is_send_doc_num){
					$PurchaseOrder->setDocNumber($DocNumber);
				}				
				
				/**/
				$_order_tax = (float) $this->get_array_isset($invoice_data,'_order_tax',0);
				$_order_shipping_tax = (float) $this->get_array_isset($invoice_data,'_order_shipping_tax',0);
				$_order_total_tax = ($_order_tax+$_order_shipping_tax);
				
				//Tax rates
				$qbo_tax_code = '';
				$apply_tax = false;
				$is_tax_applied = false;
				$is_inclusive = false;

				#New
				$ctl_inct = true;
				
				$qbo_tax_code_shipping = '';

				$tax_rate_id = 0;
				$tax_rate_id_2 = 0;

				$tax_details = (isset($invoice_data['tax_details']))?$invoice_data['tax_details']:array();
				$allow_zero_tax = true;
				
				$is_so_tax_as_li = false;
				if($this->option_checked('mw_wc_qbo_sync_odr_tax_as_li') && !$is_automated_sales_tax){
					//$is_so_tax_as_li = true;
					//$qbo_is_sales_tax = false;
				}
				
				if($qbo_is_sales_tax){
					if(count($tax_details)){
						$tax_rate_id = $tax_details[0]['rate_id'];
					}
					if(count($tax_details)>1){						
						if($tax_details[1]['tax_amount']>0 || $allow_zero_tax){
							$tax_rate_id_2 = $tax_details[1]['rate_id'];
						}
					}					
					
					if(count($tax_details)>1 && $qbo_is_shipping_allowed){
						foreach($tax_details as $td){
							if($td['tax_amount']==0 && $td['shipping_tax_amount']>0){
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

					$Tax_Code_Details = $this->mod_qbo_get_tx_dtls($qbo_tax_code,true);
					$is_qbo_dual_tax = false;

					if(count($Tax_Code_Details)){
						if($Tax_Code_Details['TaxGroup'] && count($Tax_Code_Details['TaxRateDetail'])>1){
							$is_qbo_dual_tax = true;
						}
					}


					$Tax_Rate_Ref = (isset($Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef']:'';
					$TaxPercent = $this->get_qbo_tax_rate_value_by_key($Tax_Rate_Ref);
					$Tax_Name = (isset($Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][0]['TaxRateRef_name']:'';

					//
					$NetAmountTaxable = 0;

					if($is_qbo_dual_tax){
						$Tax_Rate_Ref_2 = (isset($Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef']:'';
						$TaxPercent_2 = $this->get_qbo_tax_rate_value_by_key($Tax_Rate_Ref_2);
						$Tax_Name_2 = (isset($Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef']))?$Tax_Code_Details['TaxRateDetail'][1]['TaxRateRef_name']:'';
						$NetAmountTaxable_2 = 0;
					}

					if($qbo_tax_code_shipping!=''){
						$Tax_Code_Details_Shipping = $this->mod_qbo_get_tx_dtls($qbo_tax_code_shipping,true);
						$Tax_Rate_Ref_Shipping = (isset($Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef']:'';
						$TaxPercent_Shipping = $this->get_qbo_tax_rate_value_by_key($Tax_Rate_Ref_Shipping);
						$Tax_Name_Shipping = (isset($Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef']))?$Tax_Code_Details_Shipping['TaxRateDetail'][0]['TaxRateRef_name']:'';
						$NetAmountTaxable_Shipping = 0;
					}

					$_prices_include_tax = $this->get_array_isset($invoice_data,'_prices_include_tax','no',true);
					if($qbo_is_sales_tax){
						$tax_type = $this->get_tax_type($_prices_include_tax);
						$is_inclusive = $this->is_tax_inclusive($tax_type);
						$PurchaseOrder->setGlobalTaxCalculation($tax_type);
						//$PurchaseOrder->setApplyTaxAfterDiscount(true);
					}
					
				}
				
				$is_nc_pr_diff_tax = true; # Forced
				/*
				if($qbo_is_sales_tax || $is_automated_sales_tax){
					if(empty($qbo_tax_code) && count($tax_details)>1 && $this->check_wc_is_diff_tax_per_line($tax_details)){
						$is_nc_pr_diff_tax = true;
					}
				}
				*/				
				
				$total_line_subtotal = 0;				
				$qbo_date = ''; $is_line_item_date = false;
				if(is_array($qbo_inv_items) && count($qbo_inv_items)){
					foreach($qbo_inv_items as $qbo_item){
						$total_line_subtotal+=$qbo_item['line_subtotal'];
						if($this->wacs_base_cur_enabled()){
							$line_subtotal_base_currency+=$qbo_item['line_subtotal_base_currency'];
						}
						if(empty($qbo_date) && isset($qbo_item['Date_QF'])){
							$qbo_date = $qbo_item['Date_QF'];
							$is_line_item_date = true;
						}
					}
				}
				
				if($is_line_item_date){
					$PurchaseOrder->setTxnDate($qbo_date);
					$PurchaseOrder->setDueDate($qbo_date);
				}else{
					$PurchaseOrder->setTxnDate($wc_inv_date);
					$PurchaseOrder->setDueDate($wc_inv_due_date);
				}
				
				//Booking Due Date
				$booking_due_date = '';
				$booking_start_date = '';
				if($this->is_plugin_active('woocommerce-deposits','woocommmerce-deposits') && $this->option_checked('mw_wc_qbo_sync_enable_wc_deposit')){
					$wc_booking_dtls = $this->get_wc_booking_dtls($wc_inv_id);
					if(is_array($wc_booking_dtls) && count($wc_booking_dtls)){							
						$_booking_end = $this->get_array_isset($wc_booking_dtls,'_booking_end','');
						$booking_start_date = $this->get_array_isset($wc_booking_dtls,'_booking_start','');
						if($booking_start_date!=''){
							$booking_start_date = date('Y-m-d',strtotime($booking_start_date));
						}							
						if($_booking_end!=''){
							$booking_due_date = date('Y-m-d',strtotime($_booking_end . "-1 days"));
							$PurchaseOrder->setDueDate($booking_due_date);
						}							
					}						
				}
				
				$APAccountRef = $this->get_option('mw_wc_qbo_sync_po_sync_after_ord_pa_acc');
				$PurchaseOrder->setAPAccountRef($APAccountRef);
				
				$VendorRef = $this->get_option('mw_wc_qbo_sync_po_sync_after_ord_qb_vendor');
				$PurchaseOrder->setVendorRef($VendorRef);
				
				//TotalAmt
				
				if(is_array($qbo_inv_items) && count($qbo_inv_items)){
					foreach($qbo_inv_items as $qi_k => $qbo_item){
						$line = new QuickBooks_IPP_Object_Line();
						$line->setDetailType('ItemBasedExpenseLineDetail');
						$Description = $qbo_item['Description'];
			
						$UnitPrice = $qbo_item["UnitPrice"];
						$line_total = $qbo_item["line_total"];
						$line_subtotal = $qbo_item["line_subtotal"];
						
						$ItemRef = $this->get_array_isset($qbo_item,'ItemRef',0);
						$Qty = $this->get_array_isset($qbo_item,'Qty',1);
						$PurchaseCost = (float) $this->get_qbo_product_PurchaseCost($ItemRef);
						
						//$Amount = ($this->option_checked('mw_wc_qbo_sync_no_ad_discount_li'))?$line_total:$line_subtotal;
						$Amount = $PurchaseCost*$Qty;
						
						$line->setAmount($Amount);		
						$line->setDescription($Description);
						
						$ItemBasedExpenseLineDetail = new QuickBooks_IPP_Object_ItemBasedExpenseLineDetail();
						
						$ItemBasedExpenseLineDetail->setCustomerRef($qbo_customerid);
								
						$ItemBasedExpenseLineDetail->setItemRef($ItemRef);
						$ItemBasedExpenseLineDetail->setQty($Qty);
						$ItemBasedExpenseLineDetail->setUnitPrice($PurchaseCost);
						
						$ItemBasedExpenseLineDetail->setBillableStatus('NotBillable');
						
						$tax_class =  $qbo_item["tax_class"];
						/**/
						if($is_nc_pr_diff_tax){
							$qbo_tax_code = $this->get_per_line_tax_code_id($qbo_tax_code,$qbo_item,$tax_details,$qi_k,$qbo_inv_items);
						}
						
						if($qbo_is_sales_tax){
							if($apply_tax && $qbo_item["Taxed"]){
								$is_tax_applied = true;
								$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;

								if($is_inclusive){
									$TaxInclusiveAmt = $Amount;
									$TaxInclusiveAmt = $this->trim_after_decimal_place($TaxInclusiveAmt,7);
									
									$NetAmountTaxable += $qbo_item['line_total'];
									$ItemBasedExpenseLineDetail->setTaxInclusiveAmt($TaxInclusiveAmt);
								}

								if($ctl_inct){
									$NetAmountTaxable += $Amount;
								}

								if($TaxCodeRef!=''){
									$ItemBasedExpenseLineDetail->setTaxCodeRef($TaxCodeRef);
								}
							}else{
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$ItemBasedExpenseLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
						}
						
						/*
						if($is_automated_sales_tax){
							if($qbo_item["Taxed"]){
								$TaxCodeRef = ($qbo_company_country=='US')?'TAX':$qbo_tax_code;
								if($TaxCodeRef!=''){
									$ItemBasedExpenseLineDetail->setTaxCodeRef($TaxCodeRef);
								}
							}else{
								$zero_rated_tax_code = $this->get_qbo_zero_rated_tax_code($qbo_company_country);
								$ItemBasedExpenseLineDetail->setTaxCodeRef($zero_rated_tax_code);
							}
						}
						*/
						
						$line->addItemBasedExpenseLineDetail($ItemBasedExpenseLineDetail);
						$PurchaseOrder->addLine($line);
					}
				}
				
				/**/
				if($apply_tax && $is_tax_applied && $Tax_Rate_Ref!=''  && $Tax_Name!=''){
					$TxnTaxDetail = new QuickBooks_IPP_Object_TxnTaxDetail();
					$TxnTaxDetail->setTxnTaxCodeRef($qbo_tax_code);
					$TaxLine = new QuickBooks_IPP_Object_TaxLine();
					$TaxLine->setDetailType('TaxLineDetail');
					
					/**/
					if($is_inclusive){
						$TotalTax = $_order_tax+$_order_shipping_tax;
						$TxnTaxDetail->setTotalTax($TotalTax);
						$TaxLine->setAmount($TotalTax);
					}

					if($ctl_inct){
						$TotalTax = ($NetAmountTaxable*$TaxPercent/100);
						$TotalTax = $this->trim_after_decimal_place($TotalTax,7);
						$TxnTaxDetail->setTotalTax($TotalTax);
						$TaxLine->setAmount($TotalTax);
					}
					
					if($is_qbo_dual_tax && $TaxPercent_2>0){
						$TaxLine_2 = new QuickBooks_IPP_Object_TaxLine();
						$TaxLine_2->setDetailType('TaxLineDetail');

						$TaxLineDetail_2 = new QuickBooks_IPP_Object_TaxLineDetail();
					}

					$TaxLineDetail = new QuickBooks_IPP_Object_TaxLineDetail();
					
					$TaxLineDetail->setTaxRateRef($Tax_Rate_Ref);
					$TaxLineDetail->setPerCentBased('true');

					//$NetAmountTaxable = 0;
					if($is_inclusive || $ctl_inct){
						//$NetAmountTaxable = round($NetAmountTaxable+$NetAmountTaxable_Shipping,2);
						$NetAmountTaxable = $this->trim_after_decimal_place($NetAmountTaxable+$NetAmountTaxable_Shipping,7);
						$TaxLineDetail->setNetAmountTaxable($NetAmountTaxable);
					}					
					
					$TaxLineDetail->setTaxPercent($TaxPercent);

					$TaxLine->addTaxLineDetail($TaxLineDetail);

					if($is_qbo_dual_tax && $TaxPercent_2>0){
						$TaxLineDetail_2->setTaxRateRef($Tax_Rate_Ref_2);
						$TaxLineDetail_2->setPerCentBased('true');
						$TaxLineDetail_2->setTaxPercent($TaxPercent_2);
						
						$TaxLine_2->addTaxLineDetail($TaxLineDetail_2);
					}

					$TxnTaxDetail->addTaxLine($TaxLine);

					if($is_qbo_dual_tax && $TaxPercent_2>0){
						$TxnTaxDetail->addTaxLine($TaxLine_2);
					}
					#$PurchaseOrder->addTxnTaxDetail($TxnTaxDetail);

				}
				
				//ShipAddr
				if($this->get_array_isset($invoice_data,'_shipping_first_name','',true)!=''){
					$ShipAddr = new QuickBooks_IPP_Object_ShipAddr();
					$ShipAddr->setLine1($this->get_array_isset($invoice_data,'_shipping_first_name','',true).' '.$this->get_array_isset($invoice_data,'_shipping_last_name','',true));

					$is_cf_bf_applied = false;
					if(isset($cf_map_data['_billing_phone']) && $cf_map_data['_billing_phone']!=''){
						$bp_a = explode(',',$cf_map_data['_billing_phone']);
						if(is_array($bp_a) && in_array('ship_addr',array_map('trim', $bp_a))){
							$_billing_phone = $this->get_array_isset($invoice_data,'_billing_phone','',true);
							if($_billing_phone!=''){
								$ShipAddr->setLine2($_billing_phone);
								$is_cf_bf_applied = true;
							}
						}
					}
					
					if($is_cf_bf_applied){
						$ShipAddr->setLine3($this->get_array_isset($invoice_data,'_shipping_company','',true));
						$ShipAddr->setLine4($this->get_array_isset($invoice_data,'_shipping_address_1','',true));
						$ShipAddr->setLine5($this->get_array_isset($invoice_data,'_shipping_address_2','',true));
					}else{
						$ShipAddr->setLine2($this->get_array_isset($invoice_data,'_shipping_company','',true));
						$ShipAddr->setLine3($this->get_array_isset($invoice_data,'_shipping_address_1','',true));
						$ShipAddr->setLine4($this->get_array_isset($invoice_data,'_shipping_address_2','',true));
					}

					$ShipAddr->setCity($this->get_array_isset($invoice_data,'_shipping_city','',true));

					$country = $this->get_array_isset($invoice_data,'_shipping_country','',true);
					$country = $this->get_country_name_from_code($country);
					/**/
					if($this->is_plugin_active('custom-us-ca-sp-loc-map-for-myworks-qbo-sync') && $this->option_checked('mw_wc_qbo_sync_compt_cucsp_opn_bsa_ed')){
						$_billing_phone = $this->get_array_isset($invoice_data,'_billing_phone','',true);
						if(!empty($_billing_phone)){
							$country = $_billing_phone;
						}
					}
					
					$ShipAddr->setCountry($country);

					$ShipAddr->setCountrySubDivisionCode($this->get_array_isset($invoice_data,'_shipping_state','',true));
					$ShipAddr->setPostalCode($this->get_array_isset($invoice_data,'_shipping_postcode','',true));
					$PurchaseOrder->setShipAddr($ShipAddr);
				}
				
				$PurchaseOrder->setPOStatus('Open');
				
				/*Add PurchaseOrder Currency Start*/				
				$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
				if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){
					$currency_rate_date = $wc_inv_date;
					$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);					
					$PurchaseOrder->setCurrencyRef($_order_currency);
					$PurchaseOrder->setExchangeRate($currency_rate);
				}
				
				//
				$log_title = "";
				$log_details = "";
				$log_status = 0;
				
				//$this->_p($invoice_data);
				//$this->_p($PurchaseOrder);
				//die;
				//return false;
				
				if ($resp = $PurchaseOrderService->add($Context, $realm, $PurchaseOrder)){
					$qbo_po_id = $this->qbo_clear_braces($resp);
					$log_title.="Export Purchase Order #{$ord_id_num}\n";
					$log_details.="Purchase Order has been exported, QuickBooks Purchase Order ID is #$qbo_po_id";
					$log_status = 1;						
					$this->save_log($log_title,$log_details,'Purchase Order',$log_status,true,'Add');
					//31-05-2017
					$this->add_qbo_item_obj_into_log_file('Puchase Order Add',$invoice_data,$PurchaseOrder,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
					return $qbo_po_id;
					
				}else{
					$res_err = $PurchaseOrderService->lastError($Context);
					$log_title.="Export Purchase Order Error #{$ord_id_num}\n";
					$log_details.="Error:$res_err";				
					$this->save_log($log_title,$log_details,'Purchase Order',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Puchase Order Add',$invoice_data,$PurchaseOrder,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
					return false;
				}
			}
		}
	}
	return false;
}