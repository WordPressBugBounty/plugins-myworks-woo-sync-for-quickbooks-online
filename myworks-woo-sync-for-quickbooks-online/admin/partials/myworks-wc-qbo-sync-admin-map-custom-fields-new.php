<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

if($MSQS_QL->option_checked('mw_wc_qbo_sync_pause_up_qbo_conection')){
	$MSQS_QL = new MyWorks_WC_QBO_Sync_QBO_Lib(true);	
}

$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=custom-fields';
$table = $wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map';

if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_cf', 'map_wc_qbo_cf' ) ) {
	
	//$MSQS_QL->_p($_POST);die;
	$wpdb->query("DELETE FROM `".$table."` WHERE `id` > 0 ");
	$wpdb->query("TRUNCATE TABLE `".$table."` ");
	
	if(isset($_POST['wq_mcf_wcf']) && is_array($_POST['wq_mcf_wcf']) && isset($_POST['wq_mcf_qcf']) && is_array($_POST['wq_mcf_qcf'])){
		$wq_mcf_wcf = array_map('trim',$_POST['wq_mcf_wcf']);
		$wq_mcf_wcf_tf = (isset($_POST['wq_mcf_wcf_tf']))?array_map('trim',$_POST['wq_mcf_wcf_tf']):array();
		
		//
		$wq_mcf_wcf_tf_ft = (isset($_POST['wq_mcf_wcf_tf_ft']))?array_map('trim',$_POST['wq_mcf_wcf_tf_ft']):array();
		$wq_mcf_wcf_tf_ev = (isset($_POST['wq_mcf_wcf_tf_ev']))?array_map('trim',$_POST['wq_mcf_wcf_tf_ev']):array();
		$ext_valid_ft = ['Date'];
		$ext_valid_ev = ['yyyy-mm-dd','dd-mm-yyyy','mm-dd-yyyy','yyyy/mm/dd','dd/mm/yyyy','mm/dd/yyyy','yy/mm/dd'];
		
		if(array_filter($wq_mcf_wcf)) {
			$wq_mcf_qcf = array_map('trim',$_POST['wq_mcf_qcf']);
			
			$values = array();
			$place_holders = array();
			$query = "INSERT INTO `{$table}` (wc_field, qb_field, ext_data) VALUES ";
			
			for($i = 0; $i < count($wq_mcf_wcf); $i++){
				if($wq_mcf_wcf[$i]!='' && isset($wq_mcf_qcf[$i]) && $wq_mcf_qcf[$i]!=''){
					if($wq_mcf_wcf[$i]=='mcf_wc_oth_cus_field_manual_add'){
						if(isset($wq_mcf_wcf_tf[$i]) && !empty($wq_mcf_wcf_tf[$i]) && $wq_mcf_wcf_tf[$i]!='mcf_wc_oth_cus_field_manual_add'){
							
							$ext_data = '';
							if(isset($wq_mcf_wcf_tf_ft[$i]) && !empty($wq_mcf_wcf_tf_ft[$i]) && in_array($wq_mcf_wcf_tf_ft[$i],$ext_valid_ft)){
								if(isset($wq_mcf_wcf_tf_ev[$i]) && !empty($wq_mcf_wcf_tf_ev[$i]) && in_array($wq_mcf_wcf_tf_ev[$i],$ext_valid_ev)){
									$ext_data_a = array('field_type'=>$wq_mcf_wcf_tf_ft[$i],'ext_val'=>$wq_mcf_wcf_tf_ev[$i]);
									$ext_data = serialize($ext_data_a);
								}
							}
							
							array_push($values, esc_sql($wq_mcf_wcf_tf[$i]), esc_sql($wq_mcf_qcf[$i]),$ext_data );
						}						
					}else{
						$ext_data = '';
						if(isset($wq_mcf_wcf_tf_ft[$i]) && !empty($wq_mcf_wcf_tf_ft[$i]) && in_array($wq_mcf_wcf_tf_ft[$i],$ext_valid_ft)){
							if(isset($wq_mcf_wcf_tf_ev[$i]) && !empty($wq_mcf_wcf_tf_ev[$i]) && in_array($wq_mcf_wcf_tf_ev[$i],$ext_valid_ev)){
								$ext_data_a = array('field_type'=>$wq_mcf_wcf_tf_ft[$i],'ext_val'=>$wq_mcf_wcf_tf_ev[$i]);
								$ext_data = serialize($ext_data_a);
							}
						}
						
						array_push($values, esc_sql($wq_mcf_wcf[$i]), esc_sql($wq_mcf_qcf[$i]),$ext_data);
					}					
					$place_holders[] = "('%s', '%s', '%s')";
				}				
			}
			$query .= implode(', ', $place_holders);
			//$MSQS_QL->_p($values);die;
			if(count($values)){
				$query = $wpdb->prepare("$query ", $values);
				//echo $query;die;
				$wpdb->query($query);
			}			
		}
	}
	$MSQS_QL->set_session_val('map_page_update_message',__('Custom fields mapped successfully.','mw_wc_qbo_sync'));
	$MSQS_QL->redirect($page_url);
}

