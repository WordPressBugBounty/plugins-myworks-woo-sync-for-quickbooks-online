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
 //
 global $MWQS_OF;
 global $MSQS_QL;
 global $wpdb;
 
 $page_url = 'admin.php?page=myworks-wc-qbo-sync-log';
 
 //
 $del_log_id = (isset($_GET['del_log']))?(int) $_GET['del_log']:0;
 if($del_log_id){
	 $wpdb->query($wpdb->prepare("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE `id` = %d",$del_log_id));
	 $MSQS_QL->redirect($page_url);
 }
 
 $MSQS_QL->set_per_page_from_url();
 $items_per_page = $MSQS_QL->get_item_per_page();
 
 $MSQS_QL->set_and_get('log_search');
 $log_search = $MSQS_QL->get_session_val('log_search');
 
 $log_search = $MSQS_QL->sanitize($log_search);
 $whr = '';
 if($log_search!=''){
	//$whr.=" AND (`details` LIKE '%$log_search%' OR `log_type` LIKE '%$log_search%' OR `log_title` LIKE '%$log_search%' ) ";
	$whr.=$wpdb->prepare(" AND (`details` LIKE '%%%s%%' OR `log_type` LIKE '%%%s%%' OR `log_title` LIKE '%%%s%%' ) ",$log_search,$log_search,$log_search);
 }
 $total_records = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE `id` >0 $whr ");
 
 $page = $MSQS_QL->get_page_var();
 
 $offset = ( $page * $items_per_page ) - $items_per_page;
 $pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);
 
 $log_q = "SELECT * FROM `".$wpdb->prefix."mw_wc_qbo_sync_log` WHERE `id` >0 $whr ORDER BY `id` DESC LIMIT $offset , $items_per_page";
 $log_data = $MSQS_QL->get_data($log_q);
 
 /**/
 global $oqbc_nc;	
 if(isset($oqbc_nc) && $oqbc_nc){
	 echo '<style type="text/css">.mw-qbo-sync-welcome{display:none;}</style>';
 }
 ?>
 </br></br>
 <div class="container log-outr-sec mq_lp_cont">
 <div class="page_title"><h4><?php _e( 'Sync Log', 'mw_wc_qbo_sync' );?></h4></div>
 <div class="mw_wc_filter"> 
  <input placeholder="Search Log" type="text" id="log_search" value="<?php echo $log_search;?>">
  &nbsp;		
  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
  &nbsp;
  <button onclick="javascript:reset_item();" class="btn btn-info btn-reset">Reset</button>
  &nbsp;
  <span class="filter-right-sec">
	  <span class="entries">Show</span>
	  &nbsp;	
	  <select class="mq_lp_sel" onchange="javascript:window.location='<?php echo $page_url;?>&<?php echo $MSQS_QL->per_page_keyword;?>='+this.value;">
		<?php echo  $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page);?>
	 </select>
	 &nbsp;
	 <span>entries</span>
 </span>
 </div>
 <br />
 
 <div class="mq_lp_cdt"><?php _e( 'Current Datetime', 'mw_wc_qbo_sync' );?>: <?php echo $MSQS_QL->now('Y-m-d ');?> <b><?php echo $MSQS_QL->now('h:i:s A');?></b></div>
 <br />
 <div class="myworks-wc-qbo-sync-table-responsive">
 <table class="wp-list-table widefat fixed striped posts  menu-blue-bg">
 	<thead>
	<tr>
		<th style="text-align:center;" width="8%">#</th>
		<th width="34%">&nbsp;</th>
		<th width="36%">Message</th>
		<th width="12%">Date</th>
		<th style="text-align:center;" width="10%">Action</th>
	</tr>
	</thead>
	<tbody id="mwqs-log-list">	
	<?php if(count($log_data)): $i=1;?>	
	<?php foreach($log_data as $data):?>
	<?php		
		$qb_view_link = $MSQS_QL->get_log_qbo_view_link($data);
		$lm_dt = $MSQS_QL->log_page_msg_col_output($data);
	?>
	<tr>
		<td style="text-align:center;"><?php echo $data['id']?></td>
		<td>
		<h4 class="mq_lp_lth"><?php echo $data['log_type']?></h4>
		<div class="mq_lp_tbd <?php if( !$data['success']):?>cl_err<?php endif;?>">
			<?php echo nl2br(stripslashes($data['log_title']));?>
		</div>
		</td>
		
		<td <?php if( !$data['success']):?>style="color:red;"<?php endif;?>>
			<?php //echo nl2br(stripslashes($data['details']));?>
			<?php echo $lm_dt['details'];?>
		</td>
		
		<td>
		<span class="mq_lp_ltime"><?php echo date('h:i:s A',strtotime($data['added_date']));?></span>
		<span><?php echo date('Y-m-d',strtotime($data['added_date']));?></span>
		</td>
		<td style="text-align:center;">
		<a class="mwqslld_btn" title="Delete" href="javascript:void(0);" onclick="javascript:if(confirm('<?php echo __('Are you sure, you want to delete this!','mw_wc_qbo_sync')?>')){window.location='<?php echo  $page_url;?>&del_log=<?php echo $data['id']?>';}">x</a>
		<?php echo $qb_view_link;?>
		
		<?php if(!empty($lm_dt['oth']) && isset($lm_dt['oth']['error_code'])):?>
		<a target="_blank" class="lg_qb_view lg_qb_ecode" href="<?php echo $lm_dt['oth']['error_code_url'];?>" title="View QuickBooks Error Code Information (<?php echo $lm_dt['oth']['error_code'];?>)">?</a>
		<?php endif;?>
		
		</td>
	</tr>
	<?php $i++;endforeach;?>
	<?php endif;?>
	</tbody>
 </table>
