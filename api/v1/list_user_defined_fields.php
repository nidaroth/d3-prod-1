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

	$res = $db->Execute("SELECT PK_USER_DEFINED_FIELDS,NAME,DATA_TYPES, S_USER_DEFINED_FIELDS.PK_DATA_TYPES FROM S_USER_DEFINED_FIELDS LEFT JOIN M_DATA_TYPES ON M_DATA_TYPES.PK_DATA_TYPES = S_USER_DEFINED_FIELDS.PK_DATA_TYPES WHERE S_USER_DEFINED_FIELDS.PK_ACCOUNT = '$PK_ACCOUNT' AND S_USER_DEFINED_FIELDS.ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$PK_USER_DEFINED_FIELDS = $res->fields['PK_USER_DEFINED_FIELDS'];
		$data['USER_DEFINED_FIELDS'][$i]['ID'] 			 = $res->fields['PK_USER_DEFINED_FIELDS'];
		$data['USER_DEFINED_FIELDS'][$i]['NAME'] 	 	 = $res->fields['NAME'];
		$data['USER_DEFINED_FIELDS'][$i]['DATA_TYPE'] 	 = $res->fields['DATA_TYPES'];
		$data['USER_DEFINED_FIELDS'][$i]['DATA_TYPE_ID'] = $res->fields['PK_DATA_TYPES'];
		
		$j = 0;
		$res_det = $db->Execute("SELECT * FROM S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' AND ACTIVE = 1 ");
		while (!$res_det->EOF) { 
			$data['USER_DEFINED_FIELDS'][$i]['OPTIONS'][$j]['ID'] 				= $res_det->fields['PK_USER_DEFINED_FIELDS_DETAIL'];
			$data['USER_DEFINED_FIELDS'][$i]['OPTIONS'][$j]['TEXT'] 			= $res_det->fields['OPTION_NAME'];
			$data['USER_DEFINED_FIELDS'][$i]['OPTIONS'][$j]['DISPLAY_ORDER'] 	= $res_det->fields['DISPLAY_ORDER'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;