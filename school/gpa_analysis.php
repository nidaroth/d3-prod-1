<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "50000000");
set_time_limit(0);
if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST) || $_GET['p'] == 'r'){ //Ticket # 1195
	//echo "<pre>";print_r($_POST);exit;
	
	/* Ticket # 1195 */
	if($_GET['eid'] != '') {
		$_POST['PK_STUDENT_ENROLLMENT'] = explode(",",$_GET['eid']);
		$_POST['FORMAT']				= 1;
	}
	/* Ticket # 1195 */
	
	if($_GET['format'] != '')
		$_POST['FORMAT'] = $_GET['format'];
	
	$campus_name 	= "";
	$campus_cond 	= "";
	$campus_cond1 	= "";
	$campus_id	 	= "";
	if($_GET['campus'] != ''){
		$PK_CAMPUS 	 = $_GET['campus'];
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	if($_POST['PK_STUDENT_ENROLLMENT'] != '') {
		$PK_STUDENT_ENROLLMENT = implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
		
		if($_POST['SELECT_ENROLLMENT'] == 1) {
			$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT)");
			while (!$res->EOF) { 
				$PK_STUDENT_MASTER_ARR[] = $res->fields['PK_STUDENT_MASTER'];
				
				$res->MoveNext();
			}
			
			$PK_STUDENT_MASTER = implode(",",$PK_STUDENT_MASTER_ARR);
		}
		
		$query = "select S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUD_NAME, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, STUDENT_ID, CAMPUS_CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, STUDENT_STATUS    
		FROM 
		S_STUDENT_MASTER 
		LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
		, S_STUDENT_ENROLLMENT 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		WHERE 
		S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) 
		ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC  ";
		
		//echo $query;exit;
		
		$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
		if($_POST['FORMAT'] == 1){
			/////////////////////////////////////////////////////////////////
			$browser = '';
			if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
				$browser =  "chrome";
			else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
				$browser = "Safari";
			else
				$browser = "firefox";
			require_once('../global/tcpdf/config/lang/eng.php');
			require_once('../global/tcpdf/tcpdf.php');

				
			class MYPDF extends TCPDF {
				public function Header() {
					global $db, $campus_cond;
					
					$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					
					if($res->fields['PDF_LOGO'] != '') {
						$ext = explode(".",$res->fields['PDF_LOGO']);
						$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
					}
					
					$this->SetFont('helvetica', '', 15);
					$this->SetY(8);
					$this->SetX(55);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
					
					$this->SetFont('helvetica', 'I', 20);
					$this->SetY(8);
					$this->SetX(243);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(55, 8, "GPA Analysis", 0, false, 'L', 0, '', 0, false, 'M', 'L');
					
					
					$this->SetFillColor(0, 0, 0);
					$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
					$this->Line(180, 13, 290, 13, $style);
	
					$str = "";
					if($_POST['GPA_TYPE'] == 1) {
						$str = "Greater Than or Equal To ".$_POST['GPA_VALUE'];
					} else if($_POST['GPA_TYPE'] == 2) {
						$str = "Less Than or Equal To ".$_POST['GPA_VALUE'];
					}
					
					if($_GET['min_gpa'] != '' && $_GET['max_gpa'] != '' ){ 
						$str = "GPA Between ".$_GET['min_gpa']." and ".$_GET['max_gpa'];
					} else if($_GET['min_gpa'] != '')
						$str = "Minimum GPA ".$_GET['min_gpa'];
					else if($_GET['max_gpa'] != '')
						$str = "Maximum GPA ".$_GET['min_gpa'];
				
					$this->SetFont('helvetica', 'I', 10);
					$this->SetY(16);
					$this->SetX(185);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
					
					$cond = "";
					$res_campus = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE S_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
					while (!$res_campus->EOF) {
						if($campus_name != '')
							$campus_name .= ', ';
						$campus_name .= $res_campus->fields['CAMPUS_CODE'];
					
						$res_campus->MoveNext();
					}
					
					$this->SetFont('helvetica', 'I', 11);
					$this->SetY(20);
					$this->SetX(185);
					$this->SetTextColor(000, 000, 000);
					//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
					$this->MultiCell(102, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				}
				public function Footer() {
					global $db;
					$this->SetY(-15);
					$this->SetX(270);
					$this->SetFont('helvetica', 'I', 7);
					$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
					
					$this->SetY(-15);
					$this->SetX(10);
					$this->SetFont('helvetica', 'I', 7);
					
					$timezone = $_SESSION['PK_TIMEZONE'];
					if($timezone == '' || $timezone == 0) {
						$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
						$timezone = $res->fields['PK_TIMEZONE'];
						if($timezone == '' || $timezone == 0)
							$timezone = 4;
					}
					
					$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
					$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
						
					$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
				}
			}

			$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(7, 31, 7);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->SetAutoPageBreak(TRUE, 30);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->setLanguageArray($l);
			$pdf->setFontSubsetting(true);
			$pdf->SetFont('helvetica', '', 7, '', true);
			$pdf->AddPage();

			$total 	= 0;
			$txt 	= '';

			$sub_total = 0;
			
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student</td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />ID</td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Campus</td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />First Term</td>
								<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Program</td>
								<td width="5%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Status</td>';
								
								$include_tc = 1;
				
								if(isset($_GET['exclude_tc']) && $_GET['exclude_tc'] == 1)
									$include_tc = 0;
								if($include_tc == 1)
									$txt .= '<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Units Transferred</td>';
								
						$txt .= '<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Units Attempted</td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Units In Progress</td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Units Completed</td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><br />Completion<br />Percentage</td>'; //DIAM-1559
								
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt .= '<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Numeric<br />GPA</td>';
								
								$txt .= '<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><br /><br />GPA</td>
							</tr>
						</thead>';
			$res_en = $db->Execute($query);
			while (!$res_en->EOF) { 
			
				$Denominator = 0;
				$Numerator 	 = 0;
				$Numerator1  = 0;

				// DIAM-1356
				$summation_of_gpa      = 0;
				$summation_of_weight   = 0;
				// End DIAM-1356
				
				$TOTAL_ATTEMPTED 	= 0;
				$TOTAL_IN_PROGRESS 	= 0;
				$TOTAL_COMPLETED 	= 0;
				
				$c_in_num_grade_tot = 0;
				$total_rec 			= 0;
				
				$PK_STUDENT_ENROLLMENT 	= $res_en->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER 		= $res_en->fields['PK_STUDENT_MASTER'];
				
				if($_POST['SELECT_ENROLLMENT'] == 1) {
					$course_cond = " AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'  ";
				} else 
					$course_cond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
					
				/* Ticket # 1146 */
				$include_tc = 1;
				
				if(isset($_GET['exclude_tc']) && $_GET['exclude_tc'] == 1)
					$include_tc = 0;
					
				if(isset($_POST['EXCLUDE_TRANSFERS_COURSE']) && $_POST['EXCLUDE_TRANSFERS_COURSE'] == 1)
					$include_tc = 0;
					
				$TOTAL_UNTS_TRANSFERRED = 0;
				if($include_tc == 1) {
					if($_POST['SELECT_ENROLLMENT'] == 1) {
					} else 
						$tc_cond = " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
					
					$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, 
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
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
											FROM 
												S_STUDENT_CREDIT_TRANSFER 
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
												LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS
												LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE
											WHERE 
												S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
												AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $tc_cond 
												AND SHOW_ON_TRANSCRIPT = 1 
												AND S_GRADE.CALCULATE_GPA = 1 $en_cond3 "); // Ticket # 1152
					while (!$res_tc->EOF) {
						$Denominator += $res_tc->fields['UNITS'];
						$Numerator	 += $res_tc->fields['UNITS'] * $res_tc->fields['TC_NUMERIC_GRADE']; // Ticket # 1152
						$Numerator1	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];

						// DIAM-1356
						$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
						$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 

						$summation_of_gpa     		+= $TC_GPA_VALULE;
						$summation_of_weight  		+= $TC_GPA_WEIGHT;
						// End DIAM-1356
						
						$TOTAL_UNTS_TRANSFERRED += $res_tc->fields['UNITS'];
						
						$c_in_num_grade_tot += $res_tc->fields['NUMBER_GRADE'];
						$total_rec++;
						
						$res_tc->MoveNext();
					}

					
				}
				/* Ticket # 1146 */
				
				/* Ticket # 1152 */
				$res_course = $db->Execute("SELECT NUMERIC_GRADE, 
												COURSE_UNITS, 
												NUMBER_GRADE,
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
											FROM 
												S_STUDENT_COURSE 
												LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
												LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE, 
												M_COURSE_OFFERING_STUDENT_STATUS
											WHERE 
												M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
												AND SHOW_ON_TRANSCRIPT = 1 
												AND CALCULATE_GPA = 1 $course_cond ");
				while (!$res_course->EOF) {
					$Denominator += $res_course->fields['COURSE_UNITS'];
					$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
					$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

					// DIAM-1356
					$GPA_VALULE 			= $res_course->fields['GPA_VALUE']; 
					$GPA_WEIGHT 			= $res_course->fields['GPA_WEIGHT'];
		
					$summation_of_gpa 		+= $GPA_VALULE;
					$summation_of_weight 	+= $GPA_WEIGHT;
					// End DIAM-1356
					
					$c_in_num_grade_tot += $res_course->fields['NUMERIC_GRADE'];
					$total_rec++;
				
					$res_course->MoveNext();
				}

				
				
				$res_course = $db->Execute("select COURSE_CODE, COURSE_DESCRIPTION, S_STUDENT_COURSE.PK_COURSE_OFFERING,FINAL_GRADE, NUMERIC_GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS from S_STUDENT_COURSE LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE ,M_COURSE_OFFERING_STUDENT_STATUS WHERE M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1  $course_cond ");
				while (!$res_course->EOF) { 
					
					$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
					$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
					
					$COMPLETED_UNITS	 = 0;
					$ATTEMPTED_UNITS 	 = 0;
					
					if($res_course->fields['UNITS_ATTEMPTED'] == 1)
						$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
					
					$TOTAL_ATTEMPTED += $ATTEMPTED_UNITS; 
					
					if($res_course->fields['UNITS_COMPLETED'] == 1) {
						$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
						$TOTAL_COMPLETED  	+= $COMPLETED_UNITS;
					}
					
					if($res_course->fields['UNITS_IN_PROGRESS'] == 1) {
						$TOTAL_IN_PROGRESS  += $res_course->fields['COURSE_UNITS'];
					}
			
					$res_course->MoveNext();
				}
				/* Ticket # 1152 */
				
				//$GPA  = $Numerator1/$Denominator;
				$GPA = number_format_value_checker(($summation_of_gpa/$summation_of_weight),2);
				$flag = 1;
				if($_POST['GPA_TYPE'] == 1) {
					if($GPA < $_POST['GPA_VALUE'])
						$flag = 0;
				} else if($_POST['GPA_TYPE'] == 2) {
					if($GPA > $_POST['GPA_VALUE'])
						$flag = 0;
				}
				
				/* Ticket # 1361  */
				if($_GET['min_gpa'] != '' ){
					if($GPA >= $_GET['min_gpa']) {
					} else 
						$flag = 0;
				} 
				
				if($_GET['max_gpa'] != '' ){
					if($GPA <= $_GET['max_gpa']) {
					} else 
						$flag = 0;
				}
				/* Ticket # 1361  */
				
				if($flag == 1) {
					$txt 	.= '<tr>
								<td width="13%" >'.$res_en->fields['STUD_NAME'].'</td>
								<td width="8%" >'.$res_en->fields['STUDENT_ID'].'</td>
								<td width="8%" >'.$res_en->fields['CAMPUS_CODE'].'</td>
								<td width="8%"  >'.$res_en->fields['BEGIN_DATE_1'].'</td>
								<td width="12%"  >'.$res_en->fields['PROGRAM_CODE'].'</td>
								<td width="5%"  >'.$res_en->fields['STUDENT_STATUS'].'</td>';
								
								if($include_tc == 1)
									$txt .= '<td width="6%" align="right" >'.number_format_value_checker($TOTAL_UNTS_TRANSFERRED,2).'</td>';
								
					$txt 	.= '<td width="6%" align="right" >'.number_format_value_checker($TOTAL_ATTEMPTED,2).'</td>
								<td width="6%" align="right" >'.number_format_value_checker($TOTAL_IN_PROGRESS,2).'</td>
								<td width="6%" align="right" >'.number_format_value_checker($TOTAL_COMPLETED,2).'</td>';
					$txt 	.= '<td width="8%" align="right" >'.number_format_value_checker(($TOTAL_COMPLETED/$TOTAL_ATTEMPTED) * 100,2).'%</td>'; //DIAM-1559
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt 	.= '<td width="6%" align="right" >'.number_format_value_checker(($c_in_num_grade_tot/$total_rec),2).'</td>';
								
								$txt 	.= '<td width="7%" align="right" >'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</td>
							</tr>';
				}
				$res_en->MoveNext();
			}
			$txt 	.= '</table>';
			
			//echo $txt;exit;
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

			$file_name = 'GPA Analysis_'.uniqid().'.pdf';
			/*if($browser == 'Safari')
				$pdf->Output('temp/'.$file_name, 'FD');
			else	
				$pdf->Output($file_name, 'I');*/
				
				if($_GET['download_via_js'] == 'yes'){
					$outputFileName = 'temp/GPA_Analysis.pdf';
					$outputFileName = str_replace(
						pathinfo($outputFileName, PATHINFO_FILENAME),
						pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
						$outputFileName
					);
					$filename = $pdf->Output($outputFileName, 'F');
					header('Content-type: application/json; charset=UTF-8');
					$data_res = [];
					$data_res['path'] = $outputFileName;
					$data_res['filename'] = str_replace('temp/','',$outputFileName);
					echo json_encode($data_res);  
					exit;
				}else{
					$pdf->Output('temp/'.$file_name, 'FD');
				}
			
			return $file_name;	
			/////////////////////////////////////////////////////////////////
		} else if($_POST['FORMAT'] == 2){
			include '../global/excel/Classes/PHPExcel/IOFactory.php';
			$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
			define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

			$total_fields = 120;
			for($i = 0 ; $i <= $total_fields ; $i++){
				if($i <= 25)
					$cell[] = $cell1[$i];
				else {
					$j = floor($i / 26) - 1;
					$k = ($i % 26);
					//echo $j."--".$k."<br />";
					$cell[] = $cell1[$j].$cell1[$k];
				}	
			}

			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$file_name 		= 'GPA Analysis.xlsx';
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 30;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 30;
			$heading[] = 'First Term';
			$width[]   = 30;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Units Attempted';
			$width[]   = 20;
			$heading[] = 'Units In Progress';
			$width[]   = 20;
			$heading[] = 'Units Completed';
			$width[]   = 20;
			$heading[] = 'GPA';
			$width[]   = 20;
			
			if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){
				$heading[] = 'Numeric GPA';
				$width[]   = 20;
			}
			
			$include_tc = 1;
				
			if(isset($_GET['exclude_tc']) && $_GET['exclude_tc'] == 1)
				$include_tc = 0;
				
			if(isset($_POST['EXCLUDE_TRANSFERS_COURSE']) && $_POST['EXCLUDE_TRANSFERS_COURSE'] == 1)
				$include_tc = 0;
				
			if($include_tc == 1) {
				$heading[] = 'Hours Transferred';
				$width[]   = 20;
				$heading[] = 'Prep Hours Transferred';
				$width[]   = 20;
				$heading[] = 'Units Transferred';
				$width[]   = 20;
				$heading[] = 'FA Units Transferred';
				$width[]   = 20;
			}

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				
				$i++;
			}

			$res_en = $db->Execute($query);
			while (!$res_en->EOF) { 

				$Denominator = 0;
				$Numerator 	 = 0;
				$Numerator1  = 0;

				// DIAM-1356
				$summation_of_gpa      = 0;
				$summation_of_weight   = 0;
				// End DIAM-1356
				
				$TOTAL_ATTEMPTED 	= 0;
				$TOTAL_IN_PROGRESS 	= 0;
				$TOTAL_COMPLETED 	= 0;
				
				$c_in_num_grade_tot = 0;
				$total_rec 			= 0;
				
				$PK_STUDENT_ENROLLMENT 	= $res_en->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER 		= $res_en->fields['PK_STUDENT_MASTER'];
				
				if($_POST['SELECT_ENROLLMENT'] == 1) {
					$course_cond = " AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'  ";
				} else 
					$course_cond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
					
				/* Ticket # 1146 */
				$include_tc = 1;
				
				if(isset($_GET['exclude_tc']) && $_GET['exclude_tc'] == 1)
					$include_tc = 0;
					
				if(isset($_POST['EXCLUDE_TRANSFERS_COURSE']) && $_POST['EXCLUDE_TRANSFERS_COURSE'] == 1)
					$include_tc = 0;
					
				if($include_tc == 1) {
					if($_POST['SELECT_ENROLLMENT'] == 1) {
					} else 
						$tc_cond = " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
					
					
					$TOTAL_HOURS_TRASFERRED 		= 0;
					$TOTAL_PREP_HOURS_TRASFERRED 	= 0;
					$TOTAL_UNIT_TRASFERRED 			= 0;
					$TOTAL_FA_UNIT_TRASFERRED 		= 0;
					
					$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, 
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
												S_STUDENT_CREDIT_TRANSFER.HOUR, 
												S_STUDENT_CREDIT_TRANSFER.PREP, 
												S_STUDENT_CREDIT_TRANSFER.FA_UNITS,
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
											FROM 
												S_STUDENT_CREDIT_TRANSFER 
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
												LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
												LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE
											WHERE 
												S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
												AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $tc_cond 
												AND SHOW_ON_TRANSCRIPT = 1 
												AND S_GRADE.CALCULATE_GPA = 1 $en_cond3 "); // Ticket # 1152
					while (!$res_tc->EOF) {
						$Denominator += $res_tc->fields['UNITS'];
						$Numerator	 += $res_tc->fields['UNITS'] * $res_tc->fields['TC_NUMERIC_GRADE']; // Ticket # 1152
						$Numerator1	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];

						// DIAM-1356
						$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
						$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
						// End DIAM-1356

						// DIAM-1356
						$summation_of_gpa     		+= $TC_GPA_VALULE;
						$summation_of_weight  		+= $TC_GPA_WEIGHT;
						// End DIAM-1356
						
						$TOTAL_HOURS_TRASFERRED 		+= $res_tc->fields['HOUR'];
						$TOTAL_PREP_HOURS_TRASFERRED 	+= $res_tc->fields['PREP'];
						$TOTAL_UNIT_TRASFERRED 			+= $res_tc->fields['UNITS'];
						$TOTAL_FA_UNIT_TRASFERRED 		+= $res_tc->fields['FA_UNITS'];
						
						$c_in_num_grade_tot += $res_tc->fields['NUMBER_GRADE'];
						$total_rec++;
						
						$res_tc->MoveNext();
					}
					
					
					
				}
				/* Ticket # 1146 */
				
				/* Ticket # 1152 */
				$res_course = $db->Execute("SELECT 
												NUMERIC_GRADE, 
												COURSE_UNITS, 
												NUMBER_GRADE,
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
											FROM 
												S_STUDENT_COURSE 
												LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE
												LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE,  
												M_COURSE_OFFERING_STUDENT_STATUS
											WHERE 
												M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
												AND SHOW_ON_TRANSCRIPT = 1 
												AND CALCULATE_GPA = 1 $course_cond ");
				while (!$res_course->EOF) {
					$Denominator += $res_course->fields['COURSE_UNITS'];
					$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
					$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];

					// DIAM-1356
					$GPA_VALULE 			= $res_course->fields['GPA_VALUE']; 
					$GPA_WEIGHT 			= $res_course->fields['GPA_WEIGHT'];
					// End DIAM-1356 

					// DIAM-1356
					$summation_of_gpa     	+= $GPA_VALULE;
					$summation_of_weight  	+= $GPA_WEIGHT;
					// End DIAM-1356
					
					$c_in_num_grade_tot += $res_course->fields['NUMERIC_GRADE'];
					$total_rec++;
				
					$res_course->MoveNext();
				}

				
				
				$res_course = $db->Execute("select COURSE_CODE, COURSE_DESCRIPTION, S_STUDENT_COURSE.PK_COURSE_OFFERING,FINAL_GRADE, NUMERIC_GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS from S_STUDENT_COURSE LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE ,M_COURSE_OFFERING_STUDENT_STATUS WHERE M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1  $course_cond ");
				while (!$res_course->EOF) { 
					
					$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
					$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
					
					$COMPLETED_UNITS	 = 0;
					$ATTEMPTED_UNITS 	 = 0;
					
					if($res_course->fields['UNITS_ATTEMPTED'] == 1)
						$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
					
					$TOTAL_ATTEMPTED += $ATTEMPTED_UNITS; 
					
					if($res_course->fields['UNITS_COMPLETED'] == 1) {
						$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
						$TOTAL_COMPLETED  	+= $COMPLETED_UNITS;
					}
					
					if($res_course->fields['UNITS_IN_PROGRESS'] == 1) {
						$TOTAL_IN_PROGRESS  += $res_course->fields['COURSE_UNITS'];
					}
			
					$res_course->MoveNext();
				}
				/* Ticket # 1152 */
				
				//$GPA  = $Numerator1/$Denominator;
				$GPA = number_format_value_checker(($summation_of_gpa/$summation_of_weight),2);
				$flag = 1;
				if($_POST['GPA_TYPE'] == 1) {
					if($GPA < $_POST['GPA_VALUE'])
						$flag = 0;
				} else if($_POST['GPA_TYPE'] == 2) {
					if($GPA > $_POST['GPA_VALUE'])
						$flag = 0;
				}
				
				/* Ticket # 1361  */
				if($_GET['min_gpa'] != '' ){
					if($GPA >= $_GET['min_gpa']) {
					} else 
						$flag = 0;
				} 
				
				if($_GET['max_gpa'] != '' ){
					if($GPA <= $_GET['max_gpa']) {
					} else 
						$flag = 0;
				}
				/* Ticket # 1361  */
				
				if($flag == 1) {
				
					$line++;
					$index = -1;
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['STUD_NAME']);

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['STUDENT_ID']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['CAMPUS_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['BEGIN_DATE_1']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['PROGRAM_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_en->fields['STUDENT_STATUS']);

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_ATTEMPTED);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_IN_PROGRESS);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_COMPLETED);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker(($summation_of_gpa/$summation_of_weight),2)); // DIAM-1356
					
					if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker((number_format_value_checker(($c_in_num_grade_tot/$total_rec),2)),2));
					}
					
					if($include_tc == 1) {
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_HOURS_TRASFERRED);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_PREP_HOURS_TRASFERRED);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_UNIT_TRASFERRED);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_FA_UNIT_TRASFERRED);
					}
				}
				
				$res_en->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			if($_GET['download_via_js'] == 'yes'){
				header('Content-type: application/json; charset=UTF-8');
				$data_res = [];
				$data_res['path'] = $outputFileName;
				$data_res['filename'] = str_replace('temp/','',$outputFileName);
				echo json_encode($data_res);  
				exit;
			}else{
				header("location:".$outputFileName);
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_GPA_ANALYSIS?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_GPA_ANALYSIS?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="GPA_TYPE" name="GPA_TYPE"  class="form-control" onchange="show_gpa(this.value)" >
												<option value="">All GPA</option>
												<option value="1">Greater Than or Equal To</option>
												<option value="2">Less Than or Equal To</option>
											</select>
										</div>
										<div class="col-md-2 ">
											<input type="text" name="GPA_VALUE" id="GPA_VALUE" class="form-control" placeholder="GPA Value" style="display:none" >
										</div>
									</div>
									<div class="row">
										<div class="col-md-2">
											Select Enrollment
											<select id="SELECT_ENROLLMENT" name="SELECT_ENROLLMENT" class="form-control" >
												<option value="1" >All Enrollments</option>
												<option value="2" >Current Enrollments</option>
											</select>
										</div>
										<!-- Ticket # 1146 -->
										<div class="col-md-2 align-self-center ">
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE_TRANSFERS_COURSE" name="EXCLUDE_TRANSFERS_COURSE" value="1" >
												<label class="custom-control-label" for="EXCLUDE_TRANSFERS_COURSE" ><?=EXCLUDE_TRANSFERS_COURSE?></label>
											</div>
										</div>
										<!-- Ticket # 1146 -->
										
										<div class="col-md-6 align-self-center "></div>
										<div class="col-md-2 ">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<!-- New -->
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									<br />
									<div id="student_div" >
										<? //$_REQUEST['show_check'] = 1;
										//require_once('ajax_search_student_for_reports.php'); ?>
									</div>
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	
	<script type="text/javascript">
		
		function search(){
			jQuery(document).ready(function($) {
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&show_check=1';
				var value = $.ajax({
					url: "ajax_search_student_for_reports",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
						document.getElementById('SEARCH_SELECT_ALL').checked = true;
						fun_select_all()
					}		
				}).responseText;
			});
		}
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			show_btn()
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
	});
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	
	function show_gpa(val){
		if(val == '')
			document.getElementById('GPA_VALUE').style.display = 'none';
		else
			document.getElementById('GPA_VALUE').style.display = 'block';
	}
	</script>

</body>

</html>
