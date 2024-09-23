<?php
global $MSQS_QL;
$isConnectionPage = (isset($_GET['page']) && $_GET['page'] == 'myworks-wc-qbo-sync-connection')?true:false;
if(!$isConnectionPage && !(int) $MSQS_QL->get_option('mw_wc_qbo_sync_qbo_is_connected')):
	echo '<link href="'.esc_url( plugins_url( "css/dash-board-sec.css", dirname(__FILE__) ) ).'" rel="stylesheet" type="text/css">';
?>
<div class="mw-qbo-sync-welcome">
	<div class="mw-qbo-sync-title">
		<img width="225"  alt="mw-qbo-sync" src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/mwd-logo-wb.png' ) ?>" class="mw-qbo-sync-logo"><small><sup>v<?php echo MyWorks_WC_QBO_Sync_Admin::return_plugin_version() ?></sup></small>
		<span class="baseline" style="font-size:25px">Connect to QuickBooks Online to begin syncing!</span>
	</div>

	<div class="mw-qbo-sync-settings-section">
		<p>Connect to your QuickBooks Online account to begin using MyWorks!</p>
		<a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=myworks-wc-qbo-sync-connection') ?>" id="mw-qbo-sync-signup">Visit MyWorks Sync > Connection to Connect</a>
	</div>
</div>
<?php endif;?>