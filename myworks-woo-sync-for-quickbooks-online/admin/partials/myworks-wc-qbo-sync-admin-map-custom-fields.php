<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

$page_url = 'admin.php?page=myworks-wc-qbo-map&tab=custom-fields';
$table = $wpdb->prefix.'mw_wc_qbo_sync_wq_cf_map';

$show_avl_fields = (isset($_GET['show_fields']) && $_GET['show_fields']=='1')?true:false;


if ( ! empty( $_POST ) && check_admin_referer( 'myworks_wc_qbo_sync_map_wc_qbo_cf', 'map_wc_qbo_cf' ) ) {
	
	//$MSQS_QL->_p($_POST);
	$wpdb->query("DELETE FROM `".$table."` WHERE `id` > 0 ");
	$wpdb->query("TRUNCATE TABLE `".$table."` ");
	
	if(isset($_POST['wq_mcf_wcf']) && is_array($_POST['wq_mcf_wcf']) && isset($_POST['wq_mcf_qcf']) && is_array($_POST['wq_mcf_qcf'])){
		$wq_mcf_wcf = array_map('trim',$_POST['wq_mcf_wcf']);
		if(array_filter($wq_mcf_wcf)) {
			$wq_mcf_qcf = array_map('trim',$_POST['wq_mcf_qcf']);
			
			$values = array();
			$place_holders = array();
			$query = "INSERT INTO `{$table}` (wc_field, qb_field) VALUES ";
			
			for($i = 0; $i < count($wq_mcf_wcf); $i++){
				if($wq_mcf_wcf[$i]!='' && isset($wq_mcf_qcf[$i]) && $wq_mcf_qcf[$i]!=''){
					array_push($values, esc_sql($wq_mcf_wcf[$i]), esc_sql($wq_mcf_qcf[$i]));
					$place_holders[] = "('%s', '%s')";
				}				
			}
			$query .= implode(', ', $place_holders);
			if(count($values)){
				$query = $wpdb->prepare("$query ", $values);
				//echo $query;
				$wpdb->query($query);
			}			
		}
	}
	$MSQS_QL->set_session_val('map_page_update_message',__('Custom fields mapped successfully.','mw_wc_qbo_sync'));
	$MSQS_QL->redirect($page_url);
}

