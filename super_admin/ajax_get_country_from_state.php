<? require_once("../global/config.php"); 

$PK_STATES  = $_REQUEST['state'];
$id 		= $_REQUEST['id'];
?>
<select id="<?=$id?>" name="<?=$id?>" class="form-control" >
	<? $res_type = $db->Execute("select Z_COUNTRY.PK_COUNTRY, Z_COUNTRY.NAME from Z_COUNTRY,Z_STATES WHERE Z_COUNTRY.ACTIVE = '1' AND Z_STATES.PK_COUNTRY =  Z_COUNTRY.PK_COUNTRY AND PK_STATES = '$PK_STATES' ORDER BY Z_COUNTRY.NAME ASC ");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_COUNTRY'] ?>" selected ><?=$res_type->fields['NAME']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>