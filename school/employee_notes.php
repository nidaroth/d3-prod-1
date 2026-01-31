<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/notes.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'document_del'){
	$db->Execute("DELETE FROM S_EMPLOYEE_NOTES_DOCUMENTS WHERE PK_EMPLOYEE_NOTES_DOCUMENTS = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:employee_notes?id=".$_GET['id'].'&eid='.$_GET['eid'].'&t='.$_GET['t']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$EMPLOYEE_NOTES = $_POST;
	$EMPLOYEE_NOTES['SATISFIED'] = $_POST['SATISFIED'];

	if($EMPLOYEE_NOTES['FOLLOWUP_DATE'] != '')
		$EMPLOYEE_NOTES['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($EMPLOYEE_NOTES['FOLLOWUP_DATE']));
		
	if($EMPLOYEE_NOTES['FOLLOWUP_TIME'] != '')
		$EMPLOYEE_NOTES['FOLLOWUP_TIME'] = date("H:i:s",strtotime($EMPLOYEE_NOTES['FOLLOWUP_TIME']));
	else
		$EMPLOYEE_NOTES['FOLLOWUP_TIME'] = '';

	if($EMPLOYEE_NOTES['NOTE_DATE'] != '')
		$EMPLOYEE_NOTES['NOTE_DATE'] = date("Y-m-d",strtotime($EMPLOYEE_NOTES['NOTE_DATE']));
	else
		$EMPLOYEE_NOTES['NOTE_DATE'] = '';
	
	if($EMPLOYEE_NOTES['NOTE_TIME'] != '')
		$EMPLOYEE_NOTES['NOTE_TIME'] = date("H:i:s",strtotime($EMPLOYEE_NOTES['NOTE_TIME']));
	else
		$EMPLOYEE_NOTES['NOTE_TIME'] = '';
		
	if($_GET['id'] == ''){
		$EMPLOYEE_NOTES['PK_ACCOUNT']   			= $_SESSION['PK_ACCOUNT'];
		$EMPLOYEE_NOTES['PK_EMPLOYEE_MASTER'] 		= $_GET['eid'];
		$EMPLOYEE_NOTES['PK_DEPARTMENT'] 			= $_POST['PK_DEPARTMENT'];
		$EMPLOYEE_NOTES['PK_EMPLOYEE_NOTE_TYPE'] 	= $_POST['PK_EMPLOYEE_NOTE_TYPE'];
		$EMPLOYEE_NOTES['NOTES'] 					= $_POST['NOTES'];
		
		$EMPLOYEE_NOTES['CREATED_BY']  		= $_SESSION['PK_USER'];
		$EMPLOYEE_NOTES['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_NOTES', $EMPLOYEE_NOTES, 'insert');
		
		$PK_EMPLOYEE_NOTES = $db->insert_ID();
	} else {
		$PK_EMPLOYEE_NOTES = $_GET['id'];
		//echo "<pre> K_EMPLOYEE_NOTES = '$PK_EMPLOYEE_NOTES' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";print_r($EMPLOYEE_NOTES);exit;
		$EMPLOYEE_NOTES['EDITED_BY']  = $_SESSION['PK_USER'];
		$EMPLOYEE_NOTES['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_NOTES', $EMPLOYEE_NOTES, 'update', " PK_EMPLOYEE_NOTES = '$PK_EMPLOYEE_NOTES' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' " );
	}
	//echo "<pre>";print_r($EMPLOYEE_DOCUMENTS);exit;
	
	//echo "<pre>";print_r($_FILES);exit;
	$i = 0;
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/employee/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	for($k = 0 ; $k < count($_FILES['ATTACHMENT']['name']) ; $k++){

		$extn 			= explode(".",$_FILES['ATTACHMENT']['name'][$i]);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."_".rand(10000,99999);
		$file11			= $_GET['eid'].'_task_'.$rand_string.".".$extn[$iindex];	
		$extension   	= strtolower($extn[$iindex]);
		
		if($extension != "php" && $extension != "js" && $extension != "html" && $extension != "htm"  ){ 
			$newfile1    = $file_dir_1.$file11;
					
			move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'][$i], $newfile1);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/employee/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);
			
			// $EMPLOYEE_NOTES_DOCUMENTS['DOCUMENT_PATH'] 		= $newfile1;
			$EMPLOYEE_NOTES_DOCUMENTS['DOCUMENT_PATH'] 		= $url;
			$EMPLOYEE_NOTES_DOCUMENTS['DOCUMENT_NAME'] 		= $_FILES['ATTACHMENT']['name'][$i];
			$EMPLOYEE_NOTES_DOCUMENTS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$EMPLOYEE_NOTES_DOCUMENTS['PK_EMPLOYEE_NOTES'] 	= $PK_EMPLOYEE_NOTES;
			$EMPLOYEE_NOTES_DOCUMENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$EMPLOYEE_NOTES_DOCUMENTS['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_EMPLOYEE_NOTES_DOCUMENTS', $EMPLOYEE_NOTES_DOCUMENTS, 'insert');

			// delete tmp file
			unlink($newfile1);
		}
		
		$i++;
	}
	
	header("location:employee?id=".$_GET['eid'].'&t='.$_GET['t'].'&tab=notes');
}
if($_GET['id'] == ''){
	$PK_DEPARTMENT 				= '';
	$PK_EMPLOYEE_NOTE_TYPE		= '';
	$FOLLOWUP_DATE 				= '';
	$FOLLOWUP_TIME				= '';
	$PK_NOTE_STATUS 			= '';
	$PK_NOTES_PRIORITY_MASTER 	= '';
	$IS_EVENT 					= '';
	$SATISFIED					= '';
	$NOTES	 					= '';
	$NOTE_DATE 					= '';
	$NOTE_TIME 					= '';
	$PK_EVENT_OTHER				= '';
	if($_GET['event'] == 1)
		$IS_EVENT = 1;
} else {
	$res = $db->Execute("SELECT * FROM S_EMPLOYEE_NOTES WHERE PK_EMPLOYEE_NOTES = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	if($res->RecordCount() == 0){
		header("location:employee?id=".$_GET['eid'].'&t='.$_GET['t']);
		exit;
	}
	
	$PK_DEPARTMENT 				= $res->fields['PK_DEPARTMENT'];
	$PK_EMPLOYEE_NOTE_TYPE 		= $res->fields['PK_EMPLOYEE_NOTE_TYPE'];
	$NOTES  					= $res->fields['NOTES'];
	$FOLLOWUP_DATE 				= $res->fields['FOLLOWUP_DATE'];
	$FOLLOWUP_TIME  			= $res->fields['FOLLOWUP_TIME'];
	$PK_NOTE_STATUS 			= $res->fields['PK_NOTE_STATUS'];
	$PK_NOTES_PRIORITY_MASTER 	= $res->fields['PK_NOTES_PRIORITY_MASTER'];
	$IS_EVENT 					= $res->fields['IS_EVENT'];
	$SATISFIED 					= $res->fields['SATISFIED'];
	$NOTE_DATE 					= $res->fields['NOTE_DATE'];
	$NOTE_TIME 					= $res->fields['NOTE_TIME'];
	$PK_EVENT_OTHER 			= $res->fields['PK_EVENT_OTHER'];
	
	if($FOLLOWUP_DATE != '0000-00-00')
		$FOLLOWUP_DATE = date("m/d/Y",strtotime($FOLLOWUP_DATE));
	else
		$FOLLOWUP_DATE = '';
		
	if($FOLLOWUP_TIME != '00:00:00')
		$FOLLOWUP_TIME = date("h:i A",strtotime($FOLLOWUP_TIME));
	else
		$FOLLOWUP_TIME = '';
		
	if($NOTE_DATE != '0000-00-00')
		$NOTE_DATE = date("m/d/Y",strtotime($NOTE_DATE));
	else
		$NOTE_DATE = '';

	if($NOTE_TIME != '00:00:00')
		$NOTE_TIME = date("h:i A",strtotime($NOTE_TIME));
	else
		$NOTE_TIME = '';
}

$res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[eid]' AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$IMAGE					= $res->fields['IMAGE'];
$FIRST_NAME 			= $res->fields['FIRST_NAME'];
$LAST_NAME 				= $res->fields['LAST_NAME'];
$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];

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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?=NOTES_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles" >
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor" ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=NOTES_PAGE_TITLE?> </h4>
                    </div>
					<div class="col-md-1 align-self-center" >
						<? if($IMAGE != '') { ?>
							<div class="row el-element-overlay">
								<div class="card" style="margin-bottom: 0;" >
									<div class="el-card-item" style="padding-bottom:0" >
										<div class="el-card-avatar el-overlay-1" style="margin-bottom: 0;" > 
											<img src="<?=$IMAGE?>" alt="user" />
											<div class="el-overlay">
												<ul class="el-info">
													<li><a class="btn default btn-outline image-popup-vertical-fit" href="<?=$IMAGE?>"><i class="icon-magnifier"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!--<img src="<?=$IMAGE?>" style="height: 80px;" />-->
						<? } ?>
					</div>
					<div class="col-md-3 align-self-center">
						<?=$FIRST_NAME.' '.$MIDDLE_NAME.' '.$LAST_NAME?>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
									<div class="row">
                                        <div class="col-md-12">
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<? $res_type = $db->Execute("select PK_EMPLOYEE_NOTE_TYPE,EMPLOYEE_NOTE_TYPE,DESCRIPTION from M_EMPLOYEE_NOTE_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by EMPLOYEE_NOTE_TYPE ASC"); ?>
														<select id="PK_EMPLOYEE_NOTE_TYPE" name="PK_EMPLOYEE_NOTE_TYPE" class="form-control">
															<option></option>
															<? while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_EMPLOYEE_NOTE_TYPE']?>" <? if($res_type->fields['PK_EMPLOYEE_NOTE_TYPE'] == $PK_EMPLOYEE_NOTE_TYPE) echo "selected"; ?> ><?=$res_type->fields['EMPLOYEE_NOTE_TYPE'].' '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_EMPLOYEE_NOTE_TYPE">
															<? echo NOTES_TYPE; ?>
														</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select id="PK_NOTE_STATUS" name="PK_NOTE_STATUS" class="form-control">
															<option></option>
															<? $res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by NOTE_STATUS ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_NOTE_STATUS']?>" <? if($res_type->fields['PK_NOTE_STATUS'] == $PK_NOTE_STATUS) echo "selected"; ?> ><?=$res_type->fields['NOTE_STATUS']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_NOTE_STATUS">
															<? echo NOTE_STATUS;?>
														</label>
													</div>
												</div>
													
											</div>
										
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40" id="NOTE_DATE_LABEL" >
														<input type="text" class="form-control date" id="NOTE_DATE" name="NOTE_DATE" value="<?=$NOTE_DATE?>" >
														<span class="bar"></span>
														<label for="NOTE_DATE">
															<?=NOTE_DATE;?>
														</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40" id="NOTE_TIME_LABEL" >
														<input type="text" class="form-control timepicker" id="NOTE_TIME" name="NOTE_TIME" value="<?=$NOTE_TIME?>" >
														<span class="bar"></span>
														<label for="NOTE_TIME">
															<?=NOTE_TIME;?>
														</label>
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
														<label for="FOLLOWUP_TIME"><?=TIME?></label>
													</div>
												</div>
												
											</div>
											
											<div class="row">
												<div class="col-md-9">
													<div class="form-group m-b-40">
														<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
														<span class="bar"></span>
														<label for="NOTES"><?=COMMENTS?></label>
													</div>
												</div>
												
											</div>
											
											<div class="row">	
												<div class="col-md-9">
													<div class="row">
														<div class="col-md-12">
															<a href="javascript:void(0)" onclick="add_attachment()" ><b><?=ADD_ATTACHMENTS?></b></a>
															<div id="attachments_div"> </div>
														</div>
													</div>
													<? if($_GET['id'] != ''){
														$res_type = $db->Execute("select PK_EMPLOYEE_NOTES_DOCUMENTS,DOCUMENT_NAME,DOCUMENT_PATH from S_EMPLOYEE_NOTES_DOCUMENTS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_NOTES = '$_GET[id]' ");
														while (!$res_type->EOF) { ?>
															<div class="row">
																<div class="col-md-10">
																	<a href="<?=aws_url($res_type->fields['DOCUMENT_PATH'])?>" target="_blank" ><?=$res_type->fields['DOCUMENT_NAME']?></a>
																</div>
																<div class="col-md-2">
																	<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_EMPLOYEE_NOTES_DOCUMENTS']?>','document')" title="<?=DELETE?>" class="btn"><i class="icon-trash"></i></a>
																</div>
															</div>
														<?	$res_type->MoveNext();
														}
													} ?>
												</div>
										
												<!--<div class="col-sm-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="SATISFIED" name="SATISFIED" value="1" <? if($SATISFIED == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="SATISFIED"><?=COMPLETE?></label>
														</div>
													</div>
												</div>-->
											</div>
										</div>
									 </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='employee?id=<?=$_GET['eid']?>&t=<?=$_GET['t']?>&tab=notes'" ><?=CANCEL?></button>
												
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
						window.location.href = 'employee_notes?act=document_del&id=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>&iid='+$("#DELETE_ID").val();
											
				} else
					$("#deleteModal").modal("hide");
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
			
			document.getElementById('NOTE_DATE').value = t[1]+'/'+t[0]+'/'+t[2]
			document.getElementById('NOTE_TIME').value = time
			
			document.getElementById('NOTE_DATE_LABEL').classList.add("focused");
			document.getElementById('NOTE_TIME_LABEL').classList.add("focused");
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