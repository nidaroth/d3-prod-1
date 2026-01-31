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
require_once("check_access.php");

if(check_access('MANAGEMENT_ADMISSION') == 0 && check_access('REPORT_CUSTOM_REPORT') == 0){
	header("location:../index");
	exit;
}
	
require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		/* Ticket # 1588 */
		if($_GET['sid'] != ''){
			$CONTENT = pdf_custom_header($_GET['sid'], $_GET['eid'], 1);
			$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
			$this->SetMargins('', 45, '');
			
		} else {
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			if($res->fields['PDF_LOGO'] != '') {
				$ext = explode(".",$res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}
			
			$this->SetFont('helvetica', '', 15);
			$this->SetY(8);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
		/* Ticket # 1588 */
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetX(230);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, "Documents Report", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(185);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(102, 5, "With Notes".$str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(270);
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

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 25, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9, '', true);
$pdf->AddPage();

$res = $db->Execute($_SESSION['document_report_query']);

$EMP_NAME = '';
if($res->RecordCount() > 0) {
	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<thead>
					<tr>
						<td width="15%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Student</b></td>
						<td width="10%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Email</b></td>
						<td width="8%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Campus</b></td>
						<td width="8%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>First Term</b></td>
						<td width="10%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Department</b></td>
						<td width="12%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Document</b></td>
						<td width="13%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Employee</b></td>
						<td width="8%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Requested</b></td>
						<td width="8%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Follow Up</b></td>
						<td width="8%" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" ><b>Received</b></td>						
					</tr>
				</thead>
				<tbody>';
}	
while (!$res->EOF) { 
	$PK_STUDENT_DOCUMENTS = $res->fields['PK_STUDENT_DOCUMENTS']; 
	$DEPARTMENT_NAME		= '';
	$res_dep = $db->Execute("SELECT DEPARTMENT FROM S_STUDENT_DOCUMENTS_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_DOCUMENTS_DEPARTMENT.PK_DEPARTMENT AND PK_STUDENT_DOCUMENTS = '$PK_STUDENT_DOCUMENTS' ORDER BY DEPARTMENT ASC "); 
	while (!$res_dep->EOF) { 
		if($DEPARTMENT_NAME != '')
			$DEPARTMENT_NAME .= ', ';
			
		$DEPARTMENT_NAME .= $res_dep->fields['DEPARTMENT'];
		$res_dep->MoveNext();
	}
	/*DIAM-1439  */
	$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
	$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE, CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");	
	/*DIAM-1439 */

	/*DIAM-1439 */
	$txt .= '<tr>
		<td width="15%"><a href="'.$http_path.'school/student?id='.$res->fields['PK_STUDENT_MASTER'].'&eid='.$res->fields['PK_STUDENT_ENROLLMENT'].'&tab=documentsTab" target="_blank" >'.$res->fields['STU_NAME'].'</a></td>
		<td width="10%"><a href="mailto:'.$res->fields['EMAIL'].'" >'.$res->fields['EMAIL'].'</a></td>
		<td width="8%">'.$res_camp_1->fields['CAMPUS_CODE'].'</td>
		<td width="8%">'.$res->fields['BEGIN_DATE_1'].'</td>
		<td width="10%">'.$DEPARTMENT_NAME.'</td>
		<td width="12%">'.$res->fields['DOCUMENT_TYPE'].'</td>
		<td width="13%">'.$res->fields['EMP_NAME'].'</td>
		<td width="8%">'.$res->fields['REQUESTED_DATE'].'</td>
		<td width="8%">'.$res->fields['FOLLOWUP_DATE'].'</td>
		<td width="8%">'.$res->fields['DATE_RECEIVED'].'</td>
	</tr>';
	
	if($res->fields['NOTES'] != ''){
		$txt .= '<tr>
			<td width="35%"></td>
			<td width="65%"><i>Notes:</i> '.$res->fields['NOTES'].'</td>
		</tr>';
	}
	
	$res->MoveNext();
}

if($res->RecordCount() > 0)			
	$txt .= '</tbody>
			</table>';


	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Documents Report.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');*/
	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
