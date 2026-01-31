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

	$res = $db->Execute("SELECT PK_CUSTOM_FIELDS,FIELD_NAME,IF(SECTION = 1, 'Student',IF(SECTION = 2, 'Employee', IF(SECTION = 3, 'Teacher',''))) AS SECTION_NAME, DEPARTMENT, TAB, DATA_TYPES, SECTION, S_CUSTOM_FIELDS.PK_DEPARTMENT, S_CUSTOM_FIELDS.PK_DATA_TYPES, S_USER_DEFINED_FIELDS.NAME as USER_DEFINED_FIELD, S_CUSTOM_FIELDS.PK_USER_DEFINED_FIELDS  FROM S_CUSTOM_FIELDS LEFT JOIN M_DATA_TYPES ON M_DATA_TYPES.PK_DATA_TYPES = S_CUSTOM_FIELDS.PK_DATA_TYPES LEFT JOIN S_USER_DEFINED_FIELDS ON S_USER_DEFINED_FIELDS.PK_USER_DEFINED_FIELDS = S_CUSTOM_FIELDS.PK_USER_DEFINED_FIELDS LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_CUSTOM_FIELDS.PK_DEPARTMENT WHERE  S_CUSTOM_FIELDS.PK_ACCOUNT = '$PK_ACCOUNT' AND S_CUSTOM_FIELDS.ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['CUSTOM_FIELDS'][$i]['ID'] 		 	= $res->fields['PK_CUSTOM_FIELDS'];
		$data['CUSTOM_FIELDS'][$i]['FIELD_NAME'] 	= $res->fields['FIELD_NAME'];
		$data['CUSTOM_FIELDS'][$i]['SECTION'] 	 	= $res->fields['SECTION_NAME'];
		$data['CUSTOM_FIELDS'][$i]['SECTION_ID'] 	= $res->fields['SECTION'];
		$data['CUSTOM_FIELDS'][$i]['DEPARTMENT'] 	= $res->fields['DEPARTMENT'];
		$data['CUSTOM_FIELDS'][$i]['DEPARTMENT_ID'] = $res->fields['PK_DEPARTMENT'];
		$data['CUSTOM_FIELDS'][$i]['TAB'] 		 	= $res->fields['TAB'];
		$data['CUSTOM_FIELDS'][$i]['DATA_TYPE']  	= $res->fields['DATA_TYPES'];
		$data['CUSTOM_FIELDS'][$i]['DATA_TYPE_ID']  = $res->fields['PK_DATA_TYPES'];
		$data['CUSTOM_FIELDS'][$i]['USER_DEFINED_FIELD_NAME']  	= $res->fields['USER_DEFINED_FIELD'];
		$data['CUSTOM_FIELDS'][$i]['USER_DEFINED_FIELD_ID']  	= $res->fields['PK_USER_DEFINED_FIELDS'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;