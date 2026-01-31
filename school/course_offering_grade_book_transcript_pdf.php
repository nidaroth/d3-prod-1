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

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
require_once("function_transcript_header.php");
	
class MYPDF extends TCPDF {

	/** DIAM-2340 **/
	public function setCampus($var){
		$this->campus = $var;
	}
	/** End DIAM-2340 **/

    public function Header() {
		global $db;
		
		if($this->PageNo() == 1) {
		} else {
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(5);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(150);
			$this->Cell(55, 8, "Student Transcript", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
    }
    public function Footer() {
		global $db;
		
		/** DIAM-2340 **/
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$PK_CAMPUS = $this->campus;

		$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 21 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS'  ");
		
		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
		/** End DIAM-2340 **/
		
		$this->SetY(-15);
		$this->SetX(180);
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
require_once("pdf_custom_header.php"); //Ticket # 1645
function co_grade_book_transcript_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf, $report_name = null){
	global $db;
	
	$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);

	// DIAM-2340
	$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 21");
	$FOOTER_LOC = $res_type->fields['FOOTER_LOC'];
	$BASE 		= 48 + $FOOTER_LOC; 
	// End DIAM-2340

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(3, 15, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, $BASE);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 7, '', true);

$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$LOGO = '';
if($res->fields['PDF_LOGO'] != '')
	$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';
	
if($_GET['id'] == '') {
	$PK_STUDENT_MASTER_ARR[] = $_SESSION['PK_STUDENT_MASTER'];
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
} else {
	//$PK_STUDENT_MASTER_ARR = explode(",",$_GET['id']);
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

/* Ticket #1170 */
if($_GET['report_type'] == 1) {
	$border_1 = "border-top:1px solid #000;";
} else {
	$border_1 = "";
}
/* Ticket #1170 */

//require_once("pdf_custom_header.php"); //Ticket # 1645
foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
	
	$en_cond  = "";
	$en_cond1 = "";
	if($_GET['eid'] != ''){
		$en_cond  = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		$en_cond1 = " AND S_STUDENT_GRADE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		$en_cond2 = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		$en_cond3 = " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
	}
	
	$res_stu = $db->Execute("select FIRST_NAME, LAST_NAME,CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
	
	$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1645
	
	$pdf->STUD_NAME = $res_stu->fields['NAME'];
	$pdf->AddPage();
	
	/** DIAM-2340 **/
	if($_GET['current_enrol'] == ''){
		$res_std_enroll_id = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY IS_ACTIVE_ENROLLMENT DESC LIMIT 1 "); 
		$_GET['current_enrol'] = $res_std_enroll_id->fields['PK_STUDENT_ENROLLMENT'];
		
	}
	$res_camp = $db->Execute("select PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = $_GET[current_enrol] ");
	$pdf->setCampus($res_camp->fields['PK_CAMPUS']);
	/** End DIAM-2340 **/
	
	$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 

	$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
	
	/* Ticket # 1645 */
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
	/* Ticket # 1645 */						
							$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STUDENT_STATUS, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE,SESSION, BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond ORDER By BEGIN_DATE_1 ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");
							while (!$res_type->EOF) {
								$PK_STUDENT_ENROLLMENT2 = $res_type->fields['PK_STUDENT_ENROLLMENT'];
								$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
								$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

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
								$res_type->MoveNext();
							}
				$txt .= '</table>
					</td>
				</tr>
			</table>';
			
	$txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>
			<table border="0" cellspacing="0" cellpadding="3" width="100%" >
				<tr>
					<td width="100%" align="center" ><b><i style="font-size:50px">Course Offering Grade Book Transcript</i></b><br /></td>
				</tr>
			</table>
			<br /><br />';
			
			$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS 
			FROM 
			S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE 
			WHERE 
			S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND 
			PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_GRADE.POINTS != '' $en_cond1 ");
			if($res_stu_point->RecordCount() > 0) {
			
				$res_term = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1,S_TERM_MASTER.TERM_DESCRIPTION
				FROM
				S_STUDENT_COURSE,M_COURSE_OFFERING_STUDENT_STATUS , S_COURSE_OFFERING, S_TERM_MASTER, S_COURSE_OFFERING_GRADE, M_GRADE_BOOK_TYPE
				WHERE 
				S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
				S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
				S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
				S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
				S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE AND 
				M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond2 
				GROUP BY  S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
				if($res_term->RecordCount() > 0) {											
					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								<tr>
									<td width="10%" ></td>
									<td width="15%" ><b><u>Type</u></b></td>
									<td width="20%" ><b><u>Description</u></b></td>
									<td width="15%" align="right" ><b><u>Points Earned</u></b></td>
									<td width="15%" align="right" ><b><u>Total Points</u></b></td>
									<td width="15%" align="right" ><b><u>Percentage Earned</u></b></td>
									<td width="10%"></td>
								</tr>';
						while (!$res_term->EOF) { 
							$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];
							
							$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS 
							FROM 
							S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE, S_COURSE_OFFERING 
							WHERE 
							S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
							S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'  $en_cond1 "); //DIAM-2141 //AND S_STUDENT_GRADE.POINTS != ''
							
							if($res_stu_point->RecordCount() > 0) {
							//DIAM - 1377
							if(has_wvjc_access_transcript_desc($_SESSION['PK_ACCOUNT'],1)){ 
								if(has_wvjc_access_show_only_term_desc($_SESSION['PK_ACCOUNT'],1)){
									if($res_term->fields['TERM_DESCRIPTION'] != '') 
													$desc =  $res_term->fields['TERM_DESCRIPTION'];
											    else 
													$desc = $res_term->fields['BEGIN_DATE_1']; 
									$txt .= '<tr>
							<td width="100%" ><b style="font-size:40px" ><i>Term: '.$desc.'</i></b></td>
							</tr>';
								}else{
								$dash_desc ='';
								if(!empty($res_term->fields['TERM_DESCRIPTION'])){
									$dash_desc = ' - '.$res_term->fields['TERM_DESCRIPTION'];
								}	

							$txt .= '<tr>
							<td width="100%" ><b style="font-size:40px" ><i>Term: '.$res_term->fields['BEGIN_DATE_1'].$dash_desc.'</i></b></td>
							</tr>';
							}

							}else{
							//DIAM - 1377

								$txt .= '<tr>
											<td width="100%" ><b style="font-size:40px" ><i>Term: '.$res_term->fields['BEGIN_DATE_1'].'</i></b></td>
										</tr>';
							}
								
								$res_course = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, S_COURSE_OFFERING.PK_COURSE, TRANSCRIPT_CODE, COURSE_DESCRIPTION   
								FROM
								S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_COURSE_OFFERING, S_COURSE_OFFERING_GRADE, S_COURSE , M_GRADE_BOOK_TYPE
								WHERE 
								S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
								S_COURSE_OFFERING.PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
								S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
								S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
								S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
								S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
								S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE  AND 
								M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 
								$en_cond2 GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER BY TRANSCRIPT_CODE ASC");
								
								while (!$res_course->EOF) { 
								
									$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
									
									$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS 
									FROM 
									S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE 
									WHERE 
									S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND 
									S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'  $en_cond1 "); //DIAM-2141 //AND S_STUDENT_GRADE.POINTS != ''
									if($res_stu_point->RecordCount() > 0) {
										$txt .= '<tr>
											<td width="100%" ><b style="font-size:40px" ><i>'.$res_course->fields['TRANSCRIPT_CODE'].' - '.$res_course->fields['COURSE_DESCRIPTION'].'</i></b></td>
										</tr>';
										
										$flag 	= 0;
										$iii 	= 0;
										$TOT_STUD_WEIGHTED_POINTS 	= 0;
										$TOT_CO_WEIGHTED_POINTS 	= 0;
										
										$res_test_type = $db->Execute("select S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE, GRADE_BOOK_TYPE   
										FROM
										S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_COURSE_OFFERING, S_COURSE_OFFERING_GRADE, S_COURSE , M_GRADE_BOOK_TYPE
										WHERE 
										S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
										S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
										S_COURSE_OFFERING.PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
										S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
										S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
										S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
										S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
										S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE  AND 
										M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond2 
										GROUP BY S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE ORDER BY GRADE_BOOK_TYPE ASC");
										while (!$res_test_type->EOF) { 
											$iii++;
											$PK_GRADE_BOOK_TYPE = $res_test_type->fields['PK_GRADE_BOOK_TYPE'];
											
											$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS, S_COURSE_OFFERING_GRADE.DESCRIPTION 
											FROM 
											S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE 
											WHERE 
											PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND 
											S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND 
											S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond1 "); //DIAM-2141 //AND S_STUDENT_GRADE.POINTS != ''
											if($res_stu_point->RecordCount() > 0){
												$flag = 1;
												$STUD_POINTS 	= 0;
												$CO_POINTS 		= 0;
												$CO_WEIGHT 		= 0;
												
												$SUB_TOT_STUD_WEIGHTED_POINTS 	= 0;
												$SUB_TOT_CO_WEIGHTED_POINTS 	= 0;
												
												$jj = 0;
												while (!$res_stu_point->EOF) { 
													$jj++;
													if($jj == 1)
														$GRADE_BOOK_TYPE = $res_test_type->fields['GRADE_BOOK_TYPE'];
													else
														$GRADE_BOOK_TYPE = '';
													
													$STUD_POINTS 	+= $res_stu_point->fields['STUD_POINTS'];
													$CO_POINTS 		+= $res_stu_point->fields['CO_POINTS'];
													$CO_WEIGHT 		+= $res_stu_point->fields['CO_WEIGHT'];
													
													$STUD_WEIGHTED_POINTS 	= $res_stu_point->fields['STUD_POINTS'] * $res_stu_point->fields['CO_WEIGHT'];
													$CO_WEIGHTED_POINTS		= $res_stu_point->fields['CO_POINTS'] * $res_stu_point->fields['CO_WEIGHT'];
													
													// DIAM-2389
													if($res_stu_point->fields['STUD_POINTS']!="")
													{
														$TOT_STUD_WEIGHTED_POINTS 	+= $STUD_WEIGHTED_POINTS;
														$TOT_CO_WEIGHTED_POINTS 	+= $CO_WEIGHTED_POINTS;
														$SUB_TOT_STUD_WEIGHTED_POINTS 	+= $STUD_WEIGHTED_POINTS;
														$SUB_TOT_CO_WEIGHTED_POINTS 	+= $CO_WEIGHTED_POINTS;
													}
													// End DIAM-2389

														/** DIAM-1182  // DIAM-2141 **/
														$Points_Earned = $res_stu_point->fields['STUD_POINTS'];
														$Final_Points_Earn = '';
														$Percentage_Earned = '';
														if($Points_Earned != "")
														{
															$Final_Points_Earn = number_format_value_checker($STUD_WEIGHTED_POINTS,2);
															$Percentage_Earned = number_format_value_checker(($STUD_WEIGHTED_POINTS / $CO_WEIGHTED_POINTS * 100),2).' %';
														}

														$Sub_Tot_Weighted_Points_Earned = $res_stu_point->fields['STUD_POINTS'];
														$Final_Sub_Tot_Weighted_Points_Earned = '';
														$Sub_Tot_Weighted_Per_Earned = '';
														if($Sub_Tot_Weighted_Points_Earned != "")
														{
															$Final_Sub_Tot_Weighted_Points_Earned = number_format_value_checker($SUB_TOT_STUD_WEIGHTED_POINTS,2);
															$Sub_Tot_Weighted_Per_Earned = number_format_value_checker((($SUB_TOT_STUD_WEIGHTED_POINTS) / ($SUB_TOT_CO_WEIGHTED_POINTS) * 100),2).' %';
														}
														/** End DIAM-1182 // DIAM-2141 **/
													
													/* Ticket 1170  // DIAM-2141*/
													if($_GET['report_type'] == 1) {
														$txt .= '<tr>
																	<td width="10%" ></td>
																	<td width="15%" >'.$GRADE_BOOK_TYPE.'</td>
																	<td width="20%" >'.$res_stu_point->fields['DESCRIPTION'].'</td>
																	<td width="15%" align="right" >'.$Final_Points_Earn.'</td>
																	<td width="15%" align="right" >'.number_format_value_checker($CO_WEIGHTED_POINTS,2).'</td>
																	<td width="15%" align="right" >'.$Percentage_Earned.' </td>
																	<td width="10%" ></td>
																</tr>';
													}
													/* Ticket 1170 */

													$res_stu_point->MoveNext();
												}
												
												/* Ticket 1170 */
												if($_GET['report_type'] == 1) {
													$GRADE_BOOK_TYPE1 = '';
												} else {
													$GRADE_BOOK_TYPE1 = $res_test_type->fields['GRADE_BOOK_TYPE'];
												}
												
												/* Ticket # 1170  // DIAM-2141 */
												// DIAM-2389
												if($_GET['report_type'] == 1) {
													$txt .= '<tr>
															<td width="10%" ></td>
															<td width="15%" >'.$GRADE_BOOK_TYPE1.'</td>
															<td width="20%" style="'.$border_1.'" ><i>Weighted Total:</i></td>
															<td width="15%" style="'.$border_1.'" align="right" >'.$Final_Sub_Tot_Weighted_Points_Earned.'</td>
															<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker(($SUB_TOT_CO_WEIGHTED_POINTS),2).'</td>
															<td width="15%" style="'.$border_1.'" align="right" >'.$Sub_Tot_Weighted_Per_Earned.'</td>
															<td width="10%"></td>
														</tr>
														<tr>
															<td width="100%" ><br /></td>
														</tr>';
												}
												// End DIAM-2389
												/* Ticket # 1170  // DIAM-2141 */
											}
											
											$res_test_type->MoveNext();
										}
										
										if($flag == 1){
											// DIAM-2141
											if($TOT_CO_WEIGHTED_POINTS > 0){
												$per1 = ($TOT_STUD_WEIGHTED_POINTS / $TOT_CO_WEIGHTED_POINTS * 100);
												// if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
												// 	$per1 = (number_format_value_checker($TOT_STUD_WEIGHTED_POINTS,2) / number_format_value_checker($TOT_CO_WEIGHTED_POINTS,2) * 100);
												// }
											}
											else{
												$per1 = 0;
											}
											// DIAM-2141
											$txt .= '<tr>
													<td width="10%" ></td>
													<td width="15%" ></td>
													<td width="20%" style="'.$border_1.'" ><i>Weighted Current Total:</i></td>
													<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker($TOT_STUD_WEIGHTED_POINTS,2).'</td>
													<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker($TOT_CO_WEIGHTED_POINTS,2).'</td>
													<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker($per1,2).' %</td>
													<td width="10%"></td>
												</tr>';
										}
									}
									$res_course->MoveNext();
								}
							}		
							$res_term->MoveNext();
						}
						
					$txt .= '</table>
					<br /><br />';
				}
			}
		
$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" >
		<tr nobr="true">
			<td width="100%" >
			<table border="0" cellspacing="0" cellpadding="2" width="100%" >
				<tr>
					<td width="50%"><i style="font-size:50px">'.$res_stu->fields['NAME'].'</i></td>
				</tr>
				<tr>
					<td width="8%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Term</b></td>
					<td width="13%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Course</b></td>
					<td width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Attended</b></td>
					<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Missed</b></td>
					<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Scheduled</b></td>
					<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Absent Count</b></td>
					<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Absent Hours Missed</b></td>
					<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Tardy Count</b></td>
					<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Tardy Hours Missed</b></td>
					<td width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Left Early Count</b></td>
					<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Left Early Hours Missed</b></td>
					<td width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Attendance Percentage</b></td>
					<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Numeric Grade</b></td>
					<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Final Course Grade</b></td>
				</tr>';
				$NUMERIC_GRADE = 0;
				
				$Denominator 	= 0;
				$Numerator 		= 0;
				$Numerator1 	= 0;

				// DIAM-2076
				$summation_of_gpa      = 0;
				$summation_of_weight   = 0;
				// End DIAM-2076
				
				/* Ticket # 1152 */
				$res_course = $db->Execute("select NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE 
				FROM
				S_STUDENT_COURSE, M_COURSE_OFFERING_STUDENT_STATUS, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_GRADE  
				WHERE 
				S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
				S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
				S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
				S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
				S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
				M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION AND 
				S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE AND 
				M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND 
				SHOW_ON_TRANSCRIPT = 1 AND CALCULATE_GPA = 1 $en_cond2 ");
				while (!$res_course->EOF) {
					$Denominator += $res_course->fields['COURSE_UNITS'];
					$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
					$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
					
					$res_course->MoveNext();
				}
				/* Ticket # 1152 */
				
				/* Ticket #1146 */
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
													AND S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE $en_cond3 "); // DIAM-2076
							
					while (!$res_tc->EOF) {
						$Denominator += $res_tc->fields['UNITS'];
						$Numerator	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];
						$Numerator1	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];

						// DIAM-2076
						$TC_GPA_VALULE 		  = $res_tc->fields['GPA_VALUE']; 
						$TC_GPA_WEIGHT 		  = $res_tc->fields['GPA_WEIGHT']; 

						$summation_of_gpa     += $TC_GPA_VALULE;
						$summation_of_weight  += $TC_GPA_WEIGHT;
						// End DIAM-2076
						
						$NUMERIC_GRADE 	= $res_tc->fields['NUMERIC_GRADE'];
						
						$txt .=	'<tr>
							<td width="8%" style="border-left:1px solid #000;" >Transfer</td>
							<td width="13%" style="border-right:1px solid #000;"  >'.$res_tc->fields['TRANSCRIPT_CODE'].'</td>
							<td width="7%" align="right" ></td>
							<td width="6%" align="right" ></td>
							<td width="8%" align="right" style="border-right:1px solid #000;" ></td>
							<td width="6%" align="right" ></td>
							<td width="6%" align="right" style="border-right:1px solid #000;" ></td>
							<td width="6%" align="right" ></td>
							<td width="6%" align="right" style="border-right:1px solid #000;" ></td>
							<td width="8%" align="right" ></td>
							<td width="7%" align="right" style="border-right:1px solid #000;" ></td>
							<td width="9%" align="right" ></td>
							<td width="6%" align="right"  >'.$NUMERIC_GRADE.'</td>
							<td width="6%" align="right" style="border-right:1px solid #000;" >'.$res_tc->fields['GRADE'].'</td>
						</tr>';

						$res_tc->MoveNext();
					}
				}
				/* Ticket #1146 */	
				
				/* Ticket # 1152 */
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
												S_STUDENT_COURSE.PK_STUDENT_COURSE, 
												NUMERIC_GRADE, 
												S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
												S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
												)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
												S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
												) ELSE 0 END AS GPA_WEIGHT
											FROM 
												S_STUDENT_COURSE 
												LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
												LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS
												LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE
												LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER
												LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
											WHERE 
												S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
												AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
												-- AND S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER 
												-- AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
												-- AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
												-- AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE 
												-- AND M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
												-- AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
												AND SHOW_ON_TRANSCRIPT = 1 $en_cond2 
											GROUP BY 
												S_COURSE_OFFERING.PK_COURSE_OFFERING 
											ORDER BY 
												BEGIN_DATE ASC, 
												TRANSCRIPT_CODE ASC ");
				/* Ticket # 1152 */
				
				$total_schedule = 0;
				$total_attended = 0;
				$total_missed 	= 0;
				$tot_com_sch = 0; //DIAM-2300
				
				$total_absent 			= 0;
				// DIAM-2300
				$total_absent_hour      = 0;
				$total_tardy            = 0; 
				$total_tardy_hour       = 0;
				$total_left_early       = 0;
				// End DIAM-2300
				$total_left_early_hour 	= 0;
				
				$total_attended_percentage 	= 0;
				$per_index 					= 0;
				
				$c_in_att_tot 	= 0;
				$c_in_comp_tot 	= 0;
				$c_in_cu_gnu 	= 0;
				$c_in_gpa_tot 	= 0;
				
				$tot_obt_numeric = 0;
				$tot_numeric 	 = 0;
				$total_schedule1 = 0;
				while (!$res_course->EOF) { 
					$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
					$PK_STUDENT_COURSE	= $res_course->fields['PK_STUDENT_COURSE'];
					
					$COMPLETED_UNITS	= 0;
					$ATTEMPTED_UNITS	= 0;
					$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
					
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
						$gnu 				 = $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; // Ticket # 1152
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
					// DIAM-2300
					$TO_DATE  = date('Y-m-d');
					$att_cond = " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') <= '$TO_DATE'  ";
					// DIAM-2300
					$SCHEDULED_HOUR 	 = 0;
					$COMP_SCHEDULED_HOUR = 0;
					$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $att_cond");
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
					
					$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
					
					$res_abs = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as ABSENT, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS ABSENT_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE IN ($absent_att_code)  AND COMPLETED = 1 ");
					
					$res_tardy = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as TARDY, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS TARDY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 16  AND COMPLETED = 1 ");
					
					$res_left_early = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as LEFT_EARLY, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS LEFT_EARLY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 5  AND COMPLETED = 1 ");
					
					$missed = $COMP_SCHEDULED_HOUR - $res_attended->fields['ATTENDED_HOUR'];
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
					
					if($SCHEDULED_HOUR > 0) {
						$attended_percentage = $res_attended->fields['ATTENDED_HOUR'] / $COMP_SCHEDULED_HOUR * 100;
						$total_attended_percentage += $attended_percentage;
						$per_index++;
					} else 
						$attended_percentage = 0;
					
					$NUMERIC_GRADE = '';
					if(trim($res_course->fields['CALCULATE_GPA']) == 1) { // Ticket # 1152
						$NUMERIC_GRADE 	= $res_course->fields['NUMERIC_GRADE'];
					}
					
					
					if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
					// DIAM-1527
					$PK_STUDENT_ENROLLMENT	= $res_course->fields['PK_STUDENT_ENROLLMENT'];
					$sch_res_sch = $db->Execute("SELECT S_COURSE.HOURS AS HOURS_SCHEDULDED FROM S_STUDENT_COURSE LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE WHERE S_STUDENT_COURSE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
					$SCHEDULED_HOUR = $sch_res_sch->fields['HOURS_SCHEDULDED'];
					$total_schedule1 += $sch_res_sch->fields['HOURS_SCHEDULDED'];
					$total_schedule=0;
					$total_schedule =$total_schedule1;
					// DIAM-1527
					}
					
					/* Ticket # 1152 */
					$txt .=	'<tr>
							<td width="8%" style="border-left:1px solid #000;" >'.$res_course->fields['BEGIN_DATE_1'].'</td>
							<td width="13%" style="border-right:1px solid #000;"  >'.$res_course->fields['TRANSCRIPT_CODE'].' ('. substr($res_course->fields['SESSION'],0,1).' - '. $res_course->fields['SESSION_NO'].')</td>
							<td width="7%" align="right" >'.number_format_value_checker($res_attended->fields['ATTENDED_HOUR'],2).'</td>
							<td width="6%" align="right" >'.number_format_value_checker(($missed),2).'</td>
							<td width="8%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($COMP_SCHEDULED_HOUR,2).'</td>
							<td width="6%" align="right" >'.$res_abs->fields['ABSENT'].'</td>
							<td width="6%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_abs->fields['ABSENT_HOUR'],2).'</td>
							<td width="6%" align="right" >'.$res_tardy->fields['TARDY'].'</td>
							<td width="6%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_tardy->fields['TARDY_HOUR'],2).'</td>
							<td width="8%" align="right" >'.$res_left_early->fields['LEFT_EARLY'].'</td>
							<td width="7%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_left_early->fields['LEFT_EARLY_HOUR'],2).'</td>
							<td width="9%" align="right" >'.number_format_value_checker($attended_percentage,2).' %</td>
							<td width="6%" align="right"  >'.$NUMERIC_GRADE.'</td>
							<td width="6%" align="right" style="border-right:1px solid #000;" >'.$res_course->fields['GRADE'].'</td>
						</tr>';
					/* Ticket # 1152 */
						
					$res_course->MoveNext();
				}
				
				if($tot_com_sch > 0)
					$total_attended_percentage = $total_attended / $tot_com_sch * 100;
				else
					$total_attended_percentage = 0;
					
				$gpa = '';
				if($c_in_comp_tot > 0)
					$gpa = number_format_value_checker(($c_in_cu_gnu / $c_in_comp_tot),2);
	
				$res_tc_1 = $db->Execute("SELECT SUM(HOUR) as HOUR FROM S_STUDENT_CREDIT_TRANSFER, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1");
				//DIAM-2141
				if($include_tc==1) {
					$str_txt =	'<td width="8%" style="border-top:1px solid #000;" >Transferred: </td><td width="13%" style="border-top:1px solid #000;"  >'.number_format_value_checker($res_tc_1->fields['HOUR'],2).'</td>';
					$strmsg='';
				}else{
					$str_txt =	'<td width="8%" style="border-top:1px solid #000;" ></td><td width="13%" style="border-top:1px solid #000;" ></td>';
					$strmsg='<i>Report Does not Include Transfer Credit</i>';
				}
				//DIAM-2141
			$txt .=	'<tr>
					'.$str_txt.'
					<td width="7%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker($total_attended,2).'</td>
					<td width="6%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker($total_missed,2).'</td>
					<td width="8%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker($tot_com_sch,2).'</td>
					<td width="6%" align="right" style="border-top:1px solid #000;" >'.$total_absent.'</td>
					<td width="6%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker($total_absent_hour,2).'</td>
					<td width="6%" align="right" style="border-top:1px solid #000;" >'.$total_tardy.'</td>
					<td width="6%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker($total_tardy_hour,2).'</td>
					<td width="8%" align="right" style="border-top:1px solid #000;" >'.$total_left_early.'</td>
					<td width="7%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker($total_left_early_hour,2).'</td>
					<td width="9%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker($total_attended_percentage,2).' %</td>
					<td width="6%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker(($Numerator/$Denominator),2).'</td>
					<td width="6%" align="right" style="border-top:1px solid #000;" >'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</td>
				</tr>
				<tr>
					<td colspan="11" >'.$strmsg.'</td>
					<td colspan="3" align="right"  ><i>(Cumulative GPA)</i></td>
				</tr>';
		$txt .=	'</table>
				</td>
			</tr>
		</table>
			<br /><br /><br />
			
			<table border="0" cellspacing="0" cellpadding="3" width="100%" >
				<tr>
					<td width="45%" align="right" ><i>Official Signature:</i></td>
					<td width="20%" style="border-bottom:1px solid #000;" ></td>
				</tr>
			</table>';

	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Course_Offering_Grade_Book_Transcript_'.uniqid().'.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/
