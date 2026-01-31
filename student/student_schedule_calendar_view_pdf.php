<?php session_start();
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');

require_once("../school/pdf_custom_header.php"); //Ticket # 1588
function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0) {
	global $db;
	
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
	
    $first_of_month = gmmktime(0, 0, 0, $month, 1, $year);

    $day_names = array(); //generate all the day names according to the current locale
    for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t+=86400) //January 4, 1970 was a Sunday
        $day_names[$n] = ucfirst(gmstrftime('%A', $t)); //%A means full textual day name

    list($month, $year, $month_name, $weekday) = explode(',', gmstrftime('%m, %Y, %B, %w', $first_of_month));
    $weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
    $title   = htmlentities(ucfirst($month_name)) . $year;  //note that some locales don't capitalize month and day names

   $calendar = '<div>
					<table border="0" cellspacing="0" cellpadding="3" width="100%" >
						<tr>
							<td style="text-align:center;font-weight:bold;background-color:#2c4574;color:#fff;" width="100%" colspan="7" ><center>'.$title.'</center></td>
						</tr>';

    if($day_name_length) {   
		//if the day names should be shown ($day_name_length > 0)
        //if day_name_length is >3, the full name of the day will be printed
		
		$calendar  .= "<tr>";
        foreach($day_names as $d)
            $calendar  .= '<th style="text-align:center;background-color:#E9ECEF;color:#000;" >' . htmlentities($d) . '</th>';
        $calendar  .= "</tr><tr>";
    }

    if($weekday > 0) {
        for ($i = 0; $i < $weekday; $i++) {
             $calendar  .= '<td style="background-color:#FFF;border:0.5px solid #d3d3d3;" >&nbsp;</td>'; //initial 'empty' days
        }
    }
    for($day = 1, $days_in_month = gmdate('t',$first_of_month); $day <= $days_in_month; $day++, $weekday++) {
		//echo date("m/d/Y",$first_of_month);exit;
		
		$dd = date($year.'-'.$month.'-'.$day);
		
		$res_sch = $db->Execute("select PK_STUDENT_SCHEDULE, IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, START_TIME, END_TIME, S_STUDENT_SCHEDULE.HOURS, ROOM_NO, COURSE_CODE, ROOM_DESCRIPTION, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME from 

		S_STUDENT_SCHEDULE 
		LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
		LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING
		LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR
		LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_STUDENT_SCHEDULE.PK_CAMPUS_ROOM 
		LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 

		WHERE 
		S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND 
		S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
		S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SCHEDULE_DATE = '$dd' ");
				
		$schedule = '';
		while (!$res_sch->EOF) {
			//$schedule .= '<br />';
			
			$schedule .= '<div style="color:#7d9ace;font-size:25px;color:#FFF;background-color:#3A87AD;" >
							&nbsp;'.$res_sch->fields['COURSE_CODE'].'['.date("h:i A",strtotime($res_sch->fields['START_TIME'])).' - '.date("h:i A",strtotime($res_sch->fields['END_TIME'])).'] [Room # '.$res_sch->fields['ROOM_NO'].'] [Inst: '.$res_sch->fields['NAME'].
						']&nbsp;</div>';
			$res_sch->MoveNext();
		}
		
		$style = "background-color:#FFF;height:80px";
		if(date('N',strtotime($dd)) == 6 || date('N',strtotime($dd)) == 7)
			$style .= "color:#3a5d9c;";
	
        if($weekday == 7) {
            $weekday   = 0; //start a new week
            $calendar  .= "</tr>\n<tr>";
        }
       
	   $calendar  .= '<td style="border:0.5px solid #d3d3d3;'.$style.'" >'.$day.' '.$schedule.'</td>';
    }
    if($weekday != 7) {
		for($in1 = 7-$weekday ; $in1 > 0 ; $in1--)
			$calendar  .= '<td id="emptydays" style="background-color:#FFF;border:0.5px solid #d3d3d3;" >&nbsp;</td>'; //remaining "empty" days
	}
    return $calendar . "</tr>\n</table>\n</div>\n";
}

class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		$CONTENT = pdf_custom_header($_SESSION['PK_STUDENT_MASTER'], '', 1);
		$this->SetY(3);
		$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
		$this->SetMargins('', 42, '');
		
		$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
		$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
		
		$res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,OTHER_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		$IMAGE					= $res->fields['IMAGE'];
		$FIRST_NAME 			= $res->fields['FIRST_NAME'];
		$LAST_NAME 				= $res->fields['LAST_NAME'];
		$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
		$OTHER_NAME	 			= $res->fields['OTHER_NAME'];
		
		$res = $db->Execute("SELECT STATUS_DATE,STUDENT_STATUS,CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
		$STATUS_DATE 	 = $res->fields['STATUS_DATE'];
		$STUDENT_STATUS	 = $res->fields['STUDENT_STATUS'];
		$CAMPUS_PROGRAM  = $res->fields['CODE'];
		$FIRST_TERM_DATE = $res->fields['BEGIN_DATE_1'];

		if($STATUS_DATE != '0000-00-00')
			$STATUS_DATE = date("m/d/Y",strtotime($STATUS_DATE));
		else
			$STATUS_DATE = '';
		
		$this->SetFont('helvetica', 'I', 16);
		$this->SetY(10);
		$this->SetX(220);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, $FIRST_NAME.' '.$MIDDLE_NAME.' '.$LAST_NAME, 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(180, 13, 285, 13, $style);
		
		$this->SetFont('helvetica', 'I', 14);
		$this->SetY(17);
		$this->SetX(220);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, $CAMPUS_PROGRAM.' - '.$FIRST_TERM_DATE, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		/*$this->SetFont('helvetica', 'I', 15);
		$this->SetY(17);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, $this->CustomHeaderText, 0, false, 'R', 0, '', 0, false, 'M', 'L');*/
		
    }
    public function Footer() {
		$this->SetY(-15);
		$this->SetX(270);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, date('l, F d, Y'), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 20, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 8, '', true);

$start = new DateTime(date("Y-m-d",strtotime($_GET['sd'])));
$start->modify('first day of this month');
$end      = new DateTime(date("Y-m-d",strtotime($_GET['ed'])));
$end->modify('first day of next month');
$interval = DateInterval::createFromDateString('1 month');
$period   = new DatePeriod($start, $interval, $end);

/////////////////////
$time = time();
$today = date('j', $time);
$days = array($today => array(null, null,'<div id="today">' . $today . '</div>'));

foreach ($period as $dt) {
	$pdf->AddPage();

	$time = time();
	$today = date('j', $time);
	$days = array($today => array(null, null,'<div id="today">' . $today . '</div>'));
	$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td width="100%" style="background-color:#d4ddef;" >'.generate_calendar($dt->format("Y"), $dt->format("m"), $days, 1, null, 0).'</td>
				</tr>
			</table>';
	//echo $txt;exit;
	$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}

$file_name = 'Calendar.pdf';
if($browser == 'Safari')
	$pdf->Output('../school/temp/'.$file_name, 'FD');
else	
	$pdf->Output('../school/temp/'.$file_name, 'FI');
	
//$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	

?>