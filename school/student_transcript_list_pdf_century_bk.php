<?php ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require_once('../global/config.php');

use setasign\Fpdi\Fpdi;

$bordercss = " border:0.5px solid #c0c0c0;  ";
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
require_once("function_transcript_header.php"); //Ticket # 1169 

if(check_access('REPORT_REGISTRAR') == 0 && $_SESSION['PK_USER_TYPE'] != 3){
	header("location:../index");
	exit;
}
// echo "<pre>";
// print_r($_REQUEST);exit;
	
class MYPDF extends TCPDF {

	/** DIAM - 1315 **/
	public $campus;

    public function setCampus($var){
        $this->campus = $var;
    }
	/** End DIAM - 1315 **/

    public function Header() {
		global $db;
		$PK_STUDENT_MASTER = $this->PK_STUDENT_MASTER;
		$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588
		$res_stu = $db->Execute("select FIRST_NAME, LAST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, OLD_DSIS_STU_NO, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
		
		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		// $header_txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
		
		/* Ticket # 1588 */
		$header_txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
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
									<td style="width:60%" >
										<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
									</td> 
								</tr>';
		/* Ticket # 1588 */						
								/* Ticket # 1169 */
								$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond ORDER By BEGIN_DATE ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC");
								while (!$res_type->EOF) {
									$PK_STUDENT_ENROLLMENT 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
									$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
									
									$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
									



									$res_HEADER_DATA = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, OFFICIAL_CAMPUS_NAME, CAMPUS_CODE, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH,'%m/%d/%Y' )) AS DATE_OF_BIRTH, S_STUDENT_CONTACT.EMAIL, CONCAT(DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' ),' - ',DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y' )) AS TERM_RANGE, TERM_DESCRIPTION, IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS END_DATE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, M_ENROLLMENT_STATUS.DESCRIPTION as FULL_PART_TIME, M_FUNDING.FUNDING, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE,'%m/%d/%Y' )) AS GRADE_DATE, S_STUDENT_CONTACT.HOME_PHONE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA,'%m/%d/%Y' )) AS LDA, IF(MIDPOINT_DATE = '0000-00-00','',DATE_FORMAT(MIDPOINT_DATE,'%m/%d/%Y' )) AS MIDPOINT_DATE, M_CAMPUS_PROGRAM.HOURS, M_CAMPUS_PROGRAM.MONTHS, M_CAMPUS_PROGRAM.UNITS, M_CAMPUS_PROGRAM.WEEKS, SESSION, SESSION_ABBREVIATION, SSN as SSN_1, SSN as SSN_2, STUDENT_STATUS, STUDENT_ID, STUDENT_GROUP, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE , M_CAMPUS_PROGRAM.DESCRIPTION as PROGRAM_DESCRIPTION, BADGE_ID, S_STUDENT_CONTACT.ADDRESS as STUD_ADDRESS_1, S_STUDENT_CONTACT.ADDRESS_1 as STUD_ADDRESS_2, CONCAT(S_STUDENT_CONTACT.CITY, ', ', STATE_CODE, ' - ', S_STUDENT_CONTACT.ZIP) as STUD_CITY_STATE_ZIP   
	FROM 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' 
	LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP  
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $order_by");


									$STR_PROGRAM = "Program: ".$res_HEADER_DATA->fields['PROGRAM_CODE']." : ".$res_HEADER_DATA->fields['PROGRAM_DESCRIPTION'];
									$STR_STATUS = "Status: ".$res_HEADER_DATA->fields['STUDENT_STATUS'];
									$STR_GRAD_DATE = "Exp. Grad: ".$res_HEADER_DATA->fields['EXPECTED_GRAD_DATE'];
									$STR_SESSION = "Session: ".$res_HEADER_DATA->fields['SESSION'];
									$STR_START_DATE = "Start Date: ".$res_HEADER_DATA->fields['BEGIN_DATE'];
									$STR_FT_PT = $res_HEADER_DATA->fields['FULL_PART_TIME'];
									$header_txt .= '
									
									<tr>
									<td style="border-top:0.5px solid #c0c0c0"  width="100%">'.$STR_PROGRAM.'</td></tr>
									<tr>
												<td width="100%" width="34%" >
													'.$STR_STATUS.'
												</td>
												<td width="100%" width="34%" >
													'.$STR_GRAD_DATE.'
												</td>
												<td  width="100%" width="32%" >
													'.$STR_SESSION.'
												</td>
											</tr>
											<tr>
												<td width="34%" >
													'.$STR_START_DATE.'
												</td>
												<td  width="34%" >
													
												</td>
												<td  width="32%" >
													'.$STR_FT_PT.'
												</td>
											</tr>';
											
									$res_type->MoveNext();
								}
								/* Ticket # 1169 */
					$header_txt .= '</table>
						</td>
					</tr>
				</table><br><div style="border-top: 2px #c0c0c0 solid" ></div>';
				$this->writeHTML($header_txt, true, false, true, false, '');
		 
		 
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-60);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		/** DIAM - 1315 **/
		$PK_CAMPUS = $this->campus;

		$CONTENT = '<div style="font-size:45px; text-align:center; letter-spacing:10;">NO CREDIT APPROVED BELOW THIS LINE</div>
		<span style="font-size:30px; text-align:center;  ">Grades with Corresponding Honor Points</span>
		<span style="font-size:30px; text-align:center;  ">A: (4) Excellent B: (3) Good C: (2) Satisfactory D: (1) Deficient F: (0) Failure
		WA: Administrative Withdrawal WV:Voluntary Withdrawal R: Repeated I: Incomplete LOA: Leave of Absent T: Tranfer Credit  </span>
		<span style="font-size:30px; text-align:center;  "><b>VALID ONLY WITH REGISTRAR'."'".'S SIGNATURE IN RED INK AND THE OFFICIAL SEAL</b></span><br>
		<span style="font-size:28px; text-align:center;"><b>Official Signature:__________________________________________________                 Fecha:____________________________</b></span>
