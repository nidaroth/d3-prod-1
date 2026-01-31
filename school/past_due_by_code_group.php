<?
ini_set("pcre.backtrack_limit", "5000000");

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/transaction_summary.php");
require_once("../language/student_balance.php");
require_once("check_access.php");
include '../global/excel/Classes/PHPExcel/IOFactory.php';
require_once '../global/mpdf/vendor/autoload.php';

if (check_access('REPORT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	#common init for excel 
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
	#end of  - common init for excel  
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	// echo "<pre>";print_r($_REQUEST);exit;
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
		$cond = "";
		if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
			$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
			$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
			$cond .= " AND DISBURSEMENT_DATE BETWEEN '$ST' AND '$ET' ";
		} else if ($_POST['START_DATE'] != '') {
			$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
			$cond .= " AND DISBURSEMENT_DATE >= '$ST' ";
		} else if ($_POST['END_DATE'] != '') {
			$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
			$cond .= " AND DISBURSEMENT_DATE <= '$ET' ";
		}

		if (!empty($_POST['PK_STUDENT_STATUS'])) {
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") ";
		} else {
			$sts = "";
			$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
			while (!$res_type->EOF) {
				if ($sts != '')
					$sts .= ',';
				$sts .= $res_type->fields['PK_STUDENT_STATUS'];
				$res_type->MoveNext();
			}

			if ($sts != '')
				$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $sts . ") ";
		}

		$PK_AR_LEDGER_CODE_SELECTED = implode(",", $_POST['PK_AR_LEDGER_CODE']);

		//echo $cond;exit;
		$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, DISBURSEMENT_DATE ";
		$query = "SELECT 
					CONCAT(LAST_NAME, ', ', FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, 
					IF(BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d')) AS BEGIN_DATE_1, 
					DISBURSEMENT_AMOUNT, 
					M_CAMPUS_PROGRAM.CODE AS PROGRAM_CODE, 
					STUDENT_STATUS, 
					STUDENT_ID, 
					S_STUDENT_DISBURSEMENT.FUNDS_REQUESTED AS FUNDS_REQUIRED, -- DIAM - 1276
					IF(DISBURSEMENT_DATE = '0000-00-00', '', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d')) AS DISBURSEMENT_DATE_1, 
					CELL_PHONE, 
					HOME_PHONE, 
					EMAIL, 
					DATEDIFF('$ET', DISBURSEMENT_DATE) AS DAYS_PAST, 
					S_STUDENT_MASTER.PK_STUDENT_MASTER 
			    FROM 
					S_STUDENT_MASTER 
					LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
					LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
					AND PK_STUDENT_CONTACT_TYPE_MASTER = 1, 
					S_STUDENT_DISBURSEMENT, 
					S_STUDENT_ENROLLMENT 
					LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
					LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
					LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
					LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING
				WHERE 
					S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER 
					AND S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
					AND S_STUDENT_DISBURSEMENT.DEPOSITED_DATE = '0000-00-00' -- DIAM-1276
					AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
					AND PK_DISBURSEMENT_STATUS IN (2, 3, 4) $cond ";

		if ($_POST['FORMAT'] == 1) {
			/////////////////////////////////////////////////////////////////
			

			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if ($PDF_LOGO != '')
				$logo = '<img src="' . $PDF_LOGO . '" height="50px" />';

			$date_str = "";
			if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
				$date_str = " " . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
			else if ($_POST['START_DATE'] != '')
				$date_str = " From " . $_POST['START_DATE'];
			else if ($_POST['END_DATE'] != '')
				$date_str = " As of Date: " . $_POST['END_DATE'];

			$stud_str = "";
			if (empty($_POST['PK_STUDENT_STATUS'])) {
				$stud_str = "All Student Status";
			} else {
				$stud_str = "";
				$res_type_all = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) ");

				$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");

				if ($res_type_all->RecordCount() == $res_type->RecordCount())
					$stud_str = "All Student Status";
				else {
					while (!$res_type->EOF) {
						if ($stud_str != '')
							$stud_str .= ', ';
						$stud_str .= $res_type->fields['STUDENT_STATUS'];
						$res_type->MoveNext();
					}

					if ($stud_str != '')
						$stud_str = "Student Status: " . $stud_str;
				}
			}

			$led_str = "";
			if (empty($_POST['PK_AR_LEDGER_CODE'])) {
				$led_str = "All Ledger Codes";
			} else {
				$led_str = "";
				$res_type_all = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 ");

				$PK_AR_LEDGER_CODE_SELECTED = implode(",", $_POST['PK_AR_LEDGER_CODE']);
				$res_type = $db->Execute("select CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");

				if ($res_type_all->RecordCount() == $res_type->RecordCount())
					$led_str = "All Ledger Codes";
				else {
					while (!$res_type->EOF) {
						if ($led_str != '')
							$led_str .= ', ';
						$led_str .= $res_type->fields['CODE'];
						$res_type->MoveNext();
					}
					if ($led_str != '')
						$led_str = "Ledger Codes: " . $led_str;
				}
			}

			$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >' . $logo . '</td>
							<td width="50%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
							<td width="30%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Past Due Payments</b></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $date_str . '</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $stud_str . '</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $led_str . '</td>
						</tr>
					</table>';

			$timezone = $_SESSION['PK_TIMEZONE'];
			if ($timezone == '' || $timezone == 0) {
				$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$timezone = $res->fields['PK_TIMEZONE'];
				if ($timezone == '' || $timezone == 0)
					$timezone = 4;
			}

			$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
			$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());

			$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>' . $date . '</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
						</tr>
					</table>';

			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 7,
				'format' => [210, 296],
				'orientation' => 'L'
			]);
			$mpdf->autoPageBreak = true;

			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);

			$total 	= 0;

			$gt_30  = 0;
			$gt_60  = 0;
			$gt_90  = 0;
			$gt_120 = 0;
			$gt_121 = 0;
			$gt 	= 0;
			if ($counter_index > 0) {
				$pagebreak = ' style="page-break-before: always" ';
			}
			$txt .= '<br><h1 ' . $pagebreak . '>' . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . '</h1>';

			$res_ledger = $db->Execute("select PK_AR_LEDGER_CODE, CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");
			while (!$res_ledger->EOF) {
				$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];
				$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
						<thead>
							<tr>
								<td width="100%" colspan="12" ><b style="font-size:18px" >' . $res_ledger->fields['CODE'] . '</b></td>
							</tr>
							<tr>
								<td style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;border-right:1px solid #000;" ></td>
								<td width="10%" style="border-top:1px solid #000;border-right:1px solid #000;" ></td>
								<td width="7%" style="border-top:1px solid #000;border-right:1px solid #000;" ></td>
								<td width="6%" style="border-top:1px solid #000;border-right:1px solid #000;" > </td>
								<td width="4%" style="border-top:1px solid #000;border-right:1px solid #000;" ><b>Funds</b></td>
								<td width="3%" style="border-top:1px solid #000;border-right:1px solid #000;" ><b>Past</b></td>
								
								<td colspan="2" style="border-top:1px solid #000;border-right:1px solid #000;;" align="center" ><b>Expected Payment</b></td>
								<td style="border-top:1px solid #000;border-right:1px solid #000;" ></td>
								<td colspan="2" style="border-top:1px solid #000;border-right:1px solid #000;" align="center"><b>Phone</b></td>
							</tr>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" ><b>Student</b></td>
								<td width="9%" style="border-bottom:1px solid #000;border-right:1px solid #000;" ><b>Student ID</b></td>
								<td width="10%" style="border-bottom:1px solid #000;border-right:1px solid #000;" ><b>Program Code</b></td>
								<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;" ><b>Status</b></td>
								<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;" ><b>Start Date</b></td>
								<td width="4%" style="border-bottom:1px solid #000;border-right:1px solid #000;" ><b>Reqd.</b></td>
								<td width="3%" style="border-bottom:1px solid #000;border-right:1px solid #000;" ><b>Due</b></td>
								
								<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" ><b>Date</b></td>
								<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Amount</b></td>
								<td width="18%" style="border-bottom:1px solid #000;border-right:1px solid #000;" ><b>Email</b></td>
								<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" ><b>Home</b></td>
								<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" ><b>Mobile</b></td>
							</tr>
						</thead>';

				$i 				   	= 0;
				$PK_STUDENT_MASTER 	= '';
				$st_30  			= 0;
				$st_60  			= 0;
				$st_90  			= 0;
				$st_120 			= 0;
				$st_121 			= 0;
				$st 				= 0;
				$res = $db->Execute($query . " AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' " . $order_by);
				while (!$res->EOF) {
					$i++;

					$border = "";
					if ($PK_STUDENT_MASTER != $res->fields['PK_STUDENT_MASTER']) {
						$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
						if ($i > 1)
							$border = "border-top:1px solid #000;";
					}

					if ($i == $res->RecordCount())
						$border .= "border-bottom:1px solid #000;";

					if ($res->fields['DAYS_PAST'] <= 30) {
						$gt_30  += $res->fields['DISBURSEMENT_AMOUNT'];
						$st_30  += $res->fields['DISBURSEMENT_AMOUNT'];
					} else if ($res->fields['DAYS_PAST'] <= 60) {
						$gt_60  += $res->fields['DISBURSEMENT_AMOUNT'];
						$st_60  += $res->fields['DISBURSEMENT_AMOUNT'];
					} else if ($res->fields['DAYS_PAST'] <= 90) {
						$gt_90  += $res->fields['DISBURSEMENT_AMOUNT'];
						$st_90  += $res->fields['DISBURSEMENT_AMOUNT'];
					} else if ($res->fields['DAYS_PAST'] <= 120) {
						$gt_120  += $res->fields['DISBURSEMENT_AMOUNT'];
						$st_120  += $res->fields['DISBURSEMENT_AMOUNT'];
					} else {
						$gt_121  += $res->fields['DISBURSEMENT_AMOUNT'];
						$st_121  += $res->fields['DISBURSEMENT_AMOUNT'];
					}

					$gt += $res->fields['DISBURSEMENT_AMOUNT'];
					$st += $res->fields['DISBURSEMENT_AMOUNT'];

					$amount = $res->fields['DISBURSEMENT_AMOUNT'];
					if ($amount < 0)
						$amount = '(' . number_format_value_checker_new(($amount * -1), 2) . ')';
					else
						$amount = number_format_value_checker_new($amount, 2);

					// DIAM - 1276
					$Funds_Reqd = $res->fields['FUNDS_REQUIRED'];
					if ($Funds_Reqd != '' && $Funds_Reqd == 1) {
						$Funds_Reqd_Res = 'Y';
					} else {
						$Funds_Reqd_Res = 'N';
					}
					// End DIAM - 1276

					$txt .= '<tr>
							<td style="' . $border . '" >' . $res->fields['NAME'] . '</td>
							<td style="' . $border . '" >' . $res->fields['STUDENT_ID'] . '</td>
							<td style="' . $border . '" >' . $res->fields['PROGRAM_CODE'] . '</td>
							<td style="' . $border . '" >' . $res->fields['STUDENT_STATUS'] . '</td>
							<td style="' . $border . '" >' . $res->fields['BEGIN_DATE_1'] . '</td>
							<td align="center" style="' . $border . '" >' . $Funds_Reqd_Res . '</td>
							<td style="' . $border . '" >' . $res->fields['DAYS_PAST'] . '</td>
							<td style="' . $border . '" >' . $res->fields['DISBURSEMENT_DATE_1'] . '</td>
							<td style="' . $border . '" align="right" >$ ' . $amount . '</td>
							<td style="' . $border . '" >' . $res->fields['EMAIL'] . '</td>
							<td style="' . $border . '" >' . $res->fields['HOME_PHONE'] . '</td>
							<td style="' . $border . '" >' . $res->fields['CELL_PHONE'] . '</td>
						</tr>';
					$res->MoveNext();
				}

				if ($st_30 < 0)
					$st_30 = '(' . number_format_value_checker_new(($st_30 * -1), 2) . ')';
				else
					$st_30 = number_format_value_checker_new($st_30, 2);

				if ($st_60 < 0)
					$st_60 = '(' . number_format_value_checker_new(($st_60 * -1), 2) . ')';
				else
					$st_60 = number_format_value_checker_new($st_60, 2);

				if ($st_90 < 0)
					$st_90 = '(' . number_format_value_checker_new(($st_90 * -1), 2) . ')';
				else
					$st_90 = number_format_value_checker_new($st_90, 2);

				if ($st_120 < 0)
					$st_120 = '(' . number_format_value_checker_new(($st_120 * -1), 2) . ')';
				else
					$st_120 = number_format_value_checker_new($st_120, 2);

				if ($st_121 < 0)
					$st_121 = '(' . number_format_value_checker_new(($st_121 * -1), 2) . ')';
				else
					$st_121 = number_format_value_checker_new($st_121, 2);

				if ($st < 0)
					$st = '(' . number_format_value_checker_new(($st * -1), 2) . ')';
				else
					$st = number_format_value_checker_new($st, 2);

				$txt 	.= '</table><br /><br />
						<table border="0" cellspacing="0" cellpadding="0" width="100%">
							<tr nobr="true" >
								<td width="12%" ></td>
								<td width="80%" >
									<table border="0" cellspacing="0" cellpadding="2" width="100%">
										<tr>
											<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;border-left:1px solid #000;" ><b>Ledger</b></td>
											<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>0-30</b></td>
											<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>31-60</b></td>
											<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>61-90</b></td>
											<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>91-120</b></td>
											<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>Over 120</b></td>
											<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>Total</b></td>
										</tr>
										<tr>
											<td align="center" style="border-bottom:1px solid #000;border-right:1px solid #000;border-left:1px solid #000;"  ><b>Totals</b></td>
											<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $st_30 . '</td>
											<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $st_60 . '</td>
											<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $st_90 . '</td>
											<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $st_120 . '</td>
											<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $st_121 . '</td>
											<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $st . '</td>
										</tr>
									</table>
								</td>
							</tr>
						</table><br /><br />';
				$res_ledger->MoveNext();
			}

			if ($gt_30 < 0)
				$gt_30 = '(' . number_format_value_checker_new(($gt_30 * -1), 2) . ')';
			else
				$gt_30 = number_format_value_checker_new($gt_30, 2);

			if ($gt_60 < 0)
				$gt_60 = '(' . number_format_value_checker_new(($gt_60 * -1), 2) . ')';
			else
				$gt_60 = number_format_value_checker_new($gt_60, 2);

			if ($gt_90 < 0)
				$gt_90 = '(' . number_format_value_checker_new(($gt_90 * -1), 2) . ')';
			else
				$gt_90 = number_format_value_checker_new($gt_90, 2);

			if ($gt_120 < 0)
				$gt_120 = '(' . number_format_value_checker_new(($gt_120 * -1), 2) . ')';
			else
				$gt_120 = number_format_value_checker_new($gt_120, 2);

			if ($gt_121 < 0)
				$gt_121 = '(' . number_format_value_checker_new(($gt_121 * -1), 2) . ')';
			else
				$gt_121 = number_format_value_checker_new($gt_121, 2);

				$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] = $gt;
			if ($gt < 0)
				$gt = '(' . number_format_value_checker_new(($gt * -1), 2) . ')';
			else
				$gt = number_format_value_checker_new($gt, 2);
			$txt 	.= '<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr nobr="true" >
							<td width="12%" ></td>
							<td width="80%" >
								<table border="0" cellspacing="0" cellpadding="2" width="100%">
									<tr>
										<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;border-left:1px solid #000;" ><b>Report</b></td>
										<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>0-30</b></td>
										<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>31-60</b></td>
										<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>61-90</b></td>
										<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>91-120</b></td>
										<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>Over 120</b></td>
										<td align="center" style="border-top:1px solid #000;border-right:1px solid #000;"  ><b>Total</b></td>
									</tr>
									<tr>
										<td align="center" style="border-bottom:1px solid #000;border-right:1px solid #000;border-left:1px solid #000;"  ><b>Totals</b></td>
										<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $gt_30 . '</td>
										<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $gt_60 . '</td>
										<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $gt_90 . '</td>
										<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $gt_120 . '</td>
										<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $gt_121 . '</td>
										<td align="right" style="border-bottom:1px solid #000;border-right:1px solid #000;"  >$' . $gt . '</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
					


			//echo $txt;exit;


			/////////////////////////////////////////////////////////////////
		} else if ($_POST['FORMAT'] == 2) {

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
			$file_name 		= 'Past Due.xlsx';
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
			$line 	= 1;
			$index 	= -1;
			$width = false;
			$heading = false;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Start Date';
			$width[]   = 15;
			$heading[] = 'Funds Requested';
			$width[]   = 15;
			$heading[] = 'Past Due';
			$width[]   = 15;
			$heading[] = 'Ledger';
			$width[]   = 20;
			$heading[] = 'Expected Payment Date';
			$width[]   = 20;
			$heading[] = 'Expected Payment Due';
			$width[]   = 20;
			$heading[] = 'Email';
			$width[]   = 20;
			$heading[] = 'Home Phone';
			$width[]   = 20;
			$heading[] = 'Mobile Phone';
			$width[]   = 20;

			$i = 0;
			foreach ($heading as $title) {
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				$i++;
			}

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			$res_ledger = $db->Execute("select PK_AR_LEDGER_CODE, CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");
			while (!$res_ledger->EOF) {
				$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

				$res = $db->Execute($query . " AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' " . $order_by);
				while (!$res->EOF) {

					// DIAM - 1276
					$Funds_Reqd = $res->fields['FUNDS_REQUIRED'];
					if ($Funds_Reqd != '' && $Funds_Reqd == 1) {
						$Funds_Reqd_Res = 'Y';
					} else {
						$Funds_Reqd_Res = 'N';
					}
					// End DIAM - 1276

					$line++;
					$index = -1;

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($Funds_Reqd_Res);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DAYS_PAST']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DISBURSEMENT_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DISBURSEMENT_AMOUNT']);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
						array(
							'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
						)
					);
					$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] += $res->fields['DISBURSEMENT_AMOUNT'];

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);

					$res->MoveNext();
				}

				$res_ledger->MoveNext();
			}

			// $objWriter->save($outputFileName);
			// $objPHPExcel->disconnectWorksheets();
			// header("location:" . $outputFileName);
		}
		$counter_index++;
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
			<th style="border: 1px solid black;text-align:right"><b>Total</b></th>
		</tr>
	</thead>
<tbody>';
foreach ($final_group_summary as $key => $value) {
	$txt .= 
	'<tr>
		<td style="border: 1px solid black;">'.$key.'</td>
		<td style="border: 1px solid black;text-align:right">$'.number_format_value_checker($value,2).'</td>
	</tr>';
	// dd($value ,number_format_value_checker($value ) );
}
$txt .= "</tbody></table> </td></tr></table>";

		$file_name = 'Past Due_' . uniqid() . '.pdf';
		$mpdf->WriteHTML($txt);
		$mpdf->Output($file_name, 'D');
		return $file_name;
	}
	else if($_POST['FORMAT'] == 2){
		
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
$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Total");
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
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode('#,###,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->applyFromArray(
		array(
			'code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
		)
	);
}
#normal code below 
foreach ($objPHPExcel->getWorksheetIterator() as $sheet) {
    // Get the highest column number (e.g., ZZ) and last row number for the current sheet
    $highestColumn = $sheet->getHighestColumn();
    $lastRow = $sheet->getHighestRow();

    // Set the horizontal alignment for the range A1:ZZ(last row)
    $sheet->getStyle('A1:' . $highestColumn . $lastRow)
          ->getAlignment()
          ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
}
$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:" . $outputFileName);
	}
}
