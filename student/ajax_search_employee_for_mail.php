<? require_once("../global/config.php"); 

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

if(!empty($_REQUEST['PK_DEPARTMENT']))
	$cond .= " AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT IN (".$_REQUEST['PK_DEPARTMENT'].") ";

$union = "";
if($_REQUEST['INTERNAL_ID'] != '' ) { 
	$cond1 = "";
	$res_rep = $db->Execute("select PK_USER from Z_INTERNAL_EMAIL_RECEPTION WHERE INTERNAL_ID = '$_REQUEST[INTERNAL_ID]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_rep->EOF) { 
		if($cond1 != '')
			$cond1 .= ',';
			
		$cond1 .= $res_rep->fields['PK_USER'];
		
		$res_rep->MoveNext();
	}
	
	$union = " UNION select CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, PK_USER FROM Z_USER, S_STUDENT_MASTER WHERE Z_USER.ACTIVE = '1' AND Z_USER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3  AND PK_USER IN ($cond1) ";
}
	
$res_stud = $db->Execute("SELECT * FROM (select CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, PK_USER     
FROM 
Z_USER, S_EMPLOYEE_MASTER 
LEFT JOIN S_EMPLOYEE_DEPARTMENT ON S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
WHERE 
Z_USER.ACTIVE = '1' AND Z_USER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE = 2 AND S_EMPLOYEE_MASTER.ACTIVE = 1 AND INTERNAL_MESSAGE_ENABLED = 1 $cond GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER  $union ) AS TEMP ORDER BY NAME ASC "); 
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
		} ?>
		<option value="<?=$PK_USER?>" <?=$selected?> ><?=$res_stud->fields['NAME'] ?></option>
	<?	$res_stud->MoveNext();
	} ?>	
</select>