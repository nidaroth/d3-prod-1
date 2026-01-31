<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_ssn_report.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}
$ssn = $_REQUEST['ssn'];
$cond = "";
if($ssn != '') {
	$SSN = my_encrypt($_SESSION['PK_ACCOUNT'].$PK_STUDENT_MASTER,$ssn);
	$cond .= " AND SSN != '$SSN' ";
}
/* Ticket #1432  */
if($_REQUEST['search_ssn'] != ''){
	$SSN   = my_encrypt($_SESSION['PK_ACCOUNT'].$PK_STUDENT_MASTER,$_REQUEST['search_ssn']);
	$cond .= " AND SSN = '$SSN' ";
}
/* Ticket #1432  */
?>

<table class="table table-hover" >
	<thead>
		<tr>
			<th><?=SSN?></th>
			<th><?=LAST_NAME?></th>
			<th><?=FIRST_NAME?></th>
			<th><?=STUDENT_ID?></th>
			<th><?=STATUS?></th>
			<th><?=ARCHIVED?></th>
			<th><?=ACTION?></th>
		</tr>
	</thead>
	<tbody>
		<? $query = "select SSN,COUNT(SSN) FROM S_STUDENT_MASTER WHERE SSN != '' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY SSN HAVING COUNT(SSN) > 1 ORDER BY SSN ASC ";
		$_SESSION['QUERY'] = $query;
		$res_ssn = $db->Execute($query);
		if($res_ssn->RecordCount() == 0) { ?>
			<tr>
				<td align="center" colspan="5" ><?=NO_RECORD_FOUND?></td>
			</tr>
		<? } else {
			while (!$res_ssn->EOF) {
				$SSN 	 = $res_ssn->fields['SSN'];
				$SSN_DE  = my_decrypt('',$SSN);
				
				$res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER,FIRST_NAME,LAST_NAME, STUDENT_ID, STUDENT_STATUS , S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, If(ARCHIVED = 1,'Yes', 'No') as ARCHIVED FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE SSN = '$SSN' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC "); 
				while (!$res_type->EOF) { ?>
					<tr>
						<td><?=$SSN_DE?></td>
						<td><?=$res_type->fields['LAST_NAME']?></td>
						<td><?=$res_type->fields['FIRST_NAME']?></td>
						<td><?=$res_type->fields['STUDENT_ID']?></td>
						<td><?=$res_type->fields['STUDENT_STATUS']?></td>
						<td><?=$res_type->fields['ARCHIVED']?></td>
						
						<td>
							<a href="student?id=<?=$res_type->fields['PK_STUDENT_MASTER'] ?>&eid=<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>&t=1" title="<?=EDIT?>" target="_blank" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
						</td>
					</tr>
				<?	$res_type->MoveNext();
				} 
				$res_ssn->MoveNext();
			}
		} ?>
	</tbody>
</table>