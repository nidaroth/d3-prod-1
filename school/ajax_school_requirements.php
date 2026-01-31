<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$count  		     	= $_REQUEST['count'];
$PK_SCHOOL_REQUIREMENT  = $_REQUEST['PK_SCHOOL_REQUIREMENT'];
if($PK_SCHOOL_REQUIREMENT == '') {
	$PK_REQUIREMENT_CATEGORY	= '';
	$REQUIREMENT  				= '';
	$MANDATORY    				= '';
	$ACTIVE   	  				= 1;
} else {
	$result = $db->Execute("SELECT * FROM S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SCHOOL_REQUIREMENT = '$PK_SCHOOL_REQUIREMENT' ");
	$PK_REQUIREMENT_CATEGORY	= $result->fields['PK_REQUIREMENT_CATEGORY'];
	$REQUIREMENT  				= $result->fields['REQUIREMENT'];
	$MANDATORY    				= $result->fields['MANDATORY'];
	$ACTIVE   	  				= $result->fields['ACTIVE'];
}
?>
<div class="row" id="table_<?=$count?>" >
	<input type="hidden" name="PK_SCHOOL_REQUIREMENT[]"  value="<?=$PK_SCHOOL_REQUIREMENT?>" />
	<input type="hidden" name="COUNT[]"  value="<?=$count?>" />
	
	<div class="col-md-6">
		<input type="text" name="REQUIREMENT[]" placeholder="" id="REQUIREMENT_<?=$count?>"  class="required-entry form-control" value="<?=$REQUIREMENT?>" />
	</div>
	
	<div class="col-md-2">
		<select id="PK_REQUIREMENT_CATEGORY_<?=$count?>" name="PK_REQUIREMENT_CATEGORY[]"  class="required-entry form-control" >
			<option selected></option>
			<? $res_type = $db->Execute("select PK_REQUIREMENT_CATEGORY,REQUIREMENT_CATEGORY from Z_REQUIREMENT_CATEGORY WHERE ACTIVE = 1 ");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_REQUIREMENT_CATEGORY']?>" <? if($PK_REQUIREMENT_CATEGORY == $res_type->fields['PK_REQUIREMENT_CATEGORY']) echo "selected"; ?> ><?=$res_type->fields['REQUIREMENT_CATEGORY']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
	
	<div class="col-md-1">
		<div class="d-flex">
			<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="MANDATORY_<?=$count?>" name="MANDATORY_<?=$count?>" value="1" <? if($MANDATORY == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="MANDATORY_<?=$count?>"><?=YES?></label>
			</div>
		</div>
	</div>
	
	<div class="col-md-1">
		<div class="d-flex">
			<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="ACTIVE_<?=$count?>" name="ACTIVE_<?=$count?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="ACTIVE_<?=$count?>"><?=YES?></label>
			</div>
		</div>
	</div>
	
	<div class="col-md-1">
		<a href="javascript:void(0)" onclick="delete_row('<?=$count?>','detail')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
	</div>
</div>