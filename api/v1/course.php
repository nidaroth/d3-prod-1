<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"PREREQUISITE_COURSE":[6,7]}';

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

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
	
	$res_st = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
	if($res_st->RecordCount() == 0){
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] = 'Invalid Value - '.$_GET[id];
	}
	
	if(!empty($DATA->PREREQUISITE_COURSE)){
		foreach($DATA->PREREQUISITE_COURSE as $PK_COURSE) {
			$res_st = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] = 'Invalid PREREQUISITE_COURSE Value - '.$PK_COURSE;
			} else 
				$PK_PREREQUISITE_COURSE_ARR[] = $PK_COURSE;
		}
	}
	
	/*if(!empty($DATA->COREQUISITE_COURSE)){
		foreach($DATA->COREQUISITE_COURSE as $PK_COURSE) {
			$res_st = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] = 'Invalid COREQUISITE_COURSE Value - '.$PK_COURSE;
			} else 
				$PK_COURSE_COREQUISITES_ARR[] = $PK_COURSE;
		}
	}*/

	if($data['SUCCESS'] == 1) {
		
		if(!empty($PK_PREREQUISITE_COURSE_ARR)){
			foreach($PK_PREREQUISITE_COURSE_ARR as $PK_PREREQUISITE_COURSE) {
				$res = $db->Execute("SELECT PK_COURSE_PREREQUISITE FROM S_COURSE_PREREQUISITE WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_PREREQUISITE_COURSE = '$PK_PREREQUISITE_COURSE' "); 

				if($res->RecordCount() == 0) {
					$COURSE_PREREQUISITE['PK_COURSE']   			= $_GET['id'];
					$COURSE_PREREQUISITE['PK_PREREQUISITE_COURSE'] 	= $PK_PREREQUISITE_COURSE;
					$COURSE_PREREQUISITE['PK_ACCOUNT'] 				= $PK_ACCOUNT;
					$COURSE_PREREQUISITE['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_COURSE_PREREQUISITE', $COURSE_PREREQUISITE, 'insert');
					$PK_COURSE_PREREQUISITE_ARR[] = $db->insert_ID();
				} else {
					$PK_COURSE_PREREQUISITE_ARR[] = $res->fields['PK_COURSE_PREREQUISITE'];
				}
			}
		}
		
		$cond = "";
		if(!empty($PK_COURSE_PREREQUISITE_ARR))
			$cond = " AND PK_COURSE_PREREQUISITE NOT IN (".implode(",",$PK_COURSE_PREREQUISITE_ARR).") ";
			
		$db->Execute("DELETE FROM S_COURSE_PREREQUISITE WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$PK_ACCOUNT' $cond "); 	
	
		/*if(!empty($PK_COURSE_COREQUISITES_ARR)){
			foreach($PK_COURSE_COREQUISITES_ARR as $PK_COURSE_COREQUISITES) {
				$COURSE_COREQUISITES['PK_COURSE']   			= $_GET['id'];
				$COURSE_COREQUISITES['PK_COREQUISITES_COURSE'] 	= $PK_COURSE_COREQUISITES;
				$COURSE_COREQUISITES['PK_ACCOUNT'] 				= $PK_ACCOUNT;
				$COURSE_COREQUISITES['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_COURSE_COREQUISITES', $COURSE_COREQUISITES, 'insert');
			}
		}*/
		
		/////////////////
		
		$data['MESSAGE'] = 'Course Updated';
		
	}
}

$data = json_encode($data);
echo $data;