<?php
$DATE_OF_BIRTH 	= $_REQUEST['DATE_OF_BIRTH'];
$AGE 			= '';
if($DATE_OF_BIRTH != '') {
	$date = new DateTime($DATE_OF_BIRTH);
	$DATE_OF_BIRTH = $date->format("Y-m-d");
	
	$bday 	= new DateTime($DATE_OF_BIRTH); // Your date of birth
	$today 	= new Datetime(date('Y-m-d'));
	$diff 	= $bday->diff($today);
	if($diff->format('%R') == '-')
		$AGE 	= '';
	else
		$AGE 	= $diff->y;
}
echo $AGE;

