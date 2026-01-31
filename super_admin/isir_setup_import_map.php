<? require_once("../global/config.php"); 
require_once("../language/common.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php'; 

$msg 	= '';
$error 	= array();
$flag 	= 0;
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$ISIR_SETUP_MASTER['FROM_NAME']   		= $_POST['FROM_NAME'];
	$ISIR_SETUP_MASTER['YEAR_INDICATION']   = $_POST['YEAR_INDICATION'];
	$ISIR_SETUP_MASTER['ACTIVE']   			= 0;
	$ISIR_SETUP_MASTER['CREATED_BY']  		= $_SESSION['PK_USER'];
	$ISIR_SETUP_MASTER['CREATED_ON']  		= date("Y-m-d H:i");
	db_perform('Z_ISIR_SETUP_MASTER', $ISIR_SETUP_MASTER, 'insert');
	$PK_ISIR_SETUP_MASTER = $db->insert_ID();
		
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
	
			$ISIR_SETUP_DETAIL = array();
			
			$res_fields = $db->Execute("SELECT * from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]'");
			while (!$res_fields->EOF) {
				$TABLE_COLUMN = trim($res_fields->fields['TABLE_COLUMN']);
				$EXCEL_COLUMN = trim($res_fields->fields['EXCEL_COLUMN']);
				
				$ISIR_SETUP_DETAIL[$TABLE_COLUMN] = trim($row[$EXCEL_COLUMN]);
				
				$res_fields->MoveNext();
			}
			
			if(strtolower(trim($ISIR_SETUP_DETAIL['HAS_LEDGEND'])) == 'yes' || strtolower(trim($ISIR_SETUP_DETAIL['HAS_LEDGEND'])) == 'y')
				$ISIR_SETUP_DETAIL['HAS_LEDGEND'] = 1;
			else
				$ISIR_SETUP_DETAIL['HAS_LEDGEND'] = 0;
				
			if($ISIR_SETUP_DETAIL['DSIS_FIELD_NAME'] != ''){
				$DSIS_FIELD_NAME = trim($ISIR_SETUP_DETAIL['DSIS_FIELD_NAME']);
				$res_fi = $db->Execute("select DSIS_FIELD from Z_ISIR_DSIS_FIELDS WHERE ACTIVE = 1 AND TRIM(FIELD_NAME) = '$DSIS_FIELD_NAME' ");
				$ISIR_SETUP_DETAIL['DSIS_FIELD_NAME'] = $res_fi->fields['DSIS_FIELD'];
			}

			$ISIR_SETUP_DETAIL['PK_ISIR_SETUP_MASTER'] 	= $PK_ISIR_SETUP_MASTER;
			$ISIR_SETUP_DETAIL['ACTIVE'] 				= 1;
			$ISIR_SETUP_DETAIL['CREATED_BY'] 			= $_SESSION['PK_USER'];
			$ISIR_SETUP_DETAIL['CREATED_ON'] 			= date("Y-m-d H:i");
			db_perform('Z_ISIR_SETUP_DETAIL', $ISIR_SETUP_DETAIL, 'insert');
			//echo "<pre>";print_r($ISIR_SETUP_DETAIL);exit;
		}
		
		if(empty($error)){
			header("location:manage_isir_setup");
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
	<title>ISIR Setup Upload Mapping | <?=$title?></title>
	
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">ISIR Setup Upload Mapping</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="FROM_NAME" name="FROM_NAME" value="<?=$FROM_NAME?>" >
												<span class="bar"></span>
												<label for="FROM_NAME">Form Name</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="YEAR_INDICATION" name="YEAR_INDICATION" value="<?=$YEAR_INDICATION?>" >
												<span class="bar"></span>
												<label for="YEAR_INDICATION">Year Indication</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="FIELD_NO" >
														<select id="LEDGER_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(trim(strtolower($res->fields['EXCEL_COLUMN_NAME'])) == "field #") echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_CODE">Field #</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="HEADING" >
														<select id="LEDGER_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>"  <? if(trim(strtolower($res->fields['EXCEL_COLUMN_NAME'])) == "heading" || $res->fields['EXCEL_COLUMN_NAME'] == "field name") echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_CODE">Heading</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="START" >
														<select id="LEDGER_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(trim(strtolower($res->fields['EXCEL_COLUMN_NAME'])) == "start") echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_CODE">Start</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="END" >
														<select id="LEDGER_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(trim(strtolower($res->fields['EXCEL_COLUMN_NAME'])) == "end") echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_CODE">End</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="DSIS_FIELD_NAME" >
														<select id="LEDGER_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(trim(strtolower($res->fields['EXCEL_COLUMN_NAME'])) == "dsis fields") echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_CODE">DSIS Fields</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="HAS_LEDGEND" >
														<select id="LEDGER_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(trim(strtolower($res->fields['EXCEL_COLUMN_NAME'])) == "has legend") echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_CODE">Has Legend</label>
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
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_isir_setup'" ><?=CANCEL?></button>
												
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