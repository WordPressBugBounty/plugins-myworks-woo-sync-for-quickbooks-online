<?php
if ( ! defined( 'ABSPATH' ) )
     exit;
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
function myworks_wc_qbo_sync_check_license(){	
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_check_license', 'check_plugin_license' ) ) {
		// process form data
		global $MWQS_OF;
		global $MSQS_QL;
		$mw_wc_qbo_sync_localkey = get_option('mw_wc_qbo_sync_localkey','');
		$mw_wc_qbo_sync_localkey = $MSQS_QL->sanitize($mw_wc_qbo_sync_localkey);
		
		$mw_wc_qbo_sync_license =  $MWQS_OF->var_p('mw_wc_qbo_sync_license');
		$mw_wc_qbo_sync_license = $MSQS_QL->sanitize($mw_wc_qbo_sync_license);
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $mw_wc_qbo_sync_license!=$MSQS_QL->get_option('mw_wc_qbo_sync_license')){
			$MSQS_QL->set_session_val('new_license_check',1);
		}		
		
		if($MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey,true)){
			echo 'License Activated';
		}else{
			echo 'Invalid License key';
		}		
	}
	wp_die();
}

/*Not in Use*/
function myworks_wc_qbo_sync_check_license_latest(){	
	if ( ! empty( $_POST ) && check_admin_referer( 'mw_wc_qbo_sync_check_license_latest', 'check_plugin_license_latest' ) ) {
		// process form data
		global $MWQS_OF;
		global $MSQS_QL;
		$mw_wc_qbo_sync_localkey = get_option('mw_wc_qbo_sync_localkey','');
		$mw_wc_qbo_sync_localkey = $MSQS_QL->sanitize($mw_wc_qbo_sync_localkey);
		
		$mw_wc_qbo_sync_license =  $MWQS_OF->var_p('mw_wc_qbo_sync_license');
		$mw_wc_qbo_sync_license = $MSQS_QL->sanitize($mw_wc_qbo_sync_license);
		
		/**/
		$mw_wc_qbo_sync_access_token =  $MWQS_OF->var_p('mw_wc_qbo_sync_access_token');
		$mw_wc_qbo_sync_access_token = $MSQS_QL->sanitize($mw_wc_qbo_sync_access_token);
		
		if(empty($mw_wc_qbo_sync_access_token) || empty($mw_wc_qbo_sync_access_token)){
			echo json_encode(array('is_valid_license'=>0,'status'=>'Invalid','message'=>'Invalid license key or access token'));
			wp_die();
		}
		
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $mw_wc_qbo_sync_license!=$MSQS_QL->get_option('mw_wc_qbo_sync_license')){
			$MSQS_QL->set_session_val('new_license_check',1);
		}		
		
		$is_valid_license = 0;
		if($MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey,true)){
			$is_valid_license = 1;
			update_option('mw_wc_qbo_sync_access_token',$mw_wc_qbo_sync_access_token);
		}
		
		$license_status = $MWQS_OF->get_license_status();
		$license_check_err_msg = '';
		if(!$is_valid_license){
			$license_check_err_msg = 'Please enter a valid license key.';
		}		
		
		if($license_status == 'Expired'){
			$license_check_err_msg = 'Your license key is expired.';
		}
		
		if($license_status == 'Suspended'){
			$license_check_err_msg = 'Your license key is suspended.';
		}

		$lc_ra = array('is_valid_license'=>$is_valid_license,'status'=>$license_status,'message'=>$license_check_err_msg);
		echo json_encode($lc_ra);
		
	}
	wp_die();
}

function mw_wc_qbo_sync_refresh_log_chart(){
	global $MSQS_QL;
	$vp = $MSQS_QL->var_p('period');
	$vp  = $MSQS_QL->sanitize($vp);
	$MSQS_QL->set_session_val('dashboard_graph_period',$vp);
	echo $MSQS_QL->get_log_chart_output($vp);
	wp_die();
}

