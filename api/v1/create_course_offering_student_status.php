<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"STATUS":"aaaaa","DESCRIPTION":"desc","POST_TUITION":"Yes","SHOW_ON_TRANSCRIPT":"No","SHOW_ON_REPORT_CARD":"No","CALCULATE_SAP":"","MAKE_AS_DEFAULT":"yes"}';

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
	
	if($DATA->STATUS == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Status Missing';
	}
	
	if($DATA->POST_TUITION == '') {
		$POST_TUITION = 0;
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
	
	if($DATA->SHOW_ON_TRANSCRIPT == '') {
		$SHOW_ON_TRANSCRIPT = 0;
	} else {
		if(strtolower($DATA->SHOW_ON_TRANSCRIPT) == 'yes') 
			$SHOW_ON_TRANSCRIPT = 1;
		else if(strtolower($DATA->SHOW_ON_TRANSCRIPT) == 'no') 
			$SHOW_ON_TRANSCRIPT = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Show On Transcript Value';
		}
	}
	
	if($DATA->SHOW_ON_REPORT_CARD == '') {
		$SHOW_ON_REPORT_CARD = 0;
	} else {
		if(strtolower($DATA->SHOW_ON_REPORT_CARD) == 'yes') 
			$SHOW_ON_REPORT_CARD = 1;
		else if(strtolower($DATA->SHOW_ON_REPORT_CARD) == 'no') 
			$SHOW_ON_REPORT_CARD = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Show On Report Card Value';
		}
	}
	
	if($DATA->CALCULATE_SAP == '') {
		$CALCULATE_SAP = 0;
	} else {
		if(strtolower($DATA->CALCULATE_SAP) == 'yes') 
			$CALCULATE_SAP = 1;
		else if(strtolower($DATA->CALCULATE_SAP) == 'no') 
			$CALCULATE_SAP = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Calculate SAP Value';
		}
	}
	
	if($DATA->MAKE_AS_DEFAULT == '') {
		$MAKE_AS_DEFAULT = 0;
	} else {
		if(strtolower($DATA->MAKE_AS_DEFAULT) == 'yes') 
			$MAKE_AS_DEFAULT = 1;
		else if(strtolower($DATA->MAKE_AS_DEFAULT) == 'no') 
			$MAKE_AS_DEFAULT = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Make As Default Value';
		}
	}
	
	if($data['SUCCESS'] == 1) {
		if($MAKE_AS_DEFAULT == 1) {
			$db->Execute("UPDATE M_COURSE_OFFERING_STUDENT_STATUS SET MAKE_AS_DEFAULT = 0 WHERE PK_ACCOUNT = '$PK_ACCOUNT'"); 
		}
		$COURSE_OFFERING_STUDENT_STATUS['COURSE_OFFERING_STUDENT_STATUS'] 	= trim($DATA->STATUS);
		$COURSE_OFFERING_STUDENT_STATUS['DESCRIPTION'] 						= trim($DATA->DESCRIPTION);
		$COURSE_OFFERING_STUDENT_STATUS['POST_TUITION'] 					= $POST_TUITION;
		$COURSE_OFFERING_STUDENT_STATUS['SHOW_ON_TRANSCRIPT'] 				= $SHOW_ON_TRANSCRIPT;
		$COURSE_OFFERING_STUDENT_STATUS['SHOW_ON_REPORT_CARD'] 				= $SHOW_ON_REPORT_CARD;
		$COURSE_OFFERING_STUDENT_STATUS['CALCULATE_SAP'] 					= $CALCULATE_SAP;
		$COURSE_OFFERING_STUDENT_STATUS['MAKE_AS_DEFAULT'] 					= $MAKE_AS_DEFAULT;
		$COURSE_OFFERING_STUDENT_STATUS['PK_ACCOUNT']  	 					= $PK_ACCOUNT;
		$COURSE_OFFERING_STUDENT_STATUS['CREATED_ON'] 	 					= date("Y-m-d H:i");
		db_perform('M_COURSE_OFFERING_STUDENT_STATUS', $COURSE_OFFERING_STUDENT_STATUS, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Course Offering Student Status Created';
	}
}

$data = json_encode($data);
echo $data;
