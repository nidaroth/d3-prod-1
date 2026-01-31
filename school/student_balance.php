<? /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
//$debug=true;
ini_set("memory_limit", "3000M");
ini_set("max_execution_time", "600");

ini_set("pcre.backtrack_limit", "500000000");

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_balance.php");
require_once("check_access.php");

if (check_access('REPORT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);

	$cond 	= "";
	if ($_POST['ENROLLMENT_OPTIONS'] == 1 || $_POST['ENROLLMENT_OPTIONS'] == 2 || $_POST['ENROLLMENT_OPTIONS'] == 3 || $_POST['ENROLLMENT_OPTIONS'] == 5) {
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
	} else if ($_POST['ENROLLMENT_OPTIONS'] == 4) {
		if ($_POST['REPORT_OPTIONS_1'] == 1)
			$ET = date("Y-m-t", strtotime($_POST['YEAR'] . '-12-01'));
		else
			$ET = date("Y-m-t", strtotime($_POST['YEAR'] . '-' . $_POST['MONTH'] . '-01'));

		$cond .= " AND TRANSACTION_DATE <= '$ET' ";
	}

	if ($_POST['ENROLLMENT_OPTIONS'] == 4) {
		if ($_POST['REPORT_OPTIONS_1'] == 1) {
			for ($i = 1; $i <= 12; $i++) {
				$YEAR_ARR[]	 = $_POST['YEAR'];
				$MONTH_ARR[] = $i;
			}
		} else if ($_POST['REPORT_OPTIONS_1'] == 2) {

			$start    = date("Y-m-01", strtotime($_POST['YEAR'] . '-' . $_POST['MONTH'] . '-01 -11 month'));
			$start    = new DateTime($start);

			$end   	  = date("Y-m-01", strtotime($_POST['YEAR'] . '-' . $_POST['MONTH'] . '-01 1 month'));
			$end      = new DateTime($end);

			//echo $start->format("Y")."-".$start->format("m")."<br />".$end->format("Y")."-".$end->format("m");

			$interval = DateInterval::createFromDateString('1 month');
			$period   = new DatePeriod($start, $interval, $end);

			foreach ($period as $dt) {
				$YEAR_ARR[]	 = $dt->format("Y");
				$MONTH_ARR[] = $dt->format("m");
			}
		}
		//echo "<pre>";print_r($YEAR_ARR);print_r($MONTH_ARR);exit;
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

	if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 1) order by STUDENT_STATUS ASC");
		while (!$res_type->EOF) {
			if ($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}
		if ($sts != '')
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $sts . ") ";
	}

	if (!empty($_POST['PK_TERM_MASTER'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER in (" . implode(",", $_POST['PK_TERM_MASTER']) . ") ";
	}

	if (!empty($_POST['PK_CAMPUS_PROGRAM'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (" . implode(",", $_POST['PK_CAMPUS_PROGRAM']) . ") ";
	}

	if (!empty($_POST['PK_FUNDING'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_FUNDING in (" . implode(",", $_POST['PK_FUNDING']) . ") ";
	}

	/* Ticket # 1282 */
	$group_by 		= "";
	$disb_group_by 	= "";
	if ($_POST['ENROLLMENT_OPTIONS'] == 1) {
		$group_by 		= " S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT ";
		$disb_group_by 	= " S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT ";
	} else if ($_POST['ENROLLMENT_OPTIONS'] == 2) {
		$cond 	 		.= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		$group_by 		= " S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT ";
		$disb_group_by 	= " S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT ";
	} else if ($_POST['ENROLLMENT_OPTIONS'] == 3 || $_POST['ENROLLMENT_OPTIONS'] == 4 || $_POST['ENROLLMENT_OPTIONS'] == 5) {
		$group_by 		= " S_STUDENT_LEDGER.PK_STUDENT_MASTER ";
		$disb_group_by 	= " S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER ";
	}

	if ($_POST['ENROLLMENT_OPTIONS'] == 3 || $_POST['ENROLLMENT_OPTIONS'] == 4 || $_POST['ENROLLMENT_OPTIONS'] == 5)
		$cond .= " AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 ";
	else
		$cond .= " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";

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

	//echo $cond;exit;
	$query = "select S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, PK_STUDENT_LEDGER, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE ,IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%Y-%m-%d' )) AS TRANSACTION_DATE_1, SUM(CREDIT) AS CREDIT, SUM(DEBIT) AS DEBIT, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, FUNDING, STUDENT_STATUS,  IF(LDA = '0000-00-00','', DATE_FORMAT(LDA, '%Y-%m-%d' )) AS LDA, CAMPUS_CODE, S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER, STUDENT_ID, IF(MIDPOINT_DATE = '0000-00-00','', DATE_FORMAT(MIDPOINT_DATE, '%m/%d/%Y' )) AS MIDPOINT_DATE, IF(GRADE_DATE = '0000-00-00','', DATE_FORMAT(GRADE_DATE, '%m/%d/%Y' )) AS GRADE_DATE, IF(DETERMINATION_DATE = '0000-00-00','', DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y' )) AS DETERMINATION_DATE, IF(DROP_DATE = '0000-00-00','', DATE_FORMAT(DROP_DATE, '%m/%d/%Y' )) AS DROP_DATE      
	from 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER , 
	S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	, S_STUDENT_LEDGER 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	(S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond $campus_cond1  ";
	/* Ticket # 1282 */
	//echo $query;exit;	


	$report_option = "";
	if ($_POST['ENROLLMENT_OPTIONS'] == 1)
		$report_option = "Student Balance - All Enrollments";
	else if ($_POST['ENROLLMENT_OPTIONS'] == 2)
		$report_option = "Student Balance - Current Enrollment";
	else if ($_POST['ENROLLMENT_OPTIONS'] == 3)
		$report_option = "Student Balance - Student";
	else if ($_POST['ENROLLMENT_OPTIONS'] == 4)
		$report_option = "Student Balance - End of Month";
	else if ($_POST['ENROLLMENT_OPTIONS'] == 5)
		$report_option = "Student Balance - Last Transaction";

	$query_disb = "SELECT SUM(DISBURSEMENT_AMOUNT) as DISBURSEMENT_AMOUNT FROM S_STUDENT_DISBURSEMENT WHERE PK_DISBURSEMENT_STATUS IN (2,3,4) AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	if ($_POST['FORMAT'] == 1) {
		require_once '../global/mpdf/vendor/autoload.php';
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE, EMAIL FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 	= $res->fields['PDF_LOGO'];
		$EMAIL			= $res->fields['EMAIL'];
		$PHONE			= $res->fields['PHONE'];
		$SCHOOL_ADDRESS = trim($res->fields['ADDRESS'] . " " . $res->fields['ADDRESS_1']) . "<br />" . $res->fields['CITY'] . " " . $res->fields['STATE_CODE'] . " " . $res->fields['ZIP'];

		$logo = "";
		if ($PDF_LOGO != '')
			$logo = '<img src="' . $PDF_LOGO . '" height="50px" />';

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
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>COMP40002</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
						</tr>
					</table>';

		$str_date = "";
		if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
			$str_date = " " . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
		else if ($_POST['START_DATE'] != '')
			$str_date = " from " . $_POST['START_DATE'];
		else if ($_POST['END_DATE'] != '')
			$str_date = " Balance As Of: " . $_POST['END_DATE'];

		$balanace_option = "";
		if ($_POST['REPORT_OPTIONS'] == 1)
			$balanace_option = "All Balances";
		else if ($_POST['REPORT_OPTIONS'] == 2)
			$balanace_option = "Positive Balances";
		else if ($_POST['REPORT_OPTIONS'] == 3)
			$balanace_option = "Zero Balances";
		else if ($_POST['REPORT_OPTIONS'] == 4)
			$balanace_option = "Negative Balances";
		else if ($_POST['REPORT_OPTIONS'] == 5)
			$balanace_option = "Non-Zero Balances";
		else if ($_POST['REPORT_OPTIONS'] == 6)
			$balanace_option = "Positive and Zero Balances Only";
		else if ($_POST['REPORT_OPTIONS'] == 7)
			$balanace_option = "Negative and Zero Balances Only";

		$INCLUDE_ALL_LEADS = "No";
		if ($_POST['INCLUDE_ALL_LEADS'] == 1)
			$INCLUDE_ALL_LEADS = "Yes";

		$INCLUDE_PROJECTED_DISBURSEMENTS = "No";
		if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1)
			$INCLUDE_PROJECTED_DISBURSEMENTS = "Yes";

		$str = "";
		if (empty($_POST['PK_STUDENT_STATUS'])) {
			$str = "All Student Status";
		} else {
			$str = "";
			$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");
			while (!$res_type->EOF) {
				if ($str != '')
					$str .= ', ';
				$str .= $res_type->fields['STUDENT_STATUS'];
				$res_type->MoveNext();
			}

			if ($str != '')
				$str = "Student Status: " . $str;
		}

		$funding = "";
		if (empty($_POST['PK_FUNDING'])) {
			$funding = "All Fundings";
		} else {
			$funding = "";
			$res_type = $db->Execute("select FUNDING from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_FUNDING IN (" . implode(",", $_POST['PK_FUNDING']) . ") order by FUNDING ASC");
			while (!$res_type->EOF) {
				if ($funding != '')
					$funding .= ', ';
				$funding .= $res_type->fields['FUNDING'];
				$res_type->MoveNext();
			}
		}

		if ($_POST['ENROLLMENT_OPTIONS'] == 1 || $_POST['ENROLLMENT_OPTIONS'] == 2 || $_POST['ENROLLMENT_OPTIONS'] == 3) {
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >' . $logo . '</td>
								<td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
								<td width="40%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>' . $report_option . '</b></td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >' . $str_date . '</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Balance Option: ' . $balanace_option . '</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Include All Leads: ' . $INCLUDE_ALL_LEADS . '</td>
										</tr>
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Include Projected Disbursements: ' . $INCLUDE_PROJECTED_DISBURSEMENTS . '</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $str . '</td>
							</tr>
							<tr>
								<td colspan="3" width="100%" align="right" style="font-size:13px;" >Funding: ' . $funding . '</td>
							</tr>
						</table>';
		} else if ($_POST['ENROLLMENT_OPTIONS'] == 4) {
			$header = '<table width="100%" >
					<tr>
						<td width="20%" valign="top" >' . $logo . '</td>
						<td width="30%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
						<td width="50%" valign="top" >
							<table width="100%" >
								<tr>
									<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>' . $report_option . '</b></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): ' . $campus_name . '</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Selected Year: ' . $_POST['YEAR'] . '</td>
					</tr>';

			if ($_POST['REPORT_OPTIONS_1'] == 2) {
				$header .= '<tr>
										<td colspan="3" width="100%" align="right" style="font-size:13px;" >Selected Month: ' . date("F", strtotime($_POST['YEAR'] . '-' . $_POST['MONTH'] . '-01')) . '</td>
									</tr>';
			}

			$header .= '<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $str . '</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Include All Leads: ' . $INCLUDE_ALL_LEADS . '</td>
					</tr>
				</table>';
		} else if ($_POST['ENROLLMENT_OPTIONS'] == 5) {
			$header = '<table width="100%" >
					<tr>
						<td width="20%" valign="top" >' . $logo . '</td>
						<td width="30%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
						<td width="50%" valign="top" >
							<table width="100%" >
								<tr>
									<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>' . $report_option . '</b></td>
								</tr>
							</table>query
						</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): ' . $campus_name . '</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $str_date . '</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Report Option: ' . $balanace_option . '</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $str . '</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Funding: ' . $funding . '</td>
					</tr>
					<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Include All Leads: ' . $INCLUDE_ALL_LEADS . '</td>
					</tr>
				</table>';
		}

		$margin_top = 70;
		if ($_POST['ENROLLMENT_OPTIONS'] == 4)
			$margin_top = 50;

		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => $margin_top,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 8,
			'format' => [210, 296],
			'orientation' => 'L',

		]);
		$mpdf->autoPageBreak = true;
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);

		/////////////////////////////////////////////////////////////////

		$total 	= 0;
		$txt 	= '';

		if ($_POST['ENROLLMENT_OPTIONS'] == 1 || $_POST['ENROLLMENT_OPTIONS'] == 2 || $_POST['ENROLLMENT_OPTIONS'] == 3) {
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="11%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student</td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >SSN</td>
								<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Program</td>
								<td width="9%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Funding</td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Status</td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Start Date</td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Exp<br />Grade Date</td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >LDA</td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Debit</td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Credit</td>
								<td width="8%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Balance</td>';

			if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1) {
				$txt .= '<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Projected Disbursements</td>
											 <td width="8%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Projected<br />Balance</td>';
			}

			$txt .= '</tr>
						</thead>';

			$TOT_BALANCE 		= 0;
			$TOT_DISB_BALANCE 	= 0;
			//echo $query." ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";exit;
			$res_ledger = $db->Execute($query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");

			$sno = 0;
			while (!$res_ledger->EOF) {
				$PK_STUDENT_ENROLLMENT	= $res_ledger->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER		= $res_ledger->fields['PK_STUDENT_MASTER'];
				$SSN 					= $res_ledger->fields['SSN'];

				if ($SSN != '') {
					$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'], $SSN);
					$SSN_ORG = $SSN;
					$SSN_ARR = explode("-", $SSN);
					$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
				}

				$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
				$flag 		= 0;
				if ($_POST['REPORT_OPTIONS'] == 1) {
					$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 2) {
					if ($BALANCE > 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 3) {
					if ($BALANCE == 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 4) {
					if ($BALANCE < 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 5) {
					if ($BALANCE != 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 6) {
					if ($BALANCE >= 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 7) {
					if ($BALANCE <= 0)
						$flag = 1;
				}

				if ($flag == 1) {
					$sno++;
					$TOT_BALANCE += $BALANCE;
					$BALANCE1	 = $BALANCE;

					/* Ticket # 1281  */
					if ($BALANCE < 0) {
						$BALANCE = $BALANCE * -1;
						$BALANCE = '($ ' . number_format_value_checker($BALANCE, 2) . ')';
					} else
						$BALANCE = '$ ' . number_format_value_checker($BALANCE, 2);
					/* Ticket # 1281  */

					$txt 	.= '<tr>
								<td >' . $sno . '. ' . $res_ledger->fields['NAME'] . '</td>
								<td >' . $SSN . '</td>
								<td >' . $res_ledger->fields['PROGRAM_CODE'] . '</td>
								<td >' . $res_ledger->fields['FUNDING'] . '</td>
								<td >' . $res_ledger->fields['STUDENT_STATUS'] . '</td>
								<td >' . $res_ledger->fields['BEGIN_DATE_1'] . '</td>
								<td >' . $res_ledger->fields['EXPECTED_GRAD_DATE'] . '</td>
								<td >' . $res_ledger->fields['LDA'] . '</td>
								<td align="right" >$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2) . '</td>
								<td align="right" >$ ' . number_format_value_checker($res_ledger->fields['CREDIT'], 2) . '</td>
								<td align="right" >' . $BALANCE . '</td>';

					if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1) {
						$disb_cond = "";
						if ($_POST['ENROLLMENT_OPTIONS'] == 1 || $_POST['ENROLLMENT_OPTIONS'] == 2) {
							$disb_cond = " AND S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
						} else {
							$disb_cond = " AND S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
						}

						$res_disb = $db->Execute($query_disb . " " . $disb_cond . " GROUP BY " . $disb_group_by);

						$DISBURSEMENT_AMOUNT = $res_disb->fields['DISBURSEMENT_AMOUNT'];
						if ($DISBURSEMENT_AMOUNT < 0) {
							$DISBURSEMENT_AMOUNT = $DISBURSEMENT_AMOUNT * -1;
							$DISBURSEMENT_AMOUNT = '($ ' . number_format_value_checker($DISBURSEMENT_AMOUNT, 2) . ')';
						} else
							$DISBURSEMENT_AMOUNT = '$ ' . number_format_value_checker($DISBURSEMENT_AMOUNT, 2);

						$DISB_BALANCE 		= $BALANCE1 - $res_disb->fields['DISBURSEMENT_AMOUNT'];
						$TOT_DISB_BALANCE  += $DISB_BALANCE;

						if ($DISB_BALANCE < 0) {
							$DISB_BALANCE = $DISB_BALANCE * -1;
							$DISB_BALANCE = '($ ' . number_format_value_checker($DISB_BALANCE, 2) . ')';
						} else
							$DISB_BALANCE = '$ ' . number_format_value_checker($DISB_BALANCE, 2);

						$txt .= '<td align="right" >' . $DISBURSEMENT_AMOUNT . '</td>
											<td align="right" >' . $DISB_BALANCE . '</td>';
					}
					$txt .= '</tr>';
				}

				$res_ledger->MoveNext();
			}

			if ($TOT_BALANCE < 0) {
				$TOT_BALANCE = $TOT_BALANCE * -1;
				$TOT_BALANCE = '($ ' . number_format_value_checker($TOT_BALANCE, 2) . ')';
			} else
				$TOT_BALANCE = number_format_value_checker($TOT_BALANCE, 2);

			if ($TOT_DISB_BALANCE < 0) {
				$TOT_DISB_BALANCE = $TOT_DISB_BALANCE * -1;
				$TOT_DISB_BALANCE = '($ ' . number_format_value_checker($TOT_DISB_BALANCE, 2) . ')';
			} else
				$TOT_DISB_BALANCE = number_format_value_checker($TOT_DISB_BALANCE, 2);

			$txt 	.= '<tr>
							<td colspan="9" ></td>
							<td align="right" align="right" ><i><b>Total</b></i></td>
							<td align="right" align="right" ><i><b>$ ' . $TOT_BALANCE . '</b></i></td>';
			if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1) {
				$txt .= '<td align="right" align="right" ><i><b>Total</b></i></td>
									<td align="right" align="right" ><i><b>$ ' . $TOT_DISB_BALANCE . '</b></i></td>';
			}

			$txt 	.= '</tr>
					</table>';
		} else if ($_POST['ENROLLMENT_OPTIONS'] == 4) {

			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student</b></td>';
			foreach ($MONTH_ARR as $ii => $MONTH)
				$txt .= '<td width="7.16%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>' . date("M", strtotime($YEAR_ARR[$ii] . "-" . $MONTH . "-01")) . '<br />' . date("Y", strtotime($YEAR_ARR[$ii] . "-" . $MONTH . "-01")) . '</b></td>';

			$txt .= '</tr>
						</thead>';

			$sno = 0;
			$total_balance[0]  = 0;
			$total_balance[1]  = 0;
			$total_balance[2]  = 0;
			$total_balance[3]  = 0;
			$total_balance[4]  = 0;
			$total_balance[5]  = 0;
			$total_balance[6]  = 0;
			$total_balance[7]  = 0;
			$total_balance[8]  = 0;
			$total_balance[9]  = 0;
			$total_balance[10] = 0;
			$total_balance[11] = 0;

			$res_ledger = $db->Execute($query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");
			while (!$res_ledger->EOF) {
				$sno++;
				$PK_STUDENT_ENROLLMENT	= $res_ledger->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER		= $res_ledger->fields['PK_STUDENT_MASTER'];

				$txt .= '<tr>
							<td >' . $sno . '. ' . $res_ledger->fields['NAME'] . '</td>';

				$j = 0;
				foreach ($MONTH_ARR as $ii => $MONTH) {
					$TRANSACTION_DATE1 = date($YEAR_ARR[$ii] . "-" . $MONTH . "-t");

					$res_ledger_1 = $db->Execute($query . " AND TRANSACTION_DATE <= '$TRANSACTION_DATE1' AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' GROUP BY $group_by ");

					$BALANCE = $res_ledger_1->fields['DEBIT'] - $res_ledger_1->fields['CREDIT'];

					$total_balance[$j] += $BALANCE;

					if ($BALANCE < 0) {
						$BALANCE = $BALANCE * -1;
						$BALANCE = '($ ' . number_format_value_checker($BALANCE, 2) . ')';
					} else
						$BALANCE = '$ ' . number_format_value_checker($BALANCE, 2);

					$txt .= '<td align="right" > ' . $BALANCE . '</td>';

					$j++;
				}
				$txt .= '</tr>';

				$res_ledger->MoveNext();
			}

			$txt 	.= '<tr>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>Total</b></i></td>';
			for ($j = 0; $j < 12; $j++) {
				if ($total_balance[$j] < 0) {
					$total_balance_1 = $total_balance[$j] * -1;
					$total_balance_1 = '($ ' . number_format_value_checker($total_balance[$j], 2) . ')';
				} else
					$total_balance_1 = '$ ' . number_format_value_checker($total_balance[$j], 2);

				$txt .= '<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>' . $total_balance_1 . '</b></i></td>';
			}
			$txt .= '</tr>
					</table>';
		} else if ($_POST['ENROLLMENT_OPTIONS'] == 5) {
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="3%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ></td>
								<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student</b></td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student ID</b></td>
								<td width="19%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Enrollment</b></td>
								<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Funding</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Mid Point</b></td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>LDA</b></td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Debit</b></td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Credit</b></td>
								<td width="9%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Balance</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Last<br />Transaction</b></td>
							</tr>
						</thead>';

			$TOT_DEBIT			= 0;
			$TOT_CREDIT			= 0;
			$TOT_BALANCE 		= 0;
			$TOT_DISB_BALANCE 	= 0;
			//echo $query." GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";exit;
			$res_ledger = $db->Execute($query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");

			$sno = 0;
			while (!$res_ledger->EOF) {
				$PK_STUDENT_ENROLLMENT	= $res_ledger->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER		= $res_ledger->fields['PK_STUDENT_MASTER'];

				$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
				$flag 		= 0;
				if ($_POST['REPORT_OPTIONS'] == 1) {
					$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 2) {
					if ($BALANCE > 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 3) {
					if ($BALANCE == 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 4) {
					if ($BALANCE < 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 5) {
					if ($BALANCE != 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 6) {
					if ($BALANCE >= 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 7) {
					if ($BALANCE <= 0)
						$flag = 1;
				}

				if ($flag == 1) {
					$TOT_DEBIT		+= $res_ledger->fields['DEBIT'];
					$TOT_CREDIT		+= $res_ledger->fields['CREDIT'];

					$sno++;
					$TOT_BALANCE += $BALANCE;
					$BALANCE1	 = $BALANCE;

					if ($BALANCE < 0) {
						$BALANCE = $BALANCE * -1;
						$BALANCE = '($ ' . number_format_value_checker($BALANCE, 2) . ')';
					} else
						$BALANCE = '$ ' . number_format_value_checker($BALANCE, 2);

					$res_last = $db->Execute("SELECT MAX(TRANSACTION_DATE) as TRANSACTION_DATE FROM S_STUDENT_LEDGER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CREDIT != 0 LIMIT 0,1 ");
					$TRANSACTION_DATE = $res_last->fields['TRANSACTION_DATE'];
					if ($TRANSACTION_DATE != '' && $TRANSACTION_DATE != '0000-00-00')
						$TRANSACTION_DATE = date("m/d/Y", strtotime($TRANSACTION_DATE));
					else
						$TRANSACTION_DATE = '';

					$txt 	.= '<tr>
									<td >' . $sno . '. </td>
									<td >' . $res_ledger->fields['NAME'] . '</td>
									<td >' . $res_ledger->fields['STUDENT_ID'] . '</td>
									<td >' . $res_ledger->fields['BEGIN_DATE_1'] . ' - ' . $res_ledger->fields['PROGRAM_CODE'] . ' - ' . $res_ledger->fields['STUDENT_STATUS'] . ' - ' . $res_ledger->fields['CAMPUS_CODE'] . '</td>
									<td >' . $res_ledger->fields['FUNDING'] . '</td>
									<td >' . $res_ledger->fields['MIDPOINT_DATE'] . '</td>
									<td >' . $res_ledger->fields['LDA'] . '</td>
									<td align="right" >$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2) . '</td>
									<td align="right" >$ ' . number_format_value_checker($res_ledger->fields['CREDIT'], 2) . '</td>
									<td align="right" >' . $BALANCE . '</td>
									<td>' . $TRANSACTION_DATE . '</td>
								</tr>';
				}
				$res_ledger->MoveNext();
			}

			if ($TOT_BALANCE < 0) {
				$TOT_BALANCE = $TOT_BALANCE * -1;
				$TOT_BALANCE = '($ ' . number_format_value_checker($TOT_BALANCE, 2) . ')';
			} else
				$TOT_BALANCE = number_format_value_checker($TOT_BALANCE, 2);

			if ($TOT_DEBIT < 0) {
				$TOT_DEBIT = $TOT_DEBIT * -1;
				$TOT_DEBIT = '($ ' . number_format_value_checker($TOT_DEBIT, 2) . ')';
			} else
				$TOT_DEBIT = number_format_value_checker($TOT_DEBIT, 2);

			if ($TOT_CREDIT < 0) {
				$TOT_CREDIT = $TOT_CREDIT * -1;
				$TOT_CREDIT = '($ ' . number_format_value_checker($TOT_CREDIT, 2) . ')';
			} else
				$TOT_CREDIT = number_format_value_checker($TOT_CREDIT, 2);

			$txt 	.= '<tr>
							<td colspan="6" style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>Total</b></i></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>$ ' . $TOT_DEBIT . '</b></i></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>$ ' . $TOT_CREDIT . '</b></i></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>$ ' . $TOT_BALANCE . '</b></i></td>
							<td style="border-top:1px solid #000;" ></td>
						</tr>
					</table>';
		}

		//echo $txt;exit;
		// echo $txt;
		// 	echo "<br><hr>";
		// 	echo "TIME TAKEN FOR LOOPING - ".microtime() - $start_time." <<";exit;
		$header_path = create_html_file('header.html', $header);
		$content_path = create_html_file('content.html', $txt);
		$file_name = $report_option . '.pdf';
		//$mpdf->WriteHTML($txt);
		//$mpdf->Output($file_name, 'D');
		//echo "xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0  --orientation Portrait --page-size A4 --margin-top 50mm --header-html ".$header_path." ".$content_path." ./school/temp/".$file_name." 2>&1";
		//exit;
		//echo passthru("xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0  --orientation Portrait --page-size A4 --margin-top 50mm --header-html ".$header_path." ".$content_path." ./school/temp/".$file_name." 2>&1");

		return $file_name;
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
		$file_name 		= 'Student Enrollment Balance.xlsx';
		$outputFileName = $dir . $report_option . '.xlsx';
		$outputFileName = str_replace(pathinfo($outputFileName, PATHINFO_FILENAME), pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(), $outputFileName);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;
		$index 	= -1;

		if ($_POST['ENROLLMENT_OPTIONS'] == 1 || $_POST['ENROLLMENT_OPTIONS'] == 2 || $_POST['ENROLLMENT_OPTIONS'] == 3) {
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Funding';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Exp Grade Date';
			$width[]   = 20;
			$heading[] = 'LDA';
			$width[]   = 20;
			$heading[] = 'Debit';
			$width[]   = 20;
			$heading[] = 'Credit';
			$width[]   = 20;
			$heading[] = 'Balance';
			$width[]   = 20;

			if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1) {
				$heading[] = 'Projected Disbursements';
				$width[]   = 20;
				$heading[] = 'Projected Balance';
				$width[]   = 20;
			}

			$i = 0;
			foreach ($heading as $title) {
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			$TOT_BALANCE = 0;
			$res_ledger = $db->Execute($query . " GROUP BY $group_by ORDER BY CAMPUS_CODE ASC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");

			$sno = 0;
			while (!$res_ledger->EOF) {
				$SSN 					= $res_ledger->fields['SSN'];
				$PK_STUDENT_ENROLLMENT	= $res_ledger->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER		= $res_ledger->fields['PK_STUDENT_MASTER'];

				/*
				if($SSN != '') {
					$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'],$SSN);
					$SSN_ORG = $SSN;
					$SSN_ARR = explode("-",$SSN);
					$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
				}*/

				$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
				$flag 		= 0;
				if ($_POST['REPORT_OPTIONS'] == 1) {
					$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 2) {
					if ($BALANCE > 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 3) {
					if ($BALANCE == 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 4) {
					if ($BALANCE < 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 5) {
					if ($BALANCE != 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 6) {
					if ($BALANCE >= 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 7) {
					if ($BALANCE <= 0)
						$flag = 1;
				}

				if ($flag == 1) {
					$sno++;
					$TOT_BALANCE += $BALANCE;

					/*if($BALANCE < 0) {
						$BALANCE = $BALANCE * -1;
						$BALANCE = '('.number_format_value_checker($BALANCE,2).')';
					} else
						$BALANCE = number_format_value_checker($BALANCE,2);*/

					$line++;
					$index = -1;

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CAMPUS_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['NAME']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['BEGIN_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['FUNDING']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['PROGRAM_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['EXPECTED_GRAD_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['LDA']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DEBIT']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CREDIT']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BALANCE);

					if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1) {
						$disb_cond = "";
						if ($_POST['ENROLLMENT_OPTIONS'] == 1 || $_POST['ENROLLMENT_OPTIONS'] == 2) {
							$disb_cond = " AND S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
						} else {
							$disb_cond = " AND S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ";
						}

						$res_disb = $db->Execute($query_disb . " " . $disb_cond . " GROUP BY " . $disb_group_by);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disb->fields['DISBURSEMENT_AMOUNT']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(($BALANCE - $res_disb->fields['DISBURSEMENT_AMOUNT']));
					}
				}

				$res_ledger->MoveNext();
			}
		} else if ($_POST['ENROLLMENT_OPTIONS'] == 4) {
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus Code';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Funding';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;

			foreach ($MONTH_ARR as $ii => $MONTH) {
				$heading[] = date("F - Y", strtotime($YEAR_ARR[$ii] . "-" . $MONTH . "-01"));
				$width[]   = 20;
			}

			$heading[] = 'Home Phone';
			$width[]   = 20;
			$heading[] = 'Mobile Phone';
			$width[]   = 20;
			$heading[] = 'Email';
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

			$res_ledger = $db->Execute($query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");
			while (!$res_ledger->EOF) {
				$PK_STUDENT_ENROLLMENT	= $res_ledger->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER		= $res_ledger->fields['PK_STUDENT_MASTER'];

				$line++;
				$index = -1;

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['NAME']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_ID']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['BEGIN_DATE_1']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['FUNDING']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['PROGRAM_CODE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_STATUS']);

				$j = 0;
				foreach ($MONTH_ARR as $ii => $MONTH) {
					$TRANSACTION_DATE1 = date($YEAR_ARR[$ii] . "-" . $MONTH . "-t");

					$res_ledger_1 = $db->Execute($query . " AND TRANSACTION_DATE <= '$TRANSACTION_DATE1' AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' GROUP BY $group_by ");

					$BALANCE = $res_ledger_1->fields['DEBIT'] - $res_ledger_1->fields['CREDIT'];

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BALANCE);

					$j++;
				}

				$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_address->fields['HOME_PHONE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_address->fields['CELL_PHONE']);

				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_address->fields['EMAIL']);

				$res_ledger->MoveNext();
			}
		} else if ($_POST['ENROLLMENT_OPTIONS'] == 5) {
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus Code';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Funding';
			$width[]   = 20;
			$heading[] = 'Mid Point';
			$width[]   = 20;
			$heading[] = 'Exp Grade Date';
			$width[]   = 20;
			$heading[] = 'Grad Date';
			$width[]   = 20;
			$heading[] = 'LDA';
			$width[]   = 20;
			$heading[] = 'Determination Date';
			$width[]   = 20;
			$heading[] = 'Drop Date';
			$width[]   = 20;
			$heading[] = 'Debit';
			$width[]   = 20;
			$heading[] = 'Credit';
			$width[]   = 20;
			$heading[] = 'Balance';
			$width[]   = 20;
			$heading[] = 'Last Transaction';
			$width[]   = 20;
			$heading[] = 'Home Phone';
			$width[]   = 20;
			$heading[] = 'Mobile Phone';
			$width[]   = 20;
			$heading[] = 'Email';
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

			$TOT_BALANCE = 0;
			$res_ledger = $db->Execute($query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");

			$sno = 0;
			while (!$res_ledger->EOF) {
				$PK_STUDENT_MASTER = $res_ledger->fields['PK_STUDENT_MASTER'];

				$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
				$flag 		= 0;
				if ($_POST['REPORT_OPTIONS'] == 1) {
					$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 2) {
					if ($BALANCE > 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 3) {
					if ($BALANCE == 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 4) {
					if ($BALANCE < 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 5) {
					if ($BALANCE != 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 6) {
					if ($BALANCE >= 0)
						$flag = 1;
				} else if ($_POST['REPORT_OPTIONS'] == 7) {
					if ($BALANCE <= 0)
						$flag = 1;
				}

				if ($flag == 1) {
					$sno++;
					$TOT_BALANCE += $BALANCE;

					/*if($BALANCE < 0) {
						$BALANCE = $BALANCE * -1;
						$BALANCE = '('.number_format_value_checker($BALANCE,2).')';
					} else
						$BALANCE = number_format_value_checker($BALANCE,2);*/

					$line++;
					$index = -1;

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['NAME']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_ID']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CAMPUS_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['BEGIN_DATE_1']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['PROGRAM_CODE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_STATUS']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['FUNDING']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['MIDPOINT_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['EXPECTED_GRAD_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['GRADE_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['LDA']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DETERMINATION_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DROP_DATE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DEBIT']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CREDIT']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BALANCE);

					$res_last = $db->Execute("SELECT MAX(TRANSACTION_DATE) as TRANSACTION_DATE FROM S_STUDENT_LEDGER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CREDIT != 0 LIMIT 0,1 ");
					$TRANSACTION_DATE = $res_last->fields['TRANSACTION_DATE'];
					if ($TRANSACTION_DATE != '' && $TRANSACTION_DATE != '0000-00-00')
						$TRANSACTION_DATE = date("m/d/Y", strtotime($TRANSACTION_DATE));
					else
						$TRANSACTION_DATE = '';

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TRANSACTION_DATE);

					$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_address->fields['HOME_PHONE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_address->fields['CELL_PHONE']);

					$index++;
					$cell_no = $cell[$index] . $line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_address->fields['EMAIL']);
				}

				$res_ledger->MoveNext();
			}
		}

		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:" . $outputFileName);
	}
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<title><?= MNU_STUDENT_BALANCE ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		#advice-required-entry-PK_STUDENT_STATUS {
			position: absolute;
			top: 57px;
			width: 140px
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
		}

		.option_red>a>label {
			color: red !important;
		}

		.select2-results__option .wrap:before {
			font-family: fontAwesome;
			color: #999;
			content: "\f096";
			width: 25px;
			height: 25px;
			padding-right: 10px;

		}

		.select2-container--default .select2-selection--single {
			border: 1px solid #e9e9e9 !important;
		}

		.select2-container .select2-selection--single {
			height: 34px;
		}

		.select2-container .select2-selection--single .select2-selection__rendered {
			padding-top: 3px !important;
			;
		}

		.multi-checkboxes_wrap:before {
			font-family: fontAwesome;
			color: #999;
			content: "\f096";
			width: 25px;
			height: 25px;
			padding-right: 10px;

		}

		.multi-checkboxes_wrap[aria-selected=true]:before {
			content: "\f14a";
		}

		/* not required css */

		.row {
			padding: 10px;
		}

		.select2-multiple,
		.select2-multiple2 {
			width: 50%
		}

		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}

		/* 14 june 2023 */
		#PK_CAMPUS_PROGRAM_DIV .select2-container--open,
		#PK_CAMPUS_PROGRAM_DIV .select2-dropdown--below {
			min-width: 600px !important;
		}

		#PK_STUDENT_STATUS_DIV .select2-container--open,
		#PK_STUDENT_STATUS_DIV .select2-dropdown--below {
			min-width: 600px !important;
		}

		#PK_FUNDING_DIV .select2-container--open {
			min-width: 470px !important;

		}

		.select2-container--open .select2-dropdown {
			top: 25px;
		}

		.select2-container--default.select2-container--open.select2-container--below .select2-selection--single,
		.select2-container--default.select2-container--open.select2-container--below .select2-selection--multiple {
			width: 221.5px !important;
			position: absolute;
			top: -10px;
		}

		/* 14 june 2023 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor">
							<?= MNU_STUDENT_BALANCE ?>
						</h4>
					</div>
				</div>

				<form class="floating-labels" method="post" name="form1" action="student_balance_pdf.php" id="form1" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row form-group">
										<!-- Ticket # 1282 -->
										<div class="col-md-4">
											<?= REPORT_TYPE ?>
											<select id="ENROLLMENT_OPTIONS" name="ENROLLMENT_OPTIONS" class="form-control" onchange="show_fields()">
												<option value="1">Student Balance - All Enrollments</option>
												<option value="2">Student Balance - Current Enrollment</option>
												<option value="4">Student Balance - End of Month</option>
												<option value="5">Student Balance - Last Transaction</option>
												<option value="3">Student Balance - Student</option>
											</select>
										</div>
										<!-- Ticket # 1282 -->
									</div>

									<div class="row form-group">
										<div class="col-md-12">
											<h4 class="text-themecolor"><?= REPORT_FILTERS ?></h4>
										</div>

										<div class="col-md-2" id="CAMPUS_DIV">
											<?= CAMPUS ?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
												<option value="0">All</option>
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
												while (!$res_type->EOF) {
													$option_label = $res_type->fields['CAMPUS_CODE'];
													if ($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
													$style = "disabled='disabled'"; ?>
													<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red' " . $style; ?> <? if ($res_type->RecordCount() == 1) echo "selected"; ?>><?= $option_label ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 " id="FIRST_TERM_DIV">
											<lable><?= FIRST_TERM ?></lable>
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control first_term">
												<option value="0">All</option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) {
													$option_label = $res_type->fields['BEGIN_DATE_1'];
													if ($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
													$style = "disabled='disabled'"; ?>
													<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red' " . $style; ?>><?= $option_label ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV">
											<lable><?= PROGRAM ?></lable>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control">
												<option value="0">All</option>

												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
												while (!$res_type->EOF) {
													$option_label = $res_type->fields['DESCRIPTION'];
													if ($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
													$style = "disabled='disabled'"; ?>
													<option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>" <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red' " . $style; ?>><?= $res_type->fields['CODE'] . ' - ' . $option_label ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2" id="PK_STUDENT_STATUS_DIV">
											<lable><?= STUDENT_STATUS ?></lable>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control required-entry">
												<option value="0">All</option>
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, STUDENT_STATUS ASC"); //13 June 2023
												while (!$res_type->EOF) {
													$option_label = $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'];
													if ($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
													$style = "disabled='disabled'"; ?>
													<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>" <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red' " . $style; ?>><?= $option_label ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2" id="PK_FUNDING_DIV">
											</lable><?= FUNDING ?></lable>
											<select id="PK_FUNDING" name="PK_FUNDING[]" multiple class="form-control">
												<option value="0">All</option>

												<? $res_type = $db->Execute("select * from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, FUNDING ASC");
												while (!$res_type->EOF) {
													$option_label = $res_type->fields['FUNDING'] . ' - ' . $res_type->fields['DESCRIPTION'];
													if ($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
													$style = "disabled='disabled'"; ?>
													<option value="<?= $res_type->fields['PK_FUNDING'] ?>" <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red' " . $style; ?>><?= $option_label ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">
											<h4 class="text-themecolor"><?= REPORT_PARAMETERS ?></h4>
										</div>

										<!--<div class="col-md-2">
											<?= START_DATE ?>
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="" >
										</div>-->
										<div class="col-md-2" id="END_DATE_DIV">
											<?= BALANCE_AS_OF_DATE ?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="">
										</div>

										<div class="col-md-3" id="REPORT_OPTIONS_DIV">
											<?= REPORT_OPTIONS ?>
											<select id="REPORT_OPTIONS" name="REPORT_OPTIONS" class="form-control">
												<option value="1">All Balances</option> <!-- Ticket # 1282 -->
												<option value="2">Positive Balances</option>
												<option value="3">Zero Balances</option>
												<option value="4">Negative Balances</option>
												<option value="5">Non-Zero Balances</option>
												<option value="6">Positive and Zero Balances Only</option>
												<option value="7">Negative and Zero Balances Only</option>
											</select>
										</div>

										<div class="col-md-2" id="REPORT_OPTIONS_1_DIV">
											<?= REPORT_OPTIONS ?>
											<select id="REPORT_OPTIONS_1" name="REPORT_OPTIONS_1" class="form-control" onchange="show_month()">
												<option value="1">By Selected Year</option>
												<option value="2">By Selected Year/Month</option>
											</select>
										</div>

										<div class="col-md-1" id="YEAR_DIV">
											<?= YEAR ?>
											<select id="YEAR" name="YEAR" class="form-control">
												<? $res_type = $db->Execute("SELECT DISTINCT(YEAR(TRANSACTION_DATE)) as TRANS_YEAR FROM S_STUDENT_LEDGER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND YEAR(TRANSACTION_DATE) > 0 ORDER BY YEAR(TRANSACTION_DATE) DESC ");
												while (!$res_type->EOF) {  ?>
													<option value="<?= $res_type->fields['TRANS_YEAR'] ?>"><?= $res_type->fields['TRANS_YEAR'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-1" id="MONTH_DIV">
											<?= MONTH ?>
											<select id="MONTH" name="MONTH" class="form-control">
												<? for ($i = 1; $i <= 12; $i++) { ?>
													<option value="<?= $i ?>"><?= date("M", strtotime("2022-" . $i . "-01")) ?></option>
												<? } ?>
											</select>
										</div>
									</div>

									<div class="row">
										<!-- 19 june -->
										<!-- <div class="col-md-2" id="INCLUDE_ALL_LEADS_DIV" >
											<br /><br />
											<input type="checkbox" id="INCLUDE_ALL_LEADS" name="INCLUDE_ALL_LEADS" value="1" >
											<? //=INCLUDE_ALL_LEADS
											?>
										</div> -->

										<div class="col-md-3" id="INCLUDE_PROJECTED_DISBURSEMENTS_DIV">
											<br /><br />
											<input type="checkbox" id="INCLUDE_PROJECTED_DISBURSEMENTS" name="INCLUDE_PROJECTED_DISBURSEMENTS" value="1">
											<?= INCLUDE_PROJECTED_DISBURSEMENTS ?>
										</div>

										<div class="col-md-2" style="padding: 0;max-width:10.667%;flex: 0 0 10.667%;">
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

			show_fields();
		});
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script src="../backend_assets/dist/js/select2.multi-checkboxes.js"></script>
	<script type="text/javascript">
		function downloadFile(urlToSend, filename) {
			var loader = document.getElementsByClassName("preloader_grid");

			var req = new XMLHttpRequest();
			req.open("GET", urlToSend, true);
			req.responseType = "blob";
			req.onload = function(event) {
				if (this.status == 200) {
					var blob = req.response;
					var fileName = filename //if you have the fileName header available
					var link = document.createElement('a');
					link.href = window.URL.createObjectURL(blob);
					link.download = fileName;
					link.click();
					loader[0].style.display = "none";
				} else {
					//do nothing
					loader[0].style.display = "none";
				}

			};

			req.send();
		}
		// call async function for fetch reprot 
		async function fetchReport(formData) {
			return await jQuery.ajax({
				url: "student_balance_pdf",
				type: "POST",
				data: formData,
				async: true
			}).then(response => response);
		}

		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				var path = "<?php echo $http_path; ?>"
				if (result == true) {
					set_notification = false;
					var loader = document.getElementsByClassName("preloader_grid");
					loader[0].style.display = "block";
					document.getElementById('FORMAT').value = val
					const data = $('#form1').serialize();
					var result = fetchReport(data);
					result.then((result) => {
							if (result) {
								name = result.split('/')[1];
								var lastcharacter = path.charAt(path.length - 1)
								if (lastcharacter == '/') {
									downloadFile(path + 'school/' + result, name);
								} else {
									downloadFile(path + '/school/' + result, name);
								}
								set_notification = true;
							}
						},
						(error) => {
							console.log(error);
						});
				}

			});


		}




		function show_fields() {
			document.getElementById('CAMPUS_DIV').style.display = 'none';
			document.getElementById('FIRST_TERM_DIV').style.display = 'none';
			document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'none';
			document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'none';
			document.getElementById('PK_FUNDING_DIV').style.display = 'none';
			document.getElementById('END_DATE_DIV').style.display = 'none';
			document.getElementById('REPORT_OPTIONS_DIV').style.display = 'none';
			//document.getElementById('INCLUDE_ALL_LEADS_DIV').style.display 					= 'none'; //19 june
			document.getElementById('INCLUDE_PROJECTED_DISBURSEMENTS_DIV').style.display = 'none';

			document.getElementById('REPORT_OPTIONS_1_DIV').style.display = 'none';
			document.getElementById('YEAR_DIV').style.display = 'none';
			document.getElementById('MONTH_DIV').style.display = 'none';

			if (document.getElementById('ENROLLMENT_OPTIONS').value == 1 || document.getElementById('ENROLLMENT_OPTIONS').value == 2 || document.getElementById('ENROLLMENT_OPTIONS').value == 3) {
				document.getElementById('CAMPUS_DIV').style.display = 'inline';
				document.getElementById('FIRST_TERM_DIV').style.display = 'inline';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'inline';
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'inline';
				document.getElementById('PK_FUNDING_DIV').style.display = 'inline';
				document.getElementById('END_DATE_DIV').style.display = 'inline';
				document.getElementById('REPORT_OPTIONS_DIV').style.display = 'inline';
				//document.getElementById('INCLUDE_ALL_LEADS_DIV').style.display 					= 'inline'; // 19 june
				document.getElementById('INCLUDE_PROJECTED_DISBURSEMENTS_DIV').style.display = 'inline';
			} else if (document.getElementById('ENROLLMENT_OPTIONS').value == 4) {
				document.getElementById('CAMPUS_DIV').style.display = 'inline';
				document.getElementById('FIRST_TERM_DIV').style.display = 'inline';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'inline';
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'inline';
				document.getElementById('PK_FUNDING_DIV').style.display = 'inline';
				//document.getElementById('INCLUDE_ALL_LEADS_DIV').style.display 					= 'inline';  // 19 june

				document.getElementById('REPORT_OPTIONS_1_DIV').style.display = 'inline';
				document.getElementById('YEAR_DIV').style.display = 'inline';
				document.getElementById('MONTH_DIV').style.display = 'inline';

				document.getElementById('INCLUDE_PROJECTED_DISBURSEMENTS').checked = false;
				document.getElementById('END_DATE').value = '';
				show_month()

			} else if (document.getElementById('ENROLLMENT_OPTIONS').value == 5) {
				document.getElementById('CAMPUS_DIV').style.display = 'inline';
				document.getElementById('FIRST_TERM_DIV').style.display = 'inline';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display = 'inline';
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display = 'inline';
				document.getElementById('PK_FUNDING_DIV').style.display = 'inline';
				document.getElementById('END_DATE_DIV').style.display = 'inline';
				document.getElementById('REPORT_OPTIONS_DIV').style.display = 'inline';
				//document.getElementById('INCLUDE_ALL_LEADS_DIV').style.display 					= 'inline';  // 19 june

				document.getElementById('INCLUDE_PROJECTED_DISBURSEMENTS').checked = false;
			}
		}

		function show_month() {
			if (document.getElementById('REPORT_OPTIONS_1').value == 1)
				document.getElementById('MONTH_DIV').style.display = 'none';
			else
				document.getElementById('MONTH_DIV').style.display = 'inline';
		}
	</script>

	<!-- <script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/> -->
	<script type="text/javascript">
		jQuery(document).ready(function($) {






			// var select=$('.first_term').select2MultiCheckboxes({
			// 	minimumResultsForSearch: 20, // at least 20 results must be displayed

			// 	templateSelection: function(selected, total) {
			// 	return "Selected " + selected.length +" of "+total;
			// 	}
			// })
			//console.log(select);
			// 		select.on('select2:select', function (e) {
			//     var data = e.params.data;
			//     console.log(data);
			// });
			// $('#PK_STUDENT_STATUS').multiselect({
			// 	includeSelectAllOption: true,
			// 	allSelectedText: '<?= ALL_STUDENT_STATUS ?>',
			// 	nonSelectedText: '',
			// 	numberDisplayed: 2,
			// 	nSelectedText: '<?= STUDENT_STATUS ?> selected'
			// });

			// $('#PK_TERM_MASTER').multiselect({
			// 	includeSelectAllOption: true,
			// 	allSelectedText: '<?= ALL_FIRST_TERM ?>',
			// 	nonSelectedText: '',
			// 	numberDisplayed: 2,
			// 	nSelectedText: '<?= FIRST_TERM ?> selected',
			// 	onChange: function(option, checked) {
			//         console.log(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
			//     }

			// });
			// $('#PK_CAMPUS_PROGRAM').multiselect({
			// 	includeSelectAllOption: true,
			// 	allSelectedText: '<?= ALL_PROGRAM ?>',
			// 	nonSelectedText: '',
			// 	numberDisplayed: 2,
			// 	nSelectedText: '<?= PROGRAM ?> selected',
			// 	onChange: function(option, checked) {
			//         console.log(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
			//     }
			// });
			// $('#PK_CAMPUS').multiselect({
			// 	includeSelectAllOption: true,
			// 	allSelectedText: '<?= CAMPUS ?>',
			// 	nonSelectedText: '',
			// 	numberDisplayed: 2,
			// 	nSelectedText: '<?= CAMPUS ?> selected'
			// });
			// $('#PK_FUNDING').multiselect({
			// 	includeSelectAllOption: true,
			// 	allSelectedText: 'All <?= FUNDING ?>',
			// 	nonSelectedText: '',
			// 	numberDisplayed: 2,
			// 	nSelectedText: '<?= FUNDING ?> selected'
			// });

		});
	</script>
</body>

</html>