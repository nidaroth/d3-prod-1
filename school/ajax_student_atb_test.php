<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_STUDENT_ATB_TEST  	= $_REQUEST['PK_STUDENT_ATB_TEST'];
$atb_test_count 		= $_REQUEST['atb_test_count']; 
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid']; 
$PK_STUDENT_MASTER 		= $_REQUEST['sid'];

if($PK_STUDENT_ATB_TEST == '') {
	
	$PK_ATB_CODE 		= '';
	$PK_ATB_TEST_CODE 	= '';
	$PK_ATB_ADMIN_CODE 	= '';
	$COMPLETED_DATE 	= '';

} else {
	$res_dd = $db->Execute("select * FROM S_STUDENT_ATB_TEST WHERE PK_STUDENT_ATB_TEST = '$PK_STUDENT_ATB_TEST' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$PK_ATB_CODE 		= $res_dd->fields['PK_ATB_CODE'];
	$PK_ATB_TEST_CODE   = $res_dd->fields['PK_ATB_TEST_CODE'];
	$PK_ATB_ADMIN_CODE  = $res_dd->fields['PK_ATB_ADMIN_CODE'];
	$COMPLETED_DATE 	= $res_dd->fields['COMPLETED_DATE'];
	
	if($COMPLETED_DATE != '0000-00-00')
		$COMPLETED_DATE = date("m/d/Y",strtotime($COMPLETED_DATE));
	else
		$COMPLETED_DATE = '';
}
?>
<div class="d-flex" id="atb_test_div_<?=$atb_test_count?>" >
	<input type="hidden" name="PK_STUDENT_ATB_TEST[]" value="<?=$PK_STUDENT_ATB_TEST?>" />
	<input type="hidden" name="atb_test_count[]" value="<?=$atb_test_count?>" />
	<div class="col-3 col-sm-3">
		<select id="PK_ATB_CODE_<?=$program_course_id?>" name="PK_ATB_CODE[]" class="form-control" <?=$disabled?> >
			<option ></option>
			<? $res_type = $db->Execute("select PK_ATB_CODE,ATB_CODE,DESCRIPTION from M_ATB_CODE WHERE ACTIVE = 1 order by ATB_CODE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_ATB_CODE'] ?>" <? if($PK_ATB_CODE == $res_type->fields['PK_ATB_CODE']) echo "selected"; ?> ><?=$res_type->fields['ATB_CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
	
	<div class="col-3 col-sm-3">
		<select id="PK_ATB_TEST_CODE_<?=$program_course_id?>" name="PK_ATB_TEST_CODE[]" class="form-control" <?=$disabled?> >
			<option ></option>
			<? $res_type = $db->Execute("select PK_ATB_TEST_CODE,ATB_TEST_CODE from M_ATB_TEST_CODE WHERE ACTIVE = 1 order by ATB_TEST_CODE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_ATB_TEST_CODE'] ?>" <? if($PK_ATB_TEST_CODE == $res_type->fields['PK_ATB_TEST_CODE']) echo "selected"; ?> ><?=$res_type->fields['ATB_TEST_CODE']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
	
	<div class="col-3 col-sm-3">
		<select id="PK_ATB_ADMIN_CODE_<?=$program_course_id?>" name="PK_ATB_ADMIN_CODE[]" class="form-control" <?=$disabled?> >
			<option ></option>
			<? $res_type = $db->Execute("select PK_ATB_ADMIN_CODE,ATB_ADMIN_CODE,DESCRIPTION from M_ATB_ADMIN_CODE WHERE ACTIVE = 1 order by ATB_ADMIN_CODE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_ATB_ADMIN_CODE'] ?>" <? if($PK_ATB_ADMIN_CODE == $res_type->fields['PK_ATB_ADMIN_CODE']) echo "selected"; ?> ><?=$res_type->fields['ATB_ADMIN_CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>

	<div class="col-1 col-sm-1" >
		<input type="text" class="form-control date" placeholder="" name="COMPLETED_DATE[]" id="COMPLETED_DATE_<?=$atb_test_count?>" value="<?=$COMPLETED_DATE?>" <?=$disabled?> />
	</div>
	
	<div class="col-1 col-sm-1" >
		<? if(($disabled == '' && ($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3)) || $PK_STUDENT_ATB_TEST == ''){ ?>
		<a href="javascript:void(0)" onclick="delete_row(<?=$atb_test_count?>,'student_atb_test')" ><i class="far fa-trash-alt"></i></a>
		<? } ?>
	</div>
</div>