function mw_wc_qbo_sync_window(){
	global $MSQS_QL;
	global $MSQS_AD;
	//
	global $wpdb;
	
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_window', 'window_qbo_sync' ) ) {
		//
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
			$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
			
		}
		
		$sync_type = $MSQS_QL->var_p('sync_type');
		$item_type = $MSQS_QL->var_p('item_type');
		$id = (int) $MSQS_QL->var_p('id');
		$cur_item = (int) $MSQS_QL->var_p('cur_item');
		$tot_item = (int ) $MSQS_QL->var_p('tot_item');
		
		$check_sync_valid = true;
		if($sync_type!='push' && $sync_type!='pull'){
			$check_sync_valid = false;
		}
		if($item_type!='customer' && $item_type!='invoice' && $item_type!='payment' && $item_type!='product' && $item_type!='inventory' && $item_type!='v_inventory' && $item_type!='category' && $item_type!='variation' && $item_type!='refund'){
			$check_sync_valid = false;
			
			if($item_type == 'vendor' && $MSQS_QL->is_wq_vendor_pm_enable()){
				$check_sync_valid = true;
			}
		}
		if(!$id || !$cur_item || !$tot_item){
			$check_sync_valid = false;
		}
		
		if($check_sync_valid){
			try{
				$key =  $cur_item;		  
				$per = $key/$tot_item*100;
				$per = ceil($per);			
				$msg = '';
				
				//Push
				if($sync_type=='push'){
					if($item_type=='customer'){
						$return_id = $MSQS_AD->myworks_wc_qbo_sync_registration_realtime(array('user_id'=>$id));
						$manual_push_update = $MSQS_QL->get_session_val('sync_window_push_manual_update',false,true);
						
						//$manual_push_session_err_msg = $MSQS_QL->get_session_msg('manual_push_session_err_msg','error');
						
						if($return_id){
							if($manual_push_update){
								$msg = "<span class='success_green'>Customer #$id has been updated, QuickBooks customer id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Customer #$id has been pushed, QuickBooks customer id #$return_id</span>";
							}							
						}else{
							if($manual_push_update){
								$msg = "<span class='error_red'>There was an error updating customer #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error pushing customer #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='vendor'){
						$return_id = $MSQS_AD->myworks_wc_qbo_sync_vendor_push(array('user_id'=>$id));
						$manual_push_update = $MSQS_QL->get_session_val('sync_window_push_manual_update',false,true);
						
						//$manual_push_session_err_msg = $MSQS_QL->get_session_msg('manual_push_session_err_msg','error');
						
						if($return_id){
							if($manual_push_update){
								$msg = "<span class='success_green'>Vendor #$id has been updated, QuickBooks vendor id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Vendor #$id has been pushed, QuickBooks vendor id #$return_id</span>";
							}							
						}else{
							if($manual_push_update){
								$msg = "<span class='error_red'>There was an error updating vendor #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error pushing vendor #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='invoice'){											
						$return_id = $MSQS_AD->myworks_wc_qbo_sync_order_realtime(array('order_id'=>$id));
						$manual_push_update = $MSQS_QL->get_session_val('sync_window_push_manual_update',false,true);
						
						/**/
						$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($id);
						
						$ord_id_num = ($wc_inv_no!='')?$wc_inv_no:$id;
						
						//Split Order
						if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
							$spli_order_manual = $MSQS_QL->get_session_val('spli_order_manual',false,true);
							if($spli_order_manual){
								$msg = "<span style='color:gray;'>Split order push action triggered for order #$ord_id_num</span>";
								$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
							}
						}
						
						//23-05-2017
						$rfd_q = $wpdb->prepare("SELECT ID FROM `{$wpdb->posts}` WHERE `post_type` = 'shop_order_refund' AND `post_parent` = %d ORDER BY ID ASC ",$id);
						$rf_data = $MSQS_QL->get_data($rfd_q);
						//$MSQS_QL->_p($rf_data);
						if(is_array($rf_data) && !empty($rf_data)){
							foreach($rf_data as $rfd){
								$refund_id = (int) $rfd['ID'];
								if($refund_id){
									$MSQS_AD->mw_wc_qbo_sync_woocommerce_order_refunded(array('order_id'=>$id),$refund_id);
								}								
							}							
						}
						
						//$manual_push_session_err_msg = $MSQS_QL->get_session_msg('manual_push_session_err_msg','error');
						
						$ord_sync_in_qb_as = 'Invoice';
						
						if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($id)){
							$ord_sync_in_qb_as = 'Sales Receipt';
						}else{
							if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($id)){
								$ord_sync_in_qb_as = 'Estimate';
							}
						}
						
						$osa_ltxt = 'a '.$ord_sync_in_qb_as;
						if($ord_sync_in_qb_as == 'Invoice'){
							$osa_ltxt = 'an '.$ord_sync_in_qb_as;
						}
						
						if($return_id){					
							/*YITH WooCommerce Gift Cards Premium*/
							$return_id_gift_payment = 0;
							$yith_gp_fc = false;
							//$manual_push_update
							if($ord_sync_in_qb_as != 'Sales Receipt' && $MSQS_QL->option_checked('mw_wc_qbo_sync_compt_yithwgcp_gpc_ed')){
								$yithwgcp_gcp_qb_acc = (int) $MSQS_QL->get_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc');
								if($yithwgcp_gcp_qb_acc > 0){
									$p_order_data = $MSQS_QL->get_wc_order_details_from_order($id,get_post($id));									
									if(isset($p_order_data['_ywgc_applied_gift_cards_totals']) && $p_order_data['_ywgc_applied_gift_cards_totals'] > 0){
										if(floatval($p_order_data['_order_total']) == 0){											
											$MSQS_QL->set_session_val('yithwgcp_gpc_fp_manual_payment',true);
											if(!$MSQS_QL->check_os_payment_get_obj(array('wc_inv_id'=>$id))){
												$yith_gp_fc = true;
												$return_id_gift_payment = $MSQS_AD->mw_qbo_wc_order_payment($id);
											}											
										}
									}
								}
							}							
							
							if($manual_push_update){
								$msg = "<span class='success_green'>Order #$ord_id_num has been updated into QuickBooks as {$osa_ltxt}.</span>";
							}else{
								$msg = "<span class='success_green'>Order #$ord_id_num has been synced into QuickBooks as {$osa_ltxt}.</span>";
							}
						}else{							
							if($manual_push_update){
								$msg = "<span class='error_red'>There was an error updating Order #$ord_id_num, Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error syncing Order #$ord_id_num, Check MyWorks Sync > Log for additional details.</span>";
							}
						}
						
						/**/
						if($yith_gp_fc){
							if($return_id_gift_payment){
								$msg .= "<span class='success_green'>Gift payment for Order #$ord_id_num has been pushed, QuickBooks payment id #$return_id_gift_payment</span>";
							}else{
								$msg .= "<span class='error_red'>There was an error pushing gift payment for order #$ord_id_num , Check MyWorks Sync > Log for additional details.</span>";
							}
						}
						
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='product'){									
						$return_id = $MSQS_AD->mw_qbo_wc_product_save(array('product_id'=>$id));
						$manual_push_update = $MSQS_QL->get_session_val('sync_window_push_manual_update',false,true);
						if($return_id){
							if($manual_push_update){
								$msg = "<span class='success_green'>Product #$id has been updated, QuickBooks product id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Product #$id has been pushed, QuickBooks product id #$return_id</span>";
							}							
						}else{
							if($manual_push_update){
								$msg = "<span class='error_red'>There was an error updating product #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error pushing product #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='variation'){							
						$return_id = $MSQS_AD->mw_qbo_wc_variation_save(array('variation_id'=>$id));
						$manual_push_update = $MSQS_QL->get_session_val('sync_window_push_manual_update',false,true);
						if($return_id){
							if($manual_push_update){
								$msg = "<span class='success_green'>Variation #$id has been updated, QuickBooks product id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Variation #$id has been pushed, QuickBooks product id #$return_id</span>";
							}							
						}else{
							if($manual_push_update){
								$msg = "<span class='error_red'>There was an error updating variation #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error pushing variation #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='payment' && !$MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
						$return_id = $MSQS_AD->mw_qbo_wc_order_payment(array('payment_id'=>$id));						
						if($return_id){
							$msg = "<span class='success_green'>Payment #$id has been pushed, QuickBooks payment id #$return_id</span>";
						}else{
							$msg = "<span class='error_red'>There was an error pushing payment #$id , Check MyWorks Sync > Log for additional details.</span>";
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);				
					}
					
					//25-04-2017
					if($item_type=='category'){						
						$return_id = $MSQS_AD->myworks_wc_qbo_sync_product_category_realtime(array('category_id'=>$id));
						$manual_push_update = $MSQS_QL->get_session_val('sync_window_push_manual_update',false,true);
						if($return_id){
							if($manual_push_update){
								$msg = "<span class='success_green'>Category #$id has been updated, QuickBooks category id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Category #$id has been pushed, QuickBooks category id #$return_id</span>";
							}							
						}else{
							if($manual_push_update){
								$msg = "<span class='error_red'>There was an error updating category #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error pushing category #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='inventory'){
						$return_id = $MSQS_QL->UpdateQboInventory(array('wc_inventory_id'=>$id,'manual'=>true));						
						
						if($return_id){
							$msg = "<span class='success_green'>Inventory #$id has been updated, QuickBooks inventory id #$return_id</span>";
						}else{
							$msg = "<span class='error_red'>There was an error updating inventory #$id , Check MyWorks Sync > Log for additional details.</span>";
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='v_inventory'){
						$return_id = $MSQS_QL->VariationUpdateQboInventory(array('wc_inventory_id'=>$id,'manual'=>true,'is_variation'=>true));
						
						if($return_id){
							$msg = "<span class='success_green'>Variation Inventory #$id has been updated, QuickBooks inventory id #$return_id</span>";
						}else{
							$msg = "<span class='error_red'>There was an error updating variation inventory #$id , Check MyWorks Sync > Log for additional details.</span>";
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					/**/
					if($item_type=='refund'){
						$order_id = 0; 
						$rf_data = $wpdb->get_row("SELECT `post_parent` FROM `{$wpdb->posts}` WHERE `post_type` = 'shop_order_refund' AND `ID` = {$id} ");
						if(is_object($rf_data) && !empty($rf_data)){
							$order_id = $rf_data->post_parent;
						}
						
						$return_id = $MSQS_AD->mw_wc_qbo_sync_woocommerce_order_refunded(array('order_id'=>$order_id),$id);
						$manual_push_update = $MSQS_QL->get_session_val('sync_window_push_manual_update',false,true);
						if($return_id){
							if($manual_push_update){
								$msg = "<span class='success_green'>Refund #$id has been updated, QuickBooks refund id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Refund #$id has been pushed, QuickBooks refund id #$return_id</span>";
							}							
						}else{
							if($manual_push_update){
								$msg = "<span class='error_red'>There was an error updating refund #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error pushing refund #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
				}
				
				//Pull
				if($sync_type=='pull' && $MSQS_QL->option_checked('mw_wc_qbo_sync_pull_enable')){
					if($item_type=='inventory' && !$MSQS_QL->is_plg_lc_p_l(false)){
						$return_id = $MSQS_QL->UpdateWooCommerceInventory(array('qbo_inventory_id'=>$id,'manual'=>true));						
						
						if($return_id){
							$msg = "<span class='success_green'>Inventory #$id has been updated, WooCommerce product id #$return_id</span>";
						}else{
							$msg = "<span class='error_red'>There was an error updating inventory #$id , Check MyWorks Sync > Log for additional details.</span>";
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='product'){
						$return_id = $MSQS_QL->Qbo_Pull_Product(array('qbo_product_id'=>$id,'manual'=>true));
						$manual_pull_update = $MSQS_QL->get_session_val('sync_window_pull_manual_update',false,true);
						
						if($return_id){
							if($manual_pull_update){
								$msg = "<span class='success_green'>Product #$id has been updated, WooCommerce product id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Product #$id has been imported, WooCommerce product id #$return_id</span>";
							}							
						}else{
							if($manual_pull_update){
								$msg = "<span class='error_red'>There was an error importing product #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error updating product #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
					
					if($item_type=='category' && !$MSQS_QL->is_plg_lc_p_l()){
						$return_id = $MSQS_QL->Qbo_Pull_Category(array('qbo_category_id'=>$id,'manual'=>true));
						$manual_pull_update = $MSQS_QL->get_session_val('sync_window_pull_manual_update',false,true);
						
						if($return_id){
							if($manual_pull_update){
								$msg = "<span class='success_green'>Category #$id has been updated, WooCommerce category id #$return_id</span>";
							}else{
								$msg = "<span class='success_green'>Category #$id has been imported, WooCommerce category id #$return_id</span>";
							}							
						}else{
							if($manual_pull_update){
								$msg = "<span class='error_red'>There was an error updating category #$id , Check MyWorks Sync > Log for additional details.</span>";
							}else{
								$msg = "<span class='error_red'>There was an error importing category #$id , Check MyWorks Sync > Log for additional details.</span>";
							}							
						}
						$MSQS_QL->show_sync_window_message($key, $msg , $per, $tot_item);
					}
				}
				
			}catch (Exception $e) {
				$Exception = $e->getMessage();
			}
		}
	}
	wp_die();
}

function mw_wc_qbo_sync_clear_all_mappings(){	
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_mappings', 'clear_all_mappings' ) ) {		
		global $wpdb;
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` ");
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` ");
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_paymentmethod_map` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_paymentmethod_map` ");
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_promo_code_product_map` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_promo_code_product_map` ");
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_shipping_product_map` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_shipping_product_map` ");
		
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_tax_map` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_tax_map` ");
		
		//23-05-2017
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_wq_cf_map` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_wq_cf_map` ");
		
		//23-06-2017
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` ");
		
		if(isset($_POST['payment_map_delete'])){
			$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_payment_id_map` WHERE `id` > 0 ");
			$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_payment_id_map` ");
		}
		echo 'Success';
	}
	wp_die();
}

function mw_wc_qbo_sync_clear_all_mappings_products(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_mappings_products', 'clear_all_mappings_products' ) ) {	
		global $wpdb;
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_product_pairs` ");
		echo 'Success';
	}
	wp_die();
}

function mw_wc_qbo_sync_clear_all_mappings_variations(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_mappings_variations', 'clear_all_mappings_variations' ) ) {	
		global $wpdb;
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_variation_pairs` ");
		echo 'Success';
	}
	wp_die();
}

function mw_wc_qbo_sync_clear_all_mappings_customers(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_mappings_customers', 'clear_all_mappings_customers' ) ) {	
		global $wpdb;
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_customer_pairs` ");
		echo 'Success';
	}
	wp_die();
}

function mw_wc_qbo_sync_clear_all_mappings_vendors(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_mappings_vendors', 'clear_all_mappings_vendors' ) ) {	
		global $wpdb;
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_vendor_pairs` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_vendor_pairs` ");
		echo 'Success';
	}
	wp_die();
}

function mw_wc_qbo_sync_automap_vendors(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_vendors', 'automap_vendors' ) ) {
		global $MSQS_QL;
		$map_count = (int) $MSQS_QL->AutoMapVendor();
		//echo 'Success';
		echo 'Total Vendor Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_automap_vendors_by_name(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_vendors_by_name', 'automap_vendors_by_name' ) ) {
		global $MSQS_QL;
		$map_count = (int) $MSQS_QL->AutoMapVendorByName();
		//echo 'Success';
		echo 'Total Vendor Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_automap_customers(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_customers', 'automap_customers' ) ) {
		global $MSQS_QL;
		$map_count = (int) $MSQS_QL->AutoMapCustomer();
		//echo 'Success';
		echo 'Total Customer Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_automap_customers_wf_qf(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_customers_wf_qf', 'automap_customers_wf_qf' ) ) {
		global $MSQS_QL;
		
		$cam_wf = (isset($_POST['cam_wf']))?trim($_POST['cam_wf']):'';
		$cam_qf = (isset($_POST['cam_qf']))?trim($_POST['cam_qf']):'';
		
		$mo_um = false;
		if(isset($_POST['mo_um']) && $_POST['mo_um'] == 'true'){
			$mo_um = true;
		}
		
		$map_count = (int) $MSQS_QL->AutoMapCustomerWfQf($cam_wf,$cam_qf,$mo_um);
		//echo 'Success';
		echo 'Total Customer Mapped: '.$map_count;
	}	
	wp_die();
}

//28-07-2017
function mw_wc_qbo_sync_automap_customers_by_name(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_customers_by_name', 'automap_customers_by_name' ) ) {
		global $MSQS_QL;
		$map_count = (int) $MSQS_QL->AutoMapCustomerByName();
		//echo 'Success';
		echo 'Total Customer Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_automap_products(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_products', 'automap_products' ) ) {
		global $MSQS_QL;
		$map_count = (int) $MSQS_QL->AutoMapProduct();
		//echo 'Success';
		echo 'Total Product Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_automap_products_wf_qf(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_products_wf_qf', 'automap_products_wf_qf' ) ) {
		global $MSQS_QL;
		
		$pam_wf = (isset($_POST['pam_wf']))?trim($_POST['pam_wf']):'';
		$pam_qf = (isset($_POST['pam_qf']))?trim($_POST['pam_qf']):'';
		
		$mo_um = false;
		if(isset($_POST['mo_um']) && $_POST['mo_um'] == 'true'){
			$mo_um = true;
		}
		
		$map_count = (int) $MSQS_QL->AutoMapProductWfQf($pam_wf,$pam_qf,$mo_um);
		//echo 'Success';
		echo 'Total Product Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_automap_products_by_name(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_products_by_name', 'automap_products_by_name' ) ) {		
		global $MSQS_QL;
		$map_count = (int) $MSQS_QL->AutoMapProductByName();
		//echo 'Success';
		echo 'Total Product Mapped: '.$map_count;
	}	
	wp_die();
}

//26-04-2017
function mw_wc_qbo_sync_automap_variations(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_variations', 'automap_variations' ) ) {
		global $MSQS_QL;
		$map_count = (int) $MSQS_QL->AutoMapVariation();
		//echo 'Success';
		echo 'Total Variation Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_automap_variations_wf_qf(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_automap_variations_wf_qf', 'automap_variations_wf_qf' ) ) {
		global $MSQS_QL;
		
		$vam_wf = (isset($_POST['vam_wf']))?trim($_POST['vam_wf']):'';
		$vam_qf = (isset($_POST['vam_qf']))?trim($_POST['vam_qf']):'';
		
		$mo_um = false;
		if(isset($_POST['mo_um']) && $_POST['mo_um'] == 'true'){
			$mo_um = true;
		}
		
		$map_count = (int) $MSQS_QL->AutoMapVariationWfQf($vam_wf,$vam_qf,$mo_um);
		//echo 'Success';
		echo 'Total Variation Mapped: '.$map_count;
	}	
	wp_die();
}

function mw_wc_qbo_sync_clear_all_logs(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_logs', 'mwqs_clear_all_logs' ) ) {
		global $wpdb;	
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE `id` > 0 ");
		$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_log` ");
		echo 'Success';
	}	
	wp_die();
}

function mw_wc_qbo_sync_clear_all_log_errors(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_log_errors', 'mwqs_clear_all_log_errors' ) ) {
		global $wpdb;
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE `success` = 0 ");
		echo 'Success';
	}	
	wp_die();
}

function mw_wc_qbo_sync_clear_all_queues(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_clear_all_queues', 'mwqs_clear_all_queues' ) ) {
		global $wpdb;	
		$wpdb->query("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_real_time_sync_queue` WHERE `id` > 0 AND `run` = 0 ");
		//$wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."mw_wc_qbo_sync_real_time_sync_queue` ");
		echo 'Success';
	}	
	wp_die();
}

function mw_wc_qbo_sync_trial_license_check_again(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_trial_license_check_again', 'trial_license_check_again' ) ) {
		global $MSQS_QL;
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_trial_license')){
			delete_option('mw_wc_qbo_sync_localkey');
			echo 'Success';
		}		
	}	
	wp_die();
}


