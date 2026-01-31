<?php
$HID 	= explode(",",$_REQUEST['HID']);
$DATE 	= explode(",",$_REQUEST['DATE']);
$DATE_MAP 		= array();
$DATE_SORTED 	= array();

$i = 0;
foreach($DATE as $val) {
	$DATE_MAP[$val] = $HID[$i];
	
	$i++;
}

function date_sort($a, $b) {
    return strtotime($a) - strtotime($b);
}
usort($DATE, "date_sort");
//print_r($DATE_MAP);
//print_r($DATE);

$i = 0;
foreach($DATE as $val) {
	$DATE_SORTED[] = $DATE_MAP[$val];
}

//print_r($DATE_SORTED);
echo implode(",",$DATE_SORTED);