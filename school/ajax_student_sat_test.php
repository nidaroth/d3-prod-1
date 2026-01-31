<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_STUDENT_SAT_TEST  	= $_REQUEST['PK_STUDENT_SAT_TEST'];
$sat_test_count 		= $_REQUEST['sat_test_count']; 
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid']; 
$PK_STUDENT_MASTER 		= $_REQUEST['sid'];

if($PK_STUDENT_SAT_TEST == '') {
	
	$PK_SAT_MEASURE 	= '';
	$SAT_SCORE 			= '';
	$SAT_NATIONAL_RANK 	= '';
	$SAT_USER_RANK 		= '';
	$SAT_TEST_DATE 		= '';

} else {
	$res_dd = $db->Execute("select * FROM S_STUDENT_SAT_TEST WHERE PK_STUDENT_SAT_TEST = '$PK_STUDENT_SAT_TEST' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$PK_SAT_MEASURE 	= $res_dd->fields['PK_SAT_MEASURE'];
	$SAT_SCORE   		= $res_dd->fields['SCORE'];
	$SAT_NATIONAL_RANK  = $res_dd->fields['NATIONAL_RANK'];
	$SAT_USER_RANK 		= $res_dd->fields['USER_RANK'];
	$SAT_TEST_DATE 		= $res_dd->fields['TEST_DATE'];
	
	if($SAT_TEST_DATE != '0000-00-00')
		$SAT_TEST_DATE = date("m/d/Y",strtotime($SAT_TEST_DATE));
	else
		$SAT_TEST_DATE = '';
}
?>
<div class="d-flex" id="sat_test_div_<?=$sat_test_count?>" >
	<input type="hidden" name="PK_STUDENT_SAT_TEST[]" value="<?=$PK_STUDENT_SAT_TEST?>" />
	<input type="hidden" name="sat_test_count[]" value="<?=$sat_test_count?>" />
	<div class="col-3 col-sm-3">
		<select id="PK_SAT_MEASURE_<?=$sat_test_count?>" name="PK_SAT_MEASURE[]" class="form-control" <?=$disabled?> >
			<option ></option>
			<? $res_type = $db->Execute("select PK_SAT_MEASURE,SAT_MEASURE,DESCRIPTION from M_SAT_MEASURE WHERE ACTIVE = 1 order by SAT_MEASURE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_SAT_MEASURE'] ?>" <? if($PK_SAT_MEASURE == $res_type->fields['PK_SAT_MEASURE']) echo "selected"; ?> ><?=$res_type->fields['SAT_MEASURE'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
	
	<div class="col-1 col-sm-1" >
		<input type="text" class="form-control" placeholder="" name="SAT_SCORE[]" id="SAT_SCORE_<?=$sat_test_count?>" value="<?=$SAT_SCORE?>" <?=$disabled?> />
	</div>
	
	<div class="col-2 col-sm-2" >
		<input type="text" class="form-control" placeholder="" name="SAT_NATIONAL_RANK[]" id="SAT_NATIONAL_RANK<?=$sat_test_count?>" value="<?=$SAT_NATIONAL_RANK?>" <?=$disabled?> />
	</div>
	
	<div class="col-2 col-sm-2" >
		<input type="text" class="form-control" placeholder="" name="SAT_USER_RANK[]" id="SAT_USER_RANK_<?=$sat_test_count?>" value="<?=$SAT_USER_RANK?>"<?=$disabled?>  />
	</div>
	
	<div class="col-1 col-sm-1" >
		<input type="text" class="form-control date" placeholder="" name="SAT_TEST_DATE[]" id="SAT_TEST_DATE_<?=$sat_test_count?>" value="<?=$SAT_TEST_DATE?>" <?=$disabled?> />
	</div>
	
	<div class="col-1 col-sm-1" >
		<? if(($disabled == '' && ($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3)) || $PK_STUDENT_ATB_TEST == ''){ ?>
		<a href="javascript:void(0)" onclick="delete_row(<?=$sat_test_count?>,'student_sat_test')" ><i class="far fa-trash-alt"></i></a>
		<? } ?>
	</div>
</div>