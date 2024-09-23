<?php
if ( ! defined( 'ABSPATH' ) )
exit;
?>

<?php 
global $MSQS_QL;
$is_valid_page = false;
$page = (isset($_GET['page']))?$_GET['page']:'';
$tab = (isset($_GET['tab']))?$_GET['tab']:'';
$rf_data_count = (isset($_GET['rf_data_count']))?$_GET['rf_data_count']:'';
$variation = (isset($_GET['variation']))?$_GET['variation']:'';
if(is_admin() && !empty($page)){	
	$is_valid_page = true;
	if(($page == 'myworks-wc-qbo-map' || $page == 'myworks-wc-qbo-push') && empty($tab)){
		$is_valid_page = false;
	}
}

$pv_tab = false;
?>

<?php if($is_valid_page):?>
<style>
	.wqam_ndc{
		padding: 20px 20px 50px 20px;
	}
	
	.wqam_tbl{
		width:360px;
	}
	
	.wqam_tbl td {
	  padding: 10px 0px 10px 0px;
	}
	.wqam_select{
		width:170px;
		float:none !important;
	}
</style>

<div class="container guide-bg-none">
    <div class="guide-wrap">
        <div class="guide-outer">
            <div class="guidelines">
            <?php if($page == 'myworks-wc-qbo-map'){ ?>
             <div class="common-content">
			  <?php if($tab == 'product' && empty($variation)):?>
              <?php if(!empty($rf_data_count) && is_numeric($rf_data_count)):?>
              <span>&nbsp; Total Product Imported: <?php echo (int) $rf_data_count;?></span>
              <?php endif;?>
              <span id="mwqs_automap_products_msg"></span>
              <span id="mwqs_automap_products_msg_by_name"></span>
			  <?php endif;?>
			  
			  <?php if($tab == 'product' && $variation == 1):?>
              <?php if(!empty($rf_data_count) && is_numeric($rf_data_count)):?>
              <span>&nbsp; Total Variation Imported: <?php echo (int) $rf_data_count;?></span>
              <?php endif;?>
              <span id="mwqs_automap_variations_msg"></span>
			  <?php endif;?>
			  
			  <?php if($tab == 'customer'):?>
              <?php if(!empty($rf_data_count) && is_numeric($rf_data_count)):?>
              <span>&nbsp; Total Customer Imported: <?php echo (int) $rf_data_count;?></span>
              <?php endif;?>
              <span id="mwqs_automap_customers_msg"></span>
              <span id="mwqs_automap_customers_msg_by_name"></span>
			  <?php endif;?>
			  
			  <?php if($tab == 'vendor' && $MSQS_QL->is_wq_vendor_pm_enable()):?>
			  <?php if(!empty($rf_data_count) && is_numeric($rf_data_count)):?>
              <span>&nbsp; Total Vendor Imported: <?php echo (int) $rf_data_count;?></span>
              <?php endif;?>
              <span id="mwqs_automap_vendors_msg"></span>
              <span id="mwqs_automap_vendors_msg_by_name"></span>
			  <?php endif;?>
			  
             </div>
            <?php } ?>
                <div class="tab_prdct_sect">    
                    <ul>
                        <li><span class="toggle-btn">Guidelines <i class="fa fa-angle-down"></i></span></li>
                        <?php if($pv_tab && $page == 'myworks-wc-qbo-map' && $tab == 'product'){ ?>
                        <li class="ab tab_one <?php if($variation != 1){ ?>active<?php } ?>" data-id="product_tab"><span><a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=product');?>">Products</a></span></li>
                        <li class="ab tab_two <?php if($variation == 1){ ?>active<?php } ?>" data-id="variation_tab"><span><a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=product&variation=1');?>">Variations</a></span></li>
                        <?php } ?>

                        <?php if($pv_tab && $page == 'myworks-wc-qbo-push'){ ?>
                        <?php if($tab == 'product' || $tab == 'variation'){ ?>
                        <li class="ab tab_one <?php if($tab == 'product'){ ?>active<?php } ?>" data-id="product_tab"><span><a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=product');?>">Products</a></span></li>
                        <li class="ab tab_two <?php if($tab == 'variation'){ ?>active<?php } ?>" data-id="variation_tab"><span><a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=variation');?>">Variations</a></span></li>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                
                    <div id="guide-target" >                        
                        <?php
                        switch ($page) {
                            case "myworks-wc-qbo-sync-settings":
                                echo __mwqbo_settings_page_guide();
                                break;
                            case "myworks-wc-qbo-map":
                                echo __mwqbo_map_page_guide();
                                break;
                            case "myworks-wc-qbo-push":
                                echo __mwqbo_push_page_guide();
                                break;                          
                            default:
                                echo __mwqbo_default_guide();
                        }
                        ?>
                    </div>
                </div>
            </div>   
        </div>
        
        <div class="guide-dropdown-outer guide-responsive">
        
            <?php if($page == 'myworks-wc-qbo-map' && $tab == 'product'){ ?>
            <?php if($variation != 1){ ?>
        	  <div class="refresh g-d-o-btn">
              <!-- Map > Product -->
            	<a href="<?php echo site_url('index.php?mw_qbo_sync_public_quick_refresh=1&data_type=product');?>" id="mwqs_refresh_data_from_qbo">
                <button title="Update products from quickbooks to local database">Refresh QuickBooks Products</button>			
              </a>
            </div>
			
			<?php if($html_section=false):?>
            <div class="aoutomated-outer g-d-o-btn">
                <div class="col col-m auto-map-btn">
                    <span  class="dropbtn col-m-btn">Automap Products <i class="fa fa-angle-down"></i></span>
                    <div class="dropdown-content">
                        <ul class="guide-accordion">
                            <?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_products', 'automap_products' ); ?>
                            <li> 
                                <a id="mwqs_automap_products"><?php _e( 'By Sku', 'mw_wc_qbo_sync' );?></a>
                            </li>
                            <li>
                            <?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_products_by_name', 'automap_products_by_name' ); ?>
                            <a id="mwqs_automap_products_by_name"><?php _e( 'By Name', 'mw_wc_qbo_sync' );?></a>
                            </li>
                        </ul>
                    </div>  
                </div>
            </div>
			<?php endif;?>
			
			<div class="aoutomated-outer g-d-o-btn">
				<div class="col col-m auto-map-btn">
					<span  class="dropbtn col-m-btn">Automap Products<i class="fa fa-angle-down"></i></span>
					<div class="dropdown-content wqam_ndc">
						<div class="myworks-wc-qbo-sync-table-responsive">
							<table class="wqam_tbl">
								<tr>
									<td width="50%"><?php _e( 'WooCommerce Field', 'mw_wc_qbo_sync' );?> :</td>
									<td>
										<?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_products_wf_qf', 'automap_products_wf_qf' ); ?>
										<select class="wqam_select" id="pam_wf">
											<option value=""></option>
											<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_pam_wf_list());?>
										</select>
									</td>
								</tr>
								
								<tr>
									<td><?php _e( 'QuickBooks Field', 'mw_wc_qbo_sync' );?> :</td>
									<td>
										<select class="wqam_select" id="pam_qf">
											<option value=""></option>
											<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_pam_qf_list());?>
										</select>
									</td>
								</tr>
								
								<tr>
									<td colspan="2">
										<input type="checkbox" id="pam_moum_chk" value="true" checked>
										&nbsp;
										<?php _e( 'Only apply to unmapped products', 'mw_wc_qbo_sync' );?>
									</td>
								</tr>
								
								<tr>								
									<td>								
									<button id="mwqs_automap_products_wf_qf">Automap</button>
									</td>
									<td>
										<span id="pam_wqf_e_msg"></span>
									</td>
								</tr>
								
								
							</table>
						</div>						
					</div>
				</div>
			</div>
			
            <?php } ?>

            <?php if($variation == 1){ ?>
            <div class="refresh g-d-o-btn">
              <!-- Map > Variation -->
              <a href="<?php echo site_url('index.php?mw_qbo_sync_public_quick_refresh=1&data_type=product&variation=1');?>" id="mwqs_refresh_data_from_qbo">
                <button title="Update variations from quickbooks to local database">Refresh QuickBooks Products</button>     
              </a>
            </div>
			
			<?php if($html_section=false):?>
            <div class="aoutomated-outer g-d-o-btn">
              <div class="col col-m auto-map-btn">
                <span  class="dropbtn col-m-btn">Automap Variations <i class="fa fa-angle-down"></i></span>
                  <div class="dropdown-content">
                    <ul class="guide-accordion">
                        <?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_variations', 'automap_variations' ); ?>
                          <li> 
                              <a id="mwqs_automap_variations"><?php _e( 'By Sku', 'mw_wc_qbo_sync' );?></a>
                          </li>
                      </ul>
                  </div>  
              </div>
            </div>
			<?php endif;?>
			
			<div class="aoutomated-outer g-d-o-btn">
				<div class="col col-m auto-map-btn">
					<span  class="dropbtn col-m-btn">Automap Variations<i class="fa fa-angle-down"></i></span>
					<div class="dropdown-content wqam_ndc">
						<table class="wqam_tbl">
							<tr>
								<td width="50%"><?php _e( 'WooCommerce Field', 'mw_wc_qbo_sync' );?> :</td>
								<td>
									<?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_variations_wf_qf', 'automap_variations_wf_qf' ); ?>
									<select class="wqam_select" id="vam_wf">
										<option value=""></option>
										<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_vam_wf_list());?>
									</select>
								</td>
							</tr>
							
							<tr>
								<td><?php _e( 'QuickBooks Field', 'mw_wc_qbo_sync' );?> :</td>
								<td>
									<select class="wqam_select" id="vam_qf">
										<option value=""></option>
										<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_vam_qf_list());?>
									</select>
								</td>
							</tr>
							
							<tr>
								<td colspan="2">
									<input type="checkbox" id="vam_moum_chk" value="true" checked>
									&nbsp;
									<?php _e( 'Only apply to unmapped variations', 'mw_wc_qbo_sync' );?>
								</td>
							</tr>
							
							<tr>								
								<td>								
								<button id="mwqs_automap_variations_wf_qf">Automap</button>
								</td>
								<td>
									<span id="vam_wqf_e_msg"></span>
								</td>
							</tr>
							
							
						</table>						
					</div>
				</div>
			</div>
			
            <?php } ?>
            <?php } ?>

            <?php if($page == 'myworks-wc-qbo-map' && $tab == 'customer'){ ?>
            <div class="refresh g-d-o-btn">
              <!-- Map > Customer -->
              <a href="<?php echo site_url('index.php?mw_qbo_sync_public_quick_refresh=1&data_type=customer');?>" id="mwqs_refresh_data_from_qbo">
                <button title="Update customers from quickbooks to local database">Refresh QuickBooks Customers</button>     
              </a>
            </div>
			
			<?php if($html_section=false):?>
            <div class="aoutomated-outer g-d-o-btn">
                <div class="col col-m auto-map-btn">
                    <span  class="dropbtn col-m-btn">Automap Customers <i class="fa fa-angle-down"></i></span>
                    <div class="dropdown-content">
                        <ul class="guide-accordion">
                            <?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_customers', 'automap_customers' ); ?>
                            <li> 
                                <a id="mwqs_automap_customers"><?php _e( 'By Email', 'mw_wc_qbo_sync' );?></a>
                            </li>
                            <li>
                            <?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_customers_by_name', 'automap_customers_by_name' ); ?>
                            <a id="mwqs_automap_customers_by_name"><?php _e( 'By Name', 'mw_wc_qbo_sync' );?></a>
                            </li>
                        </ul>
                    </div>  
                </div>
            </div>
			<?php endif;?>
			
			 <div class="aoutomated-outer g-d-o-btn">
				<div class="col col-m auto-map-btn">
					<span  class="dropbtn col-m-btn">Automap Customers<i class="fa fa-angle-down"></i></span>
					<div class="dropdown-content wqam_ndc">
						<table class="wqam_tbl">
							<tr>
								<td width="50%"><?php _e( 'WooCommerce Field', 'mw_wc_qbo_sync' );?> :</td>
								<td>
									<?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_customers_wf_qf', 'automap_customers_wf_qf' ); ?>
									<select class="wqam_select" id="cam_wf">
										<option value=""></option>
										<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_cam_wf_list());?>
									</select>
								</td>
							</tr>
							
							<tr>
								<td><?php _e( 'QuickBooks Field', 'mw_wc_qbo_sync' );?> :</td>
								<td>
									<select class="wqam_select" id="cam_qf">
										<option value=""></option>
										<?php echo $MSQS_QL->only_option('',$MSQS_QL->get_n_cam_qf_list());?>
									</select>
								</td>
							</tr>
							
							<tr>
								<td colspan="2">
									<input type="checkbox" id="cam_moum_chk" value="true" checked>
									&nbsp;
									<?php _e( 'Only apply to unmapped customers', 'mw_wc_qbo_sync' );?>
								</td>
							</tr>
							
							<tr>								
								<td>								
								<button id="mwqs_automap_customers_wf_qf">Automap</button>
								</td>
								<td>
									<span id="cam_wqf_e_msg"></span>
								</td>
							</tr>
							
							
						</table>						
					</div>
				</div>
			 </div>
            <?php } ?>
			
			<?php if($page == 'myworks-wc-qbo-map' && $tab == 'vendor' && $MSQS_QL->is_wq_vendor_pm_enable()){ ?>
            <div class="refresh g-d-o-btn">
              <!-- Map > Vendor -->
              <a href="<?php echo site_url('index.php?mw_qbo_sync_public_quick_refresh=1&data_type=vendor');?>" id="mwqs_refresh_data_from_qbo">
                <button title="Update vendors from quickbooks to local database">Refresh Vendors</button>     
              </a>
            </div>

            <div class="aoutomated-outer g-d-o-btn">
                <div class="col col-m auto-map-btn">
                    <span  class="dropbtn col-m-btn">Automap Vendors <i class="fa fa-angle-down"></i></span>
                    <div class="dropdown-content">
                        <ul class="guide-accordion">
                            <?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_vendors', 'automap_vendors' ); ?>
                            <li> 
                                <a id="mwqs_automap_vendors"><?php _e( 'By Email', 'mw_wc_qbo_sync' );?></a>
                            </li>
                            <li>
                            <?php wp_nonce_field( 'myworks_wc_qbo_sync_automap_vendors_by_name', 'automap_vendors_by_name' ); ?>
                            <a id="mwqs_automap_vendors_by_name"><?php _e( 'By Name', 'mw_wc_qbo_sync' );?></a>
                            </li>
                        </ul>
                    </div>  
                </div>
            </div>
            <?php } ?>

            <div class="guide-dropdown" style="position:static">
               <span class="dropbtn">Need Help?  <i class="fa fa-angle-down"></i></span>
               <div class="dropdown-content">
                  <?php
                  switch ($page) {
                      case "myworks-wc-qbo-sync-settings":
                          echo __mwqbo_settings_page_help();
                          break;
                      case "myworks-wc-qbo-map":
                          echo __mwqbo_map_page_help();
                          break;
                      case "myworks-wc-qbo-push":
                          echo __mwqbo_push_page_help();
                          break;                          
                      default:
                          echo __mwqbo_default_help();
                  }
                  ?>
             </div>
          </div>
          
    
      </div>
    </div><!--guide-wrap-->
    
