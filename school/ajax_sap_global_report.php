<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/bulk_text.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond 		= "";
$group_by 	= "";
$table 		= "";

$camp_cond = "";
if(!empty($_REQUEST['PK_CAMPUS'])) {
	$table .= ",S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].") AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$camp_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].")  ";
}

if(!empty($_REQUEST['PK_TERM_MASTER'])){
    $cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN (".$_REQUEST['PK_TERM_MASTER'].") ";
}	

if(!empty($_REQUEST['PK_CAMPUS_PROGRAM'])){
    $cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (".$_REQUEST['PK_CAMPUS_PROGRAM'].") ";
}
	
if(!empty($_REQUEST['PK_STUDENT_STATUS'])){
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".$_REQUEST['PK_STUDENT_STATUS'].") ";
}

if(!empty($_REQUEST['PK_STUDENT_GROUP'])){
    $cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN (".$_REQUEST['PK_STUDENT_GROUP'].") ";
}
	
if ($_REQUEST['MIDPOINT_START_DATE'] != '' && $_REQUEST['MIDPOINT_END_DATE'] != '') {
	$MIDPOINT_START_DATE 	= date("Y-m-d", strtotime($_REQUEST['MIDPOINT_START_DATE']));
	$MIDPOINT_END_DATE 	= date("Y-m-d", strtotime($_REQUEST['MIDPOINT_END_DATE']));

	$cond .= " AND S_STUDENT_ENROLLMENT.MIDPOINT_DATE BETWEEN '$MIDPOINT_START_DATE' AND '$MIDPOINT_END_DATE' ";
} else if ($_REQUEST['MIDPOINT_BEGIN_DATE'] != '') {
	$MIDPOINT_BEGIN_DATE = date("Y-m-d", strtotime($_REQUEST['MIDPOINT_BEGIN_DATE']));

	$cond .= " AND S_STUDENT_ENROLLMENT.MIDPOINT_DATE >= '$MIDPOINT_BEGIN_DATE' ";
} else if ($_REQUEST['MIDPOINT_END_DATE'] != '') {
	$MIDPOINT_END_DATE = date("Y-m-d", strtotime($_REQUEST['MIDPOINT_END_DATE']));

	$cond .= " AND S_STUDENT_ENROLLMENT.MIDPOINT_DATE <= '$MIDPOINT_END_DATE' ";
}


// if(!empty($_REQUEST['COURSE_PK_TERM_MASTER'])){
// 	$cond .= " AND S_STUDENT_COURSE.PK_TERM_MASTER IN ($_REQUEST[COURSE_PK_TERM_MASTER]) ";
// }

// if($_REQUEST['ENROLLMENT'] == 2 || $_REQUEST['ENROLLMENT'] == "") {
// 	$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
// }

if($_REQUEST['SEARCH_TXT'] != '') {
	$cond .= " AND (CONCAT(TRIM(S_STUDENT_MASTER.LAST_NAME),', ', TRIM(S_STUDENT_MASTER.FIRST_NAME)) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.FIRST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.LAST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' ) ";
}

$group_by = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
if($_REQUEST['group_by']){
    $group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
}

$res_stud = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_GROUP, M_CAMPUS_PROGRAM.CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, M_STUDENT_STATUS.STUDENT_STATUS AS STUDENT_STATUS
FROM S_STUDENT_MASTER  $table , S_STUDENT_ENROLLMENT
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM  
LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0  $cond $group_by ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC"); 

?>

<div class="row">
    <div class="col-12" style="text-align:right" >
        <b><?=TOTAL_COUNT.': '.$res_stud->RecordCount() ?></b>
    </div>
    <div class="col-12" style="text-align:right" >
        <b><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span></b>
    </div>
</div>

<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<th>
				<input type="checkbox"  name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<th><?=STUDENT?></th>
			<th><?=CAMPUS?></th>
			<th><?=FIRST_TERM?></th>
			<th><?=PROGRAM?></th>
			<th><?=STUDENT_STATUS?></th>
			<th><?=STUDENT_GROUP?></th> 
		</tr>
	</thead>
	<tbody>
	<? while (!$res_stud->EOF) { ?>
		<tr>
			<th>
				<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" class="stud_enr" id="PK_STUDENT_ENROLLMENT" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" onclick="get_count()" />
			</th>
			<td >
				<input type="hidden" name="PK_STUDENT_MASTER[]" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" >
				<input type="hidden" name="PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" id="S_PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" >
				
				<?=$res_stud->fields['STU_NAME']?>
			</td>
			<td >
				<? $PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
				$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' $camp_cond ");
				echo $res_camp_1->fields['CAMPUS_CODE'];
				?>
			</td>
			<td  > 
				<?=$res_stud->fields['BEGIN_DATE_1']?>
			</td>
			<td >
				<?=$res_stud->fields['CODE']?>
			</td>
			<td >
				<?=$res_stud->fields['STUDENT_STATUS']?>
			</td>
			<td colspan="2" >
				<?=$res_stud->fields['STUDENT_GROUP']?>
			</td>
		</tr>
		
	<?	$res_stud->MoveNext();
	} ?>
	</tbody>
</table>