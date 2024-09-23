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
 
 MyWorks_WC_QBO_Sync_Admin::is_trial_version_check();
 global $MWQS_OF;
 global $MSQS_QL;
 
 global $MSQS_AD;
 
 if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	 $MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
 }
 
 if(isset($_GET['debug'])){
	/*
	if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection') && isset($_GET['qbo_connection'])){
		$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);
	}
	*/
	
	$MSQS_QL->debug();
	
	//
	if(isset($_GET['id']) && isset($_GET['d_key']) && (int) $_GET['id'] > 0 && $_GET['d_key'] == 'myworks' ){
		$d_oid = (int) $_GET['id'];
		$MSQS_QL->_p($MSQS_QL->get_wc_order_details_from_order($d_oid,get_post($d_oid)));
	}
	
	/*Other Debug*/
	//$MSQS_AD->mw_wc_qbo_sync_woocommerce_order_refunded(1847);
	
	if(isset($_GET['lkd'])){
		if(isset($_GET['l_key']) && !empty($_GET['l_key'])){
			$l_key = $MSQS_QL->sanitize($_GET['l_key']);
		}else{
			$l_key = $MSQS_QL->get_option('mw_wc_qbo_sync_license');
		}
		
		if(!empty($l_key)){
			$llk = '';$orc = false;
			$u_llk = isset($_GET['u_llk'])?1:0;
			if($u_llk){
				$llk = get_option('mw_wc_qbo_sync_localkey','');
			}else{
				$orc = true;
			}
			
			$lcr = $MWQS_OF->lcf_debug_f($l_key,$llk,true,$orc);
			echo 'License Key: '.$l_key;
			$MSQS_QL->_p($lcr);
		}		
		
	}
	
 }

if(isset($_GET['run_queue_sync'])){	
	$MSQS_AD->mw_qbo_sync_queue_cron_function_execute();
}
 
 $dashboard_graph_period = $MSQS_QL->get_session_val('dashboard_graph_period','month');
 $db_graph = $MSQS_QL->get_log_chart_output($dashboard_graph_period); 
 
 $plugin_version = MyWorks_WC_QBO_Sync_Admin::return_plugin_version();
 ?>
 <div class="qcpp_cnt">
	 <img width="300"  alt="mw-qbo-sync" src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/mwd-logo.png' ) ?>" class="mw-qbo-sync-logo">
 </div>
 
 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.js"></script>
 <!--Graph-->
<div id="mw_wc_qbo_sync_grph_div" style="background:white;">
<div class="page_title">
	<!-- <h3 title="<?php echo $plugin_version;?>"><?php _e( 'Dashboard', 'mw_wc_qbo_sync' );?></h3> -->
	<div class="dashboard_main_buttons">
	<?php wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_mappings', 'clear_all_mappings' ); ?>
	<button title="<?php _e( 'Clear all data from map tables', 'mw_wc_qbo_sync' );?>" id="mwqs_clear_all_mappings"><?php _e( 'Clear All Mappings', 'mw_wc_qbo_sync' );?></button>
	&nbsp;

	<a id="mwqs_refresh_data_from_qbo" target="_blank" href="<?php echo site_url('index.php?mw_qbo_sync_public_quick_refresh=1');?>">
	<button title="<?php _e( 'Refresh your sync to recognize the latest customers and products currently in QuickBooks.', 'mw_wc_qbo_sync' );?>"><?php _e( 'Refresh Customers & Products', 'mw_wc_qbo_sync' );?></button>
	</a>
	<div id="mwqs_dashboard_ajax_loader"></div>
	</div>
</div>

<div id="mw_wc_qbo_sync_grph_div_new">
<?php echo $db_graph;?>
</div>

</div>

<?php 
	$dashboard_status_data = $MSQS_QL->get_dashboard_status_items();
	//$MSQS_QL->_p($dashboard_status_data);
