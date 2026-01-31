<?
// $ATTENDANCE_HOUR = $_REQUEST['ATTENDANCE_HOUR'];
// $BREAK_IN_MIN 	 = $_REQUEST['BREAK_IN_MIN'];
// $BREAK1			 = 0;

// if($BREAK_IN_MIN > 0)
// 	$BREAK1 = $BREAK_IN_MIN / 60;
	
// $ATTENDANCE_HOUR = number_format(($ATTENDANCE_HOUR - $BREAK1),2);
// #//echo $ATTENDANCE_HOUR;

// $temp = explode(".",$ATTENDANCE_HOUR);
// if($temp[1] == '' || $temp[1] == 0)
// 	echo $temp[0].':00';
// else {
// 	$min = round(60 / (100 / ($temp[1])));
// 	if($min < 10)
// 		$min = '0'.$min;
// 	echo $temp[0].':'.$min;
// }


$ATTENDANCE_HOUR = $_REQUEST['ATTENDANCE_HOUR'];
$BREAK_IN_MIN 	 = $_REQUEST['BREAK_IN_MIN']; 

$HOURS_DEC = $ATTENDANCE_HOUR - $BREAK_IN_MIN;

$EXPLODED_HOURS_DEC = explode('.',$HOURS_DEC);
$MINUTES_AFTER_EXPLODE = $HOURS_DEC - $EXPLODED_HOURS_DEC[0];
$HOUR_CONVERTED_STR = str_pad($EXPLODED_HOURS_DEC[0],2,"0",STR_PAD_LEFT).':'.str_pad((round($MINUTES_AFTER_EXPLODE * 60 )),2,"0",STR_PAD_LEFT);

// dump($HOURS_DEC);
// dump($EXPLODED_HOURS_DEC[1]);
echo $HOUR_CONVERTED_STR;
