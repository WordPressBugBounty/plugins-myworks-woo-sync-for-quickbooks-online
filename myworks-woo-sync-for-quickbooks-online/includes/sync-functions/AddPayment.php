<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * Add Payment Into Quickbooks Online.
 *
 * @since    1.0.0
 * Last Updated: 2019-01-25
*/

$include_this_function = true;

if($include_this_function){
	global $wpdb;
	if($this->is_connected()){
	//$this->_p($customer_data);
	if($this->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
		return false;
	}

	$manual = $this->get_array_isset($payment_data,'manual',false);
	$payment_id = (int) $this->get_array_isset($payment_data,'payment_id',0);
	$wc_inv_id = (int) $this->get_array_isset($payment_data,'order_id',0);
	$wc_cus_id = (int) $this->get_array_isset($payment_data,'customer_user',0);
	
	/**/
	if($this->is_order_sync_as_sales_receipt($wc_inv_id)){
		return false;
	}
	
	if(!$payment_id){
		$this->save_log('Export Payment Error #'.$payment_id,'Woocommerce payment id not found!','Payment',0);
		return false;
	}
	
	/*
	if(!$wc_cus_id){
		$this->save_log('Export Payment Error #'.$payment_id,'Woocommerce customer not found!','Payment',0);
		return false;
	}
	*/
	
	/**/
	$wc_inv_no = $this->get_woo_ord_number_from_order($wc_inv_id,$payment_data);	
	
	#$qbo_invoice_id = (int) $this->get_qbo_invoice_id($wc_inv_id,$wc_inv_no);
	$qbo_invoice_id = 0;
	$qbo_customer_id = 0;
	$qbo_invoice_obj = $this->check_quickbooks_invoice_get_obj($wc_inv_id,$wc_inv_no);

	if(is_object($qbo_invoice_obj) && !empty($qbo_invoice_obj)){
		$qbo_invoice_id = (int) $this->qbo_clear_braces($qbo_invoice_obj->getId());
		$qbo_customer_id = (int) $this->qbo_clear_braces($qbo_invoice_obj->getCustomerRef());
	}
	
	$ord_id_num = (!empty($wc_inv_no))?$wc_inv_no:$wc_inv_id;
	
	if(!$qbo_invoice_id){
		$this->save_log('Export Payment Error #'.$payment_id,'QuickBooks invoice not found!','Payment',0);
		return false;
	}
	
	if(!$qbo_customer_id){
		$this->save_log('Export Payment Error #'.$payment_id,'QuickBooks customer not found!','Payment',0);
		return false;
	}
	
	if($this->if_sync_payment($payment_data)){
		if(!$this->check_payment_get_obj($payment_data,$qbo_invoice_id,$qbo_customer_id)){
				$Context = $this->Context;
				$realm = $this->realm;

				$PaymentService = new QuickBooks_IPP_Service_Payment();
				$Payment = new QuickBooks_IPP_Object_Payment();

				$_payment_method = $this->get_array_isset($payment_data,'payment_method','',true);
				$_payment_method_title = $this->get_array_isset($payment_data,'payment_method_title','',true);

				$_order_currency = $this->get_array_isset($payment_data,'order_currency','',true);
				
				if($this->wacs_base_cur_enabled()){
					$base_currency = get_woocommerce_currency();
					$pm_map_data = $this->get_mapped_payment_method_data($_payment_method,$base_currency);
				}else{
					$pm_map_data = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);
				}
				
				$enable_payment = (int) $this->get_array_isset($pm_map_data,'enable_payment',0);
				$_paid_date = $this->get_array_isset($payment_data,'paid_date','',true);
				if(empty($_paid_date)){
					$_paid_date = $this->get_array_isset($payment_data,'order_date','',true);
				}
				
				if($this->wacs_base_cur_enabled()){
					$payment_amount = $this->get_order_base_currency_total_from_order_id($wc_inv_id);
				}else{
					$payment_amount = $this->get_array_isset($payment_data,'order_total',0);
				}
				
				$payment_amount = floatval($payment_amount);
				//
				$enable_transaction = (int) $this->get_array_isset($pm_map_data,'enable_transaction',0);
				
				if($enable_transaction && $this->option_checked('mw_wc_qbo_sync_sync_txn_fee_as_ng_li')){
					/*
					if($_payment_method == 'stripe' || strpos($_payment_method, 'paypal') !== false){
						if($_payment_method == 'stripe'){							
							$txn_fee_amount = (float) get_post_meta($wc_inv_id,'_stripe_fee',true);
						}else{
							//$txn_fee_amount = (float) get_post_meta($wc_inv_id,'PayPal Transaction Fee',true);
							$txn_fee_amount = (float) get_post_meta($wc_inv_id,'_paypal_transaction_fee',true);
						}
						
						$payment_amount = $payment_amount-$txn_fee_amount;
					}
					*/
					
					$tfli_data = $this->get_order_txn_fee_data_by_id($wc_inv_id);
					if(is_array($tfli_data)){
						$txn_fee_amount = $tfli_data['t_f_amnt'];
						$payment_amount = $payment_amount-$txn_fee_amount;
					}
				}
				
				if($enable_payment && $payment_amount>0){
					if($_paid_date==''){
						$this->save_log('Export Payment Error #'.$payment_id,'Payment date not found!','Payment',0);
						return false;
					}

					$_transaction_id = $this->get_array_isset($payment_data,'transaction_id','',true);

					$_paid_date = $this->view_date($_paid_date);

					$qb_p_method_id = (int) $this->get_array_isset($pm_map_data,'qb_p_method_id',0);
					$qbo_account_id = (int) $this->get_array_isset($pm_map_data,'qbo_account_id',0);

					$enable_batch = (int) $this->get_array_isset($pm_map_data,'enable_batch',0);
					$udf_account_id = (int) $this->get_array_isset($pm_map_data,'udf_account_id',0);

					//$Payment->setPaymentRefNum($_transaction_id);
					//$Payment->setPaymentRefNum($payment_id);
					
					/**/
					$RefNum = $ord_id_num;
					if($this->get_option('mw_wc_qbo_sync_qb_pmnt_ref_num_vf') == 'TXN_ID'){
						$RefNum = $_transaction_id;
					}
					
					//$_transaction_id_trim = $this->get_array_isset(array('_transaction_id'=>$RefNum),'_transaction_id','',true,21);
					if(strlen($RefNum) > 21){
						$RefNum = substr($RefNum,-21);
					}
					
					$_transaction_id_trim = $RefNum;
					
					$Payment->setPaymentRefNum($_transaction_id_trim);
					
					$Payment->setTxnDate($_paid_date);
					
					//Payment Memo Add
					$Pmnt_Memo = 'Order#: '.$ord_id_num.PHP_EOL;
					$Pmnt_Memo.= 'Transaction ID: '.$_transaction_id;
					$Payment->setPrivateNote($Pmnt_Memo);
					
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
					
					/**/
					if(isset($payment_data['mw_qbo_yithwgcp']) && $payment_data['mw_qbo_yithwgcp']){
						$yithwgcp_gcp_qb_acc = (int) $this->get_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc');
						if($yithwgcp_gcp_qb_acc > 0){
							if(isset($payment_data['mw_qbo_yith_full_gift_paid']) || (isset($payment_data['mw_qbo_yith_partial_gift_paid']) && isset($payment_data['mw_qbo_yith_partial_gift_payment_add']))){
								$dtar_acc_id = $yithwgcp_gcp_qb_acc;
							}							
						}
						
						/**/
						$qb_p_method_id = (int) $this->get_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod');
						if($qb_p_method_id > 0){
							$Payment->setPaymentMethodRef($qb_p_method_id);
						}
					}
					
					$Payment->setTotalAmt($payment_amount);

					$Line = new QuickBooks_IPP_Object_Line();
					$Line->setAmount($payment_amount);

					$LinkedTxn = new QuickBooks_IPP_Object_LinkedTxn();
					$LinkedTxn->setTxnId($qbo_invoice_id);
					$LinkedTxn->setTxnType('Invoice');
					$Line->setLinkedTxn($LinkedTxn);
					$Payment->addLine($Line);

					$Payment->setCustomerRef("{-$qbo_customer_id}");
					
					if($enable_batch){
						 $Payment->setDepositToAccountRef("{-$udf_account_id}");
					}else{
						 $Payment->setDepositToAccountRef("{-$dtar_acc_id}");
					}

					//$Payment->setDepositToAccountRef("{-$qbo_account_id}");

					//Add payment log
					$log_title = "";
					$log_details = "";
					$log_status = 0;

					//$this->_p($payment_data);
					//$this->_p($Payment);
					//return false;

					if ($resp = $PaymentService->add($Context, $realm, $Payment)){
						$qbo_pmnt_id = $this->qbo_clear_braces($resp);
						$log_title.="Export Payment #$payment_id\n";
						$log_details.="Payment #$payment_id has been exported, QuickBooks Payment ID is #$qbo_pmnt_id";
						$log_status = 1;
						$this->save_payment_id_map($payment_id,$qbo_pmnt_id);
						$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Payment Add',$payment_data,$Payment,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);

						//30-05-2017
						if(!$enable_batch){							
							if($enable_transaction && !$this->option_checked('mw_wc_qbo_sync_sync_txn_fee_as_ng_li')){
								$je_extra_data = array();
								$je_extra_data['wc_inv_no'] = $wc_inv_no;
								$je_extra_data['qbo_customer_id'] = $qbo_customer_id;
								$je_extra_data['qbo_invoice_id'] = $qbo_invoice_id;
								$je_extra_data['qbo_payment_id'] = $qbo_pmnt_id;

								$je_data = $this->get_je_data_from_pmnt_data($payment_data,$pm_map_data,$je_extra_data);
								//$this->_p($je_data);
								if(is_array($je_data) && count($je_data)){
									$this->AddJournalEntry($je_data);
								}
							}
						}
						
						//17-05-2017
						$individual_batch_support = (int) $this->get_array_isset($pm_map_data,'individual_batch_support',0);
						if($individual_batch_support){
							$this->Cron_Deposit(array($_payment_method),$payment_id,array($_order_currency));
						}
						
						return $qbo_pmnt_id;

					}else{
						$res_err = $PaymentService->lastError($Context);
						$log_title.="Export Payment Error #$payment_id\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Payment Add',$payment_data,$Payment,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
					}
				}else{
					$this->save_log('Export Payment Error #'.$payment_id,'Payment sync not enabled for the gateway or invalid payment amount.','Payment',0);
					return false;
				}

			}

		}
	}
	
	return false;
}