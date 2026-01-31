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

	$res = $db->Execute("SELECT PK_SOC_CODE,SOC_CODE,SOC_TITLE, IPEDS_CATEGORY, M_SOC_CODE.PK_IPEDS_CATEGORY_MASTER FROM M_SOC_CODE LEFT JOIN M_IPEDS_CATEGORY_MASTER ON M_IPEDS_CATEGORY_MASTER.PK_IPEDS_CATEGORY_MASTER = M_SOC_CODE.PK_IPEDS_CATEGORY_MASTER WHERE M_SOC_CODE.PK_ACCOUNT = '$PK_ACCOUNT' AND M_SOC_CODE.ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['SOC_CODE'][$i]['ID'] 				= $res->fields['PK_SOC_CODE'];
		$data['SOC_CODE'][$i]['CODE'] 				= $res->fields['SOC_CODE'];
		$data['SOC_CODE'][$i]['TITLE'] 				= $res->fields['SOC_TITLE'];
		$data['SOC_CODE'][$i]['IPEDS_CATEGORY'] 	= $res->fields['IPEDS_CATEGORY'];
		$data['SOC_CODE'][$i]['IPEDS_CATEGORY_ID'] 	= $res->fields['PK_IPEDS_CATEGORY_MASTER'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;