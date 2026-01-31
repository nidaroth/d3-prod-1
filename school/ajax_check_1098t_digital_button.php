<?
$year  = $_REQUEST['year'];
$year1 = $_REQUEST['year'] + 1;
if(strtotime(date("Y-m-d")) == strtotime(date($year1."-m-d")) && strtotime(date("Y-m")) == strtotime(date($year1."-03")) )
	echo "a";
else
	echo "b";