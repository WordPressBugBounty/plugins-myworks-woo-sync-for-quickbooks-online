<?php
global $MSQS_QL;
/**/
global $oqbc_nc;
$oqbc_nc = false;
if(!(int) $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_connected') && $MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_default_settings') && $MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_data_mapped')){
	$oqbc_nc = true;
}
?>
<?php echo '<link href="'.esc_url( plugins_url( "css/dash-board-sec.css", dirname(__FILE__) ) ).'" rel="stylesheet" type="text/css">' ?>
<div class="mw-qbo-sync-welcome">
	<div class="mw-qbo-sync-title">
		<img width="225"  alt="mw-qbo-sync" src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/mwd-logo-wb.png' ) ?>" class="mw-qbo-sync-logo"><small><sup>v<?php echo MyWorks_WC_QBO_Sync_Admin::return_plugin_version() ?></sup></small>
		<span class="baseline" style="font-size:25px">Simply follow the setup steps below to start syncing!</span>
		<?php if((int) $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_connected') && $MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_refreshed') && $MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_default_settings') && $MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_data_mapped')){ ?>
		<a title="Dismiss this notice" class="mw-qbo-sync-notice-dismiss mw-qbo-sync-welcome-remove" href="<?php echo admin_url( 'admin.php?page=myworks-wc-qbo-sync&mw_wc_qbo_sync_qbo_is_init=true') ?>"><span class="dashicons dashicons-dismiss"></span><span class="screen-reader-text">Dismiss this notice</span></a>
		<?php } ?>
	</div>
	<div class="mw-qbo-sync-settings-section">
		<div class="mw-qbo-sync-columns counter">
			<div class="col-1-3 <?php if((int) $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_connected')){ ?>over-layer<?php } ?>">
				<img width="48" height="48" alt="" src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/connecticon.png' ) ?>">
				<div class="mw-qbo-sync-col-content">
					<p class="mw-qbo-sync-col-title">Connect to QuickBooks Online</p>
					<p class="mw-qbo-sync-col-desc">Don't have an QuickBooks account yet? Register for one at quickbooks.intuit.com!</p>
					<p>
						<input type="hidden" value="f453ebb0a5" name="mw-qbo-syncsignupnonce" id="mw-qbo-syncsignupnonce">							<a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=myworks-wc-qbo-sync-connection') ?>" id="mw-qbo-sync-signup">Connect to QuickBooks Online!</a></p>
				</div>
			</div>

			<div class="col-1-3 <?php if($MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_default_settings')){ ?>over-layer<?php } ?>">
				<img width="48" height="48" alt="" src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/configureicon.png' ) ?>">
				<div class="mw-qbo-sync-col-content">
					<p class="mw-qbo-sync-col-title">Save Default Settings</p>
					<p class="mw-qbo-sync-col-desc">Visit MyWorks Sync > Settings to set and save your default settings. Not sure? <a href="https://support.myworks.software/woocommerce-sync-for-quickbooks-online/getting-started/settings" target="_blank">Check out our documentation!</a>.</p>
					<p>
						<input type="hidden" value="a72829cbec" name="mw-qbo-synccheckapikeynonce" id="mw-qbo-synccheckapikeynonce">							<a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=myworks-wc-qbo-sync-settings') ?>" id="mw-qbo-sync-save-api-key">Default Settings</a></p>
				</div>
			</div>
			<div class="col-1-3 <?php if($MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_data_mapped')){ ?>over-layer<?php } ?>">
				<img width="48" height="48" alt="" src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/saveicon.png' ) ?>">
				<div class="mw-qbo-sync-col-content">
					<p class="mw-qbo-sync-col-title">Map Existing Data</p>
					<p class="mw-qbo-sync-col-desc">You're almost finished! You just need to map at least 1 current customer, product, and payment method!<br/><br/>
				    <span class="<?php if($MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_data_mapped_customer')){ ?>tick-image<?php }else{ ?>close-image<?php } ?>">Map 1 Customer</span><br/>
				    <span class="<?php if($MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_data_mapped_product')){ ?>tick-image<?php }else{ ?>close-image<?php } ?>">Map 1 Product </span><br/>
				    <span class="<?php if($MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_data_mapped_payment')){ ?>tick-image<?php }else{ ?>close-image<?php } ?>">Map 1 Payment Method </span>
				    </p>
					<p><a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=myworks-wc-qbo-map') ?>">Go to Mapping</a></p>
				</div>
			</div>
		</div>
	</div>
</div>