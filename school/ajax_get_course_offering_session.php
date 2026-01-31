<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['PK_COURSE_OFFERING'];
$res_ses = $db->Execute("select SESSION, SESSION_NO FROM S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
echo substr($res_ses->fields['SESSION'],0,1).'-'.$res_ses->fields['SESSION_NO']; //Ticket # 1686