function mw_wc_qbo_sync_del_license_local_key(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_del_license_local_key', 'del_license_local_key' ) ) {
		delete_option('mw_wc_qbo_sync_localkey');
		echo 'Success';
	}	
	wp_die();
}

function mw_wc_qbo_sync_del_conn_cred_local_key(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_del_conn_cred_local_key', 'del_conn_cred_local_key' ) ) {
		delete_option('mw_wc_qbo_sync_conn_cred_local_key');
		echo 'Success';
	}	
	wp_die();
}

function mw_wc_qbo_sync_qcpp_on_off(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_qcpp_on_off', 'qcpp_on_off' ) ) {
		global $MSQS_QL;
		$r_arr = array();
		$qcpp_val = (int) $MSQS_QL->var_p('qcpp_val');
		$mw_wc_qbo_sync_pause_up_qbo_conection = ($qcpp_val==1)?'':'true';
		update_option('mw_wc_qbo_sync_pause_up_qbo_conection',$mw_wc_qbo_sync_pause_up_qbo_conection);
		
		if($mw_wc_qbo_sync_pause_up_qbo_conection=='true'){
			$r_arr['status'] = 'paused';
			$r_arr['msg'] = 'Syncing Paused - Queue Sync Enabled';
		}else{
			$r_arr['status'] = 'active';
			$r_arr['msg'] = 'Syncing Active';
		}
		echo json_encode($r_arr);
	}	
	wp_die();
}