$cf_map_data = $MSQS_QL->get_tbl($table);
?>
<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-map-nav.php' ?>
<div class="container map-tax-class-outer map-product-responsive">
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
										<th width="45%" class="title-description">
											WooCommerce Order Field
										</th>
										<th width="5%" >&nbsp;</th>
										<th width="45%" class="title-description">
											QuickBooks Online Invoice/Sales Receipt Field
										</th>
										<th width="5%" >&nbsp;</th>
									</tr>
								</thead>
								<tbody id="wq_mcf_tb">
									<?php if(is_array($cf_map_data) && count($cf_map_data)):?>
									<?php foreach($cf_map_data as $cfm_data):?>
									<tr>
										<td><input type="text" value="<?php echo $cfm_data['wc_field'];?>" class="mcf_txt" name="wq_mcf_wcf[]"/></td>
										<td></td>
										<td><input type="text" value="<?php echo $cfm_data['qb_field'];?>" class="mcf_txt"  name="wq_mcf_qcf[]"/></td>
										<td><a href="#" class="remove_field">Remove</a></td>
									</tr>
									<?php endforeach;?>
									<?php endif;?>
								</tbody>
							</table>
							<div style="padding:10px 0px 0px 20px;">
								<a data-cft="order" class="wq_mcf_afb" href="javascript:void(0)">Add Fields</a>
							</div>
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
			</div>
			
			<?php if($show_avl_fields):?>
			<div style="padding:5px;">
				<div style="text-align:right;">
					<a class="wq_mcf_avfl" href="javascript:void(0)">Show Available Fields</a>
				</div>
				
			<div id="avfl_cnt" style="display:none;">
					<table class="mw-qbo-sync-map-table" width="100%">
						<thead>
							<tr>									
								<th width="42%" class="title-description">
									WooCommerce Order Field
								</th>
								<th width="10%" >&nbsp;->&nbsp;</th>
								<th width="42%" class="title-description">
									QuickBooks Online Invoice/Sales Receipt Field
								</th>
								<th width="6%" >&nbsp;</th>
							</tr>
						</thead>
						
						<tbody>
							<tr>
								<td>shipping_details</td>
								<td></td>
								<td>customer_memo</td>
								<td></td>
							</tr>
							
							<tr>
								<td>shipping_method_name</td>
								<td></td>
								<td>ship_method</td>
								<td></td>
							</tr>
							
							<tr>
								<td>billing_phone</td>
								<td></td>
								<td>bill_addr,ship_addr</td>
								<td></td>
							</tr>
							
							<tr>
								<td>source</td>
								<td></td>
								<td>{qbo_custom_field_id},{qbo_custom_field_name}</td>
								<td>
									<div class="material-icons tooltipped tooltip">?
										<span class="tooltiptext">
											<?php _e( 'Please Enter QBO Custom Field ID and Name Like 2,Location', 'mw_wc_qbo_sync' );?>
										</span>
									</div>
								</td>
							</tr>
							
							<tr>
								<td>customval1</td>
								<td></td>
								<td>CustomerMemo</td>
								<td>
									<div class="material-icons tooltipped tooltip">?
										<span class="tooltiptext">
											<?php _e( 'This option will work if the WooCommerce Checkout Field Editor (woocommerce-checkout-field-editor) plugin is active', 'mw_wc_qbo_sync' );?>
										</span>
									</div>
								</td>
							</tr>
							
							<tr>
								<td>billing_purchaseorder</td>
								<td></td>
								<td>{qbo_custom_field_id},{qbo_custom_field_name}</td>
								<td>
									<div class="material-icons tooltipped tooltip">?
										<span class="tooltiptext">
											<?php _e( 'This option will work if the Woo Checkout Field Editor Pro (woo-checkout-field-editor-pro) plugin is active', 'mw_wc_qbo_sync' );?>
										</span>
									</div>
								</td>
							</tr>
							
							<tr>
								<td>NewWebOrder</td>
								<td></td>
								<td>{qbo_custom_field_id},{qbo_custom_field_name}</td>
								<td>
									<div class="material-icons tooltipped tooltip">?
										<span class="tooltiptext">
											<?php _e( 'This option will work if the WooCommerce Checkout Field Editor Pro (woocommerce-checkout-field-editor-pro) plugin is active', 'mw_wc_qbo_sync' );?>
										</span>
									</div>
								</td>
							</tr>
							
							<tr>
								<td>lead_source</td>
								<td></td>
								<td>{qbo_custom_field_id},{qbo_custom_field_name}</td>
								<td>
									<div class="material-icons tooltipped tooltip">?
										<span class="tooltiptext">
											<?php _e( 'This option will work if the WooCommerce Checkout Field Editor Pro (woocommerce-checkout-field-editor-pro) plugin is active', 'mw_wc_qbo_sync' );?>
										</span>
									</div>
								</td>
							</tr>
							
							<tr>
								<td>est_shipp_date</td>
								<td><small>yyyy-mm-dd</small></td>
								<td>ShipDate</td>
								<td>
									<div class="material-icons tooltipped tooltip">?
										<span class="tooltiptext">
											<?php _e( 'This option will work if the WooCommerce Checkout Field Editor Pro (woocommerce-checkout-field-editor-pro) plugin is active', 'mw_wc_qbo_sync' );?>
										</span>
									</div>
								</td>
							</tr>							
							
							
							<tr>
								<td>order_phone_number</td>
								<td></td>
								<td>{qbo_custom_field_id},{qbo_custom_field_name}</td>
								<td>
									<div class="material-icons tooltipped tooltip">?
										<span class="tooltiptext">
											<?php _e( 'Please Enter QBO Custom Field ID and Name Separated By Comma(,)', 'mw_wc_qbo_sync' );?>
										</span>
									</div>
								</td>
							</tr>
							
						</tbody>						
					</table>
			</div>
				
			</div>
			<?php endif;?>
			
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
				$("#wq_mcf_tb").append('<tr><td><input type="text" class="mcf_txt" name="wq_mcf_wcf[]"/></td><td></td><td><input type="text" class="mcf_txt"  name="wq_mcf_qcf[]"/></td><td><a href="#" class="remove_field">Remove</a></td></tr>');
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
		
		//
		<?php if($show_avl_fields):?>
		$('a.wq_mcf_avfl').click(function(){
			var link = $(this);
			$('#avfl_cnt').slideToggle('slow', function() {
				if ($(this).is(':visible')) {
					 link.text('Hide Available Fields');                
				} else {
					 link.text('Show Available Fields');             
				}        
			});       
		});
		
		 $("a.wq_mcf_avfl").trigger("click");
		<?php endif;?>
	});
</script>