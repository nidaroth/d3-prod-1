<?
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/account_ledger_report.php");
require_once("check_access.php");

if(check_access('REPORT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	if($_POST['DATE_TYPE'] == 1) {
		$DATE_TYPE = "Transaction Date";
	} else if($_POST['DATE_TYPE'] == 2) {
		$DATE_TYPE = "Created Date";
	}
	
	if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	}
	if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
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
	
	$DETAIL 	= 'FALSE';
	$PROGRAM 	= 'FALSE';
	if($_POST['EXPORT_TYPE'] == 2)
		$PROGRAM = 'TRUE';
	else if($_POST['EXPORT_TYPE'] == 3)
		$DETAIL = 'TRUE';
	else if($_POST['EXPORT_TYPE'] == 4)	{
		$DETAIL  = 'TRUE';
		$PROGRAM = 'TRUE';
	}

	if($_POST['FORMAT'] == 1) {
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		
		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

		/*$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 30, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->SetFont('helvetica', '', 8, '', true);
		$pdf->setFontSubsetting(false) ;
		
		$pdf->AddPage();*/
		
		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			
		$report_name = "";
		if($_POST['EXPORT_TYPE'] == 1) {
			$report_name = "Quickbooks Review";
		} else if($_POST['EXPORT_TYPE'] == 2) {
			$report_name = "QuickbooksWith Program Review";
		} else if($_POST['EXPORT_TYPE'] == 3) {
			$report_name = "Quickbooks Detail Review";
		} else if($_POST['EXPORT_TYPE'] == 4) {
			$report_name = "Quickbooks Detail With Program Review";
		}
			
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>'.$report_name.'</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Date Range: '.$_POST['START_DATE']." - ".$_POST['END_DATE'].'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Date Type: '.$DATE_TYPE.'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus: '.$campus_name.'</td>
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
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>ACCT11010</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
						</tr>
					</table>';
		
		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => 35,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 9
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<thead>
						<tr>';
							if($_POST['EXPORT_TYPE'] == 3 || $_POST['EXPORT_TYPE'] == 4) {
								$txt .= '<td width="18%" style="border-bottom:1px solid #000;">
											<b><i>Name</i></b>
										</td>';
							}
							$txt .= '<td width="12%" style="border-bottom:1px solid #000;">
								<b><i>Date</i></b>
							</td>
							<td width="22%" style="border-bottom:1px solid #000;">
								<b><i>Account</i></b>
							</td>
							<td width="25%" style="border-bottom:1px solid #000;">
								<b><i>Description</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Amount</i></b>
							</td>';
							if($_POST['EXPORT_TYPE'] == 3 || $_POST['EXPORT_TYPE'] == 4) {
								$txt .= '<td width="10%" style="border-bottom:1px solid #000;">
											<b><i>Payment Type</i></b>
										</td>
										<td width="10%" style="border-bottom:1px solid #000;">
											<b><i>Reference Number</i></b>
										</td>';
							}
							
							if($_POST['EXPORT_TYPE'] == 1 || $_POST['EXPORT_TYPE'] == 2) {
								$txt .= '<td width="13%" style="border-bottom:1px solid #000;">
											<b><i>Exported Date</i></b>
										</td>';
							}
							if($_POST['EXPORT_TYPE'] == 2 || $_POST['EXPORT_TYPE'] == 4) {
								$txt .= '<td width="15%" style="border-bottom:1px solid #000;">
											<b><i>Program Code</i></b>
										</td>';
							}
							
				$txt .= '</tr>
					</thead>';
		$balance = 0;
		$res = $db->Execute("CALL ACCT11010(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."',$_POST[INCLUDE_ALREADY_EXPORTED] ,$DETAIL,$PROGRAM,'".$DATE_TYPE."',TRUE)");
		$printed = 0;
		
		while (!$res->EOF) { 
			//echo "<pre>";print_r($res);exit;
			
			$balance += $res->fields['Amount'];
			$txt .= '<tr>';
					if($_POST['EXPORT_TYPE'] == 3 || $_POST['EXPORT_TYPE'] == 4) {
						$txt .= '<td >'.$res->fields['STUDENT_NAME'].'</td>';
					}
			$txt .= '<td >'.$res->fields['Date'].'</td>
					<td >'.$res->fields['Account'].'</td>
					<td >'.$res->fields['Description'].'</td>
					<td align="right" >$ '.number_format_value_checker($res->fields['Amount'],2).'</td>';
					
					if($_POST['EXPORT_TYPE'] == 3 || $_POST['EXPORT_TYPE'] == 4) {
						$txt .= '<td >'.$res->fields['Payment Type'].'</td>';
						$txt .= '<td >'.$res->fields['Reference Number'].'</td>';
					}
							
					if($_POST['EXPORT_TYPE'] == 1 || $_POST['EXPORT_TYPE'] == 2) {
						$txt .= '<td >'.$res->fields['ExportDate'].'</td>';
					}
					if($_POST['EXPORT_TYPE'] == 2 || $_POST['EXPORT_TYPE'] == 4) {
						$txt .= '<td  >'.$res->fields['Program'].'</td>';
					}
				$txt .= '</tr>';
			$res->MoveNext();
		}
		
		if($_POST['EXPORT_TYPE'] == 3 || $_POST['EXPORT_TYPE'] == 4)
			$colspan = "4";
		else
			$colspan = "3";
		$txt .= '<tr>
					<td colspan="'.$colspan.'" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right"><b><i>Balance</i></b></td>
					<td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>$ '.number_format_value_checker(round($balance,2),2).'</i></b></td>
					<td style="border-top:1px solid #000;border-bottom:1px solid #000;"></td>';
					if($_POST['EXPORT_TYPE'] == 2 || $_POST['EXPORT_TYPE'] == 4) {
						$txt .= '<td style="border-top:1px solid #000;border-bottom:1px solid #000;"></td>';
					}
					if($_POST['EXPORT_TYPE'] == 3 || $_POST['EXPORT_TYPE'] == 4) {
						$txt .= '<td style="border-top:1px solid #000;border-bottom:1px solid #000;"></td>';
					}
				$txt .= '</tr>
			</table>';

		if($_POST['EXPORT_TYPE'] == 1)
			$file_name = 'Quickbooks Review.pdf';
		else if($_POST['EXPORT_TYPE'] == 2)
			$file_name = 'Quickbooks With Program Review.pdf';
		else if($_POST['EXPORT_TYPE'] == 3)
			$file_name = 'Quickbooks Detail Review.pdf';
		else if($_POST['EXPORT_TYPE'] == 4)
			$file_name = 'Quickbooks Detail With Program Review.pdf';

		$mpdf->WriteHTML($txt);
		$mpdf->Output($file_name, 'D');
		
	} else if($_POST['FORMAT'] == 2) {
		
		if($_POST['EXPORT_TYPE'] == 1)
			$file_name = 'Quickbooks.txt';
		else if($_POST['EXPORT_TYPE'] == 2)
			$file_name = 'Quickbooks With Program.txt';
		else if($_POST['EXPORT_TYPE'] == 3)
			$file_name = 'Quickbooks Detail.txt';
		else if($_POST['EXPORT_TYPE'] == 4)	
			$file_name = 'Quickbooks Detail With Program.txt';
			
		$iff_txt = "";
		
		$res = $db->Execute("CALL ACCT11010(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."',$_POST[INCLUDE_ALREADY_EXPORTED] ,$DETAIL,$PROGRAM,'".$DATE_TYPE."',FALSE)");
		while (!$res->EOF) {
			$iff_txt .= $res->fields['QB']."\r";
			
			$res->MoveNext();
		}

		//$file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
		$file_dir_1 = '../backend_assets/tmp_upload/';
		
		$file_name1 = $file_dir_1.$file_name;
		
		unlink($file_name1);
		file_put_contents($file_name1,$iff_txt);
		$fh = fopen($file_name1,'a');
		
		fwrite($fh,'');	
		fclose($fh);
		
		/*$file_p = fopen($file_name1,"w");
		fwrite($file_p,$iff_txt);
		fclose($file_p);*/

		$page_content = file_get_contents($file_name1);
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="'.$file_name);
		echo $page_content;
		exit;
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
	<title><?=ACCOUNTING_LEDGER_EXPORT_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
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
							<?=ACCOUNTING_LEDGER_EXPORT_TITLE?>
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
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2">
											<?=DATE_TYPE?>
											<select id="DATE_TYPE" name="DATE_TYPE" class="form-control" >
												<option value="1">By Trans Date</option>
												<option value="2">By Created Date</option>
											</select>
										</div>
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2" style="flex: 0 0 20.667%;max-width: 20.667%;" >
											<?=EXPORT_TYPE ?>
											<select id="EXPORT_TYPE" name="EXPORT_TYPE" class="form-control" >
												<option value="1" >Quickbooks</option>
												<option value="2" >Quickbooks with Program</option>
												<option value="3" >Quickbooks Detail</option>
												<option value="4" >Quickbooks Detail with Program</option>
											</select>
										</div>
										
										<div class="col-md-2" style="flex: 0 0 12.667%;max-width: 12.667%;"  >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXPORT?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
											<input type="hidden" name="INCLUDE_ALREADY_EXPORTED" id="INCLUDE_ALREADY_EXPORTED" value="TRUE" >
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=PREVIOUSLY_EXPORTED_TRANSACTION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="exported_msg" style="display:none" ><?=PREVIOUSLY_EXPORTED_TRANSACTION_MSG?></div>
						<div class="form-group" id="pdf_msg" style="display:none"  ><?=PREVIOUSLY_REVIEW_TRANSACTION_MSG?></div>
						
						<input type="hidden" name="FORMAT_1" id="FORMAT_1" >
					</div>
					<div class="modal-footer">
						<div id="exported_btn" style="display:none" >
							<button type="button" onclick="conf_submit_form(1)" class="btn waves-effect waves-light btn-info"><?=EXPORT_ALL?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_submit_form(0)" ><?=EXPORT_NEW?></button>
						</div>
						<div id="pdf_btn" style="display:none" >
							<button type="button" onclick="conf_submit_form(1)" class="btn waves-effect waves-light btn-info"><?=REVIEW_ALL?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_submit_form(0)" ><?=REVIEW_NEW?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
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
	
	function check_trans(val){
		jQuery(document).ready(function($) {
			var data  = 'DATE_TYPE='+$('#DATE_TYPE').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val();
			var value = $.ajax({
				url: "ajax_check_ledger_transaction",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					//alert(data)
					if(data == "a")
						document.form1.submit();
					else {
						if(document.getElementById('FORMAT').value == 1) {
							document.getElementById('pdf_msg').style.display = 'block'
							document.getElementById('pdf_btn').style.display = 'block'
							
							document.getElementById('exported_msg').style.display = 'none'
							document.getElementById('exported_btn').style.display = 'none'
						} else {
							document.getElementById('pdf_msg').style.display = 'none'
							document.getElementById('pdf_btn').style.display = 'none'
							
							document.getElementById('exported_msg').style.display = 'block'
							document.getElementById('exported_btn').style.display = 'block'
						}
						show_popup(val)
					}
				}		
			}).responseText;
		});
	}
	
	function show_popup(val){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#FORMAT_1").val(val)
		});
	}
	
	function conf_submit_form(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				document.getElementById('INCLUDE_ALREADY_EXPORTED').value = 'TRUE';
			} else 
				document.getElementById('INCLUDE_ALREADY_EXPORTED').value = 'FALSE';
			
			$("#deleteModal").modal("hide");
			var val1 = $("#FORMAT_1").val();
			document.form1.submit();
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
				//document.form1.submit();
				check_trans(val)
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