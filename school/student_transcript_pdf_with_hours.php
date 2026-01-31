<?php ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require_once('../global/config.php');
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
class MYPDF extends TCPDF {

	/** DIAM - 1315 **/
	public $campus;

    public function setCampus($var){
        $this->campus = $var;
    }
	/** End DIAM - 1315 **/

    public function Header() {
		global $db;
		
		if($_GET['id'] == '' || $_GET['uno'] == 1) { 
			// get the current page break margin
			$bMargin = $this->getBreakMargin();
			// get current auto-page-break mode
			$auto_page_break = $this->AutoPageBreak;
			// disable auto-page-break
			$this->SetAutoPageBreak(false, 0);
			// set bacground image
		
			if($_GET['id'] == '' || $_GET['uno'] == 1) { 
				$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6");
			} else {
				$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4");
			}

			$ImageW = 175; //WaterMark Size
			$ImageH = 175;

			//$pdf->setPage(1); //WaterMark Page    

			$myPageWidth  = $this->getPageWidth();
			$myPageHeight = $this->getPageHeight() - ($res_type->fields['FOOTER_LOC'] + 10);
			$myX = ( $myPageWidth / 2 ) - 90;  //WaterMark Positioning
			$myY = ( $myPageHeight / 2 ) - 80;

			$this->SetAlpha(0.30);
			$this->Image('../backend_assets/images/unoffical_1.png', $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);

			//Likewise can be added for all pages after writing all pages.
			$this->SetAlpha(1);
			
			// restore auto-page-break status
			$this->SetAutoPageBreak($auto_page_break, $bMargin);
			// set the starting point for the page content
			$this->setPageMark();
		}
		
		if($_SESSION['temp_id'] == $this->PK_STUDENT_MASTER){
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			$this->SetMargins('', 25, '');
			
			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			
			if($_GET['uno'] == 1) {
				$this->SetX(130);
				$this->Cell(55, 8, "Unofficial Student Transcript", 0, false, 'L', 0, '', 0, false, 'M', 'L');
			} else {
				$this->SetX(150);
				$this->Cell(55, 8, "Student Transcript", 0, false, 'L', 0, '', 0, false, 'M', 'L');
			}
		} else {
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;
		}
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);

		/** DIAM - 1315 **/
		$PK_CAMPUS = $this->campus;
		
		if($_GET['id'] == '' || $_GET['uno'] == 1) { 
			//$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6");
			$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS'  "); 
		} else {
			//$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4");
			$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS'  "); 
		}
		/** End DIAM - 1315 **/
		
