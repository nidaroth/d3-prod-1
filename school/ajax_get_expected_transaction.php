<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/expected_transaction.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0 || check_access('MANAGEMENT_DIAMOND_PAY') == 0  ) {
	header("location:../index");
	exit;
}
$ST = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
$ET = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
$cond = " AND DISBURSEMENT_DATE BETWEEN '$ST' AND '$ET' ";

if($_REQUEST['PK_AR_LEDGER_CODE'] != '')
	$cond .= " AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE IN ($_REQUEST[PK_AR_LEDGER_CODE]) ";
	
if($_REQUEST['PK_STUDENT_STATUS'] != '')
	$cond .= " AND  S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS  IN ($_REQUEST[PK_STUDENT_STATUS]) ";

$campus_cond1 = "";
if($_REQUEST['PK_CAMPUS'] != '') {
	$cond .= " AND  S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($_REQUEST[PK_CAMPUS])) ";
	$campus_cond1 = " AND S_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
}
	
$query = "select CONCAT(LAST_NAME,' ',FIRST_NAME) as NAME, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d' )) AS DISBURSEMENT_DATE_1, CONCAT(CODE,' - ',LEDGER_DESCRIPTION) AS LEDGER, STUDENT_STATUS, DISBURSEMENT_AMOUNT, STUDENT_ID, S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT    
from 
S_STUDENT_DISBURSEMENT, S_STUDENT_ENROLLMENT 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
, S_STUDENT_MASTER 
LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
, M_AR_LEDGER_CODE  
WHERE 
S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
PK_DISBURSEMENT_STATUS = 2 AND QUICK_PAYMENT = 1 AND 
S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND 
S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER $cond 
ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC, DISBURSEMENT_DATE ASC ";
//echo $query;
$_SESSION['query'] = $query;

?>
<table data-toggle="table" class="table-striped" id="report_1" >
	<thead>
		<tr>
			<th ><?=STUDENT?></th>
			<th ><?=STUDENT_ID?></th>
			<th ><?=CAMPUS?></th>
			<th ><?=STUDENT_STATUS?></th>
			<th ><?=LEDGER_CODE?></th>
			<th ><?=DISBURSEMENT_DATE?></th>
			<th ><?=DISBURSEMENT_AMOUNT?></th>
		</tr>
	</thead>
	<tbody>
	<? $res_payment = $db->Execute($query);
	while (!$res_payment->EOF) { ?>
		<tr >
			<td ><?=$res_payment->fields['NAME']?></td>
			<td ><?=$res_payment->fields['STUDENT_ID']?></td>
			<td >
				<? $PK_STUDENT_ENROLLMENT = $res_payment->fields['PK_STUDENT_ENROLLMENT'];
				$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  $campus_cond1 ");
				echo $res_campus->fields['CAMPUS_CODE']; ?>
			</td>
			<td ><?=$res_payment->fields['STUDENT_STATUS']?></td>
			<td ><?=$res_payment->fields['LEDGER']?></td>
			<td ><?=$res_payment->fields['DISBURSEMENT_DATE_1']?></td>
			<td ><div style="padding-top: 11px;width:100%;text-align:right" >$ <?=number_format_value_checker($res_payment->fields['DISBURSEMENT_AMOUNT'],2)?></div></td>
		</tr>
	<?	$res_payment->MoveNext();
	} ?>
	</tbody>
</table>