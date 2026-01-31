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

	$res = $db->Execute("SELECT PK_MISC_BATCH_MASTER,BATCH_DATE, BATCH_NO,COMMENTS,POSTED_DATE, DESCRIPTION,CREDIT,DEBIT,BATCH_STATUS, S_MISC_BATCH_MASTER.PK_BATCH_STATUS FROM 
	S_MISC_BATCH_MASTER 
	LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_MISC_BATCH_MASTER.PK_BATCH_STATUS 
	WHERE
	S_MISC_BATCH_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' ");
	$i = 0;
	while (!$res->EOF) { 
		$PK_MISC_BATCH_MASTER = $res->fields['PK_MISC_BATCH_MASTER'];
		$data['MISCELLANEOUS_BATCH'][$i]['ID'] 				= $PK_MISC_BATCH_MASTER;
		$data['MISCELLANEOUS_BATCH'][$i]['BATCH_NO'] 		= $res->fields['BATCH_NO'];
		$data['MISCELLANEOUS_BATCH'][$i]['BATCH_DATE'] 		= $res->fields['BATCH_DATE'];
		$data['MISCELLANEOUS_BATCH'][$i]['DESCRIPTION'] 	= $res->fields['DESCRIPTION'];
		$data['MISCELLANEOUS_BATCH'][$i]['COMMENTS'] 		= $res->fields['COMMENTS'];
		$data['MISCELLANEOUS_BATCH'][$i]['POSTED_DATE'] 	= $res->fields['POSTED_DATE'];
		
		$j = 0;
		$res_det = $db->Execute("select S_MISC_BATCH_DETAIL.PK_MISC_BATCH_DETAIL, S_MISC_BATCH_DETAIL.PK_TERM_BLOCK, S_MISC_BATCH_DETAIL.PK_AR_LEDGER_CODE, TRANSACTION_DATE, DEBIT, CREDIT, AY, AP, BATCH_DETAIL_DESCRIPTION, S_MISC_BATCH_DETAIL.PK_STUDENT_MASTER , CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, BEGIN_DATE, CODE, S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT
		FROM 
		S_MISC_BATCH_DETAIL
		LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_MISC_BATCH_DETAIL.PK_TERM_BLOCK 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_MISC_BATCH_DETAIL.PK_AR_LEDGER_CODE 
		LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_MISC_BATCH_DETAIL.PK_STUDENT_MASTER 
		WHERE PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND S_MISC_BATCH_DETAIL.PK_ACCOUNT = '$PK_ACCOUNT' 
		ORDER BY TRANSACTION_DATE ASC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
		while (!$res_det->EOF) {
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['ID']					= $res_det->fields['PK_MISC_BATCH_DETAIL'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['STUDENT_ID']			= $res_det->fields['PK_STUDENT_MASTER'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['STUDENT_ENROLLMENT_ID']	= $res_det->fields['PK_STUDENT_ENROLLMENT'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['STUDENT_NAME']			= $res_det->fields['NAME'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['TERM_BLOCK_ID']			= $res_det->fields['PK_TERM_BLOCK'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['TERM_BLOCK']			= $res_det->fields['BEGIN_DATE'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['LEDGER_CODE_ID']		= $res_det->fields['PK_AR_LEDGER_CODE'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['LEDGER_CODE']			= $res_det->fields['CODE'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['TRANSACTION_DATE']		= $res_room->fields['TRANSACTION_DATE'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['DEBIT']					= $res_det->fields['DEBIT'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['CREDIT']				= $res_det->fields['CREDIT'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['DESCRIPTION']			= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['ACADEMIC_YEAR']			= $res_det->fields['AY'];
			$data['MISCELLANEOUS_BATCH'][$i]['DETAIL'][$j]['ACADEMIC_PERIOD']		= $res_det->fields['AP'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;