<?
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
ini_set("pcre.backtrack_limit", "50000000");

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if (check_access('REPORT_FINANCE') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	// echo "<pre>";print_r($_REQUEST);exit;
	/* if(isset($_REQUEST['PK_LEDGER_CODE_GROUP'])){
		$imploded = implode(',',$_REQUEST['PK_LEDGER_CODE_GROUP']);
		$ar_ledger_codes = $db->Execute("SELECT GROUP_CONCAT(PK_AR_LEDGER_CODES) AS CONCATED_RES FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN ($imploded) ");
		$ar_ledger_codes = explode(',' , $ar_ledger_codes->fields['CONCATED_RES']);
		$ar_ledger_codes = array_unique($ar_ledger_codes);
		$_POST['PK_AR_LEDGER_CODE'] = $ar_ledger_codes;
		
	} */

	#Initiate PHPEXCEL Instance 
	if ($_POST['FORMAT'] == 2) {
		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Disbursed Funds.xlsx';
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(pathinfo($outputFileName, PATHINFO_FILENAME), pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(), $outputFileName);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	}
	$ar_ledger_codes_header_str  = $db->Execute("SELECT GROUP_CONCAT(LEDGER_CODE_GROUP) AS LEDGER_CODE_GROUPS FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN (" . implode(',', $_REQUEST['PK_LEDGER_CODE_GROUP']) . ") ");
	$counter_index = 0;
	$summary_total = [];
	foreach ($_REQUEST['PK_LEDGER_CODE_GROUP'] as $key1 => $PK_LEDGER_CODE_GROUP1) {
		$line = 0;
		$imploded =  $PK_LEDGER_CODE_GROUP1;
		$ar_ledger_codes = $db->Execute("SELECT GROUP_CONCAT(PK_AR_LEDGER_CODES) AS CONCATED_RES , LEDGER_CODE_GROUP FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP IN ($imploded) ");
		$ar_ledger_codes_exploded = explode(',', $ar_ledger_codes->fields['CONCATED_RES']);
		$ar_ledger_codes_exploded = array_unique($ar_ledger_codes_exploded);
		$_POST['PK_AR_LEDGER_CODE'] = [];
		$_POST['PK_AR_LEDGER_CODE'] = $ar_ledger_codes_exploded;

		//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);

		$cond = "";
		if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
			$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
			$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
			$cond .= " AND DEPOSITED_DATE BETWEEN '$ST' AND '$ET' ";
		} else if ($_POST['START_DATE'] != '') {
			$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
			$cond .= " AND DEPOSITED_DATE >= '$ST' ";
		} else if ($_POST['END_DATE'] != '') {
			$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
			$cond .= " AND DEPOSITED_DATE <= '$ET' ";
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

		$ledger_cond = "";
		if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
			$ledger_cond = " AND PK_AR_LEDGER_CODE in (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
		}

		if (!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (" . implode(",", $_REQUEST['PK_CAMPUS_PROGRAM']) . ") ";

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

		$query = "select S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%Y-%m-%d' )) AS DISBURSEMENT_DATE, IF(DEPOSITED_DATE = '0000-00-00','', DATE_FORMAT(DEPOSITED_DATE, '%Y-%m-%d' )) AS DEPOSITED_DATE, DISBURSEMENT_AMOUNT, DISBURSEMENT_STATUS ,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, S_STUDENT_ACADEMICS.STUDENT_ID, M_CAMPUS_PROGRAM.UNITS , IF(MIDPOINT_DATE = '0000-00-00','', DATE_FORMAT(MIDPOINT_DATE, '%Y-%m-%d' )) AS MIDPOINT_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%Y-%m-%d' )) AS EXPECTED_GRAD_DATE, STUDENT_STATUS, CAMPUS_CODE, M_ENROLLMENT_STATUS.CODE as FOLL_PART_CODE 
	FROM  
	S_STUDENT_DISBURSEMENT 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT
	LEFT JOIN M_DISBURSEMENT_STATUS ON M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS  
	LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
	WHERE 
	S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS IN (1) $cond $campus_cond1 ";

		//echo $cond;exit;

		if ($_POST['FORMAT'] == 1) {
			/////////////////////////////////////////////////////////////////
			require_once '../global/mpdf/vendor/autoload.php';

			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

			$logo = "";
			if ($PDF_LOGO != '')
				$logo = '<img src="' . $PDF_LOGO . '" height="50px" />';

			$Deposit_date = "";
			if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
				$Deposit_date = " : " . $_POST['START_DATE'] . ' - ' . $_POST['END_DATE'];
			else if ($_POST['START_DATE'] != '')
				$Deposit_date = " from " . $_POST['START_DATE'];
			else if ($_POST['END_DATE'] != '')
				$Deposit_date = " to " . $_POST['END_DATE'];

			$sts = "";
			if (empty($_POST['PK_STUDENT_STATUS'])) {
				$sts = "All Student Status";
			} else {
				$str = "";

				$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (" . implode(",", $_POST['PK_STUDENT_STATUS']) . ") order by STUDENT_STATUS ASC");
				while (!$res_type->EOF) {
					if ($sts != '')
						$sts .= ', ';
					$sts .= $res_type->fields['STUDENT_STATUS'];
					$res_type->MoveNext();
				}

				// DIAM-1451
				$all_count  = $db->Execute("SELECT PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
				if (count($_POST['PK_STUDENT_STATUS']) == $all_count->RecordCount()) {
					$sts = " All";
				}
				// End DIAM-1451

				if ($sts != '') {
					$sts = "Status(es): " . $sts;
				}

				// DIAM-1451
				$res_type_led = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 AND PK_AR_LEDGER_CODE IN (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") order by CODE ASC");
				while (!$res_type_led->EOF) {
					if ($led_code != '') {
						$led_code .= ', ';
					}
					$led_code .= $res_type_led->fields['CODE'];
					$res_type_led->MoveNext();
				}

				$all_count_led  = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC");
				if (count($_POST['PK_AR_LEDGER_CODE']) == $all_count_led->RecordCount()) {
					$led_code = " All";
				}

				if ($led_code != '') {
					$led_code = "Ledger Code(s): " . $led_code;
				}
				$led_code = substr($led_code, 0, 95);
				if (strlen($led_code) >= 95) {
					$led_code .= '...';
				}
				// End DIAM-1451

			}

			$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >' . $logo . '</td>
							<td width="30%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Disbursed Funds</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus: ' . $campus_name . '</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Transaction Date Between' . $Deposit_date . '</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" > Legder Code Groups : ' . $ar_ledger_codes_header_str->fields['LEDGER_CODE_GROUPS']
				. '</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >' . $sts . '</td>
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
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
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
				'default_font_size' => 8
			]);
			$mpdf->autoPageBreak = true;

			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);

			$total 	= 0;
			$res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 $ledger_cond ORDER BY CODE ASC ");
			if ($res_ledger->RecordCount() > 0) {
				if($counter_index > 0){
					$final_txt .= "<pagebreak>";
				}
				$final_txt .= "<table>
					<thead>
					<tr>
					<td>
					<h1>" . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . "</h1>
					</td>
					</tr>
					</thead>
					</table>";
			}
			while (!$res_ledger->EOF) {
				$PK_AR_LEDGER_CODE = $res_ledger->fields['PK_AR_LEDGER_CODE'];

				$sub_total = 0;
				$res_disp = $db->Execute($query . " AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, DEPOSITED_DATE ASC ");

				if ($res_disp->RecordCount() > 0) {

					$final_txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td colspan="10" ><br></td>
								</tr>
								<tr>
									<td colspan="10" ><h2><i>' . $res_ledger->fields['CODE'] . ' (' . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . ') ' . '</i></h2></td>
								</tr>
								<tr>
									<td width="13%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >Student</td>
									<td width="9%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >ID</td>
									<td width="7%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >Campus</td>
									<td width="9%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >First Term</td>
									<td width="10%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >Program</td>
									<td width="9%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >Status</td>
									<td width="5%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >FT/PT</td>
									<td width="11%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >Disbursement<br>Date</td>
									<td width="10%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >Transaction<br>Date</td>
									<td width="3%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" align="center" >AY</td>
									<td width="3%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" align="center" >AP</td>
									<td width="11%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;font-weight: bold;" >Disbursement<br>Amount</td>
								</tr>
							</thead>';

					while (!$res_disp->EOF) {

						$SSN = $res_disp->fields['SSN'];
						if ($SSN != '') {
							$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'], $SSN);
							$SSN_ORG = $SSN;
							$SSN_ARR = explode("-", $SSN);
							$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
						}
						$final_txt 	.= '<tr>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['NAME'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['STUDENT_ID'] . '<br>' . $SSN_ORG . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['CAMPUS_CODE'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['BEGIN_DATE_1'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['PROGRAM_CODE'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['STUDENT_STATUS'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['FOLL_PART_CODE'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['DISBURSEMENT_DATE'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" >' . $res_disp->fields['DEPOSITED_DATE'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" align="center" >' . $res_disp->fields['ACADEMIC_YEAR'] . '</td>
								<td style="border-left:1px solid #000;border-bottom:1px solid #000;" align="center" >' . $res_disp->fields['ACADEMIC_PERIOD'] . '</td>
								<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >$ ' . $res_disp->fields['DISBURSEMENT_AMOUNT'] . '</td>
							</tr>';

						$sub_total += $res_disp->fields['DISBURSEMENT_AMOUNT'];
						$res_disp->MoveNext();
					}

					$total += $sub_total;
					$final_txt 	.= '<tr>
								<td colspan="9" align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;font-size:12px;font-weight: bold;" >' . $res_ledger->fields['CODE'] . ' Total </td>
								<td colspan="3" align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;font-size:12px;font-weight: bold;" >$ ' . number_format_value_checker($sub_total, 2) . '</td>
							</tr>
						</table><br />';
						$summary_total[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] += $sub_total;

				}
				// $mpdf->WriteHTML($final_txt);

				$res_ledger->MoveNext();
			}
			$final_txt 	.= '<br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" align="right" style="font-size:20px;" align="right" ><i>  <b style="font-size:20px;">' . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . ' Group Total : </b> $ ' . number_format_value_checker($total, 2) . '</i></td>
						</tr>
					</table> ';

			//echo $final_txt;exit;
			// $mpdf->WriteHTML($final_txt);
			// $mpdf->Output("Disbursed Funds_" . uniqid() . ".pdf", 'D');
			// exit;
			/////////////////////////////////////////////////////////////////
		} else if ($_POST['FORMAT'] == 2) {

			$summary_total[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] = 0;

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

			// $style = array(
			// 	'alignment' => array(
			// 		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			// 	)
			// );
			// $line 	= 1;
			// $cell_no = 'A1';
			// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Student');
			// $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			// $marge_cells = 'A1:H1';
			// $objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
			// $objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

			// $line 	= 1;
			// $cell_no = 'I1';
			// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Disbursements');
			// $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			// $marge_cells = 'I1:L1';
			// $objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
			// $objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

			$line  = 1;
			$index = -1;
			$heading = array();
			$width 	 = array();
			$width = false;
			$heading = false;
			$heading[] = 'Ledger Code';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'SSN';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'First Term';
			$width[]   = 15;
			$heading[] = 'Program';
			$width[]   = 25;
			$heading[] = 'Status';
			$width[]   = 15;
			$heading[] = 'FT/PT';
			$width[]   = 10;
			$heading[] = 'Disbursement Date';
			$width[]   = 20;
			$heading[] = 'Transaction Date';
			$width[]   = 17;
			$heading[] = 'AY';
			$width[]   = 5;
			$heading[] = 'AP';
			$width[]   = 5;
			$heading[] = 'Disbursement Amount';
			$width[]   = 22;

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

				$res_disp = $db->Execute($query . " AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, DEPOSITED_DATE ASC ");

				if ($res_disp->RecordCount() > 0) {
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
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['STUDENT_ID']);

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
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['FOLL_PART_CODE']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_DATE']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DEPOSITED_DATE']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_YEAR']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['ACADEMIC_PERIOD']);

						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_disp->fields['DISBURSEMENT_AMOUNT']);
						$summary_total[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] += $res_disp->fields['DISBURSEMENT_AMOUNT'];

						$res_disp->MoveNext();
					}
				}
				$res_ledger->MoveNext();
			}
		}
		$counter_index++;
	}


	if ($_POST['FORMAT'] == 1) {
		// echo $final_txt ; exit;
		// echo '<pre>';
		// echo htmlspecialchars($final_txt);
		// echo '</pre>';exit;
		$final_txt .= '
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
					<th style="border: 1px solid black;"><b>Total</b></th>
				</tr>
			</thead>
		<tbody>';
		foreach ($summary_total as $key => $value) {
			$final_txt .= 
			'<tr>
				<td style="border: 1px solid black;">'.$key.'</td>
				<td style="border: 1px solid black;">$'.number_format_value_checker($value,2).'</td>
			</tr>';
		}
		$final_txt .= "</tbody></table> </td></tr></table>";
		$mpdf->WriteHTML($final_txt);
		$mpdf->Output("Disbursed Funds_" . uniqid() . ".pdf", 'D');
		exit;
	} else if ($_POST['FORMAT'] == 2) {

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


		foreach ($summary_total as $key => $value) {
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

		}
		#normal code below 
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:" . $outputFileName);
	}
}
