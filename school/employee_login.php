<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_MASTER = $_GET['id'];

$result = $db->Execute("SELECT Z_USER.* ,PK_PANELS,Z_ACCOUNT.SCHOOL_NAME,EMPLOYEE_LABEL,Z_ACCOUNT.ACTIVE AS ACTIVE_1, Z_ACCOUNT.PK_TIMEZONE, HAS_STUDENT_PORTAL, HAS_INSTRUCTOR_PORTAL FROM Z_USER,Z_ACCOUNT where Z_USER.PK_ACCOUNT = Z_ACCOUNT.PK_ACCOUNT AND PK_USER_TYPE = 2 AND ID = '$PK_STUDENT_MASTER' ");

$IS_FACULTY 		= 0;
$NEED_SCHOOL_ACCESS = 0;
if($result->fields['PK_USER_TYPE'] == 1 || $result->fields['PK_USER_TYPE'] == 2) {
	$ID = $result->fields['ID'];
	$res_emp = $db->Execute("SELECT IS_FACULTY,NEED_SCHOOL_ACCESS FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$ID' ");
	
	if($res_emp->fields['IS_FACULTY'] == 1) {
		$IS_FACULTY 		= 1;
		$NEED_SCHOOL_ACCESS = $res_emp->fields['NEED_SCHOOL_ACCESS'];
	}
}

$folder = "school/";

$_SESSION['FOLDER'] 	 	= $folder;
$_SESSION['PK_USER_TYPE'] 	= $result->fields['PK_USER_TYPE'];
$_SESSION['SCHOOL_NAME'] 	= $result->fields['SCHOOL_NAME'];
$_SESSION['EMPLOYEE_LABEL'] = $result->fields['EMPLOYEE_LABEL'];
$_SESSION['PK_USER'] 	 	= $result->fields['PK_USER'];
$_SESSION['PK_ACCOUNT']  	= $result->fields['PK_ACCOUNT'];
$_SESSION['PK_ROLES'] 		= $result->fields['PK_ROLES'];
$_SESSION['PK_LANGUAGE'] 	= $result->fields['PK_LANGUAGE'];

$HAS_INSTRUCTOR_PORTAL = $result->fields['HAS_INSTRUCTOR_PORTAL'];

if($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3)
	$_SESSION['PK_TIMEZONE'] = $result->fields['PK_TIMEZONE'];
	
$_SESSION['PK_EMPLOYEE_MASTER'] = $result->fields['ID'];
$res_emp = $db->Execute("SELECT FIRST_NAME, LAST_NAME,IMAGE,TURN_OFF_ASSIGNMENTS,IS_FACULTY FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
$_SESSION['NAME'] 					= $res_emp->fields['FIRST_NAME'].' '.$res_emp->fields['LAST_NAME'];
$_SESSION['PROFILE_IMAGE']  		= $res_emp->fields['IMAGE'];
$_SESSION['TURN_OFF_ASSIGNMENTS']  	= $res_emp->fields['TURN_OFF_ASSIGNMENTS'];

$res_dep = $db->Execute("SELECT M_DEPARTMENT.PK_DEPARTMENT,PK_DEPARTMENT_MASTER FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND S_EMPLOYEE_DEPARTMENT.ACTIVE = 1 AND M_DEPARTMENT.ACTIVE = 1 AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT ");
if($res_dep->RecordCount() <= 1) {
	$_SESSION['PK_DEPARTMENT'] 			= $res_dep->fields['PK_DEPARTMENT'];
	$_SESSION['PK_DEPARTMENT_MASTER'] 	= $res_dep->fields['PK_DEPARTMENT_MASTER'];
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
	
	$_SESSION['PK_DEPARTMENT'] 			= $PK_DEPARTMENT;
	$_SESSION['PK_DEPARTMENT_MASTER'] 	= $PK_DEPARTMENT_MASTER;
}

if($_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3 || $_SESSION['PK_ROLES'] == 4 || $_SESSION['PK_ROLES'] == 5) {
	$res_camp = $db->Execute("SELECT S_CAMPUS.PK_CAMPUS,OFFICIAL_CAMPUS_NAME,PK_TIMEZONE FROM S_EMPLOYEE_CAMPUS,S_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND S_EMPLOYEE_CAMPUS.ACTIVE = 1 AND S_EMPLOYEE_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS ");
	if($res_camp->RecordCount() <= 1) {
		$_SESSION['PK_CAMPUS'] 		= $res_camp->fields['PK_CAMPUS'];
		$_SESSION['CAMPUS_NAME'] 	= $res_camp->fields['OFFICIAL_CAMPUS_NAME'];
		$_SESSION['PK_TIMEZONE'] 	= $res_camp->fields['PK_TIMEZONE'];
		$_SESSION['MULTI_CAMPUS'] 	= 0;
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
			
			$_SESSION['PK_TIMEZONE'] = $res_camp->fields['PK_TIMEZONE'];
			
			$res_camp->MoveNext();
		}
		
		$_SESSION['CAMPUS_NAME'] 	= $CAMPUS_NAME;
		$_SESSION['PK_CAMPUS'] 	 	= $PK_CAMPUS;
		$_SESSION['MULTI_CAMPUS'] 	= 1;
	}
}

if($IS_FACULTY == 1 && $NEED_SCHOOL_ACCESS == 1) {
	if($HAS_INSTRUCTOR_PORTAL == 0) {
		header("location:../".$_SESSION['FOLDER']."index");
		exit;
	} else {
		header("location:../select-site");
		exit;
	}
} else if($res_emp->fields['IS_FACULTY'] == 1){
	$_SESSION['FOLDER'] = 'instructor/';
}

header("location:../".$_SESSION['FOLDER']."index");
exit;
