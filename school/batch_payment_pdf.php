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

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
	
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
		
		/*$this->SetFont('helvetica', '', 8);
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
		$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L'); */
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(9);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(235);
		$this->Cell(55, 8, "Payment Batch", 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(180, 13, 290, 13, $style);
		
		$res = $db->Execute("SELECT BATCH_NO, BATCH_PK_CAMPUS , COMMENTS FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$COMMENTS				= gen_trim_string($res->fields['COMMENTS'] , 155 , true);
		$this->SetFont('helvetica', 'I', 13);
		$this->SetY(16);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(235);
		$this->Cell(55, 7, "Batch # ".$res->fields['BATCH_NO'], 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$BATCH_PK_CAMPUS = $res->fields['BATCH_PK_CAMPUS'];
		
		$campus_name = "";
		$res_campus = $db->Execute("select PK_CAMPUS, CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($BATCH_PK_CAMPUS) order by CAMPUS_CODE ASC");
		while (!$res_campus->EOF) {
			if($campus_name != '')
				$campus_name .= ', ';
			$campus_name .= $res_campus->fields['CAMPUS_CODE'];

			$res_campus->MoveNext();
		}
		
		$res = $db->Execute("SELECT * FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		$DATE_RECEIVED  			= $res->fields['DATE_RECEIVED'];
		$PK_BATCH_STATUS		= $res->fields['PK_BATCH_STATUS'];
		$POSTED_DATE			= $res->fields['POSTED_DATE'];
		$BATCH_posted_total			= $res->fields['AMOUNT'];
		if($DATE_RECEIVED == '0000-00-00')
			$DATE_RECEIVED = '';
		else
			$DATE_RECEIVED = date("m/d/Y",strtotime($DATE_RECEIVED));
			
		if($POSTED_DATE == '0000-00-00')
			$POSTED_DATE = '';
		else
			$POSTED_DATE = date("m/d/Y",strtotime($POSTED_DATE));
			
		$res = $db->Execute("SELECT BATCH_STATUS FROM M_BATCH_STATUS WHERE PK_BATCH_STATUS = '$PK_BATCH_STATUS' "); 
		$BATCH_STATUS = $res->fields['BATCH_STATUS'];
		
		$this->SetFont('helvetica', 'I', 10);
		
		$this->SetY(20);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Status: ".$BATCH_STATUS, 0, 'R', 0, 0, '', '', true);
		
		$this->SetY(25);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Total: $ ".number_format_value_checker($BATCH_posted_total, 2), 0, 'R', 0, 0, '', '', true);
		
		$this->SetY(30);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Date: ".$DATE_RECEIVED, 0, 'R', 0, 0, '', '', true);
		
		if($PK_BATCH_STATUS == 2) {
			$this->SetY(35);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Posted Date: ".$POSTED_DATE, 0, 'R', 0, 0, '', '', true);
			
			$this->SetY(40);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);

			$this->SetY(40+5);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Batch Comments: ".$COMMENTS, 0, 'R', 0, 0, '', '', true);
		} else {
			$this->SetY(35);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);

			$this->SetY(40);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Batch Comments: ".$COMMENTS, 0, 'R', 0, 0, '', '', true);
		}
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(250);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(50, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
		
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
$pdf->SetMargins(7, 50, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);
$pdf->AddPage();

$res = $db->Execute("SELECT S_PAYMENT_BATCH_MASTER.* FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$_GET[id]' AND S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
if($res->RecordCount() == 0){
	header("location:manage_batch_payment");
	exit;
}
$txt = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
			<tr>
				<td width="9%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Student</i></b>
				</td>
				<td width="7%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>ID</i></b>
				</td>
				<td width="7%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Ledger Code</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Disbursement Date</i></b>
				</td>
				<td width="7%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Trans Date</i></b>
				</td>
				<td width="8%" align="right" style="border-bottom:1px solid #000;">
					<b><i>Disbursement Amount (Credit)</i></b>
				</td>
				<td width="7%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Batch Detail</i></b>
				</td>
				<td width="6%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Payment Type</i></b>
				</td>
				<td width="3%" style="border-bottom:1px solid #000;" align="right" >
					<br /><br /><br /><b><i>AY</i></b>
				</td>
				<td width="3%" style="border-bottom:1px solid #000;" align="right" >
					<br /><br /><br /><b><i>AP</i></b>
				</td>
				<td width="6%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Check #</i></b>
				</td>
				<td width="6%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Receipt #</i></b>
				</td>
				<td width="6%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Status</i></b>
				</td>
				<td width="9%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Enrollment</i></b>
				</td>
				<td width="7%" style="border-bottom:1px solid #000;">
					<br /><br /><br /><b><i>Term Block</i></b>
				</td>
				<td width="4%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Prior Year</i></b>
				</td>
				<td width="4%" style="border-bottom:1px solid #000;">
					<b><i>Message</i></b>
				</td>
			</tr>';
			
		$res_disb = $db->Execute("select S_STUDENT_MASTER.SSN, S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL,S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT, IF(BATCH_TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(BATCH_TRANSACTION_DATE, '%m/%d/%Y' )) AS  BATCH_TRANSACTION_DATE, S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE.CODE AS LEDGER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,RECEIPT_NO, BATCH_NO, ACADEMIC_YEAR, ACADEMIC_PERIOD,BATCH_DETAIL_DESCRIPTION, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE1, DISBURSEMENT_AMOUNT, IF(DEPOSITED_DATE = '0000-00-00','', DATE_FORMAT(DEPOSITED_DATE, '%m/%d/%Y' )) AS DEPOSITED_DATE, BATCH_PAYMENT_STATUS, BATCH_NO,RECEIVED_AMOUNT, IF(PRIOR_YEAR = 1,'Yes', IF(PRIOR_YEAR = 2,'No','')) AS PRIOR_YEAR_1, PRIOR_YEAR,PK_DETAIL_TYPE, DETAIL ,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, S_PAYMENT_BATCH_DETAIL.CHECK_NO AS STUD_CHECK_NO, STUDENT_ID, CAMPUS_CODE, DISBURSEMENT_TYPE
		from 
		S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL 
		LEFT JOIN M_BATCH_PAYMENT_STATUS ON M_BATCH_PAYMENT_STATUS.PK_BATCH_PAYMENT_STATUS = S_PAYMENT_BATCH_DETAIL.PK_BATCH_PAYMENT_STATUS 
		LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK , S_STUDENT_DISBURSEMENT 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE
		, S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		WHERE 
		S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL AND 
		S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = '$_GET[id]'  
		GROUP  BY S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL
		ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, DISBURSEMENT_DATE ASC, M_AR_LEDGER_CODE.CODE ASC ");
		$posted_total = 0;
		while (!$res_disb->EOF) { 
			
			$posted_total += $res_disb->fields['RECEIVED_AMOUNT']; 
			$DETAIL = '';
			if($res_disb->fields['PK_DETAIL_TYPE'] == 4) {
				$DETAIL1 = $res_disb->fields['DETAIL'];
				$res_det1 = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL1' ");
				$DETAIL = $res_det1->fields['AR_PAYMENT_TYPE'];
			} 
			
			$PK_STUDENT_ENROLLMENT = $res_disb->fields['PK_STUDENT_ENROLLMENT'];
			$res_en_2 = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
			
			$SPLIT = '';
			if($res_disb->fields['DISBURSEMENT_TYPE'] == 1) 
				$SPLIT = "Split";
				

			$SSN = $res_disb->fields['SSN'];
			$SSN = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);

			$txt .= '<tr>
						<td >'.trim($res_disb->fields['NAME']).'</td>
						<td >'.trim($res_disb->fields['STUDENT_ID']).'<br>'.$SSN.'</td>
						<td >'.$res_disb->fields['LEDGER'].'</td>
						<td >'.$res_disb->fields['DISBURSEMENT_DATE1'].'</td>
						<td >'.$res_disb->fields['BATCH_TRANSACTION_DATE'].'</td>
						<td align="right" >$ '.number_format_value_checker($res_disb->fields['RECEIVED_AMOUNT'], 2).'</td>
						<td >'.$res_en_2->fields['CODE'].' - '.$res_en_2->fields['BEGIN_DATE_1'].'</td>
						<td >'.$DETAIL.'</td>
						<td align="right" >'.$res_disb->fields['ACADEMIC_YEAR'].'</td>
						<td align="right" >'.$res_disb->fields['ACADEMIC_PERIOD'].'</td>
						<td >'.$res_disb->fields['STUD_CHECK_NO'].'</td>
						<td >'.$res_disb->fields['RECEIPT_NO'].'</td>
						<td >'.$res_disb->fields['BATCH_PAYMENT_STATUS'].'</td>
						
						<td >'.$res_en_2->fields['BEGIN_DATE_1'].' - '.$res_en_2->fields['CODE'].' - '.$res_en_2->fields['STUDENT_STATUS'].' - '.$res_disb->fields['CAMPUS_CODE'].'</td>
						<td >'.$res_disb->fields['BEGIN_DATE_1'].'</td>
						<td >'.$res_disb->fields['PRIOR_YEAR_1'].'</td>
						<td >'.$SPLIT.'</td>
						
					</tr>';
			
			$res_disb->MoveNext();
		}
		
		$txt .= '<tr>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ><b>Total</b></td>
					<td style="border-top:1px solid #000;" align="right" ><b>$ '.number_format_value_checker($posted_total, 2).'</b></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
					<td style="border-top:1px solid #000;" ></td>
				</tr>
			</table>';
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Batch Payment.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');*/

$pdf->Output('temp/'.$file_name, 'FD');

return $file_name;	