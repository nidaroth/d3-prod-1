<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/notes.php");
require_once("get_department_from_t.php");

/* Ticket #1066  */
if($_SESSION['PK_ROLES'] == 3){
} else {
	require_once("check_access.php");

	$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
	$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
	$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
	$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
	$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

	if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0 && $PLACEMENT_ACCESS == 0 ){ 
		header("location:../index");
		exit;
	}
}
/* Ticket #1066  */
$PK_DEPARTMENT = get_department_from_t($_REQUEST['t']);

$search 	= $_REQUEST['search'];
$sid 		= $_REQUEST['sid'];
$eid 		= $_REQUEST['eid'];
$t 			= $_REQUEST['t'];
$event		= $_REQUEST['event'];
$all_dept	= $_REQUEST['all_dept'];

$s_field = $_REQUEST['field'];
$s_order = $_REQUEST['order'];

if($_REQUEST['field'] == '')
	$_REQUEST['field'] = " cast(CONCAT(NOTE_DATE,' ',NOTE_TIME)as datetime) DESC "; //Ticket # 1019

$task_cond = " AND IS_EVENT = '$event' ";
if($search != '')
	$task_cond .= " AND (NOTE_TYPE like '%$search%' OR CONCAT(EMP.FIRST_NAME,' ',EMP.LAST_NAME) like '%$search%' OR S_STUDENT_NOTES.NOTES like '%$search%') ";

if($event == 1)
	$note_type = 'event';
else
	$note_type = 'notes';

$cols = 10;
if($event == 1)
	$cols++;

