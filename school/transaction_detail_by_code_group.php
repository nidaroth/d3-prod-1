<?
ini_set("memory_limit", "20000M");
ini_set("max_execution_time", "1000");
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/transaction_summary.php");
require_once("check_access.php");
include '../global/excel/Classes/PHPExcel/IOFactory.php';

if (check_access('REPORT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	if ($_POST['FORMAT'] == 2) {
		$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Past Due.xlsx';
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(pathinfo($outputFileName, PATHINFO_FILENAME), pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(), $outputFileName);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	}
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	// echo "<pre>";print_r($_REQUEST);exit;

	if ($_POST['FORMAT'] == 1) {
		/////////////////////////////////////////////////////////////////
		$browser = '';
		if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
			$browser =  "chrome";
		else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');


		class MYPDF extends TCPDF
		{
			public function Header()
			{
				global $db, $campus_name;

				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

				if ($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".", $res->fields['PDF_LOGO']);
					// $this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}

				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(235);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Transaction Detail", 0, false, 'L', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(200, 13, 293, 13, $style);

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(14);
				$this->SetX(140);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(152, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);

				$str = "";
				if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = "Between " . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
				else if ($_POST['START_DATE'] != '')
					$str = "From " . $_POST['START_DATE'];
				else if ($_POST['END_DATE'] != '')
					$str = "To " . $_POST['END_DATE'];

				$this->SetY(21);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Transaction Dates: " . $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$report_option = "";
				if ($_POST['REPORT_OPTIONS'] == 1)
					$report_option = "All Transactions";
				else if ($_POST['REPORT_OPTIONS'] == 2)
					$report_option = "Positive Credits or Debits";
				else if ($_POST['REPORT_OPTIONS'] == 3)
					$report_option = "Negative Credits or Debits";
				else if ($_POST['REPORT_OPTIONS'] == 4)
					$report_option = "Positive Credits Only";
				else if ($_POST['REPORT_OPTIONS'] == 5)
					$report_option = "Negative Credits Only";
				else if ($_POST['REPORT_OPTIONS'] == 6)
					$report_option = "Positive Debits Only";
				else if ($_POST['REPORT_OPTIONS'] == 7)
					$report_option = "Negative Debits Only";

				$this->SetY(23);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Report Options: " . $report_option, 0, 'R', 0, 0, '', '', true);

				$detail_option = "";
				if ($_POST['DETAIL_OPTION'] == 1)
					$detail_option = "Award Year";
				else if ($_POST['DETAIL_OPTION'] == 2)
					$detail_option = "AY/AP";
				else if ($_POST['DETAIL_OPTION'] == 3)
					$detail_option = "Description";
				else if ($_POST['DETAIL_OPTION'] == 4)
					$detail_option = "Fee/Payment Type";
				else if ($_POST['DETAIL_OPTION'] == 5)
					$detail_option = "Loan Gross & Fee";
				else if ($_POST['DETAIL_OPTION'] == 6)
					$detail_option = "PYA";
				else if ($_POST['DETAIL_OPTION'] == 7)
					$detail_option = "Receipt # & Check #";
				else if ($_POST['DETAIL_OPTION'] == 8)
					$detail_option = "Term Block";

				$this->SetY(28);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Detail Options: " . $detail_option, 0, 'R', 0, 0, '', '', true);

				$group_by = "";
				if ($_POST['GROUP_BY'] == 1)
					$group_by = "Ledger Code";
				else if ($_POST['GROUP_BY'] == 2)
					$group_by = "Program";
				else if ($_POST['GROUP_BY'] == 3)
					$group_by = "Student";

				$this->SetY(33);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(102, 5, "Group By: " . $group_by, 0, 'R', 0, 0, '', '', true);
			}
			public function Footer()
			{
				global $db, $TIMEZONE;

				$this->SetY(-15);
				$this->SetX(270);
				$this->SetFont('helvetica', 'I', 7);
				$this->Cell(30, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

				$this->SetY(-15);
				$this->SetX(10);
				$this->SetFont('helvetica', 'I', 7);

				$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

				$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
			}
		}

		$pdf = new MYPDF(L, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(3, 40, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();
	}
	#for pdf $txt 
	$txt 	= '';
	#init vars for L-C-G
	$ar_ledger_codes_header_str  = $db->Execute("SELECT GROUP_CONCAT(LEDGER_CODE_GROUP) AS LEDGER_CODE_GROUPS FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN (" . implode(',', $_REQUEST['PK_LEDGER_CODE_GROUP']) . ") ");
	$counter_index = 0;
	$final_group_summary = [];
	foreach ($_REQUEST['PK_LEDGER_CODE_GROUP'] as $key1 => $PK_LEDGER_CODE_GROUP1) {
		$line = 0;
		$imploded =  $PK_LEDGER_CODE_GROUP1;
		$ar_ledger_codes = $db->Execute("SELECT GROUP_CONCAT(PK_AR_LEDGER_CODES) AS CONCATED_RES , LEDGER_CODE_GROUP FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN ($imploded) ");
		$ar_ledger_codes_exploded = explode(',', $ar_ledger_codes->fields['CONCATED_RES']);
		$ar_ledger_codes_exploded = array_unique($ar_ledger_codes_exploded);
		$_POST['PK_AR_LEDGER_CODE'] = [];
		$_POST['PK_AR_LEDGER_CODE'] = $ar_ledger_codes_exploded;

		$timezone = $_SESSION['PK_TIMEZONE'];
		if ($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if ($timezone == '' || $timezone == 0)
				$timezone = 4;
		}

		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$TIMEZONE = $res->fields['TIMEZONE'];

		$campus_name = "";
		$campus_cond = "";
		$campus_id	 = "";
		if (!empty($_POST['PK_CAMPUS'])) {
			$PK_CAMPUS 	 = implode(",", $_POST['PK_CAMPUS']);
			$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		}
		//DIAM-1328
		$display_ssn = "";
		if (!empty($_POST['DISPLAY_SSN'])) {
			if ($_POST['DISPLAY_SSN'] == 1) {
				$display_ssn = 1;
			}
		}

		$cond = "";
		$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
		while (!$res_campus->EOF) {
			if ($campus_name != '')
				$campus_name .= ', ';
			$campus_name .= $res_campus->fields['CAMPUS_CODE'];

			if ($campus_id != '')
				$campus_id .= ',';
			$campus_id .= $res_campus->fields['PK_CAMPUS'];

			$res_campus->MoveNext();
		}
		$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ";

		if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
			$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
			$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
			$cond .= " AND TRANSACTION_DATE BETWEEN '$ST' AND '$ET' ";
		} else if ($_POST['START_DATE'] != '') {
			$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
			$cond .= " AND TRANSACTION_DATE >= '$ST' ";
		} else if ($_POST['END_DATE'] != '') {
			$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
			$cond .= " AND TRANSACTION_DATE <= '$ET' ";
		}

		if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
			$cond .= " AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE IN (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
		}

		$having  = "";
		$having1 = "";
		if ($_POST['REPORT_OPTIONS'] == 1) {
		} else if ($_POST['REPORT_OPTIONS'] == 2) {
			$having  = " ((DEBIT) > 0 OR (CREDIT) > 0) ";
			$having1 = " (DEBIT > 0 OR CREDIT > 0) ";
		} else if ($_POST['REPORT_OPTIONS'] == 3) {
			$having  = " ((DEBIT) < 0 OR (CREDIT) < 0) ";
			$having1 = " (DEBIT < 0 OR CREDIT < 0) ";
		} else if ($_POST['REPORT_OPTIONS'] == 4) {
			$having  = " (CREDIT) > 0 ";
			$having1 = " CREDIT > 0 ";
		} else if ($_POST['REPORT_OPTIONS'] == 5) {
			$having  = " (CREDIT) < 0 ";
			$having1 = " CREDIT < 0 ";
		} else if ($_POST['REPORT_OPTIONS'] == 6) {
			$having  = " (DEBIT) > 0 ";
			$having1 = " DEBIT > 0 ";
		} else if ($_POST['REPORT_OPTIONS'] == 7) {
			$having  = " (DEBIT) < 0 ";
			$having1 = " DEBIT < 0 ";
		}

		if ($having != '') {
			$having  = " AND " . $having;
			$having1 = " HAVING " . $having1;
		}

		if ($_POST['GROUP_BY'] == 1) {
			$main_query = "select S_STUDENT_LEDGER.PK_AR_LEDGER_CODE, CONCAT(CODE,' - ',LEDGER_DESCRIPTION) AS LEDGER , S_STUDENT_LEDGER.DEBIT AS DEBIT , S_STUDENT_LEDGER.CREDIT AS CREDIT
		from 
		S_STUDENT_LEDGER   
		LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
		, S_STUDENT_CAMPUS 
		WHERE 
		S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT AND 
		S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond  $having
		GROUP BY S_STUDENT_LEDGER.PK_AR_LEDGER_CODE  ORDER BY CONCAT(CODE,' - ',LEDGER_DESCRIPTION) ASC";
		} else if ($_POST['GROUP_BY'] == 2) {
			$main_query = "SELECT CONCAT(M_CAMPUS_PROGRAM.CODE,' - ',M_CAMPUS_PROGRAM.DESCRIPTION) as PROGRAM_CODE, M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM 
		from 
		S_STUDENT_LEDGER   
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
		, S_STUDENT_CAMPUS, S_STUDENT_ENROLLMENT
        LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
		WHERE 
		S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT AND 
		-- S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM AND 
		S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT AND 
		S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond $having
		GROUP BY M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM  ORDER BY CONCAT(M_CAMPUS_PROGRAM.CODE,' - ',M_CAMPUS_PROGRAM.DESCRIPTION) ASC";
		} else if ($_POST['GROUP_BY'] == 3) {
			$main_query = "select S_STUDENT_LEDGER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS STUDENT_NAME 
		from 
		S_STUDENT_LEDGER   
		LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
		, S_STUDENT_CAMPUS, S_STUDENT_MASTER 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_LEDGER.PK_STUDENT_MASTER AND 
		S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT AND 
		S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond $having
		GROUP BY S_STUDENT_LEDGER.PK_STUDENT_MASTER  ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC";
		}

		$det_query = "select CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID,SSN, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL, S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL, S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL, PK_STUDENT_LEDGER,  IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%Y-%m-%d' )) AS TRANSACTION_DATE_1, CREDIT,  DEBIT, CAMPUS_CODE, S_STUDENT_LEDGER.CREATED_BY, S_STUDENT_LEDGER.CREATED_ON, AWARD_YEAR, GROSS_AMOUNT, FEE_AMOUNT, M_AR_LEDGER_CODE.CODE  as LEDGER_CODE
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	,S_STUDENT_ENROLLMENT
				
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	,S_STUDENT_LEDGER 
	LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
	LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_STUDENT_LEDGER.PK_STUDENT_DISBURSEMENT 
	LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_DISBURSEMENT.PK_AWARD_YEAR 
	, S_STUDENT_CAMPUS  
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	(S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond ";

		//echo $cond;exit;

		if ($_POST['FORMAT'] == 1) {


			/*** DIAM-673 ***/

			$report_option = "";
			if ($_POST['REPORT_OPTIONS'] == 1)
				$report_option = "All Transactions";
			else if ($_POST['REPORT_OPTIONS'] == 2)
				$report_option = "Positive Credits or Debits";
			else if ($_POST['REPORT_OPTIONS'] == 3)
				$report_option = "Negative Credits or Debits";
			else if ($_POST['REPORT_OPTIONS'] == 4)
				$report_option = "Positive Credits Only";
			else if ($_POST['REPORT_OPTIONS'] == 5)
				$report_option = "Negative Credits Only";
			else if ($_POST['REPORT_OPTIONS'] == 6)
				$report_option = "Positive Debits Only";
			else if ($_POST['REPORT_OPTIONS'] == 7)
				$report_option = "Negative Debits Only";


			$detail_option = "";
			if ($_POST['DETAIL_OPTION'] == 1)
				$detail_option = "Award Year";
			else if ($_POST['DETAIL_OPTION'] == 2)
				$detail_option = "AY/AP";
			else if ($_POST['DETAIL_OPTION'] == 3)
				$detail_option = "Description";
			else if ($_POST['DETAIL_OPTION'] == 4)
				$detail_option = "Fee/Payment Type";
			else if ($_POST['DETAIL_OPTION'] == 5)
				$detail_option = "Loan Gross & Fee";
			else if ($_POST['DETAIL_OPTION'] == 6)
				$detail_option = "PYA";
			else if ($_POST['DETAIL_OPTION'] == 7)
				$detail_option = "Receipt # & Check #";
			else if ($_POST['DETAIL_OPTION'] == 8)
				$detail_option = "Term Block";


			$group_by = "";
			if ($_POST['GROUP_BY'] == 1)
				$group_by = "Ledger Code";
			else if ($_POST['GROUP_BY'] == 2)
				$group_by = "Program";
			else if ($_POST['GROUP_BY'] == 3)
				$group_by = "Student";


			$str = "";
			if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
				$str = "Between " . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
			} else if ($_POST['START_DATE'] != '') {
				$str = "From " . $_POST['START_DATE'];
			} else if ($_POST['END_DATE'] != '') {
				$str = "To " . $_POST['END_DATE'];
			}

			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 	= $res->fields['PDF_LOGO'];

			$logo = "";
			if ($PDF_LOGO != '') {
				//$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				$PDF_LOGO = str_replace('../', $http_path, $PDF_LOGO);
				$logo = '<img src="' . $PDF_LOGO . '" width="100px" />';
			}

			$header = '<table width="100%" >
					<tr>
						<td width="15%" valign="top" >' . $logo . '</td>
						<td width="45%" valign="top" style="font-size:25px;font-family: helvetica;padding-top:20px;" >' . $SCHOOL_NAME . '</td>
						<td width="40%" valign="top" >
							<table width="100%" >
								<tr>
									<td align="right" style="font-size:28px;border-bottom:1px solid #000;font-family: helvetica;font-style: italic;" >Transaction Detail</td>
								</tr>
								<tr>
									<td colspan="3" align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Campus(es): ' . $campus_name . '</td>
								</tr>
								<tr>
									<td colspan="3" align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Transaction Dates: ' . $str . '</td>
								</tr>
								<tr>
									<td colspan="3" align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Report Option: ' . $report_option . '</td>
								</tr>
								<tr>
									<td colspan="3" align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Detail Options: ' . $detail_option . '</td>
								</tr>
								<tr>
									<td colspan="3" align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Group By: ' . $group_by . '</td>
								</tr>
							</table>
						</td>
					</tr>
					
				</table>';

			$date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

			$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>' . $date_footer . '</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
							<td></td>							
						</tr>
					</table>';
			/*** End DIAM-673 ***/

			$total 	= 0;
			if ($counter_index > 0) {
				$pagebreak = ' style="page-break-before: always" ';
			}
			$counter_index++;
			$txt .= '<br><h2 ' . $pagebreak . '>' . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . '</h2>';
			// dd($txt);

			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" style="font-size:11px;">';

			$TOT_CREDIT = 0;
			$TOT_DEBIT 	= 0;
			// if(isset($_REQUEST['debug']))
			// {echo $main_query;exit;}
			$res_ledger = $db->Execute($main_query);


			while (!$res_ledger->EOF) {

				$SUB_TOT_CREDIT = 0;
				$SUB_TOT_DEBIT 	= 0;

				if ($_POST['GROUP_BY'] == 1) {
					$PK_AR_LEDGER_CODE 	= $res_ledger->fields['PK_AR_LEDGER_CODE'];
					$cond2 = " AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ";

					$txt 	.= '<tr>
								<td colspan="14"><i style="font-size:22px"><b>' . $res_ledger->fields['LEDGER'] . '</b></i></td>
							</tr>';
				} else if ($_POST['GROUP_BY'] == 2) {
					$PK_CAMPUS_PROGRAM 	= $res_ledger->fields['PK_CAMPUS_PROGRAM'];
					$cond2 = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ";

					$txt 	.= '<tr>
								<td colspan="14"><i style="font-size:22px"><b>' . $res_ledger->fields['PROGRAM_CODE'] . '</b></i></td>
							</tr>';
				} else if ($_POST['GROUP_BY'] == 3) {
					$PK_STUDENT_MASTER 	= $res_ledger->fields['PK_STUDENT_MASTER'];
					$cond2 = " AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";

					$txt 	.= '<tr>
								<td colspan="14"><i style="font-size:22px"><b>' . $res_ledger->fields['STUDENT_NAME'] . '</b></i></td>
							</tr>';
				}

				$width = 10;
				if ($_POST['GROUP_BY'] == 2 || $_POST['GROUP_BY'] == 3)
					$width = 8;

				$txt 	.= '<tr>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Trans Date</td>
							<td width="' . $width . '%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >' . ($display_ssn ? "SSN" : "ID") . '</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Campus</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Program</td>
							<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Status</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >First Term Date</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Description</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Source</td>';

				if ($_POST['GROUP_BY'] == 2 || $_POST['GROUP_BY'] == 3)
					$txt .= '<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Ledger Code</td>';

				if ($_POST['DETAIL_OPTION'] == 1) {
					$txt .= '<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>Award Year</i></b>
										</td>';
				} else if ($_POST['DETAIL_OPTION'] == 2) {
					$txt .= '<td width="4%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>AY</i></b>
										</td>
										<td width="4%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>AP</i></b>
										</td>';
				} else if ($_POST['DETAIL_OPTION'] == 3) {
					$txt .= '<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>Description</i></b>
										</td>';
				} else if ($_POST['DETAIL_OPTION'] == 4) {
					$txt .= '<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>Fee/Payment Type</i></b>
										</td>';
				} else if ($_POST['DETAIL_OPTION'] == 5) {
					$txt .= '<td width="4%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>Gross</i></b>
										</td>
										<td width="4%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>Fee</i></b>
										</td>';
				} else if ($_POST['DETAIL_OPTION'] == 6) {
					$txt .= '<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>PYA</i></b>
										</td>';
				} else if ($_POST['DETAIL_OPTION'] == 7 || $_POST['DETAIL_OPTION'] == '') {
					$txt .= '<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>Receipt/Check #</i></b>
										</td>';
				} else if ($_POST['DETAIL_OPTION'] == 8) {
					$txt .= '<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;">
											<b><i>Term Block</i></b>
										</td>';
				}

				$txt 	.= '<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="center" >Debit</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="center" >Credit</td>
							<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Created By</td>
						</tr>';

				$res_ledger_det = $db->Execute($det_query . " $cond2 $having1 ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, TRANSACTION_DATE ASC ");
				while (!$res_ledger_det->EOF) {
					$CREDIT = $res_ledger_det->fields['CREDIT'];
					$DEBIT  = $res_ledger_det->fields['DEBIT'];

					$DESCRIPTION = "";
					$CHECK_NO 	 = "";
					$RECEIPT_NO  = "";
					$SOURCE 	 = "";

					$DETAIL1		= '';
					$PRIOR_YEAR		= "";

					if ($res_ledger_det->fields['PK_PAYMENT_BATCH_DETAIL'] > 0) {
						$res_det = $db->Execute("SELECT S_PAYMENT_BATCH_DETAIL.CHECK_NO,RECEIPT_NO,BATCH_DETAIL_DESCRIPTION,BATCH_NO, PRIOR_YEAR, ACADEMIC_YEAR, S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK, S_PAYMENT_BATCH_DETAIL.CREATED_ON, ACADEMIC_PERIOD, PK_DETAIL_TYPE, DETAIL FROM S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT WHERE S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER AND S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = '" . $res_ledger_det->fields['PK_PAYMENT_BATCH_DETAIL'] . "' ");

						$DESCRIPTION 		= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
						$CHECK_NO 	 		= $res_det->fields['CHECK_NO'];
						$RECEIPT_NO  		= $res_det->fields['RECEIPT_NO'];
						$SOURCE 	 		= "Payment: " . $res_det->fields['BATCH_NO'];

						$ACADEMIC_YEAR 		= $res_det->fields['ACADEMIC_YEAR'];
						$ACADEMIC_PERIOD 	= $res_det->fields['ACADEMIC_PERIOD'];
						$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];

						if ($res_det->fields['PK_DETAIL_TYPE'] == 4) {
							$DETAIL = $res_det->fields['DETAIL'];
							$res_det1a = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL' ");
							$DETAIL1 = $res_det1a->fields['AR_PAYMENT_TYPE'];
						}

						if ($res_det->fields['PRIOR_YEAR'] == 1)
							$PRIOR_YEAR = 'Yes';
						else
							$PRIOR_YEAR = 'No';
					} else if ($res_ledger_det->fields['PK_MISC_BATCH_DETAIL'] > 0) {
						$res_det = $db->Execute("SELECT BATCH_NO,BATCH_DETAIL_DESCRIPTION, S_MISC_BATCH_DETAIL.CREATED_ON, AY, AP, PK_TERM_BLOCK, PAYMENT_MODE, MISC_RECEIPT_NO, PRIOR_YEAR, PK_AR_FEE_TYPE, PK_AR_PAYMENT_TYPE FROM S_MISC_BATCH_MASTER,S_MISC_BATCH_DETAIL WHERE S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER = S_MISC_BATCH_DETAIL.PK_MISC_BATCH_MASTER AND PK_MISC_BATCH_DETAIL = '" . $res_ledger_det->fields['PK_MISC_BATCH_DETAIL'] . "' ");

						$DESCRIPTION 		= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
						$SOURCE 	 		= "Misc: " . $res_det->fields['BATCH_NO'];

						$ACADEMIC_YEAR 		= $res_det->fields['AY'];
						$ACADEMIC_PERIOD 	= $res_det->fields['AP'];
						$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];

						$DETAIL1 = '';
						if ($res_det->fields['PAYMENT_MODE'] == 1)
							$DETAIL1 = 'Check';
						else if ($res_det->fields['PAYMENT_MODE'] == 2)
							$DETAIL1 = 'Cash';
						else if ($res_det->fields['PAYMENT_MODE'] == 3)
							$DETAIL1 = 'Money Order';
						else if ($res_det->fields['PAYMENT_MODE'] == 4 || $res_det->fields['PAYMENT_MODE'] == 5) //Ticket #1081
							$DETAIL1 = 'Credit Card';

						if ($res_det->fields['MISC_RECEIPT_NO'] == '')
							$RECEIPT_NO = '';
						else
							$RECEIPT_NO = $MISC_RECEIPT_NO;

						if ($res_det->fields['PK_AR_FEE_TYPE'] > 0) {
							$res11 = $db->Execute("select AR_FEE_TYPE FROM M_AR_FEE_TYPE WHERE PK_AR_FEE_TYPE = '" . $res_det->fields['PK_AR_FEE_TYPE'] . "' ");
							$DETAIL1 = $res11->fields['AR_FEE_TYPE'];
						} else if ($res_det->fields['PK_AR_PAYMENT_TYPE'] > 0) {
							$res11 = $db->Execute("select AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '" . $res_det->fields['PK_AR_PAYMENT_TYPE'] . "' ");
							$DETAIL1 = $res11->fields['AR_PAYMENT_TYPE'];
						}

						if ($res_det->fields['PRIOR_YEAR'] == 1)
							$PRIOR_YEAR = 'Yes';
						else
							$PRIOR_YEAR = 'No';
					} else if ($res_ledger_det->fields['PK_TUITION_BATCH_DETAIL'] > 0) {
						$res_det = $db->Execute("SELECT BATCH_NO,AY,AP,BATCH_DETAIL_DESCRIPTION, S_TUITION_BATCH_DETAIL.CREATED_ON, PK_TERM_BLOCK FROM S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND PK_TUITION_BATCH_DETAIL = '" . $res_ledger_det->fields['PK_TUITION_BATCH_DETAIL'] . "' ");

						$DESCRIPTION = $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
						$SOURCE 	 = "Tuition: " . $res_det->fields['BATCH_NO'];

						$ACADEMIC_YEAR 	 	= $res_det->fields['AY'];
						$ACADEMIC_PERIOD	= $res_det->fields['AP'];
						$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];
					}

					if ($CREDIT < 0) {
						$CREDIT = $CREDIT * -1;
						$CREDIT = '(' . number_format_value_checker($CREDIT, 2) . ')';
					} else
						$CREDIT = number_format_value_checker($CREDIT, 2);

					if ($DEBIT < 0) {
						$DEBIT = $DEBIT * -1;
						$DEBIT = '(' . number_format_value_checker($DEBIT, 2) . ')';
					} else
						$DEBIT = number_format_value_checker($DEBIT, 2);

					$CREATED_BY1 = '';
					if ($res_ledger_det->fields['CREATED_BY'] > 0) {
						$CREATED_BY = $res_ledger_det->fields['CREATED_BY'];
						$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$CREATED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) ");
						$CREATED_BY1 = $res_user->fields['NAME'];
					}

					$res_term = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, DESCRIPTION from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$LED_PK_TERM_BLOCK' ");

					$txt 	.= '<tr>
								<td>' . $res_ledger_det->fields['TRANSACTION_DATE_1'] . '</td>
								<td>' . $res_ledger_det->fields['NAME'] . '</td>
								<td>' . ($display_ssn ? my_decrypt('', $res_ledger_det->fields['SSN']) : $res_ledger_det->fields['STUDENT_ID']) . '</td>
								<td>' . $res_ledger_det->fields['CAMPUS_CODE'] . '</td>
								<td>' . $res_ledger_det->fields['PROGRAM_CODE'] . '</td>
								<td>' . $res_ledger_det->fields['STUDENT_STATUS'] . '</td>
								<td>' . $res_ledger_det->fields['BEGIN_DATE_1'] . '</td>
								<td>' . $DESCRIPTION . '</td>
								<td>' . $SOURCE . '</td>';

					if ($_POST['GROUP_BY'] == 2 || $_POST['GROUP_BY'] == 3)
						$txt .= '<td>' . $res_ledger_det->fields['LEDGER_CODE'] . '</td>';

					if ($_POST['DETAIL_OPTION'] == 1) {
						$txt .= '<td>' . $res_ledger_det->fields['AWARD_YEAR'] . '</td>';
					} else if ($_POST['DETAIL_OPTION'] == 2) {
						$txt .= '<td>' . $ACADEMIC_YEAR . '</td>
											 <td>' . $ACADEMIC_PERIOD . '</td>';
					} else if ($_POST['DETAIL_OPTION'] == 3) {
						$txt .= '<td>' . $DESCRIPTION . '</td>';
					} else if ($_POST['DETAIL_OPTION'] == 4) {
						$txt .= '<td>' . $DETAIL1 . '</td>';
					} else if ($_POST['DETAIL_OPTION'] == 5) {
						$txt .= '<td align="right">' . $res_ledger_det->fields['GROSS_AMOUNT'] . '</td>
											<td align="right" >' . $res_ledger_det->fields['FEE_AMOUNT'] . '</td>';
					} else if ($_POST['DETAIL_OPTION'] == 6) {
						$txt .= '<td>' . $PRIOR_YEAR . '</td>';
					} else if ($_POST['DETAIL_OPTION'] == 7 || $_POST['DETAIL_OPTION'] == '') {
						$txt .= '<td>
												Rcpt # ' . $RECEIPT_NO . '<br />
												Chk # ' . $CHECK_NO . '
											</td>';
					} else if ($_POST['DETAIL_OPTION'] == 8) {
						$txt .= '<td>' . $res_term->fields['BEGIN_DATE_1'] . ' - ' . $res_term->fields['END_DATE_1'] . ' - ' . $res_term->fields['DESCRIPTION'] . '</td>';
					}

					$txt .= '<td align="right" >' . $DEBIT . '</td>
										 <td align="right" >' . $CREDIT . '</td>
										 <td>' . $CREATED_BY1 . '<br />' . convert_to_user_date($res_ledger_det->fields['CREATED_ON'], 'm/d/Y h:i A', $TIMEZONE, date_default_timezone_get()) . '</td>
							</tr>';

					$TOT_CREDIT += $res_ledger_det->fields['CREDIT'];
					$TOT_DEBIT 	+= $res_ledger_det->fields['DEBIT'];

					$SUB_TOT_CREDIT += $res_ledger_det->fields['CREDIT'];
					$SUB_TOT_DEBIT 	+= $res_ledger_det->fields['DEBIT'];

					$res_ledger_det->MoveNext();
				}

				if ($SUB_TOT_CREDIT < 0) {
					$SUB_TOT_CREDIT = $SUB_TOT_CREDIT * -1;
					$SUB_TOT_CREDIT = '(' . number_format_value_checker($SUB_TOT_CREDIT, 2) . ')';
				} else
					$SUB_TOT_CREDIT = number_format_value_checker($SUB_TOT_CREDIT, 2);

				if ($SUB_TOT_DEBIT < 0) {
					$SUB_TOT_DEBIT = $SUB_TOT_DEBIT * -1;
					$SUB_TOT_DEBIT = '(' . number_format_value_checker($SUB_TOT_DEBIT, 2) . ')';
				} else
					$SUB_TOT_DEBIT = number_format_value_checker($SUB_TOT_DEBIT, 2);

				$txt 	.= '<tr>
							<td colspan="10" align="right"><b><i>' . $res_ledger->fields['LEDGER'] . ' Totals: </i></b></td>
							<td align="right" ><b><i>$ ' . $SUB_TOT_DEBIT . '</i></b></td>
							<td align="right" ><b><i>$ ' . $SUB_TOT_CREDIT . '</i></b></td>
						</tr>';

				$res_ledger->MoveNext();
			}

			if ($TOT_DEBIT < 0) {
				$TOT_DEBIT = $TOT_DEBIT * -1;
				$TOT_DEBIT = '(' . number_format_value_checker($TOT_DEBIT, 2) . ')';
			} else
				$TOT_DEBIT = number_format_value_checker($TOT_DEBIT, 2);

			if ($TOT_CREDIT < 0) {
				$TOT_CREDIT = $TOT_CREDIT * -1;
				$TOT_CREDIT = '(' . number_format_value_checker($TOT_CREDIT, 2) . ')';
			} else
				$TOT_CREDIT = number_format_value_checker($TOT_CREDIT, 2);

			$txt 	.= '<tr>
						<td colspan="10" align="right"><b><i>Grand Totals: </i></b></td>
						<td align="right" ><b><i>$ ' . $TOT_DEBIT . '</i></b></td>
						<td align="right" ><b><i>$ ' . $TOT_CREDIT . '</i></b></td>
					</tr>
				</table>';
				$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['TOT_DEBIT'] = $TOT_DEBIT;
				$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['TOT_CREDIT'] = $TOT_CREDIT;

			//echo $txt;exit;
			//$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


			/*** End DIAM-673 ***/

			/////////////////////////////////////////////////////////////////
		} else if ($_POST['FORMAT'] == 2) {

			$file_name = "Transaction Details - ";
			if ($_POST['REPORT_OPTIONS'] == 1)
				$file_name .= "All Transactions.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 2)
				$file_name .= "Positive Credits or Debits.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 3)
				$file_name .= "Negative Credits or Debits.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 4)
				$file_name .= "Positive Credits Only.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 5)
				$file_name .= "Negative Credits Only.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 6)
				$file_name .= "Positive Debits Only.xlsx";
			else if ($_POST['REPORT_OPTIONS'] == 7)
				$file_name .= "Negative Debits Only.xlsx";

			$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
			define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

			$total_fields = 120;
			for ($i = 0; $i <= $total_fields; $i++) {
				if ($i <= 25)
					$cell[] = $cell1[$i];
				else {
					$j = floor($i / 26) - 1;
					$k = ($i % 26);
					//echo $j."--".$k."<br />";
					$cell[] = $cell1[$j] . $cell1[$k];
				}
			}

			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$outputFileName = $dir . $file_name;
			$outputFileName = str_replace(pathinfo($outputFileName, PATHINFO_FILENAME), pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(), $outputFileName);

			try {
				//check if sheet exists 
				$sheety = $objPHPExcel->setActiveSheetIndex($counter_index);
				$sheety->setTitle(substr(clean_sheet_title($ar_ledger_codes->fields['LEDGER_CODE_GROUP']) , 0 , 31));
			} catch (\Throwable $th) {
				//throw $th;
				//else create new sheet and set to active
				$newsheety = $objPHPExcel->createSheet($counter_index);
				$newsheety->setTitle(substr(clean_sheet_title($ar_ledger_codes->fields['LEDGER_CODE_GROUP']) , 0 , 31));
				$objPHPExcel->setActiveSheetIndex($counter_index);
			}
			$cell_no = 'A1';
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($campus_name);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			
			$line 	= 2;
			$index 	= -1;
			$width = false;
			$heading = false;
			$heading[] = 'Trans Date';
			$width[]   = 20;
			$heading[] = 'Ledger Code';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = ($display_ssn ? "SSN" : "ID");
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'First Term Date';
			$width[]   = 20;
			$heading[] = 'Description';
			$width[]   = 20;
			$heading[] = 'Source';
			$width[]   = 20;

			if ($_POST['DETAIL_OPTION'] == 1) {
				$heading[] = "Award Year";
				$width[]   = 20;
			} else if ($_POST['DETAIL_OPTION'] == 2) {
				$heading[] = "AY";
				$width[]   = 20;
				$heading[] = "AP";
				$width[]   = 20;
			} else if ($_POST['DETAIL_OPTION'] == 3) {
				$heading[] = "Description";
				$width[]   = 20;
			} else if ($_POST['DETAIL_OPTION'] == 4) {
				$heading[] = "Fee/Payment Type";
				$width[]   = 20;
			} else if ($_POST['DETAIL_OPTION'] == 5) {
				$heading[] = "Loan Gross";
				$width[]   = 20;
				$heading[] = "Fee";
				$width[]   = 20;
			} else if ($_POST['DETAIL_OPTION'] == 6) {
				$heading[] = "PYA";
				$width[]   = 20;
			} else if ($_POST['DETAIL_OPTION'] == 7) {
				$heading[] = "Receipt # & Check #";
				$width[]   = 20;
			} else if ($_POST['DETAIL_OPTION'] == 8) {
				$heading[] = "Term Block";
				$width[]   = 20;
			}

			$heading[] = 'Debit';
			$width[]   = 20;
			$heading[] = 'Credit';
			$width[]   = 20;
			$heading[] = 'Created By';
			$width[]   = 20;
			$heading[] = 'Created On';
			$width[]   = 20;

			$i = 0;
			foreach ($heading as $title) {
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			$TOT_CREDIT = 0;
			$TOT_DEBIT 	= 0;

			$res_ledger = $db->Execute($main_query);

			while (!$res_ledger->EOF) {

				if ($_POST['GROUP_BY'] == 1) {
					$PK_AR_LEDGER_CODE 	= $res_ledger->fields['PK_AR_LEDGER_CODE'];
					$cond2 = " AND S_STUDENT_LEDGER.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ";
				} else if ($_POST['GROUP_BY'] == 2) {
					$PK_CAMPUS_PROGRAM 	= $res_ledger->fields['PK_CAMPUS_PROGRAM'];
					$cond2 = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ";
				} else if ($_POST['GROUP_BY'] == 3) {
					$PK_STUDENT_MASTER 	= $res_ledger->fields['PK_STUDENT_MASTER'];
					$cond2 = " AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
				}

				$SUB_TOT_CREDIT = 0;
				$SUB_TOT_DEBIT 	= 0;

				$res_ledger_det = $db->Execute($det_query . " $cond2 $having1 ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, TRANSACTION_DATE ASC ");
				while (!$res_ledger_det->EOF) {

					$DESCRIPTION = "";
					$CHECK_NO 	 = "";
					$RECEIPT_NO  = "";
					$SOURCE 	 = "";

					$DETAIL1		= '';
					$PRIOR_YEAR		= "";

					if ($res_ledger_det->fields['PK_PAYMENT_BATCH_DETAIL'] > 0) {
						$res_det = $db->Execute("SELECT S_PAYMENT_BATCH_DETAIL.CHECK_NO,RECEIPT_NO,BATCH_DETAIL_DESCRIPTION,BATCH_NO, PRIOR_YEAR, ACADEMIC_YEAR, S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK, S_PAYMENT_BATCH_DETAIL.CREATED_ON, ACADEMIC_PERIOD, PK_DETAIL_TYPE, DETAIL FROM S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT WHERE S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER AND S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = '" . $res_ledger_det->fields['PK_PAYMENT_BATCH_DETAIL'] . "' ");

						$DESCRIPTION 		= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
						$CHECK_NO 	 		= $res_det->fields['CHECK_NO'];
						$RECEIPT_NO  		= $res_det->fields['RECEIPT_NO'];
						$SOURCE 	 		= "Payment: " . $res_det->fields['BATCH_NO'];

						$ACADEMIC_YEAR 		= $res_det->fields['ACADEMIC_YEAR'];
						$ACADEMIC_PERIOD 	= $res_det->fields['ACADEMIC_PERIOD'];
						$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];

						if ($res_det->fields['PK_DETAIL_TYPE'] == 4) {
							$DETAIL = $res_det->fields['DETAIL'];
							$res_det1a = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL' ");
							$DETAIL1 = $res_det1a->fields['AR_PAYMENT_TYPE'];
						}

						if ($res_det->fields['PRIOR_YEAR'] == 1)
							$PRIOR_YEAR = 'Yes';
						else
							$PRIOR_YEAR = 'No';
					} else if ($res_ledger_det->fields['PK_MISC_BATCH_DETAIL'] > 0) {
						$res_det = $db->Execute("SELECT BATCH_NO,BATCH_DETAIL_DESCRIPTION, S_MISC_BATCH_DETAIL.CREATED_ON, AY, AP, PK_TERM_BLOCK, PAYMENT_MODE, MISC_RECEIPT_NO, PRIOR_YEAR, PK_AR_FEE_TYPE, PK_AR_PAYMENT_TYPE FROM S_MISC_BATCH_MASTER,S_MISC_BATCH_DETAIL WHERE S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER = S_MISC_BATCH_DETAIL.PK_MISC_BATCH_MASTER AND PK_MISC_BATCH_DETAIL = '" . $res_ledger_det->fields['PK_MISC_BATCH_DETAIL'] . "' ");

						$DESCRIPTION 		= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
						$SOURCE 	 		= "Misc: " . $res_det->fields['BATCH_NO'];

						$ACADEMIC_YEAR 		= $res_det->fields['AY'];
						$ACADEMIC_PERIOD 	= $res_det->fields['AP'];
						$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];

						$DETAIL1 = '';
						if ($res_det->fields['PAYMENT_MODE'] == 1)
							$DETAIL1 = 'Check';
						else if ($res_det->fields['PAYMENT_MODE'] == 2)
							$DETAIL1 = 'Cash';
						else if ($res_det->fields['PAYMENT_MODE'] == 3)
							$DETAIL1 = 'Money Order';
						else if ($res_det->fields['PAYMENT_MODE'] == 4 || $res_det->fields['PAYMENT_MODE'] == 5) //Ticket #1081
							$DETAIL1 = 'Credit Card';

						if ($res_det->fields['MISC_RECEIPT_NO'] == '')
							$RECEIPT_NO = '';
						else
							$RECEIPT_NO = $MISC_RECEIPT_NO;

						if ($res_det->fields['PK_AR_FEE_TYPE'] > 0) {
							$res11 = $db->Execute("select AR_FEE_TYPE FROM M_AR_FEE_TYPE WHERE PK_AR_FEE_TYPE = '" . $res_det->fields['PK_AR_FEE_TYPE'] . "' ");
							$DETAIL1 = $res11->fields['AR_FEE_TYPE'];
						} else if ($res_det->fields['PK_AR_PAYMENT_TYPE'] > 0) {
							$res11 = $db->Execute("select AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '" . $res_det->fields['PK_AR_PAYMENT_TYPE'] . "' ");
							$DETAIL1 = $res11->fields['AR_PAYMENT_TYPE'];
						}

						if ($res_det->fields['PRIOR_YEAR'] == 1)
							$PRIOR_YEAR = 'Yes';
						else
							$PRIOR_YEAR = 'No';
					} else if ($res_ledger_det->fields['PK_TUITION_BATCH_DETAIL'] > 0) {
						$res_det = $db->Execute("SELECT BATCH_NO,AY,AP,BATCH_DETAIL_DESCRIPTION, S_TUITION_BATCH_DETAIL.CREATED_ON, PK_TERM_BLOCK FROM S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND PK_TUITION_BATCH_DETAIL = '" . $res_ledger_det->fields['PK_TUITION_BATCH_DETAIL'] . "' ");

						$DESCRIPTION = $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
						$SOURCE 	 = "Tuition: " . $res_det->fields['BATCH_NO'];

						$ACADEMIC_YEAR 	 	= $res_det->fields['AY'];
						$ACADEMIC_PERIOD	= $res_det->fields['AP'];
						$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];
					}

					$line++;
					$index = -1;

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['TRANSACTION_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['LEDGER_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['NAME']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(($display_ssn ? my_decrypt('', $res_ledger_det->fields['SSN']) : $res_ledger_det->fields['STUDENT_ID']));

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['CAMPUS_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['PROGRAM_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['STUDENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['BEGIN_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DESCRIPTION);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SOURCE);

					$res_term = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, DESCRIPTION from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$LED_PK_TERM_BLOCK' ");

					if ($_POST['DETAIL_OPTION'] == 1) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['AWARD_YEAR']);
					} else if ($_POST['DETAIL_OPTION'] == 2) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ACADEMIC_YEAR);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ACADEMIC_PERIOD);
					} else if ($_POST['DETAIL_OPTION'] == 3) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DESCRIPTION);
					} else if ($_POST['DETAIL_OPTION'] == 4) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DETAIL1);
					} else if ($_POST['DETAIL_OPTION'] == 5) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['GROSS_AMOUNT']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['FEE_AMOUNT']);
					} else if ($_POST['DETAIL_OPTION'] == 6) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($PRIOR_YEAR);
					} else if ($_POST['DETAIL_OPTION'] == 7) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($RECEIPT_NO . "/" . $CHECK_NO);
					} else if ($_POST['DETAIL_OPTION'] == 8) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_term->fields['BEGIN_DATE_1'] . ' - ' . $res_term->fields['END_DATE_1'] . ' - ' . $res_term->fields['DESCRIPTION']);
					}

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['DEBIT']);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode("0.00");
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
						array(
							'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
						)
					);
					$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['TOT_DEBIT'] += $res_ledger_det->fields['DEBIT'];
					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger_det->fields['CREDIT']);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode("0.00");
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
						array(
							'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
						)
					);
					$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']]['TOT_CREDIT'] += $res_ledger_det->fields['CREDIT'];
					$index++;
					$cell_no = $cell[$index] . $line;
					if ($res_ledger_det->fields['CREATED_BY'] > 0) {
						$CREATED_BY = $res_ledger_det->fields['CREATED_BY'];
						$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$CREATED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) ");
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_user->fields['NAME']);
					}

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(convert_to_user_date($res_ledger_det->fields['CREATED_ON'], 'm/d/Y h:i A', $TIMEZONE, date_default_timezone_get()));

					$res_ledger_det->MoveNext();
				}

				$res_ledger->MoveNext();
			}

			// $objWriter->save($outputFileName);
			// $objPHPExcel->disconnectWorksheets();
			// header("location:" . $outputFileName);
			$counter_index++;
		}
		
	}
	if ($_POST['FORMAT'] == 1) {
		
## ADD GROUP CODE SUMMARY 

$txt .= '
<br>
<br>
<br>
<br>
<br>
<h2>Ledger Code Group Summary</h2>
<br>
<br>
<table border="0" cellspacing="0" cellpadding="3" width="100%" >
<tr><td width="15%"></td><td>
<table  style="border-collapse: collapse;" border="0" cellspacing="0" cellpadding="3" width="80%">
	<thead>
		<tr>
			<th style="border: 1px solid black;"><b>Ledger Code Group</b></th>
			<th style="border: 1px solid black;text-align:right"><b>Total Debit</b></th>
			<th style="border: 1px solid black;text-align:right"><b>Total Credit</b></th>
		</tr>
	</thead>
<tbody>';
foreach ($final_group_summary as $key => $value) {
	$txt .= 
	'<tr>
		<td style="border: 1px solid black;">'.$key.'</td>
		<td style="border: 1px solid black;text-align:right">$'.$value['TOT_DEBIT'].'</td>
		<td style="border: 1px solid black;text-align:right">$'.$value['TOT_CREDIT'].'</td>
	</tr>';
	// dd($value ,number_format_value_checker($value ) );
}
$txt .= "</tbody></table> </td></tr></table>";
		$file_name = 'Transaction_Detail_' . uniqid() . '.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/

		// $pdf->Output('temp/'.$file_name, 'FD');
		// return $file_name;	

		/*** DIAM-673 ***/
		$header_cont = '<!DOCTYPE HTML>
		<html>
		<head>
		<style>
		div{ padding-bottom:20px !important; }	
		</style>
		</head>
		<body>
		<div> ' . $header . ' </div>
		</body>
		</html>';

		$html_body_cont = '<!DOCTYPE HTML>
		<html>
		<head> <style>
		table{  margin-top: 2px; }
		table tr{  padding-top: 1px !important; }
		</style>
		</head>
		<body>' . $txt . '</body></html>';

		$footer_cont = '<!DOCTYPE HTML><html><head><style>
		tbody td{ font-size:14px !important; }
		</style></head><body>' . $footer . '</body></html>';

		$header_path = create_html_file('header.html', $header_cont);
		$content_path = create_html_file('content.html', $html_body_cont);
		$footer_path = create_html_file('footer.html', $footer_cont);

		$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation Landscape --page-size A4 --page-width 210  --page-height 296 --margin-top 50mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/' . $file_name . ' 2>&1';

		$pdfdata = array('filepath' => 'temp/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/' . $file_name);
		//print_r($pdfdata);
		sleep(2);
		exec($pdfdata['exec']);
		header('Content-Type: Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
		readfile($pdfdata['filepath']);
		exit;
	}
	if($_POST['FORMAT'] == 2){
		// dd($final_group_summary);
		#iterate for summary sheet 
		$newsheety = $objPHPExcel->createSheet($counter_index);
		$newsheety->setTitle('Summary');
		$objPHPExcel->setActiveSheetIndex($counter_index);
		$line = 1;
		$index = -1;
		#header 
		$index++;
		$cell_no = $cell[$index] . $line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Ledger Code Group");
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(60);
		
		
		$index++;
		$cell_no = $cell[$index] . $line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Total Debit");
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(40);

		$index++;
		$cell_no = $cell[$index] . $line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Total Credit");
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(40);
		
		
		foreach ($final_group_summary as $key => $value) {
			$line++;
			# code...
			$index = -1;
		
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
		
		
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value['TOT_DEBIT']);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode('#,###,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
				array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
				)
			);
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value['TOT_CREDIT']);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode('#,###,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
				array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
				)
			);
		}
		#normal code below 
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:" . $outputFileName);
			}
}
