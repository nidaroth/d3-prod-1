<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/batch_payment.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0){
	header("location:../index");
	exit;
}

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}
$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
	
class MYPDF extends TCPDF {
	public function Header() {
		global $db;
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		
		if($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".",$res->fields['PDF_LOGO']);
			$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(6);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
				
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(9);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(245);
		$this->Cell(55, 8, "Payment Batch", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(210, 13, 293, 13, $style);	
		
	}
	public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(270);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
			
		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9, '', true);
$pdf->AddPage();

$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
		<thead>
			<tr>
				<td width="12%" style="border-bottom:1px solid #000;text-align:left;">
					<b><i>'.BATCH_NO.'</i></b>
				</td>
				<td width="10%" style="border-bottom:1px solid #000;text-align:left;">
					<b><i>'.CAMPUS.'</i></b>
				</td>
				<td width="10%" style="border-bottom:1px solid #000;text-align:left;">
					<b><i>'.BATCH_STATUS.'</i></b>
				</td>
				
				<td width="10%" style="border-bottom:1px solid #000;text-align:left;">
					<b><i>'.BATCH_DATE.'</i></b>
				</td>
				
				<td width="10%" style="border-bottom:1px solid #000;text-align:left;">
					<b><i>'.POSTED_DATE.'</i></b>
				</td>

				<td width="15%" style="border-bottom:1px solid #000;text-align:left;">
					<b><i>'.CHECK_NO.'</i></b>
				</td>

				<td width="20%" style="border-bottom:1px solid #000;text-align:left;">
					<b><i>'.LEDGER_CODES.'</i></b>
				</td>

				<td width="13%" style="border-bottom:1px solid #000;">
					<b><i>'.BATCH_AMOUNT.'('.CREDIT.')'.'</i></b>
				</td>

				
			</tr>
		</thead>';

	
	$res = $db->Execute($_SESSION['QUERY']); 
	while (!$res->EOF) { 
	
		$BATCH_NO 		= $res->fields['BATCH_NO'];
		$BATCH_STATUS	= $res->fields['BATCH_STATUS'];
		if($res->fields['DATE_RECEIVED'] != '' && $res->fields['DATE_RECEIVED'] != '0000-00-00')
			$DATE_RECEIVED = date('m/d/Y',strtotime($res->fields['DATE_RECEIVED']));
		else
			$DATE_RECEIVED = '';
			
			
		if($res->fields['POSTED_DATE'] != '' && $res->fields['POSTED_DATE'] != '0000-00-00')
			$POSTED_DATE = date('m/d/Y',strtotime($res->fields['POSTED_DATE']));
		else
			$POSTED_DATE = '';
		
		$CHECK_NO			= $res->fields['CHECK_NO'];

		$CODE = '';
		$PK_PAYMENT_BATCH_MASTER = $res->fields['PK_PAYMENT_BATCH_MASTER'];
		$res_led = $db->Execute("SELECT CODE FROM S_PAYMENT_BATCH_DETAIL, M_AR_LEDGER_CODE, S_STUDENT_DISBURSEMENT WHERE PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND S_PAYMENT_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT GROUP BY S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE ORDER BY CODE ASC "); 
		while (!$res_led->EOF) { 
			if($CODE != '')
				$CODE .= ', ';
			$CODE .= $res_led->fields['CODE'];
			
			$res_led->MoveNext();
		}
		$row['CODE'] = $CODE;

		$CREDIT_AMOUNT  		= $res->fields['AMOUNT'];
		$BATCH_PK_CAMPUS = $res->fields['BATCH_PK_CAMPUS'];

		$CAMPUS_STR = '';
		$res_type = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($BATCH_PK_CAMPUS)  order by CAMPUS_CODE ASC");
		while (!$res_type->EOF) {
			if ($CAMPUS_STR != '')
				$CAMPUS_STR .= ", ";
			$CAMPUS_STR .= $res_type->fields['CAMPUS_CODE'];
			$res_type->MoveNext();
		}


		
		$txt .= '  <tbody>
		<tr>
						<td  width="12%" align="left">'.$BATCH_NO.'</td>
						<td width="10%" align="left">'.$CAMPUS_STR.'</td>
						<td width="10%" align="left">'.$BATCH_STATUS.'</td>
						<td width="10%" align="left">'.$DATE_RECEIVED.'</td>
						<td width="10%" align="left">'.$POSTED_DATE.'</td>
						<td width="15%" align="left">'.$CHECK_NO.'</td>				
						<td width="20%" align="left">'.$CODE	.'</td>
						<td width="13%" align="right">$'.$CREDIT_AMOUNT	.'</td>
				 </tr>  </tbody>';
		
		$res->MoveNext();
	}
	
	$txt .= '</table>';
	
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
$file_name = 'Payment Batch.pdf';
$pdf->Output('temp/'.$file_name, 'FD');

return $file_name;