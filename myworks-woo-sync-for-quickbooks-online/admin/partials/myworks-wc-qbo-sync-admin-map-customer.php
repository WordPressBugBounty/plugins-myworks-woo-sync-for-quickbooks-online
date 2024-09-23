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
 
$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=customer';

//
if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_customer', 'map_wc_qbo_customer' ) ) {
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
			$save_data['qbo_customerid'] = $value;
			
			$table = $wpdb->prefix.'mw_wc_qbo_sync_customer_pairs';
			if($MSQS_QL->get_field_by_val($table,'id','wc_customerid',$key)){
				$wpdb->update($table,$save_data,array('wc_customerid'=>$key),'',array('%d'));
			}else{
				$save_data['wc_customerid'] = $key;
				$wpdb->insert($table, $save_data);
			}
		}
		//$MSQS_QL->set_session_msg('map_client_msg',__('Customers mapped successfully.','mw_wc_qbo_sync'));		
		$MSQS_QL->set_session_val('map_page_update_message',__('Customers mapped successfully.','mw_wc_qbo_sync'));
	}
	$wpdb->query("DELETE FROM `".$table."` WHERE `qbo_customerid` = 0 ");
	$MSQS_QL->redirect($page_url);
	//$MSQS_QL->_p($item_ids);
}

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('cl_map_search');
$cl_map_search = $MSQS_QL->get_session_val('cl_map_search');

$total_records = $MSQS_QL->count_customers($cl_map_search);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$cl_map_data = $MSQS_QL->get_customers($cl_map_search," $offset , $items_per_page");
$qbo_customer_options = '';
if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
	$cdd_sb = 'dname';
	$mw_wc_qbo_sync_client_sort_order = $MSQS_QL->sanitize($MSQS_QL->get_option('mw_wc_qbo_sync_client_sort_order'));
	if($mw_wc_qbo_sync_client_sort_order!=''){
		$cdd_sb = $mw_wc_qbo_sync_client_sort_order;
		if($cdd_sb!='dname' && $cdd_sb!='first' && $cdd_sb!='last' && $cdd_sb!='company'){
			$cdd_sb = 'dname';
		}
	}
	$qbo_customer_options = $MSQS_QL->option_html('', $wpdb->prefix.'mw_wc_qbo_sync_qbo_customers','qbo_customerid','dname','',$cdd_sb.' ASC','',true);
}

$selected_options_script = '';

?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>

