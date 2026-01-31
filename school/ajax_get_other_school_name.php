<? require_once("../global/config.php"); 

require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0 && $PLACEMENT_ACCESS == 0){
	header("location:../index");
	exit;
}

$search = $_GET['search'];	

$result = $db->Execute("SELECT PK_STUDENT_OTHER_EDU, SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, OTHER_SCHOOL_PHONE, OTHER_SCHOOL_FAX, S_STUDENT_OTHER_EDU.PK_STATE FROM S_STUDENT_OTHER_EDU LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_OTHER_EDU.PK_STATE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SCHOOL_NAME LIKE '$search%' GROUP BY SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, S_STUDENT_OTHER_EDU.PK_STATE, ZIP, OTHER_SCHOOL_PHONE, OTHER_SCHOOL_FAX ");
$i = 0;
while (!$result->EOF){ 
	$item[$i]['itemName'] 	= $result->fields['SCHOOL_NAME']." (".trim($result->fields['ADDRESS']." ".$result->fields['ADDRESS_1'])." ".$result->fields['CITY']." ".$result->fields['STATE_CODE']." Ph: ".$result->fields['OTHER_SCHOOL_PHONE']." Fax: ".$result->fields['OTHER_SCHOOL_FAX'].")";
	$item[$i]['itemId'] 	= $result->fields['SCHOOL_NAME'];
	
	$item[$i]['itemADDRESS'] 		= $result->fields['ADDRESS'];
	$item[$i]['itemADDRESS_1'] 		= $result->fields['ADDRESS_1'];
	$item[$i]['itemCITY'] 			= $result->fields['CITY'];
	$item[$i]['itemSTATE_CODE'] 	= $result->fields['STATE_CODE'];
	$item[$i]['itemZIP'] 			= $result->fields['ZIP'];
	$item[$i]['itemPHONE'] 			= $result->fields['OTHER_SCHOOL_PHONE'];
	$item[$i]['itemFAX'] 			= $result->fields['OTHER_SCHOOL_FAX'];
	$item[$i]['itemPK_STATE'] 		= $result->fields['PK_STATE'];
	
	$i++;
	$result->MoveNext();
} 
echo json_encode($item);