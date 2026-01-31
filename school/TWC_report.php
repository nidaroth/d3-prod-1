<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/TWC_report.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT TWC FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['TWC'] == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	$PK_CAMPUS_PROGRAM_ID = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_id 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN($campus_id)"; //DIAM-939

	}

	if(!empty($_POST['PK_CAMPUS_PROGRAM'])){
		$PK_CAMPUS_PROGRAM_ID 	 = implode(",",$_POST['PK_CAMPUS_PROGRAM']);
	}

	if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	}
	if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	}
		
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];
		//DIAM-939
		// if($campus_id != '')
		// 	$campus_id .= ',';
		// $campus_id .= $res_campus->fields['PK_CAMPUS'];
		//DIAM-939
		
		$res_campus->MoveNext();
	}

	if($_POST['FORMAT'] == 1) {
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	
		$REPORT_NAME = "";
		if($_POST['REPORT_TYPE'] == 1)
			$REPORT_NAME = "Enrollment Outcome";
		else if($_POST['REPORT_TYPE'] == 2)
			$REPORT_NAME = "Master Student Registration List";
		else if($_POST['REPORT_TYPE'] == 3)
			$REPORT_NAME = "Completer Information";
			
		require_once '../global/mpdf/vendor/autoload.php';
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Texas Workforce Commission</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >'.$REPORT_NAME.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Between: '.$_POST['START_DATE'].' - '.$_POST['END_DATE'].'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
						</tr>';
						
						if($_POST['REPORT_TYPE'] == 1) {
							$header .= '<tr>
											<td colspan="3" width="100%" align="right" style="font-size:13px;" >Group By: '.$_POST['GROUP_BY'].'</td>
										</tr>';
						} else if($_POST['REPORT_TYPE'] == 2){
							$header .= '<tr>
											<td colspan="3" width="100%" align="right" style="font-size:13px;" >Date Type: '.$_POST['DATE_TYPE'].'</td>
										</tr>';
						}
						
		$header .= '</table>';
					
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
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>COMP50001</i></td>
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
			'default_font_size' => 8,
			'format' => [210, 296],
			'orientation' => 'L'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		if($_POST['REPORT_TYPE'] == 1) {
			
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
						<thead>
							<tr>
								<td width="7.70%" style="border-bottom:1px solid #000;">
									<br /><b><i>Program</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;" >
									<br /><b><i>Beginning Population</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>New Enrollments</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Re-Entry</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Transfer-In</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Transfer-Out</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Drops Military</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Drops Incarcerated</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Drops Deceased</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Drops Other</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Other Withdrawals</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Graduates</i></b>
								</td>
								<td width="7.70%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Other Completers</i></b>
								</td>
							</tr>
						</thead>';
					
			$res = $db->Execute("CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Enrollment Outcome', '".$ST."', '".$ET."', 'Summary', '".$_POST['GROUP_BY']."', '',".$_SESSION['PK_USER'].",'".$PK_CAMPUS_PROGRAM_ID."' )");
			while (!$res->EOF) {
				$b1 = "";
				$b2 = "";
				
				$txt .= '<tr>
							<td>'.$res->fields['PROGRAM'].'</td>
							<td>'.$res->fields['BEGINNING_POPULATION'].'</td>
							<td>'.$res->fields['NEW_ENROLLMENTS'].'</td>
							<td>'.$res->fields['RE_ENTRY'].'</td>
							<td>'.$res->fields['TRANSFER_IN'].'</td>
							<td>'.$res->fields['TRANSFER_OUT'].'</td>
							<td>'.$res->fields['DROPS_MILITARY'].'</td>
							<td>'.$res->fields['DROPS_INCARCERATED'].'</td>
							<td>'.$res->fields['DROPS_DECEASED'].'</td>
							<td>'.$res->fields['DROPS_OTHER'].'</td>
							<td>'.$res->fields['OTHER_WITHDRAWALS'].'</td>
							<td>'.$res->fields['GRADUATES'].'</td>
							<td>'.$res->fields['OTHER_COMPLETERS'].'</td>
						</tr>';
			
				$res->MoveNext();
			}
			
			$txt .= '</table>';
			$mpdf->allow_charset_conversion = true; //DIAM-1057
			$mpdf->charset_in = 'iso-8859-4'; //DIAM-1057
			$mpdf->WriteHTML($txt); 
			$mpdf->Output('TWC Enrollment Outcome.pdf', 'D'); //DIAM-1057
		} else if($_POST['REPORT_TYPE'] == 2) {
			
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
						<thead>
							<tr>
								<td width="6.75%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Date</i></b>
								</td>
								<td width="15.9%" style="border-bottom:1px solid #000;">
									<br /><b><i>Name of Student</i></b>
								</td>
								
								<td width="26.60%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Address City/State/Zip</i></b>
								</td>
								<td width="8.15%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Phone</i></b>
								</td>
								<td width="7.15%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Student ID</i></b>
								</td>
								<td width="6.15%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Date of Birth</i></b>
								</td>
								<td width="12.15%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Name of Program</i></b>
								</td>
								<td width="10.5%" style="border-bottom:1px solid #000;"  >
									<br /><b><i>Current/Grad/Drop/Cancel/Term & Date</i></b>
								</td>
							</tr>
						</thead>';
					
			//echo "CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Master Student Roster', '".$ST."', '".$ET."', 'Summary', '".$_POST['GROUP_BY']."' , '".$_POST['DATE_TYPE']."' )";exit;
			$res = $db->Execute("CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Master Student Roster', '".$ST."', '".$ET."', 'Summary', '".$_POST['GROUP_BY']."' , '".$_POST['DATE_TYPE']."', ".$_SESSION['PK_USER'].",'".$PK_CAMPUS_PROGRAM_ID."' )");
			while (!$res->EOF) {
				$Date = "";
				if($res->fields['Date'] != '' && $res->fields['Date'] != '0000-00-00')
					$Date = date("m/d/Y", strtotime($res->fields['Date'] ));
					
				//$DOB = $res->fields['Date of Birth'];
				$DOB = date("m/d/Y", strtotime($res->fields['Date of Birth'] ));
				//if($res->fields['DOB'] != '' && $res->fields['DOB'] != '0000-00-00')
					//$DOB = date("m/d/Y", strtotime($res->fields['DOB'] ));
					
				$START_DATE = "";
				if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
					$START_DATE = date("m/d/Y", strtotime($res->fields['START_DATE'] ));
					
				// $SSN = $res->fields['SSID No.'];
				// if($SSN != '') {
				// 	$SSN  = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
				// }
				
				$SSN = $res->fields['SSID No.']; //DIAM-1057
				// if(empty($SSN))
				//   $SSN ='NA';
					
				$txt .= '<tr>
							<td>'.$Date.'</td>
							<td>'.$res->fields['Name of Student'].'</td>
							<td>'.$res->fields['Address City/State/Zip'].'</td>
							<td>'.$res->fields['Phone'].'</td>
							<td>'.$SSN.'</td>
							<td>'.$DOB.'</td>
							<td>'.$res->fields['Name of Program'].'</td>
							<td>'.$res->fields['Current/Grad/Drop/Cancel/Term & Date'].'</td>
						</tr>';
			
				$res->MoveNext();
			}
			
			$txt .= '</table>';
			$mpdf->allow_charset_conversion = true; //DIAM-1057
			$mpdf->charset_in = 'iso-8859-4'; //DIAM-1057
			$mpdf->WriteHTML($txt); //DIAM-1057
			$mpdf->Output('TWC Master Student Registration List.pdf', 'D'); //DIAM-1057
		}
	
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
		
		if($_POST['REPORT_TYPE'] == 1) {

			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			//$file_name 		= 'Enrollment Outcome.xlsx';
			$file_name 		= 'TWC Enrollment Outcome.xlsx'; //DIAM-1057
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;	
			$index 	= -1;

			$heading[] = 'Student';
			$width[]   = 20;
			//DIAM-1057
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			//DIAM-1057
			$heading[] = 'Program Group';
			$width[]   = 20;
			$heading[] = 'Program Code'; //DIAM-1057
			$width[]   = 20;
			//DIAM-1057
			$heading[] = 'Program Description';
			$width[]   = 20;
			//DIAM-1057
			$heading[] = 'Start Date';
			$width[]   = 20;
			$heading[] = 'Drop Date';
			$width[]   = 20;
			$heading[] = 'LDA';
			$width[]   = 20;
			$heading[] = 'Grad Date';
			$width[]   = 20;
			
			$heading[] = 'Population Report Status';
			$width[]   = 20;
			$heading[] = 'Beginning Population';
			$width[]   = 20;
			$heading[] = 'New Enrollments';
			$width[]   = 20;
			$heading[] = 'Re-Entry';
			$width[]   = 20;
			$heading[] = 'Transfer In'; //DIAM-1057
			$width[]   = 20;
			$heading[] = 'Transfer Out'; //DIAM-1057
			$width[]   = 20;
			$heading[] = 'Drops Military';
			$width[]   = 20;
			$heading[] = 'Drops Incarcerated';
			$width[]   = 20;
			$heading[] = 'Drops Deceased';
			$width[]   = 20;
			$heading[] = 'Drops Other';
			$width[]   = 20;
			$heading[] = 'Other Withdrawals';
			$width[]   = 20;
			$heading[] = 'Graduates';
			$width[]   = 20;
			$heading[] = 'Other Completers';
			$width[]   = 20;
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				$i++;
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
			//echo "CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Enrollment Outcome', '".$ST."', '".$ET."', 'Detail', '', '' )";exit;
			$res_fa = $db->Execute("CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Enrollment Outcome', '".$ST."', '".$ET."', 'Detail', '', '', ".$_SESSION['PK_USER'].",'".$PK_CAMPUS_PROGRAM_ID."' )");
			while (!$res_fa->EOF) { 
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT']);
				 //DIAM-1057
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_ID']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CAMPUS_CODE']);
				 //DIAM-1057
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM_GROUP']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM_CODE']);

				//DIAM-1057
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM_DESCRIPTION']);
				//DIAM-1057
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['START_DATE'] != '' && $res_fa->fields['START_DATE'] != '0000-00-00' ) {
					// $dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['START_DATE'])));
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					// $objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['START_DATE']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['DROP_DATE'] != '' && $res_fa->fields['DROP_DATE'] != '0000-00-00' ) {
					// $dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['DROP_DATE'])));
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					// $objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['DROP_DATE']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['LDA'] != '' && $res_fa->fields['LDA'] != '0000-00-00' ) {
					// $dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['LDA'])));
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					// $objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['LDA']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['GRAD_DATE'] != '' && $res_fa->fields['GRAD_DATE'] != '0000-00-00' ) {
					// $dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['GRAD_DATE'])));
					// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					// $objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['GRAD_DATE']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['POPULATION_REPORT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['BEGINNING_POPULATION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['NEW_ENROLLMENTS']);
					
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['RE_ENTRY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['TRANSFER_IN']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['TRANSFER_OUT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['DROPS_MILITARY']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['DROPS_INCARCERATED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['DROPS_DECEASED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['DROPS_OTHER']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['OTHER_WITHDRAWALS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['GRADUATES']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['OTHER_COMPLETERS']);
				
				$res_fa->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 2) {
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			//$file_name 		= 'Master Student Registration List.xlsx';
			$file_name 		= 'TWC Master Student Registration List.xlsx';	//DIAM-1057		
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;	
			$index 	= -1;
			
			$heading[] = 'DATE';
			$width[]   = 20;
			$heading[] = 'Name of Student';
			$width[]   = 20;
			$heading[] = 'Address City/Sate/Zip';
			$width[]   = 20;
			$heading[] = 'PHONE';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Date of Birth';
			$width[]   = 20;
			$heading[] = 'Name of Program';
			$width[]   = 20;
			$heading[] = 'Student Status';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'End Date Type';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Admissions Rep';
			$width[]   = 20;
			$heading[] = 'Lead Source';
			$width[]   = 20;
			$heading[] = 'Session';
			$width[]   = 20;

				
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				$i++;
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
		
			//echo "CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Master Student Roster', '".$ST."', '".$ET."', 'Detail', '', '' )";exit;
			$res_fa = $db->Execute("CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Master Student Roster', '".$ST."', '".$ET."', 'Detail', '', '', ".$_SESSION['PK_USER'].",'".$PK_CAMPUS_PROGRAM_ID."' )");
			while (!$res_fa->EOF) { 
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['DATE'] != '' && $res_fa->fields['DATE'] != '0000-00-00' ) {
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['DATE']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				}

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT']);

				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['ADDRESS CITY/STATE/ZIP']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PHONE']);
				
				$SSN = $res_fa->fields['STUDENT_ID'];				
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN);

				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['DOB'] != '' && $res_fa->fields['DOB'] != '0000-00-00' ) {
					//$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['DOB'])));
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['DOB']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					//$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM_DESCRIPTION']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_STATUS']);

				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['END_DATE'] != '' && $res_fa->fields['END_DATE'] != '0000-00-00' ) {
					//$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['DOB'])));
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['END_DATE']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					//$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['END_DATE_TYPE']);


				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CAMPUS']);
				
				// $index++;
				// $cell_no = $cell[$index].$line;
				// if($res_fa->fields['DATE'] != '' && $res_fa->fields['DATE'] != '0000-00-00' ) {
				// 	$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['DATE'])));
				// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				// 	$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				// }
			
							
				// $index++;
				// $cell_no = $cell[$index].$line;
				// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CITY_STATE_ZIP']);
				

				// $index++;
				// $cell_no = $cell[$index].$line;
				// if($res_fa->fields['START_DATE'] != '' && $res_fa->fields['START_DATE'] != '0000-00-00' ) {
				// 	$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['START_DATE'])));
				// 	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				// 	$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				// }
				
				// $index++;
				// $cell_no = $cell[$index].$line;
				// $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['ADMISSIONS_REP']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['LEAD_SOURCE']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['SESSION']);
							
				
	
				$res_fa->MoveNext();
			}
		} else if($_POST['REPORT_TYPE'] == 3) {
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			//$file_name 		= 'Completer Information.xlsx';
			$file_name 		= 'TWC Completer Information.xlsx'; //DIAM-1057
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;	
			$index 	= -1;

			$heading[] = '1';
			$width[]   = 20;
			$heading[] = '2';
			$width[]   = 20;
			$heading[] = '3';
			$width[]   = 20;
			$heading[] = '4';
			$width[]   = 20;
			$heading[] = '5';
			$width[]   = 20;
			$heading[] = '6';
			$width[]   = 20;
			$heading[] = '7';
			$width[]   = 20;
			$heading[] = '8';
			$width[]   = 20;
			$heading[] = '9';
			$width[]   = 20;
			$heading[] = '10';
			$width[]   = 20;
			$heading[] = '11';
			$width[]   = 20;
			$heading[] = '12';
			$width[]   = 20;
			$heading[] = '13';
			$width[]   = 20;
			$heading[] = '14';
			$width[]   = 20;
			$heading[] = '15';
			$width[]   = 20;
			$heading[] = '16';
			$width[]   = 20;
			$heading[] = '17';
			$width[]   = 20;
			
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				$i++;
			}	

			$objPHPExcel->getActiveSheet()->freezePane('A1');
		
			// echo "CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Completer Information', '".$ST."', '".$ET."', 'Detail', '', '', ".$_SESSION['PK_USER'].",'".$PK_CAMPUS_PROGRAM_ID."' )"; 

			// die;
			$res_fa = $db->Execute("CALL COMP50001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', 'Completer Information', '".$ST."', '".$ET."', 'Detail', '', '', ".$_SESSION['PK_USER'].",'".$PK_CAMPUS_PROGRAM_ID."' )");
			while (!$res_fa->EOF) { 
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_2']);
				
				$SSN = $res_fa->fields['_3'];
				if($SSN != '') {
					$SSN  = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN);
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['_4'] != '' && $res_fa->fields['_4'] != '00/00/0000' ) {
					//$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('m/d/Y', $res_fa->fields['_4']))); //DIAM-1057
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['_4']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					//$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				if($res_fa->fields['_5'] != '' && $res_fa->fields['_5'] != '00/00/0000' ) {
					//$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('m/d/Y', $res_fa->fields['_5']))); //DIAM-1057
					$dateValue = date("m/d/Y",strtotime($res_fa->fields['_5']));
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
					//$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22);
				}
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_6']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_7']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_8']);
					
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_9']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_10']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_11']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_12']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_13']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_14']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_15']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_16']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['_17']);
					
				$res_fa->MoveNext();
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
	<title><?=MNU_TWC ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS, #advice-required-entry-PK_STUDENT_STATUS{position: absolute;top: 57px; width:150px}
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
							<?=MNU_TWC ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">

								<div class="col-md-12 text-right" id="REPORT_SETUP_BTN" >
											<button type="button" onclick="window.location.href='TWC_setup'" class="btn waves-effect waves-light btn-info"><?=REPORT_SETUP?></button>
											<br />
											<? $res = $db->Execute("select * from S_TWC_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											if($res->fields['EDITED_ON'] != '' && $res->fields['EDITED_ON'] != '0000-00-00 00:00:00'){
												$EDITED_BY	= $res->fields['EDITED_BY'];
												$EDITED_ON	= $res->fields['EDITED_ON'];
												$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$EDITED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 

												$EDITED_BY	= $res_user->fields['NAME']; 
												echo "Edited: ".$EDITED_BY." ".date("m/d/Y",strtotime($EDITED_ON));
											} ?>
									</div>

									<div class="row form-group">
										<div class="col-md-2">
											<?=REPORT_TYPE ?>
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" onchange="show_fields()" >
												<option value="3" >Completer Information</option>
												<option value="1" >Enrollment Outcome</option>
												<option value="2" >Master Student Registration List</option>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2 ">
										<?=PROGRAM?>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT PK_CAMPUS_PROGRAM,CODE, DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,CODE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'];
                                                    if($res_type->fields['ACTIVE'] == 0)
                                                    {
                                                        $option_label .= " (Inactive)"; 
                                                    }
													?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-1 align-self-center" >
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" placeholder="" >
										</div>
										
										<div class="col-md-1 align-self-center" >
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" placeholder="" >
										</div>
										
										<div class="col-md-2" id="GROUP_BY_DIV" >
											<?=GROUP_BY ?>
											<select id="GROUP_BY" name="GROUP_BY" class="form-control" >
												<option value="Program" >Program</option>
												<option value="Program Group" >Program Group</option>
											</select>
										</div>
										
										<div class="col-md-2" id="DATE_TYPE_DIV" style="display:none" >
											<?=DATE_TYPE ?>
											<select id="DATE_TYPE" name="DATE_TYPE" class="form-control" >
												<option value="Contract Signed Date" >Contract Signed Date</option>
												<option value="First Term Date" >First Term Date</option>
											</select>
										</div>
										
										
										
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" id="PDF_BTN" style="display:none" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="EXCEL_BTN" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
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
		
		if(document.getElementById('REPORT_TYPE').value == 2) {
			document.getElementById('DATE_TYPE_DIV').style.display 	= 'block'
		} else {
			document.getElementById('DATE_TYPE_DIV').style.display 	= 'none'
		}
		
		if(document.getElementById('REPORT_TYPE').value == 1) {
			document.getElementById('GROUP_BY_DIV').style.display 	= 'inline'
		} else {
			document.getElementById('GROUP_BY_DIV').style.display 	= 'none'
		}
		
		if(document.getElementById('REPORT_TYPE').value == 3) {
			document.getElementById('PDF_BTN').style.display 	= 'none'
		} else {
			document.getElementById('PDF_BTN').style.display 	= 'inline';
		}

		if(document.getElementById('REPORT_TYPE').value == 2 || document.getElementById('REPORT_TYPE').value == 1){
			//document.getElementById('REPORT_SETUP_BTN').style.marginLeft = '0px';
		}else{
			//document.getElementById('REPORT_SETUP_BTN').style.marginLeft = '230px';
		}
	}
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

		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});

	});
	</script>
</body>

</html>
