<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/term_master.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$type 		= $_REQUEST['type'];
$PK_CAMPUS 	= $_REQUEST['campus'];
if($type == 1 || $type == 2 || $type == 5) { ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ></option>
		<option value="1" ><?=YES ?></option>
		<option value="0" ><?=NO ?></option>
	</select>
<? } else if($type == 3) { ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_CAMPUS,OFFICIAL_CAMPUS_NAME from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 4) { ?>
	<input id="UPDATE_VALUE" name="UPDATE_VALUE" value="" type="text" class="form-control" placeholder="<?=GROUP?>" >
<? } else { ?>
<? } ?>