		/*$txt = "";
		if($res_type->fields['BOLD'] == 1)
			$txt .= "B";
		if($res_type->fields['ITALIC'] == 1)
			$txt .= "I";*/
		
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

function student_transcript_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf, $report_name){
	global $db;
	
	$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);
	
	if($_GET['id'] == '' || $_GET['uno'] != '') { 
		$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6");
	} else {
		$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4");
	}
	$FOOTER_LOC = $res_type->fields['FOOTER_LOC'];
	$BASE 		= 48 + $FOOTER_LOC; //DIAM-1639
	
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonOSPacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 10, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, $BASE);
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
	
	$data_count = array(); // DIAM-2239
	
	require_once("pdf_custom_header.php"); //Ticket # 1588
	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {

	  $en_conds = "";
	  if($_GET['eid'] != ''){
		  $en_conds = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
	  }
	  $res_term_dub = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, S_TERM_MASTER.TERM_DESCRIPTION FROM S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, M_COURSE_OFFERING_STUDENT_STATUS WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_conds ORDER By BEGIN_DATE_1 ASC");
	  if($res_term_dub->RecordCount() > 0) // DIAM-2239
	  {
		
		$en_cond = "";
		if($_GET['eid'] != ''){
			$en_cond = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		}
		
		$res_stu = $db->Execute("select LAST_NAME, FIRST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, OLD_DSIS_STU_NO, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); //Ticket # 1157  
		
		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		
		$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588

		$pdf->STUD_NAME	 		= $res_stu->fields['NAME'];
		$pdf->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
		$pdf->startPageGroup();
		$pdf->AddPage();

		//DIAM-1401 
		if($_GET['current_enrol'] == ''){
			$res_std_enroll_id = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY IS_ACTIVE_ENROLLMENT DESC LIMIT 1 "); 
			$_GET['current_enrol'] = $res_std_enroll_id->fields['PK_STUDENT_ENROLLMENT'];
			
		}
		/** DIAM - 1315 **/
		$res_camp = $db->Execute("select PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_ENROLLMENT = $_GET[current_enrol] ");
		$pdf->setCampus($res_camp->fields['PK_CAMPUS']); 
		/** End DIAM - 1315 **/

		/** DIAM - 1314 **/
		if (has_student_trascript_access($_SESSION['PK_ACCOUNT'])) 
		{
			$stud_id = $res_stu->fields['OLD_DSIS_STU_NO'];
			$new_report_name = 'Student Transcript';
		}
		else{
			$stud_id = $res_stu->fields['STUDENT_ID'].'<br />DOB: '.$res_stu->fields['DOB'];
			$new_report_name = $report_name;
		}
		/** End DIAM - 1314 **/

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
									<td style="width:60%" >
										<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
									</td>
									<td align="right" style="width:40%" >
										<span style="line-height:5px" >ID: '.$stud_id.'<br />Phone: '.$res_add->fields['HOME_PHONE'].'</span>
									</td>
								</tr>';
		/* Ticket # 1588 */
		
								/* Ticket # 1169 */
								$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond ORDER By BEGIN_DATE ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");
								while (!$res_type->EOF) {
									$PK_STUDENT_ENROLLMENT 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
									$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
									
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
									$res_type->MoveNext();
								}
								/* Ticket # 1169 */
					$txt .= '</table>
						</td>
					</tr>
				</table>';
			
		/* ticket #1240 */		
		$txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>
				<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td width="100%" align="center" ><b><i style="font-size:50px">'.$new_report_name.'</i></b><br /></td>
					</tr>
					<tr>
						<td width="20%" ><br /><br /><u><b>Course</b></u></td>
						<td width="30%" ><br /><br /><u><b>Course Description</b></u></td>';
						
						if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
							$txt .= '<td width="10%" ><br /><br /><u><b>Grade</b></u></td>';
							
						if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
							$txt .= '<td width="10%" align="right" ><b>Numeric</b><br /><u><b>Grade</b></u></td>';
						
				// NUEVA COLUMNA HOURS
				$txt .= '<td width="10%" align="right" ><br /><br /><u><b>Hours</b></u></td>
						<td width="10%" align="right" ><b>Units</b><br /><u><b>Attempted</b></u></td>
						<td width="10%" align="right" ><b>Units</b><br /><u><b>Completed</b></u></td>
						<td width="10%" align="right" ><br /><br /><u><b>GPA</b></u></td>
					</tr>';
					/* ticket #1240 */	
					
					$total_cum_rec		= 0;
					$c_in_num_grade_tot = 0; //ticket #1240
					$c_in_att_tot 		= 0;
					$c_in_comp_tot 		= 0;
					$c_in_cu_gnu 		= 0;
					$c_in_gpa_tot 		= 0;

					$summation_of_gpa      = 0;
					$summation_of_weight   = 0;
					
					$Denominator = 0;
					$Numerator 	 = 0;
					$Numerator1  = 0;

					// TOTAL DE HORAS (TRANSFER + COURSES)
					$c_hours_total = 0; // DVB HOURS CUMULATIVE
					
					if($_GET['exclude_tc'] != 1) {
						$Sub_Denominator = 0;
						$Sub_Numerator 	 = 0;
						$Sub_Numerator1  = 0;
						
						$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, NUMBER_GRADE, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND  CALCULATE_GPA = 1 AND SHOW_ON_TRANSCRIPT = 1 $en_cond ");
						while (!$res_tc->EOF) {
							//diam-726
							$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
							$res_grade_top = $db->Execute("SELECT NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' "); 
							$Denominator += $res_tc->fields['UNITS'];
							$Numerator1	 += $res_tc->fields['UNITS'] * $res_grade_top->fields['NUMBER_GRADE'];
							
							$Sub_Denominator += $res_tc->fields['UNITS'];
							$Sub_Numerator1	 += $res_tc->fields['UNITS'] * $res_grade_top->fields['NUMBER_GRADE'];
						
							$res_tc->MoveNext();
						}
						//DIAM-781
						$sql="SELECT S_COURSE.TRANSCRIPT_CODE, CREDIT_TRANSFER_STATUS, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, S_STUDENT_CREDIT_TRANSFER.TC_NUMERIC_GRADE, S_STUDENT_CREDIT_TRANSFER.GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_GRADE.NUMBER_GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS,
						CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
						CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT,
						S_STUDENT_CREDIT_TRANSFER.HOUR
						FROM S_STUDENT_CREDIT_TRANSFER
						LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE  
						LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
						LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
						WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
						AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
						AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER BY S_COURSE.TRANSCRIPT_CODE ASC";
						
						$res_tc = $db->Execute($sql); //ticket #1240
						if($res_tc->RecordCount() > 0) {
							$txt .= '<tr>
										<td width="100%" ><i style="font-size:45px">Term: Transfer</i></td>
									</tr>';
						}
						
						$total_rec				= 0;
						$c_in_num_grade_sub_tot = 0;//ticket #1240
						$c_in_att_sub_tot 		= 0;
						$c_in_comp_sub_tot 		= 0;
						$c_in_cu_sub_gnu 		= 0;
						$c_in_gpa_sub_tot 		= 0;

						//DIAM-781
						$tc_gpa_value_total=0;
						$tc_gpa_weight_total=0;
						$total_tc_gpa_weighted =0;

						// TOTAL HORAS TRANSFER
						$c_hours_transfer_sub = 0; // DVB HOURS por bloque de Transfer
						
						//DIAM-781
						while (!$res_tc->EOF) {
							
							$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT']; 
							$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
							
							$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, PROGRAM_TRANSCRIPT_CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
							$COMPLETED_UNITS	 = 0;
							$ATTEMPTED_UNITS	 = 0;

							// HORAS TRANSFER
							$HOURSCOURSE = 0;
							if($res_tc->fields['HOUR'] != ''){
								$HOURSCOURSE = $res_tc->fields['HOUR'];
							}
							
							//diam-726
							$res_grade_data = $db->Execute("SELECT UNITS_ATTEMPTED,UNITS_COMPLETED,CALCULATE_GPA,NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' "); 
							//diam-726
							if($res_grade_data->fields['UNITS_ATTEMPTED'] == 1)
								$ATTEMPTED_UNITS = $res_tc->fields['UNITS'];
							
							$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
							$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
							
							// sumar horas
							$c_hours_total      += $HOURSCOURSE;
							$c_hours_transfer_sub += $HOURSCOURSE;
							
							//diam-726
							if($res_grade_data->fields['UNITS_COMPLETED'] == 1) {
								$COMPLETED_UNITS 	 = $res_tc->fields['UNITS'];
								$c_in_comp_tot  	+= $COMPLETED_UNITS;
								$c_in_comp_sub_tot  += $COMPLETED_UNITS;
							}
						
							$gnu = 0;
							//diam-726
							if($res_grade_data->fields['CALCULATE_GPA'] == 1) {
								$gnu 				 = $res_tc->fields['UNITS'] * $res_grade_data->fields['NUMBER_GRADE']; //diam-726
								$c_in_cu_gnu 		+= $gnu; 
								$c_in_cu_sub_gnu 	+= $gnu; 
								
								$gpa				= $gnu / $COMPLETED_UNITS;;
								$c_in_gpa_sub_tot 	+= $gpa;
								$c_in_gpa_tot 		+= $gpa;
								
								$c_in_num_grade_sub_tot	+= $res_tc->fields['TC_NUMERIC_GRADE']; //ticket #1240
								$c_in_num_grade_tot		+= $res_tc->fields['TC_NUMERIC_GRADE']; //ticket #1240
								
								$total_rec++;
								$total_cum_rec++;

								// calulated gpa DIAM-781
								$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
								$tc_gpa_value_total 		+= $TC_GPA_VALULE; 
								$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
								$tc_gpa_weight_total 		+= $TC_GPA_WEIGHT; 
								// calulated gpa DIAM-781

								$summation_of_gpa     += $TC_GPA_VALULE;
								$summation_of_weight  += $TC_GPA_WEIGHT;
							}
							
							/* ticket #1240 */	
							$txt .= '<tr>
										<td width="20%" >'.$res_tc->fields['TRANSCRIPT_CODE'].'</td>
										<td width="30%" >'.$res_tc->fields['COURSE_DESCRIPTION'].'</td>';
										
										if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
											$txt .= '<td width="10%" >'.$res_tc->fields['GRADE'].'</td>';
											
										if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
											$txt .= '<td width="10%" align="right" >'.$res_tc->fields['TC_NUMERIC_GRADE'].'</td>';
										
								// HOURS
								$txt .= '<td width="10%" align="right" >'.number_format_value_checker($HOURSCOURSE,2).'</td>
										<td width="10%" align="right" >'.number_format_value_checker($ATTEMPTED_UNITS,2).'</td>
										<td width="10%" align="right" >'.number_format_value_checker($COMPLETED_UNITS,2).'</td>
										<td width="10%" align="right" ></td>
									</tr>';
							/* ticket #1240 */	
										
							$res_tc->MoveNext();
						} 
						

						//DIAM-781
					    $tc_gpa_weighted=0;
						if($tc_gpa_value_total>0)
						{
							$tc_gpa_weighted=$tc_gpa_value_total/$tc_gpa_weight_total;
							$total_tc_gpa_weighted +=$tc_gpa_weighted;
						}
						
						/* ticket #1240 */	
						/* Ticket # 1152 */

						if($res_tc->RecordCount() > 0) {
							$txt .= '<tr>
								<td width="50%" style="border-top:1px solid #c0c0c0" align="right" ><i>Term Transfer Total: </i></td>';
								
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ></td>';
									
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker(($c_in_num_grade_sub_tot/$total_rec),2).'</b></i></td>';
								
								// TOTAL HORAS TRANSFER
								$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($c_hours_transfer_sub,2).'</b></i></td>';

								$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_att_sub_tot,2).'</b></i></td>
								<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_comp_sub_tot,2).'</b></i></td>
								<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($tc_gpa_weighted,2).'</b></i></td>
							</tr>
							<tr>
								<td width="50%"  align="right" ><i>Cumulative Total: </i></td>';
								
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt .= '<td width="10%"  align="right" ></td>';
									
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt .= '<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker(($c_in_num_grade_tot / $total_cum_rec),2).'</b></i></td>';
								
							// TOTAL HORAS ACUMULADAS (TRANSFER HASTA AHORA)
							$txt .= '<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker($c_hours_total,2).'</b></i></td>';

							$txt .= '<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_att_tot,2).'</b></i></td>
								<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_comp_tot,2).'</b></i></td>
								<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</b></i></td>
							</tr>';
						}
						
						$txt .= '</table>';
					}
					
					$res_term = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, S_TERM_MASTER.TERM_DESCRIPTION FROM S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, M_COURSE_OFFERING_STUDENT_STATUS WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER By BEGIN_DATE_1 ASC");
					if($res_term->RecordCount() > 0 && $_GET['json_check'] == '1') // DIAM-2239
					{
						$data_count[] = $res_term->RecordCount();
					}
					// End DIAM-2239
					while (!$res_term->EOF) {
						$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];
						
						/** DIAM - 1314 **/
						if (has_student_trascript_access($_SESSION['PK_ACCOUNT'])) 
						{
							$BEGIN_DATE 	= $res_term->fields['TERM_DESCRIPTION'];
						}
						else{
							$BEGIN_DATE 	= $res_term->fields['BEGIN_DATE'];
						}
						/** End DIAM - 1314 **/

						//DIAM - 1377
						$dash_desc ='';
						if(has_wvjc_access_transcript_desc($_SESSION['PK_ACCOUNT'],1)){ 
							if(has_wvjc_access_show_only_term_desc($_SESSION['PK_ACCOUNT'],1)){ 
								$BEGIN_DATE_DESC 	= ($res_term->fields['TERM_DESCRIPTION'] != '') ? $res_term->fields['TERM_DESCRIPTION'] : $res_term->fields['BEGIN_DATE'];
							}else{
								if(!empty($res_term->fields['TERM_DESCRIPTION'])){
									$dash_desc = ' - '.$res_term->fields['TERM_DESCRIPTION'];
								}	
								$BEGIN_DATE_DESC 	= $res_term->fields['BEGIN_DATE'].$dash_desc;
							}
						}
						//DIAM - 1377

						$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" >
									<tr nobr="true">
										<td width="100%" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" ><i style="font-size:45px">Term: '.$BEGIN_DATE_DESC.'</i></td>
												</tr>';
						
						$total_rec				= 0;
						$c_in_num_grade_sub_tot = 0; //Ticket # 1240
						$c_in_att_sub_tot 		= 0;
						$c_in_comp_sub_tot 		= 0;
						$c_in_cu_sub_gnu 		= 0;
						$c_in_gpa_sub_tot 		= 0;
						
						$Sub_Denominator = 0;
						$Sub_Numerator 	 = 0;
						$Sub_Numerator1  = 0;

						$gpa_value_total=0;
						$gpa_value_sub_total=0;
						$gpa_weight_total=0;
						$gpa_sub_weight_total=0;

						// TOTAL HORAS POR TERM
						$c_hours_term_sub = 0; // DVB HOURS POR TERM
						
						/* Ticket # 1152 */
						$res_course = $db->Execute("select TRANSCRIPT_CODE, COURSE_DESCRIPTION, S_STUDENT_COURSE.PK_COURSE_OFFERING,FINAL_GRADE, GRADE, NUMERIC_GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED,WEIGHTED_GRADE_CALC, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS,CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT from S_STUDENT_COURSE LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE ,M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER BY TRANSCRIPT_CODE ASC ");	
						while (!$res_course->EOF) {
							$Denominator += $res_course->fields['COURSE_UNITS'];
							$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
							$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
							
							$Sub_Denominator += $res_course->fields['COURSE_UNITS'];
							$Sub_Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
							$Sub_Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
						
							$res_course->MoveNext();
						}
						//DIAM-781
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
											) ELSE 0 END AS GPA_WEIGHT,
											S_COURSE.HOURS
										FROM 
											S_STUDENT_COURSE 
											LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
											LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
											LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE, 
											M_COURSE_OFFERING_STUDENT_STATUS 
										WHERE 
											PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
											AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' 
											AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
											AND SHOW_ON_TRANSCRIPT = 1 $en_cond 
										ORDER BY 
											TRANSCRIPT_CODE ASC";
						$res_course = $db->Execute($sql_course);	
						while (!$res_course->EOF) { 
							
							$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
							$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
							$COMPLETED_UNITS	 = 0;
							$ATTEMPTED_UNITS	 = 0;

							// HORAS CURSO
							$HOURSCOURSE = 0;
							if($res_course->fields['HOURS'] != ''){
								$HOURSCOURSE = $res_course->fields['HOURS'];
							}
							
							if($res_course->fields['UNITS_ATTEMPTED'] == 1)
								$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
							
							$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
							$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 

							// sumar horas
							$c_hours_total      += $HOURSCOURSE;
							$c_hours_term_sub   += $HOURSCOURSE;
							
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
								
								$total_rec++;
								$total_cum_rec++;
								
								$c_in_num_grade_sub_tot += $res_course->fields['NUMERIC_GRADE'];
								$c_in_num_grade_tot		+= $res_course->fields['NUMERIC_GRADE'];

								// calulated gpa DIAM-781
								$GPA_VALULE 				 = $res_course->fields['GPA_VALUE']; 
								$gpa_value_total 		+= $GPA_VALULE; 
								$GPA_WEIGHT 				 = $res_course->fields['GPA_WEIGHT']; 
								$gpa_weight_total 		+= $GPA_WEIGHT; 

								$summation_of_gpa    += $GPA_VALULE;
								$summation_of_weight += $GPA_WEIGHT;
							}

							//DIAM-1377
							$COURSE_DESCRIPTION='';
							if(has_wvjc_access_transcript_desc($_SESSION['PK_ACCOUNT'],1)){ 
								$COURSE_DESCRIPTION = $res_course->fields['COURSE_DESCRIPTION'];
							}
							//DIAM-1377

							
							/* ticket #1240 */	
							
							$txt .= '<tr>
										<td width="20%" >'.$res_course->fields['TRANSCRIPT_CODE'].'</td>
										<td width="30%" >'.$COURSE_DESCRIPTION.'</td>';
										
										if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
											$txt .= '<td width="10%" >'.$res_course->fields['GRADE'].'</td>';
											
										if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
											$txt .= '<td width="10%"  align="right" >'.$res_course->fields['NUMERIC_GRADE'].'</td>';
										
									$txt .= '<td width="10%" align="right" >'.number_format_value_checker($HOURSCOURSE,2).'</td>
										<td width="10%" align="right" >'.number_format_value_checker($ATTEMPTED_UNITS,2).'</td>
										<td width="10%" align="right" >'.number_format_value_checker($COMPLETED_UNITS,2).'</td>
										<td width="10%" align="right" ></td>
									</tr>';
							/* ticket #1240 */	
							
							$res_course->MoveNext();
						} 
						
						/* Ticket # 1152 */
						
					    $gpa_weighted=0;
						if($gpa_value_total>0)
						{
							$gpa_weighted=$gpa_value_total/$gpa_weight_total;
							$total_tc_gpa_weighted +=$gpa_weighted;
						}
						
						/* ticket #1240 */
						$txt .= '<tr>
								<td width="50%" style="border-top:1px solid #c0c0c0" align="right" ><i>Term '.$BEGIN_DATE.' Total: </i></td>';
								
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ></td>';
									
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
									$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker(($c_in_num_grade_sub_tot/$total_rec),2).'</b></i></td>';
								
						// TOTAL HORAS POR TERM
						$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($c_hours_term_sub,2).'</b></i></td>';
									
						$txt .= '<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_att_sub_tot,2).'</b></i></td>
							<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_comp_sub_tot,2).'</b></i></td>
							<td width="10%" style="border-top:1px solid #c0c0c0" align="right" ><i><b style="font-size:30px">'.number_format_value_checker($gpa_weighted,2).'</b></i></td>
						</tr>
						<tr>
							<td width="50%" colspan="2" align="right" ><i><b style="font-size:35px">Cumulative Total: </b></i></td>';
							
							if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
								$txt .= '<td width="10%" align="right" ></td>';
								
							if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
								$txt .= '<td width="10%" align="right" ><i><b style="font-size:30px">'.number_format_value_checker(($c_in_num_grade_tot/$total_cum_rec),2).'</b></i></td>';
							
					// TOTAL HORAS ACUMULADAS (TRANSFER + TERMS)
					$txt .= '<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker($c_hours_total,2).'</b></i></td>';

					$txt .= '<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_att_tot,2).'</b></i></td>
							<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_comp_tot,2).'</b></i></td>
							<td width="10%" align="right" ><i><b style="font-size:35px">'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</b></i></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>';
		/* ticket #1240 */	
						
						$res_term->MoveNext();
					}
					
			/* Ticket # 1187 */	
			/* Ticket # 1219 */
			if($_GET['inc_att'] == 1) {
				$en_cond1 = "";
				$en_cond2 = "";
				if($_GET['eid'] != ''){
					$en_cond1 = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
					$en_cond2 = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
				}
				$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
				$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
				
				/* Ticket # 1219 */
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
					
				/* Ticket # 1219 */
				
				$res_enroll = $db->Execute("SELECT SUM(M_CAMPUS_PROGRAM.HOURS) as HOURS FROM S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM $en_cond  ");
				
				$res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond1 ");
				
				$TO_DATE  = date('Y-m-d');
				$att_cond = " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') <= '$TO_DATE'  ";
				
				$SCHEDULED_HOUR 	 = 0;
				$COMP_SCHEDULED_HOUR = 0;
				$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $en_cond2 $att_cond");
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
												<td width="13%" align="right" >'.number_format_value_checker($COMP_SCHEDULED_HOUR,2).'</td><!--DIAM-2300-->
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
			/* Ticket # 1219 */
					
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	  } // DIAM-2239, If End for Data check
	}

	// DIAM-2239
	if(empty($data_count) && $_GET['json_check'] == '1' && $_GET['zip'] == '0') 
	{
		header('Content-type: application/json; charset=utf-8');
		$data['error'] = "No data in the report for the selections you made.";
		echo json_encode($data);exit;
	}
	// End DIAM-2239

	$file_name = $report_name.'_'.uniqid().'.pdf';

	// DIAM-2367
	$FIRST_NAME = remove_special_character_from_string($res_stu->fields['FIRST_NAME']);
	$LAST_NAME  = remove_special_character_from_string($res_stu->fields['LAST_NAME']);
	// End DIAM-2367
	
	// DIAM-2239
	if($_GET['json_check'] == '1')
	{
		if($one_stud_per_pdf == 0) {
			$file_dir_1 = 'temp/';
			$pdf->Output($file_dir_1.$file_name, 'F');

			header('Content-type: application/json; charset=utf-8');
			$response["file_name"] = $file_name;
			$response["path"] =  $file_dir_1.$file_name; 
			echo json_encode($response);
		} else {
			$file_dir_1 = '../backend_assets/tmp_upload/';
	
			$file_name  = $LAST_NAME.'_'.$FIRST_NAME.'-'.$res_stu->fields['STUDENT_ID'].'-'.$report_name.'_'.$PK_STUDENT_MASTER.'.pdf';
			$pdf->Output($file_dir_1.$file_name, 'F');

			$record['file_name'] = $file_name;
			$record['record_count'] = count($data_count);

			return $record;
		}
		
	}
	else{
		if($one_stud_per_pdf == 0) {
			$file_dir_1 = 'temp/';
			$pdf->Output($file_dir_1.$file_name, 'FD');
		} else {
			$file_dir_1 = '../backend_assets/tmp_upload/';
	
			$file_name  = $LAST_NAME.'_'.$FIRST_NAME.'-'.$res_stu->fields['STUDENT_ID'].'-'.$report_name.'_'.$PK_STUDENT_MASTER.'.pdf';
			$pdf->Output($file_dir_1.$file_name, 'F');
		}
		
		return $file_name;
	}
	// End DIAM-2239
	
}


