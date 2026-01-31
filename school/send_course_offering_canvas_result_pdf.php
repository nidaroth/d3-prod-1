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

require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ENABLE_CANVAS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_CANVAS'] == 0) {
	header("location:../index");
	exit;
}

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".",$res->fields['PDF_LOGO']);
			$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(8);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(75, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(218);
		$this->Cell(55, 8, 'Course Offering Result', 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(200, 13, 290, 13, $style);
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
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9, '', true);
$pdf->AddPage();

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<thead>
				<tr>
					<td width="8%" style="border-bottom:1px solid #000;" ><b>'.TERM.'</b></td>
					<td width="15%" style="border-bottom:1px solid #000;" ><b>'.COURSE_CODE.'</b></td>
					<td width="11%" style="border-bottom:1px solid #000;" ><b>'.SESSION.'</b></td>
					<td width="12%" style="border-bottom:1px solid #000;" ><b>'.INSTRUCTOR.'</b></td>
					
					<td width="10%" style="border-bottom:1px solid #000;" ><b>'.CAMPUS.'</b></td>
					<td width="5%" style="border-bottom:1px solid #000;" ><b>'.SENT.'</b></td>
					<td width="9%" style="border-bottom:1px solid #000;" ><b>'.SENT_ON.'</b></td>
					<td width="10%" style="border-bottom:1px solid #000;" ><b>'.SENT_BY.'</b></td>
					<td width="20%" style="border-bottom:1px solid #000;" ><b>'.MESSAGE.'</b></td>
				</tr>
			</thead>';
			$res_type = $db->Execute($_SESSION['query']);
			while (!$res_type->EOF) {
				$PK_COURSE_OFFERING = $res_type->fields['PK_COURSE_OFFERING'];
				$res1 = $db->Execute("SELECT SUCCESS, S_COURSE_OFFERING_CANVAS.CREATED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,MESSAGE, IF(S_COURSE_OFFERING_CANVAS.SUCCESS = 1,'Success','Failed') as STATUS FROM S_COURSE_OFFERING_CANVAS LEFT JOIN Z_USER ON Z_USER.PK_USER = S_COURSE_OFFERING_CANVAS.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE  PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY SUCCESS DESC ");
				
				$SENT = 'N';
				if($res1->RecordCount() > 0)
					$SENT = 'Y';
				
				$txt .= '<tr>
							<td width="8%">'.$res_type->fields['TERM_BEGIN_DATE'].'</td>
							<td width="15%">'.$res_type->fields['COURSE_CODE'].'</td>
							<td width="11%">'.$res_type->fields['SESSION'].'</td>
							<td width="12%">'.$res_type->fields['INSTRUCTOR_NAME'].'</td>
							
							<td width="10%">'.$res_type->fields['OFFICIAL_CAMPUS_NAME'].'</td>
							<td width="5%">'.$SENT.'</td>
							<td width="9%" >'.convert_to_user_date($res1->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()).'</td>
							<td width="10%" >'.$res1->fields['NAME'].'</td>
							<td width="20%" >'.$res1->fields['MESSAGE'].'</td>
						</tr>';
				$res_type->MoveNext();
			}
			
$txt .= '</table>';
		
		////////////////////////////////
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Send Course Offering Result.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	