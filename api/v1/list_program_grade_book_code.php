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

	$res = $db->Execute("SELECT PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION, HOUR, SESSIONS, POINTS, GRADE_BOOK_TYPE, M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE FROM M_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE WHERE M_GRADE_BOOK_CODE.PK_ACCOUNT = '$PK_ACCOUNT' AND M_GRADE_BOOK_CODE.ACTIVE = 1");
	$i = 0;
	while (!$res->EOF) { 
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['ID'] 					= $res->fields['PK_GRADE_BOOK_CODE'];
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['CODE'] 				= $res->fields['CODE'];
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['DESCRIPTION']  		= $res->fields['DESCRIPTION'];
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['GRADE_BOOK_TYPE']  	= $res->fields['GRADE_BOOK_TYPE'];
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['GRADE_BOOK_TYPE_ID']  = $res->fields['DESCRIPTION'];
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['HOUR']  				= $res->fields['HOUR'];
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['SESSIONS']  			= $res->fields['SESSIONS'];
		$data['PROGRAM_GRADE_BOOK_CODE'][$i]['POINTS']  			= $res->fields['POINTS'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;