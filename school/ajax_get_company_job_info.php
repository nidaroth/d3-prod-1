<? require_once("../global/config.php"); 
require_once("../language/common.php");

$PK_COMPANY 	= $_REQUEST['cid'];
$PK_COMPANY_JOB = $_REQUEST['id'];

$res = $db->Execute("SELECT * FROM S_COMPANY_JOB WHERE PK_COMPANY = '$PK_COMPANY' AND PK_COMPANY_JOB = '$PK_COMPANY_JOB' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' LIMIT 1"); 

$result 						= array();
$result['JOB_TITLE'] 			= '';
$result['PK_PLACEMENT_TYPE']	= '';
$result['PK_COMPANY_CONTACT']	= '';
$result['PK_SOC_CODE']			= '';
$result['PK_ENROLLMENT_STATUS']	= '';
$result['PK_PAY_TYPE']			= '';
$result['PAY_AMOUNT']			= '';
$result['ANNUAL_SALARY']		= '';
$result['WEEKLY_HOURS']			= '';
$result['PK_PLACEMENT_STATUS']	= '';

if($res->RecordCount() > 0) {
	$result['JOB_TITLE'] 			= $res->fields['JOB_TITLE'];
	$result['PK_PLACEMENT_TYPE']  	= $res->fields['PK_PLACEMENT_TYPE'];
	$result['PK_COMPANY_CONTACT'] 	= $res->fields['PK_COMPANY_CONTACT'];
	$result['PK_SOC_CODE'] 			= $res->fields['PK_SOC_CODE'];
	$result['PK_ENROLLMENT_STATUS'] = $res->fields['PK_ENROLLMENT_STATUS'];
	$result['PK_PAY_TYPE'] 			= $res->fields['PK_PAY_TYPE'];
	$result['PAY_AMOUNT'] 			= $res->fields['PAY_AMOUNT'];
	$result['ANNUAL_SALARY']		= $res->fields['ANNUAL_SALARY'];
	$result['WEEKLY_HOURS'] 		= $res->fields['WEEKLY_HOURS'];
	$result['PK_PLACEMENT_STATUS'] 	= $res->fields['PK_PLACEMENT_STATUS'];
	
	$result['INSTITUTIONAL_EMPLOYMENT'] = $res->fields['INSTITUTIONAL_EMPLOYMENT'];
	$result['SELF_EMPLOYED'] 			= $res->fields['SELF_EMPLOYED'];
}

echo json_encode($result);
?>
