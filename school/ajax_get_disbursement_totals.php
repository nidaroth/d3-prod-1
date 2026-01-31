<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$SID					= $_REQUEST['sid'];
$EID					= $_REQUEST['eid'];

$en_cond="";
if(!empty($EID)){
$en_cond = "AND SD.PK_STUDENT_ENROLLMENT IN ($EID)";
}

$arr_disb_status = array('Unapproved','Approved','Hold','Deposited','Undeposited','Disbursement');
$arr_disb_totals= array();

$DISBUR_TOT_SQL = "SELECT SD.PK_STUDENT_MASTER, SD.PK_STUDENT_ENROLLMENT, LC.CODE, SD.DISBURSEMENT_DATE, SUM(SD.DISBURSEMENT_AMOUNT) AS DISB_TOTAL, SD.APPROVED_DATE, SD.DEPOSITED_DATE, DS.DISBURSEMENT_STATUS, SD.BATCH
FROM S_STUDENT_DISBURSEMENT AS SD
INNER JOIN M_AR_LEDGER_CODE AS LC ON SD.PK_AR_LEDGER_CODE = LC.PK_AR_LEDGER_CODE
LEFT JOIN M_DISBURSEMENT_STATUS AS DS ON SD.PK_DISBURSEMENT_STATUS = DS.PK_DISBURSEMENT_STATUS
WHERE SD.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
AND SD.PK_STUDENT_MASTER = '$SID' $en_cond ";

$wh_cond = '';
if($arr_disb_status[0]=='Unapproved'){
	$wh_cond1 = 'AND DS.DISBURSEMENT_STATUS IS NULL';
	$res_disb_totals = $db->Execute($DISBUR_TOT_SQL.$wh_cond1);
	$arr_disb_totals[] = ($res_disb_totals->fields['DISB_TOTAL'])?$res_disb_totals->fields['DISB_TOTAL']:0;
}

if($arr_disb_status[1]=='Approved'){
	$wh_cond2 = 'AND DS.DISBURSEMENT_STATUS IS NOT NULL AND DS.PK_DISBURSEMENT_STATUS = 2';
	$res_disb_totals = $db->Execute($DISBUR_TOT_SQL.$wh_cond2);
	$arr_disb_totals[] = ($res_disb_totals->fields['DISB_TOTAL'])?$res_disb_totals->fields['DISB_TOTAL']:0;
}

if($arr_disb_status[2]=='Hold'){
	$wh_cond3 = 'AND DS.DISBURSEMENT_STATUS ="Hold"';
	$res_disb_totals = $db->Execute($DISBUR_TOT_SQL.$wh_cond3);
	$arr_disb_totals[] = ($res_disb_totals->fields['DISB_TOTAL'])?$res_disb_totals->fields['DISB_TOTAL']:0;
}

if($arr_disb_status[3]=='Deposited'){
	$wh_cond4 = 'AND SD.DEPOSITED_DATE <> "0000-00-00"';
	$res_disb_totals = $db->Execute($DISBUR_TOT_SQL.$wh_cond4);
	$arr_disb_totals[] =($res_disb_totals->fields['DISB_TOTAL'])?$res_disb_totals->fields['DISB_TOTAL']:0;
}

if($arr_disb_status[4]=='Undeposited'){
	$wh_cond5 = 'AND SD.DEPOSITED_DATE = "0000-00-00"';
	$res_disb_totals = $db->Execute($DISBUR_TOT_SQL.$wh_cond5);
	$arr_disb_totals[] = ($res_disb_totals->fields['DISB_TOTAL'])?$res_disb_totals->fields['DISB_TOTAL']:0;
}

if($arr_disb_status[5]=='Disbursement'){
	//$wh_cond = 'AND DS.DISBURSEMENT_STATUS IS NULL';
	$res_disb_totals = $db->Execute($DISBUR_TOT_SQL);
	$arr_disb_totals[] = ($res_disb_totals->fields['DISB_TOTAL'])?$res_disb_totals->fields['DISB_TOTAL']:0;
}
//print_r($arr_disb_totals);
echo implode('||||',$arr_disb_totals);
?>