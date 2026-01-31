<?php session_start();
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
require_once("function_transcript_header.php");
require_once("check_access.php"); // DIAM-1757
	
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "50000000");
set_time_limit(0);

class MYPDF extends TCPDF {

	/** DIAM-2340 **/
	public function setCampus($var){
		$this->campus = $var;
	}
	/** End DIAM-2340 **/

    public function Header() {
		global $db;
		
		if($_SESSION['temp_id'] == $this->PK_STUDENT_MASTER){
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(5);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			$this->SetMargins('', 25, '');
			
			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(200);
			$this->Cell(55, 8, "Satisfactory Progress Report Card", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		} else 
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;
		
    }
    public function Footer() {
		global $db;

		/** DIAM-2340 **/
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$PK_CAMPUS = $this->campus;

		$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 22 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS'  ");
		
		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(290, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
		/** End DIAM-2340 **/

		$this->SetY(-15);
		$this->SetX(270);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(140);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Student Satisfactory Progress Report Card', 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(15);
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
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(10, 195, 290, 195, $style);
    }
}

// DIAM-2340
$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 22");
$FOOTER_LOC = $res_type->fields['FOOTER_LOC'];
$BASE 		= 48 + $FOOTER_LOC; 
// End DIAM-2340

$_SESSION['temp_id'] = '';
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 15, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, $BASE);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 7, '', true);

$PK_STUDENT_MASTER_ARR	= explode(",",$_GET['id']);
$PK_STUDENT_ENROLLMENT 	= $_GET['eid'];

require_once("pdf_custom_header.php"); //Ticket # 1588

foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
	
	$week = 0;
	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$LOGO = '';
	if($res->fields['PDF_LOGO'] != '')
		$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" style="height:100px" />';

	$res_stu = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); //Ticket # 1157  
	if($res_stu->RecordCount() == 0){
		header("../index");
	}
	
	$pdf->STUD_NAME 		= $res_stu->fields['NAME'];
	$pdf->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
	$pdf->startPageGroup();
	$pdf->AddPage();

	/** DIAM-2340 **/
	if($_GET['current_enrol'] == ''){
		$res_std_enroll_id = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY IS_ACTIVE_ENROLLMENT DESC LIMIT 1 "); 
		$_GET['current_enrol'] = $res_std_enroll_id->fields['PK_STUDENT_ENROLLMENT'];
		
	}
	$res_camp = $db->Execute("select PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = $_GET[current_enrol] ");
	$pdf->setCampus($res_camp->fields['PK_CAMPUS']);
	/** End DIAM-2340 **/
	
	$res_en1 = $db->Execute("select GROUP_CONCAT(PK_STUDENT_ENROLLMENT) as PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ");
	$PK_STUDENT_ENROLLMENT 	= $res_en1->fields['PK_STUDENT_ENROLLMENT'];
	
	/* Ticket #1145 */
	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$exc_att_code_arr = array();
	$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	while (!$res_exc_att_code->EOF) {
		$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_exc_att_code->MoveNext();
	}
	/* Ticket #1145 */
		
	$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 


	$res_min = $db->Execute("SELECT MIN(BEGIN_DATE) as BEGIN_DATE FROM S_STUDENT_ENROLLMENT, S_TERM_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER ");
	$START_DATE = date("Y-m-d", strtotime('sunday last week', strtotime($res_min->fields['BEGIN_DATE'])));

	$res_max = $db->Execute("SELECT MAX(SCHEDULE_DATE) AS SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ");
	$END_DATE = date("Y-m-d", strtotime('saturday this week', strtotime($res_max->fields['SCHEDULE_DATE'])));
	
	$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588

	/* Ticket # 1588 */
	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td width="50%">'.$CONTENT.'</td>
					<td width="50%">
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
							<tr>
								<td style="border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;" ><span style="font-size:30px" ><b>'.$res_stu->fields['NAME'].'</b></span></td>
							</tr>';
	/* Ticket # 1588 */
							$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STUDENT_STATUS, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE, M_CAMPUS_PROGRAM.DESCRIPTION,SESSION, BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ORDER By BEGIN_DATE_1 ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");

							while (!$res_type->EOF) {
								$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
								$PK_STUDENT_ENROLLMENT2 = $res_type->fields['PK_STUDENT_ENROLLMENT'];
								
								$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
								
								$txt .= '<tr>
											<td style="border-left:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="34%" >
												'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
											<td style="border-left:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="34%" >
												'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
											<td style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="32%" >
												'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
										</tr>
										<tr>
											<td width="34%" style="border-left:0.5px solid #000;" >
												'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
											<td  width="34%" style="border-left:0.5px solid #000;" >
												'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
											<td  width="32%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;" >
												'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
										</tr>
										<tr>
											<td width="34%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000" >
												'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
											<td  width="34%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000" >
												'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
											<td  width="32%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000" width="100%" width="32%"  >
												'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
											</td>
										</tr>';
								$res_type->MoveNext();
							}
								
						$txt .= '</table>';
		
				//$res_sch = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE  AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 $exclude_cond ");
				// DIAM-1757
				$att_cond ='';
				if(has_etc_access($_SESSION['PK_ACCOUNT'],1)){				
				$TO_DATE  = date('Y-m-d');
				$att_cond .= " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') <= '$TO_DATE'  ";
				}
				// DIAM-1757
				$SCHEDULED_HOUR 	 = 0;
				$COMP_SCHEDULED_HOUR = 0;
				$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $att_cond ");
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
				
				$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
				$per = 0;
				if($COMP_SCHEDULED_HOUR > 0)
					$per = ($res_attended->fields['ATTENDED_HOUR'] / $COMP_SCHEDULED_HOUR * 100);
				
				// DIAM-1662-14-MAY-2024
				$sch_hours ='';
				if(has_etc_access($_SESSION['PK_ACCOUNT'],1)){				
					$sch_hours = number_format_value_checker($COMP_SCHEDULED_HOUR,2);
				}else{
					$sch_hours = number_format_value_checker($SCHEDULED_HOUR,2);
				}
				//DIAM-1662-14-MAY-2024

				$txt .= '<br /><br />
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
							<tr>
								<td  width="15%" style="border-top:0.5px solid #000000;border-left:0.5px solid #000000;" >
									Student Totals
								</td>
								<td  width="15%" style="border-top:0.5px solid #000000" align="right" >
									Attended 
								</td>
								<td  width="15%" style="border-top:0.5px solid #000000" align="right" >
									Scheduled
								</td>
								<td  width="15%" style="border-top:0.5px solid #000000" align="right" >
									Percentage
								</td>
								<td  width="15%" style="border-top:0.5px solid #000000" align="right" >
									Units Attempted
								</td>
								<td  width="15%" style="border-top:0.5px solid #000000" align="right" >
									Units Completed
								</td>
								<td  width="10%" style="border-top:0.5px solid #000000;border-right:0.5px solid #000000;" align="right" >
									GPA
								</td>
							</tr>
							<tr>
								<td  width="15%" style="border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;" ></td>
								<td  width="15%" style="border-bottom:0.5px solid #000000" align="right" >'.number_format_value_checker($res_attended->fields['ATTENDED_HOUR'],2).'</td>
								<td  width="15%" style="border-bottom:0.5px solid #000000" align="right" >'.number_format_value_checker($sch_hours,2).'</td>
								<td  width="15%" style="border-bottom:0.5px solid #000000" align="right" >'.number_format_value_checker($per,2).' %</td>
								<td  width="15%" style="border-bottom:0.5px solid #000000" align="right" >
									[Units Attempted]
								</td>
								<td  width="15%" style="border-bottom:0.5px solid #000000" align="right" >
									[Units Completed]
								</td>
								<td  width="10%" style="border-bottom:0.5px solid #000000;border-right:0.5px solid #000000;" align="right" >
									[GPA]
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">';
			
		do {
			$end = date("Y-m-d", strtotime($START_DATE.' + 28 days')); 

			$res_type = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, S_COURSE_OFFERING.PK_COURSE, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, SESSION_NO, SESSION,FINAL_GRADE, GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS, S_STUDENT_COURSE.PK_STUDENT_COURSE, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT     
			FROM
			S_STUDENT_COURSE 
			LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
			, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_STUDENT_SCHEDULE  
			WHERE 
			S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT  IN ($PK_STUDENT_ENROLLMENT) AND 
			S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
			S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
			S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
			S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
			M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION  AND 
			S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE AND 
			SCHEDULE_DATE BETWEEN '$START_DATE' AND '$end' 
			GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER BY BEGIN_DATE ASC, TRANSCRIPT_CODE ASC");
			
			if($res_type->RecordCount() > 0) { // DIAM-2340, condition added to remove empty records
				$week++;
				$txt .= '<tr nobr="true">
							<td width="100%">
								<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<thead>
										<tr>
											<td width="50px" ></td>
											<td width="150px" ></td>
											
											<td colspan="7" width="19.95%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
												Week '.$week.': '.date("M d", strtotime($START_DATE)).' - '.date("M d, Y", strtotime($START_DATE.' + 6 days')).'
											</td>';
											$week++;
										$txt .= '<td colspan="7" width="19.95%" style="border-right:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
												Week '.$week.': '.date("M d", strtotime($START_DATE.' + 7 days')).' - '.date("M d, Y", strtotime($START_DATE.' + 13 days')).'
											</td>';
											$week++;
										$txt .= '<td colspan="7" width="19.95%" style="border-right:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
												Week '.$week.': '.date("M d", strtotime($START_DATE.' + 14 days')).' - '.date("M d, Y", strtotime($START_DATE.' + 20 days')).'
											</td>';
											$week++;
										$txt .= '<td colspan="7" width="19.95%" style="border-right:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
												Week '.$week.': '.date("M d", strtotime($START_DATE.' + 21 days')).' - '.date("M d, Y", strtotime($START_DATE.' + 27 days')).'
											</td>';
								$txt .= '</tr>';
								$txt .= '<tr>
											<td width="50px" >
												<b>Term</b>
											</td>
											<td width="150px" >
												<b>Course</b>
											</td>';
									$count1 = 3;
									for($i = 0 ; $i <= $count1 ; $i++) {
										$txt .= '<td width="2.85%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
													S
												</td>
												<td width="2.85%" style="border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
													M
												</td>
												<td width="2.85%" style="border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
													T
												</td>
												<td width="2.85%" style="border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
													W
												</td>
												<td width="2.85%" style="border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
													T
												</td>
												<td width="2.85%" style="border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
													F
												</td>
												<td width="2.85%" style="border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">
													S
												</td>';
										}
								$txt .= '</tr>
									</thead>';
								
								while (!$res_type->EOF) { 
									$PK_STUDENT_COURSE 		= $res_type->fields['PK_STUDENT_COURSE'];
									$PK_STUDENT_ENROLLMENT1 = $res_type->fields['PK_STUDENT_ENROLLMENT'];
									
									$txt .= '<tr>
												<td width="50px" >'.$res_type->fields['BEGIN_DATE'].'</td>
												<td width="150px" >'.$res_type->fields['TRANSCRIPT_CODE'].' ('. substr($res_type->fields['SESSION'],0,1).' - '. $res_type->fields['SESSION_NO'].')</td>';

									for($i = 0 ; $i <= 27 ; $i++){ 
										$DATE = date("Y-m-d", strtotime($START_DATE.' + '.$i.' days')); 
										
										$res = $db->Execute("select ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.COMPLETED, M_ATTENDANCE_CODE.CODE, PK_SCHEDULE_TYPE  from S_STUDENT_ATTENDANCE, M_ATTENDANCE_CODE, S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT1) AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND SCHEDULE_DATE = '$DATE' AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE  ");
										
										$ATTENDANCE_HOURS = '';
										if($res->RecordCount() > 0) {
											if($res->fields['CODE'] == 'I')
												$ATTENDANCE_HOURS = 'I';
											else {
												if($res->fields['COMPLETED'] == 1 || $res->fields['PK_SCHEDULE_TYPE'] == 2)
													$ATTENDANCE_HOURS = number_format_value_checker($res->fields['ATTENDANCE_HOURS'],2);
												else
													$ATTENDANCE_HOURS = '';
											}
										}
										
										$txt .= '<td width="2.85%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;">'.$ATTENDANCE_HOURS.'</td>';
									}
									$txt .= '</tr>';
									
									$res_type->MoveNext();
								}
										
						$txt .= '</table><br /><br />
							</td>
						</tr>';
								
			}
				
			$START_DATE = date("Y-m-d", strtotime($START_DATE.' + 28 days')); 
			
		} while(strtotime($END_DATE) >= strtotime($START_DATE));
		
		$txt .= '</table>';
		
		$txt .= '<i><span style="font-size:35px" >'.$res_stu->fields['NAME'].'</span></i><br /><br />';
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<thead>
						<tr>
							<td width="8%" style="border-bottom:0.5px solid #000;border-left:0.5px solid #000;border-top:0.5px solid #000;" ><br /><br /><b>Term</b></td>
							<td width="16%" style="border-bottom:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000;" ><br /><br /><b>Course</b></td>
							<td width="8%" style="border-bottom:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><br /><br /><b>Hours Attended</b></td>
							<td width="7%" style="border-bottom:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><br /><br /><b>Hours Missed</b></td>
							<td width="8%" style="border-bottom:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><br /><br /><b>Hours Scheduled</b></td>
							<td width="7%" style="border-bottom:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><br /><br /><b>Absent Count</b></td>
							<td width="7%" style="border-bottom:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><b>Absent Hours Missed</b></td>
							<td width="6%" style="border-bottom:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><br /><br /><b>Tardy Count</b></td>
							<td width="6%" style="border-bottom:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><b>Tardy Hours Missed</b></td>
							<td width="8%" style="border-bottom:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><br /><br /><b>Left Early Count</b></td>
							<td width="7%" style="border-bottom:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><b>Left Early Hours Missed</b></td>
							<td width="6%" style="border-bottom:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><b>Attendance Percentage</b></td>
							<td width="6%" style="border-bottom:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000;" align="right" ><b>Final Course Grade</b></td>
						</tr>
					</thead>';
		
		$Denominator 	= 0;
		$Numerator 		= 0;
		$Numerator1 	= 0;
		
		$c_in_att_tot 	= 0;
		$c_in_comp_tot 	= 0;

		// DIAM-2076
		$summation_of_gpa      = 0;
		$summation_of_weight   = 0;
		// End DIAM-2076

		$include_tc = 1;
		if($_GET['exclude_tc'] == 1)
			$include_tc = 0;
		
		if($include_tc == 1) { 			
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
										CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
										S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
										)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
										CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
										S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
										) ELSE 0 END AS GPA_WEIGHT
									FROM 
										S_STUDENT_CREDIT_TRANSFER 
										LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
										LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS, 
										S_GRADE 
									WHERE 
										S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
										AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
										AND SHOW_ON_TRANSCRIPT = 1 
										AND S_GRADE.CALCULATE_GPA = 1 
										AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE 
										AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) "); //Ticket # 1152
					
			while (!$res_tc->EOF) {
				$Denominator += $res_tc->fields['UNITS'];
				$Numerator	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];
				$Numerator1	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];
				
				$NUMERIC_GRADE 	= $res_tc->fields['NUMERIC_GRADE'];
				
				if($res_tc->fields['UNITS_ATTEMPTED'] == 1) 
					$c_in_att_tot 	+= $res_tc->fields['UNITS'];
					
				if($res_tc->fields['UNITS_COMPLETED'] == 1) 
					$c_in_comp_tot  += $res_tc->fields['UNITS'];
				
				// DIAM-2076
				$TC_GPA_VALULE 		  = $res_tc->fields['GPA_VALUE']; 
				$TC_GPA_WEIGHT 		  = $res_tc->fields['GPA_WEIGHT']; 

				$summation_of_gpa     += $TC_GPA_VALULE;
				$summation_of_weight  += $TC_GPA_WEIGHT;
				// End DIAM-2076

				$txt .=	'<tr>
							<td width="8%" style="border-left:0.5px solid #000;" >Transfer</td>
							<td width="16%" style="border-right:0.5px solid #000;"  >'.$res_tc->fields['TRANSCRIPT_CODE'].'</td>
							<td width="8%" align="right" ></td>
							<td width="7%" align="right" ></td>
							<td width="8%" align="right" style="border-right:0.5px solid #000;" ></td>
							<td width="7%" align="right" ></td>
							<td width="7%" align="right" style="border-right:0.5px solid #000;" ></td>
							<td width="6%" align="right" ></td>
							<td width="6%" align="right" style="border-right:0.5px solid #000;" ></td>
							<td width="8%" align="right" ></td>
							<td width="7%" align="right" style="border-right:0.5px solid #000;" ></td>
							<td width="6%" align="right" ></td>
							<td width="6%" align="right" style="border-right:0.5px solid #000;" >'.$res_tc->fields['GRADE'].'</td>
						</tr>';

				$res_tc->MoveNext();
			}
		}

		$res_course = $db->Execute("select NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE   
		FROM
		S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_GRADE 
		WHERE 
		S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
		S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
		S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
		S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
		S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
		M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION AND CALCULATE_GPA = 1  AND  S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE
		GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ");
		while (!$res_course->EOF) {
			$Denominator += $res_course->fields['COURSE_UNITS'];
			$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
			$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
		
			$res_course->MoveNext();
		}
		
		$res_course = $db->Execute("SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING, 
										S_COURSE_OFFERING.PK_COURSE, 
										TRANSCRIPT_CODE, 
										COURSE_DESCRIPTION, 
										IF(BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y')) AS BEGIN_DATE_1, 
										SESSION_NO, 
										SESSION, 
										FINAL_GRADE, 
										GRADE, 
										NUMBER_GRADE, 
										CALCULATE_GPA, 
										UNITS_ATTEMPTED, 
										UNITS_COMPLETED, 
										UNITS_IN_PROGRESS, 
										COURSE_UNITS, 
										S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, 
										PK_STUDENT_COURSE, 
										CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
										S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
										)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
										CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
										S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
										) ELSE 0 END AS GPA_WEIGHT
									FROM 
										S_STUDENT_COURSE 
										LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
										LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING
										LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE
										LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER
										LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
									WHERE 
										S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
										AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) 
										-- AND S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER 
										-- AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
										-- AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
										-- AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE 
										-- AND M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
									-- GROUP BY 
									-- 	S_COURSE_OFFERING.PK_COURSE_OFFERING 
									ORDER BY 
										BEGIN_DATE ASC, 
										TRANSCRIPT_CODE ASC ");

		$tot_com_sch 	= 0;
		$total_schedule = 0;
		$total_attended = 0;
		$total_missed 	= 0;
		
		$total_absent 			= 0;
		$total_left_early_hour 	= 0;
		$total_absent_hour =0; //DIAM-1662
		$total_tardy_hour =0; //DIAM-1662
		$total_tardy=0; //DIAM-1662
		
		$total_attended_percentage 	= 0;
		$per_index 					= 0;
		
		$c_in_cu_gnu 	= 0;
		$c_in_gpa_tot 	= 0;
		
		while (!$res_course->EOF) { 
			$PK_COURSE_OFFERING 	= $res_course->fields['PK_COURSE_OFFERING'];
			$PK_STUDENT_ENROLLMENT1 = $res_course->fields['PK_STUDENT_ENROLLMENT'];
			$PK_STUDENT_COURSE 		= $res_course->fields['PK_STUDENT_COURSE'];
			$COMPLETED_UNITS	 	= 0;
			$ATTEMPTED_UNITS	 	= 0;
			$FINAL_GRADE 			= $res_course->fields['FINAL_GRADE'];
			
			if($res_course->fields['UNITS_ATTEMPTED'] == 1) // Ticket # 1152
				$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
			
			$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
			$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
			
			if($res_course->fields['UNITS_COMPLETED'] == 1) { // Ticket # 1152
				$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
				$c_in_comp_tot  	+= $COMPLETED_UNITS;
				$c_in_comp_sub_tot  += $COMPLETED_UNITS;
			}
			
			$gnu = 0;
			$gpa = 0;
			if($res_course->fields['CALCULATE_GPA'] == 1) { // Ticket # 1152
				$gnu 				 = $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];  // Ticket # 1152
				$c_in_cu_gnu 		+= $gnu; 
				$c_in_cu_sub_gnu 	+= $gnu; 
				
				$gpa				= $gnu / $COMPLETED_UNITS;;
				$c_in_gpa_sub_tot 	+= $gpa;
				$c_in_gpa_tot 		+= $gpa;

				// DIAM-2076
				$GPA_VALULE 			= $res_course->fields['GPA_VALUE']; 
				$GPA_WEIGHT 			= $res_course->fields['GPA_WEIGHT']; 
				
				$summation_of_gpa 		+= $GPA_VALULE;
				$summation_of_weight 	+= $GPA_WEIGHT;
				// End DIAM-2076
			}
			
			$SCHEDULED_HOUR 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $att_cond ");
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
						$tot_com_sch		 += $res_sch->fields['HOURS'];	
					}
				}	
				$res_sch->MoveNext();
			}

			$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT1) AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
			
			$res_abs = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as ABSENT, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS ABSENT_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE IN ($absent_att_code) AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT1) ");
			
			$res_tardy = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as TARDY, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS TARDY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 16  AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT1) ");
			
			$res_left_early = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as LEFT_EARLY, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS LEFT_EARLY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 5  AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT1) ");
			
			$missed = $COMP_SCHEDULED_HOUR - $res_attended->fields['ATTENDED_HOUR'];
			if($missed < 0)
				$missed = 0;
			if($missed < 0)
				$missed = 0;
				
			$total_schedule += $SCHEDULED_HOUR;
			$total_attended += $res_attended->fields['ATTENDED_HOUR'];
			$total_missed 	+= $missed;
			
			$total_absent 		+= $res_abs->fields['ABSENT'];
			$total_absent_hour 	+= $res_abs->fields['ABSENT_HOUR'];
			
			$total_tardy 		+= $res_tardy->fields['TARDY'];
			$total_tardy_hour 	+= $res_tardy->fields['TARDY_HOUR'];
			
			$total_left_early 		+= $res_left_early->fields['LEFT_EARLY'];
			$total_left_early_hour 	+= $res_left_early->fields['LEFT_EARLY_HOUR'];
			
			// DIAM-1662-14-MAY-2024
			if(has_etc_access($_SESSION['PK_ACCOUNT'],1)){	

			if($COMP_SCHEDULED_HOUR > 0) {
				$attended_percentage = $res_attended->fields['ATTENDED_HOUR'] / $COMP_SCHEDULED_HOUR * 100;
				//$total_attended_percentage += $attended_percentage;
				$per_index++;
			} else 
				$attended_percentage = 0;

			}else{
				
			if($SCHEDULED_HOUR > 0) {
				$attended_percentage = $res_attended->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR * 100;
				$total_attended_percentage += $attended_percentage;
				$per_index++;
			} else 
				$attended_percentage = 0;

			} // DIAM-1662-14-MAY-2024
			
			// DIAM-1662-14-MAY-2024
			$sch_hours ='';
			if(has_etc_access($_SESSION['PK_ACCOUNT'],1)){				
				$sch_hours = number_format_value_checker($COMP_SCHEDULED_HOUR,2);
			}else{
				$sch_hours = number_format_value_checker($SCHEDULED_HOUR,2);
			}
			//DIAM-1662-14-MAY-2024
				
			$txt .=	'<tr>
					<td width="8%" style="border-left:0.5px solid #000;" >'.$res_course->fields['BEGIN_DATE_1'].'</td>
					<td width="16%" style="border-right:0.5px solid #000;"  >'.$res_course->fields['TRANSCRIPT_CODE'].' ('. substr($res_course->fields['SESSION'],0,1).' - '. $res_course->fields['SESSION_NO'].')</td>
					<td width="8%" align="right" >'.number_format_value_checker($res_attended->fields['ATTENDED_HOUR'],2).'</td>
					<td width="7%" align="right" >'.number_format_value_checker(($missed),2).'</td>
					<td width="8%" align="right" style="border-right:0.5px solid #000;" >'.$sch_hours.'</td>
					<td width="7%" align="right" >'.$res_abs->fields['ABSENT'].'</td>
					<td width="7%" align="right" style="border-right:0.5px solid #000;" >'.number_format_value_checker($res_abs->fields['ABSENT_HOUR'],2).'</td>
					<td width="6%" align="right" >'.$res_tardy->fields['TARDY'].'</td>
					<td width="6%" align="right" style="border-right:0.5px solid #000;" >'.number_format_value_checker($res_tardy->fields['TARDY_HOUR'],2).'</td>
					<td width="8%" align="right" >'.$res_left_early->fields['LEFT_EARLY'].'</td>
					<td width="7%" align="right" style="border-right:0.5px solid #000;" >'.number_format_value_checker($res_left_early->fields['LEFT_EARLY_HOUR'],2).'</td>
					<td width="6%" align="right" >'.number_format_value_checker($attended_percentage,2).' %</td>
					<td width="6%" align="right" style="border-right:0.5px solid #000;" >'.$res_course->fields['GRADE'].'</td>
				</tr>';
				
			$res_course->MoveNext();
		}
			
		if($tot_com_sch > 0)
			$total_attended_percentage = $total_attended / $tot_com_sch * 100;
		else
			$total_attended_percentage = 0;

		$gpa = number_format_value_checker(($summation_of_gpa/$summation_of_weight),2);


		// DIAM-1662-14-MAY-2024 
		if(has_etc_access($_SESSION['PK_ACCOUNT'],1)){				
			$total_schedule = $tot_com_sch;
		}
		//DIAM-1662-14-MAY-2024
		
		$res_tc_1 = $db->Execute("SELECT SUM(HOUR) as HOUR FROM S_STUDENT_CREDIT_TRANSFER, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1");
		$txt .=	'<tr>
				<td width="24%" style="border-top:0.5px solid #000;" >Transferred: '.number_format_value_checker($res_tc_1->fields['HOUR'],2).'</td>
				<td width="8%" align="right" style="border-top:0.5px solid #000;" >'.number_format_value_checker($total_attended,2).'</td>
				<td width="7%" align="right" style="border-top:0.5px solid #000;" >'.number_format_value_checker($total_missed,2).'</td>
				<td width="8%" align="right" style="border-top:0.5px solid #000;" >'.number_format_value_checker($total_schedule,2).'</td>
				<td width="7%" align="right" style="border-top:0.5px solid #000;" >'.$total_absent.'</td>
				<td width="7%" align="right" style="border-top:0.5px solid #000;" >'.number_format_value_checker($total_absent_hour,2).'</td>
				<td width="6%" align="right" style="border-top:0.5px solid #000;" >'.$total_tardy.'</td>
				<td width="6%" align="right" style="border-top:0.5px solid #000;" >'.number_format_value_checker($total_tardy_hour,2).'</td>
				<td width="8%" align="right" style="border-top:0.5px solid #000;" >'.$total_left_early.'</td>
				<td width="7%" align="right" style="border-top:0.5px solid #000;" >'.number_format_value_checker($total_left_early_hour,2).'</td>
				<td width="6%" align="right" style="border-top:0.5px solid #000;" >'.number_format_value_checker($total_attended_percentage,2).' %</td>
				<td width="6%" align="right" style="border-top:0.5px solid #000;" >'.$gpa.'</td>
			</tr>
			<tr>
				<td width="32%" ></td>
				<td width="7%" align="right"  ></td>
				<td width="8%" align="right"  ></td>
				<td width="7%" align="right"  ></td>
				<td width="7%" align="right"  ></td>
				<td width="6%" align="right"  ></td>
				<td width="6%" align="right"  ></td>
				<td width="7%" align="right"  ></td>
				<td width="7%" align="right"  ></td>
				<td width="15%" align="right"  ><i>(Cumulative GPA)</i></td>
			</tr>';
		$txt .=	'</table>';
		
		$txt = str_replace("[Units Attempted]",number_format_value_checker($c_in_att_tot, 2),$txt);
		$txt = str_replace("[Units Completed]",number_format_value_checker($c_in_comp_tot, 2),$txt);
		$txt = str_replace("[GPA]",$gpa,$txt);
		
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Satisfactory_Progress_Report_'.uniqid().'.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/
if($_GET['download_via_js'] == 'yes'){
	$outputFileName = 'temp/Satisfactory_Progress_Report.pdf';
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
}
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
