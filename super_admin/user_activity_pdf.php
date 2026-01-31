<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/user_activity.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
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
		
		$this->Image("../backend_assets/images/DDlogo_FullColor_333.png", 8, 3, 0, 18, "png", '', 'T', false, 300, '', false, false, 0, false, false, false);
		
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(6);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		/*$this->SetFont('helvetica', '', 8);
		$this->SetY(13);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(17);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8,$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(21);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');*/
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(9);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(250);
		$this->Cell(55, 8, "User Activity", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(210, 13, 293, 13, $style);
		
		/*$res = $db->Execute("SELECT BATCH_NO FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$this->SetFont('helvetica', 'I', 13);
		$this->SetY(16);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(150);
		$this->Cell(55, 7, "Batch # ".$res->fields['BATCH_NO'], 0, false, 'R', 0, '', 0, false, 'M', 'L');*/
		
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

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
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
				<td width="15%" style="border-bottom:1px solid #000;">
					<b><i>Account Name</i></b>
				</td>
				<td width="15%" style="border-bottom:1px solid #000;">
					<b><i>'.USER_TYPE.'</i></b>
				</td>
				<td width="15%" style="border-bottom:1px solid #000;">
					<b><i>'.USER_NAME.'</i></b>
				</td>
				<td width="15%" style="border-bottom:1px solid #000;">
					<b><i>'.LOGIN_ID.'</i></b>
				</td>
				
				<td width="20%" style="border-bottom:1px solid #000;">
					<b><i>'.LOGIN_TIME.'</i></b>
				</td>
				
				<td width="20%" style="border-bottom:1px solid #000;">
					<b><i>'.LOGOUT_TIME.'</i></b>
				</td>
			</tr>
		</thead>';

	
	$res = $db->Execute($_SESSION['QUERY']); 
	while (!$res->EOF) { 
	
		$LOGIN_TIME = convert_to_user_date($res->fields['LOGIN_TIME'],'l, M d, Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
	
		if($res->fields['LOGOUT_TIME'] == '' || $res->fields['LOGOUT_TIME'] == '0000-00-00 00:00:00')
			$LOGOUT_TIME = 'User did not log out';
		else
			$LOGOUT_TIME = convert_to_user_date($res->fields['LOGOUT_TIME'],'l, M d, Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
		
		$txt .= '<tr>
				<td width="15%" >'.$res->fields['SCHOOL_NAME'].'</td>
				<td width="15%" >'.$res->fields['ROLES'].'</td>
				<td width="15%" >'.$res->fields['NAME'].'</td>
				<td width="15%" >'.$res->fields['USER_ID'].'</td>
				<td width="20%" >'.$LOGIN_TIME.'</td>
				<td width="20%" >'.$LOGOUT_TIME.'</td>
			</tr>';
		
		$res->MoveNext();
	}
	
	$txt .= '</table>';
	
//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'User Activity.pdf';
/*
if($browser == 'Safari')
$pdf->Output('temp/'.$file_name, 'FD');
else	
$pdf->Output($file_name, 'I');
*/
$pdf->Output('../school/temp/'.$file_name, 'FD');

return $file_name;