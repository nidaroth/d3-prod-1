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
		$this->SetX(230);
		$sts = "Attendance Review";

		$this->Cell(55, 8, $sts, 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(200, 13, 290, 13, $style);
		
		$res_cs = $db->Execute("select PK_COURSE_OFFERING,COURSE_CODE,SESSION, SESSION_NO,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE from 

		S_COURSE_OFFERING 
		LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
		LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
		,S_TERM_MASTER
		
		WHERE 
		S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER  AND PK_COURSE_OFFERING = '$_GET[co_id]' ORDER BY COURSE_CODE ASC ");
		
		$str = $res_cs->fields['COURSE_CODE'].' ('.$res_cs->fields['SESSION'].' - '.$res_cs->fields['SESSION_NO'].') ';
		$this->SetFont('helvetica', 'I', 12);
		$this->SetY(16);
		$this->SetX(185);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(104, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
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

$start = 0;
$res_co1 = $db->Execute("SELECT MIN(SCHEDULE_DATE) AS SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[co_id]' ");
$START_DATE = date("Y-m-d", strtotime('monday this week', strtotime($res_co1->fields['SCHEDULE_DATE'])));

$txt = '';
do {
	$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
		<thead>
			<tr>
				<td rowspan="2" width="10%" style="border-left:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;text-align:center;">
					<b style="line-height:10px" >Student</b>
				</td>
				<td colspan="7" width="30.03%" style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;text-align:center;">
					<b>'.date("m/d/Y", strtotime($START_DATE)).' - '.date("m/d/Y", strtotime($START_DATE.' + 6 days')).'</b>
				</td>
				<td colspan="7" width="30.03%" style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;text-align:center;">
					<b>'.date("m/d/Y", strtotime($START_DATE.' + 7 days')).' - '.date("m/d/Y", strtotime($START_DATE.' + 13 days')).'</b>
				</td>
				<td colspan="7" width="30.03%" style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;text-align:center;">
					<b>'.date("m/d/Y", strtotime($START_DATE.' + 14 days')).' - '.date("m/d/Y", strtotime($START_DATE.' + 20 days')).'</b>
				</td>
			</tr>
			<tr>';
			for($i = 0 ; $i <= 2 ; $i++) {
				$txt .= '<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">
							<b>M</b>
						</td>
						<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">
							<b>T</b>
						</td>
						<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">
							<b>W</b>
						</td>
						<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">
							<b>T</b>
						</td>
						<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">
							<b>F</b>
						</td>
						<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">
							<b>S</b>
						</td>
						<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;text-align:center;">
							<b>S</b>
						</td>';
			}
	$txt .= '</tr>
		</thead>
		<tbody>';
		$res_cs = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME,' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_COURSE.PK_STUDENT_COURSE from S_STUDENT_COURSE, S_STUDENT_MASTER WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = '$_GET[co_id]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC");
		while (!$res_cs->EOF) { 
			$PK_STUDENT_ENROLLMENT 	= $res_cs->fields['PK_STUDENT_ENROLLMENT']; 
			$PK_STUDENT_COURSE 		= $res_cs->fields['PK_STUDENT_COURSE'];
			
			$txt .= '<tr>
						<td width="10%" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >'.$res_cs->fields['NAME'].'</td>';

			for($i = 0 ; $i <= 20 ; $i++){ 
				$DATE = date("Y-m-d", strtotime($START_DATE.' + '.$i.' days')); 
				
				$res = $db->Execute("select ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.COMPLETED, M_ATTENDANCE_CODE.CODE  from S_STUDENT_ATTENDANCE LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND SCHEDULE_DATE = '$DATE' AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE "); 
				
				$ATTENDANCE_HOURS = '';
				if($res->RecordCount() > 0)
					$ATTENDANCE_HOURS = number_format_value_checker($res->fields['ATTENDANCE_HOURS'],2);
					
				$txt .= '<td width="4.29%" style="border-right:1px solid #000;border-bottom:1px solid #000;" >'.$ATTENDANCE_HOURS.'</td>';
			}
			$txt .= '</tr>';
			
			$res_cs->MoveNext();
		}
		
	$txt .= '</tbody>
	</table><br /><br />';	
	
	$START_DATE = date("Y-m-d", strtotime($START_DATE.' +21 days'));

	$res_co1 = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[co_id]' AND SCHEDULE_DATE >= '$START_DATE'; ");
	
	//echo "SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[co_id]' AND SCHEDULE_DATE >= '$START_DATE'; <br /><br />";
	
	$start++;

} while ($res_co1->RecordCount() > 0);
//exit;
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Attendance Review.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('../school/temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('../school/temp/'.$file_name, 'FD');
return $file_name;	