<?php require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("../language/program_course_progress.php");
require_once("function_transcript_header.php");


ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "50000000");
set_time_limit(0);

$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";

require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {

	public function Header() {
		global $db;
		
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
		
		$this->SetFont('helvetica', 'I', 17);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(137);
		$this->Cell(55, 8, "Program Course Progress", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(135, 13, 208, 13, $style);

		/*$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(152);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 5, 'Pursuit of Program', 0, false, 'R', 0, '', 0, false, 'M', 'L');*/
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
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

$_SESSION['temp_id'] = '';
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 30, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 7, '', true);


$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$LOGO = '';
if($res->fields['PDF_LOGO'] != '')
	$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';
	
$PK_STUDENT_ENROLLMENT_ARRAY = explode(",",$_GET['eid']);

foreach($PK_STUDENT_ENROLLMENT_ARRAY as $PK_STUDENT_ENROLLMENT){
	
	$res_en = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER, PK_STUDENT_ENROLLMENT, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE,SESSION, BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS, UNITS, HOURS, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM  FROM 
	S_STUDENT_ENROLLMENT 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	, S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER ");
	
	$PK_STUDENT_MASTER = $res_en->fields['PK_STUDENT_MASTER'];
	$PK_CAMPUS_PROGRAM = $res_en->fields['PK_CAMPUS_PROGRAM'];
	
	$res_course = $db->Execute("SELECT GROUP_CONCAT(PK_COURSE) as PK_COURSE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND ACTIVE = 1 ");
	$PK_COURSE_ACT = $res_course->fields['PK_COURSE'];
	
	$res_course = $db->Execute("SELECT GROUP_CONCAT(PK_COURSE) as PK_COURSE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND ACTIVE = 0 ");
	$PK_COURSE_INACT = $res_course->fields['PK_COURSE'];
	
	$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
	
	$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
	$pdf->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT;
	$pdf->STUD_NAME 			= $res_en->fields['NAME'];
	$pdf->startPageGroup();
	$pdf->AddPage();
	
	$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
	$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
				<tr>
					<td width="50%">
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
							<tr>
								<td width="100%"><b style="font-size:50px" >'.$res_en->fields['NAME'].'</b></td>
							</tr>
							<tr>
								<td width="100%" ><span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span></td>
							</tr>
						</table>
					</td>
					<td width="50%">
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
							<tr>
								<td width="34%" >
									'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
								<td width="100%" width="34%" >
									'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
								<td width="100%" width="32%" >
									'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
							</tr>
							<tr>
								<td width="34%" >
									'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
								<td  width="34%" >
									'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
								<td  width="32%" >
									'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
							</tr>
							<tr>
								<td width="34%" >
									'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
								<td  width="34%" >
									'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
								<td  width="32%" width="32%"  >
									'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>';

	$txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<thead>
					<tr>
						<td width="10%" style="border-bottom:1px solid #000;" ><b><br />Term</b></td>
						<td width="14%" style="border-bottom:1px solid #000;" ><b><br />Course</b></td>
						<td width="18%" style="border-bottom:1px solid #000;" ><b>Course<br />Description</b></td>
						<td width="9%" style="border-bottom:1px solid #000;" align="right" ><b>Units<br />Required</b></td>
						<td width="9%" style="border-bottom:1px solid #000;" align="right" ><b>Units<br />Attempted</b></td>
						<td width="9%" style="border-bottom:1px solid #000;" align="right" ><b>Units<br />In Progress</b></td>
						<td width="8%" style="border-bottom:1px solid #000;" align="right" ><b>Units<br />Completed</b></td>
						<td width="8%" style="border-bottom:1px solid #000;" align="right" ><b><br />Grade</b></td>
						<td width="8%" style="border-bottom:1px solid #000;" align="right" ><b>Numeric<br />Grade</b></td>
						<td width="8%" style="border-bottom:1px solid #000;" align="right" ><b>Numeric<br />GPA</b></td>
					</tr>
				</thead>';

		$txt .= '<tr >
					<td width="100%" ><i style="font-size:45px">Program Courses - Passed</i></td>
				</tr>';
				
		$GRAND_TOT_ATTEMPTED 	 = 0;
		$GRAND_TOT_IN_PROGRESS 	 = 0;
		$GRAND_TOT_COMPLETED 	 = 0;
		$GRAND_TOT_REQUIRED 	 = 0;
		
		$GRAND_TOT_Denominator 	 = 0;
		$GRAND_TOT_Numerator	 = 0;
		$GRAND_TOT_Numerator1	 = 0;
		$GRAND_TOT_GPA	 		 = 0;	
		$GRAND_TOT_NO_GPA	 	 = 0;
		
		$TOT_ATTEMPTED 	 = 0;
		$TOT_IN_PROGRESS = 0;
		$TOT_COMPLETED 	 = 0;
		$TOT_REQUIRED 	 = 0;
		
		$TOT_Denominator = 0;
		$TOT_Numerator	 = 0;
		$TOT_Numerator1	 = 0;
		$TOT_GPA	 	 = 0;
		$TOT_NO_GPA	 	 = 0;
		
		$assigned_co = array();
		$res_course_schedule = $db->Execute("SELECT * FROM (
			select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_COMPLETED = 1 AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) 
			UNION 
			select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_GRADE.UNITS_COMPLETED = 1 AND SHOW_ON_TRANSCRIPT = 1 AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) 
			) 
		AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
		while (!$res_course_schedule->EOF) {
			$assigned_co[$res_course_schedule->fields['PK_COURSE']] = $res_course_schedule->fields['PK_COURSE'];
			
			$REQUIRED = $res_course_schedule->fields['COURSE_UNITS'];
			
			$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_REQUIRED += $res_course_schedule->fields['COURSE_UNITS'];
			
			$ATTEMPTED = '';
			if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
				$ATTEMPTED 				 = $res_course_schedule->fields['COURSE_UNITS'];
				$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
				$GRAND_TOT_ATTEMPTED 	+= $res_course_schedule->fields['COURSE_UNITS'];
			}
			
			$IN_PROGRESS = '';
			if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
				$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
				$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
				$GRAND_TOT_IN_PROGRESS 	+= $res_course_schedule->fields['COURSE_UNITS'];
			}
			
			$COMPLETED = '';
			if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
				$COMPLETED 			 = $res_course_schedule->fields['COURSE_UNITS'];
				$TOT_COMPLETED 		 += $res_course_schedule->fields['COURSE_UNITS'];
				$GRAND_TOT_COMPLETED += $res_course_schedule->fields['COURSE_UNITS'];
			}
			
			$Numerator1  = 0;
			$Denominator = 0;
			if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
				$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
				$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
				
				$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
				$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
				$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
				
				$GRAND_TOT_Denominator 	 += $res_course_schedule->fields['COURSE_UNITS'];
				$GRAND_TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
				$GRAND_TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
				
				$GRAND_TOT_NO_GPA++;
				$TOT_NO_GPA++;
				
				$GPA = 0;
				if($Numerator1 > 0 && $Denominator > 0)
					$GPA = $Numerator1/$Denominator;
					
				$TOT_GPA 		+= $GPA;
				$GRAND_TOT_GPA 	+= $GPA;
				
				$GPA = number_format_value_checker($GPA,2);
			} else
				$GPA = '';
			
			$txt .= '<tr >
						<td width="10%" >'.$res_course_schedule->fields['BEGIN_DATE_1'].'</td>
						<td width="14%" >'.$res_course_schedule->fields['COURSE_CODE'].'</td>
						<td width="18%" >'.$res_course_schedule->fields['COURSE_DESCRIPTION'].'</td>
						<td width="9%" align="right" >'.$res_course_schedule->fields['COURSE_UNITS'].'</td>
						<td width="9%" align="right" >'.$ATTEMPTED.'</td>
						<td width="9%" align="right" >'.$IN_PROGRESS.'</td>
						<td width="8%" align="right" >'.$COMPLETED.'</td>
						<td width="8%" align="right" >'.$res_course_schedule->fields['GRADE'].'</td>
						<td width="8%" align="right" >'.$res_course_schedule->fields['NUMBER_GRADE'].'</td>
						<td width="8%" align="right" >'.$GPA.'</td>
					</tr>';
				
			$res_course_schedule->MoveNext();
		}
		
		$GPA = 0;
		if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
			$GPA = number_format_value_checker(($TOT_GPA/$TOT_NO_GPA),2);
		
		$txt .= '<tr >
					<td width="10%" ></td>
					<td width="14%" ></td>
					<td width="18%" ></td>
					<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_REQUIRED,2).'</td>
					<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_ATTEMPTED,2).'</td>
					<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_IN_PROGRESS,2).'</td>
					<td width="8%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_COMPLETED,2).'</td>
					<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
					<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
					<td width="8%" align="right" style="border-top: 1px solid #000;" >'.$GPA.'</td>
				</tr>';
				
	$txt .= '<tr >
				<td width="100%" ><i style="font-size:45px">Program Courses - Not Passed</i></td>
			</tr>';

	$TOT_ATTEMPTED 	 = 0;
	$TOT_IN_PROGRESS = 0;
	$TOT_COMPLETED 	 = 0;
	$TOT_REQUIRED 	 = 0;
	
	$TOT_Denominator = 0;
	$TOT_Numerator	 = 0;
	$TOT_Numerator1	 = 0;
	
	$TOT_GPA 	= 0;
	$TOT_NO_GPA = 0;
	
	$res_course_schedule = $db->Execute("SELECT * FROM (
		select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_ATTEMPTED = 1 AND (UNITS_COMPLETED = 0 OR IS_DEFAULT = 1) AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) 
		UNION 
		select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_GRADE.UNITS_ATTEMPTED = 1 AND (S_GRADE.UNITS_COMPLETED = 0 OR S_GRADE.IS_DEFAULT = 1) AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) AND SHOW_ON_TRANSCRIPT = 1 AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
		) 
	AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
	
	//$res_course_schedule = $db->Execute("select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_ATTEMPTED = 1 AND (UNITS_COMPLETED = 0 OR IS_DEFAULT = 1) AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
	while (!$res_course_schedule->EOF) {
		$assigned_co[$res_course_schedule->fields['PK_COURSE']] = $res_course_schedule->fields['PK_COURSE'];
		
		$REQUIRED = $res_course_schedule->fields['COURSE_UNITS'];
		
		$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
		$GRAND_TOT_REQUIRED += $res_course_schedule->fields['COURSE_UNITS'];
		
		$ATTEMPTED = '';
		if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
			$ATTEMPTED 				= $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_ATTEMPTED 	+= $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$IN_PROGRESS = '';
		if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
			$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_IN_PROGRESS 	+= $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$COMPLETED = '';
		if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
			$COMPLETED 			 	= $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_COMPLETED 		 	+= $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_COMPLETED 	+= $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$Numerator1  = 0;
		$Denominator = 0;
		if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
			$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
			$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
			
			$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
			$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
			
			$GRAND_TOT_Denominator 	 += $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
			$GRAND_TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
			
			$GRAND_TOT_NO_GPA++;
			$TOT_NO_GPA++;
			
			$GPA = 0;
			if($Numerator1 > 0 && $Denominator > 0)
				$GPA = $Numerator1/$Denominator;
				
			$TOT_GPA 		+= $GPA;
			$GRAND_TOT_GPA 	+= $GPA;
				
			$GPA = number_format_value_checker($GPA, 2);
		} else
			$GPA = '';
		
		$txt .= '<tr >
					<td width="10%" >'.$res_course_schedule->fields['BEGIN_DATE_1'].'</td>
					<td width="14%" >'.$res_course_schedule->fields['COURSE_CODE'].'</td>
					<td width="18%" >'.$res_course_schedule->fields['COURSE_DESCRIPTION'].'</td>
					<td width="9%" align="right" >'.$res_course_schedule->fields['COURSE_UNITS'].'</td>
					<td width="9%" align="right" >'.$ATTEMPTED.'</td>
					<td width="9%" align="right" >'.$IN_PROGRESS.'</td>
					<td width="8%" align="right" >'.$COMPLETED.'</td>
					<td width="8%" align="right" >'.$res_course_schedule->fields['GRADE'].'</td>
					<td width="8%" align="right" >'.$res_course_schedule->fields['NUMBER_GRADE'].'</td>
					<td width="8%" align="right" >'.$GPA.'</td>
				</tr>';
			
		$res_course_schedule->MoveNext();
	}

	$PK_COURSE_ACT_ARR 	= explode(",",$PK_COURSE_ACT );
	$not_assigned_co 	= array();
	
	foreach($PK_COURSE_ACT_ARR as $PK_COURSE_ACT1){
		$found = 0;
		foreach($assigned_co as $assigned_co1) {
			if($assigned_co1 == $PK_COURSE_ACT1){
				$found = 1;
			}
		}
		if($found == 0) {
			$not_assigned_co[] = $PK_COURSE_ACT1;
		}
	}
	$not_assigned_co1 = implode(",",$not_assigned_co);

	$res_course_schedule = $db->Execute("select UNITS, COURSE_CODE, COURSE_DESCRIPTION from S_COURSE WHERE S_COURSE.PK_COURSE IN ($not_assigned_co1) AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY COURSE_CODE ASC");
	while (!$res_course_schedule->EOF) {
		
		$TOT_REQUIRED 		+= $res_course_schedule->fields['UNITS'];
		$GRAND_TOT_REQUIRED += $res_course_schedule->fields['UNITS'];
		
		$txt .= '<tr >
					<td width="10%" ></td>
					<td width="14%" >'.$res_course_schedule->fields['COURSE_CODE'].'</td>
					<td width="18%" >'.$res_course_schedule->fields['COURSE_DESCRIPTION'].'</td>
					<td width="9%" align="right" >'.$res_course_schedule->fields['UNITS'].'</td>
					<td width="9%" align="right" ></td>
					<td width="9%" align="right" ></td>
					<td width="8%" align="right" ></td>
					<td width="8%" align="right" ></td>
					<td width="8%" align="right" ></td>
					<td width="8%" align="right" ></td>
				</tr>';
			
		$res_course_schedule->MoveNext();
	}
	
	/*$GPA = 0;
	if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
		$GPA = $TOT_GPA/$TOT_NO_GPA;
	$GPA = number_format_value_checker($GPA, 2);*/
	
	$GPA = '';
	
	$txt .= '<tr >
				<td width="10%" ></td>
				<td width="14%" ></td>
				<td width="18%" ></td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_REQUIRED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_ATTEMPTED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_IN_PROGRESS,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_COMPLETED,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.$GPA.'</td>
			</tr>';
	
	$GPA = 0;
	if($GRAND_TOT_GPA > 0 && $GRAND_TOT_NO_GPA > 0)
		$GPA = $GRAND_TOT_GPA/$GRAND_TOT_NO_GPA;
	$GPA = number_format_value_checker($GPA, 2);
	
	$txt .= '<tr >
				<td width="24%" >Program Course Totals:</td>
				<td width="18%" ></td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($GRAND_TOT_REQUIRED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($GRAND_TOT_ATTEMPTED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($GRAND_TOT_IN_PROGRESS,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($GRAND_TOT_COMPLETED,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.$GPA.'</td>
			</tr>';
			
	$PURSUIT_TOT_ATTEMPTED 	 = $GRAND_TOT_ATTEMPTED;
	$PURSUIT_TOT_IN_PROGRESS = $GRAND_TOT_IN_PROGRESS;
	$PURSUIT_TOT_COMPLETED 	 = $GRAND_TOT_COMPLETED;
	$PURSUIT_TOT_REQUIRED 	 = $GRAND_TOT_REQUIRED;
	
	$PURSUIT_TOT_Denominator = $GRAND_TOT_Denominator;
	$PURSUIT_TOT_Numerator	 = $GRAND_TOT_Numerator;
	$PURSUIT_TOT_Numerator1	 = $GRAND_TOT_Numerator1;
	
	$PURSUIT_TOT_GPA 	= $GRAND_TOT_GPA;
	$PURSUIT_TOT_NO_GPA = $GRAND_TOT_NO_GPA;
	
	$TOT_ATTEMPTED 	 = 0;
	$TOT_IN_PROGRESS = 0;
	$TOT_COMPLETED 	 = 0;
	$TOT_REQUIRED 	 = 0;
	
	$TOT_GPA 	= 0;
	$TOT_NO_GPA = 0;
	
	$TOT_Denominator = 0;
	$TOT_Numerator	 = 0;
	$TOT_Numerator1	 = 0;
			
	$txt .= '<tr >
				<td width="100%" ><i style="font-size:45px">Program Courses - Inactive Requirements</i></td>
			</tr>';
	
	$res_course_schedule = $db->Execute("SELECT * FROM (
		select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE IN ($PK_COURSE_INACT) 
		UNION 
		select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE, M_CREDIT_TRANSFER_STATUS  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE IN ($PK_COURSE_INACT) AND SHOW_ON_TRANSCRIPT = 1 AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS  
		) 
	AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
	
	//$res_course_schedule = $db->Execute("select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE IN ($PK_COURSE_INACT) AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
	while (!$res_course_schedule->EOF) {

		$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
		$GRAND_TOT_REQUIRED += $res_course_schedule->fields['COURSE_UNITS'];
		
		$ATTEMPTED = '';
		if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
			$ATTEMPTED 				 = $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_ATTEMPTED 	+= $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$IN_PROGRESS = '';
		if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
			$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_IN_PROGRESS 	+= $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$COMPLETED = '';
		if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
			$COMPLETED 			 = $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_COMPLETED 		 += $res_course_schedule->fields['COURSE_UNITS'];
			$GRAND_TOT_COMPLETED += $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$Numerator1  = 0;
		$Denominator = 0;
		if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
			$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
			$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
			
			$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
			$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
			
			$TOT_NO_GPA++;
			
			$GPA = 0;
			if($Numerator1 > 0 && $Denominator > 0)
				$GPA = $Numerator1/$Denominator;
				
			$TOT_GPA += $GPA;
		} else
			$GPA = '';
		
		$txt .= '<tr >
					<td width="10%" >'.$res_course_schedule->fields['BEGIN_DATE_1'].'</td>
					<td width="14%" >'.$res_course_schedule->fields['COURSE_CODE'].'</td>
					<td width="18%" >'.$res_course_schedule->fields['COURSE_DESCRIPTION'].'</td>
					<td width="9%" align="right" >'.$res_course_schedule->fields['COURSE_UNITS'].'</td>
					<td width="9%" align="right" >'.$ATTEMPTED.'</td>
					<td width="9%" align="right" >'.$IN_PROGRESS.'</td>
					<td width="8%" align="right" >'.$COMPLETED.'</td>
					<td width="8%" align="right" >'.$res_course_schedule->fields['GRADE'].'</td>
					<td width="8%" align="right" >'.$res_course_schedule->fields['NUMBER_GRADE'].'</td>
					<td width="8%" align="right" >'.$GPA.'</td>
				</tr>';
			
		$res_course_schedule->MoveNext();
	}
	
	$GPA = 0;
	if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
		$GPA = $TOT_GPA/$TOT_NO_GPA;
	$GPA = number_format_value_checker($GPA, 2);
	
	$txt .= '<tr >
				<td width="24%" >Inactive Requirement Totals:</td>
				<td width="18%" ></td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_REQUIRED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_ATTEMPTED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_IN_PROGRESS,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_COMPLETED,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.$GPA.'</td>
			</tr>';
			
	//////////////////////	
	$TOT_ATTEMPTED 	 = 0;
	$TOT_IN_PROGRESS = 0;
	$TOT_COMPLETED 	 = 0;
	$TOT_REQUIRED 	 = 0;
	
	$TOT_GPA 	= 0;
	$TOT_NO_GPA = 0;
	
	$TOT_Denominator = 0;
	$TOT_Numerator	 = 0;
	$TOT_Numerator1	 = 0;
	
	$res_course = $db->Execute("SELECT GROUP_CONCAT(PK_COURSE) as PK_COURSE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ");
	$PK_PROG_COURSE_ALL = $res_course->fields['PK_COURSE'];
	$txt .= '<tr >
				<td width="100%" ><i style="font-size:45px">Non Program Courses</i></td>
			</tr>';	
			
	/*$res_course_schedule = $db->Execute("SELECT * FROM (
			select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_COMPLETED = 1 AND S_COURSE.PK_COURSE NOT IN ($PK_PROG_COURSE_ALL) 
			UNION 
			select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_GRADE.UNITS_COMPLETED = 1 AND S_COURSE.PK_COURSE IN ($PK_PROG_COURSE_ALL) 
			) 
		AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");*/
		
	$res_course_schedule = $db->Execute("SELECT * FROM (
		select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE NOT IN ($PK_PROG_COURSE_ALL) 
		UNION 
		select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE, M_CREDIT_TRANSFER_STATUS  WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE NOT IN ($PK_PROG_COURSE_ALL) AND SHOW_ON_TRANSCRIPT = 1 AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS    
		) 
	AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
		
	while (!$res_course_schedule->EOF) {
		
		$REQUIRED = $res_course_schedule->fields['COURSE_UNITS'];
		
		$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
		
		$ATTEMPTED = '';
		if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
			$ATTEMPTED 				 = $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$IN_PROGRESS = '';
		if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
			$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$COMPLETED = '';
		if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
			$COMPLETED 			 = $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_COMPLETED 		 += $res_course_schedule->fields['COURSE_UNITS'];
		}
		
		$Numerator1  = 0;
		$Denominator = 0;
		if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
			$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
			$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
			
			$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
			$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
			$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];

			$TOT_NO_GPA++;
			
			$GPA = 0;
			if($Numerator1 > 0 && $Denominator > 0)
				$GPA = $Numerator1/$Denominator;
				
			$TOT_GPA 		+= $GPA;
			$GPA = number_format_value_checker($GPA,2);
		} else
			$GPA = '';
		
		$txt .= '<tr >
					<td width="10%" >'.$res_course_schedule->fields['BEGIN_DATE_1'].'</td>
					<td width="14%" >'.$res_course_schedule->fields['COURSE_CODE'].'</td>
					<td width="18%" >'.$res_course_schedule->fields['COURSE_DESCRIPTION'].'</td>
					<td width="9%" align="right" >'.$res_course_schedule->fields['COURSE_UNITS'].'</td>
					<td width="9%" align="right" >'.$ATTEMPTED.'</td>
					<td width="9%" align="right" >'.$IN_PROGRESS.'</td>
					<td width="8%" align="right" >'.$COMPLETED.'</td>
					<td width="8%" align="right" >'.$res_course_schedule->fields['GRADE'].'</td>
					<td width="8%" align="right" >'.$res_course_schedule->fields['NUMBER_GRADE'].'</td>
					<td width="8%" align="right" >'.$GPA.'</td>
				</tr>';
			
		$res_course_schedule->MoveNext();
	}
	
	$GPA = 0;
	if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
		$GPA = $TOT_GPA/$TOT_NO_GPA;
	$GPA = number_format_value_checker($GPA, 2);
	
	$txt .= '<tr >
				<td width="24%" >Non Program Course Totals:</td>
				<td width="18%" ></td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_REQUIRED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_ATTEMPTED,2).'</td>
				<td width="9%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_IN_PROGRESS,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.number_format_value_checker($TOT_COMPLETED,2).'</td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" ></td>
				<td width="8%" align="right" style="border-top: 1px solid #000;" >'.$GPA.'</td>
			</tr>';
			
	//////////////////////	
	
	$UNITS_150 = ($res_en->fields['UNITS'] * 1.5);
	
	$RESULT = '';
	if(($UNITS_150 - $GRAND_TOT_ATTEMPTED) >= ($res_en->fields['UNITS'] - $GRAND_TOT_COMPLETED))
		$RESULT = 'PASS';
	else
		$RESULT = 'FAIL';
	
	$txt .= '<tr >
				<td width="100%" ><br /><br /></td>
			</tr>
			<tr >
				<td width="24%" style="border-bottom: 1px solid #000;" ><b>Student Totals:</b></td>
				<td width="18%" style="border-bottom: 1px solid #000;" ></td>
				<td width="9%" style="border-bottom: 1px solid #000;" align="right" ><b>'.number_format_value_checker($GRAND_TOT_REQUIRED,2).'</b></td>
				<td width="9%" style="border-bottom: 1px solid #000;" align="right" ><b>'.number_format_value_checker($GRAND_TOT_ATTEMPTED,2).'</b></td>
				<td width="9%" style="border-bottom: 1px solid #000;" align="right" ><b>'.number_format_value_checker($GRAND_TOT_IN_PROGRESS,2).'</b></td>
				<td width="8%" style="border-bottom: 1px solid #000;" align="right" ><b>'.number_format_value_checker($GRAND_TOT_COMPLETED,2).'</b></td>
				<td width="8%" style="border-bottom: 1px solid #000;" align="right" ></td>
				<td width="8%" style="border-bottom: 1px solid #000;" align="right" ></td>
				<td width="8%" style="border-bottom: 1px solid #000;" align="right" ></td>
			</tr>
			<tr >
				<td width="20%" ><b>Pursuit of Program:</b></td>
				<td width="22%" >Program Units</td>
				<td width="9%" align="right" >'.number_format_value_checker($res_en->fields['UNITS'], 2).'</td>
			</tr>
			<tr >
				<td width="20%" ></td>
				<td width="22%" style="border-bottom: 1px solid #000;"  >Program Units @ 150%</td>
				<td width="9%" align="right" style="border-bottom: 1px solid #000;"  >'.number_format_value_checker($UNITS_150,2).'</td>
			</tr>
			<tr >
				<td width="100%" ><br /></td>
			</tr>
			<tr >
				<td width="20%" ></td>
				<td width="22%" >Program Units Completed:</td>
				<td width="9%" align="right" >'.number_format_value_checker($GRAND_TOT_COMPLETED,2).'</td>
			</tr>
			<tr >
				<td width="20%" ></td>
				<td width="22%" >Program Units  Attempted:</td>
				<td width="9%" align="right" >'.number_format_value_checker($GRAND_TOT_ATTEMPTED,2).'</td>
			</tr>
			<tr >
				<td width="20%" ></td>
				<td width="22%" >Program Units Remaining:</td>
				<td width="9%" align="right" >'.number_format_value_checker(($res_en->fields['UNITS'] - $GRAND_TOT_COMPLETED),2).'</td>
			</tr>
			<tr >
				<td width="20%" ></td>
				<td width="22%" style="border-bottom: 1px solid #000;"  >Pursuit of Program Remaining Units:</td>
				<td width="9%" style="border-bottom: 1px solid #000;"  align="right" >'.number_format_value_checker(($UNITS_150 - $GRAND_TOT_ATTEMPTED),2).'</td>
			</tr>
			<tr >
				<td width="100%" ><br /></td>
			</tr>
			<tr >
				<td width="20%" ></td>
				<td width="22%" >Pursuit of Program Status:</td>
				<td width="9%" align="right" >'.$RESULT.'</td>
			</tr>';

	$txt .= '</table>';
	//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Program Course Progress.pdf';

if($_GET['download_via_js'] == 'yes'){
	$outputFileName = 'temp/Program_Course_Progress.pdf';
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
	$pdf->Output('../school/temp/'.$file_name, 'FD');
	return $file_name;
}
	

?>
