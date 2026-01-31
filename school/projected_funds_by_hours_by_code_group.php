<?php 
// include '../global/excel/Classes/PHPExcel/IOFactory.php';

if (!empty($_POST)) {
	$cond = "";
	if ($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
		$cond .= " AND DISBURSEMENT_DATE BETWEEN '$ST' AND '$ET' ";
	} else if ($_POST['START_DATE'] != '') {
		$ST = date("Y-m-d", strtotime($_POST['START_DATE']));
		$cond .= " AND DISBURSEMENT_DATE >= '$ST' ";
	} else if ($_POST['END_DATE'] != '') {
		$ET = date("Y-m-d", strtotime($_POST['END_DATE']));
		$cond .= " AND DISBURSEMENT_DATE <= '$ET' ";
	}

	/** DIAM - 601 **/
	$INCLUDE_ALL_LEADS = "No";
	if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
		$INCLUDE_ALL_LEADS = "Yes";
	}

	if (!empty($_POST['PK_STUDENT_STATUS'])) {
		//$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
		$sts = implode(",", $_POST['PK_STUDENT_STATUS']);
	} else {
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 0) - 13 June 2023 - DIAM-635
		while (!$res_type->EOF) {
			if ($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}

		// if($sts != '')
		// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	}

	if ($_POST['INCLUDE_ALL_LEADS'] == 1) {
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC"); // remove - AND (ADMISSIONS = 1) - 13 June 2023 - DIAM-635
		while (!$res_type->EOF) {
			if ($sts != '') {
				$sts .= ',';
			}
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}
		// if($sts != '')
		// 	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	}

	if (!empty($_POST['PK_STUDENT_STATUS']) || $_POST['INCLUDE_ALL_LEADS'] == 1) {
		$final_sts = implode(',', array_unique(explode(',', $sts)));
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (" . $final_sts . ") ";
		//echo $cond;exit;
	}
	/** End DIAM - 601 **/

	$ledger_cond = "";
	if (!empty($_POST['PK_AR_LEDGER_CODE'])) {
		$ledger_cond = " AND PK_AR_LEDGER_CODE in (" . implode(",", $_POST['PK_AR_LEDGER_CODE']) . ") ";
	}

	if (!empty($_POST['PK_CAMPUS_PROGRAM'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM in (" . implode(",", $_POST['PK_CAMPUS_PROGRAM']) . ") ";
	}

	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if (!empty($_POST['PK_CAMPUS'])) {
		$PK_CAMPUS 	  = implode(",", $_POST['PK_CAMPUS']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}

	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if ($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];

		if ($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];

		$res_campus->MoveNext();
	}

	if ($_POST['FORMAT'] == 1) {
		
		$projected_fund_data=fetch_data($cond,$ledger_cond,$campus_id,$_POST['FORMAT']);

		$total 	= 0;
		$summary_arr = [];
		

		foreach ($projected_fund_data['complete'] as $key=>$items) {
			$sub_total = 0;

		if (count($items)>0) {
			if($counter_index > 0){
				$pagebreak = ' style="page-break-before: always" ';
			}
			$txt .= '<br><h2 '.$pagebreak.'>' . $ar_ledger_codes->fields['LEDGER_CODE_GROUP'] . '</h2>';

				$txt .= '<br><br>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="50%" ><h1><i>' . $key . '</i></h1></td>
								<td width="50%" align="right"><h2><i>Required Hours Completed</i></h2></td>
							</tr>
							<tr>
								<td width="26%" align="center" style="border-top:1px solid #000;border-left:1px solid #000; font-size:28px;" ><b>Student</b> </td>
								<td width="29%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Current Enrollment</b> </td>
								<td width="25%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Disbursement</b> </td>
								<td width="20%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Attendance Hours</b> </td>
							</tr>
							<tr nobr="true" >
								<td width="13%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Name</b></td>
								<td width="8%" style="border-bottom:1px solid #000;" ><b>SSN</b></td>
								<td width="5%" style="border-bottom:1px solid #000;" ><b>EFC/SAI</b></td>

								<td width="5%" style="border-left:1px solid #000;border-bottom:1px solid #000;"><b>Status</b></td>
								<td width="5%" style="border-bottom:1px solid #000;" ><b>Program</b></td>
								<td width="6%" style="border-bottom:1px solid #000;" ><b>First Term</b></td>
								<td width="6%" style="border-bottom:1px solid #000;" ><b>Exp. Grad</b></td>
								<td width="7%" style="border-bottom:1px solid #000;" ><b>LDA</b></td>

								<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>AR Balance</b></td>
								<td width="7%" style="border-bottom:1px solid #000;" ><b>Date</b></td>
								<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AY</b></td>
								<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AP</b></td>
								<td width="7%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Amount</b></td>

								<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Completed</b></td>
								<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Required</b></td>
								<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Remaining</b></td>

							</tr>
						</thead>';

						$i=1;
						foreach ($items as $row){

							$txt 	.= '<tr nobr="true">
							<td width="13%"> '.$i.'&nbsp;&nbsp;&nbsp;' . $row['NAME'].'</td>
							<td width="8%">' . $row['SSN'] . '</td>
							<td width="5%">' . $row['EFC_NO'] . '</td>
							<td width="5%">' . $row['STUDENT_STATUS'] . '</td>
							<td width="5%">' . $row['PROGRAM_CODE'] . '</td>
							<td width="6%">' . $row['BEGIN_DATE_1'] . '</td>
							<td width="6%">' . $row['EXPECTED_GRAD_DATE'] . '</td>
							<td width="7%">' . $row['LDA_DATE'] . '</td>
							<td width="7%">$'.$row['AR_BALANCE'].'</td>					
							<td width="7%">' . $row['DISBURSEMENT_DATE'] . '</td>
							<td width="2%" align="right">' . $row['ACADEMIC_YEAR'] . '</td>
							<td width="2%" align="right">' . $row['ACADEMIC_PERIOD'] . '</td>
							<td width="7%" align="right">$ ' . $row['DISBURSEMENT_AMOUNT'] . '</td>
							<td width="7%" align="right">' .$row['HOURS_COMPLETED']. '</td>
							<td width="7%" align="right">' . $row['HOURS_REQUIRED'] . '</td>
							<td width="6%" align="right">' . $row['HOURS_REMAINING'] . '</td>
						</tr>';
					$sub_total += $row['DISBURSEMENT_AMOUNT'];
					$i++;

						}
				$total += $sub_total;
				$txt 	.= '<tr>
							<td width="60%"></td>
							<td width="40%" style="font-size:30px;" align="right"><b>' . $key . '&nbsp;&nbsp;&nbsp; $ ' . number_format_value_checker($sub_total, 2) . '</b></td>
						</tr>
					</table>';
				$summary_arr[$key] = $sub_total;
				$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] += $sub_total;
			}
			
			//$res_ledger->MoveNext();
		}


		$total 	= 0;
		$summary_arr = [];
		foreach ($projected_fund_data['incomplete'] as $key=>$items) {
			$sub_total = 0;

			if (count($items)>0) {
					$txt .= '<br><br>
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="50%" ><h1><i>' . $key . '</i></h1></td>
									<td width="50%" align="right"><h2><i>Required Hours Incompleted</i></h2></td>
								</tr>
								<tr>
									<td width="26%" align="center" style="border-top:1px solid #000;border-left:1px solid #000; font-size:28px;" ><b>Student</b> </td>
									<td width="29%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Current Enrollment</b> </td>
									<td width="25%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Disbursement</b> </td>
									<td width="20%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:28px;" ><b>Attendance Hours</b> </td>
								</tr>
								<tr nobr="true" >
									<td width="13%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>Name</b></td>
									<td width="8%" style="border-bottom:1px solid #000;" ><b>SSN</b></td>
									<td width="5%" style="border-bottom:1px solid #000;" ><b>EFC/SAI</b></td>
	
									<td width="5%" style="border-left:1px solid #000;border-bottom:1px solid #000;"><b>Status</b></td>
									<td width="5%" style="border-bottom:1px solid #000;" ><b>Program</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>First Term</b></td>
									<td width="6%" style="border-bottom:1px solid #000;" ><b>Exp. Grad</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>LDA</b></td>
	
									<td width="7%" style="border-left:1px solid #000;border-bottom:1px solid #000;" ><b>AR Balance</b></td>
									<td width="7%" style="border-bottom:1px solid #000;" ><b>Date</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AY</b></td>
									<td width="2%" style="border-bottom:1px solid #000;" align="right" ><b>AP</b></td>
									<td width="7%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Amount</b></td>
	
									<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Completed</b></td>
									<td width="7%" align="right" style="border-bottom:1px solid #000;" ><b>Required</b></td>
									<td width="6%" align="right" style="border-right:1px solid #000;border-bottom:1px solid #000;" ><b>Remaining</b></td>
	
								</tr>
							</thead>';
	
							$i=1;
							foreach ($items as $row){
	
								$txt 	.= '<tr nobr="true">
								<td width="13%"> '.$i.'&nbsp;&nbsp;&nbsp;' . $row['NAME'].'</td>
								<td width="8%">' . $row['SSN'] . '</td>
								<td width="5%">' . $row['EFC_NO'] . '</td>
								<td width="5%">' . $row['STUDENT_STATUS'] . '</td>
								<td width="5%">' . $row['PROGRAM_CODE'] . '</td>
								<td width="6%">' . $row['BEGIN_DATE_1'] . '</td>
								<td width="6%">' . $row['EXPECTED_GRAD_DATE'] . '</td>
								<td width="7%">' . $row['LDA_DATE'] . '</td>
								<td width="7%">$'.$row['AR_BALANCE'].'</td>					
								<td width="7%">' . $row['DISBURSEMENT_DATE'] . '</td>
								<td width="2%" align="right">' . $row['ACADEMIC_YEAR'] . '</td>
								<td width="2%" align="right">' . $row['ACADEMIC_PERIOD'] . '</td>
								<td width="7%" align="right">$ ' . $row['DISBURSEMENT_AMOUNT'] . '</td>
								<td width="7%" align="right">' .$row['HOURS_COMPLETED']. '</td>
								<td width="7%" align="right">' . $row['HOURS_REQUIRED'] . '</td>
								<td width="6%" align="right">' . $row['HOURS_REMAINING'] . '</td>
							</tr>';
						$sub_total += $row['DISBURSEMENT_AMOUNT'];
						$i++;
	
							}
					$total += $sub_total;
					$txt 	.= '<tr>
								<td width="60%"></td>
								<td width="40%" style="font-size:30px;" align="right"><b>' . $key . '&nbsp;&nbsp;&nbsp; $ ' . number_format_value_checker($sub_total, 2) . '</b></td>
							</tr>
						</table>';
					$summary_arr[$key] = $sub_total;
					$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] += $sub_total;

				}
				
				//$res_ledger->MoveNext();
			}
	



		

		// $fp_test = fopen('temp/test_13405.html', 'w');
		// fwrite($fp_test, $txt);

		//echo $txt;exit;

		
	} else if ($_POST['FORMAT'] == 2) {

		$projected_fund_data=fetch_data($cond,$ledger_cond,$campus_id,$_POST['FORMAT']);


		//END OF PREQ
		$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		$total_fields = 120;
		for ($i = 0; $i <= $total_fields; $i++) {
			if ($i <= 25)
				$cell[] = $cell1[$i];
			else {
				$j = floor($i / 26) - 1;
				$k = ($i % 26);
				//echo $j."--".$k."<br />";
				$cell[] = $cell1[$j] . $cell1[$k];
			}
		}

		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Projected Funds .xlsx';
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(
			pathinfo($outputFileName, PATHINFO_FILENAME),
			pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
			$outputFileName
		);

	
		try {
					//check if sheet exists 
					$sheety = $objPHPExcel->setActiveSheetIndex($counter_index);
					$sheety->setTitle(substr(clean_sheet_title($ar_ledger_codes->fields['LEDGER_CODE_GROUP']) , 0 , 31));
				} catch (\Throwable $th) {
					//throw $th;
					//else create new sheet and set to active
					$newsheety = $objPHPExcel->createSheet($counter_index);
					$newsheety->setTitle(substr(clean_sheet_title($ar_ledger_codes->fields['LEDGER_CODE_GROUP']) , 0 , 31));
					$objPHPExcel->setActiveSheetIndex($counter_index);
				}

		$line++;
		$index 	= -1;
		//students details
		$heading = false;
		$width = false;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Ledger Code';
		$width[]   = 20;
		$heading[] = 'Student';
		$width[]   = 15;
		$heading[] = 'SSN';
		$width[]   = 15;
		$heading[] = 'EFC/SAI';
		$width[]   = 15;
		//enrrollment details
		$heading[] = 'Status';
		$width[]   = 15;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 15;
		$heading[] = 'Exp. Grad';
		$width[]   = 15;
		$heading[] = 'LDA';
		$width[]   = 15;
		// Disbursement detail
		$heading[] = 'AR Balance';
		$width[]   = 15;
		$heading[] = 'Disbursement Date';
		$width[]   = 15;
		$heading[] = 'AY';
		$width[]   = 15;
		$heading[] = 'AP';
		$width[]   = 15;
		$heading[] = 'Disbursement Amount';
		$width[]   = 15;
		// required hours completed
		$heading[] = 'Hours Completed';
		$width[]   = 15;
		$heading[] = 'Hours Required';
		$width[]   = 15;
		$heading[] = 'Hours Remaining';
		$width[]   = 15;


		$i = 0;
		foreach ($heading as $title) {
			$index++;
			$cell_no = $cell[$index] . $line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);

			$i++;
		}

		foreach ($projected_fund_data as $key=>$items) 
		{
				if (count($items)>0) 
				{				
					foreach ($items as $row){
									$line++;
									$index = -1;

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['CAMPUS']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($key);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['NAME']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['SSN']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['EFC_NO']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['STUDENT_STATUS']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['PROGRAM_CODE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['BEGIN_DATE_1']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['EXPECTED_GRAD_DATE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['LDA_DATE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['AR_BALANCE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['DISBURSEMENT_DATE']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['ACADEMIC_YEAR']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['ACADEMIC_PERIOD']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['DISBURSEMENT_AMOUNT']);
									$final_group_summary[$ar_ledger_codes->fields['LEDGER_CODE_GROUP']] += $row['DISBURSEMENT_AMOUNT'];

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['HOURS_COMPLETED']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['HOURS_REQUIRED']);

									$index++;
									$cell_no = $cell[$index] . $line;
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($row['HOURS_REMAINING']);
						}
			    }



			}

		
	}


	
}
?>
