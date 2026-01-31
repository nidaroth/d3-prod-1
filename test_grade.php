<? require_once("global/config.php"); 
/*
$res_acc = $db->Execute("SELECT PK_STUDENT_CREDIT_TRANSFER,PK_GRADE FROM S_STUDENT_CREDIT_TRANSFER ");
while (!$res_acc->EOF) {
	$PK_STUDENT_CREDIT_TRANSFER = $res_acc->fields['PK_STUDENT_CREDIT_TRANSFER'];
	$PK_GRADE 					= $res_acc->fields['PK_GRADE'];

	$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' "); 
	$TRANSFER_CREDIT['GRADE'] 				= $res_grade_data->fields['GRADE'];
	$TRANSFER_CREDIT['NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
	$TRANSFER_CREDIT['CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
	$TRANSFER_CREDIT['UNITS_ATTEMPTED'] 	= $res_grade_data->fields['UNITS_ATTEMPTED'];
	$TRANSFER_CREDIT['UNITS_COMPLETED'] 	= $res_grade_data->fields['UNITS_COMPLETED'];
	$TRANSFER_CREDIT['UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
	$TRANSFER_CREDIT['WEIGHTED_GRADE_CALC'] = $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
	$TRANSFER_CREDIT['RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
	db_perform('S_STUDENT_CREDIT_TRANSFER', $TRANSFER_CREDIT, 'update'," PK_STUDENT_CREDIT_TRANSFER = '$PK_STUDENT_CREDIT_TRANSFER' ");
	
	$res_acc->MoveNext();
}


$res_acc = $db->Execute("SELECT PK_STUDENT_COURSE,FINAL_TOTAL_GRADE,FINAL_GRADE,PK_COURSE_OFFERING FROM S_STUDENT_COURSE ");
while (!$res_acc->EOF) {
	$PK_STUDENT_COURSE  = $res_acc->fields['PK_STUDENT_COURSE'];
	$PK_COURSE_OFFERING = $res_acc->fields['PK_COURSE_OFFERING'];
	$FINAL_TOTAL_GRADE  = $res_acc->fields['FINAL_TOTAL_GRADE'];
	$FINAL_GRADE	    = $res_acc->fields['FINAL_GRADE'];

	$res_type = $db->Execute("select UNITS from S_COURSE_OFFERING, S_COURSE WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE ");
	$S_STUDENT_COURSE['COURSE_UNITS'] = $res_type->fields['UNITS'];
	
	$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$FINAL_TOTAL_GRADE' "); 
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
	$S_STUDENT_COURSE['FINAL_TOTAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
	
	$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$FINAL_GRADE' "); 
	$S_STUDENT_COURSE['FINAL_GRADE_GRADE'] 					= $res_grade_data->fields['GRADE'];
	$S_STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 			= $res_grade_data->fields['NUMBER_GRADE'];
	$S_STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 			= $res_grade_data->fields['CALCULATE_GPA'];
	$S_STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
	$S_STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
	$S_STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 		= $res_grade_data->fields['UNITS_IN_PROGRESS'];
	$S_STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
	$S_STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 			= $res_grade_data->fields['RETAKE_UPDATE'];
	
	db_perform('S_STUDENT_COURSE', $S_STUDENT_COURSE, 'update'," PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");
	
	$res_acc->MoveNext();
}*/