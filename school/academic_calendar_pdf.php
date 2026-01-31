<?php require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/academic_calendar.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('SETUP_SCHOOL') == 0){
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

if(!empty($_POST))
{ 
	function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $PK_SESSION) 
	{
		global $db, $campus_id;
		
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
								<td style="text-align:center;font-weight:bold;background-color:#2c4574;color:#fff;" width="100%" colspan="7" >'.$title.'</td>
							</tr>';

		if($day_name_length) {   
			//if the day names should be shown ($day_name_length > 0)
			//if day_name_length is >3, the full name of the day will be printed
			
			$calendar  .= "<tr>";
			foreach($day_names as $d)
				$calendar  .= '<th style="background-color:#7d9ace;color:#FFF;" >' . htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d) . '</th>';
			$calendar  .= "</tr><tr>";
		}

		if($weekday > 0) {
			for ($i = 0; $i < $weekday; $i++) {
				$calendar  .= '<td >&nbsp;</td>'; //initial 'empty' days
			}
		}
		for($day = 1, $days_in_month = gmdate('t',$first_of_month); $day <= $days_in_month; $day++, $weekday++) {
			//echo date("m/d/Y",$first_of_month);exit;
			
			$dd = date($year.'-'.$month.'-'.$day);
			
			$res = $db->Execute("select PK_ACADEMIC_CALENDAR_SESSION,COLOR,LEAVE_TYPE from M_ACADEMIC_CALENDAR,M_ACADEMIC_CALENDAR_CAMPUS,M_ACADEMIC_CALENDAR_SESSION LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = M_ACADEMIC_CALENDAR_SESSION.PK_SESSION WHERE M_ACADEMIC_CALENDAR_SESSION.ACTIVE = 1 AND M_ACADEMIC_CALENDAR_SESSION.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_ACADEMIC_CALENDAR_SESSION.PK_SESSION = '$PK_SESSION' AND ACADEMY_DATE = '$dd' AND M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_SESSION.PK_ACADEMIC_CALENDAR  AND M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_CAMPUS.PK_ACADEMIC_CALENDAR AND M_ACADEMIC_CALENDAR_CAMPUS.PK_CAMPUS IN ($campus_id)  ");
		
			if($res->RecordCount() > 0){
				if($res->fields['LEAVE_TYPE'] == 1)
					$color = '#f6c2bb';
				else
					$color = '#ec93bc';
					
				$style = "background-color:".$color.";color:#000000;";
			} else {
				$style = "background-color:#FFF;";
				if(date('N',strtotime($dd)) == 6 || date('N',strtotime($dd)) == 7)
					$style .= "color:#3a5d9c;";
			}
			
			if($weekday == 7) {
				$weekday   = 0; //start a new week
				$calendar  .= "</tr>\n<tr>";
			}
			if(isset($days[$day]) and is_array($days[$day])) {
				@list($link, $classes, $content) = $days[$day];
				if(is_null($content))  $content  = $day;
				$calendar  .= '<td style="'.$style.'" >'.$content.'</td>';
			} else 
				$calendar  .= '<td style="'.$style.'" >'.$day.'</td>';
		}
		if($weekday != 7) 
			$calendar  .= '<td id="emptydays"  colspan="' . (7-$weekday) . '">&nbsp;</td>'; //remaining "empty" days

		return $calendar . "</tr>\n</table>\n</div>\n";
	}
	
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

	class MYPDF extends TCPDF {
		public function Header() {
			global $db, $campus_name;
			
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
				$ext = explode(".",$res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}
			$this->SetFont('helvetica', '', 13);
			$this->SetY(4);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			$this->MultiCell(85, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
			
			
			$this->SetFont('helvetica', '', 8);
			$this->SetY(17);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(20);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['CITY'].' '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');//DIAM-1421
		
			$this->SetY(24);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			
			
			$txt = '';
			$date1 = explode("/",$_POST['START_DATE']);
			$date2 = explode("/",$_POST['END_DATE']);
			if($date1[1] == $date2[1])
				$txt = $date1[1];
			else
				$txt = $date1[1].' to '.$date2[1];
			
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(103);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(100, 8, "Academic Calendar - ".$txt, 0, false, 'R', 0, '', 0, false, 'M', 'L');

			$this->SetFillColor(0, 0, 0);
			$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
			$this->Line(130, 13, 202, 13, $style);
			
			$header_data = $this->getHeaderData();
			//echo $header_data['title'].'<br />';
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(17);
			$this->SetX(137);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(65, 8, $header_data['title'], 0, false, 'R', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', 'I', 10);
			$this->SetY(20);
			$this->SetX(50);
			$this->SetTextColor(000, 000, 000);
			$this->MultiCell(150, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
			
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
		}
	}

	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 28, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, 40);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 9, '', true);
	
	$date1 = explode("/",$_POST['START_DATE']);
	$date2 = explode("/",$_POST['END_DATE']);
	$start = new DateTime($date1[1].'-'.$date1[0].'-01');
	$start->modify('first day of this month');
	$end      = new DateTime($date2[1].'-'.$date2[0].'-01');
	$end->modify('first day of next month');
	$interval = DateInterval::createFromDateString('1 month');
	$period   = new DatePeriod($start, $interval, $end);

	// DIAM-2133
	$start_dt 		= ($date1[1].'-'.$date1[0]);
	$fianl_start_dt = date('Y-m',strtotime($start_dt));
	$end_dt   		= ($date2[1].'-'.$date2[0]);
	$fianl_end_dt 	= date('Y-m',strtotime($end_dt));
	// End DIAM-2133

	$res_type = $db->Execute("SELECT PK_SESSION,SESSION from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC");
	while (!$res_type->EOF) {
		//$pdf->CustomHeaderText = $res_type->fields['SESSION'];
		//echo $res_type->fields['SESSION'].'-------<br >';
		$pdf->setHeaderData('', 0, $res_type->fields['SESSION'], '');
		$pdf->AddPage();

		$time = time();
		$today = date('j', $time);
		$days = array($today => array(null, null,'<div id="today">' . $today . '</div>'));
		
		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
		
			$i = 1;
			foreach ($period as $dt) {

				if($i == 1)
					$txt .= '<tr nobr="true">';
						
					$txt .= '<td width="32%" style="background-color:#d4ddef;" >'.generate_calendar($dt->format("Y"), $dt->format("m"), $days, 1, null, 0,$res_type->fields['PK_SESSION']).'</td><td width="2%" style="background-color:#FFF;" ></td>';
				
				$i++;
				
				if($i == 4) {
					$i = 1;
					$txt .= '</tr>
							<tr>
								<td width="100%" ><br /></td>
							</tr>';
				}
			}
			if($i < 4 && $i != 1)
			{
				$txt .= '</tr>';
			}
			
		$txt .= '</table>';

		// DIAM-2133
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">';	
		// $txt .= '<tr><td colspan="2" width="100%" ><br /></td></tr>';

		$txt .= '<tr><td>Holiday</td><td>Breaks</td></tr>';
		$txt .= '<tr>';
		$PK_SESSION = $res_type->fields['PK_SESSION'];

		$sQuery = "SELECT 
						M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR, 
						M_ACADEMIC_CALENDAR.LEAVE_TYPE,
						M_ACADEMIC_CALENDAR.TITLE,
						M_ACADEMIC_CALENDAR.START_DATE,
						M_ACADEMIC_CALENDAR.END_DATE
					FROM 
						M_ACADEMIC_CALENDAR_SESSION 
						LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = M_ACADEMIC_CALENDAR_SESSION.PK_SESSION 
						LEFT JOIN M_ACADEMIC_CALENDAR ON M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_SESSION.PK_ACADEMIC_CALENDAR
						LEFT JOIN M_ACADEMIC_CALENDAR_CAMPUS ON M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_CAMPUS.PK_ACADEMIC_CALENDAR
					WHERE 
						M_ACADEMIC_CALENDAR_SESSION.ACTIVE = 1 
						AND M_ACADEMIC_CALENDAR.ACTIVE = 1
						AND M_ACADEMIC_CALENDAR.LEAVE_TYPE IN (1,2)
						AND M_ACADEMIC_CALENDAR_SESSION.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
						AND M_ACADEMIC_CALENDAR_SESSION.PK_SESSION = '$PK_SESSION' 
						AND M_ACADEMIC_CALENDAR_CAMPUS.PK_CAMPUS IN ($campus_id)
						AND DATE_FORMAT(M_ACADEMIC_CALENDAR.START_DATE, '%Y-%m') >= '$fianl_start_dt'
						AND DATE_FORMAT(M_ACADEMIC_CALENDAR.END_DATE, '%Y-%m') <= '$fianl_end_dt' 
					GROUP BY 
						M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR, M_ACADEMIC_CALENDAR.LEAVE_TYPE 
					ORDER BY 
						M_ACADEMIC_CALENDAR.START_DATE ";
		// echo $sQuery."<br>";
		$res_hol_break = $db->Execute($sQuery);
		$holid_tr = $break_tr = [];
		while (!$res_hol_break->EOF) 
		{
			if($res_hol_break->fields['START_DATE'] != '0000-00-00')
			{
				$hol_break_start_date = date("m/d/Y",strtotime($res_hol_break->fields['START_DATE']));
			}
			else
			{
				$hol_break_start_date = '';
			}

			if($res_hol_break->fields['END_DATE'] != '0000-00-00')
			{
				$hol_break_end_date = date("m/d/Y",strtotime($res_hol_break->fields['END_DATE']));
			}
			else
			{
				$hol_break_end_date = '';
			}

			$LEAVE_TYPE = $res_hol_break->fields['LEAVE_TYPE'];
			$txt_l = $txt_r = "";
			if($LEAVE_TYPE == 1)
			{
				$txt_l = $hol_break_start_date.' - '.$res_hol_break->fields['TITLE'];
				$holid_tr[] = $txt_l;
			}
			if($LEAVE_TYPE == 2)
			{
				$txt_r = $res_hol_break->fields['TITLE'].' ('.$hol_break_start_date.' - '.$hol_break_end_date.')';
				$break_tr[] = $txt_r;
				$k++;
			}

			$res_hol_break->MoveNext();
			$cnt++;
			
		}

		$txt .= '<td style="font-size:27px;"><table border="0" cellspacing="0" cellpadding="0" width="100%">';
		foreach($holid_tr as $hold_val)
		{
			$txt .= '<tr>
						<td>'.$hold_val.'</td>
					</tr>';
		}
		$txt .= '</table></td>';
		
		$txt .= '<td style="font-size:27px;"><table border="0" cellspacing="0" cellpadding="0" width="100%">';
		$k = 1;
		foreach($break_tr as $break_val)
		{
			$txt .= '<tr>
						<td>'.$k.'. '.$break_val.'</td>
					</tr>';
			$k++;
		}
		$txt .= '</table></td>';
		$txt .= '</tr>';
		$txt .= '</table>';
		// End DIAM-2133

		//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
		$res_type->MoveNext();
	}

	$file_name = 'Calendar_'.uniqid().'.pdf';
		
	$pdf->Output('temp/'.$file_name, 'FD');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=ACADEMIC_CALENDAR_PAGE_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 30px;width: 140px}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=ACADEMIC_CALENDAR_PAGE_TITLE ?>
						</h4>
                    </div>
                </div>
				
                <div class="row">
					<div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									 <div class="col-12">
										<div class="card">
											<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" >
												<div class="p-20">
													<div class="d-flex">
														<div class="col-12 col-sm-3 form-group">
															<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
																<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
														<div class="col-12 col-sm-3 form-group">
															<input id="START_DATE" name="START_DATE" type="text" class="form-control required-entry" value="<?=$START_DATE?>">
															<span class="bar"></span> 
															<label for="START_DATE"><?=START_DATE?></label>
														</div>
														<div class="col-12 col-sm-3 form-group">
															<input id="END_DATE" name="END_DATE" type="text" class="form-control required-entry" value="<?=$END_DATE?>">
															<span class="bar"></span> 
															<label for="END_DATE"><?=END_DATE?></label>
														</div>
														<div class="col-12 col-sm-3 form-group">
															<button type="submit" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
	</div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#START_DATE").datepicker( {
				format: "mm/yyyy",
				startView: "months", 
				minViewMode: "months",
				orientation: "bottom auto"
			});
			
			$("#END_DATE").datepicker( {
				format: "mm/yyyy",
				startView: "months", 
				minViewMode: "months",
				orientation: "bottom auto"
			});
		});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>
</body>

</html>
