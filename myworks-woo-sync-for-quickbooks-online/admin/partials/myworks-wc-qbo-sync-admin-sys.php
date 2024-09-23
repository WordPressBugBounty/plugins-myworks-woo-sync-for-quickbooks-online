<?php
/* Template Name: Demo */
if ( ! defined( 'ABSPATH' ) )
exit;

MyWorks_WC_QBO_Sync_Admin::is_trial_version_check();
?>
<?php global $wpdb ?>

<div class="container map-coupon-code-outer">
	<div class="page_title"><h4><?php _e( 'System Environment', 'mw_wc_qbo_sync' );?></h4></div>
	<div class="card">
		<div class="card-content">
			<div class="col s12 m12 l12">
				<div class="myworks-wc-qbo-sync-table-responsive">
					<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">		
                    	<thead>
                        	<tr>
                            	<th width="30%">
									<?php _e( 'WordPress Environment', 'mw_wc_qbo_sync' );?>								    	
                                </th>
                                <th width="10%">
                                    <?php _e( 'Information', 'mw_wc_qbo_sync' );?>						    	
                                </th>
                                <th width="60%">
                                    <?php _e( 'WordPress Status', 'mw_wc_qbo_sync' );?> 
                                </th>
                        	</tr>
                        </thead>								
						<tr>
							<td>Home URL: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo home_url(); ?></td>
						</tr>
						<tr>
							<td>Site URL: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo site_url(); ?></td>
						</tr>

						<tr>
							<td>WC Version: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo WC()->version; ?></td>
						</tr>
						<tr>
							<td>Log Directory Writable: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo MW_QBO_SYNC_LOG_DIR; ?>&nbsp;(<?php if ( @fopen( MW_QBO_SYNC_LOG_DIR . 'mw-qbo-sync-log.log', 'a' ) ) { echo 'Writable'; }else{ echo 'Not Writable'; } ?>)</td>
						</tr>
						<tr>
							<td>WP Version: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo get_bloginfo('version'); ?></td>
						</tr>
						<tr>
							<td>WP Multisite: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if ( is_multisite() ) { echo 'Multisite is enabled'; }else{ echo 'Multisite is disabled'; } ?></td>
						</tr>
						<tr>
							<td>WP Memory Limit: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<?php
							$memory = wc_let_to_num( WP_MEMORY_LIMIT );

							if ( function_exists( 'memory_get_usage' ) ) {
								$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) );
								$memory        = max( $memory, $system_memory );
							}
							?>
							<td><?php echo size_format( $memory ) ?></td>
						</tr>
						<tr>
							<td>WP Debug Mode: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if (defined('WP_DEBUG') && true === WP_DEBUG) {
									   echo 'TRUE';
									}else{
										echo 'FALSE';
									} ?></td>
						</tr>
						<tr>
							<td>WP Cron: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ){ echo 'False'; }else{ echo 'Enabled'; } ?></td>
						</tr>
						<tr>
							<td>Language: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo get_locale(); ?></td>
						</tr>
						</table>

						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">		
                    	<thead>
                        	<tr>
                            	<th width="30%">
									<?php _e( 'Server Environment', 'mw_wc_qbo_sync' );?>								    	
                                </th>
                                <th width="10%">
                                    <?php _e( 'Information', 'mw_wc_qbo_sync' );?>						    	
                                </th>
                                <th width="60%">
                                    <?php _e( 'Server Status', 'mw_wc_qbo_sync' );?> 
                                </th>
                        	</tr>
                        </thead>
						<tr>
							<td>Server Info: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo $_SERVER['SERVER_SOFTWARE'] ?></td>
						</tr>
						<tr>
							<td>PHP Version: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo phpversion(); ?></td>
						</tr>
						<tr>
							<td>PHP Post Max Size: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo ini_get('post_max_size'); ?></td>
						</tr>
						<tr>
							<td>PHP Time Limit: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo ini_get('max_execution_time');  ?></td>
						</tr>
						<tr>
							<td>PHP Max Input Vars: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo ini_get('max_input_vars'); ?></td>
						</tr>
						<tr>
							<td>cURL Version: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php $curl_version = curl_version() ?><?php echo $curl_version['version'].', '.$curl_version['host'].', '. $curl_version['ssl_version'] ?></td>
						</tr>
						<tr>
							<td>SUHOSIN Installed: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if(extension_loaded('suhosin')){ echo 'Yes'; }else{ echo 'No'; } ?></td>
						</tr>
						<tr>
							<td>MySQL Version: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo $wpdb->db_version(); ?></td>
						</tr>
						<tr>
							<td>Max Upload Size: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php  echo ini_get('upload_max_filesize'); ?> </td>
						</tr>
						<tr>
							<td>Default Timezone is UTC: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php echo date_default_timezone_get(); ?></td>
						</tr>
						<tr>
							<td>fsockopen/cURL: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) echo 'Enabled'; else echo 'Disabled'; ?></td>
						</tr>
						<tr>
							<td>SoapClient: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td>
							<?php 
								if ( class_exists( 'SoapClient' ) ) {
									echo "True";
								}else{
									echo "False";
								}
							?></td>
						</tr>

						<tr>
							<td>DOMDocument: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if ( class_exists( 'DOMDocument' ) ) { echo 'True'; } else { echo 'False'; } ?></td>
						</tr>
						<tr>
							<td>GZip: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if ( is_callable( 'gzopen' ) ) { echo 'True'; }else{ echo 'False'; } ?></td>
						</tr>
						<tr>
							<td>Multibyte String: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<td><?php if ( extension_loaded( 'mbstring' ) ) { echo 'Enabled'; }else{ echo 'Disabled'; } ?></td>
						</tr>
						<tr>
							<td>Remote Post: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<?php
							$response = wp_safe_remote_post( 'https://www.paypal.com/cgi-bin/webscr', array(
								'timeout'     => 60,
								'user-agent'  => 'WooCommerce/' . WC()->version,
								'httpversion' => '1.1',
								'body'        => array(
									'cmd'    => '_notify-validate'
								)
							) );
							?>
							<td><?php if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) { echo 'True'; }else{ echo 'False'; } ?></td>
						</tr>
						<tr>
							<td>SRemote Get: </td>
							<td><div class="material-icons tooltipped tooltip">?<span class="tooltiptext"></span>
							</div></td>
							<?php $response = wp_safe_remote_get( 'https://woocommerce.com/wc-api/product-key-api?request=ping&network=' . ( is_multisite() ? '1' : '0' ) ); ?>
							<td><?php if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) { echo 'True'; }else{ echo 'False'; } ?></td>
						</tr>
						</table>

						<table class="mw-qbo-sync-settings-table menu-blue-bg" width="100%">		
                    	<thead>
                        	<tr>
                            	<th width="30%">
									<?php _e( 'Database Environment', 'mw_wc_qbo_sync' );?>								    	
                                </th>
								<th width="10%">&nbsp;</th>
                                <th width="60%">
                                    <?php _e( 'Database Status', 'mw_wc_qbo_sync' );?> 
                                </th>
                        	</tr>
                        </thead>
						<?php
							$tables = MyWorks_WC_QBO_Sync_Admin::health_checker('tables');
							foreach ( $tables as $table ) {
							?>
							<tr>
								<td><?php echo esc_html( $table ); ?></td>
								<td>&nbsp;</td>
								<td><?php echo $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s;", $wpdb->prefix . $table ) ) !== $wpdb->prefix . $table ? __( 'Table does not exist', 'mw_wc_qbo_sync' ) :  __( 'Table exist', 'mw_wc_qbo_sync' ) ; ?></td>
							</tr>
						<?php } ?>
					</table>
				</div>	
			</div>
		</div>
	</div>
</div>