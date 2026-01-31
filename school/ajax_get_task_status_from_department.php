<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond = "";
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
	$cond .= " AND M_TASK_STATUS.ACTIVE = 1 ";
}

$res_type = $db->Execute("select PK_TASK_STATUS,TASK_STATUS,DESCRIPTION from M_TASK_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by trim(TASK_STATUS) ASC"); ?>
<select id="PK_TASK_STATUS" name="PK_TASK_STATUS" class="form-control">
	<option></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_TASK_STATUS']?>" ><?=$res_type->fields['TASK_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>