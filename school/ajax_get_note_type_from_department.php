<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond = " AND TYPE = 1 ";
if($_REQUEST['event'] == 1)
	$cond = " AND TYPE = 2 ";
	
if($_REQUEST['t'] != '') {	
	$t_1 	= explode(',',$_REQUEST['t']);
	$dep_t 	= "-1";
	foreach($t_1 as $t){
		if($dep_t != '')
			$dep_t .= ",";
			
		$dep_t .= get_department_from_t($t);
	}
	$cond .= " AND PK_DEPARTMENT IN ($dep_t) ";
}

if($_REQUEST['show_inactive'] != 1) {	
	$cond .= " AND M_NOTE_TYPE.ACTIVE = 1 ";
}

$res_type = $db->Execute("select PK_NOTE_TYPE,NOTE_TYPE,DESCRIPTION from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by TRIM(NOTE_TYPE) ASC"); ?>
<select id="PK_NOTE_TYPE" name="PK_NOTE_TYPE" class="form-control">
	<option></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_NOTE_TYPE']?>" ><?=$res_type->fields['NOTE_TYPE'].' - '.$res_type->fields['DESCRIPTION']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>