<? require_once("../global/config.php");
$PK_ACCOUNT = $_REQUEST['PK_ACCOUNT'];

$res_type = $db->Execute("select PK_COURSE_OFFERING,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,COURSE_CODE,SESSION,SESSION_NO FROM S_COURSE, S_COURSE_OFFERING LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE  S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$PK_ACCOUNT' ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC  "); ?>
<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" onchange="get_course_offering_detail(this.value);get_student(this.value);" >
	<option></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_COURSE_OFFERING']?>" ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['COURSE_CODE'].' ('.$res_type->fields['SESSION'].'-'.$res_type->fields['SESSION_NO'].')' ?></option>
		<? $res_type->MoveNext();
	} ?>
</select>