<? require_once("../global/config.php"); 
$PK_HELP_CATEGORY = $_REQUEST['cat'];
?>
<select class="form-control required-entry" id="PK_HELP_SUB_CATEGORY" name="PK_HELP_SUB_CATEGORY" >
	<option value="" ></option>
	<? $res_dd = $db->Execute("select PK_HELP_SUB_CATEGORY,HELP_SUB_CATEGORY FROM M_HELP_SUB_CATEGORY WHERE ACTIVE = 1 AND PK_HELP_CATEGORY = '$PK_HELP_CATEGORY' ORDER BY HELP_SUB_CATEGORY ASC"); 
	while (!$res_dd->EOF) { ?>
		<option value="<?=$res_dd->fields['PK_HELP_SUB_CATEGORY']?>" <? if($res_dd->fields['PK_HELP_SUB_CATEGORY'] == $PK_HELP_SUB_CATEGORY ) echo "selected"; ?> ><?=$res_dd->fields['HELP_SUB_CATEGORY'] ?></option>
	<?	$res_dd->MoveNext();
	} ?>
</select>