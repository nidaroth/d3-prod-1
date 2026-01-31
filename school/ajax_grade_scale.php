<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$count  		 		= $_REQUEST['count'];
$PK_GRADE_SCALE_DETAIL  = $_REQUEST['PK_GRADE_SCALE_DETAIL'];
if($PK_GRADE_SCALE_DETAIL == '') {
	$MIN_PERCENTAGE  = '';
	$MAX_PERCENTAGE  = '';
	$PK_GRADE   	 = '';
} else {
	$result = $db->Execute("SELECT * FROM S_GRADE_SCALE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_DETAIL = '$PK_GRADE_SCALE_DETAIL' ");
	$MIN_PERCENTAGE  = $result->fields['MIN_PERCENTAGE'];
	$MAX_PERCENTAGE  = $result->fields['MAX_PERCENTAGE'];
	$PK_GRADE    	 = $result->fields['PK_GRADE'];
	
}
?>
<tr id="table_<?=$count?>" >
	<td >
		<input type="hidden" name="PK_GRADE_SCALE_DETAIL[]"  value="<?=$PK_GRADE_SCALE_DETAIL?>" />
		<input type="hidden" name="COUNT[]"  value="<?=$count?>" />
		<input type="text" class="form-control" placeholder="" name="MIN_PERCENTAGE[]" id="CODE_<?=$count?>" value="<?=$MIN_PERCENTAGE?>" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="MAX_PERCENTAGE[]" id="MAX_PERCENTAGE_<?=$count?>" value="<?=$MAX_PERCENTAGE?>" />
	</td>
	<td>
		<select id="PK_GRADE_<?=$count?>" name="PK_GRADE[]" class="form-control">
			<option selected></option>
			<? $res_type = $db->Execute("select PK_GRADE,GRADE from S_GRADE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by GRADE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_GRADE'] ?>" <? if($PK_GRADE == $res_type->fields['PK_GRADE']) echo "selected"; ?> ><?=$res_type->fields['GRADE']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<a href="javascript:void(0);" onclick="delete_row('<?=$count?>','grade')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>
