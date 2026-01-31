<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"NAME":"Drop Down11","DATA_TYPE_ID":"2","OPTIONS":[{"ID":"1","TEXT":"Drop Down 1","DISPLAY_ORDER":"1"},{"ID":"4","TEXT":"Drop Down 4","DISPLAY_ORDER":"3"}]}';

$DATA = ($DATA);
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
	
	if($DATA->NAME == '') {
		$error[$i] = 'Name Department Missing';
		$i++;
	}
	
	if($DATA->DATA_TYPE_ID == '') {
		$error[$i] = 'Date Type ID Missing';
		$i++;
	} else {
		$PK_DATA_TYPES = trim($DATA->DATA_TYPE_ID);
		$res = $db->Execute("SELECT PK_DATA_TYPES FROM M_DATA_TYPES WHERE  ACTIVE = 1 AND PK_DATA_TYPES = '$PK_DATA_TYPES' ");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Date Type ID';
		}
	}

	if($i > 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = implode(",",$error);
	}

	if($data['SUCCESS'] == 1) {
		$USER_DD['NAME'] 			= $DATA->NAME;
		$USER_DD['PK_DATA_TYPES'] 	= $PK_DATA_TYPES;
		$USER_DD['PK_ACCOUNT']  	= $PK_ACCOUNT;
		$USER_DD['CREATED_ON']  	= date("Y-m-d H:i");
		db_perform('S_USER_DEFINED_FIELDS', $USER_DD, 'insert');
		$PK_USER_DEFINED_FIELDS = $db->insert_ID();
	
		foreach($DATA->OPTIONS as $OPTIONS){
			$USER_DEFINED_FIELDS_DETAIL = array();
			$USER_DEFINED_FIELDS_DETAIL['OPTION_NAME'] 			  = trim($OPTIONS->TEXT);
			$USER_DEFINED_FIELDS_DETAIL['DISPLAY_ORDER'] 		  = trim($OPTIONS->DISPLAY_ORDER);
			$USER_DEFINED_FIELDS_DETAIL['PK_USER_DEFINED_FIELDS'] = $PK_USER_DEFINED_FIELDS;
			db_perform('S_USER_DEFINED_FIELDS_DETAIL', $USER_DEFINED_FIELDS_DETAIL, 'insert');
		}
						
		$data['MESSAGE'] 		= 'User Defined Fields Created';
		$data['INTERNAL_ID'] 	= $PK_USER_DEFINED_FIELDS;
		
		$res = $db->Execute("SELECT PK_USER_DEFINED_FIELDS,NAME,DATA_TYPES, S_USER_DEFINED_FIELDS.PK_DATA_TYPES FROM S_USER_DEFINED_FIELDS LEFT JOIN M_DATA_TYPES ON M_DATA_TYPES.PK_DATA_TYPES = S_USER_DEFINED_FIELDS.PK_DATA_TYPES WHERE S_USER_DEFINED_FIELDS.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ");
		$i = 0;
		while (!$res->EOF) { 
			
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
}

$data = json_encode($data);
echo $data;