<div class="container map-customer-outer map-product-responsive">
	 <div class="page_title"><h4><?php _e( 'Customer Mappings', 'mw_wc_qbo_sync' );?></h4></div>
	 <div class="mw_wc_filter">
	 <span class="search_text">Search</span>
	  &nbsp;
	  <input type="text" id="cl_map_search" placeholder="NAME / EMAIL / COMPANY / ID" value="<?php echo $cl_map_search;?>">
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
											<th width="23%">WooCommerce Customer Name</th>
											<th width="23%">Email</th>
											<th width="23%">Company</th>
											<th width="25%" class="title-description">
												QuickBooks Customer								    	
											</th>
										</tr>
									</thead>
									<tbody>                					
										<?php if(count($cl_map_data)):?>
										<?php foreach($cl_map_data as $data):?>
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
												<?php
												$dd_options = '<option value=""></option>';
												$dd_ext_class = '';
												if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
													$dd_ext_class = 'mwqs_dynamic_select';
													if((int) $data['qbo_customerid']){
														$dd_options = '<option value="'.$data['qbo_customerid'].'">'.$MSQS_QL->escape($data['qbo_dname']).'</option>';
													}
												}else{
													$dd_options.=$qbo_customer_options;
													if((int) $data['qbo_customerid']){
														$selected_options_script.='jQuery(\'#map_client_'.$data['ID'].'\').val(\''.(int) $data['qbo_customerid'].'\');';
													}
												}											
												?>

												<select class="mw_wc_qbo_sync_select2 <?php echo $dd_ext_class;?>" name="map_client_<?php echo $data['ID']?>" id="map_client_<?php echo $data['ID']?>">
													<?php echo $dd_options;?>
												</select>
											
											</td>
											
										</tr>
										<?php endforeach;?>
										<?php else:?>
										<tr style="display:none;">
											<td colspan="5">
												<p class="mwqb_tblnd">
													<?php //_e( 'No customers found.', 'mw_wc_qbo_sync' );?>
												</p>
											</td>
										</tr>
										<?php endif;?>
	            				</tbody>
								</table>
								<?php echo $pagination_links?>
								<?php if(empty($cl_map_data)):?>
								   <h4 class="mw_mlp_ndf">
										<?php _e( 'No available customers to display.', 'mw_wc_qbo_sync' );?>
									</h4>
								<?php endif;?>
							</div>
						</div>
					</div>
					
					<?php if($total_records > 0):?>
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_customer', 'map_wc_qbo_customer' ); ?>
						<div class="input-field col s12 m6 l4">
							<button class="waves-effect waves-light btn save-btn mw-qbo-sync-green">Save</button>
						</div>
					</div>
					<?php endif;?>
				</form>
				
				<br />

				<div class="col col-m">
				<h5><?php _e( 'Clear All Customers Mappings', 'mw_wc_qbo_sync' );?></h5>
				<?php wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_mappings_customers', 'clear_all_mappings_customers' ); ?>
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
		$('#mwqs_automap_customers_wf_qf').click(function(){
			var cam_wf = $('#cam_wf').val().trim();
			var cam_qf = $('#cam_qf').val().trim();
			
			var mo_um = '';
			if($('#cam_moum_chk').is(':checked')){
				mo_um = 'true';
			}
			
			if(cam_wf!='' && cam_qf!=''){
				$('#cam_wqf_e_msg').html('');
				if(confirm('<?php echo __('This will override any previous customer mappings, and scan your WooCommerce & QuickBooks Online customers by selected fields to automatically match them for you.')?>')){
					var data = {
						"action": 'mw_wc_qbo_sync_automap_customers_wf_qf',
						"automap_customers_wf_qf": jQuery('#automap_customers_wf_qf').val(),
						"cam_wf": cam_wf,
						"cam_qf": cam_qf,
						"mo_um": mo_um,
					};
					
					var loading_msg = 'Loading...';
					jQuery('#mwqs_automap_customers_msg').html(loading_msg);
					
					jQuery.ajax({
					   type: "POST",
					   url: ajaxurl,
					   data: data,
					   cache:  false ,
					   //datatype: "json",
					   success: function(result){
						   if(result!=0 && result!=''){							
							jQuery('#mwqs_automap_customers_msg').html(result);							
							window.location='<?php echo admin_url($page_url)?>';
						   }else{
							 jQuery('#mwqs_automap_customers_msg').html('Automap was timed out and could not fully complete. Please try again');							
						   }				  
					   },
					   error: function(result) {							
							jQuery('#mwqs_automap_customers_msg').html('Automap was timed out and could not fully complete. Please try again');
					   }
					});
					
				}
			}else{				
				$('#cam_wqf_e_msg').html('Please select automap fields.');
			}
		});
		
		<?php if($js_section=false):?>
		$('#mwqs_automap_customers').click(function(){
			if(confirm('<?php echo __('This will override any previous customer mappings, and scan your WooCommerce & QuickBooks Online customers by email to automatically match them for you.')?>')){
				jQuery('#mwqs_automap_customers_msg').html('');
				jQuery('#mwqs_automap_customers_msg_by_name').html('');
				var data = {
					"action": 'mw_wc_qbo_sync_automap_customers',
					"automap_customers": jQuery('#automap_customers').val(),
				};
				var loading_msg = 'Loading...';
				jQuery('#mwqs_automap_customers_msg').html(loading_msg);
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					   if(result!=0 && result!=''){
						//alert(result);
						//jQuery('#mwqs_automap_customers_msg').html('Success');
						jQuery('#mwqs_automap_customers_msg').html(result);
						//alert('Success!');
						//location.reload();
						window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 jQuery('#mwqs_automap_customers_msg').html('Error!');
						 //alert('Error!');			 
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_automap_customers_msg').html('Error!');
				   }
				});
			}
		});
		
		$('#mwqs_automap_customers_by_name').click(function(){
			if(confirm('<?php echo __('This will override any previous customer mappings, and scan your WooCommerce & QuickBooks Online customers by Display Name to automatically match them for you.')?>')){
				jQuery('#mwqs_automap_customers_msg_by_name').html('');
				jQuery('#mwqs_automap_customers_msg').html('');
				var data = {
					"action": 'mw_wc_qbo_sync_automap_customers_by_name',
					"automap_customers_by_name": jQuery('#automap_customers_by_name').val(),
				};
				var loading_msg = 'Loading...';
				jQuery('#mwqs_automap_customers_msg_by_name').html(loading_msg);
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					   if(result!=0 && result!=''){
						//alert(result);
						//jQuery('#mwqs_automap_customers_msg_by_name').html('Success');
						jQuery('#mwqs_automap_customers_msg_by_name').html(result);
						//alert('Success!');
						//location.reload();
						window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 jQuery('#mwqs_automap_customers_msg_by_name').html('Error!');
						 //alert('Error!');			 
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_automap_customers_msg_by_name').html('Error!');
				   }
				});
			}
		});
		
		<?php endif;?>
		
		$('#mwqs_cacm_btn').click(function(){
			if(confirm('<?php echo __('Are you sure, you want to clear all customer mappings?','mw_wc_qbo_sync')?>')){
				var loading_msg = 'Loading...';
				jQuery('#mwqs_cacm_msg').html(loading_msg);
				var data = {
					"action": 'mw_wc_qbo_sync_clear_all_mappings_customers',
					"clear_all_mappings_customers": jQuery('#clear_all_mappings_customers').val(),
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
 <?php echo $MWQS_OF->get_select2_js('.mw_wc_qbo_sync_select2','qbo_customer');?>