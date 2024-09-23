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

$req_headers = apache_request_headers();
//echo '<pre>';print_r($req_headers);echo '</pre>';
$mw_wc_qbo_sync_license = $MSQS_QL->get_option('mw_wc_qbo_sync_license');

$dashboard_domain = $MSQS_QL->get_dashboard_domain();
//$req_host = $req_headers['Host'];
$req_host = (isset($req_headers['Servername']))?trim($req_headers['Servername']):'';

$Remote_LicenseKey = (isset($req_headers['Licensekey']))?trim($req_headers['Licensekey']):'';

$Dirpath = (isset($req_headers['Dirpath']))?trim($req_headers['Dirpath']):'';
$Userip = (isset($req_headers['Userip']))?trim($req_headers['Userip']):'';

//New
if(empty($Remote_LicenseKey)){$Remote_LicenseKey = (isset($req_headers[strtoupper('Licensekey')]))?trim($req_headers[strtoupper('Licensekey')]):'';}
if(empty($Dirpath)){$Dirpath = (isset($req_headers[strtoupper('Dirpath')]))?trim($req_headers[strtoupper('Dirpath')]):'';}
if(empty($Userip)){$Userip = (isset($req_headers[strtoupper('Userip')]))?trim($req_headers[strtoupper('Userip')]):'';}

$Local_dir = $MWQS_OF->get_plugin_connection_dir();
$Local_ip = $MWQS_OF->get_plugin_ip();

$extra_validate = false;
if(!empty($mw_wc_qbo_sync_license)){ // $Dirpath!='' && $Local_ip!=''  && $Dirpath==$Local_dir && $Userip==$Local_ip
	$extra_validate = true;
}

$return = array();
$return['success'] = 0;
$return['message'] = 'Not Authorized';

// && $dashboard_domain===$req_host
if($Remote_LicenseKey!='' && $mw_wc_qbo_sync_license===$Remote_LicenseKey && $extra_validate){	
	$AccessToken = (isset($_POST['AccessToken']))?trim($_POST['AccessToken']):'';
	$Update_Token =  (isset($_POST['Update_Token']))?(int) trim($_POST['Update_Token']):0;
	//echo '<pre>';print_r($_POST);echo '</pre>';
	$AccessToken   = $MSQS_QL->sanitize( $AccessToken );	
	
	if($AccessToken!=''){
		$return['success'] = 1;		
		update_option( 'mw_wc_qbo_sync_access_token', $AccessToken );		
		$return['message'] = 'Success';
	}else{
		if($Update_Token){
			$return['success'] = 1;
			update_option( 'mw_wc_qbo_sync_access_token', $AccessToken );
			$return['message'] = 'Success';
		}else{
			$return['message'] = 'Access Token Empty';
		}		
	}	
}

header('Content-Type: application/json');
echo json_encode($return);
exit(0);