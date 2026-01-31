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

		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(9);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(224);
		$this->Cell(55, 8, "Tuition Batch Review", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(180, 13, 290, 13, $style);
		
		$res = $db->Execute("SELECT S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER, IF(TRANS_DATE = '0000-00-00', '', DATE_FORMAT(TRANS_DATE,'%m/%d/%Y') ) as TRANS_DATE, IF(POSTED_DATE = '0000-00-00', '', DATE_FORMAT(POSTED_DATE,'%m/%d/%Y') ) as POSTED_DATE, BATCH_NO, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER ,BATCH_STATUS, IF(TYPE = 1,'Program', IF(TYPE = 2,'Course',IF(TYPE = 7,'Estimated Fees By Program', IF(TYPE = 9,'Estimated Fees By Student','')))) AS TYPE, S_TUITION_BATCH_MASTER.PK_BATCH_STATUS, TUITION_BATCH_PK_CAMPUS, SUM(AMOUNT) as DEBIT FROM S_TUITION_BATCH_MASTER LEFT JOIN S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_TUITION_BATCH_MASTER.PK_BATCH_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER WHERE S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$this->SetFont('helvetica', 'I', 9);
		$this->SetY(16);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(235);
		$this->Cell(55, 7, "Batch # ".$res->fields['BATCH_NO'], 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$TUITION_BATCH_PK_CAMPUS = $res->fields['TUITION_BATCH_PK_CAMPUS'];
		
		$campus_name = "";
		$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($TUITION_BATCH_PK_CAMPUS) order by CAMPUS_CODE ASC");
		while (!$res_campus->EOF) {
			if($campus_name != '')
				$campus_name .= ', ';
			$campus_name .= $res_campus->fields['CAMPUS_CODE'];

			$res_campus->MoveNext();
		}
		
		$this->SetY(19);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Total: $ ".number_format_value_checker($res->fields['DEBIT'], 2), 0, 'R', 0, 0, '', '', true);
		
		$this->SetY(24);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Date: ".$res->fields['TRANS_DATE'], 0, 'R', 0, 0, '', '', true);
		
		if($res->fields['PK_BATCH_STATUS'] == 2) {
			$this->SetY(28);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Posted Date: ".$res->fields['POSTED_DATE'], 0, 'R', 0, 0, '', '', true);
			
			$this->SetY(33);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
			
			$this->SetY(38);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Type: ".$res->fields['TYPE'], 0, 'R', 0, 0, '', '', true);
		} else {
			$this->SetY(28);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
			
			$this->SetY(34);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Type: ".$res->fields['TYPE'], 0, 'R', 0, 0, '', '', true);
		}
		
    }
    public function Footer() {
		global $db;
		/* Ticket #1898 */
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
		/* Ticket #1898 */
    }
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 42, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);
$pdf->AddPage();

$res = $db->Execute("SELECT S_TUITION_BATCH_MASTER.*, IF(TYPE = 1,'Program', IF(TYPE = 2,'Course',IF(TYPE = 7,'Estimated Other Fee',''))) AS TYPE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1 FROM S_TUITION_BATCH_MASTER LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER WHERE PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
if($res->RecordCount() == 0){
	header("location:manage_tuition_batch");
	exit;
}
$TYPE 				= $res->fields['TYPE'];
$TRANS_DATE 		= $res->fields['TRANS_DATE'];
if($TRANS_DATE == '0000-00-00')
	$TRANS_DATE = '';
else
	$TRANS_DATE = date("m/d/Y",strtotime($TRANS_DATE));

$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
			<tr>
				<td width="12%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Student</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Student ID</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Ledger Code</i></b>
				</td>
				<td width="13%" style="border-bottom:1px solid #000;">
					<b><i>Ledger Code<br />Description</i></b>
				</td>
				<td width="7%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Trans Date</i></b>
				</td>
				<td width="9%" style="border-bottom:1px solid #000;" align="right">
					<br /><br /><b><i>Debit</i></b>
				</td>
				<td width="10%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Batch Detail</i></b>
				</td>
				<td width="4%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>AY</i></b>
				</td>
				<td width="4%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>AP</i></b>
				</td>
				<td width="13%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Enrollment</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Term Block</i></b>
				</td>
				<td width="5%" style="border-bottom:1px solid #000;">
					<b><i>Prior  year</i></b>
				</td>
			</tr>';
			
		$res_disb_count = $db->Execute("select PK_STUDENT_MASTER from 
		S_TUITION_BATCH_DETAIL 
		WHERE 
		PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_STUDENT_MASTER  ");
		
		if($TYPE == 2)
			$order_by = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, COURSE_BATCH_DESC ASC, CODE ASC ";
		else
			$order_by = " CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, CODE ASC ";
	
		$total 	= 0;
		$res_disb1 = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS STUD_NAME, STUDENT_ID, M_AR_LEDGER_CODE.CODE, BATCH_DETAIL_DESCRIPTION, AMOUNT, IF(TRANSACTION_DATE = '0000-00-00','',DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE, TUITION_BATCH_DETAIL_AY, TUITION_BATCH_DETAIL_AP, IF(TUITION_BATCH_DETAIL_PRIOR_YEAR = 1, 'Yes', 'No') as TUITION_BATCH_DETAIL_PRIOR_YEAR, CONCAT(IF(S_TERM_BLOCK.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_BLOCK.BEGIN_DATE, '%m/%d/%Y' )), ' - ', DESCRIPTION) AS TERM_BLOCK, PK_STUDENT_ENROLLMENT, TUITION_BATCH_DETAIL_PK_COURSE_OFFERING, CONCAT(COURSE_CODE, ' (', SUBSTRING(SESSION, 1, 1), '-', SESSION_NO, ') - ', IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') ) AS COURSE_BATCH_DESC  
		from 
		S_TUITION_BATCH_DETAIL 
		LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = TUITION_BATCH_DETAIL_PK_COURSE_OFFERING 
		LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
		LEFT JOIN S_COURSE on S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
		LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_TUITION_BATCH_DETAIL.PK_STUDENT_MASTER 
		LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_TUITION_BATCH_DETAIL.PK_AR_LEDGER_CODE 
		LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_TUITION_BATCH_DETAIL.PK_TERM_BLOCK
		WHERE 
		PK_TUITION_BATCH_MASTER = '$_GET[id]' AND S_TUITION_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY $order_by ");
		while (!$res_disb1->EOF) { 
			$PK_STUDENT_ENROLLMENT	= $res_disb1->fields['PK_STUDENT_ENROLLMENT'];
			$PK_COURSE_OFFERING		= $res_disb1->fields['TUITION_BATCH_DETAIL_PK_COURSE_OFFERING'];
			
			$total 	+= $res_disb1->fields['AMOUNT'];
				
			$res_enroll = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, IS_ACTIVE_ENROLLMENT,FUNDING FROM S_STUDENT_ENROLLMENT LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");

			$BEGIN_DATE_1 	= trim($res_enroll->fields['BEGIN_DATE_1']);
			$CODE 			= trim($res_enroll->fields['CODE']);
			$STUDENT_STATUS = trim($res_enroll->fields['STUDENT_STATUS']);
			$FUNDING 		= trim($res_enroll->fields['FUNDING']);
			
			if($TYPE == 2) {
				$BATCH_DETAIL = $res_disb1->fields['COURSE_BATCH_DESC'];
			} else if($TYPE == 1 || $TYPE == 9) {
				$res_type = $db->Execute("SELECT CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0 AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				$BATCH_DETAIL = $res_type->fields['CODE'].' - '.$res_type->fields['BEGIN_DATE_1'];
			}
	
			$txt .= '<tr>
						<td>'.$res_disb1->fields['STUD_NAME'].'</td>
						<td>'.$res_disb1->fields['STUDENT_ID'].'</td>
						<td>'.$res_disb1->fields['CODE'].'</td>
						<td>'.$res_disb1->fields['BATCH_DETAIL_DESCRIPTION'].'</td>
						<td>'.$res_disb1->fields['TRANSACTION_DATE'].'</td>
						<td align="right" >$ '.number_format_value_checker($res_disb1->fields['AMOUNT'], 2).'</td>
						<td>'.$BATCH_DETAIL.'</td>
						<td>'.$res_disb1->fields['TUITION_BATCH_DETAIL_AY'].'</td>
						<td>'.$res_disb1->fields['TUITION_BATCH_DETAIL_AP'].'</td>
						<td>'.$BEGIN_DATE_1.' - '.$CODE.' - '.$STUDENT_STATUS.'</td>
						<td>'.$res_disb1->fields['TERM_BLOCK'].'</td>
						<td>'.$res_disb1->fields['TUITION_BATCH_DETAIL_PRIOR_YEAR'].'</td>
					</tr>';
			
			$res_disb1->MoveNext();
		}
		
		$txt .= '
				<tr>
					<td colspan="3" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>Student Count: '.$res_disb_count->RecordCount().'</i></b></td>
					<td colspan="2" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>Batch Totals: $ </i></b></td>
					<td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>'.number_format_value_checker($total,2).'</i></b></td>
					<td colspan="6" style="border-top:1px solid #000;border-bottom:1px solid #000;"></td>
				</tr>
			</table>';
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Tuition Payment.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/
$pdf->Output('temp/'.$file_name, 'FD');

return $file_name;	