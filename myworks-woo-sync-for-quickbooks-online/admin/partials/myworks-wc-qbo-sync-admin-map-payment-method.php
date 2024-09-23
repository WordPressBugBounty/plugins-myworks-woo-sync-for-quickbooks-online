<?php
if ( ! defined( 'ABSPATH' ) )
exit;
 
global $MWQS_OF;
global $MSQS_QL;
global $wpdb;
global $woocommerce;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=payment-method';
if (! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_payment_methods', 'map_wc_qbo_payment_method' ) ) {
	$table = $wpdb->prefix.'mw_wc_qbo_sync_paymentmethod_map';
	
	$wpdb->query($wpdb->prepare("DELETE FROM `$table` WHERE `id` > %d",0));
	
	$pm_name_cur_list_arr = array();
	 foreach($_POST as $k=>$val){
		 $check_pm = false;
		 if (preg_match('#^pm__#', $k) === 1) {
			$check_pm = true;
		 }
		 
		 /**/		 
		 if($check_pm){
			$p_method_str = $k;
			$p_method_arr = explode('__',$p_method_str);
			
			$p_method = '';
			$p_cur = '';
			if(count($p_method_arr)>2){
				$p_method = $p_method_arr[1];
				$p_cur = $p_method_arr[2];
			}
			
			$q_a_id  = (int) $val;
			
			if($p_method!='' && $p_cur!=''){
				if(isset($pm_name_cur_list_arr[$p_method.'__'.$p_cur])){
					continue;
				}
				$pm_name_cur_list_arr[$p_method.'__'.$p_cur] = true;
				
				$term_id = (int) (isset($_POST[$p_method.'__'.$p_cur.'_term']))?$_POST[$p_method.'__'.$p_cur.'_term']:0;
				
				if($q_a_id || $term_id>0){					
					$p_map_tran = 0;
			
					if(isset($_POST[$p_method.'__'.$p_cur.'_transaction'])){
						$p_map_tran = 1;
					}
					
					
					$p_map_ep = 0;
					
					if(isset($_POST[$p_method.'__'.$p_cur.'_ep'])){
						$p_map_ep = 1;
					}
					
					
					$p_map_tr = 0;
					
					if(isset($_POST[$p_method.'__'.$p_cur.'_tr'])){
						$p_map_tr = 1;
					}
					
					
					$p_map_er = 0;
					
					if(isset($_POST[$p_method.'__'.$p_cur.'_er'])){
						$p_map_er = 1;
					}
					
					$p_txn_exp_acc = (int) (isset($_POST[$p_method.'__'.$p_cur.'_expacc']))?$_POST[$p_method.'__'.$p_cur.'_expacc']:0;
					
					$enable_batch = 0;					
					if(isset($_POST[$p_method.'__'.$p_cur.'_ebatch'])){
						$enable_batch = 1;
					}
					
					$udf_account_id = (int) (isset($_POST[$p_method.'__'.$p_cur.'_udfacc']))?$_POST[$p_method.'__'.$p_cur.'_udfacc']:0;
					
					
					$vendor_id = (int) (isset($_POST[$p_method.'__'.$p_cur.'_vendor']))?$_POST[$p_method.'__'.$p_cur.'_vendor']:0;
					
					$deposit_date_field = (int) (isset($_POST[$p_method.'__'.$p_cur.'_batchdate']))?$_POST[$p_method.'__'.$p_cur.'_batchdate']:0;
					
					//$term_id = (int) (isset($_POST[$p_method.'__'.$p_cur.'_term']))?$_POST[$p_method.'__'.$p_cur.'_term']:0;
					
					//04-05-2017
					$ps_order_status = (int) (isset($_POST[$p_method.'__'.$p_cur.'_orst']))?$_POST[$p_method.'__'.$p_cur.'_orst']:'';
					
					$deposit_cron_sch = (isset($_POST[$p_method.'__'.$p_cur.'_dsch']))?trim($_POST[$p_method.'__'.$p_cur.'_dsch']):'';
					
					$deposit_cron_utc = (int) (isset($_POST[$p_method.'__'.$p_cur.'_dct']))?$_POST[$p_method.'__'.$p_cur.'_dct']:'';
					if($deposit_cron_utc!=''){
						$utc_arr = $MSQS_QL->get_dps_utc_time_arr();
						if(!is_array($utc_arr)){
							$deposit_cron_utc = '';
						}else{
							if(!isset($utc_arr[$deposit_cron_utc])){
								$deposit_cron_utc = '';
							}
						}
					}
					
					$lump_weekend_batches = 0;
					
					if(isset($_POST[$p_method.'__'.$p_cur.'_lwb'])){
						$lump_weekend_batches = 1;
					}
					
					$individual_batch_support = 0;
					
					if(isset($_POST[$p_method.'__'.$p_cur.'_ibs'])){
						$individual_batch_support = 1;
					}
					
					if($enable_batch==0){
						$lump_weekend_batches = 0;
						$individual_batch_support = 0;
						$deposit_cron_utc = '';
						//$deposit_cron_sch = 'Daily';
					}
					
					if($individual_batch_support==1){
						$lump_weekend_batches = 0;
					}
					
					$inv_due_date_days = 0;
					if(isset($_POST[$p_method.'__'.$p_cur.'_iddd'])){
						$inv_due_date_days = (int) $_POST[$p_method.'__'.$p_cur.'_iddd'];
					}

					$order_sync_as = (isset($_POST[$p_method.'__'.$p_cur.'_qosa']))?$_POST[$p_method.'__'.$p_cur.'_qosa']:'';
					
					$qb_p_method_id = (int) (isset($_POST[$p_method.'__'.$p_cur.'_qbpmethod']))?$_POST[$p_method.'__'.$p_cur.'_qbpmethod']:0;
					
					/**/
					if($udf_account_id <1 || empty($deposit_cron_utc)){
						$enable_batch = 0;
					}
					
					//save
					$pm_map_save_data = array();
					
					$pm_map_save_data['wc_paymentmethod'] = $p_method;
					$pm_map_save_data['qbo_account_id'] = $q_a_id;
					$pm_map_save_data['currency'] = $p_cur;
					$pm_map_save_data['enable_transaction'] = $p_map_tran;
					$pm_map_save_data['txn_expense_acc_id'] = $p_txn_exp_acc;
					$pm_map_save_data['enable_payment'] = $p_map_ep;
					$pm_map_save_data['txn_refund'] = $p_map_tr;
					$pm_map_save_data['enable_refund'] = $p_map_er;
					$pm_map_save_data['enable_batch'] = $enable_batch;
					$pm_map_save_data['udf_account_id'] = $udf_account_id;
					$pm_map_save_data['vendor_id'] = $vendor_id;
					$pm_map_save_data['deposit_date_field'] = $deposit_date_field;
					$pm_map_save_data['qb_p_method_id'] = $qb_p_method_id;
					$pm_map_save_data['lump_weekend_batches'] = $lump_weekend_batches;
					$pm_map_save_data['term_id'] = $term_id;
					
					//
					$pm_map_save_data['ps_order_status'] = $ps_order_status;
					$pm_map_save_data['individual_batch_support'] = $individual_batch_support;
					
					$pm_map_save_data['deposit_cron_sch'] = $deposit_cron_sch;
					$pm_map_save_data['deposit_cron_utc'] = $deposit_cron_utc;
					
					$pm_map_save_data['inv_due_date_days'] = $inv_due_date_days;
					
					$pm_map_save_data['order_sync_as'] = $order_sync_as;
					
					$pm_map_save_data = array_map(array($MSQS_QL, 'sanitize'), $pm_map_save_data);
					//$MSQS_QL->_p($pm_map_save_data);
					$wpdb->insert($table, $pm_map_save_data);					
						
				}
			}
		 }
	 }
	 $MSQS_QL->set_session_val('map_page_update_message',__('Payment methods mapped successfully.','mw_wc_qbo_sync'));
	 $MSQS_QL->redirect($page_url);	 
}

