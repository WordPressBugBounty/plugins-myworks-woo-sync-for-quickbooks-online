<?php
if ( ! defined( 'ABSPATH' ) )
     exit;

// Security: Check user capabilities
if ( ! current_user_can( 'manage_woocommerce' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
}

// Security: Validate required globals exist
if ( ! isset( $MWQS_OF ) || ! isset( $MSQS_QL ) || ! isset( $wpdb ) ) {
    wp_die( esc_html__( 'Required components are not available.' ) );
}

 $page_url = 'admin.php?page=myworks-wc-qbo-push&tab=vendor';
 
 // Security: Generate nonce for CSRF protection
 $push_vendor_nonce = wp_create_nonce( 'push_vendor_action' );
 
 global $MWQS_OF;
 global $MSQS_QL;
 global $wpdb;
 
$MSQS_QL->set_per_page_from_url();
$items_per_page = $MSQS_QL->get_item_per_page();

$MSQS_QL->set_and_get('cl_push_search');
$cl_push_search = $MSQS_QL->get_session_val('cl_push_search');

// Security: Sanitize search input
$cl_push_search = sanitize_text_field( wp_unslash( $cl_push_search ) );

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
		// Security: Validate data array and sanitize values
		if( isset( $data['qbo_vendorid'] ) && absint( $data['qbo_vendorid'] ) > 0 ){
			$cust_item_ids_arr[] = "'" . absint( $data['qbo_vendorid'] ) . "'";
		}		
	}
	if( ! empty( $cust_item_ids_arr ) ) {
		$push_map_data_arr = $MSQS_QL->get_push_vendor_map_data($cust_item_ids_arr);
	}
	//$MSQS_QL->_p($push_map_data_arr);
}

?>
<style>
	.sstchc{display:none;}
</style>
<div class="container">
	<div class="page_title"><h4><?php esc_html_e( 'Vendor Push', 'mw_wc_qbo_sync' );?></h4></div>
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
									  <input type="text" id="cl_push_search" value="<?php echo esc_attr( $cl_push_search );?>">
									  &nbsp;		
									  <button onclick="javascript:search_item();" class="btn btn-info">Filter</button>
									  &nbsp;
									  <button onclick="javascript:reset_item();" class="btn btn-info">Reset</button>
									  &nbsp;
									  <span class="filter-right-sec">
										  <span class="entries">Show entries</span>
										  &nbsp;
										  <select style="width:50px;" onchange="javascript:window.location='<?php echo esc_url_raw($page_url);?>&<?php echo esc_js( $MSQS_QL->per_page_keyword );?>='+this.value;">
											<?php echo wp_kses( $MSQS_QL->only_option($items_per_page,$MSQS_QL->show_per_page), array( 'option' => array( 'value' => array(), 'selected' => array() ) ) );?>
										 </select>
									 </span>
									 </div>
									 <br />
									 <div class="row">
										<div class="input-field col s12 m12 14">
											<button id="push_selected_vendor_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green"><?php echo esc_html__('Push Selected Vendors','mw_wc_qbo_sync')?></button>
											<button id="push_all_vendor_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo esc_html__('Push All Vendors','mw_wc_qbo_sync')?></button>
											<button disabled="disabled" id="push_all_unsynced_vendor_btn" class="waves-effect waves-light btn save-btn mw-qbo-sync-green hide"><?php echo esc_html__('Push Un-synced Vendors','mw_wc_qbo_sync')?></button>
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
														<th width="5%" <?php echo esc_attr($sstchc);?>><?php echo wp_kses( __( 'Sync<br>Status', 'mw_wc_qbo_sync' ), array( 'br' => array() ) ); ?></th>
													</tr>
												</thead>
												<tbody>
												<?php if(count($cl_push_data)):?>
												<?php foreach($cl_push_data as $data):?>
												<?php
												// Security: Initialize variables and validate data
												$sync_status_html = '';
												if($show_sync_status){
													$sync_status_html = '<i class="fa fa-times-circle" style="color:red"></i>';
													if( isset( $data['qbo_vendorid'] ) && absint( $data['qbo_vendorid'] ) > 0 ){
														$qbo_vendorid = absint( $data['qbo_vendorid'] );
														if(is_array($push_map_data_arr) && in_array($qbo_vendorid,$push_map_data_arr)){
															$qbo_href = $MSQS_QL->get_push_qbo_view_href('Vendor',$qbo_vendorid);
															$sync_status_html = '<i title="' . esc_attr( 'Mapped to #' . absint( $data['qbo_vendorid'] ) . ' - Click to view it in QuickBooks Online' ) . '" class="fa fa-check-circle" style="color:green"></i>';
															$sync_status_html = '<a target="_blank" href="' . esc_url( $qbo_href ) . '">' . $sync_status_html . '</a>';
														}												
													}
												}
												
												// Security: Initialize and validate supplier data
												$sup_id = 0;
												$sup_name = '';
												if( isset( $data['ID'] ) && absint( $data['ID'] ) > 0 ) {
													$supplier_dtls = $MSQS_QL->get_atum_supplier_dtls_from_wc_vendor_usr_id( absint( $data['ID'] ) );
													if(is_array($supplier_dtls) && count($supplier_dtls)){
														$sup_id = isset( $supplier_dtls['ID'] ) ? absint( $supplier_dtls['ID'] ) : 0;
														$sup_name = isset( $supplier_dtls['post_title'] ) ? sanitize_text_field( $supplier_dtls['post_title'] ) : '';
													}
												}
												?>
												<tr>
													<td>
													<?php if($sup_id>0):?>
													<input type="checkbox" id="cl_push_<?php echo esc_attr( absint( isset( $data['ID'] ) ? $data['ID'] : 0 ) )?>">
													<?php endif;?>
													</td>
													<td><?php echo esc_html( absint( isset( $data['ID'] ) ? $data['ID'] : 0 ) )?></td>
													
													<td><a href="<?php echo esc_url( admin_url('user-edit.php?user_id=' . absint( isset( $data['ID'] ) ? $data['ID'] : 0 ) ) ) ?>" target="_blank"><?php echo esc_html( isset( $data['display_name'] ) ? $data['display_name'] : '' )?></a></td>												
													
													<td><?php echo esc_html( isset( $data['first_name'] ) ? $data['first_name'] : '' )?></td>
													<td><?php echo esc_html( isset( $data['last_name'] ) ? $data['last_name'] : '' )?></td>
													<td><?php echo esc_html( isset( $data['user_email'] ) ? $data['user_email'] : '' )?></td>
													<td><?php echo esc_html( isset( $data['billing_company'] ) ? $data['billing_company'] : '' )?></td>
													
													<td>
														<?php if($sup_id>0):?>
														<a href="<?php echo esc_url( admin_url('post.php?post=' . absint( $sup_id ) . '&action=edit') )?>" target="_blank"><?php echo esc_html( $sup_name );?></a>
														<?php endif;?>
													</td>
													
													<td <?php echo esc_attr( $sstchc );?>><?php echo $sync_status_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
												</tr>
												<?php endforeach;?>
												<?php endif;?>
												</tbody>
											</table>
										</div>
									</div>
						           <?php echo $pagination_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						        </div>
						    </div>
						</div>
					</div>
			</div>
		</div>
	</div>