</div>


<script>
jQuery('.toggle-btn').click(function() {
    jQuery('#guide-target').slideToggle('fast');
});
jQuery(".toggle-btn").click(function(){
    jQuery(".toggle-btn").toggleClass("toggle-sub");
});

  
jQuery('.guide-accordion').find('li').click(function(){
	if(jQuery(this).hasClass('open')){			
		jQuery(this).find('.guide-submenu').slideUp();
		jQuery(this).removeClass('open');
	}else{
		jQuery('.guide-accordion').find('.guide-submenu').slideUp();
		jQuery('.guide-accordion').find('li').removeClass('open');
		jQuery(this).find('.guide-submenu').slideDown();
		jQuery(this).addClass('open');
	}
});

</script>
<?php endif;?>

<?php
function __mwqbo_settings_page_guide(){
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  $HTML = '<div class="guide settings default" style="display: block;">
            All the dropdowns on this <strong>Default</strong> tab need to be selected to complete setup. Hover over the question marks on the right if you are unsure what a certain setting is, and make sure that the option you select makes sense based on your store settings.
            </div>
            <div class="guide settings order" style="display: none;">
            This section contains settings relevant to syncing WooCommerce Orders. All of these settings are optional and can be left as is for acceptable plugin operation. If you are unsure about a setting, hover over the question mark on the right for an explanation.
            </div>
            <div class="guide settings tax" style="display: none;">
            This section contains settings relevant to tax settings for syncing WooCommerce Orders. All of these settings must be set for acceptable plugin operation. Hover over the question marks on the right for an explanation of each setting.
          </div>
          <div class="guide settings map" style="display: none;">
            This section contains settings relevant to the mapping operations of our plugin. All of these settings are optional and can be left as is for acceptable plugin operation. Hover over the question marks on the right for an explanation of each setting.
            </div>
            <div class="guide settings pull" style="display: none;">
            This section contains settings relevant to the pull operations (pulling data from QuickBooks Online to WooCommerce) of our plugin. All of these settings are optional and can be left as is for acceptable plugin operation. Hover over the question marks on the right for an explanation of each setting.
            </div>
            <div class="guide settings sync" style="display: none;">
            This section contains settings relevant to the real time sync operations of our plugin. These settings are set to recommended defaults and can be left as is for acceptable plugin operation. Hover over the question marks on the right for an explanation of each setting.
          </div>
          <div class="guide settings misc" style="display: none;">
            This section contains miscellaneous settings that are already set to recommended defaults.
          </div>
          <script>
          jQuery(document).ready(function(e){
            jQuery("#mw_qbo_sybc_settings_tab_one").on("click",function(e){
              jQuery(".settings").hide();
              jQuery(".default").show();
            });
            jQuery("#mw_qbo_sybc_settings_tab_two").on("click",function(e){
              jQuery(".settings").hide();
              jQuery(".order").show();
            });
            jQuery("#mw_qbo_sybc_settings_tab_five").on("click",function(e){
              jQuery(".settings").hide();
              jQuery(".map").show();
            });
            jQuery("#mw_qbo_sybc_settings_tab_four").on("click",function(e){
              jQuery(".settings").hide();
              jQuery(".tax").show();
            });
            jQuery("#mw_qbo_sybc_settings_tab_six").on("click",function(e){
              jQuery(".settings").hide();
              jQuery(".pull").show();
            });
            jQuery("#mw_qbo_sybc_settings_tab_wh").on("click",function(e){
              jQuery(".settings").hide();
              jQuery(".sync").show();
            });
            jQuery("#mw_qbo_sybc_settings_tab_nine").on("click",function(e){
              jQuery(".settings").hide();
              jQuery(".misc").show();
            });
          })
          </script>
          ';
  return $HTML;
}

