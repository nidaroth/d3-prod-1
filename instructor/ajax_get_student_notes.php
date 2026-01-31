<? require_once("../global/config.php"); 
require_once("../language/instructor_student_notes.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_ENROLLMENT = $_REQUEST['val']; ?>
<div class="row">
	<div class="col-12 form-group">
		<button type="button" style="float:right" onclick="create_student_notes('<?=$PK_STUDENT_ENROLLMENT?>')" class="btn waves-effect waves-light btn-info"><?=CREATE_NOTES?></button>
	</div>
</div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th ><?=NOTE_DATE?></th>
			<th ><?=NOTE_TYPE?></th>
			<th ><?=STATUS?></th>
			<th ><?=EMPLOYEE?></th>
			<th ><?=FOLLOW_UP_DATE?></th>
			<th ><?=COMPLETED?></th>
			<th ><?=COMMENTS?></th>
		</tr>
	</thead>
	<tbody>
		<? $res = $db->Execute("select PK_STUDENT_NOTES, NOTE_STATUS, CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(NOTE_DATE = '0000-00-00', '', DATE_FORMAT(NOTE_DATE,'%m/%d/%Y')) AS NOTE_DATE1, NOTE_TIME, S_STUDENT_NOTES.NOTES, NOTE_TYPE, CONCAT(EMP.FIRST_NAME,' ',EMP.LAST_NAME) AS EMP_NAME, IF(IS_EVENT = 1,'Yes', 'No') AS IS_EVENT, NOTES_PRIORITY, IF(FOLLOWUP_DATE = '0000-00-00', '', DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS  FOLLOWUP_DATE, IF(SATISFIED = 1, 'Yes', 'No') as SATISFIED, CONCAT(CREATED_EMP.FIRST_NAME,' ',CREATED_EMP.LAST_NAME) AS CREATED_BY,PK_NOTE_TYPE_MASTER, DEPARTMENT FROM S_STUDENT_NOTES LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_NOTES.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER AS CREATED_EMP ON Z_USER.ID = CREATED_EMP.PK_EMPLOYEE_MASTER LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_NOTES.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER AS EMP ON EMP.PK_EMPLOYEE_MASTER = S_STUDENT_NOTES.PK_EMPLOYEE_MASTER LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_NOTES.PK_NOTES_PRIORITY_MASTER LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_STUDENT_NOTES.PK_NOTE_STATUS LEFT JOIN M_NOTE_TYPE ON S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE  WHERE S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ORDER BY NOTE_DATE ASC ");
		while (!$res->EOF) { ?>
			<tr>
				<td >
					<?=$res->fields['NOTE_DATE1']?>
				</td>
				<td >
					<?=$res->fields['NOTE_TYPE'];?>
				</td>
				<td >
					<?=$res->fields['NOTE_STATUS'] ?>
				</td>
				<td >
					<?=$res->fields['EMP_NAME'];?>
				</td>
				<td >
					<?=$res->fields['FOLLOWUP_DATE'];?>
				</td>
				<td >
					<?=$res->fields['SATISFIED'];?>
				</td>
				<td >
					<?=nl2br($res->fields['NOTES']);?>
				</td>
			</tr>
		<?	$res->MoveNext();
		} ?>
	</tbody>
</table>