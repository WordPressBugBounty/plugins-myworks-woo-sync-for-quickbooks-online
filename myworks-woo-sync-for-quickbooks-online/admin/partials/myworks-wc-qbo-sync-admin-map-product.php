<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=product';

if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_product', 'map_wc_qbo_product' ) ) {
	$item_ids = array();
	foreach ($_POST as $key=>$value){
		if ($MSQS_QL->start_with($key, "map_product_")){
			$id = (int) str_replace("map_product_", "", $key);
			if($id){ //&& (int) $value
				$item_ids[$id] = (int) $value;
			}
		}
	}
	
	$table = $wpdb->prefix.'mw_wc_qbo_sync_product_pairs';
	
	if(count($item_ids)){
		foreach ($item_ids as $key=>$value){
			$save_data = array();			
			$save_data['quickbook_product_id'] = $value;
			$save_data['class_id'] = (isset($_POST['class_map_product_'.$key]))?$_POST['class_map_product_'.$key]:'';			
			
			if($MSQS_QL->get_field_by_val($table,'id','wc_product_id',$key)){
				$wpdb->update($table,$save_data,array('wc_product_id'=>$key),'',array('%d'));
			}else{
				$save_data['wc_product_id'] = $key;
				$wpdb->insert($table, $save_data);
			}
		}
		$MSQS_QL->set_session_val('map_page_update_message',__('Products mapped successfully.','mw_wc_qbo_sync'));
	}
	//
	$wpdb->query("DELETE FROM `".$table."` WHERE `quickbook_product_id` = 0 ");
	$MSQS_QL->redirect($page_url);
}

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('product_map_search');
$product_map_search = $MSQS_QL->get_session_val('product_map_search');

$MSQS_QL->set_and_get('product_type_srch');
$product_type_srch = $MSQS_QL->get_session_val('product_type_srch');

$MSQS_QL->set_and_get('product_um_srch');
$product_um_srch = $MSQS_QL->get_session_val('product_um_srch');

$total_records = $MSQS_QL->count_woocommerce_product_list($product_map_search,false,$product_type_srch,$product_um_srch);

$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);

$wc_product_list = $MSQS_QL->get_woocommerce_product_list($product_map_search," $offset , $items_per_page",false,$product_type_srch,$product_um_srch);

$qbo_product_options = '';
if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
	$qbo_product_options.= $MSQS_QL->get_product_dropdown_list('',false,true);
}

$qbo_class_options_value = $MSQS_QL->get_class_dropdown_list();
$qbo_class_options = '<option value=""></option>';
$qbo_class_options.= $qbo_class_options_value;

$selected_options_script = '';

$wc_p_types = wc_get_product_types();
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>

