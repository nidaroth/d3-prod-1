<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/questionnaire.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php'; 

$msg 	= '';
$error 	= array();
$flag 	= 0;
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$i 		= 0;
	$flag 	= 1;
	foreach($_POST['FIELDS'] as $FIELDS ){
		$EXCEL_COLUMN = $_POST['EXCEL_COLUMN'][$i];
		if($FIELDS != '') {
			$MAP_DETAIL['TABLE_COLUMN'] = $FIELDS;
			db_perform('Z_EXCEL_MAP_DETAIL', $MAP_DETAIL, 'update'," PK_MAP_MASTER = '$_GET[id]' AND EXCEL_COLUMN = '$EXCEL_COLUMN' ");
		} else {
			$db->Execute("DELETE FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND EXCEL_COLUMN = '$EXCEL_COLUMN' ");
		}		
		$i++;
	}
	
	$db->Execute("DELETE FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = '' ");
		
	$res = $db->Execute("SELECT FILE_LOCATION,HEADING_ROW_NO FROM Z_EXCEL_MAP_MASTER WHERE PK_MAP_MASTER = '$_GET[id]' ");
	$newfile1 		= $res->fields['FILE_LOCATION'];
	$HEADING_ROW_NO = $res->fields['HEADING_ROW_NO'];

	if ($newfile1 != ""){
		$extn = explode(".",$newfile1);
		$ii = count($extn) - 1;

		if(strtolower($extn[$ii]) == 'xlsx' || strtolower($extn[$ii]) == 'xls' || strtolower($extn[$ii]) == 'csv'){
			$inputFileName = $newfile1;
			
			if(strtolower($extn[$ii]) == 'csv'){
				$inputFileType = 'CSV';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
				$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			}else{
				//echo $inputFileName.'--';exit;	
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			}
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		}
		$i = 0;
		foreach($sheetData as $row ){
			$i++;
			if($i <= $HEADING_ROW_NO){
				continue;
			}
			
			$QUESTIONNAIRE = array();
			
			$res_fields = $db->Execute("SELECT * from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]'");
			while (!$res_fields->EOF) {
				$TABLE_COLUMN = trim($res_fields->fields['TABLE_COLUMN']);
				$EXCEL_COLUMN = trim($res_fields->fields['EXCEL_COLUMN']);
				
				$QUESTIONNAIRE[$TABLE_COLUMN] = trim($row[$EXCEL_COLUMN]);
				
				$res_fields->MoveNext();
			}
			
			$error_str = "";
			
			/*if($QUESTIONNAIRE['DEPARTMENT'] != '') {
				$res_type = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER IN (2,7) AND DEPARTMENT = '$QUESTIONNAIRE[DEPARTMENT]' ");
				if($res_type->RecordCount() == 0)
					$error_str = 'Invalid Department <b>'.$QUESTIONNAIRE['DEPARTMENT'].'</b>';
				else
					$QUESTIONNAIRE['PK_DEPARTMENT'] = $res_type->fields['PK_DEPARTMENT'];
			} else
				$error_str = 'Department <b>Missing</b>';
			
			unset($QUESTIONNAIRE['DEPARTMENT']);*/
			
			$QUESTIONNAIRE['PK_DEPARTMENT'] = $_GET['dep'];
			
			if($error_str != '')
				$error[] = 'Row #'.$i.' - '.$error_str;
			else {
				$QUESTIONNAIRE['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
				$QUESTIONNAIRE['CREATED_BY'] 	= $_SESSION['PK_USER'];
				$QUESTIONNAIRE['CREATED_ON'] 	= date("Y-m-d H:i");	
				//echo "<pre>";print_r($QUESTIONNAIRE);exit;
				db_perform('M_QUESTIONNAIRE', $QUESTIONNAIRE, 'insert');
			}
		}
		
		if(empty($error)){
			header("location:manage_questionnaire");
			exit;
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
	<title><?=QUESTIONNAIRE_PAGE_TITLE.' '.MAPPING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=QUESTIONNAIRE_PAGE_TITLE.' '.MAPPING?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									
									<div class="row">
                                        <div class="col-md-6">
											<!--<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="DEPARTMENT" >
														<select id="DEPARTMENT" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower($res->fields['EXCEL_COLUMN_NAME']) == strtolower(DEPARTMENT)) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="DEPARTMENT"><?=DEPARTMENT?></label>
													</div>
												</div>
											</div>-->
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="QUESTION"  >
														<select id="QUESTION" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower($res->fields['EXCEL_COLUMN_NAME']) == strtolower(QUESTION)) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="QUESTION"><?=QUESTION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="DISPLAY_ORDER" >
														<select id="DISPLAY_ORDER" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower($res->fields['EXCEL_COLUMN_NAME']) == strtolower(DISPLAY_ORDER)) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="DISPLAY_ORDER"><?=DISPLAY_ORDER?></label>
													</div>
												</div>
											</div>
										</div>
										 <div class="col-md-6">
											<div class="col-lg-12" style="color:red" >
											<? if(!empty($error)){
												echo "<u>Below Data Not Imported due to below Reason</u><br />";
												foreach($error as $error1)
													echo $error1."<br />";
											} else 
												if($flag == 1)
													echo "Uploaded Successfully"; ?>
											</div>
										</div>
									</div>
									
									
									<br />
									<div class="row">
                                        <div class="col-md-4">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>
												
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