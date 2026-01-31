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
		// $this->Cell(30, 10, "Official Signature: _______________________", 0, false, 'C', 0, '', 0, false, 'T', 'M');
		// $image_file = "../assets/images/signature/focus/Joe - High Res.png";
		// $this->Image($image_file,110, 280, 30, 15, '', 'T', 'M');
    }
}


require_once("pdf_custom_sap_header.php"); 

function custom_make_up_forms_overdue_pdf($PK_STUDENT_MASTERS, $one_stud_per_pdf){
	global $db;


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
    $pdf->startPageGroup();
	$pdf->AddPage();

	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$LOGO = '';
	if($res->fields['PDF_LOGO'] != '')
		$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';

	if($_GET['report_type'] == '')
		$_GET['report_type'] = 1;

	if($_GET['report_type'] == 1) {
		$border_1 = "border-top:1px solid #000;";
		$border_2 = "border-bottom:1px solid #000;";
	} else {
		$border_1 = "";
	}
	
	$PK_STUDENT_MASTER_ARR = $PK_STUDENT_MASTERS;

    $txt = "";
		
    $txt .= '
            <table border="0" cellspacing="0" cellpadding="3" width="100%" >
                <tr>
                    <td width="100%" align="center" ><b><i style="font-size:50px">Make-Up Forms Overdue</i></b><br /></td>
                </tr>
            </table>
            <br /><br />';

    $txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
            <thead>
                <tr>
                    <td style="height: 25px;line-height:8px;" align="center" width="30%">
                        <b>Student</b>
                    </td>
                    <td style="height: 25px;line-height:8px;" align="center" width="30%">
                        <b>Program</b>
                    </td>
                    <td style="height: 25px;line-height:8px;" align="center" width="30%">
                        <b>Course</b>
                    </td>
                    <td style="height: 25px;line-height:8px;" align="center" width="10%">
                        <b>Class Date</b>
                    </td>
                </tr>
            </thead>';


	$PK_STUDENT_ENROLLMENT = implode(',',$PK_STUDENT_MASTER_ARR);

	$res_makeup = $db->Execute("SELECT S_COURSE.COURSE_CODE AS COURSE_CODE,
									CONCAT(S_STUDENT_MASTER.LAST_NAME,', ',S_STUDENT_MASTER.FIRST_NAME) AS STUD_NAME,
									M_CAMPUS_PROGRAM.DESCRIPTION AS PROGRAM_DESCRIPTION,
									IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE, '%m/%d/%Y'), '') AS CLASS_DATE
								FROM 
									S_STUDENT_SCHEDULE 
									LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
									LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT
									LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER 
									LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
									LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
									LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
									LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE
									LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
									LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE 
								WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT)
									AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
									AND M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = 1
									AND S_STUDENT_ATTENDANCE.COMPLETED = 1
								ORDER BY 
									STUD_NAME ASC ");

	while (!$res_makeup->EOF) 
	{
		$txt .= '<tr>	
					<td width="30%">'.$res_makeup->fields['STUD_NAME'].'</td>
					<td width="30%">'.$res_makeup->fields['PROGRAM_DESCRIPTION'].'</td>
					<td width="30%">'.$res_makeup->fields['COURSE_CODE'].'</td>
					<td width="10%">'.$res_makeup->fields['CLASS_DATE'].'</td>
				</tr>';
		$res_makeup->MoveNext();
	}		
		
    $txt .= '</table>';

    $pdf->writeHTML($txt);

	$file_name 	= 'Custom_Make_Up_Forms_Overdue_'.uniqid().'.pdf';
  
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

$Get_Stud_Master = $_POST['PK_STUDENT_MASTER'];
$student_array   = array();

$s=0;
foreach ($Get_Stud_Master as $key => $value) {
	# code...
	$student_array[$value][]= $_POST['PK_STUDENT_ENROLLMENT'][$s];
	$s++;
}

if(!empty($student_array)) {
	echo custom_make_up_forms_overdue_pdf($_POST['PK_STUDENT_ENROLLMENT'], 0);
}

	