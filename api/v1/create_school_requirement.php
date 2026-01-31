<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

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

	if($data['SUCCESS'] == 1) {
		foreach($DATA->SCHOOL_REQUIREMENTS as $SCHOOL_REQUIREMENT){	
			if($SCHOOL_REQUIREMENT->TEXT != ''){ 
				$MANDATORY = '';
				if(strtolower(trim($SCHOOL_REQUIREMENT->MANDATORY)) == 'yes')
					$MANDATORY = 1;
					
				$SCHOOL_REQUIREMENT_ARR = array();
				$SCHOOL_REQUIREMENT_ARR['REQUIREMENT'] 	= trim($SCHOOL_REQUIREMENT->TEXT);
				$SCHOOL_REQUIREMENT_ARR['MANDATORY'] 	= $MANDATORY;
				$SCHOOL_REQUIREMENT_ARR['PK_ACCOUNT']  	= $PK_ACCOUNT;
				$SCHOOL_REQUIREMENT_ARR['CREATED_ON']  	= date("Y-m-d H:i");
				db_perform('S_SCHOOL_REQUIREMENT', $SCHOOL_REQUIREMENT_ARR, 'insert');
				$PK_SCHOOL_REQUIREMENT = $db->insert_ID;
				
				$res_stu = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = '$PK_STUDENT_STATUS' ");
				while (!$res_stu->EOF) {
					$STUDENT_REQUIREMENT['PK_STUDENT_MASTER'] 		= $res_stu->fields['PK_STUDENT_MASTER'];
					$STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT'] 	= $res_stu->fields['PK_STUDENT_ENROLLMENT'];;
					$STUDENT_REQUIREMENT['TYPE'] 				  	= 1;
					$STUDENT_REQUIREMENT['ID'] 				  		= $PK_SCHOOL_REQUIREMENT;
					$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $SCHOOL_REQUIREMENT_ARR['REQUIREMENT'];
					$STUDENT_REQUIREMENT['MANDATORY'] 				= $SCHOOL_REQUIREMENT_ARR['MANDATORY'];
					$STUDENT_REQUIREMENT['PK_ACCOUNT']  			= $PK_ACCOUNT;
					$STUDENT_REQUIREMENT['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert');

					$res_stu->MoveNext();
				}
			}
		}
						
		$data['MESSAGE'] = 'School Requirement Created';
		
		$res = $db->Execute("select PK_SCHOOL_REQUIREMENT,REQUIREMENT, IF(MANDATORY = 1,'Yes','No') as MANDATORY from S_SCHOOL_REQUIREMENT where PK_ACCOUNT='$PK_ACCOUNT' AND ACTIVE=1");
		$i = 0;
		while (!$res->EOF) { 
			$data['SCHOOL_REQUIREMENTS'][$i]['ID'] 		  = $res->fields['PK_SCHOOL_REQUIREMENT'];
			$data['SCHOOL_REQUIREMENTS'][$i]['TEXT'] 	  = $res->fields['REQUIREMENT'];
			$data['SCHOOL_REQUIREMENTS'][$i]['MANDATORY'] = $res->fields['MANDATORY'];
			
			$i++;
			$res->MoveNext();
		}
	}
}

$data = json_encode($data);
echo $data;
