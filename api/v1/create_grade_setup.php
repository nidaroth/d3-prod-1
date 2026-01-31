<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"GRADE_SETUP":[{"GRADE":"A1","NUMBER_GRADE":"11","IS_DEFAULT":"Yes","CALCULATE_GPA":"No","UNITS_ATTEMPTED":"No","UNITS_COMPLETED":"No","UNITS_IN_PROGRESS":"No","WEIGHTED_GRADE_CALC":"No","RETAKE_UPDATE":"No","DISPLAY_ORDER":"22"},{"GRADE":"A2","NUMBER_GRADE":"Yes","IS_DEFAULT":"No","CALCULATE_GPA":"No","UNITS_ATTEMPTED":"No","UNITS_COMPLETED":"No","UNITS_IN_PROGRESS":"No","WEIGHTED_GRADE_CALC":"No","RETAKE_UPDATE":"No","DISPLAY_ORDER":"23"}]}';

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
	foreach($DATA->GRADE_SETUP as $GRADE_SETUP){
	
		if($GRADE_SETUP->GRADE == '') {
			$error[$i] = 'Missing Grade';
			$i++;
		}
		
		if($GRADE_SETUP->CALCULATE_GPA == '') {
			$CALCULATE_GPA = 0;
		} else {
			if(strtolower($GRADE_SETUP->CALCULATE_GPA) == 'yes') 
				$CALCULATE_GPA = 1;
			else if(strtolower($GRADE_SETUP->CALCULATE_GPA) == 'no') 
				$CALCULATE_GPA = 0;
			else {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Calculate GPA Value';
			}
		}
		
		if($GRADE_SETUP->UNITS_ATTEMPTED == '') {
			$UNITS_ATTEMPTED = 0;
		} else {
			if(strtolower($GRADE_SETUP->UNITS_ATTEMPTED) == 'yes') 
				$UNITS_ATTEMPTED = 1;
			else if(strtolower($GRADE_SETUP->UNITS_ATTEMPTED) == 'no') 
				$UNITS_ATTEMPTED = 0;
			else {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Units Attempted Value';
			}
		}
		
		if($GRADE_SETUP->UNITS_COMPLETED == '') {
			$UNITS_COMPLETED = 0;
		} else {
			if(strtolower($GRADE_SETUP->UNITS_COMPLETED) == 'yes') 
				$UNITS_COMPLETED = 1;
			else if(strtolower($GRADE_SETUP->UNITS_COMPLETED) == 'no') 
				$UNITS_COMPLETED = 0;
			else {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Units Completed Value';
			}
		}
		
		if($GRADE_SETUP->UNITS_IN_PROGRESS == '') {
			$UNITS_IN_PROGRESS = 0;
		} else {
			if(strtolower($GRADE_SETUP->UNITS_IN_PROGRESS) == 'yes') 
				$UNITS_IN_PROGRESS = 1;
			else if(strtolower($GRADE_SETUP->UNITS_IN_PROGRESS) == 'no') 
				$UNITS_IN_PROGRESS = 0;
			else {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Units in Progress Value';
			}
		}
		
		if($GRADE_SETUP->WEIGHTED_GRADE_CALC == '') {
			$WEIGHTED_GRADE_CALC = 0;
		} else {
			if(strtolower($GRADE_SETUP->WEIGHTED_GRADE_CALC) == 'yes') 
				$WEIGHTED_GRADE_CALC = 1;
			else if(strtolower($GRADE_SETUP->WEIGHTED_GRADE_CALC) == 'no') 
				$WEIGHTED_GRADE_CALC = 0;
			else {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Weighted Grade Calc Value';
			}
		}
		
		if($GRADE_SETUP->RETAKE_UPDATE == '') {
			$RETAKE_UPDATE = 0;
		} else {
			if(strtolower($GRADE_SETUP->RETAKE_UPDATE) == 'yes') 
				$RETAKE_UPDATE = 1;
			else if(strtolower($GRADE_SETUP->RETAKE_UPDATE) == 'no') 
				$RETAKE_UPDATE = 0;
			else {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Retake Update Value';
			}
		}
		
		if($GRADE_SETUP->IS_DEFAULT == '') {
			$IS_DEFAULT = 0;
		} else {
			if(strtolower($GRADE_SETUP->IS_DEFAULT) == 'yes') 
				$IS_DEFAULT = 1;
			else if(strtolower($GRADE_SETUP->IS_DEFAULT) == 'no') 
				$IS_DEFAULT = 0;
			else {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Is Default Value';
			}
		}
	}

	if($i > 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = implode(",",$error);
	}

	if($data['SUCCESS'] == 1) {
		foreach($DATA->GRADE_SETUP as $GRADE_SETUP){	
			if($IS_DEFAULT == 1) {
				$db->Execute("UPDATE S_GRADE SET IS_DEFAULT = 0 WHERE PK_ACCOUNT = '$PK_ACCOUNT' ");
			}
			
			$S_GRADE = array();
			$S_GRADE['GRADE'] 					= $GRADE_SETUP->GRADE;
			$S_GRADE['NUMBER_GRADE'] 			= $GRADE_SETUP->NUMBER_GRADE;
			$S_GRADE['IS_DEFAULT'] 				= $IS_DEFAULT;
			$S_GRADE['CALCULATE_GPA'] 			= $CALCULATE_GPA;
			$S_GRADE['UNITS_ATTEMPTED'] 		= $UNITS_ATTEMPTED;
			$S_GRADE['UNITS_COMPLETED'] 		= $UNITS_COMPLETED;
			$S_GRADE['UNITS_IN_PROGRESS'] 		= $UNITS_IN_PROGRESS;
			$S_GRADE['WEIGHTED_GRADE_CALC'] 	= $WEIGHTED_GRADE_CALC;
			$S_GRADE['RETAKE_UPDATE'] 			= $RETAKE_UPDATE;
			$S_GRADE['DISPLAY_ORDER'] 			= $GRADE_SETUP->DISPLAY_ORDER;
			$S_GRADE['PK_ACCOUNT']  			= $PK_ACCOUNT;
			$S_GRADE['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_GRADE', $S_GRADE, 'insert');
		}
						
		$data['MESSAGE'] = 'Grade Setup Created';
		
		$res = $db->Execute("SELECT PK_GRADE,GRADE,NUMBER_GRADE, IF(CALCULATE_GPA = 1,'Yes','No') as CALCULATE_GPA, IF(UNITS_ATTEMPTED = 1,'Yes','No') as UNITS_ATTEMPTED, IF(UNITS_COMPLETED = 1,'Yes','No') as UNITS_COMPLETED, IF(UNITS_IN_PROGRESS = 1,'Yes','No') as UNITS_IN_PROGRESS, IF(WEIGHTED_GRADE_CALC = 1,'Yes','No') as WEIGHTED_GRADE_CALC, IF(RETAKE_UPDATE = 1,'Yes','No') as RETAKE_UPDATE, IF(IS_DEFAULT = 1,'Yes','No') as IS_DEFAULT, DISPLAY_ORDER FROM S_GRADE WHERE PK_ACCOUNT = '$PK_ACCOUNT' ORDER BY DISPLAY_ORDER ASC");
		$i = 0;
		while (!$res->EOF) { 
			$data['GRADE_SETUP'][$i]['ID'] 			 		= $res->fields['PK_GRADE'];
			$data['GRADE_SETUP'][$i]['GRADE'] 	 			= $res->fields['GRADE'];
			$data['GRADE_SETUP'][$i]['NUMBER_GRADE'] 	 	= $res->fields['NUMBER_GRADE'];
			
			$data['GRADE_SETUP'][$i]['CALCULATE_GPA'] 		= $res->fields['CALCULATE_GPA'];
			$data['GRADE_SETUP'][$i]['UNITS_ATTEMPTED'] 	= $res->fields['UNITS_ATTEMPTED'];
			$data['GRADE_SETUP'][$i]['UNITS_COMPLETED'] 	= $res->fields['UNITS_COMPLETED'];
			$data['GRADE_SETUP'][$i]['UNITS_IN_PROGRESS'] 	= $res->fields['UNITS_IN_PROGRESS'];
			$data['GRADE_SETUP'][$i]['WEIGHTED_GRADE_CALC'] = $res->fields['WEIGHTED_GRADE_CALC'];
			$data['GRADE_SETUP'][$i]['RETAKE_UPDATE'] 		= $res->fields['RETAKE_UPDATE'];
			$data['GRADE_SETUP'][$i]['IS_DEFAULT'] 			= $res->fields['IS_DEFAULT'];
			
			$data['GRADE_SETUP'][$i]['DISPLAY_ORDER'] 		= $res->fields['DISPLAY_ORDER'];
			
			$i++;
			$res->MoveNext();
		}
		
	}
}

$data = json_encode($data);
echo $data;
