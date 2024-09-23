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
 
 $page_url = 'admin.php?page=myworks-wc-qbo-sync-queue';

 //
 $del_queue_id = (isset($_GET['del_queue']))?(int) $_GET['del_queue']:0;
 if($del_queue_id){
	 $wpdb->query($wpdb->prepare("DELETE FROM `".$wpdb->prefix."mw_wc_qbo_sync_real_time_sync_queue` WHERE `id` = %d AND `run` = 0 ",$del_queue_id));
	 $MSQS_QL->redirect($page_url);
 }
 
 $MSQS_QL->set_per_page_from_url();
 $items_per_page = $MSQS_QL->get_item_per_page();
 
 $MSQS_QL->set_and_get('queue_search');
 $queue_search = $MSQS_QL->get_session_val('queue_search');
 
 $queue_search = $MSQS_QL->sanitize($queue_search);
 $whr = '';
 if($queue_search!=''){
	//$whr.=" AND (`item_type` LIKE '%$queue_search%' OR `item_action` LIKE '%$queue_search%' OR `item_id` = '{$queue_search}' ) ";
	$whr.=$wpdb->prepare(" AND (`item_type` LIKE '%%%s%%' OR `item_action` LIKE '%%%s%%' OR `item_id` = %s ) ",$queue_search,$queue_search,$queue_search);
 }
 $total_records = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."mw_wc_qbo_sync_real_time_sync_queue` WHERE `id` >0 AND `run` = 0 $whr ");
 
 $page = $MSQS_QL->get_page_var();
 
 $offset = ( $page * $items_per_page ) - $items_per_page;
 $pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);
 
 $queue_q = "SELECT * FROM `".$wpdb->prefix."mw_wc_qbo_sync_real_time_sync_queue` WHERE `id` >0 AND `run` = 0 $whr ORDER BY `id` DESC LIMIT $offset , $items_per_page";
 $queue_data = $MSQS_QL->get_data($queue_q);
 
 /**/
 global $oqbc_nc;	
 if(isset($oqbc_nc) && $oqbc_nc){
	 echo '<style type="text/css">.mw-qbo-sync-welcome{display:none;}</style>';
 }
 ?>
 </br></br>
 <div class="container queue-outr-sec">
 <div class="page_title"><h4></h4></div>
 <div class="mw_wc_filter">
 <span class="search_text">Search queue</span>
  &nbsp;
  <input type="text" id="queue_search" value="<?php echo $queue_search;?>">
  &nbsp;		
  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
  &nbsp;
  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
  &nbsp;
  <span class="filter-right-sec">
	  <span class="entries">Show entries</span>
	  &nbsp;	
	  <select style="width:50px;" onchange="javascript:window.location='<?php echo $page_url;?>&<?php echo $MSQS_QL->per_page_keyword;?>='+this.value;">
		<?php echo  $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page);?>
	 </select>
 </span>
 </div>
 <br />
 
<!--  <div style="padding:10px;"><?php _e( 'Current Datetime', 'mw_wc_qbo_sync' );?>: <?php echo $MSQS_QL->now('Y-m-d H:i:s');?></div> -->
  
 <?php 
 //$cdt = $MSQS_QL->now('Y-m-d H:i:s');
 $cdt = date('Y-m-d H:i:s');
 $next_queue_cron_run = wp_next_scheduled( 'mw_qbo_sync_queue_cron_hook' );
 //
 $s_ncrt_cdt_diff = $next_queue_cron_run-strtotime($cdt);
 
 $next_queue_cron_run = date('Y-m-d H:i:s',$next_queue_cron_run);
 $start_date = new DateTime($cdt);
 $since_start = $start_date->diff(new DateTime($next_queue_cron_run));
 
 $min_d = $since_start->i;
 $min_s = $since_start->s;
 
 $qpit = $MSQS_QL->get_option('mw_wc_qbo_sync_queue_cron_interval_time');
 if(empty($qpit)){$qpit = 'MWQBO_5min';}
 
 $oa_qit = $MSQS_QL->get_qb_queue_p_til();
 if(!isset($oa_qit[$qpit])){$qpit = 'MWQBO_5min';}
 
 $ncrt_int = str_replace(array('MWQBO_','min'),'',$qpit);
 $ncrt_int = (int) $ncrt_int;
 if($ncrt_int < 5){
	$ncrt_int = 5;
 }
 
 $ncrt_int = $ncrt_int*60;
 ?> 
 
 <div id="mwqs_q_ncr_tdv">
  <h3 style="text-align:center"><?php echo $min_d;?> min, <?php echo $min_s;?> sec</h3>
  <p style="text-align:center; margin-top:-20px;">until next queue sync</p>
 </div>
 <div class="myworks-wc-qbo-sync-table-responsive">
 <table class="wp-list-table widefat fixed striped posts  menu-blue-bg">
 	<thead>
	<tr>
		<th width="10%">#</th>
		<th width="20%">Item Type</th>
		<th width="25%">Item Action</th>
		<th width="15%">Item ID</th>
		<th width="20%">Added</th>
		<th style="text-align:center;" width="10%">Action</th>
	</tr>
	</thead>
	<tbody id="mwqs-queue-list">	
	<?php if(count($queue_data)): $i=1;?>	
	<?php foreach($queue_data as $data):?>	
	<tr>
		<td><?php echo $data['id']?></td>
		<td><?php echo $data['item_type']?></td>
		<td><?php echo $data['item_action']?></td>
		<td><?php echo $data['item_id']?></td>		
		<td><?php echo $data['added_date']?></td>
		<td style="text-align:center;"><a class="mwqslld_btn" title="Delete" href="javascript:void(0);" onclick="javascript:if(confirm('<?php echo __('Are you sure, you want to delete this!','mw_wc_qbo_sync')?>')){window.location='<?php echo  $page_url;?>&del_queue=<?php echo $data['id']?>';}">x</a></td>
		
	</tr>
	<?php $i++;endforeach;?>
	<?php endif;?>
	</tbody>
 </table>
