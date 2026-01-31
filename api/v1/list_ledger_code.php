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

	$res = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,INVOICE_DESCRIPTION,GL_CODE_DEBIT,GL_CODE_CREDIT, IF(AWARD_LETTER = 1, 'Yes', 'No') AS AWARD_LETTER, IF(INVOICE = 1, 'Yes', 'No') AS INVOICE,IF(TITLE_IV = 1, 'Yes', 'No') AS TITLE_IV, IF(TYPE = 1, 'Award', IF(TYPE = 2, 'Fee', '')) AS TYPE FROM M_AR_LEDGER_CODE where PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['LEDGER_CODE'][$i]['ID'] 						= $res->fields['PK_AR_LEDGER_CODE'];
		$data['LEDGER_CODE'][$i]['LEDGER_CODE'] 			= $res->fields['CODE'];
		$data['LEDGER_CODE'][$i]['LEDGER_DESCRIPTION'] 		= $res->fields['LEDGER_DESCRIPTION'];
		$data['LEDGER_CODE'][$i]['INVOICE_DESCRIPTION'] 	= $res->fields['INVOICE_DESCRIPTION'];
		$data['LEDGER_CODE'][$i]['GL_CODE_DEBIT'] 			= $res->fields['GL_CODE_DEBIT'];
		$data['LEDGER_CODE'][$i]['GL_CODE_CREDIT'] 			= $res->fields['GL_CODE_CREDIT'];
		$data['LEDGER_CODE'][$i]['AWARD_LETTER'] 			= $res->fields['AWARD_LETTER'];
		$data['LEDGER_CODE'][$i]['INVOICE'] 				= $res->fields['INVOICE'];
		$data['LEDGER_CODE'][$i]['TITLE_IV'] 				= $res->fields['TITLE_IV'];
		$data['LEDGER_CODE'][$i]['TYPE'] 					= $res->fields['TYPE'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;