?>
<div class="dash-bottm mwqs_db_status_cont">
     <div class="col-sm3 module-stat">
         <h3>Sync Status</h3>
         <ul>
         	<li>
				<a <?php if(!$MSQS_QL->get_array_isset($dashboard_status_data,'quickbooks_connection',false)){echo ' class="dbst_err"';}?>>
					QuickBooks Connection
				</a>
			</li>
			
			<li>
				<a <?php if(!$MSQS_QL->get_array_isset($dashboard_status_data,'initial_quickbooks_data_loaded',false)){echo ' class="dbst_err"';}?>>
					Initial QuickBooks Data Loaded
				</a>
			</li>
			
			<li>
				<a <?php if(!$MSQS_QL->get_array_isset($dashboard_status_data,'default_setting_saved',false)){echo ' class="dbst_err"';}?>>
					Default Settings Saved
				</a>
			</li>
			
			<li>
				<a <?php if(!$MSQS_QL->get_array_isset($dashboard_status_data,'mapping_active',false)){echo ' class="dbst_err"';}?>>
					Mapping Active
				</a>
			</li>
         </ul>
     </div>
     <div class="col-sm3 mapping-stat map-sta-a">
     	<h3>Mapping Status</h3>
         <ul>
         	<li>
				<a>
					<b>Customers Mapped</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'customer_mapped',0)?></span>
				</a>
			</li>
			
			<li>
				<a>
					<b>Products Mapped</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'product_mapped',0)?></span>
				</a>
			</li>
			
			<li>
				<a>
					<b>Variations Mapped</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'variation_mapped',0)?></span>
				</a>
			</li>
			
			<li>
				<a>
					<b>Gateways Mapped</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'gateway_mapped',0)?></span>
				</a>
			</li>
			
         </ul>  
     </div>
     <div class="col-sm3 mapping-stat sync-a">
     	<h3>WooCommerce Status</h3>
         <ul>         	
			<li>
				<a>
					<b>Customers</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'wc_total_customer',0)?></span>
				</a>
			</li>
			
			<li>
				<a>
					<b>Products</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'wc_total_product',0)?></span>
				</a>
			</li>
			
			<li>
				<a>
					<b>Variations</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'wc_total_variation',0)?></span>
				</a>
			</li>
			
			<li>
				<a>
					<b>Active Gateways</b>
					<span class="right-btnn"><?php echo $MSQS_QL->get_array_isset($dashboard_status_data,'wc_total_gateway',0)?></span>
				</a>
			</li>
         </ul>  
     </div>
</div> 

<?php
//07-04-2017
$logfile_path = MW_QBO_SYNC_LOG_DIR."mw-qbo-sync-log.log";
if($MSQS_QL->option_checked('mw_wc_qbo_sync_err_add_item_obj_into_log_file') && file_exists($logfile_path) && isset($_GET['debug'])):
$logfile = @fopen($logfile_path, "r") or die("Unable to open plugin error log file!");

$log_content = '';
$file_size = filesize($logfile_path);
if($file_size > 0){
	$log_content = @fread($logfile,$file_size);
}
?>
<div style="margin:20px 20px 0px 0px;">
<h5>Debug Add/Update Error Log File</h5>
<textarea readonly="true" style="height:600px;background:white;"><?php echo $log_content;?></textarea>
</div>
<?php
 fclose($logfile);
 endif;
 ?>
 
 <?php
//27-04-2017
$logfile_path = MW_QBO_SYNC_LOG_DIR."mw-qbo-sync-req-res-log.log";
if($MSQS_QL->option_checked('mw_wc_qbo_sync_success_add_item_obj_into_log_file') && file_exists($logfile_path) && isset($_GET['debug'])):
$logfile = @fopen($logfile_path, "r") or die("Unable to open plugin success log file!");

$log_content = '';
$file_size = filesize($logfile_path);
if($file_size > 0){
	$log_content = @fread($logfile,$file_size);
}
?>
<div style="margin:20px 20px 0px 0px;">
<h5>Debug Add/Update Success Log File</h5>
<textarea readonly="true" style="height:600px;background:white;"><?php echo $log_content;?></textarea>
</div>
<?php
 fclose($logfile);
 endif;
 ?>

 <script>
