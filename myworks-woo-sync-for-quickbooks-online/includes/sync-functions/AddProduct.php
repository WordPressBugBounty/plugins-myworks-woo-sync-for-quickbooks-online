<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * Add Product Into Quickbooks Online.
 *
 * @since    1.0.0
 * Last Updated: 2019-01-25
*/

$include_this_function = true;

if($include_this_function){
	if($this->is_connected()){
		$Context = $this->Context;
		$realm = $this->realm;

		$wc_product_id = (int) $this->get_array_isset($product_data,'wc_product_id',0,true);
		if($this->if_sync_product($wc_product_id)){
			if(!$this->check_product_exists($product_data)){
				$ItemService = new QuickBooks_IPP_Service_Item();
				$item = new QuickBooks_IPP_Object_Item();

				//08-05-2017
				$name_replace_chars = array(':');
				$name = $this->get_array_isset($product_data,'name','',true,100,false,$name_replace_chars);
				$name_ori_pt = $this->get_array_isset($product_data,'name_ori_pt','',true,100,false,$name_replace_chars);
				
				//$name = $this->get_array_isset($product_data,'name','',true);
				$sku = $this->get_array_isset($product_data,'_sku','',true,100);
				
				$_manage_stock = $this->get_array_isset($product_data,'_manage_stock','no',true);
				$_downloadable = $this->get_array_isset($product_data,'_downloadable','no',true);
				$_virtual = $this->get_array_isset($product_data,'_virtual','no',true);

				$_stock = $this->get_array_isset($product_data,'_stock',0,true);
				if($_stock==''){$_stock=0;}

				$is_variation = $this->get_array_isset($product_data,'is_variation',false,false);
				//04-01-2018
				
				/*
				if($is_variation){
					$name = $this->get_variation_name_from_id($name,'',$wc_product_id);
				}
				*/
				
				$sync_item = ($is_variation)?'Variation':'Product';

				if($_manage_stock=='yes'){
					if($this->get_qbo_company_info('is_sku_enabled')){
						$type = 'Inventory';
					}else{
						$type = 'NonInventory';
					}

				}elseif($_virtual=='yes'){
					$type = 'Service';
				}else{
					$type = 'NonInventory';
				}

				//Group,Service,NonInventory

				$_sale_price = $this->get_array_isset($product_data,'_sale_price',0);
				$_min_variation_price = $this->get_array_isset($product_data,'_min_variation_price',0);

				$_max_variation_price = $this->get_array_isset($product_data,'_max_variation_price',0);

				//$_price = $this->get_array_isset($product_data,'_price',0);
				$_price = $this->get_array_isset($product_data,'_regular_price',0);
				
				//
				if(isset($product_data['wholesale_customer_wholesale_price']) && $this->is_only_plugin_active('woocommerce-wholesale-prices','woocommerce-wholesale-prices.bootstrap') && $this->option_checked('mw_wc_qbo_sync_wwpfps_qb')){
					$_price = $this->get_array_isset($product_data,'wholesale_customer_wholesale_price',0);
				}
				
				$unitPrice = $_price;

				$_tax_class = $this->get_array_isset($product_data,'_tax_class','');

				$_tax_status = $this->get_array_isset($product_data,'_tax_status','');
				$taxable = ($_tax_status!='' && $_tax_status!='none')?true:false;

				$active = $this->get_array_isset($product_data,'active',true);

				//$qty = (int) $this->get_array_isset($product_data,'total_stock',0);
				$qty = $_stock;

				$item->setName($name);

				$mw_wc_qbo_sync_product_pull_desc_field = $this->get_option('mw_wc_qbo_sync_product_pull_desc_field');
				$desc = '';
				//09-07-2017
				if($this->option_checked('mw_wc_qbo_sync_wc_qbo_product_desc')){
					//$desc = $name;
					$desc = $this->get_array_isset($product_data,'name_ori_pt','',true,4000);
				}else{
					if($mw_wc_qbo_sync_product_pull_desc_field=='short_description'){
						$desc = $this->get_array_isset($product_data,'short_description','',true,4000);
					}else{
						if($is_variation){
							$desc = $this->get_array_isset($product_data,'_variation_description','',true,4000);
						}else{
							$desc = $this->get_array_isset($product_data,'description','',true,4000);
						}						
					}
				}

				#New
				$qb_p_cost = (float) $this->get_array_isset($product_data,'qb_p_cost',0);
				if(!empty($qb_p_cost)){
					$item->setPurchaseCost($qb_p_cost);
				}

				$qb_p_category = $this->get_array_isset($product_data,'qb_p_category','');
				if(!empty($qb_p_category)){
					$item->setParentRef($qb_p_category);
					$item->setSubItem(true);
				}
				
				//22-09-2017
				if($this->is_plugin_active('woocommerce-cost-of-goods') && $this->option_checked('mw_wc_qbo_sync_wcogs_fiels')){
					$_wc_cog_cost = $this->get_array_isset($product_data,'_wc_cog_cost',0);
					$item->setPurchaseCost($_wc_cog_cost);
				}
				
				if($mw_wc_qbo_sync_product_pull_desc_field!='none' || $this->option_checked('mw_wc_qbo_sync_wc_qbo_product_desc')){
					$item->setDescription($desc);
				}					

				$item->setType($type);

				$item->setSku($sku);

				//$unitPrice = number_format($unitPrice, 2);
				$unitPrice = str_replace(',','',$unitPrice);
				$unitPrice = floatval($unitPrice);

				$item->setUnitPrice($unitPrice);

				$item->setTaxable($taxable);
				$item->setActive($active);
				
				$qbo_product_account = $this->get_array_isset($product_data,'qb_income_account','');
				if(empty($qbo_product_account)){
					$qbo_product_account = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_product_account');
				}
				
				if(!$qbo_product_account){
					$this->save_log('Export '.$sync_item.' Error #'.$wc_product_id,'QuickBooks product account not selected.','Product',0);
					return false;
				}

				$item->setIncomeAccountRef($qbo_product_account);

				//
				$qbo_product_expense_account = $this->get_array_isset($product_data,'qb_cogs_account','');
				if(empty($qbo_product_expense_account)){
					$qbo_product_expense_account = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_expense_account');
				}
				
				if($type=='Inventory'){

					$item->setQtyOnHand($qty);
					$item->setTrackQtyOnHand(true);
					
					$qb_isd = $this->get_option('mw_wc_qbo_sync_qbo_inventory_start_date');
					if(empty($qb_isd)){
						$post_date = $this->get_array_isset($product_data,'post_date','');
						$qb_isd = $this->view_date($post_date);
						if(empty($qb_isd)){
							$qb_isd = $this->now('Y-m-d');
						}						
					}
					
					$item->setInvStartDate($qb_isd);
					
					$qbo_product_asset_account = $this->get_array_isset($product_data,'qb_ia_account','');
					if(empty($qbo_product_asset_account)){
						$qbo_product_asset_account = (int) $this->get_option('mw_wc_qbo_sync_default_qbo_asset_account');
					}
					
					if(!$qbo_product_asset_account){
						$this->save_log('Export '.$sync_item.' Error #'.$wc_product_id,'QuickBooks product inventory asset account not selected.','Product',0);
						return false;
					}
					$item->setAssetAccountRef($qbo_product_asset_account);
					
					if(!$qbo_product_expense_account){
						$this->save_log('Export '.$sync_item.' Error #'.$wc_product_id,'QuickBooks product expense account not selected.','Product',0);
						return false;
					}					

				}
				
				//
				$purchase_desc_field = $this->get_option('mw_wc_qbo_sync_produc_push_purchase_desc_field');
				$isea_ref = true;
				if($type != 'Inventory' && $purchase_desc_field == 'none'){
					$isea_ref = false;
				}
				
				if($isea_ref && $qbo_product_expense_account){
					$item->setExpenseAccountRef($qbo_product_expense_account);
				}				
				
				$PreferredVendor = $this->get_array_isset($product_data,'qb_p_vendor','');
				if(!empty($PreferredVendor)){
					$item->setPrefVendorRef($PreferredVendor);
				}
				
				if($this->is_wq_vendor_pm_enable()){
					$_supplier = (int) $this->get_array_isset($product_data,'_supplier','',true);
					if($_supplier>0){
						$sv_id = (int) get_post_meta($_supplier,'_default_settings_assigned_to',true);
						if($sv_id>0){
							$v_company = '';
							$qv_id = (int) $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_vendor_pairs','qbo_vendorid','wc_customerid',$sv_id);
							if($qv_id){
								$v_company = $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors','company','qbo_vendorid',$qv_id);
							}
							
							if(empty($v_company)){
								//$v_company = get_user_meta($sv_id,'billing_company',true);
								$v_company = $this->get_field_by_val($wpdb->prefix.'users','user_login','ID',$sv_id);
							}
							
							//$item->setPurchaseDesc($v_company);
						}
					}
					
					$reorder_point = $this->get_array_isset($product_data,'reorder_point','',true);
					if($reorder_point){
						
					}
					
					$consignors_commission = $this->get_array_isset($product_data,'option_2_commission','',true);
					if($consignors_commission){
						//$item->setPurchaseDesc($consignors_commission);
						//$item->setDescription($consignors_commission);
					}

					/**/
					$cp_name = $name;
					if(!empty($v_company)){$cp_name.= ' - '.$v_company;}
					if(!empty($consignors_commission)){$cp_name.= ' - '.$consignors_commission;}
					$cp_name = $this->get_array_isset(array('cp_name'=>$cp_name),'cp_name','',true,100,false,$name_replace_chars);
					$item->setName($cp_name);
				}
				
				//				
				$purchase_desc = '';
				
				if($purchase_desc_field != 'none'){
					if($purchase_desc_field=='short_description'){
						$purchase_desc = $this->get_array_isset($product_data,'short_description','',true,1000);
					}elseif($purchase_desc_field=='name'){
						//$purchase_desc = $name;
						$purchase_desc = $this->get_array_isset($product_data,'name_ori_pt','',true,1000);
					}else{
						if($is_variation){
							$purchase_desc = $this->get_array_isset($product_data,'_variation_description','',true,1000);
						}else{
							$purchase_desc = $this->get_array_isset($product_data,'description','',true,1000);
						}						
					}
				}				
				
				if(!empty($purchase_desc)){
					$item->setPurchaseDesc($purchase_desc);
				}
				
				$log_title = "";
				$log_details = "";
				$log_status = 0;

				//$this->_p($product_data);
				//$this->_p($item);
				//die;
				//return false;

				if ($resp = $ItemService->add($Context, $realm, $item)){
					$qbo_item_id = $this->qbo_clear_braces($resp);
					$log_title.="Export {$sync_item} #$wc_product_id\n";
					$log_details.="{$sync_item} #$wc_product_id has been exported, QuickBooks Product ID is #$qbo_item_id";
					$log_status = 1;
					$this->save_log($log_title,$log_details,'Product',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file(''.$sync_item.' Add',$product_data,$item,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);

					$this->save_qbo_item_local($qbo_item_id,$name,$sku,$type);
					$this->save_item_map($wc_product_id,$qbo_item_id,false,$is_variation);
					
					#New
					$this->PushProductImg($qbo_item_id,$product_data,$is_variation);

					return $qbo_item_id;

				}else{
					$res_err = $ItemService->lastError($Context);
					$log_title.="Export {$sync_item} Error #$wc_product_id\n";
					$log_details.="Error:$res_err";
					$this->save_log($log_title,$log_details,'Product',$log_status,true,'Add');
					$this->add_qbo_item_obj_into_log_file(''.$sync_item.' Add',$product_data,$item,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
					return false;
				}
			}
		}
	}
	return false;
}