<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
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
								<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
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
								<th width="20%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Student</i></b>
								</th>
								<th width="15%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Student ID</i></b>
								</th>
								<th width="15%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Campus</i></b>
								</th>
								<th width="15%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
									<b><i>Program</i></b>
								</th>
								<th width="12%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
									<b><i>Program Months</i></b>
								</th>
								<th width="12%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
									<b><i>Tuition Charged</i></b>
								</th>
							</tr>
						</thead>';	
						
			$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'ERROR REPORT')");
			while (!$res->EOF) {

				$txt .= '<tr>
							<td >'.$res->fields['STUDENT'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['PROGRAM'].'</td>
							<td >'.$res->fields['PROGRAM_MONTHS'].'</td>
							<td align="right" >$'.number_format($res->fields['TUITION_CHARGED'], 2).'</td>
						</tr>';

				$res->MoveNext();
			}
			
			$txt .= '</table>';
				
			//echo $txt;exit;
			
			$mpdf->WriteHTML($txt);
			$mpdf->Output("Error Report.pdf", 'D');
			exit;
			
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;	
			$index 	= -1;

			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Program Months';
			$width[]   = 20;
			$heading[] = 'Tuition Charged';
			$width[]   = 20;
						
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	
			
			$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'ERROR REPORT')");
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
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);
	
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_MONTHS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TUITION_CHARGED']);
				
				$res->MoveNext();
			}

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
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
								<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
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
			
			$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS')");
			while (!$res->EOF) {
				if($res->fields['RECORD_TYPE'] != 'REPORT TOTAL') {
					if($PROGRAM != $res->fields['PROGRAM']) {
						if($txt != '')
							$txt .= '</table>';
							
						$PROGRAM = $res->fields['PROGRAM'];
						$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<thead>
									<tr>
										<th colspan="12" align="left" ><b style="font-size:20px">'.$res->fields['PROGRAM'].' - '.$res->fields['PROGRAM_DESCRIPTION'].' - '.$res->fields['PROGRAM_MONTHS'].'</b><br /></th>
									</tr>
									<tr>
										<th width="11%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Student</i></b>
										</th>
										<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Status</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>First Term</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>End Date</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Tuition Charged</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Previous Earnings</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Calculation Status/Type</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Prorated Reason</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Calculation Date</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Finalized Date</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Earnings Amount</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Unearned Amount</i></b>
										</th>
									</tr>
								</thead>';
					}
					$ENROLLMENT_BEGIN_DATE = '';
					if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
						$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
						
					$ENROLLMENT_END_DATE = '';
					if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
						$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_END_DATE']));
					
					if($res->fields['RECORD_TYPE'] == 'DETAIL') {
						$txt .= '<tr>
									<td >'.$res->fields['STUDENT'].'</td>
									<td >'.$res->fields['STUDENT_STATUS'].'</td>
									<td >'.$ENROLLMENT_BEGIN_DATE.'</td>
									<td >'.$ENROLLMENT_END_DATE.'</td>
									<td align="right" >$'.number_format($res->fields['TUITION_CHARGED'], 2).'</td>
									<td align="right" >$'.number_format($res->fields['PREVIOUS_EARNINGS'], 2).'</td>
									<td >'.$res->fields['CALCULATION'].'</td>
									<td >'.$res->fields['PRORATED_REASON'].'</td>
									<td ></td>
									<td ></td>
									<td align="right" >$'.number_format($res->fields['EARNINGS_AMOUNT'], 2).'</td>
									<td align="right" >$'.number_format($res->fields['UNEARNED_TUITION'], 2).'</td>
								</tr>';
					} else if($res->fields['RECORD_TYPE'] == 'PROGRAM TOTAL') {
						$txt .= '<tr>
									<td style="border-top:1px solid #000;" ></td>
									<td style="border-top:1px solid #000;" ></td>
									<td style="border-top:1px solid #000;" ></td>
									<td style="border-top:1px solid #000;" ><b>Totals</b></td>
									<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TUITION_CHARGED'], 2).'</b></td>
									<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['PREVIOUS_EARNINGS'], 2).'</b></td>
									<td style="border-top:1px solid #000;" ></td>
									<td style="border-top:1px solid #000;" ></td>
									<td style="border-top:1px solid #000;" ></td>
									<td style="border-top:1px solid #000;" ></td>
									<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['EARNINGS_AMOUNT'], 2).'</b></td>
									<td style="border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['UNEARNED_TUITION'], 2).'</b></td>
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
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ><b>Grand Totals</b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['TUITION_CHARGED'], 2).'</b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['PREVIOUS_EARNINGS'], 2).'</b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['EARNINGS_AMOUNT'], 2).'</b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b>$'.number_format($res->fields['UNEARNED_TUITION'], 2).'</b></td>
						</tr>';
			}
			
			if($txt != '')
				$txt .= '</table>';
				
			//echo $txt;exit;
			
			$mpdf->WriteHTML($txt);
			$mpdf->Output("Monthly Earnings.pdf", 'D');
			return "Monthly Earnings.pdf";	
			
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;		
			$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS EXCEL')");

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

				/*$ENROLLMENT_BEGIN_DATE = '';
				if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
					$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
					
				$ENROLLMENT_END_DATE = '';
				if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
						$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_END_DATE']));*/
						
			}

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
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
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
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
				
			$PK_STUDENT_MASTER_ARR = explode(",", $_POST['SELECTED_PK_STUDENT_MASTER']);
			foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
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

				$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="40%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="40%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Student Earnings</b></td>
									</tr>
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
							<td colspan="3" width="100%" align="right" ><b>Current Enrollment: '.$ENROLLMENT.'</b></td>
						</tr>
					</table>';
					
				$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
				$footer = '<table width="100%" >
								<tr>
									<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
									<td width="43%" valign="top" style="font-size:10px;" align="center" >Prorated Reasons: &nbsp;&nbsp;&nbsp; L-LOA &nbsp;&nbsp;&nbsp; H-Holiday &nbsp;&nbsp;&nbsp; B-Break   C-Closure &nbsp;&nbsp;&nbsp; F-First Month</td>
									<td width="23%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
								</tr>
							</table>';	
					
				$mpdf->SetHTMLHeader($header);
				$mpdf->SetHTMLFooter($footer);
				$mpdf->AddPage();
				
				$PROGRAM 	= "";
				$txt 		= "";
			
				$STUD_TUITION 			= 0;
				$STUD_PREVIOUS_EARNING 	= 0;
				$STUD_EARNINGS_AMOUNT 	= 0;
				$STUD_UNEARNED_AMOUNT 	= 0;
				//echo $_POST['SELECTED_PK_STUDENT_MASTER']."<br />CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].",0 , ".$PK_STUDENT_MASTER.",'STUDENT EARNINGS')";exit;
				$count   				= 0;
				$prog_tot_displayed   	= 0;
				//$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].",0 , ".$PK_STUDENT_MASTER.",'STUDENT EARNINGS')");
				$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", 0,0 , ".$PK_STUDENT_MASTER.",'STUDENT EARNINGS')");
				if($res->RecordCount() == 0) {
					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<thead>
									<tr>
										<th colspan="12" align="left" ><b style="font-size:15px" >'.$PROGRAM.'</b><br /></th>
									</tr>
									<tr>
										<th width="11%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Earnings Year/Month</i></b>
										</th>
										<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Status</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>First Term</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>End Date</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Tuition Charged</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Previous Earnings</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Calculation Status/Type</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Prorated Reason</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Calculation Date</i></b>
										</th>
										<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Finalized Date</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Earnings Amount</i></b>
										</th>
										<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
											<b><i>Unearned Amount</i></b>
										</th>
									</tr>
								</thead>';
				}
				while (!$res->EOF) {
					if($PROGRAM != $res->fields['PROGRAM']) {
						
						if($txt != '' && $count > 0) {
							if($prog_tot_displayed == 0) {
								$prog_tot_displayed = 1;
								$txt .= '<tr>
											<td  ></td>
											<td  ></td>
											<td  ></td>
											<td  ><b>Totals</b></td>
											<td  align="right" ><b></b></td>
											<td  align="right" ><b></b></td>
											<td  ></td>
											<td  ></td>
											<td  ></td>
											<td  ></td>
											<td  align="right" ><b>$'.number_format($STUD_EARNINGS_AMOUNT, 2).'</b></td>
											<td  align="right" ><b></b></td>
										</tr>';
							}
							$txt .= '</table>';
						}
						
						$count   = 0;
						$PROGRAM = $res->fields['PROGRAM'];
						
						$PROG_TUITION 			= 0;
						$PROG_PREVIOUS_EARNING 	= 0;
						$PROG_EARNINGS_AMOUNT 	= 0;
						$PROG_UNEARNED_AMOUNT 	= 0;
						$prog_tot_displayed		= 0;
						
						$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<thead>
										<tr>
											<th colspan="12" align="left" ><b style="font-size:15px" >'.$PROGRAM.' - '.$res->fields['PROGRAM_DESCRIPTION'].'</b><br /></th>
										</tr>
										<tr>
											<th width="11%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Earnings Year/Month</i></b>
											</th>
											<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Status</i></b>
											</th>
											<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>First Term</i></b>
											</th>
											<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>End Date</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Tuition Charged</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Previous Earnings</i></b>
											</th>
											<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Calculation Status/Type</i></b>
											</th>
											<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Prorated Reason</i></b>
											</th>
											<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Calculation Date</i></b>
											</th>
											<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
												<b><i>Finalized Date</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Earnings Amount</i></b>
											</th>
											<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" >
												<b><i>Unearned Amount</i></b>
											</th>
										</tr>
									</thead>';
					}
					
					$count++;
					$ENROLLMENT_BEGIN_DATE = '';
					if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
						$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
						
					$ENROLLMENT_END_DATE = '';
					if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
						$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_END_DATE']));
						
					$CALCULATION_DATE = '';
					if($res->fields['CALCULATION_DATE'] != '' && $res->fields['CALCULATION_DATE'] != '0000-00-00')
						$CALCULATION_DATE = date("m/d/Y", strtotime($res->fields['CALCULATION_DATE']));
						
					$FINALIZED_DATE = '';
					if($res->fields['FINALIZED_DATE'] != '' && $res->fields['FINALIZED_DATE'] != '0000-00-00')
						$FINALIZED_DATE = date("m/d/Y", strtotime($res->fields['FINALIZED_DATE']));
						
					$txt .= '<tr>
								<td >'.$res->fields['EARNINGS_YEAR_MONTH'].'</td>
								<td >'.$res->fields['STUDENT_STATUS'].'</td>
								<td >'.$ENROLLMENT_BEGIN_DATE.'</td>
								<td >'.$ENROLLMENT_END_DATE.'</td>
								<td align="right" >$'.number_format($res->fields['TUITION_CHARGED'], 2).'</td>
								<td align="right" >$'.number_format($res->fields['PREVIOUS_EARNINGS'], 2).'</td>
								<td >'.$res->fields['CALCULATION'].'</td>
								<td >'.$res->fields['PRORATED_REASON'].'</td>
								<td >'.$CALCULATION_DATE.'</td>
								<td >'.$FINALIZED_DATE.'</td>
								<td align="right" >$'.number_format($res->fields['EARNINGS_AMOUNT'], 2).'</td>
								<td align="right" >$'.number_format($res->fields['UNEARNED_TUITION'], 2).'</td>
							</tr>';
					
					$PROG_TUITION 			+= $res->fields['TUITION_CHARGED'];
					$PROG_PREVIOUS_EARNING 	+= $res->fields['PREVIOUS_EARNINGS'];
					$PROG_EARNINGS_AMOUNT 	+= $res->fields['EARNINGS_AMOUNT'];
					$PROG_UNEARNED_AMOUNT 	+= $res->fields['UNEARNED_TUITION'];
					
					$STUD_TUITION 			+= $res->fields['TUITION_CHARGED'];
					$STUD_PREVIOUS_EARNING 	+= $res->fields['PREVIOUS_EARNINGS'];
					$STUD_EARNINGS_AMOUNT 	+= $res->fields['EARNINGS_AMOUNT'];
					$STUD_UNEARNED_AMOUNT 	+= $res->fields['UNEARNED_TUITION'];
					
					$res->MoveNext();
				}
				
				if($prog_tot_displayed == 0) {
					$prog_tot_displayed = 1;
					$txt .= '<tr>
								<td  ></td>
								<td  ></td>
								<td  ></td>
								<td  ><b>Totals</b></td>
								<td  align="right" ><b></b></td>
								<td  align="right" ><b></b></td>
								<td  ></td>
								<td  ></td>
								<td  ></td>
								<td  ></td>
								<td  align="right" ><b>$'.number_format($STUD_EARNINGS_AMOUNT, 2).'</b></td>
								<td  align="right" ><b></b></td>
							</tr>';
				}
				
				$txt .= '<tr>
							<th colspan="12" align="left" ><br /><br /></th>
						</tr>
						<tr>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ><b>Grand Totals</b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b></b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b></b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b>$'.number_format($STUD_EARNINGS_AMOUNT, 2).'</b></td>
							<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b></b></td>
						</tr>';
				
				if($txt != '')
					$txt .= '</table>';
				
				$mpdf->WriteHTML($txt);
				
				$db->close();
				$db->connect($db_host,'root',$db_pass,$db_name);
			}
			
			$mpdf->Output("Student Earnings.pdf", 'D');
			return "Student Earnings.pdf";
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
			$file_name 		= 'Student Earnings.xlsx';
			$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

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
				$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", 0,0 , ".$PK_STUDENT_MASTER.",'STUDENT EARNINGS')");
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
					
					$index++;
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EARNINGS_AMOUNT']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UNEARNED_TUITION']);
					
					$res->MoveNext();
				}
				
				$db->close();
				$db->connect($db_host,'root',$db_pass,$db_name);
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
		$file_name 		= 'Year Earnings.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;

		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Program Description';
		$width[]   = 20;
		
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'End Date';
		$width[]   = 20;
		$heading[] = 'Program Months';
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
		
		//$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].",".$_POST['MONTH']." , 0,'YEAR EARNINGS')");
		$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].",0 , 0,'YEAR EARNINGS')");
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CURRENT_STATUS']);
			
			$ENROLLMENT_BEGIN_DATE = '';
			if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
				$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_BEGIN_DATE);
			
			$ENROLLMENT_END_DATE = '';
			if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
				$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_END_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENROLLMENT_END_DATE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_MONTHS']);
			
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
								<td width="33%" valign="top" style="font-size:10px;" align="center" >ACCT20011</td>
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
			//echo "CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS COMPARISON')";exit;			
			$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS COMPARISON')");
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
										<th colspan="11" align="left" ><b style="font-size:20px">'.$res->fields['PROGRAM'].' - '.$res->fields['PROGRAM_DESCRIPTION'].'</b><br /></th>
									</tr>
									<tr>
										<th width="14%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Student</i></b>
										</th>
										<th width="10%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>ID</i></b>
										</th>
										<th width="12%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
											<b><i>Student Status</i></b>
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
									<td style="border-top:1px solid #000;" colspan="2" ></td>
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
									<td style="border-top:1px solid #000;" colspan="9" align="right" >RECEIVED_NOT_EARNED_BALANCE</td>
									<td style="border-top:1px solid #000;" colspan="2" align="right" ><b>$'.number_format(($res->fields['RECEIVED_NOT_EARNED_POSITIVE'] - $res->fields['RECEIVED_NOT_EARNED_NEGATIVE']), 2).'</b></td>
								</tr>';
					}
				}
						
				$res->MoveNext();
			}
			
			if($res->fields['RECORD_TYPE'] == 'REPORT TOTAL') {
				$txt .= '<tr>
							<th colspan="22" align="left" ><br /><br /></th>
						</tr>
						<tr>
							<td style="border-top:1px solid #000;" colspan="2" ></td>
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
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;	
			$index 	= -1;

			$heading[] = 'Earnings Year';
			$width[]   = 20;
			$heading[] = 'Earnings Month';
			$width[]   = 20;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Program Description';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'Previous Earnings';
			$width[]   = 20;
			$heading[] = 'Current Earnings';
			$width[]   = 20;
			$heading[] = 'Total Earnings';
			$width[]   = 20;
			$heading[] = 'Unearned Tuition';
			$width[]   = 20;
			$heading[] = 'Total Tuition';
			$width[]   = 20;
			$heading[] = 'Non Tuition Charged';
			$width[]   = 20;
			$heading[] = 'Total Received';
			$width[]   = 20;
			$heading[] = 'Received Not Earned Positive';
			$width[]   = 20;
			$heading[] = 'Received Not Earned Negative';
			$width[]   = 20;
						
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			}	
		
			$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].", ".$_POST['MONTH'].", 0,'MONTH EARNINGS COMPARISON EXCEL')");
			$s_no = 0;
			while (!$res->EOF) {
				$line++;
				$index = -1;

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EARNINGS_YEAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EARNINGS_MONTH']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PREVIOUS_EARNINGS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CURRENT_EARNINGS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TOTAL_EARNINGS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UNEARNED_TUITION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TOTAL_TUITION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NON_TUITION_CHARGED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TOTAL_RECEIVED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RECEIVED_NOT_EARNED_POSITIVE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['RECEIVED_NOT_EARNED_NEGATIVE']);
				
				$res->MoveNext();
			}
			
			$objPHPExcel->getActiveSheet()->freezePane('A1');
		
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
			
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
	<title><?=MNU_EARNINGS_REPORTS ?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_EARNINGS_REPORTS ?></h4>
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
												<div class="col-2 col-sm-2">
													<div class="form-group m-b-40">
														<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control required-entry" onchange="show_fields()" >
															<option value="1" >Error Report</option>
															<option value="2" >Monthly Earnings</option>
															<option value="5" >Monthly Earnings - Received Comparison</option>
															<option value="3" >Student Earnings</option>
															<option value="4" >Yearly Earnings</option>
														</select>
														
														<span class="bar"></span> 
														<label for="REPORT_TYPE"><?=REPORT_TYPE?></label>
													</div>
												</div>
												
												<div class="col-3 col-sm-3" id="PK_CAMPUS_DIV" >
													<div class="form-group m-b-40">
														<select id="PK_CAMPUS" name="PK_CAMPUS"  class="form-control required-entry" >
															<option ></option>
															<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
															while (!$res_type->EOF) { 
																if($res_type->RecordCount() == 1)
																	$selected = 'selected'; ?>
																<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
															<?	$res_type->MoveNext();
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
													<button  type="button" onclick="window.location.href='earnings_calculation'" class="btn waves-effect waves-light btn-info"><?=RETURN_TO_CALCULATION ?></button>
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
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
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
</body>

</html>