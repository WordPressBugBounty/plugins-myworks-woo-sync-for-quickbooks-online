<?php
if ( ! defined( 'ABSPATH' ) )
exit;

$page_url = 'admin.php?page=myworks-wc-qbo-push&tab=deposit';

global $MWQS_OF;
global $MSQS_QL;
global $wpdb;

$_payment_method = 'stripe';
$_order_currency = 'USD';

$MSQS_QL->_p($_payment_method);	
$MSQS_QL->_p($_order_currency);


$pm_map_data = $MSQS_QL->get_mapped_payment_method_data($_payment_method,$_order_currency);
$enable_batch = (int) $MSQS_QL->get_array_isset($pm_map_data,'enable_batch',0);
$deposit_cron_utc = $MSQS_QL->get_array_isset($pm_map_data,'deposit_cron_utc','');

$wp_timezone = $MSQS_QL->get_sys_timezone();

$MSQS_QL->_p('UTC Deposit Cron Time: '.$deposit_cron_utc);
$MSQS_QL->_p('Wordpress TimeZone: '.$wp_timezone);

echo '<hr/>';

/*Just Testing Other Things - But this looks great*/
if(isset($_GET['time']) && $_GET['time'] == 1 && !empty($deposit_cron_utc) && !empty($wp_timezone)){
	$enable_batch = false;
	$lump_weekend_batches = (int) $pm_map_data['lump_weekend_batches'];
	
	$utc_now = new DateTime();
	$utc_now->setTimezone(new DateTimeZone('UTC'));
	$utc_date = $utc_now->format('Y-m-d');
	//
	//$utc_date = '2019-08-11';
	echo 'Current Date: '.$utc_date.'<br>';
	
	$utc_date_time = $utc_date.' '.$deposit_cron_utc.':00';
	
	$wp_date_time_c = $MSQS_QL->converToTz($utc_date_time,$wp_timezone,'UTC');
	$interval_per_page = 100;
	echo '<b>Currently Showing '.$interval_per_page.' Time Interval</b><br>';
	
	for($i=1;$i <= $interval_per_page;$i++){
		$c_day = date('l',strtotime($wp_date_time_c));
		$prev_dt = date('Y-m-d H:i:s', strtotime('-24 hours', strtotime($wp_date_time_c)));
		$f_time = $wp_date_time_c;
		
		if($lump_weekend_batches){
			if($c_day == 'Monday'){
				$prev_dt = date('Y-m-d H:i:s', strtotime('-72 hours', strtotime($wp_date_time_c)));		
			}
			
			if($c_day == 'Sunday'){
				$prev_dt = date('Y-m-d H:i:s', strtotime('-48 hours', strtotime($wp_date_time_c)));
				$f_time = date('Y-m-d H:i:s', strtotime('+24 hours', strtotime($wp_date_time_c)));
			}
			
			if($c_day == 'Saturday'){
				$f_time = date('Y-m-d H:i:s', strtotime('+48 hours', strtotime($wp_date_time_c)));
			}
		}		
		
		$fday = date('l',strtotime($f_time));
		$pday = date('l',strtotime($prev_dt));
		
		$ext_whr = '';
		$ext_join = '';
		
		$ext_whr = " AND p.post_date BETWEEN '{$prev_dt}' AND '{$f_time}' ";
		$ext_join = "
			INNER JOIN {$wpdb->postmeta} pm1 ON (p.ID=pm1.post_id AND pm1.meta_key = '_payment_method' AND pm1.meta_value = '{$_payment_method}')
			INNER JOIN {$wpdb->postmeta} pm2 ON (p.ID=pm2.post_id AND pm2.meta_key = '_order_currency' AND pm2.meta_value = '{$_order_currency}')
		";
		
		$sql = "
		SELECT p.ID, p.post_date
		FROM
		{$wpdb->prefix}posts as p							
		{$ext_join}
		WHERE
		p.post_type = 'shop_order'
		AND p.post_status NOT IN('auto_draft','trash','draft')
		{$ext_whr}
		ORDER BY post_date DESC;
		";
		
		$q_data =  $MSQS_QL->get_data($sql);
		
		echo $i.'.';
		echo '<div style="background:lightgray; padding:20px;">';
		echo ''.$f_time.' ['.$fday.'] - '.''.$prev_dt.' ['.$pday.']<br>';		
		
		if(is_array($q_data) && !empty($q_data)){
			echo 'Total Orders: '.count($q_data).'<br>';
			
			foreach($q_data as $k => $v){				
				$MSQS_QL->_p(array(
					$v['ID'],$v['post_date'],date('l',strtotime($v['post_date']))
				));
			}
			
		}else{
			echo 'Total Orders: 0<br>';
		}		
		
		echo '</div>';
		echo '<br/>';
		
		$wp_date_time_c = $prev_dt;
	}
	
}

