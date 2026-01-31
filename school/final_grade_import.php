<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");
require_once("../language/final_grade_input.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if (trim($_FILES['FILE_XLX']['name'])!= ""){
		$extn = explode(".",$_FILES['FILE_XLX']['name']);
		$type = $_FILES['FILE_XLX']['type'];
		$ii = count($extn) - 1;
		if(strtolower($extn[$ii]) == 'csv' || strtolower($extn[$ii]) == 'xls' || strtolower($extn[$ii]) == 'xlsx'){ 
			$ext1 = $extn[$ii];
			if(strtolower($extn[$ii]) == 'txt')
				$ext1 = 'csv';
				
			//$newfile1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/co_grade_book_'.date("Y-m-d-H-i-s").'.'.$ext1;
			$newfile1 = '../backend_assets/tmp_upload/co_grade_book_'.date("Y-m-d-H-i-s").'.'.$ext1;
			move_uploaded_file($_FILES['FILE_XLX']['tmp_name'], $newfile1);
			include '../global/excel/Classes/PHPExcel/IOFactory.php';
			$inputFileName = $newfile1;
			
			if(strtolower($extn[$ii]) == 'csv'){
				$inputFileType = 'CSV';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
				$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			}else{
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			}
						
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			
			$FINAL_GRADE_IMPORT['MATCH_BY']     	= $_POST['MATCH_BY'];
			$FINAL_GRADE_IMPORT['FILE_NAME']    	= $_FILES['FILE_XLX']['name'];
			$FINAL_GRADE_IMPORT['LOCATION']     	= $newfile1;
			$FINAL_GRADE_IMPORT['PK_ACCOUNT']   	= $_SESSION['PK_ACCOUNT'];
			$FINAL_GRADE_IMPORT['UPLOADED_BY']  	= $_SESSION['PK_USER'];
			$FINAL_GRADE_IMPORT['UPLOADED_ON']  	= date("Y-m-d H:i");
			db_perform('S_FINAL_GRADE_IMPORT', $FINAL_GRADE_IMPORT, 'insert');
			$PK_FINAL_GRADE_IMPORT = $db->insert_ID();
			
			$EXCEL_MAP_MASTER['HEADING_ROW_NO'] = 0;
			$EXCEL_MAP_MASTER['FILE_LOCATION'] 	= $newfile1;
			$EXCEL_MAP_MASTER['FILE_NAME'] 		= $_FILES['FILE_XLX']['name'];
			$EXCEL_MAP_MASTER['CREATED_BY'] 	= $_SESSION['PK_USER'];
			$EXCEL_MAP_MASTER['CREATED_ON'] 	= date("Y-m-d H:i");
			db_perform('Z_EXCEL_MAP_MASTER', $EXCEL_MAP_MASTER, 'insert');
			$PK_MAP_MASTER = $db->insert_ID();
			
			$i = 1;
			foreach($sheetData as $row){
				//echo "<pre>";print_r($row);	exit;
				/*if($i == 1){
					$i++;
					continue;
				}*/
				
				foreach($row as $key => $value)	{
					$EXCEL_MAP_DETAIL['PK_MAP_MASTER'] 		 = $PK_MAP_MASTER;
					$EXCEL_MAP_DETAIL['EXCEL_COLUMN'] 	   	 = trim($key);
					$EXCEL_MAP_DETAIL['EXCEL_COLUMN_NAME'] 	 = trim($value);
					db_perform('Z_EXCEL_MAP_DETAIL', $EXCEL_MAP_DETAIL, 'insert');
				}
				header("location:final_grade_import_map_column?id=".$PK_MAP_MASTER.'&iid='.$PK_FINAL_GRADE_IMPORT);
				exit;
			}
		} else {
		    $msg1 = 'Invalid file format. Accepted formats include: csv, xls, or xlsx'; 
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
	<title><?=MNU_FINAL_GRADE_IMPORT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_FINAL_GRADE_IMPORT ?> </h4>
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
									<br />
									<? } ?>
									
									<div class="row">
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-3">
													<label for="MATCH_BY"><?=MATCH_ON?></label>
												</div>
												<div class="col-md-4">
													<select id="MATCH_BY" name="MATCH_BY" class="form-control required-entry" >
														<option value="" ></option>
														<option value="1" ><?=COURSE_OFFERING?></option>
														<option value="2" ><?=EXTERNAL_ID?></option>
													</select>
												</div>
											</div>
											<br />
											
											<div class="row">
												<div class="col-md-3">
													<label for="FILE_XLX"><?=UPLOAD_FILE?></label>
												</div>
												<div class="col-md-9">
													<input type="file" class="form-control required-entry" id="FILE_XLX" name="FILE_XLX" value="" >
												</div>
											</div>
											
											<br />
											<div class="row">
												<div class="col-md-3"></div>
												<div class="col-md-6">
													<div class="form-group m-b-5" >
														<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=IMPORT?></button>
													</div>
												</div>
											</div>
										</div>
										
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-12 " style="text-align:right" >
													<button type="button" onclick="window.location.href='final_grade_import_template'" name="btn" class="btn waves-effect waves-light btn-info"><?=FINAL_GRADE_IMPORT_TEMPLATE?></button>
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