<br><br><br><br>
		
		';
		
		//$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 5");
		/** End DIAM - 1315 **/
		
		$BASE = -45;
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$CONTENT = nl2br($CONTENT);
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

function student_transcript_list_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf, $report_name){
	global $db; 
	
	$NA_PROGRAMS = $db->Execute("SELECT GROUP_CONCAT(PK_CAMPUS_PROGRAM) AS PK_CAMPUS_PROGRAMS FROM M_CAMPUS_PROGRAM WHERE (CODE like 'NA' OR CODE like '%-NA' OR CODE like 'NA-%' OR CODE like '%-NA-%') AND PK_ACCOUNT = '".$_SESSION['PK_ACCOUNT']."' GROUP BY PK_ACCOUNT ")->fields['PK_CAMPUS_PROGRAMS']; 

	$STUDENTS_HAVING_NA_PROGRAMS = $db->Execute("SELECT GROUP_CONCAT(DISTINCT(PK_STUDENT_MASTER)) AS PK_STUDENT_MASTERS  FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '".$_SESSION['PK_ACCOUNT']."'  AND PK_STUDENT_MASTER IN ($PK_STUDENT_MASTERS) GROUP BY PK_ACCOUNT")->fields['PK_STUDENT_MASTERS']; 



	$PK_STUDENT_MASTER_ARR = explode(",",$STUDENTS_HAVING_NA_PROGRAMS);
	// dd($STUDENTS_HAVING_NA_PROGRAMS);
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 8));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	/* Ticket # 1234 */
	$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 5");
	$BREAK_VAL = 30 + 18;
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

	//ticket #1240
	$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	require_once("pdf_custom_header.php"); //Ticket # 1588
	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
		
		/*if($uno != '') {
			$ImageW = 175; //WaterMark Size
			$ImageH = 175;

			$pdf->setPage(1); //WaterMark Page    

			$myPageWidth = $pdf->getPageWidth();
			$myPageHeight = $pdf->getPageHeight();
			$myX = ( $myPageWidth / 2 ) - 90;  //WaterMark Positioning
			$myY = ( $myPageHeight / 2 ) - 80;

			$pdf->SetAlpha(0.30);
			$pdf->Image('../backend_assets/images/unoffical_1.png', $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);

			//Likewise can be added for all pages after writing all pages.
			$pdf->SetAlpha(1);
		}*/

		$en_cond = "";
		if($_GET['eid'] != ''){
			$en_cond = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
			$PK_STUDENT_ENROLLMENTS_OF_CURRENT_PK_STUDENT_MASTER = $_GET['eid'];
		}else{
			$PK_STUDENT_ENROLLMENTS_OF_CURRENT_PK_STUDENT_MASTER = $db->Execute("SELECT GROUP_CONCAT(PK_STUDENT_ENROLLMENT) AS PK_STUDENT_ENROLLMENTS  FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '".$_SESSION['PK_ACCOUNT']."'   AND PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) GROUP BY PK_ACCOUNT")->fields['PK_STUDENT_ENROLLMENTS']; 
			$en_cond = " AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENTS_OF_CURRENT_PK_STUDENT_MASTER) "; 
		}

		$extra_enrollment_offset = count(explode(',' , $PK_STUDENT_ENROLLMENTS_OF_CURRENT_PK_STUDENT_MASTER)) -1;
		if($extra_enrollment_offset > 0 ){
			$extra_enrollment_offset = $extra_enrollment_offset * 18.80;
		}
	
	$pdf->SetMargins(7, 54 + $extra_enrollment_offset, 7);
		// $en_cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($NA_PROGRAMS) ";
		
		$res_stu = $db->Execute("select FIRST_NAME, LAST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, OLD_DSIS_STU_NO, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
		
		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		
		$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588
		
		$pdf->STUD_NAME 		= $res_stu->fields['NAME'];
		$pdf->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
		$pdf->startPageGroup();
		$pdf->AddPage();

		/** DIAM - 1315 **/
		if($_GET['current_enrol'] == ''){
			$res_std_enroll_id = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY IS_ACTIVE_ENROLLMENT DESC LIMIT 1 "); 
			$_GET['current_enrol'] = $res_std_enroll_id->fields['PK_STUDENT_ENROLLMENT'];
			
		}
		$res_camp = $db->Execute("select PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = $_GET[current_enrol] ");
		$pdf->setCampus($res_camp->fields['PK_CAMPUS']);
		/** End DIAM - 1315 **/

		/** DIAM - 1314 **/
		if (has_student_trascript_access($_SESSION['PK_ACCOUNT'])) 
		{
			$stud_id = $res_stu->fields['OLD_DSIS_STU_NO'];
		}
		else{
			$stud_id = $res_stu->fields['STUDENT_ID'].'<br />DOB: '.$res_stu->fields['DOB'];
		}
		/** End DIAM - 1314 **/
// $txt = "Testing";
		// $txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
		
	// 	/* Ticket # 1588 */
	// 	$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
	// 				<tr>
	// 					<td width="50%">'.$CONTENT.'</td>
	// 					<td width="50%">
	// 						<table border="0" cellspacing="0" cellpadding="3" width="100%" >
	// 							<tr>
	// 								<td style="width:100%" >
	// 									<b style="font-size:50px" >'.$res_stu->fields['NAME'].'</b><br />
	// 								</td>
	// 							</tr>
	// 							<tr>
	// 								<td style="width:60%" >
	// 									<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
	// 								</td> 
	// 							</tr>';
	// 	/* Ticket # 1588 */						
	// 							/* Ticket # 1169 */
	// 							$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond ORDER By BEGIN_DATE ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC");
	// 							while (!$res_type->EOF) {
	// 								$PK_STUDENT_ENROLLMENT 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
	// 								$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
									
	// 								$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
									



	// 								$res_HEADER_DATA = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, OFFICIAL_CAMPUS_NAME, CAMPUS_CODE, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH,'%m/%d/%Y' )) AS DATE_OF_BIRTH, S_STUDENT_CONTACT.EMAIL, CONCAT(DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' ),' - ',DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y' )) AS TERM_RANGE, TERM_DESCRIPTION, IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS END_DATE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, M_ENROLLMENT_STATUS.DESCRIPTION as FULL_PART_TIME, M_FUNDING.FUNDING, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE,'%m/%d/%Y' )) AS GRADE_DATE, S_STUDENT_CONTACT.HOME_PHONE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA,'%m/%d/%Y' )) AS LDA, IF(MIDPOINT_DATE = '0000-00-00','',DATE_FORMAT(MIDPOINT_DATE,'%m/%d/%Y' )) AS MIDPOINT_DATE, M_CAMPUS_PROGRAM.HOURS, M_CAMPUS_PROGRAM.MONTHS, M_CAMPUS_PROGRAM.UNITS, M_CAMPUS_PROGRAM.WEEKS, SESSION, SESSION_ABBREVIATION, SSN as SSN_1, SSN as SSN_2, STUDENT_STATUS, STUDENT_ID, STUDENT_GROUP, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE , M_CAMPUS_PROGRAM.DESCRIPTION as PROGRAM_DESCRIPTION, BADGE_ID, S_STUDENT_CONTACT.ADDRESS as STUD_ADDRESS_1, S_STUDENT_CONTACT.ADDRESS_1 as STUD_ADDRESS_2, CONCAT(S_STUDENT_CONTACT.CITY, ', ', STATE_CODE, ' - ', S_STUDENT_CONTACT.ZIP) as STUD_CITY_STATE_ZIP   
	// FROM 
	// S_STUDENT_MASTER 
	// LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' 
	// LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES 
	// LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	// LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	// LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	// LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS 
	// LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	// LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	// LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
	// LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	// LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP  
	// LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	// LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	// WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $order_by");


	// 								$STR_PROGRAM = "Program: ".$res_HEADER_DATA->fields['PROGRAM_CODE']." : ".$res_HEADER_DATA->fields['PROGRAM_DESCRIPTION'];
	// 								$STR_STATUS = "Status: ".$res_HEADER_DATA->fields['STUDENT_STATUS'];
	// 								$STR_GRAD_DATE = "Exp. Grad: ".$res_HEADER_DATA->fields['EXPECTED_GRAD_DATE'];
	// 								$STR_SESSION = "Session: ".$res_HEADER_DATA->fields['SESSION'];
	// 								$STR_START_DATE = "Start Date: ".$res_HEADER_DATA->fields['BEGIN_DATE'];
	// 								$STR_FT_PT = $res_HEADER_DATA->fields['FULL_PART_TIME'];
	// 								$txt .= '
									
	// 								<tr>
	// 								<td style="border-top:0.5px solid #c0c0c0"  width="100%">'.$STR_PROGRAM.'</td></tr>
	// 								<tr>
	// 											<td width="100%" width="34%" >
	// 												'.$STR_STATUS.'
	// 											</td>
	// 											<td width="100%" width="34%" >
	// 												'.$STR_GRAD_DATE.'
	// 											</td>
	// 											<td  width="100%" width="32%" >
	// 												'.$STR_SESSION.'
	// 											</td>
	// 										</tr>
	// 										<tr>
	// 											<td width="34%" >
	// 												'.$STR_START_DATE.'
	// 											</td>
	// 											<td  width="34%" >
													
	// 											</td>
	// 											<td  width="32%" >
	// 												'.$STR_FT_PT.'
	// 											</td>
	// 										</tr>';
											
	// 								$res_type->MoveNext();
	// 							}
	// 							/* Ticket # 1169 */
	// 				$txt .= '</table>
	// 					</td>
	// 				</tr>
	// 			</table>';
				
				/* ticket #1240 */
			 
		$txt .= ' 
		<br>
		<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
					
						<td width="100%" align="center" ><b><i style="font-size:50px">'.$report_name.'</i></b><br /><br></td>
					</tr>
					</table>';
					$txt .= 
					'
				<table border="0" cellspacing="0" cellpadding="3" width="100%" > 
					<thead>
					<tr>
						<td width="10%" ><br /><br /><u><b>Term</b></u></td>
						<td width="10%" ><br /><br /><u><b>Course</b></u></td>
						<td width="38%" ></td>';
						
						   

							$txt .= '
							<td width="10%" align="right" ><b>Credits</b><br /><u><b>Attempted</b></u></td>
							<td width="8%" align="right" ><b>Class</b><br /><u><b>Hours</b></u></td>
							<td width="10%" align="right" ><b>Credits</b><br /><u><b>Earned</b></u></td>
							<td width="7%" align="right" ><br /><br /><u><b>Grade</b></u></td>
							<td width="7%" align="right" align="right" ><br /><br /><u><b>GPA</b></u></td>
					</tr>
					</thead>'; 
					/* ticket #1240 */
					
					$total_rec		= 0;
					$total_cum_rec 	= 0;
					
					$c_in_num_grade_tot = 0; //ticket #1240
					$total_clock_hours = 0;
					$c_in_att_tot 		= 0;
					$c_in_comp_tot 		= 0;
					$c_in_cu_gnu 		= 0;		
					
					$Denominator = 0;
					$Numerator 	 = 0;
					$Numerator1  = 0;
					
					$COMPLETED_UNITS = 0;
					$cummilative_block_CA_total = '';
					$cummilative_block_HOURS_total = '';
					$cummilative_block_CE_total = 0;
					$current_block_CE_total = 0;
					$cummilative_block_GPA_VALUE = '';
					$cummilative_block_GPA_WEIGHT = '';

					if($_GET['exclude_tc'] != 1) {
						$Sub_Denominator = 0;
						$Sub_Numerator 	 = 0;
						$Sub_Numerator1  = 0;
						
						/* Ticket # 1152 */
						$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, S_GRADE.NUMBER_GRADE, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, S_GRADE.GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_GRADE.NUMBER_GRADE,S_GRADE. CALCULATE_GPA, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND  S_GRADE.CALCULATE_GPA = 1 AND SHOW_ON_TRANSCRIPT = 1 AND S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE $en_cond ");

						while (!$res_tc->EOF) {
							$Denominator += $res_tc->fields['UNITS'];
							$Numerator1	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];
							
							$Sub_Denominator += $res_tc->fields['UNITS'];
							$Sub_Numerator1	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];
						
							$res_tc->MoveNext();
						}
						
						//DIAM-781
						$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, CREDIT_TRANSFER_STATUS, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, TC_NUMERIC_GRADE, S_GRADE.GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_GRADE.NUMBER_GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS,CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER BY S_COURSE.TRANSCRIPT_CODE ASC"); // Ticket # 1240
						/* Ticket # 1152 */
					 
						if($res_tc->RecordCount() > 0 ){
							// ADD TRANSFER HEADER
							// $txt .= "<tr colspan='8'><h2 style='font-size:20px !important'>TERM : Transfer </h2></tr>"; 
						}
						while (!$res_tc->EOF) {

							// calulated gpa DIAM-781
							$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
							$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
							// calulated gpa DIAM-781
							
							$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT']; 
							$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
							$COMPLETED_UNITS	 	= 0;
							$ATTEMPTED_UNITS	 	= 0;
							
							if($res_tc->fields['UNITS_ATTEMPTED'] == 1)
								$ATTEMPTED_UNITS = $res_tc->fields['UNITS'];
							
							$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, PROGRAM_TRANSCRIPT_CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
							
							$c_in_att_tot += $ATTEMPTED_UNITS; 
							
							if($res_tc->fields['UNITS_COMPLETED'] == 1) {
								$COMPLETED_UNITS 	 = $res_tc->fields['UNITS'];
								$c_in_comp_tot  	+= $COMPLETED_UNITS;
							}
						
							$gnu = 0;
							$gpa = '';
							if($res_tc->fields['CALCULATE_GPA'] == 1) {
								$total_rec++;
								$total_cum_rec++;
								
								$c_in_num_grade_tot		+= $res_tc->fields['TC_NUMERIC_GRADE']; //ticket #1240
								$total_clock_hours += $res_tc->fields['HOURS'];//DIAM-1436
								$gnu 			 = $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE']; 
								$c_in_cu_gnu 	+= $gnu; 
								$gpa			= $c_in_cu_gnu / $c_in_comp_tot;
							}
							
							/* ticket #1240 */
							$txt .= '<tr>
										<td width="10%" style="" >Transfer</td>
										<td width="10%" style="" >'.$res_tc->fields['TRANSCRIPT_CODE'].'</td>
										<td width="38%" style="" >'.$res_tc->fields['COURSE_DESCRIPTION'].'</td>
										<td width="10%" style="" align="right" >'.number_format_value_checker($ATTEMPTED_UNITS,2).'</td>';
										
							 $tc_hours_to_show = "0.00";
							 if($res_tc->fields['HOURS'] > 0){
								$tc_hours_to_show = $res_tc->fields['HOURS'];
							 }
									if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
										$txt .= '<td width="8%"  style=""align="right" >'.number_format_value_checker($tc_hours_to_show,2).'</td>';
								//DIAM-781
								$txt .= '
										<td width="10%" style="" align="right" >'.$COMPLETED_UNITS.'</td>
										<td width="7%" style="" align="right">'.$res_tc->fields['GRADE'].'</td>
										<td width="10%" style="" align="right" ></td>
									</tr>';
							/* ticket #1240 */
							$current_block_CA_total  = $current_block_CA_total +  $res_tc->fields['UNITS'];
							$current_block_HOURS_total = $current_block_HOURS_total + $res_tc->fields['HOURS'];
							if($res_tc->fields['UNITS_COMPLETED'] == 1) {
							$current_block_CE_total = $current_block_CE_total + $COMPLETED_UNITS;
							}
							if($res_tc->fields['CALCULATE_GPA'] == 1) {
							$current_block_GPA_VALUE = $current_block_GPA_VALUE + $TC_GPA_VALULE;
							$current_block_GPA_WEIGHT = $current_block_GPA_WEIGHT + $TC_GPA_WEIGHT;
							}

							//FOR CUMMILATIVE 
							$cummilative_block_CA_total = $cummilative_block_CA_total +  $res_tc->fields['UNITS'];
							$cummilative_block_HOURS_total = $cummilative_block_HOURS_total+ $res_tc->fields['HOURS'];
							if($res_tc->fields['UNITS_COMPLETED'] == 1) {
							$cummilative_block_CE_total = $cummilative_block_CE_total + $COMPLETED_UNITS;
							}
							if($res_tc->fields['CALCULATE_GPA'] == 1) {
							$cummilative_block_GPA_VALUE = $cummilative_block_GPA_VALUE + $TC_GPA_VALULE;
							$cummilative_block_GPA_WEIGHT = $cummilative_block_GPA_WEIGHT + $TC_GPA_WEIGHT;
							}
										
							$res_tc->MoveNext();
						}


					}
