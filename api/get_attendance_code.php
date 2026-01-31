<?php require_once("../global/config.php"); 
$API_KEY = $_GET['key'];

$res_user = $db->Execute("SELECT PK_USER,PK_ACCOUNT,ID FROM Z_USER where USER_API_KEY = '$API_KEY' ");
$PK_ACCOUNT 		= $res_user->fields['PK_ACCOUNT'];
$PK_USER 			= $res_user->fields['PK_USER'];
$PK_EMPLOYEE_MASTER = $res_user->fields['ID'];

if($PK_USER == 0 || $PK_USER == '' || $PK_EMPLOYEE_MASTER == 0 || $PK_EMPLOYEE_MASTER == '') {
	$RET_DATA['STATUS']  = 0;
	$RET_DATA['MESSAGE'] = 'Something went Wrong';
} else {
	$RET_DATA['STATUS']  = 1;
	$RET_DATA['MESSAGE'] = '';
	
	$res_type = $db->Execute("select PK_ATTENDANCE_CODE,CONCAT(CODE,'; ',ATTENDANCE_CODE) AS ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE ACTIVE = 1 order by CODE ASC ");
	$i = 0;
	while (!$res_type->EOF) { 
								
		$RET_DATA['ATTENDANCE_CODE'][$i]['PK_ATTENDANCE_CODE'] 	= $res_type->fields['PK_ATTENDANCE_CODE'];
		$RET_DATA['ATTENDANCE_CODE'][$i]['TEXT'] 				= $res_type->fields['ATTENDANCE_CODE'];
		
		$i++;
		$res_type->MoveNext();
	} 
}

echo json_encode($RET_DATA);