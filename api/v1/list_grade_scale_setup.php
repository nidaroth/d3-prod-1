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

	$res = $db->Execute("SELECT PK_GRADE_SCALE_MASTER, GRADE_SCALE FROM S_GRADE_SCALE_MASTER WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['GRADE_SCALE'][$i]['ID'] 		= $res->fields['PK_GRADE_SCALE_MASTER'];
		$data['GRADE_SCALE'][$i]['TEXT'] 	= $res->fields['GRADE_SCALE'];
		
		$PK_GRADE_SCALE_MASTER = $res->fields['PK_GRADE_SCALE_MASTER'];
		$j = 0;
		$res_det = $db->Execute("SELECT PK_GRADE_SCALE_DETAIL,MIN_PERCENTAGE,MAX_PERCENTAGE,S_GRADE_SCALE_DETAIL.PK_GRADE, GRADE FROM S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE WHERE S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' ");
		while (!$res_det->EOF) { 
			$data['GRADE_SCALE'][$i]['SCALE'][$j]['ID'] 			= $res_det->fields['PK_GRADE_SCALE_DETAIL'];
			$data['GRADE_SCALE'][$i]['SCALE'][$j]['MIN_PERCENTAGE'] = $res_det->fields['MIN_PERCENTAGE'];
			$data['GRADE_SCALE'][$i]['SCALE'][$j]['MAX_PERCENTAGE'] = $res_det->fields['MAX_PERCENTAGE'];
			$data['GRADE_SCALE'][$i]['SCALE'][$j]['GRADE_LETTER']	= $res_det->fields['GRADE'];
			$data['GRADE_SCALE'][$i]['SCALE'][$j]['GRADE_ID']		= $res_det->fields['PK_GRADE'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;