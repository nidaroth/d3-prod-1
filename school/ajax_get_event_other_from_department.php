<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond = "";
if($_REQUEST['task'] == 1)
	$cond .= " AND TYPE = 1 ";
else
	$cond .= " AND TYPE = 2 ";
	
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
	$cond .= " AND M_EVENT_OTHER.ACTIVE = 1 ";
}

?>
<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER" class="form-control">
	<option></option>
	<? $res_type = $db->Execute("select PK_EVENT_OTHER,EVENT_OTHER,DESCRIPTION from M_EVENT_OTHER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by TRIM(EVENT_OTHER) ASC");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_EVENT_OTHER']?>" <? if($PK_EVENT_OTHER == $res_type->fields['PK_EVENT_OTHER']) echo "selected"; ?> ><?=$res_type->fields['EVENT_OTHER'].' - '.$res_type->fields['DESCRIPTION']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>