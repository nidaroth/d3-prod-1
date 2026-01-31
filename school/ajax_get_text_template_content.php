<? require_once("../global/config.php"); 
require_once('replace_student_tags.php'); //Ticket # 1429 

$res_temp = $db->Execute("SELECT * FROM S_TEXT_TEMPLATE WHERE PK_TEXT_TEMPLATE = '$_REQUEST[val]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 

$res = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, FIRST_NAME, LAST_NAME, STUDENT_STATUS, S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS, SESSION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, USER_ID   
FROM 
S_STUDENT_MASTER 
LEFT JOIN Z_USER ON Z_USER.ID = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_USER_TYPE = 3 
, S_STUDENT_ENROLLMENT 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]'");

$CONTENT = $res_temp->fields['CONTENT'];
$CONTENT = str_ireplace("{Logo}",'',$CONTENT);
$CONTENT = str_ireplace("{LOGIN_ID}",$res->fields['USER_ID'],$CONTENT);
$CONTENT = str_ireplace("{LOGIN ID}",$res->fields['USER_ID'],$CONTENT);
$CONTENT = str_ireplace("{Term}",$res->fields['BEGIN_DATE'],$CONTENT);
$CONTENT = str_ireplace("{Session}",$res->fields['SESSION'],$CONTENT);
$CONTENT = str_ireplace("{Student Status}",$res->fields['STUDENT_STATUS'],$CONTENT);
$CONTENT = str_ireplace("{First Name}",$res->fields['FIRST_NAME'],$CONTENT);
$CONTENT = str_ireplace("{Last Name}",$res->fields['LAST_NAME'],$CONTENT);
$CONTENT = str_ireplace("{Student Name}",$res->fields['NAME'],$CONTENT);
	$CONTENT = str_ireplace("{FIRST_NAME}",$res->fields['FIRST_NAME'],$CONTENT);
	$CONTENT = str_ireplace("{LAST_NAME}",$res->fields['LAST_NAME'],$CONTENT);

$CONTENT = replace_mail_content($CONTENT, $_GET['eid'], $_SESSION['PK_ACCOUNT']); //Ticket # 1429 
	
echo $res_temp->fields['PK_TEXT_SETTINGS']."|||".$CONTENT;