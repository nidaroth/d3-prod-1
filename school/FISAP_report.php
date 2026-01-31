<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/FISAP_report.php");
require_once("check_access.php");

function formatPriceRanges($input) {
	if(preg_match('/[a-zA-Z]/', $input) === 1){
		return $input;
	}
	if( strpos($input, '-')  === false	){
		if( strpos($input, '+')  !== false	){
			return "$".number_format(str_replace('+' , '',$input))." and over";
		}
		else{
			return "$".number_format($input);
		}
	}else{
		// Define a regular expression pattern to match the price ranges
		$pattern = '/(\d+)-(\d+)/';

		// Define a callback function to format the matched prices
		$callback = function ($matches) {
			$price1 = number_format($matches[1]);
			$price2 = number_format($matches[2]);
			return "$$price1 - $$price2";
		};

		// Use preg_replace_callback to apply the formatting to the input string
		$amount_range = preg_replace_callback($pattern, $callback, $input);

		return $amount_range;
	}
	
}

$res_add_on = $db->Execute("SELECT FISAP FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['FISAP'] == 0 || check_access('MANAGEMENT_FISAP') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$res = $db->Execute("SELECT AWARD_YEAR FROM M_AWARD_YEAR WHERE PK_AWARD_YEAR = '$_POST[PK_AWARD_YEAR]' ");
	$AWARD_YEAR	 = $res->fields['AWARD_YEAR'];
	$REPORT_TYPE = "";
	$REPORT_NAME = "";
	
	if($_POST['REPORT_TYPE'] == 1) {
		$REPORT_NAME 		= "FISAP Part II Section D - Traditional";
		$SUB_REPORT_NAME 	= "School With Traditional Calendar";	
		$SP_REPORT_TYPE 	= "Part II Section D - Traditional Calendar";
	} else if($_POST['REPORT_TYPE'] == 2) {
		$REPORT_NAME 		= "FISAP Part II Section D";
		$SUB_REPORT_NAME 	= "School With Non-traditional Calendar";		
		$SP_REPORT_TYPE 	= "Part II Section D - Non-traditional Calendar";
	} else if($_POST['REPORT_TYPE'] == 3) {
		$REPORT_NAME 		= "FISAP Part II Section F";
		$SUB_REPORT_NAME 	= "";		
		$SP_REPORT_TYPE 	= "Part II Section F";
		
	} else if($_POST['REPORT_TYPE'] == 4) {
		$REPORT_NAME 		= "FISAP Part VI Section A";
		$SUB_REPORT_NAME 	= "";
		$SP_REPORT_TYPE 	= "Part VI Section A";
		
	} else if($_POST['REPORT_TYPE'] == 5) {
		$REPORT_NAME 		= "FISAP Program Review";
		$SUB_REPORT_NAME 	= "";
		$SP_REPORT_TYPE 	= "Program Review";
	} else if($_POST['REPORT_TYPE'] == 6) {
		$REPORT_NAME 		= "FISAP Student Review";
		$SUB_REPORT_NAME 	= "";
		$SP_REPORT_TYPE 	= "Student Review";
	}

	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];
		
		if($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];
		
		$res_campus->MoveNext();
	}

	if($_POST['FORMAT'] == 1) {
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		
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
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>'.$REPORT_NAME.'</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >'.$SUB_REPORT_NAME.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Award Year: '.$AWARD_YEAR.'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus: '.$campus_name.'</td>
						</tr>
					</table>';
					
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		if($_POST['REPORT_TYPE'] == 2)
			$REPORT_NAME = "FISAP Part II Section D - Non-Traditional";
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>COMP20100</i></td>
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
			'default_font_size' => 9
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
	
		
		$i = 0;
		if($_POST['REPORT_TYPE'] == 1) {
			$txt = "";
		} else if($_POST['REPORT_TYPE'] == 2) {
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">';
				$txt .= '<thead>
							<tr>
								<td colspan="2" style="border-top:1px solid #000;border-left:1px solid #000;">
								</td>
								<td colspan="3" style="border-top:1px solid #000;border-left:1px solid #000;" align="center" >
									<b><i>Undergraduate</i></b>
								</td>
								<td colspan="3" style="border-top:1px solid #000;border-left:1px solid #000;" align="center" >
									<b><i>Graduate</i></b>
								</td>
								<td colspan="3" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" align="center" >
									<b><i>Total</i></b>
								</td>
							</tr>
							<tr>
								<td width="8%" style="border-bottom:1px solid #000;border-left:1px solid #000;">
									<b><i>Month</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>Year</i></b>
								</td>
								
								<td width="10%" style="border-bottom:1px solid #000;border-left:1px solid #000;" align="center" >
									<b><i>Continuing<br />(a)</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;" align="center" >
									<b><i>New Start<br />(b)</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;" align="center" >
									<b><i>Ended</i><br /><br /><br /></b>
								</td>
								
								<td width="10%" style="border-bottom:1px solid #000;border-left:1px solid #000;" align="center" >
									<b><i>Continuing<br />(c)</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;" align="center" >
									<b><i>New Start<br />(d)</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;" align="center" >
									<b><i>Ended</i><br /><br /><br /></b>
								</td>
								
								<td width="10%" style="border-bottom:1px solid #000;border-left:1px solid #000;" align="center" >
									<b><i>Continuing</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;" align="center" >
									<b><i>New Start</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="center" >
									<b><i>Ended</i><br /></b>
								</td>
							</tr>
						</thead>';
				
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'summary')");
			while (!$res->EOF) { 
				
					$txt .= '<tr>';
					
					if($res->fields['RECORD_TYPE'] == "DETAIL") {
						$border = "";
						$b1 	= "";
						$b2 	= "";
						$txt .= '<td style="border-left:1px solid #000;" >'.$res->fields['PERIOD_MONTH_TEXT'].'</td>
								<td >'.$res->fields['PERIOD_YEAR'].'</td>';
					} else {
						$border = "border-top:1px solid #000;;border-bottom:1px solid #000;border-bottom:1px solid #000;";
						$b1 	= "<b>";
						$b2 	= "</b>";
						$txt .= '<td style="border-left:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;" colspan="2" ><b>Total</b></td>';
					}
		
					$txt .= '<td align="center" style="border-left:1px solid #000;'.$border.'" >'.$b1.$res->fields['UNDERGRADUATE_CONTINUING'].$b2.'</td>
							<td align="center" style="'.$border.'" >'.$b1.$res->fields['UNDERGRADUATE_NEW_START'].$b2.'</td>
							<td align="center" style="'.$border.'" >'.$b1.$res->fields['UNDERGRADUATE_ENDED'].$b2.'</td>
							
							<td align="center" style="border-left:1px solid #000;'.$border.'" >'.$b1.$res->fields['GRADUATE_CONTINUING'].$b2.'</td>
							<td align="center" style="'.$border.'" >'.$b1.$res->fields['GRADUATE_NEW_START'].$b2.'</td>
							<td align="center" style="'.$border.'" >'.$b1.$res->fields['GRADUATE_ENDED'].$b2.'</td>
							
							<td align="center" style="border-left:1px solid #000;'.$border.'" >'.$b1.$res->fields['TOTAL_CONTINUING'].$b2.'</td>
							<td align="center" style="'.$border.'" >'.$b1.$res->fields['TOTAL_NEW_START'].$b2.'</td>
							<td style="border-right:1px solid #000;'.$border.'" align="center" >'.$b1.$res->fields['TOTAL_ENDED'].$b2.'</td>';
							
					$txt .= '</tr>';
				
				$res->MoveNext();
			}
			
			$txt .= '</table>';
		} else if($_POST['REPORT_TYPE'] == 3) {
			$txt = '';
			include('fisap_report_part_II_section_f_pdf.php');
				
		} else if($_POST['REPORT_TYPE'] == 4) {	
			
			
			$txt .= '  

<style> td{font-size : 10px !important}</style>
		<div>	OPEID Number <b>______________________________________</b> State <b>______________ </b>

			<h2>Part VI. Program Summary for Award Year '.$AWARD_YEAR.'</h2>

			<h3 style="font-weight:normal">Section A. Distribution of Program Recipients and Expenditures by Type of Student </h3>
		</div>

			<table border="0" cellspacing="0" cellpadding="4" width="100%">';
				// $txt .= '<thead>
				// 			<tr>
				// 				<td ></td>
				// 				<td align="center" colspan="2"><b>Federal Perkins Loan</b></td>
				// 				<td align="center" colspan="2"><b>FSEOG</b></td>
				// 				<td align="center" colspan="2"><b>FWS</b></td>
				// 				<td align="center"><b>Unduplicated</b></td>
				// 			</tr>
				// 			<tr>
				// 				<td width="15%" > Undergraduate
				// 				Dependent -
				// 				Taxable and Untaxed
				// 				Income</td>
				// 				<td width="12%"  align="right" >
				// 					<b><i>Recipients</i></b>
				// 				</td>
				// 				<td width="12%" >
				// 					<b><i>Funds</i></b>
				// 				</td>
								
				// 				<td width="12%"  align="right" >
				// 					<b><i>Recipients</i></b>
				// 				</td>
				// 				<td width="12%"  >
				// 					<b><i>Funds</i></b>
				// 				</td>
								
				// 				<td width="12%" align="right" >
				// 					<b><i>Recipients</i></b>
				// 				</td>
				// 				<td width="12%" >
				// 					<b><i>Funds</i></b>
				// 				</td>
								
				// 				<td width="13%" align="right" >
				// 					<b><i>Recipients</i></b>
				// 				</td>
				// 			</tr>
				// 		</thead>';
			
				$txt .= '
				
				 
				 
				<tr>
					<td width="18%" align="left"  > <b>Undergraduate
					Dependent -
					Taxable and Untaxed
					Income<b></td>
					<td width="12%"  valign="top" align="left" style="border:1px solid black ; border-left:hidden" >
						<b><i>Federal Perkins Loan Recipients <br>(a)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>Federal Perkins Loan Funds <br>(b)</i></b>
					</td>
					
					<td width="11%"  valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FSEOG Recipients <br>(c)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FSEOG Funds <br>(d)</i></b>
					</td>
					
					<td width="11%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FWS Recipients <br>(e)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FWS Funds <br>(f)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden;border-right:hidden" >
						<b><i>Unduplicated Recipients</i></b>
					</td>
				</tr>
			 ';	

			$DETAIL1 = 0;
			$DETAIL2 = 0;
			// echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'summary')";exit;
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'summary')");
		$income_grp_index = 1;
			while (!$res->EOF) { 
				
					if($res->fields['RECORD_TYPE'] == "DETAIL1" && $DETAIL1 == 0) {
						// $txt .= '<tr><td colspan="8"><b>Dependent</b></td></tr>';
						$DETAIL1 = 1;
					} else if($res->fields['RECORD_TYPE'] == "DETAIL2" && $DETAIL2 == 0 ) {
						$txt .= '<tr><td colspan="8"   ></td></tr>';
						$txt .= '
				
			 <tr><td colspan="8"><br> </td></tr>
				<tr>
					<td width="17%" align="left"  > <b>Undergraduate
					Independent -
					Taxable and Untaxed
					Income<b></td>
					<td width="12%"  valign="top" align="left" style="border:1px solid black ; border-left:hidden" >
						<b><i>Federal Perkins Loan Recipients <br>(a)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>Federal Perkins Loan Funds <br>(b)</i></b>
					</td>
					
					<td width="12%"  valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FSEOG Recipients <br>(c)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FSEOG Funds <br>(d)</i></b>
					</td>
					
					<td width="11%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FWS Recipients <br>(e)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden" >
						<b><i>FWS Funds <br>(f)</i></b>
					</td>
					<td width="12%" valign="top" align="left" style="border:1px solid black; border-left:hidden;border-right:hidden" >
						<b><i>Unduplicated Recipients</i></b>
					</td>
				</tr> ';
						// $txt .= '<tr><td colspan="8"><b>Independent</b></td></tr>';
						$DETAIL2 = 1;
					}
					if($res->fields['RECORD_TYPE'] == "TOTAL1" || $res->fields['RECORD_TYPE'] == "TOTAL2" || $res->fields['RECORD_TYPE'] == "TOTAL3"){
						if($res->fields['RECORD_TYPE'] == "TOTAL1"){
							$styler_total = "height:85px;";
						}
							else{
								$styler_total = "height:35px;";
							// $txt .= '<tr><td colspan="8" ><br /><br /></td></tr>';
						}
						$txt .= '<tr style="'.$styler_total.'">
									<td  > <br> <b>'.$res->fields['INCOME_GROUP'].' </b><br>(fields 1-'.($income_grp_index-1).')<br></td>
									<td style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >'.$res->fields['PERKINS_COUNT'].'</td>
									<td style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >$ '.number_format_value_checker($res->fields['PERKINS'],2).' </td>
									
									<td style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >'.$res->fields['FSEOG_COUNT'].'</td>
									<td style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >$ '.number_format_value_checker($res->fields['FSEOG'],2).'</td>
									
									<td style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >'.$res->fields['FWS_COUNT'].'</td>
									<td style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >$ '.number_format_value_checker($res->fields['FWS'],2).'</td>
									
									<td style="  border-bottom:1px solid black" align="left" >'.$res->fields['UNDUPLICATED_COUNT'].'</td>
								</tr>';
								
					} else {

						 if( strtolower($res->fields['INCOME_GROUP']) == strtolower('Graduate/Professional')){
							$res->fields['INCOME_GROUP'] = str_replace('/' , ' / ' , $res->fields['INCOME_GROUP']);
						 }
						$income_grp = formatPriceRanges($res->fields['INCOME_GROUP']);
						
						$txt .= '<tr>
									<td  > <b>'.$income_grp_index.'. '.$income_grp.'</b></td>
									<td  style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >'.$res->fields['PERKINS_COUNT'].'</td>
									<td  style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >$ '.number_format_value_checker($res->fields['PERKINS'],2).'</td>
									
									<td  style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >'.$res->fields['FSEOG_COUNT'].'</td>
									<td  style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >$ '.number_format_value_checker($res->fields['FSEOG'],2).'</td>
									
									<td  style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >'.$res->fields['FWS_COUNT'].'</td>
									<td  style="border-right:1px solid black ; border-bottom:1px solid black" align="left" >$ '.number_format_value_checker($res->fields['FWS'],2).'</td>
									
									<td  style="  border-bottom:1px solid black" align="left" >'.$res->fields['UNDUPLICATED_COUNT'].'</td>
								</tr>';
								$income_grp_index = $income_grp_index + 1 ;
					}
					
				
				$res->MoveNext();
			}
			
			$txt .= '</table>';
		} else if($_POST['REPORT_TYPE'] == 5) {
			$txt = "";
		} else if($_POST['REPORT_TYPE'] == 6) {
			$txt = "";
		}
		// echo $txt; exit;
		$mpdf->WriteHTML($txt);
		$mpdf->Output($REPORT_NAME."_".$_SESSION['PK_USER']."_".time().'.pdf', 'D');
		
	} else if($_POST['FORMAT'] == 2) {
		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		$total_fields = 120;
		for($i = 0 ; $i <= $total_fields ; $i++){
			if($i <= 25)
				$cell[] = $cell1[$i];
			else {
				$j = floor($i / 26) - 1;
				$k = ($i % 26);
				//echo $j."--".$k."<br />";
				$cell[] = $cell1[$j].$cell1[$k];
			}	
		}
		
		if($_POST['REPORT_TYPE'] == 2)
			$REPORT_NAME = "FISAP Part II Section D - Non-Traditional";
		
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= $REPORT_NAME.'.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		if($_POST['REPORT_TYPE'] == 1) {
			$line 	= 1;	
			$index 	= -1;
		
			$heading[] = 'Student';
			$width[]   = 25;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20; 
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Start Date';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'Credential Level';
			$width[]   = 20;
			$heading[] = 'Category';
			$width[]   = 20;
			
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			// echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')";exit;
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')");
			while (!$res->EOF) {
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']); 

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['START_DATE'])));
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res->fields['END_DATE'] != '' && $res->fields['END_DATE'] != '0000-00-00')
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['END_DATE'])));
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREDENTIAL_LEVEL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CATEGORY']);
				
				
				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 2) {
			$line 	= 1;	
			$index 	= -1;
		
			$heading[] = 'Student Name';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Start Date';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'Credential Level';
			$width[]   = 20;
			$heading[] = 'Category';
			$width[]   = 20; 
			$heading[] = 'Award Year';
			$width[]   = 20;
			$heading[] = 'July';
			$width[]   = 20;
			$heading[] = 'August';
			$width[]   = 20;
			$heading[] = 'September';
			$width[]   = 20;
			$heading[] = 'October';
			$width[]   = 20;
			$heading[] = 'November';
			$width[]   = 20;
			$heading[] = 'December';
			$width[]   = 20;
			$heading[] = 'January';
			$width[]   = 20;
			$heading[] = 'February';
			$width[]   = 20;
			$heading[] = 'March';
			$width[]   = 20;
			$heading[] = 'April';
			$width[]   = 20;
			$heading[] = 'May';
			$width[]   = 20;
			$heading[] = 'June';
			$width[]   = 20;
		
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			//echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')";exit;
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')");
			while (!$res->EOF) {
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['START_DATE'])));
					
				$index++;
				$cell_no = $cell[$index].$line;
				if($res->fields['END_DATE'] != '' && $res->fields['END_DATE'] != '0000-00-00')
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['END_DATE'])));
					
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREDENTIAL_LEVEL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CATEGORY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res->fields['AWARD_YEAR'] != '' && $res->fields['AWARD_YEAR'] != '0000-00-00')
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AWARD_YEAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['JUL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AUG']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SEP']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OCT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOV']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DEC']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['JAN']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FEB']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['APR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MAY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['JUN']);
				
				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 3) {
			$line 	= 1;	
			$index 	= -1;
		
			$heading[] = 'Income Group';
			$width[]   = 20;
			$heading[] = 'Student Name';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;			
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Start Date';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'Award Year';
			$width[]   = 20;
			$heading[] = 'Dependent Status';
			$width[]   = 20;
			$heading[] = 'Student Income';
			$width[]   = 20;
			$heading[] = 'Parent Income';
			$width[]   = 20;
			$heading[] = 'Income';
			$width[]   = 20;
			$heading[] = 'Income Level';
			$width[]   = 20;
			$heading[] = 'Automatic Zero EFC';
			$width[]   = 20;
		
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			// echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')";
			// exit;
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')");
			while (!$res->EOF) {
				if(stristr(trim($res->fields['RECORD_TYPE']), "HEADER") == ""){ 
					$line++;
					$index = -1;
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['INCOME_GROUP']);
										
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);
											
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date('m/d/Y',strtotime($res->fields['START_DATE'])));

					$index++;
					$cell_no = $cell[$index].$line;
					if($res->fields['END_DATE'] != '' && $res->fields['END_DATE'] != '0000-00-00')
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date('m/d/Y',strtotime($res->fields['END_DATE'])));

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AWARD_YEAR']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DEPENDENT_STATUS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_INCOME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PARENT_INCOME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['INCOME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['INCOME_LEVEL']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AUTOMATIC_ZERO_EFC']);
				}
				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 4) {
			$line 	= 1;	
			$index 	= -1;
		
			$heading[] = 'Student Name';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Start Date';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'Dependent Status';
			$width[]   = 20;
			$heading[] = 'Student Income';
			$width[]   = 20;
			$heading[] = 'Parent Income';
			$width[]   = 20;
			$heading[] = 'FISAP Income';
			$width[]   = 20;
			$heading[] = 'Income Group';
			$width[]   = 20;
			$heading[] = 'PERKINS Count';
			$width[]   = 20;
			$heading[] = 'PERKINS';
			$width[]   = 20;
			$heading[] = 'FSEOG Count';
			$width[]   = 20;
			$heading[] = 'FSEOG';
			$width[]   = 20;
			$heading[] = 'FWS Count';
			$width[]   = 20;
			$heading[] = 'FWS';
			$width[]   = 20;
		
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			//echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')";exit;
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')");
			while (!$res->EOF) {
				if(stristr(trim($res->fields['RECORD_TYPE']), "HEADER") == ""){ 
					$line++;
					$index = -1;
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['START_DATE'])));
						
					$index++;
					$cell_no = $cell[$index].$line;
					if($res->fields['END_DATE'] != '' && $res->fields['END_DATE'] != '0000-00-00')
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['END_DATE'])));
						

					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DEPENDENT_STATUS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_INCOME']);
			
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PARENT_INCOME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FISAP_INCOME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['INCOME_GROUP']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PERKINS_COUNT']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PERKINS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FSEOG_COUNT']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FSEOG']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FWS_COUNT']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FWS']);
				}
				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 5) {
			$line 	= 1;	
			$index 	= -1;
		
			
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'FISAP Setup';
			$width[]   = 20;
			$heading[] = 'Credential Level';
			$width[]   = 20;
			$heading[] = 'Total Enrollment Count';
			$width[]   = 20;
			$heading[] = 'FISAP Year Enrollment Count';
			$width[]   = 20;
			$heading[] = 'Active';
			$width[]   = 20;
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			//echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')";exit;
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')");
			while (!$res->EOF) {
				$line++;
				$index = -1;
				
				
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
				if($res->fields['PROGRAM_CODE'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);
				if($res->fields['PROGRAM_DESCRIPTION'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FISAP_SETUP']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREDENTIAL_LEVEL']);
				if($res->fields['CREDENTIAL_LEVEL'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TOTAL_ENROLLMENT_COUNT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FISAP_YEAR_ENROLLMENT_COUNT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ACTIVE']);

				$res->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 6) {
			$line 	= 1;	
			$index 	= -1;
		
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Program Code';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Program Group';
			$width[]   = 20;
			$heading[] = 'Full/Part Time';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Start Date';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20; 
			$heading[] = 'OPEID';
			$width[]   = 20;
			$heading[] = 'Award Year';
			$width[]   = 20;
			$heading[] = 'FISAP FA Record';
			$width[]   = 20; 
			$heading[] = 'EFC NO';
			$width[]   = 20;
			$heading[] = 'Automatic Zero EFC';
			$width[]   = 20;
			$heading[] = 'Dependent Status';
			$width[]   = 20;
			$heading[] = 'Student Income';
			$width[]   = 20;
			$heading[] = 'Parent Income';
			$width[]   = 20;
			$heading[] = 'FISAP Ledger Transactions';
			$width[]   = 20;
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');

			// echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')";exit;
			$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'Detail')");
			while (!$res->EOF) {
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_GROUP']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FULL_PART_TIME']);
				if($res->fields['FULL_PART_TIME'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
					
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);

				$index++;
				$cell_no = $cell[$index].$line;
				if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['START_DATE'])));
					
				$index++;
				$cell_no = $cell[$index].$line;
				if($res->fields['END_DATE'] != '' && $res->fields['END_DATE'] != '0000-00-00')
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(date("Y-m-d", strtotime($res->fields['END_DATE'])));	
				
			
		
				
				
				
				
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OPEID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AWARD_YEAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FISAP_FA_RECORD']);
				if(strtoupper(trim($res->fields['FISAP_FA_RECORD'])) == "MISSING FA")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EFC_NO']);
				if($res->fields['EFC_NO'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AUTOMATIC_ZERO_EFC']);
				if($res->fields['AUTOMATIC_ZERO_EFC'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DEPENDENT_STATUS']);
				if($res->fields['DEPENDENT_STATUS'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_INCOME']);
				if($res->fields['STUDENT_INCOME'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PARENT_INCOME']);
				if($res->fields['PARENT_INCOME'] == "")
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("FFFFFF00");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FISAP_LEDGER_TRANSACTIONS']);
		
				$res->MoveNext();
			}
		}

		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
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
	<title><?=MNU_FISAP_REPORT ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.option_red > a > label{color:red !important}
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
                        <h4 class="text-themecolor">
							<?=MNU_FISAP_REPORT ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-8">
											<div class="row form-group">
												<div class="col-md-3">
													<span class="bar"></span> 
													<label ><?=REPORTING_YEAR?></label>
												</div>
											</div>
											<div class="row">
												<div class="col-md-11" style="margin-left:25px" >
													Fiscal Operation Report for <?=date("Y")-1?>-<?=date("y")?> and Application to Participate <?=date("Y")+1?>-<?=date("y")+2?>
												</div>
											</div>
											
											<br />
											<div class="row form-group">
												<div class="col-md-3">
													<span class="bar"></span> 
													<label ><?=AWARD_YEAR?></label>
												</div>
											</div>
											<div class="row">
												<div class="col-md-10" style="margin-left:25px" >
													<? $YEAR = date("Y") - 1;
													$res = $db->Execute("SELECT PK_AWARD_YEAR, AWARD_YEAR FROM M_AWARD_YEAR WHERE YEAR(BEGIN_DATE) = '$YEAR' AND ACTIVE = 1 "); ?>
													<input type="hidden" name="PK_AWARD_YEAR" id="PK_AWARD_YEAR" value="<?=$res->fields['PK_AWARD_YEAR'] ?>" >
													<?=$res->fields['AWARD_YEAR'] ?>
												</div>
											</div>
											
											<br /><br />
											<div class="row">
												<div class="col-5 col-sm-5 col-md-5 focused">
													<span class="bar"></span> 
													<label ><?=REPORT_TYPE?></label>
												</div>
												
												<div class="col-3 col-sm-3 col-md-3 focused">
													<span class="bar"></span> 
													<label ><?=CAMPUS?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-5">
													<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" onchange="show_fields()" >
														<option value="1" >Part II Section D - Traditional Calendar</option>
														<option value="2" >Part II Section D - Non-traditional Calendar</option>
														<option value="3" >Part II Section F</option>
														<option value="4" >Part VI Section A</option>
														<option value="5" >Program Review</option>
														<option value="6" >Student Review</option>
													</select>
												</div>
											
												<div class="col-md-3 ">
													<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
														<? $res_type = $db->Execute("SELECT CAMPUS_CODE,PK_CAMPUS,ACTIVE FROM S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, CAMPUS_CODE ASC");
														while (!$res_type->EOF) 
														{ 
															$option_label = $res_type->fields['CAMPUS_CODE'];
															if($res_type->fields['ACTIVE'] == 0)
															{
																$option_label .= " (Inactive)";
															}
															?>
															<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<!-- <div class="validation-advice" id="advice-required-entry-PK_CAMPUS" style="">This is a required field.</div> -->
													<style>
														#advice-required-entry-PK_CAMPUS {
															position: absolute;
															bottom: -35px;
														}
													</style>
												</div>
												
												<div class="col-md-2" style="padding: 0;" >
													<button type="button" onclick="submit_form(1)" id="PDF_BUTTON" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
													<button type="button" onclick="submit_form(2)" id="EXCEL_BUTTON" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
													<input type="hidden" name="FORMAT" id="FORMAT" >
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-10 text-right" >
													<button type="button" onclick="window.location.href='FISAP_setup'" class="btn waves-effect waves-light btn-info"><?=REPORT_SETUP?></button>
													<br />
													<? $res = $db->Execute("select * from S_FISAP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
													if($res->RecordCount() > 0){
														$EDITED_BY	= $res->fields['EDITED_BY'];
														$EDITED_ON	= $res->fields['EDITED_ON'];
														$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$EDITED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 

														$EDITED_BY	= $res_user->fields['NAME']; 
														echo "Edited: ".$EDITED_BY." ".date("m/d/Y",strtotime($EDITED_ON));
													} ?>
													
												</div>
											</div>
										</div>
									</div>
									
									
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
									
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		show_fields()
	});
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	
	function show_fields(){
		var val = document.getElementById('REPORT_TYPE').value
		
		document.getElementById('PDF_BUTTON').style.display 	= "inline";
		document.getElementById('EXCEL_BUTTON').style.display 	= "inline";
		
		if(val == 1 || val == 5 || val == 6)
			document.getElementById('PDF_BUTTON').style.display = "none";
	}
	
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>
</body>

</html>