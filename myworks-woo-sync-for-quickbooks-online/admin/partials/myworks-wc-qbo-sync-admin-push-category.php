<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-push&tab=category';
 
global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('category_push_search');
$category_push_search = $MSQS_QL->get_session_val('category_push_search');

$total_records = $MSQS_QL->count_woocommerce_category_list($category_push_search);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$wc_category_list = $MSQS_QL->get_woocommerce_category_list($category_push_search," $offset , $items_per_page");

//$MSQS_QL->_p($wc_category_list);

$show_sync_status = $MSQS_QL->if_show_sync_status($items_per_page,50);
$sstchc = (!$show_sync_status)?'class="sstchc"':'';

$push_map_data_arr = array();
if($show_sync_status && is_array($wc_category_list) && count($wc_category_list)){
	$cat_item_names_arr = array();
	foreach($wc_category_list as $cat_details){
		
		$name_replace_chars = array(':');					
		$cat_name = $MSQS_QL->get_array_isset(array('cat_name'=>$cat_details['name']),'cat_name','',true,100,false,$name_replace_chars);
		
		$cat_item_names_arr[] = "'".addslashes($cat_name)."'";
	}
	$push_map_data_arr = $MSQS_QL->get_push_category_map_data($cat_item_names_arr);
	//$MSQS_QL->_p($push_map_data_arr);
}
?>
<style>
	.sstchc{display:none;}
</style>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Category Push', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card">
		<div class="card-content">

						<div class="col s12 m12 l12">

						        <div class="panel panel-primary">
						             <div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input type="text" id="category_push_search" value="<?php echo $category_push_search;?>">
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
											<button id="push_selected_category_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Push Selected Categories','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_category_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push All Categories','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_unsynced_category_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo __('Push Un-synced Categories','mw_wc_qbo_sync')?></button>
										</div>
									</div>
									 <br />

									<?php if(is_array($wc_category_list) && count($wc_category_list)):?>
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">
											<table class="table" id="mwqs_category_push_table">
												<thead>
													<tr>
														<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'category_push_')">
														</th>
														<th width="5%">#</th>
														<th width="40%">Name</th>
														<th width="8%">Parent</th>
														<th width="30%">Description</th>
														<th width="10%">Product Count</th>												
														<th width="5%" <?php echo $sstchc;?>>&nbsp;</th>
													</tr>
												</thead>
												<tbody>
												
												<?php foreach($wc_category_list as $p_val):?>
												
												<tr>
													<td><input type="checkbox" id="category_push_<?php echo $p_val['id']?>"></td>
													<td><?php echo $p_val['id']?></td>
													<td><a href="<?php echo admin_url('term.php?taxonomy=product_cat&post_type=product&tag_ID=').$p_val['id'] ?>" target="_blank"><?php _e( $p_val['name'], 'mw_wc_qbo_sync' );?></a></td>
													<td><?php echo $p_val['parent']?></td>
													<td><?php _e( $p_val['description'], 'mw_wc_qbo_sync' );?></td>
													<td><?php echo (int) $p_val['product_count']?></td>
													<?php 
														$name_replace_chars = array(':');					
														$cat_name = $MSQS_QL->get_array_isset(array('cat_name'=>$p_val['name']),'cat_name','',true,100,false,$name_replace_chars);
													?>
													<td class="ph_cat_ss<?php if(!$show_sync_status){echo ' sstchc';}?>" id="ph_cat_ss_<?php echo md5(base64_encode($cat_name));?>"></td>
												</tr>
												<?php endforeach;?>									
												</tbody>
											</table>
										</div>
									</div>
									<?php echo $pagination_links?>
									<?php else:?>									
									<h4 class="mw_mlp_ndf">
										<?php _e( 'No available categories to display.', 'mw_wc_qbo_sync' );?>
									</h4>
									<?php endif;?>						           
						        </div>

						</div>
		</div>
	</div>
</div>
<?php $sync_window_url = $MSQS_QL->get_sync_window_url();?>
 <script type="text/javascript">
	function search_item(){		
		var category_push_search = jQuery('#category_push_search').val();
		if(category_push_search!=''){			
			window.location = '<?php echo $page_url;?>&category_push_search='+category_push_search;
		}else{
			alert('<?php echo __('Please enter search keyword.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&category_push_search=';
	}
	
	jQuery(document).ready(function($) {
		var list_ids = [];
		
		 <?php if(is_array($push_map_data_arr) && count($push_map_data_arr)):?>
		 <?php foreach($push_map_data_arr as $pmd):?>
		 <?php
			$sync_status_html = '<i title="QuickBooks Category Id #'.$pmd['Id'].'" class="fa fa-check-circle" style="color:green"></i>';			
		 ?>
		 list_ids.push("<?php echo $pmd['Name']?>");		 
		 jQuery('#ph_cat_ss_<?php echo $pmd['Name']?>').html('<?php echo $sync_status_html?>');
		 <?php endforeach;?>		 
		 
		 jQuery('.ph_cat_ss').each(function(){
			 console.log($(this).attr('id').replace("ph_cat_ss_", ""));
			 if($.inArray($(this).attr('id').replace("ph_cat_ss_", ""), list_ids) == -1){
				$(this).html('<i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		 
		 <?php endif;?>
		 <?php unset($push_map_data_arr);?>
		 
		var item_type = 'category';
		$('#push_selected_category_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='category_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('category_push_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_category_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_category_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_all=1&item_type='+item_type,'mw_qs_category_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_category_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=push&sync_unsynced=1&item_type='+item_type,'mw_qs_category_push',0,0,650,350);
			return false;
		});
	});
 </script>
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_category_push_table');?>