$wc_p_methods = array();
$wc_currency_list = array();
$mw_wc_qbo_sync_store_currency = $MSQS_QL->get_option('mw_wc_qbo_sync_store_currency');
if($mw_wc_qbo_sync_store_currency!='' && $MSQS_QL->get_qbo_company_setting('is_m_currency')){
	$wc_currency_list = explode(',',$mw_wc_qbo_sync_store_currency);
}else{
	$wc_currency_list[] = get_woocommerce_currency();
}

//$MSQS_QL->_p($wc_currency_list);
//
$available_gateways = WC()->payment_gateways()->payment_gateways;
if(is_array($available_gateways) && count($available_gateways)){
	foreach($available_gateways as $key=>$value){
		if($value->enabled=='yes'){
			$wc_p_methods[$value->id] = $value->title;
		}		
	}
}

$is_valid_pm = false;
if(is_array($wc_p_methods) && count($wc_p_methods) && is_array($wc_currency_list) && count($wc_currency_list)){
	$is_valid_pm = true;
}

$pm_map_data = $MSQS_QL->get_tbl($wpdb->prefix.'mw_wc_qbo_sync_paymentmethod_map');

$qbo_account_options = '<option value=""></option>';
$qbo_account_options.= $MSQS_QL->get_account_dropdown_list('',true,true);

$qbo_account_options_all = '<option value=""></option>';
$qbo_account_options_all.= $MSQS_QL->get_account_dropdown_list('',true);

$qbo_payment_method_options = '<option value=""></option>';
$qbo_payment_method_options.= $MSQS_QL->get_payment_method_dropdown_list();

$qbo_term_options = '<option value=""></option>';
#New
$qbo_term_options.= '<option value="1335">QuickBooks customer\'s default term</option>';
$qbo_term_options.= $MSQS_QL->get_term_dropdown_list();

$qbo_vendor_options = '<option value=""></option>';
$qbo_vendor_options.= $MSQS_QL->get_vendor_dropdown_list();

$order_statuses = wc_get_order_statuses();

//$MSQS_QL->_p($pm_map_data);
$pmd_frmt = array();$pmd_pmca = array();
if(is_array($pm_map_data) && count($pm_map_data)){
	foreach($pm_map_data as $list){
		$pmd_frmt[$list['wc_paymentmethod'].'__'.$list['currency']] = $list;
		$pmd_pmca[$list['wc_paymentmethod'].$list['currency']] = $list['wc_paymentmethod'].$list['currency'];
	}
}
//$MSQS_QL->_p($pmd_frmt);
$disable_this_section = true;
?>

<?php
	$qost_arr = array(
		'Invoice' => 'Invoice',
		'SalesReceipt' => 'SalesReceipt',
		//'Estimate' => 'Estimate',
	);
	
	if(!$MSQS_QL->is_plg_lc_p_l()){
		$qost_arr['Estimate'] = 'Estimate';
	}
	
	//$wo_qsa = ($MSQS_QL->get_option('mw_wc_qbo_sync_order_as_sales_receipt')=='true')?'SalesReceipt':'Invoice';
	$wo_qsa = $MSQS_QL->get_option('mw_wc_qbo_sync_order_qbo_sync_as');
	if($wo_qsa!='Invoice' && $wo_qsa!='SalesReceipt' && $wo_qsa!='Estimate'){
		$wo_qsa = 'Invoice';
	}
	
	//
	$js_dtos = '';
?>

<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>

