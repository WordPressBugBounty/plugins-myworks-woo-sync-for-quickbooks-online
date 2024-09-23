<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$page_url = 'admin.php?page=myworks-wc-qbo-sync-connection&latest=1';

if ( ! empty( $_POST['update_access_token'] ) && check_admin_referer( 'myworks_wc_qbo_save_access_token', 'update_access_token' ) ) {
	$mw_wc_qbo_sync_access_token_update = $MSQS_QL->var_p('mw_wc_qbo_sync_access_token');
	$mw_wc_qbo_sync_access_token_update = $MSQS_QL->sanitize($mw_wc_qbo_sync_access_token_update);
	
	if(!empty($mw_wc_qbo_sync_access_token_update)){
		update_option( 'mw_wc_qbo_sync_access_token', $mw_wc_qbo_sync_access_token_update );	
		if($MSQS_QL->option_checked('mw_wc_qbo_sync_session_cn_ls_chk') && $mw_wc_qbo_sync_access_token_update!=$MSQS_QL->get_option('mw_wc_qbo_sync_access_token')){
			$MSQS_QL->set_session_val('new_access_token_rts',1);
		}
		$MSQS_QL->redirect($page_url);
	}
}

$mw_wc_qbo_sync_license = $MSQS_QL->get_option('mw_wc_qbo_sync_license','');
$mw_wc_qbo_sync_localkey = $MSQS_QL->get_option('mw_wc_qbo_sync_localkey','');

$is_license_activated = $MWQS_OF->is_valid_license($mw_wc_qbo_sync_license,$mw_wc_qbo_sync_localkey);
$license_status = $MWQS_OF->get_license_status();
?>

