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
	
	if($DATA->BATCH_DATE == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing BATCH_DATE Value';
	}
	
	if($DATA->BATCH_NO == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing BATCH_NO Value';
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
		$res_batch = $db->Execute("select PK_MISC_BATCH_MASTER from S_MISC_BATCH_MASTER WHERE BATCH_NO = '$BATCH_NO' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_batch->RecordCount() == 0){
			$MISC_BATCH_MASTER['PK_BATCH_STATUS']			= 2;
			$MISC_BATCH_MASTER['STUDENT_TYPE']  			= 1;
			$MISC_BATCH_MASTER['BATCH_DATE']  				= $DATA->BATCH_DATE;
			$MISC_BATCH_MASTER['DESCRIPTION'] 				= $DATA->DESCRIPTION;
			$MISC_BATCH_MASTER['COMMENTS'] 					= $DATA->COMMENTS;
			$MISC_BATCH_MASTER['POSTED_DATE'] 				= $DATA->POSTED_DATE;
			$MISC_BATCH_MASTER['BATCH_NO'] 					= $DATA->BATCH_NO;
			$MISC_BATCH_MASTER['MISC_BATCH_PK_CAMPUS'] 		= implode(",",$DATA->CAMPUS);
			$MISC_BATCH_MASTER['PK_ACCOUNT']  				= $PK_ACCOUNT;
			$MISC_BATCH_MASTER['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_MISC_BATCH_MASTER', $MISC_BATCH_MASTER, 'insert');
			$PK_MISC_BATCH_MASTER = $db->insert_ID();
			
		} else {
			$PK_MISC_BATCH_MASTER = $res_batch->fields['PK_MISC_BATCH_MASTER'];
		}
		
		if(!empty($DATA->DETAILS)){
			foreach($DATA->DETAILS as $DETAILS) {
				
				$PK_STUDENT_MASTER 		= $DETAILS->STUDENT_ID;
				$PK_STUDENT_ENROLLMENT 	= $DETAILS->STUDENT_ENROLLMENT_ID;
				
				$res_st = $db->Execute("select ENROLLMENT_PK_TERM_BLOCK from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				$ENROLLMENT_PK_TERM_BLOCK 	= $res_st->fields['ENROLLMENT_PK_TERM_BLOCK'];
				
				$MISC_BATCH_DETAIL['PK_TERM_BLOCK'] 			= $DETAILS->TERM_BLOCK_ID;
				$MISC_BATCH_DETAIL['PK_AR_LEDGER_CODE']  		= $DETAILS->LEDGER_CODE_ID;
				$MISC_BATCH_DETAIL['TRANSACTION_DATE']  		= $DETAILS->TRANSACTION_DATE;
				$MISC_BATCH_DETAIL['DEBIT']  					= $DETAILS->DEBIT;
				$MISC_BATCH_DETAIL['CREDIT']  					= $DETAILS->CREDIT;
				$MISC_BATCH_DETAIL['AY']  						= $DETAILS->ACADEMIC_YEAR;
				$MISC_BATCH_DETAIL['AP']  						= $DETAILS->ACADEMIC_PERIOD;
				$MISC_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION'] 	= $DETAILS->DESCRIPTION;
				$MISC_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
				$MISC_BATCH_DETAIL['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$MISC_BATCH_DETAIL['PK_MISC_BATCH_MASTER']  	= $PK_MISC_BATCH_MASTER;
				$MISC_BATCH_DETAIL['PK_ACCOUNT']  				= $PK_ACCOUNT;
				
				if($DETAILS->CREATED_ON_DATE != '')
					$MISC_BATCH_DETAIL['CREATED_ON'] = $DETAILS->CREATED_ON_DATE;
				else
					$MISC_BATCH_DETAIL['CREATED_ON'] = date("Y-m-d H:i");
				db_perform('S_MISC_BATCH_DETAIL', $MISC_BATCH_DETAIL, 'insert');
				$PK_MISC_BATCH_DETAIL = $db->insert_ID();
				
				$ledger_data['PK_MISC_BATCH_DETAIL'] 	= $PK_MISC_BATCH_DETAIL;
				$ledger_data['PK_AR_LEDGER_CODE'] 		= $MISC_BATCH_DETAIL['PK_AR_LEDGER_CODE'];
				$ledger_data['CREDIT_AMOUNT'] 			= $MISC_BATCH_DETAIL['CREDIT'];
				$ledger_data['DEBIT_AMOUNT'] 			= $MISC_BATCH_DETAIL['DEBIT'];
				$ledger_data['DATE'] 					= $MISC_BATCH_DETAIL['TRANSACTION_DATE'];
				$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $MISC_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'];
				$ledger_data['PK_STUDENT_MASTER'] 		= $MISC_BATCH_DETAIL['PK_STUDENT_MASTER'];
				$ledger_data['PK_ACCOUNT'] 				= $PK_ACCOUNT;
				student_ledger($ledger_data);
				
			}
		}
		
		$res_st = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT FROM S_MISC_BATCH_DETAIL WHERE PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		
		$MISC_BATCH_MASTER1['CREDIT'] = $res_st->fields['CREDIT'];
		$MISC_BATCH_MASTER1['DEBIT'] = $res_st->fields['DEBIT'];
		db_perform('S_MISC_BATCH_MASTER', $MISC_BATCH_MASTER1, 'update'," PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		
		$data['MESSAGE'] = 'Batch Created';
	}
}

$data = json_encode($data);
echo $data;