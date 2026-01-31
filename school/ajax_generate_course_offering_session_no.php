<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){  
	header("location:../index");
	exit;
}

$PK_COURSE 		= $_REQUEST['PK_COURSE'];
$PK_TERM_MASTER = $_REQUEST['PK_TERM_MASTER'];
$PK_CAMPUS 		= $_REQUEST['PK_CAMPUS'];
$PK_SESSION 	= $_REQUEST['PK_SESSION'];

$res = $db->Execute("SELECT MAX(SESSION_NO) AS SESSION_NO FROM S_COURSE_OFFERING WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$PK_TERM_MASTER' /*AND PK_CAMPUS = '$PK_CAMPUS'*/ AND PK_SESSION = '$PK_SESSION' "); 
$SESSION_NO = $res->fields['SESSION_NO'];
if($SESSION_NO == '' || $SESSION_NO == 0)
	$SESSION_NO = 1;
else
	$SESSION_NO += 1;
echo $SESSION_NO;