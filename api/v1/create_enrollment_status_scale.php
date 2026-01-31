<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"STATUS_NAME":"API","SCALE":[{"CODE":"F","MIN_UNITS_PER_TERM":"1.00"},{"CODE":"3Q","MIN_UNITS_PER_TERM":"2.00"},{"CODE":"H","MIN_UNITS_PER_TERM":"3.00"},{"CODE":"LH","MIN_UNITS_PER_TERM":"5.00"}]}';

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
	
	if($DATA->STATUS_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Status Name Missing';
	}
	
	$i = 0;
	foreach($DATA->SCALE as $SCALE){
		if($SCALE->CODE == '') {
			$error[$i] = ' Code Missing';
			$i++;
		} else {
			$CODE = trim($SCALE->CODE);
			
			$res = $db->Execute("select PK_SCHOOL_ENROLLMENT_STATUS from M_SCHOOL_ENROLLMENT_STATUS WHERE CODE = '$CODE' ");
			if($res->RecordCount() == 0) {
				$error[$i] = $CODE.' - Invalid Code ';
				$i++;
			}
		}
	}
	
	if($i > 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = implode(",",$error);
	}
	
	
	if($data['SUCCESS'] == 1) {
		$ENROLLMENT_STATUS_SCALE_MASTER['ENROLLMENT_STATUS'] 	= $DATA->STATUS_NAME;
		$ENROLLMENT_STATUS_SCALE_MASTER['PK_ACCOUNT']  			= $PK_ACCOUNT;
		$ENROLLMENT_STATUS_SCALE_MASTER['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('M_ENROLLMENT_STATUS_SCALE_MASTER', $ENROLLMENT_STATUS_SCALE_MASTER, 'insert');
		$PK_ENROLLMENT_STATUS_SCALE_MASTER = $db->insert_ID();
				
		foreach($DATA->SCALE as $SCALE){
			if($SCALE->CODE != ''){ 
				
				$CODE = trim($SCALE->CODE);
				$res = $db->Execute("select PK_SCHOOL_ENROLLMENT_STATUS from M_SCHOOL_ENROLLMENT_STATUS WHERE CODE = '$CODE' ");
				
				$ENROLLMENT_STATUS_SCALE = array();
				$ENROLLMENT_STATUS_SCALE['PK_ENROLLMENT_STATUS_SCALE_MASTER'] 	= $PK_ENROLLMENT_STATUS_SCALE_MASTER;
				$ENROLLMENT_STATUS_SCALE['PK_SCHOOL_ENROLLMENT_STATUS'] 		= $res->fields['PK_SCHOOL_ENROLLMENT_STATUS'];
				$ENROLLMENT_STATUS_SCALE['MIN_UNITS_PER_TERM']  				= $SCALE->MIN_UNITS_PER_TERM;
				$ENROLLMENT_STATUS_SCALE['PK_ACCOUNT']  						= $PK_ACCOUNT;
				$ENROLLMENT_STATUS_SCALE['CREATED_ON']  						= date("Y-m-d H:i");
				db_perform('M_ENROLLMENT_STATUS_SCALE', $ENROLLMENT_STATUS_SCALE, 'insert');
			}
		}
		
		$data['INTERNAL_ID'] = $PK_ENROLLMENT_STATUS_SCALE_MASTER;
		$data['MESSAGE'] 	 = 'Enrollment Status Scale Created';
		
		$res = $db->Execute("select PK_ENROLLMENT_STATUS_SCALE_MASTER,ENROLLMENT_STATUS from M_ENROLLMENT_STATUS_SCALE_MASTER where PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 AND PK_ENROLLMENT_STATUS_SCALE_MASTER = '$PK_ENROLLMENT_STATUS_SCALE_MASTER' ");
		$i = 0;
		while (!$res->EOF) { 
		
			$PK_ENROLLMENT_STATUS_SCALE_MASTER = $res->fields['PK_ENROLLMENT_STATUS_SCALE_MASTER'];
			$j = 0;
			$res_det = $db->Execute("select PK_ENROLLMENT_STATUS_SCALE,CODE,DESCRIPTION,MIN_UNITS_PER_TERM from M_ENROLLMENT_STATUS_SCALE LEFT JOIN M_SCHOOL_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS_SCALE.PK_SCHOOL_ENROLLMENT_STATUS = M_SCHOOL_ENROLLMENT_STATUS.PK_SCHOOL_ENROLLMENT_STATUS where PK_ACCOUNT = '$PK_ACCOUNT' AND M_ENROLLMENT_STATUS_SCALE.ACTIVE = 1 AND PK_ENROLLMENT_STATUS_SCALE_MASTER = '$PK_ENROLLMENT_STATUS_SCALE_MASTER' ");
			while (!$res_det->EOF) { 
				$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['CODE'] 				= $res_det->fields['CODE'];
				$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['DESCRIPTION'] 			= $res_det->fields['DESCRIPTION'];
				$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['MIN_UNITS_PER_TERM']	= $res_det->fields['MIN_UNITS_PER_TERM'];
				$data['ENROLLMENT_STATUS'][$i]['SCALE'][$j]['INTERNAL_ID'] 			= $res_det->fields['PK_ENROLLMENT_STATUS_SCALE'];
				$j++;
				$res_det->MoveNext();
			}
			
			$i++;
			$res->MoveNext();
		}
	}
}

$data = json_encode($data);
echo $data;
