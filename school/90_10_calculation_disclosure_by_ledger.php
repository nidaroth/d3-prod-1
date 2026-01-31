<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['_90_10'] == 0 || check_access('MANAGEMENT_90_10') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){

	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$ST = '';
	$ET = '';
	if($_POST['START_DATE'] != '')
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		
	if($_POST['END_DATE'] != '')
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
	
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

	//TRUE - Program Group
	//False - Program
	if($_POST['RUN_BY'] == 1 || $_POST['RUN_BY'] == 3)
		$BY_PROGRAM = 'FALSE';
	else if($_POST['RUN_BY'] == 2)
		$BY_PROGRAM = 'TRUE';

	//echo CALL ACCT90103(97, '175', '2025-01-01','2025-11-25', FALSE);
	$res = $db->Execute("CALL ACCT90103(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."', $BY_PROGRAM)");
	$BATCH_ID = $res->fields['BatchID'];
	
	$db->close();
	//$db->connect($db_host,'root',$db_pass,$db_name);
	$db->connect($DB_HOST, $DB_USER, $DB_PASS, $DB_DATABASE); //DIAM-1680
	
	if($_POST['RUN_BY'] == 1 || $_POST['RUN_BY'] == 2){
		$res = $db->Execute("SELECT DISTINCT(PROGRAM) as PROGRAM FROM S_TEMP_ACCT90103 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ORDER BY PROGRAM ASC");
		while (!$res->EOF) {
			$PROGRAM_ARR[] = $res->fields['PROGRAM'];
			$res->MoveNext();
		}
	} else {
		$PROGRAM_ARR[] = '';
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
				global $db,$campus_name;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(8);
				$this->SetX(55);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(55, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 13);
				$this->SetY(10);
				$this->SetX(115);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(55, 8, "90/10 Calculation Disclosure by Ledger Code", 0, false, 'L', 0, '', 0, false, 'M', 'L');
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(115, 13, 207, 13, $style);
				
				$str = "Transactions between: ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];

				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(16);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$this->SetFont('helvetica', 'I', 8);
				$this->SetY(18);
				$this->SetX(100);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, $campus_name, 0, 'R', 0, 0, '', '', true);
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
				
				$this->SetY(-15);
				$this->SetX(100);
				$this->Cell(30, 10, 'ACCT90102', 0, false, 'C', 0, '', 0, false, 'T', 'M');
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
		

		$total 	= 0;
		$txt 	= '';
				
		foreach($PROGRAM_ARR as $PROGRAM){
			$pdf->AddPage();
			
			if($_POST['RUN_BY'] == 1 || $_POST['RUN_BY'] == 2)
				$prog_cond = " AND PROGRAM = '$PROGRAM' ";
			else
				$prog_cond = " ";
			
			$CATEGORY_ARR = array();
			$res = $db->Execute("SELECT DISTINCT(CATEGORY) as CATEGORY FROM S_TEMP_ACCT90103 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' $prog_cond ORDER BY CATEGORY ASC");
			while (!$res->EOF) {
				$CATEGORY_ARR[] = $res->fields['CATEGORY'];
				$res->MoveNext();
			}
			
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%" ><b style="font-size:40px" >'.$PROGRAM.'</b></td>
						</tr>
					</table>';
			
			$sub_total = 0;
			foreach($CATEGORY_ARR as $CATEGORY){
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="100%" ><b style="font-size:30px" >'.$CATEGORY.'</b></td>
								</tr>
								<tr>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Ledger Code</b></td>
									<td width="30%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Description</b></td>
									<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Type</b></td>
									<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Title IV</b></td>
									<td width="10%" style="border-top:1px solid #000;border-bottom:1px solid #000;" ><b>Active</b></td>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" ><b>Amount</b></td>
								</tr>
							</thead>';
				
				$sub_total = 0;
				$res_led = $db->Execute("SELECT SUM(AMOUNT) as AMOUNT, LEDGER_CODE, LEDGER_DESCRIPTION, LEDGER_TYPE, TITLE_IV, ACTIVE FROM S_TEMP_ACCT90103 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' $prog_cond AND CATEGORY = '$CATEGORY' GROUP BY LEDGER_CODE ORDER BY LEDGER_CODE ASC");
				while (!$res_led->EOF) {
					if($res_led->fields['AMOUNT'] > 0)
						$AMOUNT = number_format_value_checker($res_led->fields['AMOUNT'],2);
					else {
						$AMOUNT = '('.number_format_value_checker(abs($res_led->fields['AMOUNT']),2).')';
					}
					$sub_total += $res_led->fields['AMOUNT'];
					$txt 	.= '<tr>
								<td width="20%" >'.$res_led->fields['LEDGER_CODE'].'</td>
								<td width="30%" >'.$res_led->fields['LEDGER_DESCRIPTION'].'</td>
								<td width="10%" >'.$res_led->fields['LEDGER_TYPE'].'</td>
								<td width="10%" >'.$res_led->fields['TITLE_IV'].'</td>
								<td width="10%" >'.$res_led->fields['ACTIVE'].'</td>
								<td width="20%" align="right" >'.$AMOUNT.'</td>
							</tr>';
							
					$res_led->MoveNext();
				}
				if($sub_total > 0)
					$AMOUNT = number_format_value_checker($sub_total,2);
				else {
					$AMOUNT = '('.number_format_value_checker(abs($sub_total),2).')';
				}
				$txt 	.= '<tr>
								<td width="20%" ></td>
								<td width="20%" ></td>
								<td width="20%" ></td>
								<td width="20%" style="border-top:1px solid #000" ><b>Total</b></td>
								<td width="20%" style="border-top:1px solid #000" align="right" ><b>'.$AMOUNT.'</b></td>
							</tr>';
				$txt 	.= '</table>';
			}
			
			//echo $txt;exit;
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		}
		
		$db->Execute("DELETE FROM S_TEMP_ACCT90103 WHERE BATCH_ID = '$BATCH_ID' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$file_name = '90_10 Calculation Disclosure By Leader Code.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
	} else if($_POST['FORMAT'] == 2) {
		//<!--DIAM-1680-->
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
		$file_name 		= '90_10 Calculation Disclosure By Leader Code.xlsx';
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
		$index 	= 0;
		
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Campus: ".$campus_name);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);

		$line++;	
		$index 	= -1;
		
		if(!empty($PROGRAM_ARR[0])){
		$heading[] = 'Program';
		$width[]   = 20;
		}
		$heading[] = 'Category';
		$width[]   = 20;
		$heading[] = 'Ledger Code';
		$width[]   = 20;
		$heading[] = 'Description';
		$width[]   = 20;
		$heading[] = 'Type';
		$width[]   = 20;
		$heading[] = 'Title IV';
		$width[]   = 20;
		$heading[] = 'Active';
		$width[]   = 20;
		$heading[] = 'Amount';
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

		$total 	= 0;
		foreach($PROGRAM_ARR as $PROGRAM){
				
		if($_POST['RUN_BY'] == 1 || $_POST['RUN_BY'] == 2)
			$prog_cond = " AND PROGRAM = '$PROGRAM' ";
		else
			$prog_cond = " ";

			$CATEGORY_ARR = array();
			$res = $db->Execute("SELECT DISTINCT(CATEGORY) as CATEGORY FROM S_TEMP_ACCT90103 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' $prog_cond ORDER BY CATEGORY ASC");
			while (!$res->EOF) {
				$CATEGORY_ARR[] = $res->fields['CATEGORY'];
				$res->MoveNext();
			}
			
			foreach($CATEGORY_ARR as $CATEGORY){
				$sub_total = 0;
				$res_led = $db->Execute("SELECT SUM(AMOUNT) as AMOUNT, LEDGER_CODE, LEDGER_DESCRIPTION, LEDGER_TYPE, TITLE_IV, ACTIVE,CATEGORY,PROGRAM FROM S_TEMP_ACCT90103 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' $prog_cond AND CATEGORY = '$CATEGORY' GROUP BY LEDGER_CODE ORDER BY LEDGER_CODE ASC");
				while (!$res_led->EOF) {
						$line++;
						$index = -1;
						if(!empty($PROGRAM_ARR[0])){
						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_led->fields['PROGRAM']);
						}

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_led->fields['CATEGORY']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_led->fields['LEDGER_CODE']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_led->fields['LEDGER_DESCRIPTION']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_led->fields['LEDGER_TYPE']);

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_led->fields['TITLE_IV']);


						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_led->fields['ACTIVE']);

						if($res_led->fields['AMOUNT'] > 0)
						$AMOUNT = number_format_value_checker($res_led->fields['AMOUNT'],2);
						else {
							//$AMOUNT = '('.number_format_value_checker(abs($res_led->fields['AMOUNT']),2).')';
							$AMOUNT = number_format_value_checker($res_led->fields['AMOUNT'],2);
						}
						$sub_total += $res_led->fields['AMOUNT'];

						$index++;
						$cell_no = $cell[$index].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($AMOUNT);
							
							
						$res_led->MoveNext();
					}

						if($sub_total > 0)
						$AMOUNT = number_format_value_checker($sub_total,2);
						else {
						$AMOUNT = '('.number_format_value_checker(abs($sub_total),2).')';
						}

						$line++;
						$index++;
						$cell_no = $cell[$index-2].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Total");
						$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);

						
						$index++;
						$cell_no = $cell[$index-2].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($AMOUNT);

						$line++;
						$index++;
						$cell_no = $cell[$index-2].$line;
						$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("");
						
				}
						

			}
					
		$db->Execute("DELETE FROM S_TEMP_ACCT90103 WHERE BATCH_ID = '$BATCH_ID' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
		
	}
	//<!--DIAM-1680-->
	
	
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
	<title><?=MNU_90_10_CALCULATION_DISCLOSURE_BY_LEDGER ?> | <?=$title?></title>
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
							<?=MNU_90_10_CALCULATION_DISCLOSURE_BY_LEDGER ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-3 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-3">
											<?=RUN_BY?>
											<select id="RUN_BY" name="RUN_BY" class="form-control" >
												<option value="1" >Program - One Program per page</option>
												<option value="2" >Program Group - One Program Group per page</option>
												<option value="3" >All - All Programs Combined on one page</option>
											</select>
										</div>
										
										<div class="col-md-2 ">
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
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button><!--DIAM-1680-->
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
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
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