if($res_tc->RecordCount() > 0 ){


					$txt .= '<tr>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="48%" align="right" >Transfer  Total</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$current_block_CA_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="8%" align="right">'.number_format_value_checker($current_block_HOURS_total,2).'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$current_block_CE_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right">'.number_format_value_checker(($current_block_GPA_VALUE/$current_block_GPA_WEIGHT),2).'</td>
							
							</tr>';

							$txt .= '<tr>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%"></td>
							<td style=" padding-bottom:10px; font-size:32px;" width="48%" align="right" >Cumulative Total</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CA_total.'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="8%" align="right">'.number_format_value_checker($cummilative_block_HOURS_total,2).'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CE_total.'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="7%" align="right"></td>
							<td style=" padding-bottom:10px; font-size:32px;" width="7%" align="right">'.number_format_value_checker(($cummilative_block_GPA_VALUE/$cummilative_block_GPA_WEIGHT),2).'</td>
							
							</tr>';
}
					
					//DIAM-781
					$gpa_value_total=0;
					$gpa_weight_total=0;
					//DIAM-781
					/* Ticket # 1152 */ //DIAM-781 
					//DIAM-863
					 $res_course = $db->Execute("select  CONCAT(BEGIN_DATE,END_DATE) AS TERM_BLOCK, S_COURSE.UNITS AS CREDIT_BASE , S_COURSE.HOURS,S_COURSE_OFFERING.PK_COURSE_OFFERING, BEGIN_DATE as BEGIN_DATE_1,END_DATE,  FINAL_GRADE, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE,S_TERM_MASTER.TERM_DESCRIPTION, TRANSCRIPT_CODE, COURSE_DESCRIPTION, GRADE,NUMERIC_GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS,CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT from S_STUDENT_COURSE LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE ,M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER BY CONCAT(BEGIN_DATE_1,END_DATE) ASC, TRANSCRIPT_CODE ASC ");	
					$prev_term_block = '';
					$prev_term_block_str = '';
					$current_block_CA_total = '';
					$current_block_HOURS_total = '';
					$current_block_CE_total = '';
					$current_block_GPA_VALUE  = '';
					$current_block_GPA_WEIGHT = '';
					$first_flag = true;

					
					while (!$res_course->EOF) { 
					
						$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
						$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
						$COMPLETED_UNITS	 = '0';
						$ATTEMPTED_UNITS	 = '0';
						
						if($res_course->fields['UNITS_ATTEMPTED'] == 1)
							$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
						
						$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
						
						
						$Sub_Denominator = 0;
						$Sub_Numerator 	 = 0;
						$Sub_Numerator1  = 0;
						
						if($res_course->fields['UNITS_COMPLETED'] == 1) {
							$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
							$c_in_comp_tot  	+= $COMPLETED_UNITS;
						}
						
						if($res_course->fields['CALCULATE_GPA'] == 1) {
							$Denominator += $res_course->fields['COURSE_UNITS'];
							$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
							$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
							
							$Sub_Denominator += $res_course->fields['COURSE_UNITS'];
							$Sub_Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
							$Sub_Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
							
							$c_in_num_grade_tot += $res_course->fields['NUMERIC_GRADE']; //ticket #1240					
							$total_rec++;
						}
						$total_clock_hours += $res_course->fields['HOURS'];//DIAM-1436
						$gnu = 0;
						$gpa = '';
						if($res_course->fields['CALCULATE_GPA'] == 1) {
							$gnu 			= $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; 
							$c_in_cu_gnu 	+= $gnu;
							$gpa			= $c_in_cu_gnu / $c_in_comp_tot;
							$gpa			= number_format_value_checker($gpa,2);

							// calulated gpa DIAM-781
							$GPA_VALULE 				 = $res_course->fields['GPA_VALUE']; 
							$gpa_value_total 		+= $GPA_VALULE; 
							$GPA_WEIGHT 				 = $res_course->fields['GPA_WEIGHT']; 
							$gpa_weight_total 		+= $GPA_WEIGHT; 
						}
						
							//DIAM - 1377
							$BEGIN_DATE 	= $res_course->fields['BEGIN_DATE'];
							if(has_wvjc_access($_SESSION['PK_ACCOUNT'],1)){ 
								$dash_desc ='';
								if(!empty($res_course->fields['TERM_DESCRIPTION'])){
									$dash_desc = ' - '.$res_course->fields['TERM_DESCRIPTION'];
								}
								$BEGIN_DATE 	= $res_course->fields['BEGIN_DATE'].$dash_desc;
							}
							//DIAM - 1377

						/* ticket #1240 */

						if($prev_term_block != $res_course->fields['TERM_BLOCK']){
							
							if(!$first_flag){

						
							$txt .= '<tr>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="48%" align="right" >TERM : '.$prev_term_block_str.'  Total</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$current_block_CA_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="8%" align="right">'.number_format_value_checker($current_block_HOURS_total,2).'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$current_block_CE_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right">'.number_format_value_checker(($current_block_GPA_VALUE/$current_block_GPA_WEIGHT),2).'</td>
							
							</tr>';

							$txt .= '<tr>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%"></td>
							<td style=" padding-bottom:10px; font-size:32px;" width="48%" align="right" >Cumulative Total</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CA_total.'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="8%" align="right">'.number_format_value_checker($cummilative_block_HOURS_total,2).'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CE_total.'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="7%" align="right"></td>
							<td style=" padding-bottom:10px; font-size:32px;" width="7%" align="right">'.number_format_value_checker(($cummilative_block_GPA_VALUE/$cummilative_block_GPA_WEIGHT),2).'</td>
							
							</tr>';
							
						}

						// if($first_flag){
							$txt .= "<tr colspan='8'><h2 style='font-size:20px !important'>TERM : ".date("m/d/Y", strtotime($res_course->fields['BEGIN_DATE_1'])).' to '.date("m/d/Y", strtotime($res_course->fields['END_DATE']))."</h2></tr>";
							$prev_term_block = $res_course->fields['TERM_BLOCK'];
						// }else{
						// 	$txt .= "<tr colspan='8'><h2 style='font-size:20px !important'>TERM : $prev_term_block_str </h2></tr>";
						// 							$prev_term_block = $res_course->fields['TERM_BLOCK'];
						// }
							
							

							$first_flag  = false;
						

							$current_block_CA_total = '';
							$current_block_HOURS_total = '';
							$current_block_CE_total = '';
							$current_block_GPA_VALUE = '';
							$current_block_GPA_WEIGHT = '';

							$prev_term_block_str = date("m/d/Y", strtotime($res_course->fields['BEGIN_DATE_1'])).' to '.date("m/d/Y", strtotime($res_course->fields['END_DATE']));
						}


						$sql_course="SELECT TRANSCRIPT_CODE, 
											COURSE_DESCRIPTION, 
											S_STUDENT_COURSE.PK_COURSE_OFFERING, 
											FINAL_GRADE, 
											GRADE, 
											NUMERIC_GRADE, 
											NUMBER_GRADE, 
											CALCULATE_GPA, 
											UNITS_ATTEMPTED, 
											WEIGHTED_GRADE_CALC, 
											UNITS_COMPLETED, 
											UNITS_IN_PROGRESS, 
											COURSE_UNITS, 
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
											LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE, 
											M_COURSE_OFFERING_STUDENT_STATUS 
										WHERE 
											 M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
											AND SHOW_ON_TRANSCRIPT = 1 $en_cond 
										ORDER BY 
											TRANSCRIPT_CODE ASC";
						$res_course_2 = $db->Execute($sql_course);	
						if($res_course_2->fields['UNITS_COMPLETED'] == 1) {
							$COMPLETED_UNITS_2	 = $res_course_2->fields['COURSE_UNITS'];
							// $c_in_comp_tot  	+= $COMPLETED_UNITS;
							// $c_in_comp_sub_tot  += $COMPLETED_UNITS;
						}else{
							$COMPLETED_UNITS_2 = 0;
						}
						$txt .= '<tr>
									<td width="10%" >'.$BEGIN_DATE.'</td>
									<td width="10%" >'.$res_course->fields['TRANSCRIPT_CODE'].'</td>
									<td width="38%" >'.$res_course->fields['COURSE_DESCRIPTION'].'</td> 
									<td width="10%" align="right" >'.number_format_value_checker($res_course->fields['CREDIT_BASE'],0).'</td> 
									<td width="8%"  align="right" >'.$res_course->fields['HOURS'].'</td>
									<td width="10%" align="right" >'.number_format_value_checker($COMPLETED_UNITS,0).'</td>
									<td width="7%"  align="right">'.$res_course->fields['GRADE'].'</td>
									<td width="7%"  align="right" ></td>
								</tr>';
					 
						$current_block_CA_total  = $current_block_CA_total +  $res_course->fields['CREDIT_BASE'];
						$current_block_HOURS_total = $current_block_HOURS_total + $res_course->fields['HOURS'];
						if($res_course->fields['UNITS_COMPLETED'] == 1) {
						$current_block_CE_total = $current_block_CE_total + $COMPLETED_UNITS;
						}
						if($res_course->fields['CALCULATE_GPA'] == 1) {
						$current_block_GPA_VALUE = $current_block_GPA_VALUE + $GPA_VALULE;
						$current_block_GPA_WEIGHT = $current_block_GPA_WEIGHT + $GPA_WEIGHT;
						}
						$cummilative_block_CA_total = $cummilative_block_CA_total +  $res_course->fields['CREDIT_BASE'];
						$cummilative_block_HOURS_total = $cummilative_block_HOURS_total+ $res_course->fields['HOURS'];
						if($res_course->fields['UNITS_COMPLETED'] == 1) {
						$cummilative_block_CE_total = $cummilative_block_CE_total + $COMPLETED_UNITS;
						}
						if($res_course->fields['CALCULATE_GPA'] == 1) {
						$cummilative_block_GPA_VALUE = $cummilative_block_GPA_VALUE + $GPA_VALULE;
						$cummilative_block_GPA_WEIGHT = $cummilative_block_GPA_WEIGHT + $GPA_WEIGHT;
						}



						/* ticket #1240 */
						
						$res_course->MoveNext();
					} 
			/* Ticket # 1152 */
			
			//DIAM-781
			$gpa_weighted=0;
			if($gpa_value_total>0)
			{
				$gpa_weighted=$gpa_value_total/$gpa_weight_total;
			}

			/* ticket #1240 */
			$prev_term_block_str = date("m/d/Y", strtotime($res_course->fields['BEGIN_DATE_1'])).' to '.date("m/d/Y", strtotime($res_course->fields['END_DATE']));
			$txt .= '<tr>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="48%" align="right" >TERM : '.$prev_term_block_str.'  Total</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$current_block_CA_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="8%" align="right">'.number_format_value_checker($current_block_HOURS_total,2).'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$current_block_CE_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right">'.number_format_value_checker(($current_block_GPA_VALUE/$current_block_GPA_WEIGHT),2).'</td>
							
							</tr>';

							$txt .= '<tr>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%"></td>
							<td style=" padding-bottom:10px; font-size:32px;" width="48%" align="right" >Cumulative Total </td>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CA_total.'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="8%" align="right">'.number_format_value_checker($cummilative_block_HOURS_total,2).'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CE_total.'</td>
							<td style=" padding-bottom:10px; font-size:32px;" width="7%" align="right"></td>
							<td style=" padding-bottom:10px; font-size:32px;" width="7%" align="right">'.number_format_value_checker(($cummilative_block_GPA_VALUE/$cummilative_block_GPA_WEIGHT),2).'</td>
							
							</tr>
							</table>';
			$txt .= '
			</table> 
			<table border="0" cellspacing="0" cellpadding="3" width="687px" >
					 
			<tr>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="48%" align="right" ><b> Student Transcript Total </b></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CA_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="8%" align="right">'.number_format_value_checker($cummilative_block_HOURS_total,2).'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="10%" align="right">'.$cummilative_block_CE_total.'</td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right"></td>
							<td style="border-top:0.5px solid #c0c0c0; padding-bottom:10px; font-size:32px;" width="7%" align="right">'.number_format_value_checker(($cummilative_block_GPA_VALUE/$cummilative_block_GPA_WEIGHT),2).'</td>
							
							</tr>
					</table> ';
					/* ticket #1240 */

		/* Ticket # 1187 */	
		/* Ticket # 1219 */
		if($_GET['inc_att'] == 1) {
			$en_cond1 = "";
			$en_cond2 = "";
			if($PK_STUDENT_ENROLLMENTS_OF_CURRENT_PK_STUDENT_MASTER != ''){
				$en_cond1 = " AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENTS_OF_CURRENT_PK_STUDENT_MASTER) ";
				$en_cond2 = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENTS_OF_CURRENT_PK_STUDENT_MASTER) ";
			}
			// $en_cond1 .= " AND PK_CAMPUS_PROGRAM IN ($NA_PROGRAMS) ";
			// $en_cond2 .= " AND PK_CAMPUS_PROGRAM IN ($NA_PROGRAMS) ";
			$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
			$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
			
			/* Ticket # 1219 */
			$excluded_att_code  = "";
			//$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
			//$excluded_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
			
			$exc_att_code_arr = array();
			$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
			while (!$res_exc_att_code->EOF) {
				$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
				$res_exc_att_code->MoveNext();
			}

			$exclude_cond  = "";
			if(!empty($exc_att_code_arr))
				$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (".implode(",",$exc_att_code_arr).") ";
				
			/* Ticket # 1219 */
			
			$res_enroll = $db->Execute("SELECT SUM(M_CAMPUS_PROGRAM.HOURS) as HOURS FROM S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM $en_cond  ");
			
			$res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond1 ");
			
			//$res_sch_all = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 $exclude_cond $en_cond2 "); /* Ticket # 1219 */

			//$res_sch = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 $exclude_cond $en_cond2 "); /* Ticket # 1219 */
			
			$SCHEDULED_HOUR 	 = 0;
			$COMP_SCHEDULED_HOUR = 0;
			$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $en_cond2 ");
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
			
			$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) $en_cond2 ");
			
			$res_attended_all = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) $en_cond2 ");

			// $txt .= '<br /><br /><br /><br />
			// <table border="0" cellspacing="0" cellpadding="3" width="100%" >
			// 	<tr nobr="true" >
			// 		<td><b>Attendance Summary</b><br /></td>
			// 	</tr>
			// 	<tr nobr="true" >
			// 		<td width="15%" ></td>
			// 		<td width="70%" >
			// 			<table border="0" cellspacing="0" cellpadding="3" width="100%" >
			// 				<tr>
			// 					<td width="100%" >
			// 						<table border="1" cellspacing="0" cellpadding="3" width="100%" > 
			// 							<tr>
			// 								<td width="32%" >Total Required Hours</td>
			// 								<td width="13%" align="right" >'.number_format_value_checker($res_enroll->fields['HOURS'],2).'</td>
			// 								<td width="32%" >Total Attended Hours</td>
			// 								<td width="13%" align="right" >'.number_format_value_checker($res_attended_all->fields['ATTENDED_HOUR'],2).'</td>
			// 							</tr>
			// 							<tr>
			// 								<td width="32%" >Total Transfer Hours</td>
			// 								<td width="13%" align="right" >'.number_format_value_checker($res_trans->fields['HOUR'],2).'</td>
			// 								<td width="32%" >Total Hours Remaining</td>
			// 								<td width="13%" align="right" >'.number_format_value_checker(($res_enroll->fields['HOURS'] - $res_attended->fields['ATTENDED_HOUR'] - $res_trans->fields['HOUR']),2).'</td>
			// 							</tr>
			// 							<tr>
			// 								<td width="32%" >Total Scheduled Hours</td>
			// 								<td width="13%" align="right" >'.number_format_value_checker($SCHEDULED_HOUR,2).'</td>
			// 								<td width="32%" >Attendance Percentage</td>
			// 								<td width="13%" align="right" >'.number_format_value_checker(($res_attended->fields['ATTENDED_HOUR'] / $COMP_SCHEDULED_HOUR * 100),2).'%</td>
			// 							</tr>
			// 						</table>									
			// 					</td>
			// 				</tr>
			// 			</table>
			// 		</td>
			// 		<td width="15%" ></td>
			// 	</tr>
			// </table>';
		}
		/* Ticket # 1187 */	
		/* Ticket # 1219 */
		// echo $txt;exit; 
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}

	$file_name = $report_name.'_'.uniqid().'.pdf';
	
	if($one_stud_per_pdf == 0) {
		$file_dir_1 = 'temp/';
		$pdf->Output($file_dir_1.$file_name, 'FD');
	} else {
		// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
		$file_dir_1 = '../backend_assets/tmp_upload/';

		$file_name  = str_replace(' ' , '_',$report_name).'_'.uniqid().'.pdf';
		$pdf->Output($file_dir_1.$file_name, 'F');
	}

	return $file_dir_1.$file_name;	
}

