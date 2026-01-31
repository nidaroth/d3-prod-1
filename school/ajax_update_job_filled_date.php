<? require_once("../global/config.php"); 
require_once("check_access.php");

$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');
if($PLACEMENT_ACCESS == 0){
	header("location:../index");
	exit;
}
$COMPANY_JOB['JOB_FILLED'] = date("Y-m-d",strtotime($_REQUEST['date']));
$COMPANY_JOB['OPEN_JOB']   = 'N';
db_perform('S_COMPANY_JOB', $COMPANY_JOB, 'update'," PK_COMPANY_JOB = '$_REQUEST[PK_COMPANY_JOB]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");