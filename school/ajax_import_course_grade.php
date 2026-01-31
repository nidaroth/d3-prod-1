<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

////////////////////////////
////////////////////////////
////////////////////////////if grade book is edited check on instructor panel > GRADE BOOK SETUP
////////////////////////////
////////////////////////////

$grade_cunt  	= $_REQUEST['grade_cunt'];
$PK_COURSE  	= $_REQUEST['PK_COURSE'];
$PK_COURSE_F 	= $_REQUEST['id']; // DIAM-767
$result12 = $db->Execute("SELECT PK_COURSE_GRADE_BOOK,CODE FROM S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$PK_COURSE' ORDER BY COLUMN_NO ASC ");
while (!$result12->EOF) {
	$_REQUEST['PK_COURSE_GRADE_BOOK'] 		= $result12->fields['PK_COURSE_GRADE_BOOK'];
	$_REQUEST['PK_COURSE_OFFERING_GRADE'] 	= '';
	$_REQUEST['grade_cunt']  				= $grade_cunt;
	//Begin DIAM-767
	$CODE  				= $result12->fields['CODE'];
	$result = $db->Execute("SELECT * FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_F' AND CODE='$CODE'");
	if($result->RecordCount()==0)
	{

	include('ajax_course_offering_grade.php');
	
	}
	//End DIAM-767
	
	$grade_cunt++;	
	$result12->MoveNext();
}