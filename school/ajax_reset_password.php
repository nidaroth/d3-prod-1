<? require_once("../global/config.php");
require_once("../language/common.php");

if($_REQUEST['type'] == 's'){
	$ID				= $_REQUEST['id'];
	$PK_USER_TYPE 	= 3;
} else {
	$PK_USER_TYPE = 2;
}

$result = $db->Execute("SELECT PK_USER,PK_ROLES,ACTIVE FROM Z_USER WHERE ID = '$ID' AND PK_USER_TYPE = '$PK_USER_TYPE' "); 
if($result->RecordCount() == 0){
	$msg = LOGIN_NOT_FOUND;
} else {
	$PK_USER  	  = $result->fields['PK_USER'];
	$PK_ROLES 	  = $result->fields['PK_ROLES'];
	
	if($result->fields['ACTIVE'] == 0)
		$msg = ACCOUNT_BLOCKED;
	else {
		if($PK_USER_TYPE == 2) {
			$res_usr_email = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME, EMAIL FROM S_EMPLOYEE_MASTER , Z_USER WHERE Z_USER.PK_USER = '$PK_USER' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
		} else if($PK_USER_TYPE == 3) {
			$res_usr_email = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME, EMAIL FROM S_STUDENT_MASTER, S_STUDENT_CONTACT , Z_USER WHERE Z_USER.PK_USER = '$PK_USER' AND Z_USER.ID = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");
		}
		
		if($res_usr_email->fields['EMAIL'] != '') {
			do {
				$CODE = generateRandomString(30);
				$result = $db->Execute("SELECT CODE FROM Z_RESET_PASSWORD where CODE = '$CODE'");
			} while ($result->RecordCount() > 0);
			//invalidate the old link
			$res = $db->Execute("SELECT * FROM Z_RESET_PASSWORD where PK_USER = '$PK_USER' AND ACTIVE='1'");
			if($res->RecordCount() > 0){
				$db->Execute("UPDATE Z_RESET_PASSWORD SET ACTIVE = '0' where PK_USER = '$PK_USER'");
			}
			$RESET_PASSWORD['PK_USER'] 	= $PK_USER;
			$RESET_PASSWORD['CODE'] 	= $CODE;
			db_perform('Z_RESET_PASSWORD', $RESET_PASSWORD, 'insert');
			
			require_once("../global/mail.php");
			forgot_password_mail($db,$http_path,$PK_USER,$CODE,$res_usr_email->fields['NAME'],$res_usr_email->fields['EMAIL']);
			
			$msg = RESET_EMAIL_SENT_TO.' '.$res_usr_email->fields['EMAIL'];
		} else {
			$msg = EMAIL_ID_NOT_FOUND;
		}
	}
}
echo $msg;