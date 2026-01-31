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
	
	$pn = $_GET['pn'];
	if($pn == '')
		$pn = 1;
		
	$cond = "";
	if($_GET['batch_no'] != '')
		$cond = " AND BATCH_NO = '$_GET[batch_no]' ";

	$query = "SELECT PK_PAYMENT_BATCH_MASTER,DATE_RECEIVED, BATCH_NO, CHECK_NO,AMOUNT,BATCH_STATUS, PK_AR_LEDGER_CODE, S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS, S_PAYMENT_BATCH_MASTER.COMMENTS, POSTED_DATE, S_PAYMENT_BATCH_MASTER.PK_AR_LEDGER_CODE  
	FROM 
	S_PAYMENT_BATCH_MASTER 
	LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS 
	WHERE 
	S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' $cond ";
	$res = $db->Execute($query);
	
	$no = $res->RecordCount();
	$nr = $no;
	
	$limit = "";
	if($nr > 0){
		$itemsPerPage = 50;
		$limit  = 'LIMIT ' .($pn - 1) * $itemsPerPage .',' .$itemsPerPage;
		$limit1 = 'LIMIT ' .$pn * $itemsPerPage .',' .$itemsPerPage;
	}
	
	$res = $db->Execute($query." ".$limit1);
	if($res->RecordCount() > 0)
		$next_page = $pn + 1;
	else
		$next_page = 0;
		
	$data['NEXT_PAGE'] = $next_page;
	
	$i = 0;
	$res = $db->Execute($query." ".$limit);
	while (!$res->EOF) { 
		$PK_PAYMENT_BATCH_MASTER = $res->fields['PK_PAYMENT_BATCH_MASTER'];
		$PK_AR_LEDGER_CODE 		 = $res->fields['PK_AR_LEDGER_CODE'];
		
		$CODE = '';
		$res_leg = $db->Execute("SELECT CODE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		while (!$res_leg->EOF) { 
			if($CODE != '')
				$CODE .= ', ';
			$CODE .= $res_leg->fields['CODE'];
			
			$res_leg->MoveNext();
		}
		
		$data['PAYMENT_BATCH'][$i]['ID'] 				= $PK_PAYMENT_BATCH_MASTER;
		$data['PAYMENT_BATCH'][$i]['BATCH_NO'] 			= $res->fields['BATCH_NO'];
		$data['PAYMENT_BATCH'][$i]['DATE_RECEIVED'] 	= $res->fields['DATE_RECEIVED'];
		$data['PAYMENT_BATCH'][$i]['CHECK_NO'] 			= $res->fields['CHECK_NO'];
		$data['PAYMENT_BATCH'][$i]['COMMENTS'] 			= $res->fields['COMMENTS'];
		$data['PAYMENT_BATCH'][$i]['POSTED_DATE'] 		= $res->fields['POSTED_DATE'];
		$data['PAYMENT_BATCH'][$i]['LEDGER_CODE_ID'] 	= $res->fields['PK_AR_LEDGER_CODE'];
		$data['PAYMENT_BATCH'][$i]['LEDGER_CODE'] 		= $CODE;
		$data['PAYMENT_BATCH'][$i]['BATCH_STATUS'] 		= $res->fields['BATCH_STATUS'];
		
		$j = 0;
		$res_det = $db->Execute("select S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL, S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL, S_PAYMENT_BATCH_DETAIL.PK_STUDENT_MASTER, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE.CODE AS LEDGER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,RECEIPT_NO, BATCH_NO, ACADEMIC_YEAR, S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK, ACADEMIC_PERIOD,BATCH_DETAIL_DESCRIPTION,DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, DEPOSITED_DATE, BATCH_PAYMENT_STATUS, BATCH_NO,RECEIVED_AMOUNT,APPROVED_DATE,HOURS_REQUIRED, IF(PRIOR_YEAR = 1,'Yes', IF(PRIOR_YEAR = 2,'No','')) AS PRIOR_YEAR_1, PRIOR_YEAR ,BEGIN_DATE, S_PAYMENT_BATCH_DETAIL.CHECK_NO AS STUD_CHECK_NO, PK_AR_PAYMENT_TYPE,AR_PAYMENT_TYPE, S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT 
		from 
		S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL 
		LEFT JOIN M_BATCH_PAYMENT_STATUS ON M_BATCH_PAYMENT_STATUS.PK_BATCH_PAYMENT_STATUS = S_PAYMENT_BATCH_DETAIL.PK_BATCH_PAYMENT_STATUS 
		LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK , S_STUDENT_DISBURSEMENT 
		LEFT JOIN M_AR_PAYMENT_TYPE ON M_AR_PAYMENT_TYPE.PK_AR_PAYMENT_TYPE = S_STUDENT_DISBURSEMENT.DETAIL 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE
		, S_STUDENT_MASTER 
		WHERE 
		S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND 
		S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' 
		ORDER BY DISBURSEMENT_DATE ASC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
		
		while (!$res_det->EOF) {
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['BATCH_DETAIL_ID']		= $res_det->fields['PK_PAYMENT_BATCH_DETAIL'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['DISBURSEMENT_ID']		= $res_det->fields['PK_STUDENT_DISBURSEMENT'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['STUDENT_ID']			= $res_det->fields['PK_STUDENT_MASTER'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['STUDENT_ENROLLMENT_ID']	= $res_det->fields['PK_STUDENT_ENROLLMENT'];
			
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['STUDENT_NAME']			= $res_det->fields['NAME'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['TERM_BLOCK_ID']			= $res_det->fields['PK_TERM_BLOCK'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['TERM_BLOCK']			= $res_det->fields['BEGIN_DATE'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['ACADEMIC_YEAR']			= $res_room->fields['ACADEMIC_YEAR'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['ACADEMIC_PERIOD']		= $res_det->fields['ACADEMIC_PERIOD'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['DISBURSEMENT_DATE']		= $res_det->fields['DISBURSEMENT_DATE'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['DISBURSEMENT_AMOUNT']	= $res_det->fields['DISBURSEMENT_AMOUNT'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['APPROVED_DATE']			= $res_det->fields['APPROVED_DATE'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['DEPOSITED_DATE']		= $res_det->fields['DEPOSITED_DATE'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['HOURS_REQUIRED']		= $res_det->fields['HOURS_REQUIRED'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['RECEIPT_NO']			= $res_det->fields['RECEIPT_NO'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['PRIOR_YEAR']			= $res_det->fields['PRIOR_YEAR'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['CHECK_NO']				= $res_det->fields['CHECK_NO'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['DESCRIPTION']			= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['PAYMENT_TYPE_ID']		= $res_det->fields['PK_AR_PAYMENT_TYPE'];
			$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['PAYMENT_TYPE']			= $res_det->fields['AR_PAYMENT_TYPE'];
			
			//$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['AWARD_YEAR_ID']	= $res_det->fields['AWARD_YEAR_ID'];
			//$data['PAYMENT_BATCH'][$i]['DISBURSEMENT'][$j]['AWARD_YEAR']		= $res_det->fields['COMPLETED'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;