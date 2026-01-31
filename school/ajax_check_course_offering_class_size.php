<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['PK_COURSE_OFFERING'];
$res_co  = $db->Execute("select CLASS_SIZE, ROOM_SIZE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' "); //Ticket # 1325
$res_stu = $db->Execute("select PK_STUDENT_COURSE FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

if($res_stu->RecordCount() >= $res_co->fields['CLASS_SIZE']){
	echo "b|||".$res_co->fields['CLASS_SIZE'].'|||'.$res_stu->RecordCount();
} else {
	echo "a|||".$res_co->fields['CLASS_SIZE'].'|||'.$res_stu->RecordCount(); //Ticket # 1325
}
/* Ticket # 1325 */
if($res_stu->RecordCount() >= $res_co->fields['ROOM_SIZE']){
	echo "|||b|||".$res_co->fields['ROOM_SIZE'].'|||'.$res_stu->RecordCount();
} else {
	echo "|||a|||".$res_co->fields['ROOM_SIZE'].'|||'.$res_stu->RecordCount();
}
/* Ticket # 1325 */