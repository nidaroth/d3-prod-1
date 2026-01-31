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
	
	if($DATA->DATE_RECEIVED == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing DATE_RECEIVED Value';
	}
	
	if($DATA->BATCH_NO == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing BATCH_NO Value';
	}
	
	$PK_AR_LEDGER_CODE = $DATA->LEDGER_CODE_ID;
	if($PK_AR_LEDGER_CODE == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing LEDGER_CODE_ID Value';
	} else {
		$res_st = $db->Execute("select PK_AR_LEDGER_CODE from M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid LEDGER_CODE_ID Value - '.$PK_AR_LEDGER_CODE;
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
	
	if(!empty($DATA->DISBURSEMENT)){
		foreach($DATA->DISBURSEMENT as $DISBURSEMENT) {
			
			$PK_STUDENT_MASTER = $DISBURSEMENT->STUDENT_ID;
			if($PK_STUDENT_MASTER == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing DISBURSEMENT->STUDENT_ID Value';
			} else {
				$res_st = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DISBURSEMENT->STUDENT_ID Value - '.$PK_STUDENT_MASTER;
				}
			}
			
			$PK_STUDENT_ENROLLMENT = $DISBURSEMENT->STUDENT_ENROLLMENT_ID;
			if($PK_STUDENT_ENROLLMENT == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing DISBURSEMENT->STUDENT_ENROLLMENT_ID Value';
			} else {
				$res_st = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DISBURSEMENT->STUDENT_ENROLLMENT_ID Value - '.$PK_STUDENT_ENROLLMENT;
				}
			}
			
			$PRIOR_YEAR = $DISBURSEMENT->PRIOR_YEAR;
			if(strtolower($PRIOR_YEAR) == 'yes')
				$PRIOR_YEAR = 1;
			else if(strtolower($PRIOR_YEAR) == 'no')
				$PRIOR_YEAR = 2;
			else if($PRIOR_YEAR != '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid DISBURSEMENT->PRIOR_YEAR Value - .'.$PRIOR_YEAR;
			}
			
			$PK_TERM_BLOCK = $DISBURSEMENT->TERM_BLOCK_ID;
			if($PK_TERM_BLOCK != '') {
				$res_st = $db->Execute("select PK_TERM_BLOCK from S_TERM_BLOCK WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_TERM_BLOCK = '$PK_TERM_BLOCK' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
			
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DISBURSEMENT->TERM_BLOCK_ID Value - '.$DISBURSEMENT->TERM_BLOCK_ID;
				}
			}
			
			$PK_AWARD_YEAR = $DISBURSEMENT->AWARD_YEAR_ID;
			if($PK_AR_LEDGER_CODE != '') {
				$res_st = $db->Execute("select PK_AWARD_YEAR from M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$PK_AWARD_YEAR' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DISBURSEMENT->AWARD_YEAR_ID Value - '.$PK_AWARD_YEAR;
				}
			}
			
			$PK_AR_PAYMENT_TYPE = $DISBURSEMENT->PAYMENT_TYPE_ID;
			if($PK_AR_PAYMENT_TYPE != '') {
				$res_st = $db->Execute("select PK_AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '$PK_AR_PAYMENT_TYPE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DISBURSEMENT->PAYMENT_TYPE_ID Value - '.$PK_AR_PAYMENT_TYPE;
				}
			}
			
			if($DISBURSEMENT->TRANSACTION_DATE == '') {
				$data['MESSAGE'] .= 'Missing DISBURSEMENT->TRANSACTION_DATE Value - ';
			}
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$BATCH_NO = $DATA->BATCH_NO;
		$res_batch = $db->Execute("select PK_PAYMENT_BATCH_MASTER,PK_AR_LEDGER_CODE,DATE_RECEIVED from S_PAYMENT_BATCH_MASTER WHERE BATCH_NO = '$BATCH_NO' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_batch->RecordCount() == 0){
			$PAYMENT_BATCH_MASTER['DATE_RECEIVED'] 	 	= $DATA->DATE_RECEIVED;
			$PAYMENT_BATCH_MASTER['CHECK_NO'] 			= $DATA->CHECK_NO;
			$PAYMENT_BATCH_MASTER['COMMENTS'] 		 	= $DATA->COMMENTS;
			$PAYMENT_BATCH_MASTER['POSTED_DATE'] 		= $DATA->POSTED_DATE;
			$PAYMENT_BATCH_MASTER['BATCH_NO'] 			= $BATCH_NO;
			$PAYMENT_BATCH_MASTER['PK_AR_LEDGER_CODE'] 	= $DATA->LEDGER_CODE_ID;
			$PAYMENT_BATCH_MASTER['BATCH_PK_CAMPUS'] 	= implode(",",$DATA->CAMPUS);
			
			$PAYMENT_BATCH_MASTER['PK_BATCH_STATUS']	= 2;
			$PAYMENT_BATCH_MASTER['PK_ACCOUNT']  		= $PK_ACCOUNT;
			$PAYMENT_BATCH_MASTER['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER, 'insert');
			$PK_PAYMENT_BATCH_MASTER = $db->insert_ID();
			
			$PK_AR_LEDGER_CODE 	= $DATA->LEDGER_CODE_ID;
			$DATE_RECEIVED 		= $DATA->DATE_RECEIVED;
		} else {
			$PK_PAYMENT_BATCH_MASTER 	= $res_batch->fields['PK_PAYMENT_BATCH_MASTER'];
			$PK_AR_LEDGER_CODE 			= $res_batch->fields['PK_AR_LEDGER_CODE'];
			$DATE_RECEIVED				= $res_batch->fields['DATE_RECEIVED'];
		}
		
		if(!empty($DATA->DISBURSEMENT)){
			foreach($DATA->DISBURSEMENT as $DISBURSEMENT) {
				$PRIOR_YEAR = $DISBURSEMENT->PRIOR_YEAR;
			
				if(strtolower($PRIOR_YEAR) == 'yes')
					$PRIOR_YEAR = 1;
				else if(strtolower($PRIOR_YEAR) == 'no')
					$PRIOR_YEAR = 2;
				else
					$PRIOR_YEAR = 2;
				
				$PK_STUDENT_MASTER 		= $DISBURSEMENT->STUDENT_ID;
				$PK_STUDENT_ENROLLMENT 	= $DISBURSEMENT->STUDENT_ENROLLMENT_ID;
				
				$res_st = $db->Execute("select ENROLLMENT_PK_TERM_BLOCK from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				$ENROLLMENT_PK_TERM_BLOCK 	= $res_st->fields['ENROLLMENT_PK_TERM_BLOCK'];
				
				$STUDENT_DISBURSEMENT1 = array();
				$STUDENT_DISBURSEMENT1['PK_DISBURSEMENT_STATUS'] 	= 2;
				$STUDENT_DISBURSEMENT1['PK_AR_LEDGER_CODE'] 	 	= $PK_AR_LEDGER_CODE;
				$STUDENT_DISBURSEMENT1['PK_TERM_BLOCK'] 		 	= $DISBURSEMENT->TERM_BLOCK_ID;
				$STUDENT_DISBURSEMENT1['ACADEMIC_YEAR'] 		 	= $DISBURSEMENT->ACADEMIC_YEAR;
				$STUDENT_DISBURSEMENT1['ACADEMIC_PERIOD'] 		 	= $DISBURSEMENT->ACADEMIC_PERIOD;
				$STUDENT_DISBURSEMENT1['DISBURSEMENT_DATE'] 	 	= $DISBURSEMENT->DISBURSEMENT_DATE;
				$STUDENT_DISBURSEMENT1['DISBURSEMENT_AMOUNT'] 	 	= $DISBURSEMENT->DISBURSEMENT_AMOUNT;
				$STUDENT_DISBURSEMENT1['PK_AWARD_YEAR'] 		 	= $DISBURSEMENT->AWARD_YEAR_ID;
				$STUDENT_DISBURSEMENT1['APPROVED_DATE'] 		 	= $DISBURSEMENT->APPROVED_DATE;
				$STUDENT_DISBURSEMENT1['HOURS_REQUIRED'] 		 	= $DISBURSEMENT->HOURS_REQUIRED;
				$STUDENT_DISBURSEMENT1['PK_DISBURSEMENT_STATUS']  	= 1; 
				$STUDENT_DISBURSEMENT1['PK_DETAIL_TYPE']  		 	= 4; 
				$STUDENT_DISBURSEMENT1['DETAIL']  				 	= $DISBURSEMENT->PAYMENT_TYPE_ID;
				
				if($DISBURSEMENT->DEPOSITED_DATE != '')
					$STUDENT_DISBURSEMENT1['DEPOSITED_DATE'] = $DISBURSEMENT->DEPOSITED_DATE;
				else
					$STUDENT_DISBURSEMENT1['DEPOSITED_DATE'] = $DATE_RECEIVED;
				
				$STUDENT_DISBURSEMENT1['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
				$STUDENT_DISBURSEMENT1['PK_STUDENT_MASTER'] 	 = $PK_STUDENT_MASTER;
				$STUDENT_DISBURSEMENT1['PK_ACCOUNT'] 			 = $PK_ACCOUNT;
				$STUDENT_DISBURSEMENT1['CREATED_ON']  			 = date("Y-m-d H:i");
				db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT1, 'insert');
				$PK_STUDENT_DISBURSEMENT = $db->insert_ID();
				
				$PAYMENT_BATCH_DETAIL['RECEIPT_NO'] 				= $DISBURSEMENT->RECEIPT_NO;
				$PAYMENT_BATCH_DETAIL['PK_STUDENT_MASTER']  		= $PK_STUDENT_MASTER;
				$PAYMENT_BATCH_DETAIL['PK_STUDENT_ENROLLMENT']  	= $PK_STUDENT_ENROLLMENT;
				$PAYMENT_BATCH_DETAIL['PK_PAYMENT_BATCH_MASTER']  	= $PK_PAYMENT_BATCH_MASTER;
				$PAYMENT_BATCH_DETAIL['PK_STUDENT_DISBURSEMENT']  	= $PK_STUDENT_DISBURSEMENT;
				$PAYMENT_BATCH_DETAIL['DUE_AMOUNT']  				= $DISBURSEMENT->DISBURSEMENT_AMOUNT;
				$PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT']  			= $DISBURSEMENT->DISBURSEMENT_AMOUNT;
				$PAYMENT_BATCH_DETAIL['PRIOR_YEAR']  				= $PRIOR_YEAR;
				$PAYMENT_BATCH_DETAIL['PK_TERM_BLOCK']  			= $DISBURSEMENT->TERM_BLOCK_ID;
				$PAYMENT_BATCH_DETAIL['CHECK_NO']  					= $DISBURSEMENT->CHECK_NO;
				$PAYMENT_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION']  	= $DISBURSEMENT->DESCRIPTION;
				$PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE']  	= $DISBURSEMENT->TRANSACTION_DATE;
				$PAYMENT_BATCH_DETAIL['PK_BATCH_PAYMENT_STATUS'] 	= 3;
				$PAYMENT_BATCH_DETAIL['PK_ACCOUNT']  				= $PK_ACCOUNT;
				
				if($DISBURSEMENT->TERM_BLOCK_ID != '')
					$PAYMENT_BATCH_DETAIL['PK_TERM_BLOCK'] = $DISBURSEMENT->TERM_BLOCK_ID;
				else
					$PAYMENT_BATCH_DETAIL['PK_TERM_BLOCK'] = $ENROLLMENT_PK_TERM_BLOCK;
				
				if($DISBURSEMENT->CREATED_ON_DATE != '')
					$PAYMENT_BATCH_DETAIL['CREATED_ON'] = $DISBURSEMENT->CREATED_ON_DATE;
				else
					$PAYMENT_BATCH_DETAIL['CREATED_ON'] = date("Y-m-d H:i");
					
				db_perform('S_PAYMENT_BATCH_DETAIL', $PAYMENT_BATCH_DETAIL, 'insert');
				$PK_PAYMENT_BATCH_DETAIL = $db->insert_ID();
				
				$STUDENT_DISBURSEMENT2['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
				db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT2, 'update'," PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				
				$ledger_data['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
				$ledger_data['PK_STUDENT_DISBURSEMENT'] = $PK_STUDENT_DISBURSEMENT;
				$ledger_data['PK_AR_LEDGER_CODE'] 		= $PK_AR_LEDGER_CODE;
				$ledger_data['AMOUNT'] 					= $PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT'];
				$ledger_data['DATE'] 					= $DISBURSEMENT->TRANSACTION_DATE;
				$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
				$ledger_data['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$ledger_data['PK_ACCOUNT'] 				= $PK_ACCOUNT;
				student_ledger($ledger_data);

				//update_disbursement_status($PAYMENT_BATCH_DETAIL['PK_STUDENT_MASTER'],$PAYMENT_BATCH_DETAIL['PK_STUDENT_ENROLLMENT']);
				
			}
		}
		
		$res_st = $db->Execute("select SUM(RECEIVED_AMOUNT) as RECEIVED_AMOUNT FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		
		$PAYMENT_BATCH_MASTER1['AMOUNT'] = $res_st->fields['RECEIVED_AMOUNT'];
		db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER1, 'update'," PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		
		$data['MESSAGE'] = 'Batch Created ';
	}
}

$data = json_encode($data);
echo $data;