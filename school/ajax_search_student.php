<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_COURSE 	= $_REQUEST['PK_STUDENT_COURSE'];

$cond 		= "";
$group_by 	= "";
$table 		= "";
if($_REQUEST['STU_NAME'] != '')
	$cond .= " AND CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) like '%$_REQUEST[STU_NAME]%' ";
	
if($_REQUEST['PK_STUDENT_GROUP'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN ($_REQUEST[PK_STUDENT_GROUP]) ";
	
if($_REQUEST['PK_TERM_MASTER'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN ($_REQUEST[PK_TERM_MASTER]) ";
	
if($_REQUEST['PK_CAMPUS_PROGRAM'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($_REQUEST[PK_CAMPUS_PROGRAM]) ";
	
if($_REQUEST['PK_STUDENT_STATUS'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($_REQUEST[PK_STUDENT_STATUS]) ";

if($_REQUEST['PK_COURSE'] != '')
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE IN ($_REQUEST[PK_COURSE]) ";

if($_REQUEST['PK_COURSE_OFFERING'] != '')
	$cond .= " AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN ($_REQUEST[PK_COURSE_OFFERING]) ";
	
if(isset($_REQUEST['LEAD'])) {
	if($_REQUEST['LEAD'] == 1)
		$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 1 ";
	else
		$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 ";
}

if($_REQUEST['PK_SESSION'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_SESSION IN ($_REQUEST[PK_SESSION]) ";

if($_REQUEST['PK_CAMPUS'] != '') {
	$table .= ",S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
}
if($_REQUEST['PK_COURSE'] != '' || $_REQUEST['PK_COURSE_OFFERING'] != '') {
	$table .= ",S_STUDENT_COURSE, S_COURSE_OFFERING ";
	
	$cond .= " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING ";
}	

/*if($_REQUEST['type'] == 'add_course_stu')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT NOT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE')  AND S_STUDENT_MASTER.ARCHIVED = 0 ";*/

if($_REQUEST['type'] == 'add_course_stu') {
	$table 				 .= ",S_STUDENT_CAMPUS ";
	$PK_COURSE_OFFERING1 = $_REQUEST['PK_COURSE_OFFERING1'];
	
	$res_co_camp 	= $db->Execute("SELECT PK_CAMPUS FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING1' ");
	if($_REQUEST['PK_CAMPUS_COUR'] != '') {  // DIAM - 79
		$PK_CAMPUS 	= $_REQUEST['PK_CAMPUS_COUR'];
	}
	else{
		$PK_CAMPUS 	= $res_co_camp->fields['PK_CAMPUS'];
	}
	
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT NOT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING1') AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	
}	
if($_REQUEST['type'] != 'consolidate')
	$cond .= " AND S_STUDENT_MASTER.ARCHIVED = 0 ";

if($_REQUEST['type'] == 'consolidate') {
	if($REGISTRAR_ACCESS != 3 && $FINANCE_ACCESS != 3 && $ACCOUNTING_ACCESS != 3 && $PLACEMENT_ACCESS != 3)
		$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 1 ";
}
	
if($_REQUEST['type'] == 'letter_gen' || $_REQUEST['active_enroll'] == 1 )
	$cond .= " AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 ";	
	
if($_REQUEST['type'] == 'bulk_create_login') {
	$table 	.= " ";
	$cond 	.= " AND S_STUDENT_MASTER.LOGIN_CREATED != 1 AND STUDENT_ID != ''";
	$group_by = ' S_STUDENT_MASTER.PK_STUDENT_MASTER ';
}
if($group_by != '')
	$group_by = ' GROUP BY '.$group_by;
	
/* Ticket # 1552 */
if($_REQUEST['SEARCH_TXT'] != '') {
	$cond .= " AND (CONCAT(TRIM(S_STUDENT_MASTER.LAST_NAME),', ', TRIM(S_STUDENT_MASTER.FIRST_NAME)) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.FIRST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.LAST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' ) ";
}
/* Ticket # 1552 */

$res_type = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,SSN,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME,STUDENT_GROUP, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS,IF(STATUS_DATE = '0000-00-00','',DATE_FORMAT(STATUS_DATE,'%m/%d/%Y' )) AS STATUS_DATE, STUDENT_ID 
FROM 
S_STUDENT_MASTER $table , S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM  
,M_STUDENT_STATUS 
WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.ARCHIVED = 0 $cond $group_by ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC ");	
?>
<div class="table-responsive p-20">
	<? /* Ticket # 1827 */
	if($_REQUEST['type'] == 'letter_gen' || $_REQUEST['show_count'] == 1){ ?>
	<div class="row">
        <div class="col-12" style="text-align:right" >
			<b><?=TOTAL_COUNT.': '.$res_type->RecordCount() ?></b>
		</div>
		<? if($_REQUEST['show_count'] == 1){ ?>
		<div class="col-12" style="text-align:right" >
			<b><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT_SPAN"></span></b>
		</div>
		<? } ?>
	</div>
	<? } /* Ticket # 1827 */ ?>
	<table class="table table-hover" >
		<thead>
			<tr>
				<? if($_REQUEST['type'] == 'consolidate'){ ?>
					<th><?=LEAD?></th>
					<th><?=SSN?></th>
					<th><?=PROGRAM?></th>
					<th><?=LEAD_STATUS?></th>
					<th><?=LEAD_DATE?></th>
				<? } else if($_REQUEST['type'] == 'bulk_create_login'){ ?>
					<th>
						<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
					</th>
					<th><?=STUDENT?></th>
					<th><?=STATUS?></th>
					<th><?=FIRST_TERM?></th>
					<th><?=PROGRAM?></th>
					<th><?=GROUP_CODE?></th>
				<? } else if($_REQUEST['type'] == 'student_id'){ ?>
					<th>
						<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
					</th>
					<th><?=STUDENT?></th>
					<th><?=CAMPUS?></th>
					<th><?=FIRST_TERM?></th>
					<th><?=PROGRAM?></th>
					<th><?=GROUP_CODE?></th>
					<th><?=STATUS?></th>
				<? } else { ?>
					<th>
						<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
					</th>
					<th><?=STUDENT?></th>
					<th><?=STUDENT_ID?></th>
					<? if($_REQUEST['type'] == 'add_course_stu') { ?>
					<th><?=ENROLLMENT?></th>
					<? } ?>
					<th><?=PROGRAM?></th>
					<th><?=STATUS?></th>
					<th><?=GROUP_CODE?></th>
				<? } ?>
				
			</tr>
		</thead>
		<tbody>
			<? while (!$res_type->EOF) { ?>
				<tr id="search_stu_det_<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" >
					<? if($_REQUEST['type'] == 'consolidate'){ 
						$SSN  = $res_type->fields['SSN'];
						if($SSN != '') {
							$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
							$SSN_ORG = $SSN;
							$SSN_ARR = explode("-",$SSN);
							$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
						} ?>
						<td><?=$res_type->fields['STU_NAME']?></td>
						<td><?=$SSN?></td>
						<td><?=$res_type->fields['CODE']?></td>
						<td><?=$res_type->fields['STUDENT_STATUS']?></td>
						<td><?=$res_type->fields['STATUS_DATE']?></td>
						<td>
							<button type="button" onclick="consolidate_stud('<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>')" class="btn waves-effect waves-light btn-info"><?=SELECT?></button>
						</td>
					<? } else if($_REQUEST['type'] == 'bulk_create_login'){ ?>
						<td>
							<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" onclick="show_btn()" />
						</td>
						<td><?=$res_type->fields['STU_NAME']?></td>
						<td><?=$res_type->fields['STUDENT_STATUS']?></td>
						<td><?=$res_type->fields['BEGIN_DATE']?></td>
						<td><?=$res_type->fields['CODE']?></td>
						<td><?=$res_type->fields['STUDENT_GROUP']?></td>
					<? } else if($_REQUEST['type'] == 'student_id'){ ?>
						<td>
							<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" onclick="show_btn()" />
						</td>
						<td><?=$res_type->fields['STU_NAME']?></td>
						<td>
							<? $PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
							if($_REQUEST['PK_CAMPUS'] != '')
								$camp_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
							else
								$camp_cond = "";
							$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  $camp_cond ");
							echo $res_campus->fields['CAMPUS_CODE'];
							?>
						</td>
						<td><?=$res_type->fields['BEGIN_DATE']?></td>
						<td><?=$res_type->fields['CODE']?></td>
						<td><?=$res_type->fields['STUDENT_GROUP']?></td>
						<td><?=$res_type->fields['STUDENT_STATUS']?></td>
					<? } else { ?>
						<td>
							<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" onclick="show_btn()" />
						</td>
						<td><?=$res_type->fields['STU_NAME']?></td>
						<td><?=$res_type->fields['STUDENT_ID']?></td>
						<? if($_REQUEST['type'] == 'add_course_stu') { 
							$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, IS_ACTIVE_ENROLLMENT,FUNDING, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '".$res_type->fields['PK_STUDENT_ENROLLMENT']."' "); ?>
							<td><?=$res_enroll->fields['BEGIN_DATE_1'].' - '.$res_enroll->fields['CODE'].' - '.$res_enroll->fields['STUDENT_STATUS'].' - '.$res_enroll->fields['CAMPUS_CODE']?></td>
						<? } ?>
						<td><?=$res_type->fields['CODE']?></td>
						<td><?=$res_type->fields['STUDENT_STATUS']?></td>
						<td><?=$res_type->fields['STUDENT_GROUP']?></td>
						
					<? } ?>
					
				</tr>
			<?	$res_type->MoveNext();
			} ?>
		</tbody>
	</table>
</div>