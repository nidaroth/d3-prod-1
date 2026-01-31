<?
/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/_1098T_Setup.php");
require_once("check_access.php");
$debug = 0;
$res_add_on = $db->Execute("SELECT _1098T FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$res_type = $db->Execute("SELECT PK_1098T_EIN, EIN_NO FROM _1098T_EIN WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
$res_type_for_EIN = $db->Execute("SELECT PK_1098T_EIN, EIN_NO FROM _1098T_EIN WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if ((check_access('MANAGEMENT_ACCOUNTING') == 0 && ($_SESSION['PK_STUDENT_MASTER'] == '' || $_SESSION['PK_STUDENT_MASTER'] == 0)) || $res_add_on->fields['_1098T'] == 0) {
	header("location:../index");
	exit;
}

// DIAM-2046
function has_1098t_access($pk_account)
{ 
	$domain_name = array('d3-2.diamondsis.com');
	$actual_URL = $_SERVER['HTTP_HOST'];
	$account_array=array('516');
	if(in_array($pk_account,$account_array) && in_array($actual_URL, $domain_name)){
		return 1;
	}else{
		return 0;
	}
}
// End DIAM-2046

$report_error = "";
if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;
	if ($_POST['PK_1098T_EIN'] == 0) {

		$PK_1098T_EIN = "";
		$res_campus = $db->Execute("SELECT PK_1098T_EIN FROM _1098T_EIN_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res_campus->EOF) {
			if ($PK_1098T_EIN != '')
				$PK_1098T_EIN .= ',';
			$PK_1098T_EIN .= $res_campus->fields['PK_1098T_EIN'];
			$res_campus->MoveNext();
		}
	} else {
		$PK_1098T_EIN = $_POST['PK_1098T_EIN'];
	}

	$timezone = $_SESSION['PK_TIMEZONE'];
	if ($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if ($timezone == '' || $timezone == 0)
			$timezone = 4;
	}

	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];

	if ($_SESSION['PK_STUDENT_MASTER'] > 0)
		$PK_STUDENT_MASTER = $_SESSION['PK_STUDENT_MASTER'];
	else
		$PK_STUDENT_MASTER = 0;

	if ($_POST['FORMAT'] == 1) {    // This block for student ledger pdf  DIAM-11
		require_once '../global/mpdf/vendor/autoload.php';

		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

		$logo = "";
		if ($PDF_LOGO != '')
			$logo = '<img src="' . $PDF_LOGO . '" height="50px" />';

		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >' . $logo . '</td>
							<td width="40%" valign="top" style="font-size:20px" >' . $SCHOOL_NAME . '</td>
							<td width="40%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>1098-T Ledger Transactions</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Between: 01/01/'.$_POST['CALENDAR_YEAR'].' and 12/31/'.$_POST['CALENDAR_YEAR'].'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';


		$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $TIMEZONE, date_default_timezone_get());

		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>' . $date . '</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" >ACCT1098'.$_POST['CALENDAR_YEAR'].'</td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of [pagetotal]</i></td>
						</tr>
					</table>';

		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => 35,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 9,
			'orientation' => 'P'
		]);
		$mpdf->autoPageBreak = true;

		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		// echo "CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . "," . $_SESSION['PK_USER'] . ",'" . $PK_1098T_EIN . "', 'Student 1098T Ledger',$PK_STUDENT_MASTER)";exit;
		$res = $db->Execute("CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . "," . $_SESSION['PK_USER'] . ",'" . $PK_1098T_EIN . "', 'Student 1098T Ledger',$PK_STUDENT_MASTER)");
		if ($res->fields['ERROR']) {
			$report_error = $res->fields['ERROR'];
		} else {
			// create generic array for student wise
			$student = array();
			$final_array = array();
			while (!$res->EOF) {
				$student[$res->fields['PK_STUDENT_MASTER']] = $res->fields;
				$final_array[] = $res->fields;
				$res->MoveNext();
			}
			$transaction = array();
			foreach ($student as $k => $v) {
				foreach ($final_array as $val) {
					if ($val['PK_STUDENT_MASTER'] == $k) {
						$transaction[$k][] = $val;
					}
				}
			}
			$db->close();
			$db->connect($db_host, 'root', $db_pass, $db_name);
			foreach ($student as $key => $row) {
				$mpdf->AddPage('', '', 1);

				$mpdf->AliasNbPageGroups('[pagetotal]');

				$PK_STUDENT_MASTER 		= $row['PK_STUDENT_MASTER'];
				$PK_STUDENT_ENROLLMENT  = $row['PK_STUDENT_ENROLLMENT'];
				$StudentName  			= $row['STUDENT_NAME'];
				$CODE  			= $row['PROGRAM_CODE'];
				$WillNotReceive1098T	= $row['WillNotReceive1098T'];

				$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,
				STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, STUDENT_ID, LEAD_SOURCE, FUNDING, 
				IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,
				IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DATE_OF_BIRTH, 
				IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS  EXPECTED_GRAD_DATE,
				IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE, '%m/%d/%Y' )) AS  GRADE_DATE,
				IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS  LDA,
				IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y' )) AS  DETERMINATION_DATE,
				IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE, '%m/%d/%Y' )) AS  DROP_DATE
				FROM 
				S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
				LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
				LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
				LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE 
				LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
				LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
				WHERE 
				S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
				S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 AND
				PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

				$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");

				$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" colspan="4" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" ><b>' . $StudentName . '</b></td>
							</tr>
							<tr>
								<td width="100%" colspan="4" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >Program ' . $res_enroll->fields['CODE'] . ' - ' . $res_enroll->fields['DESCRIPTION'] . '</td>
							</tr>
							<tr>
								<td width="30%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>Student ID:</td>
											<td>' . $res_enroll->fields['STUDENT_ID'] . '</td>
										</tr>
										<tr>
											<td>DOB:</td>
											<td>' . $res_enroll->fields['DATE_OF_BIRTH'] . '</td>
										</tr>
										<tr>
											<td>Phone:</td>
											<td>' . $res_address->fields['CELL_PHONE'] . '</td>
										</tr>
									</table>
								</td>
								<td width="30%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>Status:</td>
											<td>' . $res_enroll->fields['STUDENT_STATUS'] . '</td>
										</tr>
										<tr>
											<td>First Term Date:</td>
											<td>' . $res_enroll->fields['TERM_MASTER'] . '</td>
										</tr>
										<tr>
											<td>Exp. Grad Date:</td>
											<td>' . $res_enroll->fields['EXPECTED_GRAD_DATE'] . '</td>
										</tr>
									</table>
								</td>
								<td width="30%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>Grad Date:</td>
											<td>' . $res_enroll->fields['GRADE_DATE'] . '</td>
										</tr>
										<tr>
											<td>LDA:</td>
											<td>' . $res_enroll->fields['LDA'] . '</td>
										</tr>
										<tr>
											<td>Determination Date:</td>
											<td>' . $res_enroll->fields['DETERMINATION_DATE'] . '</td>
										</tr>
										<tr>
											<td>Drop Date:</td>
											<td>' . $res_enroll->fields['DROP_DATE'] . '</td>
										</tr>
									</table>
								</td>
								<td width="40%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>' . $res_address->fields['ADDRESS'] . '</td>
										</tr>
										<tr>
											<td>' . $res_address->fields['ADDRESS_1'] . '</td>
										</tr>
										<tr>
											<td>' . $res_address->fields['CITY'] . ', ' . $res_address->fields['STATE_CODE'] . ' ' . $res_address->fields['ZIP'] . '<br />' . $res_address->fields['COUNTRY'] . '</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					<br /><br />';

				if ($WillNotReceive1098T != '') {
					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="100%" align="center" ><b style="font-size:20px" >' . $WillNotReceive1098T . '</b></td>
									</tr>
								</table>';
				}

				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<thead>
									<tr>
										<td width="10%" style="border-bottom:1px solid #000;">
											<b><i>Trans Date</i></b>
										</td>
										<td width="17%" style="border-bottom:1px solid #000;">
											<b><i>Ledger Code</i></b>
										</td>
										<td width="17%" style="border-bottom:1px solid #000;">
											<b><i>Description</i></b>
										</td>
										<td width="12%" style="border-bottom:1px solid #000;" align="right">
											<b><i>Debit</i></b>
										</td>
										<td width="12%" style="border-bottom:1px solid #000;" align="right" >
											<b><i>Credit</i></b>
										</td>
										<td width="12%" style="border-bottom:1px solid #000;" align="center">
											<b><i>PYA</i></b>
										</td>
										<td width="20%" style="border-bottom:1px solid #000;">
											<b><i>1098-T Code</i></b>
										</td>
									</tr>
								</thead>';

				$DEBIT  = 0;
				$CREDIT = 0;

				$GRAND_TOTAL  			= 0;
				$PYA_TOTAL  			= 0;
				$GRANT_TOTAL  			= 0;
				$SCHOLARSHIP_TOTAL  	= 0;
				$INSURER_TOTAL  		= 0;
				$_1098_T_TOTAL  		= 0;
				$NON_QUALIFIED_TOTAL  	= 0;



				foreach ($transaction[$PK_STUDENT_MASTER] as $tran => $t) {


					$DEBIT  += $t['DEBIT'];
					$CREDIT += $t['CREDIT'];

					$GRAND_TOTAL  			+= $t['TotalAmt'];
					$PYA_TOTAL  			+= $t['PYAAmt'];
					$GRANT_TOTAL  			+= $t['GrantAmt'];
					$SCHOLARSHIP_TOTAL  	+= $t['ScholarshipAmt'];
					$INSURER_TOTAL  		+= $t['TuitionFromInsurerAmt'];
					$_1098_T_TOTAL  		+= $t['_1098TAmt'];
					$NON_QUALIFIED_TOTAL  	+= $t['NonQualifiedFeeAmt'];

					if ($GRAND_TOTAL < 0.0001) {
						$GRAND_TOTAL1 = '0.000';
					} else {
						$GRAND_TOTAL1 = $GRAND_TOTAL;
					}

					$txt .= '<tr>
								<td>' . ($t['TRANSACTION_DATE'] ? date('m/d/Y', strtotime($t['TRANSACTION_DATE'])) : '') . '</td>
								<td>' . $t['LEDGER_CODE'] . '</td>
								<td>' . $t['Description'] . '</td>
								<td align="right" >$' . number_format_value_checker($t['DEBIT'], 2) . '</td>
								<td align="right" >$' . number_format_value_checker($t['CREDIT'], 2) . '</td>
								<td align="center" >' . (($t['PYAAmt'] != "0.00") ? 'Y' : '') . '</td>
								<td>' . $t['_1098TCode'] . '</td>
							</tr>';
					//$res_1->MoveNext();


				}

				//exit;
				$txt .= '<tr>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" align="right" >$' . number_format_value_checker($DEBIT, 2) . '</td>
							<td style="border-top:1px solid #000;" align="right" >$' . number_format_value_checker($CREDIT, 2) . '</td>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" ></td>
						</tr>
					</table>
					<br />
					<br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td Width="100%" >
								<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><u><b>Grand Total</b></u><br />
											$' . number_format_value_checker($GRAND_TOTAL1, 2) . '
										</td>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><u><b>PYA Total</b></u><br />
											$' . number_format_value_checker($PYA_TOTAL, 2) . '
										</td>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><u><b>Grant Total</b></u><br />
											$' . number_format_value_checker($GRANT_TOTAL, 2) . '
										</td>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<u><b>Scholarship Total</b></u><br />
											$' . number_format_value_checker($SCHOLARSHIP_TOTAL, 2) . '
										</td>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<u><b>Tuition from Insurer Total</b></u><br />
											$' . number_format_value_checker($INSURER_TOTAL, 2) . '
										</td>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><u><b>1098-T Total</b></u><br />
											$' . number_format_value_checker($_1098_T_TOTAL, 2) . '
										</td>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<u><b>Non-Qualified Fee Total</b></u><br />
											$' . number_format_value_checker($NON_QUALIFIED_TOTAL, 2) . '
										</td>
									</tr>
									<tr>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;">Box 1</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 4</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 5</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 5</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 10</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" ></td>
										<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" ></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
				//echo $txt;
				//exit;
				$mpdf->WriteHTML($txt);
				//$res->MoveNext();
			}
			$mpdf->Output('Student Ledgers.pdf', 'D');
		}
	} else if ($_POST['FORMAT'] == 2) { // 1098T Form
		$error_flag_format_2 = false;
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
			}
			public function Footer()
			{
			}
		}

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(21.5, 15, 9.5);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);

		$res_setup = $db->Execute("select CORRECTED, CHANGED_REPORTING_METHOD from _1098T_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$res = $db->Execute("CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . ",0,'" . $PK_1098T_EIN . "', 'Student 1098T Form',$PK_STUDENT_MASTER)");
		
		if ($res->fields['ERROR']) {
			$report_error = $res->fields['ERROR'];
		} else {
			while (!$res->EOF && $error_flag_format_2 == false) {

				$pdf->AddPage();
				$txt 	= '';

				$SSN 		= $res->fields['SSN_Encrypted'];
				$SSN_DE1  	= my_decrypt('', $SSN);

				$student_ssn_format_2 = '';
				$student_ssn_format_2 = str_replace('_','',str_replace('-' , ''  , my_decrypt($_SESSION['PK_ACCOUNT'] ,  $res->fields['SSN_Encrypted'])));
 
				if(strlen($student_ssn_format_2) != 9 || !is_numeric($student_ssn_format_2)){
					$report_error = "ERROR DETECTED:  Run the Student 1098T ERROR REPORT";
					$error_flag_format_2 = true;
				}

				if ($SSN != '') {
					$len = strlen($SSN_DE1);
					$_4 = $len - 1;
					$_3 = $len - 2;
					$_2 = $len - 3;
					$_1 = $len - 4;
					$SSN_DE = 'XXX-XX-' . $SSN_DE1[$_1] . $SSN_DE1[$_2] . $SSN_DE1[$_3] . $SSN_DE1[$_4];
				}

				// DVB 27 02 2025
				if($_SESSION['PK_ACCOUNT'] == 54){
					$SSN_DE = $SSN_DE1;
				}

				if ($res->fields['CORRECTED'] == 1 || $res_setup->fields['CORRECTED'] == 1)
					$corrected_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
				else
					$corrected_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';

				if ($res->fields['Box3'] == 1 || $res_setup->fields['CHANGED_REPORTING_METHOD'] == 1)
					$box3_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
				else
					$box3_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';

				if ($res->fields['Box7'] == 1)
					$box7_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
				else
					$box7_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';

				if ($res->fields['Box8'] == 1)
					$box8_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
				else
					$box8_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';

				if ($res->fields['Box9'] == 1)
					$box9_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
				else
					$box9_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';

				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
						<tr>
							<td width="40%" align="right" >
								' . $corrected_img . '
							</td>
							<td width="50%" >
								<b style="font-size:35px;line-height:6px" >CORRECTED</b>
							</td>
						</tr>
						<tr>
							<td width="100%" >
								<table border="0" cellspacing="0" cellpadding="0" width="100%" >
									<tr>
										<td width="46%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px" >
															FILER\'S name, street address, city or town, state or province, country, ZIP or foreign postal code, and telephone number
														</div>
														
														<div style="font-size:25px;line-height:6px;" >
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $res->fields['SCHOOL_NAME'] . '<br />
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $res->fields['SchoolAddress1'] . ' ' . $res->fields['SchoolAddress2'] . '<br />
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $res->fields['SchoolCSZ'] . '<br />
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $res->fields['SchoolPhone'] . '
														</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;">
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >1</b>
													</td>
													<td width="85%" >
														<div style="font-size:22px;" >
															Payments received for<br />
															qualified tuition and<br />
															related expenses
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="right"  style="border-bottom:1px solid #000;" >
														<div style="font-size:25px;" >$' . number_format_value_checker($res->fields['Box1'], 2) . '</div>
													</td>
												</tr>
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >2</b>
													</td>
													<td width="85%" >
														<div style="font-size:25px;" >' . $res->fields['Box2'] . '</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" align="center" >
														<div style="font-size:25px;" >OMB No. 1545-1574</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<b style="font-size:80px;line-height:6px" >'.$_POST['CALENDAR_YEAR'].'</b>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<b style="font-size:40px;" >Form 1098-T</b>
													</td>
												</tr>
											</table>
										</td>
										<td width="17%" align="right" >
											<br /><br /><br />
											<b style="font-size:45px;" >Tuition Statement</b>
										</td>
									</tr>
									<tr>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px;" >
															FILER\'S employer identification no.
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<div style="font-size:32px;" >' . $res->fields['FEDERAL_ID_NO'] . '</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px;" >
															STUDENT\'S TIN
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<div style="font-size:32px;" >' . $SSN_DE . '</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="38%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="8%" >
														<b style="font-size:30px;" >3</b>
													</td>
													<td width="75%" >
														<div style="font-size:22px;" >
															
														</div>
													</td>
													<td width="15%" align="right" >&nbsp;</td>
												</tr>
											</table>
										</td>
										<td width="17%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" rowspan="3" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<b style="font-size:45px;" >Copy B<br />For Student</b>
														<br /><br />
														<div style="font-size:22px;" >
															This is important<br />
															tax information<br />
															and is being<br />
															furnished to the<br />
															IRS. This form<br />
															must be used to<br />
															complete Form 8863<br />
															to claim education<br />
															credits. Give it to the<br />
															tax preparer or use it to<br />
															prepare the tax return.
														</div>
													</td>
												</tr>
											</table>	
										</td>
									</tr>
									
									<tr>
										<td width="46%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%">
														<div style="font-size:22px;" >
															STUDENT\'S name
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" height="23px">
														<div style="font-size:32px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $res->fields['Sort'] . '</div>
														
													</td>
												</tr>
												<tr>
													<td width="100%" style="border-top:1px solid #000;">
														<div style="font-size:22px;" >
															Street address (including apt. no.)
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" height="23px">
														<div style="font-size:32px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $res->fields['StudentAddress1'] . ' ' . $res->fields['StudentAddress2'] . '</div>
														
													</td>
												</tr>
												<tr>
													<td width="100%" style="border-top:1px solid #000;" >
														<div style="font-size:22px;" >
															City or town, state or province, country, and ZIP or foreign postal code
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" height="23px">
														<div style="font-size:32px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $res->fields['StudentCSZ'] . '</div>
														
													</td>
												</tr>
											</table>
										</td>
										<td width="38%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="8%" >
														<b style="font-size:30px;" >4</b>
													</td>
													<td width="42%" >
														<div style="font-size:22px;" >
															Adjustments made for a<br />
															prior year
														</div>
													</td>
													<td width="8%" style="border-left:1px solid #000;" >
														<b style="font-size:30px;" >5</b>
													</td>
													<td width="42%" >
														<div style="font-size:22px;" >
															Scholarships or grants
														</div>
													</td>
												</tr>
												<tr>
													<td width="50%" align="right" style="border-bottom:1px solid #000;" >
														<div style="font-size:25px;" >$' . number_format_value_checker($res->fields['Box4'], 2) . '</div>
													</td>
													<td width="50%" align="right" style="border-bottom:1px solid #000;border-left:1px solid #000;" >
														<div style="font-size:25px;" >$' . number_format_value_checker($res->fields['Box5'], 2) . '</div>
													</td>
												</tr>
												
												<tr>
													<td width="8%" style="border-left:1px solid #000;" >
														<b style="font-size:30px;" >6</b>
													</td>
													<td width="42%" >
														<table border="0" cellspacing="0" cellpadding="3" width="100%" >
															<tr>
																<td width="100%" >
																	<div style="font-size:22px;" >
																		Adjustments to scholarships<br />
																		or grants for a prior year
																	</div>
																</td>
															</tr>
															<tr>
																<td width="100%" align="right" >
																	<div style="font-size:25px;" >$' . number_format_value_checker($res->fields['Box6'], 2) . '</div>
																</td>
															</tr>
														</table>
													</td>
													<td width="8%" style="border-left:1px solid #000;" >
														<b style="font-size:30px;" >7</b>
													</td>
													<td width="42%" >
														<table border="0" cellspacing="0" cellpadding="0" width="100%" >
															<tr>
																<td width="100%" >
																	<div style="font-size:22px;" >
																		Checked if the amount<br />
																		in box 1 includes<br />
																		amounts for an<br />
																		academic period
																	</div>
																</td>
															</tr>
															<tr>
																<td width="60%" >
																	<div style="font-size:22px;" >
																		beginning January-<br />
																		March '.($_POST['CALENDAR_YEAR']+1).'
																	</div>
																</td>
																<td width="40%" align="right" >' . $box7_img . '&nbsp;</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									
									<tr>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px;" >
															Service Provider/Acct. No. (see instr.)
														</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="10%" >
														<b style="font-size:30px;" >8</b>
													</td>
													<td width="65%" >
														<div style="font-size:22px;" >
															Checked if at least<br />
															half-time student
														</div>
													</td>
													<td width="25%" align="right" >' . $box8_img . '&nbsp;</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="2" width="100%" >
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >9</b>
													</td>
													<td width="60%" >
														<div style="font-size:22px;" >
															Checked if a<br />
															graduate student
														</div>
													</td>
													<td width="25%" align="right" >' . $box9_img . '&nbsp;</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="2" width="100%" >
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >10</b>
													</td>
													<td width="85%" >
														<div style="font-size:22px;" >
															Ins. contract reimb./refund
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="right" >
														<div style="font-size:25px;" >$' . number_format_value_checker($res->fields['Box10'], 2) . '</div>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									
									<tr>
										<td width="23%" style="border-top:1px solid #000;" >
											<b style="font-size:30px;line-height:6px" >Form 1098-T</b>
										</td>
										<td width="23%" style="border-top:1px solid #000;" >
											<div style="line-height:6px" >(keep for your records)</div>
										</td>
										<td width="19%" style="border-top:1px solid #000;" >
											<div style="line-height:6px" >www.irs.gov/Form1098T</div>
										</td>
										<td width="36%" style="border-top:1px solid #000;" align="right" >
											<div style="line-height:6px" >Department of the Treasury - Internal Revenue Service</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						
						<tr>
							<td width="51%" >
								<br /><br /><b style="font-size:35px;line-height:6px" >Instructions for Student</b><br />
								
								<div style="line-height:5px" >
									You, or the person who can claim you as a dependent, may be able to claim
									an education credit on Form 1040 or 1040-SR. This statement has been
									furnished to you by an eligible educational institution in which you are enrolled, or by an insurer who makes reimbursements or refunds of qualified tuition and related expenses to you. This statement is required to support any claim for an education credit. Retain this statement for your records. To see if you qualify for a credit, and for help in calculating the amount of your credit, see Pub. 970, Form 8863, and the Instructions for Form 1040. Also, for more information, go to www.irs.gov/Credits-Deductions/Individuals/Qualified-Ed-Expenses and https://www.irs.gov/Education.<br />
									&nbsp;&nbsp;Your institution must include its name, address, and information contact telephone number on this statement. It may also include contact information for a service provider. Although the filer or the service provider may be able to answer certain questions about the statement, do not contact the filer or the service provider for explanations of the requirements for (and how to figure) any education credit that you may claim. <b><br />Student\'s taxpayer identification number (TIN).</b> For your protection, this form may show only the last four digits of your TIN (SSN, ITIN, ATIN, or EIN). However, the issuer has reported your complete TIN to the IRS. <b>Caution:</b> If your TIN is not shown in this box, your school was not able to provide it. Contact your school if you have questions.<br />
									
									<b>Account number.</b> May show an account or other unique number the filer assigned to distinguish your account.<br />
									
									<b>Box 1.</b> Shows the total payments received by an eligible educational institution in '.$_POST['CALENDAR_YEAR'].' from any source for qualified tuition and related expenses less any reimbursements or refunds made during '.$_POST['CALENDAR_YEAR'].' that relate to those payments received during '.$_POST['CALENDAR_YEAR'].'.<br />
									
									<b>Box 2.</b> Reserved for future use.<br />
									
									<b>Box 3.</b> Reserved for future use.<br />
									
									<b>Box 4.</b> Shows any adjustment made by an eligible educational institution for a prior year for qualified tuition and related expenses that were reported on a prior year Form 1098-T. This amount may reduce any allowable education credit that you claimed for the prior year (may result in an increase in tax liability for the year of the refund). See "recapture" in the index to Pub. 970 to report a reduction in your education credit or tuition and fees deduction.
									<br /><br /><br /><br /><br /><br /><br />
									<div style="font-size:32px;" >
									' . $res->fields['Sort'] . '<br />
									' . $res->fields['StudentAddress1'] . ' ' . $res->fields['StudentAddress2'] . '<br />
									' . $res->fields['StudentCSZ'] . '
									</div>
								</div>
							</td>
							<td width="1%" >
							</td>
							<td width="48%" >
								<br />
								<div style="line-height:5px" >
									<b>Box 5.</b> Shows the total of all scholarships or grants administered and processed by the eligible educational institution. The amount of scholarships or grants for the calendar year (including those not reported by the institution) may reduce the amount of the education credit you claim for the year.<br />
									
									<b>TIP:</b> You may be able to increase the combined value of an education credit and certain educational assistance (including Pell Grants) if the student includes some or all of the educational assistance in income in the year it is received. For details, see Pub. 970.<br />
									
									<b>Box 6.</b> Shows adjustments to scholarships or grants for a prior year. This amount may affect the amount of any allowable tuition and fees deduction or education credit that you claimed for the prior year. You may have to file an amended income tax return (Form 1040-X) for the prior year.<br />
									
									<b>Box 7.</b> Shows whether the amount in box 1 includes amounts for an academic period beginning January–March '.($_POST['CALENDAR_YEAR'] + 1 ).'. See Pub. 970 for how to report these amounts.<br />
									
									<b>Box 8.</b> Shows whether you are considered to be carrying at least one-half the normal full-time workload for your course of study at the reporting institution.<br />
									
									<b>Box 9.</b> Shows whether you are considered to be enrolled in a program leading to a graduate degree, graduate-level certificate, or other recognized graduate-level educational credential.<br />
									
									<b>Box 10.</b> Shows the total amount of reimbursements or refunds of qualified tuition and related expenses made by an insurer. The amount of reimbursements or refunds for the calendar year may reduce the amount of any education credit you can claim for the year (may result in an increase in tax liability for the year of the refund).<br />
									
									<b>Future developments.</b> For the latest information about developments related to Form 1098-T and its instructions, such as legislation enacted after they were published, go to www.irs.gov/Form1098T.<br />
									
									<b>FreeFile.</b> Go to www.irs.gov/FreeFile to see if you qualify for no-cost online federal tax preparation, e-filing, and direct deposit or payment options.
								</div>
							</td>
						</tr>
					</table>';

				//echo $txt;exit;
				$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

				$res->MoveNext();
			}
			if($error_flag_format_2 == false){
				$file_name = '1098T Forms.pdf';
				/*if($browser == 'Safari')
				$pdf->Output('temp/'.$file_name, 'FD');
			else	
				$pdf->Output($file_name, 'I');*/
	
				$pdf->Output('temp/' . $file_name, 'FD');
				return $file_name;
			}
			
		}
	} else if ($_POST['FORMAT'] == 3) // Create Electronic File.
	{
		// DIAM - 69
		$error_flag_format_3 = false;
		$connection = mysqli_connect($db_host, 'root', $db_pass, $db_name);
		$sql_b_total_rec = "CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . ",0,'" . $PK_1098T_EIN . "', 'Electronic Form Part B',$PK_STUDENT_MASTER)";
		$query_b_rec = mysqli_query($connection, $sql_b_total_rec);
		$_SESSION['Total_Records_Part_B'] = mysqli_num_rows($query_b_rec);

		$txt 	= '';
		/* Record Name: Transmitter “T” Record */

		//$res = $db->Execute("CALL ACCT10982022(".$_SESSION['PK_ACCOUNT'].",0,'".$PK_1098T_EIN."', 'Electronic Form Part T',$PK_STUDENT_MASTER)");
		$i = 1;
		$sQuery_Record_T = "SELECT 'T' AS RECORD_TYPE, _1098T_EIN.TRANSMITTER_CONTROL_CODE,
									_1098T_EIN.PK_1098T_EIN AS PK_1098T_EIN,
									_1098T_EIN.EIN_NO AS FEDERAL_ID_NO,
									_1098T_EIN.TRANSMITTER_NAME AS TRANS_NAME,
									_1098T_EIN.TRANSMITTER AS TRANSMITTER,
									_1098T_EIN.COMPANY_ISSUER_NAME AS COMPANY_ISSUER_NAME,
									_1098T_EIN.ADDRESS AS CompanyAddress1,
									_1098T_EIN.ADDRESS_1 AS CompanyAddress2,
									_1098T_EIN.CITY AS CITY,
									Z_STATES.STATE_CODE AS STATE,
									_1098T_EIN.ZIP AS ZIP,
									_1098T_EIN.CONTACT_NAME AS ContactName,
									_1098T_EIN.CONTACT_PHONE AS ContactPhone,
									_1098T_EIN.CONTACT_EMAIL AS ContactEmail,
									'V' AS VendorIndicator,
									'DIAMOND SIS' AS VendorName,
									'2625 Townsgate Road, Suite 330' AS VendorAddress,
									'Westlake Village' AS VendorCity,
									'CA' AS VendorState,
									'91361' AS VendorZip,
									'JIM QUEEN' AS VendorContactName,
									'2135452829' AS VendorPhone
									FROM _1098T_EIN JOIN Z_STATES ON Z_STATES.PK_STATES = _1098T_EIN.PK_STATES WHERE TRANSMITTER='1' AND _1098T_EIN.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'";
		$res = $db->Execute($sQuery_Record_T);
		$PK_1098T_EIN_ID = $res->fields['PK_1098T_EIN'];
		$total_count = $res->RecordCount();
		if ($total_count == '0') {
			$sQuery_Record_T = "SELECT 'T' AS RECORD_TYPE, _1098T_EIN.TRANSMITTER_CONTROL_CODE,
									_1098T_EIN.PK_1098T_EIN AS PK_1098T_EIN,
									_1098T_EIN.EIN_NO AS FEDERAL_ID_NO,
									_1098T_EIN.TRANSMITTER_NAME AS TRANS_NAME,
									_1098T_EIN.TRANSMITTER AS TRANSMITTER,
									_1098T_EIN.COMPANY_ISSUER_NAME AS COMPANY_ISSUER_NAME,
									_1098T_EIN.ADDRESS AS CompanyAddress1,
									_1098T_EIN.ADDRESS_1 AS CompanyAddress2,
									_1098T_EIN.CITY AS CITY,
									Z_STATES.STATE_CODE AS STATE,
									_1098T_EIN.ZIP AS ZIP,
									_1098T_EIN.CONTACT_NAME AS ContactName,
									_1098T_EIN.CONTACT_PHONE AS ContactPhone,
									_1098T_EIN.CONTACT_EMAIL AS ContactEmail,
									'V' AS VendorIndicator,
									'DIAMOND SIS' AS VendorName,
									'2625 Townsgate Road, Suite 330' AS VendorAddress,
									'Westlake Village' AS VendorCity,
									'CA' AS VendorState,
									'91361' AS VendorZip,
									'JIM QUEEN' AS VendorContactName,
									'2135452829' AS VendorPhone
									FROM _1098T_EIN JOIN Z_STATES ON Z_STATES.PK_STATES = _1098T_EIN.PK_STATES WHERE _1098T_EIN.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' limit 1";
			$res = $db->Execute($sQuery_Record_T);
			$PK_1098T_EIN_ID = $res->fields['PK_1098T_EIN'];
			$total_count = $res->RecordCount();
		}
		while (!$res->EOF) {
			$Calendar_Year = $_POST['CALENDAR_YEAR'];
			$Record_Type = $res->fields['RECORD_TYPE'];
			$TIN_NO_REC  = $res->fields['FEDERAL_ID_NO'];
			$TIN_NO  = preg_replace('/[^A-Za-z0-9 ]/', '', $TIN_NO_REC);
			$Trans_CC = $res->fields['TRANSMITTER_CONTROL_CODE'];
			$Trans_Name = strtoupper($res->fields['TRANS_NAME']);
			$COMP_ISSUER_NAME = strtoupper($res->fields['COMPANY_ISSUER_NAME']);
			$Address1 = $res->fields['CompanyAddress1'];
			$Address2 = $res->fields['CompanyAddress2'];
			$CITY = strtoupper($res->fields['CITY']);
			$STATE = $res->fields['STATE'];
			$ZIP = $res->fields['ZIP'];
			$Contact_Name = strtoupper($res->fields['ContactName']);
			$Contact_Phone_Rec = $res->fields['ContactPhone'];
			$string  = preg_replace('/[^A-Za-z0-9 ]/', '', $Contact_Phone_Rec);
			$Contact_Phone = str_replace(' ', '', $string);
			$Contact_Email = strtoupper($res->fields['ContactEmail']);
			$VendorIndicator = $res->fields['VendorIndicator'];
			$VendorName = $res->fields['VendorName'];
			$VendorAddress = $res->fields['VendorAddress'];
			$VendorCity = $res->fields['VendorCity'];
			$VendorState = $res->fields['VendorState'];
			$VendorZip = $res->fields['VendorZip'];
			$VendorContactName = $res->fields['VendorContactName'];
			$VendorPhone = $res->fields['VendorPhone'];

			$Address = $Address1 . ' ' . $Address2;
			$ISSUER_ADDRESS = strtoupper($Address);

			$Field1 = $Record_Type;
			$Field3 = '';
			$Field4 = $TIN_NO;
			$Field5 = $Trans_CC;
			$Field6 = '';
			$Field7 = '';
			$Field8 = '';
			$Field9 = $Trans_Name;
			$Field10 = '';
			$Field11 = $COMP_ISSUER_NAME;
			$Field12 = '';
			$Field13 = $ISSUER_ADDRESS;
			$Field17 = '';
			$Field18 = $_SESSION['Total_Records_Part_B']; // Remaining Find Data
			$Field22 = '';
			$Field23 = $i; // Remaining Find Data
			$Field24 = '';
			$Field25 = $VendorIndicator;
			$Field26 = $VendorName;
			$Field27 = $VendorAddress;
			$Field28 = $VendorCity;
			$Field29 = $VendorState;
			$Field30 = $VendorZip;
			$Field31 = $VendorContactName;
			$Field32 = $VendorPhone;
			$Field33 = '';
			$Field34 = '';
			$Field35 = '';
			$Field36 = '';

			$sField1 = $Field1; // Field Position 1 (Length - 1)
			$sField2 = $Calendar_Year; // Field Position 2-5 (Length - 4)
			$sField3 = substr(str_pad($Field3, 1, " "), 0, 1); // Field Position 6 (Length - 1)
			$sField4 = substr(str_pad($Field4, 9, " "), 0, 9); // Field Position 7-15 (Length - 9)
			$sField5 = substr(str_pad($Field5, 5, " "), 0, 5); // Field Position 16-20 (Length - 5)
			$sField6 = substr(str_pad($Field6, 7, " "), 0, 7); // Field Position 21-27 (Length - 7)
			$sField7 = substr(str_pad($Field7, 1, " "), 0, 1); // Field Position 28 (Length - 1)
			$sField8 = substr(str_pad($Field8, 1, " "), 0, 1); // Field Position 29 (Length - 1)
			$sField9 = substr(str_pad($Field9, 80, " "), 0, 80); // Field Position 30-109 (Length - 80)
			$sField10 = $Field10; // Field Position 70-109 (Length - 40)
			$sField11 = substr(str_pad($Field11, 80, " "), 0, 80); // Field Position 110-189 (Length - 80)
			$sField12 = $Field12; // Field Position 150-189 (Length - 40)
			$sField13 = substr(str_pad($Field13, 40, " "), 0, 40); // Field Position 190-229 (Length - 40)
			$sField14 = substr(str_pad($CITY, 40, " "), 0, 40); // Field Position 230-269 (Length - 40)
			$sField15 = substr(str_pad($STATE, 2, " "), 0, 2); // Field Position 270-271 (Length - 2)
			$sField16 = substr(str_pad($ZIP, 9, " "), 0, 9); // Field Position 272-280 (Length - 9)
			$sField17 = substr(str_pad($Field17, 15, " "), 0, 15); // Field Position 281-295 (Length - 15)
			$sField18 = substr(str_pad($Field18, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 296-303 (Length - 8)
			$sField19 = substr(str_pad($Contact_Name, 40, " "), 0, 40); // Field Position 304-343 (Length - 40)
			$sField20 = substr(str_pad($Contact_Phone, 15, " "), 0, 15); // Field Position 344-358 (Length - 15)
			$sField21 = substr(str_pad($Contact_Email, 50, " "), 0, 50); // Field Position 359-408 (Length - 50)
			$sField22 = substr(str_pad($Field22, 91, " "), 0, 91); // Field Position 409-499 (Length - 91)
			$sField23 = substr(str_pad($Field23, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 500-507 (Length - 8)
			$sField24 = substr(str_pad($Field24, 10, " "), 0, 10); // Field Position 508-517 (Length - 10)
			$sField25 = substr(str_pad($Field25, 1, " "), 0, 1); // Field Position 518 (Length - 1)
			$sField26 = substr(str_pad($Field26, 40, " "), 0, 40); // Field Position 519-558 (Length - 40)
			$sField27 = substr(str_pad($Field27, 40, " "), 0, 40); // Field Position 559-598 (Length - 40)
			$sField28 = substr(str_pad($Field28, 40, " "), 0, 40); // Field Position 599-638 (Length - 40)
			$sField29 = substr(str_pad($Field29, 2, " "), 0, 2); // Field Position 639-640 (Length - 2)
			$sField30 = substr(str_pad($Field30, 9, " "), 0, 9); // Field Position 641-649 (Length - 9)
			$sField31 = substr(str_pad($Field31, 40, " "), 0, 40); // Field Position 650-689 (Length - 40)
			$sField32 = substr(str_pad($Field32, 15, " "), 0, 15); // Field Position 690-704 (Length - 15)
			$sField33 = substr(str_pad($Field33, 35, " "), 0, 35); // Field Position 705-739 (Length - 35)
			$sField34 = substr(str_pad($Field34, 1, " "), 0, 1); // Field Position 740 (Length - 1)
			$sField35 = substr(str_pad($Field35, 8, " "), 0, 8); // Field Position 741-748 (Length - 8)
			$sField36 = substr(str_pad($Field36, 2, " "), 0, 2); // Field Position 749-750 (Length - 2)

			$txt = $sField1 . $sField2 . $sField3 . $sField4 . $sField5 . $sField6 . $sField7 . $sField8 . $sField9 . $sField10 . $sField11 . $sField12 . $sField13 . $sField14 . $sField15 . $sField16 . $sField17 . $sField18 . $sField19 . $sField20 . $sField21 . $sField22 . $sField23 . $sField24 . $sField25 . $sField26 . $sField27 . $sField28 . $sField29 . $sField30 . $sField31 . $sField32 . $sField33 . $sField34 . $sField35 . $sField36;

			$txt .= "\n";
			if ($i === $total_count) {
				$mydata = $i;
			}

			$i++;

			$res->MoveNext();
		}

		/* End Record Name: Transmitter “T” Record */

		/* Record Name: Issuer “A” Record */
		//$res = $db->Execute("CALL ACCT10982022(".$_SESSION['PK_ACCOUNT'].",0,'".$PK_1098T_EIN."', 'Electronic Form Part A',$PK_STUDENT_MASTER)");
		$j = $mydata + 1;

		// DIAM-2046, condition only for WVJC College.
		if (has_1098t_access($_SESSION['PK_ACCOUNT']))
		{
			$where = ' AND _1098T_EIN.PK_1098T_EIN = "' . $_POST['PK_1098T_EIN'] . '" ';
		}else{
			$where = ' AND TRANSMITTER=0 AND _1098T_EIN.PK_1098T_EIN <> "' . $PK_1098T_EIN_ID . '" ';
		}
		// End DIAM-2046

		$sQuery_Record_A = "SELECT 'A' AS RECORD_TYPE,
									_1098T_EIN.EIN_NO AS FEDERAL_ID_NO,
									_1098T_EIN.TRANSMITTER AS TRANSMITTER,
									_1098T_EIN.COMPANY_ISSUER_NAME AS COMPANY_ISSUER_NAME,
									_1098T_EIN.ADDRESS AS CompanyAddress1,
									_1098T_EIN.ADDRESS_1 AS CompanyAddress2,
									_1098T_EIN.CITY AS CITY,
									Z_STATES.STATE_CODE AS STATE,
									_1098T_EIN.ZIP AS ZIP,
									_1098T_EIN.CONTACT_NAME AS ContactName,
									_1098T_EIN.CONTACT_PHONE AS ContactPhone,
									_1098T_EIN.CONTACT_EMAIL AS ContactEmail
									FROM _1098T_EIN JOIN Z_STATES ON Z_STATES.PK_STATES = _1098T_EIN.PK_STATES WHERE  _1098T_EIN.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $where ";
		$res_type = $db->Execute($sQuery_Record_A);
		$total_count1 = $res_type->RecordCount();
		if ($total_count1 == '0') {
			$sQuery_Record_A = "SELECT 'A' AS RECORD_TYPE,
									_1098T_EIN.EIN_NO AS FEDERAL_ID_NO,
									_1098T_EIN.TRANSMITTER AS TRANSMITTER,
									_1098T_EIN.COMPANY_ISSUER_NAME AS COMPANY_ISSUER_NAME,
									_1098T_EIN.ADDRESS AS CompanyAddress1,
									_1098T_EIN.ADDRESS_1 AS CompanyAddress2,
									_1098T_EIN.CITY AS CITY,
									Z_STATES.STATE_CODE AS STATE,
									_1098T_EIN.ZIP AS ZIP,
									_1098T_EIN.CONTACT_NAME AS ContactName,
									_1098T_EIN.CONTACT_PHONE AS ContactPhone,
									_1098T_EIN.CONTACT_EMAIL AS ContactEmail
									FROM _1098T_EIN JOIN Z_STATES ON Z_STATES.PK_STATES = _1098T_EIN.PK_STATES WHERE _1098T_EIN.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'";
			$res_type = $db->Execute($sQuery_Record_A);
			$total_count1 = $res_type->RecordCount();
		}
		$_SESSION['Total_Records_Part_A'] = $res_type->RecordCount();
		while (!$res_type->EOF) {
			$Calendar_Year = $_POST['CALENDAR_YEAR'];
			$Record_Type = $res_type->fields['RECORD_TYPE'];
			$TIN_NO_REC  = $res_type->fields['FEDERAL_ID_NO'];
			$TIN_NO  = preg_replace('/[^A-Za-z0-9 ]/', '', $TIN_NO_REC);
			$COMP_ISSUER_NAME = $res_type->fields['COMPANY_ISSUER_NAME'];
			$Address1 = $res_type->fields['CompanyAddress1'];
			$Address2 = $res_type->fields['CompanyAddress2'];
			$CITY = strtoupper($res_type->fields['CITY']);
			$STATE = $res_type->fields['STATE'];
			$ZIP = $res_type->fields['ZIP'];
			$Contact_Phone_Rec = $res_type->fields['ContactPhone'];
			$string  = preg_replace('/[^A-Za-z0-9 ]/', '', $Contact_Phone_Rec);
			$Contact_Phone = str_replace(' ', '', $string);

			$strArray = explode(' ', $COMP_ISSUER_NAME);
			$NameControl = strtoupper(substr($strArray[1], 0, 4));

			$TypeReturn  = '8'; // Type of Return - 1098-T
			$AmountCodes = '13457'; // Amount Codes - 1098-T
			$ISSUER_NAME = strtoupper($COMP_ISSUER_NAME);

			$Address = $Address1 . ' ' . $Address2;
			$ISSUER_ADDRESS = strtoupper($Address);

			$AField1 = $Record_Type;
			$AField3 = '';
			$AField4 = '';
			$AField5 = $TIN_NO;
			$AField6 = $NameControl;
			$AField7 = '';
			$AField8 = $TypeReturn;
			$AField9 = $AmountCodes;
			$AField10 = '';
			$AField11 = '';
			$AField12 = $ISSUER_NAME;
			$AField13 = '';
			$AField14 = '0';
			$AField15 = $ISSUER_ADDRESS;
			$AField20 = '';
			$AField21 = $j; // Remaining Find Data
			$AField22 = '';
			$AField23 = '';

			$sAField1 = $AField1; // Field Position 1 (Length - 1)
			$sAField2 = $Calendar_Year; // Field Position 2-5 (Length - 4)
			$sAField3 = substr(str_pad($AField3, 1, " "), 0, 1); // Field Position 6 (Length - 1)
			$sAField4 = substr(str_pad($AField4, 5, " "), 0, 5); // Field Position 7-11 (Length - 5)
			$sAField5 = substr(str_pad($AField5, 9, "0"), 0, 9); // Field Position 12-20 (Length - 9)
			$sAField6 = substr(str_pad($AField6, 4, " "), 0, 4); // Field Position 21-24 (Length - 4)
			$sAField7 = substr(str_pad($AField7, 1, " "), 0, 1); // Field Position 25 (Length - 1)
			$sAField8 = substr(str_pad($AField8, 2, " "), 0, 2); // Field Position 26-27 (Length - 2)
			$sAField9 = substr(str_pad($AField9, 18, " "), 0, 18); // Field Position 28-45 (Length - 18)
			$sAField10 = substr(str_pad($AField10, 6, " "), 0, 6); // Field Position 46-51 (Length - 6)
			$sAField11 = substr(str_pad($AField11, 1, " "), 0, 1); // Field Position 52 (Length - 1)
			$sAField12 = substr(str_pad($AField12, 80, " "), 0, 80); // Field Position 53-132 (Length - 80)
			$sAField13 = $AField13; // Field Position 93-132 (Length - 40)
			$sAField14 = substr(str_pad($AField14, 1, " "), 0, 1); // Field Position 133 (Length - 1)
			$sAField15 = substr(str_pad($AField15, 40, " "), 0, 40); // Field Position 134-173 (Length - 40)
			$sAField16 = substr(str_pad($CITY, 40, " "), 0, 40); // Field Position 174-213 (Length - 40)
			$sAField17 = substr(str_pad($STATE, 2, " "), 0, 2); // Field Position 214-215 (Length - 2)
			$sAField18 = substr(str_pad($ZIP, 9, " "), 0, 9); // Field Position 216-224 (Length - 9)
			$sAField19 = substr(str_pad($Contact_Phone, 15, " "), 0, 15); // Field Position 225-239 (Length - 15)
			$sAField20 = substr(str_pad($AField20, 260, " "), 0, 260); // Field Position 240-499 (Length - 260)
			$sAField21 = substr(str_pad($AField21, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 500-507 (Length - 8)
			$sAField22 = substr(str_pad($AField22, 241, " "), 0, 241); // Field Position 508-748 (Length - 241)
			$sAField23 = substr(str_pad($AField23, 2, " "), 0, 2); // Field Position 749-750 (Length - 2)


			$txt .= $sAField1 . $sAField2 . $sAField3 . $sAField4 . $sAField5 . $sAField6 . $sAField7 . $sAField8 . $sAField9 . $sAField10 . $sAField11 . $sAField12 . $sAField13 . $sAField14 . $sAField15 . $sAField16 . $sAField17 . $sAField18 . $sAField19 . $sAField20 . $sAField21 . $sAField22 . $sAField23;

			$txt .= "\n";

			if ($j - 1 === $total_count1) {
				$mydata1 = $j;
			}

			$j++;

			$res_type->MoveNext();
		}
		/* End Record Name: Issuer “A” Record */

		/* Record Name: Issuer “B” Record */
		$conn = mysqli_connect($db_host, 'root', $db_pass, $db_name);
		$k = $mydata1 + 1;
		$sql_b = "CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . ",0,'" . $PK_1098T_EIN . "', 'Electronic Form Part B',$PK_STUDENT_MASTER)";

		$query_b = mysqli_query($conn, $sql_b);
		//$_SESSION['Total_Records_Part_B'] = mysqli_num_rows($query_b);
		$total_count2 = mysqli_num_rows($query_b);
		while ($row = mysqli_fetch_assoc($query_b)) {
			$Calendar_Year = $_POST['CALENDAR_YEAR'];
			$RECORD_TYPE = $row['RECORD_TYPE'];
			$SSN 		 = $row['SSN_Encrypted'];
			$SSN_VERIFIED = $row['SSN_VERIFIED'];
			$SSN_TIN_REC = my_decrypt('', $SSN);
			$SSN_TIN     = preg_replace('/[^A-Za-z0-9 ]/', '', $SSN_TIN_REC);

			$student_ssn_format_3 = '';
			$student_ssn_format_3 = str_replace('_','',str_replace('-' , ''  , my_decrypt($_SESSION['PK_ACCOUNT'] ,  $SSN)));

			if(strlen($student_ssn_format_3) != 9 || !is_numeric($student_ssn_format_3)){
				$report_error = "ERROR DETECTED:  Run the Student 1098T ERROR REPORT";
				$error_flag_format_3 = true;
			}

			$Last_Name  = $row['LAST_NAME'];
			$string_rec = str_replace(' ', '', $Last_Name);
			$Name = preg_replace('/[^A-Za-z0-9 ]/', '', $string_rec);
			$NameControl = strtoupper(substr($Name, 0, 4));

			$TIN_NO = $row['FEDERAL_ID_NO'];
			$Trans_CC = $row['TRANSMITTER_CONTROL_CODE'];
			$COMP_ISSUER_NAME = $row['COMPANY_ISSUER_NAME'];
			$Trans_Name = $row['TRANS_NAME'];
			$Address1 = $row['SchoolAddress1'];
			$Address2 = $row['SchoolAddress2'];
			$CITY = $row['CITY'];
			$STATE = $row['STATE'];
			$ZIP = $row['ZIP'];
			$CITY_STATE_ZIP = $row['SchoolCSZ'];
			$Contact_Name = $row['SchoolName'];
			$Contact_Phone = $row['SchoolPhone'];
			$Contact_Email = $row['SchoolEmail'];
			$Student_halftime = $row['halftime'];
			$Student_future_tuition = $row['future_tuition'];
			$Student_graduate = $row['graduate'];
			$TYPE_TIN_SSN = $row['TYPE_TIN_SSN'];
			$STUD_Add1 = $row['StudentAddress1'];
			$STUD_Add2 = $row['StudentAddress2'];
			$STUD_CSZ = $row['StudentCSZ'];
			$StudentCity = $row['StudentCity'];
			$StudentState = $row['StudentState'];
			$StudentZip = $row['StudentZip'];
			$ACCOUNT_NUMBER = $row['ACCOUNT_NUMBER'];
			$PAYMENT_AMOUNT_1 = round($row['PAYMENT_AMOUNT_1'], 0);
			$PAYMENT_AMOUNT_3 = round($row['PAYMENT_AMOUNT_3'], 0);
			$PAYMENT_AMOUNT_4 = round($row['PAYMENT_AMOUNT_4'], 0);
			$PAYMENT_AMOUNT_5 = round($row['PAYMENT_AMOUNT_5'], 0);
			$PAYMENT_AMOUNT_7 = round($row['PAYMENT_AMOUNT_7'], 0);
			$STUDENT_NAME = strtoupper($row['STUDENT_NAME']);

			$BField1 = $RECORD_TYPE;
			$BField3 = '';
			$BField4 = $NameControl;
			$BField5 = $TYPE_TIN_SSN;
			$BField6 = $SSN_TIN;
			$BField7 = $ACCOUNT_NUMBER;
			$BField8 = '';
			$BField9 = '';
			$BField10 = $PAYMENT_AMOUNT_1;
			$BField11 = '';
			$BField12 = $PAYMENT_AMOUNT_3;
			$BField13 = $PAYMENT_AMOUNT_4;
			$BField14 = $PAYMENT_AMOUNT_5;
			$BField15 = '';
			$BField16 = $PAYMENT_AMOUNT_7;
			$BField17 = '';
			$BField18 = '';
			$BField19 = '';
			$BField20 = '';
			$BField21 = '';
			$BField22 = '';
			$BField23 = '';
			$BField24 = '';
			$BField25 = '';
			$BField26 = '';
			$BField27 = '';
			$BField28 = '';
			$BField29 = '';
			$BField30 = $STUDENT_NAME;
			$BField31 = '';
			$BField32 = $STUD_Add1 . ' ' . $STUD_Add2;
			$BField33 = '';
			$BField34 = $StudentCity;
			$BField35 = $StudentState;
			$BField36 = $StudentZip;
			$BField37 = '';
			$BField38 = $k; // Remaining Find Data
			$BField39 = '';
			if ($SSN_VERIFIED == '1') {
				$BField40 = '1';
			} else {
				$BField40 = '';
			}

			$BField41 = '';
			if ($Student_halftime == '1') {
				$BField42 = '1';
			} else {
				$BField42 = '';
			}

			if ($Student_graduate == '1') {
				$BField43 = '1';
			} else {
				$BField43 = '';
			}
			if ($Student_future_tuition == '1') {
				$BField44 = '1';
			} else {
				$BField44 = '';
			}

			$BField45 = '';
			$BField46 = '';
			$BField47 = '';
			$BField48 = '';
			$BField49 = '';

			$sBField1 = $BField1; // Field Position 1 (Length - 1)
			$sBField2 = $Calendar_Year; // Field Position 2-5 (Length - 4)
			$sBField3 = substr(str_pad($BField3, 1, " "), 0, 1); // Field Position 6 (Length - 1)
			$sBField4 = substr(str_pad($BField4, 4, " "), 0, 4); // Field Position 7-10 (Length - 4)
			$sBField5 = substr(str_pad($BField5, 1, " "), 0, 1); // Field Position 11 (Length - 1)
			$sBField6 = substr(str_pad($BField6, 9, " "), 0, 9); // Field Position 12-20 (Length - 9)
			$sBField7 = substr(str_pad($BField7, 20, " "), 0, 20); // Field Position 21-40 (Length - 20)
			$sBField8 = substr(str_pad($BField8, 4, " "), 0, 4); // Field Position 41-44 (Length - 4)
			$sBField9 = substr(str_pad($BField9, 10, " "), 0, 10); // Field Position 45-54 (Length - 10)
			$sBField10 = '+' . substr(str_pad($BField10, 11, "0", STR_PAD_LEFT), 0, 12); // Field Position 55-66 (Length - 12)
			$sBField11 = substr(str_pad($BField11, 12, "0"), 0, 12); // Field Position 67-78 (Length - 12)
			$sBField12 = substr(str_pad($BField12, 12, "0", STR_PAD_LEFT), 0, 12); // Field Position 79-90  (Length - 12)
			$sBField13 = substr(str_pad($BField13, 12, "0", STR_PAD_LEFT), 0, 12); // Field Position 91-102 (Length - 12)
			$sBField14 = substr(str_pad($BField14, 12, "0", STR_PAD_LEFT), 0, 12); // Field Position 103-114 (Length - 12)
			$sBField15 = substr(str_pad($BField15, 12, "0"), 0, 12); // Field Position 115-126 (Length - 12)
			$sBField16 = substr(str_pad($BField16, 12, "0", STR_PAD_LEFT), 0, 12); // Field Position 127-138 (Length - 12)
			$sBField17 = substr(str_pad($BField17, 12, "0"), 0, 12); // Field Position 139-150 (Length - 12)
			$sBField18 = substr(str_pad($BField18, 12, "0"), 0, 12); // Field Position 151-162 (Length - 12)
			$sBField19 = substr(str_pad($BField19, 12, "0"), 0, 12); // Field Position 163-174 (Length - 12)
			$sBField20 = substr(str_pad($BField20, 12, "0"), 0, 12); // Field Position 175-186 (Length - 12)
			$sBField21 = substr(str_pad($BField21, 12, "0"), 0, 12); // Field Position 187-198 (Length - 12)
			$sBField22 = substr(str_pad($BField22, 12, "0"), 0, 12); // Field Position 199-210 (Length - 12)
			$sBField23 = substr(str_pad($BField23, 12, "0"), 0, 12); // Field Position 211-222 (Length - 12)
			$sBField24 = substr(str_pad($BField24, 12, "0"), 0, 12); // Field Position 223-234 (Length - 12)
			$sBField25 = substr(str_pad($BField25, 12, "0"), 0, 12); // Field Position 235-246 (Length - 12)
			$sBField26 = substr(str_pad($BField26, 12, "0"), 0, 12); // Field Position 247-258 (Length - 12)
			$sBField27 = substr(str_pad($BField27, 12, "0"), 0, 12); // Field Position 259-270 (Length - 12)
			$sBField28 = substr(str_pad($BField28, 16, " "), 0, 16); // Field Position 271-286 (Length - 16)
			$sBField29 = substr(str_pad($BField29, 1, " "), 0, 1); // Field Position 287 (Length - 1)
			$sBField30 = substr(str_pad($BField30, 80, " "), 0, 80); // Field Position 288-367 (Length - 80)
			$sBField31 = $BField31; // Field Position 328-367 (Length - 40)
			$sBField32 = substr(str_pad($BField32, 40, " "), 0, 40); // Field Position 368-407 (Length - 40)
			$sBField33 = substr(str_pad($BField33, 40, " "), 0, 40); // Field Position 408-447 (Length - 40)
			$sBField34 = substr(str_pad($BField34, 40, " "), 0, 40); // Field Position 448-487 (Length - 40)
			$sBField35 = substr(str_pad($BField35, 2, " "), 0, 2); // Field Position 488-489 (Length - 2)
			$sBField36 = substr(str_pad($BField36, 9, " "), 0, 9); // Field Position 490-498 (Length - 9)
			$sBField37 = substr(str_pad($BField37, 1, " "), 0, 1); // Field Position 499 (Length - 1)
			$sBField38 = substr(str_pad($BField38, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 500-507 (Length - 8)
			$sBField39 = substr(str_pad($BField39, 36, " "), 0, 36); // Field Position 508-543 (Length - 36)

			// Payee “B” Record - Record Layout Positions 544-750 for Form 1098-T
			$sBField40 = substr(str_pad($BField40, 1, " "), 0, 1); // Field Position 544 (Length - 1)
			$sBField41 = substr(str_pad($BField41, 2, " "), 0, 2); // Field Position 545-546 (Length - 2)
			$sBField42 = substr(str_pad($BField42, 1, " "), 0, 1); // Field Position 547 (Length - 1)
			$sBField43 = substr(str_pad($BField43, 1, " "), 0, 1); // Field Position 548 (Length - 1)
			$sBField44 = substr(str_pad($BField44, 1, " "), 0, 1); // Field Position 549 (Length - 1)
			$sBField45 = substr(str_pad($BField45, 1, " "), 0, 1); // Field Position 550 (Length - 1)
			$sBField46 = substr(str_pad($BField46, 112, " "), 0, 112); // Field Position 551-662 (Length - 112)
			$sBField47 = substr(str_pad($BField47, 60, " "), 0, 60); // Field Position 663-722 (Length - 60)
			$sBField48 = substr(str_pad($BField48, 26, " "), 0, 26); // Field Position 723-748 (Length - 26)
			$sBField49 = substr(str_pad($BField49, 2, " "), 0, 2); // Field Position 749-750 (Length - 2)
			// End Payee “B” Record - Record Layout Positions 544-750 for Form 1098-T

			$txt .= $sBField1 . $sBField2 . $sBField3 . $sBField4 . $sBField5 . $sBField6 . $sBField7 . $sBField8 . $sBField9 . $sBField10 . $sBField11 . $sBField12 . $sBField13 . $sBField14 . $sBField15 . $sBField16 . $sBField17 . $sBField18 . $sBField19 . $sBField20 . $sBField21 . $sBField22 . $sBField23 . $sBField24 . $sBField25 . $sBField26 . $sBField27 . $sBField28 . $sBField29 . $sBField30 . $sBField31 . $sBField32 . $sBField33 . $sBField34 . $sBField35 . $sBField36 . $sBField37 . $sBField38 . $sBField39 . $sBField40 . $sBField41 . $sBField42 . $sBField43 . $sBField44 . $sBField45 . $sBField46 . $sBField47 . $sBField48 . $sBField49;

			$txt .= "\n";
			/*if ($k-1 === $total_count2) {
				$mydata2 = $k;

			}*/

			$k++;
		}

		mysqli_next_result($conn);
		/* End Record Name: Issuer “B” Record */

		/* Record Name: Issuer “C” Record */
		$x = $k;
		$sql_c = "CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . ",0,'" . $PK_1098T_EIN . "', 'Electronic Form Part C',$PK_STUDENT_MASTER)";
		$query_c = mysqli_query($conn, $sql_c);
		$total_count3 = mysqli_num_rows($query_c);

		while ($rows = mysqli_fetch_assoc($query_c)) {
			// $student_ssn_format_3 = '';
			// $student_ssn_format_3 = str_replace('_','',str_replace('-' , ''  , my_decrypt($_SESSION['PK_ACCOUNT'] ,  $SSN)));

			// if(strlen($student_ssn_format_3) != 9 || !is_numeric($student_ssn_format_3)){
			// 	$report_error = "ERROR DETECTED:  Run the Student 1098T ERROR REPORT";
			// 	$error_flag_format_3 = true;
			// }
			$RECORD_TYPE = $rows['RECORD_TYPE'];
			$CONTROL_TOTAL_1 = round($rows['CONTROL_TOTAL_1'], 0);
			$CONTROL_TOTAL_3 = round($rows['CONTROL_TOTAL_3'], 0);
			$CONTROL_TOTAL_4 = round($rows['CONTROL_TOTAL_4'], 0);
			$CONTROL_TOTAL_5 = round($rows['CONTROL_TOTAL_5'], 0);
			$CONTROL_TOTAL_7 = round($rows['CONTROL_TOTAL_7'], 0);

			$CField1 = $RECORD_TYPE;
			$CField2 = $_SESSION['Total_Records_Part_B']; // Remaining Find Data
			$CField3 = '';
			$CField4 = $CONTROL_TOTAL_1;
			$CField5 = '';
			$CField6 = $CONTROL_TOTAL_3;
			$CField7 = $CONTROL_TOTAL_4;
			$CField8 = $CONTROL_TOTAL_5;
			$CField9 = '';
			$CField10 = $CONTROL_TOTAL_7;
			$CField11 = '';
			$CField12 = '';
			$CField13 = '';
			$CField14 = '';
			$CField15 = '';
			$CField16 = '';
			$CField17 = '';
			$CField18 = '';
			$CField19 = '';
			$CField20 = '';
			$CField21 = '';
			$CField22 = '';
			$CField23 = $x; // Remaining Find Data
			$CField24 = '';
			$CField25 = '';

			$sCField1 = $CField1; // Field Position 1 (Length - 1)
			$sCField2 = substr(str_pad($CField2, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 2-9 (Length - 8)
			$sCField3 = substr(str_pad($CField3, 8, " "), 0, 6); // Field Position 10-15 (Length - 6)
			$sCField4 = '+' . substr(str_pad($CField4, 17, "0", STR_PAD_LEFT), 0, 18); // Field Position 16-33 (Length - 18)
			$sCField5 = substr(str_pad($CField5, 18, "0"), 0, 18); // Field Position 34-51 (Length - 18)
			$sCField6 = '+' . substr(str_pad($CField6, 17, "0", STR_PAD_LEFT), 0, 18); // Field Position 52-69 (Length - 18)
			$sCField7 = '+' . substr(str_pad($CField7, 17, "0", STR_PAD_LEFT), 0, 18); // Field Position 70-87 (Length - 18)
			$sCField8 = '+' . substr(str_pad($CField8, 17, "0", STR_PAD_LEFT), 0, 18); // Field Position 88-105 (Length - 18)
			$sCField9 = substr(str_pad($CField9, 18, "0"), 0, 18); // Field Position 106-123 (Length - 18)
			$sCField10 = '+' . substr(str_pad($CField10, 17, "0", STR_PAD_LEFT), 0, 18); // Field Position 124-141 (Length - 18)
			$sCField11 = substr(str_pad($CField11, 18, "0"), 0, 18); // Field Position 142-159 (Length - 18)
			$sCField12 = substr(str_pad($CField12, 18, "0"), 0, 18); // Field Position 160-177  (Length - 18)
			$sCField13 = substr(str_pad($CField13, 18, "0"), 0, 18); // Field Position 178-195 (Length - 18)
			$sCField14 = substr(str_pad($CField14, 18, "0"), 0, 18); // Field Position 196-213 (Length - 18)
			$sCField15 = substr(str_pad($CField15, 18, "0"), 0, 18); // Field Position 214-231 (Length - 18)
			$sCField16 = substr(str_pad($CField16, 18, "0"), 0, 18); // Field Position 232-249 (Length - 18)
			$sCField17 = substr(str_pad($CField17, 18, "0"), 0, 18); // Field Position 250-267 (Length - 18)
			$sCField18 = substr(str_pad($CField18, 18, "0"), 0, 18); // Field Position 268-285 (Length - 18)
			$sCField19 = substr(str_pad($CField19, 18, "0"), 0, 18); // Field Position 286-303 (Length - 18)
			$sCField20 = substr(str_pad($CField20, 18, "0"), 0, 18); // Field Position 304-321 (Length - 18)
			$sCField21 = substr(str_pad($CField21, 18, "0"), 0, 18); // Field Position 322-339 (Length - 18)
			$sCField22 = substr(str_pad($CField22, 160, " "), 0, 160); // Field Position 340-499 (Length - 160)
			$sCField23 = substr(str_pad($CField23, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 500-507 (Length - 8)
			$sCField24 = substr(str_pad($CField24, 241, " "), 0, 241); // Field Position 508-748 (Length - 241)
			$sCField25 = substr(str_pad($CField25, 2, " "), 0, 2); // Field Position 749-750 (Length - 2)

			$txt .= $sCField1 . $sCField2 . $sCField3 . $sCField4 . $sCField5 . $sCField6 . $sCField7 . $sCField8 . $sCField9 . $sCField10 . $sCField11 . $sCField12 . $sCField13 . $sCField14 . $sCField15 . $sCField16 . $sCField17 . $sCField18 . $sCField19 . $sCField20 . $sCField21 . $sCField22 . $sCField23 . $sCField24 . $sCField25;

			$txt .= "\n";

			/*if ($x-1 === $total_count3) {
				$mydata3 = $x;
			}*/

			$x++;
		}
		mysqli_next_result($conn);
		/* End Record Name: Issuer “C” Record */

		/* Record Name: Issuer “K” Record */
		/*$y = $x;
		$sql_k="CALL ACCT10982022(".$_SESSION['PK_ACCOUNT'].",0,'".$PK_1098T_EIN."', 'Electronic Form Part K',$PK_STUDENT_MASTER)";
		$query_k = mysqli_query($conn,$sql_k);
		$total_count4 = mysqli_num_rows($query_k);
		while ($record = mysqli_fetch_assoc($query_k)) 
		{
			$RECORD_TYPE = $record['RECORD_TYPE'];
			$CONTROL_TOTAL_1 = round($record['CONTROL_TOTAL_1'], 0);
			$CONTROL_TOTAL_3 = round($record['CONTROL_TOTAL_3'], 0);
			$CONTROL_TOTAL_4 = round($record['CONTROL_TOTAL_4'], 0);
			$CONTROL_TOTAL_5 = round($record['CONTROL_TOTAL_5'], 0);
			$CONTROL_TOTAL_7 = round($record['CONTROL_TOTAL_7'], 0);
			
			$KField1 = $RECORD_TYPE;
			$KField2 = $_SESSION['Total_Records_Part_B']; // Remaining Find Data
			$KField3 = '';
			$KField4 = $CONTROL_TOTAL_1;
			$KField5 = '';
			$KField6 = $CONTROL_TOTAL_3;
			$KField7 = $CONTROL_TOTAL_4;
			$KField8 = $CONTROL_TOTAL_5;
			$KField9 = '';
			$KField10 = $CONTROL_TOTAL_7;
			$KField11 = '';
			$KField12 = '';
			$KField13 = '';
			$KField14 = '';
			$KField15 = '';
			$KField16 = '';
			$KField17 = '';
			$KField18 = '';
			$KField19 = '';
			$KField20 = '';
			$KField21 = '';
			$KField22 = '';
			$KField23 = $y; // Remaining Find Data
			$KField24 = '';
			$KField25 = '';
			$KField26 = '';
			$KField27 = '';
			$KField28 = '';
			$KField29 = '';

			$sKField1 = $KField1; // Field Position 1 (Length - 1)
			$sKField2 = substr(str_pad($KField2,8,"0",STR_PAD_LEFT),0,8); // Field Position 2-9 (Length - 8)
			$sKField3 = substr(str_pad($KField3,6," "),0,6); // Field Position 10-15 (Length - 6)
			$sKField4 = '+'.substr(str_pad($KField4,17,"0",STR_PAD_LEFT),0,18); // Field Position 16-33 (Length - 18)
			$sKField5 = substr(str_pad($KField5,18,"0"),0,18); // Field Position 34-51 (Length - 18)
			$sKField6 = '+'.substr(str_pad($KField6,17,"0",STR_PAD_LEFT),0,18); // Field Position 52-69 (Length - 18)
			$sKField7 = '+'.substr(str_pad($KField7,17,"0",STR_PAD_LEFT),0,18); // Field Position 70-87 (Length - 18)
			$sKField8 = '+'.substr(str_pad($KField8,17,"0",STR_PAD_LEFT),0,18); // Field Position 88-105 (Length - 18)
			$sKField9 = substr(str_pad($KField9,18,"0"),0,18); // Field Position 106-123 (Length - 18)
			$sKField10 = '+'.substr(str_pad($KField10,17,"0",STR_PAD_LEFT),0,18); // Field Position 124-141 (Length - 18)
			$sKField11 = substr(str_pad($KField11,18,"0"),0,18); // Field Position 142-159 (Length - 18)
			$sKField12 = substr(str_pad($KField12,18,"0"),0,18); // Field Position 160-177  (Length - 18)
			$sKField13 = substr(str_pad($KField13,18,"0"),0,18); // Field Position 178-195 (Length - 18)
			$sKField14 = substr(str_pad($KField14,18,"0"),0,18); // Field Position 196-213 (Length - 18)
			$sKField15 = substr(str_pad($KField15,18,"0"),0,18); // Field Position 214-231 (Length - 18)
			$sKField16 = substr(str_pad($KField16,18,"0"),0,18); // Field Position 232-249 (Length - 18)
			$sKField17 = substr(str_pad($KField17,18,"0"),0,18); // Field Position 250-267 (Length - 18)
			$sKField18 = substr(str_pad($KField18,18,"0"),0,18); // Field Position 268-285 (Length - 18)
			$sKField19 = substr(str_pad($KField19,18,"0"),0,18); // Field Position 286-303 (Length - 18)
			$sKField20 = substr(str_pad($KField20,18,"0"),0,18); // Field Position 304-321 (Length - 18)
			$sKField21 = substr(str_pad($KField21,18,"0"),0,18); // Field Position 322-339 (Length - 18)
			$sKField22 = substr(str_pad($KField22,160," "),0,160); // Field Position 340-499 (Length - 160)
			$sKField23 = substr(str_pad($KField23,8,"0",STR_PAD_LEFT),0,8); // Field Position 500-507 (Length - 8)
			$sKField24 = substr(str_pad($KField24,199," "),0,199); // Field Position 508-706 (Length - 199)
			$sKField25 = substr(str_pad($KField25,18," "),0,18); // Field Position 707-724 (Length - 18)
			$sKField26 = substr(str_pad($KField26,18," "),0,18); // Field Position 725-742 (Length - 18)
			$sKField27 = substr(str_pad($KField27,4," "),0,4); // Field Position 743-746 (Length - 4)
			$sKField28 = substr(str_pad($KField28,2," "),0,2); // Field Position 747-748 (Length - 2)
			$sKField29 = substr(str_pad($KField29,2," "),0,2); // Field Position 749-750 (Length - 2)
		
			$txt .= $sKField1.$sKField2.$sKField3.$sKField4.$sKField5.$sKField6.$sKField7.$sKField8.$sKField9.$sKField10.$sKField11.$sKField12.$sKField13.$sKField14.$sKField15.$sKField16.$sKField17.$sKField18.$sKField19.$sKField20.$sKField21.$sKField22.$sKField23.$sKField24.$sKField25.$sKField26.$sKField27.$sKField28.$sKField29;

			$txt .= "\n";

			//if ($y-1 === $total_count4) {
				//$mydata4 = $y;
			//}

			$y++;
			
		}*/
		/* End Record Name: Issuer “K” Record */

		/* Record Name: Issuer “F” Record */
		$z = $x;
		$FField1 = 'F';
		$FField2 = $_SESSION['Total_Records_Part_B']; // Remaining Find Data
		$FField3 = '';
		$FField4 = '';
		$FField5 = '';
		$FField6 = '';
		$FField7 = $z; // Remaining Find Data
		$FField8 = '';
		$FField9 = '';

		$sFField1 = $FField1; // Field Position 1 (Length - 1)
		$sFField2 = substr(str_pad($FField2, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 2-9 (Length - 8)
		$sFField3 = substr(str_pad($FField3, 21, "0"), 0, 21); // Field Position 10-15 (Length - 21)
		$sFField4 = substr(str_pad($FField4, 19, " "), 0, 19); // Field Position 10-30 (Length - 19)
		$sFField5 = substr(str_pad($FField5, 8, " "), 0, 8); // Field Position 31-49 (Length - 8)
		$sFField6 = substr(str_pad($FField6, 442, " "), 0, 442); // Field Position 50-57 (Length - 442)
		$sFField7 = substr(str_pad($FField7, 8, "0", STR_PAD_LEFT), 0, 8); // Field Position 58-499 (Length - 8)
		$sFField8 = substr(str_pad($FField8, 241, " "), 0, 241); // Field Position 500-507 (Length - 241)
		$sFField9 = substr(str_pad($FField9, 2, " "), 0, 2); // Field Position 508-748 (Length - 2)

		$txt .= $sFField1 . $sFField2 . $sFField3 . $sFField4 . $sFField5 . $sFField6 . $sFField7 . $sFField8 . $sFField9;
		/* End Record Name: Issuer “F” Record */

		$file_name = '1098T-'.$_POST['CALENDAR_YEAR'].'-ElectronicFile.txt';
		//file_put_contents($file_name, $txt);
		if($error_flag_format_3 == false){
			$txt_content = fopen('temp/' . $file_name, "w") or die("Unable to open file!");
			fwrite($txt_content, $txt);
			fclose($txt_content);
			chmod($txt_content, 0777);
			$file_path = 'temp/' . $file_name;
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename=' . basename($file_path));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file_path));
			header("Content-Type: text/plain");
			ob_clean();
			flush();
			readfile($file_path);
			exit;
		}
		

		// End DIAM - 69

	} else if ($_POST['FORMAT'] == 4) {
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

		$time = convert_to_user_date(date('Y-m-d H:i:s'), 'Y-m-d H-i', $TIMEZONE, date_default_timezone_get());
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'REVIEW ONLY - Do Not Transmit to IRS - ' . $_SESSION['SCHOOL_NAME'] . ' - 1098T ' . $_POST['CALENDAR_YEAR'] . ' ' . $time . '.xlsx';
		$file_name		= str_replace("/", " ", $file_name);
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

		$line 	= 1;
		$index 	= -1;
		$res = $db->Execute("CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . ",0," . $_POST['PK_1098T_EIN'] . ", 'Export Data for Review',$PK_STUDENT_MASTER)");

		if ($res->fields['ERROR']) {
			$report_error = $res->fields['ERROR'];
		} else {
			$heading = array_keys($res->fields);
			//print_r($heading);	

			while (!$res->EOF) {
				$index = -1;
				foreach ($heading as $key) {
					if ($key != 'ROW_TYPE') {
						//Get Header column name and set styling 
						if ($line == 1) {
							$index++;
							$cell_no = $cell[$index] . $line;
							$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
							$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							$objPHPExcel->getActiveSheet()->freezePane('A1');
						} else {
							// Get data column value and set data
							$index++;
							$cell_no = $cell[$index] . $line;
							$cellValue = $res->fields[$key];
							/** dvb 23 11 2024 **/
							if ($key== 'SSNEncrypted') {
								$SSN 		= $res->fields['SSNEncrypted'];
								$cellValue 	= my_decrypt('', $SSN);
							}
							$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
						}
					} else {
						echo 'Skip header';
					}
				}

				$line++;

				$res->MoveNext();
			}
			//exit;
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:" . $outputFileName);
		}
	} else if ($_POST['FORMAT'] == 5) { //Student 1098T Form ERROR REPORT
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

		$time = convert_to_user_date(date('Y-m-d H:i:s'), 'Y-m-d H-i', $TIMEZONE, date_default_timezone_get());
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'ERROR REPORT - Do Not Transmit to IRS - ' . $_SESSION['SCHOOL_NAME'] . ' - 1098T ' . $_POST['CALENDAR_YEAR'] . ' ' . $time . '.xlsx';
		$file_name		= str_replace("/", " ", $file_name);
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(
			pathinfo($outputFileName, PATHINFO_FILENAME),
			pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
			$outputFileName
		);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel    = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;
		$index 	= -1;
		$res = $db->Execute("CALL ACCT1098".$_POST['CALENDAR_YEAR']."(" . $_SESSION['PK_ACCOUNT'] . ",0," . $_POST['PK_1098T_EIN'] . ", 'Student 1098T ERROR REPORT',$PK_STUDENT_MASTER)");


		if ($res->fields['QUALIFIED_STUDENT_ROW_TYPE_FIELD'] == 'QUALIFIED_STUDENT_ROW_TYPE') {
			$heading = ["LAST NAME",	"FIRST NAME",	"ADDRESS",	"CITY",	"STATE",	"ZIP",	/*"SSN REVIEW",*/ 	"CURRENT ADDRESS", "ERROR" , "SSN"];

			
			$index = -1;
			foreach ($heading as $key) {
				if ($key != 'ROW_TYPE') {
					//Get Header column name and set styling 
					if ($line == 1) {
						$index++;
						$cell_no = $cell[$index] . $line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);
						$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
						$objPHPExcel->getActiveSheet()->freezePane('A1');
					}
				}
			}

			$line++;
			while (!$res->EOF) {
				$index = -1;
				$student_ssn = '';
				$student_ssn = str_replace('_','',str_replace('-' , ''  , my_decrypt($_SESSION['PK_ACCOUNT'] ,  $res->fields['STUDENT_SSN_ENCRYPTED'])));
 
				if(strlen($student_ssn) != 9 || !is_numeric($student_ssn)){


					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LAST_NAME']);
			
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FIRST_NAME']);
			

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ADDRESS_1']);

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ADDRESS_CITY']);
			
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ADDRESS_STATE']);
			

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ADDRESS_ZIP']);

					// $index++;
					// $cell_no = $cell[$index].$line;
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SSN_REVIEW']);
			
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CURRENT_ADDRESS']);
			

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Invalid SSN. SSN Must be of 9 digits.");
			
					$report_error = "ERROR DETECTED:  Run the Student 1098T ERROR REPORT";

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($student_ssn);

					$line++;

				}

				
				$res->MoveNext();
			}
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:" . $outputFileName);
		}else{
			$heading = array_keys($res->fields);
			//print_r($heading);	
	
			while (!$res->EOF) {
				$index = -1;
				if($res->fields['ROW_TYPE'] != 'A_QUALIFIED_STUDENT_ROW_TYPE_FIELD_E'){

				
				foreach ($heading as $key) {
					if ($key != 'ROW_TYPE' && $key != 'STUDENT_SSN_ENCRYPTED' ) {
						//Get Header column name and set styling 
						if ($line == 1) {
							$index++;
							$cell_no = $cell[$index] . $line;
							$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
							$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							$objPHPExcel->getActiveSheet()->freezePane('A1');
						} else {
							// Get data column value and set data
							$index++;
							$cell_no = $cell[$index] . $line;
							$cellValue = $res->fields[$key];
							if ($res->fields[$key] == 'SSNEncrypted') {
								$SSN 		= $res->fields['SSNEncrypted'];
								$cellValue 	= my_decrypt('', $SSN);
							}
							if($key == 'SSN_REVIEW'){
								$ENCRYPTEDSSNAV = $res->fields['STUDENT_SSN_ENCRYPTED'];
								$DECRYPTEDSSN 	= str_replace('_','',str_replace('-' , ''  ,my_decrypt('', $ENCRYPTEDSSNAV)));
								if( strlen($DECRYPTEDSSN) != 9 ){
									$cellValue = "Invalid SSN. SSN Must be of 9 digits";
								}
							}
							$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
						}
					} else {
						echo 'Skip header';
					}
				}

				$line++;
				}else{
					$ENCRYPTEDSSNAV = $res->fields['STUDENT_SSN_ENCRYPTED'];
					$DECRYPTEDSSN 	= str_replace('_','',str_replace('-' , ''  ,my_decrypt('', $ENCRYPTEDSSNAV)));
					if( strlen($DECRYPTEDSSN) != 9 ){

					
					foreach ($heading as $key) {
						if ($key != 'ROW_TYPE' && $key != 'STUDENT_SSN_ENCRYPTED' ) {
							//Get Header column name and set styling 
							if ($line == 1) {
								$index++;
								$cell_no = $cell[$index] . $line;
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
								$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
								$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
								$objPHPExcel->getActiveSheet()->freezePane('A1');
							} else {
								// Get data column value and set data
								$index++;
								$cell_no = $cell[$index] . $line;
								$cellValue = $res->fields[$key];
								if ($res->fields[$key] == 'SSNEncrypted') {
									$SSN 		= $res->fields['SSNEncrypted'];
									$cellValue 	= my_decrypt('', $SSN);
								}
								if($key == 'SSN_REVIEW'){
									$cellValue = "Invalid SSN. SSN Must be of 9 digits";
								}
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							}
						} else {
							echo 'Skip header';
						}
					}
					$line++;
					}
				}
	 
	
				$res->MoveNext();
			}
			//exit;
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:" . $outputFileName);
		}
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
	<title><?= MNU_1098T ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}
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
						<h4 class="text-themecolor"><?= MNU_1098T ?></h4>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
								<div class="p-20">
									<div class="d-flex">
										<div class="col-6 col-sm-6 ">

											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_1098T_EIN" name="PK_1098T_EIN" class="form-control required-entry" onchange="get_eid_detail(this.value)">
														<option></option>
														<?

														while (!$res_type_for_EIN->EOF) { ?>
															<option value="<?= $res_type_for_EIN->fields['PK_1098T_EIN'] ?>"><?= $res_type_for_EIN->fields['EIN_NO'] ?></option>
														<? $res_type_for_EIN->MoveNext();
														} ?>
													</select>
													<span class="bar"></span>
													<label for="PK_1098T_EIN"><?= EIN_1 ?></label>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="CALENDAR_YEAR" name="CALENDAR_YEAR" class="form-control required-entry"> <!--  onchange="show_btn(this.value)" -->
														<option></option>
														<option value="2022">2022</option>
														<option value="2023">2023</option>
														<option value="2024">2024</option><!-- dvb 23 11 2024 -->
                                                        <option value="2025">2025</option>
													</select>
													<span class="bar"></span>
													<label for="CALENDAR_YEAR"><?= CALENDAR_YEAR ?></label>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span>
													<label><?= EXPORT_DATA_TO_REVIEW ?></label>
												</div>

												<div class="col-3 col-sm-3 focused">
													<button id="EXCEL_BTN" type="button" onclick="submit_form(4)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
												</div>
											</div>
											<br /><br />
											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span>
													<label><?= VIEW_RELATED_STUDENT_LEDGERS ?></label>
												</div>

												<div class="col-3 col-sm-3 focused">
													<button id="PDF_BTN" type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
												</div>
											</div>
											<br /><br />

											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span>
													<label><?= ERROR_REPORT ?></label>
												</div>

												<div class="col-3 col-sm-3 focused">
													<button id="ERROR_REPORT_EXCEL_BTN" type="button" onclick="submit_form(5)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
												</div>
											</div>

											<hr />

											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span>
													<label><?= PRINT_1098T_FORMS ?></label>
												</div>

												<div class="col-3 col-sm-3 focused">
													<button id="PRINT_1098T_FORMS_BTN" type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
												</div>
											</div>
											<br /><br />

											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span>
													<label><?= CREATE_ELECTRONIC_FILE ?></label>
												</div>

												<div class="col-6 col-sm-6 focused">
													<button id="CREATE_ELECTRONIC_FILE" type="button" onclick="submit_form(3)" class="btn waves-effect waves-light btn-info" id="CREATE_ELECTRONIC_FILE_BTN"><?= EXPORT ?></button>
													<span id="CREATE_ELECTRONIC_FILE_SPAN"  style="margin-left : 5px; color : red">Available in March</span>
												</div>
											</div>
											<br /><br />


											<br /><br />

										</div>
										<div class="col-6 col-sm-6 ">

											<div class="row">
												<div class="col-12 col-sm-6 focused">
													<span class="bar"></span>
													<label for="CAMPUS"><?= CAMPUS ?></label>
												</div>
												<div class="col-12 col-sm-12 form-group" id="CAMPUS_DIV">
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control " id="TRANSMITTER_CONTROL_CODE" name="TRANSMITTER_CONTROL_CODE" value="" readonly>
														<span class="bar"></span>
														<label for="TRANSMITTER_CONTROL_CODE"><?= TRANSMITTER_CONTROL_CODE ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control " id="EIN" name="EIN" value="" readonly>
														<span class="bar"></span>
														<label for="EIN"><?= EIN_1 ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control " id="CONTACT_NAME" name="CONTACT_NAME" value="" readonly>
														<span class="bar"></span>
														<label for="CONTACT_NAME"><?= CONTACT_NAME ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control phone-inputmask" id="CONTACT_PHONE" name="CONTACT_PHONE" value="" readonly>
														<span class="bar"></span>
														<label for="CONTACT_PHONE"><?= CONTACT_PHONE ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control validate-email" id="CONTACT_EMAIL" name="CONTACT_EMAIL" value="" readonly>
														<span class="bar"></span>
														<label for="CONTACT_EMAIL"><?= CONTACT_EMAIL ?></label>
													</div>
												</div>
											</div>

										</div>
									</div>
								</div>
								<input type="hidden" name="FORMAT" id="FORMAT">
							</form>
						</div>
					</div>
				</div>

			</div>
		</div>

		<? require_once("footer.php"); ?>
		<?php if ($report_error != "") { ?>
			<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1">1098T Error Reporting</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" style="color: red;font-size: 15px;">
								<b><?php echo $report_error; ?></b>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script type="text/javascript">
		//var form1 = new Validation('form1');
		var error = '<?php echo  $report_error; ?>';

		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if (result == true) {
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}
		jQuery(document).ready(function($) {
			if (error != "") {
				jQuery('#errorModal').modal();
			}
		})

		function get_eid_detail(val) {
			jQuery(document).ready(function($) {
				var data = 'id=' + val;
				var value = $.ajax({
					url: "ajax_get_eid_detail",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						data = data.split("|||");
						document.getElementById("TRANSMITTER_CONTROL_CODE").value = data[0]
						document.getElementById("EIN").value = data[1]
						document.getElementById("CONTACT_NAME").value = data[2]
						document.getElementById("CONTACT_PHONE").value = data[3]
						document.getElementById("CONTACT_EMAIL").value = data[4]
						document.getElementById("CAMPUS_DIV").innerHTML = data[5]

						$('.floating-labels .form-control').on('focus blur', function(e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}
				}).responseText;
			});
		}

		function show_btn(year) {
			jQuery(document).ready(function($) {
				var data = 'year=' + year;
				var value = $.ajax({
					url: "ajax_check_1098t_digital_button",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						if (data == 'a') {
							document.getElementById("CREATE_ELECTRONIC_FILE_BTN").disabled = false
							document.getElementById("CREATE_ELECTRONIC_FILE_SPAN").style.display = 'none';
						} else {
							document.getElementById("CREATE_ELECTRONIC_FILE_BTN").disabled = true
							document.getElementById("CREATE_ELECTRONIC_FILE_SPAN").style.display = 'inline';
						}
					}
				}).responseText;
			});
		}
		jQuery(document).ready(function($) {
			checkforButtons();
			$('#PK_1098T_EIN').on('change', function() {
				checkforButtons();
			});
			$('#CALENDAR_YEAR').on('change', function() {
				checkforButtons();
			});

			function checkforButtons() {
				var EIN = $('#PK_1098T_EIN').val();
				var YEAR = $('#CALENDAR_YEAR').val();

				if (EIN != '' && YEAR != '') {
					activateButtons(true);
				} else {
					activateButtons(false);
				}
			}

            function activateButtons(flag) {
                            if (flag == true) {
                                $('#EXCEL_BTN').prop('disabled', false);
                                $('#PDF_BTN').prop('disabled', false);
                                $('#ERROR_REPORT_EXCEL_BTN').prop('disabled', false);
                                $('#PRINT_1098T_FORMS_BTN').prop('disabled', false);
                                
                                // Lógica específica para el año 2025
                                var YEAR = $('#CALENDAR_YEAR').val();
                                
                                if (YEAR == '2025') {
                                    // Si es 2025, deshabilita el botón y muestra el mensaje
                                    $('#CREATE_ELECTRONIC_FILE').prop('disabled', true);
                                    $('#CREATE_ELECTRONIC_FILE_SPAN').show();
                                } else {
                                    // Para otros años, habilita el botón y oculta el mensaje
                                    $('#CREATE_ELECTRONIC_FILE').prop('disabled', false);
                                    $('#CREATE_ELECTRONIC_FILE_SPAN').hide();
                                }

                            } else {
                                $('#EXCEL_BTN').prop('disabled', true);
                                $('#PDF_BTN').prop('disabled', true);
                                $('#ERROR_REPORT_EXCEL_BTN').prop('disabled', true);
                                $('#PRINT_1098T_FORMS_BTN').prop('disabled', true);
                                $('#CREATE_ELECTRONIC_FILE').prop('disabled', true);
                                $('#CREATE_ELECTRONIC_FILE_SPAN').hide();
                            }
                        }
		})
	</script>
	<?php $report_error = ""; ?>

</body>

</html>
