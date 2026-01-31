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

	$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.PK_CREDENTIAL_LEVEL, CONCAT(M_CREDENTIAL_LEVEL.CODE,' - ',M_CREDENTIAL_LEVEL.DESCRIPTION) as CREDENTIAL_LEVEL, IF(CLOCK_CREDIT = 1,'Clock', IF(CLOCK_CREDIT = 2,'Credit',IF(CLOCK_CREDIT = 3,'NA','')) ) as CLOCK_CREDIT_1, CLOCK_CREDIT, IF(DIPLOMA = 1,'Diploma', IF(DIPLOMA = 2,'Certificate',IF(DIPLOMA = 3,'NA','')) ) as DIPLOMA_1, DIPLOMA, M_CAMPUS_PROGRAM.CODE, M_CAMPUS_PROGRAM.DESCRIPTION, IF(USE_PROGRAM_GRADE = 1,'Yes','No') as USE_PROGRAM_GRADE, M_CAMPUS_PROGRAM.CAPACITY, ENROLLMENT_STATUS, PROGRAM_GROUP, M_CAMPUS_PROGRAM.PK_PROGRAM_GROUP, GRADE_SCALE,  M_CAMPUS_PROGRAM.PK_GRADE_SCALE_MASTER, IF(M_CAMPUS_PROGRAM.ACTIVE = 1,'Yes','No') as ACTIVE , MONTHS,  WEEKS, HOURS, UNITS, FA_UNITS, UNIT_COST, TUITION_TYPE, M_TUITION_TYPE.PK_TUITION_TYPE, DAILY_HOURS_SUNDAY, DAILY_HOURS_MONDAY, DAILY_HOURS_TUESDAY, DAILY_HOURS_WEDNESDAY, DAILY_HOURS_THURSDAY, DAILY_HOURS_FRIDAY, DAILY_HOURS_SATURDAY, NOTES , CONCAT(M_SPECIAL_PROGRAM_INDICATOR.CODE,' - ',M_SPECIAL_PROGRAM_INDICATOR.DESCRIPTION) as SPECIAL_PROGRAM_INDICATOR, M_CAMPUS_PROGRAM.PK_SPECIAL_PROGRAM_INDICATOR, M_CAMPUS_PROGRAM.PK_ENROLLMENT_STATUS_SCALE_MASTER, CIP_CODE, CIP_DEFINITION      
	FROM 
	M_CAMPUS_PROGRAM 
	LEFT JOIN M_PROGRAM_GROUP ON M_PROGRAM_GROUP.PK_PROGRAM_GROUP = M_CAMPUS_PROGRAM.PK_PROGRAM_GROUP 
	LEFT JOIN M_SPECIAL_PROGRAM_INDICATOR ON M_SPECIAL_PROGRAM_INDICATOR.PK_SPECIAL_PROGRAM_INDICATOR = M_CAMPUS_PROGRAM.PK_SPECIAL_PROGRAM_INDICATOR  
	LEFT JOIN M_TUITION_TYPE ON M_TUITION_TYPE.PK_TUITION_TYPE = M_CAMPUS_PROGRAM.PK_TUITION_TYPE
	LEFT JOIN S_GRADE_SCALE_MASTER ON S_GRADE_SCALE_MASTER.PK_GRADE_SCALE_MASTER = M_CAMPUS_PROGRAM.PK_GRADE_SCALE_MASTER 
	LEFT JOIN M_CREDENTIAL_LEVEL ON M_CREDENTIAL_LEVEL.PK_CREDENTIAL_LEVEL = M_CAMPUS_PROGRAM.PK_CREDENTIAL_LEVEL 
	LEFT JOIN M_ENROLLMENT_STATUS_SCALE_MASTER ON M_ENROLLMENT_STATUS_SCALE_MASTER.PK_ENROLLMENT_STATUS_SCALE_MASTER = M_CAMPUS_PROGRAM.PK_ENROLLMENT_STATUS_SCALE_MASTER 
	WHERE M_CAMPUS_PROGRAM.PK_ACCOUNT = '$PK_ACCOUNT' ");
	
	$i = 0;
	while (!$res->EOF) { 
		$PK_CAMPUS_PROGRAM = $res->fields['PK_CAMPUS_PROGRAM'];
		$data['PROGRAM'][$i]['ID'] 								= $PK_CAMPUS_PROGRAM;
		$data['PROGRAM'][$i]['CODE'] 							= $res->fields['CODE'];
		$data['PROGRAM'][$i]['DESCRIPTION'] 					= $res->fields['DESCRIPTION'];
		$data['PROGRAM'][$i]['USE_PROGRAM_GRADE'] 				= $res->fields['USE_PROGRAM_GRADE'];
		$data['PROGRAM'][$i]['CREDENTIAL_LEVEL_ID'] 			= $res->fields['PK_CREDENTIAL_LEVEL'];
		$data['PROGRAM'][$i]['CREDENTIAL_LEVEL'] 				= $res->fields['CREDENTIAL_LEVEL'];
		$data['PROGRAM'][$i]['CLOCK_CREDIT_ID'] 				= $res->fields['CLOCK_CREDIT'];
		$data['PROGRAM'][$i]['CLOCK_CREDIT'] 					= $res->fields['CLOCK_CREDIT_1'];
		$data['PROGRAM'][$i]['DIPLOMA_ID'] 						= $res->fields['DIPLOMA'];
		$data['PROGRAM'][$i]['DIPLOMA'] 						= $res->fields['DIPLOMA_1'];
		$data['PROGRAM'][$i]['CAPACITY'] 						= $res->fields['CAPACITY'];
		$data['PROGRAM'][$i]['ENROLLMENT_STATUS'] 				= $res->fields['ENROLLMENT_STATUS'];
		$data['PROGRAM'][$i]['ENROLLMENT_STATUS_ID'] 			= $res->fields['PK_ENROLLMENT_STATUS_SCALE_MASTER'];
		$data['PROGRAM'][$i]['PROGRAM_GROUP'] 					= $res->fields['PROGRAM_GROUP'];
		$data['PROGRAM'][$i]['PROGRAM_GROUP_ID'] 				= $res->fields['PK_PROGRAM_GROUP'];
		$data['PROGRAM'][$i]['GRADE_SCALE'] 					= $res->fields['GRADE_SCALE'];
		$data['PROGRAM'][$i]['GRADE_SCALE_ID'] 					= $res->fields['PK_GRADE_SCALE_MASTER'];
		$data['PROGRAM'][$i]['SPECIAL_PROGRAM_INDICATOR'] 		= $res->fields['SPECIAL_PROGRAM_INDICATOR'];
		$data['PROGRAM'][$i]['SPECIAL_PROGRAM_INDICATOR_ID'] 	= $res->fields['PK_SPECIAL_PROGRAM_INDICATOR'];
		$data['PROGRAM'][$i]['CIP_CODE'] 						= $res->fields['CIP_CODE'];
		$data['PROGRAM'][$i]['CIP_DEFINITION'] 					= $res->fields['CIP_DEFINITION'];
		$data['PROGRAM'][$i]['NOTES'] 							= $res->fields['NOTES'];
		$data['PROGRAM'][$i]['UNITS'] 							= $res->fields['UNITS'];
		$data['PROGRAM'][$i]['FA_UNITS'] 						= $res->fields['FA_UNITS'];
		$data['PROGRAM'][$i]['UNIT_COST'] 						= $res->fields['UNIT_COST'];
		$data['PROGRAM'][$i]['TUITION_TYPE'] 					= $res->fields['TUITION_TYPE'];
		$data['PROGRAM'][$i]['TUITION_TYPE_ID'] 				= $res->fields['PK_TUITION_TYPE'];
		$data['PROGRAM'][$i]['ACTIVE'] 							= $res->fields['ACTIVE'];
		
		$data['PROGRAM'][$i]['PROGRAM_LENGTH']['MONTHS']		= $res->fields['MONTHS'];
		$data['PROGRAM'][$i]['PROGRAM_LENGTH']['WEEKS']			= $res->fields['WEEKS'];
		$data['PROGRAM'][$i]['PROGRAM_LENGTH']['HOURS']			= $res->fields['HOURS'];
		
		$data['PROGRAM'][$i]['DAILY_HOURS']['SUNDAY']		= $res->fields['DAILY_HOURS_SUNDAY'];
		$data['PROGRAM'][$i]['DAILY_HOURS']['MONDAY']		= $res->fields['DAILY_HOURS_MONDAY'];
		$data['PROGRAM'][$i]['DAILY_HOURS']['TUESDAY']		= $res->fields['DAILY_HOURS_TUESDAY'];
		$data['PROGRAM'][$i]['DAILY_HOURS']['WEDNESDAY']	= $res->fields['DAILY_HOURS_WEDNESDAY'];
		$data['PROGRAM'][$i]['DAILY_HOURS']['THURSDAY']		= $res->fields['DAILY_HOURS_THURSDAY'];
		$data['PROGRAM'][$i]['DAILY_HOURS']['FRIDAY']		= $res->fields['DAILY_HOURS_FRIDAY'];
		$data['PROGRAM'][$i]['DAILY_HOURS']['SATURDAY']		= $res->fields['DAILY_HOURS_SATURDAY'];
		
		$j = 0;
		$res_det = $db->Execute("select OFFICIAL_CAMPUS_NAME,S_CAMPUS.PK_CAMPUS FROM S_CAMPUS,M_CAMPUS_PROGRAM_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = M_CAMPUS_PROGRAM_CAMPUS.PK_CAMPUS AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND M_CAMPUS_PROGRAM_CAMPUS.PK_ACCOUNT = '$PK_ACCOUNT'"); 
		while (!$res_det->EOF) { 
			$data['PROGRAM'][$i]['CAMPUS'][$j]['NAME']			= $res_det->fields['OFFICIAL_CAMPUS_NAME'];
			$data['PROGRAM'][$i]['CAMPUS'][$j]['CAMPUS_ID']	= $res_det->fields['PK_CAMPUS'];
			
			$j++;
			$res_det->MoveNext();
		}

		$j = 0;
		$res_det = $db->Execute("select REQUIREMENT, IF(MANDATORY = 1,'Yes', 'No') as MANDATORY, IF(ACTIVE = 1,'Yes','No') as ACTIVE from M_CAMPUS_PROGRAM_REQUIREMENT WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		while (!$res_det->EOF) { 
			$data['PROGRAM'][$i]['REQUIREMENTS'][$j]['TEXT']		= $res_det->fields['REQUIREMENT'];
			$data['PROGRAM'][$i]['REQUIREMENTS'][$j]['MANDATORY']	= $res_det->fields['MANDATORY'];
			$data['PROGRAM'][$i]['REQUIREMENTS'][$j]['ACTIVE']		= $res_det->fields['ACTIVE'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select ACADEMIC_YEAR, PERIOD, MONTHS, WEEKS, UNITS, HOUR, FA_UNITS, IF(ACTIVE = 1,'Yes','No') as ACTIVE  from M_CAMPUS_PROGRAM_AY WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		while (!$res_det->EOF) { 
			$data['PROGRAM'][$i]['AY_INFO'][$j]['ACADEMIC_YEAR']	= $res_det->fields['ACADEMIC_YEAR'];
			$data['PROGRAM'][$i]['AY_INFO'][$j]['PERIOD']			= $res_det->fields['PERIOD'];
			$data['PROGRAM'][$i]['AY_INFO'][$j]['MONTHS']			= $res_det->fields['MONTHS'];
			$data['PROGRAM'][$i]['AY_INFO'][$j]['WEEKS']			= $res_det->fields['WEEKS'];
			$data['PROGRAM'][$i]['AY_INFO'][$j]['HOUR']				= $res_det->fields['HOUR'];
			$data['PROGRAM'][$i]['AY_INFO'][$j]['FA_UNITS']			= $res_det->fields['FA_UNITS'];
			$data['PROGRAM'][$i]['AY_INFO'][$j]['ACTIVE']			= $res_det->fields['ACTIVE'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select M_CAMPUS_PROGRAM_FEE.PK_AR_LEDGER_CODE, M_AR_LEDGER_CODE.CODE AS LEDGER_CODE, FEE_TYPE, M_CAMPUS_PROGRAM_FEE.PK_FEE_TYPE, M_CAMPUS_PROGRAM_FEE.PK_DEPENDENT_STATUS, CONCAT(M_DEPENDENT_STATUS.CODE,' - ',M_DEPENDENT_STATUS.DESCRIPTION) AS DEPENDENT_STATUS , CONCAT(M_HOUSING_TYPE.CODE,' - ',M_HOUSING_TYPE.DESCRIPTION) AS HOUSING_TYPE, M_CAMPUS_PROGRAM_FEE.PK_GE_DISCLOSURE, GE_DISCLOSURE, M_CAMPUS_PROGRAM_FEE.DESCRIPTION, AMOUNT, AY, AP, IF(M_CAMPUS_PROGRAM_FEE.ACTIVE = 1, 'Yes', 'No') as ACTIVE, M_CAMPUS_PROGRAM_FEE.PK_HOUSING_TYPE   
		from 
		M_CAMPUS_PROGRAM_FEE 
		LEFT JOIN M_GE_DISCLOSURE ON M_GE_DISCLOSURE.PK_GE_DISCLOSURE = M_CAMPUS_PROGRAM_FEE.PK_GE_DISCLOSURE 
		LEFT JOIN M_HOUSING_TYPE ON M_HOUSING_TYPE.PK_HOUSING_TYPE = M_CAMPUS_PROGRAM_FEE.PK_HOUSING_TYPE 
		LEFT JOIN M_DEPENDENT_STATUS ON M_DEPENDENT_STATUS.PK_DEPENDENT_STATUS = M_CAMPUS_PROGRAM_FEE.PK_DEPENDENT_STATUS 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = M_CAMPUS_PROGRAM_FEE.PK_AR_LEDGER_CODE 
		LEFT JOIN M_FEE_TYPE ON M_FEE_TYPE.PK_FEE_TYPE = M_CAMPUS_PROGRAM_FEE.PK_FEE_TYPE 
		WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		while (!$res_det->EOF) { 
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['FEE']					= $res_det->fields['LEDGER_CODE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['FEE_ID']				= $res_det->fields['PK_AR_LEDGER_CODE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['FEE_TYPE']				= $res_det->fields['FEE_TYPE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['FEE_TYPE_ID']			= $res_det->fields['PK_FEE_TYPE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['DESCRIPTION']			= $res_det->fields['DESCRIPTION'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['AMOUNT']				= $res_det->fields['AMOUNT'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['AY']					= $res_det->fields['AY'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['AP']					= $res_det->fields['AP'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['DEPENDENT_STATUS']		= $res_det->fields['DEPENDENT_STATUS'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['DEPENDENT_STATUS_ID']	= $res_det->fields['PK_DEPENDENT_STATUS'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['HOUSING_TYPE']			= $res_det->fields['HOUSING_TYPE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['HOUSING_TYPE_ID']		= $res_det->fields['PK_HOUSING_TYPE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['GE_DISCLOSURE']		= $res_det->fields['GE_DISCLOSURE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['GE_DISCLOSURE_ID']		= $res_det->fields['PK_GE_DISCLOSURE'];
			$data['PROGRAM'][$i]['TUITION_FEE'][$j]['ACTIVE']				= $res_det->fields['ACTIVE'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select M_CAMPUS_PROGRAM_COURSE.PK_COURSE, COURSE_CODE, PK_COREQUISITE, PK_PREREQUISITE, COURSE_ORDER, COURSE_TYPE, IF(COURSE_TYPE = 1, 'Required',IF(COURSE_TYPE = 2, 'Elective','')) AS COURSE_TYPE_TEXT, IF(M_CAMPUS_PROGRAM_COURSE.ACTIVE = 1, 'Yes', 'No') as ACTIVE FROM M_CAMPUS_PROGRAM_COURSE 
		LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = M_CAMPUS_PROGRAM_COURSE.PK_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		while (!$res_det->EOF) { 
			$PK_COREQUISITE  = $res_det->fields['PK_COREQUISITE'];
			$PK_PREREQUISITE = $res_det->fields['PK_PREREQUISITE'];
			
			$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['COURSE_CODE']		= $res_det->fields['COURSE_CODE'];
			$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['COURSE_CODE_ID']	= $res_det->fields['PK_COURSE'];
			$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['ORDER']				= $res_det->fields['COURSE_ORDER'];
			$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['COURSE_TYPE_ID']	= $res_det->fields['COURSE_TYPE'];
			$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['COURSE_TYPE']		= $res_det->fields['COURSE_TYPE_TEXT'];
			$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['ACTIVE']			= $res_det->fields['ACTIVE'];
			
			$k = 0;
			$res_det1 = $db->Execute("select COURSE_CODE FROM S_COURSE WHERE PK_COURSE IN ($PK_PREREQUISITE) AND PK_ACCOUNT = '$PK_ACCOUNT'"); 
			while (!$res_det1->EOF) { 
				$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['PREREQUISITE'][$k] = $res_det1->fields['COURSE_CODE'];
				
				$k++;
				$res_det1->MoveNext();
			}
			
			$k = 0;
			$res_det1 = $db->Execute("select COURSE_CODE FROM S_COURSE WHERE PK_COURSE IN ($PK_COREQUISITE) AND PK_ACCOUNT = '$PK_ACCOUNT'"); 
			while (!$res_det1->EOF) { 
				$data['PROGRAM'][$i]['PROGRAM_COURSE'][$j]['COREQUISITE'][$k] = $res_det1->fields['COURSE_CODE'];
				
				$k++;
				$res_det1->MoveNext();
			}
		
			$j++;
			$res_det->MoveNext();
		}
		
		$res_det = $db->Execute("SELECT IF(GE_PROGRAM = 1, 'Yes', 'No') as GE_PROGRAM, IF(MED_DEN_RESI = 1, 'Yes', 'No') as MED_DEN_RESI, IF(FISAP = 1, 'Yes', 'No') as FISAP, IF(_1098T = 1, 'Yes', 'No') as _1098T, IF(_90_10_REPORT = 1, 'Yes', 'No') as _90_10_REPORT, IF(ABHES = 1, 'Yes', 'No') as ABHES, TERM_DAYS, TERM_MONTHS, M_CAMPUS_PROGRAM_ANALYTICS_SETUP.PK_ENROLLMENT_STATUS, CONCAT(M_ENROLLMENT_STATUS.CODE,' - ',M_ENROLLMENT_STATUS.DESCRIPTION) AS ENROLLMENT_STATUS, EARNING_TYPE, M_EARNING_TYPE.PK_EARNING_TYPE 
		FROM 
		M_CAMPUS_PROGRAM_ANALYTICS_SETUP 
		LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = M_CAMPUS_PROGRAM_ANALYTICS_SETUP.PK_ENROLLMENT_STATUS 
		LEFT JOIN M_EARNING_TYPE ON M_EARNING_TYPE.PK_EARNING_TYPE = M_CAMPUS_PROGRAM_ANALYTICS_SETUP.PK_EARNING_TYPE 
		WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['GE_PROGRAM']			= $res_det->fields['GE_PROGRAM'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['MED_DEN_RESI']			= $res_det->fields['MED_DEN_RESI'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['FISAP']				= $res_det->fields['FISAP'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['_1098T']				= $res_det->fields['_1098T'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['_90_10_REPORT']		= $res_det->fields['_90_10_REPORT'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['ABHES']				= $res_det->fields['ABHES'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['TERM_DAYS']			= $res_det->fields['TERM_DAYS'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['TERM_MONTHS']			= $res_det->fields['TERM_MONTHS'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['ENROLLMENT_STATUS']	= $res_det->fields['ENROLLMENT_STATUS'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['ENROLLMENT_STATUS_ID']	= $res_det->fields['PK_ENROLLMENT_STATUS'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['EARNING_TYPE']			= $res_det->fields['EARNING_TYPE'];
		$data['PROGRAM'][$i]['ANALYTICS_SETUP']['EARNING_TYPE_ID']		= $res_det->fields['PK_EARNING_TYPE'];
		

		$j = 0;
		$res_det = $db->Execute("SELECT CONCAT(M_AR_LEDGER_CODE.CODE,' - ',LEDGER_DESCRIPTION) AS LEDGER, M_CAMPUS_PROGRAM_AWARD.PK_AR_LEDGER_CODE, DAYS_FROM_START, ACADEMIC_YEAR, ACADEMIC_PERIOD, GROSS_AMOUNT, FEE_AMOUNT, NET_AMOUNT, HOURS_REQUIRED, NO_OF_PAYMENTS, CONCAT(M_DEPENDENT_STATUS.CODE,' - ',M_DEPENDENT_STATUS.DESCRIPTION) AS DEPENDENT_STATUS, M_CAMPUS_PROGRAM_AWARD.PK_DEPENDENT_STATUS, PAYMENT_FREQUENCY, M_CAMPUS_PROGRAM_AWARD.PK_PAYMENT_FREQUENCY
		FROM 
		M_CAMPUS_PROGRAM_AWARD 
		LEFT JOIN M_PAYMENT_FREQUENCY ON M_PAYMENT_FREQUENCY.PK_PAYMENT_FREQUENCY = M_CAMPUS_PROGRAM_AWARD.PK_PAYMENT_FREQUENCY 
		LEFT JOIN M_DEPENDENT_STATUS ON M_DEPENDENT_STATUS.PK_DEPENDENT_STATUS = M_CAMPUS_PROGRAM_AWARD.PK_DEPENDENT_STATUS 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = M_CAMPUS_PROGRAM_AWARD.PK_AR_LEDGER_CODE 
		WHERE M_CAMPUS_PROGRAM_AWARD.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		while (!$res_det->EOF) { 
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['LEDGER_CODE']			= $res_det->fields['LEDGER'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['LEDGER_CODE_ID']		= $res_det->fields['PK_AR_LEDGER_CODE'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['DAYS_FROM_START']		= $res_det->fields['DAYS_FROM_START'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['ACADEMIC_YEAR']			= $res_det->fields['ACADEMIC_YEAR'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['ACADEMIC_PERIOD']		= $res_det->fields['ACADEMIC_PERIOD'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['GROSS_AMOUNT']			= $res_det->fields['GROSS_AMOUNT'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['FEE_AMOUNT']			= $res_det->fields['FEE_AMOUNT'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['NET_AMOUNT']			= $res_det->fields['NET_AMOUNT'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['HOURS_REQUIRED']		= $res_det->fields['HOURS_REQUIRED'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['NO_OF_PAYMENTS']		= $res_det->fields['NO_OF_PAYMENTS'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['DEPENDENT_STATUS']		= $res_det->fields['DEPENDENT_STATUS'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['DEPENDENT_STATUS_ID']	= $res_det->fields['PK_DEPENDENT_STATUS'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['PAYMENT_FREQUENCY']		= $res_det->fields['PAYMENT_FREQUENCY'];
			$data['PROGRAM'][$i]['FINANCIAL_PLAN'][$j]['PAYMENT_FREQUENCY_ID']	= $res_det->fields['PK_PAYMENT_FREQUENCY'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("SELECT M_GRADE_BOOK_CODE.CODE as GRADE_BOOK_CODE, S_PROGRAM_GRADE_BOOK.PK_GRADE_BOOK_CODE, S_PROGRAM_GRADE_BOOK.DESCRIPTION, GRADE_BOOK_TYPE, S_PROGRAM_GRADE_BOOK.PK_GRADE_BOOK_TYPE, S_PROGRAM_GRADE_BOOK.SESSION, S_PROGRAM_GRADE_BOOK.HOUR, S_PROGRAM_GRADE_BOOK.POINTS
		FROM 
		S_PROGRAM_GRADE_BOOK 
		LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_PROGRAM_GRADE_BOOK.PK_GRADE_BOOK_TYPE 
		LEFT JOIN M_GRADE_BOOK_CODE ON M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_PROGRAM_GRADE_BOOK.PK_GRADE_BOOK_CODE 
		WHERE S_PROGRAM_GRADE_BOOK.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		while (!$res_det->EOF) { 
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['GRADE_BOOK_CODE']		= $res_det->fields['GRADE_BOOK_CODE'];
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['GRADE_BOOK_ID']			= $res_det->fields['PK_GRADE_BOOK_CODE'];
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['DESCRIPTION']			= $res_det->fields['DESCRIPTION'];
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['GRADE_BOOK_TYPE']		= $res_det->fields['GRADE_BOOK_TYPE'];
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['GRADE_BOOK_TYPE_ID']	= $res_det->fields['PK_GRADE_BOOK_TYPE'];
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['SESSION']				= $res_det->fields['SESSION'];
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['HOUR']					= $res_det->fields['HOUR'];
			$data['PROGRAM'][$i]['GRADE_BOOK'][$j]['POINTS']				= $res_det->fields['POINTS'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;