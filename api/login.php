<?php require_once("../global/config.php"); 

$DATA = (file_get_contents('php://input'));
//$DATA = '{"LOGIN_ID":"johnsmith1","PASSWORD":"Pass!234"}';

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

$USER_ID 	= trim($DATA->LOGIN_ID);
$PASSWORD 	= ($DATA->PASSWORD);

//////////////////////
$result = $db->Execute("SELECT PASSWORD FROM Z_USER where USER_ID = '$USER_ID'");
if($result->RecordCount() > 0) {
	$hash  	   = $result->fields['PASSWORD'];
	$PASSWORD  = crypt($PASSWORD, $hash);
	
	$result = $db->Execute("SELECT Z_USER.* ,PK_PANELS,Z_ACCOUNT.SCHOOL_NAME,EMPLOYEE_LABEL,Z_ACCOUNT.ACTIVE AS ACTIVE_1, Z_ACCOUNT.PK_TIMEZONE,LOGO FROM Z_USER,Z_ACCOUNT where Z_USER.USER_ID = '$USER_ID' AND PASSWORD = '$PASSWORD' AND Z_USER.PK_ACCOUNT = Z_ACCOUNT.PK_ACCOUNT ");
	
	if($result->RecordCount() == 0){
		$RET_DATA['STATUS']  = 0;
		$RET_DATA['MESSAGE'] = 'Invalid User ID/Password';
	} else {
		
		if($result->fields['ACTIVE_1'] == 0){
			$RET_DATA['STATUS']  = 0;
			$RET_DATA['MESSAGE'] = 'Your Account Has Been Blocked. Please Contact The Admin';;
		} else {
			$RET_DATA['STATUS']  = 1;
			$RET_DATA['MESSAGE'] = 'Success';
			$RET_DATA['API_KEY'] = $result->fields['USER_API_KEY'];
			
			$LOGO = '';
			if($result->fields['LOGO'] != '')
				$LOGO .= str_replace("../",$http_path,$result->fields['LOGO']);
				
			$RET_DATA['LOGO']  = $LOGO;
		}
	}
} else {
	$RET_DATA['STATUS']  = 0;
	$RET_DATA['MESSAGE'] = 'Invalid User ID/Password';
}
//////////////////////

echo json_encode($RET_DATA);

?>