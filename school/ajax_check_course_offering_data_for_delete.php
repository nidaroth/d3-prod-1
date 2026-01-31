<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['id'];

$res_def_grade 	= $db->Execute("SELECT PK_GRADE  FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_DEFAULT = 1 ");
$PK_GRADE 		= $res_def_grade->fields['PK_GRADE'];

$res_grade  = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND (FINAL_GRADE > 0 AND FINAL_GRADE != '$PK_GRADE') ");
$res_attend = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND COMPLETED = 1");
$res_stu 	= $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

if($res_grade->RecordCount() == 0 && $res_attend->RecordCount() == 0 && $res_stu->RecordCount() == 0 ) {
	echo "0|||"; ?>
	<div class="row">
		<div class="col-md-10"  ><?=DELETE_MESSAGE_GENERAL?><br /></div>
	</div>
<? } else { 
	echo "1|||"; ?>
	<div class="row">
		<div class="col-md-10" style="color:red;font-weight:bold" ><?=DELETE_WARNING?><br /></div>
	</div>
	<? if($res_grade->RecordCount() > 0){ ?>
	<div class="row">
		<div class="col-md-1">
			<input type="checkbox" name="DELETE_CHECK[]" onclick="enable_delete_btn()" >
		</div>
		<div class="col-md-10"><?=DELETE_ALL_GRADE_FOR_THIS_COURSE?></div>
	</div>
<?  } 
	if($res_attend->RecordCount() > 0){ ?>
	<div class="row">
		<div class="col-md-1">
			<input type="checkbox" name="DELETE_CHECK[]" onclick="enable_delete_btn()" >
		</div>
		<div class="col-md-10"><?=DELETE_ALL_ATTENDANCE_FOR_THIS_COURSE?></div>
	</div>
<?  }
	if($res_stu->RecordCount() > 0){ ?>
	<div class="row">
		<div class="col-md-1">
			<input type="checkbox" name="DELETE_CHECK[]" onclick="enable_delete_btn()" >
		</div>
		<div class="col-md-10"><?=REMOVE_ALL_STUDENT_FROM_THIS_COURSE?></div>
	</div>
<?  } ?>
	<div class="row">
		<div class="col-md-1">
			<input type="checkbox" name="DELETE_CHECK[]" onclick="enable_delete_btn()" >
		</div>
		<div class="col-md-10"><?=DELETE_THE_COURSE_OFFERING?></div>
	</div>
<? } 
//echo "<br />".' --- '.$res_grade->RecordCount().' --- '.$res_attend->RecordCount().' --- '.$res_stu->RecordCount().' --- '; ?>