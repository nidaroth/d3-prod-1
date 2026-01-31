<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/lead_task_report.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

if(check_access('REPORT_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

$cond = "";
if($_REQUEST['sid'] != ''){
	$cond 		= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_REQUEST[sid]' ";
	$group_by 	= " GROUP BY PK_STUDENT_TASK ";
} else {
	if($_POST['TASK_COMPLETED'] == 1)
		$cond .= " AND S_STUDENT_TASK.COMPLETED = 1";
	else if($_POST['TASK_COMPLETED'] == 2)
		$cond .= " AND S_STUDENT_TASK.COMPLETED = 0";
		
	if($_POST['PK_EMPLOYEE_MASTER'] != '')
		$cond .= " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER IN ($_POST[PK_EMPLOYEE_MASTER]) ";
		
	if($_POST['PK_TASK_TYPE'] != '')
		$cond .= " AND S_STUDENT_TASK.PK_TASK_TYPE IN ($_POST[PK_TASK_TYPE]) ";
		
	if($_POST['PK_TASK_STATUS'] != '')
		$cond .= " AND S_STUDENT_TASK.PK_TASK_STATUS IN ($_POST[PK_TASK_STATUS]) ";
		
	if($_POST['PK_EVENT_OTHER'] != '')
		$cond .= " AND S_STUDENT_TASK.PK_EVENT_OTHER IN ($_POST[PK_EVENT_OTHER]) ";
		
	if($_POST['PK_EVENT_OTHER'] != '')
		$cond .= " AND S_STUDENT_TASK.PK_EVENT_OTHER IN ($_POST[PK_EVENT_OTHER]) ";

	if($_POST['PK_DEPARTMENT'] != ''){
		$dep_t 	= "";
		$PK_DEPARTMENT = explode(",",$_POST['PK_DEPARTMENT']);
		foreach($PK_DEPARTMENT as $t){
			if($dep_t != '')
				$dep_t .= ",";
				
			$dep_t .= get_department_from_t($t);
		}
		
		$cond .= " AND S_STUDENT_TASK.PK_DEPARTMENT IN ($dep_t) ";
	}

	if($_POST['PK_TERM_MASTER'] != '')
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN ($_POST[PK_TERM_MASTER]) ";
		
	/* Ticket # 1751 */
	if($_POST['CREATED_BY'] != '')
		$cond .= " AND S_EMPLOYEE_MASTER_CREATED_BY.PK_EMPLOYEE_MASTER IN ($_POST[CREATED_BY]) ";
	/* Ticket # 1751 */

	$field = "";
	if($_POST['DATE_TYPE'] == 'TD')
		$field = "TASK_DATE";
	else if($_POST['DATE_TYPE'] == 'FD')
		$field = "FOLLOWUP_DATE";
		
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $field BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND $field >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $field <= '$ET' ";
	}
	
	if($_REQUEST['PK_CAMPUS'] != '') {
		$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
	}
	
	if($_REQUEST['PK_STUDENT_STATUS'] != '') {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($_REQUEST[PK_STUDENT_STATUS]) ";
	}
	
	/* Ticket # 1552 */
	if($_REQUEST['SEARCH_TXT'] != '') {
		$cond .= " AND (CONCAT(TRIM(S_STUDENT_MASTER.LAST_NAME),', ', TRIM(S_STUDENT_MASTER.FIRST_NAME)) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.FIRST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.LAST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' ) ";
	}
	/* Ticket # 1552 */
	
	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
	//$group_by 	= " GROUP BY PK_STUDENT_TASK ";
}

/* Ticket # 1751 */
$query = "select PK_STUDENT_TASK, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ', S_STUDENT_MASTER.MIDDLE_NAME) AS STU_NAME ,TASK_TIME, TASK_TYPE, TASK_STATUS, S_STUDENT_TASK.NOTES, M_CAMPUS_PROGRAM.CODE ,IF(TASK_DATE = '0000-00-00', '',  DATE_FORMAT(TASK_DATE,'%Y-%m-%d')) AS TASK_DATE_1, TASK_DATE, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%Y-%m-%d')) AS FOLLOWUP_DATE, FOLLOWUP_TIME, IF(S_STUDENT_TASK.COMPLETED = 1,'Yes','No') as COMPLETED, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) AS EMP_NAME, NOTES_PRIORITY ,S_STUDENT_CONTACT.CELL_PHONE, S_STUDENT_CONTACT.HOME_PHONE ,S_STUDENT_CONTACT.EMAIL, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STUDENT_STATUS, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, CAMPUS_CODE, STUDENT_ID, STUDENT_GROUP, EVENT_OTHER, CONCAT(S_EMPLOYEE_MASTER_CREATED_BY.LAST_NAME,', ',S_EMPLOYEE_MASTER_CREATED_BY.FIRST_NAME) AS CREATED_BY_NAME FROM 

S_STUDENT_MASTER 
LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' 
, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
 , S_STUDENT_TASK 
LEFT JOIN Z_USER as Z_USER_CREATED_BY ON Z_USER_CREATED_BY.PK_USER = S_STUDENT_TASK.CREATED_BY 
LEFT JOIN S_EMPLOYEE_MASTER as S_EMPLOYEE_MASTER_CREATED_BY ON S_EMPLOYEE_MASTER_CREATED_BY.PK_EMPLOYEE_MASTER = Z_USER_CREATED_BY.ID AND PK_USER_TYPE IN (1,2)  

LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_TASK.PK_NOTES_PRIORITY_MASTER 
LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER 
LEFT JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE 
LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS 
LEFT JOIN M_EVENT_OTHER ON M_EVENT_OTHER.PK_EVENT_OTHER = S_STUDENT_TASK.PK_EVENT_OTHER 
,M_STUDENT_STATUS $table 
WHERE 
S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
S_STUDENT_MASTER.ARCHIVED = 0 AND 
S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_TASK.PK_STUDENT_ENROLLMENT AND 
S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER  = S_STUDENT_TASK.PK_STUDENT_MASTER AND 
M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND M_STUDENT_STATUS.ADMISSIONS = 1 $cond ";
/* Ticket # 1751 */

$order_by = " ORDER BY CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) ASC, CAMPUS_CODE ASC, CONCAT(S_STUDENT_MASTER.LAST_NAME,' ',S_STUDENT_MASTER.FIRST_NAME) ASC, TASK_DATE ASC  ";

/* Ticket # 1216 */
$_SESSION['task_report_query'] 		= $query;
$_SESSION['task_report_order_by']	= $order_by;
/* Ticket # 1216 */
//echo $query.$group_by.$order_by;
$res_stud = $db->Execute($query.$group_by." ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,' ',S_STUDENT_MASTER.FIRST_NAME) ASC "); ?>
<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<!-- Ticket # 1216 -->
			<th>
				<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<!-- Ticket # 1216 -->
			<th><?=STUDENT?></th>
			<th><?=STUDENT_ID?></th>
			<th><?=CAMPUS_CODE?></th>
			<th><?=FIRST_TERM?></th>
			<th><?=PROGRAM?></th>
			<th><?=STATUS?></th>
			<th><?=STUDENT_GROUP?></th>
			<? if($_REQUEST['sid'] != ''){ ?>
			<th><?=TASK_TYPE?></th>
			<th><?=TASK_STATUS?></th>
			<th><?=COMPLETED?></th>
			<? } ?>
			<th>
				<?=TOTAL_COUNT.': '.$res_stud->RecordCount() ?>
				<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span> <!-- Ticket # 1216 -->
			</th>
		</tr>
	</thead>
	<tbody>
	<? while (!$res_stud->EOF) { ?>
		<tr>
			<!-- Ticket # 1216 -->
			<td>
				<input type="checkbox" name="PK_STUDENT_MASTER[]" id="PK_STUDENT_MASTER" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" onclick="get_count()" />
			</td>
			<!-- Ticket # 1216 -->
			<td >
				<?=$res_stud->fields['STU_NAME']?>
			</td>
			<td >
				<?=$res_stud->fields['STUDENT_ID']?>
			</td>
			
			<td >
				<?=$res_stud->fields['CAMPUS_CODE']?>
			</td>
			<td >
				<?=$res_stud->fields['BEGIN_DATE_1']?>
			</td>
			<td >
				<?=$res_stud->fields['CODE']?>
			</td>
			<td >
				<?=$res_stud->fields['STUDENT_STATUS']?>
			</td>
			<td >
				<?=$res_stud->fields['STUDENT_GROUP']?>
			</td>
			
			<? if($_REQUEST['sid'] != ''){ ?>
			<td >
				<?=$res_stud->fields['TASK_TYPE']?>
			</td>
			<td >
				<?=$res_stud->fields['TASK_STATUS']?>
			</td>
			<td colspan="2" >
				<?=$res_stud->fields['COMPLETED']?>
			</td>
			<? } ?>
		</tr>
		
	<?	$res_stud->MoveNext();
	} ?>
	</tbody>
</table>