function mw_wc_qbo_sync_get_nqc_time_diff(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_get_nqc_time_diff', 'nqc_time_diff' ) ) {
		global $MSQS_QL;
		$cdt = $MSQS_QL->now('Y-m-d H:i:s');
		$next_queue_cron_run = wp_next_scheduled( 'mw_qbo_sync_queue_cron_hook' );

		$s_ncrt_cdt_diff = $next_queue_cron_run-strtotime($cdt);
		echo $s_ncrt_cdt_diff;
	}	
	wp_die();
}

//
function mw_wc_qbo_sync_rg_all_inc_variation_names(){	
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_rg_all_inc_variation_names', 'rg_all_inc_variation_names' ) ) {
		global $MSQS_QL;		
		//$tot_vn_updated =  $MSQS_QL->Fix_All_WooCommerce_Variations_Names();
		$tot_vn_updated = 0;
		echo 'Total number of variations name updated: '.$tot_vn_updated;
	}	
	wp_die();
}

/**/
function mw_wc_qbo_sync_redirect_deactivation_popup() {

	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'myworks_wc_qbo_sync_deactivate_feedback_nonce' ) ) {
		wp_send_json_error();
	}

	$feedback_url = 'https://forms.hubspot.com/uploads/form/v2/4333867/14e17026-f443-4d9e-a912-9558746c6634';

	$deactivation_reason = '';
	$deactivation_domain = '';
	$deactivation_license_key = '';

	if ( ! empty( $_POST['deactivation_reason'] ) ) {
		$deactivation_reason = $_POST['deactivation_reason'];
	}	

	if ( ! empty( $_POST['deactivation_domain'] ) ) {
		$deactivation_domain = $_POST['deactivation_domain'];
	}

	if ( ! empty( $_POST['deactivation_license_key'] ) ) {
		$deactivation_license_key = $_POST['deactivation_license_key'];
	}

	if ( ! empty( $_POST['email'] ) ) {
		$email = $_POST['email'];
	}	

	wp_remote_post($feedback_url, [
		'timeout' => 30,
		'body' => [
			'deactivation_reason' => $deactivation_reason,
			'deactivation_domain' => $deactivation_domain,
			'deactivation_license_key' => $deactivation_license_key,
			'email' => $email
		],
	] );

	wp_send_json_success();

	wp_die();
}

