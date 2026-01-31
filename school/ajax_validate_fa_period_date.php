<? 
$begin_dt	= date('Y-m-d', strtotime($_REQUEST['begin']));
$end_dt 	= date('Y-m-d', strtotime($_REQUEST['end']));
$date		= date('Y-m-d', strtotime($_REQUEST['date']));

if (($date >= $begin_dt) && ($date <= $end_dt)){
    echo "a";
} else {
    echo "b";  
}