<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"CODE":"from","TITLE":"api","IPEDS_CATEGORY_ID":"1"}';

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
	
	if($DATA->CODE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Code Missing';
	}
	
	if($DATA->TITLE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Title Missing';
	}
	
	$PK_IPEDS_CATEGORY_MASTER = trim($DATA->IPEDS_CATEGORY_ID);
	if(strtolower($PK_IPEDS_CATEGORY_MASTER) == 'null')
		$PK_IPEDS_CATEGORY_MASTER = '';
		
	if($PK_IPEDS_CATEGORY_MASTER != '') {
		$res = $db->Execute("SELECT PK_IPEDS_CATEGORY_MASTER FROM M_IPEDS_CATEGORY_MASTER WHERE ACTIVE = 1 AND PK_IPEDS_CATEGORY_MASTER = '$PK_IPEDS_CATEGORY_MASTER' ");
		if($res->RecordCount() == 0) {
			$PK_IPEDS_CATEGORY_MASTER = $res->fields['PK_IPEDS_CATEGORY_MASTER'];
			
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid IPEDS Category Value';
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$SOC_CODE['SOC_CODE'] 					= trim($DATA->CODE);
		$SOC_CODE['SOC_TITLE']					= trim($DATA->TITLE);
		$SOC_CODE['PK_IPEDS_CATEGORY_MASTER']	= $PK_IPEDS_CATEGORY_MASTER;
		$SOC_CODE['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$SOC_CODE['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('M_SOC_CODE', $SOC_CODE, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		
		$data['MESSAGE'] = 'SOC Code Created';
	}
}

$data = json_encode($data);
echo $data;
