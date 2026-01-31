<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");
require_once("function_transcript_header.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_MASTER_ARR = array();
if(!empty($_POST)){
	/* Ticket # 1214 */
	$PK_STUDENT_MASTER_ARR = array();
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
		$PK_STUDENT_MASTER_ARR[] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}
	/* Ticket # 1214 */
	
	$PK_TERM_MASTER	= $_POST['PK_TERM_MASTER'];
} else if($_GET['id'] != ''){
	$PK_STUDENT_MASTER_ARR[] = $_GET['id'];
	$PK_TERM_MASTER			 = $_GET['term'];
}

if(!empty($PK_STUDENT_MASTER_ARR)){
	$browser = '';
	if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
		$browser =  "chrome";
	else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
		$browser = "Safari";
	else
		$browser = "firefox";
	require_once('../global/tcpdf/config/lang/eng.php');
	require_once('../global/tcpdf/tcpdf.php');
	require_once('../global/config.php');
	
	require_once("pdf_custom_header.php"); //Ticket # 1588
		
	class MYPDF extends TCPDF {
		public function Header() {
			global $db, $PK_TERM_MASTER	;
			
			if($_SESSION['temp_id'] == $this->PK_STUDENT_ENROLLMENT){
				$this->SetFont('helvetica', 'I', 15);
				$this->SetY(8);
				$this->SetX(10);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->SetMargins('', 25, '');
				
			} else {
				/* Ticket # 1588 */
				$CONTENT = pdf_custom_header($this->PK_STUDENT_MASTER, $this->PK_STUDENT_ENROLLMENT, 1);
				$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
				$this->SetMargins('', 45, '');
				/* Ticket # 1588 */
				
				$_SESSION['temp_id'] = $this->PK_STUDENT_ENROLLMENT;
			}
			
			$this->SetFont('helvetica', 'I', 20);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(147);
			$this->Cell(55, 8, "Student Report Card", 0, false, 'R', 0, '', 0, false, 'M', 'L');

			$this->SetFillColor(0, 0, 0);
			$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
			$this->Line(130, 13, 202, 13, $style);
			
			$str = "";
			$res_type = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_TERM_MASTER = '$PK_TERM_MASTER' ");
			$str = "Term Date: ".$res_type->fields['BEGIN_DATE'].' - '.$res_type->fields['END_DATE'];
				
			$this->SetFont('helvetica', 'I', 10);
			$this->SetY(16);
			$this->SetX(100);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		}
		public function Footer() {
			global $db;
			
			$this->SetY(-28);
			$this->SetX(10);
			$this->SetFont('helvetica', 'I', 7);
			
			$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 3");
			
			$BASE = -28 - $res_type->fields['FOOTER_LOC'];
			$this->SetY($BASE);
			$this->SetX(10);
			$this->SetFont('helvetica', '', 7);
			
			// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
			$CONTENT = nl2br($res_type->fields['CONTENT']);
			$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true); //Ticket # 1234 
			
			
			$this->SetY(-10); /* Ticket # 1212 */
			$this->SetX(180);
			$this->SetFont('helvetica', 'I', 7);
			$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			
			$this->SetY(-10); /* Ticket # 1212 */
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

	$_SESSION['temp_id'] = '';
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 31, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	
	/* Ticket # 1212 */
	$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 3");
	$BREAK_VAL = 30 + $res_type->fields['FOOTER_LOC'];
	$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);
	/* Ticket # 1212 */
	
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 9, '', true);

	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
		
		$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
		if($res->RecordCount() == 0){
			header("location:manage_student?t=".$_GET['t']);
			exit;
		}

		$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

		if($DATE_OF_BIRTH != '0000-00-00')
			$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
		else
			$DATE_OF_BIRTH = '';
			
		$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

		$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,PROGRAM_TRANSCRIPT_CODE,M_CAMPUS_PROGRAM.UNITS, M_CAMPUS_PROGRAM.HOURS, M_CAMPUS_PROGRAM.DESCRIPTION, STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1 "); 
		$PK_STUDENT_ENROLLMENT_ACTIVE 	= $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
		$PK_CAMPUS_PROGRAM 				= $res_enroll->fields['PK_CAMPUS_PROGRAM'];
		$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
		if($EXPECTED_GRAD_DATE != '0000-00-00')
			$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
		else
			$EXPECTED_GRAD_DATE = '';
		
		$pdf->STUD_NAME 			= $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME']." ".$res->fields['MIDDLE_NAME'];
		$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
		$pdf->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT_ACTIVE;
		$pdf->startPageGroup();
		$pdf->AddPage();

		$txt = '<div style="border-bottom:3px solid #000" ></div>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td style="width:40%" >
							'.trim($res->fields['LAST_NAME']).', '.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].'<br />'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'<br />'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'<br />'.$res_address->fields['COUNTRY'].'
						</td>
						<td style="width:60%" >
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td width="34%" >
										'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
									<td width="100%" width="34%" >
										'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
									<td width="100%" width="32%" >
										'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
								</tr>
								<tr>
									<td width="34%" >
										'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
									<td  width="34%" >
										'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
									<td  width="32%" >
										'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
								</tr>
								<tr>
									<td width="34%" >
										'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
									<td  width="34%" >
										'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
									<td  width="32%" width="32%"  >
										'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ").'
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<div style="border-top:3px solid #000" ></div>
				
				<br /><br />
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<thead>
						<tr>
							<td width="40%" style="border-bottom:1px solid #000;">
								<b>Course</b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b>Grade</b>
							</td>
							<td width="10%" align="right" style="border-bottom:1px solid #000;" >
								<b>Number</b>
							</td>
							<td width="15%" align="right" style="border-bottom:1px solid #000;" >
								<b>Units Attempted</b>
							</td>
							<td width="15%" align="right" style="border-bottom:1px solid #000;" >
								<b>Units Completed</b>
							</td>
							<td width="10%" align="right" style="border-bottom:1px solid #000;" >
								<b>GPA</b>
							</td>
						</tr>
					</thead>
					<tbody>';

				
				$c_in_att_tot 	= 0;
				$c_in_comp_tot 	= 0;
				$c_in_cu_gnu 	= 0;
				$c_in_gpa_tot 	= 0;
				
				$res_term = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE FROM S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_TERM_MASTER  WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_REPORT_CARD = 1 ");
				while (!$res_term->EOF) {
					$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];
					$BEGIN_DATE 	= $res_term->fields['BEGIN_DATE'];
					
					$txt .= '<tr>
								<td width="100%" ><i style="font-size:45px">Term: '.$res_term->fields['BEGIN_DATE'].'</i></td>
							</tr>';
					
					$c_in_att_sub_tot 	= 0;
					$c_in_comp_sub_tot 	= 0;
					$c_in_cu_sub_gnu 	= 0;
					$c_in_gpa_sub_tot 	= 0;

					//DIAM-781
					$course_gpa_value_total=0;
					$course_gpa_weight_total=0;
					$denominator =0;
					//DIAM-781
					$numerator	= 0; 
					// DIAM-781

					/* Ticket # 1152 */ // DIAM-781

					
					
					$res_course = $db->Execute("select TRANSCRIPT_CODE, COURSE_DESCRIPTION, S_STUDENT_COURSE.PK_COURSE_OFFERING,FINAL_GRADE, GRADE, NUMBER_GRADE,  CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS, FINAL_TOTAL_GRADE_NUMBER_GRADE,CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT from S_STUDENT_COURSE LEFT JOIN S_GRADE ON  S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE , M_COURSE_OFFERING_STUDENT_STATUS  WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_REPORT_CARD = 1  ORDER BY TRANSCRIPT_CODE ASC ");
					while (!$res_course->EOF) { 					
						$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
						$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
						
						$COMPLETED_UNITS	 = 0;
						$ATTEMPTED_UNITS	 = 0;
						
						if($res_course->fields['UNITS_ATTEMPTED'] == 1)
							$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
						
						$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
						$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
						
						if($res_course->fields['UNITS_COMPLETED'] == 1) {
							$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
							$c_in_comp_tot  	+= $COMPLETED_UNITS;
							$c_in_comp_sub_tot  += $COMPLETED_UNITS;
						}
						
						$gnu = 0;
						$gpa = 0;
						if($res_course->fields['CALCULATE_GPA'] == 1) {
							$gnu 				 = $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; 
							$c_in_cu_gnu 		+= $gnu; 
							$c_in_cu_sub_gnu 	+= $gnu; 
							
							$gpa				= $gnu / $COMPLETED_UNITS;;
							$c_in_gpa_sub_tot 	+= $gpa;
							$c_in_gpa_tot 		+= $gpa;


							
							/// calulated gpa DIAM-781
							$C_GPA_VALULE 				 = $res_course->fields['GPA_VALUE']; 
							$course_gpa_value_total 		+= $C_GPA_VALULE; 
							$C_GPA_WEIGHT 				 = $res_course->fields['GPA_WEIGHT']; 
							$course_gpa_weight_total 		+= $C_GPA_WEIGHT; 
							
							/**
							 * Numerator = Unit attempted * grade number  (Also consider weighted grades) 
							 *  Denominator = Term total (sum of unit attempted) 
							 *  GPA = numerator/denominator
							 * 
							 */
							// if($res_course->fields['UNITS_ATTEMPTED'] == 1){
								
							// 	$numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; 
							// 	//$tot_numerator	+= $numerator; 

							// 	$ATTEMPTED_UNITS_VAL = $res_course->fields['COURSE_UNITS'];
							// 	$denominator 	+= $ATTEMPTED_UNITS_VAL; //DIAM-781

							// }

							// calulated gpa DIAM-781
						}
					
						$txt .= '<tr>
									<td width="10%" >'.$res_course->fields['TRANSCRIPT_CODE'].'</td>
									<td width="30%" >'.$res_course->fields['COURSE_DESCRIPTION'].'</td>
									
									<td width="10%" align="right" >'.$res_course->fields['GRADE'].'</td>
									<td width="10%" align="right" >'.$res_course->fields['NUMBER_GRADE'].'</td>
									<td width="15%" align="right" >'.number_format_value_checker($ATTEMPTED_UNITS,2).'</td>
									<td width="15%" align="right" >'.number_format_value_checker($COMPLETED_UNITS,2).'</td>
									<td width="10%" align="right" ></td>
								</tr>';
						
						$res_course->MoveNext();
					} 
					/* Ticket # 1152 */
					
					//$GPA = '';
					// if($c_in_comp_tot > 0)
					// 	$GPA = number_format_value_checker(($c_in_cu_gnu / $c_in_comp_tot),2);
				
					//DIAM-781
					$GPA=0;
					if($course_gpa_value_total>0)
					{
						$GPA =$course_gpa_value_total/$course_gpa_weight_total;
					}

					// if($numerator>0){
					// 	$GPA = $numerator/$denominator;
					// }
					
						
						/* Ticket # 1478 */
					// $txt .= '<tr>
					// 		<td width="60%" align="right" ><b>Term Total:</b></td>
					// 		<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_att_sub_tot,2).'</b></td>
					// 		<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_comp_sub_tot,2).'</b></td>
					// 		<td width="10%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($GPA,2).'</b></td>
					// 	</tr>
					// 	<tr>
					// 		<td width="60%" align="right" ><b>Cumulative Total:</b></td>
					// 		<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_att_tot,2).'</b></td>
					// 		<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_comp_tot,2).'</b></td>
					// 		<td width="10%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($GPA,2).'</b></td>
					// 	</tr>';
					//DIAM-781
					$txt .= '<tr>
							<td width="60%" align="right" ><b>Term Total:</b></td>
							<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_att_sub_tot,2).'</b></td>
							<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_comp_sub_tot,2).'</b></td>
							<td width="10%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($GPA,2).'</b></td>
						</tr>';
						/* Ticket # 1478 */
					//DIAM-781
					$res_term->MoveNext();
				}
				
				$c_in_att_tot 	= 0;
				$c_in_comp_tot 	= 0;
				$c_in_cu_gnu 	= 0;
				$c_in_gpa_tot 	= 0;
				
				$c_in_gpa_sub_tot 	= 0;

				//DIAM-781
				$tc_gpa_value_total=0;
				$tc_gpa_weight_total=0;
				//DIAM-781
				
				/* Ticket #1146 */
				$include_tc = 1;
				
				if(isset($_GET['exclude_tc']) && $_GET['exclude_tc'] == 1)
					$include_tc = 0;
					
				if(isset($_POST['EXCLUDE_TRANSFERS_COURSE']) && $_POST['EXCLUDE_TRANSFERS_COURSE'] == 1)
					$include_tc = 0;
					
				if($include_tc == 1) { 		
					/* Ticket # 1152 */ // DIAM-781
					$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, CREDIT_TRANSFER_STATUS, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, S_GRADE.GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_GRADE.NUMBER_GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS,CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_GRADE ON S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND SHOW_ON_TRANSCRIPT = 1  ");
					/* Ticket #1146 */									
					while (!$res_tc->EOF) {
						
						$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT']; 
						$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
						$COMPLETED_UNITS	 	= 0;
						
						if($res_tc->fields['UNITS_ATTEMPTED'] == 1)
							$ATTEMPTED_UNITS = $res_tc->fields['UNITS'];
						else
							$ATTEMPTED_UNITS = 0;
			
						$c_in_att_tot += $ATTEMPTED_UNITS; 
						
						if($res_tc->fields['UNITS_COMPLETED'] == 1) {
							$COMPLETED_UNITS 	 = $ATTEMPTED_UNITS;
							$c_in_comp_tot  	+= $COMPLETED_UNITS;
						}
					
						$gnu = 0;
						$gpa = '';
						if($res_tc->fields['CALCULATE_GPA'] == 1) {
							$gnu 			 = $ATTEMPTED_UNITS * $res_tc->fields['NUMBER_GRADE']; 
							$c_in_cu_gnu 	+= $gnu; 
							$gpa			= $c_in_cu_gnu / $c_in_comp_tot;

							// calulated gpa DIAM-781
							$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
							$tc_gpa_value_total 		+= $TC_GPA_VALULE; 
							$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
							$tc_gpa_weight_total 		+= $TC_GPA_WEIGHT; 
							// calulated gpa DIAM-781

						}
									
						$res_tc->MoveNext();
					}
					/* Ticket # 1152 */
				}
				
				/* Ticket # 1152 */ // DIAM-781
				$res_course = $db->Execute("select TRANSCRIPT_CODE, COURSE_DESCRIPTION, S_STUDENT_COURSE.PK_COURSE_OFFERING,FINAL_GRADE, GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS, FINAL_TOTAL_GRADE_NUMBER_GRADE,CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT from S_STUDENT_COURSE LEFT JOIN S_GRADE ON  S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY TRANSCRIPT_CODE ASC ");	
				while (!$res_course->EOF) { 
					
					$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
					$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
					
					if($res_course->fields['UNITS_ATTEMPTED'] == 1)
						$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
					else
						$ATTEMPTED_UNITS = 0;
						
					$COMPLETED_UNITS	 = 0;
					$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
					
					if($res_course->fields['UNITS_COMPLETED'] == 1) {
						$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
						$c_in_comp_tot  	+= $COMPLETED_UNITS;
					}
					
					$gnu = 0;
					$gpa = 0;
					if($res_course->fields['CALCULATE_GPA'] == 1) {
						$gnu 				 = $ATTEMPTED_UNITS * $res_course->fields['NUMBER_GRADE']; 
						$c_in_cu_gnu 		+= $gnu; 
						
						$gpa				= $gnu / $COMPLETED_UNITS;
						$c_in_gpa_tot 		+= $gpa;

						// calulated gpa DIAM-781
						$TC_GPA_VALULE 				 = $res_course->fields['GPA_VALUE']; 
						$tc_gpa_value_total 		+= $TC_GPA_VALULE; 
						$TC_GPA_WEIGHT 				 = $res_course->fields['GPA_WEIGHT']; 
						$tc_gpa_weight_total 		+= $TC_GPA_WEIGHT; 
						// calulated gpa DIAM-781

					}
					
					$res_course->MoveNext();
				}
				/* Ticket # 1152 */
				
				// $GPA = '';
				// if($c_in_comp_tot > 0)
				// 	$GPA = number_format_value_checker(($c_in_cu_gnu / $c_in_comp_tot),2);


				//DIAM-781
				$tc_gpa_weighted=0;
				if($tc_gpa_value_total>0)
				{
					$tc_gpa_weighted=$tc_gpa_value_total/$tc_gpa_weight_total;
					$GPA= number_format_value_checker($tc_gpa_weighted,2);
				}
				
				//DIAM-781
				// $txt .= '<tr>
				// 			<td width="60%" align="right" ><b>Student Total:</b></td>
				// 			<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_att_tot,2).'</b></td>
				// 			<td width="15%" style="border-top:1px solid #000;" align="right"><b>'.number_format_value_checker($c_in_comp_tot,2).'</b></td>
				// 			<td width="10%" style="border-top:1px solid #000;" align="right"><b>'.$GPA.'</b></td>
				// 		</tr>
				// 	</tbody>
				// </table>';

				$txt .= '</tbody>
				</table>';
				//DIAM-781
			/* Ticket # 1187 */	
			/* Ticket # 1219 */
			if($_GET['inc_att'] == 1 || $_POST['DISPLAY_ATTENDNACE'] == 1) {
				$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
				$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
				
				$excluded_att_code  = "";
				$exc_att_code_arr = array();
				$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
				while (!$res_exc_att_code->EOF) {
					$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
					$res_exc_att_code->MoveNext();
				}

				$exclude_cond  = "";
				if(!empty($exc_att_code_arr))
					$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (".implode(",",$exc_att_code_arr).") ";
				
				$res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT_ACTIVE) ");
				
				//$res_sch_all = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 $exclude_cond AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT_ACTIVE) "); 
	
				$SCHEDULED_HOUR 	 = 0;
				$COMP_SCHEDULED_HOUR = 0;
				$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT_ACTIVE) "); 
				while (!$res_sch->EOF) { 
					$exc_att_flag = 0;
					foreach($exc_att_code_arr as $exc_att_code) {
						if($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
							$exc_att_flag = 1;
							break;
						}
					}
					if($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0){
						$SCHEDULED_HOUR += $res_sch->fields['HOURS'];
					
						if($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2) {
							$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];	
						}
					}	
					$res_sch->MoveNext();
				}
				
				$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT_ACTIVE) AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
				
				$res_attended_all = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT_ACTIVE) AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code)  ");

				$txt .= '<br /><br /><br /><br />
				<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr nobr="true" >
						<td><b>Attendance Summary</b><br /></td>
					</tr>
					<tr nobr="true" >
						<td width="15%" ></td>
						<td width="70%" >
							<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								<tr>
									<td width="100%" >
										<table border="1" cellspacing="0" cellpadding="3" width="100%" >
											<tr>
												<td width="32%" >Total Required Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_enroll->fields['HOURS'],2).'</td>
												<td width="32%" >Total Attended Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_attended_all->fields['ATTENDED_HOUR'],2).'</td>
											</tr>
											<tr>
												<td width="32%" >Total Transfer Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_trans->fields['HOUR'],2).'</td>
												<td width="32%" >Total Hours Remaining</td>
												<td width="13%" align="right" >'.number_format_value_checker(($res_enroll->fields['HOURS'] - $res_attended->fields['ATTENDED_HOUR'] - $res_trans->fields['HOUR']),2).'</td>
											</tr>
											<tr>
												<td width="32%" >Total Scheduled Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($SCHEDULED_HOUR,2).'</td>
												<td width="32%" >Attendance Percentage</td>
												<td width="13%" align="right" >'.number_format_value_checker(($res_attended->fields['ATTENDED_HOUR'] / $COMP_SCHEDULED_HOUR * 100),2).'%</td>
											</tr>
										</table>									
									</td>
								</tr>
							</table>
						</td>
						<td width="15%" ></td>
					</tr>
				</table>';
			}
			/* Ticket # 1187 */
			
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}
	
	$file_name = 'Student Report Card.pdf';
	/*
	if($browser == 'Safari')
		$pdf->Output('temp/'.$file_name, 'FD');
	else	
		$pdf->Output($file_name, 'I');
	*/	
	$pdf->Output('temp/'.$file_name, 'FD');
	return $file_name;	
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
	<title><?=MNU_REPORT_CARD?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		/* Ticket # 715 */
		.dropdown-menu>li>a {
			white-space: nowrap;
		}
		#PK_CAMPUS_PROGRAM .multiselect-container{
			overflow-x: scroll;
			max-width: 650px !important;		
		}
		/* Ticket # 715 */
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
                        <h4 class="text-themecolor"><?=MNU_REPORT_CARD?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
									<!-- DIAM-1806 -->
									<div class="col-md-2">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- DIAM-1806 -->
										<div class="col-md-2 ">
											<?=TERM?>
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER"  class="form-control" onchange="search()" > <!-- Ticket # 1212 -->
												<option value=""></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 715 -->
										<div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV" >
										<lable><?=PROGRAM ?></lable>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="search()" >
											<option value="0">All</option>

												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$res_type->fields['CODE'].' - '.$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 715 -->

										<!-- Ticket # 1187 -->
										<div class="col-md-2 ">
											<?=DISPLAY_ATTENDNACE?>
											<select id="DISPLAY_ATTENDNACE" name="DISPLAY_ATTENDNACE"  class="form-control" >
												<option value="2" ><?=NO ?></option>
												<option value="1" ><?=YES ?></option>
											</select>
										</div>
										<!-- Ticket # 1187 -->
										
										<!-- Ticket # 1146 -->
										<div class="col-md-2 align-self-center ">
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE_TRANSFERS_COURSE" name="EXCLUDE_TRANSFERS_COURSE" value="1" <? if($EXCLUDE_TRANSFERS_COURSE == 1) echo "checked"; ?> >
												<label class="custom-control-label" for="EXCLUDE_TRANSFERS_COURSE" ><?=EXCLUDE_TRANSFERS_COURSE?></label>
											</div>
										</div>
										<!-- Ticket # 1146 -->
										
										<div class="col-md-2 ">
											<br />
											<button type="submit" class="btn waves-effect waves-light btn-info" id="btn_1" style="display:none;" ><?=PDF?></button> <!-- Ticket # 1212 -->
										</div>
										
									</div>
									
									<!-- Ticket # 1212 -->
									<br />
									<div id="student_div" >
									</div>
									<!-- Ticket # 1212 -->
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	/* Ticket # 715 */
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?= PROGRAM ?>',
			nonSelectedText: '<?= PROGRAM ?>',
			numberDisplayed: 2,
			nSelectedText: '<?= PROGRAM ?> selected'
		});

		//DIAM-1806
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		//DIAM-1806

	});
	/* Ticket # 715 */
	/* Ticket # 1212 */
	function search(){
		jQuery(document).ready(function($) {
			var data  = 'COURSE_PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+ '&PK_CAMPUS_PROGRAM=' + $('#PK_CAMPUS_PROGRAM').val()+'&show_check=1&show_count=1&PK_CAMPUS='+$('#PK_CAMPUS').val(); /* Ticket # 715 DIAM-1806 */ 
			var value = $.ajax({
				url: "ajax_search_student_for_reports",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('student_div').innerHTML = data
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
		get_count()
	}
	
	function show_btn(){
		
		var flag = 0;
		var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
		for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
			if(PK_STUDENT_ENROLLMENT[i].checked == true) {
				flag++;
				break;
			}
		}
		
		if(flag == 1) {
			document.getElementById('btn_1').style.display = 'inline';
		} else {
			document.getElementById('btn_1').style.display = 'none';
		}
	}
	
	function get_count(){
		var tot = 0
		var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
		for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
			if(PK_STUDENT_ENROLLMENT[i].checked == true)
				tot++;
		}
		document.getElementById('SELECTED_COUNT').innerHTML = tot
		show_btn()
	}
	/* Ticket # 1212 */
	</script>
</body>

</html>
