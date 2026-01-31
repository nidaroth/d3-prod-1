<? require_once("../global/config.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$db->Execute("UPDATE S_STUDENT_MASTER SET ENABLE_AUTO_PAYMENT = '$_REQUEST[va1]' WHERE PK_STUDENT_MASTER = '$_REQUEST[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($_REQUEST['va1'] == 1){
	$NOTES = 'Recurring Payments enabled by school user';
} else {
	$NOTES = 'Recurring Payments disabled by school user';
}

$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 1 ");

$STUDENT_NOTES['IS_EVENT'] 				= 0;
$STUDENT_NOTES['NOTE_DATE'] 			= date("Y-m-d",strtotime($_REQUEST['NOTE_DATE']));
$STUDENT_NOTES['NOTE_TIME'] 			= date("H:i:s",strtotime($_REQUEST['NOTE_TIME']));
$STUDENT_NOTES['PK_DEPARTMENT'] 		= $res->fields['PK_DEPARTMENT'];
$STUDENT_NOTES['PK_EMPLOYEE_MASTER'] 	= $_SESSION['PK_EMPLOYEE_MASTER'];
$STUDENT_NOTES['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $_REQUEST['sid'];
$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $_REQUEST['eid'];
$STUDENT_NOTES['NOTES'] 				= $NOTES;
$STUDENT_NOTES['CREATED_BY']  			= '';
$STUDENT_NOTES['CREATED_ON']  			= date("Y-m-d H:i");
db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');