<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}
$PK_CAMPUS = $_REQUEST['PK_CAMPUS'];
	
$res_type = $db->Execute("select S_COURSE.PK_COURSE, CONCAT(TRANSCRIPT_CODE, ' - ', S_COURSE.COURSE_CODE, ' - ', COURSE_DESCRIPTION) as TRANSCRIPT_CODE, S_COURSE.ACTIVE FROM S_COURSE, S_COURSE_CAMPUS WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE.PK_COURSE = S_COURSE_CAMPUS.PK_COURSE AND PK_CAMPUS = '$PK_CAMPUS' ORDER BY S_COURSE.ACTIVE DESC, TRANSCRIPT_CODE ASC "); /* Ticket # 1355  */  // Ticket #1773 ?>
<select id="PK_COURSE" name="PK_COURSE" class="form-control required-entry" onchange="get_course_offering();" <? if($_REQUEST['onclick_fun'] == 1) { ?> onclick="check_campus()" <? } ?> > <!-- Ticket # 1458 --> <!-- Ticket #1149 - term -->
	<option></option>
	<? while (!$res_type->EOF) { 
		$option_label = $res_type->fields['TRANSCRIPT_CODE'];
		if($res_type->fields['ACTIVE'] == 0)
			$option_label .= " (Inactive)";?>
		<option value="<?=$res_type->fields['PK_COURSE']?>" <? if($res_type->fields['PK_COURSE'] == $_REQUEST['c']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
		<? $res_type->MoveNext();
	} ?>
</select>