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

	// $res_campus  = $db->Execute("select PK_CAMPUS, CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$_POST[PK_CAMPUS]' ");
	// $CAMPUS_CODE = $res_campus->fields['CAMPUS_CODE'];
	$PK_CAMPUS = implode(',', $_POST['PK_CAMPUS']);
	$PK_TERM_MASTER = implode(',', $_POST['PK_TERM_MASTER']);
	$PK_CAMPUS_PROGRAM = implode(',', $_POST['PK_CAMPUS_PROGRAM']);
	$PK_STUDENT_STATUS = implode(',', $_POST['PK_STUDENT_STATUS']);
	$PK_FUNDING = implode(',', $_POST['PK_FUNDING']);

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
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Campus Ivy Report.xlsx';
		$outputFileName = $dir . $file_name;
		$outputFileName = str_replace(
			pathinfo($outputFileName, PATHINFO_FILENAME),
			pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
			$outputFileName
		);

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;
		$index 	= -1;
		// CALL FINA10001(80,0,'139', DATE_FORMAT(NOW(),'%Y-%m-%d'),180);
		$params = ' "' . $PK_CAMPUS . '" , "' . $PK_TERM_MASTER . '" , "' . $PK_CAMPUS_PROGRAM . '" , "' . $PK_STUDENT_STATUS . '" , "' . $PK_FUNDING . '"';

		$array_co_students = "";
		if (!empty($_REQUEST['PK_TERM_MASTER1'])) {
			$sQuery_Stud_assing_course = "SELECT S_STUDENT_COURSE.PK_STUDENT_MASTER, S_TERM_MASTER.PK_TERM_MASTER, S_TERM_MASTER.TERM_DESCRIPTION FROM S_STUDENT_COURSE INNER JOIN S_COURSE_OFFERING ON S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING INNER JOIN S_TERM_MASTER ON S_COURSE_OFFERING.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER INNER JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond_co AND S_TERM_MASTER.PK_TERM_MASTER IN (" . $PK_TERM_MASTER1 . ") GROUP BY S_STUDENT_COURSE.PK_STUDENT_MASTER";
			$res_co_stud = $db->Execute($sQuery_Stud_assing_course);


			$array_co_students = array();
			while (!$res_co_stud->EOF) {
				$array_co_students[] = $res_co_stud->fields['PK_STUDENT_MASTER'];
				$res_co_stud->MoveNext();
			}
			$array_co_students = implode(",", $array_co_students);
		}
		$newDate = date("Y-m-d");
		if ($_SESSION['PK_ACCOUNT'] == 63) {
			$caller = "CALL FINA10001_WVJC(" . $_SESSION['PK_ACCOUNT'] . "," . $_SESSION['PK_USER'] . " , " . $params . " , '" .  $newDate . "',180,'" .  $array_co_students . "')";
		} else {
			$caller = "CALL FINA10001(" . $_SESSION['PK_ACCOUNT'] . "," . $_SESSION['PK_USER'] . " , " . $params . " , '" .  $newDate . "',180)";
		}
		// echo $caller;

		// echo "<br>";
		// echo "CALL FINA10001(".$_SESSION['PK_ACCOUNT'].",".$_SESSION['PK_USER'].",".$PK_CAMPUS.", ".$newDate.",180)";
		// exit;
		$res = $db->Execute($caller);

		if ($res->fields['ERROR']) {
			$report_error = $res->fields['ERROR'];
		} else {
			$heading = array_keys($res->fields);
			//print_r($heading);	

			while (!$res->EOF) {
				$index = -1;
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
								$cellValue 	= my_decrypt('', $SSN);
							}

							if ($key == 'DateOfBirth' || $key == 'GradDate' || $key == 'StartDate' || $key == 'WithdrawnDate' || $key == 'LDA') {
								$fecha = $res->fields[$key];
								if ($fecha && strtotime($fecha)) {
									$timestamp = strtotime($fecha);
									$excelDate = PHPExcel_Shared_Date::PHPToExcel($timestamp);
									$objPHPExcel->getActiveSheet()->setCellValue($cell_no, $excelDate);
									$objPHPExcel->getActiveSheet()
										->getStyle($cell_no)
										->getNumberFormat()
										->setFormatCode('mm/dd/yyyy');
								} else {
									$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
								}
							} else {
								$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
							}
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
	<title><?= CAMPUS_IVY_REPORT ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
		}

		li>a>label {
			position: unset !important;
		}

		.option_red>a>label {
			color: red !important
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
						<h4 class="text-themecolor"><?= CAMPUS_IVY_REPORT ?></h4>
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
												<!-- <?php dump($res_type1); ?> -->
												<!-- DIAM-953 -->
												<div class="col-2 col-sm-2" id="PK_CAMPUS_DIV">
													<?= CAMPUS ?>
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

												<div class="col-md-2 " id="PK_TERM_MASTER_DIV">
													First Term
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control"> <? $res_type = $db->Execute("select ACTIVE,PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'   order by ACTIVE DESC,BEGIN_DATE DESC");
																																		while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_TERM_MASTER'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo "class='option_red'" ?>><?= $res_type->fields['BEGIN_DATE_1'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
																																		} ?>
													</select>
												</div>

												<div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV">
													Program
													<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control">
														<? $res_type = $db->Execute("select ACTIVE,PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CODE ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo "class='option_red'" ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>

												<div class="col-md-2 " id="PK_STUDENT_STATUS_DIV">
													Student Status
													<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
														<? $res_type = $db->Execute("select ACTIVE,PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0  order by ACTIVE DESC,STUDENT_STATUS ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo "class='option_red'" ?>><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>

												<div class="col-md-2">
													Funding
													<select id="PK_FUNDING" multiple name="PK_FUNDING[]" class="form-control" <?= $disabled ?>>
														<option></option>
														<? $res_type = $db->Execute("select * from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,FUNDING ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?= $res_type->fields['PK_FUNDING'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo "class='option_red'" ?> <? if ($PK_FUNDING == $res_type->fields['PK_FUNDING']) echo "selected"; ?>><?= $res_type->fields['FUNDING'] . ' - ' . $res_type->fields['DESCRIPTION'] ?>
																<?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
															</option>
														<? $res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span>
												</div>

												<!-- DIAM-953 -->


												<div class="col-3 col-sm-3 ">
													<button type="button" onclick="submit_form(1)" id="EXCEL_BTN" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
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

			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Campus',
				nonSelectedText: 'Select <?= CAMPUS ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= CAMPUS ?> selected'
			});

			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Status',
				nonSelectedText: 'Select Student Status',
				numberDisplayed: 2,
				nSelectedText: '<?= STATUS ?> selected'
			});

			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All First Term',
				nonSelectedText: 'Select <?= FIRST_TERM ?>',
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
				nonSelectedText: 'Select <?= PROGRAM ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= PROGRAM ?> selected'
			});

			$('#PK_FUNDING').multiselect({
				includeSelectAllOption: true,
				allSelectedText: ' All Funding',
				nonSelectedText: 'Select Funding',
				numberDisplayed: 2,
				nSelectedText: 'Funding selected'
			});

		});
	</script>

	<?php $report_error = ""; ?>

</body>

</html>