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

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$TIMEZONE = $res->fields['TIMEZONE'];

require_once("pdf_custom_header.php");
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		if($_GET['id'] != ''){
				if($this->PageNo() == 1) {
					$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
					$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
					$this->SetMargins('', 45, '');
				} else {
					$this->SetFont('helvetica', 'I', 15);
					$this->SetY(8);
					$this->SetX(10);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
					$this->SetMargins('', 25, '');
				}
			
		} else {
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			if($res->fields['PDF_LOGO'] != '') {
				$ext = explode(".",$res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}
			
			$this->SetFont('helvetica', '', 15);
			$this->SetY(6);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', '', 8);
			$this->SetY(13);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(17);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(21);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
		
		$this->SetFont('helvetica', 'I', 16);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(135);
		$this->Cell(55, 8, "Expected Grad Date Review", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(130, 13, 205, 13, $style);

    }
    public function Footer() {
		global $db, $TIMEZONE; 
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE ,date_default_timezone_get());
		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(100);
		$this->Cell(30, 10, "REGR10101", 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);

$PK_STUDENT_ENROLLMENTS = explode(",",$_GET['eid']);

foreach($PK_STUDENT_ENROLLMENTS as $PK_STUDENT_ENROLLMENT) {
	
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,PROGRAM_TRANSCRIPT_CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
	$PK_STUDENT_MASTER = $res_enroll->fields['PK_STUDENT_MASTER'];
	
	$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 

	$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

	if($DATE_OF_BIRTH != '0000-00-00')
		$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
	else
		$DATE_OF_BIRTH = '';
		
	$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';

	$pdf->startPageGroup();
	$pdf->AddPage();
	$pdf->STUD_NAME = $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME']." ".$res->fields['MIDDLE_NAME'];
	
	$txt = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td width="81%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>'.$res->fields['LAST_NAME'].', '.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].'</b></td>
				</tr>
				<tr>
					<td width="81%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">Program: '.$res_enroll->fields['PROGRAM_TRANSCRIPT_CODE'].' - '.$res_enroll->fields['DESCRIPTION'].'</td>
				</tr>
				<tr>
					<td width="27%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">ID: '.$res->fields['STUDENT_ID'].'<br />DOB: '.$DATE_OF_BIRTH.'<br />Phone: '.$res_address->fields['CELL_PHONE'].'</td>

					<td width="27%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">Status: '.$res_enroll->fields['STUDENT_STATUS'].'<br />First Term: '.$res_enroll->fields['TERM_MASTER'].'<br />Exp. Grad: '.$EXPECTED_GRAD_DATE.'</td>
				
					<td width="27%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;">'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'<br />'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'<br />'.$res_address->fields['COUNTRY'].'</td>
				</tr>
			</table>
			<br /><br />
			
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<thead>
					<tr>
						<td width="5%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Wk</b>
						</td>
						<td width="25%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Week Days</b>
						</td>
						<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Sun</b>
						</td>
						<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Mon</b>
						</td>
						<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Tue</b>
						</td>
						<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Wed</b>
						</td>
						<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Thu</b>
						</td>
						<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">
							<b>Fri</b>
						</td>
						<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" >
							<b>Sat</b>
						</td>
					</tr>
				</thead>
				<tbody>';
		
			$res = $db->Execute("call REGR10101('Graduation100', ".$PK_STUDENT_ENROLLMENT.", 'REPORT')");
			while (!$res->EOF) {

				$txt .= '<tr nobr="true" >
							<td width="5%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['WK'].'
							</td>
							<td width="25%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['WK_DATES'].'
							</td>
							<td width="10%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['SUN'].'
							</td>
							<td width="10%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['MON'].'
							</td>
							<td width="10%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['TUE'].'
							</td>
							<td width="10%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['WED'].'
							</td>
							<td width="10%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['THU'].'
							</td>
							<td width="10%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;">
								'.$res->fields['FRI'].'
							</td>
							<td width="10%" align="right" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;" >
								'.$res->fields['SAT'].'
							</td>
						</tr>';
				$res->MoveNext();
			}
			
			$txt .= '</table>';
			
		//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Expected Graduation Date Review.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	