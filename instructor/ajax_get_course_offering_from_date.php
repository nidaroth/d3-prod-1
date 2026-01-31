<? require_once("../global/config.php"); 
require_once("../language/common.php");

$date  = $_REQUEST['date']; 
if($date != '')
	$date = date("Y-m-d", strtotime($date));
	
$cond  = " AND SCHEDULE_DATE = '$date' ";

?>
<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" onchange="get_schedule(this.value)" >
	<option ></option>
	<? $res_cs = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE,SESSION, SESSION_NO,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, COURSE_DESCRIPTION from 

	S_COURSE_OFFERING 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
	,S_TERM_MASTER, S_COURSE_OFFERING_SCHEDULE_DETAIL
	
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER  AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING $cond GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER BY COURSE_CODE ASC ");
			
	while (!$res_cs->EOF) { ?>
		<option value="<?=$res_cs->fields['PK_COURSE_OFFERING']?>" <? if($_REQUEST['def'] == $res_cs->fields['PK_COURSE_OFFERING']) echo "selected"; ?> ><?=$res_cs->fields['COURSE_CODE'].' ('.substr($res_cs->fields['SESSION'],0,1).' - '.$res_cs->fields['SESSION_NO'].') '.$res_cs->fields['COURSE_DESCRIPTION'] ?></option>
	<?	$res_cs->MoveNext();
	} ?>
</select>