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
	if($_GET['str'] != '')
		$cond = " AND '$_GET[str]' BETWEEN BEGIN_DATE AND END_DATE ";

	$res = $db->Execute("SELECT PK_TERM_MASTER,BEGIN_DATE, END_DATE, TERM_DESCRIPTION,TERM_GROUP,IF(ALLOW_ONLINE_ENROLLMENT = 1, 'Yes', 'No') AS ALLOW_ONLINE_ENROLLMENT ,IF(LMS_ACTIVE = 1, 'Yes', 'No') AS LMS_ACTIVE FROM S_TERM_MASTER where PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 $cond  ");
	$i = 0;
	while (!$res->EOF) { 
		$data['TERM'][$i]['ID'] 						= $res->fields['PK_TERM_MASTER'];
		$data['TERM'][$i]['BEGIN_DATE'] 				= $res->fields['BEGIN_DATE'];
		$data['TERM'][$i]['END_DATE'] 					= $res->fields['END_DATE'];
		$data['TERM'][$i]['DESCRIPTION'] 				= $res->fields['TERM_DESCRIPTION'];
		$data['TERM'][$i]['TERM_GROUP'] 				= $res->fields['TERM_GROUP'];
		$data['TERM'][$i]['OLD_DSIS_ID'] 				= $res->fields['OLD_DSIS_ID'];
		$data['TERM'][$i]['ALLOW_ONLINE_ENROLLMENT'] 	= $res->fields['ALLOW_ONLINE_ENROLLMENT'];
		$data['TERM'][$i]['LMS_ACTIVE'] 				= $res->fields['LMS_ACTIVE'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;