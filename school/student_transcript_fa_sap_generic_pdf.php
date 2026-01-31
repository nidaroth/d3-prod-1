<?php 
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

ini_set('display_errors', 0);
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

if(check_access('REPORT_REGISTRAR') == 0 && $_SESSION['PK_USER_TYPE'] != 3){
	header("location:../index");
	exit;
}
	
class MYPDF extends TCPDF {

    public function Header() {
		global $db;
		
		if($_GET['id'] == '' || $_GET['uno'] == 1) { 
			// get the current page break margin
			$bMargin = $this->getBreakMargin();
			// get current auto-page-break mode
			$auto_page_break = $this->AutoPageBreak;
			// disable auto-page-break
			$this->SetAutoPageBreak(false, 0);
			// set bacground image
			
			$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 11");
		
			$ImageW = 175; //WaterMark Size
			$ImageH = 175;

			//$pdf->setPage(1); //WaterMark Page    

			$myPageWidth  = $this->getPageWidth();
			$myPageHeight = $this->getPageHeight() - ($res_type->fields['FOOTER_LOC'] + 10);
			$myX = ( $myPageWidth / 2 ) - 90;  //WaterMark Positioning
			$myY = ( $myPageHeight / 2 ) - 80;

			$this->SetAlpha(0.30);
			$this->Image('../backend_assets/images/unoffical_1.png', $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);

			//Likewise can be added for all pages after writing all pages.
			$this->SetAlpha(1);
			
			// restore auto-page-break status
			$this->SetAutoPageBreak($auto_page_break, $bMargin);
			// set the starting point for the page content
			$this->setPageMark();
		}
		
		if($_SESSION['temp_id'] == $this->PK_STUDENT_MASTER){
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(5);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			$this->SetX(150);
			$this->Cell(55, 8, "Student SAP", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		} else {
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;
		}
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		//$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 11");
		
		/*$txt = "";
		if($res_type->fields['BOLD'] == 1)
			$txt .= "B";
		if($res_type->fields['ITALIC'] == 1)
			$txt .= "I";*/
		$CONTENT = '';
		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		//$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
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

$_SESSION['temp_id'] = '';	
function student_transcript_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf, $report_name){
	global $db;
	
	$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);
	
	$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 11");

	$FOOTER_LOC = $res_type->fields['FOOTER_LOC'];
	$BASE 		= 30 + $FOOTER_LOC;
	
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 15, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, $BASE);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 8, '', true);

	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$LOGO = '';
	if($res->fields['PDF_LOGO'] != '')
		$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';

	$uno = 'Official ';
	if($_GET['uno'] == 1)
		$uno = 'Unofficial ';

	require_once("pdf_custom_header.php"); //Ticket # 1588

	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
		
		$en_cond = "";
		$array_eid = array();
		if($_GET['eid'] != ''){
			$en_cond = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
			$string = $_GET['eid'];
			$array_eid = explode(',', $string);
		}
		
		$res_stu = $db->Execute("select LAST_NAME, FIRST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
		
		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");

		$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588
		
		// $pdf->STUD_NAME = $res_stu->fields['NAME'];
		// $pdf->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
		$pdf->startPageGroup();
		$pdf->AddPage();
		
		$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td width="100%">
							<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								';
								foreach($array_eid as $array_eid_row)
								{
									$array_en_cond = " AND PK_STUDENT_ENROLLMENT = '$array_eid_row' ";

									$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, S_SAP_SCALE_SETUP.PK_SAP_SCALE FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_SAP_SCALE_SETUP ON S_SAP_SCALE_SETUP.PK_SAP_SCALE = M_CAMPUS_PROGRAM.PK_SAP_SCALE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $array_en_cond ORDER BY BEGIN_DATE ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");

									// $count = 1;
									while (!$res_type->EOF) {
										$PK_STUDENT_ENROLLMENT 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
										$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
										$aPK_SAP_SCALE          = $res_type->fields['PK_SAP_SCALE'];

										$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

											// if($count != 1)
											// {
											// 	$txt .= '<div style="page-break-before: always;">';
											// }
											$txt .= '<tr>
														<td width="50%">'.$CONTENT.'</td>
														<td width="50%">
															<table border="0" cellspacing="0" cellpadding="3" width="100%">
																<tr>
																	<td style="width:100%" >
																		<b style="font-size:50px" >'.$res_stu->fields['NAME'].'</b><br />
																	</td>
																</tr>
																<tr>
																	<td style="width:60%" >
																		<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
																	</td>
																	<td align="right" style="width:40%" >
																		<span style="line-height:5px" >ID: '.$res_stu->fields['STUDENT_ID'].'<br />DOB: '.$res_stu->fields['DOB'].'<br />Phone: '.$res_add->fields['HOME_PHONE'].'</span>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
													<tr>
														<div style="border-top: 1px solid #c0c0c0;" ></div>
														<td width="34%" >
															'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
														<td width="34%" >
															'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
														<td width="32%" >
															'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
													</tr>
													<tr>
														<td width="34%" >
															'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
														<td  width="34%" >
															'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
														<td  width="32%" >
															'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
													</tr>
													<tr>
														<td width="34%" >
															'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
														<td  width="34%" >
															'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
														<td  width="32%" >
															'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ").'
														</td>
													</tr>
													<tr>
														<td width="100%" colspan="2" >';
																$txt .=  get_official_student_transcript($PK_STUDENT_MASTER,$array_en_cond,$report_name,$aPK_SAP_SCALE,$array_eid_row);
												$txt .= '</td>
													</tr>';

											// if($count != 1)
											// {
											//   $txt .= '</div>';
											// }
										//$count++;		
										
										$res_type->MoveNext();
									}
									
								}	
					$txt .= '</table>
						</td>
					</tr>
				</table>';

				$needle   = '<div style="page-break-before: always;"></div>'; 
				$pos      = strripos($txt, $needle);
				$txt = substr_replace($txt , '' ,  $pos , strlen($needle) );

		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}
	
	 // echo $txt;exit; // dvb
	$file_name = $report_name.'_'.uniqid().'.pdf';
	
	if($one_stud_per_pdf == 0) {
		$file_dir_1 = 'temp/';
		$pdf->Output($file_dir_1.$file_name, 'FD');
	} else {
		//$file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
		$file_dir_1 = '../backend_assets/tmp_upload/';

		$file_name  = $res_stu->fields['LAST_NAME'].'_'.$res_stu->fields['FIRST_NAME'].'-'.$res_stu->fields['STUDENT_ID'].'-'.$report_name.'_'.$PK_STUDENT_MASTER.'.pdf';
		$pdf->Output($file_dir_1.$file_name, 'F');
	}

	return $file_name;
}

