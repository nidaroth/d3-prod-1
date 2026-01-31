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
require_once("check_access.php");
require_once("../language/common.php"); 
require_once("../language/company_job.php");
require_once("../language/menu.php");

if(check_access('REPORT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_COMPANY_JOB_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
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
	
	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND JOB_POSTED BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND JOB_POSTED >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND JOB_POSTED <= '$ET' ";
	}
	
	$query1 = "SELECT S_COMPANY.PK_COMPANY,COMPANY_NAME,CITY, PHONE 
	FROM S_COMPANY, S_COMPANY_JOB 
	WHERE S_COMPANY_JOB.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND OPEN_JOB = 'Y' AND S_COMPANY.PK_COMPANY = S_COMPANY_JOB.PK_COMPANY GROUP BY S_COMPANY.PK_COMPANY ORDER BY COMPANY_NAME ASC ";

	$query2 = "SELECT OPEN_JOB, S_COMPANY_JOB.PK_COMPANY_JOB, JOB_NUMBER, JOB_TITLE, S_COMPANY_CONTACT.NAME , WEEKLY_HOURS, PAY_AMOUNT,  M_PLACEMENT_TYPE.DESCRIPTION AS PK_PLACEMENT_TYPE, Z_EMPLOYMENT_TYPE.EMPLOYMENT AS EMPLOYMENT, PAY_AMOUNT,PK_ENROLLMENT_STATUS, JOB_POSTED, JOB_FILLED, JOB_CANCELED, GROUP_CONCAT(CAMPUS_CODE SEPARATOR ', ') as CAMPUS 
	FROM 
	S_COMPANY_JOB 
	LEFT JOIN S_COMPANY_JOB_CAMPUS ON S_COMPANY_JOB_CAMPUS.PK_COMPANY_JOB = S_COMPANY_JOB.PK_COMPANY_JOB 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COMPANY_JOB_CAMPUS.PK_CAMPUS 
	LEFT JOIN Z_EMPLOYMENT_TYPE ON Z_EMPLOYMENT_TYPE.PK_EMPLOYMENT_TYPE = S_COMPANY_JOB.EMPLOYMENT 
	LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE = S_COMPANY_JOB.PK_PLACEMENT_TYPE 
	LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_JOB.PK_COMPANY_CONTACT = S_COMPANY_CONTACT.PK_COMPANY_CONTACT 
	WHERE S_COMPANY_JOB.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND OPEN_JOB = 'Y' $campus_cond1  $cond";
	//echo $query2;exit;
	if($_POST['FORMAT'] == 1){
		
		class MYPDF extends TCPDF {
			public function Header() {
				global $db, $campus_name;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(75, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetTextColor(000, 000, 000);
				$this->SetX(255);
				$this->Cell(55, 8, 'Open Jobs', 0, false, 'L', 0, '', 0, false, 'M', 'L');

				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(240, 13, 290, 13, $style);
				
				$this->SetFont('helvetica', 'I', 13);
				$this->SetY(15);
				$this->SetX(130);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(160, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
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

		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="15%" style="border-bottom:1px solid #000;" ><br /><br /><b>Company</b></td>
							<td width="15%" style="border-bottom:1px solid #000;" ><br /><br /><b>Campus</b></td>
							<td width="16%" style="border-bottom:1px solid #000;" ><br /><br /><b>Job Title</b></td>
							<td width="8%" style="border-bottom:1px solid #000;" align="right" ><br /><br /><b>Weekly Hours</b></td>
							<td width="8%" style="border-bottom:1px solid #000;" align="right" ><br /><br /><b>Pay Amount</b></td>
							<td width="8%" style="border-bottom:1px solid #000;" ><br /><br /><b>City</b></td>
							<td width="8%" style="border-bottom:1px solid #000;" ><br /><br /><b>Job Posted</b></td>
							<td width="12%" style="border-bottom:1px solid #000;" ><br /><br /><b>Company Contact</b></td>
							<td width="12%" style="border-bottom:1px solid #000;" ><br /><br /><b>Company Phone</b></td>
						</tr>
					</thead>';
					
					$res_comp = $db->Execute($query1);
					while (!$res_comp->EOF) {
						$PK_COMPANY = $res_comp->fields['PK_COMPANY'];
					
						$res_type = $db->Execute($query2." AND S_COMPANY_JOB.PK_COMPANY = '$PK_COMPANY' GROUP BY S_COMPANY_JOB.PK_COMPANY_JOB ORDER BY JOB_POSTED DESC, JOB_TITLE ASC ");
						while (!$res_type->EOF) {
							$JOB_POSTED  = ($res_type->fields['JOB_POSTED'] != '0000-00-00' && $res_type->fields['JOB_POSTED'] != '' ? date("m/d/Y",strtotime($res_type->fields['JOB_POSTED'])) : '');
							$txt .= '<tr>
										<td width="15%">'.$res_comp->fields['COMPANY_NAME'].'</td>
										<td width="15%">'.$res_type->fields['CAMPUS'].'</td>
										<td width="16%">'.$res_type->fields['JOB_TITLE'].'</td>
										<td width="8%" align="right" >'.$res_type->fields['WEEKLY_HOURS'].'</td>
										<td width="8%" align="right" >$ '.number_format_value_checker($res_type->fields['PAY_AMOUNT'],2).'</td>
										<td width="8%">'.$res_comp->fields['CITY'].'</td>
										<td width="8%">'.$JOB_POSTED.'</td>
										<td width="12%">'.$res_type->fields['NAME'].'</td>
										<td width="12%">'.$res_comp->fields['PHONE'].'</td>
									</tr>';
							$res_type->MoveNext();
						}	
						if($res_type->RecordCount() > 0) {
							$txt .= '<tr>
										<td width="15%"></td>
										<td width="15%"></td>
										<td width="55%" ><b>Company: '.$res_comp->fields['COMPANY_NAME'].'</b></td>
										<td width="13%">Openings: '.$res_type->RecordCount().'</td>
									</tr>';
						}
						
						$res_comp->MoveNext();
					}
		$txt .= '</table>';
				
				////////////////////////////////
				
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Open Jobs.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');
		*/	
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
	} else if($_POST['FORMAT'] == 2) {
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
		$file_name 		= 'Open Jobs.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;

		$heading[] = 'Company';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Job Title';
		$width[]   = 20;
		$heading[] = 'Weekly Hours';
		$width[]   = 20;
		$heading[] = 'Pay Amount';
		$width[]   = 20;
		$heading[] = 'City';
		$width[]   = 20;
		$heading[] = 'Job Posted';
		$width[]   = 20;
		$heading[] = 'Company Contact';
		$width[]   = 20;
		$heading[] = 'Company Phone';
		$width[]   = 20;
		
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$res_comp = $db->Execute($query1);
		while (!$res_comp->EOF) {
			$PK_COMPANY = $res_comp->fields['PK_COMPANY'];
		
			$res_type = $db->Execute($query2." AND S_COMPANY_JOB.PK_COMPANY = '$PK_COMPANY' GROUP BY S_COMPANY_JOB.PK_COMPANY_JOB ORDER BY JOB_POSTED DESC, JOB_TITLE ASC ");
			while (!$res_type->EOF) {
				$JOB_POSTED  = ($res_type->fields['JOB_POSTED'] != '0000-00-00' && $res_type->fields['JOB_POSTED'] != '' ? date("Y-m-d",strtotime($res_type->fields['JOB_POSTED'])) : '');

				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['COMPANY_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['CAMPUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['JOB_TITLE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['WEEKLY_HOURS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue(number_format_value_checker($res_type->fields['PAY_AMOUNT'],2));
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['CITY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($JOB_POSTED);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['PHONE']);
				
				$res_type->MoveNext();
			}	
			
			$res_comp->MoveNext();
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
	<title><?=MNU_OPEN_JOBS ?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_OPEN_JOBS?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" >
									
									<div class="row">
										<div class="col-md-2 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT= '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?>  ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									
										<div class="col-md-2">
											<?=JOB_POSTED_START_DATE?>
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="" >
										</div>
										
										<div class="col-md-2">
											<?=JOB_POSTED_END_DATE?>
											<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2 ">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<br /><br /><br /><br /><br /><br />
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>
</body>

</html>