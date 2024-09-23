<?php
if ( ! defined( 'ABSPATH' ) )
     exit;
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://myworks.design/software/wordpress/woocommerce/myworks-wc-qbo-sync
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/admin/partials
 */

 global $MWQS_OF;
 global $MSQS_QL;
 
 $disable_section = true;
 
 if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}
 
 $page_url = 'admin.php?page=myworks-wc-qbo-sync-connection';
 $conn_url = admin_url($page_url);
 $conn_url = base64_encode($conn_url);
 
 if (! empty( $_POST['update_connection_data'] ) && check_admin_referer( 'myworks_wc_qbo_save_connection_data', 'update_connection_data' ) ) {
	
	$mw_wc_qbo_sync_license_update = $MSQS_QL->var_p('mw_wc_qbo_sync_license_update');
	$mw_wc_qbo_sync_license_update = $MSQS_QL->sanitize($mw_wc_qbo_sync_license_update);
	 
	$mw_wc_qbo_sync_connection_number_update = $MSQS_QL->var_p('mw_wc_qbo_sync_connection_number');
	//$mw_wc_qbo_sync_connection_number_update = $MSQS_QL->sanitize($mw_wc_qbo_sync_connection_number_update);
	$mw_wc_qbo_sync_connection_number_update = (int) $mw_wc_qbo_sync_connection_number_update;
	if(!$mw_wc_qbo_sync_connection_number_update){ $mw_wc_qbo_sync_connection_number_update=1;}
	if($mw_wc_qbo_sync_connection_number_update>5){$mw_wc_qbo_sync_connection_number_update=5;}
	 
	 
	$mw_wc_qbo_sync_sandbox_mode_update = $MSQS_QL->var_p('mw_wc_qbo_sync_sandbox_mode');
	if($mw_wc_qbo_sync_sandbox_mode_update!='yes' && $mw_wc_qbo_sync_sandbox_mode_update!='no'){
		$mw_wc_qbo_sync_sandbox_mode_update = 'no';
	}
	
	
	
	//update_option( 'mw_wc_qbo_sync_license', $mw_wc_qbo_sync_license_update );
	if($MSQS_QL->get_option('mw_wc_qbo_sync_license','')!=$mw_wc_qbo_sync_license_update){
		//update_option( 'mw_wc_qbo_sync_localkey', '' );
	}
	
	$mw_wc_qbo_sync_connection_webhook = '';
	if(isset($_POST['mw_wc_qbo_sync_connection_webhook'])){
		$mw_wc_qbo_sync_connection_webhook = 'true';
	}
	
	/*
	update_option( 'mw_wc_qbo_sync_connection_webhook', $mw_wc_qbo_sync_connection_webhook );
	
	update_option( 'mw_wc_qbo_sync_connection_number', $mw_wc_qbo_sync_connection_number_update );	
	update_option( 'mw_wc_qbo_sync_sandbox_mode', $mw_wc_qbo_sync_sandbox_mode_update );
	*/
	
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $mw_wc_qbo_sync_connection_number_update!=$MSQS_QL->get_option('mw_wc_qbo_sync_connection_number')){
		$MSQS_QL->set_session_val('new_con_number_rts',1);
	}
	
	$MSQS_QL->redirect($page_url);
 }
 
 if ( ! empty( $_POST['update_access_token'] ) && check_admin_referer( 'myworks_wc_qbo_save_access_token', 'update_access_token' ) ) {
	$mw_wc_qbo_sync_access_token_update = $MSQS_QL->var_p('mw_wc_qbo_sync_access_token');
	$mw_wc_qbo_sync_access_token_update = $MSQS_QL->sanitize($mw_wc_qbo_sync_access_token_update);
	
	update_option( 'mw_wc_qbo_sync_access_token', $mw_wc_qbo_sync_access_token_update );
	
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $mw_wc_qbo_sync_access_token_update!=$MSQS_QL->get_option('mw_wc_qbo_sync_access_token')){
		$MSQS_QL->set_session_val('new_access_token_rts',1);
	}
	
	$MSQS_QL->redirect($page_url);
 }
 

 $mw_wc_qbo_sync_license = $MSQS_QL->get_option('mw_wc_qbo_sync_license','');
 $mw_wc_qbo_sync_localkey = $MSQS_QL->get_option('mw_wc_qbo_sync_localkey','');
 
 $mw_wc_qbo_sync_sandbox_mode = $MSQS_QL->get_option('mw_wc_qbo_sync_sandbox_mode','');
 $mw_wc_qbo_sync_access_token = $MSQS_QL->get_option('mw_wc_qbo_sync_access_token','');
 
 $mw_wc_qbo_sync_connection_number = $MSQS_QL->get_option('mw_wc_qbo_sync_connection_number','');
  
 $sandbox = ($mw_wc_qbo_sync_sandbox_mode=='yes')?'&sandbox=1':'&sandbox=0';
 
 $wp_plugin_api_url = site_url('index.php?mw_qbo_sync_public_api=1');
 $wp_plugin_api_url = base64_encode($wp_plugin_api_url);
 
 $wp_plugin_qbo_webhook_url = site_url('index.php?mw_qbo_sync_public_qbo_webhooks=1');
 $wp_plugin_qbo_webhook_url = base64_encode($wp_plugin_qbo_webhook_url);
 
 //$enable_webhook = ($MSQS_QL->option_checked('mw_wc_qbo_sync_webhook_enable'))?1:0;
 $enable_webhook = 0;
 if($mw_wc_qbo_sync_connection_number==1 && $MSQS_QL->option_checked('mw_wc_qbo_sync_connection_webhook')){
	 $enable_webhook = 1;
 }
 
 if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_webhook_enable')){
	$enable_webhook = 0;
 }
 
 $extra_connection_params = $MWQS_OF->get_connection_iframe_extra_params();
 
 $local_connection_status_txt = '';
 
 //
 $deposit_ser_cron_data = '';
 if($MSQS_QL->is_connected()){
	 //	
	 $deposit_ser_cron_data = $MSQS_QL->get_dps_cron_ser_str();
	 
	 $Context = $MSQS_QL->getContext();
	 $realm = $MSQS_QL->getRealm();
	 
	 $CompanyInfoService = new QuickBooks_IPP_Service_CompanyInfo();
	 $quickbooks_CompanyInfo = $CompanyInfoService->get($Context, $realm);
	 //$MSQS_QL->_p($quickbooks_CompanyInfo);
	 $local_connection_status_txt = '<h5 style="color:green;">Connected</h5>';	
 }else{
	 $local_connection_status_txt = '<h5 style="color:red;">Not Connected</h5>';
 }
 
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="mw_wc_qbo_sync_container container conect-outer">
<?php if($MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey)):?>
<div class="mwqs_conection_options">
	<h4>
	Step 1:	<?php echo __('Enter License Key','mw_wc_qbo_sync');?>
	</h4>
	
	<form method="post" action="<?php echo $page_url;?>">
		<div class="myworks-wc-qbo-sync-table-responsive">
			<table class="widefat fixed">
				<tr>
					<td width="20%"><label for="mw_wc_qbo_sync_license_update" class="mw_wc_qbo_sync_label">License Key:</label></td>
					<td  width="50%">
					<input class="mw_wc_qbo_sync_input" type="text" name="mw_wc_qbo_sync_license_update" id="mw_wc_qbo_sync_license_update" value="<?php echo $mw_wc_qbo_sync_license; ?>" required="required" disabled="disabled"/>
					&nbsp;<span class="mw_wc_qbo_sync_span"></span>
					
					&nbsp;
					<a id="mwqs_dllk" title="<?php echo __('Refresh your license information','mw_wc_qbo_sync');?>" href="javascript:void(0)" onclick="">
						<i class="fa fa-refresh"></i> Refresh
					</a>
					<?php wp_nonce_field( 'myworks_wc_qbo_sync_del_license_local_key', 'del_license_local_key' );?>
					
					</td>
					<td  width="30%">
						<p class="mw_wc_qbo_sync_paragraph">
						<?php 
						echo __('To update your license key, deactivate and re-activate the plugin. All your settings and mappings will be saved.','mw_wc_qbo_sync');
						?>
						</p>
					</td>
				</tr>
				<!-- REMOVING FOR NEW CONNECTION PROCESS -->
				<?php if(!$disable_section):?>
				<tr class="alternate">
					<td><label for="mw_wc_qbo_sync_connection_number" class="mw_wc_qbo_sync_label">Connection Number:</label></td>
					<td>
					<select class="mw_wc_qbo_sync_input" name="mw_wc_qbo_sync_connection_number" id="mw_wc_qbo_sync_connection_number">
					<?php echo $MSQS_QL->only_option($mw_wc_qbo_sync_connection_number,$MSQS_QL->wc_connection_num());?>
					</select>
					</td>
					<td>
					<p class="mw_wc_qbo_sync_paragraph">
					<?php echo __('The default connection is 1, so only change this if you have more than one license/site connecting to the same QuickBooks company.','mw_wc_qbo_sync');?>
					</p>
					</td>
				</tr>
				
				<?php if($mw_wc_qbo_sync_connection_number==1 && $MSQS_QL->option_checked('mw_wc_qbo_sync_webhook_enable')):?>
				<tr>
					<td><label for="mw_wc_qbo_sync_connection_webhook" class="mw_wc_qbo_sync_label">Accept WebHooks from QuickBooks Online?</label></td>
					<td>
					<input <?php if($MSQS_QL->option_checked('mw_wc_qbo_sync_connection_webhook')){echo 'checked="checked"';}?> type="checkbox" class="mw_wc_qbo_sync_input" name="mw_wc_qbo_sync_connection_webhook" id="mw_wc_qbo_sync_connection_webhook">
					</td>
					<td>
					<p class="mw_wc_qbo_sync_paragraph">
					<?php echo __('Select this box to accept webhooks (real-time sync) from this QuickBooks Online company as well as Connection #1.');?>
					</p>
					</td>
				</tr>
				<?php endif;?>
				
				

				<tr class="alternate">
					<td><label for="mw_wc_qbo_sync_sandbox_mode" class="mw_wc_qbo_sync_label">Sandbox Mode:</label></td>
					<td>
					<select class="mw_wc_qbo_sync_input" name="mw_wc_qbo_sync_sandbox_mode" id="mw_wc_qbo_sync_sandbox_mode">
					<?php echo $MSQS_QL->only_option($mw_wc_qbo_sync_sandbox_mode,$MSQS_QL->no_yes);?>
					</select>
					</td>
					<td>
					<p class="mw_wc_qbo_sync_paragraph">
					<?php echo __('We recommend you set this to NO, unless you have a developer account and are using sandbox.qbo.intuit.com','mw_wc_qbo_sync');?>
					</p>
					</td>
				</tr>			
				<?php endif;?>
				
			
				
			</table>
		</div>
	
	<br />
	
	<div class="mw_wc_qbo_sync_clear"></div>
	
	<?php wp_nonce_field( 'myworks_wc_qbo_save_connection_data', 'update_connection_data' ); ?>

	
	</form>
