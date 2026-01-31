<?php require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));
//$DATA = '{"USER_ID":"ramesh@topcone.com","PASSWORD":"Pass!234"}';

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

$USER_ID 	= trim($DATA->USER_ID);
$PASSWORD 	= ($DATA->PASSWORD);

$result    = $db->Execute("SELECT PASSWORD FROM Z_USER where USER_ID = '$USER_ID'");
$hash  	   = $result->fields['PASSWORD'];
$PASSWORD  = crypt($PASSWORD, $hash);
$result = $db->Execute("SELECT Z_USER.* ,PK_PANELS,Z_ACCOUNT.SCHOOL_NAME,EMPLOYEE_LABEL,Z_ACCOUNT.ACTIVE AS ACTIVE_1, Z_ACCOUNT.PK_TIMEZONE, HAS_STUDENT_PORTAL, HAS_INSTRUCTOR_PORTAL FROM Z_USER,Z_ACCOUNT where Z_USER.USER_ID = '$USER_ID' AND PASSWORD = '$PASSWORD' AND Z_USER.PK_ACCOUNT = Z_ACCOUNT.PK_ACCOUNT ");

$IS_FACULTY = 0;
if($result->fields['PK_USER_TYPE'] == 1 || $result->fields['PK_USER_TYPE'] == 2) {
	$ID = $result->fields['ID'];
	$res_emp = $db->Execute("SELECT IS_FACULTY FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$ID' ");
	
	if($res_emp->fields['IS_FACULTY'] == 1)
		$IS_FACULTY = 1;
}

