<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"FIELD_NAME":"api","TAB":"Enrollment","SECTION_ID":"1","DEPARTMENT_ID":"-1","DATA_TYPE_ID":"1"}';

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
	
	if($DATA->FIELD_NAME == '') {
		$error[$i] = 'Field Name Missing';
		$i++;
	}
	
	if($DATA->SECTION_ID == '') {
		$error[$i] = 'Section ID Missing';
		$i++;
	} else {
		if($DATA->SECTION_ID != 1 && $DATA->SECTION_ID != 2) {
			$error[$i] = 'Invalid Section ID Value';
			$i++;
		}
	}
	
	if($DATA->SECTION_ID == 1){
		/*if($DATA->DEPARTMENT_ID == '') {
			$error[$i] = 'Department ID Missing';
			$i++;
		} else {*/
			$PK_DEPARTMENT = trim($DATA->DEPARTMENT_ID);
			
			if($PK_DEPARTMENT == -1) {
			} else {
				$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 AND PK_DEPARTMENT = '$PK_DEPARTMENT' ");
				if($res->RecordCount() == 0) {
					$error[$i] = 'Invalid Department ID';
					$i++;
				} else {
					$PK_DEPARTMENT = $res->fields['PK_DEPARTMENT'];
				}
			}
		//}
		
		/*if($DATA->TAB == '') {
			$error[$i] = 'Tab Missing';
			$i++;
		} else {
			if($DATA->TAB != 'Info' && $DATA->TAB != 'Other' && $DATA->TAB != 'Financial Aid') {
				$error[$i] = 'Invalid Tab Value';
				$i++;
			}
		}*/
	}
	
	if($DATA->DATA_TYPE_ID == '') {
		$error[$i] = 'Data Type ID Missing';
		$i++;
	} else {
		$PK_DATA_TYPES = trim($DATA->DATA_TYPE_ID);
		
		$res = $db->Execute("SELECT PK_DATA_TYPES FROM M_DATA_TYPES WHERE PK_DATA_TYPES = '$PK_DATA_TYPES' ");
		if($res->RecordCount() == 0) {
			$error[$i] = 'Invalid Data Type ID Missing';
			$i++;
		} else {
			$PK_DATA_TYPES = $res->fields['PK_DATA_TYPES'];
		}
	}
	
	if($PK_DATA_TYPES == 2 || $PK_DATA_TYPES == 3){
		if($DATA->USER_DEFINED_FIELD_ID == '') {
			$error[$i] = 'User Defined Field ID Missing';
			$i++;
		} else {
			$PK_USER_DEFINED_FIELDS = trim($DATA->USER_DEFINED_FIELD_ID);
			
			$res = $db->Execute("SELECT PK_USER_DEFINED_FIELDS FROM S_USER_DEFINED_FIELDS WHERE PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' AND PK_DATA_TYPES = '$PK_DATA_TYPES' ");
			if($res->RecordCount() == 0) {
				$error[$i] = 'Invalid User Defined Field ID Missing';
				$i++;
			} else {
				$PK_USER_DEFINED_FIELDS = $res->fields['PK_USER_DEFINED_FIELDS'];
			}
		}
	}

	if($i > 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = implode(",",$error);
	}

	if($data['SUCCESS'] == 1) {
		$CUSTOM_FIELDS['SECTION'] 					= $DATA->SECTION_ID;
		$CUSTOM_FIELDS['PK_DEPARTMENT'] 			= $PK_DEPARTMENT;
		$CUSTOM_FIELDS['TAB'] 						= $DATA->TAB;
		$CUSTOM_FIELDS['FIELD_NAME'] 				= $DATA->FIELD_NAME;
		$CUSTOM_FIELDS['PK_DATA_TYPES'] 			= $PK_DATA_TYPES;
		$CUSTOM_FIELDS['PK_USER_DEFINED_FIELDS'] 	= $PK_USER_DEFINED_FIELDS;
		
		if(strtolower($CUSTOM_FIELDS['TAB']) == 'enrollment')
			$CUSTOM_FIELDS['TAB'] = 'Other';
		
		$CUSTOM_FIELDS['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$CUSTOM_FIELDS['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('S_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'insert');
		$PK_CUSTOM_FIELDS = $db->insert_ID();
				
		$data['MESSAGE'] 		= 'User Defined Fields Created';
		$data['INTERNAL_ID'] 	= $PK_CUSTOM_FIELDS;
		
	}
}

$data = json_encode($data);
echo $data;
