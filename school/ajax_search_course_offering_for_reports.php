<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");

$cond  = "";
if(!empty($_REQUEST['PK_CAMPUS']))
	$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].") ";

if(!empty($_REQUEST['PK_TERM_MASTER']))
	$cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER IN (".$_REQUEST['PK_TERM_MASTER'].") ";
	
if(!empty($_REQUEST['PK_COURSE']))
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE IN (".$_REQUEST['PK_COURSE'].") ";

if(!empty($_REQUEST['PK_COURSE_OFFERING']))
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN (".$_REQUEST['PK_COURSE_OFFERING'].") ";	
	
if(!empty($_REQUEST['PK_COURSE_OFFERING_STATUS']))
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS IN (".$_REQUEST['PK_COURSE_OFFERING_STATUS'].") ";		
	
if(!empty($_REQUEST['PK_SESSION']))
	$cond .= " AND S_COURSE_OFFERING.PK_SESSION IN (".$_REQUEST['PK_SESSION'].") ";	
	
if($_REQUEST['SESSION_NO'] != '') {
	$cond .= " AND SESSION_NO = '$_REQUEST[SESSION_NO]' ) ";
}

$res_co = $db->Execute("SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE, LMS_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, CAMPUS_CODE ,CONCAT(EMP_INSTRUCTOR.LAST_NAME,', ',EMP_INSTRUCTOR.FIRST_NAME) AS INSTRUCTOR_NAME,SESSION,SESSION_NO, ROOM_NO, S_COURSE_OFFERING.ROOM_SIZE, S_COURSE_OFFERING.CLASS_SIZE, COURSE_OFFERING_STATUS, S_COURSE_OFFERING.INSTRUCTOR, S_COURSE_OFFERING.PK_CAMPUS, TRANSCRIPT_CODE 
FROM 
S_COURSE_OFFERING 
LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM  
LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR 
WHERE 
S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond 
ORDER BY CAMPUS_CODE ASC, BEGIN_DATE ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC  "); 

?>
<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<? } ?>
			<th><?=CAMPUS ?></th>
			<th><?=TERM?></th>
			
			<th><?=COURSE?></th>
			<th><?=SESSION?></th>
			<th><?=SESSION_NO?></th>
			<th><?=INSTRUCTOR?></th>
			
			<th><?=COURSE_OFFERING_STATUS_1?></th>
			<th><?=NO_OF_STUDENT?></th>
			<th>
				<?=TOTAL_COUNT.': '.$res_co->RecordCount() ?>
				<? if($_REQUEST['show_check'] == 1) { ?>
				<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
				<? } ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<? while (!$res_co->EOF) { ?>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="SEL_PK_COURSE_OFFERING[]" id="SEL_PK_COURSE_OFFERING" value="<?=$res_co->fields['PK_COURSE_OFFERING']?>" <? if($_REQUEST['show_check'] == 1 ) { ?> onclick="get_count()" <? } ?> />
			</th>
			<? } ?>
			<td >
				<? if($_REQUEST['show_check'] != 1){ ?>
				<input type="hidden" name="SEL_PK_COURSE_OFFERING[]" value="<?=$res_co->fields['PK_COURSE_OFFERING']?>" >
				<? } ?>
				
				<?=$res_co->fields['CAMPUS_CODE']?>
			</td>
			<td  > 
				<?=$res_co->fields['TERM_BEGIN_DATE']?>
			</td>
			
			<td  > 
				<?=$res_co->fields['COURSE_CODE']?>
			</td>
			<td  > 
				<?=$res_co->fields['SESSION']?>
			</td>
			<td  > 
				<?=$res_co->fields['SESSION_NO']?>
			</td>
			<td  > 
				<?=$res_co->fields['INSTRUCTOR_NAME']?>
			</td>
			<td  > 
				<?=$res_co->fields['COURSE_OFFERING_STATUS']?>
			</td>
			<td colspan="2"  >
				<? $PK_COURSE_OFFERING = $res_co->fields['PK_COURSE_OFFERING'];
				$res_co_count = $db->Execute("SELECT COUNT(PK_STUDENT_COURSE) AS NO_STUD FROM S_STUDENT_COURSE, S_GRADE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE FINAL_GRADE = PK_GRADE AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND UNITS_IN_PROGRESS = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ");
				echo $res_co_count->fields['NO_STUD']; ?>
			</td>
		</tr>
		
	<?	$res_co->MoveNext();
	} ?>
	</tbody>
</table>