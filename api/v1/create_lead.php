<? require_once("../../global/config.php"); 
require_once("../../school/function_attendance.php"); 

$DATA = (file_get_contents('php://input'));
/*$file = "data/create_lead_".date('m_d_y_h_i_s').".txt";
file_put_contents($file, $DATA);*/

//$DATA = '{"FIRST_NAME":"From","LAST_NAME":"API","MIDDLE_NAME":"M","EMAIL":"aaa@www.in","HOME_PHONE":"123-456-789","CELL_PHONE":"444-555-6666" ,"LEAD_SOURCE_ID":"1","ADM_USER_ID":"ADM user","OLD_DSIS_STU_NO":"","OLD_DSIS_LEAD_ID":"","ADDRESS":{"STREET":"2/F1 A.S.S.S.S Road","CITY":"VNR","STATE_CODE":"CA","ZIP":"62600","COUNTRY_CODE":"US"}}';

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}

$DATA = urldecode($DATA);
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
	
	$FIRST_NAME 				= trim($DATA->FIRST_NAME);
	$LAST_NAME 					= trim($DATA->LAST_NAME);
	$MIDDLE_NAME 				= trim($DATA->MIDDLE_NAME);
	$OTHER_NAME 				= trim($DATA->OTHER_NAME);
	$SSN 						= trim($DATA->SSN);
	$SSN_VERIFIED 				= trim($DATA->SSN_VERIFIED);
	$DATE_OF_BIRTH 				= trim($DATA->DATE_OF_BIRTH);
	$DRIVERS_LICENSE 			= trim($DATA->DRIVERS_LICENSE);
	$PK_DRIVERS_LICENSE_STATE 	= trim($DATA->DRIVERS_LICENSE_STATE_ID);
	$PK_MARITAL_STATUS 			= trim($DATA->MARITAL_STATUS_ID);
	$GENDER 					= trim($DATA->GENDER_ID);
	$PK_COUNTRY_CITIZEN			= trim($DATA->COUNTRY_CITIZEN_ID);
	$PK_CITIZENSHIP 			= trim($DATA->CITIZENSHIP_ID);
	$PLACE_OF_BIRTH 			= trim($DATA->PLACE_OF_BIRTH);
	$PK_STATE_OF_RESIDENCY 		= trim($DATA->STATE_OF_RESIDENCY_ID);
	$OLD_DSIS_STU_NO 			= trim($DATA->OLD_DSIS_STU_NO);
	$OLD_DSIS_LEAD_ID 			= trim($DATA->OLD_DSIS_LEAD_ID);
	$IPEDS_ETHNICITY 			= trim($DATA->IPEDS_ETHNICITY);
	$BADGE_ID					= trim($DATA->BADGE_ID);
	$STUDENT_ID 				= trim($DATA->STUDENT_ID);
	$ADM_USER_ID 				= trim($DATA->ADM_USER_ID);
	$PK_HIGHEST_LEVEL_OF_EDU 	= trim($DATA->HIGHEST_LEVEL_OF_EDU_ID);
	$PREVIOUS_COLLEGE 			= trim($DATA->PREVIOUS_COLLEGE);
	$FERPA_BLOCK 				= trim($DATA->FERPA_BLOCK);
	$ARCHIVED 					= trim($DATA->ARCHIVED);
	
	if($FIRST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'First Name - Missing';
	}
	
	if($LAST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Last Name - Missing';
	}
	
	if($PK_DRIVERS_LICENSE_STATE != '') {
		$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE PK_STATES = '$PK_DRIVERS_LICENSE_STATE' ");
		$PK_DRIVERS_LICENSE_STATE = $res_st->fields['PK_STATES'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid DRIVERS_LICENSE_STATE_ID Value';
		}
	}
	
	if($PK_MARITAL_STATUS != '') {
		$res_st = $db->Execute("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE PK_MARITAL_STATUS = '$PK_MARITAL_STATUS' AND ACTIVE = 1 ");
		$PK_MARITAL_STATUS = $res_st->fields['PK_MARITAL_STATUS'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid MARITAL_STATUS_ID Value';
		}
	}
	
	if(strtolower($SSN_VERIFIED) == 'yes')
		$SSN_VERIFIED = 1;
	else if(strtolower($SSN_VERIFIED) == 'no')
		$SSN_VERIFIED = 0;
	else if($SSN_VERIFIED != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';

		$data['MESSAGE'] .= 'Invalid SSN_VERIFIED Value';
	} else
		$SSN_VERIFIED = 0;
		
	if(strtolower($ARCHIVED) == 'yes')
		$ARCHIVED = 1;
	else if(strtolower($ARCHIVED) == 'no')
		$ARCHIVED = 0;
	else if($ARCHIVED != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid ARCHIVED Value';
	} else
		$ARCHIVED = 0;
	
	if($GENDER != '') {
		if($GENDER != 1 && $GENDER != 2) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid GENDER_ID Value';
		}
	}
	
	if(strtolower($PREVIOUS_COLLEGE) == 'yes')
		$PREVIOUS_COLLEGE = 1;
	else if(strtolower($PREVIOUS_COLLEGE) == 'no')
		$PREVIOUS_COLLEGE = 2;
	else if($PREVIOUS_COLLEGE != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid PREVIOUS_COLLEGE Value';
	} else
		$PREVIOUS_COLLEGE = 2;
		
	if(strtolower($FERPA_BLOCK) == 'yes')
		$FERPA_BLOCK = 1;
	else if(strtolower($FERPA_BLOCK) == 'no')
		$FERPA_BLOCK = 2;
	else if($FERPA_BLOCK != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid FERPA_BLOCK Value';
	} else
		$FERPA_BLOCK = 2;	
	
	if($PK_CITIZENSHIP != '') {
		$res_st = $db->Execute("select PK_CITIZENSHIP from Z_CITIZENSHIP WHERE PK_CITIZENSHIP = '$PK_CITIZENSHIP' ");
		$PK_CITIZENSHIP = $res_st->fields['PK_CITIZENSHIP'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid COUNTRY_CITIZEN_ID Value';
		}
	}
	
	if($PK_STATE_OF_RESIDENCY != '') {
		$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE PK_STATES = '$PK_STATE_OF_RESIDENCY' ");
		$PK_STATE_OF_RESIDENCY = $res_st->fields['PK_STATES'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid STATE_OF_RESIDENCY_ID Value';
		}
	}
	
	if(!empty($DATA->RACE)){
		foreach($DATA->RACE as $PK_RACE) {
			$res_st = $db->Execute("select PK_RACE from Z_RACE WHERE PK_RACE = '$PK_RACE' ");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid RACE Value - '.$PK_RACE;
			} else 
				$PK_RACE_ARR[] = $PK_RACE;
		}
	}
	
	

	foreach($DATA->ENROLLMENT as $ENROLLMENT){
		$IS_ACTIVE_ENROLLMENT 		= trim($ENROLLMENT->IS_ACTIVE_ENROLLMENT);
		$PK_TERM_MASTER 			= trim($ENROLLMENT->FIRST_TERM_DATE_ID); 
		$PK_CAMPUS_PROGRAM 			= trim($ENROLLMENT->PROGRAM_ID);
		$PK_STUDENT_STATUS 			= trim($ENROLLMENT->STATUS_ID);
		$STATUS_DATE 				= trim($ENROLLMENT->STATUS_DATE);
		$PK_REPRESENTATIVE 			= trim($ENROLLMENT->ADMISSION_REP_ID);
		$PK_LEAD_SOURCE 			= trim($ENROLLMENT->LEAD_SOURCE_ID);
		$PK_LEAD_CONTACT_SOURCE 	= trim($ENROLLMENT->CONTACT_SOURCE_ID);
		$CONTRACT_SIGNED_DATE 		= trim($ENROLLMENT->CONTRACT_SIGNED_DATE);
		$CONTRACT_END_DATE 			= trim($ENROLLMENT->CONTRACT_END_DATE);
		$ENTRY_DATE 				= trim($ENROLLMENT->ENTRY_DATE);
		$ENTRY_TIME 				= trim($ENROLLMENT->ENTRY_TIME);
		$EXPECTED_GRAD_DATE 		= trim($ENROLLMENT->EXPECTED_GRAD_DATE);
		//$FIRST_TERM 		 		= trim($ENROLLMENT->FIRST_TIME); //Ticket #971
		
		$ORIGINAL_EXPECTED_GRAD_DATE = trim($ENROLLMENT->ORIGINAL_EXPECTED_GRAD_DATE);
		$PK_ENROLLMENT_STATUS 		= trim($ENROLLMENT->PART_FULL_TIME_ID);
		$PK_SESSION 				= trim($ENROLLMENT->SESSION_ID);
		$PK_FUNDING 				= trim($ENROLLMENT->FUNDING_ID);
		$PK_STUDENT_GROUP 			= trim($ENROLLMENT->STUDENT_GROUP_ID);
		$ENROLLMENT_PK_TERM_BLOCK 	= trim($ENROLLMENT->TERM_BLOCK_ID);
		$LDA 						= trim($ENROLLMENT->LDA);
		$DETERMINATION_DATE 		= trim($ENROLLMENT->DETERMINATION_DATE);
		$DROP_DATE 					= trim($ENROLLMENT->DROP_DATE);
		$MIDPOINT_DATE 				= trim($ENROLLMENT->MIDPOINT_DATE);
		$PK_PLACEMENT_STATUS		= trim($ENROLLMENT->PLACEMENT_STATUS_ID);
		$PK_SPECIAL					= trim($ENROLLMENT->CEO_CPL_SPECIAL_ID);
		$PK_DROP_REASON				= trim($ENROLLMENT->DROP_REASON_ID);
		$GRADE_DATE					= trim($ENROLLMENT->GRAD_DATE);
		
		$PK_1098T_REPORTING_TYPE	= trim($ENROLLMENT->_1098T_REPORTING_TYPE_ID);
		$FT_PT_EFFECTIVE_DATE		= trim($ENROLLMENT->FT_PT_EFFECTIVE_DATE);
		$PK_DISTANCE_LEARNING		= trim($ENROLLMENT->DISTANCE_LEARNING_ID);
		$FIRST_TERM					= trim($ENROLLMENT->IPEDS_ENROLLMENT_STATUS_ID);
		$STRF_PAID_DATE				= trim($ENROLLMENT->STRF_PAID_DATE);

		$REENTRY					= trim($ENROLLMENT->REENTRY);
		$TRANSFER_IN				= trim($ENROLLMENT->TRANSFER_IN);
		$TRANSFER_OUT				= trim($ENROLLMENT->TRANSFER_OUT);
		
		if(strtolower($REENTRY) == 'yes')
			$REENTRY = 1;
		else if(strtolower($REENTRY) == 'no')
			$REENTRY = 0;
		else if($REENTRY != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ENROLLMENT->REENTRY Value';
		} else
			$REENTRY = 0;
			
		if(strtolower($TRANSFER_IN) == 'yes')
			$TRANSFER_IN = 1;
		else if(strtolower($TRANSFER_IN) == 'no')
			$TRANSFER_IN = 0;
		else if($TRANSFER_IN != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ENROLLMENT->TRANSFER_IN Value';
		} else
			$TRANSFER_IN = 0;
			
		if(strtolower($TRANSFER_OUT) == 'yes')
			$TRANSFER_OUT = 1;
		else if(strtolower($TRANSFER_OUT) == 'no')
			$TRANSFER_OUT = 0;
		else if($TRANSFER_OUT != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';

			$data['MESSAGE'] .= 'Invalid ENROLLMENT->TRANSFER_OUT Value';
		} else
			$TRANSFER_OUT = 0;
		
		if(strtolower($IS_ACTIVE_ENROLLMENT) != 'yes' && strtolower($IS_ACTIVE_ENROLLMENT) != 'no' && strtolower($IS_ACTIVE_ENROLLMENT) != '') {
			$data['SUCCESS'] = 0;
			$data['MESSAGE'] .= 'Invalid ENROLLMENT->IS_ACTIVE_ENROLLMENT Value - '.$IS_ACTIVE_ENROLLMENT;
		}
		
		if($PK_TERM_MASTER != '') {
			$res_st = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_TERM_MASTER = '$PK_TERM_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			$PK_TERM_MASTER = $res_st->fields['PK_TERM_MASTER'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->FIRST_TERM_DATE_ID Value - '.$PK_TERM_MASTER;
			}
		}
		
		if($PK_CAMPUS_PROGRAM != '') {
			$res_st = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			$PK_CAMPUS_PROGRAM = $res_st->fields['PK_CAMPUS_PROGRAM'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->PROGRAM_ID Value - '.$PK_CAMPUS_PROGRAM;
			}
		}
		
		if($PK_STUDENT_STATUS != '') {
			$res_st = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$PK_STUDENT_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			$PK_STUDENT_STATUS = $res_st->fields['PK_STUDENT_STATUS'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->STATUS_ID Value - '.$PK_STUDENT_STATUS;
			}
		} else {
			$res_st = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			$PK_STUDENT_STATUS = $res_st->fields['PK_STUDENT_STATUS'];
		} 
		
		if($PK_REPRESENTATIVE != '') {
			$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_REPRESENTATIVE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			$PK_REPRESENTATIVE = $res_st->fields['PK_EMPLOYEE_MASTER'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->ADMISSION_REP_ID Value - '.$PK_REPRESENTATIVE;
			}
		}
		
		if($PK_LEAD_SOURCE != '') {
			$res_st = $db->Execute("select PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_LEAD_SOURCE = '$PK_LEAD_SOURCE' ");
			$PK_LEAD_SOURCE = $res_st->fields['PK_LEAD_SOURCE'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->LEAD_SOURCE_ID Value - '.$PK_LEAD_SOURCE;
			}
		}
		
		if($PK_LEAD_CONTACT_SOURCE != '') {
			$res_st = $db->Execute("select PK_LEAD_CONTACT_SOURCE from M_LEAD_CONTACT_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_LEAD_CONTACT_SOURCE = '$PK_LEAD_CONTACT_SOURCE' ");
			$PK_LEAD_CONTACT_SOURCE = $res_st->fields['PK_LEAD_CONTACT_SOURCE'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->CONTACT_SOURCE_ID Value - '.$PK_LEAD_CONTACT_SOURCE;
			}
		}
		
		if($PK_ENROLLMENT_STATUS != '') {
			$res_st = $db->Execute("select PK_ENROLLMENT_STATUS from M_ENROLLMENT_STATUS WHERE PK_ENROLLMENT_STATUS = '$PK_ENROLLMENT_STATUS' ");
			$PK_ENROLLMENT_STATUS = $res_st->fields['PK_ENROLLMENT_STATUS'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->PART_FULL_TIME_ID Value - '.$PK_ENROLLMENT_STATUS;
			}
		}
		
		if($PK_SESSION != '') {
			$res_st = $db->Execute("select PK_SESSION from M_SESSION WHERE PK_SESSION = '$PK_SESSION' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			//$PK_SESSION = $res_st->fields['PK_SESSION'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->SESSION_ID Value - '.$PK_SESSION;
			}
		}
		
		if($PK_FUNDING != '') {
			$res_st = $db->Execute("select PK_FUNDING from M_FUNDING WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_FUNDING = '$PK_FUNDING' ");
			$PK_FUNDING = $res_st->fields['PK_FUNDING'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->FUNDING_ID Value - '.$PK_FUNDING;
			}
		}
		
		if($PK_PLACEMENT_STATUS != '') {
			$res_st = $db->Execute("select PK_PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_PLACEMENT_STATUS = '$PK_PLACEMENT_STATUS' ");
			$PK_PLACEMENT_STATUS = $res_st->fields['PK_PLACEMENT_STATUS'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->PLACEMENT_STATUS_ID Value - '.$PK_PLACEMENT_STATUS;
			}
		}
		
		if($PK_SPECIAL != '') {
			$res_st = $db->Execute("select PK_SPECIAL from Z_SPECIAL WHERE PK_SPECIAL = '$PK_SPECIAL' ");
			$PK_SPECIAL = $res_st->fields['PK_SPECIAL'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->CEO_CPL_SPECIAL_ID Value - '.$PK_SPECIAL;
			}
		}
		
		if($PK_STUDENT_GROUP != '') {
			$res_st = $db->Execute("select PK_STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_GROUP = '$PK_STUDENT_GROUP' ");
			$PK_STUDENT_GROUP = $res_st->fields['PK_STUDENT_GROUP'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->STUDENT_GROUP_ID Value - '.$PK_STUDENT_GROUP;
			}
		}
		
		if($PK_DROP_REASON != '') {
			$res_st = $db->Execute("select PK_DROP_REASON from M_DROP_REASON WHERE PK_DROP_REASON = '$PK_DROP_REASON' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			$PK_DROP_REASON = $res_st->fields['PK_DROP_REASON'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->DROP_REASON_ID Value - '.$PK_DROP_REASON;
			}
		}
		
		if($PK_1098T_REPORTING_TYPE != '') {
			$res_st = $db->Execute("select PK_1098T_REPORTING_TYPE from Z_1098T_REPORTING_TYPE WHERE PK_1098T_REPORTING_TYPE = '$PK_1098T_REPORTING_TYPE' ");
			$PK_1098T_REPORTING_TYPE = $res_st->fields['PK_1098T_REPORTING_TYPE'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->_1098T_REPORTING_TYPE_ID Value - '.$PK_1098T_REPORTING_TYPE;
			}
		}
		
		if($PK_DISTANCE_LEARNING != '') {
			$res_st = $db->Execute("select PK_DISTANCE_LEARNING from M_DISTANCE_LEARNING WHERE PK_DISTANCE_LEARNING = '$PK_DISTANCE_LEARNING' ");
			$PK_DISTANCE_LEARNING = $res_st->fields['PK_DISTANCE_LEARNING'];

			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->DISTANCE_LEARNING_ID Value - '.$PK_DISTANCE_LEARNING;
			}
		}

		if($FIRST_TERM != '') {
			$res_st = $db->Execute("select PK_FIRST_TERM from M_FIRST_TERM WHERE PK_FIRST_TERM = '$FIRST_TERM' ");
			$FIRST_TERM = $res_st->fields['FIRST_TERM'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->IPEDS_ENROLLMENT_STATUS_ID Value - '.$FIRST_TERM;
			}
		}
		
		if($ENROLLMENT_PK_TERM_BLOCK != '') {
			$res_st = $db->Execute("select PK_TERM_BLOCK from S_TERM_BLOCK WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_TERM_BLOCK = '$ENROLLMENT_PK_TERM_BLOCK' ");
			$ENROLLMENT_PK_TERM_BLOCK = $res_st->fields['PK_TERM_BLOCK'];
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
		
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ENROLLMENT->TERM_BLOCK_ID Value - '.$ENROLLMENT_PK_TERM_BLOCK;
			}
		}
		
		if(!empty($ENROLLMENT->CAMPUS)){
			foreach($ENROLLMENT->CAMPUS as $PK_CAMPUS) {
				$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->CAMPUS Value - '.$PK_CAMPUS;
				}
			}
		}
		
		if(!empty($ENROLLMENT->LOA)){
			foreach($ENROLLMENT->LOA as $LOA) {
				$PK_DEPARTMENT = $LOA->DEPARTMENT_ID;
				if($PK_DEPARTMENT == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->LOA->DEPARTMENT_ID Value';
				} else if($PK_DEPARTMENT != -1){
					$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0 ){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->LOA->DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
					}
				}
				
				if($LOA->BEGIN_DATE == '') {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= ' Missing ENROLLMENT->LOA->BEGIN_DATE Value';
				}
				
				/*if($LOA->END_DATE == '') {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= ' Missing ENROLLMENT->LOA->END_DATE Value';
				}*/
			}
		}
		
		if(!empty($ENROLLMENT->PROBATION)){
			foreach($ENROLLMENT->PROBATION as $PROBATION) {
				$PK_DEPARTMENT = $PROBATION->DEPARTMENT_ID;
				if($PK_DEPARTMENT == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->PROBATION_TYPE_ID->DEPARTMENT_ID Value';
				} else if($PK_DEPARTMENT != -1){
					$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0 ){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->PROBATION_TYPE_ID->DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
					}
				}
				
				$PK_PROBATION_TYPE = $PROBATION->PROBATION_TYPE_ID;
				if($PK_PROBATION_TYPE == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->PROBATION->PROBATION_TYPE_ID Value';
				} else {
					$res_st = $db->Execute("select PK_PROBATION_TYPE from M_PROBATION_TYPE WHERE PK_PROBATION_TYPE = '$PK_PROBATION_TYPE' ");
					if($res_st->RecordCount() == 0 ){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->PROBATION->PROBATION_TYPE_ID Value - '.$PK_PROBATION_TYPE;
					}
				}
				
				$PK_PROBATION_LEVEL = $PROBATION->PROBATION_LEVEL_ID;
				if($PK_PROBATION_LEVEL == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->PROBATION->PROBATION_LEVEL_ID Value';
				} else {
					$res_st = $db->Execute("select PK_PROBATION_LEVEL from M_PROBATION_LEVEL WHERE PK_PROBATION_LEVEL = '$PK_PROBATION_LEVEL' ");
					if($res_st->RecordCount() == 0 ){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->PROBATION->PROBATION_LEVEL_ID Value - '.$PK_PROBATION_TYPE;
					}
				}
				
				$PK_PROBATION_STATUS = $PROBATION->PROBATION_STATUS_ID;
				if($PK_PROBATION_STATUS == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->PROBATION->PROBATION_STATUS_ID Value';
				} else {
					$res_st = $db->Execute("select PK_PROBATION_STATUS from M_PROBATION_STATUS WHERE PK_PROBATION_STATUS = '$PK_PROBATION_STATUS' ");
					if($res_st->RecordCount() == 0 ){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->PROBATION->PROBATION_STATUS_ID Value - '.$PK_PROBATION_TYPE;
					}
				}
				
				if($PROBATION->BEGIN_DATE == '') {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= ' Missing ENROLLMENT->PROBATION->BEGIN_DATE Value';
				}
				
				/*if($PROBATION->END_DATE == '') {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= ' Missing ENROLLMENT->PROBATION->END_DATE Value';
				}*/
			}
		}
		
		if(!empty($ENROLLMENT->TASK)){
			foreach($ENROLLMENT->TASK as $TASK) {
				$PK_TASK_TYPE = $TASK->TASK_TYPE_ID;
				if($PK_TASK_TYPE == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->TASK->TASK_TYPE_ID Value';
				} else {
					$res_st = $db->Execute("select PK_TASK_TYPE from M_TASK_TYPE WHERE PK_TASK_TYPE = '$PK_TASK_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->TASK->TASK_TYPE_ID Value - '.$PK_TASK_TYPE;
					}
				}
				
				$PK_DEPARTMENT = $TASK->DEPARTMENT_ID;
				if($PK_DEPARTMENT == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->TASK->DEPARTMENT_ID Value';
				} else if($PK_DEPARTMENT != -1){
					$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0 ){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->TASK->DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
					}
				}

				$PK_TASK_STATUS = $TASK->TASK_STATUS_ID;
				if($PK_TASK_STATUS == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->TASK->TASK_STATUS_ID Value';
				} else {
					$res_st = $db->Execute("select PK_TASK_STATUS from M_TASK_STATUS WHERE PK_TASK_STATUS = '$PK_TASK_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->TASK->TASK_STATUS_ID Value - '.$PK_TASK_STATUS;
					}
				}
				
				$PK_EVENT_OTHER = $TASK->TASK_OTHER_ID;
				if($PK_EVENT_OTHER != '') {
					$res_st = $db->Execute("select PK_EVENT_OTHER from M_EVENT_OTHER WHERE PK_EVENT_OTHER = '$PK_EVENT_OTHER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->TASK->TASK_OTHER_ID Value - '.$PK_EVENT_OTHER;
					}
				}
				
				$PK_NOTES_PRIORITY_MASTER = $TASK->PRIORITY_ID;
				if($PK_NOTES_PRIORITY_MASTER == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->TASK->PRIORITY_ID Value';
				} else {
					$res_st = $db->Execute("select PK_NOTES_PRIORITY_MASTER from M_NOTES_PRIORITY_MASTER WHERE PK_NOTES_PRIORITY_MASTER = '$PK_NOTES_PRIORITY_MASTER' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->TASK->PRIORITY_ID Value - '.$PK_NOTES_PRIORITY_MASTER;
					}
				}
				
				$PK_EMPLOYEE_MASTER = $TASK->EMPLOYEE_ID;
				if($PK_EMPLOYEE_MASTER != ''){ 
					$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid EENROLLMENT->TASK->MPLOYEE_ID Value - '.$PK_EMPLOYEE_MASTER;
					}
				}
				
				$COMPLETED = $TASK->COMPLETED;
				if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->TASK->COMPLETED Value';
				}
				
			}
		}
		
		if(!empty($ENROLLMENT->NOTES)){
			foreach($ENROLLMENT->NOTES as $NOTES) {
				$PK_DEPARTMENT = $NOTES->DEPARTMENT_ID;
				if($PK_DEPARTMENT == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->NOTES->DEPARTMENT_ID Value';
				} else if($PK_DEPARTMENT != -1){
					$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0 ){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->NOTES->DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
					}
				}
				
				$PK_NOTE_STATUS = $NOTES->NOTE_STATUS_ID;
				if($PK_NOTE_STATUS != '') {
					$res_st = $db->Execute("select PK_NOTE_STATUS from M_NOTE_STATUS WHERE PK_NOTE_STATUS = '$PK_NOTE_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->NOTES->NOTE_STATUS_ID Value - '.$PK_NOTE_STATUS;
					}
				}
				
				$PK_NOTE_TYPE = $NOTES->NOTE_TYPE_ID;
				if($PK_NOTE_TYPE == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing  ENROLLMENT->NOTES->NOTE_TYPE_ID Value';
				} else {
					$res_st = $db->Execute("select PK_NOTE_TYPE from M_NOTE_TYPE WHERE PK_NOTE_TYPE = '$PK_NOTE_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->NOTES->NOTE_TYPE_ID Value - '.$PK_NOTE_TYPE;
					}
				}
				
				$PK_EMPLOYEE_MASTER = $NOTES->EMPLOYEE_ID;
				if($PK_EMPLOYEE_MASTER != ''){ 
					$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid  ENROLLMENT->NOTES->EMPLOYEE_ID Value - '.$PK_EMPLOYEE_MASTER;
					}
				}
				
				$COMPLETED = $NOTES->COMPLETED;
				if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid  ENROLLMENT->NOTES->COMPLETED Value';
				}
			}
		}
		
		if(!empty($ENROLLMENT->EVENTS)){
			foreach($ENROLLMENT->EVENTS as $EVENTS) {
				$PK_DEPARTMENT = $EVENTS->DEPARTMENT_ID;
				if($PK_DEPARTMENT == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->EVENTS->DEPARTMENT_ID Value';
				} else {
					$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->EVENTS->DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
					}
				}
				
				$PK_NOTE_STATUS = $EVENTS->EVENTS_STATUS_ID;
				if($PK_NOTE_STATUS == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->EVENTS->EVENTS_STATUS_ID Value';
				} else {
					$res_st = $db->Execute("select PK_NOTE_STATUS from M_NOTE_STATUS WHERE PK_NOTE_STATUS = '$PK_NOTE_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->EVENTS->EVENTS_STATUS_ID Value - '.$PK_NOTE_STATUS;
					}
				}
				
				$PK_NOTE_TYPE = $EVENTS->EVENTS_TYPE_ID;
				if($PK_NOTE_TYPE == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->EVENTS->EVENTS_TYPE_ID Value';
				} else {
					$res_st = $db->Execute("select PK_NOTE_TYPE from M_NOTE_TYPE WHERE PK_NOTE_TYPE = '$PK_NOTE_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->EVENTS->EVENTS_TYPE_ID Value - '.$PK_NOTE_TYPE;
					}
				}
				
				$PK_EVENT_OTHER = $EVENTS->EVENTS_OTHER_ID;
				if($PK_EVENT_OTHER != '') {
					$res_st = $db->Execute("select PK_EVENT_OTHER from M_EVENT_OTHER WHERE PK_EVENT_OTHER = '$PK_EVENT_OTHER' AND PK_ACCOUNT = '$PK_ACCOUNT' AND TYPE = 2 ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->EVENTS->EVENTS_OTHER_ID Value - '.$PK_EVENT_OTHER;
					}
				}
				
				$PK_EMPLOYEE_MASTER = $EVENTS->EMPLOYEE_ID;
				if($PK_EMPLOYEE_MASTER != ''){ 
					$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->EVENTS->EMPLOYEE_ID Value - '.$PK_EMPLOYEE_MASTER;
					}
				}
				
				$COMPLETED = $EVENTS->COMPLETED;
				if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->EVENTS->COMPLETED Value';
				}
			}
		}
		
		if(!empty($ENROLLMENT->DOCUMENTS)){
			foreach($ENROLLMENT->DOCUMENTS as $DOCUMENTS) {
				$PK_DOCUMENT_TYPE = $DOCUMENTS->DOCUMENT_TYPE_ID;
				
				if(empty($DOCUMENTS->DEPARTMENTS)){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Missing ENROLLMENT->DOCUMENTS->DEPARTMENTS';
				} else {
					foreach($DOCUMENTS->DEPARTMENTS as $PK_DEPARTMENT){
						$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
						if($res_st->RecordCount() == 0){
							$data['SUCCESS'] = 0;
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= 'Invalid ENROLLMENT->DOCUMENTS->DEPARTMENTS Value - '.$PK_DEPARTMENT;
						}
					}
				}
				
				if($PK_DOCUMENT_TYPE != '') {
					$res_st = $db->Execute("select PK_DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE PK_DOCUMENT_TYPE = '$PK_DOCUMENT_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->DOCUMENTS->DOCUMENT_TYPE_ID Value - '.$PK_DOCUMENT_TYPE;
					}
					
					$PK_EMPLOYEE_MASTER = $DOCUMENTS->EMPLOYEE_ID;
					if($PK_EMPLOYEE_MASTER != ''){ 
						$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
						if($res_st->RecordCount() == 0){
							$data['SUCCESS'] = 0;
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= 'Invalid ENROLLMENT->DOCUMENTS->EMPLOYEE_ID Value - '.$PK_EMPLOYEE_MASTER;
						}
					}
				}
				
				$RECEIVED = $DOCUMENTS->RECEIVED;
				if(strtolower($RECEIVED) != 'yes' && strtolower($RECEIVED) != 'no' && strtolower($RECEIVED) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->DOCUMENTS->RECEIVED Value';
				}
			}
		}
		
		if(!empty($ENROLLMENT->REQUIREMENT)){
			foreach($ENROLLMENT->REQUIREMENT as $REQUIREMENT) {
				$MANDATORY = $REQUIREMENT->MANDATORY;
				if(strtolower($MANDATORY) != 'yes' && strtolower($MANDATORY) != 'no' && strtolower($MANDATORY) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->REQUIREMENT->MANDATORY Value';
				}
				
				$COMPLETED = $REQUIREMENT->COMPLETED;
				if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->REQUIREMENT->COMPLETED Value';
				}
				
				$REQUIREMENT_TYPE = $REQUIREMENT->REQUIREMENT_TYPE;
				if(strtolower($REQUIREMENT_TYPE) != 'school' && strtolower($REQUIREMENT_TYPE) != 'program' && strtolower($REQUIREMENT_TYPE) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->REQUIREMENT->REQUIREMENT_TYPE Value';
				}
				
				$PK_EMPLOYEE_MASTER = $REQUIREMENT->COMPLETED_BY;
				if($PK_EMPLOYEE_MASTER != ''){ 
					$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->REQUIREMENT->COMPLETED_BY Value - '.$PK_EMPLOYEE_MASTER;
					}
				}
				
			}
		}
		
		if(!empty($ENROLLMENT->QUESTIONNAIRE)){
			foreach($ENROLLMENT->QUESTIONNAIRE as $QUESTIONNAIRE) {
				$PK_DEPARTMENT = $QUESTIONNAIRE->DEPARTMENT_ID;
				if($PK_DEPARTMENT == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->REQUIREMENT->DEPARTMENT_ID Value';
				} else {
					$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->REQUIREMENT->DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
					}
				}
				
				$PK_QUESTIONNAIRE = $QUESTIONNAIRE->QUESTION_ID;
				if($PK_QUESTIONNAIRE == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->REQUIREMENT->QUESTION_ID Value';
				} else {
					$res_st = $db->Execute("select PK_QUESTIONNAIRE from M_QUESTIONNAIRE WHERE PK_QUESTIONNAIRE = '$PK_QUESTIONNAIRE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->REQUIREMENT->QUESTION_ID Value - '.$PK_QUESTIONNAIRE;
					}
				}
			}
		}
		
		if(!empty($ENROLLMENT->COURSE_OFFERING)){
			foreach($ENROLLMENT->COURSE_OFFERING as $COURSE_OFFERING) {
				$PK_COURSE_OFFERING = $COURSE_OFFERING->ID;
				if($PK_COURSE_OFFERING == '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Missing ENROLLMENT->COURSE_OFFERING->ID Value';
				} else {
					$res_st = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->ID Value - '.$PK_COURSE_OFFERING;
					}
				}
				
				if($COURSE_OFFERING->COURSE_OFFERING_GRADE_BOOK != '') {
					foreach($COURSE_OFFERING->COURSE_OFFERING_GRADE_BOOK  as $COURSE_OFFERING_GRADE_BOOK ) {
						$PK_COURSE_OFFERING_GRADE 	= $COURSE_OFFERING_GRADE_BOOK->ID;
						$res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$PK_ACCOUNT'");
						if($res_grade->RecordCount() == 0) {
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->COURSE_OFFERING_GRADE_BOOK->ID Value - '.$PK_COURSE_OFFERING_GRADE;
						}
					}
				}
				
				$FINAL_GRADE_ID = $COURSE_OFFERING->FINAL_GRADE_ID;
				if($FINAL_GRADE_ID != '') {
					$res_st = $db->Execute("select PK_GRADE from S_GRADE WHERE PK_GRADE = '$FINAL_GRADE_ID' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->FINAL_GRADE_ID Value - '.$FINAL_GRADE_ID;
					}
				}
				
				$PK_COURSE_OFFERING_STUDENT_STATUS = $COURSE_OFFERING->COURSE_OFFERING_STUDENT_STATUS;
				if($PK_COURSE_OFFERING_STUDENT_STATUS != '') {
					$res_st = $db->Execute("select PK_COURSE_OFFERING_STUDENT_STATUS from M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_COURSE_OFFERING_STUDENT_STATUS = '$PK_COURSE_OFFERING_STUDENT_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->COURSE_OFFERING_STUDENT_STATUS Value - '.$PK_COURSE_OFFERING_STUDENT_STATUS;
					}
				}
				
				$CREATE_SCHEDULED_ATTENDANCE = $COURSE_OFFERING->CREATE_SCHEDULED_ATTENDANCE;
				if(strtolower($CREATE_SCHEDULED_ATTENDANCE) != 'yes' && strtolower($CREATE_SCHEDULED_ATTENDANCE) != 'no' && strtolower($CREATE_SCHEDULED_ATTENDANCE) != '') {
					$data['SUCCESS'] = 0;
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->CREATE_SCHEDULED_ATTENDANCE Value - '.$CREATE_SCHEDULED_ATTENDANCE;
				}
				
				if(!empty($COURSE_OFFERING->ATTENDANCE)){
					foreach($COURSE_OFFERING->ATTENDANCE as $ATTENDANCE) {
						
						if($ATTENDANCE->DATE == '') {
							$data['SUCCESS'] = 0;
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= ' Missing ENROLLMENT->COURSE_OFFERING->ATTENDANCE->DATE Value';
						}
						
						if($ATTENDANCE->START_TIME == '') {
							$data['SUCCESS'] = 0;
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= ' Missing ENROLLMENT->COURSE_OFFERING->ATTENDANCE->START_TIME Value';
						}
						
						$PK_ATTENDANCE_CODE = $ATTENDANCE->ATTENDANCE_CODE_ID;
						if($PK_ATTENDANCE_CODE != '') {
							$res_st = $db->Execute("select PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ");
							if($res_st->RecordCount() == 0){
								$data['SUCCESS'] = 0;
								if($data['MESSAGE'] != '')
									$data['MESSAGE'] .= ', ';
									
								$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->ATTENDANCE->ATTENDANCE_CODE_ID Value - '.$PK_ATTENDANCE_CODE;
							}
						}
						
						$COMPLETED = $ATTENDANCE->COMPLETED;
						if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
							$data['SUCCESS'] = 0;
							$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->ATTENDANCE->COMPLETED Value - '.$COMPLETED;
						}
						
					}
				}
				
				if(!empty($COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE)){
					foreach($COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE as $NON_SCHEDULED_ATTENDANCE) {
						
						if($NON_SCHEDULED_ATTENDANCE->DATE == '') {
							$data['SUCCESS'] = 0;
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= ' Missing ENROLLMENT->COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE->DATE Value';
						}
						
						if($NON_SCHEDULED_ATTENDANCE->START_TIME == '') {
							$data['SUCCESS'] = 0;
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= ' Missing ENROLLMENT->COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE->START_TIME Value';
						}
						
						if($NON_SCHEDULED_ATTENDANCE->END_TIME == '') {
							$data['SUCCESS'] = 0;
							if($data['MESSAGE'] != '')
								$data['MESSAGE'] .= ', ';
								
							$data['MESSAGE'] .= ' Missing ENROLLMENT->COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE->END_TIME Value';
						}
						
						$PK_ATTENDANCE_CODE = $NON_SCHEDULED_ATTENDANCE->ATTENDANCE_CODE_ID;
						if($PK_ATTENDANCE_CODE != '') {
							$res_st = $db->Execute("select PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ");
							if($res_st->RecordCount() == 0){
								$data['SUCCESS'] = 0;
								if($data['MESSAGE'] != '')
									$data['MESSAGE'] .= ', ';
									
								$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE->ATTENDANCE_CODE_ID Value - '.$PK_ATTENDANCE_CODE;
							}
						}
						
						$COMPLETED = $NON_SCHEDULED_ATTENDANCE->COMPLETED;
						if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
							$data['SUCCESS'] = 0;
							$data['MESSAGE'] .= 'Invalid ENROLLMENT->COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE->COMPLETED Value - '.$COMPLETED;
						}
						
					}
				}
				
			}
		}
		//////////////////////////////////////
		if(!empty($ENROLLMENT->DISBURSEMENT)){
			foreach($ENROLLMENT->DISBURSEMENT as $DISBURSEMENT) {
				$PK_AR_LEDGER_CODE = $DISBURSEMENT->LEDGER_CODE_ID;
				if($PK_AR_LEDGER_CODE != '') {
					$res_st = $db->Execute("select PK_AR_LEDGER_CODE from M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->DISBURSEMENT->LEDGER_CODE_ID Value - '.$PK_AR_LEDGER_CODE;
					}
				}
				
				$PK_AWARD_YEAR = $DISBURSEMENT->AWARD_YEAR_ID;
				if($PK_AR_LEDGER_CODE != '') {
					$res_st = $db->Execute("select PK_AWARD_YEAR from M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$PK_AWARD_YEAR' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->DISBURSEMENT->AWARD_YEAR_ID Value - '.$PK_AWARD_YEAR;
					}
				}
				
				$PK_TERM_BLOCK = $DISBURSEMENT->TERM_BLOCK_ID;
				if($PK_TERM_BLOCK != '') {
					$res_st = $db->Execute("select PK_TERM_BLOCK from S_TERM_BLOCK WHERE PK_TERM_BLOCK = '$PK_TERM_BLOCK' ");
					if($res_st->RecordCount() == 0){
						$data['SUCCESS'] = 0;
						if($data['MESSAGE'] != '')
							$data['MESSAGE'] .= ', ';
							
						$data['MESSAGE'] .= 'Invalid ENROLLMENT->DISBURSEMENT->TERM_BLOCK_ID Value - '.$PK_TERM_BLOCK;
					}
				}
				
				$FUNDS_REQUESTED = $DISBURSEMENT->FUNDS_REQUESTED;
				if(strtolower($FUNDS_REQUESTED) == 'yes')
					$FUNDS_REQUESTED = 1;
				else if(strtolower($FUNDS_REQUESTED) == 'no')
					$FUNDS_REQUESTED = 2;
				else if($FUNDS_REQUESTED != '') {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid ENROLLMENT->DISBURSEMENT->FUNDS_REQUESTED Value - '.$DISBURSEMENT->FUNDS_REQUESTED;
				}
			}
		}
		/////////////////////////////////////
		//////////////////////////////////////
	}
	
	if(!empty($DATA->CONTACTS)){
		foreach($DATA->CONTACTS as $CONTACTS) {
			$PK_STUDENT_CONTACT_TYPE_MASTER = $CONTACTS->CONTACT_TYPE_ID;
			if($PK_STUDENT_CONTACT_TYPE_MASTER == '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Missing CONTACT_TYPE_ID Value';
			} else {
				$res_st = $db->Execute("select PK_STUDENT_CONTACT_TYPE_MASTER from M_STUDENT_CONTACT_TYPE_MASTER WHERE PK_STUDENT_CONTACT_TYPE_MASTER = '$PK_STUDENT_CONTACT_TYPE_MASTER' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid CONTACT_TYPE_ID Value - '.$PK_STUDENT_CONTACT_TYPE_MASTER;
				}
			}
			
			$PK_STUDENT_RELATIONSHIP_MASTER = $CONTACTS->RELATIONSHIP_ID;
			if($PK_STUDENT_RELATIONSHIP_MASTER != '') {
				$res_st = $db->Execute("select PK_STUDENT_RELATIONSHIP_MASTER from M_STUDENT_RELATIONSHIP_MASTER WHERE PK_STUDENT_RELATIONSHIP_MASTER = '$PK_STUDENT_RELATIONSHIP_MASTER' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid RELATIONSHIP_ID Value - '.$PK_STUDENT_RELATIONSHIP_MASTER;
				}
			}
			
			$PK_STATES = $CONTACTS->STATE_ID;
			if($PK_STATES != '') {
				$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE PK_STATES = '$PK_STATES' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid STATE_ID Value - '.$PK_STATES;
				}
			}
			
			$PK_COUNTRY = $CONTACTS->COUNTRY_ID;
			if($PK_COUNTRY == '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Missing COUNTRY_ID Value';
			} else {
				$res_st = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE PK_COUNTRY = '$PK_COUNTRY' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid COUNTRY_ID Value - '.$PK_COUNTRY;
				}
			}
			
			$IS_ADDRESS_INVALID = $CONTACTS->IS_ADDRESS_INVALID;
			if(strtolower($IS_ADDRESS_INVALID) != 'yes' && strtolower($IS_ADDRESS_INVALID) != 'no' && strtolower($IS_ADDRESS_INVALID) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid IS_ADDRESS_INVALID Value';
			}
				
			$HOME_PHONE_INVALID = $CONTACTS->HOME_PHONE_INVALID;
			if(strtolower($HOME_PHONE_INVALID) != 'yes' && strtolower($HOME_PHONE_INVALID) != 'no' && strtolower($HOME_PHONE_INVALID) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid HOME_PHONE_INVALID Value';
			}
			
			$WORK_PHONE_INVALID = $CONTACTS->WORK_PHONE_INVALID;
			if(strtolower($WORK_PHONE_INVALID) != 'yes' && strtolower($WORK_PHONE_INVALID) != 'no' && strtolower($WORK_PHONE_INVALID) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid WORK_PHONE_INVALID Value';
			}
			
			$CELL_PHONE_INVALID = $CONTACTS->CELL_PHONE_INVALID;
			if(strtolower($CELL_PHONE_INVALID) != 'yes' && strtolower($CELL_PHONE_INVALID) != 'no' && strtolower($CELL_PHONE_INVALID) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid CELL_PHONE_INVALID Value';
			}
			
			$OTHER_PHONE_INVALID = $CONTACTS->OTHER_PHONE_INVALID;
			if(strtolower($OTHER_PHONE_INVALID) != 'yes' && strtolower($OTHER_PHONE_INVALID) != 'no' && strtolower($OTHER_PHONE_INVALID) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid OTHER_PHONE_INVALID Value';
			}
			
			$EMAIL_INVALID = $CONTACTS->EMAIL_INVALID;
			if(strtolower($EMAIL_INVALID) != 'yes' && strtolower($EMAIL_INVALID) != 'no' && strtolower($EMAIL_INVALID) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid EMAIL_INVALID Value';
			}
			
			$EMAIL_OTHER_INVALID = $CONTACTS->EMAIL_OTHER_INVALID;
			if(strtolower($EMAIL_OTHER_INVALID) != 'yes' && strtolower($EMAIL_OTHER_INVALID) != 'no' && strtolower($EMAIL_OTHER_INVALID) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid EMAIL_OTHER_INVALID Value';
			}
			
			$OPT_OUT = $CONTACTS->OPT_OUT;
			if(strtolower($OPT_OUT) != 'yes' && strtolower($OPT_OUT) != 'no' && strtolower($OPT_OUT) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid OPT_OUT Value';
			}
			
			$USE_EMAIL = $CONTACTS->USE_EMAIL;
			if(strtolower($USE_EMAIL) != 'yes' && strtolower($USE_EMAIL) != 'no' && strtolower($USE_EMAIL) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid USE_EMAIL Value';
			}
			
			$ACTIVE = $CONTACTS->ACTIVE;
			if(strtolower($ACTIVE) != 'yes' && strtolower($ACTIVE) != 'no' && strtolower($ACTIVE) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid ACTIVE Value';
			}
		}
	}
	
	if(!empty($DATA->OTHER_EDUCATION)){
		foreach($DATA->OTHER_EDUCATION as $OTHER_EDUCATION) {
			$PK_EDUCATION_TYPE = $OTHER_EDUCATION->EDUCATION_TYPE_ID;
			if($PK_EDUCATION_TYPE == '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Missing EDUCATION_TYPE_ID Value';
			} else {
				$res_st = $db->Execute("select PK_EDUCATION_TYPE from M_EDUCATION_TYPE WHERE PK_EDUCATION_TYPE = '$PK_EDUCATION_TYPE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid EDUCATION_TYPE_ID Value - '.$PK_EDUCATION_TYPE;
				}
			}
			
			$GRADUATED = $OTHER_EDUCATION->GRADUATED;
			if(strtolower($GRADUATED) != 'yes' && strtolower($GRADUATED) != 'no' && strtolower($GRADUATED) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid GRADUATED Value';
			}
			
			$TRANSCRIPT_REQUESTED = $OTHER_EDUCATION->TRANSCRIPT_REQUESTED;
			if(strtolower($TRANSCRIPT_REQUESTED) != 'yes' && strtolower($TRANSCRIPT_REQUESTED) != 'no' && strtolower($TRANSCRIPT_REQUESTED) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid TRANSCRIPT_REQUESTED Value';
			}
			
			$TRANSCRIPT_RECEIVED = $OTHER_EDUCATION->TRANSCRIPT_RECEIVED;
			if(strtolower($TRANSCRIPT_RECEIVED) != 'yes' && strtolower($TRANSCRIPT_RECEIVED) != 'no' && strtolower($TRANSCRIPT_RECEIVED) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid TRANSCRIPT_RECEIVED Value';
			}
			
			$PK_STATES = $OTHER_EDUCATION->STATE_ID;
			if($PK_STATES != '') {
				$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE PK_STATES = '$PK_STATES' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid STATE_ID Value - '.$PK_STATES;
				}
			}
			
		}
	}
	
	if(!empty($DATA->TESTS)){
		foreach($DATA->TESTS as $TESTS) {
			$PASSED = $TESTS->PASSED;
			if(strtolower($PASSED) != 'yes' && strtolower($PASSED) != 'no' && strtolower($PASSED) != '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Invalid PASSED Value';
			}
		}
	}
	
	if(!empty($DATA->ATB_TEST)){
		foreach($DATA->ATB_TEST as $ATB_TEST) {
			$PK_ATB_CODE = $ATB_TEST->ATB_CODE_ID;
			if($PK_ATB_CODE != '') {
				$res_st = $db->Execute("select PK_ATB_CODE from M_ATB_CODE WHERE PK_ATB_CODE = '$PK_ATB_CODE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid ATB_CODE_ID Value - '.$PK_ATB_CODE;
				}
			}
			
			$PK_ATB_TEST_CODE = $ATB_TEST->ATB_TEST_CODE_ID;
			if($PK_ATB_TEST_CODE != '') {
				$res_st = $db->Execute("select PK_ATB_TEST_CODE from M_ATB_TEST_CODE WHERE PK_ATB_TEST_CODE = '$PK_ATB_TEST_CODE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid ATB_TEST_CODE_ID Value - '.$PK_ATB_TEST_CODE;
				}
			}
			
			$PK_ATB_ADMIN_CODE = $ATB_TEST->ATB_ADMIN_CODE_ID;
			if($PK_ATB_ADMIN_CODE != '') {
				$res_st = $db->Execute("select PK_ATB_ADMIN_CODE from M_ATB_ADMIN_CODE WHERE PK_ATB_ADMIN_CODE = '$PK_ATB_ADMIN_CODE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid ATB_ADMIN_CODE_ID Value - '.$PK_ATB_ADMIN_CODE;
				}
			}
			
		}
	}
	
	if(!empty($DATA->ACT_TEST)){
		foreach($DATA->ACT_TEST as $ACT_TEST) {
			//echo "<pre>";print_r($ACT_TEST);exit;
			$PK_ACT_MEASURE = $ATB_TEST->ACT_MEASURE_ID;
			if($PK_ACT_MEASURE != '') {
				$res_st = $db->Execute("select PK_ACT_MEASURE from M_ACT_MEASURE WHERE PK_ACT_MEASURE = '$PK_ACT_MEASURE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid ACT_MEASURE_ID Value - '.$PK_ACT_MEASURE;
				}
			}
		}
	}
	
	if(!empty($DATA->SAT_TEST)){
		foreach($DATA->SAT_TEST as $SAT_TEST) {
			$PK_SAT_MEASURE = $SAT_TEST->SAT_MEASURE_ID;
			if($PK_SAT_MEASURE != '') {
				$res_st = $db->Execute("select PK_SAT_MEASURE from M_SAT_MEASURE WHERE PK_SAT_MEASURE = '$PK_SAT_MEASURE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid SAT_MEASURE_ID Value - '.$PK_SAT_MEASURE;
				}
			}
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$STUDENT_MASTER['FIRST_NAME'] 				= $FIRST_NAME;
		$STUDENT_MASTER['LAST_NAME'] 				= $LAST_NAME;
		$STUDENT_MASTER['MIDDLE_NAME'] 				= $MIDDLE_NAME;
		$STUDENT_MASTER['OTHER_NAME'] 				= $OTHER_NAME;
		$STUDENT_MASTER['SSN_VERIFIED'] 			= $SSN_VERIFIED;
		$STUDENT_MASTER['DATE_OF_BIRTH'] 			= $DATE_OF_BIRTH;
		$STUDENT_MASTER['DRIVERS_LICENSE'] 			= $DRIVERS_LICENSE;
		$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] = $PK_DRIVERS_LICENSE_STATE;
		$STUDENT_MASTER['PK_MARITAL_STATUS']  		= $PK_MARITAL_STATUS;
		$STUDENT_MASTER['GENDER']  					= $GENDER;
		$STUDENT_MASTER['PK_COUNTRY_CITIZEN']  		= $PK_COUNTRY_CITIZEN;
		$STUDENT_MASTER['PK_CITIZENSHIP']  			= $PK_CITIZENSHIP;
		$STUDENT_MASTER['PLACE_OF_BIRTH']  			= $PLACE_OF_BIRTH;
		$STUDENT_MASTER['PK_STATE_OF_RESIDENCY']  	= $PK_STATE_OF_RESIDENCY;
		$STUDENT_MASTER['BADGE_ID']  				= $BADGE_ID;
		$STUDENT_MASTER['OLD_DSIS_STU_NO']  		= $OLD_DSIS_STU_NO;
		$STUDENT_MASTER['OLD_DSIS_LEAD_ID']  		= $OLD_DSIS_LEAD_ID;
		$STUDENT_MASTER['ARCHIVED']  				= $ARCHIVED;
	
		if(!empty($PK_RACE_ARR)){
			$IPEDS_ETHNICITY = '';
			foreach($PK_RACE_ARR as $PK_RACE_1){
				if($PK_RACE_1 == 1) {
					$IPEDS_ETHNICITY = 'Hispanic/Latino';
					break;
				}
			}
			if($IPEDS_ETHNICITY == ''){
				if(count($PK_RACE_ARR) > 1)
					$IPEDS_ETHNICITY = 'Two or more races';
				else {
					$res_l = $db->Execute("select RACE FROM Z_RACE WHERE PK_RACE = '$PK_RACE_ARR[0]'");
					$IPEDS_ETHNICITY = $res_l->fields['RACE'];
				}
			}
		}
		
		$STUDENT_MASTER['IPEDS_ETHNICITY']  		= $IPEDS_ETHNICITY;
		$STUDENT_MASTER['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$STUDENT_MASTER['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
		$PK_STUDENT_MASTER = $db->insert_ID();
		
		if($SSN != '') {
			$SSN1 = preg_replace( '/[^0-9]/', '',$SSN);
			$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
				
			$STUDENT_MASTER1['SSN'] = my_encrypt($PK_ACCOUNT.$PK_EMPLOYEE_MASTER,$SSN1);
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER1, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		}
		
		$res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$PK_ACCOUNT' "); 
		if($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1) {
			$STUDENT_ID = $res_acc->fields['STUD_CODE'].$res_acc->fields['STUD_NO'];
			$STUD_NO = $res_acc->fields['STUD_NO'] + 1;
			$db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$PK_ACCOUNT' "); 
		}
		
		/* Ticket # 1595
		$STUDENT_ACADEMICS['ENTRY_DATE'] 	 			= $ENTRY_DATE;
		$STUDENT_ACADEMICS['ENTRY_TIME'] 	 			= $ENTRY_TIME;
		*/
		//$STUDENT_ACADEMICS['PK_LEAD_CONTACT_SOURCE'] 	= $PK_LEAD_CONTACT_SOURCE;
		$STUDENT_ACADEMICS['ADM_USER_ID']				= $ADM_USER_ID;
		$STUDENT_ACADEMICS['STUDENT_ID']				= $STUDENT_ID;
		$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU']	= $PK_HIGHEST_LEVEL_OF_EDU;
		$STUDENT_ACADEMICS['PREVIOUS_COLLEGE']			= $PREVIOUS_COLLEGE;
		$STUDENT_ACADEMICS['FERPA_BLOCK']				= $FERPA_BLOCK;
		$STUDENT_ACADEMICS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
		$STUDENT_ACADEMICS['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$STUDENT_ACADEMICS['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');
		
		if(!empty($PK_RACE_ARR)){
			foreach($PK_RACE_ARR as $PK_RACE) {
				$STUDENT_RACE['PK_RACE']   			= $PK_RACE;
				$STUDENT_RACE['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
				$STUDENT_RACE['PK_ACCOUNT'] 		= $PK_ACCOUNT;
				$STUDENT_RACE['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_STUDENT_RACE', $STUDENT_RACE, 'insert');
			}
		}
		
		
		
		foreach($DATA->ENROLLMENT as $ENROLLMENT){
			//echo "<pre>";print_r($ENROLLMENT);echo "<br />-------------------------<br />";
			$DSIS_OLD_ENROLLMENT_ID 	= trim($ENROLLMENT->DSIS_OLD_ENROLLMENT_ID);
			$IS_ACTIVE_ENROLLMENT 		= trim($ENROLLMENT->IS_ACTIVE_ENROLLMENT);
			$PK_TERM_MASTER 			= trim($ENROLLMENT->FIRST_TERM_DATE_ID);
			$PK_CAMPUS_PROGRAM 			= trim($ENROLLMENT->PROGRAM_ID);
			$PK_STUDENT_STATUS 			= trim($ENROLLMENT->STATUS_ID);
			$STATUS_DATE 				= trim($ENROLLMENT->STATUS_DATE);
			$PK_REPRESENTATIVE 			= trim($ENROLLMENT->ADMISSION_REP_ID);
			$PK_LEAD_SOURCE 			= trim($ENROLLMENT->LEAD_SOURCE_ID);
			$PK_LEAD_CONTACT_SOURCE 	= trim($ENROLLMENT->CONTACT_SOURCE_ID);
			$CONTRACT_SIGNED_DATE 		= trim($ENROLLMENT->CONTRACT_SIGNED_DATE);
			$CONTRACT_END_DATE 			= trim($ENROLLMENT->CONTRACT_END_DATE);
			$ENTRY_DATE 				= trim($ENROLLMENT->ENTRY_DATE);
			$ENTRY_TIME 				= trim($ENROLLMENT->ENTRY_TIME);
			$EXPECTED_GRAD_DATE 		= trim($ENROLLMENT->EXPECTED_GRAD_DATE);
			$ORIGINAL_EXPECTED_GRAD_DATE = trim($ENROLLMENT->ORIGINAL_EXPECTED_GRAD_DATE);
			$PK_ENROLLMENT_STATUS 		= trim($ENROLLMENT->PART_FULL_TIME_ID);
			$PK_SESSION 				= trim($ENROLLMENT->SESSION_ID);
			$PK_FUNDING 				= trim($ENROLLMENT->FUNDING_ID);
			$PK_STUDENT_GROUP 			= trim($ENROLLMENT->STUDENT_GROUP_ID);
			$ENROLLMENT_PK_TERM_BLOCK 	= trim($ENROLLMENT->TERM_BLOCK_ID);
			$LDA 						= trim($ENROLLMENT->LDA);
			$DETERMINATION_DATE 		= trim($ENROLLMENT->DETERMINATION_DATE);
			$DROP_DATE 					= trim($ENROLLMENT->DROP_DATE);
			$MIDPOINT_DATE 				= trim($ENROLLMENT->MIDPOINT_DATE);
			$PK_PLACEMENT_STATUS		= trim($ENROLLMENT->PLACEMENT_STATUS_ID);
			$PK_SPECIAL					= trim($ENROLLMENT->CEO_CPL_SPECIAL_ID);
			$PK_DROP_REASON				= trim($ENROLLMENT->DROP_REASON_ID);
			$GRADE_DATE					= trim($ENROLLMENT->GRAD_DATE);
			$PK_1098T_REPORTING_TYPE	= trim($ENROLLMENT->_1098T_REPORTING_TYPE_ID);
			$FT_PT_EFFECTIVE_DATE		= trim($ENROLLMENT->FT_PT_EFFECTIVE_DATE);
			$PK_DISTANCE_LEARNING		= trim($ENROLLMENT->DISTANCE_LEARNING_ID);
			$FIRST_TERM					= trim($ENROLLMENT->IPEDS_ENROLLMENT_STATUS_ID);
			$STRF_PAID_DATE				= trim($ENROLLMENT->STRF_PAID_DATE);
			
			if($PK_STUDENT_STATUS == '') {
				$res_st = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				$PK_STUDENT_STATUS = $res_st->fields['PK_STUDENT_STATUS'];
			} 
		
			if(strtolower($IS_ACTIVE_ENROLLMENT) == 'yes') {
				$db->Execute("UPDATE S_STUDENT_ENROLLMENT SET IS_ACTIVE_ENROLLMENT = 0 WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
				
				$IS_ACTIVE_ENROLLMENT = 1;
			} else if(strtolower($IS_ACTIVE_ENROLLMENT) == 'no')
				$IS_ACTIVE_ENROLLMENT = 0;
			else {
				$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_ACTIVE_ENROLLMENT = 1 "); 
				if($res_en->RecordCount() == 0)
					$IS_ACTIVE_ENROLLMENT = 1;
				else
					$IS_ACTIVE_ENROLLMENT = 0;
			}
			
			/* Ticket # 1595 */
			$STUDENT_ENROLLMENT['ENTRY_DATE'] 	 			= $ENTRY_DATE;
			$STUDENT_ENROLLMENT['ENTRY_TIME'] 	 			= $ENTRY_TIME;
			/* Ticket # 1595 */
			$STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] 	= $IS_ACTIVE_ENROLLMENT;
			$STUDENT_ENROLLMENT['DSIS_OLD_ENROLLMENT_ID'] 	= $DSIS_OLD_ENROLLMENT_ID;
			$STUDENT_ENROLLMENT['CONTRACT_SIGNED_DATE'] 	= $CONTRACT_SIGNED_DATE;
			$STUDENT_ENROLLMENT['CONTRACT_END_DATE'] 	 	= $CONTRACT_END_DATE;
			$STUDENT_ENROLLMENT['EXPECTED_GRAD_DATE'] 	 	= $EXPECTED_GRAD_DATE;
			$STUDENT_ENROLLMENT['FIRST_TERM'] 				= $FIRST_TERM; //Ticket #971
			$STUDENT_ENROLLMENT['ORIGINAL_EXPECTED_GRAD_DATE'] 	= $ORIGINAL_EXPECTED_GRAD_DATE;
			$STUDENT_ENROLLMENT['PK_ENROLLMENT_STATUS'] 	= $PK_ENROLLMENT_STATUS;
			$STUDENT_ENROLLMENT['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
			$STUDENT_ENROLLMENT['PK_TERM_MASTER'] 	 		= $PK_TERM_MASTER;
			$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] 	 	= $PK_CAMPUS_PROGRAM;
			$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] 	 	= $PK_STUDENT_STATUS;
			$STUDENT_ENROLLMENT['STATUS_DATE'] 	 			= $STATUS_DATE;
			$STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] 	 	= $PK_REPRESENTATIVE;
			$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] 	 		= $PK_LEAD_SOURCE;
			$STUDENT_ENROLLMENT['PK_SESSION'] 	 			= $PK_SESSION;
			$STUDENT_ENROLLMENT['PK_FUNDING'] 	 			= $PK_FUNDING;
			$STUDENT_ENROLLMENT['PK_STUDENT_GROUP'] 	 	= $PK_STUDENT_GROUP;
			$STUDENT_ENROLLMENT['ENROLLMENT_PK_TERM_BLOCK'] = $ENROLLMENT_PK_TERM_BLOCK;
			$STUDENT_ENROLLMENT['LDA'] 						= $LDA;
			$STUDENT_ENROLLMENT['DETERMINATION_DATE'] 		= $DETERMINATION_DATE;
			$STUDENT_ENROLLMENT['DROP_DATE'] 				= $DROP_DATE;
			$STUDENT_ENROLLMENT['MIDPOINT_DATE'] 			= $MIDPOINT_DATE;
			$STUDENT_ENROLLMENT['PK_PLACEMENT_STATUS'] 		= $PK_PLACEMENT_STATUS;
			$STUDENT_ENROLLMENT['PK_SPECIAL'] 				= $PK_SPECIAL;
			$STUDENT_ENROLLMENT['PK_DROP_REASON'] 			= $PK_DROP_REASON;
			$STUDENT_ENROLLMENT['GRADE_DATE'] 				= $GRADE_DATE;
			
			$STUDENT_ENROLLMENT['PK_1098T_REPORTING_TYPE'] 	= $PK_1098T_REPORTING_TYPE;
			$STUDENT_ENROLLMENT['FT_PT_EFFECTIVE_DATE'] 	= $FT_PT_EFFECTIVE_DATE;
			$STUDENT_ENROLLMENT['FIRST_TERM'] 				= $FIRST_TERM;
			$STUDENT_ENROLLMENT['PK_DISTANCE_LEARNING'] 	= $PK_DISTANCE_LEARNING;
			$STUDENT_ENROLLMENT['STRF_PAID_DATE'] 			= $STRF_PAID_DATE;

			$STUDENT_ENROLLMENT['REENTRY'] 					= $REENTRY;
			$STUDENT_ENROLLMENT['TRANSFER_IN'] 				= $TRANSFER_IN;
			$STUDENT_ENROLLMENT['TRANSFER_OUT'] 			= $TRANSFER_OUT;

			//$STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] 	= 1;
			$STUDENT_ENROLLMENT['PK_ACCOUNT']  		 		= $PK_ACCOUNT;
			$STUDENT_ENROLLMENT['CREATED_ON']  		 		= date("Y-m-d H:i");
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'insert');
			$PK_STUDENT_ENROLLMENT = $db->insert_ID();

			$STUDENT_STATUS_LOG['PK_STUDENT_STATUS'] 		= $STUDENT_ENROLLMENT['PK_STUDENT_STATUS'];
			$STUDENT_STATUS_LOG['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
			$STUDENT_STATUS_LOG['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
			$STUDENT_STATUS_LOG['PK_ACCOUNT']  				= $PK_ACCOUNT;
			$STUDENT_STATUS_LOG['CHANGED_ON']  				= date("Y-m-d H:i");
			db_perform('S_STUDENT_STATUS_LOG', $STUDENT_STATUS_LOG, 'insert');
			
			if(!empty($ENROLLMENT->LOA)){
				foreach($ENROLLMENT->LOA as $LOA) {
					$STUDENT_LOA['PK_DEPARTMENT']  			= $LOA->DEPARTMENT_ID;
					$STUDENT_LOA['BEGIN_DATE']  			= $LOA->BEGIN_DATE;
					$STUDENT_LOA['END_DATE']  				= $LOA->END_DATE;
					$STUDENT_LOA['REASON']  				= $LOA->REASON;
					$STUDENT_LOA['NOTES']  					= $LOA->NOTES;
					$STUDENT_LOA['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
					$STUDENT_LOA['PK_ACCOUNT']   			= $PK_ACCOUNT;
					$STUDENT_LOA['PK_STUDENT_MASTER']  		= $PK_STUDENT_MASTER;
					$STUDENT_LOA['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_STUDENT_LOA', $STUDENT_LOA, 'insert');
				}
			}
			
			if(!empty($ENROLLMENT->CUSTOM_FIELDS)){
				foreach($ENROLLMENT->CUSTOM_FIELDS as $CUSTOM_FIELDS) {
					$res_st = $db->Execute("select FIELD_NAME from S_CUSTOM_FIELDS WHERE PK_CUSTOM_FIELDS = '".$CUSTOM_FIELDS->CUSTOM_FIELDS_ID."' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					
					$CUSTOM_FIELDS_ARR = array();
					$CUSTOM_FIELDS_ARR['PK_ACCOUNT'] 		 	= $PK_ACCOUNT;
					$CUSTOM_FIELDS_ARR['PK_STUDENT_MASTER']  	= $PK_STUDENT_MASTER;
					$CUSTOM_FIELDS_ARR['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
					$CUSTOM_FIELDS_ARR['PK_CUSTOM_FIELDS'] 	 	= $CUSTOM_FIELDS->CUSTOM_FIELDS_ID;
					$CUSTOM_FIELDS_ARR['FIELD_VALUE'] 		 	= $CUSTOM_FIELDS->FIELD_VALUE;
					$CUSTOM_FIELDS_ARR['FIELD_NAME'] 		 	= $res_st->fields['FIELD_NAME'];
					$CUSTOM_FIELDS_ARR['CREATED_ON']  		 	= date("Y-m-d H:i");
					db_perform('S_STUDENT_CUSTOM_FIELDS', $CUSTOM_FIELDS_ARR, 'insert');
				}
			}
			
			if(!empty($ENROLLMENT->PROBATION)){
				foreach($ENROLLMENT->PROBATION as $PROBATION) {
					$STUDENT_PROBATION['PK_DEPARTMENT']  			= $PROBATION->DEPARTMENT_ID;
					$STUDENT_PROBATION['PK_PROBATION_TYPE']  		= $PROBATION->PROBATION_TYPE_ID;
					$STUDENT_PROBATION['PK_PROBATION_LEVEL']  		= $PROBATION->PROBATION_LEVEL_ID;
					$STUDENT_PROBATION['PK_PROBATION_STATUS']  		= $PROBATION->PROBATION_STATUS_ID;
					$STUDENT_PROBATION['BEGIN_DATE']  				= $PROBATION->BEGIN_DATE;
					$STUDENT_PROBATION['END_DATE']  				= $PROBATION->END_DATE;
					$STUDENT_PROBATION['REASON']  					= $PROBATION->REASON;
					$STUDENT_PROBATION['NOTES']  					= $PROBATION->NOTES;
					$STUDENT_PROBATION['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
					$STUDENT_PROBATION['PK_ACCOUNT']   				= $PK_ACCOUNT;
					$STUDENT_PROBATION['PK_STUDENT_MASTER']  		= $PK_STUDENT_MASTER;
					$STUDENT_PROBATION['CREATED_ON']  				= date("Y-m-d H:i");
					db_perform('S_STUDENT_PROBATION', $STUDENT_PROBATION, 'insert');
				}
			}
			
			if(!empty($ENROLLMENT->TASK)){
				foreach($ENROLLMENT->TASK as $TASK) {
					$COMPLETED = $TASK->COMPLETED;
					if(strtolower($COMPLETED) == 'yes')
						$COMPLETED = 1;
					else if(strtolower($COMPLETED) == 'no')
						$COMPLETED = 0;
					else
						$COMPLETED = 0;
					
					$STUDENT_TASK['PK_DEPARTMENT']  			= $TASK->DEPARTMENT_ID;
					$STUDENT_TASK['PK_TASK_STATUS']  			= $TASK->TASK_STATUS_ID;
					$STUDENT_TASK['PK_EVENT_OTHER']  			= $TASK->TASK_OTHER_ID;
					$STUDENT_TASK['TASK_DATE']  				= $TASK->TASK_DATE;
					$STUDENT_TASK['TASK_TIME'] 					= $TASK->TASK_TIME;
					$STUDENT_TASK['PK_TASK_TYPE']  				= $TASK->TASK_TYPE_ID;
					$STUDENT_TASK['PK_NOTES_PRIORITY_MASTER']  	= $TASK->PRIORITY_ID;
					$STUDENT_TASK['PK_EMPLOYEE_MASTER']  		= $TASK->EMPLOYEE_ID;
					$STUDENT_TASK['FOLLOWUP_DATE']  			= $TASK->FOLLOWUP_DATE;
					$STUDENT_TASK['FOLLOWUP_TIME']  			= $TASK->FOLLOWUP_TIME;
					$STUDENT_TASK['COMPLETED']  				= $COMPLETED;
					$STUDENT_TASK['NOTES']  					= $TASK->COMMENTS;
					$STUDENT_TASK['PK_STUDENT_ENROLLMENT'] 		= $PK_STUDENT_ENROLLMENT;
					$STUDENT_TASK['PK_ACCOUNT']   				= $PK_ACCOUNT;
					$STUDENT_TASK['PK_STUDENT_MASTER']  		= $PK_STUDENT_MASTER;
					$STUDENT_TASK['CREATED_ON']  				= date("Y-m-d H:i");
					db_perform('S_STUDENT_TASK', $STUDENT_TASK, 'insert');
					
				}
			}
			
			if(!empty($ENROLLMENT->NOTES)){
				foreach($ENROLLMENT->NOTES as $NOTES) {
			
					$COMPLETED = $NOTES->COMPLETED;
					if(strtolower($COMPLETED) == 'yes')
						$COMPLETED = 1;
					else if(strtolower($COMPLETED) == 'no')
						$COMPLETED = 0;
					else
						$COMPLETED = 0;
					
					$STUDENT_NOTES = array();
					$STUDENT_NOTES['PK_ACCOUNT']   			= $PK_ACCOUNT;
					$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
					$STUDENT_NOTES['PK_DEPARTMENT'] 		= $NOTES->DEPARTMENT_ID;
					$STUDENT_NOTES['PK_NOTE_STATUS'] 		= $NOTES->NOTE_STATUS_ID;
					$STUDENT_NOTES['PK_NOTE_TYPE'] 			= $NOTES->NOTE_TYPE_ID;
					$STUDENT_NOTES['NOTE_DATE'] 			= $NOTES->NOTE_DATE;
					$STUDENT_NOTES['NOTE_TIME'] 			= $NOTES->NOTE_TIME;
					$STUDENT_NOTES['FOLLOWUP_DATE'] 		= $NOTES->FOLLOWUP_DATE;
					$STUDENT_NOTES['FOLLOWUP_TIME'] 		= $NOTES->FOLLOWUP_TIME;
					$STUDENT_NOTES['PK_EMPLOYEE_MASTER'] 	= $NOTES->EMPLOYEE_ID;
					$STUDENT_NOTES['SATISFIED'] 			= $COMPLETED;
					$STUDENT_NOTES['NOTES'] 				= $NOTES->COMMENTS;
					$STUDENT_NOTES['IS_EVENT'] 				= 0;
					$STUDENT_NOTES['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');
				}
			}
			
			if(!empty($ENROLLMENT->EVENTS)){
				foreach($ENROLLMENT->EVENTS as $EVENTS) {

					$COMPLETED = $TASK->COMPLETED;
					if(strtolower($COMPLETED) == 'yes')
						$COMPLETED = 1;
					else if(strtolower($COMPLETED) == 'no')
						$COMPLETED = 0;
					else
						$COMPLETED = 0;
					
					$STUDENT_NOTES = array();
					$STUDENT_NOTES['PK_ACCOUNT']   			= $PK_ACCOUNT;
					$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
					$STUDENT_NOTES['PK_DEPARTMENT'] 		= $EVENTS->DEPARTMENT_ID;
					$STUDENT_NOTES['PK_NOTE_STATUS'] 		= $EVENTS->EVENTS_STATUS_ID;
					$STUDENT_NOTES['PK_NOTE_TYPE'] 			= $EVENTS->EVENTS_TYPE_ID;
					$STUDENT_NOTES['PK_EVENT_OTHER'] 		= $EVENTS->EVENTS_OTHER_ID;
					$STUDENT_NOTES['NOTE_DATE'] 			= $EVENTS->EVENTS_DATE;
					$STUDENT_NOTES['NOTE_TIME'] 			= $EVENTS->EVENTS_TIME;
					$STUDENT_NOTES['FOLLOWUP_DATE'] 		= $EVENTS->FOLLOWUP_DATE;
					$STUDENT_NOTES['FOLLOWUP_TIME'] 		= $EVENTS->FOLLOWUP_TIME;
					$STUDENT_NOTES['PK_EMPLOYEE_MASTER'] 	= $EVENTS->EMPLOYEE_ID;
					$STUDENT_NOTES['SATISFIED'] 			= $COMPLETED;
					$STUDENT_NOTES['NOTES'] 				= $EVENTS->COMMENTS;
					$STUDENT_NOTES['IS_EVENT'] 				= 1;
					$STUDENT_NOTES['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');
				}
			}
			
			if(!empty($ENROLLMENT->DOCUMENTS)){
				foreach($ENROLLMENT->DOCUMENTS as $DOCUMENTS) {
				
					$RECEIVED = $DOCUMENTS->RECEIVED;
					if(strtolower($RECEIVED) == 'yes')
						$RECEIVED = 1;
					else if(strtolower($RECEIVED) == 'no')
						$RECEIVED = 0;
					else
						$RECEIVED = 0;
						
					if($DOCUMENTS->DOCUMENT_TYPE == '') {
						$res_st = $db->Execute("select DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE PK_DOCUMENT_TYPE = '".$DOCUMENTS->DOCUMENT_TYPE_ID."' ");
						$DOCUMENT_TYPE = $res_st->fields['DOCUMENT_TYPE'];
					} else
						$DOCUMENT_TYPE = $DOCUMENTS->DOCUMENT_TYPE;
				
					$COPY_TO		= '';
					$DOCUMENT_URL 	= $DOCUMENTS->DOCUMENT_URL;
					if($DOCUMENT_URL != '') {
						//$file_dir_1 = '../backend_assets/school/school_'.$PK_ACCOUNT.'/student/';
						$file_dir_1 = '../backend_assets/tmp_upload/';
						
						$DOCUMENT_TYPE = str_replace("/","-",$_POST['DOCUMENT_TYPE']);
						$DOCUMENT_TYPE = str_replace("\\","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace("&","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace("*","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace(":","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace("?","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace("<","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace(">","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace("|","-",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace(" ","_",$DOCUMENT_TYPE);
						$DOCUMENT_TYPE = str_replace("=","_",$DOCUMENT_TYPE);

						$extn 			= explode(".",$DOCUMENTS->DOCUMENT_NAME);
						$iindex			= count($extn) - 1;
						$rand_string 	= time()."_".rand(10000,99999);
						$file11			= $PK_STUDENT_MASTER.'_'.$DOCUMENT_TYPE.'_'.$rand_string.".".$extn[$iindex];	
						$extension   	= strtolower($extn[$iindex]);
						$COPY_TO		= $file_dir_1.$file11;
						
						file_put_contents("../".$COPY_TO, fopen($DOCUMENT_URL, 'r'));
					}
					
					$STUDENT_DOCUMENTS['PK_EMPLOYEE_MASTER']  	= $DOCUMENTS->EMPLOYEE_ID;
					$STUDENT_DOCUMENTS['PK_DOCUMENT_TYPE']  	= $DOCUMENTS->DOCUMENT_TYPE_ID;
					$STUDENT_DOCUMENTS['DOCUMENT_TYPE']  		= $DOCUMENTS->DOCUMENT_TYPE;
					$STUDENT_DOCUMENTS['REQUESTED_DATE']  		= $DOCUMENTS->REQUESTED_DATE;
					$STUDENT_DOCUMENTS['RECEIVED']  			= $RECEIVED;
					$STUDENT_DOCUMENTS['DATE_RECEIVED']  		= $DOCUMENTS->DATE_RECEIVED;
					$STUDENT_DOCUMENTS['FOLLOWUP_DATE']  		= $DOCUMENTS->FOLLOWUP_DATE;
					$STUDENT_DOCUMENTS['NOTES']  				= $DOCUMENTS->NOTES;
					$STUDENT_DOCUMENTS['DOCUMENT_NAME']  		= $DOCUMENTS->DOCUMENT_NAME;
					$STUDENT_DOCUMENTS['DOCUMENT_PATH']  		= $COPY_TO;
					
					$STUDENT_DOCUMENTS['PK_ACCOUNT']  			= $PK_ACCOUNT;
					$STUDENT_DOCUMENTS['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$STUDENT_DOCUMENTS['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
					$STUDENT_DOCUMENTS['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_STUDENT_DOCUMENTS', $STUDENT_DOCUMENTS, 'insert');
					$PK_STUDENT_DOCUMENTS = $db->insert_ID();
					
					foreach($DOCUMENTS->DEPARTMENTS as $PK_DEPARTMENT){
						$STUDENT_DOCUMENTS_DEPARTMENT['PK_DEPARTMENT']   		= $PK_DEPARTMENT;
						$STUDENT_DOCUMENTS_DEPARTMENT['PK_STUDENT_DOCUMENTS'] 	= $PK_STUDENT_DOCUMENTS;
						$STUDENT_DOCUMENTS_DEPARTMENT['PK_ACCOUNT'] 			= $PK_ACCOUNT;
						$STUDENT_DOCUMENTS_DEPARTMENT['CREATED_ON']  			= date("Y-m-d H:i");
						db_perform('S_STUDENT_DOCUMENTS_DEPARTMENT', $STUDENT_DOCUMENTS_DEPARTMENT, 'insert');
					}
				}
			}
			
			if(!empty($ENROLLMENT->REQUIREMENT)){
				foreach($ENROLLMENT->REQUIREMENT as $REQUIREMENT) {
					
					$MANDATORY = $REQUIREMENT->MANDATORY;
					if(strtolower($MANDATORY) == 'yes')
						$MANDATORY = 1;
					else if(strtolower($MANDATORY) == 'no')
						$MANDATORY = 0;
					else
						$MANDATORY = 0;
						
					$COMPLETED = $REQUIREMENT->COMPLETED;
					if(strtolower($COMPLETED) == 'yes')
						$COMPLETED = 1;
					else if(strtolower($COMPLETED) == 'no')
						$COMPLETED = 0;
					else
						$COMPLETED = 0;

					$REQUIREMENT_TYPE = $REQUIREMENT->REQUIREMENT_TYPE;
					if(strtolower($REQUIREMENT_TYPE) == 'school')
						$REQUIREMENT_TYPE = 1;
					else if(strtolower($REQUIREMENT_TYPE) == 'program')
						$REQUIREMENT_TYPE = 0;
					else
						$REQUIREMENT_TYPE = 0;
						
					$STUDENT_REQUIREMENT['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
					$STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
					$STUDENT_REQUIREMENT['TYPE'] 				  	= $REQUIREMENT_TYPE;
					$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $REQUIREMENT->REQUIREMENT;
					$STUDENT_REQUIREMENT['COMPLETED_ON'] 			= $REQUIREMENT->COMPLETED_ON;
					$STUDENT_REQUIREMENT['COMPLETED_BY'] 			= $REQUIREMENT->COMPLETED_BY;
					$STUDENT_REQUIREMENT['MANDATORY'] 				= $MANDATORY;
					$STUDENT_REQUIREMENT['COMPLETED'] 				= $COMPLETED;
					$STUDENT_REQUIREMENT['PK_ACCOUNT']  			= $PK_ACCOUNT;
					$STUDENT_REQUIREMENT['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert');
					
				}
			}
			
			if(!empty($ENROLLMENT->QUESTIONNAIRE)){
				foreach($ENROLLMENT->QUESTIONNAIRE as $QUESTIONNAIRE) {
					$STUDENT_QUESTIONNAIRE['ANSWER'] 				 = $QUESTIONNAIRE->ANSWER;
					$STUDENT_QUESTIONNAIRE['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
					$STUDENT_QUESTIONNAIRE['PK_QUESTIONNAIRE']  	 = $QUESTIONNAIRE->QUESTION_ID;
					$STUDENT_QUESTIONNAIRE['PK_STUDENT_MASTER'] 	 = $PK_STUDENT_MASTER;
					$STUDENT_QUESTIONNAIRE['PK_ACCOUNT'] 			 = $PK_ACCOUNT;
					$STUDENT_QUESTIONNAIRE['CREATED_ON']  			 = date("Y-m-d H:i");
					db_perform('S_STUDENT_QUESTIONNAIRE', $STUDENT_QUESTIONNAIRE, 'insert');
				}
			}
			
			if(!empty($ENROLLMENT->CAMPUS)){
				foreach($ENROLLMENT->CAMPUS as $PK_CAMPUS) {
					$STUDENT_CAMPUS['PK_CAMPUS']   				= $PK_CAMPUS;
					$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
					$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT']  	= $PK_STUDENT_ENROLLMENT;
					$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $PK_ACCOUNT;
					$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
					db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
				}
			}
			
			///////////////////////////////////
			if(!empty($ENROLLMENT->COURSE_OFFERING)){
				$i = 0;
				$res_def_grade 	= $db->Execute("SELECT PK_GRADE  FROM S_GRADE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND IS_DEFAULT = 1 ");
				foreach($ENROLLMENT->COURSE_OFFERING as $COURSE_OFFERING) {
					$PK_COURSE_OFFERING = $COURSE_OFFERING->ID;
					
					$res1 	= $db->Execute("SELECT PK_TERM_MASTER, UNITS FROM S_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING.PK_ACCOUNT = '$PK_ACCOUNT'");
					
					$j = $i + 1;
					$STUDENT_COURSE = array();
					$STUDENT_COURSE['PK_TERM_MASTER'] 		 				= $res1->fields['PK_TERM_MASTER'];
					$STUDENT_COURSE['PK_COURSE_OFFERING'] 	 				= $PK_COURSE_OFFERING;
					$STUDENT_COURSE['PROGRAM_COURSE_ORDER']  				= $j;
					$STUDENT_COURSE['COURSE_UNITS'] 		 				= $res1->fields['UNITS'];
					$STUDENT_COURSE['FINAL_GRADE'] 			 				= $res_def_grade->fields['PK_GRADE'];
					$STUDENT_COURSE['PK_STUDENT_ENROLLMENT'] 				= $PK_STUDENT_ENROLLMENT;
					$STUDENT_COURSE['PK_STUDENT_MASTER'] 	 				= $PK_STUDENT_MASTER;
					$STUDENT_COURSE['PK_ACCOUNT'] 			 				= $PK_ACCOUNT;
					$STUDENT_COURSE['CREATED_ON']  			 				= date("Y-m-d H:i");
					$STUDENT_COURSE['NUMERIC_GRADE'] 		 				= $COURSE_OFFERING->NUMERIC_GRADE;
					$STUDENT_COURSE['PK_COURSE_OFFERING_STUDENT_STATUS'] 	= $COURSE_OFFERING->COURSE_OFFERING_STUDENT_STATUS;
					
					if($STUDENT_COURSE['PK_COURSE_OFFERING_STUDENT_STATUS'] == '') {
						$res_sts1 = $db->Execute("SELECT PK_COURSE_OFFERING_STUDENT_STATUS FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE MAKE_AS_DEFAULT = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' "); 
						$STUDENT_COURSE['PK_COURSE_OFFERING_STUDENT_STATUS'] = $res_sts1->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
					}
					
					if($COURSE_OFFERING->FINAL_GRADE_ID != '') {
						$STUDENT_COURSE['FINAL_GRADE'] 						= $COURSE_OFFERING->FINAL_GRADE_ID;
						$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$STUDENT_COURSE[FINAL_GRADE]' "); 
						$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
						$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
						$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
						$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
						$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
						$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
						$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
						$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
					}
					db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'insert');
					$PK_STUDENT_COURSE = $db->insert_ID();
					
					$db->Execute("UPDATE S_STUDENT_ENROLLMENT SET COURSE_ASSIGNED = 1 WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
					
					$res_grade = $db->Execute("SELECT * FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING='$PK_COURSE_OFFERING' AND PK_ACCOUNT='$PK_ACCOUNT'"); 
					while (!$res_grade->EOF) {
						$STUDENT_GRADE = array();
						$STUDENT_GRADE['PK_COURSE_OFFERING_GRADE'] 	= $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
						$STUDENT_GRADE['PK_COURSE_OFFERING']		= $PK_COURSE_OFFERING;
						$STUDENT_GRADE['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
						$STUDENT_GRADE['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
						$STUDENT_GRADE['PK_ACCOUNT'] 			 	= $PK_ACCOUNT;
						$STUDENT_GRADE['CREATED_ON']  			 	= date("Y-m-d H:i");
						db_perform('S_STUDENT_GRADE', $STUDENT_GRADE, 'insert');
						
						$res_grade->MoveNext();
					}
					
					if($COURSE_OFFERING->COURSE_OFFERING_GRADE_BOOK != '') {
						foreach($COURSE_OFFERING->COURSE_OFFERING_GRADE_BOOK  as $COURSE_OFFERING_GRADE_BOOK ) {
							$PK_COURSE_OFFERING_GRADE 	= $COURSE_OFFERING_GRADE_BOOK->ID;
							$POINTS 					= $COURSE_OFFERING_GRADE_BOOK->POINTS;
							
							$res_grade = $db->Execute("SELECT PK_STUDENT_GRADE FROM S_STUDENT_GRADE WHERE PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
							if($res_grade->RecordCount() == 0) {
								$STUDENT_GRADE = array();
								$STUDENT_GRADE['PK_COURSE_OFFERING_GRADE'] 	= $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
								$STUDENT_GRADE['POINTS'] 					= $POINTS;
								$STUDENT_GRADE['PK_COURSE_OFFERING']		= $PK_COURSE_OFFERING;
								$STUDENT_GRADE['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
								$STUDENT_GRADE['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
								$STUDENT_GRADE['PK_ACCOUNT'] 			 	= $PK_ACCOUNT;
								$STUDENT_GRADE['CREATED_ON']  			 	= date("Y-m-d H:i");
								db_perform('S_STUDENT_GRADE', $STUDENT_GRADE, 'insert');
								$PK_STUDENT_GRADE = $db->insert_ID();
							} else {
								$PK_STUDENT_GRADE 		 = $res_grade->fields['PK_STUDENT_GRADE'];
								$STUDENT_GRADE 			 = array();
								$STUDENT_GRADE['POINTS'] = $POINTS;
								db_perform('S_STUDENT_GRADE', $STUDENT_GRADE, 'update'," PK_STUDENT_GRADE = '$PK_STUDENT_GRADE' ");
							}
						}
						
						$TOTAL_POINTS = 0;
						$res_grade = $db->Execute("SELECT S_STUDENT_GRADE.POINTS, WEIGHT FROM S_STUDENT_GRADE,S_COURSE_OFFERING_GRADE where S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
						while (!$res_grade->EOF) {
							$TOTAL_POINTS += ($res_grade->fields['POINTS'] * $res_grade->fields['WEIGHT']);
							$res_grade->MoveNext();
						}
						
						$MAX_CURRENT_POINTS = 0;
						$res_grade = $db->Execute("SELECT SUM(WEIGHTED_POINTS) as WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE,S_STUDENT_GRADE WHERE S_STUDENT_GRADE.PK_ACCOUNT = '$PK_ACCOUNT' AND S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE = S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
						$MAX_CURRENT_POINTS = $res_grade->fields['WEIGHTED_POINTS'];

						$MAX_FINAL_POINTS = 0;
						$res_grade = $db->Execute("SELECT SUM(WEIGHTED_POINTS) as WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' "); 
						$MAX_FINAL_POINTS = $res_grade->fields['WEIGHTED_POINTS'];

						if($MAX_CURRENT_POINTS > 0)
							$CURRENT_PERCENTAGE = number_format_value_checker(($TOTAL_POINTS / $MAX_CURRENT_POINTS * 100),2);
						else
							$CURRENT_PERCENTAGE = 0;
							
						if($MAX_FINAL_POINTS > 0)
							$FINAL_PERCENTAGE = number_format_value_checker(($TOTAL_POINTS / $MAX_FINAL_POINTS * 100),2);
						else
							$FINAL_PERCENTAGE = 0;
							
						$res_1 = $db->Execute("SELECT PK_GRADE_SCALE_MASTER FROM S_STUDENT_COURSE, S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM  "); 
						$PK_GRADE_SCALE_MASTER  = $res_1->fields['PK_GRADE_SCALE_MASTER'];
							
						$res_1 = $db->Execute("SELECT PK_GRADE_SCALE_DETAIL,GRADE,S_GRADE.PK_GRADE FROM S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE WHERE S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' AND MAX_PERCENTAGE >= '$CURRENT_PERCENTAGE' AND MIN_PERCENTAGE <= '$CURRENT_PERCENTAGE' "); 
						
						$res_2 = $db->Execute("SELECT PK_GRADE_SCALE_DETAIL,GRADE,S_GRADE.PK_GRADE FROM S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE  WHERE S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' AND MAX_PERCENTAGE >= '$FINAL_PERCENTAGE' AND MIN_PERCENTAGE <= '$FINAL_PERCENTAGE' "); 
						
						$STUDENT_COURSE = array();
						$STUDENT_COURSE['FINAL_TOTAL_OBTAINED'] 	= $TOTAL_POINTS;
						$STUDENT_COURSE['FINAL_MAX_TOTAL'] 			= $MAX_FINAL_POINTS;
						$STUDENT_COURSE['FINAL_TOTAL_GRADE'] 		= $res_2->fields['PK_GRADE'];
						
						$STUDENT_COURSE['CURRENT_TOTAL_OBTAINED'] 	= $TOTAL_POINTS;
						$STUDENT_COURSE['CURRENT_MAX_TOTAL'] 		= $MAX_CURRENT_POINTS;
						$STUDENT_COURSE['CURRENT_TOTAL_GRADE'] 		= $res_1->fields['PK_GRADE'];
						
						db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update', " PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");
					}
					
					if(strtolower($COURSE_OFFERING->CREATE_SCHEDULED_ATTENDANCE) == 'yes') {
						$res_sch = $db->Execute("SELECT * FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$PK_ACCOUNT' "); 
						while (!$res_sch->EOF) {
							$STUDENT_SCHEDULE['SCHEDULE_DATE'] 	 	 	 			= $res_sch->fields['SCHEDULE_DATE'];
							$STUDENT_SCHEDULE['START_TIME'] 	 	 	 			= $res_sch->fields['START_TIME'];
							$STUDENT_SCHEDULE['END_TIME'] 	 	 		 			= $res_sch->fields['END_TIME'];
							$STUDENT_SCHEDULE['HOURS'] 	 	 			 			= $res_sch->fields['HOURS'];
							$STUDENT_SCHEDULE['PK_CAMPUS_ROOM'] 	 	 			= $res_sch->fields['PK_CAMPUS_ROOM'];
							$STUDENT_SCHEDULE['PK_STUDENT_COURSE'] 	 	 			= $PK_STUDENT_COURSE;
							$STUDENT_SCHEDULE['PK_COURSE_OFFERING_SCHEDULE_DETAIL']	= $res_sch->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
							$STUDENT_SCHEDULE['PK_SCHEDULE_TYPE'] 	 				= 1;
							$STUDENT_SCHEDULE['PK_STUDENT_ENROLLMENT'] 	 			= $PK_STUDENT_ENROLLMENT;
							$STUDENT_SCHEDULE['PK_STUDENT_MASTER'] 	 	 			= $PK_STUDENT_MASTER;
							$STUDENT_SCHEDULE['PK_ACCOUNT'] 			 			= $PK_ACCOUNT;
							$STUDENT_SCHEDULE['CREATED_ON']  			 			= date("Y-m-d H:i");
							db_perform('S_STUDENT_SCHEDULE', $STUDENT_SCHEDULE, 'insert');
							
							$res_sch->MoveNext();
						}
					}
					
					if(!empty($COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE)){
						foreach($COURSE_OFFERING->NON_SCHEDULED_ATTENDANCE as $NON_SCHEDULED_ATTENDANCE) {
							$DATE 				= $NON_SCHEDULED_ATTENDANCE->DATE;
							$START_TIME 		= $NON_SCHEDULED_ATTENDANCE->START_TIME.':00';
							$END_TIME 			= $NON_SCHEDULED_ATTENDANCE->END_TIME.':00';
							$HOURS 				= $NON_SCHEDULED_ATTENDANCE->HOURS;
							$ATTENDED_HOUR 		= $NON_SCHEDULED_ATTENDANCE->ATTENDED_HOUR;
							$PK_ATTENDANCE_CODE = $NON_SCHEDULED_ATTENDANCE->ATTENDANCE_CODE_ID;
							$COMPLETED 			= $NON_SCHEDULED_ATTENDANCE->COMPLETED;
							
							if(strtolower($COMPLETED) == 'yes')
								$COMPLETED = 1;
							else
								$COMPLETED = 0;
							
							$PK_STUDENT_SCHEDULE = create_non_schedule('',$PK_COURSE_OFFERING,$DATE,$START_TIME,$END_TIME,$HOURS,$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT, 1,$PK_ACCOUNT,0);
							
							attendance_entry($PK_COURSE_OFFERING_SCHEDULE_DETAIL,$COMPLETED,'',$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$ATTENDED_HOUR, $PK_ATTENDANCE_CODE,$PK_ACCOUNT,0);
						}
					}
					
					if(!empty($COURSE_OFFERING->ATTENDANCE)){
						foreach($COURSE_OFFERING->ATTENDANCE as $ATTENDANCE) {
							$DATE 				= $ATTENDANCE->DATE;
							$START_TIME 		= $ATTENDANCE->START_TIME.':00';
							$ATTENDED_HOUR 		= $ATTENDANCE->ATTENDED_HOUR;
							$PK_ATTENDANCE_CODE = $ATTENDANCE->ATTENDANCE_CODE_ID;
							$COMPLETED 			= $ATTENDANCE->COMPLETED;
							
							if(strtolower($COMPLETED) == 'yes')
								$COMPLETED = 1;
							else
								$COMPLETED = 0;
							
							$res_sch = $db->Execute("SELECT PK_STUDENT_SCHEDULE, PK_COURSE_OFFERING_SCHEDULE_DETAIL FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_ACCOUNT = '$PK_ACCOUNT' AND SCHEDULE_DATE = '$DATE' AND START_TIME = '$START_TIME' "); 
							if($res_sch->RecordCount() > 0){
								$PK_STUDENT_SCHEDULE = $res_sch->fields['PK_STUDENT_SCHEDULE'];
								$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_sch->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
								
								attendance_entry($PK_COURSE_OFFERING_SCHEDULE_DETAIL,$COMPLETED,'',$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$ATTENDED_HOUR, $PK_ATTENDANCE_CODE,$PK_ACCOUNT,0);
								
							}
						}
					}
					
				}
			}
			////////////////////////////////////
			
			if(!empty($ENROLLMENT->DISBURSEMENT)){
				foreach($ENROLLMENT->DISBURSEMENT as $DISBURSEMENT) {
					$STUDENT_DISBURSEMENT = array();
					$STUDENT_DISBURSEMENT['PK_AR_LEDGER_CODE'] 		= $DISBURSEMENT->LEDGER_CODE_ID;
					$STUDENT_DISBURSEMENT['PK_AWARD_YEAR'] 			= $DISBURSEMENT->AWARD_YEAR_ID;
					$STUDENT_DISBURSEMENT['PK_TERM_BLOCK'] 			= $DISBURSEMENT->TERM_BLOCK_ID;
					$STUDENT_DISBURSEMENT['ACADEMIC_YEAR'] 			= $DISBURSEMENT->ACADEMIC_YEAR;
					$STUDENT_DISBURSEMENT['ACADEMIC_PERIOD'] 		= $DISBURSEMENT->ACADEMIC_PERIOD;
					$STUDENT_DISBURSEMENT['DISBURSEMENT_DATE'] 		= $DISBURSEMENT->DISBURSEMENT_DATE;
					$STUDENT_DISBURSEMENT['DISBURSEMENT_AMOUNT'] 	= $DISBURSEMENT->DISBURSEMENT_AMOUNT;
					$STUDENT_DISBURSEMENT['HOURS_REQUIRED'] 		= $DISBURSEMENT->HOURS_REQUIRED;
					$STUDENT_DISBURSEMENT['FUNDS_REQUESTED'] 		= $DISBURSEMENT->FUNDS_REQUESTED;
					$STUDENT_DISBURSEMENT['COMMENTS'] 				= $DISBURSEMENT->COMMENTS;
					$STUDENT_DISBURSEMENT['GROSS_AMOUNT'] 			= $DISBURSEMENT->GROSS_AMOUNT;
					$STUDENT_DISBURSEMENT['FEE_AMOUNT'] 			= $DISBURSEMENT->FEE_AMOUNT;
					$STUDENT_DISBURSEMENT['APPROVED_DATE'] 			= $DISBURSEMENT->APPROVED_DATE;
					
					if(strtolower($STUDENT_DISBURSEMENT['FUNDS_REQUESTED']) == 'yes')
						$STUDENT_DISBURSEMENT['FUNDS_REQUESTED'] = 1;

					$STUDENT_DISBURSEMENT['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
					$STUDENT_DISBURSEMENT['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
					$STUDENT_DISBURSEMENT['PK_ACCOUNT'] 			= $PK_ACCOUNT;
					$STUDENT_DISBURSEMENT['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'insert');
					
				}
			}
			
			/////////////////////
			/////////////////////
		}
		
		if(!empty($DATA->CONTACTS)){
			foreach($DATA->CONTACTS as $CONTACTS) {
				$PK_STUDENT_CONTACT_TYPE_MASTER = $CONTACTS->CONTACT_TYPE_ID;
				$PK_STUDENT_RELATIONSHIP_MASTER = $CONTACTS->RELATIONSHIP_ID;
				$PK_STATES = $CONTACTS->STATE_ID;
				$PK_COUNTRY = $CONTACTS->COUNTRY_ID;
				
				$IS_ADDRESS_INVALID = $CONTACTS->IS_ADDRESS_INVALID;
				if(strtolower($IS_ADDRESS_INVALID) == 'yes')
					$IS_ADDRESS_INVALID = 1;
				else if(strtolower($IS_ADDRESS_INVALID) == 'no')
					$IS_ADDRESS_INVALID = 0;
				else
					$IS_ADDRESS_INVALID = 0;	
					
				$HOME_PHONE_INVALID = $CONTACTS->HOME_PHONE_INVALID;
				if(strtolower($HOME_PHONE_INVALID) == 'yes')
					$HOME_PHONE_INVALID = 1;
				else if(strtolower($HOME_PHONE_INVALID) == 'no')
					$HOME_PHONE_INVALID = 0;
				else
					$HOME_PHONE_INVALID = 0;	
					
				$WORK_PHONE_INVALID = $CONTACTS->WORK_PHONE_INVALID;
				if(strtolower($WORK_PHONE_INVALID) == 'yes')
					$WORK_PHONE_INVALID = 1;
				else if(strtolower($WORK_PHONE_INVALID) == 'no')
					$WORK_PHONE_INVALID = 0;
				else
					$WORK_PHONE_INVALID = 0;	
					
				$CELL_PHONE_INVALID = $CONTACTS->CELL_PHONE_INVALID;
				if(strtolower($CELL_PHONE_INVALID) == 'yes')
					$CELL_PHONE_INVALID = 1;
				else if(strtolower($CELL_PHONE_INVALID) == 'no')
					$CELL_PHONE_INVALID = 0;
				else
					$CELL_PHONE_INVALID = 0;
					
				$OTHER_PHONE_INVALID = $CONTACTS->OTHER_PHONE_INVALID;
				if(strtolower($OTHER_PHONE_INVALID) == 'yes')
					$OTHER_PHONE_INVALID = 1;
				else if(strtolower($OTHER_PHONE_INVALID) == 'no')
					$OTHER_PHONE_INVALID = 0;
				else
					$OTHER_PHONE_INVALID = 0;
					
				$EMAIL_INVALID = $CONTACTS->EMAIL_INVALID;
				if(strtolower($EMAIL_INVALID) == 'yes')
					$EMAIL_INVALID = 1;
				else if(strtolower($EMAIL_INVALID) == 'no')
					$EMAIL_INVALID = 0;
				else
					$EMAIL_INVALID = 0;
					
				$EMAIL_OTHER_INVALID = $CONTACTS->EMAIL_OTHER_INVALID;
				if(strtolower($EMAIL_OTHER_INVALID) == 'yes')
					$EMAIL_OTHER_INVALID = 1;
				else if(strtolower($EMAIL_OTHER_INVALID) == 'no')
					$EMAIL_OTHER_INVALID = 0;
				else
					$EMAIL_OTHER_INVALID = 0;
					
				$OPT_OUT = $CONTACTS->OPT_OUT;
				if(strtolower($OPT_OUT) == 'yes')
					$OPT_OUT = 1;
				else if(strtolower($OPT_OUT) == 'no')
					$OPT_OUT = 0;
				else
					$OPT_OUT = 0;
					
				$USE_EMAIL = $CONTACTS->USE_EMAIL;
				if(strtolower($USE_EMAIL) == 'yes')
					$USE_EMAIL = 1;
				else if(strtolower($USE_EMAIL) == 'no')
					$USE_EMAIL = 0;
				else
					$USE_EMAIL = 0;
					
				$ACTIVE = $CONTACTS->ACTIVE;
				if(strtolower($ACTIVE) == 'yes')
					$ACTIVE = 1;
				else if(strtolower($ACTIVE) == 'no')
					$ACTIVE = 0;
				else
					$ACTIVE = 1;
					
				$HOME_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->HOME_PHONE);
				$WORK_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->WORK_PHONE);
				$CELL_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->CELL_PHONE);
				$OTHER_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->OTHER_PHONE);
				$FAX			= preg_replace( '/[^0-9]/', '',$CONTACTS->FAX);
				
				if($HOME_PHONE != '')
					$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
					
				if($WORK_PHONE != '')
					$WORK_PHONE = '('.$WORK_PHONE[0].$WORK_PHONE[1].$WORK_PHONE[2].') '.$WORK_PHONE[3].$WORK_PHONE[4].$WORK_PHONE[5].'-'.$WORK_PHONE[6].$WORK_PHONE[7].$WORK_PHONE[8].$WORK_PHONE[9];
					
				if($CELL_PHONE != '')
					$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
					
				if($OTHER_PHONE != '')
					$OTHER_PHONE = '('.$OTHER_PHONE[0].$OTHER_PHONE[1].$OTHER_PHONE[2].') '.$OTHER_PHONE[3].$OTHER_PHONE[4].$OTHER_PHONE[5].'-'.$OTHER_PHONE[6].$OTHER_PHONE[7].$OTHER_PHONE[8].$OTHER_PHONE[9];
					
				if($FAX != '')
					$FAX = '('.$FAX[0].$FAX[1].$FAX[2].') '.$FAX[3].$FAX[4].$FAX[5].'-'.$FAX[6].$FAX[7].$FAX[8].$FAX[9];
				
				$STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER']   	= $PK_STUDENT_CONTACT_TYPE_MASTER;
				$STUDENT_CONTACT['PK_STUDENT_RELATIONSHIP_MASTER']   	= $PK_STUDENT_RELATIONSHIP_MASTER;
				$STUDENT_CONTACT['CONTACT_DESCRIPTION']   				= $CONTACTS->CONTACT_DESCRIPTION;
				$STUDENT_CONTACT['COMPANY_NAME']   						= $CONTACTS->COMPANY_NAME;
				$STUDENT_CONTACT['CONTACT_TITLE']   					= $CONTACTS->CONTACT_TITLE;
				$STUDENT_CONTACT['ADDRESS']   							= $CONTACTS->ADDRESS;
				$STUDENT_CONTACT['ADDRESS_1']   						= $CONTACTS->ADDRESS_1;
				$STUDENT_CONTACT['CITY']   								= $CONTACTS->CITY;
				$STUDENT_CONTACT['PK_STATES']   						= $CONTACTS->STATE_ID;
				$STUDENT_CONTACT['ZIP']   								= $CONTACTS->ZIP;
				$STUDENT_CONTACT['PK_COUNTRY']   						= $CONTACTS->COUNTRY_ID;
				$STUDENT_CONTACT['CONTACT_NAME']   						= $CONTACTS->CONTACT_NAME;
				$STUDENT_CONTACT['ADDRESS_INVALID'] 					= $IS_ADDRESS_INVALID;
				$STUDENT_CONTACT['HOME_PHONE'] 							= $HOME_PHONE;
				$STUDENT_CONTACT['HOME_PHONE_INVALID'] 					= $HOME_PHONE_INVALID;
				$STUDENT_CONTACT['WORK_PHONE'] 							= $WORK_PHONE;
				$STUDENT_CONTACT['WORK_PHONE_INVALID'] 					= $WORK_PHONE_INVALID;
				$STUDENT_CONTACT['OTHER_PHONE'] 						= $OTHER_PHONE;
				$STUDENT_CONTACT['OTHER_PHONE_INVALID'] 				= $OTHER_PHONE_INVALID;
				$STUDENT_CONTACT['CELL_PHONE'] 							= $CELL_PHONE;
				$STUDENT_CONTACT['CELL_PHONE_INVALID'] 					= $CELL_PHONE_INVALID;
				$STUDENT_CONTACT['FAX'] 								= $FAX;
				$STUDENT_CONTACT['EMAIL'] 								= $CONTACTS->EMAIL;
				$STUDENT_CONTACT['EMAIL_INVALID'] 						= $EMAIL_INVALID;
				$STUDENT_CONTACT['EMAIL_OTHER'] 						= $CONTACTS->EMAIL_OTHER;
				$STUDENT_CONTACT['EMAIL_OTHER_INVALID'] 				= $EMAIL_OTHER_INVALID;
				$STUDENT_CONTACT['OPT_OUT']   							= $OPT_OUT;
				$STUDENT_CONTACT['USE_EMAIL'] 							= $USE_EMAIL;
				$STUDENT_CONTACT['ACTIVE'] 								= $ACTIVE;
				$STUDENT_CONTACT['WEBSITE'] 							= $CONTACTS->WEBSITE;
				$STUDENT_CONTACT['PK_ACCOUNT']   						= $PK_ACCOUNT;
				$STUDENT_CONTACT['PK_STUDENT_MASTER']   				= $PK_STUDENT_MASTER;
				$STUDENT_CONTACT['CREATED_ON']  						= date("Y-m-d H:i");
				db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
			}
		}
		
		if(!empty($DATA->OTHER_EDUCATION)){
			foreach($DATA->OTHER_EDUCATION as $OTHER_EDUCATION) {
				
				$GRADUATED = $OTHER_EDUCATION->GRADUATED;
				if(strtolower($GRADUATED) == 'yes')
					$GRADUATED = 1;
				else if(strtolower($GRADUATED) == 'no')
					$GRADUATED = 0;
				else
					$GRADUATED = 0;
					
				$TRANSCRIPT_REQUESTED = $OTHER_EDUCATION->TRANSCRIPT_REQUESTED;
				if(strtolower($TRANSCRIPT_REQUESTED) == 'yes')
					$TRANSCRIPT_REQUESTED = 1;
				else if(strtolower($TRANSCRIPT_REQUESTED) == 'no')
					$TRANSCRIPT_REQUESTED = 0;
				else
					$TRANSCRIPT_REQUESTED = 0;
					
				$TRANSCRIPT_RECEIVED = $OTHER_EDUCATION->TRANSCRIPT_RECEIVED;
				if(strtolower($TRANSCRIPT_RECEIVED) == 'yes')
					$TRANSCRIPT_RECEIVED = 1;
				else if(strtolower($TRANSCRIPT_RECEIVED) == 'no')
					$TRANSCRIPT_RECEIVED = 0;
				else
					$TRANSCRIPT_RECEIVED = 0;
					
				$SCHOOL_PHONE 	= preg_replace( '/[^0-9]/', '',$OTHER_EDUCATION->SCHOOL_PHONE);
				$SCHOOL_FAX 	= preg_replace( '/[^0-9]/', '',$OTHER_EDUCATION->SCHOOL_FAX);
					
				if($SCHOOL_PHONE != '')
					$SCHOOL_PHONE = '('.$SCHOOL_PHONE[0].$SCHOOL_PHONE[1].$SCHOOL_PHONE[2].') '.$SCHOOL_PHONE[3].$SCHOOL_PHONE[4].$SCHOOL_PHONE[5].'-'.$SCHOOL_PHONE[6].$SCHOOL_PHONE[7].$SCHOOL_PHONE[8].$SCHOOL_PHONE[9];
					
				if($SCHOOL_FAX != '')
					$SCHOOL_FAX = '('.$SCHOOL_FAX[0].$SCHOOL_FAX[1].$SCHOOL_FAX[2].') '.$SCHOOL_FAX[3].$SCHOOL_FAX[4].$SCHOOL_FAX[5].'-'.$SCHOOL_FAX[6].$SCHOOL_FAX[7].$SCHOOL_FAX[8].$SCHOOL_FAX[9];
				
				$STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] 		= $OTHER_EDUCATION->EDUCATION_TYPE_ID;;
				$STUDENT_OTHER_EDU['GRADUATED'] 				= $GRADUATED;
				$STUDENT_OTHER_EDU['GRADUATED_DATE'] 			= $OTHER_EDUCATION->GRADUATED_DATE;;
				$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] 		= $TRANSCRIPT_REQUESTED;
				$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED_DATE'] = $OTHER_EDUCATION->TRANSCRIPT_REQUESTED_DATE;
				$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'] 		= $TRANSCRIPT_RECEIVED;
				$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED_DATE']  = $OTHER_EDUCATION->TRANSCRIPT_RECEIVED_DATE;
				$STUDENT_OTHER_EDU['SCHOOL_NAME']  				= $OTHER_EDUCATION->SCHOOL_NAME;
				$STUDENT_OTHER_EDU['ADDRESS'] 	 				= $OTHER_EDUCATION->ADDRESS;
				$STUDENT_OTHER_EDU['ADDRESS_1']  				= $OTHER_EDUCATION->ADDRESS_1;
				$STUDENT_OTHER_EDU['CITY']  					= $OTHER_EDUCATION->CITY;
				$STUDENT_OTHER_EDU['PK_STATE']  				= $OTHER_EDUCATION->STATE_ID;
				$STUDENT_OTHER_EDU['ZIP']  						= $OTHER_EDUCATION->ZIP;
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE']  		= $SCHOOL_PHONE;
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX']  		= $SCHOOL_FAX;
				$STUDENT_OTHER_EDU['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
				$STUDENT_OTHER_EDU['PK_ACCOUNT'] 			 	= $PK_ACCOUNT;
				$STUDENT_OTHER_EDU['CREATED_ON']  			 = date("Y-m-d H:i");
				db_perform('S_STUDENT_OTHER_EDU', $STUDENT_OTHER_EDU, 'insert');
				
			}
		}
		
		if(!empty($DATA->CUSTOM_FIELDS)){
			foreach($DATA->CUSTOM_FIELDS as $CUSTOM_FIELDS) {
				$res_st = $db->Execute("SELECT FIELD_NAME FROM S_CUSTOM_FIELDS WHERE PK_CUSTOM_FIELDS = '".$CUSTOM_FIELDS->CUSTOM_FIELDS_ID."' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				$CUSTOM_FIELDS_ARR = array();
				$CUSTOM_FIELDS_ARR['PK_ACCOUNT'] 		 = $PK_ACCOUNT;
				$CUSTOM_FIELDS_ARR['PK_STUDENT_MASTER']  = $PK_STUDENT_MASTER;
				$CUSTOM_FIELDS_ARR['PK_CUSTOM_FIELDS'] 	 = $CUSTOM_FIELDS->CUSTOM_FIELDS_ID;
				$CUSTOM_FIELDS_ARR['FIELD_VALUE'] 		 = $CUSTOM_FIELDS->FIELD_VALUE;
				$CUSTOM_FIELDS_ARR['FIELD_NAME'] 		 = $res_st->fields['FIELD_NAME'];
				$CUSTOM_FIELDS_ARR['CREATED_ON']  		 = date("Y-m-d H:i");
				db_perform('S_STUDENT_CUSTOM_FIELDS', $CUSTOM_FIELDS_ARR, 'insert');
			}
		}
		
		if(!empty($DATA->TESTS)){
			foreach($DATA->TESTS as $TESTS) {
				$PASSED = $TESTS->PASSED;
				if(strtolower($PASSED) == 'yes')
					$PASSED = 1;
				else if(strtolower($PASSED) == 'no')
					$PASSED = 0;
				else
					$PASSED = 0;
					
				$STUDENT_TEST['TEST_LABEL'] 			= $TESTS->TEST_LABEL;
				$STUDENT_TEST['TEST_RESULT'] 			= $TESTS->TEST_RESULT;
				$STUDENT_TEST['PASSED'] 				= $PASSED;
				$STUDENT_TEST['TEST_DATE'] 				= $TESTS->TEST_DATE;
				//$STUDENT_TEST['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
				$STUDENT_TEST['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$STUDENT_TEST['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$STUDENT_TEST['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_TEST', $STUDENT_TEST, 'insert');
			}
		}
		
		if(!empty($DATA->ATB_TEST)){
			foreach($DATA->ATB_TEST as $ATB_TEST) {
				$STUDENT_ATB_TEST['PK_ATB_CODE'] 			= $ATB_TEST->ATB_CODE_ID;
				$STUDENT_ATB_TEST['PK_ATB_TEST_CODE'] 		= $ATB_TEST->ATB_TEST_CODE_ID;
				$STUDENT_ATB_TEST['PK_ATB_ADMIN_CODE'] 		= $ATB_TEST->ATB_ADMIN_CODE_ID;
				$STUDENT_ATB_TEST['COMPLETED_DATE'] 		= $ATB_TEST->COMPLETED_DATE;
				//$STUDENT_ATB_TEST['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
				$STUDENT_ATB_TEST['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$STUDENT_ATB_TEST['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$STUDENT_ATB_TEST['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_ATB_TEST', $STUDENT_ATB_TEST, 'insert');
			}
		}
		
		if(!empty($DATA->ACT_TEST)){
			foreach($DATA->ACT_TEST as $ACT_TEST) {
				$STUDENT_ACT_TEST['PK_ACT_MEASURE'] 		= $ACT_TEST->ACT_MEASURE_ID;
				$STUDENT_ACT_TEST['SCORE'] 					= $ACT_TEST->SCORE;
				$STUDENT_ACT_TEST['STATE_RANK'] 			= $ACT_TEST->STATE_RANK;
				$STUDENT_ACT_TEST['NATIONAL_RANK'] 			= $ACT_TEST->NATIONAL_RANK;
				$STUDENT_ACT_TEST['TEST_DATE'] 				= $ACT_TEST->TEST_DATE;
				//$STUDENT_ACT_TEST['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
				$STUDENT_ACT_TEST['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$STUDENT_ACT_TEST['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$STUDENT_ACT_TEST['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_ACT_TEST', $STUDENT_ACT_TEST, 'insert');
			}
		}
		
		if(!empty($DATA->SAT_TEST)){
			foreach($DATA->SAT_TEST as $SAT_TEST) {
				$PK_SAT_MEASURE = $SAT_TEST->SAT_MEASURE_ID;
				$STUDENT_SAT_TEST['PK_SAT_MEASURE'] 		= $SAT_TEST->SAT_MEASURE_ID;
				$STUDENT_SAT_TEST['SCORE'] 					= $SAT_TEST->SCORE;
				$STUDENT_SAT_TEST['NATIONAL_RANK'] 			= $SAT_TEST->NATIONAL_RANK;
				$STUDENT_SAT_TEST['USER_RANK'] 				= $SAT_TEST->USER_RANK;
				$STUDENT_SAT_TEST['TEST_DATE'] 				= $SAT_TEST->TEST_DATE;
				//$STUDENT_SAT_TEST['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
				$STUDENT_SAT_TEST['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$STUDENT_SAT_TEST['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$STUDENT_SAT_TEST['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_SAT_TEST', $STUDENT_SAT_TEST, 'insert');
			}
		}
		
		$data['MESSAGE'] 	 = 'Lead Created';
		$data['INTERNAL_ID'] = $PK_STUDENT_MASTER;
		
	}
}

$data = json_encode($data);
echo $data;