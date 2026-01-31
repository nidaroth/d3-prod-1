<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_STUDENT_ACT_TEST  	= $_REQUEST['PK_STUDENT_ACT_TEST'];
$act_test_count 		= $_REQUEST['act_test_count']; 
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid']; 
$PK_STUDENT_MASTER 		= $_REQUEST['sid'];

if($PK_STUDENT_ACT_TEST == '') {
	
	$PK_ACT_MEASURE = '';
	$SCORE 			= '';
	$STATE_RANK 	= '';
	$NATIONAL_RANK 	= '';
	$ACT_TEST_DATE 	= '';

} else {
	$res_dd = $db->Execute("select * FROM S_STUDENT_ACT_TEST WHERE PK_STUDENT_ACT_TEST = '$PK_STUDENT_ACT_TEST' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$PK_ACT_MEASURE = $res_dd->fields['PK_ACT_MEASURE'];
	$SCORE   		= $res_dd->fields['SCORE'];
	$STATE_RANK  	= $res_dd->fields['STATE_RANK'];
	$NATIONAL_RANK 	= $res_dd->fields['NATIONAL_RANK'];
	$ACT_TEST_DATE 		= $res_dd->fields['TEST_DATE'];
	
	if($ACT_TEST_DATE != '0000-00-00')
		$ACT_TEST_DATE = date("m/d/Y",strtotime($ACT_TEST_DATE));
	else
		$ACT_TEST_DATE = '';
}
?>
<div class="d-flex" id="act_test_div_<?=$act_test_count?>" >
	<input type="hidden" name="PK_STUDENT_ACT_TEST[]" value="<?=$PK_STUDENT_ACT_TEST?>" />
	<input type="hidden" name="act_test_count[]" value="<?=$act_test_count?>" />
	<div class="col-3 col-sm-3">
		<select id="PK_ACT_MEASURE_<?=$act_test_count?>" name="PK_ACT_MEASURE[]" class="form-control" <?=$disabled?> >
			<option ></option>
			<? $res_type = $db->Execute("select PK_ACT_MEASURE,ACT_MEASURE,DESCRIPTION from M_ACT_MEASURE WHERE ACTIVE = 1 order by ACT_MEASURE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_ACT_MEASURE'] ?>" <? if($PK_ACT_MEASURE == $res_type->fields['PK_ACT_MEASURE']) echo "selected"; ?> ><?=$res_type->fields['ACT_MEASURE'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
	
	<div class="col-1 col-sm-1" >
		<input type="text" class="form-control" placeholder="" name="SCORE[]" id="SCORE_<?=$act_test_count?>" value="<?=$SCORE?>" <?=$disabled?> />
	</div>
	
	<div class="col-2 col-sm-2" >
		<input type="text" class="form-control" placeholder="" name="STATE_RANK[]" id="STATE_RANK_<?=$act_test_count?>" value="<?=$STATE_RANK?>" <?=$disabled?> />
	</div>
	
	<div class="col-2 col-sm-2" >
		<input type="text" class="form-control" placeholder="" name="NATIONAL_RANK[]" id="NATIONAL_RANK_<?=$act_test_count?>" value="<?=$NATIONAL_RANK?>" <?=$disabled?> />
	</div>
	
	<div class="col-1 col-sm-1" >
		<input type="text" class="form-control date" placeholder="" name="ACT_TEST_DATE[]" id="ACT_TEST_DATE_<?=$act_test_count?>" value="<?=$ACT_TEST_DATE?>" <?=$disabled?> />
	</div>
	
	<div class="col-1 col-sm-1" >
		<? if(($disabled == '' && ($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3)) || $PK_STUDENT_ATB_TEST == ''){ ?>
		<a href="javascript:void(0)" onclick="delete_row(<?=$act_test_count?>,'student_act_test')" ><i class="far fa-trash-alt"></i></a>
		<? } ?>
	</div>
</div>