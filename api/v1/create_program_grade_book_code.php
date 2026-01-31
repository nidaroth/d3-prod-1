<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"CODE":"code 1aaa","DESCRIPTION":"desc 1","HOUR":"1","SESSIONS":"2","POINTS":"3","GRADE_BOOK_TYPE_ID":"1"}';

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
	
	if($DATA->CODE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'CODE Value Missing';
	} 
	
	if($DATA->GRADE_BOOK_TYPE != '') {
		$GRADE_BOOK_TYPE = $DATA->GRADE_BOOK_TYPE;
		$res = $db->Execute("SELECT PK_GRADE_BOOK_TYPE FROM M_GRADE_BOOK_TYPE where GRADE_BOOK_TYPE = '$GRADE_BOOK_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid GRADE_BOOK_TYPE value';
		} else
			$PK_GRADE_BOOK_TYPE = $res->fields['PK_GRADE_BOOK_TYPE'];
	} 

	if($data['SUCCESS'] == 1) {
		$GRADE_BOOK_CODE['CODE'] 				= trim($DATA->CODE);
		$GRADE_BOOK_CODE['DESCRIPTION'] 		= trim($DATA->DESCRIPTION);
		$GRADE_BOOK_CODE['PK_GRADE_BOOK_TYPE'] 	= $PK_GRADE_BOOK_TYPE;
		$GRADE_BOOK_CODE['HOUR'] 				= trim($DATA->HOUR);
		$GRADE_BOOK_CODE['SESSIONS'] 			= trim($DATA->SESSIONS);
		$GRADE_BOOK_CODE['POINTS'] 				= trim($DATA->POINTS);
		$GRADE_BOOK_CODE['PK_ACCOUNT']  		= $PK_ACCOUNT;
		$GRADE_BOOK_CODE['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('M_GRADE_BOOK_CODE', $GRADE_BOOK_CODE, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] = 'Program Grade Book Code Created';
	}
}

$data = json_encode($data);
echo $data;
