<? require_once("../global/config.php"); 
/*if(!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
	$PK_CAMPUS_PROGRAM = implode(",",$_REQUEST['PK_CAMPUS_PROGRAM']);
else
	$PK_CAMPUS_PROGRAM = '';
*/
$PK_CAMPUS_PROGRAM = $_REQUEST['PK_CAMPUS_PROGRAM'];
	
$cond_2 = "";
if($PK_CAMPUS_PROGRAM != '' )
	$cond_2 = " AND PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) ";
	
$res_type = $db->Execute("select AY FROM M_CAMPUS_PROGRAM_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond_2 GROUP By AY "); ?>
<select id="AY" name="AY" class="form-control" >
	<option value="-1" ><? if($_REQUEST['show_all'] == 1) echo "All AY" ?></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['AY']?>" <? if($res_type->fields['AY'] == $_REQUEST['val']) echo "selected"; ?> ><?=$res_type->fields['AY'] ?></option>
		<? $res_type->MoveNext();
	} ?>
</select>