<? require_once("../global/config.php"); 
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_STUDENT_TEST  		= $_REQUEST['PK_STUDENT_TEST'];
$student_test_count 	= $_REQUEST['student_test_count']; 
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid']; 
$PK_STUDENT_MASTER 		= $_REQUEST['sid'];

if($PK_STUDENT_TEST == '') {
	
	$TEST_RESULT 	= '';
	$PASSED 	 	= '';
	$TEST_DATE 	 	= '';
	
	/*$res_dd = $db->Execute("select PK_STUDENT_TEST FROM S_STUDENT_TEST WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
	if($res_dd->RecordCount == 0)
		$TEST_LABEL = 'Test 1';
	else
		$TEST_LABEL = 'Test '.($res_dd->RecordCount + 1);*/
		
	$TEST_LABEL = 'Test '.$student_test_count;
	
} else {
	$res_dd = $db->Execute("select * FROM S_STUDENT_TEST WHERE PK_STUDENT_TEST = '$PK_STUDENT_TEST' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$TEST_LABEL 	= $res_dd->fields['TEST_LABEL'];
	$TEST_RESULT   	= $res_dd->fields['TEST_RESULT'];
	$PASSED   		= $res_dd->fields['PASSED'];
	$TEST_DATE 	 	= $res_dd->fields['TEST_DATE'];
	
	if($TEST_DATE != '0000-00-00')
		$TEST_DATE = date("m/d/Y",strtotime($TEST_DATE));
	else
		$TEST_DATE = '';
}
?>
<div class="d-flex" id="stuTest_div_<?=$student_test_count?>" >
	<input type="hidden" name="PK_STUDENT_TEST[]" value="<?=$PK_STUDENT_TEST?>" />
	<input type="hidden" name="student_test_count[]" value="<?=$student_test_count?>" />
	<div class="col-2 col-sm-2 ">
		<input type="text" class="form-control" placeholder="" name="TEST_LABEL[]" id="TEST_LABEL_<?=$student_test_count?>" value="<?=$TEST_LABEL?>" <?=$disabled?> />
	</div>
	
	<div class="col-2 col-sm-2" >
		<input type="text" class="form-control" placeholder="" name="TEST_RESULT[]" id="TEST_RESULT_<?=$student_test_count?>" value="<?=$TEST_RESULT?>" <?=$disabled?> />
	</div>
	
	<div class="col-2 col-sm-2" >
		<div class="d-flex">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="PASSED_<?=$student_test_count?>" name="PASSED_<?=$student_test_count?>" value="1" <? if($PASSED == 1) echo "checked"; ?> <?=$disabled?> >
				<label class="custom-control-label" for="PASSED_<?=$student_test_count?>"><?=YES?></label>
			</div>
		</div>
	</div>
	
	<div class="col-1 col-sm-1" >
		<input type="text" class="form-control date" placeholder="" name="TEST_DATE[]" id="TEST_DATE_<?=$student_test_count?>" value="<?=$TEST_DATE?>" <?=$disabled?> />
	</div>
	
	<div class="col-1 col-sm-1" >
		<? if(($disabled == '' && ($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3)) || $PK_STUDENT_TEST == ''){ ?>
		<a href="javascript:void(0)" onclick="delete_row(<?=$student_test_count?>,'student_test')" ><i class="far fa-trash-alt"></i></a>
		<? } ?>
	</div>
</div>