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
	if($_GET['begin_date'] != '')
		$cond .= " AND trim(BEGIN_DATE) = '$_GET[begin_date]' ";
		
	if($_GET['old_dsis_id'] != '')
		$cond .= " AND trim(OLD_DSIS_ID) = '$_GET[old_dsis_id]' ";

	$res = $db->Execute("SELECT PK_TERM_MASTER,OLD_DSIS_ID,BEGIN_DATE FROM S_TERM_MASTER WHERE PK_ACCOUNT = '$PK_ACCOUNT' $cond " );
	if($res->RecordCount() == 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'No Record Found';
	}
	
	$i = 0;
	while (!$res->EOF) { 
		$data['RESULT'][$i]['OLD_DSIS_ID'] 	= $res->fields['OLD_DSIS_ID'];
		$data['RESULT'][$i]['ID'] 			= $res->fields['PK_TERM_MASTER'];
		$data['RESULT'][$i]['BEGIN_DATE'] 	= $res->fields['BEGIN_DATE'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;
