<?php require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

require_once '../global/mpdf/vendor/autoload.php';

$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

$logo = "";
if($PDF_LOGO != '')
	$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	
$header = '<table width="100%" >
				<tr>
					<td width="20%" valign="top" >'.$logo.'</td>
					<td width="40%" valign="top" style="font-size:18px" >'.$SCHOOL_NAME.'</td>
					<td width="40%" valign="top" >
						<table width="100%" >
							<tr>
								<td width="100%" align="right" style="font-size:18px;border-bottom:1px solid #000;" ><b>Course Master Listing</b></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>';
			
$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
			
$footer = '<table width="100%" >
				<tr>
					<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
					<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>ACCT11010</i></td>
					<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
				</tr>
			</table>';

$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 7,
	'margin_right' => 5,
	'margin_top' => 20,
	'margin_bottom' => 15,
	'margin_header' => 3,
	'margin_footer' => 10,
	'default_font_size' => 8
]);
$mpdf->autoPageBreak = true;

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);

$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
			<thead>
				<tr>
					<td width="13%" style="border-bottom:1px solid #000;">
						<b>Course<br />Code</b>
					</td>
					<td width="13%" style="border-bottom:1px solid #000;">
						<b>Transcript<br />Code</b>
					</td>
					<td width="18%" style="border-bottom:1px solid #000;">
						<b>Course<br />Description</b>
					</td>
					<td width="10%" style="border-bottom:1px solid #000;" align="right">
						<br /><b>FA Units</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000;" align="right">
						<br /><b>Units</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000;" align="right">
						<br /><b>Hours</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000;" align="right">
						<b>Active<br />Status</b>
					</td>
					<td width="7%" style="border-bottom:1px solid #000;" align="right">
						<b>Class<br />Size</b>
					</td>
					<td width="10%" style="border-bottom:1px solid #000;" align="center">
						<b>Default<br />Att. Code</b>
					</td>
					<td width="10%" style="border-bottom:1px solid #000;" align="right" >
						<br /><b>Course Fees</b>
					</td>
				</tr>
			</thead>
			<tbody>';
			
		$res = $db->Execute($_SESSION['query']." ORDER BY S_COURSE.ACTIVE DESC, COURSE_CODE ASC ");
		while (!$res->EOF) {
			$PK_COURSE 	= $res->fields['PK_COURSE'];
			$res_fee 	= $db->Execute("SELECT SUM(FEE_AMT) as FEE_AMT FROM S_COURSE_FEE WHERE PK_COURSE = '$PK_COURSE' ");
			$FEE 		= $res_fee->fields['FEE_AMT'];
			
			$txt .= '<tr >
						<td >
							'.$res->fields['COURSE_CODE'].'
						</td>
						<td >
							'.$res->fields['TRANSCRIPT_CODE'].'
						</td>
						<td >
							'.$res->fields['COURSE_DESCRIPTION'].'
						</td>
						<td align="right" >
							'.$res->fields['FA_UNITS'].'
						</td>
						<td align="right" >
							'.$res->fields['UNITS'].'
						</td>
						<td align="right" >
							'.$res->fields['HOURS'].'
						</td>
						<td align="right" >
							'.$res->fields['ACTIVE_1'].'
						</td>
						<td align="right" >
							'.$res->fields['MAX_CLASS_SIZE'].'
						</td>
						<td align="center" >
							'.$res->fields['CODE'].'
						</td>
						<td align="right" >
							$ '.$FEE.'
						</td>
					</tr>';
			$res->MoveNext();
		}
		
		$txt .= '</tbody>
			</table>';
		
	//echo $txt;exit;
$mpdf->WriteHTML($txt);
$file_name = 'Course Master List.pdf';
$mpdf->Output($file_name, 'D');

return $file_name;	