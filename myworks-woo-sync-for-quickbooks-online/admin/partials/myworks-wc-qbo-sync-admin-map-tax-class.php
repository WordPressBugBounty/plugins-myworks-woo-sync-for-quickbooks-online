<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

/*
if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}
*/

$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=tax-class';

if($MSQS_QL->use_new_qbo_local_data('taxcode') && $MSQS_QL->get_option('mw_wc_qbo_sync_app_setting_qbo_taxcodes_data_fetched') != 'true'){
	# Fetch and save new QBO tax codes into DB
	$MSQS_QL->save_all_taxcodes();
	update_option('mw_wc_qbo_sync_app_setting_qbo_taxcodes_data_fetched','true',false);
}

if (! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_tax', 'map_wc_qbo_tax' ) && current_user_can( 'manage_options' ) ) {
	$item_ids = array();
	$item_ids_combo = array();
	//$MSQS_QL->_p($_POST);die;
	foreach ($_POST as $key=>$value){
		$key = sanitize_text_field(wp_unslash($key));
		$value = sanitize_text_field(wp_unslash($value));
		if ($MSQS_QL->start_with($key, "wtax_")){
			$id = (int) str_replace("wtax_", "", $key);			
			//if($id && (int) $value){}
			$item_ids[$id] = (int) $value;
		}
		
		if ($MSQS_QL->start_with($key, "cobmbo_wtax_")){
			$id = (int) str_replace("cobmbo_wtax_", "", $key);			
			//if($id && (int) $value){}
			$item_ids_combo[$id] = (int) $value;
		}	
	}
	//$MSQS_QL->_p($item_ids);$MSQS_QL->_p($item_ids_combo);die;
	$is_tax_saved = false;
	$table = $wpdb->prefix.'mw_wc_qbo_sync_tax_map';
	// Validate table name prefix to ensure it's safe
	if (strpos($table, $wpdb->prefix) !== 0) {
		return;
	}
	/*
	$wpdb->query("DELETE FROM `".$table."` WHERE `id` > 0 ");
	$wpdb->query("TRUNCATE TABLE `".$table."` ");
	*/
	if(count($item_ids)){
		foreach ($item_ids as $key=>$value){
			$save_data = array();
			$save_data['wc_tax_id'] = $key;
			$save_data['qbo_tax_code'] = $value;
			$save_data['wc_tax_id_2'] = 0;
			
			//Update
			// Validate table name prefix to ensure it's safe
			if (strpos($table, $wpdb->prefix) === 0) {
				$ch_q = $wpdb->prepare("SELECT `id` FROM `" . esc_sql($table) . "` WHERE `wc_tax_id` = %d AND `wc_tax_id_2` = %d ",$save_data['wc_tax_id'],$save_data['wc_tax_id_2']);
				
				$ch_data = $MSQS_QL->get_row($ch_q);
				if(is_array($ch_data) && count($ch_data)){
					unset($save_data['wc_tax_id']);
					unset($save_data['wc_tax_id_2']);
					$wpdb->update($table,$save_data,array('id'=>$ch_data['id']),'',array('%d'));
				}else{
					$wpdb->insert($table, $save_data);
				}
			}			
		}
		$is_tax_saved = true;		
	}
	
	if(count($item_ids_combo)){		
		foreach ($item_ids_combo as $key=>$value){
			$save_data = array();
			$save_data['wc_tax_id'] = $key;
			$save_data['qbo_tax_code'] = $value;
			$save_data['wc_tax_id_2'] = (isset($_POST['sc_wtax_'.$key]))?(int) sanitize_text_field(wp_unslash($_POST['sc_wtax_'.$key])):0;
			
			if($save_data['wc_tax_id_2'] < 1){
				// Validate table name prefix to ensure it's safe
			if (strpos($table, $wpdb->prefix) === 0) {
				$wpdb->query($wpdb->prepare("DELETE FROM `" . esc_sql($table) . "` WHERE `wc_tax_id` = %d AND `wc_tax_id_2` > 0", $key));
			}
				continue;
			}
			
			//Update
			// Validate table name prefix to ensure it's safe
			if (strpos($table, $wpdb->prefix) === 0) {
				$ch_q = $wpdb->prepare("SELECT `id` FROM `" . esc_sql($table) . "` WHERE `wc_tax_id` = %d AND `wc_tax_id_2` = %d ",$save_data['wc_tax_id'],$save_data['wc_tax_id_2']);
				
				$ch_data = $MSQS_QL->get_row($ch_q);
				if(is_array($ch_data) && count($ch_data)){
					unset($save_data['wc_tax_id']);
					unset($save_data['wc_tax_id_2']);
					$wpdb->update($table,$save_data,array('id'=>$ch_data['id']),'',array('%d'));
				}else{
					$wpdb->insert($table, $save_data);
				}
			}
		}
		$is_tax_saved = true;
	}
	if($is_tax_saved){
		$MSQS_QL->set_session_val('map_page_update_message',__('Tax rates mapped successfully.','mw_wc_qbo_sync'));
	}
	
	// Use prepared statement for safer SQL construction
	// Validate table name prefix to ensure it's safe
	if (strpos($table, $wpdb->prefix) === 0) {
		$table_name = esc_sql($table);
		$tax_rates_table = esc_sql($wpdb->prefix . 'woocommerce_tax_rates');
		$wpdb->query($wpdb->prepare("DELETE FROM `$table_name` WHERE `qbo_tax_code` = %d OR `qbo_tax_code` = %s OR wc_tax_id NOT IN(SELECT `tax_rate_id` FROM `$tax_rates_table`) OR (wc_tax_id_2 > %d AND wc_tax_id_2 NOT IN(SELECT `tax_rate_id` FROM `$tax_rates_table`))", 0, '', 0)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names safely escaped with esc_sql()
	}
	
	##
	$MSQS_QL->set_and_post('sh_aps_sec');
	
	$MSQS_QL->redirect($page_url);
}

##
$sh_aps_sec = $MSQS_QL->get_session_val('sh_aps_sec');

$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('tax_map_search');
$tax_map_search = $MSQS_QL->get_session_val('tax_map_search');
// Additional sanitization for search input
if (isset($_GET['tax_map_search'])) {
	$tax_map_search = sanitize_text_field(wp_unslash($_GET['tax_map_search']));
}

//$wc_tax_classes = WC_Tax::get_tax_classes();
//$wc_tax_rates = $MSQS_QL->get_tbl($wpdb->prefix.'woocommerce_tax_rates','','','tax_rate_class ASC');
$wc_tax_rates_a = $MSQS_QL->get_tbl($wpdb->prefix.'woocommerce_tax_rates','','','tax_rate_class ASC');
$wc_tax_rates_a = $MSQS_QL->get_wc_tax_rates_a_lc_add($wc_tax_rates_a);
//$MSQS_QL->_p($wc_tax_rates_a);

$tax_map_search = sanitize_text_field(wp_unslash($tax_map_search));
$tax_map_search = $MSQS_QL->sanitize($tax_map_search);
$whr = '';

$wtr_t = $wpdb->prefix.'woocommerce_tax_rates';
$wtr_lt = $wpdb->prefix.'woocommerce_tax_rate_locations';

// Validate table names for security
if (strpos($wtr_t, $wpdb->prefix) !== 0 || strpos($wtr_lt, $wpdb->prefix) !== 0) {
	$wc_tax_rates = array();
	$total_records = 0;
	$pagination_links = '';
} else {

$join = " LEFT JOIN `{$wtr_lt}` trl ON (tr.tax_rate_id = trl.tax_rate_id AND trl.location_type = 'city') ";

if($tax_map_search!=''){
	//$whr.=" AND (`tax_rate_name` LIKE '%$tax_map_search%' OR `tax_rate_class` LIKE '%$tax_map_search%' ) ";
	$escaped_search = '%' . $wpdb->esc_like($tax_map_search) . '%';
	$whr.=$wpdb->prepare(" AND (tr.`tax_rate_name` LIKE %s OR tr.`tax_rate_class` LIKE %s OR trl.`location_code` LIKE %s ) ",$escaped_search,$escaped_search,$escaped_search);
	// OR `tax_rate_country` LIKE '%$tax_map_search%' OR `tax_rate_state` LIKE '%$tax_map_search%'
}

// Use prepared statements for safer SQL queries
$wtr_t_safe = $wpdb->prefix . 'woocommerce_tax_rates';
$wtr_lt_safe = $wpdb->prefix . 'woocommerce_tax_rate_locations';

// Validate table names
if (strpos($wtr_t_safe, $wpdb->prefix) !== 0 || strpos($wtr_lt_safe, $wpdb->prefix) !== 0) {
	$wc_tax_rates = array();
	$total_records = 0;
} else {
	$join = " LEFT JOIN `{$wtr_lt_safe}` trl ON (tr.tax_rate_id = trl.tax_rate_id AND trl.location_type = 'city') ";
	
	if ($whr !== '') {
		$total_records_query = "SELECT COUNT(*) FROM `{$wtr_t_safe}` tr {$join} WHERE tr.`tax_rate_id` > 0 {$whr}";
		$tax_query = "SELECT tr.* , trl.location_code FROM `{$wtr_t_safe}` tr {$join} WHERE tr.`tax_rate_id` > 0 {$whr} ORDER BY tr.`tax_rate_class` ASC LIMIT %d, %d"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	} else {
		$total_records_query = "SELECT COUNT(*) FROM `{$wtr_t_safe}` WHERE `tax_rate_id` > 0";
		$tax_query = "SELECT tr.* , trl.location_code FROM `{$wtr_t_safe}` tr {$join} WHERE tr.`tax_rate_id` > 0 ORDER BY tr.`tax_rate_class` ASC LIMIT %d, %d"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
	
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query safely constructed with validated table names
	$total_records = $wpdb->get_var($total_records_query);
	$offset = $MSQS_QL->get_offset($MSQS_QL->get_page_var(),$items_per_page);
	
	if ($whr !== '') {
		$wc_tax_rates = $MSQS_QL->get_data($tax_query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	} else {
		$prepared_tax_query = $wpdb->prepare($tax_query, $offset, $items_per_page); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wc_tax_rates = $MSQS_QL->get_data($prepared_tax_query);
	}
}

$pagination_links = $MSQS_QL->get_paginate_links($total_records,$items_per_page);
//$MSQS_QL->_p($wc_tax_rates);

$qbo_tax_options = '<option value=""></option>';
$qbo_tax_options.=$MSQS_QL->get_tax_code_dropdown_list();

$selected_options_script = '';
$wc_all_tax_rates = $MSQS_QL->get_wc_tax_rate_id_array($wc_tax_rates_a);
// Validate table name for security
$map_table = $wpdb->prefix.'mw_wc_qbo_sync_tax_map';
if (strpos($map_table, $wpdb->prefix) === 0) {
	$tm_map_data = $MSQS_QL->get_tbl($map_table);
} else {
	$tm_map_data = array();
}
if(is_array($tm_map_data) && count($tm_map_data)){
	foreach($tm_map_data as $tm_k=>$tm_val){
		if($tm_val['wc_tax_id_2']>0){
			$tl_tax_rate_class = (isset($wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate_class']))?$wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate_class']:'';
			$tl_tax_rate_class = ($tl_tax_rate_class=='')?'Standard rate':ucfirst(str_replace('-',' ',$tl_tax_rate_class));
			$tl_city = (isset($wc_all_tax_rates[$tm_val['wc_tax_id_2']]['location_code']))?$wc_all_tax_rates[$tm_val['wc_tax_id_2']]['location_code']:'';
			$tl_country = (isset($wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate_country']))?$wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate_country']:'';
			$tl_state = (isset($wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate_state']))?$wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate_state']:'';
			$tl_taxrate = (isset($wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate']))?$wc_all_tax_rates[$tm_val['wc_tax_id_2']]['tax_rate']:'';
			
			$selected_options_script.='jQuery(\'#sc_wtax_'.esc_js($tm_val['wc_tax_id']).'\').val(\''.esc_js($tm_val['wc_tax_id_2']).'\');';
			$selected_options_script.='jQuery(\'#cobmbo_wtax_'.esc_js($tm_val['wc_tax_id']).'\').val(\''.esc_js($tm_val['qbo_tax_code']).'\');';
			
			$selected_options_script.='jQuery(\'#tl_tax_rate_class_'.esc_js($tm_val['wc_tax_id']).'\').html(\''.esc_js($tl_tax_rate_class).'\');';
			$selected_options_script.='jQuery(\'#tl_city_'.esc_js($tm_val['wc_tax_id']).'\').html(\''.esc_js($tl_city).'\');';
			$selected_options_script.='jQuery(\'#tl_country_'.esc_js($tm_val['wc_tax_id']).'\').html(\''.esc_js($tl_country).'\');';
			$selected_options_script.='jQuery(\'#tl_state_'.esc_js($tm_val['wc_tax_id']).'\').html(\''.esc_js($tl_state).'\');';
			$selected_options_script.='jQuery(\'#tl_taxrate_'.esc_js($tm_val['wc_tax_id']).'\').html(\''.esc_js($tl_taxrate).'\');';
		}else{
			$selected_options_script.='jQuery(\'#wtax_'.esc_js($tm_val['wc_tax_id']).'\').val(\''.esc_js($tm_val['qbo_tax_code']).'\');';
		}		
	}	
}

}
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>

<div class="container map-tax-class-outer map-product-responsive">
	<div class="page_title flex-box">
	<h4><?php esc_html_e( 'Tax Mappings', 'mw_wc_qbo_sync' );?></h4>
		<div class="dashboard_main_buttons p-mapbtn">			
			<?php if($sh_aps_sec != 'show'):?>
			<button class="sh_compound_tx show_advanced_payment_sync">Show Compound Taxes</button>
			<?php else:?>
			<button class="sh_compound_tx hide_advanced_payment_sync">Hide Compound Taxes</button>
			<?php endif;?>
		</div>
	</div>
	<div class="mw_wc_filter">
	 <span class="search_text">Search</span>
	  &nbsp;
	  <input type="text" id="tax_map_search" value="<?php echo esc_attr($tax_map_search);?>">
	  &nbsp;		
	  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
	  &nbsp;
	  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
	  &nbsp;
	  <span class="filter-right-sec">
		  <span class="entries">Show entries</span>
		  &nbsp;
		  <select style="width:50px;" onchange="javascript:window.location='<?php echo esc_url_raw($page_url);?>&<?php echo esc_attr($MSQS_QL->per_page_keyword);?>='+this.value;">
			<?php echo wp_kses($MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page) ?: '', array('option' => array('value' => array(), 'selected' => array())));?>
		 </select>
	 </span>
	 </div>
	 
	<div class="card">
		<div class="card-content">
			<div class="row">
				<?php if(is_array($wc_tax_rates) && count($wc_tax_rates)):?>
				<form method="POST" class="col s12 m12 l12" action="<?php echo esc_url($page_url);?>">
					<div class="row">
						<div class="col s12 m12 l12">
							<div class="myworks-wc-qbo-sync-table-responsive">
								<table class="mw-qbo-sync-map-table menu-blue-bg" width="100%">
	                            	<thead>
	                                	<tr>
	                                    	<th width="5%" class="title-description" id="th_id">
												ID							    	
											</th>
	                                        <th width="25%" class="title-description" id="th_tn">
												Tax	Name							    	
	                                        </th>
	                                        <th width="10%" class="title-description" id="th_tc">
	                                            Tax	Class						    	
	                                        </th>
											<th width="10%" class="title-description" id="th_ct">
	                                            City								    	
	                                        </th>
	                                        <th width="10%" class="title-description" id="th_cn">
	                                            Country								    	
	                                        </th>
	                                        <th width="10%" class="title-description" id="th_st">
	                                            State								    	
	                                        </th>
	                                        <th width="10%" class="title-description" id="th_rt">
	                                            Rate								    	
	                                        </th>
	                                        <th width="20%" class="title-description" id="th_qt">
	                                            QuickBooks Tax
	                                        </th>
	                                    </tr>
	                                </thead>

									<?php 
									foreach($wc_tax_rates as $rates):
									$tax_rate_class = ($rates['tax_rate_class']=='')?'Standard rate':ucfirst(str_replace('-',' ',$rates['tax_rate_class']));
									?>
									<tr>
										<td><?php echo esc_html($rates['tax_rate_id']);?></td>
										<td><?php echo esc_html($rates['tax_rate_name']);?></td>
										<td><?php echo esc_html($tax_rate_class);?></td>
										<td><?php echo esc_html($rates['location_code']);?></td>
										<td><?php echo esc_html($rates['tax_rate_country']);?></td>
										<td><?php echo esc_html($rates['tax_rate_state']);?></td>
										<td><?php echo esc_html($rates['tax_rate']);?></td>
										<td>
										<select class="mw_wc_qbo_sync_select2 qbo_select" name="wtax_<?php echo esc_attr($rates['tax_rate_id']);?>" id="wtax_<?php echo esc_attr($rates['tax_rate_id']);?>">
										<?php echo wp_kses($qbo_tax_options, array('option' => array('value' => array())));?>
										</select>							
										</td>
									</tr>
									<tr id="sc_tx_row_<?php echo esc_attr($rates['tax_rate_id']);?>" class="crs_tr" <?php if($sh_aps_sec!='show'){echo 'style="display:none;"';}?>>
										<td>+&nbsp;</td>
										<td>
										<?php echo esc_html($rates['tax_rate_name']);?><br />
										<select class="qbo_select mw_wc_qbo_sync_select2 sc_sel_tx" name="sc_wtax_<?php echo esc_attr($rates['tax_rate_id']);?>" id="sc_wtax_<?php echo esc_attr($rates['tax_rate_id']);?>">
											<?php echo wp_kses($MSQS_QL->get_wc_tax_rate_dropdown($wc_tax_rates_a,'',$rates['tax_rate_id']), array('option' => array('value' => array(), 'data-tax_rate_class' => array(), 'data-tax_rate_city' => array(), 'data-tax_rate_country' => array(), 'data-tax_rate_state' => array(), 'data-tax_rate' => array())));?>
										</select>
										</td>
										
										<td id="tl_tax_rate_class_<?php echo esc_attr($rates['tax_rate_id']);?>"></td>
										<td id="tl_city_<?php echo esc_attr($rates['tax_rate_id']);?>"></td>
										<td id="tl_country_<?php echo esc_attr($rates['tax_rate_id']);?>"></td>
										<td id="tl_state_<?php echo esc_attr($rates['tax_rate_id']);?>"></td>
										<td id="tl_taxrate_<?php echo esc_attr($rates['tax_rate_id']);?>"></td>
										<td>
											<select class="qbo_select mw_wc_qbo_sync_select2" name="cobmbo_wtax_<?php echo esc_attr($rates['tax_rate_id']);?>" id="cobmbo_wtax_<?php echo esc_attr($rates['tax_rate_id']);?>">
												<?php echo wp_kses($qbo_tax_options, array('option' => array('value' => array())));?>
											</select>
										</td>
									</tr>
									<?php endforeach;?>
								</table>
								 <?php echo !empty($pagination_links) ? $pagination_links : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
					</div>
					
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_tax', 'map_wc_qbo_tax' ); ?>
						<input type="hidden" name="sh_aps_sec" id="sh_aps_sec" value="">
						<div class="input-field col s12 m6 l4">
							<button class="waves-effect waves-light btn save-btn mw-qbo-sync-green">Save</button>
						</div>
					</div>
					
				</form>
				<?php else:?>
				
				<h4 class="mw_mlp_ndf">
					<?php esc_html_e( 'No available taxes to display.', 'mw_wc_qbo_sync' );?>
				</h4>
				<?php endif;?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	function search_item(){		
		var tax_map_search = jQuery('#tax_map_search').val();
		tax_map_search = jQuery.trim(tax_map_search);
		if(tax_map_search!=''){
			window.location = '<?php echo esc_url_raw($page_url);?>&tax_map_search='+encodeURIComponent(tax_map_search);
		}else{
			alert('<?php echo esc_js(__('Please enter search keyword.','mw_wc_qbo_sync')); ?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo esc_url_raw($page_url);?>&tax_map_search=';
	}
	
	jQuery(document).ready(function($){
		jQuery('.sc_sel_tx').change(function(){			
			var p_tx = jQuery(this).attr('id');
			p_tx = p_tx.replace('sc_wtax_','');
			
			var tx_val = $('option:selected', this).val();
					
			if(tx_val!=''){				
				var tax_rate_class = $('option:selected', this).attr('data-tax_rate_class');
				if(!tax_rate_class.trim()){
					tax_rate_class = 'Standard rate';
				}
				
				var tx_city = $('option:selected', this).attr('data-tax_rate_city');
				var tx_country = $('option:selected', this).attr('data-tax_rate_country');
				var tx_state = $('option:selected', this).attr('data-tax_rate_state');
				var tx_taxrate = $('option:selected', this).attr('data-tax_rate');
				
				jQuery('#tl_tax_rate_class_'+p_tx).html(tax_rate_class);
				jQuery('#tl_city_'+p_tx).html(tx_city);
				jQuery('#tl_country_'+p_tx).html(tx_country);
				jQuery('#tl_state_'+p_tx).html(tx_state);
				jQuery('#tl_taxrate_'+p_tx).html(tx_taxrate);
			}else{
				jQuery('#tl_tax_rate_class_'+p_tx).html('');
				jQuery('#tl_city_'+p_tx).html('');
				jQuery('#tl_country_'+p_tx).html('');
				jQuery('#tl_state_'+p_tx).html('');
				jQuery('#tl_taxrate_'+p_tx).html('');
			}
		});
		<?php if($selected_options_script!=''):?>		
			<?php echo wp_kses($selected_options_script, array());?>		
		<?php endif;?>
		
		jQuery('.sh_compound_tx').click(function(){
			var crs = jQuery(this).text();			
			if(crs=='Show Compound Taxes'){
				jQuery('#sh_aps_sec').val('show');
				jQuery(this).addClass('hide_advanced_payment_sync').removeClass('show_advanced_payment_sync');
				
				$('#th_id').attr('width','20%');$('#th_tn').attr('width','19%');$('#th_tc').attr('width','10%');$('#th_ct').attr('width','10%');
				$('#th_cn').attr('width','7%');$('#th_st').attr('width','7%');$('#th_rt').attr('width','7%');$('#th_qt').attr('width','20%');
				
				jQuery('.crs_tr').show();			
				jQuery(this).text('Hide Compound Taxes');	
			}
			
			if(crs=='Hide Compound Taxes'){
				jQuery('#sh_aps_sec').val('hide');
				jQuery(this).addClass('show_advanced_payment_sync').removeClass('hide_advanced_payment_sync');
					
				$('#th_id').attr('width','5%');$('#th_tn').attr('width','25%');$('#th_tc').attr('width','10%');$('#th_ct').attr('width','10%');
				$('#th_cn').attr('width','10%');$('#th_st').attr('width','10%');$('#th_rt').attr('width','10%');$('#th_qt').attr('width','20%');
				
				jQuery('.crs_tr').hide();				
				jQuery(this).text('Show Compound Taxes');
			}
		});
	});				
</script>
<?php echo $MWQS_OF->get_select2_js('.mw_wc_qbo_sync_select2'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already sanitized in get_select2_js function ?>