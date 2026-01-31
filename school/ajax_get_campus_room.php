<? require_once("../global/config.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
$PK_CAMPUS 			= $_REQUEST['campus'];
$SELECTED_VALUE1	= $_REQUEST['SELECTED_VALUE1'];
?>
<select id="<?=$_REQUEST['id']?>" name="<?=$_REQUEST['name']?>" class="form-control" <?=$_REQUEST['disable']?> <? if($_REQUEST['onchange'] == 1){ ?> onchange="get_room_max_size(this.value)" <? } ?> > <!-- Ticket # 1325 -->
	<option value=""></option>
	<? /* Ticket #1696  */
	$res_type = $db->Execute("select PK_CAMPUS_ROOM, ROOM_NO, ROOM_DESCRIPTION, ACTIVE from M_CAMPUS_ROOM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($PK_CAMPUS) order by ACTIVE DESC, ROOM_NO ASC"); 
	while (!$res_type->EOF) { 
		$option_label = $res_type->fields['ROOM_NO'].' - '.$res_type->fields['ROOM_DESCRIPTION'];
		if($res_type->fields['ACTIVE'] == 0)
			$option_label .= " (Inactive)"; ?>
		<option value="<?=$res_type->fields['PK_CAMPUS_ROOM'] ?>" <? if($SELECTED_VALUE1 == $res_type->fields['PK_CAMPUS_ROOM']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
	<?	$res_type->MoveNext();
	} /* Ticket #1696  */ ?>
</select>