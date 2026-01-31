<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_TERM_MASTER		= $_REQUEST['val'];
$id					= $_REQUEST['id'];
$multiple			= $_REQUEST['multiple'];
$PK_COURSE_OFFERING = $_REQUEST['PK_COURSE_OFFERING'];

$campus_cond = "";
if($_REQUEST['filter_campus'] == 1) {
	$PK_STUDENT_MASTER = $_REQUEST['s_id'];
	$res_type = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_ACTIVE_ENROLLMENT = 1");
	$PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
	
	$res_type = $db->Execute("select PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_type->EOF) {
		if($campus_cond != '')
			$campus_cond .= ',';
		$campus_cond .= $res_type->fields['PK_CAMPUS'];
		
		$res_type->MoveNext();
	}
	$campus_cond = " AND S_COURSE_OFFERING.PK_CAMPUS IN ($campus_cond) ";
}

if($multiple == 1)
	$name = "PK_COURSE_OFFERING[]";
else
	$name = "PK_COURSE_OFFERING";
	
if($id != '')
	$id = "PK_COURSE_OFFERING_".$id;
else
	$id = "PK_COURSE_OFFERING";
	
?>
<select id="<?=$id?>" name="<?=$name?>" class="form-control <? if($_REQUEST['make_required'] == 1) { ?> required-entry <? } ?> " onchange="get_course_offering_session(this.value,'<?=$_REQUEST['id']?>');get_course_desc(this.value,'<?=$_REQUEST['id']?>');get_course_units(this.value,'<?=$_REQUEST['id']?>');" > <!-- Ticket #1663 -->
	<option value="" ></option>	
	<? $res_type = $db->Execute("select PK_COURSE_OFFERING,COURSE_CODE, SESSION, SESSION_NO, COURSE_DESCRIPTION from S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION  WHERE S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$PK_TERM_MASTER' AND S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE $campus_cond ORDER BY COURSE_CODE ASC"); //Ticket # 1250  
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_COURSE_OFFERING'] ?>" <? if($res_type->fields['PK_COURSE_OFFERING'] == $PK_COURSE_OFFERING) echo "selected"; ?> ><?=$res_type->fields['COURSE_CODE'].' ('. substr($res_type->fields['SESSION'],0,1).' - '. $res_type->fields['SESSION_NO'].') '.$res_type->fields['COURSE_DESCRIPTION'] ?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>