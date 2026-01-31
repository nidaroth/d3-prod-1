<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond = " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_NOTES.PK_STUDENT_MASTER AND IS_EVENT = '$_REQUEST[event]' ";
if($_REQUEST['t'] != '') {
	$PK_DEPARTMENT = get_department_from_t($_REQUEST['t']);
	$cond .= " AND S_STUDENT_NOTES.PK_DEPARTMENT = '$PK_DEPARTMENT' ";
}

if($_REQUEST['PK_NOTE_TYPE'] != '')
	$cond .= " AND S_STUDENT_NOTES.PK_NOTE_TYPE = '".$_REQUEST['PK_NOTE_TYPE']."' ";
	
if($_REQUEST['PK_NOTE_STATUS'] != '')
	$cond .= " AND S_STUDENT_NOTES.PK_NOTE_STATUS = '".$_REQUEST['PK_NOTE_STATUS']."' ";
	
if($_REQUEST['PK_EVENT_OTHER'] != '')
	$cond .= " AND S_STUDENT_NOTES.PK_EVENT_OTHER = '".$_REQUEST['PK_EVENT_OTHER']."' ";

if($_REQUEST['PK_EMPLOYEE_MASTER'] != '')
	$cond .= " AND S_STUDENT_NOTES.PK_EMPLOYEE_MASTER = '".$_REQUEST['PK_EMPLOYEE_MASTER']."' ";

if($_REQUEST['FROM_NOTE_DATE'] != '')
	$FROM_NOTE_DATE = date("Y-m-d",strtotime($_REQUEST['FROM_NOTE_DATE']));
else
	$FROM_NOTE_DATE = '';
	
if($_REQUEST['TO_NOTE_DATE'] != '')
	$TO_NOTE_DATE = date("Y-m-d",strtotime($_REQUEST['TO_NOTE_DATE']));
else
	$TO_NOTE_DATE = '';
	
if($_REQUEST['FROM_FOLLOWUP_DATE'] != '')
	$FROM_FOLLOWUP_DATE = date("Y-m-d",strtotime($_REQUEST['FROM_FOLLOWUP_DATE']));
else
	$FROM_FOLLOWUP_DATE = '';
	
if($_REQUEST['TO_FOLLOWUP_DATE'] != '')
	$TO_FOLLOWUP_DATE = date("Y-m-d",strtotime($_REQUEST['TO_FOLLOWUP_DATE']));
else
	$TO_FOLLOWUP_DATE = '';
	
if($FROM_NOTE_DATE != '' && $TO_NOTE_DATE != '') {
	$cond .= " AND S_STUDENT_NOTES.NOTE_DATE BETWEEN '$FROM_NOTE_DATE' AND '$TO_NOTE_DATE' ";
} else if($FROM_NOTE_DATE != '') {
	$cond .= " AND S_STUDENT_NOTES.NOTE_DATE >= '$FROM_NOTE_DATE' ";
} else if($TO_NOTE_DATE != '') {
	$cond .= " AND S_STUDENT_NOTES.NOTE_DATE <='$TO_NOTE_DATE' ";
}

if($FROM_FOLLOWUP_DATE != '' && $TO_FOLLOWUP_DATE != '') {
	$cond .= " AND S_STUDENT_NOTES.FOLLOWUP_DATE BETWEEN '$FROM_FOLLOWUP_DATE' AND '$TO_FOLLOWUP_DATE' ";
} else if($FROM_FOLLOWUP_DATE != '') {
	$cond .= " AND S_STUDENT_NOTES.FOLLOWUP_DATE >= '$FROM_FOLLOWUP_DATE' ";
} else if($TO_FOLLOWUP_DATE != '') {
	$cond .= " AND S_STUDENT_NOTES.FOLLOWUP_DATE <='$TO_FOLLOWUP_DATE' ";
}

if($_REQUEST['NOTE_COMPLETED'] == 1) {
	$cond .= " AND S_STUDENT_NOTES.SATISFIED = 1 ";
} else if($_REQUEST['NOTE_COMPLETED'] == 2) {
	$cond .= " AND S_STUDENT_NOTES.SATISFIED = 0 ";
}

