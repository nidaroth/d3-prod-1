<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"PLACEMENT_STATUS":"From"}';

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
	$PK_COMPANY = isset($DATA->COMPANY_ID)?trim($DATA->COMPANY_ID):'';
	if($PK_COMPANY == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';			
		$data['MESSAGE'] .= 'Missing COMPANY_ID value';
	} else {
		$res = $db->Execute("SELECT PK_COMPANY FROM S_COMPANY where PK_COMPANY = '$PK_COMPANY' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';			
			$data['MESSAGE'] .= 'Invalid COMPANY_ID value';
		}
		else {
			$PK_COMPANY_JOB = isset($DATA->COMPANY_JOB_ID)?trim($DATA->COMPANY_JOB_ID):'';
			if($PK_COMPANY_JOB == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';			
				$data['MESSAGE'] .= 'Missing COMPANY_JOB_ID value';
			} else {
				$res = $db->Execute("SELECT PK_COMPANY_JOB FROM S_COMPANY_JOB where  PK_COMPANY = '$PK_COMPANY' AND PK_COMPANY_JOB = '$PK_COMPANY_JOB' AND PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = '1'");
				if($res->RecordCount() == 0) {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';			
					$data['MESSAGE'] .= 'Invalid COMPANY_JOB_ID value';
				}
			}

			$PK_COMPANY_CONTACT = isset($DATA->COMPANY_CONTACT_ID)?trim($DATA->COMPANY_CONTACT_ID):'';
			if($PK_COMPANY_CONTACT != '') {
				$res = $db->Execute("SELECT PK_COMPANY_CONTACT FROM S_COMPANY_CONTACT where PK_COMPANY = '$PK_COMPANY' AND PK_COMPANY_CONTACT = '$PK_COMPANY_CONTACT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				if($res->RecordCount() == 0) {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';			
					$data['MESSAGE'] .= 'Invalid COMPANY_CONTACT_ID value';
				} else {
					$AR_STUDENT_JOB['PK_COMPANY_CONTACT'] 			= $PK_COMPANY_CONTACT;
				}
			}
		}
	}
	
	if(trim($DATA->SUPERVISOR) != '') {
		$AR_STUDENT_JOB['SUPERVISOR'] 					= $SUPERVISOR;
	}
	if(trim($DATA->NOTES) != '') {
		$AR_STUDENT_JOB['NOTES'] 						= $NOTES;
	}

	$PK_PLACEMENT_TYPE = isset($DATA->PLACEMENT_TYPE_ID)?trim($DATA->PLACEMENT_TYPE_ID):'';
	if($PK_PLACEMENT_TYPE != '') {
		$res = $db->Execute("SELECT PK_PLACEMENT_TYPE FROM M_PLACEMENT_TYPE where PK_PLACEMENT_TYPE = '$PK_PLACEMENT_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';			
			$data['MESSAGE'] .= 'Invalid PLACEMENT_TYPE_ID value';
		} else {
			$AR_STUDENT_JOB['PK_PLACEMENT_TYPE'] 			= $PK_PLACEMENT_TYPE;
		}
	}

	$START_DATE = isset($DATA->START_DATE)?trim($DATA->START_DATE):'';
	if($START_DATE != '') {
		$AR_STUDENT_JOB['START_DATE'] = ($START_DATE != '' ? date("Y-m-d",strtotime($START_DATE)) : '');
	}
	$END_DATE = isset($DATA->END_DATE)?trim($DATA->END_DATE):'';
	if($END_DATE != '') {
		$AR_STUDENT_JOB['END_DATE'] = ($END_DATE != '' ? date("Y-m-d",strtotime($END_DATE)) : '');
	}

	$AR_STUDENT_JOB['PK_FULL_PART_TIME'] = 2;
	if(isset($DATA->FULL_PART_TIME) && $DATA->FULL_PART_TIME != '') {
		
		if(strtolower(trim($DATA->FULL_PART_TIME)) != "yes" && strtolower(trim($DATA->FULL_PART_TIME)) != "y" && strtolower(trim($DATA->FULL_PART_TIME)) != "no" && strtolower(trim($DATA->FULL_PART_TIME)) != "n") {
			$data['SUCCESS'] = 0;

			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid FULL_PART_TIME value';
		} else {
			if(strtolower(trim($DATA->FULL_PART_TIME)) == "yes" || strtolower(trim($DATA->FULL_PART_TIME)) == "y") {
				$AR_STUDENT_JOB['PK_FULL_PART_TIME'] = 1;
			}		
		}
	}

	$PK_PAY_TYPE = isset($DATA->PAY_TYPE_ID)?trim($DATA->PAY_TYPE_ID):'';
	if($PK_PAY_TYPE != '') {
		$res = $db->Execute("SELECT PK_PAY_TYPE FROM M_PAY_TYPE where PK_PAY_TYPE = '$PK_PAY_TYPE' AND ACTIVE = '1'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';			
			$data['MESSAGE'] .= 'Invalid PAY_TYPE_ID value';
		} else {
			$AR_STUDENT_JOB['PK_PAY_TYPE'] = $PK_PAY_TYPE;
		}
	}

	$PAY_AMOUNT = isset($DATA->PAY_AMOUNT)?trim($DATA->PAY_AMOUNT):'';
	if($PAY_AMOUNT != '') {
		$AR_STUDENT_JOB['PAY_AMOUNT'] = $PAY_AMOUNT;
	}

	$WEEKLY_HOURS = isset($DATA->WEEKLY_HOURS)?trim($DATA->WEEKLY_HOURS):'';
	if($WEEKLY_HOURS != '') {
		$AR_STUDENT_JOB['WEEKLY_HOURS'] = $WEEKLY_HOURS;
	}

	$ANNUAL_SALARY = isset($DATA->ANNUAL_SALARY)?trim($DATA->ANNUAL_SALARY):'';
	if($ANNUAL_SALARY != '') {
		$AR_STUDENT_JOB['ANNUAL_SALARY'] = $ANNUAL_SALARY;
	}

	$AR_STUDENT_JOB['CURRENT_JOB'] = 0;
	if(isset($DATA->CURRENT_JOB) && $DATA->CURRENT_JOB != '') {
		if(strtolower(trim($DATA->CURRENT_JOB)) != "yes" && strtolower(trim($DATA->CURRENT_JOB)) != "y" && strtolower(trim($DATA->CURRENT_JOB)) != "no" && strtolower(trim($DATA->CURRENT_JOB)) != "n") {
			$data['SUCCESS'] = 0;

			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid CURRENT_JOB value';
		} else {
			if(strtolower(trim($DATA->CURRENT_JOB)) == "yes" || strtolower(trim($DATA->CURRENT_JOB)) == "y") {
				$AR_STUDENT_JOB['CURRENT_JOB'] = 1;
			}		
		}
	}

	$PK_STUDENT_ENROLLMENT = isset($DATA->STUDENT_ENROLLMENT_ID)?trim($DATA->STUDENT_ENROLLMENT_ID):'';
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
		} else {
			$res_st = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			
			$AR_STUDENT_JOB['PK_STUDENT_MASTER'] 		= $res_st->fields['PK_STUDENT_MASTER'];
			$AR_STUDENT_JOB['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
		}
	}
	
	$PK_PLACEMENT_STATUS = isset($DATA->PLACEMENT_STATUS_ID)?trim($DATA->PLACEMENT_STATUS_ID):'';
	if($PK_PLACEMENT_STATUS != '') {
		$res = $db->Execute("SELECT PK_PLACEMENT_STATUS FROM M_PLACEMENT_STATUS where PK_PLACEMENT_STATUS = '$PK_PLACEMENT_STATUS' AND ACTIVE = '1' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';			
			$data['MESSAGE'] .= 'Invalid PLACEMENT_STATUS_ID value';
		} else {
			$AR_STUDENT_JOB['PK_PLACEMENT_STATUS'] 			= $PK_PLACEMENT_STATUS;
		}
	}

	$DOCUMENTED = isset($DATA->DOCUMENTED)?trim($DATA->DOCUMENTED):'';
	if($DOCUMENTED != '') {
		$AR_STUDENT_JOB['DOCUMENTED'] = ($DOCUMENTED != '' ? date("Y-m-d",strtotime($DOCUMENTED)) : '');
	}

	$PK_PLACEMENT_VERIFICATION_SOURCE = isset($DATA->PLACEMENT_VERIFICATION_SOURCE_ID)?trim($DATA->PLACEMENT_VERIFICATION_SOURCE_ID):'';
	if($PK_PLACEMENT_VERIFICATION_SOURCE != '') {
		$res = $db->Execute("SELECT PK_PLACEMENT_VERIFICATION_SOURCE, VERIFICATION_SOURCE FROM M_PLACEMENT_VERIFICATION_SOURCE where PK_PLACEMENT_VERIFICATION_SOURCE = '$PK_PLACEMENT_VERIFICATION_SOURCE' AND ACTIVE = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' order by VERIFICATION_SOURCE ASC");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';			
			$data['MESSAGE'] .= 'Invalid PLACEMENT_VERIFICATION_SOURCE_ID value';
		} else {
			$AR_STUDENT_JOB['PK_PLACEMENT_VERIFICATION_SOURCE'] = $PK_PLACEMENT_VERIFICATION_SOURCE;
		}
	}

	$VERIFICATION_DATE = isset($DATA->VERIFICATION_DATE)?trim($DATA->VERIFICATION_DATE):'';
	if($VERIFICATION_DATE != '') {
		$AR_STUDENT_JOB['VERIFICATION_DATE'] = ($VERIFICATION_DATE != '' ? date("Y-m-d",strtotime($VERIFICATION_DATE)) : '');
	}

	$PK_SOC_CODE = isset($DATA->SOC_CODE_ID)?trim($DATA->SOC_CODE_ID):'';
	if($PK_SOC_CODE != '') {
		$res_st = $db->Execute("select PK_SOC_CODE from M_SOC_CODE WHERE PK_SOC_CODE = '$PK_SOC_CODE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';				
			$data['MESSAGE'] .= 'Invalid SOC_CODE_ID Value - '.$PK_SOC_CODE;
		} else {
			$AR_STUDENT_JOB['PK_SOC_CODE'] = $PK_SOC_CODE;
		}
	}
	
	
	if($data['SUCCESS'] == 1) {
		$AR_STUDENT_JOB['PK_ENROLLMENT_STATUS'] 		= $PK_STUDENT_ENROLLMENT;
		$AR_STUDENT_JOB['PK_COMPANY'] 					= $PK_COMPANY;
		$AR_STUDENT_JOB['PK_COMPANY_JOB'] 				= $PK_COMPANY_JOB;
		$AR_STUDENT_JOB['PK_ACCOUNT']  					= $PK_ACCOUNT;
		$AR_STUDENT_JOB['CREATED_ON']  					= date("Y-m-d H:i");
		db_perform('S_STUDENT_JOB', $AR_STUDENT_JOB, 'insert');
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Student Job Created';
	}
}

$data = json_encode($data);
echo $data;
