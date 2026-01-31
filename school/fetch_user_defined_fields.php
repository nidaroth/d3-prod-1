<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$count  		     			= $_REQUEST['count'];
$PK_USER_DEFINED_FIELDS_DETAIL  = $_REQUEST['PK_USER_DEFINED_FIELDS_DETAIL'];
$ACTION              			= $_REQUEST['ACTION'];
$result = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL = '$PK_USER_DEFINED_FIELDS_DETAIL' ");
$PK_USER_DEFINED_FIELDS_DETAIL  = $result->fields['PK_USER_DEFINED_FIELDS_DETAIL'];
$OPTION_NAME      				= $result->fields['OPTION_NAME'];
$DISPLAY_ORDER 					= $result->fields['DISPLAY_ORDER'];
$ACTIVE   						= $result->fields['ACTIVE'];

?>
<div class="row" id="table_<?=$count?>" >
	<input type="hidden" name="PK_USER_DEFINED_FIELDS_DETAIL[]"  value="<?=$PK_USER_DEFINED_FIELDS_DETAIL?>" />
	<input type="hidden" name="COUNT[]"  value="<?=$count?>" />
	
	<div class="col-md-6">
		<input type="text" name="OPTION_NAME[]" placeholder="" id="OPTION_NAME_<?=$count?>"  class="required-entry form-control" value="<?=$OPTION_NAME?>" />
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