if($_REQUEST['PK_CAMPUS'] != '') {
	$table .= ",S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
}
?>
<table class="table table-hover">
	<thead>
		<tr>
			<th ><input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" /></th>
			<th ><?=NAME?></th>
			<th ><?=ENROLLMENT?></th>
			<th ><?=DATE?></th>
			<th ><?=EMPLOYEE?></th>
			<th ><?=TYPE?></th>
			<th ><?=STATUS?></th>
			<th ><?=COMMENTS?></th>
			<th ><?=COMPLETED?></th>
		</tr>
	</thead>
	
	<? $res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,' ',S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME ,PK_STUDENT_NOTES, NOTE_STATUS, CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(NOTE_DATE = '0000-00-00', '', DATE_FORMAT(NOTE_DATE,'%m/%d/%Y')) AS NOTE_DATE_1, NOTE_TIME, S_STUDENT_NOTES.NOTES, NOTE_TYPE, CONCAT(EMP.FIRST_NAME,' ',EMP.LAST_NAME) AS EMP_NAME, IF(IS_EVENT = 1,'Yes', 'No') AS IS_EVENT, NOTES_PRIORITY, FOLLOWUP_DATE, IF(SATISFIED = 1,'Yes','No') as SATISFIED, CONCAT(CREATED_EMP.FIRST_NAME,' ',CREATED_EMP.LAST_NAME) AS CREATED_BY,PK_NOTE_TYPE_MASTER, if(S_STUDENT_NOTES.PK_DEPARTMENT = -1, 'All Departments', DEPARTMENT) AS DEPARTMENT 
	FROM 
	S_STUDENT_MASTER, S_STUDENT_NOTES 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_NOTES.CREATED_BY AND PK_USER_TYPE IN (1,2) 
	LEFT JOIN S_EMPLOYEE_MASTER AS CREATED_EMP ON Z_USER.ID = CREATED_EMP.PK_EMPLOYEE_MASTER 
	LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_NOTES.PK_STUDENT_ENROLLMENT 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN S_EMPLOYEE_MASTER AS EMP ON EMP.PK_EMPLOYEE_MASTER = S_STUDENT_NOTES.PK_EMPLOYEE_MASTER 
	LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_NOTES.PK_NOTES_PRIORITY_MASTER 
	LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_STUDENT_NOTES.PK_NOTE_STATUS 
	LEFT JOIN M_NOTE_TYPE ON S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE  
	$table 
	WHERE S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $cond  ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,' ',S_STUDENT_MASTER.FIRST_NAME) ASC, NOTE_DATE ASC ");
	
	while (!$res_type->EOF) { ?>
		<tr >
			<td><input type="checkbox" name="PK_STUDENT_NOTES[]" id="PK_STUDENT_NOTES" value="<?=$res_type->fields['PK_STUDENT_NOTES']?>" onclick="get_count()" checked /></td>
			
			<td><?=$res_type->fields['STUD_NAME']?></td>
			<td><?=$res_type->fields['CODE'].'<br />'.$res_type->fields['BEGIN_DATE_1']?></td>
			
			<td>
				<? echo $res_type->fields['NOTE_DATE_1'];
				if($res_type->fields['NOTE_TIME'] != '00-00-00' && $res_type->fields['NOTE_DATE_1'] != '') 
					echo ' '.date("h:i A", strtotime($res_type->fields['NOTE_TIME'])); ?>
			</td>
			<td><?=$res_type->fields['EMP_NAME']?></td>
			<td><?=$res_type->fields['NOTE_TYPE']?></td>
			<td><?=$res_type->fields['NOTE_STATUS']?></td>
			
			<td><?=nl2br($res_type->fields['NOTES'])?></td>
			<td><?=$res_type->fields['SATISFIED']?></td>
		</tr>
	<?	$res_type->MoveNext();
	} ?>
</table>|||<?=$res_type->RecordCount() ?>