$wc_avl_cf_list = $MSQS_QL->get_wc_avl_cf_map_fields();
$qbo_avl_cf_list = $MSQS_QL->get_qbo_avl_cf_map_fields();
//$MSQS_QL->_p($wc_avl_cf_list);
//$MSQS_QL->_p($qbo_avl_cf_list);

$wc_avl_cf_list_by_group = $MSQS_QL->get_wc_avl_cf_map_fields_by_group();
//$MSQS_QL->_p($wc_avl_cf_list_by_group);

$cf_map_data = $MSQS_QL->get_tbl($table);
//$MSQS_QL->_p($cf_map_data);
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>
<style type="text/css">
	select.mcf_select{float:none;width:220px;}
	optgroup[label="Billing"] {
        /*background: #FFFFFF;*/
		font-size: 12px;
    }
</style>
<div class="container map-tax-class-outer">
	<div class="page_title"><h4><?php _e( 'Custom Fields Mappings', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card">
		<div class="card-content">
			<div class="row mcf_cont">
				<form method="POST" class="col s12 m12 l12" action="<?php echo $page_url;?>">
					<div class="row">
						<div class="col s12 m12 l12">
						<div class="myworks-wc-qbo-sync-table-responsive">
							<table class="mw-qbo-sync-map-table menu-blue-bg" width="100%">
								<thead>
									<tr>									
										<th width="60%" class="title-description">
											WooCommerce Order Field
										</th>
										<th width="5%" >&nbsp;</th>
										<th width="30%" class="title-description">
											QuickBooks Online Invoice/Sales Receipt Field
										</th>
										<th width="5%" >&nbsp;</th>
									</tr>
								</thead>
								<tbody id="wq_mcf_tb">
									<?php if(is_array($cf_map_data) && count($cf_map_data)):?>
									<?php foreach($cf_map_data as $cfm_data):?>
									<tr>
										<td>
											<?php if(is_array($wc_avl_cf_list) && count($wc_avl_cf_list) && array_key_exists($cfm_data['wc_field'],$wc_avl_cf_list)):?>
												<select class="mcf_select" name="wq_mcf_wcf[]">
													<?php //echo $MSQS_QL->only_option($cfm_data['wc_field'],$wc_avl_cf_list);?>
													<?php
														if(is_array($wc_avl_cf_list_by_group) && count($wc_avl_cf_list_by_group)){
															foreach($wc_avl_cf_list_by_group as $waclbg){
																$og_s = (isset($waclbg['sub']))?'style="color:gray;"':'';
																
																echo '<optgroup '.$og_s.' label="'.$waclbg['title'].'">';
																if(isset($waclbg['fields']) && is_array($waclbg['fields']) && count($waclbg['fields'])){
																	foreach($waclbg['fields'] as $wcf_k => $wcf_v){
																		$selected = ($cfm_data['wc_field']==$wcf_k)?'selected':'';
																		echo '<option '.$selected.' value="'.$wcf_k.'">'.$wcf_v.'</option>';
																	}
																}
															}
														}
													?>
												</select>
											<?php else:?>
												<input type="text" value="<?php echo $cfm_data['wc_field'];?>" class="mcf_txt" name="wq_mcf_wcf[]"/>
											<?php endif;?>								
											
											<?php
											$ext_ft_ev_txt = '';
											$m_ed_field_n = '';
											$m_ed_field_type = '';
											$m_ed_ext_val = '';
											if(!empty($cfm_data['ext_data'])){
												$ext_data = $cfm_data['ext_data'];
												$ext_data = unserialize($ext_data);												
												if(is_array($ext_data) && !empty($ext_data)){
													if(isset($ext_data['field_type']) && isset($ext_data['ext_val'])){
														if(!empty($ext_data['field_type']) && !empty($ext_data['ext_val'])){
															$ext_ft_ev_txt = $ext_data['field_type'].' ('.$ext_data['ext_val'].')';
															$ext_ft_ev_txt = '&nbsp;<span>'.$ext_ft_ev_txt.'</span>';
															
															$m_ed_field_n = $cfm_data['wc_field'];
															$m_ed_field_type = $ext_data['field_type'];
															$m_ed_ext_val = $ext_data['ext_val'];
														}
													}
												}
											}
											echo $ext_ft_ev_txt;
											?>
											
											<input type="hidden" class="mcf_txt" name="wq_mcf_wcf_tf[]" value="<?php echo $m_ed_field_n;?>"/>
											
											<input type="hidden" class="mcf_txt" name="wq_mcf_wcf_tf_ft[]" value="<?php echo $m_ed_field_type;?>"/>
											<input type="hidden" class="mcf_txt" name="wq_mcf_wcf_tf_ev[]" value="<?php echo $m_ed_ext_val;?>"/>
											
										</td>
										<td></td>										
										<td>											
											<?php if(is_array($qbo_avl_cf_list) && count($qbo_avl_cf_list) && array_key_exists($cfm_data['qb_field'],$qbo_avl_cf_list)):?>
												<select class="mcf_select" name="wq_mcf_qcf[]">
													<?php echo $MSQS_QL->only_option($cfm_data['qb_field'],$qbo_avl_cf_list);?>
												</select>
											<?php else:?>
												<input type="text" value="<?php echo $cfm_data['qb_field'];?>" class="mcf_txt" name="wq_mcf_qcf[]"/>
											<?php endif;?>
										</td>
										<td><a href="#" class="remove_field">Remove</a></td>
									</tr>
									<?php endforeach;?>
									<?php endif;?>
								</tbody>
							</table>
							</div>
							<div style="padding:10px 0px 0px 20px;">
								<a data-cft="order" class="wq_mcf_afb" href="javascript:void(0)">Add Fields</a>
							</div>							
						</div>
					</div>
					<div class="row">
						<?php wp_nonce_field( 'myworks_wc_qbo_sync_map_wc_qbo_cf', 'map_wc_qbo_cf' ); ?>
						<div class="input-field col s12 m6 l4">
							<button id="mcf_sb" class="waves-effect waves-light btn save-btn mw-qbo-sync-green" disabled="disabled">Save</button>
						</div>
					</div>
				</form>
				
				<div id="wq_mcf_clone_fields" style="display:none;">
					<table>
					<tr>
						<td>
							<select class="mcf_select mcfws_nf" name="wq_mcf_wcf[]">
								<option value=""></option>
								<?php //echo $MSQS_QL->only_option('',$wc_avl_cf_list);?>
								<?php
									if(is_array($wc_avl_cf_list_by_group) && count($wc_avl_cf_list_by_group)){
										foreach($wc_avl_cf_list_by_group as $waclbg){
											$og_s = (isset($waclbg['sub']))?'style="color:gray;"':'';
											
											echo '<optgroup '.$og_s.' label="'.$waclbg['title'].'">';
											if(isset($waclbg['fields']) && is_array($waclbg['fields']) && count($waclbg['fields'])){
												foreach($waclbg['fields'] as $wcf_k => $wcf_v){													
													echo '<option value="'.$wcf_k.'">'.$wcf_v.'</option>';
												}
											}
										}
									}
								?>
								<option value="mcf_wc_oth_cus_field_manual_add">Others(Add manually)</option>
							</select>
							&nbsp;
							<input type="hidden" class="mcf_txt wmwt_cl" name="wq_mcf_wcf_tf[]"/>
							&nbsp;							
							<select title="Field Type" name="wq_mcf_wcf_tf_ft[]" class="mcf_select_e wmwt_cl_ft" style="float:none; display:none;">
								<option value="">Normal</option>
								<option value="Date">Date</option>
							</select>							
							&nbsp;
							<!--<input type="hidden" name="wq_mcf_wcf_tf_ev[]" class="mcf_txt_e wmwt_cl_ev"/>-->
							<select name="wq_mcf_wcf_tf_ev[]" class="mcf_select_e wmwt_cl_ev"  style="float:none; display:none;">
								<option value="yyyy-mm-dd">yyyy-mm-dd</option>
								<option value="dd-mm-yyyy">dd-mm-yyyy</option>
								<option value="mm-dd-yyyy">mm-dd-yyyy</option>
								
								<option value="yyyy/mm/dd">yyyy/mm/dd</option>
								<option value="dd/mm/yyyy">dd/mm/yyyy</option>
								<option value="mm/dd/yyyy">mm/dd/yyyy</option>
								
								<option value="yy/mm/dd">yy/mm/dd</option>
							</select>
						</td>
						<td></td>
						<td>
							<select class="mcf_select" name="wq_mcf_qcf[]">
								<option value=""></option>
								<?php echo $MSQS_QL->only_option('',$qbo_avl_cf_list);?>
							</select>
						</td>
						<td><a href="#" class="remove_field">Remove</a></td>
					</tr>
					</table>
				</div>
				
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function($){
		var max_fields = 1000;
		var x = <?php echo (int) count($cf_map_data);?>;
		
		if(x>0){
			jQuery('#mcf_sb').removeAttr('disabled');
		}
		jQuery('.wq_mcf_afb').click(function(e){
			e.preventDefault();
			var cft = jQuery(this).data('cft');
			jQuery('#mcf_sb').removeAttr('disabled');
			if(x < max_fields){
				x++;
				/*
				$("#wq_mcf_tb").append('<tr><td><input type="text" class="mcf_txt" name="wq_mcf_wcf[]"/></td><td></td><td><input type="text" class="mcf_txt"  name="wq_mcf_qcf[]"/></td><td><a href="#" class="remove_field">Remove</a></td></tr>');
				*/
				var na_fields = $('#wq_mcf_clone_fields').html();
				na_fields = na_fields.replace('<table>','').replace('<tbody>','').replace('</tbody>','').replace('</table>','');
				na_fields = na_fields.trim();
				//alert(na_fields);
				$("#wq_mcf_tb").append(na_fields);
			}else{
				alert('Max '+max_fields+' allowed.')
			}
		});	
		
		$("#wq_mcf_tb").on("click",".remove_field", function(e){ //user click on remove text
			e.preventDefault();			
			$(this).parent('td').parent('tr').remove();			
			//if(x==1){jQuery('#mcf_sb').attr('disabled','disabled');}
			x--;
		})
		
		$(document).on('change', '.mcfws_nf', function() {	
			if($(this).val()=='mcf_wc_oth_cus_field_manual_add'){
				$(this).next('input.wmwt_cl').attr('type','text');
				$(this).next('input.wmwt_cl').next('select.wmwt_cl_ft').show();
			}else{
				$(this).next('input.wmwt_cl').val('');
				$(this).next('input.wmwt_cl').attr('type','hidden');
				
				$(this).next('input.wmwt_cl').next('select.wmwt_cl_ft').val('');
				$(this).next('input.wmwt_cl').next('select.wmwt_cl_ft').hide();
				
				$(this).next('input.wmwt_cl').next('select.wmwt_cl_ft').next('select.wmwt_cl_ev').val('yyyy-mm-dd');
				$(this).next('input.wmwt_cl').next('select.wmwt_cl_ft').next('select.wmwt_cl_ev').hide();
			}
		});
		
		$(document).on('change', '.wmwt_cl_ft', function() {			
			if($(this).val()=='Date'){				
				$(this).next('select.wmwt_cl_ev').show();
			}else{
				$(this).next('select.wmwt_cl_ev').val('yyyy-mm-dd');
				$(this).next('select.wmwt_cl_ev').hide();
			}
		});
		
	});
</script>