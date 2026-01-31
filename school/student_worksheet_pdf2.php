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
require_once('cutome_excel_generator.php');
include '../global/excel/Classes/PHPExcel/IOFactory.php';
require_once("pdf_custom_header.php");

if ($_GET['detail'] == 1) {
	$view = " Detailed View";
} else {
	$view = " Summary View";
}
$dt_array[] = ['*bold*' => ['Ledger Worksheet : ' . $view]];
class MYPDF extends TCPDF
{
	public function Header()
	{
		global $db;

		if ($_SESSION['temp_id'] != $this->PK_STUDENT_MASTER) {
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;

			$CONTENT = pdf_custom_header($this->PK_STUDENT_MASTER, $this->PK_STUDENT_ENROLLMENT, 1);
			$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true, '', true, true);
			$this->SetMargins('', 45, '');

			$this->SetFont('helvetica', 'I', 20);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(145);
			$this->Cell(55, 8, "Ledger Worksheet", 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetFillColor(0, 0, 0);
			$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
			$this->Line(140, 13, 202, 13, $style);

			$PK_STUDENT_ENROLLMENT = $this->PK_STUDENT_ENROLLMENT;
			$res_camp = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0");

			$this->SetFont('helvetica', 'I', 10);
			$this->SetY(16);
			$this->SetX(98);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(104, 5, "Campus: " . $res_camp->fields['CAMPUS_CODE'], 0, false, 'R', 0, '', 0, false, 'M', 'L');

			$text = "";
			if ($_GET['cur_en'] == 1)
				$text = "Current Enrollment";
			else if ($_GET['cur_en'] == 2)
				$text = "All Enrollments";
			else {
				$eid = explode(",", $_GET['eid']);
				$res_camp = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '" . $this->PK_STUDENT_MASTER . "' ");
				if ($res_camp->RecordCount() == count($eid))
					$text = "All Enrollments";
				else
					$text = "Selected Enrollments";
			}

			$this->SetFont('helvetica', 'I', 10);
			$this->SetY(22);
			$this->SetX(98);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(104, 5, $text, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		} else {
			$this->SetFont('helvetica', 'I', 18);
			$this->SetY(10);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(10);
			$this->Cell(55, 8, $this->STUD_NAME, 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetFont('helvetica', 'I', 18);
			$this->SetY(10);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(145);
			$this->Cell(55, 8, "Ledger Worksheet", 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetMargins('', 18, '');
		}
	}
	public function Footer()
	{
		global $db;

		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);

		$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 13");

		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);

		$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true, '', true, true);

		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page ' . $this->getPageNumGroupAlias() . ' of ' . $this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

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

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 13"); //Ticket # 1234 
$BREAK_VAL = 30 + $res_type->fields['FOOTER_LOC'];
$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);

$PK_STUDENT_MASTER_ARR = explode(",", $_GET['stud_id']);
$_SESSION['temp_id'] = '';
foreach ($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {

	$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER ");
	if ($res->RecordCount() == 0) {
		header("location:index");
		exit;
	}

	$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

	if ($DATE_OF_BIRTH != '0000-00-00')
		$DATE_OF_BIRTH = date("m/d/Y", strtotime($DATE_OF_BIRTH));
	else
		$DATE_OF_BIRTH = '';

	$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");

	$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1 ");
	$PK_STUDENT_ENROLLMENT_ACTIVE = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];

	$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
	$pdf->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT_ACTIVE;
	$pdf->STUD_NAME 			= $res->fields['LAST_NAME'] . ", " . $res->fields['FIRST_NAME'] . " " . $res->fields['MIDDLE_NAME'];



	$PK_STUDENT_ENROLLMENT = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
	$res_camp = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0");

	if ($_GET['cur_en'] == 1)
		$enroll_text = "Current Enrollment";
	else if ($_GET['cur_en'] == 2)
		$enroll_text = "All Enrollments";
	else {
		$eid = explode(",", $_GET['eid']);
		$res_camp2 = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '" . $PK_STUDENT_MASTER . "' ");
		if ($res_camp2->RecordCount() == count($eid))
			$enroll_text = "All Enrollments";
		else
			$enroll_text = "Selected Enrollments";
	}
	$dt_array[] = ["*bold*" => [$res->fields['LAST_NAME'] . ", " . $res->fields['FIRST_NAME'] . " " . $res->fields['MIDDLE_NAME'], "Campus: " . $res_camp->fields['CAMPUS_CODE'] . " ($enroll_text)", "Date of report : " . date('m/d/Y')]];;
	$pdf->startPageGroup();
	$pdf->AddPage();

	$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
	if ($EXPECTED_GRAD_DATE != '0000-00-00')
		$EXPECTED_GRAD_DATE = date("m/d/Y", strtotime($EXPECTED_GRAD_DATE));
	else
		$EXPECTED_GRAD_DATE = '';

	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="80%">
				<tr>
					<td width="100%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>' . $res->fields['LAST_NAME'] . ', ' . $res->fields['FIRST_NAME'] . ' ' . $res->fields['MIDDLE_NAME'] . '</b></td>
				</tr>
				<tr>
					<td width="100%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">Program: ' . $res_enroll->fields['CODE'] . ' - ' . $res_enroll->fields['DESCRIPTION'] . '</td>
				</tr>
				<tr>
					<td width="33%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">ID: ' . $res->fields['STUDENT_ID'] . '<br />DOB: ' . $DATE_OF_BIRTH . '<br />Phone: ' . $res_address->fields['CELL_PHONE'] . '</td>

					<td width="33%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">Status: ' . $res_enroll->fields['STUDENT_STATUS'] . '<br />First Term: ' . $res_enroll->fields['TERM_MASTER'] . '<br />Exp. Grad: ' . $EXPECTED_GRAD_DATE . '</td>
				
					<td width="34%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;">' . $res_address->fields['ADDRESS'] . ' ' . $res_address->fields['ADDRESS_1'] . '<br />' . $res_address->fields['CITY'] . ', ' . $res_address->fields['STATE_CODE'] . ' ' . $res_address->fields['ZIP'] . '<br />' . $res_address->fields['COUNTRY'] . '</td>
				</tr>
			</table>
			<br /><br />';
	$dt_array[] = ['Program ' . $res_enroll->fields['CODE'] . ' - ' . $res_enroll->fields['DESCRIPTION']];

	$dt_array[] = [
		"ID : " . $res->fields['STUDENT_ID'],
		"Status : " . $res_enroll->fields['STUDENT_STATUS'],
		"Address : " . $res_address->fields['ADDRESS'] . ' ' . $res_address->fields['ADDRESS_1']
	];

	$dt_array[] = [
		'DOB: ' . $DATE_OF_BIRTH,
		'First Term: ' . $res_enroll->fields['TERM_MASTER'],
		$res_address->fields['CITY'] . ', ' . $res_address->fields['STATE_CODE'] . ' ' . $res_address->fields['ZIP']
	];

	$dt_array[] = [
		'Phone: ' . $res_address->fields['CELL_PHONE'],
		'Exp. Grad: ' . $EXPECTED_GRAD_DATE,
		$res_address->fields['COUNTRY']
	];

	$dt_array[] = [''];

	$encond = "";
	if ($_GET['cur_en'] == 1)
		$encond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT_ACTIVE' ";

	if ($_GET['detail'] == 1) {
		$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>
							<td width="30%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Fees</i></b>
							</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Trans Date</i></b>
							</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;" align="right" >
								<b><i>Amount</i></b>
							</td>
							<td width="30%" align="right" >
								<b><i>Fee Total [FEE_TOTAL]</i></b>
							</td>
						</tr>
					</thead>';
		$dt_array[] = [
			'*header*' => [
				'Fees',
				'Trans Date',
				'Amount'
			]
		];
		$TOT_DEBIT = 0;
		$res_ledger = $db->Execute("select PK_STUDENT_LEDGER,LEDGER_DESCRIPTION,TRANSACTION_DATE AS TRANSACTION_DATE_1 ,IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE,CREDIT,DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND DEBIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $encond ORDER BY TRANSACTION_DATE_1 ASC, CODE ASC ");
		while (!$res_ledger->EOF) {
			$TOT_DEBIT  += $res_ledger->fields['DEBIT'];

			$txt .= '<tr>
							<td width="30%" >' . $res_ledger->fields['CODE'] . '</td>
							<td width="10%" >' . $res_ledger->fields['TRANSACTION_DATE'] . '</td>
							<td width="20%" align="right" >$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2) . '</td>
						</tr>';
			$dt_array[] = [
				$res_ledger->fields['CODE'],
				$res_ledger->fields['TRANSACTION_DATE'],
				'$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2)
			];
			$res_ledger->MoveNext();
		}

		$txt .= '<tr>
						<td width="30%" ><b>Total</b></td>
						<td width="10%" ></td>
						<td width="20%" align="right" ><b>$ ' . number_format_value_checker($TOT_DEBIT, 2) . '</b></td>
					</tr>';
		$dt_array[] = [
			'*bold*' => [
				'Total',
				'',
				'$ ' . number_format_value_checker($TOT_DEBIT, 2)
			]
		];
		$txt .= '</table><br /><br />';
		$txt = str_replace("[FEE_TOTAL]", "$ " . number_format_value_checker($TOT_DEBIT, 2), $txt);
		$dt_array[] = [
			'*bold*' => [
				'Fee Total',
				'',
				"$ " . number_format_value_checker($TOT_DEBIT, 2)
			]
		];
		$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>
							<td width="30%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Received</i></b>
							</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Trans Date</i></b>
							</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;" align="right" >
								<b><i>Amount</i></b>
							</td>
							<td width="30%" align="right" >
								<b><i>Received Total [RECEIVED_TOTAL]</i></b>
							</td>
						</tr>
					</thead>';
		$dt_array[] = [''];
		$dt_array[] = [
			'*header*' => [
				'Received',
				'Trans Date',
				'Amount'
			]
		];
		$TOT_CREDIT = 0;
		$res_ledger = $db->Execute("select PK_STUDENT_LEDGER,LEDGER_DESCRIPTION, TRANSACTION_DATE AS TRANSACTION_DATE_1 ,IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE,CREDIT,DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CREDIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $encond ORDER BY TRANSACTION_DATE_1 ASC, CODE ASC ");
		while (!$res_ledger->EOF) {
			$TOT_CREDIT  += $res_ledger->fields['CREDIT'];

			$txt .= '<tr>
						<td width="30%" >' . $res_ledger->fields['CODE'] . '</td>
						<td width="10%" >' . $res_ledger->fields['TRANSACTION_DATE'] . '</td>
						<td width="20%" align="right" >$ ' . number_format_value_checker($res_ledger->fields['CREDIT'], 2) . '</td>
					</tr>';
			$dt_array[] = [
				$res_ledger->fields['CODE'],
				$res_ledger->fields['TRANSACTION_DATE'],
				'$ ' . number_format_value_checker($res_ledger->fields['CREDIT'], 2)

			];
			$res_ledger->MoveNext();
		}

		$SUB_TOTAL = $TOT_DEBIT - $TOT_CREDIT;
		$txt .= '<tr>
					<td width="30%" ><b>Total</b></td>
					<td width="10%" ></td>
					<td width="20%" align="right" ><b>$ ' . number_format_value_checker($TOT_CREDIT, 2) . '</b></td>
					<td width="30%" align="right" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" >
						<b><i>Sub Total $ ' . number_format_value_checker($SUB_TOTAL, 2) . '</i></b>
					</td>
				</tr>';
		$dt_array[] = [
			'*bold*' => [
				'Total',
				'',
				'$ ' . number_format_value_checker($TOT_CREDIT, 2)
			]
		];

		$txt .= '</table><br /><br />';
		$txt = str_replace("[RECEIVED_TOTAL]", "$ " . number_format_value_checker($TOT_CREDIT, 2), $txt);
		$dt_array[] = [
			'*bold*' => [
				'Received Total',
				'',
				'$ ' . number_format_value_checker($TOT_CREDIT, 2)
			]
		];
		$dt_array[] = [
			'*bold*' => [
				'Sub Total',
				'',
				'$ ' . number_format_value_checker($SUB_TOTAL, 2)
			]
		];
		$dt_array[] = [''];
		$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>
							<td width="30%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Projected</i></b>
							</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Trans Date</i></b>
							</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;" align="right" >
								<b><i>Amount</i></b>
							</td>
							<td width="30%" align="right" >
								<b><i>Projected Total [PROJECTED_TOTAL]</i></b>
							</td>
						</tr>
					</thead>';

		$dt_array[] = [
			'*header*' => [
				'Projected',
				'Trans Date',
				'Amount'
			]
		];
		$TOT_PROJECTED = 0;

		$k = 0;
		$res_ledger = $db->Execute("select DISBURSEMENT_AMOUNT, DISBURSEMENT_DATE as DISBURSEMENT_DATE_1, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE, M_AR_LEDGER_CODE.CODE from S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DISBURSEMENT_STATUS = 2 AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $encond ORDER BY DISBURSEMENT_DATE_1 ASC, CODE ASC ");
		while (!$res_ledger->EOF) {
			$TOT_PROJECTED  += $res_ledger->fields['DISBURSEMENT_AMOUNT'];

			$txt .= '<tr>
						<td width="30%" >' . $res_ledger->fields['CODE'] . '</td>
						<td width="10%" >' . $res_ledger->fields['DISBURSEMENT_DATE'] . '</td>
						<td width="20%" align="right" >$ ' . number_format_value_checker($res_ledger->fields['DISBURSEMENT_AMOUNT'], 2) . '</td>';

			$txt .= '</tr>';
			$dt_array[] = [
				$res_ledger->fields['CODE'],
				$res_ledger->fields['DISBURSEMENT_DATE'],
				'$ ' . number_format_value_checker($res_ledger->fields['DISBURSEMENT_AMOUNT'], 2)
			];
			$res_ledger->MoveNext();
		}

		$GRAND_TOTAL = $SUB_TOTAL - $TOT_PROJECTED;
		$txt .= '<tr>
					<td width="30%" ><b>Total</b></td>
					<td width="10%" ></td>
					<td width="20%" align="right" ><b>$ ' . number_format_value_checker($TOT_PROJECTED, 2) . '</b></td>
					<td width="30%" align="right" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" >
						<b><i>Grand Total $ ' . number_format_value_checker($GRAND_TOTAL, 2) . '</i></b>
					</td>
				</tr>';
		$dt_array[] = [
			'*bold*' => [
				'Total',
				'',
				'$ ' . number_format_value_checker($TOT_PROJECTED, 2)
			]
		];

		$txt .= '</table><br /><br />';
		$txt = str_replace("[PROJECTED_TOTAL]", "$ " . number_format_value_checker($TOT_PROJECTED, 2), $txt);
		$dt_array[] = [
			'*bold*' => [
				'Projected Total',
				'',
				"$ " . number_format_value_checker($TOT_PROJECTED, 2)
			]
		];
		$dt_array[] = [
			'*bold*' => [
				'Grand Total',
				'',
				'$ ' . number_format_value_checker($GRAND_TOTAL, 2)
			]
		];
	} else {





		$dt_array[] = [''];
		$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>
							<td width="30%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i></i></b>
							</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;" align="right" >
								<b><i></i></b>
							</td>
							<td width="30%" align="right" >
								<b><i>Fee Total [FEE_TOTAL]</i></b>
							</td>
						</tr>
					</thead>';
		$dt_array[] = [
			'*header*' => [
				'Fees',
				'Amount'
			]
		];
		$TOT_DEBIT = 0;
		$res_ledger = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND DEBIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $encond GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY CODE ASC ");
		while (!$res_ledger->EOF) {
			$TOT_DEBIT  += $res_ledger->fields['DEBIT'];

			$txt .= '<tr>
							<td width="30%" >' . $res_ledger->fields['CODE'] . '</td>
							<td width="20%" align="right" >$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2) . '</td>
						</tr>';
			$dt_array[] = [
				$res_ledger->fields['CODE'],
				'$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2)
			];
			$res_ledger->MoveNext();
		}

