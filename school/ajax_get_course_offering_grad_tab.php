<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
$id = $_REQUEST['id'];
$grade_cunt = 1; 
$result1 = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$id' ORDER BY GRADE_ORDER ASC "); //Ticket #1290
$reccnt = $result1->RecordCount();
while (!$result1->EOF) {
	$_REQUEST['PK_COURSE_OFFERING_GRADE'] 	= $result1->fields['PK_COURSE_OFFERING_GRADE'];
	$_REQUEST['grade_cunt']  				= $grade_cunt;
	
	include('ajax_course_offering_grade.php');
	
	$grade_cunt++;	
	$result1->MoveNext();
} 

