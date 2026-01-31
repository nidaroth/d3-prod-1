<? 
$inactive_dt = $_REQUEST['inactive_dt'];
$return_dt 	 = $_REQUEST['return_dt'];
$today		 = $_REQUEST['today'];

/*
if(strtotime($return_dt) > strtotime($today))
	echo "a";
else */
if(strtotime($return_dt) < strtotime($inactive_dt))
	echo "b";
else
	echo "c";
	