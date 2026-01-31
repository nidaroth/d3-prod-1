<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"COURSE_CODE":"course code API","TRANSCRIPT_CODE":"trans code","COURSE_DESCRIPTION":"course dec","UNITS":"1.00","FA_UNITS":"2.00","HOURS":"3.00","PREP_HOURS":"4","MAX_CLASS_SIZE":"5","DEFAULT_ATTENDANCE_TYPE_ID":"2","DEFAULT_ATTENDANCE_CODE_ID":"11","EXTERNAL_ID":"6","ACTIVE":"Yes","ALLOW_ONLINE_ENROLLMENT":"Yes","FULL_COURSE_DESCRIPTION":"full course desc","CAMPUS":[2,3],"PREREQUISITE_COURSE":[6,7],"PREREQUISITE_COURSE":[3,6],"FEES":[{"FEE_ID":"152","DESCRIPTION":"fee desc 1","FEE_AMT":"1.00","ISBN_10":"2","ISBN_13":"3","SCHOOL_COST":"4.00"},{"FEE_ID":"166","DESCRIPTION":"fee desc 2","FEE_AMT":"5.00","ISBN_10":"6","ISBN_13":"7","SCHOOL_COST":"8.00"}],"GRADE_BOOK":[{"COLUMN_NO":"1","CODE":"2","DESCRIPTION":"3","TYPE_ID":"1","PERIOD":"4","POINTS":"5","WEIGHT":"6",},{"COLUMN_NO":"8","CODE":"9","DESCRIPTION":"10","TYPE_ID":"2","PERIOD":"12","POINTS":"13","WEIGHT":"14",}]}';

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}