		$txt .= '<tr>
						<td width="30%" ><b>Total</b></td>
						<td width="20%" align="right" ><b>$ ' . number_format_value_checker($TOT_DEBIT, 2) . '</b></td>
					</tr>';
		$dt_array[] = [
			'*bold*' => [
				'Total',
				'$ ' . number_format_value_checker($TOT_DEBIT, 2)
			]
		];
		$txt .= '</table><br /><br />';
		$txt = str_replace("[FEE_TOTAL]", "$ " . number_format_value_checker($TOT_DEBIT, 2), $txt);
		$dt_array[] = [
			'*bold*' => [
				'Fee Total',
				'$ ' . number_format_value_checker($TOT_DEBIT, 2)
			]
		];

		$dt_array[] = [''];
		$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>
							<td width="30%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Received</i></b>
							</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;" align="right" >
								<b><i>Amount</i></b>
							</td>
							<td width="30%" align="right" >
								<b><i>Received Total [RECEIVED_TOTAL]</i></b>
							</td>
						</tr>
					</thead>';
		$dt_array[] = [
			'*header*' => [
				'Received',
				'Amount'
			]
		];
		$TOT_CREDIT = 0;
		$res_ledger = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT, M_AR_LEDGER_CODE.CODE from S_STUDENT_LEDGER LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE WHERE  S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CREDIT != 0 AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $encond GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE ORDER BY CODE ASC ");
		while (!$res_ledger->EOF) {
			$TOT_CREDIT  += $res_ledger->fields['CREDIT'];

			$txt .= '<tr>
						<td width="30%" >' . $res_ledger->fields['CODE'] . '</td>
						<td width="20%" align="right" >$ ' . number_format_value_checker($res_ledger->fields['CREDIT'], 2) . '</td>
					</tr>';
			$dt_array[] = [
				$res_ledger->fields['CODE'],
				number_format_value_checker($res_ledger->fields['CREDIT'], 2)
			];
			$res_ledger->MoveNext();
		}

