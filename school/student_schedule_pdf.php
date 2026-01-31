<?php require_once('../global/config.php');
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once("function_transcript_header.php");
	
class MYPDF extends TCPDF {
	protected $campus;

    public function setCampus($var){
        $this->campus = $var;
    }
	
    public function Header() {
		global $db;
		
		$this->SetFont('helvetica', 'I', 17);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(240);
		$this->Cell(55, 8, "Student Schedule", 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
		if($_SESSION['temp_id'] == $this->PK_STUDENT_MASTER){
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			$this->SetMargins('', 25, '');
			
		} else {
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;
		}
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-10);
		$this->SetX(270);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-10);
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
		
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$PK_CAMPUS = $this->campus;
		$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 1 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER  "); //AND PK_CAMPUS = '$PK_CAMPUS'
		
		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(277, 20, $CONTENT.' ', 0, 'L', 0, 0, '', '', true,'',true,true); //Ticket # 1234 
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		
    }
}

function displayDates($date1, $date2) {
	global $db;
	
	$dates = array();
	$current = strtotime($date1);
	$date2 	 = strtotime($date2);
	$stepVal = '+1 day';
	while( $current <= $date2 ) {
	
		$temp_date = date("Y-m-d", $current);
		$dates[] = $temp_date;
		
		$current = strtotime($stepVal, $current);
	}
	return $dates;
}

$_SESSION['temp_id'] = '';
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 15, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
/* Ticket # 1234 */
$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 1");
$BREAK_VAL = 10 + $res_type->fields['FOOTER_LOC'];
$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);
if( ($_SERVER['HTTP_HOST'] == 'd3.diamondsis.com' || $_SERVER['HTTP_HOST'] == 'uat-74.diamondsis.io' ||  $_SERVER['HTTP_HOST'] == 'localhost' ) && $_SESSION['PK_ACCOUNT'] == 87){
	$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL+25);
}
if( ($_SERVER['HTTP_HOST'] == 'd3-2.diamondsis.com'  && $_SESSION['PK_ACCOUNT'] == 502 ) || ( $_SERVER['HTTP_HOST'] == 'uat-74-2.diamondsis.io' ||  $_SERVER['HTTP_HOST'] == 'localhost' && ( $_SESSION['PK_ACCOUNT'] == 500 || $_SESSION['PK_ACCOUNT'] == 501) )){
	$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL+25);
}
/* Ticket # 1234 */
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 7, '', true);


$res_school = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$LOGO = '';
if($res_school->fields['PDF_LOGO'] != '')
	$LOGO = '<img src="'.$res_school->fields['PDF_LOGO'].'" />';
	
require_once("pdf_custom_header.php"); //Ticket # 1588
	
