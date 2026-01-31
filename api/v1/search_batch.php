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
	
	$PK 	= "";
	$TABLE 	= "";
	if(strtolower(trim($_GET['batch_type'])) == 'payment'){
		$PK 	= "PK_PAYMENT_BATCH_MASTER";
		$TABLE 	= "S_PAYMENT_BATCH_MASTER";
	} else if(strtolower(trim($_GET['batch_type'])) == 'tuition'){
		$PK 	= "PK_TUITION_BATCH_MASTER";
		$TABLE 	= "S_TUITION_BATCH_MASTER";
	} else if(strtolower(trim($_GET['batch_type'])) == 'misc'){
		$PK 	= "PK_MISC_BATCH_MASTER";
		$TABLE 	= "S_MISC_BATCH_MASTER";
	}
	$BATCH_NO = trim($_GET['batch_no']);
	
	$res = $db->Execute("SELECT $PK,BATCH_NO FROM $TABLE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND BATCH_NO = '$BATCH_NO' ");
	if($res->RecordCount() == 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'No Record Found';
	}
	
	$i = 0;
	while (!$res->EOF) { 
		$data['RESULT'][$i]['BATCH_NO'] 	= $res->fields['BATCH_NO'];
		$data['RESULT'][$i]['ID'] 			= $res->fields[$PK];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;
