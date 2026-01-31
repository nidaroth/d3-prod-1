<?
ini_set("pcre.backtrack_limit", "5000000");

require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/activity_report.php");
require_once("../language/menu.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	}
	
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	/* Ticket # 1758 */
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
	/* Ticket # 1758 */
	
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
	
	if($_POST['FORMAT'] == 1){
		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
		
		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			
		$report_name = "";
		if($_POST['REPORT_TYPE'] == 1) {
			$report_name = "Activity Type: Email";
		} else if($_POST['REPORT_TYPE'] == 2) {
			$report_name = "Activity Type: Events";
		} else if($_POST['REPORT_TYPE'] == 3) {
			$report_name = "Activity Type: Internal Messages";
		} else if($_POST['REPORT_TYPE'] == 4) {
			$report_name = "Activity Type: LOA";
		} else if($_POST['REPORT_TYPE'] == 5) {
			$report_name = "Activity Type: Notes";
		} else if($_POST['REPORT_TYPE'] == 6) {
			$report_name = "Activity Type: Probation";
		} else if($_POST['REPORT_TYPE'] == 7) {
			$report_name = "Activity Type: Tasks";
		} else if($_POST['REPORT_TYPE'] == 8) {
			$report_name = "Activity Type: Texts";
		}
		
		/* Ticket # 1752  */
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="50%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="30%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Activity Report</b></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >'.$report_name.'</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Between: '.$_POST['START_DATE']." - ".$_POST['END_DATE'].'</td>
						</tr>
					</table>';
		/* Ticket # 1752  */
		
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
		
		$file_name = "Activity Report - ";
		if($_POST['REPORT_TYPE'] == 1)
			$file_name .= "Email.pdf";
		else if($_POST['REPORT_TYPE'] == 2)
			$file_name .= "Events.pdf";
		else if($_POST['REPORT_TYPE'] == 3)
			$file_name .= "Internal Messages.pdf";
		else if($_POST['REPORT_TYPE'] == 4)
			$file_name .= "LOA.pdf";
		else if($_POST['REPORT_TYPE'] == 5)
			$file_name .= "Notes.pdf";
		else if($_POST['REPORT_TYPE'] == 6)
			$file_name .= "Probation.pdf";
		else if($_POST['REPORT_TYPE'] == 7)
			$file_name .= "Tasks.pdf";
		else if($_POST['REPORT_TYPE'] == 8)
			$file_name .= "Texts.pdf";
		
	} else {
		$file_name = "Activity Report - ";
		if($_POST['REPORT_TYPE'] == 1)
			$file_name .= "Email.xlsx";
		else if($_POST['REPORT_TYPE'] == 2)
			$file_name .= "Events.xlsx";
		else if($_POST['REPORT_TYPE'] == 3)
			$file_name .= "Internal Messages.xlsx";
		else if($_POST['REPORT_TYPE'] == 4)
			$file_name .= "LOA.xlsx";
		else if($_POST['REPORT_TYPE'] == 5)
			$file_name .= "Notes.xlsx";
		else if($_POST['REPORT_TYPE'] == 6)
			$file_name .= "Probation.xlsx";
		else if($_POST['REPORT_TYPE'] == 7)
			$file_name .= "Tasks.xlsx";
		else if($_POST['REPORT_TYPE'] == 8)
			$file_name .= "Texts.xlsx";
					
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
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->setPreCalculateFormulas(false);
		
		$cell_no = 'A1';
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($campus_name);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	}
	
	if($_POST['REPORT_TYPE'] == 1){
		$cond .= " AND SENT_ON BETWEEN '$ST' AND '$ET' ";
		
		$query = "select PK_EMAIL_LOG, CONCAT(LAST_NAME,', ',FIRST_NAME,' ', MIDDLE_NAME) as NAME, STUDENT_ID, SUBJECT, SENT_ON, EMAIL_ID, MAIL_CONTENT, CAMPUS_CODE FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_EMAIL_LOG, S_STUDENT_CAMPUS, S_CAMPUS  
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_EMAIL_LOG.PK_STUDENT_MASTER AND 
		S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND 
		S_EMAIL_LOG.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY PK_EMAIL_LOG ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC, SENT_ON ASC";
		
		if($_POST['FORMAT'] == 1){
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
									<b><i>Sent On</i></b>
								</td>
								<td width="15%" style="border-bottom:1px solid #000;">
									<b><i>To Email Address</i></b>
								</td>
								<td width="20%" style="border-bottom:1px solid #000;">
									<b><i>Subject</i></b>
								</td>
								<td width="34%" style="border-bottom:1px solid #000;">
									<b><i>Content</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
				$date = convert_to_user_date($res->fields['SENT_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
				
				$txt .= '<tr>
							<td >'.$res->fields['NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$date.'</td>
							<td >'.$res->fields['EMAIL_ID'].'</td>
							<td >'.$res->fields['SUBJECT'].'</td>
							<td >'.strip_tags(nl2br($res->fields['MAIL_CONTENT'])).'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'Student ID';
			$width[]   = 15;
			$heading[] = 'Sent On';
			$width[]   = 20;
			$heading[] = 'To Email Address';
			$width[]   = 30;
			$heading[] = 'Subject';
			$width[]   = 40;
			$heading[] = 'Content';
			$width[]   = 50;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				
				$i++;
			}

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				$date = convert_to_user_date($res->fields['SENT_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get()); //Ticket # 1755 

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($date);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SUBJECT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(strip_tags($res->fields['MAIL_CONTENT']));
				
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 2){
		$PK_STUDENT_MASTER_ARR = array();
		foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
			$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
		}
		$PK_STUDENT_MASTER = implode(",", $PK_STUDENT_MASTER_ARR);
		
		$query = $_SESSION['ACTIVTY_QUERY']." AND S_STUDENT_NOTES.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) GROUP BY PK_STUDENT_NOTES ".$_SESSION['ACTIVTY_QUERY_ORDER'];
		//echo $query;exit;
		
		if($_POST['FORMAT'] == 1){
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="15%" style="border-bottom:1px solid #000;">
									<b><i>Enrollment</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Event Date</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Event Time</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Event Type</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Event Status</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Employee</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Completed</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
			
				$NOTE_TIME = '';
				if($res->fields['NOTE_TIME'] != '00-00-00' && $res->fields['NOTE_DATE_1'] != '') 
					$NOTE_TIME = date("h:i A", strtotime($res->fields['NOTE_TIME']));
					
				$FOLLOWUP_TIME = '';
				if($res->fields['FOLLOWUP_TIME'] != '00-00-00' && $res->fields['FOLLOWUP_DATE_1'] != '') 
					$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));
				
				$txt .= '<tr>
							<td >'.$res->fields['NAME'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['NOTE_DATE_1'].'</td>
							<td >'.$NOTE_TIME.'</td>
							<td >'.$res->fields['NOTE_TYPE'].'</td>
							<td >'.$res->fields['NOTE_STATUS'].'</td>
							<td >'.$res->fields['EMP_NAME'].'</td>
							<td >'.$res->fields['COMPLETED'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'Student ID';
			$width[]   = 15;
			$heading[] = 'Enrollment';
			$width[]   = 15;
			$heading[] = 'Event Date';
			$width[]   = 15;
			$heading[] = 'Event Time';
			$width[]   = 15;
			$heading[] = 'Event Type';
			$width[]   = 15;
			$heading[] = 'Event Status';
			$width[]   = 15;
			$heading[] = 'Event Other';
			$width[]   = 15;
			$heading[] = 'Company';
			$width[]   = 15;
			$heading[] = 'Follow Up Date';
			$width[]   = 15;
			$heading[] = 'Follow Up Time';
			$width[]   = 15;
			$heading[] = 'Employee';
			$width[]   = 15;
			$heading[] = 'Created By';
			$width[]   = 15;
			$heading[] = 'Home Phone';
			$width[]   = 15;
			$heading[] = 'Mobile Phone';
			$width[]   = 15;
			$heading[] = 'Email';
			$width[]   = 15;
			$heading[] = 'Completed';
			$width[]   = 15;
			$heading[] = 'Comments';
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

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				$NOTE_TIME = '';
				if($res->fields['NOTE_TIME'] != '00-00-00' && $res->fields['NOTE_DATE_1'] != '') 
					$NOTE_TIME = date("h:i A", strtotime($res->fields['NOTE_TIME']));
					
				$FOLLOWUP_TIME = '';
				if($res->fields['FOLLOWUP_TIME'] != '00-00-00' && $res->fields['FOLLOWUP_DATE_1'] != '') 
					$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NOTE_TIME);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_TYPE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_STATUS']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EVENT_OTHER']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_NAME']);
		
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FOLLOWUP_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FOLLOWUP_TIME);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMP_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREATED_BY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPLETED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
				
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 3){
		$cond .= " AND Z_INTERNAL_EMAIL.CREATED_ON BETWEEN '$ST' AND '$ET' ";
		
		$query = "select Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME, STUDENT_ID, SUBJECT, Z_INTERNAL_EMAIL.CREATED_ON, CONTENT, CAMPUS_CODE 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS, Z_INTERNAL_EMAIL, Z_INTERNAL_EMAIL_RECEPTION, Z_USER, S_STUDENT_CAMPUS, S_CAMPUS  
		WHERE 
		(Z_INTERNAL_EMAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_INTERNAL_EMAIL.PK_ACCOUNT = '1') AND 
		Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL = Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL AND 
		Z_INTERNAL_EMAIL_RECEPTION.PK_USER = Z_USER.PK_USER AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  
		$cond GROUP BY Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC, Z_INTERNAL_EMAIL.CREATED_ON ASC";
		
		if($_POST['FORMAT'] == 1){
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="17%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Sent On</i></b>
								</td>
								<td width="26%" style="border-bottom:1px solid #000;">
									<b><i>Subject</i></b>
								</td>
								<td width="34%" style="border-bottom:1px solid #000;">
									<b><i>Content</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
				$date = convert_to_user_date($res->fields['CREATED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
				
				$txt .= '<tr>
							<td >'.$res->fields['NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$date.'</td>
							<td >'.$res->fields['SUBJECT'].'</td>
							<td >'.$res->fields['CONTENT'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'Student ID';
			$width[]   = 15;
			$heading[] = 'Sent On';
			$width[]   = 20;
			$heading[] = 'Subject';
			$width[]   = 30;
			$heading[] = 'Content';
			$width[]   = 50;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				
				$i++;
			}

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				$date = convert_to_user_date($res->fields['CREATED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($date);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SUBJECT']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(strip_tags($res->fields['CONTENT']));
				
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 4){
		$cond .= " AND S_STUDENT_LOA.BEGIN_DATE BETWEEN '$ST' AND '$ET' ";
		
		if(!empty($_POST['PK_STUDENT_STATUS'])){
			$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
		}
		
		$query = "select PK_STUDENT_LOA, CONCAT(LAST_NAME,', ',FIRST_NAME,' ', MIDDLE_NAME) as NAME, STUDENT_ID, S_STUDENT_LOA.NOTES, REASON, CODE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1 ,IF(S_STUDENT_LOA.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.BEGIN_DATE, '%Y-%m-%d' )) AS LOA_BEGIN_DATE ,IF(S_STUDENT_LOA.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.END_DATE, '%Y-%m-%d' )) AS LOA_END_DATE, DATEDIFF(S_STUDENT_LOA.END_DATE, S_STUDENT_LOA.BEGIN_DATE) AS NO_OF_DAYS, CAMPUS_CODE, STUDENT_STATUS   
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_LOA 
		LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_LOA.PK_STUDENT_ENROLLMENT 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		, S_STUDENT_CAMPUS, S_CAMPUS 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_LOA.PK_STUDENT_MASTER AND 
		S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY PK_STUDENT_LOA ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC, S_STUDENT_LOA.BEGIN_DATE ASC";
		
		if($_POST['FORMAT'] == 1){
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="17%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="20%" style="border-bottom:1px solid #000;">
									<b><i>Enrollment</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Begin Date</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>End Date</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>No of Days</i></b>
								</td>
								<td width="12%" style="border-bottom:1px solid #000;">
									<b><i>Reason</i></b>
								</td>
								<td width="18%" style="border-bottom:1px solid #000;">
									<b><i>Notes</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
				$NO_OF_DAYS = 0;
				if($res->fields['NO_OF_DAYS'] > 0)
					$NO_OF_DAYS = $res->fields['NO_OF_DAYS'] + 1;
				$txt .= '<tr>
							<td >'.$res->fields['NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['LOA_BEGIN_DATE'].'</td>
							<td >'.$res->fields['LOA_END_DATE'].'</td>
							<td >'.$NO_OF_DAYS.'</td>
							<td >'.$res->fields['REASON'].'</td>
							<td >'.nl2br($res->fields['NOTES']).'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Enrollment';
			$width[]   = 20;
			$heading[] = 'Begin Date';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'No of Days';
			$width[]   = 20;
			$heading[] = 'Reason';
			$width[]   = 20;
			$heading[] = 'Notes';
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

			$res = $db->Execute($query);
			while (!$res->EOF) { 
			
				$NO_OF_DAYS = 0;
				if($res->fields['NO_OF_DAYS'] > 0)
					$NO_OF_DAYS = $res->fields['NO_OF_DAYS'] + 1;

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LOA_BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LOA_END_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NO_OF_DAYS);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['REASON']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
				
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 5){
		$query = $_SESSION['ACTIVTY_QUERY'];
		
		$PK_STUDENT_MASTER_ARR = array();
		foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
			$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
		}
		$PK_STUDENT_MASTER = implode(",", $PK_STUDENT_MASTER_ARR);
		
		$query = $query." AND S_STUDENT_NOTES.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) GROUP BY PK_STUDENT_NOTES ".$_SESSION['ACTIVTY_QUERY_ORDER'];
		//echo $query;exit;
		
		if($_POST['FORMAT'] == 1){
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="15%" style="border-bottom:1px solid #000;">
									<b><i>Enrollment</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Note Date</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Note Time</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Note Type</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Note Status</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Employee</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Completed</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
			
				$NOTE_TIME = '';
				if($res->fields['NOTE_TIME'] != '00-00-00' && $res->fields['NOTE_DATE_1'] != '') 
					$NOTE_TIME = date("h:i A", strtotime($res->fields['NOTE_TIME']));
					
				$FOLLOWUP_TIME = '';
				if($res->fields['FOLLOWUP_TIME'] != '00-00-00' && $res->fields['FOLLOWUP_DATE_1'] != '') 
					$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));
				
				$txt .= '<tr>
							<td >'.$res->fields['NAME'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['NOTE_DATE_1'].'</td>
							<td >'.$NOTE_TIME.'</td>
							<td >'.$res->fields['NOTE_TYPE'].'</td>
							<td >'.$res->fields['NOTE_STATUS'].'</td>
							<td >'.$res->fields['EMP_NAME'].'</td>
							<td >'.$res->fields['COMPLETED'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'Student ID';
			$width[]   = 15;
			$heading[] = 'Enrollment';
			$width[]   = 15;
			$heading[] = 'Note Date';
			$width[]   = 15;
			$heading[] = 'Note Time';
			$width[]   = 15;
			$heading[] = 'Note Type';
			$width[]   = 15;
			$heading[] = 'Note Status';
			$width[]   = 15;
			$heading[] = 'Follow Up Date';
			$width[]   = 15;
			$heading[] = 'Follow Up Time';
			$width[]   = 15;
			$heading[] = 'Employee';
			$width[]   = 15;
			$heading[] = 'Created By';
			$width[]   = 15;
			$heading[] = 'Home Phone';
			$width[]   = 15;
			$heading[] = 'Mobile Phone';
			$width[]   = 15;
			$heading[] = 'Email';
			$width[]   = 15;
			$heading[] = 'Completed';
			$width[]   = 15;
			$heading[] = 'Comments';
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

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				$NOTE_TIME = '';
				if($res->fields['NOTE_TIME'] != '00-00-00' && $res->fields['NOTE_DATE_1'] != '') 
					$NOTE_TIME = date("h:i A", strtotime($res->fields['NOTE_TIME']));
					
				$FOLLOWUP_TIME = '';
				if($res->fields['FOLLOWUP_TIME'] != '00-00-00' && $res->fields['FOLLOWUP_DATE_1'] != '') 
					$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($NOTE_TIME);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_TYPE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTE_STATUS']);
		
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FOLLOWUP_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FOLLOWUP_TIME);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMP_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREATED_BY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPLETED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
				
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 6){
		$cond .= " AND S_STUDENT_PROBATION.BEGIN_DATE BETWEEN '$ST' AND '$ET' ";
		
		if(!empty($_POST['PK_PROBATION_TYPE'])){
			$cond .= " AND S_STUDENT_PROBATION.PK_PROBATION_TYPE IN (".implode(",",$_POST['PK_PROBATION_TYPE']).") ";
		}
		
		if(!empty($_POST['PK_PROBATION_LEVEL'])){
			$cond .= " AND S_STUDENT_PROBATION.PK_PROBATION_LEVEL IN (".implode(",",$_POST['PK_PROBATION_LEVEL']).") ";
		}
		
		if(!empty($_POST['PK_PROBATION_STATUS'])){
			$cond .= " AND S_STUDENT_PROBATION.PK_PROBATION_STATUS IN (".implode(",",$_POST['PK_PROBATION_STATUS']).") ";
		}
		
		$query = "select PK_STUDENT_PROBATION, CONCAT(LAST_NAME,', ',FIRST_NAME,' ',MIDDLE_NAME) as NAME,CODE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%Y-%m-%d' )) AS BEGIN_DATE_1, S_STUDENT_PROBATION.NOTES, REASON,IF(S_STUDENT_PROBATION.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_PROBATION.BEGIN_DATE, '%Y-%m-%d' )) AS PROBATION_BEGIN_DATE ,IF(S_STUDENT_PROBATION.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_PROBATION.END_DATE, '%Y-%m-%d' )) AS PROBATION_END_DATE, PROBATION_TYPE, PROBATION_LEVEL, PROBATION_STATUS, CAMPUS_CODE, STUDENT_STATUS, STUDENT_ID
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS, S_CAMPUS, S_STUDENT_PROBATION 
		LEFT JOIN M_PROBATION_TYPE ON M_PROBATION_TYPE.PK_PROBATION_TYPE = S_STUDENT_PROBATION.PK_PROBATION_TYPE 
		LEFT JOIN M_PROBATION_LEVEL ON M_PROBATION_LEVEL.PK_PROBATION_LEVEL = S_STUDENT_PROBATION.PK_PROBATION_LEVEL 
		LEFT JOIN M_PROBATION_STATUS ON M_PROBATION_STATUS.PK_PROBATION_STATUS = S_STUDENT_PROBATION.PK_PROBATION_STATUS 
		LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_PROBATION.PK_STUDENT_ENROLLMENT 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS =  S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_PROBATION.PK_STUDENT_MASTER AND 
		S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY PK_STUDENT_PROBATION ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC, S_STUDENT_PROBATION.BEGIN_DATE ASC";
		
		if($_POST['FORMAT'] == 1){
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="18%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="18%" style="border-bottom:1px solid #000;">
									<b><i>Enrollment</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>Begin Date</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>End Date</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Probation Type</i></b>
								</td>
								<td width="9%" style="border-bottom:1px solid #000;">
									<b><i>Probation Level</i></b>
								</td>
								<td width="9%" style="border-bottom:1px solid #000;">
									<b><i>Probation Status</i></b>
								</td>
								<td width="12%" style="border-bottom:1px solid #000;">
									<b><i>Reason</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
				$NO_OF_DAYS = 0;
				if($res->fields['NO_OF_DAYS'] > 0)
					$NO_OF_DAYS = $res->fields['NO_OF_DAYS'] + 1;
				$txt .= '<tr>
							<td >'.$res->fields['NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['PROBATION_BEGIN_DATE'].'</td>
							<td >'.$res->fields['PROBATION_END_DATE'].'</td>
							<td >'.$res->fields['PROBATION_TYPE'].'</td>
							<td >'.$res->fields['PROBATION_LEVEL'].'</td>
							<td >'.$res->fields['PROBATION_STATUS'].'</td>
							<td >'.$res->fields['REASON'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Enrollment';
			$width[]   = 20;
			$heading[] = 'Begin Date';
			$width[]   = 20;
			$heading[] = 'End Date';
			$width[]   = 20;
			$heading[] = 'Probation Type';
			$width[]   = 20;
			$heading[] = 'Probation Level';
			$width[]   = 20;
			$heading[] = 'Probation Status';
			$width[]   = 20;
			$heading[] = 'Reason';
			$width[]   = 20;
			$heading[] = 'Notes';
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

			$res = $db->Execute($query);
			while (!$res->EOF) { 

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROBATION_BEGIN_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROBATION_END_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROBATION_TYPE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROBATION_LEVEL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROBATION_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['REASON']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
				
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 7){

		$query = $_SESSION['ACTIVTY_QUERY'];
		
		$PK_STUDENT_MASTER_ARR = array();
		foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
			$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
		}
		$PK_STUDENT_MASTER = implode(",", $PK_STUDENT_MASTER_ARR);
		
		$query = $query." AND S_STUDENT_TASK.PK_STUDENT_MASTER IN ($PK_STUDENT_MASTER) GROUP BY PK_STUDENT_TASK ".$_SESSION['ACTIVTY_QUERY_ORDER'];
		
		//echo $query;exit;
		/* Ticket # 1752  */
		if($_POST['FORMAT'] == 1){
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="6%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="17%" style="border-bottom:1px solid #000;">
									<b><i>Enrollment</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>Task Date</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>Task Time</i></b>
								</td>
								<td width="9%" style="border-bottom:1px solid #000;">
									<b><i>Task Type</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Task Status</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Task Other</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Priority</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Employee</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>Completed</i></b>
								</td>
							</tr>
						</thead>';
			
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
			
				$TASK_TIME = '';
				if($res->fields['TASK_TIME'] != '00-00-00' && $res->fields['TASK_DATE_1'] != '') 
					$TASK_TIME = date("h:i A", strtotime($res->fields['TASK_TIME']));
					
				$FOLLOWUP_TIME = '';
				if($res->fields['FOLLOWUP_TIME'] != '00-00-00' && $res->fields['FOLLOWUP_DATE'] != '') 
					$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));
				
				$txt .= '<tr>
							<td >'.$res->fields['NAME'].'</td>
							<td >'.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE'].'</td>
							<td >'.$res->fields['TASK_DATE_1'].'</td>
							<td >'.$TASK_TIME.'</td>
							<td >'.$res->fields['TASK_TYPE'].'</td>
							<td >'.$res->fields['TASK_STATUS'].'</td>
							<td >'.$res->fields['EVENT_OTHER'].'</td>
							<td >'.$res->fields['NOTES_PRIORITY'].'</td>
							<td >'.$res->fields['EMP_NAME'].'</td>
							<td >'.$res->fields['COMPLETED'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'Student ID';
			$width[]   = 15;
			$heading[] = 'Enrollment';
			$width[]   = 15;
			$heading[] = 'Task Date';
			$width[]   = 15;
			$heading[] = 'Task Time';
			$width[]   = 15;
			$heading[] = 'Task Type';
			$width[]   = 15;
			$heading[] = 'Task Status';
			$width[]   = 15;
			$heading[] = 'Task Other';
			$width[]   = 15;
			$heading[] = 'Priority';
			$width[]   = 15;
			$heading[] = 'Follow Up Date';
			$width[]   = 15;
			$heading[] = 'Follow Up Time';
			$width[]   = 15;
			$heading[] = 'Employee';
			$width[]   = 15;
			$heading[] = 'Created By';
			$width[]   = 15;
			
			$heading[] = 'Home Phone';
			$width[]   = 15;
			$heading[] = 'Mobile Phone';
			$width[]   = 15;
			$heading[] = 'Email';
			$width[]   = 15;
			$heading[] = 'Completed';
			$width[]   = 15;
			$heading[] = 'Comments';
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

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				$TASK_TIME = '';
				if($res->fields['TASK_TIME'] != '00-00-00' && $res->fields['TASK_DATE_1'] != '') 
					$TASK_TIME = date("h:i A", strtotime($res->fields['TASK_TIME']));
					
				$FOLLOWUP_TIME = '';
				if($res->fields['FOLLOWUP_TIME'] != '00-00-00' && $res->fields['FOLLOWUP_DATE'] != '') 
					$FOLLOWUP_TIME = date("h:i A", strtotime($res->fields['FOLLOWUP_TIME']));

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TASK_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($TASK_TIME);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TASK_TYPE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TASK_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EVENT_OTHER']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES_PRIORITY']);
		
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FOLLOWUP_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FOLLOWUP_TIME);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMP_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREATED_BY_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPLETED']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
				
				$res->MoveNext();
			}
			/* Ticket # 1752  */
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
		}
	} else if($_POST['REPORT_TYPE'] == 8){
		$cond .= " AND S_TEXT_LOG.SENT_ON BETWEEN '$ST' AND '$ET' ";
		
		if(!empty($_POST['PK_DEPARTMENT'])){
			$dep_t 	= "";
			foreach($_POST['PK_DEPARTMENT'] as $t){
				if($dep_t != '')
					$dep_t .= ",";
					
				$dep_t .= get_department_from_t($t);
			}
			
			$cond .= " AND S_TEXT_LOG.PK_DEPARTMENT IN ($dep_t) ";
		}

		$query = "select PK_TEXT_LOG, TEXT_CONTENT, TO_PHONE, SENT_ON, STUDENT_ID, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ', S_EMPLOYEE_MASTER.FIRST_NAME) AS EMP_NAME, DEPARTMENT, IF(IS_RECEIVED_MSG = 1,'Received','Sent') as STATUS, S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CAMPUS_CODE FROM 
		S_TEXT_LOG 
		LEFT JOIN Z_USER ON S_TEXT_LOG.CREATED_BY = Z_USER.PK_USER 
		LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID 
		LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_TEXT_LOG.PK_DEPARTMENT 
		,S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_CAMPUS, S_CAMPUS 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_TEXT_LOG.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND 
		S_TEXT_LOG.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY PK_TEXT_LOG ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC, SENT_ON ASC";
		
		if($_POST['FORMAT'] == 1){
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="15%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student ID</i></b>
								</td>
								<td width="12%" style="border-bottom:1px solid #000;">
									<b><i>Date/Time</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Cell #</i></b>
								</td>
								<td width="15%" style="border-bottom:1px solid #000;">
									<b><i>Employee</i></b>
								</td>
								<td width="12%" style="border-bottom:1px solid #000;">
									<b><i>Department</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Status</i></b>
								</td>
								<td width="15%" style="border-bottom:1px solid #000;">
									<b><i>Text</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
				$date = convert_to_user_date($res->fields['SENT_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get()); //Ticket # 1755 
				
				$txt .= '<tr>
							<td >'.$res->fields['STUD_NAME'].'</td>
							<td >'.$res->fields['STUDENT_ID'].'</td>
							<td >'.$date.'</td>
							<td >'.$res->fields['TO_PHONE'].'</td>
							<td >'.$res->fields['EMP_NAME'].'</td>
							<td >'.$res->fields['DEPARTMENT'].'</td>
							<td >'.$res->fields['STATUS'].'</td>
							<td >'.$res->fields['TEXT_CONTENT'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output($file_name, 'D');
		} else {
			$line = 1;
			$index 	= -1;
			$heading[] = 'Student';
			$width[]   = 20;
			$heading[] = 'Campus';
			$width[]   = 20;
			$heading[] = 'Student ID';
			$width[]   = 20;
			$heading[] = 'Date/Time';
			$width[]   = 20;
			$heading[] = 'Cell #';
			$width[]   = 20;
			$heading[] = 'Employee';
			$width[]   = 20;
			$heading[] = 'Department';
			$width[]   = 20;
			$heading[] = 'Status';
			$width[]   = 20;
			$heading[] = 'Text';
			$width[]   = 30;

			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				
				$i++;
			}

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				$date = convert_to_user_date($res->fields['SENT_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUD_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($date);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TO_PHONE']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMP_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DEPARTMENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TEXT_CONTENT']);
				
				$res->MoveNext();
			}
			
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
	<title><?=MNU_ACTIVITY_REPORT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
		
		/* Ticket # 1752 */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1752 */
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
                        <h4 class="text-themecolor">
						<? echo MNU_ACTIVITY_REPORT ?> </h4>
                    </div>
                </div>
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row" style="margin-bottom:1px" >
										<div class="col-md-2">
											<?=ACTIVITY_TYPE?>
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" onchange="show_fields(this.value)" >
												<option value=""></option>
												<option value="1">Emails</option>
												<option value="2">Events</option>
												<!-- <option value="3">Internal Messages</option> -->
												<option value="4">LOA</option>
												<option value="5">Notes</option>
												<option value="6">Probation</option>
												<option value="7">Tasks</option>
												<option value="8">Texts</option>
											</select>
										</div>
										
										<div class="col-md-2" id="PK_CAMPUS_DIV" style="display:none" >
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="search(0)" >
												<? /* Ticket # 1753 */
												$res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option> <!-- Ticket # 1921 -->
												<?	$res_type->MoveNext();
												} /* Ticket # 1753 */ ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="EVENT_DATE_TYPE_DIV" style="display:none" >
											<?=DATE_TYPE?>
											<select id="EVENT_DATE_TYPE" name="EVENT_DATE_TYPE" class="form-control" >
												<option value="ED" >Event Date</option>
												<option value="FD" >Follow Up Date</option>
											</select>
										</div>
										
										<div class="col-md-2 " id="NOTE_DATE_TYPE_DIV" style="display:none" >
											<?=DATE_TYPE?>
											<select id="NOTE_DATE_TYPE" name="NOTE_DATE_TYPE" class="form-control" >
												<option value="ND" >Note Date</option>
												<option value="FD" >Follow Up Date</option>
											</select>
										</div>
										
										<div class="col-md-2 " id="TASK_DATE_TYPE_DIV" style="display:none" >
											<?=DATE_TYPE?>
											<select id="TASK_DATE_TYPE" name="TASK_DATE_TYPE" class="form-control" onchange="search(0)" >
												<option value="TD" >Task Date</option>
												<option value="FD" >Follow Up Date</option>
											</select>
										</div>
										
										<div class="col-md-2" id="START_DATE_DIV" style="display:none" >
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" onchange="search(0)" >
										</div>
										<div class="col-md-2" id="END_DATE_DIV" style="display:none" >
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" onchange="search(0)" >
										</div>
										
										
										
										<div class="col-md-2 ">
											<br />
											<button type="button" onclick="submit_form(1)" id="btn_1" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="btn_2" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
										</div>
									</div>
									<hr style="border-top: 1px solid #ccc;" />
									
									<div class="row" style="margin-bottom:1px" >
									
										<div class="col-md-2 " id="PK_TASK_TYPE_DIV" style="display:none" >
											<?=TASK_TYPE?>
											<div id="PK_TASK_TYPE_DIV_1" >
												<select id="PK_TASK_TYPE" name="PK_TASK_TYPE[]" class="form-control" multiple  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 " id="PK_TASK_STATUS_DIV" style="display:none" >
											<?=TASK_STATUS?>
											<div id="PK_TASK_STATUS_DIV_1" >
												<select id="PK_TASK_STATUS" name="PK_TASK_STATUS[]" multiple class="form-control"  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 " id="PK_NOTE_TYPE_DIV" style="display:none" >
											<div id="PK_NOTE_TYPE_LABEL" ><?=EVENT_TYPE?></div>
											<div id="PK_NOTE_TYPE_DIV_1" >
												<select id="PK_NOTE_TYPE" name="PK_NOTE_TYPE[]" class="form-control" multiple  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 " id="PK_NOTE_STATUS_DIV" style="display:none" >
											<div id="PK_NOTE_STATUS_LABEL" ><?=EVENT_STATUS?></div>
											<div id="PK_NOTE_STATUS_DIV_1" >
												<select id="PK_NOTE_STATUS" name="PK_NOTE_STATUS[]" multiple class="form-control"  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 " id="PK_EVENT_OTHER_DIV" style="display:none" >
											<div id="PK_EVENT_OTHER_LABEL" ><?=EVENT_OTHER?></div>
											<div id="PK_EVENT_OTHER_DIV_1" >
												<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER[]" multiple class="form-control"  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 " id="TASK_COMPLETED_DIV" style="display:none" >
											<div id="COMPLETED_LABEL" ><?=EVENT_COMPLETED?></div>
											<select id="COMPLETED" name="COMPLETED" class="form-control"  >
												<option value="0" >Both</option>
												<option value="1" >Yes</option>
												<option value="2" >No</option>
											</select>
										</div>

									</div>

									<div class="row" style="margin-bottom:1px" >
										<!-- DIAM-1183 -->
										<div class="col-md-2 " id="PK_COMPANY_DIV" style="display:none" >
											<div id="COMPANY_LABEL" ><?=COMPANY?></div>
											<select id="PK_COMPANY" name="PK_COMPANY[]" multiple class="form-control">
												<? $res_type = $db->Execute("SELECT PK_COMPANY,COMPANY_NAME,ACTIVE FROM S_COMPANY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, COMPANY_NAME ASC ");
												while (!$res_type->EOF) 
												{ 
													$selected 		= "";
													$PK_COMPANY 	= $res_type->fields['PK_COMPANY']; 
													if($PK_COMPANY_ARR == $PK_COMPANY) {
														$selected = 'selected';
													}

													$option_labels = $res_type->fields['COMPANY_NAME'];
													if($res_type->fields['ACTIVE'] == 0)
													{
														$option_labels .= " (Inactive)";
													}
													?>
													<option value="<?=$res_type->fields['PK_COMPANY']?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) {echo "class='option_red'"; } ?>  ><?=$option_labels?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- End DIAM-1183 -->
										<div class="col-md-2" id="PK_DEPARTMENT_DIV" style="display:none" >
											<?=DEPARTMENT?>
											<select id="PK_DEPARTMENT" name="PK_DEPARTMENT[]" multiple class="form-control" onchange="fetch_values();search(0)" >
												<option value="5">Accounting</option>
												<option value="1">Admissions</option>
												<option value="3">Finance</option>
												<option value="6">Placement</option>
												<option value="2">Registrar</option>
											</select>
										</div>
										
										<div class="col-md-2" id="PK_EMPLOYEE_MASTER_DIV" style="display:none" >
											<?=EMPLOYEE?>
											<div id="PK_EMPLOYEE_MASTER_DIV_1" >
												<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER[]" multiple class="form-control required-entry" >
												</select>
											</div>
										</div>
										
										<!-- Ticket # 1752  -->
										<div class="col-md-2" id="CREATED_BY_DIV" style="display:none" >
											<?=CREATED_BY?>
											<div id="CREATED_BY_DIV_1" >
												<select id="CREATED_BY" name="CREATED_BY[]" multiple class="form-control" >
												</select>
											</div>
										</div>
										<!-- Ticket # 1752  -->
									</div>

									<div class="row" style="margin-bottom:1px" >			
										<div class="col-md-2" id="PROBATION_TYPE" style="display:none" >
											<?=PROBATION_TYPE ?>
											<select id="PK_PROBATION_TYPE" name="PK_PROBATION_TYPE[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PROBATION_TYPE, PROBATION_TYPE, ACTIVE from M_PROBATION_TYPE WHERE 1=1 order by ACTIVE DESC, PROBATION_TYPE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['PROBATION_TYPE'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_PROBATION_TYPE']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="PROBATION_LEVEL" style="display:none" >
											<?=PROBATION_LEVEL ?>
											<select id="PK_PROBATION_LEVEL" name="PK_PROBATION_LEVEL[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PROBATION_LEVEL, PROBATION_LEVEL, ACTIVE from M_PROBATION_LEVEL WHERE 1=1 order by ACTIVE DESC, PROBATION_LEVEL ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['PROBATION_LEVEL'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_PROBATION_LEVEL']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="PROBATION_STATUS" style="display:none" >
											<?=PROBATION_STATUS ?>
											<select id="PK_PROBATION_STATUS" name="PK_PROBATION_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PROBATION_STATUS, PROBATION_STATUS, ACTIVE from M_PROBATION_STATUS WHERE 1=1 order by ACTIVE DESC, PROBATION_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['PROBATION_STATUS'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_PROBATION_STATUS']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="row" style="margin-bottom:1px" >	
										<!-- DIAM-1279 -->
										<div class="col-md-2 form-group " id="PK_TERM_MASTER_DIV" style="display:none" >
											<div id="TERM_MASTER_LABEL" ><?=FIRST_TERM?></div>
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) { 
													$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'];
													if($res_type->fields['ACTIVE'] == 0)
														$str .= ' (Inactive)'; ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 form-group " id="PK_CAMPUS_PROGRAM_DIV" style="display:none" >
											<div id="CAMPUS_PROGRAM_LABEL" ><?=PROGRAM?></div>
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- End DIAM-1279 -->		
										<div class="col-md-2" id="PK_STUDENT_STATUS_DIV" style="display:none" >
											<?=STUDENT_STATUS?><br />
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";  ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-1 ">
											<br />
											<button type="button" onclick="search(1)" id="btn_search" class="btn waves-effect waves-light btn-info" style="display:none" ><?=SEARCH?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									
									
									<br />
									<div id="PHONE_DIV" >
										
									</div>
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
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function show_fields(val){
			document.getElementById('START_DATE_DIV').style.display 			= 'none';
			document.getElementById('END_DATE_DIV').style.display 				= 'none';
			document.getElementById('PK_CAMPUS_DIV').style.display 				= 'none';
			document.getElementById('PK_EMPLOYEE_MASTER_DIV').style.display 	= 'none';
			document.getElementById('PK_DEPARTMENT_DIV').style.display 			= 'none';
			document.getElementById('PK_NOTE_TYPE_DIV').style.display 			= 'none';
			document.getElementById('PK_NOTE_STATUS_DIV').style.display 		= 'none';
			document.getElementById('TASK_COMPLETED_DIV').style.display 		= 'none';
			document.getElementById('PK_COMPANY_DIV').style.display 			= 'none';
			document.getElementById('EVENT_DATE_TYPE_DIV').style.display 		= 'none';
			document.getElementById('NOTE_DATE_TYPE_DIV').style.display 		= 'none';
			document.getElementById('PK_TERM_MASTER_DIV').style.display 		= 'none'; // DIAM-1279
			document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 		= 'none'; // DIAM-1279
			document.getElementById('PK_EVENT_OTHER_DIV').style.display 		= 'none';
			document.getElementById('PK_TASK_TYPE_DIV').style.display 			= 'none';
			document.getElementById('PK_TASK_STATUS_DIV').style.display 		= 'none';
			document.getElementById('TASK_DATE_TYPE_DIV').style.display 		= 'none';
			document.getElementById('CREATED_BY_DIV').style.display 			= 'none'; //Ticket # 1752
			document.getElementById('btn_search').style.display 				= 'none';
			document.getElementById('PROBATION_TYPE').style.display 			= 'none'; // DIAM-1279
			document.getElementById('PROBATION_LEVEL').style.display 			= 'none'; // DIAM-1279
			document.getElementById('PROBATION_STATUS').style.display 			= 'none'; // DIAM-1279
			document.getElementById('PK_STUDENT_STATUS_DIV').style.display 		= 'none';
			document.getElementById('PHONE_DIV').innerHTML						= '';
			
			if(val == 1 || val == 2 || val == 3 || val == 4 || val == 5 || val == 6|| val == 7 || val == 8) {
				document.getElementById('btn_search').style.display = 'inline';
				document.getElementById('btn_1').style.display 		= 'none';
				document.getElementById('btn_2').style.display 		= 'none';
			}
			
			if(val == 1 || val == 3 || val == 4 || val == 6 || val == 8) {
				document.getElementById('START_DATE_DIV').style.display = 'inline';
				document.getElementById('END_DATE_DIV').style.display 	= 'inline';
				document.getElementById('PK_CAMPUS_DIV').style.display 	= 'inline';
				
				if(val == 1)
				{
					document.getElementById('PK_TERM_MASTER_DIV').style.display     = 'inline'; // DIAM-1279
					document.getElementById('TERM_MASTER_LABEL').innerHTML 		    = '<?=FIRST_TERM?>' // DIAM-1279
					document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display  = 'inline'; // DIAM-1279
					document.getElementById('CAMPUS_PROGRAM_LABEL').innerHTML 		= '<?=PROGRAM?>' // DIAM-1279
					document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';
				}
				if(val == 8)
				{
					document.getElementById('PK_DEPARTMENT_DIV').style.display 	= 'inline';

					document.getElementById('PK_TERM_MASTER_DIV').style.display     = 'inline'; // DIAM-1279
					document.getElementById('TERM_MASTER_LABEL').innerHTML 		    = '<?=FIRST_TERM?>' // DIAM-1279
					document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display  = 'inline'; // DIAM-1279
					document.getElementById('CAMPUS_PROGRAM_LABEL').innerHTML 		= '<?=PROGRAM?>' // DIAM-1279
					document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';
				}					
				if(val == 6)
				{
					document.getElementById('PROBATION_TYPE').style.display 	= 'inline'; // DIAM-1279
					document.getElementById('PROBATION_LEVEL').style.display 	= 'inline'; // DIAM-1279
					document.getElementById('PROBATION_STATUS').style.display 	= 'inline'; // DIAM-1279

					document.getElementById('PK_TERM_MASTER_DIV').style.display     = 'inline'; // DIAM-1279
					document.getElementById('TERM_MASTER_LABEL').innerHTML 		    = '<?=FIRST_TERM?>' // DIAM-1279
					document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display  = 'inline'; // DIAM-1279
					document.getElementById('CAMPUS_PROGRAM_LABEL').innerHTML 		= '<?=PROGRAM?>' // DIAM-1279
					document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';
				}					
				if(val == 4)
				{
					document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';

					document.getElementById('PK_TERM_MASTER_DIV').style.display     = 'inline'; // DIAM-1279
					document.getElementById('TERM_MASTER_LABEL').innerHTML 		    = '<?=FIRST_TERM?>' // DIAM-1279
					document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display  = 'inline'; // DIAM-1279
					document.getElementById('CAMPUS_PROGRAM_LABEL').innerHTML 		= '<?=PROGRAM?>' // DIAM-1279
					document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';
				}
					
			} else if(val == 2 || val == 5 ) {
				document.getElementById('START_DATE_DIV').style.display 			= 'inline';
				document.getElementById('END_DATE_DIV').style.display 				= 'inline';
				document.getElementById('PK_CAMPUS_DIV').style.display 				= 'inline';
				document.getElementById('PK_EMPLOYEE_MASTER_DIV').style.display 	= 'inline';
				document.getElementById('PK_DEPARTMENT_DIV').style.display 			= 'inline';
				document.getElementById('PK_NOTE_TYPE_DIV').style.display 			= 'inline';
				document.getElementById('PK_NOTE_STATUS_DIV').style.display 		= 'inline';
				document.getElementById('TASK_COMPLETED_DIV').style.display 		= 'inline';
				
				
				document.getElementById('CREATED_BY_DIV').style.display 			= 'inline'; //Ticket # 1753
				
				if(val == 2) {
					document.getElementById('PK_EVENT_OTHER_DIV').style.display 	= 'inline';

					document.getElementById('PK_COMPANY_DIV').style.display 		= 'inline'; // DIAM-1183

					document.getElementById('COMPLETED_LABEL').innerHTML 			= '<?=EVENT_COMPLETED?>'
					document.getElementById('PK_NOTE_TYPE_LABEL').innerHTML 		= '<?=EVENT_TYPE?>'
					document.getElementById('PK_EVENT_OTHER_LABEL').innerHTML 		= '<?=EVENT_OTHER?>'
					document.getElementById('PK_NOTE_STATUS_LABEL').innerHTML 		= '<?=EVENT_STATUS?>'
					document.getElementById('EVENT_DATE_TYPE_DIV').style.display 	= 'inline';

					document.getElementById('PK_TERM_MASTER_DIV').style.display     = 'inline'; // DIAM-1279
					document.getElementById('TERM_MASTER_LABEL').innerHTML 		    = '<?=FIRST_TERM?>' // DIAM-1279
					document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display  = 'inline'; // DIAM-1279
					document.getElementById('CAMPUS_PROGRAM_LABEL').innerHTML 		= '<?=PROGRAM?>' // DIAM-1279
					document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';
				} else {
					document.getElementById('PK_TERM_MASTER_DIV').style.display     = 'inline'; // DIAM-1279
					document.getElementById('TERM_MASTER_LABEL').innerHTML 		    = '<?=FIRST_TERM?>' // DIAM-1279
					document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display  = 'inline'; // DIAM-1279
					document.getElementById('CAMPUS_PROGRAM_LABEL').innerHTML 		= '<?=PROGRAM?>' // DIAM-1279
					document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';

					document.getElementById('COMPLETED_LABEL').innerHTML 		= '<?=NOTES_COMPLETED?>'
					document.getElementById('PK_NOTE_TYPE_LABEL').innerHTML 	= '<?=NOTES_TYPE?>'
					document.getElementById('PK_EVENT_OTHER_LABEL').innerHTML 	= '<?=NOTES_OTHER?>'
					document.getElementById('PK_NOTE_STATUS_LABEL').innerHTML 	= '<?=NOTES_STATUS?>'
					document.getElementById('NOTE_DATE_TYPE_DIV').style.display = 'inline';
				}
			} else if(val == 7) {
				document.getElementById('START_DATE_DIV').style.display 			= 'inline';
				document.getElementById('END_DATE_DIV').style.display 				= 'inline';
				document.getElementById('PK_CAMPUS_DIV').style.display 				= 'inline';
				document.getElementById('PK_EMPLOYEE_MASTER_DIV').style.display 	= 'inline';
				document.getElementById('PK_DEPARTMENT_DIV').style.display 			= 'inline';
				document.getElementById('PK_EVENT_OTHER_DIV').style.display 		= 'inline';
				document.getElementById('PK_EVENT_OTHER_LABEL').innerHTML 			= '<?=TASK_OTHER?>'
				document.getElementById('COMPLETED_LABEL').innerHTML 				= '<?=TASK_COMPLETED?>'
				document.getElementById('TASK_COMPLETED_DIV').style.display 		= 'inline';
				document.getElementById('TASK_DATE_TYPE_DIV').style.display 		= 'inline';
				document.getElementById('PK_TASK_TYPE_DIV').style.display 			= 'inline';
				document.getElementById('PK_TASK_STATUS_DIV').style.display 		= 'inline';
				document.getElementById('CREATED_BY_DIV').style.display 			= 'inline'; //Ticket # 1752

				document.getElementById('PK_TERM_MASTER_DIV').style.display     = 'inline'; // DIAM-1279
				document.getElementById('TERM_MASTER_LABEL').innerHTML 		    = '<?=FIRST_TERM?>' // DIAM-1279
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display  = 'inline'; // DIAM-1279
				document.getElementById('CAMPUS_PROGRAM_LABEL').innerHTML 		= '<?=PROGRAM?>' // DIAM-1279
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 	= 'inline';
				
				document.getElementById('btn_1').style.display 			= 'none';
				document.getElementById('btn_2').style.display 			= 'none';
			}
			
			fetch_values()
		}
		
		function fetch_values(){
			jQuery(document).ready(function($) { 
				var REPORT_TYPE = document.getElementById('REPORT_TYPE').value;
				var t = $("#PK_DEPARTMENT").val()
				if(REPORT_TYPE == 2) {
					get_note_type(t,1)
					get_note_status(t,1)
					get_event_other(t,0)
					get_employee(t,1)
					get_created_by(t,0)
				} else if(REPORT_TYPE == 5) {
					get_note_type(t,0)
					get_note_status(t,0)
					get_employee(t,0)
					get_created_by(t,0) //Ticket # 1753 
				} else if(REPORT_TYPE == 7) {
					get_task_type(t,0)
					get_task_status(t,0)
					get_event_other(t,1)
					get_employee(t,0)
					get_created_by(t,0) //Ticket # 1752 
				}
			});	
		}
		
		/* Ticket # 1752  */
		function get_created_by(val,events){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event='+events+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_employee_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						data = data.replace("PK_EMPLOYEE_MASTER","CREATED_BY")
						document.getElementById('CREATED_BY_DIV_1').innerHTML = data
						
						document.getElementById('CREATED_BY').setAttribute('multiple', true);
						document.getElementById('CREATED_BY').name = "CREATED_BY[]"
						document.getElementById('CREATED_BY').setAttribute('onchange', "search(0)");
						$("#CREATED_BY option[value='']").remove();
						
						$("#CREATED_BY").children().first().remove();
		
						$('#CREATED_BY').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=CREATED_BY?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=CREATED_BY?> selected', //Ticket # 1593
							enableCaseInsensitiveFiltering: true, //Ticket # 1593
						});
					}		
				}).responseText;
			});
		}
		/* Ticket # 1752  */
		
		function get_task_type(val,events){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event='+events+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_task_type_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_TASK_TYPE_DIV_1').innerHTML = data
						document.getElementById('PK_TASK_TYPE').setAttribute('multiple', true);
						document.getElementById('PK_TASK_TYPE').setAttribute('onchange', "search(0)");
						document.getElementById('PK_TASK_TYPE').name = "PK_TASK_TYPE[]"
						$("#PK_TASK_TYPE option[value='']").remove();
						
						$("#PK_TASK_TYPE").children().first().remove();
		
						$('#PK_TASK_TYPE').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=TASK_TYPE?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=TASK_TYPE?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_task_status(val,events){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event='+events+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_task_status_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_TASK_STATUS_DIV_1').innerHTML 	= data
						
						document.getElementById('PK_TASK_STATUS').setAttribute('multiple', true);
						document.getElementById('PK_TASK_STATUS').setAttribute('onchange', "search(0)");
						document.getElementById('PK_TASK_STATUS').name = "PK_TASK_STATUS[]"
						$("#PK_TASK_STATUS option[value='']").remove();
						
						$("#PK_TASK_STATUS").children().first().remove();
		
						$('#PK_TASK_STATUS').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=TASK_STATUS?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=TASK_STATUS?> selected'
						});
					}		
				}).responseText;
			});
		}
		
		function get_note_type(val,events){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event='+events+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_note_type_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_NOTE_TYPE_DIV_1').innerHTML = data
						
						document.getElementById('PK_NOTE_TYPE').setAttribute('multiple', true);
						document.getElementById('PK_NOTE_TYPE').name = "PK_NOTE_TYPE[]"
						$("#PK_NOTE_TYPE option[value='']").remove();
						
						$("#PK_NOTE_TYPE").children().first().remove();
		
						$('#PK_NOTE_TYPE').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=EVENT_TYPE?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=EVENT_TYPE?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_event_other(val,task){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&task='+task+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_event_other_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_EVENT_OTHER_DIV_1').innerHTML = data
						
						document.getElementById('PK_EVENT_OTHER').setAttribute('multiple', true);
						document.getElementById('PK_EVENT_OTHER').name = "PK_EVENT_OTHER[]"
						document.getElementById('PK_EVENT_OTHER').setAttribute('onchange', "search(0)");
						$("#PK_EVENT_OTHER option[value='']").remove();
						
						$("#PK_EVENT_OTHER").children().first().remove();
		
						$('#PK_EVENT_OTHER').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=EVENT_OTHER?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=EVENT_OTHER?> selected'
						});	
						
					}		
				}).responseText;
			});
		}
		function get_note_status(val,events){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event='+events+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_note_status_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_NOTE_STATUS_DIV_1').innerHTML = data
						
						document.getElementById('PK_NOTE_STATUS').setAttribute('multiple', true);
						document.getElementById('PK_NOTE_STATUS').name = "PK_NOTE_STATUS[]"
						$("#PK_NOTE_STATUS option[value='']").remove();
						
						$("#PK_NOTE_STATUS").children().first().remove();
		
						$('#PK_NOTE_STATUS').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=EVENT_STATUS?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=EVENT_STATUS?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_employee(val,events){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event='+events+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_employee_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_EMPLOYEE_MASTER_DIV_1').innerHTML = data
						
						document.getElementById('PK_EMPLOYEE_MASTER').setAttribute('multiple', true);
						document.getElementById('PK_EMPLOYEE_MASTER').name = "PK_EMPLOYEE_MASTER[]"
						document.getElementById('PK_EMPLOYEE_MASTER').setAttribute('onchange', "search(0)");
						$("#PK_EMPLOYEE_MASTER option[value='']").remove();
						
						$("#PK_EMPLOYEE_MASTER").children().first().remove();
		
						$('#PK_EMPLOYEE_MASTER').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=EMPLOYEE?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=EMPLOYEE?> selected', //Ticket # 1593
							enableCaseInsensitiveFiltering: true, //Ticket # 1593
						});
				
					}		
				}).responseText;
			});
		}
		
		/* Ticket # 1582 */
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
		
		function search(type){
			if(type == 0) {
				document.getElementById('PHONE_DIV').innerHTML = ''
				
			} else {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true){ 
					jQuery(document).ready(function($) {
						if(document.getElementById('REPORT_TYPE').value == 7)
						{
							var data  = 'TASK_DATE_TYPE='+$('#TASK_DATE_TYPE').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_TASK_TYPE='+$('#PK_TASK_TYPE').val()+'&PK_TASK_STATUS='+$('#PK_TASK_STATUS').val()+'&PK_EVENT_OTHER='+$('#PK_EVENT_OTHER').val()+'&COMPLETED='+$('#COMPLETED').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_DEPARTMENT='+$('#PK_DEPARTMENT').val()+'&PK_EMPLOYEE_MASTER='+$('#PK_EMPLOYEE_MASTER').val()+'&CREATED_BY='+$('#CREATED_BY').val()+'&show_check=1&show_count=1&type=task'; //Ticket # 1752 
						}
						else if(document.getElementById('REPORT_TYPE').value == 5)
						{
							var data  = 'NOTE_DATE_TYPE='+$('#NOTE_DATE_TYPE').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_NOTE_TYPE='+$('#PK_NOTE_TYPE').val()+'&PK_NOTE_STATUS='+$('#PK_NOTE_STATUS').val()+'&COMPLETED='+$('#COMPLETED').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_DEPARTMENT='+$('#PK_DEPARTMENT').val()+'&PK_EMPLOYEE_MASTER='+$('#PK_EMPLOYEE_MASTER').val()+'&CREATED_BY='+$('#CREATED_BY').val()+'&show_check=1&show_count=1&type=notes'; 
						}
						else if(document.getElementById('REPORT_TYPE').value == 2)
						{
							var data  = 'NOTE_DATE_TYPE='+$('#EVENT_DATE_TYPE').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_NOTE_TYPE='+$('#PK_NOTE_TYPE').val()+'&PK_NOTE_STATUS='+$('#PK_NOTE_STATUS').val()+'&PK_EVENT_OTHER='+$('#PK_EVENT_OTHER').val()+'&COMPLETED='+$('#COMPLETED').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COMPANY='+$('#PK_COMPANY').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_DEPARTMENT='+$('#PK_DEPARTMENT').val()+'&PK_EMPLOYEE_MASTER='+$('#PK_EMPLOYEE_MASTER').val()+'&CREATED_BY='+$('#CREATED_BY').val()+'&show_check=1&show_count=1&type=event'; 
						}
						else if( document.getElementById('REPORT_TYPE').value == 1 || document.getElementById('REPORT_TYPE').value == 4 )
						{
							if( document.getElementById('REPORT_TYPE').value == 1){
								var param  = 'emails';
							}
							else{
								var param  = 'loa';
							}
							var data  = '&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&show_check=1&show_count=1&type='+param; 
						}
						else if(document.getElementById('REPORT_TYPE').value == 6)
						{
							var data  = '&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_PROBATION_TYPE='+$('#PK_PROBATION_TYPE').val()+'&PK_PROBATION_LEVEL='+$('#PK_PROBATION_LEVEL').val()+'&PK_PROBATION_STATUS='+$('#PK_PROBATION_STATUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&show_check=1&show_count=1&type=probation'; 
						}
						else if(document.getElementById('REPORT_TYPE').value == 8)
						{
							var data  = '&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_DEPARTMENT='+$('#PK_DEPARTMENT').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&show_check=1&show_count=1&type=texts'; 
						}
							
						//alert(data)
						var value = $.ajax({
							url: "ajax_activity_report?",	
							type: "POST",		 
							data: data,		
							async: false,
							cache: false,
							success: function (data) {	
								document.getElementById('PHONE_DIV').innerHTML = data
								show_btn()
							}		
						}).responseText;
					});
				}
			}
		}
		/* Ticket # 1582 */
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
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_CAMPUS').val('').trigger("change");
		
		$('#PK_EMPLOYEE_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EMPLOYEE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=EMPLOYEE?> selected', //Ticket # 1593
			enableCaseInsensitiveFiltering: true, //Ticket # 1593
		});
		$('#PK_EMPLOYEE_MASTER').val('').trigger("change");
		
		$('#PK_DEPARTMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DEPARTMENT?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=DEPARTMENT?> selected'
		});
		$('#PK_DEPARTMENT').val('').trigger("change");
		
		$('#PK_NOTE_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EVENT_TYPE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=EVENT_TYPE?> selected'
		});
		$('#PK_NOTE_TYPE').val('').trigger("change");
		
		$('#PK_NOTE_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EVENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=EVENT_STATUS?> selected'
		});
		$('#PK_NOTE_STATUS').val('').trigger("change");
		
		$('#PK_EVENT_OTHER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EVENT_OTHER?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=EVENT_OTHER?> selected'
		});
		$('#PK_EVENT_OTHER').val('').trigger("change");
		
		$('#PK_TASK_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TASK_TYPE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=TASK_TYPE?> selected'
		});
		$('#PK_TASK_TYPE').val('').trigger("change");
		
		$('#PK_TASK_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TASK_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=TASK_STATUS?> selected'
		});
		$('#PK_TASK_STATUS').val('').trigger("change");
		
		/* Ticket # 1752  */
		$('#CREATED_BY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CREATED_BY?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CREATED_BY?> selected', //Ticket # 1593
			enableCaseInsensitiveFiltering: true, //Ticket # 1593
		});
		$('#CREATED_BY').val('').trigger("change");
		/* Ticket # 1752  */
		
		$('#PK_PROBATION_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROBATION_TYPE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROBATION_TYPE?> selected'
		});
		
		$('#PK_PROBATION_LEVEL').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROBATION_LEVEL?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROBATION_LEVEL?> selected'
		});
		
		$('#PK_PROBATION_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROBATION_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROBATION_STATUS?> selected'
		});
		
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});

		// DIAM-1183
		$('#PK_COMPANY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COMPANY?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=COMPANY?> selected'
		});
		// End DIAM-1183

		// DIAM-1279
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});

		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		// End DIAM-1279
		

	});
	</script>

</body>

</html>