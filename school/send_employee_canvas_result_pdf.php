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
require_once("../language/employee.php");
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

$CHK_PK_EMPLOYEE_MASTER		= isset($_REQUEST['CHK_PK_EMPLOYEE_MASTER']) ? ($_REQUEST['CHK_PK_EMPLOYEE_MASTER']) : '';

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
		$this->Cell(55, 8, 'Canvas Send Instructor', 0, false, 'L', 0, '', 0, false, 'M', 'L');

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

$where = "";
if(!empty($CHK_PK_EMPLOYEE_MASTER))
{
	$where .= " AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER IN (".$CHK_PK_EMPLOYEE_MASTER.") ";
}

$sQuery_Final = "SELECT CAMPUS_CODE
						,S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER
						,S_EMPLOYEE_MASTER.EMAIL AS EMAIL
						,CONCAT(S_EMPLOYEE_MASTER.LAST_NAME, ', ', S_EMPLOYEE_MASTER.FIRST_NAME) AS INSTRUCTOR_NAME 
					FROM 
						S_EMPLOYEE_MASTER 
						LEFT JOIN S_EMPLOYEE_MASTER_CANVAS ON S_EMPLOYEE_MASTER_CANVAS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
						-- LEFT JOIN S_EMPLOYEE_COURSE_CANVAS ON S_EMPLOYEE_COURSE_CANVAS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER_CANVAS.PK_EMPLOYEE_MASTER 
						-- LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_EMPLOYEE_COURSE_CANVAS.PK_COURSE_OFFERING 
						LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER
						LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS
					WHERE 
						S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
						AND S_EMPLOYEE_MASTER.IS_FACULTY = 1
						$where
					GROUP BY 
						S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER
					ORDER BY 
						CAMPUS_CODE ASC, 
						S_EMPLOYEE_MASTER.LAST_NAME ASC, 
						S_EMPLOYEE_MASTER.FIRST_NAME ASC, 
						S_EMPLOYEE_MASTER_CANVAS.CREATED_ON ASC ";

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<thead>
				<tr>
					<td width="8%" style="border-bottom:1px solid #000;" ><b>'.CAMPUS.'</b></td>
					<td width="15%" style="border-bottom:1px solid #000;" ><b>'.INSTRUCTOR.'</b></td>	
					<td width="23%" style="border-bottom:1px solid #000;" ><b>'.EMAIL.'</b></td>
					<td width="14%" style="border-bottom:1px solid #000;" ><b>'.SENT_ON.'</b></td>
					<td width="15%" style="border-bottom:1px solid #000;" ><b>'.SENT_BY.'</b></td>
					<td width="4%" style="border-bottom:1px solid #000;" ><b>'.SENT.'</b></td>
					<td width="21%" style="border-bottom:1px solid #000;" ><b>'.MESSAGE.'</b></td>
				</tr>
			</thead>
			<tbody>';
			$res_type = $db->Execute($sQuery_Final);
			while (!$res_type->EOF) {
				$PK_EMPLOYEE_MASTER = $res_type->fields['PK_EMPLOYEE_MASTER'];
				$res1 = $db->Execute("SELECT SUCCESS, S_EMPLOYEE_MASTER_CANVAS.CREATED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,MESSAGE FROM S_EMPLOYEE_MASTER_CANVAS LEFT JOIN Z_USER ON Z_USER.PK_USER = S_EMPLOYEE_MASTER_CANVAS.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE  S_EMPLOYEE_MASTER_CANVAS.PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ORDER BY S_EMPLOYEE_MASTER_CANVAS.CREATED_ON DESC");	
				
				$SENT = 'N';
				if($res1->RecordCount() > 0)
				{
					$SENT = 'Y';
				}

				$res_campus = $db->Execute("SELECT GROUP_CONCAT(CAMPUS_CODE) AS CAMPUS_CODE FROM S_CAMPUS, S_EMPLOYEE_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS  AND PK_EMPLOYEE_MASTER = '".$PK_EMPLOYEE_MASTER."' ORDER BY CAMPUS_CODE ASC ");

				$CAMPUS_CODE = $res_campus->fields['CAMPUS_CODE'];
				
				$txt .= '<tr>
							<td width="8%">'.$CAMPUS_CODE.'</td>
							<td width="15%">'.$res_type->fields['INSTRUCTOR_NAME'].'</td>
							<td width="23%">'.$res_type->fields['EMAIL'].'</td>				
							<td width="14%">'.convert_to_user_date($res1->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()).'</td>
							<td width="15%">'.$res1->fields['NAME'].'</td>
							<td width="4%">'.$SENT.'</td>
							<td width="21%">'.$res1->fields['MESSAGE'].'</td>
						</tr>';
				$res_type->MoveNext();
			}
			
$txt .= '</tbody></table>';
		
		////////////////////////////////
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$outputFileName = 'Canvas_Send_Instructor.pdf';
$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$outputFileName, 'FD');
return $outputFileName;	