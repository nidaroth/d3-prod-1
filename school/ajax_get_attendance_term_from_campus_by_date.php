<? 
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if (check_access('REPORT_REGISTRAR') == 0) {
	header("location:../index");
	exit;
}

$report_type= $_POST['report_type'];

$TREM_BEGIN_START_DATE = isset($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = isset($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];

$TREM_END_START_DATE = isset($_REQUEST['TREM_END_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_START_DATE']) : $_SESSION['TREM_END_START_DATE'];

$TREM_END_END_DATE = isset($_REQUEST['TREM_END_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_END_DATE']) : $_SESSION['TREM_END_END_DATE'];



$cond = "";
if($_REQUEST['PK_CAMPUS'] != '') {
	$cond = " AND S_TERM_MASTER_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
}

if($TREM_BEGIN_START_DATE != '' && $TREM_BEGIN_END_DATE != '' ) {
	$TREM_BEGIN_START_DATE=date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));
	$TREM_BEGIN_END_DATE=date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));
	$cond .= " AND S_TERM_MASTER.BEGIN_DATE >= '$TREM_BEGIN_START_DATE' AND S_TERM_MASTER.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
}

if($TREM_END_START_DATE != '' && $TREM_END_END_DATE != '' ) {
	$TREM_END_START_DATE=date('Y-m-d',strtotime($TREM_END_START_DATE));
	$TREM_END_END_DATE=date('Y-m-d',strtotime($TREM_END_END_DATE));
	$cond .= " AND S_TERM_MASTER.END_DATE >= '$TREM_END_START_DATE' AND S_TERM_MASTER.END_DATE <= '$TREM_END_END_DATE' ";
} 

//  echo "select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by BEGIN_DATE DESC"; exit();
?>

	<? 
	$PK_TERM_MASTER_VALUES = array();
	 $sql = "select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by BEGIN_DATE DESC";
	//  echo $sql;exit;
	$res_type = $db->Execute($sql);
	// dump("select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by BEGIN_DATE DESC");
	while (!$res_type->EOF) { 
		$PK_TERM_MASTER_VALUES[] = $res_type->fields['PK_TERM_MASTER'];	
		$res_type->MoveNext();
	}
	?>
	<?	if($report_type==1 || $report_type==5){ ?> <!--DIAM-1417-->
	<input type="hidden" id="PK_TERM_MASTER_5" name="PK_TERM_MASTER_5" value="<?=implode(',',$PK_TERM_MASTER_VALUES)?>" />
	<? } ?>
	<?	if($report_type==11){ ?>
	<input type="hidden" id="PK_TERM_MASTER_4" name="PK_TERM_MASTER_4" value="<?=implode(',',$PK_TERM_MASTER_VALUES)?>" />
	<? } ?>
	<?	if($report_type==3){ ?>
	<input type="hidden" id="PK_TERM_MASTER_2" name="PK_TERM_MASTER_2" value="<?=implode(',',$PK_TERM_MASTER_VALUES)?>" />
	<? } ?>
	<?	if($report_type==12){ ?>
	<input type="hidden" id="COURSE_PK_TERM" name="COURSE_PK_TERM" value="<?=implode(',',$PK_TERM_MASTER_VALUES)?>" />
	<? } ?>




