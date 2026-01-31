<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_COURSE	= $_REQUEST['PK_COURSE'];
$SELECTED   = $_REQUEST['SELECTED'];
?>
<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" onchange="get_course_offering_from_term();" >
	<option value="" ></option>		
	<? /* Ticket #1149 - term */
	$res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER ,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, S_TERM_MASTER.ACTIVE from S_TERM_MASTER,S_COURSE_OFFERING WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND PK_COURSE = '$PK_COURSE' GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by S_TERM_MASTER.ACTIVE DESC, BEGIN_DATE DESC");
	while (!$res_type->EOF) { 
		$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
		if($res_type->fields['ACTIVE'] == 0)
			$str .= ' (Inactive)'; ?>
		<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['PK_TERM_MASTER'] == $SELECTED) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
	<?	$res_type->MoveNext();
	} 
	/* Ticket #1149 - term */ ?>
</select>