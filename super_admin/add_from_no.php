<? require_once("../global/config.php"); 

$from_count 		= $_REQUEST['from_count'];
$PK_TEXT_SETTINGS 	= $_REQUEST['PK_TEXT_SETTINGS'];

if($PK_TEXT_SETTINGS == ''){
	$SID 		= '';
	$TOKEN 		= '';
	$FROM_NO 	= '';
} else {
	$res_set = $db->Execute("SELECT * FROM S_TEXT_SETTINGS WHERE PK_TEXT_SETTINGS = '$PK_TEXT_SETTINGS' ");
	$SID 		= $res_set->fields['SID'];
	$TOKEN 		= $res_set->fields['TOKEN'];
	$FROM_NO 	= $res_set->fields['FROM_NO'];
}
?>
<div class="d-flex" id="PK_TEXT_SETTINGS_DIV_<?=$from_count?>" >
	<div class="col-12 col-sm-3 form-group">
		<input id="PK_TEXT_SETTINGS_<?=$from_count?>" name="PK_TEXT_SETTINGS[]" type="hidden" value="<?=$PK_TEXT_SETTINGS?>" />
		<input id="SID_<?=$from_count?>" name="SID[]" type="text" class="form-control" value="<?=$SID?>">
		<span class="bar"></span> 
		<label for="SID">SID</label>
	</div>
	<div class="col-12 col-sm-3 form-group">
		<input id="TOKEN_<?=$from_count?>" name="TOKEN[]" type="text" class="form-control" value="<?=$TOKEN?>">
		<span class="bar"></span> 
		<label for="TOKEN">Token</label>
	</div>
	<div class="col-12 col-sm-3 form-group">
		<input id="FROM_NO_<?=$from_count?>" name="FROM_NO[]" type="text" class="form-control" value="<?=$FROM_NO?>">
		<span class="bar"></span> 
		<label for="FROM_NO">From #</label>
	</div>
	<div class="col-12 col-sm-1 form-group">
		<a href="javascript:void(0);" onclick="delete_row('<?=$from_count?>','from_no')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</div>
</div>