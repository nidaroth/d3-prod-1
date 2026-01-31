<?php 
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}



$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
	
ini_set('max_execution_time', 6000); // 6000 Seconds

$_GET['group_by']=$_POST['GROUP_BY'];
$_GET['eid']=implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
$_GET['campus']=implode(",",$_POST['PK_CAMPUS']);
	
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
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
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',WEBSITE) as WEBSITE,HIDE_ACCOUNT_ADDRESS_ON_REPORTS FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //DIAM-1421
		
		if($res->fields['PDF_LOGO'] != '') {
			//echo $res->fields['SCHOOL_NAME'];exit;
			if ($res->fields['SCHOOL_NAME']=='First Institute') {
				$width = 8;
			}
			else{
				$width = 18;
			}
			$ext = explode(".",$res->fields['PDF_LOGO']);
			$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, $width, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
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
		$this->Cell(55, 8,$res->fields['CITY'].' '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L'); //DIAM-1421
		
		$this->SetY(21);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(147);
		$this->Cell(55, 8, "Last Day In Class", 0, false, 'L', 0, '', 0, false, 'M', 'L');

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
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(8, 279, 202, 279, $style);
		
		$campus_name  = "";
		$campus_cond  = "";
		$campus_cond1 = "";
		$campus_id	  = "";
		if(!empty($_POST['PK_CAMPUS'])){
			$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
			$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}
		
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
		
		$this->SetFont('helvetica', 'I', 9);
		$this->SetY(13);
		$this->SetX(112);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(90, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
		
		$group_by = "";
		if($_GET['group_by'] == 1)
			$group_by = "Program";
		else if($_GET['group_by'] == 2)
			$group_by = "Status";
		else if($_GET['group_by'] == 3)
			$group_by = "First Term";
		else if($_GET['group_by'] == 4)
			$group_by = "Student - Sort Only";
		else if($_GET['group_by'] == 5)
			$group_by = "Days Ago";
			
		$this->SetY(18);
		$this->SetX(112);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(90, 5, "Group By: ".$group_by, 0, 'R', 0, 0, '', '', true);
				
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 18);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);
$pdf->AddPage();

$table 		= "";
$cond 		= "";
$FIELD 		= "";
$ID			= array();
$ID_NAME	= array();
if($_GET['group_by'] == 1){
	$FIELD = " S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM ";
	
	$res = $db->Execute("select S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM ORDER BY M_CAMPUS_PROGRAM.CODE ASC");

	while (!$res->EOF) {
		$ID[] 		= $res->fields['PK_CAMPUS_PROGRAM'];
		$ID_NAME[] 	= "Program: ".$res->fields['PROGRAM_CODE'];
		$res->MoveNext();
	}
} else if($_GET['group_by'] == 2){
	$FIELD = " S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS ";
	
	$res = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS, STUDENT_STATUS FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS ORDER BY STUDENT_STATUS ASC ");

	while (!$res->EOF) {
		$ID[] 		= $res->fields['PK_STUDENT_STATUS'];
		$ID_NAME[] 	= "Status: ".$res->fields['STUDENT_STATUS'];
		$res->MoveNext();
	}
} else if($_GET['group_by'] == 3){
	$FIELD = " S_STUDENT_ENROLLMENT.PK_TERM_MASTER ";
	
	$res = $db->Execute("select S_STUDENT_ENROLLMENT.PK_TERM_MASTER, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE_1 FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_ENROLLMENT.PK_TERM_MASTER ORDER BY S_TERM_MASTER.BEGIN_DATE ");

	while (!$res->EOF) {
		$ID[] 		= $res->fields['PK_TERM_MASTER'];
		$ID_NAME[] 	= "First Term: ".$res->fields['BEGIN_DATE_1'];
		$res->MoveNext();
	}
} else if($_GET['group_by'] == 4){
	$FIELD 		= " 1 ";
	$ID[] 		= 1;
	$ID_NAME[] 	= '';

} else if($_GET['group_by'] == 5){
	//$res = $db->Execute("select MAX(SCHEDULE_DATE) as SCHEDULE_DATE FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_CODE = 14 AND S_STUDENT_ATTENDANCE.COMPLETED = 1 GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT ORDER BY SCHEDULE_DATE ASC ");

	/* DIAM-621, query changes as suggested by Barre */
	$res = $db->Execute("SELECT MAX(S_STUDENT_SCHEDULE.SCHEDULE_DATE) as SCHEDULE_DATE FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE, S_ATTENDANCE_CODE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.PRESENT = 1 AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND S_ATTENDANCE_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) GROUP BY S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT ORDER BY SCHEDULE_DATE ASC ");
	/* End DIAM-621 */

	while (!$res->EOF) {
		$date1 = new DateTime($res->fields['SCHEDULE_DATE']);
		$date2 = new DateTime(date("Y-m-d"));
		$interval = $date1->diff($date2);

		$SCHEDULE_DATE 	= date("m/d/Y",strtotime($SCHEDULE_DATE));
		$days_ago 		= $interval->format('%a');
		if($interval->format('%R') == "-")
			$days_ago = "-".$days_ago;
		
		$flag = 1;
		foreach($ID as $ID1){
			if($ID1 == $res->fields['SCHEDULE_DATE']) {
				$flag = 0;
				break;
			}
		}
		if($flag == 1) {
			$ID[] 		= $res->fields['SCHEDULE_DATE'];
			$ID_NAME[] 	= "Days Ago: ".$days_ago;
		}
		
		$res->MoveNext();
	}

	$group_by   = "GROUP BY STUDENT_ID";
	$FIELD 		= " SCHEDULE_DATE ";
	$table 		= ",S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE, S_ATTENDANCE_CODE"; // DIAM-621, Add new table S_ATTENDANCE_CODE
	//$cond 		= " AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND PK_ATTENDANCE_CODE = 14 AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$cond 		= " AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE = S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.PRESENT = 1 AND S_ATTENDANCE_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ATTENDANCE.COMPLETED = 1 AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT "; // DIAM-621, as changes suggested by Barre

}

$res_present_att_code = $db->Execute("select GROUP_CONCAT(M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE) as PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
$present_att_code = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];

$k = 0;
$txt = '';

foreach($ID as $schedule_date1){

	$i = 1;
	$j = 0;
	$days_array=array(); // DIAM - 57
	
	if($k > 0)
	{
		//$txt .= "<br><br><br>";
		
	}
		
	$res_att = $db->Execute("select CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_ID, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT FROM 
	S_STUDENT_MASTER
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	$table 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) $cond AND $FIELD = '$schedule_date1' $group_by ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC");

	while (!$res_att->EOF) {
		$PK_STUDENT_ENROLLMENT = $res_att->fields['PK_STUDENT_ENROLLMENT'];

		$res_sch = $db->Execute("select MAX(SCHEDULE_DATE) as SCHEDULE_DATE FROM S_STUDENT_SCHEDULE, S_STUDENT_ATTENDANCE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_CODE in ($present_att_code) AND S_STUDENT_ATTENDANCE.COMPLETED = 1");
		if($_GET['group_by'] == 5) // DIAM - 57
		{

			if($i == 1)
			{
				if($schedule_date1 == $res_sch->fields['SCHEDULE_DATE'])
				{
					
			
				}
				
			}
		} // End DIAM - 57
		else
		{
			if($i == 1)
			{

				$txt .= '<br><br><br><table border="0" cellspacing="0" cellpadding="2" width="100%">
							<thead style="border-bottom:1px solid #000;">
								<tr>
									<td><span style="font-size:45px" >'.$ID_NAME[$k].'</span></td>
								</tr>
								<tr>
									<td width="20%" style="border-bottom:0.5px solid #000;">
										<br /><br /><b>Student</b>
									</td>
									<td width="10%" style="border-bottom:0.5px solid #000;">
										<br /><br /><b>ID</b>
									</td>
									<td width="15%" style="border-bottom:0.5px solid #000;">
										<br /><br /><b>Student Status</b>
									</td>
									<td width="15%" style="border-bottom:0.5px solid #000;">
										<br /><br /><b>Student Program</b>
									</td>
									<td width="10%" style="border-bottom:0.5px solid #000;">
										<b>Student<br />First Term</b>
									</td>
									<td width="10%" style="border-bottom:0.5px solid #000;">
										<b>Last Date<br />In Class</b>
									</td>
									<td width="10%" style="border-bottom:0.5px solid #000;">
										<b>Next Course Start</b>
									</td>
									<td width="10%" style="border-bottom:0.5px solid #000;" align="right" >
										<br /><br /><b>Days Ago</b>
									</td>
								</tr>
							</thead>
						<tbody>';
				
			}
		}
		
		$SCHEDULE_DATE = $res_sch->fields['SCHEDULE_DATE'];
		if($SCHEDULE_DATE != '' && $SCHEDULE_DATE != '0000-00-00') {
			$date1 = new DateTime($SCHEDULE_DATE);
			$date2 = new DateTime(date("Y-m-d"));
			$interval = $date1->diff($date2);

			$SCHEDULE_DATE 	= date("m/d/Y",strtotime($SCHEDULE_DATE));
			$days_ago 		= $interval->format('%a');
			if($interval->format('%R') == "-")
				$days_ago = "-".$days_ago;
		} else {
			$SCHEDULE_DATE 	= '';
			$days_ago 		= '';
		}
		
		$flag = 1;
		if($_GET['group_by'] == 5) {
			if($schedule_date1 != $res_sch->fields['SCHEDULE_DATE']){
				$flag = 0;
			}
			else{
				$j++;
			}
		} else{
			$j++;		
		}
		
		if($_GET['group_by'] == 5) { // DIAM - 57
			/*if ($ID_NAME[$k] == 'Days Ago: 3416') {
				echo $schedule_date1 .'=='. $res_sch->fields['SCHEDULE_DATE'];
				exit;
				// code...
			}*/
			if($schedule_date1 == $res_sch->fields['SCHEDULE_DATE']) {

				if($j==1){

					$txt .= '<br><br><br><table border="0" cellspacing="0" cellpadding="2" width="100%">
						<thead style="border-bottom:1px solid #000;">
							<tr>
								<td><span style="font-size:45px" >'.$ID_NAME[$k].'</span></td>
							</tr>
							<tr>
								<td width="20%" style="border-bottom:0.5px solid #000;">
									<br /><br /><b>Student</b>
								</td>
								<td width="10%" style="border-bottom:0.5px solid #000;">
									<br /><br /><b>ID</b>
								</td>
								<td width="15%" style="border-bottom:0.5px solid #000;">
									<br /><br /><b>Student Status</b>
								</td>
								<td width="15%" style="border-bottom:0.5px solid #000;">
									<br /><br /><b>Student Program</b>
								</td>
								<td width="10%" style="border-bottom:0.5px solid #000;">
									<b>Student<br />First Term</b>
								</td>
								<td width="10%" style="border-bottom:0.5px solid #000;">
									<b>Last Date<br />In Class</b>
								</td>
								<td width="10%" style="border-bottom:0.5px solid #000;">
									<b>Next Course Start</b>
								</td>
								<td width="10%" style="border-bottom:0.5px solid #000;" align="right" >
									<br /><br /><b>Days Ago</b>
								</td>
							</tr>
						</thead>
					<tbody>';
				}

				array_push($days_array,$j);

				/* Ticket #1185 */
				$today 					= date("Y-m-d");
				$NEXT_COURSE_ST_DATE 	= '';
				$res_next_term = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_COURSE from S_STUDENT_COURSE, S_COURSE_OFFERING, S_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER AND BEGIN_DATE >= '$today' ORDER BY BEGIN_DATE ASC ");
				if($res_next_term->RecordCount() > 0) {
					$PK_STUDENT_COURSE11 = $res_next_term->fields['PK_STUDENT_COURSE'];
					$res_next_sch = $db->Execute("select SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE11' AND SCHEDULE_DATE != '0000-00-00' ORDER BY SCHEDULE_DATE ASC ");
					if($res_next_sch->RecordCount() > 0) {
						$NEXT_COURSE_ST_DATE = date("m/d/Y",strtotime($res_next_sch->fields['SCHEDULE_DATE']));
					}
				}
				/* Ticket #1185 */
				
				$txt .= '<tr nobr="true" >
							<td width="3%" >
								'.$j.'
							</td>
							<td width="17%" >
								'.$res_att->fields['STU_NAME'].'
							</td>
							<td width="10%" >
								'.$res_att->fields['STUDENT_ID'].'
							</td>
							<td width="15%" >
								'.$res_att->fields['STUDENT_STATUS'].'
							</td>
							<td width="15%"  >
								'.$res_att->fields['PROGRAM_CODE'].'
							</td>
							<td width="10%" >
								'.$res_att->fields['BEGIN_DATE'].'
							</td>
							<td width="10%" >
								'.$SCHEDULE_DATE.'
							</td>
							<td width="10%" >
								'.$NEXT_COURSE_ST_DATE.'
							</td>
							<td width="10%" align="right" >
								'.$days_ago.'
							</td>
						</tr>';
				
			}
			if($res_att->RecordCount() == $i)
			{
					$txt .= '</tbody>
						</table>';
			}
			

		} //  End DIAM - 57
		else{

			if($flag == 1) {
				/* Ticket #1185 */
				$today 					= date("Y-m-d");
				$NEXT_COURSE_ST_DATE 	= '';
				$res_next_term = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_COURSE from S_STUDENT_COURSE, S_COURSE_OFFERING, S_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER AND BEGIN_DATE >= '$today' ORDER BY BEGIN_DATE ASC ");
				if($res_next_term->RecordCount() > 0) {
					$PK_STUDENT_COURSE11 = $res_next_term->fields['PK_STUDENT_COURSE'];
					$res_next_sch = $db->Execute("select SCHEDULE_DATE FROM S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND  S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE11' AND SCHEDULE_DATE != '0000-00-00' ORDER BY SCHEDULE_DATE ASC ");
					if($res_next_sch->RecordCount() > 0) {
						$NEXT_COURSE_ST_DATE = date("m/d/Y",strtotime($res_next_sch->fields['SCHEDULE_DATE']));
					}
				}
				/* Ticket #1185 */
				$txt .= '<tr nobr="true" >
							<td width="3%" >
								'.$j.'
							</td>
							<td width="17%" >
								'.$res_att->fields['STU_NAME'].'
							</td>
							<td width="10%" >
								'.$res_att->fields['STUDENT_ID'].'
							</td>
							<td width="15%" >
								'.$res_att->fields['STUDENT_STATUS'].'
							</td>
							<td width="15%"  >
								'.$res_att->fields['PROGRAM_CODE'].'
							</td>
							<td width="10%" >
								'.$res_att->fields['BEGIN_DATE'].'
							</td>
							<td width="10%" >
								'.$SCHEDULE_DATE.'
							</td>
							<td width="10%" >
								'.$NEXT_COURSE_ST_DATE.'
							</td>
							<td width="10%" align="right" >
								'.$days_ago.'
							</td>
						</tr>';			
			
			}
			if($res_att->RecordCount() == $i) {
				$txt .= '</tbody>
					</table>';
			}
			

		}

		$i++;
		$res_att->MoveNext();
	}
		
	$k++;
}
	
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


$file_name = 'Last Day In Class_'.uniqid().'.pdf';
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
*/
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
