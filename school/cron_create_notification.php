<? 
// $path = '../';
$path = '/var/www/html/D3/';
require_once($path."global/config.php");
require_once($path."global/create_notification.php");

$date = date("Y-m-d");
$res = $db->Execute("select PK_STUDENT_MASTER,PK_ACCOUNT,PK_TASK_TYPE from S_STUDENT_OTHER_EDU WHERE TRANSCRIPT_REQUESTED_DATE = '$date' AND PK_TASK_TYPE > 0 ");
while (!$res->EOF){

	$res1 = $db->Execute("SELECT PK_STUDENT_ENROLLMENT,PK_REPRESENTATIVE FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '".$res->fields['PK_STUDENT_MASTER']."' GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ") ;
	
	$res_not = $db->Execute("select PK_EVENT_TEMPLATE from S_EVENT_TEMPLATE WHERE ACTIVE = '1' AND PK_ACCOUNT = '".$res->fields['PK_ACCOUNT']."' AND PK_EVENT_TYPE = 4");
	$noti_data['PK_EVENT_TEMPLATE'] 	= $res_not->fields['PK_EVENT_TEMPLATE'];
	$noti_data['PK_TASK_TYPE'] 			= $res->fields['PK_TASK_TYPE'];
	$noti_data['PK_EMPLOYEE_MASTER'] 	= $res1->fields['PK_REPRESENTATIVE'];
	$noti_data['PK_STUDENT_MASTER'] 	= $res->fields['PK_STUDENT_MASTER'];
	$noti_data['PK_STUDENT_ENROLLMENT'] = $res1->fields['PK_STUDENT_ENROLLMENT'];
	create_notification($noti_data);
	
	//echo "<br />----------------------<br />";
	
	$res->MoveNext();
}

$res = $db->Execute("select PK_ACCOUNT,PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT,PK_STUDENT_TASK,PK_TASK_TYPE,PK_EMPLOYEE_MASTER FROM S_STUDENT_TASK WHERE COMPLETED = 0 AND FOLLOWUP_DATE = '$date' ");
while (!$res->EOF){

	$res_not = $db->Execute("select PK_EVENT_TEMPLATE from S_EVENT_TEMPLATE WHERE ACTIVE = '1' AND PK_ACCOUNT = '".$res->fields['PK_ACCOUNT']."' AND PK_EVENT_TYPE = 6");
	$noti_data['PK_EVENT_TEMPLATE'] 	= $res_not->fields['PK_EVENT_TEMPLATE'];
	$noti_data['PK_TASK_TYPE'] 			= $res->fields['PK_TASK_TYPE'];
	$noti_data['PK_EMPLOYEE_MASTER'] 	= $res->fields['PK_EMPLOYEE_MASTER'];
	$noti_data['PK_STUDENT_MASTER'] 	= $res->fields['PK_STUDENT_MASTER'];
	$noti_data['PK_STUDENT_ENROLLMENT'] = $res->fields['PK_STUDENT_ENROLLMENT'];
	$noti_data['PK_STUDENT_TASK'] 		= $res->fields['PK_STUDENT_TASK'];
	//echo "<pre>";print_r($noti_data);
	create_notification($noti_data);
	
	//echo "<br />----------------------<br />";
	
	$res->MoveNext();
}

echo "done";