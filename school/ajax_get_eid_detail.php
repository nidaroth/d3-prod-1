<? require_once("../global/config.php"); 
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _1098T FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_1098T'] == 0){
	header("location:../index");
	exit;
}

$CAMPUS = "";
$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_CAMPUS, _1098T_EIN_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = _1098T_EIN_CAMPUS.PK_CAMPUS  AND PK_1098T_EIN = '$_REQUEST[id]' order by CAMPUS_CODE ASC");
while (!$res_campus->EOF) { 
	if($CAMPUS != '')
		$CAMPUS .= ', ';
		
	$CAMPUS .= $res_campus->fields['CAMPUS_CODE'];
	$res_campus->MoveNext();
}

$res = $db->Execute("select * from _1098T_EIN WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_1098T_EIN = '$_REQUEST[id]' ");
echo $res->fields['TRANSMITTER_CONTROL_CODE']."|||".$res->fields['EIN_NO']."|||".$res->fields['CONTACT_NAME']."|||".$res->fields['CONTACT_PHONE']."|||".$res->fields['CONTACT_EMAIL']."|||".$CAMPUS;