$PK_STUDENT_MASTER_ARR = explode(",",$_GET['id']);
foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER){
	
	$res_stu = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); //Ticket # 1157  
	if($res_stu->RecordCount() == 0){
		header("location:index");
		exit;
	}

	$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
	
	$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STUDENT_STATUS, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE,SESSION, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = '1' ORDER By BEGIN_DATE DESC");
	$PK_STUDENT_ENROLLMENT2	= $res_en->fields['PK_STUDENT_ENROLLMENT'];
	$PK_CAMPUS_PROGRAM 		= $res_en->fields['PK_CAMPUS_PROGRAM'];
	$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$res_camp = $db->Execute("select PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$pdf->STUD_NAME 		= $res_stu->fields['NAME'];
	$pdf->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
	$pdf->startPageGroup();
	$pdf->AddPage();
	$pdf->setCampus($res_camp->fields['PK_CAMPUS']);
	
	$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588
	
	$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
	
	/* Ticket # 1588 */
	$STD_STUDENT_ID = $res_stu->fields['STUDENT_ID'];
	$STD_DOB = $res_stu->fields['DOB'];
	$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
				<tr>
					<td width="50%">'.$CONTENT.'</td>
					<td width="50%">
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
							<tr>
								<td style="width:50%" >
									<b style="font-size:50px" >'.$res_stu->fields['NAME'].'</b><br />
								</td>
								<td></td>
							</tr>
							<tr>
								<td style="width:50% ; text-align : left;" >
									<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
								</td>
								<td style="width:50% ;text-align: right;" >
								ID: '.$STD_STUDENT_ID.'<br>
								DOB: '.$STD_DOB.'<br>
								Phone: '.$res_add->fields['CELL_PHONE'].'<br>
								</td>
							</tr>';
		/* Ticket # 1588 */					
							//while (!$res_en->EOF) {
						$txt .= '<tr>
									<td style="border-top:0.5px solid #c0c0c0" width="34%" >
										'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
									<td style="border-top:0.5px solid #c0c0c0" width="100%" width="34%" >
										'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
									<td style="border-top:0.5px solid #c0c0c0" width="100%" width="32%" >
										'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
								</tr>
								<tr>
									<td width="34%" >
										'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
									<td  width="34%" >
										'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
									<td  width="32%" >
										'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
								</tr>
								<tr>
									<td width="34%" >
										'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
									<td  width="34%" >
										'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
									<td  width="32%" width="32%"  >
										'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td>
								</tr>';
								/*$res_en->MoveNext();
							}*/
				$txt .= '</table>
					</td>
				</tr>
			</table>';
				
				$PK_TERM_MASTER_ARR = array();
				if($_GET['t_id'] != '') {
					//$PK_TERM_MASTER_ARR = explode(",",$_GET['t_id']); //Ticket # 1786 
					
					$res_term = $db->Execute("select PK_TERM_MASTER from  S_TERM_MASTER WHERE PK_TERM_MASTER IN ($_GET[t_id]) ORDER BY BEGIN_DATE ASC ");
					while (!$res_term->EOF) {
						$PK_TERM_MASTER_ARR[] = $res_term->fields['PK_TERM_MASTER'];
						$res_term->MoveNext();
					}
				} else if($_GET['t_id_from_ses'] != '') {
					$res_term = $db->Execute("select PK_TERM_MASTER from  S_TERM_MASTER WHERE PK_TERM_MASTER IN ($_SESSION[REP_SEARCH_PK_TERM_MASTER]) ORDER BY BEGIN_DATE ASC ");
					while (!$res_term->EOF) {
						$PK_TERM_MASTER_ARR[] = $res_term->fields['PK_TERM_MASTER'];
						$res_term->MoveNext();
					}
				} else {
					$res_term = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER from  S_TERM_MASTER, S_STUDENT_COURSE WHERE S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC ");
					while (!$res_term->EOF) {
						$PK_TERM_MASTER_ARR[] = $res_term->fields['PK_TERM_MASTER'];
						$res_term->MoveNext();
					}
				}

				foreach($PK_TERM_MASTER_ARR as $PK_TERM_MASTER1){ 
					$res_term = $db->Execute("SELECT IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE FROM S_TERM_MASTER WHERE PK_TERM_MASTER = '$PK_TERM_MASTER1' "); 
					
					$tot_UNITS 		= 0;
					$tot_FA_UNITS 	= 0;
					$res_course = $db->Execute("SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING, 
														TRANSCRIPT_CODE, 
														SESSION, 
														SESSION_NO, 
														COURSE_DESCRIPTION, 
														COURSE_UNITS,
														S_COURSE.UNITS, 
														S_COURSE.FA_UNITS, 
														START_DATE as START_DATE1, 
														IF(
														START_DATE = '0000-00-00', 
														'', 
														DATE_FORMAT(START_DATE, '%m/%d/%Y')
														) AS START_DATE, 
														IF(
														END_DATE = '0000-00-00', 
														'', 
														DATE_FORMAT(END_DATE, '%m/%d/%Y')
														) AS END_DATE, 
														CONCAT(
														FIRST_NAME, ' ', MIDDLE_NAME, ' ', 
														LAST_NAME
														) AS INSTRUCTOR 
													FROM 
														S_STUDENT_COURSE 
														LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
														LEFT JOIN M_SESSION On M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
														LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
														LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
														LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR 
													WHERE 
														PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
														AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER1' 
													ORDER BY 
														TRANSCRIPT_CODE ASC
				   ");	
								
					if($res_course->RecordCount() > 0) {
						$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" >
									<tr>
										<td>
											<table border="0" cellspacing="1" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" ><i >Term: '.$res_term->fields['BEGIN_DATE'].' - '.$res_term->fields['END_DATE'].'</i></td>
												</tr>
												<tr>
													<td width="12%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" ><b>Course</b></td>
													<td width="13%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" ><b>Course Description</b></td>
													<td width="5%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" align="right" ><b>Units</b></td>
													<td width="7%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" align="right" ><b>FA Units</b></td>
													<td width="10%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" ><b>Instructor</b></td>
													<td width="17%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" ><b>Class Meetings</b></td>
													<td width="14%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" ><b>Days of Week</b></td>
													<td width="8%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" ><b>Room</b></td>
													<td width="14%" style="border-top: 2px #c0c0c0 solid;border-bottom: 2px #c0c0c0 solid;" ><b>Class Times</b></td>
												</tr>';
					}
					$UNITS	 = 0;
					$FA_UNITS	 = 0;				
					while (!$res_course->EOF) { 
						$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];

						// DIAM-1532

						$UNITS 	   = $res_course->fields['UNITS'];
						$FA_UNITS  = $res_course->fields['FA_UNITS'];

						
						// if($res_course->fields['UNITS_ATTEMPTED'] == 1 || $res_course->fields['UNITS_COMPLETED'] == 1) {
						// 	$UNITS 	   = $res_course->fields['UNITS'];
						// 	$FA_UNITS  = $res_course->fields['FA_UNITS'];

						$tot_UNITS 		+= $res_course->fields['UNITS'];
						$tot_FA_UNITS 	+= $res_course->fields['FA_UNITS'];
						// }
						// End DIAM-1532
						
						$CLASS_METTINGS_A = array();
						$PK_CAMPUS_ROOM_A = array();
						$TIME_A 		  = array();
						$DAYS1_A 		  = array();
						
						$res_build = $db->Execute("SELECT SCHEDULE_DATE,PK_CAMPUS_ROOM,START_DATE,END_DATE,DEF_START_TIME,DEF_END_TIME FROM S_COURSE_OFFERING_SCHEDULE LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE=S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE  WHERE S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY SCHEDULE_DATE ASC LIMIT 1"); // DIAM-1406, Remove -> GROUP BY PK_CAMPUS_ROOM,START_TIME,END_TIME
						while (!$res_build->EOF) {
							$SCHEDULE_DATE 	= $res_build->fields['SCHEDULE_DATE'];
							$PK_CAMPUS_ROOM = $res_build->fields['PK_CAMPUS_ROOM'];
							//$START_TIME 	= $res_build->fields['START_TIME'];
							//$END_TIME 		= $res_build->fields['END_TIME'];
							$START_DATE 		= $res_build->fields['START_DATE'];
							$END_DATE 			= $res_build->fields['END_DATE'];
							$START_TIME 	= $res_build->fields['DEF_START_TIME'];
							$END_TIME 		= $res_build->fields['DEF_END_TIME'];
							
							//$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM'  ORDER BY SCHEDULE_DATE DESC LIMIT 1"); 
							//$SCHEDULE_DATE1 = $res_build1->fields['SCHEDULE_DATE'];  // AND START_TIME >= '$START_TIME' AND END_TIME <= '$END_TIME'
							
							$CLASS_METTINGS_A[] = date("m/d/Y",strtotime($START_DATE)).' to '.date("m/d/Y",strtotime($END_DATE));
							$PK_CAMPUS_ROOM_A[] = $PK_CAMPUS_ROOM;
							$TIME_A[]			= date("h:i A",strtotime($START_TIME)).' to '.date("h:i A",strtotime($END_TIME));
							
							$dates = array();
							
							$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND SCHEDULE_DATE BETWEEN '$START_DATE' AND  '$END_DATE' ORDER BY SCHEDULE_DATE ASC "); // AND START_TIME >= '$START_TIME' AND END_TIME <= '$END_TIME'
							while (!$res_build1->EOF) {
								$dates[] = $res_build1->fields['SCHEDULE_DATE'];
								$res_build1->MoveNext();
							}
							
							$DAYS_A = array();
							foreach($dates as $date) {
								$N = date("N",strtotime($date));
								if($N == 1)
									$DAYS_A[$N] = 'M';
								else if($N == 2)
									$DAYS_A[$N] = 'Tu';
								else if($N == 3)
									$DAYS_A[$N] = 'W';
								else if($N == 4)
									$DAYS_A[$N] = 'Th';
								else if($N == 5)
									$DAYS_A[$N] = 'F';
								else if($N == 6)
									$DAYS_A[$N] = 'Sa';
								else if($N == 7)
									$DAYS_A[$N] = 'Su';
							}
							ksort($DAYS_A);
							$DAYS1_A[] = implode(", ",$DAYS_A);
							
							$res_build->MoveNext();
						}
						
						$txt .= '<tr>
									<td width="12%" >'.$res_course->fields['TRANSCRIPT_CODE'].' ('. substr($res_course->fields['SESSION'],0,1).' - '. $res_course->fields['SESSION_NO'].')'.'</td>
									<td width="13%" >'.$res_course->fields['COURSE_DESCRIPTION'].'</td>
									<td width="5%" align="right" >'.number_format_value_checker($UNITS,2).'</td>
									<td width="7%" align="right" >'.number_format_value_checker($FA_UNITS,2).'</td>
									<td width="10%" >'.$res_course->fields['INSTRUCTOR'].'</td>
									<td width="17%" >';
									foreach($CLASS_METTINGS_A as $key => $val) {
										$txt .= $val.'<br />';
									}
							$txt .='</td>
									<td width="14%" >';
										foreach($DAYS1_A as $key => $val){
											$txt .= $val.'<br />';
										}
							$txt .= '</td>
									<td width="8%" >';
									foreach($PK_CAMPUS_ROOM_A as $key => $PK_CAMPUS_ROOM){
										$res = $db->Execute("SELECT ROOM_NO FROM M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM  = '$PK_CAMPUS_ROOM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
										$txt .= $res->fields['ROOM_NO'].'<br />';
									}
							$txt .= '</td>
									<td width="14%" >';
									foreach($TIME_A as $key => $val){
										$txt .= $val.'<br />';
									}
							$txt .= '</td>
								</tr>';
						
						
						
						$res_course->MoveNext();
					} 
					if($res_course->RecordCount() > 0) {
						$txt .= '<tr>
									<td width="25%" style="border-top:0.5px solid #c0c0c0"align="right"><i><b >Total Units for Term: '.$res_term->fields['BEGIN_DATE'].'</b></i></td>
									<td width="5%" style="border-top:0.5px solid #c0c0c0" align="right" ><i><b >'.number_format_value_checker($tot_UNITS,2).'</b></i></td>
									<td width="7%" style="border-top:0.5px solid #c0c0c0" align="right" ><i><b >'.number_format_value_checker($tot_FA_UNITS,2).'</b></i></td>
									<td width="61%" style="border-top:0.5px solid #c0c0c0" align="right" ></td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br /><br /><br />';
				}
			}
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}
$file_name = 'Student Schedule'.'.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');*/

$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
