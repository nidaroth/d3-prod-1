<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_probation.php");
require_once("../global/common_functions.php");

require_once("get_department_from_t.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0 && $PLACEMENT_ACCESS == 0){
	header("location:../index");
	exit;
}

$sid 		= $_REQUEST['sid'];
$eid 		= $_REQUEST['eid'];
$t 			= $_REQUEST['t'];
$all_dept 	= $_REQUEST['all_dept'];

$doc_dep = get_department_from_t($t);
if($all_dept == 1) {
	$doc_dep_cond = "";
}  else {
	$doc_dep_cond = " AND S_STUDENT_DOCUMENTS_DEPARTMENT.PK_DEPARTMENT = '$doc_dep' ";
}
?>
<table class="table table-hover">
	<thead>
		<tr>
			<th><?=ENROLLMENT?></th>
			<th><?=DEPARTMENT?></th>
			<th><?=REQUESTED?></th>
			<th><?=DOCUMENT?></th>
			<th><?=EMPLOYEE?></th>
			<th><?=FOLLOW_UP_DATE?></th>
			<th><?=RECEIVED?></th>
			<th><?=DATE_RECEIVED?></th>
			<th><?=ATTACHMENTS?></th><!-- Ticket #1136 -->
			<th><?=OPTIONS?></th>
		</tr>
	</thead>
	<tbody>
		<? $res_type = $db->Execute("select S_STUDENT_DOCUMENTS.PK_STUDENT_DOCUMENTS, M_CAMPUS_PROGRAM.CODE,IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, CONCAT(M_DOCUMENT_TYPE.CODE, ' - ', S_STUDENT_DOCUMENTS.DOCUMENT_TYPE) AS DOCUMENT_TYPE, S_STUDENT_DOCUMENTS.NOTES, IF(REQUESTED_DATE = '0000-00-00', '', DATE_FORMAT(REQUESTED_DATE,'%m/%d/%Y')) AS REQUESTED_DATE_1, IF(RECEIVED = 1,'Yes', 'No') as RECEIVED, IF(DATE_RECEIVED = '0000-00-00', '',  DATE_FORMAT(DATE_RECEIVED,'%m/%d/%Y')) AS DATE_RECEIVED, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(LAST_NAME,', ', FIRST_NAME) AS NAME, DOCUMENT_PATH, DOCUMENT_NAME, CAMPUS_CODE, STUDENT_STATUS FROM S_STUDENT_DOCUMENTS LEFT JOIN M_DOCUMENT_TYPE ON M_DOCUMENT_TYPE.PK_DOCUMENT_TYPE = S_STUDENT_DOCUMENTS.PK_DOCUMENT_TYPE LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_DOCUMENTS.PK_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_DOCUMENTS.PK_EMPLOYEE_MASTER , S_STUDENT_DOCUMENTS_DEPARTMENT WHERE S_STUDENT_DOCUMENTS.PK_STUDENT_MASTER = '$sid' AND S_STUDENT_DOCUMENTS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_DOCUMENTS.PK_STUDENT_DOCUMENTS = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_STUDENT_DOCUMENTS $doc_dep_cond GROUP BY S_STUDENT_DOCUMENTS.PK_STUDENT_DOCUMENTS ORDER BY REQUESTED_DATE ASC ");
		
		while (!$res_type->EOF) { 
			$PK_STUDENT_DOCUMENTS 	= $res_type->fields['PK_STUDENT_DOCUMENTS']; 
			$DEPARTMENT_NAME		= '';
			$edit_access			= 0;
			$res_dep = $db->Execute("SELECT S_STUDENT_DOCUMENTS_DEPARTMENT.PK_DEPARTMENT, DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_DEPARTMENT AND PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS'  ORDER BY DEPARTMENT ASC "); 
			while (!$res_dep->EOF) { 
				if($DEPARTMENT_NAME != '')
					$DEPARTMENT_NAME .= ', ';
					
				if($res_dep->fields['PK_DEPARTMENT'] == $doc_dep) {
					if(($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3) && $t == 1)
						$edit_access = 1;
					else if(($REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3) && $t == 2)
						$edit_access = 1;
					else if(($FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3) && $t == 3)
						$edit_access = 1;
					else if(($ACCOUNTING_ACCESS == 2 || $ACCOUNTING_ACCESS == 3) && $t == 5)
						$edit_access = 1;
					else if(($PLACEMENT_ACCESS == 2 || $PLACEMENT_ACCESS == 3) && $t == 6)
						$edit_access = 1;
				}
				
				$DEPARTMENT_NAME .= $res_dep->fields['DEPARTMENT'];
				$res_dep->MoveNext();
			} ?>
			<tr>
				<td><?=$res_type->fields['CODE'].' - '.$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></td>
				<td><?=$DEPARTMENT_NAME?></td>
				<td><?=$res_type->fields['REQUESTED_DATE_1']?></td>
				<td><?=$res_type->fields['DOCUMENT_TYPE']?></td>
				<td><?=$res_type->fields['NAME']?></td>
				<td><?=$res_type->fields['FOLLOWUP_DATE']?></td>
				<td><?=$res_type->fields['RECEIVED']?></td>
				<td><?=$res_type->fields['DATE_RECEIVED']?></td>
				<td>
					<? if($res_type->fields['DOCUMENT_PATH'] != ''){ ?>
					<a href="<?=aws_url($res_type->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_type->fields['DOCUMENT_NAME']?></a>
					<? } ?>
				</td>
				<td>
					<? if($edit_access == 1 || $_SESSION['PK_ROLES'] == 2){ ?>
					<a href="student_document?sid=<?=$sid?>&eid=<?=$eid?>&id=<?=$res_type->fields['PK_STUDENT_DOCUMENTS']?>&t=<?=$t?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
					<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_DOCUMENTS']?>','document')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
					<? } ?>
				</td>
			</tr>
		<?	$res_type->MoveNext();
		} ?>
	</tbody>
</table>