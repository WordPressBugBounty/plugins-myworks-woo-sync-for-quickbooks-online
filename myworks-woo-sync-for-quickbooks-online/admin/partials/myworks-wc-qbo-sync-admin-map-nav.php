<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MSQS_QL;

$tab = isset($_GET['tab'])?$_GET['tab']:'';
$variation = (isset($_GET['variation']))?true:false;

global $wpdb;
$wc_tot_tax_rates = (int) $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."woocommerce_tax_rates` WHERE `tax_rate_id` >0 ");
?>
<nav class="mw-qbo-sync-grey">
	<div class="nav-wrapper">
		<a class="brand-logo left" href="javascript:void(0)">
			<img src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/mwd-logo.png' ) ?>">
		</a>
		<ul class="hide-on-med-and-down right">
			<?php if(!$MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')):?>
			<?php if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt') || !empty($MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus'))):?>
			<li class="cust-icon <?php if($tab=='customer') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=customer') ?>">Customer</a>
			</li>		
			<?php endif;?>
			<?php endif;?>
			
			<li class="pro-icon <?php if($tab=='product' && !$variation) echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=product') ?>">Product</a>
			</li>

			<li class="var-icon <?php if($tab=='product' && $variation) echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=product&variation=1') ?>">Variation</a>
			</li>

			<li class="pay-icon <?php if($tab=='payment-method') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=payment-method') ?>">Payment Method</a>
			</li>
			
			<?php if($wc_tot_tax_rates > 0):?>
			<?php if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_odr_tax_as_li')):?>
			<?php if(!$MSQS_QL->get_qbo_company_setting('is_automated_sales_tax')):?>
			<li class="tax-icon <?php if($tab=='tax-class') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=tax-class') ?>">Tax Rate</a>
			</li>
			<?php endif;?>
			<?php endif;?>
			<?php endif;?>
			
			<?php $discount_disabled = true;?>
			<?php if(!$discount_disabled && !$MSQS_QL->is_plg_lc_p_l() && !$MSQS_QL->get_qbo_company_setting('is_discount_allowed')):?>
			<li class="cou-icon <?php if($tab=='coupon-code') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=coupon-code') ?>">Coupon Code</a>
			</li>
			<?php endif;?>
			
			<?php if(!$MSQS_QL->is_plg_lc_p_l() && !$MSQS_QL->get_qbo_company_setting('is_shipping_allowed')):?>
			<li class="ship-icon <?php if($tab=='shipping-method') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=shipping-method') ?>">Shipping Method</a>
			</li>
			<?php endif;?>
			
			<?php if($MSQS_QL->is_plugin_active('myworks-qbo-sync-custom-field-mapping') && $MSQS_QL->check_sh_cfm_hash()):?>
			<li class="cf-icon <?php if($tab=='custom-fields') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=custom-fields') ?>">Custom Fields</a>
			</li>
			<?php endif;?>
			
			<?php if($MSQS_QL->is_wq_vendor_pm_enable()):?>
			<li class="vendor-icon <?php if($tab=='vendor') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-map&tab=vendor') ?>">Vendor</a>
			</li>
			<?php endif;?>
			
		</ul>
	</div>
</nav>

<?php require_once plugin_dir_path( __FILE__ ) . 'myworks-wc-qbo-sync-admin-guidelines.php' ?>