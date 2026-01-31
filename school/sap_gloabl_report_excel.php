<?
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/sap_scale.php");
require_once("check_access.php");

if (check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0 && $_SESSION['PK_ROLES'] != 3) {
	header("location:../index");
	exit;
}

$Get_Stud_Enrollment = $_POST['PK_STUDENT_ENROLLMENT'];
$Get_Stud_Master     = $_POST['PK_STUDENT_MASTER'];

include '../global/excel/Classes/PHPExcel/IOFactory.php';

$dir             = 'temp/';
$inputFileType  = 'Excel2007';
$file_name      = 'SAP Global Report.xlsx';
$outputFileName = $dir . $file_name;
$outputFileName = str_replace(
	pathinfo($outputFileName, PATHINFO_FILENAME),
	pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
	$outputFileName
);

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$total_fields = 120;
for ($i = 0; $i <= $total_fields; $i++) {
	if ($i <= 25)
		$cell[] = $cell1[$i];
	else {
		$j = floor($i / 26) - 1;
		$k = ($i % 26);
		//echo $j."--".$k."<br />";
		$cell[] = $cell1[$j] . $cell1[$k];
	}
}


$line = 1;
$index  = -1;
$heading[] = 'Last Name';
$width[]   = 20;
$heading[] = 'First Name';
$width[]   = 20;
$heading[] = 'Student ID';
$width[]   = 20;
$heading[] = 'Campus';
$width[]   = 20;
$heading[] = 'First Term';
// $width[]   = 20;
// $heading[] = 'End Term';
$width[]   = 20;
$heading[] = 'Program';
$width[]   = 20;
$heading[] = 'Course Code';
$width[]   = 20;
$heading[] = 'Session';
$width[]   = 20;
// $heading[] = 'Evaluation Period';
// $width[]   = 20;
$heading[] = 'Credits Attempted';
$width[]   = 20;
$heading[] = 'Credits Completed';
$width[]   = 20;
$heading[] = 'GPA';
$width[]   = 20;
$heading[] = 'Status';
$width[]   = 20;

/** DIAM - 862, SAP Pending List **/
$heading[] = 'Student Group';
$width[]   = 20;
$heading[] = 'Mid Point Date';
$width[]   = 20;
$heading[] = 'SAP Scale';
$width[]   = 20;
$heading[] = 'Program Pace';
$width[]   = 20;
$heading[] = 'Period';
$width[]   = 20;
$heading[] = 'Program Pace Percentage';
$width[]   = 20;

$heading[] = 'Hours Completed/Hours Scheduled';
$width[]   = 20;
$heading[] = 'Hours Completed/Program Hours';
$width[]   = 20;
$heading[] = 'Hours Scheduled/Program Hours';
$width[]   = 20;
$heading[] = 'Include Transfers (Hours)';
$width[]   = 20;

$heading[] = 'FA Units Completed/FA Units Attempted';
$width[]   = 20;
$heading[] = 'FA Units Completed/Program FA Units';
$width[]   = 20;
$heading[] = 'FA Units Attempted/Program FA Units';
$width[]   = 20;
$heading[] = 'Include Transfers (FA Units)';
$width[]   = 20;

$heading[] = 'Units Completed/Units Attempted';
$width[]   = 20;
$heading[] = 'Units Completed/Program Units';
$width[]   = 20;
$heading[] = 'Units Attempted/Program Units';
$width[]   = 20;
$heading[] = 'Include Transfers (Units)';
$width[]   = 20;

$heading[] = 'Cumulative GPA';
$width[]   = 20;
$heading[] = 'Include Transfers (GPA)';
$width[]   = 20;

$heading[] = 'SAP Status';
$width[]   = 20;
$heading[] = 'SAP Warning';
$width[]   = 20;

$heading[] = 'Hours Completed';
$width[]   = 20;
$heading[] = 'Hours Scheduled';
$width[]   = 20;
$heading[] = 'Hours Program';
$width[]   = 20;
$heading[] = 'Hours Completed/Scheduled';
$width[]   = 20;
$heading[] = 'Hours Completed/Program';
$width[]   = 20;
$heading[] = 'Hours Scheduled/Program';
$width[]   = 20;

$heading[] = 'FA Units Completed';
$width[]   = 20;
$heading[] = 'FA Units Attempted';
$width[]   = 20;
$heading[] = 'Program FA Units';
$width[]   = 20;
$heading[] = 'FA Units Completed/Attempted';
$width[]   = 20;
$heading[] = 'FA Units Completed/Program';
$width[]   = 20;
$heading[] = 'FA Units Attempted/Program';
$width[]   = 20;

$heading[] = 'Units Completed';
$width[]   = 20;
$heading[] = 'Hours Attempted';
$width[]   = 20;
$heading[] = 'Program Units';
$width[]   = 20;
$heading[] = 'Units Completed/Attempted';
$width[]   = 20;
$heading[] = 'Units Completed/Program';
$width[]   = 20;
$heading[] = 'Units Attempted/Program';
$width[]   = 20;

$heading[] = 'Cumulative GPA';
$width[]   = 20;
/** End DIAM - 862, SAP Pending List **/

$i = 0;
foreach ($heading as $title) {
	$index++;
	$cell_no = $cell[$index] . $line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);

	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getAlignment()->setWrapText(true);

	$i++;
}