function __mwqbo_map_page_guide(){
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';  
  if($tab=='customer'){
    $HTML = '<div class="guide">
            This page allows you to map existing WooCommerce customers to existing QuickBooks customers. Only customers that exist in both systems need to be mapped.
            </div>
            <div class="guide">
            When new customers are created in WooCommerce from this point forward, they will be automatically synced to QuickBooks Online AND mapped in this page.
            </div>';
  }elseif($tab=='payment-method'){
    $HTML = '<div class="guide">
            To map & configure WooCommerce payment gateways for when orders are synced to QuickBooks Online, turn the ‘Enable Payment Syncing’ switch on for each payment method you’d like to sync, and then select a label and a <strong>bank account</strong> for this payment method. This ensures that payments are deposited into the correct QuickBooks Online bank account when we sync orders over to QuickBooks Online.
            </div>
			<div class="guide">
			            Advanced options are available - click “Show Advanced Options” and hover over the question marks on the right for an explanation of each setting.
			            </div>';
  }elseif($tab=='product'){
    $HTML = '<div class="guide">
            This section allows you to map (or link) together products that exist in both systems. If a product exists in WooCommerce, but not in QuickBooks Online, you would not be able to map it here until you push it to QuickBooks in MyWorks Sync > Push > Products. (Pushing a product will also automatically map it here.)
            </div>
            <div class="guide">
            When new products are created in WooCommerce from this point forward, they will be automatically synced to QuickBooks Online AND mapped in this page - if you have the Product switch enabled in MyWorks Sync > Settings > Automatic Sync.
            </div>
            <div class="guide">
            We recommend you map as many of your products as you can. Mapping products ensures that orders are accurately synced over and inventory can be accurately synced.
            </div>
            <div class="guide">
            There is no need to map a parent variable product, since a parent variable product itself never actually gets ordered. Only its variations would need to get mapped - in the Variations tab on this page. Since QuickBooks Online does not directly support variations, variations in WooCommerce can be mapped to QuickBooks Online products. 
          </div>';
  }elseif($tab=='tax-class'){
    $HTML = '<div class="guide">
            This page allows you to map WooCommerce tax rules to existing QuickBooks Online tax rules.
            If a tax rule is not mapped, and an order is placed that includes that tax rule, an error will most likely occur.
            </div>
            <div class="guide">
            If you have more than 100 tax rules, we highly recommend considering an automated tax rule management system - such as <a href="https://support.myworks.software/woocommerce-sync-for-quickbooks-online/compatibility-addons/avalara-avatax">Avalara</a>. This will greatly reduce the time you spend manually managing your tax rates in WooCommerce.
            </div>';
  }elseif($tab=='shipping-method'){
    $HTML = '<div class="guide">
            To map your WooCommerce Shipping Methods to QuickBooks shipping methods, choose a QuickBooks Online product in the dropdown in the right column and select it. You can also search for a product in the dropdown field. Then scroll to the bottom and click save.
            </div>';
  }else{
    $HTML = '<div class="guide">
            Need help on this? Please contact our support anytime! 
            </div>';
  }
  return $HTML;
}

