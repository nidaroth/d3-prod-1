<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$count  	 = $_REQUEST['count'];
$PK_GRADE  = $_REQUEST['PK_GRADE'];
if($PK_GRADE == '') {
	$GRADE  = '';
	$NUMBER_GRADE   		= '';
	$CALCULATE_GPA   		= '';
	$UNITS_ATTEMPTED    	= '';
	$UNITS_COMPLETED    	= '';
	$UNITS_IN_PROGRESS    	= '';
	$WEIGHTED_GRADE_CALC    = '';
	$RETAKE_UPDATE    		= '';
	$DISPLAY_ORDER			= '';
	$IS_DEFAULT				= '';
	$ACTIVE   	  			= 1;
	$RETAKE_GRADE			= '';
} else {
	$result = $db->Execute("SELECT * FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE = '$PK_GRADE' ");
	$GRADE  				= $result->fields['GRADE'];
	$NUMBER_GRADE    		= $result->fields['NUMBER_GRADE'];
	$CALCULATE_GPA    		= $result->fields['CALCULATE_GPA'];
	$UNITS_ATTEMPTED    	= $result->fields['UNITS_ATTEMPTED'];
	$UNITS_COMPLETED    	= $result->fields['UNITS_COMPLETED'];
	$UNITS_IN_PROGRESS    	= $result->fields['UNITS_IN_PROGRESS'];
	$WEIGHTED_GRADE_CALC    = $result->fields['WEIGHTED_GRADE_CALC'];
	$RETAKE_UPDATE    		= $result->fields['RETAKE_UPDATE'];
	$DISPLAY_ORDER    		= $result->fields['DISPLAY_ORDER'];
	$IS_DEFAULT    			= $result->fields['IS_DEFAULT'];
	$ACTIVE   	 			= $result->fields['ACTIVE'];
	$RETAKE_GRADE			= $result->fields['RETAKE_GRADE'];
}
?>
<tr id="table_<?=$count?>" >
	<td >
		<input type="hidden" name="PK_GRADE[]"  value="<?=$PK_GRADE?>" />
		<input type="hidden" name="COUNT[]"  value="<?=$count?>" />
		<input type="text" class="form-control" placeholder="" name="GRADE[]" id="GRADE_<?=$count?>" value="<?=$GRADE?>" style="width:150px" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="NUMBER_GRADE[]" id="NUMBER_GRADE_<?=$count?>" value="<?=$NUMBER_GRADE?>" style="width:100px" />
	</td>
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="CALCULATE_GPA_<?=$count?>" name="CALCULATE_GPA_<?=$count?>" value="1" <? if($CALCULATE_GPA == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="CALCULATE_GPA_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="UNITS_ATTEMPTED_<?=$count?>" name="UNITS_ATTEMPTED_<?=$count?>" value="1" <? if($UNITS_ATTEMPTED == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="UNITS_ATTEMPTED_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="UNITS_COMPLETED_<?=$count?>" name="UNITS_COMPLETED_<?=$count?>" value="1" <? if($UNITS_COMPLETED == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="UNITS_COMPLETED_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="UNITS_IN_PROGRESS_<?=$count?>" name="UNITS_IN_PROGRESS_<?=$count?>" value="1" <? if($UNITS_IN_PROGRESS == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="UNITS_IN_PROGRESS_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="WEIGHTED_GRADE_CALC_<?=$count?>" name="WEIGHTED_GRADE_CALC_<?=$count?>" value="1" <? if($WEIGHTED_GRADE_CALC == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="WEIGHTED_GRADE_CALC_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="RETAKE_UPDATE_<?=$count?>" name="RETAKE_UPDATE_<?=$count?>" value="1" <? if($RETAKE_UPDATE == 1) echo "checked"; ?> onclick="show_retake_grade(<?=$count?>)" >
				<label class="custom-control-label" for="RETAKE_UPDATE_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<select id="RETAKE_GRADE_<?=$count?>" name="RETAKE_GRADE_<?=$count?>" class="form-control <? if($RETAKE_UPDATE == 1) { ?> required-entry <? } ?> " style="width:150px;<? if($RETAKE_UPDATE == 0) echo "display:none"; ?>"  >
			<option value="" ></option>
			<? $result = $db->Execute("SELECT PK_GRADE, GRADE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY DISPLAY_ORDER ASC");
			while (!$result->EOF) { ?>
				<option value="<?=$result->fields['PK_GRADE']?>" <? if($RETAKE_GRADE == $result->fields['PK_GRADE']) echo "selected"; ?> ><?=$result->fields['GRADE']?></option>
			<? $result->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="DISPLAY_ORDER[]" id="DISPLAY_ORDER_<?=$count?>" value="<?=$DISPLAY_ORDER?>" style="width:100px" />
	</td>
	
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input IS_DEFAULT" id="IS_DEFAULT_<?=$count?>" name="IS_DEFAULT_<?=$count?>" value="1" <? if($IS_DEFAULT == 1) echo "checked"; ?> onclick="set_default(<?=$count?>)" >
				<label class="custom-control-label" for="IS_DEFAULT_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<div class="d-flex" style="text-align:center;">
			<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="ACTIVE_<?=$count?>" name="ACTIVE_<?=$count?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="ACTIVE_<?=$count?>">&nbsp;</label>
			</div>
		</div>
	</td>
	<td>
		<? if($PK_GRADE == ''){ ?>
		<a href="javascript:void(0);" onclick="delete_row('<?=$count?>','grade')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
		<? } ?>
	</td>
</tr>
