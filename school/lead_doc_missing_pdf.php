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
	
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		$res = $db->Execute("SELECT LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($res->fields['LOGO'] != '') {
			$ext = explode(".",$res->fields['LOGO']);
			$this->Image($res->fields['LOGO'], 8, 3, 0, 20, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(8);
		$this->SetX(45);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetX(123);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, "Documents Not Received", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(102, 5, "With Notes".$str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('times', 'BI', 8);
		$this->SetY(28);
		$this->SetX(8);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(39, 4, 'Student', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(28);
		$this->SetX(47);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19, 4, 'First Term', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(28);
		$this->SetX(66);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(58, 4, 'Document Description', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(28);
		$this->SetX(124);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(39, 4, 'Admin Rep', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(28);
		$this->SetX(163);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(19, 4, 'Requested', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(28);
		$this->SetX(182);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(20, 4, 'Date In', 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(100, 13, 202, 13, $style);
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(8, 25, 202, 25, $style);
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(8, 31, 202, 31, $style);
    }
    public function Footer() {
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, date('l, F d, Y'), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('times', '', 7, '', true);
$pdf->AddPage();

$cond = "";
if($_GET['tc'] == 1)
	$cond .= " AND COMPLETED = 1";
else if($_GET['tc'] == 2)
	$cond .= " AND COMPLETED = 0";
	
if($_GET['e'] != '')
	$cond .= " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_GET[e]' ";
	
$field = "";
if($_GET['dt'] == 'TD')
	$field = "TASK_DATE";
else if($_GET['dt'] == 'FD')
	$field = "FOLLOWUP_DATE";
	
if($_GET['st'] != '' && $_GET['et'] != '') {
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND $field BETWEEN '$ST' AND '$ET' ";
} else if($_GET['st'] != ''){
	$ST = date("Y-m-d",strtotime($_GET['st']));
	$cond .= " AND $field >= '$ST' ";
} else if($_GET['et'] != ''){
	$ET = date("Y-m-d",strtotime($_GET['et']));
	$cond .= " AND $field <= '$ET' ";
}
	
$res = $db->Execute("select PK_STUDENT_DOCUMENTS, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME,' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STU_NAME ,DOCUMENT_TYPE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME,' ',S_EMPLOYEE_MASTER.MIDDLE_NAME) AS EMP_NAME, IF(REQUESTED_DATE = '0000-00-00', '',  DATE_FORMAT(REQUESTED_DATE,'%m/%d/%Y')) AS REQUESTED_DATE, S_STUDENT_DOCUMENTS.NOTES FROM 

S_STUDENT_MASTER LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ,
S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, 
S_STUDENT_DOCUMENTS 
LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_DOCUMENTS.PK_EMPLOYEE_MASTER 
WHERE 
S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER  = S_STUDENT_DOCUMENTS.PK_STUDENT_MASTER AND DOCUMENT_PATH = '' GROUP BY PK_STUDENT_DOCUMENTS 
ORDER BY CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) ASC ");

$EMP_NAME = '';
if($res->RecordCount() > 0)
	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
	
while (!$res->EOF) { 
	$txt .= '<tr>
		<td width="20%">'.$res->fields['STU_NAME'].'</td>
		<td width="10%"> </td>
		<td width="30%">'.$res->fields['DOCUMENT_TYPE'].'</td>
		<td width="20%">'.$res->fields['EMP_NAME'].'</td>
		<td width="10%">'.$res->fields['REQUESTED_DATE'].'</td>
		<td width="10%">'.$res->fields['TASK_STATUS'].'</td>
	</tr>';
	
	if($res->fields['NOTES'] != ''){
		$txt .= '<tr>
			<td width="20%"></td>
			<td width="10%"> </td>
			<td width="70%">'.$res->fields['NOTES'].'</td>
		</tr>';
	}
	
	$res->MoveNext();
}

if($res->RecordCount() > 0)			
	$txt .= '</table>';


	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'lead document not received'.'.pdf';
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
	
//$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	