<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT COE FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['COE'] == 0 || $res->fields['COE'] == '') {
	header("location:../index");
	exit;
}

if(!empty($_POST)){

	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE BETWEEN '$ST' AND '$ET' ";
		$cond_2 .= " AND SSS.SCHEDULE_DATE BETWEEN '$ST' AND '$ET' ";

	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE >= '$ST' ";
		$cond_2 .= " AND SSS.SCHEDULE_DATE >= '$ST' ";

	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$ET' ";
		$cond_2 .= " AND SSS.SCHEDULE_DATE <= '$ET' ";

	}
	if(!empty($_REQUEST['PK_STUDENT_STATUS'])){
		$not_int_str = implode(',' , $_REQUEST['PK_STUDENT_STATUS']);
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS NOT IN ($not_int_str)";
	}
	
	if(!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (".implode(",",$_REQUEST['PK_CAMPUS_PROGRAM']).") ";

	$query1 = 
	"SELECT 
	CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, 
	M_CAMPUS_PROGRAM.CODE, 
	STUDENT_STATUS, 
	IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER , 
	IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%Y-%m-%d' )) AS LDA, 
	SUM(S_STUDENT_SCHEDULE.HOURS) AS SCHEDULED_HOUR, 
	S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT  
	FROM 
	S_STUDENT_SCHEDULE
    LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE
	LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL 
    LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER= S_STUDENT_MASTER.PK_STUDENT_MASTER
    LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
    LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
	LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE	  
	WHERE 
	S_STUDENT_ATTENDANCE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	PK_SCHEDULE_TYPE = 1 AND 
	S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND
	S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 
	S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 AND 
	M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE IN (
		SELECT M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE 
		from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE 
		WHERE M_ATTENDANCE_CODE.ACTIVE = 1 
		AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE 
		AND S_ATTENDANCE_CODE.ACTIVE = 1 
		AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ) 
	$cond 
	GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT ORDER BY  CONCAT(LAST_NAME,' ',FIRST_NAME) ";
	
	$query2 = 
	"SELECT 
		SUM(ATTENDANCE_HOURS) AS ATTENDANCE_HOURS,

	(
		SELECT
			SUM(ATTENDANCE_HOURS)
		FROM
			S_STUDENT_ATTENDANCE AS SSC
			LEFT JOIN S_STUDENT_SCHEDULE AS SSS ON SSC.PK_STUDENT_SCHEDULE = SSS.PK_STUDENT_SCHEDULE
		WHERE
			 
		   SSC.PK_COURSE_OFFERING_SCHEDULE_DETAIL = 0 
			$cond_2
			AND SSC.PK_ATTENDANCE_CODE != 7
			AND SSC.PK_ATTENDANCE_CODE IN(
			SELECT
				M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE
			FROM
				M_ATTENDANCE_CODE,
				S_ATTENDANCE_CODE
			WHERE
				M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '505' AND PRESENT = 1
		) AND SSS.PK_STUDENT_ENROLLMENT = S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT
	) AS NON_SCHEDULED_ATT_HOURS
	FROM 
	S_STUDENT_SCHEDULE
    LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE
	LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL OR S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL IS NULL
    LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_ATTENDANCE.PK_STUDENT_MASTER= S_STUDENT_MASTER.PK_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
	LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE	  
	WHERE 
	S_STUDENT_ATTENDANCE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	PK_SCHEDULE_TYPE = 1 AND 
	S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL AND
	S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = 1 AND 	
	S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE != 7 AND 
	M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE AND
	M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE IN (
		SELECT M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE 
		from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE 
		WHERE M_ATTENDANCE_CODE.ACTIVE = 1 
		AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE 
		AND S_ATTENDANCE_CODE.ACTIVE = 1 
		AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
		AND PRESENT = 1) 
	$cond ";  
	if($_POST['FORMAT'] == 1) {
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');
		
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
				$this->SetX(138);
				$this->Cell(55, 8, "COE FTE Calculation", 0, false, 'L', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 205, 13, $style);
				
				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " : ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = " from ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = " to ".$_POST['END_DATE'];
					
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Attendance Dates: ".$str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
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
		$pdf->SetMargins(7, 20, 7);
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
							<td width="20%" style="border-bottom:1px solid #000;">
								<b><i>Student</i></b>
							</td>
							<td width="20%" style="border-bottom:1px solid #000;">
								<b><i>Program</i></b>
							</td>
							<td width="20%" style="border-bottom:1px solid #000;">
								<b><i>Status</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Start Date</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>LDA</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Scheduled</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Present</i></b>
							</td>
						</tr>
					</thead>
					<tbody>';
					
					$sno 				= 0;
					$total_attended 	= 0;
					$total_scheduled 	= 0;
					
					$res_schedule = $db->Execute($query1);
					while (!$res_schedule->EOF) {
						$PK_STUDENT_ENROLLMENT = $res_schedule->fields['PK_STUDENT_ENROLLMENT'];
						//if($PK_STUDENT_ENROLLMENT == 2232119){
							// echo $query2." AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT "; exit;
						//}
						$res_attended = $db->Execute($query2." AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT ");
						$sno++;
						$txt .= '<tr>
									<td width="20%" >'.$sno.'. '.$res_schedule->fields['NAME'].'</td>
									<td width="20%" >'.$res_schedule->fields['CODE'].'</td>
									<td width="20%" >'.$res_schedule->fields['STUDENT_STATUS'].'</td>
									<td width="10%" >'.$res_schedule->fields['TERM_MASTER'].'</td>
									<td width="10%" >'.$res_schedule->fields['LDA'].'</td>
									<td width="10%" align="right" >'.number_format_value_checker($res_schedule->fields['SCHEDULED_HOUR'],2).'</td>
									<td width="10%" align="right" >'.number_format_value_checker($res_attended->fields['ATTENDANCE_HOURS']+$res_attended->fields['NON_SCHEDULED_ATT_HOURS'],2).'</td>
								</tr>';
						
						$total_attended  += $res_attended->fields['ATTENDANCE_HOURS']+$res_attended->fields['NON_SCHEDULED_ATT_HOURS'];
						$total_scheduled += $res_schedule->fields['SCHEDULED_HOUR'];
						
						$res_schedule->MoveNext();
					}
		
		$txt .= '</tbody>
			</table>
			<br /><br />';
		
		$str = "";
		if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
			$str = " : ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
		else if($_POST['START_DATE'] != '')
			$str = " from ".$_POST['START_DATE'];
		else if($_POST['END_DATE'] != '')
			$str = " to ".$_POST['END_DATE'];
		$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr>
						<td width="48%" border="1" >
							<table  cellspacing="0" cellpadding="2" width="100%">
								<tr>
									<td width="100%" align="center" ><i>Attendance Dates: '.$str.'</i><br /></td>
								</tr>
								<tr>
									<td width="40%" >Total Present Hours:</td>
									<td width="60%" >'.number_format_value_checker($total_attended,2).'</td>
								</tr>
								<tr>
									<td width="40%" >Total Scheduled Hours:</td>
									<td width="60%" >'.number_format_value_checker($total_scheduled,2).'</td>
								</tr>
								<tr>
									<td width="40%" >Total Students:</td>
									<td width="60%" >'.$sno.'</td>
								</tr>
								<tr>
									<td width="40%" >Calculated: </td>
									<td width="60%" >'.date("m/d/Y h:i A").'</td>
								</tr>
							</table>
						</td>
						
						<td width="2%" >
						</td>
						
						<td width="48%" border="1" >
							<table  cellspacing="0" cellpadding="2" width="100%">
								<tr>
									<td width="100%" align="center" ><i>Total FTE by Scheduled Hours</i><br /></td>
								</tr>
								<tr>
									<td width="40%" >Clock Hours (900):</td>
									<td width="60%" >'.number_format_value_checker(($total_scheduled / 900),2).'</td>
								</tr>
								<tr>
									<td width="40%" >Semester Credit Hours (30):</td>
									<td width="60%" >'.number_format_value_checker(($total_scheduled / 30),2).'</td>
								</tr>
								<tr>
									<td width="40%" >Quarter Credit Hours (45):</td>
									<td width="60%" >'.number_format_value_checker(($total_scheduled / 45),2).'</td>
								</tr>
								<tr>
									<td width="100%" ><br /></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
		
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'COE Attendance Report'.'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
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

		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'COE Attendance Report.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$str = "";
		if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
			$str = " : ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
		else if($_POST['START_DATE'] != '')
			$str = " from ".$_POST['START_DATE'];
		else if($_POST['END_DATE'] != '')
			$str = " to ".$_POST['END_DATE'];
			
		$line 	 = 1;
		$cell_no = $cell[0].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Attendance Dates: ".$str);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);

		$line 	= 2;	
		$index 	= -1;

		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'TermBeginDate';
		$width[]   = 20;
		$heading[] = 'StudentStatus';
		$width[]   = 20;
		$heading[] = 'ProgramCode';
		$width[]   = 20;
		$heading[] = 'EnrollmentLDA';
		$width[]   = 20;
		$heading[] = 'HoursPresent';
		$width[]   = 20;
		$heading[] = 'HoursScheduled';
		$width[]   = 20;

		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A3');

		$res_schedule = $db->Execute($query1);
		while (!$res_schedule->EOF) {
		
			$PK_STUDENT_ENROLLMENT = $res_schedule->fields['PK_STUDENT_ENROLLMENT'];
			$res_attended = $db->Execute($query2." AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT ");
			
			$index = -1;
			$line++;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_schedule->fields['NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_schedule->fields['TERM_MASTER']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_schedule->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_schedule->fields['CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_schedule->fields['LDA']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(($res_attended->fields['ATTENDANCE_HOURS']+$res_attended->fields['NON_SCHEDULED_ATT_HOURS']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_schedule->fields['SCHEDULED_HOUR']);
			
			$res_schedule->MoveNext();
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	}
	
	exit;
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
	<title><?=MNU_FTE_CALCULATION?> | <?=$title?></title>
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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_FTE_CALCULATION?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2 ">
											<?=START_DATE?>
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="" >
										</div>
										<!-- <div class="col-md-4 form-group">
										Status To Exclude
													<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div> -->
										<div class="col-md-4 ">
											<?=PROGRAM?>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control"  >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=RUN?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXPORT_TO_EXCEL?></button>
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
	});
	
	function submit_form(val){
		document.getElementById('FORMAT').value = val
		document.form1.submit();
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});

		$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=STUDENT_STATUS?>',
				nonSelectedText: '<?=STUDENT_STATUS?>',
				numberDisplayed: 1,
				nSelectedText: '<?=STUDENT_STATUS?> selected'
			});
	});
	</script>

</body>

</html>