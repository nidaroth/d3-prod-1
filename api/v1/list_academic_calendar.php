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
	
	$cond = "";
	if($_GET['start_dt'] != '')
		$cond .= " AND START_DATE = '$_GET[start_dt]' ";
	if($_GET['end_dt'] != '')
		$cond .= " AND END_DATE = '$_GET[end_dt]' ";	
	if($_GET['leave_type'] != '')
		$cond .= " AND LEAVE_TYPE = '$_GET[leave_type]' ";	

	$res = $db->Execute("SELECT * FROM M_ACADEMIC_CALENDAR WHERE PK_ACCOUNT = '$PK_ACCOUNT' $cond ");
	$i = 0;
	while (!$res->EOF) { 
		$PK_ACADEMIC_CALENDAR = $res->fields['PK_ACADEMIC_CALENDAR'];
		$data['ACADEMIC_CALENDAR'][$i]['ID'] 	 		= $PK_ACADEMIC_CALENDAR;
		$data['ACADEMIC_CALENDAR'][$i]['TITLE'] 		= $res->fields['TITLE'];
		$data['ACADEMIC_CALENDAR'][$i]['LEAVE_TYPE'] 	= $res->fields['LEAVE_TYPE'];
		$data['ACADEMIC_CALENDAR'][$i]['START_DATE'] 	= $res->fields['START_DATE'];
		$data['ACADEMIC_CALENDAR'][$i]['END_DATE'] 		= $res->fields['END_DATE'];
		
		if($res->fields['ACTIVE'] == 1)
			$data['ACADEMIC_CALENDAR'][$i]['ACTIVE'] = 'Yes';
		else
			$data['ACADEMIC_CALENDAR'][$i]['ACTIVE'] = 'No';
			
		$SESSION = array();
		
		$res_session = $db->Execute("SELECT SESSION FROM M_SESSION, M_ACADEMIC_CALENDAR_SESSION WHERE M_ACADEMIC_CALENDAR_SESSION.PK_ACCOUNT = '$PK_ACCOUNT' AND M_SESSION.PK_SESSION = M_ACADEMIC_CALENDAR_SESSION.PK_SESSION AND PK_ACADEMIC_CALENDAR = '$PK_ACADEMIC_CALENDAR' GROUP BY M_ACADEMIC_CALENDAR_SESSION.PK_SESSION ");
		while (!$res_session->EOF) { 
			$SESSION[] = $res_session->fields['SESSION'];
			
			$res_session->MoveNext();
		}
		
		$data['ACADEMIC_CALENDAR'][$i]['SESSION'] = $SESSION;
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;