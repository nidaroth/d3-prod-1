<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");
ini_set("memory_limit","512M");
ini_set('max_execution_time', 600);
if (check_access('REPORT_FINANCE') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	if(isset($_REQUEST['PK_LEDGER_CODE_GROUP'])){
		include_once('projected_funds_by_code_group.php');
		exit;
	}
	// echo "<pre>";print_r($_REQUEST);exit;
	if(isset($_REQUEST['PK_LEDGER_CODE_GROUP'])){
		$imploded = implode(',',$_REQUEST['PK_LEDGER_CODE_GROUP']);
		$ar_ledger_codes = $db->Execute("SELECT GROUP_CONCAT(PK_AR_LEDGER_CODES) AS CONCATED_RES FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN ($imploded) ");
		$ar_ledger_codes = explode(',' , $ar_ledger_codes->fields['CONCATED_RES']);
		$ar_ledger_codes = array_unique($ar_ledger_codes);
		$_POST['PK_AR_LEDGER_CODE'] = $ar_ledger_codes;
		
	}
	if ($_REQUEST['REPORT_OPTION'] == '1') {
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

		/** DIAM - 601 **/
		$INCLUDE_ALL_LEADS = "No";
		if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
			$INCLUDE_ALL_LEADS = "Yes";
		}

		if (!empty($_POST['PK_STUDENT_STATUS'])) {
			//$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
			$sts = implode(",", $_POST['PK_STUDENT_STATUS']);
		} else {
			$sts = "";
			$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 0) - 13 June 2023 - DIAM-635
			while (!$res_type->EOF) {
				if ($sts != '')
					$sts .= ',';
				$sts .= $res_type->fields['PK_STUDENT_STATUS'];
				$res_type->MoveNext();
			}

			// if($sts != '')
			// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
		}

		if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
			$sts = "";
			$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 1) - 13 June 2023 - DIAM-635
			while (!$res_type->EOF) {
				if ($sts != '') {
					$sts .= ',';
				}
				$sts .= $res_type->fields['PK_STUDENT_STATUS'];
				$res_type->MoveNext();
			}
			// if($sts != '')
			// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
		}

		if (!empty($_POST['PK_STUDENT_STATUS']) || $_POST['INCLUDE_ALL_LEADS'] == 1) {
			$final_sts = implode(',', array_unique(explode(',', $sts)));
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $final_sts . ") ";
			//echo $cond;exit;
		}
		/** End DIAM - 601 **/

		$ledger_cond = "";
		if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
			$ledger_cond = " AND PK_AR_LEDGER_CODE in (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
		}

		if (!empty($_POST['PK_CAMPUS_PROGRAM'])) {
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (" . implode(",", $_POST['PK_CAMPUS_PROGRAM']) . ") ";
		}

		$campus_name  = "";
		$campus_cond  = "";
		$campus_cond1 = "";
		$campus_id	  = "";
		if (!empty($_POST['PK_CAMPUS'])) {
			$PK_CAMPUS 	  = implode(",", $_POST['PK_CAMPUS']);
			$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}

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
		$groupby = " GROUP BY PK_STUDENT_DISBURSEMENT"; //DIAM-2146
		$query = "SELECT STUDENT_ID , S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,CONCAT(LAST_NAME,', ',FIRST_NAME,' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d' )) AS DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, DISBURSEMENT_STATUS ,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, M_CAMPUS_PROGRAM.UNITS , IF(MIDPOINT_DATE = '0000-00-00','', DATE_FORMAT(MIDPOINT_DATE, '%Y-%m-%d' )) AS MIDPOINT_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE, STUDENT_STATUS , S_PAYMENT_BATCH_MASTER.BATCH_NO , M_BATCH_STATUS.BATCH_STATUS
		FROM  
		S_STUDENT_DISBURSEMENT 
		LEFT JOIN S_PAYMENT_BATCH_DETAIL ON
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL
		LEFT JOIN S_PAYMENT_BATCH_MASTER ON
			S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER
		LEFT JOIN M_BATCH_STATUS ON
			M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS
		LEFT JOIN M_AWARD_YEAR ON
			M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_DISBURSEMENT.PK_AWARD_YEAR
		LEFT JOIN M_DISBURSEMENT_STATUS ON M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT
		LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
		LEFT JOIN S_STUDENT_ACADEMICS on S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
		LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
		WHERE 
		S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  
		$campus_cond1
		AND M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS IN (2,3) ";

		// echo $query;exit;

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
						$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
					}

					$this->SetFont('helvetica', '', 15);
					$this->SetY(5);
					$this->SetX(55);
					$this->SetTextColor(000, 000, 000);
					$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

					$this->SetFont('helvetica', 'I', 20);
					$this->SetY(8);
					$this->SetX(185);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(104, 8, "Projected Funds", 0, false, 'R', 0, '', 0, false, 'M', 'L');


					$this->SetFillColor(0, 0, 0);
					$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
					$this->Line(180, 13, 290, 13, $style);

					$this->SetFont('helvetica', 'I', 8);
					$this->SetY(14);
					$this->SetX(140);
					$this->SetTextColor(000, 000, 000);
					//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
					$this->MultiCell(150, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);

					$str = "";
					if (empty($_POST['PK_STUDENT_STATUS'])) {
						$str = "All Student Statuses";
					} else {
						$str = "";
						$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");
						while (!$res_type->EOF) {
							if ($str != '')
								$str .= ',';
							$str .= $res_type->fields['STUDENT_STATUS'];
							$res_type->MoveNext();
						}
						$str = "Status(es): " . $str;
					}
					$str = substr($str, 0, 300);
					if (strlen($str) >= 300) {
						$str .= '...';
					}
					$this->SetY(18);
					$this->SetX(130);
					$this->SetTextColor(000, 000, 000);
					$this->MultiCell(160, 8, $str, 0, 'R', 0, 0, '', '', true);

					$str = "";
					if (empty($_POST['PK_AR_LEDGER_CODE'])) {
						$str = "All Ledger Codes";
					} else {
						$str = "";
						$res_type_all = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 ");

						$PK_AR_LEDGER_CODE_SELECTED = implode(",", $_POST['PK_AR_LEDGER_CODE']);
						$res_type = $db->Execute("select CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");

						if ($res_type_all->RecordCount() == $res_type->RecordCount())
							$str = "All Ledger Codes";
						else {
							while (!$res_type->EOF) {
								if ($str != '')
									$str .= ', ';
								$str .= $res_type->fields['CODE'];
								$res_type->MoveNext();
							}
							if ($str != '')
								$str = "Ledger Code(s): " . $str;
						}
					}
					$str = substr($str, 0, 95);
					if (strlen($str) >= 95) {
						$str .= '...';
					}
					$this->SetY(30);
					$this->SetX(140);
					$this->SetTextColor(000, 000, 000);
					$this->MultiCell(150, 15, $str, 0, 'R', 0, 0, '', '', true);

					$str = "";
					if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
						$str = " Between " . $_POST['START_DATE'] . ' and ' . $_POST['END_DATE'];
					else if ($_POST['START_DATE'] != '')
						$str = " from " . $_POST['START_DATE'];
					else if ($_POST['END_DATE'] != '')
						$str = " to " . $_POST['END_DATE'];

					$this->SetFont('helvetica', 'I', 10);
					$this->SetY(40);
					$this->SetX(140);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(150, 5, "Disbursement Date" . $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				}
				public function Footer()
				{
					global $db;

					$this->SetY(-15);
					$this->SetX(270);
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
			$pdf->SetMargins(7, 39, 7);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->SetAutoPageBreak(TRUE, 15);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->setLanguageArray($l);
			$pdf->setFontSubsetting(true);
			$pdf->SetFont('helvetica', '', 7, '', true);
			$pdf->AddPage();

			$total 	= 0;
			$txt 	= '';
			$summary_arr = [];
			$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");
			while (!$res_ledger->EOF) {
				$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

				$sub_total = 0;
				$res_disp = $db->Execute($query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' $groupby ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC "); //DIAM-2146

				if ($res_disp->RecordCount() > 0) {

					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead style="font-size:13px;">
								<tr>
									<td colspan="16" width="100%" ><h3><i>' . $res_ledger->fields['CODE'] . '</i></h3></td>
								</tr>
								<tr>
									<td colspan="10" width="79%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" ><b>Student</b></td>
									<td colspan="6" width="21%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" ><b>Disbursements</b></td>
								</tr>
								<tr>
									<td width="13%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Name</b></td>
									<td width="8%" style="border-bottom:1px solid #000;" ><b>Student ID</b></td>
									<td width="8%" style="border-bottom:1px solid #000;" ><b>SSN</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>Campus</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>First Term</b></td>
									<td width="10%" style="border-bottom:1px solid #000;" ><b>Program</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>Status</b></td>
									<td width="8%" style="border-bottom:1px solid #000;" ><b>Exp. Grad</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>Midpoint</b></td>
									<td width="5%" style="border-bottom:1px solid #000;" ><b>Units</b></td>
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Date</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="center" ><b>AY</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="center" ><b>AP</b></td>
									<td width="8%" align="right" style="border-bottom:1px solid #000;" ><b>Amount</b></td>
									<td width="3%" style="border-bottom:1px solid #000;" align="center" ><b>Status</b></td>
									<td width="3%" style="border-right:1px solid #000;border-bottom:1px solid #000;" align="center" ><b>Batch</b></td>
								</tr>
							</thead>
							<tbody  style="font-size:13px;">';

					while (!$res_disp->EOF) {
						$PK_STUDENT_ENROLLMENT = $res_disp->fields['PK_STUDENT_ENROLLMENT'];
						$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0  $campus_cond1 ");

						$SSN = $res_disp->fields['SSN'];
						if ($SSN != '') {
							$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'], $SSN);
							$SSN_ORG = $SSN;
							$SSN_ARR = explode("-", $SSN);
							$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
						}
						$txt 	.= '<tr>
										<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['NAME'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_disp->fields['STUDENT_ID'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $SSN_ORG . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_campus->fields['CAMPUS_CODE'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_disp->fields['BEGIN_DATE_1'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_disp->fields['PROGRAM_CODE'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_disp->fields['STUDENT_STATUS'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_disp->fields['EXPECTED_GRAD_DATE'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_disp->fields['MIDPOINT_DATE'] . '</td>
										<td style="border-bottom:1px solid #000;" >' . $res_disp->fields['UNITS'] . '</td>
										<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['DISBURSEMENT_DATE'] . '</td>
										<td style="border-bottom:1px solid #000;" align="center" >' . $res_disp->fields['ACADEMIC_YEAR'] . '</td>
										<td style="border-bottom:1px solid #000;" align="center" >' . $res_disp->fields['ACADEMIC_PERIOD'] . '</td>
										<td align="right" style="border-bottom:1px solid #000;" >$' . $res_disp->fields['DISBURSEMENT_AMOUNT'] . '</td>
										<td align="center" style="border-bottom:1px solid #000;" >' . $res_disp->fields['DISBURSEMENT_STATUS'] . '</td>
										<td align="center" style="border-right:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['BATCH_NO'] . '</td>
									</tr>';

						$sub_total += $res_disp->fields['DISBURSEMENT_AMOUNT'];
						$res_disp->MoveNext();
					}



					$total += $sub_total;
					$txt 	.= '<tr>
								<td colspan="8" style="border-left:1px solid #000;border-bottom:1px solid #000;" ></td>
								<td colspan="8" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;font-size:15px;font-weight:bold;" align="right" >' . $res_ledger->fields['CODE'] . '&nbsp;&nbsp;&nbsp; $' . number_format_value_checker($sub_total, 2) . '</td>
							</tr>
						  </tbody>
						</table>';
					$summary_arr[$res_ledger->fields['CODE']] = $sub_total;
				}
				// $summary_arr[$res_ledger->fields['CODE']] = number_format_value_checker($sub_total, 2);
				$res_ledger->MoveNext();
			}

			$txt 	.= '<br /><br /><br /><br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" align="right" style="font-size:22px;" align="right" ><i>Report Total $' . number_format_value_checker($total, 2) . '</i></td>
						</tr>
					</table>';

			$border_bottom = ' border-bottom:1px solid #000; ';
			$border_left = ' border-left:1px solid #000; ';
			$border_right = ' border-right:1px solid #000; ';
			$border_top = ' border-top:1px solid #000; ';
			$border_all = $border_bottom . $border_left . $border_right . $border_top;
			// print_r($summary_arr);exit;
			if (!empty($summary_arr)) {
				// $txt .= ' <h1>Summary</h1> ';
				// $txt .= '<table border="0" cellspacing="0" cellpadding="3" width="40%">';

				// $txt .= '<tr><th style="' . $border_all . '"><h3>Ledger</h3></th><th style="' . $border_all . '"><h3>Amount</h3></th></tr>';
				// $tmp_total = 0;
				// foreach ($summary_arr as $LedgerCODE => $codeamount) {
				// 	$txt .=  '<tr><td  style=" ' . $border_all . '">' . $LedgerCODE . '</td><td  style=" ' . $border_all . '">$ ' . number_format_value_checker($codeamount, 2) . '</td></tr>';
				// 	$tmp_total = $tmp_total + $codeamount;
				// }
				// $txt .= '<tr><td style="' . $border_all . '"><b>Total</b></td><td style="' . $border_all . '"style="' . $border_all . '"><b>$ ' . number_format_value_checker($tmp_total, 2) . '</b></td></tr>';
				// $txt .= '</table>';
			}


			$fp_test = fopen('temp/test_13405.html', 'w');
			fwrite($fp_test, $txt);

			// echo $txt;exit;

			//$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

			$file_name = 'Projected_Funds.pdf';
			$file_name = str_replace(
				pathinfo($file_name, PATHINFO_FILENAME),
				pathinfo($file_name, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
				$file_name
			);
			/*if($browser == 'Safari')
				$pdf->Output('temp/'.$file_name, 'FD');
			else	
				$pdf->Output($file_name, 'I');*/

			// $pdf->Output('temp/' . $file_name, 'FD');
			// return $file_name;
			
			$str = "";
			if (empty($_POST['PK_STUDENT_STATUS'])) {
				$str = "All Student Statuses";
			} else {
				$str = "";
				$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");
				while (!$res_type->EOF) {
					if ($str != '')
						$str .= ',';
					$str .= $res_type->fields['STUDENT_STATUS'];
					$res_type->MoveNext();
				}
				$str = "Status(es): " . $str;
			}
			$str = substr($str, 0, 100);
			if (strlen($str) >= 100) {
				$str .= '...';
			}

			$str_ledger_code = "";
			if (empty($_POST['PK_AR_LEDGER_CODE'])) {
				$str_ledger_code = "All Ledger Codes";
			} else {
				$str_ledger_code = "";
				$res_type_all = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 ");

				$PK_AR_LEDGER_CODE_SELECTED = implode(",", $_POST['PK_AR_LEDGER_CODE']);
				$res_type = $db->Execute("select CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");

				if ($res_type_all->RecordCount() == $res_type->RecordCount())
					$str_ledger_code = "All Ledger Codes";
				else {
					while (!$res_type->EOF) {
						if ($str_ledger_code != '')
							$str_ledger_code .= ', ';
						$str_ledger_code .= $res_type->fields['CODE'];
						$res_type->MoveNext();
					}
					if ($str_ledger_code != '')
						$str_ledger_code = "Ledger Code(s): " . $str_ledger_code;
				}
			}
			$str_ledger_code = substr($str_ledger_code, 0, 95);
			if (strlen($str_ledger_code) >= 95) {
				$str_ledger_code .= '...';
			}

			$str_disb_date = "";
			if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
				$str_disb_date = " Between " . $_POST['START_DATE'] . ' and ' . $_POST['END_DATE'];
			else if ($_POST['START_DATE'] != '')
				$str_disb_date = " from " . $_POST['START_DATE'];
			else if ($_POST['END_DATE'] != '')
				$str_disb_date = " to " . $_POST['END_DATE'];

			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 	= $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != ''){
				//$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				$PDF_LOGO=str_replace('../',$http_path,$PDF_LOGO);
				$logo = '<img src="'.$PDF_LOGO.'" width="100px" />';
			}

			$header = '<table width="100%" >
						<tr>
							<td width="15%" valign="top" >'.$logo.'</td>
							<td width="45%" valign="top" style="font-size:25px;font-family: helvetica;padding-top:20px;" >'.$SCHOOL_NAME.'</td>
							<td width="40%" valign="top" >
								<table width="100%" >
									<tr>
										<td align="right" style="font-size:28px;border-bottom:1px solid #000;font-family: helvetica;font-style: italic;" >Projected Funds</td>
									</tr>
									<tr>
										<td align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Campus(es): '.$campus_name.'</td>
									</tr>
									<tr>
										<td align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >'.$str.'</td>
									</tr>
									<tr>
										<td align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >'.$str_ledger_code.'</td>
									</tr>
									<tr>
										<td align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Disbursement Date '.$str_disb_date.'</td>
									</tr>
								</table>
							</td>
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
			$date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());
			
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date_footer.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
								<td></td>							
							</tr>
						</table>';

			$header_cont= '<!DOCTYPE HTML>
			<html>
			<head>
			<style>
			div{ padding-bottom:20px !important; }	
			</style>
			</head>
			<body>
			<div> '.$header.' </div>
			</body>
			</html>';

			$html_body_cont = '<!DOCTYPE HTML>
			<html>
			<head> <style>
			table{  margin-top: 2px; }
			table tr{  padding-top: 1px !important; }
			</style>
			</head>
			<body>'.$txt.'</body></html>';
			//echo $html_body_cont;exit;

			$footer_cont= '<!DOCTYPE HTML><html><head><style>
			tbody td{ font-size:14px !important; }
			</style></head><body>'.$footer.'</body></html>';

			$header_path= create_html_file('header_projected_funds.html',$header_cont);
			$content_path=create_html_file('content_projected_funds.html',$html_body_cont);
			$footer_path= create_html_file('footer_projected_funds.html',$footer_cont);

			$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation Landscape --page-size A4 --page-width 210  --page-height 297 --margin-top 50mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/'.$file_name.' 2>&1';
					
			$pdfdata =array('filepath'=>'temp/'.$file_name,'exec'=>$exec,'filename'=>$file_name,'filefullpath'=>$http_path.'/school/temp/'.$file_name);		
			//print_r($pdfdata);
			sleep(2);
			exec($pdfdata['exec']);	
			header('Content-Type: Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
			readfile($pdfdata['filepath']);

			unlink('../school/temp/header_projected_funds.html');
			unlink('../school/temp/content_projected_funds.html');
			unlink('../school/temp/footer_projected_funds.html');

			exit;	

			/////////////////////////////////////////////////////////////////
		} else if ($_POST['FORMAT'] == 2) {

			include '../global/excel/Classes/PHPExcel/IOFactory.php';
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
			$file_name 		= 'Projected Funds.xlsx';
			$outputFileName = $dir . $file_name;
			$outputFileName = str_replace(
				pathinfo($outputFileName, PATHINFO_FILENAME),
				pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
				$outputFileName
			);

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line++;
			$index 	= -1;
			$heading[] = 'Ledger Code';
			$width[]   = 20;
			$heading[] = 'Name';
			$width[]   = 15;
			$heading[] = 'Student ID';
			$width[]   = 23;
			$heading[] = 'SSN';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 15;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 15;
			$heading[] = 'Exp. Grad';
			$width[]   = 15;
			$heading[] = 'Midpoint';
			$width[]   = 15;
			$heading[] = 'Units';
			$width[]   = 15;
			$heading[] = 'Date';
			$width[]   = 15;
			$heading[] = 'AY';
			$width[]   = 15;
			$heading[] = 'AP';
			$width[]   = 15;
			$heading[] = 'Amount';
			$width[]   = 15;
			$heading[] = 'Disbursement Status';
			$width[]   = 15;
			$heading[] = 'Batch';
			$width[]   = 15;

			$i = 0;
			foreach ($heading as $title) {
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);

				$i++;
			}

			$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");
			while (!$res_ledger->EOF) {
				$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

				$sub_total = 0;
				$res_disp = $db->Execute($query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' $groupby ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC "); //DIAM-2146

				while (!$res_disp->EOF) {

					$SSN = $res_disp->fields['SSN'];
					if ($SSN != '') {
						$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'], $SSN);
						$SSN_ORG = $SSN;
						$SSN_ARR = explode("-", $SSN);
						$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
					}

					$PK_STUDENT_ENROLLMENT = $res_disp->fields['PK_STUDENT_ENROLLMENT'];

					$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ");

					$line++;
					$index = -1;

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['NAME']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res_disp->fields['STUDENT_ID']));


					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN_ORG);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['BEGIN_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['PROGRAM_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['STUDENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['EXPECTED_GRAD_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['MIDPOINT_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['UNITS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_YEAR']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_PERIOD']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_AMOUNT']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['BATCH_NO']);

					$res_disp->MoveNext();
				}
				$res_ledger->MoveNext();
			}
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
	if ($_REQUEST['REPORT_OPTION'] == '2') {
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

		/** DIAM - 601 **/
		$INCLUDE_ALL_LEADS = "No";
		if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
			$INCLUDE_ALL_LEADS = "Yes";
		}

		if (!empty($_POST['PK_STUDENT_STATUS'])) {
			//$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
			$sts = implode(",", $_POST['PK_STUDENT_STATUS']);
		} else {
			$sts = "";
			$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 0) - 13 June 2023 - DIAM-635
			while (!$res_type->EOF) {
				if ($sts != '')
					$sts .= ',';
				$sts .= $res_type->fields['PK_STUDENT_STATUS'];
				$res_type->MoveNext();
			}

			// if($sts != '')
			// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
		}

		if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
			$sts = "";
			$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 1) - 13 June 2023 - DIAM-635
			while (!$res_type->EOF) {
				if ($sts != '') {
					$sts .= ',';
				}
				$sts .= $res_type->fields['PK_STUDENT_STATUS'];
				$res_type->MoveNext();
			}
			// if($sts != '')
			// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
		}

		if (!empty($_POST['PK_STUDENT_STATUS']) || $_POST['INCLUDE_ALL_LEADS'] == 1) {
			$final_sts = implode(',', array_unique(explode(',', $sts)));
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $final_sts . ") ";
			//echo $cond;exit;
		}
		/** End DIAM - 601 **/

		$ledger_cond = "";
		if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
			$ledger_cond = " AND PK_AR_LEDGER_CODE in (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
		}

		if (!empty($_POST['PK_CAMPUS_PROGRAM'])) {
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (" . implode(",", $_POST['PK_CAMPUS_PROGRAM']) . ") ";
		}

		$campus_name  = "";
		$campus_cond  = "";
		$campus_cond1 = "";
		$campus_id	  = "";
		if (!empty($_POST['PK_CAMPUS'])) {
			$PK_CAMPUS 	  = implode(",", $_POST['PK_CAMPUS']);
			$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}

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

		$query = "select M_ENROLLMENT_STATUS.DESCRIPTION ,S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL,S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL as D2,STUDENT_ID , S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,CONCAT(LAST_NAME,', ',FIRST_NAME,' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d' )) AS DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, DISBURSEMENT_STATUS ,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, M_CAMPUS_PROGRAM.UNITS , IF(MIDPOINT_DATE = '0000-00-00','', DATE_FORMAT(MIDPOINT_DATE, '%Y-%m-%d' )) AS MIDPOINT_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE, STUDENT_STATUS , S_PAYMENT_BATCH_MASTER.BATCH_NO , M_BATCH_STATUS.BATCH_STATUS
		FROM  
		S_STUDENT_DISBURSEMENT 
		LEFT JOIN S_PAYMENT_BATCH_DETAIL ON
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL
		LEFT JOIN S_PAYMENT_BATCH_MASTER ON
			S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER
		LEFT JOIN M_BATCH_STATUS ON
			M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS
		LEFT JOIN M_AWARD_YEAR ON
			M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_DISBURSEMENT.PK_AWARD_YEAR
		LEFT JOIN M_DISBURSEMENT_STATUS ON M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS,  
		S_STUDENT_MASTER, S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_ACADEMICS on S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
		LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
		LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS
		WHERE 
		S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($campus_id) ) AND 
		S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = 2 ";

		// echo $query;exit;

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
						$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
					}

					$this->SetFont('helvetica', '', 15);
					$this->SetY(5);
					$this->SetX(55);
					$this->SetTextColor(000, 000, 000);
					$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

					$this->SetFont('helvetica', 'I', 20);
					$this->SetY(8);
					$this->SetX(185);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(104, 8, "Projected Funds - Attendance", 0, false, 'R', 0, '', 0, false, 'M', 'L');


					$this->SetFillColor(0, 0, 0);
					$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
					$this->Line(180, 13, 290, 13, $style);

					$this->SetFont('helvetica', 'I', 8);
					$this->SetY(14);
					$this->SetX(140);
					$this->SetTextColor(000, 000, 000);
					//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
					$this->MultiCell(150, 5, "Campus(es): " . $campus_name, 0, 'R', 0, 0, '', '', true);

					$str = "";
					if (empty($_POST['PK_STUDENT_STATUS'])) {
						$str = "All Student Statuses";
					} else {
						$str = "";
						$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");
						while (!$res_type->EOF) {
							if ($str != '')
								$str .= ',';
							$str .= $res_type->fields['STUDENT_STATUS'];
							$res_type->MoveNext();
						}
						$str = "Status(es): " . $str;
					}

					$str = substr($str, 0, 300);
					if (strlen($str) >= 300) {
						$str .= '...';
					}
					$this->SetY(18);
					$this->SetX(130);
					$this->SetTextColor(000, 000, 000);
					$this->MultiCell(160, 8, $str, 0, 'R', 0, 0, '', '', true);

					$str = "";
					if (empty($_POST['PK_AR_LEDGER_CODE'])) {
						$str = "All Ledger Codes";
					} else {
						$str = "";
						$res_type_all = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 ");

						$PK_AR_LEDGER_CODE_SELECTED = implode(",", $_POST['PK_AR_LEDGER_CODE']);
						$res_type = $db->Execute("select CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");

						if ($res_type_all->RecordCount() == $res_type->RecordCount())
							$str = "All Ledger Codes";
						else {
							while (!$res_type->EOF) {
								if ($str != '')
									$str .= ', ';
								$str .= $res_type->fields['CODE'];
								$res_type->MoveNext();
							}
							if ($str != '')
								$str = "Ledger Code(s): " . $str;
						}
					}
					$str = substr($str, 0, 95);
					if (strlen($str) >= 95) {
						$str .= '...';
					}
					$this->SetY(30);
					$this->SetX(140);
					$this->SetTextColor(000, 000, 000);
					$this->MultiCell(150, 15, $str, 0, 'R', 0, 0, '', '', true);

					$str = "";
					if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
						$str = " Between " . $_POST['START_DATE'] . ' and ' . $_POST['END_DATE'];
					else if ($_POST['START_DATE'] != '')
						$str = " from " . $_POST['START_DATE'];
					else if ($_POST['END_DATE'] != '')
						$str = " to " . $_POST['END_DATE'];

					$this->SetFont('helvetica', 'I', 10);
					$this->SetY(40);
					$this->SetX(140);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(150, 5, "Disbursement Date" . $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				}
				public function Footer()
				{
					global $db;

					$this->SetY(-15);
					$this->SetX(270);
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
			$pdf->SetMargins(7, 39, 7);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->SetAutoPageBreak(TRUE, 15);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->setLanguageArray($l);
			$pdf->setFontSubsetting(true);
			$pdf->SetFont('helvetica', '', 7, '', true);
			$pdf->AddPage();

			$total 	= 0;
			$txt 	= '';
			$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");


			///PREREQUISIT FOR ATTENDANCE 
			$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
			$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

			$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
			$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

			$excluded_att_code  = "";
			$exc_att_code_arr = array();
			$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
			while (!$res_exc_att_code->EOF) {
				$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
				$res_exc_att_code->MoveNext();
			}

			$exclude_cond  = "";
			if (!empty($exc_att_code_arr))
				$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (" . implode(",", $exc_att_code_arr) . ") ";
			//END OF PREQ
			$summary_arr = [];
			while (!$res_ledger->EOF) {
				$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

				$sub_total = 0;
				$res_disp = $db->Execute($query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC ");

				if ($res_disp->RecordCount() > 0) {

					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="100%" ><h1><i>' . $res_ledger->fields['CODE'] . '</i></h1></td>
								</tr>
								<tr>
									<td width="71%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" ><b>Student</b></td>
									<td width="17%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" ><b>Disbursements</b></td>
									<td width="12%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" ><b>Attendance</b></td>
								</tr>
								<tr>
									<td width="13%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Name</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>Student ID</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>SSN</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>Campus</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>First Term</b></td>
									<td width="10%" style="border-bottom:1px solid #000;" ><b>Program</b></td>
									<td width="5%" style="border-bottom:1px solid #000;" ><b>Status</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>Exp. Grad</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>Midpoint</b></td>
									<td width="5%" style="border-bottom:1px solid #000;" ><b>Units</b></td>
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Date</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AY</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AP</b></td>
									<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Amount</b></td>
									<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Scheduled</b></td>
									<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Attended</b></td>
								</tr>
							</thead>';

					while (!$res_disp->EOF) {
						$PK_STUDENT_ENROLLMENT = $res_disp->fields['PK_STUDENT_ENROLLMENT'];
						$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0  $campus_cond1 ");

						$SSN = $res_disp->fields['SSN'];
						if ($SSN != '') {
							$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'], $SSN);
							$SSN_ORG = $SSN;
							$SSN_ARR = explode("-", $SSN);
							$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
						}

						#get attendance code & etc 
						///START OF ATTENADANCE SQLS


						$stud_cond 	= " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
						$tc_cond	= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";

						$complete_cond = " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1  ";
						$att_com_cond  = " AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ";


						$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond"); //Ticket # 1247

						$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond "); //Ticket # 1247

						$cond1 = "";
						//AV - disabled for this report , show total time till now instead of inside a sate rnage
						// if($_GET['date'] != '')
						// 	$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";



						$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'];

						$SCHEDULED_HOURS 	 = 0;
						$COMP_SCHEDULED_HOUR = 0;
						$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1"); //Ticket # 1247
						while (!$res_sch->EOF) {
							$exc_att_flag = 0;
							foreach ($exc_att_code_arr as $exc_att_code) {
								if ($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
									$exc_att_flag = 1;
									break;
								}
							}

							/* Ticket # 1247 */
							if ($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0) {
								if ($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2 || $_GET['incomplete'] == 1) {
									$SCHEDULED_HOURS	 += $res_sch->fields['HOURS'];
									$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];
								}
							}
							/* Ticket # 1247 */

							$res_sch->MoveNext();
						}

						/////////END OF ATTENDANCE SQLS

						$txt 	.= '<tr>
								<td width="13%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['NAME'] . '</td>
								<td width="7%" style="border-bottom:1px solid #000;" >' . $res_disp->fields['STUDENT_ID'] . '</td>
								<td width="6%" style="border-bottom:1px solid #000;" >' . $SSN_ORG . '</td>
								<td width="6%" style="border-bottom:1px solid #000;" >' . $res_campus->fields['CAMPUS_CODE'] . '</td>
								<td width="6%" style="border-bottom:1px solid #000;" >' . $res_disp->fields['BEGIN_DATE_1'] . '</td>
								<td width="10%" style="border-bottom:1px solid #000;" >' . $res_disp->fields['PROGRAM_CODE'] . '</td>
								<td width="5%" style="border-bottom:1px solid #000;" >' . $res_disp->fields['STUDENT_STATUS'] . '</td>
								<td width="6%" style="border-bottom:1px solid #000;" >' . $res_disp->fields['EXPECTED_GRAD_DATE'] . '</td>
								<td width="7%" style="border-bottom:1px solid #000;" >' . $res_disp->fields['MIDPOINT_DATE'] . '</td>
								<td width="5%" style="border-bottom:1px solid #000;" >' . $res_disp->fields['UNITS'] . '</td>
								
								<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['DISBURSEMENT_DATE'] . '</td>
								<td width="2%" style="border-bottom:1px solid #000;" align="right" >' . $res_disp->fields['ACADEMIC_YEAR'] . '</td>
								<td width="2%" style="border-bottom:1px solid #000;" align="right" >' . $res_disp->fields['ACADEMIC_PERIOD'] . '</td>
								<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" >$ ' . $res_disp->fields['DISBURSEMENT_AMOUNT'] . '</td>
								<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" >' . number_format_value_checker($SCHEDULED_HOURS, 2) . '</td>
								<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" >' . number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']), 2) . '</td>
							</tr>';

						$sub_total += $res_disp->fields['DISBURSEMENT_AMOUNT'];
						$res_disp->MoveNext();
					}

					$total += $sub_total;
					$txt 	.= '<tr>
								<td width="60%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ></td>
								<td width="40%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;font-size:35px;" align="right" >' . $res_ledger->fields['CODE'] . '&nbsp;&nbsp;&nbsp; $ ' . number_format_value_checker($sub_total, 2) . '</td>
							</tr>
						</table>';
					$summary_arr[$res_ledger->fields['CODE']] = $sub_total;
				}
				$res_ledger->MoveNext();
			}

			$txt 	.= '<br /><br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" align="right" style="font-size:45px;" align="right" ><i>Report Total $ ' . number_format_value_checker($total, 2) . '</i></td>
						</tr>
					</table>';



			$border_bottom = ' border-bottom:1px solid #000; ';
			$border_left = ' border-left:1px solid #000; ';
			$border_right = ' border-right:1px solid #000; ';
			$border_top = ' border-top:1px solid #000; ';
			$border_all = $border_bottom . $border_left . $border_right . $border_top;
			// print_r($summary_arr);exit;
			if (!empty($summary_arr)) {
				$txt .= '  ';
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="40%">';

				$txt .= '<thead>
				<tr>
				<th><h1>Summary</h1></th>
				<th><h1>Report</h1></th>
				</tr>
				<tr>
				<th style="' . $border_all . '"><h3>Ledger</h3></th>
				<th style="' . $border_all . '"><h3>Amount</h3></th>
				</tr>
				</thead>';
				$tmp_total = 0;
				foreach ($summary_arr as $LedgerCODE => $codeamount) {
					$txt .=  '<tr><td  style=" ' . $border_all . '">' . $LedgerCODE . '</td><td  style=" ' . $border_all . '">$ ' . number_format_value_checker($codeamount, 2) . '</td></tr>';
					$tmp_total = $tmp_total + $codeamount;
				}
				$txt .= '<tr><td style="' . $border_all . '"><b>Total</b></td><td style="' . $border_all . '"style="' . $border_all . '"><b>$ ' . number_format_value_checker($tmp_total, 2) . '</b></td></tr>';
				$txt .= '</table>';
			}
			$fp_test = fopen('temp/test_13405.html', 'w');
			fwrite($fp_test, $txt);

			// echo $txt;exit;

			$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

			$file_name = 'Projected Funds Attendance.pdf';
			$file_name = str_replace(
				pathinfo($file_name, PATHINFO_FILENAME),
				pathinfo($file_name, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
				$file_name
			);
			/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/

			$pdf->Output('temp/' . $file_name, 'FD');
			return $file_name;
			/////////////////////////////////////////////////////////////////
		} else if ($_POST['FORMAT'] == 2) {


			///PREREQUISIT FOR ATTENDANCE 
			$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
			$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

			$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
			$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

			$excluded_att_code  = "";
			$exc_att_code_arr = array();
			$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
			while (!$res_exc_att_code->EOF) {
				$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
				$res_exc_att_code->MoveNext();
			}

			$exclude_cond  = "";
			if (!empty($exc_att_code_arr))
				$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (" . implode(",", $exc_att_code_arr) . ") ";
			//END OF PREQ
			include '../global/excel/Classes/PHPExcel/IOFactory.php';
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
			$file_name 		= 'Projected Funds Attendance.xlsx';
			$outputFileName = $dir . $file_name;
			$outputFileName = str_replace(
				pathinfo($outputFileName, PATHINFO_FILENAME),
				pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
				$outputFileName
			);

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line++;
			$index 	= -1;
			$heading[] = 'Ledger Code';
			$width[]   = 20;
			$heading[] = 'Name';
			$width[]   = 15;
			$heading[] = 'Student ID';
			$width[]   = 23;
			$heading[] = 'SSN';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 15;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 15;
			$heading[] = 'Exp. Grad';
			$width[]   = 15;
			$heading[] = 'Midpoint';
			$width[]   = 15;

			$heading[] = 'FT/PT Status';
			$width[]   = 15;
			$heading[] = 'Disbursement Date';
			$width[]   = 15;
			$heading[] = 'Disbursement Amount';
			$width[]   = 15;
			$heading[] = 'Disbursement Status';
			$width[]   = 15;
			$heading[] = 'AY';
			$width[]   = 15;
			$heading[] = 'AP';
			$width[]   = 15;
			$heading[] = 'Award Year';
			$width[]   = 15;
			$heading[] = 'Funds Requested';
			$width[]   = 15;
			$heading[] = 'Hours Scheduled';
			$width[]   = 15;
			$heading[] = 'Hours Attended';
			$width[]   = 15;
			$heading[] = 'Funds Requested';
			$width[]   = 15;
			$heading[] = 'Batch Number';
			$width[]   = 15;
			$heading[] = 'Batch Status';
			$width[]   = 15;





			// $heading[] = 'Units';
			// $width[]   = 15;
			// $heading[] = 'Date';
			// $width[]   = 15;
			// $heading[] = 'AY';
			// $width[]   = 15;
			// $heading[] = 'AP';
			// $width[]   = 15;
			// $heading[] = 'Amount';
			// $width[]   = 15;
			// $heading[] = 'Hours Scheduled';
			// $width[]   = 15;
			// $heading[] = 'Hours Attended';
			// $width[]   = 15;





			$i = 0;
			foreach ($heading as $title) {
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);

				$i++;
			}

			$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");
			while (!$res_ledger->EOF) {




				$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

				$sub_total = 0;
				$res_disp = $db->Execute($query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC ");
				// echo $query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC ";exit;
				while (!$res_disp->EOF) {

					$SSN = $res_disp->fields['SSN'];
					if ($SSN != '') {
						$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'], $SSN);
						$SSN_ORG = $SSN;
						$SSN_ARR = explode("-", $SSN);
						$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
					}

					$PK_STUDENT_ENROLLMENT = $res_disp->fields['PK_STUDENT_ENROLLMENT'];

					$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ");


					///START OF ATTENADANCE SQLS


					$stud_cond 	= " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
					$tc_cond	= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";

					$complete_cond = " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1  ";
					$att_com_cond  = " AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ";


					$res_att = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  $att_com_cond $stud_cond"); //Ticket # 1247

					$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $stud_cond "); //Ticket # 1247

					$cond1 = "";
					//AV - disabled for this report , show total time till now instead of inside a sate rnage
					// if($_GET['date'] != '')
					// 	$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";



					$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'];

					$SCHEDULED_HOURS 	 = 0;
					$COMP_SCHEDULED_HOUR = 0;
					$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond  $cond1"); //Ticket # 1247
					while (!$res_sch->EOF) {
						$exc_att_flag = 0;
						foreach ($exc_att_code_arr as $exc_att_code) {
							if ($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
								$exc_att_flag = 1;
								break;
							}
						}

						/* Ticket # 1247 */
						if ($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0) {
							if ($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2 || $_GET['incomplete'] == 1) {
								$SCHEDULED_HOURS	 += $res_sch->fields['HOURS'];
								$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];
							}
						}
						/* Ticket # 1247 */

						$res_sch->MoveNext();
					}

					/////////END OF ATTENDANCE SQLS


					$line++;
					$index = -1;


					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['PK_STUDENT_DISBURSEMENT']);

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['PK_PAYMENT_BATCH_DETAIL']);

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['D2']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['NAME']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(trim($res_disp->fields['STUDENT_ID']));


					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN_ORG);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['BEGIN_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['PROGRAM_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['STUDENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['EXPECTED_GRAD_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['MIDPOINT_DATE']);

					//new fields 1308
					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DESCRIPTION']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_AMOUNT']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_YEAR']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_PERIOD']);



					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['AWARD_YEAR']);

					if ($res_disp->fields['AWARD_YEAR'] == '1') {
						$funds_requested = 'Yes';
					} else if ($res_disp->fields['AWARD_YEAR'] == '2') {
						$funds_requested = 'No';
					}
					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($funds_requested);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($SCHEDULED_HOURS, 2));

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']), 2));

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['BATCH_NO']);
					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['BATCH_STATUS']);
					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['UNITS']);

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_DATE']);

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_YEAR']);

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_PERIOD']);

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_AMOUNT']);

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($SCHEDULED_HOURS, 2));

					// $index++;
					// $cell_no = $cell[$index] . $line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker(($res_att->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']), 2));





					$res_disp->MoveNext();
				}
				$res_ledger->MoveNext();
			}
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
	}else if($_REQUEST['REPORT_OPTION'] == '4'){
		//DIAM-1763
		include('projected_funds_by_hours.php');
	}

	// DIAM-1666
	else if ($_REQUEST['REPORT_OPTION'] == '3') {
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

		$INCLUDE_ALL_LEADS = "No";
		if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
			$INCLUDE_ALL_LEADS = "Yes";
		}

		if (!empty($_POST['PK_STUDENT_STATUS'])) {
			//$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
			$sts = implode(",", $_POST['PK_STUDENT_STATUS']);
		} else {
			$sts = "";
			$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); 
			while (!$res_type->EOF) {
				if ($sts != '')
					$sts .= ',';
				$sts .= $res_type->fields['PK_STUDENT_STATUS'];
				$res_type->MoveNext();
			}

			// if($sts != '')
			// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
		}

		if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
			$sts = "";
			$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC");
			while (!$res_type->EOF) {
				if ($sts != '') {
					$sts .= ',';
				}
				$sts .= $res_type->fields['PK_STUDENT_STATUS'];
				$res_type->MoveNext();
			}
			// if($sts != '')
			// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
		}

		if (!empty($_POST['PK_STUDENT_STATUS']) || $_POST['INCLUDE_ALL_LEADS'] == 1) {
			$final_sts = implode(',', array_unique(explode(',', $sts)));
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $final_sts . ") ";
			//echo $cond;exit;
		}

		$ledger_cond = "";
		if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
			$ledger_cond = " AND PK_AR_LEDGER_CODE in (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
		}

		if (!empty($_POST['PK_CAMPUS_PROGRAM'])) {
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (" . implode(",", $_POST['PK_CAMPUS_PROGRAM']) . ") ";
		}

		$campus_name  = "";
		$campus_cond  = "";
		$campus_cond1 = "";
		$campus_id	  = "";
		if (!empty($_POST['PK_CAMPUS'])) {
			$PK_CAMPUS 	  = implode(",", $_POST['PK_CAMPUS']);
			$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}

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

		$query = "SELECT STUDENT_ID , S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,CONCAT(LAST_NAME,', ',FIRST_NAME,' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d' )) AS DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, DISBURSEMENT_STATUS ,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, M_CAMPUS_PROGRAM.UNITS , IF(MIDPOINT_DATE = '0000-00-00','', DATE_FORMAT(MIDPOINT_DATE, '%Y-%m-%d' )) AS MIDPOINT_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE, STUDENT_STATUS , S_PAYMENT_BATCH_MASTER.BATCH_NO , M_BATCH_STATUS.BATCH_STATUS
		FROM  
		S_STUDENT_DISBURSEMENT 
		LEFT JOIN S_PAYMENT_BATCH_DETAIL ON
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL
		LEFT JOIN S_PAYMENT_BATCH_MASTER ON
			S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER
		LEFT JOIN M_BATCH_STATUS ON
			M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS
		LEFT JOIN M_AWARD_YEAR ON
			M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_DISBURSEMENT.PK_AWARD_YEAR
		LEFT JOIN M_DISBURSEMENT_STATUS ON M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT
		LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
		LEFT JOIN S_STUDENT_ACADEMICS on S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
		LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
		WHERE 
		S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
		$campus_cond1 
		AND M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS IN (2,3) ";
		// echo $query;exit;

		if ($_POST['FORMAT'] == 1) {
		
			$browser = '';
			if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
				$browser =  "chrome";
			else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
				$browser = "Safari";
			else
				$browser = "firefox";

			$total 	= 0;
			$txt 	= '';
			$summary_arr = [];
			$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");
			$txt .= '<table border="0" cellspacing="0" cellpadding="8" width="100%">
						<tr>
							<td width="30%"></td>
							<td colspan="2" align="right" style="font-size:18px;" ><i>All Student Status</i></td>
						</tr>';
				while (!$res_ledger->EOF) {
					$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

					$sub_total = 0;
					$res_disp = $db->Execute($query . " $cond AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, STUDENT_ID ASC,DISBURSEMENT_DATE ASC ");

					if ($res_disp->RecordCount() > 0) {

						while (!$res_disp->EOF) {
								
							$sub_total += $res_disp->fields['DISBURSEMENT_AMOUNT'];
							$res_disp->MoveNext();
						}
						$total += $sub_total;
													
						$txt .= '<tr>
									<td width="60%"></td>
									<td width="20%" style="font-size:15px;border-top:1px solid #000;" align="left" >' . $res_ledger->fields['CODE'] . '</td>
									<td width="20%" style="font-size:15px;border-top:1px solid #000;" align="right" >$ ' . number_format_value_checker($sub_total, 2) . '</td>
								</tr>';
					}
					$res_ledger->MoveNext();
				}
			$txt .= '</table>';

			$txt .= '<br /><br />
					<table border="0" cellspacing="0" cellpadding="8" width="100%">
						<tr>
							<td width="20%" align="left" style="border-top:1px solid #000;font-size:20px;" ><i>Report Total</i></td>
							<td width="30%" align="right" style="border-top:1px solid #000;font-size:20px;" ><i>$' . number_format_value_checker($total, 2) . '</i></td>
							<td width="50%"></td>
						</tr>
					</table>';

			// echo $txt;exit;

			$file_name = 'Projected_Funds_Summary.pdf';
			$file_name = str_replace(
				pathinfo($file_name, PATHINFO_FILENAME),
				pathinfo($file_name, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
				$file_name
			);

			$str = "";
			if (empty($_POST['PK_STUDENT_STATUS'])) {
				$str = "All Student Statuses";
			} else {
				$str = "";
				$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");
				while (!$res_type->EOF) {
					if ($str != '')
						$str .= ',';
					$str .= $res_type->fields['STUDENT_STATUS'];
					$res_type->MoveNext();
				}
				$str = "Status(es): " . $str;
			}
			$str = substr($str, 0, 100);
			if (strlen($str) >= 100) {
				$str .= '...';
			}

			$str_ledger_code = "";
			if (empty($_POST['PK_AR_LEDGER_CODE'])) {
				$str_ledger_code = "All Ledger Codes";
			} else {
				$str_ledger_code = "";
				$res_type_all = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 ");

				$PK_AR_LEDGER_CODE_SELECTED = implode(",", $_POST['PK_AR_LEDGER_CODE']);
				$res_type = $db->Execute("select CODE from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE_SELECTED) ");

				if ($res_type_all->RecordCount() == $res_type->RecordCount())
					$str_ledger_code = "All Ledger Codes";
				else {
					while (!$res_type->EOF) {
						if ($str_ledger_code != '')
							$str_ledger_code .= ', ';
						$str_ledger_code .= $res_type->fields['CODE'];
						$res_type->MoveNext();
					}
					if ($str_ledger_code != '')
						$str_ledger_code = "Ledger Code(s): " . $str_ledger_code;
				}
			}
			$str_ledger_code = substr($str_ledger_code, 0, 95);
			if (strlen($str_ledger_code) >= 95) {
				$str_ledger_code .= '...';
			}

			$str_disb_date = "";
			if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
				$str_disb_date = "" . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
			else if ($_POST['START_DATE'] != '')
				$str_disb_date = "" . $_POST['START_DATE'];
			else if ($_POST['END_DATE'] != '')
				$str_disb_date = "" . $_POST['END_DATE'];

			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 	= $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != ''){
				//$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				$PDF_LOGO=str_replace('../',$http_path,$PDF_LOGO);
				$logo = '<img src="'.$PDF_LOGO.'" width="100px" />';
			}

			$header = '<table width="100%" >
						<tr>
							<td width="15%" valign="top" >'.$logo.'</td>
							<td width="35%" valign="top" style="font-size:20px;font-family: helvetica;padding-top:20px;" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td align="right" style="font-size:28px;border-bottom:1px solid #000;font-family: helvetica;font-style: italic;" >Projected Funds</td>
									</tr>
									<tr>
										<td align="right" style="font-size:16px;font-family: helvetica;font-style: italic;" >Disbursement Dates: '.$str_disb_date.'</td>
									</tr>
								</table>
							</td>
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
			$date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());
			
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date_footer.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
								<td></td>							
							</tr>
						</table>';

			$header_cont= '<!DOCTYPE HTML>
			<html>
			<head>
			<style>
			div{ padding-bottom:20px !important; }	
			</style>
			</head>
			<body>
			<div> '.$header.' </div>
			</body>
			</html>';

			$html_body_cont = '<!DOCTYPE HTML>
			<html>
			<head> <style>
			table{  margin-top: 2px; }
			table tr{  padding-top: 1px !important; }
			</style>
			</head>
			<body>'.$txt.'</body></html>';

			$footer_cont= '<!DOCTYPE HTML><html><head><style>
			tbody td{ font-size:14px !important; }
			</style></head><body>'.$footer.'</body></html>';

			$header_path= create_html_file('header_projected_funds_summary.html',$header_cont);
			$content_path=create_html_file('content_projected_funds_summary.html',$html_body_cont);
			$footer_path= create_html_file('footer_projected_funds_summary.html',$footer_cont);

			$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 210  --page-height 297 --margin-top 30mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/'.$file_name.' 2>&1';
					
			$pdfdata =array('filepath'=>'temp/'.$file_name,'exec'=>$exec,'filename'=>$file_name,'filefullpath'=>$http_path.'/school/temp/'.$file_name);		
			//print_r($pdfdata);
			sleep(2);
			exec($pdfdata['exec']);	
			header('Content-Type: Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
			readfile($pdfdata['filepath']);

			unlink('../school/temp/header_projected_funds_summary.html');
			unlink('../school/temp/content_projected_funds_summary.html');
			unlink('../school/temp/footer_projected_funds_summary.html');

			exit;	
		}
	}
	// End DIAM-1666
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?= MNU_PROJECTED_FUNDS ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.red a>label {
			color: red !important;
		}

		#advice-required-entry-PK_AR_LEDGER_CODE {
			position: absolute;
			top: 57px;
			width: 140px
		}

		#advice-required-entry-PK_STUDENT_STATUS {
			position: absolute;
			top: 57px;
			width: 140px
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
		}
		.option_red > a > label{color:red !important}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor">
							<?= MNU_PROJECTED_FUNDS ?>
						</h4>
					</div>
				</div>

				<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row form-group <?php

																// $allowed_accounts = [15, 505];
																// if (!in_array($_SESSION['PK_ACCOUNT'], $allowed_accounts)) {
																// 	echo ' d-none ';
																// } ?>">
										<div class="col-md-3 ">
											<?= REPORT_OPTIONS ?>
											<select id="REPORT_OPTION" name="REPORT_OPTION" class="form-control" onclick="show_buttons()">
												<option value="1">Projected Funds</option>												
												<option value="2">Projected Funds - Attendance</option>
												<option value="4">Projected Funds - Hours</option>
												<option value="3">Projected Funds - Summary</option>
											</select>
										</div>
									</div>
									<div class="row form-group">
										<div class="col-md-2 ">
											<?= CAMPUS ?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 ">
											<?= PROGRAM ?>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
												while (!$res_type->EOF) 
												{ 
													$ACTIVE 	= $res_type->fields['ACTIVE'];
													if ($ACTIVE == '0') {
														$Status = '(Inactive)';
													} else {
														$Status = '';
													}
													?>
													<option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>"><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- DIAM - 601, added inactive condition with highlighted in red color -->
										<div class="col-md-2">
											<?= STATUS ?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control required-entry">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,STUDENT_STATUS ASC");
												while (!$res_type->EOF) {

													$ACTIVE 	= $res_type->fields['ACTIVE'];
													if ($ACTIVE == '0') {
														$Status = '(Inactive)';
													} else {
														$Status = '';
													}
												?>
													<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>"><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- End DIAM - 601 -->

									</div>

									<div class="row">
										<div class="col-md-2">
											<?= AWARD_LEDGER_CODES ?>
											<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE[]" multiple class="form-control">
												<? $res_type = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 ORDER BY ACTIVE DESC, CODE ASC");
												while (!$res_type->EOF) { 
													
													$ACTIVE 	= $res_type->fields['ACTIVE'];
													if ($ACTIVE == '0') {
														$Status = '(Inactive)';
													} else {
														$Status = '';
													}
													
													?>
													<option value="<?= $res_type->fields['PK_AR_LEDGER_CODE'] ?>"><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
											<button class="linkbutton" id="PK_AR_LEDGER_CODE_helper_text" style="display:none" type="button" onclick="ToggleLedgerSelection('PK_LEDGER_CODE_GROUP')">Use Ledger Code</button>
										</div>
										<div class="col-md-2">
											<div class="form-group">
													Ledger Code Group
														<select id="PK_LEDGER_CODE_GROUP" name="PK_LEDGER_CODE_GROUP[]" multiple class="form-control " disabled>
															<? $res_type = $db->Execute("SELECT PK_LEDGER_CODE_GROUP,LEDGER_CODE_GROUP,LEDGER_CODE_GROUP_DESC,ACTIVE from S_LEDGER_CODE_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, LEDGER_CODE_GROUP ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?php echo $res_type->fields['PK_LEDGER_CODE_GROUP'] ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['LEDGER_CODE_GROUP'] ?><? if($res_type->fields['ACTIVE'] == 0) echo " (Inactive)"; ?></option>
															<? $res_type->MoveNext();
															} ?>
														</select>
														<style>.linkbutton{
															background: none!important;
															border: none;
															padding: 0!important;
															/*optional*/
															font-family: arial, sans-serif;
															/*input has OS specific font-family*/
															color: #069;
															text-decoration: underline;
															cursor: pointer;
															}
															.multiselect.dropdown-toggle.btn.btn-default.disabled{
																background-color:  #cbcbcb !important;
															}
														</style>
														<button class="linkbutton" id="PK_LEDGER_CODE_GROUP_helper_text" type="button" onclick="ToggleLedgerSelection('PK_AR_LEDGER_CODE')">Use Ledger Group</button>
														<script>
														var togglewith = '';
														function ToggleLedgerSelection(AlternateID = 'not_initiated'){
															jQuery(document).ready(function($) { 
																if( AlternateID != 'PK_LEDGER_CODE_GROUP'){
																	togglewith = AlternateID;
																	AlternateIDopt = document.getElementById(AlternateID);
																	if(AlternateIDopt.getAttribute('multiple') !== null){
																		$('#'+AlternateID).multiselect('disable');
																		$('#PK_LEDGER_CODE_GROUP').multiselect('enable'); 
																		add_toggerler(AlternateID);

																	}

																}else{ 
																	if(AlternateIDopt.getAttribute('multiple') !== null){ 	 
																		$('#'+AlternateID).multiselect('disable');
																		$('#'+togglewith).multiselect('enable'); 											
																		add_toggerler(AlternateID);
																	}
																}  
																});
														}
													 
														function add_toggerler(AlternateID){
															jQuery(document).ready(function($) {
																if(AlternateID != 'PK_LEDGER_CODE_GROUP'){
																	$('#'+AlternateID+'_helper_text').show();
																	$('#PK_LEDGER_CODE_GROUP_helper_text').hide();
																}else{
																	$('#'+AlternateID+'_helper_text').show();
																	$('#'+togglewith+'_helper_text').hide();
																} 
															});
														}
														</script>
													</div> 
													
											</div>

										<div class="col-md-2">
											Disbursement Start Date
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
										</div>
										<div class="col-md-2">
											Disbursement End Date
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="">
										</div>

										<div class="col-md-2">
											<!-- <br /><br />
											<input type="checkbox" id="INCLUDE_ALL_LEADS" name="INCLUDE_ALL_LEADS" value="1" >
											<?= INCLUDE_ALL_LEADS ?> -->
										</div>

										<div class="col-md-2" style="padding: 0;">
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
											<button type="button" id="btn_excel" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
											<input type="hidden" name="FORMAT" id="FORMAT">
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<? require_once("footer.php"); ?>
	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		jQuery(document).ready(function($) {

			show_buttons(); // DIAM-1666

			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if(result == true){
				if($('#PK_AR_LEDGER_CODE').val() == '' && $('#PK_LEDGER_CODE_GROUP').val() == ''){
					result = false;
					console.log('PK_AR_LEDGER_CODE',$('#PK_AR_LEDGER_CODE').val());
					console.log('PK_LEDGER_CODE_GROUP',$('#PK_LEDGER_CODE_GROUP').val());
					alert("Select At Least One Ledger Code or Group")
				}
			}
				if (result == true) {
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}

		// DIAM-1666
		function show_buttons()
		{
			var val = document.getElementById('REPORT_OPTION').value;
			if(val == 3) {
				document.getElementById('btn_excel').style.display = 'none';
			}
			else{
				document.getElementById('btn_excel').style.display = 'inline';
			}
		}
		// End DIAM-1666

	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_AR_LEDGER_CODE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= AWARD_LEDGER_CODES ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= AWARD_LEDGER_CODES ?> selected'
			});
			$('#PK_LEDGER_CODE_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Ledger Groups',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: 'Ledger Groups selected'
			});
			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STATUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= STATUS ?> selected'
			});

			$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= PROGRAM ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= PROGRAM ?> selected'
			});

			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= CAMPUS ?> selected'
			});

			// added color for inactive text
			child = $('.multiselect-container').children();
			child.each(function(i, val) {
				var str1 = val.innerText
				if (str1.indexOf("Inactive") != -1) {
					$(this).addClass('red')
				}

			});

		});
	</script>

</body>

</html>