function __mwqbo_push_page_guide(){
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  if($tab=='customer'){
    $HTML = '<div class="guide">
            This section allows you to push customers from WooCommerce to QuickBooks Online. If a customer already exists in QuickBooks Online, you should map them in MyWorks Sync > Map > Customers.
            </div>
            <div class="guide">
            It isn\'t very common to push customers on this page, as if a customer does not exist in QuickBooks Online, our integration will automatically create them in QuickBooks Online the next time they place an order in WooCommerce. Hence, it is perfectly fine to leave customers unsynced on this page if they don\'t exist in QuickBooks Online.
            </div>';
  }elseif($tab=='invoice'){
    $HTML = '<div class="guide">
            This section allows you to push existing WooCommerce orders into QuickBooks Online. New WooCommerce orders will be automatically synced into QuickBooks Online. If an order already exists in QuickBooks Online, and you push it here - it will simply be updated in QuickBooks Online, never duplicated.
            </div>
            <div class="guide">
            If you have orders set to sync to QuickBooks Online as Invoices (in MyWorks Sync > Settings > Order), note that you must also push over the Payment after you push the Order over - as pushing orders on this page will push the invoice over to QuickBooks Online. You can push payments in MyWorks Sync > Push > Payments.
            </div>
            <div class="guide">
           We recommend you only push over orders that are completed or processing. If an order is pending payment or cancelled, for example - pushing it to QuickBooks Online will create it in QuickBooks Online as an actual order, which would be incorrect.
            </div>';
  }elseif($tab=='product'){
    $HTML = '<div class="guide">
            This section allows you to push products from WooCommerce to QuickBooks Online. If a product already exists in QuickBooks Online, you should map it - in MyWorks Sync > Map > Products.
            </div>
            <div class="guide">
           Products that have Manage Stock turned on in WooCommerce will be created in QuickBooks Online as Inventory Products when you push them. It is important to note that the inventory Start Date of these products in QuickBooks Online will be today\'s date (the day that you push them).
            </div>
            <div class="guide">
           If a product already exists in QuickBooks Online, and you simply want to update its inventory level in QuickBooks Online - then you should visit MyWorks Sync > Push > Inventory Levels.
            </div>';
	    }elseif($tab=='variation'){
	      $HTML = '<div class="guide">
	              This section allows you to push variations from WooCommerce to QuickBooks Online. If a product already exists in QuickBooks Online, you should map it - in MyWorks Sync > Map > Products.
	              </div>
	              <div class="guide">
	             Since QuickBooks Online does not directly support variations, variations in WooCommerce will be created in QuickBooks Online as products - and mapped together. 
	              </div>
	              <div class="guide">
	             Variations that have Manage Stock turned on in WooCommerce will be created in QuickBooks Online as Inventory Products when you push them. It is important to note that the inventory Start Date of these products in QuickBooks Online will be today\'s date (the day that you push them).
	              </div>
	              ';
  }elseif($tab=='inventory'){
    $HTML = '<div class="guide">
            This section allows you to push inventory levels from WooCommerce to QuickBooks Online for products that already exist in both systems AND are mapped in MyWorks Sync > Map > Products. Only mapped products will show up on this page. 
            </div>
            <div class="guide">
            If the intended product does not yet exist in QuickBooks Online, you must first push it over in MyWorks Sync > Push > Products, before pushing over inventory levels.
            </div>';
	    }elseif($tab=='category'){
	      $HTML = '<div class="guide">
	              This section allows you to push WooCommerce categories over to QuickBooks Online. This is totally optional - as categories in QuickBooks Online are purely organizational - and have no effect on orders or products in QuickBooks Online. 
	              </div>
	            ';
  }elseif($tab=='shipping-method'){
    $HTML = '<div class="guide">
            To map your WooCommerce Shipping Methods to QuickBooks shipping methods, choose a QuickBooks Online product in the dropdown in the right column and select it. You can also search for a product in the dropdown field. Then scroll to the bottom and click save.
            </div>';
  }
  elseif($tab=='payment'){
    $HTML = '<div class="guide">
            In this tab, you can select and push payments from <a target="_blank" href="https://myworks.software/integrations/woocommerce-quickbooks-sync/quickbooks-online/" target="_blank" rel="nofollow noreferrer">﻿WooCommerce to QuickBooks Online﻿</a>. Choose the Filter option to search for a specific customer or date range, and use the dropdown on the top right to display 20 or more entries per page.
            </div>
            <div class="guide">
            Before pushing payments, you should check and verify the mappings in Map > Payment Methods are correct.
            </div>
            <div class="guide">
            Note that <b><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714553-Push-Pages#payments-0-4">an invoice must already exist in QuickBooks Online</b>, or it will error out. 
            </div>';
  }else{
    $HTML = '<div class="guide">
            Need help on this? Please contact our support anytime! 
            </div>';
  }
  return $HTML;
}

