<?php
error_reporting(0);
global $pages, $redirect;

$pages = array(
	'tour_start'	   => 'plugins.php',
    'setup_menu'	   => 'admin.php?page=myworks-wc-qbo-sync-connection',
    'real_time_sync'   => 'admin.php?page=myworks-wc-qbo-sync-settings',
    'push_section'     => 'admin.php?page=myworks-wc-qbo-push',
    'log_section'      => 'admin.php?page=myworks-wc-qbo-sync-log',
    'tour_end' 		   =>  'admin.php?page=myworks-wc-qbo-sync&mw_qbo_sync_tour=completed'
);

$redirect = array(
	0   => admin_url( 'plugins.php' ),
    1   => admin_url( 'admin.php?page=myworks-wc-qbo-sync-connection' ),
    2   => admin_url( 'admin.php?page=myworks-wc-qbo-sync-settings' ),
    3   => admin_url( 'admin.php?page=myworks-wc-qbo-push' ),
    4   => admin_url( 'admin.php?page=myworks-wc-qbo-sync-log' ),
    5   => admin_url( 'admin.php?page=myworks-wc-qbo-sync&mw_qbo_sync_tour=completed' )
);

function _pageDetermine(){
	global $pages;
	$current_url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	foreach($pages as $key=>$value){
		if(strpos($current_url, $value) !== false) return $key;
	}
	return;
}

function _tourHelper(){
	global $pages, $redirect;
	call_user_func(_pageDetermine());
}

function tour_start(){
	global $redirect;
	echo '<div id="mw-wc-qbo-sync-tour-0" class="mw-wc-qbo-sync-tour mw-wc-qbo-sync-tour-left step-1-popup" style="position: absolute; width: 320px; top: 471px; left: 160px; z-index: 9999;"><div class="mw-wc-qbo-sync-tour-content"><h3>Welcome to MyWorks Sync!</h3>
<p>Would you like to go on a guided tour of our plugin? It will take less than 60 seconds and get you started with setup & syncing!</p><div class="mw-wc-qbo-sync-tour-buttons"><div class="ass-tour-buttons"><a class="button button-large" href="'.$redirect[5].'">Close</a><a class="button button-large button-primary" href="'.$redirect[1].'">Start Tour</a></div></div></div><div class="mw-wc-qbo-sync-tour-arrow"><div class="mw-wc-qbo-sync-tour-arrow-inner"></div></div></div>';
}

function setup_menu(){
	global $redirect;
	echo '<div id="mw-wc-qbo-sync-tour-0" class="mw-wc-qbo-sync-tour mw-wc-qbo-sync-tour-top step-2-popup" style="position: absolute; width: 320px; top: 97px; left: 1022px; z-index: 9999;"><div class="mw-wc-qbo-sync-tour-content"><h3>Here is your settings panel!</h3>
<p>Here is where you can configure important plugin options that affect the way your surveys are run.</p>
<p>You can come back here anytime after the tour to configure these settings.</p><div class="mw-wc-qbo-sync-tour-buttons"><div class="ass-tour-buttons"><a class="button button-large" href="'.$redirect[5].'">Close</a><a class="button button-large" href="'.$redirect[0].'">Previous</a><a class="button button-large button-primary" href="'.$redirect[2].'">Next</a></div></div></div><div class="mw-wc-qbo-sync-tour-arrow"><div class="mw-wc-qbo-sync-tour-arrow-inner"></div></div></div>';
}

function real_time_sync(){
	global $redirect;
	echo "<script>
			$(document).ready(function(e){
				$('#mw_qbo_sybc_settings_tab_wh_body').show();
				$('#mw_qbo_sybc_settings_tab_wh').parent().addClass('active');
			});
		  </script>";
	echo '<div id="mw-wc-qbo-sync-tour-0" class="mw-wc-qbo-sync-tour mw-wc-qbo-sync-tour-top" style="position: absolute; width: 320px; top: 97px; left: 1022px; z-index: 9999;"><div class="mw-wc-qbo-sync-tour-content"><h3>This is the settings area.</h3>
<p>Here is where you can configure important plugin options that affect the way your surveys are run.</p>
<p>You can come back here anytime after the tour to configure these settings.</p><div class="mw-wc-qbo-sync-tour-buttons"><div class="ass-tour-buttons"><a class="button button-large" href="'.$redirect[5].'">Close</a><a class="button button-large" href="'.$redirect[1].'">Previous</a><a class="button button-large button-primary" href="'.$redirect[3].'">Next</a></div></div></div><div class="mw-wc-qbo-sync-tour-arrow"><div class="mw-wc-qbo-sync-tour-arrow-inner"></div></div></div>';
}

function push_section(){
	global $redirect;
	echo '<div id="mw-wc-qbo-sync-tour-0" class="mw-wc-qbo-sync-tour mw-wc-qbo-sync-tour-left" style="position: absolute; width: 320px; top: 308px; left: 160px; z-index: 9999;"><div class="mw-wc-qbo-sync-tour-content"><h3>After Sale Surveys is made for surveying customers after they complete their purchase.</h3>
<p>It can give you ongoing insights into all the things you have ever wanted to know about your customers.</p>
<p>Asking your customers to complete a survey right after they ordered is the best time to ask because you already have their full attention.</p><div class="mw-wc-qbo-sync-tour-buttons"><div class="ass-tour-buttons"><a class="button button-large" href="'.$redirect[5].'">Close</a><a class="button button-large" href="'.$redirect[2].'">Previous</a><a class="button button-large button-primary" href="'.$redirect[4].'">Next</a></div></div></div><div class="mw-wc-qbo-sync-tour-arrow"><div class="mw-wc-qbo-sync-tour-arrow-inner"></div></div></div>';
}

function log_section(){
	global $redirect;
	echo '<div id="mw-wc-qbo-sync-tour-0" class="mw-wc-qbo-sync-tour mw-wc-qbo-sync-tour-left" style="position: absolute; width: 320px; top: 365px; left: 160px; z-index: 9999;"><div class="mw-wc-qbo-sync-tour-content"><h3>This is the Survey Responses.</h3>
<p>Here you can find every response on every survey from your customers.</p>
<p>You can drill down into particular responses to get more details about how they answered the questions.</p>
<p>This concludes the tour. Click on the button below to add your first survey:</p>
<div class="mw-wc-qbo-sync-tour-buttons"><div class="ass-tour-buttons"><a class="button button-large" href="'.$redirect[5].'">Close</a><a class="button button-large" href="'.$redirect[3].'">Previous</a></div></div></div><div class="mw-wc-qbo-sync-tour-arrow"><div class="mw-wc-qbo-sync-tour-arrow-inner"></div></div></div>';
}

function tour_end(){
	update_option('mw_qbo_sync_tour_completed','true');
}

_tourHelper();
?>