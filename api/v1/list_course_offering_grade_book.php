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

	$cond = " AND PK_COURSE_OFFERING = '$_GET[id]' ";
	
	$res = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE,CODE, S_COURSE_OFFERING_GRADE.DESCRIPTION, GRADE_BOOK_TYPE, S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE, DATE, POINTS, WEIGHT, WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE WHERE S_COURSE_OFFERING_GRADE.PK_ACCOUNT = '$PK_ACCOUNT' AND S_COURSE_OFFERING_GRADE.ACTIVE = 1 $cond ");
	$i = 0;
	while (!$res->EOF) { 
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['ID'] 					= $res->fields['PK_COURSE_OFFERING_GRADE'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['CODE'] 				= $res->fields['CODE'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['DESCRIPTION']  		= $res->fields['DESCRIPTION'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['GRADE_BOOK_TYPE']  	= $res->fields['GRADE_BOOK_TYPE'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['GRADE_BOOK_TYPE_ID']  	= $res->fields['PK_GRADE_BOOK_TYPE'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['DATE']  				= $res->fields['DATE'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['POINTS']  				= $res->fields['POINTS'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['WEIGHT']  				= $res->fields['WEIGHT'];
		$data['COURSE_OFFERING_GRADE_BOOK'][$i]['WEIGHTED_POINTS']  	= $res->fields['WEIGHTED_POINTS'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;