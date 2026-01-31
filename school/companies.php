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

	if($_POST['ACTIVE'] == 1){
		$cond .= " AND S_COMPANY.ACTIVE = 1 ";
	} else if($_POST['ACTIVE'] == 2){
		$cond .= " AND S_COMPANY.ACTIVE = 0 ";
	}
	
	$having = "";
	if($_POST['COMPANY_JOB'] == 1){
		$having = " HAVING OPEN_JOBS > 0 ";
	} else if($_POST['COMPANY_JOB'] == 2){
		$having = " HAVING OPEN_JOBS = 0 ";
	}
	
	if(!empty($_POST['PK_PLACEMENT_TYPE'])) {
		$cond .= " AND S_COMPANY.PK_PLACEMENT_TYPE in (".implode(",",$_POST['PK_PLACEMENT_TYPE']).") ";
	}
	
	if(!empty($_POST['PK_PLACEMENT_COMPANY_STATUS'])) {
		$cond .= " AND S_COMPANY.PK_PLACEMENT_COMPANY_STATUS in (".implode(",",$_POST['PK_PLACEMENT_COMPANY_STATUS']).") ";
	}
	
	if(!empty($_POST['PK_COMPANY_SOURCE'])) {
		$cond .= " AND S_COMPANY.PK_COMPANY_SOURCE in (".implode(",",$_POST['PK_COMPANY_SOURCE']).") ";
	}
	
	$query = "SELECT COMPANY_NAME, S_COMPANY.PHONE as COMPANY_PHONE , M_PLACEMENT_TYPE.TYPE, PLACEMENT_COMPANY_STATUS, WEBSITE, IF(S_COMPANY.ACTIVE = 1,'Y','N') as ACTIVE, CONCAT(ADDRESS, ' ', ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP,  S_COMPANY_CONTACT.NAME, S_COMPANY_CONTACT.TITLE, S_COMPANY_CONTACT.PHONE as CONTACT_PHONE, S_COMPANY_CONTACT.EMAIL, COUNT(S_COMPANY_JOB_ALL.PK_COMPANY) AS TOTAL_JOBS, COUNT(S_COMPANY_JOB_OPEN.PK_COMPANY) AS OPEN_JOBS, COMPANY_SOURCE 
	FROM S_COMPANY 
	LEFT JOIN M_COMPANY_SOURCE ON M_COMPANY_SOURCE.PK_COMPANY_SOURCE = S_COMPANY.PK_COMPANY_SOURCE 
	LEFT JOIN S_COMPANY_JOB as S_COMPANY_JOB_ALL ON S_COMPANY_JOB_ALL.PK_COMPANY = S_COMPANY.PK_COMPANY AND S_COMPANY_JOB_ALL.ACTIVE = 1 
	LEFT JOIN S_COMPANY_JOB as S_COMPANY_JOB_OPEN ON S_COMPANY_JOB_OPEN.PK_COMPANY = S_COMPANY.PK_COMPANY AND S_COMPANY_JOB_OPEN.JOB_CANCELED = '0000-00-00' AND S_COMPANY_JOB_OPEN.JOB_FILLED = '0000-00-00' AND S_COMPANY_JOB_OPEN.ACTIVE = 1 
	LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE = S_COMPANY.PK_PLACEMENT_TYPE 
	LEFT JOIN M_PLACEMENT_COMPANY_STATUS ON M_PLACEMENT_COMPANY_STATUS.PK_PLACEMENT_COMPANY_STATUS = S_COMPANY.PK_PLACEMENT_COMPANY_STATUS 
	LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_COMPANY.PK_STATES 
	LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_CONTACT.PK_COMPANY_CONTACT = S_COMPANY.PK_COMPANY_CONTACT
	WHERE 
	S_COMPANY.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond GROUP BY S_COMPANY.PK_COMPANY $having ";
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
				$this->SetX(170);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Companies", 0, false, 'L', 0, '', 0, false, 'M', 'L');
							
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(150, 11, 202, 11, $style);
				
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
		$pdf->SetMargins(7, 31, 7);
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
								<td width="25%" style="border-bottom:1px solid #000;" ><b>Company</b></td>
								<td width="15%" style="border-bottom:1px solid #000;" ><b>Company Type</b></td>
								<td width="15%" style="border-bottom:1px solid #000;" ><b>Company Phone</b></td>
								<td width="20%" style="border-bottom:1px solid #000;" ><b>Main Contact</b></td>
								<td width="20%" style="border-bottom:1px solid #000;" ><b>Company Address</b></td>
								<td width="5%" style="border-bottom:1px solid #000;" ><b>Active</b></td>
							</tr>
						</thead>';
		$res_comp = $db->Execute($query);
		while (!$res_comp->EOF) { 

			$txt .= '<tr nobr="true" >
						<td width="100%" style="border-bottom:2px solid #DDD;" >
							<table width="100%" cellpadding="3">
								<tr>
									<td style="width:25%" rowspan="4" >'.$res_comp->fields['COMPANY_NAME'].'</td>
									<td style="width:15%" >'.$res_comp->fields['TYPE'].'</td>
									<td style="width:15%" >'.$res_comp->fields['COMPANY_PHONE'].'</td>
									<td style="width:20%" ><b>Name: </b>'.$res_comp->fields['NAME'].'</td>
									<td style="width:20%" >'.$res_comp->fields['ADDRESS'].'</td>
									<td style="width:5%" align="center" >'.$res_comp->fields['ACTIVE'].'</td>
								</tr>
								<tr>
									<td style="width:30%" ><b>Status: </b>'.$res_comp->fields['PLACEMENT_COMPANY_STATUS'].'</td>
									<td style="width:20%" ><b>Title: </b>'.$res_comp->fields['TITLE'].'</td>
									<td style="width:35%" >'.$res_comp->fields['CITY'].', '.$res_comp->fields['STATE_CODE'].' '.$res_comp->fields['ZIP'].'</td>
								</tr>
								<tr>
									<td style="width:30%" ><b>Website: </b>'.$res_comp->fields['WEBSITE'].'</td>
									<td style="width:45%" ><b>Phone: </b>'.$res_comp->fields['CONTACT_PHONE'].'</td>
								</tr>
								<tr>
									<td style="width:30%" ><b>Email: </b>'.$res_comp->fields['EMAIL'].'</td>
									<td style="width:45%" ><b>Source: </b>'.$res_comp->fields['COMPANY_SOURCE'].'</td>
								</tr>
							</table>
						</td>
					</tr>';
			
			$res_comp->MoveNext();
		}
		
		$txt .= '</table>';
		
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Companies.pdf';
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
		$file_name 		= 'Companies.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;

		$heading[] = 'Company Name';
		$width[]   = 20;
		$heading[] = 'Company Type';
		$width[]   = 20;
		$heading[] = 'Company Status';
		$width[]   = 20;
		$heading[] = 'Company Source';
		$width[]   = 20;
		$heading[] = 'Company Phone';
		$width[]   = 20;
		$heading[] = 'Company Website';
		$width[]   = 20;
		$heading[] = 'Company Email';
		$width[]   = 20;
		
		$heading[] = 'Main Contact Name';
		$width[]   = 20;
		$heading[] = 'Main Contact Title';
		$width[]   = 20;
		$heading[] = 'Main Contact Phone';
		$width[]   = 20;
		
		$heading[] = 'Address';
		$width[]   = 30;
		$heading[] = 'City';
		$width[]   = 20;
		$heading[] = 'State';
		$width[]   = 20;
		$heading[] = 'Zip';
		$width[]   = 20;
		$heading[] = 'Active';
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
		
		$res_comp = $db->Execute($query);
		while (!$res_comp->EOF) { 
		
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['COMPANY_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['TYPE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['PLACEMENT_COMPANY_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['COMPANY_SOURCE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['COMPANY_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['WEBSITE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['EMAIL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['TITLE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['CONTACT_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['ADDRESS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['CITY']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['STATE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['ZIP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_comp->fields['ACTIVE']);

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
	<title><?=MNU_COMPANIES ?> | <?=$title?></title>
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
							<?=MNU_COMPANIES ?>
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
											<?=PLACEMENT_TYPE?>
											<select id="PK_PLACEMENT_TYPE" name="PK_PLACEMENT_TYPE[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PLACEMENT_TYPE, TYPE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY TYPE ASC ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_PLACEMENT_TYPE']?>" ><?=$res_type->fields['TYPE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=PLACEMENT_COMPANY_STATUS?>
											<select id="PK_PLACEMENT_COMPANY_STATUS" name="PK_PLACEMENT_COMPANY_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PLACEMENT_COMPANY_STATUS, PLACEMENT_COMPANY_STATUS from M_PLACEMENT_COMPANY_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY PLACEMENT_COMPANY_STATUS ASC ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_PLACEMENT_COMPANY_STATUS']?>" ><?=$res_type->fields['PLACEMENT_COMPANY_STATUS']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2">
											<?=ACTIVE?>
											<div class="row form-group">
												<div class="custom-control custom-radio col-md-4">
													<input type="radio" id="ACTIVE_YES" name="ACTIVE" value="1" class="custom-control-input" checked >
													<label class="custom-control-label" for="ACTIVE_YES"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-4">
													<input type="radio" id="ACTIVE_NO" name="ACTIVE" value="2" class="custom-control-input" >
													<label class="custom-control-label" for="ACTIVE_NO"><?=NO?></label>
												</div>
											</div>
										</div>
										
										<div class="col-md-5">
											<br />
											<div class="row form-group">
												<div class="custom-control custom-radio col-md-5">
													<input type="radio" id="COMPANY_JOB_OPEN" name="COMPANY_JOB" value="1" class="custom-control-input" >
													<label class="custom-control-label" for="COMPANY_JOB_OPEN"><?=COMPANIES_WITH_OPEN_JOBS?></label>
												</div>
												<div class="custom-control custom-radio col-md-5">
													<input type="radio" id="COMPANY_JOB_NOT_OPEN" name="COMPANY_JOB" value="2" class="custom-control-input" >
													<label class="custom-control-label" for="COMPANY_JOB_NOT_OPEN"><?=COMPANIES_WITHOUT_OPEN_JOBS?></label>
												</div>
												<div class="custom-control custom-radio col-md-2">
													<input type="radio" id="COMPANY_JOB_BOTH" name="COMPANY_JOB" value="3" class="custom-control-input" checked >
													<label class="custom-control-label" for="COMPANY_JOB_BOTH"><?=BOTH?></label>
												</div>
											</div>
										</div>
										
										<div class="col-md-3">
											<?=COMPANY_SOURCE?>
											<select id="PK_COMPANY_SOURCE" name="PK_COMPANY_SOURCE[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_COMPANY_SOURCE, COMPANY_SOURCE from M_COMPANY_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY COMPANY_SOURCE ASC ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COMPANY_SOURCE']?>" ><?=$res_type->fields['COMPANY_SOURCE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-9" style="text-align:right" >
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
		$('#PK_PLACEMENT_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		$('#PK_PLACEMENT_COMPANY_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_COMPANY_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PLACEMENT_COMPANY_STATUS?> selected'
		});
		$('#PK_COMPANY_SOURCE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COMPANY_SOURCE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=COMPANY_SOURCE?> selected'
		});
		
	});
	</script>

</body>

</html>