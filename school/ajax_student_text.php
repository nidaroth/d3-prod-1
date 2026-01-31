<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/notes.php");

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

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}
$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$sid 	= $_REQUEST['sid'];

/* Ticket #1091  */
/*
if($_SESSION['PK_ROLES'] == 3)
	$text_cond = " AND (S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' OR IS_RECEIVED_MSG = 1) ";
else
	$text_cond = "";
*/
/* Ticket #1091  */
	
if($_REQUEST['dep_id'] != '')
	$text_cond .= " AND S_TEXT_LOG.PK_DEPARTMENT = '$_REQUEST[dep_id]' ";
if($_REQUEST['emp_id'] != '')
	$text_cond .= " AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = '$_REQUEST[emp_id]' ";

$ST = $_REQUEST['ST'];
$ET = $_REQUEST['ET'];

if($_REQUEST['ST'] != '')
	$ST = date("Y-m-d",strtotime($_REQUEST['ST']));
	
if($_REQUEST['ET'] != '')
	$ET = date("Y-m-d",strtotime($_REQUEST['ET']));
	
if($ST != '' && $ET != '') {
	$text_cond .= " AND DATE_FORMAT(SENT_ON,'%Y-%m-%d') BETWEEN '$ST' AND '$ET' ";
} else if($ST != '') {
	$text_cond .= " AND DATE_FORMAT(SENT_ON,'%Y-%m-%d') >= '$ST' ";
} else if($ET != '') {
	$text_cond .= " AND DATE_FORMAT(SENT_ON,'%Y-%m-%d') <= '$ET' ";
}
//echo $text_cond.'--';
?>
<table class="table table-hover">
	<thead>
		<tr>
			<th style="width:28%" ><?=TEXT?></th>
			<th style="width:10%" ><?=CELL_NO?></th>
			<th style="width:20%" ><?=EMPLOYEE?></th>
			<th style="width:15%" ><?=DEPARTMENT?></th>
			<th style="width:10%" ><?=STATUS?></th>
			<th style="width:17%" ><?=DATE_TIME." / ".TIME?></th>
		</tr>
	</thead>
	<tbody>
		<? $res_1 = $db->Execute("select PK_TEXT_LOG, TEXT_CONTENT, TO_PHONE, SENT_ON, CONCAT(LAST_NAME,', ', FIRST_NAME) AS EMP_NAME, DEPARTMENT, IF(IS_RECEIVED_MSG = 1,'Received','Sent') as STATUS, S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER FROM S_TEXT_LOG LEFT JOIN Z_USER ON S_TEXT_LOG.CREATED_BY = Z_USER.PK_USER LEFT JOIN S_EMPLOYEE_MASTER ON  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_TEXT_LOG.PK_DEPARTMENT WHERE PK_STUDENT_MASTER = '$sid' AND S_TEXT_LOG.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $text_cond ORDER BY SENT_ON DESC");
		while (!$res_1->EOF) { 
			$TO_PHONE = preg_replace( '/[^0-9]/', '', $res_1->fields['TO_PHONE']); 
			$TO_PHONE = str_replace("+1","",$TO_PHONE); 
			$TO_PHONE = '('.$TO_PHONE[0].$TO_PHONE[1].$TO_PHONE[2].') '.$TO_PHONE[3].$TO_PHONE[4].$TO_PHONE[5].'-'.$TO_PHONE[6].$TO_PHONE[7].$TO_PHONE[8].$TO_PHONE[9];
			?>
			<tr>
				<td><?=$res_1->fields['TEXT_CONTENT']?></td>
				<td><?=$TO_PHONE?></td>
				<td><?=$res_1->fields['EMP_NAME'] ?></td>
				
				<td><?=$res_1->fields['DEPARTMENT']?></td>
				<td><?=$res_1->fields['STATUS']?></td>
				
				<td>
					<? if($res_1->fields['SENT_ON'] != '0000-00-00 00:00:00')
						echo convert_to_user_date($res_1->fields['SENT_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());  ?>
				</td>
			</tr>
		<?	$res_1->MoveNext();
		} ?>
	</tbody>
</table>