<div class="mw_wc_qbo_sync_container container conect-outer">
	<?php if(!$is_license_activated):?>
	
	<div class="connection_contaner">
		<div class="connection_panel">
			<div class="connection_info connect">
				<h2>Welcome!</h2>
				<h3>MyWorks Sync <span>QuickBooks Online</span></h3>
				<div class="icon_panel" id="mwlc_ip">
					<img id="mwlc_ip_img" src="<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic1.png');?>" class="img-responsive" alt="">
					<h3 id="mwlc_ip_h3">Activate Your Licence</h3>
					<p id="mwlc_ip_p" class="mwsnc_lrm"></p>
				</div>
			</div>
			<div class="side_panel">
				<div class="heading_panel">
					<h2>License Key &amp; Access Token</h2>
					<img src="<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-logo.png');?>" class="img-responsive" alt="">
				</div>
				<div class="form_section">
					<div class="form_info">
						<label title="License Key" for="mw_wc_qbo_sync_license">License Key:</label>
						<input class="form-control" type="text" id="mw_wc_qbo_sync_license" value="" placeholder="License Key">
					</div>
					<div class="form_info">
						<label title="Access Token" for="mw_wc_qbo_sync_access_token">Access Token:</label>
						<input class="form-control" type="text" id="mw_wc_qbo_sync_access_token" value="" placeholder="Access Token">
					</div>
					<input type="button" id="mw_wc_llc_nlcb" class="btn" value="ENTER">
					 <?php wp_nonce_field( 'mw_wc_qbo_sync_check_license_latest', 'check_plugin_license_latest' ); ?>
					<p class="last_info">
					<!--
					<img style="display:none;" id="lc_lfp_img" src="<?php //echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-icon2.png');?>" class="img-responsive" alt="">
					-->
					<i  style="display:none;" class="fa fa-refresh fa-spin" id="lc_lfp_img"></i>
					<span style="display:none;" id="lc_lfp_txt"><span>
					</p>
				</div>
			</div>
		</div>
	</div>
	
	<?php else:?>
	
	<?php
		$is_qbo_connected = $MSQS_QL->is_connected();
		$quickbooks_CompanyInfo = false;
		
		if($is_qbo_connected){
			$Context = $MSQS_QL->getContext();
			$realm = $MSQS_QL->getRealm();
			
			$CompanyInfoService = new QuickBooks_IPP_Service_CompanyInfo();
			$quickbooks_CompanyInfo = $CompanyInfoService->get($Context, $realm);
		}
	?>
	
	<div class="connection_contaner">
		<div class="connection_panel second_step">
			<div class="connection_info connect">
				<h2>Welcome!</h2>
				<h3>MyWorks Sync <span>QuickBooks Online</span></h3>
				<div class="icon_panel">
					<img title="License Activated" src="<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic2.png');?>" class="img-responsive" alt="">			
				</div>
				<div class="form_section">
					<form method="post" action="<?php echo $page_url;?>">
						<div class="form_info">
							<label for="mw_wc_qbo_sync_license" title="License Key">License Key:</label>
							<p title="To update your license key, deactivate and re-activate the plugin. All your settings and mappings will be saved." id="mw_wc_qbo_sync_license"><?php echo $mw_wc_qbo_sync_license;?></p>
							<a id="mwqs_dllk" title="<?php echo __('Refresh your license information','mw_wc_qbo_sync');?>" href="javascript:void(0)" onclick="">
								<i class="fa fa-refresh"></i>
							</a>
							<?php wp_nonce_field( 'myworks_wc_qbo_sync_del_license_local_key', 'del_license_local_key' );?>
						</div>
						<div class="form_info">
							<label title="Access Token" for="mw_wc_qbo_sync_access_token">Access Token:</label>
							<input class="form-control" type="text" name="mw_wc_qbo_sync_access_token" id="mw_wc_qbo_sync_access_token" value="<?php echo $MSQS_QL->get_option('mw_wc_qbo_sync_access_token');?>" placeholder="Access Token">
						</div>
						<input type="submit" class="btn" id="lcp2sb" value="Update" disabled>
						<?php wp_nonce_field( 'myworks_wc_qbo_save_access_token', 'update_access_token' ); ?>
					</form>
				</div>
			</div>
			<div class="side_panel">
				<div class="heading_panel">				
					<img src="<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-icon3.png');?>" class="img-responsive" alt="">
					<h2>QuickBooks Connection Info</h2>
					<?php if($is_qbo_connected):?>
					<img src="<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connect-icon.png');?>" class="img-responsive" alt="Connected">
					<?php else:?>
					<img src="<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/not-connected.png');?>" class="img-responsive" alt="Not Connected">
					<?php endif;?>
					
					<a id="mwqs_dqcclk" title="<?php echo __('Refresh QuickBooks Connection Status','mw_wc_qbo_sync');?>" href="javascript:void(0)" onclick="">
						<i class="fa fa-refresh"></i>
					</a>
					<?php wp_nonce_field( 'myworks_wc_qbo_sync_del_conn_cred_local_key', 'del_conn_cred_local_key' );?>
				</div>
				
				<?php if($is_qbo_connected && $quickbooks_CompanyInfo):?>
				
				<div class="quick_book_info">
					<p>
						<span>Realm:</span>
						<?php echo $realm; ?>
					</p>
					
					<p>
						<span>Company:</span>					
						<?php
						if($quickbooks_CompanyInfo->countCompanyName()){
							print($quickbooks_CompanyInfo->getCompanyName());
						}	 
						?>
					</p>
					
					<p>
						<span>Email:</span>					
						<?php
						if($quickbooks_CompanyInfo->countEmail()){		
							if(is_object($quickbooks_CompanyInfo->getEmail()) && $quickbooks_CompanyInfo->getEmail()->countAddress()){
								print($quickbooks_CompanyInfo->getEmail()->getAddress());
							}
						}	
						?>
					</p>
					
					<p>
						<span>Country:</span>					
						<?php
						if($quickbooks_CompanyInfo->countCountry()){
							print($quickbooks_CompanyInfo->getCountry());
						}	 
						?>
					</p>				
				</div>
				
				<?php else:?>
				
				<div class="quick_book_info cinfo_error">
					<p>QuickBooks online company info not found.</p>
				</div>
				
				<?php endif;?>
			</div>
		</div>
	</div>
	
	<?php endif;?>
</div>


