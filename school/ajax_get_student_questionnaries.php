<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_probation.php");
require_once("get_department_from_t.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $PLACEMENT_ACCESS == 0){
	header("location:../index");
	exit;
}

$sid 		= $_REQUEST['sid'];
$eid 		= $_REQUEST['eid'];
$t 			= $_REQUEST['t'];
$all_dept 	= $_REQUEST['all_dept'];

if($all_dept != 1) {
	$QUES_PK_DEP[] = get_department_from_t($t);
} else {
	$res_type = $db->Execute("select DISTINCT(PK_DEPARTMENT) AS PK_DEPARTMENT from M_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ");
	while (!$res_type->EOF) {
		$QUES_PK_DEP[] = $res_type->fields['PK_DEPARTMENT'];
		
		$res_type->MoveNext();
	}
}

$i = 1;
foreach($QUES_PK_DEP as $PK_DEPARTMENT11){ 
	if($all_dept == 1) {
		$res_type = $db->Execute("select DEPARTMENT from M_DEPARTMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT = '$PK_DEPARTMENT11'"); ?>
		<div class="d-flex">
			<div class="col-12 col-sm-12 form-group">
				<h4 class="card-title"><?=$res_type->fields['DEPARTMENT']?></h4>
			</div>
		</div>
	<? }
	
	$res_type = $db->Execute("select PK_QUESTIONNAIRE,QUESTION from M_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_DEPARTMENT = '$PK_DEPARTMENT11' ORDER BY DISPLAY_ORDER ASC ");
	while (!$res_type->EOF) { ?>
		<br />
		<div class="d-flex">
			<div class="col-12 form-group">
				<? $res = $db->Execute("SELECT ANSWER FROM S_STUDENT_QUESTIONNAIRE WHERE PK_STUDENT_MASTER = '$sid' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_QUESTIONNAIRE = '".$res_type->fields['PK_QUESTIONNAIRE']."' AND PK_STUDENT_ENROLLMENT = '$eid' ");  ?>
				<textarea class="form-control" rows="2" id="ANSWER_<?=$res_type->fields['PK_QUESTIONNAIRE']?>" name="ANSWER[]"><?=$res->fields['ANSWER']?></textarea>
				<input type="hidden" name="PK_QUESTIONNAIRE[]" value="<?=$res_type->fields['PK_QUESTIONNAIRE']?>" />
				<span class="bar"></span>
				<label for="QUESTION_<?=$res_type->fields['PK_QUESTIONNAIRE']?>"><?=$res_type->fields['QUESTION']?></label>
			</div>
		</div>
	<?	$i++;
		$res_type->MoveNext();
	} 
} ?>