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

$CHK_PK_STUDENT_MASTER		= isset($_REQUEST['CHK_PK_STUDENT_MASTER']) ? ($_REQUEST['CHK_PK_STUDENT_MASTER']) : '';

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
		$this->Cell(55, 8, 'Canvas Send Students', 0, false, 'L', 0, '', 0, false, 'M', 'L');

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

$where = " S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND ARCHIVED = 0  AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT   AND S_COURSE_OFFERING.LMS_ACTIVE = '1' AND  S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_TERM_MASTER.LMS_ACTIVE = '1' AND STUDENT_ID != '' ";

if(!empty($CHK_PK_STUDENT_MASTER))
{
	$where .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER IN (".$CHK_PK_STUDENT_MASTER.") ";
}

$query_canvas_send_student = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER,PK_REPRESENTATIVE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS NAME,S_STUDENT_MASTER.ARCHIVED,PK_STUDENT_STATUS_MASTER, STUDENT_ID, S_TERM_MASTER.BEGIN_DATE,STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE AS PROGRAM, EMAIL, CAMPUS_CODE,S_COURSE_OFFERING.CO_EXTERNAL_ID AS EXTERNAL_ID, S_STUDENT_ACADEMICS.STUDENT_ID AS STUDENT_ID, S_STUDENT_CONTACT.EMAIL AS EMAIL
FROM 
S_STUDENT_MASTER
LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 
, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
, S_STUDENT_COURSE, S_COURSE_OFFERING  
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
WHERE " . $where. " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ORDER BY CAMPUS_CODE ASC, S_STUDENT_MASTER.LAST_NAME ASC, S_STUDENT_MASTER.FIRST_NAME ASC";

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<thead>
				<tr>
					<td width="7%" style="border-bottom:1px solid #000;" ><b>'.CAMPUS.'</b></td>
					<td width="15%" style="border-bottom:1px solid #000;" ><b>'.STUDENT.'</b></td>
					<td width="8%" style="border-bottom:1px solid #000;" ><b>'.STUDENT_ID.'</b></td>
					<td width="15%" style="border-bottom:1px solid #000;" ><b>'.EMAIL.'</b></td>
					<td width="10%" style="border-bottom:1px solid #000;" ><b>'.FIRST_TERM.'</b></td>
					<td width="8%" style="border-bottom:1px solid #000;" ><b>'.PROGRAM.'</b></td>
					<td width="7%" style="border-bottom:1px solid #000;" ><b>'.STATUS.'</b></td>
					<td width="13%" style="border-bottom:1px solid #000;" ><b>'.SENT_ON.'</b></td>
					<td width="7%" style="border-bottom:1px solid #000;" ><b>'.SENT_BY.'</b></td>
					<td width="10%" style="border-bottom:1px solid #000;" ><b>'.MESSAGE.'</b></td>
				</tr>
			</thead>';
			$res_type = $db->Execute($query_canvas_send_student);
			while (!$res_type->EOF) {
				$PK_STUDENT_MASTER 		= $res_type->fields['PK_STUDENT_MASTER'];
				$PK_STUDENT_ENROLLMENT 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
				$res1 = $db->Execute("SELECT SUCCESS, S_STUDENT_MASTER_CANVAS.CREATED_ON, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME,MESSAGE, IF(S_STUDENT_MASTER_CANVAS.SUCCESS = 1,'Success','Failed') as STATUS FROM S_STUDENT_MASTER_CANVAS LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_MASTER_CANVAS.CREATED_BY AND PK_USER_TYPE IN (1,2) LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY S_STUDENT_MASTER_CANVAS.CREATED_ON DESC ");	
				
				$SENT = 'N';
				if($res1->RecordCount() > 0)
					$SENT = 'Y';
					
				// $CAMPUS = "";
				// $res_campus = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME FROM S_CAMPUS, S_STUDENT_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
				// while (!$res_campus->EOF) { 
				// 	if($CAMPUS != '')
				// 		$CAMPUS .= ', ';
						
				// 	$CAMPUS .= $res_campus->fields['OFFICIAL_CAMPUS_NAME'];
				// 	$res_campus->MoveNext();
				// }
				
				if($res_type->fields['BEGIN_DATE'] != '' && $res_type->fields['BEGIN_DATE'] != '0000-00-00')
					$BEGIN_DATE = date("m/d/Y",strtotime($res_type->fields['BEGIN_DATE']));
				else
					$BEGIN_DATE = '';
				
				$txt .= '<tr>
							<td width="7%">'.$res_type->fields['CAMPUS_CODE'].'</td>
							<td width="15%">'.$res_type->fields['NAME'].'</td>
							<td width="8%">'.$res_type->fields['STUDENT_ID'].'</td>
							<td width="15%">'.$res_type->fields['EMAIL'].'</td>
							<td width="10%">'.$BEGIN_DATE.'</td>
							<td width="8%">'.$res_type->fields['PROGRAM'].'</td>
							<td width="7%">'.$res_type->fields['STUDENT_STATUS'].'</td>
							<td width="13%" >'.convert_to_user_date($res1->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()).'</td>
							<td width="7%" >'.$res1->fields['NAME'].'</td>
							<td width="10%" >'.$res1->fields['MESSAGE'].'</td>
						</tr>';
				$res_type->MoveNext();
			}
			
$txt .= '</table>';
		
		////////////////////////////////
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$outputFileName = 'Canvas_Send_Student.pdf';
$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$outputFileName, 'FD');
return $outputFileName;	