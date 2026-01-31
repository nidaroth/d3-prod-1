<? require_once("../global/config.php"); 
require_once("../global/create_notification.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("get_department_from_t.php");

$PK_STUDENT_MASTER = $_REQUEST['id'];

$sts = "";
/*
$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_END_DATE = 1 ");
while (!$res_type->EOF) {
	if($sts != '')
		$sts .= ',';
		
	$sts .= $res_type->fields['PK_STUDENT_STATUS'];
	
	$res_type->MoveNext();
}
AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($sts) */

$PK_STUDENT_ENROLLMENT = '';
$res_type = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(CODE,' - ',M_CAMPUS_PROGRAM.DESCRIPTION) AS PROGRAM,IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT( BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1,STUDENT_STATUS from S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'    ");
if($res_type->RecordCount() <= 1) {
	$PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
} else {
	while (!$res_type->EOF) {
		if($PK_STUDENT_ENROLLMENT != '')
			$PK_STUDENT_ENROLLMENT .= '|||';
			
		$PK_STUDENT_ENROLLMENT .= $res_type->fields['PK_STUDENT_ENROLLMENT'].'^^^^'.$res_type->fields['PROGRAM'].' - '.$res_type->fields['BEGIN_DATE_1'].' ['.$res_type->fields['STUDENT_STATUS'].']';
		
		$res_type->MoveNext();
	}
}
echo $PK_STUDENT_ENROLLMENT;