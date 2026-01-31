<? require_once("../global/config.php"); 
require_once("../language/common.php");

$date  = $_REQUEST['date']; 
if($date != '')
	$date = date("Y-m-d", strtotime($date));
	
$cond  = " AND SCHEDULE_DATE = '$date' ";

//DIAM-1422
if(!empty($_REQUEST['PK_CAMPUS'])){
	$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS = '$_REQUEST[PK_CAMPUS]'";
}
//DIAM-1422

?>
<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control " onchange="get_course_details();" >
	<option value=""></option>
	<? $res_cs = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE,SESSION, SESSION_NO,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, COURSE_DESCRIPTION, S_COURSE_OFFERING.ACTIVE, TRANSCRIPT_CODE from 
	S_COURSE_OFFERING
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	, S_COURSE_OFFERING_SCHEDULE_DETAIL

	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING $cond ORDER BY S_COURSE_OFFERING.ACTIVE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC, S_TERM_MASTER.BEGIN_DATE DESC ");
			
	while (!$res_cs->EOF) { 
		$txt = $res_cs->fields['COURSE_CODE'].' ('.substr($res_cs->fields['SESSION'],0,1).'-'.$res_cs->fields['SESSION_NO'].') '.$res_cs->fields['TRANSCRIPT_CODE'].' - '.$res_cs->fields['COURSE_DESCRIPTION'].' - '.$res_cs->fields['TERM_BEGIN_DATE'];

		if($res_cs->fields['ACTIVE'] == 0)
			$txt .= ' (Inactive)';
		?>
		<option value="<?=$res_cs->fields['PK_COURSE_OFFERING']?>" <? if($_REQUEST['def_val'] == $res_cs->fields['PK_COURSE_OFFERING']) echo "selected"; ?> <? if($res_cs->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$txt ?></option>
	<?	$res_cs->MoveNext();
	} ?>
</select>
