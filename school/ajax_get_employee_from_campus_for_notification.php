<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/notification_settings.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$PK_EVENT_TEMPLATE 	= $_REQUEST['PK_EVENT_TEMPLATE'];
$camp_cond 			= "";
if($_REQUEST['PK_CAMPUS'] != ''){
	$PK_CAMPUS = $_REQUEST['PK_CAMPUS'];
	$camp_cond = " AND S_EMPLOYEE_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
}
?>

<select name="PK_EMPLOYEE_MASTER[]" id="PK_EMPLOYEE_MASTER" class="" multiple>
	<? $res_type = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, EMPLOYEE_ID FROM S_EMPLOYEE_MASTER LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_EMPLOYEE_MASTER.ACTIVE = 1 $camp_cond GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER ORDER BY CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) ASC"); 
	while (!$res_type->EOF) {
		$selected = '';
		$PK_EMPLOYEE_MASTER = $res_type->fields['PK_EMPLOYEE_MASTER'];
		
		$dep = '';
		$res = $db->Execute("select DEPARTMENT FROM M_DEPARTMENT,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
		while (!$res->EOF) {
			if($dep != '')
				$dep .= ', ';
				
			$dep .= $res->fields['DEPARTMENT'];
			$res->MoveNext();
		}
		
		if($dep != '')
			$dep = ' ['.$dep.']';
		
		if($PK_EVENT_TEMPLATE > 0) {
			$res = $db->Execute("select PK_EVENT_TEMPLATE_RECIPIENTS FROM S_EVENT_TEMPLATE_RECIPIENTS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			if($res->RecordCount() > 0)
				$selected = 'selected';
		} ?>
		<option value="<?=$PK_EMPLOYEE_MASTER?>" <?=$selected?> ><?=$res_type->fields['NAME'].' '.$dep?></option>
	
	<?	$res_type->MoveNext();
	} ?>
</select>