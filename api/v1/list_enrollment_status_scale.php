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

	$res = $db->Execute("select PK_ENROLLMENT_STATUS_SCALE_MASTER,ENROLLMENT_STATUS from M_ENROLLMENT_STATUS_SCALE_MASTER where PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['ENROLLMENT_STATUS'][$i]['ID'] 	= $res->fields['PK_ENROLLMENT_STATUS_SCALE_MASTER'];
		$data['ENROLLMENT_STATUS'][$i]['TEXT'] 	= $res->fields['ENROLLMENT_STATUS'];
		
		$PK_ENROLLMENT_STATUS_SCALE_MASTER = $res->fields['PK_ENROLLMENT_STATUS_SCALE_MASTER'];
		$j = 0;
		$res_det = $db->Execute("select PK_ENROLLMENT_STATUS_SCALE,CODE,DESCRIPTION,MIN_UNITS_PER_TERM from M_ENROLLMENT_STATUS_SCALE LEFT JOIN M_SCHOOL_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS_SCALE.PK_SCHOOL_ENROLLMENT_STATUS = M_SCHOOL_ENROLLMENT_STATUS.PK_SCHOOL_ENROLLMENT_STATUS where PK_ACCOUNT = '$PK_ACCOUNT' AND M_ENROLLMENT_STATUS_SCALE.ACTIVE = 1 AND PK_ENROLLMENT_STATUS_SCALE_MASTER = '$PK_ENROLLMENT_STATUS_SCALE_MASTER' ");
		while (!$res_det->EOF) { 
			$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['CODE'] 				= $res_det->fields['CODE'];
			$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['DESCRIPTION'] 			= $res_det->fields['DESCRIPTION'];
			$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['MIN_UNITS_PER_TERM']	= $res_det->fields['MIN_UNITS_PER_TERM'];
			$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['ID'] 					= $res_det->fields['PK_ENROLLMENT_STATUS_SCALE'];
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;