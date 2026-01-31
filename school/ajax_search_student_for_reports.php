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
$s_student_track_table=""; //DIAM-1017

if($_REQUEST['LEAD_ENTRY_FROM_DATE'] != '' && $_REQUEST['LEAD_ENTRY_TO_DATE'] != '') {
	$LEAD_START_DATE = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_FROM_DATE']));
	$LEAD_END_DATE 	 = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_TO_DATE']));
	$cond .= " AND ENTRY_DATE BETWEEN '$LEAD_START_DATE' AND '$LEAD_END_DATE' ";
} else if($_REQUEST['LEAD_ENTRY_FROM_DATE'] != '') {
	$LEAD_END_DATE = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_FROM_DATE']));
	$cond .= " AND ENTRY_DATE >= '$LEAD_START_DATE' ";
} else if($_REQUEST['LEAD_ENTRY_TO_DATE'] != '') {
	$LEAD_END_DATE = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_TO_DATE']));
	$cond .= " AND ENTRY_DATE <= '$LEAD_END_DATE' ";
}

$TREM_BEGIN_START_DATE = isset($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = isset($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];

$TREM_END_START_DATE = isset($_REQUEST['TREM_END_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_START_DATE']) : $_SESSION['TREM_END_START_DATE'];

$TREM_END_END_DATE = isset($_REQUEST['TREM_END_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_END_DATE']) : $_SESSION['TREM_END_END_DATE'];

//DIAM-1199 LDA
if($_POST['LDA_START_DATE'] != '' && $_POST['LDA_END_DATE'] != '') {
	$ST = date("Y-m-d",strtotime($_POST['LDA_START_DATE']));
	$ET = date("Y-m-d",strtotime($_POST['LDA_END_DATE']));
	$cond .= " AND LDA BETWEEN '$ST' AND '$ET' ";
} else if($_POST['LDA_START_DATE'] != ''){
	$ST = date("Y-m-d",strtotime($_POST['LDA_START_DATE']));
	$cond .= " AND LDA >= '$ST' ";
} else if($_POST['LDA_END_DATE'] != ''){
	$ET = date("Y-m-d",strtotime($_POST['LDA_END_DATE']));
	$cond .= " AND LDA <= '$ET' ";
}
//DIAM-1199 LDA

//589	
$TERM_CONDITION_AV = '  ';

function is_defined($variable){
	if(isset($variable) && $variable != 'undefined' && $variable != ''){
		return true;
	}else{
		return false;
	}
}

if(is_defined($TREM_BEGIN_START_DATE)){
	$TREM_BEGIN_START_DATE = date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));	
	$TERM_CONDITION_AV .= " AND S_TERM_MASTER.BEGIN_DATE >= '$TREM_BEGIN_START_DATE' ";
} 

if(is_defined($TREM_BEGIN_END_DATE)){
	$TREM_BEGIN_END_DATE = date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));	
	$TERM_CONDITION_AV .= "  AND S_TERM_MASTER.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
} 

if(is_defined($TREM_END_START_DATE)){
	$TREM_END_START_DATE = date('Y-m-d',strtotime($TREM_END_START_DATE));	
	$TERM_CONDITION_AV .= "  AND S_TERM_MASTER.END_DATE >= '$TREM_END_START_DATE' ";
} 
	 
if(is_defined($TREM_END_END_DATE)){
	$TREM_END_END_DATE = date('Y-m-d',strtotime($TREM_END_END_DATE));	
	$TERM_CONDITION_AV .= "  AND S_TERM_MASTER.END_DATE <= '$TREM_END_END_DATE' ";
} 
 
//589


