<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res = $db->Execute("SELECT ENABLE_LSQ FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_LSQ'] == 0) {
	header("location:../index");
	exit;
}

require_once("../global/lsq.php"); 

/*
$notes_PageIndex 		= 1;
$notes_PageSize  		= 100;
$stud_notes_data = get_ls_student_activity_data('0f19ba8b-815a-43ff-b096-aafc7e8bb949', $notes_PageIndex, $notes_PageSize);
echo "<pre>"; print_r($stud_notes_data);exit;*/

$msg = "";
if(!empty($_POST)){
	$inserted = 0;
	$res_stud = $db->Execute("select LSQ_ID, S_STUDENT_MASTER.PK_STUDENT_MASTER, PK_STUDENT_ENROLLMENT FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LSQ_ID != '' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1  ");
	while (!$res_stud->EOF) { 
		$PK_STUDENT_MASTER 		= $res_stud->fields['PK_STUDENT_MASTER'];
		$PK_STUDENT_ENROLLMENT 	= $res_stud->fields['PK_STUDENT_ENROLLMENT'];
		$LSQ_ID 				= $res_stud->fields['LSQ_ID'];
		
		$notes_data_retrived 	= 0;
		$notes_PageIndex 		= 1;
		$notes_PageSize  		= 100;
		do{
			
			$stud_notes_data = get_ls_student_notes_data($LSQ_ID, $notes_PageIndex, $notes_PageSize);
			foreach($stud_notes_data->List as $List) {
				$PK_STUDENT_NOTES = insert_lsq_student_notes($_SESSION['PK_ACCOUNT'], $PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT, $List);
				
				if($PK_STUDENT_NOTES > 0)
					$inserted++;
			}
			
			$notes_data_retrived += $notes_PageSize;
			$notes_PageIndex++;
			
		} while($notes_data_retrived <= $stud_notes_data->RecordCount);
		
		///////////////////////////////////////////////////
		
		$notes_data_retrived 	= 0;
		$notes_PageIndex 		= 1;
		$notes_PageSize  		= 100;
		do{
			
			$stud_notes_data = get_ls_student_activity_data($LSQ_ID, $notes_PageIndex, $notes_PageSize);
			foreach($stud_notes_data->ProspectActivities as $ProspectActivities){
				$insert_notes = 0;
				foreach($ProspectActivities->Data as $Data) {
					if($Data->Key == 'NotableEventDescription') {
						$insert_notes = 1;
						break;
					}
				}
				if($insert_notes == 1) {
					//echo "<pre>";print_r($ProspectActivities);
					
					$PK_STUDENT_NOTES = insert_lsq_student_activity($_SESSION['PK_ACCOUNT'], $PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT, $ProspectActivities);
				
					if($PK_STUDENT_NOTES > 0)
						$inserted++;
				}
			}
			
			$notes_data_retrived += $notes_PageSize;
			$notes_PageIndex++;
			
		} while($notes_data_retrived <= $stud_notes_data->RecordCount);
		
		$res_stud->MoveNext();
	}
	$msg = $inserted." Notes Imported";
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
	<title><?=MNU_IMPORT_STUDENT_NOTES?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
							<?=MNU_IMPORT_STUDENT_NOTES?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<? if($msg != ""){ ?>
									<div class="row">
										<div class="col-md-12" style="color:red" ><?=$msg?></div>
									</div>
									<? } ?>
									<div class="row">
										<div class="col-md-2" style="flex: 0 0 12.667%;max-width: 12.667%;"  >
											<br />
											<input type="hidden" name="hid" />
											<button type="button" name="btn" onclick="submit_form()" class="btn waves-effect waves-light btn-info"><?=IMPORT?></button>
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
		
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
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
				document.form1.submit();
			}
		});
	}
	</script>
	
</body>

</html>