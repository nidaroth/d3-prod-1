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
	
	$CODE								= trim($DATA->CODE);
	$DESCRIPTION 						= trim($DATA->DESCRIPTION);
	$USE_PROGRAM_GRADE 					= trim($DATA->USE_PROGRAM_GRADE);
	$PK_CREDENTIAL_LEVEL 				= trim($DATA->CREDENTIAL_LEVEL_ID);
	$CLOCK_CREDIT 						= trim($DATA->CLOCK_CREDIT_ID);
	$DIPLOMA 							= trim($DATA->DIPLOMA_ID);
	$CAPACITY 							= trim($DATA->CAPACITY);
	$PK_ENROLLMENT_STATUS_SCALE_MASTER 	= trim($DATA->ENROLLMENT_STATUS_ID);
	$PK_PROGRAM_GROUP 					= trim($DATA->PROGRAM_GROUP_ID);
	$PK_GRADE_SCALE_MASTER 				= trim($DATA->GRADE_SCALE_ID);
	$PK_SPECIAL_PROGRAM_INDICATOR 		= trim($DATA->SPECIAL_PROGRAM_INDICATOR_ID);
	$PK_TUITION_TYPE 					= trim($DATA->TUITION_TYPE_ID);
	$CIP_CODE 							= trim($DATA->CIP_CODE);
	
	$ACTIVE 							= trim($DATA->ACTIVE);
	$CIP_DEFINITION 					= trim($DATA->CIP_DEFINITION);
	$NOTES 								= trim($DATA->NOTES);
	$UNITS 								= trim($DATA->UNITS);
	$FA_UNITS 							= trim($DATA->FA_UNITS);
	$UNIT_COST 							= trim($DATA->UNIT_COST);
	
	$MONTHS 							= trim($DATA->PROGRAM_LENGTH->MONTHS);
	$WEEKS 								= trim($DATA->PROGRAM_LENGTH->WEEKS);
	$HOURS 								= trim($DATA->PROGRAM_LENGTH->HOURS);
	$DAILY_HOURS_SUNDAY 				= trim($DATA->DAILY_HOURS->SUNDAY);
	$DAILY_HOURS_MONDAY 				= trim($DATA->DAILY_HOURS->MONDAY);
	$DAILY_HOURS_TUESDAY 				= trim($DATA->DAILY_HOURS->TUESDAY);
	$DAILY_HOURS_WEDNESDAY 				= trim($DATA->DAILY_HOURS->WEDNESDAY);
	$DAILY_HOURS_THURSDAY 				= trim($DATA->DAILY_HOURS->THURSDAY);
	$DAILY_HOURS_FRIDAY 				= trim($DATA->DAILY_HOURS->FRIDAY);
	$DAILY_HOURS_SATURDAY 				= trim($DATA->DAILY_HOURS->SATURDAY);
	
	$GE_PROGRAM 						= trim($DATA->ANALYTICS_SETUP->GE_PROGRAM);
	$MED_DEN_RESI 						= trim($DATA->ANALYTICS_SETUP->MED_DEN_RESI);
	$FISAP 								= trim($DATA->ANALYTICS_SETUP->FISAP);
	$_1098T 							= trim($DATA->ANALYTICS_SETUP->_1098T);
	$_90_10_REPORT 						= trim($DATA->ANALYTICS_SETUP->_90_10_REPORT);
	$ABHES 								= trim($DATA->ANALYTICS_SETUP->ABHES);
	$TERM_DAYS 							= trim($DATA->ANALYTICS_SETUP->TERM_DAYS);
	$TERM_MONTHS 						= trim($DATA->ANALYTICS_SETUP->TERM_MONTHS);
	$PK_ENROLLMENT_STATUS 				= trim($DATA->ANALYTICS_SETUP->ENROLLMENT_STATUS_ID);
	$PK_EARNING_TYPE 					= trim($DATA->ANALYTICS_SETUP->EARNING_TYPE_ID);
	
	if($CODE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing CODE Value';
	}
	
	if(strtolower($ACTIVE) == 'yes')
		$ACTIVE = 1;
	else if(strtolower($ACTIVE) == 'no')
		$ACTIVE = 0;
	else if($ACTIVE != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid ACTIVE Value';
	} else
		$ACTIVE = 1;
	
	if(strtolower($USE_PROGRAM_GRADE) == 'yes')
		$USE_PROGRAM_GRADE = 1;
	else if(strtolower($USE_PROGRAM_GRADE) == 'no')
		$USE_PROGRAM_GRADE = 0;
	else if($USE_PROGRAM_GRADE != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid USE_PROGRAM_GRADE Value';
	} else
		$USE_PROGRAM_GRADE = 0;
		
	if($PK_CREDENTIAL_LEVEL != '') {
		$res_st = $db->Execute("select PK_CREDENTIAL_LEVEL from M_CREDENTIAL_LEVEL WHERE PK_CREDENTIAL_LEVEL = '$PK_CREDENTIAL_LEVEL' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid CREDENTIAL_LEVEL_ID Value - '.$PK_COURSE;
		}
	}
	
	if($CLOCK_CREDIT != '') {
		if($CLOCK_CREDIT != 1 && $CLOCK_CREDIT != 2 && $CLOCK_CREDIT != 3) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid CLOCK_CREDIT Value';
		}
	}
	
	if($DIPLOMA != '') {
		if($DIPLOMA != 1 && $DIPLOMA != 2 && $DIPLOMA != 3 && $DIPLOMA != 4) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid DIPLOMA Value';
		}
	}
	
	
	if($PK_ENROLLMENT_STATUS_SCALE_MASTER != '') {
		$res_st = $db->Execute("select PK_ENROLLMENT_STATUS_SCALE_MASTER from M_ENROLLMENT_STATUS_SCALE_MASTER WHERE PK_ENROLLMENT_STATUS_SCALE_MASTER = '$PK_ENROLLMENT_STATUS_SCALE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ENROLLMENT_STATUS_ID Value';
		}
	}
	
	if($PK_PROGRAM_GROUP != '') {
		$res_st = $db->Execute("select PK_PROGRAM_GROUP from M_PROGRAM_GROUP WHERE PK_PROGRAM_GROUP = '$PK_PROGRAM_GROUP' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid PROGRAM_GROUP_ID Value';
		}
	}
	
	if($PK_GRADE_SCALE_MASTER != '') {
		$res_st = $db->Execute("select PK_GRADE_SCALE_MASTER from S_GRADE_SCALE_MASTER WHERE PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid GRADE_SCALE_ID Value';
		}
	}
	
	if($PK_SPECIAL_PROGRAM_INDICATOR != '') {
		$res_st = $db->Execute("select PK_SPECIAL_PROGRAM_INDICATOR from M_SPECIAL_PROGRAM_INDICATOR WHERE PK_SPECIAL_PROGRAM_INDICATOR = '$PK_SPECIAL_PROGRAM_INDICATOR' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SPECIAL_PROGRAM_INDICATOR_ID Value';
		}
	}
	
	if($PK_TUITION_TYPE != '') {
		$res_st = $db->Execute("select PK_TUITION_TYPE from M_TUITION_TYPE WHERE PK_TUITION_TYPE = '$PK_TUITION_TYPE' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid TUITION_TYPE_ID Value';
		}
	}
	
	if(strtolower($GE_PROGRAM) == 'yes')
		$GE_PROGRAM = 1;
	else if(strtolower($GE_PROGRAM) == 'no')
		$GE_PROGRAM = 0;
	else if($GE_PROGRAM != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid GE_PROGRAM Value';
	}
		
	if(strtolower($MED_DEN_RESI) == 'yes')
		$MED_DEN_RESI = 1;
	else if(strtolower($MED_DEN_RESI) == 'no')
		$MED_DEN_RESI = 0;
	else if($MED_DEN_RESI != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid MED_DEN_RESI Value';
	}

	if(strtolower($FISAP) == 'yes')
		$FISAP = 1;
	else if(strtolower($FISAP) == 'no')
		$FISAP = 0;
	else if($FISAP != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid FISAP Value';
	}
		
	if(strtolower($_1098T) == 'yes')
		$_1098T = 1;
	else if(strtolower($_1098T) == 'no')
		$_1098T = 0;
	else if($_1098T != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid _1098T Value';
	}
		
	if(strtolower($_90_10_REPORT) == 'yes')
		$_90_10_REPORT = 1;
	else if(strtolower($_90_10_REPORT) == 'no')
		$_90_10_REPORT = 0;
	else if($_90_10_REPORT != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid _90_10_REPORT Value';
	}
	
	if(strtolower($ABHES) == 'yes')
		$ABHES = 1;
	else if(strtolower($ABHES) == 'no')
		$ABHES = 0;
	else if($ABHES != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid ABHES Value';
	}
	
	if($PK_ENROLLMENT_STATUS != '') {
		$res_st = $db->Execute("select PK_ENROLLMENT_STATUS from M_ENROLLMENT_STATUS WHERE PK_ENROLLMENT_STATUS = '$PK_ENROLLMENT_STATUS' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ANALYTICS_SETUP->ENROLLMENT_STATUS_ID Value';
		}
	}
	
	if($PK_EARNING_TYPE != '') {
		$res_st = $db->Execute("select PK_EARNING_TYPE from M_EARNING_TYPE WHERE PK_EARNING_TYPE = '$PK_EARNING_TYPE' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ANALYTICS_SETUP->EARNING_TYPE_ID Value';
		}
	}
	
	if(!empty($DATA->CAMPUS)){
		foreach($DATA->CAMPUS as $PK_CAMPUS) {
			$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid CAMPUS Value - '.$PK_CAMPUS;
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
					
				$data['MESSAGE'] .= 'Invalid PREREQUISITE_COURSE Value - '.$PK_COURSE;
			} else 
				$PK_PREREQUISITE_COURSE_ARR[] = $PK_COURSE;
		}
	}
	
	if(!empty($DATA->REQUIREMENTS)){
		foreach($DATA->REQUIREMENTS as $REQUIREMENTS) {
			$MANDATORY = $REQUIREMENTS->MANDATORY;
			
			if(strtolower($MANDATORY) == 'yes')
				$MANDATORY = 1;
			else if(strtolower($MANDATORY) == 'no')
				$MANDATORY = 0;
			else if($MANDATORY != '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid MANDATORY Value - '.$REQUIREMENTS->MANDATORY;
			}
			
		}
	}
	
	if(!empty($DATA->TUITION_FEE)){
		foreach($DATA->TUITION_FEE as $TUITION_FEE) {
			if($TUITION_FEE->FEE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing TUITION_FEE->FEE_ID Value';
			} else {
				$PK_AR_LEDGER_CODE = $TUITION_FEE->FEE_ID;
				$res_st = $db->Execute("select PK_AR_LEDGER_CODE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT='$PK_ACCOUNT'");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid TUITION_FEE->FEE_ID  Value - '.$PK_AR_LEDGER_CODE;
				}
			}
			
			if($TUITION_FEE->FEE_TYPE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing TUITION_FEE->FEE_TYPE_ID Value';
			} else {
				$PK_FEE_TYPE = $TUITION_FEE->FEE_TYPE_ID;
				$res_st = $db->Execute("select PK_FEE_TYPE FROM M_FEE_TYPE WHERE PK_FEE_TYPE = '$PK_FEE_TYPE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid TUITION_FEE->FEE_TYPE_ID  Value - '.$PK_FEE_TYPE;
				}
			}
			
			if($TUITION_FEE->DEPENDENT_STATUS_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing TUITION_FEE->DEPENDENT_STATUS_ID Value';
			} else {
				$PK_DEPENDENT_STATUS = $TUITION_FEE->DEPENDENT_STATUS_ID;
				$res_st = $db->Execute("select PK_DEPENDENT_STATUS FROM M_DEPENDENT_STATUS WHERE PK_DEPENDENT_STATUS = '$PK_DEPENDENT_STATUS' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid TUITION_FEE->DEPENDENT_STATUS_ID  Value - '.$PK_DEPENDENT_STATUS;
				}
			}
			
			if($TUITION_FEE->HOUSING_TYPE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing TUITION_FEE->HOUSING_TYPE_ID Value';
			} else {
				$PK_HOUSING_TYPE = $TUITION_FEE->HOUSING_TYPE_ID;
				$res_st = $db->Execute("select PK_HOUSING_TYPE FROM M_HOUSING_TYPE WHERE PK_HOUSING_TYPE = '$PK_HOUSING_TYPE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid TUITION_FEE->HOUSING_TYPE_ID  Value - '.$PK_HOUSING_TYPE;
				}
			}
			
			if($TUITION_FEE->GE_DISCLOSURE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing TUITION_FEE->GE_DISCLOSURE_ID Value';
			} else {
				$PK_GE_DISCLOSURE = $TUITION_FEE->GE_DISCLOSURE_ID;
				$res_st = $db->Execute("select PK_GE_DISCLOSURE FROM M_GE_DISCLOSURE WHERE PK_GE_DISCLOSURE = '$PK_GE_DISCLOSURE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid TUITION_FEE->GE_DISCLOSURE_ID  Value - '.$PK_GE_DISCLOSURE;
				}
			}
		}
	}
	
	if(!empty($DATA->PROGRAM_COURSE)){
		foreach($DATA->PROGRAM_COURSE as $PROGRAM_COURSE) {
			if($PROGRAM_COURSE->COURSE_CODE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing PROGRAM_COURSE->COURSE_CODE_ID Value';
			} else {
				$PK_COURSE = $PROGRAM_COURSE->COURSE_CODE_ID;
				$res_st = $db->Execute("select PK_COURSE FROM S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT='$PK_ACCOUNT'");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid PROGRAM_COURSE->COURSE_CODE_ID  Value - '.$PK_COURSE;
				}
			}
			
			if($PROGRAM_COURSE->COURSE_TYPE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing PROGRAM_COURSE->COURSE_TYPE_ID Value';
			} else {
				$COURSE_TYPE = $PROGRAM_COURSE->COURSE_TYPE_ID;
				if($COURSE_TYPE != 1 && $COURSE_TYPE != 2){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid PROGRAM_COURSE->COURSE_TYPE_ID  Value - '.$COURSE_TYPE;
				}
			}
			
			if(!empty($PROGRAM_COURSE->PREREQUISITE)) {
				foreach($PROGRAM_COURSE->PREREQUISITE as $PK_COURSE) {
					$res_st = $db->Execute("select PK_COURSE FROM S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT='$PK_ACCOUNT'");
					
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid PROGRAM_COURSE->PREREQUISITE Value - '.$PK_COURSE;
					}
				}
			}
			
			if(!empty($PROGRAM_COURSE->COREQUISITE)) {
				foreach($PROGRAM_COURSE->COREQUISITE as $PK_COURSE) {
					$res_st = $db->Execute("select PK_COURSE FROM S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT='$PK_ACCOUNT'");
					
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid PROGRAM_COURSE->COREQUISITE Value - '.$PK_COURSE;
					}
				}
			}
		}
	}
	
	if(!empty($DATA->FINANCIAL_PLAN)){
		foreach($DATA->FINANCIAL_PLAN as $FINANCIAL_PLAN) {
			if($FINANCIAL_PLAN->LEDGER_CODE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing FINANCIAL_PLAN->LEDGER_CODE_ID Value';
			} else {
				$PK_AR_LEDGER_CODE = $FINANCIAL_PLAN->LEDGER_CODE_ID;
				$res_st = $db->Execute("select PK_AR_LEDGER_CODE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT='$PK_ACCOUNT'");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid FINANCIAL_PLAN->LEDGER_CODE_ID  Value - '.$PK_AR_LEDGER_CODE;
				}
			}
			
			if($FINANCIAL_PLAN->DEPENDENT_STATUS_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing FINANCIAL_PLAN->DEPENDENT_STATUS_ID Value';
			} else {
				$PK_DEPENDENT_STATUS = $FINANCIAL_PLAN->DEPENDENT_STATUS_ID;
				$res_st = $db->Execute("select PK_DEPENDENT_STATUS FROM M_DEPENDENT_STATUS WHERE PK_DEPENDENT_STATUS = '$PK_DEPENDENT_STATUS' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid FINANCIAL_PLAN->DEPENDENT_STATUS_ID  Value - '.$PK_DEPENDENT_STATUS;
				}
			}
			
			if($FINANCIAL_PLAN->PAYMENT_FREQUENCY_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing FINANCIAL_PLAN->PAYMENT_FREQUENCY_ID Value';
			} else {
				$PK_PAYMENT_FREQUENCY = $FINANCIAL_PLAN->PAYMENT_FREQUENCY_ID;
				$res_st = $db->Execute("select PK_PAYMENT_FREQUENCY FROM M_PAYMENT_FREQUENCY WHERE PK_PAYMENT_FREQUENCY = '$PK_PAYMENT_FREQUENCY' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid FINANCIAL_PLAN->PAYMENT_FREQUENCY_ID  Value - '.$PK_PAYMENT_FREQUENCY;
				}
			}
		}
	}
	
	if(!empty($DATA->GRADE_BOOK)){
		foreach($DATA->GRADE_BOOK as $GRADE_BOOK) {
			if($GRADE_BOOK->GRADE_BOOK_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing GRADE_BOOK->GRADE_BOOK_ID Value';
			} else {
				$PK_GRADE_BOOK_CODE = $GRADE_BOOK->GRADE_BOOK_ID;
				$res_st = $db->Execute("select PK_GRADE_BOOK_CODE FROM M_GRADE_BOOK_CODE WHERE PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' AND PK_ACCOUNT='$PK_ACCOUNT'");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid GRADE_BOOK->GRADE_BOOK_ID  Value - '.$PK_GRADE_BOOK_CODE;
				}
			}
			
			if($GRADE_BOOK->GRADE_BOOK_TYPE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing GRADE_BOOK->GRADE_BOOK_TYPE_ID Value';
			} else {
				$PK_GRADE_BOOK_TYPE = $GRADE_BOOK->GRADE_BOOK_TYPE_ID;
				$res_st = $db->Execute("select PK_GRADE_BOOK_TYPE FROM M_GRADE_BOOK_TYPE WHERE PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND PK_ACCOUNT='$PK_ACCOUNT'");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid GRADE_BOOK->GRADE_BOOK_TYPE_ID  Value - '.$PK_GRADE_BOOK_TYPE;
				}
			}
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$CAMPUS_PROGRAM['USE_PROGRAM_GRADE'] 				 = $USE_PROGRAM_GRADE;
		$CAMPUS_PROGRAM['CODE'] 							 = $CODE;
		$CAMPUS_PROGRAM['DESCRIPTION'] 						 = $DESCRIPTION;
		$CAMPUS_PROGRAM['UNITS'] 							 = $UNITS;
		$CAMPUS_PROGRAM['FA_UNITS'] 						 = $FA_UNITS;
		$CAMPUS_PROGRAM['UNIT_COST'] 						 = $UNIT_COST;
		$CAMPUS_PROGRAM['CAPACITY'] 						 = $CAPACITY;
		$CAMPUS_PROGRAM['DIPLOMA'] 							 = $DIPLOMA;
		$CAMPUS_PROGRAM['PK_PROGRAM_GROUP'] 				 = $PK_PROGRAM_GROUP;
		$CAMPUS_PROGRAM['PK_TUITION_TYPE'] 				 	 = $PK_TUITION_TYPE;
		$CAMPUS_PROGRAM['CLOCK_CREDIT'] 					 = $CLOCK_CREDIT;
		$CAMPUS_PROGRAM['PK_GRADE_SCALE_MASTER'] 			 = $PK_GRADE_SCALE_MASTER;
		$CAMPUS_PROGRAM['MONTHS'] 							 = $MONTHS;
		$CAMPUS_PROGRAM['WEEKS'] 							 = $WEEKS;
		$CAMPUS_PROGRAM['HOURS'] 							 = $HOURS;
		$CAMPUS_PROGRAM['DAILY_HOURS_SUNDAY'] 				 = $DAILY_HOURS_SUNDAY;
		$CAMPUS_PROGRAM['DAILY_HOURS_MONDAY'] 				 = $DAILY_HOURS_MONDAY;
		$CAMPUS_PROGRAM['DAILY_HOURS_TUESDAY'] 				 = $DAILY_HOURS_TUESDAY;
		$CAMPUS_PROGRAM['DAILY_HOURS_WEDNESDAY'] 			 = $DAILY_HOURS_WEDNESDAY;
		$CAMPUS_PROGRAM['DAILY_HOURS_THURSDAY'] 			 = $DAILY_HOURS_THURSDAY;
		$CAMPUS_PROGRAM['DAILY_HOURS_FRIDAY'] 				 = $DAILY_HOURS_FRIDAY;
		$CAMPUS_PROGRAM['DAILY_HOURS_SATURDAY'] 			 = $DAILY_HOURS_SATURDAY;
		$CAMPUS_PROGRAM['NOTES'] 							 = $NOTES;
		$CAMPUS_PROGRAM['CIP_CODE'] 						 = $CIP_CODE;
		$CAMPUS_PROGRAM['CIP_DEFINITION'] 					 = $CIP_DEFINITION;
		$CAMPUS_PROGRAM['PK_SPECIAL_PROGRAM_INDICATOR'] 	 = $PK_SPECIAL_PROGRAM_INDICATOR;
		$CAMPUS_PROGRAM['PK_CREDENTIAL_LEVEL']  			 = $PK_CREDENTIAL_LEVEL;
		$CAMPUS_PROGRAM['PK_ENROLLMENT_STATUS_SCALE_MASTER'] = $PK_ENROLLMENT_STATUS_SCALE_MASTER;
		$CAMPUS_PROGRAM['PK_ACCOUNT']  						 = $PK_ACCOUNT;
		$CAMPUS_PROGRAM['CREATED_ON']  						 = date("Y-m-d H:i");
		db_perform('M_CAMPUS_PROGRAM', $CAMPUS_PROGRAM, 'insert');
		$PK_CAMPUS_PROGRAM = $db->insert_ID();

		$PROGRAM_ANALYTICS_SETUP['GE_PROGRAM'] 			 = $GE_PROGRAM;
		$PROGRAM_ANALYTICS_SETUP['MED_DEN_RESI'] 		 = $MED_DEN_RESI;
		$PROGRAM_ANALYTICS_SETUP['FISAP'] 				 = $FISAP;
		$PROGRAM_ANALYTICS_SETUP['_1098T'] 				 = $_1098T;
		$PROGRAM_ANALYTICS_SETUP['_90_10_REPORT'] 		 = $_90_10_REPORT;
		$PROGRAM_ANALYTICS_SETUP['ABHES'] 				 = $ABHES;
		$PROGRAM_ANALYTICS_SETUP['TERM_DAYS'] 			 = $TERM_DAYS;
		$PROGRAM_ANALYTICS_SETUP['TERM_MONTHS'] 		 = $TERM_MONTHS;
		$PROGRAM_ANALYTICS_SETUP['PK_ENROLLMENT_STATUS'] = $PK_ENROLLMENT_STATUS;
		$PROGRAM_ANALYTICS_SETUP['PK_EARNING_TYPE'] 	 = $PK_EARNING_TYPE;
		$PROGRAM_ANALYTICS_SETUP['PK_CAMPUS_PROGRAM'] 	 = $PK_CAMPUS_PROGRAM;
		$PROGRAM_ANALYTICS_SETUP['PK_ACCOUNT']  		 = $PK_ACCOUNT;
		$PROGRAM_ANALYTICS_SETUP['CREATED_ON']  		 = date("Y-m-d H:i");
		db_perform('M_CAMPUS_PROGRAM_ANALYTICS_SETUP', $PROGRAM_ANALYTICS_SETUP, 'insert');
		
		if(!empty($PK_CAMPUS_ARR)){
			foreach($PK_CAMPUS_ARR as $PK_CAMPUS) {
				$CAMPUS_PROGRAM_CAMPUS['PK_CAMPUS_PROGRAM'] = $PK_CAMPUS_PROGRAM;
				$CAMPUS_PROGRAM_CAMPUS['PK_ACCOUNT']  		= $PK_ACCOUNT;
				$CAMPUS_PROGRAM_CAMPUS['PK_CAMPUS'] 		= $PK_CAMPUS;
				$CAMPUS_PROGRAM_CAMPUS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('M_CAMPUS_PROGRAM_CAMPUS', $CAMPUS_PROGRAM_CAMPUS, 'insert');
			}
		}
		
		if(!empty($DATA->REQUIREMENTS)){
			foreach($DATA->REQUIREMENTS as $REQUIREMENTS) {
				$MANDATORY = $REQUIREMENTS->MANDATORY;
			
				if(strtolower($MANDATORY) == 'yes')
					$MANDATORY = 1;
				else
					$MANDATORY = 0;
					
				$PROGRAM_REQUIREMENT['REQUIREMENT']  		= $REQUIREMENTS->TEXT;
				$PROGRAM_REQUIREMENT['MANDATORY'] 	 		= $MANDATORY;
				$PROGRAM_REQUIREMENT['PK_CAMPUS_PROGRAM']  	= $PK_CAMPUS_PROGRAM;
				$PROGRAM_REQUIREMENT['PK_ACCOUNT']  		= $PK_ACCOUNT;
				db_perform('M_CAMPUS_PROGRAM_REQUIREMENT', $PROGRAM_REQUIREMENT, 'insert');
			}
		}
		
		if(!empty($DATA->AY_INFO)){
			foreach($DATA->AY_INFO as $AY_INFO) {
				
				$PROGRAM_AY['ACADEMIC_YEAR']  	 = $AY_INFO->ACADEMIC_YEAR;
				$PROGRAM_AY['PERIOD']  			 = $AY_INFO->PERIOD;
				$PROGRAM_AY['MONTHS']  			 = $AY_INFO->MONTHS;
				$PROGRAM_AY['WEEKS'] 			 = $AY_INFO->WEEKS;
				$PROGRAM_AY['UNITS']  			 = $AY_INFO->UNITS;
				$PROGRAM_AY['HOUR']  			 = $AY_INFO->HOUR;
				$PROGRAM_AY['FA_UNITS'] 		 = $AY_INFO->FA_UNITS;
				$PROGRAM_AY['PK_CAMPUS_PROGRAM'] = $PK_CAMPUS_PROGRAM;
				$PROGRAM_AY['PK_ACCOUNT']  		 = $PK_ACCOUNT;
				$PROGRAM_AY['CREATED_ON']  		 = date("Y-m-d H:i");
				db_perform('M_CAMPUS_PROGRAM_AY', $PROGRAM_AY, 'insert');
			}
		}
		
		if(!empty($DATA->TUITION_FEE)){
			foreach($DATA->TUITION_FEE as $TUITION_FEE) {
				$PROGRAM_FEE['PK_AR_LEDGER_CODE']  	= trim($TUITION_FEE->FEE_ID);
				$PROGRAM_FEE['PK_FEE_TYPE']  		= trim($TUITION_FEE->FEE_TYPE_ID);
				$PROGRAM_FEE['DESCRIPTION']  		= trim($TUITION_FEE->DESCRIPTION);
				$PROGRAM_FEE['AMOUNT'] 				= trim($TUITION_FEE->AMOUNT);
				$PROGRAM_FEE['AY']  				= trim($TUITION_FEE->AY);
				$PROGRAM_FEE['AP']  				= trim($TUITION_FEE->AP);
				$PROGRAM_FEE['PK_DEPENDENT_STATUS'] = trim($TUITION_FEE->DEPENDENT_STATUS_ID);
				$PROGRAM_FEE['PK_HOUSING_TYPE'] 	= trim($TUITION_FEE->HOUSING_TYPE_ID);
				$PROGRAM_FEE['PK_GE_DISCLOSURE'] 	= trim($TUITION_FEE->GE_DISCLOSURE_ID);
				$PROGRAM_FEE['PK_CAMPUS_PROGRAM'] 	= $PK_CAMPUS_PROGRAM;
				$PROGRAM_FEE['PK_ACCOUNT']  		= $PK_ACCOUNT;
				$PROGRAM_FEE['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('M_CAMPUS_PROGRAM_FEE', $PROGRAM_FEE, 'insert');
			}
		}
		
		if(!empty($DATA->PROGRAM_COURSE)){
			foreach($DATA->PROGRAM_COURSE as $PROGRAM_COURSE) {
			
				$PK_PREREQUISITE = '';
				if(!empty($PROGRAM_COURSE->PREREQUISITE)) {
					foreach($PROGRAM_COURSE->PREREQUISITE as $PK_COURSE) {
						if($PK_PREREQUISITE != '')
							$PK_PREREQUISITE .= ',';
						$PK_PREREQUISITE .= $PK_COURSE;
					}
				}
				
				$PK_COREQUISITE = '';
				if(!empty($PROGRAM_COURSE->COREQUISITE)) {
					foreach($PROGRAM_COURSE->COREQUISITE as $PK_COURSE) {
						if($PK_COREQUISITE != '')
							$PK_COREQUISITE .= ',';
						$PK_COREQUISITE .= $PK_COURSE;
					}
				}
				
				$PROGRAM_COURSE_ARR = array();
				$PROGRAM_COURSE_ARR['PK_COURSE']  		 = trim($PROGRAM_COURSE->COURSE_CODE_ID);
				$PROGRAM_COURSE_ARR['COURSE_TYPE']  	 = trim($PROGRAM_COURSE->COURSE_TYPE_ID);
				$PROGRAM_COURSE_ARR['COURSE_ORDER']  	 = trim($PROGRAM_COURSE->ORDER);
				$PROGRAM_COURSE_ARR['PK_COREQUISITE']    = $PK_COREQUISITE;
				$PROGRAM_COURSE_ARR['PK_PREREQUISITE']   = $PK_PREREQUISITE;
				$PROGRAM_COURSE_ARR['PK_CAMPUS_PROGRAM'] = $PK_CAMPUS_PROGRAM;
				$PROGRAM_COURSE_ARR['PK_ACCOUNT']  		 = $PK_ACCOUNT;
				$PROGRAM_COURSE_ARR['CREATED_ON']  		 = date("Y-m-d H:i");
				db_perform('M_CAMPUS_PROGRAM_COURSE', $PROGRAM_COURSE_ARR, 'insert');
			}
		}
		
		if(!empty($DATA->FINANCIAL_PLAN)){
			foreach($DATA->FINANCIAL_PLAN as $FINANCIAL_PLAN) {
				$PROGRAM_AWARD['DAYS_FROM_START']  		= trim($FINANCIAL_PLAN->DAYS_FROM_START);
				$PROGRAM_AWARD['PK_AR_LEDGER_CODE']  	= trim($FINANCIAL_PLAN->LEDGER_CODE_ID);
				$PROGRAM_AWARD['ACADEMIC_YEAR']  		= trim($FINANCIAL_PLAN->ACADEMIC_YEAR);
				$PROGRAM_AWARD['ACADEMIC_PERIOD']  		= trim($FINANCIAL_PLAN->ACADEMIC_PERIOD);
				$PROGRAM_AWARD['GROSS_AMOUNT']  		= trim($FINANCIAL_PLAN->GROSS_AMOUNT);
				$PROGRAM_AWARD['FEE_AMOUNT']  			= trim($FINANCIAL_PLAN->FEE_AMOUNT);
				//$PROGRAM_AWARD['NET_AMOUNT']  			= $PROGRAM_AWARD['GROSS_AMOUNT'] - $PROGRAM_AWARD['FEE_AMOUNT'];
				$PROGRAM_AWARD['NET_AMOUNT']  			= trim($FINANCIAL_PLAN->NET_AMOUNT);
				$PROGRAM_AWARD['PK_DEPENDENT_STATUS']  	= trim($FINANCIAL_PLAN->DEPENDENT_STATUS_ID);
				$PROGRAM_AWARD['HOURS_REQUIRED']  		= trim($FINANCIAL_PLAN->HOURS_REQUIRED);
				$PROGRAM_AWARD['NO_OF_PAYMENTS']  		= trim($FINANCIAL_PLAN->NO_OF_PAYMENTS);
				$PROGRAM_AWARD['PK_PAYMENT_FREQUENCY']  = trim($FINANCIAL_PLAN->PAYMENT_FREQUENCY_ID);
				
				$PROGRAM_AWARD['PK_CAMPUS_PROGRAM']  = $PK_CAMPUS_PROGRAM;
				$PROGRAM_AWARD['PK_ACCOUNT']  		 = $PK_ACCOUNT;
				$PROGRAM_AWARD['CREATED_ON']  		 = date("Y-m-d H:i");
				db_perform('M_CAMPUS_PROGRAM_AWARD', $PROGRAM_AWARD, 'insert');
			}
		}
		
		if(!empty($DATA->GRADE_BOOK)){
			foreach($DATA->GRADE_BOOK as $GRADE_BOOK) {
				$PROGRAM_GRADE['PK_GRADE_BOOK_CODE'] 	= trim($GRADE_BOOK->GRADE_BOOK_ID);
				$PROGRAM_GRADE['DESCRIPTION'] 			= trim($GRADE_BOOK->DESCRIPTION);
				$PROGRAM_GRADE['PK_GRADE_BOOK_TYPE'] 	= trim($GRADE_BOOK->GRADE_BOOK_TYPE_ID);
				$PROGRAM_GRADE['SESSION'] 				= trim($GRADE_BOOK->SESSION);
				$PROGRAM_GRADE['HOUR'] 					= trim($GRADE_BOOK->HOUR);
				$PROGRAM_GRADE['POINTS'] 				= trim($GRADE_BOOK->POINTS);
				$PROGRAM_GRADE['DATE'] 					= date("Y-m-d");
				$PROGRAM_GRADE['PK_CAMPUS_PROGRAM']  	= $PK_CAMPUS_PROGRAM;
				$PROGRAM_GRADE['PK_ACCOUNT']  			= $PK_ACCOUNT;
				$PROGRAM_GRADE['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_PROGRAM_GRADE_BOOK', $PROGRAM_GRADE, 'insert');
				$PK_PROGRAM_GRADE_BOOK = $db->insert_ID;
			}
		}
		
		$data['MESSAGE'] = 'Program Created';
		$data['INTERNAL_ID'] = $PK_CAMPUS_PROGRAM;
		
	}
}

$data = json_encode($data);
echo $data;