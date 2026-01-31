<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST) || $_GET['p'] == 'r'){ //Ticket # 1195
	//echo "<pre>";print_r($_POST);exit;
	$_GET['pk_co']=implode(",", $_POST['GS_PK_COURSE_OFFERING']);
	/* Ticket # 1195 */
	$stud_cond = "";
	if($_GET['eid'] != '') {
		$_POST['PK_COURSE_OFFERING'] 	= explode(",",$_GET['pk_co']);
		$_POST['FORMAT']				= 1;
		$stud_cond = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN ($_GET[eid]) ";
	}
	/* Ticket # 1195 */
	
	if($_GET['pk_co'] != '') {
		$_POST['PK_COURSE_OFFERING'] 	= explode(",",$_GET['pk_co']);
		$_POST['FORMAT']				= 1;
	}

	$cond = "";

	$PK_COURSE_OFFERING = implode(",",$_POST['PK_COURSE_OFFERING']);
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($PK_COURSE_OFFERING) ";

	//Ticket # 1195
	$query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING, COURSE_CODE, COURSE_DESCRIPTION   
	FROM
	S_COURSE, S_COURSE_OFFERING     
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND   
	S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE $cond
	GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER By COURSE_CODE ";
	
	$res_course = $db->Execute($query);
	while (!$res_course->EOF) {
		$PK_COURSE_OFFERING_ARR[] 	= $res_course->fields['PK_COURSE_OFFERING'];
		
		$res_course->MoveNext();
	}

	//Ticket # 1195
	/* Ticket # 1152 */
	$query = "SELECT S_STUDENT_COURSE.PK_STUDENT_COURSE, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) as STUD_NAME, STUDENT_ID, GRADE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, COURSE_OFFERING_STUDENT_STATUS, CAMPUS_CODE, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, NUMERIC_GRADE      
	FROM
	S_COURSE_OFFERING, S_STUDENT_COURSE 
	LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER 
	LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS = S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS  
	, S_STUDENT_MASTER, S_STUDENT_ACADEMICS,  S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	, M_STUDENT_STATUS 
	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
	S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	 M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	";
	/* Ticket # 1152 */
	
	$group_by = " GROUP BY S_STUDENT_COURSE.PK_STUDENT_COURSE ";
	$order_by = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
	
	if($_POST['FORMAT'] == 1) {
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
				global $db;
				
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
				
				$this->SetFont('helvetica', 'I', 14);
				$this->SetY(9);
				$this->SetX(178);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Grade Sheet", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 12, 208, 12, $style);
				
				/* Ticket # 1195 */
				if(isset($_POST['PK_TERM_MASTER'])){
					$res_type = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_POST[PK_TERM_MASTER]' ");
					
					$this->SetFont('helvetica', 'I', 12);
					$this->SetY(15);
					$this->SetX(143);
					$this->SetTextColor(000, 000, 000);
					$this->Cell(55, 8, "Selected Term Starts: ".$res_type->fields['BEGIN_DATE_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				}
				/* Ticket # 1195 */
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
		$pdf->SetFont('helvetica', '', 7, '', true);
		$txt = ''; //Ticket # 669
		foreach($PK_COURSE_OFFERING_ARR as $PK_COURSE_OFFERING){
			
			$res_cs = $db->Execute("select CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 , IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.END_DATE, '%m/%d/%Y' )) AS  END_DATE_1,SESSION,SESSION_NO, COURSE_OFFERING_STATUS, COURSE_CODE, COURSE_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM, S_COURSE WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");
			
			$cond1 = " AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ".$stud_cond; //Ticket # 1195

			$pdf->AddPage();
			//Ticket # 669
			$txt .= '<div style="page-break-before: always;">';
			$txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%" style="border:1px solid #000000;">
						<tr>
							<td align="left" width="25%" ><b>Course: '.$res_cs->fields['COURSE_CODE'].' ('.$res_cs->fields['SESSION'].'-'.$res_cs->fields['SESSION_NO'].')</b><br />'.$res_cs->fields['COURSE_DESCRIPTION'].'</td>
							<td align="center" width="20%" ><b>Term Date</b><br />'.$res_cs->fields['BEGIN_DATE_1'].' - '.$res_cs->fields['END_DATE_1'].'</td>
							<td align="center" width="25%" ><b>Instructor</b><br />'.$res_cs->fields['INSTRUCTOR_NAME'].'</td>
							<td align="center" width="10%"><b>Room</b><br />'.$res_cs->fields['ROOM_NO'].'</td>
							<td align="center" width="10%"><b>Attendance</b><br />'.$res_cs->fields['ATTENDANCE_TYPE'].'</td>
							<td align="center" width="10%"><b>Status</b><br />'.$res_cs->fields['COURSE_OFFERING_STATUS'].'</td>
						</tr>
					</table>';

			$txt .= '<br />';
			$txt .='<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br /><b>Student ID</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br /><b>Student Name</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br /><b>Campus</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br /><b>First Term</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br /><b>Program</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><br /><b>Status</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><b>Course Offering<br />Student Status</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><br /><br /><b>Final<br />Grade</b></td>
								<td  style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Final<br />Numeric<br />Grade</b></td>
							</tr>
						</thead>';
						//Ticket # 669

			$res_stud = $db->Execute($query." ".$cond1." ".$group_by." ".$order_by);
			//echo $query." ".$cond1." ".$group_by." ".$order_by."<br /><br />";exit;
			/* Ticket # 1152 */
			while (!$res_stud->EOF) {

				//Ticket # 669
				$txt 	.= '<tr>
								<td  style="border-bottom:1px solid #000;padding:7px;" >'.$res_stud->fields['STUDENT_ID'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['STUD_NAME'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['CAMPUS_CODE'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['BEGIN_DATE'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['CODE'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['STUDENT_STATUS'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['COURSE_OFFERING_STUDENT_STATUS'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['GRADE'].'</td>
								<td  style="border-bottom:1px solid #000;" >'.$res_stud->fields['NUMERIC_GRADE'].'</td>
							</tr>';
							//Ticket # 669
				
				$res_stud->MoveNext();
			}
			/* Ticket # 1152 */
			//Ticket # 669	
			$txt 	.= '<tr>
							<td >
								<br /><br /><br />
								<i style="font-size:14px" >Instructor Signature: </i>
							</td>
						</tr>
					</table>';
			$txt .= '</div>';
			//Ticket # 669
		     

			//echo $txt;exit;
			//$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				
		}
		//exit;
		//$file_name = 'Grade Sheet.pdf'; //Ticket # 669
		$file_name = 'Grade_Sheet_'.uniqid().'.pdf'; //Ticket # 669

		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		//$pdf->Output('temp/'.$file_name, 'FD');//Ticket # 669
		//return $file_name;	//Ticket # 669

		//Ticket # 669

		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$logo='';
		if($res->fields['PDF_LOGO'] != '')
			$PDF_LOGO =$res->fields['PDF_LOGO'];
			
			if($PDF_LOGO != ''){
				//$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				$PDF_LOGO=str_replace('../',$http_path,$PDF_LOGO);
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			}


		$SCHOOL_NAME ='';
		if($res->fields['SCHOOL_NAME'] != '')
			$SCHOOL_NAME =$res->fields['SCHOOL_NAME'];
	

		$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="40%" valign="top" style="font-size:20px;" >'.$SCHOOL_NAME.'</td>
								<td width="40%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;font-style: italic;font-family:helvetica;" ><b>Grade Sheet</b></td>
										</tr>										
									</table>
								</td>
							</tr>							
					</table>';


		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
								

		$footer = '<table width="100%">
		<tr>
			<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
			<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
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
		<div> '.$header.' </div>
		</body>
		</html>';
		$html_body_cont = '<!DOCTYPE HTML>
		<html>
		<head> <style>
		body{ font-size:12px; font-family:helvetica; }	
		table{  margin-top: 10px; }
		table tr{  padding-top: 15px !important; }
		</style>
		</head>
		<body>'.$txt.'</body></html>';
		$footer_cont= '<!DOCTYPE HTML><html><head><style>
		tbody td{ font-size:14px !important; }
		</style></head><body>'.$footer.'</body></html>';

		$header_path= create_html_file('header.html',$header_cont,'grade_sheet');
		$content_path=create_html_file('content.html',$html_body_cont,'grade_sheet');
		$footer_path= create_html_file('footer.html',$footer_cont,'grade_sheet');
	
		sleep(2);
		$margin_top="30mm";
		// if(strlen($header)>1530){
		// $margin_top="60mm";
		// }
		exec('xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 210mm  --page-height 297mm --margin-top '.$margin_top.'  --footer-spacing 8  --margin-left 5mm --margin-right 5mm  --margin-bottom 20mm --footer-font-size 8 --footer-right "Page [page] of [toPage]" --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/grade_sheet/'.$file_name.' 2>&1');
				
		//echo 'temp/grade_sheet/'.$file_name;

		header('Content-Type: Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="' . basename($http_path.'school/temp/grade_sheet/'.$file_name) . '"');
		//header('Content-Length: ' . $pdfdata['filefullpath']);
		readfile('temp/grade_sheet/'.$file_name);
		exit;	

		//Ticket # 669


		/////////////////////////////////////////////////////////////////
	} else {
		
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
	<title><?=MNU_GRADE_SHEET ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
                        <h4 class="text-themecolor"><?=MNU_GRADE_SHEET ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)" >
												<option value="" selected><?=TERM?></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-3 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										
										<div class="col-md-1">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
										</div>
										
									</div>
									<div class="row">
										<div class="col-md-2 align-self-center ">
										</div>
										<div class="col-md-8 align-self-center "></div>
										<div class="col-md-2 ">
											
											<!-- New -->
											<!--<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>-->
											
										</div>
									</div>
									
									<br /><br /><br /><br />
									<input type="hidden" name="FORMAT" id="FORMAT" >
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
	});
	
	</script>
	
	<script type="text/javascript">
		function get_course_offering(){
			jQuery(document).ready(function($) { 
				var data  = 'PK_TERM_MASTER='+document.getElementById('PK_TERM_MASTER').value+'&dont_show_term=1';
				var url	  = "ajax_get_course_offering_from_term";
			
				//alert(data)
				var value = $.ajax({
					url: url,	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_course_details(){
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>

</body>

</html>
