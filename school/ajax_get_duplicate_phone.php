<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_ssn_report.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}
$PHONE 		= $_REQUEST['PHONE'];
$PHONE_TYPE = $_REQUEST['PHONE_TYPE'];

$cond = "";

if($PHONE_TYPE == 1) {
	$field 		= "CELL_PHONE";
	$field_name = CELL_PHONE;
} else if($PHONE_TYPE == 2) {
	$field 		= "HOME_PHONE";
	$field_name = HOME_PHONE;
} else if($PHONE_TYPE == 3) {
	$field 		= "OTHER_PHONE";
	$field_name = OTHER_PHONE;
}

if($PHONE != '') {
	$PHONE = preg_replace( '/[^0-9]/', '',$PHONE);
	$cond = " AND REPLACE(REPLACE(REPLACE(REPLACE($field, '(', ''), ')', ''), '-', ''),' ','') != '$PHONE' ";
} ?>
<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<th><?=$field_name ?></th>
			<th><?=LAST_NAME?></th>
			<th><?=FIRST_NAME?></th>
			<th><?=STUDENT_ID?></th>
			<th><?=STATUS?></th>
			<th><?=ARCHIVED?></th>
			<th><?=ACTION?></th>
		</tr>
	</thead>
	<tbody>
	
		<? $_SESSION['QUERY'] = "select $field,COUNT(PK_STUDENT_CONTACT) FROM S_STUDENT_CONTACT,S_STUDENT_MASTER WHERE $field != '' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CONTACT.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 $cond GROUP BY REPLACE(REPLACE(REPLACE(REPLACE($field, '(', ''), ')', ''), '-', ''),' ','') HAVING COUNT(PK_STUDENT_CONTACT) > 1 ORDER BY REPLACE(REPLACE(REPLACE(REPLACE($field, '(', ''), ')', ''), '-', ''),' ','') ASC";
		$res_phone = $db->Execute($_SESSION['QUERY']);
		if($res_phone->RecordCount() == 0) { ?>
			<tr>
				<td align="center" colspan="5" ><?=NO_RECORD_FOUND?></td>
			</tr>
		<? } else {
			while (!$res_phone->EOF) {
				$PHONE  = $res_phone->fields[$field];
				
				$PHONE1 = preg_replace( '/[^0-9]/', '',$res_phone->fields[$field]);
				$PHONE1 = '('.$PHONE1[0].$PHONE1[1].$PHONE1[2].') '.$PHONE1[3].$PHONE1[4].$PHONE1[5].'-'.$PHONE1[6].$PHONE1[7].$PHONE1[8].$PHONE1[9];

				$res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, FIRST_NAME,LAST_NAME, STUDENT_ID, If(ARCHIVED = 1,'Yes', 'No') as ARCHIVED FROM S_STUDENT_MASTER, S_STUDENT_CONTACT, S_STUDENT_ACADEMICS WHERE $field = '$PHONE' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 AND S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC "); 
				
				while (!$res_type->EOF) { 
					$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER']; 
					$res_enroll = $db->Execute("select PK_STUDENT_ENROLLMENT, STUDENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = M_STUDENT_STATUS.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");  ?>
					<tr>
						<td><?=$PHONE1?></td>
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
				$res_phone->MoveNext();
			}
		} ?>
	</tbody>
</table>