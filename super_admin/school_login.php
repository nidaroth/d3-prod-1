<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$PK_ACCOUNT = $_GET['id'];

$result = $db->Execute("SELECT Z_ACCOUNT.SCHOOL_NAME,Z_ACCOUNT.PK_TIMEZONE,EMPLOYEE_LABEL FROM Z_ACCOUNT where PK_ACCOUNT = '$PK_ACCOUNT' ");


$_SESSION['PK_USER_TYPE'] 	= 2;
$_SESSION['SCHOOL_NAME'] 	= $result->fields['SCHOOL_NAME'];
$_SESSION['EMPLOYEE_LABEL'] = $result->fields['EMPLOYEE_LABEL'];
$_SESSION['PK_TIMEZONE'] 	= $result->fields['PK_TIMEZONE'];
$_SESSION['PK_USER'] 	 	= $_SESSION['ADMIN_PK_USER'];
$_SESSION['PK_ACCOUNT']  	= $PK_ACCOUNT;
$_SESSION['PK_ROLES'] 		= 2;
$_SESSION['PK_LANGUAGE'] 	= 1;

header("location:../school/index");