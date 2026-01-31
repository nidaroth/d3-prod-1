<? require_once("../global/config.php"); 
//Ticket #670
//$starttimestamp = strtotime($_REQUEST['START_TIME']);
//$endtimestamp 	= strtotime($_REQUEST['END_TIME']);
//$difference 	= number_format((abs($endtimestamp - $starttimestamp)/3600),2);

$starttimestamp = $_REQUEST['START_TIME'];
$endtimestamp 	= $_REQUEST['END_TIME'];
$sst = strtotime($starttimestamp);
$eet=  strtotime($endtimestamp);
$diff= $eet-$sst;
$timeElapsed= gmdate("H:i",$diff);
$timeElapsed = str_replace(":",".",$timeElapsed);
$calculated_time = number_format(abs($timeElapsed),2);
if($calculated_time < 1){
    echo number_format((($calculated_time *100)/60),2);
}else{
    $exptimeElapsed = explode(".",$calculated_time);
    $hours =$exptimeElapsed[0]; 
    $minutes=$exptimeElapsed[1] ; 
    $CalculateHours = $hours + round($minutes / 60, 2); 
    echo number_format($CalculateHours,2);
    //echo $calculated_time ;
}

//echo $difference;
//Ticket #670
