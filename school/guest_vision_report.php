<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if (check_access('MANAGEMENT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}
$report_error = "";

$res_type1 = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");


if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

	$res_campus  = $db->Execute("select PK_CAMPUS, CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$_POST[PK_CAMPUS]' ");
	$CAMPUS_CODE = $res_campus->fields['CAMPUS_CODE'];
	$PK_CAMPUS = $res_campus->fields['PK_CAMPUS'];


	$timezone = $_SESSION['PK_TIMEZONE'];
	if ($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if ($timezone == '' || $timezone == 0)
			$timezone = 4;
	}

	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];


	if ($_POST['FORMAT'] == 1) {
		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		$cell1  = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		$total_fields = 120;
		for ($i = 0; $i <= $total_fields; $i++) {
			if ($i <= 25)
				$cell[] = $cell1[$i];
			else {
				$j = floor($i / 26) - 1;
				$k = ($i % 26);
				//echo $j."--".$k."<br />";
				$cell[] = $cell1[$j] . $cell1[$k];
			}
		}

		$time = convert_to_user_date(date('Y-m-d H:i:s'), 'Y-m-d H-i', $TIMEZONE, date_default_timezone_get());
		$dir 			= 'temp/';
		$inputFileType  = 'CSV';
		$file_name 		= 'GuestVision Export.csv';
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(
			pathinfo($outputFileName, PATHINFO_FILENAME),
			pathinfo($outputFileName, PATHINFO_FILENAME) . " " . $_SESSION['PK_USER'] . " " . time(),
			$outputFileName
		);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		// $objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');

		$line 	= 1;
		$index 	= -1;
		// CALL FINA10001(80,0,'139', DATE_FORMAT(NOW(),'%Y-%m-%d'),180);
		$newDate = date("Y-m-d");
		if (!empty($_POST['PK_CAMPUS'])) {
			$PK_CAMPUS = implode(",", $_POST['PK_CAMPUS']);
			$campus_cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}
		$res = $db->Execute("
		SELECT P.CODE AS ProgramCode
		,P.DESCRIPTION AS ProgramDescription
		,'Catherine Hinds' AS School
		,S.SSN
		,S.LAST_NAME AS LastName
		,S.FIRST_NAME AS FirstName
		,SC.ADDRESS AS AddressLine1
		,SC.ADDRESS_1 AS AddressLine2
		,SC.CITY
		,STATE.STATE_CODE AS State
		,SC.ZIP PostalCode
		,SC.HOME_PHONE AS PhoneNumber_Home
		,SC.CELL_PHONE AS PhoneNumber_Auxiliary
		,SC.EMAIL AS  EmailAddress
		,CASE WHEN COALESCE(S.DATE_OF_BIRTH,'0000-00-00') = '0000-00-00' THEN '' ELSE DATE_FORMAT(S.DATE_OF_BIRTH,'%m/%d/%Y') END AS DateOfBirth
		,CASE WHEN COALESCE(SE.STATUS_DATE,'0000-00-00') = '0000-00-00' THEN '' ELSE DATE_FORMAT(SE.STATUS_DATE,'%m/%d/%Y') END AS StudentStatusDate
		,SS.STUDENT_STATUS AS StudentStatus
		,CASE WHEN COALESCE(SE.GRADE_DATE,'0000-00-00') = '0000-00-00' THEN '' ELSE DATE_FORMAT(SE.GRADE_DATE,'%m/%d/%Y') END AS GradDate
		,SA.STUDENT_ID AS Code
		FROM S_STUDENT_MASTER AS S
		INNER JOIN S_STUDENT_ACADEMICS AS SA ON S.PK_STUDENT_MASTER = SA.PK_STUDENT_MASTER
		INNER JOIN S_STUDENT_CONTACT AS SC ON S.PK_STUDENT_MASTER = SC.PK_STUDENT_MASTER
		INNER JOIN S_STUDENT_ENROLLMENT AS SE ON S.PK_STUDENT_MASTER = SE.PK_STUDENT_MASTER
		INNER JOIN M_CAMPUS_PROGRAM AS P On SE.PK_CAMPUS_PROGRAM = P.PK_CAMPUS_PROGRAM
		INNER JOIN M_STUDENT_STATUS AS SS ON SE.PK_STUDENT_STATUS = SS.PK_STUDENT_STATUS
		LEFT JOIN Z_STATES AS STATE ON SC.PK_STATES = STATE.PK_STATES
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = SE.PK_STUDENT_ENROLLMENT
		WHERE S.PK_ACCOUNT = '" . $_SESSION['PK_ACCOUNT'] . "' 
		$campus_cond
		AND SS.ALLOW_ATTENDANCE = 1
		AND SC.PK_STUDENT_CONTACT_TYPE_MASTER = 1
		ORDER BY S.LAST_NAME, S.FIRST_NAME;");

		if ($res->fields['ERROR']) {
			$report_error = $res->fields['ERROR'];
		} else {
			$heading = array_keys($res->fields);
			//print_r($heading);	
 
			$header_arr = ["Program" , "Program Description" , "School" , "SSN" , "Last Name" , "First Name" , "Address Line 1","Address Line 2" , "City" , "State" , "Postal Code" , "Phone Number Home" , "Phone Number Auxiliary","Email","Date Of Birth","Status Date","Status","Graduation Date" , "Code"];
			
			foreach ($header_arr as $head) { 
				$index++;
				$cell_no = $cell[$index] . $line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($head);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
				$objPHPExcel->getActiveSheet()->freezePane('A1');
				
				
			}
	 
			$line++;
		 
			while (!$res->EOF) {
				$index 	= -1;
				foreach ($heading as $key) {
					if ($key != 'ROW_TYPE') {
						//Get Header column name and set styling 
						if ($line == 1) {
							$index++;
							$cell_no = $cell[$index] . $line;
							$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
							$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							$objPHPExcel->getActiveSheet()->freezePane('A1');
						} else {
							// Get data column value and set data
							$index++;
							$cell_no = $cell[$index] . $line;
							$cellValue = $res->fields[$key];
							if ($key == 'SSN') {
								$SSN 		= $res->fields['SSN'];
								if($SSN != ''){
									// $cellValue = "\t000000000";
									$cellValue = "\t000000000";
								}
								// $cellValue 	= my_decrypt('', $SSN);
							} 
							// $objPHPExcel->getActiveSheet()->setCellValue($cell_no, '="'.$cellValue.'"');
							// $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getNumberFormat()->setFormatCode('000000000');
							$objPHPExcel->getActiveSheet()->setCellValueExplicit($cell_no,$cellValue, PHPExcel_Cell_DataType::TYPE_STRING);
							 
						}
					} else {
						echo 'Skip header';
					}
				}

				$line++;

				$res->MoveNext();
			}
			//exit;
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:" . $outputFileName);
		}
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
	<title> GuestVision Export| <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
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
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor">GuestVision Export</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
								<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="">
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-12 ">
											<div class="row">

												<div class="col-2 col-sm-2" id="PK_CAMPUS_DIV">
													<div class="form-group m-b-40">
														<select id="PK_CAMPUS" name="PK_CAMPUS[]" class="form-control" multiple>
															<?
															while (!$res_type1->EOF) {
																if ($res_type1->RecordCount() == 1)
																	$selected = 'selected'; ?>
																<option value="<?= $res_type1->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type1->fields['CAMPUS_CODE'] ?></option>
															<? $res_type1->MoveNext();
															} ?>
														</select>

														<span class="bar"></span>
														<!-- <label for="PK_CAMPUS"><?= CAMPUS ?></label> -->
													</div>
												</div>


												<div class="col-3 col-sm-3 ">
													<button type="button" onclick="submit_form(1)" id="EXCEL_BTN" class="btn waves-effect waves-light btn-info">CSV</button>
													<input type="hidden" name="FORMAT" id="FORMAT">
												</div>

											</div>

										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>

			</div>
		</div>

		<? require_once("footer.php"); ?>

		<?php if ($report_error != "") { ?>
			<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1">Warning</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" style="color: red;font-size: 15px;">
								<b><?php echo $report_error; ?></b>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

	</div>

	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script type="text/javascript">
		var error = '<?php echo  $report_error; ?>';
		jQuery(document).ready(function($) {
			if (error != "") {
				jQuery('#errorModal').modal();
			}

			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= "Campus" ?>',
				nonSelectedText: '<?= "Campus" ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= "Campus" ?> selected'
			});
		});


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

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {

			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '<?= STATUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= STATUS ?> selected'
			});

			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '<?= ALL_FIRST_TERM ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= FIRST_TERM ?> selected'
			});

			$('#PK_STUDENT_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= GROUP_CODE ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= GROUP_CODE ?> selected'
			});

			$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '<?= ALL_PROGRAM ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= PROGRAM ?> selected'
			});
		});
	</script>

	<?php $report_error = ""; ?>

</body>

</html>