</div>
 <?php echo $pagination_links?>
 
 <?php if(count($queue_data)):?>
 <br />
 <div>
<?php wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_queues', 'mwqs_clear_all_queues' ); ?>
<button id="mwqs_clear_all_queues_btn"><?php _e( 'Clear all queues', 'mw_wc_qbo_sync' );?></button>
<!--
&nbsp;
<?php //wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_queue_errors', 'mwqs_clear_all_queue_errors' ); ?>
<button id="mwqs_clear_all_queue_errors_btn"><?php //_e( 'Clear all queue errors', 'mw_wc_qbo_sync' );?></button>
-->
<br/>
<br/>
<br/>
</div>
<?php endif;?>
<?php wp_nonce_field( 'myworks_wc_qbo_sync_get_nqc_time_diff', 'nqc_time_diff' ); ?>
</div> 
 <script type="text/javascript">
	function search_item(){		
		var queue_search = jQuery('#queue_search').val();
		if(queue_search!=''){
			window.location = '<?php echo $page_url;?>&queue_search='+queue_search;
		}else{
			alert('<?php echo __('Please enter search keyword.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&queue_search=';
	}
	
	function qct_counter(duration){
		
		 var timer = duration, minutes, seconds;
		var x = setInterval(function() {
			minutes = parseInt(timer / 60, 10);
			seconds = parseInt(timer % 60, 10);
			if(seconds>=0){
				document.getElementById("mwqs_q_ncr_tdv").innerHTML = '<h3 style="text-align:center">'+minutes+' min, '+seconds+' sec</h3><p style="text-align:center; margin-top:-20px;">until next queue sync</p>';
			}			
			
			if (--timer < 0) {
				timer = '<?php echo $ncrt_int;?>';
			}						
		}, 1000);
	}
	
	jQuery(document).ready(function($){
		<?php if(count($queue_data)):?>
		$('#mwqs_clear_all_queues_btn').click(function(){
			if(confirm('<?php echo __('Are you sure, you want to clear all queues?','mw_wc_qbo_sync')?>')){
				var data = {
					"action": 'mw_wc_qbo_sync_clear_all_queues',
					"mwqs_clear_all_queues": jQuery('#mwqs_clear_all_queues').val(),
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
					   $('#mwqs_clear_all_queues_btn').html(btn_text);
					   if(result!=0 && result!=''){
						 //alert('Success');
						 window.location='<?php echo $page_url;?>';
					   }else{
						 alert('Error!');			 
					   }					   	
				   },
				   error: function(result) {
						$('#mwqs_clear_all_queues_btn').html(btn_text);
						alert('Error!');					
				   }
				});
			}
		});
		
		$('#mwqs_clear_all_queue_errors_btn').click(function(){			
			if(confirm('<?php echo __('Are you sure, you want to clear all queue errors?','mw_wc_qbo_sync')?>')){
				var data = {
					"action": 'mw_wc_qbo_sync_clear_all_queue_errors',
					"mwqs_clear_all_queue_errors": jQuery('#mwqs_clear_all_queue_errors').val(),
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
					    $('#mwqs_clear_all_queue_errors_btn').html(btn_text);
					   if(result!=0 && result!=''){
						 //alert('Success');
						 window.location='<?php echo $page_url;?>';
					   }else{
						 alert('Error!');			 
					   }				  
				   },
				   error: function(result) {
						$('#mwqs_clear_all_queue_errors_btn').html(btn_text);
						alert('Error!');					
				   }
				});
			}
		});
		<?php endif;?>
		qct_counter('<?php echo $s_ncrt_cdt_diff;?>');
	});
	
	
 </script>