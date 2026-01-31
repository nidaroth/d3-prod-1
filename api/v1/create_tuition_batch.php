<? require_once("../../global/config.php"); 
require_once("../../school/function_student_ledger.php"); 
require_once("../../school/function_update_disbursement_status.php");

$DATA = (file_get_contents('php://input'));

//$DATA = '{"DATE_RECEIVED":"2021-05-01","CHECK_NO":"ABC123","COMMENTS":"from API","POSTED_DATE":"2021-05-01","BATCH_NO":"A1234","LEDGER_CODE_ID":"171","DISBURSEMENT":[{"STUDENT_ID":"5","TERM_BLOCK_ID":"","ACADEMIC_YEAR":"1","ACADEMIC_PERIOD":"2","DISBURSEMENT_DATE":"2021-04-28","DISBURSEMENT_AMOUNT":"55","AWARD_YEAR_ID":"3","APPROVED_DATE":"2021-04-20", "DEPOSITED_DATE":"2021-05-01","HOURS_REQUIRED":"1", "RECEIPT_NO":"aaa1","PRIOR_YEAR":"no","CHECK_NO":"L123","DESCRIPTION":"ssssssss"},{"STUDENT_ID":"52","TERM_BLOCK_ID":"4","ACADEMIC_YEAR":"2","ACADEMIC_PERIOD":"2","DISBURSEMENT_DATE":"2021-04-27", "DISBURSEMENT_AMOUNT":"65","AWARD_YEAR_ID":"4","APPROVED_DATE":"2021-04-20","DEPOSITED_DATE":"2021-05-01","HOURS_REQUIRED":"55","RECEIPT_NO":"bbbb1","PRIOR_YEAR":"yes","CHECK_NO":"B123","DESCRIPTION":"bbbbbbb"}]}';

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
	
	if($DATA->BATCH_NO == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
		$data['MESSAGE'] .= 'Missing BATCH_NO Value';
	}
	
	if($DATA->TRANSACTION_DATE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
		$data['MESSAGE'] .= 'Missing TRANSACTION_DATE Value';
	}
	
	if($DATA->TERM_ID == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
		$data['MESSAGE'] .= 'Missing TERM_ID Value';
	} else {
		$PK_TERM_MASTER = $DATA->TERM_ID;
		$res_st = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_TERM_MASTER = '$PK_TERM_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid TERM_ID Value - '.$PK_TERM_MASTER;
		}
	}
	
	if($DATA->STUDENT_TYPE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
		$data['MESSAGE'] .= 'Missing STUDENT_TYPE Value';
	} else {
		$STUDENT_TYPE = $DATA->STUDENT_TYPE;
		if($STUDENT_TYPE != 1 && $STUDENT_TYPE != 2 && $STUDENT_TYPE != 3 && $STUDENT_TYPE != 4 && $STUDENT_TYPE != 5 && $STUDENT_TYPE != 6 && $STUDENT_TYPE != 8){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid STUDENT_TYPE Value - '.$STUDENT_TYPE;
		}
	}
	if($DATA->ALL_PROGRAMS == 1) {
		$PK_CAMPUS_PROGRAM = "";
		$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		while (!$res->EOF) { 
			if($PK_CAMPUS_PROGRAM != '')
				$PK_CAMPUS_PROGRAM .= ',';
				
			$PK_CAMPUS_PROGRAM .= $res->fields['PK_CAMPUS_PROGRAM'];
			
			$res->MoveNext();
		}
	} else {
		if(empty($DATA->PROGRAM_ID)) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Missing PROGRAM_ID Value';
		} else {
			foreach($DATA->PROGRAM_ID as $PK_CAMPUS_PROGRAM){
				$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				if($res->RecordCount() == 0) {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid PROGRAM_ID Value - '.$PK_CAMPUS_PROGRAM;
				}
			}
			$PK_CAMPUS_PROGRAM = implode($DATA->PROGRAM_ID);
		}
	}

	if($DATA->FEE_TYPE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
		$data['MESSAGE'] .= 'Missing FEE_TYPE Value';
	} else {
		$FEE_TYPE = $DATA->FEE_TYPE;
		if($FEE_TYPE != 1 && $FEE_TYPE != 2){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid FEE_TYPE Value - '.$FEE_TYPE;
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
			}
		}
	}
	if(!empty($DATA->DETAILS)){
		foreach($DATA->DETAILS as $DETAILS) {
			
			$PK_STUDENT_MASTER = $DETAILS->STUDENT_ID;
			if($PK_STUDENT_MASTER == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing DETAILS->STUDENT_ID Value';
			} else {
				$res_st = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DETAILS->STUDENT_ID Value - '.$PK_STUDENT_MASTER;
				}
			}

			$PK_STUDENT_ENROLLMENT = $DETAILS->STUDENT_ENROLLMENT_ID;
			if($PK_STUDENT_ENROLLMENT == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing DETAILS->STUDENT_ENROLLMENT_ID Value';
			} else {
				$res_st = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DETAILS->STUDENT_ENROLLMENT_ID Value - '.$PK_STUDENT_ENROLLMENT;
				}
			}
			
			$PK_TERM_BLOCK = $DETAILS->TERM_BLOCK_ID;
			if($PK_TERM_BLOCK != '') {
				$res_st = $db->Execute("select PK_TERM_BLOCK from S_TERM_BLOCK WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_TERM_BLOCK = '$PK_TERM_BLOCK' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
			
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DETAILS->TERM_BLOCK_ID Value - '.$DETAILS->TERM_BLOCK_ID;
				}
			}
			
			$PK_AR_LEDGER_CODE = $DETAILS->LEDGER_CODE_ID;
			if($PK_AR_LEDGER_CODE == '') {
				$data['SUCCESS'] = 0;
				$data['MESSAGE'] .= 'Missing DETAILS->LEDGER_CODE_ID Value';
			} else {
				$res_st = $db->Execute("select PK_AR_LEDGER_CODE from M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DETAILS->LEDGER_CODE_ID Value - '.$PK_AR_LEDGER_CODE;
				}
			}
			
			if($DETAILS->TRANSACTION_DATE == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing DETAILS->TRANSACTION_DATE Value';
			}
			
		}
	}

	if($data['SUCCESS'] == 1) {
		$BATCH_NO = $DATA->BATCH_NO;
		$res_batch = $db->Execute("select PK_TUITION_BATCH_MASTER from S_TUITION_BATCH_MASTER WHERE BATCH_NO = '$BATCH_NO' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_batch->RecordCount() == 0){
			$TUITION_BATCH_MASTER['PK_BATCH_STATUS']			= 2;
			$TUITION_BATCH_MASTER['TYPE']  						= 1;
			$TUITION_BATCH_MASTER['BATCH_NO'] 					= $BATCH_NO;
			$TUITION_BATCH_MASTER['POSTED_DATE'] 				= $DATA->POSTED_DATE;
			$TUITION_BATCH_MASTER['COMMENTS'] 					= $DATA->COMMENTS;
			$TUITION_BATCH_MASTER['PK_TERM_MASTER'] 			= $DATA->TERM_ID;
			$TUITION_BATCH_MASTER['STUDENT_TYPE'] 				= $DATA->STUDENT_TYPE;
			$TUITION_BATCH_MASTER['TRANS_DATE'] 				= $DATA->TRANSACTION_DATE;
			$TUITION_BATCH_MASTER['PK_CAMPUS_PROGRAM'] 			= $PK_CAMPUS_PROGRAM;
			$TUITION_BATCH_MASTER['AY'] 						= $DATA->ACADEMIC_YEAR;
			$TUITION_BATCH_MASTER['AP'] 						= $DATA->ACADEMIC_PERIOD;
			$TUITION_BATCH_MASTER['PK_FEE_TYPE'] 				= $DATA->FEE_TYPE;
			$TUITION_BATCH_MASTER['TUITION_BATCH_PK_CAMPUS'] 	= implode(",",$DATA->CAMPUS);
			$TUITION_BATCH_MASTER['OPTION_1'] 					= 1;
			$TUITION_BATCH_MASTER['PK_ACCOUNT']  				= $PK_ACCOUNT;
			$TUITION_BATCH_MASTER['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_TUITION_BATCH_MASTER', $TUITION_BATCH_MASTER, 'insert');
			$PK_TUITION_BATCH_MASTER = $db->insert_ID();
			
		} else {
			$PK_TUITION_BATCH_MASTER = $res_batch->fields['PK_TUITION_BATCH_MASTER'];
		}
		
		if(!empty($DATA->DETAILS)){
			foreach($DATA->DETAILS as $DETAILS) {
				
				$PK_STUDENT_MASTER 		= $DETAILS->STUDENT_ID;
				$PK_STUDENT_ENROLLMENT 	= $DETAILS->STUDENT_ENROLLMENT_ID;
				
				$res_st = $db->Execute("select ENROLLMENT_PK_TERM_BLOCK from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				$ENROLLMENT_PK_TERM_BLOCK 	= $res_st->fields['ENROLLMENT_PK_TERM_BLOCK'];
				
				$TUITION_BATCH_DETAIL = array();

				$TUITION_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'] 		= $PK_STUDENT_ENROLLMENT;
				$TUITION_BATCH_DETAIL['PK_STUDENT_MASTER'] 			= $PK_STUDENT_MASTER;
				$TUITION_BATCH_DETAIL['PK_AR_LEDGER_CODE']  		= $DETAILS->LEDGER_CODE_ID;
				$TUITION_BATCH_DETAIL['TRANSACTION_DATE']  			= $DETAILS->TRANSACTION_DATE;
				$TUITION_BATCH_DETAIL['AMOUNT']  					= $DETAILS->AMOUNT;
				$TUITION_BATCH_DETAIL['PK_TERM_BLOCK']  			= $DETAILS->TERM_BLOCK_ID;
				$TUITION_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION']  	= $DETAILS->DESCRIPTION;
				$TUITION_BATCH_DETAIL['PK_TUITION_BATCH_MASTER'] 	= $PK_TUITION_BATCH_MASTER;
				$TUITION_BATCH_DETAIL['PK_ACCOUNT']  			 	= $PK_ACCOUNT;
				
				if($DETAILS->CREATED_ON_DATE != '')
					$TUITION_BATCH_DETAIL['CREATED_ON'] = $DETAILS->CREATED_ON_DATE;
				else
					$TUITION_BATCH_DETAIL['CREATED_ON'] = date("Y-m-d H:i");
				db_perform('S_TUITION_BATCH_DETAIL', $TUITION_BATCH_DETAIL, 'insert');
				$PK_TUITION_BATCH_DETAIL = $db->insert_ID();

				$ledger_data['PK_TUITION_BATCH_DETAIL'] = $PK_TUITION_BATCH_DETAIL;
				$ledger_data['PK_AR_LEDGER_CODE'] 		= $TUITION_BATCH_DETAIL['PK_AR_LEDGER_CODE'];
				$ledger_data['AMOUNT'] 					= $TUITION_BATCH_DETAIL['AMOUNT'];
				$ledger_data['DATE'] 					= $TUITION_BATCH_DETAIL['TRANSACTION_DATE'];
				$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $TUITION_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'];
				$ledger_data['PK_STUDENT_MASTER'] 		= $TUITION_BATCH_DETAIL['PK_STUDENT_MASTER'];
				$ledger_data['PK_ACCOUNT'] 				= $PK_ACCOUNT;
				student_ledger($ledger_data);
				
			}
		}

		$data['MESSAGE'] = 'Batch Created';
	}
}

$data = json_encode($data);
echo $data;