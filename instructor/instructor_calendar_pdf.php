<?php require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/instructor_calendar.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
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
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0) {
	global $db;
	
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

	$start 	= date("Y-m-d",strtotime($_GET['sd']));
	$end 	= date("Y-m-d",strtotime($_GET['ed']));

    for($day = 1, $days_in_month = gmdate('t',$first_of_month); $day <= $days_in_month; $day++, $weekday++) {
		//echo date("m/d/Y",$first_of_month);exit;
		
		$dd = date($year.'-'.$month.'-'.$day);
		
		$res_sch = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE1, START_TIME, END_TIME, S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS, ROOM_NO, COURSE_CODE, ROOM_DESCRIPTION, SESSION, SESSION_NO, IF(BEGIN_DATE != '0000-00-00', DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y'),'') AS BEGIN_DATE from 

		S_COURSE_OFFERING 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
		LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
		LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
		LEFT JOIN M_SESSION On M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
		, S_COURSE_OFFERING_SCHEDULE_DETAIL 
		LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_CAMPUS_ROOM 

		WHERE 
		S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		(INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND
		S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND SCHEDULE_DATE = '$dd' AND SCHEDULE_DATE BETWEEN '$start' AND '$end' ");
				
		$schedule = '';
		while (!$res_sch->EOF) {
			//$schedule .= '<br />';
			$START_TIME = '';
			$END_TIME 	= '';
			
			if($res_sch->fields['START_TIME'] != '00:00::')
				$START_TIME = date("h:i A",strtotime($res_sch->fields['START_TIME']));
				
			if($res_sch->fields['END_TIME'] != '00:00::')
				$END_TIME = date("h:i A",strtotime($res_sch->fields['END_TIME']));
			
			$schedule .= '<div style="color:#7d9ace;font-size:25px;color:#FFF;background-color:#3A87AD;" >
							&nbsp;'.$START_TIME.' - '.$END_TIME.' ('.$res_sch->fields['ROOM_NO'].')<br />'.$res_sch->fields['BEGIN_DATE'].' - '.$res_sch->fields['COURSE_CODE'].' ('.substr($res_sch->fields['SESSION'],0,1).' - '.$res_sch->fields['SESSION_NO'].')
						&nbsp;</div>';
	
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
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".",$res->fields['PDF_LOGO']);
			$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 20, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 14);
		$this->SetY(2);
		$this->SetX(45);
		$this->SetTextColor(000, 000, 000);
		$this->MultiCell(180, 3, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
		//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', '', 8);
		$this->SetY(13);
		$this->SetX(45);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(17);
		$this->SetX(45);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8,$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetY(21);
		$this->SetX(45);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFont('helvetica', 'I', 15);
		$this->SetY(8);
		$this->SetX(190);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(100, 8, "Instructor Schedule", 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(190, 11, 290, 11, $style);
		
		$res_emp = $db->Execute("SELECT FIRST_NAME, LAST_NAME FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
		$this->SetFont('helvetica', 'I', 13);
		$this->SetY(14);
		$this->SetX(190);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(100, 8, $res_emp->fields['LAST_NAME'].', '.$res_emp->fields['FIRST_NAME'], 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 13);
		$this->SetY(20);
		$this->SetX(190);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(100, 8, "Between: ".$_GET['sd'].' - '.$_GET['ed'], 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$header_data = $this->getHeaderData();
		//echo $header_data['title'].'<br />';
		$this->SetFont('helvetica', 'I', 15);
		$this->SetY(17);
		$this->SetX(190);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, $header_data['title'], 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
	}
	public function Footer() {
		$this->SetY(-15);
		$this->SetX(190);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(100, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(100, 10, date('l, F d, Y'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
	}
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 40);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9, '', true);

$start = new DateTime(date("Y-m-d",strtotime($_GET['sd'])));
$start->modify('first day of this month');
$end      = new DateTime(date("Y-m-d",strtotime($_GET['ed'])));
$end->modify('first day of next month');
$interval = DateInterval::createFromDateString('1 month');
$period   = new DatePeriod($start, $interval, $end);


//$pdf->CustomHeaderText = $res_type->fields['SESSION'];
//echo $res_type->fields['SESSION'].'-------<br >';
$pdf->setHeaderData('', 0, $res_type->fields['SESSION'], '');
$pdf->AddPage();

$time = time();
$today = date('j', $time);
$days = array($today => array(null, null,'<div id="today">' . $today . '</div>'));

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">';

foreach ($period as $dt) {
	$txt .= '<tr nobr="true">';
	$txt .= '<td style="background-color:#d4ddef;" >'.generate_calendar($dt->format("Y"), $dt->format("m"), $days, 1, null, 0).'</td>';
	$txt .= '</tr>';
}

$txt .= '</table>';
//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
//exit;
$file_name = 'Calendar.pdf';

$pdf->Output('../school/temp/'.$file_name, 'FD');