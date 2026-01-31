<? require_once("../global/config.php");

$PK_STUDENT_ENROLLMENT = $_REQUEST['eid'];
$res = $db->Execute("call REGR10101('Graduation50', ".$PK_STUDENT_ENROLLMENT.", 'DATE')");

if($res->fields['CALCULATED_DATE'] != '' && $res->fields['CALCULATED_DATE'] != '0000-00-00') {
	echo date("m/d/Y", strtotime($res->fields['CALCULATED_DATE']));
} else
	echo "";