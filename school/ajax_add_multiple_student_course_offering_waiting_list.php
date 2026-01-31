<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$eids 					= explode(",",$_REQUEST['eids']);
$PK_COURSE_OFFERING 	= $_REQUEST['PK_COURSE_OFFERING'];

foreach($eids as $PK_STUDENT_ENROLLMENT) {
	$res1 = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
	
	$_REQUEST['id']  = $res1->fields['PK_STUDENT_MASTER'];
	$_REQUEST['eid'] = $PK_STUDENT_ENROLLMENT;
	
	include("ajax_add_student_course_offering_waiting_list.php");
}