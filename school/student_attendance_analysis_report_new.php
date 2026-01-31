<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_attendance_analysis_report.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST) || !empty($_GET)){
	//echo "<pre>";print_r($_POST);exit;
	
	$cond = "";
	if($_POST['START_DATE'] != '')
		$cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_POST['START_DATE']))."' ";
	if($_POST['SELECT_ENROLLMENT'] == 2)
		$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		
	if($_GET['id'] != ''){
		$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '".$_GET['id']."' ";
		
		if($_GET['date'] != '')
			$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";
		
		if($_GET['eid'] != ''){
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		} else {
			if($_GET['type'] == 2)
				$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		}
	}
	
	$query = "select CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_ID, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE, SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS, SUM(S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS) as SCHEDULED_HOURS, M_CAMPUS_PROGRAM.HOURS, PK_STUDENT_COURSE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER, STUDENT_STATUS   
	FROM 
	S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
	LEFT JOIN S_TERM_MASTER ON S_STUDENT_ENROLLMENT.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	,S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE 
	LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE  AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 14 $cond 
	GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC ";
	
	if($_POST['FORMAT'] == 1 || !empty($_GET)){ 
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
			
		class MYPDF extends TCPDF {
			public function Header() {
				global $db;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(6);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				/*$this->SetFont('helvetica', '', 8);
				$this->SetY(13);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(17);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8,$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(21);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');*/
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(9);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(150);
				$this->Cell(55, 8, "Attendance Analysis", 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(125, 13, 205, 13, $style);
			
				$START_DATE = '';
				if($_POST['START_DATE'] != '' )
					$START_DATE = $_POST['START_DATE'];
				else
					$START_DATE = $_GET['date'];
				
				if($START_DATE != '') {
					$this->SetFont('helvetica', 'I', 13);
					$this->SetY(16);
					$this->SetTextColor(000, 000, 000);
					$this->SetX(150);
					$this->Cell(55, 7, "As of ".$START_DATE, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				}
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
		$pdf->SetFont('helvetica', '', 8, '', true);
		$pdf->AddPage();

		$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<thead>
						<tr>
							<td width="58%" >
								&nbsp;
							</td>
							<td width="8%" align="center" style="border-right:1px solid #000;" >
								<b><i>Program</i></b>
							</td>
							<td width="12%" align="center" style="border-right:1px solid #000;" >
								<b><i>Non Scheduled</i></b>
							</td>
							<td width="24%" align="center" >
								<b><i>Scheduled Attendance</i></b>
							</td>
						</tr>
						<tr>
							<td width="16%" style="border-bottom:1px solid #000;">
								<b><i>Student</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Student ID</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Program</i></b>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;">
								<b><i>First Term Date</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Status</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="center" >
								<b><i>Hours</i></b>
							</td>
							<td width="12%" style="border-bottom:1px solid #000;border-right:1px solid #000;" align="center" >
								<b><i>Attendance</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Attended</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Scheduled</i></b>
							</td>
							<td width="9%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Percentage</i></b>
							</td>
						</tr>
					</thead>';

				$res = $db->Execute($query);
				while (!$res->EOF) { 
						$PK_STUDENT_COURSE 		= $res->fields['PK_STUDENT_COURSE'];
						$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
						
						$cond1 = "";
						if($_GET['date'] != '')
							$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";

						$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 14 AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $cond1 ");
						
						$res_s = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_STUDENT_ATTENDANCE.COMPLETED = 1 $cond1 AND PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 ");
						
						//$res_ns = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $cond1 AND PK_SCHEDULE_TYPE = 2 ");
						
						$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS'];
						$txt .= '<tr>
								<td width="16%" >'.$res->fields['STU_NAME'].'</td>
								<td width="10%" >'.$res->fields['STUDENT_ID'].'</td>
								<td width="10%" >'.$res->fields['PROGRAM_TRANSCRIPT_CODE'].'</td>
								
								<td width="12%" >'.$res->fields['TERM_MASTER'].'</td>
								<td width="10%" >'.$res->fields['STUDENT_STATUS'].'</td>
								
								<td width="8%" style="border-right:1px solid #000;" align="right" >'.number_format_value_checker($res->fields['HOURS'],2).'</td>
								<td width="12%" style="border-right:1px solid #000;" align="right" >'.number_format_value_checker($res_ns->fields['ATTENDANCE_HOURS'],2).'</td>
								<td width="7%" align="right" >'.number_format_value_checker($res->fields['ATTENDANCE_HOURS'],2).'</td>
								<td width="8%" align="right" >'.number_format_value_checker($SCHEDULED_HOURS,2).'</td>
								<td width="9%" align="right" >'.number_format_value_checker((($res->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']) / $SCHEDULED_HOURS * 100),2).'%</td>
							</tr>';
						
					$res->MoveNext();
				}
				
				$txt .= '</table>';
				
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Attendance Analysis.pdf';
		/*
		if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');
		*/
		$pdf->Output('temp/'.$file_name, 'FD');

		return $file_name;
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
		$file_name 		= 'Attendance Analysis.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$line 	= 1;
		$cell_no = 'H1';
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Scheduled Attendance');
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = 'H1:J1';
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);
		
		
		$line++;
		$index 	= -1;
		$heading[] = 'Student';
		$width[]   = 30;
		$heading[] = 'Student ID';
		$width[]   = 15;
		$heading[] = 'Program';
		$width[]   = 15;
		$heading[] = 'First Term Date';
		$width[]   = 15;
		$heading[] = 'Status';
		$width[]   = 15;
		$heading[] = 'Program Hours';
		$width[]   = 15;
		$heading[] = 'Non Scheduled Attendance';
		$width[]   = 15;
		$heading[] = 'Attended';
		$width[]   = 15;
		$heading[] = 'Scheduled';
		$width[]   = 15;
		$heading[] = 'Percentage';
		$width[]   = 15;
		
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
		
		$res = $db->Execute($query);
		while (!$res->EOF) { 
			$PK_STUDENT_COURSE 		= $res->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
			
			//$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 14 AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE ");
			
			$cond1 = "";
			if($_GET['date'] != '')
				$cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '".date("Y-m-d",strtotime($_GET['date']))."' ";
				
			$res_ns = $db->Execute("SELECT SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_SCHEDULE_TYPE = 2 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = 14 AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE $cond1 ");

			$res_s = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND S_STUDENT_ATTENDANCE.COMPLETED = 1 $cond1 AND PK_SCHEDULE_TYPE = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 ");
			
			//$res_ns = $db->Execute("SELECT SUM(S_STUDENT_SCHEDULE.HOURS) as SCHEDULED_HOURS FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  $cond1 AND PK_SCHEDULE_TYPE = 2 ");
			
			$SCHEDULED_HOURS = $res_s->fields['SCHEDULED_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS'];
			
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_TRANSCRIPT_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TERM_MASTER']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res->fields['HOURS'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_ns->fields['ATTENDANCE_HOURS'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res->fields['ATTENDANCE_HOURS'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($SCHEDULED_HOURS,2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue((($res->fields['ATTENDANCE_HOURS'] + $res_ns->fields['ATTENDANCE_HOURS']) / $SCHEDULED_HOURS));
			$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

			$res->MoveNext();
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
	<title><?=MNU_STUDENT_ATTENDANCE_ANALYSIS_REPORT?> | <?=$title?></title>
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
							<?=MNU_STUDENT_ATTENDANCE_ANALYSIS_REPORT?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											<?=SELECT_ENROLLMENT?>
											<select id="SELECT_ENROLLMENT" name="SELECT_ENROLLMENT" class="form-control" >
												<option value="1" >All Enrollments</option>
												<option value="2" >Current Enrollments</option>
											</select>
										</div>
										<div class="col-md-2">
											<?=AS_OF_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
										</div>
										
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<!-- New -->
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		search();
	});
	
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
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