		$SUB_TOTAL = $TOT_DEBIT - $TOT_CREDIT;
		$txt .= '<tr>
					<td width="30%" ><b>Total</b></td>
					<td width="20%" align="right" ><b>$ ' . number_format_value_checker($TOT_CREDIT, 2) . '</b></td>
					<td width="30%" align="right" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" >
						<b><i>Sub Total $ ' . number_format_value_checker($SUB_TOTAL, 2) . '</i></b>
					</td>
				</tr>';
		$dt_array[] = [
			'*bold*' => [
				'Total',
				number_format_value_checker($TOT_CREDIT, 2)
			]
		];

		$txt .= '</table><br /><br />';
		$txt = str_replace("[RECEIVED_TOTAL]", "$ " . number_format_value_checker($TOT_CREDIT, 2), $txt);
		$dt_array[] = [
			'*bold*' => [
				'Received Total',
				number_format_value_checker($TOT_CREDIT, 2)
			]
		];

		$dt_array[] = [
			'*bold*' => [
				'Sub Total',
				number_format_value_checker($SUB_TOTAL, 2)
			]
		];

		$dt_array[] = [''];

		$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>
							<td width="30%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;">
								<b><i>Projected</i></b>
							</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #d1ccd5;" align="right" >
								<b><i>Amount</i></b>
							</td>
							<td width="30%" align="right" >
								<b><i>Projected Total [PROJECTED_TOTAL]</i></b>
							</td>
						</tr>
					</thead>';

