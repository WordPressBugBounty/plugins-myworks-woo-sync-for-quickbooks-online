<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * Update Only Product Price Into Quickbooks Online.
 *
 * @since    
 * Last Updated: 2021-04-07
*/

$include_this_function = true;

if($include_this_function){
	$manual = $this->get_array_isset($product_data,'manual',false);
	if($manual){
		$this->set_session_val('sync_window_push_manual_update',true);
	}
	
	if($this->is_connected()){
		global $wpdb;
		//$this->_p($product_data);
		$wc_product_id = (int) $this->get_array_isset($product_data,'wc_product_id',0,true);
		if($wc_product_id && $this->if_sync_product($wc_product_id)){
			$is_variation = $this->get_array_isset($product_data,'is_variation',false,false);
			$sync_item = ($is_variation)?'Variation (Only Price)':'Product (Only Price)';

			$map_tbl = ($is_variation)?'mw_wc_qbo_sync_variation_pairs':'mw_wc_qbo_sync_product_pairs';
			$w_p_f = ($is_variation)?'wc_variation_id':'wc_product_id';

			$map_data = $this->get_row("SELECT `quickbook_product_id` FROM `".$wpdb->prefix."{$map_tbl}` WHERE `{$w_p_f}` = $wc_product_id AND `quickbook_product_id` > 0 ");
			$quickbook_product_id = 0;
			if(is_array($map_data) && count($map_data)){
				$quickbook_product_id = (int) $map_data['quickbook_product_id'];
			}
			if(!$quickbook_product_id){
				if($manual){
					$this->save_log('Update '.$sync_item.' Error #'.$wc_product_id,$sync_item.' not mapped.','Product',0);
				}
				return false;
			}
			
			$Context = $this->Context;
			$realm = $this->realm;

			$ItemService = new QuickBooks_IPP_Service_Item();
			$sql = "SELECT * FROM Item WHERE Type IN('Inventory','Service','NonInventory') AND Id = '$quickbook_product_id' ";
			$items = $ItemService->query($Context, $realm, $sql);

			if(!$items || empty($items)){
				if($manual){
					$this->save_log('Update '.$sync_item.' Error #'.$wc_product_id,'Invalid QuickBooks product. ','Product',0);
				}
				return false;
			}

			$item = $items[0];
			
			/*
			if($item->getType()=='Group'){
				$this->save_log('Update '.$sync_item.' Error #'.$wc_product_id,'Invalid QuickBooks product (Bundle). ','Product',0);
				return false;
			}
			*/
			
			//$_price = $this->get_array_isset($product_data,'_price',0);
			$_price = $this->get_array_isset($product_data,'_regular_price',0);
			
			//
			if(isset($product_data['wholesale_customer_wholesale_price']) && $this->is_only_plugin_active('woocommerce-wholesale-prices','woocommerce-wholesale-prices.bootstrap') && $this->option_checked('mw_wc_qbo_sync_wwpfps_qb')){
				$_price = $this->get_array_isset($product_data,'wholesale_customer_wholesale_price',0);
			}
			
			$unitPrice = $_price;
			
			//$unitPrice = number_format($unitPrice, 2);
			$unitPrice = str_replace(',','',$unitPrice);
			$unitPrice = floatval($unitPrice);
			
			$item->setUnitPrice($unitPrice);
			
			$log_title = "";
			$log_details = "";
			$log_status = 0;
			
			//$this->_p($product_data);
			//$this->_p($item);
			//die;
			//return false;
			
			if ($resp = $ItemService->update($Context, $realm, $item->getId(), $item)){
				$qbo_item_id = $this->qbo_clear_braces($item->getId());
				$log_title.="Update {$sync_item} #$wc_product_id\n";
				$log_details.="{$sync_item} #$wc_product_id has been updated, QuickBooks Product ID is #$qbo_item_id";
				$log_status = 1;
				$this->save_log($log_title,$log_details,'Product',$log_status,true,'Update');
				$this->add_qbo_item_obj_into_log_file(''.$sync_item.' Update',$product_data,$item,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);

				$this->save_qbo_item_local($qbo_item_id,$name,$sku,$type);
				return $qbo_item_id;

			}else{
				$res_err = $ItemService->lastError($Context);
				$log_title.="Update {$sync_item} Error #$wc_product_id\n";
				$log_details.="Error:$res_err";
				$this->save_log($log_title,$log_details,'Product',$log_status,true,'Update');
				$this->add_qbo_item_obj_into_log_file(''.$sync_item.' Update',$product_data,$item,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
				return false;
			}
		}
		
	}
	return false;
}