<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT DISTINCT(EARNINGS_MONTH)  FROM S_STUDENT_EARNINGS WHERE PK_ACCOUNT =".$_SESSION['PK_ACCOUNT']." AND EARNINGS_YEAR = ".$_REQUEST['id']);

$months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
?>
<option value=""></option>
<?php
if($res->RecordCount() > 0){
	while (!$res->EOF) { 
			?>		
			<option value="<?=$res->fields['EARNINGS_MONTH']?>"><?=$months[$res->fields['EARNINGS_MONTH']]?></option>
	<? 
	$res->MoveNext();
	} 
}else if($_REQUEST['id']==""){
?>
<?php foreach ($months as $key => $value) { ?>
	<option value="<?=$key?>"><?=$value?></option>
<?php } ?>
<!-- <option value=""></option> -->
<? } ?>