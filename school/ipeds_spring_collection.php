<?
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/ipeds_spring_collection_setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['IPEDS'] == 0 || check_access('MANAGEMENT_IPEDS') == 0){
	header("location:../index");
	exit;
}

$report_error="";

$res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	if($_POST['START_DATE'] != ''){
		$START_DATE = date("Y-m-d",strtotime($_POST['START_DATE']));
	}
	if($_POST['END_DATE'] != ''){
		$END_DATE = date("Y-m-d",strtotime($_POST['END_DATE']));
	}
	
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) 
	{
		if($campus_name != '')
		{
			$campus_name .= ', ';
		}
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];
		
		if($campus_id != '')
		{
			$campus_id .= ',';
		}
		$campus_id .= $res_campus->fields['PK_CAMPUS'];
		
		$res_campus->MoveNext();
	}
 
 	if($_POST['REPORT_TYPE'] == 1)
 	{
		if($_POST['FORMAT'] == 2)
		{
			
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
			$file_name 		= 'IPEDS_Spring _2425 - Fall_Enrollment_PartABCD_22_1708378775.xlsx';
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
			$outputFileName );

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;		
			$res = $db->Execute("CALL COMP20003_NEW(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$START_DATE."', '".$END_DATE."','Part A,B,C,D')");
			/*echo count($res->fields);
			echo "<pre>";*/
			//print_r($res);exit;
			/*if (count($res->fields) == '0') 
			{
				$report_error = "No data in the report for the selections made.";
			} 
			else 
			{*/
				$index = -1;
				$heading = array_keys($res->fields);
				foreach ($heading as $title) {
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
					$objPHPExcel->getActiveSheet()->freezePane('A1'); 
								
				}
				$line++ ;
				while (!$res->EOF) 
				{

					$index = -1;
					foreach ($heading as $key) 
					{
						//print_r($res->fields[$key]);
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								$index++;
								$cell_no = $cell[$index].$line;
						        $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
						        $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
						        $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							    $objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{

								// Get data column value and set data
									
								 $index++;
								 $cell_no = $cell[$index].$line;
								 $cellValue=$res->fields[$key];
								 $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
								
								 
							}	
					   }
					   else
					   {
							echo 'Skip header';
					   }
					}
					
					$line++;
					$res->MoveNext();
							
				}

				$objPHPExcel->getActiveSheet()->freezePane('A1');
				
				$objWriter->save($outputFileName);
				$objPHPExcel->disconnectWorksheets();
				header("location:".$outputFileName);
			/*}*/
			
		}
	}
	else if($_POST['REPORT_TYPE'] == 2)
	{
		if($_POST['FORMAT'] == 2)
		{
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
			$file_name 		= 'IPEDS_Spring _2425 - Fall_Enrollment_PartE_22_1708378775.xlsx';
			$outputFileName = $dir.$file_name; 
			$outputFileName = str_replace(
			pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
			$outputFileName );

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

			$line 	= 1;		
			$res = $db->Execute("CALL COMP20003_NEW(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$START_DATE."', '".$END_DATE."','Part E')");
			/*echo count($res->fields);
			echo "<pre>";*/
			//print_r($res->fields);exit;
			/*if (count($res->fields) == '0') 
			{
				$report_error = "No data in the report for the selections made.";
			} 
			else 
			{*/
				
				$heading = array_keys($res->fields);
				$index = -1;
				foreach ($heading as $title) {
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
					$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
					$objPHPExcel->getActiveSheet()->freezePane('A1');
								
				}
				$line++;
				while (!$res->EOF) 
				{

					$index = -1;
					foreach ($heading as $key) 
					{
						//print_r($res->fields[$key]);
						if($key!='ROW_TYPE')
						{
							//Get Header column name and set styling 
							if($line==1)
							{
								$index++;
								$cell_no = $cell[$index].$line;
						        $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields[$key]);
						        $objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
						        $objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(20);
							    $objPHPExcel->getActiveSheet()->freezePane('A1');
							}
							else
							{

								// Get data column value and set data
									
								 $index++;
								 $cell_no = $cell[$index].$line;
								 $cellValue=$res->fields[$key];
								 $objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($cellValue);
								
								 
							}	
					   }
					   else
					   {
							echo 'Skip header';
					   }
					}
					
					$line++;
					$res->MoveNext();
							
				}

				$objPHPExcel->getActiveSheet()->freezePane('A1');
				
				$objWriter->save($outputFileName);
				$objPHPExcel->disconnectWorksheets();
				header("location:".$outputFileName);
			/*}*/
			
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
	<title><?=MNU_IPEDS_SPRING_COLLECTIONS_FALL_ENRO?> | <?=$title?></title>
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
						IPEDS Spring Collection - 2024-2025
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
											Survey
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" >
												<option value="Fall Enrollment">Fall Enrollment</option> 
											</select>
										</div>
										<div class="col-md-2">
										Survey Options
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" >
												<option value="1">Part A, B, C, D</option>
												<option value="2">Part E</option>
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
										
										<div class="col-md-2">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? 
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" style="padding-right: 80px;text-align: right;" >
											<br />
											<!-- <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button> -->
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

        <?php if($report_error!="") {?>
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">

	var error= '<?php echo  $report_error; ?>';
	jQuery(document).ready(function($) {
	   if(error!=""){
		jQuery('#errorModal').modal();
	   }
	});

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

	<?php $report_error=""; ?>

</body>

</html>