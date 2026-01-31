<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cunt_grade_book = 1; 
$result1 = $db->Execute("SELECT * FROM S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$_REQUEST[cid]' ");
$reccnt = $result1->RecordCount();
while (!$result1->EOF) {
	$_REQUEST['PK_COURSE_GRADE_BOOK'] 	= $result1->fields['PK_COURSE_GRADE_BOOK'];
	$_REQUEST['cunt_grade_book']  		= $cunt_grade_book;
	$_REQUEST['copy']  					= 1;
	
	include('ajax_grade_book.php');
	
	$cunt_grade_book++;	
	$result1->MoveNext();
}