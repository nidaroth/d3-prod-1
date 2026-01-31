<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

function get_perivous_period($period_calculation,$period){
		foreach($period_calculation as $value)
			{
				if($value['period']==$period){
					return $value;
					break;
				}
			}
}

function get_most_recently_completed_terms($PK_STUDENT_MASTER,$enrollment_id)
{
	global $db;

	$total_cum_rec		= 0;
	$c_in_num_grade_tot = 0; //ticket #1240
	$c_in_att_tot 		= 0;
	$c_in_comp_tot 		= 0;
	$c_in_cu_gnu 		= 0;
	$c_in_gpa_tot 		= 0;

	$summation_of_gpa      = 0;
	$summation_of_weight   = 0;
	
	$Denominator = 0;
	$Numerator 	 = 0;
	$Numerator1  = 0;

	$period=1;

	$sap_array=array();
	$en_cond="AND PK_STUDENT_ENROLLMENT IN ($enrollment_id)";

	$sql="SELECT S_COURSE.TRANSCRIPT_CODE, CREDIT_TRANSFER_STATUS, S_COURSE.COURSE_DESCRIPTION, S_STUDENT_CREDIT_TRANSFER.UNITS, S_COURSE.FA_UNITS, S_STUDENT_CREDIT_TRANSFER.TC_NUMERIC_GRADE, S_STUDENT_CREDIT_TRANSFER.GRADE, PK_STUDENT_ENROLLMENT, S_STUDENT_CREDIT_TRANSFER.PK_GRADE, S_GRADE.NUMBER_GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS,
	CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
	CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
	FROM S_STUDENT_CREDIT_TRANSFER
	LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE  
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
	LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
	WHERE S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
	AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
	AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER BY S_COURSE.TRANSCRIPT_CODE ASC";


	
	$res_tc = $db->Execute($sql);
	while (!$res_tc->EOF) 
	{
		$COMPLETED_UNITS	 = 0;
		$ATTEMPTED_UNITS	 = 0;
		//diam-726
		//diam-726
		if($res_tc->fields['UNITS_ATTEMPTED'] == 1)
			$ATTEMPTED_UNITS = $res_tc->fields['UNITS'];
		
		$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
		$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
		//diam-726
		if($res_tc->fields['UNITS_COMPLETED'] == 1) {
			$COMPLETED_UNITS 	 = $res_tc->fields['UNITS'];
			$c_in_comp_tot  	+= $COMPLETED_UNITS;
			$c_in_comp_sub_tot  += $COMPLETED_UNITS;
		}
	
		$gnu = 0;
		//diam-726
		if($res_tc->fields['CALCULATE_GPA'] == 1) {
			$gnu 				 = $res_tc->fields['UNITS'] * $res_tc->fields['TC_NUMERIC_GRADE']; //diam-726
			$c_in_cu_gnu 		+= $gnu; 
			$c_in_cu_sub_gnu 	+= $gnu; 
			
			$gpa				= $gnu / $COMPLETED_UNITS;;
			$c_in_gpa_sub_tot 	+= $gpa;
			$c_in_gpa_tot 		+= $gpa;
			
			$c_in_num_grade_sub_tot	+= $res_tc->fields['TC_NUMERIC_GRADE']; //ticket #1240
			$c_in_num_grade_tot		+= $res_tc->fields['TC_NUMERIC_GRADE']; //ticket #1240
			
			$total_rec++;
			$total_cum_rec++;

			// calulated gpa DIAM-781
			$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
			$tc_gpa_value_total 		+= $TC_GPA_VALULE; 
			$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
			$tc_gpa_weight_total 		+= $TC_GPA_WEIGHT; 
			// calulated gpa DIAM-781

			$summation_of_gpa     += $TC_GPA_VALULE;
			$summation_of_weight  += $TC_GPA_WEIGHT;
		}

$res_tc->MoveNext();
}




	$res_term = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, S_TERM_MASTER.TERM_DESCRIPTION FROM S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, M_COURSE_OFFERING_STUDENT_STATUS WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS AND SHOW_ON_TRANSCRIPT = 1 $en_cond ORDER By BEGIN_DATE_1 ASC");
					while (!$res_term->EOF) 
					{
						$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];
						
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
							if($res_course->fields['CALCULATE_GPA'] == 1) 
							{
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

								// calulated gpa DIAM-781
								$GPA_VALULE 				 = $res_course->fields['GPA_VALUE']; 
								$gpa_value_total 		+= $GPA_VALULE; 
								$GPA_WEIGHT 				 = $res_course->fields['GPA_WEIGHT']; 
								$gpa_weight_total 		+= $GPA_WEIGHT; 

								$summation_of_gpa    += $GPA_VALULE;
								$summation_of_weight += $GPA_WEIGHT;
							}


							

							$res_course->MoveNext();
						}

						$gpa_weighted=0;
							//$total_gpa_weighted=0;
						if($gpa_value_total>0)
						{
							$gpa_weighted=$gpa_value_total/$gpa_weight_total;
							$total_tc_gpa_weighted +=$gpa_weighted;
						}

						

						$cumulative_attempted_total=$c_in_att_tot;
						$cumulative_completed_total=$c_in_comp_tot;
						$cumulative_gpa_total=$summation_of_gpa/$summation_of_weight;
						$sap_array[$res_term->fields['BEGIN_DATE_1']]=array(
							'period'=>$period,
							'attempted_total'=>$cumulative_attempted_total,'completed_total'=>$cumulative_completed_total,
							'gpa_total'=>$cumulative_gpa_total
						);
				$period++;
				$res_term->MoveNext();
			}

			// if($PK_STUDENT_MASTER=='1874642'){
			// 	echo $c_in_comp_tot;
			// 	echo "<br>";
			// 	echo $c_in_att_tot;
			// 	print_r($sap_array);
			// 	exit;
			// }

		return $sap_array;



}