</div>
<?php 
// Security: Prepare secure JavaScript variables
$sync_window_url = $MSQS_QL->get_sync_window_url();
?>
 <script type="text/javascript">
	function search_item(){		
		var cl_push_search = jQuery('#cl_push_search').val();
		// Security: Validate and sanitize input
		cl_push_search = jQuery.trim(cl_push_search);
		if(cl_push_search!=''){			
			// Security: Encode the search parameter
			window.location = '<?php echo esc_url_raw($page_url);?>&cl_push_search='+encodeURIComponent(cl_push_search);
		}else{
			alert('<?php echo esc_js(__('Please enter search keyword.','mw_wc_qbo_sync')); ?>');
		}
	}

	function reset_item(){		
		window.location = '<?php echo esc_url_raw($page_url);?>&cl_push_search=';
	}
	
	jQuery(document).ready(function($) {
		var item_type = '<?php echo esc_js( 'vendor' ); ?>';
		var sync_window_url = '<?php echo esc_js( $sync_window_url ); ?>';
		var push_nonce = '<?php echo esc_js( $push_vendor_nonce ); ?>';
		
		$('#push_selected_vendor_btn').click(function(){
			var item_ids = '';
			var item_checked = 0;
			
			jQuery( "input[id^='cl_push_']" ).each(function(){
				if(jQuery(this).is(":checked")){
					item_checked = 1;
					var only_id = jQuery(this).attr('id').replace('cl_push_','');
					// Security: Validate numeric ID
					only_id = parseInt(only_id, 10);
					if(only_id>0 && !isNaN(only_id)){
						item_ids+=only_id+',';
					}					
				}
			});
			
			if(item_ids!=''){
				item_ids = item_ids.substring(0, item_ids.length - 1);
			}
			
			if(item_checked==0){
				alert('<?php echo esc_js(__('Please select at least one item.','mw_wc_qbo_sync')); ?>');
				return false;
			}
			
			// Security: Include nonce in URL
			popUpWindow(sync_window_url+'&sync_type=push&item_ids='+encodeURIComponent(item_ids)+'&item_type='+encodeURIComponent(item_type)+'&_wpnonce='+encodeURIComponent(push_nonce),'mw_qs_vendor_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_vendor_btn').click(function(){
			// Security: Include nonce and encode parameters
			popUpWindow(sync_window_url+'&sync_type=push&sync_all=1&item_type='+encodeURIComponent(item_type)+'&_wpnonce='+encodeURIComponent(push_nonce),'mw_qs_vendor_push',0,0,650,350);
			return false;
		});
		
		$('#push_all_unsynced_vendor_btn').click(function(){
			// Security: Include nonce and encode parameters
			popUpWindow(sync_window_url+'&sync_type=push&sync_unsynced=1&item_type='+encodeURIComponent(item_type)+'&_wpnonce='+encodeURIComponent(push_nonce),'mw_qs_vendor_push',0,0,650,350);
			return false;
		});
	});
 </script>
<?php echo wp_kses( $MWQS_OF->get_tablesorter_js('#mwqs_vendor_push_table'), array('script' => array('src' => array(), 'type' => array(), 'crossorigin' => array(), 'onerror' => array()), 'style' => array()) );?>