<?php require_once("../global/config.php"); 
function displayDates($date1, $date2, $format) {
	global $db;
	
	$days[1]  = 0;
	$days[2]  = 0;
	$days[3]  = 0;
	$days[4]  = 0;
	$days[5]  = 0;
	$days[6]  = 0;
	$days[7]  = 0;
	$days[8]  = 0;
	$days[9]  = 0;
	$days[10] = 0;
	$days[11] = 0;
	$days[12] = 0;
	
	$current = strtotime($date1);
	$date2 	 = strtotime($date2);
	$stepVal = '+1 day';
	while( $current <= $date2 ) {
	
		$flag		= 1;
		$temp_date 	= date($format, $current);

		$res_type = $db->Execute("select PK_ACADEMIC_CALENDAR_SESSION from M_ACADEMIC_CALENDAR,M_ACADEMIC_CALENDAR_SESSION WHERE M_ACADEMIC_CALENDAR.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_ACADEMIC_CALENDAR.ACTIVE = 1 AND M_ACADEMIC_CALENDAR_SESSION.ACTIVE = 1 AND ACADEMY_DATE = '$temp_date' AND LEAVE_TYPE = 2 AND M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_SESSION.PK_ACADEMIC_CALENDAR ");
		if($res_type->RecordCount() == 0 ) {
			$n 			= date('n',strtotime($temp_date));
			$val 		= $days[$n];
			$val++;
			$days[$n] 	= $val;
		}
		
		$current = strtotime($stepVal, $current);
	}
	return $days;
}
$BEGIN_DATE = $_REQUEST['BEGIN_DATE'];
$END_DATE 	= $_REQUEST['END_DATE'];

if($BEGIN_DATE != '' && $END_DATE != '') {
	$BEGIN_DATE = date("Y-m-d",strtotime($BEGIN_DATE));
	$END_DATE   = date("Y-m-d",strtotime($END_DATE));
	
	$days = displayDates($BEGIN_DATE, $END_DATE,'Y-m-d');
} else {
	$days[1]  = 0;
	$days[2]  = 0;
	$days[3]  = 0;
	$days[4]  = 0;
	$days[5]  = 0;
	$days[6]  = 0;
	$days[7]  = 0;
	$days[8]  = 0;
	$days[9]  = 0;
	$days[10] = 0;
	$days[11] = 0;
	$days[12] = 0;
}
$str = '';
foreach($days as $day) {
	if($str != '')
		$str .= '|||';
		
	$str .= $day;
}
	
echo $str;