$report_name = "";
if($_GET['id'] == '') {
	if($_SESSION['eid'] == '') {
		$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
		//$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
	} else
		$_GET['eid'] = $_SESSION['eid'];
	
	$report_name = "Academic Review";
	
	student_transcript_list_pdf($_SESSION['PK_STUDENT_MASTER'], 0, $report_name);
} else {
	$report_name = "Student Academic Transcript";
	
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
		
		// $folder = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/student_transcript_list';
		$folder = '../backend_assets/tmp_upload/student_transcript_list';
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
			$PK_STUDENT_MASTERS_ZIP = $_GET['id'];
			$NA_PROGRAMS = $db->Execute("SELECT GROUP_CONCAT(PK_CAMPUS_PROGRAM) AS PK_CAMPUS_PROGRAMS FROM M_CAMPUS_PROGRAM WHERE (CODE like 'NA' OR CODE like '%-NA' OR CODE like 'NA-%' OR CODE like '%-NA-%') AND PK_ACCOUNT = '".$_SESSION['PK_ACCOUNT']."' GROUP BY PK_ACCOUNT ")->fields['PK_CAMPUS_PROGRAMS']; 

			$STUDENTS_HAVING_NA_PROGRAMS_ZIP = $db->Execute("SELECT GROUP_CONCAT(PK_STUDENT_MASTER) AS PK_STUDENT_MASTERS  FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '".$_SESSION['PK_ACCOUNT']."'  AND PK_STUDENT_MASTER IN ($PK_STUDENT_MASTERS_ZIP) GROUP BY PK_ACCOUNT")->fields['PK_STUDENT_MASTERS']; 
			$PK_STUDENT_MASTER_ARR = explode(",",$STUDENTS_HAVING_NA_PROGRAMS_ZIP);
			foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
				$file_name_1 = student_transcript_list_pdf($PK_STUDENT_MASTER, 1, $report_name);
				
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
		{	
			$pdf = new Fpdi();
			$merge_array = [];		
			$exploded_865 = explode(',',$_GET['id']); 
			foreach($exploded_865 as $exploded_std ){
				// echo "Hi $exploded_std <br>";
				$merge_array[] = student_transcript_list_pdf($exploded_std, 1, $report_name);
			}
			// print_r($merge_array);
			foreach ($merge_array as $file) {
				$pages = $pdf->setSourceFile($file);
				for ($i = 1; $i <= $pages; $i++) {
					$tplIdx = $pdf->importPage($i);
					$pdf->AddPage();
					$pdf->useTemplate($tplIdx);
				}
			}
			foreach ($merge_array as $file) {
			unlink($file);
			}
			$pdf->Output('Student_Academic_Transcript_'.$_SESSION['PK_ACCOUNT'].'_'.time().'.pdf', 'D');
			
		
		}
}
