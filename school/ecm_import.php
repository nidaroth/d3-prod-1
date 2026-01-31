<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/ecm_ledger.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

$res_add_on = $db->Execute("SELECT ECM FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_TITLE_IV_SERVICER') == 0 || $res_add_on->fields['ECM'] == 0){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if (trim($_FILES['FILE_XLX']['name'])!= ""){
		$extn = explode(".",$_FILES['FILE_XLX']['name']);
		$type = $_FILES['FILE_XLX']['type'];
		$ii = count($extn) - 1;
		if(strtolower($extn[$ii]) == 'txt' || strtolower($extn[$ii]) == 'csv'){ 
			$ext1 = $extn[$ii];
			if(strtolower($extn[$ii]) == 'txt')
				$ext1 = 'csv';
				
			// $newfile1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/ecm_'.date("Y-m-d-H-i-s").'.'.$ext1;
			$newfile1 = '../backend_assets/tmp_upload/ecm_'.date("Y-m-d-H-i-s").'.'.$ext1;
			move_uploaded_file($_FILES['FILE_XLX']['tmp_name'], $newfile1);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/ecm_'.date("Y-m-d-H-i-s").'.'.$ext1;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);

			include '../global/excel/Classes/PHPExcel/IOFactory.php';
			$inputFileName = $newfile1;
			
			$inputFileType = 'CSV';
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			
			$ECM_PROCESSOR['FILE_NAME']    = $_FILES['FILE_XLX']['name'];
			// $ECM_PROCESSOR['LOCATION']     = $newfile1;
			$ECM_PROCESSOR['LOCATION']     = $url;
			$ECM_PROCESSOR['PK_ACCOUNT']   = $_SESSION['PK_ACCOUNT'];
			$ECM_PROCESSOR['UPLOADED_BY']  = $_SESSION['PK_USER'];
			$ECM_PROCESSOR['UPLOADED_ON']  = date("Y-m-d H:i");
			db_perform('S_ECM_PROCESSOR', $ECM_PROCESSOR, 'insert');
			$PK_ECM_PROCESSOR = $db->insert_ID();

			// delete tmp file
			unlink($newfile1);
			
			$i = 1;
			foreach($sheetData as $row){
				//echo "<pre>";print_r($row);	exit;
				/*if($i == 1){
					$i++;
					continue;
				}*/
				
				$start_date = '';
				$end_date 	= '';
				
				$res = $db->Execute("SELECT PK_AWARD_YEAR FROM M_AWARD_YEAR WHERE TRIM(SHORT_DESC) = '".trim($row['H'])."' ");
				$PK_AWARD_YEAR = $res->fields['PK_AWARD_YEAR'];
		
				$res = $db->Execute("SELECT PK_ECM_LEDGER_MASTER, PK_ECM_LEDGER_TYPE_MASTER FROM M_ECM_LEDGER_MASTER WHERE TRIM(ECM_LEDGER) = '".trim($row['A'])."' ");
				$PK_ECM_LEDGER_MASTER 		= $res->fields['PK_ECM_LEDGER_MASTER'];
				$PK_ECM_LEDGER_TYPE_MASTER 	= $res->fields['PK_ECM_LEDGER_TYPE_MASTER'];
				
				$res = $db->Execute("SELECT PK_ECM_LEDGER,PK_AR_LEDGER_CODE FROM M_ECM_LEDGER  WHERE PK_ECM_LEDGER_MASTER = '$PK_ECM_LEDGER_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AWARD_YEAR = '$PK_AWARD_YEAR' ");
				$PK_ECM_LEDGER 		= $res->fields['PK_ECM_LEDGER'];
				$PK_AR_LEDGER_CODE 	= $res->fields['PK_AR_LEDGER_CODE'];
				
				$PK_STUDENT_MASTER = 0;
				$SSN = preg_replace( '/[^0-9]/', '',$row['C']);
				if($SSN != '') {
					$SSN = $SSN[0].$SSN[1].$SSN[2].'-'.$SSN[3].$SSN[4].'-'.$SSN[5].$SSN[6].$SSN[7].$SSN[8];
					
					$SSN = my_encrypt($_SESSION['PK_ACCOUNT'],$SSN);
					$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_MASTER  WHERE SSN = '$SSN' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
				}
				//echo $PK_STUDENT_MASTER.'----<br />';
				$ECM_PROCESSOR_DETAIL = array();
				$ECM_PROCESSOR_DETAIL['ECM_LEDGER']    		= $row['A'];
				$ECM_PROCESSOR_DETAIL['PK_ECM_LEDGER']    	= $PK_ECM_LEDGER;
				$ECM_PROCESSOR_DETAIL['PK_AR_LEDGER_CODE']  = $PK_AR_LEDGER_CODE;
				$ECM_PROCESSOR_DETAIL['PK_STUDENT_MASTER']  = $PK_STUDENT_MASTER;
				$ECM_PROCESSOR_DETAIL['FA_SCHOOL_CODE']    	= $row['B'];
				$ECM_PROCESSOR_DETAIL['SSN']    			= $SSN;
				$ECM_PROCESSOR_DETAIL['DISBURSEMENT_DATE']  = $row['G'];
				$ECM_PROCESSOR_DETAIL['AWARD_YEAR']    		= $row['H'];
				$ECM_PROCESSOR_DETAIL['PELL_AMOUNT']   	 	= $row['I'];
				$ECM_PROCESSOR_DETAIL['SUB_AMOUNT']    		= $row['J'];
				$ECM_PROCESSOR_DETAIL['UNSUB_AMOUNT']    	= $row['K'];
				$ECM_PROCESSOR_DETAIL['PLUS_AMOUNT']    	= $row['L'];
				$ECM_PROCESSOR_DETAIL['SEOG_AMOUNT']    	= $row['M'];
				$ECM_PROCESSOR_DETAIL['DISBURSE_AMOUNT']    = $ECM_PROCESSOR_DETAIL['PELL_AMOUNT'] + $ECM_PROCESSOR_DETAIL['SUB_AMOUNT'] + $ECM_PROCESSOR_DETAIL['UNSUB_AMOUNT'] + $ECM_PROCESSOR_DETAIL['PLUS_AMOUNT'] + $ECM_PROCESSOR_DETAIL['SEOG_AMOUNT'] ;
				$ECM_PROCESSOR_DETAIL['PK_AWARD_YEAR']    	= $PK_AWARD_YEAR;
				$ECM_PROCESSOR_DETAIL['PK_ECM_PROCESSOR']   = $PK_ECM_PROCESSOR;
				$ECM_PROCESSOR_DETAIL['PK_ACCOUNT']   		= $_SESSION['PK_ACCOUNT'];
				
				if($ECM_PROCESSOR_DETAIL['DISBURSEMENT_DATE'] != '') {
					$ECM_PROCESSOR_DETAIL['DISBURSEMENT_DATE'] = date("Y-m-d",strtotime($ECM_PROCESSOR_DETAIL['DISBURSEMENT_DATE']));
					
					$start_date = date('Y-m-d',(strtotime( '-30 day' , strtotime($ECM_PROCESSOR_DETAIL['DISBURSEMENT_DATE'])))); 
					$end_date 	= date('Y-m-d',(strtotime( '+30 day' , strtotime($ECM_PROCESSOR_DETAIL['DISBURSEMENT_DATE'])))); 
				}
				if($PK_ECM_LEDGER_TYPE_MASTER == 1){
					//loan
					$MIN_AMOUNT = $ECM_PROCESSOR_DETAIL['DISBURSE_AMOUNT'] - 5;
					$MAX_AMOUNT = $ECM_PROCESSOR_DETAIL['DISBURSE_AMOUNT'] + 5;
				} else if($PK_ECM_LEDGER_TYPE_MASTER == 2){
					//Pell
					$MIN_AMOUNT = $ECM_PROCESSOR_DETAIL['DISBURSE_AMOUNT'] - 1;
					$MAX_AMOUNT = $ECM_PROCESSOR_DETAIL['DISBURSE_AMOUNT'] + 1;
				}
				$res_disb = $db->Execute("SELECT PK_STUDENT_DISBURSEMENT, PK_STUDENT_ENROLLMENT FROM S_STUDENT_DISBURSEMENT  WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND DISBURSEMENT_AMOUNT BETWEEN '$MIN_AMOUNT' and '$MAX_AMOUNT' AND DISBURSEMENT_DATE BETWEEN '$start_date' and '$end_date' AND PK_DISBURSEMENT_STATUS = 2 AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_AWARD_YEAR = '$PK_AWARD_YEAR' AND PK_STUDENT_MASTER > 0");
				
				if($res_disb->RecordCount() > 0){
					$ECM_PROCESSOR_DETAIL['PK_STUDENT_DISBURSEMENT'] 	= $res_disb->fields['PK_STUDENT_DISBURSEMENT'];
					$ECM_PROCESSOR_DETAIL['PK_STUDENT_ENROLLMENT'] 		= $res_disb->fields['PK_STUDENT_ENROLLMENT'];
					$ECM_PROCESSOR_DETAIL['MESSAGE'] 					= 'Payment Matched to Student';
				} else {
					if($ECM_PROCESSOR_DETAIL['PK_STUDENT_MASTER'] == '' || $ECM_PROCESSOR_DETAIL['PK_STUDENT_MASTER'] == 0)
						$ECM_PROCESSOR_DETAIL['MESSAGE'] = 'No Matching Student Found';
					else
						$ECM_PROCESSOR_DETAIL['MESSAGE'] = 'Payment Not Matched to Student';
				}
				
				db_perform('S_ECM_PROCESSOR_DETAIL', $ECM_PROCESSOR_DETAIL, 'insert');
			}
			
			header("location:ecm_import_result?id=".$PK_ECM_PROCESSOR);
			exit;
			
		} else {
		    $msg1 = 'Invalid file format. Please upload .txt or .csv file only'; 
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
	<title><?=MNU_ECM_TRANSACTION_IMPORT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_ECM_TRANSACTION_IMPORT ?> </h4>
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
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>
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