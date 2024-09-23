<?php
if ( ! defined( 'ABSPATH' ) )
     exit;
 $page_url = 'admin.php?page=myworks-wc-qbo-push&tab=vendor';
 
 global $MWQS_OF;
 global $MSQS_QL;
 global $wpdb;
 
$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('cl_push_search');
$cl_push_search = $MSQS_QL->get_session_val('cl_push_search');

$total_records = $MSQS_QL->count_vendors($cl_push_search,true);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$cl_push_data = $MSQS_QL->get_vendors($cl_push_search," $offset , $items_per_page",true);
//$MSQS_QL->_p($cl_push_data);

$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page);
$sstchc = (!$show_sync_status)?'class="sstchc"':'';

//11-07-2017
$push_map_data_arr = array();
if($show_sync_status && is_array($cl_push_data) && count($cl_push_data)){
	$cust_item_ids_arr = array();
	foreach($cl_push_data as $data){
		if((int) $data['qbo_vendorid']){
			$cust_item_ids_arr[] = "'".(int) $data['qbo_vendorid']."'";
		}		
	}
	$push_map_data_arr = $MSQS_QL->get_push_vendor_map_data($cust_item_ids_arr);
	//$MSQS_QL->_p($push_map_data_arr);
}

?>
<style>
	.sstchc{display:none;}
</style>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Vendor Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card qo-push-responsive">
		<div class="card-content">
			<div class="">
					<div class="">
						<div class="col s12 m12 l12">
							<div class="">
						        <div class="panel panel-primary">
						             <div class="mw_wc_filter">
									  <span class="search_text">Search</span>
									  &nbsp;
									  <input type="text" id="cl_push_search" value="<?php echo $cl_push_search;?>">
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
									 <div class="row">
										<div class="input-field col s12 m12 14">
											<button id="push_selected_vendor_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Push Selected Vendors','mw_wc_qbo_sync')?></button>
											<button id="push_all_vendor_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push All Vendors','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_unsynced_vendor_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push Un-synced Vendors','mw_wc_qbo_sync')?></button>
										</div>
									</div>
									<br />
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">
											<table id="mwqs_vendor_push_table" class="table tablesorter">
												<thead>
													<tr>
														<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'cl_push_')">
														</th>
														<th width="4%">ID</th>													
														<th width="15%">Username</th>
														<th width="15%">First Name</th>
														<th width="15%">Last Name</th>
														<th width="15%">Email</th>
														<th width="15%">Company</th>
														<th width="14%">Supplier</th>
														<th width="5%" <?php echo $sstchc;?>>Sync</br>Status</th>
													</tr>
												</thead>
												<tbody>
												<?php if(count($cl_push_data)):?>
												<?php foreach($cl_push_data as $data):?>
												<?php
												$sync_status_html = '';
												if($show_sync_status){
													$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
													if((int) $data['qbo_vendorid']){
														$qbo_vendorid = (int) $data['qbo_vendorid'];
														if(is_array($push_map_data_arr) && in_array($qbo_vendorid,$push_map_data_arr)){
															$qbo_href = $MSQS_QL->get_push_qbo_view_href('Vendor',$qbo_vendorid);
															$sync_status_html = '<i title="Mapped to #'.$data['qbo_vendorid'].' - Click to view it in QuickBooks Online" class="fa fa-check-circle" style="color:green"></i>';
															$sync_status_html = '<a target="_blank" href="'.$qbo_href.'">'.$sync_status_html.'</a>';
														}												
													}
												}
												
												$sup_id = 0;
												$sup_name = '';
												$supplier_dtls = $MSQS_QL->get_atum_supplier_dtls_from_wc_vendor_usr_id($data['ID']);
												if(is_array($supplier_dtls) && count($supplier_dtls)){
													$sup_id = $supplier_dtls['ID'];
													$sup_name = $supplier_dtls['post_title'];
												}
												?>
												<tr>
													<td>
													<?php if($sup_id>0):?>
													<input type="checkbox" id="cl_push_<?php echo $data['ID']?>">
													<?php endif;?>
													</td>
													<td><?php echo $data['ID']?></td>
													
													<td><a href="<?php echo admin_url('user-edit.php?user_id=').$data['ID'] ?>" target="_blank"><?php echo $data['display_name']?></a></td>												
													
													<td><?php echo $data['first_name']?></td>
													<td><?php echo $data['last_name']?></td>
													<td><?php echo $data['user_email']?></td>
													<td><?php echo $data['billing_company']?></td>
													
													<td>
														<?php if($sup_id>0):?>
														<a href="<?php echo admin_url('post.php?post='.$sup_id.'&action=edit')?>" target="_blank"><?php echo $sup_name;?></a>
														<?php endif;?>
													</td>
													
													<td <?php echo $sstchc;?>><?php echo $sync_status_html;?></td>
												</tr>
												<?php endforeach;?>
												<?php endif;?>
												</tbody>
											</table>
										</div>
									</div>
						           <?php echo $pagination_links?>
						        </div>
						    </div>
						</div>
					</div>
			</div>
		</div>
	</div>
</div>
<?php $sync_window_url = $MSQS_QL->get_sync_window_url();?>
 <script type="text/javascript">
	function search_item(){		
		var cl_push_search = jQuery('#cl_push_search').val();
		if(cl_push_search!=''){			
			window.location = '<?php echo $page_url;?>&cl_push_search='+cl_push_search;
		}else{
			alert('<?php echo __('Please enter search keyword.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&cl_push_search=';
	}
	
	jQuery(document).ready(function($) {
		var item_type = 'vendor';
		$('#push_selected_vendor_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='cl_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('cl_push_','');
					only_id = parseInt(only_id);
					if(only_id>0){
						item_ids+=only_id+',';
					}					
				}
			});
			
			if(item_ids!=''){
				item_ids = item_ids.substring(0, item_ids.length - 1);
			}
			
			if(item_checked==0){
				alert('<?php echo __('Please select at least one item.','mw_wc_qbo_sync');?>');
				return false;
			}
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_vendor_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_vendor_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_all=1&item_type='+item_type,'mw_qs_vendor_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_vendor_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_unsynced=1&item_type='+item_type,'mw_qs_vendor_push',0,0,650,350);
			return false;
		});
	});
 </script>
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_vendor_push_table');?>