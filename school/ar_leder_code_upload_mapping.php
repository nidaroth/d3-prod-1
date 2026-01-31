<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ar_leder_code.php");
require_once("check_access.php");

if(check_access('SETUP_ACCOUNTING') == 0 ){
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
	
			$AR_LEDGER_CODE = array();
			
			$res_fields = $db->Execute("SELECT * from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]'");
			while (!$res_fields->EOF) {
				$TABLE_COLUMN = trim($res_fields->fields['TABLE_COLUMN']);
				$EXCEL_COLUMN = trim($res_fields->fields['EXCEL_COLUMN']);
				
				$AR_LEDGER_CODE[$TABLE_COLUMN] = trim($row[$EXCEL_COLUMN]);
				
				$res_fields->MoveNext();
			}
			
			$error_str = "";
			
			if($AR_LEDGER_CODE['TYPE'] != '') {
				if(strtolower($AR_LEDGER_CODE['TYPE']) == 'award')
					$AR_LEDGER_CODE['TYPE'] = 1;
				else if(strtolower($AR_LEDGER_CODE['TYPE']) == 'fee')
					$AR_LEDGER_CODE['TYPE'] = 2;
				else 
					$error_str .= 'Type <b>'.$AR_LEDGER_CODE['TYPE'].'</b>';
			}
			
			if($AR_LEDGER_CODE['NEED_ANALYSIS'] != '') {
				if(strtolower($AR_LEDGER_CODE['NEED_ANALYSIS']) == 'yes' || strtolower($AR_LEDGER_CODE['NEED_ANALYSIS']) == 'y')
					$AR_LEDGER_CODE['NEED_ANALYSIS'] = 1;
				else if(strtolower($AR_LEDGER_CODE['NEED_ANALYSIS']) == 'no' || strtolower($AR_LEDGER_CODE['NEED_ANALYSIS']) == 'n')
					$AR_LEDGER_CODE['NEED_ANALYSIS'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= 'Need Analysis <b>'.$AR_LEDGER_CODE['NEED_ANALYSIS'].'</b>';
				}
			}
			
			if($AR_LEDGER_CODE['AWARD_LETTER'] != '') {
				if(strtolower(trim($AR_LEDGER_CODE['AWARD_LETTER'])) == 'yes' || strtolower(trim($AR_LEDGER_CODE['AWARD_LETTER'])) == 'y')
					$AR_LEDGER_CODE['AWARD_LETTER'] = 1;
				else if(strtolower(trim($AR_LEDGER_CODE['AWARD_LETTER'])) == 'no' || strtolower(trim($AR_LEDGER_CODE['AWARD_LETTER'])) == 'n')
					$AR_LEDGER_CODE['AWARD_LETTER'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= 'Award Letter <b>'.$AR_LEDGER_CODE['AWARD_LETTER'].'</b>';
				}
			}

			if($AR_LEDGER_CODE['INVOICE'] != '') {
				if(strtolower($AR_LEDGER_CODE['INVOICE']) == 'yes' || strtolower($AR_LEDGER_CODE['INVOICE']) == 'y' )
					$AR_LEDGER_CODE['INVOICE'] = 1;
				else if(strtolower($AR_LEDGER_CODE['INVOICE']) == 'no' || strtolower($AR_LEDGER_CODE['INVOICE']) == 'n')
					$AR_LEDGER_CODE['INVOICE'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= 'Invoice <b>'.$AR_LEDGER_CODE['INVOICE'].'</b>';
				}
			}
			
			if($AR_LEDGER_CODE['TITLE_IV'] != '') {
				if(strtolower($AR_LEDGER_CODE['TITLE_IV']) == 'yes' || strtolower($AR_LEDGER_CODE['TITLE_IV']) == 'y' )
					$AR_LEDGER_CODE['TITLE_IV'] = 1;
				else if(strtolower($AR_LEDGER_CODE['TITLE_IV']) == 'no' || strtolower($AR_LEDGER_CODE['TITLE_IV']) == 'n')
					$AR_LEDGER_CODE['TITLE_IV'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= 'Title IV <b>'.$AR_LEDGER_CODE['TITLE_IV'].'</b>';
				}
			}
				
			if($error_str != '')
				$error[] = 'Row #'.$i.' - Invalid '.$error_str;
			else {
				unset($AR_LEDGER_CODE['PK_ACCOUNT']);
				unset($AR_LEDGER_CODE['CREATED_BY']);
				unset($AR_LEDGER_CODE['CREATED_ON']);
				unset($AR_LEDGER_CODE['EDITED_BY']);
				unset($AR_LEDGER_CODE['EDITED_ON']);
				
				$res_led = $db->Execute("SELECT PK_AR_LEDGER_CODE FROM M_AR_LEDGER_CODE WHERE CODE = '$AR_LEDGER_CODE[CODE]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				if($res_led->RecordCount() == 0) {
					$AR_LEDGER_CODE['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
					$AR_LEDGER_CODE['CREATED_BY'] 	= $_SESSION['PK_USER'];
					$AR_LEDGER_CODE['CREATED_ON'] 	= date("Y-m-d H:i");	
					//echo "<pre>";print_r($AR_LEDGER_CODE);exit;
					db_perform('M_AR_LEDGER_CODE', $AR_LEDGER_CODE, 'insert');
				} else {
					$PK_AR_LEDGER_CODE = $res_led->fields['PK_AR_LEDGER_CODE'];
					$AR_LEDGER_CODE['EDITED_BY'] 	= $_SESSION['PK_USER'];
					$AR_LEDGER_CODE['EDITED_ON'] 	= date("Y-m-d H:i");	
					db_perform('M_AR_LEDGER_CODE', $AR_LEDGER_CODE, 'update'," PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ");
				}
			}
		}
		
		if(empty($error)){
			header("location:manage_ar_leder_code");
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
	<title><?=AR_LEDGER_CODE_PAGE_TITLE.' '.MAPPING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=AR_LEDGER_CODE_PAGE_TITLE.' '.MAPPING?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="CODE" >
														<select id="LEDGER_CODE" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LEDGER_CODE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_CODE"><?=LEDGER_CODE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="LEDGER_DESCRIPTION" >
														<select id="LEDGER_DESCRIPTION" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LEDGER_DESCRIPTION))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LEDGER_DESCRIPTION"><?=LEDGER_DESCRIPTION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="INVOICE_DESCRIPTION" >
														<select id="INVOICE_DESCRIPTION" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",INVOICE_DESCRIPTION))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="INVOICE_DESCRIPTION"><?=INVOICE_DESCRIPTION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="GL_CODE_DEBIT" >
														<select id="GL_CODE_DEBIT" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",GL_CODE_DEBIT))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="GL_CODE_DEBIT"><?=GL_CODE_DEBIT?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="GL_CODE_CREDIT" >
														<select id="GL_CODE_CREDIT" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",GL_CODE_CREDIT))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="GL_CODE_CREDIT"><?=GL_CODE_CREDIT?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="TYPE" >
														<select id="TYPE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",TYPE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="TYPE"><?=TYPE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="NEED_ANALYSIS" >
														<select id="NEED_ANALYSIS" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",NEED_ANALYSIS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="NEED_ANALYSIS"><?=NEED_ANALYSIS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="AWARD_LETTER" >
														<select id="AWARD_LETTER" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",AWARD_LETTER))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="AWARD_LETTER"><?=AWARD_LETTER?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="INVOICE" >
														<select id="INVOICE" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",INVOICE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="INVOICE"><?=INVOICE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="TITLE_IV" >
														<select id="TITLE_IV" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",TITLE_IV))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="TITLE_IV"><?=TITLE_IV?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_ar_leder_code'" ><?=CANCEL?></button>
												
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