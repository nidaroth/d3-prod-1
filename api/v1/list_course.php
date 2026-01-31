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

	$res = $db->Execute("SELECT PK_COURSE,COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION, UNITS, FA_UNITS, HOURS, PREP_HOURS, MAX_CLASS_SIZE, S_COURSE.PK_ATTENDANCE_CODE, ATTENDANCE_CODE,  S_COURSE.PK_ATTENDANCE_TYPE, ATTENDANCE_TYPE, EXTERNAL_ID, IF(ALLOW_ONLINE_ENROLLMENT = 1, 'Yes', 'No') as ALLOW_ONLINE_ENROLLMENT, FULL_COURSE_DESCRIPTION, IF(S_COURSE.ACTIVE = 1, 'Yes', 'No') as ACTIVE FROM S_COURSE LEFT JOIN M_ATTENDANCE_CODE on M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE =  S_COURSE.PK_ATTENDANCE_CODE LEFT JOIN M_ATTENDANCE_TYPE on M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE =  S_COURSE.PK_ATTENDANCE_TYPE WHERE S_COURSE.PK_ACCOUNT = '$PK_ACCOUNT' ");
	$i = 0;
	while (!$res->EOF) { 
		$PK_COURSE = $res->fields['PK_COURSE'];
		$data['COURSE'][$i]['ID'] 							= $PK_COURSE;
		$data['COURSE'][$i]['COURSE_CODE'] 					= $res->fields['COURSE_CODE'];
		$data['COURSE'][$i]['TRANSCRIPT_CODE'] 				= $res->fields['TRANSCRIPT_CODE'];
		$data['COURSE'][$i]['COURSE_DESCRIPTION'] 			= $res->fields['COURSE_DESCRIPTION'];
		$data['COURSE'][$i]['UNITS'] 						= $res->fields['UNITS'];
		$data['COURSE'][$i]['FA_UNITS'] 					= $res->fields['FA_UNITS'];
		$data['COURSE'][$i]['HOURS'] 						= $res->fields['HOURS'];
		$data['COURSE'][$i]['PREP_HOURS'] 					= $res->fields['PREP_HOURS'];
		$data['COURSE'][$i]['MAX_CLASS_SIZE'] 				= $res->fields['MAX_CLASS_SIZE'];
		$data['COURSE'][$i]['DEFAULT_ATTENDANCE_TYPE_ID'] 	= $res->fields['PK_ATTENDANCE_TYPE'];
		$data['COURSE'][$i]['DEFAULT_ATTENDANCE_TYPE'] 		= $res->fields['ATTENDANCE_TYPE'];
		$data['COURSE'][$i]['DEFAULT_ATTENDANCE_CODE_ID'] 	= $res->fields['PK_ATTENDANCE_CODE'];
		$data['COURSE'][$i]['DEFAULT_ATTENDANCE_CODE'] 		= $res->fields['ATTENDANCE_CODE'];
		$data['COURSE'][$i]['EXTERNAL_ID'] 					= $res->fields['EXTERNAL_ID'];
		$data['COURSE'][$i]['ACTIVE'] 						= $res->fields['ACTIVE'];
		$data['COURSE'][$i]['ALLOW_ONLINE_ENROLLMENT'] 		= $res->fields['ALLOW_ONLINE_ENROLLMENT'];
		$data['COURSE'][$i]['FULL_COURSE_DESCRIPTION'] 		= $res->fields['FULL_COURSE_DESCRIPTION'];
		

		$j = 0;
		$res_det = $db->Execute("select PK_COURSE_CAMPUS,OFFICIAL_CAMPUS_NAME,S_CAMPUS.PK_CAMPUS FROM S_CAMPUS,S_COURSE_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_COURSE_CAMPUS.PK_CAMPUS AND PK_COURSE = '$PK_COURSE' AND S_COURSE_CAMPUS.PK_ACCOUNT = '$PK_ACCOUNT'"); 
		while (!$res_det->EOF) { 
			$data['COURSE'][$i]['CAMPUS'][$j]['NAME']		= $res_det->fields['OFFICIAL_CAMPUS_NAME'];
			$data['COURSE'][$i]['CAMPUS'][$j]['CAMPUS_ID']	= $res_det->fields['PK_CAMPUS'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select PK_COURSE_PREREQUISITE,COURSE_CODE,S_COURSE.PK_COURSE FROM S_COURSE,S_COURSE_PREREQUISITE WHERE S_COURSE.PK_COURSE = S_COURSE_PREREQUISITE.PK_PREREQUISITE_COURSE AND S_COURSE_PREREQUISITE.PK_COURSE = '$PK_COURSE' AND S_COURSE_PREREQUISITE.PK_ACCOUNT = '$PK_ACCOUNT'"); 
		while (!$res_det->EOF) { 
			$data['COURSE'][$i]['PREREQUISITE_COURSE'][$j]['CODE']		= $res_det->fields['COURSE_CODE'];
			$data['COURSE'][$i]['PREREQUISITE_COURSE'][$j]['COURSE_ID']	= $res_det->fields['PK_COURSE'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select PK_COURSE_COREQUISITES,COURSE_CODE,S_COURSE.PK_COURSE FROM S_COURSE,S_COURSE_COREQUISITES WHERE S_COURSE.PK_COURSE = S_COURSE_COREQUISITES.PK_COREQUISITES_COURSE AND S_COURSE_COREQUISITES.PK_COURSE = '$PK_COURSE' AND S_COURSE_COREQUISITES.PK_ACCOUNT = '$PK_ACCOUNT'"); 
		while (!$res_det->EOF) { 
			$data['COURSE'][$i]['COREQUISITE_COURSE'][$j]['CODE']		= $res_det->fields['COURSE_CODE'];
			$data['COURSE'][$i]['COREQUISITE_COURSE'][$j]['COURSE_ID']	= $res_det->fields['PK_COURSE'];
			
			$j++;
			$res_det->MoveNext();
		}

		$j = 0;
		$res_det = $db->Execute("SELECT CONCAT(CODE,' - ',LEDGER_DESCRIPTION) AS LEDGER, S_COURSE_FEE.PK_AR_LEDGER_CODE, S_COURSE_FEE.DESCRIPTION, FEE_AMT, ISBN_10, ISBN_13, SCHOOL_COST  FROM S_COURSE_FEE LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_COURSE_FEE.PK_AR_LEDGER_CODE WHERE S_COURSE_FEE.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_COURSE = '$PK_COURSE' "); 
		while (!$res_det->EOF) { 
			$data['COURSE'][$i]['FEES'][$j]['FEE']			= $res_det->fields['LEDGER'];
			$data['COURSE'][$i]['FEES'][$j]['FEE_ID']		= $res_det->fields['PK_AR_LEDGER_CODE'];
			$data['COURSE'][$i]['FEES'][$j]['DESCRIPTION']	= $res_det->fields['DESCRIPTION'];
			$data['COURSE'][$i]['FEES'][$j]['FEE_AMT']		= $res_det->fields['FEE_AMT'];
			$data['COURSE'][$i]['FEES'][$j]['ISBN_10']		= $res_det->fields['ISBN_10'];
			$data['COURSE'][$i]['FEES'][$j]['ISBN_13']		= $res_det->fields['ISBN_13'];
			$data['COURSE'][$i]['FEES'][$j]['SCHOOL_COST']	= $res_det->fields['SCHOOL_COST'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("SELECT PK_COURSE_GRADE_BOOK,COLUMN_NO, CODE, S_COURSE_GRADE_BOOK.DESCRIPTION, GRADE_BOOK_TYPE, S_COURSE_GRADE_BOOK.PK_GRADE_BOOK_TYPE, PERIOD, POINTS, WEIGHT, WEIGHTED_POINTS FROM S_COURSE_GRADE_BOOK LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_COURSE_GRADE_BOOK.PK_GRADE_BOOK_TYPE WHERE S_COURSE_GRADE_BOOK.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_COURSE = '$PK_COURSE'"); 
		while (!$res_det->EOF) { 
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['COLUMN_NO']			= $res_det->fields['COLUMN_NO'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['CODE']				= $res_det->fields['CODE'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['DESCRIPTION']		= $res_det->fields['DESCRIPTION'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['TYPE']				= $res_det->fields['GRADE_BOOK_TYPE'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['TYPE_ID']			= $res_det->fields['PK_GRADE_BOOK_TYPE'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['PERIOD']				= $res_det->fields['PERIOD'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['POINTS']				= $res_det->fields['POINTS'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['WEIGHT']				= $res_det->fields['WEIGHT'];
			$data['COURSE'][$i]['GRADE_BOOK'][$j]['WEIGHTED_POINTS']	= $res_det->fields['WEIGHTED_POINTS'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;