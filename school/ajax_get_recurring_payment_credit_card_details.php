<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/recurring_payment_credit_card_details.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0 || check_access('MANAGEMENT_DIAMOND_PAY') == 0 ) {
	header("location:../index");
	exit;
}

$ST = $_REQUEST['START_DATE'];
$ET = $_REQUEST['END_DATE'];
$cond = " AND DATE_FORMAT(STR_TO_DATE(CARD_EXP, '%m/%Y'), '%m/%Y') BETWEEN '$ST' AND '$ET' ";

if($_REQUEST['PK_STUDENT_STATUS'] != '')
	$cond .= " AND  S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS  IN ($_REQUEST[PK_STUDENT_STATUS]) ";
	
if($_REQUEST['STUDENT'] != '')
	$cond .= " AND CONCAT(LAST_NAME,' ',FIRST_NAME) LIKE '%$_REQUEST[STUDENT]%' ";
	
if($_REQUEST['NAME_ON_CARD'] != '')
	$cond .= " AND NAME_ON_CARD LIKE '%$_REQUEST[NAME_ON_CARD]%' ";
	
if($_REQUEST['IS_PRIMARY'] == 1)
	$cond .= " AND IS_PRIMARY = 1 ";
else if($_REQUEST['IS_PRIMARY'] == 2)
	$cond .= " AND IS_PRIMARY = 0 ";
	
if($_REQUEST['ACTIVE'] == 1)
	$cond .= " AND S_STUDENT_CREDIT_CARD.ACTIVE = 1 ";
else if($_REQUEST['ACTIVE'] == 2)
	$cond .= " AND S_STUDENT_CREDIT_CARD.ACTIVE = 0 ";
	
$campus_cond1 = "";
if($_REQUEST['PK_CAMPUS'] != '') {
	$cond .= " AND  S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($_REQUEST[PK_CAMPUS])) ";
	$campus_cond1 = " AND S_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
}

$query = "select CONCAT(LAST_NAME,' ',FIRST_NAME) as NAME, NAME_ON_CARD, CARD_NO, CARD_EXP, CARD_TYPE, STUDENT_STATUS, IF(IS_PRIMARY = 1,'Yes', 'No') as IS_PRIMARY, IF(S_STUDENT_CREDIT_CARD.ACTIVE = 1,'Yes', 'No') as ACTIVE, STUDENT_ID, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT      
from 
S_STUDENT_CREDIT_CARD, S_STUDENT_MASTER 
LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
, S_STUDENT_ENROLLMENT 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
WHERE 
S_STUDENT_CREDIT_CARD.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
S_STUDENT_CREDIT_CARD.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 $cond 
ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC ";
$_SESSION['query'] = $query;

?>
<table data-toggle="table" class="table-striped" id="report_1" >
	<thead>
		<tr>
			<th ><?=STUDENT?></th>
			<th ><?=STUDENT_ID?></th>
			<th ><?=CAMPUS?></th>
			<th ><?=STUDENT_STATUS?></th>
			<th ><?=NAME_ON_CARD?></th>
			<th ><?=CARD_TYPE?></th>
			<th ><?=LAST_4_CC?></th>
			<th ><?=CARD_EXP_DATE?></th>
			<th ><?=IS_PRIMARY?></th>
			<th ><?=ACTIVE?></th>
		</tr>
	</thead>
	<tbody>
	<? $res_card = $db->Execute($query);
	while (!$res_card->EOF) { ?>
		<tr >
			<td ><?=$res_card->fields['NAME']?></td>
			<td ><?=$res_card->fields['STUDENT_ID']?></td>
			<td >
				<? $PK_STUDENT_ENROLLMENT = $res_card->fields['PK_STUDENT_ENROLLMENT'];
				$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  $campus_cond1 ");
				echo $res_campus->fields['CAMPUS_CODE']; ?>
			</td>
			<td ><?=$res_card->fields['STUDENT_STATUS']?></td>
			<td ><?=$res_card->fields['NAME_ON_CARD']?></td>
			<td ><?=$res_card->fields['CARD_TYPE']?></td>
			<td >
				<? if($res_card->fields['CARD_NO'] != '')
					echo substr($res_card->fields['CARD_NO'],-4) ?>
			</td>
			<td ><?=$res_card->fields['CARD_EXP']?></td>
			
			<td ><?=$res_card->fields['IS_PRIMARY']?></td>
			<td ><?=$res_card->fields['ACTIVE']?></td>
		</tr>
	<?	$res_card->MoveNext();
	} ?>
	</tbody>
</table>