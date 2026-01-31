<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_MASTER = $_GET['id'];

$result = $db->Execute("SELECT Z_USER.* ,PK_PANELS,Z_ACCOUNT.SCHOOL_NAME,EMPLOYEE_LABEL,Z_ACCOUNT.ACTIVE AS ACTIVE_1, Z_ACCOUNT.PK_TIMEZONE, HAS_STUDENT_PORTAL, HAS_INSTRUCTOR_PORTAL FROM Z_USER,Z_ACCOUNT where Z_USER.PK_ACCOUNT = Z_ACCOUNT.PK_ACCOUNT AND PK_USER_TYPE = 3 AND ID = '$PK_STUDENT_MASTER' ");

$_SESSION['FOLDER'] 	 		= "student/";
$_SESSION['PK_USER_TYPE'] 		= $result->fields['PK_USER_TYPE'];
$_SESSION['SCHOOL_NAME'] 		= $result->fields['SCHOOL_NAME'];
$_SESSION['EMPLOYEE_LABEL'] 	= $result->fields['EMPLOYEE_LABEL'];
$_SESSION['PK_USER'] 	 		= $result->fields['PK_USER'];
$_SESSION['PK_ACCOUNT']  		= $result->fields['PK_ACCOUNT'];
$_SESSION['PK_ROLES'] 			= $result->fields['PK_ROLES'];
$_SESSION['PK_LANGUAGE'] 		= $result->fields['PK_LANGUAGE'];
$_SESSION['PK_STUDENT_MASTER'] 	= $result->fields['ID'];

if($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3)
	$_SESSION['PK_TIMEZONE'] = $result->fields['PK_TIMEZONE'];

$res_stu = $db->Execute("SELECT FIRST_NAME, LAST_NAME,IMAGE FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER='$_SESSION[PK_STUDENT_MASTER]' ");
$_SESSION['NAME'] 			= $res_stu->fields['FIRST_NAME'].' '.$res_stu->fields['LAST_NAME'];
$_SESSION['PROFILE_IMAGE']  = $res_stu->fields['IMAGE'];

$res_stu = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1 ");
$PK_STUDENT_ENROLLMENT = $res_stu->fields['PK_STUDENT_ENROLLMENT'];
///////////////
$res_camp = $db->Execute("SELECT S_CAMPUS.PK_CAMPUS,OFFICIAL_CAMPUS_NAME,PK_TIMEZONE FROM S_STUDENT_CAMPUS,S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CAMPUS.ACTIVE = 1 AND S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS ");
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

header("location:../".$_SESSION['FOLDER']."index");
exit;