</div>

<script>
jQuery(document).ready(function($){
	$('#mwqs_dllk').click(function(){
		if(confirm('This will refresh your license status.')){
			jQuery(this).html('<i class="fa fa-refresh"></i> Loading...');
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
					 jQuery('#mwqs_dllk').html('<i class="fa fa-refresh"></i> Error!');					 
				   }				  
			   },
			   error: function(result) { 		
					jQuery('#mwqs_dllk').html('<i class="fa fa-refresh"></i> Error!');
			   }
			});
		}		
	});
	
	$('#mwqs_dqcclk').click(function(){
		if(confirm('Are you sure, you want to refresh local qbo connection data?')){
			jQuery(this).html('<i class="fa fa-refresh"></i> Loading...');
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
					 jQuery('#mwqs_dqcclk').html('<i class="fa fa-refresh"></i> Error!');					 
				   }				  
			   },
			   error: function(result) { 		
					jQuery('#mwqs_dqcclk').html('<i class="fa fa-refresh"></i> Error!');
			   }
			});
		}		
	});
	
});

</script>

<div class="mwqs_conection_frame_cont">
	<h4>
	Step 2:	<?php echo __('Connect to QuickBooks Online','mw_wc_qbo_sync');?>
	</h4>
	<?php if(!$disable_section):?>
	<iframe src="<?php echo $MSQS_QL->get_quickbooks_connection_dashboard_url();?>/wc-qbo-connection.php?iframe=1&connection=<?php echo (int) $mw_wc_qbo_sync_connection_number;?>&wp_plugin_api_url=<?php echo $wp_plugin_api_url;?>&wp_plugin_qbo_webhook_url=<?php echo $wp_plugin_qbo_webhook_url;?>&enable_webhook=<?php echo $enable_webhook;?><?php echo $sandbox;?><?php echo $extra_connection_params;?>&deposit_data=<?php echo $deposit_ser_cron_data;?>&conn_url=<?php echo $conn_url;?>" height="900" width="100%"></iframe>
	
	</br></br>
	<?php endif;?>
	<a target="_blank" href="<?php echo $MSQS_QL->get_quickbooks_connection_dashboard_url();?>/clientarea.php?action=productdetails&id=<?php  MyWorks_WC_QBO_Sync_Admin::mw_wc_qbo_get_service_id(); ?>">
		<input type="submit" style="text-align:center" class="button button-primary button-large mw_wc_qbo_sync_submit" value="Connect" />
	</a>	
