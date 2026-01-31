<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/mail.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$RECEPTIONS 		 			= $_POST['RECEPTION'];
	$FILE_NAMES 	 	 			= $_POST['FILE_NAME'];
	$FILE_LOCATIONS 	 			= $_POST['FILE_LOCATION'];
	$PK_INTERNAL_EMAIL_ATTACHMENT	= $_POST['PK_INTERNAL_EMAIL_ATTACHMENT'];
	unset($_POST['RECEPTION']);
	unset($_POST['FILE_NAME']);
	unset($_POST['FILE_LOCATION']);
	unset($_POST['PK_INTERNAL_EMAIL_ATTACHMENT']);

	$INTERNAL_EMAIL = $_POST;

	if($_GET['id'] == '' || $_GET['type'] == 'forward'){
		$INTERNAL_EMAIL['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
		$INTERNAL_EMAIL['CREATED_BY']  		= $_SESSION['PK_USER'];
		$INTERNAL_EMAIL['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('Z_INTERNAL_EMAIL', $INTERNAL_EMAIL, 'insert');
		$PK_INTERNAL_EMAIL = $db->insert_ID();
		
		$INTERNAL_EMAIL1['INTERNAL_ID'] = $PK_INTERNAL_EMAIL;
		$INTERNAL_ID					= $PK_INTERNAL_EMAIL;
		db_perform('Z_INTERNAL_EMAIL', $INTERNAL_EMAIL1, 'update'," PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' ");
	} else {
		if($_GET['type'] == 'draft') {
			$PK_INTERNAL_EMAIL = $_GET['id'];
			db_perform('Z_INTERNAL_EMAIL', $INTERNAL_EMAIL, 'update'," PK_INTERNAL_EMAIL = '$_GET[id]' AND CREATED_BY = '$_SESSION[PK_USER]' ");
		} else {
			$PK_INTERNAL_EMAIL = $_GET['pk'];
			
			$res = $db->Execute("SELECT INTERNAL_ID from Z_INTERNAL_EMAIL WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' ");
			$INTERNAL_ID = $res->fields['INTERNAL_ID'];
		
			$INTERNAL_EMAIL['INTERNAL_ID'] 		= $INTERNAL_ID;
			$INTERNAL_EMAIL['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$INTERNAL_EMAIL['CREATED_BY']  		= $_SESSION['PK_USER'];
			$INTERNAL_EMAIL['CREATED_ON']  		= date("Y-m-d H:i");
			
			db_perform('Z_INTERNAL_EMAIL', $INTERNAL_EMAIL, 'insert');
			$PK_INTERNAL_EMAIL = $db->insert_ID();
		}
		
	}
	if(!empty($RECEPTIONS)){
		foreach($RECEPTIONS as $RECEPTION){
			//echo "select PK_INTERNAL_EMAIL_RECEPTION from INTERNAL_EMAIL_RECEPTION WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' AND PK_USER = '$RECEPTION' ";exit;
			$res = $db->Execute("select PK_INTERNAL_EMAIL_RECEPTION from Z_INTERNAL_EMAIL_RECEPTION WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' AND PK_USER = '$RECEPTION' ");
			if($res->RecordCount() == 0){
				$INTERNAL_EMAIL_RECEPTION = array();
				$INTERNAL_EMAIL_RECEPTION['INTERNAL_ID'] 		= $INTERNAL_ID;
				$INTERNAL_EMAIL_RECEPTION['PK_INTERNAL_EMAIL'] 	= $PK_INTERNAL_EMAIL;
				$INTERNAL_EMAIL_RECEPTION['PK_USER'] 			= $RECEPTION;
				$INTERNAL_EMAIL_RECEPTION['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				$INTERNAL_EMAIL_RECEPTION['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('Z_INTERNAL_EMAIL_RECEPTION', $INTERNAL_EMAIL_RECEPTION, 'insert');
				$PK_INTERNAL_EMAIL_RECEPTION_IDS[] =  $db->insert_ID();
			} else {
				$PK_INTERNAL_EMAIL_RECEPTION_IDS[] = $res->fields['PK_INTERNAL_EMAIL_RECEPTION'];
			}
		}
	}
	
	$res = $db->Execute("select PK_INTERNAL_EMAIL_RECEPTION from Z_INTERNAL_EMAIL_RECEPTION WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' AND PK_USER = '$_SESSION[PK_USER]' ");
	if($res->RecordCount() == 0){
		$INTERNAL_EMAIL_RECEPTION = array();
		$INTERNAL_EMAIL_RECEPTION['SELF_ADDED'] 		= 1;
		$INTERNAL_EMAIL_RECEPTION['INTERNAL_ID'] 		= $INTERNAL_ID;
		$INTERNAL_EMAIL_RECEPTION['PK_INTERNAL_EMAIL'] 	= $PK_INTERNAL_EMAIL;
		$INTERNAL_EMAIL_RECEPTION['PK_USER'] 			= $_SESSION['PK_USER'];
		$INTERNAL_EMAIL_RECEPTION['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
		$INTERNAL_EMAIL_RECEPTION['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('Z_INTERNAL_EMAIL_RECEPTION', $INTERNAL_EMAIL_RECEPTION, 'insert');
		$PK_INTERNAL_EMAIL_RECEPTION_IDS[] =  $db->insert_ID();
	}
	
	$cond = "";
	if(!empty($PK_INTERNAL_EMAIL_RECEPTION_IDS)){
		$cond = " AND PK_INTERNAL_EMAIL_RECEPTION NOT IN (".implode(",",$PK_INTERNAL_EMAIL_RECEPTION_IDS).") ";
	}
	$db->Execute("DELETE from Z_INTERNAL_EMAIL_RECEPTION WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' $cond AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$i = 0;
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/email_attachments/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	for($k = 0 ; $k < count($_FILES['ATTACHMENT']['name']) ; $k++){
		if($_FILES['ATTACHMENT']['name'][$i] != '') {
			$extn 			= explode(".",$_FILES['ATTACHMENT']['name'][$i]);
			$iindex			= count($extn) - 1;
			$rand_string 	= time()."_".rand(10000,99999);
			$file11			= $rand_string."_".$_FILES['ATTACHMENT']['name'][$i];	
			$extension   	= strtolower($extn[$iindex]);
			
			if($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm"  ){ 
				$newfile1    = $file_dir_1.$file11;
						
				move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'][$i], $newfile1);

				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/email_attachments/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);

				$INTERNAL_EMAIL_ATTACHMENT['PK_INTERNAL_EMAIL'] = $PK_INTERNAL_EMAIL;
				$INTERNAL_EMAIL_ATTACHMENT['FILE_NAME'] 	 	= $_FILES['ATTACHMENT']['name'][$i];
				// $INTERNAL_EMAIL_ATTACHMENT['LOCATION'] 	 		= $newfile1;
				$INTERNAL_EMAIL_ATTACHMENT['LOCATION'] 	 		= $url;
				$INTERNAL_EMAIL_ATTACHMENT['UPLOADED_ON'] 		= date("Y-m-d H:i");
				$INTERNAL_EMAIL_ATTACHMENT['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				db_perform('Z_INTERNAL_EMAIL_ATTACHMENT', $INTERNAL_EMAIL_ATTACHMENT, 'insert');
				$PK_INTERNAL_EMAIL_ATTACHMENT[] = $db->insert_ID();

				// delete tmp file
				unlink($newfile1);
			}
		}
		$i++;
	}
	
	if($_GET['type'] == 'forward') {
		$res_type = $db->Execute("select * from Z_INTERNAL_EMAIL_ATTACHMENT WHERE PK_INTERNAL_EMAIL = '$_GET[id]' ");
		while (!$res_type->EOF) {
			$copy_to = $PK_INTERNAL_EMAIL.'_'.$res_type->fields['LOCATION'];
			copy($res_type->fields['LOCATION'],$copy_to);
			
			$INTERNAL_EMAIL_ATTACHMENT['PK_INTERNAL_EMAIL'] = $PK_INTERNAL_EMAIL;
			$INTERNAL_EMAIL_ATTACHMENT['FILE_NAME'] 	 	= $res_type->fields['FILE_NAME'];
			$INTERNAL_EMAIL_ATTACHMENT['LOCATION'] 	 		= $copy_to;
			$INTERNAL_EMAIL_ATTACHMENT['UPLOADED_ON'] 		= date("Y-m-d H:i");
			$INTERNAL_EMAIL_ATTACHMENT['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			db_perform('Z_INTERNAL_EMAIL_ATTACHMENT', $INTERNAL_EMAIL_ATTACHMENT, 'insert');
			
			$res_type->MoveNext();
		} 
	} else if($_GET['type'] == 'draft') {
		$cond = "";
		if(!empty($PK_INTERNAL_EMAIL_ATTACHMENT)){
			$cond = " AND PK_INTERNAL_EMAIL_ATTACHMENT NOT IN (".implode(",",$PK_INTERNAL_EMAIL_ATTACHMENT).") ";
		}
		$db->Execute("DELETE from Z_INTERNAL_EMAIL_ATTACHMENT WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' $cond AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	if($_POST['DRAFT'] == 0)
		header("location:my_mails");
	else
		header("location:my_mails?type=draft");
}
if($_GET['id'] == ''){
	$SUBJECT 		= '';
	$CONTENT 		= '';

} else {
	$table = "";
	if($_GET['type'] == 'reply' || $_GET['type'] == 'forward') {
		$cond  = " AND Z_INTERNAL_EMAIL_RECEPTION.PK_USER = '$_SESSION[PK_USER]' AND Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL = '$_GET[pk]' AND Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL = Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL  ";
		$table = " ,Z_INTERNAL_EMAIL_RECEPTION";
	} else
		$cond = " AND Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL = '$_GET[id]' AND CREATED_BY = '$_SESSION[PK_USER]' ";

	$res = $db->Execute("select Z_INTERNAL_EMAIL.* from Z_INTERNAL_EMAIL $table WHERE 1=1 $cond");
	if($res->RecordCount() == 0 ){
		header("location:my_mails?type=draft");
		exit;
	}
	
	$SUBJECT		= $res->fields['SUBJECT'];
	$INTERNAL_ID	= $res->fields['INTERNAL_ID'];
	if($_GET['type'] != 'reply') {
		$CONTENT 		= $res->fields['CONTENT'];
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
	<title>
		<?=COMPOSE_TITLE ?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/pages/inbox.css">
	
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
       
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=COMPOSE?></h4>
                    </div>
                </div>
				<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-lg-12">
							<div class="card">
								<div class="row">
									<div class="col-xlg-2 col-lg-3 col-md-4 ">
										<? include('mail_left_menu.php') ?>
									</div>
									<div class="col-xlg-10 col-lg-9 col-md-8 bg-light border-left">
										<div class="card-body">
											<h3 class="card-title">
												<? if($_GET['id'] == '') echo COMPOSE_NEW_MAIL;
												else if($_GET['type'] == 'reply') echo REPLY;
												else if($_GET['type'] == 'reply') echo FORWARD;
												else echo DRAFT; ?>
											</h3>
											<div class="form-group">
												<select name="RECEPTION[]" id="RECEPTION" class="form-control required-entry select2" style="width:95%" multiple >
													<option value="">Select</option>
													<? $res_type = $db->Execute("select PK_USER,CONCAT(SCHOOL_NAME,' - ',S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, PK_USER_TYPE, ID from Z_USER, S_EMPLOYEE_MASTER, Z_ACCOUNT WHERE Z_USER.ACTIVE = '1' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2) AND Z_ACCOUNT.PK_ACCOUNT = S_EMPLOYEE_MASTER.PK_ACCOUNT AND PK_USER != '$_SESSION[PK_USER]' ORDER BY PK_USER_TYPE ASC, NAME ASC ");
													while (!$res_type->EOF) { 
														$PK_USER = $res_type->fields['PK_USER'];
														$dep	 = "";
														
														if($res_type->fields['PK_USER_TYPE'] == 2) {
															$PK_EMPLOYEE_MASTER = $res_type->fields['ID'];
				
															$dep = '';
															$res2 = $db->Execute("select DEPARTMENT FROM M_DEPARTMENT,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
															while (!$res2->EOF) {
																if($dep != '')
																	$dep .= ', ';
																	
																$dep .= $res2->fields['DEPARTMENT'];
																$res2->MoveNext();
															}
														} else if($res_type->fields['PK_USER_TYPE'] == 3) {
															$dep = "Student";
														}
														
														if($dep != '')
															$dep = ' ['.$dep.']';
					
														$selected = '';
														if($_GET['id'] != '') { 
															$res_rep = $db->Execute("select PK_INTERNAL_EMAIL_RECEPTION from Z_INTERNAL_EMAIL_RECEPTION WHERE INTERNAL_ID = '$INTERNAL_ID' AND PK_USER = '$PK_USER' ");
															if($res_rep->RecordCount() > 0)
																$selected = 'selected';
														} ?>
														<option value="<?=$PK_USER?>" <?=$selected?> ><?=$res_type->fields['NAME'].' '.$dep?></option>
													<?	$res_type->MoveNext();
													} ?>	
												</select>
											</div>
											<div class="form-group">
												<? if($_GET['type'] == 'reply')
													echo $SUBJECT;
												else { ?>
												<input class="form-control required-entry" placeholder="<?=SUBJECT?>" id="SUBJECT" name="SUBJECT" value="<?=$SUBJECT?>" >
												<? } ?>
											</div>
											<div class="form-group">
												<textarea class="textarea_editor form-control" name="CONTENT" id="CONTENT" rows="15" placeholder="Enter text ..."><?=$CONTENT?></textarea>
											</div>
											<h4><i class="ti-link"></i> <?=ATTACHMENT?></h4>
											<div class="fallback">
												<input id="ATTACHMENT" type="file" name="ATTACHMENT[]" multiple onchange="ajax_upload1()" />
											</div>
											<div class="form-group">
												<div class="col-xlg-2 col-lg-2 col-md-2">&nbsp;</div>
												<div class="col-xlg-10 col-lg-10 col-md-10" id="attachment_files">
													<? $i = 0;
													if($_GET['id'] != '' && $_GET['type'] != 'reply'){
														$res_type = $db->Execute("select * from Z_INTERNAL_EMAIL_ATTACHMENT WHERE PK_INTERNAL_EMAIL = '$_GET[id]' ");
														while (!$res_type->EOF) { ?>
															<div id="attach_<?=$i?>" >
																<input type="hidden" name="PK_INTERNAL_EMAIL_ATTACHMENT[]" value="<?=$res_type->fields['PK_INTERNAL_EMAIL_ATTACHMENT']?>" >	
																<input type="hidden" name="FILE_NAME[]" value="<?=$res_type->fields['FILE_NAME']?>" >	
																<input type="hidden" name="FILE_LOCATION[]" value="<?=$res_type->fields['LOCATION']?>" >
																<a href="<?=$res_type->fields['LOCATION']?>" target="blank" ><?=$res_type->fields['FILE_NAME']?></a>
																<a href="javascript:void(0)" onclick="delete_attachment('<?=$i?>')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a></a>
															</div>
														<?	$i++;
															$res_type->MoveNext();
														} 
													}
													$uploded_count = $i; ?>
												</div>
											</div>
											
											<input type="hidden" name="DRAFT" id="DRAFT" value="0" >
											<button type="button" onclick="save_frm(0)" class="btn btn-success"><i class="fa fa-envelope-o"></i> <?=SEND?></button>
											<button type="button" onclick="save_frm(1)" class="btn btn-success"><i class="fa fa-envelope-o"></i> <?=SAVE_AS_DRAFT?></button>
											<button type="button" onclick="window.location.href='my_mails'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
       <? require_once("footer.php"); ?>
	   
	   <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?=DELETE_MESSAGE_GENERAL ?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete_attachment(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete_attachment(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
		
    </div>
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function save_frm(val){
		document.getElementById('DRAFT').value = val; 
		
		var valid = new Validation('form1', {onSubmit:false});
		var result = valid.validate();
		if(result == true)
			document.form1.submit();
	}
	</script>
	
	<!-- <script src="https://cdn.tiny.cloud/1/d6quzxl18kigwmmr6z03zgk3w47922rw1epwafi19cfnj00i/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
	<? require_once("../global/tiny-cloud.php"); ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			tinymce.init({ 
				selector:'textarea',
				browser_spellcheck: true,
				menubar:false,
				statusbar: false,
				height: '300',
				plugins: [
					'advlist lists hr pagebreak',
					'wordcount code',
					'nonbreaking save table contextmenu directionality',
					'template paste textcolor colorpicker textpattern '
				],
				toolbar1: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor',	
				paste_data_images: true,
				height: 400,
			});
			
		});
		
		function delete_attachment(id){
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}
		function conf_delete_attachment(val){
			jQuery(document).ready(function($) {
				if(val == 1)
					$("#attach_"+$("#DELETE_ID").val()).remove();
					
				$("#deleteModal").modal("hide");
			});
		}
    </script>
	
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#RECEPTION').select2({
				placeholder: "<?=TO?>",
			});
		});
	</script>
</body>

</html>
