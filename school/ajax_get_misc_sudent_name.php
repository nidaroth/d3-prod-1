<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$search = trim($_GET['search']);

$SSN = trim($search);
$SSN = preg_replace( '/[^0-9]/', '',$SSN);
if(strlen($SSN) == 4) {
	$SSN  = 'xxxxx'.$SSN;
	$SSN1 = $SSN;
	
	$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
	$SSN1 = my_encrypt('',$SSN1);
	$SSN1 = substr($SSN1,8);
	
	//$sub_where .= " S_STUDENT_MASTER.SSN like '%$SSN1' ";
} else {
	$SSN1 = $SSN;
	$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
	$SSN1 = my_encrypt('',$SSN1);
	
	//$sub_where .= " S_STUDENT_MASTER.SSN = '$SSN1' ";
}

$result = $db->Execute($_SESSION['MISC_BATCH_STU_QUERY']."  AND ( CONCAT(trim(LAST_NAME),', ',trim(FIRST_NAME)) LIKE '$search%' OR trim(LAST_NAME) LIKE '$search%' OR trim(FIRST_NAME) LIKE '$search%' OR STUDENT_ID LIKE '$search%' OR SSN LIKE '$SSN1%' ) ".$_SESSION['MISC_BATCH_STU_GROUP_BY']." ".$_SESSION['MISC_BATCH_STU_ORDER_BY']);
$i = 0;
while (!$result->EOF){ 

	$SSN = $result->fields['SSN'];
	if($SSN != '') {
		$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'],$SSN);
		$SSN_ORG = $SSN;
		$SSN_ARR = explode("-",$SSN);
		$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
	}
	$data = '';
	if($SSN != '')
	{
		$data = ', '.$SSN;
	}

	$item[$i]['itemName'] 	= $result->fields['NAME'].' ('.$result->fields['STUDENT_ID'].$data.')';
	$item[$i]['itemId'] 	= $result->fields['PK_STUDENT_MASTER'];
	$item[$i]['ssn'] 		= $SSN;
	
	$i++;
	$result->MoveNext();
} 
echo json_encode($item);