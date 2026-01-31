<? require_once("../global/config.php"); 
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_CAMPUS_PROGRAM_REQUIREMENT  = $_REQUEST['PK_CAMPUS_PROGRAM_REQUIREMENT'];
$requirement_id 				= $_REQUEST['requirement_id']; 

if($PK_CAMPUS_PROGRAM_REQUIREMENT == '') {
	$PK_REQUIREMENT_CATEGORY	= '';
	$REQUIREMENT 				= '';
	$MANDATORY 	 				= '';
	$ACTIVE 	 				= 1;
} else {
	$res_dd = $db->Execute("select * FROM M_CAMPUS_PROGRAM_REQUIREMENT WHERE PK_CAMPUS_PROGRAM_REQUIREMENT = '$PK_CAMPUS_PROGRAM_REQUIREMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$PK_REQUIREMENT_CATEGORY = $res_dd->fields['PK_REQUIREMENT_CATEGORY'];
	$REQUIREMENT 			 = $res_dd->fields['REQUIREMENT'];
	$MANDATORY   			 = $res_dd->fields['MANDATORY'];
	$ACTIVE 	 			 = $res_dd->fields['ACTIVE'];
}
?>
<div class="row" id="requirement_div_<?=$requirement_id?>" >
	<input type="hidden" name="PK_CAMPUS_PROGRAM_REQUIREMENT[]" value="<?=$PK_CAMPUS_PROGRAM_REQUIREMENT?>" />
	<input type="hidden" name="requirement_id[]" value="<?=$requirement_id?>" />
	<label for="input-text" class="col-sm-4 control-label">&nbsp;</label>
	<div class="col-sm-6">
		<input type="text" class="form-control" placeholder="" name="REQUIREMENT[]" id="REQUIREMENT_<?=$requirement_id?>" value="<?=$REQUIREMENT?>" />
	</div>
	
	<div class="col-md-2">
		<select id="PK_REQUIREMENT_CATEGORY_<?=$count?>" name="PK_REQUIREMENT_CATEGORY[]"  class="required-entry form-control" error_label="<?=REQUIREMENT?> - <?=TAB_REQUIREMENT?>" ><!-- Ticket # 1160 -->
			<option selected></option>
			<? $res_type = $db->Execute("select PK_REQUIREMENT_CATEGORY,REQUIREMENT_CATEGORY from Z_REQUIREMENT_CATEGORY WHERE ACTIVE = 1 ");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_REQUIREMENT_CATEGORY']?>" <? if($PK_REQUIREMENT_CATEGORY == $res_type->fields['PK_REQUIREMENT_CATEGORY']) echo "selected"; ?> ><?=$res_type->fields['REQUIREMENT_CATEGORY']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
	
	<div class="col-sm-1">
		<div class="d-flex">
			<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="MANDATORY_<?=$requirement_id?>" name="MANDATORY_<?=$requirement_id?>" value="1" <? if($MANDATORY == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="MANDATORY_<?=$requirement_id?>"><?=YES?></label>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<div class="d-flex">
			<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="ACTIVE_<?=$requirement_id?>" name="ACTIVE_<?=$requirement_id?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="ACTIVE_<?=$requirement_id?>"><?=YES?></label>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<a href="javascript:void(0)" onclick="delete_row(<?=$requirement_id?>,'requirement')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
	</div>
</div>