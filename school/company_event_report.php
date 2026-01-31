<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/company.php");
require_once("check_access.php");

if(check_access('REPORT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	
	$cond  = "";
	$field = "";

	if(!empty($_POST['PK_COMPANY'])) {
		$cond .= " AND S_COMPANY_EVENT.PK_COMPANY in (".implode(",",$_POST['PK_COMPANY']).") ";
	}
	
	if(!empty($_POST['PK_PLACEMENT_COMPANY_EVENT_TYPE'])) {
		$cond .= " AND S_COMPANY_EVENT.PK_PLACEMENT_COMPANY_EVENT_TYPE in (".implode(",",$_POST['PK_PLACEMENT_COMPANY_EVENT_TYPE']).") ";
	}
	
	$fields = "";
	
	if($_POST['DATE_TYPE'] == 1)
		$fields = "EVENT_DATE";
	else if($_POST['DATE_TYPE'] == 2)
		$fields = "FOLLOW_UP_DATE";
	
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $fields BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND $fields >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND $fields <= '$ET' ";
	}
	
	$query = "SELECT COMPANY_NAME, PLACEMENT_COMPANY_EVENT_TYPE, IF(EVENT_DATE = '0000-00-00','',DATE_FORMAT(EVENT_DATE, '%Y-%m-%d' )) AS EVENT_DATE, IF(FOLLOW_UP_DATE = '0000-00-00','',DATE_FORMAT(FOLLOW_UP_DATE, '%Y-%m-%d' )) AS FOLLOW_UP_DATE, S_COMPANY_CONTACT.NAME as  COMPANY_CONTACT_NAME,CONCAT(FIRST_NAME,' ',LAST_NAME) AS SCHOOL_EMP, IF(COMPLETE = 1, 'Yes', 'No') as COMPLETE, NOTE 
	FROM S_COMPANY, S_COMPANY_EVENT 
	LEFT JOIN M_PLACEMENT_COMPANY_EVENT_TYPE ON M_PLACEMENT_COMPANY_EVENT_TYPE.PK_PLACEMENT_COMPANY_EVENT_TYPE = S_COMPANY_EVENT.PK_PLACEMENT_COMPANY_EVENT_TYPE 
	LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_CONTACT.PK_COMPANY_CONTACT = S_COMPANY_EVENT.PK_COMPANY_CONTACT 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COMPANY_EVENT.PK_COMPANY_CONTACT_EMPLOYEE  
	WHERE 
	S_COMPANY_EVENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COMPANY.PK_COMPANY = S_COMPANY_EVENT.PK_COMPANY $cond GROUP BY S_COMPANY_EVENT.PK_COMPANY_EVENT ";
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
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 17);
				$this->SetY(8);
				$this->SetX(155);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Company Events", 0, false, 'L', 0, '', 0, false, 'M', 'L');
							
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(150, 11, 202, 11, $style);
				
				$str = "Company Events between: ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(12);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, $str, 0, 'R', 0, 0, '', '', true);
				
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

		//$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true);
		
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 25, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$txt 	= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
						<thead>
							<tr>
								<td width="23%" style="border-bottom:1px solid #000;" ><b>Company</b></td>
								<td width="17%" style="border-bottom:1px solid #000;" ><b>Event Type</b></td>
								<td width="10%" style="border-bottom:1px solid #000;" ><b>Event Date</b></td>
								<td width="13%" style="border-bottom:1px solid #000;" ><b>Follow Up Date</b></td>
								
								<td width="15%" style="border-bottom:1px solid #000;" ><b>Company Contact</b></td>
								
								<td width="15%" style="border-bottom:1px solid #000;" ><b>School Employee</b></td>
								<td width="7%" style="border-bottom:1px solid #000;" ><b>Complete</b></td>
							</tr>
						</thead>';
		$res_comp = $db->Execute($query);
		while (!$res_comp->EOF) { 

			$txt .= '<tr nobr="true" >
						<td width="100%" style="border-bottom:2px solid #DDD;" >
							<table width="100%" cellpadding="3">
								<tr>
									<td style="width:23%" >'.$res_comp->fields['COMPANY_NAME'].'</td>
									<td style="width:17%" >'.$res_comp->fields['PLACEMENT_COMPANY_EVENT_TYPE'].'</td>
									<td style="width:10%" >'.$res_comp->fields['EVENT_DATE'].'</td>
									<td style="width:13%" >'.$res_comp->fields['FOLLOW_UP_DATE'].'</td>
									<td style="width:15%" >'.$res_comp->fields['COMPANY_CONTACT_NAME'].'</td>
									<td style="width:15%" >'.$res_comp->fields['SCHOOL_EMP'].'</td>
									<td style="width:7%" align="center" >'.$res_comp->fields['COMPLETE'].'</td>
								</tr>
								<tr>
									<td style="width:23%" ></td>
									<td style="width:77%" ><b>NOTES: </b>'.$res_comp->fields['NOTE'].'</td>
								</tr>
							</table>
						</td>
					</tr>';
			
			$res_comp->MoveNext();
		}
		
		$txt .= '</table>';
		
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Company Events.pdf';
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
		$file_name 		= 'Company Events.xlsx';
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

		$heading[] = 'Company Name';
		$width[]   = 25;
		$heading[] = 'Event Type';
		$width[]   = 25;
		$heading[] = 'Event Date';
		$width[]   = 20;
		$heading[] = 'Follow Up Date';
		$width[]   = 20;
		$heading[] = 'Company Contact';
		$width[]   = 20;
		$heading[] = 'School Employee';
		$width[]   = 20;
		$heading[] = 'Complete';
		$width[]   = 20;
		$heading[] = 'Notes';
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

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$res_comp = $db->Execute($query);
		while (!$res_comp->EOF) { 
		
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['COMPANY_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['PLACEMENT_COMPANY_EVENT_TYPE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['EVENT_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['FOLLOW_UP_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['COMPANY_CONTACT_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['SCHOOL_EMP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['COMPLETE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['NOTE']);
			
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
	<title><?=MNU_COMPANY_EVENTS ?> | <?=$title?></title>
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
							<?=MNU_COMPANY_EVENTS ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-3">
											<?=COMPANY_NAME?>
											<select id="PK_COMPANY" name="PK_COMPANY[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_COMPANY, COMPANY_NAME from S_COMPANY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY COMPANY_NAME ASC ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COMPANY']?>" ><?=$res_type->fields['COMPANY_NAME']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=EVENT_TYPE?>
											<select id="PK_PLACEMENT_COMPANY_EVENT_TYPE" name="PK_PLACEMENT_COMPANY_EVENT_TYPE[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_PLACEMENT_COMPANY_EVENT_TYPE, PLACEMENT_COMPANY_EVENT_TYPE from M_PLACEMENT_COMPANY_EVENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY PLACEMENT_COMPANY_EVENT_TYPE ASC ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_PLACEMENT_COMPANY_EVENT_TYPE'] ?>" ><?=$res_type->fields['PLACEMENT_COMPANY_EVENT_TYPE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2">
											<?=DATE_TYPE?>
											<select id="DATE_TYPE" name="DATE_TYPE" class="form-control">
												<option value="1">Event Date</option>
												<option value="2">Follow Up Date</option>
											</select>
										</div>
										
										<div class="col-md-1">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										
										<div class="col-md-1">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
						
										<div class="col-md-2" style="text-align:right" >
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_COMPANY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COMPANY_NAME?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=COMPANY_NAME?> selected'
		});
		$('#PK_PLACEMENT_COMPANY_EVENT_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EVENT_TYPE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=EVENT_TYPE?> selected'
		});
	});
	</script>

</body>

</html>