<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if (check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

	if ($_POST['FORMAT'] == 1) {
		/////////////////////////////////////////////////////////////////
		$browser = '';
		if (stripos($_SERVER['HTTP_USER_AGENT'], "chrome") != false)
			$browser =  "chrome";
		else if (stripos($_SERVER['HTTP_USER_AGENT'], "Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');


		class MYPDF extends TCPDF
		{
			public function Header()
			{
				global $db;

				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

				if ($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".", $res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}

				$this->SetFont('helvetica', '', 15);
				$this->SetY(6);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(75, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);

				$this->SetFont('helvetica', 'I', 14);
				$this->SetY(10);
				$this->SetX(160);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Instructor Schedule", 0, false, 'L', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
			}
			public function Footer()
			{
				global $db;
				$this->SetY(-15);
				$this->SetX(180);
				$this->SetFont('helvetica', 'I', 7);
				$this->Cell(30, 10, 'Page ' . $this->getPageNumGroupAlias() . ' of ' . $this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

				$this->SetY(-15);
				$this->SetX(10);
				$this->SetFont('helvetica', 'I', 7);

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

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(3, 31, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);

		$res_term = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_POST[PK_TERM_MASTER]' ");
		foreach ($_POST['INSTRUCTOR'] as $INSTRUCTOR) {
			$res_name = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$INSTRUCTOR' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

			$pdf->startPageGroup();
			$pdf->AddPage();

			$total_units = 0;
			$txt = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
						<thead>
							<tr>
								<td align="left" width="50%" ><b style="font-size:40px">' . $res_name->fields['NAME'] . '</b></td>
								<td align="right" width="50%" ><b style="font-size:40px">Term ' . $res_term->fields['BEGIN_DATE_1'] . '</b></td>
							</tr>
							<tr>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Term</b></td>
								<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Course</b></td>
								<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Description</b></td>
								
								<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Room</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Students</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Hours</b></td>
								<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Units</b></td>
								<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Class Date</b></td>
								<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ></td>
								<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Class Time</b></td>
							</tr>
						</thead>';

			$res_cs = $db->Execute("SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING, TRANSCRIPT_CODE, COURSE_DESCRIPTION, SESSION, SESSION_NO, S_COURSE.HOURS, S_COURSE.UNITS      
			FROM
			S_COURSE, S_COURSE_OFFERING 
			LEFT JOIN M_SESSION On M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
			LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING
			WHERE 
			S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
			S_COURSE_OFFERING.PK_TERM_MASTER = '$_POST[PK_TERM_MASTER]' AND INSTRUCTOR IN ($INSTRUCTOR)
			GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER By TRANSCRIPT_CODE");

			while (!$res_cs->EOF) {
				$PK_COURSE_OFFERING = $res_cs->fields['PK_COURSE_OFFERING'];

				$CLASS_METTINGS_A = array();
				$PK_CAMPUS_ROOM_A = array();
				$TIME_A 		  = array();
				$DAYS1_A 		  = array();

				// $res_build = $db->Execute("select SCHEDULE_DATE,PK_CAMPUS_ROOM,START_TIME,END_TIME from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_CAMPUS_ROOM,START_TIME,END_TIME ORDER BY SCHEDULE_DATE ASC, START_TIME ASC");

				$partion_5_6_mysql_way  = "SELECT SCHEDULE_DATE,PK_CAMPUS_ROOM,START_TIME,END_TIME FROM (
					SELECT *, IF(@prev <> PK_CAMPUS_ROOM + START_TIME + END_TIME, @rn:=0,@rn), @prev:= PK_CAMPUS_ROOM + START_TIME + END_TIME, @rn:=@rn+1 AS rn
					FROM S_COURSE_OFFERING_SCHEDULE_DETAIL, (SELECT @rn:=0)rn, (SELECT @prev:='')prev  WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
					ORDER BY PK_CAMPUS_ROOM ASC, START_TIME ASC , END_TIME ASC , SCHEDULE_DATE ASC
				)t WHERE rn = 1 ORDER BY SCHEDULE_DATE ASC, START_TIME ASC;";
				$res_build = $db->Execute($partion_5_6_mysql_way);
				while (!$res_build->EOF) {
					$SCHEDULE_DATE 	= $res_build->fields['SCHEDULE_DATE'];
					$PK_CAMPUS_ROOM = $res_build->fields['PK_CAMPUS_ROOM'];
					$START_TIME 	= $res_build->fields['START_TIME'];
					$END_TIME 		= $res_build->fields['END_TIME'];

					$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME'  ORDER BY SCHEDULE_DATE DESC ");
					$SCHEDULE_DATE1 = $res_build1->fields['SCHEDULE_DATE'];

					$CLASS_METTINGS_A[] = date("m/d/Y", strtotime($SCHEDULE_DATE)) . ' to ' . date("m/d/Y", strtotime($SCHEDULE_DATE1));
					$PK_CAMPUS_ROOM_A[] = $PK_CAMPUS_ROOM;
					$TIME_A[]			= date("h:i A", strtotime($START_TIME)) . ' to ' . date("h:i A", strtotime($END_TIME));

					$dates = array();

					$res_build1 = $db->Execute("select SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND START_TIME = '$START_TIME' AND END_TIME = '$END_TIME' AND SCHEDULE_DATE BETWEEN '$SCHEDULE_DATE' AND  '$SCHEDULE_DATE1' ORDER BY SCHEDULE_DATE ASC ");
					while (!$res_build1->EOF) {
						$dates[] = $res_build1->fields['SCHEDULE_DATE'];
						$res_build1->MoveNext();
					}

					$DAYS_A = array();
					foreach ($dates as $date) {
						$N = date("N", strtotime($date));
						if ($N == 1)
							$DAYS_A[$N] = 'M';
						else if ($N == 2)
							$DAYS_A[$N] = 'Tu';
						else if ($N == 3)
							$DAYS_A[$N] = 'W';
						else if ($N == 4)
							$DAYS_A[$N] = 'Th';
						else if ($N == 5)
							$DAYS_A[$N] = 'F';
						else if ($N == 6)
							$DAYS_A[$N] = 'Sa';
						else if ($N == 7)
							$DAYS_A[$N] = 'Su';
					}
					ksort($DAYS_A);
					$DAYS1_A[] = implode(", ", $DAYS_A);

					$res_build->MoveNext();
				}

				$txt 	.= '<tr>
							<td width="7%" >' . $res_term->fields['BEGIN_DATE_1'] . '</td>
							<td width="12%" >' . $res_cs->fields['TRANSCRIPT_CODE'] . ' (' . substr($res_cs->fields['SESSION'], 0, 1) . ' - ' . $res_cs->fields['SESSION_NO'] . ')</td>
							<td width="13%" >' . $res_cs->fields['COURSE_DESCRIPTION'] . '</td>';

				$txt .= '<td width="8%" >';
				foreach ($PK_CAMPUS_ROOM_A as $key => $PK_CAMPUS_ROOM) {
					$res = $db->Execute("SELECT ROOM_NO FROM M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM  = '$PK_CAMPUS_ROOM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$txt .= $res->fields['ROOM_NO'] . '<br />';
				}
				$txt .= '</td>';

				$res1 = $db->Execute("SELECT PK_STUDENT_COURSE FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
				$txt .= '<td width="7%" >' . $res1->RecordCount() . '</td>
							<td width="7%" align="right" >' . number_format_value_checker($res_cs->fields['HOURS'], 2) . '</td>
							<td width="7%" align="right" >' . number_format_value_checker($res_cs->fields['UNITS'], 2) . '</td>';

				$txt .= '<td width="16%" >';
				foreach ($CLASS_METTINGS_A as $key => $val) {
					$txt .= $val . '<br />';
				}
				$txt .= '</td>';

				$txt .= '<td width="13%" >';
				foreach ($DAYS1_A as $key => $val) {
					$txt .= $val . '<br />';
				}
				$txt .= '</td>';

				$txt .= '<td width="14%" >';
				foreach ($TIME_A as $key => $val) {
					$txt .= $val . '<br />';
				}
				$txt .= '</td>';

				$txt .= '</tr>';

				$total_units += $res_cs->fields['UNITS'];
				$res_cs->MoveNext();
			}

			$txt 	.= '<tr>
							<td width="54%" ></td>
							<td width="7%" style="border-top:1px solid #000" align="right" >' . number_format_value_checker($total_units, 2) . '</td>
						</tr>
					</table>';
			$pdf->writeHTML($txt, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
		}

		//exit;
		$file_name = 'Instructor Schedule.pdf';

		$pdf->Output('temp/' . $file_name, 'FD');
		return $file_name;
		/////////////////////////////////////////////////////////////////
	}
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
	<title><?= MNU_INSTRUCTOR_SCHEDULE ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor"><?= MNU_INSTRUCTOR_SCHEDULE ?></h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels " method="post" name="form1" id="form1">
									<div class="row" style="padding-bottom:10px;">

										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
												<option value="" selected><?= TERM ?></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>"><?= $res_type->fields['BEGIN_DATE_1'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-4 " id="INSTRUCTOR_DIV"><!-- Ticket # 1593-->
											<select id="INSTRUCTOR" name="INSTRUCTOR" class="form-control">
												<option value=""></option>
											</select>
										</div>

										<div class="col-md-1">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button>
										</div>

									</div>
									<div class="row">
										<div class="col-md-2 align-self-center ">
										</div>
										<div class="col-md-8 align-self-center "></div>
										<div class="col-md-2 ">

											<!-- New -->
											<!--<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>-->

										</div>
									</div>

									<br /><br /><br /><br />
									<input type="hidden" name="FORMAT" id="FORMAT">
								</form>
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
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

			$('#INSTRUCTOR').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= INSTRUCTOR ?>',
				nonSelectedText: '<?= INSTRUCTOR ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= INSTRUCTOR ?> selected'
			});
		});
	</script>

	<script type="text/javascript">
		function get_course_offering() {
			jQuery(document).ready(function($) {
				var data = 'PK_TERM_MASTER=' + document.getElementById('PK_TERM_MASTER').value;
				var url = "ajax_get_course_offering_instructor_from_term";
				//alert(data)
				var value = $.ajax({
					url: url,
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						document.getElementById('INSTRUCTOR_DIV').innerHTML = data;
						document.getElementById('INSTRUCTOR').setAttribute('multiple', true);
						document.getElementById('INSTRUCTOR').name = "INSTRUCTOR[]"
						$("#INSTRUCTOR option[value='']").remove();

						$('#INSTRUCTOR').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?= INSTRUCTOR ?>',
							nonSelectedText: '<?= INSTRUCTOR ?>',
							numberDisplayed: 2,
							nSelectedText: '<?= INSTRUCTOR ?> selected', //Ticket # 1593
							enableCaseInsensitiveFiltering: true, //Ticket # 1593
						});
					}
				}).responseText;
			});
		}

		function get_course_details() {}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if (result == true) {
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}
	</script>

</body>

</html>