<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_REQUEST)){

	$cond = "";
	if($_REQUEST['st'] != '' && $_REQUEST['et'] != '') {
		$ST = date("Y-m-d",strtotime($_REQUEST['st']));
		$ET = date("Y-m-d",strtotime($_REQUEST['et']));
		$cond .= " AND (SCHEDULE_DATE BETWEEN '$ST' AND '$ET')";
	} else if($_REQUEST['st'] != ''){
		$ST = date("Y-m-d",strtotime($_REQUEST['st']));
		$cond .= " AND (SCHEDULE_DATE >= '$ST') ";
	} else if($_REQUEST['et'] != ''){
		$ET = date("Y-m-d",strtotime($_REQUEST['et']));
		$cond .= " AND (SCHEDULE_DATE <= '$ET') ";
	}
	
	if($_REQUEST['ENROLLMENT_TYPE'] == 2) {
		$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
	}
	
	/*if($_REQUEST['exc_inactive'] == 1) {
		$cond .= " AND M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE != 7 ";
	}*/
	
	$PK_STUDENT_MASTER_arr = explode(",",$_REQUEST['s_id']);
	
	$query = "select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y &nbsp;&nbsp;%a'),'') AS SCHEDULE_DATE1, IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%Y-%m-%d  %a'),'') AS SCHEDULE_DATE2, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, IF(END_TIME != '00:00:00', DATE_FORMAT(END_TIME,'%h:%i %p'),'') AS END_TIME, IF(START_TIME != '00:00:00', DATE_FORMAT(START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, COURSE_CODE, TRANSCRIPT_CODE, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','N') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO,ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.CREATED_ON, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS CREATED_BY 
	from 

	S_STUDENT_SCHEDULE 
	LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
	LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
	LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
	LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE 
	LEFT JOIN Z_USER ON Z_USER.PK_USER = S_STUDENT_ATTENDANCE.CREATED_BY  
	LEFT JOIN S_EMPLOYEE_MASTER ON Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) 
	, S_STUDENT_ENROLLMENT

	WHERE 
	S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT  $cond ";
	$order = " ORDER BY SCHEDULE_DATE ASC, START_TIME ASC ";
	
	$present_att_code_arr = array();
	$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	while (!$res_present_att_code->EOF) {
		$present_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_present_att_code->MoveNext();
	}

	$exc_att_code_arr = array();
	$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	while (!$res_exc_att_code->EOF) {
		$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_exc_att_code->MoveNext();
	}
	
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_REQUEST['campus'])){
		$PK_CAMPUS 	 = $_REQUEST['PK_CAMPUS'];
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	//echo $cond;exit;
		
	if($_REQUEST['FORMAT'] == 1){
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
				
				$this->SetFont('helvetica', '', 8);
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
				$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(140);
				$this->Cell(55, 8, "Attendance Report", 0, false, 'L', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
				
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
		$pdf->SetMargins(7, 13, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 20);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 8, '', true);
		
		if($_REQUEST['ENROLLMENT_TYPE'] == 1)
			$ENROLL_TYPE = "All Enrollments";
		else
			$ENROLL_TYPE = "Current Enrollments";
		
		$INACTIVE_ATT_LBL = "Exclude Inactive Attendance Code: ";
		if($_REQUEST['exc_inactive'] == 1)
			$INACTIVE_ATT_LBL .= "Yes";
		else
			$INACTIVE_ATT_LBL .= "No";
					
		foreach($PK_STUDENT_MASTER_arr as $PK_STUDENT_MASTER) {
			$pdf->AddPage();
			
			$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,PROGRAM_TRANSCRIPT_CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
			
			$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 

			$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

			if($DATE_OF_BIRTH != '0000-00-00')
				$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
			else
				$DATE_OF_BIRTH = '';
				
			$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	
			
			$PK_STUDENT_ENROLLMENT = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
			$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' $camp_cond ");

			$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
			if($EXPECTED_GRAD_DATE != '0000-00-00')
				$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
			else
				$EXPECTED_GRAD_DATE = '';
			
			$txt = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
						<thead>
							<tr>
								<td width="100%" align="right">
									Campus: '.$res_camp_1->fields['CAMPUS_CODE'].'
								</td>
							</tr>
							<tr>
								<td width="100%" align="right">
									Between: '.date("m/d/Y",strtotime($_REQUEST['st']))." - ".date("m/d/Y",strtotime($_REQUEST['et'])).'
								</td>
							</tr>
							<tr>
								<td width="100%" align="right">'.$ENROLL_TYPE.'</td>
							</tr>
							<tr>
								<td width="100%" align="right">'.$INACTIVE_ATT_LBL.'<br /></td>
							</tr>
							<tr>
								<td width="75%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>'.$res->fields['LAST_NAME'].', '.$res->fields['FIRST_NAME'].' '.substr($res->fields['MIDDLE_NAME'], 0, 1).'</b></td>
							</tr>
							<tr>
								<td width="75%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">Program: '.$res_enroll->fields['PROGRAM_TRANSCRIPT_CODE'].' - '.$res_enroll->fields['DESCRIPTION'].'</td>
							</tr>
							<tr>
								<td width="25%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">ID: '.$res->fields['STUDENT_ID'].'<br />DOB: '.$DATE_OF_BIRTH.'<br />Phone: '.$res_address->fields['CELL_PHONE'].'</td>

								<td width="25%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;">Status: '.$res_enroll->fields['STUDENT_STATUS'].'<br />First Term: '.$res_enroll->fields['TERM_MASTER'].'<br />Exp. Grad: '.$EXPECTED_GRAD_DATE.'</td>
							
								<td width="25%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;">'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'<br />'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'<br />'.$res_address->fields['COUNTRY'].'</td>
							</tr>
							<tr>
								<br /><br />
								<td width="30%">
								</td>
								<td width="46%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Schedule</b>
								</td>
								<td width="24%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;">
									<b>Attendance</b>
								</td>
							</tr>
							<tr>
								<td width="18%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Course</b>
								</td>
								<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Type</b>
								</td>
								<td width="13%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Class Date</b>
								</td>
								<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Start Time</b>
								</td>
								<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>End Time</b>
								</td>
								<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Hours</b>
								</td>
								<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Complete</b>
								</td>
								<td width="5%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Code</b>
								</td>
								<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
									<b>Hours</b>
								</td>
								<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >
									<b>Cum.</b>
								</td>
							</tr>
						</thead>
						<tbody>';
				//echo $query." AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ".$order;exit;
					$res_course_schedule = $db->Execute($query." AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ".$order);

					$total_scheduled 			= 0;
					$total_completed_scheduled 	= 0;
					$total_attended 			= 0;
					$cum_total					= 0;
					while (!$res_course_schedule->EOF) {
					
						if($_REQUEST['exc_inactive'] == 0 || ($_REQUEST['exc_inactive'] == 1 && $res_course_schedule->fields['PK_ATTENDANCE_CODE'] != 7 )) {

							$exc_att_flag = 0;
							foreach($exc_att_code_arr as $exc_att_code) {
								if($exc_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
									$exc_att_flag = 1;
									break;
								}
							}
							
							$present_flag = 0;
							foreach($present_att_code_arr as $present_att_code) {
								if($present_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
									$present_flag = 1;
									break;
								}
							}
							
							if(($res_course_schedule->fields['COMPLETED'] == 'Y' && $present_flag == 1) || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 )
								$total_attended += $res_course_schedule->fields['ATTENDANCE_HOURS'];
							
							if($exc_att_flag == 0)
								$total_scheduled += $res_course_schedule->fields['HOURS'];
								
							if($res_course_schedule->fields['COMPLETED'] == 'Y' && $exc_att_flag == 0){
								$total_completed_scheduled 	+= $res_course_schedule->fields['HOURS'];
								
								if($present_flag == 1)
									$cum_total	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
							}
							
							if($res_course_schedule->fields['COMPLETED'] == 'N') {
								//$ATTENDANCE_CODE = 'P';
								$ATTENDANCE_CODE  = '';
								$ATTENDANCE_HOURS = 0; 
							} else {
								$ATTENDANCE_CODE  = $res_course_schedule->fields['ATTENDANCE_CODE'];
								$ATTENDANCE_HOURS = $res_course_schedule->fields['ATTENDANCE_HOURS'];
							}
							$txt .= '<tr nobr="true" >
									<td width="18%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.$res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')'.'
									</td>
									<td width="12%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.$res_course_schedule->fields['SCHEDULE_TYPE'].'
									</td>
									<td width="13%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.$res_course_schedule->fields['SCHEDULE_DATE1'].'
									</td>
									<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.$res_course_schedule->fields['START_TIME'].'
									</td>
									<td width="8%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.$res_course_schedule->fields['END_TIME'].'
									</td>
									<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.number_format_value_checker($res_course_schedule->fields['HOURS'],2).'
									</td>
									<td width="8%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.$res_course_schedule->fields['COMPLETED'].'
									</td>
									<td width="5%" align="center" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.$ATTENDANCE_CODE.'
									</td>
									<td width="9%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;">
										'.number_format_value_checker($ATTENDANCE_HOURS,2).'
									</td>
									<td width="10%" align="right" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" >
										'.number_format_value_checker($cum_total,2).'
									</td>
								</tr>';
						}
						$res_course_schedule->MoveNext();
					}
					
					$txt .= '<tr>
								<td width="30%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;"><b>Total Scheduled: '.number_format_value_checker($total_scheduled,2).'</b></td>
								<td width="70%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-bottom:0.5px solid #000;;border-right:0.5px solid #000;">
									<b>Totals for Completed Attendance &nbsp;&nbsp;&nbsp;&nbsp;
									Scheduled: '.number_format_value_checker($total_completed_scheduled,2).'&nbsp;&nbsp;&nbsp;&nbsp;Attended: '.number_format_value_checker($total_attended,2).'</b>
									&nbsp;&nbsp;&nbsp;&nbsp;
									<b>Percentage:  '.number_format_value_checker((($total_attended/$total_completed_scheduled) * 100),2).' %</b>
								</td>
							</tr>
						</tbody>
					</table>';
					
				//echo $txt;exit;
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		}

		$file_name = 'Attendance_Report_By_Date_Range_'.uniqid().'.pdf';
		$outputFileName =$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
		$outputFileName );  
		/*
		if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');
		*/	
		
		if($_REQUEST['output_file_loc_in_json'] == 'yes'){
			$pdf->Output('temp/'.$outputFileName, 'F');
			$file_nm = str_replace($dir , '' ,$outputFileName);
			echo json_encode(['filename'=> $outputFileName ,'path'=>'temp/'.$outputFileName]);
			return;
		}
		$pdf->Output('temp/'.$outputFileName, 'FD');
		return $outputFileName;	

		/////////////////////////////////////////////////////////////////
	} else if($_REQUEST['FORMAT'] == 2){
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
		$file_name 		= 'Attendance Report By Date Range.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName);  

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
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Schedule');
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = 'H1:L1';
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

		$cell_no = 'M1';
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue('Attendance');
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
		$marge_cells = 'M1:Q1';
		$objPHPExcel->getActiveSheet()->mergeCells($marge_cells);
		$objPHPExcel->getActiveSheet()->getStyle($marge_cells)->applyFromArray($style);

		$line++;
		$index 	= -1;

		$heading[] = 'Name';
		$width[]   = 20;
		$heading[] = 'ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Exp Grad Date';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;		

		$heading[] = 'Course';
		$width[]   = 20;
		$heading[] = 'Type';
		$width[]   = 20;
		$heading[] = 'Class Date';
		$width[]   = 20;
		$heading[] = 'Start Time';
		$width[]   = 20;
		$heading[] = 'End Time';
		$width[]   = 20;
		$heading[] = 'Hours';
		$width[]   = 20;
		$heading[] = 'Complete';
		$width[]   = 20;
		$heading[] = 'Code';
		$width[]   = 20;
		$heading[] = 'Hours';
		$width[]   = 20;
		$heading[] = 'Cumulative';
		$width[]   = 20;
		$heading[] = 'Created By';
		$width[]   = 20;
		$heading[] = 'Date/Time Stamp';
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

		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}

		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$TIMEZONE = $res->fields['TIMEZONE'];

		$objPHPExcel->getActiveSheet()->freezePane('A2');

		foreach($PK_STUDENT_MASTER_arr as $PK_STUDENT_MASTER) {
			$klm++;
			$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = 1 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
			
			$PK_STUDENT_MASTER  = $res_enroll->fields['PK_STUDENT_MASTER'];
			
			$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 

			$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

			if($DATE_OF_BIRTH != '0000-00-00')
				$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
			else
				$DATE_OF_BIRTH = '';
				
			$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

			$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
			if($EXPECTED_GRAD_DATE != '0000-00-00')
				$EXPECTED_GRAD_DATE = date("Y-m-d",strtotime($EXPECTED_GRAD_DATE));
			else
				$EXPECTED_GRAD_DATE = '';
			
			$res_course_schedule = $db->Execute($query." AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ".$order);

			$total_scheduled 			= 0;
			$total_completed_scheduled 	= 0;
			$total_attended 			= 0;
			$cum_total					= 0;
			
			while (!$res_course_schedule->EOF) {

				if($_REQUEST['exc_inactive'] == 0 || ($_REQUEST['exc_inactive'] == 1 && $res_course_schedule->fields['PK_ATTENDANCE_CODE'] != 7 )) {
					$line++;
					$index = -1;
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LAST_NAME'].', '.$res->fields['FIRST_NAME'].' '.substr($res->fields['MIDDLE_NAME'], 0, 1));
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
					
					$PK_STUDENT_ENROLLMENT = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
					$res_camp_1 = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' $camp_cond ");
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_camp_1->fields['CAMPUS_CODE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EXPECTED_GRAD_DATE);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['TERM_MASTER']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['CODE'].' - '.$res_enroll->fields['DESCRIPTION']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_enroll->fields['STUDENT_STATUS']);
					
					if(($res_course_schedule->fields['COMPLETED'] == 'Y' && $res_course_schedule->fields['ATTENDANCE_CODE'] == 'P') || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 )
						$total_attended += $res_course_schedule->fields['ATTENDANCE_HOURS'];
					
					$total_scheduled += $res_course_schedule->fields['HOURS'];
					if($res_course_schedule->fields['COMPLETED'] == 'Y') {
						$total_completed_scheduled 	+= $res_course_schedule->fields['HOURS'];
						$cum_total					+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
					}
					
					if($res_course_schedule->fields['COMPLETED'] == 'N') {
						//$ATTENDANCE_CODE = 'P';
						$ATTENDANCE_CODE  = '';
						$ATTENDANCE_HOURS = 0; 
					} else {
						$ATTENDANCE_CODE  = $res_course_schedule->fields['ATTENDANCE_CODE'];
						$ATTENDANCE_HOURS = $res_course_schedule->fields['ATTENDANCE_HOURS'];
					}
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['TRANSCRIPT_CODE'].' ('. substr($res_course_schedule->fields['SESSION'],0,1).' - '. $res_course_schedule->fields['SESSION_NO'].')');
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SCHEDULE_TYPE']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['SCHEDULE_DATE2']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['START_TIME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['END_TIME']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_course_schedule->fields['HOURS'],2));
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['COMPLETED']);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ATTENDANCE_CODE);
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($ATTENDANCE_HOURS,2));
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($cum_total,2));
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_course_schedule->fields['CREATED_BY']);
					
					if($res_course_schedule->fields['CREATED_ON'] != '0000-00-00 00:00:00' && $res_course_schedule->fields['CREATED_ON'] != '') {
						$date = convert_to_user_date($res_course_schedule->fields['CREATED_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
						
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($date);
					}
				}
				
				$res_course_schedule->MoveNext();
			}
		}

		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		if($_REQUEST['output_file_loc_in_json'] == 'yes'){
			$file_nm = str_replace($dir , '' ,$outputFileName);
			echo json_encode(['filename'=> $file_nm ,'path'=>$outputFileName]);
			return;
		}
		header("location:".$outputFileName);
	}

}