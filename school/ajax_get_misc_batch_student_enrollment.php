<? require_once("../global/config.php"); 
$stud_id_222 	= $_REQUEST['stud_id']; 
$count1 		= $_REQUEST['count1']; ?>

<select id="BATCH_PK_STUDENT_ENROLLMENT_<?=$count1?>" name="BATCH_PK_STUDENT_ENROLLMENT[]" class="form-control required-entry" onchange="get_term(this.value,<?=$count1?>)"  >
	<option></option>
	<? $res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$stud_id_222' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res_type->EOF) { 
		$selected = ""; 
		if($_REQUEST['en_def_val'] != ''){
			if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $_REQUEST['en_def_val'])
				$selected = "selected"; 
		} else { 
			if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1)
				$selected = "selected"; 
		} ?>
		<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <?=$selected ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS']?></option>
	<?	$res_type->MoveNext();
	} ?>
</select>