// $pdf->Output('temp/'.$file_name, 'FD');
// return $file_name;	
###########################

// DIAM-2367
$FIRST_NAME = remove_special_character_from_string($res_stu->fields['FIRST_NAME']);
$LAST_NAME  = remove_special_character_from_string($res_stu->fields['LAST_NAME']);
// End DIAM-2367

if($one_stud_per_pdf == 0) {
	$file_dir_1 = 'temp/';
	if($_GET['download_via_js'] == 'yes'){
		$pdf->Output($file_dir_1.$file_name, 'F');
		header('Content-type: application/json; charset=UTF-8');
		$data_res = [];
		$data_res['path'] = $file_dir_1.$file_name;
		$data_res['filename'] = $file_name;
		echo json_encode($data_res);  
		exit;
	}else{
		$pdf->Output($file_dir_1.$file_name, 'FD');
	}
	
} else {
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
	$file_dir_1 = '../backend_assets/tmp_upload/';

	$file_name  = $LAST_NAME.'_'.$FIRST_NAME.'-'.$res_stu->fields['STUDENT_ID'].'-'.$file_name.'_'.$PK_STUDENT_MASTER.'.pdf';
	$pdf->Output($file_dir_1.$file_name, 'F');
}

return $file_name;
}

if($_GET['id'] == '') {
$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];

