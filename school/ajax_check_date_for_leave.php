<?php require_once("../global/config.php"); 

$temp_date 	= $_REQUEST['date'];
$error		= '';
if($temp_date != '') {
	$temp_date = date("Y-m-d",strtotime($temp_date));
	$res_type = $db->Execute("select LEAVE_TYPE from M_ACADEMIC_CALENDAR,M_ACADEMIC_CALENDAR_SESSION WHERE M_ACADEMIC_CALENDAR.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_ACADEMIC_CALENDAR.ACTIVE = 1 AND M_ACADEMIC_CALENDAR_SESSION.ACTIVE = 1 AND ACADEMY_DATE = '$temp_date' AND M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_SESSION.PK_ACADEMIC_CALENDAR ");
	
	if($res_type->RecordCount() > 0) {
		if($res_type->fields['LEAVE_TYPE'] == 1)
			$error	= 'This date fall under Holiday';
		else if($res_type->fields['LEAVE_TYPE'] == 2)
			$error	= 'This date fall under Break';
		else
			$error	= 'This date fall under Closure';
	}
}
echo $error;