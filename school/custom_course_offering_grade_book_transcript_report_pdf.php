<?php 
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

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
			//$this->Cell(55, 8, "Student Transcript", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		} else 
			$_SESSION['temp_id'] = $this->PK_STUDENT_ENROLLMENT;
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(183);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(18);
		$this->SetFont('helvetica', 'I', 8);
		
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

		$this->SetY(-15);
		$this->SetX(100);
		$this->SetFont('helvetica', 'I', 8);

		if($_POST['show_signature_line']=='yes'){
		$this->Cell(30, 10, "Official Signature: _______________________", 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$image_file = "../assets/images/signature/focus/Joe - High Res.png";
		$this->Image($image_file,110, 280, 30, 15, '', 'T', 'M');
		}


    }
}


require_once("pdf_custom_sap_header.php"); 
function co_grade_book_progress_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf, $report_name = null){
	global $db,$DB_HOST,$DB_USER,$DB_PASS,$DB_DATABASE;


	
	$PK_STUDENT_MASTER_ARR = $PK_STUDENT_MASTERS;

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

	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 15, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, 15);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 8, '', true);


	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$LOGO = '';
	if($res->fields['PDF_LOGO'] != '')
		$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';
	
	$cond = "";
	if($_GET['type'] == 1) {
		$label = "All Enrollments";
		$cond  = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN (".$_GET['eid'].") ";
		$cond1 = " AND PK_STUDENT_ENROLLMENT IN (".$_GET['eid'].") ";
		$cond2 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (".$_GET['eid'].") ";
		
	} else if($_GET['type'] == 2) {
		$res_en11 = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_GET[id]' AND IS_ACTIVE_ENROLLMENT = 1");
		
		$label = "Current Enrollment";
		$cond  = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '".$res_en11->fields['PK_STUDENT_ENROLLMENT']."' ";
		$cond1 = " AND PK_STUDENT_ENROLLMENT = '".$res_en11->fields['PK_STUDENT_ENROLLMENT']."' ";
		$cond2 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '".$res_en11->fields['PK_STUDENT_ENROLLMENT']."' ";
	} else if($_GET['type'] == 3) {
		$label = "By Term";
		$cond  = " AND S_COURSE_OFFERING.PK_TERM_MASTER = '$_GET[term]' ";
		$cond1 = " AND PK_TERM_MASTER = '$_GET[term]' ";
		$cond2 = " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER = '$_GET[term]' ";
	}

	$def_grade = 0;
	$res_def_grade = $db->Execute("SELECT PK_GRADE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_DEFAULT = 1");
	if($res_def_grade->RecordCount() > 0)
		$def_grade = $res_def_grade->fields['PK_GRADE'];
		
	$show_cond = "";
	if($_GET['show'] == 2)
		$show_cond = " AND (FINAL_GRADE > 0 AND FINAL_GRADE != '$def_grade' ) ";
	else if($_GET['show'] == 3) {
		if($def_grade == 0)
			$show_cond = " AND FINAL_GRADE = 0 ";
		else
			$show_cond = " AND (FINAL_GRADE = 0 OR FINAL_GRADE = '$def_grade' ) ";
	}


	/* Ticket #1145 */

	/* Ticket #1170 */
	if($_GET['report_type'] == '')
		$_GET['report_type'] = 1;

	if($_GET['report_type'] == 1) {
		$border_1 = "border-top:1px solid #000;";
		$border_2 = "border-bottom:1px solid #000;";
	} else {
		$border_1 = "";
	}
	/* Ticket #1170 */
	$m=0;

	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER=>$enrollment) {

		


		$PK_STUDENT_ENROLLMENT=implode(',',$enrollment);

		
		$en_cond = "";
		if($_GET['eid'] != ''){
			$en_cond = " AND PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
		}

		$res_stu = $db->Execute("select FIRST_NAME, LAST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
		
		$res_add = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		
		$CONTENT = pdf_custom_header($PK_STUDENT_MASTER, '', 2); //Ticket # 1588
		
		$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STUDENT_STATUS, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE,SESSION, BEGIN_DATE as BEGIN_DATE_1, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS FROM S_STUDENT_ENROLLMENT LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond $cond2 ORDER By BEGIN_DATE_1 ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");
		
		$pdf->STUD_NAME 			= $res_stu->fields['NAME'];
		$pdf->PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
		$pdf->startPageGroup();
		$pdf->AddPage();
		//$pdf->AliasNbPageGroups('[pagetotal]');
		//$txt = "";
		$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';

		//Ticket # 936
		$table_td = '';
		if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])){ 

			if(empty($res_add->fields['EMAIL']))
			$res_add->fields['EMAIL'] = $res_add->fields['EMAIL_OTHER'];

			if(empty($res_add->fields['CELL_PHONE']))
			$res_add->fields['CELL_PHONE'] = $res_add->fields['OTHER_PHONE'];
			
			$table_td .= '<td style="width:55%" >
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
			

		}
		//Ticket # 936
		/* Ticket # 1588 */
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td width="50%">'.$CONTENT.'</td>
						<td width="50%">
							<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								<tr>
									<td style="width:100%" >
										<b style="font-size:50px" >'.$res_stu->fields['NAME'].'</b><br />
									</td>
								</tr>
								<tr>
									<td style="width:45%" >
										<span style="line-height:5px" >'.$res_add->fields['ADDRESS'].'<br />'.$res_add->fields['CITY'].', '.$res_add->fields['STATE_CODE'].' '.$res_add->fields['ZIP'].'<br />'.$res_add->fields['COUNTRY'].'</span>
									</td>'.$table_td.'
								</tr>';
								/* Ticket # 1588 */
								
								while (!$res_type->EOF) {
									$PK_CAMPUS_PROGRAM 		= $res_type->fields['PK_CAMPUS_PROGRAM'];
									$PK_STUDENT_ENROLLMENT2 = $res_type->fields['PK_STUDENT_ENROLLMENT'];
									$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
									if(in_array($res_type->fields['PK_STUDENT_ENROLLMENT'],$_POST['PK_STUDENT_ENROLLMENT'])){
									$custom_program="";
									$custom_program_sap="";
									$custom_program_colour = '#c0c0c0';	
									if($res_report_header->fields['BOX_1']=="PROGRAM_CODE"){
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
									}
									$res_type->MoveNext();
								}
					$txt .= '</table>
						</td>
					</tr>
				</table>';
		
		$txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>
				<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td width="100%" align="center" ><b><i style="font-size:50px">Transcript: '.$report_option.' </i></b><br /></td>
					</tr>
				</table>
				<br /><br />';
				$header_font = "33px";

				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
								   <thead>
									<tr>
										<td width="10%" ></td>
										<td width="15%"  style="font-size:'.$header_font.'"  ><b><u>Type</u></b></td>
										<td width="20%"  style="font-size:'.$header_font.'"  ><b><u>Description</u></b></td>				
										<td width="15%" align="right"  style="font-size:'.$header_font.'"  ><b><u>Points Earned</u></b></td>
										<td width="15%" align="right"  style="font-size:'.$header_font.'"  ><b><u>Total Points</u></b></td>
										<td width="15%" align="right"  style="font-size:'.$header_font.'"  ><b><u>Percentage Earned</u></b></td>
										<td width="10%"></td>
									</tr>
									</thead>';
			
				$db->next_result();
				$terms=[];
				$data=[];
				$ndata=[];
				#Santizating 
				$T_START_DATE = $_POST['MIDPOINT_START_DATE']? "'".date('Y-m-d',strtotime($_POST['MIDPOINT_START_DATE']))."'" :'null';
				$T_END_DATE   = $_POST['MIDPOINT_END_DATE']? "'".date('Y-m-d',strtotime($_POST['MIDPOINT_END_DATE']))."'" :'null';
				$res_stu_point = $db->Execute("call StudentTranscriptGradbook('".$PK_STUDENT_ENROLLMENT."',$_SESSION[PK_ACCOUNT],$T_START_DATE,$T_END_DATE,".$PK_STUDENT_MASTER.")");
				$data_empty=[];
				while (!$res_stu_point->EOF) {
					$data_empty=$res_stu_point->fields;					
					$ndata[]=$data_empty;
					$terms[$res_stu_point->fields['BEGIN_DATE']]=array('term_date'=>$res_stu_point->fields['BEGIN_DATE'],'course_code'=>$res_stu_point->fields['COURSE_CODE'].' - '.$res_stu_point->fields['COURSE_DESCRIPTION'] );
					$res_stu_point->MoveNext();
				} 
					foreach ($ndata as $key => $value1) {
						$data[$value1['BEGIN_DATE']][$value1['GRADE_BOOK_TYPE']][]=$value1;
					}
				$total_rec_point=0;
				$total_earned_points=0;
				$total_weight_points=0;
				if(count($terms) > 0) {
					foreach ($terms as $key => $value) 
					{
						$txt .= '<tr>
						            <td width="100%" ><b style="font-size:40px" ><i>Term: '.date('m/d/Y',strtotime($value['term_date'])).'</i></b></td>
				 		         </tr>';
						$txt .= '<tr>
							 		<td width="100%" ><b style="font-size:40px" ><i>'.$value['course_code'].'</i></b></td>
			 					</tr>';
								
							$STUD_POINTS 	= 0;
							$CO_POINTS 		= 0;
							$CO_WEIGHT 		= 0;
							$TOT_STUD_WEIGHTED_POINTS 	= 0;
							$TOT_CO_WEIGHTED_POINTS 	= 0;
							
							foreach ($data[$value['term_date']] as $k => $v) 
							{
									
								$SUB_TOT_STUD_WEIGHTED_POINTS 	= 0;
								$SUB_TOT_CO_WEIGHTED_POINTS 	= 0;
									foreach ($v as $n) {
										$GRADE_BOOK_TYPE = $n['GRADE_BOOK_TYPE'];
										$STUD_POINTS 	+= $n['StudentPoints'];
										$CO_POINTS 		+= $n['CoursePoints'];
										$CO_WEIGHT 		+= $n['WEIGHT'];
		
										$STUD_WEIGHTED_POINTS 	= $n['StudentPoints'] * $n['WEIGHT']*$n['GradeBookItemComplete'];
		
										$CO_WEIGHTED_POINTS		= $n['CoursePoints'] * $n['WEIGHT']*$n['GradeBookItemComplete'];
		
										if($n['StudentPoints']!=""){	
										$TOT_STUD_WEIGHTED_POINTS 	+= $STUD_WEIGHTED_POINTS;
										$TOT_CO_WEIGHTED_POINTS 	+= $CO_WEIGHTED_POINTS;
											
										$SUB_TOT_STUD_WEIGHTED_POINTS 	+= $STUD_WEIGHTED_POINTS;
										$SUB_TOT_CO_WEIGHTED_POINTS 	+= $CO_WEIGHTED_POINTS;
										}
										$total_rec_point++;										
										$txt .= '<tr>
													<td width="10%" ></td>
													<td width="15%" >'.$GRADE_BOOK_TYPE.'</td>
													<td width="20%" >'.$n['DESCRIPTION'].'</td>
													<td width="15%" align="right" >'.($n['StudentPoints']!=""?number_format_value_checker($STUD_WEIGHTED_POINTS,2):"").'</td>
													<td width="15%" align="right" >'.number_format_value_checker($CO_WEIGHTED_POINTS,2).'</td>
													<td width="15%" align="right" >'.number_format_value_checker(($STUD_WEIGHTED_POINTS / $CO_WEIGHTED_POINTS * 100),2).' %</td>
													<td width="10%" ></td>
												</tr>';
										
									}
									
								
									$txt .= '<tr>
														<td width="10%" ></td>
														<td width="15%" style="'.$border_2.'"  >'.$GRADE_BOOK_TYPE1.'</td>
														<td width="20%" style="'.$border_2.'" ><i>Weighted Total:</i></td>
														<td width="15%" style="'.$border_2.'"  align="right" >'.number_format_value_checker(($SUB_TOT_STUD_WEIGHTED_POINTS),2).'</td>
														<td width="15%"  style="'.$border_2.'" align="right" >'.number_format_value_checker(($SUB_TOT_CO_WEIGHTED_POINTS),2).'</td>
														<td width="15%"  style="'.$border_2.'" align="right" >'.number_format_value_checker((($SUB_TOT_STUD_WEIGHTED_POINTS) / ($SUB_TOT_CO_WEIGHTED_POINTS) * 100),2).' %</td>
														<td width="10%"></td>
													</tr>
													';

							}

							if($TOT_CO_WEIGHTED_POINTS > 0)
								$per1 = ($TOT_STUD_WEIGHTED_POINTS / $TOT_CO_WEIGHTED_POINTS * 100);
							else
								$per1 = 0;
							
							/* Ticket # 1219   */
							$txt .= '<tr>
										<td width="10%" ></td>
										<td width="15%"  ></td>';

									$txt .= '<td width="20%"  ><i>Weighted Current Total:</i></td>
										<td width="15%"  align="right" >'.number_format_value_checker($TOT_STUD_WEIGHTED_POINTS,2).'</td>
										<td width="15%"  align="right" >'.number_format_value_checker($TOT_CO_WEIGHTED_POINTS,2).'</td>
										<td width="15%"  align="right" >'.number_format_value_checker($per1,2).' %</td>
										<td width="10%"></td>
									</tr>';
							/* Ticket # 1219   */

							$total_earned_points +=$TOT_STUD_WEIGHTED_POINTS;
							$total_weight_points +=$TOT_CO_WEIGHTED_POINTS;

						}

						
					}

					if($total_weight_points > 0)
								$percombined= ($total_earned_points / $total_weight_points * 100);
							else
								$percombined = 0;
				
				$txt .= '
					<tr>
					  <td width="100%" ><br /></td>
					</tr>
					<tr>
					<td width="10%" ></td>
					<td width="5%" ></td>
				    <td width="30%"></td>
					<td width="15%"  align="right" ><b><u>Points Earned</u></b></td>
					<td width="15%"  align="right" ><b><u>Total Points</u></b></td>
					<td width="15%" align="right" ><b><u>Percentage Earned</u></b></td>
					<td width="10%"> </td>
				</tr> 
					';

				$txt .= '
					<tr>
					<td width="10%" ></td>
					<td width="5%" ></td>
				    <td width="30%"><b>Total Combined Grade Book Points:</b></td>
					<td width="15%"  align="right" >'.number_format_value_checker($total_earned_points,2).'</td>
					<td width="15%"  align="right" >'.number_format_value_checker($total_weight_points,2).'</td>
					<td width="15%" align="right" >'.number_format_value_checker($percombined,2).' %</td>
					<td width="10%"> </td>
				</tr> 
				<tr>
				<td width="100%" ><br /></td>
			  </tr>';

				$txt .= '</table> <br><br><br><br>
				
				
				';
				$db->next_result();
				$res_course = $db->Execute("call CourseTotals($_SESSION[PK_ACCOUNT],".$PK_STUDENT_MASTER.",null,null,'".$PK_STUDENT_ENROLLMENT."','')");

				//$txt="";
				$txt .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" >
						<tr nobr="true">
							<td width="100%" >';
							if($_POST['show_attenance_summary']=='yes'){
								$txt .= '<table border="0" cellspacing="0" cellpadding="2" width="100%" >';
								$txt .= '<tr>';
								
								   $border_left = "";
									$border_3 		= "border-top:1px solid #000;";

									$txt .= '<td width="8%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Term</b></td>
									<td width="13%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" ><br /><br /><br /><b>Course</b></td>';
									
									$txt .= '<td width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;'.$border_left.'" align="right" ><br /><br /><b>Hours Attended</b></td>
										<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Missed</b></td>
										<td width="8%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Hours Scheduled</b></td>
										<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Absent Count</b></td>
										<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Absent Hours Missed</b></td>
										<td width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Tardy Count</b></td>
										<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Tardy Hours Missed</b></td>
										<td width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Left Early Count</b></td>
										<td width="7%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Left Early Hours Missed</b></td>
										<td width="9%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><br /><br /><b>Attendance Percentage</b></td>										
										<td width="6%" style="border-bottom:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;" align="right" ><b>Final Course Grade</b></td>
									</tr>';
									$total_attended=0;
									$total_AbsentHoursMissed=0;
									$total_ScheuldedHours=0;
									$missed_total=0;
									$total_AbsentCount=0;
									$total_TardyCount=0;
									$total_TardyHoursMissed=0;
									$total_LeftEarlyCount=0;
									$total_LeftEarlyHoursMissed=0;
									$total_attended_percentage=0;
									$total_GPAValue=0;
									$total_GPAWeight=0;
									$GPA=0;
									$total_rec=0;

									while (!$res_course->EOF) {

									$total_attended +=$res_course->fields['AttendedHours'];

									// dvb 27 12 2024
									if(($res_course->fields['GRADE'] != "W" && $_SESSION['PK_ACCOUNT'] == 99) || $_SESSION['PK_ACCOUNT'] != 99){
										$total_ScheuldedHours +=$res_course->fields['ScheduledHours'];
									}
									// dvb end
									
									$total_AbsentHoursMissed +=$res_course->fields['AbsentHoursMissed'];
									$total_AbsentCount +=$res_course->fields['AbsentCount'];
									$total_TardyCount +=$res_course->fields['TardyCount'];
									$total_TardyHoursMissed +=$res_course->fields['TardyHoursMissed'];
									$total_LeftEarlyCount +=$res_course->fields['LeftEarlyCount'];
									$total_LeftEarlyHoursMissed +=$res_course->fields['LeftEarlyHoursMissed'];
									$total_attended_percentage +=($res_course->fields['AttendancePercentage']*100);
									$total_GPAValue +=$res_course->fields['GPAValue'];
									$total_GPAWeight +=$res_course->fields['GPAWeight'];

								    //echo "scheduled=".$res_course->fields['ScheduledHours'].' Attendence hours '.$res_course->fields['AttendedHours'];
									//echo "<hr>";
									if($res_course->fields['AttendancePercentage']>0){
										$total_rec++;
									}

									$missed= ($res_course->fields['ScheduledHours']-$res_course->fields['AttendedHours']);
									$missed_total +=$missed;
									
									$txt .=	'<tr>
									<td width="8%" style="border-left:1px solid #000;" >'.date('m/d/Y',strtotime($res_course->fields['BEGIN_DATE'])).'</td>
									<td width="13%" style="border-right:1px solid #000;"  >'.$res_course->fields['COURSE_CODE'].' ('. substr($res_course->fields['SESSION'],0,1).' - '. $res_course->fields['SESSION_NO'].')</td>
									<td width="7%" align="right" >'.number_format_value_checker($res_course->fields['AttendedHours'],2).'</td>
									<td width="6%" align="right" >'.number_format_value_checker($missed,2).'</td>
									<td width="8%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_course->fields['ScheduledHours'],2).'</td>
									<td width="6%" align="right" >'.$res_course->fields['AbsentCount'].'</td>
									<td width="6%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_course->fields['AbsentHoursMissed'],2).'</td>
									<td width="6%" align="right" >'.$res_course->fields['TardyCount'].'</td>
									<td width="6%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_course->fields['TardyHoursMissed'],2).'</td>
									<td width="8%" align="right" >'.$res_course->fields['LeftEarlyCount'].'</td>
									<td width="7%" align="right" style="border-right:1px solid #000;" >'.number_format_value_checker($res_course->fields['LeftEarlyHoursMissed'],2).'</td>
									<td width="9%" align="right" >'.number_format_value_checker(($res_course->fields['AttendancePercentage']*100),2).' %</td>
									<td width="6%" align="right" style="border-right:1px solid #000;" >'.$res_course->fields['GRADE'].'</td>
								</tr>';
								
								$res_course->MoveNext();

						}
						//exit;
						if($total_GPAValue>0){
						$GPA=$total_GPAValue/$total_GPAWeight;
						}
						if($total_attended_percentage>0){
							$percentage_course=($total_attended_percentage/$total_rec);
						}else{
							$percentage_course=0;
						}
						//total calculation
						$border_3 		= "border-top:1px solid #000;";
						$border_left 	= "";
						$txt .=	'<tr>
						<td width="21%" style="border-top:1px solid #000;" ></td>';
						$txt .=	'<td width="7%" align="right" style="'.$border_3.$border_left.'" >'.number_format_value_checker($total_attended,2).'</td> 
						<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker($missed_total,2).'</td>
						<td width="8%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_ScheuldedHours,2).'</td>
						<td width="6%" align="right" style="'.$border_3.$border_left.'" >'.$total_AbsentCount.'</td>
						<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_AbsentHoursMissed,2).'</td>
						<td width="6%" align="right" style="'.$border_3.$border_left.'" >'.$total_TardyCount.'</td>
						<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_TardyHoursMissed,2).'</td>
						<td width="8%" align="right" style="'.$border_3.$border_left.'" >'.$total_LeftEarlyCount.'</td>
						<td width="7%" align="right" style="'.$border_3.'" >'.number_format_value_checker($total_LeftEarlyHoursMissed,2).'</td>
						<td width="9%" align="right" style="'.$border_3.$border_left.'" >'.number_format_value_checker($percentage_course,2).' %</td>';
						$txt .=	'<td width="6%" align="right" style="'.$border_3.'" >'.number_format_value_checker($GPA,2).'</td>';

					$txt .=	'</tr>';
					
					
					$txt .=	'
						<tr>
						<td width="50%" ><i>Report does not include Transfer Credits</i></td>
						
						<td width="47%" align="right"  ><i>(Cumulative GPA)</i></td>
						</tr>
						<tr>
						<td width="60%"><i>Absent hours missed sums the scheduled hours for days marked as "A" (absent)</i></td>
						<td width="40%" align="right"></td>
						</tr>';
			
	$txt.='
	</table>';
					} //show_attenance_summary
	$txt .= '</td>
				</tr>
			</table>
			
				
				';
			

		$m++;
		$db->next_result();
		$pdf->writeHTML($txt);
		



	}
	//$db->close();





	$file_name = 'Custom_course_offering_grade_book_transcript_report'.'.pdf';
  
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
}

//$Get_Stud_Enrollment = $_POST['PK_STUDENT_ENROLLMENT'];


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

	