</div>
 <?php echo $pagination_links?>
 
 <?php if(count($log_data)):?>
 <br />
 <div> 
<?php wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_logs', 'mwqs_clear_all_logs' ); ?>
<button id="mwqs_clear_all_logs_btn"><?php _e( 'Clear Entire Log', 'mw_wc_qbo_sync' );?></button>
&nbsp;
<?php wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_log_errors', 'mwqs_clear_all_log_errors' ); ?>
<button id="mwqs_clear_all_log_errors_btn"><?php _e( 'Clear Error Logs', 'mw_wc_qbo_sync' );?></button>
<br/>
<br/>
<br/>
</div>
<?php endif;?>

</div> 
 <script type="text/javascript">
	function search_item(){		
		var log_search = jQuery('#log_search').val();
		if(log_search!=''){
			window.location = '<?php echo $page_url;?>&log_search='+log_search;
		}else{
			alert('<?php echo __('Please enter search keyword.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&log_search=';
	}
	
	<?php if(count($log_data)):?>
	jQuery(document).ready(function($){		
		$('#mwqs_clear_all_logs_btn').click(function(){
			if(confirm('<?php echo __('This will clear all log entries. OK to proceed?','mw_wc_qbo_sync')?>')){
				var data = {
					"action": 'mw_wc_qbo_sync_clear_all_logs',
					"mwqs_clear_all_logs": jQuery('#mwqs_clear_all_logs').val(),
				};
				var btn_text = $(this).html();
				var loading_msg = 'Loading...';
				$(this).html(loading_msg);
				
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					   $('#mwqs_clear_all_logs_btn').html(btn_text);
					   if(result!=0 && result!=''){
						 //alert('Success');
						 window.location='<?php echo $page_url;?>';
					   }else{
						 alert('Error!');			 
					   }					   	
				   },
				   error: function(result) {
						$('#mwqs_clear_all_logs_btn').html(btn_text);
						alert('Error!');					
				   }
				});
			}
		});
		
		$('#mwqs_clear_all_log_errors_btn').click(function(){			
			if(confirm('<?php echo __('This will clear all error log entries. OK to proceed?','mw_wc_qbo_sync')?>')){
				var data = {
					"action": 'mw_wc_qbo_sync_clear_all_log_errors',
					"mwqs_clear_all_log_errors": jQuery('#mwqs_clear_all_log_errors').val(),
				};
				
				var btn_text = $(this).html();				
				var loading_msg = 'Loading...';
				$(this).html(loading_msg);
				
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					    $('#mwqs_clear_all_log_errors_btn').html(btn_text);
					   if(result!=0 && result!=''){
						 //alert('Success');
						 window.location='<?php echo $page_url;?>';
					   }else{
						 alert('Error!');			 
					   }				  
				   },
				   error: function(result) {
						$('#mwqs_clear_all_log_errors_btn').html(btn_text);
						alert('Error!');					
				   }
				});
			}
		});
	});
	<?php endif;?>
 </script>