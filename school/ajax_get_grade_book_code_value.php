<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$PK_GRADE_BOOK_CODE = $_REQUEST['val'];
$res_cs = $db->Execute("SELECT CODE, M_GRADE_BOOK_CODE.DESCRIPTION, HOUR, SESSIONS, POINTS, M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE, GRADE_BOOK_TYPE  FROM M_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE WHERE M_GRADE_BOOK_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' ");
echo $res_cs->fields['CODE'].'|||'.$res_cs->fields['DESCRIPTION'].'|||'.$res_cs->fields['HOUR'].'|||'.$res_cs->fields['SESSIONS'].'|||'.$res_cs->fields['POINTS'].'|||'.$res_cs->fields['PK_GRADE_BOOK_TYPE'].'|||'.$res_cs->fields['GRADE_BOOK_TYPE'];