function get_official_student_transcript($PK_STUDENT_MASTER, $en_cond, $aPK_SAP_SCALE, $PK_STUDENT_ENR)
{

	global $db;
	$attendance_data = '1';
	// SAP
	$res_sap_scale_setup = $db->Execute("SELECT * FROM S_SAP_SCALE_SETUP WHERE PK_SAP_SCALE = '$aPK_SAP_SCALE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ");
	//print_r($res_sap_scale_setup->fields);exit;
	$PK_SAP_SCALE                 =  $res_sap_scale_setup->fields['PK_SAP_SCALE'];
	$PK_PROGRAM_PACE              =  $res_sap_scale_setup->fields['PK_PROGRAM_PACE'];
	/***************************************************/
	$HOURS_COMPLETED_SCHEDULED 	  =  $res_sap_scale_setup->fields['HOURS_COMPLETED_SCHEDULED'];

	$hours_comp  = false;
	$hours_sched = false;
	$program_hours = false;
	$hours_completed_scheduled_per = false;
	$hours_completed_program_per = false;
	$hours_scheduled_program_per = false;
	if ($HOURS_COMPLETED_SCHEDULED == '1') {
		$hours_comp  = true;
		$hours_sched = true;
		$hours_completed_scheduled_per = true;
	}
	$HOURS_COMPLETED_PROGRAM   	  =  $res_sap_scale_setup->fields['HOURS_COMPLETED_PROGRAM'];
	if ($HOURS_COMPLETED_PROGRAM == '1') {
		$hours_comp    = true;
		$program_hours = true;
		$hours_completed_program_per = true;
	}
	$HOURS_SCHEDULED_PROGRAM   	  =  $res_sap_scale_setup->fields['HOURS_SCHEDULED_PROGRAM'];
	if ($HOURS_SCHEDULED_PROGRAM == '1') {
		$hours_sched   = true;
		$program_hours = true;
		$hours_scheduled_program_per = true;
	}
	/***************************************************/
	$FA_UNITS_COMPLETED_ATTEMPTED =  $res_sap_scale_setup->fields['FA_UNITS_COMPLETED_ATTEMPTED'];

	$fa_units_comp   = false;
	$fa_units_attemp = false;
	$program_fa_units = false;
	$fa_units_comp_attemp_per = false;
	$fa_units_comp_program_per = false;
	$fa_units_attemp_program_per = false;
	if ($FA_UNITS_COMPLETED_ATTEMPTED == '1') {
		$fa_units_comp   = true;
		$fa_units_attemp = true;
		$fa_units_comp_attemp_per = true;
	}
	$FA_UNITS_COMPLETED_PROGRAM   =  $res_sap_scale_setup->fields['FA_UNITS_COMPLETED_PROGRAM'];
	if ($FA_UNITS_COMPLETED_PROGRAM == '1') {
		$fa_units_comp    = true;
		$program_fa_units = true;
		$fa_units_comp_program_per = true;
	}
	$FA_UNITS_ATTEMPTED_PROGRAM   =  $res_sap_scale_setup->fields['FA_UNITS_ATTEMPTED_PROGRAM'];
	if ($FA_UNITS_ATTEMPTED_PROGRAM == '1') {
		$fa_units_attemp  = true;
		$program_fa_units = true;
		$fa_units_attemp_program_per = true;
	}
	/***************************************************/
	$UNITS_COMPLETED_ATTEMPTED    =  $res_sap_scale_setup->fields['UNITS_COMPLETED_ATTEMPTED'];

	$units_comp   = false;
	$units_attemp = false;
	$program_units = false;
	$units_comp_attemp_per = false;
	$units_comp_program_per = false;
	$units_attemp_program_per = false;
	if ($UNITS_COMPLETED_ATTEMPTED == '1') {
		$units_comp   = true;
		$units_attemp = true;
		$units_comp_attemp_per = true;
	}
	$UNITS_COMPLETED_PROGRAM      =  $res_sap_scale_setup->fields['UNITS_COMPLETED_PROGRAM'];
	if ($UNITS_COMPLETED_PROGRAM == '1') {
		$units_comp    = true;
		$program_units = true;
		$units_comp_program_per = true;
	}
	$UNITS_ATTEMPTED_PROGRAM      =  $res_sap_scale_setup->fields['UNITS_ATTEMPTED_PROGRAM'];
	if ($UNITS_ATTEMPTED_PROGRAM == '1') {
		$units_attemp  = true;
		$program_units = true;
		$units_attemp_program_per = true;
	}
	/***************************************************/
	$CUMULATIVE_GPA               =  $res_sap_scale_setup->fields['CUMULATIVE_GPA'];
	/***************************************************/
	$hours_include_transfer     = false;
	$units_include_transfer     = false;
	$fa_units_include_transfer  = false;
	$gpa_include_transfer       = false;
	$PERIOD_HOURS_COMPLETED_SCHEDULED               =  $res_sap_scale_setup->fields['PERIOD_HOURS_COMPLETED_SCHEDULED'];
	if ($PERIOD_HOURS_COMPLETED_SCHEDULED == '1') {
		$hours_include_transfer  = true;
		$hours_comp    = true;
		$hours_sched   = true;
		$program_hours = true;
	}
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED      =  $res_sap_scale_setup->fields['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED'];
	if ($PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED == '1') {
		$units_include_transfer  = true;
		$units_comp    = true;
		$units_attemp  = true;
		$program_units = true;
	}
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED            =  $res_sap_scale_setup->fields['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'];
	if ($PERIOD_FA_UNITS_COMPLETED_ATTEMPTED == '1') {
		$fa_units_include_transfer  = true;
		$fa_units_comp    = true;
		$fa_units_attemp  = true;
		$program_fa_units = true;
	}
	$PERIOD_GPA      =   $res_sap_scale_setup->fields['PERIOD_GPA'];
	if ($PERIOD_GPA == '1') {
		$gpa_include_transfer  = true;
	}
	/***************************************************/
	// End SAP

	$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$total_cum_gpa = 0;
	$total_units_att = '';
	$total_units_compt = '';
	$total_fa_units_att = '';
	$total_fa_units_compt = '';
	$total_prog_unit_tot = '';
	$total_prog_fa_unit_tot = '';
	$total_prog_hour_tot = '';
	$total_prog_comp_hour_tot = '';
	$total_sched_hour_tot = '';


	$total_cum_rec		= 0;
	$c_in_num_grade_tot = 0;
	$c_in_att_tot 		= 0;
	$c_fa_in_att_tot 	= 0;
	$c_in_comp_tot 		= 0;
	$c_fa_in_comp_tot 	= 0;
	$c_in_cu_gnu 		= 0;
	$c_in_gpa_tot 		= 0;

	$Denominator = 0;
	$Numerator 	 = 0;
	$Numerator1  = 0;

	#Initiating cummulative variables -av
	$c_in_prog_hour_tot      = 0;
	$c_in_prog_unit_tot      = 0;
	$c_in_num_grade_sub_tot  = 0;
	$c_in_att_sub_tot 		 = 0;
	$c_in_comp_sub_tot 		 = 0;
	$c_fa_in_comp_sub_tot 	 = 0;
	$c_in_cu_sub_gnu 		 = 0;
	$c_in_gpa_sub_tot 		 = 0;
	$c_fa_in_att_sub_tot 	 = 0;
	$c_in_prog_unit_sub_tot  = 0;
	$c_in_prog_fa_unit_tot   = 0;
	$c_in_prog_fa_unit_sub_tot = 0;
	$c_in_prog_comp_hour_tot = 0;
	$c_in_prog_comp_hour_sub_tot = 0;
	$c_in_sched_hour_tot 	 = 0;
	$c_in_sched_hour_sub_tot = 0;
	$c_in_prog_hour_sub_tot  = 0;
	$gpa_credit_units        = 0;
	$new_total_cum_gpa = 0;

	// DIAM-787, new calulated gpa
	$tc_gpa_value_total    = 0;
	$tc_gpa_weight_total   = 0;
	$total_tc_gpa_weighted = 0;
	$summation_of_gpa   = 0;
	$summation_of_weight   = 0;
	// End DIAM-787, new calulated gpa

	// Term: Transfer -  (Transfer Credit)
	if ($hours_include_transfer || $units_include_transfer || $fa_units_include_transfer || $gpa_include_transfer) {
		if ($_GET['exclude_tc'] != 1  || $attendance_data == '1') {
			$Sub_Denominator = 0;
			$Sub_Numerator 	 = 0;
			$Sub_Numerator1  = 0;

			$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, NUMBER_GRADE,S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS 
				FROM S_STUDENT_CREDIT_TRANSFER 
				LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
				LEFT JOIN M_CAMPUS_PROGRAM_COURSE ON M_CAMPUS_PROGRAM_COURSE.PK_COURSE = S_COURSE.PK_COURSE
				WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS_MASTER = '1' AND M_CAMPUS_PROGRAM_COURSE.GRADE_INCLUDE_IN_SAP='1' AND CALCULATE_GPA = 1 AND SHOW_ON_TRANSCRIPT = 1 $en_cond ");
			while (!$res_tc->EOF) {

				$PK_GRADE				= $res_tc->fields['PK_GRADE'];
				$res_grade_top = $db->Execute("SELECT NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' ");

				$Denominator += $res_tc->fields['UNITS'];
				$Numerator1	 += $res_tc->fields['UNITS'] * $res_grade_top->fields['NUMBER_GRADE'];

				$Sub_Denominator += $res_tc->fields['UNITS'];
				$Sub_Numerator1	 += $res_tc->fields['UNITS'] * $res_grade_top->fields['NUMBER_GRADE'];

				$res_tc->MoveNext();
			}

			$res_tc = $db->Execute("SELECT CREDIT_TRANSFER_STATUS, 
									S_COURSE.TRANSCRIPT_CODE,
									S_COURSE.COURSE_DESCRIPTION, 
									S_COURSE.PK_COURSE,
									S_STUDENT_CREDIT_TRANSFER.COURSE_DESCRIPTION AS COUR_DESC, 
									S_STUDENT_CREDIT_TRANSFER.UNITS, 
									S_STUDENT_CREDIT_TRANSFER.FA_UNITS, 
									S_STUDENT_CREDIT_TRANSFER.HOUR AS COMPLETED_HOURS,
									S_COURSE.HOURS AS PROGRAM_HOURS, 
									S_COURSE.UNITS AS PROGRAM_UNITS, 
									S_COURSE.FA_UNITS AS PROGRAM_FA_UNITS,
									TC_NUMERIC_GRADE, 
									S_GRADE.GRADE, 
									PK_STUDENT_ENROLLMENT, 
									S_STUDENT_CREDIT_TRANSFER.PK_GRADE, 
									S_GRADE.NUMBER_GRADE, 
									S_GRADE.CALCULATE_GPA, 
									S_GRADE.UNITS_ATTEMPTED, 
									S_GRADE.UNITS_COMPLETED, 
									S_GRADE.UNITS_IN_PROGRESS,
									CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
									CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
								FROM 
									S_STUDENT_CREDIT_TRANSFER 
									LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE
									LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
									LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
									LEFT JOIN M_CAMPUS_PROGRAM_COURSE ON M_CAMPUS_PROGRAM_COURSE.PK_COURSE = S_COURSE.PK_COURSE 
									WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS_MASTER = '1' AND M_CAMPUS_PROGRAM_COURSE.GRADE_INCLUDE_IN_SAP='1' AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER BY S_COURSE.TRANSCRIPT_CODE ASC");

			$total_rec				= 0;

			while (!$res_tc->EOF) {

				$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT'];
				$PK_GRADE				= $res_tc->fields['PK_GRADE'];
				$PK_COURSE     			= $res_tc->fields['PK_COURSE'];


				$COMPLETED_UNITS	 = 0;
				$ATTEMPTED_UNITS	 = 0;
				$PROGRAM_UNITS       = 0;

				$FA_ATTEMPTED_UNITS	 = 0;
				$FA_COMPLETED_UNITS	 = 0;
				$PROGRAM_FA_UNITS    = 0;

				$COMPLETED_HOURS     = 0;
				$SCHEDULE_HOURS      = 0;
				$PROGRAM_HOURS       = 0;

				$res_grade_data = $db->Execute("SELECT UNITS_ATTEMPTED,UNITS_COMPLETED,CALCULATE_GPA,NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' ");
				if ($res_grade_data->fields['UNITS_ATTEMPTED'] == 1) {
					if ($units_include_transfer) {
						//$ATTEMPTED_UNITS 	 = $res_tc->fields['UNITS'];
					}
					if ($fa_units_include_transfer) {
						//$FA_ATTEMPTED_UNITS	 = $res_tc->fields['FA_UNITS'];
					}
				}

				$c_in_att_tot 		+= $ATTEMPTED_UNITS;
				$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS;

				$c_fa_in_att_tot 		+= $FA_ATTEMPTED_UNITS;
				$c_fa_in_att_sub_tot 	+= $FA_ATTEMPTED_UNITS;

				if ($res_grade_data->fields['UNITS_COMPLETED'] == 1) {
					if ($units_include_transfer) {
						$COMPLETED_UNITS 	 = $res_tc->fields['UNITS'];
					}
					if ($fa_units_include_transfer) {
						$FA_COMPLETED_UNITS	 = $res_tc->fields['FA_UNITS'];
					}
				}

				$c_in_comp_tot  	+= $COMPLETED_UNITS;
				$c_in_comp_sub_tot  += $COMPLETED_UNITS;

				$c_fa_in_comp_tot  		+= $FA_COMPLETED_UNITS;
				$c_fa_in_comp_sub_tot  	+= $FA_COMPLETED_UNITS;
				if ($units_include_transfer) {
					$PROGRAM_UNITS = 0; //$res_tc->fields['PROGRAM_UNITS'];
				}
				$c_in_prog_unit_tot     += $PROGRAM_UNITS;
				$c_in_prog_unit_sub_tot += $PROGRAM_UNITS;

				if ($fa_units_include_transfer) {
					$PROGRAM_FA_UNITS = 0; //$res_tc->fields['PROGRAM_FA_UNITS'];
				}
				$c_in_prog_fa_unit_tot 	    += $PROGRAM_FA_UNITS;
				$c_in_prog_fa_unit_sub_tot 	+= $PROGRAM_FA_UNITS;

				if ($hours_include_transfer) {
					$COMPLETED_HOURS = $res_tc->fields['COMPLETED_HOURS'];
				}
				$c_in_prog_comp_hour_tot 	 += $COMPLETED_HOURS;
				$c_in_prog_comp_hour_sub_tot += $COMPLETED_HOURS;

				if ($hours_include_transfer) {
					$SCHEDULE_HOURS  = 0;
				}
				$c_in_sched_hour_tot 	    += $SCHEDULE_HOURS;
				$c_in_sched_hour_sub_tot 	+= $SCHEDULE_HOURS;

				if ($hours_include_transfer) {
					$PROGRAM_HOURS   = 0; //$res_tc->fields['PROGRAM_HOURS'];
				}
				$c_in_prog_hour_tot 	    += $PROGRAM_HOURS;
				$c_in_prog_hour_sub_tot 	+= $PROGRAM_HOURS;



				$gnu = 0;
				if ($res_grade_data->fields['CALCULATE_GPA'] == 1) {
					$gnu 				 = $res_tc->fields['UNITS'] * $res_grade_data->fields['NUMBER_GRADE'];
					$c_in_cu_gnu 		+= $gnu;
					$c_in_cu_sub_gnu 	+= $gnu;

					$gpa				= $gnu / $COMPLETED_UNITS;;
					$c_in_gpa_sub_tot 	+= $gpa;
					$c_in_gpa_tot 		+= $gpa;

					$c_in_num_grade_sub_tot	+= $res_tc->fields['TC_NUMERIC_GRADE'];
					$c_in_num_grade_tot		+= $res_tc->fields['TC_NUMERIC_GRADE'];

					// DIAM-787, new calulated gpa
					$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE'];
					$tc_gpa_value_total 		+= $TC_GPA_VALULE;
					$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT'];
					$tc_gpa_weight_total 		+= $TC_GPA_WEIGHT;
					// End DIAM-787, new calulated gpa

					$total_rec++;
					$total_cum_rec++;
				}

				// DIAM-787, new calulated gpa
				$tc_gpa_weighted = 0;
				if ($tc_gpa_value_total > 0) {
					$tc_gpa_weighted        = ($tc_gpa_value_total / $tc_gpa_weight_total);
				}
				$summation_of_gpa     += number_format_value_checker($TC_GPA_VALULE, 2);
				$summation_of_weight  += number_format_value_checker($TC_GPA_WEIGHT, 2);
				// End DIAM-787, new calulated gpa

				$Data_Grade = $res_tc->fields['GRADE'];
				if ($Data_Grade != '-' || 1 == 1) { // dvb
					$my_values = $tc_gpa_weighted; //($total_tc_gpa_weighted/$total_cum_rec);
					$total_cum_gpa = $my_values;
					$total_units_att = $c_in_att_tot;
					$total_units_compt = $c_in_comp_tot;
					$total_fa_units_att = $c_fa_in_att_tot;
					$total_fa_units_compt = $c_fa_in_comp_tot;
					$total_prog_unit_tot = $c_in_prog_unit_tot;
					$total_prog_fa_unit_tot = $c_in_prog_fa_unit_tot;

					$total_prog_comp_hour_tot = $c_in_prog_comp_hour_tot;
					$total_sched_hour_tot = $c_in_sched_hour_tot;
					$total_prog_hour_tot = $c_in_prog_hour_tot;
				}

				if ($gpa_include_transfer) {
					$gpa_credit_units = ($tc_gpa_value_total / $tc_gpa_weight_total);
				}

				$res_tc->MoveNext();
			}
		}
	}
	// End Term: Transfer -  (Transfer Credit)

	##END OF TRANSFER TERM BLOCK 

	#START : FOR NORMAL TERMS 
	$res_term = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE 
		FROM S_STUDENT_COURSE 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, M_COURSE_OFFERING_STUDENT_STATUS 
		WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER By BEGIN_DATE_1 ASC");
	while (!$res_term->EOF) {
		$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];
		$BEGIN_DATE 	= $res_term->fields['BEGIN_DATE'];

		$total_rec				= 0;

		$Sub_Denominator = 0;
		$Sub_Numerator 	 = 0;
		$Sub_Numerator1  = 0;

		// DIAM-787, new calulated gpa	
		$gpa_value_total  = 0;
		$gpa_weight_total = 0;
		// End DIAM-787, new calulated gpa

		$res_course = $db->Execute("SELECT NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE 
            from S_STUDENT_COURSE 
            LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
            LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE ,M_COURSE_OFFERING_STUDENT_STATUS, S_GRADE 
            WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 AND  S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE $en_cond AND CALCULATE_GPA = 1 ");
		while (!$res_course->EOF) {
			$Denominator += $res_course->fields['COURSE_UNITS'];
			$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
			$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

			$Sub_Denominator += $res_course->fields['COURSE_UNITS'];
			$Sub_Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
			$Sub_Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

			$res_course->MoveNext();
		}

		$res_course = $db->Execute("SELECT 
                                            COURSE_DESCRIPTION, 
                                            S_COURSE.FA_UNITS, 
                                            S_STUDENT_COURSE.PK_COURSE_OFFERING, 
                                            FINAL_GRADE, 
                                            S_GRADE.GRADE, 
                                            NUMERIC_GRADE, 
                                            S_GRADE.NUMBER_GRADE, 
											S_GRADE.CALCULATE_GPA, 
											S_GRADE.UNITS_ATTEMPTED, 
											S_GRADE.UNITS_COMPLETED, 
											S_GRADE.UNITS_IN_PROGRESS, 
                                            S_STUDENT_COURSE.COURSE_UNITS AS COURSE_UNITS, 
                                            S_COURSE.HOURS AS PROGRAM_HOURS, 
                                            S_COURSE.UNITS AS PROGRAM_UNITS, 
                                            S_COURSE.FA_UNITS AS PROGRAM_FA_UNITS, 
                                            M_CAMPUS_PROGRAM.UNITS AS SAP_PROGRAM_UNITS, 
                                            M_CAMPUS_PROGRAM.FA_UNITS AS SAP_PROGRAM_FA_UNITS, 
                                            M_CAMPUS_PROGRAM.HOURS AS SAP_PROGRAM_HOURS, 
                                            S_STUDENT_MASTER.PK_STUDENT_MASTER, 
                                            S_STUDENT_MASTER.LAST_NAME AS LAST_NAME, 
                                            S_STUDENT_MASTER.FIRST_NAME AS FIRST_NAME, 
											S_STUDENT_ACADEMICS.STUDENT_ID AS STUDENT_ID,
                                            S_CAMPUS.CAMPUS_CODE AS CAMPUS_CODE, 
                                            STUDENT_GROUP, 
											S_STUDENT_ENROLLMENT.MIDPOINT_DATE AS MIDPOINT_DATE,
                                            M_CAMPUS_PROGRAM.CODE AS PROGRAM_CODE, 
                                            IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d')) AS BEGIN_DATE_1, 
                                            IF(S_TERM_MASTER.END_DATE = '0000-00-00', '', DATE_FORMAT(S_TERM_MASTER.END_DATE, '%Y-%m-%d')) AS END_DATE_1, 
                                            S_COURSE.COURSE_CODE AS COURSE_CODE, 
                                            S_COURSE.TRANSCRIPT_CODE AS TRANSCRIPT_CODE, 
                                            M_SESSION.SESSION AS SESSION_NAME,
											CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
											CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
                                        FROM 
                                            S_STUDENT_COURSE 
                                            LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
                                            LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
                                            LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
                                            LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT 
                                            LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP
                                            LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
                                            LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
                                            LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
											LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
                                            LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
                                            LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
                                            LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION,
                                            M_COURSE_OFFERING_STUDENT_STATUS 
                                        WHERE 
                                            S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
                                            AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' 
                                            AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
                                            AND SHOW_ON_TRANSCRIPT = 1 
                                            AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENR' 
                                            AND M_CAMPUS_PROGRAM.PK_SAP_SCALE = '$aPK_SAP_SCALE' 
                                        ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC ");

		// dds($res_course);
			$return=array();
		while (!$res_course->EOF) {

			$SAP_PROGRAM_UNITS    = $res_course->fields['SAP_PROGRAM_UNITS'];
			$SAP_PROGRAM_FA_UNITS = $res_course->fields['SAP_PROGRAM_FA_UNITS'];
			$SAP_PROGRAM_HOURS    = $res_course->fields['SAP_PROGRAM_HOURS'];

			$LAST_NAME       = $res_course->fields['LAST_NAME'];
			$FIRST_NAME      = $res_course->fields['FIRST_NAME'];
			$STUDENT_ID      = $res_course->fields['STUDENT_ID'];
			$CAMPUS_CODE     = $res_course->fields['CAMPUS_CODE'];
			$BEGIN_DATE_1    = $res_course->fields['BEGIN_DATE_1'];
			$END_DATE_1      = $res_course->fields['END_DATE_1'];
			$PROGRAM_CODE    = $res_course->fields['PROGRAM_CODE'];
			$COURSE_CODE     = $res_course->fields['COURSE_CODE'];
			$TRANSCRIPT_CODE = $res_course->fields['TRANSCRIPT_CODE'];
			$SESSION_NAME    = $res_course->fields['SESSION_NAME'];

			$STUDENT_GROUP    = $res_course->fields['STUDENT_GROUP'];
			$MIDPOINT_DATE   = $res_course->fields['MIDPOINT_DATE'];


			$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
			$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
			// Query for get the COMPLETED_HOURS, SCHEDULE_HOURS
			$res_attendance_hours = $db->Execute("SELECT  
                        sum(S_STUDENT_SCHEDULE.HOURS) as SCHEDULE_HOURS, 
                        sum(ATTENDANCE_HOURS) as COMPLETED_HOURS 
                    FROM 
                        S_STUDENT_SCHEDULE 
                        LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
                        LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL
                    WHERE 
                        S_STUDENT_SCHEDULE.PK_STUDENT_MASTER =  '$PK_STUDENT_MASTER'
                        AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENR'
                        AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                        AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'
                        AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ");

			$COMPLETED_UNITS	 = 0;
			$ATTEMPTED_UNITS	 = 0;
			$PROGRAM_UNITS       = 0;

			$FA_COMPLETED_UNITS	 = 0;
			$FA_ATTEMPTED_UNITS	 = 0;
			$PROGRAM_FA_UNITS    = 0;

			$COMPLETED_HOURS     = 0;
			$SCHEDULE_HOURS      = 0;
			$PROGRAM_HOURS       = 0;

			if ($res_course->fields['UNITS_ATTEMPTED'] == 1) {
				$ATTEMPTED_UNITS 	 = $res_course->fields['COURSE_UNITS'];
				$FA_ATTEMPTED_UNITS	 = $res_course->fields['FA_UNITS'];
			}

			$c_in_att_tot 		+= $ATTEMPTED_UNITS;
			$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS;

			$c_fa_in_att_sub_tot 	+= $FA_ATTEMPTED_UNITS;
			$c_fa_in_att_tot 		+= $FA_ATTEMPTED_UNITS;

			if ($res_course->fields['UNITS_COMPLETED'] == 1) {
				$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
				$FA_COMPLETED_UNITS	 = $res_course->fields['FA_UNITS'];

				$c_in_comp_tot  	+= $COMPLETED_UNITS;
				$c_in_comp_sub_tot  += $COMPLETED_UNITS;

				$c_fa_in_comp_sub_tot   += $FA_COMPLETED_UNITS;
				$c_fa_in_comp_tot  		+= $FA_COMPLETED_UNITS;
			}

			$PROGRAM_UNITS = $res_course->fields['PROGRAM_UNITS'];
			$c_in_prog_unit_tot     += $PROGRAM_UNITS;
			$c_in_prog_unit_sub_tot += $PROGRAM_UNITS;

			$PROGRAM_FA_UNITS = $res_course->fields['PROGRAM_FA_UNITS'];
			$c_in_prog_fa_unit_tot 	    += $PROGRAM_FA_UNITS;
			$c_in_prog_fa_unit_sub_tot 	+= $PROGRAM_FA_UNITS;

			$COMPLETED_HOURS = $res_attendance_hours->fields['COMPLETED_HOURS'];
			$c_in_prog_comp_hour_tot 	 += $COMPLETED_HOURS;
			$c_in_prog_comp_hour_sub_tot += $COMPLETED_HOURS;

			$SCHEDULE_HOURS  = $res_attendance_hours->fields['SCHEDULE_HOURS'];
			$c_in_sched_hour_tot 	    += $SCHEDULE_HOURS;
			$c_in_sched_hour_sub_tot 	+= $SCHEDULE_HOURS;

			$PROGRAM_HOURS = $res_course->fields['PROGRAM_HOURS'];
			$c_in_prog_hour_tot 	    += $PROGRAM_HOURS;
			$c_in_prog_hour_sub_tot 	+= $PROGRAM_HOURS;

			$gnu = 0;
			$gpa = 0;
			if ($res_course->fields['CALCULATE_GPA'] == 1) {
				$gnu 				 = $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
				$c_in_cu_gnu 		+= $gnu;
				$c_in_cu_sub_gnu 	+= $gnu;

				$gpa				= $gnu / $COMPLETED_UNITS;
				$c_in_gpa_sub_tot 	+= $gpa;
				$c_in_gpa_tot 		+= $gpa;

				$total_rec++;
				$total_cum_rec++;

				$c_in_num_grade_sub_tot += $res_course->fields['NUMERIC_GRADE'];
				$c_in_num_grade_tot		+= $res_course->fields['NUMERIC_GRADE'];

				// DIAM-787, new calulated gpa
				$GPA_VALULE 		     = $res_course->fields['GPA_VALUE'];
				$gpa_value_total 		+= $GPA_VALULE;
				$GPA_WEIGHT 		     = $res_course->fields['GPA_WEIGHT'];
				$gpa_weight_total 		+= $GPA_WEIGHT;
				// End DIAM-787, new calulated gpa
			}

			// DIAM-787, new calulated gpa
			$gpa_weighted = 0;

			if ($gpa_value_total > 0) {
				$gpa_weighted           = ($gpa_value_total / $gpa_weight_total);
			}
			$summation_of_gpa += $GPA_VALULE;
			$summation_of_weight += $GPA_WEIGHT;

			// End DIAM-787, new calulated gpa

			// DIAM-2043
			$res_sap_scale_setup_data = $db->Execute("SELECT * FROM 
															S_SAP_SCALE_SETUP_DETAIL AS SAP_DET
														WHERE 
															SAP_DET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
															AND SAP_DET.ACTIVE = '1' 
															AND SAP_DET.PK_SAP_SCALE = '$PK_SAP_SCALE' ");
			
			while (!$res_sap_scale_setup_data->EOF) { 

				$PROGRAM_PACE_PERCENTAGE = $res_sap_scale_setup_data->fields['PROGRAM_PACE_PERCENTAGE'];
				$PERIOD_CAL              = $res_sap_scale_setup_data->fields['PERIOD'];

				if($PK_PROGRAM_PACE != '')
				{
					switch ($PK_PROGRAM_PACE) {
						case '1': // Hours Completed
							$final_hours_completed_val = ($SAP_PROGRAM_HOURS * $PROGRAM_PACE_PERCENTAGE) / 100;
							// $c_in_prog_comp_hour_tot;
							break;
						case '2': // Hours Scheduled
							$final_hours_scheduled_val = ($SAP_PROGRAM_HOURS * $PROGRAM_PACE_PERCENTAGE) / 100;
							//$c_in_sched_hour_tot;
							break;
						case '3': // FA Units Completed
							$final_fa_units_completed_val =  ($SAP_PROGRAM_FA_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100; 
							//$c_fa_in_comp_tot;
							break;
						case '4': // FA Units Attempted
							$final_fa_units_attempted_val =  ($SAP_PROGRAM_FA_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100;
							//$c_fa_in_att_tot;
							break;	
						case '5': // Units Completed
							$final_units_completed_val =  ($SAP_PROGRAM_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100;
							//$c_in_comp_tot;
							break;	
						case '6': // Units Attempted
							$final_units_attempted_val =  ($SAP_PROGRAM_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100;
							//$c_in_att_tot;
							break;				
						default:
							# code...
							break;
					}
				}
				
				if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){
					$Data_Grade = $res_course->fields['GRADE'];
					if($Data_Grade != '-'  || 1 == 1) // dvb
					{
						// if($final_hours_completed_val >= $c_in_prog_comp_hour_tot)
						// {
						// 	$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
						// 	'total_prog_unit_tot'=>$c_in_prog_unit_tot,
						// 	'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
						// 	'total_prog_hour_tot'=>$c_in_prog_hour_tot,
						// 	'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
						// }
						// if($final_hours_scheduled_val >= $c_in_sched_hour_tot)
						// {
						// 	$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
						// 	'total_prog_unit_tot'=>$c_in_prog_unit_tot,
						// 	'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
						// 	'total_prog_hour_tot'=>$c_in_prog_hour_tot,
						// 	'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
						// }

						// if($final_fa_units_completed_val >= $c_fa_in_comp_tot)
						// {
						// 	$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
						// 	'total_prog_unit_tot'=>$c_in_prog_unit_tot,
						// 	'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
						// 	'total_prog_hour_tot'=>$c_in_prog_hour_tot,
						// 	'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
						// }
						// if($final_fa_units_attempted_val >= $c_fa_in_att_tot)
						// {
						// 	$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
						// 	'total_prog_unit_tot'=>$c_in_prog_unit_tot,
						// 	'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
						// 	'total_prog_hour_tot'=>$c_in_prog_hour_tot,
						// 	'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
						// }
						// if($final_units_completed_val >= $c_in_comp_tot)
						// {
						// 	$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
						// 	'total_prog_unit_tot'=>$c_in_prog_unit_tot,
						// 	'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
						// 	'total_prog_hour_tot'=>$c_in_prog_hour_tot,
						// 	'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
						// }
						// if($final_units_attempted_val >= $c_in_att_tot)
						// {
						// 	$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
						// 	'total_prog_unit_tot'=>$c_in_prog_unit_tot,
						// 	'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
						// 	'total_prog_hour_tot'=>$c_in_prog_hour_tot,
						// 	'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
						// }
						// dvb
						if (
				        	(
					            $c_in_prog_comp_hour_tot >= $final_hours_completed_val && !empty($final_hours_completed_val) ||
					            $c_in_sched_hour_tot >= $final_hours_scheduled_val && !empty($final_hours_scheduled_val) ||
					            $c_fa_in_comp_tot >= $final_fa_units_completed_val && !empty($final_fa_units_completed_val) ||
					            $c_fa_in_att_tot >= $final_fa_units_attempted_val && !empty($final_fa_units_attempted_val) ||
					            $c_in_comp_tot >= $final_units_completed_val && !empty($final_units_completed_val) ||
					            $c_in_att_tot >= $final_units_attempted_val && !empty($final_units_attempted_val)
					        )
				            &&
				            !isset($eval_array[$PERIOD_CAL])
				        ) {
				            $eval_array[$PERIOD_CAL] = array(
				                'total_prog_comp_hour_tot' => $c_in_prog_comp_hour_tot,
				                'total_sched_hour_tot'     => $c_in_sched_hour_tot,
				                'total_units_att'          => $c_in_att_tot,
				                'total_units_compt'        => $c_in_comp_tot,
				                'total_fa_units_att'       => $c_fa_in_att_tot,
				                'total_fa_units_compt'     => $c_fa_in_comp_tot,
				                'total_prog_unit_tot'      => $c_in_prog_unit_tot,
				                'total_prog_fa_unit_tot'   => $c_in_prog_fa_unit_tot,
				                'total_prog_hour_tot'      => $c_in_prog_hour_tot,
				                'new_total_cum_gpa'        => ($summation_of_gpa / $summation_of_weight)
				            );

				            // echo 'eval_array';
				            // echo '<pre>';
				            // echo '$PERIOD_CAL'.$PERIOD_CAL;
				            // print_r($eval_array[$PERIOD_CAL]);
				            
				        }

					}
				}
					

				$res_sap_scale_setup_data->MoveNext();

			}
			// End DIAM-2043

			//if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){
			$Data_Grade = $res_course->fields['GRADE'];
			if ($Data_Grade != '-'  || 1 == 1) { // dvb
				$my_values = $gpa_weighted; //($total_tc_gpa_weighted/$total_cum_rec);
				$total_cum_gpa = $my_values;
				$new_total_cum_gpa = ($summation_of_gpa / $summation_of_weight);
				$total_units_att = $c_in_att_tot;
				$total_units_compt = $c_in_comp_tot;
				$total_fa_units_att = $c_fa_in_att_tot;
				$total_fa_units_compt = $c_fa_in_comp_tot;
				$total_prog_unit_tot = $c_in_prog_unit_tot;
				$total_prog_fa_unit_tot = $c_in_prog_fa_unit_tot;

				$total_prog_comp_hour_tot = $c_in_prog_comp_hour_tot;
				$total_sched_hour_tot = $c_in_sched_hour_tot;
				$total_prog_hour_tot = $c_in_prog_hour_tot;
			}
			//}

			// Sap Evalution
			$res_sap_scale_setup_detail = $db->Execute("SELECT 
				SAP_WAR.SAP_WARNING, 
				SAP_DET.*,
				SAP_SETUP.SAP_SCALE_NAME AS SAP_SCALE_NAME,
				S_PROGRAM_PACE.PROGRAM_PACE_NAME AS PROGRAM_PACE_NAME
			FROM 
				S_SAP_SCALE_SETUP_DETAIL AS SAP_DET 
				INNER JOIN S_SAP_WARNING AS SAP_WAR ON SAP_DET.PK_SAP_WARNING = SAP_WAR.PK_SAP_WARNING 
				INNER JOIN S_SAP_SCALE_SETUP AS SAP_SETUP ON SAP_SETUP.PK_SAP_SCALE = SAP_DET.PK_SAP_SCALE
				INNER JOIN S_PROGRAM_PACE AS S_PROGRAM_PACE ON S_PROGRAM_PACE.PK_PROGRAM_PACE = SAP_SETUP.PK_PROGRAM_PACE
			WHERE 
				SAP_DET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
				AND SAP_WAR.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
				AND SAP_DET.ACTIVE = '1' 
				AND SAP_WAR.ACTIVE = '1' 
				AND S_PROGRAM_PACE.ACTIVE = '1'
				AND SAP_DET.PK_SAP_SCALE = '$PK_SAP_SCALE' ");
			//print_r($res_sap_scale_setup_detail->fields);exit;

			

			$Periods = 1;
			$flag = '0';
			$i=0;
			
			while (!$res_sap_scale_setup_detail->EOF) {

				$SAP_WARNINGS            	= $res_sap_scale_setup_detail->fields['SAP_WARNING'];
				$PROGRAM_PACE_PERCENTAGE 	= $res_sap_scale_setup_detail->fields['PROGRAM_PACE_PERCENTAGE'];
				$PERIOD                  	= $res_sap_scale_setup_detail->fields['PERIOD'];
				$PERIODS 				 	= $eval_array[$PERIOD];

				$s_total_units_att = $PERIODS['total_units_att'] ? $PERIODS['total_units_att'] : '0.00';
				$s_total_units_compt = $PERIODS['total_units_compt'] ? $PERIODS['total_units_compt'] : '0.00';
				$s_total_prog_unit_tot = $PERIODS['total_prog_unit_tot'] ? $PERIODS['total_prog_unit_tot'] : '0.00';

				$cum_course_unit1 = ($s_total_units_compt / $s_total_units_att) * 100; // Units Completed/Hours Attempted
				$cum_course_unit2 = ($s_total_units_compt / $s_total_prog_unit_tot) * 100; // Units Completed/Program Units
				$cum_course_unit3 = ($s_total_units_att / $s_total_prog_unit_tot) * 100; // Units Attempted/Program Units

				$s_total_fa_units_att = $PERIODS['total_fa_units_att'] ? $PERIODS['total_fa_units_att'] : '0.00';
				$s_total_fa_units_compt = $PERIODS['total_fa_units_compt'] ? $PERIODS['total_fa_units_compt'] : '0.00';
				$s_total_prog_fa_unit_tot = $PERIODS['total_prog_fa_unit_tot'] ? $PERIODS['total_prog_fa_unit_tot'] : '0.00';

				$cum_course_unit4 = ($s_total_fa_units_compt / $s_total_fa_units_att) * 100; // FA Units Completed/FA Units Attempted
				$cum_course_unit5 = ($s_total_fa_units_compt / $s_total_prog_fa_unit_tot) * 100; // FA Units Completed/Program FA Units
				$cum_course_unit6 = ($s_total_fa_units_att / $s_total_prog_fa_unit_tot) * 100; // FA Units Attempted/Program FA Units

				$s_total_prog_comp_hour_tot = $PERIODS['total_prog_comp_hour_tot'] ? $PERIODS['total_prog_comp_hour_tot'] : '0.00';
				$s_total_sched_hour_tot = $PERIODS['total_sched_hour_tot'] ? $PERIODS['total_sched_hour_tot'] : '0.00';
				$s_total_prog_hour_tot = $PERIODS['total_prog_hour_tot'] ? $PERIODS['total_prog_hour_tot'] : '0.00';

				$cum_course_unit7 = ($s_total_prog_comp_hour_tot / $s_total_sched_hour_tot) * 100; // Hours Completed/Hours Scheduled
				$cum_course_unit8 = ($s_total_prog_comp_hour_tot / $s_total_prog_hour_tot) * 100; // Hours Completed/Program Hours
				$cum_course_unit9 = ($s_total_sched_hour_tot / $s_total_prog_hour_tot) * 100; // Hours Scheduled/Program Hours

				$s_total_cum_gpa = $PERIODS['new_total_cum_gpa'] ? $PERIODS['new_total_cum_gpa'] : '0.00';

				$txt_Units_Attempted = '';
				$txt_Units_Completed = '';
				$txt_Units_Program = '';
				$txt_units_comp_attemp_per = '';
				$txt_units_comp_program_per = '';
				$txt_units_attemp_program_per = '';

				$txt_FA_Units_Attempted = '';
				$txt_FA_Units_Completed = '';
				$txt_FA_Units_Program = '';
				$txt_fa_units_comp_attemp_per = '';
				$txt_fa_units_comp_program_per = '';
				$txt_fa_units_attemp_program_per = '';

				$txt_Hours_Completed = '';
				$txt_Hours_Scheduled = '';
				$txt_Hours_Program = '';
				$txt_hours_completed_scheduled_per = '';
				$txt_hours_completed_program_per = '';
				$txt_hours_scheduled_program_per = '';
				
				$txt_cum_gpa = '';

				if($units_attemp){
					$txt_Units_Attempted = number_format_value_checker($s_total_units_att,2);
				}
				if($units_comp){
					$txt_Units_Completed = number_format_value_checker($s_total_units_compt,2);
				}
				if($program_units){
					$txt_Units_Program = number_format_value_checker($s_total_prog_unit_tot,2);
				}
				if($units_comp_attemp_per){
					$txt_units_comp_attemp_per = number_format_value_checker($cum_course_unit1,2);
				}
				if($units_comp_program_per){
					$txt_units_comp_program_per = number_format_value_checker($cum_course_unit2,2);
				}
				if($units_attemp_program_per){
					$txt_units_attemp_program_per = number_format_value_checker($cum_course_unit3,2);
				}


				if($fa_units_attemp){
					$txt_FA_Units_Attempted = number_format_value_checker($s_total_fa_units_att,2);
				}
				if($fa_units_comp){
					$txt_FA_Units_Completed = number_format_value_checker($s_total_fa_units_compt,2);
				}
				if($program_fa_units){
					$txt_FA_Units_Program = number_format_value_checker($s_total_prog_fa_unit_tot,2);
				}
				if($fa_units_comp_attemp_per){
					$txt_fa_units_comp_attemp_per = number_format_value_checker($cum_course_unit4,2);
				}
				if($fa_units_comp_program_per){
					$txt_fa_units_comp_program_per = number_format_value_checker($cum_course_unit5,2);
				}
				if($fa_units_attemp_program_per){
					$txt_fa_units_attemp_program_per = number_format_value_checker($cum_course_unit6,2);
				}


				if($hours_comp){
					$txt_Hours_Completed = number_format_value_checker($s_total_prog_comp_hour_tot,2);
				}
				if($hours_sched){
					$txt_Hours_Scheduled = number_format_value_checker($s_total_sched_hour_tot,2);
				}
				if($program_hours){
					$txt_Hours_Program = number_format_value_checker($s_total_prog_hour_tot,2);
				}
				if($hours_completed_scheduled_per){
					$txt_hours_completed_scheduled_per = number_format_value_checker($cum_course_unit7,2);
				}
				if($hours_completed_program_per){
					$txt_hours_completed_program_per = number_format_value_checker($cum_course_unit8,2);
				}
				if($hours_scheduled_program_per){
					$txt_hours_scheduled_program_per = number_format_value_checker($cum_course_unit9,2);
				}

				if($CUMULATIVE_GPA == '1'){
					$txt_cum_gpa = number_format_value_checker($s_total_cum_gpa,2);
				}
	

				/* SAP Program Pace Calculations */
				if ($PK_PROGRAM_PACE != '') {
					switch ($PK_PROGRAM_PACE) {
						case '1': // Hours Completed
							$sap_program_pace_per = ($s_total_prog_comp_hour_tot / $SAP_PROGRAM_HOURS) * 100; // Hours Completed/Program Hours(This program come from Program setting)
							break;
						case '2': // Hours Scheduled
							$sap_program_pace_per = ($s_total_sched_hour_tot / $SAP_PROGRAM_HOURS) * 100; // Hours Scheduled/Program Hours(This program come from Program setting)
							break;
						case '3': // FA Units Completed
							$sap_program_pace_per = ($s_total_fa_units_compt / $SAP_PROGRAM_FA_UNITS) * 100; // FA Units Completed/Program FA Units Hours(This program come from Program setting)
							break;
						case '4': // FA Units Attempted
							$sap_program_pace_per = ($s_total_fa_units_att / $SAP_PROGRAM_FA_UNITS) * 100; // FA Units Attempted/Program FA Units Hours(This program come from Program setting)
							break;
						case '5': // Units Completed
							$sap_program_pace_per = ($s_total_units_compt / $SAP_PROGRAM_UNITS) * 100; // Units Completed/Program Units Hours(This program come from Program setting)
							break;
						case '6': // Units Attempted
							$sap_program_pace_per = ($s_total_units_att / $SAP_PROGRAM_UNITS) * 100; // Units Attempted/Program Units Attempted(This program come from Program setting)
							break;
						default:
							# code...
							break;
					}
				}
				/* End SAP Program Pace Calculations */

				/** DIAM-862 **/
				$SAP_SCALE               	= $res_sap_scale_setup_detail->fields['SAP_SCALE_NAME'];
				$PROGRAM_PACE            	= $res_sap_scale_setup_detail->fields['PROGRAM_PACE_NAME'];

				$HOURS_COMPLETED_HOURS_SCHEDULED  		= $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_COMPLETED_SCHEDULED'];
				$HOURS_COMPLETED_PROGRAM_HOURS  		= $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_COMPLETED_PROGRAM'];
				$HOURS_SCHEDULED_PROGRAM_HOURS  		= $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_SCHEDULED_PROGRAM'];

				$FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED  	= $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED'];
				$FA_UNITS_COMPLETED_PROGRAM_FA  	    = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM'];
				$FA_UNITS_ATTEMPTED_PROGRAM_FA  	    = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM'];

				$STD_UNITS_COMPLETED_ATTEMPTED_UNITS  	= $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_COMPLETED_ATTEMPTED'];
				$STD_UNITS_COMPLETED_PROGRAM_UNITS 		= $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_COMPLETED_PROGRAM'];
				$STD_UNITS_ATTEMPTED_PROGRAM_UNITS      = $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_ATTEMPTED_PROGRAM'];

				$GPA_CUMULATIVE_UNITS  				    = $res_sap_scale_setup_detail->fields['CUMULATIVE_GPA'];

				$CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED_YN  = $res_sap_scale_setup_detail->fields['PERIOD_HOURS_COMPLETED_SCHEDULED'];
				if ($CUMULATIVE_PERIOD_HOURS_COMPLETED_SCHEDULED_YN == '1') {
					$INCLUDE_TRANSFER_HOURS_COMPLETED_SCHEDULED = 'Yes';
				} else {
					$INCLUDE_TRANSFER_HOURS_COMPLETED_SCHEDULED = 'No';
				}

				$CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED_YN  = $res_sap_scale_setup_detail->fields['PERIOD_UNITS_COMPLETED_ATTEMPTED'];
				if ($CUMULATIVE_PERIOD_UNITS_COMPLETED_ATTEMPTED_YN == '1') {
					$INCLUDE_TRANSFER_UNITS_COMPLETED_ATTEMPTED = 'Yes';
				} else {
					$INCLUDE_TRANSFER_UNITS_COMPLETED_ATTEMPTED = 'No';
				}

				$CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_YN = $res_sap_scale_setup_detail->fields['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'];
				if ($CUMULATIVE_PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_YN == '1') {
					$INCLUDE_TRANSFER_FA_UNITS_COMPLETED_ATTEMPTED = 'Yes';
				} else {
					$INCLUDE_TRANSFER_FA_UNITS_COMPLETED_ATTEMPTED = 'No';
				}

				$CUMULATIVE_PERIOD_GPA_YN  						= $res_sap_scale_setup_detail->fields['PERIOD_GPA'];
				if ($CUMULATIVE_PERIOD_GPA_YN == '1') {
					$INCLUDE_TRANSFER_GPA = 'Yes';
				} else {
					$INCLUDE_TRANSFER_GPA = 'No';
				}

				/** End DIAM-862 **/

				$final_flag = '0';
				if ($PERIOD == '1') {
					if ($PROGRAM_PACE_PERCENTAGE <= $sap_program_pace_per) {
						$flag = '1';
					} else {
						$flag = '0';
					}
				} else {
					if ($flag == '1') {
						if ($PROGRAM_PACE_PERCENTAGE <= $sap_program_pace_per) {
							$final_flag = '1';
						} else {
							$final_flag = '0';
						}
					}
				}

				// dvb
				$gpa_flag_hour_comp_sch = $gpa_flag_hour_comp_prog = $gpa_flag_hour_sch_prog = 
				$gpa_flag_fa_unit_comp_att = $gpa_flag_fa_unit_comp_prog = $gpa_flag_fa_unit_att_prog = 
				$gpa_flag_unit_comp_att = $gpa_flag_unit_comp_prog = $gpa_flag_unit_att_prog = 
				$gpa_flag_cum_gpa = '1'; // asume que cumple si no se evalúa

				if ($HOURS_COMPLETED_SCHEDULED == '1') {
					$SAP_HOURS_COMPLETED_SCHEDULED = $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_COMPLETED_SCHEDULED'];

					if ($cum_course_unit7 >= $SAP_HOURS_COMPLETED_SCHEDULED) {
						$gpa_flag_hour_comp_sch = '1';
						$value = ' Met';
					} else {
						$gpa_flag_hour_comp_sch = '0';
						$value = ' Not Met';
					}
				}
				if ($HOURS_COMPLETED_PROGRAM == '1') {
					$SAP_HOURS_COMPLETED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_COMPLETED_PROGRAM'];

					if ($cum_course_unit8 >= $SAP_HOURS_COMPLETED_PROGRAM) {
						$gpa_flag_hour_comp_prog = '1';
						$value = ' Met';
					} else {
						$gpa_flag_hour_comp_prog = '0';
						$value = ' Not Met';
					}
				}
				if ($HOURS_SCHEDULED_PROGRAM == '1') {
					$SAP_HOURS_SCHEDULED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_SCHEDULED_PROGRAM'];

					if ($cum_course_unit9 >= $SAP_HOURS_SCHEDULED_PROGRAM) {
						$gpa_flag_hour_sch_prog = '1';
						$value = ' Met';
					} else {
						$gpa_flag_hour_sch_prog = '0';
						$value = ' Not Met';
					}
				}

				if ($FA_UNITS_COMPLETED_ATTEMPTED == '1') {
					$SAP_FA_UNITS_COMPLETED_ATTEMPTED = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED'];
					if ($cum_course_unit4 >= $SAP_FA_UNITS_COMPLETED_ATTEMPTED) {
						$gpa_flag_fa_unit_comp_att = '1';
						$value = ' Met';
					} else {
						$gpa_flag_fa_unit_comp_att = '0';
						$value = ' Not Met';
					}
				}
				if ($FA_UNITS_COMPLETED_PROGRAM == '1') {
					$SAP_FA_UNITS_COMPLETED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM'];
					if ($cum_course_unit5 >= $SAP_FA_UNITS_COMPLETED_PROGRAM) {
						$gpa_flag_fa_unit_comp_prog = '1';
						$value = ' Met';
					} else {
						$gpa_flag_fa_unit_comp_prog = '0';
						$value = ' Not Met';
					}
				}
				if ($FA_UNITS_ATTEMPTED_PROGRAM == '1') {
					$SAP_FA_UNITS_ATTEMPTED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM'];
					if ($cum_course_unit6 >= $SAP_FA_UNITS_ATTEMPTED_PROGRAM) {
						$gpa_flag_fa_unit_att_prog = '1';
						$value = ' Met';
					} else {
						$gpa_flag_fa_unit_att_prog = '0';
						$value = ' Not Met';
					}
				}

				if ($UNITS_COMPLETED_ATTEMPTED == '1') {
					$SAP_UNITS_COMPLETED_ATTEMPTED = $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_COMPLETED_ATTEMPTED'];
					if ($cum_course_unit1 >= $SAP_UNITS_COMPLETED_ATTEMPTED) {
						$gpa_flag_unit_comp_att = '1';
						$value = ' Met';
					} else {
						$gpa_flag_unit_comp_att = '0';
						$value = ' Not Met';
					}
				}
				if ($UNITS_COMPLETED_PROGRAM == '1') {
					$SAP_UNITS_COMPLETED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_COMPLETED_PROGRAM'];

					if ($cum_course_unit2 >= $SAP_UNITS_COMPLETED_PROGRAM) {
						$gpa_flag_unit_comp_prog = '1';
						$value = ' Met';
					} else {
						$gpa_flag_unit_comp_prog = '0';
						$value = ' Not Met';
					}
				}
				if ($UNITS_ATTEMPTED_PROGRAM == '1') {
					$SAP_UNITS_ATTEMPTED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_ATTEMPTED_PROGRAM'];

					if ($cum_course_unit3 >= $SAP_UNITS_ATTEMPTED_PROGRAM) {
						$gpa_flag_unit_att_prog = '1';
						$value = ' Met';
					} else {
						$gpa_flag_unit_att_prog = '0';
						$value = ' Not Met';
					}
				}

				if ($CUMULATIVE_GPA == '1') {
					$SAP_PERIOD_GPA = $res_sap_scale_setup_detail->fields['CUMULATIVE_GPA'];
					if ($s_total_cum_gpa >= $SAP_PERIOD_GPA) {
						$gpa_flag_cum_gpa = '1';
						$value = ' Met';
					} else {
						$gpa_flag_cum_gpa = '0';
						$value = ' Not Met';
					}
				}


				if ($gpa_flag_hour_comp_sch == '1' && $gpa_flag_hour_comp_prog == '1' && $gpa_flag_hour_sch_prog == '1' && $gpa_flag_fa_unit_comp_att == '1' && $gpa_flag_fa_unit_comp_prog == '1' && $gpa_flag_fa_unit_att_prog == '1' && $gpa_flag_unit_comp_att == '1' && $gpa_flag_unit_comp_prog == '1' && $gpa_flag_unit_att_prog == '1' && $gpa_flag_cum_gpa == '1') {


					$flag_gpa_response = 'Meeting SAP';
					$SAP_STATUS        = 'Pass';
					$SAP_WARNING       = 'No';
				} else {

					
					
					$flag_gpa_response = 'Not Meeting SAP';
					$SAP_STATUS        = 'Fail';
					$SAP_WARNING       = 'Yes';
				}

				$SAP_EV_PERIOD = '';
				$value_meet_sap = '';
				$flag_check = '';
				if ($flag == '1' && $PERIOD == '1') {
					$SAP_EV_PERIOD .= $Periods;
					$value_meet_sap = $flag_gpa_response;
					$flag_check = '1';
				} else {
					if ($final_flag == '1' && $flag = '1') {
						$SAP_EV_PERIOD .= $Periods;
						$value_meet_sap = $flag_gpa_response;
						$flag_check = '1';
					} else {
						$value_meet_sap = 'Not Meeting SAP';
						$flag_check = '0';
					}
				}

				if ($SAP_EV_PERIOD != '') {
					$SAP_EV_PERIOD_ARR[] =  $SAP_EV_PERIOD;
					$SAP_EV_PERIOD_RES = implode(',', $SAP_EV_PERIOD_ARR);
				}
				$SAP_WARNING_RESP = $value_meet_sap . ' - ' . $SAP_WARNINGS;
				// $SAP_WARNING_RESP = implode(' | ', $SAP_WARNING_DATA);

				// DIAM-2043
				if($flag_check == '0')
				{
					$txt_Hours_Completed = ($txt_Hours_Completed) != '' ? 'Student is not meeting SAP' : '';
					$txt_Hours_Scheduled = ($txt_Hours_Scheduled) != '' ? 'Student is not meeting SAP' : '';
					$txt_Hours_Program = ($txt_Hours_Program) != '' ? 'Student is not meeting SAP' : '';
					$txt_hours_completed_scheduled_per = ($txt_hours_completed_scheduled_per) != '' ? 'Student is not meeting SAP' : '';
					$txt_hours_completed_program_per = ($txt_hours_completed_program_per) != '' ? 'Student is not meeting SAP' : '';
					$txt_hours_scheduled_program_per = ($txt_hours_scheduled_program_per) != '' ? 'Student is not meeting SAP' : '';
					
					$txt_FA_Units_Completed = ($txt_FA_Units_Completed) != '' ? 'Student is not meeting SAP' : '';
					$txt_FA_Units_Program = ($txt_FA_Units_Program) != '' ? 'Student is not meeting SAP' : '';
					$txt_FA_Units_Attempted = ($txt_FA_Units_Attempted) != '' ? 'Student is not meeting SAP' : '';
					$txt_fa_units_comp_attemp_per = ($txt_fa_units_comp_attemp_per) != '' ? 'Student is not meeting SAP' : '';
					$txt_fa_units_comp_program_per = ($txt_fa_units_comp_program_per) != '' ? 'Student is not meeting SAP' : '';
					$txt_fa_units_attemp_program_per = ($txt_fa_units_attemp_program_per) != '' ? 'Student is not meeting SAP' : '';

					$txt_Units_Completed = ($txt_Units_Completed) != '' ? 'Student is not meeting SAP' : '';
					$txt_Units_Program = ($txt_Units_Program) != '' ? 'Student is not meeting SAP' : '';
					$txt_Units_Attempted = ($txt_Units_Attempted) != '' ? 'Student is not meeting SAP' : '';
					$txt_units_comp_attemp_per = ($txt_units_comp_attemp_per) != '' ? 'Student is not meeting SAP' : '';
					$txt_units_comp_program_per = ($txt_units_comp_program_per) != '' ? 'Student is not meeting SAP' : '';
					$txt_units_attemp_program_per = ($txt_units_attemp_program_per) != '' ? 'Student is not meeting SAP' : '';

					$txt_cum_gpa = 'Student is not meeting';
				}
				// End DIAM-2043

				$return[$i]['LAST_NAME']      	    = $LAST_NAME;
				$return[$i]['FIRST_NAME']      	    = $FIRST_NAME;
				$return[$i]['STUDENT_ID']      	    = $STUDENT_ID;
				$return[$i]['CAMPUS_CODE']     	    = $CAMPUS_CODE;
				$return[$i]['BEGIN_DATE_1']    		= $BEGIN_DATE_1;
				$return[$i]['END_DATE_1']      		= $END_DATE_1;
				$return[$i]['PROGRAM_CODE']    		= $PROGRAM_CODE;
				$return[$i]['COURSE_CODE']     		= $COURSE_CODE;
				$return[$i]['TRANSCRIPT_CODE'] 		= $TRANSCRIPT_CODE;
				$return[$i]['SESSION_NAME']         = $SESSION_NAME;

				//$return[$i]['SAP_EVALUTION_PERIOD'] = $SAP_EV_PERIOD_RES;
				$return[$i]['CREDITS_ATTEMPTED']    = number_format_value_checker($s_total_units_att, 2);
				$return[$i]['CREDITS_COMPLETED']    = number_format_value_checker($s_total_units_compt, 2);
				$return[$i]['GPA_VALUE']            = number_format_value_checker($s_total_cum_gpa, 2);
				$return[$i]['SAP_WARNING_RESP']     = $SAP_WARNING_RESP;

				$return[$i]['STUDENT_GROUP']		   = $STUDENT_GROUP;
				$return[$i]['MID_POINT_DATE']		   = $MIDPOINT_DATE;
				$return[$i]['SAP_SCALE']		   	   = $SAP_SCALE;
				$return[$i]['PROGRAM_PACE'] 		   = $PROGRAM_PACE;
				$return[$i]['PERIOD'] 				   = $PERIOD;
				$return[$i]['PROGRAM_PACE_PERCENTAGE'] = $PROGRAM_PACE_PERCENTAGE;

				$return[$i]['HOURS_COMPLETED_HOURS_SCHEDULED'] 		   = $HOURS_COMPLETED_HOURS_SCHEDULED;
				$return[$i]['HOURS_COMPLETED_PROGRAM_HOURS'] 		   = $HOURS_COMPLETED_PROGRAM_HOURS;
				$return[$i]['HOURS_SCHEDULED_PROGRAM_HOURS'] 		   = $HOURS_SCHEDULED_PROGRAM_HOURS;
				$return[$i]['INCLUDE_TRANSFER_HOURS_COMPLETED_SCHEDULED']  = $INCLUDE_TRANSFER_HOURS_COMPLETED_SCHEDULED;

				$return[$i]['FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED']    = $FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED;
				$return[$i]['FA_UNITS_COMPLETED_PROGRAM_FA'] 		   = $FA_UNITS_COMPLETED_PROGRAM_FA;
				$return[$i]['FA_UNITS_ATTEMPTED_PROGRAM_FA'] 		   = $FA_UNITS_ATTEMPTED_PROGRAM_FA;
				$return[$i]['INCLUDE_TRANSFER_FA_UNITS_COMPLETED_ATTEMPTED'] = $INCLUDE_TRANSFER_FA_UNITS_COMPLETED_ATTEMPTED;

				$return[$i]['STD_UNITS_COMPLETED_ATTEMPTED_UNITS'] 	   = $STD_UNITS_COMPLETED_ATTEMPTED_UNITS;
				$return[$i]['STD_UNITS_COMPLETED_PROGRAM_UNITS'] 	   = $STD_UNITS_COMPLETED_PROGRAM_UNITS;
				$return[$i]['STD_UNITS_ATTEMPTED_PROGRAM_UNITS'] 	   = $STD_UNITS_ATTEMPTED_PROGRAM_UNITS;
				$return[$i]['INCLUDE_TRANSFER_UNITS_COMPLETED_ATTEMPTED'] = $INCLUDE_TRANSFER_UNITS_COMPLETED_ATTEMPTED;

				$return[$i]['GPA_CUMULATIVE_UNITS'] 		           = $GPA_CUMULATIVE_UNITS;
				$return[$i]['INCLUDE_TRANSFER_GPA'] 		           = $INCLUDE_TRANSFER_GPA;

				$return[$i]['SAP_STATUS'] 		                       = $SAP_STATUS;
				$return[$i]['SAP_WARNING'] 		                       = $SAP_WARNING;

				// DIAM-2043
				$return[$i]['HOURS_COMPLETED'] 		                   = $txt_Hours_Completed;
				$return[$i]['HOURS_SCHEDULED'] 		                   = $txt_Hours_Scheduled;
				$return[$i]['HOURS_PROGRAM'] 		                   = $txt_Hours_Program;
				$return[$i]['HOURS_COMPLETED_SCHEDULED_PER'] 		   = $txt_hours_completed_scheduled_per;
				$return[$i]['HOURS_COMPLETED_PROGRAM_PER'] 		   	   = $txt_hours_completed_program_per;
				$return[$i]['HOURS_SCHEDULED_PROGRAM_PER'] 		   	   = $txt_hours_scheduled_program_per;

				$return[$i]['FA_UNIT_COMPLETED'] 		               = $txt_FA_Units_Completed;
				$return[$i]['FA_UNIT_PROGRAM'] 		                   = $txt_FA_Units_Program;
				$return[$i]['FA_UNIT_ATTEMPTED'] 		               = $txt_FA_Units_Attempted;
				$return[$i]['FA_UNIT_COMPLETED_ATTEMPTED_PER'] 		   = $txt_fa_units_comp_attemp_per;
				$return[$i]['FA_UNIT_COMPLETED_PROGRAM_PER'] 		   = $txt_fa_units_comp_program_per;
				$return[$i]['FA_UNIT_ATTEMPTED_PROGRAM_PER'] 		   = $txt_fa_units_attemp_program_per;

				$return[$i]['UNIT_COMPLETED'] 		                   = $txt_Units_Completed;
				$return[$i]['UNIT_PROGRAM'] 		                   = $txt_Units_Program;
				$return[$i]['UNIT_ATTEMPTED'] 		                   = $txt_Units_Attempted;
				$return[$i]['UNIT_COMPLETED_ATTEMPTED_PER'] 		   = $txt_units_comp_attemp_per;
				$return[$i]['UNIT_COMPLETED_PROGRAM_PER'] 		   	   = $txt_units_comp_program_per;
				$return[$i]['UNIT_ATTEMPTED_PROGRAM_PER'] 		   	   = $txt_units_attemp_program_per;

				$return[$i]['CUMULATIVE_GPA'] 		   	   			   = $txt_cum_gpa;
				// End DIAM-2043

				$res_sap_scale_setup_detail->MoveNext();
				$Periods++;
				$i++;
			}

			// End Sap Evalution
			

			$res_course->MoveNext();
		}
		$res_term->MoveNext();
	}
	//echo "<pre>";print_r($return);
	//exit;
	return $return;
	
}

$cnt = 0;
foreach ($Get_Stud_Master as $PK_STUDENT_MASTER) {
	$Get_Stud_Enrollment_Id = $Get_Stud_Enrollment[$cnt];
	$array_en_cond = " AND PK_STUDENT_ENROLLMENT = '$Get_Stud_Enrollment_Id' ";

	$res_type = $db->Execute("SELECT 
                                S_SAP_SCALE_SETUP.PK_SAP_SCALE 
                                FROM 
                                    S_STUDENT_ENROLLMENT 
                                    LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
                                    LEFT JOIN S_SAP_SCALE_SETUP ON S_SAP_SCALE_SETUP.PK_SAP_SCALE = M_CAMPUS_PROGRAM.PK_SAP_SCALE 
                                    LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
                                WHERE 
                                    S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $array_en_cond 
                                ORDER BY 
                                    BEGIN_DATE ASC, 
                                    M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC   ");
	//$counter = 0;
	while (!$res_type->EOF) {
		$aPK_SAP_SCALE   = $res_type->fields['PK_SAP_SCALE'];

		$result_en   = get_official_student_transcript($PK_STUDENT_MASTER, $array_en_cond, $aPK_SAP_SCALE, $Get_Stud_Enrollment_Id);
		// echo "<pre>";print_r($result_en);
		foreach($result_en as $res_en)
		{
			//echo "<pre>";print_r($res_en);
			if ($res_en['FIRST_NAME'] != '' || $res_en['LAST_NAME'] != '' || $res_en['CAMPUS_CODE'] != '' || $res_en['BEGIN_DATE_1'] != '' || $res_en['PROGRAM_CODE'] != '') {
				$line++;
				$index = -1;

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['LAST_NAME']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FIRST_NAME']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['STUDENT_ID']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['BEGIN_DATE_1']);

				// $index++;
				// $cell_no = $cell[$index] . $line;
				// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['END_DATE_1']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['PROGRAM_CODE']);

				$sCourse_Data = '';
				if ($res_en['COURSE_CODE'] != '' && $res_en['TRANSCRIPT_CODE'] != '') {
					$sCourse_Data = $res_en['COURSE_CODE'] . ',' . $res_en['TRANSCRIPT_CODE'];
				}

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($sCourse_Data);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['SESSION_NAME']);

				// $index++;
				// $cell_no = $cell[$index] . $line;
				// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['SAP_EVALUTION_PERIOD']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['CREDITS_ATTEMPTED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['CREDITS_COMPLETED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['GPA_VALUE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['SAP_WARNING_RESP']);

				$objPHPExcel->getActiveSheet()->getStyle('J:J')->getNumberFormat()->setFormatCode('0.00');
				$objPHPExcel->getActiveSheet()->getStyle('K:K')->getNumberFormat()->setFormatCode('0.00');
				$objPHPExcel->getActiveSheet()->getStyle('L:L')->getNumberFormat()->setFormatCode('0.00');

				/** DIAM - 862, SAP Pending List **/
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['STUDENT_GROUP']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['MIDPOINT_DATE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['SAP_SCALE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['PROGRAM_PACE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['PERIOD']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['PROGRAM_PACE_PERCENTAGE']);
				// $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(25);


				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_COMPLETED_HOURS_SCHEDULED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_COMPLETED_PROGRAM_HOURS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_SCHEDULED_PROGRAM_HOURS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['INCLUDE_TRANSFER_HOURS_COMPLETED_SCHEDULED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNITS_COMPLETED_PROGRAM_FA']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNITS_ATTEMPTED_PROGRAM_FA']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['INCLUDE_TRANSFER_FA_UNITS_COMPLETED_ATTEMPTED']);


				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['STD_UNITS_COMPLETED_ATTEMPTED_UNITS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['STD_UNITS_COMPLETED_PROGRAM_UNITS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['STD_UNITS_ATTEMPTED_PROGRAM_UNITS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['INCLUDE_TRANSFER_UNITS_COMPLETED_ATTEMPTED']);


				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['GPA_CUMULATIVE_UNITS']);
				$objPHPExcel->getActiveSheet()->getStyle("AE:AE")->getNumberFormat()->setFormatCode('0.00');

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['INCLUDE_TRANSFER_GPA']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['SAP_STATUS']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['SAP_WARNING']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_COMPLETED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_SCHEDULED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_PROGRAM']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_COMPLETED_SCHEDULED_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_COMPLETED_PROGRAM_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['HOURS_SCHEDULED_PROGRAM_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNIT_COMPLETED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNIT_PROGRAM']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNIT_ATTEMPTED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNIT_COMPLETED_ATTEMPTED_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNIT_COMPLETED_PROGRAM_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['FA_UNIT_ATTEMPTED_PROGRAM_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['UNIT_COMPLETED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['UNIT_PROGRAM']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['UNIT_ATTEMPTED']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['UNIT_COMPLETED_ATTEMPTED_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['UNIT_COMPLETED_PROGRAM_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['UNIT_ATTEMPTED_PROGRAM_PER']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en['CUMULATIVE_GPA']);

				$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(42);

				/** End DIAM - 862, SAP Pending List **/
			}
		}
		// $counter++;
		$res_type->MoveNext();
	}
	$cnt++;
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
//eader("location:" . $outputFileName);

echo $outputFileName;
exit;
