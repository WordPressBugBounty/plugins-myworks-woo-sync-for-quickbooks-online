<?php
if ( ! defined( 'ABSPATH' ) )
exit;
 
global $MSQS_QL;
$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
?>
<nav class="mw-qbo-sync-grey">
	<div class="nav-wrapper">
		<a class="brand-logo left" href="javascript:void(0)">
			<img src="<?php echo esc_url( plugins_url( 'myworks-woo-sync-for-quickbooks-online/admin/image/mwd-logo.png' ) ) ?>">
		</a>
		<ul class="hide-on-med-and-down right">
			<?php if(!$MSQS_QL->is_plg_lc_p_l()):?>
			<li class="cust-icon <?php if($tab=='customer'/* || !isset($_GET['tab'])*/) echo esc_attr('active') ?>" style="display: none">
				<a href="<?php echo esc_url(admin_url('admin.php?page=myworks-wc-qbo-pull&tab=customer')) ?>">Customer</a>
			</li>
			<li class="invo-icon <?php if($tab=='invoice') echo esc_attr('active') ?>" style="display: none">
				<a href="<?php echo esc_url(admin_url('admin.php?page=myworks-wc-qbo-pull&tab=invoice')) ?>">Invoice</a>
			</li>
			<?php endif;?>
			
			<li class="pro-icon <?php if($tab=='product'/* || !isset($_GET['tab'])*/) echo esc_attr('active') ?>">
				<a href="<?php echo esc_url(admin_url('admin.php?page=myworks-wc-qbo-pull&tab=product')) ?>">Product</a>
			</li>
			
			<?php if(!$MSQS_QL->is_plg_lc_p_l(false)): #lpa?>
			<?php if($MSQS_QL->get_qbo_company_info('is_sku_enabled')):?>
			<li class="inven-icon <?php if($tab=='inventory') echo esc_attr('active') ?>">
				<a href="<?php echo esc_url(admin_url('admin.php?page=myworks-wc-qbo-pull&tab=inventory')) ?>">Inventory Levels</a>
			</li>
			<?php endif;?>
			
			<?php if(!$MSQS_QL->is_plg_lc_p_l() && $MSQS_QL->get_qbo_company_info('is_category_enabled')):?>
			<li class="cat-icon <?php if($tab=='category') echo esc_attr('active') ?>">
				<a href="<?php echo esc_url(admin_url('admin.php?page=myworks-wc-qbo-pull&tab=category')) ?>">Category</a>
			</li>
			<?php endif;?>
			
			<li class="pay-icon <?php if($tab=='payment') echo esc_attr('active') ?>" style="display: none">
				<a href="<?php echo esc_url(admin_url('admin.php?page=myworks-wc-qbo-pull&tab=payment')) ?>">Payment</a>
			</li>
			<?php endif;?>
		</ul>
	</div>
</nav>