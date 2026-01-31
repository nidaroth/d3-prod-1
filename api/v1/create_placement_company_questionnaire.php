<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"QUESTION":"Test Qus 1", "QUESTION_GROUP": 3, "DISPLAY_ORDER": 4}';

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
	
	if(trim($DATA->QUESTION) == '') {
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing QUESTION Value';
	}
	if(trim($DATA->QUESTION_GROUP) == '') {
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing QUESTION_GROUP Value';
	} else {
		$res = $db->Execute("SELECT * FROM M_PLACEMENT_COMPANY_QUESTION_GROUP where PK_PLACEMENT_COMPANY_QUESTION_GROUP = ".trim($DATA->QUESTION_GROUP));
		if($res->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid QUESTION_GROUP Value';
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$QUESTIONNAIRE['QUESTIONS'] 							= trim($DATA->QUESTION);
		$QUESTIONNAIRE['PK_PLACEMENT_COMPANY_QUESTION_GROUP'] 	= trim($DATA->QUESTION_GROUP);
		if(isset($DATA->DISPLAY_ORDER) && trim($DATA->DISPLAY_ORDER)!='') {
			$QUESTIONNAIRE['DISPLAY_ORDER'] 					= trim($DATA->DISPLAY_ORDER);
		}
		
		$QUESTIONNAIRE['PK_ACCOUNT']  							= $PK_ACCOUNT;
		$QUESTIONNAIRE['CREATED_ON']  							= date("Y-m-d H:i");
		db_perform('M_PLACEMENT_COMPANY_QUESTIONNAIRE', $QUESTIONNAIRE, 'insert');
		// exit;
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Question Created';
	}
}

$data = json_encode($data);
echo $data;
