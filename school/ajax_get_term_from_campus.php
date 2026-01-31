<? require_once("../global/config.php"); 
require_once("get_department_from_t.php");
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond = "";
if($_REQUEST['PK_CAMPUS'] != '') {
	$cond = " AND S_TERM_MASTER_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
}
?>
<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" >
	<option value=""></option>
	<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by BEGIN_DATE DESC");
	while (!$res_type->EOF) { ?>
		<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']." - ".$res_type->fields['END_DATE_1']." - ".$res_type->fields['TERM_DESCRIPTION']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>