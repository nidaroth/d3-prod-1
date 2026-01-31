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

	$res = $db->Execute("SELECT PK_PLACEMENT_COMPANY_QUESTIONNAIRE,QUESTIONS,DISPLAY_ORDER,PLACEMENT_COMPANY_QUESTION_GROUP FROM M_PLACEMENT_COMPANY_QUESTIONNAIRE LEFT JOIN M_PLACEMENT_COMPANY_QUESTION_GROUP ON M_PLACEMENT_COMPANY_QUESTION_GROUP.PK_PLACEMENT_COMPANY_QUESTION_GROUP = M_PLACEMENT_COMPANY_QUESTIONNAIRE.PK_PLACEMENT_COMPANY_QUESTION_GROUP WHERE M_PLACEMENT_COMPANY_QUESTIONNAIRE.ACTIVE = 1 AND M_PLACEMENT_COMPANY_QUESTIONNAIRE.PK_ACCOUNT='$PK_ACCOUNT'");
	$i = 0;
	while (!$res->EOF) { 
		$data['PLACEMENT_COMPANY_QUESTION_GROUP'][$i]['ID']    								= $res->fields['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'];
		$data['PLACEMENT_COMPANY_QUESTION_GROUP'][$i]['QUESTIONS']  						= $res->fields['QUESTIONS'];
		$data['PLACEMENT_COMPANY_QUESTION_GROUP'][$i]['DISPLAY_ORDER']  					= $res->fields['DISPLAY_ORDER'];
		$data['PLACEMENT_COMPANY_QUESTION_GROUP'][$i]['PLACEMENT_COMPANY_QUESTION_GROUP']  	= $res->fields['PLACEMENT_COMPANY_QUESTION_GROUP'];
		$i++;
		$res->MoveNext();
	}
}
$data = json_encode($data);
echo $data;