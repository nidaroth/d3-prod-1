<? require_once("../global/config.php"); 
require_once("../global/texting.php");
require_once("get_department_from_t.php");

$msg_sts = send_text($_REQUEST['no'],$_SESSION['PK_ACCOUNT'],$_REQUEST['msg'],'',$_REQUEST['PK_TEXT_SETTINGS']);

if($msg_sts == 1){
	$PK_DEPARTMENT = get_department_from_t($_REQUEST['t']);	
	$text_data['PK_DEPARTMENT'] 		= $PK_DEPARTMENT;
	$text_data['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
	$text_data['PK_STUDENT_MASTER'] 	= $_REQUEST['sid'];
	$text_data['PK_STUDENT_ENROLLMENT'] = $_REQUEST['eid'];
	$text_data['TEXT_CONTENT'] 			= $_REQUEST['msg'];
	$text_data['TO_PHONE'] 				= $_REQUEST['no'];
	text_log($text_data);
}