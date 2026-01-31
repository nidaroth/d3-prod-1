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

require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		/* Ticket # 1588 */
		if($_GET['id'] != ''){
			$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
			$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
			$this->SetMargins('', 45, '');
			
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
		/* Ticket # 1588 */
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(167);
		$this->Cell(55, 8, "Attendance", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(140, 13, 202, 13, $style);
		
		if($_GET['completed'] == 1)
			$label = "Completed";
		else if($_GET['completed'] == 2)
			$label = "Not Completed";
		else
			$label = "Completed and Not Completed";
			
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(98);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(104, 5, $label, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
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
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);

if($_GET['id'] == '') {
	$_GET['id'] = $_SESSION['PK_STUDENT_MASTER'];
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
}

$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
if($res->RecordCount() == 0){
	header("location:manage_student?t=".$_GET['t']);
	exit;
}

$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

if($DATE_OF_BIRTH != '0000-00-00')
	$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
else
	$DATE_OF_BIRTH = '';
	
$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

/* Ticket #1145   */
$present_att_code_arr = array();
$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
while (!$res_present_att_code->EOF) {
	$present_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_present_att_code->MoveNext();
}

$exc_att_code_arr = array();
$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
while (!$res_exc_att_code->EOF) {
	$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_exc_att_code->MoveNext();
}
/* Ticket #1145   */

$PK_STUDENT_ENROLLMENTS = explode(",",$_GET['eid']);
foreach($PK_STUDENT_ENROLLMENTS as $PK_STUDENT_ENROLLMENT) {
	$pdf->AddPage();
	
	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 

	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';

	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="80%">
				<tr>
					<td width="100%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>'.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].' '.$res->fields['LAST_NAME'].'</b></td>
				</tr>
				<tr>
					<td width="100%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">Program: '.$res_enroll->fields['CODE'].' - '.$res_enroll->fields['DESCRIPTION'].'</td>
				</tr>
				<tr>
					<td width="33%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">ID: '.$res->fields['STUDENT_ID'].'<br />DOB: '.$DATE_OF_BIRTH.'<br />Phone: '.$res_address->fields['CELL_PHONE'].'</td>

					<td width="33%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">Status: '.$res_enroll->fields['STUDENT_STATUS'].'<br />First Term: '.$res_enroll->fields['TERM_MASTER'].'<br />Exp. Grad: '.$EXPECTED_GRAD_DATE.'</td>
				
					<td width="34%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;">'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'<br />'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'<br />'.$res_address->fields['COUNTRY'].'</td>
				</tr>
			</table>
			<br /><br />
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<thead>
					<tr>
						<td width="22%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							<b>Course</b>
						</td>
						<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							<b>Type</b>
						</td>';
						
						if($_GET['detail_view'] == 1){
							$txt .= '<td width="13%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										<b>Class Date</b>
									</td>
									<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										<b>Start Time</b>
									</td>
									<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										<b>End Time</b>
									</td>
									<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										<b>Hours</b>
									</td>
									<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										<b>Complete</b>
									</td>
									<td width="5%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										<b>Code</b>
									</td>';
						}
						
				$txt .= '<td width="15%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">
							<b>Attended Hours</b>
						</td>
					</tr>
				</thead>
				<tbody>';
		
			$res_course_schedule = $db->Execute($_SESSION['query']);

			$TOTAL_HOURS 		= 0;
			$ATTENDANCE_HOURS 	= 0;
			while (!$res_course_schedule->EOF) {
				$exc_att_flag = 0;
				foreach($exc_att_code_arr as $exc_att_code) {
					if($exc_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}
				
				$present_flag = 0;
				foreach($present_att_code_arr as $present_att_code) {
					if($present_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
						$present_flag = 1;
						break;
					}
				}
				
				if($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' && $exc_att_flag == 0)
					$TOTAL_HOURS += $res_course_schedule->fields['HOURS'];
					
				if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2) {
					if($present_flag == 1) {
						$ATTENDANCE_HOURS += $res_course_schedule->fields['ATTENDANCE_HOURS'];
					}
				}
		
				if($_GET['detail_view'] == 1) 
					$COURSE = $res_course_schedule->fields['COURSE_CODE']; 
				else 
					$COURSE = $res_course_schedule->fields['COURSE_CODE'].' ('. $res_course_schedule->fields['SESSION'].' - '. $res_course_schedule->fields['SESSION_NO'].')';
					
				if($_GET['detail_view'] == 1) 
					$TYPE = $res_course_schedule->fields['SCHEDULE_TYPE']; 
				else 
					$TYPE = "Summary";
				
					
					if($_GET['show_inactive'] == 1 || ($_GET['show_inactive'] == 0 && $res_course_schedule->fields['ATTENDANCE_CODE'] != 'I') ){ //DIAM-983
	
				$txt .= '<tr nobr="true" >
						<td width="22%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">'.$COURSE.'</td>
						<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
							'.$TYPE.'
						</td>';
						if($_GET['detail_view'] == 1){
							if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 || $res_course_schedule->fields['ATTENDANCE_CODE'] == 'I')
								$ATTENDANCE_CODE = $res_course_schedule->fields['ATTENDANCE_CODE'];
							else
								$ATTENDANCE_CODE = '';
				
							$txt .= '<td width="13%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
								'.$res_course_schedule->fields['SCHEDULE_DATE'].'
							</td>
							<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
								'.$res_course_schedule->fields['START_TIME'].'
							</td>
							<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
								'.$res_course_schedule->fields['END_TIME'].'
							</td>
							<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
								'.number_format_value_checker($res_course_schedule->fields['HOURS'],2).'
							</td>
							<td width="8%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
								'.$res_course_schedule->fields['COMPLETED'].'
							</td>
							<td width="5%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
								'.$ATTENDANCE_CODE.'
							</td>';
						}
						if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 || $res_course_schedule->fields['ATTENDANCE_CODE'] == 'I') 
							$ATTENDANCE_HOURS1 = $res_course_schedule->fields['ATTENDANCE_HOURS'];
						else
							$ATTENDANCE_HOURS1 = '0';
							
					$txt .= '<td width="15%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">
							'.number_format_value_checker($ATTENDANCE_HOURS1,2).'
						</td>
					</tr>';

					} //DIAM-983
					
				$res_course_schedule->MoveNext();
			}
			
			$txt .= '<tr>
						<td width="34%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;"><b>Total</b></td>';
						if($_GET['detail_view'] == 1){
							$txt .= '<td width="29%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;"><b></b></td>
								<td width="9%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;" align="right" >
								<b>'.number_format_value_checker($TOTAL_HOURS,2).'</b>
							</td>
							<td width="13%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;"> </td>';
						}
						$txt .= '<td width="15%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;;border-right:0.5px solid #000;"  align="right" >
							<b>'.number_format_value_checker($ATTENDANCE_HOURS,2).'</b>
						</td>
					</tr>
				</tbody>
			</table>';
			
		//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Attendance.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
