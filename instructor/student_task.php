<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/student_task.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

if($_GET['act'] == 'document_del'){
	$db->Execute("DELETE FROM S_STUDENT_TASK_DOCUMENTS WHERE PK_STUDENT_TASK_DOCUMENTS = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:student_task?id=".$_GET['id'].'&sid='.$_GET['sid'].'&eid='.$_GET['eid']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$STUDENT_TASK = $_POST;
	$STUDENT_TASK['COMPLETED'] = $_POST['COMPLETED'];
	
	//$res_type = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = '3' ");
	//$PK_DEPARTMENT = $res_type->fields['PK_DEPARTMENT'];	
	
	$PK_DEPARTMENT = -1;

	if($STUDENT_TASK['TASK_DATE'] != '') {
		$STUDENT_TASK['TASK_DATE'] 	= date("Y-m-d",strtotime($STUDENT_TASK['TASK_DATE']));
		$TASK_DATE					= date("Y-m-d",strtotime($STUDENT_TASK['TASK_DATE']));
	} else {
		$STUDENT_TASK['TASK_DATE'] 	= '';
		$TASK_DATE					= date("Y-m-d");
	}
	
	if($STUDENT_TASK['TASK_TIME'] != '')
		$STUDENT_TASK['TASK_TIME'] = date("H:i:s",strtotime($STUDENT_TASK['TASK_TIME']));
	else
		$STUDENT_TASK['TASK_TIME'] = '';
		
	if($STUDENT_TASK['FOLLOWUP_DATE'] != '') {
		$STUDENT_TASK['FOLLOWUP_DATE']  = date("Y-m-d",strtotime($STUDENT_TASK['FOLLOWUP_DATE']));
		$FOLLOWUP_DATE					= $STUDENT_TASK['FOLLOWUP_DATE'];
	} else {
		$STUDENT_TASK['FOLLOWUP_DATE'] 	= '';
		$FOLLOWUP_DATE					=  '';
	}
		
	if($STUDENT_TASK['FOLLOWUP_TIME'] != '')
		$STUDENT_TASK['FOLLOWUP_TIME'] = date("H:i:s",strtotime($STUDENT_TASK['FOLLOWUP_TIME']));
	else
		$STUDENT_TASK['FOLLOWUP_TIME'] = '';
	
	$STUDENT_TASK['PK_DEPARTMENT'] 			= $PK_DEPARTMENT;
	$STUDENT_TASK['NOTES'] 					= $_POST['NOTES'];
	
	$res_type = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");
	
	if($_GET['id'] == ''){
		$STUDENT_TASK['PK_EMPLOYEE_MASTER']   	= $_SESSION['PK_EMPLOYEE_MASTER'];
		$STUDENT_TASK['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
		$STUDENT_TASK['PK_STUDENT_ENROLLMENT'] 	= $_GET['eid'];
		$STUDENT_TASK['PK_STUDENT_MASTER'] 		= $res_type->fields['PK_STUDENT_MASTER'];
		$STUDENT_TASK['CREATED_BY']  			= $_SESSION['PK_USER'];
		$STUDENT_TASK['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_STUDENT_TASK', $STUDENT_TASK, 'insert');
		$PK_STUDENT_TASK = $db->insert_ID();
	} else {
		$PK_STUDENT_TASK = $_GET['id'];
		$STUDENT_TASK['EDITED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_TASK['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_STUDENT_TASK', $STUDENT_TASK, 'update'," PK_STUDENT_TASK = '$PK_STUDENT_TASK' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
	}
	//echo "<pre>";print_r($STUDENT_DOCUMENTS);exit;
	
	//echo "<pre>";print_r($_FILES);exit;
	$i = 0;
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	for($k = 0 ; $k < count($_FILES['ATTACHMENT']['name']) ; $k++){
		if($_FILES['ATTACHMENT']['name'][$i] != '') {
			$extn 			= explode(".",$_FILES['ATTACHMENT']['name'][$i]);
			$iindex			= count($extn) - 1;
			$rand_string 	= time()."_".rand(10000,99999);
			$file11			= $PK_STUDENT_MASTER.'_task_'.$rand_string.".".$extn[$iindex];	
			$extension   	= strtolower($extn[$iindex]);
			
			if($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm"  ){ 
				$newfile1    = $file_dir_1.$file11;
						
				move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'][$i], $newfile1);

				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/student/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);
				
				// $STUDENT_TASK_DOCUMENTS['DOCUMENT_PATH'] 	= $newfile1;
				$STUDENT_TASK_DOCUMENTS['DOCUMENT_PATH'] 	= $url;
				$STUDENT_TASK_DOCUMENTS['DOCUMENT_NAME'] 	= $_FILES['ATTACHMENT']['name'][$i];
				$STUDENT_TASK_DOCUMENTS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				$STUDENT_TASK_DOCUMENTS['PK_STUDENT_TASK'] 	= $PK_STUDENT_TASK;
				$STUDENT_TASK_DOCUMENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$STUDENT_TASK_DOCUMENTS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_STUDENT_TASK_DOCUMENTS', $STUDENT_TASK_DOCUMENTS, 'insert');

				// delete tmp file
				unlink($newfile1);
			}
		}
		$i++;
	}
	if($_GET['p'] == 'i')
		header("location:index.php");
	else if($_GET['p'] == 'm')
		header("location:management");
	else
		header("location:student?id=".$_GET['sid'].'&eid='.$_GET['eid'].'&tab=taskTab');
}
if($_GET['id'] == ''){
	$PK_DEPARTMENT		= '';
	$TASK_DATE 			= '';
	$TASK_TIME			= '';
	$PK_TASK_TYPE	 	= '';
	$PK_TASK_STATUS	 	= '';
	$PK_EMPLOYEE_MASTER	= $_SESSION['PK_EMPLOYEE_MASTER'];
	$FOLLOWUP_DATE	 	= '';
	$FOLLOWUP_TIME		= '';
	$COMPLETED	 		= '';
	$NOTES	 			= '';
	$PK_EVENT_OTHER		= '';
	$PK_NOTES_PRIORITY_MASTER = '';
} else {
	$res = $db->Execute("SELECT * FROM S_STUDENT_TASK WHERE PK_STUDENT_TASK = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' "); 
	
	if($res->RecordCount() == 0){
		header("location:student?id=".$_GET['sid'].'&tab=taskTab&t='.$_GET['t'].'&eid='.$_GET['eid']);
		exit;
	}
	
	$PK_DEPARTMENT 		= $res->fields['PK_DEPARTMENT'];
	$TASK_DATE 			= $res->fields['TASK_DATE'];
	$TASK_TIME 			= $res->fields['TASK_TIME'];
	$PK_TASK_TYPE  		= $res->fields['PK_TASK_TYPE'];
	$PK_TASK_STATUS  	= $res->fields['PK_TASK_STATUS'];
	$PK_EMPLOYEE_MASTER = $res->fields['PK_EMPLOYEE_MASTER'];
	$FOLLOWUP_DATE  	= $res->fields['FOLLOWUP_DATE'];
	$FOLLOWUP_TIME  	= $res->fields['FOLLOWUP_TIME'];
	$COMPLETED  		= $res->fields['COMPLETED'];
	$NOTES  			= $res->fields['NOTES'];
	$PK_EVENT_OTHER		= $res->fields['PK_EVENT_OTHER'];
	$PK_NOTES_PRIORITY_MASTER = $res->fields['PK_NOTES_PRIORITY_MASTER'];
	
	if($PK_DEPARTMENT == -1)
		$SHOW_ON_ALL_DEP = 1;
	else
		$SHOW_ON_ALL_DEP = 0;
	
	if($TASK_DATE != '0000-00-00')
		$TASK_DATE = date("m/d/Y",strtotime($TASK_DATE));
	else
		$TASK_DATE = '';
		
	if($FOLLOWUP_DATE != '0000-00-00')
		$FOLLOWUP_DATE = date("m/d/Y",strtotime($FOLLOWUP_DATE));
	else
		$FOLLOWUP_DATE = '';
		
	if($TASK_TIME != '00:00:00')
		$TASK_TIME = date("h:i A",strtotime($TASK_TIME));
	else
		$TASK_TIME = '';
		
	if($FOLLOWUP_TIME != '00:00:00')
		$FOLLOWUP_TIME = date("h:i A",strtotime($FOLLOWUP_TIME));
	else
		$FOLLOWUP_TIME = '';
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
	<title><?=TASK_PAGE_TITLE ?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=TASK_PAGE_TITLE ?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_TASK_TYPE" name="PK_TASK_TYPE" class="form-control required-entry">
													<option></option>
													<? $cond = " AND (PK_DEPARTMENT = -1) ";
													$res_type = $db->Execute("select PK_TASK_TYPE,TASK_TYPE,DESCRIPTION from M_TASK_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by TASK_TYPE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TASK_TYPE']?>" <? if($res_type->fields['PK_TASK_TYPE'] == $PK_TASK_TYPE) echo "selected"; ?> ><?=$res_type->fields['TASK_TYPE'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_TASK_TYPE">
													<?=TASK_TYPE ?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_TASK_STATUS" name="PK_TASK_STATUS" class="form-control ">
													<option></option>
													<? $res_type = $db->Execute("select PK_TASK_STATUS,TASK_STATUS,DESCRIPTION from M_TASK_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT = -1 order by TASK_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TASK_STATUS']?>" <? if($res_type->fields['PK_TASK_STATUS'] == $PK_TASK_STATUS) echo "selected"; ?> ><?=$res_type->fields['TASK_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_TASK_STATUS">
													<?=TASK_STATUS ?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER" class="form-control ">
													<option></option>
													<? $res_type = $db->Execute("select PK_EVENT_OTHER,EVENT_OTHER,DESCRIPTION from M_EVENT_OTHER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT = -1 AND TYPE = 1 order by EVENT_OTHER ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EVENT_OTHER']?>" <? if($res_type->fields['PK_EVENT_OTHER'] == $PK_EVENT_OTHER) echo "selected"; ?> ><?=$res_type->fields['EVENT_OTHER'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_EVENT_OTHER">
													<?=TASK_OTHER ?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_NOTES_PRIORITY_MASTER" name="PK_NOTES_PRIORITY_MASTER" class="form-control">
													<option></option>
													<? $res_type = $db->Execute("select PK_NOTES_PRIORITY_MASTER,NOTES_PRIORITY from M_NOTES_PRIORITY_MASTER WHERE ACTIVE = 1 ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_NOTES_PRIORITY_MASTER']?>" <? if($res_type->fields['PK_NOTES_PRIORITY_MASTER'] == $PK_NOTES_PRIORITY_MASTER) echo "selected"; ?> ><?=$res_type->fields['NOTES_PRIORITY']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_NOTES_PRIORITY_MASTER">
													<?=PRIORITY?>
												</label>
											</div>
										</div>
										
												
									</div>
									
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40 " id="TASK_DATE_LABEL">
												<input type="text" class="form-control required-entry date" id="TASK_DATE" name="TASK_DATE" value="<?=$TASK_DATE?>" onchange="check_date()" >
												<span class="bar"></span>
												<label for="TASK_DATE"><?=TASK_DATE?></label>
												<div id="date_error" style="color:red" ></div>
											</div>
											
										</div>
								   
										<div class="col-md-3">
											<div class="form-group m-b-40 " id="TASK_TIME_LABEL" >
												<input type="text" class="form-control required-entry timepicker" id="TASK_TIME" name="TASK_TIME" value="<?=$TASK_TIME?>" >
												<span class="bar"></span>
												<label for="TASK_TIME"><?=TASK_TIME?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="FOLLOWUP_DATE" name="FOLLOWUP_DATE" value="<?=$FOLLOWUP_DATE?>" >
												<span class="bar"></span>
												<label for="FOLLOWUP_DATE"><?=FOLLOWUP_DATE?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control timepicker" id="FOLLOWUP_TIME" name="FOLLOWUP_TIME" value="<?=$FOLLOWUP_TIME?>" >
												<span class="bar"></span>
												<label for="FOLLOWUP_TIME"><?=TASK_TIME?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
												<span class="bar"></span>
												<label for="NOTES"><?=COMMENTS?></label>
											</div>
										</div>
										<div class="col-md-6">
											<a href="javascript:void(0)" onclick="add_attachment()" ><b><?=ADD_ATTACHMENTS?></b></a>
											<div id="attachments_div"> </div>
											
											<? if($_GET['id'] != ''){
												$res_type = $db->Execute("select PK_STUDENT_TASK_DOCUMENTS,DOCUMENT_NAME,DOCUMENT_PATH from S_STUDENT_TASK_DOCUMENTS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_TASK = '$_GET[id]' ");
												while (!$res_type->EOF) { ?>
													<div class="row">
														<div class="col-md-10">
															<a href="<?=aws_url($res_type->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_type->fields['DOCUMENT_NAME']?></a>
														</div>
														<div class="col-md-2">
															<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_TASK_DOCUMENTS']?>','document')" title="<?=DELETE?>" class="btn"><i class="icon-trash"></i></a>
														</div>
													</div>
												<?	$res_type->MoveNext();
												}
											} ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-3 custom-control custom-checkbox form-group">
											<input type="checkbox" class="custom-control-input" id="COMPLETED" name="COMPLETED" value="1" <? if($COMPLETED == 1) echo "checked"; ?> >
											<label class="custom-control-label" for="COMPLETED"><?=COMPLETED?></label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6"></div>
										<div class="col-md-6">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<? if($_GET['p'] == 'i')
												$url = "index";
											else if($_GET['p'] == 'm')
												$url = "management";
											else
												$url = "student?id=".$_GET['sid'].'&eid='.$_GET['eid'].'&tab=taskTab'; ?>
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='<?=$url?>'" ><?=CANCEL?></button>
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
    <? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
	<script src="../backend_assets/dist/js/pages/mask.init.js"></script>

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
		<? if($_GET['id'] == ''){ ?>
		timenow()
		<? } ?>
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
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
						window.location.href = 'student_task?act=document_del&id=<?=$_GET['id']?>&sid=<?=$_GET['sid']?>&eid=<?=$_GET['eid']?>&iid='+$("#DELETE_ID").val();
											
				} else
					$("#deleteModal").modal("hide");
			});
		}
		
		function check_date(){
			jQuery(document).ready(function($) {
				if(document.getElementById('TASK_DATE').value != "") {
					var data  = 'date='+document.getElementById('TASK_DATE').value
					var value = $.ajax({
						url: "../school/ajax_check_date_for_leave",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('date_error').innerHTML = data;
						}		
					}).responseText;
				} else
					document.getElementById('date_error').innerHTML = '';
			});
		}
		function timenow(){
			var now= new Date(), 
			ampm= 'am', 
			h= now.getHours(), 
			m= now.getMinutes(), 
			s= now.getSeconds();
			if(h >= 12){
				if(h > 12) h -= 12;
					ampm= 'pm';
			}

			if(m<10) m= '0'+m;
			if(s<10) s= '0'+s;
			//var t = now.toLocaleDateString('en-GB')
			var t = FixLocaleDateString(now.toLocaleDateString('en-GB'))
			var time = h + ':' + m + ' ' + ampm;
			t = t.split("/");
			//var t1 = t[2]+'-'+t[1]+'-'+t[0]+' '+time;
			//return t1; 
			
			document.getElementById('TASK_DATE').value = t[1]+'/'+t[0]+'/'+t[2]
			document.getElementById('TASK_TIME').value = time
			
			document.getElementById('TASK_DATE_LABEL').classList.add("focused");
			document.getElementById('TASK_TIME_LABEL').classList.add("focused");
		}
		function FixLocaleDateString(localeDate) {
			var newStr = "";
			for (var i = 0; i < localeDate.length; i++) {
				var code = localeDate.charCodeAt(i);
				if (code >= 47 && code <= 57) {
					newStr += localeDate.charAt(i);
				}
			}
			return newStr;
		}
		
	</script>
	
	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
    <script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>
	
</body>

</html>