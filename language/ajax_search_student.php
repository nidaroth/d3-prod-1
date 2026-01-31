<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_COURSE 	= $_REQUEST['PK_STUDENT_COURSE'];

$cond 		= "";
$group_by 	= "";
if($_REQUEST['STU_NAME'] != '')
	$cond .= " AND CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) like '%$_REQUEST[STU_NAME]%' ";
	
if($_REQUEST['PK_STUDENT_GROUP'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP = '$_REQUEST[PK_STUDENT_GROUP]' ";
	
if($_REQUEST['PK_TERM_MASTER'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER = '$_REQUEST[PK_TERM_MASTER]' ";
	
if($_REQUEST['PK_CAMPUS_PROGRAM'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$_REQUEST[PK_CAMPUS_PROGRAM]' ";
	
if($_REQUEST['PK_STUDENT_STATUS'] != '')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = '$_REQUEST[PK_STUDENT_STATUS]' ";

if($_REQUEST['PK_COURSE'] != '')
	$cond .= " AND S_STUDENT_COURSE.PK_COURSE = '$_REQUEST[PK_COURSE]' ";

if($_REQUEST['PK_COURSE_OFFERING'] != '')
	$cond .= " AND S_STUDENT_COURSE.PK_COURSE_OFFERING = '$_REQUEST[PK_COURSE_OFFERING]' ";

$table = "";
if($_REQUEST['PK_COURSE'] != '' || $_REQUEST['PK_COURSE_OFFERING'] != '') {
	$table = ",S_STUDENT_COURSE";
	
	$cond .= " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT";
}	

/*if($_REQUEST['type'] == 'add_course_stu')
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT NOT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE')  AND S_STUDENT_MASTER.ARCHIVED = 0 ";*/

if($_REQUEST['type'] == 'add_course_stu')
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT NOT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING') ";
	
if($_REQUEST['type'] != 'consolidate')
	$cond .= " AND S_STUDENT_MASTER.ARCHIVED = 0 ";

if($_REQUEST['type'] == 'letter_gen')
	$cond .= " AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 ";	
	
if($_REQUEST['type'] == 'bulk_create_login') {
	$cond .= " AND S_STUDENT_MASTER.LOGIN_CREATED != 1 ";
	$group_by = ' S_STUDENT_MASTER.PK_STUDENT_MASTER ';
}
if($group_by != '')
	$group_by = ' GROUP BY '.$group_by;
?>
<div class="table-responsive p-20">
	<table class="table table-hover" >
		<thead>
			<tr>
				<? if($_REQUEST['type'] == 'consolidate'){ ?>
					<th><?=LEAD?></th>
					<th><?=SSN?></th>
					<th><?=PROGRAM?></th>
					<th><?=LEAD_STATUS?></th>
					<th><?=LEAD_DATE?></th>
				<? } else { ?>
					<th>
						<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
					</th>
					<th><?=STUDENT?></th>
					<th><?=GROUP_CODE?></th>
					<th><?=FIRST_TERM?></th>
					<th><?=PROGRAM?></th>
					<th><?=STATUS?></th>
				<? } ?>
				
			</tr>
		</thead>
		<tbody>
			<? $res_type = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,SSN,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME,STUDENT_GROUP, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS,IF(STATUS_DATE = '0000-00-00','',DATE_FORMAT(STATUS_DATE,'%m/%d/%Y' )) AS STATUS_DATE
			FROM 
			S_STUDENT_MASTER $table , S_STUDENT_ENROLLMENT 
			LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
			LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
			LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
			LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
			
			WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 $cond $group_by ");

			while (!$res_type->EOF) { ?>
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
					<? } else { ?>
						<td>
							<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" onclick="show_btn()" />
						</td>
						<td><?=$res_type->fields['STU_NAME']?></td>
						<td><?=$res_type->fields['STUDENT_GROUP']?></td>
						<td><?=$res_type->fields['BEGIN_DATE']?></td>
						<td><?=$res_type->fields['CODE']?></td>
						<td><?=$res_type->fields['STUDENT_STATUS']?></td>
					<? } ?>
					
				</tr>
			<?	$res_type->MoveNext();
			} ?>
		</tbody>
	</table>
</div>