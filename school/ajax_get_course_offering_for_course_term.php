<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){  
	header("location:../index");
	exit;
}

$PK_COURSE 		= $_REQUEST['PK_COURSE'];
$PK_TERM_MASTER = $_REQUEST['PK_TERM_MASTER'];

$res_type = $db->Execute("select PK_COURSE_OFFERING,COURSE_CODE,SESSION,SESSION_NO, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE FROM S_COURSE, S_COURSE_OFFERING LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE S_COURSE.PK_COURSE = '$PK_COURSE' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_TERM_MASTER = '$PK_TERM_MASTER' ORDER BY COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC "); ?>
<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" onchange="get_course_offering_detail(this.value);get_student(this.value);" >
	<option></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_COURSE_OFFERING']?>" ><?=$res_type->fields['COURSE_CODE'].' ('.substr($res_type->fields['SESSION'],0,1).'-'.$res_type->fields['SESSION_NO'].') '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION'].' - '.$res_type->fields['TERM_BEGIN_DATE'] ?></option>
		<? $res_type->MoveNext();
	} ?>
</select>