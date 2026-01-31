<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/questionnaire.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}
$msg1 = '';
if(!empty($_POST)){
	
	if (trim($_FILES['FILE_XLX']['name'])!= ""){
		$extn = explode(".",$_FILES['FILE_XLX']['name']);
		$type = $_FILES['FILE_XLX']['type'];
		$ii = count($extn) - 1;
		if(strtolower($extn[$ii]) == 'xlsx' || strtolower($extn[$ii]) == 'xls' || strtolower($extn[$ii]) == 'csv'){ 	
			$newfile1 = 'temp/questonaries_'.date("Y-m-d-H-i-s").'.'.$extn[$ii];
			move_uploaded_file($_FILES['FILE_XLX']['tmp_name'], $newfile1);
			include '../global/excel/Classes/PHPExcel/IOFactory.php';
			$inputFileName = $newfile1;
			
			$EXCEL_MAP_MASTER['HEADING_ROW_NO'] = $_POST['HEADING_ROW_NO'];
			$EXCEL_MAP_MASTER['FILE_LOCATION'] 	= $inputFileName;
			$EXCEL_MAP_MASTER['FILE_NAME'] 		= $_FILES['FILE_XLX']['name'];
			$EXCEL_MAP_MASTER['CREATED_BY'] 	= $_SESSION['PK_USER'];
			$EXCEL_MAP_MASTER['CREATED_ON'] 	= date("Y-m-d H:i");
			db_perform('Z_EXCEL_MAP_MASTER', $EXCEL_MAP_MASTER, 'insert');
			$PK_MAP_MASTER = $db->insert_ID();

			if(strtolower($extn[$ii]) == 'csv'){
				$inputFileType = 'CSV';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
				$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			}else{
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			}
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			$i = 1;
			foreach($sheetData as $row){
				if($i < $_POST['HEADING_ROW_NO']){
					$i++;
					continue;
				}
				
				foreach($row as $key => $value)	{
					$EXCEL_MAP_DETAIL['PK_MAP_MASTER'] 		 = $PK_MAP_MASTER;
					$EXCEL_MAP_DETAIL['EXCEL_COLUMN'] 	   	 = trim($key);
					$EXCEL_MAP_DETAIL['EXCEL_COLUMN_NAME'] 	 = trim($value);
					db_perform('Z_EXCEL_MAP_DETAIL', $EXCEL_MAP_DETAIL, 'insert');
				}
				header("location:questionnaire_upload_mapping?id=".$PK_MAP_MASTER.'&dep='.$_POST['PK_DEPARTMENT']);
				exit;
			}
		}else{
		    $msg1 = 'Invalid File Format. Please Upload xlsx,xls,cvs file only'; 
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
	<title><?=QUESTIONNAIRE_PAGE_TITLE.' '.UPLOAD?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=QUESTIONNAIRE_PAGE_TITLE.' '.UPLOAD?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									
									<? if($msg1 != '' ){ ?>
									<div class="row">
										<div class="col-md-2">&nbsp;</div>
                                        <div class="col-md-6" style="color:red">
											<?=$msg1?>
										</div>
                                    </div>
									<? } ?>
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_DEPARTMENT" name="PK_DEPARTMENT" class="form-control " >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_DEPARTMENT, DEPARTMENT from M_DEPARTMENT WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER IN (2,6,7) ORDER BY DEPARTMENT ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_DEPARTMENT'] ?>" <? if($res_type->fields['PK_DEPARTMENT'] == $PK_DEPARTMENT) echo "selected"; ?> ><?=$res_type->fields['DEPARTMENT']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_DEPARTMENT"><?=DEPARTMENT?></label>
											</div>
										</div>
                                    </div>
									<div class="row">
										<div class="col-md-1">
											<label for="FILE_XLX"><?=UPLOAD_FILE?></label>
										</div>
                                        <div class="col-md-5">
											<input type="file" class="form-control required-entry" id="FILE_XLX" name="FILE_XLX" value="" >
										</div>
                                    </div>
									<div class="row">
										<div class="col-md-2">
											<label for="HEADING_ROW_NO"><?=HEADING_ROW_NO?></label>
										</div>
                                        <div class="col-md-2">
											<input type="text" class="form-control required-entry" id="HEADING_ROW_NO" name="HEADING_ROW_NO" value="1" >
										</div>
										<div class="col-sm-1">
											<span class="mytooltip tooltip-effect-1">
												<span class="tooltip-item tool_tip_custom">
													<i class="mdi mdi-help-circle help_size"></i>
												</span>
												<span class="tooltip-content clearfix">
													<span class="tooltip-text">
														<? if($_SESSION['PK_LANGUAGE'] == 1)
															$lan_field = "TOOL_CONTENT_ENG";
														else
															$lan_field = "TOOL_CONTENT_SPA"; 
														$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 20"); 
														echo $res_help->fields[$lan_field]; ?>
													</span>
												</span>
											</span>
										</div>
                                    </div>
									<br />
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=NEXT?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_questionnaire'" ><?=CANCEL?></button>
												
											</div>
										</div>
									</div>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>