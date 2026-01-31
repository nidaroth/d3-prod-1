<?php require_once('../global/config.php');
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
require_once("pdf_custom_header.php");  
require_once("function_transcript_header.php"); //DIAM-2180


if(check_access('REPORT_REGISTRAR') == 0 && $_SESSION['PK_USER_TYPE'] != 3){
	header("location:../index");
	exit;
}
function getExplodedeheadervlues($box,$box_field){
	$boxpos = strpos($box, ':');
	if ($boxpos !== false) {
		$BOX_44_DATA = explode(":",$box);
	} else {
		$BOX_44_DATA = explode(":",$box); //not found
		if($box_field =='STUD_ADDRESS_1')
			array_unshift($BOX_44_DATA,"Address Line 1");
		else if($box_field =='STUD_ADDRESS_2')
			array_unshift($BOX_44_DATA,"Address Line 2");
		if($box_field =='STUD_CITY_STATE_ZIP')
			array_unshift($BOX_44_DATA,"City,State-zip");
	}

	return $BOX_44_DATA;
}
class MYPDF extends TCPDF
{
	public function Header()
	{
		global $db;	
		// if ($_GET['id'] != '') {

		// 	if ($this->PageNo() > 0 ) {
				$CONTENT = '';//pdf_custom_header($_GET['id'], $_GET['eid'], 1);
				$CONTENT = '<b>'.str_replace("<br /><br />", "<br />", $CONTENT).'</b>'; 
				$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true, '', true, true);
				$this->SetMargins('', 35, '');
		// 	} else {
		// 		// $res = $db->Execute("SELECT FIRST_NAME, LAST_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		// 		// $STUD_NAME = $res->fields['LAST_NAME'] . ', ' . $res->fields['FIRST_NAME'];
		// 		// $this->SetFont('helvetica', 'I', 15);
		// 		// $this->SetY(8);
		// 		// $this->SetX(10);
		// 		// $this->SetTextColor(000, 000, 000);
		// 		// $this->Cell(75, 8, $STUD_NAME, 0, false, 'L', 0, '', 0, false, 'M', 'L');
		// 		// $this->SetMargins('', 25, '');
		// 	}
		// } else {
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS) as ADDRESS,
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

			if ($res->fields['PDF_LOGO'] != '') {
				$ext = explode(".", $res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}

			$this->SetFont('helvetica', '', 15);
			$this->SetY(8);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetFont('helvetica', '', 8);
			$this->SetY(13);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['ADDRESS'] . ' ' . $res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetY(17);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['CITY'] . ' ' . $res->fields['STATE_CODE'] . ' ' . $res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetY(21);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');

			$this->SetY(25);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['WEBSITE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		//}

		$this->SetFont('helvetica', 'I', 18);
		$this->SetY(15);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, "Official Transcript", 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(120, 19, 202, 19, $style);
		
		$this->SetFont('helvetica', 'I', 18);
		$this->SetY(23);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, "Detail", 0, false, 'R', 0, '', 0, false, 'M', 'L');

		if ($_REQUEST['dt'] != '') {
			$date = date("m/d/Y", strtotime($_REQUEST['dt']));	
			$datestr = "As of : ".$date;		
		}
		
		$this->SetFont('helvetica', 'I', 12);
		$this->SetY(30);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, $datestr, 0, false, 'R', 0, '', 0, false, 'M', 'L');
	}
	public function Footer()
	{
		global $db;

		// $res_acc = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 19");
		// $CONTENT = nl2br($res_acc->fields['CONTENT']);	
		// $this->SetY(-50);
		// $this->SetX(10);
		// $this->SetFont('helvetica', '', 8);
        // $this->writeHTML($CONTENT, false, true, false, true);

		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);

		$timezone = $_SESSION['PK_TIMEZONE'];
		$timezone = $_SESSION['PK_TIMEZONE'];
		if ($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if ($timezone == '' || $timezone == 0)
				$timezone = 4;
		}

		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'), 'l, F d, Y h:i A', $res->fields['TIMEZONE'], date_default_timezone_get());

		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}


