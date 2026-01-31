<? require_once("../global/config.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$db->Execute("UPDATE S_STUDENT_MASTER SET ENABLE_AUTO_PAYMENT = '$_REQUEST[va1]' WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");
if($_REQUEST['va1'] == 1){
	$NOTES = 'Student has enabled Recurring Payments via the Student Portal';
} else {
	$NOTES = 'Student has disabled Recurring Payments via the Student Portal';
}

$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 1 ");
$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1 ");

$STUDENT_NOTES['IS_EVENT'] 				= 0;
$STUDENT_NOTES['NOTE_DATE'] 			= date("Y-m-d",strtotime($_REQUEST['NOTE_DATE']));
$STUDENT_NOTES['NOTE_TIME'] 			= date("H:i:s",strtotime($_REQUEST['NOTE_TIME']));
$STUDENT_NOTES['PK_DEPARTMENT'] 		= $res->fields['PK_DEPARTMENT'];
$STUDENT_NOTES['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $_SESSION['PK_STUDENT_MASTER'];
$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $res_en->fields['PK_STUDENT_ENROLLMENT'];
$STUDENT_NOTES['NOTES'] 				= $NOTES;
$STUDENT_NOTES['CREATED_BY']  			= '';
$STUDENT_NOTES['CREATED_ON']  			= date("Y-m-d H:i");
db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');