function __mwqbo_default_guide(){
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  $HTML = '<div class="guide">
            Need help on this? Please contact our support anytime! 
            </div>';
  return $HTML;
}

function __mwqbo_map_page_help(){
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  if($tab=='customer'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991814-Mapping">About Mapping</a></li>
        <li><a target="_blank"  href="https://support.myworks.software/hc/en-us/articles/360047990354-What-is-the-Auto-Mapping-feature-and-how-can-I-use-it-#customers-0-1">Auto-Mapping Customers</a></li>
        <li><a target="_blank"  href="https://www.youtube.com/watch?v=KHECaScWVx4">Video Walkthrough</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991494-What-does-Mapping-mean-and-why-do-I-have-to-Map-my-customers-and-products-">What does “Mapping” mean, and why do I have to Map my customers and products?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048713833-What-is-the-Auto-Mapping-feature-and-how-can-I-use-it-">What is the “Auto Mapping” feature, and how can I use it?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991414-How-are-mappings-handled-on-an-ongoing-basis-">How are mappings handled on an ongoing basis?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='payment-method'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991814-Mapping#payment-methods-0-5">Mapping payment gateways</a></li>
        <li><a target="_blank" href="https://www.youtube.com/watch?v=a4oSksFnOVs">Video Walkthrough</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991414-How-are-mappings-handled-on-an-ongoing-basis-">How are mappings handled on an ongoing basis?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991594-Syncing-Transaction-Fees-with-QuickBooks-Online">How can I handle transaction fees?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714413-Handling-Transaction-Fees-with-PayPal">How can I sync transaction fees with PayPal?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991674-Batch-Deposit-Support-Overview">Do you have batch support?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714453-Batch-Support-for-pre-existing-orders">How can I handle batch support for existing orders?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='product'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991814-Mapping#products-0-3">Product Mapping</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991814-Mapping#automapping-0-4">Auto-Mapping Products</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712753-Variation-Support">Variation Support</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712733-Syncing-orders-with-Bundled-products-with-QuickBooks-Online">Bundled Product Support</a></li>
        <li><a target="_blank" href="https://www.youtube.com/watch?v=md4x4EX5ZVU">Video Walkthrough</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048713833-What-is-the-Auto-Mapping-feature-and-how-can-I-use-it-#what-is-the-auto-mapping-feature-and-how-can-i-use-it--0-0">How can I use the auto-mapping feature?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991414-How-are-mappings-handled-on-an-ongoing-basis-">How are mappings handled on an ongoing basis?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712753-Variation-Support#mapping-0-2">How can I map my variations?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712733-Syncing-orders-with-Bundled-products-with-QuickBooks-Online#mapping-0-3">How can I map bundled products?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='tax-class'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712213-Mapping-Taxes-Best-Practices">Mapping Taxes - Best Practices</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712213-Mapping-Taxes-Best-Practices#mapping-combined-tax-rates-0-3">Mapping Combined Tax Rates</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991414-How-are-mappings-handled-on-an-ongoing-basis-">How are mappings handled on an ongoing basis?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='shipping-method'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712373-Using-Shipping-Discounts-with-QuickBooks-Online">Using Shipping and Discounts with QuickBooks Online</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
         <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991414-How-are-mappings-handled-on-an-ongoing-basis-">How are mappings handled on an ongoing basis?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712333-Shipping-As-a-line-item-or-a-subtotal-field-">Shipping: As a line item or a subtotal field?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }else{
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Need more help?</div>
      <ul class="guide-submenu">
        <li><a href="#">Our support team is always available to help you!</a></li>
        <li><a href="https://support.myworks.software/hc/en-us/requests/new">Open a Ticket</a></li>
      </ul>
    </li>
  </ul>';
  }
  return $HTML;
}

