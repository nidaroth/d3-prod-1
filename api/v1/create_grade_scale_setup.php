<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"TEXT":"API","SCALE":[{"MIN_PERCENTAGE":"36.00","MAX_PERCENTAGE":"50.00","GRADE_ID":"4"},{"MIN_PERCENTAGE":"51.00","MAX_PERCENTAGE":"70.00","GRADE_ID":"1"}]}';

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

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
	
	if($DATA->TEXT == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'TEXT Value Missing';
	}
	
	$i = 0;
	foreach($DATA->SCALE as $SCALE){
		if($SCALE->GRADE_ID == '') {
			$error[$i] = ' GRADE_ID Value Missing';
			$i++;
		} else {
			$PK_GRADE = trim($SCALE->GRADE_ID);
			
			$res = $db->Execute("select PK_GRADE from S_GRADE WHERE PK_GRADE = '$PK_GRADE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			if($res->RecordCount() == 0) {
				$error[$i] = 'Invalid GRADE_ID - '.$PK_GRADE;
				$i++;
			}
		}
	}
	
	if($i > 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = implode(",",$error);
	}
	
	
	if($data['SUCCESS'] == 1) {
		$GRADE_SCALE_MASTER['GRADE_SCALE'] 	= $DATA->TEXT;
		$GRADE_SCALE_MASTER['PK_ACCOUNT']  	= $PK_ACCOUNT;
		$GRADE_SCALE_MASTER['CREATED_ON']  	= date("Y-m-d H:i");
		db_perform('S_GRADE_SCALE_MASTER', $GRADE_SCALE_MASTER, 'insert');
		$PK_GRADE_SCALE_MASTER = $db->insert_ID();
				
		foreach($DATA->SCALE as $SCALE){

			$GRADE_SCALE_DETAIL['MIN_PERCENTAGE'] 			= $SCALE->MIN_PERCENTAGE;
			$GRADE_SCALE_DETAIL['MAX_PERCENTAGE'] 			= $SCALE->MAX_PERCENTAGE;
			$GRADE_SCALE_DETAIL['PK_GRADE'] 				= $SCALE->GRADE_ID;
			$GRADE_SCALE_DETAIL['PK_GRADE_SCALE_MASTER']  	= $PK_GRADE_SCALE_MASTER;
			$GRADE_SCALE_DETAIL['PK_ACCOUNT']  				= $PK_ACCOUNT;
			$GRADE_SCALE_DETAIL['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_GRADE_SCALE_DETAIL', $GRADE_SCALE_DETAIL, 'insert');

		}
		
		$data['INTERNAL_ID'] = $PK_GRADE_SCALE_MASTER;
		$data['MESSAGE'] 	 = 'Grade Scale Setup Created';
		
		$res = $db->Execute("SELECT PK_GRADE_SCALE_MASTER, GRADE_SCALE FROM S_GRADE_SCALE_MASTER WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' ");
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
}

$data = json_encode($data);
echo $data;