$data['URL'] = '';
if($result->RecordCount() == 0){
	$data['SUCCESS'] = 0;
	$data['MESSAGE'] = 'Invalid User ID/Password';
} else if($result->fields['PK_USER_TYPE'] == 2 && $result->fields['HAS_INSTRUCTOR_PORTAL'] == 0 && $IS_FACULTY == 1) {
	$data['SUCCESS'] = 0;
	$data['MESSAGE'] = 'You Dont Have Access To This Portal';
} else if($result->fields['PK_USER_TYPE'] == 3 && $result->fields['HAS_STUDENT_PORTAL'] == 0) {
	$data['SUCCESS'] = 0;
	$data['MESSAGE'] = 'You Dont Have Access To This Portal';
} else {
	if($result->fields['ACTIVE'] == 0 || $result->fields['ACTIVE_1'] == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Your Account Has Been Blocked. Please Contact The Admin';
	} else {
		if($result->fields['PK_USER_TYPE'] == 1) {
			$folder = "super_admin/";
			
			$SESSION_DATA['ADMIN_PK_USER'] 	 	= $result->fields['PK_USER'];
			$SESSION_DATA['ADMIN_PK_ACCOUNT']  	= $result->fields['PK_ACCOUNT'];
			$SESSION_DATA['ADMIN_PK_ROLES'] 	= $result->fields['PK_ROLES'];
			$SESSION_DATA['ADMIN_PK_TIMEZONE'] 	= $result->fields['PK_TIMEZONE'];
		} else if($result->fields['PK_USER_TYPE'] == 2)
			$folder = "school/";
		else if($result->fields['PK_USER_TYPE'] == 3)
			$folder = "student/";

		$SESSION_DATA['FOLDER'] 	 	= $folder;
		$SESSION_DATA['PK_USER_TYPE'] 	= $result->fields['PK_USER_TYPE'];
		$SESSION_DATA['SCHOOL_NAME'] 	= $result->fields['SCHOOL_NAME'];
		$SESSION_DATA['EMPLOYEE_LABEL'] = $result->fields['EMPLOYEE_LABEL'];
		$SESSION_DATA['PK_USER'] 	 	= $result->fields['PK_USER'];
		$SESSION_DATA['PK_ACCOUNT']  	= $result->fields['PK_ACCOUNT'];
		$SESSION_DATA['PK_ROLES'] 		= $result->fields['PK_ROLES'];
		$SESSION_DATA['PK_LANGUAGE'] 	= $result->fields['PK_LANGUAGE'];
		
		if($SESSION_DATA['PK_ROLES'] == 1 || $SESSION_DATA['PK_ROLES'] == 2 || $SESSION_DATA['PK_ROLES'] == 3)
			$SESSION_DATA['PK_TIMEZONE'] = $result->fields['PK_TIMEZONE'];
		
		$LOGIN_HISTORY['PK_ROLES']   = $SESSION_DATA['PK_ROLES'];
		$LOGIN_HISTORY['PK_USER'] 	 = $SESSION_DATA['PK_USER'];
		$LOGIN_HISTORY['IP_ADDRESS'] = get_ip_address();
		$LOGIN_HISTORY['LOGIN_TIME'] = date("Y-m-d H:i:s");
		db_perform('Z_LOGIN_HISTORY', $LOGIN_HISTORY, 'insert');
		$PK_LOGIN_HISTORY = $db->insert_ID();
		$SESSION_DATA['PK_LOGIN_HISTORY'] = $PK_LOGIN_HISTORY;
		
		$multi = 0;
		if($result->fields['PK_USER_TYPE'] == 1 || $result->fields['PK_USER_TYPE'] == 2) {
			$SESSION_DATA['PK_EMPLOYEE_MASTER'] = $result->fields['ID'];
			$res_emp = $db->Execute("SELECT FIRST_NAME, LAST_NAME,IMAGE,TURN_OFF_ASSIGNMENTS,IS_FACULTY FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$SESSION_DATA[PK_EMPLOYEE_MASTER]' ");
			$SESSION_DATA['NAME'] 					= $res_emp->fields['FIRST_NAME'].' '.$res_emp->fields['LAST_NAME'];
			$SESSION_DATA['PROFILE_IMAGE']  		= $res_emp->fields['IMAGE'];
			$SESSION_DATA['TURN_OFF_ASSIGNMENTS']  	= $res_emp->fields['TURN_OFF_ASSIGNMENTS'];
			
			if($res_emp->fields['IS_FACULTY'] == 1)
				$SESSION_DATA['FOLDER'] = 'instructor/';
		} else if($result->fields['PK_USER_TYPE'] == 3){
			$SESSION_DATA['PK_STUDENT_MASTER'] = $result->fields['ID'];
			
			$res_stu = $db->Execute("SELECT FIRST_NAME, LAST_NAME,IMAGE FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER='$SESSION_DATA[PK_STUDENT_MASTER]' ");
			$SESSION_DATA['NAME'] 			= $res_emp->fields['FIRST_NAME'].' '.$res_emp->fields['LAST_NAME'];
			$SESSION_DATA['PROFILE_IMAGE']  = $res_emp->fields['IMAGE'];
		}
		
		if($result->fields['PK_USER_TYPE'] != 3){
		
			$res_dep = $db->Execute("SELECT M_DEPARTMENT.PK_DEPARTMENT,PK_DEPARTMENT_MASTER FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE PK_EMPLOYEE_MASTER = '$SESSION_DATA[PK_EMPLOYEE_MASTER]' AND S_EMPLOYEE_DEPARTMENT.ACTIVE = 1 AND M_DEPARTMENT.ACTIVE = 1 AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT ");
			if($res_dep->RecordCount() <= 1) {
				$SESSION_DATA['PK_DEPARTMENT'] 			= $res_dep->fields['PK_DEPARTMENT'];
				$SESSION_DATA['PK_DEPARTMENT_MASTER'] 	= $res_dep->fields['PK_DEPARTMENT_MASTER'];
			} else {
				$PK_DEPARTMENT 		  = '';
				$PK_DEPARTMENT_MASTER = '';
				while (!$res_dep->EOF) {
					if($PK_DEPARTMENT != '')
						$PK_DEPARTMENT .= ',';
						
					$PK_DEPARTMENT .= $res_dep->fields['PK_DEPARTMENT'];
					
					if($PK_DEPARTMENT_MASTER != '')
						$PK_DEPARTMENT_MASTER .= ',';
						
					$PK_DEPARTMENT_MASTER .= $res_dep->fields['PK_DEPARTMENT_MASTER'];
					
					$res_dep->MoveNext();
				}
				
				$SESSION_DATA['PK_DEPARTMENT'] 			= $PK_DEPARTMENT;
				$SESSION_DATA['PK_DEPARTMENT_MASTER'] 	= $PK_DEPARTMENT_MASTER;
			}
		
			if($SESSION_DATA['PK_ROLES'] == 3 || $SESSION_DATA['PK_ROLES'] == 4 || $SESSION_DATA['PK_ROLES'] == 5) {
				$res_camp = $db->Execute("SELECT S_CAMPUS.PK_CAMPUS,OFFICIAL_CAMPUS_NAME,PK_TIMEZONE FROM S_EMPLOYEE_CAMPUS,S_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$SESSION_DATA[PK_EMPLOYEE_MASTER]' AND S_EMPLOYEE_CAMPUS.ACTIVE = 1 AND S_EMPLOYEE_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS ");
				if($res_camp->RecordCount() <= 1) {
					$SESSION_DATA['PK_CAMPUS'] 		= $res_camp->fields['PK_CAMPUS'];
					$SESSION_DATA['CAMPUS_NAME'] 	= $res_camp->fields['OFFICIAL_CAMPUS_NAME'];
					$SESSION_DATA['PK_TIMEZONE'] 	= $res_camp->fields['PK_TIMEZONE'];
					$SESSION_DATA['MULTI_CAMPUS'] 	= 0;
				} else {
					$multi = 1;
					
					$CAMPUS_NAME 	= '';
					$PK_CAMPUS 		= '';
					while (!$res_camp->EOF) {
						if($CAMPUS_NAME != '')
							$CAMPUS_NAME .= ', ';
							
						$CAMPUS_NAME .= $res_camp->fields['OFFICIAL_CAMPUS_NAME'];
						
						if($PK_CAMPUS != '')
							$PK_CAMPUS .= ',';
							
						$PK_CAMPUS .= $res_camp->fields['PK_CAMPUS'];
						
						$SESSION_DATA['PK_TIMEZONE'] = $res_camp->fields['PK_TIMEZONE'];
						
						$res_camp->MoveNext();
					}
					
					$SESSION_DATA['CAMPUS_NAME'] 	= $CAMPUS_NAME;
					$SESSION_DATA['PK_CAMPUS'] 	 	= $PK_CAMPUS;
					$SESSION_DATA['MULTI_CAMPUS'] 	= 1;
				}
			}
		}
		
		$str = '';
		foreach($SESSION_DATA as $KEY => $VALUE) {
			if($str != '')
				$str .= "^^^^";
			
			$str .= $KEY.'||||'.$VALUE;
		}			
		
		do {
			$CODE = generateRandomString(40);
			$result = $db->Execute("SELECT CODE FROM Z_LOGIN_ACCESS where CODE = '$CODE'");
		} while ($result->RecordCount() > 0);
	
		$LOGIN_ACCESS['CODE']  		= $CODE;
		$LOGIN_ACCESS['DATA']  		= $str;
		$LOGIN_ACCESS['CREATED_ON'] = date("Y-m-d H:i");
		db_perform('Z_LOGIN_ACCESS', $LOGIN_ACCESS, 'insert');
		$PK_LOGIN_ACCESS = $db->insert_ID(); 
		
		$data['SUCCESS'] = 1;
		$data['MESSAGE'] = 'Success';
		$data['URL'] 	 = $http_path.'login-access?code='.$CODE;
	}
}
		
echo json_encode($data);
?>