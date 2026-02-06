<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$page_url = 'admin.php?page=myworks-wc-qbo-sync-connection&new=1';

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

$mw_wc_qbo_sync_access_token = $MSQS_QL->get_option('mw_wc_qbo_sync_access_token','');

$local_connection_status_txt = '';
if($MSQS_QL->is_connected()){	 
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
<div class="mw_wc_qbo_sync_container container conect-outer">
	<?php if($MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey)):?>
	<div class="mwqs_conection_options">
		<h4><?php echo esc_html__('Configure License Key & Access Token','mw_wc_qbo_sync');?></h4>
		<form method="post" action="<?php echo esc_url($page_url);?>">
			<div class="myworks-wc-qbo-sync-table-responsive">
				<table class="widefat fixed">
					<tr>
						<td width="20%"><label for="mw_wc_qbo_sync_license_update" class="mw_wc_qbo_sync_label">License Key:</label></td>
						<td  width="50%">
						<input class="mw_wc_qbo_sync_input" type="text" name="mw_wc_qbo_sync_license_update" id="mw_wc_qbo_sync_license_update" value="<?php echo esc_attr($mw_wc_qbo_sync_license); ?>" required="required" disabled="disabled"/>
						&nbsp;<span class="mw_wc_qbo_sync_span"></span>
						</td>
						<td  width="30%">
							<p class="mw_wc_qbo_sync_paragraph">
							<?php 
							echo esc_html__('To update your license key, deactivate and re-activate the plugin. All your settings and mappings will be saved.','mw_wc_qbo_sync');
							?>
							</p>
						</td>
					</tr>
					
					<tr>
						<td><label for="mw_wc_qbo_sync_access_token" class="mw_wc_qbo_sync_label">Access Token:</label></td>
						<td>
						<input class="mw_wc_qbo_sync_input" type="text" name="mw_wc_qbo_sync_access_token" id="mw_wc_qbo_sync_access_token" value="<?php echo esc_attr($mw_wc_qbo_sync_access_token); ?>" required="required"/>
						&nbsp;<span class="mw_wc_qbo_sync_span"></span>
						</td>
						<td>
							<p class="mw_wc_qbo_sync_paragraph">
								<?php echo esc_html__('Please refresh the page after adding new access token','mw_wc_qbo_sync');?>
							</p>
						</td>
					</tr>				
				</table>
				
				<br />
				<div class="mw_wc_qbo_sync_clear"></div>
				<?php wp_nonce_field( 'myworks_wc_qbo_save_access_token', 'update_access_token' ); ?>
				<input type="submit" name="mwqs_at_btn" class="button button-primary button-large mw_wc_qbo_sync_submit" value="Save" />
			</div>
		</form>
	</div>
	
	<div class="mwqs_rp_cont">
		<h4><?php echo esc_html__('Refresh Page','mw_wc_qbo_sync');?></h4>
		<button class="button button-primary button-large" onclick="javascript:window.location.reload();">
		<?php echo esc_html__('Reload','mw_wc_qbo_sync');?>
		</button>
	</div>
	
	<div class="mwqs_conection_local_info">
		<h4>	
			<?php echo esc_html__('Local QuickBooks Online Connection Info','mw_wc_qbo_sync');?>
		</h4>
		
		<div class="cnctin-infrmtionarea">	
			<?php echo esc_html($local_connection_status_txt);?>
			<div class="com-add">
			<?php  if($MSQS_QL->is_connected()):?>
			<p>Realm: <?php echo esc_html($realm); ?></p>
			<?php  if(isset($quickbooks_CompanyInfo) && $quickbooks_CompanyInfo):?>
			<p>Company: 
			<?php
			if($quickbooks_CompanyInfo->countCompanyName()){
				echo esc_html($quickbooks_CompanyInfo->getCompanyName());
			}	 
			?>
			</p>
			<p>Email: 
			<?php
			if($quickbooks_CompanyInfo->countEmail()){		
				if(is_object($quickbooks_CompanyInfo->getEmail()) && $quickbooks_CompanyInfo->getEmail()->countAddress()){
					echo esc_html($quickbooks_CompanyInfo->getEmail()->getAddress());
				}
			}	
			?>
			</p>
			<p>Country: 
			<?php
			if($quickbooks_CompanyInfo->countCountry()){
				echo esc_html($quickbooks_CompanyInfo->getCountry());
			}	 
			?>
			</p>
			<?php else:?>
			<p style="color:red;"><?php echo esc_html__('QuickBooks online company info not found.','mw_wc_qbo_sync');?></p>
			<?php endif;?>
			
			<?php endif;?>
			</div>

			<br />
			<b style="font-size:16px;">
			<?php echo esc_html__('Reload the connection page after connect, disconect or reconnect to view real time connection status','mw_wc_qbo_sync');?>
			</b>
		
		</div>
		
	</div>
	<?php endif;?>
	
	<?php if($MWQS_OF->get_license_status()!='Active'):?>
	<div class="qbd_input_license">
		<?php if($MWQS_OF->get_license_status()=='Invalid'):?>
		<p>
			<?php echo esc_html__('Please enter a valid license key in order to continue.','mw_wc_qbo_sync');?>
			<br/><br/>
			<strong><?php echo esc_html__('Installing for the first time?','mw_wc_qbo_sync');?></strong> 
			<?php echo esc_html__('Great! Simply enter your key below.','mw_wc_qbo_sync');?>
			<br/>
			<strong><?php echo esc_html__('Moving sites?','mw_wc_qbo_sync');?></strong> 
			<?php echo esc_html__('Don\'t forget to click Change Site in your account with us.','mw_wc_qbo_sync');?>
			<br/><br/>			
		</p>
		
		<?php elseif($MWQS_OF->get_license_status()=='Expired'):?>
		<p>
			<?php echo esc_html__('Your license key is','mw_wc_qbo_sync');?> 
			<strong><?php echo esc_html__('expired','mw_wc_qbo_sync');?></strong>.
			<?php echo esc_html__('Please renew your license with us or enter a valid license key in order to continue to use the plugin.','mw_wc_qbo_sync');?>
		</p>
		
		<?php elseif($MWQS_OF->get_license_status()=='Suspended'):?>
		<p>
			<?php echo esc_html__('Your license key is','mw_wc_qbo_sync');?> <strong><?php echo esc_html__('suspended','mw_wc_qbo_sync');?></strong>. <?php echo esc_html__('Please either upgrade to a paid license by clicking the','mw_wc_qbo_sync');?> <strong><?php echo esc_html__('Upgrade Now','mw_wc_qbo_sync');?></strong> <?php echo esc_html__('button above, or enter a valid license key in order to continue','mw_wc_qbo_sync');?>.		
		</p>
		
		<?php else:?>
		<p>
			<?php echo esc_html__('Please enter a valid license key in order to continue.','mw_wc_qbo_sync');?>
		</p>
		
		<?php endif;?>
		
		<div class="mwqs_conection_license_check">
			<form method="post" id="myworks_wc_qbo_sync_check_license">
				<label for ="mw_wc_qbo_sync_license">License Key: </label>
				<input type="text" placeholder = "QBOSync-000000000000000000" name="mw_wc_qbo_sync_license" id="mw_wc_qbo_sync_license" value="<?php echo esc_attr($mw_wc_qbo_sync_license);?>">
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