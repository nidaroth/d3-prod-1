<? /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
//$debug=true;
ini_set("memory_limit", "-1");
ini_set("max_execution_time", "600");

ini_set("pcre.backtrack_limit", "500000000");

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_balance.php");
require_once("check_access.php");

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
		//$sts = implode(",",$_POST['PK_STUDENT_STATUS']);  //JUNE 19 2023
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") ";
	} else {
		$sts = "";
		//$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC"); // 19 june
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_STATUS ASC");
		while (!$res_type->EOF) {
			if ($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}

		if ($sts != '')
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $sts . ") ";
	}
	/* JUNE 19 2023
	if($_POST['INCLUDE_ALL_LEADS'] == 1){
		$sts1 = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 1 order by STUDENT_STATUS ASC");
		while (!$res_type->EOF) {
			if($sts1 != '')
				$sts1 .= ',';
			$sts1 .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}
		// if($sts != '')
		// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
		$sts .= ",".$sts1;
	} 
	
	//JUNE 7 2023
	if(!empty($_POST['PK_STUDENT_STATUS'])) {		
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	}
	*/

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
	S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND
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
		if ($PDF_LOGO != '') {
			//$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			$PDF_LOGO = str_replace('../', $http_path, $PDF_LOGO);
			$logo = '<img src="' . $PDF_LOGO . '" height="50px" />';
		}

		//$logo = '<img src="http://localhost/DSIS_GIT/local/backend_assets/school/school_15/other/PDF_LOGO_1001626203930_95685.png" height="50px" />';

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
							<td></td>							
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
		/*	// 19 june 2023
		$INCLUDE_ALL_LEADS = "No";
		if($_POST['INCLUDE_ALL_LEADS'] == 1)
			$INCLUDE_ALL_LEADS = "Yes";*/
		$INCLUDE_ALL_LEADS = "";

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
										<!--<tr>
											<td width="100%" align="right" style="font-size:13px;" >Include All Leads: ' . $INCLUDE_ALL_LEADS . '</td>
										</tr>-->
										<tr>
											<td width="100%" align="right" style="font-size:13px;" >Include Projected Disbursements: ' . $INCLUDE_PROJECTED_DISBURSEMENTS . '</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3" width="80%" align="right" style="font-size:13px;" >' . $str . '</td>
							</tr>
							<tr>
								<td colspan="3" width="80%" align="right" style="font-size:13px;" >Funding: ' . $funding . '</td>
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
						<td colspan="3" width="80%" align="right" style="font-size:13px;" >' . $str . '</td>
					</tr>
					<!--<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Include All Leads: ' . $INCLUDE_ALL_LEADS . '</td>
					</tr>-->
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
							</table>
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
						<td colspan="3" width="80%" align="right" style="font-size:13px;" >' . $str . '</td>
					</tr>
					<tr>
						<td colspan="3" width="80%" align="right" style="font-size:10px;" >Funding: ' . $funding . '</td>
					</tr>
					<!--<tr>
						<td colspan="3" width="100%" align="right" style="font-size:13px;" >Include All Leads: ' . $INCLUDE_ALL_LEADS . '</td>
					</tr>-->
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
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student ID</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >Program</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >Funding</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >Status</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >Start Date</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >Expected<br />Grad Date</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" >LDA</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Debit</td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Credit</td>
								<td  align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Balance</td>';

			if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1) {
				$txt .= '<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Projected Disbursements</td>
											 <td  align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Projected<br />Balance</td>';
			}

			$txt .= '</tr>
						</thead> <tbody>';

			$TOT_BALANCE 		= 0;
			$TOT_DISB_BALANCE 	= 0;
			//echo $query." ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";exit;
			// Begin store query in table
			$sql_query = $query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
			$log_array = array('SQL_QUERY' => $sql_query, 'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'], 'PK_USER' => $_SESSION['PK_USER'], 'REPORT_NAME' => 'Student Balance');

			log_query($log_array);
			// End store query in table
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
								<td style="padding-top:10px;font-size:13px;" >' . $sno . '. ' . $res_ledger->fields['NAME'] . '</td>
								<td style="padding-top:10px;font-size:13px;" >' . $res_ledger->fields['STUDENT_ID'] . '</td>
								<td style="padding-top:10px;font-size:13px;" >' . $res_ledger->fields['PROGRAM_CODE'] . '</td>
								<td style="padding-top:10px;font-size:13px;" >' . $res_ledger->fields['FUNDING'] . '</td>
								<td style="padding-top:10px;font-size:13px;" >' . $res_ledger->fields['STUDENT_STATUS'] . '</td>
								<td style="padding-top:10px;font-size:13px;" >' . $res_ledger->fields['BEGIN_DATE_1'] . '</td>
								<td style="padding-top:10px;font-size:13px;" >' . $res_ledger->fields['EXPECTED_GRAD_DATE'] . '</td>
								<td style="padding-top:10px;font-size:13px;" >' . $res_ledger->fields['LDA'] . '</td>
								<td  style="padding-top:10px;font-size:13px;" align="right" >$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2) . '</td>
								<td  style="padding-top:10px;font-size:13px;" align="right" >$ ' . number_format_value_checker($res_ledger->fields['CREDIT'], 2) . '</td>
								<td  style="padding-top:10px;font-size:13px;" align="right" >' . $BALANCE . '</td>';

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

						$txt .= '<td  style="padding-top:10px;font-size:13px;" align="right" >' . $DISBURSEMENT_AMOUNT . '</td>
											<td  style="padding-top:10px;font-size:13px;" align="right" >' . $DISB_BALANCE . '</td>';
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
							<td align="right" style="padding-top:10px;font-size:13px;"  ><i><b>Total</b></i></td>
							<td align="right" style="padding-top:10px;font-size:13px;" ><i><b>$ ' . $TOT_BALANCE . '</b></i></td>';
			if ($_POST['INCLUDE_PROJECTED_DISBURSEMENTS'] == 1) {
				$txt .= '<td  align="right" style="padding-top:10px;font-size:13px;" ><i><b>Total</b></i></td>
									<td  align="right" style="padding-top:10px;font-size:13px;" ><i><b>$ ' . $TOT_DISB_BALANCE . '</b></i></td>';
			}

			$txt 	.= '</tr> </tbody>
					</table>';
		} else if ($_POST['ENROLLMENT_OPTIONS'] == 4) {

			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student</b></td>';
			foreach ($MONTH_ARR as $ii => $MONTH)
				$txt .= '<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>' . date("M", strtotime($YEAR_ARR[$ii] . "-" . $MONTH . "-01")) . '<br />' . date("Y", strtotime($YEAR_ARR[$ii] . "-" . $MONTH . "-01")) . '</b></td>';

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
			// Begin store query in table
			$sql_query = $query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
			$log_array = array('SQL_QUERY' => $sql_query, 'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'], 'PK_USER' => $_SESSION['PK_USER'], 'REPORT_NAME' => 'Student Balance');
			log_query($log_array);
			// End store query in table
			$res_ledger = $db->Execute($query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ");
			while (!$res_ledger->EOF) {
				$sno++;
				$PK_STUDENT_ENROLLMENT	= $res_ledger->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_MASTER		= $res_ledger->fields['PK_STUDENT_MASTER'];

				$txt .= '<tr>
							<td >' . $sno . '. ' . $res_ledger->fields['NAME'] . '</td>';

				$j = 0;
				foreach ($MONTH_ARR as $ii => $MONTH) {
					// DIAM-2317
					$sTRANSACTION_DATE = date('Y-m', strtotime($YEAR_ARR[$ii] . "-" . $MONTH));
					$TRANSACTION_DATE1 = date('Y-m-t', strtotime($sTRANSACTION_DATE));
					// End DIAM-2317

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
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student ID</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Enrollment</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Funding</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Mid Point</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>LDA</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Debit</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Credit</b></td>
								<td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Balance</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Last<br />Transaction</b></td>
							</tr>
						</thead>';

			$TOT_DEBIT			= 0;
			$TOT_CREDIT			= 0;
			$TOT_BALANCE 		= 0;
			$TOT_DISB_BALANCE 	= 0;
			//echo $query." GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";exit;
			// Begin store query in table
			$sql_query = $query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
			$log_array = array('SQL_QUERY' => $sql_query, 'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'], 'PK_USER' => $_SESSION['PK_USER'], 'REPORT_NAME' => 'Student Balance');
			log_query($log_array);
			// End store query in table
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
									<td style="padding-top:10px;font-size:8xp;" >' . $sno . '. </td>
									<td style="padding-top:10px;font-size:8xp;" >' . $res_ledger->fields['NAME'] . '</td>
									<td style="padding-top:10px;" >' . $res_ledger->fields['STUDENT_ID'] . '</td>
									<td style="padding-top:10px;" >' . $res_ledger->fields['BEGIN_DATE_1'] . ' - ' . $res_ledger->fields['PROGRAM_CODE'] . ' - ' . $res_ledger->fields['STUDENT_STATUS'] . ' - ' . $res_ledger->fields['CAMPUS_CODE'] . '</td>
									<td style="padding-top:10px;" >' . $res_ledger->fields['FUNDING'] . '</td>
									<td style="padding-top:10px;" >' . $res_ledger->fields['MIDPOINT_DATE'] . '</td>
									<td style="padding-top:10px;" >' . $res_ledger->fields['LDA'] . '</td>
									<td style="padding-top:10px;" align="right" >$ ' . number_format_value_checker($res_ledger->fields['DEBIT'], 2) . '</td>
									<td style="padding-top:10px;" align="right" >$ ' . number_format_value_checker($res_ledger->fields['CREDIT'], 2) . '</td>
									<td style="padding-top:10px;" align="right" >' . $BALANCE . '</td>
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

			$txt 	.= '<tr style="border-top:1px solid #000;">
							<td colspan="6" style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>Total</b></i></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>$ ' . $TOT_DEBIT . '</b></i></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>$ ' . $TOT_CREDIT . '</b></i></td>
							<td style="border-top:1px solid #000;" align="right" align="right" ><i><b>$ ' . $TOT_BALANCE . '</b></i></td>
							<td style="border-top:1px solid #000;" ></td>
						</tr>
					</table>';
		}

		$report_option1 = str_replace(' ', '_', $report_option);
		$file_name1 = $report_option1 . '_' . uniqid() . '.pdf';
		// $mpdf->WriteHTML($txt);
		// $save_path="./temp/".$file_name;
		// $mpdf->Output($save_path, 'F');
		// echo 'temp/'.$file_name;

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
		body{ font-size:14px; }	
		table{  margin-top: 30px; }
		table tr{  padding-top: 14px !important; 
		}
		thead tr {font-weight : 600}
		td{vertical-align: middle !important; border-bottom: 1px solid #000;padding-top:10px;}
		body,html {
			padding: 0;
			margin: 0; 
		}
		</style>
		</head>
		<body>' . $txt . '</body></html>';
		$footer_cont = '<!DOCTYPE HTML><html><head><style>
		tbody td{ font-size:14px !important; }
		</style></head><body>' . $footer . '</body></html>';

		$header_path = create_html_file('header.html', $header_cont);
		$content_path = create_html_file('content.html', $html_body_cont);
		$footer_path = create_html_file('footer.html', $footer_cont);
		//$file_name = $report_option.'.pdf';
		//$mpdf->WriteHTML($txt);
		//$mpdf->Output($file_name, 'D');
		//echo "xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0  --orientation Portrait --page-size A4 --margin-top 50mm --header-html ".$header_path." ".$content_path." ../school/temp/".$file_name." 2>&1";
		sleep(2);
		$margin_top = "50mm";
		if (strlen($header) > 1530) {
			$margin_top = "60mm";
		}
		// exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation Landscape --page-size A5 --page-width 500mm  --page-height 296mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/'.$file_name1.' 2>&1');
		exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation Landscape --page-size A4 --page-width 210mm  --page-height 297mm --margin-top ' . $margin_top . '  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/' . $file_name1 . ' 2>&1');

		echo 'temp/' . $file_name1;

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
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Funding';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Expected Grade Date';
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
			// Begin store query in table
			$sql_query = $query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
			$log_array = array('SQL_QUERY' => $sql_query, 'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'], 'PK_USER' => $_SESSION['PK_USER'], 'REPORT_NAME' => 'Student Balance');
			log_query($log_array);
			// End store query in table
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
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_ID']);

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
					// DIAM-2317
					$sTRANSACTION_DATE = date('Y-m', strtotime($YEAR_ARR[$ii] . "-" . $MONTH));
					$TRANSACTION_DATE1 = date('Y-m-t', strtotime($sTRANSACTION_DATE));
					// End DIAM-2317

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
			$heading[] = 'Expected Grade Date';
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
			// Begin store query in table
			$sql_query = $query . " GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
			$log_array = array('SQL_QUERY' => $sql_query, 'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'], 'PK_USER' => $_SESSION['PK_USER'], 'REPORT_NAME' => 'Student Balance');
			log_query($log_array);
			// End store query in table
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
		//header("location:".$outputFileName);e
		echo $outputFileName;
	}
}
