<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/expected_transaction.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0 || check_access('MANAGEMENT_DIAMOND_PAY') == 0  ) {
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
	
	if($_POST['FORMAT'] == 1) {
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
				$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I',18);
				$this->SetY(8);
				$this->SetX(130);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "Expected Transactions", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(120, 13, 202, 13, $style);
				
				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = "Between ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = "From ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = "To ".$_POST['END_DATE'];
					
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Disbursement Dates: ".$str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetY(20);
				$this->SetX(98);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				
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

		$txt  = '';
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="18%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student</td>
							<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student ID</td>
							<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Campus</td>
							<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student Status</td>
							<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Ledger Code</td>
							<td width="12%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Disbursement Date</td>
							<td width="15%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Disbursement Amount</td>
						</tr>
					</thead>';

		$res = $db->Execute($_SESSION['query']);
		while (!$res->EOF) {
			$CARD_NO = "";
			if($res->fields['CARD_NO'] != '')
				$CARD_NO = substr($res->fields['CARD_NO'],-4);
				
			$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
			$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  $campus_cond1 ");
				
			$txt .= '<tr>
						<td width="18%" >'.$res->fields['NAME'].'</td>
						<td width="10%" >'.$res->fields['STUDENT_ID'].'</td>
						<td width="15%" >'.$res_campus->fields['CAMPUS_CODE'].'</td>
						<td width="15%" >'.$res->fields['STUDENT_STATUS'].'</td>
						<td width="15%" >'.$res->fields['LEDGER'].'</td>
						<td width="12%" >'.$res->fields['DISBURSEMENT_DATE_1'].'</td>
						<td width="15%" align="right" >$ '.number_format_value_checker($res->fields['DISBURSEMENT_AMOUNT'],2).'</td>
					</tr>';
					
			$res->MoveNext();
		}
		$txt .= '</table>';
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Expected Transactions.pdf';
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
		$file_name 		= "Expected Transactions.xlsx";
		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
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

		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Student Status';
		$width[]   = 20;
		$heading[] = 'Ledger Code';
		$width[]   = 20;
		$heading[] = 'Disbursement Date';
		$width[]   = 20;
		$heading[] = 'Disbursement Amount';
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

		$res = $db->Execute($_SESSION['query']);
		while (!$res->EOF) {
	
			$line++;
			$index = -1;
			
			$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
			$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  $campus_cond1 ");
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LEDGER']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DISBURSEMENT_DATE_1']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DISBURSEMENT_AMOUNT']);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode("0.00");
			
			$res->MoveNext();
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	}
	exit;
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
	<title><?=MNU_EXPECTED_TRANSACTION ?> | <?=$title?></title>
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
                        <h4 class="text-themecolor">
						<? echo MNU_EXPECTED_TRANSACTION ?> </h4>
                    </div>
                </div>
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row" style="margin-bottom: 20px;" >
										<div class="col-md-2 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-3 ">
											<?=STUDENT_STATUS?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1  AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="row" style="margin-bottom: 20px;" >
										<div class="col-md-3 ">
											<?=QUICK_PAYMENT_LEDGER_CODE?>
											<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND QUICK_PAYMENT = 1 AND TYPE = 1 order by CODE ASC"); //Ticket # 1431
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="row" style="margin-bottom: 20px;" >
										<div class="col-md-2 ">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2 ">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2 ">
											<br />
											<button type="button" onclick="do_search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
											
											<button type="button" onclick="submit_form(1)" id="PDF_DIV" style="display:none" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="EXCEL_DIV" style="display:none" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<br />
									<div id="SEARCH_DIV" >
										<br /><br /><br /><br />
									</div>
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
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function do_search(){
			jQuery(document).ready(function($) { 
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true){ 
					var START_DATE 			= document.getElementById('START_DATE').value;
					var END_DATE 			= document.getElementById('END_DATE').value;
					var PK_AR_LEDGER_CODE 	= $('#PK_AR_LEDGER_CODE').val();
					var PK_STUDENT_STATUS 	= $('#PK_STUDENT_STATUS').val();
					var PK_CAMPUS 			= $('#PK_CAMPUS').val();

					var data  = 'START_DATE='+START_DATE+'&END_DATE='+END_DATE+'&PK_AR_LEDGER_CODE='+PK_AR_LEDGER_CODE+'&PK_STUDENT_STATUS='+PK_STUDENT_STATUS+'&PK_CAMPUS='+PK_CAMPUS;
					var value = $.ajax({
						url: "ajax_get_expected_transaction",
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('SEARCH_DIV').innerHTML 	= data;
							document.getElementById('PDF_DIV').style.display 	= 'inline';
							document.getElementById('EXCEL_DIV').style.display 	= 'inline';
							
							$('#report_1').bootstrapTable({})
						}		
					}).responseText;
				}
			});
		}
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
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_AR_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=LEDGER_CODE?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=LEDGER_CODE?> selected'
		});
		
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
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