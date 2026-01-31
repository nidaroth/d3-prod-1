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

	$res = $db->Execute("SELECT PK_PLACEMENT_STATUS,PLACEMENT_STATUS,PLACEMENT_STUDENT_STATUS_CATEGORY, M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY , IF(EMPLOYED = 1,'Yes','No') as EMPLOYED FROM M_PLACEMENT_STATUS LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE M_PLACEMENT_STATUS.PK_ACCOUNT = '$PK_ACCOUNT' AND M_PLACEMENT_STATUS.ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['PLACEMENT_STATUS'][$i]['ID'] 										= $res->fields['PK_PLACEMENT_STATUS'];
		$data['PLACEMENT_STATUS'][$i]['TEXT'] 										= $res->fields['PLACEMENT_STATUS'];
		$data['PLACEMENT_STATUS'][$i]['PLACEMENT_STUDENT_STATUS_CATEGORY'] 			= $res->fields['PLACEMENT_STUDENT_STATUS_CATEGORY'];
		$data['PLACEMENT_STATUS'][$i]['PLACEMENT_STUDENT_STATUS_CATEGORY_ID'] 		= $res->fields['PK_PLACEMENT_STUDENT_STATUS_CATEGORY'];
		$data['PLACEMENT_STATUS'][$i]['EMPLOYED'] 									= $res->fields['EMPLOYED'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;