if($enable_batch && !empty($deposit_cron_utc) && !empty($wp_timezone)){
	$lump_weekend_batches = (int) $pm_map_data['lump_weekend_batches'];
	
	$utc_now = new DateTime();
	$utc_now->setTimezone(new DateTimeZone('UTC'));
	$utc_date = $utc_now->format('Y-m-d');
	$utc_date_time = $utc_date.' '.$deposit_cron_utc.':00';
	
	$wp_date_time_c = $MSQS_QL->converToTz($utc_date_time,$wp_timezone,'UTC');
	$last_24_hour_dt = date('Y-m-d H:i:s', strtotime('-24 hours', strtotime($wp_date_time_c)));
	
	$MSQS_QL->_p($utc_date_time);
	$MSQS_QL->_p($wp_date_time_c);	
	$MSQS_QL->_p($last_24_hour_dt);
	
	/*
	$datetime1 = strtotime($wp_date_time_c);
	$datetime2 = strtotime($utc_date_time);
	$interval  = ($datetime2 - $datetime1);
	$minutes   = round($interval / 60);	
	*/
	
	$hours = date('G',strtotime($wp_date_time_c));
	$minutes = date('i',strtotime($wp_date_time_c));
	
	//$MSQS_QL->_p($hours);
	//$MSQS_QL->_p($minutes);
	
	$total_minutes = ($hours*60)+$minutes;
	
	$sql = "
		SELECT 
		DATE_ADD(DATE(p.post_date - INTERVAL ({$total_minutes}) minute), INTERVAL ({$total_minutes})  minute) as interval_start,
		DATE_ADD(DATE(p.post_date - INTERVAL ({$total_minutes}) minute), INTERVAL (24*60 + {$total_minutes})  minute) as interval_end,
		COUNT(*) as cnt,
		GROUP_CONCAT(ID) as order_ids,
		GROUP_CONCAT(post_date) as order_datetimes,
		GROUP_CONCAT(DAYOFWEEK(DATE(p.post_date))) as order_days
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} pm1 ON (p.ID=pm1.post_id AND pm1.meta_key = '_payment_method' AND pm1.meta_value = '{$_payment_method}')
		INNER JOIN {$wpdb->postmeta} pm2 ON (p.ID=pm2.post_id AND pm2.meta_key = '_order_currency' AND pm2.meta_value = '{$_order_currency}')
		WHERE p.post_type='shop_order'
		AND p.post_status NOT IN('auto_draft','trash','draft')
		GROUP BY DATE(p.post_date - interval ({$total_minutes}) minute)
		ORDER BY interval_start DESC;
	";
	
	//$MSQS_QL->_p($sql);
	
	$order_list = $MSQS_QL->get_data($sql);
	//$MSQS_QL->_p($order_list);
	if(is_array($order_list) && !empty($order_list)){
		foreach($order_list as $ol){
			$order_ids = $ol['order_ids'];
			$order_ids = explode(',',$order_ids);
			
			$order_datetimes = $ol['order_datetimes'];
			$order_datetimes = explode(',',$order_datetimes);
			
			$order_days = $ol['order_days'];
			$order_days = explode(',',$order_days);
			$order_days_s = array_map(array($MSQS_QL,'week_day_int_to_str_w'),$order_days);
			
			$is_wd = date('l',strtotime($ol['interval_start']));
			$ie_wd = date('l',strtotime($ol['interval_end']));
			
			echo 'Original Result<br>';
			echo '<div style="background:gray; padding:20px;">';
			$MSQS_QL->_p($ol['interval_start'].'['.$is_wd.']');	
			$MSQS_QL->_p($ol['interval_end'].'['.$ie_wd.']');
			
			$MSQS_QL->_p('Total Orders: '. $ol['cnt']);
			$MSQS_QL->_p('Order IDs:');
			$MSQS_QL->_p($order_ids);
			
			$MSQS_QL->_p($order_datetimes);
			//$MSQS_QL->_p($order_days);
			$MSQS_QL->_p($order_days_s);
			echo '</div>';
			echo '<br/>';
			
			$is_weekend_blank = false;
			if($lump_weekend_batches){
				if(($is_wd == 'Saturday' || $is_wd == 'Sunday') && ($ie_wd == 'Saturday' || $ie_wd == 'Sunday')){
					//continue;
					$is_weekend_blank = true;
				}else{
					$oi_odt_od = $MSQS_QL->mbl_lwb_remove_weekend_orders($order_ids,$order_datetimes,$order_days,$order_days_s,$wp_date_time_c);
					//$MSQS_QL->_p($oi_odt_od);
					$order_ids = $oi_odt_od['order_ids'];
					$order_datetimes = $oi_odt_od['order_datetimes'];
					$order_days = $oi_odt_od['order_days'];
					$order_days_s = $oi_odt_od['order_days_s'];
					
					if($is_wd == 'Monday' || $ie_wd == 'Monday'){
						$start_dt = $ol['interval_start'];
						$last_72_hour_dt = date('Y-m-d H:i:s', strtotime('-48 hours', strtotime($start_dt)));
						if($ie_wd == 'Monday'){
							$start_dt = $ol['interval_end'];
							$last_72_hour_dt = date('Y-m-d H:i:s', strtotime('-72 hours', strtotime($start_dt)));
						}
						
						$ext_whr = '';
						$ext_join = '';
						
						$ext_whr = " AND p.post_date BETWEEN '{$last_72_hour_dt}' AND '{$start_dt}' ";
						$ext_join = "
							INNER JOIN {$wpdb->postmeta} pm1 ON (p.ID=pm1.post_id AND pm1.meta_key = '_payment_method' AND pm1.meta_value = '{$_payment_method}')
							INNER JOIN {$wpdb->postmeta} pm2 ON (p.ID=pm2.post_id AND pm2.meta_key = '_order_currency' AND pm2.meta_value = '{$_order_currency}')
						";
						$sql = "
						SELECT p.ID, p.post_date
						FROM
						{$wpdb->prefix}posts as p							
						{$ext_join}
						WHERE
						p.post_type = 'shop_order'
						AND p.post_status NOT IN('auto_draft','trash','draft')
						{$ext_whr}
						ORDER BY post_date DESC;
						";
						$q_data =  $MSQS_QL->get_data($sql);
						//$MSQS_QL->_p($sql);
						echo '##<>##[BETWEEN: '.$last_72_hour_dt.' - '.$start_dt.']<br>';
						$MSQS_QL->_p($q_data);
							
						if(is_array($q_data) && !empty($q_data)){							
							/**/
							foreach($q_data as $k => $v){
								/*
								$order_ids[] = $v['ID'];
								$order_datetimes[] = $v['post_date'];
								$order_days[] = date('w',strtotime($v['post_date']));
								$order_days_s[] = date('l',strtotime($v['post_date']));
								*/
								$MSQS_QL->_p(array(
									$v['ID'],$v['post_date'],date('w',strtotime($v['post_date'])),date('l',strtotime($v['post_date']))
								));
							}
							
						}else{
							//$is_weekend_blank = true;
						}
					}
					
				}				
			}			
			
			echo 'After Filter<br>';
			echo '<div style="background:lightgray; padding:20px;">';
			$MSQS_QL->_p($ol['interval_start'].'['.$is_wd.']');	
			$MSQS_QL->_p($ol['interval_end'].'['.$ie_wd.']');
			
			if($is_weekend_blank){
				echo '<p>--Weekend --</p>';
			}else{
				$ol['cnt'] = count($order_ids);
				$MSQS_QL->_p('Total Orders: '. $ol['cnt']);
				$MSQS_QL->_p('Order IDs:');
				$MSQS_QL->_p($order_ids);
				
				$MSQS_QL->_p($order_datetimes);
				//$MSQS_QL->_p($order_days);
				$MSQS_QL->_p($order_days_s);
			}			
			
			echo '</div>';
			echo '<br/>';
			echo '<hr>';
		}
	}
	
}