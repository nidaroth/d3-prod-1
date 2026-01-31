<? require_once("../global/config.php");
require_once("../global/create_notification.php");
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("get_department_from_t.php");
require_once("function_student_ledger.php");
require_once("../global/mail.php");
require_once("../global/texting.php");

require_once("check_access.php");
$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if ($REGISTRAR_ACCESS == 0) {
	header("location:../index");
}
//ticket #1240
$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
?>

<table data-toggle="table" class="table-striped" id="student_course_table">
	<thead>
		<tr>
			<th><?= TERM ?></th>
			<th><?= COURSE ?></th>
			<th><?= SESSION ?></th>
			<th><?= COURSE_OFFERING_STUDENT_STATUS ?></th><!-- Ticket # 1686 -->
			<th><?= COURSE_DESCRIPTION ?></th>
			<th><?= UNITS_ATTEMPTED ?></th>
			<th><?= UNITS_COMPLETED ?></th>

			<!-- Ticket # 1240   -->
			<? if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) { ?>
				<th><?= GRADE ?></th>
				<!-- <th ><?= GPA ?></th> -->
			<? }
			if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) { ?>
				<th><?= NUMERIC_GRADE_1 ?></th><!-- Ticket # 1686 -->
			<? } ?>
			<!-- Ticket # 1240   -->

			<th><?= ENROLLMENT ?></th>
			<th><?= OPTIONS ?></th>
		</tr>
	</thead>
	<tbody>
		<? $course_id = 2;
		$c_in_num_grade_tot 	= 0; //Ticket # 1240
		$c_in_prog_tot 			= 0;
		$c_in_att_tot 			= 0;
		$c_in_comp_tot 			= 0;
		$c_in_cu_gnu 			= 0;
		$c_in_cu_tot 			= 0;

		$Denominator 	= 0;
		$Numerator 		= 0;
		$Numerator1 	= 0;

		$summation_of_gpa      = 0;
		$summation_of_weight   = 0;

		/***** Ticket #974******/
		if ($_REQUEST['EXCLUDE_TRANSFERS_FROM_GPA'] != 1) { //Ticket #1221

			$res_course = $db->Execute("SELECT 
											S_COURSE.COURSE_CODE, 
											S_COURSE.COURSE_DESCRIPTION, 
											S_STUDENT_CREDIT_TRANSFER.UNITS, 
											S_COURSE.FA_UNITS, 
											S_GRADE.GRADE, 
											PK_STUDENT_ENROLLMENT, 
											S_STUDENT_CREDIT_TRANSFER.PK_GRADE, 
											S_GRADE.NUMBER_GRADE, 
											S_GRADE.CALCULATE_GPA, 
											S_GRADE.UNITS_ATTEMPTED, 
											S_GRADE.UNITS_COMPLETED, 
											S_GRADE.UNITS_IN_PROGRESS,
											CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) * S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
											CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT 
										FROM 
											S_STUDENT_CREDIT_TRANSFER 
											LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER, 
											S_GRADE, 
											M_CREDIT_TRANSFER_STATUS 
										WHERE 
											S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
											AND PK_STUDENT_MASTER = '$_REQUEST[sid]' 
											AND PK_STUDENT_ENROLLMENT IN($_REQUEST[eid]) 
											AND S_GRADE.CALCULATE_GPA = 1 
											AND S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
											AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
											AND SHOW_ON_TRANSCRIPT = 1"); //Ticket #1221 Ticket # 1251 Ticket # 1287

			while (!$res_course->EOF) {
				$Denominator += $res_course->fields['UNITS'];
				$Numerator1	 += $res_course->fields['UNITS'] * $res_course->fields['NUMBER_GRADE'];

				$res_course->MoveNext();
			}
		}
		/***** Ticket #974******/
		/*** Ticket #872 ***/
		/*** Ticket #996 ***/
		$res_tc = $db->Execute("SELECT S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_CREDIT_TRANSFER, 
										CONCAT(
										S_COURSE.COURSE_CODE, ' - ', S_COURSE.TRANSCRIPT_CODE
										) as COURSE_CODE, 
										CREDIT_TRANSFER_STATUS, 
										S_COURSE.COURSE_DESCRIPTION, 
										S_STUDENT_CREDIT_TRANSFER.UNITS, 
										S_COURSE.FA_UNITS, 
										S_GRADE.GRADE, 
										PK_STUDENT_ENROLLMENT, 
										S_STUDENT_CREDIT_TRANSFER.PK_GRADE, 
										S_GRADE.NUMBER_GRADE, 
										S_GRADE.CALCULATE_GPA, 
										S_GRADE.UNITS_ATTEMPTED, 
										S_GRADE.UNITS_COMPLETED, 
										S_GRADE.UNITS_IN_PROGRESS, 
										TC_NUMERIC_GRADE, 
										SHOW_ON_TRANSCRIPT,
										CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) * S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
										CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT 
									FROM 
										S_STUDENT_CREDIT_TRANSFER 
										LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
										LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
										LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
									WHERE 
										S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
										AND PK_STUDENT_MASTER = '$_REQUEST[sid]' 
										AND PK_STUDENT_ENROLLMENT IN($_REQUEST[eid]) 
										AND SHOW_ON_TRANSCRIPT = 1 
									ORDER BY 
										S_COURSE.COURSE_CODE ASC"); //Ticket #1221 Ticket #1240 Ticket # 1287 Ticket # 1740 Ticket # 1912 

		$res_stud_course1 = $db->Execute("SELECT 
																			PK_STUDENT_COURSE, 
																			S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,
																			CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
																			CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT
																		FROM 
																			S_STUDENT_COURSE 
																			LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
																			LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
																			LEFT JOIN S_TERM_MASTER On S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER
																			LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE
																		WHERE 
																			S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_REQUEST[sid]' 
																			AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN($_REQUEST[eid]) 
																			AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
																		ORDER BY 
																			BEGIN_DATE ASC, 
																			COURSE_CODE ASC "); //Ticket #1221
		//AND S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE
		// GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE


		if ($res_tc->RecordCount() > 0 || $res_stud_course1->RecordCount() > 0)
			$COURSE_ASSIGNED = 1;
		if ($COURSE_ASSIGNED == 0) {
			/*$res_course = $db->Execute("select PK_CAMPUS_PROGRAM_COURSE from M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY COURSE_ORDER ASC ");
			
			while (!$res_course->EOF) { 
				$_REQUEST['course_id'] 	  				= $course_id;
				$_REQUEST['eid'] 	  					= $_GET['eid'];
				$_REQUEST['PK_CAMPUS_PROGRAM_COURSE'] 	= $res_course->fields['PK_CAMPUS_PROGRAM_COURSE'];
				
				include("ajax_student_course.php");
				$course_id++;
				
				$res_course->MoveNext();
			}*/
		} else {
			while (!$res_tc->EOF) {
				$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT'];
				$PK_GRADE				= $res_tc->fields['PK_GRADE'];

				if ($EXCLUDE_TRANSFERS_FROM_GPA == 0) {
					/* Ticket # 1287 */
					if ($res_tc->fields['UNITS_ATTEMPTED'] == 1) { //Ticket # 1912
						$c_in_att_tot  += $res_tc->fields['UNITS'];
					}
					if ($res_tc->fields['UNITS_COMPLETED'] == 1) {
						$c_in_comp_tot += $res_tc->fields['UNITS'];
					}

					/* Ticket # 1287 */

					if ($res_tc->fields['CALCULATE_GPA'] == 1 && $res_tc->fields['SHOW_ON_TRANSCRIPT'] == 1) { // Ticket # 1287

						// calulated gpa DIAM-781
						$TC_GPA_VALULE 	 = $res_tc->fields['GPA_VALUE'];
						$TC_GPA_WEIGHT 	 = $res_tc->fields['GPA_WEIGHT'];

						$summation_of_gpa    += $TC_GPA_VALULE;
						$summation_of_weight += $TC_GPA_WEIGHT;
						// End calulated gpa DIAM-781

						$c_in_cu_gnu += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];

						if ($res_tc->fields['UNITS_COMPLETED'] == 1 && $res_tc->fields['SHOW_ON_TRANSCRIPT'] == 1) // Ticket # 1287
							$c_in_cu_tot += $res_tc->fields['UNITS'];
					}
				}


		?>
				<tr>
					<td>Transfer</td>
					<td><?= $res_tc->fields['COURSE_CODE'] ?></td>
					<td></td>
					<td><?= $res_tc->fields['CREDIT_TRANSFER_STATUS'] ?></td>
					<td><?= $res_tc->fields['COURSE_DESCRIPTION'] ?></td>
					<td>
						<div style="padding-top: 11px;width:100%;text-align:right"><? if ($res_tc->fields['UNITS_ATTEMPTED'] == 1) echo $res_tc->fields['UNITS'];
																					else echo "0.00"; ?></div>
					</td> <!-- Ticket # 1912 -->
					<td>
						<div style="padding-top: 11px;width:100%;text-align:right"><? if ($res_tc->fields['UNITS_COMPLETED'] == 1) echo $res_tc->fields['UNITS'];
																					else echo "0.00"; ?></div>
					</td> <!-- Ticket # 1912 -->
					<!-- Ticket # 1240   -->
					<? if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) { ?>
						<td>
							<div style="padding-top: 11px;"><?= $res_tc->fields['GRADE'] ?></div>
						</td>

					<? } ?>
					<!-- <td ><div style="padding-top: 11px;width:100%;" ><?= number_format_value_checker(($summation_of_gpa / $summation_of_weight), 2) ?></div></td> -->
					<? if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) { ?>
						<td>
							<div style="padding-top: 11px;width:50px;text-align:right;"><?= $res_tc->fields['TC_NUMERIC_GRADE'] ?></div>
						</td><!-- Ticket # 1685 -->
					<? } ?>
					<!-- Ticket # 1240   -->

					<td>
						<!-- Ticket #996 -->
						<input type="hidden" name="STUDENT_COURSE_TC_PK_STUDENT_CREDIT_TRANSFER[]" value="<?= $res_tc->fields['PK_STUDENT_CREDIT_TRANSFER'] ?>" />

						<!-- Ticket # 1686 -->
						<select id="STUDENT_COURSE_TC_PK_STUDENT_ENROLLMENT_<?= $res_tc->fields['PK_STUDENT_CREDIT_TRANSFER'] ?>" name="STUDENT_COURSE_TC_PK_STUDENT_ENROLLMENT[]" class="form-control required-entry">
							<? $res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CAMPUS_CODE, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_REQUEST[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //Ticket #1221
							while (!$res_type->EOF) { ?>
								<option value="<?= $res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if ($res_type->fields['PK_STUDENT_ENROLLMENT'] == $PK_STUDENT_ENROLLMENT) echo "selected"; ?> <? if ($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?>><?= $res_type->fields['BEGIN_DATE_1'] . ' - ' . $res_type->fields['CODE'] . ' - ' . $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['CAMPUS_CODE'] ?></option>
							<? $res_type->MoveNext();
							} ?>
						</select>
						<!-- Ticket # 1686 -->
					</td>
					<td></td>
				</tr>
				<? $res_tc->MoveNext();
			}

			/* Ticket # 1251 */
			$res_course = $db->Execute("SELECT 
											NUMERIC_GRADE, 
											COURSE_UNITS, 
											NUMBER_GRADE,
											CALCULATE_GPA, 
											-- CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
											-- CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT
										  FROM 
											S_STUDENT_COURSE 
											LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
											LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
											LEFT JOIN S_TERM_MASTER On S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, 
											S_GRADE 
										  WHERE 
											S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_REQUEST[sid]' 
											AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN($_REQUEST[eid]) 
											AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
											AND CALCULATE_GPA = 1 
											AND S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE");

			while (!$res_course->EOF) {
				$Denominator += $res_course->fields['COURSE_UNITS'];
				$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
				$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

				// if($res_course->fields['CALCULATE_GPA'] == 1) {
				// 	$GPA_VALULE 	= $res_course->fields['GPA_VALUE']; 
				// 	$GPA_WEIGHT 	= $res_course->fields['GPA_WEIGHT'];
				// }


				// $summation_of_gpa    += $GPA_VALULE;
				// $summation_of_weight += $GPA_WEIGHT;

				$res_course->MoveNext();
			}
			/* Ticket # 1251 */

			while (!$res_stud_course1->EOF) {
				$_REQUEST['course_id'] 	  		= $course_id;
				//$_REQUEST['sid'] 	  			= $_GET['id']; //Ticket #996
				$_REQUEST['PK_STUDENT_COURSE'] 	= $res_stud_course1->fields['PK_STUDENT_COURSE'];

				$GPA_VALUES  = $res_stud_course1->fields['GPA_VALUE'];
				$GPA_WEIGHTS = $res_stud_course1->fields['GPA_WEIGHT'];

				$summation_of_gpa    += $GPA_VALUES;
				$summation_of_weight += $GPA_WEIGHTS;

				include("ajax_student_course.php");
				$course_id++;

				$res_stud_course1->MoveNext();
			}

			if ($_REQUEST['sid'] > 0) { //Ticket #1221
				$res_waiting = $db->Execute("SELECT PK_COURSE_OFFERING_WAITING_LIST, S_COURSE_OFFERING_WAITING_LIST.PK_COURSE_OFFERING, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE,SESSION, SESSION_NO, S_COURSE_OFFERING_WAITING_LIST.PK_STUDENT_ENROLLMENT FROM S_COURSE_OFFERING_WAITING_LIST, S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER WHERE S_COURSE_OFFERING_WAITING_LIST.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING_WAITING_LIST.PK_STUDENT_MASTER = '$_REQUEST[sid]' AND S_COURSE_OFFERING_WAITING_LIST.PK_STUDENT_ENROLLMENT IN($_REQUEST[eid]) AND S_COURSE_OFFERING_WAITING_LIST.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE "); //Ticket #1221 // Ticket # 1442 Ticket # 1740 
				while (!$res_waiting->EOF) {
					$PK_STUDENT_ENROLLMENT = $res_waiting->fields['PK_STUDENT_ENROLLMENT'];
					$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); ?>
					<tr>
						<td>
							<div style="color:#FF0000;"><?= $res_waiting->fields['BEGIN_DATE'] ?></div>
						</td>
						<td>
							<div style="color:#FF0000;"><?= $res_waiting->fields['COURSE_CODE'] . " " . $res_waiting->fields['TRANSCRIPT_CODE'] ?></div>
						</td> <!-- Ticket # 1740  -->
						<td>
							<div style="color:#FF0000;"><?= substr($res_waiting->fields['SESSION'], 0, 1) . '-' . $res_waiting->fields['SESSION_NO'] ?></div>
						</td> <!-- Ticket # 1740  -->
						<td>
							<div style="color:#FF0000;"><?= WAITING_LIST ?></div>
						</td>
						<td></td>
						<td></td>
						<td></td>

						<!-- Ticket # 1240   -->
						<? if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) { ?>
							<td></td>
						<? }
						if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) { ?>
							<td></td>
						<? } ?>
						<!-- Ticket # 1240   -->

						<td><?= $res_type->fields['BEGIN_DATE_1'] . ' - ' . $res_type->fields['CODE'] . ' - ' . $res_type->fields['STUDENT_STATUS'] ?></td>
						<td>
							<!--  Ticket # 1442  -->
							<a href="javascript:void(0);" onclick="delete_row('<?= $res_waiting->fields['PK_COURSE_OFFERING_WAITING_LIST'] ?>','stu_course_wait')" title="<?= DELETE ?>" class="btn delete-color btn-circle" style="width:25px; height:25px; padding: 2px;"><i class="far fa-trash-alt"></i> </a>

							<a href="course_offering?id=<?= $res_waiting->fields['PK_COURSE_OFFERING'] ?>" title="Course" class="btn consolidate-color btn-circle" style="width:25px; height:25px; padding: 2px; background-color: #220A25 !important;"><i class="mdi mdi-book-open-page-variant"></i> </a>
							<!--  Ticket # 1442  -->
						</td>
					</tr>
		<? $res_waiting->MoveNext();
				}
			}
		}
		if ($c_in_cu_gnu > 0 && $c_in_cu_tot > 0) {
			$gpa11 = number_format_value_checker(($c_in_cu_gnu / $c_in_cu_tot), 2);
		}


		// $GPA = 0;
		// if($Numerator1 > 0 && $Denominator > 0)
		// 	$GPA = $Numerator1/$Denominator;  
		?>
	</tbody>
	<tfoot>
		<!-- Ticket # 1240   -->
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td><?= TOTAL ?></td>
			<td>
				<div style="padding-top: 11px;width:100%;text-align:right"><?= UNITS_INPROGRESS . ': ' . number_format_value_checker($c_in_prog_tot, 2) ?></div>
			</td>
			<td>
				<div style="padding-top: 11px;width:100%;text-align:right"><?= number_format_value_checker($c_in_att_tot, 2) ?></div>
			</td>
			<td>
				<div style="padding-top: 11px;width:100%;text-align:right"><?= number_format_value_checker($c_in_comp_tot, 2) ?></div>
			</td>

			<td colspan="2">
				<div style="padding-top: 11px;width:100%;"><?= GPA . ': ' . number_format_value_checker(($summation_of_gpa / $summation_of_weight), 2) ?></div>
			</td>
			<? if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) { ?>
				<td></td>
			<? } ?>
			<td></td>
		</tr>
		<!-- Ticket # 1240   -->
	</tfoot>
</table>