co_grade_book_transcript_pdf($_SESSION['PK_STUDENT_MASTER'], 0);
} else {

if($_GET['zip'] == 1) {
	function unlinkRecursive($dir, $deleteRootToo){
		if(!$dh = @opendir($dir)){
			return;
		}
		while (false !== ($obj = readdir($dh))){
			if($obj == '.' || $obj == '..'){
				continue;
			}
			if (!@unlink($dir . '/' . $obj)){
				unlinkRecursive($dir.'/'.$obj, true);
			}
		}
		closedir($dh);
		if ($deleteRootToo){
			@rmdir($dir);
		}
		return;
	}
	
	class FlxZipArchive extends ZipArchive {
		public function addDir($location, $name) {
			$this->addEmptyDir($name);
			$this->addDirDo($location, $name);
		} 
		private function addDirDo($location, $name) {
			$name .= '/';
			$location .= '/';
			$dir = opendir ($location);
			while ($file = readdir($dir)){
				if ($file == '.' || $file == '..') 
					continue;
				$do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
				$this->$do($location . $file, $name . $file);
			}
		}
	}
	
	// $folder = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/Course_Offering_Grade_Book_Transcript';
	$folder = '../backend_assets/tmp_upload/Course_Offering_Grade_Book_Transcript';
	$zip_file_name  = $folder.'.zip';
	if($folder != '') {
		unlinkRecursive("$folder/",0);
		unlink($zip_file_name);
		@rmdir($folder);
	}
	mkdir($folder);
	
	$za = new FlxZipArchive;
	$res = $za->open($zip_file_name, ZipArchive::CREATE);
	if($res === TRUE) {
		$PK_STUDENT_MASTER_ARR = explode(",",$_GET['id']);
		foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
			$file_name_1 = co_grade_book_transcript_pdf($PK_STUDENT_MASTER, 1);
			
			// $za->addFile('../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1, $file_name_1);
			$za->addFile('../backend_assets/tmp_upload/'.$file_name_1, $file_name_1);
			
			// $file_name_arr[] = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1;
			$file_name_arr[] = '../backend_assets/tmp_upload/'.$file_name_1;
		}
		
		$za->close();
		
		unlinkRecursive("$folder/",0);
		@rmdir($folder);
		
		foreach($file_name_arr as $file_name_2)
			unlink($file_name_2);
		
		header("location:".$zip_file_name);
	}
} else 
	co_grade_book_transcript_pdf($_GET['id'], 0);
}
