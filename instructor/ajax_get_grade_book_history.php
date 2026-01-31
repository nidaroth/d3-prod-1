<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/instructor_grade_book_setup.php");
require_once("../language/course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['id']; 
$last_date = $_REQUEST['last_date']; 
$result1 = $db->Execute("SELECT PK_COURSE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
$PK_COURSE 	= $result1->fields['PK_COURSE']; 
?>
<? $grade_cunt = 1; 
$result1 = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE_HISTROY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND `CREATED_ON` = '$last_date' ORDER BY GRADE_ORDER ASC");

$reccnt = $result1->RecordCount();
while (!$result1->EOF) {
$_REQUEST['PK_COURSE_OFFERING_GRADE'] 	= $result1->fields['PK_COURSE_OFFERING_GRADE'];
$_REQUEST['grade_cunt']  				= $grade_cunt;

include('../school/ajax_course_offering_grade_history.php');

$grade_cunt++;	
$result1->MoveNext();
} 
?>

