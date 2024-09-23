<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $MSQS_QL;
global $MWQS_OF;
$tab = isset($_GET['tab'])?$_GET['tab']:'';
$show_srmi = true; # Setup restricted menu items
?>
<nav class="mw-qbo-sync-grey">
	<div class="nav-wrapper">
		<a class="brand-logo left" href="javascript:void(0)">
			<img src="<?php echo plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/mwd-logo.png' ) ?>">
		</a>
		<ul class="hide-on-med-and-down right">
			<?php if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt') || !empty($MSQS_QL->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus'))):?>
			
			<?php if(!$MSQS_QL->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')):?>
			<li class="cust-icon <?php if($tab=='customer') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=customer') ?>">Customer</a>
			</li>
			<?php endif;?>
			
			<?php if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')):?>
			<li class="ord-icon <?php if($tab=='invoice') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=invoice') ?>">Order</a>
			</li>
			<?php endif;?>
			<?php else:?>
			<?php if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')):?>
			<li class="ord-icon <?php if($tab=='invoice') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=invoice') ?>">Order</a>
			</li>
			<?php endif;?>
			<?php endif;?>
			
			<?php if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')):?>
			<?php if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_estimate')):?>
			
			<?php if(!$MSQS_QL->is_plg_lc_p_l()):?>
			<li class="pay-icon <?php if($tab=='refund') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=refund') ?>">Refund</a>
			</li>
			<?php endif;?>
			
			<?php if(!$MSQS_QL->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')):?>
			<li class="pay-icon <?php if($tab=='payment') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=payment') ?>">Payment</a>
			</li>
			<?php endif;?>
			<?php endif;?>
			<?php endif;?>
			
			<?php if($MSQS_QL->option_checked('mw_wc_qbo_sync_qbo_is_default_settings') || $show_srmi):?>
			<li class="pro-icon <?php if($tab=='product') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=product') ?>">Product</a>
			</li>

			<li class="var-icon <?php if($tab=='variation') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=variation') ?>">Variation</a>
			</li>
			<?php endif;?>
			
			<?php if($MSQS_QL->option_checked('mw_qbo_sync_activation_redirect')):?>
			
			<?php /* <li class="var-icon <?php if($tab=='variation') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=variation') ?>">Variation</a>
			</li> */ ?>
			
			<?php if(!$MSQS_QL->is_plg_lc_p_l(false) && $MSQS_QL->get_qbo_company_info('is_sku_enabled')): #lpa?>
			
			<li class="pro-icon <?php if($tab=='inventory') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=inventory') ?>">Inventory Levels</a>
			</li>
			
			<?php endif;?>
			
			<?php if($enable_this=false && $MSQS_QL->get_qbo_company_info('is_category_enabled')):?>
			<li class="cat-icon <?php if($tab=='category') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=category') ?>">Category</a>
			</li>
			<?php endif;?>
			
			<?php endif;?>
			
			<?php if($MSQS_QL->is_wq_vendor_pm_enable()):?>
			<li class="vendor-icon <?php if($tab=='vendor') echo 'active' ?>">
				<a href="<?php echo admin_url('admin.php?page=myworks-wc-qbo-push&tab=vendor') ?>">Vendor</a>
			</li>
			<?php endif;?>
			
		</ul>
	</div>
</nav>

<?php require_once 'myworks-wc-qbo-sync-admin-guidelines.php' ?>