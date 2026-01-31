<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"COURSE_OFFERING_GRADE_BOOK": [{ "COURSE_OFFERING_ID": "1","CODE": "1","DESCRIPTION": "Desc 1", "GRADE_BOOK_TYPE_ID": "1", "DATE": "2021-05-30", "POINTS": "2", "WEIGHT": "1" }, { "COURSE_OFFERING_ID": "1", "CODE": "YYY", "DESCRIPTION": "Desc 2", "GRADE_BOOK_TYPE_ID": "3", "DATE": "2021-06-30", "POINTS": "2", "WEIGHT": "0.5" } ]}';

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
	
	foreach($DATA->COURSE_OFFERING_GRADE_BOOK as $DATA1){
		//echo "<pre>";print_r($DATA1);exit;
		$PK_COURSE_OFFERING = $DATA1->COURSE_OFFERING_ID;
		if($PK_COURSE_OFFERING == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Missing COURSE_OFFERING_ID Value';
		} else {
			$res_st = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid COURSE_OFFERING_ID Value - '.$PK_COURSE_OFFERING;
			}
		}
		
		$PK_GRADE_BOOK_TYPE = $DATA1->GRADE_BOOK_TYPE_ID;
		if($PK_GRADE_BOOK_TYPE == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Missing GRADE_BOOK_TYPE_ID Value';
		} else {
			$res_st = $db->Execute("select PK_GRADE_BOOK_TYPE from M_GRADE_BOOK_TYPE WHERE PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
	
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid GRADE_BOOK_TYPE_ID Value - '.$PK_GRADE_BOOK_TYPE;
			}
		}
		
		if($DATA1->CODE == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Missing CODE Value';
		}

	}
	
	if($data['SUCCESS'] == 1) {
	
		foreach($DATA->COURSE_OFFERING_GRADE_BOOK as $DATA1){
			$PK_STUDENT_ENROLLMENT = $DATA1->STUDENT_ENROLLMENT_ID;
			$res_st = $db->Execute("select PK_CAMPUS_PROGRAM, PK_STUDENT_MASTER from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			
			$COURSE_OFFERING_GRADE = array();
			$COURSE_OFFERING_GRADE['CODE'] 					= $DATA1->CODE;
			$COURSE_OFFERING_GRADE['DESCRIPTION'] 			= $DATA1->DESCRIPTION;
			$COURSE_OFFERING_GRADE['PK_GRADE_BOOK_TYPE'] 	= $DATA1->GRADE_BOOK_TYPE_ID;
			$COURSE_OFFERING_GRADE['DATE'] 					= $DATA1->DATE;
			$COURSE_OFFERING_GRADE['POINTS'] 				= $DATA1->POINTS;
			$COURSE_OFFERING_GRADE['WEIGHT'] 				= $DATA1->WEIGHT;
			$COURSE_OFFERING_GRADE['WEIGHTED_POINTS'] 		= $COURSE_OFFERING_GRADE['POINTS'] * $COURSE_OFFERING_GRADE['WEIGHT'];
			$COURSE_OFFERING_GRADE['PK_COURSE_OFFERING'] 	= $DATA1->COURSE_OFFERING_ID;
			$COURSE_OFFERING_GRADE['PK_ACCOUNT']  			= $PK_ACCOUNT;
			$COURSE_OFFERING_GRADE['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_COURSE_OFFERING_GRADE', $COURSE_OFFERING_GRADE, 'insert');
			$INTERNAL_ID_ARRAY[] = $db->insert_ID();
		}
		$data['INTERNAL_ID'] = $INTERNAL_ID_ARRAY;
		
		$data['MESSAGE'] = 'Course Offering Grade Book Created';
	}
}

$data = json_encode($data);
echo $data;
