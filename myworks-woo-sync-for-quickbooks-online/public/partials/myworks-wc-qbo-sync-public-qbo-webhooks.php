<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://myworks.design/software/wordpress/woocommerce/myworks-wc-qbo-sync
 * @since      1.0.0
 *
 * @package    MyWorks_WC_QBO_Sync
 * @subpackage MyWorks_WC_QBO_Sync/public/partials
 */
?>
<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $MWQS_OF;
global $MSQS_QL;

/*
if($MSQS_QL->is_plg_lc_p_l()){
	exit(0);
}
*/

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
}

//$MSQS_QL->save_log('Webhook Cron Request Debug','Received webhook cron request from server','Webhook',3);
$req_headers = apache_request_headers();
//echo '<pre>';print_r($req_headers);echo '</pre>';

$mw_wc_qbo_sync_license = $MSQS_QL->get_option('mw_wc_qbo_sync_license');
$mw_wc_qbo_sync_access_token = $MSQS_QL->get_option('mw_wc_qbo_sync_access_token');

$dashboard_domain = $MSQS_QL->get_dashboard_domain();
$Local_dir = $MWQS_OF->get_plugin_connection_dir();
$Local_ip = $MWQS_OF->get_plugin_ip();

$req_host = (isset($req_headers['Servername']))?trim($req_headers['Servername']):'';
$Remote_LicenseKey = (isset($req_headers['Licensekey']))?trim($req_headers['Licensekey']):'';
$Remote_AccessToken = (isset($req_headers['Accesstoken']))?trim($req_headers['Accesstoken']):'';

$Dirpath = (isset($req_headers['Dirpath']))?trim($req_headers['Dirpath']):'';
$Userip = (isset($req_headers['Userip']))?trim($req_headers['Userip']):'';

//New
if(empty($req_host)){$req_host = (isset($req_headers[strtoupper('Servername')]))?trim($req_headers[strtoupper('Servername')]):'';}
if(empty($Remote_LicenseKey)){$Remote_LicenseKey = (isset($req_headers[strtoupper('Licensekey')]))?trim($req_headers[strtoupper('Licensekey')]):'';}
if(empty($Remote_AccessToken)){$Remote_AccessToken = (isset($req_headers[strtoupper('Accesstoken')]))?trim($req_headers[strtoupper('Accesstoken')]):'';}
if(empty($Dirpath)){$Dirpath = (isset($req_headers[strtoupper('Dirpath')]))?trim($req_headers[strtoupper('Dirpath')]):'';}
if(empty($Userip)){$Userip = (isset($req_headers[strtoupper('Userip')]))?trim($req_headers[strtoupper('Userip')]):'';}

$extra_validate = false;
if(!empty($mw_wc_qbo_sync_access_token) && $mw_wc_qbo_sync_access_token===$Remote_AccessToken){
// $Dirpath!='' && $Local_ip!='' &&  && $Dirpath==$Local_dir && $Userip==$Local_ip
	//$extra_validate = true;
}

$extra_validate = true;

$return = array();
$return['success'] = 0;
$return['message'] = 'Not Authorized';

// && $dashboard_domain===$req_host
if($Remote_LicenseKey!='' && $mw_wc_qbo_sync_license===$Remote_LicenseKey && $extra_validate){
	#$MSQS_QL->save_log('Webhook Request','Received webhook request from server','Webhook',2);
	$requestBody = json_decode(file_get_contents("php://input"));
	if(isset($requestBody->realmId)){
		$remote_realmId = $requestBody->realmId;
		$local_realmId = $MSQS_QL->getRealm();
		if($remote_realmId==$local_realmId){
			$return['message'] = 'Webhooks Method Executed';
			$return['success'] = 1;
			//Webhook function
			$entities = $requestBody->entities;
			#$MSQS_QL->save_log('Webhook Request Items',json_encode($entities),'Webhook',2);
			$MSQS_QL->Process_QuickBooks_WebHooks_Request($entities);
		}
	}
}

header('Content-Type: application/json');
echo json_encode($return);
exit(0);