</div>


<div class="mwqs_at_cont">
	<h4>
	Step 3:	<?php echo __('Enter Access Token','mw_wc_qbo_sync');?>
	</h4>
	
	<form method="post" action="<?php echo $page_url;?>">
	<table class="widefat fixed">
		<tr>
			<td><label for="mw_wc_qbo_sync_access_token" class="mw_wc_qbo_sync_label">Access Token:</label></td>
			<td>
			<input class="mw_wc_qbo_sync_input" type="text" name="mw_wc_qbo_sync_access_token" id="mw_wc_qbo_sync_access_token" value="<?php echo $mw_wc_qbo_sync_access_token; ?>" required="required"/>
			&nbsp;<span class="mw_wc_qbo_sync_span"></span>
			</td>
			<td>
				<p class="mw_wc_qbo_sync_paragraph">
					<?php echo __('Enter the access token found inside your account with us after connecting to QuickBooks.','mw_wc_qbo_sync');?>
				</p>
			</td>
		</tr>
	</table>
	
	<br />
	
	<div class="mw_wc_qbo_sync_clear"></div>
	
	<?php wp_nonce_field( 'myworks_wc_qbo_save_access_token', 'update_access_token' ); ?>
	<input type="submit" name="mwqs_at_btn" class="button button-primary button-large mw_wc_qbo_sync_submit" value="Save" />
	</form>
