<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond 		= "";
$group_by 	= "";
$table 		= "";

if(!empty($_REQUEST['PK_CAMPUS'])) {
	$table .= ",S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].") AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
}

if(!empty($_REQUEST['PK_STUDENT_STATUS']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".$_REQUEST['PK_STUDENT_STATUS'].") ";
	
if(!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (".$_REQUEST['PK_CAMPUS_PROGRAM'].") ";
	
if(!empty($_REQUEST['PK_TERM_MASTER']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN (".$_REQUEST['PK_TERM_MASTER'].") ";

if(!empty($_REQUEST['PK_COURSE_OFFERING'])) {
	$table .= ",S_STUDENT_COURSE ";
	$cond .= " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN (".$_REQUEST['PK_COURSE_OFFERING'].") ";
}

$res_stud = $db->Execute("select Z_USER.PK_USER, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT   
FROM 
Z_USER, S_STUDENT_MASTER, S_STUDENT_ACADEMICS  $table , S_STUDENT_ENROLLMENT 
WHERE Z_USER.ACTIVE = '1' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $cond GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC"); 
?>
<select name="RECEPTION[]" id="RECEPTION" class="form-control required-entry select2" style="width:95%" multiple >
	<option value="">Select</option>
	<? while (!$res_stud->EOF) { 
		$PK_USER = $res_stud->fields['PK_USER'];

		$selected = '';
		if($_REQUEST['INTERNAL_ID'] != '' ) { 
			$res_rep = $db->Execute("select PK_INTERNAL_EMAIL_RECEPTION from Z_INTERNAL_EMAIL_RECEPTION WHERE INTERNAL_ID = '$_REQUEST[INTERNAL_ID]' AND PK_USER = '$PK_USER' AND (PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR PK_ACCOUNT = 1)");
			if($res_rep->RecordCount() > 0)
				$selected = 'selected';
		} 
		
		if($_REQUEST['PK_STUDENT_MASTER'] == $res_stud->fields['PK_STUDENT_MASTER']) { 
			$selected = 'selected';
		} ?>
		<option value="<?=$PK_USER?>" <?=$selected?> ><?=$res_stud->fields['STU_NAME'] ?></option>
	<?	$res_stud->MoveNext();
	} ?>	
</select>