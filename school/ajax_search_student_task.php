<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
$PK_DEPARTMENT = get_department_from_t($_REQUEST['t']);

$cond = " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TASK.PK_STUDENT_MASTER ";
if($_REQUEST['PK_TASK_TYPE'] != '')
	$cond .= " AND S_STUDENT_TASK.PK_TASK_TYPE = '".$_REQUEST['PK_TASK_TYPE']."' ";
	
if($_REQUEST['PK_TASK_STATUS'] != '')
	$cond .= " AND S_STUDENT_TASK.PK_TASK_STATUS = '".$_REQUEST['PK_TASK_STATUS']."' ";
	
if($_REQUEST['PK_EVENT_OTHER'] != '')
	$cond .= " AND S_STUDENT_TASK.PK_EVENT_OTHER = '".$_REQUEST['PK_EVENT_OTHER']."' ";
	
if($_REQUEST['PK_NOTES_PRIORITY_MASTER'] != '')
	$cond .= " AND S_STUDENT_TASK.PK_NOTES_PRIORITY_MASTER = '".$_REQUEST['PK_NOTES_PRIORITY_MASTER']."' ";

if($_REQUEST['PK_EMPLOYEE_MASTER'] != '')
	$cond .= " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '".$_REQUEST['PK_EMPLOYEE_MASTER']."' ";

if($_REQUEST['FROM_TASK_DATE'] != '')
	$FROM_TASK_DATE = date("Y-m-d",strtotime($_REQUEST['FROM_TASK_DATE']));
else
	$FROM_TASK_DATE = '';
	
if($_REQUEST['TO_TASK_DATE'] != '')
	$TO_TASK_DATE = date("Y-m-d",strtotime($_REQUEST['TO_TASK_DATE']));
else
	$TO_TASK_DATE = '';
	
if($_REQUEST['FROM_FOLLOWUP_DATE'] != '')
	$FROM_FOLLOWUP_DATE = date("Y-m-d",strtotime($_REQUEST['FROM_FOLLOWUP_DATE']));
else
	$FROM_FOLLOWUP_DATE = '';
	
if($_REQUEST['TO_FOLLOWUP_DATE'] != '')
	$TO_FOLLOWUP_DATE = date("Y-m-d",strtotime($_REQUEST['TO_FOLLOWUP_DATE']));
else
	$TO_FOLLOWUP_DATE = '';
	
if($FROM_TASK_DATE != '' && $TO_TASK_DATE != '') {
	$cond .= " AND S_STUDENT_TASK.TASK_DATE BETWEEN '$FROM_TASK_DATE' AND '$TO_TASK_DATE' ";
} else if($FROM_TASK_DATE != '') {
	$cond .= " AND S_STUDENT_TASK.TASK_DATE >= '$FROM_TASK_DATE' ";
} else if($TO_TASK_DATE != '') {
	$cond .= " AND S_STUDENT_TASK.TASK_DATE <='$TO_TASK_DATE' ";
}

if($FROM_FOLLOWUP_DATE != '' && $TO_FOLLOWUP_DATE != '') {
	$cond .= " AND S_STUDENT_TASK.FOLLOWUP_DATE BETWEEN '$FROM_FOLLOWUP_DATE' AND '$TO_FOLLOWUP_DATE' ";
} else if($FROM_FOLLOWUP_DATE != '') {
	$cond .= " AND S_STUDENT_TASK.FOLLOWUP_DATE >= '$FROM_FOLLOWUP_DATE' ";
} else if($TO_FOLLOWUP_DATE != '') {
	$cond .= " AND S_STUDENT_TASK.FOLLOWUP_DATE <='$TO_FOLLOWUP_DATE' ";
}

if($_REQUEST['TASK_COMPLETED'] == 1) {
	$cond .= " AND S_STUDENT_TASK.COMPLETED = 1 ";
} else if($_REQUEST['TASK_COMPLETED'] == 2) {
	$cond .= " AND S_STUDENT_TASK.COMPLETED = 0 ";
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
			<th ><?=TASK?></th>
			<th ><?=PRIORITY?></th>
			<th ><?=TASK_STATUS?></th>
			<th ><?=NOTES?></th>
			<th ><?=COMPLETED?></th>
		</tr>
	</thead>
	
	<? $res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME ,PK_STUDENT_TASK, TASK_TIME, CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, TASK_TYPE,TASK_STATUS,S_STUDENT_TASK.NOTES ,IF(TASK_DATE = '0000-00-00', '',  DATE_FORMAT(TASK_DATE,'%m/%d/%Y')) AS TASK_DATE_1, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, FOLLOWUP_TIME, IF(COMPLETED = 1,'Yes','No') as COMPLETED, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME, NOTES_PRIORITY 
	FROM 
	S_STUDENT_MASTER, S_STUDENT_TASK 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_TASK.PK_STUDENT_ENROLLMENT 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_TASK.PK_NOTES_PRIORITY_MASTER 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER 
	LEFT JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE 
	LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS 
	$table 
	WHERE S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) ASC, TASK_DATE ASC ");
	while (!$res_type->EOF) { ?>
		<tr >
			<td><input type="checkbox" name="PK_STUDENT_TASK[]" id="PK_STUDENT_TASK" value="<?=$res_type->fields['PK_STUDENT_TASK']?>" onclick="get_count()" checked /></td>
			
			<td><?=$res_type->fields['STUD_NAME']?></td>
			<td><?=$res_type->fields['CODE'].'<br />'.$res_type->fields['BEGIN_DATE_1']?></td>
			
			<td>
				<? echo $res_type->fields['TASK_DATE_1'];
				if($res_type->fields['TASK_TIME'] != '00-00-00' && $res_type->fields['TASK_DATE_1'] != '') 
					echo ' '.date("h:i A", strtotime($res_type->fields['TASK_TIME'])); ?>
			</td>
			<td><?=$res_type->fields['EMP_NAME']?></td>
			<td><?=$res_type->fields['TASK_TYPE']?></td>
			<td><?=$res_type->fields['NOTES_PRIORITY']?></td>
			<td><?=$res_type->fields['TASK_STATUS']?></td>
			<td><?=nl2br($res_type->fields['NOTES'])?></td>
			<td><?=$res_type->fields['COMPLETED']?></td>
		</tr>
	<?	$res_type->MoveNext();
	} ?>
</table>|||<?=$res_type->RecordCount() ?>