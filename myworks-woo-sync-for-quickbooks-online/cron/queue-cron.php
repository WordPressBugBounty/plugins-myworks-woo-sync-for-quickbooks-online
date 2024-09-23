<?php
/**/
if(php_sapi_name()!='cli' && php_sapi_name()!='cgi-fcgi' || isset($_SERVER['REMOTE_ADDR'])){	
	echo '<html>';
	echo '<head><title>MyWorks Sync Cron</title></head>';
	echo '<body><h2>ERROR!</h2><p>You can run this page only in CLI.</p></body>';
	echo '</html>';
	die;
}

define( 'WP_USE_THEMES', false );
require_once dirname ( __FILE__ ) .  '/../../../../wp-load.php';

global $MSQS_QL;
if(isset($MSQS_QL) && is_object($MSQS_QL) && !empty($MSQS_QL)){
	global $MSQS_AD;	
	if(isset($MSQS_AD) && is_object($MSQS_AD) && !empty($MSQS_AD)){
		$MSQS_AD->mw_qbo_sync_queue_cron_function_execute(); #queue		
	}
}