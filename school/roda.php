<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(has_wvjc_access($_SESSION['PK_ACCOUNT'])==0){
	header("location:../index");
	exit;
}

$current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1];
if($current_page != $_SESSION['PREVIOUS_PAGE'] || $_GET['clear'] == 1 ){ //Ticket # 1826
	$_SESSION['SORT_FIELD'] 	 = 'S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ';
	$_SESSION['SORT_ORDER'] 	 = '';
	$_SESSION['PAGE'] 			 = 1;
	$_SESSION['rows'] 			 = 25;
	$_SESSION['PREVIOUS_PAGE'] 	 = $current_page;
	$_SESSION['SRC_SEARCH'] 		= '';
	$_SESSION['SRC_PK_CAMPUS'] 		= '';
	$_SESSION['SRC_PK_TERM_MASTER'] = '';
	$_SESSION['SRC_PK_COURSE'] 		= '';
	$_SESSION['SRC_PK_SESSION'] 	= '';
	$_SESSION['SRC_INSTRUCTOR'] 	= '';
	$_SESSION['SRC_PK_CAMPUS_ROOM'] = '';
	$_SESSION['SRC_PK_STUDENT_STATUS'] 		= '';

}


if(!empty($_POST)){


	$semester_code 	= '';
	if(!empty($_POST['semester_code'])){
		$semester_code 	 = $_POST['semester_code'];
	}

	$campus_code 	= '';
	if(!empty($_POST['campus_code'])){
		$campus_code 	 = $_POST['campus_code'];
	}

	$campus_name 	= '';
	if(!empty($_POST['campus_name'])){
		$campus_name 	 = $_POST['campus_name'];
	}

	$mod_start_date 	= '';
	if(!empty($_POST['mod_start_date'])){
	    $mod_start_date = date("Y-m-d",strtotime($_POST['mod_start_date']));

	}


	// if($_POST['term_begin_start_date'] != ''){
	// 	$ST = date("Y-m-d",strtotime($_POST['term_begin_start_date']));
	// }
	// if($_POST['term_begin_end_date'] != ''){
	// 	$ET = date("Y-m-d",strtotime($_POST['term_begin_end_date']));
	// }

	$PK_CAMPUS ='';
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS = $_POST['PK_CAMPUS'];
	}

	$PK_STUDENT_STATUS ='';
	if(!empty($_POST['PK_STUDENT_STATUS'])){
	$PK_STUDENT_STATUS = implode(",",$_POST['PK_STUDENT_STATUS']);
	}

	$PK_CAMPUS_PROGRAM ='';
	if(!empty($_POST['PK_CAMPUS_PROGRAM'])){
	$PK_CAMPUS_PROGRAM = implode(",",$_POST['PK_CAMPUS_PROGRAM']);
	}

	if($_POST['FORMAT'] == 1) {

		//echo "CALL CUST10001(".$_SESSION['PK_ACCOUNT'].", '".$CHK_PK_COURSE_OFFERING."', '".$PK_STUDENT_STATUS."', 'Student File')";
		$res = $db->Execute("CALL CUST10001(".$_SESSION['PK_ACCOUNT'].",".$PK_CAMPUS.",'".$PK_STUDENT_STATUS."','".$PK_CAMPUS_PROGRAM."','".$semester_code."','".$mod_start_date."','".$campus_code."', '".$campus_name."','Student File')");

		while (!$res->EOF) { 
			$heading = str_replace('_',' ',array_keys($res->fields));	
			$contents[] = $res->fields;							
			$res->MoveNext();
		}
 
		$file_name = 'Student_File.csv';
		//$dir 			= 'temp/';
		$outputFileName = $file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
		$outputFileName );  

		$delimiter = ","; 
		$f = fopen("php://output", "w");
		fputcsv($f, $heading, $delimiter);		 
		foreach($contents as $row){ 
			// Adding data into CSV
			fputcsv($f, $row, $delimiter); 
		}
		fclose($f);
		// Telling browser to download file as CSV
		header('Content-Type: text/csv'); 
		header('Content-Disposition: attachment; filename="'.$outputFileName.'";'); 
		exit();

	}else if($_POST['FORMAT'] == 2){
	
		//echo "CALL CUST10001(".$_SESSION['PK_ACCOUNT'].", '".$CHK_PK_COURSE_OFFERING."', '".$PK_STUDENT_STATUS."', '','Student Course File')";
		
		$res = $db->Execute("CALL CUST10001(".$_SESSION['PK_ACCOUNT'].",".$PK_CAMPUS.",'".$PK_STUDENT_STATUS."','".$PK_CAMPUS_PROGRAM."','".$semester_code."','".$mod_start_date."','".$campus_code."', '".$campus_name."', 'Student Course File')");

		while (!$res->EOF) { 
			$heading = str_replace('_',' ',array_keys($res->fields));	
			$contents[] = $res->fields;							
			$res->MoveNext();
		}
 
		$file_name = 'Student_Course_File.csv';
		//$dir 			= 'temp/';
		$outputFileName = $file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
		$outputFileName);  
		$delimiter = ","; 
		$f = fopen("php://output", "w");
		fputcsv($f, $heading, $delimiter);		 
		foreach($contents as $row){ 
			// Adding data into CSV
			fputcsv($f, $row, $delimiter); 
		}
		fclose($f);
		// Telling browser to download file as CSV
		header('Content-Type: text/csv'); 
		header('Content-Disposition: attachment; filename="'.$outputFileName.'";'); 
		exit();
	}
	//print_r($_POST);die;
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
	<title><?=MNU_RODA?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.datagrid-header td {font-size: 14px !important; vertical-align: bottom;}

	li > a > label{position: unset !important;}
		/* Ticket # 1149 - term */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1149 - term */
		
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}	
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid"> <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
						<?=MNU_RODA ?> </h4>
                    </div>
                </div>			

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" >				

								<div class="row" style="padding-bottom:10px;">
									<!-- Ticket # 1341  -->
									<div class="col-md-2" style="max-width: 250px !important; display: block;">
										<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
											<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION, ADMISSIONS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' AND ADMISSIONS = 0 order by ADMISSIONS DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
												$option_label = $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'];
												
												?>
											<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>" ><?=$option_label ?></option>
											<? $res_type->MoveNext();
												} ?>
										</select>
									</div>
									<div class="col-md-2 " >
										<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control" >
											<? $res_type = $db->Execute("SELECT CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
											<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
											<?	$res_type->MoveNext();
												} ?>
										</select>
									</div>

									<div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV">
										<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control">
											<? $res_type = $db->Execute("select ACTIVE,PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CODE ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?= $res_type->fields['PK_CAMPUS_PROGRAM'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
												</option>
											<? $res_type->MoveNext();
											} ?>
										</select>
									</div>
									<!-- <div class="col-md-3" style="bottom:21px;max-width:320px">
										<b style="margin-bottom:5px">Course Term Date Range</b>
										<?php 
											$term_start_date=date('m/d/Y',strtotime("-3 months",strtotime(date('Y-m-d'))));
											$term_end_date=date('m/d/Y',strtotime("+3 months",strtotime(date('Y-m-d'))));
										?>	
										<div class="d-flex " style="margin-bottom:5px">
											<input type="text" class="form-control date" name="term_begin_start_date" field="term_begin_start_date" id="term_begin_start_date"  style="max-width:100%;" placeholder="Start Date" value="<?php echo $term_start_date; ?>" >
											<input type="text" class="form-control date" name="term_begin_end_date"  field="term_begin_end_date" id="term_begin_end_date" onchange="doSearch()" style="max-width:100%;" placeholder="End Date" value="<?php echo $term_end_date; ?>" >
										</div>
									</div> -->

								    <div class="col-md-2" style="bottom:21px;max-width:320px">
										<b style="margin-bottom:5px">Semester Code</b>
										<div class="d-flex " style="margin-bottom:5px">
											<input type="text" class="form-control" name="semester_code" id="semester_code"  style="max-width:100%;" placeholder="Semester Code" value="" >
										</div>
									</div>

									<div class="col-md-2" style="bottom:21px;max-width:320px">
										<b style="margin-bottom:5px">Campus Code</b>
										<div class="d-flex " style="margin-bottom:5px">
											<input type="text" class="form-control" name="campus_code" id="campus_code"  style="max-width:100%;" placeholder="Campus Code" value="" >
										</div>
									</div>

								</div>
									<div class="row m-t-5">

									<div class="col-md-2" style="bottom:21px;max-width:320px">
										<b style="margin-bottom:5px">Campus Name</b>
										<div class="d-flex " style="margin-bottom:5px">
											<input type="text" class="form-control" name="campus_name" id="campus_name"  style="max-width:100%;" placeholder="Campus Name" value="" >
										</div>
									</div>

									<div class="col-md-2" style="bottom:21px;max-width:320px">
										<b style="margin-bottom:5px">MOD Start Date</b>
										<div class="d-flex " style="margin-bottom:5px">
											<input type="text" class="form-control date" name="mod_start_date" id="mod_start_date"  style="max-width:100%;" placeholder="MOD Start Date" value="" >
										</div>
									</div>

									<div class="col-md-3">										
											<button type="button" class="btn waves-effect waves-light btn-info" id="STUDENT_FILE_BTN"   onclick="submit_form(1)" id="btn_1">Student File</button>
											<button type="button" class="btn waves-effect waves-light btn-info" id="STUDENT_COURSE_BTN"  onclick="submit_form(2)" id="btn_1">Student Course</button>
									</div>
										<div class="col-md-3">										

											<input type="hidden" name="FORMAT" id="FORMAT">
										</div>
									</div>
																	
									
									<br />									
								
									




                            </div>
                        </div>
					</div>
				</div>

				</form>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
		<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">

	// function search(e){
	// 	if (e.keyCode == 13) {
	// 		doSearch();
	// 	}
	// }
	$(function(){
		jQuery(document).ready(function($) {
			jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
			// DIAM-589
			var loader = document.getElementsByClassName("preloader_grid");
			loader[0].style.display = "none";
			// End DIAM-589

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
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS ?>',
			nonSelectedText: '<?=STUDENT_STATUS ?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS ?> selected'
		});

		$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= PROGRAM ?>',
				nonSelectedText: '<?= PROGRAM ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= PROGRAM ?> selected'
		});
			

		$('#MOD_START_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		}).on('change', function(sdate) {

			
		});

		// $('#MIDPOINT_END_DATE').datepicker({
		// autoclose: true,
		// todayHighlight: true,
		// orientation: "bottom auto"
		// }).on('change', function(edate) {
			
		// });

		
	});

	function submit_form(val){
		jQuery(document).ready(function($) {
			//var valid = new Validation('form1', {onSubmit:false});
			//var result = valid.validate();
			//if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			//}
		});
	}
	</script>

</body>

</html>
