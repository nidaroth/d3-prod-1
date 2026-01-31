<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_TERM_MASTER	= $_REQUEST['PK_TERM_MASTER'];
$term_cond 		= " AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($PK_TERM_MASTER) ";
$def_val		= explode(",",$_REQUEST['def_val']);
?>
<select id="CAL_PK_COURSE_OFFERING" name="CAL_PK_COURSE_OFFERING[]" multiple class="form-control" >
	 <? $res_type = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, COURSE_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'), '') AS TERM_BEGIN_DATE, SESSION_NO, SESSION from S_COURSE, S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER WHERE S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.ACTIVE = 1 $term_cond  AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING");
	while (!$res_type->EOF) { 
		$selected = "";
		if(!empty($def_val)){
			foreach($def_val as $def_val1){
				if($def_val1 == $res_type->fields['PK_COURSE_OFFERING'])
					$selected = "selected";
			}
		} ?>
		<option value="<?=$res_type->fields['PK_COURSE_OFFERING'] ?>" <?=$selected ?> >
			<?=$res_type->fields['COURSE_CODE']." (".$res_type->fields['SESSION']." - ".$res_type->fields['SESSION_NO'].")"; ?>
		</option>
	<?	$res_type->MoveNext();
	} ?>
</select>