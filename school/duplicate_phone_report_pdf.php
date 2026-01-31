<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_ssn_report.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');

	
class MYPDF extends TCPDF {
	public function Header() {
		global $db;

		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".",$res->fields['PDF_LOGO']);
			$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(8);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 17);
		$this->SetY(8);
		$this->SetX(160);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, "Duplicate Phone", 0, false, 'L', 0, '', 0, false, 'M', 'L');
					
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(150, 11, 202, 11, $style);
		
	}
	public function Footer() {
		global $db;

		$this->SetY(-15);
		$this->SetX(180);
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

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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
$pdf->SetFont('helvetica', '', 7, '', true);
$pdf->AddPage();

if($_GET['type'] == 1) {
	$field 		= "CELL_PHONE";
	$field_name = CELL_PHONE;
} else if($_GET['type'] == 2) {
	$field 		= "HOME_PHONE";
	$field_name = HOME_PHONE;
} else if($_GET['type'] == 3) {
	$field 		= "OTHER_PHONE";
	$field_name = OTHER_PHONE;
}

$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<thead>
				<tr>
					<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >'.$field_name.'</td>
					<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Last Name</td>
					<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >First Name</td>
					<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student ID</td>
					<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Status</td>
					<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Archived</td>
				</tr>
			</thead>';
$res_phone = $db->Execute($_SESSION['QUERY']);			
while (!$res_phone->EOF) {
	$PHONE = $res_phone->fields[$field];
	
	$PHONE1 = preg_replace( '/[^0-9]/', '',$res_phone->fields[$field]);
	$PHONE1 = '('.$PHONE1[0].$PHONE1[1].$PHONE1[2].') '.$PHONE1[3].$PHONE1[4].$PHONE1[5].'-'.$PHONE1[6].$PHONE1[7].$PHONE1[8].$PHONE1[9];
	
	$res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, FIRST_NAME,LAST_NAME, STUDENT_ID, If(ARCHIVED = 1,'Yes', 'No') as ARCHIVED FROM S_STUDENT_MASTER, S_STUDENT_CONTACT, S_STUDENT_ACADEMICS WHERE $field = '$PHONE' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 AND S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC"); 
	while (!$res_type->EOF){
		$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER']; 
		$res_enroll = $db->Execute("select PK_STUDENT_ENROLLMENT, STUDENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_STUDENT_STATUS ON S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = M_STUDENT_STATUS.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
		
		
		$txt 	.= '<tr>
					<td width="15%" >'.$PHONE1.'</td>
					<td width="15%" >'.$res_type->fields['LAST_NAME'].'</td>
					<td width="15%" >'.$res_type->fields['FIRST_NAME'].'</td>
					<td width="20%" >'.$res_type->fields['STUDENT_ID'].'</td>
					<td width="20%" >'.$res_enroll->fields['STUDENT_STATUS'].'</td>
					<td width="15%" >'.$res_type->fields['ARCHIVED'].'</td>
				</tr>';
				
		$res_type->MoveNext();
	}
	$res_phone->MoveNext();
}
$txt 	.= '</table>';

$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'Duplicate Phone.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');*/
	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	