function __mwqbo_push_page_help(){
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  if($tab=='customer'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714553-Push-Pages">Intro to pushing</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714553-Push-Pages#customers-0-1">Pushing customers</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991814-Mapping#customers-0-1">Mapping customers</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991494-What-does-Mapping-mean-and-why-do-I-have-to-Map-my-customers-and-products-">What does “Mapping” mean, and why do I have to Map my customers and products?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='invoice'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714553-Push-Pages#orders-0-2">How to push orders</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Troubleshooting</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/woocommerce-sync-for-quickbooks-online/troubleshooting/cant-modify-a-transaction-before-you-started-tracking-quantity-on-hand">You can’t create or modify a transaction with a date that comes before you started tracking quantity on hand</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='product'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714553-Push-Pages#products-variations-0-3">Pushing Products</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991814-Mapping#products-0-3">Mapping products</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712753-Variation-Support">Do you support variations?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712733-Syncing-orders-with-Bundled-products-with-QuickBooks-Online">Do you support bundles?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712613-How-can-I-control-if-products-are-synced-to-QuickBooks-as-Inventory-or-Non-Inventory-">How can I control if products are synced to QuickBooks as Inventory or Non-Inventory?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991494-What-does-Mapping-mean-and-why-do-I-have-to-Map-my-customers-and-products-">What does “Mapping” mean, and why do I have to Map my customers and products?</a></li>
      </ul>
    </li>
    
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='variation'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='inventory'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047990214-Syncing-Inventory-with-QuickBooks-Online">Syncing Inventory with QuickBooks Online</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714553-Push-Pages#inventory-levels-0-5">Pushing inventory</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712613-How-can-I-control-if-products-are-synced-to-QuickBooks-as-Inventory-or-Non-Inventory-">How can I control if products are synced to QuickBooks as Inventory or Non-Inventory?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='category'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='shipping-method'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    
    '.__mwqbo_need_help_common().'
  </ul>';
  }elseif($tab=='payment'){
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714553-Push-Pages#payments-0-4">Pushing payments</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991594-Syncing-Transaction-Fees-with-QuickBooks-Online">Transaction fee syncing</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991694-Handling-non-immediate-payment-gateways-COD-Wire-Transfer-">How can I Handle non-immediate payment gateways (COD, Wire Transfer)?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991634-Batch-Deposit-Support-with-Stripe">How can I handle batch support with stripe?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  }else{
    $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
    <div class="acco-link">Need more help?</div>
    <ul class="guide-submenu">
      <li><a href="#">Our support team is always available to help you!</a></li>
      <li><a href="https://support.myworks.software/hc/en-us/requests/new">Open a Ticket</a></li>
    </ul>
    </li>
  </ul>';
  }
  return $HTML;
}

