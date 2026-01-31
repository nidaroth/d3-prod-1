<?php ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require_once('../global/config.php');
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once("check_access.php");
require_once("function_transcript_header.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		if($_SESSION['temp_id'] == $this->PK_STUDENT_ENROLLMENT){
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(5);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(150);
			$this->Cell(55, 8, "Student Transcript", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		} else 
			$_SESSION['temp_id'] = $this->PK_STUDENT_ENROLLMENT;
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(12);
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
		
		//Ticket # 936
		if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])){ 
		$this->SetY(-15);
		$this->SetX(100);
		$this->SetFont('helvetica', 'I', 8);
		$official_signature='';
		if($_POST['show_signature_line']=='yes'){
			$official_signature = 'Official Signature: ____________________';			
		}

		$student_signature='';
		if($_POST['show_student_signature_line']=='yes'){
			$student_signature = 'Student Signature: ____________________';
		}

		$this->Cell(30, 10,$official_signature.'     '.$student_signature, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		if($_POST['show_signature_line']=='yes'){
			$image_file = "../assets/images/signature/focus/Joe - High Res.png";
			$this->Image($image_file,80, 280, 30, 15, '', 'T', 'M');
		}
	
	}
		//Ticket # 936

    }
}

$_SESSION['temp_id'] = '';
//Ticket # 936
if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])){ 
	require_once("pdf_custom_sap_header.php"); //Ticket # 936
//Ticket # 936	
}else{
	require_once("pdf_custom_header.php"); //Ticket # 1588
}
//Ticket # 936
function co_grade_book_progress_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf, $report_name = null){
	global $db;
	
	//$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);
	$PK_STUDENT_MASTER_ARR = $PK_STUDENT_MASTERS;
	$report_option="";
	if($_POST['REPORT_OPTION']==3){

		$TERM_START_DATE = date('m/d/Y',strtotime($_POST['MIDPOINT_START_DATE']));
		$TERM_END_DATE   = date('m/d/Y',strtotime($_POST['MIDPOINT_END_DATE']));

		if(!empty($TERM_START_DATE) && !empty($TERM_END_DATE))
			$report_option="Terms: ".$TERM_START_DATE." - ".$TERM_END_DATE;

	}else if($_POST['REPORT_OPTION']==2){
		$report_option="Current Enrollment";
	}else{
		$report_option="All Enrollments";

	}
	//$_GET['report_type'] = $_POST['REPORT_OPTION'];
	$_GET['show'] = $_POST['show'];
	$_GET['exclude_tc'] = $_POST['exclude_tc'];

	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 15, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, 20);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 8, '', true);

	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$LOGO = '';
	if($res->fields['PDF_LOGO'] != '')
		$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';
	



	$def_grade = 0;
	$res_def_grade = $db->Execute("SELECT PK_GRADE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_DEFAULT = 1");
	if($res_def_grade->RecordCount() > 0)
		$def_grade = $res_def_grade->fields['PK_GRADE'];
		
	$show_cond = "";
	$in_progress="";
	if($_GET['show'] == 2){
		$show_cond = " AND (FINAL_GRADE > 0 AND FINAL_GRADE != '$def_grade' ) "; 
		$in_progress="AND S_STUDENT_GRADE.POINTS != ''";

	}
	else if($_GET['show'] == 3) {
		$in_progress="";
		if($def_grade == 0)
			$show_cond = " AND FINAL_GRADE = 0 ";
		else
			$show_cond = " AND (FINAL_GRADE = 0 OR FINAL_GRADE = '$def_grade' ) ";
	}

	// ATTENDANCE BOX
	$show_cond_1 = "";
	$in_progress_1="";
	if($_GET['show'] == 2 || $_GET['show'] == 3 ){
		$show_cond_1 = "";
		$in_progress_1= "";
	}
	

	/* Ticket #1145 */
	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$exc_att_code_arr = array();
	$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	while (!$res_exc_att_code->EOF) {
		$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_exc_att_code->MoveNext();
	}
	/* Ticket #1145 */
	
	/* Ticket #1170 */
	if($_GET['report_type'] == '')
		$_GET['report_type'] = 1;

	if($_GET['report_type'] == 1) {
		$border_1 = "border-top:1px solid #000;";
	} else {
		$border_1 = "";
	}

	/* Ticket #1170 */

	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER=>$enrollment) {

		
		$cond = "";
		$cond1 ="";
		$cond2 ="";
		$PK_STUDENT_ENROLLMENT=implode(',',$enrollment);

		if($_POST['REPORT_OPTION'] == 1) {
			$label = "All Enrollments";
			$cond  = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN (".$PK_STUDENT_ENROLLMENT.") ";
			$cond1 = " AND PK_STUDENT_ENROLLMENT IN (".$PK_STUDENT_ENROLLMENT.") ";
			$cond2 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (".$PK_STUDENT_ENROLLMENT.") ";
			
		} else if($_POST['REPORT_OPTION'] == 2) {
			// $res_en11 = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_GET[id]' AND IS_ACTIVE_ENROLLMENT = 1");
			
			$label = "Current Enrollment";
			// $cond  = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '".$res_en11->fields['PK_STUDENT_ENROLLMENT']."' ";
			// $cond1 = " AND PK_STUDENT_ENROLLMENT = '".$res_en11->fields['PK_STUDENT_ENROLLMENT']."' ";
			// $cond2 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '".$res_en11->fields['PK_STUDENT_ENROLLMENT']."' ";
			$cond  = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN (".$PK_STUDENT_ENROLLMENT.") ";
			$cond1 = " AND PK_STUDENT_ENROLLMENT IN (".$PK_STUDENT_ENROLLMENT.") ";
			$cond2 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (".$PK_STUDENT_ENROLLMENT.") ";
		} else if($_POST['REPORT_OPTION'] == 3) {
			$label = "By Term";
			// $cond  = " AND S_COURSE_OFFERING.PK_TERM_MASTER = '$_GET[term]' ";
			// $cond1 = " AND PK_TERM_MASTER = '$_GET[term]' ";
			// $cond2 = " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER = '$_GET[term]' ";
			$TERM_START_DATE = date('Y-m-d',strtotime($_POST['MIDPOINT_START_DATE']));
			$TERM_END_DATE   = date('Y-m-d',strtotime($_POST['MIDPOINT_END_DATE']));
	
			$cond  = " AND S_TERM_MASTER.BEGIN_DATE BETWEEN '$TERM_START_DATE' AND '$TERM_END_DATE' ";
			$cond1 = " AND S_TERM_MASTER.BEGIN_DATE BETWEEN '$TERM_START_DATE' AND '$TERM_END_DATE' ";
			$cond2 = " AND S_TERM_MASTER.BEGIN_DATE BETWEEN '$TERM_START_DATE' AND '$TERM_END_DATE' ";
		}
	

		
		$en_cond = "";
		if($_GET['eid'] != ''){
			$en_cond = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		}
		
		$res_stu = $db->Execute("select FIRST_NAME, LAST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
		
		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		
		$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588
		//Ticket # 936
		$res_type = $db->Execute("SELECT M_CAMPUS_PROGRAM.CODE,M_CAMPUS_PROGRAM.DESCRIPTION,PK_STUDENT_ENROLLMENT, STUDENT_STATUS, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE,SESSION, BEGIN_DATE as BEGIN_DATE_1, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond $cond2 ORDER By BEGIN_DATE_1 ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");
		
		$pdf->STUD_NAME 			= $res_stu->fields['NAME'];
		$pdf->PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
		$pdf->startPageGroup();
		$pdf->AddPage();
		
		
		$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
		/* Ticket # 1588 */
		//Ticket # 936
		$table_td = '';
		if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])){ 

			if(empty($res_add->fields['EMAIL']))
			$res_add->fields['EMAIL'] = $res_add->fields['EMAIL_OTHER'];

			if(empty($res_add->fields['CELL_PHONE']))
			$res_add->fields['CELL_PHONE'] = $res_add->fields['OTHER_PHONE'];
			
			$table_td .= '<td style="width:50%" >
			<span style="line-height:5px" >';

			 if(!empty($res_add->fields['EMAIL']))
			 $table_td .= 'Email: '.$res_add->fields['EMAIL'].'<br/>';
			
			if(!empty($res_stu->fields['STUDENT_ID']))
			$table_td .= 'Student ID: '.$res_stu->fields['STUDENT_ID'].'<br/>';

			if(!empty($res_stu->fields['DOB']))
			$table_td .= 'DOB: '.date('m/d/Y',strtotime($res_stu->fields['DOB'])).'<br/>';

			if(!empty($res_add->fields['CELL_PHONE']))
			$table_td .= 'Phone: '.$res_add->fields['CELL_PHONE'];

			$table_td .= '</span>
			</td>';
			
			$thead_start = '<thead>';
			$thead_end = '</thead>';
		}
		//Ticket # 936

		//Ticket # 936
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td width="50%">'.$CONTENT.'</td>
						<td width="50%">
							<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								<tr>
									<td colspan="2">
										<b style="font-size:50px" >'.$res_stu->fields['NAME'].'</b><br />
									</td>
								</tr>
								<tr>
									<td>
										<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
									</td>'.$table_td.'					
								</tr>';
		/* Ticket # 1588 */
								
								while (!$res_type->EOF) {
									$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
									$PK_STUDENT_ENROLLMENT2 = $res_type->fields['PK_STUDENT_ENROLLMENT'];
									$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
									
									//Ticket # 936
									if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])){ 
										
										if($res_report_header->fields['BOX_1']=='LDA')
											$res_report_header->fields['BOX_1'] = "";

										if($res_report_header->fields['BOX_4']=='LDA')
											$res_report_header->fields['BOX_4'] = "";

										if($res_report_header->fields['BOX_7']=='LDA')
											$res_report_header->fields['BOX_7'] = "";

										if($res_report_header->fields['BOX_2']=='LDA')
											$res_report_header->fields['BOX_2'] = "";

										if($res_report_header->fields['BOX_5']=='LDA')
											$res_report_header->fields['BOX_5'] = "";

										if($res_report_header->fields['BOX_8']=='LDA')
											$res_report_header->fields['BOX_8'] = "";

										if($res_report_header->fields['BOX_3']=='LDA')
											$res_report_header->fields['BOX_3'] = "";

										if($res_report_header->fields['BOX_6']=='LDA')
											$res_report_header->fields['BOX_6'] = "";

										if($res_report_header->fields['BOX_9']=='LDA')
											$res_report_header->fields['BOX_9'] = "";
									
									}
									//Ticket # 936
									$custom_program="";
									$custom_program_sap="";
									$custom_program_colour = '#c0c0c0';
									if($res_report_header->fields['BOX_1']=="PROGRAM_CODE" && has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1){
										$custom_program_sap='<tr><td width="100%" style="border-top:0.5px solid #c0c0c0" width="100%">
										'.transcript_header('PROGRAM_CODE_DESCRIPTION', " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
									</td></tr>';
										$custom_program_colour = '#fff';
										}else{
											$custom_program='<td style="border-top:0.5px solid #c0c0c0" width="34%" >
											'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
										</td>';										
										}
									$txt .= $custom_program_sap.'<tr>
												'.$custom_program.'
												<td style="border-top:0.5px solid '.$custom_program_colour .'" width="100%" width="34%" >
													'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
												<td style="border-top:0.5px solid '.$custom_program_colour .'" width="100%" width="32%" >
													'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
											</tr>
											<tr>
												<td width="34%" >
													'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
												<td  width="34%" >
													'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
												<td  width="32%" >
													'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
											</tr>
											<tr>
												<td width="34%" >
													'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
												<td  width="34%" >
													'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
												<td  width="32%" width="32%"  >
													'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
												</td>
											</tr>';
									$res_type->MoveNext();
								}
					$txt .= '</table>
						</td>
					</tr>
				</table>';
		
		$txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>
				<table border="0" cellspacing="0" cellpadding="3" width="100%" >
				<tr>
				<td width="100%" align="center" ><b><i style="font-size:50px">Student Progress Report: '.$report_option.' </i></b><br /></td>
			</tr>
				</table>
				<br /><br />';
				
				$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS 
				FROM 
				S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE 
				WHERE 
				S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND 
				PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $in_progress");
				if($res_stu_point->RecordCount() > 0) {

					$res_term = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 
					FROM
					S_STUDENT_COURSE, S_COURSE_OFFERING, S_TERM_MASTER, S_COURSE_OFFERING_GRADE, M_GRADE_BOOK_TYPE
					WHERE 
					S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
					S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
					S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
					S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
					S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE $cond  $show_cond 
					GROUP BY  S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
					
					if($res_term->RecordCount() > 0) {		
						/* Ticket # 1219   */
						$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >'.$thead_start.'
									<tr>
										<td width="10%" ></td>';
										if($_GET['report_type'] == 1) {
											$txt .= '<td width="15%" ><b><u>Type</u></b></td>
													<td width="20%" ><b><u>Description</u></b></td>';				
										} else {
											$txt .= '<td width="20%" ></td>';
										}
								$txt .= '<td width="15%" align="right" ><b><u>Points Earned</u></b></td>
										<td width="15%" align="right" ><b><u>Total Points</u></b></td>
										<td width="15%" align="right" ><b><u>Percentage Earned</u></b></td>
										<td width="10%"></td>
									</tr>
									<tr><td width="100%" ></td></tr>
									'.$thead_end;
									
							/* Ticket # 1219   */

							if($_POST['REPORT_OPTION'] == 3) {
								$cond11 = '';
							}else{
								$cond11 = $cond1;
							}
							
							while (!$res_term->EOF) { 
								$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];

								$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS 
								FROM 
								S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE, S_COURSE_OFFERING 
								WHERE 
								S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
								S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $in_progress $cond11 ");
								
								if($res_stu_point->RecordCount() > 0) {
									/*$txt .= '<tr>
												<td width="100%" ><b style="font-size:40px" ><i>Term: '.$res_term->fields['BEGIN_DATE_1'].'</i></b></td>
											</tr>';*/
									
											if($_POST['REPORT_OPTION'] == 3) {
												$res_course_cond = '';
											}else{
												$res_course_cond = $cond;
											}

									$res_course = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, S_COURSE_OFFERING.PK_COURSE, TRANSCRIPT_CODE, COURSE_DESCRIPTION   
									FROM
									S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE_OFFERING_GRADE, S_COURSE , M_GRADE_BOOK_TYPE
									WHERE 
									S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
									S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
									S_COURSE_OFFERING.PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
									S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
									S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
									S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
									S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
									S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE $res_course_cond  $show_cond 
									GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER BY TRANSCRIPT_CODE ASC");
									
									if($res_course->RecordCount() > 0) {
										$txt .= '<tr>
													<td width="100%" ><b style="font-size:40px" ><i>Term: '.$res_term->fields['BEGIN_DATE_1'].'</i></b></td>
												</tr>';
									}
									
									while (!$res_course->EOF) { 
									
										$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
										
										$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS 
										FROM 
										S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE 
										WHERE 
										S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND 
										S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $in_progress");
										if($res_stu_point->RecordCount() > 0) {
											$txt .= '<tr>
												<td width="100%" ><b style="font-size:40px" ><i>'.$res_course->fields['TRANSCRIPT_CODE'].' - '.$res_course->fields['COURSE_DESCRIPTION'].'</i></b></td>
											</tr>';
											
											$flag 	= 0;
											$iii 	= 0;
											$TOT_STUD_WEIGHTED_POINTS 	= 0;
											$TOT_CO_WEIGHTED_POINTS 	= 0;
											//DIAM-936
											if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1){
												$ORDER_BY="FIELD(GRADE_BOOK_TYPE, 'Exam', 'Practical Eval','Homework','Participation','Professionalism','Final Eval','Final')";
											}else{
												$ORDER_BY ="GRADE_BOOK_TYPE ASC";
											}
											
											$res_test_type = $db->Execute("select S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE, GRADE_BOOK_TYPE   
											FROM
											S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE_OFFERING_GRADE, S_COURSE , M_GRADE_BOOK_TYPE
											WHERE 
											S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
											S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
											S_COURSE_OFFERING.PK_TERM_MASTER = '$PK_TERM_MASTER' AND 
											S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
											S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
											S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
											S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
											S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE  $show_cond 
											GROUP BY S_COURSE_OFFERING_GRADE.PK_GRADE_BOOK_TYPE ORDER BY $ORDER_BY");
											while (!$res_test_type->EOF) { 
												$iii++;
												$PK_GRADE_BOOK_TYPE = $res_test_type->fields['PK_GRADE_BOOK_TYPE'];
												
												$res_stu_point = $db->Execute("SELECT S_COURSE_OFFERING_GRADE.POINTS AS CO_POINTS, S_COURSE_OFFERING_GRADE.WEIGHT AS CO_WEIGHT , S_STUDENT_GRADE.POINTS STUD_POINTS, S_COURSE_OFFERING_GRADE.DESCRIPTION 
												FROM 
												S_STUDENT_GRADE, S_COURSE_OFFERING_GRADE 
												WHERE 
												PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND 
												S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE AND 
												S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $in_progress");
												if($res_stu_point->RecordCount() > 0){
													$flag = 1;
													$STUD_POINTS 	= 0;
													$CO_POINTS 		= 0;
													$CO_WEIGHT 		= 0;
													
													$SUB_TOT_STUD_WEIGHTED_POINTS 	= 0;
													$SUB_TOT_CO_WEIGHTED_POINTS 	= 0;
													
													$jj = 0;
													while (!$res_stu_point->EOF) { 
														$jj++;
														if($jj == 1)
															$GRADE_BOOK_TYPE = $res_test_type->fields['GRADE_BOOK_TYPE'];
														//else
															//$GRADE_BOOK_TYPE = '';
														
														$STUD_POINTS 	+= $res_stu_point->fields['STUD_POINTS'];
														$CO_POINTS 		+= $res_stu_point->fields['CO_POINTS'];
														$CO_WEIGHT 		+= $res_stu_point->fields['CO_WEIGHT'];
														
														$STUD_WEIGHTED_POINTS 	= $res_stu_point->fields['STUD_POINTS'] * $res_stu_point->fields['CO_WEIGHT'];
														$CO_WEIGHTED_POINTS		= $res_stu_point->fields['CO_POINTS'] * $res_stu_point->fields['CO_WEIGHT'];
														
														if($res_stu_point->fields['STUD_POINTS']!="")
														{
															$TOT_STUD_WEIGHTED_POINTS 	+= $STUD_WEIGHTED_POINTS;
															$TOT_CO_WEIGHTED_POINTS 	+= $CO_WEIGHTED_POINTS;														
															$SUB_TOT_STUD_WEIGHTED_POINTS += $STUD_WEIGHTED_POINTS;
															$SUB_TOT_CO_WEIGHTED_POINTS   += $CO_WEIGHTED_POINTS;
														}

														/** DIAM-1182 **/
														// $Points_Earned = number_format_value_checker($STUD_WEIGHTED_POINTS,2);
														// $Final_Points_Earn = '';
														// if($Points_Earned != "" && $Points_Earned != 0.00)
														// {
														// 	$Final_Points_Earn = $Points_Earned;
														// }

														// $Sub_Tot_Weighted_Points_Earned = number_format_value_checker($SUB_TOT_STUD_WEIGHTED_POINTS,2);
														// $Final_Sub_Tot_Weighted_Points_Earned = '';
														// if($Sub_Tot_Weighted_Points_Earned != "" && $Sub_Tot_Weighted_Points_Earned != 0.00)
														// {
														// 	$Final_Sub_Tot_Weighted_Points_Earned = $Sub_Tot_Weighted_Points_Earned;
														// }
														/** End DIAM-1182 **/
														
														/* Ticket 1170 */
														if($_GET['report_type'] == 1) {
															$txt .= '<tr>
																		<td width="10%" ></td>
																		<td width="15%" >'.$GRADE_BOOK_TYPE.'</td>
																		<td width="20%" >'.$res_stu_point->fields['DESCRIPTION'].'</td>
																		<td width="15%" align="right" >'. ($res_stu_point->fields['STUD_POINTS']!=""?number_format_value_checker($STUD_WEIGHTED_POINTS,2):"").'</td>
																		<td width="15%" align="right" >'.number_format_value_checker($CO_WEIGHTED_POINTS,2).'</td>
																		<td width="15%" align="right" >'.number_format_value_checker(($STUD_WEIGHTED_POINTS / $CO_WEIGHTED_POINTS * 100),2).' %</td>
																		<td width="10%" ></td>
																	</tr>';
														}
														/* Ticket 1170 */
														
														$res_stu_point->MoveNext();
													}
													
													/* Ticket 1170 */
													if($_GET['report_type'] == 1) {
														$GRADE_BOOK_TYPE1 = '';
													} else {
														$GRADE_BOOK_TYPE1 = $res_test_type->fields['GRADE_BOOK_TYPE'];
													}
													
													/* Ticket # 1219   */
													if($_GET['report_type'] == 1) {
														$txt .= '<tr>
																<td width="10%" ></td>
																<td width="15%" >'.$GRADE_BOOK_TYPE1.'</td>
																<td width="20%" style="'.$border_1.'" ><i>Weighted Total:</i></td>
																<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker(($SUB_TOT_STUD_WEIGHTED_POINTS),2).'</td>
																<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker(($SUB_TOT_CO_WEIGHTED_POINTS),2).'</td>
																<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker((($SUB_TOT_STUD_WEIGHTED_POINTS) / ($SUB_TOT_CO_WEIGHTED_POINTS) * 100),2).' %</td>
																<td width="10%"></td>
															</tr>
															<tr>
																<td width="100%" ><br /></td>
															</tr>';
													}
													/* Ticket # 1219   */
												}
												
												$res_test_type->MoveNext();
											}
											
											if($flag == 1){
												if($TOT_CO_WEIGHTED_POINTS > 0)
													$per1 = ($TOT_STUD_WEIGHTED_POINTS / $TOT_CO_WEIGHTED_POINTS * 100);
												else
													$per1 = 0;
												
												/* Ticket # 1219   */
												$txt .= '<tr>
															<td width="10%" ></td>';
														if($_GET['report_type'] == 1) {
															$txt .= '<td width="15%" ></td>';
														}
														$txt .= '<td width="20%" style="'.$border_1.'" ><i>Weighted Current Total:</i></td>
															<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker($TOT_STUD_WEIGHTED_POINTS,2).'</td>
															<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker($TOT_CO_WEIGHTED_POINTS,2).'</td>
															<td width="15%" style="'.$border_1.'" align="right" >'.number_format_value_checker($per1,2).' %</td>
															<td width="10%"></td>
														</tr>';
												/* Ticket # 1219   */
											}
										}
										$res_course->MoveNext();
									}
								}		
								$res_term->MoveNext();
							}
							
						$txt .= '</table>
						<br /><br />';
					}
				}
			
	$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" >
		<tr nobr="true">
			<td width="100%" >';
			if($_POST['show_attenance_summary']=='yes'){
				$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%" >';
					if($_GET['report_type'] == 1) {
						/*$txt .= '<tr>
								<td width="50%"><i style="font-size:50px">'.$res_stu->fields['NAME'].'</i></td>
							</tr>';*/
					} else {
						$txt .= '<tr>
									<td width="50%"><i style="font-size:35px">Attendance/GPA Summary</i></td>
								</tr>';
					}
				$txt .= '<tr>';
				
					if($_GET['report_type'] == 1) {
						$border_left = "";
						$txt .= '<td width="8%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Term</b></td>
								<td width="13%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Course</b></td>';
					} else {
						$txt .= '<td width="8%"></td>';
						$border_left = "border-left:1px solid #000;";
					}	
					$txt .= '<td width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;'.$border_left.'" align="right" ><br /><br /><b>Hours Attended</b></td>
						<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Missed</b></td>
						<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Scheduled</b></td>
						<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Absent Count</b></td>
						<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Absent Hours Missed</b></td>
						<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Tardy Count</b></td>
						<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Tardy Hours Missed</b></td>
						<td width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Left Early Count</b></td>
						<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Left Early Hours Missed</b></td>
						<td width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Attendance Percentage</b></td>';
						
						if($_GET['report_type'] == 1) {
							$txt .= '
							<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Final Course Grade</b></td>';
						}else {
							$txt .= '<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Cumulative GPA</b></td>';
						}
					$txt .= '</tr>';

					


					$NUMERIC_GRADE = 0;
					
					$Denominator 	= 0;
					$Numerator 		= 0;
					$Numerator1 	= 0;
					
					/* Ticket #1146 */
					$include_tc = 1;
					if($_POST['exclude_tc'] == 1)
						$include_tc = 0;

					if($include_tc == 1 ) { 			
						$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, CREDIT_TRANSFER_STATUS, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, S_GRADE.GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_GRADE.NUMBER_GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS FROM S_STUDENT_CREDIT_TRANSFER LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND SHOW_ON_TRANSCRIPT = 1 AND S_GRADE.CALCULATE_GPA = 1 AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE $en_cond3 ");
								
						while (!$res_tc->EOF) {
							$Denominator += $res_tc->fields['UNITS'];
							$Numerator	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];
							$Numerator1	 += $res_tc->fields['UNITS'] * $res_tc->fields['NUMBER_GRADE'];
							
							$NUMERIC_GRADE 	= $res_tc->fields['NUMERIC_GRADE'];
							
							if($_GET['report_type'] == 1) {
								$txt .=	'<tr>
									<td width="8%" style="border-left:1px solid #000;" >Transfer</td>
									<td width="13%" style="border-right:1px solid #000;"  >'.$res_tc->fields['TRANSCRIPT_CODE'].'</td>
									<td width="7%" align="right" ></td>
									<td width="6%" align="right" ></td>
									<td width="8%" align="right" style="border-right:1px solid #000;" ></td>
									<td width="6%" align="right" ></td>
									<td width="6%" align="right" style="border-right:1px solid #000;" ></td>
									<td width="6%" align="right" ></td>
									<td width="6%" align="right" style="border-right:1px solid #000;" ></td>
									<td width="8%" align="right" ></td>
									<td width="7%" align="right" style="border-right:1px solid #000;" ></td>
									<td width="9%" align="right" ></td>
									<td width="6%" align="right" style="border-right:1px solid #000;" >'.$res_tc->fields['GRADE'].'</td>
								</tr>';
							}
							
							$res_tc->MoveNext();
						}
					}
					/* Ticket #1146 */	
					
					/* Ticket # 1152 */
					
					if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1 && $_GET['REPORT_OPTION']==3){
						$cond="";
					}
					$res_course = $db->Execute("select NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE  
					FROM
					S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION, S_GRADE 
					WHERE 
					S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
					S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
					S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
					S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
					S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
					S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE AND 
					M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION AND CALCULATE_GPA = 1 $cond $show_cond_1 ");
					while (!$res_course->EOF) {
						$Denominator += $res_course->fields['COURSE_UNITS'];
						$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
						$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
					
						$res_course->MoveNext();
					}
					
					if($_POST['REPORT_OPTION'] == 3) {
						$res_course_cond1 = '';
					}else{
						$res_course_cond1 = $cond;
					}
					$res_course = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, S_COURSE_OFFERING.PK_COURSE, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, SESSION_NO, SESSION,FINAL_GRADE, GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS, S_STUDENT_COURSE.PK_STUDENT_COURSE, NUMERIC_GRADE, FINAL_TOTAL_GRADE_NUMBER_GRADE       
					FROM
					S_STUDENT_COURSE 
					LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
					, S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION 
					WHERE 
					S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
					S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
					S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
					S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
					S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
					M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION $res_course_cond1 $show_cond_1 
					GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER BY BEGIN_DATE ASC, TRANSCRIPT_CODE ASC");

					// echo "select S_COURSE_OFFERING.PK_COURSE_OFFERING, S_COURSE_OFFERING.PK_COURSE, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, SESSION_NO, SESSION,FINAL_GRADE, GRADE, NUMBER_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, COURSE_UNITS, S_STUDENT_COURSE.PK_STUDENT_COURSE, NUMERIC_GRADE, FINAL_TOTAL_GRADE_NUMBER_GRADE       
					// FROM
					// S_STUDENT_COURSE 
					// LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
					// , S_COURSE_OFFERING, S_COURSE, S_TERM_MASTER, M_SESSION 
					// WHERE 
					// S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					// S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
					// S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND 
					// S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
					// S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
					// S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
					// M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION $cond $show_cond 
					// GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER BY BEGIN_DATE ASC, TRANSCRIPT_CODE ASC";
					// exit;
					/* Ticket # 1152 */
					
					$tot_com_sch 	= 0;
					$total_schedule = 0;
					$total_attended = 0;
					$total_missed 	= 0;
					
					$total_absent 			= 0;
					$total_absent_hour = 0;
					$total_left_early =0;

					$total_left_early_hour 	= 0;
					
					$total_attended_percentage 	= 0;
					$per_index 					= 0;
					
					$c_in_att_tot 	= 0;
					$c_in_comp_tot 	= 0;
					$c_in_cu_gnu 	= 0;
					$c_in_gpa_tot 	= 0;
					
					$tot_obt_numeric = 0;
					$tot_numeric 	 = 0;
					
					$total_weight 	 = 0;

					$total_tardy = 0;
					$total_tardy_hour =0;
					
					while (!$res_course->EOF) { 
						$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
						$PK_STUDENT_COURSE	= $res_course->fields['PK_STUDENT_COURSE'];
						
						$COMPLETED_UNITS	= 0;
						$ATTEMPTED_UNITS	= 0;
						$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
							
						if($res_course->fields['UNITS_ATTEMPTED'] == 1) //Ticket # 1152
							$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
						
						$c_in_att_tot 		+= $ATTEMPTED_UNITS;
						$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
						
						if($res_course->fields['UNITS_COMPLETED'] == 1) { //Ticket # 1152
							$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
							$c_in_comp_tot  	+= $COMPLETED_UNITS;
							$c_in_comp_sub_tot  += $COMPLETED_UNITS;
						}
						
						$gnu = 0;
						$gpa = 0;
						if($res_course->fields['CALCULATE_GPA'] == 1) { //Ticket # 1152
							$gnu 				 = $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; //Ticket # 1152
							$c_in_cu_gnu 		+= $gnu; 
							$c_in_cu_sub_gnu 	+= $gnu; 
							
							$gpa				= $gnu / $COMPLETED_UNITS;;
							$c_in_gpa_sub_tot 	+= $gpa;
							$c_in_gpa_tot 		+= $gpa;
						}
						
						//$res_sch = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS SCHEDULED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE  AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE !=  7 $exclude_cond ");
						
						$SCHEDULED_HOUR 	 = 0;
						$COMP_SCHEDULED_HOUR = 0;
						$res_sch = $db->Execute("SELECT S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, S_STUDENT_ATTENDANCE.COMPLETED, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
						while (!$res_sch->EOF) { 
							$exc_att_flag = 0;
							foreach($exc_att_code_arr as $exc_att_code) {
								if($exc_att_code == $res_sch->fields['PK_ATTENDANCE_CODE']) {
									$exc_att_flag = 1;
									break;
								}
							}
							if($res_sch->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0){
								$SCHEDULED_HOUR += $res_sch->fields['HOURS'];
							
								if($res_sch->fields['COMPLETED'] == 1 || $res_sch->fields['PK_SCHEDULE_TYPE'] == 2) {
									$COMP_SCHEDULED_HOUR += $res_sch->fields['HOURS'];	
									$tot_com_sch		 += $res_sch->fields['HOURS'];	
								}
							}	
							$res_sch->MoveNext();
						}
				
						$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
						
						$res_abs = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as ABSENT, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS ABSENT_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE IN ($absent_att_code)  AND COMPLETED = 1 ");
						
						$res_tardy = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as TARDY, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS TARDY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 16  AND COMPLETED = 1 AND (S_STUDENT_ATTENDANCE.COMPLETED = 1 OR S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE = 2)");
						
						$res_left_early = $db->Execute("SELECT COUNT(S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE) as LEFT_EARLY, IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS - ATTENDANCE_HOURS),0) AS LEFT_EARLY_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 5  AND COMPLETED = 1 ");
						
						$missed = $COMP_SCHEDULED_HOUR - $res_attended->fields['ATTENDED_HOUR'];
						if($missed < 0)
							$missed = 0;
						$total_schedule += $SCHEDULED_HOUR;
						$total_attended += $res_attended->fields['ATTENDED_HOUR'];
						$total_missed 	+= $missed;
						
						$total_absent 		+= $res_abs->fields['ABSENT'];
						$total_absent_hour 	+= $res_abs->fields['ABSENT_HOUR'];
						
						$total_tardy 		+= $res_tardy->fields['TARDY'];
						$total_tardy_hour 	+= $res_tardy->fields['TARDY_HOUR'];
						
						$total_left_early 		+= $res_left_early->fields['LEFT_EARLY'];
						$total_left_early_hour 	+= $res_left_early->fields['LEFT_EARLY_HOUR'];
						
						if( has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1 && $COMP_SCHEDULED_HOUR > 0){
							$attended_percentage = $res_attended->fields['ATTENDED_HOUR'] / $COMP_SCHEDULED_HOUR * 100;
							$total_attended_percentage += $attended_percentage;
							$per_index++;
						}
						else if($SCHEDULED_HOUR > 0 && has_custom_sap_report($_SESSION['PK_ACCOUNT'])==0 ) {
							$attended_percentage = $res_attended->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR * 100;
							$total_attended_percentage += $attended_percentage;
							$per_index++;
						} else 
							$attended_percentage = 0;
						
						$NUMERIC_GRADE = '';
						if(trim($res_course->fields['CALCULATE_GPA']) == 1) { ////Ticket # 1152
							$NUMERIC_GRADE 	= $res_course->fields['NUMERIC_GRADE'];
						}
						
						if($_GET['report_type'] == 1) {
							/* Ticket # 1152 */
							$focus_scheduled_hours=0;
							if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1)
							{
								$focus_scheduled_hours=$COMP_SCHEDULED_HOUR;

							}else{

								$focus_scheduled_hours=$SCHEDULED_HOUR;
							}

							$txt .=	'<tr>
									<td width="8%" style="border-left:1px solid #000;" >'.$res_course->fields['BEGIN_DATE_1'].'</td>
									<td width="13%" style="border-right:1px solid #000;"  >'.$res_course->fields['TRANSCRIPT_CODE'].' ('. substr($res_course->fields['SESSION'],0,1).' - '. $res_course->fields['SESSION_NO'].')</td>
									<td width="7%" align="right" >'.number_format_value_checker($res_attended->fields['ATTENDED_HOUR'],2).'</td>
									<td width="6%" align="right" >'.number_format_value_checker(($missed),2).'</td>
									<td width="8%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($focus_scheduled_hours,2).'</td>
									<td width="6%" align="right" >'.$res_abs->fields['ABSENT'].'</td>
									<td width="6%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_abs->fields['ABSENT_HOUR'],2).'</td>
									<td width="6%" align="right" >'.$res_tardy->fields['TARDY'].'</td>
									<td width="6%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_tardy->fields['TARDY_HOUR'],2).'</td>
									<td width="8%" align="right" >'.$res_left_early->fields['LEFT_EARLY'].'</td>
									<td width="7%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_left_early->fields['LEFT_EARLY_HOUR'],2).'</td>
									<td width="9%" align="right" >'.number_format_value_checker($attended_percentage,2).' %</td>
									<td width="6%" align="right" style="border-right:1px solid #000;" >'.$res_course->fields['GRADE'].'</td>
								</tr>';
							/* Ticket # 1152 */
						}
						
						$res_course->MoveNext();
					}
					
					if($tot_com_sch > 0)
						$total_attended_percentage = $total_attended / $tot_com_sch * 100;
					else
						$total_attended_percentage = 0;

					if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1){
						$total_schedule=$tot_com_sch;
					}
						
					$gpa = '';
					if($c_in_comp_tot > 0)
						$gpa = number_format_value_checker(($c_in_cu_gnu / $c_in_comp_tot),2);
						
				$res_tc_1 = $db->Execute("SELECT SUM(HOUR) as HOUR FROM S_STUDENT_CREDIT_TRANSFER, M_CREDIT_TRANSFER_STATUS WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS AND SHOW_ON_TRANSCRIPT = 1");
				
				$txt .=	'<tr>';
						if($_GET['report_type'] == 1) {
							$border_3 		= "border-top:1px solid #000;";
							$border_left 	= "";
							$txt .=	'<td width="21%" style="border-top:1px solid #000;" >Transferred: '.number_format_value_checker($res_tc_1->fields['HOUR'],2).'</td>';
						} else {
							$border_3 		= "border-bottom:1px solid #000;";
							$border_left 	= "border-left:1px solid #000;";
							$txt .= '<td width="8%"></td>';
						}
				$txt .=	'<td width="7%" align="right" style="'.$border_3.$border_left.'" >'.number_format_value_checker($total_attended,2).'</td> 
						<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_missed,2).'</td>
						<td width="8%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_schedule,2).'</td>
						<td width="6%" align="right" style="'.$border_3.$border_left.'" >'.$total_absent.'</td>
						<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_absent_hour,2).'</td>
						<td width="6%" align="right" style="'.$border_3.$border_left.'" >'.$total_tardy.'</td>
						<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_tardy_hour,2).'</td>
						<td width="8%" align="right" style="'.$border_3.$border_left.'" >'.$total_left_early.'</td>
						<td width="7%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_left_early_hour,2).'</td>
						<td width="9%" align="right" style="'.$border_3.$border_left.'" >'.number_format_value_checker($total_attended_percentage,2).' %</td>';
						
						if($_GET['report_type'] == 1) {
							$txt .=	'
									<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker(($Numerator1/$Denominator),2).'</td>';
						} else {
							$txt .=	'<td width="8%" align="right" style="'.$border_3.'border-right:1px solid #000;" >'.number_format_value_checker(($Numerator1/$Denominator),2).'</td>';
						}
					$txt .=	'</tr>
					<tr>';
					if($_GET['report_type'] != 1) {
						$txt .=	'<td width="8%" align="right"  ></td>';
					}
					$txt .=	'<td width="29%" ></td>
						<td width="7%" align="right"  ></td>
						<td width="8%" align="right"  ></td>
						<td width="7%" align="right"  ></td>
						<td width="7%" align="right"  ></td>
						<td width="6%" align="right"  ></td>
						<td width="6%" align="right"  ></td>
						<td width="8%" align="right"  ></td>
						<td width="8%" align="right"  ></td>';
						if($_GET['report_type'] == 1 && has_custom_sap_report($_SESSION['PK_ACCOUNT'])==0 ) {
							$txt .=	'<td width="13%" align="right"  ><i>(Cumulative GPA)</i></td>';
						}
					$txt .=	'</tr>';

					if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1 && $_GET['report_type'] == 1 && $include_tc!=1)
					{
						$txt.='<tr>
						<td width="60%"><i>Report does not include transfer credits.</i></td>
						<td width="40%" align="right"><i>(Cumulative GPA)</i></td>
						</tr>
						<tr>
						<td width="60%"><i>Absent hours missed sums the scheduled hours for days marked as "A" (absent)</i></td>
						<td width="40%" align="right"></td>
						</tr>';
					}else if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==1 && $_GET['report_type'] == 1 && $include_tc==1){
						$txt.='<tr>
						<td width="60%"></td>
						<td width="40%" align="right"><i>(Cumulative GPA)</i></td>
						</tr>
						';
					}

			$txt .=	'</table>';
			} //show_attenance_summary
		$txt .=	'</td>
				</tr>
			</table>';
			//Ticket # 936
			if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==0){ 
			$txt .=	'<br /><br /><br />
				
				<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td width="45%" align="right" ><i>Official Signature:</i></td>
						<td width="20%" style="border-bottom:1px solid #000;" ></td>
					</tr>
				</table>';
			}
			//Ticket # 936
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}
	//echo $txt; exit;
	$file_name = 'Course Offering Grade Book Progress Report'.'.pdf';

	// if($one_stud_per_pdf == 0) {
	// 	$file_dir_1 = 'temp/';
	// 	$pdf->Output($file_dir_1.$file_name, 'FD');
	// } else {
	// 	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
	// 	$file_dir_1 = '../backend_assets/tmp_upload/';

	// 	$file_name  = $res_stu->fields['LAST_NAME'].'_'.$res_stu->fields['FIRST_NAME'].'-'.$res_stu->fields['STUDENT_ID'].'-'.$file_name.'_'.$PK_STUDENT_MASTER.'.pdf';
	// 	$pdf->Output($file_dir_1.$file_name, 'F');
	// }

	$data_res = [];
	if($one_stud_per_pdf == 0) {
		//$file_dir_1 = 'temp/';
		//$pdf->Output($file_dir_1.$file_name, 'FD');
		$dir 			= 'temp/';
		$outputFileName = $dir.$file_name; 
		$pdf->Output($outputFileName, 'F');
		header('Content-type: application/json; charset=UTF-8');		
		$data_res['path'] = $outputFileName;
		$data_res['filename'] = $file_name;
		
	} 
	
	return json_encode($data_res);

	//return $file_name;
}

$Get_Stud_Master     = $_POST['PK_STUDENT_MASTER'];
$student_array=array();
$s=0;
foreach ($Get_Stud_Master as $key => $value) {
	# code...
	$student_array[$value][]= $_POST['PK_STUDENT_ENROLLMENT'][$s];
	$s++;
}

if(!empty($student_array)) {
	echo co_grade_book_progress_pdf($student_array, 0);
}
