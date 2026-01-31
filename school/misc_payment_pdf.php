<?php session_start();
$browser = '';
if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
	$browser =  "chrome";
else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');

class MYPDF extends TCPDF
{
	public function Header()
	{
		global $db;

		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

		if ($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".", $res->fields['PDF_LOGO']);
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
		$this->Cell(55, 8, "Miscellaneous Batch", 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(180, 13, 290, 13, $style);

		$res = $db->Execute("SELECT BATCH_NO, MISC_BATCH_PK_CAMPUS FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$this->SetFont('helvetica', 'I', 13);
		$this->SetY(16);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(235);
		$this->Cell(55, 7, "Batch # " . $res->fields['BATCH_NO'], 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$MISC_BATCH_PK_CAMPUS = $res->fields['MISC_BATCH_PK_CAMPUS'];

		$campus_name = "";
		$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($MISC_BATCH_PK_CAMPUS) order by CAMPUS_CODE ASC");
		while (!$res_campus->EOF) {
			if ($campus_name != '')
				$campus_name .= ', ';
			$campus_name .= $res_campus->fields['CAMPUS_CODE'];

			$res_campus->MoveNext();
		}

		$res = $db->Execute("SELECT * FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$BATCH_DATE  			= $res->fields['BATCH_DATE'];
		$PK_BATCH_STATUS		= $res->fields['PK_BATCH_STATUS'];
		$POSTED_DATE			= $res->fields['POSTED_DATE'];
		$CREDIT  				= $res->fields['CREDIT'];
		$DEBIT					= $res->fields['DEBIT'];
		$BATCH_TOTAL			= $DEBIT - $CREDIT;

		if ($BATCH_DATE == '0000-00-00')
			$BATCH_DATE = '';
		else
			$BATCH_DATE = date("m/d/Y", strtotime($BATCH_DATE));

		if ($POSTED_DATE == '0000-00-00')
			$POSTED_DATE = '';
		else
			$POSTED_DATE = date("m/d/Y", strtotime($POSTED_DATE));

		$res = $db->Execute("SELECT BATCH_STATUS FROM M_BATCH_STATUS WHERE PK_BATCH_STATUS = '$PK_BATCH_STATUS' ");
		$BATCH_STATUS = $res->fields['BATCH_STATUS'];

		$this->SetFont('helvetica', 'I', 10);

		$this->SetY(20);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Status: " . $BATCH_STATUS, 0, 'R', 0, 0, '', '', true);

		$this->SetY(25);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Total: $ " . number_format_value_checker($BATCH_TOTAL, 2), 0, 'R', 0, 0, '', '', true);

		$this->SetY(30);
		$this->SetX(140);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(150, 5, "Batch Date: " . $BATCH_DATE, 0, 'R', 0, 0, '', '', true);

		if ($PK_BATCH_STATUS == 2) {
			$this->SetY(35);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Posted Date: " . $POSTED_DATE, 0, 'R', 0, 0, '', '', true);

			$this->SetY(40);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);
		} else {
			$this->SetY(35);
			$this->SetX(140);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);
		}
	}
	public function Footer()
	{
		global $db;

		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);

		$timezone = $_SESSION['PK_TIMEZONE'];
		if ($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if ($timezone == '' || $timezone == 0)
				$timezone = 4;
		}

		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());

		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 46, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);
$pdf->AddPage();

$res = $db->Execute("SELECT BATCH_NO, IF(BATCH_DATE = '0000-00-00','',DATE_FORMAT(BATCH_DATE, '%m/%d/%Y' )) AS BATCH_DATE, DESCRIPTION, IF(POSTED_DATE = '0000-00-00','',DATE_FORMAT(POSTED_DATE, '%m/%d/%Y' )) AS POSTED_DATE, BATCH_STATUS FROM S_MISC_BATCH_MASTER LEFT JOIN M_BATCH_STATUS ON M_BATCH_STATUS.PK_BATCH_STATUS = S_MISC_BATCH_MASTER.PK_BATCH_STATUS WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND S_MISC_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if ($res->RecordCount() == 0) {
	header("location:manage_misc_batch");
	exit;
}
$TRANS_DATE = $res->fields['TRANS_DATE'];
if ($TRANS_DATE == '0000-00-00')
	$TRANS_DATE = '';
else
	$TRANS_DATE = date("m/d/Y", strtotime($TRANS_DATE));

$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
			<tr>
				<td width="10%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Student</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Student ID</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Ledger Code</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Trans Date</i></b>
				</td>
				<td width="8%" align="right" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Debit</i></b>
				</td>
				<td width="8%" align="right" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Credit</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Description</i></b>
				</td>
				<td width="8%" style="border-bottom:1px solid #000;">
					<b><i>Fee/Payment Type</i></b>
				</td>
				<td width="3%" style="border-bottom:1px solid #000;" align="right" >
					<br /><br /><b><i>AY</i></b>
				</td>
				<td width="3%" style="border-bottom:1px solid #000;" align="right" >
					<br /><br /><b><i>AP</i></b>
				</td>
				<td width="6%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Receipt #</i></b>
				</td>
				<td width="11%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Enrollment</i></b>
				</td>
				<td width="9%" style="border-bottom:1px solid #000;">
					<br /><br /><b><i>Term Block</i></b>
				</td>
				<td width="4%" style="border-bottom:1px solid #000;">
					<b><i>Prior Year</i></b>
				</td>
			</tr>';

$debit_total 	= 0;
$credit_total 	= 0;
$res_disb1 = $db->Execute("select S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT from S_MISC_BATCH_DETAIL LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_MISC_BATCH_DETAIL.PK_STUDENT_MASTER LEFT JOIn S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND S_MISC_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  GROUP BY S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
while (!$res_disb1->EOF) {
	$PK_STUDENT_ENROLLMENT = $res_disb1->fields['PK_STUDENT_ENROLLMENT'];

	$res_disb = $db->Execute("select S_MISC_BATCH_DETAIL.*, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID,CODE,LEDGER_DESCRIPTION from S_MISC_BATCH_DETAIL LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_MISC_BATCH_DETAIL.PK_AR_LEDGER_CODE LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_MISC_BATCH_DETAIL.PK_STUDENT_MASTER LEFT JOIn S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND S_MISC_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");

	$sub_debit_total = 0;
	$TRANS_DATE = "";
	while (!$res_disb->EOF) {
		$res_enroll = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, IS_ACTIVE_ENROLLMENT,FUNDING FROM S_STUDENT_ENROLLMENT LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '" . $res_disb->fields['PK_STUDENT_ENROLLMENT'] . "' ");

		$debit_total 		+= $res_disb->fields['DEBIT'];
		$credit_total 		+= $res_disb->fields['CREDIT'];

		$PRIOR_YEAR = '';
		if ($res_disb->fields['PRIOR_YEAR'] == 1)
			$PRIOR_YEAR = 'Yes';
		else
			$PRIOR_YEAR = 'No';

		$FEE_PAYMENT_TYPE = '';
		if ($res_disb->fields['PK_AR_FEE_TYPE'] > 0) {
			$res11 = $db->Execute("select AR_FEE_TYPE FROM M_AR_FEE_TYPE WHERE PK_AR_FEE_TYPE = '" . $res_disb->fields['PK_AR_FEE_TYPE'] . "' ");
			$FEE_PAYMENT_TYPE = $res11->fields['AR_FEE_TYPE'];
		} else if ($res_disb->fields['PK_AR_PAYMENT_TYPE'] > 0) {
			$res11 = $db->Execute("select AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '" . $res_disb->fields['PK_AR_PAYMENT_TYPE'] . "' ");
			$FEE_PAYMENT_TYPE = $res11->fields['AR_PAYMENT_TYPE'];
		}

		$TERM_BLOCK = '';
		$res11 = $db->Execute("select CONCAT(IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )), ' - ', DESCRIPTION) AS TERM_BLOCK from S_TERM_BLOCK WHERE PK_TERM_BLOCK = '" . $res_disb->fields['PK_TERM_BLOCK'] . "' ");
		$TERM_BLOCK = $res11->fields['TERM_BLOCK'];

		if ($res_disb->fields['TRANSACTION_DATE'] != '' && $res_disb->fields['TRANSACTION_DATE'] != '0000-00-00')
			$TRANS_DATE = date("m/d/Y", strtotime($res_disb->fields['TRANSACTION_DATE']));

		$txt .= '<tr>
								<td >' . trim($res_disb->fields['NAME']) . '</td>
								<td >' . trim($res_disb->fields['STUDENT_ID']) . '</td>
								<td >' . $res_disb->fields['CODE'] . '</td>
								<td >' . $TRANS_DATE . '</td>
								<td align="right" >$ ' . number_format_value_checker($res_disb->fields['DEBIT'], 2) . '</td>
								<td align="right" >$ ' . number_format_value_checker($res_disb->fields['CREDIT'], 2) . '</td>
								<td >' . $res_disb->fields['BATCH_DETAIL_DESCRIPTION'] . '</td>
								<td >' . $FEE_PAYMENT_TYPE . '</td>
								<td align="right" >' . $res_disb->fields['AY'] . '</td>
								<td align="right" >' . $res_disb->fields['AP'] . '</td>
								<td >' . $res_disb->fields['MISC_RECEIPT_NO'] . '</td>
								<td >' . $res_enroll->fields['BEGIN_DATE_1'] . ' - ' . $res_enroll->fields['CODE'] . ' - ' . $res_enroll->fields['STUDENT_STATUS'] . '</td>
								<td >' . $TERM_BLOCK . '</td>
								<td >' . $PRIOR_YEAR . '</td>
							</tr>';

		$res_disb->MoveNext();
	}


	$res_disb1->MoveNext();
}

$txt .= '
				<tr>
					<td colspan="3" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>Student Count: ' . $res_disb1->RecordCount() . '</i></b></td>
					<td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>Totals: </i></b></td>
					<td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>$ ' . number_format_value_checker($debit_total, 2) . '</i></b></td>
					<td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>$ ' . number_format_value_checker($credit_total, 2) . '</i></b></td>
					<td colspan="8" style="border-top:1px solid #000;border-bottom:1px solid #000;" ></td>
				</tr>
			</table>';

//echo $txt;exit;
$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

$file_name = 'Misc Payment.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/
$pdf->Output('temp/' . $file_name, 'FD');

return $file_name;
