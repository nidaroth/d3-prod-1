<? require_once("../global/config.php"); 

function get_details_for_fa_from_program($FA_PK_ENROLLMENT){
	global $db;
	
	$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$FA_PK_ENROLLMENT' "); 
	$PK_CAMPUS_PROGRAM	= $res->fields['PK_CAMPUS_PROGRAM'];

	$res_prog = $db->Execute("SELECT MONTHS,SUM(AMOUNT) AS AMOUNT FROM M_CAMPUS_PROGRAM LEFT JOIN M_CAMPUS_PROGRAM_FEE ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM_FEE.PK_CAMPUS_PROGRAM AND M_CAMPUS_PROGRAM_FEE.ACTIVE = 1 AND PK_FEE_TYPE = 2 AND PK_DEPENDENT_STATUS = 4 WHERE M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ");

	$data['PROGRAM_LENGTH']	= $res_prog->fields['MONTHS'];
	$data['PROGRAM_COST']	= $res_prog->fields['AMOUNT'];
	
	return $data;
}
//get_details_for_fa_from_program($_REQUEST['FA_PK_ENROLLMENT'],0);