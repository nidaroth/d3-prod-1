<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond = "";

//if($_REQUEST['pk_campus_id'] != '') {
	$cond = " AND S_TERM_MASTER_CAMPUS.PK_CAMPUS IN ($_REQUEST[pk_campus_id]) ";
//}

$UNION = "";
if($_REQUEST['DEF_TERM'] > 0) {
	$UNION = " UNION select PK_TERM_MASTER, BEGIN_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_TERM_MASTER = '$_REQUEST[DEF_TERM]' ";
}

?>
<select id="<?=$_REQUEST['obj_id']?>" name="<?=$_REQUEST['obj_name']?>" class="form-control <? if($_REQUEST['required'] == 1) echo "required-entry"; ?>" <? if($_REQUEST['style'] != '') echo "style='".$_REQUEST['style']."'"; ?> <?=$_REQUEST['disabled'] ?> <? if($_REQUEST['onclick_fun'] == 1) { ?> onclick="check_campus()" <? } ?> <? if($_REQUEST['onchange'] != '') { ?> onchange="<?=$_REQUEST['onchange']?>" <? } ?> >
	<option value=""></option>
	<? $res_type = $db->Execute("SELECT * FROM (select S_TERM_MASTER.PK_TERM_MASTER, BEGIN_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, S_TERM_MASTER.ACTIVE from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_TERM_MASTER.ACTIVE = 1 $cond $UNION) as TEMP order by ACTIVE DESC, BEGIN_DATE DESC");
	while (!$res_type->EOF) { 
		$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
		if($res_type->fields['ACTIVE'] == 0)
			$str .= ' (Inactive)'; ?>
			
		<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($_REQUEST['DEF_TERM'] == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
		
	<?	$res_type->MoveNext();
	} ?>
</select>