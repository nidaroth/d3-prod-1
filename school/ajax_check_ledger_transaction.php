<? require_once("../global/config.php");

$cond  = "";
$field = "";
$table = "";

if($_POST['DATE_TYPE'] == 1) {
	$field  = " S_STUDENT_LEDGER.TRANSACTION_DATE ";
} else if($_POST['DATE_TYPE'] == 2) {
	$field  = " S_STUDENT_LEDGER.CREATED_ON ";	
}

if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
	$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	$cond .= " AND $field BETWEEN '$ST' AND '$ET' ";
} else if($_POST['START_DATE'] != ''){
	$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	$cond .= " AND $field >= '$ST' ";
} else if($_POST['END_DATE'] != ''){
	$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	$cond .= " AND $field <= '$ET' ";
}

if(!empty($_POST['PK_CAMPUS'])) {
	$table = ", S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS in (".$_POST['PK_CAMPUS'].") AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT ";
}

$res = $db->Execute("SELECT S_STUDENT_LEDGER.PK_STUDENT_LEDGER  
FROM 
S_STUDENT_LEDGER 
LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
, S_STUDENT_ENROLLMENT 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
, S_STUDENT_MASTER  $table 
WHERE 
S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND EXPORTED_DATE != '0000-00-00' $cond  ");

if($res->RecordCount() == 0)
	echo "a";
else
	echo "b";