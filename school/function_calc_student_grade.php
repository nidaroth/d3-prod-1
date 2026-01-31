<? 
require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
require_once("../school/check_access.php");

// DIAM-2264
function number_format_data($val, $precision = 0)
{

  $res = number_format($val, $precision,'.','');

  if ($res == null)
    return number_format(0, $precision);
  else if (strtolower($res) == inf)
    return number_format(0, $precision);
  else if (strtolower($res) == nan)
    return number_format(0, $precision);

  // var_dump($res);
  return $res;
}
// End DIAM-2264

function calc_stu_grade($POINTS_1,$PK_STUDENT_GRADE,$PK_STUDENT_COURSE,$PK_STUDENT_MASTER,$SAVE){
	global $db;
	
	$temp_cond = "";
	if($POINTS_1 == '')
		$temp_cond = " AND 0 = 1 ";
		
	$POINTS 		   = explode(",",$POINTS_1);
	$PK_STUDENT_GRADE1 = explode(",",$PK_STUDENT_GRADE);
	
	$i = 0;
	$TOTAL_POINTS = 0;
	foreach($POINTS as $POINT){
		$PK_STUDENT_GRADE2 = $PK_STUDENT_GRADE1[$i];
		$res = $db->Execute("SELECT WEIGHT FROM S_STUDENT_GRADE,S_COURSE_OFFERING_GRADE where PK_STUDENT_GRADE = '$PK_STUDENT_GRADE2' AND S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE "); 
		
		$TOTAL_POINTS += ($POINT * $res->fields['WEIGHT']);
		
		$i++;
	}
	if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
	$TOTAL_POINTS =  number_format_data($TOTAL_POINTS,2); // DIAM-1527
	}
	$res = $db->Execute("SELECT UNITS FROM S_COURSE, S_COURSE_OFFERING, S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	$COURSE_UNIT = $res->fields['UNITS'];
	
	$res = $db->Execute("SELECT S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM,PK_GRADE_SCALE_MASTER,PK_COURSE_OFFERING,FINAL_GRADE FROM S_STUDENT_COURSE, S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM "); 
	$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
	$PK_CAMPUS_PROGRAM 		= $res->fields['PK_CAMPUS_PROGRAM'];
	$PK_GRADE_SCALE_MASTER  = $res->fields['PK_GRADE_SCALE_MASTER'];
	$PK_COURSE_OFFERING  	= $res->fields['PK_COURSE_OFFERING'];
	$FINAL_GRADE  			= $res->fields['FINAL_GRADE'];
		
	$MAX_CURRENT_POINTS = 0;
	$res = $db->Execute("SELECT SUM(WEIGHTED_POINTS) as WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE,S_STUDENT_GRADE WHERE S_STUDENT_GRADE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GRADE IN ($PK_STUDENT_GRADE) AND S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE = S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE "); 
	$MAX_CURRENT_POINTS = $res->fields['WEIGHTED_POINTS'];
	if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
	$MAX_CURRENT_POINTS = number_format_data($res->fields['WEIGHTED_POINTS'],2);  // DIAM-1527
	}

	$MAX_FINAL_POINTS = 0;
	$res = $db->Execute("SELECT SUM(WEIGHTED_POINTS) as WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' "); 
	$MAX_FINAL_POINTS = $res->fields['WEIGHTED_POINTS'];
	if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
	$MAX_FINAL_POINTS = number_format_data($res->fields['WEIGHTED_POINTS'],2);  // DIAM-1527
	}

	if($MAX_CURRENT_POINTS > 0)
		$CURRENT_PERCENTAGE = number_format_data(($TOTAL_POINTS / $MAX_CURRENT_POINTS * 100),2);
	else
		$CURRENT_PERCENTAGE = 0;
		
	if($MAX_FINAL_POINTS > 0)
		$FINAL_PERCENTAGE = number_format_data(($TOTAL_POINTS / $MAX_FINAL_POINTS * 100),2);
	else
		$FINAL_PERCENTAGE = 0;

	$res = $db->Execute("SELECT PK_GRADE_SCALE_DETAIL,GRADE,S_GRADE.PK_GRADE FROM S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE WHERE S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' AND MAX_PERCENTAGE >= '$CURRENT_PERCENTAGE' AND MIN_PERCENTAGE <= '$CURRENT_PERCENTAGE' $temp_cond "); 

	$str = number_format_data($TOTAL_POINTS,2).'/'.number_format_data($MAX_CURRENT_POINTS,2).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format_data($CURRENT_PERCENTAGE,2).'%|||'.number_format_data($TOTAL_POINTS,2).'|||'.number_format_data($MAX_CURRENT_POINTS,2).'|||'.$res->fields['GRADE'].'|||'.$res->fields['PK_GRADE'];

	$res_1 = $db->Execute("SELECT PK_GRADE_SCALE_DETAIL,GRADE,S_GRADE.PK_GRADE FROM S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE  WHERE S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' AND MAX_PERCENTAGE >= '$FINAL_PERCENTAGE' AND MIN_PERCENTAGE <= '$FINAL_PERCENTAGE' $temp_cond "); 
	

	$str .= '|||'.number_format_data($TOTAL_POINTS,2).'/'.number_format_data($MAX_FINAL_POINTS,2).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format_data($FINAL_PERCENTAGE,2).'%|||'.number_format_data($MAX_FINAL_POINTS,2).'|||'.$res_1->fields['GRADE'].'|||'.$res_1->fields['PK_GRADE'].'|||'.$res->fields['PK_GRADE_SCALE_DETAIL'].'|||'.$res_1->fields['PK_GRADE_SCALE_DETAIL'];
	if($SAVE == 0)
		return $str;
	else if($SAVE == 1) {
		$STUDENT_COURSE = array();
		
		$STUDENT_COURSE['COURSE_UNITS'] 			= $COURSE_UNIT;
		$STUDENT_COURSE['FINAL_TOTAL_OBTAINED'] 	= $TOTAL_POINTS;
		$STUDENT_COURSE['FINAL_MAX_TOTAL'] 			= $MAX_FINAL_POINTS;
		$STUDENT_COURSE['FINAL_TOTAL_GRADE'] 		= $res_1->fields['PK_GRADE'];
		
		$STUDENT_COURSE['CURRENT_TOTAL_OBTAINED'] 	= $TOTAL_POINTS;
		$STUDENT_COURSE['CURRENT_MAX_TOTAL'] 		= $MAX_CURRENT_POINTS;
		$STUDENT_COURSE['CURRENT_TOTAL_GRADE'] 		= $res->fields['PK_GRADE'];
		
		$res_is_def = $db->Execute("SELECT IS_DEFAULT FROM S_GRADE WHERE PK_GRADE = '$FINAL_GRADE' "); //Ticket # 1900 
		if($FINAL_GRADE > 0 && $res_is_def->fields['IS_DEFAULT'] != 1) { //Ticket # 1900 
			$STUDENT_COURSE['FINAL_GRADE'] = $res_1->fields['PK_GRADE'];
			
			$PERCENTAGE = number_format_data(($STUDENT_COURSE['FINAL_TOTAL_OBTAINED'] / $STUDENT_COURSE['FINAL_MAX_TOTAL'] * 100),2);
			$PERCENTAGE = str_replace(",","",$PERCENTAGE);
			$STUDENT_COURSE['NUMERIC_GRADE'] = $PERCENTAGE;
			
			$flag = 0;
			$res_tc = $db->Execute("SELECT FINAL_GRADE FROM S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			$CUR_PK_GRADE = $res_tc->fields['FINAL_GRADE'];
			if($CUR_PK_GRADE != $STUDENT_COURSE['FINAL_GRADE']) {
				$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$STUDENT_COURSE[FINAL_GRADE]' "); 
				$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
				$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
				$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
				$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
				$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
				$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
				$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
				$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
				//echo "aaaa";exit;
				$flag = 1;
			}
		}
		db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update'," PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		/* Ticket #1034 */
		/*if($flag == 1) {
			$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 9");
			if($res_noti->RecordCount() > 0) {
				if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
					send_final_grade_posted_mail($PK_STUDENT_COURSE,$res_noti->fields['PK_EMAIL_TEMPLATE']);
				}
				
				if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
					send_final_grade_posted_text($PK_STUDENT_COURSE,$res_noti->fields['PK_TEXT_TEMPLATE']);
				}
			}
		}*/
		/* Ticket #1034 */
		
	} else if($SAVE == 2) {
		$STUDENT_COURSE = array();
		$STUDENT_COURSE['COURSE_UNITS'] 			= $COURSE_UNIT;
		$STUDENT_COURSE['FINAL_TOTAL_OBTAINED'] 	= $TOTAL_POINTS;
		$STUDENT_COURSE['FINAL_MAX_TOTAL'] 			= $MAX_FINAL_POINTS;
		$STUDENT_COURSE['FINAL_TOTAL_GRADE'] 		= $res_1->fields['PK_GRADE'];
		$STUDENT_COURSE['FINAL_GRADE'] 				= $res_1->fields['PK_GRADE'];
		
		$STUDENT_COURSE['CURRENT_TOTAL_OBTAINED'] 	= $TOTAL_POINTS;
		$STUDENT_COURSE['CURRENT_MAX_TOTAL'] 		= $MAX_CURRENT_POINTS;
		$STUDENT_COURSE['CURRENT_TOTAL_GRADE'] 		= $res->fields['PK_GRADE'];
		
		$PERCENTAGE = number_format_data(($STUDENT_COURSE['FINAL_TOTAL_OBTAINED'] / $STUDENT_COURSE['FINAL_MAX_TOTAL'] * 100),2);
		$PERCENTAGE = str_replace(",","",$PERCENTAGE);
		$STUDENT_COURSE['NUMERIC_GRADE'] = $PERCENTAGE;
		
		$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$STUDENT_COURSE[FINAL_GRADE]' "); 
		$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
		$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
		$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
		$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
		$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
		$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
		$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
		$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
		db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update'," PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
} ?>
