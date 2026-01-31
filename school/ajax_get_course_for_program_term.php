<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_CAMPUS_PROGRAM 	= $_REQUEST['PK_CAMPUS_PROGRAM'];
$PK_TERM_MASTER 	= $_REQUEST['PK_TERM_MASTER'];

$res_type = $db->Execute("select PK_CAMPUS_PROGRAM,COURSE_CODE,SESSION,SESSION_NO FROM M_CAMPUS_PROGRAM_COURSE, S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND M_CAMPUS_PROGRAM_COURSE.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND M_CAMPUS_PROGRAM_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$PK_TERM_MASTER' "); ?>
<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM" class="form-control" onchange="get_course_offering_detail(this.value);get_student(this.value);" >
	<option></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['COURSE_CODE'].' ('.$res_type->fields['SESSION'].'-'.$res_type->fields['SESSION_NO'].')' ?></option>
		<? $res_type->MoveNext();
	} ?>
</select>