<div class="container map-product-responsive">
	<div class="page_title"><h4><?php _e( 'Product Mappings', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="mw_wc_filter">
	 <span class="search_text">Search</span>
	  &nbsp;
	  <input type="text" id="product_map_search" placeholder="NAME / SKU / ID" value="<?php echo $product_map_search;?>">
	  &nbsp;
									  
	  <span class="search_text">Product Type</span>
	  &nbsp;
	  <select id="product_type_srch" style="width:200px !important;">
		<option value="">All but parent variable products</option>
		<option value="all"<?php if($product_type_srch == 'all'){echo ' selected';}?>>All</option>
		<?php echo  $MSQS_QL->only_option($product_type_srch,$wc_p_types);?>
	  </select>
	  &nbsp;
		
	 <span>
		 <select title="Mapped/UnMapped" style="width:80px;" name="product_um_srch" id="product_um_srch">
			<?php if(empty($product_um_srch)):?>
			<option value="">All</option>
			<?php endif;?>
			<?php echo  $MSQS_QL->only_option($product_um_srch,array('only_um'=>'Only Unmapped','only_m'=>'Only Mapped'));?>
		 </select>
	 </span>
	 
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
	<div class="card">
		<div class="card-content">
			<div class="row">
				<?php if(is_array($wc_product_list) && count($wc_product_list)):?>
				<form method="POST" class="col s12 m12 l12" action="<?php echo $page_url;?>">
					<div class="row">
						<div class="col s12 m12 l12">
							<div class="myworks-wc-qbo-sync-table-responsive">
								<table class="mw-qbo-sync-settings-table menu-blue-bg menu-bg-a new-table" width="100%">
									<thead>
										<tr>
											<th width="5%">&nbsp; ID</th>
											<th width="35%">
												WooCommerce Product								    	
											</th>
											<th width="10%">
												SKU								    	
											</th>
											<th width="10%">Type</th>
											<th width="<?php echo !empty($qbo_class_options_value)?'20%':'40%' ?>">
												QuickBooks Product								    	
											</th>
											<?php if(!empty($qbo_class_options_value)){ ?>
											<th width="20%">
												QuickBooks Class
											</th>
											<?php } ?>
											</tr>
										</tr>
									</thead>
									<?php foreach($wc_product_list as $p_val):?>
									<tr>
										<td><?php echo $p_val['ID']?></td>
										<td>
										<b><a href="<?php echo admin_url('post.php?action=edit&post=').$p_val['ID'] ?>" target="_blank"><?php _e( $p_val['name'], 'mw_wc_qbo_sync' );?></b>
										</a>					
										</td>
										<td><?php echo $p_val['sku']?></td>
										<td><?php echo $p_val['wc_product_type'];?></td>
										<td>										
											
											<?php
											$dd_options = '<option value=""></option>';
											$dd_ext_class = '';
											if($MSQS_QL->option_checked('mw_wc_qbo_sync_select2_ajax')){
												$dd_ext_class = 'mwqs_dynamic_select';
												if((int) $p_val['quickbook_product_id']){												
													$dd_options = '<option value="'.$p_val['quickbook_product_id'].'">'.$MSQS_QL->escape(stripslashes($p_val['qp_name'])).'</option>';
												}
											}else{
												$dd_options.=$qbo_product_options;
												if((int) $p_val['quickbook_product_id']){
													$selected_options_script.='jQuery(\'#map_product_'.$p_val['ID'].'\').val(\''.(int) $p_val['quickbook_product_id'].'\');';												
												}
											}																		
											?>
											
											<select class="mw_wc_qbo_sync_select2 <?php echo $dd_ext_class;?>" name="map_product_<?php echo $p_val['ID']?>" id="map_product_<?php echo $p_val['ID']?>">
												<?php echo $dd_options;?>
											</select>
											
										</td>
										<?php if(!empty($qbo_class_options_value)){ ?>
										<td>
											<select class="mw_wc_qbo_sync_select2" name="class_map_product_<?php echo $p_val['ID']?>" id="class_map_product_<?php echo $p_val['ID']?>">
												<?php echo $qbo_class_options;?>
											</select>
											<?php 
											if(!empty($p_val['class_id'])){
												$selected_options_script.='jQuery(\'#class_map_product_'.$p_val['ID'].'\').val(\''.$p_val['class_id'].'\');';
											}
											?>	
										</td>
										<?php } ?>
									</tr>
									<?php endforeach;?>
								</table>
								<?php echo $pagination_links?>
							</div>
						</div>
					</div>
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_product', 'map_wc_qbo_product' ); ?>
						<div class="input-field col s12 m6 l4">
							<button class="waves-effect waves-light btn save-btn mw-qbo-sync-green">Save</button>
						</div>
					</div>
				</form>
				
				<br />
				<div class="col col-m">
				<h5><?php _e( 'Clear All Products Mappings', 'mw_wc_qbo_sync' );?></h5>
				<?php wp_nonce_field( 'myworks_wc_qbo_sync_clear_all_mappings_products', 'clear_all_mappings_products' ); ?>
				<button id="mwqs_capm_btn"><?php _e( 'Clear Mappings', 'mw_wc_qbo_sync' );?></button>
				&nbsp;
				<span id="mwqs_capm_msg"></span>
				</div>
				
				<?php else:?>
				
				<h4 class="mw_mlp_ndf">
					<?php _e( 'No available products to display.', 'mw_wc_qbo_sync' );?>
				</h4>
				<?php endif;?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function search_item(){		
		var product_map_search = jQuery('#product_map_search').val();
		product_map_search = jQuery.trim(product_map_search);
		
		var product_type_srch = jQuery('#product_type_srch').val();
		product_type_srch = jQuery.trim(product_type_srch);
		
		var product_um_srch = jQuery('#product_um_srch').val();
		product_um_srch = jQuery.trim(product_um_srch);
		
		if(product_map_search!='' || product_type_srch!='' || product_um_srch!=''){			
			window.location = '<?php echo $page_url;?>&product_map_search='+product_map_search+'&product_type_srch='+product_type_srch+'&product_um_srch='+product_um_srch;
		}else{
			alert('<?php echo $MWQS_OF->get_mpp_bs_msg($page_url);?>');
		}
	}
	
	function reset_item(){		
		window.location = '<?php echo $page_url;?>&product_map_search=&product_type_srch=&product_um_srch=';
	}
	<?php if($selected_options_script!=''):?>
	jQuery(document).ready(function(){
		<?php echo $selected_options_script;?>
	});
	<?php endif;?>
	
	jQuery(document).ready(function($){
		$('#mwqs_automap_products_wf_qf').click(function(){
			var pam_wf = $('#pam_wf').val().trim();
			var pam_qf = $('#pam_qf').val().trim();
			
			var mo_um = '';
			if($('#pam_moum_chk').is(':checked')){
				mo_um = 'true';
			}
			
			if(pam_wf!='' && pam_qf!=''){
				$('#pam_wqf_e_msg').html('');
				if(confirm('<?php echo __('This will override any previous product mappings, and scan your WooCommerce & QuickBooks Online products by selected fields to automatically match them for you.')?>')){
					var data = {
						"action": 'mw_wc_qbo_sync_automap_products_wf_qf',
						"automap_products_wf_qf": jQuery('#automap_products_wf_qf').val(),
						"pam_wf": pam_wf,
						"pam_qf": pam_qf,
						"mo_um": mo_um,
					};
					
					var loading_msg = 'Loading...';
					jQuery('#mwqs_automap_products_msg').html(loading_msg);
					
					jQuery.ajax({
					   type: "POST",
					   url: ajaxurl,
					   data: data,
					   cache:  false ,
					   //datatype: "json",
					   success: function(result){
						   if(result!=0 && result!=''){							
							jQuery('#mwqs_automap_products_msg').html(result);							
							window.location='<?php echo admin_url($page_url)?>';
						   }else{
							 jQuery('#mwqs_automap_products_msg').html('Automap was timed out and could not fully complete. Please try again');					
						   }				  
					   },
					   error: function(result) {							
							jQuery('#mwqs_automap_products_msg').html('Automap was timed out and could not fully complete. Please try again');
					   }
					});
					
				}
			}else{				
				$('#pam_wqf_e_msg').html('Please select automap fields.');
			}
		});
		
		<?php if($js_section=false):?>
		$('#mwqs_automap_products').click(function(){
			if(confirm('<?php echo __('This will override any previous product mappings, and scan your WooCommerce & QuickBooks Online products by SKU to automatically match them for you.')?>')){
				jQuery('#mwqs_automap_products_msg').html('');
				jQuery('#mwqs_automap_products_msg_by_name').html('');
				var data = {
					"action": 'mw_wc_qbo_sync_automap_products',
					"automap_products": jQuery('#automap_products').val(),
				};
				var loading_msg = 'Loading...';
				jQuery('#mwqs_automap_products_msg').html(loading_msg);
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					   if(result!=0 && result!=''){
						//alert(result);
						//jQuery('#mwqs_automap_products_msg').html('Success');
						jQuery('#mwqs_automap_products_msg').html(result);
						//alert('Success!');
						//location.reload();
						window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 jQuery('#mwqs_automap_products_msg').html('Error!');
						 //alert('Error!');			 
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_automap_products_msg').html('Error!');
				   }
				});
			}
		});
		
		$('#mwqs_automap_products_by_name').click(function(){
			if(confirm('<?php echo __('This will override any previous product mappings, and scan your WooCommerce & QuickBooks Online products by name to automatically match them for you.')?>')){
				jQuery('#mwqs_automap_products_msg_by_name').html('');
				jQuery('#mwqs_automap_products_msg').html('');
				var data = {
					"action": 'mw_wc_qbo_sync_automap_products_by_name',
					"automap_products_by_name": jQuery('#automap_products_by_name').val(),
				};
				var loading_msg = 'Loading...';
				jQuery('#mwqs_automap_products_msg_by_name').html(loading_msg);
				jQuery.ajax({
				   type: "POST",
				   url: ajaxurl,
				   data: data,
				   cache:  false ,
				   //datatype: "json",
				   success: function(result){
					   if(result!=0 && result!=''){
						//alert(result);
						//jQuery('#mwqs_automap_products_msg_by_name').html('Success');
						jQuery('#mwqs_automap_products_msg_by_name').html(result);
						//alert('Success!');
						//location.reload();
						window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 jQuery('#mwqs_automap_products_msg_by_name').html('Error!');
						 //alert('Error!');			 
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_automap_products_msg_by_name').html('Error!');
				   }
				});
			}
		});
		
		<?php endif;?>
		
		$('#mwqs_capm_btn').click(function(){
			if(confirm('<?php echo __('Are you sure, you want to clear all product mappings?','mw_wc_qbo_sync')?>')){
				var loading_msg = 'Loading...';
				jQuery('#mwqs_capm_msg').html(loading_msg);
				var data = {
					"action": 'mw_wc_qbo_sync_clear_all_mappings_products',
					"clear_all_mappings_products": jQuery('#clear_all_mappings_products').val(),
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
						 jQuery('#mwqs_capm_msg').html('Success!');
						 window.location='<?php echo admin_url($page_url)?>';
					   }else{
						 //alert('Error!');
						jQuery('#mwqs_capm_msg').html('Error!');
					   }				  
				   },
				   error: function(result) {  
						//alert('Error!');
						jQuery('#mwqs_capm_msg').html('Error!');
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
 <?php echo $MWQS_OF->get_select2_js('.mw_wc_qbo_sync_select2','qbo_product');?>