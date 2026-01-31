<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_attendance_analysis_report.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$cond   = "";
	$fields = "";
	
	if($_POST['DATE_TYPE'] == 1)
		$fields = "DROP_DATE";
	else if($_POST['DATE_TYPE'] == 2)
		$fields = "LDA";
	else if($_POST['DATE_TYPE'] == 3)
		$fields = "DETERMINATION_DATE";
	
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $fields BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND $fields >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $fields <= '$ET' ";
	}
	
	if(!empty($_POST['PK_STUDENT_STATUS'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
	} else {
		$sts = "";
		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
		while (!$res_type->EOF) {
			if($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
		
			$res_type->MoveNext();
		}
		
		if($sts != '')
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS in (".$sts.") ";
	}
	
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}

	$res_prog = $db->Execute("select M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.CODE, M_CAMPUS_PROGRAM.DESCRIPTION  
	from 
	S_STUDENT_MASTER, S_STUDENT_ENROLLMENT  
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	,M_STUDENT_STATUS 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $campus_cond1 
	GROUP BY M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM ORDER BY CODE ASC ");
	while (!$res_prog->EOF) {
		$PK_CAMPUS_PROGRAMS[]   = $res_prog->fields['PK_CAMPUS_PROGRAM'];
		$CAMPUS_PROGRAMS_NAME[] = $res_prog->fields['CODE'].' '.$res_prog->fields['DESCRIPTION'];
		
		$res_prog->MoveNext();
	}

	$query = "select CONCAT(LAST_NAME,', ', FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, STUDENT_ID, STUDENT_STATUS, SESSION, DROP_REASON, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1, IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE, '%Y-%m-%d' )) AS DROP_DATE, IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE, '%Y-%m-%d' )) AS DETERMINATION_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%Y-%m-%d' )) AS LDA, IF(GRADE_DATE = '0000-00-00', '', DATE_FORMAT(GRADE_DATE, '%Y-%m-%d' )) AS GRADE_DATE_1, S_STUDENT_MASTER.PK_STUDENT_MASTER, CAMPUS_CODE, M_CAMPUS_PROGRAM.CODE    
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT  
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	LEFT JOIN M_DROP_REASON ON M_DROP_REASON.PK_DROP_REASON = S_STUDENT_ENROLLMENT.PK_DROP_REASON 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	,M_STUDENT_STATUS 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $campus_cond1 ";
	
	$group_by = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
	
	//echo $query.$group_by.$order_by;exit;

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
				global $db, $campus_cond;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 14);
				$this->SetY(9);
				$this->SetX(178);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Drop Report", 0, false, 'L', 0, '', 0, false, 'M', 'R');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 205, 13, $style);

				$str = date("l, F d, Y", strtotime($_POST['AS_OF_DATE']));
				
				if($_POST['DATE_TYPE'] == 1)
					$str = "Drop Dates ";
				else if($_POST['DATE_TYPE'] == 2)
					$str = "LDA Dates ";
				else if($_POST['DATE_TYPE'] == 3)
					$str = "Determination Dates ";
				
				$str .= $_POST['START_DATE']." to ".$_POST['END_DATE'];
						
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(15);
				$this->SetX(103);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				global $query, $group_by, $status_lbl;
				$res_stud = $db->Execute($query." ".$group_by);
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(19);
				$this->SetX(103);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Report Student Count: ".$res_stud->RecordCount(), 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
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
				$this->SetY(21);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				
				$str = "";
				if(empty($_POST['PK_STUDENT_STATUS'])) {
					$str = "All Statuses";
				} else {
					$str = "";
					$res_type_all = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 ");
					
					$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") order by STUDENT_STATUS ASC");
				
					if($res_type_all->RecordCount() == $res_type->RecordCount())
						$str = "All Statuses";
					else {
						while (!$res_type->EOF) {
							if($str != '')
								$str .= ', ';
							$str .= $res_type->fields['STUDENT_STATUS'];
							$res_type->MoveNext();
						}
						if($str != '')
							$str = "Student Status: ".$str;
					}
					
				}
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(27);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(150, 5, $str, 0, 'R', 0, 0, '', '', true);
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
		$pdf->SetMargins(7, 38, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';

		$sub_total = 0;
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student</td>
							<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Student ID</td>
							<td width="11%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Mobile Phone</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Status</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Session</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />First Term</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />LDA</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br />Drop Date</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Determination Date</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Drop Reason</td>
						</tr>
					</thead>';

		foreach($PK_CAMPUS_PROGRAMS as $key => $PK_CAMPUS_PROGRAMS_1){
			$cond = "";
			if($PK_CAMPUS_PROGRAMS_1 != -1 ) {
				$txt .= '<tr>
						<td width="10%" >
							<b style="font-size:40px"><i>Program:</i></b>
						</td>
						<td width="90%" >
							<i style="font-size:40px">'.$CAMPUS_PROGRAMS_NAME[$key].'</i>
						</td>
					</tr>';
					
				$cond = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAMS_1' ";
			}
			//echo $query." ".$cond." ".$order_by."<br /><br />";	

			$res_stud = $db->Execute($query." ".$cond." ".$group_by." ".$order_by);
			while (!$res_stud->EOF) { 
				
				$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];
				$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	
				
				$CELL_PHONE = preg_replace( '/[^0-9]/', '',$res_address->fields['CELL_PHONE']);
				if($CELL_PHONE != '')
					$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
				
				$txt 	.= '<tr>
							<td width="20%" >'.$res_stud->fields['NAME'].'</td>
							<td width="13%" >'.$res_stud->fields['STUDENT_ID'].'</td>
							<td width="11%" >'.$CELL_PHONE.'</td>
							<td width="10%" >'.$res_stud->fields['STUDENT_STATUS'].'</td>
							<td width="8%" >'.$res_stud->fields['SESSION'].'</td>
							<td width="8%" >'.$res_stud->fields['BEGIN_DATE_1'].'</td>
							<td width="8%" >'.$res_stud->fields['LDA'].'</td>
							<td width="8%" >'.$res_stud->fields['DROP_DATE'].'</td>
							<td width="10%" >'.$res_stud->fields['DETERMINATION_DATE'].'</td>
							<td width="8%" >'.$res_stud->fields['DROP_REASON'].'</td>
						</tr>';
				
				$res_stud->MoveNext();
			}
		}
		
		$txt 	.= '</table>';

		//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Drop Report.pdf';
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
		$file_name 		= 'Drop Report.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line = 1;
		$index 	= -1;
		$heading[] = 'Student';
		$width[]   = 30;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Home Phone';
		$width[]   = 20;
		$heading[] = 'Mobile Phone';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Session';
		$width[]   = 20;
		
		$heading[] = 'LDA';
		$width[]   = 20;
		$heading[] = 'Drop Date';
		$width[]   = 20;
		
		$heading[] = 'Determination Date';
		$width[]   = 20;
		$heading[] = 'Drop Reason';
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

		$res_stud = $db->Execute($query." ".$cond." ".$group_by." ".$order_by);
		while (!$res_stud->EOF) { 
			
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];
			$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	
			
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['BEGIN_DATE_1']);
			
			$HOME_PHONE = preg_replace( '/[^0-9]/', '',$res_address->fields['HOME_PHONE']);
			if($HOME_PHONE != '')
				$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($HOME_PHONE);
			
			$CELL_PHONE = preg_replace( '/[^0-9]/', '',$res_address->fields['CELL_PHONE']);
			if($CELL_PHONE != '')
				$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($CELL_PHONE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['SESSION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['LDA']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['DROP_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['DETERMINATION_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['DROP_REASON']);

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
	<title><?=MNU_DROP_REPORT ?> | <?=$title?></title>
	<style>
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 35px;width: 140px}
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
							<?=MNU_DROP_REPORT ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row form-group">
										<div class="col-md-2">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control"  >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>

									<div class="row form-group">
										<div class="col-md-2 ">
											<?=DATE_TYPE ?>
											<select id="DATE_TYPE" name="DATE_TYPE"  class="form-control" >
												<option value="1">Drop</option>
												<option value="2">LDA</option>
												<option value="3">Determination</option>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
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
	});
	
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=ALL_STATUSES?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
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