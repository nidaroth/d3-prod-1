<?php require_once("../global/config.php");
$PK_DATA_TYPES 				= $_REQUEST['val'];
$PK_USER_DEFINED_FIELDS 	= $_REQUEST['PK_USER_DEFINED_FIELDS'];

$style = '';
if($PK_DATA_TYPES == 2 || $PK_DATA_TYPES == 3)
	$style = 'display:block';
else
	$style = 'display:none';
?>
<select name="PK_USER_DEFINED_FIELDS" id="PK_USER_DEFINED_FIELDS" class="form-control required-entry" style="<?=$style?>" >
	<option value=""></option>
	<? $res_type = $db->Execute("select * from S_USER_DEFINED_FIELDS WHERE ACTIVE = '1' AND PK_DATA_TYPES = '$PK_DATA_TYPES' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_USER_DEFINED_FIELDS']?>" <? if($res_type->fields['PK_USER_DEFINED_FIELDS'] == $PK_USER_DEFINED_FIELDS) echo 'selected="selected"'; ?> ><?=$res_type->fields['NAME']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>