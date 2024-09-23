<?php 
if ( ! defined( 'ABSPATH' ) )
     exit;
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
global $MWQS_OF;
global $MSQS_QL;
global $wpdb;
 
$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=vendor';

//
if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_vendor', 'map_wc_qbo_vendor' ) ) {
	//$MSQS_QL->_p($_POST);
	$item_ids = array();
	foreach ($_POST as $key=>$value){
		if ($MSQS_QL->start_with($key, "map_client_")){
			$id = (int) str_replace("map_client_", "", $key);
			if($id){ //&& (int) $value
				$item_ids[$id] = (int) $value;
			}
		}
	}
	if(count($item_ids)){
		foreach ($item_ids as $key=>$value){
			$save_data = array();			
			$save_data['qbo_vendorid'] = $value;
			
			$table = $wpdb->prefix.'mw_wc_qbo_sync_vendor_pairs';
			if($MSQS_QL->get_field_by_val($table,'id','wc_vendorid',$key)){
				$wpdb->update($table,$save_data,array('wc_vendorid'=>$key),'',array('%d'));
			}else{
				$save_data['wc_vendorid'] = $key;
				$wpdb->insert($table, $save_data);
			}
		}
		//$MSQS_QL->set_session_msg('map_client_msg',__('Vendors mapped successfully.','mw_wc_qbo_sync'));		
		$MSQS_QL->set_session_val('map_page_update_message',__('Vendors mapped successfully.','mw_wc_qbo_sync'));
	}
	$wpdb->query("DELETE FROM `".$table."` WHERE `qbo_vendorid` = 0 ");
	$MSQS_QL->redirect($page_url);
	//$MSQS_QL->_p($item_ids);
}

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('cl_map_search');
$cl_map_search = $MSQS_QL->get_session_val('cl_map_search');

$total_records = $MSQS_QL->count_vendors($cl_map_search);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$cl_map_data = $MSQS_QL->get_vendors($cl_map_search," $offset , $items_per_page");
$qbo_vendor_options = '';
if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
	$cdd_sb = 'dname';
	$mw_wc_qbo_sync_client_sort_order = $MSQS_QL->sanitize($MSQS_QL->get_option('mw_wc_qbo_sync_client_sort_order'));
	if($mw_wc_qbo_sync_client_sort_order!=''){
		$cdd_sb = $mw_wc_qbo_sync_client_sort_order;
		if($cdd_sb!='dname' && $cdd_sb!='first' && $cdd_sb!='last' && $cdd_sb!='company'){
			$cdd_sb = 'dname';
		}
	}
	$qbo_vendor_options = $MSQS_QL->option_html('', $wpdb->prefix.'mw_wc_qbo_sync_qbo_vendors','qbo_vendorid','dname','',$cdd_sb.' ASC','',true);
}

$selected_options_script = '';
//$MSQS_QL->_p($cl_map_data);
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>

<div class="container map-vendor-outer">
	 <div class="page_title"><h4><?php _e( 'Vendor Mappings', 'mw_wc_qbo_sync' );?></h4></div>
	 <div class="mw_wc_filter">
	 <span class="search_text">Search</span>
	  &nbsp;
	  <input type="text" id="cl_map_search" value="<?php echo $cl_map_search;?>">
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
	<?php //$MSQS_QL->show_session_msg('map_client_msg','success');?>
	<div class="card">
		<div class="card-content">
			<div class="row">
				<form method="POST" class="col s12 m12 l12">
					<div class="row">
						<div class="col s12 m12 l12">
							<div class="myworks-wc-qbo-sync-table-responsive">
								<table class="mw-qbo-sync-settings-table menu-blue-bg menu-bg-a new-table">
									<thead>
										<tr>
											<th width="5%">&nbsp; ID</th>
											<th width="21%">WooCommerce Vendor Name</th>
											<th width="17%">Email</th>
											<th width="17%">Company</th>
											<th width="14%">Supplier</th>
											<th width="25%">
												QuickBooks Vendor								    	
											</th>
										</tr>
									</thead>
									<tbody>                					
										<?php if(count($cl_map_data)):?>
										<?php foreach($cl_map_data as $data):?>
										<?php
											$sup_id = 0;
											$sup_name = '';
											$supplier_dtls = $MSQS_QL->get_atum_supplier_dtls_from_wc_vendor_usr_id($data['ID']);
											if(is_array($supplier_dtls) && count($supplier_dtls)){
												$sup_id = $supplier_dtls['ID'];
												$sup_name = $supplier_dtls['post_title'];
											}
										?>
										<tr>
											<td><?php echo $data['ID']?></td>
											<td>
												<a href="<?php echo admin_url('user-edit.php?user_id=').$data['ID'] ?>" target="_blank">
											<?php echo $data['first_name']?> <?php echo $data['last_name']?>
										</a>									
											</td>	
											<td><?php echo $data['user_email']?></td>
											<td><?php echo $data['billing_company']?></td>
											
											<td>
												<?php if($sup_id>0):?>
												<a href="<?php echo admin_url('post.php?post='.$sup_id.'&action=edit')?>" target="_blank"><?php echo $sup_name;?></a>
												<?php endif;?>
											</td>
											
											<td>											
												<?php
												$dd_options = '<option value=""></option>';
												$dd_ext_class = '';
												if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
													$dd_ext_class = 'mwqs_dynamic_select';
													if((int) $data['qbo_vendorid']){
														$dd_options = '<option value="'.$data['qbo_vendorid'].'">'.$data['qbo_dname'].'</option>';
													}
												}else{												
													$dd_options.=$qbo_vendor_options;
													if((int) $data['qbo_vendorid']){
														$selected_options_script.='jQuery(\'#map_client_'.$data['ID'].'\').val(\''.(int) $data['qbo_vendorid'].'\');';
													}
												}											
												?>

												<select class="mw_wc_qbo_sync_select2 <?php echo $dd_ext_class;?>" name="map_client_<?php echo $data['ID']?>" id="map_client_<?php echo $data['ID']?>">
													<?php echo $dd_options;?>
												</select>
											
											</td>
											
										</tr>
										<?php endforeach;?>
										<?php endif;?>
	            				</tbody>
								</table>
							</div>
							<?php echo $pagination_links?>
							<?php if(empty($cl_map_data)):?>
							   <h4 class="mw_mlp_ndf">
									<?php _e( 'No available vendors to display.', 'mw_wc_qbo_sync' );?>
								</h4>
							<?php endif;?>
						</div>
					</div>
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_vendor', 'map_wc_qbo_vendor' ); ?>
						<div class="input-field col s12 m6 l4">
							<button class="waves-effect waves-light btn save-btn mw-qbo-sync-green">Save</button>
						</div>
					</div>
				</form>
				
				<br />

				<div class="col col-m">
				<h5><?php _e( 'Clear All Vendors Mappings', 'mw_wc_qbo_sync' );?></h5>
				<?php wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_mappings_vendors', 'clear_all_mappings_vendors' ); ?>
				<button id="mwqs_cacm_btn"><?php _e( 'Clear Mappings', 'mw_wc_qbo_sync' );?></button>
				&nbsp;
				<span id="mwqs_cacm_msg"></span>
				</div>
				
			</div>
		</div>
	</div>
