<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/notes.php");

/* Ticket #1066  */
if($_SESSION['PK_ROLES'] == 3){
} else {
	require_once("check_access.php");
	require_once("get_department_from_t.php");

	$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
	$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
	$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
	$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
	$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');
	
	if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0 && $PLACEMENT_ACCESS == 0 ){ 
		header("location:../index");
		exit;
	}
	
	$PK_DEPARTMENT = get_department_from_t($_REQUEST['t']);
}
/* Ticket #1066  */

$search = $_REQUEST['search'];
$sid 	= $_REQUEST['sid'];
$eid 	= $_REQUEST['eid'];
$t 		= $_REQUEST['t'];
$all_dept	= $_REQUEST['all_dept']; //Ticket # 1467

$s_field = $_REQUEST['field'];
$s_order = $_REQUEST['order'];

if($_REQUEST['field'] == '') {
	//$_REQUEST['field'] = ' COMPLETED ASC, TASK_DATE DESC ';
	$_REQUEST['field'] = ' TASK_DATE DESC, TASK_TIME DESC '; //Ticket # 1075
}
	
$task_cond = "";
if($search != '')
	$task_cond .= " AND (TASK_TYPE like '%$search%' OR CONCAT(FIRST_NAME,' ',LAST_NAME) like '%$search%' OR S_STUDENT_TASK.NOTES like '%$search%') ";
	