$DATA = ($DATA);
$DATA = json_decode($DATA);

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
	
	$COURSE_CODE				= trim($DATA->COURSE_CODE);
	$TRANSCRIPT_CODE 			= trim($DATA->TRANSCRIPT_CODE);
	$COURSE_DESCRIPTION 		= trim($DATA->COURSE_DESCRIPTION);
	$UNITS 						= trim($DATA->UNITS);
	$FA_UNITS 					= trim($DATA->FA_UNITS);
	$HOURS 						= trim($DATA->HOURS);
	$PREP_HOURS 				= trim($DATA->PREP_HOURS);
	$MAX_CLASS_SIZE 			= trim($DATA->MAX_CLASS_SIZE);
	$PK_ATTENDANCE_TYPE 		= trim($DATA->DEFAULT_ATTENDANCE_TYPE_ID);
	$PK_ATTENDANCE_CODE 		= trim($DATA->DEFAULT_ATTENDANCE_CODE_ID);
	$EXTERNAL_ID 				= trim($DATA->EXTERNAL_ID);

	$ACTIVE 					= trim($DATA->ACTIVE);
	$ALLOW_ONLINE_ENROLLMENT 	= trim($DATA->ALLOW_ONLINE_ENROLLMENT);
	$FULL_COURSE_DESCRIPTION 	= trim($DATA->FULL_COURSE_DESCRIPTION);
	
	if($COURSE_CODE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] = 'Missing COURSE_CODE Value';
	}
	
	if(strtolower($ACTIVE) == 'yes')
		$ACTIVE = 1;
	else if(strtolower($ACTIVE) == 'no')
		$ACTIVE = 0;
	else if($ACTIVE != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] = 'Invalid ACTIVE Value';
	} else
		$ACTIVE = 1;
	
	if(strtolower($ALLOW_ONLINE_ENROLLMENT) == 'yes')
		$ALLOW_ONLINE_ENROLLMENT = 1;
	else if(strtolower($ALLOW_ONLINE_ENROLLMENT) == 'no')
		$ALLOW_ONLINE_ENROLLMENT = 0;
	else if($ALLOW_ONLINE_ENROLLMENT != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] = 'Invalid ALLOW_ONLINE_ENROLLMENT Value';
	} else
		$ALLOW_ONLINE_ENROLLMENT = 0;
		
	if(!empty($DATA->CAMPUS)){
		foreach($DATA->CAMPUS as $PK_CAMPUS) {
			$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] = 'Invalid CAMPUS Value - '.$PK_CAMPUS;
			} else 
				$PK_CAMPUS_ARR[] = $PK_CAMPUS;
		}
	}
	
	if(!empty($DATA->PREREQUISITE_COURSE)){
		foreach($DATA->PREREQUISITE_COURSE as $PK_COURSE) {
			$res_st = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] = 'Invalid PREREQUISITE_COURSE Value - '.$PK_COURSE;
			} else 
				$PK_PREREQUISITE_COURSE_ARR[] = $PK_COURSE;
		}
	}
	
	if(!empty($DATA->COREQUISITE_COURSE)){
		foreach($DATA->COREQUISITE_COURSE as $PK_COURSE) {
			$res_st = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] = 'Invalid COREQUISITE_COURSE Value - '.$PK_COURSE;
			} else 
				$PK_COURSE_COREQUISITES_ARR[] = $PK_COURSE;
		}
	}

	if(!empty($DATA->FEES)){
		foreach($DATA->FEES as $FEES) {
			if($FEES->FEE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] = 'Missing FEE_ID Value';
			} else {
				$PK_AR_LEDGER_CODE = $FEES->FEE_ID;
				$res_st = $db->Execute("select PK_AR_LEDGER_CODE from M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT='$PK_ACCOUNT'");
			
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] = 'Invalid FEE_ID Value - '.$PK_AR_LEDGER_CODE;
				}
			}
		}
	}
	
	if(!empty($DATA->GRADE_BOOK)){
		foreach($DATA->GRADE_BOOK as $GRADE_BOOK) {
			if($GRADE_BOOK->TYPE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] = 'Missing TYPE_ID Value';
			} else {
				$PK_GRADE_BOOK_TYPE = $GRADE_BOOK->TYPE_ID;
				$res_st = $db->Execute("select PK_GRADE_BOOK_TYPE FROM M_GRADE_BOOK_TYPE WHERE PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND PK_ACCOUNT='$PK_ACCOUNT'");
			
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] = 'Invalid TYPE_ID Value - '.$PK_GRADE_BOOK_TYPE;
				}
			}
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$COURSE['COURSE_CODE']  			= $COURSE_CODE;
		$COURSE['TRANSCRIPT_CODE']  		= $TRANSCRIPT_CODE;
		$COURSE['COURSE_DESCRIPTION']  		= $COURSE_DESCRIPTION;
		$COURSE['UNITS']  					= $UNITS;
		$COURSE['HOURS']  					= $HOURS;
		$COURSE['FA_UNITS']  				= $FA_UNITS;
		$COURSE['PREP_HOURS']  				= $PREP_HOURS;
		$COURSE['MAX_CLASS_SIZE']  			= $MAX_CLASS_SIZE;
		$COURSE['PK_ATTENDANCE_TYPE']  		= $PK_ATTENDANCE_TYPE;
		$COURSE['PK_ATTENDANCE_CODE']  		= $PK_ATTENDANCE_CODE;
		$COURSE['EXTERNAL_ID']  			= $EXTERNAL_ID;
		$COURSE['ALLOW_ONLINE_ENROLLMENT']  = $ALLOW_ONLINE_ENROLLMENT;
		$COURSE['FULL_COURSE_DESCRIPTION']  = $FULL_COURSE_DESCRIPTION;
		
		$COURSE['PK_ACCOUNT']  = $PK_ACCOUNT;
		$COURSE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_COURSE', $COURSE, 'insert');
		$PK_COURSE = $db->insert_ID();
		
		if(!empty($PK_CAMPUS_ARR)){
			foreach($PK_CAMPUS_ARR as $PK_CAMPUS) {
				$COURSE_CAMPUS['PK_CAMPUS']   	= $PK_CAMPUS;
				$COURSE_CAMPUS['PK_COURSE'] 	= $PK_COURSE;
				$COURSE_CAMPUS['PK_ACCOUNT'] 	= $PK_ACCOUNT;
				$COURSE_CAMPUS['CREATED_ON']  	= date("Y-m-d H:i");
				db_perform('S_COURSE_CAMPUS', $COURSE_CAMPUS, 'insert');
			}
		}
		
		if(!empty($PK_PREREQUISITE_COURSE_ARR)){
			foreach($PK_PREREQUISITE_COURSE_ARR as $PK_PREREQUISITE_COURSE) {
				$COURSE_PREREQUISITE['PK_COURSE']   			= $PK_COURSE;
				$COURSE_PREREQUISITE['PK_PREREQUISITE_COURSE'] 	= $PK_PREREQUISITE_COURSE;
				$COURSE_PREREQUISITE['PK_ACCOUNT'] 				= $PK_ACCOUNT;
				$COURSE_PREREQUISITE['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_COURSE_PREREQUISITE', $COURSE_PREREQUISITE, 'insert');
			}
		}
		
		if(!empty($PK_COURSE_COREQUISITES_ARR)){
			foreach($PK_COURSE_COREQUISITES_ARR as $PK_COURSE_COREQUISITES) {
				$COURSE_COREQUISITES['PK_COURSE']   			= $PK_COURSE;
				$COURSE_COREQUISITES['PK_COREQUISITES_COURSE'] 	= $PK_COURSE_COREQUISITES;
				$COURSE_COREQUISITES['PK_ACCOUNT'] 				= $PK_ACCOUNT;
				$COURSE_COREQUISITES['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_COURSE_COREQUISITES', $COURSE_COREQUISITES, 'insert');
			}
		}
		
		////////////////
		if(!empty($DATA->FEES)){
			foreach($DATA->FEES as $FEES) {
				$COURSE_FEE['PK_AR_LEDGER_CODE']   	= $FEES->FEE_ID;
				$COURSE_FEE['DESCRIPTION']   		= $FEES->DESCRIPTION;
				$COURSE_FEE['FEE_AMT']   			= $FEES->FEE_AMT;
				$COURSE_FEE['ISBN_10']   			= $FEES->ISBN_10;
				$COURSE_FEE['ISBN_13']   			= $FEES->ISBN_13;
				$COURSE_FEE['SCHOOL_COST']   		= $FEES->SCHOOL_COST;
				$COURSE_FEE['PK_COURSE'] 			= $PK_COURSE;
				$COURSE_FEE['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$COURSE_FEE['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_COURSE_FEE', $COURSE_FEE, 'insert');
				
			}
		}
		
		if(!empty($DATA->GRADE_BOOK)){
			foreach($DATA->GRADE_BOOK as $GRADE_BOOK) {
				$GRADE_BOOK_ARR['COLUMN_NO']   			= $GRADE_BOOK->COLUMN_NO;
				$GRADE_BOOK_ARR['CODE']   				= $GRADE_BOOK->CODE;
				$GRADE_BOOK_ARR['DESCRIPTION']   		= $GRADE_BOOK->DESCRIPTION;
				$GRADE_BOOK_ARR['PK_GRADE_BOOK_TYPE']   = $GRADE_BOOK->TYPE_ID;
				$GRADE_BOOK_ARR['PERIOD']   			= $GRADE_BOOK->PERIOD;
				$GRADE_BOOK_ARR['POINTS']   			= $GRADE_BOOK->POINTS;
				$GRADE_BOOK_ARR['WEIGHT']   			= $GRADE_BOOK->WEIGHT;
				$GRADE_BOOK_ARR['WEIGHTED_POINTS']   	= $GRADE_BOOK_ARR['POINTS'] * $GRADE_BOOK_ARR['WEIGHT'];
				$GRADE_BOOK_ARR['PK_COURSE'] 			= $PK_COURSE;
				$GRADE_BOOK_ARR['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$GRADE_BOOK_ARR['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_COURSE_GRADE_BOOK', $GRADE_BOOK_ARR, 'insert');
				
			}
		}
		/////////////////
		
		$data['MESSAGE'] = 'Course Created';
		$data['INTERNAL_ID'] = $PK_COURSE;
		
	}
}

$data = json_encode($data);
echo $data;