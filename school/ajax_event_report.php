<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/event_report.php");
require_once("../language/menu.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

$cond = "";
if($_REQUEST['NOTE_DATE_TYPE'] == 'ND'){
	$field = "NOTE_DATE";
}
else if($_REQUEST['NOTE_DATE_TYPE'] == 'FD'){
	$field = "FOLLOWUP_DATE";
}
else if($_REQUEST['NOTE_DATE_TYPE'] == 'ED'){
	$field = "NOTE_DATE";
}
	

if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
	$ST = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
	$ET = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
	$cond = " AND $field BETWEEN '$ST' AND '$ET' ";
} else if($_REQUEST['START_DATE'] != ''){
	$ST = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
	$cond = " AND $field >= '$ST' ";
} else if($_REQUEST['END_DATE'] != ''){
	$ET = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
	$cond = " AND $field <= '$ET' ";
}

if($_REQUEST['PK_DEPARTMENT'] != '') {
	$dep_t 	= "";
	$PK_DEPARTMENT = explode(",",$_REQUEST['PK_DEPARTMENT']);
	foreach($PK_DEPARTMENT as $t){
		if($dep_t != '')
			$dep_t .= ",";
			
		$dep_t .= get_department_from_t($t);
	}
	
	if($_REQUEST['type'] == 'task' )
		$cond .= " AND S_STUDENT_TASK.PK_DEPARTMENT IN ($dep_t) ";
	else if($_REQUEST['type'] == 'notes' || $_REQUEST['type'] == 'event')
		$cond .= " AND S_STUDENT_NOTES.PK_DEPARTMENT IN ($dep_t) ";
	else if($_REQUEST['type'] == 'texts')
		$cond .= " AND S_TEXT_LOG.PK_DEPARTMENT IN ($dep_t) ";
}
if($_REQUEST['PK_EMPLOYEE_MASTER'] != '') {
	if($_REQUEST['type'] == 'task')
		$cond .= " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER IN ($_REQUEST[PK_EMPLOYEE_MASTER]) ";
	else if($_REQUEST['type'] == 'notes' || $_REQUEST['type'] == 'event')
		$cond .= " AND S_STUDENT_NOTES.PK_EMPLOYEE_MASTER IN ($_REQUEST[PK_EMPLOYEE_MASTER]) ";
}

if($_REQUEST['COMPLETED'] == 1){
	if($_REQUEST['type'] == 'task')
		$cond .= " AND S_STUDENT_TASK.COMPLETED = 1";
	else if($_REQUEST['type'] == 'notes' || $_REQUEST['type'] == 'event')
		$cond .= " AND S_STUDENT_NOTES.SATISFIED = 1";
} else if($_REQUEST['COMPLETED'] == 2) {
	if($_REQUEST['type'] == 'task')
		$cond .= " AND S_STUDENT_TASK.COMPLETED = 0";
	else if($_REQUEST['type'] == 'notes' || $_REQUEST['type'] == 'event')
		$cond .= " AND S_STUDENT_NOTES.SATISFIED = 0";
}

if($_REQUEST['PK_COMPANY'] != '')
{
	$cond .= " AND S_STUDENT_NOTES.PK_COMPANY = '$_REQUEST[PK_COMPANY]' ";
}
	
if($_REQUEST['PK_CAMPUS'] != '') {
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
}

// DIAM-1279
if($_REQUEST['PK_TERM_MASTER'] != '') {
	$cond .= " AND S_TERM_MASTER.PK_TERM_MASTER IN ($_REQUEST[PK_TERM_MASTER]) ";
}

if($_REQUEST['PK_CAMPUS_PROGRAM'] != '') {
	$cond .= " AND M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM IN ($_REQUEST[PK_CAMPUS_PROGRAM]) ";
}

if($_REQUEST['PK_STUDENT_STATUS'] != '') {
	$cond .= " AND M_STUDENT_STATUS.PK_STUDENT_STATUS IN ($_REQUEST[PK_STUDENT_STATUS]) ";
}

if($_REQUEST['CREATED_BY'] != '') {
	$cond .= " AND S_EMPLOYEE_MASTER_CREATED_BY.PK_EMPLOYEE_MASTER IN ($_REQUEST[CREATED_BY]) ";
}

 if($_REQUEST['type'] == 'notes' || $_REQUEST['type'] == 'event') {	
			
	if(!empty($_REQUEST['PK_NOTE_TYPE']))
	{
		$cond .= " AND S_STUDENT_NOTES.PK_NOTE_TYPE IN (".$_REQUEST['PK_NOTE_TYPE'].") ";
	}
		
	if(!empty($_REQUEST['PK_NOTE_STATUS']))
	{
		$cond .= " AND S_STUDENT_NOTES.PK_NOTE_STATUS IN (".$_REQUEST['PK_NOTE_STATUS'].") ";
	}
		
	if(!empty($_REQUEST['PK_EVENT_OTHER']))
	{
		$cond .= " AND S_STUDENT_NOTES.PK_EVENT_OTHER IN (".$_REQUEST['PK_EVENT_OTHER'].") ";
	}
		
}
	
