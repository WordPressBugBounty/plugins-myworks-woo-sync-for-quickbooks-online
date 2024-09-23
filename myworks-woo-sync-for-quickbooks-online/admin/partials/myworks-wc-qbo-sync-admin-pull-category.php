<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-pull&tab=category';

global $wpdb;
global $MWQS_OF;
global $MSQS_QL;

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('category_pull_search');
$category_pull_search = $MSQS_QL->get_session_val('category_pull_search');

$MSQS_QL->set_and_get('category_pull_date_from');
$category_pull_date_from = $MSQS_QL->get_session_val('category_pull_date_from');

$MSQS_QL->set_and_get('category_pull_date_to');
$category_pull_date_to = $MSQS_QL->get_session_val('category_pull_date_to');


$total_records = $MSQS_QL->count_qbo_inventory_list($category_pull_search,$category_pull_date_from,$category_pull_date_to,'category');

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$qbo_category_list = $MSQS_QL->get_qbo_inventory_list($category_pull_search," STARTPOSITION $offset MaxResults $items_per_page",$category_pull_date_from,$category_pull_date_to,'category');
$order_statuses = wc_get_order_statuses();

$wc_currency = get_woocommerce_currency();
$wc_currency_symbol = get_woocommerce_currency_symbol($wc_currency);

$qbo_home_currency = $MSQS_QL->get_qbo_company_setting('h_currency');

