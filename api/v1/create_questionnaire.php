<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"QUESTIONNAIRES":[{"TEXT":"Requirement 1","DEPARTMENT":"Admissions","DISPLAY_ORDER":"1"},{"TEXT":"Requirement 2","DEPARTMENT":"Admissions 1","DISPLAY_ORDER":"2"},{"TEXT":"Requirement 32","DEPARTMENT":"Admissions 1","DISPLAY_ORDER":"3"}]}';

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

	$i = 0;
	foreach($DATA->QUESTIONNAIRES as $QUESTIONNAIRE){
		if($QUESTIONNAIRE->DEPARTMENT == '') {
			$error[$i] = $QUESTIONNAIRE->TEXT.' - Missing Department';
			$i++;
		} else {
			$DEPARTMENT = trim($QUESTIONNAIRE->DEPARTMENT);
			
			$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 AND DEPARTMENT = '$DEPARTMENT' ");
			if($res->RecordCount() == 0) {
				$error[$i] = $QUESTIONNAIRE->TEXT.' - Invalid Department '.$DEPARTMENT;
				$i++;
			}
		}
	}
	
	if($i > 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = implode(",",$error);
	}

	if($data['SUCCESS'] == 1) {
	
		foreach($DATA->QUESTIONNAIRES as $QUESTIONNAIRE){
			if($QUESTIONNAIRE->TEXT != ''){ 
				
				$DEPARTMENT = trim($QUESTIONNAIRE->DEPARTMENT);
				$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 AND DEPARTMENT = '$DEPARTMENT' ");
				
				$QUESTIONNAIRE_ARR = array();
				$QUESTIONNAIRE_ARR['QUESTION'] 		= trim($QUESTIONNAIRE->TEXT);
				$QUESTIONNAIRE_ARR['DISPLAY_ORDER'] = trim($QUESTIONNAIRE->DISPLAY_ORDER);
				$QUESTIONNAIRE_ARR['PK_DEPARTMENT'] = $res->fields['PK_DEPARTMENT'];
				$QUESTIONNAIRE_ARR['PK_ACCOUNT']  	= $PK_ACCOUNT;
				$QUESTIONNAIRE_ARR['CREATED_ON']  	= date("Y-m-d H:i");
				db_perform('M_QUESTIONNAIRE', $QUESTIONNAIRE_ARR, 'insert');
				$PK_QUESTIONNAIRE_ARR[] = $db->insert_ID();
			}
		}
						
		$data['MESSAGE'] = 'School Questionnaire Created';
		
		$PK_QUESTIONNAIRE = implode(",",$PK_QUESTIONNAIRE_ARR);
		$res = $db->Execute("SELECT PK_QUESTIONNAIRE,QUESTION, DEPARTMENT, DISPLAY_ORDER, M_QUESTIONNAIRE.PK_DEPARTMENT FROM M_QUESTIONNAIRE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_QUESTIONNAIRE.PK_DEPARTMENT WHERE M_QUESTIONNAIRE.PK_ACCOUNT = '$PK_ACCOUNT' AND M_QUESTIONNAIRE.ACTIVE = 1 AND PK_QUESTIONNAIRE = '$PK_QUESTIONNAIRE' ");
		$i = 0;
		while (!$res->EOF) { 
			$data['QUESTIONNAIRES'][$i]['ID'] 			 = $res->fields['PK_QUESTIONNAIRE'];
			$data['QUESTIONNAIRES'][$i]['QUESTION'] 	 = $res->fields['QUESTION'];
			$data['QUESTIONNAIRES'][$i]['DEPARTMENT'] 	 = $res->fields['DEPARTMENT'];
			$data['QUESTIONNAIRES'][$i]['DEPARTMENT_ID'] = $res->fields['PK_DEPARTMENT'];
			$data['QUESTIONNAIRES'][$i]['DISPLAY_ORDER'] = $res->fields['DISPLAY_ORDER'];
			
			$i++;
			$res->MoveNext();
		}
		
	}
}

$data = json_encode($data);
echo $data;
