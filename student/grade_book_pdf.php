<? require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("../language/grade_book.php");
require_once("../school/check_access.php");

$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
	
require_once("../school/function_transcript_header.php");	
require_once("../school/pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
    }
    public function Footer() {
		global $db;
		
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

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 10, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 7, '', true);
$pdf->AddPage();

$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$LOGO = '';
if($res->fields['PDF_LOGO'] != '')
	$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';

$res_stu = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
	
$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 

$CONTENT = pdf_custom_header($_SESSION['PK_STUDENT_MASTER'], '', 2); //Ticket # 1588

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
						
						$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE,SESSION, BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ORDER By BEGIN_DATE_1 ASC, M_CAMPUS_PROGRAM.CODE ASC ");
						while (!$res_type->EOF) {
							$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
							$PK_STUDENT_ENROLLMENT2 = $res_type->fields['PK_STUDENT_ENROLLMENT'];
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
			<thead>
				<tr>
					<td width="25%" style="border-bottom:1px solid #000;" ><b>'.TERM.'</b></td>
					<td width="25%" style="border-bottom:1px solid #000;" ><b>'.COURSE.'</b></td>
					<td width="30%" style="border-bottom:1px solid #000;" ><b>'.DESCRIPTION.'</b></td>
					<td width="10%" style="border-bottom:1px solid #000;" align="right" ><b>'.STUDENT_POINTS.'</b></td>
					<td width="10%" style="border-bottom:1px solid #000;" align="right" ><b>'.TOTAL_POINTS.'</b></td>
				</tr>
			</thead>
			<tbody>';

$stu_total 	= 0;
$total 		= 0;
$PK_STUDENT_GRADE_VALUE = array(); // DIAM-1527		
$result1 = $db->Execute($_SESSION['QUERY']);
while (!$result1->EOF) {
	$PK_COURSE_OFFERING_GRADE = $result1->fields['PK_COURSE_OFFERING_GRADE'];
	
	$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS,PK_STUDENT_MASTER FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' "); 
	$PK_STUDENT_GRADE  = $res_stu_grade->fields['PK_STUDENT_GRADE']; 
	$PK_STUDENT_MASTER = $res_stu_grade->fields['PK_STUDENT_MASTER']; 
	// DIAM-1527													
	$res_w = $db->Execute("SELECT WEIGHT FROM S_STUDENT_GRADE,S_COURSE_OFFERING_GRADE where PK_STUDENT_GRADE = '$PK_STUDENT_GRADE' AND S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE "); 
	// DIAM-1527
	if($res_stu_grade->fields['POINTS'] != '') {
		$PK_STUDENT_GRADE_VALUE []=$PK_STUDENT_GRADE; // DIAM-1527
		$stu_total 	+= $res_stu_grade->fields['POINTS'];
			
			if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
			$total += ($res_stu_grade->fields['POINTS'] * $res_w->fields['WEIGHT']); // DIAM-1527
			}else{
			$total 		+= $result1->fields['POINTS'];
			}
	}
	$txt .= '<tr>
				<td width="25%" >'.$result1->fields['BEGIN_DATE_1'] .'</td>
				<td width="25%" >'.$result1->fields['COURSE_CODE'] .'</td>
				<td width="30%" >'.$result1->fields['CODE'].' - '.$result1->fields['DESCRIPTION'] .'</td>
				<td width="10%" align="right" >'.$res_stu_grade->fields['POINTS'].'</td>
				<td width="10%" align="right" >'.$result1->fields['POINTS'] .'</td>
			</tr>';

	$result1->MoveNext();
} 
// DIAM-1527
if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
$PK_STUDENT_GRADE_VALUE = implode(',',$PK_STUDENT_GRADE_VALUE);
$res = $db->Execute("SELECT SUM(WEIGHTED_POINTS) as WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE,S_STUDENT_GRADE WHERE S_STUDENT_GRADE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GRADE IN ($PK_STUDENT_GRADE_VALUE) AND S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE = S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE "); 
$MAX_CURRENT_POINTS = number_format_value_checker($res->fields['WEIGHTED_POINTS'],2);  // DIAM-1527
$total =  number_format_value_checker($total,2);  // DIAM-1527
}

$txt .= '<tr>
			<td width="25%" style="border-top:1px solid #000;" ></td>
			<td width="25%" style="border-top:1px solid #000;" ></td>
			<td width="30%" style="border-top:1px solid #000;" ></td>
			<td width="10%" style="border-top:1px solid #000;" ></td>';
			if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
			$txt .= '<td width="10%" style="border-top:1px solid #000;" align="right">'.number_format_value_checker(($total / $MAX_CURRENT_POINTS *100),2).' %</td>';
			}else{
			$txt .= '<td width="10%" style="border-top:1px solid #000;" align="right">'.number_format_value_checker(($stu_total / $total *100),2).' %</td>';
			}

			$txt .= '</tr>
	</tbody>
</table>';
												
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			
$file_name = 'Grade Book.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('../school/temp/'.$file_name, 'FD');
else	
	$pdf->Output('../school/temp/'.$file_name, 'FI');
*/
$pdf->Output('../school/temp/'.$file_name, 'FD');
return $file_name;	
