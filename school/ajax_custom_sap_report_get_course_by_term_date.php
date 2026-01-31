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

$COURSE_FIRST_TERM_START_DATE = isset($_REQUEST['COURSE_FIRST_TERM_START_DATE']) ? mysql_real_escape_string($_REQUEST['COURSE_FIRST_TERM_START_DATE']) : $_SESSION['COURSE_FIRST_TERM_START_DATE'];

$COURSE_LAST_TERM_END_DATE = isset($_REQUEST['COURSE_LAST_TERM_END_DATE']) ? mysql_real_escape_string($_REQUEST['COURSE_LAST_TERM_END_DATE']) : $_SESSION['COURSE_LAST_TERM_END_DATE'];


$cond = "";
if($_REQUEST['PK_CAMPUS'] != '') {
	$cond = " AND S_TERM_MASTER_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
}

if($COURSE_FIRST_TERM_START_DATE != '' && $COURSE_LAST_TERM_END_DATE != '' ) {
	$COURSE_FIRST_TERM_START_DATE=date('Y-m-d',strtotime($COURSE_FIRST_TERM_START_DATE));
	$COURSE_LAST_TERM_END_DATE=date('Y-m-d',strtotime($COURSE_LAST_TERM_END_DATE));
	$cond .= " AND S_TERM_MASTER.BEGIN_DATE >= '$COURSE_FIRST_TERM_START_DATE' AND S_TERM_MASTER.BEGIN_DATE <= '$COURSE_LAST_TERM_END_DATE' ";
}


//  echo "select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by BEGIN_DATE DESC"; exit();
?>

	<? 
	$PK_TERM_MASTER_VALUES = array();
	 $sql = "select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by BEGIN_DATE DESC";
	//  echo $sql;exit;
	$res_type = $db->Execute($sql);

	while (!$res_type->EOF) { 
		$PK_TERM_MASTER_VALUES[] = $res_type->fields['PK_TERM_MASTER'];	
		$res_type->MoveNext();
	}

	$course_name = "PK_COURSE";
	$course_id 	 = "PK_COURSE";
	$multiple 	 = "multiple";
	$PK_TERM = implode(",",$PK_TERM_MASTER_VALUES);
	?>
<select id="<?=$course_id?>" name="<?=$course_name?>" <?=$multiple?> class="form-control" >
		
	<? $res_type1 = $db->Execute("SELECT * FROM (select S_COURSE.PK_COURSE, COURSE_CODE,TRANSCRIPT_CODE,COURSE_DESCRIPTION, S_COURSE.ACTIVE from S_COURSE_OFFERING, S_COURSE WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND PK_TERM_MASTER IN ($PK_TERM) ) as TEMP GROUP BY PK_COURSE order by ACTIVE DESC, COURSE_CODE ASC ");

	while (!$res_type1->EOF) { 
		if($res_type1->fields['ACTIVE'] == 0)
			//$str .= ' (Inactive)'; 			
	  ?>		
		<option value="<?= $res_type1->fields['PK_COURSE'] ?>" <?php if ($res_type1->fields['ACTIVE'] == '0') echo ' class="option_red" ' ?>><?= $res_type1->fields['COURSE_CODE'] . ' - ' . $res_type1->fields['TRANSCRIPT_CODE'] . ' - ' . $res_type1->fields['COURSE_DESCRIPTION'] ?> <?php if ($res_type1->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
																</option>
	<?	$res_type1->MoveNext();
	} ?>
</select>

<input type="hidden" name="COURSE_PK_TERM" id="COURSE_PK_TERM" value="<?=$PK_TERM?>"> 


