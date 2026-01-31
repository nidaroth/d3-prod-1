<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_ENROLLMENT = $_REQUEST['str'];

$cond = "";
if($_REQUEST['str1'] != '')
	$cond = " AND PK_STUDENT_ENROLLMENT NOT IN (".$_REQUEST['str1'].") ";

$res_type = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME,STUDENT_GROUP, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS
FROM 
S_STUDENT_MASTER $table , S_STUDENT_ENROLLMENT 
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 

WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $cond ");

$i = 0;
while (!$res_type->EOF) { ?>
	<tr id="stu_tr_<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" >
		<? if($_REQUEST['type'] == 'bulk_create_login'){ ?>
		<td><?=$res_type->fields['STU_NAME']?></td>
		<td><?=$res_type->fields['STUDENT_STATUS']?></td>
		<td><?=$res_type->fields['BEGIN_DATE']?></td>
		<td><?=$res_type->fields['CODE']?></td>
		<td><?=$res_type->fields['STUDENT_GROUP']?></td>
		<td>
			<input type="hidden" name="PK_STUDENT_ENROLLMENT_1[]" value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" >
			<input type="hidden" name="PK_STUDENT_MASTER_1[]" value="<?=$res_type->fields['PK_STUDENT_MASTER']?>" >
			
			<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>')" title="<?=REMOVE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
		</td>
		<? } else { ?>
		<td><?=$res_type->fields['STU_NAME']?></td>
		<td><?=$res_type->fields['STUDENT_GROUP']?></td>
		<td><?=$res_type->fields['BEGIN_DATE']?></td>
		<td><?=$res_type->fields['CODE']?></td>
		<td><?=$res_type->fields['STUDENT_STATUS']?></td>
		<td>
			<input type="hidden" name="PK_STUDENT_ENROLLMENT_1[]" value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" >
			<input type="hidden" name="PK_STUDENT_MASTER_1[]" value="<?=$res_type->fields['PK_STUDENT_MASTER']?>" >
			
			<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>')" title="<?=REMOVE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
		</td>
		<? } ?>
	</tr>
<?	$i++;
	$res_type->MoveNext();
} ?>
	