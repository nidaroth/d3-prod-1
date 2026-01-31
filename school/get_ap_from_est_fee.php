<? require_once("../global/config.php"); 

$PK_CAMPUS_PROGRAM = $_REQUEST['PK_CAMPUS_PROGRAM'];
	
$cond_2 = "";
if($PK_CAMPUS_PROGRAM != '' && $PK_CAMPUS_PROGRAM != '-1')
{
    $cond_2 = " AND PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) ";
}
	
$res_type = $db->Execute("select ACADEMIC_PERIOD FROM S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond_2 GROUP BY ACADEMIC_PERIOD "); ?>
<select id="AP_FEE" name="AP_FEE" class="form-control" >
	<option value="-1" ><? if($_REQUEST['show_all'] == 1) echo "All AP" ?></option>
	<? while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['ACADEMIC_PERIOD']?>" <? if($res_type->fields['ACADEMIC_PERIOD'] == $_REQUEST['val']) echo "selected"; ?> ><?=$res_type->fields['ACADEMIC_PERIOD'] ?></option>
		<? $res_type->MoveNext();
	} ?>
</select>