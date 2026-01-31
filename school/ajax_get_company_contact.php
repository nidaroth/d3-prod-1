<? require_once("../global/config.php"); 
require_once("../language/common.php");

$PK_COMPANY = $_REQUEST['id'];
?>
<select id="PK_COMPANY_JOB" tabindex="3" name="PK_COMPANY_JOB" class="form-control" style="width:100%;" onchange="get_job_info(this.value)">
	<option selected></option>
	<? $res_type = $db->Execute("SELECT PK_COMPANY_JOB,JOB_TITLE,PK_PLACEMENT_TYPE,JOB_NUMBER, ACTIVE FROM S_COMPANY_JOB WHERE JOB_CANCELED = '0000-00-00' AND JOB_FILLED = '0000-00-00' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '".$PK_COMPANY."'  ORDER BY ACTIVE DESC, JOB_TITLE ASC");															
	while (!$res_type->EOF) { 
		$option_label = $res_type->fields['JOB_TITLE'];
		if($res_type->fields['ACTIVE'] == 0)
			$option_label .= " (Inactive)"; ?>
		<option value="<?=$res_type->fields['PK_COMPANY_JOB'] ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>
~!~
<select id="PK_COMPANY_CONTACT" tabindex="11" name="PK_COMPANY_CONTACT" class="form-control" >
	<option selected></option>
		<? $res_type = $db->Execute("select PK_COMPANY_CONTACT, NAME, ACTIVE from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$PK_COMPANY' ORDER BY ACTIVE DESC, NAME ASC ");
	while (!$res_type->EOF) { 
		$option_label = $res_type->fields['NAME'];
		if($res_type->fields['ACTIVE'] == 0)
			$option_label .= " (Inactive)"; ?>
		<option value="<?=$res_type->fields['PK_COMPANY_CONTACT'] ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>