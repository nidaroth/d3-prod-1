<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/projected_funds.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}



if(!empty($_POST) || !empty($_GET)){ //Ticket # 1194 
	/* Ticket # 1194  */
	if(!empty($_GET)) {
		$_POST['START_DATE'] = $_GET['st'];
		$_POST['END_DATE'] 	 = $_GET['et'];
		$_POST['FORMAT'] 	 = $_GET['FORMAT'];

		$_POST['CO'] 	 = $_GET['co'];  //DIAM-1417
		$_POST['T_ID'] 	 = $_GET['t_id'];  //DIAM-1417
		$_POST['PK_CAMPUS'] = $_GET['campus']; //DIAM-1417
		$_POST['co_start_date'] = $_GET['co_start_date']; //DIAM-1417
		$_POST['co_end_date'] = $_GET['co_end_date']; //DIAM-1417
	}
	/* Ticket # 1194  */
	
	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND (S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE BETWEEN '$ST' AND '$ET')";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND (S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE >= '$ST') ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND (S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE <= '$ET') ";
	}
 	//DIAM-1417
	$course_term_cond = "";
	if($_POST['T_ID'] != '') {
		$course_term_cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($_POST[T_ID]) "; //Ticket # 1341
	}

	if($_POST['CO'] != '') {
		$course_term_cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($_POST[CO]) ";
	}

	// DIAM-2183
	if($_POST['co_start_date'] != '' && $_POST['co_end_date'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['co_start_date']));
		$ET = date("Y-m-d",strtotime($_POST['co_end_date']));
		$cond .= " AND (S_TERM_MASTER.BEGIN_DATE BETWEEN '$ST' AND '$ET')";
	} else if($_POST['co_start_date'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['co_start_date']));
		$cond .= " AND (S_TERM_MASTER.BEGIN_DATE >= '$ST') ";
	} else if($_POST['co_end_date'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['co_end_date']));
		$cond .= " AND (S_TERM_MASTER.BEGIN_DATE <= '$ET') ";
	}
	// End DIAM-2183
	
		$campus_name  = "";
		$campus_cond  = "";
		$campus_id	  = "";
		if(!empty($_POST['PK_CAMPUS'])){
			$PK_CAMPUS 	  = $_POST['PK_CAMPUS'];
			$campus_cond  .= " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$cond 		  .= " AND S_COURSE_OFFERING.PK_CAMPUS IN ($PK_CAMPUS) "; // DIAM-2183
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

		// $term_str = "";
		// $res_campus = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER IN ($_POST[T_ID]) order by BEGIN_DATE DESC");
		// while (!$res_campus->EOF) {
		// 	if($term_str != '')
		// 		$term_str .= ', ';
		// 	$term_str .= $res_campus->fields['BEGIN_DATE_1'];			
		// 	$res_campus->MoveNext();
		// }

			// if(count(explode(',',$term_str)) > 5){
			// 	$term_str = "Multiple Terms Selected";
			// }

	 //DIAM-1417
	//DIAM-1417
	$query = "select PK_COURSE_OFFERING_SCHEDULE_DETAIL, TRANSCRIPT_CODE, COURSE_DESCRIPTION, SESSION_NO, SESSION, CONCAT(LAST_NAME,', ',FIRST_NAME) AS INSTRUCTOR_NAME, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, IF(SCHEDULE_DATE = '0000-00-00','',DATE_FORMAT(SCHEDULE_DATE, '%Y-%m-%d' )) AS  SCHEDULE_DATE_1  
	FROM 
	S_COURSE_OFFERING_SCHEDULE_DETAIL, S_COURSE_OFFERING
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = INSTRUCTOR 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER
	, S_COURSE 
	WHERE 
	S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_COURSE_OFFERING_SCHEDULE_DETAIL.ACTIVE = 1 AND COMPLETED = 0 $cond $course_term_cond AND 
	S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND 
	S_COURSE_OFFERING.PK_COURSE  = S_COURSE.PK_COURSE 
	ORDER BY TRANSCRIPT_CODE ASC, COURSE_DESCRIPTION ASC, SCHEDULE_DATE ASC  ";
	
	//echo $query;exit;
		
	if($_POST['FORMAT'] == 1){
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
				$this->SetY(5);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(130);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Attendance Incomplete", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
				//DIAM-1417
				global $campus_name; 
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Campus(es): ".$campus_name, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				//DIAM-1417
				$term_str = "";
				if($_POST['co_start_date'] != '' && $_POST['co_end_date'] != '')
					$term_str = " Course Term Dates Between: ".$_POST['co_start_date'].' - '.$_POST['co_end_date'];
				else if($_POST['co_start_date'] != '')
					$term_str = " Course Term Date From: ".$_POST['co_start_date'];
				else if($_POST['co_end_date'] != '')
					$term_str = " Course Term Date To: ".$_POST['co_end_date'];
				//DIAM-1417
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(21);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5,$term_str, 0, false, 'R', 0, '', 0, false, 'M', 'L');

				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " Attendance Dates Between: ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = "  Attendance Date From: ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = "  Attendance Date To: ".$_POST['END_DATE'];
					
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(26);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');	
				//DIAM-1417
				

				
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
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';

		$sub_total = 0;
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Term</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Course</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Session</td>
							<td width="25%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Course Description</td>
							<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Instructor</td>
							<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Class Date</td>
						</tr>
					</thead>';
		$res_co = $db->Execute($query);
		while (!$res_co->EOF) { 
			
			$txt 	.= '<tr>
						<td width="10%" >'.$res_co->fields['BEGIN_DATE_1'].'</td>
						<td width="20%" >'.$res_co->fields['TRANSCRIPT_CODE'].'</td>
						<td width="10%"  >'.substr($res_co->fields['SESSION'],0,1).' - '. $res_co->fields['SESSION_NO'].'</td>
						<td width="25%" >'.$res_co->fields['COURSE_DESCRIPTION'].'</td>
						<td width="20%" >'.$res_co->fields['INSTRUCTOR_NAME'].'</td>
						<td width="15%" >'.$res_co->fields['SCHEDULE_DATE_1'].'</td>
					</tr>';
			
			$res_co->MoveNext();
		}
		$txt 	.= '</table>';
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Attendance_Incomplete_'.uniqid().'.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
	} else if($_POST['FORMAT'] == 2){
		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		$total_fields = 120;
		for($i = 0 ; $i <= $total_fields ; $i++){
			if($i <= 25)
				$cell[] = $cell1[$i];
			else {
				$j = floor($i / 26) - 1;
				$k = ($i % 26);
				//echo $j."--".$k."<br />";
				$cell[] = $cell1[$j].$cell1[$k];
			}	
		}

		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Attendance Incomplete.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line = 1;
		$index 	= -1;
		$heading[] = 'Term';
		$width[]   = 15;
		$heading[] = 'Course';
		$width[]   = 30;
		$heading[] = 'Session';
		$width[]   = 20;
		$heading[] = 'Course Description';
		$width[]   = 40;
		$heading[] = 'Instructor';
		$width[]   = 25;
		$heading[] = 'Class Date';
		$width[]   = 20;

		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			
			$i++;
		}

		$res_co = $db->Execute($query);
		while (!$res_co->EOF) { 

			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['BEGIN_DATE_1']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['TRANSCRIPT_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(substr($res_co->fields['SESSION'],0,1).' - '. $res_co->fields['SESSION_NO']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['COURSE_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['INSTRUCTOR_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['SCHEDULE_DATE_1']);

			$res_co->MoveNext();
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
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
	<title><?=MNU_ATTENDANCE_INCOMPLETE?> | <?=$title?></title>
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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_ATTENDANCE_INCOMPLETE?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2" style="padding: 0;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
								</div>
							</div>
						</div>
					</div>
				</form>
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
