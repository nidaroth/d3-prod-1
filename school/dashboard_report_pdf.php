<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/dashboard.php");

if($_SESSION['PK_ROLES'] == 4 || $_SESSION['PK_ROLES'] == 5){ 
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
require_once('../global/config.php');
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/dashboard.php");

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
		$this->Cell(75, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		if($_GET['t'] == 1) $str = NEW_LEADS; 
		else if($_GET['t'] == 2) $str = QUALIFIED_LEADS; 
		else if($_GET['t'] == 3) $str = NEW_APPLICATIONS;
		else if($_GET['t'] == 4) $str = NEW_STUDENTS;
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(230);
		$this->Cell(55, 8, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(200, 13, 290, 13, $style);
		
		$res = $db->Execute("SELECT NEW_LEAD_STATUS,QUALIFIED_LEAD_STATUS,NEW_APPLICATIONS_STATUS,NEW_STUDENTS_STATUS, NEW_LEAD_FROM_DATE, NEW_LEAD_TO_DATE, QUALIFIED_LEAD_FROM_DATE, QUALIFIED_LEAD_TO_DATE, NEW_APPLICATIONS_FROM_DATE, NEW_APPLICATIONS_TO_DATE, NEW_STUDENTS_FROM_DATE, NEW_STUDENTS_TO_DATE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");  
		$NEW_LEAD_STATUS 		 = $res->fields['NEW_LEAD_STATUS']; 
		$QUALIFIED_LEAD_STATUS 	 = $res->fields['QUALIFIED_LEAD_STATUS'];
		$NEW_APPLICATIONS_STATUS = $res->fields['NEW_APPLICATIONS_STATUS'];
		$NEW_STUDENTS_STATUS 	 = $res->fields['NEW_STUDENTS_STATUS']; 

		$NEW_LEAD_FROM_DATE 	 	 = $res->fields['NEW_LEAD_FROM_DATE']; 
		$NEW_LEAD_TO_DATE 	 		 = $res->fields['NEW_LEAD_TO_DATE']; 
		$QUALIFIED_LEAD_FROM_DATE 	 = $res->fields['QUALIFIED_LEAD_FROM_DATE']; 
		$QUALIFIED_LEAD_TO_DATE 	 = $res->fields['QUALIFIED_LEAD_TO_DATE']; 
		$NEW_APPLICATIONS_FROM_DATE  = $res->fields['NEW_APPLICATIONS_FROM_DATE']; 
		$NEW_APPLICATIONS_TO_DATE 	 = $res->fields['NEW_APPLICATIONS_TO_DATE']; 
		$NEW_STUDENTS_FROM_DATE 	 = $res->fields['NEW_STUDENTS_FROM_DATE']; 
		$NEW_STUDENTS_TO_DATE 	 	 = $res->fields['NEW_STUDENTS_TO_DATE']; 

		$date = "";

		if($_GET['t'] == 1){
			if($NEW_LEAD_FROM_DATE != '' && $NEW_LEAD_FROM_DATE != '0000-00-00' && $NEW_LEAD_TO_DATE != '' && $NEW_LEAD_TO_DATE != '0000-00-00' )
				$date = date("m/d/Y",strtotime($NEW_LEAD_FROM_DATE))." - ".date("m/d/Y",strtotime($NEW_LEAD_TO_DATE));
			else if($NEW_LEAD_FROM_DATE != '' && $NEW_LEAD_FROM_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($NEW_LEAD_FROM_DATE));
			else if($NEW_LEAD_TO_DATE != '' && $NEW_LEAD_TO_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($NEW_LEAD_TO_DATE));
		}	

		if($_GET['t'] == 2){
			if($QUALIFIED_LEAD_FROM_DATE != '' && $QUALIFIED_LEAD_FROM_DATE != '0000-00-00' && $QUALIFIED_LEAD_TO_DATE != '' && $QUALIFIED_LEAD_TO_DATE != '0000-00-00' )
				$date = date("m/d/Y",strtotime($QUALIFIED_LEAD_FROM_DATE))." - ".date("m/d/Y",strtotime($QUALIFIED_LEAD_TO_DATE));
			else if($QUALIFIED_LEAD_FROM_DATE != '' && $QUALIFIED_LEAD_FROM_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($QUALIFIED_LEAD_FROM_DATE));
			else if($QUALIFIED_LEAD_TO_DATE != '' && $QUALIFIED_LEAD_TO_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($QUALIFIED_LEAD_TO_DATE));
		}	

		if($_GET['t'] == 3){
			if($NEW_APPLICATIONS_FROM_DATE != '' && $NEW_APPLICATIONS_FROM_DATE != '0000-00-00' && $NEW_APPLICATIONS_TO_DATE != '' && $NEW_APPLICATIONS_TO_DATE != '0000-00-00' )
				$date = date("m/d/Y",strtotime($NEW_APPLICATIONS_FROM_DATE))." - ".date("m/d/Y",strtotime($NEW_APPLICATIONS_TO_DATE));
			else if($NEW_APPLICATIONS_FROM_DATE != '' && $NEW_APPLICATIONS_FROM_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($NEW_APPLICATIONS_FROM_DATE));
			else if($NEW_APPLICATIONS_TO_DATE != '' && $NEW_APPLICATIONS_TO_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($NEW_APPLICATIONS_TO_DATE));
		}	

		if($_GET['t'] == 4){
			if($NEW_STUDENTS_FROM_DATE != '' && $NEW_STUDENTS_FROM_DATE != '0000-00-00' && $NEW_STUDENTS_TO_DATE != '' && $NEW_STUDENTS_TO_DATE != '0000-00-00' )
				$date = date("m/d/Y",strtotime($NEW_STUDENTS_FROM_DATE))." - ".date("m/d/Y",strtotime($NEW_STUDENTS_TO_DATE));
			else if($NEW_STUDENTS_FROM_DATE != '' && $NEW_STUDENTS_FROM_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($NEW_STUDENTS_FROM_DATE));
			else if($NEW_STUDENTS_TO_DATE != '' && $NEW_STUDENTS_TO_DATE != '0000-00-00')
				$date = date("m/d/Y",strtotime($NEW_STUDENTS_TO_DATE));
		}

		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(185);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(104, 5, $date, 0, false, 'R', 0, '', 0, false, 'M', 'L');
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

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
			<thead>
				<tr>
					<td width="5%" style="border-bottom:1px solid #000;" ><b>#</b></td>
					<td width="13%" style="border-bottom:1px solid #000;" ><b>'.STUDENT.'</b></td>
					<td width="13%" style="border-bottom:1px solid #000;" ><b>'.STUDENT_ID.'</b></td>
					<td width="13%" style="border-bottom:1px solid #000;" ><b>'.STUDENT_STATUS.'</b></td>
					<td width="8%" style="border-bottom:1px solid #000;" ><b>'.STATUS_DATE.'</b></td>
					<td width="13%" style="border-bottom:1px solid #000;" ><b>'.LEAD_SOURCE.'</b></td>
					<td width="10%" style="border-bottom:1px solid #000;" ><b>'.FIRST_TERM_DATE.'</b></td>
					<td width="13%" style="border-bottom:1px solid #000;" ><b>'.PROGRAM.'</b></td>
					<td width="13%" style="border-bottom:1px solid #000;" ><b>'.ADMISSION_REP.'</b></td>
				</tr>
			</thead>';
			$i = 0;
			$res_type = $db->Execute($_SESSION['REPORT_QUERY']);
			while (!$res_type->EOF) {
				$i++;
				$txt .= '<tr>
							<td width="5%">'.$i.'</td>
							<td width="13%">'.$res_type->fields['STUDENT_NAME'].'</td>
							<td width="13%">'.$res_type->fields['STUDENT_ID'].'</td>
							<td width="13%">'.$res_type->fields['STUDENT_STATUS'].'</td>
							<td width="8%">'.$res_type->fields['STATUS_DATE'].'</td>
							<td width="13%">'.$res_type->fields['LEAD_SOURCE'].'</td>
							<td width="10%">'.$res_type->fields['TERM_DATE'].'</td>
							<td width="13%">'.$res_type->fields['CODE'].'</td>
							<td width="13%">'.$res_type->fields['EMP_NAME'].'</td>
						</tr>';
				$res_type->MoveNext();
			}
		$txt .= '</table>';
		
		////////////////////////////////
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

if($_GET['t'] == 1) $file_name = NEW_LEADS; 
else if($_GET['t'] == 2) $file_name = QUALIFIED_LEADS; 
else if($_GET['t'] == 3) $file_name = NEW_APPLICATIONS;
else if($_GET['t'] == 4) $file_name = NEW_STUDENTS;
$file_name .= '.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	