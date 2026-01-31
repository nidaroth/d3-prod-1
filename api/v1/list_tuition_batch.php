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

	$res = $db->Execute("SELECT PK_TUITION_BATCH_MASTER,TRANS_DATE, BATCH_NO, BEGIN_DATE AS TERM_MASTER ,BATCH_STATUS,POSTED_DATE, IF(TYPE = 1,'Program', IF(TYPE = 2,'Course',IF(TYPE = 7,'Estimated Other Fee',''))) AS TYPE, S_TUITION_BATCH_MASTER.PK_BATCH_STATUS, COMMENTS, S_TUITION_BATCH_MASTER.PK_TERM_MASTER, TRANS_DATE, STUDENT_TYPE, AY,  AP, PK_FEE_TYPE, S_TUITION_BATCH_MASTER.PK_CAMPUS_PROGRAM, CODE 
	FROM 
	S_TUITION_BATCH_MASTER 
	LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_TUITION_BATCH_MASTER.PK_BATCH_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_TUITION_BATCH_MASTER.PK_CAMPUS_PROGRAM 
	WHERE
	S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' ");
	$i = 0;
	while (!$res->EOF) { 
		$PK_TUITION_BATCH_MASTER = $res->fields['PK_TUITION_BATCH_MASTER'];
		$data['TUITION_BATCH'][$i]['ID'] 				= $PK_TUITION_BATCH_MASTER;
		$data['TUITION_BATCH'][$i]['BATCH_NO'] 			= $res->fields['BATCH_NO'];
		$data['TUITION_BATCH'][$i]['POSTED_DATE'] 		= $res->fields['POSTED_DATE'];
		$data['TUITION_BATCH'][$i]['COMMENTS'] 			= $res->fields['COMMENTS'];
		$data['TUITION_BATCH'][$i]['TERM_ID'] 			= $res->fields['PK_TERM_MASTER'];
		$data['TUITION_BATCH'][$i]['TERM'] 				= $res->fields['TERM_MASTER'];
		$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] 		= $res->fields['STUDENT_TYPE'];
		$data['TUITION_BATCH'][$i]['TRANSACTION_DATE'] 	= $res->fields['TRANS_DATE'];
		$data['TUITION_BATCH'][$i]['PROGRAM_ID'] 		= $res->fields['PK_CAMPUS_PROGRAM'];
		$data['TUITION_BATCH'][$i]['PROGRAM_CODE'] 		= $res->fields['CODE'];
		$data['TUITION_BATCH'][$i]['ACADEMIC_YEAR'] 	= $res->fields['AY'];
		$data['TUITION_BATCH'][$i]['ACADEMIC_PERIOD'] 	= $res->fields['AP'];
		$data['TUITION_BATCH'][$i]['FEE_TYPE'] 			= $res->fields['PK_FEE_TYPE'];
		$data['TUITION_BATCH'][$i]['BATCH_STATUS'] 		= $res->fields['BATCH_STATUS'];
		
		if($data['TUITION_BATCH'][$i]['STUDENT_TYPE'] == 1) {
			$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] = 'All Students';
		} else if($data['TUITION_BATCH'][$i]['STUDENT_TYPE'] == 2) {
			$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] = 'Program Students';
		} else if($data['TUITION_BATCH'][$i]['STUDENT_TYPE'] == 3) {
			$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] = 'Course Students';
		} else if($data['TUITION_BATCH'][$i]['STUDENT_TYPE'] == 4) {
			$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] = 'Unit Students';
		} else if($data['TUITION_BATCH'][$i]['STUDENT_TYPE'] == 5) {
			$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] = 'Term Students';
		} else if($data['TUITION_BATCH'][$i]['STUDENT_TYPE'] == 6) {
			$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] = 'Selected Students';
		} else if($data['TUITION_BATCH'][$i]['STUDENT_TYPE'] == 8) {
			$data['TUITION_BATCH'][$i]['STUDENT_TYPE'] = 'Scheduled Hours';
		}
		
		if($data['TUITION_BATCH'][$i]['FEE_TYPE'] == 1) {
			$data['TUITION_BATCH'][$i]['FEE_TYPE'] = 'Budget Only';
		} else if($data['TUITION_BATCH'][$i]['FEE_TYPE'] == 2) {
			$data['TUITION_BATCH'][$i]['FEE_TYPE'] = 'Tuition & Budget';
		}
		
		$j = 0;
		$res_det = $db->Execute("select S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_DETAIL, S_TUITION_BATCH_DETAIL.PK_TERM_BLOCK, S_TUITION_BATCH_DETAIL.PK_AR_LEDGER_CODE, TRANSACTION_DATE, AMOUNT, BATCH_DETAIL_DESCRIPTION, S_TUITION_BATCH_DETAIL.PK_STUDENT_MASTER , CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BEGIN_DATE, CODE, S_TUITION_BATCH_DETAIL.PK_STUDENT_ENROLLMENT
		FROM 
		S_TUITION_BATCH_DETAIL
		LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_TUITION_BATCH_DETAIL.PK_TERM_BLOCK 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_TUITION_BATCH_DETAIL.PK_AR_LEDGER_CODE 
		LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_TUITION_BATCH_DETAIL.PK_STUDENT_MASTER 
		WHERE PK_TUITION_BATCH_MASTER = '$PK_TUITION_BATCH_MASTER' AND S_TUITION_BATCH_DETAIL.PK_ACCOUNT = '$PK_ACCOUNT' 
		ORDER BY TRANSACTION_DATE ASC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
		while (!$res_det->EOF) {
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['ID']						= $res_det->fields['PK_TUITION_BATCH_DETAIL'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['STUDENT_ID']				= $res_det->fields['PK_STUDENT_MASTER'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['STUDENT_ENROLLMENT_ID']	= $res_det->fields['PK_STUDENT_ENROLLMENT'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['STUDENT_NAME']			= $res_det->fields['NAME'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['TERM_BLOCK_ID']			= $res_det->fields['PK_TERM_BLOCK'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['TERM_BLOCK']				= $res_det->fields['BEGIN_DATE'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['LEDGER_CODE_ID']			= $res_det->fields['PK_AR_LEDGER_CODE'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['LEDGER_CODE']				= $res_det->fields['CODE'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['TRANSACTION_DATE']		= $res_room->fields['TRANSACTION_DATE'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['AMOUNT']					= $res_det->fields['AMOUNT'];
			$data['TUITION_BATCH'][$i]['DETAIL'][$j]['DESCRIPTION']				= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;