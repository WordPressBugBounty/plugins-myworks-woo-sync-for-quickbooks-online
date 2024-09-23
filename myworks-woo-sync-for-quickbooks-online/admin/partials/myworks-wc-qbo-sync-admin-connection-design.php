<?php 
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;

$disable_access_token = true;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
}

$plugin_dir_url = plugin_dir_url( dirname(__FILE__) );
$plugin_folder_name = 'myworks-woo-sync-for-quickbooks-online';
//$page_url = 'admin.php?page=myworks-wc-qbo-sync-connection&design=1';
$page_url = 'admin.php?page=myworks-wc-qbo-sync-connection';

/**/
if(!$disable_access_token && !empty($_POST['update_access_token']) && check_admin_referer('myworks_wc_qbo_save_access_token', 'update_access_token')){	
	$mw_wc_qbo_sync_access_token_update = $MSQS_QL->var_p('mw_wc_qbo_sync_access_token_update');
	$mw_wc_qbo_sync_access_token_update = $MSQS_QL->sanitize($mw_wc_qbo_sync_access_token_update);
	update_option( 'mw_wc_qbo_sync_access_token', $mw_wc_qbo_sync_access_token_update );

	if($MSQS_QL->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $mw_wc_qbo_sync_access_token_update!=$MSQS_QL->get_option('mw_wc_qbo_sync_access_token')){
		$MSQS_QL->set_session_val('new_access_token_rts',1);
	}
	$MSQS_QL->redirect($page_url);
}

$mw_wc_qbo_sync_license = $MSQS_QL->get_option('mw_wc_qbo_sync_license','');
$mw_wc_qbo_sync_localkey = $MSQS_QL->get_option('mw_wc_qbo_sync_localkey','');
$mw_wc_qbo_sync_access_token = $MSQS_QL->get_option('mw_wc_qbo_sync_access_token','');

if($MSQS_QL->is_connected()){	
	$Context = $MSQS_QL->getContext();
	$realm = $MSQS_QL->getRealm();

	$CompanyInfoService = new QuickBooks_IPP_Service_CompanyInfo();
	$quickbooks_CompanyInfo = $CompanyInfoService->get($Context, $realm);
	//$MSQS_QL->_p($quickbooks_CompanyInfo);
	$local_connection_status_txt = 'Connected';
	
	/**/
	global $oqbc_nc;	
	if(isset($oqbc_nc) && $oqbc_nc){
		echo '<style type="text/css">.mw-qbo-sync-welcome{display:none;}</style>';
	}
}else{
	$local_connection_status_txt = '<span style="color:red;">Not Connected</span>';
}

$ldfcpv = $MWQS_OF->get_ldfcpv();
?>

