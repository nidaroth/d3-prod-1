<?php session_start();
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
// require_once('../global/tcpdf/config/lang/eng.php');
// require_once('../global/tcpdf/tcpdf.php');
// require_once('../global/config.php');
require_once('/var/www/html/D3/global/tcpdf/config/lang/eng.php');
require_once('/var/www/html/D3/global/tcpdf/tcpdf.php');
require_once('/var/www/html/D3/global/config.php');

//$pdf_path = realpath(__DIR__).'/';

	
class MYPDF extends TCPDF {
    public function Header() {
		
    }
    public function Footer() {
		
    }
}

function generate_invoice_pdf($PK_PAYMENT_BATCH_DETAIL, $PK_PAYMENT_BATCH_MASTER, $PK_MISC_BATCH_DETAIL, $save){
	global $db;
	
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 10, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, 30);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 9, '', true);
	
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

	$res_pay = $db->Execute("SELECT ENABLE_DIAMOND_PAY, CHARGE_PROCESSING_FEE_FROM_STUDENT FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$CHARGE_PROCESSING_FEE_FROM_STUDENT = $res_pay->fields['CHARGE_PROCESSING_FEE_FROM_STUDENT'];
	$ENABLE_DIAMOND_PAY = $res_pay->fields['ENABLE_DIAMOND_PAY'];

	$cond = " AND 1 = 0 ";
	
	if($PK_PAYMENT_BATCH_DETAIL != '' || $PK_PAYMENT_BATCH_MASTER != '') {
		if($PK_PAYMENT_BATCH_DETAIL != '')
			$cond = " AND S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL = '$PK_PAYMENT_BATCH_DETAIL' ";
		else if($PK_PAYMENT_BATCH_MASTER != '')
			$cond = " AND S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' ";

		// DIAM-2361
		if($ENABLE_DIAMOND_PAY == 2)
		{
			$PARAMS = ", S_PAYMENT_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_STAX AS PK_STUDENT_CREDIT_CARD_PAYMENT, S_STUDENT_CREDIT_CARD_STAX.CARD_NO AS CARD_NO, S_STUDENT_CREDIT_CARD_STAX.NAME_ON_CARD AS CARD_NAME, S_STUDENT_CREDIT_CARD_STAX.CARD_TYPE AS CARD_TYPE, S_STUDENT_CREDIT_CARD_STAX.CUSTOMER_ID AS ORDER_ID, S_PAYMENT_BATCH_DETAIL.CREATED_ON AS CREATED_ON ";
			$JOINS = "LEFT JOIN S_STUDENT_CREDIT_CARD_STAX ON S_STUDENT_CREDIT_CARD_STAX.PK_STUDENT_CREDIT_CARD_STAX = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_STAX";
		}
		else
		{
			$PARAMS = ", S_PAYMENT_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_PAYMENT AS PK_STUDENT_CREDIT_CARD_PAYMENT, CARD_NO, CARD_NAME, CARD_TYPE, S_STUDENT_CREDIT_CARD_PAYMENT.ORDER_ID, S_STUDENT_CREDIT_CARD_PAYMENT.CREATED_ON, CONV_FEE_AMOUNT ";
			$JOINS = "LEFT JOIN S_STUDENT_CREDIT_CARD_PAYMENT ON S_STUDENT_CREDIT_CARD_PAYMENT.PK_STUDENT_CREDIT_CARD_PAYMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_PAYMENT";
		}
		// End DIAM-2361
			
		$res_disb = $db->Execute("SELECT S_PAYMENT_BATCH_DETAIL.PK_STUDENT_ENROLLMENT,M_AR_LEDGER_CODE.CODE AS LEDGER, RECEIPT_NO, RECEIVED_AMOUNT, IF(DEPOSITED_DATE = '0000-00-00','', DATE_FORMAT(DEPOSITED_DATE, '%m/%d/%Y' )) AS DEPOSITED_DATE, IF(BATCH_TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(BATCH_TRANSACTION_DATE, '%m/%d/%Y' )) AS BATCH_TRANSACTION_DATE , S_PAYMENT_BATCH_DETAIL.CHECK_NO AS STUD_CHECK_NO, IF(S_PAYMENT_BATCH_DETAIL.EDITED_BY > 0, S_PAYMENT_BATCH_DETAIL.EDITED_BY, S_PAYMENT_BATCH_DETAIL.CREATED_BY) as PROCESSED_BY_ID, PK_DETAIL_TYPE, DETAIL $PARAMS
		FROM 
		S_PAYMENT_BATCH_MASTER,  S_PAYMENT_BATCH_DETAIL 
		LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_MASTER 
		$JOINS
		, S_STUDENT_DISBURSEMENT 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE 
		WHERE 
		S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER AND 
		S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL $cond 
		ORDER BY LAST_NAME ASC, FIRST_NAME ASC, RECEIPT_NO ASC  ");
		
		$ref_label = 'Check #';
	} else if($PK_MISC_BATCH_DETAIL != '') {

		// DIAM-2361
		if($ENABLE_DIAMOND_PAY == 2)
		{
			$PARAMS = ", S_MISC_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_STAX AS PK_STUDENT_CREDIT_CARD_PAYMENT, S_STUDENT_CREDIT_CARD_STAX.CARD_NO AS CARD_NO, S_STUDENT_CREDIT_CARD_STAX.NAME_ON_CARD AS CARD_NAME, S_STUDENT_CREDIT_CARD_STAX.CARD_TYPE AS CARD_TYPE, S_STUDENT_CREDIT_CARD_STAX.CUSTOMER_ID AS ORDER_ID, S_MISC_BATCH_DETAIL.CREATED_ON AS CREATED_ON ";
			$JOINS = "LEFT JOIN S_STUDENT_CREDIT_CARD_STAX ON S_STUDENT_CREDIT_CARD_STAX.PK_STUDENT_CREDIT_CARD_STAX = S_MISC_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_STAX";
		}
		else
		{
			$PARAMS = ", S_MISC_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_PAYMENT AS PK_STUDENT_CREDIT_CARD_PAYMENT, CARD_NO, CARD_NAME, CARD_TYPE, S_STUDENT_CREDIT_CARD_PAYMENT.ORDER_ID, S_STUDENT_CREDIT_CARD_PAYMENT.CREATED_ON, CONV_FEE_AMOUNT ";
			$JOINS = "LEFT JOIN S_STUDENT_CREDIT_CARD_PAYMENT ON S_STUDENT_CREDIT_CARD_PAYMENT.PK_STUDENT_CREDIT_CARD_PAYMENT = S_MISC_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_PAYMENT";
		}
		// End DIAM-2361

		$res_disb = $db->Execute("SELECT S_MISC_BATCH_DETAIL.PK_STUDENT_ENROLLMENT,M_AR_LEDGER_CODE.CODE AS LEDGER, MISC_RECEIPT_NO AS RECEIPT_NO, S_MISC_BATCH_DETAIL.CREDIT AS RECEIVED_AMOUNT, IF(TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(TRANSACTION_DATE, '%m/%d/%Y' )) AS BATCH_TRANSACTION_DATE , REF_NUMBER AS STUD_CHECK_NO, PAYMENT_MODE, IF(S_MISC_BATCH_DETAIL.EDITED_BY > 0, S_MISC_BATCH_DETAIL.EDITED_BY, S_MISC_BATCH_DETAIL.CREATED_BY) as PROCESSED_BY_ID, PK_AR_FEE_TYPE,  PK_AR_PAYMENT_TYPE $PARAMS
		FROM
		S_MISC_BATCH_MASTER, S_MISC_BATCH_DETAIL 
		LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_MISC_BATCH_DETAIL.PK_AR_LEDGER_CODE 
		$JOINS
		WHERE 
		S_MISC_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_MISC_BATCH_DETAIL.PK_MISC_BATCH_MASTER = S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER AND 
		S_MISC_BATCH_DETAIL.PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' ");
		
		if($res_disb->fields['PAYMENT_MODE'] == 1)
			$ref_label = 'Check #';
		else if($res_disb->fields['PAYMENT_MODE'] == 3)
			$ref_label = 'Money Order #';
		else if($res_disb->fields['PAYMENT_MODE'] == 5)
			$ref_label = 'CC Payment Info';
		else
			$ref_label = '';
			
		$PAYMENT_TYPE = '';
		if($res_disb->fields['PAYMENT_MODE'] == 1)
			$PAYMENT_TYPE = 'Check';
		else if($res_disb->fields['PAYMENT_MODE'] == 2)
			$PAYMENT_TYPE = 'Cash';
		else if($res_disb->fields['PAYMENT_MODE'] == 3)
			$PAYMENT_TYPE = 'Money Order';
		else if($res_disb->fields['PAYMENT_MODE'] == 4 || $res_disb->fields['PAYMENT_MODE'] == 5)
			$PAYMENT_TYPE = 'Credit Card';
			
		if($res_disb->fields['PK_AR_FEE_TYPE'] > 0) {
			$res11 = $db->Execute("select AR_FEE_TYPE FROM M_AR_FEE_TYPE WHERE PK_AR_FEE_TYPE = '".$res_disb->fields['PK_AR_FEE_TYPE']."' ");
			$PAYMENT_TYPE = $res11->fields['AR_FEE_TYPE'];
		} else if($res_disb->fields['PK_AR_PAYMENT_TYPE'] > 0) {
			$res11 = $db->Execute("select AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '".$res_disb->fields['PK_AR_PAYMENT_TYPE']."' ");
			$PAYMENT_TYPE = $res11->fields['AR_PAYMENT_TYPE'];
		}
	}

	if($res_disb!=null) 
	{
	
	
	while (!$res_disb->EOF) { 
		$pdf->AddPage();
		
		$PK_STUDENT_ENROLLMENT 			= $res_disb->fields['PK_STUDENT_ENROLLMENT'];
		$PK_STUDENT_CREDIT_CARD_PAYMENT = $res_disb->fields['PK_STUDENT_CREDIT_CARD_PAYMENT'];
		
		if($PK_PAYMENT_BATCH_DETAIL != '' || $PK_PAYMENT_BATCH_MASTER != '') {
			$PAYMENT_TYPE = '';
			if($res_disb->fields['PK_DETAIL_TYPE'] == 4) {
				$DETAIL = $res_disb->fields['DETAIL'];
				$res_det1a = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL' ");
				$PAYMENT_TYPE = $res_det1a->fields['AR_PAYMENT_TYPE'];
			}
		}

		$PROCESSED_BY_ID = $res_disb->fields['PROCESSED_BY_ID'];
		$res_user = $db->Execute("SELECT CONCAT(FIRST_NAME,' ', LAST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$PROCESSED_BY_ID' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 
		
		//$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS) as ADDRESS,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS_1) as ADDRESS_1,
			IF(
			HIDE_ACCOUNT_ADDRESS_ON_REPORTS = '1',
			'',
			IF(CITY!='',CONCAT(CITY, ','),'')
				) AS CITY,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',STATE_CODE) as STATE_CODE,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ZIP) as ZIP,
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',PHONE) as PHONE, 
			IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',WEBSITE) as WEBSITE,HIDE_ACCOUNT_ADDRESS_ON_REPORTS FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //DIAM-2271 ,

		$res_enroll = $db->Execute("SELECT CODE, M_CAMPUS_PROGRAM.DESCRIPTION,SESSION,STUDENT_ID, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER "); 

		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="49.5%" style="border:2px solid #000000;" >
							<br /><br />
							<table width="100%" >
								<tr>
									<td width="35%" >'; 
										if($res->fields['PDF_LOGO'] != '')
											$txt .= '<img src="'.$res->fields['PDF_LOGO'].'" />';
									$txt .= '</td>
									<td width="5%" ></td>
									<td width="60%" >
										'.$res->fields['SCHOOL_NAME'].'<br /><br />
										'.$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'].'
										<br />
										'.$res->fields['CITY'].' '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'].'<br />
										'.$res->fields['PHONE'].'<br /><br />
										'.$res->fields['WEBSITE'].'<br />
									</td>
								</tr>
							</table>
						</td>
						<td width="1%" ></td>
						<td width="49.5%" style="border:2px solid #000000;"   >
							<br /><br />
							<table width="100%" >
								<tr>
									<td width="2%" ></td>
									<td width="35%" >'; 
										if($res->fields['PDF_LOGO'] != '')
											$txt .= '<img src="'.$res->fields['PDF_LOGO'].'" />';
									$txt .= '</td>
									<td width="5%" ></td>
									<td width="58%" >
										'.$res->fields['SCHOOL_NAME'].'<br /><br />
										'.$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'].'
										<br />
										'.$res->fields['CITY'].' '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'].'<br />
										'.$res->fields['PHONE'].'<br /><br />
										'.$res->fields['WEBSITE'].'
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>'; //DIAM-2271
			if($PK_STUDENT_CREDIT_CARD_PAYMENT == 0) {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="49.5%" style="border-left:2px solid #000000;border-right:2px solid #000000;" align="center" ><br /><br /><i><span style="font-size:50px;">Payment Receipt</span></i><br /><br /></td>
						<td width="1%" ></td>
						<td width="49.5%" style="border-left:2px solid #000000;border-right:2px solid #000000;" align="center" ><br /><br /><i><span style="font-size:50px;">Payment Receipt</span></i><br /><br /></td>
					</tr>
					<tr>
						<td width="49.5%" style="border-left:2px solid #000000;border-right:2px solid #000000;border-bottom:2px solid #000000;" align="center" ><i><span style="font-size:35px;">Receipt Number: '.$res_disb->fields['RECEIPT_NO'].'</span></i><br /><br /></td>
						<td width="1%" ></td>
						<td width="49.5%" style="border-left:2px solid #000000;border-right:2px solid #000000;border-bottom:2px solid #000000;" align="center" ><i><span style="font-size:35px;">Receipt Number: '.$res_disb->fields['RECEIPT_NO'].'</span></i><br /><br /></td>
					</tr>
				</table>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="49.5%" style="border:2px solid #000000;" >
							<table width="100%" cellspacing="0" cellpadding="3" >
								<tr>
									<td width="40%" ><b><i>Name</i></b></td>
									<td width="60%" >'.trim($res_enroll->fields['NAME']).'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Student ID</i></b></td>
									<td width="60%" >'.$res_enroll->fields['STUDENT_ID'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Program</i></b></td>
									<td width="60%" >'.$res_enroll->fields['CODE'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Session</i></b></td>
									<td width="60%" >'.$res_enroll->fields['SESSION'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Date</i></b></td>
									<td width="60%" >'.$res_disb->fields['BATCH_TRANSACTION_DATE'].'</td>
								</tr>
								<tr>
									<td width="100%" ><br /></td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Amount Of Payment</i></b></td>
									<td width="60%" >$ '.number_format_value_checker($res_disb->fields['RECEIVED_AMOUNT'],2).'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Ledger Code</i></b></td>
									<td width="60%" >'.$res_disb->fields['LEDGER'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Payment Type</i></b></td>
									<td width="60%" >'.$PAYMENT_TYPE.'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>'.$ref_label.'</i></b></td>
									<td width="60%" >'.$res_disb->fields['STUD_CHECK_NO'].'</td>
								</tr>
								<tr>
									<td width="100%" ><br /><br /><br /><br /></td>
								</tr>
								<tr>
									<td width="49%" align="center"><i>'.$res_user->fields['NAME'].'</i></td>
									<td width="2%" ></td>
									<td width="49%"  align="center"></td>
								</tr>
								<tr>
									<td width="49%" style="border-top:0.5px solid #000000;" align="center"><b><i>Processed By</i></b></td>
									<td width="2%" ></td>
									<td width="49%" style="border-top:0.5px solid #000000;" align="center"><b><i>Student Signature</i></b></td>
								</tr>
								<tr>
									<td width="100%" align="center" >Institution Copy</td>
								</tr>
							</table>
						</td>
						<td width="1%" ></td>
						<td width="49.5%" style="border:2px solid #000000;" >
							<table width="100%" cellspacing="0" cellpadding="3" >
								<tr>
									<td width="40%" ><b><i>Name</i></b></td>
									<td width="60%" >'.$res_enroll->fields['NAME'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Student ID</i></b></td>
									<td width="60%" >'.$res_enroll->fields['STUDENT_ID'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Program</i></b></td>
									<td width="60%" >'.$res_enroll->fields['CODE'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Session</i></b></td>
									<td width="60%" >'.$res_enroll->fields['SESSION'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Date</i></b></td>
									<td width="60%" >'.$res_disb->fields['BATCH_TRANSACTION_DATE'].'</td>
								</tr>
								<tr>
									<td width="100%" ><br /></td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Amount Of Payment</i></b></td>
									<td width="60%" >$ '.number_format_value_checker($res_disb->fields['RECEIVED_AMOUNT'],2).'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Ledger Code</i></b></td>
									<td width="60%" >'.$res_disb->fields['LEDGER'].'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>Payment Type</i></b></td>
									<td width="60%" >'.$PAYMENT_TYPE.'</td>
								</tr>
								<tr>
									<td width="40%" ><b><i>'.$ref_label.'</i></b></td>
									<td width="60%" >'.$res_disb->fields['STUD_CHECK_NO'].'</td>
								</tr>
								<tr>
									<td width="100%" ><br /><br /><br /><br /></td>
								</tr>
								<tr>
									<td width="49%" align="center"><b><i></i></b></td>
									<td width="2%" ></td>
									<td width="49%"  align="center"></td>
								</tr>
								<tr>
									<td width="49%" align="center"></td>
									<td width="2%" ></td>
									<td width="49%" align="center"></td>
								</tr>
								<tr>
									<td width="100%" align="center" >Student Copy</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
			} else {
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td width="49.5%" style="border:2px solid #000000;" >
							<table width="100%" cellspacing="0" cellpadding="3" >
								<tr>
									<td width="42%" ><b>Payment Receipt: </b></td>
									<td width="58%" >'.$res_disb->fields['RECEIPT_NO'].'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Student Name: </td>
									<td width="55%" >'.trim($res_enroll->fields['NAME']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Student ID:</td>
									<td width="58%" >'.$res_enroll->fields['STUDENT_ID'].'</td>
								</tr>
								<tr>
									<td width="100%" ><br /><br /><b>Payment Details </b></td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Date: </td>
									<td width="58%" >'.convert_to_user_date($res_disb->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Amount:</td>
									<td width="58%" >$'.number_format_value_checker($res_disb->fields['RECEIVED_AMOUNT'],2).'</td>
								</tr>';
								if($CHARGE_PROCESSING_FEE_FROM_STUDENT == 1 && $ENABLE_DIAMOND_PAY != 2){
									$txt .= '<tr>
										<td width="2%" ></td>
										<td width="40%" >Credit Card Fee:</td>
										<td width="58%" >$'.number_format_value_checker($res_disb->fields['CONV_FEE_AMOUNT'],2).'</td>
									</tr>
									<tr>
										<td width="2%" ></td>
										<td width="40%" >Total Amount:</td>
										<td width="58%" >$'.number_format_value_checker(($res_disb->fields['RECEIVED_AMOUNT'] + $res_disb->fields['CONV_FEE_AMOUNT']),2).'</td>
									</tr>';
								}
						$txt .= '<tr>
									<td width="100%" ><br /><br /><b>Transaction Details </b></td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Transaction ID: </td>
									<td width="58%" >'.trim($res_disb->fields['ORDER_ID']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Name On Card: </td>
									<td width="58%" >'.trim($res_disb->fields['CARD_NAME']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Card #: </td>
									<td width="58%" >'.trim($res_disb->fields['CARD_NO']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Card Type: </td>
									<td width="58%" >'.trim($res_disb->fields['CARD_TYPE']).'</td>
								</tr>
								<tr>
									<td width="100%" ><br /><br /><br /><br /></td>
								</tr>
								<tr>
									<td width="49%" align="center"><i>'.$res_user->fields['NAME'].'</i></td>
									<td width="2%" ></td>
									<td width="49%"  align="center"></td>
								</tr>
								<tr>
									<td width="49%" style="border-top:0.5px solid #000000;" align="center"><b><i>Processed By</i></b></td>
									<td width="2%" ></td>
									<td width="49%" style="border-top:0.5px solid #000000;" align="center"><b><i>Student Signature</i></b></td>
								</tr>
								<tr>
									<td width="100%" align="center" >Institution Copy</td>
								</tr>
							</table>
						</td>
						<td width="1%" ></td>
						<td width="49.5%" style="border:2px solid #000000;" >
							<table width="100%" cellspacing="0" cellpadding="3" >
								<tr>
									<td width="42%" ><b>Payment Receipt: </b></td>
									<td width="58%" >'.$res_disb->fields['RECEIPT_NO'].'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Student Name: </td>
									<td width="55%" >'.trim($res_enroll->fields['NAME']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Student ID:</td>
									<td width="58%" >'.$res_enroll->fields['STUDENT_ID'].'</td>
								</tr>
								<tr>
									<td width="100%" ><br /><br /><b>Payment Details </b></td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Date: </td>
									<td width="58%" >'.convert_to_user_date($res_disb->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Amount:</td>
									<td width="58%" >$'.number_format_value_checker($res_disb->fields['RECEIVED_AMOUNT'],2).'</td>
								</tr>';
								if($CHARGE_PROCESSING_FEE_FROM_STUDENT == 1 && $ENABLE_DIAMOND_PAY != 2){
									$txt .= '<tr>
										<td width="2%" ></td>
										<td width="40%" >Credit Card Fee:</td>
										<td width="58%" >$'.number_format_value_checker($res_disb->fields['CONV_FEE_AMOUNT'],2).'</td>
									</tr>
									<tr>
										<td width="2%" ></td>
										<td width="40%" >Total Amount:</td>
										<td width="58%" >$'.number_format_value_checker(($res_disb->fields['RECEIVED_AMOUNT'] + $res_disb->fields['CONV_FEE_AMOUNT']),2).'</td>
									</tr>';
								}
						$txt .= '<tr>
									<td width="100%" ><br /><br /><b>Transaction Details </b></td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Transaction ID: </td>
									<td width="58%" >'.trim($res_disb->fields['ORDER_ID']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Name On Card: </td>
									<td width="58%" >'.trim($res_disb->fields['CARD_NAME']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Card #: </td>
									<td width="58%" >'.trim($res_disb->fields['CARD_NO']).'</td>
								</tr>
								<tr>
									<td width="2%" ></td>
									<td width="40%" >Card Type: </td>
									<td width="58%" >'.trim($res_disb->fields['CARD_TYPE']).'</td>
								</tr>
								<tr>
									<td width="100%" ><br /><br /><br /><br /></td>
								</tr>
								<tr>
									<td width="49%" align="center"><b><i></i></b></td>
									<td width="2%" ></td>
									<td width="49%"  align="center"></td>
								</tr>
								<tr>
									<td width="49%" align="center"></td>
									<td width="2%" ></td>
									<td width="49%" align="center"></td>
								</tr>
								<tr>
									<td width="100%" align="center" >Student Copy</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
			}			
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		$res_disb->MoveNext();
	}
}
	if($PK_PAYMENT_BATCH_DETAIL != '')
	{
		$file_name = 'Receipt_'.$res_disb->fields['RECEIPT_NO'].'_'.uniqid().'.pdf';
	}
	else
	{
        $file_name = 'Receipt_'.$res_disb->fields['RECEIPT_NO'].'_'.uniqid().'.pdf';
	}
	
	if($save == '1')
		$pdf->Output('temp/'.$file_name, 'F');
	else	
		$pdf->Output('temp/'.$file_name, 'FD');
		
	return 'temp/'.$file_name;	
}