$pull_map_data_arr = array();
if(is_array($qbo_category_list) && count($qbo_category_list)){
	$cat_item_names_arr = array();
	foreach($qbo_category_list as $category){		
		//$cat_item_names_arr[] = "'".addslashes(esc_sql($category->getName()))."'";
		$cat_name = htmlspecialchars(esc_sql($category->getName()));
		$cat_name = $MSQS_QL->sanitize($cat_name);
		$cat_item_names_arr[] = "'".addslashes($cat_name)."'";
	}
	$pull_map_data_arr = $MSQS_QL->get_pull_category_map_data($cat_item_names_arr);
	//$MSQS_QL->_p($pull_map_data_arr);
}
?>
<div class="container">
	<div class="page_title"><h4><?php _e( 'Category Pull', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card pull-block-responsive">
		<div class="card-content">

						<div class="col s12 m12 l12">
	
								<div class="panel panel-primary">
									<div class="mw_wc_filter">
									 <span class="search_text">Search</span>
									  &nbsp;
									  <input placeholder="<?php echo __('Name','mw_wc_qbo_sync')?>" type="text" id="category_pull_search" value="<?php echo esc_attr($category_pull_search);?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('Created from yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="category_pull_date_from" value="<?php echo esc_attr($category_pull_date_from);?>">
									  &nbsp;
									  <input class="mwqs_datepicker" placeholder="<?php echo __('Created to yyyy-mm-dd','mw_wc_qbo_sync')?>" type="text" id="category_pull_date_to" value="<?php echo esc_attr($category_pull_date_to);?>">
									  &nbsp;									  
									  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
									  &nbsp;
									  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
									  <span class="filter-right-sec">
										  <span class="entries">Show entries</span>
										  &nbsp;
										  <select style="width:50px;" onchange="javascript:window.location='<?php echo esc_url($page_url);?>&<?php echo $MSQS_QL->per_page_keyword;?>='+this.value;">
											<?php echo  $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page);?>
										 </select>
									 </span>
									 </div>
									 <br />
									 <div class="row">						
										<div class="input-field col s12 m12 14">
											<button id="pull_selected_category_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Pull Selected Categories','mw_wc_qbo_sync')?></button>
											<button style="display:none;" disabled="disabled" id="pull_all_category_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo __('Pull All Categories','mw_wc_qbo_sync')?></button>
											
										</div>
									</div>
									<br />
								 
									 <?php if(is_array($qbo_category_list) && count($qbo_category_list)):?>
									 <?php //$MSQS_QL->_p($qbo_category_list);?>
									<div class="table-m">
										<div class="myworks-wc-qbo-sync-table-responsive">

										<table id="mwqs_category_pull_table" class="table tablesorter" width="100%">
											<thead>
												<tr>
													<th width="2%">
														<input type="checkbox" onclick="javascript:mw_qbo_sync_check_all(this,'category_pull_')">
													</th>
													<th width="5%">Id</th>													
													<th width="30%"><?php _e( 'Name', 'mw_wc_qbo_sync' ) ?></th>
													<th width="28%"><?php _e( 'Parent', 'mw_wc_qbo_sync' ) ?></th>
													<th width="10%"><?php _e( 'Active', 'mw_wc_qbo_sync' ) ?></th>											
													<th title="CreateTime" width="20%"><?php _e( 'Created', 'mw_wc_qbo_sync' ) ?></th>
													<th width="5%">Sync</br>Status</th>
												</tr>
											</thead>
											
											 <tbody>
												<?php 
												foreach($qbo_category_list as $category):
												$category_id = $MSQS_QL->qbo_clear_braces($category->getId());
												?>
													<tr>
														<td><input type="checkbox" id="category_pull_<?php echo (int) $category_id?>"></td>
														<td><?php echo (int) $category_id?></td>														
														<td><?php echo $MSQS_QL->escape($category->getName());?></td>
														<td>
														<?php 
															if($category->countSubItem() && $category->getSubItem()=='true'){
																$ParentRef_name = $category->getParentRef_name();
																echo $MSQS_QL->escape($ParentRef_name.' (#'.$MSQS_QL->qbo_clear_braces($category->getParentRef()).')');
															}
														?>
														</td>
														<td><?php echo $MSQS_QL->escape($category->getActive());?></td>		
														<td><?php echo $MSQS_QL->escape($MSQS_QL->view_date($category->getMetaData()->getCreateTime(),'Y-m-d H:i:s'));?></td>
														<td class="ph_cat_ss" id="ph_cat_ss_<?php echo md5(base64_encode($category->getName()));?>"></td>
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

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php $sync_window_url = $MSQS_QL->get_sync_window_url();?>
 <script type="text/javascript">
	function search_item(){		
		var category_pull_search = jQuery('#category_pull_search').val();
		var category_pull_date_from = jQuery('#category_pull_date_from').val();
		var category_pull_date_to = jQuery('#category_pull_date_to').val();
		
		category_pull_search = jQuery.trim(category_pull_search);
		category_pull_date_from = jQuery.trim(category_pull_date_from);
		category_pull_date_to = jQuery.trim(category_pull_date_to);
		
		if(category_pull_search!='' || category_pull_date_from!='' || category_pull_date_to!=''){		
			window.location = '<?php echo $page_url;?>&category_pull_search='+category_pull_search+'&category_pull_date_from='+category_pull_date_from+'&category_pull_date_to='+category_pull_date_to;
		}else{
			alert('<?php echo __('Please enter search keyword or dates.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&category_pull_search=&category_pull_date_from=&category_pull_date_to=';
	}
	
	jQuery(document).ready(function($) {
		var list_ids = [];
		
		 <?php if(is_array($pull_map_data_arr) && count($pull_map_data_arr)):?>
		 <?php foreach($pull_map_data_arr as $pmd):?>
		 <?php
			$sync_status_html = '<i title="WooCommerce Product Category Id #'.$pmd['Id'].'" class="fa fa-check-circle" style="color:green"></i>';			
		 ?>
		 list_ids.push("<?php echo $pmd['Name']?>");		 
		 jQuery('#ph_cat_ss_<?php echo $pmd['Name']?>').html('<?php echo $sync_status_html?>');
		 <?php endforeach;?>
		 <?php endif;?>
		 <?php unset($pull_map_data_arr);?>
		 
		 jQuery('.ph_cat_ss').each(function(){
			 console.log($(this).attr('id').replace("ph_cat_ss_", ""));
			 if($.inArray($(this).attr('id').replace("ph_cat_ss_", ""), list_ids) == -1){
				$(this).html('<i class="fa fa-times-circle" style="color:red"></i>');
			 }
		 });
		 
		var item_type = 'category';
		$('#pull_selected_category_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='category_pull_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('category_pull_','');
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
			
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&item_ids='+item_ids+'&item_type='+item_type,'mw_qs_category_pull',0,0,650,350);
			return false;
		});
		
		$('#pull_all_category_btn').click(function(){
			popUpWindow('<?php echo $sync_window_url;?>&sync_type=pull&sync_all=1&item_type='+item_type,'mw_qs_category_pull',0,0,650,350);
			return false;
		});	
		
	});
	
	jQuery( function($) {
		$('.mwqs_datepicker').css('cursor','pointer');
		$( ".mwqs_datepicker" ).datepicker(
			{ 
			dateFormat: 'yy-mm-dd',
			yearRange: "-50:+0",
			changeMonth: true,
			changeYear: true
			}
		);
	  } );
 </script>
 <?php echo $MWQS_OF->get_tablesorter_js('#mwqs_category_pull_table');?>