<div class="ncd-cnt body-content">
	<div class="margin">
		<?php if($MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey)):?>
		<div class="rows">
		   <div class="cols-md-8 left-row">
			  <div class="left-outer">
				 <div class="left-iner">
					<div class="lt-top">
					   <div class="lkq">
						  <div class="lkg-img">
							 <img src="<?php echo plugins_url( $plugin_folder_name.'/admin/image/quick.png' ) ?>" class="img-res">
						  </div>
						  <div class="lkg-content">
							 <h3>QuickBooks Connection</h3>
							 <span>WooCommerce Sync for QuickBooks Online</span>
						  </div>
					   </div>
					</div>
					<div class="md-licence">
					   <div class="inr-l">
						  <div class="value-key">
							 <label for="mw_wc_qbo_sync_license_update"> License key</label>
							 <input title="<?php echo __('To update your license key, deactivate and re-activate the plugin. All your settings and mappings will be saved.','mw_wc_qbo_sync');?>" type="text" name="mw_wc_qbo_sync_license_update" id="mw_wc_qbo_sync_license_update" value="<?php echo $mw_wc_qbo_sync_license;?>" disabled="disabled">
							 
							 <div class="refresh-key">
								<img src="<?php echo plugins_url( $plugin_folder_name.'/admin/image/refresh-key.png' ) ?>" class="img-res">
								<span><a id="mwqs_dllk" title="<?php echo __('Refresh your license information','mw_wc_qbo_sync');?>" href="javascript:void(0);">Refresh License</a></span>
								<?php wp_nonce_field( 'myworks_wc_qbo_sync_del_license_local_key', 'del_license_local_key' );?>
							 </div>
						  </div>
						  
						  <?php if(!$disable_access_token):?>
						  <form id="lkqc_atls_form" method="post" action="<?php echo $page_url;?>">
							  <div class="value-key" style="margin-top:15px;">
								 <label for="mw_wc_qbo_sync_access_token_update"> Access Token</label>
								 <input title="<?php echo __('Enter the access token found inside your account with us after connecting to QuickBooks.','mw_wc_qbo_sync');?>" type="text" name="mw_wc_qbo_sync_access_token_update" id="mw_wc_qbo_sync_access_token_update" value="<?php echo $mw_wc_qbo_sync_access_token;?>">
							  </div>
							  
							  <div class="">
							  </br></br>
								 <a href="javascript:void(0);" onclick="javascript:document.getElementById('lkqc_atls_form').submit();" class="CmnBtn">Save changes</a>
							  </div>
							  <?php wp_nonce_field( 'myworks_wc_qbo_save_access_token', 'update_access_token' ); ?>
						  </form>
						  <?php endif;?>
						  
					   </div>
					   <div class="licence-list">
						  <ul>
							 <li class="current">
								<div class="left-status">
								   Status
								</div>
								<div class="right-status">
								   <?php echo (isset($ldfcpv['status']))?$ldfcpv['status']:''?>
								</div>
							 </li>
							 <li>
								<div class="left-status">
								   Plan
								</div>
								<div class="right-status">
								   <?php echo (isset($ldfcpv['plan']))?$ldfcpv['plan']:''?>
								</div>
							 </li>
							 <li>
								<div class="left-status">
								   Next Due Date
								</div>
								<div class="right-status">
								  <?php echo (isset($ldfcpv['nextduedate']) && !empty($ldfcpv['nextduedate']) && $ldfcpv['nextduedate'] != '0000-00-00')?date('M j, Y',strtotime($ldfcpv['nextduedate'])):''?>
								</div>
							 </li>
							 <li>
								<div class="left-status">
								   Billing Cycle
								</div>
								<div class="right-status">
								  <?php echo (isset($ldfcpv['billingcycle']))?$ldfcpv['billingcycle']:''?>
								</div>
							 </li>
							 <?php if(!$MSQS_QL->is_plg_lc_p_sr()):?>
							 <li>
								<div class="left-status">
								   Monthly Orders
								</div>
								<div class="right-status">
								  <?php echo (int) $MSQS_QL->get_osl_sm_val();?> of <?php echo (int) $MSQS_QL->get_osl_lp_count();?>
								</div>
							 </li>
							 <?php endif;?>
						  </ul>
						  
						  
					   </div>
					</div>
					<?php  if($MSQS_QL->is_connected()):?>
					<div class="quick-book">
					   <div class="quick-pdng">
						  <h3>Manage QuickBooks Connection</h3>
						  <p>You're already connected to QuickBooks, you can manage your connection here.</p>
						  <div class="Connect-now">
							 <a  target="_blank" href="<?php echo $MSQS_QL->get_quickbooks_connection_dashboard_url();?>/clientarea.php?action=productdetails&id=<?php echo (int) $MSQS_QL->get_option('mw_wc_qbo_sync_service_id');?>" class="CmnBtn">Manage Connection</a>
						  </div>
					   </div>
					</div>
					
				 <?php else:?>
					<div class="quick-book">
					   <div class="quick-pdng">
						  <h3>Connect to QuickBooks</h3>
						  <p>Your license key is active, click here to connect to your QuickBooks Online account.</p>
						  <div class="Connect-now">
							 <a  target="_blank" href="<?php echo $MSQS_QL->get_quickbooks_connection_dashboard_url();?>/clientarea.php?action=productdetails&id=<?php echo (int) $MSQS_QL->get_option('mw_wc_qbo_sync_service_id');?>" class="CmnBtn">Connect</a>
						  </div>
					   </div>
					</div> 
					 
					 
			<?php endif;?>					
					
				 </div>				 
			  </div>
		   </div>
		   
		   <div class="cols-md-4 right-row">
			  <div class="side-bar">
				 <div class="i-img">
					<img src="<?php echo plugins_url( $plugin_folder_name.'/admin/image/i.png' ) ?>" class="img-res">
				 </div>
				 <h3>Connection Info</h3>
				 <?php  if($MSQS_QL->is_connected()):?>
				 <?php if($quickbooks_CompanyInfo):?>
				 <div class="usa-block">
					<h3>
						<?php
						if($quickbooks_CompanyInfo->countCompanyName()){
							print($quickbooks_CompanyInfo->getCompanyName());
						}	 
						?>
						
						<?php
						if($quickbooks_CompanyInfo->countCountry()){
							print('['.$quickbooks_CompanyInfo->getCountry().']');
						}	 
						?>
					</h3>
					<span>
						<?php
						if($quickbooks_CompanyInfo->countEmail()){		
							if(is_object($quickbooks_CompanyInfo->getEmail()) && $quickbooks_CompanyInfo->getEmail()->countAddress()){
								print($quickbooks_CompanyInfo->getEmail()->getAddress());
							}
						}	
						?>
					</span>
				 </div>
				 <?php endif;?>
				 
				 <div class="licence-list">
					<ul>
					   <li class="current">
						  <div class="left-status">
							 Status
						  </div>
						  <div class="right-status">
							<?php echo $local_connection_status_txt;?>
						  </div>
					   </li>
					   <li>
						  <div class="left-status">
							 Realm
						  </div>
						  <div class="right-status">
							 <?php print($realm); ?>
						  </div>
					   </li>
					</ul>
				 </div>
				 <?php else:?>
				 <div class="licence-list">
					<ul>
					   <li class="current">
						  <div class="left-status">
							 Status
						  </div>
						  <div class="right-status">
							<?php echo $local_connection_status_txt;?>
						  </div>
					   </li>					   
					</ul>
				 </div>
				 <?php endif;?>
				 
				 <?php if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_is_oauth2_qb_connection_fa')):?>
				 <div class="refresh-key">
					<img src="<?php echo plugins_url( $plugin_folder_name.'/admin/image/side-bar-refresh-key.png' ) ?>" class="img-res">
					<span><a id="mwqs_dqcclk" title="<?php echo __('Refresh QuickBooks Connection Status.','mw_wc_qbo_sync');?>" href="javascript:void(0);">Refresh Connection</a></span>
					<?php wp_nonce_field( 'myworks_wc_qbo_sync_del_conn_cred_local_key', 'del_conn_cred_local_key' );?>
				 </div>
				 <?php endif;?>
			  </div>
			  <div class="side-bar dflt">
				  <h3>Check out our Documentation!</h3>
				 <div class="usa-block">
					<p>Need help setting up or starting to use the sync? Check out our helpful documentation and videos to get up and running easily! 
					<br>
					</p>
					<div class="Connect-now">
							 <a href="https://support.myworks.software/woocommerce-sync-for-quickbooks-online" class="CmnBtn">Documentation</a>
						  </div>
				 </div>
				 
			  </div>
			  <div class="side-bar dflt">
				  <h3>Still need help? Easily open a ticket.</h3>
				 <div class="usa-block">
					<p>Have a question and can't find an answer in our documentation? Our helpful support team is always online via support ticket to give you a hand.</p>
					<div class="Connect-now">
							 <a href="<?php echo $MSQS_QL->get_quickbooks_connection_dashboard_url();?>/submitticket.php?step=2&deptid=2" target="_blank" class="CmnBtn">Open Ticket</a>
						  </div>
				 </div>
				 
			  </div>
		   </div>
		</div>
		
		<script>
		jQuery(document).ready(function($){
			$('#mwqs_dllk').click(function(){
				if(confirm('This will refresh your license status.')){
					jQuery(this).html('Loading...');
					var data = {
						"action": 'mw_wc_qbo_sync_del_license_local_key',
						"del_license_local_key": jQuery('#del_license_local_key').val(),
					};
					jQuery.ajax({
					   type: "POST",
					   url: ajaxurl,
					   data: data,
					   cache:  false ,
					   //datatype: "json",
					   success: function(result){
						   if(result!=0 && result!=''){					
							location.reload();
						   }else{
							 jQuery('#mwqs_dllk').html('Error!');					 
						   }				  
					   },
					   error: function(result) { 		
							jQuery('#mwqs_dllk').html('Error!');
					   }
					});
				}		
			});
			
			<?php if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_is_oauth2_qb_connection_fa')):?>
			$('#mwqs_dqcclk').click(function(){
				if(confirm('Are you sure, you want to refresh local qbo connection data?')){
					jQuery(this).html('Loading...');
					var data = {
						"action": 'mw_wc_qbo_sync_del_conn_cred_local_key',
						"del_conn_cred_local_key": jQuery('#del_conn_cred_local_key').val(),
					};
					jQuery.ajax({
					   type: "POST",
					   url: ajaxurl,
					   data: data,
					   cache:  false ,
					   //datatype: "json",
					   success: function(result){
						   if(result!=0 && result!=''){					
							location.reload();
						   }else{
							 jQuery('#mwqs_dqcclk').html('Error!');					 
						   }				  
					   },
					   error: function(result) { 		
							jQuery('#mwqs_dqcclk').html('Error!');
					   }
					});
				}		
			});
			<?php endif;?>
			
		});
		</script>
		
		<?php endif;?>
		
		<?php
			$invalid_license_msg = '<strong>Please enter your MyWorks license key to continue.</strong> </br><a target="_blank" href="https://myworks.software/pricing?utm_source=plugin_link&utm_medium=link&utm_campaign=plugin_link">Don\'t have one? Sign up for a MyWorks account here.</a></br></br>';
			
			if($MWQS_OF->get_license_status()=='Invalid'){
				$invalid_license_msg = 'Please enter a valid MyWorks license key in order to continue.
				</br></br>
				<strong>Installing for the first time?</strong> Great! Simply enter your key below.
				</br>
				<strong>Moving sites?</strong> Don\'t forget to re-issue your license with us in your account.
				</br></br>';
			}
			
			if($MWQS_OF->get_license_status()=='Expired'){
				$invalid_license_msg = 'Your license key is <strong>expired</strong>. Please renew your license with us or enter a valid license key in order to continue to use the plugin.';
			}
			
			if($MWQS_OF->get_license_status()=='Suspended'){
				$invalid_license_msg = 'Your license key is <strong>suspended</strong>. Please either upgrade to a paid license by clicking the <strong>Upgrade Now</strong> button above, or enter a valid license key in order to continue.';
			}
		?>
		
		<?php if($MWQS_OF->get_license_status()!='Active'):?>
		<div class="qbd_input_license">
			<p><?php echo $invalid_license_msg;?></p>
			<div class="mwqs_conection_license_check">
				<form method="post" id="myworks_wc_qbo_sync_check_license">
					<label for ="mw_wc_qbo_sync_license">License Key: </label>
					<input type="text" placeholder = "QBOSync-000000000000000000" name="mw_wc_qbo_sync_license" id="mw_wc_qbo_sync_license" value="<?php echo $mw_wc_qbo_sync_license;?>">
					 <?php wp_nonce_field( 'myworks_wc_qbo_sync_check_license', 'check_plugin_license' ); ?>
					<input size="30" type="submit" value="Enter" class="button button-primary">
					<span id="mwqs_license_chk_loader" style="visibility:hidden;">
						<img src="<?php echo esc_url( plugins_url( 'image/ajax-loader.gif', dirname(__FILE__) ) );?>" alt="Loading..." />
					</span>
				</form>
			</div>
		</div>
		<?php endif;?>
	 </div>
</div>