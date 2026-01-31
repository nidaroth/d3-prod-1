<? require_once("../global/config.php"); 

if($_REQUEST['t'] == 1) 
	$cond = " AND PK_DOCUMENT_TEMPLATE_SUB_CATEGORY NOT IN (5) ";
else 
	$cond = " AND PK_DOCUMENT_TEMPLATE_SUB_CATEGORY NOT IN (6,7) "; ?>
<select id="PK_DOCUMENT_TEMPLATE_SUB_CATEGORY" name="PK_DOCUMENT_TEMPLATE_SUB_CATEGORY[]" multiple class="form-control" >
	<? $res_type = $db->Execute("select PK_DOCUMENT_TEMPLATE_SUB_CATEGORY,DOCUMENT_TEMPLATE_SUB_CATEGORY from Z_DOCUMENT_TEMPLATE_SUB_CATEGORY WHERE ACTIVE = 1 AND PK_DOCUMENT_TEMPLATE_CATEGORY IN ($_REQUEST[PK_DOCUMENT_TEMPLATE_CATEGORY]) $cond ORDER BY DOCUMENT_TEMPLATE_SUB_CATEGORY ASC ");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_DOCUMENT_TEMPLATE_SUB_CATEGORY']?>" ><?=$res_type->fields['DOCUMENT_TEMPLATE_SUB_CATEGORY'] ?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>