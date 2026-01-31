<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/bulk_text.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond 		= "";
$group_by 	= "";
$table 		= "";

if(!empty($_REQUEST['PK_STUDENT_GROUP']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN (".$_REQUEST['PK_STUDENT_GROUP'].") ";
	


if(!empty($_REQUEST['PK_TERM_MASTER']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN (".$_REQUEST['PK_TERM_MASTER'].") ";


	
if(!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (".$_REQUEST['PK_CAMPUS_PROGRAM'].") ";
	
if(!empty($_REQUEST['PK_STUDENT_STATUS']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".$_REQUEST['PK_STUDENT_STATUS'].") ";

if(!empty($_REQUEST['PK_COURSE']))
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE IN (".$_REQUEST['PK_COURSE'].") ";

if(!empty($_REQUEST['PK_COURSE_OFFERING']))
	$cond .= " AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN (".$_REQUEST['PK_COURSE_OFFERING'].") ";
	

	
$camp_cond = "";
if(!empty($_REQUEST['PK_CAMPUS'])) {
	$table .= ",S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].") AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$camp_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].")  ";
}

// if($_REQUEST['STU_NAME'] != '') {
// 	$cond .= " AND CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) LIKE '%$_REQUEST[STU_NAME]%' ";
// }
// if($_REQUEST['NO_LEAD'] == 1) {
// 	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 ";
// } else if($_REQUEST['LEAD'] == 1) {
// 	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 1 ";
// } else if($_REQUEST['no_admin_check'] == 1) {
// } else 
 	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 ";
	
// if($_REQUEST['bulk_text'] == 1) {
// 	$table .= ",S_STUDENT_CONTACT ";
// 	$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CONTACT.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' AND OPT_OUT = 0 AND S_STUDENT_CONTACT.ACTIVE = 1 ";
// }
	
if(!empty($_REQUEST['PK_COURSE_OFFERING']) || !empty($_REQUEST['PK_COURSE']) || !empty($_REQUEST['COURSE_PK_TERM_MASTER']) ) { //Ticket # 1212, 1214 
	if($_REQUEST['bulk_text'] == 1 || $_REQUEST['page'] == 'letter_gen') {
	} else {
		$table .= ",S_STUDENT_COURSE, S_COURSE_OFFERING ";
		
		$cond .= " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING ";
	}
}	


if($_REQUEST['ENROLLMENT'] == 2 || $_REQUEST['ENROLLMENT'] == "") {
	$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
}


$group_by = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
if($_REQUEST['group_by'])
	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";

  	$wh_cond='';
	$ladger_cond 		= "";
	$FROM_DATE 	= $_REQUEST['START_DATE'];
	$TO_DATE 	= $_REQUEST['END_DATE'];
	if($FROM_DATE != '' && $TO_DATE != ''){
		$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
		$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));
		
		$ladger_cond .= " AND S_STUDENT_DISBURSEMENT.DISBURSEMENT_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ";
	} else if($FROM_DATE != ''){
		$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
		$ladger_cond .= " AND S_STUDENT_DISBURSEMENT.DISBURSEMENT_DATE >= '$FROM_DATE' ";
	} else if($TO_DATE != ''){
		$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));
		$ladger_cond .= " AND S_STUDENT_DISBURSEMENT.DISBURSEMENT_DATE <= '$TO_DATE' ";
	}

  if($_REQUEST['exclude_no_due'] == 1) {	
	$PK_STUDENT_MASTER_ARRAY = array();
	$res_ledger = $db->Execute(	"select S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER,S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, DISBURSEMENT_AMOUNT, IF(DISBURSEMENT_DATE != '0000-00-00', DATE_FORMAT(DISBURSEMENT_DATE,'%m/%d/%Y'),'') AS DISBURSEMENT_DATE_1, CODE, INVOICE_DESCRIPTION from S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE ON S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PAYMENT_BATCH_DETAIL = 0 AND PK_DISBURSEMENT_STATUS IN (2) AND INVOICE = 1 $ladger_cond ORDER BY DISBURSEMENT_DATE ASC");

	while (!$res_ledger->EOF) { 

		$PK_STUDENT_MASTER_ARRAY[]=$res_ledger->fields['PK_STUDENT_MASTER'];
		$res_ledger->MoveNext();
	}

	$PK_STUDENT_MASTER_STR = implode(',',$PK_STUDENT_MASTER_ARRAY);
	
	$wh_cond =" AND S_STUDENT_MASTER.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER_STR)";
}
//exit;

$res_stud = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_GROUP, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STUDENT_ID   
FROM 
S_STUDENT_MASTER LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER , S_STUDENT_ACADEMICS  $table , S_STUDENT_ENROLLMENT
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
, M_STUDENT_STATUS  
WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS $wh_cond $ladger_cond $cond $group_by ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC"); 

?>

<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<? } ?>
			<th><?=STUDENT?></th>
			<th><?=STUDENT_ID?></th><!-- Ticket # 1371 -->
			<th><?=CAMPUS?></th><!-- Ticket # 1371 -->
			<th><?=FIRST_TERM?></th>
			<th><?=PROGRAM?></th>
			<th><?=STATUS?></th>
			<th><?=STUDENT_GROUP?></th> <!-- Ticket # 1247 -->
			<th>	
				<?=TOTAL_COUNT.': '.$res_stud->RecordCount() ?>				
				<? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?>
					<?php } ?>
				<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
				
			</th>
		</tr>
	</thead>
	<tbody id="statusDiv"> 
	<? 
	
	while (!$res_stud->EOF) { ?>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" <? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?> onclick="get_count()" <? } ?> />
			</th>
			<? } ?>
			<td >
				<input type="hidden" name="PK_STUDENT_MASTER[]" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" >
				<input type="hidden" name="PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" id="S_PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" ><!-- Ticket # 1193 --><!-- Ticket # 1673 -->
				
				<? if($_REQUEST['show_check'] != 1){ ?>
				<input type="hidden" name="PK_STUDENT_ENROLLMENT[]" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" >
				<? } ?>
				
				<?=$res_stud->fields['STU_NAME']?>
			</td>
			<td  > <!-- Ticket # 1371 -->
				<?=$res_stud->fields['STUDENT_ID']?>
			</td>
			<!-- Ticket # 1371 -->
			<td >
				<? $PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
				/* Ticket # 1371  */
				$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE, CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' $camp_cond ");
				echo $res_camp_1->fields['CAMPUS_CODE'];
				/* Ticket # 1371  */ ?>
			</td>
			<td  > <!-- Ticket # 1247 -->
				<?=$res_stud->fields['BEGIN_DATE_1']?>
			</td>
			<!-- Ticket # 1247 -->
			<td >
				<?=$res_stud->fields['CODE']?>
			</td>
			<td >
				<?=$res_stud->fields['STUDENT_STATUS']?>
			</td>
			
			
			<td colspan="2" >
				<?=$res_stud->fields['STUDENT_GROUP']?>
			</td>
		</tr>
		
	<?	$res_stud->MoveNext();
		
	} 
	?>
	</tbody>

</table>