function mw_wc_qbo_sync_refresh_log_chart(period){	
	var data = {
		"action": 'mw_wc_qbo_sync_refresh_log_chart',
		"period": period,
	};
	
	jQuery('#mw_wc_qbo_sync_grph_div_new').css('opacity',0.6);
	jQuery.ajax({
	   type: "POST",
	   url: ajaxurl,
	   data: data,
	   cache:  false ,
	   //datatype: "json",
	   success: function(result){
		   if(result!=0 && result!=''){
			jQuery('#mw_wc_qbo_sync_grph_div_new').html(result);
		   }else{
			 alert('Error!');			 
		   }
		   jQuery('#mw_wc_qbo_sync_grph_div_new').css('opacity',1);
	   },
	   error: function(result) {  
			alert('Error!');
			jQuery('#mw_wc_qbo_sync_grph_div_new').css('opacity',1);
	   }
	});
}

jQuery(document).ready(function($){
	$('#mwqs_refresh_data_from_qbo').click(function(event){
		if(!confirm('<?php echo __('This will update the data in our sync with the latest Customers & Products in your QuickBooks company. No data will be synced at this time.','mw_wc_qbo_sync')?>')){
			event.preventDefault();
		}
	});
	$('#mwqs_clear_all_mappings').click(function(){
		if(confirm('<?php echo __('Are you sure you want to clear your mappings?','mw_wc_qbo_sync')?>')){
			var loading_msg = 'Loading...';
			jQuery('#mwqs_dashboard_ajax_loader').html(loading_msg);
			var data = {
				"action": 'mw_wc_qbo_sync_clear_all_mappings',
				"clear_all_mappings": jQuery('#clear_all_mappings').val(),
			};
			jQuery.ajax({
			   type: "POST",
			   url: ajaxurl,
			   data: data,
			   cache:  false ,
			   //datatype: "json",
			   success: function(result){
				   if(result!=0 && result!=''){
					 //alert('Success');
					 jQuery('#mwqs_dashboard_ajax_loader').html('Success!');
				   }else{
					 //alert('Error!');
					jQuery('#mwqs_dashboard_ajax_loader').html('Error!');
				   }				  
			   },
			   error: function(result) {  
					//alert('Error!');
					jQuery('#mwqs_dashboard_ajax_loader').html('Error!');
			   }
			});
		}
	});
	
	jQuery('#qcpp_btn_id').click(function(){
		if(confirm('<?php echo __('Are you sure you want to change syncing status?','mw_wc_qbo_sync')?>')){
			var loading_msg = 'Loading...';
			//jQuery('#qcpp_msg_id').html(loading_msg);
			jQuery('#qcpp_btn_id').html(loading_msg);
			var data = {
				"action": 'mw_wc_qbo_sync_qcpp_on_off',
				"qcpp_on_off": jQuery('#qcpp_on_off').val(),
				"qcpp_val": jQuery('#qcpp_val').val(),
			};
			jQuery.ajax({
			   type: "POST",
			   url: ajaxurl,
			   data: data,
			   cache:  false ,
			   datatype: "json",
			   success: function(result){
				   if(result!=0 && result!=''){
					 result = JSON.parse(result);
					 if(result.status=='paused'){
						jQuery('#qcpp_btn_id').removeClass('active').addClass('paused');
						//jQuery('#qcpp_msg_id').removeClass('m_active').addClass('m_paused');
						jQuery('#qcpp_btn_id').text('Syncing Paused - Queue Sync Enabled');
						jQuery('#qcpp_btn_id').attr('title','Click to active all QuickBooks Online sync');
						jQuery('#qcpp_val').val(1);
					 }else{					
						jQuery('#qcpp_btn_id').removeClass('paused').addClass('active');
						//jQuery('#qcpp_msg_id').removeClass('m_paused').addClass('m_active');
						jQuery('#qcpp_btn_id').text('Syncing Active');
						jQuery('#qcpp_btn_id').attr('title','Click to pause all QuickBooks Online sync - enabled queue sync');
						jQuery('#qcpp_val').val(0);
					 }
					 //jQuery('#qcpp_msg_id').html(result.msg);
					 jQuery('#qcpp_btn_id').html(result.msg);
				   }else{				
					//jQuery('#qcpp_msg_id').html('Error!');
					jQuery('#qcpp_btn_id').html('Error!');
				   }				  
			   },
			   error: function(result) {  
					//alert('Error!');
					jQuery('#qcpp_msg_id').html('Error!');
			   }
			});
		}		
	});
	
});
</script>