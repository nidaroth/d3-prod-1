<? require_once("../global/config.php"); 

$PK_CAMPUS_PROGRAM = $_REQUEST['PK_CAMPUS_PROGRAM'];
	
$cond_2 = "";
if($PK_CAMPUS_PROGRAM != '' )
{
    $cond_2 = " AND PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) ";
}
	
$res_type = $db->Execute("select ACADEMIC_YEAR FROM S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond_2 GROUP BY ACADEMIC_YEAR "); ?>
<select id="AY_FEE" name="AY_FEE" class="form-control" >
	<option value="-1" ><? if($_REQUEST['show_all'] == 1) echo "All AY" ?></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['ACADEMIC_YEAR']?>" <? if($res_type->fields['ACADEMIC_YEAR'] == $_REQUEST['val']) echo "selected"; ?> ><?=$res_type->fields['ACADEMIC_YEAR'] ?></option>
		<? $res_type->MoveNext();
	} ?>
</select>