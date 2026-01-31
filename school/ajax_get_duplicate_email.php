<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_ssn_report.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}
$EMAIL = $_REQUEST['EMAIL'];
$cond = "";
if($EMAIL != '') {
	$cond = " AND EMAIL != '$EMAIL' ";
} ?>
<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<th><?=EMAIL?></th>
			<th><?=LAST_NAME?></th>
			<th><?=FIRST_NAME?></th>
			<th><?=STUDENT_ID?></th>
			<th><?=STATUS?></th>
			<th><?=ARCHIVED?></th>
			<th><?=ACTION?></th>
		</tr>
	</thead>
	<tbody>
		<? $_SESSION['QUERY'] = "select EMAIL,COUNT(EMAIL) FROM S_STUDENT_CONTACT,S_STUDENT_MASTER WHERE EMAIL != '' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CONTACT.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 $cond GROUP BY EMAIL HAVING COUNT(EMAIL) > 1 ORDER BY EMAIL ASC";
		$res_email = $db->Execute($_SESSION['QUERY']);
		if($res_email->RecordCount() == 0) { ?>
			<tr>
				<td align="center" colspan="5" ><?=NO_RECORD_FOUND?></td>
			</tr>
		<? } else {
			while (!$res_email->EOF) {
				$EMAIL 	 = $res_email->fields['EMAIL'];
				
				//$res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, FIRST_NAME,LAST_NAME, STUDENT_ID ,PK_STUDENT_ENROLLMENT, STUDENT_STATUS  FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT, M_STUDENT_STATUS, S_STUDENT_CONTACT WHERE EMAIL = '$EMAIL' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 AND S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = M_STUDENT_STATUS.PK_STUDENT_STATUS ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC "); 
				
				$res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, FIRST_NAME,LAST_NAME, STUDENT_ID, If(ARCHIVED = 1,'Yes', 'No') as ARCHIVED FROM S_STUDENT_MASTER, S_STUDENT_CONTACT, S_STUDENT_ACADEMICS WHERE EMAIL = '$EMAIL' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 AND S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC "); 
				
				while (!$res_type->EOF) { 
					$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER']; 
					$res_enroll = $db->Execute("select PK_STUDENT_ENROLLMENT, STUDENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = M_STUDENT_STATUS.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");  ?>
					<tr>
						<td><?=$EMAIL?></td>
						<td><?=$res_type->fields['LAST_NAME']?></td>
						<td><?=$res_type->fields['FIRST_NAME']?></td>
						<td><?=$res_type->fields['STUDENT_ID']?></td>
						<td><?=$res_enroll->fields['STUDENT_STATUS']?></td>
						<td><?=$res_type->fields['ARCHIVED']?></td>
						<td>
							<a href="student?id=<?=$res_type->fields['PK_STUDENT_MASTER'] ?>&eid=<?=$res_enroll->fields['PK_STUDENT_ENROLLMENT']?>&t=1" title="<?=EDIT?>" target="_blank" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
						</td>
					</tr>
				<?	$res_type->MoveNext();
				} 
				$res_email->MoveNext();
			}
		} ?>
	</tbody>
</table>