if($_REQUEST['type'] == 'notes' || $_REQUEST['type'] == 'event' ) 
{
	
	if($_REQUEST['type'] == 'notes')
		$event_cond = " AND IS_EVENT = 0 ";
	else
		$event_cond = " AND IS_EVENT = 1 ";
	$query = "SELECT PK_STUDENT_NOTES, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.MIDDLE_NAME) as NAME, STUDENT_ID, NOTE_STATUS, CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1, IF(NOTE_DATE = '0000-00-00', '', DATE_FORMAT(NOTE_DATE,'%Y-%m-%d')) AS NOTE_DATE_1, NOTE_TIME, IF(FOLLOWUP_DATE = '0000-00-00', '', DATE_FORMAT(FOLLOWUP_DATE,'%Y-%m-%d')) AS FOLLOWUP_DATE_1, FOLLOWUP_TIME, S_STUDENT_NOTES.NOTES, NOTE_TYPE, CONCAT(EMP.FIRST_NAME,' ',EMP.LAST_NAME) AS EMP_NAME, NOTES_PRIORITY, SATISFIED, CONCAT(S_EMPLOYEE_MASTER_CREATED_BY.FIRST_NAME,' ',S_EMPLOYEE_MASTER_CREATED_BY.LAST_NAME) AS CREATED_BY,PK_NOTE_TYPE_MASTER, if(S_STUDENT_NOTES.PK_DEPARTMENT = -1, 'All Departments', DEPARTMENT) AS DEPARTMENT, EVENT_OTHER,S_STUDENT_NOTES.PK_EVENT_OTHER, IF(SATISFIED = 1, 'Yes', 'No') as COMPLETED, CAMPUS_CODE, STUDENT_STATUS, STUDENT_GROUP, S_STUDENT_NOTES.PK_STUDENT_ENROLLMENT, S_STUDENT_NOTES.PK_STUDENT_MASTER, S_STUDENT_CONTACT.CELL_PHONE, S_STUDENT_CONTACT.HOME_PHONE ,S_STUDENT_CONTACT.EMAIL, S_COMPANY.COMPANY_NAME,S_STUDENT_CONTACT.WORK_PHONE
	FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' 
	, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS, S_CAMPUS, S_STUDENT_NOTES 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_NOTES.CREATED_BY AND PK_USER_TYPE IN (1,2) 
	LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_CREATED_BY ON Z_USER.ID = S_EMPLOYEE_MASTER_CREATED_BY.PK_EMPLOYEE_MASTER 
	LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_NOTES.PK_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN S_EMPLOYEE_MASTER AS EMP ON EMP.PK_EMPLOYEE_MASTER = S_STUDENT_NOTES.PK_EMPLOYEE_MASTER 
	LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_NOTES.PK_NOTES_PRIORITY_MASTER 
	LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_STUDENT_NOTES.PK_NOTE_STATUS 
	LEFT JOIN M_NOTE_TYPE ON S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE 
	LEFT JOIN M_EVENT_OTHER ON S_STUDENT_NOTES.PK_EVENT_OTHER = M_EVENT_OTHER.PK_EVENT_OTHER 
	LEFT JOIN S_COMPANY ON S_STUDENT_NOTES.PK_COMPANY = S_COMPANY.PK_COMPANY 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_NOTES.PK_STUDENT_MASTER AND 
	S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond  $event_cond ";
	
	$QUERY_GROUP = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
	$QUERY_ORDER = " ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME,' ', S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC, NOTE_DATE ASC, NOTE_TIME ASC ";
}


$_SESSION['EVENT_QUERY'] 			= $query;
$_SESSION['EVENT_QUERY_ORDER'] 	= $QUERY_ORDER;
$res_stud = $db->Execute($query." ".$QUERY_GROUP." ".$QUERY_ORDER);	
//echo $query." ".$QUERY_GROUP." ".$QUERY_ORDER;
//$res_stud = $db->Execute($query." ".$QUERY_GROUP." ".$QUERY_ORDER); 
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
			<th><?=STUDENT_ID?></th>
			<th><?=CAMPUS_CODE?></th>
			<th><?=FIRST_TERM?></th>
			<th><?=PROGRAM?></th>
			<th><?=STATUS?></th>
			<th><?=STUDENT_GROUP?></th> <!-- Ticket # 1247 -->
			<th>
				<?=TOTAL_COUNT.': '.$res_stud->RecordCount() ?>
				<? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?>
				<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
				<? } ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<? while (!$res_stud->EOF) { ?>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" <? if($_REQUEST['show_count'] == 1) { ?> onclick="get_count()" <? } ?> />
			</th>
			<? } ?>
			<td >
				<input type="hidden" name="PK_STUDENT_MASTER[]" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" >
				<input type="hidden" name="PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" >
				
				<? if($_REQUEST['show_check'] != 1){ ?>
				<input type="hidden" name="PK_STUDENT_ENROLLMENT[]" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" >
				<? } ?>
				
				<?=$res_stud->fields['NAME']?>
			</td>
			<td >
				<?=$res_stud->fields['STUDENT_ID'] ?>
			</td>
			<td >
				<?=$res_stud->fields['CAMPUS_CODE'] ?>
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
	} ?>
	</tbody>
</table>