<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/time_clock.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

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
			$dateTime=date("Y-m-d-H-i-s");
			// $newfile1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/time_clock_'.date("Y-m-d-H-i-s").'.'.$ext1;
			$newfile1 = '../backend_assets/tmp_upload/time_clock_'.$dateTime.'.'.$ext1;
			move_uploaded_file($_FILES['FILE_XLX']['tmp_name'], $newfile1);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/time_clock_'.date("Y-m-d-H-i-s").'.'.$ext1;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);

			$newfile2 = 'temp/time_clock_'.$dateTime.'.'.$ext1;
			//move_uploaded_file($_FILES['FILE_XLX']['tmp_name'], $newfile2);
			if (!copy($newfile1, $newfile2)) {
				echo "failed to copy $newfile1...\n";
			}

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

			// delete tmp file
			unlink($newfile1);

			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			
			$TIME_CLOCK_PROCESSOR['FILE_NAME']    	= $_FILES['FILE_XLX']['name'];
			$TIME_CLOCK_PROCESSOR['IMPORT_OPTIONS'] = $_POST['IMPORT_OPTIONS'];
			// $TIME_CLOCK_PROCESSOR['LOCATION']     	= $newfile1;
			$TIME_CLOCK_PROCESSOR['LOCATION']     	= $url;
			$TIME_CLOCK_PROCESSOR['PK_ACCOUNT']   	= $_SESSION['PK_ACCOUNT'];
			$TIME_CLOCK_PROCESSOR['UPLOADED_BY']  	= $_SESSION['PK_USER'];
			$TIME_CLOCK_PROCESSOR['UPLOADED_ON']  	= date("Y-m-d H:i");
			db_perform('S_TIME_CLOCK_PROCESSOR', $TIME_CLOCK_PROCESSOR, 'insert');
			$PK_TIME_CLOCK_PROCESSOR = $db->insert_ID();
			
			$EXCEL_MAP_MASTER['HEADING_ROW_NO'] = 0;
			// $EXCEL_MAP_MASTER['FILE_LOCATION'] 	= $newfile1;
			$EXCEL_MAP_MASTER['FILE_LOCATION'] 	= $url;
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
				header("location:time_clock_map_column?id=".$PK_MAP_MASTER.'&c_id='.$PK_TIME_CLOCK_PROCESSOR.'&t='.$_POST['IMPORT_OPTIONS']);
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
	<title><?=TIME_CLOCK_IMPORT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=TIME_CLOCK_IMPORT ?> </h4>
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
										<div class="col-md-2">
											<label for="IMPORT_OPTIONS"><?=IMPORT_OPTIONS?></label>
										</div>
                                        <div class="col-md-2">
											<select id="IMPORT_OPTIONS" name="IMPORT_OPTIONS" class="form-control required-entry" >
												<option value="" ></option>
												<option value="1" >Daily In/Out</option>
												<option value="2" >Daily In/Out/Break</option>
												<option value="3" >Daily Hours</option>
												<option value="4" >Daily Detail</option>
											</select>
										</div>
                                    </div>
									<br />
									
									<div class="row">
										<div class="col-md-2">
											<label for="FILE_XLX"><?=UPLOAD_FILE?></label>
										</div>
                                        <div class="col-md-6">
											<input type="file" class="form-control required-entry" id="FILE_XLX" name="FILE_XLX" value="" >
										</div>
                                    </div>
									<br />
									<div class="row">
										<div class="col-md-2"></div>
                                        <div class="col-md-6">
											<div class="form-group m-b-5" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=IMPORT?></button>
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