		$dt_array[] = [
			'*header*' => [
				'Projected',
				'Amount'
			]
		];
		$TOT_PROJECTED = 0;
		$res_ledger = $db->Execute("select SUM(DISBURSEMENT_AMOUNT) AS DISBURSEMENT_AMOUNT, M_AR_LEDGER_CODE.CODE from S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DISBURSEMENT_STATUS = 2 AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $encond GROUP BY S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE ORDER BY CODE ASC");
		while (!$res_ledger->EOF) {
			$TOT_PROJECTED  += $res_ledger->fields['DISBURSEMENT_AMOUNT'];

			$txt .= '<tr>
						<td width="30%" >' . $res_ledger->fields['CODE'] . '</td>
						<td width="20%" align="right" >$ ' . number_format_value_checker($res_ledger->fields['DISBURSEMENT_AMOUNT'], 2) . '</td>
					</tr>';
			$dt_array[] = [

				$res_ledger->fields['CODE'],
				'$ ' . number_format_value_checker($res_ledger->fields['DISBURSEMENT_AMOUNT'], 2)

			];
			$res_ledger->MoveNext();
		}

		$GRAND_TOTAL = $SUB_TOTAL - $TOT_PROJECTED;
		$txt .= '<tr>
					<td width="30%" ><b>Total</b></td>
					<td width="20%" align="right" ><b>$ ' . number_format_value_checker($TOT_PROJECTED, 2) . '</b></td>
					<td width="30%" align="right" style="border-top: 1px solid #000;border-bottom: 1px solid #000;" >
						<b><i>Grand Total $ ' . number_format_value_checker($GRAND_TOTAL, 2) . '</i></b>
					</td>
				</tr>';
		$dt_array[] = [
			'*bold*' => [
				'Total',
				'$ ' . number_format_value_checker($TOT_PROJECTED, 2)
			]
		];
		$txt .= '</table><br /><br />';
		$txt = str_replace("[PROJECTED_TOTAL]", "$ " . number_format_value_checker($TOT_PROJECTED, 2), $txt);
		$dt_array[] = [
			'*bold*' => [
				'Projected Total',
				'$ ' . number_format_value_checker($TOT_PROJECTED, 2)
			]
		];
		$dt_array[] = [
			'*bold*' => [
				'Grand Total',
				'$ ' . number_format_value_checker($GRAND_TOTAL, 2)
			]
		];
	}
	$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
}


$file_name = 'worksheet.pdf';
$file_name = 'Ledger.xlsx';

// echo "<pre>";
// print_r($dt_array);
// echo "---------<br>";
$outputFileName = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $file_name, $dt_array, $header);
echo $outputFileName;
exit;

/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/

if (isset($_REQUEST['excel_flag'])) {

	exit;
} else {
	// $pdf->Output('temp/' . $file_name, 'FD');
}
return $file_name;
