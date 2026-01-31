<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/company_event.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'document_del'){
	$res = $db->Execute("SELECT DOCUMENT_PATH FROM S_COMPANY_EVENT_DOCUMENTS WHERE PK_COMPANY_EVENT_DOCUMENTS = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	unlink($res->fields['DOCUMENT_PATH']);
	
	$db->Execute("DELETE FROM S_COMPANY_EVENT_DOCUMENTS WHERE PK_COMPANY_EVENT_DOCUMENTS = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:company_event?id=".$_GET['id'].'&cid='.$_GET['cid']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$COMPANY_EVENT = $_POST;
	$COMPANY_EVENT['EVENT_DATE']   	 = ($COMPANY_EVENT['EVENT_DATE'] != '' ? date("Y-m-d",strtotime($COMPANY_EVENT['EVENT_DATE'])) : '');
	$COMPANY_EVENT['FOLLOW_UP_DATE'] = ($COMPANY_EVENT['FOLLOW_UP_DATE'] != '' ? date("Y-m-d",strtotime($COMPANY_EVENT['FOLLOW_UP_DATE'])) : '');

	if(isset($COMPANY_EVENT['COMPLETE']))
		$COMPANY_EVENT['COMPLETE'] = 1;
	else
		$COMPANY_EVENT['COMPLETE'] = 0;

	if($_GET['id'] == '') {
		$COMPANY_EVENT['PK_COMPANY']  = $_GET['cid'];
		$COMPANY_EVENT['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$COMPANY_EVENT['CREATED_BY']  = $_SESSION['PK_USER'];
		$COMPANY_EVENT['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_COMPANY_EVENT', $COMPANY_EVENT, 'insert');
		$PK_COMPANY_EVENT = $db->insert_ID();
	} 
	else {
		$COMPANY_EVENT['EDITED_BY']  = $_SESSION['PK_USER'];
		$COMPANY_EVENT['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_COMPANY_EVENT', $COMPANY_EVENT, 'update'," PK_COMPANY_EVENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]'");
		$PK_COMPANY_EVENT = $_GET['id'];
	}
	
	$i = 0;
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	for($k = 0 ; $k < count($_FILES['ATTACHMENT']['name']) ; $k++){

		$extn 			= explode(".",$_FILES['ATTACHMENT']['name'][$i]);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."_".rand(10000,99999);
		$file11			= $PK_STUDENT_MASTER.'_comp_event_'.$rand_string.".".$extn[$iindex];	
		$extension   	= strtolower($extn[$iindex]);
		
		if($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm"  ){ 
			$newfile1    = $file_dir_1.$file11;
					
			move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'][$i], $newfile1);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);
			
			// $COMPANY_EVENT_DOCUMENTS['DOCUMENT_PATH'] 		= $newfile1;
			$COMPANY_EVENT_DOCUMENTS['DOCUMENT_PATH'] 		= $url;
			$COMPANY_EVENT_DOCUMENTS['DOCUMENT_NAME'] 		= $_FILES['ATTACHMENT']['name'][$i];
			$COMPANY_EVENT_DOCUMENTS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$COMPANY_EVENT_DOCUMENTS['PK_COMPANY_EVENT'] 	= $PK_COMPANY_EVENT;
			$COMPANY_EVENT_DOCUMENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$COMPANY_EVENT_DOCUMENTS['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_COMPANY_EVENT_DOCUMENTS', $COMPANY_EVENT_DOCUMENTS, 'insert');

			// delete tmp file
			unlink($newfile1);
		}
		
		$i++;
	}

	header("location:company?id=".$_GET['cid']."&tab=eventsTab");
}
if($_GET['id'] == '') {
	$PK_PLACEMENT_COMPANY_EVENT_TYPE = '';
	$EVENT_DATE			   			 = '';
	$FOLLOW_UP_DATE 			     = '';
	$PK_COMPANY_CONTACT_EMPLOYEE 	 = '';
	$PK_COMPANY_CONTACT 		     = '';
	$COMPLETE 			   			 = '';
	$NOTE 	   						 = '';
	$ACTIVE  		   				 = '';
} 
else {
	$res = $db->Execute("SELECT * FROM S_COMPANY_EVENT WHERE PK_COMPANY_EVENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]'"); 
	if($res->RecordCount() == 0) {
		header("location:company?id=".$_GET['id']);
		exit;
	}

	$PK_PLACEMENT_COMPANY_EVENT_TYPE = $res->fields['PK_PLACEMENT_COMPANY_EVENT_TYPE'];
	$EVENT_DATE			   			 = $res->fields['EVENT_DATE'];
	$FOLLOW_UP_DATE 			     = $res->fields['FOLLOW_UP_DATE'];
	$PK_COMPANY_CONTACT_EMPLOYEE 	 = $res->fields['PK_COMPANY_CONTACT_EMPLOYEE'];
	$PK_COMPANY_CONTACT 		     = $res->fields['PK_COMPANY_CONTACT'];
	$COMPLETE 			   			 = $res->fields['COMPLETE'];
	$NOTE 	   						 = $res->fields['NOTE'];
	$ACTIVE  		   				 = $res->fields['ACTIVE'];

	$FOLLOW_UP_DATE  = ($FOLLOW_UP_DATE != '0000-00-00' &&  $FOLLOW_UP_DATE != '' ? date("m/d/Y",strtotime($FOLLOW_UP_DATE)) : '');
	$EVENT_DATE   	 = ($EVENT_DATE != '0000-00-00' &&  $EVENT_DATE != '' ? date("m/d/Y",strtotime($EVENT_DATE)) : '');
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
	<title><?=COMPANY_EVENT_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=COMPANY_EVENT_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >									
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-6">
											<div class="form-group m-b-40">
												<select id="PK_PLACEMENT_COMPANY_EVENT_TYPE" name="PK_PLACEMENT_COMPANY_EVENT_TYPE" class="form-control">
													<option selected></option>
														<? $res_type = $db->Execute("select PK_PLACEMENT_COMPANY_EVENT_TYPE, PLACEMENT_COMPANY_EVENT_TYPE from M_PLACEMENT_COMPANY_EVENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY PLACEMENT_COMPANY_EVENT_TYPE ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PLACEMENT_COMPANY_EVENT_TYPE'] ?>" <? if($PK_PLACEMENT_COMPANY_EVENT_TYPE == $res_type->fields['PK_PLACEMENT_COMPANY_EVENT_TYPE']) echo "selected"; ?> ><?=$res_type->fields['PLACEMENT_COMPANY_EVENT_TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_PLACEMENT_COMPANY_EVENT_TYPE"><?=EVENT_TYPE?></label>
											</div>
										</div>
                                    </div>

									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date date-inputmask" id="EVENT_DATE" name="EVENT_DATE" value="<?=$EVENT_DATE?>" >
												<span class="bar"></span>
												<label for="EVENT_DATE"><?=EVENT_DATE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date date-inputmask" id="FOLLOW_UP_DATE" name="FOLLOW_UP_DATE" value="<?=$FOLLOW_UP_DATE?>" >
												<span class="bar"></span>
												<label for="FOLLOW_UP_DATE"><?=FOLLOW_UP_DATE?></label>
											</div>
										</div>
									</div>

									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
											<select id="PK_COMPANY_CONTACT" name="PK_COMPANY_CONTACT" class="form-control" >
													<option selected></option>
														<? $res_type = $db->Execute("select PK_COMPANY_CONTACT, NAME from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]' AND ACTIVE = '1' ORDER BY NAME ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_COMPANY_CONTACT'] ?>" <? if($PK_COMPANY_CONTACT == $res_type->fields['PK_COMPANY_CONTACT']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_COMPANY_CONTACT"><?=CONTACT?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<select id="PK_COMPANY_CONTACT_EMPLOYEE" name="PK_COMPANY_CONTACT_EMPLOYEE" class="form-control" >
													<option selected></option>
													<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($PK_COMPANY_CONTACT_EMPLOYEE == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_COMPANY_CONTACT_EMPLOYEE"><?=EMPLOYEE?></label>
											</div>
										</div>
                                    </div>

									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-6">
											<div class="form-group m-b-40">
												<textarea class="form-control  rich" id="NOTE" name="NOTE"><?=$NOTE?></textarea>
												<span class="bar"></span> 
												<label for="NOTE"><?=NOTE?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-5">
											<div class="row">
												<div class="col-md-12">
													<a href="javascript:void(0)" onclick="add_attachment()" ><b><?=ADD_ATTACHMENTS?></b></a>
													<div id="attachments_div"> </div>
												</div>
											</div>
											<? if($_GET['id'] != ''){
												$res_type = $db->Execute("select PK_COMPANY_EVENT_DOCUMENTS,DOCUMENT_NAME,DOCUMENT_PATH from S_COMPANY_EVENT_DOCUMENTS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY_EVENT = '$_GET[id]' ");
												while (!$res_type->EOF) { ?>
													<div class="row">
														<div class="col-md-10">
															<a href="<?=aws_url($res_type->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_type->fields['DOCUMENT_NAME']?></a>
														</div>
														<div class="col-md-2">
															<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_COMPANY_EVENT_DOCUMENTS']?>','document')" title="<?=DELETE?>" class="btn"><i class="icon-trash"></i></a>
														</div>
													</div>
												<?	$res_type->MoveNext();
												}
											} ?>
										</div>
										
										<div class="col-md-3 custom-control custom-checkbox form-group">
											<input type="checkbox" class="custom-control-input" id="COMPLETE" name="COMPLETE" value="1" <? if($COMPLETE == 1) echo "checked"; ?> >
											<label class="custom-control-label" for="COMPLETE"><?=COMPLETE?></label>
										</div>
									</div>

									<? if($_GET['id'] != ''){ ?>
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-6">
											<div class="row form-group">
												<div class="custom-control col-md-2 mt-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1 ml-2">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label ml-2" for="customRadio22">No</label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
																	
									<div class="row p-b-10">
                                        <div class="col-md-5 submit-button-sec">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='company?id=<?=$_GET['cid']?>&tab=eventsTab'" ><?=CANCEL?></button>
											</div>
										</div>
									</div>
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
			
			<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" id="delete_message" ></div>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="0" />
						</div>
						<div class="modal-footer">
							<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		var form1 = new Validation('form1');

		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
		
		function add_attachment(){
			var name  =  'ATTACHMENT[]';
			var data  =  '<div class="row" >';
				data += 	'<div class="col-lg-8">';
				data += 	 	'<input type="file" name="'+name+'" multiple />';
				data += 	 '</div>';
				data += '</div>';
			jQuery(document).ready(function($) {
				$("#attachments_div").append(data);
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'document')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.DOCUMENT?>?';
				
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'document')
						window.location.href = 'company_event?act=document_del&id=<?=$_GET['id']?>&cid=<?=$_GET['cid']?>&iid='+$("#DELETE_ID").val();
											
				} else
					$("#deleteModal").modal("hide");
			});
		}
	</script>

</body>

</html>