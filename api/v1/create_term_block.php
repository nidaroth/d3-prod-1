<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

/*$DATA = '{
   "BEGIN_DATE":"2020-11-01",
   "END_DATE":"2021-03-31",
   "DESCRIPTION":"Desc",
   "JAN":"0",
   "FEB":"0",
   "MAR":"0",
   "APR":"0",
   "MAY":"0",
   "JUN":"0",
   "JUL":"31",
   "AUG":"31",
   "SEP":"18",
   "OCT":"0",
   "NOV":"0",
   "DEC":"0",
   "EARNINGS_DAYS":"80",
}';*/

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
	
	if($DATA->BEGIN_DATE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Begin Date Missing';
	} 
	
	if($DATA->END_DATE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'End Date Missing';
	} 

	if($data['SUCCESS'] == 1) {
	
		/*$days[1]  = 0;
		$days[2]  = 0;
		$days[3]  = 0;
		$days[4]  = 0;
		$days[5]  = 0;
		$days[6]  = 0;
		$days[7]  = 0;
		$days[8]  = 0;
		$days[9]  = 0;
		$days[10] = 0;
		$days[11] = 0;
		$days[12] = 0;
		
		$TOT_DAYS = 0;
		$current = strtotime(trim($DATA->BEGIN_DATE));
		$date2 	 = strtotime(trim($DATA->END_DATE));
		$stepVal = '+1 day';
		while( $current <= $date2 ) {
		
			$flag		= 1;
			$temp_date 	= date('Y-m-d', $current);

			$res_type = $db->Execute("select PK_ACADEMIC_CALENDAR_SESSION from M_ACADEMIC_CALENDAR,M_ACADEMIC_CALENDAR_SESSION WHERE M_ACADEMIC_CALENDAR.PK_ACCOUNT = '$PK_ACCOUNT' AND M_ACADEMIC_CALENDAR.ACTIVE = 1 AND M_ACADEMIC_CALENDAR_SESSION.ACTIVE = 1 AND ACADEMY_DATE = '$temp_date' AND LEAVE_TYPE = 2 AND M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_SESSION.PK_ACADEMIC_CALENDAR ");
			if($res_type->RecordCount() == 0 ) {
				$n 			= date('n',strtotime($temp_date));
				$val 		= $days[$n];
				$val++;
				$days[$n] 	= $val;
			}
			
			$current = strtotime($stepVal, $current);
		}*/

		$TERM_BLOCK['BEGIN_DATE'] 	= trim($DATA->BEGIN_DATE);
		$TERM_BLOCK['END_DATE'] 	= trim($DATA->END_DATE);
		
		$TERM_BLOCK['JAN'] 			= trim($DATA->JAN);
		$TERM_BLOCK['FEB'] 			= trim($DATA->FEB);
		$TERM_BLOCK['MAR'] 			= trim($DATA->MAR);
		$TERM_BLOCK['APR'] 			= trim($DATA->APR);
		$TERM_BLOCK['MAY'] 			= trim($DATA->MAY);
		$TERM_BLOCK['JUN'] 			= trim($DATA->JUN);
		$TERM_BLOCK['JUL'] 			= trim($DATA->JUL);
		$TERM_BLOCK['AUG'] 			= trim($DATA->AUG);
		$TERM_BLOCK['SEP'] 			= trim($DATA->SEP);
		$TERM_BLOCK['OCT'] 			= trim($DATA->OCT);
		$TERM_BLOCK['NOV'] 			= trim($DATA->NOV);
		$TERM_BLOCK['DECEMBER'] 	= trim($DATA->DEC);
		$TERM_BLOCK['DAYS'] 		= trim($DATA->EARNINGS_DAYS);
		
		//$TERM_BLOCK['DAYS'] 		= $TERM_BLOCK['JAN'] + $TERM_BLOCK['FEB'] + $TERM_BLOCK['MAR'] + $TERM_BLOCK['APR'] + $TERM_BLOCK['MAY'] + $TERM_BLOCK['JUN'] + $TERM_BLOCK['JUL'] + $TERM_BLOCK['AUG'] + $TERM_BLOCK['SEP'] + $TERM_BLOCK['OCT'] + $TERM_BLOCK['NOV'] + $TERM_BLOCK['DECEMBER'];
		$TERM_BLOCK['DESCRIPTION'] 	= trim($DATA->DESCRIPTION);
		$TERM_BLOCK['PK_ACCOUNT']  	= $PK_ACCOUNT;
		$TERM_BLOCK['CREATED_ON']  	= date("Y-m-d H:i");
		db_perform('S_TERM_BLOCK', $TERM_BLOCK, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] = 'Term Block Created';
	}
}

$data = json_encode($data);
echo $data;