$uno = 'Official '; //Ticket # 1149
if($_GET['uno'] == 1)
	$uno = 'Unofficial ';

$report_name = "";
if($_GET['id'] == '') {
	if($_SESSION['eid'] == '') {
		$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
		//$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
	} else
		$_GET['eid'] = $_SESSION['eid'];
	
	$uno = 'Unofficial ';
	$report_name = "Academic Review By Term Report";
	
	student_transcript_pdf($_SESSION['PK_STUDENT_MASTER'], 0, $report_name);
} else {
	$report_name = $uno."Student Transcript";
	
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
		
		// $folder = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/student_transcripts';
		$folder = '../backend_assets/tmp_upload/student_transcripts';
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
				$file_name_1 = student_transcript_pdf($PK_STUDENT_MASTER, 1, $report_name);
				$total_count[] = $file_name_1['record_count']; // DIAM-2239
				if($file_name_1['record_count'] != '0') // DIAM-2239
				{	
					// $za->addFile('../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1, $file_name_1);
					$za->addFile('../backend_assets/tmp_upload/'.$file_name_1['file_name'], $file_name_1['file_name']);
					
					// $file_name_arr[] = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1;
					$file_name_arr[] = '../backend_assets/tmp_upload/'.$file_name_1['file_name'];
				}
			}
			// DIAM-2239
			$record_count = array_sum($total_count);
			if(empty($record_count) && $record_count == '0') 
			{
				header('Content-type: application/json; charset=utf-8');
				$data['error'] = "No data in the report for the selections you made.";
				echo json_encode($data);exit;
			}
			// End DIAM-2239

			$za->close();
			
			unlinkRecursive("$folder/",0);
			@rmdir($folder);
			
			foreach($file_name_arr as $file_name_2)
				unlink($file_name_2);
			
			// DIAM-2239
			if($_GET['json_check'] == '1')
			{
				header('Content-type: application/json; charset=utf-8');
				$response = array();
				$response['path'] = $zip_file_name;
				$response['file_name'] = 'student_transcripts.zip';
				echo json_encode($response);
			}
			else{
				header("location:".$zip_file_name);
			}
			// End DIAM-2239
			
		}
	} else 
		student_transcript_pdf($_GET['id'], 0, $report_name);
}
