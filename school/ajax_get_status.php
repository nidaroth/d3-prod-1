<? require_once("../global/config.php"); 
require_once("../language/student.php");

if($_REQUEST['do_not_show_admission'] == 1) {
	$cond = " AND (ADMISSIONS = 0) ";
} else if($_REQUEST['SHOW_LEAD'] == 1) {
	$cond = " AND (ADMISSIONS = 1) ";
} else {
	if($_REQUEST['t'] == 1)
		$cond = " AND (ADMISSIONS = 1) ";
	else if($_REQUEST['t'] == 2 || $_REQUEST['t'] == 3 || $_REQUEST['t'] == 4 || $_REQUEST['t'] == 5 || $_REQUEST['t'] == 6)
		$cond = " AND (ADMISSIONS = 0) ";
}
?>
<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="doSearch()">
	<option value="" ><?=STATUS?></option>
	<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $cond order by STUDENT_STATUS ASC");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>