//
function mw_wc_qbo_sync_odpage_qbsync(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_odpage_qbsync', 'odpage_qbsync' ) ) {
		$id = (isset($_POST['ord_id']))?(int) $_POST['ord_id']:0;
		if($id > 0){
			global $MSQS_QL;
			global $MSQS_AD;
			
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
				$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
				
			}		
			
			$odp_qb_ou = (isset($_POST['odp_qb_ou']) && $_POST['odp_qb_ou'] == 1)?true:false;
			$return_id = $MSQS_AD->myworks_wc_qbo_sync_order_realtime(array('order_id'=>$id,'odp_qb'=>1));
			
			/*
			$qb_osa = 'Invoice';
			
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($id)){
				$qb_osa = 'Sales Receipt';
			}else{
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($id)){
					$qb_osa = 'Estimate';
				}
			}
			*/
			
			echo 'Attempted';
		}		
	}
	
	wp_die();
}

function mw_wc_qbo_sync_odpage_sync_status(){
	if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_odpage_syncstatus', 'odpage_syncstatus' ) ) {
		$ord_ids = $_POST['ord_ids'];
		global $MSQS_QL;
		
		$n_ord_ids = array();
		if(is_array($ord_ids) && !empty($ord_ids)){
			foreach($ord_ids as $oi){
				$osa = 'Invoice';
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt') || $MSQS_QL->is_order_sync_as_sales_receipt($oi)){
					$osa = 'SalesReceipt';
				}
				
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate') || $MSQS_QL->is_order_sync_as_estimate($oi)){
					$osa = 'Estimate';
				}
				
				if(!isset($n_ord_ids[$osa])){
					$n_ord_ids[$osa] = array();
				}
				
				$ord_no = $oi;
				/**/
				$is_qb_next_ord_num = false;
				if($MSQS_QL->option_checked('mw_wc_qbo_sync_use_qb_next_ord_num_iowon') && !$MSQS_QL->get_qbo_company_setting('is_custom_txn_num_allowed')){
					$is_qb_next_ord_num = true;
					if($oi){
						$ord_no = get_post_meta($oi,'_mw_qbo_sync_ord_doc_no',true);
						$ord_no = trim($ord_no);					
					}				
				}
				
				/**/
				if(!$is_qb_next_ord_num){
					$wc_inv_no = $MSQS_QL->get_woo_ord_number_from_order($oi);
					if(!empty($wc_inv_no)){
						$ord_no = $wc_inv_no;
					}
				}
				
				$n_ord_ids[$osa][$oi] = "'".$ord_no."'";
				if($MSQS_QL->is_plugin_active('split-order-custom-po-for-myworks-qbo-sync')){
					$n_ord_ids[$osa]['S-'.$oi] = "'S-".$ord_no."'";
					$n_ord_ids[$osa]['OS-'.$oi] = "'OS-".$ord_no."'";
				}
			}
		}
		
		//$MSQS_QL->_p($n_ord_ids);
		$ord_qb_map_data_a = array();
		$qbc_status = 'NC';
		if(is_array($n_ord_ids) && !empty($n_ord_ids)){
			if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
				$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
			}
			
			if($MSQS_QL->is_connected()){
				$qbc_status = 'C';
				$d_osa = array();
				foreach($n_ord_ids as $oi_k => $oi_v){
					if(!in_array($oi_k,$d_osa)){
						$d_osa[] = $oi_k;
						
						$is_qbosa_sr = ($oi_k == 'SalesReceipt')?true:false;
						$is_qbosa_est = ($oi_k == 'Estimate')?true:false;
						
						$ord_qb_map_data_a[$oi_k] = $MSQS_QL->get_push_invoice_map_data($oi_v,$is_qbosa_sr,$is_qbosa_est,true,true);
					}
				}
			}else{
				$qbc_status = 'NC';
			}
		}
		
		$r_arr = array();
		$r_arr['qbc_status'] = $qbc_status;
		$r_arr['map_data'] = $ord_qb_map_data_a;
		//$MSQS_QL->_p($r_arr);
		echo json_encode($r_arr);
	}
	
	wp_die();
}