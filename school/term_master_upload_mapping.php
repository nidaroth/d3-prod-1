<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/term_master.php");
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
		
	$res = $db->Execute("SELECT FILE_LOCATION,HEADING_ROW_NO FROM Z_EXCEL_MAP_MASTER WHERE PK_MAP_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
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
	
			$TERM_MASTER = array();
			
			$res_fields = $db->Execute("SELECT * from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]'");
			while (!$res_fields->EOF) {
				$TABLE_COLUMN = trim($res_fields->fields['TABLE_COLUMN']);
				$EXCEL_COLUMN = trim($res_fields->fields['EXCEL_COLUMN']);
				
				$TERM_MASTER[$TABLE_COLUMN] = trim($row[$EXCEL_COLUMN]);
				
				$res_fields->MoveNext();
			}
			
			$error_str = "";

			if($TERM_MASTER['ALLOW_ONLINE_ENROLLMENT'] != '') {
				if(strtolower($TERM_MASTER['ALLOW_ONLINE_ENROLLMENT']) == 'yes' || strtolower($TERM_MASTER['ALLOW_ONLINE_ENROLLMENT']) == 'y')
					$TERM_MASTER['ALLOW_ONLINE_ENROLLMENT'] = 1;
				else if(strtolower($TERM_MASTER['ALLOW_ONLINE_ENROLLMENT']) == 'no' || strtolower($TERM_MASTER['ALLOW_ONLINE_ENROLLMENT']) == 'n')
					$TERM_MASTER['ALLOW_ONLINE_ENROLLMENT'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= 'Need Analysis <b>'.$TERM_MASTER['ALLOW_ONLINE_ENROLLMENT'].'</b>';
				}
			}
			
			if($TERM_MASTER['LMS_ACTIVE'] != '') {
				if(strtolower(trim($TERM_MASTER['LMS_ACTIVE'])) == 'yes' || strtolower(trim($TERM_MASTER['LMS_ACTIVE'])) == 'y')
					$TERM_MASTER['LMS_ACTIVE'] = 1;
				else if(strtolower(trim($TERM_MASTER['LMS_ACTIVE'])) == 'no' || strtolower(trim($TERM_MASTER['LMS_ACTIVE'])) == 'n')
					$TERM_MASTER['LMS_ACTIVE'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= 'Award Letter <b>'.$TERM_MASTER['LMS_ACTIVE'].'</b>';
				}
			}
			
			if($TERM_MASTER['BEGIN_DATE'] != '') {
				$BEGIN_DATE = str_replace("/","-",$TERM_MASTER['BEGIN_DATE']);
				$BEGIN_DATE = explode("-",$BEGIN_DATE);
				if($BEGIN_DATE[2] < 100)
					$year = 2000 + $BEGIN_DATE[2];
				else
					$year = $BEGIN_DATE[2];
				
				$TERM_MASTER['BEGIN_DATE'] = $year.'/'.$BEGIN_DATE[0].'/'.$BEGIN_DATE[1];
			}
			
			if($TERM_MASTER['END_DATE'] != '') {
				$END_DATE = str_replace("/","-",$TERM_MASTER['END_DATE']);
				$END_DATE = explode("-",$END_DATE);
				if($END_DATE[2] < 100)
					$year = 2000 + $END_DATE[2];
				else
					$year = $END_DATE[2];
				
				$TERM_MASTER['END_DATE'] = $year.'/'.$END_DATE[0].'/'.$END_DATE[1];
			}
			
			$PK_CAMPUS_ARR = array();
			if($TERM_MASTER['CAMPUS'] != '') {
				$CAMPUS_arr = explode(",", $TERM_MASTER['CAMPUS']);
				foreach($CAMPUS_arr as $CAMPUS_1){
					$CAMPUS_1 = trim($CAMPUS_1);
					if($CAMPUS_1 != ''){
						$res_camp = $db->Execute("SELECT PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (TRIM(OFFICIAL_CAMPUS_NAME) = '$CAMPUS_1' OR TRIM(CAMPUS_NAME) = '$CAMPUS_1' OR TRIM(CAMPUS_CODE) = '$CAMPUS_1' ) AND ACTIVE = 1 ");
						if($res_camp->RecordCount() > 0){
							$PK_CAMPUS_ARR[] = $res_camp->fields['PK_CAMPUS'];
						} else {
							if($error_str != '')
								$error_str .= ', ';
							$error_str .= 'Invalid Campus <b>'.$CAMPUS_1.'</b>';
						}
					}
				}
			}
			unset($TERM_MASTER['CAMPUS']);
			
			if($error_str != '')
				$error[] = 'Row #'.$i.' - Invalid '.$error_str;
			else {
				$TERM_MASTER['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
				$TERM_MASTER['CREATED_BY'] 	= $_SESSION['PK_USER'];
				$TERM_MASTER['CREATED_ON'] 	= date("Y-m-d H:i");	
				//echo "<pre>";print_r($TERM_MASTER);exit;
				db_perform('S_TERM_MASTER', $TERM_MASTER, 'insert');
				$PK_TERM_MASTER = $db->insert_ID();
				
				if(!empty($PK_CAMPUS_ARR)){
					foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
						$TERM_MASTER_CAMPUS['PK_TERM_MASTER']   = $PK_TERM_MASTER;
						$TERM_MASTER_CAMPUS['PK_CAMPUS'] 		= $PK_CAMPUS;
						$TERM_MASTER_CAMPUS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
						$TERM_MASTER_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
						$TERM_MASTER_CAMPUS['CREATED_ON']  		= date("Y-m-d H:i");
						db_perform('S_TERM_MASTER_CAMPUS', $TERM_MASTER_CAMPUS, 'insert');
					}
				}
			}
		}
		
		if(empty($error)){
			header("location:manage_term_master");
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
	<title><?=TERM_MASTER_PAGE_TITLE.' '.MAPPING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=TERM_MASTER_PAGE_TITLE.' '.MAPPING?> </h4>
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
														<input type="hidden" name="FIELDS[]" value="BEGIN_DATE" >
														<select id="BEGIN_DATE" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",BEGIN_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="BEGIN_DATE"><?=BEGIN_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="END_DATE" >
														<select id="END_DATE" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",END_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="END_DATE"><?=END_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="TERM_DESCRIPTION" >
														<select id="TERM_DESCRIPTION" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DESCRIPTION))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="TERM_DESCRIPTION"><?=DESCRIPTION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="TERM_GROUP" >
														<select id="TERM_GROUP" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",GROUP))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="GROUP"><?=GROUP?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="ALLOW_ONLINE_ENROLLMENT" >
														<select id="ALLOW_ONLINE_ENROLLMENT" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ALLOW_ONLINE_ENROLLMENT))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="ALLOW_ONLINE_ENROLLMENT"><?=ALLOW_ONLINE_ENROLLMENT?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="LMS_ACTIVE" >
														<select id="LMS_ACTIVE" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LMS_ACTIVE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="LMS_ACTIVE"><?=LMS_ACTIVE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="CAMPUS" >
														<select id="CAMPUS" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CAMPUS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="CAMPUS"><?=CAMPUS?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_term_master'" ><?=CANCEL?></button>
												
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