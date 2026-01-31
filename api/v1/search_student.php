<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}

$flag = 1;
if($API_KEY == ''){
	$data['SUCCESS'] = 0;
	$data['MESSAGE'] = 'API Key Missing';
	
	$flag = 0;
} else {
	$res = $db->Execute("SELECT PK_ACCOUNT,ACTIVE FROM Z_ACCOUNT where API_KEY = '$API_KEY'");
	if($res->RecordCount() == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Invalid API Key';
		
		$flag = 0;
	} else if($res->fields['ACTIVE'] == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Your Account Is Blocked.';
		
		$flag = 0;
	}
	
	$PK_ACCOUNT = $res->fields['PK_ACCOUNT'];
}

if($flag == 1){
	$data['SUCCESS'] = 1;
	$data['MESSAGE'] = '';
	
	$cond   = "";
	if($_GET['old_stu_id'] != '')
		$cond .= " AND trim(OLD_DSIS_STU_NO) = '".trim($_GET['old_stu_id'])."' ";
	if($_GET['old_lead_id'] != '')
		$cond .= " AND trim(OLD_DSIS_LEAD_ID) = '".trim($_GET['old_lead_id'])."' ";
	if($_GET['adm_id'] != '') {
		$cond  .= " AND trim(ADM_USER_ID) = '".trim($_GET['adm_id'])."' ";
	}	
	$res = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, OLD_DSIS_STU_NO, OLD_DSIS_LEAD_ID, ADM_USER_ID  FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' AND ARCHIVED = 0 $cond ");
	if($res->RecordCount() == 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'No Record Found';
	}
	
	$i = 0;
	while (!$res->EOF) { 
		$data['RESULT'][$i]['OLD_DSIS_STU_NO']  = $res->fields['OLD_DSIS_STU_NO'];
		$data['RESULT'][$i]['OLD_DSIS_LEAD_ID'] = $res->fields['OLD_DSIS_LEAD_ID'];
		$data['RESULT'][$i]['ADM_USER_ID'] 		= $res->fields['ADM_USER_ID'];
			
		$data['RESULT'][$i]['ID'] = $res->fields['PK_STUDENT_MASTER'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;
