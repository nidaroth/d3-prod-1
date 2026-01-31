<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}


	
$PK_DEPARTMENT = get_department_from_t($_REQUEST['t']);	
$cond .= " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";

if($_REQUEST['show_inactive'] != 1) {	
	$cond .= " AND M_EVENT_OTHER.ACTIVE = 1 ";
}
?>
<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER" class="form-control">
	<option></option>
	<? $res_type = $db->Execute("select PK_EVENT_OTHER,EVENT_OTHER,DESCRIPTION from M_EVENT_OTHER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $cond order by trim(EVENT_OTHER) ASC");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_EVENT_OTHER']?>" <? if($PK_EVENT_OTHER == $res_type->fields['PK_EVENT_OTHER']) echo "selected"; ?> ><?=$res_type->fields['EVENT_OTHER'].' - '.$res_type->fields['DESCRIPTION']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>