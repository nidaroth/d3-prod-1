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
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".",$res->fields['PDF_LOGO']);
			$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 20, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(8);
		$this->SetX(45);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(165);
		$sts = "Daily Roster";

		$this->Cell(55, 8, $sts, 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(130, 13, 202, 13, $style);
		
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
$pdf->SetFont('helvetica', '', 9, '', true);
$pdf->AddPage();

$res_cs = $db->Execute("select DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y') AS TERM_DATE,COURSE_CODE,SESSION, SESSION_NO, S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING, DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE,'%m/%d/%Y') AS CLASS_START, DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.END_DATE, '%m/%d/%Y') AS CLASS_END, S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE 
from S_COURSE_OFFERING_SCHEDULE_DETAIL 
LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING
LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER
LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING
WHERE S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$_GET[id]' ");
$PK_COURSE_OFFERING = $res_cs->fields['PK_COURSE_OFFERING'];

if($res_cs->fields['CLASS_END'] == '00/00/0000') {
	$PK_COURSE_OFFERING_SCHEDULE = $res_cs->fields['PK_COURSE_OFFERING_SCHEDULE'];
	$res_cs1 = $db->Execute("SELECT  DATE_FORMAT(MAX(SCHEDULE_DATE), '%m/%d/%Y') AS CLASS_END FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING_SCHEDULE = '$PK_COURSE_OFFERING_SCHEDULE' ");
	
	$CLASS_END = $res_cs1->fields['CLASS_END'];
} else
	$CLASS_END = $res_cs->fields['CLASS_END'];

$res_csd = $db->Execute("select SCHEDULE_DATE,CONCAT(DATE_FORMAT(START_TIME,'%h:%i %p'),' - ',DATE_FORMAT(END_TIME,'%h:%i %p')) AS TIME from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$_GET[id]' ");

$res_sch = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
$res_sch_comp = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(COMPLETED = 1,' - Completed','') AS COMPLETED from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND COMPLETED = 1");

$txt = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td width="45%" >
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td >Term: '.$res_cs->fields['TERM_DATE'].'</td>
						</tr>
						<tr>
							<td >Course Offering: '.$res_cs->fields['COURSE_CODE'].' ('.$res_cs->fields['SESSION'].' - '.$res_cs->fields['SESSION_NO'].')</td>
						</tr>
					</table>
				</td>
				<td width="33%" >
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td >Completed Class Meetings: '.$res_sch_comp->RecordCount().'</td>
						</tr>
						<tr>
							<td >Scheduled Class Meetings: '.$res_sch->RecordCount().'</td>
						</tr>
					</table>
				</td>
				<td width="22%" >
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td align="right">First Class Date: '.$res_cs->fields['CLASS_START'].'</td>
						</tr>
						<tr>
							<td align="right">Last Class Date: '.$CLASS_END.'</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br /><br />
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td width="40%" >
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td >Class Date: '.date('l, M d, Y',strtotime($res_csd->fields['SCHEDULE_DATE'])).'</td>
						</tr>
						<tr>
							<td >Class Time: '.$res_csd->fields['TIME'].'</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br /><br />
		<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<tr>
				<td style="border-bottom:2px solid #000;" >Student</td>
			</tr>';
			$res_cs = $db->Execute($_SESSION['QUERY']);
			while (!$res_cs->EOF) { 
				$txt .=	'<tr>
							<td style="border-bottom:0.5px solid #000">'.$res_cs->fields['STUDENT_ID'].' - '.$res_cs->fields['NAME'].'</td>
						</tr>';
				$res_cs->MoveNext();
			}
$txt .=	'</table>';
		
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Daily Roster.pdf';
if($browser == 'Safari')
	$pdf->Output('../school/temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
	
//$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	