</div>

 <script type="text/javascript">
	function search_item(){		
		var cl_map_search = jQuery('#cl_map_search').val();
		cl_map_search = jQuery.trim(cl_map_search);
		if(cl_map_search!=''){
			window.location = '<?php echo $page_url;?>&cl_map_search='+cl_map_search;
		}else{
			alert('<?php echo __('Please enter search keyword.','mw_wc_qbo_sync')?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo $page_url;?>&cl_map_search=';
	}
	<?php if($selected_options_script!=''):?>
	jQuery(document).ready(function(){
		<?php echo $selected_options_script;?>
	});
	<?php endif;?>
	
	jQuery(document).ready(function($){
		$('#mwqs_automap_vendors').click(function(){
			if(confirm('<?php echo __('This will override any previous vendor mappings, and scan your WooCommerce & QuickBooks Online vendors by email to automatically match them for you.')?>')){
				jQuery('#mwqs_automap_vendors_msg').html('');
				jQuery('#mwqs_automap_vendors_msg_by_name').html('');
				var data = {
					"action": 'mw_wc_qbo_sync_automap_vendors',
					"automap_vendors": jQuery('#automap_vendors').val(),
				};
				var loading_msg = 'Loading...';
				jQuery('#mwqs_automap_vendors_msg').html(loading_msg);
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					   if(result!=0 && result!=''){
						//alert(result);
						//jQuery('#mwqs_automap_vendors_msg').html('Success');
						jQuery('#mwqs_automap_vendors_msg').html(result);
						//alert('Success!');
						//location.reload();
						window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 jQuery('#mwqs_automap_vendors_msg').html('Automap was timed out and could not fully complete. Please try again');
						 //alert('Error!');			 
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_automap_vendors_msg').html('Automap was timed out and could not fully complete. Please try again');
				   }
				});
			}
		});
		
		$('#mwqs_automap_vendors_by_name').click(function(){
			if(confirm('<?php echo __('This will override any previous vendor mappings, and scan your WooCommerce & QuickBooks Online vendors by Display Name to automatically match them for you.')?>')){
				jQuery('#mwqs_automap_vendors_msg_by_name').html('');
				jQuery('#mwqs_automap_vendors_msg').html('');
				var data = {
					"action": 'mw_wc_qbo_sync_automap_vendors_by_name',
					"automap_vendors_by_name": jQuery('#automap_vendors_by_name').val(),
				};
				var loading_msg = 'Loading...';
				jQuery('#mwqs_automap_vendors_msg_by_name').html(loading_msg);
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					   if(result!=0 && result!=''){
						//alert(result);
						//jQuery('#mwqs_automap_vendors_msg_by_name').html('Success');
						jQuery('#mwqs_automap_vendors_msg_by_name').html(result);
						//alert('Success!');
						//location.reload();
						window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 jQuery('#mwqs_automap_vendors_msg_by_name').html('Automap was timed out and could not fully complete. Please try again');
						 //alert('Error!');			 
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_automap_vendors_msg_by_name').html('Automap was timed out and could not fully complete. Please try again');
				   }
				});
			}
		});
		
		$('#mwqs_cacm_btn').click(function(){
			if(confirm('<?php echo __('Are you sure, you want to clear all vendor mappings?','mw_wc_qbo_sync')?>')){
				var loading_msg = 'Loading...';
				jQuery('#mwqs_cacm_msg').html(loading_msg);
				var data = {
					"action": 'mw_wc_qbo_sync_clear_all_mappings_vendors',
					"clear_all_mappings_vendors": jQuery('#clear_all_mappings_vendors').val(),
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
						 jQuery('#mwqs_cacm_msg').html('Success!');
						 window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 //alert('Error!');
						jQuery('#mwqs_cacm_msg').html('Error!');
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_cacm_msg').html('Error!');
				   }
				});
			}
		});
		
		$('#mwqs_refresh_data_from_qbo').click(function(event){
			if(!confirm('<?php echo __('Are you sure, you want to refresh data from quickbooks?','mw_wc_qbo_sync')?>')){
				event.preventDefault();
			}
		});
		
	});
 </script>
 <?php echo $MWQS_OF->get_select2_js('.mw_wc_qbo_sync_select2','qbo_vendor');?>