function get_official_student_transcript($PK_STUDENT_MASTER,$en_cond,$report_name,$aPK_SAP_SCALE,$PK_STUDENT_ENR)
{
	global $db;
	$attendance_data = '1';
	// SAP
	$res_sap_scale_setup = $db->Execute("SELECT * FROM S_SAP_SCALE_SETUP WHERE PK_SAP_SCALE = '$aPK_SAP_SCALE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ");
	//print_r($res_sap_scale_setup->fields);exit;
	$PK_SAP_SCALE                 =  $res_sap_scale_setup->fields['PK_SAP_SCALE'];
	$PK_PROGRAM_PACE              =  $res_sap_scale_setup->fields['PK_PROGRAM_PACE'];
	/***************************************************/
	$HOURS_COMPLETED_SCHEDULED 	  =  $res_sap_scale_setup->fields['HOURS_COMPLETED_SCHEDULED'];


	$hours_comp  = false;
	$hours_sched = false;
	$program_hours = false;
	if($HOURS_COMPLETED_SCHEDULED == '1')
	{
		$hours_comp  = true;
		$hours_sched = true;
	} 
	$HOURS_COMPLETED_PROGRAM   	  =  $res_sap_scale_setup->fields['HOURS_COMPLETED_PROGRAM'];
	if($HOURS_COMPLETED_PROGRAM == '1')
	{
		$hours_comp    = true;
		$program_hours = true;
	} 
	$HOURS_SCHEDULED_PROGRAM   	  =  $res_sap_scale_setup->fields['HOURS_SCHEDULED_PROGRAM'];
	if($HOURS_SCHEDULED_PROGRAM == '1')
	{
		$hours_sched   = true;
		$program_hours = true;
	}
	/***************************************************/
	$FA_UNITS_COMPLETED_ATTEMPTED =  $res_sap_scale_setup->fields['FA_UNITS_COMPLETED_ATTEMPTED'];

	$fa_units_comp   = false;
	$fa_units_attemp = false;
	$program_fa_units = false;
	if($FA_UNITS_COMPLETED_ATTEMPTED == '1')
	{
		$fa_units_comp   = true;
		$fa_units_attemp = true;
	}
	$FA_UNITS_COMPLETED_PROGRAM   =  $res_sap_scale_setup->fields['FA_UNITS_COMPLETED_PROGRAM'];
	if($FA_UNITS_COMPLETED_PROGRAM == '1')
	{
		$fa_units_comp    = true;
		$program_fa_units = true;
	}
	$FA_UNITS_ATTEMPTED_PROGRAM   =  $res_sap_scale_setup->fields['FA_UNITS_ATTEMPTED_PROGRAM'];
	if($FA_UNITS_ATTEMPTED_PROGRAM == '1')
	{
		$fa_units_attemp  = true;
		$program_fa_units = true;
	}
	/***************************************************/
	$UNITS_COMPLETED_ATTEMPTED    =  $res_sap_scale_setup->fields['UNITS_COMPLETED_ATTEMPTED'];

	$units_comp   = false;
	$units_attemp = false;
	$program_units = false;
	if($UNITS_COMPLETED_ATTEMPTED == '1')
	{
		$units_comp   = true;
		$units_attemp = true;
	}
	$UNITS_COMPLETED_PROGRAM      =  $res_sap_scale_setup->fields['UNITS_COMPLETED_PROGRAM'];
	if($UNITS_COMPLETED_PROGRAM == '1')
	{
		$units_comp    = true;
		$program_units = true;
	}
	$UNITS_ATTEMPTED_PROGRAM      =  $res_sap_scale_setup->fields['UNITS_ATTEMPTED_PROGRAM'];
	if($UNITS_ATTEMPTED_PROGRAM == '1')
	{
		$units_attemp  = true;
		$program_units = true;
	}
	/***************************************************/
	$CUMULATIVE_GPA               =  $res_sap_scale_setup->fields['CUMULATIVE_GPA'];
	/***************************************************/
	$hours_include_transfer     = false;
	$units_include_transfer     = false;
	$fa_units_include_transfer  = false;
	$gpa_include_transfer       = false;
	$PERIOD_HOURS_COMPLETED_SCHEDULED               =  $res_sap_scale_setup->fields['PERIOD_HOURS_COMPLETED_SCHEDULED'];
	if($PERIOD_HOURS_COMPLETED_SCHEDULED == '1')
	{
		$hours_include_transfer  = true;
		$hours_comp    = true;
		$hours_sched   = true;
		$program_hours = true;
	}
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED      =  $res_sap_scale_setup->fields['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED'];
	if($PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED == '1')
	{
		$units_include_transfer  = true;
		$units_comp    = true;
		$units_attemp  = true;
		$program_units = true;
	}
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED            =  $res_sap_scale_setup->fields['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'];
	if($PERIOD_FA_UNITS_COMPLETED_ATTEMPTED == '1')
	{
		$fa_units_include_transfer  = true;
		$fa_units_comp    = true;
		$fa_units_attemp  = true;
		$program_fa_units = true;
	}
	$PERIOD_GPA      =   $res_sap_scale_setup->fields['PERIOD_GPA'];
	if($PERIOD_GPA == '1')
	{
		$gpa_include_transfer  = true;
	}
	/***************************************************/
	// End SAP

	$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$total_cum_gpa = '';
	$total_units_att = '';
	$total_units_compt = '';
	$total_fa_units_att = '';
	$total_fa_units_compt = '';
	$total_prog_unit_tot = '';
	$total_prog_fa_unit_tot = '';
	$total_prog_hour_tot = '';
	$total_prog_comp_hour_tot = '';
	$total_sched_hour_tot = '';

    $txt = ''; 
	    $txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>
				 <table border="0.4" cellspacing="0" cellpadding="3" >
							<tr>
								<td align="center" ><b><i style="font-size:50px">'.$report_name.'</i></b><br /></td>
							</tr>
							<tr>
								<td width="6%" ><br /><br /><u><b>Course</b></u></td>
								<td width="8%" ></td>';
								
								if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
								{
									$txt .= '<td width="6%" ><br /><br /><u><b>Grade</b></u></td>';
								}
								
								if($units_attemp){
									$txt .= '<td width="9%" align="center" ><b>Units</b><br /><u><b>Attempted</b></u></td>';
								}
								if($units_comp){
									$txt .= '<td width="9%" align="center" ><b>Units</b><br /><u><b>Completed</b></u></td>';
								}
								if($program_units){
									$txt .= '<td width="7%" align="center" ><b>Units</b><br /><u><b>Program</b></u></td>';
								}
								if($fa_units_attemp){
									$txt .= '<td width="9%" align="center" ><b>FA Units</b><br /><u><b>Attempted</b></u></td>';
								}
								if($fa_units_comp){
									$txt .= '<td width="9%" align="center" ><b>FA Units</b><br /><u><b>Completed</b></u></td>';
								}
								if($program_fa_units){
									$txt .= '<td width="7%" align="center" ><b>FA Units</b><br /><u><b>Program</b></u></td>';
								}
								if($hours_comp){
									$txt .= '<td width="9%" align="center" ><b>Hours</b><br /><u><b>Completed</b></u></td>';
								}
								if($hours_sched){
									$txt .= '<td width="9%" align="center" ><b>Hours</b><br /><u><b>Scheduled</b></u></td>';
								}
								if($program_hours){
									$txt .= '<td width="7%" align="center" ><b>Hours</b><br /><u><b>Program</b></u></td>';
								}
								
								if($CUMULATIVE_GPA == '1'){
									$txt .= '<td width="5%" align="center" ><br /><br /><u><b>GPA</b></u></td>';
								}
								
					$txt .='</tr>';

					$total_cum_rec		= 0;
					$c_in_num_grade_tot = 0;
					$c_in_att_tot 		= 0;
					$c_fa_in_att_tot 	= 0;
					$c_in_comp_tot 		= 0;
					$c_fa_in_comp_tot 	= 0;
					$c_in_cu_gnu 		= 0;
					$c_in_gpa_tot 		= 0;
					
					$Denominator = 0;
					$Numerator 	 = 0;
					$Numerator1  = 0;
					
					#Initiating cummulative variables -av
					$c_in_prog_hour_tot      = 0;
					$c_in_prog_unit_tot      = 0;
					$c_in_num_grade_sub_tot  = 0;
					$c_in_att_sub_tot 		 = 0;
					$c_in_comp_sub_tot 		 = 0;
					$c_fa_in_comp_sub_tot 	 = 0;
					$c_in_cu_sub_gnu 		 = 0;
					$c_in_gpa_sub_tot 		 = 0;
					$c_fa_in_att_sub_tot 	 = 0;
					$c_in_prog_unit_sub_tot  = 0;
					$c_in_prog_fa_unit_tot   = 0;
					$c_in_prog_fa_unit_sub_tot = 0;
					$c_in_prog_comp_hour_tot = 0;
					$c_in_prog_comp_hour_sub_tot = 0;
					$c_in_sched_hour_tot 	 = 0; 
					$c_in_sched_hour_sub_tot = 0;
					$c_in_prog_hour_sub_tot  = 0;
					$gpa_credit_units        = 0;
					$new_total_cum_gpa = 0;

					// DIAM-787, new calulated gpa
					$tc_gpa_value_total    = 0;
					$tc_gpa_weight_total   = 0;
					$summation_of_gpa   = 0;
					$summation_of_weight   = 0;
					// End DIAM-787, new calulated gpa
						
					// Term: Transfer -  (Transfer Credit)
					if($hours_include_transfer || $units_include_transfer || $fa_units_include_transfer || $gpa_include_transfer)
					{
						if($_GET['exclude_tc'] != 1  || $attendance_data == '1') {
							$Sub_Denominator = 0;
							$Sub_Numerator 	 = 0;
							$Sub_Numerator1  = 0;
							
							$res_tc = $db->Execute("SELECT S_COURSE.TRANSCRIPT_CODE, NUMBER_GRADE,S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, CALCULATE_GPA, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS 
							FROM S_STUDENT_CREDIT_TRANSFER 
							LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
							LEFT JOIN M_CAMPUS_PROGRAM_COURSE ON M_CAMPUS_PROGRAM_COURSE.PK_COURSE = S_COURSE.PK_COURSE
							WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS_MASTER = '1' AND M_CAMPUS_PROGRAM_COURSE.GRADE_INCLUDE_IN_SAP='1' AND CALCULATE_GPA = 1 AND SHOW_ON_TRANSCRIPT = 1 $en_cond ");

							while (!$res_tc->EOF) {

								$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
								$res_grade_top = $db->Execute("SELECT NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' "); 
								
								$Denominator += $res_tc->fields['UNITS'];
								$Numerator1	 += $res_tc->fields['UNITS'] * $res_grade_top->fields['NUMBER_GRADE'];
								
								$Sub_Denominator += $res_tc->fields['UNITS'];
								$Sub_Numerator1	 += $res_tc->fields['UNITS'] * $res_grade_top->fields['NUMBER_GRADE'];
							
								$res_tc->MoveNext();
							}
							
							$res_tc = $db->Execute("SELECT CREDIT_TRANSFER_STATUS, 
									S_COURSE.TRANSCRIPT_CODE,
									S_COURSE.COURSE_DESCRIPTION, 
									S_COURSE.PK_COURSE,
									S_STUDENT_CREDIT_TRANSFER.COURSE_DESCRIPTION AS COUR_DESC, 
									S_STUDENT_CREDIT_TRANSFER.UNITS, 
									S_STUDENT_CREDIT_TRANSFER.FA_UNITS, 
									S_STUDENT_CREDIT_TRANSFER.HOUR AS COMPLETED_HOURS,
									S_COURSE.HOURS AS PROGRAM_HOURS, 
									S_COURSE.UNITS AS PROGRAM_UNITS, 
									S_COURSE.FA_UNITS AS PROGRAM_FA_UNITS,
									TC_NUMERIC_GRADE, 
									S_GRADE.GRADE, 
									PK_STUDENT_ENROLLMENT, 
									S_STUDENT_CREDIT_TRANSFER.PK_GRADE, 
									S_GRADE.NUMBER_GRADE, 
									S_GRADE.CALCULATE_GPA, 
									S_GRADE.UNITS_ATTEMPTED, 
									S_GRADE.UNITS_COMPLETED, 
									S_GRADE.UNITS_IN_PROGRESS,
									CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
									CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
								FROM 
									S_STUDENT_CREDIT_TRANSFER 
									LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE
									LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
									LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
									-- LEFT JOIN M_CAMPUS_PROGRAM_COURSE ON M_CAMPUS_PROGRAM_COURSE.PK_COURSE = S_COURSE.PK_COURSE 
									WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS_MASTER = '1'
									-- AND M_CAMPUS_PROGRAM_COURSE.GRADE_INCLUDE_IN_SAP='1' 
									AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER BY S_COURSE.TRANSCRIPT_CODE ASC");

							if($res_tc->RecordCount() > 0) {
								$txt .= '<tr>
											<td width="100%" ><i style="font-size:45px">Term: Transfer</i></td>
										</tr>';
							}
							
							$total_rec				= 0;
							
							while (!$res_tc->EOF) {
								
								$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT']; 
								$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
								$PK_COURSE     			= $res_tc->fields['PK_COURSE'];

								// Query for get the COMPLETED_HOURS, SCHEDULE_HOURS
								// $res_attendance_hours = $db->Execute("SELECT sum(S_STUDENT_SCHEDULE.HOURS) as SCHEDULE_HOURS, 
								// 		sum(ATTENDANCE_HOURS) as COMPLETED_HOURS 
								// 	FROM 
								// 		S_STUDENT_SCHEDULE 
								// 		LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
								// 		LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL
								// 	WHERE 
								// 		S_STUDENT_SCHEDULE.PK_STUDENT_MASTER =  '$PK_STUDENT_MASTER'
								// 		AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENR'
								// 		AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
								// 		AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE = '$PK_COURSE'
								// 		AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ");
								
								// $res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, FA_UNITS, STATUS_DATE, STUDENT_STATUS, PROGRAM_TRANSCRIPT_CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 

								$COMPLETED_UNITS	 = 0;
								$ATTEMPTED_UNITS	 = 0;
								$PROGRAM_UNITS       = 0;
								
								$FA_ATTEMPTED_UNITS	 = 0;
								$FA_COMPLETED_UNITS	 = 0;
								$PROGRAM_FA_UNITS    = 0;

								$COMPLETED_HOURS     = 0;
								$SCHEDULE_HOURS      = 0;
								$PROGRAM_HOURS       = 0;
								
								$res_grade_data = $db->Execute("SELECT UNITS_ATTEMPTED,UNITS_COMPLETED,CALCULATE_GPA,NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' "); 
								if($res_grade_data->fields['UNITS_ATTEMPTED'] == 1) {
									if($units_include_transfer)
									{
										//$ATTEMPTED_UNITS 	 = $res_tc->fields['UNITS'];
									}
									if($fa_units_include_transfer)
									{
										//$FA_ATTEMPTED_UNITS	 = $res_tc->fields['FA_UNITS'];
									}
								}
								
								$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
								$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
								
								$c_fa_in_att_tot 		+= $FA_ATTEMPTED_UNITS; 
								$c_fa_in_att_sub_tot 	+= $FA_ATTEMPTED_UNITS; 
								
								if($res_grade_data->fields['UNITS_COMPLETED'] == 1) {
									if($units_include_transfer)
									{
										$COMPLETED_UNITS 	 = $res_tc->fields['UNITS'];
									}
									if($fa_units_include_transfer)
									{
										$FA_COMPLETED_UNITS	 = $res_tc->fields['FA_UNITS'];
									}									
								}

								$c_in_comp_tot  	+= $COMPLETED_UNITS;
								$c_in_comp_sub_tot  += $COMPLETED_UNITS;
								
								$c_fa_in_comp_tot  		+= $FA_COMPLETED_UNITS;
								$c_fa_in_comp_sub_tot  	+= $FA_COMPLETED_UNITS;
								if($units_include_transfer)
								{
									$PROGRAM_UNITS = 0; //$res_tc->fields['PROGRAM_UNITS'];
								}
								$c_in_prog_unit_tot     += $PROGRAM_UNITS; 
								$c_in_prog_unit_sub_tot += $PROGRAM_UNITS; 										

								if($fa_units_include_transfer)
								{
									$PROGRAM_FA_UNITS = 0; //$res_tc->fields['PROGRAM_FA_UNITS'];
								}
								$c_in_prog_fa_unit_tot 	    += $PROGRAM_FA_UNITS; 
								$c_in_prog_fa_unit_sub_tot 	+= $PROGRAM_FA_UNITS;

								if($hours_include_transfer)
								{
									$COMPLETED_HOURS = $res_tc->fields['COMPLETED_HOURS'];
								}
								$c_in_prog_comp_hour_tot 	 += $COMPLETED_HOURS; 
								$c_in_prog_comp_hour_sub_tot += $COMPLETED_HOURS;

								if($hours_include_transfer)
								{
									$SCHEDULE_HOURS  = 0;
								}
								$c_in_sched_hour_tot 	    += $SCHEDULE_HOURS; 
								$c_in_sched_hour_sub_tot 	+= $SCHEDULE_HOURS;

								if($hours_include_transfer)
								{
									$PROGRAM_HOURS   = 0; //$res_tc->fields['PROGRAM_HOURS'];
								}
								$c_in_prog_hour_tot 	    += $PROGRAM_HOURS; 
								$c_in_prog_hour_sub_tot 	+= $PROGRAM_HOURS;


								$gnu = 0;
								if($res_grade_data->fields['CALCULATE_GPA'] == 1) {
									$gnu 				 = $res_tc->fields['UNITS'] * $res_grade_data->fields['NUMBER_GRADE']; 
									$c_in_cu_gnu 		+= $gnu; 
									$c_in_cu_sub_gnu 	+= $gnu; 
									
									$gpa				= $gnu / $COMPLETED_UNITS;;
									$c_in_gpa_sub_tot 	+= $gpa;
									$c_in_gpa_tot 		+= $gpa;
									
									$c_in_num_grade_sub_tot	+= $res_tc->fields['TC_NUMERIC_GRADE'];
									$c_in_num_grade_tot		+= $res_tc->fields['TC_NUMERIC_GRADE'];

									// DIAM-787, new calulated gpa
									$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
									$tc_gpa_value_total 		+= $TC_GPA_VALULE; 
									$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
									$tc_gpa_weight_total 		+= $TC_GPA_WEIGHT; 
									// End DIAM-787, new calulated gpa
									
									$total_rec++;
									$total_cum_rec++;
								}

								// DIAM-787, new calulated gpa
								$tc_gpa_weighted = 0;
								if($tc_gpa_value_total>0)
								{
									$tc_gpa_weighted        = ($tc_gpa_value_total/$tc_gpa_weight_total);
								}
								$summation_of_gpa  += number_format_value_checker($TC_GPA_VALULE,2);
								$summation_of_weight  += number_format_value_checker($TC_GPA_WEIGHT,2);
								// End DIAM-787, new calulated gpa

								$Data_Grade = $res_tc->fields['GRADE'];
								if($Data_Grade != '-')
								{
									$my_values = ($tc_gpa_value_total/$tc_gpa_weight_total);
									$total_cum_gpa = $my_values;
									$total_units_att = $c_in_att_tot;
									$total_units_compt = $c_in_comp_tot;
									$total_fa_units_att = $c_fa_in_att_tot;
									$total_fa_units_compt = $c_fa_in_comp_tot;
									$total_prog_unit_tot = $c_in_prog_unit_tot;
									$total_prog_fa_unit_tot = $c_in_prog_fa_unit_tot;

									$total_prog_comp_hour_tot = $c_in_prog_comp_hour_tot;
									$total_sched_hour_tot = $c_in_sched_hour_tot;
									$total_prog_hour_tot = $c_in_prog_hour_tot;
								}

								if($gpa_include_transfer)
								{
									$gpa_credit_units = ($tc_gpa_value_total/$tc_gpa_weight_total);
								}
								
								$txt .= '<tr>
											<td width="6%" >'.$res_tc->fields['TRANSCRIPT_CODE'].'</td>
											<td width="8%" >'.$res_tc->fields['COUR_DESC'].'</td>';
											
											if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
											{
												$txt .= '<td width="6%" >'.$res_tc->fields['GRADE'].'</td>';
											}

											if($units_attemp){
												$txt .= '<td width="9%" align="center" >'.number_format_value_checker($ATTEMPTED_UNITS,2).'</td>';
											}
											if($units_comp){
												$txt .= '<td width="9%" align="center" >'.number_format_value_checker($COMPLETED_UNITS,2).'</td>';
											}
											if($program_units){
												$txt .= '<td width="7%" align="center" >'.number_format_value_checker($PROGRAM_UNITS,2).'</td>';
											}
											if($fa_units_attemp){
												$txt .= '<td width="9%" align="center" >'.number_format_value_checker($FA_ATTEMPTED_UNITS,2).'</td>';
											}
											if($fa_units_comp){
												$txt .= '<td width="9%" align="center" >'.number_format_value_checker($FA_COMPLETED_UNITS,2).'</td>';
											}
											if($program_fa_units){
												$txt .= '<td width="7%" align="center" >'.number_format_value_checker($PROGRAM_FA_UNITS,2).'</td>';
											}
											if($hours_comp){
												$txt .= '<td width="9%" align="center" >'.number_format_value_checker($COMPLETED_HOURS,2).'</td>';
											}
											if($hours_sched){
												$txt .= '<td width="9%" align="center" >'.number_format_value_checker($SCHEDULE_HOURS,2).'</td>';
											}
											if($program_hours){
												$txt .= '<td width="7%" align="center" >'.number_format_value_checker($PROGRAM_HOURS,2).'</td>';
											}
							
											if($CUMULATIVE_GPA == '1'){
												$txt .= '<td width="5%" align="center" >'. number_format_value_checker($tc_gpa_weighted,2).'</td>';
												
											}
											

											
											
									$txt .='</tr>';
											
								$res_tc->MoveNext();
							} 

							if($res_tc->RecordCount() > 0) {
								$txt .= '<tr>
											<td colspan="3" align="center" ><i>Term Transfer Total: </i></td>';

											if($units_attemp){
												$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_att_sub_tot,2).'</b></i></td>';
											}
											if($units_comp){
												$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_comp_sub_tot,2).'</b></i></td>';
											}
											if($program_units){
												$txt .= '<td width="7%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_unit_sub_tot,2).'</b></i></td>';
											}
											if($fa_units_attemp){
												$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_fa_in_att_sub_tot,2).'</b></i></td>';
											}
											if($fa_units_comp){
												$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_fa_in_comp_sub_tot,2).'</b></i></td>';
											}
											if($program_fa_units){
												$txt .= '<td width="7%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_fa_unit_sub_tot,2).'</b></i></td>';
											}
											if($hours_comp){
												$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_comp_hour_sub_tot,2).'</b></i></td>';
											}
											if($hours_sched){
												$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_sched_hour_sub_tot,2).'</b></i></td>';
											}
											if($program_hours){
												$txt .= '<td width="7%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_hour_sub_tot,2).'</b></i></td>';
											}

											if($CUMULATIVE_GPA == '1'){
												$txt .= '<td width="5%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($tc_gpa_weighted,2).'</b></i></td>';
											}
									 

											

								$txt .= '</tr>
								<tr>
									<td colspan="3" align="center" ><i><b style="font-size:35px">Cumulative Total: </b></i></td>';

										
									if($units_attemp){
										$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_att_tot,2).'</b></i></td>';
									}
									if($units_comp){
										$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_comp_tot,2).'</b></i></td>';
									}
									if($program_units){
										$txt .= '<td width="7%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_unit_tot,2).'</b></i></td>';
									}
									if($fa_units_attemp){
										$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_fa_in_att_tot,2).'</b></i></td>';
									}
									if($fa_units_comp){
										$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_fa_in_comp_tot,2).'</b></i></td>';
									}
									if($program_fa_units){
										$txt .= '<td width="7%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_fa_unit_tot,2).'</b></i></td>';
									}
									if($hours_comp){
										$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_comp_hour_tot,2).'</b></i></td>';
									}
									if($hours_sched){
										$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_sched_hour_tot,2).'</b></i></td>';
									}
									if($program_hours){
										$txt .= '<td width="7%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_hour_tot,2).'</b></i></td>';
									}

									// if($CUMULATIVE_GPA == '1'){
									// 	$txt .= '<td width="5%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker(($tc_gpa_value_total/$tc_gpa_weight_total),2).'</b></i></td>';
									// }

									if($CUMULATIVE_GPA == '1'){
										$txt .= '<td width="5%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</b></i></td>';
									}
									
										
								$txt .= '</tr>';
							}
						}
					}
					// End Term: Transfer -  (Transfer Credit)
	    $txt .= '</table>';

		##END OF TRANSFER TERM BLOCK 
		$eval_array = [];			
		#START : FOR NORMAL TERMS 
		$res_term = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE 
		FROM S_STUDENT_COURSE 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, M_COURSE_OFFERING_STUDENT_STATUS 
		WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER By BEGIN_DATE_1 ASC");

		while (!$res_term->EOF) {
			$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];
			$BEGIN_DATE 	= $res_term->fields['BEGIN_DATE'];
			
			$txt .= '
					<table border="0.5" cellspacing="0" cellpadding="0" >
						<tr> 
							<td >
								<table border="0.5" cellspacing="0" cellpadding="3"  >
									<tr>
										<td ><i style="font-size:45px">Term: '.$res_term->fields['BEGIN_DATE'].'</i></td>
									</tr>'; // nobr="true"

									$total_rec				= 0;
									// $c_in_num_grade_sub_tot = 0;
									$c_in_att_sub_tot 		= 0;
									$c_fa_in_att_sub_tot 	= 0;
									$c_in_comp_sub_tot 		= 0;
									$c_fa_in_comp_sub_tot 	= 0;
									// $c_in_cu_sub_gnu 		= 0;
									// $c_in_gpa_sub_tot 		= 0;
									// $c_in_prog_unit_tot     = 0;
									// $c_in_prog_hour_tot     = 0;
									$c_in_prog_unit_sub_tot = 0;
									// $c_in_prog_fa_unit_tot  = 0;
									$c_in_prog_fa_unit_sub_tot = 0;
									// $c_in_prog_comp_hour_tot = 0;
									$c_in_prog_comp_hour_sub_tot = 0;
									// $c_in_sched_hour_tot 	 = 0; 
									$c_in_sched_hour_sub_tot = 0; 
									$c_in_prog_hour_sub_tot = 0;
									
									$Sub_Denominator = 0;
									$Sub_Numerator 	 = 0;
									$Sub_Numerator1  = 0;

									// DIAM-787, new calulated gpa	
									$gpa_value_total=0;
									$gpa_weight_total=0;
									// End DIAM-787, new calulated gpa
									
									$res_course = $db->Execute("SELECT NUMERIC_GRADE, COURSE_UNITS, NUMBER_GRADE 
									from S_STUDENT_COURSE 
									LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
									LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE ,M_COURSE_OFFERING_STUDENT_STATUS, S_GRADE 
									WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 AND  S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE $en_cond AND CALCULATE_GPA = 1 ");

									while (!$res_course->EOF) {
										$Denominator += $res_course->fields['COURSE_UNITS'];
										$Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
										$Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
										
										$Sub_Denominator += $res_course->fields['COURSE_UNITS'];
										$Sub_Numerator	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMERIC_GRADE'];
										$Sub_Numerator1	 += $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE'];
									
										$res_course->MoveNext();
									}

									$res_course = $db->Execute("SELECT 
											TRANSCRIPT_CODE, 
											COURSE_DESCRIPTION, 
											S_COURSE.FA_UNITS, 
											S_STUDENT_COURSE.PK_COURSE_OFFERING, 
											FINAL_GRADE, 
											S_GRADE.GRADE, 
											NUMERIC_GRADE, 
											S_GRADE.NUMBER_GRADE, 
											S_GRADE.CALCULATE_GPA, 
											S_GRADE.UNITS_ATTEMPTED, 
											S_GRADE.UNITS_COMPLETED, 
											S_GRADE.UNITS_IN_PROGRESS, 
											S_STUDENT_COURSE.COURSE_UNITS, 
											S_COURSE.HOURS AS PROGRAM_HOURS, 
											S_COURSE.UNITS AS PROGRAM_UNITS, 
											S_COURSE.FA_UNITS AS PROGRAM_FA_UNITS, 
											M_CAMPUS_PROGRAM.UNITS AS SAP_PROGRAM_UNITS, 
											M_CAMPUS_PROGRAM.FA_UNITS AS SAP_PROGRAM_FA_UNITS, 
											M_CAMPUS_PROGRAM.HOURS AS SAP_PROGRAM_HOURS, 
											CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
											CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT 
										FROM 
											S_STUDENT_COURSE 
											LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
											LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
											LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
											LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT 
											LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, 
											M_COURSE_OFFERING_STUDENT_STATUS 
										WHERE 
											S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
											AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' 
											AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
											AND SHOW_ON_TRANSCRIPT = 1 
											AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENR' 
											AND M_CAMPUS_PROGRAM.PK_SAP_SCALE = '$aPK_SAP_SCALE' 
										ORDER BY 
											TRANSCRIPT_CODE ASC ");	
									// dds($res_course); dvb
									 // echo '<pre>';
									  // print_r($res_course->fields);
									while (!$res_course->EOF) { 

										$SAP_PROGRAM_UNITS    = $res_course->fields['SAP_PROGRAM_UNITS'];
										$SAP_PROGRAM_FA_UNITS = $res_course->fields['SAP_PROGRAM_FA_UNITS'];
										$SAP_PROGRAM_HOURS    = $res_course->fields['SAP_PROGRAM_HOURS'];

										$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
										$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
										// Query for get the COMPLETED_HOURS, SCHEDULE_HOURS
										$res_attendance_hours = $db->Execute("SELECT  
												sum(S_STUDENT_SCHEDULE.HOURS) as SCHEDULE_HOURS, 
												sum(ATTENDANCE_HOURS) as COMPLETED_HOURS 
											FROM 
												S_STUDENT_SCHEDULE 
												LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
												LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL
											WHERE 
												S_STUDENT_SCHEDULE.PK_STUDENT_MASTER =  '$PK_STUDENT_MASTER'
												AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENR'
												AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
												AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'
												AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ");
										
										// echo '<pre>';
										// echo "SELECT  
										// 		sum(S_STUDENT_SCHEDULE.HOURS) as SCHEDULE_HOURS, 
										// 		sum(ATTENDANCE_HOURS) as COMPLETED_HOURS 
										// 	FROM 
										// 		S_STUDENT_SCHEDULE 
										// 		LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
										// 		LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_ATTENDANCE.PK_COURSE_OFFERING_SCHEDULE_DETAIL
										// 	WHERE 
										// 		S_STUDENT_SCHEDULE.PK_STUDENT_MASTER =  '$PK_STUDENT_MASTER'
										// 		AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENR'
										// 		AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
										// 		AND S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'
										// 		AND S_STUDENT_ATTENDANCE.COMPLETED = 1 ";
										// echo 'res_attendance_hours';
										 // print_r($res_attendance_hours);

										$COMPLETED_UNITS	 = 0;
										$ATTEMPTED_UNITS	 = 0;
										$PROGRAM_UNITS       = 0;
										
										$FA_COMPLETED_UNITS	 = 0;
										$FA_ATTEMPTED_UNITS	 = 0;
										$PROGRAM_FA_UNITS    = 0;

										$COMPLETED_HOURS     = 0;
										$SCHEDULE_HOURS      = 0;
										$PROGRAM_HOURS       = 0;
										
										if($res_course->fields['UNITS_ATTEMPTED'] == 1) {
											$ATTEMPTED_UNITS 	 = $res_course->fields['COURSE_UNITS'];
											$FA_ATTEMPTED_UNITS	 = $res_course->fields['FA_UNITS'];
										}
										
										$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
										$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
										
										$c_fa_in_att_sub_tot 	+= $FA_ATTEMPTED_UNITS; 
										$c_fa_in_att_tot 		+= $FA_ATTEMPTED_UNITS; 
										
										if($res_course->fields['UNITS_COMPLETED'] == 1) { 
											$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
											$FA_COMPLETED_UNITS	 = $res_course->fields['FA_UNITS'];
											
											$c_in_comp_tot  	+= $COMPLETED_UNITS;
											$c_in_comp_sub_tot  += $COMPLETED_UNITS;
											
											$c_fa_in_comp_sub_tot   += $FA_COMPLETED_UNITS;
											$c_fa_in_comp_tot  		+= $FA_COMPLETED_UNITS;
											
										}

										$PROGRAM_UNITS = $res_course->fields['PROGRAM_UNITS'];
										$c_in_prog_unit_tot     += $PROGRAM_UNITS; 
										$c_in_prog_unit_sub_tot += $PROGRAM_UNITS; 										

										$PROGRAM_FA_UNITS = $res_course->fields['PROGRAM_FA_UNITS'];
										$c_in_prog_fa_unit_tot 	    += $PROGRAM_FA_UNITS; 
										$c_in_prog_fa_unit_sub_tot 	+= $PROGRAM_FA_UNITS;

										$COMPLETED_HOURS = $res_attendance_hours->fields['COMPLETED_HOURS'];
										$c_in_prog_comp_hour_tot 	 += $COMPLETED_HOURS; 
										$c_in_prog_comp_hour_sub_tot += $COMPLETED_HOURS;

										// echo 'c_in_prog_comp_hour_tot|='.$c_in_prog_comp_hour_tot;
										// echo 'c_in_prog_comp_hour_sub_tot|='.$c_in_prog_comp_hour_tot;

										$SCHEDULE_HOURS  = $res_attendance_hours->fields['SCHEDULE_HOURS'];

										$c_in_sched_hour_tot 	    += $SCHEDULE_HOURS; 
										$c_in_sched_hour_sub_tot 	+= $SCHEDULE_HOURS;

										$PROGRAM_HOURS = $res_course->fields['PROGRAM_HOURS'];
										$c_in_prog_hour_tot 	    += $PROGRAM_HOURS; 
										$c_in_prog_hour_sub_tot 	+= $PROGRAM_HOURS;
										
										$gnu = 0;
										$gpa = 0;
										if($res_course->fields['CALCULATE_GPA'] == 1) {
											$gnu 				 = $res_course->fields['COURSE_UNITS'] * $res_course->fields['NUMBER_GRADE']; 
											$c_in_cu_gnu 		+= $gnu; 
											$c_in_cu_sub_gnu 	+= $gnu; 
											
											$gpa				= $gnu / $COMPLETED_UNITS;;
											$c_in_gpa_sub_tot 	+= $gpa;
											$c_in_gpa_tot 		+= $gpa;
											
											$total_rec++;
											$total_cum_rec++;
											
											$c_in_num_grade_sub_tot += $res_course->fields['NUMERIC_GRADE'];
											$c_in_num_grade_tot		+= $res_course->fields['NUMERIC_GRADE'];

											// DIAM-787, new calulated gpa
											$GPA_VALULE 		     = $res_course->fields['GPA_VALUE']; 
											$gpa_value_total 		+= $GPA_VALULE; 
											$GPA_WEIGHT 		     = $res_course->fields['GPA_WEIGHT']; 
											$gpa_weight_total 		+= $GPA_WEIGHT; 
											// End DIAM-787, new calulated gpa
										}

										// DIAM-787, new calulated gpa
										$gpa_weighted_val = 0;
										if($gpa_value_total>0)
										{
											$gpa_weighted_val        = ($gpa_value_total/$gpa_weight_total);
										}
										$summation_of_gpa += $GPA_VALULE;
										$summation_of_weight += $GPA_WEIGHT;
										// End DIAM-787, new calulated gpa

										// DIAM-2043
										$res_sap_scale_setup_data = $db->Execute("SELECT * FROM 
																						S_SAP_SCALE_SETUP_DETAIL AS SAP_DET
																					WHERE 
																						SAP_DET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
																						AND SAP_DET.ACTIVE = '1' 
																						AND SAP_DET.PK_SAP_SCALE = '$PK_SAP_SCALE' ");
										while (!$res_sap_scale_setup_data->EOF) { 

											$PROGRAM_PACE_PERCENTAGE = $res_sap_scale_setup_data->fields['PROGRAM_PACE_PERCENTAGE'];
											$PERIOD_CAL              = $res_sap_scale_setup_data->fields['PERIOD'];

											if($PK_PROGRAM_PACE != '')
											{
												switch ($PK_PROGRAM_PACE) {
													case '1': // Hours Completed
														$final_hours_completed_val = ($SAP_PROGRAM_HOURS * $PROGRAM_PACE_PERCENTAGE) / 100;
														// $c_in_prog_comp_hour_tot;
														break;
													case '2': // Hours Scheduled
														$final_hours_scheduled_val = ($SAP_PROGRAM_HOURS * $PROGRAM_PACE_PERCENTAGE) / 100;
														//$c_in_sched_hour_tot;
														break;
													case '3': // FA Units Completed
														$final_fa_units_completed_val =  ($SAP_PROGRAM_FA_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100; 
														//$c_fa_in_comp_tot;
														break;
													case '4': // FA Units Attempted
														$final_fa_units_attempted_val =  ($SAP_PROGRAM_FA_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100;
														//$c_fa_in_att_tot;
														break;	
													case '5': // Units Completed
														$final_units_completed_val =  ($SAP_PROGRAM_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100;
														//$c_in_comp_tot;
														break;	
													case '6': // Units Attempted
														$final_units_attempted_val =  ($SAP_PROGRAM_UNITS * $PROGRAM_PACE_PERCENTAGE) / 100;
														//$c_in_att_tot;
														break;				
													default:
														# code...
														break;
												}
											}
												
												// echo 'res_sp_set';
												// print_r($res_sp_set->fields);
												// echo '<br>';
											// if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){
											// 	$Data_Grade = $res_course->fields['GRADE'];

											// 	echo 'eval_arraylast';
											// 	if($Data_Grade != '-' || 1==1) // dvb
											// 	{
											// 		echo 'todos entran aqui?';
											// 		if($final_hours_completed_val >= $c_in_prog_comp_hour_tot)
											// 		{
											// 			$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
											// 			'total_prog_unit_tot'=>$c_in_prog_unit_tot,
											// 			'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
											// 			'total_prog_hour_tot'=>$c_in_prog_hour_tot,
											// 			'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
											// 		}
											// 		echo $final_hours_scheduled_val.'|¿c_in_sched_hour_tot?|'.$c_in_sched_hour_tot;
											// 		if($final_hours_scheduled_val <= $c_in_sched_hour_tot)
											// 		{

											// 			$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
											// 			'total_prog_unit_tot'=>$c_in_prog_unit_tot,
											// 			'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
											// 			'total_prog_hour_tot'=>$c_in_prog_hour_tot,
											// 			'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
											// 			print_r('eval_array');
											// 			print_r($eval_array[$PERIOD_CAL]);
											// 		}

											// 		if($final_fa_units_completed_val >= $c_fa_in_comp_tot)
											// 		{
											// 			$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
											// 			'total_prog_unit_tot'=>$c_in_prog_unit_tot,
											// 			'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
											// 			'total_prog_hour_tot'=>$c_in_prog_hour_tot,
											// 			'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
											// 		}
											// 		if($final_fa_units_attempted_val >= $c_fa_in_att_tot)
											// 		{
											// 			$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
											// 			'total_prog_unit_tot'=>$c_in_prog_unit_tot,
											// 			'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
											// 			'total_prog_hour_tot'=>$c_in_prog_hour_tot,
											// 			'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
											// 		}
											// 		if($final_units_completed_val >= $c_in_comp_tot)
											// 		{
											// 			$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
											// 			'total_prog_unit_tot'=>$c_in_prog_unit_tot,
											// 			'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
											// 			'total_prog_hour_tot'=>$c_in_prog_hour_tot,
											// 			'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
											// 		}
											// 		if($final_units_attempted_val >= $c_in_att_tot)
											// 		{
											// 			$eval_array[$PERIOD_CAL] = array('total_prog_comp_hour_tot'=>$c_in_prog_comp_hour_tot,'total_sched_hour_tot'=>$c_in_sched_hour_tot,'total_units_att'=>$c_in_att_tot,'total_units_compt'=>$c_in_comp_tot,'total_fa_units_att'=>$c_fa_in_att_tot,'total_fa_units_compt'=>$c_fa_in_comp_tot,
											// 			'total_prog_unit_tot'=>$c_in_prog_unit_tot,
											// 			'total_prog_fa_unit_tot'=>$c_in_prog_fa_unit_tot,
											// 			'total_prog_hour_tot'=>$c_in_prog_hour_tot,
											// 			'new_total_cum_gpa'=>($summation_of_gpa/$summation_of_weight));
											// 		}

											// 	}
											// }
											// dvb 30 06 2025
											if ($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3) {
											    $Data_Grade = $res_course->fields['GRADE'];

											    // El if siempre se cumple por '|| 1==1', pero se mantiene para depuración

											    if ($Data_Grade != '-' || 1 == 1) {
											        // echo 'todos entran aqui?';

											        if (
											        	(
												            $c_in_prog_comp_hour_tot >= $final_hours_completed_val && !empty($final_hours_completed_val) ||
												            $c_in_sched_hour_tot >= $final_hours_scheduled_val && !empty($final_hours_scheduled_val) ||
												            $c_fa_in_comp_tot >= $final_fa_units_completed_val && !empty($final_fa_units_completed_val) ||
												            $c_fa_in_att_tot >= $final_fa_units_attempted_val && !empty($final_fa_units_attempted_val) ||
												            $c_in_comp_tot >= $final_units_completed_val && !empty($final_units_completed_val) ||
												            $c_in_att_tot >= $final_units_attempted_val && !empty($final_units_attempted_val)
												        )
											            &&
											            !isset($eval_array[$PERIOD_CAL])
											        ) {
											            $eval_array[$PERIOD_CAL] = array(
											                'total_prog_comp_hour_tot' => $c_in_prog_comp_hour_tot,
											                'total_sched_hour_tot'     => $c_in_sched_hour_tot,
											                'total_units_att'          => $c_in_att_tot,
											                'total_units_compt'        => $c_in_comp_tot,
											                'total_fa_units_att'       => $c_fa_in_att_tot,
											                'total_fa_units_compt'     => $c_fa_in_comp_tot,
											                'total_prog_unit_tot'      => $c_in_prog_unit_tot,
											                'total_prog_fa_unit_tot'   => $c_in_prog_fa_unit_tot,
											                'total_prog_hour_tot'      => $c_in_prog_hour_tot,
											                'new_total_cum_gpa'        => ($summation_of_gpa / $summation_of_weight)
											            );

											            // echo 'eval_array';
											            // echo '<pre>';
											            // echo '$PERIOD_CAL'.$PERIOD_CAL;
											            // print_r($eval_array[$PERIOD_CAL]);
											            
											        }

											        // echo $final_hours_scheduled_val . '|¿c_in_sched_hour_tot?|'. $c_in_sched_hour_tot;
											    }
											}
												

											$res_sap_scale_setup_data->MoveNext();

										}
										// End DIAM-2043

										if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){
											$Data_Grade = $res_course->fields['GRADE'];
											if($Data_Grade != '-')
											{
												$my_values = ($gpa_value_total/$gpa_weight_total);
												$total_cum_gpa = $my_values;
												$new_total_cum_gpa = ($summation_of_gpa/$summation_of_weight);
												//echo $new_total_cum_gpa."<br>";
												$total_units_att = $c_in_att_tot;
												$total_units_compt = $c_in_comp_tot;
												$total_fa_units_att = $c_fa_in_att_tot;
												$total_fa_units_compt = $c_fa_in_comp_tot;
												$total_prog_unit_tot = $c_in_prog_unit_tot;
												$total_prog_fa_unit_tot = $c_in_prog_fa_unit_tot;

												$total_prog_comp_hour_tot = $c_in_prog_comp_hour_tot;
												$total_sched_hour_tot = $c_in_sched_hour_tot;
												$total_prog_hour_tot = $c_in_prog_hour_tot;
											}
										}
										
										$txt .= '<tr>
													<td width="6%" >'.$res_course->fields['TRANSCRIPT_CODE'].'</td>
													<td width="8%" >'.$res_course->fields['COURSE_DESCRIPTION'].'</td>';
													
													if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
													{
														$txt .= '<td width="6%" align="center" >'.$res_course->fields['GRADE'].'</td>';
													}

													if($units_attemp){
														$txt .= '<td width="9%" align="center" >'.number_format_value_checker($ATTEMPTED_UNITS,2).'</td>';
													}
													if($units_comp){
														$txt .= '<td width="9%" align="center" >'.number_format_value_checker($COMPLETED_UNITS,2).'</td>';
													}
													if($program_units){
														$txt .= '<td width="7%" align="center" >'.number_format_value_checker($PROGRAM_UNITS,2).'</td>';
													}
													if($fa_units_attemp){
														$txt .= '<td width="9%" align="center" >'.number_format_value_checker($FA_ATTEMPTED_UNITS,2).'</td>';
													}
													if($fa_units_comp){
														$txt .= '<td width="9%" align="center" >'.number_format_value_checker($FA_COMPLETED_UNITS,2).'</td>';
													}
													if($program_fa_units){
														$txt .= '<td width="7%" align="center" >'.number_format_value_checker($PROGRAM_FA_UNITS,2).'</td>';
													}
													if($hours_comp){
														$txt .= '<td width="9%" align="center" >'.number_format_value_checker($COMPLETED_HOURS,2).'</td>';
													}
													if($hours_sched){
														$txt .= '<td width="9%" align="center" >'.number_format_value_checker($SCHEDULE_HOURS,2).'</td>';
													}
													if($program_hours){
														$txt .= '<td width="7%" align="center" >'.number_format_value_checker($PROGRAM_HOURS,2).'</td>';
													}
									
													if($CUMULATIVE_GPA == '1'){
														$txt .= '<td width="5%" align="center" >'.number_format_value_checker($gpa_weighted_val,2).'</td>';
													}
													
										$txt .= '</tr>';
						
										$res_course->MoveNext();
									} 

										
									$txt .= '<tr>
												<td colspan="3" align="center" ><i><b>Term '.$BEGIN_DATE.' Total: </i></b></td>';

												if($units_attemp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_att_sub_tot,2).'</b></i></td>';
												}
												if($units_comp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_comp_sub_tot,2).'</b></i></td>';
												}
												if($program_units){
													$txt .= '<td width="7%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_unit_sub_tot,2).'</b></i></td>';
												}
												if($fa_units_attemp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_fa_in_att_sub_tot,2).'</b></i></td>';
												}
												if($fa_units_comp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_fa_in_comp_sub_tot,2).'</b></i></td>';
												}
												if($program_fa_units){
													$txt .= '<td width="7%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_fa_unit_sub_tot,2).'</b></i></td>';
												}
												if($hours_comp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_comp_hour_sub_tot,2).'</b></i></td>';
												}
												if($hours_sched){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_sched_hour_sub_tot,2).'</b></i></td>';
												}
												if($program_hours){
													$txt .= '<td width="7%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($c_in_prog_hour_sub_tot,2).'</b></i></td>';
												}

												if($CUMULATIVE_GPA == '1'){
													$txt .= '<td width="5%" align="center" ><i><b style="font-size:30px">'.number_format_value_checker($gpa_weighted_val,2).'</b></i></td>';
												}

												
												
									$txt .= '</tr>
											<tr>
												<td colspan="3" align="center" ><i><b style="font-size:35px">Cumulative Total: </b></i></td>';

												if($units_attemp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_att_tot,2).'</b></i></td>';
												}
												if($units_comp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_comp_tot,2).'</b></i></td>';
												}
												if($program_units){
													$txt .= '<td width="7%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_unit_tot,2).'</b></i></td>';
												}
												if($fa_units_attemp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_fa_in_att_tot,2).'</b></i></td>';
												}
												if($fa_units_comp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_fa_in_comp_tot,2).'</b></i></td>';
												}
												if($program_fa_units){
													$txt .= '<td width="7%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_fa_unit_tot,2).'</b></i></td>';
												}
												if($hours_comp){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_comp_hour_tot,2).'</b></i></td>';
												}
												if($hours_sched){
													$txt .= '<td width="9%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_sched_hour_tot,2).'</b></i></td>';
												}
												if($program_hours){
													$txt .= '<td width="7%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker($c_in_prog_hour_tot,2).'</b></i></td>';
												}

												// if($CUMULATIVE_GPA == '1'){
												// 	$txt .= '<td width="5%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker(($gpa_value_total/$gpa_weight_total),2).'</b></i></td>';
												// }

												if($CUMULATIVE_GPA == '1'){
													$txt .= '<td width="5%" align="center" ><i><b style="font-size:35px">'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</b></i></td>';
												}
												
									$txt .= '</tr>
								</table>
							</td>
						</tr>
					</table>';
									
			$res_term->MoveNext();
		}
		
		// Display Attendance

		// End Display Attendance

		// Sap Evalution
		$data1 = $data2 = $data3 = $data4 = $data5 = $data6 = $data7 = $data8 = $data9 = $data10 = '';
		if($HOURS_COMPLETED_SCHEDULED == '1'){
			$data1 = '<td>Hours Completed/<br>Scheduled</td>';
		}
		if($HOURS_COMPLETED_PROGRAM == '1'){
			$data2 = '<td>Hours Completed/<br>Program</td>';
		}
		if($HOURS_SCHEDULED_PROGRAM == '1'){
			$data3 = '<td>Hours Scheduled/<br>Program</td>';
		}

		if($FA_UNITS_COMPLETED_ATTEMPTED == '1'){
			$data4 = '<td>FA Units Completed/<br>Attempted</td>';
		}
		if($FA_UNITS_COMPLETED_PROGRAM == '1'){
			$data5 = '<td>FA Units Completed/<br>Program</td>';
		}
		if($FA_UNITS_ATTEMPTED_PROGRAM == '1'){
			$data6 = '<td>FA Units Attempted/<br>Program</td>';
		}

		if($UNITS_COMPLETED_ATTEMPTED == '1'){
			$data7 = '<td>Units Completed/<br>Attempted</td>';
		}
		if($UNITS_COMPLETED_PROGRAM == '1'){
			$data8 = '<td>Units Completed/<br>Program</td>';
		}
		if($UNITS_ATTEMPTED_PROGRAM == '1'){
			$data9 = '<td>Units Attempted/<br>Program</td>';
		}

		if($CUMULATIVE_GPA == '1'){
			$data10 = '<td>Cumulative GPA</td>';
		}

		$res_sap_scale_setup_detail = $db->Execute("SELECT SAP_WAR.SAP_WARNING,SAP_DET.* FROM S_SAP_SCALE_SETUP_DETAIL AS SAP_DET INNER JOIN S_SAP_WARNING AS SAP_WAR ON SAP_DET.PK_SAP_WARNING = SAP_WAR.PK_SAP_WARNING WHERE SAP_DET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SAP_WAR.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SAP_DET.ACTIVE = '1' AND SAP_WAR.ACTIVE = '1' AND SAP_DET.PK_SAP_SCALE = '$PK_SAP_SCALE'  ORDER BY SAP_DET.PERIOD ASC ");
		// echo 'res_sap_scale_setup_detail';
		// print_r($res_sap_scale_setup_detail->fields);

		$sap_warning = '';
		$final_sap_warning = '';
		
		$sap_warning_header = '
							<tr>
								<td></td>
								'.$data1.$data2.$data3.$data4.$data5.$data6.$data7.$data8.$data9.$data10.'
							</tr>';

		$Period = 1;
		$flag = '0';
		
		while (!$res_sap_scale_setup_detail->EOF) 
		{ 
			$SAP_WARNINGS            = $res_sap_scale_setup_detail->fields['SAP_WARNING'];
			$PROGRAM_PACE_PERCENTAGE = $res_sap_scale_setup_detail->fields['PROGRAM_PACE_PERCENTAGE'];
			$PERIOD                  = $res_sap_scale_setup_detail->fields['PERIOD'];
			$PERIODS 				 = $eval_array[$PERIOD];
			// dvb
			 // echo "<pre>";
			// echo 'PERIODS';
			//  print_r($PERIODS);
			//  ECHO '<BR>';

			$s_total_units_att = $PERIODS['total_units_att'] ? $PERIODS['total_units_att'] : '0.00';
			$s_total_units_compt = $PERIODS['total_units_compt'] ? $PERIODS['total_units_compt'] : '0.00';
			$s_total_prog_unit_tot = $PERIODS['total_prog_unit_tot'] ? $PERIODS['total_prog_unit_tot'] : '0.00';

			$cum_course_unit1 = ($s_total_units_compt / $s_total_units_att) * 100 ; // Units Completed/Hours Attempted
			$cum_course_unit2 = ($s_total_units_compt / $s_total_prog_unit_tot) * 100 ; // Units Completed/Program Units
			$cum_course_unit3 = ($s_total_units_att / $s_total_prog_unit_tot) * 100 ; // Units Attempted/Program Units

			$s_total_fa_units_att = $PERIODS['total_fa_units_att'] ? $PERIODS['total_fa_units_att'] : '0.00';
			$s_total_fa_units_compt = $PERIODS['total_fa_units_compt'] ? $PERIODS['total_fa_units_compt'] : '0.00';
			$s_total_prog_fa_unit_tot = $PERIODS['total_prog_fa_unit_tot'] ? $PERIODS['total_prog_fa_unit_tot'] : '0.00';

			$cum_course_unit4 = ($s_total_fa_units_compt / $s_total_fa_units_att) * 100 ; // FA Units Completed/FA Units Attempted
			$cum_course_unit5 = ($s_total_fa_units_compt / $s_total_prog_fa_unit_tot) * 100 ; // FA Units Completed/Program FA Units
			$cum_course_unit6 = ($s_total_fa_units_att / $s_total_prog_fa_unit_tot) * 100 ; // FA Units Attempted/Program FA Units

			$s_total_prog_comp_hour_tot = $PERIODS['total_prog_comp_hour_tot'] ? $PERIODS['total_prog_comp_hour_tot'] : '0.00';
			$s_total_sched_hour_tot = $PERIODS['total_sched_hour_tot'] ? $PERIODS['total_sched_hour_tot'] : '0.00';
			$s_total_prog_hour_tot = $PERIODS['total_prog_hour_tot'] ? $PERIODS['total_prog_hour_tot'] : '0.00';

			$cum_course_unit7 = ($s_total_prog_comp_hour_tot / $s_total_sched_hour_tot) * 100 ; // Hours Completed/Hours Scheduled
			$cum_course_unit8 = ($s_total_prog_comp_hour_tot / $s_total_prog_hour_tot) * 100 ; // Hours Completed/Program Hours
			$cum_course_unit9 = ($s_total_sched_hour_tot / $s_total_prog_hour_tot) * 100 ; // Hours Scheduled/Program Hours

			$s_total_cum_gpa = $PERIODS['new_total_cum_gpa'] ? $PERIODS['new_total_cum_gpa'] : '0.00';

			$txt_hour_com_sch = '';
			$txt_hour_com_prog = '';
			$txt_hour_sch_prog = '';

			$txt_fa_comp_att = '';
			$txt_fa_comp_prog = '';
			$txt_fa_att_prog = '';

			$txt_unit_com_att = '';
			$txt_unit_com_prog = '';
			$txt_unit_att_prog = '';
			
			$txt_cum_gpa = '';
			
			if($HOURS_COMPLETED_SCHEDULED == '1'){
				$txt_hour_com_sch = '<tr>
										<td width="20%"></td>
										<td width="20%">Hours Completed : '.number_format_value_checker($s_total_prog_comp_hour_tot,2).'</td>
										<td width="20%">Hours Scheduled : '.number_format_value_checker($s_total_sched_hour_tot,2).'</td>
										<td width="30%">Hours Completed/Scheduled
										: '.number_format_value_checker($cum_course_unit7,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';
			}
			if($HOURS_COMPLETED_PROGRAM == '1'){
				$txt_hour_com_prog = '<tr>
										<td width="20%"></td>
										<td width="20%">Hours Completed : '.number_format_value_checker($s_total_prog_comp_hour_tot,2).'</td>
										<td width="20%">Hours Program : '.number_format_value_checker($s_total_prog_hour_tot,2).'</td>
										<td width="30%">Hours Completed/Program
										: '.number_format_value_checker($cum_course_unit8,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';

			}
			if($HOURS_SCHEDULED_PROGRAM == '1'){
				$txt_hour_sch_prog = '<tr>
										<td width="20%"></td>
										<td width="20%">Hours Scheduled : '.number_format_value_checker($s_total_sched_hour_tot,2).'</td>
										<td width="20%">Hours Program : '.number_format_value_checker($s_total_prog_hour_tot,2).'</td>
										<td width="30%">Hours Scheduled/Program
										: '.number_format_value_checker($cum_course_unit9,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';

			}

			if($FA_UNITS_COMPLETED_ATTEMPTED == '1'){
				$txt_fa_comp_att = '<tr>
										<td width="20%"></td>
										<td width="20%">FA Units Attempted : '.number_format_value_checker($s_total_fa_units_att,2).'</td>
										<td width="20%">FA Units Completed : '.number_format_value_checker($s_total_fa_units_compt,2).'</td>
										<td width="30%">FA Units Completed/Attempted
										: '.number_format_value_checker($cum_course_unit4,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';
			}
			if($FA_UNITS_COMPLETED_PROGRAM == '1'){
				$txt_fa_comp_prog = '<tr>
										<td width="20%"></td>
										<td width="20%">FA Units Program : '.number_format_value_checker($s_total_prog_fa_unit_tot,2).'</td>
										<td width="20%">FA Units Completed : '.number_format_value_checker($s_total_fa_units_compt,2).'</td>
										<td width="30%">FA Units Completed/Program
										: '.number_format_value_checker($cum_course_unit5,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';

			}
			if($FA_UNITS_ATTEMPTED_PROGRAM == '1'){
				$txt_fa_att_prog = '<tr>
										<td width="20%"></td>
										<td width="20%">FA Units Attempted : '.number_format_value_checker($s_total_fa_units_att,2).'</td>
										<td width="20%">FA Units Program : '.number_format_value_checker($s_total_prog_fa_unit_tot,2).'</td>
										<td width="30%">FA Units Program/Attempted
										: '.number_format_value_checker($cum_course_unit6,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';

			}

			if($UNITS_COMPLETED_ATTEMPTED == '1'){
				$txt_unit_com_att = '<tr>
										<td width="20%"></td>
										<td width="20%">Units Attempted : '.number_format_value_checker($s_total_units_att,2).'</td>
										<td width="20%">Units Completed : '.number_format_value_checker($s_total_units_compt,2).'</td>
										<td width="30%">Units Completed/Attempted : '.number_format_value_checker($cum_course_unit1,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';
			}
			if($UNITS_COMPLETED_PROGRAM == '1'){
				$txt_unit_com_prog = '<tr>
										<td width="20%"></td>
										<td width="20%">Units Program : '.number_format_value_checker($s_total_prog_unit_tot,2).'</td>
										<td width="20%">Units Completed : '.number_format_value_checker($s_total_units_compt,2).'</td>
										<td width="30%">Units Completed/Program : '.number_format_value_checker($cum_course_unit2,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';

			}
			if($UNITS_ATTEMPTED_PROGRAM == '1'){
				$txt_unit_att_prog = '<tr>
										<td width="20%"></td>
										<td width="20%">Units Attempted : '.number_format_value_checker($s_total_units_att,2).'</td>
										<td width="20%">Units Program : '.number_format_value_checker($s_total_prog_unit_tot,2).'</td>
										<td width="30%">Units Attempted/Program : '.number_format_value_checker($cum_course_unit3,2).'%</td>
									</tr>
									<tr>
										<td><br></td>
									</tr>';

			}

			if($CUMULATIVE_GPA == '1'){
				$txt_cum_gpa = '<tr>
									<td width="20%"></td>
									<td width="20%">Cumulative GPA : '.number_format_value_checker($s_total_cum_gpa,2).'</td>
								</tr>
								<tr>
									<td><br><br></td>
								</tr>';
			}

			/* SAP Program Pace Calculations */
			if($PK_PROGRAM_PACE != '')
			{
				switch ($PK_PROGRAM_PACE) {
					case '1': // Hours Completed
						// echo $s_total_prog_comp_hour_tot .'/'. $SAP_PROGRAM_HOURS."<br>";
						$sap_program_pace_per = ($s_total_prog_comp_hour_tot / $SAP_PROGRAM_HOURS) * 100 ; // Hours Completed/Program Hours(This program come from Program setting)
						break;
					case '2': // Hours Scheduled
						$sap_program_pace_per = ($s_total_sched_hour_tot / $SAP_PROGRAM_HOURS) * 100 ; // Hours Scheduled/Program Hours(This program come from Program setting)
						break;
					case '3': // FA Units Completed
						$sap_program_pace_per = ($s_total_fa_units_compt / $SAP_PROGRAM_FA_UNITS) * 100 ; // FA Units Completed/Program FA Units Hours(This program come from Program setting)
						break;
					case '4': // FA Units Attempted
						$sap_program_pace_per = ($s_total_fa_units_att / $SAP_PROGRAM_FA_UNITS) * 100 ; // FA Units Attempted/Program FA Units Hours(This program come from Program setting)
						break;	
					case '5': // Units Completed
						$sap_program_pace_per = ($s_total_units_compt / $SAP_PROGRAM_UNITS) * 100 ; // Units Completed/Program Units Hours(This program come from Program setting)
						break;	
					case '6': // Units Attempted
						$sap_program_pace_per = ($s_total_units_att / $SAP_PROGRAM_UNITS) * 100 ; // Units Attempted/Program Units Attempted(This program come from Program setting)
						break;				
					default:
						# code...
						break;
				}
			}
			/* End SAP Program Pace Calculations */
			// dvb
			// echo '$SAP_PROGRAM_HOURS'.$SAP_PROGRAM_HOURS;
			// echo '<br>$s_total_sched_hour_tot'.$s_total_sched_hour_tot;
			// echo '<br>$PROGRAM_PACE_PERCENTAGE'.$PROGRAM_PACE_PERCENTAGE.'('.($PROGRAM_PACE_PERCENTAGE/100*$SAP_PROGRAM_HOURS).')';
			// echo '<br>$sap_program_pace_per'.$sap_program_pace_per;
			$final_flag = '0';
			if($PERIOD == '1')
			{
				if($PROGRAM_PACE_PERCENTAGE <= $sap_program_pace_per)
				{
					$flag = '1';
				}
				else{
					$flag = '0';
				}
			}
			else
			{
				if($flag == '1')
				{
					if($PROGRAM_PACE_PERCENTAGE <= $sap_program_pace_per)
					{
						$final_flag = '1';
					}
					else{
						$final_flag = '0';
					}
				}
			}
			
			// echo "Flag => ".$flag." | Final Flag => ".$final_flag."<br>";
			// echo $PROGRAM_PACE_PERCENTAGE .' <= '. $sap_program_pace_per;exit;

			$my_data1 = $my_data2 = $my_data3 = $my_data4 = $my_data5 = $my_data6 = $my_data7 = $my_data8 = $my_data9 = $my_data10 = '';
			$my_data1_1 = $my_data2_1 = $my_data3_1 = $my_data4_1 = $my_data5_1 = $my_data6_1 = $my_data7_1 = $my_data8_1 = $my_data9_1 = $my_data10_1 = '';
			$my_data1_2 = $my_data2_2 = $my_data3_2 = $my_data4_2 = $my_data5_2 = $my_data6_2 = $my_data7_2 = $my_data8_2 = $my_data9_2 = $my_data10_2 = '';
			if($HOURS_COMPLETED_SCHEDULED == '1'){
				$SAP_HOURS_COMPLETED_SCHEDULED = $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_COMPLETED_SCHEDULED'];

				if($cum_course_unit7 >= $SAP_HOURS_COMPLETED_SCHEDULED)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data1 = '<td align="center">'.$value.'</td>';
				$my_data1_1 = '<td align="center">'.$SAP_HOURS_COMPLETED_SCHEDULED.'</td>';
				$my_data1_2 = '<td align="center">'.number_format_value_checker($cum_course_unit7,2).'</td>';
			}
			if($HOURS_COMPLETED_PROGRAM == '1'){
				$SAP_HOURS_COMPLETED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_COMPLETED_PROGRAM'];

				if($cum_course_unit8 >= $SAP_HOURS_COMPLETED_PROGRAM)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data2 = '<td align="center">'.$value.'</td>';
				$my_data2_1 = '<td align="center">'.$SAP_HOURS_COMPLETED_PROGRAM.'</td>';
				$my_data2_2 = '<td align="center">'.number_format_value_checker($cum_course_unit8,2).'</td>';
			}
			if($HOURS_SCHEDULED_PROGRAM == '1'){
				$SAP_HOURS_SCHEDULED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_HOURS_SCHEDULED_PROGRAM'];

				if($cum_course_unit9 >= $SAP_HOURS_SCHEDULED_PROGRAM)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data3 = '<td align="center">'.$value.'</td>';
				$my_data3_1 = '<td align="center">'.$SAP_HOURS_SCHEDULED_PROGRAM.'</td>';
				$my_data3_2 = '<td align="center">'.number_format_value_checker($cum_course_unit9,2).'</td>';
			}

			if($FA_UNITS_COMPLETED_ATTEMPTED == '1'){
				$SAP_FA_UNITS_COMPLETED_ATTEMPTED = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED'];
				if($cum_course_unit4 >= $SAP_FA_UNITS_COMPLETED_ATTEMPTED)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data4 = '<td align="center">'.$value.'</td>';
				$my_data4_1 = '<td align="center">'.$SAP_FA_UNITS_COMPLETED_ATTEMPTED.'</td>';
				$my_data4_2 = '<td align="center">'.number_format_value_checker($cum_course_unit4,2).'</td>';
			}
			if($FA_UNITS_COMPLETED_PROGRAM == '1'){
				$SAP_FA_UNITS_COMPLETED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM'];
				if($cum_course_unit5 >= $SAP_FA_UNITS_COMPLETED_PROGRAM)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data5 = '<td align="center">'.$value.'</td>';
				$my_data5_1 = '<td align="center">'.$SAP_FA_UNITS_COMPLETED_PROGRAM.'</td>';
				$my_data5_2 = '<td align="center">'.number_format_value_checker($cum_course_unit5,2).'</td>';
			}
			if($FA_UNITS_ATTEMPTED_PROGRAM == '1'){
				$SAP_FA_UNITS_ATTEMPTED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM'];
				if($cum_course_unit6 >= $SAP_FA_UNITS_ATTEMPTED_PROGRAM)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data6 = '<td align="center">'.$value.'</td>';
				$my_data6_1 = '<td align="center">'.$SAP_FA_UNITS_ATTEMPTED_PROGRAM.'</td>';
				$my_data6_2 = '<td align="center">'.number_format_value_checker($cum_course_unit6,2).'</td>';
			}

			if($UNITS_COMPLETED_ATTEMPTED == '1'){
				$SAP_UNITS_COMPLETED_ATTEMPTED = $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_COMPLETED_ATTEMPTED'];
				if($cum_course_unit1 >= $SAP_UNITS_COMPLETED_ATTEMPTED)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data7 = '<td align="center">'.$value.'</td>';
				$my_data7_1 = '<td align="center">'.$SAP_UNITS_COMPLETED_ATTEMPTED.'</td>';
				$my_data7_2 = '<td align="center">'.number_format_value_checker($cum_course_unit1,2).'</td>';
			}
			if($UNITS_COMPLETED_PROGRAM == '1'){
				$SAP_UNITS_COMPLETED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_COMPLETED_PROGRAM'];

				if($cum_course_unit2 >= $SAP_UNITS_COMPLETED_PROGRAM)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data8 = '<td align="center">'.$value.'</td>';
				$my_data8_1 = '<td align="center">'.$SAP_UNITS_COMPLETED_PROGRAM.'</td>';
				$my_data8_2 = '<td align="center">'.number_format_value_checker($cum_course_unit2,2).'</td>';
			}
			if($UNITS_ATTEMPTED_PROGRAM == '1'){
				$SAP_UNITS_ATTEMPTED_PROGRAM = $res_sap_scale_setup_detail->fields['CUMULATIVE_UNITS_ATTEMPTED_PROGRAM'];

				if($cum_course_unit3 >= $SAP_UNITS_ATTEMPTED_PROGRAM)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data9 = '<td align="center">'.$value.'</td>';
				$my_data9_1 = '<td align="center">'.$SAP_UNITS_ATTEMPTED_PROGRAM.'</td>';
				$my_data9_2 = '<td align="center">'.number_format_value_checker($cum_course_unit3,2).'</td>';
			}
			
			if($CUMULATIVE_GPA == '1'){
				$SAP_PERIOD_GPA = $res_sap_scale_setup_detail->fields['CUMULATIVE_GPA'];
				if($s_total_cum_gpa >= $SAP_PERIOD_GPA)
				{
					$value = ' Met';
				}else{
					$value = ' Not Met';
				}
				$my_data10 = '<td align="center">'.$value.'</td>';
				$my_data10_1 = '<td align="center">'.$SAP_PERIOD_GPA.'</td>';
				$my_data10_2 = '<td align="center">'.number_format_value_checker($s_total_cum_gpa,2).'</td>';
			}

			$sap_warning = '
							<tr>
								  <td>'.$SAP_WARNINGS.'</td>
								  '.$my_data1.$my_data2.$my_data3.$my_data4.$my_data5.$my_data6.$my_data7.$my_data8.$my_data9.$my_data10.'
							</tr>
							<tr>
								<td>Required</td>
								'.$my_data1_1.$my_data2_1.$my_data3_1.$my_data4_1.$my_data5_1.$my_data6_1.$my_data7_1.$my_data8_1.$my_data9_1.$my_data10_1.'
						    </tr>
							<tr>
								<td>Actual</td>
								'.$my_data1_2.$my_data2_2.$my_data3_2.$my_data4_2.$my_data5_2.$my_data6_2.$my_data7_2.$my_data8_2.$my_data9_2.$my_data10_2.'
							</tr>
							';
			// dvb
			// echo '<br>====';
			// echo $flag;
			// echo '====';
			// echo $PERIOD;							
			// echo '<br>';
			if($flag == '1' && $PERIOD == '1')
			{
				$final_sap_warning .= '
									<div style="page-break-before: always;"></div>
									<table>
										<tr>
											<td width="20%"><br><br></td>
										</tr>
										<tr>
											<td width="20%"></td>
											<td><b>Sap Evalution</b>: <b>Period '.$Period.'</b></td>
										</tr>
										<tr>
											<td><br></td>
										</tr>
										'.$txt_hour_com_sch.$txt_hour_com_prog.$txt_hour_sch_prog.$txt_fa_comp_att.$txt_fa_comp_prog.$txt_fa_att_prog.$txt_unit_com_att.$txt_unit_com_prog.$txt_unit_att_prog.$txt_cum_gpa.'
									</table>
									<table><tr><td><br><br></td></tr></table>
									<table border="0.5" cellspacing="0" cellpadding="3" style="margin-left: auto;margin-right: auto;" >
									'.$sap_warning_header.$sap_warning.'
									</table>
								';
			}
			else
			{
				if($final_flag == '1' && $flag = '1')
				{
					$final_sap_warning .= '
									<div style="page-break-before: always;"></div>
									<table>
										<tr>
											<td width="20%"><br><br></td>
										</tr>
										<tr>
											<td width="20%"></td>
											<td><b>Sap Evalution</b>: <b>Period '.$Period.'</b></td>
										</tr>
										<tr>
											<td><br></td>
										</tr>
										'.$txt_hour_com_sch.$txt_hour_com_prog.$txt_hour_sch_prog.$txt_fa_comp_att.$txt_fa_comp_prog.$txt_fa_att_prog.$txt_unit_com_att.$txt_unit_com_prog.$txt_unit_att_prog.$txt_cum_gpa.'
									</table>
									<table><tr><td><br><br></td></tr></table>
									<table border="0.5" cellspacing="0" cellpadding="3" style="margin-left: auto;margin-right: auto;" >
									'.$sap_warning_header.$sap_warning.'
									</table>
								';
				}
				else{
					$final_sap_warning .= '
									
									<table>
										<tr>
											<td width="20%"><br><br></td>
										</tr>
										<tr>
											<td width="20%"></td>
											<td><b>Sap Evalution</b>: <b>Period '.$Period.'</b></td>
										</tr>
										<tr>
											<td width="20%"></td>
											<td>Student is not meeting the minimum criteria for Evaluation Period '.$Period.'</td>
										</tr>
									</table>
								';
				}
			}
			
			$res_sap_scale_setup_detail->MoveNext();
			$Period++;
		}

		$txt .= $final_sap_warning;

		/* DIAM-2043 */
		$res_acc = $db->Execute("SELECT CONTENT FROM S_PDF_FOOTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 20 AND ACTIVE = 1 ");
		//$txt .= nl2br(str_replace(" ","&nbsp;",$res->fields['CONTENT']));
		$txt .= '<table><tr><td style="white-space: pre-wrap;text-align: justify;">' . nl2br($res_acc->fields['CONTENT']) . '</td></tr></table>';
		/* End DIAM-2043 */

		$txt .= '<div style="page-break-before: always;"></div>';				
		//echo $txt;exit;		 
		// End Sap Evalution

	return $txt;	
    
}

$uno = 'Official ';
if($_GET['uno'] == 1)
	$uno = 'Unofficial ';

$report_name = "";
if($_GET['id'] == '') {
	if($_SESSION['eid'] == '') {
		$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
		//$_GET['eid'] = $res->fields['PK_STUDENT_ENROLLMENT'];
	} else
		$_GET['eid'] = $_SESSION['eid'];
	
	$uno = 'Unofficial ';
	$report_name = "Academic Review By Term Report";
	
	student_transcript_pdf($_SESSION['PK_STUDENT_MASTER'], 0, $report_name);
} else {
	$report_name = "Student SAP";

	student_transcript_pdf($_GET['id'], 0, $report_name);
}
?>