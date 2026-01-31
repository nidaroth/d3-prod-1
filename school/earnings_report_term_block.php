<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$report_error="";

$res_type1 = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");

if($_GET['rt'] == 3){
	$_POST['REPORT_TYPE'] 					= 3;
	$_POST['FORMAT']						= $_GET['format'];
	$_POST['PK_CAMPUS']						= $_GET['camp_id'];
	$_POST['SELECTED_PK_STUDENT_MASTER']	= $_GET['sid'];
	$_POST['YEAR']							= $_GET['YEAR'];
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$res_campus  = $db->Execute("select PK_CAMPUS, CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$_POST[PK_CAMPUS]' ");
	$CAMPUS_CODE = $res_campus->fields['CAMPUS_CODE'];
	
	$MONTH = "";
	if($_POST['MONTH'] == 1)
		$MONTH = "JAN";
	else if($_POST['MONTH'] == 2)
		$MONTH = "FEB";
	else if($_POST['MONTH'] == 3)
		$MONTH = "MAR";
	else if($_POST['MONTH'] == 4)
		$MONTH = "APR";
	else if($_POST['MONTH'] == 5)
		$MONTH = "MAY";
	else if($_POST['MONTH'] == 6)
		$MONTH = "JUN";
	else if($_POST['MONTH'] == 7)
		$MONTH = "JUL";
	else if($_POST['MONTH'] == 8)
		$MONTH = "AUG";
	else if($_POST['MONTH'] == 9)
		$MONTH = "SEP";
	else if($_POST['MONTH'] == 10)
		$MONTH = "OCT";
	else if($_POST['MONTH'] == 11)
		$MONTH = "NOV";
	else if($_POST['MONTH'] == 12)
		$MONTH = "DEC";
	
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];
	
	if($_POST['REPORT_TYPE'] == 1){
		if($_POST['FORMAT'] == 1){
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
							<td width="50%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="30%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Error Report</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus: '.$CAMPUS_CODE.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Earning Year/Month: '.$_POST['YEAR']." ".$MONTH.'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
					
		
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >ACCT20014</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 25,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 9,
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			$txt 		= "";
			
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<th width="12%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Campus</i></b>
								</th>
								<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Program</i></b>
								</th>
								<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Program Description</i></b>
								</th>
								<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>First Term</i></b>
								</th>
								<th width="15%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Student</i></b>
								</th>
								<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Student ID</i></b>
								</th>
								<th width="13%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Term Block</i></b>
								</th>
								<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Term Block Error</i></b>
								</th>
								<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
									<b><i>Tuition Charged</i></b>
								</th>
							</tr>
						</thead>';	
			
			$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'ERROR REPORT PDF')");
			if(count($res->fields) == '0')
			{
				$report_error = "No data in the report for the selections made.";
			}
			else
			{
				while (!$res->EOF) 
				{
					$txt .= '<tr>
								<td >'.$res->fields['CAMPUS_CODE'].'</td>
								<td >'.$res->fields['PROGRAM'].'</td>
								<td >'.$res->fields['PROGRAM_DESCRIPTION'].'</td>
								<td >'.$res->fields['FIRST_TERM'].'</td>
								<td >'.$res->fields['STUDENT'].'</td>
								<td >'.$res->fields['STUDENT_ID'].'</td>
								<td >'.$res->fields['TERM_BLOCK'].'</td>
								<td >'.$res->fields['TERM_BLOCK_ERROR'].'</td>
								<td align="right" >$'.number_format($res->fields['TUITION_CHARGED'], 2).'</td>
							</tr>';

					$res->MoveNext();
				}
				$txt .= '</table>';
				
				//echo $txt;exit;
				$mpdf->WriteHTML($txt);
				$mpdf->Output("Error Report.pdf", 'D');
				exit;
			}
			
		} else if($_POST['FORMAT'] == 2){
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
			
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$file_name 		= 'Error Report.xlsx';
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;			
			$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'ERROR REPORT EXCEL')");
			if(count($res->fields) == '0')
			{
				$report_error = "No data in the report for the selections made.";
			}
			else
			{
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{

					$index = -1;
					foreach ($heading as $key) 
					{
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								$index++;
								$cell_no = $cell[$index].$line;
						        $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
						        $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
						        $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							    $objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{
								//print_r($res->fields['ID_SSN']);exit;
								// Get data column value and set data
								 $index++;
								 $cell_no = $cell[$index].$line;
								 $cellValue=$res->fields[$key];
								 $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
								 
							}	
					   }
					   else
					   {
							echo 'Skip header';
					   }
					}
					
					$line++;
					$res->MoveNext();
				}

				$objPHPExcel->getActiveSheet()->freezePane('A1');
				
				$objWriter->save($outputFileName);
				$objPHPExcel->disconnectWorksheets();
				header("location:".$outputFileName);
			}
			
		}
	} else if($_POST['REPORT_TYPE'] == 2){
		if($_POST['FORMAT'] == 1){
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
							<td width="50%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="30%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Monthly Earnings</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus: '.$CAMPUS_CODE.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Earning Year/Month: '.$_POST['YEAR']." ".$MONTH.'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
					
		
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >ACCT20014</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 25,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 9,
				'format' => [210, 296],
				'orientation' => 'L'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			$txt 		= "";
			$PROGRAM 	= "";

			$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS PDF')");
			
			if (count($res->fields) == '0') 
			{
				$report_error = "No data in the report for the selections made.";
			}
			else
			{

				$data=[];
				$terms=[];

				while (!$res->EOF) 
				{
					$data[$res->fields['TERM_BLOCK']][]=$res->fields;
					$terms[$res->fields['TERM_BLOCK']]=array('TERM_BLOCK'=>$res->fields['TERM_BLOCK'],'EARNINGS_TYPE'=>$res->fields['EARNINGS_TYPE'],'TERM_BLOCK_DESCRIPTION'=>$res->fields['TERM_BLOCK_DESCRIPTION']);
					$res->MoveNext();
				}
				// echo "<pre>";
				// print_r($data);exit;

				$FINAL_TOTAL_EARNED 	         = 0;
				$FINAL_TOTAL_UNEARNED_TUITION    = 0;
				$FINAL_TOTAL_TUITION_CHARGED 	 = 0;
				$TOTAL_EARNINGS_AMOUNT   = 0;
				$TEMP_TUITION_TOTAL = 0;
				
				$txt = '';
				foreach($terms as $key=>$val)
				{
					$txt .= '
					<style> 
						td{
							word-break:break-all !important; 
						}
						th{
							border-bottom:1px solid #000;border-top:1px solid #000; 
						}
						.th_nostyle{
							border : none !important;
						}
					</style>
					<table border="0" cellspacing="0" cellpadding="3" style="width: 100%;">
						<thead>
							<tr>
								<th class="th_nostyle" align="left" colspan="13" ><b style="font-size:20px"> '.$val['EARNINGS_TYPE'].' : '.str_replace('—','-',$val['TERM_BLOCK']).' - '.$val['TERM_BLOCK_DESCRIPTION'].' </b><br /></th>
							</tr>
							<tr>
								<th align="left" style="width:10% !important;" >
									<b><i>Program</i></b>
								</th>
								<th align="left" style="width:10% !important;" >
									<b><i>Student</i></b>
								</th>
								<th align="center" style="width:8% !important;">
									<b><i>Student ID</i></b>
								</th>
								<th align="left" style="width:5% !important;">
									<b><i>Status</i></b>
								</th>
								<th align="left" style="width:7% !important;">
									<b><i>First Term</i></b>
								</th>
								<th align="left" style="width:8% !important;">
									<b><i>End Date</i></b>
								</th>
								<th align="right" style="width:6% !important;">
									<b><i>Earnings Calculation</i></b>
								</th>
								<th align="right" style="width:5% !important;">
									<b><i>Daily Amount</i></b>
								</th>
								<th align="right" style="width:5% !important;">
									<b><i>Month Days</i></b>
								</th>
								<th align="right" style="width:7% !important;">
									<b><i>Current Earnings</i></b>
								</th>
								<th align="right" style="width:10% !important;">
									<b><i>Total Earnings</i></b>
								</th>
								<th align="right" style="width:11% !important;">
									<b><i>Unearned Tuition</i></b>
								</th>
								<th align="right" style="width:8% !important;">
									<b><i>Total Tuition</i></b>
								</th>
							</tr>
						</thead>
						<tbody>
					';

					$TOTAL_EARNED 	         = 0;
					$TOTAL_UNEARNED_TUITION  = 0;
					$TOTAL_TUITION_CHARGED 	 = 0;
					$TOTAL_EARNINGS_AMOUNT   = 0;
					$TEMP_TUITION_TOTAL = 0;

					foreach ($data[$val['TERM_BLOCK']] as $k => $results)
					{
						$ENROLLMENT_BEGIN_DATE = '';
						if($results['ENROLLMENT_BEGIN_DATE'] != '' && $results['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
						{
							$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($results['ENROLLMENT_BEGIN_DATE']));
						}
							
						$ENROLLMENT_END_DATE = '';
						if($results['ENROLLMENT_END_DATE'] != '' && $results['ENROLLMENT_END_DATE'] != '0000-00-00')
						{
							$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($results['ENROLLMENT_END_DATE']));
						}

						$TOTAL_EARNINGS_AMOUNT_1 	= $results['TOTAL_EARNED'] + $results['PREVIOUS_EARNINGS']; 
						
						$TEMP_TUITION_TOTAL 		= $results['TUITION_CHARGED'];
						if($TOTAL_EARNINGS_AMOUNT_1 >= $TEMP_TUITION_TOTAL)
						{
							$TOTAL_EARNINGS_AMOUNT = $TEMP_TUITION_TOTAL;
						}
						else{
							$TOTAL_EARNINGS_AMOUNT = $results['TOTAL_EARNED'];
						}

						$FINAL_UNEARNED_TUITION     = $results['TUITION_CHARGED'] - $TOTAL_EARNINGS_AMOUNT;
							
						$txt .= '<tr>
									<td align="left">'.$results['PROGRAM'].'</td>
									<td align="left">'.$results['STUDENT'].'</td>
									<td align="center">'.$results['STUDENT_ID'].'</td>
									<td align="left">'.$results['STUDENT_STATUS'].'</td>
									<td align="left">'.$ENROLLMENT_BEGIN_DATE.'</td>
									<td align="left">'.$ENROLLMENT_END_DATE.'</td>
									<td align="right">'.$results['CALCULATION'].'</td>
									<td align="right">$'.$results['DAILY_AMOUNT'].'</td>
									<td align="right">'.$results['MONTH_EARNING_DAYS'].'</td>
									<td align="right">$'.$results['CURRENT_EARNINGS'].'</td>
									<td align="right">$'.number_format($TOTAL_EARNINGS_AMOUNT, 2).'</td>
									<td align="right">$'.number_format($FINAL_UNEARNED_TUITION, 2).'</td>
									<td align="right">$'.number_format($results['TUITION_CHARGED'], 2).'</td>
								</tr>';
						$TOTAL_EARNED 	         += $TOTAL_EARNINGS_AMOUNT;
						$TOTAL_UNEARNED_TUITION  += $FINAL_UNEARNED_TUITION;
						$TOTAL_TUITION_CHARGED 	 += $results['TUITION_CHARGED'];
								
					}
					$txt .= '<tr>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ><b>Totals</b></td>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ></td>
								<td style="border-top:1px solid #000;" ></td>
								<td align="right" style="border-top:1px solid #000;" ><b>$'.number_format($TOTAL_EARNED, 2).'</b></td>
								<td align="right" style="border-top:1px solid #000;" ><b>$'.number_format($TOTAL_UNEARNED_TUITION, 2).'</b></td>
								<td align="right" style="border-top:1px solid #000;" ><b>$'.number_format($TOTAL_TUITION_CHARGED, 2).'</b></td>
							</tr>';

					$txt .= '</tbody></table>';

					$FINAL_TOTAL_EARNED 	         += $TOTAL_EARNED;
					$FINAL_TOTAL_UNEARNED_TUITION    += $TOTAL_UNEARNED_TUITION;
					$FINAL_TOTAL_TUITION_CHARGED 	 += $TOTAL_TUITION_CHARGED;

				}
				$txt .= '
						<style> 
							td{
								word-break:break-all !important; 
							}
							th{
								border-bottom:1px solid #000;border-top:1px solid #000; 
							}
							.th_nostyle{
								border : none !important;
							}
						</style>
						<table border="0" cellspacing="0" cellpadding="3" style="width: 100%;">
							<tr>
								<th class="th_nostyle" colspan="13" ><br /><br /></th>
							</tr>
							<tr>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:9% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:10% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:8% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:5% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:7% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:8% !important;" ><b>Grand Totals</b></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:6% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:5% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:5% !important;" ></td>
								<td style="border-bottom:1px solid #000;border-top:1px solid #000;width:7% !important;" ></td>
								<td align="right" style="border-bottom:1px solid #000;border-top:1px solid #000;width:10% !important;" ><b>$'.number_format($FINAL_TOTAL_EARNED, 2).'</b></td>
								<td align="right" style="border-bottom:1px solid #000;border-top:1px solid #000;width:11% !important;" ><b>$'.number_format($FINAL_TOTAL_UNEARNED_TUITION, 2).'</b></td>
								<td align="right" style="border-bottom:1px solid #000;border-top:1px solid #000;width:8% !important;" ><b>$'.number_format($FINAL_TOTAL_TUITION_CHARGED, 2).'</b></td>
							</tr>
						</table>
						';
				// echo $txt;exit;

				$header = '<table width="100%" >
									<tr>
										<td width="20%" valign="top" >'.$logo.'</td>
										<td width="50%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
										<td width="30%" valign="top" >
											<table width="100%" >
												<tr>
													<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Monthly Earnings</b></td>
												</tr>
												<tr>
													<td width="100%" align="right" style="font-size:13px;" >Campus: '.$CAMPUS_CODE.'</td>
												</tr>
												<tr>
													<td width="100%" align="right" style="font-size:13px;" >Earning Year/Month: '.$_POST['YEAR']." ".$MONTH.'</td>
												</tr>
											</table>
										</td>
									</tr>							
							</table>';

				$header_cont= '<!DOCTYPE HTML>
				<html>
				<head>
				<style>
				div{ padding-bottom:20px !important; }	
				</style>
				</head>
				<body>
				<div> '.$header.' </div>
				</body>
				</html>';

				$html_body_cont = '<!DOCTYPE HTML>
				<html>
				<head> <style>
				table{  margin-top: 2px; }
				table tr{  padding-top: 1px !important; }
				</style>
				</head>
				<body>'.$txt.'</body></html>';

				$date_footer = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE ,date_default_timezone_get());

				$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date_footer.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
							<td></td>							
						</tr>
					</table>';
				$footer_cont= '<!DOCTYPE HTML><html><head><style>
					tbody td{ font-size:14px !important; }
					</style></head><body>'.$footer.'</body></html>';

				$header_path = create_html_file('header.html', $header_cont, "invoice");
				$content_path = create_html_file('content.html', $txt, "invoice");
				$footer_path= create_html_file('footer.html',$footer_cont);

				$file_name = 'Monthly_Earnings_'.uniqid().'.pdf';
				$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 350 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

				$pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

				exec($pdfdata['exec'], $output, $retval);
				echo 'school/temp/invoice/' . $file_name;
				header('Content-Type: Content-Type: application/pdf');
				header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
				readfile($pdfdata['filepath']);
				exit;

				// $mpdf->WriteHTML($txt);
				// $mpdf->Output("Monthly Earnings.pdf", 'D');
				// return "Monthly Earnings.pdf";
			}
			
		} else if($_POST['FORMAT'] == 2){
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
			
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$file_name 		= 'Monthly Earnings.xlsx';
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;		
			// $res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS EXCEL')");

			// if (count($res->fields) == '0') 
			// {
			// 	$report_error = "No data in the report for the selections made.";
			// } 
			// else 
			// {
				
			// 	$heading = array_keys($res->fields);
			// 	while (!$res->EOF) 
			// 	{

			// 		$index = -1;
			// 		foreach ($heading as $key) 
			// 		{
			// 			//print_r($res->fields[$key]);
			// 			if($key!='ROW_TYPE')
			// 			{
			// 				//Get Header column name and set styling 
			// 				if($line==1)
			// 				{
			// 					$index++;
			// 					$cell_no = $cell[$index].$line;
			// 			        $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
			// 			        $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			// 			        $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
			// 				    $objPHPExcel->getActiveSheet()->freezePane('A1');
			// 				}
			// 				else
			// 				{

			// 					// Get data column value and set data
									
			// 					$index++;
			// 					$cell_no = $cell[$index].$line;
			// 					$cellValue=$res->fields[$key];
			// 					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
			// 					$objPHPExcel->getActiveSheet()->getStyle("S:S")->getNumberFormat()->setFormatCode('0.00');
			// 					$objPHPExcel->getActiveSheet()->getStyle("U:U")->getNumberFormat()->setFormatCode('0.00');
			// 					$objPHPExcel->getActiveSheet()->getStyle("V:V")->getNumberFormat()->setFormatCode('0.00');
			// 					$objPHPExcel->getActiveSheet()->getStyle("W:W")->getNumberFormat()->setFormatCode('0.00');	
			// 				}	
			// 		   }
			// 		   else
			// 		   {
			// 				echo 'Skip header';
			// 		   }
			// 		}
					
			// 		$line++;
			// 		$res->MoveNext();

			// 		/*$ENROLLMENT_BEGIN_DATE = '';
			// 		if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
			// 			$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
						
			// 		$ENROLLMENT_END_DATE = '';
			// 		if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
			// 				$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_END_DATE']));*/
							
			// 	}

			// 	$objPHPExcel->getActiveSheet()->freezePane('A1');
				
			// 	$objWriter->save($outputFileName);
			// 	$objPHPExcel->disconnectWorksheets();
			// 	header("location:".$outputFileName);
			// }

			// New Code
			$index 	= -1;

			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Earnings Type';
			$width[]   = 20;
			$heading[] = 'Earnings Year';
			$width[]   = 20;
			$heading[] = 'Earnings Month';
			$width[]   = 20;
			$heading[] = 'Term Block';
			$width[]   = 20;
			$heading[] = 'Term Block Description';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Session';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Student Group';
			$width[]   = 20;
			$heading[] = 'Earnings Calculation';
			$width[]   = 20;
			$heading[] = 'Calculation Date';
			$width[]   = 20;
			$heading[] = 'Finalized Date';
			$width[]   = 20;
			$heading[] = 'Daily Amount';
			$width[]   = 20;
			$heading[] = 'Month Days';
			$width[]   = 20;
			$heading[] = 'Current Earnings';
			$width[]   = 20;
			$heading[] = 'Total Earnings';
			$width[]   = 20;
			$heading[] = 'Unearned Tuition';
			$width[]   = 20;
			$heading[] = 'Total Tuition';
			$width[]   = 20;
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	
			
			$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS EXCEL NEW')");
			if (count($res->fields) == '0') 
			{
				$report_error = "No data in the report for the selections made.";
			}
			else
			{

				$data=[];
				$terms=[];

				while (!$res->EOF) 
				{
					$data[$res->fields['TERM_BLOCK']][]=$res->fields;
					$terms[$res->fields['TERM_BLOCK']]=array('TERM_BLOCK'=>$res->fields['TERM_BLOCK'],'EARNINGS_TYPE'=>$res->fields['EARNINGS_TYPE'],'TERM_BLOCK_DESCRIPTION'=>$res->fields['TERM_BLOCK_DESCRIPTION']);
					$res->MoveNext();
				}
				// echo "<pre>";
				// print_r($data);exit;

				foreach($terms as $key=>$val)
				{

					$TOTAL_EARNINGS_AMOUNT   = 0;
					$TEMP_TUITION_TOTAL = 0;

					foreach ($data[$val['TERM_BLOCK']] as $k => $results)
					{
						$line++;
						$index = -1;
			
						$ENROLLMENT_BEGIN_DATE = '';
						if($results['ENROLLMENT_BEGIN_DATE'] != '' && $results['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
						{
							$ENROLLMENT_BEGIN_DATE = date("Y-m-d", strtotime($results['ENROLLMENT_BEGIN_DATE']));
						}
							
						$ENROLLMENT_END_DATE = '';
						if($results['ENROLLMENT_END_DATE'] != '' && $results['ENROLLMENT_END_DATE'] != '0000-00-00')
						{
							$ENROLLMENT_END_DATE = date("Y-m-d", strtotime($results['ENROLLMENT_END_DATE']));
						}

						$TOTAL_EARNINGS_AMOUNT_1 	= $results['TOTAL_EARNED'] + $results['PREVIOUS_EARNINGS']; 
						
						$TEMP_TUITION_TOTAL 		= $results['TUITION_CHARGED'];
						if($TOTAL_EARNINGS_AMOUNT_1 >= $TEMP_TUITION_TOTAL)
						{
							$TOTAL_EARNINGS_AMOUNT = $TEMP_TUITION_TOTAL;
						}
						else{
							$TOTAL_EARNINGS_AMOUNT = $results['TOTAL_EARNED'];
						}

						$FINAL_UNEARNED_TUITION     = $results['TUITION_CHARGED'] - $TOTAL_EARNINGS_AMOUNT;

							
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['CAMPUS_CODE']);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['EARNINGS_TYPE']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['EARNINGS_YEAR']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['EARNINGS_MONTH']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['TERM_BLOCK']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['TERM_BLOCK_DESCRIPTION']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['PROGRAM']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['PROGRAM_DESCRIPTION']);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_BEGIN_DATE);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_END_DATE);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['STUDENT']);
					
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['STUDENT_ID']);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['SESSION']);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['STUDENT_STATUS']);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['PROGRAM_GROUP']);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['CALCULATION']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['CALCULATION_DATE']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['FINALIZED_DATE']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['DAILY_AMOUNT']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['MONTH_EARNING_DAYS']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['CURRENT_EARNINGS']);
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TOTAL_EARNINGS_AMOUNT);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FINAL_UNEARNED_TUITION);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($results['TUITION_CHARGED']);

						$objPHPExcel->getActiveSheet()->getStyle("S:S")->getNumberFormat()->setFormatCode('0.00');
						$objPHPExcel->getActiveSheet()->getStyle("U:U")->getNumberFormat()->setFormatCode('0.00');
						$objPHPExcel->getActiveSheet()->getStyle("V:V")->getNumberFormat()->setFormatCode('0.00');
						$objPHPExcel->getActiveSheet()->getStyle("W:W")->getNumberFormat()->setFormatCode('0.00');	
						$objPHPExcel->getActiveSheet()->getStyle("X:X")->getNumberFormat()->setFormatCode('0.00');	
					}
					
				}

				$objPHPExcel->getActiveSheet()->freezePane('A1');
				
				$objWriter->save($outputFileName);
				$objPHPExcel->disconnectWorksheets();
				header("location:".$outputFileName);

			}
		}

	} else if($_POST['REPORT_TYPE'] == 3){
		if($_POST['FORMAT'] == 1){
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';

			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of [pagetotal]</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 30,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 9,
				'format' => [210, 296],
				'orientation' => 'L'
			]);
			$mpdf->autoPageBreak = true;
			
			$txt = "";
			$new_tx = "";
			$PK_STUDENT_MASTER_ARR = explode(",", $_POST['SELECTED_PK_STUDENT_MASTER']);
			foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) 
			{

				$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME , STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, CAMPUS_CODE, STUDENT_ID 
				FROM 
				S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
				LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
				LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
				LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
				LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
				LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
				WHERE 
				S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				$NAME 		= $res->fields['NAME'];
				$STUDENT_ID = $res->fields['STUDENT_ID'];
				$ENROLLMENT = $res->fields['CAMPUS_CODE'].' - '.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'];
				


				$mpdf->SetHTMLHeader($header);
				$mpdf->SetHTMLFooter($footer);
				//$mpdf->AddPage();

				$mpdf->AddPage('','',1);
				$mpdf->AliasNbPageGroups('[pagetotal]');		
			

				//$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", 0,0 , ".$PK_STUDENT_MASTER.",'STUDENT EARNINGS')");	

				$sQuery = "SELECT E.PROGRAM
							,E.PROGRAM_DESCRIPTION
							,E.PK_STUDENT_MASTER    
							,E.PK_STUDENT_ENROLLMENT
							,E.STUDENT
							,E.STUDENT_ID
							,E.CAMPUS_CODE
							,E.EARNINGS_YEAR
							,E.EARNINGS_MONTH
							,E.EARNINGS_TYPE
							,E.MONTH_EARNING_DAYS
							,E.DAYS_IN_MONTH
							,CONCAT(E.EARNINGS_YEAR, '  ', UPPER(DATE_FORMAT(CONCAT_WS('-', E.EARNINGS_YEAR, E.EARNINGS_MONTH, 1), '%b'))) AS EARNINGS_YEAR_MONTH    
							,E.CALCULATION
							,E.CALCULATION_DATE    
							,E.FINALIZED_DATE    
							,E.ENROLLMENT_BEGIN_DATE
							,E.EXPECTED_GRAD_DATE
							,CASE WHEN E.ENROLLMENT_END_DATE = '2222-02-02' THEN '' ELSE E.ENROLLMENT_END_DATE END AS ENROLLMENT_END_DATE 
							,CONCAT(IFNULL(STB.BEGIN_DATE,''),' - ',IFNULL(STB.END_DATE,'')) AS TERM_BLOCK
							,CASE WHEN STB.DESCRIPTION is NULL THEN 'N0T SET' ELSE STB.DESCRIPTION END AS TERM_BLOCK_DESCRIPTION   
							,E.STUDENT_STATUS
							,E.DAILY_EARNING_RATE AS DAILY_AMOUNT
							,E.TUITION_CHARGED
							,E.PREVIOUS_EARNINGS
							,E.MONTH_EARNINGS_AMOUNT AS CURRENT_EARNINGS
							,E.EARNINGS_AMOUNT AS TOTAL_EARNED
							,E.TUITION_CHARGED-(E.PREVIOUS_EARNINGS+E.EARNINGS_AMOUNT) AS UNEARNED_TUITION
							FROM S_STUDENT_EARNINGS_TERM_BLOCK AS E
							LEFT JOIN S_TERM_BLOCK AS STB ON E.PK_TERM_BLOCK = STB.PK_TERM_BLOCK
							WHERE E.PK_ACCOUNT = ".$_SESSION['PK_ACCOUNT']."
							AND E.PK_TERM_BLOCK <> 0
							AND E.PK_CAMPUS = ".$_POST['PK_CAMPUS']."    
							AND FIND_IN_SET(E.PK_STUDENT_MASTER, ".$PK_STUDENT_MASTER.")
							GROUP BY E.EARNINGS_YEAR,E.EARNINGS_MONTH,TERM_BLOCK_DESCRIPTION
							ORDER BY TERM_BLOCK_DESCRIPTION, E.EARNINGS_YEAR, E.EARNINGS_MONTH ASC";
				//echo $sQuery;
				$res = $db->Execute($sQuery);
				//echo count($res);exit;
				if (count($res->fields) == '0') 
				{
					$report_error = "No data in the report for the selections made.";
				}
				else
				{

					$txt .= '<div style="page-break-before: always;"></div>';
					$txt .= '<table width="100%" >
							<tr>
								<td>
									<table width="100%" >
										<tr>
											<td width="100%" align="right" ><b>'.$NAME.'</b></td>
										</tr>
										<tr>
											<td width="100%" align="right" ><b>Student ID: '.$STUDENT_ID.'</b></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td width="100%" align="right" ><b>Current Enrollment: '.$ENROLLMENT.'</b></td>
							</tr>
						</table>';

					$ENROLLMENT_BEGIN_DATE = '';
					if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
					{
						$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
					}
					$EXPECTED_GRAD_DATE = '';
					if($res->fields['EXPECTED_GRAD_DATE'] != '' && $res->fields['EXPECTED_GRAD_DATE'] != '0000-00-00')
					{
						$EXPECTED_GRAD_DATE = date("m/d/Y", strtotime($res->fields['EXPECTED_GRAD_DATE']));
					}
					$txt .= '
							<hr>
							<table border="0" cellspacing="0" cellpadding="5" >
								<tr>
									<td colspan="4"><b>Program :</b> '.$res->fields['PROGRAM'].' - '.$res->fields['PROGRAM_DESCRIPTION'].'</td>
								</tr>
								<tr>
									<td style="padding-right:45px;"><b>Campus:</b> '.$res->fields['CAMPUS_CODE'].'</td>
									<td style="padding-right:45px;"><b>Status:</b> '.$res->fields['STUDENT_STATUS'].'</td>
									<td style="padding-right:45px;"><b>First Term:</b> '.$ENROLLMENT_BEGIN_DATE.'</td>
									<td style="padding-right:45px;"><b>Expected Grad:</b> '.$EXPECTED_GRAD_DATE.'</td>
								</tr>
								
							</table>
							<hr>
					';

					$STUD_TUITION 			= 0;
					$STUD_PREVIOUS_EARNING 	= 0;
					$STUD_EARNINGS_AMOUNT 	= 0;
					$STUD_UNEARNED_AMOUNT 	= 0;
					$TOTAL_EARNINGS_AMOUNT  = 0;
					
					$data=[];
					$terms=[];

					while (!$res->EOF) 
					{
						$data[$res->fields['TERM_BLOCK']][]=$res->fields;
						$terms[$res->fields['TERM_BLOCK']]=array('TERM_BLOCK'=>$res->fields['TERM_BLOCK'],'EARNINGS_TYPE'=>$res->fields['EARNINGS_TYPE'],'TERM_BLOCK_DESCRIPTION'=>$res->fields['TERM_BLOCK_DESCRIPTION']);
						$res->MoveNext();
					}
					// echo "<pre>";
					// print_r($data);exit;

					// TERM BLOCK DATE AND DESC
					foreach($terms as $key=>$val) 
					{
						$txt .= '
								<b>'.$val['EARNINGS_TYPE'].' :</b> '.$val['TERM_BLOCK'].' '.$val['TERM_BLOCK_DESCRIPTION'].'
								<style>
									
									.table_row {margin-bottom : 15px;margin-top : 16px;}
								</style>
								<table border="1" class="table_row" cellspacing="0" cellpadding="3" width="100%">
									<thead >
											<tr class="table_row">
												<th width="9%" align="left" >
													<b><i>Earnings Year/Month</i></b>
												</th>
												<th width="10%" align="left" >
													<b><i>Calculation Date</i></b>
												</th>
												<th width="9%" align="left" >
													<b><i>Calculation Status</i></b>
												</th>
												<th width="8%" align="left" >
													<b><i>Finalized Date</i></b>
												</th>
												<th width="8%" align="right" >
													<b><i>Daily Amount</i></b>
												</th>
												<th width="7%" align="right" >
													<b><i>Month Days</i></b>
												</th>
												<th width="12%" align="right" >
													<b><i>Current Earnings</i></b>
												</th>
												<th width="12%" align="right" >
													<b><i>Total Earnings</i></b>
												</th>
												<th width="13%" align="right" >
													<b><i>Unearned Amount</i></b>
												</th>
												<th width="12%" align="right" >
													<b><i>Total Tuition</i></b>
												</th>										
											</tr>
									</thead>
									<tbody>
									';

									$PROG_TUITION 			= 0;
									$PROG_PREVIOUS_EARNING 	= 0;
									$TOTAL_EARNINGS_AMOUNT  = 0;
									$PROG_EARNINGS_AMOUNT 	= 0;
									$PROG_UNEARNED_AMOUNT 	= 0;
									$STUD_EARNINGS_AMOUNT   = 0;	

									foreach ($data[$val['TERM_BLOCK']] as $k => $results)
									{

										$ENROLLMENT_END_DATE = '';
										if($results['ENROLLMENT_END_DATE'] != '' && $results['ENROLLMENT_END_DATE'] != '0000-00-00')
										{
											$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($results['ENROLLMENT_END_DATE']));
										}
											
										$CALCULATION_DATE = '';
										if($results['CALCULATION_DATE'] != '' && $results['CALCULATION_DATE'] != '0000-00-00')
										{
											$CALCULATION_DATE = date("m/d/Y", strtotime($results['CALCULATION_DATE']));
										}
											
										$FINALIZED_DATE = '';
										if($results['FINALIZED_DATE'] != '' && $results['FINALIZED_DATE'] != '0000-00-00')
										{
											$FINALIZED_DATE = date("m/d/Y", strtotime($results['FINALIZED_DATE']));
										}
										$TOTAL_EARNINGS_AMOUNT 	+= $results['CURRENT_EARNINGS'];   
										$FINAL_UNEARNED_TUITION = $results['TUITION_CHARGED'] - $TOTAL_EARNINGS_AMOUNT;
										if($FINAL_UNEARNED_TUITION < 0)
										{
											$FINAL_UNEARNED_TUITION = '0.00';
										}

										$TEMP_TUITION_TOTAL 	= $results['TUITION_CHARGED'];
										if($TOTAL_EARNINGS_AMOUNT >= $TEMP_TUITION_TOTAL)
										{
											$TOTAL_EARNINGS_AMOUNT = $TEMP_TUITION_TOTAL;
										}

										$sMONTH_EARNING_DAYS = $results['MONTH_EARNING_DAYS'];
										$sCURRENT_EARNINGS = $results['CURRENT_EARNINGS'];
										// if($sMONTH_EARNING_DAYS != 0 && $sCURRENT_EARNINGS != 0.00)
										// {
										
											$txt .= '<tr>
															<td align="center">'.$results['EARNINGS_YEAR_MONTH'].'</td>
															<td align="center">'.$CALCULATION_DATE.'</td>
															<td align="center">'.$results['CALCULATION'].'</td>
															<td align="center">'.$FINALIZED_DATE.'</td>
															<td align="right">$'.number_format($results['DAILY_AMOUNT'], 2).'</td>
															<td align="right">'.$results['MONTH_EARNING_DAYS'].'</td>
															<td align="right" >$'.number_format($results['CURRENT_EARNINGS'], 2).'</td>
															<td align="right" >$'.number_format($TOTAL_EARNINGS_AMOUNT, 2).'</td>
															<td align="right" >$'.number_format($FINAL_UNEARNED_TUITION, 2).'</td>
															<td align="right" >$'.number_format($results['TUITION_CHARGED'], 2).'</td>
														</tr>';
										// }
										

										$PROG_TUITION 			+= $results['TUITION_CHARGED'];
										$PROG_PREVIOUS_EARNING 	+= $results['PREVIOUS_EARNINGS'];
										$PROG_EARNINGS_AMOUNT 	+= $results['CURRENT_EARNINGS'];
										$PROG_UNEARNED_AMOUNT 	+= $results['UNEARNED_TUITION'];
										
										$STUD_TUITION 			+= $results['TUITION_CHARGED'];
										$STUD_PREVIOUS_EARNING 	+= $results['PREVIOUS_EARNINGS'];
										$STUD_EARNINGS_AMOUNT 	+= $results['CURRENT_EARNINGS'];
										$STUD_UNEARNED_AMOUNT 	+= $results['UNEARNED_TUITION'];

										if($STUD_EARNINGS_AMOUNT >= $TEMP_TUITION_TOTAL)
										{
											$STUD_EARNINGS_AMOUNT = $TEMP_TUITION_TOTAL;
										}

									}

									$txt .= '<tr>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td align="center" colspan="2"><b>Term Block Totals:</b></td>
													<td align="right" ><b>$'.number_format($STUD_EARNINGS_AMOUNT, 2).'</b></td>
													<td></td>
													<td></td>
													<td></td>
												</tr>';

						$txt .= "</tbody></table>";
						
					}
					// TERM BLOCK DATE AND DESC
					$new_tx .= $txt;

				} // Else End Error Reporting
				
			}
			
			//echo $new_tx;exit;
			//echo count($res);exit;
			if ($new_tx != '') 
			{
				$header = '<table width="100%" >
								<tr>
									<td width="20%" valign="top" >'.$logo.'</td>
									<td width="40%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
									<td width="40%" valign="top" >
										<table width="100%" >
											<tr>
												<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Student Earnings</b></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>';

				$header_cont= '<!DOCTYPE HTML>
				<html>
				<head>

				</head>
				<body>
				<div> '.$header.' </div>
				</body>
				</html>';

				$html_body_cont = '<!DOCTYPE HTML>
				<html>
				<head> <style>
				table{  margin-top: 2px; }
				table tr{  padding-top: 1px !important; }
				</style>
				</head>
				<body>'.$txt.'</body></html>';

				$date_footer = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE ,date_default_timezone_get());

				$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date_footer.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
							<td></td>							
						</tr>
					</table>';
				$footer_cont= '<!DOCTYPE HTML><html><head><style>
					tbody td{ font-size:14px !important; }
					</style></head><body>'.$footer.'</body></html>';

				$header_path = create_html_file('header_block_term.html', $header_cont, "invoice");
				$content_path = create_html_file('content_block_term.html', $txt, "invoice");
				$footer_path= create_html_file('footer_block_term.html',$footer_cont);

				$file_name = 'Student_Earnings_'.uniqid().'.pdf';
				$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 300 --page-height 210 --margin-top 25mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';

				$pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);

				exec($pdfdata['exec'], $output, $retval);
				echo 'school/temp/invoice/' . $file_name;
				header('Content-Type: Content-Type: application/pdf');
				header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
				readfile($pdfdata['filepath']);
				exit;

				// $file_name = 'Student_Earnings_'.uniqid().'.pdf';
				// $mpdf->Output($file_name, 'D');
				// return $file_name;
			 }

		} else if($_POST['FORMAT'] == 2){ // - Not In Used
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
			
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$file_name 		= 'Student Earnings.xlsx';
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;	
			$index 	= -1;

			$heading[] = 'Earnings Year/Month';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'Tuition Charged';
			$width[]   = 20;
			$heading[] = 'Previous Earnings';
			$width[]   = 20;
			$heading[] = 'Calculation Status/Type';
			$width[]   = 20;
			$heading[] = 'Calculation Status/Type';
			$width[]   = 20;
			$heading[] = 'Calculation Date';
			$width[]   = 20;
			$heading[] = 'Finalized Date';
			$width[]   = 20;
			$heading[] = 'Earnings Amount';
			$width[]   = 20;
			$heading[] = 'Unearned Amount';
			$width[]   = 20;
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	
			
			$PK_STUDENT_MASTER_ARR = explode(",", $_POST['SELECTED_PK_STUDENT_MASTER']);
			foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
				$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", 0,0 , ".$PK_STUDENT_MASTER.",'STUDENT EARNINGS')");
				while (!$res->EOF) {
					$line++;
					$index = -1;
		
					$ENROLLMENT_BEGIN_DATE = '';
					if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
						$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
						
					$ENROLLMENT_END_DATE = '';
					if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
						$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_END_DATE']));
						
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EARNINGS_YEAR_MONTH']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_BEGIN_DATE);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_END_DATE);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TUITION_CHARGED']);
				
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PREVIOUS_EARNINGS']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CALCULATION']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRORATED_REASON']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EARNINGS_AMOUNT']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UNEARNED_TUITION']);
					
					$res->MoveNext();
				}
				
				// $db->close();
				// $db->connect('localhost','root',$db_pass,$db_name);
			}

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
		
	} else if($_POST['REPORT_TYPE'] == 4){
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
		
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Yearly Earnings.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;

		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Earnings Type';
		$width[]   = 20;
		$heading[] = 'Earnings Year';
		$width[]   = 20;
		$heading[] = 'Term Block';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Program Description';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Session';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'End Date';
		$width[]   = 20;
		$heading[] = 'End Date Type';
		$width[]   = 20;
		$heading[] = 'Student Group';
		$width[]   = 20;
		$heading[] = 'Tuition Charged';
		$width[]   = 20;
		$heading[] = 'Previous Earnings';
		$width[]   = 20;
		$heading[] = 'Unearned Prior Year';
		$width[]   = 20;
		$heading[] = 'JAN';
		$width[]   = 20;
		$heading[] = 'FEB';
		$width[]   = 20;
		$heading[] = 'MAR';
		$width[]   = 20;
		$heading[] = 'APR';
		$width[]   = 20;
		$heading[] = 'MAY';
		$width[]   = 20;
		$heading[] = 'JUN';
		$width[]   = 20;
		$heading[] = 'JUL';
		$width[]   = 20;
		$heading[] = 'AUG';
		$width[]   = 20;
		$heading[] = 'SEP';
		$width[]   = 20;
		$heading[] = 'OCT';
		$width[]   = 20;
		$heading[] = 'NOV';
		$width[]   = 20;
		$heading[] = 'DEC';
		$width[]   = 20;
		$heading[] = 'Year Total';
		$width[]   = 20;
		$heading[] = 'Unearned Year End';
		$width[]   = 20;
					
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}	
		
		
		$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].",0 , 0,'YEARLY EARNINGS')");
		while (!$res->EOF) {
			$line++;
			$index = -1;

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EARNINGS_TYPE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EARNINGS_YEAR']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TERM_BLOCK']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);

			$ENROLLMENT_BEGIN_DATE = '';
			if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
				$ENROLLMENT_BEGIN_DATE = date("Y-m-d", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_BEGIN_DATE);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);

			if ($res->fields['SESSION'] != '') 
			{
				$option_label = substr($res->fields['SESSION'],0,1).' - '.$res->fields['SESSION'];
			}
			else{
				$option_label = '';
			}
						
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($option_label);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CURRENT_STATUS']);
						
			$ENROLLMENT_END_DATE = '';
			if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
				$ENROLLMENT_END_DATE = date("Y-m-d", strtotime($res->fields['ENROLLMENT_END_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_END_DATE);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['END_DATE_TYPE']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_GROUP']);
						
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TUITION_CHARGED']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PREVIOUS_EARNINGS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UNEARNED_PRIOR_YEAR']);
			
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['YEAR_TOTAL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UNEARNED_YEAR_END']);
			
			$res->MoveNext();
		}

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	} else if($_POST['REPORT_TYPE'] == 5){
		if($_POST['FORMAT'] == 1){
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
							<td width="35%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="45%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Monthly Earnings - Received Comparison</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus: '.$CAMPUS_CODE.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Earning Year/Month: '.$_POST['YEAR']." ".$MONTH.'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
					
		
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" >ACCT20014</td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 25,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 9,
				'format' => [210, 296],
				'orientation' => 'L'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			$txt 		= "";
			$PROGRAM 	= "";
			//echo "CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS COMPARISON')";exit;			
			$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS COMPARISON')");

			if (count($res->fields) == '0') 
			{
				$report_error = "No data in the report for the selections made.";
			}
			else
			{

				$s_no = 0;
				while (!$res->EOF) {
					if($res->fields['RECORD_TYPE'] != 'REPORT TOTAL') {
						if($PROGRAM != $res->fields['PROGRAM']) {
							$s_no = 0;
							
							if($txt != '')
								$txt .= '</table>';
								
							$PROGRAM = $res->fields['PROGRAM'];
							$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<thead>
										<tr>
											<th colspan="11" align="left" ><b style="font-size:20px">'.$res->fields['EARNINGS_TYPE'].' : '.$res->fields['TERM_BLOCK'].' — '.$res->fields['TERM_BLOCK_DESCRIPTION'].' </b><br /></th>
										</tr>
										<tr>
											<th width="14%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Program</i></b>
											</th>
											<th width="14%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Student</i></b>
											</th>
											<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Student ID</i></b>
											</th>
											<th width="12%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Status</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Current Earnings</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Total Earnings</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Unearned Tuition</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Total<br />Tuition</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Non-Tuition Charges</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Total Received</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Received Positive</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Not Earned Negative</i></b>
											</th>
										</tr>
									</thead>';
						}
				
						if($res->fields['RECORD_TYPE'] == 'DETAIL') {
							$s_no++;
							$txt .= '<tr>
										<td >'.$res->fields['PROGRAM'].'</td>
										<td >'.$s_no.'. '.$res->fields['STUDENT'].'</td>
										<td >'.$res->fields['STUDENT_ID'].'</td>
										<td >'.$res->fields['STUDENT_STATUS'].'</td>
										<td align="right" >$'.number_format($res->fields['CURRENT_EARNINGS'], 2).'</td>
										<td align="right" >$'.number_format($res->fields['TOTAL_EARNINGS'], 2).'</td>
										<td align="right" >$'.number_format($res->fields['UNEARNED_TUITION'], 2).'</td>
										<td align="right" >$'.number_format($res->fields['TOTAL_TUITION'], 2).'</td>
										<td align="right" >$'.number_format($res->fields['NON_TUITION_CHARGED'], 2).'</td>
										<td align="right" >$'.number_format($res->fields['TOTAL_RECEIVED'], 2).'</td>
										<td align="right" >$'.number_format($res->fields['RECEIVED_NOT_EARNED_POSITIVE'], 2).'</td>
										<td align="right" >$'.number_format($res->fields['RECEIVED_NOT_EARNED_NEGATIVE'], 2).'</td>
									</tr>';
						} else if($res->fields['RECORD_TYPE'] == 'PROGRAM TOTAL') {
							$txt .= '<tr>
										<td style="border-top:1px solid #000;" colspan="3" ></td>
										<td style="border-top:1px solid #000;" ><b>Program Totals</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['CURRENT_EARNINGS'], 2).'</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TOTAL_EARNINGS'], 2).'</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['UNEARNED_TUITION'], 2).'</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TOTAL_TUITION'], 2).'</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['NON_TUITION_CHARGED'], 2).'</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TOTAL_RECEIVED'], 2).'</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['RECEIVED_NOT_EARNED_POSITIVE'], 2).'</b></td>
										<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['RECEIVED_NOT_EARNED_NEGATIVE'], 2).'</b></td>
									</tr>
									<tr>
										<td style="border-top:1px solid #000;" colspan="10" align="right" >RECEIVED NOT EARNED BALANCE</td>
										<td style="border-top:1px solid #000;" colspan="2" align="right" ><b>$'.number_format(($res->fields['RECEIVED_NOT_EARNED_POSITIVE'] - $res->fields['RECEIVED_NOT_EARNED_NEGATIVE']), 2).'</b></td>
									</tr>';
						}
					}
							
					$res->MoveNext();
				}
				
				if($res->fields['RECORD_TYPE'] == 'REPORT TOTAL') {
					$txt .= '<tr>
								<th colspan="12" align="left" ><br /><br /></th>
							</tr>
							<tr>
								<td style="border-top:1px solid #000;" colspan="3" ></td>
								<td style="border-top:1px solid #000;" ><b>Report Totals</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['CURRENT_EARNINGS'], 2).'</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TOTAL_EARNINGS'], 2).'</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['UNEARNED_TUITION'], 2).'</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TOTAL_TUITION'], 2).'</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['NON_TUITION_CHARGED'], 2).'</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TOTAL_RECEIVED'], 2).'</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['RECEIVED_NOT_EARNED_POSITIVE'], 2).'</b></td>
								<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['RECEIVED_NOT_EARNED_NEGATIVE'], 2).'</b></td>
							</tr>';
				}
				
				if($txt != '')
					$txt .= '</table>';
					
				//echo $txt;exit;
				
				$mpdf->WriteHTML($txt);
				$mpdf->Output("Monthly Earnings - Received Comparison.pdf", 'D');
				exit;
			}
			
		} else if($_POST['FORMAT'] == 2){
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
			
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$file_name 		= 'Monthly Earnings - Received Comparison.xlsx';
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;			
			$res = $db->Execute("CALL ACCT20014(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS COMPARISON EXCEL')");
			if (count($res->fields) == '0') 
			{
				$report_error = "No data in the report for the selections made.";
			} 
			else 
			{
				
				$heading = array_keys($res->fields);
				while (!$res->EOF) 
				{

					$index = -1;
					foreach ($heading as $key) 
					{
						//print_r($res->fields[$key]);
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								$index++;
								$cell_no = $cell[$index].$line;
						        $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
						        $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
						        $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							    $objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{

								// Get data column value and set data
								 $index++;
								 $cell_no = $cell[$index].$line;
								 $cellValue=$res->fields[$key];
								 $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
								
							}	
					   }
					   else
					   {
							echo 'Skip header';
					   }
					}
					
					$line++;
					$res->MoveNext();
							
				}

				$objPHPExcel->getActiveSheet()->freezePane('A1');
				
				$objWriter->save($outputFileName);
				$objPHPExcel->disconnectWorksheets();
				header("location:".$outputFileName);
			}
			
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
	<title><?=MNU_EARNINGS_REPORTS_TERM_BLOCK ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; } 
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
                        <h4 class="text-themecolor"><?=MNU_EARNINGS_REPORTS_TERM_BLOCK ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-12 ">
											<div class="row">
												<div class="col-3 col-sm-3">
													<div class="form-group m-b-40">
														<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control required-entry" onchange="show_fields()" >
															<!-- <option value="1" >Error Report</option> -->
															<option value="2" >Monthly Earnings</option>
															<!-- <option value="5" >Monthly Earnings - Received Comparison</option> -->
															<option value="3" >Student Earnings</option>
															<!-- <option value="4" >Yearly Earnings</option> -->
														</select>
														
														<span class="bar"></span> 
														<label for="REPORT_TYPE"><?=REPORT_TYPE?></label>
													</div>
												</div>
												
												<div class="col-2 col-sm-2" id="PK_CAMPUS_DIV" >
													<div class="form-group m-b-40">
														<select id="PK_CAMPUS" name="PK_CAMPUS"  class="form-control required-entry" >
															<option ></option>
															<? 
															while (!$res_type1->EOF) { 
																if($res_type1->RecordCount() == 1)
																	$selected = 'selected'; ?>
																<option value="<?=$res_type1->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type1->fields['CAMPUS_CODE'] ?></option>
															<?	$res_type1->MoveNext();
															} ?>
														</select>
														
														<span class="bar"></span> 
														<label for="PK_CAMPUS"><?=CAMPUS?></label>
													</div>
												</div>
												
												<div class="col-1 col-sm-1" id="MONTH_DIV" >
													<div class="form-group m-b-40">
														<select id="MONTH" name="MONTH"  class="form-control required-entry" >
															<option value=""></option>
															<option value="1" >Jan</option>
															<option value="2" >Feb</option>
															<option value="3" >Mar</option>
															<option value="4" >Apr</option>
															<option value="5" >May</option>
															<option value="6" >Jun</option>
															<option value="7" >Jul</option>
															<option value="8" >Aug</option>
															<option value="9" >Sep</option>
															<option value="10" >Oct</option>
															<option value="11" >Nov</option>
															<option value="12" >Dec</option>
														</select>
														<span class="bar"></span> 
														<label for="MONTH"><?=MONTH?></label>
													</div>
												</div>
												
												<div class="col-1 col-sm-1" id="YEAR_DIV" >
													<div class="form-group m-b-40">
														<select id="YEAR" name="YEAR"  class="form-control required-entry" >
															<option value=""></option>
															<? for($i = date("Y") ; $i >= 2010 ; $i--){ ?>
															<option value="<?=$i?>" ><?=$i?></option>
															<? } ?>
														</select>
														<span class="bar"></span> 
														<label for="YEAR"><?=YEAR?></label>
													</div>
												</div>
												
												<div class="col-3 col-sm-3 ">
													<button  type="button" onclick="submit_form(1)" id="PDF_BTN" class="btn waves-effect waves-light btn-info"><?=PDF ?></button>
													<button  type="button" onclick="submit_form(2)" id="EXCEL_BTN" class="btn waves-effect waves-light btn-info"><?=EXCEL ?></button>
													<input type="hidden" name="FORMAT" id="FORMAT" >
												</div>
												
												<div class="col-2 col-sm-2 ">
													<button  type="button" onclick="window.location.href='earnings_calculation_term_block'" class="btn waves-effect waves-light btn-info"><?=RETURN_TO_CALCULATION ?></button>
												</div>
											</div>
											
											<div id="stud_search_filter_div">
												<hr style="border-top: 1px solid #ccc;" />
												
												<div class="row form-group">
											
													<div class="col-md-2 ">
														<?=FIRST_TERM ?>
														<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
															<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
													
													<div class="col-md-2 ">
														<?=PROGRAM ?>
														<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
															<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
													
													<div class="col-md-2">
														<?=STATUS?>
														<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
															<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
													
													<div class="col-md-2 ">
														<?=STUDENT_GROUP ?>
														<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
															<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
													
													<div class="col-md-2 ">
														<br />
														<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
													</div>
												</div>
												<div id="student_div" >
												</div>
											</div>
											
										</div>	
									</div>
								</div>
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>

        <?php if($report_error!="") {?>
		<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Warning</h4>
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

		var error= '<?php echo  $report_error; ?>';
		jQuery(document).ready(function($) {
		   if(error!=""){
			jQuery('#errorModal').modal();
		   }
		});

		jQuery(document).ready(function($) {
			show_fields()
		});
		
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
		
		function show_fields(){
			document.getElementById('PK_CAMPUS_DIV').style.display 				= 'none';
			document.getElementById('MONTH_DIV').style.display 					= 'none';
			document.getElementById('YEAR_DIV').style.display 					= 'none';
			document.getElementById('PDF_BTN').style.display 					= 'none';
			document.getElementById('EXCEL_BTN').style.display 					= 'none';
			document.getElementById('stud_search_filter_div').style.display 	= 'none';
			
			if(document.getElementById('REPORT_TYPE').value == 1 || document.getElementById('REPORT_TYPE').value == 2 || document.getElementById('REPORT_TYPE').value == 5) {
				document.getElementById('PK_CAMPUS_DIV').style.display 	= 'inline';
				document.getElementById('MONTH_DIV').style.display 		= 'inline';
				document.getElementById('YEAR_DIV').style.display 		= 'inline';
				document.getElementById('PDF_BTN').style.display 		= 'inline';
				document.getElementById('EXCEL_BTN').style.display 		= 'inline';
			} else if(document.getElementById('REPORT_TYPE').value == 3){
				document.getElementById('PK_CAMPUS_DIV').style.display 				= 'inline';
				document.getElementById('stud_search_filter_div').style.display 	= 'block';
			} else if(document.getElementById('REPORT_TYPE').value == 4){
				document.getElementById('PK_CAMPUS_DIV').style.display 	= 'inline';
				document.getElementById('YEAR_DIV').style.display 		= 'inline';
				document.getElementById('EXCEL_BTN').style.display 		= 'inline';
			}
		}
		
		function clear_search(){
			document.getElementById('student_div').innerHTML = ''
		}
		
		function search(){
			jQuery(document).ready(function($) {
				if($('#PK_CAMPUS').val() == '') {
					alert('Please Select Campus');
				} else {
					var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&show_check=1&show_count=1&NO_LEAD=1&ENROLLMENT=2';
					var value = $.ajax({
						url: "ajax_search_student_for_reports",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('student_div').innerHTML = data
							show_btn()
						}		
					}).responseText;
				}
			});
		}
		
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			get_count()
		}
		function get_count(){
			var PK_STUDENT_MASTER_sel = '';
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					if(PK_STUDENT_MASTER_sel != '')
						PK_STUDENT_MASTER_sel += ',';
						
					PK_STUDENT_MASTER_sel += document.getElementById('S_PK_STUDENT_MASTER_'+PK_STUDENT_ENROLLMENT[i].value).value
					tot++;
				}
			}
			document.getElementById('SELECTED_PK_STUDENT_MASTER').value = PK_STUDENT_MASTER_sel
			//alert(PK_STUDENT_MASTER_sel)
			
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
		
		function show_btn(){
			
			document.getElementById('PDF_BTN').style.display = 'none';
			
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1) {
				document.getElementById('PDF_BTN').style.display = 'inline';
			} 
		}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=ALL_FIRST_TERM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=ALL_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
	});
	</script>

	<?php $report_error=""; ?>

</body>

</html>