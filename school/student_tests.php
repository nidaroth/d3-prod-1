<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_test.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
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
	
	$cond = "";
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
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($campus_id) ";
	
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];
	
	/* $PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) "; */
	
	$PK_STUDENT_MASTER_ARR = array();
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
		$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}
	$PK_STUDENT_MASTER = implode(",", $PK_STUDENT_MASTER_ARR);
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) ";
	
	if($_POST['REPORT_TYPE'] == 1) {
		//ACT
		if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
			$START_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$END_DATE 	 = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND TEST_DATE BETWEEN '$START_DATE' AND '$END_DATE' ";
		} else if($_REQUEST['START_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$cond .= " AND TEST_DATE >= '$START_DATE' ";
		} else if($_REQUEST['END_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND TEST_DATE <= '$END_DATE' ";
		}
		
		$stud_query = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, CAMPUS_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE AS PROGRAM, STUDENT_STATUS, ACT_MEASURE, SCORE, STATE_RANK, NATIONAL_RANK, IF(TEST_DATE = '0000-00-00','',DATE_FORMAT(TEST_DATE,'%Y-%m-%d' )) AS TEST_DATE_1 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		, S_STUDENT_ACT_TEST 
		LEFT JOIN M_ACT_MEASURE ON M_ACT_MEASURE.PK_ACT_MEASURE = S_STUDENT_ACT_TEST.PK_ACT_MEASURE 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_ACT_TEST.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT $cond GROUP BY PK_STUDENT_ACT_TEST ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, ACT_MEASURE ASC";
		
		if($_POST['FORMAT'] == 1) {
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ACT Scores</b></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
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
				'margin_top' => 35,
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
			
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>First Term</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Program</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Status</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Measure</i></b>
								</td>
								<td width="6%" style="border-bottom:1px solid #000;">
									<b><i>Score</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>State Rank</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>National Rank</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Test Date</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td >'.$res->fields['STU_NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['BEGIN_DATE'].'</td>
							<td >'.$res->fields['PROGRAM'].'</td>
							<td >'.$res->fields['STUDENT_STATUS'].'</td>
							<td >'.$res->fields['ACT_MEASURE'].'</td>
							<td >'.$res->fields['SCORE'].'</td>
							<td >'.$res->fields['STATE_RANK'].'</td>
							<td >'.$res->fields['NATIONAL_RANK'].'</td>
							<td >'.$res->fields['TEST_DATE_1'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output("ACT Scores.pdf", 'D');
		} else {
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
			$file_name 		= 'ACT Scores.xlsx';
			$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
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
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Measure';
			$width[]   = 20;
			$heading[] = 'Score';
			$width[]   = 20;
			$heading[] = 'State Rank';
			$width[]   = 20;
			$heading[] = 'National Rank';
			$width[]   = 20;
			$heading[] = 'Test Date';
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
			
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
		
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ACT_MEASURE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SCORE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STATE_RANK']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NATIONAL_RANK']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TEST_DATE_1']);
				
				
				$res->MoveNext();
			}
			

			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 2) {
		//ATB
		if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
			$START_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$END_DATE 	 = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND COMPLETED_DATE BETWEEN '$START_DATE' AND '$END_DATE' ";
		} else if($_REQUEST['START_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$cond .= " AND COMPLETED_DATE >= '$START_DATE' ";
		} else if($_REQUEST['END_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND COMPLETED_DATE <= '$END_DATE' ";
		}
		
		$stud_query = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, CAMPUS_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE AS PROGRAM, STUDENT_STATUS, ATB_CODE, ATB_TEST_CODE, ATB_ADMIN_CODE, IF(COMPLETED_DATE = '0000-00-00','',DATE_FORMAT(COMPLETED_DATE,'%Y-%m-%d' )) AS COMPLETED_DATE_1 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		, S_STUDENT_ATB_TEST 
		LEFT JOIN M_ATB_TEST_CODE ON M_ATB_TEST_CODE.PK_ATB_TEST_CODE = S_STUDENT_ATB_TEST.PK_ATB_TEST_CODE 
		LEFT JOIN M_ATB_CODE ON M_ATB_CODE.PK_ATB_CODE = S_STUDENT_ATB_TEST.PK_ATB_CODE 
		LEFT JOIN M_ATB_ADMIN_CODE ON M_ATB_ADMIN_CODE.PK_ATB_ADMIN_CODE = S_STUDENT_ATB_TEST.PK_ATB_ADMIN_CODE 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_ATB_TEST.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT $cond GROUP BY PK_STUDENT_ATB_TEST ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, ATB_CODE ASC";
		
		if($_POST['FORMAT'] == 1) {
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ATB Tests</b></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
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
				'margin_top' => 35,
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
			
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>First Term</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Program</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Status</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Codes</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Test Codes</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Admin Codes</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Test Date</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td >'.$res->fields['STU_NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['BEGIN_DATE'].'</td>
							<td >'.$res->fields['PROGRAM'].'</td>
							<td >'.$res->fields['STUDENT_STATUS'].'</td>
							<td >'.$res->fields['ATB_CODE'].'</td>
							<td >'.$res->fields['ATB_TEST_CODE'].'</td>
							<td >'.$res->fields['ATB_ADMIN_CODE'].'</td>
							<td >'.$res->fields['COMPLETED_DATE_1'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output("ATB Tests.pdf", 'D');
		} else {
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
			$file_name 		= 'ATB Tests.xlsx';
			$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
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
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Codes';
			$width[]   = 20;
			$heading[] = 'Test Codes';
			$width[]   = 20;
			$heading[] = 'Admin Codes';
			$width[]   = 20;
			$heading[] = 'Test Date';
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
			
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
		
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ATB_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ATB_TEST_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ATB_ADMIN_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPLETED_DATE_1']);
				
				
				$res->MoveNext();
			}
			

			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
		
	} else if($_POST['REPORT_TYPE'] == 3) {
		//SAT
		if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
			$START_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$END_DATE 	 = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND TEST_DATE BETWEEN '$START_DATE' AND '$END_DATE' ";
		} else if($_REQUEST['START_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$cond .= " AND TEST_DATE >= '$START_DATE' ";
		} else if($_REQUEST['END_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND TEST_DATE <= '$END_DATE' ";
		}
		
		$stud_query = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, CAMPUS_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE AS PROGRAM, STUDENT_STATUS, SAT_MEASURE, SCORE, NATIONAL_RANK, USER_RANK, IF(TEST_DATE = '0000-00-00','',DATE_FORMAT(TEST_DATE,'%Y-%m-%d' )) AS TEST_DATE_1 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		, S_STUDENT_SAT_TEST 
		LEFT JOIN M_SAT_MEASURE ON M_SAT_MEASURE.PK_SAT_MEASURE = S_STUDENT_SAT_TEST.PK_SAT_MEASURE 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_SAT_TEST.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT $cond GROUP BY PK_STUDENT_SAT_TEST ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, SAT_MEASURE ASC";
		
		if($_POST['FORMAT'] == 1) {
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>SAT Scores</b></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
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
				'margin_top' => 35,
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
			
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>First Term</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Program</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Status</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Measure</i></b>
								</td>
								<td width="6%" style="border-bottom:1px solid #000;">
									<b><i>Score</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>National Percentile Rank</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>User Percentile Rank</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Test Date</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td >'.$res->fields['STU_NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['BEGIN_DATE'].'</td>
							<td >'.$res->fields['PROGRAM'].'</td>
							<td >'.$res->fields['STUDENT_STATUS'].'</td>
							<td >'.$res->fields['SAT_MEASURE'].'</td>
							<td >'.$res->fields['SCORE'].'</td>
							<td >'.$res->fields['NATIONAL_RANK'].'</td>
							<td >'.$res->fields['USER_RANK'].'</td>
							<td >'.$res->fields['TEST_DATE_1'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output("SAT Scores.pdf", 'D');
		} else {
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
			$file_name 		= 'SAT Scores.xlsx';
			$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
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
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Measure';
			$width[]   = 20;
			$heading[] = 'Score';
			$width[]   = 20;
			$heading[] = 'National Percentile Rank';
			$width[]   = 20;
			$heading[] = 'User Percentile Rank';
			$width[]   = 20;
			$heading[] = 'Test Date';
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
			
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
		
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SAT_MEASURE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SCORE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NATIONAL_RANK']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['USER_RANK']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TEST_DATE_1']);
				
				
				$res->MoveNext();
			}
			

			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 4) {
		//Student test
		
		if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
			$START_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$END_DATE 	 = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND TEST_DATE BETWEEN '$START_DATE' AND '$END_DATE' ";
		} else if($_REQUEST['START_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['START_DATE']));
			$cond .= " AND TEST_DATE >= '$START_DATE' ";
		} else if($_REQUEST['END_DATE'] != '') {
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['END_DATE']));
			$cond .= " AND TEST_DATE <= '$END_DATE' ";
		}
		
		$stud_query = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE AS PROGRAM, STUDENT_STATUS, TEST_LABEL, TEST_RESULT, IF(PASSED = 1, 'Yes', 'No') as PASSED, IF(TEST_DATE = '0000-00-00','',DATE_FORMAT(TEST_DATE,'%Y-%m-%d' )) AS TEST_DATE_1, CAMPUS_CODE 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		, S_STUDENT_TEST     
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_TEST.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT $cond GROUP BY PK_STUDENT_TEST ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, TEST_LABEL ASC";
		
		if($_POST['FORMAT'] == 1) {
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Student Tests</b></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
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
				'margin_top' => 35,
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
			
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>First Term</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Program</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Status</i></b>
								</td>
								<td width="17%" style="border-bottom:1px solid #000;">
									<b><i>Test</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Test Result</i></b>
								</td>
								<td width="5%" style="border-bottom:1px solid #000;">
									<b><i>Passed</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Test Date</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td >'.$res->fields['STU_NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['BEGIN_DATE'].'</td>
							<td >'.$res->fields['PROGRAM'].'</td>
							<td >'.$res->fields['STUDENT_STATUS'].'</td>
							<td >'.$res->fields['TEST_LABEL'].'</td>
							<td >'.$res->fields['TEST_RESULT'].'</td>
							<td >'.$res->fields['PASSED'].'</td>
							<td >'.$res->fields['TEST_DATE_1'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output("Student Tests.pdf", 'D');
		} else {
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
			$file_name 		= 'Student Tests.xlsx';
			$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
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
			$heading[] = 'First Term';
			$width[]   = 20;
			$heading[] = 'Program';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Test';
			$width[]   = 20;
			$heading[] = 'Test Result';
			$width[]   = 20;
			$heading[] = 'Passed';
			$width[]   = 20;
			$heading[] = 'Test Date';
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
			
			$res = $db->Execute($stud_query);			
			while (!$res->EOF) { 
		
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TEST_LABEL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TEST_RESULT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PASSED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TEST_DATE_1']);
				
				
				$res->MoveNext();
			}
			

			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	}
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_STUDENT_TESTS?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_STUDENT_TESTS?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:30px;" >
										<div class="col-md-2 ">
											<b><?=STUDENT_TEST_TYPE?></b>
											<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control"  >
												<option value="1">ACT Scores</option>
												<option value="2">ATB Tests</option>
												<option value="3">SAT Scores</option>
												<option value="4">Student Tests</option>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center"  >
											<div class="custom-control custom-radio col-md-6">
												<input type="radio" id="LEAD" name="STUDENT_TYPE" value="1" class="custom-control-input" onclick="search()" >
												<label class="custom-control-label" for="LEAD"><?=LEAD?></label>
											</div>
											<div class="custom-control custom-radio col-md-6 ">
												<input type="radio" id="STUDENT" name="STUDENT_TYPE" value="2" checked class="custom-control-input" onclick="search()" >
												<label class="custom-control-label" for="STUDENT"><?=STUDENT?></label>
											</div>
										</div>
										
										<div class="col-md-2 align-self-center" >
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" placeholder="<?=TEST_START_DATE?>" >
										</div>
										
										<div class="col-md-2 align-self-center" >
											<input type="text" class="form-control date" id="END_DATE" name="END_DATE" placeholder="<?=TEST_END_DATE?>" >
										</div>
										
									</div>
									
									<div class="row" style="padding-bottom:10px;" >
										
										<div class="col-md-2"  >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<button type="button" onclick="submit_form(1)" id="btn_1" style="display:none;" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="btn_2" style="display:none;" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
										
									</div>
								
									<br />
									<div id="student_div" >
										<? /*$_REQUEST['ENROLLMENT'] = 1;
										$_REQUEST['show_check'] 	= 1;
										$_REQUEST['show_count'] 	= 1;
										require_once('ajax_search_student_for_reports.php'); */ ?>
									</div>
									
                                </form>
                            </div>
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
		var form1 = new Validation('form1');
		function submit_form(val){
			document.getElementById('FORMAT').value = val
			document.form1.submit();
		}
		
		function search(){
			jQuery(document).ready(function($) {
				var LEAD = 1
				if(document.getElementById('LEAD').checked == true)
					LEAD = 1
				else
					LEAD = 0
				
				var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&group_by=1&ENROLLMENT=1&show_check=1&show_count=1&LEAD='+LEAD;
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
		
		function show_btn(){
			
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1) {
				document.getElementById('btn_1').style.display = 'inline';
				document.getElementById('btn_2').style.display = 'inline';
			} else {
				document.getElementById('btn_1').style.display = 'none';
				document.getElementById('btn_2').style.display = 'none';
			}
		}
		
		function get_count(){
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true)
					tot++;
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
	});
	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	</script>
</body>

</html>