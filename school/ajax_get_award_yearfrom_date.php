<? require_once("../global/config.php"); 
$date = date("Y-m-d",strtotime($_REQUEST['date']));
$res = $db->Execute("select PK_AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 AND '$date' BETWEEN BEGIN_DATE AND END_DATE  ");
echo $res->fields['PK_AWARD_YEAR'];