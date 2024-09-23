<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * Add Customer Into Quickbooks Online.
 *
 * @since    1.0.0
 * Last Updated: 2019-01-25
*/

$include_this_function = true;

if($include_this_function){
	if(!$this->is_connected()){
		return false;
	}
	if(is_array($customer_data) && count($customer_data)){
		//$this->_p($customer_data);return false;
		$wc_customerid = $this->get_array_isset($customer_data,'wc_customerid',0);
		if($this->if_sync_customer($wc_customerid)){
			if(!$this->if_qbo_customer_exists($customer_data)){
				$name_replace_chars = array(':','\t','\n');

				$firstname = $this->get_array_isset($customer_data,'firstname','',true,25,false,$name_replace_chars);
				$lastname = $this->get_array_isset($customer_data,'lastname','',true,25,false,$name_replace_chars);
				$company = $this->get_array_isset($customer_data,'company','',true,50,false,$name_replace_chars);
				$display_name = $this->get_array_isset($customer_data,'display_name','',true,100,false,$name_replace_chars);
				
				//28-03-2017
				if(!isset($customer_data['ParentRef']) && $this->option_checked('mw_wc_qbo_sync_append_client')){
					if($this->check_qbo_customer_by_display_name($display_name) && !$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_ship_addr') && !$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_company') && !$this->option_checked('mw_wc_qbo_sync_customer_qbo_check_billing_f_l_name')){
						$display_name.=' -'.$wc_customerid;
					}
				}
				//
				$middlename = '';

				$phone = $this->get_array_isset($customer_data,'billing_phone','',true,21);
				$email = $this->get_array_isset($customer_data,'email','',true);


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
				if($this->is_only_plugin_active('groups')){
					$tax_exempt = get_user_meta($wc_customerid,'myworks_qb_tax_exempt_yn',true);
					if($tax_exempt == 'yes'){
						$tax_exempt_reason = (int) get_user_meta($wc_customerid,'myworks_qb_tax_exempt_reason',true);
						if($tax_exempt_reason > 0){
							$customer->setTaxable(false);
							$customer->setTaxExemptionReasonId($tax_exempt_reason);
							//DefaultTaxCodeRef
						}
					}
				}
				
				/**/
				$customer_type_ref = $this->get_option('mw_wc_qbo_sync_qb_customer_type_fnc');
				/**/
				if($this->option_checked('mw_wc_qbo_sync_compt_np_wurqbld_ed')){
					$wc_user_role = '';
					if($wc_customerid > 0){
						$user_info = get_userdata($wc_customerid);
						if(isset($user_info->roles) && is_array($user_info->roles)){
							$wc_user_role = $user_info->roles[0];
						}							
					}
					
					if(!empty($wc_user_role)){
						$wurqbct_wur_qbct_map = get_option('mw_wc_qbo_sync_wurqbct_wur_qbct_map');
						if(is_array($wurqbct_wur_qbct_map) && count($wurqbct_wur_qbct_map) && isset($wurqbct_wur_qbct_map[$wc_user_role])){
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
				if ($resp = $customerService->add($Context, $realm, $customer)){
					$qbo_customerid = $this->qbo_clear_braces($resp);
					$log_title.="Export Customer #$wc_customerid\n";
					$log_details.="Customer #$wc_customerid has been exported, Quickbooks Customer ID is #$qbo_customerid";
					$log_status = 1;
					$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Customer Add',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
					$this->save_qbo_customer_local($qbo_customerid,$firstname,$lastname,$middlename,$company,$display_name,$email);
					$this->save_customer_map($wc_customerid,$qbo_customerid);

					return $qbo_customerid;

				}else{
					$res_err = $customerService->lastError($Context);
					$log_title.="Export Customer Error #$wc_customerid\n";
					$log_details.="Error:$res_err";
					$this->save_log($log_title,$log_details,'Customer',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file('Customer Add',$customer_data,$customer,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
					return false;
				}
			}
		}
	}
	return false;
}