if(!empty($_REQUEST['PK_FUNDING']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_FUNDING IN (".$_REQUEST['PK_FUNDING'].") ";

if(!empty($_REQUEST['PK_STUDENT_GROUP']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN (".$_REQUEST['PK_STUDENT_GROUP'].") ";
	
if(!empty($_REQUEST['PK_LEAD_SOURCE']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE IN (".$_REQUEST['PK_LEAD_SOURCE'].") ";
	

$TREM_BEGIN_START_DATE = is_defined($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = is_defined($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];
	
$TREM_END_START_DATE = is_defined($_REQUEST['TREM_END_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_START_DATE']) : $_SESSION['TREM_END_START_DATE'];
	
$TREM_END_END_DATE = is_defined($_REQUEST['TREM_END_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_END_DATE']) : $_SESSION['TREM_END_END_DATE'];
	
if(is_defined($TREM_BEGIN_START_DATE) && is_defined($TREM_BEGIN_END_DATE)) {
	$TREM_BEGIN_START_DATE=date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));
	$TREM_BEGIN_END_DATE=date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));
	$cond .= " AND S_TERM_MASTER.BEGIN_DATE >= '$TREM_BEGIN_START_DATE' AND S_TERM_MASTER.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
}
	
if( is_defined($TREM_END_START_DATE) && is_defined($TREM_END_END_DATE) ) {
	$TREM_END_START_DATE=date('Y-m-d',strtotime($TREM_END_START_DATE));
	$TREM_END_END_DATE=date('Y-m-d',strtotime($TREM_END_END_DATE));
	$cond .= " AND S_TERM_MASTER.END_DATE >= '$TREM_END_START_DATE' AND S_TERM_MASTER.END_DATE <= '$TREM_END_END_DATE' ";
} 


if(!empty($_REQUEST['PK_TERM_MASTER']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN (".$_REQUEST['PK_TERM_MASTER'].") ";

if(!empty($_REQUEST['MIDPOINT_DATE']))
{
	$result = "'" . implode ( "', '", explode(',',$_REQUEST['MIDPOINT_DATE']) ) . "'";
	$cond .= " AND S_STUDENT_ENROLLMENT.MIDPOINT_DATE IN (".$result.") ";
}
	
if(!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (".$_REQUEST['PK_CAMPUS_PROGRAM'].") ";
	
if(!empty($_REQUEST['PK_STUDENT_STATUS']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".$_REQUEST['PK_STUDENT_STATUS'].") ";

if(!empty($_REQUEST['PK_COURSE']))
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE IN (".$_REQUEST['PK_COURSE'].") ";

if(!empty($_REQUEST['PK_COURSE_OFFERING']))
	$cond .= " AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN (".$_REQUEST['PK_COURSE_OFFERING'].") ";
	
if(!empty($_REQUEST['PK_REPRESENTATIVE']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE IN (".$_REQUEST['PK_REPRESENTATIVE'].") ";
	
if(!empty($_REQUEST['PK_PLACEMENT_STATUS']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS IN (".$_REQUEST['PK_PLACEMENT_STATUS'].") ";

if(!empty($_REQUEST['PK_CAMPUS_GPA'])) // DIAM-1419
	$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS_GPA'].") ";

// DIAM-1535/DIAM-1313
// if(!empty($_REQUEST['PK_COURSE_OFFERING_TERM_MASTER'])) 
// {
// 	//$cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER IN (".$_REQUEST['PK_COURSE_OFFERING_TERM_MASTER'].") ";
// }
$cond_co='';
if(!empty($_REQUEST['PK_CAMPUS_COURSE_OFFERING'])) 
{
	$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS_COURSE_OFFERING'].") ";
	$cond_co .= " AND S_TERM_MASTER_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS_COURSE_OFFERING'].") ";
}

if(!empty($_REQUEST['PK_COURSE_OFFERING_TERM_MASTER'])) 
{
$sQuery_Stud_assing_course ="SELECT S_STUDENT_COURSE.PK_STUDENT_MASTER, S_TERM_MASTER.PK_TERM_MASTER, S_TERM_MASTER.TERM_DESCRIPTION FROM S_STUDENT_COURSE INNER JOIN S_COURSE_OFFERING ON S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING INNER JOIN S_TERM_MASTER ON S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER INNER JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond_co AND S_TERM_MASTER.PK_TERM_MASTER IN (".$_REQUEST['PK_COURSE_OFFERING_TERM_MASTER'].") GROUP BY S_STUDENT_COURSE.PK_STUDENT_MASTER";
$res_co_stud = $db->Execute($sQuery_Stud_assing_course); 
$array_co_students = array();
while (!$res_co_stud->EOF) {
	$array_co_students[] = $res_co_stud->fields['PK_STUDENT_MASTER'];	
	$res_co_stud->MoveNext();
}
$array_co_students = implode(",",$array_co_students);
//print_r($array_co_stude); exit;
$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER IN (".$array_co_students.") ";
}

// End DIAM-1535/DIAM-1313
	
$camp_cond = "";
if(!empty($_REQUEST['PK_CAMPUS'])) {
	$table .= ",S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].") AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$camp_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].")  ";
}

if($_REQUEST['STU_NAME'] != '') {
	$cond .= " AND CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) LIKE '%$_REQUEST[STU_NAME]%' ";
}
if($_REQUEST['NO_LEAD'] == 1) {
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 ";
} else if($_REQUEST['LEAD'] == 1) {
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 1 ";
} else if($_REQUEST['no_admin_check'] == 1) {
} else 
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 ";
	
if($_REQUEST['bulk_text'] == 1) {
	$table .= ",S_STUDENT_CONTACT ";
	$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CONTACT.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' AND OPT_OUT = 0 AND S_STUDENT_CONTACT.ACTIVE = 1 ";
}
	
//if(!empty($_REQUEST['PK_CAMPUS']) || !empty($_REQUEST['PK_COURSE_OFFERING']) || !empty($_REQUEST['PK_COURSE']) ) {
if(!empty($_REQUEST['PK_COURSE_OFFERING']) || !empty($_REQUEST['PK_COURSE']) || !empty($_REQUEST['COURSE_PK_TERM_MASTER']) || !empty($_REQUEST['PK_CAMPUS_GPA'])  || !empty($_REQUEST['PK_COURSE_OFFERING_TERM_MASTER'])  || !empty($_REQUEST['PK_CAMPUS_COURSE_OFFERING']) || !empty($_REQUEST['GRADE_TYPE'])) { //Ticket # 1212, 1214 //DIAM-1753
	if($_REQUEST['bulk_text'] == 1 || $_REQUEST['page'] == 'letter_gen') {
	} else {

		//DIAM-2059
		if(isset($_POST['STUDENT_REVIEW']) && $_POST['STUDENT_REVIEW']==1){
			$table .= ", S_COURSE_OFFERING ";
		}else{
		$table .= ",S_STUDENT_COURSE, S_COURSE_OFFERING ";
		}//DIAM-2059
		
		$cond .= " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING ";
	}
}	

//Ticket # 1212, 1214 
if(!empty($_REQUEST['COURSE_PK_TERM_MASTER'])){
	$cond .= " AND S_STUDENT_COURSE.PK_TERM_MASTER IN ($_REQUEST[COURSE_PK_TERM_MASTER]) ";
}
//Ticket # 1212, 1214 

if($_REQUEST['ENROLLMENT'] == 2 || $_REQUEST['ENROLLMENT'] == "") {
	$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
}

/* Ticket # 1552 */
if($_REQUEST['SEARCH_TXT'] != '') {
	$cond .= " AND (CONCAT(TRIM(S_STUDENT_MASTER.LAST_NAME),', ', TRIM(S_STUDENT_MASTER.FIRST_NAME)) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.FIRST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.LAST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' ) ";
}
/* Ticket # 1552 */

/* Ticket # 1571 */
if($_REQUEST['PK_CREDIT_TRANSFER_STATUS'] != '') {
	$table .= ",S_STUDENT_CREDIT_TRANSFER ";
	$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER AND PK_CREDIT_TRANSFER_STATUS IN ($_REQUEST[PK_CREDIT_TRANSFER_STATUS]) ";
}
/* Ticket # 1571 */

/* Ticket # 1470 */
if($_REQUEST['FINAL_GRADE'] != '') {
	$cond .= " AND S_STUDENT_COURSE.FINAL_GRADE IN ($_REQUEST[FINAL_GRADE]) ";
}
/* Ticket # 1470 */
//DIAM-1017
$START_DATE = isset($_REQUEST['START_DATE']) ? mysql_real_escape_string($_REQUEST['START_DATE']) : $_SESSION['START_DATE'];
$END_DATE = isset($_REQUEST['END_DATE']) ? mysql_real_escape_string($_REQUEST['END_DATE']) : $_SESSION['END_DATE'];

if($START_DATE != '' && $END_DATE != '' ) {
	$START_DATE=date('Y-m-d',strtotime($START_DATE));
	$END_DATE=date('Y-m-d',strtotime($END_DATE));
	$cond .= " AND S_STUDENT_TRACK_CHANGES.CHANGED_ON >= '$START_DATE' AND S_STUDENT_TRACK_CHANGES.CHANGED_ON <= '$END_DATE' ";
	$s_student_track_table="LEFT JOIN S_STUDENT_TRACK_CHANGES ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TRACK_CHANGES.PK_STUDENT_MASTER";
}
//DIAM-1017
$group_by = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
if($_REQUEST['group_by'])
{
	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
}
	
if(isset($_POST['STUDENT_REVIEW']) && $_POST['STUDENT_REVIEW']==1){
//DAIM-1199-LDA-REVIEW REPORT
$sQuery_Stud = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_GROUP, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE,  IF(
	T1.BEGIN_DATE = '0000-00-00',
	'',
	DATE_FORMAT(T1.BEGIN_DATE, '%m/%d/%Y')
) AS BEGIN_DATE_1, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STUDENT_ID   
FROM 
S_STUDENT_MASTER  $s_student_track_table, S_STUDENT_ACADEMICS  $table , S_STUDENT_ENROLLMENT
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP
LEFT  JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER
LEFT JOIN S_TERM_MASTER AS T1 ON T1.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
, M_STUDENT_STATUS 
WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS  $cond $group_by ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC";

}else{

$sQuery_Stud = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_GROUP, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STUDENT_ID   
FROM 
S_STUDENT_MASTER  $s_student_track_table, S_STUDENT_ACADEMICS  $table , S_STUDENT_ENROLLMENT
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
, M_STUDENT_STATUS 
WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS  $cond $group_by ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC";
}

$res_stud = $db->Execute($sQuery_Stud); 
//echo $sQuery_Stud;exit;

if($_REQUEST['REPORT_TYPE']!=12){
?>

<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<? } ?>
			<th><?=STUDENT?></th>
			<th><?=STUDENT_ID?></th><!-- Ticket # 1371 -->
			<th><?=CAMPUS?></th><!-- Ticket # 1371 -->
			<th><?=FIRST_TERM?></th>
			<th><?=PROGRAM?></th>
			<th><?=STATUS?></th>
			<th><?=STUDENT_GROUP?></th> <!-- Ticket # 1247 -->
			<th>	
				<?=TOTAL_COUNT.': '.$res_stud->RecordCount() ?>				
				<? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?>
					<?php } ?>
				<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
				
			</th>
		</tr>
	</thead>
	<tbody id="statusDiv"> <!-- // DIAM-757 -->
	<? while (!$res_stud->EOF) { ?>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" class="delete_if_not_selected" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" <? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?> onclick="get_count()" <? } ?> />
			</th>
			<? } ?>
			<td >
				<input type="hidden" name="PK_STUDENT_MASTER[]" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" >
				<input type="hidden" name="PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" id="S_PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" ><!-- Ticket # 1193 --><!-- Ticket # 1673 -->
				
				<? if($_REQUEST['show_check'] != 1){ ?>
				<input type="hidden" name="PK_STUDENT_ENROLLMENT[]" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" >
				<? } ?>
				
				<?=$res_stud->fields['STU_NAME']?>
			</td>
			<td  > <!-- Ticket # 1371 -->
				<?=$res_stud->fields['STUDENT_ID']?>
			</td>
			<!-- Ticket # 1371 -->
			<td >
				<? $PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
				/* Ticket # 1371  */
				$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE, CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' $camp_cond ");
				echo $res_camp_1->fields['CAMPUS_CODE'];
				/* Ticket # 1371  */ ?>
			</td>
			<td  > <!-- Ticket # 1247 -->
				<?=$res_stud->fields['BEGIN_DATE_1']?>
			</td>
			<!-- Ticket # 1247 -->
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
<? 
}

// DIAM-757 
$data = array();
if($_REQUEST['REPORT_TYPE']==12){
	 while (!$res_stud->EOF) { 
		$PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
		$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE, CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' $camp_cond ");
				 $res_camp_1->fields['CAMPUS_CODE'];

				 if($_REQUEST['show_check'] == 1){
					
					 if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') {
						$onclick= 'onclick="get_count()'; 
					}  

						$cehckbox ='<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="'.$res_stud->fields['PK_STUDENT_ENROLLMENT'].'"'.$onclick.'"/>';
					 } 

					 $cehckbox .='<input type="hidden" name="PK_STUDENT_MASTER[]" value="'.$res_stud->fields['PK_STUDENT_MASTER'].'" >';
					 $cehckbox .='<input type="hidden" name="PK_STUDENT_MASTER_'.$res_stud->fields['PK_STUDENT_ENROLLMENT'].'" id="S_PK_STUDENT_MASTER_'.$res_stud->fields['PK_STUDENT_ENROLLMENT'].'" value="'.$res_stud->fields['PK_STUDENT_MASTER'].'" >';
				
					if($_REQUEST['show_check'] != 1){
						$cehckbox .='<input type="hidden" name="PK_STUDENT_ENROLLMENT[]" value="'.$res_stud->fields['PK_STUDENT_ENROLLMENT'].'" >';
					} 

		$data[]=array(
			// 'PK_STUDENT_ENROLLMENT' =>$cehckbox,	
			// 'recid' => $res_stud->fields['PK_STUDENT_ENROLLMENT'],
			'PK_STUDENT_ENROLLMENT' => $res_stud->fields['PK_STUDENT_ENROLLMENT'],	
			'PK_STUDENT_MASTER' => $res_stud->fields['PK_STUDENT_MASTER'],	
			'STU_NAME' =>$res_stud->fields['STU_NAME'],
			'STUDENT_ID' =>$res_stud->fields['STUDENT_ID'],
			'CAMPUS_CODE' =>$res_camp_1->fields['CAMPUS_CODE'],
			'BEGIN_DATE_1' =>$res_stud->fields['BEGIN_DATE_1'],
			'CODE' =>$res_stud->fields['CODE'],
			'STUDENT_STATUS' =>$res_stud->fields['STUDENT_STATUS'],
			'STUDENT_GROUP' =>$res_stud->fields['STUDENT_GROUP']
		);
		$res_stud->MoveNext();
	 }


	

	 $json_data =  json_encode(array(
		'json_data'=>$data
	 ));

	echo $json_data;

}
// DIAM-757 
?>
