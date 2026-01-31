<? 
//$path = '../';
$path = '/var/www/html/D3/';
require_once($path."global/config.php");

$date = date("Y-m-d");
$res_user = $db->Execute("select PK_ACCOUNT,PK_USER,USER_ID,ID,PK_USER_TYPE from Z_USER WHERE ACTIVE = 1 AND PK_ACCOUNT != 1 ");
while (!$res_user->EOF){
	$PK_USER = $res_user->fields['PK_USER'];
	$ID 	 = $res_user->fields['ID'];
	
	$res_active_user = $db->Execute("select PK_ACTIVE_USERS from Z_ACTIVE_USERS WHERE PK_USER = '$PK_USER' AND DATE = '$date' ");
	if($res_active_user->RecordCount() == 0) {
		
		$USER_TYPE = '';
		if($res_user->fields['PK_USER_TYPE'] == 3) {
			$res_stud = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$ID' ");
			$USER_TYPE  = 3;
			$FIRST_NAME = $res_stud->fields['FIRST_NAME'];
			$LAST_NAME  = $res_stud->fields['LAST_NAME'];
		} else if($res_user->fields['PK_USER_TYPE'] == 2){
			$res_emp = $db->Execute("SELECT NEED_SCHOOL_ACCESS,FIRST_NAME,LAST_NAME,IS_FACULTY FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$ID' ");
			if($res_emp->fields['IS_FACULTY'] == 1) {
				if($res_emp->fields['NEED_SCHOOL_ACCESS'] == 1)
					$USER_TYPE = 1;
				else
					$USER_TYPE = 2;
			} else {
				$USER_TYPE = 1;
			}
			
			$FIRST_NAME = $res_emp->fields['FIRST_NAME'];
			$LAST_NAME  = $res_emp->fields['LAST_NAME'];
		}

		$ACTIVE_USERS['PK_ACCOUNT']  	= $res_user->fields['PK_ACCOUNT'];
		$ACTIVE_USERS['FIRST_NAME']  	= $FIRST_NAME;
		$ACTIVE_USERS['LAST_NAME']  	= $LAST_NAME;
		$ACTIVE_USERS['LOGIN_ID']  		= $res_user->fields['USER_ID'];
		$ACTIVE_USERS['USER_TYPE']  	= $USER_TYPE;
		$ACTIVE_USERS['ID']  			= $ID;
		
		$ACTIVE_USERS['PK_USER']  		= $PK_USER;
		$ACTIVE_USERS['DATE']  			= $date;

		db_perform('Z_ACTIVE_USERS', $ACTIVE_USERS, 'insert');
	}
	
	$res_user->MoveNext();
}
