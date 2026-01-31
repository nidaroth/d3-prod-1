<? require_once("../global/config.php"); 
require_once("../language/common.php");

$PK_TERM_MASTER	= $_REQUEST['PK_TERM_MASTER']; 
$cond = "";
if($_REQUEST['PK_COURSE'] != '') 
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE  = '$_REQUEST[PK_COURSE]' "; // Ticket # 1341 
	
/* Ticket # 1341  */	
if($_REQUEST['PK_CAMPUS'] != '') 
	$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS  IN ($_REQUEST[PK_CAMPUS]) "; 
/* Ticket # 1341  */ 

/* Ticket # 1342 */
$sort_order = " S_COURSE_OFFERING.ACTIVE DESC, S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ";
if($_REQUEST['sort'] == "asc")
	$sort_order = " S_COURSE_OFFERING.ACTIVE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC, S_TERM_MASTER.BEGIN_DATE DESC ";
/* Ticket # 1342 */
?>
<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control " onchange="get_course_details();" >
	<option value=""></option>
	<? $res_cs = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE,SESSION, SESSION_NO,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, COURSE_DESCRIPTION, S_COURSE_OFFERING.ACTIVE, TRANSCRIPT_CODE,COALESCE(sqSC.C,0) AS NO_STUDENT from 
	S_COURSE_OFFERING
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN (SELECT SC.PK_COURSE_OFFERING, COUNT(*) AS C FROM S_STUDENT_COURSE AS SC WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_COURSE_OFFERING) AS sqSC ON S_COURSE_OFFERING.PK_COURSE_OFFERING = sqSC.PK_COURSE_OFFERING
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($PK_TERM_MASTER) $cond  ORDER BY $sort_order "); //DIAM-2187
			
	while (!$res_cs->EOF) { 
	if( $res_cs->fields['NO_STUDENT']>0){ //DIAM-2187
		if($_REQUEST['dont_show_term'] == 1)
			$txt = $res_cs->fields['COURSE_CODE'].' ('.substr($res_cs->fields['SESSION'],0,1).'-'.$res_cs->fields['SESSION_NO'].') '.$res_cs->fields['TRANSCRIPT_CODE'].' - '.$res_cs->fields['COURSE_DESCRIPTION'].' - '.$res_cs->fields['TERM_BEGIN_DATE'];
		if($_REQUEST['dont_show_term'] == 2) // Ticket # 1341
			$txt = $res_cs->fields['COURSE_CODE'].' ('.substr($res_cs->fields['SESSION'],0,1).'-'.$res_cs->fields['SESSION_NO'].') '.$res_cs->fields['TRANSCRIPT_CODE'].' - '.$res_cs->fields['COURSE_DESCRIPTION'].' - '.$res_cs->fields['TERM_BEGIN_DATE']; // Ticket # 1341
		else
			$txt = $res_cs->fields['COURSE_CODE'].' ('.substr($res_cs->fields['SESSION'],0,1).'-'.$res_cs->fields['SESSION_NO'].') '.$res_cs->fields['TRANSCRIPT_CODE'].' - '.$res_cs->fields['COURSE_DESCRIPTION'].' - '.$res_cs->fields['TERM_BEGIN_DATE'];
			
		if($res_cs->fields['ACTIVE'] == 0)
			$txt .= ' (Inactive)';
		?>
		<option value="<?=$res_cs->fields['PK_COURSE_OFFERING']?>" <? if($_REQUEST['def_val'] == $res_cs->fields['PK_COURSE_OFFERING']) echo "selected"; ?> <? if($res_cs->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$txt ?></option>
	<? } //DIAM-2187 ?>
	<?	$res_cs->MoveNext();
	} ?>
</select>
