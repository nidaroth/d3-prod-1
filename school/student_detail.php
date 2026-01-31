<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/placement_student_status_report.php");
require_once("check_access.php");

if(check_access('REPORT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	
	$cond  = "";
	$field = "";
	
	$date_type_lbl = "";
	if($_POST['DATE_TYPE'] == 1) {
		$field = " GRADE_DATE ";
		$date_type_lbl = "By Grad Date";
	} else if($_POST['DATE_TYPE'] == 2) {
		$field = " S_TERM_MASTER.BEGIN_DATE ";	
		$date_type_lbl = "By First Term Start Date";
	} else if($_POST['DATE_TYPE'] == 3) {
		$field = " S_STUDENT_JOB.START_DATE ";	
		$date_type_lbl = "By Job Start Date";
	}
	
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $field BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND $field >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $field <= '$ET' ";
	}
	
	if($_POST['JOB_TYPE'] == 2){
		$cond .= " AND CURRENT_JOB = 1 ";
	}
	
	if(!empty($_POST['PK_PLACEMENT_STATUS'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS in (".implode(",",$_POST['PK_PLACEMENT_STATUS']).") ";
	}
	
	if($_POST['PK_CAMPUS'] != '') {
		$PK_CAMPUS = implode(",",$_POST['PK_CAMPUS']);
		$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$query = "SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as NAME, IF(GRADE_DATE = '0000-00-00','', DATE_FORMAT(GRADE_DATE, '%Y-%m-%d' )) AS GRADE_DATE, PK_STUDENT_JOB, STUDENT_STATUS, IF(PK_FULL_PART_TIME = 1,'Full Time', IF(PK_FULL_PART_TIME = 2,'Part Time','')) AS FULL_PART_TIME, IF(CURRENT_JOB = 1,'Yes','No') as CURRENT_JOB, JOB_TITLE,PAY_AMOUNT,PAY_TYPE, IF(START_DATE = '0000-00-00','', DATE_FORMAT(START_DATE, '%Y-%m-%d' )) AS START_DATE_1, SUPERVISOR, IF(DOCUMENTED = '0000-00-00','', DATE_FORMAT(DOCUMENTED, '%Y-%m-%d' )) AS DOCUMENTED, WEEKLY_HOURS, S_STUDENT_JOB.COMPANY_PHONE, S_STUDENT_JOB.ADDRESS, S_STUDENT_JOB.ADDRESS_1, S_STUDENT_JOB.CITY, S_STUDENT_JOB.ZIP, STATE_CODE,COMPANY_NAME,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, CAMPUS_CODE, M_CAMPUS_PROGRAM.CODE, PLACEMENT_STATUS, STUDENT_ID, IF(VERIFICATION_DATE = '0000-00-00','',DATE_FORMAT(VERIFICATION_DATE, '%Y-%m-%d' )) AS  VERIFICATION_DATE, CONCAT(VERIFICATION_SOURCE, ' - ', M_PLACEMENT_VERIFICATION_SOURCE.DESCRIPTION) as VERIFICATION_SOURCE    
	FROM 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS  
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
	LEFT JOIN S_STUDENT_JOB ON S_STUDENT_JOB.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN M_PLACEMENT_VERIFICATION_SOURCE ON M_PLACEMENT_VERIFICATION_SOURCE.PK_PLACEMENT_VERIFICATION_SOURCE = S_STUDENT_JOB.PK_PLACEMENT_VERIFICATION_SOURCE 
	LEFT JOIN S_COMPANY ON S_COMPANY.PK_COMPANY = S_STUDENT_JOB.PK_COMPANY 
	LEFT JOIN M_PAY_TYPE ON M_PAY_TYPE.PK_PAY_TYPE = S_STUDENT_JOB.PK_PAY_TYPE 
	LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_JOB.PK_STATES 
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER  AND 
	M_STUDENT_STATUS.ADMISSIONS = 0 
	$cond ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
		
	if($_POST['FORMAT'] == 1){

		/////////////////////////////////////////////////////////////////
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
				global $db, $date_type_lbl;

				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 13);
				$this->SetY(3);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 15);
				$this->SetY(8);
				$this->SetX(142);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Placement Student Detail", 0, false, 'L', 0, '', 0, false, 'M', 'L');
							
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(110, 11, 202, 11, $style);
				
				$str = "";
				if(empty($_POST['PK_PLACEMENT_STATUS'])) {
					$str .= "All Students ";
				} else {
					$res_type = $db->Execute("select PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'AND PK_PLACEMENT_STATUS IN (".implode(",",$_POST['PK_PLACEMENT_STATUS']).") order by PLACEMENT_STATUS ASC");
					while (!$res_type->EOF) {
						if($str != '')
							$str .= ',';
						$str .= $res_type->fields['PLACEMENT_STATUS'];
						$res_type->MoveNext();
					}
				}
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(11);
				$this->SetX(70);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(132, 5, $str, 0, 'R', 0, 0, '', '', true);
				
				if($_POST['JOB_TYPE'] == 1)
					$str = " All Jobs ";
				else if($_POST['JOB_TYPE'] == 1)
					$str = " Current Job ";
					
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(29);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(102, 5, "Job Type: ".$str, 0, 'R', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(32);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(102, 5, "Date Type: ".$date_type_lbl, 0, 'R', 0, 0, '', '', true);
				
				$str = '';
				if($_POST['START_DATE'] != '' || $_POST['END_DATE'] != '') {
				
					if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
						$str .= " Between ".$_POST['START_DATE']." and ".$_POST['END_DATE'];
					} else if($_POST['START_DATE'] != ''){
						$str .= " From ".$_POST['START_DATE'];
					} else if($_POST['END_DATE'] != ''){
						$str .= " To ".$_POST['END_DATE'];
					}
				}
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(36);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(102, 5, $str, 0, 'R', 0, 0, '', '', true);
				
				$campus_name  = "";
				$campus_cond  = "";
				$campus_cond1 = "";
				$campus_id	  = "";
				if(!empty($_POST['PK_CAMPUS'])){
					$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
					$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
					$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
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
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(40);
				$this->SetX(70);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(132, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);

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
		$pdf->SetMargins(7, 44, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$txt 	= '<table border="1" cellspacing="0" cellpadding="0" width="100%" >';
		$res_stud = $db->Execute($query);
		while (!$res_stud->EOF) { 
			$PK_STUDENT_ENROLLMENT 	= $res_stud->fields['PK_STUDENT_ENROLLMENT'];
			$PK_STUDENT_MASTER 		= $res_stud->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_JOB 		= $res_stud->fields['PK_STUDENT_JOB'];
			
			$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
			
			$txt .= '<tr>
						<td width="40%" >
							<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								<tr>
									<td style="width:47%" ><b>Student</b></td>
									<td style="width:25%" ><b>Phone</b></td>
									<td style="width:28%" >'.$res_add->fields['HOME_PHONE'].'</td>
								</tr>
								<tr>
									<td  >'.$res_stud->fields['NAME'].'</td>
									<td ><b>Grad Date</b></td>
									<td >'.$res_stud->fields['GRADE_DATE'].'</td>
								</tr>
								<tr>
									<td >'.$res_add->fields['ADDRESS'].'</td>
									<td ><b>Campus</b></td>
									<td >'.$res_stud->fields['CAMPUS_CODE'].'</td>
								</tr>
								<tr>
									<td >'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'</td>
									<td ><b>Program</b></td>
									<td >'.$res_stud->fields['CODE'].'</td>
								</tr>
								<tr>
									<td ></td>
									<td ><b>Status</b></td>
									<td >'.$res_stud->fields['STUDENT_STATUS'].'</td>
								</tr>
								<tr>
									<td ></td>
									<td ><b>Placement Sts</b></td>
									<td >'.$res_stud->fields['PLACEMENT_STATUS'].'</td>
								</tr>
							</table>
						</td>
						<td width="60%" >
							<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								<tr>
									<td style="width:39.5%" ><b>Company</b></td>
									<td style="width:15.5%" ><b>Enrollment:</b></td>
									<td style="width:17%" >'.$res_stud->fields['BEGIN_DATE_1'].'</td>
									<td style="width:17%" ><b>Current Job?</b></td>
									<td style="width:11%" >'.$res_stud->fields['CURRENT_JOB'].'</td>
								</tr>
								<tr>
									<td style="width:39.5%" >'.$res_stud->fields['COMPANY_NAME'].'</td>
									<td style="width:15.5%" ><b>Job Title:</b></td>
									<td style="width:17%" >'.$res_stud->fields['JOB_TITLE'].'</td>
									<td style="width:17%" ><b>Pay Amt:</b></td>
									<td style="width:11%" >$ '.number_format_value_checker($res_stud->fields['PAY_AMOUNT'],2).'</td>
								</tr>
								<tr>
									<td style="width:39.5%" >'.$res_stud->fields['ADDRESS'].' '.$res_stud->fields['ADDRESS_1'].'</td>
									<td style="width:15.5%" ><b>Start Date:</b></td>
									<td style="width:17%" >'.$res_stud->fields['START_DATE_1'].'</td>
									<td style="width:17%" ><b>Pay Type:</b></td>
									<td style="width:11%" >'.$res_stud->fields['PAY_TYPE'].'</td>
								</tr>
								<tr>
									<td style="width:39.5%" >'.$res_stud->fields['CITY'].', '.$res_stud->fields['STATE_CODE'].' '.$res_stud->fields['ZIP'].'</td>
									<td style="width:15.5%" ><b>Supervisor:</b></td>
									<td style="width:17%" >'.$res_stud->fields['SUPERVISOR'].'</td>
									<td style="width:17%" ><b>Full/Part Time:</b></td>
									<td style="width:11%" >'.$res_stud->fields['FULL_PART_TIME'].'</td>
								</tr>
								<tr>
									<td style="width:39.5%" >'.$res_stud->fields['COMPANY_PHONE'].'</td>
									<td style="width:15.5%" ><b>Documented:</b></td>
									<td style="width:17%" >'.$res_stud->fields['DOCUMENTED'].'</td>
									<td style="width:17%" ><b>Weekly Hours:</b></td>
									<td style="width:11%" >'.$res_stud->fields['WEEKLY_HOURS'].'</td>
								</tr>
							</table>
						</td>
					</tr>';
			
			$res_stud->MoveNext();
		}
		
		$txt .= '</table>';
		
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Placement Student Detail.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
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
		$file_name 		= 'Placement Student Detail.xlsx';
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

		$heading[] = 'Student Name';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Address';
		$width[]   = 30;
		$heading[] = 'City';
		$width[]   = 20;
		$heading[] = 'State';
		$width[]   = 20;
		$heading[] = 'Zip';
		$width[]   = 20;
		$heading[] = 'Phone';
		$width[]   = 20;
		$heading[] = 'First Term Date';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Placement Status';
		$width[]   = 20;
		$heading[] = 'Grad Date';
		$width[]   = 20;
		$heading[] = 'Company';
		$width[]   = 20;
		$heading[] = 'Company Address';
		$width[]   = 20;
		$heading[] = 'Company City';
		$width[]   = 20;
		$heading[] = 'Company State';
		$width[]   = 20;
		$heading[] = 'Company Zip';
		$width[]   = 20;
		$heading[] = 'Company Phone';
		$width[]   = 20;
		$heading[] = 'Enrollment:';
		$width[]   = 20;
		$heading[] = 'Current Job?';
		$width[]   = 20;
		$heading[] = 'Job Title:';
		$width[]   = 20;
		$heading[] = 'Start Date';
		$width[]   = 20;
		$heading[] = 'Supervisor';
		$width[]   = 20;
		$heading[] = 'Documented';
		$width[]   = 20;
		$heading[] = 'Pay Amt';
		$width[]   = 20;
		$heading[] = 'Pay Type';
		$width[]   = 20;
		$heading[] = 'Full/Part Time';
		$width[]   = 20;
		$heading[] = 'Weekly Hours';
		$width[]   = 20;
		$heading[] = 'Verification Source';
		$width[]   = 20;
		$heading[] = 'Verification Date';
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
		
		$res_stud = $db->Execute($query);
		while (!$res_stud->EOF) { 
			$PK_STUDENT_ENROLLMENT 	= $res_stud->fields['PK_STUDENT_ENROLLMENT'];
			$PK_STUDENT_MASTER 		= $res_stud->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_JOB 		= $res_stud->fields['PK_STUDENT_JOB'];
			
			$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
			
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_ID']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CAMPUS_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_add->fields['ADDRESS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_add->fields['CITY']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_add->fields['STATE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_add->fields['ZIP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_add->fields['HOME_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['BEGIN_DATE_1']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['PLACEMENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['GRADE_DATE']);
						
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['COMPANY_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['ADDRESS'].' '.$res_stud->fields['ADDRESS_1']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CITY']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STATE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['ZIP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['COMPANY_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['BEGIN_DATE_1']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CURRENT_JOB']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['JOB_TITLE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['START_DATE_1']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['SUPERVISOR']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['DOCUMENTED']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_stud->fields['PAY_AMOUNT'],2));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['PAY_TYPE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['FULL_PART_TIME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['WEEKLY_HOURS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['VERIFICATION_SOURCE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['VERIFICATION_DATE']);

			$res_stud->MoveNext();
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
	<title><?=PLACEMENT_STUDENT_STATUS_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_PLACEMENT_STATUS {position: absolute;top: 55px;width: 142px}
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
							<?=PLACEMENT_STUDENT_STATUS_TITLE?>
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
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?>  ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-3">
											<?=PLACEMENT_STATUS?>
											<select id="PK_PLACEMENT_STATUS" name="PK_PLACEMENT_STATUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" ><?=$res_type->fields['PLACEMENT_STATUS']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=JOB_TYPE?>
											<select id="JOB_TYPE" name="JOB_TYPE" class="form-control" >
												<option value="1">All Jobs</option>
												<option value="2">Current Job</option>
											</select>
										</div>
										<div class="col-md-2">
											<?=DATE_TYPE?>
											<select id="DATE_TYPE" name="DATE_TYPE" class="form-control" >
												<option value="1">By Grad Date</option>
												<option value="2">By First Term Start Date</option>
												<option value="3">By Job Start Date</option>
											</select>
										</div>
										<div class="col-md-1">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-1">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>

</body>

</html>