<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"LEDGER_CODE":"Tuition","LEDGER_DESCRIPTION":"Tuition","INVOICE_DESCRIPTION":"","GL_CODE_DEBIT":"23456","GL_CODE_CREDIT":"1234","AWARD_LETTER":"Yes","INVOICE":"No","TITLE_IV":"No","TYPE":"Award"}';

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
	
	if($DATA->LEDGER_CODE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Ledger Code Missing';
	}
	
	if($DATA->LEDGER_DESCRIPTION == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Ledger Description Missing';
	}
	
	if($DATA->TYPE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Type Missing';
	} else {
		if(strtolower($DATA->TYPE) == 'award') 
			$TYPE = 1;
		else if(strtolower($DATA->TYPE) == 'fee') 
			$TYPE = 2;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Type Value';
		}
	}
	
	if($DATA->AWARD_LETTER == '') {
		$AWARD_LETTER = 0;
	} else {
		if(strtolower($DATA->AWARD_LETTER) == 'yes') 
			$AWARD_LETTER = 1;
		else if(strtolower($DATA->AWARD_LETTER) == 'no') 
			$AWARD_LETTER = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Award Letter Value';
		}
	}
	
	if($DATA->INVOICE == '') {
		$INVOICE = 0;
	} else {
		if(strtolower($DATA->INVOICE) == 'yes') 
			$INVOICE = 1;
		else if(strtolower($DATA->INVOICE) == 'no') 
			$INVOICE = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Invoice Value';
		}
	}
	
	if($DATA->TITLE_IV == '') {
		$TITLE_IV = 0;
	} else {
		if(strtolower($DATA->TITLE_IV) == 'yes') 
			$TITLE_IV = 1;
		else if(strtolower($DATA->TITLE_IV) == 'no') 
			$TITLE_IV = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Title IV Value';
		}
	}
	
	if($DATA->NEED_ANALYSIS == '') {
		$NEED_ANALYSIS = 0;
	} else {
		if(strtolower($DATA->NEED_ANALYSIS) == 'yes') 
			$NEED_ANALYSIS = 1;
		else if(strtolower($DATA->NEED_ANALYSIS) == 'no') 
			$NEED_ANALYSIS = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Need Analysis Value';
		}
	}
	
	if($data['SUCCESS'] == 1) {

		$AR_LEDGER_CODE['CODE'] 				= trim($DATA->LEDGER_CODE);
		$AR_LEDGER_CODE['LEDGER_DESCRIPTION'] 	= trim($DATA->LEDGER_DESCRIPTION);
		$AR_LEDGER_CODE['INVOICE_DESCRIPTION'] 	= trim($DATA->INVOICE_DESCRIPTION);
		$AR_LEDGER_CODE['GL_CODE_DEBIT'] 		= trim($DATA->GL_CODE_DEBIT);
		$AR_LEDGER_CODE['GL_CODE_CREDIT'] 		= trim($DATA->GL_CODE_CREDIT);
		$AR_LEDGER_CODE['TYPE'] 				= $TYPE;
		$AR_LEDGER_CODE['NEED_ANALYSIS'] 		= $NEED_ANALYSIS;
		$AR_LEDGER_CODE['AWARD_LETTER'] 		= $AWARD_LETTER;
		$AR_LEDGER_CODE['INVOICE'] 				= $INVOICE;
		$AR_LEDGER_CODE['TITLE_IV'] 			= $TITLE_IV;
		$AR_LEDGER_CODE['PK_ACCOUNT']  	 		= $PK_ACCOUNT;
		$AR_LEDGER_CODE['CREATED_ON'] 	 		= date("Y-m-d H:i");
		db_perform('M_AR_LEDGER_CODE', $AR_LEDGER_CODE, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Ledger Code Created';
	}
}

$data = json_encode($data);
echo $data;
