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

if(check_access('REPORT_REGISTRAR') == 0 && $_SESSION['PK_USER_TYPE'] != 3){
	header("location:../index");
	exit;
}
	
class MYPDF extends TCPDF {

	public $campus;

    public function setCampus($var){
        $this->campus = $var;
    }

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
		
			if($_GET['id'] == '' || $_GET['uno'] == 1) { 
				$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6");
			} else {
				$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4");
			}

			$ImageW = 175; //WaterMark Size
			$ImageH = 175;

			//$pdf->setPage(1); //WaterMark Page    

			$myPageWidth  = $this->getPageWidth();
			$myPageHeight = $this->getPageHeight() - ($res_type->fields['FOOTER_LOC'] + 10);
			$myX = ( $myPageWidth / 2 ) - 90;  //WaterMark Positioning
			$myY = ( $myPageHeight / 2 ) - 80;

			$this->SetAlpha(0.30);
			// $this->Image('../backend_assets/images/unoffical_1.png', $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);

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
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, '' , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			$this->SetMargins('', 25, '');
			
			$this->SetFont('helvetica', 'I', 17);
			$this->SetY(8);
			$this->SetTextColor(000, 000, 000);
			
			if($_GET['uno'] == 1) {
				$this->SetX(130);
				$this->Cell(55, 8, "Unofficial Student Transcript", 0, false, 'L', 0, '', 0, false, 'M', 'L');
			} else {
				$this->SetX(150);
				$this->Cell(55, 8, "", 0, false, 'L', 0, '', 0, false, 'M', 'L');
			}
		} else {
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;
		}
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);

		$PK_CAMPUS = $this->campus;
		
		if($_GET['id'] == '' || $_GET['uno'] == 1) { 
			//$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6");
			$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS'  "); 
		} else {
			//$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4");
			$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS'  "); 
		}
		
		/*$txt = "";
		if($res_type->fields['BOLD'] == 1)
			$txt .= "B";
		if($res_type->fields['ITALIC'] == 1)
			$txt .= "I";*/
		
		$BASE = -28 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		//$CONTENT = nl2br($res_type->fields['CONTENT']);
        $CONTENT = "";
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

