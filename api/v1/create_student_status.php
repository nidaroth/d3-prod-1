<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"STUDENT_STATUS":"Tuition","DESCRIPTION":"Tuition","END_DATE_ID":"","FA_STATUS":"23456","ADMISSIONS":"1234","POST_TUITION":"Yes","DOC_28_1":"Yes","CLASS_ENROLLMENT":"Yes","_1098T":"Yes","ALLOW_ATTENDANCE":"Yes","COMPLETED":"Yes","END_DATE_ID":"Yes"}';

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
	
	if($DATA->STUDENT_STATUS == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Student Status Missing';
	}

	if($DATA->ADMISSIONS == '') {
		$ADMISSIONS = 0;
	} else {
		if(strtolower($DATA->ADMISSIONS) == 'yes') 
			$ADMISSIONS = 1;
		else if(strtolower($DATA->ADMISSIONS) == 'no') 
			$ADMISSIONS = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Admissions Value';
		}
	}
	
	if($DATA->POST_TUITION == '') {
		$LMS_ACTIVE = 0;
	} else {
		if(strtolower($DATA->POST_TUITION) == 'yes') 
			$POST_TUITION = 1;
		else if(strtolower($DATA->POST_TUITION) == 'no') 
			$POST_TUITION = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Post Tuition Value';
		}
	}
	
	if($DATA->DOC_28_1 == '') {
		$LMS_ACTIVE = 0;
	} else {
		if(strtolower($DATA->DOC_28_1) == 'yes') 
			$DOC_28_1 = 1;
		else if(strtolower($DATA->DOC_28_1) == 'no') 
			$DOC_28_1 = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Doc28.1 Value';
		}
	}
	
	if($DATA->CLASS_ENROLLMENT == '') {
		$LMS_ACTIVE = 0;
	} else {
		if(strtolower($DATA->CLASS_ENROLLMENT) == 'yes') 
			$CLASS_ENROLLMENT = 1;
		else if(strtolower($DATA->CLASS_ENROLLMENT) == 'no') 
			$CLASS_ENROLLMENT = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Class Enrollment Value';
		}
	}
	
	if($DATA->_1098T == '') {
		$LMS_ACTIVE = 0;
	} else {
		if(strtolower($DATA->_1098T) == 'yes') 
			$_1098T = 1;
		else if(strtolower($DATA->_1098T) == 'no') 
			$_1098T = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Class 1098T Value';
		}
	}
	
	if($DATA->ALLOW_ATTENDANCE == '') {
		$LMS_ACTIVE = 0;
	} else {
		if(strtolower($DATA->ALLOW_ATTENDANCE) == 'yes') 
			$ALLOW_ATTENDANCE = 1;
		else if(strtolower($DATA->ALLOW_ATTENDANCE) == 'no') 
			$ALLOW_ATTENDANCE = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Allow Attendance Value';
		}
	}
	
	if($DATA->COMPLETED == '') {
		$LMS_ACTIVE = 0;
	} else {
		if(strtolower($DATA->COMPLETED) == 'yes') 
			$COMPLETED = 1;
		else if(strtolower($DATA->COMPLETED) == 'no') 
			$COMPLETED = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Completed Value';
		}
	}
	
	if($DATA->END_DATE_ID != '') {
		$PK_END_DATE = trim($DATA->END_DATE_ID);
		$res = $db->Execute("SELECT PK_END_DATE FROM M_END_DATE WHERE  ACTIVE = 1 AND PK_END_DATE = '$PK_END_DATE' ");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid End Date ID';
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$STUDENT_STATUS['STUDENT_STATUS'] 		= trim($DATA->STUDENT_STATUS);
		$STUDENT_STATUS['DESCRIPTION'] 			= trim($DATA->DESCRIPTION);
		$STUDENT_STATUS['PK_END_DATE'] 			= $PK_END_DATE;
		$STUDENT_STATUS['FA_STATUS'] 			= trim($DATA->FA_STATUS);
		$STUDENT_STATUS['ADMISSIONS'] 			= $ADMISSIONS;
		$STUDENT_STATUS['POST_TUITION'] 		= $POST_TUITION;
		
		$STUDENT_STATUS['DOC_28_1'] 			= $DOC_28_1;
		$STUDENT_STATUS['CLASS_ENROLLMENT'] 	= $CLASS_ENROLLMENT;
		$STUDENT_STATUS['_1098T'] 				= $_1098T;
		$STUDENT_STATUS['ALLOW_ATTENDANCE'] 	= $ALLOW_ATTENDANCE;
		$STUDENT_STATUS['COMPLETED'] 			= $COMPLETED;
		
		$STUDENT_STATUS['PK_ACCOUNT']  	 	= $PK_ACCOUNT;
		$STUDENT_STATUS['CREATED_ON'] 	 		= date("Y-m-d H:i");
		db_perform('M_STUDENT_STATUS', $STUDENT_STATUS, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Student Status Created';
	}
}

$data = json_encode($data);
echo $data;
