<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

if(check_access('ADMISSION_ACCESS') == 0 && check_access('REGISTRAR_ACCESS') == 0 && check_access('FINANCE_ACCESS') == 0 && check_access('ACCOUNTING_ACCESS') == 0 && check_access('PLACEMENT_ACCESS') == 0 ){ 
	header("location:../index");
	exit;
}

$cond = "";
if($_REQUEST['FIELD_NAME'] != '')
	$cond .= " AND FIELD_NAME = '$_REQUEST[FIELD_NAME]' ";
	
if($_REQUEST['CHANGED_BY'] != '')
	$cond .= " AND CHANGED_BY = '$_REQUEST[CHANGED_BY]' ";	
	
if($_REQUEST['FROM_DATE'] != '' && $_REQUEST['TO_DATE'] != '') {
	$FROM_DATE 	 = date("Y-m-d",strtotime($_REQUEST['FROM_DATE']));
	$TO_DATE 	 = date("Y-m-d",strtotime($_REQUEST['TO_DATE']));
	
	$cond 		.= " AND DATE_FORMAT(CHANGED_ON, '%Y-%m-%d') BETWEEN '$FROM_DATE' AND '$TO_DATE' ";
} else if($_REQUEST['FROM_DATE'] != '' ) {
	$FROM_DATE 	 = date("Y-m-d",strtotime($_REQUEST['FROM_DATE']));
	$cond 		.= " AND DATE_FORMAT(CHANGED_ON, '%Y-%m-%d') >= '$FROM_DATE' ";
} else if($_REQUEST['TO_DATE'] != '' ) {
	$TO_DATE 	 = date("Y-m-d",strtotime($_REQUEST['TO_DATE']));
	$cond 		.= " AND DATE_FORMAT(CHANGED_ON, '%Y-%m-%d') <= '$TO_DATE' ";
}
?>
<table class="table table-hover" >
	<thead>
		<tr>
			<th width="25%"><?=FIELD_NAME?></th>
			<th width="25%"><?=OLD_VALUE?></th>
			<th width="25%"><?=NEW_VALUE?></th>
			<th width="13%"><?=CHANGED_BY?></th>
			<th width="12%"><?=CHANGED_ON?></th>
		</tr>
	</thead>
	<tbody>
		<? $res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
			
		$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$TIMEZONE = $res_tz->fields['TIMEZONE'];
		
		$query = "SELECT FIELD_NAME,OLD_VALUE,NEW_VALUE,CHANGED_ON,CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) AS NAME FROM S_STUDENT_TRACK_CHANGES LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_TRACK_CHANGES.CHANGED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE PK_STUDENT_MASTER = '$_REQUEST[id]' and (PK_STUDENT_ENROLLMENT = '$_REQUEST[eid]' or GLOBAL_CHANGE = 1) $cond ORDER BY PK_STUDENT_TRACK_CHANGES DESC";
		$_SESSION['REPORT_QUERY'] = $query;
		$res_type = $db->Execute($query);
		while (!$res_type->EOF) { ?>
			<tr>
				<td><?=$res_type->fields['FIELD_NAME']?></td>
				<td>
					<? if($res_type->fields['FIELD_NAME'] == 'SSN') {
						$SSN = $res_type->fields['OLD_VALUE'];
						if($SSN != '') {
							$SSN = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
						}
						echo $SSN;
					} else
						echo $res_type->fields['OLD_VALUE']; ?>
				</td>
				<td>
					<? if($res_type->fields['FIELD_NAME'] == 'SSN') {
						$SSN = $res_type->fields['NEW_VALUE'];
						if($SSN != '') {
							$SSN = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
						}
						echo $SSN;
					} else
						echo $res_type->fields['NEW_VALUE']; ?>
				</td>
				<td><?=$res_type->fields['NAME']?></td>
				<td>
					<? if($res_type->fields['CHANGED_ON'] != '0000-00-00 00:00:00')
						echo convert_to_user_date($res_type->fields['CHANGED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get()); ?>
				</td>
			</tr>
		<?	$res_type->MoveNext();
		} ?>
	</tbody>
</table>