function custom_student_transcript_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf){
	global $db;
	
	// $PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);
    $PK_STUDENT_MASTER_ARR = $PK_STUDENT_MASTERS;
	
	if($_GET['id'] == '' || $_GET['uno'] != '') { 
		$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 6");
	} else {
		$res_type = $db->Execute("SELECT FOOTER_LOC FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 4");
	}
	$FOOTER_LOC = $res_type->fields['FOOTER_LOC'];
	$BASE 		= 30 + $FOOTER_LOC;
	
	$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 10, 7);
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


	$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	require_once("pdf_custom_header.php");

	$res_pdf_header = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME as NAME, IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS) as ADDRESS,
	IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS_1) as ADDRESS_1,
	IF(
	HIDE_ACCOUNT_ADDRESS_ON_REPORTS = '1',
	'',
	IF(CITY!='',CONCAT(CITY, ','),'')
		) AS CITY,
	IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',STATE_CODE) as STATE_CODE,
	IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ZIP) as ZIP,
	IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',PHONE) as PHONE, 
	IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',WEBSITE) as WEBSITE,HIDE_ACCOUNT_ADDRESS_ON_REPORTS FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

	$CONTENT = '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td style="width:35%" height="10" >'.$LOGO.'</td>
						<td style="width:65%;line-height:15px;" >
							<span style="" ><b style="font-size:20px">'.$res_pdf_header->fields['NAME'].'</b><br /><br />'.$res_pdf_header->fields['ADDRESS'].' '.$res_pdf_header->fields['ADDRESS_1'].'<br />'.$res_pdf_header->fields['CITY'].' '.$res_pdf_header->fields['STATE_CODE'].' '.$res_pdf_header->fields['ZIP'].'<br /><br />'.$res_pdf_header->fields['PHONE'].'<br /><br />'.$res_pdf_header->fields['WEBSITE'].'</span>
						</td>
					</tr>
				</table>';
	$ddates  = date('Y-m-d');
	$sDate   = date('m/d/Y',strtotime($ddates));

	$txt = '';

	$header = '<table border="0" cellspacing="0" cellpadding="0" width="100%" >
				<tr>
					<td width="50%">'.$CONTENT.'</td>
					<td width="50%" align="right"><i><b style="font-size:25px">Student Transcript</b></i><br /><br />Date Issued: '.$sDate.'</td>
				</tr>
				</table>
				';

	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
			
		
		$en_cond = "";
		if($_GET['eid'] != ''){
			$en_cond = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		}
		
		$res_stu = $db->Execute("select LAST_NAME, FIRST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, OLD_DSIS_STU_NO, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
		
		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		
		$CONTENT_1 = pdf_custom_header($PK_STUDENT_MASTER, '', 2);

		$pdf->STUD_NAME	 		= $res_stu->fields['NAME'];
		$pdf->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
		$pdf->startPageGroup();
		$pdf->AddPage();

		if($_GET['current_enrol'] == ''){
			$res_std_enroll_id = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY IS_ACTIVE_ENROLLMENT DESC LIMIT 1 "); 
			$_GET['current_enrol'] = $res_std_enroll_id->fields['PK_STUDENT_ENROLLMENT'];
			
		}

		$total_units_att = '';
		$total_units_compt = '';
		$total_gpa_value_total = '';
		$new_total_cum_gpa = '';

		$ADDRESS 		= (utf8_decode($res_add->fields['ADDRESS']));
		$CITY 			= (utf8_decode($res_add->fields['CITY']));

		$txt .= '<div style="page-break-before: always;"></div>';
		$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"  >
					<tr><td colspan="3" style="border-bottom:1px solid #c0c0c0;"></td></tr>
					<tr>
						<td width="10%">Record of:</td>
						<td width="60%" >'.$res_stu->fields['NAME'].'</td>
						<td width="30%" >Student ID: '.$res_stu->fields['STUDENT_ID'].'</td>
					</tr>
					<tr>
						<td ></td>
						<td style="line-height:20px;">'.$ADDRESS.'<br />'.$CITY.', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</td>
						<td >Date of Birth: '.$res_stu->fields['DOB'].'</td>
					</tr>
				</table>';

				$txt .= '<style>

						.column_data_prog {
							float: left;
							width: 50%;
							padding: 5px;
						}
						
						/* Clearfix (clear floats) */
						.row_data_prog::after {
							content: "";
							clear: both;
							display: table;
						}
						
						.mytable_prog {
							border-collapse: collapse;
							width: 100%;
						}
						
						.mytable_prog th, td {
							text-align: left;
							padding: 10px;
						}				
					</style>
					<div class="row_data_prog">';

					$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond ORDER By BEGIN_DATE ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");
					while (!$res_type->EOF) {
						$PK_STUDENT_ENROLLMENT 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
						$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
						
						$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

						$BOX_1 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));
						$BOX_4 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));
						$BOX_7 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));

						$BOX_2 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));
						$BOX_5 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));
						$BOX_8 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));

						$BOX_3 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));
						$BOX_6 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));
						$BOX_9 		= (utf8_decode(transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ")));
						
						$txt .= '<div class="column_data_prog">
									<table border="0" cellspacing="0" cellpadding="3" class="mytable_prog" >
										<tr>
											<td style="border-top:1px solid #c0c0c0;border-left:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;" width="34%" >
												'.$BOX_1.'
											</td>
											<td style="border-top:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;" width="35%" >
												'.$BOX_4.'
											</td>
											<td style="border-top:1px solid #c0c0c0;border-right:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;" width="31%" >
												'.$BOX_7.'
											</td>
										</tr>
										<tr>
											<td width="34%" style="border-left:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;" >
												'.$BOX_2.'
											</td>
											<td  width="35%" style="line-height:normal;padding-top:5px;padding-bottom:5px;" >
												'.$BOX_5.'
											</td>
											<td  width="31%" style="border-right:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;" >
												'.$BOX_8.'
											</td>
										</tr>
										<tr>
											<td width="34%" style="border-left:1px solid #c0c0c0;border-bottom:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;"  >
												'.$BOX_3.'
											</td>
											<td  width="35%" style="border-bottom:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;" >
												'.$BOX_6.'
											</td>
											<td  width="31%" style="border-right:1px solid #c0c0c0;border-bottom:1px solid #c0c0c0;line-height:normal;padding-top:5px;padding-bottom:5px;" >
												'.$BOX_9.'
											</td>
										</tr>
									</table>
								</div>';
						$res_type->MoveNext();
					}
		    $txt .= '</div>';

				
		$txt .= ' <style>
					* {
						box-sizing: border-box;
					}
					
					.row_data {
						margin-left:-5px;
						margin-right:-5px;
					}
						
					.column_data {
						float: left;
						width: 50%;
						padding: 5px;
					}
					
					/* Clearfix (clear floats) */
					.row_data::after {
						content: "";
						clear: both;
						display: table;
					}
					
					.mytable {
						border-collapse: collapse;
						width: 100%;
					}
					
					.mytable th, td {
						text-align: left;
						padding: 16px;
						line-height: 4px;
					}				
                </style>
				<div style="padding-top:15px;padding-bottom:10px;font-size:25px;font-weight: bold;">Institution Credit: </div>
				<div class="row_data">
                    ';
					
					$total_cum_rec		= 0;
					$c_in_num_grade_tot = 0; 
					$c_in_att_tot 		= 0;
					$c_in_comp_tot 		= 0;
					$c_in_cu_gnu 		= 0;
					$c_in_gpa_tot 		= 0;
					$c_gpa_value_total  = 0;

					$summation_of_gpa      = 0;
					$summation_of_weight   = 0;
					
					$res_term = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE, S_TERM_MASTER.TERM_DESCRIPTION FROM S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, M_COURSE_OFFERING_STUDENT_STATUS WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER By BEGIN_DATE_1 ASC");
					while (!$res_term->EOF) {
						$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];

                        $BEGIN_DATE 	= date('d F Y', strtotime($res_term->fields['BEGIN_DATE']));
                        $END_DATE 	    = date('d F Y', strtotime($res_term->fields['END_DATE']));
                        $Final_Date     = $BEGIN_DATE .' - '. $END_DATE;

						$txt .= '
                         
						<div class="column_data">
                            <table border="0" cellspacing="0" cellpadding="3" class="mytable" >
								<thead>
                                <tr style="font-size:13px">
                                    <td width="55%" colspan="2" style="border-left:2px solid #c0c0c0;border-top:2px solid #c0c0c0;border-bottom:2px solid #c0c0c0;" ><b>'.$Final_Date.'</b></td>
                                    <td width="15%" style="border-top:2px solid #c0c0c0;border-bottom:2px solid #c0c0c0;" ><b>Grade</b></td>
                                    <td width="15%" style="border-top:2px solid #c0c0c0;border-bottom:2px solid #c0c0c0;" ><b>Credit</b></td>
                                    <td width="15%" style="border-top:2px solid #c0c0c0;border-bottom:2px solid #c0c0c0;border-right:2px solid #c0c0c0;" ><b>QP</b></td>
                                </tr>
								</thead>
								<tbody>';
						
							$total_rec				= 0;
							$c_in_num_grade_sub_tot = 0;
							$c_in_att_sub_tot 		= 0;
							$c_in_comp_sub_tot 		= 0;
							$c_in_cu_sub_gnu 		= 0;
							$c_in_gpa_sub_tot 		= 0;
							
							$Sub_Denominator = 0;
							$Sub_Numerator 	 = 0;
							$Sub_Numerator1  = 0;

							$gpa_value_total=0;
							$gpa_value_sub_total=0;
							$gpa_weight_total=0;
							$gpa_sub_weight_total=0;
							
							$sql_course="SELECT TRANSCRIPT_CODE, 
												COURSE_DESCRIPTION, 
												S_STUDENT_COURSE.PK_COURSE_OFFERING, 
												FINAL_GRADE, 
												GRADE, 
												NUMERIC_GRADE, 
												NUMBER_GRADE, 
												CALCULATE_GPA, 
												UNITS_ATTEMPTED, 
												WEIGHTED_GRADE_CALC, 
												UNITS_COMPLETED, 
												UNITS_IN_PROGRESS, 
												COURSE_UNITS, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
												S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
												)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
												CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
												S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
												) ELSE 0 END AS GPA_WEIGHT 
											FROM 
												S_STUDENT_COURSE 
												LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
												LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE, 
												M_COURSE_OFFERING_STUDENT_STATUS 
											WHERE 
												PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
												AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' 
												AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS 
												AND SHOW_ON_TRANSCRIPT = 1 $en_cond 
											ORDER BY 
												TRANSCRIPT_CODE ASC";
							$res_course = $db->Execute($sql_course);	
							while (!$res_course->EOF) { 
								
								$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
								$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
								$COMPLETED_UNITS	 = 0;
								$ATTEMPTED_UNITS	 = 0;
								
								if($res_course->fields['UNITS_ATTEMPTED'] == 1)
									$ATTEMPTED_UNITS = $res_course->fields['COURSE_UNITS'];
								
								$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
								$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
								
								if($res_course->fields['UNITS_COMPLETED'] == 1) {
									$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
									$c_in_comp_tot  	+= $COMPLETED_UNITS;
									$c_in_comp_sub_tot  += $COMPLETED_UNITS;
								}
								
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

									$GPA_VALULE 			= $res_course->fields['GPA_VALUE']; 
									$gpa_value_total 		+= $GPA_VALULE; 
									$GPA_WEIGHT 			= $res_course->fields['GPA_WEIGHT']; 
									$gpa_weight_total 		+= $GPA_WEIGHT; 

									$c_gpa_value_total		+= $GPA_VALULE;

									$summation_of_gpa    += $GPA_VALULE;
									$summation_of_weight += $GPA_WEIGHT;
								}
															
								$txt .= '<tr>
											<td width="55%" colspan="2" >'.$res_course->fields['TRANSCRIPT_CODE'].'</td>';
											
											if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
											{
												$txt .= '<td width="15%" >'.$res_course->fields['GRADE'].'</td>';
											}
											if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3)
											{
												$txt .= '<td width="15%" >'.$res_course->fields['NUMERIC_GRADE'].'</td>';
											}
											$txt .= '<td width="15%" >'.number_format_value_checker($GPA_VALULE,2).'</td>
											
										</tr>';
								
								$res_course->MoveNext();
							} 
													

							$gpa_weighted=0;
							//$total_gpa_weighted=0;
							if($gpa_value_total>0)
							{
								$gpa_weighted=$gpa_value_total/$gpa_weight_total;

								$total_tc_gpa_weighted +=$gpa_weighted;
							}
							//echo $total_tc_gpa_weighted .' | '. $total_cum_rec."<br>";		
							$txt .= '
								<tr>
									<td width="40%" align="right" ></td>
									<td width="15%"  align="right" ><i><b style="font-size:15px">Attempted</b></i></td>
									<td width="15%"  align="right" ><i><b style="font-size:15px">Completed</b></i></td>
									<td width="15%"  align="right" ><i><b style="font-size:15px">Points</b></i></td>
									<td width="15%"  align="right" ><i><b style="font-size:15px">GPA</b></i></td>
								</tr>
								<tr>
									<td width="40%" align="right" ><i><b style="font-size:15px">Semester Totals</b></i></td>
									<td width="15%"  align="right" style="font-size:15px" ><i>'.number_format_value_checker($c_in_att_sub_tot,2).'</i></td>
									<td width="15%"  align="right" style="font-size:15px" ><i>'.number_format_value_checker($c_in_comp_sub_tot,2).'</i></td>
									<td width="15%"  align="right" style="font-size:15px" ><i>'.number_format_value_checker($gpa_value_total,2).'</i></td>
									<td width="15%"  align="right" style="font-size:15px" ><i>'.number_format_value_checker($gpa_weighted,2).'</i></td>
								</tr>
								<tr>
									<td width="40%" align="right" ><i><b style="font-size:15px">Cumulative Total: </b></i></td>
									<td width="15%" align="right" style="font-size:15px" ><i>'.number_format_value_checker($c_in_att_tot,2).'</i></td>
									<td width="15%" align="right" style="font-size:15px" ><i>'.number_format_value_checker($c_in_comp_tot,2).'</i></td>
									<td width="15%" align="right" style="font-size:15px" ><i>'.number_format_value_checker($c_gpa_value_total,2).'</i></td>
									<td width="15%" align="right" style="font-size:15px" ><i>'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</i></td>
								</tr>
								</tbody>
							</table>
						</div>
                       ';

						$total_units_att 		= $c_in_att_tot;
						$total_units_compt 		= $c_in_comp_tot;
						$total_gpa_value_total 	= $c_gpa_value_total;
						$new_total_cum_gpa 		= ($summation_of_gpa/$summation_of_weight);
									
						$res_term->MoveNext();

                    }
				$txt .= '</div>';

					if($_GET['exclude_tc'] != 1) {
						
						$sql = "SELECT S_COURSE.TRANSCRIPT_CODE, 
									CREDIT_TRANSFER_STATUS, 
									S_COURSE.COURSE_DESCRIPTION, 
									S_STUDENT_CREDIT_TRANSFER.UNITS, 
									S_COURSE.FA_UNITS, 
									S_STUDENT_CREDIT_TRANSFER.TC_NUMERIC_GRADE, 
									S_STUDENT_CREDIT_TRANSFER.GRADE, 
									PK_STUDENT_ENROLLMENT, 
									S_STUDENT_CREDIT_TRANSFER.PK_GRADE, 
									S_GRADE.NUMBER_GRADE, 
									S_GRADE.CALCULATE_GPA, 
									S_GRADE.UNITS_ATTEMPTED, 
									S_GRADE.UNITS_COMPLETED, 
									S_GRADE.UNITS_IN_PROGRESS, 
									CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
									S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
									)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
									CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (
									S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC
									) ELSE 0 END AS GPA_WEIGHT 
								FROM 
									S_STUDENT_CREDIT_TRANSFER 
									LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
									LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
									LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
								WHERE 
									S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
									AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
									AND SHOW_ON_TRANSCRIPT = 1 $en_cond 
								ORDER BY 
									S_COURSE.TRANSCRIPT_CODE ASC ";

						$res_tc = $db->Execute($sql); 

						
						$total_rec				= 0;
						$c_in_num_grade_sub_tot = 0;
						$c_in_att_sub_tot_trans  = 0;
						$c_in_comp_sub_tot_trans = 0;
						$c_in_cu_sub_gnu 		= 0;
						$c_in_gpa_sub_tot 		= 0;

						$tc_gpa_value_total=0;
						$tc_gpa_weight_total=0;
						$total_tc_gpa_weighted =0;

						while (!$res_tc->EOF) {
							
							$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT']; 
							$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
							
							$COMPLETED_UNITS	 = 0;
							$ATTEMPTED_UNITS	 = 0;

							$res_grade_data = $db->Execute("SELECT UNITS_ATTEMPTED,UNITS_COMPLETED,CALCULATE_GPA,NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' "); 
							if($res_grade_data->fields['UNITS_ATTEMPTED'] == 1)
								$ATTEMPTED_UNITS = $res_tc->fields['UNITS'];
							
							$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
							$c_in_att_sub_tot_trans 	+= $ATTEMPTED_UNITS; 
							if($res_grade_data->fields['UNITS_COMPLETED'] == 1) {
								$COMPLETED_UNITS 	 = $res_tc->fields['UNITS'];
								$c_in_comp_tot  	+= $COMPLETED_UNITS;
								$c_in_comp_sub_tot_trans  += $COMPLETED_UNITS;
							}
						
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
								
								$total_rec++;
								$total_cum_rec++;

								$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
								$tc_gpa_value_total 		+= $TC_GPA_VALULE; 
								$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
								$tc_gpa_weight_total 		+= $TC_GPA_WEIGHT; 

								$c_gpa_value_total		+= $TC_GPA_VALULE;

								$summation_of_gpa     += $TC_GPA_VALULE;
								$summation_of_weight  += $TC_GPA_WEIGHT;
							}
										
							$res_tc->MoveNext();
						} 
						
					    $tc_gpa_weighted=0;
						if($tc_gpa_value_total>0)
						{
							$tc_gpa_weighted=$tc_gpa_value_total/$tc_gpa_weight_total;
							$total_tc_gpa_weighted +=$tc_gpa_weighted;
						}

						if($res_tc->RecordCount() > 0) {
							$t_in_att_sub_tot_trans = number_format_value_checker($c_in_att_sub_tot_trans,2);
							$t_in_comp_sub_tot_trans = number_format_value_checker($c_in_comp_sub_tot_trans,2);
							$gpa_value_trans = number_format_value_checker($tc_gpa_value_total,2);

							$new_total_cum_gpa_trans = number_format_value_checker(($tc_gpa_value_total/$tc_gpa_weight_total),2);

						}
						
					}
			
					$txt .= '
						<table border="0" cellspacing="0" cellpadding="0" width="50%" >
							<tr>
								<td colspan="5" style="border-top:2px solid #c0c0c0;border-bottom:2px solid #c0c0c0;border-left:2px solid #c0c0c0;border-right:2px solid #c0c0c0;" ><b>Transcript Totals</b></td>
							</tr>
							<tr>
								<td width="10%" ></td>
								<td width="10%" ><b>Attempted</b></td>
								<td width="10%" ><b>Completed</b></td>
								<td width="10%" ><b>Points</b></td>
								<td width="10%" ><b>GPA</b></td>
							</tr>
							<tr>
								<td width="10%" ><b>Institution:</b></td>
								<td width="10%" >'.number_format_value_checker($total_units_att,2).'</td>
								<td width="10%" >'.number_format_value_checker($total_units_compt,2).'</td>
								<td width="10%" >'.number_format_value_checker($total_gpa_value_total,2).'</td>
								<td width="10%" >'.number_format_value_checker($new_total_cum_gpa,2).'</td>
							</tr>
							<tr>
								<td width="10%" ><b>Transfer:</b></td>
								<td width="10%" >'.number_format_value_checker($t_in_att_sub_tot_trans,2).'</td>
								<td width="10%" >'.number_format_value_checker($t_in_comp_sub_tot_trans,2).'</td>
								<td width="10%" >'.number_format_value_checker($gpa_value_trans,2).'</td>
								<td width="10%" >'.number_format_value_checker($new_total_cum_gpa_trans,2).'</td>
							</tr>
							<tr>
								<td width="10%" ><b>Overall:</b></td>
								<td width="10%" >'.number_format_value_checker($c_in_att_tot,2).'</td>
								<td width="10%" >'.number_format_value_checker($c_in_comp_tot,2).'</td>
								<td width="10%" >'.number_format_value_checker($c_gpa_value_total,2).'</td>
								<td width="10%" >'.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2).'</td>
							</tr>
						</table>
						<br><br>
						<div>
						Official transcripts are printed on mottled grey paper and bear the Registrar signature<br>
						embossed with the college seal
						</div>';
					 
            

		// $pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	}


	$file_name 	= 'Custom_transcript_'.uniqid().'.pdf';

	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$date_footer = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());
	
	$footer = '<table width="100%" >
					<tr>
						<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date_footer.'</i></td>
						<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
						<td></td>							
					</tr>
				</table>';


	$header_cont= '<!DOCTYPE HTML>
	<html>
	<head>
	<style>
	div{ padding-bottom:20px !important; }	
	</style>
	</head>
	<body>
	<div> 
	<table border="0" cellspacing="0" cellpadding="0" width="100%" >
		<tr>
			<td width="50%">'.$CONTENT_1.'</td>
			<td width="50%" align="right"><i><b style="font-size:25px">Student Transcript</b></i><br /><br />Date Issued: '.$sDate.'</td>
		</tr>
	</table>
	</div>
	</body>
	</html>';

	$html_body_cont = '<!DOCTYPE HTML>
	<html>
	<head> <style>
	table{  margin-top: 2px; }
	table tr{  padding-top: 1px !important; }
	</style>
	</head>
	<body>'.$txt.'</body></html>';

	$footer_cont= '<!DOCTYPE HTML><html><head><style>
	tbody td{ font-size:14px !important; }
	</style></head><body>'.$footer.'</body></html>';

	$header_path = create_html_file('header.html', $header_cont, "invoice");
	$content_path = create_html_file('content.html', $html_body_cont, "invoice");
	$footer_path = create_html_file('footer.html', $footer_cont, "invoice");

	$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 296 --page-height 210 --margin-top 40mm --margin-left 7mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html ' . $header_path . ' --footer-html  ' . $footer_path . ' ' . $content_path . ' ../school/temp/invoice/' . $file_name . ' 2>&1';
	global $http_path;
	$pdfdata = array('filepath' => 'temp/invoice/' . $file_name, 'exec' => $exec, 'filename' => $file_name, 'filefullpath' => $http_path . 'school/temp/invoice/' . $file_name);
	exec($pdfdata['exec'], $output, $retval);
	header('Content-type: application/json; charset=UTF-8');
	$data_res = [];
	$data_res['path'] = 'temp/invoice/' . $file_name;
	$data_res['filename'] = $file_name;
	echo json_encode($data_res);
	exit;

	
}

$Get_Stud_Master = $_POST['PK_STUDENT_MASTER'];
$student_array   = array();

$s=0;
foreach ($Get_Stud_Master as $key => $value) {
	$student_array[$value][]= $_POST['PK_STUDENT_ENROLLMENT'][$s];
	$s++;
}

if(!empty($student_array)) {
	echo custom_student_transcript_pdf($_POST['PK_STUDENT_MASTER'], 0);
}

?>