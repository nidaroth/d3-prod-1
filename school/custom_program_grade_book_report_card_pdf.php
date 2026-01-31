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
require_once("check_access.php");
require_once("function_transcript_header.php"); //Ticket # 1139 

if(check_access('REPORT_REGISTRAR') == 0 && $_SESSION['PK_USER_TYPE'] != 3){
	header("location:../index");
	exit;
}

	
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		if($_SESSION['temp_id'] == $this->PK_STUDENT_ENROLLMENT){
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(85);
			$this->Cell(55, 8, "Program Grade Book Progress Report Card", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		} else 
			$_SESSION['temp_id'] = $this->PK_STUDENT_ENROLLMENT;
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 9");
		
		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true); //Ticket # 1234 
		
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
$pdf->SetMargins(7, 15, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
/* Ticket # 1234 */
$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 9");
$BREAK_VAL = 30 + $res_type->fields['FOOTER_LOC'];
$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);
/* Ticket # 1234 */
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);

$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$LOGO = '';
if($res->fields['PDF_LOGO'] != '')
	$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';

$PK_STUDENT_MASTER_ARR = explode(",",$_GET['id']);

$date_cond  = "";
$date_cond1 = "";

if($_GET['dt'] != '') {
	$date = date("Y-m-d",strtotime($_GET['dt']));
	$date_cond  = " AND COMPLETED_DATE <= '$date' ";
	$date_cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$date' ";
}

/* Ticket #1145 */
$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

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
/* Ticket #1145 */

require_once("pdf_custom_header.php"); //Ticket # 1588

foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
	
	$res_stu = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); //Ticket # 1157 
	
	$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
	
	if($_GET['eid'] != '')
		$en_cond1 = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
	else
		$en_cond1 = " AND IS_ACTIVE_ENROLLMENT = 1 ";
	$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STUDENT_STATUS, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE,SESSION, HOURS, UNITS, BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond1 ORDER By BEGIN_DATE_1 DESC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");
	
	if($_GET['eid'] != '')
		$PK_STUDENT_ENROLLMENT = $_GET['eid'];
	else
		$PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
		
	$pdf->PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
	$pdf->STUD_NAME 			= $res_stu->fields['NAME'];
	$pdf->startPageGroup();
	$pdf->AddPage();
	
	$res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ");
	
	/*$res_sch_all = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $date_cond1"); 
	
	$res_sch = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED = 1 $date_cond1"); 
	
	$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 14 AND PK_SCHEDULE_TYPE = 1 AND COMPLETED = 1 $date_cond1");
	
	$res_attended_all = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 $date_cond1");
	*/
	
	//$res_sch_all = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 $exclude_cond AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $date_cond1"); 

	$SCHEDULED_HOUR 	 = 0;
	$COMP_SCHEDULED_HOUR = 0;
	$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $date_cond1"); 
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
	
	$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $date_cond1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
	
	$res_attended_all = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) $date_cond1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code)  ");
	
	$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588

	$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
	
	/* Ticket # 1588 */
	$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
				<tr>
					<td width="50%">'.$CONTENT.'</td>
					<td width="50%">
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
							<tr>
								<td style="width:100%" >
									<b style="font-size:50px" >'.$res_stu->fields['NAME'].'</b><br />
								</td>
							</tr>
							<tr>
								<td style="width:100%" >
									<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
								</td>
							</tr>';
	/* Ticket # 1588 */						
							$res_en = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ORDER By BEGIN_DATE ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC");
							while (!$res_en->EOF) {
								$PK_STUDENT_ENROLLMENT 	= $res_en->fields['PK_STUDENT_ENROLLMENT'];
								$PK_CAMPUS_PROGRAM 		= $res_en->fields['PK_CAMPUS_PROGRAM'];
								
								$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
								
								$txt .= '<tr>
											<td style="border-top:0.5px solid #c0c0c0" width="100%" width="34%" >
												'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
											</td>
											<td style="border-top:0.5px solid #c0c0c0" width="100%" width="34%" >
												'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
											</td>
											<td style="border-top:0.5px solid #c0c0c0" width="100%" width="32%" >
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
											<td  width="32%" >
												'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
											</td>
										</tr>';
										
								$res_en->MoveNext();
							}
							
				$txt .= '</table>
					</td>
				</tr>
			</table>';
	$txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>
			
			<table border="0" cellspacing="0" cellpadding="3" width="100%" >
				<tr>
					<td width="100%" align="center" >
						<center><b>Program Grade Book Progress Report Card</b></center><br />
					</td>
				</tr>
				<tr>
					<td width="100%" >
						<table border="0" cellspacing="0" cellpadding="3" width="100%" >';
							
							$tot_session_req 	= 0;
							$tot_session_com 	= 0;
							$tot_hour_req 		= 0;
							$tot_hour_com 		= 0;
							$tot_point_req		= 0;
							$tot_point_com 		= 0;
							$tot_row_count		= 0;
							$res_test_type = $db->Execute("select S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE, GRADE_BOOK_TYPE from 
							S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
							WHERE 
							M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
							M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
							PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
							S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
							COMPLETED_DATE != '0000-00-00' $date_cond 
							GROUP BY S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE ORDER BY GRADE_BOOK_TYPE ASC ");
							while (!$res_test_type->EOF) { 
								$PK_GRADE_BOOK_TYPE = $res_test_type->fields['PK_GRADE_BOOK_TYPE'];
								
								$type_tot_session_req 	= 0;
								$type_tot_session_com 	= 0;
								$type_tot_hour_req 		= 0;
								$type_tot_hour_com 		= 0;
								$type_tot_point_req		= 0;
								$type_tot_point_com 	= 0;
								$type_row_count			= 1;
								
								$txt .= '<tr>
											<td width="100%" ><b>'.$res_test_type->fields['GRADE_BOOK_TYPE'].'</b></td>
										</tr>';
								
		
								$res_code = $db->Execute("select M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION  from 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
								WHERE 
								M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
								M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
								PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								COMPLETED_DATE != '0000-00-00' $date_cond AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE'
								GROUP BY M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE ORDER BY CODE ASC ");
							
								while (!$res_code->EOF) { 
									$PK_GRADE_BOOK_CODE = $res_code->fields['PK_GRADE_BOOK_CODE'];
									$txt .= '<tr>
												<td width="5%" >&nbsp;</td>
												<td width="95%" ><b>'.$res_code->fields['DESCRIPTION'].'</b></td>
											</tr>
											<tr>
												<td width="5%" >&nbsp;</td>
												<td width="11%" ></td>';
												if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
													$txt .= '<td width="10%" ><b style="font-size:22px" >Completed<br />Date</b></td>';
												}
												
										$txt .= '<td width="10.71%" ><b style="font-size:22px" >Sessions<br />Required</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Sessions<br />Completed</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Hours<br />Required</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Hours<br />Completed</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Points<br />Required</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Points<br />Completed</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Points<br />Average</b></td>
											</tr>';
										
										$sub_tot_session_req 	= 0;
										$sub_tot_session_com 	= 0;
										$sub_tot_hour_req 		= 0;
										$sub_tot_hour_com 		= 0;
										$sub_tot_point_req		= 0;
										$sub_tot_point_com 		= 0;
										$res_test = $db->Execute("select PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, CODE, M_GRADE_BOOK_CODE.DESCRIPTION GRADE_BOOK_TYPE, COMPLETED_DATE, SESSION_REQUIRED, SESSION_COMPLETED, HOUR_REQUIRED, HOUR_COMPLETED, POINTS_REQUIRED, POINTS_COMPLETED, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION, DATE_FORMAT(COMPLETED_DATE, '%m/%d/%Y') as COMPLETED_DATE_1 from 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
										WHERE 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
										M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
										PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
										COMPLETED_DATE != '0000-00-00' $date_cond AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' 
										ORDER BY CODE ASC, COMPLETED_DATE ASC");
								
										while (!$res_test->EOF) { 
											$sub_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
											$sub_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
											$sub_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
											$sub_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
											$sub_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
											$sub_tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];
											
											$type_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
											$type_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
											$type_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
											$type_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
											$type_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
											$type_tot_point_com 	+= $res_test->fields['POINTS_COMPLETED'];
											
											$tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
											$tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
											$tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
											$tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
											$tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
											$tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];
											
											
											
											if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
												$txt .= '<tr>
															<td width="5%" >&nbsp;</td>
															<td width="11.00%" ></td>';
															if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
																$txt .= '<td width="10%" style="font-size:22px" >'.$res_test->fields['COMPLETED_DATE_1'].'</td>';
															}
														$txt .= '<td width="10.71%" style="font-size:22px" >'.$res_test->fields['SESSION_REQUIRED'].'</td>
															<td width="10.71%" style="font-size:22px" >'.$res_test->fields['SESSION_COMPLETED'].'</td>
															<td width="10.71%" style="font-size:22px" >'.$res_test->fields['HOUR_REQUIRED'].'</td>
															<td width="10.71%" style="font-size:22px" >'.$res_test->fields['HOUR_COMPLETED'].'</td>
															<td width="10.71%" style="font-size:22px" >'.$res_test->fields['POINTS_REQUIRED'].'</td>
															<td width="10.71%" style="font-size:22px" >'.$res_test->fields['POINTS_COMPLETED'].'</td>
															<td width="10.71%" style="font-size:22px" ></td>
														</tr>';
											}	
											$res_test->MoveNext();
										}
										
										$txt .= '<tr>
													<td width="5%" >&nbsp;</td>
													<td width="11.0%" style="border-top:1px solid #000;" ><b style="font-size:22px" >Total</b></td>';
													if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
														$txt .= '<td style="border-top:1px solid #000;" width="10%" ></td>';
													}
												$txt .= '<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$sub_tot_session_req.'</b></td>
													<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$sub_tot_session_com.'</b></td>
													<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$sub_tot_hour_req.'</b></td>
													<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$sub_tot_hour_com.'</b></td>
													<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$sub_tot_point_req.'</b></td>
													<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$sub_tot_point_com.'</b></td>
													<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.number_format_value_checker(($type_tot_point_com / $type_row_count), 2).'</b></td>
												</tr>';
												
												$type_row_count ++;
												$tot_row_count ++;
												
									$res_code->MoveNext();
								}

								
								$txt .= '<tr>
												<td width="5%" >&nbsp;</td>
												<td width="95%" ><b>'.$res_test_type->fields['GRADE_BOOK_TYPE'].' Summary</b></td>
											</tr>
											<tr>
												<td width="5%" >&nbsp;</td>
												<td width="11.0%" ></td>';
												if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
													$txt .= '<td width="10%" ></td>';
												}
										$txt .= '<td width="10.71%" ><b style="font-size:22px" >Sessions<br />Required</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Sessions<br />Completed</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Hours<br />Required</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Hours<br />Completed</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Points<br />Required</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Points<br />Completed</b></td>
												<td width="10.71%" ><b style="font-size:22px" >Points<br />Average</b></td>
											</tr>
											<tr>
												<td width="5%" >&nbsp;</td>
												<td width="11.0%" style="border-top:1px solid #000;" ><b style="font-size:22px" >Total</b></td>';
												if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
													$txt .= '<td width="10%" style="border-top:1px solid #000;" ></td>';
												}
										$txt .= '<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$type_tot_session_req.'</b></td>
												<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$type_tot_session_com.'</b></td>
												<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$type_tot_hour_req.'</b></td>
												<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$type_tot_hour_com.'</b></td>
												<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$type_tot_point_req.'</b></td>
												<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$type_tot_point_com.'</b></td>
												<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.number_format_value_checker(($type_tot_point_com / ($type_row_count - 1)), 2).'</b></td>
											</tr>';
								$res_test_type->MoveNext();
							}
							
							$txt .= '<tr>
										<td width="5%" >&nbsp;</td>
										<td width="95%" ><b>Overall Summary</b></td>
									</tr>
									<tr>
										<td width="5%" >&nbsp;</td>
										<td width="11.0%" ></td>';
										if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
											$txt .= '<td width="10%" ></td>';
										}
								$txt .= '<td width="10.71%" ><b style="font-size:22px" >Sessions<br />Required</b></td>
										<td width="10.71%" ><b style="font-size:22px" >Sessions<br />Completed</b></td>
										<td width="10.71%" ><b style="font-size:22px" >Hours<br />Required</b></td>
										<td width="10.71%" ><b style="font-size:22px" >Hours<br />Completed</b></td>
										<td width="10.71%" ><b style="font-size:22px" >Points<br />Required</b></td>
										<td width="10.71%" ><b style="font-size:22px" >Points<br />Completed</b></td>
										<td width="10.71%" ><b style="font-size:22px" >Points<br />Average</b></td>
									</tr>
									<tr>
										<td width="5%" >&nbsp;</td>
										<td width="11.0%" style="border-top:1px solid #000;" ><b style="font-size:22px" >Total</b></td>';
										if($_GET['report_type'] == 1 || $_GET['report_type'] == 2) {
											$txt .= '<td width="10%" style="border-top:1px solid #000;" ></td>';
										}
								$txt .= '<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$tot_session_req.'</b></td>
										<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$tot_session_com.'</b></td>
										<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$tot_hour_req.'</b></td>
										<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$tot_hour_com.'</b></td>
										<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$tot_point_req.'</b></td>
										<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.$tot_point_com.'</b></td>
										<td width="10.71%" style="border-top:1px solid #000;" ><b style="font-size:22px" >'.number_format_value_checker(($tot_point_com / $tot_row_count), 2).'</b></td>
									</tr>';
							
					$txt .= '</table>
					</td>
				</tr>
			</table>';
			
			if($_GET['report_type'] == 2 || $_GET['report_type'] == 4) {
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
												<td width="13%" align="right" >'.number_format_value_checker($res_type->fields['HOURS'],2).'</td>
												<td width="32%"  >Total Attended Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_attended_all->fields['ATTENDED_HOUR'],2).'</td>
											</tr>
											<tr>
												<td width="32%" >Total Transfer Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_trans->fields['HOUR'],2).'</td>
												<td width="32%"  >Total Hours Remaining</td>
												<td width="13%" align="right" >'.number_format_value_checker(($res_type->fields['HOURS'] - $res_attended->fields['ATTENDED_HOUR'] - $res_trans->fields['HOUR']),2).'</td>
											</tr>
											<tr>
												<td width="32%" >Total Scheduled Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($SCHEDULED_HOUR,2).'</td>
												<td width="32%"  >Attendance Percentage</td>
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

	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Program Grade Book Progress Report Card.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	