</div>

<div class="mwqs_conection_local_info">
	<h4>	
	<?php echo __('QuickBooks Online Connection Status','mw_wc_qbo_sync');?>
	</h4>
	
	<div class="cnctin-infrmtionarea">	
	<?php echo $local_connection_status_txt;?>
	
	<br/>
	<a id="mwqs_dqcclk" title="<?php echo __('Refresh QuickBooks Connection Status','mw_wc_qbo_sync');?>" href="javascript:void(0)" onclick="">
		<i class="fa fa-refresh"></i> Refresh
	</a>
	<?php wp_nonce_field( 'myworks_wc_qbo_sync_del_conn_cred_local_key', 'del_conn_cred_local_key' );?>
	
	<div class="com-add">
	<?php  if($MSQS_QL->is_connected()):?>
	<p>Realm: <?php print($realm); ?></p>
	<?php  if(isset($quickbooks_CompanyInfo) && $quickbooks_CompanyInfo):?>
	<p>Company: 
	<?php
	if($quickbooks_CompanyInfo->countCompanyName()){
		print($quickbooks_CompanyInfo->getCompanyName());
	}	 
	?>
	</p>
	<p>Email: 
	<?php
	if($quickbooks_CompanyInfo->countEmail()){		
		if(is_object($quickbooks_CompanyInfo->getEmail()) && $quickbooks_CompanyInfo->getEmail()->countAddress()){
			print($quickbooks_CompanyInfo->getEmail()->getAddress());
		}
	}	
	?>
	</p>
	<p>Country: 
	<?php
	if($quickbooks_CompanyInfo->countCountry()){
		print($quickbooks_CompanyInfo->getCountry());
	}	 
	?>
	</p>
	<?php else:?>
	<p style="color:red;"><?php echo __('QuickBooks online company info not found.','mw_wc_qbo_sync');?></p>
	<?php endif;?>
	
	<?php endif;?>
	</div>

	<br />
	<b style="font-size:16px;">
	<?php echo __('Click Refresh above to check the latest connection status.','mw_wc_qbo_sync');?>
	</b>
	
	</div>
	
</div>

<?php elseif($MWQS_OF->get_license_status()=='Invalid'):?>
<div class="qbd_input_license"><p><?php echo __('Please enter a valid license key in order to continue.
</br></br>
<strong>Installing for the first time?</strong> Great! Simply enter your key below.
</br>
<strong>Moving sites?</strong> Don\'t forget to re-issue your license with us in your account.
</br></br>','mw_wc_qbo_sync');?></p>
<?php elseif($MWQS_OF->get_license_status()=='Expired'):?>
<div class="qbd_input_license"><p><?php echo __('Your license key is <strong>expired</strong>. Please renew your license with us or enter a valid license key in order to continue to use the plugin.','mw_wc_qbo_sync');?></p>
<?php elseif($MWQS_OF->get_license_status()=='Suspended'):?>
<div class="qbd_input_license"><p><?php echo __('Your license key is <strong>suspended</strong>. Please either upgrade to a paid license by clicking the <strong>Upgrade Now</strong> button above, or enter a valid license key in order to continue.','mw_wc_qbo_sync');?></p>
<?php else:?>
<div class="qbd_input_license"><p><?php echo __('Please enter a valid license key to continue. <a href="https://myworks.software/integrations/sync-woocommerce-quickbooks-online?utm_source=plugin_link&utm_medium=link&utm_campaign=plugin_link">Need one? Sign up for an account here.</a>','mw_wc_qbo_sync');?></br></br></p>
<?php endif;?>

<?php if($MWQS_OF->get_license_status()!='Active'):?>

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