$res = $db->Execute("SELECT S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if ($res->RecordCount() == 0) {
	header("location:manage_student?t=" . $_GET['t']);
	exit;
}

$_SESSION['temp_id'] = '';
//Function start
function program_grade_book_transcript($PK_STUDENT_MASTERS,$one_stud_per_pdf, $report_name){
	global $db;
	$date_cond  = "";
	$date_cond1 = "";
	$ENROLLMENT_IDS="";
	$ENROLLMENT_ID_ARR="";

	//as of date filter
	if ($_REQUEST['dt'] != '') {
		$date = date("Y-m-d", strtotime($_REQUEST['dt']));
		$date_cond  = " AND COMPLETED_DATE <= '$date' ";
		$date_cond1 = " AND S_STUDENT_SCHEDULE.SCHEDULE_DATE <= '$date' ";
	}

	if($_GET['eid']!=""){
		$ENROLLMENT_IDS = $_GET['eid'];
		$ENROLLMENT_ID_ARR = explode(",",$ENROLLMENT_IDS);
	}
		
	//from function param
	$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);

	$res_type1 = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 19");
	$BREAK_VAL = 30 + $res_type1->fields['FOOTER_LOC'];

	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
	$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 25, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 8, '', true);

	$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$LOGO = '';
	if($res->fields['PDF_LOGO'] != '')
	$LOGO = '<img src="'.$res->fields['PDF_LOGO'].'" />';


	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
	$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
	$absent_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

	$excluded_att_code  = "";
	$exc_att_code_arr = array();
	$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
	while (!$res_exc_att_code->EOF) {
		$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
		$res_exc_att_code->MoveNext();
	}

	$exclude_cond  = "";
	if(!empty($exc_att_code_arr))
		$exclude_cond = " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE NOT IN (".implode(",",$exc_att_code_arr).") ";


	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
	
		$res_stu = $db->Execute("select LAST_NAME, FIRST_NAME,CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME, STUDENT_ID, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DOB, EXCLUDE_TRANSFERS_FROM_GPA from S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
		
		$res_address = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 
		
	//print_r($ENROLLMENT_ID_ARR);exit;
	foreach($ENROLLMENT_ID_ARR as $PK_EID) {
		
		if($PK_EID != '')
			$en_cond1 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT =$PK_EID ";
		else
			$en_cond1 = " AND IS_ACTIVE_ENROLLMENT = 1 ";


		$sql_for_enroll = "SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,CODE,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING,UNITS,HOURS,MONTHS,WEEKS,FA_UNITS, EXPECTED_GRAD_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME,CAMPUS_CODE,M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM,M_CAMPUS_PROGRAM.CLOCK_CREDIT FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond1 AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";

		$res_type = $db->Execute($sql_for_enroll);

		$EXPECTED_GRAD_DATE = $res_type->fields['EXPECTED_GRAD_DATE'];
		if($EXPECTED_GRAD_DATE != '0000-00-00' && $EXPECTED_GRAD_DATE != '')
			$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
		else
			$EXPECTED_GRAD_DATE = '';

		
		if($PK_EID != '')
			$PK_STUDENT_ENROLLMENT = $PK_EID;
		else
			$PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
			
		$pdf->PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
		$pdf->STUD_NAME 			= $res_stu->fields['NAME'];
		$pdf->startPageGroup();
		$pdf->AddPage();
		
		
		$res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_ENROLLMENT =$PK_STUDENT_ENROLLMENT");
	
	

	$SCHEDULED_HOUR 	 = 0;
	$COMP_SCHEDULED_HOUR = 0;
	$res_sch = $db->Execute("SELECT HOURS, PK_ATTENDANCE_CODE, COMPLETED, PK_SCHEDULE_TYPE FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT $date_cond1"); 
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
			}
		}	
		$res_sch->MoveNext();
	}
	
	$res_attended = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT $date_cond1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code) ");
	
	$res_attended_all = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDED_HOUR FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT =$PK_STUDENT_ENROLLMENT $date_cond1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN ($present_att_code)  ");


	
		$txt = '<div style="border-top: 2px #c0c0c0 solid" ></div>';
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="1" width="100%">
                <tr>
                    <td width="50%">
                        <table border="0" cellspacing="0" cellpadding="2" width="100%">
                            <tr>
                                <td width="100%"><b>'.$res_stu->fields['FIRST_NAME'].' '.$res_stu->fields['MIDDLE_NAME'].' '.$res_stu->fields['LAST_NAME'].'</b></td>
                            </tr>
                            <tr>
                                <td width="100%"><b>'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'</b></td>
                            </tr>
                            <tr>
                                <td width="100%"><b>'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'</b></td>
                            </tr>
                            <tr>
                                <td width="100%"><b>'.$res_address->fields['COUNTRY'].'</b></td>
                            </tr>
                        </table>
					 </td>';
                    
            $txt .= '<td width="80%">';
			//DIAM-2180
			$res_type2 = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $en_cond1 ORDER By BEGIN_DATE ASC, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE ASC ");


			$PK_STUDENT_ENROLLMENT11 	= $res_type2->fields['PK_STUDENT_ENROLLMENT'];
			$PK_CAMPUS_PROGRAM11 		= $res_type2->fields['PK_CAMPUS_PROGRAM'];
			
			$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM11' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			$BOX_1  = transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_1_DATA = getExplodedeheadervlues($BOX_1,$res_report_header->fields['BOX_1']);	

			$BOX_2  = transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_2_DATA  = getExplodedeheadervlues($BOX_2,$res_report_header->fields['BOX_2']);	

			$BOX_3  = transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_3_DATA = getExplodedeheadervlues($BOX_3,$res_report_header->fields['BOX_3']);	

			$BOX_4  = transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_4_DATA = getExplodedeheadervlues($BOX_4,$res_report_header->fields['BOX_4']);			

			$BOX_5  = transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11'");
			$BOX_5_DATA  = getExplodedeheadervlues($BOX_5,$res_report_header->fields['BOX_5']);		

			$BOX_6  = transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_6_DATA  = getExplodedeheadervlues($BOX_6,$res_report_header->fields['BOX_6']);	

			$BOX_7  = transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_7_DATA = getExplodedeheadervlues($BOX_7,$res_report_header->fields['BOX_7']);	

			$BOX_8  = transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_8_DATA  = getExplodedeheadervlues($BOX_8,$res_report_header->fields['BOX_8']);	

			$BOX_9  = transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT11' ");
			$BOX_9_DATA = getExplodedeheadervlues($BOX_9,$res_report_header->fields['BOX_9']);	
			
			
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
                                $txt .='<tr>
                                    <td width="30%"><b>'.$BOX_1_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_1_DATA[1].'</b></td>
									<td width="50%"></td>
                                </tr>';
                                $txt .='<tr>
                                    <td width="30%"><b>'.$BOX_2_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_2_DATA[1].'</b></td>
									<td width="50%"></td>
                                </tr>';
                                 $txt .='<tr>
                                     <td width="30%"><b>'.$BOX_3_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_3_DATA[1].'</b></td>
									<td width="50%"></td>
                                </tr>';
                                 $txt .='<tr>
                                     <td width="30%"><b>'.$BOX_4_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_4_DATA[1].'</b></td>
									<td width="50%"></td>
                                </tr>';
                                 $txt .='<tr>
                                     <td width="30%"><b>'.$BOX_5_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_5_DATA[1].'</b></td>
									<td width="50%"></td>
                                </tr>';
                                 $txt .='<tr>
                                     <td width="30%"><b>'.$BOX_6_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_6_DATA[1].'</b></td>
									<td width="50%"></td>
                                </tr>';
                                 $txt .='<tr>
                                    <td width="30%"><b>'.$BOX_7_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_7_DATA[1].'</b></td>
									<td width="50%"></td>
                                </tr>';
								 $txt .='<tr>
									 <td width="30%"><b>'.$BOX_8_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_8_DATA[1].'</b></td>
									<td width="50%"></td>
								</tr>';
								 $txt .='<tr>
									 <td width="30%"><b>'.$BOX_9_DATA[0].'</b></td>
                                    <td width="20%"><b>'.$BOX_9_DATA[1].'</b></td>
									<td width="50%"></td>
								</tr>';
                            $txt .='</table>';
							//DIAM-2180

			$txt .= '	</td>';
			$txt .= '</tr>
             </table><br />';
			 					
				
		$txt .= '<div style="border-top: 2px #c0c0c0 solid" ></div>';		

		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
                <tr>
                    <td width="40%">
                        <table border="0" cellspacing="0" cellpadding="3" width="100%">';
						$tot_session_req 	= 0;
						$tot_session_com 	= 0;
						$tot_hour_req 		= 0;
						$tot_hour_com 		= 0;
						$tot_point_req		= 0;
						$tot_point_com 		= 0;
						$res_test_type_sql = "SELECT S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE, GRADE_BOOK_TYPE from 
						S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
						WHERE 
						M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
						M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND
						PK_STUDENT_ENROLLMENT =$PK_STUDENT_ENROLLMENT AND 
						S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
						COMPLETED_DATE != '0000-00-00' $date_cond 
						GROUP BY S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE ORDER BY GRADE_BOOK_TYPE ASC ";

						//echo "<br><br><br>";
						$res_test_type = $db->Execute($res_test_type_sql);
			
						$header_flag = false;
						while (!$res_test_type->EOF) {
			
								if ($res_test_type->fields['GRADE_BOOK_TYPE'] == 'Test') {
									if ($header_flag == false) {
										$txt .= ' <tr>
														<td width="70%" align="left"><b><u>Test Description</u></b></td>
														<td width="30%" align="right"><b><u>Average <br/>Grade</u></b></td>
													</tr>';
										$header_flag = true;
									}
									$PK_GRADE_BOOK_TYPE = $res_test_type->fields['PK_GRADE_BOOK_TYPE'];

									$type_tot_session_req 	= 0;
									$type_tot_session_com 	= 0;
									$type_tot_hour_req 		= 0;
									$type_tot_hour_com 		= 0;
									$type_tot_point_req		= 0;
									$type_tot_point_com 	= 0;

									$res_code = $db->Execute("SELECT M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION  from 
									S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
									WHERE 
									M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
									M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
									PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT AND 
									S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
									COMPLETED_DATE != '0000-00-00' $date_cond AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE'
									GROUP BY M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE ORDER BY CODE ASC");

									while (!$res_code->EOF) {
										$PK_GRADE_BOOK_CODE = $res_code->fields['PK_GRADE_BOOK_CODE'];
										
										$sub_tot_session_req 	= 0;
										$sub_tot_session_com 	= 0;
										$sub_tot_hour_req 		= 0;
										$sub_tot_hour_com 		= 0;
										$sub_tot_point_req		= 0;
										$sub_tot_point_com 		= 0;
										$res_test = $db->Execute("SELECT PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, CODE, M_GRADE_BOOK_CODE.DESCRIPTION GRADE_BOOK_TYPE, COMPLETED_DATE, SESSION_REQUIRED, SESSION_COMPLETED, HOUR_REQUIRED, HOUR_COMPLETED, POINTS_REQUIRED, POINTS_COMPLETED, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION, DATE_FORMAT(COMPLETED_DATE, '%m/%d/%Y') as COMPLETED_DATE_1 from 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
										WHERE 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
										M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
										PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
										COMPLETED_DATE != '0000-00-00' $date_cond AND 
										S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND 
										M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' 
										ORDER BY CODE ASC, COMPLETED_DATE ASC");

											while (!$res_test->EOF) {
												// dd($res_test);
												$sub_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
												$sub_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
												$sub_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
												$sub_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
												$sub_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
												$sub_tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];

												$type_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
												$type_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
												$type_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
												$type_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
												$type_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
												$type_tot_point_com 	+= $res_test->fields['POINTS_COMPLETED'];

												$tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
												$tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
												$tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
												$tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
												$tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
												$tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];

												$res_test->MoveNext();												
											}// while end		

										$txt .= '<tr>
										<td width="70%"  align="left">' . $res_code->fields['DESCRIPTION'] . '</td>
										<td width="30%" align="right">'. number_format_value_checker(($sub_tot_point_com / $sub_tot_point_req * 100), 2). '</td></tr>'; 
										$res_code->MoveNext();
									} // while end
								}//if end
							$res_test_type->MoveNext();
						}// while end
							
					$txt .= ' </table>
					</td>
					<td width="10%"></td>';

                    $txt .= '<td width="50%">
                            <table border="0" cellspacing="0" cellpadding="3" width="100%">';

					$res_test_type = $db->Execute($res_test_type_sql);
					$lab_header_flag  = false;
					while (!$res_test_type->EOF) {
		
						if($res_test_type->fields['GRADE_BOOK_TYPE'] == 'Lab') {
							if ($lab_header_flag == false) {

							$txt .= '<tr>
									<td width="40%" align="left"><b><u>Lab Description</u></b></td>
									<td width="20%" align="right"><b><u>Required<br/>Sessions</u></b></td>
									<td width="20%" align="right"><b><u>Completed<br/>Sessions</u></b></td>
									<td width="20%" align="right"><b><u>Remaining<br/>Sessions</u></b></td>
								</tr>'; 
							$lab_header_flag = true;
							}
							$PK_GRADE_BOOK_TYPE = $res_test_type->fields['PK_GRADE_BOOK_TYPE'];

							$type_tot_session_req 	= 0;
							$type_tot_session_com 	= 0;
							$type_tot_hour_req 		= 0;
							$type_tot_hour_com 		= 0;
							$type_tot_point_req		= 0;
							$type_tot_point_com 	= 0;
							$res_code = $db->Execute("SELECT M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION  from 
							S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
							WHERE 
							M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
							M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
							PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT AND 
							S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
							COMPLETED_DATE != '0000-00-00' $date_cond AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE'	GROUP BY M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE ORDER BY CODE ASC");

							while (!$res_code->EOF) {
								$PK_GRADE_BOOK_CODE = $res_code->fields['PK_GRADE_BOOK_CODE'];
								
								$sub_tot_session_req 	= 0;
								$sub_tot_session_com 	= 0;
								$sub_tot_hour_req 		= 0;
								$sub_tot_hour_com 		= 0;
								$sub_tot_point_req		= 0;
								$sub_tot_point_com 		= 0;
								$res_test = $db->Execute("select PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, CODE, M_GRADE_BOOK_CODE.DESCRIPTION GRADE_BOOK_TYPE, COMPLETED_DATE, SESSION_REQUIRED, SESSION_COMPLETED, HOUR_REQUIRED, HOUR_COMPLETED, POINTS_REQUIRED, POINTS_COMPLETED, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION, DATE_FORMAT(COMPLETED_DATE, '%m/%d/%Y') as COMPLETED_DATE_1 from 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE 
								WHERE 
								M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
								M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND 
								PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT AND 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
								COMPLETED_DATE != '0000-00-00' $date_cond AND 
								S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE = '$PK_GRADE_BOOK_TYPE' AND 
								M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = '$PK_GRADE_BOOK_CODE' 
								ORDER BY CODE ASC, COMPLETED_DATE ASC");

									while (!$res_test->EOF) {
										$sub_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
										$sub_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
										$sub_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
										$sub_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
										$sub_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
										$sub_tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];
			
										$type_tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
										$type_tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
										$type_tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
										$type_tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
										$type_tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
										$type_tot_point_com 	+= $res_test->fields['POINTS_COMPLETED'];
			
										$tot_session_req 	+= $res_test->fields['SESSION_REQUIRED'];
										$tot_session_com 	+= $res_test->fields['SESSION_COMPLETED'];
										$tot_hour_req 		+= $res_test->fields['HOUR_REQUIRED'];
										$tot_hour_com 		+= $res_test->fields['HOUR_COMPLETED'];
										$tot_point_req		+= $res_test->fields['POINTS_REQUIRED'];
										$tot_point_com 		+= $res_test->fields['POINTS_COMPLETED'];
										$res_test->MoveNext();
									}// while end

									$txt .= '<tr>
											<td width="40%" align="left">' . $res_code->fields['DESCRIPTION'] . '</td>
											<td width="20%" align="right">' . $sub_tot_session_req . '</td>
											<td width="20%" align="right">' . $sub_tot_session_com . '</td>
											<td width="20%" align="right">' . ($sub_tot_session_req - $sub_tot_session_com) . '</td>
										</tr>';
										

								$res_code->MoveNext();
							} // while end
						} //if end
							$res_test_type->MoveNext();
					}// while end
			

                	$txt .= '</table>
                        </td>
                    </tr>
                </table>';
				
				
				
				
				if($_GET['report_type'] == 1) {

					if ($res_test_type->RecordCount() > 0) {

					$cond1 = "";
					$cond2 = "";
					$cond3 = "";
					$cond4 = "";

					$cond1 = " AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT ";
					$cond2 = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT ";
					$cond3 = " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT ";
					$cond4 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT ";
					$res_grade = $db->Execute("select SUM(POINTS_REQUIRED) as POINTS_REQUIRED, SUM(POINTS_COMPLETED) as POINTS_COMPLETED from S_STUDENT_PROGRAM_GRADE_BOOK_INPUT LEFT JOIN M_GRADE_BOOK_CODE ON M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED_DATE != '0000-00-00' $cond1 ");
					$per = $res_grade->fields['POINTS_COMPLETED'] / $res_grade->fields['POINTS_REQUIRED'] * 100;
					/*
					$res_course_schedule = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) as STUD_NAME, IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, IF(S_STUDENT_SCHEDULE.END_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.END_TIME,'%h:%i %p'),'') AS END_TIME, IF(S_STUDENT_SCHEDULE.START_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, COURSE_CODE, SCHEDULE_TYPE, S_STUDENT_ATTENDANCE.COMPLETED AS COMPLETED_1, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, PK_STUDENT_ATTENDANCE,ATTENDANCE_HOURS FROM  
					S_STUDENT_MASTER, S_STUDENT_SCHEDULE 
					LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
					LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
					LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
					LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
					LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
					LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
					LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
					LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON  S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL
					WHERE 
					S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
					S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
					S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond2 ");
					$TOTAL_HOURS 		= 0;
					$ATTENDANCE_HOURS 	= 0;
					while (!$res_course_schedule->EOF) {

					if ($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2) {
						$ATTENDANCE_HOURS 	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
					}
					if (($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' && $res_course_schedule->fields['COMPLETED_1'] == 1) || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2)
						$TOTAL_HOURS += $res_course_schedule->fields['HOURS'];

					$res_course_schedule->MoveNext();
					}

					//transfer hours 
					$res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $cond3 ");
					$TRANSFER_HOURS = $res_trans->fields['HOUR'];
					//required hours 
					$res_prog = $db->Execute("SELECT SUM(HOURS) as HOURS FROM S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM $cond4 ");
					$TOTAL_REQUIRED_HOURS = $res_prog->fields['HOURS'];

					$txt .= '<br /><br /><br /><br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%" >					
						<tr nobr="true" >
							<td width="15%" ></td>
							<td width="70%" >
								<table border="0" cellspacing="0" cellpadding="3" width="100%" >
									<tr>
										<td width="100%" >
											<table border="1" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="32%" >Accumulative GPA</td>
													<td width="13%" align="right" >' . number_format_value_checker($per, 2) . '%</td>
													<td width="32%"  >Total Required Hours</td>
													<td width="13%" align="right" >'.number_format_value_checker($TOTAL_REQUIRED_HOURS,2).'</td>
												</tr>
												<tr>
													<td width="32%" ></td>
													<td width="13%" align="right" ></td>
													<td width="32%"  >Total Transfer Hours</td>
													<td width="13%" align="right" >'.number_format_value_checker($TRANSFER_HOURS, 2).'</td>
												</tr>
												<tr>
													<td width="32%" >Total Scheduled Hours</td>
													<td width="13%" align="right" >'.number_format_value_checker($TOTAL_HOURS,2).'</td>
													<td width="32%"  >Total Attended Hours</td>
													<td width="13%" align="right" >'. number_format_value_checker($ATTENDANCE_HOURS, 2).'</td>
												</tr>
												<tr>
													<td width="32%" >Accumulative Attendance</td>
													<td width="13%" align="right" >'. number_format_value_checker((($ATTENDANCE_HOURS + $TRANSFER_HOURS) / $TOTAL_REQUIRED_HOURS) * 100, 2).'%</td>
													<td width="32%"  >Total Hours Remaining</td>
													<td width="13%" align="right" >'.number_format_value_checker($TOTAL_REQUIRED_HOURS - $ATTENDANCE_HOURS, 2).'%</td>
												</tr>
											</table>									
										</td>
									</tr>
								</table>
							</td>
							<td width="15%" ></td>
						</tr>
					</table>';*/

				$txt .= '<br /><br /><br /><br />
				<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					
					<tr nobr="true" >
						<td width="15%" ></td>
						<td width="70%" >
							<table border="0" cellspacing="0" cellpadding="3" width="100%" >
										<tr>
											<td width="100%" >
											<table border="0" cellspacing="0" cellpadding="5" width="100%" style="border: 1px solid #000000;" >
											<tr>
												<td width="32%" >Accumulative GPA</td>
												<td width="13%" align="right" >' . number_format_value_checker($per, 2) . '%</td>
												<td width="32%"  >Total Required Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_type->fields['HOURS'],2).'</td>
											</tr>
											<tr>
												<td width="32%" >Program Attendance</td>
												<td width="13%" align="right" >'. number_format_value_checker((($res_attended_all->fields['ATTENDED_HOUR'] + $res_trans->fields['HOUR']) / $res_type->fields['HOURS']) * 100, 2).'%</td>
												<td width="32%"  >Total Transfer Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_trans->fields['HOUR'], 2).'</td>
											</tr>
											<tr>
												<td width="32%" ></td>
												<td width="13%" align="right" ></td>
												<td width="32%"  >Total Scheduled Hours</td>
												<td width="13%" align="right" >'.number_format_value_checker($SCHEDULED_HOUR,2).'</td>
											</tr>
											<tr>
												<td width="32%" >Current Attendance</td>
												<td width="13%" align="right" >'.number_format_value_checker(($res_attended_all->fields['ATTENDED_HOUR'] / $SCHEDULED_HOUR) * 100 , 2).'%</td>
												<td width="32%"  >Total Attended Hours</td>
												<td width="13%" align="right" >'. number_format_value_checker($res_attended_all->fields['ATTENDED_HOUR'], 2).'</td>
											</tr>
											<tr>
												<td width="32%" ></td>
												<td width="13%" align="right" ></td>
												<td width="32%"  >Total Hours Remaining</td>
												<td width="13%" align="right" >'.number_format_value_checker($res_type->fields['HOURS'] - $res_attended_all->fields['ATTENDED_HOUR'], 2).'</td>
											</tr>										

										
										</table>									
									</td>
								</tr>
							</table>
						</td>
						<td width="15%" ></td>
					</tr>
				</table>';

					} //count endif 

				} // show/hide endif

				$txt .= '<br/><br/>';
				$res_acc = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 19");

				$txt .= '<table><tr><td style="white-space: pre-wrap;text-align: justify;">'.nl2br($res_acc->fields['CONTENT']).'</td></tr></table>';
	
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		} //ENROLL FOR END
		
	}//for end
	


	$file_name = $report_name.'.pdf';	
	if($one_stud_per_pdf == 0) {
		$file_dir_1 = 'temp/';
		$pdf->Output($file_dir_1.$file_name, 'FD');
	} else {
		$file_dir_1 = '../backend_assets/tmp_upload/';
		$file_name  = $res_stu->fields['LAST_NAME'].'_'.$res_stu->fields['FIRST_NAME'].'-'.$res_stu->fields['STUDENT_ID'].'-'.$report_name.'_'.$PK_STUDENT_MASTER.'.pdf';
		$pdf->Output($file_dir_1.$file_name, 'F');
	}

	return $file_name;	

}//function end

#################################### 

$report_name = "";
if($_GET['id'] != '') {
	$report_name = "program_grade_book_transcript";	
	program_grade_book_transcript($_GET['id'], 0, $report_name);	
}
