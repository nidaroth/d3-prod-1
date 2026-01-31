<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/employee.php");
require_once("../language/menu.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond 		= "";
$group_by 	= "";
$table 		= "";

if(!empty($_REQUEST['PK_CAMPUS'])) {
	$cond .= " AND S_EMPLOYEE_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].") ";
}

if(!empty($_REQUEST['PK_DEPARTMENT'])) {
	$table = ", S_EMPLOYEE_DEPARTMENT ";
	$cond .= " AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT IN (".$_REQUEST['PK_DEPARTMENT'].") AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER  ";
}

if(!empty($_REQUEST['PK_SUPERVISOR'])) {
	$cond .= " AND S_EMPLOYEE_MASTER.PK_SUPERVISOR IN (".$_REQUEST['PK_SUPERVISOR'].")  ";
}

if($_REQUEST['FULL_PART_TIME'] != '')
	$cond .= " AND S_EMPLOYEE_MASTER.FULL_PART_TIME = '$_REQUEST[FULL_PART_TIME]' ";
	
if($_REQUEST['HAS_LOGIN'] == 1)
	$cond .= " AND S_EMPLOYEE_MASTER.LOGIN_CREATED = '1' ";
else if($_REQUEST['HAS_LOGIN'] == 2)
	$cond .= " AND S_EMPLOYEE_MASTER.LOGIN_CREATED = '0' ";
	
if($_REQUEST['INSTRUCTOR'] == 1)
	$cond .= " AND S_EMPLOYEE_MASTER.IS_FACULTY = '1' ";
else if($_REQUEST['INSTRUCTOR'] == 2)
	$cond .= " AND S_EMPLOYEE_MASTER.IS_FACULTY = '0' ";
	
if($_REQUEST['SCHOOL_ADMIN'] == 1)
	$cond .= " AND S_EMPLOYEE_MASTER.IS_ADMIN = '1' ";
else if($_REQUEST['SCHOOL_ADMIN'] == 2)
	$cond .= " AND S_EMPLOYEE_MASTER.IS_ADMIN = '0' ";
	
if($_REQUEST['ACTIVE'] == 1)
	$cond .= " AND S_EMPLOYEE_MASTER.ACTIVE = '1' ";
else if($_REQUEST['ACTIVE'] == 2)
	$cond .= " AND S_EMPLOYEE_MASTER.ACTIVE = '0' ";

$res_emp = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ', S_EMPLOYEE_MASTER.FIRST_NAME) AS EMP_NAME, CONCAT(S_EMPLOYEE_MASTER_SUP.LAST_NAME,', ', S_EMPLOYEE_MASTER_SUP.FIRST_NAME) AS SUP_NAME, S_EMPLOYEE_MASTER.TITLE, IF(S_EMPLOYEE_MASTER.FULL_PART_TIME = 1, 'Full Time', IF(S_EMPLOYEE_MASTER.FULL_PART_TIME = 2, 'Part Time', '') ) as FULL_PART_TIME_1, IF(S_EMPLOYEE_MASTER.LOGIN_CREATED = 1, 'Yes', 'No') as LOGIN_CREATED_1, IF(S_EMPLOYEE_MASTER.IS_FACULTY = 1, 'Yes', 'No') as IS_FACULTY_1, IF(S_EMPLOYEE_MASTER.IS_ADMIN = 1, 'Yes', 'No') as IS_ADMIN_1, IF(S_EMPLOYEE_MASTER.ACTIVE = 1, 'Yes', 'No') as ACTIVE_1
FROM 
S_EMPLOYEE_MASTER 
LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS 
LEFT JOIN S_EMPLOYEE_MASTER as S_EMPLOYEE_MASTER_SUP ON S_EMPLOYEE_MASTER_SUP.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_SUPERVISOR 
$table 
WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER ORDER BY CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ', S_EMPLOYEE_MASTER.FIRST_NAME) ASC"); 

?>
<div class="row">
	<div class="col-12 col-sm-4 form-group">
	<?=TOTAL_COUNT.': '.$res_emp->RecordCount() ?>
	<? if($_REQUEST['show_check'] == 1) { ?>
		<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
	<? } ?>
	</div>
</div>

<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<? } ?>
			<th><?=EMPLOYEE?></th>
			<th><?=CAMPUS_CODE?></th>
			<th><?=DEPARTMENT?></th>
			<th><?=SUPERVISOR?></th>
			<th><?=JOB_TITLE?></th>
			<th><?=FULL_PART_TIME?></th>
			<th><?=HAS_LOGIN?></th>
			<th><?=INSTRUCTOR?></th>
			<th><?=SCHOOL_ADMIN?></th>
			<th><?=ACTIVE?></th>
		</tr>
	</thead>
	<tbody>
	<? while (!$res_emp->EOF) { ?>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="PK_EMPLOYEE_MASTER[]" id="PK_EMPLOYEE_MASTER" value="<?=$res_emp->fields['PK_EMPLOYEE_MASTER']?>" <? if($_REQUEST['show_check'] == 1) { ?> onclick="get_count()" <? } ?> />
			</th>
			<? } ?>
			<td >
				<? if($_REQUEST['show_check'] != 1){ ?>
					<input type="hidden" name="PK_EMPLOYEE_MASTER[]" value="<?=$res_emp->fields['PK_EMPLOYEE_MASTER']?>" >
				<? } ?>
				<?=$res_emp->fields['EMP_NAME']?>
			</td>
			<td >
				<? $res_camp = $db->Execute("select GROUP_CONCAT(CAMPUS_CODE ORDER BY CAMPUS_CODE SEPARATOR ', ') as CAMPUS FROM S_EMPLOYEE_CAMPUS, S_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS  AND PK_EMPLOYEE_MASTER = '".$res_emp->fields['PK_EMPLOYEE_MASTER']."' ");
				echo $res_camp->fields['CAMPUS']?>
			</td>
			<td >
				<? $res_dep = $db->Execute("select GROUP_CONCAT(DEPARTMENT SEPARATOR ', ') as DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT  AND PK_EMPLOYEE_MASTER = '".$res_emp->fields['PK_EMPLOYEE_MASTER']."' ");
				echo $res_dep->fields['DEPARTMENT']?>
			</td>
			<td >
				<?=$res_emp->fields['SUP_NAME']?>
			</td>
			<td >
				<?=$res_emp->fields['TITLE']?>
			</td>
			<td >
				<?=$res_emp->fields['FULL_PART_TIME_1']?>
			</td>
			<td >
				<?=$res_emp->fields['LOGIN_CREATED_1']?>
			</td>
			<td >
				<?=$res_emp->fields['IS_FACULTY_1']?>
			</td>
			<td >
				<?=$res_emp->fields['IS_ADMIN_1']?>
			</td>
			<td >
				<?=$res_emp->fields['ACTIVE_1']?>
			</td>
		</tr>
		
	<?	$res_emp->MoveNext();
	} ?>
	</tbody>
</table>
	