if(!empty($_POST) || $_GET['p'] == 'r'){ //Ticket # 1195

	
	/* Ticket # 1195 */


	$campus_name 	= "";
	$where_cond="";

	if($_POST['PK_CAMPUS'] != ''){
		$PK_CAMPUS 	 = $_POST['PK_CAMPUS'];
		$campus_cond=" AND PK_CAMPUS IN($PK_CAMPUS)";
		$where_cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		$cond=" AND S_TERM_MASTER_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS)";
		
	}
   
	if($_POST['PK_TERM_MASTER'] != ''){

		$term_array = explode('_',$_POST['PK_TERM_MASTER']);
		$PK_TERM_MASTER 	 = $term_array[0];
		$TERM_DATE 	 = $term_array[1];

		$cond .= " AND S_TERM_MASTER.PK_TERM_MASTER =".$PK_TERM_MASTER;
	}

	$student_status="";
	if($_POST['PK_STUDENT_STATUS'] != ''){

		$PK_STUDENT_STATUS = $_POST['PK_STUDENT_STATUS'];	
		$where_cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($PK_STUDENT_STATUS) ";
		$student_status .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($PK_STUDENT_STATUS) ";


		//$cond .= " AND S_TERM_MASTER.PK_TERM_MASTER =".$PK_TERM_MASTER;
	}





	
	if($_POST['PK_TERM_MASTER'] != '') {

		$query = "SELECT 
		student_term.PK_STUDENT_MASTER,
		S_TERM_MASTER.PK_TERM_MASTER,
		IF(BEGIN_DATE = '0000-00-00',
		'',BEGIN_DATE) AS BEGIN_DATE_1,
		IF(student_term.BEGIN_DATE_NEW = '0000-00-00',
		'',
		DATE_FORMAT(student_term.BEGIN_DATE_NEW, '%m/%d/%Y' )) AS FIRST_TERM_DATE,
		S_TERM_MASTER.TERM_DESCRIPTION,
		student_term.S_PK_TERM_MASTER,
		student_term.PK_STUDENT_ENROLLMENT,
		GROUP_CONCAT(student_term.PK_STUDENT_ENROLLMENT) as PK_STUDENT_ENROLLMENT_NEW	
	FROM
		S_STUDENT_COURSE
	INNER JOIN S_COURSE_OFFERING ON
		S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING
	INNER JOIN S_TERM_MASTER ON
		S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER
	INNER JOIN S_TERM_MASTER_CAMPUS ON
		S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER
	LEFT JOIN (
		SELECT
			S_STUDENT_MASTER.PK_STUDENT_MASTER,
			S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ,
			S_STUDENT_MASTER.FIRST_NAME,
			S_STUDENT_MASTER.LAST_NAME,
			S_TERM_MASTER.BEGIN_DATE AS BEGIN_DATE_NEW,
			S_TERM_MASTER.PK_TERM_MASTER AS S_PK_TERM_MASTER

		FROM 
					S_STUDENT_MASTER,
			S_STUDENT_ENROLLMENT
		INNER JOIN S_TERM_MASTER ON
			S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
		WHERE 
					S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
			AND 
					S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
					$student_status
		
	) AS student_term ON
		S_STUDENT_COURSE.PK_STUDENT_MASTER = student_term.PK_STUDENT_MASTER AND student_term.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT
	WHERE 
			S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
			$cond
			 GROUP BY student_term.S_PK_TERM_MASTER
		 ORDER BY BEGIN_DATE_NEW ASC 
		";

	  
		$def_grade = 0;
		$res_def_grade = $db->Execute("SELECT PK_GRADE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_DEFAULT = 1");
		if($res_def_grade->RecordCount() > 0)
			$def_grade = $res_def_grade->fields['PK_GRADE'];

		$show_cond = " AND (FINAL_GRADE > 0 AND FINAL_GRADE != '$def_grade' ) ";

			
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
					global $db, $campus_cond,$TERM_DATE;
					
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
					
					$this->SetFont('helvetica', 'I', 20);
					$this->SetY(8);
					$this->SetX(220);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(55, 8, "Satisfactory Progress", 0, false, 'L', 0, '', 0, false, 'M', 'L');
					
					
					$this->SetFillColor(0, 0, 0);
					$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
					$this->Line(180, 13, 290, 13, $style);
	
					$str = "Term Calculation Date: ".$TERM_DATE;
					//exit;
				
				
					$this->SetFont('helvetica', 'I', 11);
					$this->SetY(16);
					$this->SetX(185);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(102, 4, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
					
					$cond = "";

					$res_campus = $db->Execute("select GROUP_CONCAT(CAMPUS_CODE) as CAMPUS_CODE from S_CAMPUS WHERE S_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
					$campus_name = $res_campus->fields['CAMPUS_CODE'];

					$this->SetFont('helvetica', 'I', 11);
					$this->SetY(18);
					$this->SetX(185);
					$this->SetTextColor(000, 000, 000);
					//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
					$this->MultiCell(102, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				}
				public function Footer() {
					global $db;
					$this->SetY(-15);
					$this->SetX(270);
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

			$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(7, 31, 7);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->SetAutoPageBreak(TRUE, 30);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->setLanguageArray($l);
			$pdf->setFontSubsetting(true);
			$pdf->SetFont('helvetica', '', 8, '', true);
			$pdf->AddPage();

			$total 	= 0;
			$txt 	= '';

			$sub_total = 0;
			
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Student</b></td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Program</b></td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Status</b></td>
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>First Term</b></td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Attempted</b></td>
								<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;"  ><b>Completed</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b> %Comp</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>GPA Actual</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>SAP</b></td>
								<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b>SAP Previous Terms</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b>Action</b></td>
							</tr>
						</thead>';
			$res_term = $db->Execute($query);
			while (!$res_term->EOF) 
			{ 

				$PK_TERM_MASTER=$res_term->fields['S_PK_TERM_MASTER'];
				if(!empty($PK_TERM_MASTER)){
				$BEGIN_DATE_1=$res_term->fields['BEGIN_DATE_1'];

				$arr_PK_STUDENT_ENROLLMENT_NEW=explode(',',$res_term->fields['PK_STUDENT_ENROLLMENT_NEW']);
				$PK_STUDENT_ENROLLMENT_NEW=implode(',',array_unique($arr_PK_STUDENT_ENROLLMENT_NEW));

				$txt 	.= '<tr>
								<td colspan="9" style="color:#444444"> First Term Date: '.$res_term->fields['FIRST_TERM_DATE'].'</td>
			
							</tr>';
			

				$query_for_terms_student="select IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, 
				S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) as STUD_NAME, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, STUDENT_ID, CAMPUS_CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, STUDENT_STATUS    
				FROM 
				S_STUDENT_MASTER 
				LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
				, S_STUDENT_ENROLLMENT 
				LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
				LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
				LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
				LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
				LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
				WHERE 
				S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
				S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				S_STUDENT_ENROLLMENT.PK_TERM_MASTER = $PK_TERM_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT_NEW)
				$where_cond
				ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC  ";
				// echo $query_for_terms_student;
				// exit;
				$res_en = $db->Execute($query_for_terms_student);
				while (!$res_en->EOF) 
				{ 
					$PK_STUDENT_MASTER 		= $res_en->fields['PK_STUDENT_MASTER'];
					$PK_STUDENT_ENROLLMENT 		= $res_en->fields['PK_STUDENT_ENROLLMENT'];					
					$period_calculation=get_most_recently_completed_terms($PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT);

					$final_array=$period_calculation[$BEGIN_DATE_1];

					/* Ticket # 1152 */
					$complition=0;
					if($final_array['completed_total'] > 0 && $final_array['attempted_total'] > 0){
						$complition = round(($final_array['completed_total']/$final_array['attempted_total']) *100);
					}

					$GPA = $final_array['gpa_total'];
					$period = $final_array['period'];
					$last_second = $period-1;
					$last_third = $period-2;
					//variable declarations
					$complition2=0;
					$complition3=0;
					$period2=0;
					$period3=0;
					$SAP2="";
					$SAP3="";
					$SAP="";


					// first period
					if($GPA >= 2.0 &&  $complition >= 67)
					{
					    $SAP="Yes";

					}else{

					   $SAP="No";
					}
					
					if($SAP!="Yes")
					{
						// second period
						if($last_second>0)
						{
							$period2=$last_second;
							$last_second_period=get_perivous_period($period_calculation,$last_second);

							if($last_second_period['completed_total'] > 0 && $last_second_period['attempted_total'] > 0)
							{
								$complition2 = round(($last_second_period['completed_total']/$last_second_period['attempted_total']) *100);
							}

							$GPA2 = $last_second_period['gpa_total'];

							if($GPA2 >= 2.0 &&  $complition2 >= 67)
							{
								$SAP2="Yes";
							}else{
								$SAP2="No";
							}
						}
					

						// third period
						if($last_third>0)
						{
							$last_third_period=get_perivous_period($period_calculation,$last_third);
							$period3=$last_third;
							if($last_third_period['completed_total'] > 0 && $last_third_period['attempted_total'] > 0)
							{
								$complition3 = round(($last_third_period['completed_total']/$last_third_period['attempted_total']) *100);
							}

							$GPA3 = $last_third_period['gpa_total'];

							if($GPA3 >= 2.0 &&  $complition3 >= 67)
							{
								$SAP3="Yes";
							}else{
								$SAP3="No";
							}

						}
				    }
// 				if($PK_STUDENT_MASTER==1919030){
					
// echo $SAP3.'-'.$SAP.'-'.$SAP2;
// exit;
// 				}

					// action
					$action="";
					$style="";
					if($SAP == "Yes") 
					{
					    $action="No Action";
						$style="#52BE80";

					}else if(
						($SAP == "Yes" && $SAP2 == "Yes" &&  $SAP3 == "No")
						 || 
						($SAP2 == "Yes" && $SAP3 == "Yes" && $SAP == "No")
						  || 
						($SAP3 == "Yes" && $SAP == "Yes" && $SAP2 == "No")
						  )
					{
						$action="Warning";
						$style="#EB984E";

					}else if(
						($SAP == "Yes" && $SAP2 == "No" && $SAP3 == "No" && $period2!=0 && $period!=0 && $period3!=0) 
						|| 
						($SAP2 == "Yes" && $SAP == "No" && $SAP3 == "No" && $period2!=0 && $period!=0 && $period3!=0)
						 || 
						 ($SAP3 == "Yes" && $SAP == "No" && $SAP2 == "No" && $period2!=0 && $period!=0 && $period3!=0))
					{
						$action="Probation";
						$style="red";
					}else if($period!=0 && $period2==0 && $period3==0){
						$action="No Action";
						$style="#52BE80";
					}elseif($SAP == "No" && $SAP2 == "No" && $SAP3 == "No"){
						$action="Probation";
						$style="red";
					}
					else if($period!=0 && $period2!=0 && $period3==0 && $SAP == "No" && $SAP2 == "Yes" && $SAP3 == ""){
						$action="Warning";
						$style="#EB984E";
					}

					if(trim($res_en->fields['STUDENT_STATUS'])=='Cancel' || trim($res_en->fields['STUDENT_STATUS'])=='No Show'){
						$SAP='N/A';
						$action="No Action";
						$style="#52BE80";


					}

					if(trim($res_en->fields['STUDENT_STATUS'])=='Drop' && $SAP == "No" && $SAP2 == "" && $SAP3 == ""){
						$action="Warning";
						$style="#EB984E";

					}
					
						$txt 	.= '<tr>
									<td width="15%" >'.$res_en->fields['STUD_NAME'].'</td>
									<td width="6%"  >'.$res_en->fields['PROGRAM_CODE'].'</td>
									<td width="6%"  >'.$res_en->fields['STUDENT_STATUS'].'</td>							
									<td width="8%"  >'.$res_en->fields['BEGIN_DATE_1'].'</td>';
						$txt 	.= '<td width="6%">'.number_format_value_checker($final_array['attempted_total'],2).'</td>
									<td width="6%">'.number_format_value_checker($final_array['completed_total'],2).'</td>
									<td width="7%">'.number_format_value_checker($complition).'</td>
									<td width="7%">'.number_format_value_checker(($GPA),2).'</td>
									<td width="7%">'.$SAP.'</td>
									<td width="20%"><b>Period</b>: '.($period2?$period2:'').' <b>SAP</b>: '.$SAP2.' <b>Period</b>: '.($period3?$period3:'').' <b>SAP</b>: '.$SAP3.'</td>
									<td width="7%" style="color:'.$style.'">'.$action.'</td>
								</tr>';
					$res_en->MoveNext();
				} // student loop end
			}
				$res_term->MoveNext();
			} // term loop end
			
			$txt 	.= '</table>';
			 //echo $txt;
			 //exit;
			
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

			$file_name = 'Special SAP Reporting_'.time().'.pdf';

			$dir 			= 'temp/';
			$outputFileName = $dir.$file_name; 
			$pdf->Output($outputFileName, 'F');
			header('Content-type: application/json; charset=UTF-8');		
			$data_res['path'] = $outputFileName;
			$data_res['filename'] = $file_name;
			echo  json_encode($data_res);
			/////////////////////////////////////////////////////////////////
	}
}
?>