function __mwqbo_settings_page_help(){
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  $HTML = '<ul id="guide-accordion" class="guide-accordion settings default" style="display: block;">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991914-Settings#default-0-1">Configuring default settings</a></li>
        <li><a target="_blank" href="https://myworks.software/onboarding/qbo">Video walkthroughs</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991914-Settings#default-0-1">What should my default product be?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048712333-Shipping-As-a-line-item-or-a-subtotal-field-">Shipping: As a line item or subtotal field?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>

  <ul id="guide-accordion" class="guide-accordion settings order" style="display: none;">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991914-Settings#order-0-2">Configuring order settings</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991254-Should-I-push-WooCommerce-orders-as-a-Sales-Receipt-or-an-Invoice-">Should I push WooCommerce Orders as Invoices or Sales Receipts?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991694-Handling-non-immediate-payment-gateways-COD-Wire-Transfer-">How can I handle syncing for orders not instantly paid?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991194-How-are-WooCommerce-orders-placed-by-guests-synced-to-QuickBooks-Online-">How are WooCommerce Orders Placed by Guests Synced to QuickBooks?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048713893-What-if-my-WooCommerce-order-numbers-already-exist-in-QuickBooks-Online-as-different-orders-">What if my WooCommerce order numbers already exist in QuickBooks Online – as different orders?</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Troubleshooting</div>
      <ul class="guide-submenu">
         <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048713893-What-if-my-WooCommerce-order-numbers-already-exist-in-QuickBooks-Online-as-different-orders-">Handling duplicate order numbers between WooCommerce and QuickBooks</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>

  <ul id="guide-accordion" class="guide-accordion settings tax" style="display: none;">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/1500005056861-Best-practices-on-handling-taxes-when-syncing-with-QuickBooks-Online">Configuring taxes</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047989594-Using-Automated-Sales-Tax-with-WooCommerce-and-QuickBooks-Online">Using Automated Sales Tax with WooCommerce and QuickBooks Online</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Additional Resources</div>
      <ul class="guide-submenu">
         <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/1500005056861-Best-practices-on-handling-taxes-when-syncing-with-QuickBooks-Online">Mapping Taxes</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>

  <ul id="guide-accordion" class="guide-accordion settings map" style="display: none;">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991914-Settings#mapping-0-4">Configuring mapping settings</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991414-How-are-mappings-handled-on-an-ongoing-basis-">How are mappings handled on an ongoing basis?</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991514-What-is-the-difference-between-mapping-and-pushing-data-#what-is-the-difference-between-mapping-and-pushing-data--0-0">What is the difference between mapping and pushing data?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>

  <ul id="guide-accordion" class="guide-accordion settings pull" style="display: none;">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991914-Settings#pull-0-5">Configuring pull settings</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>

  <ul id="guide-accordion" class="guide-accordion settings sync" style="display: none;">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360048714693-How-does-syncing-work-">About syncing</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991914-Settings#automatic-sync-0-6">Configuring automatic sync settings</a></li>
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991834-Getting-started-with-Queue-Syncing">About queue sync</a></li>
      </ul>
    </li>
    <li>
      <div class="acco-link">Common Questions</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991834-Getting-started-with-Queue-Syncing">How can I enable queue sync?</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>

  <ul id="guide-accordion" class="guide-accordion settings misc" style="display: none;">
    <li>
      <div class="acco-link">Getting Started</div>
      <ul class="guide-submenu">
        <li><a target="_blank" href="https://support.myworks.software/hc/en-us/articles/360047991914-Settings#miscellaneous-0-7">Configuring miscellaneous settings</a></li>
      </ul>
    </li>
    '.__mwqbo_need_help_common().'
  </ul>';
  return $HTML;
}

function __mwqbo_default_help(){
 $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  $HTML = '<ul id="guide-accordion" class="guide-accordion">
    <li>
  <div class="acco-link">Need more help?</div>
  <ul class="guide-submenu">
    <li><a href="#">Our support team is always available to help you!</a></li>
    <li><a href="https://support.myworks.software/hc/en-us/requests/new">Open a Ticket</a></li>
  </ul>
    </li>
  </ul>';
  return $HTML;
}

function __mwqbo_need_help_common(){
  global $MSQS_QL;
  $tab = (isset($_GET['tab']))?$_GET['tab']:'';
  $HTML = '<li>
      <div class="acco-link">Still need help?</div>
      <ul class="guide-submenu">
      <li><a target="_blank" href="https://support.myworks.software/hc/en-us/categories/360003544674-WooCommerce-Sync-for-QuickBooks-Online">View our documentation</a></li>
      <li><a target="_blank" href="https://support.myworks.software/hc/en-us/requests/new">Open a support ticket</a></li>
        <li><a target="_blank" href="https://myworks.software/">Live chat with us</a></li>
      </ul>
    </li>';
    return $HTML;
}
?>
<div class="dont-delete"></div>