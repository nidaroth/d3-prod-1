<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));
//$DATA = '{"START_DATE":"2022-01-01","END_DATE":"2022-01-10","TITLE":"From API","LEAVE_TYPE":"2","SESSION":["Day","Morning"]}';

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

function displayDates($date1, $date2, $format = 'm/d/Y' ) {
	$dates = array();
	$current = strtotime($date1);
	$date2 	 = strtotime($date2);
	$stepVal = '+1 day';
	while( $current <= $date2 ) {
		$dates[] = date($format, $current);
		$current = strtotime($stepVal, $current);
	}
	return $dates;
}

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

	if($DATA->START_DATE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Start Date Missing';
	}
	
	if($DATA->LEAVE_TYPE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Leave Type Missing';
	} else {
		if($DATA->LEAVE_TYPE == 1 || $DATA->LEAVE_TYPE == 2 || $DATA->LEAVE_TYPE == 3) {
		} else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Leave Type Value '.$DATA->LEAVE_TYPE;
		}
	}
	
	$PK_SESSION_ARR	 = array();
	if(!empty($DATA->SESSION)){
		foreach($DATA->SESSION as $SESSION) {
			$SESSION = trim($SESSION);
			$res_st  = $db->Execute("select PK_SESSION from M_SESSION WHERE trim(SESSION) = '$SESSION' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid SESSION Value - '.$SESSION;
			} else
				$PK_SESSION_ARR[] = $res_st->fields['PK_SESSION'];
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$ACADEMIC_CALENDAR['START_DATE'] = trim($DATA->START_DATE);
		$ACADEMIC_CALENDAR['END_DATE'] 	 = trim($DATA->END_DATE);
		$ACADEMIC_CALENDAR['LEAVE_TYPE'] = trim($DATA->LEAVE_TYPE);
		$ACADEMIC_CALENDAR['TITLE'] 	 = trim($DATA->TITLE);
		
		if($ACADEMIC_CALENDAR['START_DATE'] != '')
			$ACADEMIC_CALENDAR['START_DATE'] = date("Y-m-d",strtotime($ACADEMIC_CALENDAR['START_DATE']));
			
		if($ACADEMIC_CALENDAR['END_DATE'] != '')
			$ACADEMIC_CALENDAR['END_DATE'] = date("Y-m-d",strtotime($ACADEMIC_CALENDAR['END_DATE']));
			
		if($ACADEMIC_CALENDAR['LEAVE_TYPE'] == 1)
			$ACADEMIC_CALENDAR['END_DATE'] = $ACADEMIC_CALENDAR['START_DATE'];
			
		$ACADEMIC_CALENDAR['PK_ACCOUNT']  = $PK_ACCOUNT;
		$ACADEMIC_CALENDAR['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_ACADEMIC_CALENDAR', $ACADEMIC_CALENDAR, 'insert');
		$PK_ACADEMIC_CALENDAR = $db->insert_ID();

		if($ACADEMIC_CALENDAR['LEAVE_TYPE'] == 1)
			$SCHEDULE_DATES[] = $ACADEMIC_CALENDAR['START_DATE'];
		else {
			if($ACADEMIC_CALENDAR['START_DATE'] != '' && $ACADEMIC_CALENDAR['END_DATE'] != '')
				$SCHEDULE_DATES = displayDates($ACADEMIC_CALENDAR['START_DATE'], $ACADEMIC_CALENDAR['END_DATE'],'Y-m-d');
		}

		foreach($SCHEDULE_DATES as $SCHEDULE_DATE){ 
			foreach($PK_SESSION_ARR as $PK_SESSION) {
				$ACADEMIC_CALENDAR_SESSION['PK_ACADEMIC_CALENDAR']  = $PK_ACADEMIC_CALENDAR;
				$ACADEMIC_CALENDAR_SESSION['ACADEMY_DATE']  		= $SCHEDULE_DATE;
				$ACADEMIC_CALENDAR_SESSION['PK_SESSION']  			= $PK_SESSION;
				$ACADEMIC_CALENDAR_SESSION['PK_ACCOUNT']  			= $PK_ACCOUNT;
				$ACADEMIC_CALENDAR_SESSION['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('M_ACADEMIC_CALENDAR_SESSION', $ACADEMIC_CALENDAR_SESSION, 'insert');
			}
		}
		
		$data['INTERNAL_ID'] = $PK_ACADEMIC_CALENDAR;
		$data['MESSAGE'] 	 = 'Data Created';
	}
}

$data = json_encode($data);
echo $data;