<?php if(!$is_license_activated):?>
<script>
	jQuery(document).ready(function($){
		$('#mw_wc_llc_nlcb').click(function(){			
			var mw_wc_qbo_sync_license = $('#mw_wc_qbo_sync_license').val();
			mw_wc_qbo_sync_license = $.trim(mw_wc_qbo_sync_license);
			
			var mw_wc_qbo_sync_access_token = $('#mw_wc_qbo_sync_access_token').val();
			mw_wc_qbo_sync_access_token = $.trim(mw_wc_qbo_sync_access_token);
			
			var check_plugin_license_latest = $('#check_plugin_license_latest').val();
			
			if(mw_wc_qbo_sync_license!='' && mw_wc_qbo_sync_access_token!=''){
				$('#lc_lfp_img').show();
				$('#lc_lfp_txt').html('Loading...').show();
				
				$('#mwlc_ip').removeClass('lc_error');
				$('#mwlc_ip_img').attr('src','<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic1.png');?>');
				$('#mwlc_ip_h3').text('Activate Your Licence');
				$('#mwlc_ip_p').text('');
				
				var data = {
					"action": 'myworks_wc_qbo_sync_check_license_latest',
					"mw_wc_qbo_sync_license": mw_wc_qbo_sync_license,
					"mw_wc_qbo_sync_access_token": mw_wc_qbo_sync_access_token,
					"check_plugin_license_latest": check_plugin_license_latest,
				};
				
				data = $.param(data);
				
				$.ajax({
					type: "POST",
					url: ajaxurl,
					data: data,
					cache:  false ,
					datatype: "json",
					success: function(result){
						 if(result!='' && result!='0'){
							try{
								$('#lc_lfp_img').hide();
								$('#lc_lfp_txt').html('').hide();
								
								result = jQuery.parseJSON(result);
								if(result.is_valid_license == 1){
									$('#lc_lfp_img').show();
									$('#lc_lfp_txt').html('Reloading Page...').show();
									
									$('#mwlc_ip').addClass('lc_success');
									$('#mwlc_ip_img').attr('src','<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic3.png');?>');
									$('#mwlc_ip_h3').text('License Key Activated');
									$('#mwlc_ip_p').text('');
									//location.reload();
									setTimeout(function(){ location.reload(); }, 2000);
								}else{
									$('#mwlc_ip').addClass('lc_error');
									$('#mwlc_ip_img').attr('src','<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic-cross.png');?>');
									$('#mwlc_ip_h3').text('Invalid License Key');
									$('#mwlc_ip_p').text(result.message);
								}
								
							}catch(err){
								//err.message
								$('#lc_lfp_img').hide();
								$('#lc_lfp_txt').html('').hide();
								
								$('#mwlc_ip').addClass('lc_error');
								$('#mwlc_ip_img').attr('src','<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic-cross.png');?>');
								$('#mwlc_ip_h3').text('Invalid Response');
								$('#mwlc_ip_p').text('Invalid response format.');
							}
						 }else{
							$('#lc_lfp_img').hide();
							$('#lc_lfp_txt').html('').hide();
							
							$('#mwlc_ip').addClass('lc_error');
							$('#mwlc_ip_img').attr('src','<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic-cross.png');?>');
							//?t=Date.now()
							$('#mwlc_ip_h3').text('Invalid Response');
							$('#mwlc_ip_p').text('No response received from server.');
						 }
					},
					error: function(response) {
						$('#lc_lfp_img').hide();
						$('#lc_lfp_txt').html('').hide();
						
						$('#mwlc_ip').addClass('lc_error');
						$('#mwlc_ip_img').attr('src','<?php echo plugins_url('myworks-woo-sync-for-quickbooks-online/admin/image/connection-pic-cross.png');?>');
						$('#mwlc_ip_h3').text('Invalid Response');
						$('#mwlc_ip_p').text('Something is wrong, plesae try again later.');
					}
				});
				
			}else{
				$('#lc_lfp_txt').html('Plesae enter license key and access token.').show();
			}
		});
	});
</script>
<?php else:?>
<script>
	jQuery(document).ready(function($){
		$('#lcp2sb').removeAttr('disabled');
		
		
		$('#mw_wc_qbo_sync_access_token').bind('input', function(){
			var mw_wc_qbo_sync_access_token = $('#mw_wc_qbo_sync_access_token').val();
			mw_wc_qbo_sync_access_token = $.trim(mw_wc_qbo_sync_access_token);
			if(mw_wc_qbo_sync_access_token ==''){
				$('#lcp2sb').attr('disabled','');
			}else{
				$('#lcp2sb').removeAttr('disabled');
			}
		});
		
		$("#mw_wc_qbo_sync_access_token").on('paste keyup keydown keypress blur change',function(e){
			var mw_wc_qbo_sync_access_token = $('#mw_wc_qbo_sync_access_token').val();
			mw_wc_qbo_sync_access_token = $.trim(mw_wc_qbo_sync_access_token);
			if(mw_wc_qbo_sync_access_token ==''){
				$('#lcp2sb').attr('disabled','');
			}else{
				$('#lcp2sb').removeAttr('disabled');
			}
		});
		
		$('#mwqs_dllk').click(function(){
			if(confirm('Are you sure, you want to refresh local license data?')){
				jQuery(this).html('<i class="fa fa-refresh fa-spin"></i>');
				
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
						 jQuery('#mwqs_dllk').html('<i title="Invalid Response - Please try again." class="fa fa-refresh red-text"></i>');					 
					   }				  
				   },
				   error: function(result) { 		
						jQuery('#mwqs_dllk').html('<i title="Error Occurred - Please try again." class="fa fa-refresh red-text"></i>');
				   }
				});
			}		
		});
		
		$('#mwqs_dqcclk').click(function(){
			if(confirm('Are you sure, you want to refresh local qbo connection data?')){
				jQuery(this).html('<i class="fa fa-refresh fa-spin"></i>');
				
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
						 jQuery('#mwqs_dqcclk').html('<i title="Invalid Response - Please try again." class="fa fa-refresh red-text"></i>');
					   }				  
				   },
				   error: function(result) { 		
						jQuery('#mwqs_dqcclk').html('<i title="Error Occurred - Please try again." class="fa fa-refresh red-text"></i>');
				   }
				});
			}		
		});
			
	});
</script>
<?php endif;?>