<?php session_start();
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
require_once("check_access.php");
require_once("../language/ecm_ledger.php");

$res_add_on = $db->Execute("SELECT ECM FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_TITLE_IV_SERVICER') == 0 || $res_add_on->fields['ECM'] == 0){
	header("location:../index");
	exit;
}

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
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(230);
		$this->Cell(55, 8, 'ECM Import Result', 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(200, 13, 290, 13, $style);
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
					<td width="8%" style="border-bottom:1px solid #000;" ><br /><br /><b>'.SSN.'</b></td>
					<td width="18%" style="border-bottom:1px solid #000;" ><br /><br /><b>'.STUDENT.'</b></td>
					<td width="20%" style="border-bottom:1px solid #000;" ><br /><br /><b>'.IMPORT_RESULT.'</b></td>
					<td width="14%" style="border-bottom:1px solid #000;" ><br /><br /><b>'.LEDGER_CODE_1.'</b></td>
					<td width="10%" style="border-bottom:1px solid #000;" ><b>'.DISBURSEMENT_DATE.'</b></td>
					<td width="9%" style="border-bottom:1px solid #000;" align="right" ><b>'.DISBURSEMENT_AMOUNT.'</b></td>
					<td width="12%" style="border-bottom:1px solid #000;" ><b>'.ECM_DISBURSEMENT_DATE.'</b></td>
					<td width="9%" style="border-bottom:1px solid #000;" align="right" ><br /><br /><b>'.ECM_DISBURSEMENT_AMOUNT.'</b></td>
				</tr>
			</thead>';
			$TOT_DISBURSEMENT_AMOUNT = 0;
			$TOT_ECM_DISBURSEMENT_AMOUNT = 0;
			$res_type = $db->Execute($_SESSION['query']);
			while (!$res_type->EOF) {
				if($res_type->fields['DISBURSEMENT_DATE'] != '' && $res_type->fields['DISBURSEMENT_DATE'] != '0000-00-00')
					$DISBURSEMENT_DATE = date("m/d/Y",strtotime($res_type->fields['DISBURSEMENT_DATE']));
				else
					$DISBURSEMENT_DATE = '';
					
				if($res_type->fields['ECM_DISBURSEMENT_DATE'] != '' && $res_type->fields['ECM_DISBURSEMENT_DATE'] != '0000-00-00')
					$ECM_DISBURSEMENT_DATE = date("m/d/Y",strtotime($res_type->fields['ECM_DISBURSEMENT_DATE']));
				else
					$ECM_DISBURSEMENT_DATE = '';
					
				$TOT_DISBURSEMENT_AMOUNT 	 += $res_type->fields['DISBURSEMENT_AMOUNT'];
				$TOT_ECM_DISBURSEMENT_AMOUNT += $res_type->fields['ECM_DISBURSEMENT_AMOUNT'];
					
				$txt .= '<tr>
							<td width="8%">'.my_decrypt('',$res_type->fields['SSN']).'</td>
							<td width="18%">'.$res_type->fields['NAME'].'</td>
							<td width="20%">'.$res_type->fields['MESSAGE'].'</td>
							<td width="14%">'.$res_type->fields['CODE'].'</td>
							<td width="10%">'.$DISBURSEMENT_DATE.'</td>
							<td width="9%" align="right" >$ '.number_format_value_checker($res_type->fields['DISBURSEMENT_AMOUNT'],2).'</td>
							<td width="12%" >'.$ECM_DISBURSEMENT_DATE.'</td>
							<td width="9%" align="right">$ '.number_format_value_checker($res_type->fields['ECM_DISBURSEMENT_AMOUNT'],2).'</td>
						</tr>';
				$res_type->MoveNext();
			}
			
			$txt .= '<tr>
						<td width="8%"></td>
						<td width="18%"></td>
						<td width="20%"></td>
						<td width="14%"></td>
						<td width="10%" style="border-top:1px solid #000;" ><b>Total</b></td>
						<td width="9%" style="border-top:1px solid #000;" align="right"><b>$ '.number_format_value_checker($TOT_DISBURSEMENT_AMOUNT,2).'</b></td>
						<td width="12%" style="border-top:1px solid #000;" ></td>
						<td width="9%" style="border-top:1px solid #000;" align="right"><b>$ '.number_format_value_checker($TOT_ECM_DISBURSEMENT_AMOUNT,2).'</b></td>
					</tr>';
$txt .= '</table>';
		
		////////////////////////////////
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$file_name = 'ECM Import Result.pdf';
/*if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	