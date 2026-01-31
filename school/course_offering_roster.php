<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

/* Ticket #1225 */
if(!empty($_POST) || $_GET['p'] == 'r'){
	if($_GET['p'] == 'r') {
		$_POST['PK_TERM_MASTER']	= $_GET['tm'];
		$_POST['FORMAT']			= $_GET['FORMAT'];
	}

	$PK_COURSE_OFFERING_ARR = explode(",",$_GET['co_id']);

	$query = "SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) as STUD_NAME, STUDENT_ID, HOME_PHONE, CELL_PHONE, WORK_PHONE, EMAIL, COURSE_OFFERING_STUDENT_STATUS 
	FROM
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' 
	, S_STUDENT_ACADEMICS, S_STUDENT_COURSE  
	LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER ";
	
	$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
	$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC ";
	
	$cs_query = "SELECT DATE_FORMAT(DEF_START_TIME,'%h:%i %p') AS START_TIME, DATE_FORMAT(DEF_END_TIME,'%h:%i %p') AS END_TIME, HOURS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1 , IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%Y-%m-%d' )) AS  END_DATE_1,SESSION,SESSION_NO, COURSE_OFFERING_STATUS, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(S_COURSE_OFFERING_SCHEDULE.START_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE, '%m/%d/%Y' )) AS START_DATE, IF(S_COURSE_OFFERING_SCHEDULE.END_DATE = '0000-00-00','',DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.END_DATE, '%m/%d/%Y' )) AS END_DATE, DEF_HOURS, CAMPUS_CODE, S_COURSE_OFFERING.CO_EXTERNAL_ID AS EXTERNAL_ID  from 
	S_COURSE_OFFERING 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS
	LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
	LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
	LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR 
	LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM
	LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "; // DIAM-2429
	
	$cs_sch_query = "SELECT DATE_FORMAT(MIN(SCHEDULE_DATE), '%m/%d/%Y' ) as START_DATE, DATE_FORMAT(MAX(SCHEDULE_DATE), '%m/%d/%Y' ) as END_DATE, COUNT(PK_COURSE_OFFERING_SCHEDULE_DETAIL) as MEETING_COUNT FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE  ACTIVE = 1 ";
	
	if($_POST['FORMAT'] == 1){
		/////////////////////////////////////////////////////////////////
		
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
						<td width="45%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
						<td width="35%" valign="top" >
							<table width="100%" >
								<tr>
									<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Course Offering Roster</b></td>
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
			'default_font_size' => 8,
			'format' => [210, 296],
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$txt   = "";
		foreach($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING){
			$mpdf->AddPage();
			
			$res_cs = $db->Execute($cs_query." AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
		
			$res_cs_sch = $db->Execute($cs_sch_query." AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
			
			$txt = '<table border="1" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td align="center" width="25%" ><b>Course: '.$res_cs->fields['TRANSCRIPT_CODE'].'</b><br />'.$res_cs->fields['COURSE_DESCRIPTION'].'</td>
							<td align="center" width="20%" ><b>Term Date</b><br />'.$res_cs->fields['BEGIN_DATE_1'].' - '.$res_cs->fields['END_DATE_1'].'</td>
							<td align="center" width="25%" ><b>Instructor</b><br />'.$res_cs->fields['INSTRUCTOR_NAME'].'</td>
							<td align="center" width="20%"><b>Room</b><br />'.$res_cs->fields['ROOM_NO'].'</td>
							<td align="center" width="10%"><b>Attendance</b><br />'.$res_cs->fields['ATTENDANCE_TYPE'].'</td>
						</tr>
						<tr>
							<td colspan="5" >
								<b>Course: </b>'.$res_cs_sch->fields['START_DATE'].' to '.$res_cs_sch->fields['END_DATE'].' - '.$res_cs_sch->fields['MEETING_COUNT'].' Meetings
								<br /><b>Class: </b>'.$res_cs->fields['START_TIME'].' to '.$res_cs->fields['END_TIME'].' - '.$res_cs->fields['DEF_HOURS'].' hours
								<br /><b>External ID: </b>'.$res_cs->fields['EXTERNAL_ID'].'
							</td>
						</tr>
					</table>';
					
				$txt .= '<br /><br /><br />';
				$txt .='<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><b>Student</b></td>
									<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Course Offering<br />Student Status</b></td>
									<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><b>Student ID</b></td>
									<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><b>Home Phone</b></td>
									<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><b>Mobile</b></td>
									<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><b>Work Phone</b></td>
									<td width="16%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><b>Email</b></td>
								</tr>
							</thead>';
				
			$cond1 = " AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ";

			$res_stud = $db->Execute($query." ".$cond1." ".$group_by." ".$order_by);
			while (!$res_stud->EOF) {
				$txt 	.= '<tr>
							<td >'.$res_stud->fields['STUD_NAME'].'</td>
							<td >'.$res_stud->fields['COURSE_OFFERING_STUDENT_STATUS'].'</td>
							<td width="15%" >'.$res_stud->fields['STUDENT_ID'].'</td>
						
							<td >'.$res_stud->fields['HOME_PHONE'].'</td>
							<td >'.$res_stud->fields['CELL_PHONE'].'</td>
							<td >'.$res_stud->fields['WORK_PHONE'].'</td>
							<td >'.$res_stud->fields['EMAIL'].'</td>
						</tr>';
					
				$res_stud->MoveNext();
			}
			$txt 	.= '</table>';
			
			$mpdf->WriteHTML($txt);
		}
		
		$file_name = 'Course_Offering_Roster_'.uniqid().'.pdf';
		$mpdf->Output($file_name, 'D');
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

		$file_name 		= "Course Offering Roster.xlsx";
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line = 1;
		$index 	= -1;
		$heading[] = 'Campus';
		$width[]   = 15;
		$heading[] = 'Course';
		$width[]   = 15;
		$heading[] = 'Term Begin Date';
		$width[]   = 15;
		$heading[] = 'Instructor';
		$width[]   = 15;
		$heading[] = 'Room';
		$width[]   = 15;
		$heading[] = 'Attendance';
		$width[]   = 15;
		$heading[] = 'Student';
		$width[]   = 15;
		$heading[] = 'Course Offering Student Status';
		$width[]   = 15;
		$heading[] = 'Student ID';
		$width[]   = 15;
		$heading[] = 'Home Phone';
		$width[]   = 15;
		$heading[] = 'Mobile';
		$width[]   = 15;
		$heading[] = 'Work Phone';
		$width[]   = 15;
		$heading[] = 'Email';
		$width[]   = 15;
		$heading[] = 'External ID'; // DIAM-2429
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

		foreach($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING){
			$res_cs = $db->Execute($cs_query." AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
		
		
			$res_cs_sch = $db->Execute($cs_sch_query." AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
			
			$cond1 = " AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ";

			$res_stud = $db->Execute($query." ".$cond1." ".$group_by." ".$order_by);
			while (!$res_stud->EOF) {
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cs->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cs->fields['TRANSCRIPT_CODE'].' - '.$res_cs->fields['COURSE_DESCRIPTION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cs->fields['BEGIN_DATE_1'].' - '.$res_cs->fields['END_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cs->fields['INSTRUCTOR_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cs->fields['ROOM_NO']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cs->fields['ATTENDANCE_TYPE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUD_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['COURSE_OFFERING_STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['HOME_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CELL_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['WORK_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['EMAIL']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cs->fields['EXTERNAL_ID']); // DIAM-2429
				
				$res_stud->MoveNext();
			}
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
		exit;
	}
} ?>