?> 
<table class="table  table-hover lessPadding">
	<thead>
		<tr>
			<? if($s_field == 'BEGIN_DATE_1') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','BEGIN_DATE_1','<?=$s_order?>')" style="cursor: pointer;"><?=ENROLLMENT?></th>
			
			<? if($s_field == 'TASK_DATE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','TASK_DATE','<?=$s_order?>')" style="cursor: pointer;"><?=DATE_TIME_1?></th>
			
			<? if($s_field == 'DEPARTMENT') {
					if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
						$s_order = ' DESC '; 
					else
						$s_order = ' ASC ';
				} else 
					$s_order = ' ASC '; ?>
			<th onclick="search_task('','DEPARTMENT','<?=$s_order?>')" style="cursor: pointer;"><?=DEPARTMENT?></th>
			
			<? if($s_field == 'NAME') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','NAME','<?=$s_order?>')" style="cursor: pointer;"><?=EMPLOYEE?></th>
			
			<? if($s_field == 'TASK_TYPE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','TASK_TYPE','<?=$s_order?>')" style="cursor: pointer;"><?=TASK_TYPE?></th>
			
			<? if($s_field == 'TASK_STATUS') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','TASK_STATUS','<?=$s_order?>')" style="cursor: pointer;"><?=TASK_STATUS?></th>
			
			<? if($s_field == 'EVENT_OTHER') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','EVENT_OTHER','<?=$s_order?>')" style="cursor: pointer;"><?=TASK_OTHER?></th>
			
			<? if($s_field == 'NOTES_PRIORITY') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','NOTES_PRIORITY','<?=$s_order?>')" style="cursor: pointer;"><?=PRIORITY?></th>
			
			<? if($s_field == 'FOLLOWUP_DATE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','FOLLOWUP_DATE','<?=$s_order?>')" style="cursor: pointer;"><?=FOLLOW_UP_DATE_TIME?></th>
			
			<? if($s_field == 'COMPLETED') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_task('','COMPLETED','<?=$s_order?>')" style="cursor: pointer;"><?=COMPLETE?></th>

			<th><?=ATTACHMENTS?></th>
			<th><?=OPTIONS?></th>
		</tr>
	</thead>
	<tbody>
		<? //AND PK_STUDENT_ENROLLMENT = '$eid'
		/* Ticket #1066  */
		/*if($_SESSION['PK_ROLES'] == 3)
			$task_cond .= " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ";
		else
			$task_cond .= " AND (S_STUDENT_TASK.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_TASK.PK_DEPARTMENT = -1) ";*/
		/* Ticket #1066  */
		
		/* Ticket # 1467 */
		if($all_dept == 1) {
		} else {
			if($_SESSION['PK_ROLES'] == 3) {
				$task_cond .= " AND (S_STUDENT_TASK.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_TASK.PK_DEPARTMENT = -1 OR S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]') ";
			} else {
				//if($all_dept != 1)
					$task_cond .= " AND (S_STUDENT_TASK.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_TASK.PK_DEPARTMENT = -1) ";
			}
		}
		//echo $task_cond.'---';
		/* Ticket # 1467 */

		$res_type = $db->Execute("select PK_STUDENT_TASK,TASK_TIME, CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, TASK_TYPE,TASK_STATUS,S_STUDENT_TASK.NOTES ,IF(TASK_DATE = '0000-00-00', '',  DATE_FORMAT(TASK_DATE,'%m/%d/%Y')) AS TASK_DATE_1, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, FOLLOWUP_TIME, IF(COMPLETED = 1,'Yes','No') as COMPLETED, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME, NOTES_PRIORITY, PK_DEPARTMENT_MASTER, S_STUDENT_TASK.PK_DEPARTMENT, if(S_STUDENT_TASK.PK_DEPARTMENT = -1, 'All Departments', DEPARTMENT) AS DEPARTMENT, EVENT_OTHER FROM S_STUDENT_TASK LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_TASK.PK_DEPARTMENT LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_TASK.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_EVENT_OTHER ON M_EVENT_OTHER.PK_EVENT_OTHER = S_STUDENT_TASK.PK_EVENT_OTHER LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_TASK.PK_NOTES_PRIORITY_MASTER LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS WHERE S_STUDENT_TASK.PK_STUDENT_MASTER = '$sid' AND S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $task_cond ORDER BY $_REQUEST[field] $_REQUEST[order] ");
		while (!$res_type->EOF) {  
			$PK_DEPARTMENT_MASTER = $res_type->fields['PK_DEPARTMENT_MASTER']; ?>
			<tr>
				<td style="border-bottom: none;" ><?=$res_type->fields['CODE'].'<br />'.$res_type->fields['BEGIN_DATE_1']?></td>
				<td style="border-bottom: none;" >
					<? echo $res_type->fields['TASK_DATE_1'];
					if($res_type->fields['TASK_TIME'] != '00-00-00' && $res_type->fields['TASK_DATE_1'] != '') 
						echo "<br />".date("h:i A", strtotime($res_type->fields['TASK_TIME'])); ?>
				</td>
				<td style="border-bottom: none;" ><?=$res_type->fields['DEPARTMENT']?></td>
				<td style="border-bottom: none;" ><?=$res_type->fields['NAME']?></td>
				<td style="border-bottom: none;" ><?=$res_type->fields['TASK_TYPE']?></td>
				<td style="border-bottom: none;" ><?=$res_type->fields['TASK_STATUS']?></td>
				<td style="border-bottom: none;" ><?=$res_type->fields['EVENT_OTHER']?></td>
				<td style="border-bottom: none;" ><?=$res_type->fields['NOTES_PRIORITY']?></td>
				
				<td style="border-bottom: none;" >
					<? echo $res_type->fields['FOLLOWUP_DATE']; 
						if($res_type->fields['FOLLOWUP_TIME'] != '00-00-00' && $res_type->fields['FOLLOWUP_DATE'] != '') 
							echo '<br />'.date("h:i A", strtotime($res_type->fields['FOLLOWUP_TIME'])); ?>
				</td>
				<td style="border-bottom: none;" ><?=$res_type->fields['COMPLETED']?></td>
				<td style="border-bottom: none;" >
					<? $PK_STUDENT_TASK = $res_type->fields['PK_STUDENT_TASK']; 
					$res_at1 = $db->Execute("select DOCUMENT_NAME,DOCUMENT_PATH from S_STUDENT_TASK_DOCUMENTS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_TASK = '$PK_STUDENT_TASK' AND DOCUMENT_PATH != '' ");
					while (!$res_at1->EOF) { ?>
						<a href="<?=aws_url($res_at1->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_at1->fields['DOCUMENT_NAME']?></a><br />
					<? $res_at1->MoveNext();
					} ?>
				</td>
				<td style="border-bottom: none;" >
					<? //Ticket # 1075
					/* Ticket #1066  */
					
					/* Ticket #1468  */
					$edit_flag = 0;
					if($_SESSION['ADMIN_PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2 || $res_type->fields['PK_DEPARTMENT'] == -1) {
						$edit_flag = 1;
					} else if($PK_DEPARTMENT_MASTER == 2) {
						//admission
						if(($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3) && $t == 1)
							$edit_flag = 1;
					} else if($PK_DEPARTMENT_MASTER == 7) {
						//Registrar
						if(($REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3) && $t == 2)
							$edit_flag = 1;
					} else if($PK_DEPARTMENT_MASTER == 4) {
						//Finance
						if(($FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3) && $t == 3)
							$edit_flag = 1;
					} else if($PK_DEPARTMENT_MASTER == 1) {
						//Accounting
						if(($ACCOUNTING_ACCESS == 2 || $ACCOUNTING_ACCESS == 3) && $t == 5)
							$edit_flag = 1;
					} else if($PK_DEPARTMENT_MASTER == 6) {
						//Placement
						if(($PLACEMENT_ACCESS == 2 || $PLACEMENT_ACCESS == 3) && $t == 6)
							$edit_flag = 1;
					}
					
					if($edit_flag == 1){ ?>
						<a href="student_task?sid=<?=$sid?>&id=<?=$res_type->fields['PK_STUDENT_TASK']?>&eid=<?=$eid?>&t=<?=$_REQUEST['t']?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
					<? } 
					/* Ticket #1468  */ ?>
					
					
					<? 
					/* Ticket #1262  */
					if($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2) { ?>
						<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_TASK']?>','task')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
					<? } 
					/* Ticket #1262  */ ?>
					
				</td>
			</tr>
			<tr>
				<td style="border-top: none;" ></td>
				<td colspan="11" style="border-top: none;" >
					<?=nl2br($res_type->fields['NOTES'])?>
				</td>
			</tr>
		<?	$res_type->MoveNext();
		} ?>
	</tbody>
</table>