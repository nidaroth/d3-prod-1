<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}


$PK_TERM_MASTER	= $_REQUEST['PK_TERM_MASTER'];
$PK_COURSE		= $_REQUEST['PK_COURSE'];
$multiple		= $_REQUEST['multiple'];

if($multiple == 1)
	$name = "PK_COURSE_OFFERING[]";
else
	$name = "PK_COURSE_OFFERING";
	
$id = "PK_COURSE_OFFERING";
	
$term_cond = "";
if($PK_TERM_MASTER != '')
	$term_cond = " AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($PK_TERM_MASTER) "; 

?>
<select id="<?=$id?>" name="<?=$name?>" <? if($multiple == "1") echo "multiple"; ?> class="form-control"  >
	<? $res_type = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, COURSE_CODE, S_TERM_MASTER.BEGIN_DATE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, SESSION_NO, SESSION, TRANSCRIPT_CODE, COURSE_DESCRIPTION 
	from 
	S_STUDENT_COURSE, S_GRADE, S_COURSE, S_STUDENT_MASTER, S_COURSE_OFFERING   
	LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	WHERE 
	S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
	S_STUDENT_COURSE.FINAL_GRADE = S_GRADE.PK_GRADE AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_COURSE_OFFERING.PK_COURSE IN ($PK_COURSE) AND 
	S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE  $term_cond  
	GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING 
	ORDER BY S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC "); // DIAM-1209 Remove condition -> UNITS_IN_PROGRESS = 1 AND 
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_COURSE_OFFERING'] ?>"  >
			<? echo $res_type->fields['COURSE_CODE']." (".substr($res_type->fields['SESSION'],0,1)."-".$res_type->fields['SESSION_NO'].") ".$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']." - ".$res_type->fields['TERM_BEGIN_DATE']; ?>
		</option>
	<?	$res_type->MoveNext();
	} ?>
</select>