?> 
<table class="table table-hover lessPadding" >
	<thead>
		<tr>
			<? if($s_field == 'BEGIN_DATE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
				
			<th onclick="search_notes('','<?=$event?>','BEGIN_DATE','<?=$s_order?>')" style="cursor: pointer;" ><?=ENROLLMENT?></th>
			
			<? if($s_field == 'S_STUDENT_NOTES.NOTE_DATE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC '; 
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_notes('','<?=$event?>','S_STUDENT_NOTES.NOTE_DATE','<?=$s_order?>')" style="cursor: pointer;" ><?=DATE_TIME_1?></th>
			
			<? if($s_field == 'DEPARTMENT') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_notes('','<?=$event?>','DEPARTMENT','<?=$s_order?>')" style="cursor: pointer;" ><?=DEPARTMENT?></th>
			
			
			<? if($s_field == 'EMP_NAME') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC '; 
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_notes('','<?=$event?>','EMP_NAME','<?=$s_order?>')" style="cursor: pointer;"><?=EMPLOYEE?></th>
			
			<? if($s_field == 'NOTE_TYPE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC '; 
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_notes('','<?=$event?>','NOTE_TYPE','<?=$s_order?>')" style="cursor: pointer;">
				<? if($event == 1) echo EVENT_TYPE; else echo NOTE_TYPE_1;?>
			</th>
			
			<? if($s_field == 'NOTE_STATUS') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC '; 
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_notes('','<?=$event?>','NOTE_STATUS','<?=$s_order?>')" style="cursor: pointer;">
				<? if($event == 1) echo EVENT_STATUS; else echo NOTE_STATUS_1;?>
			</th>
			
			<? if($event == 1) {
				if($s_field == 'EVENT_OTHER') {
					if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
						$s_order = ' DESC '; 
					else
						$s_order = ' ASC '; 
				} else 
					$s_order = ' ASC '; ?>
				<th onclick="search_notes('','<?=$event?>','EVENT_OTHER','<?=$s_order?>')" style="cursor: pointer;"><?=EVENT_OTHER?></th>
			<? } ?>
			
			<? if($s_field == 'FOLLOWUP_DATE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_notes('','<?=$event?>','FOLLOWUP_DATE','<?=$s_order?>')" style="cursor: pointer;"><?=FOLLOW_UP_DATE_TIME?></th>
			
			<? if($s_field == 'SATISFIED') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_notes('','<?=$event?>','SATISFIED','<?=$s_order?>')"  style="cursor: pointer;"><?=COMPLETE?></th>

			<th><?=ATTACHMENTS?></th>
			<th><?=OPTION?></th>
		</tr>
	</thead>
	<tbody>
		<? //AND PK_STUDENT_ENROLLMENT = '$eid'
		$cond = "";
		
		/* Ticket #1066  */
		/* Ticket # 1467 */
		if($all_dept == 1) {
		} else {
			if($_SESSION['PK_ROLES'] == 3) {
				/*if($_SESSION['FROM_NSTRUCTOR_PANEL'] == 1)
					$task_cond .= " AND S_STUDENT_NOTES.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ";
				else*/
					$cond = " AND (S_STUDENT_NOTES.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_NOTES.PK_DEPARTMENT = -1 OR S_STUDENT_NOTES.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]') ";
			} else {
				if($all_dept != 1)
					$cond = " AND (S_STUDENT_NOTES.PK_DEPARTMENT = '$PK_DEPARTMENT' OR S_STUDENT_NOTES.PK_DEPARTMENT = -1) ";
			}
		}
		/* Ticket #1066  */
		
		$res_type = $db->Execute("select PK_STUDENT_NOTES, NOTE_STATUS, CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(NOTE_DATE = '0000-00-00', '', DATE_FORMAT(NOTE_DATE,'%m/%d/%Y')) AS NOTE_DATE, NOTE_TIME, S_STUDENT_NOTES.NOTES, NOTE_TYPE, S_COMPANY.COMPANY_NAME, CONCAT(EMP.FIRST_NAME,' ',EMP.LAST_NAME) AS EMP_NAME, IF(IS_EVENT = 1,'Yes', 'No') AS IS_EVENT, NOTES_PRIORITY, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) FOLLOWUP_DATE, FOLLOWUP_TIME, SATISFIED as SATISFIED_1, CONCAT(CREATED_EMP.FIRST_NAME,' ',CREATED_EMP.LAST_NAME) AS CREATED_BY, PK_NOTE_TYPE_MASTER, PK_DEPARTMENT_MASTER, S_STUDENT_NOTES.PK_DEPARTMENT, if(S_STUDENT_NOTES.PK_DEPARTMENT = -1, 'All Departments', DEPARTMENT) AS DEPARTMENT, S_STUDENT_NOTES.PK_EMPLOYEE_MASTER, EVENT_OTHER, IF(SATISFIED = 1,'Yes','No') as SATISFIED FROM S_STUDENT_NOTES LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_NOTES.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER AS CREATED_EMP ON Z_USER.ID = CREATED_EMP.PK_EMPLOYEE_MASTER LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_NOTES.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER AS EMP ON EMP.PK_EMPLOYEE_MASTER = S_STUDENT_NOTES.PK_EMPLOYEE_MASTER LEFT JOIN M_NOTES_PRIORITY_MASTER ON M_NOTES_PRIORITY_MASTER.PK_NOTES_PRIORITY_MASTER = S_STUDENT_NOTES.PK_NOTES_PRIORITY_MASTER LEFT JOIN M_EVENT_OTHER ON M_EVENT_OTHER.PK_EVENT_OTHER = S_STUDENT_NOTES.PK_EVENT_OTHER  LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_STUDENT_NOTES.PK_NOTE_STATUS LEFT JOIN M_NOTE_TYPE ON S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE LEFT JOIN S_COMPANY ON S_STUDENT_NOTES.PK_COMPANY = S_COMPANY.PK_COMPANY WHERE S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_MASTER = '$sid'   $cond $task_cond ORDER BY $_REQUEST[field] $_REQUEST[order] ");
		while (!$res_type->EOF) { 
			$PK_DEPARTMENT_MASTER = $res_type->fields['PK_DEPARTMENT_MASTER']; ?>
			<tr <? if($res_type->fields['PK_NOTE_TYPE_MASTER'] == 1 && $res_type->fields['SATISFIED_1'] == 0) { ?> style="background-color: #F77C7C !important;color: #fff;" <? } ?> >
				<td style="border-bottom: none;" ><?=$res_type->fields['CODE'].'<br />'.$res_type->fields['BEGIN_DATE_1']?></td>
				<td style="border-bottom: none;" >
					<? echo $res_type->fields['NOTE_DATE'];
					if($res_type->fields['NOTE_TIME'] != '00-00-00' && $res_type->fields['NOTE_DATE'] != '') 
						echo '<br />'.date("h:i A", strtotime($res_type->fields['NOTE_TIME'])); ?>
				</td>
				<td style="border-bottom: none;" ><?=$res_type->fields['DEPARTMENT']?></td>
				
				<td style="border-bottom: none;" ><?=$res_type->fields['EMP_NAME']?></td>
				<td style="border-bottom: none;" ><?=$res_type->fields['NOTE_TYPE']?></td>
				<td style="border-bottom: none;" ><?=$res_type->fields['NOTE_STATUS']?></td>
				<? if($event == 1) { ?>
				<td style="border-bottom: none;" ><?=$res_type->fields['EVENT_OTHER']?></td>
				<? } ?>
				
				<td style="border-bottom: none;" >
					<? echo $res_type->fields['FOLLOWUP_DATE']; 
						if($res_type->fields['FOLLOWUP_TIME'] != '00-00-00' && $res_type->fields['FOLLOWUP_DATE'] != '') 
							echo '<br />'.date("h:i A", strtotime($res_type->fields['FOLLOWUP_TIME'])); ?>
				</td>
				
				<td style="border-bottom: none;" ><?=$res_type->fields['SATISFIED']?></td>
				
				<td style="border-bottom: none;" >
					<? $PK_STUDENT_NOTES = $res_type->fields['PK_STUDENT_NOTES']; 
					$res_at1 = $db->Execute("select DOCUMENT_NAME,DOCUMENT_PATH from S_STUDENT_NOTES_DOCUMENTS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_NOTES = '$PK_STUDENT_NOTES' AND DOCUMENT_PATH != '' ");
					while (!$res_at1->EOF) { ?>
						<a href="<?=aws_url($res_at1->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_at1->fields['DOCUMENT_NAME']?></a><br />
					<? $res_at1->MoveNext();
					} ?>
				</td>
				
				<td style="border-bottom: none;" >
					<? /* Ticket #1468  */
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
						<a href="student_notes?sid=<?=$sid?>&id=<?=$res_type->fields['PK_STUDENT_NOTES']?>&t=<?=$_REQUEST['t']?>&eid=<?=$eid?>&event=<?=$event?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
					<? } ?>
					
					<? /* Ticket #1262  */
					if($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2) { ?>
						<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_NOTES']?>','<?=$note_type?>')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
					<? } 
					/* Ticket #1262  */ ?>
				</td>
			</tr>
			<!-- DIAM - 1183-->
			<? if($event == 1) { ?>
				<tr>
					<?  
						$company_text = '';
						if($res_type->fields['PK_DEPARTMENT'] == -1 || $PK_DEPARTMENT_MASTER == 6)
						{
							if($res_type->fields['COMPANY_NAME'] != ''){
								$company_text = $res_type->fields['COMPANY_NAME'];
							}
						}
					?>
					<td style="border-top: none;" ></td>
					<td colspan="<?=$cols?>" style="border-top: none;" ><?='Company: '.$company_text?></td>
				</tr>
			<? } ?>
			<!-- End DIAM - 1183-->
			<tr>
				<td style="border-top: none;" ></td>
				<td colspan="<?=$cols?>" style="border-top: none;" ><?=nl2br($res_type->fields['NOTES'])?></td>
			</tr>
		<?	$res_type->MoveNext();
		} ?>
	</tbody>
</table>