<style>
.advanced_payment_sync, .myfh{display:none;}
.hide_advanced_payment_sync{display:none;}
.mw_pmm_tbl{border-bottom:1px solid #DDDDDD;}
</style>
<div class="container map-product-responsive">
	<div class="page_title flex-box"><h4><?php _e( 'Payment Method Mappings', 'mw_wc_qbo_sync' );?></h4> 
	<?php if(!$MSQS_QL->is_plg_lc_p_l(false)):?>
	<div class="dashboard_main_buttons p-mapbtn">
		<button class="show_advanced_payment_sync" id="show_advanced_payment_sync">Show Advanced Options</button>
		<button class="hide_advanced_payment_sync" id="hide_advanced_payment_sync">Hide Advanced Options</button>
	</div>
	<?php endif;?>
	</div>
	
	<div class="card">
		<div class="card-content">
			<div class="row">
			<?php if($is_valid_pm):?>
				<form method="POST" class="col s12 m12 l12" id="mw_pmm_form">
					<div class="row">
						<div class="col s12 m12 l12">
							<?php foreach($wc_p_methods as $pm_key => $pm_val):?>							
							<div class="pm_map_list" style="margin:10px 0px 10px 0px;">
								<h5><?php echo $pm_val.' ('.$pm_key.')';?></h5>
								<div class="myworks-wc-qbo-sync-table-responsive">
								<table class="mw-qbo-sync-settings-table menu-blue-bg menu-bg-a new-table mw_pmm_tbl" style="width:100%" cellpadding="5" cellspacing="5">
									<thead>
										<tr>
										<th width="40%">&nbsp;</th>
										<?php foreach($wc_currency_list as $c_val){?>			
										<th><b><?php echo $c_val;?></b></th>
										<?php }?>
										<th>&nbsp;</th>
										</tr>
									</thead>
									<tr class="default_payment_sync">
										<td height="40">
											Enable Payment Syncing											
										</td>
										<?php foreach($wc_currency_list as $c_val){?>
										<?php
											if(!in_array($pm_key.$c_val,$pmd_pmca)){
												$js_dtos.= 'jQuery("#'.$pm_key.'__'.$c_val.'_er").prop("checked", true);';
											}
										?>
										<td>
											<input data-cba="pm__<?php echo $pm_key;?>__<?php echo $c_val;?>" data-cba-qbpmethod="<?php echo $pm_key;?>__<?php echo $c_val;?>_qbpmethod" type="checkbox" class="pm_chk_ep pm_chk" value="1" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_ep" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_ep">											
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Enable the syncing of payments for this gateway & specific currency. If not enabled, payments will not be synced in real time to QuickBooks Online.','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>
									
									<?php if($MSQS_QL->get_option('mw_wc_qbo_sync_order_qbo_sync_as') == 'Per Gateway'):?>
									<tr class="default_payment_sync">
										<td height="40">
											WooCommerce Order Sync As
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_qosa" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_qosa">												
												<?php echo $MSQS_QL->only_option('',$qost_arr);?>
											</select>
										</td>
										<?php }?>
										<td>
											<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
											  <span class="tooltiptext"><?php echo __('Choose whether the WooCommerce order as Invoice or SalesReceipt','mw_wc_qbo_sync') ?></span>
											</div>
										</td>	
									</tr>
									<?php endif;?>
									
									<tr class="default_payment_sync">
										<td height="40">
											QuickBooks Online Payment Method											
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select style="background-color:#f4f4f4" disabled="disabled" title="Enable payment first" class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_qbpmethod" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_qbpmethod">
												<?php echo $qbo_payment_method_options;?>
											</select>
										</td>
										<?php }?>
                                        <td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Select the Payment Method that this woocommerce Gateway corresponds to in QuickBooks Online. This will be reflected in invoice payments made in QuickBooks Online, and deposits made if batch support is enabled.','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>                                    
									
									<tr class="default_payment_sync">
										<td height="40">
											QuickBooks Online Bank Account											
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select style="background-color:#f4f4f4" disabled="disabled" title="Enable payment first" class="qbo_select dd_qoba" name="pm__<?php echo $pm_key;?>__<?php echo $c_val;?>" id="pm__<?php echo $pm_key;?>__<?php echo $c_val;?>">
												<?php echo $qbo_account_options;?>
											</select>
											<input type="hidden" name="pm__<?php echo $pm_key;?>__<?php echo $c_val;?>__PMC" value="0">
										</td>
										<?php }?>
										<td>
											<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
											  <span class="tooltiptext"><?php echo __('Choose the Bank Account in QuickBooks Online that payments from your woocommerce gateway will be deposited into in real life / in QuickBooks Online.','mw_wc_qbo_sync') ?></span>
											</div>
										</td>
										
									</tr>
  
									<tr class="advanced_payment_sync">
										<td height="40">
											Enable Refund Syncing											
										</td>
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>
											<input class="pm_chk" type="checkbox" value="1" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_er" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_er">
										</td>
										<?php }?>
										<td>
											<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
											<span class="tooltiptext"><?php echo __('Enable the syncing of refunds made with this gateway in this specific currency. If not enabled, refunds will not be synced in real time to QuickBooks Online.','mw_wc_qbo_sync') ?></span>
											</div>
                                        </td>
									</tr>
									
									<tr class="advanced_payment_sync">
										<td height="40">
											Enable Transaction Fee Syncing											
										</td>
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>
											<input data-cba="<?php echo $pm_key;?>__<?php echo $c_val;?>_expacc" type="checkbox" class="pm_chk_trn pm_chk" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_transaction" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_transaction" value="1">
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Enable the syncing of transaction fees to QuickBooks Online. <br><br>These will show up as a journal entry in QuickBooks Online crediting the bank account chosen to the left and debiting the expense account chosen to the right on this page.<br><br> NOTE: Transaction fees will only be synced if this option is turned on and transaction fees are recorded in woocommerce by the gateway.','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>
									
									<tr class="advanced_payment_sync">
										<td height="40">
											QuickBooks Online Expense Account											
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select style="background-color:#f4f4f4" disabled="disabled" title="Enable transaction Fee first" class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_expacc" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_expacc">
												<?php echo $qbo_account_options_all;?>
											</select>
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Choose the Expense Account in QuickBooks Online that transaction fees recorded in woocommerce will be synced to. This option is only enabled if the Sync TXN fees option is on.','mw_wc_qbo_sync') ?></span>
										</div>
										</td>										
									</tr>
    								
									<tr class="advanced_payment_sync">
										<td height="40">
											Enable Transaction Fee Refund Syncing											
										</td>
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>
											<input class="pm_chk" type="checkbox" value="1" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_tr" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_tr">
										</td>
										<?php }?>
										<td>
											<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
											  <span class="tooltiptext"><?php echo __('Enable the syncing of transaction fee refunds when a woocommerce refund is processed and synced to QuickBooks Online. <br><br>These will show up as a journal entry in QuickBooks Online debiting the bank account chosen to the left and crediting the expense account chosen to the right on this page for the amount of the transaction fee.<br><br> This option is helpful if you have a gateway like PayPal that refunds transaction fees when a payment refund is processed.','mw_wc_qbo_sync') ?></span>
											</div>
										</td>
									</tr>
									
									<tr class="advanced_payment_sync">
										<td height="40">
											Batch Support
											<!--
											</br>
											<span style="font-size:10px;color:grey;">You must visit MyWorks Sync > Connection and sign into Step 2</br>after you save these settings to register these changes.</span>
											-->
										</td>
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>
											<input data-cba="<?php echo $pm_key;?>__<?php echo $c_val;?>_udfacc" data-cba-vendor="<?php echo $pm_key;?>__<?php echo $c_val;?>_vendor" data-cba-batchdate="<?php echo $pm_key;?>__<?php echo $c_val;?>_batchdate" data-cba-lwb="<?php echo $pm_key;?>__<?php echo $c_val;?>_lwb" data-cba-ibs="<?php echo $pm_key;?>__<?php echo $c_val;?>_ibs" class="pm_chk_batch pm_chk" type="checkbox" value="1" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_ebatch" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_ebatch">
										</td>
										<?php }?>
										<td>
											<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
											  <span class="tooltiptext"><?php echo __('Batch Payment Support will allow maximum compatibility with card processors that deposit daily batches into your bank account.','mw_wc_qbo_sync') ?></span>
											</div>
                                        </td>
									</tr>
									
     								<?php if(!$disable_this_section):?>
									<tr class="advanced_payment_sync">
										<td height="40">
											Individual Batch Support									
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>					
											<input class="pm_chk" disabled="disabled" title="Enable batch payment first" type="checkbox" value="1" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_ibs" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_ibs">
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Individual Batch Support','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>
									<?php endif;?>
									
									<tr class="advanced_payment_sync">
										<td height="40">
											Deposit schedule</br>											
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>										
											<select class="qbo_select pm_nbv dd_dcsch" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_dsch" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_dsch">
												<!--<option value=""></option>-->
												<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_dps_sch_arr());?>
											</select>
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Select the day of week that our integration should create a bank deposit in QuickBooks.','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>
									
									<tr class="advanced_payment_sync">
										<td height="40">
											<!--Daily--> Batch Deposit Time (UTC)</br>
											<span style="font-size:10px;color:grey;">Stripe is 0:00 UTC.</span> 													
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>										
											<select class="qbo_select pm_nbv" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_dct" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_dct">
												<option value=""></option>
												<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_dps_utc_time_arr());?>
											</select>
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Select the time that our integration should create a bank deposit in QuickBooks that batches the last 24 hours of payments.','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>
									
     								<tr class="advanced_payment_sync">
										<td height="40">
											Undeposited Funds Account											
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select style="background-color:#f4f4f4" disabled="disabled" title="Enable batch payment first" class="qbo_select pm_nbv" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_udfacc" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_udfacc">												
												<?php echo $qbo_account_options;?>
											</select>
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Select your Undeposited Funds account - where payments will be recorded to when synced into QuickBooks. The daily deposit we create in QuickBooks will move the funds from here into the bank account chosen above.','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>     
									
									<tr class="advanced_payment_sync">
										<td height="40">
												Transaction Fees Vendor											
											</td>
											
											<?php foreach($wc_currency_list as $c_val){?>			
											<td class="new-widt">
												<select style="background-color:#f4f4f4" disabled="disabled" title="Enable batch payment first" class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_vendor" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_vendor">
													<?php echo $qbo_vendor_options;?>
												</select>
											</td>
											<?php }?>
										<td>
											<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
											  <span class="tooltiptext"><?php echo __('Select the vendor in QuickBooks Online that the transaction fees line item in the daily deposit will be recorded to if Batch Support and Transaction Fee Syncing is enabled above.','mw_wc_qbo_sync') ?></span>
											</div>
										</td>	
									</tr>									
									
									<?php 
									$s_dc_sch = '';
									$s_eb = 0;
									if(isset($pmd_frmt[$pm_key.'__'.$c_val])){
										if($pmd_frmt[$pm_key.'__'.$c_val]['enable_batch']){$s_eb = 1;}
										$s_dc_sch = $pmd_frmt[$pm_key.'__'.$c_val]['deposit_cron_sch'];
									}
									
									$tr_ext_cls = '';
									if($s_eb){
										$tr_ext_cls = 'myfh';
										if(empty($s_dc_sch) || $s_dc_sch == 'Daily'){
											$tr_ext_cls = '';
										}
									}
									?>
									<tr class="advanced_payment_sync <?php echo $pm_key.'__'.$c_val.'_wbtr';?> <?php echo $tr_ext_cls;?>">
										<td height="40">
											Combine weekend payments in Monday's batch</br>
											<span style="font-size:10px;color:grey;">Turn this option on if using Stripe.</span> 										
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td>					
											<input class="pm_chk" disabled="disabled" title="Enable batch payment first" type="checkbox" value="1" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_lwb" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_lwb">
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Enable this option if your processor lumps payments from Saturday & Sunday into Mondays batch for one deposit into your account.','mw_wc_qbo_sync') ?></span>
										</div>
                                        </td>
									</tr>
									
									<?php
									$b_date_fields = array(
										'post_date' => 'Order Date',
										'_paid_date' => 'Order Paid Date',
										'_completed_date' => 'Order Completed Date',
									);
									?>
									<tr class="advanced_payment_sync">
										<td height="40">
												Date Used for Batching Orders											
											</td>
											
											<?php foreach($wc_currency_list as $c_val){?>			
											<td class="new-widt">
												<select style="background-color:#f4f4f4" disabled="disabled" title="Enable batch payment first" class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_batchdate" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_batchdate">
													<?php echo $MSQS_QL->only_option('',$b_date_fields);?>
												</select>
											</td>
											<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Select the order date field that will be used when calculating if an order should be included in a specific batch for the day. This should be set to Order Date by default, but can be changed to Date Paid if dealing with edge scenarios where orders can be paid on a different date than when they were placed.','mw_wc_qbo_sync') ?></span>
										</div>
										</td>	
									</tr>
									
									<tr class="advanced_payment_sync">
										<td height="40">
											Terms Mapping											
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_term" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_term">
												<?php echo $qbo_term_options;?>
											</select>
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Select the Term in the QuickBooks Online Invoice, if any, that should be assigned to invoices paid with this payment method.','mw_wc_qbo_sync') ?></span>
										</div>
										</td>	
									</tr>
									
									<tr class="advanced_payment_sync">
										<td height="40">
											Sync artificial payment when order is marked as: 	</br>
												<span style="font-size:10px;color:grey;">This setting is ONLY for gateways like COD or Check where the payment is actually not recorded in WooCommerce.</span> 										
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_orst" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_orst">
												<option value=""></option>
												<?php echo $MSQS_QL->only_option('',$order_statuses);?>
											</select>
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('This setting is ONLY for gateways like COD or BACS where the payment is actually not recorded in WooCommerce. When orders are placed with these types of gateways, there is no actual payment recorded in WooCommerce, so the payment can only be synced to QuickBooks Online when the order reaches a certain status.','mw_wc_qbo_sync') ?></span>
										</div>
										</td>	
									</tr>
									
									<?php if($wo_qsa =='Invoice'):?>
									<tr class="advanced_payment_sync">
										<td height="40">
											<?php echo __('QuickBooks Invoice Due Date Delay','mw_wc_qbo_sync') ?>
										</td>
										
										<?php foreach($wc_currency_list as $c_val){?>			
										<td class="new-widt">
											<select class="qbo_select" name="<?php echo $pm_key;?>__<?php echo $c_val;?>_iddd" id="<?php echo $pm_key;?>__<?php echo $c_val;?>_iddd">
												<option value="0">0</option>
												<?php echo $MSQS_QL->only_option('',$MSQS_QL->due_days_list_arr());?>
											</select>
										</td>
										<?php }?>
										<td>
										<div class="material-icons tooltipped right tooltip"><?php echo __('?','mw_wc_qbo_sync') ?>
										  <span class="tooltiptext"><?php echo __('Select the amount of days from the order date to set the QuickBooks Invoice Due Date field. The default is 0 - the same date as the WooCommerce order.','mw_wc_qbo_sync') ?></span>
										</div>
										</td>	
									</tr>
									<?php endif;?>
									
								</table>
								</div>
								<!--<hr />-->
							</div>
							<?php endforeach;?>
						</div>
					</div>
					
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_payment_methods', 'map_wc_qbo_payment_method' ); ?>
						<div class="input-field col s12 m6 l4">
							<button class="waves-effect waves-light btn save-btn mw-qbo-sync-green">Save</button>
						</div>
					</div>
					
				</form>
				
				<?php //echo MyWorks_WC_QBO_Sync_Admin::get_checkbox_switch_assets();?>
				<script type="text/javascript">
				jQuery(document).ready(function(e){
					<?php echo $js_dtos;?>
					//jQuery('.default_payment_sync').show();
					jQuery('#show_advanced_payment_sync').show();
					jQuery('.advanced_payment_sync').hide();
					jQuery('#hide_advanced_payment_sync').hide();
					jQuery('.pm_map_list').removeClass('active');
					
					jQuery('#show_advanced_payment_sync').on('click', function(e){
						jQuery('#show_advanced_payment_sync').hide();
						jQuery('.advanced_payment_sync').not('.myfh').show();
						jQuery('#hide_advanced_payment_sync').show();
						jQuery('.pm_map_list').addClass('active');
					});

					jQuery('#hide_advanced_payment_sync').on('click', function(e){
						jQuery('#show_advanced_payment_sync').show();
						jQuery('.advanced_payment_sync').hide();
						jQuery('#hide_advanced_payment_sync').hide();
						jQuery('.pm_map_list').removeClass('active');
					});
					
					jQuery('select.dd_dcsch').on('change', function(e){						
						var dsid = jQuery(this).attr('id');							
						dsid = dsid.replace("dsch", "wbtr");						
						if(jQuery(this).val() == '' || jQuery(this).val() == 'Daily'){
							jQuery('.'+dsid).show();
						}else{
							jQuery('.'+dsid).hide();
						}
					});
				});
				
				jQuery(document).ready(function($){
					jQuery('input.pm_chk').attr('data-size','small');
					//jQuery('.pm_chk_ep , .pm_chk_trn, .pm_chk_batch').click(function(){
					jQuery('.pm_chk_ep , .pm_chk_trn , .pm_chk_batch').on('switchChange.bootstrapSwitch', function () {
			
						var chk_ba = jQuery(this).attr('data-cba');
						if(chk_ba==''){return false;}
						
						var data_cba_vendor = false;
						if (typeof jQuery(this).attr('data-cba-vendor') !== typeof undefined && jQuery(this).attr('data-cba-vendor') !== false) {
							data_cba_vendor = jQuery(this).attr('data-cba-vendor');
						}
						
						var data_cba_batchdate = false;
						if (typeof jQuery(this).attr('data-cba-batchdate') !== typeof undefined && jQuery(this).attr('data-cba-batchdate') !== false) {
							data_cba_batchdate = jQuery(this).attr('data-cba-batchdate');
						}
						
						var data_cba_qbpmethod = false;
						if (typeof jQuery(this).attr('data-cba-qbpmethod') !== typeof undefined && jQuery(this).attr('data-cba-qbpmethod') !== false) {
							data_cba_qbpmethod = jQuery(this).attr('data-cba-qbpmethod');
						}
						
						var data_cba_lwb = false;
						if (typeof jQuery(this).attr('data-cba-lwb') !== typeof undefined && jQuery(this).attr('data-cba-lwb') !== false) {
							data_cba_lwb = jQuery(this).attr('data-cba-lwb');
						}
						
						var data_cba_ibs = false;
						if (typeof jQuery(this).attr('data-cba-ibs') !== typeof undefined && jQuery(this).attr('data-cba-ibs') !== false) {
							data_cba_ibs = jQuery(this).attr('data-cba-ibs');
						}
						
						if(jQuery(this).is(':checked')){
							jQuery('#'+chk_ba).removeAttr('disabled');
							jQuery('#'+chk_ba).css('background-color','#ffffff');
							jQuery('#'+chk_ba).removeAttr('title');
							/**/
							//$('#'+chk_ba).parent('td').parent('tr').show();
							
							if(data_cba_vendor){
								jQuery('#'+data_cba_vendor).removeAttr('disabled');
								jQuery('#'+data_cba_vendor).css('background-color','#ffffff');
								jQuery('#'+data_cba_vendor).removeAttr('title');
							}
							
							if(data_cba_batchdate){
								jQuery('#'+data_cba_batchdate).removeAttr('disabled');
								jQuery('#'+data_cba_batchdate).css('background-color','#ffffff');
								jQuery('#'+data_cba_batchdate).removeAttr('title');
							}
							
							if(data_cba_lwb){
								jQuery('#'+data_cba_lwb).bootstrapSwitch('disabled',false);
								jQuery('#'+data_cba_lwb).removeAttr('disabled');
								jQuery('#'+data_cba_lwb).css('background-color','#ffffff');
								jQuery('#'+data_cba_lwb).removeAttr('title');
							}
							
							if(data_cba_ibs){
								jQuery('#'+data_cba_ibs).bootstrapSwitch('disabled',false);
								jQuery('#'+data_cba_ibs).removeAttr('disabled');
								jQuery('#'+data_cba_ibs).css('background-color','#ffffff');
								jQuery('#'+data_cba_ibs).removeAttr('title');
							}
							//
							if(data_cba_qbpmethod){					
								jQuery('#'+data_cba_qbpmethod).removeAttr('disabled');
								jQuery('#'+data_cba_qbpmethod).css('background-color','#ffffff');
								jQuery('#'+data_cba_qbpmethod).removeAttr('title');
								
								//$('#'+data_cba_qbpmethod).parent('td').parent('tr').show();
							}
							
						}else{
							jQuery('#'+chk_ba).val('');
							jQuery('#'+chk_ba).attr('disabled','disabled');
							jQuery('#'+chk_ba).css('background-color','#f4f4f4');
							/**/
							//$('#'+chk_ba).parent('td').parent('tr').hide();
							
							if(data_cba_vendor){
								jQuery('#'+data_cba_vendor).val('');
								jQuery('#'+data_cba_vendor).attr('disabled','disabled');
								jQuery('#'+data_cba_vendor).css('background-color','#f4f4f4');
							}
							
							if(data_cba_batchdate){
								jQuery('#'+data_cba_batchdate).val('post_date');
								jQuery('#'+data_cba_batchdate).attr('disabled','disabled');
								jQuery('#'+data_cba_batchdate).css('background-color','#f4f4f4');
							}
							
							if(data_cba_lwb){
								jQuery('#'+data_cba_lwb).prop('checked', false);
								jQuery('#'+data_cba_lwb).bootstrapSwitch('disabled',true);
								jQuery('#'+data_cba_lwb).attr('disabled','disabled');
								jQuery('#'+data_cba_lwb).css('background-color','#f4f4f4');
							}
							
							if(data_cba_ibs){
								jQuery('#'+data_cba_ibs).prop('checked', false);
								jQuery('#'+data_cba_ibs).bootstrapSwitch('disabled',true);
								jQuery('#'+data_cba_ibs).attr('disabled','disabled');
								jQuery('#'+data_cba_ibs).css('background-color','#f4f4f4');
							}
							
							if(data_cba_qbpmethod){
								jQuery('#'+data_cba_qbpmethod).val('');
								jQuery('#'+data_cba_qbpmethod).attr('disabled','disabled');
								jQuery('#'+data_cba_qbpmethod).css('background-color','#f4f4f4');
								
								//$('#'+data_cba_qbpmethod).parent('td').parent('tr').hide();
							}
							
							var c_title = '';							
							
							if(jQuery(this).hasClass('pm_chk_ep')){
								c_title = 'Enable payment first';
							}
							
							if(jQuery(this).hasClass('pm_chk_trn')){
								c_title = 'Enable transaction fee first';
							}
							
							if(jQuery(this).hasClass('pm_chk_batch')){
								c_title = 'Enable batch payment first';
							}
							
							jQuery('#'+chk_ba).attr('title',c_title);
							
							if(data_cba_vendor){
								jQuery('#'+data_cba_vendor).attr('title',c_title);
							}
							
							if(data_cba_batchdate){
								jQuery('#'+data_cba_batchdate).attr('title',c_title);
							}
							
							if(data_cba_lwb){
								jQuery('#'+data_cba_lwb).attr('title',c_title);
							}
							
							if(data_cba_ibs){
								jQuery('#'+data_cba_ibs).attr('title',c_title);
							}
							
							if(data_cba_qbpmethod){
								jQuery('#'+data_cba_qbpmethod).attr('title',c_title);
							}
							
						}
					});
					
					<?php if(is_array($pm_map_data) && count($pm_map_data)):?>
					<?php foreach($pm_map_data as $list):?>
					
					<?php 
						$p_map_ac_id = $list['qbo_account_id'];
						$w_p_method = $list['wc_paymentmethod'];
						$p_map_cur = $list['currency'];
						$p_map_tran = $list['enable_transaction'];
						
						$p_map_ep = $list['enable_payment'];
						
						$p_map_tr = $list['txn_refund'];
						
						$p_map_er = $list['enable_refund'];
						
						$p_map_expacc = $list['txn_expense_acc_id'];
						
						$enable_batch = $list['enable_batch'];
						
						$udf_account_id = $list['udf_account_id'];
						
						//
						$vendor_id = $list['vendor_id'];
						
						$deposit_date_field = $list['deposit_date_field'];
						if(empty($deposit_date_field)){
							$deposit_date_field = 'post_date';
						}
						
						$qb_p_method_id = $list['qb_p_method_id'];
						
						$lump_weekend_batches = $list['lump_weekend_batches'];
						$individual_batch_support = $list['individual_batch_support'];
						
						//
						$term_id = $list['term_id'];
						
						$ps_order_status = $list['ps_order_status'];
						
						$deposit_cron_sch = $list['deposit_cron_sch'];
						if(empty($deposit_cron_sch)){//$enable_batch && 
							$deposit_cron_sch = 'Daily';
						}
						
						$deposit_cron_utc = $list['deposit_cron_utc'];
						$inv_due_date_days = $list['inv_due_date_days'];
						
						$order_sync_as = $list['order_sync_as'];
					?>
					
					jQuery('#pm__<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>').val('<?php echo $p_map_ac_id;?>');
					<?php if($p_map_tran==1):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_transaction').prop('checked', true);
					<?php endif;?>
					
					<?php if($p_map_ep==1):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_ep').prop('checked', true);
					<?php endif;?>
					
					<?php if($p_map_tr==1):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_tr').prop('checked', true);
					<?php endif;?>
					
					<?php if($p_map_er==1):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_er').prop('checked', true);
					<?php endif;?>
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_expacc').val('<?php echo $p_map_expacc;?>');
					
					<?php if($enable_batch==1):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_ebatch').prop('checked', true);
					<?php endif;?>
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_udfacc').val('<?php echo $udf_account_id;?>');
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_vendor').val('<?php echo $vendor_id;?>');
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_batchdate').val('<?php echo $deposit_date_field;?>');
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_term').val('<?php echo $term_id;?>');
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_orst').val('<?php echo $ps_order_status;?>');
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_dsch').val('<?php echo $deposit_cron_sch;?>');
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_dct').val('<?php echo $deposit_cron_utc;?>');
					
					<?php if($wo_qsa =='Invoice'):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_iddd').val('<?php echo $inv_due_date_days;?>');
					<?php endif;?>
					
					<?php if(!empty($order_sync_as)):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_qosa').val('<?php echo $order_sync_as;?>');
					<?php endif;?>
					
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_qbpmethod').val('<?php echo $qb_p_method_id;?>');
					
					<?php if($lump_weekend_batches==1):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_lwb').prop('checked', true);
					<?php endif;?>
					
					<?php if($individual_batch_support==1):?>
					jQuery('#<?php echo $w_p_method;?>__<?php echo $p_map_cur;?>_ibs').prop('checked', true);
					<?php endif;?>
					
					<?php endforeach;?>
					<?php endif;?>
					
					
					jQuery('.pm_chk_ep , .pm_chk_trn , .pm_chk_batch').each(function(){
						var chk_ba = jQuery(this).attr('data-cba');
						if(chk_ba==''){return false;}
						
						var data_cba_vendor = false;
						if (typeof jQuery(this).attr('data-cba-vendor') !== typeof undefined && jQuery(this).attr('data-cba-vendor') !== false) {
							data_cba_vendor = jQuery(this).attr('data-cba-vendor');
						}
						
						var data_cba_batchdate = false;
						if (typeof jQuery(this).attr('data-cba-batchdate') !== typeof undefined && jQuery(this).attr('data-cba-batchdate') !== false) {
							data_cba_batchdate = jQuery(this).attr('data-cba-batchdate');
						}
						
						var data_cba_lwb = false;
						if (typeof jQuery(this).attr('data-cba-lwb') !== typeof undefined && jQuery(this).attr('data-cba-lwb') !== false) {
							data_cba_lwb = jQuery(this).attr('data-cba-lwb');
						}
						
						var data_cba_ibs = false;
						if (typeof jQuery(this).attr('data-cba-ibs') !== typeof undefined && jQuery(this).attr('data-cba-ibs') !== false) {
							data_cba_ibs = jQuery(this).attr('data-cba-ibs');
						}
						
						
						var data_cba_qbpmethod = false;
						if (typeof jQuery(this).attr('data-cba-qbpmethod') !== typeof undefined && jQuery(this).attr('data-cba-qbpmethod') !== false) {
							data_cba_qbpmethod = jQuery(this).attr('data-cba-qbpmethod');
						}
						
						if(jQuery(this).is(':checked')){
							jQuery('#'+chk_ba).removeAttr('disabled');
							jQuery('#'+chk_ba).css('background-color','#ffffff');
							jQuery('#'+chk_ba).removeAttr('title');
							
							//$('#'+chk_ba).parent('td').parent('tr').show();							
							
							if(data_cba_vendor){
								jQuery('#'+data_cba_vendor).removeAttr('disabled');
								jQuery('#'+data_cba_vendor).css('background-color','#ffffff');
								jQuery('#'+data_cba_vendor).removeAttr('title');
							}
							
							if(data_cba_batchdate){
								jQuery('#'+data_cba_batchdate).removeAttr('disabled');
								jQuery('#'+data_cba_batchdate).css('background-color','#ffffff');
								jQuery('#'+data_cba_batchdate).removeAttr('title');
							}
							
							if(data_cba_lwb){
								jQuery('#'+data_cba_lwb).bootstrapSwitch('disabled',false);
								jQuery('#'+data_cba_lwb).removeAttr('disabled');
								jQuery('#'+data_cba_lwb).css('background-color','#ffffff');
								jQuery('#'+data_cba_lwb).removeAttr('title');
							}
							
							if(data_cba_ibs){
								jQuery('#'+data_cba_ibs).bootstrapSwitch('disabled',false);
								jQuery('#'+data_cba_ibs).removeAttr('disabled');
								jQuery('#'+data_cba_ibs).css('background-color','#ffffff');
								jQuery('#'+data_cba_ibs).removeAttr('title');
							}
							//
							if(data_cba_qbpmethod){
								jQuery('#'+data_cba_qbpmethod).removeAttr('disabled');
								jQuery('#'+data_cba_qbpmethod).css('background-color','#ffffff');
								jQuery('#'+data_cba_qbpmethod).removeAttr('title');
								
								//$('#'+data_cba_qbpmethod).parent('td').parent('tr').show();
							}
							
						}else{
							jQuery('#'+chk_ba).val('');
							jQuery('#'+chk_ba).attr('disabled','disabled');
							jQuery('#'+chk_ba).css('background-color','#f4f4f4');
							
							//$('#'+chk_ba).parent('td').parent('tr').hide();
							
							if(data_cba_vendor){
								jQuery('#'+data_cba_vendor).val('');
								jQuery('#'+data_cba_vendor).attr('disabled','disabled');
								jQuery('#'+data_cba_vendor).css('background-color','#f4f4f4');
							}
							
							if(data_cba_batchdate){
								jQuery('#'+data_cba_batchdate).val('post_date');
								jQuery('#'+data_cba_batchdate).attr('disabled','disabled');
								jQuery('#'+data_cba_batchdate).css('background-color','#f4f4f4');
							}
							
							if(data_cba_lwb){
								jQuery('#'+data_cba_lwb).prop('checked', false);
								jQuery('#'+data_cba_lwb).bootstrapSwitch('disabled',true);
								jQuery('#'+data_cba_lwb).attr('disabled','disabled');
								jQuery('#'+data_cba_lwb).css('background-color','#f4f4f4');
							}
							
							if(data_cba_ibs){
								jQuery('#'+data_cba_ibs).prop('checked', false);
								jQuery('#'+data_cba_ibs).bootstrapSwitch('disabled',true);
								jQuery('#'+data_cba_ibs).attr('disabled','disabled');
								jQuery('#'+data_cba_ibs).css('background-color','#f4f4f4');
							}
							
							if(data_cba_qbpmethod){
								jQuery('#'+data_cba_qbpmethod).val('');
								jQuery('#'+data_cba_qbpmethod).attr('disabled','disabled');
								jQuery('#'+data_cba_qbpmethod).css('background-color','#f4f4f4');
								
								//$('#'+data_cba_qbpmethod).parent('td').parent('tr').hide();
							}
							
							var c_title = '';
							
							
							if(jQuery(this).hasClass('pm_chk_ep')){
								c_title = 'Enable payment first';
							}
							
							if(jQuery(this).hasClass('pm_chk_trn')){
								c_title = 'Enable transaction fee first';
							}
							
							if(jQuery(this).hasClass('pm_chk_batch')){
								c_title = 'Enable batch payment first';
							}
							
							jQuery('#'+chk_ba).attr('title',c_title);
							
							if(data_cba_vendor){
								jQuery('#'+data_cba_vendor).attr('title',c_title);
							}
							
							if(data_cba_batchdate){
								jQuery('#'+data_cba_batchdate).attr('title',c_title);
							}
							
							if(data_cba_lwb){
								jQuery('#'+data_cba_lwb).attr('title',c_title);
							}
							if(data_cba_ibs){
								jQuery('#'+data_cba_ibs).attr('title',c_title);
							}
							if(data_cba_qbpmethod){
								jQuery('#'+data_cba_qbpmethod).attr('title',c_title);
							}
						}
					});
					
					jQuery('input.pm_chk').bootstrapSwitch();
					
					/*
					$('.pm_nbv option').filter(function() {
						return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
					}).remove();
					*/
					
					$('.dd_qoba option').filter(function() {
						return $.trim(this.text).indexOf('(Bank)') === -1 && $.trim(this.text).indexOf('(Other Current Asset)') === -1;
					}).remove();
					$(".dd_qoba").prepend('<option value=""></option>');					
					
					//$(".pm_nbv").prepend('<option value=""></option>');
					
					$('#mw_pmm_form').on('submit', function() {						
						return mw_pmm_f_validation();						
					});
					
				});
				
				function Mw_isEmpty(val){
					return (val === undefined || val == null || val.length <= 0) ? true : false;
				}
				
				function mw_pmm_f_validation(){
					var ive = false;
					jQuery('.pm_chk_ep').each(function(){
						if(jQuery(this).is(':checked')){
							epf_id = jQuery(this).attr('id');
							epf_id_st = epf_id.substring(0,epf_id.length - 3);
							
							//var bs_enb = jQuery('#'+epf_id_st+'_ebatch').val();
							var bs_enb = jQuery('#'+epf_id_st+'_ebatch').is(':checked');
							var bs_dsch = jQuery('#'+epf_id_st+'_dsch').val();
							var bs_dct = jQuery('#'+epf_id_st+'_dct').val();
							var bs_ufa = jQuery('#'+epf_id_st+'_udfacc').val();
							
							if(bs_enb && (Mw_isEmpty(bs_dsch) || Mw_isEmpty(bs_dct) || Mw_isEmpty(bs_ufa))){
								ive = true;
							}
							
						}
					});
					
					if(ive){
						alert('Plesae select deposit schedule, cron time and batch payment holding account if batch payment enabled');
						return false;
					}
					
					return true;
				}
				</script>				
				
				<?php echo $MWQS_OF->get_select2_js();?>
				
			<?php endif;?>
			</div>
		</div>
	</div>
</div>