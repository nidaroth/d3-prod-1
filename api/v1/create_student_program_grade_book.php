<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"GRADE_BOOK_CODE_ID":"1","GRADE_BOOK_CODE_DESCRIPTION":"Desc","GRADE_BOOK_TYPE_ID":"1","COMPLETED_DATE":"2021-05-30","SESSIONS_REQUIRED":"2","SESSIONS_COMPLETED":"1","HOURS_REQUIRED":"10","HOURS_COMPLETED":"8","POINTS_REQUIRED":"15","POINTS_EARNED":"14","STUDENT_ENROLLMENT_ID":"1"}';

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
	
	foreach($DATA->STUDENT_PROGRAM_GRADE_BOOK_INPUT as $DATA1){
		//echo "<pre>";print_r($DATA1);exit;
		$PK_GRADE_BOOK_CODE = $DATA1->GRADE_BOOK_CODE_ID;
		if($PK_GRADE_BOOK_CODE == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Missing GRADE_BOOK_CODE_ID Value';
		} else {
			$res_st = $db->Execute("select PK_GRADE_BOOK_CODE from M_GRADE_BOOK_CODE WHERE PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid GRADE_BOOK_CODE_ID Value - '.$PK_GRADE_BOOK_CODE;
			}
		}
		
		$PK_GRADE_BOOK_TYPE = $DATA1->GRADE_BOOK_TYPE_ID;
		if($PK_GRADE_BOOK_TYPE == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Missing GRADE_BOOK_TYPE_ID Value';
		} else {
			$res_st = $db->Execute("select PK_GRADE_BOOK_TYPE from M_GRADE_BOOK_TYPE WHERE PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			$PK_DRIVERS_LICENSE_STATE = $res_st->fields['PK_STATES'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid GRADE_BOOK_TYPE_ID Value - '.$PK_GRADE_BOOK_TYPE;
			}
		}
		
		$PK_STUDENT_ENROLLMENT = $DATA1->STUDENT_ENROLLMENT_ID;
		if($PK_STUDENT_ENROLLMENT == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Missing STUDENT_ENROLLMENT_ID Value';
		} else {
			$res_st = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid STUDENT_ENROLLMENT_ID Value - '.$PK_STUDENT_ENROLLMENT;
			}
		}
	}
	
	if($data['SUCCESS'] == 1) {
	
		foreach($DATA->STUDENT_PROGRAM_GRADE_BOOK_INPUT as $DATA1){
			$PK_STUDENT_ENROLLMENT = $DATA1->STUDENT_ENROLLMENT_ID;
			$res_st = $db->Execute("select PK_CAMPUS_PROGRAM, PK_STUDENT_MASTER from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT = array();
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['COMPLETED_DATE'] 		= $DATA1->COMPLETED_DATE;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['SESSION_COMPLETED'] 		= $DATA1->SESSIONS_COMPLETED;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['HOUR_COMPLETED'] 		= $DATA1->HOURS_COMPLETED;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['SESSION_REQUIRED'] 		= $DATA1->SESSIONS_REQUIRED;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['HOUR_REQUIRED'] 			= $DATA1->HOURS_REQUIRED;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['POINTS_REQUIRED'] 		= $DATA1->POINTS_REQUIRED;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['POINTS_COMPLETED'] 		= $DATA1->POINTS_EARNED;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_GRADE_BOOK_CODE'] 	= $DATA1->GRADE_BOOK_CODE_ID;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['DESCRIPTION'] 			= $DATA1->GRADE_BOOK_CODE_DESCRIPTION;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_GRADE_BOOK_TYPE'] 	= $DATA1->GRADE_BOOK_TYPE_ID;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_CAMPUS_PROGRAM'] 		= $res_st->fields['PK_CAMPUS_PROGRAM']; 
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_STUDENT_MASTER'] 		= $res_st->fields['PK_STUDENT_MASTER']; 
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['PK_ACCOUNT']  			= $PK_ACCOUNT;
			$STUDENT_PROGRAM_GRADE_BOOK_INPUT['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_STUDENT_PROGRAM_GRADE_BOOK_INPUT', $STUDENT_PROGRAM_GRADE_BOOK_INPUT, 'insert');
			$INTERNAL_ID_ARRAY[] = $db->insert_ID();
		}
		$data['INTERNAL_ID'] = $INTERNAL_ID_ARRAY;
		
		$data['MESSAGE'] = 'Student Program Grade Book Created';
	}
}

$data = json_encode($data);
echo $data;
